<?php
/**
 * AG — AGENT AUDIT (brouillons à valider).
 * Le robot audite tout seul des prospects (site + email), crée le rapport
 * PRIVÉ (lien non devinable + boutons paiement) et prépare un MESSAGE.
 * Rien n'est envoyé automatiquement : chaque brouillon attend ta validation
 * dans l'app (1 tap « Envoyer »). Respecte opt-out (refus / ne plus contacter
 * / client / ignore) et un plafond quotidien. Conforme : pas de démarchage
 * à froid automatisé.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ===== File de brouillons (option dédiée, découplée du CRM) ===== */
if ( ! function_exists( 'ag_audit_drafts' ) ) {
	function ag_audit_drafts() { return (array) get_option( 'ag_audit_drafts', array() ); }
	function ag_audit_drafts_save( $d ) { update_option( 'ag_audit_drafts', array_slice( (array) $d, -200 ), false ); }
	function ag_audit_draft_key( $host ) { return strtolower( preg_replace( '#^www\.#i', '', (string) $host ) ); }
}

/* ===== Construire un brouillon depuis un audit ===== */
if ( ! function_exists( 'ag_audit_draft_build' ) ) {
	function ag_audit_draft_build( $res, $email = '', $name = '' ) {
		if ( empty( $res['ok'] ) ) { return null; }
		$host = $res['host'];
		$report = function_exists( 'ag_rapport_create' ) ? ag_rapport_create( ag_aa_render( $res ), $name, $host, (int) $res['devis'] ) : null;
		$url = $report ? $report['url'] : '';
		// Top 2 points à corriger (pour l'accroche)
		$top = array();
		foreach ( array_slice( (array) $res['findings'], 0, 2 ) as $f ) { $top[] = $f['label']; }
		$subject = 'Votre site ' . $host . ' : audit de sécurité (' . (int) $res['score'] . '/100)';
		$body  = '<p>Bonjour' . ( $name ? ' ' . esc_html( $name ) : '' ) . ',</p>';
		$body .= '<p>Nous avons réalisé un <b>audit de sécurité</b> de votre site <b>' . esc_html( $host ) . '</b> (vérifications publiques, non intrusives).</p>';
		$body .= '<p><b>Score : ' . (int) $res['score'] . '/100.</b>' . ( $top ? ' Points à corriger en priorité : ' . esc_html( implode( ', ', $top ) ) . '.' : '' ) . '</p>';
		if ( $url ) { $body .= '<p>👉 <a href="' . esc_url( $url ) . '">Voir le rapport complet et les options de correction</a></p>'; }
		$body .= '<p style="color:#888;font-size:12px">Alliance Groupe — 07 44 82 95 16. Si vous ne souhaitez plus recevoir ce type de message, répondez « STOP » à cet e-mail.</p>';
		return array(
			'host' => $host, 'url' => $res['url'], 'email' => sanitize_email( $email ), 'name' => sanitize_text_field( $name ),
			'score' => (int) $res['score'], 'devis' => (int) $res['devis'],
			'report_url' => $url, 'subject' => $subject, 'body' => $body,
			'ts' => time(), 'sent' => false, 'sent_ts' => 0,
		);
	}

	function ag_audit_draft_add( $res, $email = '', $name = '' ) {
		$dr = ag_audit_draft_build( $res, $email, $name );
		if ( ! $dr ) { return null; }
		$all = ag_audit_drafts();
		$all[ ag_audit_draft_key( $dr['host'] ) ] = $dr;
		ag_audit_drafts_save( $all );
		return $dr;
	}
}

/* ===== Agent cron : audite des prospects, prépare les brouillons ===== */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_audit_agent_cron' ) ) { wp_schedule_event( time() + 600, 'daily', 'ag_audit_agent_cron' ); }
} );

add_action( 'ag_audit_agent_cron', 'ag_audit_agent_run' );
if ( ! function_exists( 'ag_audit_agent_run' ) ) {
	function ag_audit_agent_run() {
		if ( '1' !== (string) get_option( 'ag_audit_agent_on', '0' ) ) { return; }   // OFF par défaut
		if ( ! function_exists( 'ag_audit_auto' ) ) { return; }
		$cap   = max( 1, min( 15, (int) get_option( 'ag_audit_agent_cap', 5 ) ) );    // plafond/jour
		$drafts = ag_audit_drafts();
		$list  = (array) get_option( 'ag_prospects', array() );
		$done = 0;
		foreach ( $list as $p ) {
			if ( $done >= $cap ) { break; }
			$site  = trim( (string) ( $p['website'] ?? '' ) );
			$email = trim( (string) ( $p['email'] ?? '' ) );
			$st    = strtolower( (string) ( $p['status'] ?? '' ) );
			if ( ! $site || ! $email ) { continue; }                                   // besoin site + email
			if ( preg_match( '#refus|ne_pas|ne plus|ignore|client#i', $st ) ) { continue; } // opt-out
			$k = ag_audit_draft_key( wp_parse_url( ( 0 === stripos( $site, 'http' ) ? $site : 'https://' . $site ), PHP_URL_HOST ) ?: $site );
			if ( isset( $drafts[ $k ] ) ) { continue; }                                // déjà préparé
			$res = ag_audit_auto( $site );
			if ( empty( $res['ok'] ) ) { continue; }
			if ( function_exists( 'ag_aa_hist_add' ) ) { ag_aa_hist_add( $res ); }
			ag_audit_draft_add( $res, $email, (string) ( $p['name'] ?? '' ) );
			$done++;
		}
		if ( $done && function_exists( 'ag_push' ) ) { ag_push( '📬 Agent audit : ' . $done . ' rapport(s) préparé(s), à valider dans l\'app (onglet Audit).' ); }
	}
}

/* ===== AJAX app (admin + nonce) ===== */
if ( ! function_exists( 'ag_aa_agent_guard' ) ) {
	function ag_aa_agent_guard() { return current_user_can( 'manage_options' ) && isset( $_POST['_n'] ) && wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ); }
}

add_action( 'wp_ajax_ag_app_agent_toggle', function () {
	if ( ! ag_aa_agent_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$on = ! empty( $_POST['on'] ) ? '1' : '0';
	update_option( 'ag_audit_agent_on', $on, false );
	wp_send_json_success( array( 'on' => $on ) );
} );

add_action( 'wp_ajax_ag_app_drafts_list', function () {
	if ( ! ag_aa_agent_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$out = array();
	foreach ( ag_audit_drafts() as $k => $d ) {
		if ( ! empty( $d['sent'] ) ) { continue; }
		$out[] = array( 'key' => $k, 'host' => $d['host'], 'email' => $d['email'], 'name' => $d['name'], 'score' => (int) $d['score'], 'report_url' => $d['report_url'], 'subject' => $d['subject'], 'body' => $d['body'] );
	}
	wp_send_json_success( array( 'on' => (string) get_option( 'ag_audit_agent_on', '0' ), 'drafts' => $out ) );
} );

add_action( 'wp_ajax_ag_app_draft_send', function () {
	if ( ! ag_aa_agent_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$k = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$all = ag_audit_drafts();
	if ( empty( $all[ $k ] ) ) { wp_send_json_error( array( 'm' => 'Brouillon introuvable.' ) ); }
	$d = $all[ $k ];
	if ( ! is_email( $d['email'] ) ) { wp_send_json_error( array( 'm' => 'Email du prospect manquant/invalide.' ) ); }
	$ok = wp_mail( $d['email'], $d['subject'], $d['body'], array( 'Content-Type: text/html; charset=UTF-8' ) );
	if ( $ok ) { $all[ $k ]['sent'] = true; $all[ $k ]['sent_ts'] = time(); ag_audit_drafts_save( $all ); }
	wp_send_json_success( array( 'sent' => (bool) $ok ) );
} );

add_action( 'wp_ajax_ag_app_draft_del', function () {
	if ( ! ag_aa_agent_guard() ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
	$k = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$all = ag_audit_drafts();
	if ( isset( $all[ $k ] ) ) { unset( $all[ $k ] ); ag_audit_drafts_save( $all ); }
	wp_send_json_success( array( 'm' => 'retiré' ) );
} );
