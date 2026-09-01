<?php
/**
 * AG Starter Barber — Design.
 *
 * Le design enrichi (typo Bebas, hero plein image, titres geants, nav centree,
 * menu plein ecran, cartes epurees) etait auparavant vendu dans le pack Premium.
 * Il fait desormais partie du theme GRATUIT : un template vide ne se vend pas,
 * et c'est lui qui doit donner envie.
 *
 * Le Premium ne porte plus que ce qu'un client ne sait pas faire lui-meme :
 * statistiques, SEO technique, performance.
 *
 * Les assets sont repris tels quels de l'ancien pack Premium :
 *   assets/design.css  (ex premium.css)
 *   assets/design.js   (ex premium.js)
 * Ils sont portes par la classe `ag-bb-active` sur <body>, que le theme ajoute
 * maintenant systematiquement. Cette classe reste partagee avec le pack
 * Business, qui l'ajoute aussi de son cote et construit par-dessus.
 *
 * Les reglages gardent leurs cles historiques `ag_pb_*` : un site deja
 * configure conserve son logo et ses libelles.
 *
 * @package AG_Starter_Barber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Le design est actif pour tout le monde, sans licence.
 */
function ag_barber_design_body_class( $classes ) {
	if ( ! in_array( 'ag-bb-active', $classes, true ) ) {
		$classes[] = 'ag-bb-active';
	}
	return $classes;
}
add_filter( 'body_class', 'ag_barber_design_body_class' );

/**
 * Charge le design. Priorite 25 pour passer apres la feuille principale.
 */
function ag_barber_design_assets() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	$ver = wp_get_theme()->get( 'Version' );

	// Anciennes installations : si le pack Premium charge encore sa copie du
	// design, on ne l'empile pas une seconde fois.
	if ( wp_style_is( 'ag-premium-barber-style', 'enqueued' ) || wp_style_is( 'ag-premium-barber-style', 'registered' ) ) {
		return;
	}

	// Bebas Neue (titres capitales), Cormorant Garamond (italique), Special Elite.
	wp_enqueue_style(
		'ag-barber-design-fonts',
		'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Special+Elite&display=swap',
		array(),
		null
	);

	if ( file_exists( $dir . '/assets/design.css' ) ) {
		wp_enqueue_style(
			'ag-barber-design',
			$uri . '/assets/design.css',
			array( 'ag-starter-barber-style', 'ag-barber-design-fonts' ),
			$ver
		);
	}

	if ( file_exists( $dir . '/assets/design.js' ) ) {
		wp_enqueue_script( 'ag-barber-design', $uri . '/assets/design.js', array(), $ver, true );

		wp_localize_script( 'ag-barber-design', 'agPbData', array(
			'logoUrl'   => (string) get_theme_mod( 'ag_pb_logo_url', '' ),
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
add_action( 'wp_enqueue_scripts', 'ag_barber_design_assets', 25 );

/**
 * Reglages du design dans Apparence > Personnaliser.
 * Memes cles que l'ancien pack Premium : rien n'est perdu.
 */
function ag_barber_design_customizer( $wp_customize ) {

	$wp_customize->add_section( 'ag_pb_logo', array(
		'title'       => __( 'Logo du salon', 'ag-starter-barber' ),
		'description' => __( "Image PNG/JPG/SVG qui remplace l'icone ciseaux par defaut. Uploadez-la d'abord dans Medias, copiez l'URL du fichier et collez-la ici. Recommande : carre transparent, environ 512x512.", 'ag-starter-barber' ),
		'priority'    => 30,
	) );
	$wp_customize->add_setting( 'ag_pb_logo_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'ag_pb_logo_url', array(
		'label'   => __( 'URL du logo', 'ag-starter-barber' ),
		'section' => 'ag_pb_logo',
		'type'    => 'url',
	) );

	$wp_customize->add_section( 'ag_pb_home_content', array(
		'title'       => __( 'Libelles du menu et du QR', 'ag-starter-barber' ),
		'description' => __( 'Renommez les onglets du menu et la legende du QR code.', 'ag-starter-barber' ),
		'priority'    => 31,
	) );

	$fields = array(
		'ag_pb_qr_caption'       => array( 'label' => __( 'Legende du QR code', 'ag-starter-barber' ),   'default' => 'Scannez pour prendre votre ticket' ),
		'ag_pb_nav_services'     => array( 'label' => __( 'Menu — Tarifs', 'ag-starter-barber' ),         'default' => 'Tarifs' ),
		'ag_pb_nav_queue'        => array( 'label' => __( 'Menu — File d\'attente', 'ag-starter-barber' ),'default' => 'File d\'attente' ),
		'ag_pb_nav_team'         => array( 'label' => __( 'Menu — Equipe', 'ag-starter-barber' ),         'default' => 'Équipe' ),
		'ag_pb_nav_gallery'      => array( 'label' => __( 'Menu — Galerie', 'ag-starter-barber' ),        'default' => 'Galerie' ),
		'ag_pb_nav_testimonials' => array( 'label' => __( 'Menu — Avis', 'ag-starter-barber' ),           'default' => 'Avis' ),
		'ag_pb_nav_booking'      => array( 'label' => __( 'Menu — Reserver', 'ag-starter-barber' ),       'default' => 'Réserver' ),
		'ag_pb_nav_contact'      => array( 'label' => __( 'Menu — Contact', 'ag-starter-barber' ),        'default' => 'Contact' ),
	);
	foreach ( $fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_pb_home_content',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'ag_barber_design_customizer' );
