<?php
/**
 * Formulaire de contact du site principal (page-contact.php).
 *
 * Le template poste vers admin-post.php avec action=ag_contact_form ; il
 * manquait le HANDLER côté serveur → le formulaire ne faisait RIEN (aucun
 * lead, aucune notification, aucune conversion Google Ads possible).
 *
 * Ce module :
 *   1. Traite l'envoi (nonce + honeypot anti-spam), envoie un email + une
 *      alerte interne (SMS/Telegram), et ajoute le contact au CRM Prospection.
 *   2. Redirige vers /contact/?envoye=1 (message de succès).
 *   3. Déclenche la conversion Google Ads « Demande de devis » sur l'écran
 *      de succès (label configurable via l'option ag_ads_conversion_devis).
 *
 * @package AG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Label de conversion Google Ads (Extrait d'événement). Filtrable/optionnel. */
function ag_contact_conversion_label() {
	return trim( (string) apply_filters(
		'ag_ads_conversion_devis',
		get_option( 'ag_ads_conversion_devis', 'AW-18188842206/ZCMWCPfQvcEcEN7pjuFD' )
	) );
}

/**
 * Handler du formulaire de contact (connecté + visiteur).
 */
function ag_contact_form_handle() {
	$back = wp_get_referer() ?: home_url( '/contact/' );

	// 1. Nonce.
	if ( ! isset( $_POST['ag_nonce'] ) || ! wp_verify_nonce( $_POST['ag_nonce'], 'ag_contact_nonce' ) ) {
		wp_safe_redirect( add_query_arg( 'envoye', 'err', $back ) );
		exit;
	}

	// 2. Honeypot anti-spam : si le champ caché « ag_site » est rempli, on
	//    fait semblant d'accepter (pas de traitement) pour ne pas guider le bot.
	if ( ! empty( $_POST['ag_site'] ) ) {
		wp_safe_redirect( add_query_arg( 'envoye', '1', $back ) );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$service = sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		wp_safe_redirect( add_query_arg( 'envoye', 'err', $back ) );
		exit;
	}

	$labels  = array(
		'creation-web' => 'Création Web', 'ia' => 'IA & Automatisation', 'seo' => 'SEO',
		'publicite' => 'Publicité Digitale', 'branding' => 'Branding', 'conseil' => 'Conseil Stratégique',
	);
	$service_lbl = $labels[ $service ] ?? ( $service ?: '—' );

	// 3. Email de notification (brandé si dispo).
	$to      = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
	$subject = '📩 Nouveau message de contact — ' . $name;
	$lines   = sprintf(
		"<p><strong>Nom :</strong> %s</p><p><strong>Email :</strong> %s</p><p><strong>Service :</strong> %s</p><p><strong>Message :</strong><br>%s</p>",
		esc_html( $name ), esc_html( $email ), esc_html( $service_lbl ), nl2br( esc_html( $message ) )
	);
	$body    = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Nouveau contact', $lines ) : $lines;
	$headers = array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
	wp_mail( $to, $subject, $body, $headers );

	// 4. Alerte interne (SMS Free + Telegram) + agenda (.ics) si dispo.
	if ( function_exists( 'ag_push' ) ) {
		ag_push( '📩 Contact : ' . $name, $service_lbl . ' — ' . $email . "\n" . wp_trim_words( $message, 30 ) );
	}
	if ( function_exists( 'ag_calendar_notify' ) ) {
		ag_calendar_notify( 'Contact : ' . $name, $service_lbl . ' — ' . $email . ' — ' . $message );
	}

	// 5. CRM Prospection.
	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'   => $name,
			'email'  => $email,
			'status' => 'intéressé',
			'source' => 'contact',
			'notes'  => 'Service : ' . $service_lbl . ' | ' . $message,
		) );
	}

	do_action( 'ag_contact_form_submitted', compact( 'name', 'email', 'service', 'message' ) );

	// 6. Succès → ?envoye=1 (déclenche le message + la conversion).
	wp_safe_redirect( add_query_arg( 'envoye', '1', remove_query_arg( array( 'envoye' ), $back ) ) . '#ag-contact-form' );
	exit;
}
add_action( 'admin_post_nopriv_ag_contact_form', 'ag_contact_form_handle' );
add_action( 'admin_post_ag_contact_form', 'ag_contact_form_handle' );

/**
 * Déclenche la conversion Google Ads sur l'écran de succès du formulaire
 * (?envoye=1) — fonctionne avec la balise de base déjà posée (functions.php).
 */
add_action( 'wp_footer', function () {
	if ( ! isset( $_GET['envoye'] ) || '1' !== $_GET['envoye'] ) {
		return;
	}
	$label = ag_contact_conversion_label();
	if ( '' === $label ) {
		return;
	}
	?>
<script>
if (typeof gtag === 'function') {
	gtag('event', 'conversion', { 'send_to': '<?php echo esc_js( $label ); ?>' });
}
</script>
	<?php
}, 99 );
