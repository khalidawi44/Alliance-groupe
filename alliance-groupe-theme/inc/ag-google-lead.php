<?php
/**
 * AG — Réception des leads Google Ads (Lead Form Extensions) → CRM.
 *
 * Les formulaires de lead Google Ads peuvent POSTer chaque lead vers un webhook.
 * Ici : endpoint REST `ag/v1/google-lead`, validé par une CLÉ secrète (la même
 * que tu colles dans Google Ads → « Webhook » → Clé). À réception :
 *   - création d'un prospect dans le CRM (ag_prospect_add_record, source
 *     « lead-google-ads », statut « interesse ») ;
 *   - notification interne (ag_push : Telegram/SMS).
 *
 * Réglages : Réglages → « Leads Google Ads » (affiche l'URL du webhook + la clé).
 *
 * Doc payload Google : { lead_id, user_column_data:[{column_id,column_name,
 * string_value}], google_key, is_test, campaign_id, ... }.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Clé secrète attendue (auto-générée à la 1re visite des réglages). */
function ag_glead_key() {
	$k = get_option( 'ag_google_lead_key', '' );
	if ( '' === $k ) {
		$k = wp_generate_password( 24, false, false );
		update_option( 'ag_google_lead_key', $k );
	}
	return $k;
}

/** URL publique du webhook à coller dans Google Ads. */
function ag_glead_webhook_url() {
	return rest_url( 'ag/v1/google-lead' );
}

/* ── Endpoint REST ───────────────────────────────────────────────── */
add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/google-lead', array(
		'methods'             => 'POST',
		'callback'            => 'ag_glead_receive',
		'permission_callback' => '__return_true', // validé par la clé dans le corps
	) );
} );

function ag_glead_receive( WP_REST_Request $req ) {
	$data = $req->get_json_params();
	if ( ! is_array( $data ) ) {
		$data = $req->get_params();
	}

	// 1) Validation de la clé (Google envoie "google_key").
	$sent = isset( $data['google_key'] ) ? (string) $data['google_key'] : '';
	if ( ! hash_equals( ag_glead_key(), $sent ) ) {
		return new WP_REST_Response( array( 'status' => 'unauthorized' ), 401 );
	}

	// 2) Ping de test de Google → on répond 200 sans créer de lead.
	if ( ! empty( $data['is_test'] ) ) {
		return new WP_REST_Response( array( 'status' => 'ok', 'test' => true ), 200 );
	}

	// 3) Extraction des champs (par column_id, sinon par libellé).
	$name = $email = $phone = '';
	$extra = array();
	if ( ! empty( $data['user_column_data'] ) && is_array( $data['user_column_data'] ) ) {
		foreach ( $data['user_column_data'] as $col ) {
			$id  = strtoupper( (string) ( $col['column_id'] ?? '' ) );
			$lbl = strtolower( (string) ( $col['column_name'] ?? '' ) );
			$val = trim( (string) ( $col['string_value'] ?? '' ) );
			if ( '' === $val ) {
				continue;
			}
			if ( 'FULL_NAME' === $id || strpos( $lbl, 'nom' ) !== false || strpos( $lbl, 'name' ) !== false ) {
				$name = $val;
			} elseif ( 'EMAIL' === $id || strpos( $lbl, 'mail' ) !== false ) {
				$email = $val;
			} elseif ( 'PHONE_NUMBER' === $id || strpos( $lbl, 'phone' ) !== false || strpos( $lbl, 'tél' ) !== false || strpos( $lbl, 'tel' ) !== false ) {
				$phone = $val;
			} else {
				$extra[] = ( $col['column_name'] ?? $id ) . ' : ' . $val;
			}
		}
	}

	$name  = sanitize_text_field( $name );
	$email  = sanitize_email( $email );
	$phone = sanitize_text_field( $phone );
	if ( '' === $name && '' === $email && '' === $phone ) {
		return new WP_REST_Response( array( 'status' => 'empty' ), 200 );
	}

	$notes = 'Lead Google Ads.';
	if ( $extra ) {
		$notes .= ' ' . implode( ' · ', array_map( 'sanitize_text_field', $extra ) );
	}
	if ( ! empty( $data['campaign_id'] ) ) {
		$notes .= ' (campagne ' . preg_replace( '/[^0-9]/', '', (string) $data['campaign_id'] ) . ')';
	}

	// 4) Création du prospect dans le CRM.
	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'   => $name ?: ( $email ?: $phone ),
			'email'  => $email,
			'phone'  => $phone,
			'status' => 'interesse',
			'source' => 'lead-google-ads',
			'notes'  => $notes,
		) );
	}

	// 5) Notification interne (à chaud).
	if ( function_exists( 'ag_push' ) ) {
		ag_push( '🟢 Lead Google Ads', trim( sprintf( '%s %s %s', $name, $email, $phone ) ) . ( $extra ? ' — ' . implode( ' · ', $extra ) : '' ) );
	}

	return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
}

/* ── Page de réglages (clé + URL webhook) ───────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'options-general.php',
		'Leads Google Ads',
		'Leads Google Ads',
		'manage_options',
		'ag-google-lead',
		'ag_glead_settings_page'
	);
} );

function ag_glead_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['ag_glead_regen'] ) && check_admin_referer( 'ag_glead' ) ) {
		update_option( 'ag_google_lead_key', wp_generate_password( 24, false, false ) );
		echo '<div class="notice notice-success"><p>Nouvelle clé générée. Mets-la à jour dans Google Ads.</p></div>';
	}
	$key = ag_glead_key();
	$url = ag_glead_webhook_url();
	echo '<div class="wrap"><h1>Leads Google Ads → CRM</h1>';
	echo '<p>Dans Google Ads, sur ton <strong>formulaire de lead</strong> → option <strong>« Webhook »</strong>, colle ces 2 valeurs. Chaque lead arrivera automatiquement dans <strong>Prospection</strong> (+ notification).</p>';
	echo '<table class="form-table"><tbody>';
	echo '<tr><th>URL du webhook</th><td><input type="text" class="large-text code" readonly onclick="this.select()" value="' . esc_attr( $url ) . '"></td></tr>';
	echo '<tr><th>Clé (Key)</th><td><input type="text" class="regular-text code" readonly onclick="this.select()" value="' . esc_attr( $key ) . '"></td></tr>';
	echo '</tbody></table>';
	echo '<form method="post">';
	wp_nonce_field( 'ag_glead' );
	echo '<p><button class="button" name="ag_glead_regen" value="1" onclick="return confirm(\'Régénérer la clé ? Il faudra la recoller dans Google Ads.\');">Régénérer la clé</button></p>';
	echo '</form>';
	echo '<p class="description">Astuce : utilise le bouton « Envoyer les données de test » de Google Ads pour vérifier que le webhook répond (il renverra un statut OK sans créer de prospect).</p>';
	echo '</div>';
}
