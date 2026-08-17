<?php
/**
 * ag-atelier.php — Crée/maintient la page « Atelier IA » (/atelier)
 * rendue par templates/page-atelier.php.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_init', 'ag_atelier_ensure_page' );
if ( ! function_exists( 'ag_atelier_ensure_page' ) ) {
	function ag_atelier_ensure_page() {
		if ( (int) get_option( 'ag_atelier_page_v1', 0 ) >= 1 ) { return; }
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$slug = 'atelier';
		$tpl  = 'templates/page-atelier.php';
		$existing = get_page_by_path( $slug );
		if ( ! $existing ) {
			$pid = wp_insert_post( array(
				'post_title'   => 'Atelier IA',
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Rendu par ' . $tpl . ' -->',
				'post_author'  => get_current_user_id() ?: 1,
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, '_wp_page_template', $tpl );
			}
		} else {
			update_post_meta( $existing->ID, '_wp_page_template', $tpl );
		}
		update_option( 'ag_atelier_page_v1', 1 );
	}
}
