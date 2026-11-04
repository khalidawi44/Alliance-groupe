<?php
/**
 * ag-app-admin.php — « Application » dans le tableau de bord WordPress.
 *
 * Demande de Fabrice : retrouver TOUTES les fonctionnalités de l'app mobile
 * (prospection, audit, cartographie des agences → leurs créations, Kali, Trouvés,
 * démarchage) directement dans l'admin WordPress, comme un espace à part, avec
 * exactement les mêmes actions et les MÊMES DONNÉES.
 *
 * Choix : on INTÈGRE la page de l'app (/prospection-mobile) dans une page d'admin
 * via une iframe plein écran. Même code, mêmes données, zéro doublon, toujours
 * synchronisé — ce qui est scanné ici l'est aussi sur mobile, et inversement.
 *
 * @package Alliance_Groupe
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Garantit que la page /prospection-mobile existe (app) et porte le bon template. */
add_action( 'init', function () {
	if ( get_option( 'ag_app_page_v1' ) ) { return; }
	if ( ! get_page_by_path( 'prospection-mobile' ) ) {
		$id = wp_insert_post( array(
			'post_title'   => 'Application',
			'post_name'    => 'prospection-mobile',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$tpl = 'templates/page-prospection-mobile.php';
			if ( file_exists( get_stylesheet_directory() . '/' . $tpl ) ) {
				update_post_meta( $id, '_wp_page_template', $tpl );
			}
		}
	}
	update_option( 'ag_app_page_v1', 1 );
} );

/* Menu admin : « 🚀 Application » (tout en haut) → l'app complète en plein écran. */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Application AG',
		'🚀 Application',
		'manage_options',
		'ag-app',
		'ag_app_admin_render',
		'dashicons-smartphone',
		2
	);
} );

if ( ! function_exists( 'ag_app_admin_render' ) ) {
	function ag_app_admin_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$url = home_url( '/prospection-mobile' );
		echo '<div class="wrap" style="margin-right:20px;">';
		echo '<h1 style="margin-bottom:6px;">🚀 Application — prospection · audit · cartographie · Kali</h1>';
		echo '<p class="description" style="margin:0 0 10px;max-width:900px;">Toutes les fonctionnalités de l\'app mobile, ici dans WordPress, avec <strong>les mêmes données</strong> (prospects, audits, agences cartographiées et leurs créations, preuves Kali, démarchage). Ce que vous faites ici apparaît aussi sur mobile. <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener"><strong>Ouvrir en plein écran ↗</strong></a></p>';
		echo '<iframe src="' . esc_url( $url ) . '" title="Application Alliance Groupe" style="width:100%;height:calc(100vh - 170px);min-height:620px;border:1px solid #dcdcde;border-radius:10px;background:#0b0b0f;"></iframe>';
		echo '</div>';
	}
}
