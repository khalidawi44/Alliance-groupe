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

			'domicile_seniors' => array(
				'icon'  => '🏡',
				'label' => 'Maintien à domicile (seniors)',
				'desc'  => 'Aide et accompagnement des personnes âgées à domicile : aide au quotidien, présence, sécurité. Le cœur des services à la personne.',
				'mods'  => array(
					'ag_color_accent'         => '#4f9d6b',
					'ag_hero_prefix'          => 'Bien vivre chez soi, avec',
					'ag_hero_brand'           => 'Douceur de Vie',
					'ag_hero_subtitle'        => 'Aide à domicile pour les personnes âgées : aide au lever, repas, toilette, compagnie et présence rassurante. Intervenants qualifiés — crédit d’impôt de 50 %.',
					'ag_hero_button'          => 'Devis gratuit',
					'ag_hero_button_url'      => '/devis/',
					'ag_domicile_metier_nom'  => 'service d’aide à domicile',
					'ag_domicile_hero_image'  => 'THEME:/assets/hero.jpg',
					'ag_domicile_testi_1'     => 'Grâce à Douceur de Vie, ma mère reste chez elle en toute sécurité. L’auxiliaire est douce et fiable : un vrai soulagement.|Christine et sa maman|Nantes',
					'ag_domicile_testi_2'     => 'Toujours la même personne, ponctuelle et bienveillante. Mon père a repris goût à ses journées.|Michel R.|Rezé',
					'ag_domicile_testi_3'     => 'Un accompagnement humain, et le crédit d’impôt divise la facture par deux. Je recommande les yeux fermés.|Nadia B.|Saint-Herblain',
				),
				'services' => array(
					array( 'emoji' => '🌅', 'title' => 'Aide au lever & coucher' ),
					array( 'emoji' => '🍲', 'title' => 'Préparation des repas' ),
					array( 'emoji' => '🚿', 'title' => 'Aide à la toilette' ),
					array( 'emoji' => '🧹', 'title' => 'Entretien du logement' ),
					array( 'emoji' => '🛒', 'title' => 'Courses & accompagnement' ),
					array( 'emoji' => '💬', 'title' => 'Compagnie & lien social' ),
					array( 'emoji' => '🌙', 'title' => 'Garde de nuit & présence' ),
					array( 'emoji' => '🚗', 'title' => 'Accompagnement (RDV, sorties)' ),
				),
				'how' => array(
					array( 'emoji' => '☎️', 'title' => 'Évaluation gratuite à domicile', 'desc' => 'Nous venons chez vous évaluer les besoins et bâtir un plan d’aide personnalisé, sans engagement.' ),
					array( 'emoji' => '👩‍⚕️', 'title' => 'Mise en place de l’intervenant', 'desc' => 'Nous choisissons un(e) auxiliaire qualifié(e), présenté(e) à la famille, avec des horaires adaptés à votre proche.' ),
					array( 'emoji' => '💚', 'title' => 'Suivi & tranquillité', 'desc' => 'Coordination, remplacements assurés, ajustements réguliers. Vous gardez le lien avec nous à tout moment.' ),
				),
				'faq' => array(
					array( 'q' => 'Ai-je droit au crédit d’impôt ?', 'a' => 'Oui : 50 % des sommes versées sont déductibles. Avec l’avance immédiate, vous ne réglez directement que la moitié — même si vous n’êtes pas imposable.' ),
					array( 'q' => 'Peut-on avoir toujours la même personne ?', 'a' => 'Oui, nous privilégions un intervenant référent, avec une doublure formée pour assurer la continuité (congés, imprévus).' ),
					array( 'q' => 'Intervenez-vous le week-end et la nuit ?', 'a' => 'Oui, 7j/7, jours fériés compris, avec possibilité de garde de nuit ou de présence continue.' ),
					array( 'q' => 'Comment démarrer ?', 'a' => 'Un simple appel ou une demande de devis suffit. L’évaluation à domicile est gratuite et sans engagement ; la mise en place peut être rapide.' ),
				),
				'stats' => array(
					array( 'value' => '50 %',  'label' => 'Crédit d’impôt' ),
					array( 'value' => '7j/7',  'label' => 'Disponibilité' ),
					array( 'value' => '4,9/5', 'label' => 'Satisfaction familles' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Douceur de Vie est un service d’aide et d’accompagnement à domicile dédié aux personnes âgées. Notre mission : permettre à chacun de bien vieillir chez soi, entouré, en sécurité et dans le respect de ses habitudes.\n\nNos intervenants sont sélectionnés avec soin, formés et suivis : auxiliaires de vie, aides à domicile, gardes de nuit. Nous construisons avec la famille un plan d’aide sur mesure et assurons une continuité de service (référent + doublure).\n\nDéclaré au titre des services à la personne, notre service ouvre droit au crédit d’impôt de 50 %, avec avance immédiate. Devis et évaluation à domicile gratuits et sans engagement.",
					'prestations'        => "Aide aux actes essentiels du quotidien : aide au lever et au coucher, aide à la toilette et à l’habillage, préparation et aide à la prise des repas, aide à la mobilité.\n\nConfort et vie sociale : entretien du logement et du linge, courses, accompagnement aux rendez-vous et aux sorties, compagnie et lien social, stimulation et activités.\n\nSécurité et répit : présence de jour, garde de nuit, présence continue, soutien aux aidants familiaux. Interventions ponctuelles, régulières ou en sortie d’hospitalisation. Toutes nos prestations ouvrent droit au crédit d’impôt de 50 %.",
					'zones-intervention' => "Nous intervenons à Nantes et dans toute son agglomération : Rezé, Saint-Herblain, Orvault, Vertou, Bouguenais, Saint-Sébastien-sur-Loire, Carquefou et communes voisines.\n\nInterventions 7j/7, jours fériés compris, en journée comme la nuit. Délais de mise en place courts, y compris en sortie d’hospitalisation.\n\nVotre commune n’apparaît pas ? Contactez-nous : nous étendons régulièrement notre secteur et étudions chaque demande. Évaluation des besoins gratuite à domicile.",
					'realisations'       => "Chaque jour, nos auxiliaires accompagnent des dizaines de familles pour permettre à un proche de rester chez lui. Maintien à domicile après une chute, sortie d’hôpital sécurisée, répit pour un aidant épuisé, lutte contre l’isolement : autant de situations où notre présence change tout.\n\nCe qui fait la différence : un intervenant référent, une écoute réelle des familles, une coordination sans faille et des remplacements toujours assurés.\n\nDécouvrez les témoignages de familles ci-dessous — et demandez le vôtre : nous vous mettrons en relation avec des proches accompagnés dans une situation semblable.",
				),
			),

			'domicile_familles' => array(
				'icon'  => '👨‍👩‍👧',
				'label' => 'Services à la personne (familles)',
				'desc'  => 'Offre polyvalente pour toute la famille : aide aux seniors, ménage/repassage, garde d’enfants, aide au handicap.',
				'mods'  => array(
					'ag_color_accent'         => '#4f9d6b',
					'ag_hero_prefix'          => 'Le quotidien plus léger, avec',
					'ag_hero_brand'           => 'Douceur de Vie',
					'ag_hero_subtitle'        => 'Ménage, repassage, garde d’enfants, aide aux seniors et aux personnes en situation de handicap. Un seul interlocuteur de confiance — crédit d’impôt de 50 %.',
					'ag_hero_button'          => 'Devis gratuit',
					'ag_hero_button_url'      => '/devis/',
					'ag_domicile_metier_nom'  => 'agence de services à la personne',
					'ag_domicile_hero_image'  => 'THEME:/assets/hero.jpg',
					'ag_domicile_testi_1'     => 'Une aide fiable pour le ménage et la garde des enfants après l’école. On respire enfin le soir.|Émilie & Thomas|Nantes',
					'ag_domicile_testi_2'     => 'Une seule agence pour ma mère âgée et le repassage à la maison : tellement plus simple.|Sabrina L.|Orvault',
					'ag_domicile_testi_3'     => 'Réactifs, humains, et le crédit d’impôt allège vraiment le budget. Merci.|Patrick D.|Vertou',
				),
				'services' => array(
					array( 'emoji' => '🧹', 'title' => 'Ménage & repassage' ),
					array( 'emoji' => '🍲', 'title' => 'Préparation des repas' ),
					array( 'emoji' => '👶', 'title' => 'Garde d’enfants' ),
					array( 'emoji' => '🎒', 'title' => 'Sortie d’école' ),
					array( 'emoji' => '🌅', 'title' => 'Aide aux seniors' ),
					array( 'emoji' => '♿', 'title' => 'Aide au handicap' ),
					array( 'emoji' => '🛒', 'title' => 'Courses & démarches' ),
					array( 'emoji' => '🌿', 'title' => 'Petits travaux & jardin' ),
				),
				'how' => array(
					array( 'emoji' => '☎️', 'title' => 'Premier échange gratuit', 'desc' => 'Vous nous exposez votre besoin (ponctuel ou régulier) : nous cadrons ensemble le service idéal, sans engagement.' ),
					array( 'emoji' => '🗓️', 'title' => 'Intervenant & planning', 'desc' => 'Nous vous proposons un intervenant qualifié et un planning adapté à votre organisation familiale.' ),
					array( 'emoji' => '💚', 'title' => 'Sérénité au quotidien', 'desc' => 'Suivi qualité, remplacements assurés, facturation simplifiée avec crédit d’impôt et avance immédiate.' ),
				),
				'faq' => array(
					array( 'q' => 'Le crédit d’impôt s’applique-t-il à tout ?', 'a' => 'Oui, l’ensemble de nos prestations à domicile (ménage, garde d’enfants, aide aux seniors…) ouvre droit à 50 % de crédit d’impôt, avec avance immédiate.' ),
					array( 'q' => 'Puis-je cumuler plusieurs services ?', 'a' => 'Bien sûr : un même intervenant ou une même agence pour le ménage, la garde d’enfants et l’aide à un parent âgé. Un seul interlocuteur, une seule facture.' ),
					array( 'q' => 'Intervention ponctuelle possible ?', 'a' => 'Oui : ponctuelle (grand ménage, dépannage garde) ou régulière (hebdomadaire). Vous ajustez à tout moment.' ),
					array( 'q' => 'Comment se passe la mise en place ?', 'a' => 'Un échange, un devis clair, puis la mise en place rapide d’un intervenant présenté à votre famille.' ),
				),
				'stats' => array(
					array( 'value' => '50 %',  'label' => 'Crédit d’impôt' ),
					array( 'value' => '7j/7',  'label' => 'Disponibilité' ),
					array( 'value' => '4,9/5', 'label' => 'Satisfaction familles' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Douceur de Vie accompagne les familles au quotidien : aide aux seniors, ménage et repassage, garde d’enfants, aide aux personnes en situation de handicap. Un seul interlocuteur de confiance pour tous les services à domicile.\n\nNos intervenants sont recrutés avec exigence, formés et suivis. Nous construisons un service sur mesure, ponctuel ou régulier, et assurons la continuité (référent + remplacements).\n\nDéclaré services à la personne, notre service ouvre droit au crédit d’impôt de 50 %, avec avance immédiate. Devis gratuit et sans engagement.",
					'prestations'        => "Entretien de la maison : ménage, repassage, vitres, rangement, entretien du linge.\n\nFamille & enfants : garde d’enfants à domicile, sortie d’école, aide aux devoirs, accompagnement aux activités.\n\nSeniors & handicap : aide au quotidien, présence, accompagnement aux sorties. Plus : courses, démarches, petits travaux et jardinage. Toutes nos prestations ouvrent droit au crédit d’impôt de 50 %.",
					'zones-intervention' => "Nous intervenons à Nantes et dans son agglomération : Rezé, Saint-Herblain, Orvault, Vertou, Bouguenais, Saint-Sébastien-sur-Loire, Carquefou et alentours.\n\nInterventions 7j/7 en journée comme en soirée, en ponctuel ou en régulier, avec des délais de mise en place courts.\n\nVotre commune n’apparaît pas ? Contactez-nous : nous étudions chaque demande et étendons régulièrement notre secteur.",
					'realisations'       => "Chaque semaine, nos intervenants allègent le quotidien de dizaines de familles : parents débordés, aidants d’un proche âgé, personnes en situation de handicap. Un même partenaire pour plusieurs besoins, c’est du temps et de la charge mentale en moins.\n\nCe qui fait la différence : la fiabilité, un intervenant référent, une écoute réelle et des remplacements toujours assurés.\n\nDécouvrez les témoignages de familles ci-dessous, et demandez un devis : nous construisons votre service en quelques jours.",
				),
			),

			'domicile_handicap' => array(
				'icon'  => '♿',
				'label' => 'Aide au handicap & dépendance',
				'desc'  => 'Accompagnement spécialisé des personnes en situation de handicap ou de dépendance, à domicile.',
				'mods'  => array(
					'ag_color_accent'         => '#4f9d6b',
					'ag_hero_prefix'          => 'Autonomie et dignité, avec',
					'ag_hero_brand'           => 'Douceur de Vie',
					'ag_hero_subtitle'        => 'Accompagnement des personnes en situation de handicap ou de dépendance : aide aux gestes essentiels, stimulation, présence. Intervenants formés — crédit d’impôt de 50 %.',
					'ag_hero_button'          => 'Devis gratuit',
					'ag_hero_button_url'      => '/devis/',
					'ag_domicile_metier_nom'  => 'service d’accompagnement à domicile',
					'ag_domicile_hero_image'  => 'THEME:/assets/hero.jpg',
					'ag_domicile_testi_1'     => 'Un accompagnement respectueux et compétent pour mon fils. Enfin des intervenants formés et stables.|Valérie P.|Nantes',
					'ag_domicile_testi_2'     => 'Présence de nuit fiable et rassurante. Nous avons retrouvé du répit en tant qu’aidants.|Famille Girard|Saint-Herblain',
					'ag_domicile_testi_3'     => 'Écoute, patience et professionnalisme. Le crédit d’impôt rend l’aide accessible.|André M.|Rezé',
				),
				'services' => array(
					array( 'emoji' => '🤝', 'title' => 'Aide aux gestes essentiels' ),
					array( 'emoji' => '🚿', 'title' => 'Aide à la toilette' ),
					array( 'emoji' => '🍽️', 'title' => 'Aide au repas' ),
					array( 'emoji' => '🧠', 'title' => 'Stimulation & activités' ),
					array( 'emoji' => '🦽', 'title' => 'Aide aux déplacements' ),
					array( 'emoji' => '🌙', 'title' => 'Présence de nuit' ),
					array( 'emoji' => '🗂️', 'title' => 'Aide aux démarches' ),
					array( 'emoji' => '🚗', 'title' => 'Accompagnement sorties' ),
				),
				'how' => array(
					array( 'emoji' => '☎️', 'title' => 'Évaluation personnalisée', 'desc' => 'Nous évaluons avec vous (et votre équipe médico-sociale si besoin) les besoins et le rythme d’accompagnement.' ),
					array( 'emoji' => '🧑‍⚕️', 'title' => 'Intervenant formé & référent', 'desc' => 'Un(e) accompagnant(e) formé(e) au handicap et à la dépendance, stable, présenté(e) à la famille.' ),
					array( 'emoji' => '💚', 'title' => 'Continuité & répit', 'desc' => 'Présence fiable, coordination, remplacements assurés et soutien aux aidants familiaux.' ),
				),
				'faq' => array(
					array( 'q' => 'Vos intervenants sont-ils formés au handicap ?', 'a' => 'Oui, nos accompagnants sont formés aux gestes, à la communication adaptée et à la sécurité, et bénéficient d’un suivi régulier.' ),
					array( 'q' => 'Prise en charge PCH / APA ?', 'a' => 'Nous vous orientons dans vos démarches (PCH, APA, MDPH) et nos prestations ouvrent aussi droit au crédit d’impôt de 50 %.' ),
					array( 'q' => 'Présence de nuit ou continue ?', 'a' => 'Oui : présence de nuit, garde renforcée ou présence continue selon les besoins, 7j/7.' ),
					array( 'q' => 'Comment commencer ?', 'a' => 'Contactez-nous pour une évaluation gratuite à domicile ; nous construisons ensemble un accompagnement adapté et respectueux.' ),
				),
				'stats' => array(
					array( 'value' => '50 %',  'label' => 'Crédit d’impôt' ),
					array( 'value' => '7j/7',  'label' => 'Présence possible' ),
					array( 'value' => '4,9/5', 'label' => 'Satisfaction familles' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Douceur de Vie accompagne à domicile les personnes en situation de handicap ou de dépendance, dans le respect de leur autonomie et de leur dignité. Notre priorité : une présence humaine, compétente et stable.\n\nNos accompagnants sont formés aux gestes essentiels, à la communication adaptée et à la sécurité, et suivis dans la durée. Nous travaillons en lien avec la famille et, si besoin, l’équipe médico-sociale.\n\nDéclaré services à la personne, notre service ouvre droit au crédit d’impôt de 50 %. Nous vous orientons aussi dans vos démarches (PCH, APA, MDPH). Évaluation à domicile gratuite.",
					'prestations'        => "Aide aux actes essentiels : aide à la toilette et à l’habillage, aide au repas, aide aux transferts et aux déplacements, aide à l’élimination.\n\nStimulation & lien : activités adaptées, stimulation cognitive, maintien du lien social, accompagnement aux sorties et aux rendez-vous.\n\nRépit & sécurité : présence de jour, présence de nuit, garde renforcée, soutien aux aidants. Nos prestations ouvrent droit au crédit d’impôt de 50 %.",
					'zones-intervention' => "Nous intervenons à Nantes et dans son agglomération : Rezé, Saint-Herblain, Orvault, Vertou, Bouguenais, Saint-Sébastien-sur-Loire, Carquefou et communes voisines.\n\nInterventions 7j/7, de jour comme de nuit, avec continuité assurée et remplacements garantis.\n\nVotre commune n’apparaît pas ? Contactez-nous : nous étudions chaque situation avec attention.",
					'realisations'       => "Nous accompagnons enfants et adultes en situation de handicap, ainsi que des personnes en perte d’autonomie. Maintien à domicile, répit pour les aidants, présence de nuit sécurisante : notre présence stable et formée fait la différence au quotidien.\n\nCe qui compte pour nous : le respect, la patience, la compétence et la continuité — un intervenant référent, jamais laissé sans solution.\n\nDécouvrez les témoignages de familles ci-dessous, et contactez-nous pour construire un accompagnement sur mesure.",
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
			__( 'Configuration métier', 'ag-starter-domicile' ),
			'🎯 ' . __( 'Configuration métier', 'ag-starter-domicile' ),
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
			<h1>🎯 <?php esc_html_e( 'Configuration métier — AG Starter Domicile', 'ag-starter-domicile' ); ?></h1>
			<p style="font-size:1.05em;color:#50575e;max-width:820px;"><?php esc_html_e( 'Choisissez votre spécialité de accompagnement ci-dessous. En 1 clic, le thème adapte le hero, la couleur d\'accent et la grille des 8 services. Affinez ensuite chaque texte dans Apparence > Personnaliser.', 'ag-starter-domicile' ); ?></p>

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
				💡 <?php esc_html_e( 'Après application, fine-tunez via', 'ag-starter-domicile' ); ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Apparence > Personnaliser', 'ag-starter-domicile' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-domicile-reset' ) ); ?>">🔄 <?php esc_html_e( 'Réinitialiser le thème', 'ag-starter-domicile' ); ?></a>
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
