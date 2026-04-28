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
		add_action( 'ag_brand_fallback', array( $this, 'render_default_logo_svg' ) );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 30 );

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

	/**
	 * True only when tier is exactly Premium (not Business). Used to gate
	 * features that Business already provides via pro-features.php so we
	 * don't double-render.
	 */
	private function is_premium_only_tier() {
		return class_exists( 'AG_Licence_Client' ) && 'premium' === AG_Licence_Client::get_tier();
	}

	public function add_body_class( $classes ) {
		if ( ! $this->is_active() ) {
			return $classes;
		}
		$classes[] = 'ag-premium-active';
		if ( $this->is_premium_only_tier() ) {
			$classes[] = 'ag-premium-only';
		}
		return $classes;
	}

	/**
	 * Render the default Premium SVG logo (balance de justice, dégradé or
	 * + halo) when no custom_logo is uploaded. Premium tier only — Business
	 * keeps using its own render in pro-features.php so we don't double up.
	 *
	 * Fired on the ag_brand_fallback action exposed by header.php.
	 */
	public function render_default_logo_svg() {
		if ( ! $this->is_premium_only_tier() ) {
			return;
		}
		if ( has_custom_logo() ) {
			return;
		}
		?>
		<span class="ag-premium-logo-svg" aria-hidden="true">
			<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="agPremiumLogoGold" x1="0%" y1="0%" x2="0%" y2="100%">
						<stop offset="0%" stop-color="#FFE5A0"/>
						<stop offset="50%" stop-color="#D4B45C"/>
						<stop offset="100%" stop-color="#9A7A2E"/>
					</linearGradient>
					<radialGradient id="agPremiumLogoHalo" cx="50%" cy="50%" r="50%">
						<stop offset="0%" stop-color="#FFE5A0" stop-opacity=".35"/>
						<stop offset="60%" stop-color="#D4B45C" stop-opacity=".12"/>
						<stop offset="100%" stop-color="#D4B45C" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<circle cx="32" cy="32" r="31" fill="url(#agPremiumLogoHalo)"/>
				<circle cx="32" cy="6" r="3.5" fill="url(#agPremiumLogoGold)"/>
				<rect x="30" y="9" width="4" height="42" fill="url(#agPremiumLogoGold)" rx="1"/>
				<rect x="9" y="14" width="46" height="3.5" fill="url(#agPremiumLogoGold)" rx="1.5"/>
				<circle cx="10" cy="15.7" r="3" fill="url(#agPremiumLogoGold)"/>
				<circle cx="54" cy="15.7" r="3" fill="url(#agPremiumLogoGold)"/>
				<rect x="9" y="18" width="2" height="6" fill="url(#agPremiumLogoGold)"/>
				<rect x="53" y="18" width="2" height="6" fill="url(#agPremiumLogoGold)"/>
				<path d="M2 24 L18 24 L10 32 Z" fill="url(#agPremiumLogoGold)"/>
				<path d="M46 24 L62 24 L54 32 Z" fill="url(#agPremiumLogoGold)"/>
				<path d="M22 51 L42 51 L46 58 L18 58 Z" fill="url(#agPremiumLogoGold)"/>
			</svg>
		</span>
		<?php
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
			wp_localize_script( 'ag-premium-avocat-script', 'agPremiumData', $this->get_runtime_data() );
		}
	}

	/**
	 * Pack tous les overrides Customizer pour le JS runtime.
	 */
	private function get_runtime_data() {
		$texts_defaults = $this->get_default_texts();
		$texts          = array();
		foreach ( $texts_defaults as $key => $default ) {
			$texts[ $key ] = (string) get_theme_mod( "ag_premium_text_$key", $default );
		}

		$faq_defaults = $this->get_default_faq();
		$faq          = array();
		foreach ( $faq_defaults as $idx => $qa ) {
			$n          = $idx + 1;
			$question   = (string) get_theme_mod( "ag_premium_faq_q$n", $qa['q'] );
			$answer     = (string) get_theme_mod( "ag_premium_faq_a$n", $qa['a'] );
			if ( '' === trim( $question ) || '' === trim( $answer ) ) {
				continue; // hide an entry if user blanks question or answer
			}
			$faq[] = array( 'q' => $question, 'a' => $answer );
		}

		return array(
			'texts' => $texts,
			'faq'   => $faq,
		);
	}

	/**
	 * Default editable section labels and intro paragraphs.
	 * Keys here are also used as Customizer setting suffixes.
	 */
	private function get_default_texts() {
		return array(
			'domaines_title'   => "Domaines d'expertise",
			'domaines_lead'    => 'Conseil et representation pour particuliers et entreprises dans les principaux domaines du droit.',
			'honoraires_title' => 'Honoraires',
			'honoraires_lead'  => 'Transparence totale sur les tarifs : pas de mauvaise surprise, devis ecrit avant tout engagement.',
			'maitre_tag'       => 'Le Maître',
			'cabinet_title'    => 'Cabinet',
			'rdv_section_title' => 'Prendre rendez-vous',
			'faq_h2'           => 'Comment sont calcules nos honoraires ?',
			'faq_intro'        => "Avant toute intervention, une convention d'honoraires ecrite vous est remise. Elle precise le mode de facturation choisi, le montant ou le taux horaire applicable, ainsi que les eventuels frais annexes.",
		);
	}

	/**
	 * Default FAQ Q/A. The labels here are reused both for Customizer
	 * defaults and as the runtime fallback when the user clears a field.
	 */
	private function get_default_faq() {
		return array(
			array(
				'q' => 'Comment se passe la consultation initiale ?',
				'a' => 'Le premier rendez-vous (45 minutes a 1 heure) sert a analyser votre situation, identifier les enjeux juridiques et vous proposer une strategie. La consultation est facturee a un tarif fixe communique avant le rendez-vous.',
			),
			array(
				'q' => 'Qu’est-ce qu’un forfait ?',
				'a' => 'Un prix fixe convenu a l’avance pour traiter un dossier defini : creation de societe, divorce par consentement mutuel, redaction d’un contrat, etc. Le montant est garanti dans la convention d’honoraires ecrite, aucune surprise.',
			),
			array(
				'q' => 'Comment fonctionne la facturation au temps passe ?',
				'a' => 'Le taux horaire est convenu a l’avance et vous recevez un releve detaille mensuel ou en fin de dossier precisant chaque acte effectue et le temps consacre. Convient aux dossiers dont la duree est difficile a estimer.',
			),
			array(
				'q' => 'Qu’est-ce qu’un honoraire de resultat ?',
				'a' => 'Un complement calcule en pourcentage du gain obtenu, conditionne par la reussite du dossier. Encadre par la deontologie : il s’ajoute toujours a des honoraires de base et est plafonne.',
			),
			array(
				'q' => 'L’aide juridictionnelle est-elle acceptee ?',
				'a' => 'Oui, le cabinet accepte les dossiers eligibles. Selon vos revenus, l’Etat prend en charge tout ou partie des honoraires. Apportez votre avis d’imposition et les justificatifs de revenus du foyer.',
			),
		);
	}

	/**
	 * Map of Premium domain image overrides — keyed by slug. Falls back
	 * to the built-in slug map if no Customizer override is set.
	 */
	private function get_default_domaine_slug_labels() {
		return array(
			'droit-des-affaires'           => 'Droit des affaires',
			'droit-du-travail'             => 'Droit du travail',
			'droit-de-la-famille'          => 'Droit de la famille',
			'droit-immobilier'             => 'Droit immobilier',
			'droit-penal'                  => 'Droit penal',
			'droit-fiscal'                 => 'Droit fiscal',
			'droit-international'          => 'Droit international',
			'droit-de-la-securite-sociale' => 'Droit de la securite sociale',
			'droit-des-successions'        => 'Droit des successions',
			'droit-du-numerique'           => 'Droit du numerique',
			'droit-bancaire'               => 'Droit bancaire',
			'droit-de-la-consommation'     => 'Droit de la consommation',
		);
	}

	/**
	 * Register the Customizer panel + sections + fields. Hooked at
	 * priority 30 so the theme's own customizer is already in place.
	 */
	public function register_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'ag_premium_panel', array(
			'title'    => __( 'AG Premium — Textes & images', 'ag-premium-avocat' ),
			'priority' => 200,
		) );

		// === Section 1: Textes des sections ===
		$wp_customize->add_section( 'ag_premium_textes', array(
			'title' => __( 'Textes des sections', 'ag-premium-avocat' ),
			'panel' => 'ag_premium_panel',
		) );
		$labels = array(
			'domaines_title'    => array( 'Titre — Domaines d\'expertise', 'text' ),
			'domaines_lead'     => array( 'Accroche — Domaines d\'expertise', 'textarea' ),
			'honoraires_title'  => array( 'Titre — Honoraires', 'text' ),
			'honoraires_lead'   => array( 'Accroche — Honoraires', 'textarea' ),
			'maitre_tag'        => array( 'Etiquette — Le Maître', 'text' ),
			'cabinet_title'     => array( 'Titre — Cabinet', 'text' ),
			'rdv_section_title' => array( 'Titre — Prendre rendez-vous', 'text' ),
			'faq_h2'            => array( 'Titre FAQ honoraires', 'text' ),
			'faq_intro'         => array( 'Intro FAQ honoraires', 'textarea' ),
		);
		$defaults = $this->get_default_texts();
		foreach ( $labels as $key => $cfg ) {
			$wp_customize->add_setting( "ag_premium_text_$key", array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'textarea' === $cfg[1] ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( "ag_premium_text_$key", array(
				'label'   => $cfg[0],
				'section' => 'ag_premium_textes',
				'type'    => $cfg[1],
			) );
		}

		// === Section 2: FAQ Honoraires (5 Q/R) ===
		$wp_customize->add_section( 'ag_premium_faq', array(
			'title'       => __( 'FAQ Honoraires (5 Q/R)', 'ag-premium-avocat' ),
			'description' => __( 'Laisser un champ vide pour masquer l\'entree.', 'ag-premium-avocat' ),
			'panel'       => 'ag_premium_panel',
		) );
		$faq_defaults = $this->get_default_faq();
		foreach ( $faq_defaults as $idx => $qa ) {
			$n = $idx + 1;
			$wp_customize->add_setting( "ag_premium_faq_q$n", array(
				'default'           => $qa['q'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( "ag_premium_faq_q$n", array(
				'label'   => "Question $n",
				'section' => 'ag_premium_faq',
				'type'    => 'text',
			) );
			$wp_customize->add_setting( "ag_premium_faq_a$n", array(
				'default'           => $qa['a'],
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( "ag_premium_faq_a$n", array(
				'label'   => "Reponse $n",
				'section' => 'ag_premium_faq',
				'type'    => 'textarea',
			) );
		}

		// === Section 3: Images des domaines (par slug) ===
		$wp_customize->add_section( 'ag_premium_images', array(
			'title'       => __( 'Images des domaines', 'ag-premium-avocat' ),
			'description' => __( 'Une image par domaine. Si vide, l\'image Premium par defaut est utilisee.', 'ag-premium-avocat' ),
			'panel'       => 'ag_premium_panel',
		) );
		$slug_labels = $this->get_default_domaine_slug_labels();
		foreach ( $slug_labels as $slug => $label ) {
			$key = 'ag_premium_img_' . str_replace( '-', '_', $slug );
			$wp_customize->add_setting( $key, array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, array(
				'label'   => $label,
				'section' => 'ag_premium_images',
			) ) );
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
			// 1) Customizer override per slug, 2) curated slug map, 3) pool
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
	 * its items point to those real pages instead of anchors.
	 *
	 * Idempotent — runs every admin_init for an admin user. No DB writes
	 * if everything is already in the correct state. Self-healing : if a
	 * page gets deleted or a menu item gets edited to a hash anchor, the
	 * next admin page load fixes it.
	 *
	 * - Creates any of the 4 dedicated pages that don't exist yet.
	 * - Deletes custom menu items whose URL contains `#` or is empty
	 *   (the typical "anchor" leftovers from manual edits).
	 * - Adds a page-typed menu item for each page that isn't already in
	 *   the primary menu.
	 * - If no menu is assigned to the `primary` location, creates one.
	 */
	public function ensure_pages_and_menu() {
		if ( ! $this->is_active() ) {
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
			return;
		}

		$locations = (array) get_theme_mod( 'nav_menu_locations' );
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

		if ( ! $menu_id ) {
			$menu_id = wp_create_nav_menu( __( 'Menu principal', 'ag-premium-avocat' ) );
			if ( is_wp_error( $menu_id ) ) {
				return;
			}
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		$items             = wp_get_nav_menu_items( $menu_id );
		$existing_page_ids = array();
		if ( $items ) {
			foreach ( $items as $item ) {
				// Delete any custom menu item that points to an anchor
				// (`#xxx`) or has no real URL — these are leftovers from
				// the Free fallback or manual edits.
				if ( 'custom' === $item->type ) {
					$url = trim( (string) $item->url );
					if ( '' === $url || '#' === $url || false !== strpos( $url, '#' ) ) {
						wp_delete_post( $item->ID, true );
						continue;
					}
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
	}
}
