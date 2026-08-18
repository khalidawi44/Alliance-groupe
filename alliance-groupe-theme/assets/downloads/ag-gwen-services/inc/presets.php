<?php
/**
 * Presets services a la personne — 3 specialites pour adapter le theme en
 * 1 clic (maintien a domicile seniors, services aux familles, aide au
 * handicap & dependance). Chaque preset configure le hero, la couleur
 * d'accent, la grille de services + le contenu des pages.
 *
 * Apparait dans : "Apparence > 🎯 Configuration metier".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Domicile_Presets {

	public static function get_presets() {
		return array(

			'domicile_familles' => array(
				'icon'  => '🏡',
				'label' => 'Gwen Services (par défaut)',
				'desc'  => 'Configuration sur-mesure de Gwen Services : aide aux personnes âgées, handicap léger et garde d’enfants à Nantes.',
				'mods'  => array(
					'ag_color_accent'         => '#4f9d6b',
					'ag_hero_prefix'          => 'À vos côtés, chez vous —',
					'ag_hero_brand'           => 'Gwen Services',
					'ag_hero_subtitle'        => 'Aide à domicile à Nantes : accompagnement des personnes âgées, aide au handicap léger et garde d’enfants (de 1 mois à 8 ans). Une présence de confiance — crédit d’impôt de 50 %.',
					'ag_hero_button'          => 'Devis gratuit',
					'ag_hero_button_url'      => '/devis/',
					'ag_domicile_metier_nom'  => 'aide à domicile à Nantes',
					'ag_domicile_hero_image'  => 'THEME:/assets/hero.jpg',
					'ag_domicile_testi_1'     => 'Gwen est douce, ponctuelle et d’une grande patience avec ma mère. On a enfin l’esprit tranquille.|Christine|Nantes',
					'ag_domicile_testi_2'     => 'Elle garde nos deux enfants après l’école : sérieuse, chaleureuse, de confiance. On recommande.|Émilie & Thomas|Rezé',
					'ag_domicile_testi_3'     => 'Un accompagnement respectueux pour mon frère en situation de handicap. Et le crédit d’impôt allège tout.|Patrick|Saint-Herblain',
				),
				'services' => array(
					array( 'emoji' => '👵', 'title' => 'Aide aux personnes âgées' ),
					array( 'emoji' => '🤝', 'title' => 'Aide au handicap léger' ),
					array( 'emoji' => '👶', 'title' => 'Garde d’enfants (1 mois–8 ans)' ),
					array( 'emoji' => '🌅', 'title' => 'Aide au quotidien' ),
					array( 'emoji' => '🍲', 'title' => 'Préparation des repas' ),
					array( 'emoji' => '💬', 'title' => 'Compagnie & présence' ),
					array( 'emoji' => '🚗', 'title' => 'Accompagnement & sorties' ),
					array( 'emoji' => '🎒', 'title' => 'Sortie d’école' ),
				),
				'how' => array(
					array( 'emoji' => '☎️', 'title' => 'Premier échange gratuit', 'desc' => 'Vous m’expliquez votre besoin (un parent âgé, un enfant à garder, un proche en situation de handicap) : on cadre ensemble, sans engagement.' ),
					array( 'emoji' => '🗓️', 'title' => 'Un planning adapté', 'desc' => 'Je vous propose des horaires qui collent à votre organisation, en ponctuel ou en régulier.' ),
					array( 'emoji' => '💚', 'title' => 'Une présence de confiance', 'desc' => 'La même personne, à l’écoute, avec l’avance immédiate du crédit d’impôt de 50 %.' ),
				),
				'faq' => array(
					array( 'q' => 'Le crédit d’impôt de 50 % s’applique-t-il ?', 'a' => 'Oui, sur l’ensemble des prestations à domicile (aide aux seniors, garde d’enfants, aide au handicap), avec avance immédiate — même sans être imposable.' ),
					array( 'q' => 'À partir de quel âge gardez-vous les enfants ?', 'a' => 'De 1 mois à 8 ans, à domicile, en journée ou en sortie d’école, selon vos besoins.' ),
					array( 'q' => 'Intervenez-vous le week-end ?', 'a' => 'Oui, 7j/7 selon les disponibilités, en ponctuel ou en régulier.' ),
					array( 'q' => 'Comment démarrer ?', 'a' => 'Un simple appel ou une demande de devis. L’échange et l’évaluation des besoins sont gratuits et sans engagement.' ),
				),
				'stats' => array(
					array( 'value' => '50 %',  'label' => 'Crédit d’impôt' ),
					array( 'value' => '7j/7',  'label' => 'Disponibilité' ),
					array( 'value' => 'Nantes', 'label' => 'Et alentours' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Gwen Services, c’est une présence de confiance à domicile, à Nantes et dans ses environs. J’accompagne les personnes âgées, les personnes en situation de handicap léger, et je garde vos enfants de 1 mois à 8 ans.\n\nMon engagement : de l’écoute, de la douceur, de la fiabilité. La même personne pour votre famille, des horaires adaptés à votre organisation, en ponctuel ou en régulier.\n\nToutes les prestations ouvrent droit au crédit d’impôt de 50 %, avec avance immédiate. Premier échange et évaluation des besoins gratuits, sans engagement.",
					'prestations'        => "Auprès des personnes âgées : aide au lever et au coucher, aide aux repas, compagnie et présence, courses et accompagnement aux sorties.\n\nGarde d’enfants (1 mois–8 ans) : garde à domicile en journée, sortie d’école, activités et goûter, dans un cadre sécurisant.\n\nHandicap léger : aide au quotidien, présence et accompagnement respectueux. Toutes les prestations ouvrent droit au crédit d’impôt de 50 %.",
					'zones-intervention' => "J’interviens à Nantes et dans son agglomération : Rezé, Saint-Herblain, Orvault, Vertou, Bouguenais, Saint-Sébastien-sur-Loire, Carquefou et alentours.\n\nEn journée comme en soirée, en ponctuel ou en régulier, avec des délais de mise en place courts.\n\nVotre commune n’apparaît pas ? Contactez-moi : j’étudie chaque demande avec plaisir.",
					'realisations'       => "Chaque semaine, j’accompagne des familles nantaises : un parent âgé qui reste chez lui, des enfants gardés après l’école, un proche en situation de handicap soutenu au quotidien.\n\nCe qui fait la différence : la confiance, la régularité et une vraie relation humaine.\n\nDécouvrez les témoignages ci-dessous, et demandez votre devis : on construit ensemble la solution qui vous convient.",
				),
			),

		);
	}

			public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 22 );
		add_action( 'admin_post_ag_domicile_apply_preset', array( __CLASS__, 'handle_apply' ) );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Configuration métier', 'ag-gwen-services' ),
			'🎯 ' . __( 'Configuration métier', 'ag-gwen-services' ),
			'manage_options',
			'ag-domicile-presets',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$applied = isset( $_GET['applied'] ) ? sanitize_key( $_GET['applied'] ) : '';
		$current = get_theme_mod( 'ag_domicile_metier_slug', '' );
		$presets = self::get_presets();
		?>
		<div class="wrap">
			<h1>🎯 <?php esc_html_e( 'Configuration métier — Gwen Services', 'ag-gwen-services' ); ?></h1>
			<p style="font-size:1.05em;color:#50575e;max-width:820px;"><?php esc_html_e( 'Choisissez votre spécialité de accompagnement ci-dessous. En 1 clic, le thème adapte le hero, la couleur d\'accent et la grille des 8 services. Affinez ensuite chaque texte dans Apparence > Personnaliser.', 'ag-gwen-services' ); ?></p>

			<?php if ( $applied && isset( $presets[ $applied ] ) ) : ?>
				<div class="notice notice-success"><p><strong>✅ Preset « <?php echo esc_html( $presets[ $applied ]['icon'] . ' ' . $presets[ $applied ]['label'] ); ?> » appliqué.</strong> Visitez la page d'accueil pour voir le résultat.</p></div>
			<?php endif; ?>

			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px;margin-top:24px;max-width:1200px;">
				<?php foreach ( $presets as $slug => $preset ) :
					$is_current = ( $slug === $current );
				?>
				<div style="background:#fff;border:2px solid <?php echo $is_current ? esc_attr( $preset['mods']['ag_color_accent'] ) : '#ccd0d4'; ?>;border-radius:10px;padding:24px;<?php echo $is_current ? 'box-shadow:0 4px 16px rgba(0,0,0,.08);' : ''; ?>">
					<div style="font-size:3em;margin-bottom:8px;"><?php echo esc_html( $preset['icon'] ); ?></div>
					<h2 style="margin:0 0 8px;font-size:1.4em;"><?php echo esc_html( $preset['label'] ); ?></h2>
					<?php if ( $is_current ) : ?>
						<span style="display:inline-block;padding:2px 10px;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;border-radius:12px;font-size:.78em;font-weight:700;margin-bottom:10px;">✓ ACTUELLEMENT APPLIQUÉ</span>
					<?php endif; ?>
					<p style="color:#50575e;font-size:.95em;line-height:1.5;margin:8px 0 14px;"><?php echo esc_html( $preset['desc'] ); ?></p>
					<p style="font-size:.85em;color:#777;margin:0 0 14px;"><strong>8 services inclus :</strong></p>
					<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;">
						<?php foreach ( $preset['services'] as $svc ) : ?>
							<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:14px;font-size:.78em;">
								<?php echo esc_html( $svc['emoji'] ); ?> <?php echo esc_html( $svc['title'] ); ?>
							</span>
						<?php endforeach; ?>
					</div>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Appliquer le preset « <?php echo esc_js( $preset['label'] ); ?> » ? Cela remplacera votre hero, couleur d\'accent et grille services actuels.');">
						<input type="hidden" name="action" value="ag_domicile_apply_preset" />
						<input type="hidden" name="preset" value="<?php echo esc_attr( $slug ); ?>" />
						<?php wp_nonce_field( 'ag_domicile_apply_preset' ); ?>
						<button type="submit" class="button button-primary button-large" style="width:100%;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;border-color:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;font-weight:700;">
							<?php echo $is_current ? '🔄 Réappliquer' : '✨ Appliquer ce preset'; ?>
						</button>
					</form>
				</div>
				<?php endforeach; ?>
			</div>

			<p style="margin-top:32px;color:#777;font-size:.9em;">
				💡 <?php esc_html_e( 'Après application, fine-tunez via', 'ag-gwen-services' ); ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Apparence > Personnaliser', 'ag-gwen-services' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-domicile-reset' ) ); ?>">🔄 <?php esc_html_e( 'Réinitialiser le thème', 'ag-gwen-services' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function handle_apply() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_domicile_apply_preset' );
		$slug    = isset( $_POST['preset'] ) ? sanitize_key( $_POST['preset'] ) : '';
		$presets = self::get_presets();
		if ( ! isset( $presets[ $slug ] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-domicile-presets' ) );
			exit;
		}
		$preset = $presets[ $slug ];

		foreach ( $preset['mods'] as $key => $val ) {
			// Jeton "THEME:/..." -> image embarquée dans le thème (URL résolue à l'install).
			if ( is_string( $val ) && 0 === strpos( $val, 'THEME:' ) ) {
				$val = get_template_directory_uri() . substr( $val, 6 );
			}
			set_theme_mod( $key, $val );
		}
		set_theme_mod( 'ag_domicile_metier_slug', $slug );
		set_theme_mod( 'ag_domicile_services_json', wp_json_encode( $preset['services'] ) );

		if ( ! empty( $preset['pages'] ) && is_array( $preset['pages'] ) ) {
			foreach ( $preset['pages'] as $slug => $content ) {
				$page = get_page_by_path( $slug );
				if ( ! $page ) continue;
				$paragraphs = explode( "\n\n", trim( $content ) );
				$wrapped = '';
				foreach ( $paragraphs as $p ) {
					$p = trim( $p );
					if ( $p === '' ) continue;
					$wrapped .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
				}
				wp_update_post( array(
					'ID'           => $page->ID,
					'post_content' => trim( $wrapped ),
				) );
			}
		}

		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();
		delete_site_transient( 'update_themes' );

		wp_safe_redirect( admin_url( 'themes.php?page=ag-domicile-presets&applied=' . $slug ) );
		exit;
	}

	public static function get_active_services() {
		$json = get_theme_mod( 'ag_domicile_services_json', '' );
		if ( ! $json ) return array();
		$arr = json_decode( $json, true );
		return is_array( $arr ) ? $arr : array();
	}

	public static function get_active_preset() {
		$slug = get_theme_mod( 'ag_domicile_metier_slug', '' );
		if ( ! $slug ) return null;
		$presets = self::get_presets();
		return isset( $presets[ $slug ] ) ? $presets[ $slug ] : null;
	}

	public static function get_active_stats() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['stats'] ) ) ? $p['stats'] : array();
	}

	public static function get_active_how() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['how'] ) ) ? $p['how'] : array();
	}

	public static function get_active_faq() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['faq'] ) ) ? $p['faq'] : array();
	}

	public static function get_testimonial( $i ) {
		$raw = get_theme_mod( 'ag_domicile_testi_' . (int) $i, '' );
		if ( ! $raw ) return null;
		$parts = array_map( 'trim', explode( '|', $raw ) );
		return array(
			'text' => isset( $parts[0] ) ? $parts[0] : '',
			'name' => isset( $parts[1] ) ? $parts[1] : '',
			'city' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
}
AG_Domicile_Presets::init();

add_filter( 'body_class', function ( $classes ) {
	if ( class_exists( 'AG_Domicile_Presets' ) && AG_Domicile_Presets::get_active_preset() ) {
		$classes[] = 'ag-premium-mode';
		$slug = get_theme_mod( 'ag_domicile_metier_slug', '' );
		if ( $slug ) {
			$classes[] = 'ag-metier-' . sanitize_html_class( $slug );
		}
	}
	return $classes;
} );
