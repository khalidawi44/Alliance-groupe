<?php
/**
 * AG — SUIVI & SURVEILLANCE des audits.
 *   1) Suivi des jobs actifs (file Kali) dans l'app.
 *   2) Surveillance récurrente (abonnement mensuel) : réaudit auto + alerte si ça se dégrade.
 *   3) Historique des scores (avant/après remédiation).
 * Stockage en options (pas de table SQL), comme le reste du thème.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ======================= HISTORIQUE (avant/après) ======================= */
if ( ! function_exists( 'ag_aa_hist_add' ) ) {
	function ag_aa_hist_key( $host ) { return strtolower( preg_replace( '#^www\.#i', '', (string) $host ) ); }

	function ag_aa_hist_add( $res ) {
		if ( empty( $res['ok'] ) || empty( $res['host'] ) ) { return; }
		$all = (array) get_option( 'ag_aa_history', array() );
		$k   = ag_aa_hist_key( $res['host'] );
		if ( empty( $all[ $k ] ) ) { $all[ $k ] = array(); }
		$snap = array(
			'ts'    => time(),
			'score' => (int) ( $res['score'] ?? 0 ),
			'devis' => (int) ( $res['devis'] ?? 0 ),
			'nb'    => count( (array) ( $res['findings'] ?? array() ) ),
			'crit'  => count( array_filter( (array) ( $res['findings'] ?? array() ), function ( $f ) { return ( $f['sev'] ?? '' ) === 'critique'; } ) ),
			'labels'=> array_values( array_map( function ( $f ) { return $f['sev'] . ':' . $f['label']; }, array_slice( (array) ( $res['findings'] ?? array() ), 0, 20 ) ) ),
		);
		$all[ $k ][] = $snap;
		if ( count( $all[ $k ] ) > 24 ) { $all[ $k ] = array_slice( $all[ $k ], -24 ); }
		update_option( 'ag_aa_history', $all, false );
		return $snap;
	}

	function ag_aa_hist_get( $host ) {
		$all = (array) get_option( 'ag_aa_history', array() );
		$k   = ag_aa_hist_key( $host );
		return isset( $all[ $k ] ) ? (array) $all[ $k ] : array();
	}

	/** Renvoie le dernier score connu AVANT maintenant (pour l'avant/après). */
	function ag_aa_hist_prev_score( $host ) {
		$h = ag_aa_hist_get( $host );
		if ( count( $h ) < 2 ) { return null; }
		$prev = $h[ count( $h ) - 2 ];
		return (int) ( $prev['score'] ?? 0 );
	}
}

/* ======================= SURVEILLANCE (abonnement) ======================= */
if ( ! function_exists( 'ag_aa_watch_list' ) ) {
	function ag_aa_watch_list() { return (array) get_option( 'ag_aa_watch', array() ); }

	function ag_aa_watch_add( $host, $url, $client = '', $email = '' ) {
		$w = ag_aa_watch_list();
		$k = ag_aa_hist_key( $host );
		$h = ag_aa_hist_get( $host );
		$last = $h ? end( $h ) : null;
		$w[ $k ] = array(
			'host' => $host, 'url' => esc_url_raw( $url ),
			'client' => sanitize_text_field( $client ), 'email' => sanitize_email( $email ),
			'since' => isset( $w[ $k ]['since'] ) ? $w[ $k ]['since'] : time(),
			'freq'  => 'month',
			'last_ts'    => $last ? (int) $last['ts'] : 0,
			'last_score' => $last ? (int) $last['score'] : null,
		);
		update_option( 'ag_aa_watch', $w, false );
		return $w[ $k ];
	}

	function ag_aa_watch_remove( $host ) {
		$w = ag_aa_watch_list(); $k = ag_aa_hist_key( $host );
		if ( isset( $w[ $k ] ) ) { unset( $w[ $k ] ); update_option( 'ag_aa_watch', $w, false ); }
	}
}

/* ======================= CRON mensuel + alerte régression ======================= */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_aa_monitor_cron' ) ) {
		wp_schedule_event( time() + 300, 'daily', 'ag_aa_monitor_cron' );
	}
} );

add_action( 'ag_aa_monitor_cron', 'ag_aa_monitor_run' );
if ( ! function_exists( 'ag_aa_monitor_run' ) ) {
	function ag_aa_monitor_run() {
		if ( ! function_exists( 'ag_audit_auto' ) ) { return; }
		$w = ag_aa_watch_list();
		$due = 27 * DAY_IN_SECONDS;     // réaudit mensuel
		$done = 0;
		foreach ( $w as $k => $site ) {
			if ( $done >= 8 ) { break; }                       // borne : max 8 réaudits / jour
			$last = (int) ( $site['last_ts'] ?? 0 );
			if ( $last && ( time() - $last ) < $due ) { continue; }
			$prev_score = isset( $site['last_score'] ) ? (int) $site['last_score'] : null;
			$res = ag_audit_auto( $site['url'] );
			if ( empty( $res['ok'] ) ) { continue; }
			ag_aa_hist_add( $res );
			$new_score = (int) $res['score'];
			$new_crit  = count( array_filter( (array) $res['findings'], function ( $f ) { return ( $f['sev'] ?? '' ) === 'critique' || ( $f['key'] ?? '' ) === 'leakfile'; } ) );
			// Alerte si le score chute nettement ou si une faille critique apparaît
			$regression = ( null !== $prev_score && $new_score <= $prev_score - 6 ) || $new_crit > 0;
			if ( $regression ) {
				$msg = '⚠️ Surveillance sécurité — ' . $site['host'] . ' : score ' . ( null !== $prev_score ? $prev_score . '→' : '' ) . $new_score . '/100'
					. ( $new_crit ? ', ' . $new_crit . ' faille(s) CRITIQUE(s)' : '' ) . '. Devis remédiation : ' . (int) $res['devis'] . ' €.';
				if ( function_exists( 'ag_push' ) ) { ag_push( $msg ); }
				$to = get_option( 'admin_email' );
				if ( $to ) { wp_mail( $to, 'Alerte sécurité — ' . $site['host'], $msg . "\n\n" . wp_strip_all_tags( ag_aa_render( $res ) ) ); }
			}
			$w[ $k ]['last_ts'] = time();
			$w[ $k ]['last_score'] = $new_score;
			$done++;
		}
		if ( $done ) { update_option( 'ag_aa_watch', $w, false ); }
	}
}

/* ======================= AJAX (admin + nonce app) ======================= */
if ( ! function_exists( 'ag_aa_mon_guard' ) ) {
	function ag_aa_mon_guard() {
		return current_user_can( 'manage_options' ) && isset( $_POST['_n'] ) && wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' );
	}
}

add_action( 'wp_ajax_ag_app_watch_add', function () {
	if ( ! ag_aa_mon_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
	$host = isset( $_POST['host'] ) ? sanitize_text_field( wp_unslash( $_POST['host'] ) ) : '';
	if ( ! $url && ! $host ) { wp_send_json_error( array( 'm' => 'URL manquante.' ) ); }
	if ( ! $host ) { $host = wp_parse_url( $url, PHP_URL_HOST ); }
	if ( ! $url ) { $url = 'https://' . $host; }
	$client = isset( $_POST['client'] ) ? sanitize_text_field( wp_unslash( $_POST['client'] ) ) : '';
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$row = ag_aa_watch_add( $host, $url, $client, $email );
	wp_send_json_success( array( 'watch' => $row, 'm' => $host . ' est surveillé (réaudit mensuel + alerte si ça se dégrade).' ) );
} );

add_action( 'wp_ajax_ag_app_watch_remove', function () {
	if ( ! ag_aa_mon_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$host = isset( $_POST['host'] ) ? sanitize_text_field( wp_unslash( $_POST['host'] ) ) : '';
	ag_aa_watch_remove( $host );
	wp_send_json_success( array( 'm' => 'Retiré de la surveillance.' ) );
} );

add_action( 'wp_ajax_ag_app_watch_list', function () {
	if ( ! ag_aa_mon_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$out = array();
	foreach ( ag_aa_watch_list() as $k => $s ) {
		$h = ag_aa_hist_get( $s['host'] );
		$first = $h ? reset( $h ) : null; $last = $h ? end( $h ) : null;
		$out[] = array(
			'host' => $s['host'], 'url' => $s['url'], 'client' => $s['client'],
			'first_score' => $first ? (int) $first['score'] : null,
			'last_score'  => $last ? (int) $last['score'] : ( isset( $s['last_score'] ) ? (int) $s['last_score'] : null ),
			'last_ts'     => $last ? (int) $last['ts'] : (int) ( $s['last_ts'] ?? 0 ),
			'runs'        => count( $h ),
		);
	}
	wp_send_json_success( array( 'watch' => $out ) );
} );

add_action( 'wp_ajax_ag_app_hist', function () {
	if ( ! ag_aa_mon_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$host = isset( $_POST['host'] ) ? sanitize_text_field( wp_unslash( $_POST['host'] ) ) : '';
	$h = ag_aa_hist_get( $host );
	$pts = array_map( function ( $s ) { return array( 'ts' => (int) $s['ts'], 'score' => (int) $s['score'], 'devis' => (int) $s['devis'], 'nb' => (int) $s['nb'] ); }, $h );
	wp_send_json_success( array( 'host' => $host, 'points' => $pts ) );
} );

add_action( 'wp_ajax_ag_app_jobs', function () {
	if ( ! ag_aa_mon_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	if ( ! function_exists( 'ag_pt_jobs' ) ) { wp_send_json_success( array( 'jobs' => array() ) ); }
	$jobs = array_reverse( (array) ag_pt_jobs() );
	$out = array();
	foreach ( array_slice( $jobs, 0, 20 ) as $j ) {
		$sum = (string) ( $j['summary'] ?? '' );
		$out[] = array(
			'id' => $j['id'] ?? '', 'target' => $j['target'] ?? '', 'client' => $j['client'] ?? '',
			'status' => $j['status'] ?? '', 'created' => (int) ( $j['created'] ?? 0 ),
			'finished' => (int) ( $j['finished'] ?? 0 ),
			'summary' => $sum ? mb_substr( wp_strip_all_tags( $sum ), 0, 600 ) : '',
			'error' => $j['error'] ?? '',
		);
	}
	wp_send_json_success( array( 'jobs' => $out ) );
} );
