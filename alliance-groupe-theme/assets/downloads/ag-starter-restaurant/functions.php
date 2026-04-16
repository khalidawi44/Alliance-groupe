<?php
/**
 * AG Starter Restaurant functions and definitions.
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_starter_restaurant_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function ag_starter_restaurant_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_theme_textdomain( 'ag-starter-restaurant', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Let WordPress manage the document title.
		add_theme_support( 'title-tag' );

		// Enable support for Post Thumbnails on posts and pages.
		add_theme_support( 'post-thumbnails' );

		// Switch default core markup to output valid HTML5.
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'navigation-widgets',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for core custom logo.
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 60,
				'width'       => 200,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// Add support for custom background.
		add_theme_support(
			'custom-background',
			array(
				'default-color' => '0a0a0a',
			)
		);

		// Add support for responsive embeds.
		add_theme_support( 'responsive-embeds' );

		// Add support for wide and full alignment.
		add_theme_support( 'align-wide' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary' => esc_html__( 'Menu principal', 'ag-starter-restaurant' ),
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'ag_starter_restaurant_setup' );

/**
 * Register widget area.
 */
function ag_starter_restaurant_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Barre laterale', 'ag-starter-restaurant' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Ajoutez vos widgets ici.', 'ag-starter-restaurant' ),
			'before_widget' => '<section id="%1$s" class="ag-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="ag-widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'ag_starter_restaurant_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ag_starter_restaurant_scripts() {
	wp_enqueue_style(
		'ag-starter-restaurant-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ag_starter_restaurant_scripts' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function ag_starter_restaurant_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'ag_starter_restaurant_pingback_header' );

/**
 * Load the customizer (panels, sections, settings) and its dynamic CSS output.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load licence client + auto-updater.
 */
require get_template_directory() . '/inc/class-ag-licence-client.php';
require get_template_directory() . '/inc/class-ag-updater.php';
require get_template_directory() . '/inc/pro-features.php';

add_action( 'after_setup_theme', function () {
    AG_Licence_Client::register_admin();
    new AG_Theme_Updater( 'ag-starter-restaurant', wp_get_theme()->get( 'Version' ) );
    new AG_Pro_Features( 'ag-starter-restaurant' );
}, 20 );

// Enqueue Pro JS if licence active
add_action( 'wp_enqueue_scripts', function () {
    if ( class_exists( 'AG_Licence_Client' ) && AG_Licence_Client::get_tier() !== 'free' ) {
        wp_enqueue_script(
            'ag-pro-scripts',
            get_template_directory_uri() . '/inc/pro-scripts.js',
            array(),
            '2.0.0',
            true
        );
    }
} );

/**
 * Show an admin notice inviting the user to install the companion plugin
 * which provides the one-click demo importer.
 */
function ag_starter_restaurant_companion_notice() {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	if ( class_exists( 'AG_Starter_Companion' ) ) {
		return;
	}
	$search_url = admin_url( 'plugin-install.php?s=ag-starter-companion&tab=search&type=term' );
	echo '<div class="notice notice-info is-dismissible"><p><strong>AG Starter Restaurant</strong> &mdash; ';
	printf(
		/* translators: %s: plugin search link. */
		wp_kses( __( 'Installez le plugin gratuit %s pour importer en un clic les pages, le menu et les reglages de ce theme.', 'ag-starter-restaurant' ), array( 'a' => array( 'href' => array() ), 'strong' => array() ) ),
		'<a href="' . esc_url( $search_url ) . '"><strong>AG Starter Companion</strong></a>'
	);
	echo '</p></div>';
}
add_action( 'admin_notices', 'ag_starter_restaurant_companion_notice' );
