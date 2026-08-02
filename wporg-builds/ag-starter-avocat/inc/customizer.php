<?php
/**
 * AG Starter Avocat Customizer.
 *
 * Registers settings, sections and controls under Appearance >
 * Customize so users can change colors, typography, hero text and
 * footer text live, without editing any code — same experience
 * as Astra, OceanWP or Kadence.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values per theme flavour.
 *
 * @return array
 */
function ag_starter_avocat_customizer_defaults() {
	return array(
		// Colors.
		'ag_color_accent'       => '#c9a96e',
		'ag_color_background'   => '#0a0e1a',
		'ag_color_panel'        => '#131826',
		'ag_color_border'       => '#1f2740',
		'ag_color_text'         => '#e0e0e0',
		'ag_color_heading'      => '#ffffff',
		'ag_color_muted'        => '#aaaaaa',
		// Typography.
		'ag_font_family'        => 'system',
		'ag_font_base_size'     => 16,
		'ag_font_heading_scale' => 'default',
		// Hero.
		'ag_hero_show'          => true,
		'ag_hero_prefix'        => 'Law Firm',
		'ag_hero_brand'         => '[Attorney Name]',
		'ag_hero_subtitle'      => 'Attorney at law: legal advice and defense of your interests in complete confidentiality.',
		'ag_hero_button'        => 'Book an appointment',
		'ag_hero_button_url'    => '/rendez-vous/',
		// Footer.
		'ag_footer_copyright'   => '',
		'ag_footer_credits'     => true,
		// Cabinet — informations administratives.
		'ag_cabinet_phone'      => '01 23 45 67 89',
		'ag_cabinet_emergency'  => '',
		'ag_cabinet_email'      => 'contact@votre-cabinet.fr',
		'ag_cabinet_address'    => "15 Palace Boulevard\n75001 Paris",
		'ag_cabinet_hours'      => "Monday - Friday: 9am - 7pm\nSaturday: by appointment\nVideo call available",
		'ag_cabinet_map_embed'  => '',
		'ag_cabinet_rpva'       => '',
		// Le Maître.
		'ag_maitre_show'        => true,
		'ag_maitre_name'        => '[Attorney Name]',
		'ag_maitre_barreau'     => 'Admitted to the Paris Bar',
		'ag_maitre_year'        => '2010',
		'ag_maitre_bio'         => "An attorney at the bar for over fifteen years, I assist individuals and businesses with rigor, attentiveness and discretion. My approach: analyze each case in depth, clearly explain your options, and build the most effective strategy with you.",
		'ag_maitre_specialties' => 'Business law · Employment law · Family law',
		'ag_maitre_photo'       => '',
		// Honoraires.
		'ag_honoraires_show'        => true,
		'ag_honoraires_first_label' => 'First appointment',
		'ag_honoraires_first_price' => '80€ HT',
		'ag_honoraires_first_desc'  => 'A one-hour initial consultation to review your case and propose a strategy.',
		'ag_honoraires_pack_label'  => 'Advisory package',
		'ag_honoraires_pack_price'  => 'On quote',
		'ag_honoraires_pack_desc'   => 'An all-inclusive package for cases defined in advance, with no surprises.',
		'ag_honoraires_hour_label'  => 'Time-based fees',
		'ag_honoraires_hour_price'  => '180€ HT/h',
		'ag_honoraires_hour_desc'   => 'For complex cases, transparent billing with a detailed statement.',
		'ag_honoraires_note'        => 'All fees are provided in writing before any commitment. A quote and a fee agreement are mandatory.',
		// RDV form.
		'ag_rdv_show'             => true,
		'ag_rdv_title'            => 'Book an appointment',
		'ag_rdv_subtitle'         => 'First confidential appointment within 48 business hours. Your request is handled directly by the firm.',
		'ag_rdv_recipient_email'  => '',
		'ag_rdv_rgpd_text'        => 'I agree that my data will be used only to process my appointment request, in accordance with the GDPR. No data is shared with third parties.',
		// RGPD / footer legal.
		'ag_rgpd_mention'       => 'Firm registered with the RPVA. Personal data processed in accordance with the GDPR. Absolute confidentiality guaranteed by attorney-client privilege.',
		// Home section leads (phrases d'intro sous les titres H2).
		'ag_avocat_domaines_lead'   => 'Advice and representation for individuals and businesses across the main areas of law.',
		'ag_avocat_honoraires_lead' => 'Full pricing transparency: no bad surprises, a written quote before any commitment.',
		'ag_avocat_cabinet_lead'    => 'Consultation at the office, by video or by phone.',
		// Section H2 titles + supplementary labels.
		'ag_avocat_domaines_title'      => 'Practice areas',
		'ag_avocat_honoraires_title'    => 'Fees',
		'ag_avocat_cabinet_title'       => 'The firm',
		'ag_avocat_maitre_tag'          => 'The Attorney',
		'ag_avocat_maitre_year_prefix'  => 'Admitted since',
		'ag_avocat_maitre_specialties_label' => 'Specialties:',
		'ag_avocat_cabinet_address_heading'  => 'Address',
		'ag_avocat_cabinet_hours_heading'    => 'Hours',
		'ag_avocat_cabinet_contact_heading'  => 'Contact',
		'ag_avocat_cabinet_emergency_label'  => 'Police custody 24/7:',
		'ag_avocat_domaines_empty'           => 'No practice area has been published yet.',
		'ag_avocat_domaines_empty_btn'       => 'Add a first practice area',
		'ag_avocat_domaines_empty_hint'      => 'Tip: create 4 to 6 practice areas (Business law, Employment law, Family law, Real-estate law...) each with an emoji and 3 example cases.',
		// RDV form labels.
		'ag_avocat_rdv_label_prenom'    => 'First name',
		'ag_avocat_rdv_label_nom'       => 'Name',
		'ag_avocat_rdv_label_email'     => 'Email',
		'ag_avocat_rdv_label_tel'       => 'Phone',
		'ag_avocat_rdv_label_domaine'   => 'Relevant area',
		'ag_avocat_rdv_label_format'    => 'Preferred format',
		'ag_avocat_rdv_label_message'   => 'Case description (in a few lines)',
		'ag_avocat_rdv_domaine_select'  => '— Select —',
		'ag_avocat_rdv_domaine_other'   => 'Other / to be determined',
		'ag_avocat_rdv_format_cabinet'  => 'At the office',
		'ag_avocat_rdv_format_visio'    => 'By video',
		'ag_avocat_rdv_format_phone'    => 'By phone',
		'ag_avocat_rdv_submit_label'    => 'Send my request →',
		'ag_avocat_rdv_legal_note'      => 'Confidential request protected by attorney-client privilege. Reply within 48 business hours.',
	);
}

/**
 * Retrieve a customizer setting with its default fallback.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function ag_starter_avocat_get_option( $key ) {
	$defaults = ag_starter_avocat_customizer_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( $key, $default );
}

/**
 * Register the customizer panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function ag_starter_avocat_customize_register( $wp_customize ) {
	$defaults = ag_starter_avocat_customizer_defaults();

	// Panel.
	$wp_customize->add_panel(
		'ag_starter_panel',
		array(
			'title'       => esc_html__( 'AG Starter — Customization', 'ag-starter-avocat' ),
			'description' => esc_html__( 'Change the colors, typography and key texts of your theme right here. No code required.', 'ag-starter-avocat' ),
			'priority'    => 30,
		)
	);

	// ─── Section: Améliorer mon thème (upgrade promo) ───
	$wp_customize->add_section(
		'ag_section_upgrade',
		array(
			'title'    => esc_html__( '💎 Upgrade my theme', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 5,
		)
	);
	$wp_customize->add_setting(
		'ag_upgrade_placeholder',
		array(
			'default'           => '',
			'sanitize_callback' => '__return_empty_string',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new AG_Starter_Avocat_Upgrade_Control(
			$wp_customize,
			'ag_upgrade_placeholder',
			array(
				'section'  => 'ag_section_upgrade',
				'priority' => 10,
			)
		)
	);

	// ─── Section: Couleurs ───
	$wp_customize->add_section(
		'ag_section_colors',
		array(
			'title'    => esc_html__( 'Theme colors', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 10,
		)
	);

	$colors = array(
		'ag_color_accent'     => esc_html__( 'Accent color', 'ag-starter-avocat' ),
		'ag_color_background' => esc_html__( 'Main background', 'ag-starter-avocat' ),
		'ag_color_panel'      => esc_html__( 'Cards background', 'ag-starter-avocat' ),
		'ag_color_border'     => esc_html__( 'Border color', 'ag-starter-avocat' ),
		'ag_color_text'       => esc_html__( 'Text color', 'ag-starter-avocat' ),
		'ag_color_heading'    => esc_html__( 'Headings color', 'ag-starter-avocat' ),
		'ag_color_muted'      => esc_html__( 'Secondary text', 'ag-starter-avocat' ),
	);
	$priority = 10;
	foreach ( $colors as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$key,
				array(
					'label'    => $label,
					'section'  => 'ag_section_colors',
					'priority' => $priority,
				)
			)
		);
		$priority += 5;
	}

	// ─── Section: Typographie ───
	$wp_customize->add_section(
		'ag_section_typography',
		array(
			'title'    => esc_html__( 'Typography', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 20,
		)
	);

	$wp_customize->add_setting(
		'ag_font_family',
		array(
			'default'           => $defaults['ag_font_family'],
			'sanitize_callback' => 'ag_starter_avocat_sanitize_select',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_family',
		array(
			'label'   => esc_html__( 'Font family', 'ag-starter-avocat' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'system'     => esc_html__( 'System (default, fast)', 'ag-starter-avocat' ),
				'sans'       => esc_html__( 'Classic sans-serif', 'ag-starter-avocat' ),
				'serif'      => esc_html__( 'Serif (elegant)', 'ag-starter-avocat' ),
				'monospace'  => esc_html__( 'Monospace', 'ag-starter-avocat' ),
			),
		)
	);

	$wp_customize->add_setting(
		'ag_font_base_size',
		array(
			'default'           => $defaults['ag_font_base_size'],
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_base_size',
		array(
			'label'       => esc_html__( 'Base text size (px)', 'ag-starter-avocat' ),
			'description' => esc_html__( 'Between 14 and 20.', 'ag-starter-avocat' ),
			'section'     => 'ag_section_typography',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 14,
				'max'  => 20,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'ag_font_heading_scale',
		array(
			'default'           => $defaults['ag_font_heading_scale'],
			'sanitize_callback' => 'ag_starter_avocat_sanitize_select',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_heading_scale',
		array(
			'label'   => esc_html__( 'Headings size', 'ag-starter-avocat' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'small'   => esc_html__( 'Compact', 'ag-starter-avocat' ),
				'default' => esc_html__( 'Default', 'ag-starter-avocat' ),
				'large'   => esc_html__( 'Large', 'ag-starter-avocat' ),
			),
		)
	);

	// ─── Section: Hero ───
	$wp_customize->add_section(
		'ag_section_hero',
		array(
			'title'    => esc_html__( 'Hero (home)', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 30,
		)
	);

	$hero_fields = array(
		'ag_hero_show'       => array(
			'label' => esc_html__( 'Show the hero', 'ag-starter-avocat' ),
			'type'  => 'checkbox',
		),
		'ag_hero_prefix'     => array(
			'label' => esc_html__( 'Title prefix', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
		'ag_hero_brand'      => array(
			'label' => esc_html__( 'Business name', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
		'ag_hero_subtitle'   => array(
			'label' => esc_html__( 'Subtitle', 'ag-starter-avocat' ),
			'type'  => 'textarea',
		),
		'ag_hero_button'     => array(
			'label' => esc_html__( 'Button text', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
		'ag_hero_button_url' => array(
			'label' => esc_html__( 'Button link (URL or #anchor)', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
	);
	$prio = 10;
	foreach ( $hero_fields as $key => $meta ) {
		$sanitize = 'sanitize_text_field';
		if ( 'checkbox' === $meta['type'] ) {
			$sanitize = 'ag_starter_avocat_sanitize_checkbox';
		} elseif ( 'textarea' === $meta['type'] ) {
			$sanitize = 'sanitize_textarea_field';
		}
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'    => $meta['label'],
				'section'  => 'ag_section_hero',
				'type'     => $meta['type'],
				'priority' => $prio,
			)
		);
		$prio += 5;
	}

	// ─── Section: Pied de page ───
	$wp_customize->add_section(
		'ag_section_footer',
		array(
			'title'    => esc_html__( 'Footer', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 40,
		)
	);

	$wp_customize->add_setting(
		'ag_footer_copyright',
		array(
			'default'           => $defaults['ag_footer_copyright'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_footer_copyright',
		array(
			'label'       => esc_html__( 'Custom copyright text', 'ag-starter-avocat' ),
			'description' => esc_html__( 'Leave empty for the default text.', 'ag-starter-avocat' ),
			'section'     => 'ag_section_footer',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'ag_footer_credits',
		array(
			'default'           => $defaults['ag_footer_credits'],
			'sanitize_callback' => 'ag_starter_avocat_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_footer_credits',
		array(
			'label'   => esc_html__( 'Show the credit "Free theme by Alliance Group"', 'ag-starter-avocat' ),
			'section' => 'ag_section_footer',
			'type'    => 'checkbox',
		)
	);

	// ─── Section: Cabinet (contact + horaires + plan + RPVA) ───
	$wp_customize->add_section(
		'ag_section_cabinet',
		array(
			'title'    => esc_html__( 'Firm (contact &amp; hours)', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 50,
		)
	);
	$cabinet_fields = array(
		'ag_cabinet_phone'     => array( 'label' => esc_html__( 'Firm phone', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_cabinet_emergency' => array( 'label' => esc_html__( 'Police-custody emergency number (optional)', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_cabinet_email'     => array( 'label' => esc_html__( 'Firm email', 'ag-starter-avocat' ), 'type' => 'email' ),
		'ag_cabinet_address'   => array( 'label' => esc_html__( 'Full address (one line per line)', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_cabinet_hours'     => array( 'label' => esc_html__( 'Opening hours (one line per line)', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_cabinet_map_embed' => array( 'label' => esc_html__( 'Google Maps embed URL (optional)', 'ag-starter-avocat' ), 'type' => 'url' ),
		'ag_cabinet_rpva'      => array( 'label' => esc_html__( 'RPVA number (optional)', 'ag-starter-avocat' ), 'type' => 'text' ),
	);
	$prio = 10;
	foreach ( $cabinet_fields as $key => $meta ) {
		$sanitize = ( 'textarea' === $meta['type'] ) ? 'sanitize_textarea_field' : ( 'email' === $meta['type'] ? 'sanitize_email' : ( 'url' === $meta['type'] ? 'esc_url_raw' : 'sanitize_text_field' ) );
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'    => $meta['label'],
				'section'  => 'ag_section_cabinet',
				'type'     => $meta['type'],
				'priority' => $prio,
			)
		);
		$prio += 5;
	}

	// ─── Section: Déontologie & RGPD (template « déontologie-ready ») ───
	$wp_customize->add_section(
		'ag_section_deonto',
		array(
			'title'       => esc_html__( 'Ethics &amp; GDPR', 'ag-starter-avocat' ),
			'panel'       => 'ag_starter_panel',
			'priority'    => 55,
			'description' => esc_html__( 'Notices shown in the footer. The template is "ethics-ready": no client testimonials and no Google reviews widget (prohibited by the French bar rules RIN/CNB). Remember to create the "legal-notice", "privacy" and "cookies" pages: their links will appear automatically.', 'ag-starter-avocat' ),
		)
	);
	$deonto_fields = array(
		'ag_avocat_hebergement_note' => array(
			'label'   => esc_html__( 'Hosting / privilege note (footer)', 'ag-starter-avocat' ),
			'type'    => 'text',
			'default' => __( 'Hosting in the European Union — data protected by attorney-client privilege.', 'ag-starter-avocat' ),
		),
		'ag_avocat_nocookie_note'    => array(
			'label'   => esc_html__( 'Cookie note (footer)', 'ag-starter-avocat' ),
			'type'    => 'text',
			'default' => __( 'This site does not use any advertising tracking cookies.', 'ag-starter-avocat' ),
		),
	);
	$prio = 10;
	foreach ( $deonto_fields as $key => $meta ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $meta['default'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'    => $meta['label'],
				'section'  => 'ag_section_deonto',
				'type'     => $meta['type'],
				'priority' => $prio,
			)
		);
		$prio += 5;
	}

	// ─── Section: Le Maître ───
	$wp_customize->add_section(
		'ag_section_maitre',
		array(
			'title'    => esc_html__( 'The Attorney (introduction)', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 60,
		)
	);
	$maitre_fields = array(
		'ag_maitre_show'        => array( 'label' => esc_html__( 'Show the "Attorney" section', 'ag-starter-avocat' ), 'type' => 'checkbox' ),
		'ag_maitre_name'        => array( 'label' => esc_html__( 'Attorney name', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_maitre_barreau'     => array( 'label' => esc_html__( 'Bar of admission', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_maitre_year'        => array( 'label' => esc_html__( 'Year of bar admission', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_maitre_specialties' => array( 'label' => esc_html__( 'Specialties (separated by ·)', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_maitre_bio'         => array( 'label' => esc_html__( 'Biography / background', 'ag-starter-avocat' ), 'type' => 'textarea' ),
	);
	$prio = 10;
	foreach ( $maitre_fields as $key => $meta ) {
		$sanitize = ( 'checkbox' === $meta['type'] ) ? 'ag_starter_avocat_sanitize_checkbox' : ( 'textarea' === $meta['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field' );
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'    => $meta['label'],
				'section'  => 'ag_section_maitre',
				'type'     => $meta['type'],
				'priority' => $prio,
			)
		);
		$prio += 5;
	}
	// Photo upload control.
	$wp_customize->add_setting(
		'ag_maitre_photo',
		array(
			'default'           => $defaults['ag_maitre_photo'],
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'ag_maitre_photo',
			array(
				'label'    => esc_html__( 'Attorney photo (optional)', 'ag-starter-avocat' ),
				'section'  => 'ag_section_maitre',
				'priority' => 50,
			)
		)
	);

	// ─── Section: Honoraires ───
	$wp_customize->add_section(
		'ag_section_honoraires',
		array(
			'title'    => esc_html__( 'Fees (pricing transparency)', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 70,
		)
	);
	$honoraires_fields = array(
		'ag_honoraires_show'        => array( 'label' => esc_html__( 'Show the Fees section', 'ag-starter-avocat' ), 'type' => 'checkbox' ),
		'ag_honoraires_first_label' => array( 'label' => esc_html__( 'Fee 1 — Label', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_honoraires_first_price' => array( 'label' => esc_html__( 'Fee 1 — Price', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_honoraires_first_desc'  => array( 'label' => esc_html__( 'Fee 1 — Description', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_honoraires_pack_label'  => array( 'label' => esc_html__( 'Fee 2 — Label', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_honoraires_pack_price'  => array( 'label' => esc_html__( 'Fee 2 — Price', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_honoraires_pack_desc'   => array( 'label' => esc_html__( 'Fee 2 — Description', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_honoraires_hour_label'  => array( 'label' => esc_html__( 'Fee 3 — Label', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_honoraires_hour_price'  => array( 'label' => esc_html__( 'Fee 3 — Price', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_honoraires_hour_desc'   => array( 'label' => esc_html__( 'Fee 3 — Description', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_honoraires_note'        => array( 'label' => esc_html__( 'Legal note (bottom of the block)', 'ag-starter-avocat' ), 'type' => 'textarea' ),
	);
	$prio = 10;
	foreach ( $honoraires_fields as $key => $meta ) {
		$sanitize = ( 'checkbox' === $meta['type'] ) ? 'ag_starter_avocat_sanitize_checkbox' : ( 'textarea' === $meta['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field' );
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'    => $meta['label'],
				'section'  => 'ag_section_honoraires',
				'type'     => $meta['type'],
				'priority' => $prio,
			)
		);
		$prio += 5;
	}

	// ─── Section: RDV form + RGPD ───
	$wp_customize->add_section(
		'ag_section_rdv',
		array(
			'title'    => esc_html__( 'Appointment form &amp; GDPR', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 80,
		)
	);
	$rdv_fields = array(
		'ag_rdv_show'            => array( 'label' => esc_html__( 'Show the appointment form', 'ag-starter-avocat' ), 'type' => 'checkbox' ),
		'ag_rdv_title'           => array( 'label' => esc_html__( 'Form title', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_rdv_subtitle'        => array( 'label' => esc_html__( 'Subtitle / promise', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_rdv_recipient_email' => array( 'label' => esc_html__( 'Recipient email (empty = firm email)', 'ag-starter-avocat' ), 'type' => 'email' ),
		'ag_rdv_rgpd_text'       => array( 'label' => esc_html__( 'GDPR consent text (checkbox)', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		'ag_rgpd_mention'        => array( 'label' => esc_html__( 'GDPR notice shown in the footer', 'ag-starter-avocat' ), 'type' => 'textarea' ),
	);
	$prio = 10;
	foreach ( $rdv_fields as $key => $meta ) {
		$sanitize = ( 'checkbox' === $meta['type'] ) ? 'ag_starter_avocat_sanitize_checkbox' : ( 'textarea' === $meta['type'] ? 'sanitize_textarea_field' : ( 'email' === $meta['type'] ? 'sanitize_email' : 'sanitize_text_field' ) );
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'    => $meta['label'],
				'section'  => 'ag_section_rdv',
				'type'     => $meta['type'],
				'priority' => $prio,
			)
		);
		$prio += 5;
	}

	// ─── Section: Contenu accueil — textes (leads des sections) ───
	$wp_customize->add_section(
		'ag_section_home_content',
		array(
			'title'       => esc_html__( 'Home content — texts', 'ag-starter-avocat' ),
			'panel'       => 'ag_starter_panel',
			'priority'    => 55,
			'description' => esc_html__( 'Customize the intro sentences (under the titles) of each home section.', 'ag-starter-avocat' ),
		)
	);
	$ag_avocat_home_fields = array(
		// Domaines.
		'ag_avocat_domaines_title'      => array( 'label' => esc_html__( 'Areas — H2 title', 'ag-starter-avocat' ),                'type' => 'text' ),
		'ag_avocat_domaines_lead'       => array( 'label' => esc_html__( 'Areas — intro sentence', 'ag-starter-avocat' ),         'type' => 'textarea' ),
		'ag_avocat_domaines_empty'      => array( 'label' => esc_html__( 'Areas — empty message', 'ag-starter-avocat' ),         'type' => 'textarea' ),
		'ag_avocat_domaines_empty_btn'  => array( 'label' => esc_html__( 'Areas — add button (admin)', 'ag-starter-avocat' ),    'type' => 'text' ),
		'ag_avocat_domaines_empty_hint' => array( 'label' => esc_html__( 'Areas — admin tip', 'ag-starter-avocat' ),            'type' => 'textarea' ),
		// Le Maître.
		'ag_avocat_maitre_tag'                 => array( 'label' => esc_html__( 'Attorney — label', 'ag-starter-avocat' ),                   'type' => 'text' ),
		'ag_avocat_maitre_year_prefix'         => array( 'label' => esc_html__( 'Attorney — year prefix (Admitted since…)', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_avocat_maitre_specialties_label'   => array( 'label' => esc_html__( 'Attorney — Specialties label', 'ag-starter-avocat' ),         'type' => 'text' ),
		// Honoraires.
		'ag_avocat_honoraires_title' => array( 'label' => esc_html__( 'Fees — H2 title', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_honoraires_lead'  => array( 'label' => esc_html__( 'Fees — intro sentence', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		// Cabinet.
		'ag_avocat_cabinet_title'           => array( 'label' => esc_html__( 'Firm — H2 title', 'ag-starter-avocat' ),                  'type' => 'text' ),
		'ag_avocat_cabinet_lead'            => array( 'label' => esc_html__( 'Firm — intro sentence', 'ag-starter-avocat' ),           'type' => 'textarea' ),
		'ag_avocat_cabinet_address_heading' => array( 'label' => esc_html__( 'Firm — Address block title', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_cabinet_hours_heading'   => array( 'label' => esc_html__( 'Firm — Hours block title', 'ag-starter-avocat' ),       'type' => 'text' ),
		'ag_avocat_cabinet_contact_heading' => array( 'label' => esc_html__( 'Firm — Contact block title', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_cabinet_emergency_label' => array( 'label' => esc_html__( 'Firm — police custody label', 'ag-starter-avocat' ),       'type' => 'text' ),
		// RDV form.
		'ag_avocat_rdv_label_prenom'   => array( 'label' => esc_html__( 'Appointment — First name label', 'ag-starter-avocat' ),         'type' => 'text' ),
		'ag_avocat_rdv_label_nom'      => array( 'label' => esc_html__( 'Appointment — Name label', 'ag-starter-avocat' ),            'type' => 'text' ),
		'ag_avocat_rdv_label_email'    => array( 'label' => esc_html__( 'Appointment — Email label', 'ag-starter-avocat' ),          'type' => 'text' ),
		'ag_avocat_rdv_label_tel'      => array( 'label' => esc_html__( 'Appointment — Phone label', 'ag-starter-avocat' ),      'type' => 'text' ),
		'ag_avocat_rdv_label_domaine'  => array( 'label' => esc_html__( 'Appointment — Area label', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_rdv_label_format'   => array( 'label' => esc_html__( 'Appointment — Format label', 'ag-starter-avocat' ),         'type' => 'text' ),
		'ag_avocat_rdv_label_message'  => array( 'label' => esc_html__( 'Appointment — Description label', 'ag-starter-avocat' ),    'type' => 'text' ),
		'ag_avocat_rdv_domaine_select' => array( 'label' => esc_html__( 'Appointment — area placeholder', 'ag-starter-avocat' ),  'type' => 'text' ),
		'ag_avocat_rdv_domaine_other'  => array( 'label' => esc_html__( 'Appointment — "Other" option', 'ag-starter-avocat' ),     'type' => 'text' ),
		'ag_avocat_rdv_format_cabinet' => array( 'label' => esc_html__( 'Appointment — At the office format', 'ag-starter-avocat' ),    'type' => 'text' ),
		'ag_avocat_rdv_format_visio'   => array( 'label' => esc_html__( 'Appointment — By video format', 'ag-starter-avocat' ),      'type' => 'text' ),
		'ag_avocat_rdv_format_phone'   => array( 'label' => esc_html__( 'Appointment — By phone format', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_avocat_rdv_submit_label'   => array( 'label' => esc_html__( 'Appointment — submit button', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_rdv_legal_note'     => array( 'label' => esc_html__( 'Appointment — legal note (under button)', 'ag-starter-avocat' ), 'type' => 'textarea' ),
	);
	$ag_prio = 10;
	foreach ( $ag_avocat_home_fields as $ag_key => $ag_f ) {
		$ag_type = isset( $ag_f['type'] ) ? $ag_f['type'] : 'textarea';
		$wp_customize->add_setting(
			$ag_key,
			array(
				'default'           => isset( $defaults[ $ag_key ] ) ? $defaults[ $ag_key ] : '',
				'sanitize_callback' => $ag_type === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$ag_key,
			array(
				'label'    => $ag_f['label'],
				'section'  => 'ag_section_home_content',
				'type'     => $ag_type,
				'priority' => $ag_prio,
			)
		);
		$ag_prio += 5;
	}
}
add_action( 'customize_register', 'ag_starter_avocat_customize_register' );

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Value.
 * @return bool
 */
function ag_starter_avocat_sanitize_checkbox( $value ) {
	return ( isset( $value ) && true === (bool) $value );
}

/**
 * Sanitize a select choice against the registered choices.
 *
 * @param string               $value   Raw value.
 * @param WP_Customize_Setting $setting Setting object.
 * @return string
 */
function ag_starter_avocat_sanitize_select( $value, $setting = null ) {
	$value = sanitize_key( $value );
	if ( $setting && isset( $setting->manager ) ) {
		$control = $setting->manager->get_control( $setting->id );
		if ( $control && isset( $control->choices[ $value ] ) ) {
			return $value;
		}
		return $setting->default;
	}
	return $value;
}

/**
 * Custom Customizer control that renders the "Ameliorer mon theme"
 * upgrade banner with clickable Pro / Premium / Business buttons +
 * a soft custom-site upsell. Loaded lazily after WP_Customize_Control.
 */
function ag_starter_avocat_register_upgrade_control() {
	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		return;
	}
	if ( class_exists( 'AG_Starter_Avocat_Upgrade_Control' ) ) {
		return;
	}

	class AG_Starter_Avocat_Upgrade_Control extends WP_Customize_Control {

		public $type = 'ag_upgrade_banner';

		public function render_content() {
			$utm     = '?utm_source=wp-customizer&utm_medium=ag-starter-avocat&utm_campaign=upgrade';
			$base    = 'https://alliancegroupe-inc.com/templates-wordpress';
			$contact = 'https://alliancegroupe-inc.com/contact' . $utm;

			// Détecte le tier de licence active (free / pro / premium / business).
			$tier  = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_tier() : 'free';
			$order = array( 'free' => 0, 'pro' => 1, 'premium' => 2, 'business' => 3 );
			$cur   = isset( $order[ $tier ] ) ? $order[ $tier ] : 0;

			$all_packs = array(
				'business' => array(
					'icon'  => '💎',
					'title' => 'Premium',
					'price' => '69€',
					'desc'  => esc_html__( 'Our most refined design: luxury, scroll animations, sticky header, advanced colors, testimonials, header phone, minimal ads, support. One-time payment.', 'ag-starter-avocat' ),
					'url'   => $base . $utm . '&pack=premium#ag-pricing',
				),
			);

			$packs = array();
			foreach ( $all_packs as $key => $p ) {
				if ( $order[ $key ] > $cur ) $packs[ $key ] = $p;
			}

			$tier_labels = array(
				'pro'      => '💎 Premium',
				'premium'  => '💎 Premium',
				'business' => '💎 Premium',
			);
			?>
			<div style="background:#fff;border:1px solid #d4b45c;border-radius:8px;padding:14px;margin-top:8px;">

				<?php if ( 'free' !== $tier ) : ?>
					<div style="background:linear-gradient(135deg,#d4b45c 0%,#b8941f 100%);color:#0a0a0a;padding:12px 14px;border-radius:6px;margin-bottom:<?php echo empty( $packs ) ? '0' : '12px'; ?>;text-align:center;">
						<strong style="display:block;font-size:13px;">✓ <?php echo esc_html( $tier_labels[ $tier ] ); ?> <?php esc_html_e( 'active', 'ag-starter-avocat' ); ?></strong>
						<span style="display:block;font-size:11px;margin-top:2px;opacity:.85;"><?php esc_html_e( 'Thank you for your trust!', 'ag-starter-avocat' ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $packs ) ) : ?>
					<?php if ( 'free' === $tier ) : ?>
						<p style="margin:0 0 12px;color:#50575e;font-size:12px;line-height:1.5;">
							<?php esc_html_e( 'You are using the free version. Upgrade to Premium or Business:', 'ag-starter-avocat' ); ?>
						</p>
					<?php else : ?>
						<p style="margin:0 0 12px;color:#50575e;font-size:12px;line-height:1.5;">
							<?php esc_html_e( 'Upgrade to the higher tier:', 'ag-starter-avocat' ); ?>
						</p>
					<?php endif; ?>

					<?php foreach ( $packs as $p ) : ?>
						<a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener" style="display:block;padding:10px 12px;background:#f6f7f7;border:1px solid #ddd;border-left:3px solid #d4b45c;border-radius:4px;color:#1d2327;text-decoration:none;margin-bottom:8px;transition:background .15s;">
							<strong style="display:block;color:#1d2327;font-size:13px;">
								<span style="margin-right:4px;"><?php echo esc_html( $p['icon'] ); ?></span>
								<?php echo esc_html( $p['title'] ); ?>
								<span style="float:right;color:#d4b45c;"><?php echo esc_html( $p['price'] ); ?></span>
							</strong>
							<span style="display:block;margin-top:3px;font-size:11px;color:#50575e;line-height:1.45;">
								<?php echo esc_html( $p['desc'] ); ?>
							</span>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>

				<div style="margin-top:14px;padding-top:12px;border-top:1px dashed #d4b45c;text-align:center;">
					<a href="<?php echo esc_url( $contact ); ?>" target="_blank" rel="noopener" style="display:inline-block;color:#0a0a0a;background:#d4b45c;padding:8px 14px;border-radius:4px;font-size:12px;font-weight:700;text-decoration:none;">
						💎 <?php esc_html_e( 'Custom site (+340% leads) →', 'ag-starter-avocat' ); ?>
					</a>
					<p style="margin:8px 0 0;color:#888;font-size:11px;">
						<?php esc_html_e( 'First call free, no obligation', 'ag-starter-avocat' ); ?>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
add_action( 'customize_register', 'ag_starter_avocat_register_upgrade_control', 1 );

require get_template_directory() . '/inc/customizer-output.php';
