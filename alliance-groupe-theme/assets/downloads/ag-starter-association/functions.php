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

/**
 * Helpers pour lire le titre + l'intro d'une section directement depuis
 * la page correspondante (Pages > [slug]). Ainsi l'utilisateur edite
 * UNIQUEMENT la page, et son titre + extrait apparaissent automatiquement
 * comme entete de la section sur l'accueil. Plus besoin de Customizer.
 *
 * @param string $slug         Slug de la page (ex: 'combats')
 * @param string $fallback_h2  Titre H2 de secours si la page n'existe pas
 * @param string $fallback_lead Intro de secours si la page n'a pas d'extrait
 * @return array [titre, intro]
 */
function ag_asso_page_section_text( $slug, $fallback_h2, $fallback_lead = '' ) {
	$page = get_page_by_path( $slug );
	$title = $fallback_h2;
	$lead  = $fallback_lead;
	if ( $page ) {
		$title = get_the_title( $page->ID ) ?: $fallback_h2;
		// Excerpt manuel prioritaire si l'utilisateur en a defini un.
		if ( $page->post_excerpt ) {
			$lead = $page->post_excerpt;
		} else {
			// Parse les blocs Gutenberg et extrait UNIQUEMENT le premier
			// paragraphe (skip les headings et shortcodes pour eviter de
			// concatener le H1 + le paragraphe + le shortcode).
			$blocks = function_exists( 'parse_blocks' ) ? parse_blocks( $page->post_content ) : array();
			foreach ( $blocks as $block ) {
				if ( ! empty( $block['blockName'] ) && $block['blockName'] === 'core/paragraph' ) {
					$txt = trim( wp_strip_all_tags( $block['innerHTML'] ) );
					if ( $txt ) {
						$lead = wp_trim_words( $txt, 30, '...' );
						break;
					}
				}
			}
		}
	}
	return array( $title, $lead );
}

/**
 * Rend un titre H2 avec auto-italique du dernier mot (preserve le design
 * d'origine ou le mot final est en couleur accent via .ag-asso-section__title em).
 * Pour des titres courts ou composes, l'utilisateur peut renommer la page.
 */
function ag_asso_render_split_title( $title ) {
	$words = preg_split( '/\s+/', trim( $title ) );
	if ( count( $words ) < 2 ) {
		return esc_html( $title );
	}
	$last  = array_pop( $words );
	$first = implode( ' ', $words );
	return esc_html( $first ) . ' <em>' . esc_html( $last ) . '</em>';
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

	// Popups Action Populaire : ouvre les liens AP (don, adhesion, groupe)
	// dans une fenetre centree au lieu de rediriger. Garde le visiteur
	// visuellement sur le site principal.
	wp_enqueue_script(
		'ag-asso-popups',
		get_template_directory_uri() . '/assets/popups.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	// Animations legeres via anime.js (front-page + interieur). Toggle
	// via Customizer 'ag_asso_enable_animations' (ON par defaut).
	// Respecte prefers-reduced-motion (auto-OFF cote JS pour utilisateurs
	// sensibles). anime.js depuis CDN jsdelivr (16 KB gzip, cache global).
	if ( get_theme_mod( 'ag_asso_enable_animations', 1 ) ) {
		wp_enqueue_script(
			'animejs',
			'https://cdn.jsdelivr.net/npm/animejs@3.2.2/lib/anime.min.js',
			array(),
			'3.2.2',
			true
		);
		wp_enqueue_script(
			'ag-asso-animations',
			get_template_directory_uri() . '/assets/animations.js',
			array( 'animejs' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
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

/**
 * Retourne un visuel pour un post (combat / article / event...) :
 * - Si une featured image existe : URL de la thumbnail
 * - Sinon : data-uri SVG inline avec couleur + emoji thematique
 *   selectionne automatiquement selon des mots-cles dans le titre
 *   (climat, logement, sante, democratie, transparence, egalite,
 *   hopital, petition, AG, etc.).
 */
function ag_asso_post_visual_html( $post_id = null, $size = 'large', $class = 'ag-asso-visual' ) {
	$post_id = $post_id ?: get_the_ID();
	if ( has_post_thumbnail( $post_id ) ) {
		return '<div class="' . esc_attr( $class ) . '">' . get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy' ) ) . '</div>';
	}
	$title = get_the_title( $post_id );
	$slug  = get_post_field( 'post_name', $post_id );
	$haystack = mb_strtolower( $title . ' ' . $slug );

	// Mots-cles -> [ emoji, couleur ]
	$themes = array(
		'climat'        => array( '🌍', '#1F8A3D' ),
		'ecolog'        => array( '🌱', '#1F8A3D' ),
		'logement'      => array( '🏠', '#3B5998' ),
		'loyer'         => array( '🏠', '#3B5998' ),
		'service public'=> array( '🏥', '#E10F1A' ),
		'hopital'       => array( '🏥', '#E10F1A' ),
		'hôpital'       => array( '🏥', '#E10F1A' ),
		'sante'         => array( '🏥', '#E10F1A' ),
		'santé'         => array( '🏥', '#E10F1A' ),
		'ecole'         => array( '🎓', '#3B5998' ),
		'école'         => array( '🎓', '#3B5998' ),
		'democrat'      => array( '🗳️', '#8B1A8B' ),
		'démocrat'      => array( '🗳️', '#8B1A8B' ),
		'vote'          => array( '🗳️', '#8B1A8B' ),
		'transparence'  => array( '🔍', '#0A0A0D' ),
		'egalite'       => array( '⚖️', '#FFD23F' ),
		'égalité'       => array( '⚖️', '#FFD23F' ),
		'discrim'       => array( '🤝', '#FFD23F' ),
		'petition'      => array( '✊', '#E10F1A' ),
		'pétition'      => array( '✊', '#E10F1A' ),
		'march'         => array( '🚶', '#E10F1A' ),
		'manifest'      => array( '✊', '#E10F1A' ),
		'mobilis'       => array( '✊', '#E10F1A' ),
		'assemblee'     => array( '👥', '#3B5998' ),
		'assemblée'     => array( '👥', '#3B5998' ),
		'ag '           => array( '👥', '#3B5998' ),
		'reunion'       => array( '📹', '#1F8A3D' ),
		'réunion'       => array( '📹', '#1F8A3D' ),
		'visio'         => array( '📹', '#1F8A3D' ),
		'groupe local'  => array( '🗺️', '#FFD23F' ),
		'don '          => array( '💛', '#FFD23F' ),
		'budget'        => array( '💰', '#1F8A3D' ),
		'universite'    => array( '🎓', '#3B5998' ),
		'université'    => array( '🎓', '#3B5998' ),
		'atelier'       => array( '🛠️', '#0A0A0D' ),
		'conference'    => array( '🎤', '#8B1A8B' ),
		'conférence'    => array( '🎤', '#8B1A8B' ),
	);
	$emoji = '✊';
	$color = '#E10F1A';
	foreach ( $themes as $kw => $pair ) {
		if ( strpos( $haystack, $kw ) !== false ) { $emoji = $pair[0]; $color = $pair[1]; break; }
	}
	// SVG inline data-uri
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 360" preserveAspectRatio="xMidYMid slice">'
	     . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $color . '"/><stop offset="100%" stop-color="rgba(0,0,0,.55)"/></linearGradient></defs>'
	     . '<rect width="600" height="360" fill="url(#g)"/>'
	     . '<g opacity=".18" fill="#fff">'
	     . '<circle cx="80"  cy="60"  r="3"/><circle cx="540" cy="80" r="2"/><circle cx="120" cy="320" r="2.5"/>'
	     . '<circle cx="500" cy="290" r="3"/><circle cx="300" cy="40"  r="2"/>'
	     . '</g>'
	     . '<text x="300" y="200" text-anchor="middle" font-size="160" dominant-baseline="middle">' . $emoji . '</text>'
	     . '</svg>';
	$datauri = 'data:image/svg+xml;utf8,' . rawurlencode( $svg );
	return '<div class="' . esc_attr( $class ) . ' ag-asso-visual--svg" style="background-image:url(\'' . $datauri . '\');" role="img" aria-label="' . esc_attr( $title ) . '"></div>';
}

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/welcome.php';
require_once get_template_directory() . '/inc/theme-updater.php';
require_once get_template_directory() . '/inc/block-patterns.php';
