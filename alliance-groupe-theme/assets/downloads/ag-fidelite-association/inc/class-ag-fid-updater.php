<?php
/**
 * Auto-update du plugin Pack Fidelite via raw GitHub.
 * Verifie ag-fidelite-association.json toutes les 12h, inject dans
 * le transient WP update_plugins pour que la maj apparaisse dans
 * Extensions > Mises a jour comme un plugin standard.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Updater {
	const JSON_URL  = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/ag-fidelite-association.json';
	const CACHE_KEY = 'ag_fid_remote_info';
	const CACHE_TTL = HOUR_IN_SECONDS;

	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check_update' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'plugin_info' ), 20, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush_cache' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_force_check' ) );
	}

	public static function maybe_force_check() {
		if ( empty( $_GET['ag_fid_check_update'] ) || ! current_user_can( 'manage_options' ) ) return;
		delete_site_transient( 'update_plugins' );
		delete_transient( self::CACHE_KEY );
		wp_safe_redirect( admin_url( 'plugins.php?ag_fid_checked=1' ) );
		exit;
	}

	public static function flush_cache( $upgrader, $hook_extra ) {
		if ( ! empty( $hook_extra['plugins'] ) ) delete_transient( self::CACHE_KEY );
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
		$slug = 'ag-fidelite-association';
		$file = $slug . '/' . $slug . '.php';
		if ( version_compare( $remote['version'], AG_FID_VERSION, '>' ) ) {
			$transient->response[ $file ] = (object) array(
				'slug'        => $slug,
				'plugin'      => $file,
				'new_version' => $remote['version'],
				'package'     => $remote['download_url'],
				'url'         => $remote['homepage'] ?? 'https://alliancegroupe-inc.com/wordpress-association',
				'tested'      => $remote['tested']   ?? '6.5',
				'requires'    => $remote['requires'] ?? '5.8',
			);
		}
		return $transient;
	}

	public static function plugin_info( $result, $action, $args ) {
		if ( $action !== 'plugin_information' || empty( $args->slug ) || $args->slug !== 'ag-fidelite-association' ) return $result;
		$remote = self::get_remote();
		if ( ! $remote ) return $result;
		return (object) array(
			'name'          => 'AG Fidélité Association',
			'slug'          => 'ag-fidelite-association',
			'version'       => $remote['version'],
			'author'        => '<a href="https://alliancegroupe-inc.com">Alliance Groupe</a>',
			'requires'      => $remote['requires']   ?? '5.8',
			'tested'        => $remote['tested']     ?? '6.5',
			'requires_php'  => $remote['requires_php'] ?? '7.4',
			'last_updated'  => $remote['last_updated'] ?? gmdate( 'Y-m-d' ),
			'homepage'      => $remote['homepage']   ?? 'https://alliancegroupe-inc.com/wordpress-association',
			'sections'      => array(
				'description' => $remote['description'] ?? 'Pack Fidélité — outils pour associations militantes.',
				'changelog'   => $remote['changelog']   ?? 'Voir GitHub.',
			),
			'download_link' => $remote['download_url'],
		);
	}
}
AG_Fid_Updater::init();
