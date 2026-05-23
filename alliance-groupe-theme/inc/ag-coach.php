<?php
/**
 * Alliance Groupe — Agent « Coach » (à règles, gratuit).
 *
 * Construit chaque jour la feuille de route de l'admin à partir des vraies
 * données (prospects, leads, ventes, briefs, relances) et la pousse sur le
 * téléphone (Telegram) + email. Prend l'initiative quand un seuil est franchi.
 * (L'étage IA payant viendra brancher Claude pour de vraies idées.)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'ag_parse_fr_date' ) ) {
	/** Parse "d/m/Y" ou "d/m/Y H:i" -> timestamp (0 si échec). */
	function ag_parse_fr_date( $str ) {
		$str = trim( (string) $str );
		if ( '' === $str ) return 0;
		foreach ( array( 'd/m/Y H:i', 'd/m/Y' ) as $fmt ) {
			$dt = DateTime::createFromFormat( $fmt, $str );
			if ( $dt ) return $dt->getTimestamp();
		}
		return 0;
	}
}

if ( ! function_exists( 'ag_daily_roadmap' ) ) {
	/** Renvoie la liste des tâches du jour (chaînes prêtes à afficher/pousser). */
	function ag_daily_roadmap() {
		$prospects = (array) get_option( 'ag_prospects', array() );
		$leads     = (array) get_option( 'ag_leads', array() );
		$ventes    = (array) get_option( 'ag_ambassadeur_ventes', array() );
		$briefs    = (array) get_option( 'ag_express_briefs', array() );
		$now       = current_time( 'timestamp' );
		$recent    = 2 * DAY_IN_SECONDS;

		$a_contacter = 0; $relances = 0;
		foreach ( $prospects as $p ) {
			$s = $p['status'] ?? 'nouveau';
			if ( 'nouveau' === $s ) { $a_contacter++; continue; }
			if ( in_array( $s, array( 'contacte', 'relance' ), true ) && empty( $p['replied'] ) ) {
				$lc = ag_parse_fr_date( $p['last_contact'] ?? ( $p['date_contact'] ?? '' ) );
				if ( $lc && ( $now - $lc ) > 3 * DAY_IN_SECONDS ) $relances++;
			}
		}
		$a_valider = 0;
		foreach ( $ventes as $v ) { if ( ( $v['statut'] ?? '' ) === 'declaree' ) $a_valider++; }
		$leads_recent = 0;
		foreach ( $leads as $l ) { $t = ag_parse_fr_date( $l['date'] ?? '' ); if ( ! $t || ( $now - $t ) <= $recent ) $leads_recent++; }
		$briefs_recent = 0;
		foreach ( $briefs as $b ) { $t = ag_parse_fr_date( $b['date'] ?? '' ); if ( $t && ( $now - $t ) <= $recent ) $briefs_recent++; }

		$tasks = array();
		if ( $a_valider )    $tasks[] = "💰 $a_valider vente(s) à valider / payer (priorité argent)";
		if ( $briefs_recent ) $tasks[] = "📝 $briefs_recent brief(s) Sites Express récent(s) → lancer la production";
		if ( $leads_recent ) $tasks[] = "💬 $leads_recent lead(s) du chat à rappeler vite";
		if ( $relances )     $tasks[] = "🔁 $relances relance(s) en retard (sans réponse depuis +3 jours)";
		if ( $a_contacter )  $tasks[] = "🎯 $a_contacter prospect(s) à contacter ou à assigner à un ambassadeur";

		// Coups de pression (initiative)
		if ( 0 === $a_valider && $a_contacter > 10 ) $tasks[] = "⚠️ Aucune vente en attente : pousse la prospection aujourd'hui.";
		if ( empty( $tasks ) ) $tasks[] = "✅ Tout est à jour. Lance une recherche de prospects ou recrute un ambassadeur.";
		return $tasks;
	}
}

if ( ! function_exists( 'ag_coach_message' ) ) {
	function ag_coach_message() {
		$jour = function_exists( 'wp_date' ) ? wp_date( 'l d/m' ) : date( 'd/m' );
		$txt  = "🦁 Ta feuille de route — $jour\n\n";
		foreach ( ag_daily_roadmap() as $t ) $txt .= '• ' . $t . "\n";
		$txt .= "\n👉 Tout piloter : " . admin_url( 'admin.php?page=ag-hub' );
		return $txt;
	}
}

/* ── Cron quotidien : pousse la feuille de route (Telegram interne + email) ── */
add_action( 'init', function () {
	if ( '1' !== get_option( 'ag_coach_on', '1' ) ) {
		$ts = wp_next_scheduled( 'ag_coach_daily' );
		if ( $ts ) wp_unschedule_event( $ts, 'ag_coach_daily' );
		return;
	}
	if ( ! wp_next_scheduled( 'ag_coach_daily' ) ) wp_schedule_event( strtotime( 'tomorrow 8:00' ), 'daily', 'ag_coach_daily' );
} );
add_action( 'ag_coach_daily', function () {
	if ( '1' !== get_option( 'ag_coach_on', '1' ) ) return;
	$msg = ag_coach_message();
	if ( function_exists( 'ag_push' ) ) ag_push( '🗺️ Feuille de route du jour', $msg );
	$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
	wp_mail( $to, '🦁 Ta feuille de route du jour', $msg );
} );

/* ── Bouton : envoyer la feuille de route maintenant + activer/désactiver ── */
add_action( 'admin_post_ag_coach_now', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_coach' ) ) wp_die( 'no' );
	$ok = function_exists( 'ag_push' ) ? ag_push( '🗺️ Feuille de route', ag_coach_message() ) : false;
	wp_safe_redirect( admin_url( 'admin.php?page=ag-hub&ag_hub_msg=' . ( $ok ? 'coach_ok' : 'coach_err' ) ) );
	exit;
} );
add_action( 'admin_post_ag_coach_toggle', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_coach' ) ) wp_die( 'no' );
	update_option( 'ag_coach_on', '1' === get_option( 'ag_coach_on', '1' ) ? '0' : '1' );
	$ts = wp_next_scheduled( 'ag_coach_daily' ); if ( $ts ) wp_unschedule_event( $ts, 'ag_coach_daily' );
	if ( '1' === get_option( 'ag_coach_on', '1' ) ) wp_schedule_event( strtotime( 'tomorrow 8:00' ), 'daily', 'ag_coach_daily' );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-hub' ) );
	exit;
} );
