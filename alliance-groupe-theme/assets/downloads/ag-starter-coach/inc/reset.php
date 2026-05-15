<?php
/**
 * Reinitialisation du theme — page admin avec bouton "TOUT RESET" qui nettoie
 * tous les residus d'autres templates Alliance Groupe (association/avocat/
 * barber/coach/resto) que l'utilisateur peut avoir laisse en switchant entre
 * templates pour les configurer : menus avec items "Petitions"/"Combats"/
 * "Manifeste", CPT ag_combat/ag_evenement/ag_petition/ag_groupe/ag_pv/
 * ag_domaine encore presents en base, theme_mods d'autres prefixes, pages
 * "Manifeste"/"Petitions"/etc.
 *
 * Apres reset : seules les options ag_coach_*, les pages artisan
 * (prestations, zones-intervention, realisations, qui-sommes-nous, mentions,
 * contact) et le menu primary artisan-only subsistent.
 *
 * Apparait dans : "Apparence > 🔄 Reinitialiser le theme".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ag_coach_Reset {

	/**
	 * Prefixes theme_mod d'autres templates Alliance Groupe — supprimes
	 * lors du reset (laisse ag_coach_*, background_color, blogname, etc.).
	 */
	const FOREIGN_MOD_PREFIXES = array( 'ag_asso_', 'ag_avocat_', 'ag_barber_', 'ag_artisan_', 'ag_resto_', 'ag_fid_' );

	/**
	 * CPT d'autres templates a wipe completement (pas de raison d'avoir
	 * des combats ou des domaines avocat sur un site artisan).
	 */
	const FOREIGN_CPT = array( 'ag_combat', 'ag_evenement', 'ag_groupe', 'ag_petition', 'ag_pv', 'ag_domaine' );

	/**
	 * Slugs de pages connus comme appartenant a d'autres templates.
	 * Toute page WP avec un de ces slugs sera supprimee au reset.
	 */
	const FOREIGN_PAGE_SLUGS = array(
		// Pack Fidelite Association
		'manifeste', 'combats', 'evenements', 'groupes', 'actu', 'signer',
		'don', 'adherer', 'mon-compte', 'petitions', 'reunion', 'rendez-vous',
		'rejoindre-lfi',
		// Avocat / Barber / Resto / Artisan
		'domaines', 'honoraires', 'cabinet', 'reservation', 'carte', 'menu',
		'tarifs-coach', 'seances',
		// Pages du template coach BASIQUE d'origine — à remplacer par les pages premium
		'mes-accompagnements', 'temoignages', 'a-propos',
	);

	/**
	 * Pages que le template artisan recree systematiquement.
	 * slug => array( title, content_placeholder )
	 */
	const ARTISAN_PAGES = array(
		'prestations'        => array( 'title' => 'Prestations',          'content' => 'Nos prestations principales : rénovation, installation, entretien. Devis gratuit sous 24h.' ),
		'zones-intervention' => array( 'title' => "Zones d'intervention", 'content' => 'Nous intervenons dans toute votre région, y compris en urgence.' ),
		'realisations'       => array( 'title' => 'Réalisations',         'content' => 'Découvrez nos chantiers récents : rénovations, installations techniques, travaux sur-mesure.' ),
		'qui-sommes-nous'    => array( 'title' => 'Qui sommes-nous',      'content' => "Depuis plus de dix ans, notre équipe d'artisans qualifiés accompagne particuliers et professionnels." ),
		'devis'              => array( 'title' => 'Devis en ligne',       'content' => '[ag_coach_devis]' ),
		'mentions'           => array( 'title' => 'Mentions légales',     'content' => 'Mentions légales et informations sur l\'entreprise.' ),
		'contact'            => array( 'title' => 'Contact',              'content' => 'Pour toute demande de devis ou de renseignement, contactez-nous.' ),
	);

	/**
	 * Version de l'installer — incrémenter pour forcer une réinstall sur tous
	 * les sites qui ont déjà l'ancien menu/pages. Stocké dans option
	 * ag_coach_installer_version.
	 */
	const INSTALLER_VERSION = 2;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 30 );
		add_action( 'admin_post_ag_coach_reset', array( __CLASS__, 'handle_reset' ) );
		add_action( 'admin_post_ag_coach_sync_reset', array( __CLASS__, 'handle_sync_reset' ) );
		// Auto-install au switch_theme (activation initiale du thème).
		add_action( 'after_switch_theme', array( __CLASS__, 'auto_install_pages' ) );
		// Auto-install au upgrader (mise à jour du thème via GitHub).
		add_action( 'upgrader_process_complete', array( __CLASS__, 'on_upgrade' ), 10, 2 );
		// Filet de sécurité : à chaque `init` (front + admin), si la version
		// d'installer en base < version courante, on relance. 1 SQL get_option par hit.
		add_action( 'init', array( __CLASS__, 'maybe_install' ), 99 );
	}

	public static function auto_install_pages() {
		self::install_pages_and_menu();
		update_option( 'ag_coach_installer_version', self::INSTALLER_VERSION );
	}

	public static function on_upgrade( $upgrader, $hook_extra ) {
		if ( empty( $hook_extra['type'] ) || 'theme' !== $hook_extra['type'] ) return;
		if ( empty( $hook_extra['themes'] ) ) return;
		if ( ! in_array( 'ag-starter-coach', (array) $hook_extra['themes'], true ) ) return;
		// Reset le flag pour forcer maybe_install à relancer au prochain init.
		delete_option( 'ag_coach_installer_version' );
	}

	/**
	 * Lance install_pages_and_menu si :
	 *  - le flag d'installer en base est < à la version compilée, OU
	 *  - la page /devis/ n'existe pas (filet contre tout cas exotique).
	 * Le flag est ensuite mis à jour pour ne plus rejouer.
	 */
	public static function maybe_install() {
		// Ne pas tourner pendant cron/ajax/REST front non-authentifié pour pas
		// ralentir les visiteurs (sera rejoué à la prochaine vraie pageview admin).
		if ( wp_doing_cron() || wp_doing_ajax() ) return;

		$stored = (int) get_option( 'ag_coach_installer_version', 0 );
		$needs  = ( $stored < self::INSTALLER_VERSION );

		// Vérif renforcée : si la page devis manque, on installe de toute façon
		if ( ! $needs ) {
			if ( ! get_page_by_path( 'devis' ) ) $needs = true;
		}

		if ( ! $needs ) return;

		self::install_pages_and_menu();
		update_option( 'ag_coach_installer_version', self::INSTALLER_VERSION );
	}

	/**
	 * Crée les pages coach manquantes ET reconstruit le menu primary.
	 * NE PURGE PAS les mods/CPT/pages d'autres templates (différence avec do_reset_logic).
	 * @return array stats
	 */
	public static function install_pages_and_menu() {
		$stats = array( 'pages_created' => 0 );
		$created_page_ids = array();
		foreach ( self::ARTISAN_PAGES as $slug => $data ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$content = trim( $data['content'] );
				if ( preg_match( '/^\[\w+/', $content ) ) {
					$post_content = "<!-- wp:shortcode -->\n" . $content . "\n<!-- /wp:shortcode -->";
				} else {
					$post_content = '<!-- wp:paragraph --><p>' . esc_html( $content ) . '</p><!-- /wp:paragraph -->';
				}
				$new_id = wp_insert_post( array(
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					$stats['pages_created']++;
					$created_page_ids[ $slug ] = $new_id;
				}
			} else {
				$created_page_ids[ $slug ] = $page->ID;
				// Si page existe mais contenu vide ou shortcode echappé, on le repare
				if ( '[ag_coach_devis]' === trim( $data['content'] ) ) {
					$current = get_post_field( 'post_content', $page->ID );
					if ( false === strpos( $current, 'wp:shortcode' ) && false === strpos( $current, '[ag_coach_devis]' ) ) {
						wp_update_post( array(
							'ID'           => $page->ID,
							'post_content' => "<!-- wp:shortcode -->\n[ag_coach_devis]\n<!-- /wp:shortcode -->",
						) );
					}
				}
			}
		}

		// Reconstruire le menu primary
		$menu_name = 'AG Coach — Principal';
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
			$menu_id = $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $menu_name );
		}
		if ( ! is_wp_error( $menu_id ) ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => 'Accueil',
				'menu-item-url'    => home_url( '/' ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			) );
			foreach ( array( 'prestations', 'zones-intervention', 'realisations', 'qui-sommes-nous', 'devis', 'contact' ) as $slug ) {
				if ( empty( $created_page_ids[ $slug ] ) ) continue;
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => self::ARTISAN_PAGES[ $slug ]['title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $created_page_ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
			$locations = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		return $stats;
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Réinitialiser le thème', 'ag-starter-coach' ),
			'🔄 ' . __( 'Réinitialiser', 'ag-starter-coach' ),
			'manage_options',
			'ag-coach-reset',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$done      = isset( $_GET['reset_done'] ) ? (int) $_GET['reset_done'] : 0;
		$sync_done = isset( $_GET['sync_done'] ) ? (int) $_GET['sync_done'] : 0;
		$stats     = ( $done || $sync_done ) ? get_transient( 'ag_coach_reset_stats' ) : null;
		?>
		<div class="wrap">
			<h1>🔄 <?php esc_html_e( 'Réinitialiser le thème — AG Starter Coach', 'ag-starter-coach' ); ?></h1>

			<!-- BOUTON TOUT-EN-UN : sync GitHub + companion + reset -->
			<div style="background:linear-gradient(135deg,#0a0a0f 0%,#1a1a2e 100%);border-radius:12px;padding:28px 32px;margin:20px 0;color:#fff;max-width:820px;">
				<h2 style="margin:0 0 10px;color:#fff;font-size:1.3em;">🚀 <?php esc_html_e( 'Tout faire en 1 clic', 'ag-starter-coach' ); ?></h2>
				<p style="color:rgba(255,255,255,.8);margin:0 0 18px;line-height:1.55;"><?php esc_html_e( 'Force la vérification des mises à jour GitHub (theme + companion), purge tous les caches (LiteSpeed, transients WP, mises à jour) ET nettoie les résidus d\'autres templates. Idéal après modification du repo Alliance Groupe.', 'ag-starter-coach' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Lancer le combo SYNC GITHUB + RESET ? Tous les caches seront purges et les residus supprimes.');">
					<input type="hidden" name="action" value="ag_coach_sync_reset" />
					<?php wp_nonce_field( 'ag_coach_sync_reset' ); ?>
					<button type="submit" class="button button-primary button-hero" style="background:#F37A1F;border-color:#F37A1F;color:#fff;font-weight:700;padding:0 30px;">
						🚀 <?php esc_html_e( 'SYNC GITHUB + RESET COMPLET', 'ag-starter-coach' ); ?>
					</button>
				</form>
			</div>
			<hr style="margin:30px 0;border:none;border-top:1px solid #ddd;">
			<h2><?php esc_html_e( 'Ou : reset seul (sans sync GitHub)', 'ag-starter-coach' ); ?></h2>

			<?php if ( ( $done || $sync_done ) && $stats ) : ?>
				<div class="notice notice-success">
					<p><strong>✅ <?php echo $sync_done ? esc_html__( 'Sync GitHub + reset complet effectués.', 'ag-starter-coach' ) : esc_html__( 'Réinitialisation effectuée.', 'ag-starter-coach' ); ?></strong></p>
					<?php if ( $sync_done ) : ?>
						<p>🔄 <?php esc_html_e( 'Caches mises à jour purgés (theme + companion + LiteSpeed). Si une nouvelle version est disponible, elle apparaîtra dans', 'ag-starter-coach' ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>"><?php esc_html_e( 'Apparence > Thèmes', 'ag-starter-coach' ); ?></a>.</p>
					<?php endif; ?>
					<ul style="margin-left:20px;list-style:disc;">
						<li><?php printf( esc_html__( '%d options Customizer d\'autres templates supprimées', 'ag-starter-coach' ), (int) $stats['mods_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d entrées CPT (combats/événements/pétitions/etc.) supprimées', 'ag-starter-coach' ), (int) $stats['cpt_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d pages d\'autres templates supprimées', 'ag-starter-coach' ), (int) $stats['pages_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d pages artisan créées/restaurées', 'ag-starter-coach' ), (int) $stats['pages_created'] ); ?></li>
						<li><?php esc_html_e( 'Menu principal reconstruit (6 items artisan)', 'ag-starter-coach' ); ?></li>
					</ul>
				</div>
			<?php endif; ?>

			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #d63638;padding:20px 24px;margin:20px 0;max-width:820px;">
				<h2 style="margin-top:0;color:#d63638;">⚠️ <?php esc_html_e( 'Action destructive', 'ag-starter-coach' ); ?></h2>
				<p><?php esc_html_e( 'Ce bouton supprime tous les résidus laissés par d\'autres templates Alliance Groupe (Association, Avocat, Barber, Coach, Restaurant) que vous avez pu activer précédemment :', 'ag-starter-coach' ); ?></p>
				<ul style="margin-left:24px;list-style:disc;">
					<li><?php esc_html_e( 'Options Customizer aux préfixes ag_asso_*, ag_avocat_*, ag_barber_*, ag_coach_*, ag_resto_*, ag_fid_*', 'ag-starter-coach' ); ?></li>
					<li><?php esc_html_e( 'Tous les CPT : combats, événements, groupes, pétitions, PV, domaines avocat', 'ag-starter-coach' ); ?></li>
					<li><?php esc_html_e( 'Pages WP : Manifeste, Combats, Événements, Pétitions, Don, Adhérer, Domaines, Honoraires, Carte, Réservation, etc.', 'ag-starter-coach' ); ?></li>
					<li><?php esc_html_e( 'Menu principal : reconstruit avec uniquement les items artisan', 'ag-starter-coach' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'Ce qui est conservé :', 'ag-starter-coach' ); ?></strong> <?php esc_html_e( 'vos options ag_coach_*, vos pages artisan (Prestations, Zones, Réalisations, Qui sommes-nous, Mentions, Contact), vos articles de blog, vos utilisateurs, vos plugins.', 'ag-starter-coach' ); ?></p>
				<p><?php esc_html_e( 'Si une page artisan manquait, elle est recréée avec un contenu placeholder.', 'ag-starter-coach' ); ?></p>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Confirmer la réinitialisation ? Tous les contenus d\'autres templates seront supprimés définitivement.');">
				<input type="hidden" name="action" value="ag_coach_reset" />
				<?php wp_nonce_field( 'ag_coach_reset' ); ?>
				<p>
					<label>
						<input type="checkbox" name="ag_coach_reset_confirm" value="1" required />
						<?php esc_html_e( 'J\'ai compris — supprimer définitivement tous les résidus d\'autres templates.', 'ag-starter-coach' ); ?>
					</label>
				</p>
				<p>
					<button type="submit" class="button button-primary button-hero" style="background:#d63638;border-color:#d63638;">
						🔄 <?php esc_html_e( 'TOUT RESET — remettre le thème à l\'état d\'origine', 'ag-starter-coach' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Combo : force-check GitHub theme + companion + purge transients +
	 * reset complet + purge LiteSpeed. Tout en 1 clic.
	 */
	public static function handle_sync_reset() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_coach_sync_reset' );

		// 1. Force-check GitHub theme (transients de l'updater + WP)
		delete_site_transient( 'update_themes' );
		delete_site_transient( 'update_plugins' );
		// AG_Theme_Updater::CACHE_KEY = 'ag_coach_theme_remote' (cf
		// inc/theme-updater.php). On purge ce nom exact.
		delete_transient( 'ag_coach_theme_remote' );

		// 2. Force-check companion (si plugin installe, CACHE_KEY connu)
		delete_transient( 'ag_starter_companion_remote' );
		delete_transient( 'ag_companion_remote' );

		// 3. Force WP a relancer le check theme/plugin immediatement
		if ( function_exists( 'wp_update_themes' ) ) {
			wp_update_themes();
		}
		if ( function_exists( 'wp_update_plugins' ) ) {
			wp_update_plugins();
		}

		// 4. Reset complet (re-utilise la logique du reset classique)
		$stats = self::do_reset_logic();

		// 5. Purge cache LiteSpeed + WP
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();

		set_transient( 'ag_coach_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-coach-reset&sync_done=1' ) );
		exit;
	}

	/**
	 * Logique interne du reset (utilisable par handle_reset OU handle_sync_reset).
	 * @return array stats
	 */
	private static function do_reset_logic() {
		$stats = array(
			'mods_deleted'  => 0,
			'cpt_deleted'   => 0,
			'pages_deleted' => 0,
			'pages_created' => 0,
		);

		// 1. theme_mods d'autres templates
		$mods = get_theme_mods();
		if ( is_array( $mods ) ) {
			foreach ( array_keys( $mods ) as $key ) {
				foreach ( self::FOREIGN_MOD_PREFIXES as $prefix ) {
					if ( strpos( $key, $prefix ) === 0 ) {
						remove_theme_mod( $key );
						$stats['mods_deleted']++;
						break;
					}
				}
			}
		}

		// 2. CPT en SQL direct (marche meme si post_type non enregistre)
		global $wpdb;
		foreach ( self::FOREIGN_CPT as $cpt ) {
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", $cpt ) );
			foreach ( (array) $ids as $id ) {
				wp_delete_post( (int) $id, true );
				$stats['cpt_deleted']++;
			}
		}

		// 3. Pages aux slugs non-artisan
		foreach ( self::FOREIGN_PAGE_SLUGS as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$stats['pages_deleted']++;
			}
		}

		// 4. Recreer pages artisan manquantes.
		// Si le content commence par [shortcode], on wrap dans un block Gutenberg
		// 'core/shortcode' pour qu'il soit evalue au rendu (sinon esc_html
		// transformerait les crochets en entites HTML et le shortcode resterait
		// inerte).
		$created_page_ids = array();
		foreach ( self::ARTISAN_PAGES as $slug => $data ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$content = trim( $data['content'] );
				if ( preg_match( '/^\[\w+/', $content ) ) {
					// Shortcode → wrap dans un block Gutenberg dedie
					$post_content = "<!-- wp:shortcode -->\n" . $content . "\n<!-- /wp:shortcode -->";
				} else {
					$post_content = '<!-- wp:paragraph --><p>' . esc_html( $content ) . '</p><!-- /wp:paragraph -->';
				}
				$new_id = wp_insert_post( array(
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					$stats['pages_created']++;
					$created_page_ids[ $slug ] = $new_id;
				}
			} else {
				$created_page_ids[ $slug ] = $page->ID;
			}
		}

		// 5. Reconstruire le menu primary
		$menu_name = 'AG Artisan — Principal';
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
			$menu_id = $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $menu_name );
		}
		if ( ! is_wp_error( $menu_id ) ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => 'Accueil',
				'menu-item-url'    => home_url( '/' ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			) );
			foreach ( array( 'prestations', 'zones-intervention', 'realisations', 'qui-sommes-nous', 'devis', 'contact' ) as $slug ) {
				if ( empty( $created_page_ids[ $slug ] ) ) continue;
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => self::ARTISAN_PAGES[ $slug ]['title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $created_page_ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
			$locations = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		return $stats;
	}

	public static function handle_reset() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_coach_reset' );
		if ( empty( $_POST['ag_coach_reset_confirm'] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-coach-reset' ) );
			exit;
		}

		$stats = array(
			'mods_deleted'  => 0,
			'cpt_deleted'   => 0,
			'pages_deleted' => 0,
			'pages_created' => 0,
		);

		// 1. theme_mods d'autres templates
		$mods = get_theme_mods();
		if ( is_array( $mods ) ) {
			foreach ( array_keys( $mods ) as $key ) {
				foreach ( self::FOREIGN_MOD_PREFIXES as $prefix ) {
					if ( strpos( $key, $prefix ) === 0 ) {
						remove_theme_mod( $key );
						$stats['mods_deleted']++;
						break;
					}
				}
			}
		}

		// 2. CPT d'autres templates : suppression force via SQL direct.
		// On n'utilise pas get_posts() car le post_type peut ne plus etre
		// enregistre (plugins desactives par theme guard) — auquel cas
		// get_posts retourne vide alors que les posts existent encore en BDD.
		global $wpdb;
		foreach ( self::FOREIGN_CPT as $cpt ) {
			$ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
				$cpt
			) );
			foreach ( (array) $ids as $id ) {
				// wp_delete_post handle meta + relationships cleanup
				wp_delete_post( (int) $id, true );
				$stats['cpt_deleted']++;
			}
		}

		// 3. Pages aux slugs connus comme non-artisan
		foreach ( self::FOREIGN_PAGE_SLUGS as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$stats['pages_deleted']++;
			}
		}

		// 4. Recreer les pages artisan manquantes (avec support shortcode)
		$created_page_ids = array();
		foreach ( self::ARTISAN_PAGES as $slug => $data ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$content = trim( $data['content'] );
				if ( preg_match( '/^\[\w+/', $content ) ) {
					$post_content = "<!-- wp:shortcode -->\n" . $content . "\n<!-- /wp:shortcode -->";
				} else {
					$post_content = '<!-- wp:paragraph --><p>' . esc_html( $content ) . '</p><!-- /wp:paragraph -->';
				}
				$new_id = wp_insert_post( array(
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					$stats['pages_created']++;
					$created_page_ids[ $slug ] = $new_id;
				}
			} else {
				$created_page_ids[ $slug ] = $page->ID;
			}
		}

		// 5. Reconstruire le menu primary : wipe items existants + recreer
		$menu_name = 'AG Artisan — Principal';
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
			$menu_id = $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $menu_name );
		}
		if ( ! is_wp_error( $menu_id ) ) {
			// Item "Accueil" (lien custom vers home_url)
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => __( 'Accueil', 'ag-starter-coach' ),
				'menu-item-url'    => home_url( '/' ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			) );
			// Items pages artisan dans l'ordre
			foreach ( array( 'prestations', 'zones-intervention', 'realisations', 'qui-sommes-nous', 'devis', 'contact' ) as $slug ) {
				if ( empty( $created_page_ids[ $slug ] ) ) continue;
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => self::ARTISAN_PAGES[ $slug ]['title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $created_page_ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
			$locations = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		// 6. Purger les caches WP/transients de mise a jour (LiteSpeed/etc.)
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();
		delete_site_transient( 'update_themes' );
		delete_site_transient( 'update_plugins' );

		set_transient( 'ag_coach_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-coach-reset&reset_done=1' ) );
		exit;
	}
}
ag_coach_Reset::init();
