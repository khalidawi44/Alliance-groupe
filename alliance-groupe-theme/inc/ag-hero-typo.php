<?php
/**
 * AG Hero Typo — hierarchie titre/sous-titre du hero d'accueil, 3 formats.
 *
 * Pose en CSS INLINE dans le <head> (et non dans style.css) pour deux raisons :
 *  1) La page d'accueil est servie en no-store : le CSS inline s'applique
 *     immediatement, sans dependre du cache CDN 7 jours de Hostinger (hcdn),
 *     qui ressert style.css?ver=2.0.0 (version figee dans functions.php).
 *  2) Aucune retouche de functions.php requise.
 *
 * Cible #top .hero__* : specificite (id + classe) superieure aux styles inline
 * du template page-accueil-cinema.php, donc ecrase la taille de facon fiable.
 *
 *  - PC (par defaut)   : styles du template (grand titre) — non touche ici.
 *  - Tablette (<=960px): titre 2.4->3.4rem, sous-titre 1.02rem.
 *  - Mobile (<=600px)  : titre 1.9->2.4rem, sous-titre 0.9rem, resserre.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', function () {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}
	echo "<style id=\"ag-hero-typo\">"
		. "@media (max-width:960px){#top .hero__t{font-size:clamp(2.4rem,5.2vw,3.4rem);line-height:1.05}#top .hero__sub{font-size:1.02rem;line-height:1.55;max-width:42ch}}"
		. "@media (max-width:600px){#top .hero__t{font-size:clamp(1.9rem,7.4vw,2.4rem);line-height:1.1;letter-spacing:-.01em}#top .hero__sub{font-size:.9rem;line-height:1.5;max-width:34ch;margin-top:12px}}"
		. "</style>\n";
}, 99 );
