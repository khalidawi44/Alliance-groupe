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
 * Apres reset : seules les options ag_artisan_*, les pages artisan
 * (prestations, zones-intervention, realisations, qui-sommes-nous, mentions,
 * contact) et le menu primary artisan-only subsistent.
 *
 * Apparait dans : "Apparence > 🔄 Reinitialiser le theme".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Artisan_Reset {

	/**
	 * Prefixes theme_mod d'autres templates Alliance Groupe — supprimes
	 * lors du reset (laisse ag_artisan_*, background_color, blogname, etc.).
	 */
	const FOREIGN_MOD_PREFIXES = array( 'ag_asso_', 'ag_avocat_', 'ag_barber_', 'ag_coach_', 'ag_resto_', 'ag_fid_' );

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
		// Avocat / Barber / Resto / Coach
		'domaines', 'honoraires', 'cabinet', 'reservation', 'carte', 'menu',
		'tarifs-coach', 'seances',
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
		'devis'              => array( 'title' => 'Devis en ligne',       'content' => '[ag_artisan_devis]' ),
		'mentions'           => array( 'title' => 'Mentions légales',     'content' => 'Mentions légales et informations sur l\'entreprise.' ),
		'contact'            => array( 'title' => 'Contact',              'content' => 'Pour toute demande de devis ou de renseignement, contactez-nous.' ),
	);

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 30 );
		add_action( 'admin_post_ag_artisan_reset', array( __CLASS__, 'handle_reset' ) );
		add_action( 'admin_post_ag_artisan_sync_reset', array( __CLASS__, 'handle_sync_reset' ) );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Réinitialiser le thème', 'ag-starter-artisan' ),
			'🔄 ' . __( 'Réinitialiser', 'ag-starter-artisan' ),
			'manage_options',
			'ag-artisan-reset',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$done      = isset( $_GET['reset_done'] ) ? (int) $_GET['reset_done'] : 0;
		$sync_done = isset( $_GET['sync_done'] ) ? (int) $_GET['sync_done'] : 0;
		$stats     = ( $done || $sync_done ) ? get_transient( 'ag_artisan_reset_stats' ) : null;
		?>
		<div class="wrap">
			<h1>🔄 <?php esc_html_e( 'Réinitialiser le thème — AG Starter Artisan', 'ag-starter-artisan' ); ?></h1>

			<!-- BOUTON TOUT-EN-UN : sync GitHub + companion + reset -->
			<div style="background:linear-gradient(135deg,#0a0a0f 0%,#1a1a2e 100%);border-radius:12px;padding:28px 32px;margin:20px 0;color:#fff;max-width:820px;">
				<h2 style="margin:0 0 10px;color:#fff;font-size:1.3em;">🚀 <?php esc_html_e( 'Tout faire en 1 clic', 'ag-starter-artisan' ); ?></h2>
				<p style="color:rgba(255,255,255,.8);margin:0 0 18px;line-height:1.55;"><?php esc_html_e( 'Force la vérification des mises à jour GitHub (theme + companion), purge tous les caches (LiteSpeed, transients WP, mises à jour) ET nettoie les résidus d\'autres templates. Idéal après modification du repo Alliance Groupe.', 'ag-starter-artisan' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Lancer le combo SYNC GITHUB + RESET ? Tous les caches seront purges et les residus supprimes.');">
					<input type="hidden" name="action" value="ag_artisan_sync_reset" />
					<?php wp_nonce_field( 'ag_artisan_sync_reset' ); ?>
					<button type="submit" class="button button-primary button-hero" style="background:#F37A1F;border-color:#F37A1F;color:#fff;font-weight:700;padding:0 30px;">
						🚀 <?php esc_html_e( 'SYNC GITHUB + RESET COMPLET', 'ag-starter-artisan' ); ?>
					</button>
				</form>
			</div>
			<hr style="margin:30px 0;border:none;border-top:1px solid #ddd;">
			<h2><?php esc_html_e( 'Ou : reset seul (sans sync GitHub)', 'ag-starter-artisan' ); ?></h2>

			<?php if ( ( $done || $sync_done ) && $stats ) : ?>
				<div class="notice notice-success">
					<p><strong>✅ <?php echo $sync_done ? esc_html__( 'Sync GitHub + reset complet effectués.', 'ag-starter-artisan' ) : esc_html__( 'Réinitialisation effectuée.', 'ag-starter-artisan' ); ?></strong></p>
					<?php if ( $sync_done ) : ?>
						<p>🔄 <?php esc_html_e( 'Caches mises à jour purgés (theme + companion + LiteSpeed). Si une nouvelle version est disponible, elle apparaîtra dans', 'ag-starter-artisan' ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>"><?php esc_html_e( 'Apparence > Thèmes', 'ag-starter-artisan' ); ?></a>.</p>
					<?php endif; ?>
					<ul style="margin-left:20px;list-style:disc;">
						<li><?php printf( esc_html__( '%d options Customizer d\'autres templates supprimées', 'ag-starter-artisan' ), (int) $stats['mods_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d entrées CPT (combats/événements/pétitions/etc.) supprimées', 'ag-starter-artisan' ), (int) $stats['cpt_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d pages d\'autres templates supprimées', 'ag-starter-artisan' ), (int) $stats['pages_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d pages artisan créées/restaurées', 'ag-starter-artisan' ), (int) $stats['pages_created'] ); ?></li>
						<li><?php esc_html_e( 'Menu principal reconstruit (6 items artisan)', 'ag-starter-artisan' ); ?></li>
					</ul>
				</div>
			<?php endif; ?>

			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #d63638;padding:20px 24px;margin:20px 0;max-width:820px;">
				<h2 style="margin-top:0;color:#d63638;">⚠️ <?php esc_html_e( 'Action destructive', 'ag-starter-artisan' ); ?></h2>
				<p><?php esc_html_e( 'Ce bouton supprime tous les résidus laissés par d\'autres templates Alliance Groupe (Association, Avocat, Barber, Coach, Restaurant) que vous avez pu activer précédemment :', 'ag-starter-artisan' ); ?></p>
				<ul style="margin-left:24px;list-style:disc;">
					<li><?php esc_html_e( 'Options Customizer aux préfixes ag_asso_*, ag_avocat_*, ag_barber_*, ag_coach_*, ag_resto_*, ag_fid_*', 'ag-starter-artisan' ); ?></li>
					<li><?php esc_html_e( 'Tous les CPT : combats, événements, groupes, pétitions, PV, domaines avocat', 'ag-starter-artisan' ); ?></li>
					<li><?php esc_html_e( 'Pages WP : Manifeste, Combats, Événements, Pétitions, Don, Adhérer, Domaines, Honoraires, Carte, Réservation, etc.', 'ag-starter-artisan' ); ?></li>
					<li><?php esc_html_e( 'Menu principal : reconstruit avec uniquement les items artisan', 'ag-starter-artisan' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'Ce qui est conservé :', 'ag-starter-artisan' ); ?></strong> <?php esc_html_e( 'vos options ag_artisan_*, vos pages artisan (Prestations, Zones, Réalisations, Qui sommes-nous, Mentions, Contact), vos articles de blog, vos utilisateurs, vos plugins.', 'ag-starter-artisan' ); ?></p>
				<p><?php esc_html_e( 'Si une page artisan manquait, elle est recréée avec un contenu placeholder.', 'ag-starter-artisan' ); ?></p>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Confirmer la réinitialisation ? Tous les contenus d\'autres templates seront supprimés définitivement.');">
				<input type="hidden" name="action" value="ag_artisan_reset" />
				<?php wp_nonce_field( 'ag_artisan_reset' ); ?>
				<p>
					<label>
						<input type="checkbox" name="ag_artisan_reset_confirm" value="1" required />
						<?php esc_html_e( 'J\'ai compris — supprimer définitivement tous les résidus d\'autres templates.', 'ag-starter-artisan' ); ?>
					</label>
				</p>
				<p>
					<button type="submit" class="button button-primary button-hero" style="background:#d63638;border-color:#d63638;">
						🔄 <?php esc_html_e( 'TOUT RESET — remettre le thème à l\'état d\'origine', 'ag-starter-artisan' ); ?>
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
		check_admin_referer( 'ag_artisan_sync_reset' );

		// 1. Force-check GitHub theme (transients de l'updater + WP)
		delete_site_transient( 'update_themes' );
		delete_site_transient( 'update_plugins' );
		// Le theme-updater du theme utilise un transient nomme ag_artisan_remote_manifest
		// (consts dans inc/theme-updater.php), on tente toutes les variantes.
		delete_transient( 'ag_artisan_remote_manifest' );
		delete_transient( 'ag_starter_artisan_remote_manifest' );

		// 2. Force-check companion (si plugin installe)
		delete_transient( 'ag_starter_companion_remote_manifest' );
		delete_transient( 'ag_companion_remote_manifest' );

		// 3. Reset complet (re-utilise la logique du reset classique)
		$_POST['ag_artisan_reset_confirm'] = '1';
		// On simule l'envoi du formulaire reset en respectant son nonce :
		// pour simplifier, on appelle directement la logique interne.
		$stats = self::do_reset_logic();

		// 4. Purge cache LiteSpeed + WP
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();

		set_transient( 'ag_artisan_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-reset&sync_done=1' ) );
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

		// 4. Recreer pages artisan manquantes
		$created_page_ids = array();
		foreach ( self::ARTISAN_PAGES as $slug => $data ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$new_id = wp_insert_post( array(
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $data['content'] ) . '</p><!-- /wp:paragraph -->',
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
			foreach ( array( 'prestations', 'zones-intervention', 'realisations', 'qui-sommes-nous', 'contact' ) as $slug ) {
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
		check_admin_referer( 'ag_artisan_reset' );
		if ( empty( $_POST['ag_artisan_reset_confirm'] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-reset' ) );
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

		// 4. Recreer les pages artisan manquantes
		$created_page_ids = array();
		foreach ( self::ARTISAN_PAGES as $slug => $data ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$new_id = wp_insert_post( array(
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $data['content'] ) . '</p><!-- /wp:paragraph -->',
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
				'menu-item-title'  => __( 'Accueil', 'ag-starter-artisan' ),
				'menu-item-url'    => home_url( '/' ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			) );
			// Items pages artisan dans l'ordre
			foreach ( array( 'prestations', 'zones-intervention', 'realisations', 'qui-sommes-nous', 'contact' ) as $slug ) {
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

		set_transient( 'ag_artisan_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-reset&reset_done=1' ) );
		exit;
	}
}
AG_Artisan_Reset::init();
