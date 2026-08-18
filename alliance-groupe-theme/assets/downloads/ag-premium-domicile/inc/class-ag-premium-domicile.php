<?php
/**
 * AG Premium Domicile — couche de design premium (design only).
 *
 * @package AG_Premium_Domicile
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Premium_Domicile {

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

	/**
	 * Premium actif si : mode test (Customizer) OU licence premium/business.
	 */
	private function is_active() {
		if ( get_theme_mod( 'ag_pd_force_active', false ) ) {
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
		$classes[] = 'ag-dm-premium-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}
		// Typographie premium : Fraunces (titres, chaleureux & élégant) + Nunito Sans (texte).
		wp_enqueue_style(
			'ag-pd-fonts',
			'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,500&family=Nunito+Sans:wght@400;600;700&display=swap',
			array(),
			AG_PREMIUM_DOMICILE_VERSION
		);
		if ( file_exists( AG_PREMIUM_DOMICILE_DIR . 'assets/premium.css' ) ) {
			wp_enqueue_style(
				'ag-premium-domicile-style',
				AG_PREMIUM_DOMICILE_URL . 'assets/premium.css',
				array( 'ag-pd-fonts' ),
				AG_PREMIUM_DOMICILE_VERSION
			);
		}
		if ( file_exists( AG_PREMIUM_DOMICILE_DIR . 'assets/premium.js' ) ) {
			wp_enqueue_script(
				'ag-premium-domicile-script',
				AG_PREMIUM_DOMICILE_URL . 'assets/premium.js',
				array(),
				AG_PREMIUM_DOMICILE_VERSION,
				true
			);
			wp_localize_script( 'ag-premium-domicile-script', 'agPdData', array(
				'devisUrl'    => esc_url( home_url( '/devis/' ) ),
				'devisLabel'  => (string) get_theme_mod( 'ag_pd_float_label', 'Devis gratuit' ),
				'showFloat'   => (bool) get_theme_mod( 'ag_pd_show_float', true ),
				'showTrust'   => (bool) get_theme_mod( 'ag_pd_show_trust', true ),
				'trust'       => array(
					(string) get_theme_mod( 'ag_pd_trust_1', 'Agréé services à la personne' ),
					(string) get_theme_mod( 'ag_pd_trust_2', 'Crédit d’impôt 50 %' ),
					(string) get_theme_mod( 'ag_pd_trust_3', '7j/7, jour & nuit' ),
				),
			) );
		}
	}

	public function register_customizer( $wp_customize ) {
		$wp_customize->add_section( 'ag_pd_section', array(
			'title'    => __( 'AG Premium Domicile', 'ag-premium-domicile' ),
			'priority' => 33,
		) );

		// Mode test (preview sans licence réelle).
		$wp_customize->add_setting( 'ag_pd_force_active', array(
			'default'           => false,
			'sanitize_callback' => function ( $v ) { return (bool) $v; },
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_pd_force_active', array(
			'label'       => __( 'Mode test — activer Premium même sans licence', 'ag-premium-domicile' ),
			'description' => __( 'À désactiver en production une fois la licence Premium validée.', 'ag-premium-domicile' ),
			'section'     => 'ag_pd_section',
			'type'        => 'checkbox',
		) );

		// Bouton flottant "Devis".
		$wp_customize->add_setting( 'ag_pd_show_float', array(
			'default'           => true,
			'sanitize_callback' => function ( $v ) { return (bool) $v; },
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_pd_show_float', array(
			'label'   => __( 'Afficher le bouton « Devis » flottant', 'ag-premium-domicile' ),
			'section' => 'ag_pd_section',
			'type'    => 'checkbox',
		) );
		$wp_customize->add_setting( 'ag_pd_float_label', array(
			'default'           => 'Devis gratuit',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_pd_float_label', array(
			'label'   => __( 'Texte du bouton flottant', 'ag-premium-domicile' ),
			'section' => 'ag_pd_section',
			'type'    => 'text',
		) );

		// Bandeau de confiance (hero).
		$wp_customize->add_setting( 'ag_pd_show_trust', array(
			'default'           => true,
			'sanitize_callback' => function ( $v ) { return (bool) $v; },
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_pd_show_trust', array(
			'label'   => __( 'Afficher le bandeau de confiance sur le hero', 'ag-premium-domicile' ),
			'section' => 'ag_pd_section',
			'type'    => 'checkbox',
		) );
		foreach ( array(
			'ag_pd_trust_1' => array( 'Agréé services à la personne', __( 'Bandeau confiance — 1', 'ag-premium-domicile' ) ),
			'ag_pd_trust_2' => array( 'Crédit d’impôt 50 %', __( 'Bandeau confiance — 2', 'ag-premium-domicile' ) ),
			'ag_pd_trust_3' => array( '7j/7, jour & nuit', __( 'Bandeau confiance — 3', 'ag-premium-domicile' ) ),
		) as $key => $meta ) {
			$wp_customize->add_setting( $key, array(
				'default'           => $meta[0],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( $key, array(
				'label'   => $meta[1],
				'section' => 'ag_pd_section',
				'type'    => 'text',
			) );
		}
	}
}
