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
	private $is_active       = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->is_active = $this->detect_tier();
		if ( ! $this->is_active ) {
			return;
		}

		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
		add_action( 'wp_head', array( $this, 'output_domaine_bg_overrides' ), 99 );

		// Premium-only hook handlers go below. Keep each one in its own
		// method, gated by $this->is_active (already enforced by the early
		// return above). Use .ag-premium-* CSS classes only.
	}

	/**
	 * Active si AG_Licence_Client détecte un tier >= premium. Si la classe
	 * n'existe pas (Free seul installé), le plugin reste inactif.
	 */
	private function detect_tier() {
		if ( ! class_exists( 'AG_Licence_Client' ) ) {
			return false;
		}
		$tier = AG_Licence_Client::get_tier();
		return in_array( $tier, array( 'premium', 'business' ), true );
	}

	public function add_body_class( $classes ) {
		$classes[] = 'ag-premium-active';
		return $classes;
	}

	public function enqueue_assets() {
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
}
