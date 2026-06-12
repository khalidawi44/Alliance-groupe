<?php
/**
 * AG Passerelle SMS — envoi de SMS vers N'IMPORTE QUEL numéro (candidats,
 * prospects…) via un téléphone Android + SIM (ex. Free) servant de passerelle.
 *
 * `ag_sms_send( $to, $msg )` : envoie 1 SMS. 3 modes au choix (Réglages) :
 *   - httpSMS (httpsms.com)        : header x-api-key + from + to.
 *   - android-sms-gateway (sms-gate.app) : Basic auth user:pass.
 *   - webhook générique            : URL avec {to} et {msg} (Macrodroid/Tasker…).
 *
 * Diffère de `ag_sms()` (qui n'alerte QUE ta propre ligne via l'API Free).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_sms_to_e164' ) ) {
	/** Normalise un numéro au format international (+33… par défaut FR). */
	function ag_sms_to_e164( $num ) {
		$d = trim( (string) $num );
		if ( '' === $d ) return '';
		if ( '+' === substr( $d, 0, 1 ) ) return '+' . preg_replace( '/[^0-9]/', '', $d );
		$d = preg_replace( '/[^0-9]/', '', $d );
		if ( '' === $d ) return '';
		if ( '00' === substr( $d, 0, 2 ) ) return '+' . substr( $d, 2 );
		if ( 10 === strlen( $d ) && '0' === $d[0] ) return '+33' . substr( $d, 1 ); // FR 0X…
		if ( 9 === strlen( $d ) ) return '+33' . $d;
		return '+' . $d;
	}
}
if ( ! function_exists( 'ag_sms_gateway_ready' ) ) {
	function ag_sms_gateway_ready() {
		$mode = get_option( 'ag_smsgw_mode', '' );
		if ( 'httpsms' === $mode ) return get_option( 'ag_smsgw_apikey', '' ) && get_option( 'ag_smsgw_from', '' );
		if ( 'smsgate' === $mode ) return get_option( 'ag_smsgw_user', '' ) && get_option( 'ag_smsgw_pass', '' );
		if ( 'webhook' === $mode ) return (bool) get_option( 'ag_smsgw_webhook', '' );
		return false;
	}
}
if ( ! function_exists( 'ag_sms_send' ) ) {
	/** Envoie un SMS à $to. Retourne true/false. */
	function ag_sms_send( $to, $msg ) {
		$to  = ag_sms_to_e164( $to );
		$msg = trim( wp_strip_all_tags( (string) $msg ) );
		if ( '' === $to || '' === $msg ) return false;
		$mode = get_option( 'ag_smsgw_mode', '' );

		if ( 'httpsms' === $mode ) {
			$key  = trim( (string) get_option( 'ag_smsgw_apikey', '' ) );
			$from = ag_sms_to_e164( get_option( 'ag_smsgw_from', '' ) );
			if ( '' === $key || '' === $from ) return false;
			$r = wp_remote_post( 'https://api.httpsms.com/v1/messages/send', array(
				'timeout' => 20,
				'headers' => array( 'x-api-key' => $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'content' => $msg, 'from' => $from, 'to' => $to ) ),
			) );
			return ! is_wp_error( $r ) && in_array( (int) wp_remote_retrieve_response_code( $r ), array( 200, 201, 202 ), true );
		}
		if ( 'smsgate' === $mode ) {
			$u    = trim( (string) get_option( 'ag_smsgw_user', '' ) );
			$p    = trim( (string) get_option( 'ag_smsgw_pass', '' ) );
			$base = trim( (string) get_option( 'ag_smsgw_base', 'https://api.sms-gate.app/3rdparty/v1' ) );
			if ( '' === $u || '' === $p ) return false;
			$r = wp_remote_post( rtrim( $base, '/' ) . '/message', array(
				'timeout' => 20,
				'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $u . ':' . $p ), 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'message' => $msg, 'phoneNumbers' => array( $to ) ) ),
			) );
			return ! is_wp_error( $r ) && in_array( (int) wp_remote_retrieve_response_code( $r ), array( 200, 201, 202 ), true );
		}
		if ( 'webhook' === $mode ) {
			$url = trim( (string) get_option( 'ag_smsgw_webhook', '' ) );
			if ( '' === $url ) return false;
			$url = str_replace( array( '{to}', '{msg}' ), array( rawurlencode( $to ), rawurlencode( $msg ) ), $url );
			$r   = wp_remote_get( $url, array( 'timeout' => 20 ) );
			return ! is_wp_error( $r ) && (int) wp_remote_retrieve_response_code( $r ) < 300;
		}
		return false;
	}
}
if ( ! function_exists( 'ag_sms_send_bulk' ) ) {
	/** Envoie un SMS à une liste de numéros. Retourne [envoyés, échecs]. */
	function ag_sms_send_bulk( $pairs ) {
		$ok = 0; $ko = 0;
		foreach ( $pairs as $pair ) {
			$to  = $pair['to'] ?? '';
			$msg = $pair['msg'] ?? '';
			if ( '' === $to || '' === $msg ) { $ko++; continue; }
			if ( ag_sms_send( $to, $msg ) ) $ok++; else $ko++;
			usleep( 200000 ); // 0,2 s entre 2 envois (anti-spam passerelle).
		}
		return array( $ok, $ko );
	}
}

/* ---------------------------------------------------------------- Réglages */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_smsgw_save'] ) && check_admin_referer( 'ag_smsgw' ) ) {
		foreach ( array( 'ag_smsgw_mode', 'ag_smsgw_apikey', 'ag_smsgw_from', 'ag_smsgw_user', 'ag_smsgw_pass', 'ag_smsgw_base', 'ag_smsgw_webhook' ) as $opt ) {
			if ( isset( $_POST[ $opt ] ) ) update_option( $opt, sanitize_text_field( wp_unslash( $_POST[ $opt ] ) ) );
		}
	}
	if ( isset( $_POST['ag_smsgw_test'] ) && check_admin_referer( 'ag_smsgw' ) ) {
		$to = sanitize_text_field( wp_unslash( $_POST['ag_smsgw_test_to'] ?? '' ) );
		$ok = ag_sms_send( $to, 'Test passerelle SMS Alliance Groupe ✅' );
		add_settings_error( 'ag_smsgw', 'test', $ok ? 'SMS de test envoyé ✅' : 'Échec de l’envoi — vérifie la config et que le téléphone passerelle est en ligne.', $ok ? 'updated' : 'error' );
	}
} );
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Passerelle SMS', '📲 Passerelle SMS', 'manage_options', 'ag-sms-gateway', 'ag_smsgw_render' );
}, 25 );
if ( ! function_exists( 'ag_smsgw_render' ) ) {
	function ag_smsgw_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$mode = get_option( 'ag_smsgw_mode', '' );
		settings_errors( 'ag_smsgw' );
		echo '<div class="wrap"><h1>📲 Passerelle SMS (envoi vers tout numéro)</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Branche un <strong>téléphone Android + une SIM</strong> (ex. Free) comme passerelle pour envoyer des SMS aux candidats et prospects, automatiquement et en masse. Installe une appli passerelle sur le tél, puis renseigne la config ci-dessous. <em>Respecte la loi : démarchage B2B autorisé (opt-out), pas de SMS de sollicitation aux particuliers (Bloctel) ; ajoute « STOP » dans tes messages.</em></p>';
		echo '<form method="post">';
		wp_nonce_field( 'ag_smsgw' );
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>Mode</th><td><select name="ag_smsgw_mode">';
		foreach ( array( '' => '— désactivé —', 'httpsms' => 'httpSMS (httpsms.com)', 'smsgate' => 'android-sms-gateway (sms-gate.app)', 'webhook' => 'Webhook générique (Macrodroid/Tasker)' ) as $k => $lab ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $mode, $k, false ) . '>' . esc_html( $lab ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>httpSMS — Clé API</th><td><input type="text" name="ag_smsgw_apikey" value="' . esc_attr( get_option( 'ag_smsgw_apikey', '' ) ) . '" style="width:420px"><p class="description">httpsms.com → Settings → API Keys.</p></td></tr>';
		echo '<tr><th>httpSMS — Numéro émetteur</th><td><input type="text" name="ag_smsgw_from" value="' . esc_attr( get_option( 'ag_smsgw_from', '' ) ) . '" placeholder="+336…" style="width:240px"></td></tr>';
		echo '<tr><th>sms-gate — Utilisateur</th><td><input type="text" name="ag_smsgw_user" value="' . esc_attr( get_option( 'ag_smsgw_user', '' ) ) . '" style="width:240px"></td></tr>';
		echo '<tr><th>sms-gate — Mot de passe</th><td><input type="password" name="ag_smsgw_pass" value="' . esc_attr( get_option( 'ag_smsgw_pass', '' ) ) . '" style="width:240px"></td></tr>';
		echo '<tr><th>sms-gate — URL API (optionnel)</th><td><input type="text" name="ag_smsgw_base" value="' . esc_attr( get_option( 'ag_smsgw_base', 'https://api.sms-gate.app/3rdparty/v1' ) ) . '" style="width:420px"><p class="description">Laisse par défaut pour le mode Cloud ; mets l’IP locale du tél pour le mode local.</p></td></tr>';
		echo '<tr><th>Webhook — URL</th><td><input type="text" name="ag_smsgw_webhook" value="' . esc_attr( get_option( 'ag_smsgw_webhook', '' ) ) . '" style="width:520px" placeholder="https://…/send?to={to}&text={msg}"><p class="description">Utilise <code>{to}</code> et <code>{msg}</code>.</p></td></tr>';
		echo '</tbody></table>';
		echo '<button name="ag_smsgw_save" value="1" class="button button-primary">Enregistrer</button></form>';
		echo '<hr><h2>Tester</h2><form method="post">';
		wp_nonce_field( 'ag_smsgw' );
		echo '<input type="text" name="ag_smsgw_test_to" placeholder="+336…" style="width:240px"> <button name="ag_smsgw_test" value="1" class="button">Envoyer un SMS de test</button>';
		echo '<p class="description">État : ' . ( ag_sms_gateway_ready() ? '🟢 configurée' : '🔴 non configurée' ) . '.</p></form>';
		echo '<hr><h3>Mise en place rapide (httpSMS)</h3><ol style="max-width:760px;color:#444;"><li>Installe l’appli <strong>httpSMS</strong> sur le téléphone Android (avec la SIM Free).</li><li>Connecte-toi, autorise l’envoi de SMS, récupère la <strong>clé API</strong> et le <strong>numéro</strong>.</li><li>Colle-les ci-dessus, mode « httpSMS », enregistre, puis « Envoyer un SMS de test ».</li><li>Laisse le téléphone allumé et connecté : il enverra les SMS demandés par le site.</li></ol>';
		echo '</div>';
	}
}
