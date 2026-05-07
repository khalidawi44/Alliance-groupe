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
		// Mode test : toggle Customizer permet de forcer l'activation
		// sans licence Business (utile pour preview/dev). En prod,
		// laisser cette option a OFF et utiliser la vraie licence.
		if ( get_theme_mod( 'ag_bb_force_active', false ) ) {
			return true;
		}
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
				// Phrases d'intro (leads) sous chaque titre H2 — éditables via Customizer.
				'teamLead'         => (string) get_theme_mod( 'ag_bb_team_lead', 'Des barbiers passionnés, formés à la vieille école américaine.' ),
				'galleryLead'      => (string) get_theme_mod( 'ag_bb_gallery_lead', 'Quelques coupes signées par l\'équipe.' ),
				'testimonialsLead' => (string) get_theme_mod( 'ag_bb_testimonials_lead', 'Avis clients vérifiés.' ),
				'bookingLead'      => (string) get_theme_mod( 'ag_bb_booking_lead', 'Plus simple qu\'un coup de fil. Confirmation immédiate par email.' ),
				'contactLead'      => (string) get_theme_mod( 'ag_bb_contact_lead', 'Le salon est ouvert du mardi au samedi.' ),
				// Titres H2 (pre + em).
				'teamTitlePre'         => (string) get_theme_mod( 'ag_bb_team_title_pre', 'L\'' ),
				'teamTitleEm'          => (string) get_theme_mod( 'ag_bb_team_title_em', 'équipe' ),
				'galleryTitlePre'      => (string) get_theme_mod( 'ag_bb_gallery_title_pre', 'La' ),
				'galleryTitleEm'       => (string) get_theme_mod( 'ag_bb_gallery_title_em', 'galerie' ),
				'testimonialsTitlePre' => (string) get_theme_mod( 'ag_bb_testimonials_title_pre', 'Ils' ),
				'testimonialsTitleEm'  => (string) get_theme_mod( 'ag_bb_testimonials_title_em', 'parlent de nous' ),
				'bookingTitlePre'      => (string) get_theme_mod( 'ag_bb_booking_title_pre', 'Réserver un' ),
				'bookingTitleEm'       => (string) get_theme_mod( 'ag_bb_booking_title_em', 'créneau' ),
				'contactTitlePre'      => (string) get_theme_mod( 'ag_bb_contact_title_pre', 'Nous' ),
				'contactTitleEm'       => (string) get_theme_mod( 'ag_bb_contact_title_em', 'trouver' ),
				// Booking form labels + CTA.
				'bookingLabelDate'    => (string) get_theme_mod( 'ag_bb_booking_label_date',    'Date' ),
				'bookingLabelSlot'    => (string) get_theme_mod( 'ag_bb_booking_label_slot',    'Créneau' ),
				'bookingLabelService' => (string) get_theme_mod( 'ag_bb_booking_label_service', 'Prestation' ),
				'bookingLabelBarber'  => (string) get_theme_mod( 'ag_bb_booking_label_barber',  'Barbier' ),
				'bookingLabelName'    => (string) get_theme_mod( 'ag_bb_booking_label_name',    'Prénom' ),
				'bookingLabelPhone'   => (string) get_theme_mod( 'ag_bb_booking_label_phone',   'Téléphone' ),
				'bookingPlaceholderSlot'    => (string) get_theme_mod( 'ag_bb_booking_placeholder_slot',    '— Choisir un créneau —' ),
				'bookingPlaceholderBarber'  => (string) get_theme_mod( 'ag_bb_booking_placeholder_barber',  '— N\'importe lequel —' ),
				'bookingSubmit'       => (string) get_theme_mod( 'ag_bb_booking_submit', 'Confirmer la réservation' ),
				'bookingNote'         => (string) get_theme_mod( 'ag_bb_booking_note', 'En cliquant, vous acceptez d\'être recontacté pour confirmation.' ),
				// Contact block headings.
				'contactCoordHeading' => (string) get_theme_mod( 'ag_bb_contact_coord_heading', 'Coordonnées' ),
				'contactHoursHeading' => (string) get_theme_mod( 'ag_bb_contact_hours_heading', 'Horaires' ),
				// Misc.
				'experienceSuffix'    => (string) get_theme_mod( 'ag_bb_experience_suffix', 'ans d\'expérience' ),
				'premiumBadge'        => (string) get_theme_mod( 'ag_bb_premium_badge', 'Premium' ),
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
			// Photos d'exemple Unsplash : barbershops, coupes, fade,
			// barbe, rasage. Mix portrait + atelier + outils.
			'https://images.unsplash.com/photo-1521490683330-cd7d6b4f1bef?w=1200&q=85',
			'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=1200&q=85',
			'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=1200&q=85',
			'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?w=1200&q=85',
			'https://images.unsplash.com/photo-1605497788044-5a32c7078486?w=1200&q=85',
			'https://images.unsplash.com/photo-1626015449112-43e9a8a4f0e5?w=1200&q=85',
			'https://images.unsplash.com/photo-1517832207067-4db24a2ae47c?w=1200&q=85',
			'https://images.unsplash.com/photo-1565061828011-282424e8c14d?w=1200&q=85',
			'https://images.unsplash.com/photo-1493256338651-d82f7acb2b38?w=1200&q=85',
			'https://images.unsplash.com/photo-1635273051936-83f4b6e478c0?w=1200&q=85',
			'https://images.unsplash.com/photo-1542833805-2b2b29ab66ec?w=1200&q=85',
			'https://images.unsplash.com/photo-1593702288056-f173a2207898?w=1200&q=85',
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

		// Section : Mode test / Activation
		$wp_customize->add_section( 'ag_bb_activation', array(
			'title'       => __( 'Activation', 'ag-business-barber' ),
			'description' => __( "Si tu n'as pas encore de licence Business installée, active le mode test ci-dessous pour prévisualiser le rendu.", 'ag-business-barber' ),
			'panel'       => 'ag_bb_panel',
		) );
		$wp_customize->add_setting( 'ag_bb_force_active', array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'ag_bb_force_active', array(
			'type'        => 'checkbox',
			'label'       => __( 'Mode test — activer même sans licence Business', 'ag-business-barber' ),
			'description' => __( 'À désactiver en production une fois la vraie licence Business validée.', 'ag-business-barber' ),
			'section'     => 'ag_bb_activation',
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

		// Section : Contenu accueil — textes (phrases d'intro sous chaque H2).
		$wp_customize->add_section( 'ag_bb_home_content', array(
			'title'       => __( 'Contenu accueil — textes', 'ag-business-barber' ),
			'description' => __( 'Personnalisez les phrases d\'introduction sous chaque titre des sections injectées par Business Barber.', 'ag-business-barber' ),
			'panel'       => 'ag_bb_panel',
		) );
		$lead_fields = array(
			// Équipe
			'ag_bb_team_title_pre'    => array( 'label' => __( 'Équipe — début du titre', 'ag-business-barber' ),     'default' => 'L\'',     'type' => 'text' ),
			'ag_bb_team_title_em'     => array( 'label' => __( 'Équipe — mot en accent', 'ag-business-barber' ),       'default' => 'équipe',  'type' => 'text' ),
			'ag_bb_team_lead'         => array( 'label' => __( 'Équipe — phrase d\'intro', 'ag-business-barber' ),     'default' => 'Des barbiers passionnés, formés à la vieille école américaine.', 'type' => 'textarea' ),
			'ag_bb_experience_suffix' => array( 'label' => __( 'Équipe — suffixe expérience', 'ag-business-barber' ),  'default' => 'ans d\'expérience', 'type' => 'text' ),
			// Galerie
			'ag_bb_gallery_title_pre' => array( 'label' => __( 'Galerie — début du titre', 'ag-business-barber' ),     'default' => 'La',       'type' => 'text' ),
			'ag_bb_gallery_title_em'  => array( 'label' => __( 'Galerie — mot en accent', 'ag-business-barber' ),      'default' => 'galerie',  'type' => 'text' ),
			'ag_bb_gallery_lead'      => array( 'label' => __( 'Galerie — phrase d\'intro', 'ag-business-barber' ),    'default' => 'Quelques coupes signées par l\'équipe.', 'type' => 'textarea' ),
			// Témoignages
			'ag_bb_testimonials_title_pre' => array( 'label' => __( 'Témoignages — début du titre', 'ag-business-barber' ),  'default' => 'Ils',             'type' => 'text' ),
			'ag_bb_testimonials_title_em'  => array( 'label' => __( 'Témoignages — mot en accent', 'ag-business-barber' ),    'default' => 'parlent de nous', 'type' => 'text' ),
			'ag_bb_testimonials_lead'      => array( 'label' => __( 'Témoignages — phrase d\'intro', 'ag-business-barber' ),  'default' => 'Avis clients vérifiés.', 'type' => 'textarea' ),
			// Réservation
			'ag_bb_booking_title_pre' => array( 'label' => __( 'Réservation — début du titre', 'ag-business-barber' ), 'default' => 'Réserver un', 'type' => 'text' ),
			'ag_bb_booking_title_em'  => array( 'label' => __( 'Réservation — mot en accent', 'ag-business-barber' ),  'default' => 'créneau',     'type' => 'text' ),
			'ag_bb_booking_lead'      => array( 'label' => __( 'Réservation — phrase d\'intro', 'ag-business-barber' ),'default' => 'Plus simple qu\'un coup de fil. Confirmation immédiate par email.', 'type' => 'textarea' ),
			'ag_bb_booking_label_date'    => array( 'label' => __( 'Réservation — label Date', 'ag-business-barber' ),   'default' => 'Date',       'type' => 'text' ),
			'ag_bb_booking_label_slot'    => array( 'label' => __( 'Réservation — label Créneau', 'ag-business-barber' ),'default' => 'Créneau',    'type' => 'text' ),
			'ag_bb_booking_label_service' => array( 'label' => __( 'Réservation — label Prestation', 'ag-business-barber' ), 'default' => 'Prestation', 'type' => 'text' ),
			'ag_bb_booking_label_barber'  => array( 'label' => __( 'Réservation — label Barbier', 'ag-business-barber' ),'default' => 'Barbier',    'type' => 'text' ),
			'ag_bb_booking_label_name'    => array( 'label' => __( 'Réservation — label Prénom', 'ag-business-barber' ), 'default' => 'Prénom',     'type' => 'text' ),
			'ag_bb_booking_label_phone'   => array( 'label' => __( 'Réservation — label Téléphone', 'ag-business-barber' ), 'default' => 'Téléphone', 'type' => 'text' ),
			'ag_bb_booking_placeholder_slot'    => array( 'label' => __( 'Réservation — placeholder Créneau', 'ag-business-barber' ), 'default' => '— Choisir un créneau —', 'type' => 'text' ),
			'ag_bb_booking_placeholder_barber'  => array( 'label' => __( 'Réservation — placeholder Barbier', 'ag-business-barber' ), 'default' => '— N\'importe lequel —', 'type' => 'text' ),
			'ag_bb_booking_submit'        => array( 'label' => __( 'Réservation — bouton submit', 'ag-business-barber' ),'default' => 'Confirmer la réservation', 'type' => 'text' ),
			'ag_bb_booking_note'          => array( 'label' => __( 'Réservation — note RGPD/contact', 'ag-business-barber' ), 'default' => 'En cliquant, vous acceptez d\'être recontacté pour confirmation.', 'type' => 'textarea' ),
			// Contact
			'ag_bb_contact_title_pre' => array( 'label' => __( 'Contact — début du titre', 'ag-business-barber' ),     'default' => 'Nous',     'type' => 'text' ),
			'ag_bb_contact_title_em'  => array( 'label' => __( 'Contact — mot en accent', 'ag-business-barber' ),      'default' => 'trouver',  'type' => 'text' ),
			'ag_bb_contact_lead'      => array( 'label' => __( 'Contact / horaires — phrase d\'intro', 'ag-business-barber' ), 'default' => 'Le salon est ouvert du mardi au samedi.', 'type' => 'textarea' ),
			'ag_bb_contact_coord_heading' => array( 'label' => __( 'Contact — titre bloc Coordonnées', 'ag-business-barber' ), 'default' => 'Coordonnées', 'type' => 'text' ),
			'ag_bb_contact_hours_heading' => array( 'label' => __( 'Contact — titre bloc Horaires', 'ag-business-barber' ),    'default' => 'Horaires',    'type' => 'text' ),
			// Misc
			'ag_bb_premium_badge'     => array( 'label' => __( 'Badge bas-droite (Premium)', 'ag-business-barber' ),    'default' => 'Premium',  'type' => 'text' ),
		);
		foreach ( $lead_fields as $key => $f ) {
			$type = isset( $f['type'] ) ? $f['type'] : 'textarea';
			$wp_customize->add_setting( $key, array(
				'default'           => $f['default'],
				'sanitize_callback' => $type === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( $key, array(
				'label'   => $f['label'],
				'section' => 'ag_bb_home_content',
				'type'    => $type,
			) );
		}
	}
}
