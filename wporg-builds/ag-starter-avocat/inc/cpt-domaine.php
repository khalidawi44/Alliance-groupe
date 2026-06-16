<?php
/**
 * Domaine d'expertise — HELPERS de rendu (Avocat).
 *
 * Conformité WordPress.org : le thème n'enregistre PAS le Custom Post Type
 * `ag_domaine` (c'est le plugin « AG Starter Companion » qui le fournit). Ce
 * fichier ne contient que des fonctions d'AFFICHAGE, qui se contentent
 * d'interroger le CPT s'il existe (sinon elles renvoient un résultat vide,
 * sans erreur).
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper : retrieve all published domaines as WP_Post objects, ordered
 * by menu_order then date. Returns an empty array if the CPT (provided by
 * the AG Starter Companion plugin) is not available.
 *
 * @param int $limit Number of domaines to return.
 * @return WP_Post[]
 */
function ag_starter_avocat_get_domaines( $limit = 6 ) {
	if ( ! post_type_exists( 'ag_domaine' ) ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => 'ag_domaine',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $limit ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);
}

/**
 * Returns a background image URL for a domain based on its icon keyword.
 *
 * @param string $icon Keyword (scales, gavel, shield, etc.).
 * @return string URL.
 */
function ag_starter_avocat_get_domaine_bg_url( $icon ) {
	$icon = strtolower( trim( (string) $icon ) );
	$map  = array(
		'scales'    => 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1200&q=80',
		'gavel'     => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1200&q=80',
		'shield'    => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1200&q=80',
		'briefcase' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=80',
		'house'     => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&q=80',
		'family'    => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=1200&q=80',
		'document'  => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200&q=80',
		'heart'     => 'https://images.unsplash.com/photo-1519378058457-4c29a0a2efac?w=1200&q=80',
		'lock'      => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1200&q=80',
		'bank'      => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?w=1200&q=80',
	);
	return isset( $map[ $icon ] ) ? $map[ $icon ] : 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1200&q=80';
}

/**
 * Returns inline SVG markup for a known icon keyword, or the original
 * emoji/text wrapped in a span.
 *
 * @param string $icon Either an SVG keyword or an emoji.
 * @return string HTML.
 */
function ag_starter_avocat_get_domaine_icon_html( $icon ) {
	$icon = trim( (string) $icon );
	if ( '' === $icon ) {
		$icon = 'scales';
	}
	$key  = strtolower( $icon );
	$svgs = array(
		'scales'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M5 21h14M7 6h10M5 14a3 3 0 0 0 6 0L8 8 5 14zM13 14a3 3 0 0 0 6 0l-3-6-3 6z"/></svg>',
		'gavel'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 13-7.5 7.5a2.12 2.12 0 0 1-3-3L11 10"/><path d="m16 16 6-6"/><path d="m8 8 6-6"/><path d="m9 7 8 8"/><path d="m21 11-8-8"/></svg>',
		'shield'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
		'briefcase' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M2 13h20"/></svg>',
		'house'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>',
		'family'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M21 21v-1.5a3 3 0 0 0-3-3h-1"/></svg>',
		'document'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>',
		'heart'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
		'lock'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'bank'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 21 18 0"/><path d="m3 10 9-7 9 7"/><path d="M5 10v9M9 10v9M15 10v9M19 10v9"/></svg>',
	);
	if ( isset( $svgs[ $key ] ) ) {
		return '<span class="ag-icon-svg">' . $svgs[ $key ] . '</span>';
	}
	return '<span class="ag-icon-emoji">' . esc_html( $icon ) . '</span>';
}
