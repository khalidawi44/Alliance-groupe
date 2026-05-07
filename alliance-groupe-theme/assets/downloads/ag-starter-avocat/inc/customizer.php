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
		'ag_maitre_photo'       => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=400&q=80',
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
			'title'       => esc_html__( 'AG Starter — Personnalisation', 'ag-starter-avocat' ),
			'description' => esc_html__( 'Modifiez les couleurs, la typographie et les textes cles de votre theme directement ici. Aucun code requis.', 'ag-starter-avocat' ),
			'priority'    => 30,
		)
	);

	// ─── Section: Améliorer mon thème (upgrade promo) ───
	$wp_customize->add_section(
		'ag_section_upgrade',
		array(
			'title'    => esc_html__( '💎 Ameliorer mon theme', 'ag-starter-avocat' ),
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
			'title'    => esc_html__( 'Couleurs du theme', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 10,
		)
	);

	$colors = array(
		'ag_color_accent'     => esc_html__( 'Couleur d\'accent', 'ag-starter-avocat' ),
		'ag_color_background' => esc_html__( 'Arriere-plan principal', 'ag-starter-avocat' ),
		'ag_color_panel'      => esc_html__( 'Arriere-plan des cartes', 'ag-starter-avocat' ),
		'ag_color_border'     => esc_html__( 'Couleur des bordures', 'ag-starter-avocat' ),
		'ag_color_text'       => esc_html__( 'Couleur du texte', 'ag-starter-avocat' ),
		'ag_color_heading'    => esc_html__( 'Couleur des titres', 'ag-starter-avocat' ),
		'ag_color_muted'      => esc_html__( 'Texte secondaire', 'ag-starter-avocat' ),
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
			'title'    => esc_html__( 'Typographie', 'ag-starter-avocat' ),
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
			'label'   => esc_html__( 'Famille de police', 'ag-starter-avocat' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'system'     => esc_html__( 'Systeme (defaut, rapide)', 'ag-starter-avocat' ),
				'sans'       => esc_html__( 'Sans-serif classique', 'ag-starter-avocat' ),
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
			'label'       => esc_html__( 'Taille de base du texte (px)', 'ag-starter-avocat' ),
			'description' => esc_html__( 'Entre 14 et 20.', 'ag-starter-avocat' ),
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
			'label'   => esc_html__( 'Taille des titres', 'ag-starter-avocat' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'small'   => esc_html__( 'Compact', 'ag-starter-avocat' ),
				'default' => esc_html__( 'Par defaut', 'ag-starter-avocat' ),
				'large'   => esc_html__( 'Grand', 'ag-starter-avocat' ),
			),
		)
	);

	// ─── Section: Hero ───
	$wp_customize->add_section(
		'ag_section_hero',
		array(
			'title'    => esc_html__( 'Hero (accueil)', 'ag-starter-avocat' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 30,
		)
	);

	$hero_fields = array(
		'ag_hero_show'       => array(
			'label' => esc_html__( 'Afficher le hero', 'ag-starter-avocat' ),
			'type'  => 'checkbox',
		),
		'ag_hero_prefix'     => array(
			'label' => esc_html__( 'Prefixe du titre', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
		'ag_hero_brand'      => array(
			'label' => esc_html__( 'Nom de l\'etablissement', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
		'ag_hero_subtitle'   => array(
			'label' => esc_html__( 'Sous-titre', 'ag-starter-avocat' ),
			'type'  => 'textarea',
		),
		'ag_hero_button'     => array(
			'label' => esc_html__( 'Texte du bouton', 'ag-starter-avocat' ),
			'type'  => 'text',
		),
		'ag_hero_button_url' => array(
			'label' => esc_html__( 'Lien du bouton (URL ou #ancre)', 'ag-starter-avocat' ),
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
			'title'    => esc_html__( 'Pied de page', 'ag-starter-avocat' ),
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
			'label'       => esc_html__( 'Texte de copyright personnalise', 'ag-starter-avocat' ),
			'description' => esc_html__( 'Laissez vide pour le texte par defaut.', 'ag-starter-avocat' ),
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
			'label'   => esc_html__( 'Afficher le credit "Theme gratuit par Alliance Group"', 'ag-starter-avocat' ),
			'section' => 'ag_section_footer',
			'type'    => 'checkbox',
		)
	);

	// ─── Section: Cabinet (contact + horaires + plan + RPVA) ───
	$wp_customize->add_section(
		'ag_section_cabinet',
		array(
			'title'    => esc_html__( 'Cabinet (contact &amp; horaires)', 'ag-starter-avocat' ),
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

	// ─── Section: Le Maître ───
	$wp_customize->add_section(
		'ag_section_maitre',
		array(
			'title'    => esc_html__( 'Le Maître (présentation)', 'ag-starter-avocat' ),
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
				'label'    => esc_html__( 'Photo du Maître (optionnel)', 'ag-starter-avocat' ),
				'section'  => 'ag_section_maitre',
				'priority' => 50,
			)
		)
	);

	// ─── Section: Honoraires ───
	$wp_customize->add_section(
		'ag_section_honoraires',
		array(
			'title'    => esc_html__( 'Honoraires (transparence tarifaire)', 'ag-starter-avocat' ),
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
			'title'    => esc_html__( 'Formulaire de rendez-vous &amp; RGPD', 'ag-starter-avocat' ),
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
			'title'       => esc_html__( 'Contenu accueil — textes', 'ag-starter-avocat' ),
			'panel'       => 'ag_starter_panel',
			'priority'    => 55,
			'description' => esc_html__( 'Personnalisez les phrases d\'introduction (sous les titres) de chaque section de l\'accueil.', 'ag-starter-avocat' ),
		)
	);
	$ag_avocat_home_fields = array(
		// Domaines.
		'ag_avocat_domaines_title'      => array( 'label' => esc_html__( 'Domaines — titre H2', 'ag-starter-avocat' ),                'type' => 'text' ),
		'ag_avocat_domaines_lead'       => array( 'label' => esc_html__( 'Domaines — phrase d\'intro', 'ag-starter-avocat' ),         'type' => 'textarea' ),
		'ag_avocat_domaines_empty'      => array( 'label' => esc_html__( 'Domaines — message si vide', 'ag-starter-avocat' ),         'type' => 'textarea' ),
		'ag_avocat_domaines_empty_btn'  => array( 'label' => esc_html__( 'Domaines — bouton ajout (admin)', 'ag-starter-avocat' ),    'type' => 'text' ),
		'ag_avocat_domaines_empty_hint' => array( 'label' => esc_html__( 'Domaines — astuce admin', 'ag-starter-avocat' ),            'type' => 'textarea' ),
		// Le Maître.
		'ag_avocat_maitre_tag'                 => array( 'label' => esc_html__( 'Maître — étiquette', 'ag-starter-avocat' ),                   'type' => 'text' ),
		'ag_avocat_maitre_year_prefix'         => array( 'label' => esc_html__( 'Maître — préfixe année (Inscrit depuis…)', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_avocat_maitre_specialties_label'   => array( 'label' => esc_html__( 'Maître — libellé Specialités', 'ag-starter-avocat' ),         'type' => 'text' ),
		// Honoraires.
		'ag_avocat_honoraires_title' => array( 'label' => esc_html__( 'Honoraires — titre H2', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_honoraires_lead'  => array( 'label' => esc_html__( 'Honoraires — phrase d\'intro', 'ag-starter-avocat' ), 'type' => 'textarea' ),
		// Cabinet.
		'ag_avocat_cabinet_title'           => array( 'label' => esc_html__( 'Cabinet — titre H2', 'ag-starter-avocat' ),                  'type' => 'text' ),
		'ag_avocat_cabinet_lead'            => array( 'label' => esc_html__( 'Cabinet — phrase d\'intro', 'ag-starter-avocat' ),           'type' => 'textarea' ),
		'ag_avocat_cabinet_address_heading' => array( 'label' => esc_html__( 'Cabinet — titre bloc Adresse', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_cabinet_hours_heading'   => array( 'label' => esc_html__( 'Cabinet — titre bloc Horaires', 'ag-starter-avocat' ),       'type' => 'text' ),
		'ag_avocat_cabinet_contact_heading' => array( 'label' => esc_html__( 'Cabinet — titre bloc Contact', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_cabinet_emergency_label' => array( 'label' => esc_html__( 'Cabinet — libellé garde à vue', 'ag-starter-avocat' ),       'type' => 'text' ),
		// RDV form.
		'ag_avocat_rdv_label_prenom'   => array( 'label' => esc_html__( 'RDV — label Prénom', 'ag-starter-avocat' ),         'type' => 'text' ),
		'ag_avocat_rdv_label_nom'      => array( 'label' => esc_html__( 'RDV — label Nom', 'ag-starter-avocat' ),            'type' => 'text' ),
		'ag_avocat_rdv_label_email'    => array( 'label' => esc_html__( 'RDV — label Email', 'ag-starter-avocat' ),          'type' => 'text' ),
		'ag_avocat_rdv_label_tel'      => array( 'label' => esc_html__( 'RDV — label Téléphone', 'ag-starter-avocat' ),      'type' => 'text' ),
		'ag_avocat_rdv_label_domaine'  => array( 'label' => esc_html__( 'RDV — label Domaine', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_rdv_label_format'   => array( 'label' => esc_html__( 'RDV — label Format', 'ag-starter-avocat' ),         'type' => 'text' ),
		'ag_avocat_rdv_label_message'  => array( 'label' => esc_html__( 'RDV — label Description', 'ag-starter-avocat' ),    'type' => 'text' ),
		'ag_avocat_rdv_domaine_select' => array( 'label' => esc_html__( 'RDV — placeholder domaine', 'ag-starter-avocat' ),  'type' => 'text' ),
		'ag_avocat_rdv_domaine_other'  => array( 'label' => esc_html__( 'RDV — option « Autre »', 'ag-starter-avocat' ),     'type' => 'text' ),
		'ag_avocat_rdv_format_cabinet' => array( 'label' => esc_html__( 'RDV — format Au cabinet', 'ag-starter-avocat' ),    'type' => 'text' ),
		'ag_avocat_rdv_format_visio'   => array( 'label' => esc_html__( 'RDV — format En visio', 'ag-starter-avocat' ),      'type' => 'text' ),
		'ag_avocat_rdv_format_phone'   => array( 'label' => esc_html__( 'RDV — format Par téléphone', 'ag-starter-avocat' ), 'type' => 'text' ),
		'ag_avocat_rdv_submit_label'   => array( 'label' => esc_html__( 'RDV — bouton submit', 'ag-starter-avocat' ),        'type' => 'text' ),
		'ag_avocat_rdv_legal_note'     => array( 'label' => esc_html__( 'RDV — note légale (sous bouton)', 'ag-starter-avocat' ), 'type' => 'textarea' ),
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
			$utm  = '?utm_source=wp-customizer&utm_medium=ag-starter-avocat&utm_campaign=upgrade';
			$base = 'https://alliancegroupe-inc.com/templates-wordpress';
			$contact = 'https://alliancegroupe-inc.com/contact' . $utm;
			$packs = array(
				'premium' => array(
					'icon'  => '⭐',
					'title' => 'Pack Premium',
					'price' => '99€',
					'desc'  => 'Design luxe, animations scroll, header sticky, 2 skins de couleur, temoignages clients, telephone header',
					'url'   => $base . $utm . '&pack=premium#ag-pricing',
				),
				'business' => array(
					'icon'  => '💼',
					'title' => 'Pack Business',
					'price' => '149€',
					'desc'  => 'Tout Premium + 4 skins de couleur, pub minimale, session strategique 30 min, support prioritaire',
					'url'   => $base . $utm . '&pack=business#ag-pricing',
				),
			);
			?>
			<div style="background:#fff;border:1px solid #d4b45c;border-radius:8px;padding:14px;margin-top:8px;">
				<p style="margin:0 0 12px;color:#50575e;font-size:12px;line-height:1.5;">
					<?php esc_html_e( 'Vous utilisez la version gratuite. Passez au Premium ou Business :', 'ag-starter-avocat' ); ?>
				</p>

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

				<div style="margin-top:14px;padding-top:12px;border-top:1px dashed #d4b45c;text-align:center;">
					<a href="<?php echo esc_url( $contact ); ?>" target="_blank" rel="noopener" style="display:inline-block;color:#0a0a0a;background:#d4b45c;padding:8px 14px;border-radius:4px;font-size:12px;font-weight:700;text-decoration:none;">
						💎 <?php esc_html_e( 'Site sur-mesure (+340% leads) →', 'ag-starter-avocat' ); ?>
					</a>
					<p style="margin:8px 0 0;color:#888;font-size:11px;">
						<?php esc_html_e( 'Premier appel gratuit, sans engagement', 'ag-starter-avocat' ); ?>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
add_action( 'customize_register', 'ag_starter_avocat_register_upgrade_control', 1 );

require get_template_directory() . '/inc/customizer-output.php';
