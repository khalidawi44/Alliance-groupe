<?php
/**
 * Auto-update du plugin AG Business Barber via raw GitHub.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_BB_Updater {
	const JSON_URL  = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/ag-business-barber.json';
	const SLUG      = 'ag-business-barber';
	const CACHE_KEY = 'ag_bb_remote_info';
	const CACHE_TTL = HOUR_IN_SECONDS;

	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check_update' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'plugin_info' ), 20, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush_cache' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_force_check' ) );
	}

	public static function maybe_force_check() {
		if ( empty( $_GET['ag_bb_check_update'] ) || ! current_user_can( 'manage_options' ) ) return;
		delete_site_transient( 'update_plugins' );
		delete_transient( self::CACHE_KEY );
		wp_safe_redirect( admin_url( 'plugins.php?ag_bb_checked=1' ) );
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
		$file = self::SLUG . '/' . self::SLUG . '.php';
		$current = defined( 'AG_BUSINESS_BARBER_VERSION' ) ? AG_BUSINESS_BARBER_VERSION : '0.0.0';
		if ( version_compare( $remote['version'], $current, '>' ) ) {
			$transient->response[ $file ] = (object) array(
				'slug'        => self::SLUG,
				'plugin'      => $file,
				'new_version' => $remote['version'],
				'package'     => $remote['download_url'],
				'url'         => $remote['homepage'] ?? 'https://alliancegroupe-inc.com/wordpress-barber',
				'tested'      => $remote['tested']   ?? '6.5',
				'requires'    => $remote['requires'] ?? '6.0',
			);
		}
		return $transient;
	}

	public static function plugin_info( $result, $action, $args ) {
		if ( $action !== 'plugin_information' || empty( $args->slug ) || $args->slug !== self::SLUG ) return $result;
		$remote = self::get_remote();
		if ( ! $remote ) return $result;
		return (object) array(
			'name'          => 'AG Business Barber',
			'slug'          => self::SLUG,
			'version'       => $remote['version'],
			'author'        => '<a href="https://alliancegroupe-inc.com">Alliance Groupe</a>',
			'requires'      => $remote['requires']     ?? '6.0',
			'tested'        => $remote['tested']       ?? '6.5',
			'requires_php'  => $remote['requires_php'] ?? '7.4',
			'last_updated'  => $remote['last_updated'] ?? gmdate( 'Y-m-d' ),
			'homepage'      => $remote['homepage']     ?? 'https://alliancegroupe-inc.com/wordpress-barber',
			'sections'      => array(
				'description' => $remote['description'] ?? 'AG Business Barber',
				'changelog'   => $remote['changelog']   ?? 'Voir GitHub.',
			),
			'download_link' => $remote['download_url'],
		);
	}
}
AG_BB_Updater::init();
