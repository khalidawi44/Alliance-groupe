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
if ( ! function_exists( 'ag_sms_optout_key' ) ) {
	/** Clé de comparaison d'un numéro (chiffres du format international). */
	function ag_sms_optout_key( $num ) { return preg_replace( '/[^0-9]/', '', (string) ag_sms_to_e164( $num ) ); }
}
if ( ! function_exists( 'ag_sms_is_optout' ) ) {
	/** Ce numéro a-t-il demandé STOP ? (liste globale `ag_sms_optout`). */
	function ag_sms_is_optout( $num ) {
		$k = ag_sms_optout_key( $num );
		return '' !== $k && in_array( $k, (array) get_option( 'ag_sms_optout', array() ), true );
	}
}
if ( ! function_exists( 'ag_sms_add_optout' ) ) {
	/** Enregistre un numéro en opt-out (STOP) : plus aucun SMS ne lui sera envoyé,
	 *  et le prospect correspondant passe en « ne plus contacter ». */
	function ag_sms_add_optout( $num ) {
		$k = ag_sms_optout_key( $num );
		if ( '' === $k ) return;
		$l = (array) get_option( 'ag_sms_optout', array() );
		if ( ! in_array( $k, $l, true ) ) { $l[] = $k; update_option( 'ag_sms_optout', $l, false ); }
		if ( function_exists( 'ag_prospect_mark_optout_by_phone' ) ) ag_prospect_mark_optout_by_phone( $num );
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '⛔ STOP reçu : ' . $num . ' ajouté à la liste opt-out (ne plus contacter).' );
	}
}
if ( ! function_exists( 'ag_sms_send' ) ) {
	/** Envoie un SMS à $to. Retourne true/false. */
	function ag_sms_send( $to, $msg ) {
		$to  = ag_sms_to_e164( $to );
		$msg = trim( wp_strip_all_tags( (string) $msg ) );
		if ( '' === $to || '' === $msg ) return false;
		if ( ag_sms_is_optout( $to ) ) return false; // respecte le STOP : on ne renvoie JAMAIS à un opt-out.
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
	if ( isset( $_POST['ag_inbound_save'] ) && check_admin_referer( 'ag_smsgw' ) ) {
		update_option( 'ag_inbound_keyword', sanitize_text_field( wp_unslash( $_POST['ag_inbound_keyword'] ?? '' ) ) );
		update_option( 'ag_inbound_calls', isset( $_POST['ag_inbound_calls'] ) ? '1' : '0' );
		if ( isset( $_POST['ag_inbound_regen'] ) || '' === get_option( 'ag_inbound_token', '' ) ) {
			update_option( 'ag_inbound_token', wp_generate_password( 24, false ) );
		}
	}
} );

/* ---------------------------------------------------------------- Webhook d'ENTRÉE
 * Le téléphone passerelle (httpSMS / Macrodroid / Tasker) appelle cette URL quand
 * il reçoit un SMS ou un appel → on crée la candidature + on renvoie le SMS
 * d'inscription automatiquement. Sécurisé par un jeton.
 */
if ( ! function_exists( 'ag_inbound_throttle_ok' ) ) {
	/**
	 * Garde-fou anti-spam : autorise au plus N nouvelles candidatures par heure
	 * (glissante). Empêche un flood depuis plein de numéros différents de créer des
	 * dizaines de candidatures + autant de SMS de bienvenue (coût + risque de ban SIM).
	 * Plafond ajustable via le filtre `ag_inbound_hourly_cap` (0 = illimité).
	 */
	function ag_inbound_throttle_ok() {
		$cap = (int) apply_filters( 'ag_inbound_hourly_cap', 15 );
		if ( $cap <= 0 ) return true;
		$now  = time();
		$hits = array_values( array_filter(
			array_map( 'intval', (array) get_option( 'ag_inbound_hits', array() ) ),
			function ( $ts ) use ( $now ) { return ( $now - $ts ) < HOUR_IN_SECONDS; }
		) );
		if ( count( $hits ) >= $cap ) { update_option( 'ag_inbound_hits', $hits, false ); return false; }
		$hits[] = $now;
		update_option( 'ag_inbound_hits', $hits, false );
		return true;
	}
}
if ( ! function_exists( 'ag_inbound_find_phone' ) ) {
	function ag_inbound_find_phone( $phone ) {
		if ( ! function_exists( 'ag_cand_get' ) ) return null;
		$d = function_exists( 'ag_sms_to_e164' ) ? ag_sms_to_e164( $phone ) : preg_replace( '/[^0-9]/', '', $phone );
		foreach ( ag_cand_get() as $c ) {
			$cd = function_exists( 'ag_sms_to_e164' ) ? ag_sms_to_e164( $c['phone'] ?? '' ) : preg_replace( '/[^0-9]/', '', $c['phone'] ?? '' );
			if ( $cd && $cd === $d ) return $c;
		}
		return null;
	}
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/inbound', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ag_inbound_rest',
	) );
} );
if ( ! function_exists( 'ag_inbound_pick' ) ) {
	/** Récupère la 1re valeur non vide parmi plusieurs alias (params + JSON imbriqué). */
	function ag_inbound_pick( $req, $keys ) {
		$json = $req->get_json_params();
		$pools = array();
		if ( is_array( $json ) ) {
			$pools[] = $json;
			foreach ( array( 'data', 'message', 'payload', 'sms' ) as $nest ) {
				if ( isset( $json[ $nest ] ) && is_array( $json[ $nest ] ) ) $pools[] = $json[ $nest ];
			}
		}
		foreach ( $keys as $k ) {
			$v = $req->get_param( $k );
			if ( null !== $v && '' !== $v ) return $v;
			foreach ( $pools as $pool ) {
				if ( isset( $pool[ $k ] ) && '' !== $pool[ $k ] && ! is_array( $pool[ $k ] ) ) return $pool[ $k ];
			}
		}
		return '';
	}
}
if ( ! function_exists( 'ag_inbound_rest' ) ) {
	function ag_inbound_rest( $req ) {
		// Auth par jeton (query ?token= ou header X-AG-Token).
		$token = get_option( 'ag_inbound_token', '' );
		$given = $req->get_param( 'token' );
		if ( '' === $given ) $given = $req->get_header( 'x-ag-token' );
		if ( '' === $given ) $given = ag_inbound_pick( $req, array( 'token' ) );
		if ( '' === $token || ! hash_equals( (string) $token, (string) $given ) ) {
			return new WP_REST_Response( array( 'error' => 'unauthorized' ), 401 );
		}
		// Accepte les noms de champs des apps courantes (httpSMS, sms-gate, Macrodroid…).
		$from = sanitize_text_field( (string) ag_inbound_pick( $req, array( 'from', 'contact', 'phone', 'sender', 'number', 'phoneNumber', 'address' ) ) );
		$text = sanitize_textarea_field( (string) ag_inbound_pick( $req, array( 'text', 'content', 'message', 'body', 'msg' ) ) );
		$name = sanitize_text_field( (string) ag_inbound_pick( $req, array( 'name', 'contactName' ) ) );
		$type = strtolower( sanitize_text_field( (string) ag_inbound_pick( $req, array( 'type', 'event' ) ) ) ); // 'sms' | 'call'
		if ( false !== strpos( $type, 'call' ) ) $type = 'call';
		if ( '' === $from ) return new WP_REST_Response( array( 'error' => 'no_from' ), 400 );

		// Appels : seulement si activé.
		if ( 'call' === $type && '1' !== get_option( 'ag_inbound_calls', '1' ) ) {
			return new WP_REST_Response( array( 'ignored' => 'calls_off' ), 200 );
		}
		// STOP / opt-out (SMS) : la personne demande à ne plus être contactée → on l'enregistre
		// (liste globale + prospect en « ne plus contacter ») et on s'arrête. Conformité RGPD.
		if ( 'call' !== $type && preg_match( '/(^|\W)(STOP|ARRET|ARRÊT|DESABO|DÉSABO|UNSUB)(\W|$)/iu', $text ) ) {
			if ( function_exists( 'ag_sms_add_optout' ) ) ag_sms_add_optout( $from );
			return new WP_REST_Response( array( 'optout' => true ), 200 );
		}
		// Réponse d'un PROSPECT connu (SMS OU rappel) : ce n'est pas une candidature ambassadeur
		// → on met à jour le CRM (intéressé si positif), on journalise la réponse et on alerte.
		if ( function_exists( 'ag_prospect_register_reply' ) && ag_prospect_register_reply( $from, ( 'call' === $type ? '' : $text ), $type ) ) {
			return new WP_REST_Response( array( 'prospect_reply' => true ), 200 );
		}
		// Filtre mot-clé (SMS) : si défini, le SMS doit le contenir.
		$kw = trim( (string) get_option( 'ag_inbound_keyword', '' ) );
		if ( 'call' !== $type && '' !== $kw && false === mb_stripos( $text, $kw ) ) {
			return new WP_REST_Response( array( 'ignored' => 'no_keyword' ), 200 );
		}
		// Anti-doublon : déjà candidat → on ne recrée pas / ne re-spamme pas.
		if ( ag_inbound_find_phone( $from ) ) {
			return new WP_REST_Response( array( 'duplicate' => true ), 200 );
		}
		// Anti-spam : plafond de NOUVELLES candidatures par heure (flood depuis plein de
		// numéros différents → coûts d'envoi + risque de blocage de la SIM). Au-delà, on ignore.
		if ( function_exists( 'ag_inbound_throttle_ok' ) && ! ag_inbound_throttle_ok() ) {
			return new WP_REST_Response( array( 'throttled' => true ), 429 );
		}
		if ( ! function_exists( 'ag_cand_add' ) ) return new WP_REST_Response( array( 'error' => 'module' ), 500 );
		$src = ( 'call' === $type ) ? 'appel-entrant' : 'sms-entrant';
		$rec = ag_cand_add( array(
			'prenom'     => $name,
			'phone'      => $from,
			'motivation' => trim( ( 'call' === $type ? 'Appel entrant' : 'SMS entrant' ) . ( $text ? ' : ' . $text : '' ) ),
			'source'     => $src,
		) );
		return new WP_REST_Response( array( 'ok' => (bool) $rec ), 200 );
	}
}
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

		// ── Réception : SMS/appel entrant → candidature auto ──────────────
		$tok = get_option( 'ag_inbound_token', '' );
		$wh  = rest_url( 'ag/v1/inbound' );
		echo '<hr><h2>📥 Réception : un SMS ou un appel = candidature auto</h2>';
		echo '<p style="max-width:780px;color:#444;">Quand quelqu’un t’<strong>envoie un SMS</strong> ou t’<strong>appelle</strong> sur le téléphone passerelle, il est <strong>ajouté tout seul dans 🎯 Candidatures</strong> et reçoit un <strong>SMS automatique avec le lien d’inscription</strong>. (Nécessite la passerelle ci-dessus configurée + l’app du tél réglée pour transférer les SMS/appels entrants vers l’URL ci-dessous.)</p>';
		echo '<form method="post">';
		wp_nonce_field( 'ag_smsgw' );
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>URL du webhook (à mettre dans l’app)</th><td><input type="text" readonly value="' . esc_attr( $wh ) . '" style="width:520px" onclick="this.select()"><p class="description">Méthode POST. Champs attendus : <code>token</code>, <code>from</code> (numéro), <code>text</code> (corps du SMS), <code>type</code> = <code>sms</code> ou <code>call</code>, <code>name</code> (optionnel).</p></td></tr>';
		echo '<tr><th>Jeton de sécurité</th><td><code style="font-size:13px;">' . esc_html( $tok ?: '(génère-le en enregistrant)' ) . '</code> <label style="margin-left:10px;"><input type="checkbox" name="ag_inbound_regen" value="1"> régénérer</label><p class="description">À passer en <code>?token=…</code> ou header <code>X-AG-Token</code>.</p></td></tr>';
		echo '<tr><th>Mot-clé déclencheur (SMS, optionnel)</th><td><input type="text" name="ag_inbound_keyword" value="' . esc_attr( get_option( 'ag_inbound_keyword', '' ) ) . '" style="width:200px" placeholder="ex : AMBASSADEUR"><p class="description">Si rempli, seuls les SMS contenant ce mot créent une candidature (évite que chaque SMS personnel déclenche). Mets-le dans ton annonce : « envoie AMBASSADEUR au 07… ».</p></td></tr>';
		echo '<tr><th>Appels entrants</th><td><label><input type="checkbox" name="ag_inbound_calls" ' . checked( '1', get_option( 'ag_inbound_calls', '1' ), false ) . '> Créer une candidature aussi sur un appel/appel manqué</label></td></tr>';
		echo '</tbody></table>';
		echo '<button name="ag_inbound_save" value="1" class="button button-primary">Enregistrer la réception</button></form>';
		echo '<details style="max-width:780px;margin-top:10px;"><summary class="button button-small">Comment régler le téléphone (Macrodroid)</summary><ol style="color:#444;"><li>Installe <strong>Macrodroid</strong> (gratuit) sur le tél passerelle.</li><li>Macro 1 — Déclencheur « SMS reçu » → Action « Requête HTTP POST » vers l’URL ci-dessus, corps : <code>token=' . esc_html( $tok ) . '&from=[sms_number]&text=[sms_message]&type=sms</code>.</li><li>Macro 2 — Déclencheur « Appel manqué » (ou entrant) → POST : <code>token=' . esc_html( $tok ) . '&from=[call_number]&type=call</code>.</li><li>Le reste est automatique : candidature créée + SMS d’inscription renvoyé.</li></ol></details>';
		echo '<p style="color:#b26a00;font-size:.85em;max-width:780px;">⚠️ Idéalement, utilise une <strong>ligne dédiée au recrutement</strong> (pas ton mobile perso) — sinon mets un mot-clé déclencheur pour ne pas transformer chaque SMS reçu en candidature.</p>';
		echo '</div>';
	}
}
