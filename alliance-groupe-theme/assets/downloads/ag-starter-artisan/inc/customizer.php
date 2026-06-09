<?php
/**
 * AG Starter Restaurant Customizer.
 *
 * Registers settings, sections and controls under Appearance >
 * Customize so users can change colors, typography, hero text and
 * footer text live, without editing any code — same experience
 * as Astra, OceanWP or Kadence.
 *
 * @package AG_Starter_Artisan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values per theme flavour.
 *
 * @return array
 */
function ag_starter_artisan_customizer_defaults() {
	return array(
		// Colors.
		'ag_color_accent'       => '#b87333',
		'ag_color_background'   => '#0e0d10',
		'ag_color_panel'        => '#181519',
		'ag_color_border'       => '#2a2428',
		'ag_color_text'         => '#e0e0e0',
		'ag_color_heading'      => '#ffffff',
		'ag_color_muted'        => '#aaaaaa',
		// Typography.
		'ag_font_family'        => 'system',
		'ag_font_base_size'     => 16,
		'ag_font_heading_scale' => 'default',
		// Hero.
		'ag_hero_show'          => true,
		'ag_hero_prefix'        => 'Bienvenue chez',
		'ag_hero_brand'         => '[Votre entreprise]',
		'ag_hero_subtitle'      => 'Travaux de qualite, devis gratuit, intervention rapide dans toute votre region.',
		'ag_hero_button'        => 'Demander un devis',
		'ag_hero_button_url'    => '#ag-services',
		// Footer.
		'ag_footer_copyright'   => '',
		'ag_footer_credits'     => true,
		// Home section leads (phrases d'intro sous les titres H2).
		'ag_artisan_prestations_lead' => 'Renovation, installation, entretien : nous intervenons pour tous vos travaux avec serieux et precision. Devis gratuit sous 24h.',
		'ag_artisan_zones_lead'       => 'Nous intervenons dans toute votre region, y compris en urgence. Appelez-nous au 06 00 00 00 00 pour toute demande rapide.',
		'ag_artisan_realisations_lead'=> 'Decouvrez nos chantiers recents : renovations de maison, installations techniques et travaux sur-mesure pour particuliers et professionnels.',
		'ag_artisan_about_p1'         => 'Depuis plus de dix ans, notre equipe d\'artisans qualifies accompagne particuliers et professionnels dans tous leurs projets de travaux. Rigueur, transparence sur les prix et respect des delais : voila notre engagement.',
		'ag_artisan_about_p2'         => 'Nous mettons un point d\'honneur a livrer des chantiers propres et conformes aux normes. Chaque intervention est suivie personnellement du devis a la livraison finale.',
		// Home section H2 titles (split pre + em for editorial flexibility).
		'ag_artisan_prestations_title_pre'  => 'Nos',
		'ag_artisan_prestations_title_em'   => 'prestations',
		'ag_artisan_zones_title_pre'        => 'Zones',
		'ag_artisan_zones_title_em'         => 'd\'intervention',
		'ag_artisan_realisations_title_pre' => 'Nos',
		'ag_artisan_realisations_title_em'  => 'realisations',
		'ag_artisan_about_title_pre'        => 'Qui',
		'ag_artisan_about_title_em'         => 'sommes-nous',
	);
}

/**
 * Retrieve a customizer setting with its default fallback.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function ag_starter_artisan_get_option( $key ) {
	$defaults = ag_starter_artisan_customizer_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( $key, $default );
}

/**
 * Register the customizer panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function ag_starter_artisan_customize_register( $wp_customize ) {
	$defaults = ag_starter_artisan_customizer_defaults();

	// Panel.
	$wp_customize->add_panel(
		'ag_starter_panel',
		array(
			'title'       => esc_html__( 'AG Starter — Personnalisation', 'ag-starter-artisan' ),
			'description' => esc_html__( 'Modifiez les couleurs, la typographie et les textes cles de votre theme directement ici. Aucun code requis.', 'ag-starter-artisan' ),
			'priority'    => 30,
		)
	);

	// ─── Section: Améliorer mon thème (upgrade promo) ───
	$wp_customize->add_section(
		'ag_section_upgrade',
		array(
			'title'    => esc_html__( '💎 Ameliorer mon theme', 'ag-starter-artisan' ),
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
		new AG_Starter_Artisan_Upgrade_Control(
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
			'title'    => esc_html__( 'Couleurs du theme', 'ag-starter-artisan' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 10,
		)
	);

	$colors = array(
		'ag_color_accent'     => esc_html__( 'Couleur d\'accent', 'ag-starter-artisan' ),
		'ag_color_background' => esc_html__( 'Arriere-plan principal', 'ag-starter-artisan' ),
		'ag_color_panel'      => esc_html__( 'Arriere-plan des cartes', 'ag-starter-artisan' ),
		'ag_color_border'     => esc_html__( 'Couleur des bordures', 'ag-starter-artisan' ),
		'ag_color_text'       => esc_html__( 'Couleur du texte', 'ag-starter-artisan' ),
		'ag_color_heading'    => esc_html__( 'Couleur des titres', 'ag-starter-artisan' ),
		'ag_color_muted'      => esc_html__( 'Texte secondaire', 'ag-starter-artisan' ),
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
			'title'    => esc_html__( 'Typographie', 'ag-starter-artisan' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 20,
		)
	);

	$wp_customize->add_setting(
		'ag_font_family',
		array(
			'default'           => $defaults['ag_font_family'],
			'sanitize_callback' => 'ag_starter_artisan_sanitize_select',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_family',
		array(
			'label'   => esc_html__( 'Famille de police', 'ag-starter-artisan' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'system'     => esc_html__( 'Systeme (defaut, rapide)', 'ag-starter-artisan' ),
				'sans'       => esc_html__( 'Sans-serif classique', 'ag-starter-artisan' ),
				'serif'      => esc_html__( 'Serif (elegant)', 'ag-starter-artisan' ),
				'monospace'  => esc_html__( 'Monospace', 'ag-starter-artisan' ),
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
			'label'       => esc_html__( 'Taille de base du texte (px)', 'ag-starter-artisan' ),
			'description' => esc_html__( 'Entre 14 et 20.', 'ag-starter-artisan' ),
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
			'sanitize_callback' => 'ag_starter_artisan_sanitize_select',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_heading_scale',
		array(
			'label'   => esc_html__( 'Taille des titres', 'ag-starter-artisan' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'small'   => esc_html__( 'Compact', 'ag-starter-artisan' ),
				'default' => esc_html__( 'Par defaut', 'ag-starter-artisan' ),
				'large'   => esc_html__( 'Grand', 'ag-starter-artisan' ),
			),
		)
	);

	// ─── Section: Hero ───
	$wp_customize->add_section(
		'ag_section_hero',
		array(
			'title'    => esc_html__( 'Hero (accueil)', 'ag-starter-artisan' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 30,
		)
	);

	$hero_fields = array(
		'ag_hero_show'       => array(
			'label' => esc_html__( 'Afficher le hero', 'ag-starter-artisan' ),
			'type'  => 'checkbox',
		),
		'ag_hero_prefix'     => array(
			'label' => esc_html__( 'Prefixe du titre', 'ag-starter-artisan' ),
			'type'  => 'text',
		),
		'ag_hero_brand'      => array(
			'label' => esc_html__( 'Nom de l\'etablissement', 'ag-starter-artisan' ),
			'type'  => 'text',
		),
		'ag_hero_subtitle'   => array(
			'label' => esc_html__( 'Sous-titre', 'ag-starter-artisan' ),
			'type'  => 'textarea',
		),
		'ag_hero_button'     => array(
			'label' => esc_html__( 'Texte du bouton', 'ag-starter-artisan' ),
			'type'  => 'text',
		),
		'ag_hero_button_url' => array(
			'label' => esc_html__( 'Lien du bouton (URL ou #ancre)', 'ag-starter-artisan' ),
			'type'  => 'text',
		),
	);
	$prio = 10;
	foreach ( $hero_fields as $key => $meta ) {
		$sanitize = 'sanitize_text_field';
		if ( 'checkbox' === $meta['type'] ) {
			$sanitize = 'ag_starter_artisan_sanitize_checkbox';
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
			'title'    => esc_html__( 'Pied de page', 'ag-starter-artisan' ),
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
			'label'       => esc_html__( 'Texte de copyright personnalise', 'ag-starter-artisan' ),
			'description' => esc_html__( 'Laissez vide pour le texte par defaut.', 'ag-starter-artisan' ),
			'section'     => 'ag_section_footer',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'ag_footer_credits',
		array(
			'default'           => $defaults['ag_footer_credits'],
			'sanitize_callback' => 'ag_starter_artisan_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_footer_credits',
		array(
			'label'   => esc_html__( 'Afficher le credit "Theme gratuit par Alliance Group"', 'ag-starter-artisan' ),
			'section' => 'ag_section_footer',
			'type'    => 'checkbox',
		)
	);

	// ─── Section: Contenu accueil — textes (leads des sections) ───
	$wp_customize->add_section(
		'ag_section_home_content',
		array(
			'title'       => esc_html__( 'Contenu accueil — textes', 'ag-starter-artisan' ),
			'panel'       => 'ag_starter_panel',
			'priority'    => 55,
			'description' => esc_html__( 'Personnalisez les phrases d\'introduction (sous les titres) de chaque section de l\'accueil.', 'ag-starter-artisan' ),
		)
	);
	$ag_artisan_home_fields = array(
		'ag_artisan_prestations_title_pre'  => array( 'label' => esc_html__( 'Prestations — debut du titre', 'ag-starter-artisan' ),         'type' => 'text' ),
		'ag_artisan_prestations_title_em'   => array( 'label' => esc_html__( 'Prestations — mot accentue', 'ag-starter-artisan' ),           'type' => 'text' ),
		'ag_artisan_prestations_lead'       => array( 'label' => esc_html__( 'Prestations — phrase d\'intro', 'ag-starter-artisan' ),        'type' => 'textarea' ),
		'ag_artisan_zones_title_pre'        => array( 'label' => esc_html__( 'Zones — debut du titre', 'ag-starter-artisan' ),               'type' => 'text' ),
		'ag_artisan_zones_title_em'         => array( 'label' => esc_html__( 'Zones — mot accentue', 'ag-starter-artisan' ),                 'type' => 'text' ),
		'ag_artisan_zones_lead'             => array( 'label' => esc_html__( 'Zones d\'intervention — phrase d\'intro', 'ag-starter-artisan' ), 'type' => 'textarea' ),
		'ag_artisan_realisations_title_pre' => array( 'label' => esc_html__( 'Realisations — debut du titre', 'ag-starter-artisan' ),        'type' => 'text' ),
		'ag_artisan_realisations_title_em'  => array( 'label' => esc_html__( 'Realisations — mot accentue', 'ag-starter-artisan' ),          'type' => 'text' ),
		'ag_artisan_realisations_lead'      => array( 'label' => esc_html__( 'Realisations — phrase d\'intro', 'ag-starter-artisan' ),       'type' => 'textarea' ),
		'ag_artisan_about_title_pre'        => array( 'label' => esc_html__( 'Qui sommes-nous — debut du titre', 'ag-starter-artisan' ),     'type' => 'text' ),
		'ag_artisan_about_title_em'         => array( 'label' => esc_html__( 'Qui sommes-nous — mot accentue', 'ag-starter-artisan' ),       'type' => 'text' ),
		'ag_artisan_about_p1'               => array( 'label' => esc_html__( 'Qui sommes-nous — paragraphe 1', 'ag-starter-artisan' ),       'type' => 'textarea' ),
		'ag_artisan_about_p2'               => array( 'label' => esc_html__( 'Qui sommes-nous — paragraphe 2', 'ag-starter-artisan' ),       'type' => 'textarea' ),
	);
	$ag_prio = 10;
	foreach ( $ag_artisan_home_fields as $ag_key => $ag_meta ) {
		$ag_type     = isset( $ag_meta['type'] ) ? $ag_meta['type'] : 'textarea';
		$ag_sanitize = ( 'textarea' === $ag_type ) ? 'sanitize_textarea_field' : 'sanitize_text_field';
		$wp_customize->add_setting(
			$ag_key,
			array(
				'default'           => isset( $defaults[ $ag_key ] ) ? $defaults[ $ag_key ] : '',
				'sanitize_callback' => $ag_sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$ag_key,
			array(
				'label'    => $ag_meta['label'],
				'section'  => 'ag_section_home_content',
				'type'     => $ag_type,
				'priority' => $ag_prio,
			)
		);
		$ag_prio += 5;
	}
}
add_action( 'customize_register', 'ag_starter_artisan_customize_register' );

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Value.
 * @return bool
 */
function ag_starter_artisan_sanitize_checkbox( $value ) {
	return ( isset( $value ) && true === (bool) $value );
}

/**
 * Sanitize a select choice against the registered choices.
 *
 * @param string               $value   Raw value.
 * @param WP_Customize_Setting $setting Setting object.
 * @return string
 */
function ag_starter_artisan_sanitize_select( $value, $setting = null ) {
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
function ag_starter_artisan_register_upgrade_control() {
	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		return;
	}
	if ( class_exists( 'AG_Starter_Artisan_Upgrade_Control' ) ) {
		return;
	}

	class AG_Starter_Artisan_Upgrade_Control extends WP_Customize_Control {

		public $type = 'ag_upgrade_banner';

		public function render_content() {
			$utm     = '?utm_source=wp-customizer&utm_medium=ag-starter-artisan&utm_campaign=upgrade';
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
					'desc'  => 'Notre design le plus abouti : animations, blocs premium, header sticky, polices, temoignages, galerie, WooCommerce, couleurs avancees, support. Paiement unique.',
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
							<?php esc_html_e( 'Vous utilisez la version gratuite. Packs payants disponibles :', 'ag-starter-artisan' ); ?>
						</p>
					<?php else : ?>
						<p style="margin:0 0 12px;color:#50575e;font-size:12px;line-height:1.5;">
							<?php esc_html_e( 'Upgrader vers le niveau supérieur :', 'ag-starter-artisan' ); ?>
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
						💎 <?php esc_html_e( 'Site sur-mesure (+340% leads) →', 'ag-starter-artisan' ); ?>
					</a>
					<p style="margin:8px 0 0;color:#888;font-size:11px;">
						<?php esc_html_e( 'Premier appel gratuit, sans engagement', 'ag-starter-artisan' ); ?>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
add_action( 'customize_register', 'ag_starter_artisan_register_upgrade_control', 1 );

require get_template_directory() . '/inc/customizer-output.php';
