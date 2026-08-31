<?php
/**
 * AG SEO — Nettoyage de l'index.
 *
 * Objectif : empêcher Google d'indexer les pages sans valeur SEO (espaces
 * client/ambassadeur, outils internes, pages « merci », archives fines). Ces
 * pages diluent l'autorité du domaine et gaspillent le budget de crawl — elles
 * ressortaient même AVANT les pages commerciales dans `site:`.
 *
 * Compatible Yoast (filtre wpseo_robots_array) ET cœur WordPress (wp_robots).
 * noindex + follow : Google n'indexe pas la page mais suit quand même ses liens.
 *
 * @package AllianceGroupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Slugs de pages à sortir de l'index (utilitaires / internes / thank-you).
 * Ne PAS y mettre les pages légales : elles peuvent rester indexées.
 */
function ag_noindex_slugs() {
	return array(
		// Comptes & authentification
		'connexion', 'mot-de-passe', 'maintenance',
		'mon-espace-client', 'espace-client', 'espace-client-app', 'espace-ambassadeur',
		// Outils / apps internes
		'prospection-mobile', 'ma-prospection', 'rapport', 'systeme-prospection',
		// Ambassadeurs / recrutement / interne
		'recruteur', 'tirage-au-sort', 'classement', 'programme-racines',
		'contrat-client', 'contrat-ambassadeur', 'guide-ambassadeur',
		// Pages de remerciement (aussi bloquées dans robots.txt)
		'merci-rdv', 'merci-achat',
	);
}

/**
 * Cette requête doit-elle être mise en noindex ?
 */
function ag_should_noindex() {
	// Archives fines : auteur, date, étiquettes, résultats de recherche.
	if ( is_author() || is_date() || is_tag() || is_search() ) {
		return true;
	}
	// Pages utilitaires ciblées par slug.
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( in_array( $slug, ag_noindex_slugs(), true ) ) {
			return true;
		}
	}
	return false;
}

/* Yoast SEO : forcer noindex sur ces pages (prioritaire s'il est actif). */
add_filter( 'wpseo_robots_array', function ( $robots ) {
	if ( ag_should_noindex() ) {
		$robots['index']      = 'noindex';
		$robots['follow']     = 'follow';
		$robots['max-snippet']       = null;
		$robots['max-image-preview'] = null;
		$robots['max-video-preview'] = null;
	}
	return $robots;
}, 20 );

/* En-tête HTTP X-Robots-Tag : filet robuste qui fonctionne même pour les pages
   « app » (espace client/ambassadeur) dont le template n'appelle pas wp_head(). */
add_action( 'template_redirect', function () {
	if ( ag_should_noindex() && ! headers_sent() ) {
		header( 'X-Robots-Tag: noindex, follow', true );
	}
} );

/* Cœur WordPress (si Yoast absent ou n'a pas la main) : ajouter noindex,follow. */
add_filter( 'wp_robots', function ( $robots ) {
	if ( ag_should_noindex() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	return $robots;
}, 20 );

/* Yoast sitemap : exclure explicitement ces pages (double sécurité). */
add_filter( 'wpseo_sitemap_exclude_post_ids', function ( $excluded ) {
	foreach ( ag_noindex_slugs() as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) { $excluded[] = $p->ID; }
	}
	return array_values( array_unique( $excluded ) );
} );
