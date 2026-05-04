<?php
/**
 * Customizer AG Starter Association — version riche.
 * Toutes les options sont regroupees dans le panel "Mouvement militant".
 * Sections : Identite, Couleurs, Typographie, Hero, Reseaux sociaux,
 * Bandeau urgence, Mobilisation, Contact, Pied de page.
 *
 * @package AG_Starter_Association
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ag_asso_customize( $wp_customize ) {
	$wp_customize->add_panel( 'ag_asso_panel', array(
		'title'    => __( 'Mouvement militant', 'ag-starter-association' ),
		'priority' => 30,
	) );

	// =====================================================================
	// Identite
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_identity', array(
		'title' => __( 'Identité', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$identity_fields = array(
		'ag_asso_name'       => array( 'label' => 'Nom du mouvement',       'default' => '[Nom du mouvement]' ),
		'ag_asso_slogan'     => array( 'label' => 'Slogan court (header)',  'default' => '[Slogan en quelques mots]' ),
		'ag_asso_baseline'   => array( 'label' => 'Phrase de profession de foi (footer)', 'default' => '[Une phrase qui resume le combat]' ),
	);
	foreach ( $identity_fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_asso_identity',
			'type'    => 'text',
		) );
	}

	// Logo secondaire (footer / mobile)
	$wp_customize->add_setting( 'ag_asso_logo_footer', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'ag_asso_logo_footer', array(
		'label'    => __( 'Logo pied de page (PNG/SVG transparent)', 'ag-starter-association' ),
		'section'  => 'ag_asso_identity',
	) ) );

	// =====================================================================
	// Couleurs
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_colors', array(
		'title' => __( 'Couleurs', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$color_fields = array(
		'ag_asso_color_primary'    => array( 'label' => 'Couleur principale (CTA, accent)',   'default' => '#E10F1A' ),
		'ag_asso_color_primary_dk' => array( 'label' => 'Couleur principale survol',          'default' => '#B30B14' ),
		'ag_asso_color_accent'     => array( 'label' => 'Couleur accent secondaire (jaune)',  'default' => '#FFD23F' ),
		'ag_asso_color_bg'         => array( 'label' => 'Fond principal',                     'default' => '#0A0A0D' ),
		'ag_asso_color_section'    => array( 'label' => 'Fond sections claires',              'default' => '#FFFFFF' ),
		'ag_asso_color_text'       => array( 'label' => 'Couleur texte sur fond clair',       'default' => '#0A0A0D' ),
		'ag_asso_color_text_inv'   => array( 'label' => 'Couleur texte sur fond sombre',      'default' => '#FFFFFF' ),
	);
	foreach ( $color_fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, array(
			'label'   => $f['label'],
			'section' => 'ag_asso_colors',
		) ) );
	}

	// =====================================================================
	// Typographie
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_typo', array(
		'title' => __( 'Typographie', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$wp_customize->add_setting( 'ag_asso_font_heading', array(
		'default'           => 'Anton',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_font_heading', array(
		'label'       => __( 'Police titres (Google Font)', 'ag-starter-association' ),
		'description' => __( 'Ex: Anton, Bebas Neue, Oswald, Archivo Black, Russo One', 'ag-starter-association' ),
		'section'     => 'ag_asso_typo',
		'type'        => 'text',
	) );
	$wp_customize->add_setting( 'ag_asso_font_body', array(
		'default'           => 'Inter',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_font_body', array(
		'label'       => __( 'Police texte (Google Font)', 'ag-starter-association' ),
		'description' => __( 'Ex: Inter, Roboto, Open Sans, Lato, Source Sans 3', 'ag-starter-association' ),
		'section'     => 'ag_asso_typo',
		'type'        => 'text',
	) );
	$wp_customize->add_setting( 'ag_asso_title_uppercase', array(
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'ag_asso_title_uppercase', array(
		'label'   => __( 'Titres en MAJUSCULES', 'ag-starter-association' ),
		'section' => 'ag_asso_typo',
		'type'    => 'checkbox',
	) );

	// =====================================================================
	// Hero / banniere principale
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_hero', array(
		'title' => __( 'Bannière principale (Hero)', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$hero_text = array(
		'ag_asso_hero_title' => array( 'label' => 'Titre hero',     'default' => '[Le grand titre de mobilisation]' ),
		'ag_asso_hero_sub'   => array( 'label' => 'Sous-titre',     'default' => '[Description courte du combat]' ),
		'ag_asso_cta_label'  => array( 'label' => 'Texte bouton principal',     'default' => 'Rejoindre le mouvement' ),
		'ag_asso_cta_url'    => array( 'label' => 'URL bouton principal',       'default' => '' ),
		'ag_asso_cta2_label' => array( 'label' => 'Texte bouton secondaire',    'default' => 'Faire un don' ),
		'ag_asso_cta2_url'   => array( 'label' => 'URL bouton secondaire',      'default' => '' ),
	);
	foreach ( $hero_text as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_asso_hero',
			'type'    => 'text',
		) );
	}
	$wp_customize->add_setting( 'ag_asso_hero_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'ag_asso_hero_image', array(
		'label'   => __( 'Image de fond (1920x1080 recommande)', 'ag-starter-association' ),
		'section' => 'ag_asso_hero',
	) ) );
	$wp_customize->add_setting( 'ag_asso_hero_overlay', array(
		'default'           => 60,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'ag_asso_hero_overlay', array(
		'label'       => __( 'Opacite voile noir sur image (%)', 'ag-starter-association' ),
		'description' => __( '0 = image claire, 100 = noir total', 'ag-starter-association' ),
		'section'     => 'ag_asso_hero',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
	) );
	$wp_customize->add_setting( 'ag_asso_hero_align', array(
		'default'           => 'center',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_hero_align', array(
		'label'   => __( 'Alignement texte hero', 'ag-starter-association' ),
		'section' => 'ag_asso_hero',
		'type'    => 'select',
		'choices' => array(
			'left'   => 'Gauche',
			'center' => 'Centre',
			'right'  => 'Droite',
		),
	) );

	// =====================================================================
	// Reseaux sociaux
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_social', array(
		'title' => __( 'Réseaux sociaux', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$social_fields = array(
		'ag_asso_social_facebook'  => 'Facebook',
		'ag_asso_social_twitter'   => 'X / Twitter',
		'ag_asso_social_instagram' => 'Instagram',
		'ag_asso_social_youtube'   => 'YouTube',
		'ag_asso_social_tiktok'    => 'TikTok',
		'ag_asso_social_telegram'  => 'Telegram',
		'ag_asso_social_whatsapp'  => 'WhatsApp',
		'ag_asso_social_linkedin'  => 'LinkedIn',
		'ag_asso_social_mastodon'  => 'Mastodon',
	);
	foreach ( $social_fields as $key => $label ) {
		$wp_customize->add_setting( $key, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( $key, array(
			'label'       => $label,
			'description' => 'URL complete (https://...) — laisser vide pour cacher',
			'section'     => 'ag_asso_social',
			'type'        => 'url',
		) );
	}

	// =====================================================================
	// Bandeau urgence
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_alert', array(
		'title' => __( 'Bandeau urgence (top)', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$wp_customize->add_setting( 'ag_asso_alert_active', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'ag_asso_alert_active', array(
		'label'   => __( 'Activer le bandeau d\'urgence', 'ag-starter-association' ),
		'section' => 'ag_asso_alert',
		'type'    => 'checkbox',
	) );
	$wp_customize->add_setting( 'ag_asso_alert_text', array(
		'default'           => 'Mobilisation en cours — rejoignez-nous',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_alert_text', array(
		'label'   => __( 'Message du bandeau', 'ag-starter-association' ),
		'section' => 'ag_asso_alert',
		'type'    => 'text',
	) );
	$wp_customize->add_setting( 'ag_asso_alert_link_label', array(
		'default'           => 'En savoir plus',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_alert_link_label', array(
		'label'   => __( 'Texte du lien', 'ag-starter-association' ),
		'section' => 'ag_asso_alert',
		'type'    => 'text',
	) );
	$wp_customize->add_setting( 'ag_asso_alert_link_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'ag_asso_alert_link_url', array(
		'label'   => __( 'URL du lien', 'ag-starter-association' ),
		'section' => 'ag_asso_alert',
		'type'    => 'url',
	) );

	// =====================================================================
	// Mobilisation / compteur
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_mobil', array(
		'title' => __( 'Mobilisation / compteur', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$mobil_fields = array(
		'ag_asso_signatures_count'  => array( 'label' => 'Nombre de signatures actuelles',        'default' => '12 480',        'type' => 'text' ),
		'ag_asso_signatures_target' => array( 'label' => 'Objectif de signatures',                'default' => '50 000',        'type' => 'text' ),
		'ag_asso_signatures_label'  => array( 'label' => 'Libelle du compteur',                   'default' => 'signataires',   'type' => 'text' ),
		'ag_asso_deadline'          => array( 'label' => 'Date butoir (libre)',                   'default' => '31 décembre',   'type' => 'text' ),
		'ag_asso_members_count'     => array( 'label' => 'Nombre d\'adherents',                   'default' => '2 130',         'type' => 'text' ),
		'ag_asso_groups_count'      => array( 'label' => 'Nombre de groupes locaux',              'default' => '47',            'type' => 'text' ),
	);
	foreach ( $mobil_fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_asso_mobil',
			'type'    => $f['type'],
		) );
	}

	// =====================================================================
	// Don / cotisation
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_don', array(
		'title' => __( 'Don & cotisation', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$wp_customize->add_setting( 'ag_asso_don_amounts', array(
		'default'           => '5,20,50,100,250',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_don_amounts', array(
		'label'       => __( 'Montants suggeres (separes par virgules, en €)', 'ag-starter-association' ),
		'section'     => 'ag_asso_don',
		'type'        => 'text',
	) );
	$wp_customize->add_setting( 'ag_asso_don_tax_reduc', array(
		'default'           => 66,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'ag_asso_don_tax_reduc', array(
		'label'       => __( 'Reduction d\'impot (%) — 66 ou 75', 'ag-starter-association' ),
		'description' => __( '66% interet general / 75% aide aux personnes en difficulte', 'ag-starter-association' ),
		'section'     => 'ag_asso_don',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
	) );
	$wp_customize->add_setting( 'ag_fid_cotisation', array(
		'default'           => '20',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_fid_cotisation', array(
		'label'   => __( 'Montant cotisation annuelle (€)', 'ag-starter-association' ),
		'section' => 'ag_asso_don',
		'type'    => 'number',
	) );

	// =====================================================================
	// Identite legale association (utilise par Pack Fidelite)
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_legal', array(
		'title' => __( 'Identité légale (asso loi 1901)', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$legal_fields = array(
		'ag_fid_org_name'    => array( 'label' => 'Raison sociale',          'default' => '' ),
		'ag_fid_org_siret'   => array( 'label' => 'SIRET',                   'default' => '' ),
		'ag_fid_org_rna'     => array( 'label' => 'Numero RNA (W...)',       'default' => '' ),
		'ag_fid_president'   => array( 'label' => 'Nom du/de la président·e','default' => '' ),
		'ag_fid_iban'        => array( 'label' => 'IBAN',                    'default' => '' ),
		'ag_asso_dpo_email'  => array( 'label' => 'Email DPO (RGPD)',        'default' => '' ),
		'ag_asso_host'       => array( 'label' => 'Hebergeur',               'default' => '' ),
	);
	foreach ( $legal_fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_asso_legal',
			'type'    => 'text',
		) );
	}

	// =====================================================================
	// Contact
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_contact', array(
		'title' => __( 'Contact', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$contact_fields = array(
		'ag_asso_address' => '[Adresse du local]',
		'ag_asso_email'   => '[contact@mouvement.fr]',
		'ag_asso_phone'   => '[01 00 00 00 00]',
	);
	foreach ( $contact_fields as $key => $default ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => str_replace( 'ag_asso_', '', $key ),
			'section' => 'ag_asso_contact',
			'type'    => 'text',
		) );
	}

	// =====================================================================
	// Footer
	// =====================================================================
	$wp_customize->add_section( 'ag_asso_footer', array(
		'title' => __( 'Pied de page', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$wp_customize->add_setting( 'ag_asso_footer_copy', array(
		'default'           => 'Site associatif sans publicité — propulsé par les adhérents.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ag_asso_footer_copy', array(
		'label'   => __( 'Phrase pied de page', 'ag-starter-association' ),
		'section' => 'ag_asso_footer',
		'type'    => 'text',
	) );
	$wp_customize->add_setting( 'ag_asso_show_credit', array(
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'ag_asso_show_credit', array(
		'label'   => __( 'Afficher "Theme par Alliance Groupe"', 'ag-starter-association' ),
		'section' => 'ag_asso_footer',
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'ag_asso_customize' );

/**
 * Genere les CSS variables dynamiques + le bandeau urgence depuis
 * les options Customizer. Injecte dans <head>.
 */
function ag_asso_dynamic_css() {
	$primary    = get_theme_mod( 'ag_asso_color_primary',    '#E10F1A' );
	$primary_dk = get_theme_mod( 'ag_asso_color_primary_dk', '#B30B14' );
	$accent     = get_theme_mod( 'ag_asso_color_accent',     '#FFD23F' );
	$bg         = get_theme_mod( 'ag_asso_color_bg',         '#0A0A0D' );
	$section    = get_theme_mod( 'ag_asso_color_section',    '#FFFFFF' );
	$text       = get_theme_mod( 'ag_asso_color_text',       '#0A0A0D' );
	$text_inv   = get_theme_mod( 'ag_asso_color_text_inv',   '#FFFFFF' );
	$font_h     = get_theme_mod( 'ag_asso_font_heading',     'Anton' );
	$font_b     = get_theme_mod( 'ag_asso_font_body',        'Inter' );
	$uppercase  = get_theme_mod( 'ag_asso_title_uppercase',  1 );
	$hero_img   = get_theme_mod( 'ag_asso_hero_image',       '' );
	$hero_ovr   = (int) get_theme_mod( 'ag_asso_hero_overlay', 60 ) / 100;
	$hero_align = get_theme_mod( 'ag_asso_hero_align',       'center' );

	$css  = ':root{';
	$css .= '--asso-red:'         . esc_html( $primary )    . ';';
	$css .= '--asso-red-dark:'    . esc_html( $primary_dk ) . ';';
	$css .= '--asso-yellow:'      . esc_html( $accent )     . ';';
	$css .= '--asso-black:'       . esc_html( $bg )         . ';';
	$css .= '--asso-section-bg:'  . esc_html( $section )    . ';';
	$css .= '--asso-text:'        . esc_html( $text )       . ';';
	$css .= '--asso-text-inv:'    . esc_html( $text_inv )   . ';';
	$css .= '--asso-font-heading:"' . esc_html( $font_h ) . '",Impact,sans-serif;';
	$css .= '--asso-font-body:"'    . esc_html( $font_b ) . '",system-ui,sans-serif;';
	$css .= '}';
	$css .= 'body{font-family:var(--asso-font-body);}';
	$css .= '.ag-asso-section__title,.ag-asso-hero__title,.ag-asso-header__name{font-family:var(--asso-font-heading);}';
	if ( ! $uppercase ) {
		$css .= '.ag-asso-section__title,.ag-asso-hero__title{text-transform:none;}';
	}
	if ( $hero_img ) {
		$css .= '.ag-asso-hero{background-image:linear-gradient(rgba(0,0,0,' . esc_html( $hero_ovr ) . '),rgba(0,0,0,' . esc_html( $hero_ovr ) . ')),url(' . esc_url( $hero_img ) . ');background-size:cover;background-position:center;}';
	}
	$css .= '.ag-asso-hero__inner{text-align:' . esc_html( $hero_align ) . ';}';

	echo "<style id=\"ag-asso-customizer\">\n" . $css . "\n</style>\n";
}
add_action( 'wp_head', 'ag_asso_dynamic_css', 100 );

/**
 * Charge dynamiquement les Google Fonts choisies par l'admin.
 */
function ag_asso_dynamic_fonts() {
	$font_h = get_theme_mod( 'ag_asso_font_heading', 'Anton' );
	$font_b = get_theme_mod( 'ag_asso_font_body',    'Inter' );
	$family = array();
	if ( $font_h ) { $family[] = str_replace( ' ', '+', $font_h ); }
	if ( $font_b ) { $family[] = str_replace( ' ', '+', $font_b ) . ':wght@400;500;600;700;800'; }
	if ( empty( $family ) ) return;
	$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $family ) . '&display=swap';
	wp_enqueue_style( 'ag-asso-fonts-dyn', $url, array(), null );
}
add_action( 'wp_enqueue_scripts', 'ag_asso_dynamic_fonts', 5 );
