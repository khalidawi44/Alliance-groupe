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

/* ── Prior du FILON (priorité métier décidée, pas apprise) ──────────────
   Problème de démarrage à froid : ag_scoreur_gagnants() n'apprend un métier
   QUE s'il a déjà converti. Avec 0 vente, rien n'est appris → le Scoreur ne
   sait pas que la SANTÉ doit passer EN PREMIER (décision de Fabrice 29/10,
   cf docs/FILON.md : santé #1, e-commerce #2, avocats/experts-comptables #3,
   courtiers/assurance #4). On encode donc cette priorité comme un prior fixe.
   Le filon = SÉCURITÉ/CONFORMITÉ (RGPD art. 32, HDS) : il ne se vend qu'à un
   pro qui a DÉJÀ un vrai site → prior plus fort si un site existe. */
if ( ! function_exists( 'ag_scoreur_filon_prior' ) ) {
	function ag_scoreur_filon_prior( $p, $a_un_site ) {
		$t = strtolower( (string) ( $p['type'] ?? '' ) . ' ' . (string) ( $p['name'] ?? '' ) );
		$sante = array( 'dentiste', 'dentaire', 'orthodont', 'kiné', 'kine', 'ostéo', 'osteo', 'médec', 'medec', 'santé', 'sante', 'labo', 'analyses médicales', 'radiolog', 'imagerie', 'infirmi', 'podolog', 'orthophon', 'psycho', 'clinique', 'vétérinaire', 'veterinaire', 'pharmacie', 'opticien', 'audiopro', 'sage-femme', 'ophtalmo', 'dermato' );
		$legal = array( 'avocat', 'notaire', 'huissier', 'comptable', 'expert-comptable', 'expertise comptable' );
		$fin   = array( 'courtier', 'assurance', 'mutuelle', 'patrimoine', 'fintech' );
		$hit   = function ( $arr ) use ( $t ) { foreach ( $arr as $k ) { if ( false !== strpos( $t, $k ) ) { return true; } } return false; };
		$prior = 0.0;
		if ( $hit( $sante ) )     { $prior = 28.0; } // #1 : données ultra-sensibles, obligation HDS pénale
		elseif ( $hit( $legal ) ) { $prior = 16.0; } // secret pro (avocats = email/courrier only, géré par le closer)
		elseif ( $hit( $fin ) )   { $prior = 14.0; } // données financières, obligation forte
		if ( ! $a_un_site ) { $prior *= 0.35; } // sans site, la conformité ne s'applique pas encore (angle création)
		return (float) apply_filters( 'ag_scoreur_filon_prior', $prior, $p, $a_un_site );
	}
}

/* ── Score d'un prospect (0 = faible, plus = prioritaire) ────────────── */
if ( ! function_exists( 'ag_scoreur_score' ) ) {
	function ag_scoreur_score( $p, $gagnants ) {
		$s = 0.0;
		// Contactable tout de suite (condition du canal email).
		if ( is_email( (string) ( $p['email'] ?? '' ) ) ) { $s += 15; }

		$site = trim( (string) ( $p['website'] ?? ( $p['site'] ?? '' ) ) );

		/* LE BON FILON du canal email : une entreprise qui A DÉJÀ un site (donc
		   joignable par contact@, et qui a déjà payé pour du web = intention
		   d'achat prouvée) mais perfectible → angle refonte / sécurité. */
		if ( '' !== $site ) {
			// Accroche VRAIE trouvée par l'enrichisseur → message pertinent, moins d'opt-out.
			if ( '' !== trim( (string) ( $p['constat'] ?? '' ) ) ) { $s += 14; }
			// Site faible ou exposé (signaux d'audit passif déjà stockés).
			$asc = isset( $p['audit_score'] ) ? (int) $p['audit_score'] : -1;
			if ( $asc >= 0 && $asc < 60 ) { $s += 12; }
			if ( (int) ( $p['audit_crit'] ?? 0 ) > 0 ) { $s += 8; }
			// Accessibilité faible = angle EAA concret (filon #1), accroche vraie.
			$a11y = isset( $p['a11y_score'] ) ? (int) $p['a11y_score'] : -1;
			if ( $a11y >= 0 && $a11y < 70 ) { $s += 10; }
		} else {
			// Pas de site : besoin réel (création) MAIS peu joignable par email.
			// On garde un bonus modéré, sans laisser ce segment écraser la file.
			$s += 10;
		}

		// FILON (29/10) : priorité métier décidée par Fabrice, tenue même à froid
		// (0 vente → le signal appris $gagnants est muet). Santé d'abord.
		$s += ag_scoreur_filon_prior( $p, '' !== $site );

		// Entreprise qui peut avoir besoin d'aide : note basse malgré des avis.
		$note = (float) ( $p['rating'] ?? 0 );
		$avis = (int) ( $p['reviews'] ?? 0 );
		if ( $avis > 0 && $note > 0 && $note < 4.0 ) { $s += 10; }
		// Score d'achat calculé par la chasse, s'il existe.
		if ( isset( $p['score'] ) && is_numeric( $p['score'] ) ) { $s += min( 30.0, (float) $p['score'] ); }
		// Affinité avec un métier qui a déjà converti (appris du tunnel).
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
