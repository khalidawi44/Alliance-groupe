<?php
/**
 * Auto-update du theme Gwen Services via raw GitHub.
 *
 * Deux niveaux :
 *  1. Detection classique (pre_set_site_transient_update_themes) : WordPress
 *     affiche « mise a jour disponible » et peut l'installer via son cron.
 *  2. AUTO-SYNC DIRECT (le vrai correctif) : des qu'un admin ouvre le
 *     tableau de bord, si une version plus recente existe sur GitHub, le
 *     theme la telecharge et l'installe LUI-MEME (ecriture directe, sans
 *     dependre du WP-Cron ni d'un prompt FTP). Throttle 2 h.
 *     + bouton manuel « Synchroniser maintenant » (?ag_domicile_sync_now=1).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Domicile_Theme_Updater {
	const JSON_URL  = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/ag-gwen-services.json';
	const SLUG      = 'ag-gwen-services';
	const CACHE_KEY = 'ag_domicile_theme_remote';
	const CACHE_TTL = HOUR_IN_SECONDS;
	const SYNC_LOCK = 'ag_domicile_sync_lock';
	const SYNC_TTL  = 2 * HOUR_IN_SECONDS;

	public static function init() {
		add_filter( 'pre_set_site_transient_update_themes', array( __CLASS__, 'check_update' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_force_check' ) );
		// Auto-sync direct a l'ouverture de l'admin (throttle), priorite tardive.
		add_action( 'admin_init', array( __CLASS__, 'auto_self_update' ), 99 );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
		// Bouton « Sync Gwen » dans la barre d'admin (1 clic, partout).
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_button' ), 90 );
	}

	public static function admin_bar_button( $bar ) {
		if ( ! current_user_can( 'update_themes' ) ) return;
		$bar->add_node( array(
			'id'    => 'ag-domicile-sync',
			'title' => '🔄 Sync Gwen',
			'href'  => wp_nonce_url( admin_url( '?ag_domicile_sync_now=1' ), 'ag_domicile_sync' ),
			'meta'  => array( 'title' => 'Télécharger et installer la dernière version du site depuis GitHub' ),
		) );
	}

	public static function maybe_force_check() {
		// « Verifier les MAJ » : vide juste les caches et laisse WP recontroler.
		if ( ! empty( $_GET['ag_domicile_check_theme'] ) && current_user_can( 'manage_options' ) ) {
			delete_site_transient( 'update_themes' );
			delete_transient( self::CACHE_KEY );
			delete_transient( self::SYNC_LOCK );
			wp_safe_redirect( admin_url( 'themes.php?ag_domicile_theme_checked=1' ) );
			exit;
		}
		// « Synchroniser maintenant » : telecharge + installe tout de suite.
		if ( ! empty( $_GET['ag_domicile_sync_now'] ) && current_user_can( 'update_themes' )
			&& isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ag_domicile_sync' ) ) {
			$done = self::run_self_update( true );
			wp_safe_redirect( admin_url( 'themes.php?ag_domicile_synced=' . ( $done ? '1' : '0' ) ) );
			exit;
		}
	}

	/** Auto-sync a chaque chargement admin, limite a 1 fois / SYNC_TTL. */
	public static function auto_self_update() {
		if ( ! current_user_can( 'update_themes' ) ) return;
		if ( get_transient( self::SYNC_LOCK ) ) return;          // throttle
		set_transient( self::SYNC_LOCK, 1, self::SYNC_TTL );     // pose le verrou avant (evite de marteler GitHub)
		self::run_self_update( false );
	}

	private static function get_remote() {
		$cached = get_transient( self::CACHE_KEY );
		if ( $cached !== false ) return $cached;
		$res = wp_remote_get( self::JSON_URL, array( 'timeout' => 8 ) );
		if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) return false;
		$json = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! $json || empty( $json['version'] ) ) return false;
		set_transient( self::CACHE_KEY, $json, self::CACHE_TTL );
		return $json;
	}

	public static function check_update( $transient ) {
		if ( empty( $transient->checked ) ) return $transient;
		$remote = self::get_remote();
		if ( ! $remote ) return $transient;
		$theme = wp_get_theme( self::SLUG );
		if ( ! $theme->exists() ) return $transient;
		$current = $theme->get( 'Version' );
		if ( version_compare( $remote['version'], $current, '>' ) ) {
			$transient->response[ self::SLUG ] = array(
				'theme'       => self::SLUG,
				'new_version' => $remote['version'],
				'url'         => $remote['homepage'] ?? 'https://alliancegroupe-inc.com/wordpress-domicile',
				'package'     => $remote['download_url'],
			);
		}
		return $transient;
	}

	/**
	 * Telecharge le zip depuis GitHub et l'installe directement (ecriture
	 * directe, sans prompt FTP). Renvoie true si une MAJ a bien ete posee.
	 *
	 * @param bool $force Vide le cache distant avant de verifier.
	 * @return bool
	 */
	public static function run_self_update( $force = false ) {
		if ( $force ) delete_transient( self::CACHE_KEY );
		$remote = self::get_remote();
		if ( ! $remote || empty( $remote['download_url'] ) ) return false;

		$theme = wp_get_theme( self::SLUG );
		if ( ! $theme->exists() ) return false;
		// Deja a jour : rien a faire.
		if ( ! version_compare( $remote['version'], $theme->get( 'Version' ), '>' ) ) return false;

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';

		// Forcer l'ecriture directe (Hostinger : les fichiers appartiennent a PHP).
		add_filter( 'filesystem_method', array( __CLASS__, 'force_direct_fs' ), 99 );
		if ( ! WP_Filesystem() ) {                       // hebergement non direct-writable
			remove_filter( 'filesystem_method', array( __CLASS__, 'force_direct_fs' ), 99 );
			set_transient( 'ag_domicile_sync_msg', 'fs', MINUTE_IN_SECONDS * 10 );
			return false;
		}

		$skin   = new Automatic_Upgrader_Skin();
		$upg    = new Theme_Upgrader( $skin );
		// install() + overwrite_package = remplace le theme existant (meme actif).
		$result = $upg->install( $remote['download_url'], array( 'overwrite_package' => true ) );

		remove_filter( 'filesystem_method', array( __CLASS__, 'force_direct_fs' ), 99 );

		// Rafraichir les caches de MAJ.
		delete_site_transient( 'update_themes' );
		if ( function_exists( 'wp_clean_themes_cache' ) ) wp_clean_themes_cache();

		$ok = ( true === $result );
		if ( $ok ) set_transient( 'ag_domicile_sync_msg', 'ok:' . $remote['version'], MINUTE_IN_SECONDS * 10 );
		return $ok;
	}

	public static function force_direct_fs() {
		return 'direct';
	}

	public static function admin_notice() {
		if ( ! current_user_can( 'update_themes' ) ) return;
		// Resultat d'un sync (auto ou manuel).
		$msg = get_transient( 'ag_domicile_sync_msg' );
		if ( $msg ) {
			delete_transient( 'ag_domicile_sync_msg' );
			if ( 0 === strpos( $msg, 'ok:' ) ) {
				echo '<div class="notice notice-success is-dismissible"><p><strong>Gwen Services</strong> — site mis a jour automatiquement en version <code>' . esc_html( substr( $msg, 3 ) ) . '</code>. ✅</p></div>';
			} elseif ( 'fs' === $msg ) {
				echo '<div class="notice notice-warning is-dismissible"><p><strong>Gwen Services</strong> — mise a jour detectee mais l\'hebergement bloque l\'ecriture directe. Ajoutez <code>define(\'FS_METHOD\', \'direct\');</code> dans <code>wp-config.php</code>, ou cliquez « Mettre a jour » manuellement.</p></div>';
			}
		}
		if ( ! empty( $_GET['ag_domicile_synced'] ) ) {
			$ok = '1' === $_GET['ag_domicile_synced'];
			echo '<div class="notice notice-' . ( $ok ? 'success' : 'info' ) . ' is-dismissible"><p>' . ( $ok ? 'Synchronisation effectuee : le site est a jour. ✅' : 'Le site etait deja a jour (ou l\'ecriture directe est bloquee).' ) . '</p></div>';
		}
	}
}
AG_Domicile_Theme_Updater::init();
