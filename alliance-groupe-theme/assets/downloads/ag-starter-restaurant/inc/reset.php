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
 * Apres reset : seules les options ag_restaurant_*, les pages restaurant
 * (prestations, zones-intervention, realisations, qui-sommes-nous, mentions,
 * contact) et le menu primary restaurant-only subsistent.
 *
 * Apparait dans : "Apparence > 🔄 Reinitialiser le theme".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Reset {

	/**
	 * Prefixes theme_mod d'autres templates Alliance Groupe — supprimes
	 * lors du reset (laisse ag_restaurant_*, background_color, blogname, etc.).
	 */
	// NB : pas de 'ag_fid_' ici — c'est le prefixe de la carte de fidelite DU restaurant.
	const FOREIGN_MOD_PREFIXES = array( 'ag_asso_', 'ag_avocat_', 'ag_barber_', 'ag_coach_', 'ag_resto_' );

	/**
	 * CPT d'autres templates a wipe completement (pas de raison d'avoir
	 * des combats ou des domaines avocat sur un site restaurant).
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
		// Avocat / Barber / Coach (PAS les pages restaurant carte/reservation/fidelite !)
		'domaines', 'honoraires', 'cabinet',
		'tarifs-coach', 'seances',
	);

	/**
	 * Pages que le template restaurant recree systematiquement.
	 * slug => array( title, content_placeholder )
	 */
	const RESTAURANT_PAGES = array(
		'carte'           => array( 'title' => 'Notre carte',      'content' => '[ag_restaurant_carte]' ),
		'reservation'     => array( 'title' => 'Réservation',      'content' => '[ag_restaurant_reservation]' ),
		'fidelite'        => array( 'title' => 'Fidélité',         'content' => '[ag_restaurant_fidelite]' ),
		'qui-sommes-nous' => array( 'title' => 'Qui sommes-nous',  'content' => 'Notre histoire, notre cuisine et notre équipe. Des produits frais, faits maison, dans une ambiance chaleureuse.' ),
		'contact'         => array( 'title' => 'Contact',          'content' => 'Pour réserver une table, commander ou nous joindre : retrouvez ici nos coordonnées et nos horaires.' ),
		'mentions'        => array( 'title' => 'Mentions légales', 'content' => 'Mentions légales et informations sur le restaurant.' ),
	);

	/** Pages affichées dans le menu principal, dans l'ordre. */
	const MENU_SLUGS = array( 'carte', 'reservation', 'fidelite', 'qui-sommes-nous', 'contact' );

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 30 );
		add_action( 'admin_post_ag_restaurant_reset', array( __CLASS__, 'handle_reset' ) );
		add_action( 'admin_post_ag_restaurant_sync_reset', array( __CLASS__, 'handle_sync_reset' ) );
		add_action( 'admin_post_ag_restaurant_purge_all', array( __CLASS__, 'handle_purge_all' ) );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Réinitialiser le thème', 'ag-starter-restaurant' ),
			'🔄 ' . __( 'Réinitialiser', 'ag-starter-restaurant' ),
			'manage_options',
			'ag-restaurant-reset',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$done      = isset( $_GET['reset_done'] ) ? (int) $_GET['reset_done'] : 0;
		$sync_done = isset( $_GET['sync_done'] ) ? (int) $_GET['sync_done'] : 0;
		$purge_done = isset( $_GET['purge_done'] ) ? (int) $_GET['purge_done'] : 0;
		$stats     = ( $done || $sync_done || $purge_done ) ? get_transient( 'ag_restaurant_reset_stats' ) : null;
		?>
		<div class="wrap">
			<h1>🔄 <?php esc_html_e( 'Réinitialiser le thème — AG Starter Restaurant', 'ag-starter-restaurant' ); ?></h1>

			<!-- BOUTON TOUT-EN-UN : sync GitHub + companion + reset -->
			<div style="background:linear-gradient(135deg,#0a0a0f 0%,#1a1a2e 100%);border-radius:12px;padding:28px 32px;margin:20px 0;color:#fff;max-width:820px;">
				<h2 style="margin:0 0 10px;color:#fff;font-size:1.3em;">🚀 <?php esc_html_e( 'Tout faire en 1 clic', 'ag-starter-restaurant' ); ?></h2>
				<p style="color:rgba(255,255,255,.8);margin:0 0 18px;line-height:1.55;"><?php esc_html_e( 'Force la vérification des mises à jour GitHub (theme + companion), purge tous les caches (LiteSpeed, transients WP, mises à jour) ET nettoie les résidus d\'autres templates. Idéal après modification du repo Alliance Groupe.', 'ag-starter-restaurant' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Lancer le combo SYNC GITHUB + RESET ? Tous les caches seront purges et les residus supprimes.');">
					<input type="hidden" name="action" value="ag_restaurant_sync_reset" />
					<?php wp_nonce_field( 'ag_restaurant_sync_reset' ); ?>
					<button type="submit" class="button button-primary button-hero" style="background:#F37A1F;border-color:#F37A1F;color:#fff;font-weight:700;padding:0 30px;">
						🚀 <?php esc_html_e( 'SYNC GITHUB + RESET COMPLET', 'ag-starter-restaurant' ); ?>
					</button>
				</form>
			</div>
			<hr style="margin:30px 0;border:none;border-top:1px solid #ddd;">
			<h2><?php esc_html_e( 'Ou : reset seul (sans sync GitHub)', 'ag-starter-restaurant' ); ?></h2>

			<?php if ( ( $done || $sync_done || $purge_done ) && $stats ) : ?>
				<div class="notice notice-success">
					<p><strong>✅ <?php echo $purge_done ? esc_html__( 'Nettoyage exhaustif effectué.', 'ag-starter-restaurant' ) : ( $sync_done ? esc_html__( 'Sync GitHub + reset complet effectués.', 'ag-starter-restaurant' ) : esc_html__( 'Réinitialisation effectuée.', 'ag-starter-restaurant' ) ); ?></strong></p>
						<?php if ( isset( $stats['posts_deleted'] ) ) : ?>
							<p>🔥 <?php printf( esc_html__( '%d articles et %d pages d\'autres templates supprimés.', 'ag-starter-restaurant' ), (int) $stats['posts_deleted'], (int) $stats['extra_pages_deleted'] ); ?></p>
						<?php endif; ?>
					<?php if ( $sync_done ) : ?>
						<p>🔄 <?php esc_html_e( 'Caches mises à jour purgés (theme + companion + LiteSpeed). Si une nouvelle version est disponible, elle apparaîtra dans', 'ag-starter-restaurant' ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>"><?php esc_html_e( 'Apparence > Thèmes', 'ag-starter-restaurant' ); ?></a>.</p>
					<?php endif; ?>
					<ul style="margin-left:20px;list-style:disc;">
						<li><?php printf( esc_html__( '%d options Customizer d\'autres templates supprimées', 'ag-starter-restaurant' ), (int) $stats['mods_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d entrées CPT (combats/événements/pétitions/etc.) supprimées', 'ag-starter-restaurant' ), (int) $stats['cpt_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d pages d\'autres templates supprimées', 'ag-starter-restaurant' ), (int) $stats['pages_deleted'] ); ?></li>
						<li><?php printf( esc_html__( '%d pages restaurant créées/restaurées', 'ag-starter-restaurant' ), (int) $stats['pages_created'] ); ?></li>
						<li><?php esc_html_e( 'Menu principal reconstruit (Accueil + Notre carte, Réservation, Fidélité, Qui sommes-nous, Contact)', 'ag-starter-restaurant' ); ?></li>
					</ul>
				</div>
			<?php endif; ?>

			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #d63638;padding:20px 24px;margin:20px 0;max-width:820px;">
				<h2 style="margin-top:0;color:#d63638;">⚠️ <?php esc_html_e( 'Action destructive', 'ag-starter-restaurant' ); ?></h2>
				<p><?php esc_html_e( 'Ce bouton supprime tous les résidus laissés par d\'autres templates Alliance Groupe (Association, Avocat, Barber, Coach, Restaurant) que vous avez pu activer précédemment :', 'ag-starter-restaurant' ); ?></p>
				<ul style="margin-left:24px;list-style:disc;">
					<li><?php esc_html_e( 'Options Customizer aux préfixes ag_asso_*, ag_avocat_*, ag_barber_*, ag_coach_*, ag_resto_*, ag_fid_*', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Tous les CPT : combats, événements, groupes, pétitions, PV, domaines avocat', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Pages WP : Manifeste, Combats, Événements, Pétitions, Don, Adhérer, Domaines, Honoraires, Carte, Réservation, etc.', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Menu principal : reconstruit avec uniquement les items restaurant', 'ag-starter-restaurant' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'Ce qui est conservé :', 'ag-starter-restaurant' ); ?></strong> <?php esc_html_e( 'vos options ag_restaurant_*, vos pages restaurant (Notre carte, Réservation, Fidélité, Qui sommes-nous, Mentions, Contact), vos articles de blog, vos utilisateurs, vos plugins.', 'ag-starter-restaurant' ); ?></p>
				<p><?php esc_html_e( 'Si une page restaurant manquait, elle est recréée avec un contenu placeholder.', 'ag-starter-restaurant' ); ?></p>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Confirmer la réinitialisation ? Tous les contenus d\'autres templates seront supprimés définitivement.');">
				<input type="hidden" name="action" value="ag_restaurant_reset" />
				<?php wp_nonce_field( 'ag_restaurant_reset' ); ?>
				<p>
					<label>
						<input type="checkbox" name="ag_restaurant_reset_confirm" value="1" required />
						<?php esc_html_e( 'J\'ai compris — supprimer définitivement tous les résidus d\'autres templates.', 'ag-starter-restaurant' ); ?>
					</label>
				</p>
				<p>
					<button type="submit" class="button button-primary button-hero" style="background:#d63638;border-color:#d63638;">
						🔄 <?php esc_html_e( 'TOUT RESET — remettre le thème à l\'état d\'origine', 'ag-starter-restaurant' ); ?>
					</button>
				</p>
			</form>

			<hr style="margin:34px 0;border:none;border-top:1px solid #ddd;">
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #8b0000;padding:20px 24px;margin:20px 0;max-width:820px;">
				<h2 style="margin-top:0;color:#8b0000;">🔥 <?php esc_html_e( 'Nettoyage exhaustif', 'ag-starter-restaurant' ); ?></h2>
				<p><?php esc_html_e( 'En plus du reset ci-dessus, supprime DÉFINITIVEMENT :', 'ag-starter-restaurant' ); ?></p>
				<ul style="margin-left:24px;list-style:disc;">
					<li><?php esc_html_e( 'TOUS les articles SAUF la catégorie « Recettes » (résidus de démo des autres templates inclus)', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'TOUTES les pages SAUF les pages restaurant et votre page d\'accueil', 'ag-starter-restaurant' ); ?></li>
				</ul>
				<p style="color:#8b0000;"><strong><?php esc_html_e( 'À utiliser pour repartir 100% propre. Vos vrais articles de blog seront aussi supprimés — réservez-le à un site de test ou fraîchement repris.', 'ag-starter-restaurant' ); ?></strong></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('NETTOYAGE EXHAUSTIF : tous les articles (hors Recettes) et toutes les pages non-restaurant seront supprimes definitivement. Continuer ?');">
					<input type="hidden" name="action" value="ag_restaurant_purge_all" />
					<?php wp_nonce_field( 'ag_restaurant_purge_all' ); ?>
					<p><label><input type="checkbox" name="ag_purge_confirm" value="1" required /> <?php esc_html_e( 'J\'ai compris — supprimer aussi tous les articles et pages d\'autres templates.', 'ag-starter-restaurant' ); ?></label></p>
					<button type="submit" class="button button-primary button-hero" style="background:#8b0000;border-color:#8b0000;">🔥 <?php esc_html_e( 'NETTOYAGE EXHAUSTIF', 'ag-starter-restaurant' ); ?></button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Combo : force-check GitHub theme + companion + purge transients +
	 * reset complet + purge LiteSpeed. Tout en 1 clic.
	 */
	public static function handle_sync_reset() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_restaurant_sync_reset' );

		// 1. Force-check GitHub theme (transients de l'updater + WP)
		delete_site_transient( 'update_themes' );
		delete_site_transient( 'update_plugins' );
		// AG_Theme_Updater::CACHE_KEY = 'ag_restaurant_theme_remote' (cf
		// inc/theme-updater.php). On purge ce nom exact.
		delete_transient( 'ag_restaurant_theme_remote' );

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

		set_transient( 'ag_restaurant_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-restaurant-reset&sync_done=1' ) );
		exit;
	}

	/**
	 * NETTOYAGE EXHAUSTIF : reset classique + suppression de TOUTES les pages
	 * et de TOUS les articles qui n'appartiennent pas au restaurant (résidus
	 * d'autres templates). On conserve : les pages restaurant, la page d'accueil/
	 * articles, et les articles de la catégorie « recettes ».
	 */
	public static function handle_purge_all() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_restaurant_purge_all' );

		$stats = self::do_reset_logic();
		$stats = array_merge( $stats, self::purge_other_content() );

		if ( has_action( 'litespeed_purge_all' ) ) do_action( 'litespeed_purge_all' );
		wp_cache_flush();

		set_transient( 'ag_restaurant_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-restaurant-reset&purge_done=1' ) );
		exit;
	}

	/**
	 * Supprime toutes les pages NON-restaurant et tous les articles HORS
	 * catégorie « recettes ». Vide aussi la corbeille.
	 * @return array stats supplémentaires
	 */
	private static function purge_other_content() {
		$out = array( 'extra_pages_deleted' => 0, 'posts_deleted' => 0 );

		// Pages à conserver : pages restaurant + accueil + page des articles.
		$keep = array();
		foreach ( array_keys( self::RESTAURANT_PAGES ) as $slug ) {
			$p = get_page_by_path( $slug );
			if ( $p ) $keep[] = (int) $p->ID;
		}
		$front = (int) get_option( 'page_on_front' );
		$blog  = (int) get_option( 'page_for_posts' );
		if ( $front ) $keep[] = $front;
		if ( $blog ) $keep[] = $blog;

		foreach ( (array) get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( in_array( (int) $pid, $keep, true ) ) continue;
			wp_delete_post( (int) $pid, true );
			$out['extra_pages_deleted']++;
		}

		// Articles : on garde uniquement la catégorie « recettes ».
		$cat     = get_category_by_slug( 'recettes' );
		$keep_id = $cat ? (int) $cat->term_id : 0;
		foreach ( (array) get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( $keep_id && has_category( $keep_id, (int) $pid ) ) continue;
			wp_delete_post( (int) $pid, true );
			$out['posts_deleted']++;
		}

		return $out;
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

		// 3. Pages aux slugs non-restaurant
		foreach ( self::FOREIGN_PAGE_SLUGS as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$stats['pages_deleted']++;
			}
		}

		// 4. Recreer pages restaurant manquantes.
		// Si le content commence par [shortcode], on wrap dans un block Gutenberg
		// 'core/shortcode' pour qu'il soit evalue au rendu (sinon esc_html
		// transformerait les crochets en entites HTML et le shortcode resterait
		// inerte).
		$created_page_ids = array();
		foreach ( self::RESTAURANT_PAGES as $slug => $data ) {
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
		$menu_name = 'AG Restaurant — Principal';
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
			foreach ( self::MENU_SLUGS as $slug ) {
				if ( empty( $created_page_ids[ $slug ] ) ) continue;
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => self::RESTAURANT_PAGES[ $slug ]['title'],
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
		check_admin_referer( 'ag_restaurant_reset' );
		if ( empty( $_POST['ag_restaurant_reset_confirm'] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-restaurant-reset' ) );
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

		// 3. Pages aux slugs connus comme non-restaurant
		foreach ( self::FOREIGN_PAGE_SLUGS as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$stats['pages_deleted']++;
			}
		}

		// 4. Recreer les pages restaurant manquantes (avec support shortcode)
		$created_page_ids = array();
		foreach ( self::RESTAURANT_PAGES as $slug => $data ) {
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
		$menu_name = 'AG Restaurant — Principal';
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
				'menu-item-title'  => __( 'Accueil', 'ag-starter-restaurant' ),
				'menu-item-url'    => home_url( '/' ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			) );
			// Items pages restaurant dans l'ordre
			foreach ( self::MENU_SLUGS as $slug ) {
				if ( empty( $created_page_ids[ $slug ] ) ) continue;
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => self::RESTAURANT_PAGES[ $slug ]['title'],
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

		set_transient( 'ag_restaurant_reset_stats', $stats, 60 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-restaurant-reset&reset_done=1' ) );
		exit;
	}
}
AG_Restaurant_Reset::init();
