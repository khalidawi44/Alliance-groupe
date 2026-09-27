<?php
/**
 * ag-brand-nav.php — Favicon (uniquement).
 *
 * Historique : ce module portait aussi une surcharge de navigation (hamburger à
 * toutes les tailles + contraste au scroll), utile en 1.3.5. Depuis, la lane
 * DESIGN a repris ENTIÈREMENT le menu dans `assets/signature.css` (burger ≤820px,
 * panneau plein écran habillé, animation croix) et le contraste au scroll est
 * déjà géré par le thème (`functions.php` ajoute `.scrolled` à y>50).
 *
 * La surcharge de nav d'ici entrait alors en conflit : elle forçait le burger
 * même sur ordinateur (`display:flex!important` sans media query) et un second
 * panneau plein écran, ce qui cassait le menu. On la RETIRE : `signature.css`
 * est désormais l'unique propriétaire du menu. Il ne reste ici que le favicon.
 *
 * @package ag-gwen-services
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Favicon ─────────────────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) { return; } // respecte un icône déjà réglé
	$ico = get_theme_file_uri( 'assets/favicon.ico' );
	$ap  = get_theme_file_uri( 'assets/apple-touch-icon.png' );
	echo '<link rel="icon" href="' . esc_url( $ico ) . '" sizes="any">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $ap ) . '">' . "\n";
}, 5 );
