<?php
/**
 * AG Plan du site — une page qui liste TOUTES les pages + articles publiés.
 *
 * But : le menu est réservé aux pages importantes ; ce plan attrape le reste,
 * pour que chaque page soit trouvable par un visiteur (et par Google). Il est
 * lié depuis le pied de page (présent sur tout le site).
 *
 *  - shortcode [ag_plan_du_site] (listes générées dynamiquement, jamais figées) ;
 *  - page « plan-du-site » auto-créée (idempotent), comme /avis-clients.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_PLAN_VER', 1 );

if ( ! function_exists( 'ag_plan_liste' ) ) {
	/** Rend une grille de liens à partir d'un tableau d'objets WP (pages/posts). */
	function ag_plan_liste( $items ) {
		if ( empty( $items ) ) {
			return '';
		}
		$o  = '<ul style="list-style:none;padding:0;margin:0 0 34px;display:grid;';
		$o .= 'grid-template-columns:repeat(auto-fill,minmax(min(100%,230px),1fr));gap:2px 26px">';
		foreach ( $items as $it ) {
			$url   = get_permalink( $it->ID );
			$title = get_the_title( $it->ID );
			if ( '' === trim( (string) $title ) ) {
				continue;
			}
			$o .= '<li style="padding:9px 0;border-bottom:1px solid rgba(212,180,92,.18)">';
			$o .= '<a href="' . esc_url( $url ) . '" style="text-decoration:none;color:inherit;display:block">' . esc_html( $title ) . '</a>';
			$o .= '</li>';
		}
		$o .= '</ul>';
		return $o;
	}
}

/* Shortcode : [ag_plan_du_site] */
add_shortcode( 'ag_plan_du_site', function () {
	ob_start();
	echo '<div style="max-width:1000px;margin:0 auto">';

	$pages = get_pages( array(
		'sort_column' => 'post_title',
		'sort_order'  => 'ASC',
		'post_status' => 'publish',
	) );
	if ( $pages ) {
		echo '<h2 style="font-family:Georgia,serif;font-weight:500;margin:0 0 4px">Toutes les pages</h2>';
		echo '<p style="opacity:.7;margin:0 0 18px">' . (int) count( $pages ) . ' pages publiées</p>';
		echo ag_plan_liste( $pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	$posts = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
	if ( $posts ) {
		echo '<h2 style="font-family:Georgia,serif;font-weight:500;margin:20px 0 4px">Articles du blog</h2>';
		echo '<p style="opacity:.7;margin:0 0 18px">' . (int) count( $posts ) . ' articles</p>';
		echo ag_plan_liste( $posts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</div>';
	return ob_get_clean();
} );

/* Page « plan-du-site » auto-créée (idempotent). */
add_action( 'admin_init', function () {
	if ( (int) get_option( 'ag_plan_page_done', 0 ) >= AG_PLAN_VER ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_page_by_path( 'plan-du-site' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Plan du site',
			'post_name'    => 'plan-du-site',
			'post_content' => "<p>Toutes les pages et tous les articles du site, réunis au même endroit.</p>\n[ag_plan_du_site]",
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id() ?: 1,
		) );
	}
	update_option( 'ag_plan_page_done', AG_PLAN_VER );
} );
