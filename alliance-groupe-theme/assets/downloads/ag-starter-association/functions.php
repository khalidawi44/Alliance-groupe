<?php
/**
 * AG Starter Association — functions
 *
 * Theme militant / associatif politique. Tous les textes sont des
 * placeholders entre crochets a personnaliser via le Customizer.
 *
 * @package AG_Starter_Association
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_starter_association_setup' ) ) :
	function ag_starter_association_setup() {
		load_theme_textdomain( 'ag-starter-association', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 80,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
		add_theme_support( 'responsive-embeds' );
	}
endif;
add_action( 'after_setup_theme', 'ag_starter_association_setup' );

function ag_starter_association_assets() {
	wp_enqueue_style(
		'ag-asso-fonts',
		'https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'ag-starter-association-style', get_stylesheet_uri(), array( 'ag-asso-fonts' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'ag-asso-main', get_template_directory_uri() . '/assets/main.css', array( 'ag-starter-association-style' ), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'ag_starter_association_assets' );

/**
 * Helpers de lecture des options Customizer (placeholders [...] par defaut).
 */
function ag_asso_opt( $key, $default = '' ) {
	return get_theme_mod( $key, $default );
}

require_once get_template_directory() . '/inc/customizer.php';
