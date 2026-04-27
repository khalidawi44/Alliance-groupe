<?php
/**
 * Main Premium class. Singleton.
 *
 * @package AG_Premium_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Premium_Avocat {

	private static $instance = null;
	private $is_active       = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->is_active = $this->detect_tier();
		if ( ! $this->is_active ) {
			return;
		}

		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );

		// Premium-only hook handlers go below. Keep each one in its own
		// method, gated by $this->is_active (already enforced by the early
		// return above). Use .ag-premium-* CSS classes only.
	}

	/**
	 * Active si AG_Licence_Client détecte un tier >= premium. Si la classe
	 * n'existe pas (Free seul installé), le plugin reste inactif.
	 */
	private function detect_tier() {
		if ( ! class_exists( 'AG_Licence_Client' ) ) {
			return false;
		}
		$tier = AG_Licence_Client::get_tier();
		return in_array( $tier, array( 'premium', 'business' ), true );
	}

	public function add_body_class( $classes ) {
		$classes[] = 'ag-premium-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( file_exists( AG_PREMIUM_AVOCAT_DIR . 'assets/premium.css' ) ) {
			wp_enqueue_style(
				'ag-premium-avocat-style',
				AG_PREMIUM_AVOCAT_URL . 'assets/premium.css',
				array(),
				AG_PREMIUM_AVOCAT_VERSION
			);
		}
		if ( file_exists( AG_PREMIUM_AVOCAT_DIR . 'assets/premium.js' ) ) {
			wp_enqueue_script(
				'ag-premium-avocat-script',
				AG_PREMIUM_AVOCAT_URL . 'assets/premium.js',
				array(),
				AG_PREMIUM_AVOCAT_VERSION,
				true
			);
		}
	}
}
