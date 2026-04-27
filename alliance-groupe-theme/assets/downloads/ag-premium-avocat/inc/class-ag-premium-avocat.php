<?php
/**
 * Main Premium class. Singleton.
 *
 * @package AG_Premium_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Premium_Avocat {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Hooks are always registered; tier check is lazy inside each
		// callback so that AG_Licence_Client (loaded by the Free theme)
		// is guaranteed to exist by the time the callback fires.
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
		add_action( 'wp_head', array( $this, 'output_domaine_bg_overrides' ), 99 );

		// One-shot DB setup: create the dedicated Premium pages and sync
		// the primary menu to point to them. Runs lazily on admin_init
		// so the work happens when the admin browses, not on every front
		// page load. Tracked via the option ag_premium_setup_done.
		add_action( 'admin_init', array( $this, 'ensure_pages_and_menu' ) );
	}

	/**
	 * Lazy tier check. Active si AG_Licence_Client::get_tier() retourne
	 * premium ou business. Si la classe n'existe pas (Free seul installé,
	 * licence non chargée), le plugin reste inactif.
	 */
	private function is_active() {
		if ( ! class_exists( 'AG_Licence_Client' ) ) {
			return false;
		}
		$tier = AG_Licence_Client::get_tier();
		return in_array( $tier, array( 'premium', 'business' ), true );
	}

	public function add_body_class( $classes ) {
		if ( ! $this->is_active() ) {
			return $classes;
		}
		$classes[] = 'ag-premium-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( file_exists( AG_PREMIUM_AVOCAT_DIR . 'assets/premium.css' ) ) {
			wp_enqueue_style(
				'ag-premium-avocat-style',
				AG_PREMIUM_AVOCAT_URL . 'assets/premium.css',
				array(),
				AG_PREMIUM_AVOCAT_VERSION
			);
		}
		if ( file_exists( AG_PREMIUM_AVOCAT_DIR . 'assets/premium.js' ) ) {
			wp_enqueue_script(
				'ag-premium-avocat-script',
				AG_PREMIUM_AVOCAT_URL . 'assets/premium.js',
				array(),
				AG_PREMIUM_AVOCAT_VERSION,
				true
			);
		}
	}

	/**
	 * Print inline CSS that overrides the background image of each
	 * "Domaines d'expertise" card on the front page. Strategy:
	 *
	 *   - We do NOT touch the Free template (which is locked).
	 *   - We re-query the same domaine list as Free (same order).
	 *   - For each card position (1..N), we emit a rule scoped to
	 *     `body.ag-premium-active` that overrides the inline
	 *     `style="background-image:..."` with !important.
	 *   - If a domain has its own featured image, we leave it alone.
	 *   - Slug match wins; otherwise we fall back to a per-position pool.
	 */
	public function output_domaine_bg_overrides() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! function_exists( 'ag_starter_avocat_get_domaines' ) ) {
			return;
		}
		$domaines = ag_starter_avocat_get_domaines( 12 );
		if ( ! $domaines ) {
			return;
		}

		$slug_map = $this->get_premium_domaine_slug_map();
		$pool     = $this->get_premium_domaine_pool();
		$css      = '';
		$idx      = 0;

		foreach ( $domaines as $d ) {
			$idx++;
			if ( has_post_thumbnail( $d->ID ) ) {
				continue; // user-uploaded image wins
			}
			$slug = $d->post_name;
			$url  = isset( $slug_map[ $slug ] ) ? $slug_map[ $slug ] : $pool[ ( $idx - 1 ) % count( $pool ) ];
			$css .= sprintf(
				"body.ag-premium-active .ag-domaines__grid > .ag-domaine-card:nth-child(%d) .ag-domaine-card__bg{background-image:url('%s')!important;}\n",
				$idx,
				esc_url_raw( $url )
			);
		}

		if ( $css ) {
			echo "<style id=\"ag-premium-domaine-bg\">\n" . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Map of common French legal-domain slugs to dedicated Premium photos.
	 */
	private function get_premium_domaine_slug_map() {
		return array(
			'droit-des-affaires'           => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1600&q=85',
			'droit-du-travail'             => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1600&q=85',
			'droit-de-la-famille'          => 'https://images.unsplash.com/photo-1609220136736-443140cffec6?w=1600&q=85',
			'droit-immobilier'             => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1600&q=85',
			'droit-penal'                  => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1600&q=85',
			'droit-fiscal'                 => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1600&q=85',
			'droit-international'          => 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=1600&q=85',
			'droit-de-la-securite-sociale' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1600&q=85',
			'droit-des-successions'        => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1600&q=85',
			'droit-du-numerique'           => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1600&q=85',
			'droit-bancaire'               => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?w=1600&q=85',
			'droit-de-la-consommation'     => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1600&q=85',
		);
	}

	/**
	 * Per-position fallback pool: 12 distinct curated photos.
	 */
	private function get_premium_domaine_pool() {
		return array(
			'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1600&q=85',
			'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1600&q=85',
			'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1600&q=85',
			'https://images.unsplash.com/photo-1609220136736-443140cffec6?w=1600&q=85',
			'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1600&q=85',
			'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1600&q=85',
			'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1600&q=85',
			'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=1600&q=85',
			'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1600&q=85',
			'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1600&q=85',
			'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1600&q=85',
			'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?w=1600&q=85',
		);
	}

	/**
	 * Premium-only setup : ensure the 4 dedicated pages exist (Cabinet,
	 * Expertise, Honoraires, Rendez-vous) and sync the primary menu so
	 * its items point to those real pages instead of anchors. Runs once,
	 * tracked by the option `ag_premium_setup_done`.
	 *
	 * - Skips creation for any page that already exists.
	 * - Removes anchor-based custom menu items (`#xxx`).
	 * - Adds a page-typed menu item for each page that isn't already in
	 *   the primary menu.
	 * - If no menu is assigned to the `primary` location, creates one.
	 */
	public function ensure_pages_and_menu() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( get_option( 'ag_premium_setup_done' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return; // only run for an admin so we don't trigger DB writes for visitors
		}

		$page_specs = array(
			'cabinet'     => __( 'Cabinet', 'ag-premium-avocat' ),
			'expertise'   => __( 'Domaines d\'expertise', 'ag-premium-avocat' ),
			'honoraires'  => __( 'Honoraires', 'ag-premium-avocat' ),
			'rendez-vous' => __( 'Prendre rendez-vous', 'ag-premium-avocat' ),
		);

		$page_ids = array();
		foreach ( $page_specs as $slug => $title ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$page_ids[ $slug ] = (int) $existing->ID;
				continue;
			}
			$id = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => '',
			) );
			if ( ! is_wp_error( $id ) && $id ) {
				$page_ids[ $slug ] = (int) $id;
			}
		}

		if ( empty( $page_ids ) ) {
			update_option( 'ag_premium_setup_done', 1 );
			return;
		}

		$locations = (array) get_theme_mod( 'nav_menu_locations' );
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

		if ( ! $menu_id ) {
			$menu_id = wp_create_nav_menu( __( 'Menu principal', 'ag-premium-avocat' ) );
			if ( is_wp_error( $menu_id ) ) {
				update_option( 'ag_premium_setup_done', 1 );
				return;
			}
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		$items                = wp_get_nav_menu_items( $menu_id );
		$existing_page_ids    = array();
		if ( $items ) {
			foreach ( $items as $item ) {
				if ( 'custom' === $item->type && false !== strpos( (string) $item->url, '#' ) ) {
					wp_delete_post( $item->ID, true );
					continue;
				}
				if ( 'post_type' === $item->type && 'page' === $item->object ) {
					$existing_page_ids[] = (int) $item->object_id;
				}
			}
		}

		foreach ( $page_ids as $slug => $id ) {
			if ( in_array( $id, $existing_page_ids, true ) ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $page_specs[ $slug ],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}

		update_option( 'ag_premium_setup_done', 1 );
	}
}
