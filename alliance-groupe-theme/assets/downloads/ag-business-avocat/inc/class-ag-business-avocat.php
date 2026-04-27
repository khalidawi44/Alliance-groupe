<?php
/**
 * Main Business class. Singleton.
 *
 * @package AG_Business_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Business_Avocat {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Hooks always registered; tier check lazy inside each callback.
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
	}

	/**
	 * Lazy tier check. Active uniquement si tier === business.
	 */
	private function is_active() {
		if ( ! class_exists( 'AG_Licence_Client' ) ) {
			return false;
		}
		return 'business' === AG_Licence_Client::get_tier();
	}

	public function add_body_class( $classes ) {
		if ( ! $this->is_active() ) {
			return $classes;
		}
		$classes[] = 'ag-business-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( file_exists( AG_BUSINESS_AVOCAT_DIR . 'assets/business.css' ) ) {
			wp_enqueue_style(
				'ag-business-avocat-style',
				AG_BUSINESS_AVOCAT_URL . 'assets/business.css',
				array(),
				AG_BUSINESS_AVOCAT_VERSION
			);
		}
		if ( file_exists( AG_BUSINESS_AVOCAT_DIR . 'assets/business.js' ) ) {
			wp_enqueue_script(
				'ag-business-avocat-script',
				AG_BUSINESS_AVOCAT_URL . 'assets/business.js',
				array(),
				AG_BUSINESS_AVOCAT_VERSION,
				true
			);
		}
	}
}
