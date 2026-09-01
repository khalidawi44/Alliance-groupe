<?php
/**
 * AG SEO Local Boost — profondeur de contenu locale + pages métier-local.
 *
 * Objectif : monter en 1re page Google sans attendre l'ancienneté du domaine,
 * en gagnant d'abord la LONGUE TRAÎNE locale (faible concurrence, forte
 * intention d'achat) puis en renforçant les pages piliers existantes.
 *
 *  A. Approfondit (100 % ADDITIF, non destructif — filtre the_content, comme
 *     ag-geo.php) les piliers déjà créés par ag-seo-pages.php :
 *        /creation-site-internet-nantes
 *        /securite-informatique-pme-nantes
 *        /creation-site-internet-saint-nazaire
 *     → sections supplémentaires, process, secteurs, FAQ, maillage interne.
 *  B. Crée (idempotent, versionné — jamais réécrit ensuite) les pages
 *     métier-local manquantes, chacune avec un contenu RÉELLEMENT distinct
 *     (pas de doorway) :
 *        /creation-site-internet-restaurant-nantes
 *        /creation-site-internet-artisan-nantes
 *        /creation-site-internet-avocat-nantes
 *        /creation-site-e-commerce-nantes
 *  C. Schema par page (ProfessionalService géolocalisé + Service + FAQPage
 *     — FAQ/Breadcrumb seulement si aucun plugin SEO, pour ne pas dupliquer).
 *  D. CSS de mise en beauté scoppé (marque sombre/or), maillage interne.
 *
 * Rien n'est écrit en base pour les piliers (réversible : retirer ce fichier).
 * Les pages métier sont créées une fois et restent éditables à la main.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_LOCALBOOST_VER', 1 );

/* ═══════════════════════════════════════════════════════════════════════════
 *  HELPERS DE RENDU
 * ═══════════════════════════════════════════════════════════════════════════ */

if ( ! function_exists( 'ag_lb_faq_html' ) ) {
	/** Rend une FAQ (tableau [q, r]) en H3/P (matière SEO + lisible). */
	function ag_lb_faq_html( $faq ) {
		if ( empty( $faq ) ) return '';
		$h = "<h2>Questions fréquentes</h2>\n";
		foreach ( $faq as $qa ) {
			$h .= '<h3>' . esc_html( $qa[0] ) . "</h3>\n<p>" . wp_kses_post( $qa[1] ) . "</p>\n";
		}
		return $h;
	}
}

if ( ! function_exists( 'ag_lb_cta_html' ) ) {
	/** Bandeau CTA cohérent (devis + audit gratuit). */
	function ag_lb_cta_html( $intro = '' ) {
		$ctc   = esc_url( home_url( '/contact' ) );
		$audit = esc_url( home_url( '/tester-mon-site' ) );
		$intro = $intro ?: 'Discutons de votre projet — diagnostic gratuit, sans engagement, réponse sous 24 h.';
		return '<div class="ag-lb-cta">'
			. '<p class="ag-lb-cta__txt">' . esc_html( $intro ) . '</p>'
			. '<div class="ag-lb-cta__btns">'
			. '<a class="ag-lb-btn-gold" href="tel:+33744829516">07 44 82 95 16 — Appel gratuit</a>'
			. '<a class="ag-lb-btn-ghost" href="' . $ctc . '">Demander un devis &rarr;</a>'
			. '<a class="ag-lb-btn-ghost" href="' . $audit . '">🔍 Tester mon site (gratuit)</a>'
			. '</div></div>';
	}
}

if ( ! function_exists( 'ag_lb_hero_map' ) ) {
	/** slug => [chemin image relatif à assets/images/, texte alt]. Photos étalonnées AG. */
	function ag_lb_hero_map() {
		return array(
			'creation-site-internet-nantes'            => array( 'team/reunion-naples.jpg', 'Alliance Groupe — l\'équipe au travail, bureau de Naples' ),
			'securite-informatique-pme-nantes'         => array( 'securite/nantes-cyber.jpg', 'Sécurité informatique des PME à Nantes' ),
			'creation-site-internet-saint-nazaire'     => array( 'local/saint-nazaire.jpg', 'Saint-Nazaire — port et chantiers navals' ),
			'creation-site-internet-restaurant-nantes' => array( 'local/restaurant.jpg', 'Restaurant élégant — création de site à Nantes' ),
			'creation-site-internet-artisan-nantes'    => array( 'articles/artisan.jpg', 'Artisan au travail — création de site à Nantes' ),
			'creation-site-internet-avocat-nantes'     => array( 'templates/avocat/expertise-jour.jpg', 'Cabinet d\'avocat — création de site à Nantes' ),
			'creation-site-e-commerce-nantes'          => array( 'local/ecommerce.jpg', 'Boutique e-commerce — création à Nantes' ),
			'meilleure-agence-web-nantes'              => array( 'egerie/egerie-baie.jpg', 'Alliance Groupe — De Naples à Nantes' ),
		);
	}
}

if ( ! function_exists( 'ag_lb_hero_html' ) ) {
	/** Bandeau photo étalonné AG en tête de page (si un visuel est mappé). */
	function ag_lb_hero_html( $slug ) {
		$map = ag_lb_hero_map();
		if ( empty( $map[ $slug ] ) ) return '';
		$rel = $map[ $slug ][0];
		$alt = $map[ $slug ][1];
		$url = get_stylesheet_directory_uri() . '/assets/images/' . $rel;
		return '<figure class="ag-lb-hero"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="eager" fetchpriority="high"><span class="ag-lb-hero__veil" aria-hidden="true"></span></figure>';
	}
}

if ( ! function_exists( 'ag_lb_maillage_html' ) ) {
	/** Bloc de liens internes (jus SEO) — exclut la page courante. */
	function ag_lb_maillage_html( $current_slug = '' ) {
		$links = array(
			'creation-site-internet-nantes'             => 'Création de site internet à Nantes',
			'creation-site-internet-restaurant-nantes'  => 'Site internet pour restaurant',
			'creation-site-internet-artisan-nantes'     => 'Site internet pour artisan &amp; BTP',
			'creation-site-internet-avocat-nantes'      => 'Site internet pour avocat &amp; profession libérale',
			'creation-site-e-commerce-nantes'           => 'Création de boutique e-commerce',
			'securite-informatique-pme-nantes'          => 'Sécurité informatique PME (NIS2)',
			'creation-site-internet-saint-nazaire'      => 'Création de site à Saint-Nazaire',
			'meilleure-agence-web-nantes'               => 'Comparatif : bien choisir son agence',
		);
		$out = '';
		foreach ( $links as $slug => $label ) {
			if ( $slug === $current_slug ) continue;
			$out .= '<li><a href="' . esc_url( home_url( '/' . $slug ) ) . '">' . $label . '</a></li>';
		}
		if ( '' === $out ) return '';
		return '<nav class="ag-lb-maillage" aria-label="Nos pages liées"><h2>Nos expertises près de chez vous</h2><ul>' . $out . '</ul></nav>';
	}
}

/* ═══════════════════════════════════════════════════════════════════════════
 *  A. APPROFONDISSEMENT DES PILIERS EXISTANTS (additif, non destructif)
 * ═══════════════════════════════════════════════════════════════════════════ */

if ( ! function_exists( 'ag_lb_deepen_map' ) ) {
	function ag_lb_deepen_map() {
		$expr = esc_url( home_url( '/sites-express' ) );
		$maps = array();

		/* ---- /creation-site-internet-nantes ---- */
		$h  = "<h2>Comment se passe la création de votre site, étape par étape</h2>\n";
		$h .= "<p>Pas de jargon, pas de mauvaise surprise. Notre méthode tient en quatre étapes claires, avec un interlocuteur unique du début à la fin.</p>\n";
		$h .= "<ol>\n";
		$h .= "<li><strong>Cadrage (30 min, gratuit)</strong> — on écoute votre activité, vos objectifs et vos concurrents à Nantes. On repart avec une maquette d'arborescence.</li>\n";
		$h .= "<li><strong>Design &amp; contenu</strong> — maquette à votre image (couleurs, logo, photos), textes optimisés pour Google et pour vos clients.</li>\n";
		$h .= "<li><strong>Développement sécurisé</strong> — site rapide, mobile, HTTPS, formulaires protégés, RGPD. La sécurité n'est pas une option chez nous.</li>\n";
		$h .= "<li><strong>Mise en ligne &amp; suivi</strong> — référencement local, fiche Google, puis maintenance (mises à jour, sauvegardes, sécurité).</li>\n";
		$h .= "</ol>\n";
		$h .= "<h2>Site vitrine, e-commerce ou sur-mesure : lequel pour vous ?</h2>\n";
		$h .= "<ul>\n";
		$h .= "<li><strong>Site vitrine</strong> — vous présentez votre activité et captez des appels/demandes de devis. Idéal artisans, commerçants, professions libérales. À partir de 490 €.</li>\n";
		$h .= "<li><strong>Boutique e-commerce</strong> — vous vendez en ligne 24 h/24, paiement sécurisé, gestion des stocks. <a href=\"" . esc_url( home_url( '/creation-site-e-commerce-nantes' ) ) . "\">En savoir plus &rarr;</a></li>\n";
		$h .= "<li><strong>Sur-mesure</strong> — espace client, réservation, application métier : on développe la fonctionnalité dont vous avez besoin.</li>\n";
		$h .= "</ul>\n";
		$h .= "<h2>Pour qui travaillons-nous à Nantes ?</h2>\n";
		$h .= "<p>Restaurants, artisans du bâtiment, avocats et professions libérales, commerçants, coachs, PME industrielles du bassin nantais. Chaque secteur a une page dédiée avec ses besoins : "
			. "<a href=\"" . esc_url( home_url( '/creation-site-internet-restaurant-nantes' ) ) . "\">restaurant</a>, "
			. "<a href=\"" . esc_url( home_url( '/creation-site-internet-artisan-nantes' ) ) . "\">artisan &amp; BTP</a>, "
			. "<a href=\"" . esc_url( home_url( '/creation-site-internet-avocat-nantes' ) ) . "\">avocat &amp; profession libérale</a>.</p>\n";
		$h .= "<h2>Pourquoi un site sécurisé change tout pour votre référencement</h2>\n";
		$h .= "<p>Google déclasse les sites lents, non-HTTPS ou compromis. Un site piraté peut disparaître des résultats du jour au lendemain. En réunissant création web <em>et</em> cybersécurité, on protège à la fois vos clients et votre position sur Google — c'est ce qui nous distingue des agences classiques. Voir aussi notre page <a href=\"" . esc_url( home_url( '/securite-informatique-pme-nantes' ) ) . "\">sécurité informatique PME</a>.</p>\n";
		$h .= ag_lb_faq_html( array(
			array( 'Combien de temps pour créer un site internet à Nantes ?', "Un site vitrine Express est livré en quelques jours ouvrés une fois vos contenus reçus. Un site sur-mesure prend en général de 3 à 6 semaines selon les fonctionnalités." ),
			array( 'Suis-je propriétaire de mon site ?', "Oui, à 100 %. Le site, le nom de domaine et les accès vous appartiennent. Aucune dépendance : vous pouvez partir quand vous voulez, contrairement à beaucoup d'agences qui gardent la main." ),
			array( "L'hébergement et la maintenance sont-ils inclus ?", "L'hébergement est mis en place lors de la création. La maintenance (mises à jour, sécurité, sauvegardes) est proposée de 29 à 99 €/mois — recommandée mais jamais obligatoire." ),
			array( 'Mon site sera-t-il visible sur Google à Nantes ?', "Oui : chaque site est livré avec les bases du référencement local (structure, vitesse, contenu géolocalisé) et l'optimisation de votre fiche Google Business Profile pour le secteur nantais." ),
			array( 'Faites-vous des sites pour les entreprises hors de Nantes ?', "Oui, partout en Loire-Atlantique (Saint-Herblain, Rezé, Vertou, Carquefou, Orvault, Saint-Nazaire) et en France à distance." ),
		) );
		$h .= ag_lb_cta_html();
		$maps['creation-site-internet-nantes'] = $h;

		/* ---- /securite-informatique-pme-nantes ---- */
		$h  = "<h2>5 signes que votre site (ou votre PME) est vulnérable</h2>\n";
		$h .= "<ul>\n";
		$h .= "<li>Votre site tourne sur un WordPress dont les extensions ne sont plus à jour depuis des mois.</li>\n";
		$h .= "<li>Vous n'avez pas de sauvegarde automatique testée (ou vous ne savez pas où elles sont).</li>\n";
		$h .= "<li>Les mots de passe sont partagés, faibles, ou identiques partout.</li>\n";
		$h .= "<li>Aucun pare-feu applicatif ni double authentification sur l'admin.</li>\n";
		$h .= "<li>Vous ne sauriez pas dire, aujourd'hui, si votre site a déjà été compromis.</li>\n";
		$h .= "</ul>\n";
		$h .= "<p>Si deux de ces points vous parlent, un <a href=\"" . esc_url( home_url( '/tester-mon-site' ) ) . "\">diagnostic gratuit</a> vous donnera une photo claire en 1 minute.</p>\n";
		$h .= "<h2>Ce que couvre concrètement un audit de sécurité</h2>\n";
		$h .= "<ul>\n";
		$h .= "<li>Surface d'exposition : ce qu'un attaquant voit de l'extérieur (ports, versions, fuites).</li>\n";
		$h .= "<li>Site web : injections, formulaires, droits d'accès, en-têtes de sécurité, HTTPS.</li>\n";
		$h .= "<li>Données : conformité RGPD, chiffrement, sauvegardes, plan de restauration.</li>\n";
		$h .= "<li>Organisation : mots de passe, MFA, gestion des accès, sensibilisation.</li>\n";
		$h .= "</ul>\n";
		$h .= "<h2>NIS2 : le calendrier et ce qu'il faut faire</h2>\n";
		$h .= "<p>La directive <strong>NIS2</strong> est transposée en droit français et concerne bien plus d'entreprises que l'ancienne NIS : PME de secteurs sensibles, mais aussi de nombreux <strong>sous-traitants</strong> de grands donneurs d'ordre. Concrètement, on vous demande de prouver un socle de cybersécurité (gouvernance, gestion des risques, réponse aux incidents). Notre accompagnement : diagnostic d'exposition, plan de mise en conformité priorisé, sécurisation opérationnelle. Mieux vaut anticiper que subir un contrôle ou un refus d'assurance cyber.</p>\n";
		$h .= ag_lb_faq_html( array(
			array( 'Ma PME est-elle concernée par NIS2 ?', "Beaucoup de PME et de sous-traitants le sont sans le savoir. Le plus simple est de faire vérifier votre situation : le diagnostic initial est gratuit et vous dit où vous en êtes." ),
			array( 'Un test d\'intrusion est-il légal ?', "Oui, uniquement sur mandat écrit du propriétaire du système. Sans mandat, nous ne réalisons qu'une analyse publique non-intrusive de ce qui est déjà visible de l'extérieur." ),
			array( 'Combien coûte un audit de sécurité ?', "Le test public est gratuit. Un audit approfondi et un accompagnement NIS2 font l'objet d'un devis selon la taille de votre système d'information. Contactez-nous pour une estimation." ),
			array( 'Sécurisez-vous aussi les sites que vous n\'avez pas créés ?', "Oui. Nous auditons et sécurisons des sites existants (WordPress ou autres), même réalisés par une autre agence." ),
		) );
		$h .= ag_lb_cta_html( 'Commencez par le diagnostic gratuit — vous saurez exactement où vous en êtes.' );
		$maps['securite-informatique-pme-nantes'] = $h;

		/* ---- /creation-site-internet-saint-nazaire ---- */
		$h  = "<h2>Des sites pensés pour l'économie nazairienne</h2>\n";
		$h .= "<p>Saint-Nazaire, c'est un tissu unique : chantiers navals et aéronautiques et leurs sous-traitants, artisans du bâtiment, commerces de centre-ville, restauration et tourisme de la Côte d'Amour (Pornichet, La Baule). Chaque profil a besoin d'un site différent — un sous-traitant industriel cherche la crédibilité et les appels d'offres, un restaurant de bord de mer cherche la réservation et les photos.</p>\n";
		$h .= "<h2>Notre méthode, la même qu'à Nantes</h2>\n";
		$h .= "<p>Cadrage gratuit, design à votre image, développement sécurisé, mise en ligne et référencement local. Un interlocuteur unique, joignable, qui connaît votre secteur. Découvrez le détail sur notre page <a href=\"" . esc_url( home_url( '/creation-site-internet-nantes' ) ) . "\">création de site à Nantes</a>.</p>\n";
		$h .= ag_lb_faq_html( array(
			array( 'Intervenez-vous sur toute la presqu\'île ?', "Oui : Saint-Nazaire, Pornichet, La Baule, Trignac, Montoir-de-Bretagne, Saint-André-des-Eaux et alentours, ainsi qu'à distance." ),
			array( 'Gérez-vous les appels d\'offres et documents pro ?', "Nous concevons des sites qui mettent en avant vos certifications, références et zones d'intervention — utile pour les sous-traitants industriels et le BTP." ),
			array( 'Quel budget pour un site à Saint-Nazaire ?', "Les mêmes offres claires qu'ailleurs : 490 € (Express), 890 € (Pro), 1 490 € (sur-mesure), maintenance de 29 à 99 €/mois." ),
		) );
		$h .= ag_lb_cta_html();
		$maps['creation-site-internet-saint-nazaire'] = $h;

		return $maps;
	}
}

/* Injection additive (FAQ, CTA, maillage) sur les piliers approfondis ET les
 * pages métier — source unique, wrapper de marque cohérent. Le contenu propre
 * de chaque page reste en base ; tout le reste est rendu ici (réversible). */
add_filter( 'the_content', function ( $c ) {
	if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) return $c;
	$slug = (string) get_post_field( 'post_name', get_the_ID() );
	$deep = ag_lb_deepen_map();
	$new  = ag_lb_new_defs();
	if ( isset( $deep[ $slug ] ) ) {
		$extra = $deep[ $slug ]; // déjà FAQ + CTA inclus
	} elseif ( isset( $new[ $slug ] ) ) {
		$extra = ag_lb_faq_html( $new[ $slug ]['faq'] ) . ag_lb_cta_html();
	} else {
		return $c;
	}
	return '<div class="ag-lb-page">' . ag_lb_hero_html( $slug ) . $c . $extra . ag_lb_maillage_html( $slug ) . '</div>';
}, 18 );

/* Hero de marque sur la page comparatif « meilleure-agence-web-nantes »
 * (contenu géré par ag-geo.php) — on préfixe juste le visuel égérie. */
add_filter( 'the_content', function ( $c ) {
	if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) return $c;
	if ( 'meilleure-agence-web-nantes' !== (string) get_post_field( 'post_name' ) ) return $c;
	return ag_lb_hero_html( 'meilleure-agence-web-nantes' ) . $c;
}, 17 );

/* ═══════════════════════════════════════════════════════════════════════════
 *  B. PAGES MÉTIER-LOCAL (création idempotente, versionnée)
 * ═══════════════════════════════════════════════════════════════════════════ */

if ( ! function_exists( 'ag_lb_new_defs' ) ) {
	function ag_lb_new_defs() {
		$expr  = esc_url( home_url( '/sites-express' ) );
		$nantes= esc_url( home_url( '/creation-site-internet-nantes' ) );
		$defs  = array();

		/* ---------- RESTAURANT ---------- */
		$c  = "<h1>Création de site internet pour restaurant à Nantes</h1>\n";
		$c .= "<p>Un bon restaurant se remplit d'abord sur internet. Avant de pousser votre porte, vos clients regardent vos photos, lisent vos avis et cherchent votre menu sur leur téléphone. <strong>Alliance Groupe</strong> crée des sites de restaurant à Nantes qui donnent faim, se chargent vite et transforment les curieux en réservations.</p>\n";
		$c .= "<h2>Ce qui fait vendre un site de restaurant</h2>\n<ul>";
		$c .= "<li><strong>Menu en ligne toujours à jour</strong> (fini le PDF illisible sur mobile).</li>";
		$c .= "<li><strong>Réservation en ligne</strong> ou bouton d'appel bien visible.</li>";
		$c .= "<li><strong>Photos qui donnent envie</strong> — le premier vendeur de votre cuisine.</li>";
		$c .= "<li><strong>Avis Google</strong> mis en avant + fiche optimisée pour ressortir dans « restaurant Nantes ».</li>";
		$c .= "<li><strong>Click &amp; collect / livraison</strong> si vous le proposez.</li></ul>\n";
		$c .= "<h2>Visible quand on cherche à manger près de vous</h2>\n";
		$c .= "<p>90 % des recherches « restaurant + quartier » se font sur mobile, souvent en marchant. On optimise votre site et votre fiche Google Business Profile pour que vous apparaissiez au bon moment, dans le bon quartier de Nantes.</p>\n";
		$c .= "<h2>Nos offres</h2>\n<p><strong>Site Express 490 €</strong> · <strong>Pro 890 €</strong> · <strong>Sur-mesure 1 490 €</strong> · maintenance 29–99 €/mois. <a href=\"" . $expr . "\">Voir les offres &rarr;</a></p>\n";
		$defs['creation-site-internet-restaurant-nantes'] = array(
			'title' => 'Création de site internet pour restaurant à Nantes — Alliance Groupe',
			'content' => $c,
			'geo'   => array( 'Nantes', 'Pays de la Loire', 47.2184, -1.5536 ),
			'faq'   => array(
				array( 'Peut-on intégrer la réservation en ligne ?', "Oui, via un module de réservation ou une connexion à votre outil existant (TheFork, etc.). On choisit la solution la plus simple pour vous et vos clients." ),
				array( 'Le menu est-il facile à modifier moi-même ?', "Oui. Vous changez vos plats et vos prix en autonomie, ou on s'en charge dans le cadre de la maintenance." ),
				array( 'Combien de temps pour créer le site de mon restaurant ?', "Quelques jours ouvrés pour un site Express une fois vos photos et votre carte reçues." ),
			),
		);

		/* ---------- ARTISAN / BTP ---------- */
		$c  = "<h1>Création de site internet pour artisan &amp; BTP à Nantes</h1>\n";
		$c .= "<p>Plombier, électricien, maçon, menuisier, paysagiste, couvreur : vos futurs clients tapent votre métier sur Google avant d'appeler. Sans site, vous laissez ces chantiers à vos concurrents. <strong>Alliance Groupe</strong> crée des sites d'artisan à Nantes qui inspirent confiance et font sonner le téléphone.</p>\n";
		$c .= "<h2>Ce dont un artisan a vraiment besoin</h2>\n<ul>";
		$c .= "<li><strong>Bouton d'appel et demande de devis</strong> visibles sur chaque page.</li>";
		$c .= "<li><strong>Galerie de réalisations</strong> (avant/après) — votre meilleur argument.</li>";
		$c .= "<li><strong>Zone d'intervention</strong> claire (Nantes et communes autour).</li>";
		$c .= "<li><strong>Avis clients</strong> et labels/assurances pour rassurer.</li>";
		$c .= "<li><strong>Site rapide sur mobile</strong> — vos clients cherchent souvent en urgence.</li></ul>\n";
		$c .= "<h2>Ressortir sur « artisan + votre ville »</h2>\n";
		$c .= "<p>On structure votre site et votre fiche Google pour capter les recherches locales à forte intention (« plombier Nantes », « rénovation Rezé »…), là où la concurrence est bien plus faible que sur les mots-clés nationaux.</p>\n";
		$c .= "<h2>Nos offres</h2>\n<p><strong>Site Express 490 €</strong> · <strong>Pro 890 €</strong> · <strong>Sur-mesure 1 490 €</strong> · maintenance 29–99 €/mois. <a href=\"" . $expr . "\">Voir les offres &rarr;</a></p>\n";
		$defs['creation-site-internet-artisan-nantes'] = array(
			'title' => 'Création de site internet pour artisan &amp; BTP à Nantes — Alliance Groupe',
			'content' => $c,
			'geo'   => array( 'Nantes', 'Pays de la Loire', 47.2184, -1.5536 ),
			'faq'   => array(
				array( 'Je n\'ai pas de photos pro, c\'est un problème ?', "Non. On vous guide pour photographier vos chantiers avec un simple téléphone, et on optimise le rendu. Vos réalisations réelles valent mieux que des images génériques." ),
				array( 'Le site m\'apportera-t-il vraiment des devis ?', "C'est tout l'objectif : bouton d'appel omniprésent, formulaire de devis rapide, référencement local. Un artisan bien positionné reçoit des demandes chaque semaine." ),
				array( 'Puis-je gérer plusieurs zones d\'intervention ?', "Oui, on met en avant vos communes d'intervention autour de Nantes pour capter les recherches de chacune." ),
			),
		);

		/* ---------- AVOCAT / PROFESSION LIBÉRALE ---------- */
		$c  = "<h1>Création de site internet pour avocat &amp; profession libérale à Nantes</h1>\n";
		$c .= "<p>Un client qui cherche un avocat, un notaire, un expert-comptable ou un thérapeute choisit d'abord avec ses yeux : un site sobre, crédible et sécurisé inspire confiance avant le premier rendez-vous. <strong>Alliance Groupe</strong> conçoit des sites pour les professions libérales de Nantes, dans le respect des règles déontologiques de chaque profession.</p>\n";
		$c .= "<h2>Crédibilité, confidentialité, prise de rendez-vous</h2>\n<ul>";
		$c .= "<li><strong>Design sobre et professionnel</strong> qui installe la confiance.</li>";
		$c .= "<li><strong>Prise de rendez-vous en ligne</strong> pour libérer votre secrétariat.</li>";
		$c .= "<li><strong>Sécurité &amp; RGPD renforcés</strong> — essentiel quand on traite des données sensibles.</li>";
		$c .= "<li><strong>Contenu qui démontre votre expertise</strong> (domaines d'intervention, articles).</li>";
		$c .= "<li><strong>Respect de la déontologie</strong> (barreau, ordre) dans les mentions et la communication.</li></ul>\n";
		$c .= "<h2>Être trouvé au bon moment</h2>\n";
		$c .= "<p>« avocat divorce Nantes », « expert-comptable Nantes », « ostéopathe + quartier » : ce sont des recherches locales très qualifiées. On optimise votre site et votre présence Google pour y apparaître, avec la rigueur qu'imposent ces métiers.</p>\n";
		$c .= "<h2>Nos offres</h2>\n<p><strong>Site Express 490 €</strong> · <strong>Pro 890 €</strong> · <strong>Sur-mesure 1 490 €</strong> · maintenance 29–99 €/mois. <a href=\"" . $expr . "\">Voir les offres &rarr;</a></p>\n";
		$defs['creation-site-internet-avocat-nantes'] = array(
			'title' => 'Site internet pour avocat &amp; profession libérale à Nantes — Alliance Groupe',
			'content' => $c,
			'geo'   => array( 'Nantes', 'Pays de la Loire', 47.2184, -1.5536 ),
			'faq'   => array(
				array( 'Respectez-vous les règles déontologiques (barreau, ordres) ?', "Oui. Nous connaissons les contraintes de communication des avocats et professions réglementées et adaptons le contenu (pas de publicité comparative, mentions conformes, sobriété)." ),
				array( 'Les données de mes clients seront-elles protégées ?', "C'est notre spécialité : chaque site est livré sécurisé, formulaires chiffrés, conformité RGPD, hébergement adapté aux données sensibles." ),
				array( 'Peut-on intégrer un agenda de rendez-vous ?', "Oui, avec un module de prise de rendez-vous en ligne synchronisé à votre agenda, pour réduire les appels et les no-shows." ),
			),
		);

		/* ---------- E-COMMERCE ---------- */
		$c  = "<h1>Création de site e-commerce à Nantes</h1>\n";
		$c .= "<p>Vendre en ligne, c'est ouvrir une boutique qui ne ferme jamais. Mais une boutique qui rame, se fait pirater ou n'est pas trouvée sur Google ne vend rien. <strong>Alliance Groupe</strong> crée des sites e-commerce à Nantes rapides, sécurisés et pensés pour convertir.</p>\n";
		$c .= "<h2>Une boutique qui vend vraiment</h2>\n<ul>";
		$c .= "<li><strong>Paiement sécurisé</strong> (CB, PayPal, Apple/Google Pay) et tunnel d'achat sans friction.</li>";
		$c .= "<li><strong>Fiches produits optimisées SEO</strong> pour être trouvé sur Google Shopping et l'organique.</li>";
		$c .= "<li><strong>Gestion des stocks, livraison et retours</strong> claires.</li>";
		$c .= "<li><strong>Vitesse &amp; mobile</strong> — chaque seconde de chargement coûte des ventes.</li>";
		$c .= "<li><strong>Sécurité des données bancaires</strong> et conformité RGPD, notre spécialité.</li></ul>\n";
		$c .= "<h2>Du catalogue à la première vente</h2>\n";
		$c .= "<p>On vous accompagne du choix de la solution (WooCommerce ou sur-mesure) à la mise en ligne, avec un plan de référencement pour attirer vos premiers clients — sans dépendre uniquement de la publicité payante.</p>\n";
		$c .= "<h2>Nos offres</h2>\n<p>Devis sur-mesure selon le nombre de produits et les fonctionnalités. <a href=\"" . esc_url( home_url( '/contact' ) ) . "\">Parlons de votre projet &rarr;</a> ou découvrez d'abord la <a href=\"" . $nantes . "\">création de site à Nantes</a>.</p>\n";
		$defs['creation-site-e-commerce-nantes'] = array(
			'title' => 'Création de site e-commerce à Nantes — Alliance Groupe',
			'content' => $c,
			'geo'   => array( 'Nantes', 'Pays de la Loire', 47.2184, -1.5536 ),
			'faq'   => array(
				array( 'WooCommerce ou solution sur-mesure ?', "Pour la plupart des projets, WooCommerce offre le meilleur rapport puissance/coût et vous laisse propriétaire de tout. Pour des besoins spécifiques, on développe sur-mesure." ),
				array( 'Comment serai-je payé ?', "Directement sur votre compte via un prestataire de paiement sécurisé (Stripe, PayPal…). Vous gardez le contrôle total de votre trésorerie." ),
				array( 'Comment attirer mes premiers clients ?', "Référencement des fiches produits, Google Shopping, contenu et, si vous le souhaitez, publicité maîtrisée. On bâtit une acquisition qui dure, pas seulement de la pub." ),
			),
		);

		return $defs;
	}
}

if ( ! function_exists( 'ag_lb_ensure_pages' ) ) {
	/** Crée les pages métier une seule fois (jamais réécrites ensuite). */
	function ag_lb_ensure_pages() {
		if ( (int) get_option( 'ag_localboost_pages_done', 0 ) >= AG_LOCALBOOST_VER ) return;
		if ( ! function_exists( 'wp_insert_post' ) ) return;
		foreach ( ag_lb_new_defs() as $slug => $def ) {
			if ( get_page_by_path( $slug ) ) continue;
			// Contenu propre uniquement ; FAQ/CTA/maillage sont ajoutés au rendu
			// (filtre the_content) pour rester la source unique et stylée.
			wp_insert_post( array(
				'post_title'   => wp_specialchars_decode( $def['title'] ),
				'post_name'    => $slug,
				'post_content' => $def['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => 1,
			) );
		}
		update_option( 'ag_localboost_pages_done', AG_LOCALBOOST_VER );
	}
}
add_action( 'admin_init', 'ag_lb_ensure_pages' );
add_action( 'init', function () {
	// Filet de sécurité : crée aussi les pages sans passer par le back-office
	// (une fois seulement, protégé par l'option de version).
	if ( (int) get_option( 'ag_localboost_pages_done', 0 ) < AG_LOCALBOOST_VER ) {
		ag_lb_ensure_pages();
	}
}, 99 );

/* ═══════════════════════════════════════════════════════════════════════════
 *  C. SCHEMA PAR PAGE (nouvelles pages métier)
 * ═══════════════════════════════════════════════════════════════════════════ */

add_action( 'wp_head', function () {
	if ( ! is_page() ) return;
	$slug = (string) get_post_field( 'post_name' );
	$defs = ag_lb_new_defs();
	if ( ! isset( $defs[ $slug ] ) ) return;
	$def = $defs[ $slug ];
	list( $loc, $region, $lat, $lng ) = $def['geo'];

	/* ProfessionalService géolocalisé (signal local fort). */
	$ps = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'ProfessionalService',
		'name'       => 'Alliance Groupe — ' . $loc,
		'url'        => get_permalink(),
		'image'      => get_stylesheet_directory_uri() . '/assets/images/pwa/icon-512.png',
		'telephone'  => '+33744829516',
		'email'      => 'contact@alliancegroupe-inc.com',
		'priceRange' => '€€',
		'areaServed' => array( $loc, 'Loire-Atlantique', 'France' ),
		'address'    => array( '@type' => 'PostalAddress', 'addressLocality' => $loc, 'addressRegion' => $region, 'addressCountry' => 'FR' ),
		'geo'        => array( '@type' => 'GeoCoordinates', 'latitude' => $lat, 'longitude' => $lng ),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $ps, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

	/* Service (offre + zone). */
	$svc = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'serviceType' => wp_specialchars_decode( get_the_title() ),
		'provider'    => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ?: 'Alliance Groupe', 'url' => home_url( '/' ) ),
		'areaServed'  => array( $loc, 'Loire-Atlantique', 'France' ),
		'url'         => get_permalink(),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $svc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

	/* FAQPage — seulement si aucun plugin SEO ne le fait déjà (évite le doublon). */
	if ( ! empty( $def['faq'] ) && ! ( function_exists( 'ag_seo_plugin_active' ) && ag_seo_plugin_active() ) ) {
		$items = array();
		foreach ( $def['faq'] as $qa ) {
			$items[] = array(
				'@type'          => 'Question',
				'name'           => $qa[0],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $qa[1] ) ),
			);
		}
		$fp = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items );
		echo '<script type="application/ld+json">' . wp_json_encode( $fp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}, 7 );

/* ═══════════════════════════════════════════════════════════════════════════
 *  D. MISE EN BEAUTÉ (CSS scoppé marque) — piliers approfondis + pages métier
 * ═══════════════════════════════════════════════════════════════════════════ */

add_action( 'wp_head', function () {
	if ( ! is_page() ) return;
	$slug = (string) get_post_field( 'post_name' );
	$is_new    = array_key_exists( $slug, ag_lb_new_defs() );
	$is_deep   = array_key_exists( $slug, ag_lb_deepen_map() );
	$is_hero   = array_key_exists( $slug, ag_lb_hero_map() );
	if ( ! $is_new && ! $is_deep && ! $is_hero ) return;
	?>
<style>
.ag-lb-page,.page-id-body .entry-content{--ag-gold:#D4B45C;--ag-gold2:#E7C979;--ag-or:#F37A1F}
.ag-lb-page{max-width:1000px;margin:0 auto;font-size:1.04rem;line-height:1.7}
.ag-lb-hero{position:relative;margin:0 0 1.6em;border-radius:18px;overflow:hidden;border:1px solid rgba(212,180,92,.25);aspect-ratio:16/9;max-height:420px}
.ag-lb-hero img{width:100%;height:100%;object-fit:cover;display:block}
.ag-lb-hero__veil{position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,10,15,.15),rgba(10,10,15,.55));pointer-events:none}
@media(max-width:600px){.ag-lb-hero{aspect-ratio:4/3}}
.ag-lb-page h2,.ag-lb-cta+*{scroll-margin-top:90px}
.ag-lb-page h2{color:#D4B45C;font-size:clamp(1.45rem,3vw,2rem);margin:2em 0 .5em}
.ag-lb-page h2:after{content:"";display:block;width:60px;height:3px;margin-top:.4em;border-radius:2px;background:linear-gradient(90deg,#D4B45C,#F37A1F)}
.ag-lb-page h3{color:#fff;margin:1.5em 0 .3em;font-size:1.1rem}
.ag-lb-page ul,.ag-lb-page ol{margin:1em 0;padding-left:0;list-style:none;display:grid;gap:10px}
.ag-lb-page ul>li,.ag-lb-page ol>li{position:relative;padding:12px 16px 12px 46px;background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.2);border-radius:12px}
.ag-lb-page ul>li:before{content:"✓";position:absolute;left:14px;top:12px;width:22px;height:22px;border-radius:50%;background:#D4B45C;color:#0a0a0f;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center}
.ag-lb-page ol{counter-reset:agc}
.ag-lb-page ol>li{counter-increment:agc}
.ag-lb-page ol>li:before{content:counter(agc);position:absolute;left:13px;top:11px;width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#0a0a0f;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center}
.ag-lb-cta{margin:2.4em 0;padding:1.8em 1.4em;text-align:center;border-radius:20px;border:1px solid rgba(212,180,92,.3);background:linear-gradient(160deg,#14141f,#0b0b13)}
.ag-lb-cta__txt{color:#e7dcc2;font-size:1.08rem;margin:0 0 1em}
.ag-lb-cta__btns{display:flex;flex-wrap:wrap;justify-content:center;gap:10px}
.ag-lb-btn-gold{display:inline-block;padding:13px 24px;border-radius:999px;font-weight:700;text-decoration:none;color:#0a0a0f;background:linear-gradient(90deg,#D4B45C,#F37A1F);box-shadow:0 8px 20px rgba(243,122,31,.25)}
.ag-lb-btn-ghost{display:inline-block;padding:11px 22px;border-radius:999px;font-weight:700;text-decoration:none;color:#D4B45C;border:2px solid #D4B45C}
.ag-lb-btn-gold:hover{filter:brightness(1.07)}.ag-lb-btn-ghost:hover{background:rgba(212,180,92,.12)}
.ag-lb-maillage{margin:2.6em 0 1em;padding-top:1.4em;border-top:1px solid rgba(212,180,92,.2)}
.ag-lb-maillage h2{color:#D4B45C;font-size:1.2rem;margin:0 0 .7em}
.ag-lb-maillage ul{list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:8px}
.ag-lb-maillage li{margin:0}
.ag-lb-maillage a{display:inline-block;padding:8px 14px;border-radius:999px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.25);color:#e7c979;text-decoration:none;font-size:.9rem}
.ag-lb-maillage a:hover{background:rgba(212,180,92,.18)}
</style>
<?php
}, 14 );
