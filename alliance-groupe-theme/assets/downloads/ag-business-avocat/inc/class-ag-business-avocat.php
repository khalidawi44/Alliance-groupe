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
		add_action( 'admin_init', array( $this, 'ensure_account_pages' ) );
		add_shortcode( 'ag_business_account', array( $this, 'render_account_shortcode' ) );
		add_shortcode( 'ag_business_boutique_grid', array( $this, 'render_boutique_grid_shortcode' ) );
		add_action( 'admin_notices', array( $this, 'woocommerce_admin_notice' ) );
		add_action( 'admin_notices', array( $this, 'stripe_admin_notice' ) );
		// Note : pas de filter the_content pour la team Cabinet — le
		// template page-cabinet.php du theme n'appelle pas the_content.
		// L'injection se fait cote JS via cabinetTeamHtml localise.
		add_filter( 'wp_nav_menu_args', array( $this, 'allow_submenu_depth' ) );
		add_filter( 'wp_nav_menu_items', array( $this, 'inject_account_menu_item' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'append_domaine_extras' ), 25 );
		add_action( 'ag_after_domaines', array( $this, 'render_tous_domaines_btn' ), 5 );
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
	 * Injecte un item de menu 'Connexion' (non-connecte) ou 'Mon compte'
	 * (connecte) avec un sous-menu mega-menu. Hooke wp_nav_menu_items
	 * (string-based) pour un append simple en fin de menu primaire.
	 */
	/**
	 * Bouton 'Tous nos domaines' apres la grille Domaines sur la home.
	 * Hooke sur ag_after_domaines @ 5 (avant render_parallax_quote_1
	 * qui est a priorite 10).
	 */
	public function render_tous_domaines_btn() {
		if ( ! $this->is_active() ) {
			return;
		}
		$url = function_exists( 'ag_page_url' ) ? ag_page_url( 'expertise' ) : home_url( '/expertise/' );
		?>
		<div class="ag-business-tous-domaines-cta" style="text-align:center;padding:24px 24px 48px;">
			<a href="<?php echo esc_url( $url ); ?>" class="ag-btn ag-business-tous-domaines-cta__btn"><?php esc_html_e( 'Tous nos domaines →', 'ag-business-avocat' ); ?></a>
		</div>
		<?php
	}

	public function inject_account_menu_item( $items, $args ) {
		if ( ! $this->is_active() ) {
			return $items;
		}
		if ( ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $items;
		}
		if ( is_user_logged_in() ) {
			return $items . $this->build_account_menu_logged_in();
		}
		return $items . $this->build_account_menu_logged_out();
	}

	private function build_account_menu_logged_out() {
		$login_url    = home_url( '/connexion/' );
		$register_url = home_url( '/inscription/' );
		ob_start();
		?>
		<li class="menu-item menu-item-has-children ag-business-account-item">
			<a href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Connexion', 'ag-business-avocat' ); ?></a>
			<ul class="sub-menu ag-business-account-submenu ag-business-account-submenu--logged-out">
				<li class="menu-item ag-business-account-submenu__col"><a href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Se connecter', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item ag-business-account-submenu__col"><a href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Créer un compte', 'ag-business-avocat' ); ?></a></li>
			</ul>
		</li>
		<?php
		return ob_get_clean();
	}

	private function build_account_menu_logged_in() {
		$wc_active   = class_exists( 'WooCommerce' );
		$account     = home_url( '/mon-compte/' );
		$logout      = wp_logout_url( home_url() );
		$user        = wp_get_current_user();
		$first_name  = $user->first_name ? $user->first_name : $user->display_name;

		// URLs WC endpoints (fonctionnent meme sans WC, vont juste sur /mon-compte/)
		$orders     = $wc_active ? wc_get_account_endpoint_url( 'orders' )    : $account;
		$downloads  = $wc_active ? wc_get_account_endpoint_url( 'downloads' ) : $account;
		$addresses  = $wc_active ? wc_get_account_endpoint_url( 'edit-address' ) : $account;
		$edit       = $wc_active ? wc_get_account_endpoint_url( 'edit-account' ) : admin_url( 'profile.php' );

		ob_start();
		?>
		<li class="menu-item menu-item-has-children ag-business-account-item ag-business-account-item--logged-in">
			<a href="<?php echo esc_url( $account ); ?>"><?php echo esc_html( __( 'Mon compte', 'ag-business-avocat' ) ); ?></a>
			<ul class="sub-menu ag-business-account-submenu ag-business-account-submenu--logged-in">
				<li class="menu-item ag-business-account-submenu__greeting">
					<span><?php
						/* translators: %s : prénom de l'utilisateur */
						printf( esc_html__( 'Bonjour %s', 'ag-business-avocat' ), esc_html( $first_name ) );
					?></span>
				</li>
				<li class="menu-item"><a href="<?php echo esc_url( $account ); ?>">📋 <?php esc_html_e( 'Tableau de bord', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item"><a href="<?php echo esc_url( $orders ); ?>">📦 <?php esc_html_e( 'Mes commandes', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item"><a href="<?php echo esc_url( $downloads ); ?>">⬇️ <?php esc_html_e( 'Mes téléchargements', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item"><a href="<?php echo esc_url( $account ); ?>#mes-rendez-vous">📅 <?php esc_html_e( 'Mes rendez-vous', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item"><a href="<?php echo esc_url( $account ); ?>#mes-dossiers">🗂️ <?php esc_html_e( 'Mes dossiers', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item"><a href="<?php echo esc_url( $addresses ); ?>">📍 <?php esc_html_e( 'Mes adresses', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item"><a href="<?php echo esc_url( $edit ); ?>">⚙️ <?php esc_html_e( 'Profil', 'ag-business-avocat' ); ?></a></li>
				<li class="menu-item ag-business-submenu-tous"><a href="<?php echo esc_url( $logout ); ?>">🚪 <?php esc_html_e( 'Se déconnecter', 'ag-business-avocat' ); ?></a></li>
			</ul>
		</li>
		<?php
		return ob_get_clean();
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
		if ( is_front_page() ) {
			$classes[] = 'ag-business-home';
		}
		if ( get_theme_mod( 'ag_business_hide_home_boutique', false ) ) {
			$classes[] = 'ag-business-hide-home-boutique';
		}
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
				'stripeUrls'      => array(
					(string) get_theme_mod( 'ag_business_honoraires_first_stripe', '' ),
					(string) get_theme_mod( 'ag_business_honoraires_pack_stripe', '' ),
					(string) get_theme_mod( 'ag_business_honoraires_hour_stripe', '' ),
				),
				// HTML de l'equipe injecte uniquement sur la page Cabinet
				// (le template Free n'appelle pas the_content donc on
				// passe par JS). Ordre cabinet : fondateur puis associes
				// puis collaborateurs, avec citations entre.
				'cabinetFounderHtml'       => is_page( 'cabinet' ) ? $this->render_team_group_html( 'founder', __( 'Le fondateur', 'ag-business-avocat' ) ) : '',
				'cabinetAssociatesHtml'    => is_page( 'cabinet' ) ? $this->render_team_group_html( 'associates', __( 'Avocats associés', 'ag-business-avocat' ) ) : '',
				'cabinetCollaboratorsHtml' => is_page( 'cabinet' ) ? $this->render_team_group_html( 'collaborators', __( 'Collaborateurs', 'ag-business-avocat' ) ) : '',
				// Citations parallax injectees entre les sections des
				// pages internes (cabinet, honoraires, expertise).
				// JAMAIS apres le titre haut de page (.ag-page-hero).
				'pageCitations'            => $this->get_page_citations_data(),
				// URLs d'images pour les 3 offres Boutique sur la home —
				// JS les injecte dans les cards si pas deja presentes.
				'boutiqueOfferImages'      => $this->get_boutique_offer_images(),
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
	 * Sur la home, ordre demande par l'utilisateur :
	 *   .ag-maitre Free masque (CSS) -> "Le fondateur et les associes"
	 *   -> "Notre equipe" -> bouton Decouvrir.
	 *
	 * Le render_team_section de pro-features (priorite 10) est masque
	 * par CSS (body.ag-business-active .ag-section.ag-team{display:none})
	 * et remplace par notre render_home_team_business (priorite 7).
	 */
	public function reorder_sections() {
		if ( ! $this->is_active() ) {
			return;
		}
		// Section combinee fondateur + 2 associes (compacts a droite).
		add_action( 'ag_after_maitre', array( $this, 'render_founder_and_associates' ), 5 );
		// Notre equipe (collaborateurs + conseiller fiscal).
		add_action( 'ag_after_maitre', array( $this, 'render_home_team_business' ), 7 );
		// Bouton 'Decouvrir' a la fin (apres team_section Free @10 mais
		// celui-ci est masque en CSS).
		add_action( 'ag_after_maitre', array( $this, 'render_decouvrir_after_team' ), 99 );
	}

	private function render_decouvrir_btn_html( $aria_label = 'Decouvrir' ) {
		$url = function_exists( 'ag_page_url' ) ? ag_page_url( 'cabinet' ) : home_url( '/cabinet/' );
		return '<div class="ag-business-decouvrir-cta" style="text-align:center;padding:14px 24px 24px;">'
			. '<a href="' . esc_url( $url ) . '" class="ag-btn ag-business-decouvrir-cta__btn" aria-label="' . esc_attr( $aria_label ) . '">'
			. esc_html__( 'Découvrir →', 'ag-business-avocat' )
			. '</a></div>';
	}

	public function render_decouvrir_after_team() {
		if ( ! $this->is_active() ) {
			return;
		}
		echo $this->render_decouvrir_btn_html( 'Decouvrir le cabinet et l\'equipe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Section "Le fondateur et les associes" sur la home : fondateur big
	 * a gauche (2/3) + 2 associes compacts en colonne a droite (1/3).
	 * Remplace visuellement la section .ag-maitre du theme Free
	 * (masquee en CSS via body.ag-business-active).
	 */
	public function render_founder_and_associates() {
		if ( ! $this->is_active() ) {
			return;
		}
		$team    = $this->get_default_team_data();
		$founder = isset( $team['founder'][0] ) ? $team['founder'][0] : null;
		$assocs  = isset( $team['associates'] ) ? $team['associates'] : array();
		if ( ! $founder ) {
			return;
		}
		$cabinet_url = function_exists( 'ag_page_url' ) ? ag_page_url( 'cabinet' ) : home_url( '/cabinet/' );
		// Override Customizer pour la photo (utile car les photos
		// stock par defaut peuvent ne pas correspondre a l'image
		// recherchee — homme age, barbe blanche, tribunal francais).
		$photo_override = (string) get_theme_mod( 'ag_business_founder_photo', '' );
		$photo_url      = $photo_override ? $photo_override : $founder['photo'];
		?>
		<section class="ag-section ag-business-founder" id="ag-business-founder">
			<div class="ag-container ag-business-founder__container">
				<h2 class="ag-business-founder__title"><?php esc_html_e( 'Le fondateur et les associés', 'ag-business-avocat' ); ?></h2>
				<div class="ag-business-founder__grid">
					<article class="ag-business-founder-card">
						<div class="ag-business-founder-card__photo" style="background-image:url('<?php echo esc_url( $photo_url ); ?>');" aria-hidden="true">
							<?php // Signature posee en bas a droite de la photo, en cursive Allison.
							$sig_label = $this->get_founder_signature_text( $founder['name'] ); ?>
							<span class="ag-business-founder-card__signature" aria-hidden="true"><?php echo esc_html( $sig_label ); ?></span>
						</div>
						<div class="ag-business-founder-card__body">
							<p class="ag-business-founder-card__tag"><?php esc_html_e( 'Fondateur', 'ag-business-avocat' ); ?></p>
							<h3 class="ag-business-founder-card__name"><?php echo esc_html( $founder['name'] ); ?></h3>
							<p class="ag-business-founder-card__role"><?php echo esc_html( $founder['role'] ); ?></p>
							<ul class="ag-business-founder-card__domains">
								<?php foreach ( $founder['specialties'] as $s ) : ?>
									<li><?php echo esc_html( $s ); ?></li>
								<?php endforeach; ?>
							</ul>
							<a href="<?php echo esc_url( $cabinet_url ); ?>" class="ag-btn ag-business-founder-card__btn"><?php esc_html_e( 'Découvrir →', 'ag-business-avocat' ); ?></a>
						</div>
					</article>

					<aside class="ag-business-founder__associates">
						<p class="ag-business-founder__associates-tag"><?php esc_html_e( 'Avocats associés', 'ag-business-avocat' ); ?></p>
						<?php foreach ( $assocs as $a ) : ?>
							<article class="ag-business-associate-mini">
								<div class="ag-business-associate-mini__photo" style="background-image:url('<?php echo esc_url( $a['photo'] ); ?>');" aria-hidden="true"></div>
								<div class="ag-business-associate-mini__body">
									<h4 class="ag-business-associate-mini__name"><?php echo esc_html( $a['name'] ); ?></h4>
									<p class="ag-business-associate-mini__domain"><?php echo esc_html( $a['specialties'][0] ); ?></p>
									<a href="<?php echo esc_url( $cabinet_url ); ?>" class="ag-business-associate-mini__link"><?php esc_html_e( 'Découvrir →', 'ag-business-avocat' ); ?></a>
								</div>
							</article>
						<?php endforeach; ?>
					</aside>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Texte de signature pour la card fondateur. Utilise le 1er prenom +
	 * patronyme du nom complet (ex: "Maitre Henri DELACROIX" → "H. Delacroix").
	 * Format compact pour la cursive — peut etre override via Customizer.
	 */
	private function get_founder_signature_text( $name ) {
		$override = (string) get_theme_mod( 'ag_business_founder_signature', '' );
		if ( $override ) {
			return $override;
		}
		// Compact: enleve le titre "Maitre", garde initiale + patronyme.
		$clean = preg_replace( '/^(Maître|Maitre|Me|Mr|M\.)\s+/iu', '', $name );
		$parts = preg_split( '/\s+/', trim( $clean ) );
		if ( count( $parts ) >= 2 ) {
			$first   = mb_substr( $parts[0], 0, 1 );
			$last    = end( $parts );
			$last_fmt = mb_strtoupper( mb_substr( $last, 0, 1 ) ) . mb_strtolower( mb_substr( $last, 1 ) );
			return $first . '. ' . $last_fmt;
		}
		return $clean;
	}

	/**
	 * Section "Notre equipe" sur la home (collaborateurs + conseiller
	 * fiscal). Version compacte : juste photo, nom, role, domaines, lien
	 * "Decouvrir" vers la page cabinet. Pas de cursus/formation/langues.
	 */
	public function render_home_team_business() {
		if ( ! $this->is_active() ) {
			return;
		}
		$team    = $this->get_default_team_data();
		$members = isset( $team['collaborators'] ) ? $team['collaborators'] : array();
		if ( ! $members ) {
			return;
		}
		$cabinet_url = function_exists( 'ag_page_url' ) ? ag_page_url( 'cabinet' ) : home_url( '/cabinet/' );
		?>
		<section class="ag-business-team-full ag-business-team-full--collaborators">
			<h2 class="ag-business-team-full__group-title"><?php esc_html_e( 'Notre équipe', 'ag-business-avocat' ); ?></h2>
			<div class="ag-business-team-full__grid ag-business-team-full__grid--collaborators ag-business-team-compact-grid">
				<?php foreach ( $members as $m ) : ?>
					<article class="ag-business-team-compact">
						<div class="ag-business-team-compact__photo" style="background-image:url('<?php echo esc_url( $m['photo'] ); ?>');" aria-hidden="true"></div>
						<div class="ag-business-team-compact__body">
							<h3 class="ag-business-team-compact__name"><?php echo esc_html( $m['name'] ); ?></h3>
							<p class="ag-business-team-compact__role"><?php echo esc_html( $m['role'] ); ?></p>
							<ul class="ag-business-team-compact__domains">
								<?php foreach ( $m['specialties'] as $s ) : ?>
									<li><?php echo esc_html( $s ); ?></li>
								<?php endforeach; ?>
							</ul>
							<a href="<?php echo esc_url( $cabinet_url ); ?>" class="ag-business-team-compact__link"><?php esc_html_e( 'Découvrir →', 'ag-business-avocat' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
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

		$desired_content = class_exists( 'WooCommerce' )
			? '[ag_business_boutique_grid]'
			: '<!-- WooCommerce non installe — la boutique apparaitra ici une fois le plugin active. -->';

		// Si une page existe deja (boutique/shop/magasin), on met a jour
		// son contenu si c'etait notre placeholder ou l'ancien shortcode.
		$existing = get_page_by_path( 'boutique' );
		if ( ! $existing ) {
			// Cherche shop/magasin si existes (eg WC l'a cree)
			foreach ( array( 'shop', 'magasin' ) as $alt ) {
				$found = get_posts( array(
					'name'           => $alt,
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => 1,
				) );
				if ( ! empty( $found ) ) {
					$existing = $found[0];
					break;
				}
			}
		}

		if ( $existing ) {
			// Update si contenu = ancien default ou ancien shortcode WC
			$current  = trim( (string) $existing->post_content );
			$old_defs = array(
				'[products limit="12" columns="3"]',
				'[products]',
				'<!-- WooCommerce non installe — la boutique apparaitra ici une fois le plugin active. -->',
				'',
			);
			if ( in_array( $current, $old_defs, true ) && $current !== trim( $desired_content ) ) {
				wp_update_post( array(
					'ID'           => $existing->ID,
					'post_content' => $desired_content,
				) );
			}
			return;
		}

		// Pas de page existante, on cree
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Boutique', 'ag-business-avocat' ),
			'post_name'    => 'boutique',
			'post_content' => $desired_content,
		) );
	}

	/**
	 * Shortcode [ag_business_boutique_grid] : rend les produits WC
	 * avec le meme style .ag-boutique-card que la section Boutique
	 * de la home. Pas de WC -> message d'attente.
	 */
	public function render_boutique_grid_shortcode() {
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_products' ) ) {
			return '<p style="text-align:center;padding:40px;">' . esc_html__( 'WooCommerce n\'est pas activé. La boutique sera disponible dès l\'activation du plugin.', 'ag-business-avocat' ) . '</p>';
		}

		// Parametres GET pour les filtres : recherche, categorie, prix
		// min/max, tri.
		$q       = isset( $_GET['ag_q'] ) ? sanitize_text_field( wp_unslash( $_GET['ag_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cat     = isset( $_GET['ag_cat'] ) ? sanitize_key( wp_unslash( $_GET['ag_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$min     = isset( $_GET['ag_min'] ) ? floatval( wp_unslash( $_GET['ag_min'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$max     = isset( $_GET['ag_max'] ) ? floatval( wp_unslash( $_GET['ag_max'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sort    = isset( $_GET['ag_sort'] ) ? sanitize_key( wp_unslash( $_GET['ag_sort'] ) ) : 'date_desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$args = array(
			'limit'  => 24,
			'status' => 'publish',
		);
		switch ( $sort ) {
			case 'price_asc':
				$args['orderby'] = 'price'; $args['order'] = 'ASC'; break;
			case 'price_desc':
				$args['orderby'] = 'price'; $args['order'] = 'DESC'; break;
			case 'name_asc':
				$args['orderby'] = 'title'; $args['order'] = 'ASC'; break;
			case 'popularity':
				$args['orderby']  = 'meta_value_num'; $args['meta_key'] = 'total_sales'; $args['order'] = 'DESC'; break;
			case 'date_desc':
			default:
				$args['orderby'] = 'date'; $args['order'] = 'DESC';
		}
		if ( $cat ) {
			$args['category'] = array( $cat );
		}

		$products = wc_get_products( $args );

		// Filtre prix : applique apres car wc_get_products price filter
		// fonctionne en meta_query sur _price (pas toujours fiable selon
		// le type de produit), donc filtrage in-memory plus robuste.
		if ( $min > 0 || $max > 0 ) {
			$products = array_filter( $products, function ( $p ) use ( $min, $max ) {
				if ( ! is_object( $p ) ) return false;
				$price = floatval( $p->get_price() );
				if ( $min > 0 && $price < $min ) return false;
				if ( $max > 0 && $price > $max ) return false;
				return true;
			} );
		}

		// Recherche : si query ?ag_q=, filtrer par titre/description.
		if ( '' !== $q ) {
			$needle   = mb_strtolower( $q );
			$products = array_filter( $products, function ( $p ) use ( $needle ) {
				if ( ! is_object( $p ) ) return false;
				$hay = mb_strtolower( $p->get_name() . ' ' . $p->get_short_description() . ' ' . $p->get_description() );
				return false !== mb_strpos( $hay, $needle );
			} );
		}

		// Categories pour le filtre dropdown
		$categories = function_exists( 'get_terms' ) ? get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		) ) : array();
		if ( is_wp_error( $categories ) ) {
			$categories = array();
		}

		$has_filters = ( '' !== $q || '' !== $cat || $min > 0 || $max > 0 || 'date_desc' !== $sort );

		// Best sellers (par total_sales) + recommandations (featured ou
		// 3 random) — si WC n'a pas encore de ventes, on retombe sur la
		// liste produits dans l'ordre par defaut.
		$best_sellers = wc_get_products( array(
			'limit'   => 3,
			'status'  => 'publish',
			'orderby' => 'meta_value_num',
			'meta_key' => 'total_sales',
			'order'   => 'DESC',
		) );
		if ( empty( $best_sellers ) ) {
			$best_sellers = array_slice( array_values( wc_get_products( array( 'limit' => 3, 'status' => 'publish' ) ) ), 0, 3 );
		}
		$recos = wc_get_products( array(
			'limit'    => 3,
			'status'   => 'publish',
			'featured' => true,
		) );
		if ( empty( $recos ) ) {
			$recos = array_slice( array_values( wc_get_products( array( 'limit' => 3, 'status' => 'publish', 'orderby' => 'rand' ) ) ), 0, 3 );
		}

		$contact_phone = (string) get_theme_mod( 'ag_cabinet_phone', '' );
		if ( ! $contact_phone && function_exists( 'ag_starter_avocat_get_option' ) ) {
			$contact_phone = (string) ag_starter_avocat_get_option( 'ag_cabinet_phone' );
		}
		$contact_phone_clean = preg_replace( '/[^0-9+]/', '', $contact_phone );

		ob_start();
		?>
		<div class="ag-section ag-boutique ag-business-boutique-page" id="ag-boutique">
			<div class="ag-container ag-business-boutique-page__container">
				<div class="ag-business-boutique-page__layout">
					<div class="ag-business-boutique-page__main">
						<div class="ag-business-boutique-page__toolbar">
							<div class="ag-business-boutique-page__count"><?php
								/* translators: %d : count */
								printf( esc_html( _n( '%d produit', '%d produits', count( $products ), 'ag-business-avocat' ) ), count( $products ) );
							?></div>
							<?php
							// Tri sous forme de pills cliquables (au lieu d'un select
							// HTML natif au look bof). Chaque pill = lien vers la
							// meme URL avec ag_sort=X. La pill active est highlighted.
							$sort_options = array(
								'date_desc'  => __( 'Plus récents', 'ag-business-avocat' ),
								'popularity' => __( 'Populaire', 'ag-business-avocat' ),
								'price_asc'  => __( 'Prix ↑', 'ag-business-avocat' ),
								'price_desc' => __( 'Prix ↓', 'ag-business-avocat' ),
								'name_asc'   => __( 'A-Z', 'ag-business-avocat' ),
							);
							?>
							<nav class="ag-business-boutique-page__sort-pills" aria-label="<?php esc_attr_e( 'Trier les produits', 'ag-business-avocat' ); ?>">
								<span class="ag-business-boutique-page__sort-label"><?php esc_html_e( 'Trier', 'ag-business-avocat' ); ?></span>
								<?php foreach ( $sort_options as $key => $label ) :
									$url = add_query_arg( 'ag_sort', $key );
									$is_active = ( $sort === $key );
									?>
									<a href="<?php echo esc_url( $url ); ?>" class="ag-business-boutique-pill<?php echo $is_active ? ' ag-business-boutique-pill--active' : ''; ?>" <?php echo $is_active ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
								<?php endforeach; ?>
							</nav>
						</div>
						<?php if ( $has_filters ) : ?>
							<p class="ag-business-boutique-page__search-info">
								<?php esc_html_e( 'Filtres actifs', 'ag-business-avocat' ); ?> :
								<?php if ( '' !== $q ) : ?><span class="ag-business-boutique-chip"><?php echo esc_html( $q ); ?></span><?php endif; ?>
								<?php if ( '' !== $cat ) :
									$cat_obj = get_term_by( 'slug', $cat, 'product_cat' );
									$cat_label = $cat_obj && ! is_wp_error( $cat_obj ) ? $cat_obj->name : $cat;
									?><span class="ag-business-boutique-chip"><?php echo esc_html( $cat_label ); ?></span><?php endif; ?>
								<?php if ( $min > 0 || $max > 0 ) : ?><span class="ag-business-boutique-chip"><?php
									if ( $min > 0 && $max > 0 ) echo esc_html( $min . ' € — ' . $max . ' €' );
									elseif ( $min > 0 ) echo esc_html( '≥ ' . $min . ' €' );
									else echo esc_html( '≤ ' . $max . ' €' );
								?></span><?php endif; ?>
								· <a href="<?php echo esc_url( remove_query_arg( array( 'ag_q', 'ag_cat', 'ag_min', 'ag_max', 'ag_sort' ) ) ); ?>"><?php esc_html_e( 'Effacer tout', 'ag-business-avocat' ); ?></a>
							</p>
						<?php endif; ?>
						<?php if ( empty( $products ) ) : ?>
							<p style="text-align:center;padding:40px;"><?php esc_html_e( 'Aucun produit ne correspond à ces critères.', 'ag-business-avocat' ); ?></p>
						<?php else : ?>
							<div class="ag-boutique__grid">
								<?php foreach ( $products as $product ) :
									if ( ! is_object( $product ) ) continue;
									$img_id  = $product->get_image_id();
									$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '';
									$price   = wp_strip_all_tags( $product->get_price_html() );
									$excerpt = $product->get_short_description();
									if ( '' === $excerpt ) {
										$excerpt = wp_trim_words( wp_strip_all_tags( $product->get_description() ), 24 );
									}
									$has_image = ! empty( $img_url );
									?>
									<article class="ag-boutique-card<?php echo $has_image ? ' ag-boutique-card--with-image' : ''; ?>">
										<?php if ( $has_image ) : ?>
											<div class="ag-boutique-card__image">
												<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
											</div>
										<?php endif; ?>
										<h3 class="ag-boutique-card__title"><?php echo esc_html( $product->get_name() ); ?></h3>
										<div class="ag-boutique-card__price"><?php echo esc_html( $price ); ?></div>
										<p class="ag-boutique-card__desc"><?php echo esc_html( $excerpt ); ?></p>
										<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="ag-btn ag-boutique-card__btn"><?php esc_html_e( 'Voir la fiche →', 'ag-business-avocat' ); ?></a>
									</article>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<aside class="ag-business-boutique-sidebar">
						<form class="ag-business-boutique-widget ag-business-boutique-widget--filters" method="get" action="">
							<h3 class="ag-business-boutique-widget__title"><?php esc_html_e( 'Recherche & filtres', 'ag-business-avocat' ); ?></h3>

							<label class="ag-business-booking-form__label">
								<span><?php esc_html_e( 'Mot-clé', 'ag-business-avocat' ); ?></span>
								<input type="search" name="ag_q" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un produit…', 'ag-business-avocat' ); ?>">
							</label>

							<?php if ( ! empty( $categories ) ) : ?>
								<label class="ag-business-booking-form__label">
									<span><?php esc_html_e( 'Catégorie', 'ag-business-avocat' ); ?></span>
									<select name="ag_cat">
										<option value=""><?php esc_html_e( 'Toutes', 'ag-business-avocat' ); ?></option>
										<?php foreach ( $categories as $c ) : ?>
											<option value="<?php echo esc_attr( $c->slug ); ?>" <?php selected( $cat, $c->slug ); ?>>
												<?php echo esc_html( $c->name ); ?> (<?php echo (int) $c->count; ?>)
											</option>
										<?php endforeach; ?>
									</select>
								</label>
							<?php endif; ?>

							<div class="ag-business-boutique-widget__price-range">
								<label class="ag-business-booking-form__label">
									<span><?php esc_html_e( 'Prix min (€)', 'ag-business-avocat' ); ?></span>
									<input type="number" name="ag_min" min="0" step="1" value="<?php echo $min > 0 ? esc_attr( $min ) : ''; ?>" placeholder="0">
								</label>
								<label class="ag-business-booking-form__label">
									<span><?php esc_html_e( 'Prix max (€)', 'ag-business-avocat' ); ?></span>
									<input type="number" name="ag_max" min="0" step="1" value="<?php echo $max > 0 ? esc_attr( $max ) : ''; ?>" placeholder="—">
								</label>
							</div>

							<label class="ag-business-booking-form__label">
								<span><?php esc_html_e( 'Trier par', 'ag-business-avocat' ); ?></span>
								<select name="ag_sort">
									<option value="date_desc" <?php selected( $sort, 'date_desc' ); ?>><?php esc_html_e( 'Plus récents', 'ag-business-avocat' ); ?></option>
									<option value="popularity" <?php selected( $sort, 'popularity' ); ?>><?php esc_html_e( 'Popularité', 'ag-business-avocat' ); ?></option>
									<option value="price_asc" <?php selected( $sort, 'price_asc' ); ?>><?php esc_html_e( 'Prix croissant', 'ag-business-avocat' ); ?></option>
									<option value="price_desc" <?php selected( $sort, 'price_desc' ); ?>><?php esc_html_e( 'Prix décroissant', 'ag-business-avocat' ); ?></option>
									<option value="name_asc" <?php selected( $sort, 'name_asc' ); ?>><?php esc_html_e( 'Nom (A-Z)', 'ag-business-avocat' ); ?></option>
								</select>
							</label>

							<div class="ag-business-boutique-widget__filter-actions">
								<button type="submit" class="ag-btn"><?php esc_html_e( 'Appliquer', 'ag-business-avocat' ); ?></button>
								<?php if ( $has_filters ) : ?>
									<a href="<?php echo esc_url( remove_query_arg( array( 'ag_q', 'ag_cat', 'ag_min', 'ag_max', 'ag_sort' ) ) ); ?>" class="ag-business-boutique-widget__reset"><?php esc_html_e( 'Réinitialiser', 'ag-business-avocat' ); ?></a>
								<?php endif; ?>
							</div>
						</form>

						<section class="ag-business-boutique-widget">
							<h3 class="ag-business-boutique-widget__title"><?php esc_html_e( 'Nos recommandations', 'ag-business-avocat' ); ?></h3>
							<ul class="ag-business-boutique-widget__list">
								<?php foreach ( $recos as $r ) :
									if ( ! is_object( $r ) ) continue;
									$ri = $r->get_image_id();
									$ru = $ri ? wp_get_attachment_image_url( $ri, 'thumbnail' ) : '';
									?>
									<li>
										<a href="<?php echo esc_url( get_permalink( $r->get_id() ) ); ?>">
											<?php if ( $ru ) : ?>
												<span class="ag-business-boutique-widget__thumb" style="background-image:url('<?php echo esc_url( $ru ); ?>');" aria-hidden="true"></span>
											<?php endif; ?>
											<span class="ag-business-boutique-widget__txt">
												<span class="ag-business-boutique-widget__name"><?php echo esc_html( $r->get_name() ); ?></span>
												<span class="ag-business-boutique-widget__price"><?php echo wp_kses_post( $r->get_price_html() ); ?></span>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>

						<section class="ag-business-boutique-widget">
							<h3 class="ag-business-boutique-widget__title"><?php esc_html_e( 'Meilleures ventes', 'ag-business-avocat' ); ?></h3>
							<ul class="ag-business-boutique-widget__list">
								<?php foreach ( $best_sellers as $b ) :
									if ( ! is_object( $b ) ) continue;
									$bi = $b->get_image_id();
									$bu = $bi ? wp_get_attachment_image_url( $bi, 'thumbnail' ) : '';
									?>
									<li>
										<a href="<?php echo esc_url( get_permalink( $b->get_id() ) ); ?>">
											<?php if ( $bu ) : ?>
												<span class="ag-business-boutique-widget__thumb" style="background-image:url('<?php echo esc_url( $bu ); ?>');" aria-hidden="true"></span>
											<?php endif; ?>
											<span class="ag-business-boutique-widget__txt">
												<span class="ag-business-boutique-widget__name"><?php echo esc_html( $b->get_name() ); ?></span>
												<span class="ag-business-boutique-widget__price"><?php echo wp_kses_post( $b->get_price_html() ); ?></span>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>

						<section class="ag-business-boutique-widget ag-business-boutique-widget--contact">
							<h3 class="ag-business-boutique-widget__title"><?php esc_html_e( 'Réserver un rendez-vous', 'ag-business-avocat' ); ?></h3>
							<?php if ( $contact_phone ) : ?>
								<a class="ag-business-boutique-widget__phone" href="tel:<?php echo esc_attr( $contact_phone_clean ); ?>">
									<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.5.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.5.1.4 0 .8-.2 1l-2.2 2.3z"/></svg>
									<?php echo esc_html( $contact_phone ); ?>
								</a>
							<?php endif; ?>
							<form class="ag-business-boutique-widget__form ag-business-booking-form" method="post" action="<?php echo esc_url( home_url( '/#ag-rdv' ) ); ?>">
								<?php wp_nonce_field( 'ag_rdv_send', 'ag_rdv_nonce' ); ?>
								<input type="hidden" name="ag_rdv_domaine" value="<?php esc_attr_e( 'Réservation depuis la boutique', 'ag-business-avocat' ); ?>">
								<input type="hidden" name="ag_rdv_format" value="cabinet">
								<input type="hidden" name="ag_rdv_message" value="">
								<label class="ag-business-booking-form__label">
									<span><?php esc_html_e( 'Date souhaitée', 'ag-business-avocat' ); ?></span>
									<input type="date" name="ag_rdv_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required>
								</label>
								<label class="ag-business-booking-form__label">
									<span><?php esc_html_e( 'Créneau', 'ag-business-avocat' ); ?></span>
									<select name="ag_rdv_slot" required>
										<option value=""><?php esc_html_e( '— Choisir un créneau —', 'ag-business-avocat' ); ?></option>
										<option value="09:00">09:00 — 10:00</option>
										<option value="10:00">10:00 — 11:00</option>
										<option value="11:00">11:00 — 12:00</option>
										<option value="14:00">14:00 — 15:00</option>
										<option value="15:00">15:00 — 16:00</option>
										<option value="16:00">16:00 — 17:00</option>
										<option value="17:00">17:00 — 18:00</option>
									</select>
								</label>
								<label>
									<span class="screen-reader-text"><?php esc_html_e( 'Votre nom', 'ag-business-avocat' ); ?></span>
									<input type="text" name="ag_rdv_nom" placeholder="<?php esc_attr_e( 'Nom *', 'ag-business-avocat' ); ?>" required>
								</label>
								<label>
									<span class="screen-reader-text"><?php esc_html_e( 'Votre email', 'ag-business-avocat' ); ?></span>
									<input type="email" name="ag_rdv_email" placeholder="<?php esc_attr_e( 'Email *', 'ag-business-avocat' ); ?>" required>
								</label>
								<label class="ag-business-boutique-widget__rgpd">
									<input type="checkbox" name="ag_rdv_rgpd" value="1" required>
									<span><?php esc_html_e( 'J\'accepte le traitement de mes données pour la prise de rendez-vous.', 'ag-business-avocat' ); ?></span>
								</label>
								<div class="ag-rdv__honeypot" aria-hidden="true" style="position:absolute;left:-9999px;">
									<label><input type="text" name="ag_rdv_website" tabindex="-1" autocomplete="off"></label>
								</div>
								<button type="submit" name="ag_rdv_submit" class="ag-btn"><?php esc_html_e( 'Réserver', 'ag-business-avocat' ); ?></button>
							</form>
						</section>
					</aside>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
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
		// Slugs alternatifs auto-crees par WP/WC/themes — si l'un
		// d'eux existe (meme en brouillon), on ne cree pas de doublon.
		$alias_map = array(
			'politique-confidentialite' => array( 'privacy-policy', 'politique-de-confidentialite', 'politique-confidentialite-rgpd' ),
			'politique-retour'          => array( 'refund_returns', 'remboursement-et-retour', 'politique-de-retour', 'politique-de-remboursement', 'politique-de-retour-et-de-remboursement' ),
			'cgv'                       => array( 'terms', 'conditions-generales-de-vente', 'conditions-generales', 'terms-and-conditions' ),
			'mentions-legales'          => array( 'legal-notice', 'mentions-legal' ),
			'politique-cookies'         => array( 'politique-de-cookies', 'cookies-policy', 'cookie-policy' ),
		);
		foreach ( $this->get_legal_pages_data() as $slug => $data ) {
			$check = isset( $alias_map[ $slug ] ) ? array_merge( array( $slug ), $alias_map[ $slug ] ) : array( $slug );
			if ( $this->page_exists_any_status( $check ) ) {
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
	 * Verifie si une page avec l'un des slugs donnes existe dans
	 * n'importe quel statut (publish, draft, pending, private, future).
	 * Plus permissif que get_page_by_path (qui ne voit que publish).
	 *
	 * @param string|array $slugs Un slug ou liste de slugs alternatifs.
	 * @param string       $post_type post_type (default 'page').
	 */
	private function page_exists_any_status( $slugs, $post_type = 'page' ) {
		$slugs = (array) $slugs;
		foreach ( $slugs as $slug ) {
			$found = get_posts( array(
				'name'           => $slug,
				'post_type'      => $post_type,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			) );
			if ( ! empty( $found ) ) {
				return true;
			}
		}
		return false;
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

		// Section Boutique — toggle home pour eviter doublon avec page /boutique/
		$wp_customize->add_setting( 'ag_business_hide_home_boutique', array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_business_hide_home_boutique', array(
			'type'        => 'checkbox',
			'label'       => __( 'Masquer la section Boutique sur la home', 'ag-business-avocat' ),
			'description' => __( 'Cocher si la page /boutique/ suffit (evite le doublon).', 'ag-business-avocat' ),
			'section'     => 'ag_business_boutique',
		) );

		// Section Honoraires — liens Stripe
		$wp_customize->add_section( 'ag_business_stripe', array(
			'title'       => __( 'Honoraires — liens Stripe', 'ag-business-avocat' ),
			'description' => __( "Coller l'URL d'un Stripe Payment Link pour chaque palier. Pour creer un lien : dashboard Stripe > Produits > Ajouter > Creer un lien de paiement. Si le champ est vide, aucun bouton n'apparait sur la card.", 'ag-business-avocat' ),
			'panel'       => 'ag_business_panel',
		) );
		$tiers = array(
			'first' => __( 'URL Stripe — 1ère consultation', 'ag-business-avocat' ),
			'pack'  => __( 'URL Stripe — Forfait', 'ag-business-avocat' ),
			'hour'  => __( 'URL Stripe — Au temps passé', 'ag-business-avocat' ),
		);
		foreach ( $tiers as $key => $label ) {
			$setting_key = "ag_business_honoraires_{$key}_stripe";
			$wp_customize->add_setting( $setting_key, array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( $setting_key, array(
				'label'   => $label,
				'section' => 'ag_business_stripe',
				'type'    => 'url',
			) );
		}

		// Section Fondateur — photo override
		$wp_customize->add_section( 'ag_business_founder', array(
			'title'       => __( 'Fondateur — section home', 'ag-business-avocat' ),
			'description' => __( "Photo du fondateur affichee dans la section 'Le fondateur et les associes' sur la home (et la card cabinet). Coller une URL d'image (mediatheque WP, Unsplash, etc.). Exemples Unsplash valides pour un homme age barbu : https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=1400&q=85 — ou cherche \"old man beard\" sur unsplash.com et copie le lien direct (botton Download > clic droit > copy image link).", 'ag-business-avocat' ),
			'panel'       => 'ag_business_panel',
		) );
		$wp_customize->add_setting( 'ag_business_founder_photo', array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_business_founder_photo', array(
			'label'   => __( 'URL photo fondateur', 'ag-business-avocat' ),
			'section' => 'ag_business_founder',
			'type'    => 'url',
		) );

		$wp_customize->add_setting( 'ag_business_founder_signature', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_business_founder_signature', array(
			'label'       => __( 'Texte de la signature', 'ag-business-avocat' ),
			'description' => __( "Texte pose en cursive sur la photo du fondateur. Vide = initiale + patronyme genere automatiquement.", 'ag-business-avocat' ),
			'section'     => 'ag_business_founder',
			'type'        => 'text',
		) );
	}

	public function sanitize_boutique_symbol( $value ) {
		$valid = array( 'stars', 'scales', 'gavel', 'pillar', 'none' );
		return in_array( $value, $valid, true ) ? $value : 'stars';
	}

	/**
	 * Affiche un admin notice qui propose d'installer un plugin Stripe
	 * (WP Simple Pay) quand Business est actif et qu'aucun plugin Stripe
	 * n'est detecte. Lien direct vers update.php install.
	 */
	public function stripe_admin_notice() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		// Si un plugin Stripe-like est deja la, on n'affiche rien.
		if ( class_exists( 'SimplePay\\Core\\Plugin' )
			|| class_exists( 'WC_Stripe_Helper' )
			|| function_exists( 'simpay_get_setting' ) ) {
			return;
		}
		// slug WP officiel : "stripe" (WP Simple Pay)
		$install_url = wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=stripe' ),
			'install-plugin_stripe'
		);
		$browse_url = self_admin_url( 'plugin-install.php?s=stripe&tab=search&type=term' );
		?>
		<div class="notice notice-info" style="border-left-color:#635BFF;">
			<p style="font-size:14px;">
				<strong><?php esc_html_e( 'AG Business :', 'ag-business-avocat' ); ?></strong>
				<?php esc_html_e( 'pour accepter les paiements Stripe directement sur le site, installez WP Simple Pay (gratuit).', 'ag-business-avocat' ); ?>
				&nbsp;
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary"><?php esc_html_e( 'Installer WP Simple Pay', 'ag-business-avocat' ); ?></a>
				<a href="<?php echo esc_url( $browse_url ); ?>" class="button"><?php esc_html_e( 'Voir tous les plugins Stripe', 'ag-business-avocat' ); ?></a>
				&nbsp;
				<em style="font-size:.85em;color:#666;"><?php esc_html_e( 'Ou utilisez les Stripe Payment Links sans plugin (URLs paramétrables dans le Customizer).', 'ag-business-avocat' ); ?></em>
			</p>
		</div>
		<?php
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
			if ( ! $page && ! $this->page_exists_any_status( $slug ) ) {
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
			if ( ! $page ) {
				continue; // page existe en draft mais pas published — on saute
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
	/**
	 * Liste les URLs d'images des 3 offres dans le meme ordre que
	 * render_boutique() affiche les cards. Permet a JS d'injecter
	 * une .ag-boutique-card__image en fallback Customizer (sans WC)
	 * ou en complement quand le produit WC n'a pas d'image.
	 */
	private function get_boutique_offer_images() {
		$images = array();
		foreach ( $this->get_default_offers_data() as $slug => $data ) {
			$images[] = isset( $data['image'] ) ? $data['image'] : '';
		}
		return $images;
	}

	private function get_default_offers_data() {
		return array(
			'pack-3-consultations-telephoniques' => array(
				'title'       => '3 consultations téléphoniques',
				'desc'        => 'Pack de 3 consultations de 30 min, valables 6 mois. Conseil juridique sur tout domaine.',
				'image'       => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&q=85',
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
				'image'       => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&q=85',
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
				'image'       => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=85',
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

	/**
	 * Construit le HTML d'un groupe d'equipe (associes OU collaborateurs)
	 * en tant que section autonome. Permet a JS d'inserer chaque groupe
	 * a une position differente avec une citation entre.
	 *
	 * @param string $type 'associates' ou 'collaborators'
	 * @param string $title titre H2 du groupe
	 */
	private function render_team_group_html( $type, $title ) {
		$team = $this->get_default_team_data();
		if ( ! isset( $team[ $type ] ) || empty( $team[ $type ] ) ) {
			return '';
		}
		$members = $team[ $type ];
		ob_start();
		?>
		<section class="ag-business-team-full ag-business-team-full--<?php echo esc_attr( $type ); ?>">
			<h2 class="ag-business-team-full__group-title"><?php echo esc_html( $title ); ?></h2>
			<div class="ag-business-team-full__grid ag-business-team-full__grid--<?php echo esc_attr( $type ); ?>">
				<?php foreach ( $members as $m ) {
					echo $this->render_team_card_html( $m ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	private function render_team_card_html( $m ) {
		ob_start();
		?>
		<article class="ag-business-team-card">
			<div class="ag-business-team-card__photo" style="background-image:url('<?php echo esc_url( $m['photo'] ); ?>');" aria-hidden="true"></div>
			<div class="ag-business-team-card__body">
				<header class="ag-business-team-card__header">
					<h3 class="ag-business-team-card__name"><?php echo esc_html( $m['name'] ); ?></h3>
					<p class="ag-business-team-card__role"><?php echo esc_html( $m['role'] ); ?></p>
					<p class="ag-business-team-card__barreau"><?php echo esc_html( $m['barreau'] ); ?></p>
				</header>
				<p class="ag-business-team-card__bio"><?php echo esc_html( $m['bio'] ); ?></p>
				<div class="ag-business-team-card__details">
					<div class="ag-business-team-card__block">
						<h4><?php esc_html_e( 'Spécialités', 'ag-business-avocat' ); ?></h4>
						<ul>
							<?php foreach ( $m['specialties'] as $s ) : ?>
								<li><?php echo esc_html( $s ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="ag-business-team-card__block">
						<h4><?php esc_html_e( 'Formation', 'ag-business-avocat' ); ?></h4>
						<ul>
							<?php foreach ( $m['education'] as $e ) : ?>
								<li><?php echo esc_html( $e ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="ag-business-team-card__block">
						<h4><?php esc_html_e( 'Langues', 'ag-business-avocat' ); ?></h4>
						<p><?php echo esc_html( implode( ', ', $m['languages'] ) ); ?></p>
					</div>
				</div>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	/**
	 * Profils par defaut, separes en 3 groupes :
	 *   - founder      : 1 fondateur (65 ans, mis en avant)
	 *   - associates   : 2 avocats associes (compacts, en colonne a droite)
	 *   - collaborators: 6 membres (5 collaborateurs + 1 conseiller fiscal)
	 * Photos depuis Unsplash (portraits libres), remplacables.
	 */
	private function get_default_team_data() {
		return array(
			'founder'    => array(
				array(
					'name'        => 'Maître Henri DELACROIX',
					'role'        => 'Avocat fondateur du cabinet',
					'barreau'     => 'Barreau de Paris — Inscrit depuis 1985',
					// Photo fournie par le client (iStock). Peut etre
					// remplacee via le Customizer (Apparence > Personnaliser
					// > AG Business Options > Fondateur > URL photo).
					'photo'       => 'https://media.istockphoto.com/id/1962297666/fr/photo/avocat-professionnel-assis-dans-une-salle-de-conf%C3%A9rence-et-examinant-des-documents.jpg?s=612x612&w=0&k=20&c=p5W94edsF3Ewl-moMuHLPMDlxJiDTvej_hW0aAdmOD4=',
					'specialties' => array(
						'Droit civil et commercial',
						'Contentieux complexes',
						'Arbitrage et médiation',
						'Conseil stratégique aux dirigeants',
					),
					'education'   => array(
						'Doctorat en Droit privé — Université Paris II Panthéon-Assas',
						'DEA Droit des affaires — Université Paris I Panthéon-Sorbonne',
						'CAPA — École de Formation du Barreau (EFB)',
						'Auditeur libre — Institut des Hautes Études sur la Justice (IHEJ)',
					),
					'languages'   => array( 'Français', 'Anglais', 'Italien', 'Latin juridique' ),
					'bio'         => "Plus de quarante années passées dans les prétoires français. Fondateur du cabinet en 1992, il en a forgé la réputation d'exigence et de discrétion. Ancien membre du Conseil de l'Ordre, il continue d'assurer les dossiers les plus sensibles et de transmettre son expérience aux jeunes générations.",
				),
			),
			'associates' => array(
				array(
					'name'        => 'Maître Sophie DUPONT',
					'role'        => 'Avocate associée',
					'barreau'     => 'Barreau de Paris — Inscrite depuis 2008',
					'photo'       => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&q=85',
					'specialties' => array(
						'Droit des affaires',
						'Fusions-acquisitions',
						'Droit des sociétés',
						'Contentieux commercial',
					),
					'education'   => array(
						'Master 2 Droit des affaires — Université Paris II Panthéon-Assas',
						'DJCE (Diplôme de Juriste Conseil d\'Entreprise)',
						'CAPA — École de Formation du Barreau (EFB)',
					),
					'languages'   => array( 'Français', 'Anglais', 'Italien' ),
					'bio'         => "15 ans d'expérience en accompagnement de PME et ETI. Approche pragmatique des contentieux complexes.",
				),
				array(
					'name'        => 'Maître Philippe MARTIN',
					'role'        => 'Avocat associé',
					'barreau'     => 'Barreau de Paris — Inscrit depuis 2012',
					'photo'       => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=600&q=85',
					'specialties' => array(
						'Droit pénal',
						'Droit pénal des affaires',
						'Procédure pénale',
						'Garde à vue 24h/24',
					),
					'education'   => array(
						'Master 2 Droit pénal — Université Paris I Panthéon-Sorbonne',
						'CAPA — EFB (Major de promotion)',
					),
					'languages'   => array( 'Français', 'Anglais', 'Espagnol' ),
					'bio'         => 'Plaidoiries devant toutes les juridictions, de l\'instruction à la cour d\'assises.',
				),
			),
			'collaborators' => array(
				array(
					'name'        => 'Maître Camille LEROUX',
					'role'        => 'Avocate collaboratrice senior',
					'barreau'     => 'Barreau de Paris — Inscrite depuis 2016',
					'photo'       => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=600&q=85',
					'specialties' => array(
						'Droit du travail',
						'Droit social',
						'Contentieux prud\'homal',
						'Négociation de ruptures conventionnelles',
					),
					'education'   => array(
						'Master 2 Droit social — Université Paris-Nanterre',
						'Diplôme universitaire en Droit de la sécurité sociale',
						'CAPA — EFB',
					),
					'languages'   => array( 'Français', 'Anglais' ),
					'bio'         => 'Spécialiste du droit du travail côté salariés et employeurs. A obtenu plus de 200 décisions favorables devant les Conseils de Prud\'hommes.',
				),
				array(
					'name'        => 'Maître Antoine BERNARD',
					'role'        => 'Avocat collaborateur',
					'barreau'     => 'Barreau de Paris — Inscrit depuis 2020',
					'photo'       => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=85',
					'specialties' => array(
						'Droit immobilier',
						'Droit de la copropriété',
						'Baux commerciaux et d\'habitation',
						'Vices cachés et constructions',
					),
					'education'   => array(
						'Master 2 Droit immobilier — Université Aix-Marseille',
						'Diplôme universitaire en Construction',
						'CAPA — EFB',
					),
					'languages'   => array( 'Français', 'Anglais' ),
					'bio'         => 'Expertise pointue en contentieux immobilier et copropriété. Conseille promoteurs, syndics et particuliers dans toutes leurs problématiques.',
				),
				array(
					'name'        => 'Maître Léa DUBOIS',
					'role'        => 'Avocate collaboratrice',
					'barreau'     => 'Barreau de Paris — Inscrite depuis 2021',
					'photo'       => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=600&q=85',
					'specialties' => array(
						'Droit de la famille',
						'Divorce et séparation',
						'Droit des successions',
						'Médiation familiale',
					),
					'education'   => array(
						'Master 2 Droit de la famille — Université Paris II Panthéon-Assas',
						'Diplôme universitaire en Médiation',
						'CAPA — EFB',
					),
					'languages'   => array( 'Français', 'Anglais', 'Allemand' ),
					'bio'         => 'Approche humaine et discrète des dossiers familiaux. Privilégie la médiation et le consentement mutuel quand c\'est possible.',
				),
				array(
					'name'        => 'Maître Julien MOREAU',
					'role'        => 'Avocat collaborateur',
					'barreau'     => 'Barreau de Paris — Inscrit depuis 2019',
					'photo'       => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=600&q=85',
					'specialties' => array(
						'Droit pénal des affaires',
						'Compliance et conformité',
						'Enquêtes internes',
						'Procédure pénale',
					),
					'education'   => array(
						'Master 2 Droit pénal financier — Université Paris II Panthéon-Assas',
						'Certificat de Compliance Officer — ESSEC',
						'CAPA — EFB',
					),
					'languages'   => array( 'Français', 'Anglais', 'Allemand' ),
					'bio'         => "Accompagne les dirigeants et entreprises dans leurs problématiques de conformité et de défense pénale. Ancien stagiaire du parquet financier.",
				),
				array(
					'name'        => 'Maître Inès CHEVALIER',
					'role'        => 'Avocate collaboratrice',
					'barreau'     => 'Barreau de Paris — Inscrite depuis 2022',
					'photo'       => 'https://images.unsplash.com/photo-1592621385612-4d7129426394?w=600&q=85',
					'specialties' => array(
						'Droit du numérique et données personnelles',
						'Propriété intellectuelle',
						'Contentieux contractuel',
						'RGPD et conformité',
					),
					'education'   => array(
						'Master 2 Droit du numérique — Université Paris II Panthéon-Assas',
						'DPO certifié (CNIL)',
						'CAPA — EFB',
					),
					'languages'   => array( 'Français', 'Anglais', 'Italien' ),
					'bio'         => "Conseille startups, éditeurs et grands comptes sur leurs enjeux numériques, contractuels et data. Pilote les audits RGPD du cabinet.",
				),
				array(
					'name'        => 'Monsieur Bertrand FOURNIER',
					'role'        => 'Conseiller juridique fiscal',
					'barreau'     => "Diplôme d'Avocat fiscaliste — non inscrit au barreau",
					'photo'       => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600&q=85',
					'specialties' => array(
						'Conseil fiscal aux particuliers',
						'Fiscalité du patrimoine et des successions',
						'Optimisation fiscale des entreprises',
						'Contrôles et contentieux fiscaux',
					),
					'education'   => array(
						'Master 2 Droit fiscal — Université Paris II Panthéon-Assas',
						'Diplôme Supérieur de Notariat (DSN)',
						'Ancien inspecteur des finances publiques',
					),
					'languages'   => array( 'Français', 'Anglais' ),
					'bio'         => "Vingt ans d'expérience au service des contribuables. Accompagne particuliers, dirigeants et familles dans toutes leurs déclarations, optimisations et contentieux fiscaux.",
				),
			),
		);
	}

	/**
	 * Citations juridiques + image de fond a injecter entre les
	 * sections de chaque page interne (cabinet, honoraires, expertise,
	 * rendez-vous). Format : tableau d'objets {quote, author, bg,
	 * insertAfter (selecteur DOM)}. La home a deja ses propres
	 * citations parallax via render_parallax_quote_X de pro-features.
	 */
	private function get_page_citations_data() {
		$citations = array();

		// REGLE : jamais de citation directement apres le titre de page
		// (.ag-page-hero). Les transitions s'inserent uniquement entre
		// des sections de contenu.

		if ( is_page( 'cabinet' ) ) {
			// Cabinet ordre : fondateur > associes > collaborateurs.
			// Citations : Montesquieu (founder→associes), Cicéron
			// (associes→collaborateurs), Pascal (collaborateurs→map).
			$citations[] = array(
				'quote'       => 'L\'expérience fait plus de savants que les livres.',
				'author'      => 'Montesquieu, De l\'esprit des lois',
				'bg'          => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1920&q=85',
				'insertAfter' => '.ag-business-team-full--founder',
			);
			$citations[] = array(
				'quote'       => 'Le silence est une chose admirable, mais qui demande une grande force pour ne pas être faiblesse.',
				'author'      => 'Cicéron',
				'bg'          => 'https://images.unsplash.com/photo-1589216532372-1c2a367900d9?w=1920&q=85',
				'insertAfter' => '.ag-business-team-full--associates',
			);
			$citations[] = array(
				'quote'       => 'La justice sans la force est impuissante ; la force sans la justice est tyrannique.',
				'author'      => 'Blaise Pascal',
				'bg'          => 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1920&q=85',
				'insertAfter' => '.ag-business-team-full--collaborators',
			);
		}

		if ( is_page( 'honoraires' ) ) {
			// Honoraires : citation entre la grille de paliers et la
			// section FAQ (page-article).
			$citations[] = array(
				'quote'       => 'Le contrat est la loi des parties.',
				'author'      => 'Code civil, article 1103',
				'bg'          => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=85',
				'insertAfter' => '.ag-honoraires',
			);
		}

		if ( is_page( 'expertise' ) ) {
			// Expertise : citation apres la grille des domaines.
			$citations[] = array(
				'quote'       => 'Nul n\'est censé ignorer la loi.',
				'author'      => 'Adage du droit français',
				'bg'          => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1920&q=85',
				'insertAfter' => '.ag-domaines',
			);
		}

		// Rendez-vous : pas de citation (page courte form-only).

		return $citations;
	}

	/**
	 * Cree les pages compte (Mon compte, Connexion, Inscription) si
	 * elles n'existent pas. Si WooCommerce est actif, on utilise son
	 * shortcode [woocommerce_my_account] qui gere connexion + register
	 * + tableau de bord. Sinon notre shortcode [ag_business_account].
	 *
	 * Active aussi les inscriptions WP (users_can_register) si pas
	 * deja active. Idempotent.
	 */
	public function ensure_account_pages() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Active inscriptions ouvertes si pas deja
		if ( '0' === get_option( 'users_can_register' ) || ! get_option( 'users_can_register' ) ) {
			update_option( 'users_can_register', 1 );
		}

		$wc_active = class_exists( 'WooCommerce' );

		$pages = array(
			'mon-compte' => array(
				'title'   => 'Mon compte',
				'content' => $wc_active
					? '[woocommerce_my_account]'
					: '<p>Bienvenue sur votre espace personnel.</p>[ag_business_account]',
			),
			'connexion'  => array(
				'title'   => 'Connexion',
				'content' => $wc_active
					? '<p>[woocommerce_my_account]</p>'
					: '<p>Identifiez-vous pour accéder à votre espace.</p>[ag_business_account view="login"]',
			),
			'inscription' => array(
				'title'   => 'Inscription',
				'content' => $wc_active
					? '<p>[woocommerce_my_account]</p>'
					: '<p>Créez votre compte pour suivre vos commandes et rendez-vous.</p>[ag_business_account view="register"]',
			),
		);

		// Slugs alternatifs (WC cree my-account, theme parfois variants FR)
		$account_aliases = array(
			'mon-compte'  => array( 'my-account', 'compte' ),
			'connexion'   => array( 'login', 'se-connecter' ),
			'inscription' => array( 'register', 's-inscrire' ),
		);
		foreach ( $pages as $slug => $data ) {
			$check = isset( $account_aliases[ $slug ] ) ? array_merge( array( $slug ), $account_aliases[ $slug ] ) : array( $slug );
			if ( $this->page_exists_any_status( $check ) ) {
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

		// Configure les pages WooCommerce si WC est actif et que les
		// pages 'myaccount' / 'shop' n'ont pas ete configurees.
		if ( $wc_active ) {
			$mon_compte = get_page_by_path( 'mon-compte' );
			if ( $mon_compte && ! get_option( 'woocommerce_myaccount_page_id' ) ) {
				update_option( 'woocommerce_myaccount_page_id', $mon_compte->ID );
			}
		}
	}

	/**
	 * Shortcode [ag_business_account] : fallback compte sans WooCommerce.
	 * Vues : 'login' (formulaire connexion), 'register' (inscription),
	 * ou auto (dashboard si connecte, login si non).
	 */
	public function render_account_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'view' => 'auto' ), $atts );
		$view = $atts['view'];

		ob_start();

		if ( is_user_logged_in() && 'login' !== $view && 'register' !== $view ) {
			$user = wp_get_current_user();
			?>
			<div class="ag-business-account ag-business-account--dashboard">
				<h3 class="ag-business-account__greeting">Bonjour <?php echo esc_html( $user->display_name ); ?></h3>
				<p>Connecté en tant que <strong><?php echo esc_html( $user->user_email ); ?></strong></p>
				<ul class="ag-business-account__links">
					<li><a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">Modifier mon profil</a></li>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<li><a href="<?php echo esc_url( admin_url() ); ?>">Tableau de bord</a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Se déconnecter</a></li>
				</ul>
			</div>
			<?php
		} elseif ( 'register' === $view ) {
			?>
			<div class="ag-business-account ag-business-account--register">
				<h3>Créer un compte</h3>
				<form method="post" action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" class="ag-business-account-form">
					<p>
						<label for="user_login">Identifiant</label>
						<input type="text" name="user_login" id="user_login" required autocomplete="username">
					</p>
					<p>
						<label for="user_email">Email</label>
						<input type="email" name="user_email" id="user_email" required autocomplete="email">
					</p>
					<p class="ag-business-account-form__submit">
						<button type="submit" class="ag-btn">S'inscrire</button>
					</p>
					<p class="ag-business-account-form__footnote">
						Déjà un compte ? <a href="<?php echo esc_url( home_url( '/connexion/' ) ); ?>">Se connecter</a>
					</p>
				</form>
			</div>
			<?php
		} else {
			// Login (par defaut)
			?>
			<div class="ag-business-account ag-business-account--login">
				<h3>Se connecter</h3>
				<?php
				wp_login_form( array(
					'redirect'       => home_url( '/mon-compte/' ),
					'label_username' => 'Identifiant ou email',
					'label_password' => 'Mot de passe',
					'label_remember' => 'Se souvenir de moi',
					'label_log_in'   => 'Se connecter',
				) );
				?>
				<p class="ag-business-account-form__footnote">
					Pas encore de compte ? <a href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>">S'inscrire</a>
					&nbsp;·&nbsp;
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Mot de passe oublié ?</a>
				</p>
			</div>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Sur la page single d'un ag_domaine, append au content :
	 *   - Une section 'Cas concrets traites' (5 exemples detailles)
	 *   - Une FAQ accordeon avec 5 questions juridiques courantes
	 * Donnees specifiques par slug. Si le slug n'a pas de donnees,
	 * append rien (skip).
	 */
	public function append_domaine_extras( $content ) {
		if ( ! $this->is_active() ) {
			return $content;
		}
		if ( ! is_singular( 'ag_domaine' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$slug = get_post_field( 'post_name', get_the_ID() );
		$data = $this->get_domaine_extras_data();
		if ( ! isset( $data[ $slug ] ) ) {
			return $content;
		}
		return $content . $this->render_domaine_extras_html( $data[ $slug ] );
	}

	private function render_domaine_extras_html( $extras ) {
		ob_start();
		?>
		<div class="ag-business-domaine-grid">
			<section class="ag-business-domaine-cas">
				<h2 class="ag-business-domaine-cas__title"><?php esc_html_e( 'Cas concrets', 'ag-business-avocat' ); ?></h2>
				<p class="ag-business-domaine-cas__lead"><?php esc_html_e( 'Quelques exemples représentatifs de dossiers traités.', 'ag-business-avocat' ); ?></p>
				<ul class="ag-business-domaine-cas__list">
					<?php foreach ( $extras['cas_concrets'] as $cas ) : ?>
						<li class="ag-business-cas-item">
							<h3 class="ag-business-cas-item__title"><?php echo esc_html( $cas['titre'] ); ?></h3>
							<p class="ag-business-cas-item__desc"><?php echo esc_html( $cas['desc'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="ag-business-domaine-faq">
				<h2 class="ag-business-domaine-faq__title"><?php esc_html_e( 'Questions fréquentes', 'ag-business-avocat' ); ?></h2>
				<div class="ag-business-faq">
					<?php foreach ( $extras['faq'] as $idx => $qa ) : ?>
						<details class="ag-business-faq__entry" name="ag-business-domaine-faq"<?php echo 0 === $idx ? ' open' : ''; ?>>
							<summary class="ag-business-faq__question"><?php echo esc_html( $qa['q'] ); ?></summary>
							<div class="ag-business-faq__answer"><?php echo esc_html( $qa['a'] ); ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
		<?php
		// Citation : une seule transition image entre 2-col et temoignages.
		// Prefere citations[1] (apres FAQ historique), sinon [0].
		$cite = null;
		if ( ! empty( $extras['citations'][1] ) ) {
			$cite = $extras['citations'][1];
		} elseif ( ! empty( $extras['citations'][0] ) ) {
			$cite = $extras['citations'][0];
		}
		if ( $cite ) {
			echo $this->render_domaine_citation_html( $cite ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Temoignages clients
		$testimonials = ! empty( $extras['testimonials'] ) ? $extras['testimonials'] : $this->get_default_testimonials();
		echo $this->render_domaine_testimonials_html( $testimonials ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return ob_get_clean();
	}

	private function render_domaine_testimonials_html( $items ) {
		if ( empty( $items ) ) {
			return '';
		}
		ob_start();
		?>
		<section class="ag-business-domaine-testimonials">
			<h2 class="ag-business-domaine-testimonials__title"><?php esc_html_e( 'Témoignages clients', 'ag-business-avocat' ); ?></h2>
			<div class="ag-business-domaine-testimonials__grid">
				<?php foreach ( $items as $t ) : ?>
					<article class="ag-business-testimonial-card">
						<p class="ag-business-testimonial-card__quote"><?php echo esc_html( $t['quote'] ); ?></p>
						<footer class="ag-business-testimonial-card__author">
							<span class="ag-business-testimonial-card__name"><?php echo esc_html( $t['name'] ); ?></span>
							<?php if ( ! empty( $t['role'] ) ) : ?>
								<span class="ag-business-testimonial-card__role"><?php echo esc_html( $t['role'] ); ?></span>
							<?php endif; ?>
						</footer>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	private function get_default_testimonials() {
		return array(
			array(
				'quote' => 'Accompagnement remarquable du début à la fin. Disponibilité, écoute, et stratégie juridique solide. Je recommande sans hésitation.',
				'name'  => 'Sophie M.',
				'role'  => 'Cliente — dirigeante de PME',
			),
			array(
				'quote' => 'Cabinet sérieux et discret, des conseils clairs sans jargon inutile. Mon dossier a abouti dans les délais annoncés.',
				'name'  => 'Jean-Marc T.',
				'role'  => 'Client — particulier',
			),
			array(
				'quote' => "Une expertise pointue dans un domaine pourtant très technique. J'ai été défendu avec rigueur et humanité.",
				'name'  => 'Élisabeth R.',
				'role'  => 'Cliente — particulier',
			),
		);
	}

	private function render_domaine_citation_html( $cite ) {
		ob_start();
		?>
		<section class="ag-parallax ag-parallax-business ag-business-domaine-citation" style="background-image:url('<?php echo esc_url( $cite['bg'] ); ?>');">
			<div class="ag-parallax__overlay"></div>
			<div class="ag-parallax__content">
				<p class="ag-parallax__quote"><?php echo esc_html( $cite['quote'] ); ?></p>
				<p class="ag-parallax__caption">— <?php echo esc_html( $cite['author'] ); ?></p>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * Donnees Cas concrets + FAQ par slug de domaine.
	 * 5 cas + 5 Q/R par domaine — peut etre etendu plus tard.
	 */
	private function get_domaine_extras_data() {
		return array(
			'droit-des-affaires' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Création d\'une SAS familiale', 'desc' => 'Choix de la forme juridique, rédaction des statuts, pacte d\'associés, immatriculation au RCS — accompagnement complet de A à Z.' ),
					array( 'titre' => 'Cession de fonds de commerce', 'desc' => 'Audit, négociation du prix, rédaction de l\'acte, formalités de publication, séquestre du prix — protection vendeur et acquéreur.' ),
					array( 'titre' => 'Litige entre associés', 'desc' => 'Médiation préalable, action en exclusion, dissolution-liquidation amiable ou judiciaire — résolution dans le respect du pacte.' ),
					array( 'titre' => 'Négociation de bail commercial 3-6-9', 'desc' => 'Analyse des clauses, négociation du loyer, dépôt de garantie, état des lieux, déspécialisation — défense des intérêts long terme.' ),
					array( 'titre' => 'Procédure collective (sauvegarde)', 'desc' => 'Préparation du dossier de sauvegarde, déclaration des créances, plan de continuation, dialogue avec le mandataire judiciaire.' ),
				),
				'faq' => array(
					array( 'q' => 'Quelle forme juridique choisir : SARL ou SAS ?', 'a' => 'La SAS offre plus de souplesse statutaire (pacte, classes d\'actions) et une meilleure image investisseurs. La SARL est plus encadrée mais souvent moins coûteuse à gérer. Le choix dépend du nombre d\'associés, des projets de financement et de la stratégie patrimoniale.' ),
					array( 'q' => 'Combien coûte la création d\'une société ?', 'a' => 'Comptez 1500 à 3500 € HT pour une création simple incluant statuts, pacte, formalités. Tarif forfaitaire convenu en convention d\'honoraires. Aides Pôle Emploi (ACRE) et JEI peuvent réduire le coût net.' ),
					array( 'q' => 'Comment se protéger d\'un associé majoritaire ?', 'a' => 'Pacte d\'associés avec clauses de minorité (préemption, sortie conjointe, agrément), nomination d\'un directeur général extérieur, demande de désignation d\'un expert (art. 1843-4 Code civil) en cas de conflit.' ),
					array( 'q' => 'Quelle est la durée d\'une procédure prud\'homale ?', 'a' => 'En première instance : 12 à 18 mois selon le ressort. Phase de conciliation préalable obligatoire. Appel possible (12-24 mois supplémentaires). Référé pour les urgences (paiement de salaire) en quelques semaines.' ),
					array( 'q' => 'Le bail commercial peut-il être résilié à tout moment ?', 'a' => 'Non. Le bailleur ne peut résilier qu\'à l\'échéance triennale (3-6-9) avec 6 mois de préavis ou pour motif grave. Le locataire peut donner congé tous les 3 ans. Indemnité d\'éviction due en cas de non-renouvellement.' ),
				),
				'citations' => array(
					array( 'quote' => 'Le contrat est la loi des parties.', 'author' => 'Code civil article 1103', 'bg' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1920&q=85' ),
					array( 'quote' => 'Le commerce est dans la balance le poids qui assure l\'équilibre des nations.', 'author' => 'Voltaire', 'bg' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => "Reprise d'une PME familiale dans des conditions tendues : le cabinet a structuré le pacte d'associés et anticipé chaque clause sensible. Aucun conflit depuis 4 ans.", 'name' => 'Frédéric L.', 'role' => 'Dirigeant — agroalimentaire' ),
					array( 'quote' => 'Cession de mon fonds de commerce gérée de bout en bout. Négociation ferme sur le prix, séquestre sécurisé, formalités impeccables.', 'name' => 'Nadia B.', 'role' => 'Cédante — restauration' ),
					array( 'quote' => "Procédure de sauvegarde déposée à temps grâce à leur réactivité. Plan de continuation accepté par le tribunal. Sans eux, je fermais.", 'name' => 'Jean-Christophe P.', 'role' => 'Gérant — bâtiment' ),
				),
			),
			'droit-du-travail' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Contestation de licenciement économique', 'desc' => 'Analyse de la procédure, contestation devant le CPH, négociation transactionnelle, indemnités majorées si licenciement sans cause réelle.' ),
					array( 'titre' => 'Rupture conventionnelle négociée', 'desc' => 'Calcul de l\'indemnité (légale + supra-légale), rédaction de la convention, homologation DREETS, droits chômage préservés.' ),
					array( 'titre' => 'Harcèlement moral au travail', 'desc' => 'Constitution du dossier (témoignages, mails, certificats médicaux), saisine de l\'Inspection du travail, référé prud\'homal en urgence, faute inexcusable.' ),
					array( 'titre' => 'Heures supplémentaires non payées', 'desc' => 'Reconstitution des temps de travail, mise en demeure, action prud\'homale avec rappel salaires sur 3 ans + congés payés afférents + dommages-intérêts.' ),
					array( 'titre' => 'Clause de non-concurrence abusive', 'desc' => 'Analyse de validité (durée, périmètre, contrepartie financière), négociation de levée, contestation devant le CPH si trop large.' ),
				),
				'faq' => array(
					array( 'q' => 'Quelle indemnité en cas de licenciement sans cause réelle ?', 'a' => 'Barème Macron : entre 3 et 20 mois de salaire selon ancienneté et taille de l\'entreprise. Le juge peut écarter le barème dans les cas de licenciement nul (discrimination, harcèlement). À cela s\'ajoutent l\'indemnité légale et le préavis.' ),
					array( 'q' => 'Combien dure une procédure prud\'homale ?', 'a' => 'Phase de conciliation : 1-3 mois. Audience de jugement : 6-12 mois après. Délibéré : 1-2 mois. Total : 12 à 18 mois en moyenne en première instance. Référé en urgence pour paiements échus en quelques semaines.' ),
					array( 'q' => 'Quels sont les motifs valables de licenciement ?', 'a' => 'Cause réelle et sérieuse : motif personnel (insuffisance professionnelle, faute) ou économique (suppression de poste, mutation technologique, sauvegarde de la compétitivité). Procédure stricte à respecter sous peine de licenciement sans cause.' ),
					array( 'q' => 'Comment prouver le harcèlement moral ?', 'a' => 'Le salarié doit présenter des éléments faisant présumer le harcèlement (mails, témoignages, certificats médicaux, baisse d\'évaluations soudaine). L\'employeur doit alors prouver que ses agissements sont étrangers au harcèlement (renversement de charge de la preuve).' ),
					array( 'q' => 'Une rupture conventionnelle ouvre-t-elle droit au chômage ?', 'a' => 'Oui, contrairement à une démission. C\'est l\'avantage majeur. Pôle Emploi indemnise selon les conditions habituelles (durée, salaire de référence). L\'indemnité de rupture est exonérée d\'impôt et de cotisations dans la limite légale.' ),
				),
				'citations' => array(
					array( 'quote' => 'Le travail éloigne de nous trois grands maux : l\'ennui, le vice et le besoin.', 'author' => 'Voltaire', 'bg' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1920&q=85' ),
					array( 'quote' => 'Le travail n\'est pas une marchandise. La liberté d\'expression et d\'association est essentielle pour soutenir le progrès et la dignité du travailleur.', 'author' => 'Déclaration de Philadelphie, OIT 1944', 'bg' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Licencié sans cause réelle après 11 ans : indemnités max obtenues, plus 6 mois de salaires en transactionnel. Travail de fond impressionnant.', 'name' => 'Karim S.', 'role' => 'Cadre commercial' ),
					array( 'quote' => "Harcèlement moral reconnu en 8 mois. Le cabinet a su monter un dossier béton avec témoignages et certificats. Justice rendue.", 'name' => 'Aurélie M.', 'role' => 'Salariée — secteur banque' ),
					array( 'quote' => 'Rupture conventionnelle négociée à 18 mois de salaire. Le DRH ne s\'attendait pas à cette résistance. Très satisfait.', 'name' => 'Olivier D.', 'role' => 'Ingénieur informatique' ),
				),
			),
			'droit-de-la-famille' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Divorce par consentement mutuel', 'desc' => 'Rédaction de la convention (partage des biens, prestation compensatoire, garde, pension), signature devant notaire, dépôt au minutier — 2 à 4 mois.' ),
					array( 'titre' => 'Divorce contentieux pour faute', 'desc' => 'Saisine du JAF, mesures provisoires (résidence enfants, pension), preuves de la faute (témoignages, constats d\'huissier), prononcé du divorce aux torts exclusifs.' ),
					array( 'titre' => 'Garde alternée vs garde exclusive', 'desc' => 'Évaluation de l\'intérêt de l\'enfant, calcul de la contribution à l\'entretien, saisine du JAF, médiation familiale, audition de l\'enfant si discernement.' ),
					array( 'titre' => 'Pension alimentaire impayée', 'desc' => 'Recouvrement par voie d\'huissier, paiement direct sur salaire, intervention de la CAF (ARIPA), sanctions pénales (abandon de famille).' ),
					array( 'titre' => 'PACS rompu et partage des biens', 'desc' => 'Liquidation du régime, partage des indivisions, contestation des libéralités, droits de chacun selon convention de PACS et indivisions.' ),
				),
				'faq' => array(
					array( 'q' => 'Combien de temps pour un divorce par consentement mutuel ?', 'a' => '2 à 4 mois en moyenne. Étapes : 1ère consultation avec chacun, rédaction de la convention, délai de réflexion de 15 jours obligatoire après envoi du projet, signature de la convention, dépôt au notaire (formalités d\'enregistrement).' ),
					array( 'q' => 'Quelle est la pension alimentaire moyenne pour un enfant ?', 'a' => 'Calcul selon barème indicatif du Ministère de la Justice : varie de 100 à 600 €/mois par enfant selon revenus du débiteur et mode de garde. Réévaluation annuelle indexée sur l\'INSEE. Le juge tient compte des charges de chacun.' ),
					array( 'q' => 'Garde alternée : conditions ?', 'a' => 'Accord des deux parents OU décision du juge si l\'intérêt de l\'enfant le commande. Conditions : domiciles proches (même secteur scolaire), bonne entente parentale minimale, âge de l\'enfant adapté (souvent 6 ans+), équipement double dans chaque foyer.' ),
					array( 'q' => 'Comment récupérer une pension impayée ?', 'a' => 'Plusieurs voies cumulables : recouvrement public via ARIPA (CAF), saisie sur salaire (paiement direct), saisie-attribution sur compte bancaire, plainte pour abandon de famille (art. 227-3 CP, 2 ans prison).' ),
					array( 'q' => 'Le PACS protège-t-il comme un mariage ?', 'a' => 'Non, protection moindre. Pas de prestation compensatoire ni pension alimentaire à la rupture. Pas d\'héritage automatique sauf testament. Mais avantages fiscaux (impôts communs), couverture santé du partenaire, certaines pensions de réversion possibles.' ),
				),
				'citations' => array(
					array( 'quote' => 'La famille est l\'élément naturel et fondamental de la société.', 'author' => 'Déclaration universelle des droits de l\'homme, art. 16', 'bg' => 'https://images.unsplash.com/photo-1609220136736-443140cffec6?w=1920&q=85' ),
					array( 'quote' => 'Dans l\'intérêt supérieur de l\'enfant, en toutes circonstances.', 'author' => 'Convention internationale des droits de l\'enfant', 'bg' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Divorce difficile avec enfants : le cabinet a su préserver les relations parentales tout en obtenant une garde équilibrée. Approche humaine.', 'name' => 'Stéphanie L.', 'role' => 'Cliente — divorce' ),
					array( 'quote' => "Pension impayée pendant 2 ans, 8000 € recouvrés via saisie-attribution. Procédure rapide et efficace.", 'name' => 'Mounir K.', 'role' => 'Père — recouvrement pension' ),
					array( 'quote' => 'Médiation familiale réussie après 6 mois de blocage. Plus économique et moins traumatisant qu\'un contentieux. Merci.', 'name' => 'Catherine V.', 'role' => 'Cliente — médiation' ),
				),
			),
			'droit-immobilier' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Vice caché à l\'achat', 'desc' => 'Expertise judiciaire, action rédhibitoire (annulation) ou estimatoire (réduction du prix), recours contre le vendeur et le notaire si manquement au devoir de conseil.' ),
					array( 'titre' => 'Litige de copropriété', 'desc' => 'Contestation d\'AG (délais, vote), recouvrement de charges impayées, action contre le syndic, travaux votés non réalisés, troubles de jouissance.' ),
					array( 'titre' => 'Bail commercial — déspécialisation', 'desc' => 'Demande de changement d\'activité (déspécialisation simple ou plénière), procédure devant le bailleur, décision du tribunal en cas de refus abusif.' ),
					array( 'titre' => 'Expulsion de locataire indélicat', 'desc' => 'Commandement de payer, assignation devant le juge des contentieux de la protection, jugement d\'expulsion, concours de la force publique.' ),
					array( 'titre' => 'Permis de construire contesté', 'desc' => 'Recours gracieux + recours en annulation devant le TA, suspension des travaux en référé, négociation amiable avec les voisins.' ),
				),
				'faq' => array(
					array( 'q' => 'Délai pour agir en vice caché ?', 'a' => '2 ans à compter de la découverte du vice (art. 1648 Code civil). Le vice doit être caché au moment de la vente, antérieur à celle-ci, et rendre la chose impropre à sa destination ou en diminuer fortement l\'usage.' ),
					array( 'q' => 'Comment contester une décision d\'AG de copropriété ?', 'a' => '2 mois à compter de la notification du PV pour agir en annulation devant le TGI. Motifs : irrégularités de convocation, abus de majorité, atteinte aux droits du copropriétaire opposant ou défaillant.' ),
					array( 'q' => 'Le propriétaire peut-il refuser un changement de locataire ?', 'a' => 'En location nue habitation : le bailleur peut refuser pour motifs sérieux (insolvabilité du candidat). En bail commercial : agrément du bailleur souvent requis pour la cession, refus possible mais doit être justifié.' ),
					array( 'q' => 'Combien de temps pour expulser un locataire ?', 'a' => 'Procédure complète : 6 à 18 mois selon les juridictions et la trêve hivernale (1er nov - 31 mars où l\'expulsion est suspendue). Étapes : commandement de payer, assignation, jugement, signification, concours de la force publique.' ),
					array( 'q' => 'Mon voisin construit illégalement, que faire ?', 'a' => 'Recours en annulation contre le permis devant le TA dans les 2 mois de l\'affichage (ou 6 mois en l\'absence d\'affichage). Référé suspension en urgence. Action civile pour trouble anormal du voisinage en parallèle.' ),
				),
				'citations' => array(
					array( 'quote' => 'La propriété est un droit inviolable et sacré.', 'author' => 'Déclaration des droits de l\'homme, article 17', 'bg' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1920&q=85' ),
					array( 'quote' => 'Charbonnier est maître chez soi.', 'author' => 'Adage du droit immobilier', 'bg' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => "Vice caché révélé un an après l'achat : 47 000 € obtenus en réduction de prix. Le notaire et le vendeur ont été condamnés solidairement.", 'name' => 'Patrick H.', 'role' => 'Acquéreur — maison ancienne' ),
					array( 'quote' => "Locataire impayée depuis 9 mois, expulsion obtenue malgré la trêve hivernale. Procédure carrée, sans faute.", 'name' => 'Hélène D.', 'role' => 'Bailleuse — appartement Paris' ),
					array( 'quote' => 'Permis de construire du voisin annulé devant le TA. Travaux illégaux stoppés, aucune indemnité due. Soulagés.', 'name' => 'Thierry et Anne G.', 'role' => 'Riverains — recours TA' ),
				),
			),
			'droit-penal' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Garde à vue 24h/24', 'desc' => 'Assistance immédiate dans les 24h de l\'arrivée au commissariat, droits du gardé à vue, présence aux interrogatoires, conseils stratégie de défense.' ),
					array( 'titre' => 'Comparution immédiate', 'desc' => 'Préparation express du dossier, demande de renvoi pour préparer la défense, plaidoirie sur les faits et la personnalité du prévenu, peines alternatives.' ),
					array( 'titre' => 'Défense d\'assises', 'desc' => 'Suivi de l\'instruction (réquisitoire, ordonnance de renvoi), constitution du dossier de personnalité, plaidoirie devant la cour d\'assises, recours en cassation.' ),
					array( 'titre' => 'Violences conjugales', 'desc' => 'Dépôt de plainte, ordonnance de protection en urgence (6 jours), poursuites pénales, demande de dommages-intérêts en partie civile.' ),
					array( 'titre' => 'Stupéfiants — usage simple', 'desc' => 'Amende forfaitaire ou poursuites, plaidoirie de relaxe ou peines alternatives (TIG, sursis), accompagnement thérapeutique pour éviter la prison.' ),
				),
				'faq' => array(
					array( 'q' => 'Quels sont mes droits en garde à vue ?', 'a' => 'Droit à l\'assistance d\'un avocat dès la 1ère minute, à un médecin, à un proche prévenu, au silence (sans pénalisation). Durée : 24h, prolongeable 24h sur autorisation du procureur. 96h maximum en matière de stupéfiants/terrorisme.' ),
					array( 'q' => 'Que faire si je suis convoqué par la police ?', 'a' => 'Contactez immédiatement un avocat avant l\'audition. Il pourra vous accompagner si c\'est une audition libre, vous préparer aux questions, vous protéger contre l\'auto-incrimination. Vous pouvez refuser de répondre sans avocat.' ),
					array( 'q' => 'Combien coûte un avocat pénaliste ?', 'a' => 'Tarif horaire : 200 à 450 €/h selon notoriété. Forfaits possibles : garde à vue (300-800 €), comparution immédiate (1500-3000 €), assises (5000-15000 €). Aide juridictionnelle si revenus inférieurs aux plafonds (env. 1100 €/mois célibataire).' ),
					array( 'q' => 'Combien de temps pour un procès pénal ?', 'a' => 'Comparution immédiate : audience le jour même ou sous 3 mois. Tribunal correctionnel classique : 8-18 mois. Cour d\'assises : 18-36 mois après instruction. Appel possible. Cassation pour vices de procédure ou erreur de droit.' ),
					array( 'q' => 'Le casier judiciaire s\'efface-t-il ?', 'a' => 'Effacement automatique selon la peine : amendes (3 ans), peines emprisonnement avec sursis (5 ans), peines fermes (10 ans). Effacement anticipé possible sur demande motivée au procureur. Bulletin n°2 (employeur) et n°3 (intéressé) ont des règles différentes.' ),
				),
				'citations' => array(
					array( 'quote' => 'Tout homme étant présumé innocent jusqu\'à ce qu\'il ait été déclaré coupable.', 'author' => 'Déclaration des droits de l\'homme, article 9', 'bg' => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1920&q=85' ),
					array( 'quote' => 'Mieux vaut un coupable libre qu\'un innocent en prison.', 'author' => 'Maxime du droit pénal', 'bg' => 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Garde à vue à 4h du matin : disponibilité totale, présent en moins d\'une heure. Plainte classée sans suite, je dormais chez moi le lendemain.', 'name' => 'Yann R.', 'role' => 'Mis en cause — GAV' ),
					array( 'quote' => "Procès aux assises pour mon fils, peine divisée par deux par rapport au réquisitoire. Plaidoirie magistrale, reconnaissance du juge.", 'name' => 'Fatima A.', 'role' => 'Mère — assises' ),
					array( 'quote' => "Affaire de stupéfiants, requalification obtenue en simple usage. Casier vierge préservé, je peux exercer mon métier.", 'name' => 'Bastien C.', 'role' => 'Mis en cause — correctionnel' ),
				),
			),
			'droit-fiscal' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Contrôle fiscal personnel', 'desc' => 'Assistance pendant la vérification, négociation des rectifications, recours hiérarchique, saisine du conciliateur fiscal, recours contentieux devant le tribunal administratif.' ),
					array( 'titre' => 'Contentieux URSSAF', 'desc' => 'Contestation de redressement de cotisations sociales, négociation de plan d\'apurement, saisine de la Commission de recours amiable puis du tribunal judiciaire.' ),
					array( 'titre' => 'Montage de holding patrimoniale', 'desc' => 'Optimisation du démembrement, pacte Dutreil pour transmission d\'entreprise, intégration fiscale, choix du régime mère-filles.' ),
					array( 'titre' => 'IFI (impôt sur la fortune immobilière)', 'desc' => 'Optimisation des décotes (résidence principale, démembrement), évaluation des biens, contestation de l\'évaluation administrative.' ),
					array( 'titre' => 'Régularisation d\'avoirs à l\'étranger', 'desc' => 'Procédure de régularisation spontanée, calcul des pénalités, ETNC, accompagnement vis-à-vis du STDR (devenu STG).' ),
				),
				'faq' => array(
					array( 'q' => 'Combien de temps dure un contrôle fiscal ?', 'a' => 'Vérification de comptabilité : 3 mois maximum sur place pour les PME (art. L52 LPF), prorogeable. La procédure complète (de l\'avis à la mise en recouvrement) peut durer 12 à 24 mois avec les recours.' ),
					array( 'q' => 'Quelles sont les pénalités fiscales ?', 'a' => 'Intérêts de retard : 0,20%/mois (2,4%/an). Majorations : 10% (insuffisance), 40% (manquement délibéré), 80% (manœuvres frauduleuses, abus de droit), 100% (opposition à contrôle).' ),
					array( 'q' => 'Comment contester un redressement fiscal ?', 'a' => '30 jours pour répondre à la proposition de rectification. Recours hiérarchique auprès du supérieur du vérificateur, puis interlocuteur départemental. Saisine de la commission départementale, puis tribunal administratif (impôts directs) ou tribunal judiciaire (droits d\'enregistrement).' ),
					array( 'q' => 'Le pacte Dutreil, c\'est quoi ?', 'a' => 'Engagement collectif de conservation des titres d\'une société pendant 2 ans + engagement individuel de 4 ans, qui permet une exonération de 75% sur la transmission d\'entreprise. Réduit considérablement les droits de succession ou de donation.' ),
					array( 'q' => 'Faut-il déclarer ses comptes à l\'étranger ?', 'a' => 'Oui, obligation de déclarer tout compte ouvert/utilisé/clos hors de France (formulaire 3916). Sanction en cas de non-déclaration : 1500 € par compte (ou 10000 € pour ETNC), plus prescription étendue à 10 ans pour le contrôle.' ),
				),
				'citations' => array(
					array( 'quote' => 'L\'art de l\'imposition consiste à plumer l\'oie pour obtenir le maximum de plumes avec le minimum de cris.', 'author' => 'Jean-Baptiste Colbert', 'bg' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1920&q=85' ),
					array( 'quote' => 'Nul ne peut être contraint à payer un impôt qui n\'aurait pas été légalement consenti.', 'author' => 'Déclaration des droits de l\'homme, article 14', 'bg' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Contrôle fiscal très intrusif sur 3 exercices. Redressement initial 180 K€, ramené à 24 K€ après recours hiérarchique. Différence énorme.', 'name' => 'Vincent T.', 'role' => 'Dirigeant — SAS conseil' ),
					array( 'quote' => 'Optimisation fiscale de la transmission de mon patrimoine via SCI familiale et démembrement. Économie estimée : 220 K€ sur la succession.', 'name' => 'Marie-Hélène F.', 'role' => 'Cliente — patrimoine immobilier' ),
					array( 'quote' => 'Régularisation suite à comptes étrangers non déclarés. Pénalités divisées par 4 grâce à leur connaissance des dispositifs.', 'name' => 'Anonyme', 'role' => 'Régularisation fiscale' ),
				),
			),
			'droit-international' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Contrat de distribution international', 'desc' => 'Choix de la loi applicable, clauses d\'exclusivité territoriale, juridiction compétente, modalités de résiliation, protection contre la rupture brutale.' ),
					array( 'titre' => 'Arbitrage CCI (Chambre de Commerce Internationale)', 'desc' => 'Constitution du tribunal arbitral, mémoires écrits, audience, sentence arbitrale, exequatur dans les pays de la convention de New York.' ),
					array( 'titre' => 'Détachement / expatriation', 'desc' => 'Choix du régime social (formulaire A1, certificat de couverture), fiscalité internationale, clauses du contrat (rapatriement, scolarité, logement).' ),
					array( 'titre' => 'Reconnaissance de jugement étranger', 'desc' => 'Procédure d\'exequatur en France, vérification des règles d\'ordre public, signification, recours en cas de refus.' ),
					array( 'titre' => 'Litige commercial transfrontalier', 'desc' => 'Stratégie procédurale (arbitrage vs juridiction étatique), négociation amiable, mise en jeu de garanties bancaires internationales (SWIFT, lettre de crédit).' ),
				),
				'faq' => array(
					array( 'q' => 'Quelle loi applicable à un contrat international ?', 'a' => 'Liberté de choix des parties (Règlement Rome I dans l\'UE). À défaut, la loi du pays de la résidence habituelle de la partie qui doit fournir la prestation caractéristique. Pour la vente : loi du vendeur ; pour les services : loi du prestataire.' ),
					array( 'q' => 'Quel tribunal en cas de litige international ?', 'a' => 'Clause attributive de juridiction respectée si valide (écrite, prévisible). À défaut, dans l\'UE : tribunal du domicile du défendeur (Bruxelles I bis). Hors UE : règles du droit international privé du pays saisi.' ),
					array( 'q' => 'L\'arbitrage international est-il obligatoire ?', 'a' => 'Non, il faut une clause compromissoire dans le contrat ou un compromis après le litige. La sentence arbitrale est exécutoire dans 170+ pays signataires de la Convention de New York de 1958.' ),
					array( 'q' => 'Comment se faire payer à l\'international ?', 'a' => 'Garanties usuelles : crédit documentaire (lettre de crédit), Stand-by Letter of Credit, garantie bancaire à première demande. Saisie sur biens à l\'étranger possible mais lourde (exequatur préalable).' ),
					array( 'q' => 'Quelle protection en cas d\'expatriation ?', 'a' => 'Convention de sécurité sociale ou Règlement européen 883/2004 (formulaire A1) pour rester affilié au régime français. Convention fiscale bilatérale pour éviter la double imposition. Clauses contractuelles : rapatriement, scolarité enfants, logement.' ),
				),
				'citations' => array(
					array( 'quote' => 'Le commerce guérit des préjugés destructeurs : c\'est presque une règle générale, que partout où il y a des mœurs douces, il y a du commerce.', 'author' => 'Montesquieu', 'bg' => 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=1920&q=85' ),
					array( 'quote' => 'Pacta sunt servanda — les conventions doivent être respectées.', 'author' => 'Adage du droit international', 'bg' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Litige avec un fournisseur turc, arbitrage CCI à Paris. Sentence favorable rendue en 14 mois, 320 K€ recouvrés.', 'name' => 'Société Lemoine SA', 'role' => 'Industrie — export' ),
					array( 'quote' => 'Expatriation au Vietnam pour 4 ans : convention fiscale, contrat local, rapatriement, scolarité enfants — tout négocié dans mes intérêts.', 'name' => 'Nicolas B.', 'role' => 'Expatrié — direction' ),
					array( 'quote' => 'Procédure de visa long séjour pour ma compagne brésilienne. Refus initial transformé en accord en appel. Réactivité remarquable.', 'name' => 'Mathieu E.', 'role' => 'Conjoint — visa' ),
				),
			),
			'droit-des-successions' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Liquidation de succession amiable', 'desc' => 'Inventaire des biens, déclaration de succession, calcul des droits, partage entre héritiers, paiement des droits dans les 6 mois (12 hors France).' ),
					array( 'titre' => 'Contestation de testament', 'desc' => 'Action en nullité (insanité d\'esprit, vice du consentement), réduction pour atteinte à la réserve, action en pétition d\'hérédité.' ),
					array( 'titre' => 'Donation-partage', 'desc' => 'Anticipation de la transmission, choix des biens donnés à chaque enfant, abattement fiscal (100k€/parent/enfant tous les 15 ans), pacte Dutreil pour entreprise.' ),
					array( 'titre' => 'Indivision successorale conflictuelle', 'desc' => 'Demande de partage judiciaire, vente aux enchères des biens, attribution préférentielle (résidence principale, exploitation).' ),
					array( 'titre' => 'Recel successoral', 'desc' => 'Action en révélation contre un héritier qui dissimule un bien (bijoux, comptes), sanction : privation de la part sur le bien recelé + intérêts.' ),
				),
				'faq' => array(
					array( 'q' => 'Combien de temps pour régler une succession ?', 'a' => '6 mois pour déposer la déclaration de succession (12 mois si décès hors France). Règlement complet souvent 12-24 mois selon complexité (immobilier, biens à l\'étranger, contestations). Indivision peut durer indéfiniment.' ),
					array( 'q' => 'Quels sont les droits de succession en France ?', 'a' => 'Abattement de 100 000 € par enfant (sur 15 ans), puis barème progressif de 5 à 45%. Conjoint exonéré totalement. Frères/sœurs : abattement 15 932 € puis 35-45%. Tiers : 60% au-delà de 1594 €.' ),
					array( 'q' => 'Peut-on déshériter ses enfants ?', 'a' => 'Non en France, les enfants sont héritiers réservataires. Réserve : 1/2 de la succession pour 1 enfant, 2/3 pour 2 enfants, 3/4 pour 3+. La quotité disponible peut être attribuée librement (testament, donation).' ),
					array( 'q' => 'Comment optimiser sa succession ?', 'a' => 'Donations échelonnées (abattement renouvelé tous les 15 ans), démembrement de propriété (donation de la nue-propriété), assurance-vie (152 500 € exonéré par bénéficiaire si versé avant 70 ans), pacte Dutreil pour entreprise.' ),
					array( 'q' => 'Que faire si un héritier refuse le partage ?', 'a' => 'Demande de partage judiciaire devant le TJ. Le notaire dresse un état liquidatif, le juge tranche les désaccords. Procédure longue (12-36 mois). Possibilité d\'attribution préférentielle au profit de celui qui occupe le logement.' ),
				),
				'citations' => array(
					array( 'quote' => 'La mort n\'est pas la fin du voyage : c\'est seulement le commencement d\'un autre.', 'author' => 'Maxime du droit des successions', 'bg' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=85' ),
					array( 'quote' => 'Le mort saisit le vif.', 'author' => 'Article 724 du Code civil', 'bg' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => "Conflit successoral entre 4 enfants, un demi-frère contestataire. Médiation puis partage judiciaire : tout le monde a obtenu sa part équitable.", 'name' => 'Annick et famille', 'role' => 'Héritiers — succession 1,2 M€' ),
					array( 'quote' => "Donation-partage anticipée pour mes 3 enfants. Économie de 60 K€ de droits de succession et zéro conflit prévisible.", 'name' => 'Bernard L.', 'role' => 'Donateur — patrimoine' ),
					array( 'quote' => 'Renonciation à succession très endettée traitée dans les 4 mois. Aucune dette héritée, dossier propre.', 'name' => 'Élodie M.', 'role' => 'Héritière — renonciation' ),
				),
			),
			'droit-du-numerique' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Mise en conformité RGPD', 'desc' => 'Audit du traitement des données, registre des activités, mentions d\'information, charte cookies, contrats sous-traitants (DPA), DPIA si nécessaire.' ),
					array( 'titre' => 'Contrat SaaS B2B', 'desc' => 'Rédaction des CGU/CGV, SLA (engagement de service), licence d\'utilisation, propriété intellectuelle, clause de réversibilité, conditions de résiliation.' ),
					array( 'titre' => 'Violation de données personnelles', 'desc' => 'Notification CNIL dans les 72h, communication aux personnes concernées, plan d\'action correctif, gestion de la communication de crise, défense en cas de plainte CNIL.' ),
					array( 'titre' => 'Cyber-attaque (ransomware, phishing)', 'desc' => 'Plainte au commissariat (préservation des preuves), notification CNIL, coordination avec l\'ANSSI, négociation/non-paiement de rançon, action contre les auteurs.' ),
					array( 'titre' => 'Litige e-commerce', 'desc' => 'Contestation d\'une plateforme (avis frauduleux, déréférencement), action en concurrence déloyale, obligations de la plateforme (DSA), responsabilité hébergeur vs éditeur.' ),
				),
				'faq' => array(
					array( 'q' => 'Quelles sont les obligations RGPD principales ?', 'a' => 'Registre des traitements, base légale identifiée, mentions d\'information, consentement éclairé pour cookies, sécurité technique appropriée, notification de violation 72h, droits des personnes (accès, rectification, effacement, portabilité, opposition).' ),
					array( 'q' => 'Quelles sanctions en cas de non-conformité RGPD ?', 'a' => 'Amende administrative jusqu\'à 20 millions € ou 4% du chiffre d\'affaires mondial annuel (le plus élevé). Sanctions pénales jusqu\'à 5 ans de prison + 300 000 € d\'amende (art. 226-16 et suivants Code pénal).' ),
					array( 'q' => 'Faut-il un DPO (délégué à la protection des données) ?', 'a' => 'Obligatoire pour : autorités/organismes publics, traitement à grande échelle de données sensibles, suivi systématique à grande échelle. Recommandé pour les autres. Peut être interne ou externe (DPO mutualisé).' ),
					array( 'q' => 'Comment payer un ransomware en sécurité ?', 'a' => 'Recommandation officielle : NE PAS PAYER. Le paiement n\'est plus assurable depuis 2023 sans plainte préalable. Plainte au commissariat obligatoire (Loi LOPMI). Notification ANSSI si OIV/OSE. Privilégier la sauvegarde et la reconstruction.' ),
					array( 'q' => 'Mes mentions d\'information sur le site sont-elles conformes ?', 'a' => 'Doivent contenir : identité du responsable, finalités, base légale, destinataires, durée de conservation, droits des personnes, DPO le cas échéant, droit de plainte CNIL, transferts hors UE. Audit recommandé tous les 12-24 mois.' ),
				),
				'citations' => array(
					array( 'quote' => 'La protection des données personnelles est un droit fondamental.', 'author' => 'Charte des droits fondamentaux de l\'UE, article 8', 'bg' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1920&q=85' ),
					array( 'quote' => 'La technologie change vite. Le droit doit s\'adapter sans renoncer à ses principes.', 'author' => 'Adage du droit du numérique', 'bg' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Atteinte à la e-réputation : faux avis et propos diffamants supprimés par référé Google + condamnation des auteurs identifiés.', 'name' => 'Restaurant L\'Oasis', 'role' => 'Restaurateur — e-réputation' ),
					array( 'quote' => 'Audit RGPD complet de notre SaaS, mise en conformité documentaire et registre des traitements. Plus de stress avant un client grand compte.', 'name' => 'Adrien V.', 'role' => 'CTO — startup B2B' ),
					array( 'quote' => "Vol de propriété intellectuelle par un ex-prestataire. Saisie-contrefaçon obtenue, code récupéré, dommages-intérêts à 5 chiffres.", 'name' => 'Studio NovaCode', 'role' => 'Éditeur logiciel' ),
				),
			),
			'droit-bancaire' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Prêt immobilier : taux d\'usure dépassé', 'desc' => 'Vérification du TAEG, action en déchéance d\'intérêts, restitution des intérêts indûment perçus, négociation de renégociation.' ),
					array( 'titre' => 'Fraude bancaire en ligne', 'desc' => 'Opposition immédiate, dépôt de plainte, mise en cause de la banque pour défaut d\'authentification forte (DSP2), demande de remboursement intégral, recours médiateur bancaire.' ),
					array( 'titre' => 'Mise en jeu de caution', 'desc' => 'Vérification de la régularité de la caution (mention manuscrite, proportionnalité), action en nullité, contestation du quantum, étalement des paiements.' ),
					array( 'titre' => 'Clôture abusive de compte', 'desc' => 'Recours pour rupture brutale, défaut de motivation (clients vulnérables protégés), saisine du médiateur, action en responsabilité bancaire.' ),
					array( 'titre' => 'Surendettement personnel', 'desc' => 'Dossier de surendettement à la Banque de France, plan conventionnel ou rétablissement personnel, suspension des poursuites, effacement partiel des dettes.' ),
				),
				'faq' => array(
					array( 'q' => 'Comment contester un prélèvement non autorisé ?', 'a' => 'Demande de remboursement à la banque dans les 13 mois (8 semaines pour SEPA autorisé). La banque doit rembourser sans délai sauf fraude/négligence grave du client. Recours médiateur bancaire si refus, puis tribunal.' ),
					array( 'q' => 'Le taux d\'usure, c\'est quoi ?', 'a' => 'Plafond légal du taux d\'intérêt fixé trimestriellement par la Banque de France selon le type de crédit. Dépasser le taux d\'usure entraîne déchéance des intérêts (art. L341-50 Code consommation) et peut constituer un délit.' ),
					array( 'q' => 'Une caution est-elle toujours valide ?', 'a' => 'Non. Conditions de validité : mention manuscrite spécifique (art. L341-2 et L341-3 anc.), information annuelle de la caution, proportionnalité aux revenus de la caution. Caution disproportionnée = nullité possible.' ),
					array( 'q' => 'Que faire en cas de surendettement ?', 'a' => 'Déposer un dossier à la Banque de France (commission de surendettement). Si recevable : suspension des poursuites pendant l\'instruction, élaboration d\'un plan conventionnel. Si insolvabilité totale : procédure de rétablissement personnel (effacement des dettes).' ),
					array( 'q' => 'La banque peut-elle refuser un crédit sans motif ?', 'a' => 'Oui, liberté contractuelle de la banque. Sauf : refus discriminatoire (art. 225-1 CP), refus du droit au compte (saisine BDF possible). Le refus doit être motivé pour les crédits aux PME (loi Pacte).' ),
				),
				'citations' => array(
					array( 'quote' => 'L\'argent ne fait pas le bonheur, mais il y contribue.', 'author' => 'Voltaire', 'bg' => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?w=1920&q=85' ),
					array( 'quote' => 'Le crédit est l\'âme du commerce.', 'author' => 'Adage du droit bancaire', 'bg' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Caution personnelle de 95 K€ contestée et annulée pour défaut de mise en garde de la banque. Dette éteinte, je respire.', 'name' => 'Pascal B.', 'role' => 'Caution — prêt pro contesté' ),
					array( 'quote' => 'Frais bancaires excessifs sur 6 ans : 11 200 € remboursés par négociation amiable, sans procès.', 'name' => 'Geneviève R.', 'role' => 'Cliente — recouvrement frais' ),
					array( 'quote' => "Surendettement : plan de redressement obtenu en commission, gel des poursuites, échéancier supportable. Je reconstruis pas à pas.", 'name' => 'Anonyme', 'role' => 'Procédure de surendettement' ),
				),
			),
			'droit-de-la-securite-sociale' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Refus de prise en charge AT/MP', 'desc' => 'Contestation devant la commission de recours amiable (CRA) puis le tribunal judiciaire pôle social. Reconnaissance de l\'accident du travail et indemnisation rétroactive.' ),
					array( 'titre' => 'Reconnaissance de la faute inexcusable', 'desc' => 'Action contre l\'employeur après accident grave. Majoration de la rente et indemnisation des préjudices personnels (souffrance, esthétique, agrément).' ),
					array( 'titre' => 'Recours contre une décision MDPH', 'desc' => 'Contestation du taux d\'incapacité, de la prestation refusée (PCH, AAH, AEEH), recours devant le tribunal judiciaire pôle social.' ),
					array( 'titre' => 'Pension d\'invalidité refusée par la CPAM', 'desc' => 'Recours amiable, expertise médicale contradictoire, action judiciaire. Réévaluation du taux et catégorie d\'invalidité.' ),
					array( 'titre' => 'Indu URSSAF contesté', 'desc' => 'Analyse du redressement, opposition à contrainte, recours devant le tribunal. Annulation totale ou réduction substantielle de la dette.' ),
				),
				'faq' => array(
					array( 'q' => 'Quel délai pour contester une décision de la CPAM ?', 'a' => '2 mois à compter de la notification pour saisir la commission de recours amiable (CRA). Puis 2 mois pour saisir le tribunal judiciaire pôle social après la décision de la CRA (ou rejet implicite après 2 mois de silence).' ),
					array( 'q' => 'Comment obtenir la reconnaissance de la faute inexcusable de l\'employeur ?', 'a' => 'Prouver que l\'employeur avait conscience du danger et n\'a pas pris les mesures pour préserver le salarié. Action devant le pôle social du TJ. Conséquences : majoration de la rente AT et indemnisation complémentaire des préjudices.' ),
					array( 'q' => 'Quels recours contre un refus d\'AAH ?', 'a' => 'Recours administratif préalable obligatoire (RAPO) devant la MDPH dans les 2 mois. Si rejet : tribunal judiciaire pôle social. Possibilité de demander une nouvelle expertise médicale contradictoire.' ),
					array( 'q' => 'Que faire si l\'URSSAF réclame un indu de plusieurs années ?', 'a' => 'Vérifier la prescription (3 ans en principe, 5 ans en cas de fraude présumée). Contester la base de calcul, demander des justificatifs. Opposition à contrainte dans les 15 jours. Procédure devant le pôle social.' ),
					array( 'q' => 'Mon arrêt maladie est-il indemnisé en totalité ?', 'a' => 'Indemnités journalières CPAM (50% du salaire de base, plafonné) + complément employeur si convention collective le prévoit. Délai de carence de 3 jours en maladie (sauf reprise dans les 48h). Pas de carence en AT/MP.' ),
				),
				'citations' => array(
					array( 'quote' => 'La sécurité sociale est une garantie donnée à chacun qu\'en toutes circonstances il disposera des moyens nécessaires pour assurer sa subsistance.', 'author' => 'Pierre Laroque, fondateur de la Sécurité sociale', 'bg' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1920&q=85' ),
					array( 'quote' => 'La Nation assure à l\'individu et à la famille les conditions nécessaires à leur développement.', 'author' => 'Préambule de la Constitution de 1946', 'bg' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => "Accident de trajet refusé par la CPAM, reconnu après 9 mois de procédure. Rente versée rétroactivement, soulagement total.", 'name' => 'Mickaël T.', 'role' => 'Salarié — accident de trajet' ),
					array( 'quote' => "Faute inexcusable obtenue après accident sur chantier. Indemnités majorées et préjudices personnels reconnus à hauteur de 85 K€.", 'name' => 'Pierre A.', 'role' => 'Ouvrier BTP' ),
					array( 'quote' => "Taux d'incapacité MDPH passé de 50% à 80% après recours. AAH revalorisée et carte mobilité inclusion obtenue.", 'name' => 'Linda V.', 'role' => 'Bénéficiaire AAH' ),
				),
			),
			'droit-de-la-consommation' => array(
				'cas_concrets' => array(
					array( 'titre' => 'Démarchage abusif et clauses abusives', 'desc' => 'Annulation du contrat dans le délai de rétractation (14 jours), action en nullité pour clauses abusives, restitution intégrale des sommes versées.' ),
					array( 'titre' => 'Vice caché sur véhicule d\'occasion', 'desc' => 'Expertise contradictoire, mise en demeure du vendeur, action rédhibitoire ou estimatoire. Récupération du prix ou réduction.' ),
					array( 'titre' => 'Crédit à la consommation contesté', 'desc' => 'Vérification de l\'offre préalable (mentions obligatoires, taux), déchéance du droit aux intérêts pour irrégularités, recalcul de la dette.' ),
					array( 'titre' => 'Garantie commerciale et garantie légale', 'desc' => 'Activation de la garantie légale de conformité (2 ans neuf, 1 an occasion), garantie des vices cachés (2 ans), action contre vendeur ou fabricant.' ),
					array( 'titre' => 'Litige avec un opérateur télécom/banque', 'desc' => 'Médiation préalable obligatoire, saisine du médiateur sectoriel, action devant le juge des contentieux de la protection. Résolution rapide.' ),
				),
				'faq' => array(
					array( 'q' => 'Quel délai de rétractation après un démarchage à domicile ?', 'a' => '14 jours calendaires à compter de la signature du contrat ou de la livraison du bien. Le vendeur doit fournir un formulaire type. À défaut, le délai est porté à 12 mois.' ),
					array( 'q' => 'Comment activer la garantie légale de conformité ?', 'a' => 'Mise en demeure écrite au vendeur (lettre recommandée) sous 2 ans à compter de la livraison (1 an pour l\'occasion depuis 2022). Demande de réparation, remplacement ou remboursement. Présomption d\'antériorité du défaut pendant 24 mois (12 pour occasion).' ),
					array( 'q' => 'Que faire face à un démarchage téléphonique abusif ?', 'a' => 'Inscription gratuite sur Bloctel (liste d\'opposition). Plainte à la DGCCRF en cas de non-respect. Action en cessation et dommages-intérêts si harcèlement caractérisé. Sanction pouvant aller jusqu\'à 75 000 € pour le démarcheur.' ),
					array( 'q' => 'Mon crédit à la consommation peut-il être annulé ?', 'a' => 'Si l\'offre préalable manque de mentions obligatoires (TAEG, durée, coût total), le prêteur peut être déchu du droit aux intérêts. La dette est ramenée au capital, sans intérêts ni frais. Vérification systématique recommandée.' ),
					array( 'q' => 'Comment porter plainte contre une marque ?', 'a' => 'Signalement à la DGCCRF (SignalConso), action individuelle devant le juge des contentieux de la protection, action de groupe possible via une association agréée (UFC, CLCV…) si plusieurs consommateurs lésés.' ),
				),
				'citations' => array(
					array( 'quote' => 'Le consommateur n\'est pas un imbécile : c\'est ta femme.', 'author' => 'David Ogilvy', 'bg' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1920&q=85' ),
					array( 'quote' => 'La protection du consommateur est l\'une des plus belles conquêtes du droit moderne.', 'author' => 'Adage du droit de la consommation', 'bg' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1920&q=85' ),
				),
				'testimonials' => array(
					array( 'quote' => 'Démarchage à domicile abusif annulé, 4 200 € remboursés intégralement. Le vendeur est passé en liste noire DGCCRF.', 'name' => 'Régine P.', 'role' => 'Consommatrice — démarchage' ),
					array( 'quote' => "Voiture d'occasion avec moteur HS dissimulé : véhicule remboursé en totalité, vendeur professionnel condamné à 800 € de dommages-intérêts.", 'name' => 'Hakim L.', 'role' => 'Acheteur — auto' ),
					array( 'quote' => "Crédit conso de 12 K€ déchu de ses intérêts pour défauts de mentions obligatoires. Économie de 3 800 € sur la durée du prêt.", 'name' => 'Carole D.', 'role' => 'Emprunteuse — crédit conso' ),
				),
			),
		);
	}
}
