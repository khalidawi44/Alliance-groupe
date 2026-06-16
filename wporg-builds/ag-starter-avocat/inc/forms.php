<?php
/**
 * Form handlers (Avocat)
 *
 * Handles the front-end "Prendre rendez-vous confidentiel" form.
 * Uses WordPress wp_mail() (no external dependency), nonce + honeypot
 * for spam protection, and respects the RGPD consent checkbox.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle the RDV form submission. Hooked early on init so it runs
 * before the front page renders and we can stash a status in a
 * transient that the template will read.
 */
function ag_starter_avocat_handle_rdv_form() {
	if ( ! isset( $_POST['ag_rdv_submit'] ) ) {
		return;
	}
	if ( ! isset( $_POST['ag_rdv_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ag_rdv_nonce'] ) ), 'ag_rdv_send' ) ) {
		ag_starter_avocat_set_rdv_status( 'error', __( 'Erreur de securite. Veuillez recharger la page et reessayer.', 'ag-starter-avocat' ) );
		return;
	}

	// Honeypot : real users leave this empty. Bots fill it.
	if ( ! empty( $_POST['ag_rdv_website'] ) ) {
		ag_starter_avocat_set_rdv_status( 'error', __( 'Spam detecte.', 'ag-starter-avocat' ) );
		return;
	}

	// RGPD consent.
	if ( empty( $_POST['ag_rdv_rgpd'] ) ) {
		ag_starter_avocat_set_rdv_status( 'error', __( 'Vous devez accepter le traitement de vos donnees pour soumettre une demande.', 'ag-starter-avocat' ) );
		return;
	}

	// Sanitize inputs.
	$nom       = isset( $_POST['ag_rdv_nom'] )     ? sanitize_text_field( wp_unslash( $_POST['ag_rdv_nom'] ) )     : '';
	$prenom    = isset( $_POST['ag_rdv_prenom'] )  ? sanitize_text_field( wp_unslash( $_POST['ag_rdv_prenom'] ) )  : '';
	$email     = isset( $_POST['ag_rdv_email'] )   ? sanitize_email( wp_unslash( $_POST['ag_rdv_email'] ) )        : '';
	$tel       = isset( $_POST['ag_rdv_tel'] )     ? sanitize_text_field( wp_unslash( $_POST['ag_rdv_tel'] ) )     : '';
	$domaine   = isset( $_POST['ag_rdv_domaine'] ) ? sanitize_text_field( wp_unslash( $_POST['ag_rdv_domaine'] ) ) : '';
	$format    = isset( $_POST['ag_rdv_format'] )  ? sanitize_text_field( wp_unslash( $_POST['ag_rdv_format'] ) )  : 'cabinet';
	$message   = isset( $_POST['ag_rdv_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ag_rdv_message'] ) ) : '';

	// Required fields.
	if ( '' === $nom || '' === $email || ! is_email( $email ) || '' === $message ) {
		ag_starter_avocat_set_rdv_status( 'error', __( 'Merci de remplir au moins le nom, un email valide et une description du dossier.', 'ag-starter-avocat' ) );
		return;
	}

	// Compose email body.
	$lines = array(
		sprintf( 'Nouvelle demande de rendez-vous depuis le site %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ),
		'',
		sprintf( 'Nom        : %s', $nom ),
		sprintf( 'Prenom     : %s', $prenom ),
		sprintf( 'Email      : %s', $email ),
		sprintf( 'Telephone  : %s', $tel ? $tel : '(non renseigne)' ),
		sprintf( 'Domaine    : %s', $domaine ? $domaine : '(non precise)' ),
		sprintf( 'Format     : %s', $format ),
		'',
		'Description du dossier :',
		$message,
		'',
		'---',
		'RGPD : consentement explicite donne par le demandeur.',
		sprintf( 'Date : %s', wp_date( 'd/m/Y H:i' ) ),
		sprintf( 'IP   : %s', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '?' ),
	);
	$body = implode( "\n", $lines );

	$recipient = ag_starter_avocat_get_option( 'ag_rdv_recipient_email' );
	if ( '' === $recipient || ! is_email( $recipient ) ) {
		$recipient = ag_starter_avocat_get_option( 'ag_cabinet_email' );
	}
	if ( '' === $recipient || ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$subject = sprintf( '[Site] Nouvelle demande de RDV — %s %s', $prenom, $nom );
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $email,
	);

	$sent = wp_mail( $recipient, $subject, $body, $headers );
	if ( $sent ) {
		ag_starter_avocat_set_rdv_status( 'success', __( 'Votre demande a bien ete envoyee. Le cabinet vous recontactera sous 48h ouvrees, en toute confidentialite.', 'ag-starter-avocat' ) );
	} else {
		ag_starter_avocat_set_rdv_status( 'error', __( 'Une erreur est survenue lors de l\'envoi. Vous pouvez nous joindre directement par email ou telephone.', 'ag-starter-avocat' ) );
	}
}
add_action( 'init', 'ag_starter_avocat_handle_rdv_form' );

/**
 * Stash a status (success / error + message) in a transient keyed
 * on the visitor's session cookie so the template can read it on
 * the next page load and clear it.
 *
 * @param string $type    success|error.
 * @param string $message Localized message.
 */
function ag_starter_avocat_set_rdv_status( $type, $message ) {
	$key = 'ag_rdv_' . md5( ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) . wp_get_session_token() );
	set_transient(
		$key,
		array(
			'type'    => $type,
			'message' => $message,
		),
		60
	);
	setcookie( 'ag_rdv_token', $key, time() + 60, '/', '', is_ssl(), true );
}

/**
 * Read and clear the RDV status (called from front-page.php).
 *
 * @return array|null
 */
function ag_starter_avocat_get_rdv_status() {
	if ( empty( $_COOKIE['ag_rdv_token'] ) ) {
		return null;
	}
	$key    = sanitize_text_field( wp_unslash( $_COOKIE['ag_rdv_token'] ) );
	$status = get_transient( $key );
	if ( $status ) {
		delete_transient( $key );
		setcookie( 'ag_rdv_token', '', time() - 3600, '/', '', is_ssl(), true );
		return $status;
	}
	return null;
}
