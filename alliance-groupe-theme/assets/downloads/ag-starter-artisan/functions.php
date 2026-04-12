<?php
/**
 * AG Starter Artisan functions and definitions.
 *
 * @package AG_Starter_Artisan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_starter_artisan_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function ag_starter_artisan_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_theme_textdomain( 'ag-starter-artisan', get_template_directory() . '/languages' );

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
				'primary' => esc_html__( 'Menu principal', 'ag-starter-artisan' ),
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'ag_starter_artisan_setup' );

/**
 * Register widget area.
 */
function ag_starter_artisan_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Barre laterale', 'ag-starter-artisan' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Ajoutez vos widgets ici.', 'ag-starter-artisan' ),
			'before_widget' => '<section id="%1$s" class="ag-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="ag-widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'ag_starter_artisan_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ag_starter_artisan_scripts() {
	wp_enqueue_style(
		'ag-starter-artisan-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ag_starter_artisan_scripts' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function ag_starter_artisan_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'ag_starter_artisan_pingback_header' );
