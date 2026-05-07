<?php
/**
 * Auto-update du theme AG Starter Avocat via raw GitHub.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Avocat_Theme_Updater {
	const JSON_URL  = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/ag-starter-avocat.json';
	const SLUG      = 'ag-starter-avocat';
	const CACHE_KEY = 'ag_avocat_theme_remote';
	const CACHE_TTL = HOUR_IN_SECONDS;

	public static function init() {
		add_filter( 'pre_set_site_transient_update_themes', array( __CLASS__, 'check_update' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_force_check' ) );
	}

	public static function maybe_force_check() {
		if ( empty( $_GET['ag_avocat_check_theme'] ) || ! current_user_can( 'manage_options' ) ) return;
		delete_site_transient( 'update_themes' );
		delete_transient( self::CACHE_KEY );
		wp_safe_redirect( admin_url( 'themes.php?ag_avocat_theme_checked=1' ) );
		exit;
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
				'url'         => $remote['homepage'] ?? 'https://alliancegroupe-inc.com/wordpress-avocat',
				'package'     => $remote['download_url'],
			);
		}
		return $transient;
	}
}
AG_Avocat_Theme_Updater::init();
