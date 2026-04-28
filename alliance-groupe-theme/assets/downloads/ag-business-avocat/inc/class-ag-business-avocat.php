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
		add_action( 'admin_init', array( $this, 'ensure_boutique_in_menu' ) );
		add_action( 'admin_init', array( $this, 'ensure_default_domaines' ) );
		add_action( 'admin_init', array( $this, 'ensure_domaines_submenu' ) );
		add_action( 'admin_notices', array( $this, 'woocommerce_admin_notice' ) );
		add_filter( 'wp_nav_menu_args', array( $this, 'allow_submenu_depth' ) );
	}

	/**
	 * Le theme Free demande wp_nav_menu(depth=1) qui n'affiche que les
	 * items top-level. En Business on autorise les sous-menus.
	 */
	public function allow_submenu_depth( $args ) {
		if ( ! $this->is_active() ) {
			return $args;
		}
		if ( isset( $args['theme_location'] ) && 'primary' === $args['theme_location'] ) {
			$args['depth'] = 0;
		}
		return $args;
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
				'boutiqueUrl'   => function_exists( 'ag_page_url' ) ? ag_page_url( 'boutique' ) : home_url( '/boutique/' ),
				'domaineUrls'   => $this->get_domaine_urls(),
			) );
		}
	}

	/**
	 * Liste les permaliens des 12 premiers domaines, dans l'ordre meme
	 * que ce que rend le template Free, pour que :nth-child et l'index
	 * JS pointent vers la bonne URL.
	 */
	private function get_domaine_urls() {
		if ( ! function_exists( 'ag_starter_avocat_get_domaines' ) ) {
			return array();
		}
		$domaines = ag_starter_avocat_get_domaines( 12 );
		$urls     = array();
		foreach ( (array) $domaines as $d ) {
			$urls[] = get_permalink( $d->ID );
		}
		return $urls;
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
	 * Ajoute la page Boutique au menu primaire en Business si elle n'y
	 * est pas deja. Idempotent : ne re-ajoute pas un item qui existe.
	 */
	public function ensure_boutique_in_menu() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = get_page_by_path( 'boutique' );
		if ( ! $page ) {
			return;
		}
		$locations = (array) get_theme_mod( 'nav_menu_locations' );
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
		if ( ! $menu_id ) {
			return;
		}
		$items = wp_get_nav_menu_items( $menu_id );
		if ( $items ) {
			foreach ( $items as $item ) {
				if ( 'post_type' === $item->type && (int) $item->object_id === (int) $page->ID ) {
					return; // deja present
				}
			}
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => __( 'Boutique', 'ag-business-avocat' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
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

	/**
	 * Cree les 12 domaines de droit FR par defaut (CPT ag_domaine) si ils
	 * n'existent pas. Skip ceux deja crees par l'utilisateur (par slug).
	 */
	public function ensure_default_domaines() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! post_type_exists( 'ag_domaine' ) ) {
			return;
		}
		foreach ( $this->get_default_domaines_data() as $slug => $data ) {
			$existing = get_page_by_path( $slug, OBJECT, 'ag_domaine' );
			if ( $existing ) {
				continue;
			}
			$id = wp_insert_post( array(
				'post_type'    => 'ag_domaine',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_content' => $data['content'],
				'post_excerpt' => $data['excerpt'],
			) );
			if ( ! is_wp_error( $id ) && $id ) {
				update_post_meta( $id, '_ag_domaine_icon', $data['icon'] );
				update_post_meta( $id, '_ag_domaine_examples', $data['examples'] );
			}
		}
	}

	/**
	 * Ajoute un sous-menu sous l'item "Domaines d'expertise" du menu
	 * primaire avec un item par domaine + un lien final
	 * "Tous les domaines →".
	 */
	public function ensure_domaines_submenu() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$expertise_page = get_page_by_path( 'expertise' );
		if ( ! $expertise_page ) {
			return;
		}
		$locations = (array) get_theme_mod( 'nav_menu_locations' );
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
		if ( ! $menu_id ) {
			return;
		}
		$items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $items ) ) {
			return;
		}

		// Trouve l'item parent (le lien vers la page Expertise)
		$parent_item = null;
		foreach ( $items as $item ) {
			if ( 'post_type' === $item->type && 'page' === $item->object && (int) $item->object_id === (int) $expertise_page->ID ) {
				$parent_item = $item;
				break;
			}
		}
		if ( ! $parent_item ) {
			return;
		}
		$parent_id = (int) $parent_item->ID;

		// Liste les enfants existants pour idempotence
		$existing_child_object_ids = array();
		$has_tous                  = false;
		foreach ( $items as $item ) {
			if ( (int) $item->menu_item_parent !== $parent_id ) {
				continue;
			}
			if ( 'post_type' === $item->type && 'ag_domaine' === $item->object ) {
				$existing_child_object_ids[] = (int) $item->object_id;
			}
			if ( 'custom' === $item->type && false !== strpos( $item->title, 'Tous les domaines' ) ) {
				$has_tous = true;
			}
		}

		// Ajoute un item enfant par domaine CPT publie
		$domaines = function_exists( 'ag_starter_avocat_get_domaines' ) ? ag_starter_avocat_get_domaines( 20 ) : array();
		$position = 1;
		foreach ( $domaines as $d ) {
			if ( in_array( (int) $d->ID, $existing_child_object_ids, true ) ) {
				$position++;
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => get_the_title( $d ),
				'menu-item-object'    => 'ag_domaine',
				'menu-item-object-id' => (int) $d->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
				'menu-item-position'  => $position++,
			) );
		}

		// Ajoute le lien final "Tous les domaines →" en bas du sous-menu
		if ( ! $has_tous ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => __( 'Tous les domaines', 'ag-business-avocat' ),
				'menu-item-url'       => get_permalink( $expertise_page ),
				'menu-item-type'      => 'custom',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
				'menu-item-position'  => 999,
				'menu-item-classes'   => 'ag-business-submenu-tous',
			) );
		}
	}

	/**
	 * Donnees par defaut pour les 12 domaines de droit FR les plus
	 * courants. Chaque entree contient titre, icone, excerpt, contenu,
	 * et 3 exemples de cas.
	 */
	private function get_default_domaines_data() {
		return array(
			'droit-des-affaires' => array(
				'title'    => 'Droit des affaires',
				'icon'     => 'briefcase',
				'excerpt'  => "Conseil et representation des entreprises : creation, contrats commerciaux, contentieux, restructuration.",
				'content'  => "<p>Le droit des affaires regroupe l'ensemble des regles juridiques applicables a la vie des entreprises. Notre cabinet accompagne PME, ETI et grands groupes a chaque etape de leur developpement.</p>",
				'examples' => "Creation et statuts de societes\nNegociation de baux commerciaux\nLitiges entre associes",
			),
			'droit-du-travail' => array(
				'title'    => 'Droit du travail',
				'icon'     => 'shield',
				'excerpt'  => "Defense des salaries et des employeurs : licenciements, contrats, harcelement, ruptures conventionnelles.",
				'content'  => "<p>Notre cabinet conseille et represente salaries et employeurs dans toutes les problematiques liees a la relation de travail.</p>",
				'examples' => "Contestation de licenciement\nRupture conventionnelle\nHarcelement moral / sexuel",
			),
			'droit-de-la-famille' => array(
				'title'    => 'Droit de la famille',
				'icon'     => 'family',
				'excerpt'  => "Divorce, garde d'enfants, succession, adoption, regimes matrimoniaux. Discretion et empathie garanties.",
				'content'  => "<p>Le droit de la famille touche aux moments les plus intimes de la vie. Notre approche : ecoute, transparence, accompagnement personnalise.</p>",
				'examples' => "Divorce par consentement mutuel\nDivorce contentieux\nGarde d'enfants et droit de visite",
			),
			'droit-immobilier' => array(
				'title'    => 'Droit immobilier',
				'icon'     => 'house',
				'excerpt'  => "Acquisition, vente, copropriete, baux, troubles de voisinage, constructions et permis de construire.",
				'content'  => "<p>Conseil et contentieux pour proprietaires, locataires, syndics et professionnels de l'immobilier.</p>",
				'examples' => "Vices caches a l'achat\nLitige de copropriete\nExpulsion de locataire",
			),
			'droit-penal' => array(
				'title'    => 'Droit penal',
				'icon'     => 'gavel',
				'excerpt'  => "Defense penale tous degres de juridiction : garde a vue, comparution immediate, instruction, assises.",
				'content'  => "<p>Defense des justiciables a tous les stades de la procedure penale, en France comme a l'etranger.</p>",
				'examples' => "Garde a vue 24/24\nDefense d'assises\nViolences conjugales",
			),
			'droit-fiscal' => array(
				'title'    => 'Droit fiscal',
				'icon'     => 'document',
				'excerpt'  => "Optimisation, controle fiscal, contentieux, ISF, transmission de patrimoine, fiscalite internationale.",
				'content'  => "<p>Strategie fiscale pour particuliers et entreprises, defense en cas de redressement.</p>",
				'examples' => "Controle fiscal personnel\nContentieux URSSAF\nMontage de holding patrimoniale",
			),
			'droit-international' => array(
				'title'    => 'Droit international',
				'icon'     => 'scales',
				'excerpt'  => "Contrats internationaux, arbitrage, expatriation, fusions transfrontalieres, droit europeen.",
				'content'  => "<p>Accompagnement des entreprises et particuliers dans leurs operations transfrontalieres.</p>",
				'examples' => "Contrat de distribution international\nArbitrage CCI\nDetachement et expatriation",
			),
			'droit-de-la-securite-sociale' => array(
				'title'    => 'Droit de la securite sociale',
				'icon'     => 'heart',
				'excerpt'  => "Accident du travail, maladie professionnelle, invalidite, pensions, contentieux URSSAF.",
				'content'  => "<p>Defense des assures sociaux face aux organismes : CPAM, URSSAF, MSA, RSI.</p>",
				'examples' => "Reconnaissance d'accident du travail\nFaute inexcusable de l'employeur\nLitige sur taux d'invalidite",
			),
			'droit-des-successions' => array(
				'title'    => 'Droit des successions',
				'icon'     => 'document',
				'excerpt'  => "Succession, donation, testament, liquidation, partage, fiscalite successorale, indivision.",
				'content'  => "<p>Anticiper la transmission du patrimoine ou regler une succession contestee dans le respect des heritiers.</p>",
				'examples' => "Liquidation de succession\nContestation de testament\nDonation-partage",
			),
			'droit-du-numerique' => array(
				'title'    => 'Droit du numerique',
				'icon'     => 'lock',
				'excerpt'  => "RGPD, propriete intellectuelle, e-commerce, cybersecurite, contrats SaaS, donnees personnelles.",
				'content'  => "<p>Conformite et contentieux pour les acteurs de l'economie digitale.</p>",
				'examples' => "Mise en conformite RGPD\nContrat SaaS B2B\nViolation de donnees",
			),
			'droit-bancaire' => array(
				'title'    => 'Droit bancaire',
				'icon'     => 'bank',
				'excerpt'  => "Credit immobilier, taux d'usure, cautionnement, contentieux bancaire, surendettement, fraudes.",
				'content'  => "<p>Defense des particuliers et professionnels face aux etablissements bancaires.</p>",
				'examples' => "Pret immobilier sureleve\nFraude bancaire en ligne\nMise en jeu de caution",
			),
			'droit-de-la-consommation' => array(
				'title'    => 'Droit de la consommation',
				'icon'     => 'shield',
				'excerpt'  => "Garanties, vices caches, demarchage, credit conso, clauses abusives, action de groupe.",
				'content'  => "<p>Defense des droits des consommateurs face aux professionnels.</p>",
				'examples' => "Vice cache automobile\nDemarchage abusif\nClause abusive bancaire",
			),
		);
	}
}
