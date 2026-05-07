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
			// Default logo : on cherche dans la mediatheque WP
			// (wp-content/uploads/<year>/<month>/logo.png) puis fallback
			// racine. Override via Customizer toujours dispo.
			$default_logo = home_url( '/wp-content/uploads/2026/05/logo.png' );
			wp_localize_script( 'ag-premium-barber-script', 'agPbData', array(
				'logoUrl' => (string) get_theme_mod( 'ag_pb_logo_url', $default_logo ),
				// Phrases / leads éditables via Customizer.
				'qrCaption' => (string) get_theme_mod( 'ag_pb_qr_caption', 'Scannez pour prendre votre ticket' ),
				'navLabels' => array(
					'services'           => (string) get_theme_mod( 'ag_pb_nav_services',     'Tarifs' ),
					'queue'              => (string) get_theme_mod( 'ag_pb_nav_queue',        'File d\'attente' ),
					'ag-bb-team'         => (string) get_theme_mod( 'ag_pb_nav_team',         'Équipe' ),
					'ag-bb-gallery'      => (string) get_theme_mod( 'ag_pb_nav_gallery',      'Galerie' ),
					'ag-bb-testimonials' => (string) get_theme_mod( 'ag_pb_nav_testimonials', 'Avis' ),
					'ag-bb-booking'      => (string) get_theme_mod( 'ag_pb_nav_booking',      'Réserver' ),
					'ag-bb-contact'      => (string) get_theme_mod( 'ag_pb_nav_contact',      'Contact' ),
				),
			) );
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

		// Section : Logo personnalise
		$wp_customize->add_section( 'ag_pb_logo', array(
			'title'       => __( 'Logo barber', 'ag-premium-barber' ),
			'description' => __( "URL d'une image PNG/JPG/SVG qui remplace l'icone ciseaux par defaut. Uploader d'abord l'image dans Medias > Bibliotheque, copier le 'URL du fichier', coller ici. Recommande : carre transparent (PNG/SVG), ~512x512.", 'ag-premium-barber' ),
			'panel'       => 'ag_pb_panel',
		) );
		$wp_customize->add_setting( 'ag_pb_logo_url', array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_pb_logo_url', array(
			'label'   => __( 'URL du logo', 'ag-premium-barber' ),
			'section' => 'ag_pb_logo',
			'type'    => 'url',
		) );

		// Section : Contenu accueil — textes (phrases d'intro injectées par Premium).
		$wp_customize->add_section( 'ag_pb_home_content', array(
			'title'       => __( 'Contenu accueil — textes', 'ag-premium-barber' ),
			'description' => __( 'Personnalisez les phrases ajoutées par Premium Barber sur la home.', 'ag-premium-barber' ),
			'panel'       => 'ag_pb_panel',
		) );
		$ag_pb_home_fields = array(
			'ag_pb_qr_caption'       => array( 'label' => __( 'Légende du QR code (file d\'attente)', 'ag-premium-barber' ), 'default' => 'Scannez pour prendre votre ticket', 'type' => 'text' ),
			'ag_pb_nav_services'     => array( 'label' => __( 'Nav — onglet Tarifs', 'ag-premium-barber' ),                  'default' => 'Tarifs',         'type' => 'text' ),
			'ag_pb_nav_queue'        => array( 'label' => __( 'Nav — onglet File d\'attente', 'ag-premium-barber' ),         'default' => 'File d\'attente','type' => 'text' ),
			'ag_pb_nav_team'         => array( 'label' => __( 'Nav — onglet Équipe', 'ag-premium-barber' ),                  'default' => 'Équipe',         'type' => 'text' ),
			'ag_pb_nav_gallery'      => array( 'label' => __( 'Nav — onglet Galerie', 'ag-premium-barber' ),                 'default' => 'Galerie',        'type' => 'text' ),
			'ag_pb_nav_testimonials' => array( 'label' => __( 'Nav — onglet Avis', 'ag-premium-barber' ),                    'default' => 'Avis',           'type' => 'text' ),
			'ag_pb_nav_booking'      => array( 'label' => __( 'Nav — onglet Réserver', 'ag-premium-barber' ),                'default' => 'Réserver',       'type' => 'text' ),
			'ag_pb_nav_contact'      => array( 'label' => __( 'Nav — onglet Contact', 'ag-premium-barber' ),                 'default' => 'Contact',        'type' => 'text' ),
		);
		foreach ( $ag_pb_home_fields as $ag_pb_key => $ag_pb_f ) {
			$wp_customize->add_setting( $ag_pb_key, array(
				'default'           => $ag_pb_f['default'],
				'sanitize_callback' => $ag_pb_f['type'] === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( $ag_pb_key, array(
				'label'   => $ag_pb_f['label'],
				'section' => 'ag_pb_home_content',
				'type'    => $ag_pb_f['type'],
			) );
		}
	}
}
