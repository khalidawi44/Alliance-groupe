<?php
/**
 * AG Starter Avocat functions and definitions.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_starter_avocat_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function ag_starter_avocat_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_theme_textdomain( 'ag-starter-avocat', get_template_directory() . '/languages' );

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
				'primary' => esc_html__( 'Menu principal', 'ag-starter-avocat' ),
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'ag_starter_avocat_setup' );

/**
 * Get permalink for a page by slug — works with any permalink structure.
 */
function ag_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Enqueue scripts and styles.
 */
function ag_starter_avocat_scripts() {
	wp_enqueue_style(
		'ag-starter-avocat-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'ag_starter_avocat_scripts' );

/**
 * Disable comments site-wide.
 */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'comments' );
}, 999 );

/**
 * Remove all default WordPress widgets, unregister all sidebars, hide widget admin.
 */
add_action( 'widgets_init', function () {
	global $wp_registered_sidebars;
	$wp_registered_sidebars = array();
	unregister_widget( 'WP_Widget_Pages' );
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Archives' );
	unregister_widget( 'WP_Widget_Meta' );
	unregister_widget( 'WP_Widget_Search' );
	unregister_widget( 'WP_Widget_Categories' );
	unregister_widget( 'WP_Widget_Recent_Posts' );
	unregister_widget( 'WP_Widget_Recent_Comments' );
	unregister_widget( 'WP_Widget_RSS' );
	unregister_widget( 'WP_Widget_Tag_Cloud' );
	unregister_widget( 'WP_Widget_Text' );
	unregister_widget( 'WP_Widget_HTML' );
	unregister_widget( 'WP_Widget_Custom_HTML' );
	unregister_widget( 'WP_Widget_Block' );
	unregister_widget( 'WP_Widget_Media_Audio' );
	unregister_widget( 'WP_Widget_Media_Video' );
	unregister_widget( 'WP_Widget_Media_Image' );
	unregister_widget( 'WP_Widget_Media_Gallery' );
	unregister_widget( 'WP_Nav_Menu_Widget' );
}, 99 );
add_action( 'admin_menu', function () {
	remove_submenu_page( 'themes.php', 'widgets.php' );
}, 99 );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function ag_starter_avocat_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'ag_starter_avocat_pingback_header' );

/**
 * Load the customizer (panels, sections, settings) and its dynamic CSS output.
 */
require get_template_directory() . '/inc/customizer.php';

require get_template_directory() . '/inc/class-ag-licence-client.php';
require get_template_directory() . '/inc/class-ag-updater.php';
require get_template_directory() . '/inc/pro-features.php';

add_action( 'after_setup_theme', function () {
    AG_Licence_Client::register_admin();
    new AG_Theme_Updater( 'ag-starter-avocat', wp_get_theme()->get( 'Version' ) );
    $GLOBALS['ag_pro'] = new AG_Pro_Features( 'ag-starter-avocat' );
}, 20 );

/**
 * Load the Domaine d'expertise CPT and the front-end form handler.
 */
require get_template_directory() . '/inc/cpt-domaine.php';
require get_template_directory() . '/inc/forms.php';

/**
 * Show an admin notice inviting the user to install the companion plugin
 * which provides the one-click demo importer.
 */
function ag_starter_avocat_companion_notice() {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	if ( class_exists( 'AG_Starter_Companion' ) ) {
		return;
	}
	$search_url = 'https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip';
	?>
	<div class="ag-welcome-banner" style="background:linear-gradient(135deg,#1a1a2e 0%,#0a0a0f 100%);border:1px solid rgba(212,180,92,.3);border-left:4px solid #D4B45C;border-radius:8px;padding:40px 36px;margin:20px 20px 20px 0;display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
		<div style="flex:1;min-width:280px;">
			<h2 style="color:#fff;font-size:1.6rem;margin:0 0 12px;">🎉 <?php esc_html_e( 'Bienvenue dans AG Starter Avocat !', 'ag-starter-avocat' ); ?></h2>
			<p style="color:rgba(255,255,255,.7);font-size:1.05rem;line-height:1.6;margin:0 0 8px;"><?php esc_html_e( 'Votre theme est installe. Pour un site pret a l\'emploi en 1 clic (pages, menu, reglages), installez le plugin gratuit AG Starter Companion.', 'ag-starter-avocat' ); ?></p>
			<ul style="color:rgba(255,255,255,.6);font-size:.92rem;margin:12px 0 0;padding-left:18px;">
				<li><?php esc_html_e( 'Pages creees automatiquement (Accueil, Expertise, Honoraires, Cabinet, Rendez-vous)', 'ag-starter-avocat' ); ?></li>
				<li><?php esc_html_e( 'Menu principal configure et assigne', 'ag-starter-avocat' ); ?></li>
				<li><?php esc_html_e( 'Page d\'accueil et permaliens actives', 'ag-starter-avocat' ); ?></li>
				<li><?php esc_html_e( '100% gratuit, aucune connexion internet requise', 'ag-starter-avocat' ); ?></li>
			</ul>
		</div>
		<div style="text-align:center;">
			<a href="<?php echo esc_url( $search_url ); ?>" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-size:1.05rem;font-weight:700;padding:16px 32px;border-radius:8px;text-decoration:none;box-shadow:0 4px 16px rgba(212,180,92,.3);"><?php esc_html_e( 'Télécharger AG Starter Companion →', 'ag-starter-avocat' ); ?></a>
			<p style="color:rgba(255,255,255,.4);font-size:.8rem;margin-top:10px;"><?php esc_html_e( 'Téléchargez le ZIP, puis Extensions → Ajouter → Téléverser', 'ag-starter-avocat' ); ?></p>
		</div>
	</div>
	<?php
}
add_action( 'admin_notices', 'ag_starter_avocat_companion_notice' );

function ag_starter_avocat_dashboard_widget() {
	if ( class_exists( 'AG_Starter_Companion' ) ) return;
	wp_add_dashboard_widget( 'ag_starter_welcome', esc_html__( '🚀 AG Starter Avocat — Configuration', 'ag-starter-avocat' ), 'ag_starter_avocat_dashboard_widget_render' );
	global $wp_meta_boxes;
	$widget = $wp_meta_boxes['dashboard']['normal']['core']['ag_starter_welcome'];
	unset( $wp_meta_boxes['dashboard']['normal']['core']['ag_starter_welcome'] );
	$wp_meta_boxes['dashboard']['normal']['high']['ag_starter_welcome'] = $widget;
}
function ag_starter_avocat_dashboard_widget_render() {
	$search_url = 'https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip';
	?>
	<div style="text-align:center;padding:20px 0;">
		<p style="font-size:1.15rem;margin:0 0 16px;"><strong><?php esc_html_e( 'Votre theme est pret !', 'ag-starter-avocat' ); ?></strong></p>
		<p style="color:#666;margin:0 0 20px;"><?php esc_html_e( 'Installez le plugin gratuit AG Starter Companion pour creer automatiquement vos pages, votre menu et configurer votre site en 1 clic.', 'ag-starter-avocat' ); ?></p>
		<a href="<?php echo esc_url( $search_url ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'Télécharger AG Starter Companion →', 'ag-starter-avocat' ); ?></a>
		<p style="color:#999;font-size:.85rem;margin-top:12px;"><?php esc_html_e( 'Gratuit — 10 secondes — aucune inscription', 'ag-starter-avocat' ); ?></p>
	</div>
	<?php
}
add_action( 'wp_dashboard_setup', 'ag_starter_avocat_dashboard_widget' );
