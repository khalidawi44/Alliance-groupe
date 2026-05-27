<?php
/**
 * AG — Demo Leaderboard (social proof au démarrage).
 *
 * Injecte 4 ambassadeurs + 6 recruteurs démo dans les classements
 * publics (page /classement et /recruteur), AVEC DES SCORES MODESTES,
 * pour qu'un visiteur voie "il y a déjà du monde".
 *
 * Particularités :
 *  - Les démos sont identifiables par leur email "*@demo.alliancegroupe.local"
 *    (jamais affiché en clair).
 *  - Ils n'apparaissent QUE dans le classement "all" (général), pas
 *    dans le jour/mois → évite de paraître bizarre ("aujourd'hui : 4
 *    inconnus avec 0 vente").
 *  - Toggle via option ag_demo_leaderboard_on (1 par défaut).
 *    Pour les retirer plus tard : update_option( 'ag_demo_leaderboard_on', 0 )
 *    OU supprimer/désactiver ce fichier.
 *  - Aucun impact sur les options réelles (ag_ambassadeurs, ag_ambassadeur_ventes,
 *    ag_parrain_primes) : injection au niveau du filtre uniquement.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'ag_demo_board_enabled' ) ) {
	function ag_demo_board_enabled() {
		return (bool) get_option( 'ag_demo_leaderboard_on', 1 );
	}
}

if ( ! function_exists( 'ag_demo_ambassadeurs' ) ) {
	/** 4 ambassadeurs démo — scores modestes, naturellement en bas du classement. */
	function ag_demo_ambassadeurs() {
		return array(
			array( 'email' => 'nadia.b@demo.alliancegroupe.local',  'name' => 'Nadia B.',  'ca' => 980, 'ventes' => 2, 'commission' => 98, 'is_demo' => true ),
			array( 'email' => 'sofia.l@demo.alliancegroupe.local',  'name' => 'Sofia L.',  'ca' => 890, 'ventes' => 1, 'commission' => 89, 'is_demo' => true ),
			array( 'email' => 'marc.d@demo.alliancegroupe.local',   'name' => 'Marc D.',   'ca' => 490, 'ventes' => 1, 'commission' => 49, 'is_demo' => true ),
			array( 'email' => 'karim.m@demo.alliancegroupe.local',  'name' => 'Karim M.',  'ca' => 0,   'ventes' => 0, 'commission' => 0,  'is_demo' => true ),
		);
	}
}

if ( ! function_exists( 'ag_demo_recruteurs' ) ) {
	/** 6 recruteurs démo — 1-2 filleuls, 0-1 actif, primes maigres. */
	function ag_demo_recruteurs() {
		return array(
			array( 'email' => 'julie.r@demo.alliancegroupe.local',  'name' => 'Julie R.',  'filleuls' => 2, 'actifs' => 1, 'primes' => 25, 'is_demo' => true ),
			array( 'email' => 'anais.g@demo.alliancegroupe.local',  'name' => 'Anaïs G.',  'filleuls' => 2, 'actifs' => 1, 'primes' => 25, 'is_demo' => true ),
			array( 'email' => 'leo.p@demo.alliancegroupe.local',    'name' => 'Léo P.',    'filleuls' => 2, 'actifs' => 0, 'primes' => 0,  'is_demo' => true ),
			array( 'email' => 'theo.s@demo.alliancegroupe.local',   'name' => 'Théo S.',   'filleuls' => 1, 'actifs' => 0, 'primes' => 0,  'is_demo' => true ),
			array( 'email' => 'amine.t@demo.alliancegroupe.local',  'name' => 'Amine T.',  'filleuls' => 1, 'actifs' => 0, 'primes' => 0,  'is_demo' => true ),
			array( 'email' => 'lucas.v@demo.alliancegroupe.local',  'name' => 'Lucas V.',  'filleuls' => 1, 'actifs' => 0, 'primes' => 0,  'is_demo' => true ),
		);
	}
}

/* ─── Ambassadeurs : injection dans le classement général ─────────── */
add_filter( 'ag_ambassadeur_leaderboard', function ( $rows, $period ) {
	if ( ! ag_demo_board_enabled() ) return $rows;
	if ( 'all' !== $period ) return $rows; // jamais en day/month (incohérent)
	$existing = array();
	foreach ( $rows as $r ) { $existing[ strtolower( $r['email'] ?? '' ) ] = 1; }
	foreach ( ag_demo_ambassadeurs() as $d ) {
		if ( isset( $existing[ strtolower( $d['email'] ) ] ) ) continue;
		$rows[] = $d;
	}
	usort( $rows, function ( $a, $b ) { return ( (float) ( $b['ca'] ?? 0 ) ) <=> ( (float) ( $a['ca'] ?? 0 ) ); } );
	$rank = 1;
	foreach ( $rows as &$row ) { $row['rank'] = $rank++; }
	unset( $row );
	return $rows;
}, 10, 2 );

/* ─── Recruteurs : injection dans le classement recruteurs ─────────── */
add_filter( 'ag_recruiter_leaderboard', function ( $rows ) {
	if ( ! ag_demo_board_enabled() ) return $rows;
	$existing = array();
	foreach ( $rows as $r ) { $existing[ strtolower( $r['email'] ?? '' ) ] = 1; }
	foreach ( ag_demo_recruteurs() as $d ) {
		if ( isset( $existing[ strtolower( $d['email'] ) ] ) ) continue;
		$rows[] = $d;
	}
	usort( $rows, function ( $a, $b ) {
		return ( ( $b['actifs'] ?? 0 ) <=> ( $a['actifs'] ?? 0 ) )
			?: ( ( $b['filleuls'] ?? 0 ) <=> ( $a['filleuls'] ?? 0 ) );
	} );
	$rank = 1;
	foreach ( $rows as &$row ) { $row['rank'] = $rank++; }
	unset( $row );
	return $rows;
} );

/* ─── Toggle admin (Ambassadeurs > Réglages, simple champ) ───────── */
add_action( 'admin_post_ag_demo_board_toggle', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_demo_board' ) ) wp_die( 'no' );
	update_option( 'ag_demo_leaderboard_on', empty( $_POST['on'] ) ? 0 : 1 );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-ambassadeurs&demo=' . ( ag_demo_board_enabled() ? '1' : '0' ) ) );
	exit;
} );
