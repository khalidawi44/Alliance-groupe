<?php
/**
 * Systeme de devis avec fourchette de prix par metier + localisation.
 *
 * Shortcode [ag_domicile_devis] : affiche un formulaire adapte au metier
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

class ag_domicile_Devis {

	/**
	 * Grille de prix indicative en € par metier x service.
	 * 'unit' : 'm2' = prix au m² (multiplie par surface), 'forfait' = prix fixe.
	 * Prix indicatifs (avant crédit d'impot) bases sur les tarifs moyens des services a la personne en France.
	 */
	const PRICES = array(
		'domicile_seniors' => array(
			'aide-au-lever-coucher'      => array( 'min' => 22, 'max' => 30,  'unit' => 'heure',   'label' => 'Aide au lever / coucher (par heure)' ),
			'preparation-des-repas'      => array( 'min' => 22, 'max' => 28,  'unit' => 'heure',   'label' => 'Aide aux repas (par heure)' ),
			'aide-a-la-toilette'         => array( 'min' => 24, 'max' => 32,  'unit' => 'heure',   'label' => 'Aide à la toilette (par heure)' ),
			'entretien-du-logement'      => array( 'min' => 20, 'max' => 26,  'unit' => 'heure',   'label' => 'Entretien du logement (par heure)' ),
			'courses-accompagnement'     => array( 'min' => 22, 'max' => 28,  'unit' => 'heure',   'label' => 'Courses & accompagnement (par heure)' ),
			'compagnie-lien-social'      => array( 'min' => 20, 'max' => 26,  'unit' => 'heure',   'label' => 'Compagnie & présence (par heure)' ),
			'garde-de-nuit-presence'     => array( 'min' => 90, 'max' => 160, 'unit' => 'forfait', 'label' => 'Garde de nuit (forfait / nuit)' ),
			'accompagnement-rdv-sorties' => array( 'min' => 22, 'max' => 30,  'unit' => 'heure',   'label' => 'Accompagnement sorties (par heure)' ),
			'_default'                   => array( 'min' => 22, 'max' => 30,  'unit' => 'heure',   'label' => 'Intervention à domicile (par heure)' ),
		),
		'domicile_familles' => array(
			'menage-repassage'      => array( 'min' => 20, 'max' => 28, 'unit' => 'heure', 'label' => 'Ménage & repassage (par heure)' ),
			'preparation-des-repas' => array( 'min' => 20, 'max' => 28, 'unit' => 'heure', 'label' => 'Préparation des repas (par heure)' ),
			'garde-denfants'        => array( 'min' => 18, 'max' => 26, 'unit' => 'heure', 'label' => 'Garde d’enfants (par heure)' ),
			'sortie-decole'         => array( 'min' => 18, 'max' => 24, 'unit' => 'heure', 'label' => 'Sortie d’école (par heure)' ),
			'aide-aux-seniors'      => array( 'min' => 22, 'max' => 30, 'unit' => 'heure', 'label' => 'Aide aux seniors (par heure)' ),
			'aide-au-handicap'      => array( 'min' => 24, 'max' => 32, 'unit' => 'heure', 'label' => 'Aide au handicap (par heure)' ),
			'courses-demarches'     => array( 'min' => 20, 'max' => 28, 'unit' => 'heure', 'label' => 'Courses & démarches (par heure)' ),
			'petits-travaux-jardin' => array( 'min' => 25, 'max' => 40, 'unit' => 'heure', 'label' => 'Petits travaux & jardin (par heure)' ),
			'_default'              => array( 'min' => 20, 'max' => 30, 'unit' => 'heure', 'label' => 'Intervention à domicile (par heure)' ),
		),
		'domicile_handicap' => array(
			'aide-aux-gestes-essentiels' => array( 'min' => 24, 'max' => 34,  'unit' => 'heure',   'label' => 'Aide aux gestes essentiels (par heure)' ),
			'aide-a-la-toilette'         => array( 'min' => 24, 'max' => 34,  'unit' => 'heure',   'label' => 'Aide à la toilette (par heure)' ),
			'aide-au-repas'              => array( 'min' => 22, 'max' => 30,  'unit' => 'heure',   'label' => 'Aide au repas (par heure)' ),
			'stimulation-activites'      => array( 'min' => 24, 'max' => 34,  'unit' => 'heure',   'label' => 'Stimulation & activités (par heure)' ),
			'aide-aux-deplacements'      => array( 'min' => 24, 'max' => 32,  'unit' => 'heure',   'label' => 'Aide aux déplacements (par heure)' ),
			'presence-de-nuit'           => array( 'min' => 100,'max' => 170, 'unit' => 'forfait', 'label' => 'Présence de nuit (forfait / nuit)' ),
			'aide-aux-demarches'         => array( 'min' => 22, 'max' => 30,  'unit' => 'heure',   'label' => 'Aide aux démarches (par heure)' ),
			'accompagnement-sorties'     => array( 'min' => 24, 'max' => 32,  'unit' => 'heure',   'label' => 'Accompagnement sorties (par heure)' ),
			'_default'                   => array( 'min' => 24, 'max' => 34,  'unit' => 'heure',   'label' => 'Accompagnement à domicile (par heure)' ),
		),
	);

	/**
	 * Multiplicateurs regionaux bases sur les 2 premiers chiffres du code postal.
	 * Ajustement selon le departement (cout de la vie).
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
		add_shortcode( 'ag_domicile_devis', array( __CLASS__, 'render_form' ) );
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'admin_post_nopriv_ag_domicile_devis_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'admin_post_ag_domicile_devis_submit', array( __CLASS__, 'handle_submit' ) );
	}

	/**
	 * CPT pour stocker les demandes (admin > Devis demandes).
	 */
	public static function register_cpt() {
		register_post_type( 'ag_devis_lead', array(
			'label'         => __( 'Demandes de devis', 'ag-gwen-services' ),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_icon'     => 'dashicons-clipboard',
			'menu_position' => 26,
			'supports'      => array( 'title', 'editor', 'custom-fields' ),
			'labels'        => array(
				'name'          => __( 'Demandes de devis', 'ag-gwen-services' ),
				'singular_name' => __( 'Demande de devis', 'ag-gwen-services' ),
				'menu_name'     => '💼 ' . __( 'Devis demandés', 'ag-gwen-services' ),
			),
		) );
	}

	/**
	 * Champs de formulaire specifiques par metier.
	 * Chaque champ : type (number/date/select/checkbox_group), name, label,
	 * required, placeholder, options (pour select/checkbox_group).
	 */
	public static function get_metier_fields( $metier_slug ) {
		// Champs communs a tous les domicile + adaptations par specialite
		$common = array(
			array( 'type' => 'select', 'name' => 'format', 'label' => 'Moment d’intervention', 'required' => true, 'options' => array(
				'jour'     => 'En journée',
				'nuit'     => 'La nuit',
				'continue' => 'Présence continue',
				'flexible' => 'À définir ensemble',
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
			'domicile_seniors' => array_merge( array(
				array( 'type' => 'select', 'name' => 'beneficiaire', 'label' => 'Pour qui ?', 'required' => true, 'options' => array(
					'parent'    => 'Un parent âgé',
					'conjoint'  => 'Mon conjoint',
					'moi'       => 'Moi-même',
					'proche'    => 'Un autre proche',
				) ),
				array( 'type' => 'select', 'name' => 'autonomie', 'label' => 'Niveau d’autonomie', 'required' => false, 'options' => array(
					'autonome'  => 'Plutôt autonome',
					'aide'      => 'A besoin d’aide au quotidien',
					'dependant' => 'Dépendance importante',
				) ),
				array( 'type' => 'number', 'name' => 'heures_sem', 'label' => 'Heures par semaine estimées', 'placeholder' => 'ex : 10', 'required' => false ),
			), $common ),
			'domicile_familles' => array_merge( array(
				array( 'type' => 'select', 'name' => 'besoin', 'label' => 'Besoin principal', 'required' => true, 'options' => array(
					'menage'    => 'Ménage / repassage',
					'enfants'   => 'Garde d’enfants',
					'seniors'   => 'Aide à un senior',
					'handicap'  => 'Aide au handicap',
					'plusieurs' => 'Plusieurs besoins',
				) ),
				array( 'type' => 'number', 'name' => 'heures_sem', 'label' => 'Heures par semaine estimées', 'placeholder' => 'ex : 4', 'required' => false ),
			), $common ),
			'domicile_handicap' => array_merge( array(
				array( 'type' => 'select', 'name' => 'beneficiaire', 'label' => 'Pour qui ?', 'required' => true, 'options' => array(
					'enfant'    => 'Un enfant',
					'adulte'    => 'Un adulte',
					'moi'       => 'Moi-même',
				) ),
				array( 'type' => 'select', 'name' => 'prise_en_charge', 'label' => 'Prise en charge en cours ?', 'required' => false, 'options' => array(
					'pch'         => 'PCH',
					'apa'         => 'APA',
					'mdph'        => 'Dossier MDPH',
					'aucune'      => 'Aucune / à étudier',
				) ),
				array( 'type' => 'number', 'name' => 'heures_sem', 'label' => 'Heures par semaine estimées', 'placeholder' => 'ex : 12', 'required' => false ),
			), $common ),
		);
		return isset( $fields[ $metier_slug ] ) ? $fields[ $metier_slug ] : $fields['domicile_seniors'];
	}

	/**
	 * Calcule la fourchette estimative.
	 * @return array ['min' => int, 'max' => int, 'label' => string, 'unit' => string]
	 */
	public static function estimate( $metier_slug, $service_slug, $multiplier, $postal_code ) {
		if ( ! isset( self::PRICES[ $metier_slug ] ) ) return null;
		$table = self::PRICES[ $metier_slug ];
		if ( ! isset( $table[ $service_slug ] ) ) { $service_slug = '_default'; }
		if ( ! isset( $table[ $service_slug ] ) ) return null;
		$row = $table[ $service_slug ];
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
	 * Shortcode [ag_domicile_devis] : affiche le formulaire ou le resultat.
	 */
	public static function render_form() {
		$metier_slug = get_theme_mod( 'ag_domicile_metier_slug', '' );
		$services    = class_exists( 'ag_domicile_Presets' ) ? ag_domicile_services() : array();
		$metier_nom  = get_theme_mod( 'ag_domicile_metier_nom', 'domicile' );

		if ( ! $metier_slug || empty( $services ) ) {
			return '<p style="padding:20px;background:#fffbea;border-left:4px solid #FFB400;">Veuillez d\'abord appliquer un preset métier dans <em>Apparence &rsaquo; 🎯 Configuration métier</em> pour activer le devis personnalisé.</p>';
		}

		// Etat post-submit (passe par redirect avec query string).
		$has_result = isset( $_GET['ag_devis_result'] );
		$result_min = isset( $_GET['ag_devis_min'] ) ? (int) $_GET['ag_devis_min'] : 0;
		$result_max = isset( $_GET['ag_devis_max'] ) ? (int) $_GET['ag_devis_max'] : 0;
		$result_lab = isset( $_GET['ag_devis_label'] ) ? sanitize_text_field( wp_unslash( $_GET['ag_devis_label'] ) ) : '';

		ob_start();
		$ag_dv_tel = function_exists( 'ag_domicile_opt' ) ? ag_domicile_opt( 'ag_domicile_footer_phone', '06 26 14 28 45' ) : '06 26 14 28 45';
		?>
		<div class="ag-devis-layout">
		<div class="ag-devis-wrap">
			<span class="ag-devis-kicker">✦ Estimation en 1 minute</span>
			<h2 style="margin-top:0;">Devis <span style="color:var(--ag-color-accent,#F37A1F);"><?php echo esc_html( strtolower( $metier_nom ) ); ?></span> en ligne</h2>
			<p style="color:#555;margin-bottom:20px;">Remplissez ce formulaire — vous obtenez instantanément une fourchette indicative. Toutes nos prestations ouvrent droit au crédit d’impôt de 50 %.</p>

			<?php if ( $has_result && $result_min && $result_max ) : ?>
				<div class="ag-devis-result">
					<p class="ag-devis-result__label">Estimation indicative pour : <?php echo esc_html( $result_lab ); ?></p>
					<p class="ag-devis-result__price"><?php echo number_format( $result_min, 0, ',', ' ' ); ?> € — <?php echo number_format( $result_max, 0, ',', ' ' ); ?> €</p>
					<p class="ag-devis-result__credit" style="font-weight:700;color:#2f7d54;margin:6px 0 0;">Soit <?php echo number_format( (int) round( $result_min / 2 ), 0, ',', ' ' ); ?> € — <?php echo number_format( (int) round( $result_max / 2 ), 0, ',', ' ' ); ?> € après crédit d’impôt de 50 % (avance immédiate).</p>
					<p class="ag-devis-result__note">Fourchette estimative basée sur les tarifs moyens des services à la personne dans votre région. Un devis ferme et gratuit est établi après une évaluation des besoins à domicile, sans engagement.</p>
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
					<input type="hidden" name="action" value="ag_domicile_devis_submit" />
					<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
					<?php wp_nonce_field( 'ag_domicile_devis_submit' ); ?>

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
						Fourchette indicative — crédit d’impôt de 50 % déductible. Devis ferme gratuit après évaluation à domicile.
					</p>
				</form>
			<?php endif; ?>
		</div><!-- /.ag-devis-wrap -->

		<aside class="ag-devis-aside">
			<div class="ag-devis-stamp" aria-hidden="true">
				<div class="ag-hero-stamp__spin">
					<svg viewBox="0 0 200 200"><defs><path id="agdvstamp" d="M100,100 m-72,0 a72,72 0 1,1 144,0 a72,72 0 1,1 -144,0"/></defs>
						<circle cx="100" cy="100" r="90" fill="none" stroke="#c9a36b" stroke-width="1.4" opacity=".6"/>
						<text fill="#e6c78a" font-family="Nunito Sans, sans-serif" font-size="12" font-weight="800" letter-spacing="3.2"><textPath href="#agdvstamp" startOffset="0%">· AVANCE IMMÉDIATE · SANS ENGAGEMENT </textPath></text>
					</svg>
				</div>
				<div class="ag-hero-stamp__mid"><b>50%</b><span>Crédit<br>d’impôt</span></div>
			</div>
			<h3 class="ag-devis-aside__title">Ce que vous obtenez</h3>
			<ul class="ag-devis-benefits">
				<li>Une fourchette <strong>immédiate</strong>, sans engagement</li>
				<li><strong>Crédit d’impôt 50 %</strong> déduit tout de suite (avance immédiate)</li>
				<li>Une <strong>évaluation à domicile offerte</strong></li>
				<li>Un <strong>devis ferme &amp; gratuit</strong> après la visite</li>
				<li>La <strong>même intervenante</strong>, à Nantes &amp; alentours</li>
			</ul>
			<blockquote class="ag-devis-quote">« Gwen accompagne ma mère avec une douceur incroyable. On est enfin rassurés. »<cite>— Sylvie M. · Nantes</cite></blockquote>
			<?php if ( $ag_dv_tel ) : ?>
			<a class="ag-devis-callcard" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $ag_dv_tel ) ); ?>">
				<span class="ag-devis-callcard__ic">📞</span>
				<span class="ag-devis-callcard__tx"><small>Ou appelez directement Gwen</small><strong><?php echo esc_html( $ag_dv_tel ); ?></strong></span>
			</a>
			<?php endif; ?>
		</aside>
		</div><!-- /.ag-devis-layout -->
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle submit : calcule l'estimation, stocke la demande en CPT, redirige.
	 */
	public static function handle_submit() {
		check_admin_referer( 'ag_domicile_devis_submit' );

		$service_slug = isset( $_POST['service'] ) ? sanitize_key( $_POST['service'] ) : '';
		$postal       = isset( $_POST['postal'] )  ? preg_replace( '/\D/', '', wp_unslash( $_POST['postal'] ) ) : '';
		$message      = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$name         = isset( $_POST['name'] )    ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email        = isset( $_POST['email'] )   ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone        = isset( $_POST['phone'] )   ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$redirect     = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/devis/' );

		$metier_slug = get_theme_mod( 'ag_domicile_metier_slug', '' );

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
ag_domicile_Devis::init();
