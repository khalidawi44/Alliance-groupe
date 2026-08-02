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
		'ag_hero_prefix'        => 'Cabinet',
		'ag_hero_brand'         => '[Maître Nom]',
		'ag_hero_subtitle'      => 'Avocat au barreau, conseil juridique et defense de vos interets en toute confidentialite.',
		'ag_hero_button'        => 'Prendre rendez-vous',
		'ag_hero_button_url'    => '/rendez-vous/',
		// Footer.
		'ag_footer_copyright'   => '',
		'ag_footer_credits'     => true,
		// Cabinet — informations administratives.
		'ag_cabinet_phone'      => '01 23 45 67 89',
		'ag_cabinet_emergency'  => '',
		'ag_cabinet_email'      => 'contact@votre-cabinet.fr',
		'ag_cabinet_address'    => "15 boulevard du Palais\n75001 Paris",
		'ag_cabinet_hours'      => "Lundi - Vendredi : 9h - 19h\nSamedi : sur rendez-vous\nVisio disponible",
		'ag_cabinet_map_embed'  => '',
		'ag_cabinet_rpva'       => '',
		// Le Maître.
		'ag_maitre_show'        => true,
		'ag_maitre_name'        => '[Maître Nom]',
		'ag_maitre_barreau'     => 'Inscrit au Barreau de Paris',
		'ag_maitre_year'        => '2010',
		'ag_maitre_bio'         => "Avocat au barreau depuis plus de quinze ans, j'accompagne particuliers et entreprises avec rigueur, ecoute et discretion. Mon approche : analyser chaque dossier en profondeur, vous expliquer clairement vos options, et batir avec vous la strategie la plus efficace.",
		'ag_maitre_specialties' => 'Droit des affaires · Droit du travail · Droit de la famille',
		'ag_maitre_photo'       => '',
		// Honoraires.
		'ag_honoraires_show'        => true,
		'ag_honoraires_first_label' => 'Premier rendez-vous',
		'ag_honoraires_first_price' => '80€ HT',
		'ag_honoraires_first_desc'  => 'Consultation initiale d\'1 heure pour analyser votre dossier et vous proposer une strategie.',
		'ag_honoraires_pack_label'  => 'Forfait conseil',
		'ag_honoraires_pack_price'  => 'Sur devis',
		'ag_honoraires_pack_desc'   => 'Forfait tout inclus pour les dossiers definis a l\'avance, sans surprise.',
		'ag_honoraires_hour_label'  => 'Honoraires au temps passe',
		'ag_honoraires_hour_price'  => '180€ HT/h',
		'ag_honoraires_hour_desc'   => 'Pour les dossiers complexes, facturation transparente avec releve detaille.',
		'ag_honoraires_note'        => 'Tous les tarifs sont communiques par ecrit avant tout engagement. Devis et convention d\'honoraires obligatoires.',
		// RDV form.
		'ag_rdv_show'             => true,
		'ag_rdv_title'            => 'Prendre rendez-vous',
		'ag_rdv_subtitle'         => 'Premier rendez-vous confidentiel sous 48h ouvrees. Votre demande est traitee directement par le cabinet.',
		'ag_rdv_recipient_email'  => '',
		'ag_rdv_rgpd_text'        => 'J\'accepte que mes donnees soient utilisees uniquement pour traiter ma demande de rendez-vous, conformement au RGPD. Aucune donnee n\'est partagee avec des tiers.',
		// RGPD / footer legal.
		'ag_rgpd_mention'       => 'Cabinet inscrit au RPVA. Donnees personnelles traitees conformement au RGPD. Confidentialite absolue garantie par le secret professionnel.',
		// Home section leads (phrases d'intro sous les titres H2).
		'ag_avocat_domaines_lead'   => 'Conseil et representation pour particuliers et entreprises dans les principaux domaines du droit.',
		'ag_avocat_honoraires_lead' => 'Transparence totale sur les tarifs : pas de mauvaise surprise, devis ecrit avant tout engagement.',
		'ag_avocat_cabinet_lead'    => 'Consultation au cabinet, en visio ou par telephone.',
		// Section H2 titles + supplementary labels.
		'ag_avocat_domaines_title'      => 'Domaines d\'expertise',
		'ag_avocat_honoraires_title'    => 'Honoraires',
		'ag_avocat_cabinet_title'       => 'Le cabinet',
		'ag_avocat_maitre_tag'          => 'Le Maître',
		'ag_avocat_maitre_year_prefix'  => 'Inscrit depuis',
		'ag_avocat_maitre_specialties_label' => 'Specialites :',
		'ag_avocat_cabinet_address_heading'  => 'Adresse',
		'ag_avocat_cabinet_hours_heading'    => 'Horaires',
		'ag_avocat_cabinet_contact_heading'  => 'Contact',
		'ag_avocat_cabinet_emergency_label'  => 'Garde a vue 24/7 :',
		'ag_avocat_domaines_empty'           => 'Aucun domaine d\'expertise n\'est encore publie.',
		'ag_avocat_domaines_empty_btn'       => 'Ajouter un premier domaine',
		'ag_avocat_domaines_empty_hint'      => 'Astuce : creez 4 a 6 domaines (Droit des affaires, Droit du travail, Droit de la famille, Droit immobilier...) avec un emoji et 3 exemples de cas chacun.',
		// RDV form labels.
		'ag_avocat_rdv_label_prenom'    => 'Prénom',
		'ag_avocat_rdv_label_nom'       => 'Nom',
		'ag_avocat_rdv_label_email'     => 'Email',
		'ag_avocat_rdv_label_tel'       => 'Téléphone',
		'ag_avocat_rdv_label_domaine'   => 'Domaine concerné',
		'ag_avocat_rdv_label_format'    => 'Format souhaité',
		'ag_avocat_rdv_label_message'   => 'Description du dossier (en quelques lignes)',
		'ag_avocat_rdv_domaine_select'  => '— Sélectionnez —',
		'ag_avocat_rdv_domaine_other'   => 'Autre / a determiner',
		'ag_avocat_rdv_format_cabinet'  => 'Au cabinet',
		'ag_avocat_rdv_format_visio'    => 'En visio',
		'ag_avocat_rdv_format_phone'    => 'Par téléphone',
		'ag_avocat_rdv_submit_label'    => 'Envoyer ma demande →',
		'ag_avocat_rdv_legal_note'      => 'Demande confidentielle protégée par le secret professionnel. Réponse sous 48h ouvrées.',
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
		'ag_cabinet_phone'     => array( 'label' => 'Téléphone du cabinet', 'type' => 'text' ),
		'ag_cabinet_emergency' => array( 'label' => 'Numéro d\'urgence garde à vue (optionnel)', 'type' => 'text' ),
		'ag_cabinet_email'     => array( 'label' => 'Email du cabinet', 'type' => 'email' ),
		'ag_cabinet_address'   => array( 'label' => 'Adresse complète (1 ligne par ligne)', 'type' => 'textarea' ),
		'ag_cabinet_hours'     => array( 'label' => 'Horaires d\'ouverture (1 ligne par ligne)', 'type' => 'textarea' ),
		'ag_cabinet_map_embed' => array( 'label' => 'URL Google Maps embed (optionnel)', 'type' => 'url' ),
		'ag_cabinet_rpva'      => array( 'label' => 'Numéro RPVA (optionnel)', 'type' => 'text' ),
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
			'label'   => 'Note hébergement / secret pro (pied de page)',
			'type'    => 'text',
			'default' => 'Hébergement en Union Européenne — données protégées par le secret professionnel.',
		),
		'ag_avocat_nocookie_note'    => array(
			'label'   => 'Note cookies (pied de page)',
			'type'    => 'text',
			'default' => 'Ce site n’utilise aucun cookie de traçage publicitaire.',
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
		'ag_maitre_show'        => array( 'label' => 'Afficher la section "Le Maître"', 'type' => 'checkbox' ),
		'ag_maitre_name'        => array( 'label' => 'Nom du Maître', 'type' => 'text' ),
		'ag_maitre_barreau'     => array( 'label' => 'Barreau d\'inscription', 'type' => 'text' ),
		'ag_maitre_year'        => array( 'label' => 'Année d\'inscription au barreau', 'type' => 'text' ),
		'ag_maitre_specialties' => array( 'label' => 'Spécialités (séparées par ·)', 'type' => 'text' ),
		'ag_maitre_bio'         => array( 'label' => 'Biographie / parcours', 'type' => 'textarea' ),
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
		'ag_honoraires_show'        => array( 'label' => 'Afficher la section Honoraires', 'type' => 'checkbox' ),
		'ag_honoraires_first_label' => array( 'label' => 'Tarif 1 — Libellé', 'type' => 'text' ),
		'ag_honoraires_first_price' => array( 'label' => 'Tarif 1 — Prix', 'type' => 'text' ),
		'ag_honoraires_first_desc'  => array( 'label' => 'Tarif 1 — Description', 'type' => 'textarea' ),
		'ag_honoraires_pack_label'  => array( 'label' => 'Tarif 2 — Libellé', 'type' => 'text' ),
		'ag_honoraires_pack_price'  => array( 'label' => 'Tarif 2 — Prix', 'type' => 'text' ),
		'ag_honoraires_pack_desc'   => array( 'label' => 'Tarif 2 — Description', 'type' => 'textarea' ),
		'ag_honoraires_hour_label'  => array( 'label' => 'Tarif 3 — Libellé', 'type' => 'text' ),
		'ag_honoraires_hour_price'  => array( 'label' => 'Tarif 3 — Prix', 'type' => 'text' ),
		'ag_honoraires_hour_desc'   => array( 'label' => 'Tarif 3 — Description', 'type' => 'textarea' ),
		'ag_honoraires_note'        => array( 'label' => 'Note légale (en bas du bloc)', 'type' => 'textarea' ),
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
		'ag_rdv_show'            => array( 'label' => 'Afficher le formulaire RDV', 'type' => 'checkbox' ),
		'ag_rdv_title'           => array( 'label' => 'Titre du formulaire', 'type' => 'text' ),
		'ag_rdv_subtitle'        => array( 'label' => 'Sous-titre / promesse', 'type' => 'textarea' ),
		'ag_rdv_recipient_email' => array( 'label' => 'Email destinataire (vide = email du cabinet)', 'type' => 'email' ),
		'ag_rdv_rgpd_text'       => array( 'label' => 'Texte de consentement RGPD (case à cocher)', 'type' => 'textarea' ),
		'ag_rgpd_mention'        => array( 'label' => 'Mention RGPD affichée dans le footer', 'type' => 'textarea' ),
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
					'desc'  => 'Notre design le plus abouti : luxe, animations scroll, header sticky, couleurs avancees, temoignages, telephone header, pub minimale, support. Paiement unique.',
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
						<strong style="display:block;font-size:13px;">✓ <?php echo esc_html( $tier_labels[ $tier ] ); ?> actif</strong>
						<span style="display:block;font-size:11px;margin-top:2px;opacity:.85;">Merci pour votre confiance !</span>
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
