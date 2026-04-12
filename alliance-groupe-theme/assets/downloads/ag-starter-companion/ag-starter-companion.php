<?php
/**
 * Plugin Name:       AG Starter Companion
 * Plugin URI:        https://alliancegroupe-inc.com/templates-wordpress
 * Description:       Importer un clic pour les themes AG Starter (Restaurant, Artisan, Coach). Cree automatiquement les pages, le menu et les reglages pour un site pret a l'emploi.
 * Version:           1.0.0
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

define( 'AG_STARTER_COMPANION_VERSION', '1.0.0' );
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
		$supported = array( 'ag-starter-restaurant', 'ag-starter-artisan', 'ag-starter-coach' );
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

		if ( '' === get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
			flush_rewrite_rules();
			$log[] = 'Permaliens actives';
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
	}
}

new AG_Starter_Companion();
