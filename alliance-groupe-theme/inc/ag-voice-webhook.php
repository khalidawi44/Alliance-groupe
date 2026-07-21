<?php
/**
 * AG Robot vocal — webhook de compte-rendu d'appel.
 *
 * Reçoit le résultat de chaque appel d'un robot vocal IA (Retell / Bland / Vapi /
 * Vocode…) et met à jour le prospect correspondant dans le CRM, tout seul :
 *   - intéressé / rappel / devis   → statut « intéressé » (+ alerte)
 *   - ne plus appeler / opt-out    → « ne plus contacter » (+ opt-out SMS global)
 *   - pas intéressé / refus        → « refus »
 *   - répondeur / pas de réponse   → « sans réponse » (à relancer)
 *   - (défaut)                     → « contacté »
 *
 * Endpoint : POST /wp-json/ag/v1/voice?token=…   (jeton = option ag_voice_token)
 * Champs acceptés (souples, on prend le 1er non vide) :
 *   phone|to|from|number|customer_number  = le numéro appelé
 *   outcome|result|status|disposition|call_status = résultat brut
 *   summary|transcript|analysis|notes     = texte libre (analysé aussi par mots-clés)
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* --------------------------------------------------------------- Utilitaires */
if ( ! function_exists( 'ag_voice_deep_find' ) ) {
	/** Cherche RÉCURSIVEMENT la 1re valeur scalaire non vide pour l'une des clés (insensible à la casse),
	 *  quel que soit le niveau d'imbrication (Retell niche outcome dans call.call_analysis.custom_analysis_data…). */
	function ag_voice_deep_find( $node, $keys_lc, $depth = 0 ) {
		if ( $depth > 8 || ! is_array( $node ) ) return null;
		// 1) clés directes de ce niveau
		foreach ( $node as $key => $val ) {
			if ( is_array( $val ) ) continue;
			if ( '' === (string) $val ) continue;
			if ( in_array( strtolower( (string) $key ), $keys_lc, true ) ) return (string) $val;
		}
		// 2) descente récursive
		foreach ( $node as $val ) {
			if ( is_array( $val ) ) {
				$found = ag_voice_deep_find( $val, $keys_lc, $depth + 1 );
				if ( null !== $found && '' !== $found ) return $found;
			}
		}
		return null;
	}
}
if ( ! function_exists( 'ag_voice_pick' ) ) {
	/** 1re valeur non vide parmi des alias : params de requête d'abord, puis recherche profonde dans le JSON. */
	function ag_voice_pick( $req, $keys ) {
		// a) query string / form params (au 1er niveau).
		foreach ( $keys as $k ) {
			$v = $req->get_param( $k );
			if ( null !== $v && '' !== $v && ! is_array( $v ) ) return $v;
		}
		// b) recherche récursive dans le corps JSON (gère l'imbrication profonde de Retell/Vapi/Bland).
		$json    = $req->get_json_params();
		$keys_lc = array_map( 'strtolower', $keys );
		$found   = ag_voice_deep_find( is_array( $json ) ? $json : array(), $keys_lc );
		return ( null !== $found ) ? $found : '';
	}
}
if ( ! function_exists( 'ag_voice_map_outcome' ) ) {
	/** Normalise un résultat d'appel → array( statut_CRM, est_optout ). */
	function ag_voice_map_outcome( $raw, $summary ) {
		$s = ' ' . strtolower( (string) $raw . ' ' . (string) $summary ) . ' ';
		if ( preg_match( '/(do.?not.?call|ne plus (l\')?(appel|rappel|contact)|listes? rouge|opt.?out|retire|desinscri|stop demarchage)/u', $s ) ) return array( 'ne_pas_contacter', true );
		if ( preg_match( '/(interes|intéress|rappel|call.?back|rendez|\brdv\b|devis|\boui\b|positif|positive|chaud|\bhot\b|interested)/u', $s ) ) return array( 'interesse', false );
		if ( preg_match( '/(voicemail|repondeur|répondeur|no.?answer|pas de repon|sans repon|occup|\bbusy\b|failed|echec|échec|non abouti|injoign)/u', $s ) ) return array( 'sans_reponse', false );
		if ( preg_match( '/(not.?interes|pas interes|refus|declin|déclin|negative|négatif|not_interested|rejected)/u', $s ) ) return array( 'refus', false );
		return array( 'contacte', false );
	}
}
if ( ! function_exists( 'ag_voice_status_label' ) ) {
	function ag_voice_status_label( $st ) {
		$m = array( 'interesse' => '🔥 Intéressé', 'ne_pas_contacter' => '⛔ Ne plus contacter', 'refus' => '🚫 Pas intéressé', 'sans_reponse' => '🔇 Sans réponse', 'contacte' => '📞 Contacté' );
		return $m[ $st ] ?? $st;
	}
}

/* -------------------------------------------------------- Route REST /voice */
add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/voice', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ag_voice_rest',
	) );
} );
if ( ! function_exists( 'ag_voice_rest' ) ) {
	function ag_voice_rest( $req ) {
		// Auth par jeton.
		$token = get_option( 'ag_voice_token', '' );
		$given = $req->get_param( 'token' );
		if ( '' === $given ) $given = $req->get_header( 'x-ag-token' );
		if ( '' === $given ) $given = ag_voice_pick( $req, array( 'token' ) );
		if ( '' === $token || ! hash_equals( (string) $token, (string) $given ) ) {
			return new WP_REST_Response( array( 'error' => 'unauthorized' ), 401 );
		}
		// Ne traiter QUE l'événement final (analyse dispo). Retell/Vapi envoient aussi
		// « call_started » / « call_ended » sans résultat → on les ignore (pas de fausse alerte).
		$event = strtolower( (string) ag_voice_pick( $req, array( 'event', 'event_type', 'type' ) ) );
		if ( '' !== $event && false !== strpos( $event, 'start' ) ) {
			return new WP_REST_Response( array( 'ignored' => 'call_started' ), 200 );
		}
		$phone   = sanitize_text_field( (string) ag_voice_pick( $req, array( 'phone', 'to', 'from', 'number', 'customer_number', 'to_number', 'called', 'contact' ) ) );
		$outcome = sanitize_text_field( (string) ag_voice_pick( $req, array( 'outcome', 'result', 'disposition', 'call_status', 'user_sentiment', 'call_successful' ) ) );
		$summary = sanitize_textarea_field( (string) ag_voice_pick( $req, array( 'summary', 'call_summary', 'transcript', 'analysis', 'notes', 'reason' ) ) );
		if ( '' === $phone ) return new WP_REST_Response( array( 'error' => 'no_phone' ), 400 );
		// Aucun signal exploitable (ni résultat ni résumé) → on n'invente pas de statut.
		if ( '' === $outcome && '' === $summary ) return new WP_REST_Response( array( 'ignored' => 'no_signal' ), 200 );

		list( $status, $optout ) = ag_voice_map_outcome( $outcome, $summary );
		$stamp = current_time( 'd/m/Y H:i' );
		$note  = '📞 Robot vocal (' . $stamp . ') → ' . ag_voice_status_label( $status ) . ( '' !== $outcome ? ' [' . $outcome . ']' : '' ) . ( '' !== $summary ? ' : ' . $summary : '' );

		if ( $optout && function_exists( 'ag_sms_add_optout' ) ) ag_sms_add_optout( $phone ); // opt-out global (SMS + prospect)
		$nm = function_exists( 'ag_prospect_set_status_by_phone' ) ? ag_prospect_set_status_by_phone( $phone, $status, $note, $optout ) : '';

		// Alerte (surtout si intéressé).
		$who = ( '' !== $nm ) ? $nm : $phone;
		$tag = ( 'interesse' === $status ) ? '🔥 Robot vocal : prospect INTÉRESSÉ' : '📞 Robot vocal : ' . ag_voice_status_label( $status );
		if ( 'interesse' === $status || $optout ) {
			if ( function_exists( 'ag_sms' ) )  ag_sms( $tag . ' : ' . $who . ' — ' . $phone );
			if ( function_exists( 'ag_push' ) ) ag_push( $tag, $who . "\n📞 " . $phone . ( '' !== $summary ? "\n💬 " . $summary : '' ) );
		}
		// Prospect INTÉRESSÉ → évènement Google Agenda + rappel pop-up (à rappeler).
		if ( 'interesse' === $status && function_exists( 'ag_calendar_notify' ) ) {
			ag_calendar_notify( '🔥 Rappeler (robot) : ' . $who, "Prospect intéressé suite à l'appel du robot vocal.\n📞 " . $phone . ( '' !== $summary ? "\n💬 " . $summary : '' ) . "\nÀ rappeler vite." );
		}
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( $tag . ' : ' . $who );

		return new WP_REST_Response( array( 'ok' => true, 'matched' => ( '' !== $nm ), 'status' => $status ), 200 );
	}
}

/* ------------------------------------------------- Lancer un appel via Retell */
if ( ! function_exists( 'ag_voice_ready' ) ) {
	/** L'appel sortant est-il configurable ? (clé API Retell + numéro émetteur). */
	function ag_voice_ready() {
		return '' !== trim( (string) get_option( 'ag_voice_api_key', '' ) ) && '' !== trim( (string) get_option( 'ag_voice_from', '' ) );
	}
}
if ( ! function_exists( 'ag_voice_call' ) ) {
	/**
	 * Déclenche un appel sortant du robot vocal (Retell) vers $to, en injectant des
	 * variables dynamiques ({{entreprise}}, {{ville}}, {{angle}}=creation|securite) pour
	 * qu'Emma adapte son discours. Respecte l'opt-out. Retourne true si l'appel est lancé.
	 */
	function ag_voice_call( $to, $vars = array() ) {
		$key  = trim( (string) get_option( 'ag_voice_api_key', '' ) );
		$from = trim( (string) get_option( 'ag_voice_from', '' ) );
		$to   = function_exists( 'ag_sms_to_e164' ) ? ag_sms_to_e164( $to ) : $to;
		if ( '' === $key || '' === $from || '' === $to ) return false;
		if ( function_exists( 'ag_sms_is_optout' ) && ag_sms_is_optout( $to ) ) return false; // jamais un opt-out
		$body = array( 'from_number' => $from, 'to_number' => $to );
		if ( ! empty( $vars ) ) $body['retell_llm_dynamic_variables'] = array_map( 'strval', $vars );
		$r = wp_remote_post( 'https://api.retellai.com/v2/create-phone-call', array(
			'timeout' => 20,
			'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		) );
		return ! is_wp_error( $r ) && in_array( (int) wp_remote_retrieve_response_code( $r ), array( 200, 201 ), true );
	}
}
/* Appels groupés depuis la liste des prospects (sélection). */
add_action( 'wp_ajax_ag_prospect_voice_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	if ( ! function_exists( 'ag_voice_call' ) || ! ag_voice_ready() ) wp_send_json_error( array( 'msg' => 'robot vocal non configuré' ) );
	$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$ids = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $ids ) ) );
	if ( empty( $ids ) ) wp_send_json_error();
	$ok = 0; $ko = 0;
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
		if ( ! in_array( $p['id'] ?? '', $ids, true ) ) continue;
		if ( function_exists( 'ag_prospect_blocked' ) && ag_prospect_blocked( $p['status'] ?? '' ) ) continue; // jamais aux bloqués
		$to = $p['phone_intl'] ?? ''; if ( '' === $to ) $to = $p['phone'] ?? '';
		if ( '' === $to ) continue;
		$has_site = function_exists( 'ag_site_kind' ) && 'real' === ag_site_kind( $p['website'] ?? '' )[0];
		$vars = array(
			'entreprise' => $p['name'] ?? '',
			'ville'      => $p['city'] ?? '',
			'angle'      => $has_site ? 'securite' : 'creation',
		);
		if ( ag_voice_call( $to, $vars ) ) $ok++; else $ko++;
		usleep( 300000 ); // 0,3 s entre 2 lancements
	}
	if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🤖 Robot vocal : ' . $ok . ' appel(s) lancé(s), ' . $ko . ' échec(s)' );
	wp_send_json_success( array( 'ok' => $ok, 'ko' => $ko ) );
} );

/* ----------------------------------------------------- Réglages POST */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_voice_save'] ) && check_admin_referer( 'ag_voice' ) ) {
		if ( isset( $_POST['ag_voice_api_key'] ) ) update_option( 'ag_voice_api_key', sanitize_text_field( wp_unslash( $_POST['ag_voice_api_key'] ) ) );
		if ( isset( $_POST['ag_voice_from'] ) )    update_option( 'ag_voice_from', sanitize_text_field( wp_unslash( $_POST['ag_voice_from'] ) ) );
		if ( isset( $_POST['ag_voice_regen'] ) || '' === get_option( 'ag_voice_token', '' ) ) {
			update_option( 'ag_voice_token', wp_generate_password( 24, false ) );
		}
	}
	// Appel de test vers un numéro libre (ex. ton mobile perso).
	if ( isset( $_POST['ag_voice_test'] ) && check_admin_referer( 'ag_voice' ) ) {
		$to = sanitize_text_field( wp_unslash( $_POST['ag_voice_test_to'] ?? '' ) );
		$ok = function_exists( 'ag_voice_call' ) && ag_voice_ready() && ag_voice_call( $to, array( 'entreprise' => 'votre établissement', 'angle' => 'creation' ) );
		add_settings_error( 'ag_voice', 'test', $ok ? '📞 Appel lancé vers ' . esc_html( $to ) . ' — décroche !' : 'Échec : vérifie la clé API + le numéro émetteur, et le format du numéro (+33…).', $ok ? 'updated' : 'error' );
	}
} );

/* ------------------------------------------------------------- Page d'admin */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Robot vocal IA', '🤖 Robot vocal', 'manage_options', 'ag-voice', 'ag_voice_render' );
}, 20 );
if ( ! function_exists( 'ag_voice_render' ) ) {
	function ag_voice_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$tok = get_option( 'ag_voice_token', '' );
		$url = rest_url( 'ag/v1/voice' );
		echo '<div class="wrap"><h1>🤖 Robot vocal IA — compte-rendu d\'appel</h1>';
		settings_errors( 'ag_voice' );
		echo '<p style="max-width:820px;color:#50575e;">Branche un robot vocal IA (Retell, Bland, Vapi, Vocode…) qui appelle les <strong>fixes 02/04</strong> (là où le SMS ne passe pas). À la fin de chaque appel, le robot envoie son compte-rendu à l\'URL ci-dessous → le <strong>prospect est mis à jour tout seul</strong> dans ton CRM.</p>';

		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">1. URL du webhook (à coller dans ton outil vocal)</h2>';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>URL (méthode POST)</th><td><input type="text" readonly value="' . esc_attr( add_query_arg( 'token', $tok ?: 'GENERE-LE-CI-DESSOUS', $url ) ) . '" style="width:640px" onclick="this.select()"></td></tr>';
		echo '<tr><th>Jeton de sécurité</th><td><code style="font-size:13px;">' . esc_html( $tok ?: '(génère-le en enregistrant)' ) . '</code>';
		echo '<form method="post" style="display:inline;margin-left:10px;">' . wp_nonce_field( 'ag_voice', '_wpnonce', true, false );
		echo '<label><input type="checkbox" name="ag_voice_regen" value="1"> régénérer</label> <button name="ag_voice_save" value="1" class="button button-small">Enregistrer</button></form></td></tr>';
		echo '</tbody></table>';
		echo '<p class="description">Champs attendus (souples) : <code>phone</code> (numéro appelé), <code>outcome</code> (résultat), <code>summary</code> (résumé/transcript). Le jeton passe en <code>?token=…</code> ou header <code>X-AG-Token</code>.</p>';
		echo '</div>';

		// ── Lancer des appels depuis WordPress (API Retell) ──
		$api  = get_option( 'ag_voice_api_key', '' );
		$from = get_option( 'ag_voice_from', '' );
		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">1 bis. Lancer les appels depuis la liste des prospects</h2>';
		echo '<p style="color:#50575e;">Renseigne ta <strong>clé API Retell</strong> et ton <strong>numéro émetteur</strong> → le bouton « 📞 Appeler au robot (sélection) » apparaît dans <strong>Prospection</strong>. Emma <strong>adapte son discours</strong> : prospect sans site → <em>création</em> ; avec site → <em>audit sécurité</em> (variable <code>{{angle}}</code>).</p>';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo wp_nonce_field( 'ag_voice', '_wpnonce', true, false );
		echo '<tr><th>Clé API Retell</th><td><input type="text" name="ag_voice_api_key" value="' . esc_attr( $api ) . '" style="width:420px" placeholder="key_..."><p class="description">Retell → Paramètres → API Keys.</p></td></tr>';
		echo '<tr><th>Numéro émetteur (le numéro Retell)</th><td><input type="text" name="ag_voice_from" value="' . esc_attr( $from ) . '" style="width:240px" placeholder="+14632982363"><p class="description">Le numéro acheté dans Retell, attaché à l\'agent Emma.</p></td></tr>';
		echo '</tbody></table><button name="ag_voice_save" value="1" class="button button-primary">Enregistrer</button> ';
		echo '<span style="margin-left:10px;">État : ' . ( ag_voice_ready() ? '🟢 prêt à appeler' : '🔴 à configurer' ) . '</span></form>';
		// Appel de test vers un numéro libre (mobile perso).
		echo '<hr style="margin:16px 0;"><h3 style="margin:0 0 6px;">📞 Tester un appel</h3>';
		echo '<p class="description" style="margin:0 0 8px;">Emma appelle le numéro que tu indiques (ton mobile perso par ex.) — pour tester la voix en conditions réelles.</p>';
		echo '<form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">' . wp_nonce_field( 'ag_voice', '_wpnonce', true, false );
		echo '<input type="text" name="ag_voice_test_to" placeholder="+33612345678" style="width:220px"' . ( ag_voice_ready() ? '' : ' disabled' ) . '>';
		echo '<button name="ag_voice_test" value="1" class="button"' . ( ag_voice_ready() ? '' : ' disabled title="Configure d\'abord la clé API + le numéro"' ) . '>Lancer l\'appel de test</button>';
		echo '<span class="description">Format international : <code>+33…</code> (remplace le 0 par +33).</span></form>';
		echo '</div>';

		// ── Liste opt-out (STOP / ne plus contacter) : voir + retirer ──
		$optout = (array) get_option( 'ag_sms_optout', array() );
		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">📵 Liste « ne plus contacter » (opt-out)</h2>';
		echo '<p style="color:#50575e;">Numéros qui ont dit STOP (ou marqués « ne plus appeler ») : ils ne reçoivent <strong>plus aucun SMS ni appel</strong>. Tu peux en <strong>retirer un</strong> (ex. ton propre numéro ajouté pendant les tests).</p>';
		if ( empty( $optout ) ) {
			echo '<p style="color:#50575e;"><em>Aucun numéro en opt-out pour l\'instant.</em></p>';
		} else {
			echo '<table class="widefat striped" style="max-width:520px;"><thead><tr><th>Numéro</th><th style="width:120px;"></th></tr></thead><tbody>';
			foreach ( $optout as $k ) {
				$disp = ( 0 === strpos( $k, '33' ) ) ? '+' . $k : $k;
				echo '<tr><td><code>' . esc_html( $disp ) . '</code></td><td>';
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0;">';
				echo '<input type="hidden" name="action" value="ag_optout_remove"><input type="hidden" name="_n" value="' . esc_attr( wp_create_nonce( 'ag_optout' ) ) . '"><input type="hidden" name="num" value="' . esc_attr( $disp ) . '">';
				echo '<button class="button button-small" onclick="return confirm(\'Autoriser à nouveau le contact de ' . esc_attr( $disp ) . ' ?\')">✅ Retirer</button></form>';
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}
		echo '<p class="description" style="margin-top:8px;">💡 Retirer d\'ici n\'annule pas le statut « ne plus contacter » d\'un prospect dans la liste : pour le réactiver, change aussi son statut dans <strong>Prospection</strong>.</p>';
		echo '</div>';

		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">2. Comment le résultat met à jour le prospect</h2>';
		echo '<table class="widefat striped"><thead><tr><th>Le robot renvoie…</th><th>Le prospect passe en…</th></tr></thead><tbody>';
		echo '<tr><td>intéressé / rappel / devis / RDV</td><td><strong>🔥 Intéressé</strong> + alerte SMS/Telegram</td></tr>';
		echo '<tr><td>ne plus appeler / opt-out</td><td><strong>⛔ Ne plus contacter</strong> + opt-out global (SMS aussi)</td></tr>';
		echo '<tr><td>pas intéressé / refus</td><td>🚫 Pas intéressé</td></tr>';
		echo '<tr><td>répondeur / pas de réponse / occupé</td><td>🔇 Sans réponse (à relancer)</td></tr>';
		echo '<tr><td>(autre)</td><td>📞 Contacté</td></tr>';
		echo '</tbody></table>';
		echo '<p class="description">La correspondance se fait sur les <strong>9 derniers chiffres</strong> du numéro (tolère +33 vs 0). Un prospect « client » n\'est jamais modifié.</p>';
		echo '</div>';

		echo '<div style="max-width:900px;padding:14px 18px;background:#fff8e5;border:1px solid #f0c36d;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">⚠️ Obligations légales (à respecter dans le script du robot)</h2>';
		echo '<ul style="margin:0;line-height:1.7;">';
		echo '<li><strong>Transparence IA</strong> : la voix doit annoncer dès le début qu\'elle est un <strong>assistant vocal automatique</strong>.</li>';
		echo '<li><strong>Numéro dédié démarchage</strong> (01 62/63, 09 48/49…) via un opérateur VoIP (OVH, Ringover…) — pas un mobile perso.</li>';
		echo '<li><strong>Opt-out</strong> : si l\'interlocuteur dit « ne me rappelez plus », le robot doit le marquer → « ne plus contacter » (auto ici).</li>';
		echo '<li>Cibler des <strong>professionnels</strong> (B2B) ; horaires légaux ; pas de harcèlement.</li>';
		echo '</ul></div>';
		echo '</div>';
	}
}
