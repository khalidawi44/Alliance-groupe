<?php
/**
 * ag-scoreur.php — LE SCOREUR (priorisation autonome de la file d'Hugo).
 *
 * Hugo n'a qu'un plafond de mails par jour. Autant écrire D'ABORD à ceux qui ont
 * le plus de chances de signer. Le Scoreur réordonne la file à chaque tour — tout
 * seul, sans activation — à partir de signaux PUBLICS déjà stockés (pas d'appel
 * réseau, pas de scan actif). Il ne décide pas QUI reçoit un mail (c'est
 * l'éligibilité d'Hugo), seulement l'ORDRE, dans la limite du plafond.
 *
 * En lien : il apprend des métiers qui ont déjà converti (journal `ag_funnel_events`).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ag_scoreur_on' ) ) {
	function ag_scoreur_on() { return (bool) get_option( 'ag_scoreur_on', 1 ); } // autonome par défaut
}

/* ── Les métiers qui convertissent (appris du journal) ───────────────── */
if ( ! function_exists( 'ag_scoreur_gagnants' ) ) {
	function ag_scoreur_gagnants() {
		$g = array();
		foreach ( (array) get_option( 'ag_funnel_events', array() ) as $e ) {
			$t = (string) ( $e['type'] ?? '' );
			if ( 'interested' !== $t && 'signed' !== $t ) { continue; }
			$m = (string) ( $e['metier'] ?? '' );
			if ( '' === $m ) { continue; }
			$poids = ( 'signed' === $t ) ? 3 : 1; // une signature pèse plus qu'un simple intérêt
			$g[ $m ] = ( $g[ $m ] ?? 0 ) + $poids;
		}
		return $g;
	}
}

/* ── Score d'un prospect (0 = faible, plus = prioritaire) ────────────── */
if ( ! function_exists( 'ag_scoreur_score' ) ) {
	function ag_scoreur_score( $p, $gagnants ) {
		$s = 0.0;
		// Contactable tout de suite.
		if ( is_email( (string) ( $p['email'] ?? '' ) ) ) { $s += 15; }
		// Besoin fort : pas de vrai site (signal public, déjà stocké).
		$site = trim( (string) ( $p['website'] ?? ( $p['site'] ?? '' ) ) );
		if ( '' === $site ) { $s += 30; }
		// Entreprise qui peut avoir besoin d'aide : note basse malgré des avis.
		$note = (float) ( $p['rating'] ?? 0 );
		$avis = (int) ( $p['reviews'] ?? 0 );
		if ( $avis > 0 && $note > 0 && $note < 4.0 ) { $s += 10; }
		// Score d'achat calculé par la chasse, s'il existe.
		if ( isset( $p['score'] ) && is_numeric( $p['score'] ) ) { $s += min( 30.0, (float) $p['score'] ); }
		// Affinité avec un métier qui a déjà converti.
		$m = (string) ( $p['type'] ?? '' );
		if ( '' !== $m && isset( $gagnants[ $m ] ) ) { $s += min( 25.0, 5.0 * (float) $gagnants[ $m ] ); }
		// Jamais encore contacté → on le fait avancer.
		if ( 0 === (int) ( $p['closer_step'] ?? 0 ) ) { $s += 8; }
		return $s;
	}
}

/* ── Le filtre : réordonne la file d'Hugo (meilleures cibles d'abord) ── */
if ( ! function_exists( 'ag_scoreur_trier' ) ) {
	function ag_scoreur_trier( $list ) {
		$list = (array) $list;
		if ( ! ag_scoreur_on() || count( $list ) < 2 ) { return $list; }
		$g = ag_scoreur_gagnants();
		// Décore-trie-dévore : stable et sans recalcul pendant le tri.
		$deco = array();
		foreach ( $list as $p ) { $deco[] = array( 'p' => $p, 's' => ag_scoreur_score( $p, $g ) ); }
		usort( $deco, function ( $a, $b ) { return $b['s'] <=> $a['s']; } );
		$out = array();
		foreach ( $deco as $d ) { $out[] = $d['p']; }
		return $out;
	}
}
add_filter( 'ag_closer_file', 'ag_scoreur_trier', 10, 1 );
