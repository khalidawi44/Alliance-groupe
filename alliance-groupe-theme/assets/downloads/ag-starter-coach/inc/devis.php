<?php
/**
 * Systeme de devis avec fourchette de prix par metier + localisation.
 *
 * Shortcode [ag_coach_devis] : affiche un formulaire adapte au metier
 * actif (services en liste deroulante alimentee depuis le preset).
 *
 * Au submit : calcule une fourchette estimative basee sur :
 * 1. Une grille de prix moyens metier x service (en dur dans le code)
 * 2. Un multiplicateur regional base sur le code postal (Paris/IDF, Lyon,
 *    grandes villes, province) en lecture des 2 premiers chiffres.
 * 3. Surface en m² si pertinent.
 *
 * Les coordonnees du demandeur sont stockees comme un Custom Post Type
 * 'ag_devis_lead' pour suivi en wp-admin.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ag_coach_Devis {

	/**
	 * Grille de prix indicative en € par metier x service.
	 * 'unit' : 'm2' = prix au m² (multiplie par surface), 'forfait' = prix fixe.
	 * Ces prix sont basés sur les moyennes observées sur le marché français en 2025.
	 */
	const PRICES = array(
		'coach_general' => array(
			'seance-decouverte'  => array( 'min' => 0,    'max' => 0,    'unit' => 'forfait', 'label' => 'Séance découverte (offerte, 45 min)' ),
			'coaching-individuel'=> array( 'min' => 80,   'max' => 150,  'unit' => 'forfait', 'label' => 'Séance coaching individuelle (1h)' ),
			'coaching-en-visio'  => array( 'min' => 70,   'max' => 130,  'unit' => 'forfait', 'label' => 'Séance coaching en visio (1h)' ),
			'forfait-10-seances' => array( 'min' => 700,  'max' => 1300, 'unit' => 'forfait', 'label' => 'Forfait 10 séances' ),
			'programme-3-mois'   => array( 'min' => 1500, 'max' => 2800, 'unit' => 'forfait', 'label' => 'Programme 3 mois (12 séances)' ),
			'coaching-de-groupe' => array( 'min' => 30,   'max' => 80,   'unit' => 'personne','label' => 'Coaching de groupe (par personne)' ),
			'bilan-personnel'    => array( 'min' => 180,  'max' => 350,  'unit' => 'forfait', 'label' => 'Bilan personnel approfondi (2h)' ),
			'suivi-messagerie'   => array( 'min' => 60,   'max' => 120,  'unit' => 'forfait', 'label' => 'Forfait suivi messagerie (1 mois)' ),
		),
		'coach_sportif' => array(
			'bilan-seance-d-essai'  => array( 'min' => 0,   'max' => 0,    'unit' => 'forfait', 'label' => 'Bilan + séance essai (offert)' ),
			'perte-de-poids'        => array( 'min' => 50,  'max' => 90,   'unit' => 'forfait', 'label' => 'Séance perte de poids (1h)' ),
			'prise-de-masse'        => array( 'min' => 50,  'max' => 90,   'unit' => 'forfait', 'label' => 'Séance prise de masse (1h)' ),
			'prepa-coursetriathlon' => array( 'min' => 60,  'max' => 110,  'unit' => 'forfait', 'label' => 'Prépa course/triathlon (1h)' ),
			'remise-en-forme'       => array( 'min' => 50,  'max' => 85,   'unit' => 'forfait', 'label' => 'Remise en forme (1h)' ),
			'reprise-post-blessure' => array( 'min' => 60,  'max' => 100,  'unit' => 'forfait', 'label' => 'Reprise post-blessure (1h)' ),
			'coaching-a-domicile'   => array( 'min' => 60,  'max' => 110,  'unit' => 'forfait', 'label' => 'Coaching à domicile (1h)' ),
			'programme-visio'       => array( 'min' => 40,  'max' => 70,   'unit' => 'forfait', 'label' => 'Programme visio mensuel' ),
		),
		'coach_vie' => array(
			'seance-decouverte'         => array( 'min' => 0,    'max' => 0,    'unit' => 'forfait', 'label' => 'Séance découverte (offerte, 45 min)' ),
			'transition-pro'            => array( 'min' => 90,   'max' => 160,  'unit' => 'forfait', 'label' => 'Séance transition pro (1h)' ),
			'gestion-du-stress'         => array( 'min' => 80,   'max' => 140,  'unit' => 'forfait', 'label' => 'Séance gestion du stress (1h)' ),
			'confiance-en-soi'          => array( 'min' => 80,   'max' => 140,  'unit' => 'forfait', 'label' => 'Séance confiance en soi (1h)' ),
			'equilibre-vie-pro-perso'   => array( 'min' => 80,   'max' => 140,  'unit' => 'forfait', 'label' => 'Séance équilibre vie pro/perso (1h)' ),
			'definition-objectifs'      => array( 'min' => 90,   'max' => 160,  'unit' => 'forfait', 'label' => 'Définition d\'objectifs (1h30)' ),
			'relations-famille'         => array( 'min' => 80,   'max' => 140,  'unit' => 'forfait', 'label' => 'Séance relations & famille' ),
			'sens-valeurs'              => array( 'min' => 90,   'max' => 160,  'unit' => 'forfait', 'label' => 'Travail sens & valeurs (1h30)' ),
		),
		'coach_business' => array(
			'coaching-dirigeants'        => array( 'min' => 250,  'max' => 450,  'unit' => 'forfait', 'label' => 'Coaching dirigeant (1h30)' ),
			'coaching-d-equipe'          => array( 'min' => 1200, 'max' => 3500, 'unit' => 'forfait', 'label' => 'Atelier coaching d\'équipe (1/2 journée)' ),
			'accompagnement-entrepreneurs'=>array( 'min' => 150,  'max' => 280,  'unit' => 'forfait', 'label' => 'Accompagnement entrepreneur (1h)' ),
			'prise-de-parole-en-public'  => array( 'min' => 180,  'max' => 350,  'unit' => 'forfait', 'label' => 'Coaching prise de parole (1h30)' ),
			'performance-commerciale'    => array( 'min' => 150,  'max' => 280,  'unit' => 'forfait', 'label' => 'Coaching performance commerciale (1h)' ),
			'leadership-vision'          => array( 'min' => 200,  'max' => 380,  'unit' => 'forfait', 'label' => 'Coaching leadership & vision (1h30)' ),
			'gestion-du-temps'           => array( 'min' => 120,  'max' => 220,  'unit' => 'forfait', 'label' => 'Coaching gestion du temps (1h)' ),
			'negociation'                => array( 'min' => 180,  'max' => 320,  'unit' => 'forfait', 'label' => 'Coaching négociation (1h30)' ),
		),
		'coach_mental' => array(
			'preparation-mentale'  => array( 'min' => 80,  'max' => 150, 'unit' => 'forfait', 'label' => 'Séance préparation mentale (1h)' ),
			'meditation-guidee'    => array( 'min' => 40,  'max' => 80,  'unit' => 'forfait', 'label' => 'Séance méditation guidée (45 min)' ),
			'sophrologie'          => array( 'min' => 60,  'max' => 110, 'unit' => 'forfait', 'label' => 'Séance sophrologie (1h)' ),
			'gestion-du-stress'    => array( 'min' => 70,  'max' => 130, 'unit' => 'forfait', 'label' => 'Séance gestion du stress (1h)' ),
			'sommeil-recuperation' => array( 'min' => 80,  'max' => 140, 'unit' => 'forfait', 'label' => 'Séance sommeil / récupération (1h)' ),
			'prepa-examensconcours'=> array( 'min' => 70,  'max' => 130, 'unit' => 'forfait', 'label' => 'Prépa examens / concours (1h)' ),
			'sportifs-haut-niveau' => array( 'min' => 100, 'max' => 200, 'unit' => 'forfait', 'label' => 'Préparation sportif haut niveau (1h)' ),
			'dirigeants-sous-pression'=>array( 'min' => 150, 'max' => 300, 'unit' => 'forfait', 'label' => 'Coaching dirigeant sous pression (1h)' ),
		),
	);

	/**
	 * Multiplicateurs regionaux bases sur les 2 premiers chiffres du code postal.
	 * Reflete la realite des prix BTP en France 2025 (sources : ADIL, FFB).
	 */
	const REGION_MULT = array(
		// Paris + petite couronne : +30%
		'75' => 1.30, '92' => 1.28, '93' => 1.22, '94' => 1.22,
		// IDF (grande couronne) : +15%
		'77' => 1.15, '78' => 1.18, '91' => 1.15, '95' => 1.15,
		// Cote d'Azur + grandes villes : +10 a +15%
		'06' => 1.18, '13' => 1.10, '83' => 1.12, '69' => 1.12, '74' => 1.15, '73' => 1.13,
		// Grandes villes secondaires : +5%
		'33' => 1.08, '31' => 1.05, '34' => 1.05, '67' => 1.07, '59' => 1.05, '44' => 1.06,
		// La plupart des departements : baseline (1.0)
		// DOM-TOM : ajustement specifique
		'97' => 1.20, '98' => 1.25,
	);

	public static function init() {
		add_shortcode( 'ag_coach_devis', array( __CLASS__, 'render_form' ) );
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'admin_post_nopriv_ag_coach_devis_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'admin_post_ag_coach_devis_submit', array( __CLASS__, 'handle_submit' ) );
	}

	/**
	 * CPT pour stocker les demandes (admin > Devis demandes).
	 */
	public static function register_cpt() {
		register_post_type( 'ag_devis_lead', array(
			'label'         => __( 'Demandes de devis', 'ag-starter-coach' ),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_icon'     => 'dashicons-clipboard',
			'menu_position' => 26,
			'supports'      => array( 'title', 'editor', 'custom-fields' ),
			'labels'        => array(
				'name'          => __( 'Demandes de devis', 'ag-starter-coach' ),
				'singular_name' => __( 'Demande de devis', 'ag-starter-coach' ),
				'menu_name'     => '💼 ' . __( 'Devis demandés', 'ag-starter-coach' ),
			),
		) );
	}

	/**
	 * Champs de formulaire specifiques par metier.
	 * Chaque champ : type (number/date/select/checkbox_group), name, label,
	 * required, placeholder, options (pour select/checkbox_group).
	 */
	public static function get_metier_fields( $metier_slug ) {
		// Champs communs a tous les coach + adaptations par specialite
		$common = array(
			array( 'type' => 'select', 'name' => 'format', 'label' => 'Format souhaité', 'required' => true, 'options' => array(
				'presentiel' => 'Présentiel (cabinet/domicile)',
				'visio'      => 'Visio (Zoom/Meet)',
				'mixte'      => 'Mixte (alterner)',
			) ),
			array( 'type' => 'date',   'name' => 'date_souhaite', 'label' => 'Date souhaitée pour démarrer', 'required' => false ),
			array( 'type' => 'select', 'name' => 'frequence', 'label' => 'Fréquence souhaitée', 'required' => false, 'options' => array(
				'hebdo'    => '1 fois par semaine',
				'bimensuel'=> '1 fois tous les 15 jours',
				'mensuel'  => '1 fois par mois',
				'flexible' => 'À voir avec vous',
			) ),
		);
		$fields = array(
			'coach_general' => array_merge( array(
				array( 'type' => 'select', 'name' => 'objectif', 'label' => 'Objectif principal', 'required' => true, 'options' => array(
					'developpement_perso' => 'Développement personnel',
					'transition_vie'      => 'Transition de vie',
					'gestion_stress'      => 'Gestion du stress',
					'projet_pro'          => 'Projet professionnel',
					'confiance_soi'       => 'Confiance en soi',
					'autre'               => 'Autre (à préciser)',
				) ),
			), $common ),
			'coach_sportif' => array_merge( array(
				array( 'type' => 'select', 'name' => 'objectif', 'label' => 'Objectif physique', 'required' => true, 'options' => array(
					'perte_poids'     => 'Perte de poids',
					'prise_masse'     => 'Prise de masse / musculation',
					'cardio_endurance'=> 'Cardio / endurance',
					'prepa_course'    => 'Préparation course/triathlon',
					'remise_forme'    => 'Remise en forme générale',
					'post_blessure'   => 'Reprise post-blessure',
				) ),
				array( 'type' => 'number',  'name' => 'nb_seances_sem', 'label' => 'Nombre de séances/semaine souhaité', 'placeholder' => 'ex : 2', 'required' => false ),
				array( 'type' => 'checkbox_group', 'name' => 'contraintes', 'label' => 'Contraintes médicales / blessures actuelles', 'options' => array(
					'dos'      => 'Mal de dos',
					'genoux'   => 'Problèmes genoux',
					'epaule'   => 'Épaule',
					'cardiaque'=> 'Suivi cardiaque',
					'aucune'   => 'Aucune',
				) ),
			), $common ),
			'coach_vie' => array_merge( array(
				array( 'type' => 'select', 'name' => 'objectif', 'label' => 'Thématique principale', 'required' => true, 'options' => array(
					'transition_pro'   => 'Transition professionnelle',
					'stress_anxiete'   => 'Gestion stress / anxiété',
					'confiance_soi'    => 'Confiance en soi',
					'equilibre'        => 'Équilibre vie pro/perso',
					'relations'        => 'Relations / famille',
					'projet_vie'       => 'Projet de vie / sens',
					'autre'            => 'Autre',
				) ),
			), $common ),
			'coach_business' => array_merge( array(
				array( 'type' => 'select', 'name' => 'cible', 'label' => 'Qui est concerné ?', 'required' => true, 'options' => array(
					'dirigeant'    => 'Dirigeant / CEO',
					'entrepreneur' => 'Entrepreneur / fondateur',
					'manager'      => 'Manager / chef d\'équipe',
					'commercial'   => 'Équipe commerciale',
					'collectif'    => 'Coaching collectif (équipe)',
				) ),
				array( 'type' => 'select', 'name' => 'objectif', 'label' => 'Objectif principal', 'required' => true, 'options' => array(
					'leadership'      => 'Leadership / vision',
					'commercial'      => 'Performance commerciale',
					'communication'   => 'Prise de parole / communication',
					'gestion_temps'   => 'Gestion du temps / priorités',
					'gestion_equipe'  => 'Gestion d\'équipe',
					'strategie'       => 'Stratégie d\'entreprise',
					'negociation'     => 'Négociation',
				) ),
				array( 'type' => 'select', 'name' => 'opco', 'label' => 'Prise en charge OPCO/financement pro ?', 'required' => false, 'options' => array(
					'oui'       => 'Oui, à étudier',
					'non'       => 'Non, financement perso',
					'jenesaispas' => 'Je ne sais pas',
				) ),
			), $common ),
			'coach_mental' => array_merge( array(
				array( 'type' => 'select', 'name' => 'objectif', 'label' => 'Sujet principal', 'required' => true, 'options' => array(
					'stress_anxiete'  => 'Gestion du stress / anxiété',
					'sommeil'         => 'Sommeil / récupération',
					'examens'         => 'Préparation examens / concours',
					'competition'     => 'Préparation compétition sportive',
					'pression_pro'    => 'Pression professionnelle',
					'meditation'      => 'Apprentissage méditation',
				) ),
				array( 'type' => 'select', 'name' => 'pratique', 'label' => 'Pratique actuelle de méditation/sophro ?', 'required' => false, 'options' => array(
					'aucune'     => 'Aucune',
					'debutant'   => 'Débutant',
					'intermediaire' => 'Intermédiaire',
					'avance'     => 'Avancé',
				) ),
			), $common ),
		);
		return isset( $fields[ $metier_slug ] ) ? $fields[ $metier_slug ] : $fields['coach_general'];
	}

	/**
	 * Calcule la fourchette estimative.
	 * @return array ['min' => int, 'max' => int, 'label' => string, 'unit' => string]
	 */
	public static function estimate( $metier_slug, $service_slug, $multiplier, $postal_code ) {
		if ( ! isset( self::PRICES[ $metier_slug ][ $service_slug ] ) ) return null;
		$row = self::PRICES[ $metier_slug ][ $service_slug ];
		$min = (float) $row['min'];
		$max = (float) $row['max'];

		// Multiplicateur regional
		$dept = substr( preg_replace( '/\D/', '', (string) $postal_code ), 0, 2 );
		$mult = isset( self::REGION_MULT[ $dept ] ) ? self::REGION_MULT[ $dept ] : 1.0;
		$min *= $mult;
		$max *= $mult;

		// Si prix au m² ou par personne, multiplie par la quantite fournie
		if ( in_array( $row['unit'], array( 'm2', 'personne' ), true ) && (float) $multiplier > 0 ) {
			$min *= (float) $multiplier;
			$max *= (float) $multiplier;
		}

		return array(
			'min'   => (int) round( $min ),
			'max'   => (int) round( $max ),
			'label' => $row['label'],
			'unit'  => $row['unit'],
			'mult'  => $mult,
		);
	}

	/**
	 * Shortcode [ag_coach_devis] : affiche le formulaire ou le resultat.
	 */
	public static function render_form() {
		$metier_slug = get_theme_mod( 'ag_coach_metier_slug', '' );
		$services    = class_exists( 'ag_coach_Presets' ) ? ag_coach_Presets::get_active_services() : array();
		$metier_nom  = get_theme_mod( 'ag_coach_metier_nom', 'coach' );

		if ( ! $metier_slug || empty( $services ) ) {
			return '<p style="padding:20px;background:#fffbea;border-left:4px solid #FFB400;">Veuillez d\'abord appliquer un preset métier dans <em>Apparence &rsaquo; 🎯 Configuration métier</em> pour activer le devis personnalisé.</p>';
		}

		// Etat post-submit (passe par redirect avec query string).
		$has_result = isset( $_GET['ag_devis_result'] );
		$result_min = isset( $_GET['ag_devis_min'] ) ? (int) $_GET['ag_devis_min'] : 0;
		$result_max = isset( $_GET['ag_devis_max'] ) ? (int) $_GET['ag_devis_max'] : 0;
		$result_lab = isset( $_GET['ag_devis_label'] ) ? sanitize_text_field( wp_unslash( $_GET['ag_devis_label'] ) ) : '';

		ob_start();
		?>
		<div class="ag-devis-wrap">
			<h2 style="margin-top:0;">Devis <span style="color:var(--ag-color-accent,#F37A1F);"><?php echo esc_html( strtolower( $metier_nom ) ); ?></span> en ligne</h2>
			<p style="color:#555;margin-bottom:20px;">Remplissez ce formulaire — vous obtenez instantanément une fourchette de prix indicative basée sur les tarifs moyens dans votre région.</p>

			<?php if ( $has_result && $result_min && $result_max ) : ?>
				<div class="ag-devis-result">
					<p class="ag-devis-result__label">Estimation indicative pour : <?php echo esc_html( $result_lab ); ?></p>
					<p class="ag-devis-result__price"><?php echo number_format( $result_min, 0, ',', ' ' ); ?> € — <?php echo number_format( $result_max, 0, ',', ' ' ); ?> €</p>
					<p class="ag-devis-result__note">Fourchette estimative basée sur les tarifs moyens dans votre région. Un devis ferme sera établi après visite technique sur place.</p>
				</div>
				<p style="margin-top:24px;text-align:center;">
					<a href="<?php echo esc_url( remove_query_arg( array( 'ag_devis_result', 'ag_devis_min', 'ag_devis_max', 'ag_devis_label' ) ) ); ?>" class="ag-devis-submit" style="display:inline-block;text-decoration:none;">Faire une autre estimation</a>
				</p>
			<?php else : ?>
				<?php
				$metier_fields = self::get_metier_fields( $metier_slug );
				// Pre-selection du service via query string (?service=slug-svc)
				$preselected_service = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
				?>
				<form class="ag-devis-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ag_coach_devis_submit" />
					<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
					<?php wp_nonce_field( 'ag_coach_devis_submit' ); ?>

					<div>
						<label for="ag_devis_service">Quel service vous intéresse ? *</label>
						<select name="service" id="ag_devis_service" required>
							<option value="">— Choisir —</option>
							<?php foreach ( $services as $svc ) :
								$slug = sanitize_title( $svc['title'] ); ?>
								<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $preselected_service, $slug ); ?>><?php echo esc_html( $svc['emoji'] . ' ' . $svc['title'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php // Champs DYNAMIQUES selon le metier ?>
					<?php foreach ( $metier_fields as $field ) : ?>
						<div>
							<label for="ag_devis_<?php echo esc_attr( $field['name'] ); ?>">
								<?php echo esc_html( $field['label'] ); ?>
								<?php if ( ! empty( $field['required'] ) ) : ?> *<?php endif; ?>
								<?php if ( empty( $field['required'] ) ) : ?> <em style="color:#999;font-weight:normal;">(facultatif)</em><?php endif; ?>
							</label>
							<?php if ( 'number' === $field['type'] ) : ?>
								<input type="number" name="<?php echo esc_attr( $field['name'] ); ?>" id="ag_devis_<?php echo esc_attr( $field['name'] ); ?>" min="0" step="1" placeholder="<?php echo esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ); ?>"<?php if ( ! empty( $field['required'] ) ) : ?> required<?php endif; ?> />
							<?php elseif ( 'date' === $field['type'] ) : ?>
								<input type="date" name="<?php echo esc_attr( $field['name'] ); ?>" id="ag_devis_<?php echo esc_attr( $field['name'] ); ?>" min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>"<?php if ( ! empty( $field['required'] ) ) : ?> required<?php endif; ?> />
							<?php elseif ( 'select' === $field['type'] ) : ?>
								<select name="<?php echo esc_attr( $field['name'] ); ?>" id="ag_devis_<?php echo esc_attr( $field['name'] ); ?>"<?php if ( ! empty( $field['required'] ) ) : ?> required<?php endif; ?>>
									<option value="">— Choisir —</option>
									<?php foreach ( $field['options'] as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php elseif ( 'checkbox_group' === $field['type'] ) : ?>
								<div style="display:flex;flex-wrap:wrap;gap:14px;padding:8px 0;">
									<?php foreach ( $field['options'] as $key => $label ) : ?>
										<label style="display:inline-flex;align-items:center;gap:6px;font-weight:normal;cursor:pointer;background:#fff;border:1px solid #d0d0d0;padding:8px 14px;border-radius:50px;">
											<input type="checkbox" name="<?php echo esc_attr( $field['name'] ); ?>[]" value="<?php echo esc_attr( $key ); ?>" style="margin:0;" />
											<?php echo esc_html( $label ); ?>
										</label>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $field['hint'] ) ) : ?>
								<small style="display:block;color:#888;margin-top:4px;font-size:.85rem;"><?php echo esc_html( $field['hint'] ); ?></small>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>

					<div>
						<label for="ag_devis_postal">Code postal *</label>
						<input type="text" name="postal" id="ag_devis_postal" pattern="[0-9]{5}" placeholder="75001" required />
					</div>

					<div>
						<label for="ag_devis_message">Précisez votre besoin <em style="color:#999;font-weight:normal;">(facultatif)</em></label>
						<textarea name="message" id="ag_devis_message" placeholder="Détails, contraintes, autres précisions..."></textarea>
					</div>

					<div class="ag-devis-row-2">
						<div>
							<label for="ag_devis_name">Votre nom *</label>
							<input type="text" name="name" id="ag_devis_name" required />
						</div>
						<div>
							<label for="ag_devis_email">Votre email *</label>
							<input type="email" name="email" id="ag_devis_email" required />
						</div>
					</div>

					<div>
						<label for="ag_devis_phone">Téléphone <em style="color:#999;font-weight:normal;">(facultatif)</em></label>
						<input type="tel" name="phone" id="ag_devis_phone" />
					</div>

					<div style="text-align:center;margin-top:12px;">
						<button type="submit" class="ag-devis-submit">🚀 Obtenir mon estimation</button>
					</div>

					<p style="font-size:.82rem;color:#999;text-align:center;margin-top:14px;">
						Fourchette indicative basée sur les prix moyens du marché. Devis ferme après visite technique.
					</p>
				</form>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle submit : calcule l'estimation, stocke la demande en CPT, redirige.
	 */
	public static function handle_submit() {
		check_admin_referer( 'ag_coach_devis_submit' );

		$service_slug = isset( $_POST['service'] ) ? sanitize_key( $_POST['service'] ) : '';
		$postal       = isset( $_POST['postal'] )  ? preg_replace( '/\D/', '', wp_unslash( $_POST['postal'] ) ) : '';
		$message      = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$name         = isset( $_POST['name'] )    ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email        = isset( $_POST['email'] )   ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone        = isset( $_POST['phone'] )   ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$redirect     = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/devis/' );

		$metier_slug = get_theme_mod( 'ag_coach_metier_slug', '' );

		// Capture les champs dynamiques specifiques au metier
		$dynamic_fields = self::get_metier_fields( $metier_slug );
		$dynamic_data   = array();
		foreach ( $dynamic_fields as $f ) {
			$key = $f['name'];
			if ( 'checkbox_group' === $f['type'] ) {
				$dynamic_data[ $key ] = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] )
					? array_map( 'sanitize_key', wp_unslash( $_POST[ $key ] ) )
					: array();
			} elseif ( 'number' === $f['type'] ) {
				$dynamic_data[ $key ] = isset( $_POST[ $key ] ) ? (float) $_POST[ $key ] : 0;
			} elseif ( 'date' === $f['type'] ) {
				$dynamic_data[ $key ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			} elseif ( 'select' === $f['type'] ) {
				$dynamic_data[ $key ] = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : '';
			}
		}

		// Pour le calcul : on multiplie par surface (m²) ou nb_personnes (traiteur)
		// selon ce qui est present dans les donnees dynamiques.
		$multiplier = 0;
		if ( ! empty( $dynamic_data['surface'] ) ) {
			$multiplier = (float) $dynamic_data['surface'];
		} elseif ( ! empty( $dynamic_data['nb_personnes'] ) ) {
			$multiplier = (float) $dynamic_data['nb_personnes'];
		}

		$estimate = self::estimate( $metier_slug, $service_slug, $multiplier, $postal );

		if ( ! $estimate ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		// Construit un body lisible des champs dynamiques pour l'email + content CPT
		$dynamic_summary = '';
		foreach ( $dynamic_fields as $f ) {
			$key = $f['name'];
			$val = isset( $dynamic_data[ $key ] ) ? $dynamic_data[ $key ] : '';
			if ( '' === $val || array() === $val ) continue;
			if ( is_array( $val ) ) {
				$labels = array();
				foreach ( $val as $opt_key ) {
					if ( isset( $f['options'][ $opt_key ] ) ) $labels[] = $f['options'][ $opt_key ];
				}
				$val = implode( ', ', $labels );
			} elseif ( 'select' === $f['type'] && isset( $f['options'][ $val ] ) ) {
				$val = $f['options'][ $val ];
			}
			$dynamic_summary .= sprintf( "• %s : %s\n", $f['label'], $val );
		}

		// Stocke la demande en CPT pour suivi admin
		$lead_id = wp_insert_post( array(
			'post_type'    => 'ag_devis_lead',
			'post_status'  => 'publish',
			'post_title'   => sprintf( '%s — %s (%s)', $name ?: 'Anonyme', $estimate['label'], $postal ),
			'post_content' => $message . "\n\n" . $dynamic_summary,
			'meta_input'   => array(
				'_ag_devis_service'  => $service_slug,
				'_ag_devis_metier'   => $metier_slug,
				'_ag_devis_postal'   => $postal,
				'_ag_devis_name'     => $name,
				'_ag_devis_email'    => $email,
				'_ag_devis_phone'    => $phone,
				'_ag_devis_min'      => $estimate['min'],
				'_ag_devis_max'      => $estimate['max'],
				'_ag_devis_dynamic'  => wp_json_encode( $dynamic_data ),
			),
		) );

		// Email admin (notification de nouvelle demande)
		if ( $email && get_option( 'admin_email' ) ) {
			$body = sprintf(
				"Nouvelle demande de devis :\n\n• Prestation : %s\n%s• Code postal : %s\n• Estimation : %d € – %d €\n\n• Nom : %s\n• Email : %s\n• Téléphone : %s\n\n• Message : %s",
				$estimate['label'], $dynamic_summary, $postal, $estimate['min'], $estimate['max'], $name, $email, $phone, $message
			);
			wp_mail( get_option( 'admin_email' ), 'Nouvelle demande de devis — ' . $estimate['label'], $body );
		}

		// Redirect avec resultat en query string
		$url = add_query_arg( array(
			'ag_devis_result' => 1,
			'ag_devis_min'    => $estimate['min'],
			'ag_devis_max'    => $estimate['max'],
			'ag_devis_label'  => rawurlencode( $estimate['label'] ),
		), $redirect );
		wp_safe_redirect( $url );
		exit;
	}
}
ag_coach_Devis::init();
