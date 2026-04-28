<?php
/**
 * Main Business class. Singleton.
 *
 * @package AG_Business_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Business_Avocat {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Hooks always registered; tier check lazy inside each callback.
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
		add_action( 'wp_head', array( $this, 'output_domaine_bg_overrides' ), 99 );
		add_action( 'init', array( $this, 'reorder_sections' ), 99 );
		add_action( 'admin_init', array( $this, 'ensure_boutique_page' ) );
		add_action( 'admin_notices', array( $this, 'woocommerce_admin_notice' ) );
	}

	/**
	 * Lazy tier check. Active uniquement si tier === business.
	 */
	private function is_active() {
		if ( ! class_exists( 'AG_Licence_Client' ) ) {
			return false;
		}
		return 'business' === AG_Licence_Client::get_tier();
	}

	public function add_body_class( $classes ) {
		if ( ! $this->is_active() ) {
			return $classes;
		}
		$classes[] = 'ag-business-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( file_exists( AG_BUSINESS_AVOCAT_DIR . 'assets/business.css' ) ) {
			wp_enqueue_style(
				'ag-business-avocat-style',
				AG_BUSINESS_AVOCAT_URL . 'assets/business.css',
				array(),
				AG_BUSINESS_AVOCAT_VERSION
			);
		}
		if ( file_exists( AG_BUSINESS_AVOCAT_DIR . 'assets/business.js' ) ) {
			wp_enqueue_script(
				'ag-business-avocat-script',
				AG_BUSINESS_AVOCAT_URL . 'assets/business.js',
				array(),
				AG_BUSINESS_AVOCAT_VERSION,
				true
			);
			wp_localize_script( 'ag-business-avocat-script', 'agBusinessData', array(
				'honorairesUrl' => function_exists( 'ag_page_url' ) ? ag_page_url( 'honoraires' ) : home_url( '/honoraires/' ),
			) );
		}
	}

	/**
	 * Print inline CSS that overrides the background image of each
	 * "Domaines d'expertise" card on the front page in Business tier.
	 *
	 * Self-contained : marche meme si le plugin Premium n'est pas
	 * active. Lit les images Customizer Premium si presentes (compat),
	 * sinon utilise une slug-map et un pool curatee identique a Premium.
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
		$slug_map = array(
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
		$pool = array(
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

		$css = '';
		$idx = 0;
		foreach ( $domaines as $d ) {
			$idx++;
			if ( has_post_thumbnail( $d->ID ) ) {
				continue;
			}
			$slug = $d->post_name;
			// 1) override Customizer Premium (compat), 2) slug map, 3) pool
			$customizer_key = 'ag_premium_img_' . str_replace( '-', '_', $slug );
			$customizer_url = (string) get_theme_mod( $customizer_key, '' );
			if ( $customizer_url ) {
				$url = $customizer_url;
			} elseif ( isset( $slug_map[ $slug ] ) ) {
				$url = $slug_map[ $slug ];
			} else {
				$url = $pool[ ( $idx - 1 ) % count( $pool ) ];
			}
			$css .= sprintf(
				"body.ag-business-active .ag-domaines__grid > .ag-domaine-card:nth-child(%d) .ag-domaine-card__bg{background-image:url('%s')!important;}\n",
				$idx,
				esc_url_raw( $url )
			);
		}
		if ( $css ) {
			echo "<style id=\"ag-business-domaine-bg\">\n" . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Place la section "Equipe" juste apres la section "Cabinet" au lieu
	 * de juste apres "Le Maitre". Detache l'ancien hook et le rebranche.
	 *
	 * AG_Pro_Features (theme Free) registre render_team_section sur
	 * ag_after_maitre @ 10. On detache et on rebranche sur ag_after_cabinet
	 * a une priorite basse (5) pour que l'equipe arrive AVANT boutique
	 * (priorite 10) et parallax_quote_3 (priorite 20).
	 */
	public function reorder_sections() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! isset( $GLOBALS['ag_pro'] ) ) {
			return;
		}
		$pro = $GLOBALS['ag_pro'];
		if ( ! is_object( $pro ) || ! method_exists( $pro, 'render_team_section' ) ) {
			return;
		}
		remove_action( 'ag_after_maitre', array( $pro, 'render_team_section' ), 10 );
		add_action( 'ag_after_cabinet', array( $pro, 'render_team_section' ), 5 );
	}

	/**
	 * Cree la page "Boutique" en Business si elle n'existe pas. Le contenu
	 * pose un shortcode WooCommerce — fallback texte si WC absent.
	 */
	public function ensure_boutique_page() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$existing = get_page_by_path( 'boutique' );
		if ( $existing ) {
			return;
		}
		$content = class_exists( 'WooCommerce' )
			? '[products limit="12" columns="3"]'
			: '<!-- WooCommerce non installe — la boutique apparaitra ici une fois le plugin active. -->';
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Boutique', 'ag-business-avocat' ),
			'post_name'    => 'boutique',
			'post_content' => $content,
		) );
	}

	/**
	 * Affiche un admin notice qui propose d'installer WooCommerce quand
	 * Business est actif mais que WooCommerce ne l'est pas. Lien direct
	 * vers l'installation 1-clic via update.php.
	 */
	public function woocommerce_admin_notice() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		if ( class_exists( 'WooCommerce' ) ) {
			return;
		}
		$install_url = wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ),
			'install-plugin_woocommerce'
		);
		$browse_url = self_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
		?>
		<div class="notice notice-info" style="border-left-color:#D4B45C;">
			<p style="font-size:14px;">
				<strong><?php esc_html_e( 'AG Business :', 'ag-business-avocat' ); ?></strong>
				<?php esc_html_e( 'pour activer la page Boutique, installez WooCommerce.', 'ag-business-avocat' ); ?>
				&nbsp;
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary"><?php esc_html_e( 'Installer WooCommerce', 'ag-business-avocat' ); ?></a>
				<a href="<?php echo esc_url( $browse_url ); ?>" class="button"><?php esc_html_e( 'Voir dans la liste', 'ag-business-avocat' ); ?></a>
			</p>
		</div>
		<?php
	}
}
