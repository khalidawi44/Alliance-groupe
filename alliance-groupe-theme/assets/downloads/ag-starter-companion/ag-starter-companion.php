<?php
/**
 * Plugin Name:       AG Starter Companion
 * Plugin URI:        https://alliancegroupe-inc.com/templates-wordpress
 * Description:       Importer un clic pour les themes AG Starter (Restaurant, Artisan, Coach, Avocat). Cree automatiquement les pages, le menu et les reglages pour un site pret a l'emploi.
 * Version:           1.12.0
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

define( 'AG_STARTER_COMPANION_VERSION', '1.12.0' );
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
		add_action( 'admin_init', array( $this, 'maybe_auto_reimport' ) );
		add_action( 'admin_init', array( $this, 'maybe_fix_theme_menu' ) );
		add_action( 'admin_init', array( $this, 'maybe_install_research_plugin' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_update_notice' ) );
		add_action( 'admin_notices', array( $this, 'upgrade_banner' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'upgrade_dashboard_widget' ) );
		add_action( 'customize_register', array( $this, 'upgrade_customizer_section' ), 99 );
		add_action( 'admin_footer', array( $this, 'upgrade_footer_nudge' ) );

		// CPT « Domaine d'expertise » (Avocat) — fourni par le PLUGIN (conformité
		// WordPress.org : un thème ne doit pas enregistrer de CPT). Gardé par
		// post_type_exists() → pas de double si le thème l'enregistre déjà.
		add_action( 'init', array( $this, 'register_avocat_domaine_cpt' ), 9 );
		add_action( 'add_meta_boxes', array( $this, 'avocat_domaine_metabox' ) );
		add_action( 'save_post_ag_domaine', array( $this, 'avocat_domaine_save_meta' ) );

		// Personnalisation Premium (Alliance) : image de hero modifiable + effets
		// visuels premium, communs a tous les themes AG Starter.
		add_action( 'customize_register', array( $this, 'premium_personalization_register' ), 100 );
		add_action( 'wp_head', array( $this, 'premium_personalization_css' ), 99 );
		add_action( 'wp_footer', array( $this, 'premium_personalization_js' ), 99 );

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
	 * CPT « Domaine d'expertise » (Avocat). Enregistré par le plugin pour la
	 * conformité WordPress.org. N'agit que si le thème Avocat est actif et que
	 * le CPT n'est pas déjà enregistré (évite le double avec la version premium).
	 */
	public function register_avocat_domaine_cpt() {
		if ( 'ag-starter-avocat' !== $this->get_active_theme_slug() ) {
			return;
		}
		if ( post_type_exists( 'ag_domaine' ) ) {
			return;
		}
		register_post_type( 'ag_domaine', array(
			'labels' => array(
				'name'          => __( 'Domaines d\'expertise', 'ag-starter-companion' ),
				'singular_name' => __( 'Domaine d\'expertise', 'ag-starter-companion' ),
				'menu_name'     => __( 'Domaines', 'ag-starter-companion' ),
				'add_new_item'  => __( 'Ajouter un domaine', 'ag-starter-companion' ),
				'edit_item'     => __( 'Modifier le domaine', 'ag-starter-companion' ),
				'all_items'     => __( 'Tous les domaines', 'ag-starter-companion' ),
				'not_found'     => __( 'Aucun domaine trouve.', 'ag-starter-companion' ),
			),
			'public'        => true,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 22,
			'rewrite'       => array( 'slug' => 'domaine' ),
			'has_archive'   => true,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields' ),
			'show_in_rest'  => true,
		) );
	}

	public function avocat_domaine_metabox() {
		if ( 'ag-starter-avocat' !== $this->get_active_theme_slug() ) {
			return;
		}
		add_meta_box( 'ag_domaine_meta', __( 'Détails du domaine', 'ag-starter-companion' ), array( $this, 'avocat_domaine_metabox_render' ), 'ag_domaine', 'side', 'default' );
	}

	public function avocat_domaine_metabox_render( $post ) {
		wp_nonce_field( 'ag_domaine_meta_save', 'ag_domaine_meta_nonce' );
		$icon     = get_post_meta( $post->ID, '_ag_domaine_icon', true );
		$examples = get_post_meta( $post->ID, '_ag_domaine_examples', true );
		echo '<p><label for="ag_domaine_icon"><strong>' . esc_html__( 'Icône', 'ag-starter-companion' ) . '</strong></label><br>';
		echo '<input type="text" id="ag_domaine_icon" name="ag_domaine_icon" value="' . esc_attr( $icon ) . '" maxlength="20" style="width:160px;text-align:center;" placeholder="scales"><br>';
		echo '<small>' . esc_html__( 'Mots-clés : scales, gavel, shield, briefcase, house, family, document, heart, lock, bank. Sinon emoji.', 'ag-starter-companion' ) . '</small></p>';
		echo '<p><label for="ag_domaine_examples"><strong>' . esc_html__( 'Exemples de cas traités', 'ag-starter-companion' ) . '</strong></label><br>';
		echo '<textarea id="ag_domaine_examples" name="ag_domaine_examples" rows="4" style="width:100%;">' . esc_textarea( $examples ) . '</textarea><br>';
		echo '<small>' . esc_html__( '1 ligne par exemple.', 'ag-starter-companion' ) . '</small></p>';
	}

	public function avocat_domaine_save_meta( $post_id ) {
		if ( ! isset( $_POST['ag_domaine_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ag_domaine_meta_nonce'] ) ), 'ag_domaine_meta_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['ag_domaine_icon'] ) ) {
			update_post_meta( $post_id, '_ag_domaine_icon', sanitize_text_field( wp_unslash( $_POST['ag_domaine_icon'] ) ) );
		}
		if ( isset( $_POST['ag_domaine_examples'] ) ) {
			update_post_meta( $post_id, '_ag_domaine_examples', sanitize_textarea_field( wp_unslash( $_POST['ag_domaine_examples'] ) ) );
		}
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
	 * Installe + active automatiquement le plugin « AG Recherche Juridique »
	 * sur les sites du thème Avocat (inclus avec le template). Téléchargé depuis
	 * GitHub, installé via le Plugin_Upgrader natif. Idempotent : ne tente
	 * qu'une fois, et jamais si déjà installé. Réservé à l'avocat (l'outil est
	 * lui-même réservé au cabinet connecté).
	 */
	/**
	 * Contexte « maintenance » : MAJ de plugin/thème en cours, requête AJAX ou
	 * cron, installation WP. Les routines lourdes (téléchargements GitHub,
	 * Plugin_Upgrader, écriture de functions.php) NE DOIVENT PAS s'exécuter dans
	 * ces contextes : sur certains hébergements (XAMPP sans bundle CA, etc.) un
	 * wp_remote_get HTTPS qui traîne bloque l'updater → la MAJ reste « en cours »
	 * et le plugin paraît ne pas s'activer.
	 */
	private function is_maintenance_context() {
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return true;
		}
		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return true;
		}
		if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
			return true;
		}
		global $pagenow;
		if ( isset( $pagenow ) && in_array( $pagenow, array( 'update.php', 'update-core.php' ), true ) ) {
			return true;
		}
		return false;
	}

	public function maybe_install_research_plugin() {
		if ( $this->is_maintenance_context() ) {
			return; // jamais pendant une MAJ/AJAX/cron : éviterait de bloquer l'updater
		}
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		if ( 'ag-starter-avocat' !== $this->get_active_theme_slug() ) {
			return; // outil métier avocat uniquement
		}
		$plugin_file = 'ag-avocat-recherche/ag-avocat-recherche.php';
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// Déjà présent → on s'assure juste qu'il est actif, et on s'arrête.
		if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				activate_plugin( $plugin_file );
			}
			update_option( 'ag_companion_research_installed', AG_STARTER_COMPANION_VERSION );
			return;
		}
		// On ne retente pas en boucle si une install a déjà été tentée.
		if ( get_option( 'ag_companion_research_installed' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$zip = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/ag-avocat-recherche.zip';
		$skin     = new Automatic_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $zip );

		// Marque la tentative (succès comme échec) pour ne pas boucler.
		update_option( 'ag_companion_research_installed', AG_STARTER_COMPANION_VERSION );

		if ( true === $result && file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
			activate_plugin( $plugin_file );
		}
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
		if ( isset( $_POST['ag_do_cleanup'] ) && check_admin_referer( 'ag_starter_companion_cleanup' ) ) {
			$report = $this->run_cleanup( $slug );
			update_option( 'ag_starter_companion_done_' . $slug, time() );
			echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Nettoyage termine.', 'ag-starter-companion' ) . '</strong></p><ul style="list-style:disc;padding-left:20px;">';
			foreach ( $report as $line ) {
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul><p><a href="' . esc_url( home_url( '/' ) ) . '" class="button button-primary" target="_blank">' . esc_html__( 'Voir le site', 'ag-starter-companion' ) . ' &rarr;</a></p></div>';
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

				<form method="post" style="display:inline-block;margin-left:12px;" onsubmit="return confirm('<?php echo esc_js( __( 'Mettre a la corbeille les pages des AUTRES themes et reconstruire un menu propre pour ce theme ?', 'ag-starter-companion' ) ); ?>');">
					<?php wp_nonce_field( 'ag_starter_companion_cleanup' ); ?>
					<button type="submit" name="ag_do_cleanup" class="button">
						🧹 <?php esc_html_e( 'Nettoyer (pages/menus d\'autres themes)', 'ag-starter-companion' ); ?>
					</button>
				</form>
				<p style="margin-top:10px;color:#777;font-size:.92em;max-width:780px;">
					<?php esc_html_e( 'Utile si vous avez teste plusieurs templates sur ce site : met a la corbeille les pages des autres themes (Coach, Barber...), supprime les menus parasites et reconstruit le menu de CE theme avec ses propres pages.', 'ag-starter-companion' ); ?>
				</p>
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
	/**
	 * Re-import auto a chaque MAJ du Companion : detecte le slug du
	 * theme actif (ag-starter-avocat, restaurant, artisan, coach,
	 * barber) et relance run_import si la version stockee differe.
	 */
	public function maybe_auto_reimport() {
		if ( $this->is_maintenance_context() ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;
		$theme = wp_get_theme();
		$slug  = $theme->get_template();
		$known = array( 'ag-starter-avocat', 'ag-starter-restaurant', 'ag-starter-artisan', 'ag-starter-coach', 'ag-starter-barber' );
		if ( ! in_array( $slug, $known, true ) ) return;
		$key  = 'ag_companion_imported_' . $slug;
		$cur  = AG_STARTER_COMPANION_VERSION;
		$prev = get_option( $key, '' );
		if ( $prev === $cur ) return;
		// Ne lance que si l'import initial a deja ete fait (sinon on
		// laisse le user cliquer manuellement la 1ere fois).
		if ( ! $prev ) {
			update_option( $key, $cur );
			return;
		}
		$this->run_import( $slug );
		update_option( $key, $cur );
		set_transient( 'ag_companion_just_updated_from', $prev, 60 );
	}

	/**
	 * Separation des menus par theme.
	 *
	 * Les emplacements de menu sont partages au niveau du site : quand on
	 * teste plusieurs themes (avocat, coach...) sur une meme install, le
	 * menu d'un theme peut rester assigne a l'emplacement "primary" d'un
	 * autre -> contenus melanges (ex. items Coach affiches sur Avocat).
	 *
	 * Ici on verifie que le menu assigne a "primary" appartient BIEN au
	 * theme actif (au moins une de ses pages). Sinon on relance run_import
	 * pour ce theme : il (re)cree le menu "Menu principal AG" avec les pages
	 * du theme actif et l'assigne -> chaque theme a son menu propre.
	 *
	 * Idempotent : une fois le bon menu assigne, la condition est satisfaite
	 * et on ne relance plus rien (pas de boucle). Respecte un menu custom du
	 * client tant qu'il contient au moins une page du theme.
	 */
	public function maybe_fix_theme_menu() {
		if ( $this->is_maintenance_context() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$theme = wp_get_theme();
		$slug  = $theme->get_template();
		$map   = $this->get_theme_data_map();
		if ( ! isset( $map[ $slug ] ) ) {
			return;
		}

		// Slugs de pages attendus pour ce theme (hors accueil).
		$expected = array_diff( array_keys( $map[ $slug ]['pages'] ), array( 'accueil' ) );
		if ( empty( $expected ) ) {
			return;
		}

		$locations = (array) get_theme_mod( 'nav_menu_locations' );
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

		$matches = false;
		if ( $menu_id && wp_get_nav_menu_object( $menu_id ) ) {
			$items = wp_get_nav_menu_items( $menu_id );
			if ( $items ) {
				foreach ( $items as $item ) {
					if ( 'post_type' === $item->type && 'page' === $item->object ) {
						$p = get_post( (int) $item->object_id );
						if ( $p && in_array( $p->post_name, $expected, true ) ) {
							$matches = true;
							break;
						}
					}
				}
			}
		}

		if ( $matches ) {
			return; // Le menu assigne appartient bien au theme actif.
		}

		// Menu absent, vide ou d'un autre theme -> on (re)construit celui du
		// theme actif et on l'assigne.
		$this->run_import( $slug );
		update_option( 'ag_starter_companion_done_' . $slug, time() );
	}

	public function maybe_show_update_notice() {
		$prev = get_transient( 'ag_companion_just_updated_from' );
		if ( $prev === false ) return;
		?>
		<div class="notice notice-success is-dismissible" style="border-left-width:4px;border-left-color:#D4B45C;padding:18px 22px;">
			<h3 style="margin:0 0 8px;font-size:1.2rem;">🚀 AG Starter Companion mis à jour : v<?php echo esc_html( AG_STARTER_COMPANION_VERSION ); ?> <small style="color:#888;">(depuis v<?php echo esc_html( $prev ); ?>)</small></h3>
			<p style="margin:0 0 6px;">Les pages, le menu et la page d'accueil de votre thème ont été <strong>réimportés automatiquement</strong> avec les dernières versions.</p>
			<p style="margin:0;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-starter-companion' ) ); ?>" class="button button-primary">Voir le tableau de bord</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" target="_blank">👁 Voir le site</a>
			</p>
		</div>
		<?php
		delete_transient( 'ag_companion_just_updated_from' );
	}

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
				if ( $slug_key === 'accueil' ) {
					continue;
				}
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $pages[ $slug_key ]['title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
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
	 * Nettoyage multi-themes : met a la corbeille les pages appartenant aux
	 * AUTRES themes (Coach, Barber...), supprime les menus parasites, puis
	 * reconstruit un menu propre pour le theme actif via run_import().
	 *
	 * Sert quand on a teste plusieurs templates sur une meme install et que
	 * pages/menus se sont melanges.
	 *
	 * @param string $slug Theme slug actif.
	 * @return array Journal des operations.
	 */
	public function run_cleanup( $slug ) {
		$log = array();
		$map = $this->get_theme_data_map();
		if ( ! isset( $map[ $slug ] ) ) {
			return $log;
		}

		// Slugs a conserver (pages du theme actif) + pages partagees utiles.
		$keep = array_map( 'sanitize_title', array_keys( $map[ $slug ]['pages'] ) );
		$keep = array_merge( $keep, array( 'contact' ) );

		// Slugs des autres themes, candidats a suppression.
		$foreign = array();
		foreach ( $map as $other_slug => $other_data ) {
			if ( $other_slug === $slug ) {
				continue;
			}
			foreach ( array_keys( $other_data['pages'] ) as $page_slug ) {
				$foreign[ sanitize_title( $page_slug ) ] = true;
			}
		}
		// Quelques orphelins connus (anciennes versions / pages manuelles).
		foreach ( array( 'modes-de-seance', 'le-salon', 'tarifs', 'nos-tarifs' ) as $orphan ) {
			$foreign[ $orphan ] = true;
		}
		// Ne jamais supprimer une page conservee.
		foreach ( $keep as $page_slug ) {
			unset( $foreign[ $page_slug ] );
		}

		// Mots-cles de titres typiques des AUTRES themes (coach/barber).
		// Assez specifiques pour ne pas toucher les pages business/legales.
		$title_kw = '/(accompagnement|séance|seance|salon|barber|coiffeur|témoignage|temoignage)/iu';

		// 1) Corbeille des pages d'autres themes (y compris les doublons
		//    "slug-2", "slug-3" et les pages reperees par leur titre).
		$pages = get_posts( array(
			'post_type'   => 'page',
			'numberposts' => -1,
			'post_status' => 'any',
			'suppress_filters' => true,
		) );
		foreach ( (array) $pages as $page ) {
			$base = preg_replace( '/-\d+$/', '', $page->post_name );
			if ( in_array( $base, $keep, true ) ) {
				continue; // page du theme actif / partagee -> on garde.
			}
			$is_foreign = isset( $foreign[ $base ] ) || (bool) preg_match( $title_kw, $page->post_title );
			if ( $is_foreign ) {
				wp_trash_post( $page->ID );
				/* translators: %s: page title. */
				$log[] = sprintf( __( 'Page d\'un autre theme mise a la corbeille : %s', 'ag-starter-companion' ), $page->post_title );
			}
		}

		// 2) Supprime les menus parasites (tout sauf "Menu principal AG",
		//    qui sera reconstruit juste apres pour le theme actif).
		$menus = wp_get_nav_menus();
		foreach ( (array) $menus as $menu ) {
			if ( 'Menu principal AG' !== $menu->name ) {
				wp_delete_nav_menu( $menu->term_id );
				/* translators: %s: menu name. */
				$log[] = sprintf( __( 'Menu parasite supprime : %s', 'ag-starter-companion' ), $menu->name );
			}
		}

		// 3) Reconstruit le menu propre du theme actif + reassignation.
		$log = array_merge( $log, $this->run_import( $slug ) );

		if ( empty( $log ) ) {
			$log[] = __( 'Rien a nettoyer : aucune page/menu d\'un autre theme trouve.', 'ag-starter-companion' );
		}

		return $log;
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
				. '<p>Oui, 100% gratuit. Le Premium est une option payante facultative.</p>'
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
		return $this->get_current_tier() === 'free';
	}

	private function get_current_tier() {
		if ( class_exists( 'AG_Licence_Client' ) ) {
			return AG_Licence_Client::get_tier();
		}
		return 'free';
	}

	private function has_higher_tier() {
		// Modèle 2 niveaux : Gratuit + Premium. TOUTE licence payante (premium,
		// pro ou business — anciennes clés incluses) = niveau Premium unique =
		// tout débloqué. Les cadenas/upsells ne s'affichent QUE pour le gratuit.
		return $this->is_free_tier();
	}

	/** Prix unique du Premium (modèle 2 niveaux : Gratuit + Premium). */
	private function get_premium_price() {
		return (int) apply_filters( 'ag_companion_premium_price', (int) get_option( 'ag_creator_price', 69 ) );
	}

	private function get_upgrade_buttons() {
		$buttons = array();
		// Une seule offre payante : Premium (interne 'business' = design abouti).
		// On ne propose l'upgrade qu'aux utilisateurs gratuits ; toute licence
		// payante est déjà au niveau Premium unique.
		if ( $this->is_free_tier() ) {
			$buttons['business'] = 'Premium — ' . $this->get_premium_price() . '€';
		}
		return $buttons;
	}

	/**
	 * URL d'une offre de service Alliance Groupe (client deja Premium).
	 * Pointe vers des pages reelles du site (sites-express / contact).
	 */
	private function get_service_url( $kind ) {
		$utm  = '?utm_source=wp-admin&utm_medium=ag-companion&utm_campaign=' . $kind;
		$urls = array(
			'maintenance' => 'https://alliancegroupe-inc.com/sites-express' . $utm . '#maintenance',
			'sur-mesure'  => 'https://alliancegroupe-inc.com/contact' . $utm . '&offre=sur-mesure',
		);
		$url = $urls[ $kind ] ?? 'https://alliancegroupe-inc.com/contact' . $utm;
		return apply_filters( 'ag_companion_service_url', $url, $kind );
	}

	/**
	 * Offre a proposer selon l'etat de la licence (modele 2 niveaux).
	 * - Gratuit  -> upsell vers Premium 69€.
	 * - Premium  -> services a plus forte valeur : maintenance + site sur-mesure.
	 * Retourne array( heading, text, buttons[ array(label,url,primary) ] ).
	 */
	private function get_next_offer() {
		if ( $this->is_free_tier() ) {
			return array(
				'heading' => 'Passez a la version Premium',
				'text'    => 'Header sticky, animations scroll, couleurs avancees, temoignages clients, galerie photos, boutique WooCommerce, grille de tarifs, pub minimale... Paiement unique, mises a jour a vie.',
				'buttons' => array(
					array(
						'label'   => 'Premium — ' . $this->get_premium_price() . '€',
						'url'     => $this->get_upgrade_url( 'business' ),
						'primary' => true,
					),
				),
			);
		}

		// Client deja Premium : on ne reproppose plus le Premium, on propose
		// des services complementaires a plus forte valeur.
		return array(
			'heading' => 'Allez plus loin avec Alliance Groupe',
			'text'    => 'Votre Premium est actif, merci ! Gardez votre site rapide, securise et a jour avec la maintenance pro, ou passez a un site 100% sur-mesure concu pour convertir davantage de clients.',
			'buttons' => array(
				array(
					'label'   => 'Maintenance — des 29€/mois',
					'url'     => $this->get_service_url( 'maintenance' ),
					'primary' => true,
				),
				array(
					'label'   => 'Site sur-mesure',
					'url'     => $this->get_service_url( 'sur-mesure' ),
					'primary' => false,
				),
			),
		);
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

		return array( 'premium' => '', 'business' => '' );
	}

	/**
	 * Big upgrade banner on all admin pages.
	 */
	public function upgrade_banner() {
		if ( ! $this->get_active_theme_slug() ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;
		$done = get_option( 'ag_starter_companion_done_' . $this->get_active_theme_slug() );
		if ( ! $done ) return; // Show companion install notice first

		// Cle de masquage distincte selon l'offre, pour que le passage
		// gratuit -> premium ne reste pas masque par un ancien rejet.
		$dismiss_key = $this->is_free_tier() ? 'ag_upgrade_dismissed' : 'ag_services_dismissed';

		if ( isset( $_GET['ag_dismiss_upgrade'] ) && '1' === $_GET['ag_dismiss_upgrade'] ) {
			update_user_meta( get_current_user_id(), $dismiss_key, time() );
			return;
		}
		$dismissed = get_user_meta( get_current_user_id(), $dismiss_key, true );
		if ( $dismissed && ( time() - intval( $dismissed ) ) < 7 * DAY_IN_SECONDS ) return;

		$offer       = $this->get_next_offer();
		$dismiss_url = add_query_arg( 'ag_dismiss_upgrade', '1' );
		$icon        = $this->is_free_tier() ? '⚡' : '💎';
		?>
		<div style="background:linear-gradient(135deg,#1a1a2e 0%,#0f0f18 100%);border:1px solid rgba(212,180,92,.35);border-radius:10px;padding:28px 32px;margin:20px 20px 10px 0;display:flex;align-items:center;gap:28px;flex-wrap:wrap;position:relative;">
			<a href="<?php echo esc_url( $dismiss_url ); ?>" style="position:absolute;top:10px;right:14px;color:rgba(255,255,255,.3);font-size:1.2rem;text-decoration:none;" title="Masquer 7 jours">✕</a>
			<div style="flex:1;min-width:260px;">
				<h2 style="color:#D4B45C;font-size:1.3rem;margin:0 0 8px;font-weight:800;"><?php echo esc_html( $icon . ' ' . $offer['heading'] ); ?></h2>
				<p style="color:rgba(255,255,255,.75);font-size:.95rem;line-height:1.6;margin:0;">
					<?php echo esc_html( $offer['text'] ); ?>
				</p>
			</div>
			<div style="display:flex;gap:10px;flex-wrap:wrap;">
				<?php foreach ( $offer['buttons'] as $btn ) : ?>
				<a href="<?php echo esc_url( $btn['url'] ); ?>" target="_blank" rel="noopener" style="display:inline-block;<?php echo ! empty( $btn['primary'] ) ? 'background:#D4B45C;color:#0a0a0f;' : 'background:rgba(212,180,92,.15);color:#D4B45C;border:1px solid rgba(212,180,92,.4);'; ?>font-weight:700;padding:14px 22px;border-radius:8px;text-decoration:none;font-size:.9rem;white-space:nowrap;"><?php echo esc_html( $btn['label'] ); ?> →</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Dashboard widget with upgrade CTA.
	 */
	public function upgrade_dashboard_widget() {
		if ( ! $this->get_active_theme_slug() ) return;
		$title = $this->is_free_tier()
			? '⭐ ' . esc_html__( 'Passer au niveau superieur', 'ag-starter-companion' )
			: '💎 ' . esc_html__( 'Aller plus loin avec Alliance Groupe', 'ag-starter-companion' );
		wp_add_dashboard_widget(
			'ag_upgrade_widget',
			$title,
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
		$offer  = $this->get_next_offer();
		$is_free = $this->is_free_tier();
		?>
		<div style="text-align:center;padding:16px 0;">
			<div style="font-size:2.4rem;margin-bottom:12px;"><?php echo $is_free ? '🚀' : '💎'; ?></div>
			<h3 style="margin:0 0 10px;font-size:1.1rem;"><?php echo $is_free ? 'Vous utilisez la version gratuite' : 'Votre Premium est actif ✔'; ?></h3>

			<div style="background:#f8f5ec;border:1px solid #D4B45C;border-radius:8px;padding:18px;margin:0 0 16px;text-align:left;">
				<strong style="font-size:1rem;display:block;text-align:center;margin-bottom:6px;"><?php echo esc_html( $offer['heading'] ); ?></strong>
				<p style="font-size:.82rem;color:#555;margin:0;line-height:1.5;"><?php echo esc_html( $offer['text'] ); ?></p>
			</div>
			<div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
				<?php foreach ( $offer['buttons'] as $btn ) : ?>
				<a href="<?php echo esc_url( $btn['url'] ); ?>" target="_blank" rel="noopener" class="button <?php echo ! empty( $btn['primary'] ) ? 'button-primary' : ''; ?>" style="font-size:.85rem;padding:6px 14px;"><?php echo esc_html( $btn['label'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Locked section in the Customizer.
	 */
	public function upgrade_customizer_section( $wp_customize ) {
		if ( ! $this->get_active_theme_slug() || ! $this->has_higher_tier() ) return;

		$wp_customize->add_section( 'ag_locked_pro', array(
			'title'       => esc_html__( '🔒 Header Sticky + Couleurs (Premium)', 'ag-starter-companion' ),
			'priority'    => 30,
			'description' => esc_html__( 'Le header sticky au scroll, la couleur d\'accent secondaire et le fond de footer personnalisable sont disponibles avec le Premium (69€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_pro_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_pro_info', array(
			'label'       => esc_html__( 'A partir de 69€', 'ag-starter-companion' ),
			'section'     => 'ag_locked_pro',
			'type'        => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'business' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Passer au Premium — 69€ →</a>',
		) );

		$wp_customize->add_section( 'ag_locked_animations', array(
			'title'       => esc_html__( '🔒 Animations au scroll (Premium)', 'ag-starter-companion' ),
			'priority'    => 31,
			'description' => esc_html__( 'Animations fade-in, slide-left, slide-right et scale-in au scroll. Vos sections apparaissent avec elegance quand le visiteur scrolle. Compatible prefers-reduced-motion. Premium (69€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_anim_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_anim_info', array(
			'label'   => esc_html__( 'A partir de 69€', 'ag-starter-companion' ),
			'section' => 'ag_locked_animations',
			'type'    => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'business' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Passer au Premium — 69€ →</a>',
		) );

		$wp_customize->add_section( 'ag_locked_testimonials', array(
			'title'       => esc_html__( '🔒 Temoignages + Galerie (Premium)', 'ag-starter-companion' ),
			'priority'    => 32,
			'description' => esc_html__( 'Temoignages clients (jusqu\'a 6), galerie photos, grille de tarifs. Inclus dans le Premium (69€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_testi_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_testi_info', array(
			'label'   => esc_html__( 'A partir de 69€', 'ag-starter-companion' ),
			'section' => 'ag_locked_testimonials',
			'type'    => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'business' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Passer au Premium — 69€ →</a>',
		) );

		$wp_customize->add_section( 'ag_locked_whitelabel', array(
			'title'       => esc_html__( '🔒 Pub minimale + Session strategique (Premium)', 'ag-starter-companion' ),
			'priority'    => 33,
			'description' => esc_html__( 'Reduisez la publicite Alliance Groupe a un simple copyright, personnalisez le footer, acces a des templates de pages supplementaires, et session strategique de 30 min offerte avec un expert pour optimiser votre site. Premium (69€).', 'ag-starter-companion' ),
		) );
		$wp_customize->add_setting( 'ag_locked_wl_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'ag_locked_wl_info', array(
			'label'   => esc_html__( 'A partir de 69€', 'ag-starter-companion' ),
			'section' => 'ag_locked_whitelabel',
			'type'    => 'hidden',
			'description' => '<a href="' . esc_url( $this->get_upgrade_url( 'business' ) ) . '" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Passer au Premium — 69€ →</a>',
		) );
	}

	/**
	 * Subtle footer nudge on every admin page.
	 */
	public function upgrade_footer_nudge() {
		if ( ! $this->get_active_theme_slug() || ! $this->has_higher_tier() ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;
		$buttons = $this->get_upgrade_buttons();
		if ( empty( $buttons ) ) return;
		$tier = $this->get_current_tier();
		$tier_labels = array( 'free' => 'Version gratuite', 'pro' => 'Pack Premium' );
		$label = $tier_labels[ $tier ] ?? 'Version actuelle';
		?>
		<div style="position:fixed;bottom:0;left:0;right:0;background:rgba(10,10,15,.97);border-top:2px solid #D4B45C;padding:12px 24px;z-index:9999;font-size:.85rem;" id="ag-footer-nudge">
			<div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
				<span style="color:rgba(255,255,255,.7);">⚡ <?php echo esc_html( $label ); ?> —</span>
				<?php foreach ( $buttons as $pack => $text ) :
					$is_first = ( $pack === array_key_first( $buttons ) );
				?>
				<a href="<?php echo esc_url( $this->get_upgrade_url( $pack ) ); ?>" target="_blank" rel="noopener" style="<?php echo $is_first ? 'background:#D4B45C;color:#0a0a0f;' : 'background:rgba(212,180,92,.15);color:#D4B45C;border:1px solid rgba(212,180,92,.4);'; ?>font-weight:700;padding:6px 16px;border-radius:6px;text-decoration:none;font-size:.82rem;"><?php echo esc_html( $text ); ?></a>
				<?php endforeach; ?>
				<a href="#" onclick="document.getElementById('ag-footer-nudge').style.display='none';return false;" style="color:rgba(255,255,255,.3);font-size:1rem;text-decoration:none;margin-left:6px;">✕</a>
			</div>
		</div>
		<?php
	}

	// ═══════════════════════════════════════════════════════════════
	// PERSONNALISATION PREMIUM (Alliance) — image de hero + effets,
	// communs a TOUS les themes AG Starter. Perk reserve aux licences
	// payantes (Premium) : un client Premium peut tout changer.
	// ═══════════════════════════════════════════════════════════════

	/** Cle d'image de hero native du theme actif (si elle existe). */
	private function premium_hero_native_key() {
		$map = array(
			'ag-starter-artisan'    => 'ag_artisan_hero_image',
			'ag-starter-coach'      => 'ag_coach_hero_image',
			'ag-starter-restaurant' => 'ag_restaurant_hero_image',
		);
		return $map[ $this->get_active_theme_slug() ] ?? '';
	}

	/** Image de hero effective : override premium sinon image native du theme. */
	private function premium_hero_image() {
		$img = get_theme_mod( 'ag_premium_hero_image', '' );
		if ( ! $img && ( $native = $this->premium_hero_native_key() ) ) {
			$img = get_theme_mod( $native, '' );
		}
		return $img;
	}

	public function sanitize_hero_height( $v ) {
		return in_array( $v, array( '', 'tall', 'full' ), true ) ? $v : '';
	}

	/**
	 * Panneau Customizer « Personnalisation Premium ».
	 */
	public function premium_personalization_register( $wp_customize ) {
		if ( ! $this->get_active_theme_slug() || $this->is_free_tier() ) return;

		$wp_customize->add_panel( 'ag_premium_panel', array(
			'title'       => '🎨 ' . esc_html__( 'Personnalisation Premium', 'ag-starter-companion' ),
			'priority'    => 1,
			'description' => esc_html__( 'Changez l\'image du hero et activez les effets visuels haut de gamme inspires du site Alliance Groupe. Inclus dans votre Premium.', 'ag-starter-companion' ),
		) );

		// --- Hero ---
		$wp_customize->add_section( 'ag_premium_hero', array(
			'title' => esc_html__( 'Image du hero', 'ag-starter-companion' ), 'panel' => 'ag_premium_panel', 'priority' => 1,
		) );
		$wp_customize->add_setting( 'ag_premium_hero_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' ) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'ag_premium_hero_image', array(
			'label'       => esc_html__( 'Image de fond du hero', 'ag-starter-companion' ),
			'section'     => 'ag_premium_hero',
			'description' => esc_html__( 'Remplacez la grande photo en haut de page. Format paysage conseille (1600px de large minimum).', 'ag-starter-companion' ),
		) ) );
		$wp_customize->add_setting( 'ag_premium_hero_overlay', array( 'default' => 45, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
		$wp_customize->add_control( 'ag_premium_hero_overlay', array(
			'label'       => esc_html__( 'Assombrir l\'image (lisibilite du texte)', 'ag-starter-companion' ),
			'section'     => 'ag_premium_hero', 'type' => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 85, 'step' => 5 ),
		) );
		$wp_customize->add_setting( 'ag_premium_hero_height', array( 'default' => '', 'sanitize_callback' => array( $this, 'sanitize_hero_height' ), 'transport' => 'refresh' ) );
		$wp_customize->add_control( 'ag_premium_hero_height', array(
			'label'   => esc_html__( 'Hauteur du hero', 'ag-starter-companion' ),
			'section' => 'ag_premium_hero', 'type' => 'select',
			'choices' => array( '' => esc_html__( 'Par defaut du theme', 'ag-starter-companion' ), 'tall' => esc_html__( 'Grand', 'ag-starter-companion' ), 'full' => esc_html__( 'Plein ecran', 'ag-starter-companion' ) ),
		) );

		// --- Effets ---
		$wp_customize->add_section( 'ag_premium_fx', array(
			'title'       => esc_html__( 'Effets Premium', 'ag-starter-companion' ), 'panel' => 'ag_premium_panel', 'priority' => 2,
			'description' => esc_html__( 'Touches visuelles haut de gamme. Compatibles « mouvement reduit » pour l\'accessibilite.', 'ag-starter-companion' ),
		) );
		$fx = array(
			'ag_premium_fx_reveal'   => array( 'Apparition des sections au scroll', true ),
			'ag_premium_fx_glass'    => array( 'Header verre depoli au defilement', true ),
			'ag_premium_fx_hover'    => array( 'Cartes qui se soulevent au survol', true ),
			'ag_premium_fx_smooth'   => array( 'Defilement fluide + barre de progression doree', true ),
			'ag_premium_fx_gradient' => array( 'Halo dore anime sur le hero', true ),
			'ag_premium_fx_grain'    => array( 'Grain cinematique subtil', false ),
		);
		foreach ( $fx as $key => $cfg ) {
			$wp_customize->add_setting( $key, array( 'default' => $cfg[1], 'sanitize_callback' => 'wp_validate_boolean', 'transport' => 'refresh' ) );
			$wp_customize->add_control( $key, array( 'label' => esc_html( $cfg[0] ), 'section' => 'ag_premium_fx', 'type' => 'checkbox' ) );
		}
	}

	/**
	 * CSS premium injecte sur le front (image de hero + effets).
	 */
	public function premium_personalization_css() {
		if ( ! $this->get_active_theme_slug() || $this->is_free_tier() ) return;

		$img     = $this->premium_hero_image();
		$overlay = max( 0, min( 85, (int) get_theme_mod( 'ag_premium_hero_overlay', 45 ) ) ) / 100;
		$height  = get_theme_mod( 'ag_premium_hero_height', '' );
		$css     = '';

		if ( $img ) {
			$o1   = number_format( $overlay, 2, '.', '' );
			$o2   = number_format( min( 0.92, $overlay + 0.12 ), 2, '.', '' );
			$url  = esc_url( $img );
			$css .= '.ag-hero,.ag-hero-pro{background-image:linear-gradient(rgba(8,8,12,' . $o1 . '),rgba(8,8,12,' . $o2 . ')),url("' . $url . '") !important;background-size:cover !important;background-position:center !important;background-repeat:no-repeat !important;}';
			$css .= '.ag-hero__bg{background-image:url("' . $url . '") !important;background-size:cover !important;background-position:center !important;}';
			// Lisibilite premium sur photo : texte clair + ombre douce.
			$css .= '.ag-hero h1,.ag-hero h2,.ag-hero p,.ag-hero .ag-hero__title,.ag-hero .ag-hero__subtitle,.ag-hero-pro__title,.ag-hero-pro__subtitle{color:#fff !important;text-shadow:0 2px 20px rgba(0,0,0,.5);}';
			if ( '' === $height ) $css .= '.ag-hero,.ag-hero-pro{min-height:62vh;}';
		}
		if ( 'tall' === $height ) $css .= '.ag-hero,.ag-hero-pro{min-height:78vh !important;}';
		if ( 'full' === $height ) $css .= '.ag-hero,.ag-hero-pro{min-height:100vh !important;}';

		if ( get_theme_mod( 'ag_premium_fx_gradient', true ) ) {
			$css .= '.ag-hero,.ag-hero-pro{position:relative;overflow:hidden;}.ag-hero::after,.ag-hero-pro::after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 72% 18%,rgba(212,180,92,.20),transparent 58%);pointer-events:none;z-index:1;animation:agSheen 7s ease-in-out infinite;}.ag-hero>*,.ag-hero-pro>*{position:relative;z-index:2;}@keyframes agSheen{0%,100%{opacity:.5}50%{opacity:1}}';
		}
		if ( get_theme_mod( 'ag_premium_fx_hover', true ) ) {
			$css .= '[class*="-card"]{transition:transform .28s cubic-bezier(.2,.7,.2,1),box-shadow .28s;}[class*="-card"]:hover{transform:translateY(-6px);box-shadow:0 20px 44px rgba(0,0,0,.16);}';
		}
		if ( get_theme_mod( 'ag_premium_fx_glass', true ) ) {
			$css .= '.ag-site-header,.ag-header,.site-header{transition:background .3s,backdrop-filter .3s,box-shadow .3s;}body.ag-scrolled .ag-site-header,body.ag-scrolled .ag-header,body.ag-scrolled .site-header{backdrop-filter:blur(12px) saturate(150%);-webkit-backdrop-filter:blur(12px) saturate(150%);background:rgba(12,12,18,.72) !important;box-shadow:0 6px 30px rgba(0,0,0,.25);}';
		}
		if ( get_theme_mod( 'ag_premium_fx_reveal', true ) ) {
			$css .= '@media(prefers-reduced-motion:no-preference){.ag-reveal{opacity:0;transform:translateY(26px);transition:opacity .7s ease,transform .7s cubic-bezier(.2,.7,.2,1);}.ag-reveal.ag-in{opacity:1;transform:none;}}';
		}
		if ( get_theme_mod( 'ag_premium_fx_smooth', true ) ) {
			$css .= 'html{scroll-behavior:smooth;}#ag-scroll-progress{position:fixed;top:0;left:0;height:3px;width:0;background:linear-gradient(90deg,#D4B45C,#f1d98b);z-index:99999;transition:width .1s ease;}';
		}
		if ( get_theme_mod( 'ag_premium_fx_grain', false ) ) {
			$css .= 'body::before{content:"";position:fixed;inset:0;z-index:9998;pointer-events:none;opacity:.05;background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Cfilter id=\'n\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'.9\' numOctaves=\'2\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23n)\'/%3E%3C/svg%3E");}';
		}

		if ( $css ) {
			echo "\n<style id=\"ag-premium-personalization\">" . $css . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	/**
	 * JS premium (header glass, reveal au scroll, barre de progression).
	 */
	public function premium_personalization_js() {
		if ( ! $this->get_active_theme_slug() || $this->is_free_tier() ) return;
		$reveal = get_theme_mod( 'ag_premium_fx_reveal', true ) ? 1 : 0;
		$glass  = get_theme_mod( 'ag_premium_fx_glass', true ) ? 1 : 0;
		$smooth = get_theme_mod( 'ag_premium_fx_smooth', true ) ? 1 : 0;
		if ( ! $reveal && ! $glass && ! $smooth ) return;
		?>
		<script id="ag-premium-personalization-js">
		(function(){
			var R=<?php echo (int) $reveal; ?>,G=<?php echo (int) $glass; ?>,S=<?php echo (int) $smooth; ?>;
			var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion:reduce)').matches;
			if(G){var on=function(){document.body.classList.toggle('ag-scrolled',window.scrollY>40);};window.addEventListener('scroll',on,{passive:true});on();}
			if(S){var bar=document.createElement('div');bar.id='ag-scroll-progress';document.body.appendChild(bar);var up=function(){var h=document.documentElement,sc=h.scrollHeight-h.clientHeight;bar.style.width=(sc>0?(h.scrollTop/sc*100):0)+'%';};window.addEventListener('scroll',up,{passive:true});window.addEventListener('resize',up);up();}
			if(R&&!reduce&&'IntersectionObserver'in window){
				var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('ag-in');io.unobserve(e.target);}});},{threshold:.12});
				document.querySelectorAll('section:not(.ag-hero):not(.ag-hero-pro)').forEach(function(s){s.classList.add('ag-reveal');io.observe(s);});
			}
		})();
		</script>
		<?php
	}

	/**
	 * Patch the active theme with missing files (licence client, updater, pro-features).
	 * Runs once per companion version bump. Downloads files from GitHub if missing locally.
	 */
	public function maybe_patch_theme() {
		if ( $this->is_maintenance_context() ) {
			return; // ne JAMAIS télécharger/patcher pendant une MAJ : bloquerait l'updater
		}
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

		$gh_base = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/' . $slug . '/inc/';

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
					"// ── AG Licence & Premium (added by AG Starter Companion " . AG_STARTER_COMPANION_VERSION . ") ──\n" .
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
