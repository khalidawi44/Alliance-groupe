<?php
/**
 * Systeme de devis avec fourchette de prix par metier + localisation.
 *
 * Shortcode [ag_restaurant_devis] : affiche un formulaire adapte au metier
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

class AG_Restaurant_Devis {

	/**
	 * Grille de prix indicative en € par metier x service.
	 * 'unit' : 'm2' = prix au m² (multiplie par surface), 'forfait' = prix fixe.
	 * Ces prix sont basés sur les moyennes observées sur le marché français en 2025.
	 */
	const PRICES = array(
		'macon' => array(
			'construction-maison' => array( 'min' => 1300, 'max' => 1900, 'unit' => 'm2',     'label' => 'Construction maison neuve' ),
			'extension'           => array( 'min' => 1600, 'max' => 2600, 'unit' => 'm2',     'label' => 'Extension de maison' ),
			'renovation-murs'     => array( 'min' => 80,   'max' => 160,  'unit' => 'm2',     'label' => 'Rénovation de murs (au m²)' ),
			'dalles-beton'        => array( 'min' => 60,   'max' => 110,  'unit' => 'm2',     'label' => 'Dalle béton coulée' ),
			'facades'             => array( 'min' => 50,   'max' => 150,  'unit' => 'm2',     'label' => 'Ravalement de façade' ),
			'murets-clotures'     => array( 'min' => 90,   'max' => 280,  'unit' => 'm2',     'label' => 'Muret / clôture (au m linéaire)' ),
			'terrassement'        => array( 'min' => 25,   'max' => 60,   'unit' => 'm2',     'label' => 'Terrassement' ),
			'carrelage'           => array( 'min' => 40,   'max' => 120,  'unit' => 'm2',     'label' => 'Pose de carrelage' ),
		),
		'electricien' => array(
			'depannage-urgent'         => array( 'min' => 80,    'max' => 250,   'unit' => 'forfait', 'label' => 'Dépannage urgent (déplacement + 1h)' ),
			'tableau-electrique'       => array( 'min' => 1200,  'max' => 2500,  'unit' => 'forfait', 'label' => 'Refonte tableau électrique' ),
			'eclairage'                => array( 'min' => 60,    'max' => 180,   'unit' => 'forfait', 'label' => 'Installation luminaire (par point)' ),
			'mise-aux-normes'          => array( 'min' => 90,    'max' => 130,   'unit' => 'm2',     'label' => 'Mise aux normes (au m²)' ),
			'domotique'                => array( 'min' => 1500,  'max' => 6000,  'unit' => 'forfait', 'label' => 'Installation domotique complète' ),
			'bornes-de-recharge'       => array( 'min' => 800,   'max' => 2200,  'unit' => 'forfait', 'label' => 'Borne de recharge VE installée' ),
			'diagnostic-electrique'    => array( 'min' => 120,   'max' => 280,   'unit' => 'forfait', 'label' => 'Diagnostic électrique complet' ),
			'renovation-totale'        => array( 'min' => 110,   'max' => 180,   'unit' => 'm2',     'label' => 'Rénovation électrique totale (au m²)' ),
		),
		'traiteur' => array(
			'cocktails-aperitifs'    => array( 'min' => 18, 'max' => 32,  'unit' => 'personne', 'label' => 'Cocktail apéritif (par personne)' ),
			'buffets-froidschauds'   => array( 'min' => 28, 'max' => 48,  'unit' => 'personne', 'label' => 'Buffet froid/chaud (par personne)' ),
			'repas-assis-service'    => array( 'min' => 45, 'max' => 95,  'unit' => 'personne', 'label' => 'Repas assis 3 services + serveur' ),
			'mariages-cle-en-main'   => array( 'min' => 75, 'max' => 180, 'unit' => 'personne', 'label' => 'Mariage clé en main (cocktail + dîner + pièce montée)' ),
			'seminaires-entreprise'  => array( 'min' => 32, 'max' => 65,  'unit' => 'personne', 'label' => 'Séminaire (pauses + déjeuner)' ),
			'pieces-montees'         => array( 'min' => 7,  'max' => 14,  'unit' => 'personne', 'label' => 'Pièce montée traditionnelle (au choux)' ),
			'plateaux-repas-pro'     => array( 'min' => 16, 'max' => 30,  'unit' => 'personne', 'label' => 'Plateau repas pro (livraison bureau)' ),
			'menu-vegevegan'         => array( 'min' => 28, 'max' => 55,  'unit' => 'personne', 'label' => 'Menu végétarien / vegan dédié' ),
		),
		'multiservice' => array(
			'plomberie'      => array( 'min' => 60,  'max' => 150, 'unit' => 'forfait', 'label' => 'Intervention plomberie (1h)' ),
			'electricite'    => array( 'min' => 60,  'max' => 150, 'unit' => 'forfait', 'label' => 'Intervention électricité (1h)' ),
			'peinture'       => array( 'min' => 25,  'max' => 50,  'unit' => 'm2',     'label' => 'Peinture (au m² de mur)' ),
			'jardinage'      => array( 'min' => 30,  'max' => 60,  'unit' => 'forfait', 'label' => 'Jardinage / tonte (1h)' ),
			'bricolage'      => array( 'min' => 40,  'max' => 80,  'unit' => 'forfait', 'label' => 'Petits travaux / bricolage (1h)' ),
			'demenagement'   => array( 'min' => 400, 'max' => 1500,'unit' => 'forfait', 'label' => 'Déménagement (studio/T2)' ),
			'nettoyage'      => array( 'min' => 20,  'max' => 40,  'unit' => 'forfait', 'label' => 'Nettoyage (1h)' ),
			'reparations'    => array( 'min' => 60,  'max' => 180, 'unit' => 'forfait', 'label' => 'Réparation diverse (forfait)' ),
		),
		'generaliste' => array(
			'petits-travaux'      => array( 'min' => 50,  'max' => 150, 'unit' => 'forfait', 'label' => 'Petits travaux (1h)' ),
			'renovation'          => array( 'min' => 400, 'max' => 1200,'unit' => 'm2',     'label' => 'Rénovation au m²' ),
			'depannage'           => array( 'min' => 80,  'max' => 200, 'unit' => 'forfait', 'label' => 'Dépannage (intervention)' ),
			'mise-aux-normes'     => array( 'min' => 100, 'max' => 250, 'unit' => 'm2',     'label' => 'Mise aux normes' ),
			'travaux-interieur'   => array( 'min' => 300, 'max' => 900, 'unit' => 'm2',     'label' => 'Travaux intérieur' ),
			'travaux-exterieur'   => array( 'min' => 60,  'max' => 200, 'unit' => 'm2',     'label' => 'Travaux extérieur' ),
			'devis-sur-mesure'    => array( 'min' => 0,   'max' => 0,   'unit' => 'forfait', 'label' => 'Devis personnalisé (sur visite)' ),
			'diagnostic'          => array( 'min' => 100, 'max' => 280, 'unit' => 'forfait', 'label' => 'Diagnostic technique' ),
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
		add_shortcode( 'ag_restaurant_devis', array( __CLASS__, 'render_form' ) );
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'admin_post_nopriv_ag_restaurant_devis_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'admin_post_ag_restaurant_devis_submit', array( __CLASS__, 'handle_submit' ) );
	}

	/**
	 * CPT pour stocker les demandes (admin > Devis demandes).
	 */
	public static function register_cpt() {
		register_post_type( 'ag_devis_lead', array(
			'label'         => __( 'Demandes de devis', 'ag-starter-restaurant' ),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_icon'     => 'dashicons-clipboard',
			'menu_position' => 26,
			'supports'      => array( 'title', 'editor', 'custom-fields' ),
			'labels'        => array(
				'name'          => __( 'Demandes de devis', 'ag-starter-restaurant' ),
				'singular_name' => __( 'Demande de devis', 'ag-starter-restaurant' ),
				'menu_name'     => '💼 ' . __( 'Devis demandés', 'ag-starter-restaurant' ),
			),
		) );
	}

	/**
	 * Champs de formulaire specifiques par metier.
	 * Chaque champ : type (number/date/select/checkbox_group), name, label,
	 * required, placeholder, options (pour select/checkbox_group).
	 */
	public static function get_metier_fields( $metier_slug ) {
		$fields = array(
			'macon' => array(
				array( 'type' => 'number', 'name' => 'surface', 'label' => 'Surface en m²', 'placeholder' => 'ex : 80', 'required' => false, 'hint' => 'Approximative si vous ne savez pas' ),
				array( 'type' => 'date',   'name' => 'date_souhaite', 'label' => 'Date souhaitée de démarrage', 'required' => false ),
			),
			'electricien' => array(
				array( 'type' => 'select', 'name' => 'urgence', 'label' => "Niveau d'urgence", 'required' => true, 'options' => array(
					'standard' => 'Standard (sous 7 jours)',
					'rapide'   => 'Rapide (sous 48h)',
					'urgent'   => 'URGENT (sous 2h, astreinte)',
				) ),
				array( 'type' => 'number', 'name' => 'surface', 'label' => 'Surface concernée en m² (si rénovation)', 'placeholder' => 'ex : 75', 'required' => false ),
				array( 'type' => 'date',   'name' => 'date_souhaite', 'label' => 'Date souhaitée', 'required' => false ),
			),
			'traiteur' => array(
				array( 'type' => 'date',   'name' => 'date_evenement', 'label' => "Date de l'événement", 'required' => true ),
				array( 'type' => 'number', 'name' => 'nb_personnes',  'label' => 'Nombre de personnes', 'placeholder' => 'ex : 80', 'required' => true ),
				array( 'type' => 'select', 'name' => 'type_evenement', 'label' => "Type d'événement", 'required' => false, 'options' => array(
					'cocktail'    => 'Cocktail apéritif',
					'buffet'      => 'Buffet',
					'repas-assis' => 'Repas assis avec service',
					'mariage'     => 'Mariage',
					'seminaire'   => 'Séminaire entreprise',
					'anniversaire'=> 'Anniversaire / fête privée',
				) ),
				array( 'type' => 'checkbox_group', 'name' => 'regimes', 'label' => 'Régimes alimentaires à prévoir', 'options' => array(
					'vegetarien' => 'Végétarien',
					'vegan'      => 'Vegan',
					'sansgluten' => 'Sans gluten',
					'halal'      => 'Halal',
					'casher'     => 'Casher',
				) ),
			),
			'multiservice' => array(
				array( 'type' => 'number', 'name' => 'surface', 'label' => 'Surface en m² (si applicable)', 'placeholder' => 'ex : 40', 'required' => false ),
				array( 'type' => 'select', 'name' => 'urgence', 'label' => "Délai d'intervention", 'required' => false, 'options' => array(
					'standard' => 'Standard (sous 7 jours)',
					'rapide'   => 'Rapide (sous 48h)',
					'urgent'   => 'URGENT (sous 4h)',
				) ),
				array( 'type' => 'date', 'name' => 'date_souhaite', 'label' => 'Date souhaitée', 'required' => false ),
			),
			'generaliste' => array(
				array( 'type' => 'number', 'name' => 'surface', 'label' => 'Surface en m² (si applicable)', 'placeholder' => 'ex : 50', 'required' => false ),
				array( 'type' => 'date',   'name' => 'date_souhaite', 'label' => 'Date souhaitée', 'required' => false ),
			),
		);
		return isset( $fields[ $metier_slug ] ) ? $fields[ $metier_slug ] : $fields['generaliste'];
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
	 * Shortcode [ag_restaurant_devis] : affiche le formulaire ou le resultat.
	 */
	public static function render_form() {
		$metier_slug = get_theme_mod( 'ag_restaurant_metier_slug', '' );
		$services    = class_exists( 'AG_Restaurant_Presets' ) ? AG_Restaurant_Presets::get_active_services() : array();
		$metier_nom  = get_theme_mod( 'ag_restaurant_metier_nom', 'Restaurant' );

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
					<input type="hidden" name="action" value="ag_restaurant_devis_submit" />
					<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
					<?php wp_nonce_field( 'ag_restaurant_devis_submit' ); ?>

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
		check_admin_referer( 'ag_restaurant_devis_submit' );

		$service_slug = isset( $_POST['service'] ) ? sanitize_key( $_POST['service'] ) : '';
		$postal       = isset( $_POST['postal'] )  ? preg_replace( '/\D/', '', wp_unslash( $_POST['postal'] ) ) : '';
		$message      = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$name         = isset( $_POST['name'] )    ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email        = isset( $_POST['email'] )   ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone        = isset( $_POST['phone'] )   ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$redirect     = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/devis/' );

		$metier_slug = get_theme_mod( 'ag_restaurant_metier_slug', '' );

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
AG_Restaurant_Devis::init();
