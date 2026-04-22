<?php
/**
 * Plugin Name:       AG Starter Companion
 * Plugin URI:        https://alliancegroupe-inc.com/templates-wordpress
 * Description:       Importer un clic pour les themes AG Starter (Restaurant, Artisan, Coach, Avocat). Cree automatiquement les pages, le menu et les reglages pour un site pret a l'emploi.
 * Version:           1.4.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            AGthèmes
 * Author URI:        https://alliancegroupe-inc.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ag-starter-companion
 * Domain Path:       /languages
 *
 * @package AG_Starter_Companion
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_STARTER_COMPANION_VERSION', '1.4.0' );
define( 'AG_STARTER_COMPANION_FILE', __FILE__ );

/**
 * Main plugin class.
 */
class AG_Starter_Companion {

	/**
	 * Constructor : wire WordPress hooks.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		add_action( 'admin_init', array( $this, 'maybe_patch_theme' ) );
		add_action( 'admin_notices', array( $this, 'upgrade_banner' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'upgrade_dashboard_widget' ) );
		add_action( 'customize_register', array( $this, 'upgrade_customizer_section' ), 99 );
		add_action( 'admin_footer', array( $this, 'upgrade_footer_nudge' ) );

		// Self-updater: check for new versions of this plugin
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_self_update' ) );
		add_filter( 'plugins_api', array( $this, 'self_update_info' ), 20, 3 );

		// Enable auto-updates by default for this plugin
		add_filter( 'auto_update_plugin', array( $this, 'enable_auto_update' ), 10, 2 );

		// Admin notice when update is available
		add_action( 'admin_notices', array( $this, 'update_available_notice' ) );
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ag-starter-companion', false, dirname( plugin_basename( AG_STARTER_COMPANION_FILE ) ) . '/languages' );
	}

	/**
	 * Return the slug of the currently active supported theme (parent or stylesheet),
	 * or an empty string if no AG Starter theme is active.
	 *
	 * @return string
	 */
	public function get_active_theme_slug() {
		$theme    = wp_get_theme();
		$template = $theme->get_template();     // parent theme
		$stylesh  = $theme->get_stylesheet();   // child theme if any
		$supported = array( 'ag-starter-restaurant', 'ag-starter-artisan', 'ag-starter-coach', 'ag-starter-avocat', 'ag-starter-barber' );
		foreach ( array( $stylesh, $template ) as $candidate ) {
			if ( in_array( $candidate, $supported, true ) ) {
				return $candidate;
			}
		}
		return '';
	}

	/**
	 * Map of supported themes → setup data (pages, theme name, etc.).
	 *
	 * @return array
	 */
	public function get_theme_data_map() {
		return array(
			'ag-starter-restaurant' => array(
				'name'  => 'AG Starter Restaurant',
				'pages' => array(
					'accueil'     => array(
						'title'   => __( 'Accueil', 'ag-starter-companion' ),
						'content' => '<!-- Rendu par front-page.php -->',
					),
					'carte'       => array(
						'title'   => __( 'Notre carte', 'ag-starter-companion' ),
						'content' => __( 'Presentez vos entrees, plats, desserts, formules du midi et du soir.', 'ag-starter-companion' ),
					),
					'reservation' => array(
						'title'   => __( 'Reservation', 'ag-starter-companion' ),
						'content' => __( 'Reservez votre table en ligne ou contactez-nous au 01 23 45 67 89.', 'ag-starter-companion' ),
					),
					'a-propos'    => array(
						'title'   => __( 'A propos', 'ag-starter-companion' ),
						'content' => __( 'Racontez l\'histoire de votre restaurant, vos valeurs, votre equipe.', 'ag-starter-companion' ),
					),
					'contact'     => array(
						'title'   => __( 'Contact', 'ag-starter-companion' ),
						'content' => __( 'Adresse, horaires, telephone, email et plan d\'acces.', 'ag-starter-companion' ),
					),
				),
			),
			'ag-starter-artisan'    => array(
				'name'  => 'AG Starter Artisan',
				'pages' => array(
					'accueil'      => array(
						'title'   => __( 'Accueil', 'ag-starter-companion' ),
						'content' => '<!-- Rendu par front-page.php -->',
					),
					'prestations'  => array(
						'title'   => __( 'Nos prestations', 'ag-starter-companion' ),
						'content' => __( 'Detaillez vos services : renovation, installation, entretien, depannage.', 'ag-starter-companion' ),
					),
					'realisations' => array(
						'title'   => __( 'Nos realisations', 'ag-starter-companion' ),
						'content' => __( 'Galerie de vos chantiers realises avec photos avant/apres.', 'ag-starter-companion' ),
					),
					'a-propos'     => array(
						'title'   => __( 'A propos', 'ag-starter-companion' ),
						'content' => __( 'Votre entreprise, vos qualifications, votre equipe, vos engagements.', 'ag-starter-companion' ),
					),
					'contact'      => array(
						'title'   => __( 'Contact', 'ag-starter-companion' ),
						'content' => __( 'Adresse, telephone, email, zones d\'intervention et formulaire de devis.', 'ag-starter-companion' ),
					),
				),
			),
			'ag-starter-coach'      => array(
				'name'  => 'AG Starter Coach',
				'pages' => array(
					'accueil'         => array(
						'title'   => __( 'Accueil', 'ag-starter-companion' ),
						'content' => '<!-- Rendu par front-page.php -->',
					),
					'accompagnements' => array(
						'title'   => __( 'Mes accompagnements', 'ag-starter-companion' ),
						'content' => __( 'Coaching individuel, seances de groupe, ateliers, formations.', 'ag-starter-companion' ),
					),
					'temoignages'     => array(
						'title'   => __( 'Temoignages', 'ag-starter-companion' ),
						'content' => __( 'Les retours de vos clients, leurs transformations, leurs resultats.', 'ag-starter-companion' ),
					),
					'a-propos'        => array(
						'title'   => __( 'A propos', 'ag-starter-companion' ),
						'content' => __( 'Votre parcours, vos certifications, votre approche.', 'ag-starter-companion' ),
					),
					'contact'         => array(
						'title'   => __( 'Contact', 'ag-starter-companion' ),
						'content' => __( 'Prise de rendez-vous, cabinet, visio, telephone, email.', 'ag-starter-companion' ),
					),
				),
			),
			'ag-starter-avocat'     => array(
				'name'  => 'AG Starter Avocat',
				'pages' => array(
					'accueil'      => array(
						'title'   => __( 'Accueil', 'ag-starter-companion' ),
						'content' => '<!-- Rendu par front-page.php -->',
					),
					'expertise'    => array(
						'title'   => __( 'Domaines d\'expertise', 'ag-starter-companion' ),
						'content' => __( 'Droit des affaires, droit du travail, droit de la famille, droit immobilier.', 'ag-starter-companion' ),
					),
					'honoraires'   => array(
						'title'   => __( 'Honoraires', 'ag-starter-companion' ),
						'content' => __( 'Premier rendez-vous, forfaits, honoraires au temps passe ou de resultat.', 'ag-starter-companion' ),
					),
					'cabinet'      => array(
						'title'   => __( 'Le cabinet', 'ag-starter-companion' ),
						'content' => __( 'Presentez votre cabinet, votre histoire, vos engagements deontologiques.', 'ag-starter-companion' ),
					),
					'rendez-vous'  => array(
						'title'   => __( 'Prendre rendez-vous', 'ag-starter-companion' ),
						'content' => __( 'Consultation au cabinet ou en visio. Reservation en ligne, telephone, email.', 'ag-starter-companion' ),
					),
				),
			),
			'ag-starter-barber'     => array(
				'name'  => 'AG Starter Barber',
				'pages' => array(
					'accueil'  => array(
						'title'   => __( 'Accueil', 'ag-starter-companion' ),
						'content' => '<!-- Rendu par front-page.php -->',
					),
					'tarifs'   => array(
						'title'   => __( 'Nos tarifs', 'ag-starter-companion' ),
						'content' => __( 'Coupes homme, barbe, degradé, enfant. Tarifs fixes, pas de surprise.', 'ag-starter-companion' ),
					),
					'a-propos' => array(
						'title'   => __( 'Le salon', 'ag-starter-companion' ),
						'content' => __( 'Notre equipe de barbers, notre histoire, nos valeurs.', 'ag-starter-companion' ),
					),
					'contact'  => array(
						'title'   => __( 'Contact', 'ag-starter-companion' ),
						'content' => __( 'Adresse, horaires, telephone. Pas de rendez-vous, venez quand vous voulez.', 'ag-starter-companion' ),
					),
				),
			),
		);
	}

	/**
	 * Register the admin page under Appearance.
	 */
	public function register_admin_page() {
		if ( ! $this->get_active_theme_slug() ) {
			return;
		}
		add_theme_page(
			esc_html__( 'Configuration AG Starter', 'ag-starter-companion' ),
			esc_html__( 'Configuration AG', 'ag-starter-companion' ),
			'manage_options',
			'ag-starter-companion',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Admin notice prompting users to open the setup page.
	 */
	public function admin_notice() {
		$slug = $this->get_active_theme_slug();
		if ( ! $slug || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( get_option( 'ag_starter_companion_done_' . $slug ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'appearance_page_ag-starter-companion' === $screen->id ) {
			return;
		}
		$url  = admin_url( 'themes.php?page=ag-starter-companion' );
		$map  = $this->get_theme_data_map();
		$name = isset( $map[ $slug ] ) ? $map[ $slug ]['name'] : 'AG Starter';
		echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html( $name ) . '</strong> &mdash; ';
		esc_html_e( 'Cliquez ici pour importer le contenu demo en un clic :', 'ag-starter-companion' );
		echo ' <a href="' . esc_url( $url ) . '" class="button button-primary" style="margin-left:8px;">';
		esc_html_e( 'Lancer la configuration', 'ag-starter-companion' );
		echo '</a></p></div>';
	}

	/**
	 * Render the setup page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$slug = $this->get_active_theme_slug();
		if ( ! $slug ) {
			return;
		}
		$map  = $this->get_theme_data_map();
		$data = isset( $map[ $slug ] ) ? $map[ $slug ] : array();
		$name = isset( $data['name'] ) ? $data['name'] : 'AG Starter';

		// Handle actions.
		if ( isset( $_POST['ag_do_import'] ) && check_admin_referer( 'ag_starter_companion_import' ) ) {
			$report = $this->run_import( $slug );
			update_option( 'ag_starter_companion_done_' . $slug, time() );
			echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Import termine avec succes.', 'ag-starter-companion' ) . '</strong></p><ul style="list-style:disc;padding-left:20px;">';
			foreach ( $report as $line ) {
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul><p><a href="' . esc_url( home_url( '/' ) ) . '" class="button button-primary" target="_blank">' . esc_html__( 'Voir le site', 'ag-starter-companion' ) . ' &rarr;</a></p></div>';
		}
		if ( isset( $_POST['ag_do_reset'] ) && check_admin_referer( 'ag_starter_companion_reset' ) ) {
			$this->run_reset( $slug );
			delete_option( 'ag_starter_companion_done_' . $slug );
			echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Contenu demo supprime.', 'ag-starter-companion' ) . '</strong></p></div>';
		}

		$done = (int) get_option( 'ag_starter_companion_done_' . $slug, 0 );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( sprintf( __( 'Configuration — %s', 'ag-starter-companion' ), $name ) ); ?></h1>

			<div style="max-width:780px;margin-top:20px;padding:24px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Import en un clic', 'ag-starter-companion' ); ?></h2>
				<p><?php esc_html_e( 'Cliquez sur le bouton ci-dessous pour installer automatiquement :', 'ag-starter-companion' ); ?></p>
				<ul style="list-style:disc;padding-left:20px;">
					<?php if ( isset( $data['pages'] ) ) : ?>
						<li>
							<?php
							$titles = array_map(
								static function ( $p ) {
									return $p['title'];
								},
								$data['pages']
							);
							echo esc_html( sprintf(
								/* translators: 1: number of pages, 2: list of page titles. */
								__( '%1$d pages creees : %2$s', 'ag-starter-companion' ),
								count( $titles ),
								implode( ', ', $titles )
							) );
							?>
						</li>
					<?php endif; ?>
					<li><?php esc_html_e( 'Menu principal cree et assigne automatiquement', 'ag-starter-companion' ); ?></li>
					<li><?php esc_html_e( 'Page d\'accueil definie (template front-page.php)', 'ag-starter-companion' ); ?></li>
					<li><?php esc_html_e( 'Permaliens actives (/%postname%/)', 'ag-starter-companion' ); ?></li>
				</ul>
				<p><em><?php esc_html_e( 'L\'import est 100% local : aucune connexion internet n\'est necessaire.', 'ag-starter-companion' ); ?></em></p>

				<?php if ( $done ) : ?>
					<p style="padding:12px;background:#e7f5e9;border-left:4px solid #28a745;">
						<strong><?php esc_html_e( 'Contenu demo deja importe.', 'ag-starter-companion' ); ?></strong><br>
						<?php
						printf(
							/* translators: %s: human-readable date. */
							esc_html__( 'Derniere execution : %s', 'ag-starter-companion' ),
							esc_html( wp_date( 'd/m/Y H:i', $done ) )
						);
						?>
					</p>
				<?php endif; ?>

				<form method="post" style="display:inline-block;margin-right:12px;">
					<?php wp_nonce_field( 'ag_starter_companion_import' ); ?>
					<button type="submit" name="ag_do_import" class="button button-primary button-hero">
						<?php echo $done ? esc_html__( 'Relancer l\'import', 'ag-starter-companion' ) : esc_html__( 'Importer le contenu demo', 'ag-starter-companion' ); ?>
					</button>
				</form>

				<?php if ( $done ) : ?>
					<form method="post" style="display:inline-block;" onsubmit="return confirm('<?php echo esc_js( __( 'Supprimer definitivement le contenu demo ?', 'ag-starter-companion' ) ); ?>');">
						<?php wp_nonce_field( 'ag_starter_companion_reset' ); ?>
						<button type="submit" name="ag_do_reset" class="button">
							<?php esc_html_e( 'Reinitialiser', 'ag-starter-companion' ); ?>
						</button>
					</form>
				<?php endif; ?>
			</div>

			<div style="max-width:780px;margin-top:20px;padding:24px;background:#f8f9fa;border:1px solid #ddd;">
				<h3 style="margin-top:0;"><?php esc_html_e( 'Besoin d\'aide ?', 'ag-starter-companion' ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: %s: link to contact page. */
						wp_kses(
							__( 'Ce plugin est offert par AGthèmes (Alliance Group). Si vous bloquez sur la personnalisation ou voulez un site plus avance, %s et on vous aide gratuitement a demarrer.', 'ag-starter-companion' ),
							array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
						),
						'<a href="https://alliancegroupe-inc.com/contact" target="_blank" rel="noopener">' . esc_html__( 'contactez-nous', 'ag-starter-companion' ) . '</a>'
					);
					?>
				</p>
				<p>
					<a href="https://alliancegroupe-inc.com/templates-wordpress" target="_blank" rel="noopener">
						<?php esc_html_e( 'Decouvrir d\'autres templates gratuits', 'ag-starter-companion' ); ?> &rarr;
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Run the import for the given theme slug.
	 *
	 * @param string $slug Theme slug.
	 * @return array
	 */
	public function run_import( $slug ) {
		$log = array();
		$map = $this->get_theme_data_map();
		if ( ! isset( $map[ $slug ] ) ) {
			return $log;
		}
		$pages = $map[ $slug ]['pages'];

		$page_ids = array();
		foreach ( $pages as $slug_key => $data ) {
			$existing = get_page_by_path( $slug_key );
			if ( $existing ) {
				$page_ids[ $slug_key ] = $existing->ID;
				$log[]                 = sprintf( 'Page existante conservee : %s', $data['title'] );
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $data['title'],
					'post_name'    => $slug_key,
					'post_content' => $data['content'],
				)
			);
			if ( ! is_wp_error( $id ) && $id ) {
				$page_ids[ $slug_key ] = $id;
				$log[]                 = sprintf( 'Page creee : %s', $data['title'] );
			}
		}

		if ( isset( $page_ids['accueil'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_ids['accueil'] );
			$log[] = 'Page d\'accueil configuree';
		}

		$menu_name = 'Menu principal AG';
		$menu      = wp_get_nav_menu_object( $menu_name );
		$menu_id   = false;
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
		} else {
			$menu_id = $menu->term_id;
			$items   = wp_get_nav_menu_items( $menu_id );
			if ( $items ) {
				foreach ( $items as $item ) {
					wp_delete_post( $item->ID, true );
				}
			}
		}

		if ( $menu_id && ! is_wp_error( $menu_id ) ) {
			foreach ( $page_ids as $slug_key => $id ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $pages[ $slug_key ]['title'],
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $id,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
			$locations            = get_theme_mod( 'nav_menu_locations' );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
			$log[] = 'Menu principal cree et assigne';
		}

		// Theme-specific extras : Avocat ships demo Domaines d'expertise
		// via the ag_domaine CPT registered by the theme itself.
		if ( 'ag-starter-avocat' === $slug && post_type_exists( 'ag_domaine' ) ) {
			$created = $this->import_avocat_domaines();
			if ( $created ) {
				$log[] = sprintf( '%d domaines d\'expertise crees', $created );
			}
		}

		if ( '' === get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
			flush_rewrite_rules();
			$log[] = 'Permaliens actives';
		}

		return $log;
	}

	/**
	 * Import a starter set of Domaines d'expertise (Avocat CPT).
	 *
	 * Only runs when the active theme is ag-starter-avocat and the CPT
	 * is already registered. Skips any domaine whose slug already exists
	 * so the import is idempotent.
	 *
	 * @return int Number of domaines effectively created.
	 */
	public function import_avocat_domaines() {
		$domaines = array(
			array(
				'slug'    => 'droit-des-affaires',
				'title'   => __( 'Droit des affaires', 'ag-starter-companion' ),
				'icon'    => '💼',
				'excerpt' => __( 'Conseil et representation des entreprises : creation, contrats commerciaux, contentieux, restructuration.', 'ag-starter-companion' ),
				'content' => __( 'Notre cabinet accompagne les dirigeants et les entreprises a chaque etape de leur vie : creation et choix de la structure, redaction et negociation de contrats commerciaux, mise en place de partenariats, contentieux commerciaux, restructuration et procedures collectives.', 'ag-starter-companion' ),
				'examples' => "Creation et statuts de societes\nNegociation de baux commerciaux\nLitiges entre associes\nProcedures de recouvrement\nMise en conformite RGPD",
				'order'   => 1,
			),
			array(
				'slug'    => 'droit-du-travail',
				'title'   => __( 'Droit du travail', 'ag-starter-companion' ),
				'icon'    => '👔',
				'excerpt' => __( 'Defense des salaries et des employeurs : licenciements, contrats, harcelement, ruptures conventionnelles.', 'ag-starter-companion' ),
				'content' => __( 'Que vous soyez salarie ou employeur, nous defendons vos droits avec rigueur. Contestation de licenciement, negociation de rupture conventionnelle, dossiers prud\'homaux, harcelement moral ou sexuel : nous batissons avec vous une strategie adaptee.', 'ag-starter-companion' ),
				'examples' => "Contestation de licenciement\nRupture conventionnelle\nHarcelement moral / sexuel\nNegociation salariale\nProcedure prud\'homale",
				'order'   => 2,
			),
			array(
				'slug'    => 'droit-de-la-famille',
				'title'   => __( 'Droit de la famille', 'ag-starter-companion' ),
				'icon'    => '👨‍👩‍👧',
				'excerpt' => __( 'Divorce, garde d\'enfants, succession, adoption, regimes matrimoniaux. Discretion et empathie garanties.', 'ag-starter-companion' ),
				'content' => __( 'Le droit de la famille touche a l\'intime. Notre approche allie expertise juridique et ecoute bienveillante. Nous traitons les divorces (par consentement mutuel ou contentieux), les pensions alimentaires, les questions de garde et de droit de visite, ainsi que les successions complexes.', 'ag-starter-companion' ),
				'examples' => "Divorce par consentement mutuel\nDivorce contentieux\nGarde d\'enfants et droit de visite\nPensions alimentaires\nSuccession et heritage",
				'order'   => 3,
			),
			array(
				'slug'    => 'droit-immobilier',
				'title'   => __( 'Droit immobilier', 'ag-starter-companion' ),
				'icon'    => '🏠',
				'excerpt' => __( 'Acquisition, vente, copropriete, baux, troubles de voisinage, vices caches.', 'ag-starter-companion' ),
				'content' => __( 'L\'immobilier represente un investissement majeur. Nous accompagnons proprietaires et locataires dans toutes les problematiques : redaction et contestation de baux, copropriete, troubles du voisinage, vices caches, expropriation, urbanisme.', 'ag-starter-companion' ),
				'examples' => "Litiges de copropriete\nContestation de bail\nTroubles de voisinage\nVices caches a l\'achat\nProcedure d\'expulsion",
				'order'   => 4,
			),
			array(
				'slug'    => 'droit-penal',
				'title'   => __( 'Droit penal', 'ag-starter-companion' ),
				'icon'    => '⚖️',
				'excerpt' => __( 'Defense penale 24/7. Garde a vue, plaintes, comparution immediate, instruction.', 'ag-starter-companion' ),
				'content' => __( 'En matiere penale, l\'urgence et la confidentialite sont essentielles. Nous intervenons des la garde a vue, depot de plainte, comparution immediate, instruction et audience de jugement. Defense ferme et personnalisee.', 'ag-starter-companion' ),
				'examples' => "Garde a vue (intervention 24/7)\nDepot de plainte\nComparution immediate\nDefense en cour d\'assises\nVictimes d\'infractions",
				'order'   => 5,
			),
			array(
				'slug'    => 'droit-fiscal',
				'title'   => __( 'Droit fiscal', 'ag-starter-companion' ),
				'icon'    => '📊',
				'excerpt' => __( 'Optimisation, controle fiscal, contentieux, declarations, ISF, donations et successions.', 'ag-starter-companion' ),
				'content' => __( 'Le droit fiscal est en constante evolution. Nous vous accompagnons dans vos choix patrimoniaux, vos declarations, et vous defendons en cas de controle ou de contentieux fiscal. Optimisation legale et securisation de votre patrimoine.', 'ag-starter-companion' ),
				'examples' => "Controle fiscal\nContentieux fiscal\nOptimisation patrimoniale\nDonations / successions\nDeclaration ISF / IFI",
				'order'   => 6,
			),
		);

		$created = 0;
		foreach ( $domaines as $d ) {
			$existing = get_posts(
				array(
					'name'           => $d['slug'],
					'post_type'      => 'ag_domaine',
					'post_status'    => array( 'publish', 'draft', 'future', 'pending', 'private' ),
					'posts_per_page' => 1,
				)
			);
			if ( $existing ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'ag_domaine',
					'post_status'  => 'publish',
					'post_title'   => $d['title'],
					'post_name'    => $d['slug'],
					'post_excerpt' => $d['excerpt'],
					'post_content' => $d['content'],
					'menu_order'   => $d['order'],
				)
			);
			if ( ! is_wp_error( $id ) && $id ) {
				update_post_meta( $id, '_ag_domaine_icon', $d['icon'] );
				update_post_meta( $id, '_ag_domaine_examples', $d['examples'] );
				$created++;
			}
		}
		return $created;
	}

	/**
	 * Reset the demo content for the given theme slug.
	 *
	 * @param string $slug Theme slug.
	 */
	public function run_reset( $slug ) {
		$map = $this->get_theme_data_map();
		if ( ! isset( $map[ $slug ] ) ) {
			return;
		}
		foreach ( array_keys( $map[ $slug ]['pages'] ) as $page_slug ) {
			$p = get_page_by_path( $page_slug );
			if ( $p ) {
				wp_delete_post( $p->ID, true );
			}
		}
		$menu = wp_get_nav_menu_object( 'Menu principal AG' );
		if ( $menu ) {
			wp_delete_nav_menu( $menu->term_id );
		}
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', 0 );

		// Theme-specific cleanup : delete demo avocat domaines.
		if ( 'ag-starter-avocat' === $slug && post_type_exists( 'ag_domaine' ) ) {
			$demo_slugs = array(
				'droit-des-affaires', 'droit-du-travail', 'droit-de-la-famille',
				'droit-immobilier', 'droit-penal', 'droit-fiscal',
			);
			foreach ( $demo_slugs as $demo_slug ) {
				$found = get_posts(
					array(
						'name'           => $demo_slug,
						'post_type'      => 'ag_domaine',
						'post_status'    => array( 'publish', 'draft', 'private' ),
						'posts_per_page' => 1,
					)
				);
				if ( $found ) {
					wp_delete_post( $found[0]->ID, true );
				}
			}
		}
	}

	// ═══════════════════════════════════════════════════════════════
	// AUTO-UPDATE PREFERENCE + UPDATE NOTIFICATION
	// ═══════════════════════════════════════════════════════════════

	/**
	 * Auto-approve updates for this plugin (opt-in by default).
	 */
	public function enable_auto_update( $update, $item ) {
		if ( isset( $item->slug ) && 'ag-starter-companion' === $item->slug ) {
			return true;
		}
		return $update;
	}

	/**
	 * Show a prominent notice when an update is available.
	 */
	public function update_available_notice() {
		if ( ! current_user_can( 'update_plugins' ) ) return;

		$plugin_file = plugin_basename( AG_STARTER_COMPANION_FILE );
		$updates = get_site_transient( 'update_plugins' );
		if ( empty( $updates->response[ $plugin_file ] ) ) return;

		$update = $updates->response[ $plugin_file ];
		$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=ag-starter-companion&TB_iframe=true&width=772&height=840' );
		$update_url  = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin_file ) ), 'upgrade-plugin_' . $plugin_file );
		?>
		<div class="notice notice-warning" style="border-left-color:#D4B45C;padding:16px 20px;">
			<p style="font-size:1rem;margin:0 0 8px;">
				<strong>🔄 AG Starter Companion v<?php echo esc_html( $update->new_version ); ?></strong>
				<?php esc_html_e( 'est disponible !', 'ag-starter-companion' ); ?>
			</p>
			<p style="margin:0 0 12px;color:#555;">
				<?php esc_html_e( 'Cette mise a jour contient de nouvelles fonctionnalites et corrections. Les mises a jour automatiques sont activees par defaut.', 'ag-starter-companion' ); ?>
			</p>
			<a href="<?php echo esc_url( $details_url ); ?>" class="thickbox open-plugin-details-modal" style="margin-right:12px;">
				<?php esc_html_e( 'Afficher les details', 'ag-starter-companion' ); ?>
			</a>
			<a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Mettre a jour maintenant', 'ag-starter-companion' ); ?>
			</a>
		</div>
		<?php
	}

	// ═══════════════════════════════════════════════════════════════
	// SELF-UPDATER — auto-update this plugin from alliancegroupe-inc.com
	// ═══════════════════════════════════════════════════════════════

	const UPDATE_URL = 'https://alliancegroupe-inc.com/wp-json/ag/v1/companion-update';

	public function check_self_update( $transient ) {
		if ( empty( $transient->checked ) ) return $transient;

		$cache_key = 'ag_companion_update_check';
		$remote    = get_transient( $cache_key );

		if ( false === $remote ) {
			$resp = wp_remote_get( self::UPDATE_URL, array( 'timeout' => 10 ) );
			if ( ! is_wp_error( $resp ) && 200 === wp_remote_retrieve_response_code( $resp ) ) {
				$remote = json_decode( wp_remote_retrieve_body( $resp ), true );
				set_transient( $cache_key, $remote, 12 * HOUR_IN_SECONDS );
			}
		}

		if ( $remote && ! empty( $remote['version'] ) && version_compare( AG_STARTER_COMPANION_VERSION, $remote['version'], '<' ) ) {
			$plugin_slug = plugin_basename( AG_STARTER_COMPANION_FILE );
			$transient->response[ $plugin_slug ] = (object) array(
				'slug'        => 'ag-starter-companion',
				'plugin'      => $plugin_slug,
				'new_version' => $remote['version'],
				'url'         => $remote['url'] ?? 'https://alliancegroupe-inc.com/templates-wordpress',
				'package'     => $remote['download_url'] ?? '',
				'tested'      => $remote['tested'] ?? '6.5',
				'requires'    => $remote['requires'] ?? '6.0',
				'requires_php'=> $remote['requires_php'] ?? '7.4',
			);
		}

		return $transient;
	}

	public function self_update_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) return $result;
		if ( ! isset( $args->slug ) || 'ag-starter-companion' !== $args->slug ) return $result;

		$cache_key = 'ag_companion_update_check';
		$remote    = get_transient( $cache_key );
		if ( ! $remote || empty( $remote['version'] ) ) return $result;

		$info = new stdClass();
		$info->name          = 'AG Starter Companion';
		$info->slug          = 'ag-starter-companion';
		$info->version       = $remote['version'];
		$info->author        = '<a href="https://alliancegroupe-inc.com">Alliance Groupe</a>';
		$info->homepage      = 'https://alliancegroupe-inc.com/templates-wordpress';
		$info->requires      = $remote['requires'] ?? '6.0';
		$info->requires_php  = $remote['requires_php'] ?? '7.4';
		$info->tested        = $remote['tested'] ?? '6.5';
		$info->download_link = $remote['download_url'] ?? '';
		$info->last_updated  = date( 'Y-m-d' );
		$info->added         = '2026-01-01';
		$info->active_installs = 100;

		if ( ! empty( $remote['banners'] ) ) {
			$info->banners = $remote['banners'];
		}

		$info->sections = array(
			'description' => $remote['description'] ?? 'Plugin compagnon pour les themes AG Starter.',
			'changelog'   => $remote['changelog'] ?? 'Mise a jour disponible.',
			'installation'=> '<ol>'
				. '<li>Téléchargez le fichier ZIP du plugin</li>'
				. '<li>Dans wp-admin, allez dans Extensions → Ajouter → Téléverser</li>'
				. '<li>Choisissez le ZIP et cliquez sur Installer</li>'
				. '<li>Activez le plugin</li>'
				. '<li>Un encart apparaît dans le tableau de bord pour importer le contenu demo en 1 clic</li>'
				. '</ol>',
			'faq'         => '<h4>Le plugin est-il gratuit ?</h4>'
				. '<p>Oui, 100% gratuit. Les packs Pro/Premium/Business sont des options payantes facultatives.</p>'
				. '<h4>Fonctionne-t-il avec tous les thèmes AG Starter ?</h4>'
				. '<p>Oui : Restaurant, Artisan, Coach et Avocat.</p>'
				. '<h4>Que fait le bouton "Importer le contenu demo" ?</h4>'
				. '<p>Il crée automatiquement les pages, le menu principal, configure la page d\'accueil et les permaliens.</p>',
		);

		return $info;
	}

	// ═══════════════════════════════════════════════════════════════
	// UPGRADE PROMOS — shown everywhere until user upgrades to Pro
	// ═══════════════════════════════════════════════════════════════

	private function is_free_tier() {
		if ( class_exists( 'AG_Licence_Client' ) ) {
			return AG_Licence_Client::get_tier() === 'free';
		}
		return true;
	}

	private function get_upgrade_url( $pack = 'pro' ) {
		$urls = $this->get_stripe_urls();
		if ( ! empty( $urls[ $pack ] ) && 'STRIPE_PLACEHOLDER' !== $urls[ $pack ] ) {
			return $urls[ $pack ];
		}
		return 'https://alliancegroupe-inc.com/templates-wordpress?pack=' . $pack . '#ag-pricing';
	}

	private function get_stripe_urls() {
		$cached = get_transient( 'ag_companion_stripe_urls' );
		if ( false !== $cached ) return $cached;

		$remote = get_transient( 'ag_companion_update_check' );
		if ( $remote && ! empty( $remote['stripe_urls'] ) ) {
			set_transient( 'ag_companion_stripe_urls', $remote['stripe_urls'], 12 * HOUR_IN_SECONDS );
			return $remote['stripe_urls'];
		}

		$resp = wp_remote_get( self::UPDATE_URL, array( 'timeout' => 10 ) );
		if ( ! is_wp_error( $resp ) && 200 === wp_remote_retrieve_response_code( $resp ) ) {
			$data = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( ! empty( $data['stripe_urls'] ) ) {
				set_transient( 'ag_companion_stripe_urls', $data['stripe_urls'], 12 * HOUR_IN_SECONDS );
				return $data['stripe_urls'];
			}
		}

		return array( 'pro' => '', 'premium' => '', 'business' => '' );
	}

	/**
	 * Big upgrade banner on all admin pages.
	 */
	public function upgrade_banner() {
		if ( ! $this->get_active_theme_slug() || ! $this->is_free_tier() ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;
		$done = get_option( 'ag_starter_companion_done_' . $this->get_active_theme_slug() );
		if ( ! $done ) return; // Show companion install notice first

		$dismissed = get_user_meta( get_current_user_id(), 'ag_upgrade_dismissed', true );
		if ( $dismissed && ( time() - intval( $dismissed ) ) < 7 * DAY_IN_SECONDS ) return;

		if ( isset( $_GET['ag_dismiss_upgrade'] ) && '1' === $_GET['ag_dismiss_upgrade'] ) {
			update_user_meta( get_current_user_id(), 'ag_upgrade_dismissed', time() );
			return;
		}

		$dismiss_url = add_query_arg( 'ag_dismiss_upgrade', '1' );
		?>
		<div style="background:linear-gradient(135deg,#1a1a2e 0%,#0f0f18 100%);border:1px solid rgba(212,180,92,.35);border-radius:10px;padding:28px 32px;margin:20px 20px 10px 0;display:flex;align-items:center;gap:28px;flex-wrap:wrap;position:relative;">
			<a href="<?php echo esc_url( $dismiss_url ); ?>" style="position:absolute;top:10px;right:14px;color:rgba(255,255,255,.3);font-size:1.2rem;text-decoration:none;" title="Masquer 7 jours">✕</a>
			<div style="flex:1;min-width:260px;">
				<h2 style="color:#D4B45C;font-size:1.3rem;margin:0 0 8px;font-weight:800;">⚡ <?php esc_html_e( 'Passez a la version Pro', 'ag-starter-companion' ); ?></h2>
				<p style="color:rgba(255,255,255,.75);font-size:.95rem;line-height:1.6;margin:0;">
					<?php esc_html_e( 'Header sticky, animations scroll, couleurs avancees, temoignages clients, galerie photos, boutique WooCommerce, grille de tarifs, white-label... Paiement unique, mises a jour a vie.', 'ag-starter-companion' ); ?>
				</p>
			</div>
			<div style="display:flex;gap:10px;flex-wrap:wrap;">
				<a href="<?php echo esc_url( $this->get_upgrade_url( 'pro' ) ); ?>" target="_blank" rel="noopener" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-weight:700;padding:14px 22px;border-radius:8px;text-decoration:none;font-size:.9rem;white-space:nowrap;">Pro — 49€ →</a>
				<a href="<?php echo esc_url( $this->get_upgrade_url( 'premium' ) ); ?>" target="_blank" rel="noopener" style="display:inline-block;background:rgba(212,180,92,.15);color:#D4B45C;font-weight:700;padding:14px 22px;border-radius:8px;text-decoration:none;font-size:.9rem;white-space:nowrap;border:1px solid rgba(212,180,92,.4);">Premium — 99€ →</a>
				<a href="<?php echo esc_url( $this->get_upgrade_url( 'business' ) ); ?>" target="_blank" rel="noopener" style="display:inline-block;background:rgba(212,180,92,.15);color:#D4B45C;font-weight:700;padding:14px 22px;border-radius:8px;text-decoration:none;font-size:.9rem;white-space:nowrap;border:1px solid rgba(212,180,92,.4);">Business — 149€ →</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Dashboard widget with upgrade CTA.
	 */
	public function upgrade_dashboard_widget() {
		if ( ! $this->get_active_theme_slug() || ! $this->is_free_tier() ) return;
		wp_add_dashboard_widget(
			'ag_upgrade_widget',
			'⭐ ' . esc_html__( 'Passer a la version Pro', 'ag-starter-companion' ),
			array( $this, 'render_upgrade_dashboard' )
		);
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['ag_upgrade_widget'] ) ) {
			$widget = $wp_meta_boxes['dashboard']['normal']['core']['ag_upgrade_widget'];
			unset( $wp_meta_boxes['dashboard']['normal']['core']['ag_upgrade_widget'] );
			$wp_meta_boxes['dashboard']['side']['high']['ag_upgrade_widget'] = $widget;
		}
	}

	public function render_upgrade_dashboard() {
		$url = $this->get_upgrade_url();
		?>
		<div style="text-align:center;padding:16px 0;">
			<div style="font-size:2.4rem;margin-bottom:12px;">🚀</div>
			<h3 style="margin:0 0 10px;font-size:1.1rem;"><?php esc_html_e( 'Vous utilisez la version gratuite', 'ag-starter-companion' ); ?></h3>

			<div style="background:#f8f5ec;border:1px solid #D4B45C;border-radius:8px;padding:18px;margin:0 0 16px;">
				<div style="margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,.08);">
					<strong style="font-size:1rem;">⚡ Pro — 49€</strong>
					<p style="font-size:.82rem;color:#555;margin:4px 0 0;line-height:1.4;">Header sticky, animations scroll, couleur d'accent secondaire, fond footer personnalisable, 2 polices premium.</p>
				</div>
				<div style="margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,.08);">
					<strong style="font-size:1rem;">💎 Premium — 99€</strong>
					<p style="font-size:.82rem;color:#555;margin:4px 0 0;line-height:1.4;">Tout Pro + section temoignages, galerie photos, grille de tarifs, compatible WooCommerce (boutique en ligne), traductions 6 langues.</p>
				</div>
				<div>
					<strong style="font-size:1rem;">🏆 Business — 149€</strong>
					<p style="font-size:.82rem;color:#555;margin:4px 0 0;line-height:1.4;">Tout Premium + white-label (credits AG supprimes), templates de pages supplementaires, session strategique 30 min incluse.</p>
				</div>
				<p style="font-size:.78rem;color:#888;margin:12px 0 0;text-align:center;">Paiement unique — mises a jour a vie — support inclus</p>
			</div>
			<div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
				<a href="<?php echo esc_url( $this->get_upgrade_url( 'pro' ) ); ?>" target="_blank" rel="noopener" class="button button-primary" style="font-size:.85rem;padding:6px 14px;">Pro — 49€</a>
				<a href="<?php echo esc_url( $this->get_upgrade_url( 'premium' ) ); ?>" target="_blank" rel="noopener" class="button button-primary" style="font-size:.85rem;padding:6px 14px;">Premium — 99€</a>
				<a href="<?php echo esc_url( $this->get_upgrade_url( 'business' ) ); ?>" target="_blank" rel="noopener" class="button button-primary" style="font-size:.85rem;padding:6px 14px;">Business — 149€</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Locked section in the Customizer.
	 */
	public function upgrade_customizer_section( $wp_customize ) {
		if ( ! $this->get_active_theme_slug() || ! $this->is_free_tier() ) return;

		$wp_customize->add_section( 'ag_locked_pro', array(
			'title'       => esc_html__( '🔒 Header Sticky + Couleurs (Pro)', 'ag-starter-companion' ),
			'priority'    => 30,
			'description' => esc_html__( 'Le header sticky au scroll, la couleur d\'accent secondaire et le fond de footer personnalisable sont disponibles avec le Pack Pro (49€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_pro_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_pro_info', array(
			'label'       => esc_html__( 'A partir de 49€', 'ag-starter-companion' ),
			'section'     => 'ag_locked_pro',
			'type'        => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'pro' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Acheter le Pack Pro — 49€ →</a>',
		) );

		$wp_customize->add_section( 'ag_locked_animations', array(
			'title'       => esc_html__( '🔒 Animations au scroll (Pro)', 'ag-starter-companion' ),
			'priority'    => 31,
			'description' => esc_html__( 'Animations fade-in, slide-left, slide-right et scale-in au scroll. Vos sections apparaissent avec elegance quand le visiteur scrolle. Compatible prefers-reduced-motion. Pack Pro (49€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_anim_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_anim_info', array(
			'label'   => esc_html__( 'A partir de 49€', 'ag-starter-companion' ),
			'section' => 'ag_locked_animations',
			'type'    => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'pro' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Acheter le Pack Pro — 49€ →</a>',
		) );

		$wp_customize->add_section( 'ag_locked_testimonials', array(
			'title'       => esc_html__( '🔒 Temoignages + Boutique + Galerie (Premium)', 'ag-starter-companion' ),
			'priority'    => 32,
			'description' => esc_html__( 'Section temoignages clients (jusqu\'a 6 avec etoiles), galerie photos avec hover zoom, grille de tarifs, compatibilite WooCommerce pour une boutique en ligne, traductions automatiques en 6 langues (FR, EN, ES, IT, DE, AR). Pack Premium (99€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_testi_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_testi_info', array(
			'label'   => esc_html__( 'A partir de 99€', 'ag-starter-companion' ),
			'section' => 'ag_locked_testimonials',
			'type'    => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'premium' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Acheter le Pack Premium — 99€ →</a>',
		) );

		$wp_customize->add_section( 'ag_locked_whitelabel', array(
			'title'       => esc_html__( '🔒 White-Label + Session strategique (Business)', 'ag-starter-companion' ),
			'priority'    => 33,
			'description' => esc_html__( 'Supprimez tous les credits Alliance Groupe, personnalisez entierement le footer, acces a des templates de pages supplementaires, et session strategique de 30 min offerte avec un expert pour optimiser votre site. Pack Business (149€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_wl_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_wl_info', array(
			'label'   => esc_html__( 'A partir de 149€', 'ag-starter-companion' ),
			'section' => 'ag_locked_whitelabel',
			'type'    => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'business' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Acheter le Pack Business — 149€ →</a>',
		) );
	}

	/**
	 * Subtle footer nudge on every admin page.
	 */
	public function upgrade_footer_nudge() {
		if ( ! $this->get_active_theme_slug() || ! $this->is_free_tier() ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;
		$url = $this->get_upgrade_url( 'pro' );
		?>
		<div style="position:fixed;bottom:0;left:0;right:0;background:rgba(10,10,15,.95);border-top:2px solid #D4B45C;padding:10px 24px;display:flex;align-items:center;justify-content:center;gap:16px;z-index:9999;font-size:.88rem;" id="ag-footer-nudge">
			<span style="color:rgba(255,255,255,.7);">⚡ <?php esc_html_e( 'Version gratuite — Passez a Pro pour debloquer toutes les fonctionnalites', 'ag-starter-companion' ); ?></span>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" style="background:#D4B45C;color:#0a0a0f;font-weight:700;padding:6px 18px;border-radius:6px;text-decoration:none;font-size:.85rem;">Passer a Pro →</a>
			<a href="#" onclick="document.getElementById('ag-footer-nudge').style.display='none';return false;" style="color:rgba(255,255,255,.3);font-size:.9rem;text-decoration:none;margin-left:8px;">✕</a>
		</div>
		<?php
	}

	/**
	 * Patch the active theme with missing files (licence client, updater, pro-features).
	 * Runs once per companion version bump. Downloads files from GitHub if missing locally.
	 */
	public function maybe_patch_theme() {
		$slug = $this->get_active_theme_slug();
		if ( ! $slug || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$patch_key = 'ag_companion_patched_' . AG_STARTER_COMPANION_VERSION . '_' . $slug;
		if ( get_option( $patch_key ) ) {
			return;
		}

		$theme_dir = get_template_directory();
		$inc_dir   = $theme_dir . '/inc';
		if ( ! is_dir( $inc_dir ) ) {
			wp_mkdir_p( $inc_dir );
		}

		$gh_base = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/claude/rebuild-alliance-theme-fl7ca/alliance-groupe-theme/assets/downloads/' . $slug . '/inc/';

		$files_to_patch = array(
			'class-ag-licence-client.php',
			'class-ag-updater.php',
			'pro-features.php',
			'pro-scripts.js',
		);

		$patched = 0;
		foreach ( $files_to_patch as $file ) {
			$local = $inc_dir . '/' . $file;
			if ( file_exists( $local ) ) {
				continue;
			}

			$resp = wp_remote_get( $gh_base . $file, array( 'timeout' => 30 ) );
			if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
				continue;
			}

			$content = wp_remote_retrieve_body( $resp );
			if ( strlen( $content ) > 50 ) {
				file_put_contents( $local, $content );
				$patched++;
			}
		}

		// Patch functions.php if licence require is missing
		$functions_file = $theme_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			if ( false === strpos( $functions_content, 'class-ag-licence-client.php' ) ) {
				$inject = "\n\n" .
					"// ── AG Licence & Pro (added by AG Starter Companion " . AG_STARTER_COMPANION_VERSION . ") ──\n" .
					"if ( file_exists( get_template_directory() . '/inc/class-ag-licence-client.php' ) ) {\n" .
					"    require get_template_directory() . '/inc/class-ag-licence-client.php';\n" .
					"    require get_template_directory() . '/inc/class-ag-updater.php';\n" .
					"    if ( file_exists( get_template_directory() . '/inc/pro-features.php' ) ) {\n" .
					"        require get_template_directory() . '/inc/pro-features.php';\n" .
					"    }\n" .
					"    add_action( 'after_setup_theme', function () {\n" .
					"        AG_Licence_Client::register_admin();\n" .
					"        new AG_Theme_Updater( '" . esc_attr( $slug ) . "', wp_get_theme()->get( 'Version' ) );\n" .
					"        if ( class_exists( 'AG_Pro_Features' ) ) { new AG_Pro_Features( '" . esc_attr( $slug ) . "' ); }\n" .
					"    }, 20 );\n" .
					"    add_action( 'wp_enqueue_scripts', function () {\n" .
					"        if ( class_exists( 'AG_Licence_Client' ) && AG_Licence_Client::get_tier() !== 'free' ) {\n" .
					"            wp_enqueue_script( 'ag-pro-scripts', get_template_directory_uri() . '/inc/pro-scripts.js', array(), '2.0.0', true );\n" .
					"        }\n" .
					"    } );\n" .
					"}\n";
				file_put_contents( $functions_file, $functions_content . $inject );
				$patched++;
			}
		}

		if ( $patched > 0 ) {
			update_option( $patch_key, time() );
		} else {
			update_option( $patch_key, time() );
		}
	}
}

new AG_Starter_Companion();
