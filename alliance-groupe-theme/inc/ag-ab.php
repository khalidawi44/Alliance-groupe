<?php
/**
 * ag-ab.php — MAX, l'expérimentateur (A/B autonome).
 *
 * Problème : si tous les mails ont le même objet, on ne saura jamais si un autre
 * convertirait mieux. L'équipe n'apprend pas.
 *
 * Max fait tourner, TOUT SEUL, une expérience sur l'OBJET du 1er mail :
 *  - il attribue une variante à chaque prospect (collante, déterministe) ;
 *  - Hugo envoie avec cette variante ;
 *  - l'analyste (ag-funnel) mesure le taux d'intéressés par variante ;
 *  - quand une variante gagne nettement, Max la PROMEUT : tous les nouveaux mails
 *    l'utilisent (explore → exploite). Aucune intervention humaine.
 *
 * Personne n'a rien à « activer » : c'est branché dans le pilote auto (24/7) et
 * dans l'envoi d'Hugo. Max est un agent autonome, en lien avec Hugo et Léa.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Réglages ────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_ab_actif' ) ) {
	function ag_ab_actif() { return (bool) get_option( 'ag_ab_on', 1 ); } // autonome par défaut
}
if ( ! function_exists( 'ag_ab_min_envois' ) ) {
	// Nb d'envois minimum par variante avant de pouvoir déclarer un gagnant.
	function ag_ab_min_envois() { return max( 20, (int) get_option( 'ag_ab_min', 40 ) ); }
}

/* ── L'expérience : variantes d'objet (tokens {metier} {ville}) ──────── */
if ( ! function_exists( 'ag_ab_variants' ) ) {
	function ag_ab_variants() {
		return array(
			'question' => 'Votre site {metier} à {ville} ?',
			'benefice' => 'Plus de clients pour votre activité à {ville}',
			'local'    => '{ville} : j\'ai regardé votre présence en ligne',
		);
	}
}

/* ── Choix de la variante pour un prospect (collant + explore/exploite) ─ */
if ( ! function_exists( 'ag_ab_pick' ) ) {
	function ag_ab_pick( $p ) {
		$vars = ag_ab_variants();
		if ( empty( $vars ) ) { return ''; }
		// Exploite : un gagnant a été promu → tout le monde dessus.
		$winner = (string) get_option( 'ag_ab_winner', '' );
		if ( '' !== $winner && isset( $vars[ $winner ] ) ) { return $winner; }
		// Explore : répartition déterministe et stable par prospect (pas de hasard volatil).
		$cle  = (string) ( $p['id'] ?? ( $p['email'] ?? '' ) );
		if ( '' === $cle ) { return array_key_first( $vars ); }
		$keys = array_keys( $vars );
		return $keys[ crc32( $cle ) % count( $keys ) ];
	}
}

/* ── Applique la variante à l'objet (appelé par Hugo au 1er mail) ─────── */
if ( ! function_exists( 'ag_ab_applique' ) ) {
	/**
	 * @return array array( 'sujet' => string, 'variante' => string )
	 */
	function ag_ab_applique( $sujet_base, $p, $etape = 0 ) {
		$rien = array( 'sujet' => (string) $sujet_base, 'variante' => '' );
		if ( ! ag_ab_actif() ) { return $rien; }
		if ( (int) $etape > 0 ) { return $rien; } // on teste l'OBJET du 1er contact seulement
		$metier = trim( (string) ( $p['type'] ?? '' ) );
		$ville  = trim( (string) ( $p['city'] ?? '' ) );
		if ( '' === $ville ) { return $rien; } // sans ville, l'objet serait bancal → on ne teste pas
		$vars = ag_ab_variants();
		$v    = ag_ab_pick( $p );
		if ( '' === $v || ! isset( $vars[ $v ] ) ) { return $rien; }
		$sujet = strtr( $vars[ $v ], array(
			'{metier}' => '' !== $metier ? $metier : 'votre activité',
			'{ville}'  => $ville,
		) );
		$sujet = trim( preg_replace( '/\s+/', ' ', $sujet ) );
		if ( '' === $sujet ) { return $rien; }
		return array( 'sujet' => $sujet, 'variante' => $v );
	}
}

/* ── Résultats : taux d'intéressés par variante (lus dans le journal) ── */
if ( ! function_exists( 'ag_ab_resultats' ) ) {
	function ag_ab_resultats( $jours = 0 ) {
		$events = (array) get_option( 'ag_funnel_events', array() );
		$since  = $jours > 0 ? time() - $jours * DAY_IN_SECONDS : 0;
		$vars   = ag_ab_variants();
		$res    = array();
		foreach ( array_keys( $vars ) as $k ) { $res[ $k ] = array( 'sent' => 0, 'interested' => 0, 'signed' => 0 ); }
		foreach ( $events as $e ) {
			if ( (int) ( $e['t'] ?? 0 ) < $since ) { continue; }
			$v = (string) ( $e['variante'] ?? '' );
			if ( '' === $v || ! isset( $res[ $v ] ) ) { continue; }
			$t = (string) ( $e['type'] ?? '' );
			if ( 'sent' === $t )       { $res[ $v ]['sent']++; }
			if ( 'interested' === $t ) { $res[ $v ]['interested']++; }
			if ( 'signed' === $t )     { $res[ $v ]['signed']++; }
		}
		foreach ( $res as $k => $r ) {
			$res[ $k ]['taux'] = $r['sent'] > 0 ? round( 100 * $r['interested'] / $r['sent'], 1 ) : 0.0;
		}
		return $res;
	}
}

/* ── Auto-promotion : Max couronne le gagnant, tout seul ─────────────── */
if ( ! function_exists( 'ag_ab_auto_promote' ) ) {
	function ag_ab_auto_promote() {
		if ( ! ag_ab_actif() ) { return false; }
		if ( '' !== (string) get_option( 'ag_ab_winner', '' ) ) { return false; } // déjà décidé
		$res = ag_ab_resultats();
		$min = ag_ab_min_envois();
		// Chaque variante doit avoir assez d'envois pour qu'on puisse comparer.
		$pret = true; $best = ''; $best_taux = -1.0; $second = -1.0;
		foreach ( $res as $k => $r ) {
			if ( $r['sent'] < $min ) { $pret = false; }
			if ( $r['taux'] > $best_taux ) { $second = $best_taux; $best_taux = $r['taux']; $best = $k; }
			elseif ( $r['taux'] > $second ) { $second = $r['taux']; }
		}
		if ( ! $pret || '' === $best ) { return false; }
		// Gagnant net : au moins 1 intéressé et une marge réelle sur le 2e.
		if ( $res[ $best ]['interested'] < 1 ) { return false; }
		if ( $best_taux < $second + 1.5 ) { return false; } // marge de 1,5 pt mini
		update_option( 'ag_ab_winner', $best, false );
		update_option( 'ag_ab_winner_le', time(), false );
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '🧪 Max — variante gagnante promue',
				'Objet « ' . $best .' » : ' . $best_taux . '% d\'intéressés. Tous les nouveaux mails l\'utilisent désormais.' );
		}
		return true;
	}
}

/* ── Écran : Prospection → 🧪 Expériences (Max) ──────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Expériences (Max)', '🧪 Expériences', 'manage_options', 'ag-ab', 'ag_ab_render' );
}, 27 );

add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_ab_save'] ) && check_admin_referer( 'ag_ab' ) && current_user_can( 'manage_options' ) ) {
		update_option( 'ag_ab_on', isset( $_POST['on'] ) ? 1 : 0, false );
	}
	if ( isset( $_POST['ag_ab_reset'] ) && check_admin_referer( 'ag_ab' ) && current_user_can( 'manage_options' ) ) {
		delete_option( 'ag_ab_winner' ); delete_option( 'ag_ab_winner_le' ); // relance l'exploration
	}
} );

if ( ! function_exists( 'ag_ab_render' ) ) {
	function ag_ab_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$vars   = ag_ab_variants();
		$res    = ag_ab_resultats();
		$winner = (string) get_option( 'ag_ab_winner', '' );
		echo '<div class="wrap"><h1>🧪 Max — l\'expérimentateur (A/B autonome)</h1>';
		echo '<p class="description">Max teste tout seul l\'objet du 1er mail et promeut le gagnant quand il est net (≥ ' . (int) ag_ab_min_envois() . ' envois/variante). Aucune action requise : c\'est branché sur Hugo et le pilote auto.</p>';

		echo '<form method="post" style="margin:10px 0 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">';
		wp_nonce_field( 'ag_ab' );
		echo '<label><input type="checkbox" name="on" ' . checked( ag_ab_actif(), true, false ) . '> Expérience active</label>';
		echo '<button class="button button-primary" name="ag_ab_save" value="1">Enregistrer</button>';
		if ( '' !== $winner ) {
			echo '<button class="button" name="ag_ab_reset" value="1" onclick="return confirm(\'Relancer l\\\'exploration (oublier le gagnant actuel) ?\');">Relancer l\'exploration</button>';
		}
		echo '</form>';

		if ( '' !== $winner && isset( $vars[ $winner ] ) ) {
			echo '<div style="background:#edfaef;border-left:4px solid #1e7e34;padding:10px 14px;max-width:820px;margin-bottom:14px">';
			echo '🏆 <strong>Gagnant promu</strong> : « ' . esc_html( $winner ) . ' » — <em>' . esc_html( $vars[ $winner ] ) . '</em>. Tous les nouveaux 1ers mails l\'utilisent.';
			echo '</div>';
		} else {
			echo '<p style="color:#996800">⏳ Exploration en cours : Max répartit les variantes et attend assez de données pour trancher.</p>';
		}

		echo '<table class="widefat striped" style="max-width:820px"><thead><tr><th>Variante</th><th>Objet</th><th>Envoyés</th><th>Intéressés</th><th>Taux</th></tr></thead><tbody>';
		foreach ( $vars as $k => $tpl ) {
			$r = $res[ $k ] ?? array( 'sent' => 0, 'interested' => 0, 'taux' => 0 );
			$flag = ( $k === $winner ) ? ' 🏆' : '';
			echo '<tr><td><strong>' . esc_html( $k ) . '</strong>' . $flag . '</td><td><code>' . esc_html( $tpl ) . '</code></td><td>' . (int) $r['sent'] . '</td><td>' . (int) $r['interested'] . '</td><td>' . esc_html( (string) $r['taux'] ) . ' %</td></tr>';
		}
		echo '</tbody></table>';
		echo '<p class="description" style="margin-top:14px">Les variantes s\'éditent dans <code>inc/ag-ab.php</code> → <code>ag_ab_variants()</code>. Mesure par l\'analyste (taux d\'intéressés). On teste l\'objet du 1er contact (là où se joue l\'ouverture).</p>';
		echo '</div>';
	}
}
