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
		add_action( 'admin_init', array( $this, 'ensure_legal_pages' ) );
		add_action( 'admin_init', array( $this, 'ensure_boutique_offers' ) );
		add_action( 'admin_notices', array( $this, 'woocommerce_admin_notice' ) );
		add_filter( 'wp_nav_menu_args', array( $this, 'allow_submenu_depth' ) );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 30 );
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
				'honorairesUrl'   => function_exists( 'ag_page_url' ) ? ag_page_url( 'honoraires' ) : home_url( '/honoraires/' ),
				'boutiqueUrl'     => function_exists( 'ag_page_url' ) ? ag_page_url( 'boutique' ) : home_url( '/boutique/' ),
				'domaineUrls'     => $this->get_domaine_urls(),
				'boutiqueSymbol'  => (string) get_theme_mod( 'ag_business_boutique_symbol', 'stars' ),
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
			// Belt-and-suspenders : si .ag-domaine-card__bg n'est pas rendu
			// (cas Free fallback ou structure modifiee), on pose l'image
			// directement sur la card parente.
			$css .= sprintf(
				"body.ag-business-active .ag-domaines__grid > .ag-domaine-card:nth-child(%d){background-image:url('%s')!important;background-size:cover!important;background-position:center!important;}\n",
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

	/**
	 * Cree les pages legales standards d'un site (mentions legales,
	 * RGPD, cookies, CGV, retour). Skip ce qui existe deja par slug.
	 * Le contenu est un template avec des [BRACKETS] pour le cabinet.
	 */
	public function ensure_legal_pages() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		foreach ( $this->get_legal_pages_data() as $slug => $data ) {
			if ( get_page_by_path( $slug ) ) {
				continue;
			}
			wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_content' => $data['content'],
			) );
		}
	}

	/**
	 * Customizer panel "AG Business — Options" : motif Boutique +
	 * autres futurs reglages Business.
	 */
	public function register_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'ag_business_panel', array(
			'title'    => __( 'AG Business — Options', 'ag-business-avocat' ),
			'priority' => 220,
		) );

		// Section Boutique
		$wp_customize->add_section( 'ag_business_boutique', array(
			'title' => __( 'Boutique — motif anime', 'ag-business-avocat' ),
			'panel' => 'ag_business_panel',
		) );
		$wp_customize->add_setting( 'ag_business_boutique_symbol', array(
			'default'           => 'stars',
			'sanitize_callback' => array( $this, 'sanitize_boutique_symbol' ),
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_business_boutique_symbol', array(
			'label'       => __( 'Motif anime de la section Boutique', 'ag-business-avocat' ),
			'description' => __( 'Choisir le symbole qui apparait en warp-speed dans la section Boutique de la home, ou desactiver l\'effet.', 'ag-business-avocat' ),
			'section'     => 'ag_business_boutique',
			'type'        => 'select',
			'choices'     => array(
				'stars'  => __( 'Etoiles (par defaut)', 'ag-business-avocat' ),
				'scales' => __( 'Balance de la justice', 'ag-business-avocat' ),
				'gavel'  => __( 'Marteau de juge', 'ag-business-avocat' ),
				'pillar' => __( 'Colonne classique', 'ag-business-avocat' ),
				'none'   => __( 'Desactiver', 'ag-business-avocat' ),
			),
		) );
	}

	public function sanitize_boutique_symbol( $value ) {
		$valid = array( 'stars', 'scales', 'gavel', 'pillar', 'none' );
		return in_array( $value, $valid, true ) ? $value : 'stars';
	}

	/**
	 * Templates legaux pour un site d'avocat. Marqueurs [crochets] que
	 * le cabinet doit completer apres creation.
	 */
	private function get_legal_pages_data() {
		return array(
			'mentions-legales' => array(
				'title'   => 'Mentions légales',
				'content' => $this->legal_template_mentions(),
			),
			'politique-confidentialite' => array(
				'title'   => 'Politique de confidentialité (RGPD)',
				'content' => $this->legal_template_rgpd(),
			),
			'politique-cookies' => array(
				'title'   => 'Politique de cookies',
				'content' => $this->legal_template_cookies(),
			),
			'cgv' => array(
				'title'   => 'Conditions générales de vente',
				'content' => $this->legal_template_cgv(),
			),
			'politique-retour' => array(
				'title'   => 'Politique de retour',
				'content' => $this->legal_template_retour(),
			),
		);
	}

	private function legal_template_mentions() {
		return '<p><em>Page generee automatiquement par AG Business. Remplacez les [crochets] par vos coordonnees.</em></p>
<h2>Editeur du site</h2>
<p><strong>[Nom du cabinet]</strong><br>
[Adresse complete]<br>
SIRET : [numero SIRET]<br>
Numero TVA intracommunautaire : [FR XX XXX XXX XXX]<br>
Email : [email@cabinet.fr]<br>
Telephone : [01 23 45 67 89]<br>
Inscription au Barreau de [Ville] sous le numero [numero]</p>
<h2>Directeur de la publication</h2>
<p>[Nom et prenom du Maitre], avocat au Barreau de [Ville].</p>
<h2>Hebergement</h2>
<p>[Nom de l\'hebergeur]<br>
[Adresse]<br>
Telephone : [telephone]</p>
<h2>Propriete intellectuelle</h2>
<p>L\'ensemble du contenu (textes, images, logos, structure) du present site est la propriete exclusive de [Nom du cabinet]. Toute reproduction, meme partielle, sans accord ecrit prealable est interdite.</p>
<h2>Credits</h2>
<p>Theme : AG Starter Avocat — <a href="https://alliancegroupe-inc.com" target="_blank" rel="noopener">alliancegroupe-inc.com</a></p>';
	}

	private function legal_template_rgpd() {
		return '<p><em>Page generee automatiquement par AG Business. Adaptez aux specificites de votre cabinet.</em></p>
<p>La presente politique decrit comment [Nom du cabinet] collecte, utilise et protege vos donnees personnelles, conformement au Reglement General sur la Protection des Donnees (RGPD) et a la loi Informatique et Libertes.</p>
<h2>1. Responsable du traitement</h2>
<p><strong>[Nom du cabinet]</strong>, [adresse], represente par Maitre [Nom], avocat au Barreau de [Ville]. Contact : [email].</p>
<h2>2. Donnees collectees</h2>
<ul>
<li>Identite : nom, prenom, civilite, date de naissance</li>
<li>Coordonnees : email, telephone, adresse postale</li>
<li>Donnees professionnelles : profession, employeur, situation</li>
<li>Donnees liees au dossier : faits, pieces communiquees, correspondances</li>
<li>Donnees techniques : adresse IP, logs de connexion, cookies (voir politique cookies)</li>
</ul>
<h2>3. Finalites du traitement</h2>
<ul>
<li>Gestion des dossiers juridiques et representation</li>
<li>Communication avec les clients</li>
<li>Facturation et comptabilite</li>
<li>Respect des obligations professionnelles et reglementaires</li>
<li>Statistiques anonymisees du site (audience)</li>
</ul>
<h2>4. Base legale</h2>
<p>Les donnees sont traitees sur la base de l\'execution d\'un contrat (mandat de l\'avocat), du consentement (formulaire de contact) ou d\'une obligation legale (conservation des dossiers).</p>
<h2>5. Duree de conservation</h2>
<p>Les dossiers sont conserves <strong>5 ans apres la fin de la mission</strong>, conformement aux obligations de l\'article 2.2 du Reglement Interieur National. Les pieces comptables sont conservees 10 ans.</p>
<h2>6. Destinataires des donnees</h2>
<p>Les donnees sont accessibles uniquement aux avocats et collaborateurs du cabinet, soumis au secret professionnel. Aucune donnee n\'est cedee a des tiers a des fins commerciales.</p>
<h2>7. Vos droits</h2>
<p>Vous disposez des droits suivants : acces, rectification, effacement, limitation du traitement, portabilite, opposition. Pour exercer ces droits, contactez-nous a [email]. Vous pouvez egalement saisir la CNIL (<a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>).</p>
<h2>8. Securite</h2>
<p>Le cabinet met en oeuvre des mesures techniques et organisationnelles appropriees pour proteger vos donnees contre la perte, l\'acces non autorise, la divulgation et la modification.</p>';
	}

	private function legal_template_cookies() {
		return '<p><em>Page generee automatiquement par AG Business. Adaptez selon les services tiers que vous utilisez.</em></p>
<h2>Qu\'est-ce qu\'un cookie ?</h2>
<p>Un cookie est un petit fichier texte depose sur votre terminal lors de la consultation d\'un site web. Il permet au site de reconnaitre votre navigateur et de memoriser certaines informations vous concernant.</p>
<h2>Cookies utilises sur ce site</h2>
<table>
<thead><tr><th>Cookie</th><th>Finalite</th><th>Duree</th></tr></thead>
<tbody>
<tr><td>wordpress_*</td><td>Authentification administrateur</td><td>Session</td></tr>
<tr><td>wp-settings-*</td><td>Preferences interface admin</td><td>1 an</td></tr>
<tr><td>ag_mode</td><td>Choix du mode jour / nuit</td><td>Persistant (localStorage)</td></tr>
</tbody>
</table>
<h2>Comment refuser ou supprimer les cookies ?</h2>
<p>Vous pouvez configurer votre navigateur pour refuser les cookies ou supprimer ceux deja deposes :</p>
<ul>
<li>Chrome : Reglages → Confidentialite et securite → Cookies</li>
<li>Firefox : Preferences → Vie privee et securite</li>
<li>Safari : Preferences → Confidentialite</li>
<li>Edge : Parametres → Cookies et autorisations</li>
</ul>
<p>Le refus de certains cookies peut alterer le fonctionnement du site.</p>';
	}

	private function legal_template_cgv() {
		return '<p><em>Page generee automatiquement par AG Business. A faire valider par le batonnier de votre Barreau.</em></p>
<h2>Article 1 — Objet</h2>
<p>Les presentes conditions generales regissent les ventes de produits et services proposes par <strong>[Nom du cabinet]</strong> via le site [URL] ou en cabinet.</p>
<h2>Article 2 — Prix</h2>
<p>Les prix sont indiques en euros TTC. La TVA applicable est de 20 % pour les prestations juridiques. Le cabinet se reserve le droit de modifier ses tarifs a tout moment, etant entendu que la prestation sera facturee sur la base du tarif en vigueur a la commande.</p>
<h2>Article 3 — Commande</h2>
<p>Toute commande implique l\'acceptation sans reserve des presentes CGV. La commande est consideree comme ferme et definitive apres reception du paiement.</p>
<h2>Article 4 — Paiement</h2>
<p>Les paiements s\'effectuent par carte bancaire, virement, ou cheque. Pour les prestations juridiques, une convention d\'honoraires ecrite est signee avant toute intervention.</p>
<h2>Article 5 — Livraison / Execution</h2>
<p>Les prestations dematerialisees (consultations en ligne, guides PDF) sont accessibles immediatement apres paiement. Les prestations en cabinet sont planifiees selon les disponibilites convenues.</p>
<h2>Article 6 — Retractation</h2>
<p>Conformement a l\'article L221-18 du Code de la consommation, le client dispose d\'un delai de <strong>14 jours</strong> pour exercer son droit de retractation, sauf pour les prestations entierement executees a sa demande explicite avant la fin de ce delai.</p>
<h2>Article 7 — Responsabilite</h2>
<p>La responsabilite du cabinet ne peut etre engagee qu\'en cas de faute prouvee. Elle est limitee au montant des honoraires effectivement percus.</p>
<h2>Article 8 — Litiges</h2>
<p>Tout litige releve de la competence des tribunaux du ressort de la Cour d\'appel de [Ville]. Le client peut egalement recourir a la mediation de la consommation.</p>';
	}

	private function legal_template_retour() {
		return '<p><em>Page generee automatiquement par AG Business. Concerne uniquement les ventes de produits/services dematerialises.</em></p>
<h2>Droit de retractation</h2>
<p>Conformement aux articles L221-18 et suivants du Code de la consommation, vous disposez de <strong>14 jours calendaires</strong> a compter de la conclusion du contrat pour vous retracter, sans avoir a justifier de motifs ni a payer de penalites.</p>
<h2>Comment exercer le droit de retractation ?</h2>
<p>Pour exercer votre droit, vous devez nous notifier votre decision avant l\'expiration du delai par :</p>
<ul>
<li>Email a : [email@cabinet.fr]</li>
<li>Courrier postal a : [Adresse]</li>
</ul>
<p>Indiquez vos nom, adresse, le numero de la commande et la date.</p>
<h2>Effets de la retractation</h2>
<p>Nous vous remboursons l\'integralite des sommes versees, dans un delai de <strong>14 jours</strong> a compter de la reception de votre demande, par le meme moyen de paiement utilise pour la commande.</p>
<h2>Exceptions</h2>
<p>Le droit de retractation ne peut etre exerce pour :</p>
<ul>
<li>Les prestations entierement executees avant la fin du delai de 14 jours, avec votre accord prealable et explicite</li>
<li>Les contenus numeriques telecharges (guides PDF) une fois l\'acces fourni, avec votre accord</li>
<li>Les services confidentiels de defense penale en cas d\'urgence</li>
</ul>
<h2>Contestation</h2>
<p>En cas de litige, contactez-nous d\'abord pour tenter une resolution amiable. A defaut, vous pouvez saisir le mediateur de la consommation ou le Conseil de l\'Ordre du Barreau de [Ville].</p>';
	}

	/**
	 * Cree les 3 offres affichees dans la section Boutique de la home,
	 * pour qu'elles existent en tant que produits WooCommerce (si actif)
	 * ou pages classiques (fallback). Met a jour les theme_mod URLs
	 * pour que les cards de la home pointent vers les bonnes pages.
	 *
	 * Idempotent : skip si l'offre existe deja (par slug).
	 */
	public function ensure_boutique_offers() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$offers = $this->get_default_offers_data();

		if ( class_exists( 'WC_Product_Simple' ) ) {
			// WooCommerce actif : creer un WC_Product_Simple
			foreach ( $offers as $slug => $data ) {
				if ( get_page_by_path( $slug, OBJECT, 'product' ) ) {
					continue;
				}
				$product = new WC_Product_Simple();
				$product->set_name( $data['title'] );
				$product->set_slug( $slug );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
				$product->set_regular_price( (string) $data['price_value'] );
				$product->set_short_description( $data['desc'] );
				$product->set_description( $data['long_desc'] );
				$product->set_virtual( true ); // services dematerialises
				$product->save();
			}
			return;
		}

		// WooCommerce absent : pages classiques + theme_mod URLs
		$shop_keys = array( 'ag_business_shop_1_url', 'ag_business_shop_2_url', 'ag_business_shop_3_url' );
		$i         = 0;
		foreach ( $offers as $slug => $data ) {
			$key = isset( $shop_keys[ $i ] ) ? $shop_keys[ $i ] : null;
			$i++;
			if ( ! $key ) {
				break;
			}
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$page_id = wp_insert_post( array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => '<p class="ag-offer-price"><strong>' . esc_html( $data['price_value'] ) . ' €</strong></p>' . $data['long_desc'],
				) );
				if ( is_wp_error( $page_id ) ) {
					continue;
				}
				$page = get_post( $page_id );
			}
			$url = get_permalink( $page );
			$current = (string) get_theme_mod( $key, '' );
			if ( '' === $current || '#' === $current ) {
				set_theme_mod( $key, $url );
			}
		}
	}

	/**
	 * Donnees des 3 offres par defaut affichees dans la section Boutique
	 * de la home (alignees avec les defaults de pro-features.php).
	 */
	private function get_default_offers_data() {
		return array(
			'pack-3-consultations-telephoniques' => array(
				'title'       => '3 consultations téléphoniques',
				'desc'        => 'Pack de 3 consultations de 30 min, valables 6 mois. Conseil juridique sur tout domaine.',
				'price_value' => 450,
				'long_desc'   => '<h2>Contenu du pack</h2>
<ul>
<li>3 consultations téléphoniques de 30 minutes chacune</li>
<li>Validité : 6 mois à compter de la commande</li>
<li>Tous domaines de droit couverts</li>
<li>Disponibles du lundi au vendredi 9h-18h</li>
<li>Compte-rendu écrit après chaque consultation (sur demande)</li>
</ul>
<h2>Pour qui ?</h2>
<p>Particuliers et indépendants ayant besoin d\'un avis juridique rapide, sans déplacement au cabinet.</p>
<h2>Comment ça marche</h2>
<ol>
<li>Vous commandez le pack en ligne</li>
<li>Vous recevez un email de confirmation avec le numéro à appeler</li>
<li>Vous prenez rendez-vous au moment qui vous convient</li>
<li>Le Maître vous rappelle à l\'heure convenue</li>
</ol>',
			),
			'guide-juridique-pdf' => array(
				'title'       => 'Guide juridique PDF',
				'desc'        => 'Manuel pratique 80 pages : vos droits face au licenciement, à la séparation, aux litiges courants.',
				'price_value' => 29,
				'long_desc'   => '<h2>Sommaire du guide</h2>
<ul>
<li>Droit du travail : licenciement, rupture conventionnelle, harcèlement</li>
<li>Droit de la famille : divorce, garde d\'enfants, pension</li>
<li>Droit immobilier : bail, vices cachés, copropriété</li>
<li>Droit de la consommation : démarchage, garanties, litiges</li>
<li>Modèles de courriers types prêts à utiliser</li>
</ul>
<h2>Format</h2>
<p>Document PDF de 80 pages, téléchargeable immédiatement après commande. Imprimable. Mises à jour gratuites pendant 12 mois.</p>',
			),
			'audit-contractuel' => array(
				'title'       => 'Audit contractuel',
				'desc'        => 'Analyse complète d\'un contrat (CDI, bail, partenariat) avec rapport écrit et recommandations.',
				'price_value' => 290,
				'long_desc'   => '<h2>Ce que vous obtenez</h2>
<ul>
<li>Lecture intégrale du contrat (jusqu\'à 30 pages)</li>
<li>Rapport écrit (8-12 pages) avec analyse clause par clause</li>
<li>Identification des points à risque et clauses abusives</li>
<li>Recommandations de modifications</li>
<li>Suggestions de négociation</li>
<li>Délai : 5 jours ouvrés</li>
</ul>
<h2>Types de contrats</h2>
<p>CDI / CDD, contrats de bail (commercial, habitation), contrats commerciaux et de partenariat, contrats de prestations de services, conditions générales, etc.</p>
<h2>Comment commander</h2>
<ol>
<li>Vous commandez l\'audit en ligne</li>
<li>Vous recevez un email avec un lien sécurisé pour téléverser le contrat</li>
<li>Le Maître vous envoie le rapport sous 5 jours ouvrés</li>
<li>Une visioconférence de 30 min est offerte pour clarifier les points</li>
</ol>',
			),
		);
	}
}
