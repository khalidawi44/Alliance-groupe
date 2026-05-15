<?php
/**
 * AG Starter Restaurant Customizer.
 *
 * Registers settings, sections and controls under Appearance >
 * Customize so users can change colors, typography, hero text and
 * footer text live, without editing any code — same experience
 * as Astra, OceanWP or Kadence.
 *
 * @package AG_Starter_Coach
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values per theme flavour.
 *
 * @return array
 */
function ag_starter_coach_customizer_defaults() {
	return array(
		// Colors.
		'ag_color_accent'       => '#5ec4d1',
		'ag_color_background'   => '#0b1220',
		'ag_color_panel'        => '#121a2a',
		'ag_color_border'       => '#1f2a3f',
		'ag_color_text'         => '#e0e0e0',
		'ag_color_heading'      => '#ffffff',
		'ag_color_muted'        => '#aaaaaa',
		// Typography.
		'ag_font_family'        => 'system',
		'ag_font_base_size'     => 16,
		'ag_font_heading_scale' => 'default',
		// Hero.
		'ag_hero_show'          => true,
		'ag_hero_prefix'        => 'Transformez votre potentiel avec',
		'ag_hero_brand'         => '[Votre Nom]',
		'ag_hero_subtitle'      => 'Coaching sur-mesure pour avancer avec clarte, confiance et resultats mesurables.',
		'ag_hero_button'        => 'Prendre rendez-vous',
		'ag_hero_button_url'    => '#ag-services',
		// Footer.
		'ag_footer_copyright'   => '',
		'ag_footer_credits'     => true,
		// Home section leads (phrases d'intro sous les titres H2).
		'ag_coach_individuel_lead' => 'Un accompagnement sur-mesure pour atteindre vos objectifs personnels ou professionnels. Seance d\'essai gratuite de 30 minutes.',
		'ag_coach_groupe_lead'     => 'Ateliers thematiques et formations collectives pour progresser ensemble dans un cadre bienveillant et structurant.',
		'ag_coach_temoignages_lead'=> 'Decouvrez les retours de mes clients : reconversion reussie, prise de parole debloquee, confiance retrouvee.',
		'ag_coach_about_p1'        => 'Coach professionnelle certifiee, j\'accompagne depuis plus de dix ans des dirigeants, entrepreneurs et particuliers dans leurs transformations. Mon approche combine ecoute active, methodes eprouvees et ancrage concret.',
		'ag_coach_about_p2'        => 'Chaque accompagnement est unique : je m\'adapte a votre rythme et a vos objectifs, avec pour seule boussole votre progres. Premier rendez-vous toujours gratuit pour valider ensemble la meilleure formule.',
		// Home section H2 titles (split pre + em for editorial flexibility).
		'ag_coach_individuel_title_pre'  => 'Coaching',
		'ag_coach_individuel_title_em'   => 'individuel',
		'ag_coach_groupe_title_pre'      => 'Seances',
		'ag_coach_groupe_title_em'       => 'de groupe',
		'ag_coach_temoignages_title_pre' => '',
		'ag_coach_temoignages_title_em'  => 'Temoignages',
		'ag_coach_about_title_pre'       => 'Mon',
		'ag_coach_about_title_em'        => 'parcours',
	);
}

/**
 * Retrieve a customizer setting with its default fallback.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function ag_starter_coach_get_option( $key ) {
	$defaults = ag_starter_coach_customizer_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( $key, $default );
}

/**
 * Register the customizer panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function ag_starter_coach_customize_register( $wp_customize ) {
	$defaults = ag_starter_coach_customizer_defaults();

	// Panel.
	$wp_customize->add_panel(
		'ag_starter_panel',
		array(
			'title'       => esc_html__( 'AG Starter — Personnalisation', 'ag-starter-coach' ),
			'description' => esc_html__( 'Modifiez les couleurs, la typographie et les textes cles de votre theme directement ici. Aucun code requis.', 'ag-starter-coach' ),
			'priority'    => 30,
		)
	);

	// ─── Section: Améliorer mon thème (upgrade promo) ───
	$wp_customize->add_section(
		'ag_section_upgrade',
		array(
			'title'    => esc_html__( '💎 Ameliorer mon theme', 'ag-starter-coach' ),
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
		new AG_Starter_Coach_Upgrade_Control(
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
			'title'    => esc_html__( 'Couleurs du theme', 'ag-starter-coach' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 10,
		)
	);

	$colors = array(
		'ag_color_accent'     => esc_html__( 'Couleur d\'accent', 'ag-starter-coach' ),
		'ag_color_background' => esc_html__( 'Arriere-plan principal', 'ag-starter-coach' ),
		'ag_color_panel'      => esc_html__( 'Arriere-plan des cartes', 'ag-starter-coach' ),
		'ag_color_border'     => esc_html__( 'Couleur des bordures', 'ag-starter-coach' ),
		'ag_color_text'       => esc_html__( 'Couleur du texte', 'ag-starter-coach' ),
		'ag_color_heading'    => esc_html__( 'Couleur des titres', 'ag-starter-coach' ),
		'ag_color_muted'      => esc_html__( 'Texte secondaire', 'ag-starter-coach' ),
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
			'title'    => esc_html__( 'Typographie', 'ag-starter-coach' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 20,
		)
	);

	$wp_customize->add_setting(
		'ag_font_family',
		array(
			'default'           => $defaults['ag_font_family'],
			'sanitize_callback' => 'ag_starter_coach_sanitize_select',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_family',
		array(
			'label'   => esc_html__( 'Famille de police', 'ag-starter-coach' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'system'     => esc_html__( 'Systeme (defaut, rapide)', 'ag-starter-coach' ),
				'sans'       => esc_html__( 'Sans-serif classique', 'ag-starter-coach' ),
				'serif'      => esc_html__( 'Serif (elegant)', 'ag-starter-coach' ),
				'monospace'  => esc_html__( 'Monospace', 'ag-starter-coach' ),
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
			'label'       => esc_html__( 'Taille de base du texte (px)', 'ag-starter-coach' ),
			'description' => esc_html__( 'Entre 14 et 20.', 'ag-starter-coach' ),
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
			'sanitize_callback' => 'ag_starter_coach_sanitize_select',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_font_heading_scale',
		array(
			'label'   => esc_html__( 'Taille des titres', 'ag-starter-coach' ),
			'section' => 'ag_section_typography',
			'type'    => 'select',
			'choices' => array(
				'small'   => esc_html__( 'Compact', 'ag-starter-coach' ),
				'default' => esc_html__( 'Par defaut', 'ag-starter-coach' ),
				'large'   => esc_html__( 'Grand', 'ag-starter-coach' ),
			),
		)
	);

	// ─── Section: Hero ───
	$wp_customize->add_section(
		'ag_section_hero',
		array(
			'title'    => esc_html__( 'Hero (accueil)', 'ag-starter-coach' ),
			'panel'    => 'ag_starter_panel',
			'priority' => 30,
		)
	);

	$hero_fields = array(
		'ag_hero_show'       => array(
			'label' => esc_html__( 'Afficher le hero', 'ag-starter-coach' ),
			'type'  => 'checkbox',
		),
		'ag_hero_prefix'     => array(
			'label' => esc_html__( 'Prefixe du titre', 'ag-starter-coach' ),
			'type'  => 'text',
		),
		'ag_hero_brand'      => array(
			'label' => esc_html__( 'Nom de l\'etablissement', 'ag-starter-coach' ),
			'type'  => 'text',
		),
		'ag_hero_subtitle'   => array(
			'label' => esc_html__( 'Sous-titre', 'ag-starter-coach' ),
			'type'  => 'textarea',
		),
		'ag_hero_button'     => array(
			'label' => esc_html__( 'Texte du bouton', 'ag-starter-coach' ),
			'type'  => 'text',
		),
		'ag_hero_button_url' => array(
			'label' => esc_html__( 'Lien du bouton (URL ou #ancre)', 'ag-starter-coach' ),
			'type'  => 'text',
		),
	);
	$prio = 10;
	foreach ( $hero_fields as $key => $meta ) {
		$sanitize = 'sanitize_text_field';
		if ( 'checkbox' === $meta['type'] ) {
			$sanitize = 'ag_starter_coach_sanitize_checkbox';
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
			'title'    => esc_html__( 'Pied de page', 'ag-starter-coach' ),
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
			'label'       => esc_html__( 'Texte de copyright personnalise', 'ag-starter-coach' ),
			'description' => esc_html__( 'Laissez vide pour le texte par defaut.', 'ag-starter-coach' ),
			'section'     => 'ag_section_footer',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'ag_footer_credits',
		array(
			'default'           => $defaults['ag_footer_credits'],
			'sanitize_callback' => 'ag_starter_coach_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'ag_footer_credits',
		array(
			'label'   => esc_html__( 'Afficher le credit "Theme gratuit par Alliance Group"', 'ag-starter-coach' ),
			'section' => 'ag_section_footer',
			'type'    => 'checkbox',
		)
	);

	// ─── Section: Contenu accueil — textes (leads des sections) ───
	$wp_customize->add_section(
		'ag_section_home_content',
		array(
			'title'       => esc_html__( 'Contenu accueil — textes', 'ag-starter-coach' ),
			'panel'       => 'ag_starter_panel',
			'priority'    => 55,
			'description' => esc_html__( 'Personnalisez les phrases d\'introduction (sous les titres) de chaque section de l\'accueil.', 'ag-starter-coach' ),
		)
	);
	$ag_coach_home_fields = array(
		'ag_coach_individuel_title_pre'  => array( 'label' => esc_html__( 'Coaching individuel — debut du titre', 'ag-starter-coach' ),  'type' => 'text' ),
		'ag_coach_individuel_title_em'   => array( 'label' => esc_html__( 'Coaching individuel — mot accentue', 'ag-starter-coach' ),    'type' => 'text' ),
		'ag_coach_individuel_lead'       => array( 'label' => esc_html__( 'Coaching individuel — phrase d\'intro', 'ag-starter-coach' ), 'type' => 'textarea' ),
		'ag_coach_groupe_title_pre'      => array( 'label' => esc_html__( 'Seances de groupe — debut du titre', 'ag-starter-coach' ),    'type' => 'text' ),
		'ag_coach_groupe_title_em'       => array( 'label' => esc_html__( 'Seances de groupe — mot accentue', 'ag-starter-coach' ),      'type' => 'text' ),
		'ag_coach_groupe_lead'           => array( 'label' => esc_html__( 'Seances de groupe — phrase d\'intro', 'ag-starter-coach' ),   'type' => 'textarea' ),
		'ag_coach_temoignages_title_pre' => array( 'label' => esc_html__( 'Temoignages — debut du titre', 'ag-starter-coach' ),          'type' => 'text' ),
		'ag_coach_temoignages_title_em'  => array( 'label' => esc_html__( 'Temoignages — mot accentue', 'ag-starter-coach' ),            'type' => 'text' ),
		'ag_coach_temoignages_lead'      => array( 'label' => esc_html__( 'Temoignages — phrase d\'intro', 'ag-starter-coach' ),         'type' => 'textarea' ),
		'ag_coach_about_title_pre'       => array( 'label' => esc_html__( 'Mon parcours — debut du titre', 'ag-starter-coach' ),         'type' => 'text' ),
		'ag_coach_about_title_em'        => array( 'label' => esc_html__( 'Mon parcours — mot accentue', 'ag-starter-coach' ),           'type' => 'text' ),
		'ag_coach_about_p1'              => array( 'label' => esc_html__( 'Mon parcours — paragraphe 1', 'ag-starter-coach' ),           'type' => 'textarea' ),
		'ag_coach_about_p2'              => array( 'label' => esc_html__( 'Mon parcours — paragraphe 2', 'ag-starter-coach' ),           'type' => 'textarea' ),
	);
	$ag_prio = 10;
	foreach ( $ag_coach_home_fields as $ag_key => $ag_meta ) {
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
add_action( 'customize_register', 'ag_starter_coach_customize_register' );

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Value.
 * @return bool
 */
function ag_starter_coach_sanitize_checkbox( $value ) {
	return ( isset( $value ) && true === (bool) $value );
}

/**
 * Sanitize a select choice against the registered choices.
 *
 * @param string               $value   Raw value.
 * @param WP_Customize_Setting $setting Setting object.
 * @return string
 */
function ag_starter_coach_sanitize_select( $value, $setting = null ) {
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
function ag_starter_coach_register_upgrade_control() {
	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		return;
	}
	if ( class_exists( 'AG_Starter_Coach_Upgrade_Control' ) ) {
		return;
	}

	class AG_Starter_Coach_Upgrade_Control extends WP_Customize_Control {

		public $type = 'ag_upgrade_banner';

		public function render_content() {
			$utm     = '?utm_source=wp-customizer&utm_medium=ag-starter-coach&utm_campaign=upgrade';
			$base    = 'https://alliancegroupe-inc.com/templates-wordpress';
			$contact = 'https://alliancegroupe-inc.com/contact' . $utm;

			// Détecte le tier de licence active (free / pro / premium / business).
			$tier  = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_tier() : 'free';
			$order = array( 'free' => 0, 'pro' => 1, 'premium' => 2, 'business' => 3 );
			$cur   = isset( $order[ $tier ] ) ? $order[ $tier ] : 0;

			$all_packs = array(
				'pro' => array(
					'icon'  => '⚡',
					'title' => 'Pack Pro',
					'price' => '49€',
					'desc'  => 'Animations, blocs Gutenberg, customizer avance, sticky header, polices premium',
					'url'   => $base . $utm . '&pack=pro#ag-pricing',
				),
				'premium' => array(
					'icon'  => '🌍',
					'title' => 'Pack Premium',
					'price' => '99€',
					'desc'  => 'Tout Pro + multi-langue 6 langues + WooCommerce + support 12 mois',
					'url'   => $base . $utm . '&pack=premium#ag-pricing',
				),
				'business' => array(
					'icon'  => '💼',
					'title' => 'Pack Business',
					'price' => '149€',
					'desc'  => 'Tout Premium + installation visio + maintenance 1 an + audit SEO + white-label',
					'url'   => $base . $utm . '&pack=business#ag-pricing',
				),
			);

			// Ne garde QUE les packs strictement supérieurs au tier courant.
			$packs = array();
			foreach ( $all_packs as $key => $p ) {
				if ( $order[ $key ] > $cur ) $packs[ $key ] = $p;
			}

			$tier_labels = array(
				'pro'      => '⚡ Pack Pro',
				'premium'  => '🌍 Pack Premium',
				'business' => '💼 Pack Business',
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
							<?php esc_html_e( 'Vous utilisez la version gratuite. Packs payants disponibles :', 'ag-starter-coach' ); ?>
						</p>
					<?php else : ?>
						<p style="margin:0 0 12px;color:#50575e;font-size:12px;line-height:1.5;">
							<?php esc_html_e( 'Upgrader vers le niveau supérieur :', 'ag-starter-coach' ); ?>
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
						💎 <?php esc_html_e( 'Site sur-mesure (+340% leads) →', 'ag-starter-coach' ); ?>
					</a>
					<p style="margin:8px 0 0;color:#888;font-size:11px;">
						<?php esc_html_e( 'Premier appel gratuit, sans engagement', 'ag-starter-coach' ); ?>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
add_action( 'customize_register', 'ag_starter_coach_register_upgrade_control', 1 );

require get_template_directory() . '/inc/customizer-output.php';
