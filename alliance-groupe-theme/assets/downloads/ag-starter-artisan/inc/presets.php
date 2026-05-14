<?php
/**
 * Presets metier — 5 configurations rapides pour adapter le theme a
 * differents corps de metier (electricien, macon, boulanger, etc.) en
 * 1 clic. Chaque preset configure le hero, la couleur d'accent et
 * la grille de services affichee sur la page d'accueil.
 *
 * Style visuel inspire de meilleur-artisan.com : palette orange/blanc,
 * grille services 4x2 avec emojis, hero centre, CTA contrastant.
 *
 * Apparait dans : "Apparence > 🎯 Configuration metier".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Artisan_Presets {

	/**
	 * Definition des presets. Chaque preset declare ses theme_mods et
	 * sa liste de services (8 max pour grille 4x2).
	 */
	public static function get_presets() {
		return array(

			'generaliste' => array(
				'icon'  => '🔧',
				'label' => 'Artisan généraliste',
				'desc'  => 'Tous travaux du bâtiment : rénovation, dépannage, petits travaux. Pour artisan multi-compétences.',
				'mods'  => array(
					'ag_color_accent'        => '#F37A1F',
					'ag_hero_prefix'         => 'Vos travaux avec',
					'ag_hero_brand'          => 'Votre Entreprise',
					'ag_hero_subtitle'       => 'Devis gratuit, intervention rapide, satisfaction garantie. Plus de 10 ans d\'expérience à votre service.',
					'ag_hero_button'         => 'Demander un devis',
					'ag_artisan_metier_nom'  => 'Artisan généraliste',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Travail soigné, équipe ponctuelle, devis respecté à l\'euro près. Je recommande.|Sophie M.|Lyon',
					'ag_artisan_testi_2'     => 'Intervenu en urgence un dimanche pour une fuite, et tout réparé en 2h. Top !|Karim B.|Marseille',
					'ag_artisan_testi_3'     => 'Rénovation complète de mon appart, chantier impeccable et finitions parfaites.|Laure D.|Paris',
				),
				'services' => array(
					array( 'emoji' => '🔧', 'title' => 'Petits travaux' ),
					array( 'emoji' => '🛠️', 'title' => 'Rénovation' ),
					array( 'emoji' => '⚡', 'title' => 'Dépannage' ),
					array( 'emoji' => '📐', 'title' => 'Mise aux normes' ),
					array( 'emoji' => '🏠', 'title' => 'Travaux intérieur' ),
					array( 'emoji' => '🚜', 'title' => 'Travaux extérieur' ),
					array( 'emoji' => '💼', 'title' => 'Devis sur mesure' ),
					array( 'emoji' => '🔍', 'title' => 'Diagnostic' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Décrivez vos travaux', 'desc' => 'Appelez-nous ou remplissez le formulaire pour expliquer votre besoin.' ),
					array( 'emoji' => '📋', 'title' => 'Devis gratuit sous 24h', 'desc' => 'Visite technique offerte si nécessaire, puis devis détaillé sans engagement.' ),
					array( 'emoji' => '✅', 'title' => 'Travaux réalisés', 'desc' => 'Intervention rapide, chantier propre, garantie sur les travaux réalisés.' ),
				),
				'faq' => array(
					array( 'q' => 'Combien coûte un devis ?', 'a' => 'Nos devis sont 100% gratuits et sans engagement, même après visite technique.' ),
					array( 'q' => 'Sous quel délai intervenez-vous ?', 'a' => 'En général sous 48h pour un devis, et 7 à 21 jours pour démarrer les travaux selon leur ampleur.' ),
					array( 'q' => 'Êtes-vous assurés ?', 'a' => 'Oui, garantie décennale + responsabilité civile professionnelle. Attestations sur demande.' ),
				),
			),

			'electricien' => array(
				'icon'  => '⚡',
				'label' => 'Électricien',
				'desc'  => 'Installation, dépannage, mise aux normes, domotique. Tableau électrique, éclairage, bornes de recharge.',
				'mods'  => array(
					'ag_color_accent'        => '#FFB400',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Électricien',
					'ag_hero_subtitle'       => 'Installation, dépannage, mise aux normes — intervention 7j/7, devis gratuit. Certification Qualifelec.',
					'ag_hero_button'         => 'Dépannage urgent',
					'ag_artisan_metier_nom'  => 'Électricien',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Tableau électrique refait en 1 journée, propre et conforme. Excellent.|Pierre L.|Toulouse',
					'ag_artisan_testi_2'     => 'Dépannage un dimanche soir : arrivé en 40 min, panne réglée. Sauveur !|Aïcha R.|Lille',
					'ag_artisan_testi_3'     => 'Installation domotique complète : éclairage, volets, alarme. Travail au top.|Julien G.|Nantes',
				),
				'services' => array(
					array( 'emoji' => '⚡', 'title' => 'Dépannage urgent' ),
					array( 'emoji' => '🔌', 'title' => 'Tableau électrique' ),
					array( 'emoji' => '💡', 'title' => 'Éclairage' ),
					array( 'emoji' => '📐', 'title' => 'Mise aux normes' ),
					array( 'emoji' => '🏠', 'title' => 'Domotique' ),
					array( 'emoji' => '🚗', 'title' => 'Bornes de recharge' ),
					array( 'emoji' => '🔍', 'title' => 'Diagnostic électrique' ),
					array( 'emoji' => '🛠️', 'title' => 'Rénovation totale' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Diagnostic au téléphone', 'desc' => 'Décrivez votre panne ou besoin, on évalue tout de suite avec vous.' ),
					array( 'emoji' => '⚡', 'title' => 'Intervention rapide', 'desc' => 'Dépannage sous 2h en urgence, RDV planifié pour les travaux programmés.' ),
					array( 'emoji' => '📜', 'title' => 'Attestation Consuel', 'desc' => 'Pour toute nouvelle installation : mise en service après contrôle Consuel.' ),
				),
				'faq' => array(
					array( 'q' => 'Êtes-vous certifié Qualifelec ?', 'a' => 'Oui, certification Qualifelec à jour. Garantie décennale + assurance pro.' ),
					array( 'q' => 'Acceptez-vous les urgences nuit/week-end ?', 'a' => 'Oui, ligne d\'astreinte 7j/7. Majoration tarif nuit/dimanche selon barème transparent.' ),
					array( 'q' => 'Puis-je bénéficier d\'aides MaPrimeRénov\' ?', 'a' => 'Pour rénovation électrique avec gain énergétique (chauffage, isolation), oui. On vous guide dans le montage du dossier.' ),
				),
			),

			'macon' => array(
				'icon'  => '🧱',
				'label' => 'Maçon',
				'desc'  => 'Construction, rénovation, extension, façades. Dalles béton, murets, terrassement, carrelage.',
				'mods'  => array(
					'ag_color_accent'        => '#A0522D',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Maçon',
					'ag_hero_subtitle'       => 'Construction, rénovation, extension. Devis sous 48h, travaux soignés, garantie décennale.',
					'ag_hero_button'         => 'Devis travaux',
					'ag_artisan_metier_nom'  => 'Maçon',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Extension de 35m² livrée dans les délais, finitions impeccables. Équipe pro.|Bruno T.|Bordeaux',
					'ag_artisan_testi_2'     => 'Ravalement de façade complet, échafaudage propre, voisinage respecté.|Catherine L.|Strasbourg',
					'ag_artisan_testi_3'     => 'Dalle béton 80m² coulée en 1 journée, parfaitement nivelée. Excellent rapport qualité-prix.|Marc V.|Rennes',
				),
				'services' => array(
					array( 'emoji' => '🏠', 'title' => 'Construction maison' ),
					array( 'emoji' => '🏗️', 'title' => 'Extension' ),
					array( 'emoji' => '🧱', 'title' => 'Rénovation murs' ),
					array( 'emoji' => '⬜', 'title' => 'Dalles béton' ),
					array( 'emoji' => '🏛️', 'title' => 'Façades' ),
					array( 'emoji' => '🪨', 'title' => 'Murets / clôtures' ),
					array( 'emoji' => '🚜', 'title' => 'Terrassement' ),
					array( 'emoji' => '◼️', 'title' => 'Carrelage' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Visite gratuite du chantier', 'desc' => 'Sur place, on évalue le projet et on échange sur vos contraintes.' ),
					array( 'emoji' => '📐', 'title' => 'Devis détaillé sous 48h', 'desc' => 'Métré précis, matériaux chiffrés, planning prévisionnel.' ),
					array( 'emoji' => '🏗️', 'title' => 'Chantier livré', 'desc' => 'Suivi quotidien, propreté du chantier, garantie décennale signée.' ),
				),
				'faq' => array(
					array( 'q' => 'Avez-vous la garantie décennale ?', 'a' => 'Oui, garantie décennale obligatoire + biennale + responsabilité civile. Attestations fournies au devis.' ),
					array( 'q' => 'Combien de temps pour une extension ?', 'a' => 'Comptez 6 à 12 semaines selon m² + complexité (permis de construire à anticiper si > 20m²).' ),
					array( 'q' => 'Faites-vous le permis de construire ?', 'a' => 'Oui, nous accompagnons le dépôt en mairie et travaillons avec un architecte si > 150m².' ),
				),
			),

			'boulanger' => array(
				'icon'  => '🥖',
				'label' => 'Boulanger / Pâtissier',
				'desc'  => 'Pains, viennoiseries, pâtisseries artisanales. Gâteaux sur commande, sandwich, buffet réception.',
				'mods'  => array(
					'ag_color_accent'        => '#C8923C',
					'ag_hero_prefix'         => 'Boulangerie',
					'ag_hero_brand'          => 'artisanale',
					'ag_hero_subtitle'       => 'Pains traditionnels, viennoiseries, pâtisseries — fait maison chaque jour avec des produits frais et locaux.',
					'ag_hero_button'         => 'Voir la carte',
					'ag_artisan_metier_nom'  => 'Boulanger / Pâtissier',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Le meilleur pain au levain du quartier, croûte parfaite. J\'y vais tous les matins.|Émilie F.|Lyon',
					'ag_artisan_testi_2'     => 'Gâteau d\'anniversaire sur commande : magnifique et délicieux. Tout le monde a adoré.|Thomas D.|Paris',
					'ag_artisan_testi_3'     => 'Buffet de 80 personnes livré à l\'heure, fraîcheur exceptionnelle, prestation au top.|Nora M.|Nice',
				),
				'services' => array(
					array( 'emoji' => '🥖', 'title' => 'Pains traditionnels' ),
					array( 'emoji' => '🥐', 'title' => 'Viennoiseries' ),
					array( 'emoji' => '🍰', 'title' => 'Pâtisseries fines' ),
					array( 'emoji' => '🎂', 'title' => 'Gâteaux sur commande' ),
					array( 'emoji' => '🥪', 'title' => 'Sandwich / snacking' ),
					array( 'emoji' => '🥧', 'title' => 'Tartes maison' ),
					array( 'emoji' => '🧁', 'title' => 'Macarons / petits fours' ),
					array( 'emoji' => '🍞', 'title' => 'Buffet réception' ),
				),
				'how' => array(
					array( 'emoji' => '🌾', 'title' => 'Farines locales', 'desc' => 'Nous travaillons avec des meuniers de la région. Traçabilité totale.' ),
					array( 'emoji' => '👨‍🍳', 'title' => 'Fait maison chaque jour', 'desc' => 'Pétrissage manuel, longue fermentation, cuisson au four à sole.' ),
					array( 'emoji' => '🛍️', 'title' => 'Disponible en boutique', 'desc' => 'Ouverture 6h–20h du mardi au dimanche. Commandes sur mesure 48h à l\'avance.' ),
				),
				'faq' => array(
					array( 'q' => 'Acceptez-vous les commandes sur mesure ?', 'a' => 'Oui — pièces montées, gâteaux personnalisés, buffets : passez commande 48h à l\'avance.' ),
					array( 'q' => 'Avez-vous du pain sans gluten ?', 'a' => 'Oui, une gamme sans gluten et une gamme bio. Demandez la liste complète en boutique.' ),
					array( 'q' => 'Livrez-vous pour les événements ?', 'a' => 'Oui, livraison locale pour mariages, baptêmes, événements pro. Devis sur demande.' ),
				),
			),

			'multiservice' => array(
				'icon'  => '🛠️',
				'label' => 'Multiservices',
				'desc'  => 'Plombier, électricien, peintre, jardinier — un seul interlocuteur pour tous types de travaux.',
				'mods'  => array(
					'ag_color_accent'        => '#2E86AB',
					'ag_hero_prefix'         => 'Tous travaux',
					'ag_hero_brand'          => 'multiservices',
					'ag_hero_subtitle'       => 'Plomberie, électricité, peinture, jardinage, bricolage. Un seul interlocuteur, intervention rapide.',
					'ag_hero_button'         => 'Demander un devis',
					'ag_artisan_metier_nom'  => 'Multiservices',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Plomberie, peinture, montage de meubles : tout fait en 1 jour. Pratique !|Anne C.|Montpellier',
					'ag_artisan_testi_2'     => 'Équipe sympa, devis transparent, travail soigné. Je rappelle pour la suite.|Mehdi A.|Lille',
					'ag_artisan_testi_3'     => 'Jardin réaménagé + clôture + abri : un seul artisan pour tout. Super expérience.|Valérie P.|Toulouse',
				),
				'services' => array(
					array( 'emoji' => '🚿', 'title' => 'Plomberie' ),
					array( 'emoji' => '⚡', 'title' => 'Électricité' ),
					array( 'emoji' => '🎨', 'title' => 'Peinture' ),
					array( 'emoji' => '🌿', 'title' => 'Jardinage' ),
					array( 'emoji' => '🔧', 'title' => 'Bricolage' ),
					array( 'emoji' => '📦', 'title' => 'Déménagement' ),
					array( 'emoji' => '🧹', 'title' => 'Nettoyage' ),
					array( 'emoji' => '🛠️', 'title' => 'Réparations' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Un seul interlocuteur', 'desc' => 'Décrivez l\'ensemble de vos besoins, on coordonne tout pour vous.' ),
					array( 'emoji' => '📋', 'title' => 'Devis groupé', 'desc' => 'Devis unique pour plusieurs prestations = économies + planning optimisé.' ),
					array( 'emoji' => '🛠️', 'title' => 'Intervention coordonnée', 'desc' => 'Notre équipe enchaîne les prestations, vous gagnez du temps.' ),
				),
				'faq' => array(
					array( 'q' => 'Travaillez-vous pour les pros aussi ?', 'a' => 'Oui — bureaux, commerces, copropriétés. Facture et devis professionnels sur demande.' ),
					array( 'q' => 'Quels types de travaux refusez-vous ?', 'a' => 'Très gros œuvre (charpente, terrassement lourd) — pour cela on vous oriente vers un confrère spécialisé.' ),
					array( 'q' => 'Acceptez-vous les chèques emploi-service (CESU) ?', 'a' => 'Oui pour les particuliers, sur les prestations éligibles (jardinage, petit bricolage, ménage).' ),
				),
			),

		);
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 22 );
		add_action( 'admin_post_ag_artisan_apply_preset', array( __CLASS__, 'handle_apply' ) );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Configuration métier', 'ag-starter-artisan' ),
			'🎯 ' . __( 'Configuration métier', 'ag-starter-artisan' ),
			'manage_options',
			'ag-artisan-presets',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$applied = isset( $_GET['applied'] ) ? sanitize_key( $_GET['applied'] ) : '';
		$current = get_theme_mod( 'ag_artisan_metier_slug', '' );
		$presets = self::get_presets();
		?>
		<div class="wrap">
			<h1>🎯 <?php esc_html_e( 'Configuration métier — AG Starter Artisan', 'ag-starter-artisan' ); ?></h1>
			<p style="font-size:1.05em;color:#50575e;max-width:820px;"><?php esc_html_e( 'Choisissez votre corps de métier ci-dessous. En 1 clic, le thème adapte le hero, la couleur d\'accent et la grille des 8 services affichée sur la page d\'accueil. Vous pourrez ensuite affiner chaque texte dans Apparence > Personnaliser.', 'ag-starter-artisan' ); ?></p>

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
						<input type="hidden" name="action" value="ag_artisan_apply_preset" />
						<input type="hidden" name="preset" value="<?php echo esc_attr( $slug ); ?>" />
						<?php wp_nonce_field( 'ag_artisan_apply_preset' ); ?>
						<button type="submit" class="button button-primary button-large" style="width:100%;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;border-color:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;font-weight:700;">
							<?php echo $is_current ? '🔄 Réappliquer' : '✨ Appliquer ce preset'; ?>
						</button>
					</form>
				</div>
				<?php endforeach; ?>
			</div>

			<p style="margin-top:32px;color:#777;font-size:.9em;">
				💡 <?php esc_html_e( 'Après application, fine-tunez via', 'ag-starter-artisan' ); ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Apparence > Personnaliser', 'ag-starter-artisan' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-artisan-reset' ) ); ?>">🔄 <?php esc_html_e( 'Réinitialiser le thème', 'ag-starter-artisan' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function handle_apply() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_artisan_apply_preset' );
		$slug    = isset( $_POST['preset'] ) ? sanitize_key( $_POST['preset'] ) : '';
		$presets = self::get_presets();
		if ( ! isset( $presets[ $slug ] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-presets' ) );
			exit;
		}
		$preset = $presets[ $slug ];

		// 1. Applique les theme_mods
		foreach ( $preset['mods'] as $key => $val ) {
			set_theme_mod( $key, $val );
		}

		// 2. Stocke le slug actif (pour affichage badge "actuellement applique")
		set_theme_mod( 'ag_artisan_metier_slug', $slug );

		// 3. Stocke la liste des services en JSON (relu par front-page.php)
		set_theme_mod( 'ag_artisan_services_json', wp_json_encode( $preset['services'] ) );

		// 4. Purge les caches (LiteSpeed front + transients WP) pour que le
		// nouveau hero / la nouvelle grille soit visible immediatement sur
		// la home, sans attendre l'expiration de cache.
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();
		delete_site_transient( 'update_themes' );

		wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-presets&applied=' . $slug ) );
		exit;
	}

	/**
	 * Helper appele depuis front-page.php pour recuperer la liste des
	 * services a afficher dans la grille (decodee depuis le JSON).
	 *
	 * @return array Tableau de services [['emoji' => '⚡', 'title' => '...'], ...]
	 *               ou tableau vide si aucun preset applique.
	 */
	public static function get_active_services() {
		$json = get_theme_mod( 'ag_artisan_services_json', '' );
		if ( ! $json ) return array();
		$arr = json_decode( $json, true );
		return is_array( $arr ) ? $arr : array();
	}

	/**
	 * Recupere le preset actuellement applique (data complete, pas que mods).
	 *
	 * @return array|null
	 */
	public static function get_active_preset() {
		$slug = get_theme_mod( 'ag_artisan_metier_slug', '' );
		if ( ! $slug ) return null;
		$presets = self::get_presets();
		return isset( $presets[ $slug ] ) ? $presets[ $slug ] : null;
	}

	/** @return array [['emoji', 'title', 'desc'], ...] */
	public static function get_active_how() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['how'] ) ) ? $p['how'] : array();
	}

	/** @return array [['q', 'a'], ...] */
	public static function get_active_faq() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['faq'] ) ) ? $p['faq'] : array();
	}

	/**
	 * Recupere un temoignage stocke en theme_mod (format "texte|nom|ville").
	 * @return array|null ['text', 'name', 'city']
	 */
	public static function get_testimonial( $i ) {
		$raw = get_theme_mod( 'ag_artisan_testi_' . (int) $i, '' );
		if ( ! $raw ) return null;
		$parts = array_map( 'trim', explode( '|', $raw ) );
		return array(
			'text' => isset( $parts[0] ) ? $parts[0] : '',
			'name' => isset( $parts[1] ) ? $parts[1] : '',
			'city' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
}
AG_Artisan_Presets::init();

/**
 * Quand un preset est applique, on ajoute la classe body 'ag-premium-mode'
 * pour permettre au CSS d'overrider le theme sombre par defaut et passer
 * en palette claire style meilleur-artisan.com.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( class_exists( 'AG_Artisan_Presets' ) && AG_Artisan_Presets::get_active_preset() ) {
		$classes[] = 'ag-premium-mode';
	}
	return $classes;
} );
