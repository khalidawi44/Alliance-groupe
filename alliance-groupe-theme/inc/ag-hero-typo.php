<?php
/**
 * AG Hero Typo — hierarchie titre/sous-titre du hero d'accueil, 3 formats,
 * + anti-cache des medias hero (video / poster / fond).
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

/**
 * Anti-cache des medias du hero.
 *
 * Le HTML de l'accueil est no-store, mais les fichiers .mp4/.jpg du hero sont
 * mis en cache par le navigateur ET le CDN a URL identique : quand on remplace
 * la video par une nouvelle du meme nom, l'ancienne continue d'etre resservie.
 *
 * On ajoute ?v=<date de modif> aux URLs des medias hero cote client. L'URL
 * change des que le fichier change, donc navigateurs et CDN reprennent la
 * nouvelle version (meme principe que l'enqueue filemtime de style.css).
 */
add_action( 'wp_footer', function () {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}
	$path = get_stylesheet_directory();
	$uri  = get_stylesheet_directory_uri();
	$rels = array(
		'/assets/videos/hero-egerie-court.mp4',
		'/assets/videos/hero-egerie-poster.jpg',
		'/assets/images/cities/baie_naples_nuit.jpg',
	);
	$map = array();
	foreach ( $rels as $rel ) {
		$abs = $path . $rel;
		$ver = file_exists( $abs ) ? filemtime( $abs ) : '1';
		$map[ $uri . $rel ] = $uri . $rel . '?v=' . $ver;
	}
	echo "<script id=\"ag-hero-cache\">(function(){"
		. "var m=" . wp_json_encode( $map ) . ";"
		. "function b(el,a){var u=el.getAttribute(a);if(!u)return;var q=u.split('?')[0];if(m[q]&&u!==m[q]){el.setAttribute(a,m[q]);}}"
		. "document.querySelectorAll('.hero img,.hero video').forEach(function(el){b(el,'src');b(el,'poster');});"
		. "})();</script>\n";
}, 99 );
