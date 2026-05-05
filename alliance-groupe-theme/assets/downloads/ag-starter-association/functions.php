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
		register_nav_menus( array(
			'primary' => __( 'Menu principal',  'ag-starter-association' ),
			'footer'  => __( 'Menu pied de page', 'ag-starter-association' ),
		) );
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

	if ( is_singular( 'ag_evenement' ) ) {
		wp_enqueue_script( 'ag-asso-event', get_template_directory_uri() . '/assets/event.js', array(), wp_get_theme()->get( 'Version' ), true );
		wp_localize_script( 'ag-asso-event', 'agEvtCfg', array(
			'ajax' => admin_url( 'admin-ajax.php' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'ag_starter_association_assets' );

/**
 * AJAX endpoint : incremente compteur participants/supports d'un event.
 * Anti-double-click cote serveur via cookie de signature ; cote client
 * le bouton est bloque apres clic. Pas de login requis.
 */
function ag_asso_event_count() {
	$pid    = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
	$action = isset( $_POST['kind'] )     ? sanitize_key( $_POST['kind'] ) : '';
	$nonce  = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
	if ( ! $pid || ! wp_verify_nonce( $nonce, 'ag_event_count' ) ) {
		wp_send_json_error( array( 'msg' => 'Invalid request' ), 400 );
	}
	if ( get_post_type( $pid ) !== 'ag_evenement' ) {
		wp_send_json_error( array( 'msg' => 'Unknown event' ), 404 );
	}
	$key = ( $action === 'support' ) ? '_ag_event_supports' : '_ag_event_participants';
	$cookie = 'ag_evt_' . $pid . '_' . $action;
	if ( isset( $_COOKIE[ $cookie ] ) ) {
		wp_send_json_error( array( 'msg' => 'Already counted', 'count' => (int) get_post_meta( $pid, $key, true ) ), 200 );
	}
	$current = (int) get_post_meta( $pid, $key, true );
	$new     = $current + 1;
	update_post_meta( $pid, $key, $new );
	setcookie( $cookie, '1', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	wp_send_json_success( array( 'count' => $new ) );
}
add_action( 'wp_ajax_ag_event_count',        'ag_asso_event_count' );
add_action( 'wp_ajax_nopriv_ag_event_count', 'ag_asso_event_count' );

/**
 * Helpers de lecture des options Customizer (placeholders [...] par defaut).
 */
function ag_asso_opt( $key, $default = '' ) {
	return get_theme_mod( $key, $default );
}

/**
 * Retourne l'URL d'une page si elle existe (via Pack Fidelite), sinon
 * une ancre de fallback. Permet au theme de fonctionner en standalone
 * (mode one-page avec ancres) ET avec le plugin Pack Fidelite (vraies
 * pages separees /manifeste/, /combats/, etc.).
 */
function ag_asso_link( $slug, $fallback_anchor = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}
	return $fallback_anchor !== '' ? $fallback_anchor : '#' . $slug;
}

require_once get_template_directory() . '/inc/customizer.php';
