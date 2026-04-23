<?php
/**
 * AG Starter Barber functions and definitions.
 *
 * @package AG_Starter_Barber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'ag_starter_barber_setup' ) ) :
function ag_starter_barber_setup() {
    load_theme_textdomain( 'ag-starter-barber', get_template_directory() . '/languages' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'custom-logo', array( 'height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true ) );
    add_theme_support( 'custom-background', array( 'default-color' => '0a0a0a' ) );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    register_nav_menus( array( 'primary' => esc_html__( 'Menu principal', 'ag-starter-barber' ) ) );
}
endif;
add_action( 'after_setup_theme', 'ag_starter_barber_setup' );

function ag_starter_barber_widgets_init() {
    register_sidebar( array(
        'name' => esc_html__( 'Barre laterale', 'ag-starter-barber' ),
        'id' => 'sidebar-1',
        'before_widget' => '<section id="%1$s" class="ag-widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="ag-widget-title">',
        'after_title' => '</h2>',
    ) );
}
add_action( 'widgets_init', 'ag_starter_barber_widgets_init' );

function ag_starter_barber_scripts() {
    wp_enqueue_style( 'ag-google-fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap', array(), null );
    wp_enqueue_style( 'ag-starter-barber-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'ag_starter_barber_scripts' );

// Load Queue System
require get_template_directory() . '/inc/queue-system.php';
require get_template_directory() . '/inc/customizer.php';

// Load Licence + Pro
require get_template_directory() . '/inc/class-ag-licence-client.php';
require get_template_directory() . '/inc/class-ag-updater.php';
require get_template_directory() . '/inc/pro-features.php';

add_action( 'after_setup_theme', function () {
    AG_Barber_Queue::register_admin();
    AG_Licence_Client::register_admin();
    new AG_Theme_Updater( 'ag-starter-barber', wp_get_theme()->get( 'Version' ) );
    $GLOBALS['ag_pro'] = new AG_Pro_Features( 'ag-starter-barber' );
}, 20 );

// Handle ?ag_queue=join URL (QR code landing)
add_action( 'template_redirect', function () {
    if ( isset( $_GET['ag_queue'] ) && 'join' === $_GET['ag_queue'] ) {
        get_template_part( 'page-queue' );
        exit;
    }
    if ( isset( $_GET['ag_queue'] ) && 'ticket' === $_GET['ag_queue'] ) {
        get_template_part( 'page-ticket' );
        exit;
    }
} );

// Companion plugin notice
function ag_starter_barber_companion_notice() {
    if ( ! current_user_can( 'install_plugins' ) || class_exists( 'AG_Starter_Companion' ) ) return;
    $url = 'https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip';
    ?>
    <div style="background:linear-gradient(135deg,#1a1a2e 0%,#0a0a0f 100%);border:1px solid rgba(212,180,92,.3);border-left:4px solid #D4B45C;border-radius:8px;padding:40px 36px;margin:20px 20px 20px 0;display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
        <div style="flex:1;min-width:280px;">
            <h2 style="color:#fff;font-size:1.6rem;margin:0 0 12px;">💈 <?php esc_html_e( 'Bienvenue dans AG Starter Barber !', 'ag-starter-barber' ); ?></h2>
            <p style="color:rgba(255,255,255,.7);font-size:1.05rem;line-height:1.6;margin:0;"><?php esc_html_e( 'Installez le plugin AG Starter Companion pour importer les pages demo en 1 clic. Le systeme de file d\'attente QR code est deja actif.', 'ag-starter-barber' ); ?></p>
        </div>
        <a href="<?php echo esc_url( $url ); ?>" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-size:1.05rem;font-weight:700;padding:16px 32px;border-radius:8px;text-decoration:none;"><?php esc_html_e( 'Télécharger AG Starter Companion →', 'ag-starter-barber' ); ?></a>
    </div>
    <?php
}
add_action( 'admin_notices', 'ag_starter_barber_companion_notice' );

// Dashboard widget
function ag_starter_barber_dashboard_widget() {
    if ( class_exists( 'AG_Starter_Companion' ) ) return;
    wp_add_dashboard_widget( 'ag_starter_welcome', '💈 AG Starter Barber — Configuration', 'ag_starter_barber_dashboard_render' );
}
function ag_starter_barber_dashboard_render() {
    $url = 'https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip';
    echo '<div style="text-align:center;padding:20px 0;"><p style="font-size:1.15rem;"><strong>Votre thème barber est prêt !</strong></p><a href="' . esc_url( $url ) . '" class="button button-primary button-hero">Télécharger AG Starter Companion →</a></div>';
}
add_action( 'wp_dashboard_setup', 'ag_starter_barber_dashboard_widget' );
