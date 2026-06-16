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

/**
 * Helper: read a Customizer option (short alias for ag_starter_artisan_get_option).
 */
if ( ! function_exists( 'ag_artisan_opt' ) ) {
	function ag_artisan_opt( $key, $default = '' ) {
		return get_theme_mod( $key, $default );
	}
}

/**
 * Lit le titre + l'intro d'une section directement depuis la page WP
 * correspondante (Pages > [slug]). L'utilisateur edite la page : titre =
 * H2 de la section, premier paragraphe = lead.
 *
 * @param string $slug          Slug de la page
 * @param string $fallback_h2   Titre de secours
 * @param string $fallback_lead Intro de secours
 * @return array [titre, intro]
 */
if ( ! function_exists( 'ag_artisan_page_section_text' ) ) {
	function ag_artisan_page_section_text( $slug, $fallback_h2, $fallback_lead = '' ) {
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
 * (mot final en couleur accent dans <span>).
 */
if ( ! function_exists( 'ag_artisan_render_split_title' ) ) {
	function ag_artisan_render_split_title( $title ) {
		$words = preg_split( '/\s+/', trim( $title ) );
		if ( count( $words ) < 2 ) {
			return '<span>' . esc_html( $title ) . '</span>';
		}
		$last  = array_pop( $words );
		$first = implode( ' ', $words );
		return esc_html( $first ) . ' <span>' . esc_html( $last ) . '</span>';
	}
}

/**
 * Load the customizer (panels, sections, settings) and its dynamic CSS output.
 */
require get_template_directory() . '/inc/customizer.php';

require get_template_directory() . '/inc/class-ag-licence-client.php';
require get_template_directory() . '/inc/class-ag-updater.php';
require get_template_directory() . '/inc/pro-features.php';

add_action( 'after_setup_theme', function () {
    AG_Licence_Client::register_admin();
    new AG_Theme_Updater( 'ag-starter-artisan', wp_get_theme()->get( 'Version' ) );
    $GLOBALS['ag_pro'] = new AG_Pro_Features( 'ag-starter-artisan' );
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
    if ( class_exists( 'AG_Licence_Client' ) && AG_Licence_Client::get_tier() !== 'free' ) {
        wp_enqueue_script( 'ag-pro-scripts', get_template_directory_uri() . '/inc/pro-scripts.js', array(), '2.0.0', true );
    }
} );

/**
 * Show an admin notice inviting the user to install the companion plugin
 * which provides the one-click demo importer.
 */
function ag_starter_artisan_companion_notice() {
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
			<h2 style="color:#fff;font-size:1.6rem;margin:0 0 12px;">🎉 <?php esc_html_e( 'Bienvenue dans AG Starter Artisan !', 'ag-starter-artisan' ); ?></h2>
			<p style="color:rgba(255,255,255,.7);font-size:1.05rem;line-height:1.6;margin:0 0 8px;"><?php esc_html_e( 'Votre theme est installe. Pour un site pret a l\'emploi en 1 clic (pages, menu, reglages), installez le plugin gratuit AG Starter Companion.', 'ag-starter-artisan' ); ?></p>
			<ul style="color:rgba(255,255,255,.6);font-size:.92rem;margin:12px 0 0;padding-left:18px;">
				<li><?php esc_html_e( 'Pages creees automatiquement (Accueil, Prestations, Realisations, A propos, Contact)', 'ag-starter-artisan' ); ?></li>
				<li><?php esc_html_e( 'Menu principal configure et assigne', 'ag-starter-artisan' ); ?></li>
				<li><?php esc_html_e( 'Page d\'accueil et permaliens actives', 'ag-starter-artisan' ); ?></li>
				<li><?php esc_html_e( '100% gratuit, aucune connexion internet requise', 'ag-starter-artisan' ); ?></li>
			</ul>
		</div>
		<div style="text-align:center;">
			<a href="<?php echo esc_url( $search_url ); ?>" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-size:1.05rem;font-weight:700;padding:16px 32px;border-radius:8px;text-decoration:none;box-shadow:0 4px 16px rgba(212,180,92,.3);"><?php esc_html_e( 'Télécharger AG Starter Companion', 'ag-starter-artisan' ); ?></a>
			<p style="color:rgba(255,255,255,.4);font-size:.8rem;margin-top:10px;"><?php esc_html_e( 'Téléchargez le ZIP, puis Extensions → Ajouter → Téléverser', 'ag-starter-artisan' ); ?></p>
		</div>
	</div>
	<?php
}
add_action( 'admin_notices', 'ag_starter_artisan_companion_notice' );

function ag_starter_artisan_dashboard_widget() {
	if ( class_exists( 'AG_Starter_Companion' ) ) return;
	wp_add_dashboard_widget( 'ag_starter_welcome', esc_html__( '🚀 AG Starter Artisan — Configuration', 'ag-starter-artisan' ), 'ag_starter_artisan_dashboard_widget_render' );
	global $wp_meta_boxes;
	$widget = $wp_meta_boxes['dashboard']['normal']['core']['ag_starter_welcome'];
	unset( $wp_meta_boxes['dashboard']['normal']['core']['ag_starter_welcome'] );
	$wp_meta_boxes['dashboard']['normal']['high']['ag_starter_welcome'] = $widget;
}
function ag_starter_artisan_dashboard_widget_render() {
	$search_url = 'https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip';
	?>
	<div style="text-align:center;padding:20px 0;">
		<p style="font-size:1.15rem;margin:0 0 16px;"><strong><?php esc_html_e( 'Votre theme est pret !', 'ag-starter-artisan' ); ?></strong></p>
		<p style="color:#666;margin:0 0 20px;"><?php esc_html_e( 'Installez le plugin gratuit AG Starter Companion pour creer automatiquement vos pages, votre menu et configurer votre site en 1 clic.', 'ag-starter-artisan' ); ?></p>
		<a href="<?php echo esc_url( $search_url ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'Télécharger AG Starter Companion', 'ag-starter-artisan' ); ?></a>
		<p style="color:#999;font-size:.85rem;margin-top:12px;"><?php esc_html_e( 'Gratuit — 10 secondes — aucune inscription', 'ag-starter-artisan' ); ?></p>
	</div>
	<?php
}
add_action( 'wp_dashboard_setup', 'ag_starter_artisan_dashboard_widget' );

// Auto-update via raw GitHub.
require_once get_template_directory() . '/inc/theme-updater.php';

// Guide d'utilisation (admin)
require_once get_template_directory() . '/inc/guide.php';

// Reinitialisation du theme — bouton "TOUT RESET" qui nettoie les
// residus d'autres templates Alliance Groupe (menus, CPT, pages, mods).
require_once get_template_directory() . '/inc/reset.php';

// Presets metier : Apparence > 🎯 Configuration metier (electricien,
// macon, boulanger, multiservices, generaliste). Pilote la grille
// services 4x2 affichee sur la page d'accueil.
require_once get_template_directory() . '/inc/presets.php';

// Systeme de devis : page /devis/ avec formulaire dynamique + fourchette
// de prix par metier x service + multiplicateur regional. Demandes
// stockees en CPT ag_devis_lead (admin > Devis demandes).
require_once get_template_directory() . '/inc/devis.php';

// Section "Alliance Groupe en vidéo" — affichée en mode premium uniquement.
require_once get_template_directory() . '/inc/promo-video.php';

/**
 * Active les animations scroll-reveal (.ag-anim -> .is-visible) via
 * IntersectionObserver. Inline pour eviter une requete http supplementaire.
 * Respecte prefers-reduced-motion (le CSS .ag-anim ne s'active pas dans ce cas).
 */
add_action( 'wp_footer', function () {
	if ( ! ( class_exists( 'AG_Artisan_Presets' ) && AG_Artisan_Presets::get_active_preset() ) ) {
		return; // animations seulement en mode premium (preset metier applique)
	}
	?>
	<script>
	(function () {
		// === 1. Scroll-reveal animations ===
		if ( ! window.matchMedia || ! window.matchMedia('(prefers-reduced-motion: reduce)').matches ) {
			if ( 'IntersectionObserver' in window ) {
				var io = new IntersectionObserver(function (entries) {
					entries.forEach(function (entry) {
						if ( entry.isIntersecting ) {
							entry.target.classList.add('is-visible');
							io.unobserve(entry.target);
						}
					});
				}, { rootMargin: '0px 0px -50px 0px', threshold: 0.08 });
				document.querySelectorAll('.ag-anim').forEach(function (el) { io.observe(el); });
			} else {
				document.querySelectorAll('.ag-anim').forEach(function (el) { el.classList.add('is-visible'); });
			}
		}

		// === 2. Smart sticky header : hide on scroll down, show on scroll up ===
		var header = document.querySelector('.ag-site-header');
		if ( header ) {
			var lastY = window.scrollY;
			var ticking = false;
			window.addEventListener('scroll', function () {
				if ( ticking ) return;
				ticking = true;
				requestAnimationFrame(function () {
					var y = window.scrollY;
					if ( y > 120 && y > lastY + 5 ) {
						header.classList.add('is-hidden');
					} else if ( y < lastY - 5 ) {
						header.classList.remove('is-hidden');
					}
					if ( y > 50 ) header.classList.add('scrolled'); else header.classList.remove('scrolled');
					lastY = y;
					ticking = false;
				});
			}, { passive: true });
		}

		// === 3. Back-to-top button ===
		var btn = document.createElement('button');
		btn.className = 'ag-backtop';
		btn.type = 'button';
		btn.setAttribute('aria-label', 'Retour en haut');
		btn.innerHTML = '↑';
		document.body.appendChild(btn);
		window.addEventListener('scroll', function () {
			if ( window.scrollY > 400 ) btn.classList.add('is-visible'); else btn.classList.remove('is-visible');
		}, { passive: true });
		btn.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});

		// === 4. Lightbox galerie (page Realisations) ===
		var galleryLinks = document.querySelectorAll('.ag-realisations-card');
		if ( galleryLinks.length ) {
			var images = Array.from(galleryLinks).map(function (a) {
				return { src: a.href, caption: a.querySelector('.ag-realisations-card__caption') ? a.querySelector('.ag-realisations-card__caption').textContent : '' };
			});
			var lb = document.createElement('div');
			lb.className = 'ag-lightbox';
			lb.innerHTML = '<button class="ag-lightbox__close" aria-label="Fermer">×</button>' +
				'<button class="ag-lightbox__prev" aria-label="Précédente">‹</button>' +
				'<img class="ag-lightbox__img" alt="" />' +
				'<button class="ag-lightbox__next" aria-label="Suivante">›</button>' +
				'<p class="ag-lightbox__caption"></p>';
			document.body.appendChild(lb);
			var lbImg = lb.querySelector('.ag-lightbox__img');
			var lbCap = lb.querySelector('.ag-lightbox__caption');
			var current = 0;
			function show(i) {
				current = (i + images.length) % images.length;
				lbImg.src = images[current].src;
				lbCap.textContent = images[current].caption;
			}
			function open(i) { show(i); lb.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
			function close() { lb.classList.remove('is-open'); document.body.style.overflow = ''; }
			galleryLinks.forEach(function (a, i) {
				a.addEventListener('click', function (e) { e.preventDefault(); open(i); });
			});
			lb.querySelector('.ag-lightbox__close').addEventListener('click', close);
			lb.querySelector('.ag-lightbox__prev').addEventListener('click', function (e) { e.stopPropagation(); show(current - 1); });
			lb.querySelector('.ag-lightbox__next').addEventListener('click', function (e) { e.stopPropagation(); show(current + 1); });
			// Click background to close (mais pas sur l'image elle-meme)
			lb.addEventListener('click', function (e) { if ( e.target === lb ) close(); });
			// Keyboard nav
			document.addEventListener('keydown', function (e) {
				if ( ! lb.classList.contains('is-open') ) return;
				if ( e.key === 'Escape' ) close();
				else if ( e.key === 'ArrowLeft' ) show(current - 1);
				else if ( e.key === 'ArrowRight' ) show(current + 1);
			});
		}
	})();
	</script>
	<?php
}, 30 );

/**
 * Credit footer — attribution Alliance Groupe (lien VISIBLE, conforme Google).
 * Retirable uniquement via le code (pas d'option admin), comme Astra/OceanWP.
 */
if ( ! function_exists( 'ag_artisan_credit' ) ) :
	function ag_artisan_credit() {
		echo '<div class="ag-credit"><small>';
		echo '&copy; ' . esc_html( date( 'Y' ) ) . ' &mdash; ';
		printf(
			wp_kses( __( 'Création du site : %s', 'ag-starter-artisan' ), array( 'a' => array( 'href' => array(), 'title' => array() ) ) ),
			'<a href="https://alliancegroupe-inc.com/wordpress-artisan" title="Création de site WordPress pour artisan par Alliance Groupe">Alliance Groupe</a>'
		);
		echo ' &middot; <a href="https://alliancegroupe-inc.com" title="Agence web et IA Alliance Groupe">' . esc_html__( 'Agence Web & IA', 'ag-starter-artisan' ) . '</a>';
		echo '</small></div>';
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
