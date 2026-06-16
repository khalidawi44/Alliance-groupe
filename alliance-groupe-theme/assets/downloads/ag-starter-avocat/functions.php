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
 * Menu de secours : si AUCUN menu n'est assigne a l'emplacement "primary"
 * (ce qui arrive a chaque changement de theme, les emplacements etant lies
 * au theme actif), on liste automatiquement les pages du site. Ainsi le
 * menu n'est JAMAIS vide — il suffira ensuite, si on veut, de creer un menu
 * sur-mesure dans Apparence > Menus.
 */
if ( ! function_exists( 'ag_starter_avocat_menu_fallback' ) ) :
	function ag_starter_avocat_menu_fallback() {
		// On exclut les pages utilitaires (legal, e-commerce) qui n'ont rien
		// a faire dans le menu principal, puis on limite a 5 onglets pour
		// rester epure.
		$exclude_slugs = array(
			'mentions-legales', 'politique-de-confidentialite', 'confidentialite',
			'politique-de-cookies', 'cookies', 'cgv', 'cgu', 'conditions-generales',
			'plan-du-site', 'panier', 'commande', 'mon-compte', 'checkout', 'cart',
			'my-account', 'livraison', 'retours', 'remboursement',
		);
		$exclude_ids = array();
		foreach ( $exclude_slugs as $slug ) {
			$p = get_page_by_path( $slug );
			if ( $p ) {
				$exclude_ids[] = $p->ID;
			}
		}
		$pages = wp_list_pages( array(
			'echo'        => false,
			'title_li'    => '',
			'depth'       => 1,
			'sort_column' => 'menu_order, post_title',
			'number'      => 5,
			'exclude'     => implode( ',', $exclude_ids ),
		) );
		if ( $pages ) {
			echo '<ul class="ag-primary-menu">' . $pages . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		// Aucune Page reelle (import de contenu pas encore lance) : on affiche
		// un menu par defaut pointant vers les sections standard du cabinet,
		// pour que le menu ne soit JAMAIS vide.
		$defaults = array(
			''            => __( 'Accueil', 'ag-starter-avocat' ),
			'expertise'   => __( "Domaines d'expertise", 'ag-starter-avocat' ),
			'honoraires'  => __( 'Honoraires', 'ag-starter-avocat' ),
			'cabinet'     => __( 'Le cabinet', 'ag-starter-avocat' ),
			'rendez-vous' => __( 'Prendre rendez-vous', 'ag-starter-avocat' ),
		);
		echo '<ul class="ag-primary-menu">';
		foreach ( $defaults as $slug => $label ) {
			$url = $slug ? home_url( '/' . $slug . '/' ) : home_url( '/' );
			echo '<li class="menu-item"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul>';
	}
endif;

/**
 * Helper: read a Customizer option (alias for ag_starter_avocat_get_option
 * with a runtime fallback default for safety).
 */
if ( ! function_exists( 'ag_avocat_opt' ) ) {
	function ag_avocat_opt( $key, $default = '' ) {
		return get_theme_mod( $key, $default );
	}
}

/**
 * Lit le titre + l'intro d'une section directement depuis la page WP
 * correspondante (Pages > [slug]). Ainsi l'utilisateur edite UNIQUEMENT
 * la page : titre = H2 de la section, premier paragraphe = lead.
 *
 * @param string $slug          Slug de la page
 * @param string $fallback_h2   Titre de secours si la page n'existe pas
 * @param string $fallback_lead Intro de secours
 * @return array [titre, intro]
 */
if ( ! function_exists( 'ag_avocat_page_section_text' ) ) {
	function ag_avocat_page_section_text( $slug, $fallback_h2, $fallback_lead = '' ) {
		$page  = get_page_by_path( $slug );
		$title = $fallback_h2;
		$lead  = $fallback_lead;
		if ( $page ) {
			$title = get_the_title( $page->ID ) ?: $fallback_h2;
			if ( $page->post_excerpt ) {
				$lead = $page->post_excerpt;
			} else {
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
}

/**
 * Rend un titre H2 avec auto-italique du dernier mot — preserve le design
 * d'origine (mot final en couleur accent via .ag-section-title em / span).
 */
if ( ! function_exists( 'ag_avocat_render_split_title' ) ) {
	function ag_avocat_render_split_title( $title ) {
		$words = preg_split( '/\s+/', trim( $title ) );
		if ( count( $words ) < 2 ) {
			return esc_html( $title );
		}
		$last  = array_pop( $words );
		$first = implode( ' ', $words );
		return esc_html( $first ) . ' <em>' . esc_html( $last ) . '</em>';
	}
}

/**
 * Get permalink for a page by slug — works with any permalink structure.
 *
 * In Free tier: pages dediees (rendez-vous, expertise, cabinet, honoraires)
 * n'existent pas conceptuellement. On force l'ancre vers la home, meme si
 * une page WP avec ce slug a ete creee par erreur. En Premium+, on utilise
 * la page WP si elle existe, sinon fallback ancre.
 */
function ag_page_url( $slug ) {
	$anchors = array(
		'rendez-vous' => '#ag-rdv',
		'expertise'   => '#ag-domaines',
		'cabinet'     => '#ag-cabinet',
		'honoraires'  => '#ag-honoraires',
	);

	$tier = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_tier() : 'free';

	if ( 'free' !== $tier ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return get_permalink( $page );
		}
	}

	if ( isset( $anchors[ $slug ] ) ) {
		return home_url( '/' . $anchors[ $slug ] );
	}

	return home_url( '/' . $slug . '/' );
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
			<a href="<?php echo esc_url( $search_url ); ?>" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-size:1.05rem;font-weight:700;padding:16px 32px;border-radius:8px;text-decoration:none;box-shadow:0 4px 16px rgba(212,180,92,.3);"><?php esc_html_e( 'Télécharger AG Starter Companion', 'ag-starter-avocat' ); ?></a>
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
		<a href="<?php echo esc_url( $search_url ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'Télécharger AG Starter Companion', 'ag-starter-avocat' ); ?></a>
		<p style="color:#999;font-size:.85rem;margin-top:12px;"><?php esc_html_e( 'Gratuit — 10 secondes — aucune inscription', 'ag-starter-avocat' ); ?></p>
	</div>
	<?php
}
add_action( 'wp_dashboard_setup', 'ag_starter_avocat_dashboard_widget' );

// Auto-update via raw GitHub.
require_once get_template_directory() . '/inc/theme-updater.php';

// Guide d'utilisation (admin)
require_once get_template_directory() . '/inc/guide.php';

// Pré-remplissage auto depuis ag-prefill.json (sites generes par le Createur AG)
require_once get_template_directory() . '/inc/ag-prefill.php';

/**
 * Credit footer — attribution Alliance Groupe (lien VISIBLE, conforme Google).
 * Retirable uniquement via le code (pas d'option admin), comme Astra/OceanWP.
 */
if ( ! function_exists( 'ag_avocat_credit' ) ) :
	function ag_avocat_credit() {
		echo '<p class="ag-credit"><small>';
		printf(
			wp_kses( __( 'Site realise par %s', 'ag-starter-avocat' ), array( 'a' => array( 'href' => array(), 'title' => array() ) ) ),
			'<a href="https://alliancegroupe-inc.com/wordpress-avocat" title="Site WordPress pour avocat par Alliance Groupe">Alliance Groupe</a>'
		);
		echo ' &middot; <a href="https://alliancegroupe-inc.com/bureau-nantes" title="Agence web Alliance Groupe Nantes">' . esc_html__( 'Agence Web Nantes & Marrakech', 'ag-starter-avocat' ) . '</a>';
		echo '</small></p>';
	}
endif;

/* ── AG : mises à jour AUTOMATIQUES de tous les composants Alliance Groupe ──
 * Plus aucun update manuel : WordPress installe en arrière-plan toute MAJ d'un
 * thème ou plugin dont le slug commence par « ag- ». (Nécessite FS_METHOD
 * 'direct' sur l'hébergement pour écrire les fichiers sans demander de FTP.) */
if ( ! function_exists( 'ag_force_auto_updates' ) ) {
	function ag_force_auto_updates( $update, $item ) {
		$slug = ( is_object( $item ) && ! empty( $item->slug ) ) ? (string) $item->slug : '';
		return ( 0 === strpos( $slug, 'ag-' ) ) ? true : $update;
	}
	add_filter( 'auto_update_plugin', 'ag_force_auto_updates', 10, 2 );
	add_filter( 'auto_update_theme',  'ag_force_auto_updates', 10, 2 );
	add_action( 'admin_init', function () {
		if ( ! wp_next_scheduled( 'wp_update_plugins' ) ) { wp_schedule_single_event( time() + 60, 'wp_update_plugins' ); }
		if ( ! wp_next_scheduled( 'wp_update_themes' ) )  { wp_schedule_single_event( time() + 60, 'wp_update_themes' ); }
	} );
}
