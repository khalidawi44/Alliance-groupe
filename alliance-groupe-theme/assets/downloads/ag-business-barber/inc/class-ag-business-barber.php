<?php
/**
 * Main Business Barber class. Singleton.
 *
 * @package AG_Business_Barber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Business_Barber {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 30 );
	}

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
		$classes[] = 'ag-bb-active';
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}
		// Fonts industrielles : Bebas Neue (display uppercase) +
		// Cormorant Garamond (italic chic) + Special Elite (typewriter
		// pour le côté vintage/old-school).
		wp_enqueue_style(
			'ag-bb-fonts',
			'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Special+Elite&display=swap',
			array(),
			AG_BUSINESS_BARBER_VERSION
		);
		if ( file_exists( AG_BUSINESS_BARBER_DIR . 'assets/business.css' ) ) {
			wp_enqueue_style(
				'ag-business-barber-style',
				AG_BUSINESS_BARBER_URL . 'assets/business.css',
				array( 'ag-bb-fonts' ),
				AG_BUSINESS_BARBER_VERSION
			);
		}
		if ( file_exists( AG_BUSINESS_BARBER_DIR . 'assets/business.js' ) ) {
			wp_enqueue_script(
				'ag-business-barber-script',
				AG_BUSINESS_BARBER_URL . 'assets/business.js',
				array(),
				AG_BUSINESS_BARBER_VERSION,
				true
			);
			wp_localize_script( 'ag-business-barber-script', 'agBbData', array(
				'team'         => $this->get_team_data(),
				'gallery'      => $this->get_gallery_data(),
				'testimonials' => $this->get_testimonials_data(),
				'shopAddress'  => (string) get_theme_mod( 'ag_bb_address', '12 rue des Coiffeurs, 75011 Paris' ),
				'shopPhone'    => (string) get_theme_mod( 'ag_bb_phone', '01 23 45 67 89' ),
				'shopInsta'    => (string) get_theme_mod( 'ag_bb_insta', '@alliancebarber' ),
				'shopHours'    => $this->get_hours_data(),
				'bookingSlots' => array( '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00' ),
			) );
		}
	}

	private function get_team_data() {
		return array(
			array(
				'name'       => 'JIMMY "LE FADER"',
				'role'       => 'Barbier fondateur',
				'years'      => 18,
				'specialty'  => 'Dégradés américains, fade skin, design lines',
				'insta'      => '@jimmy_fade',
				'photo'      => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=900&q=85',
			),
			array(
				'name'       => 'TONI "LA LAME"',
				'role'       => 'Maître rasoir',
				'years'      => 12,
				'specialty'  => 'Rasage traditionnel, serviette chaude, taille de barbe',
				'insta'      => '@toni_blade',
				'photo'      => 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=900&q=85',
			),
			array(
				'name'       => 'ENZO',
				'role'       => 'Barbier senior',
				'years'      => 8,
				'specialty'  => 'Coupes classiques, pompadour, side-part',
				'insta'      => '@enzo_cuts',
				'photo'      => 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?w=900&q=85',
			),
			array(
				'name'       => 'MAYA',
				'role'       => 'Barbière',
				'years'      => 5,
				'specialty'  => 'Coupes femme courtes, undercuts, colorations',
				'insta'      => '@maya_clipper',
				'photo'      => 'https://images.unsplash.com/photo-1580618864180-f6d7d39b8ff6?w=900&q=85',
			),
		);
	}

	private function get_gallery_data() {
		return array(
			'https://images.unsplash.com/photo-1521490683330-cd7d6b4f1bef?w=1200&q=85',
			'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=1200&q=85',
			'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=1200&q=85',
			'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?w=1200&q=85',
			'https://images.unsplash.com/photo-1605497788044-5a32c7078486?w=1200&q=85',
			'https://images.unsplash.com/photo-1626015449112-43e9a8a4f0e5?w=1200&q=85',
			'https://images.unsplash.com/photo-1517832207067-4db24a2ae47c?w=1200&q=85',
			'https://images.unsplash.com/photo-1565061828011-282424e8c14d?w=1200&q=85',
		);
	}

	private function get_testimonials_data() {
		return array(
			array(
				'quote' => 'Le meilleur fade que j\'aie eu de ma vie. Jimmy est un artiste, pas juste un barbier. Je viens depuis 2 ans, jamais déçu.',
				'name'  => 'Karim B.',
				'role'  => 'Client fidèle',
			),
			array(
				'quote' => 'Le rasage à l\'ancienne avec serviette chaude par Toni, c\'est un vrai moment. On en sort comme neuf, vraiment.',
				'name'  => 'Mathieu R.',
				'role'  => 'Premier rasage traditionnel',
			),
			array(
				'quote' => 'Ambiance vintage, musique au top, café offert. C\'est pas juste une coupe, c\'est une expérience.',
				'name'  => 'Antoine D.',
				'role'  => 'Régulier depuis 6 mois',
			),
			array(
				'quote' => 'Maya m\'a refait toute ma coupe après un raté ailleurs. Pro, à l\'écoute, et le résultat est dingue.',
				'name'  => 'Léa M.',
				'role'  => 'Cliente — undercut',
			),
		);
	}

	private function get_hours_data() {
		return array(
			array( 'day' => 'Lundi',    'hours' => 'Fermé' ),
			array( 'day' => 'Mardi',    'hours' => '09h — 19h' ),
			array( 'day' => 'Mercredi', 'hours' => '09h — 19h' ),
			array( 'day' => 'Jeudi',    'hours' => '09h — 20h' ),
			array( 'day' => 'Vendredi', 'hours' => '09h — 20h' ),
			array( 'day' => 'Samedi',   'hours' => '08h30 — 19h30' ),
			array( 'day' => 'Dimanche', 'hours' => 'Fermé' ),
		);
	}

	public function register_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'ag_bb_panel', array(
			'title'    => __( 'AG Business Barber', 'ag-business-barber' ),
			'priority' => 220,
		) );
		$wp_customize->add_section( 'ag_bb_contact', array(
			'title' => __( 'Contact & adresse', 'ag-business-barber' ),
			'panel' => 'ag_bb_panel',
		) );
		$fields = array(
			'ag_bb_address' => array( 'label' => __( 'Adresse', 'ag-business-barber' ), 'default' => '12 rue des Coiffeurs, 75011 Paris' ),
			'ag_bb_phone'   => array( 'label' => __( 'Téléphone', 'ag-business-barber' ), 'default' => '01 23 45 67 89' ),
			'ag_bb_insta'   => array( 'label' => __( 'Instagram (@handle)', 'ag-business-barber' ), 'default' => '@alliancebarber' ),
		);
		foreach ( $fields as $key => $f ) {
			$wp_customize->add_setting( $key, array(
				'default'           => $f['default'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( $key, array(
				'label'   => $f['label'],
				'section' => 'ag_bb_contact',
				'type'    => 'text',
			) );
		}
	}
}
