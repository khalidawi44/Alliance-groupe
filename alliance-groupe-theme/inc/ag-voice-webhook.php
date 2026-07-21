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
if ( ! function_exists( 'ag_voice_pick' ) ) {
	/** 1re valeur non vide parmi des alias (params + JSON imbriqué). */
	function ag_voice_pick( $req, $keys ) {
		$json  = $req->get_json_params();
		$pools = array();
		if ( is_array( $json ) ) {
			$pools[] = $json;
			foreach ( array( 'data', 'call', 'message', 'payload', 'result', 'analysis', 'call_analysis' ) as $nest ) {
				if ( isset( $json[ $nest ] ) && is_array( $json[ $nest ] ) ) $pools[] = $json[ $nest ];
			}
		}
		foreach ( $keys as $k ) {
			$v = $req->get_param( $k );
			if ( null !== $v && '' !== $v && ! is_array( $v ) ) return $v;
			foreach ( $pools as $pool ) {
				if ( isset( $pool[ $k ] ) && '' !== $pool[ $k ] && ! is_array( $pool[ $k ] ) ) return $pool[ $k ];
			}
		}
		return '';
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
		$phone   = sanitize_text_field( (string) ag_voice_pick( $req, array( 'phone', 'to', 'from', 'number', 'customer_number', 'to_number', 'called', 'contact' ) ) );
		$outcome = sanitize_text_field( (string) ag_voice_pick( $req, array( 'outcome', 'result', 'disposition', 'status', 'call_status', 'user_sentiment', 'call_successful' ) ) );
		$summary = sanitize_textarea_field( (string) ag_voice_pick( $req, array( 'summary', 'call_summary', 'transcript', 'analysis', 'notes', 'reason' ) ) );
		if ( '' === $phone ) return new WP_REST_Response( array( 'error' => 'no_phone' ), 400 );

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
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( $tag . ' : ' . $who );

		return new WP_REST_Response( array( 'ok' => true, 'matched' => ( '' !== $nm ), 'status' => $status ), 200 );
	}
}

/* ----------------------------------------------------- Réglages (jeton) POST */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_voice_save'] ) && check_admin_referer( 'ag_voice' ) ) {
		if ( isset( $_POST['ag_voice_regen'] ) || '' === get_option( 'ag_voice_token', '' ) ) {
			update_option( 'ag_voice_token', wp_generate_password( 24, false ) );
		}
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
