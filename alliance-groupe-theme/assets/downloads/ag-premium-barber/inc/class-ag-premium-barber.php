<?php
/**
 * Main Premium Barber class. Singleton.
 *
 * @package AG_Premium_Barber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Premium_Barber {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 25 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 30 );
	}

	private function is_active() {
		// Mode test (Customizer) pour preview sans licence reelle.
		if ( get_theme_mod( 'ag_pb_force_active', false ) ) {
			return true;
		}
		if ( ! class_exists( 'AG_Licence_Client' ) ) {
			return false;
		}
		$tier = AG_Licence_Client::get_tier();
		return in_array( $tier, array( 'premium', 'business' ), true );
	}

	public function add_body_class( $classes ) {
		if ( ! $this->is_active() ) {
			return $classes;
		}
		// Classe partagee avec Business (les 2 utilisent le meme design)
		$classes[] = 'ag-bb-active';
		// Classe Premium-specifique (utile pour cibler Premium uniquement
		// si jamais on veut des differences)
		$classes[] = 'ag-bb-premium-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}
		// Fonts industrielles : Bebas Neue (display uppercase) +
		// Cormorant Garamond (italic chic) + Special Elite (typewriter).
		wp_enqueue_style(
			'ag-pb-fonts',
			'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Special+Elite&display=swap',
			array(),
			AG_PREMIUM_BARBER_VERSION
		);
		if ( file_exists( AG_PREMIUM_BARBER_DIR . 'assets/premium.css' ) ) {
			wp_enqueue_style(
				'ag-premium-barber-style',
				AG_PREMIUM_BARBER_URL . 'assets/premium.css',
				array( 'ag-pb-fonts' ),
				AG_PREMIUM_BARBER_VERSION
			);
		}
		if ( file_exists( AG_PREMIUM_BARBER_DIR . 'assets/premium.js' ) ) {
			wp_enqueue_script(
				'ag-premium-barber-script',
				AG_PREMIUM_BARBER_URL . 'assets/premium.js',
				array(),
				AG_PREMIUM_BARBER_VERSION,
				true
			);
		}
	}

	public function register_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'ag_pb_panel', array(
			'title'    => __( 'AG Premium Barber', 'ag-premium-barber' ),
			'priority' => 215,
		) );
		$wp_customize->add_section( 'ag_pb_activation', array(
			'title'       => __( 'Activation', 'ag-premium-barber' ),
			'description' => __( "Mode test pour prévisualiser le rendu sans licence Premium réelle.", 'ag-premium-barber' ),
			'panel'       => 'ag_pb_panel',
		) );
		$wp_customize->add_setting( 'ag_pb_force_active', array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_pb_force_active', array(
			'type'        => 'checkbox',
			'label'       => __( 'Mode test — activer Premium même sans licence', 'ag-premium-barber' ),
			'description' => __( 'À désactiver en production une fois la licence Premium validée.', 'ag-premium-barber' ),
			'section'     => 'ag_pb_activation',
		) );
	}
}
