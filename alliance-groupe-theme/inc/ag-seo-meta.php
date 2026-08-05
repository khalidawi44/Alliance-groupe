<?php
/**
 * AG SEO Meta — Titres + descriptions optimisees par page + schemas
 * additionnels (LocalBusiness par bureau, Offer pour templates).
 *
 * Strategie mots-cles :
 *   - Primaires : agence web Nantes, creation site WordPress, agence IA
 *   - Niche : site WordPress [metier] x6, templates WordPress gratuits
 *   - Geo : Nantes + Marrakech + Naples (3 LocalBusiness)
 *   - Differentiateur : multiculturel franco-italo-marocain
 *
 * Pattern titre : [Mot-cle principal] | [Ville si pertinent] — Alliance Groupe
 * Pattern desc  : 150-160 chars, verbe action, telephone, mot-cle, CTA
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retourne le couple (title, description) optimise SEO pour la page courante.
 * Fallback : valeurs vides => le theme retombe sur l'auto-gen (excerpt/title).
 *
 * @return array{title:string, description:string, og_image_alt:string}
 */
function ag_seo_meta() {
	static $cache = null;
	if ( $cache !== null ) return $cache;

	$slug = '';
	// __home__ s'applique aussi si la page utilise le template Accueil (robuste
	// même si « Réglages → Lecture » ne pointe pas cette page comme page d'accueil).
	if ( is_front_page() || is_home() || is_page_template( 'templates/page-accueil.php' ) ) {
		$slug = '__home__';
	} elseif ( is_singular() ) {
		$slug = get_post_field( 'post_name' );
	}

	// Catalogue centralise : titles + descriptions tunes par mots-cles.
	// Chaque description ~150-160 chars, integre 1-3 mots-cles + ville + CTA.
	$map = array(
		'__home__' => array(
			'title'   => 'Agence web, sécurité & IA à Nantes — Alliance Groupe',
			'desc'    => 'Agence web & IA à Nantes : sites sécurisés + automatisation par IA (assistant, devis instantané, prospection). Testez votre site gratuitement ☎ 07.44.82.95.16',
			'img_alt' => 'Alliance Groupe — agence web, sécurité et IA à Nantes',
		),
		// ── Services ──────────────────────────────────────────────
		'service-creation-web' => array(
			'title'   => 'Création de site WordPress sur-mesure à Nantes — Alliance Groupe',
			'desc'    => 'Création de sites WordPress sur-mesure à Nantes & en France : vitrines, e-commerce, refonte. Design premium, performance, SEO. Audit gratuit 30 min.',
			'img_alt' => 'Service de création de sites web Alliance Groupe',
		),
		'service-ia' => array(
			'title'   => 'Agence IA & Automatisation pour PME — Alliance Groupe',
			'desc'    => 'IA & automatisation : chatbots, workflows, agents OpenAI/Claude pour PME. Gains de temps mesurables, sécurisés. Audit gratuit ☎ Nantes.',
			'img_alt' => 'Service IA et automatisation Alliance Groupe',
		),
		'service-seo' => array(
			'title'   => 'Agence SEO à Nantes — Référencement Google — Alliance Groupe',
			'desc'    => 'Agence SEO à Nantes : audit complet, stratégie de référencement naturel, netlinking. Dominez Google en local et national. Audit SEO gratuit 30 min.',
			'img_alt' => 'Service SEO et référencement naturel Alliance Groupe',
		),
		'service-publicite' => array(
			'title'   => 'Publicité Digitale Google Ads & Meta Ads — Alliance Groupe',
			'desc'    => 'Campagnes Google Ads & Meta Ads optimisées pour le ROI : audit pub, lancement, tracking, optimisation continue. Alliance Groupe Nantes ☎ Audit gratuit.',
			'img_alt' => 'Service publicité digitale Alliance Groupe',
		),
		'service-branding' => array(
			'title'   => 'Branding & Identité Visuelle Premium — Alliance Groupe',
			'desc'    => 'Branding & identité visuelle premium : logos, chartes graphiques, design system, naming. Alliance Groupe Nantes ☎ Audit branding gratuit 30 min.',
			'img_alt' => 'Service branding et identité visuelle Alliance Groupe',
		),
		'service-conseil' => array(
			'title'   => 'Conseil Stratégique Digital pour PME — Alliance Groupe',
			'desc'    => 'Conseil stratégique digital : audit complet, roadmap, accompagnement. Pour PME francophones. Alliance Groupe Nantes ☎ Audit conseil gratuit 30 min.',
			'img_alt' => 'Service conseil stratégique Alliance Groupe',
		),
		// ── Bureaux (SEO local) ───────────────────────────────────
		'bureau-nantes' => array(
			'title'   => 'Agence Web & IA à Nantes — Bureau Alliance Groupe',
			'desc'    => 'Bureau Alliance Groupe à Nantes (Pays de la Loire). Création WordPress, SEO, IA pour PME locales. Équipe française ☎ 07.44.82.95.16 — Devis gratuit.',
			'img_alt' => 'Bureau Alliance Groupe à Nantes',
		),
		'bureau-marrakech' => array(
			'title'   => 'Agence Web à Marrakech — Bureau Alliance Groupe Maroc',
			'desc'    => 'Bureau Alliance Groupe à Marrakech : SEO, IA, automatisation pour PME francophones. Pôle data et référencement de l\'agence ☎ Devis sur mesure.',
			'img_alt' => 'Bureau Alliance Groupe à Marrakech',
		),
		'bureau-naples' => array(
			'title'   => 'Agenzia Web a Napoli — Alliance Groupe Italia',
			'desc'    => 'Bureau Alliance Groupe à Naples : développement WordPress avancé, architecture backend, DevOps. Sviluppo siti web e applicazioni ☎ Contattaci.',
			'img_alt' => 'Bureau Alliance Groupe à Naples',
		),
		// ── Templates métier (long-tail = peu de concurrence) ─────
		'templates' => array(
			'title'   => '6 Templates WordPress Métier Gratuits — Alliance Groupe',
			'desc'    => '6 templates WordPress métier gratuits : coach, artisan, avocat, barber, restaurant, association. Téléchargement immédiat, prêts à personnaliser.',
			'img_alt' => 'Templates WordPress métier gratuits Alliance Groupe',
		),
		'wordpress-coach' => array(
			'title'   => 'Site WordPress pour Coach Sportif & Mental — Template Gratuit',
			'desc'    => 'Site WordPress pour coach sportif, mental ou bien-être : template gratuit + version premium sur-mesure. Réservations, témoignages, programmes. Audit gratuit.',
			'img_alt' => 'Template WordPress pour coach',
		),
		'wordpress-artisan' => array(
			'title'   => 'Site WordPress pour Artisan — Template Gratuit — Alliance Groupe',
			'desc'    => 'Site WordPress pour artisan (menuisier, plombier, électricien…) : vitrine, services, devis en ligne, galerie. Template gratuit + sur-mesure. Audit gratuit.',
			'img_alt' => 'Template WordPress pour artisan',
		),
		'wordpress-avocat' => array(
			'title'   => 'Site WordPress pour Avocat — Template Conforme — Alliance Groupe',
			'desc'    => 'Site WordPress pour avocat : design premium conforme CNB, mentions légales, prise de RDV. Template gratuit + version Business. Audit gratuit 30 min.',
			'img_alt' => 'Template WordPress pour avocat',
		),
		'wordpress-barber' => array(
			'title'   => 'Site WordPress pour Barber Shop & Coiffeur — Template Gratuit',
			'desc'    => 'Site WordPress pour barber shop ou salon de coiffure : réservations en ligne, galerie, équipe. Template gratuit + premium. Alliance Groupe ☎ Audit.',
			'img_alt' => 'Template WordPress pour barber shop',
		),
		'wordpress-restaurant' => array(
			'title'   => 'Site WordPress pour Restaurant — Template Gratuit — Alliance Groupe',
			'desc'    => 'Site WordPress pour restaurant : menu en ligne, réservations, galerie, avis. Template gratuit + sur-mesure premium. Alliance Groupe ☎ Audit gratuit.',
			'img_alt' => 'Template WordPress pour restaurant',
		),
		'wordpress-association' => array(
			'title'   => 'Site WordPress pour Association — Template Gratuit — Alliance Groupe',
			'desc'    => 'Site WordPress pour association : adhésions, événements, dons, actualités. Template gratuit prêt à utiliser. Alliance Groupe ☎ Audit gratuit.',
			'img_alt' => 'Template WordPress pour association',
		),
		// ── Pages stratégiques ─────────────────────────────────────
		'a-propos' => array(
			'title'   => 'À propos — Studio web indépendant à Nantes — Alliance Groupe',
			'desc'    => 'Alliance Groupe : studio web indépendant à Nantes, fondé par Fabrizio. Audit de sécurité, création de sites, maintenance. Un seul interlocuteur, du conseil à la livraison.',
			'img_alt' => 'À propos Alliance Groupe',
		),
		'notre-fondateur' => array(
			'title'   => 'Fabrizio, fondateur d\'Alliance Groupe — Studio web à Nantes',
			'desc'    => 'Fabrizio, fondateur d\'Alliance Groupe : né à Naples, installé à Nantes. Studio indépendant : un seul interlocuteur, du conseil à la livraison. Sécuriser ce qui compte.',
			'img_alt' => 'Fabrizio fondateur Alliance Groupe',
		),
		'realisations' => array(
			'title'   => 'Réalisations — Sites web sur-mesure — Alliance Groupe',
			'desc'    => 'Réalisations Alliance Groupe : sites WordPress sur-mesure, refontes, e-commerce. Des projets concrets, des résultats mesurables.',
			'img_alt' => 'Réalisations Alliance Groupe',
		),
		'contact' => array(
			'title'   => 'Contact — Alliance Groupe, studio web à Nantes',
			'desc'    => 'Contactez Alliance Groupe, studio web à Nantes ☎ 07.44.82.95.16 ✉ contact@alliancegroupe-inc.com — Réponse sous 24 h.',
			'img_alt' => 'Contact Alliance Groupe',
		),
		'rendez-vous' => array(
			'title'   => 'Réserver un Audit Digital Gratuit 30 min — Alliance Groupe',
			'desc'    => 'Réservez un appel gratuit de 30 min : audit digital sur-mesure de votre site, SEO, stratégie IA. Visio, sans engagement. Alliance Groupe — agence web.',
			'img_alt' => 'Réservation audit gratuit Alliance Groupe',
		),
		'rdv' => array(
			'title'   => 'Réserver un Audit Digital Gratuit 30 min — Alliance Groupe',
			'desc'    => 'Réservez un appel gratuit de 30 min : audit digital sur-mesure de votre site, SEO, stratégie IA. Visio, sans engagement. Alliance Groupe — agence web.',
			'img_alt' => 'Réservation audit gratuit Alliance Groupe',
		),
		'pourquoi-alliance' => array(
			'title'   => 'Pourquoi Alliance Groupe vs Templates ThemeForest — Comparatif',
			'desc'    => 'Pourquoi choisir Alliance Groupe plutôt qu\'un template ThemeForest ? Comparatif détaillé sur-mesure vs DIY : prix, performance, SEO, support, ROI.',
			'img_alt' => 'Comparatif Alliance Groupe vs ThemeForest',
		),
		'questions-flash' => array(
			'title'   => 'Questions Flash — Réponses Express d\'Experts — Alliance Groupe',
			'desc'    => 'Questions Flash Alliance Groupe : 3 packs pour obtenir des réponses d\'experts sur votre projet digital en 24-48h. WordPress, SEO, IA, stratégie.',
			'img_alt' => 'Questions Flash Alliance Groupe',
		),
		'services' => array(
			'title'   => 'Nos services — Audit, création, maintenance — Alliance Groupe',
			'desc'    => 'Alliance Groupe : audit de sécurité, création de sites WordPress sur-mesure et maintenance. Studio web indépendant à Nantes.',
			'img_alt' => 'Services Alliance Groupe',
		),
		'programme-racines' => array(
			'title'   => 'Programme Racines — Devenez Entrepreneur avec Alliance Groupe',
			'desc'    => 'Programme Racines : Alliance Groupe devient votre associé. Site premium, accompagnement création d\'entreprise, gestion du numérique et formation pour les quartiers populaires.',
			'img_alt' => 'Programme Racines Alliance Groupe — entrepreneuriat quartiers populaires',
		),
		'ambassadeurs' => array(
			'title'   => 'Programme Ambassadeurs — Gagnez 10% en vendant nos services',
			'desc'    => 'Rejoignez le programme ambassadeurs Alliance Groupe : vendez nos services digitaux et touchez 10% de commission par vente, payés via PayPal. Inscription gratuite par email.',
			'img_alt' => 'Programme Ambassadeurs Alliance Groupe — 10% de commission',
		),
		'sites-express' => array(
			'title'   => 'Sites Express — Votre site pro livré en quelques jours, prix fixe',
			'desc'    => 'Commandez votre site web en ligne : packs à prix fixe (490€, 890€, 1490€), paiement sécurisé, livraison rapide. Sans rendez-vous — vous payez, vous briefez, on livre.',
			'img_alt' => 'Sites Express Alliance Groupe — sites web à prix fixe livrés vite',
		),
	);

	$cache = array( 'title' => '', 'desc' => '', 'img_alt' => '' );
	if ( $slug && isset( $map[ $slug ] ) ) {
		$cache = array(
			'title'   => $map[ $slug ]['title'],
			'desc'    => $map[ $slug ]['desc'],
			'img_alt' => $map[ $slug ]['img_alt'],
		);
	}
	return $cache;
}

// ── 1. Override du title (filter document_title_parts) ─────────────
// Détecte un plugin SEO majeur : si présent, on le laisse gérer title/meta/OG
// (sinon on aurait des balises EN DOUBLE, ce qui nuit au référencement).
if ( ! function_exists( 'ag_seo_plugin_active' ) ) {
	function ag_seo_plugin_active() {
		return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'AIOSEOP_VERSION' );
	}
}
add_filter( 'document_title_parts', function ( $parts ) {
	if ( ag_seo_plugin_active() ) return $parts;
	$meta = ag_seo_meta();
	if ( ! empty( $meta['title'] ) ) {
		// Remplace tout pour avoir le pattern exact (sans " — Alliance Groupe" auto-ajoute)
		$parts = array( 'title' => $meta['title'] );
	}
	return $parts;
} );

// Force separateur clean (le " — " standard) — sauf si un plugin SEO gère le titre.
add_filter( 'document_title_separator', function ( $sep ) { return ag_seo_plugin_active() ? $sep : '—'; } );

// ── 2. Override meta description, OG, Twitter (priority 1 = avant les anciens) ──
// On retire les anciens hooks pour eviter doublons, puis on re-emet proprement.
remove_action( 'wp_head', 'ag_seo_meta_description_legacy', 10 ); // au cas ou

add_action( 'wp_head', function () {
	if ( ag_seo_plugin_active() ) return; // Yoast & co. gèrent meta/OG/Twitter → pas de doublon.
	$meta = ag_seo_meta();
	if ( empty( $meta['desc'] ) ) return; // fallback : le hook legacy s'en charge

	// 1. Meta description
	echo '<meta name="description" content="' . esc_attr( $meta['desc'] ) . '">' . "\n";

	// 2. Open Graph description override
	echo '<meta property="og:description" content="' . esc_attr( $meta['desc'] ) . '" data-ag-override>' . "\n";

	// 3. Twitter description override
	echo '<meta name="twitter:description" content="' . esc_attr( $meta['desc'] ) . '" data-ag-override>' . "\n";

	// 4. og:image:alt si on a un alt defini
	if ( ! empty( $meta['img_alt'] ) ) {
		echo '<meta property="og:image:alt" content="' . esc_attr( $meta['img_alt'] ) . '">' . "\n";
		echo '<meta name="twitter:image:alt" content="' . esc_attr( $meta['img_alt'] ) . '">' . "\n";
	}

	// 5. og:title + twitter:title override (override le wp_get_document_title qui peut avoir un suffixe site)
	if ( ! empty( $meta['title'] ) ) {
		echo '<meta property="og:title" content="' . esc_attr( $meta['title'] ) . '" data-ag-override>' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $meta['title'] ) . '" data-ag-override>' . "\n";
	}
}, 1 );

// Empeche le hook legacy (section 8 fonctions.php) d'ecrire un 2eme meta description
// quand on a deja un override custom. Le hook legacy verifie la presence du contenu output.
add_filter( 'ag_skip_legacy_meta_description', function ( $skip ) {
	$meta = ag_seo_meta();
	return ! empty( $meta['desc'] ) ? true : $skip;
} );

// ── 3. LocalBusiness JSON-LD specifique a chaque page bureau ───────
add_action( 'wp_head', function () {
	if ( ! is_page() ) return;
	$slug = get_post_field( 'post_name' );

	$bureaux = array(
		'bureau-nantes' => array(
			'name'     => 'Alliance Groupe — Bureau de Nantes',
			'locality' => 'Nantes',
			'region'   => 'Pays de la Loire',
			'country'  => 'FR',
			'lat'      => 47.2173,
			'lng'      => -1.5534,
			'lang'     => 'fr-FR',
		),
		'bureau-marrakech' => array(
			'name'     => 'Alliance Groupe — Bureau de Marrakech',
			'locality' => 'Marrakech',
			'region'   => 'Marrakech-Safi',
			'country'  => 'MA',
			'lat'      => 31.6295,
			'lng'      => -8.0088,
			'lang'     => 'fr-FR',
		),
		'bureau-naples' => array(
			'name'     => 'Alliance Groupe — Ufficio di Napoli',
			'locality' => 'Napoli',
			'region'   => 'Campania',
			'country'  => 'IT',
			'lat'      => 40.8518,
			'lng'      => 14.2681,
			'lang'     => 'it-IT',
		),
	);

	if ( ! isset( $bureaux[ $slug ] ) ) return;
	$b = $bureaux[ $slug ];

	$lb = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'ProfessionalService',
		'name'      => $b['name'],
		'url'       => get_permalink(),
		'telephone' => '+33744829516',
		'email'     => 'contact@alliancegroupe-inc.com',
		'address'   => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $b['locality'],
			'addressRegion'   => $b['region'],
			'addressCountry'  => $b['country'],
		),
		'geo' => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => $b['lat'],
			'longitude' => $b['lng'],
		),
		'parentOrganization' => array(
			'@type' => 'Organization',
			'name'  => 'Alliance Groupe',
			'url'   => home_url( '/' ),
		),
		'priceRange' => '€€',
		'serviceArea' => array(
			'@type' => 'AdministrativeArea',
			'name'  => $b['locality'],
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $lb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

// ── 3b. Verification webmaster tools (Google Search Console + Bing) ──
//    Codes stockes en options WP, configurables depuis Outils > Import AG.
//    Affiche les meta tags dans <head> si configures.
add_action( 'wp_head', function () {
	$gsc  = trim( (string) get_option( 'ag_gsc_verification', '' ) );
	$bing = trim( (string) get_option( 'ag_bing_verification', '' ) );
	$fb   = trim( (string) get_option( 'ag_fb_domain_verification', '' ) );
	$pin  = trim( (string) get_option( 'ag_pinterest_verification', '' ) );

	if ( $gsc !== '' ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $gsc ) . '">' . "\n";
	}
	if ( $bing !== '' ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( $bing ) . '">' . "\n";
	}
	if ( $fb !== '' ) {
		echo '<meta name="facebook-domain-verification" content="' . esc_attr( $fb ) . '">' . "\n";
	}
	if ( $pin !== '' ) {
		echo '<meta name="p:domain_verify" content="' . esc_attr( $pin ) . '">' . "\n";
	}
}, 1 );

// ── 4. Offer schema sur la page Templates (prix Pro/Premium/Business) ──
add_action( 'wp_head', function () {
	if ( ! is_page( 'templates' ) ) return;

	$offers = array(
		'@context' => 'https://schema.org',
		'@type'    => 'AggregateOffer',
		'name'     => 'Templates WordPress métier Alliance Groupe',
		'description' => '6 templates WordPress métier gratuits + version Premium',
		'url'         => get_permalink(),
		'lowPrice'    => '0',
		'highPrice'   => (string) ( function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69 ),
		'priceCurrency' => 'EUR',
		'offerCount'  => '2',
		'offers'      => array(
			array(
				'@type'         => 'Offer',
				'name'          => 'Template Gratuit',
				'price'         => '0',
				'priceCurrency' => 'EUR',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink(),
			),
			array(
				'@type'         => 'Offer',
				'name'          => 'Pack Premium',
				'price'         => (string) ( function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69 ),
				'priceCurrency' => 'EUR',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink(),
			),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $offers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

// ── 5. FAQPage (rich snippet) sur l'accueil — Yoast free ne le génère pas ──
add_action( 'wp_head', function () {
	if ( ! is_front_page() && ! is_home() ) return;
	$faq = array(
		array( 'Combien coûte un site internet chez Alliance Groupe ?', 'Nos sites web professionnels démarrent à 490 € (pack Site Express), 890 € (Pro) et 1490 € (sur-mesure), avec une maintenance optionnelle de 29 à 99 €/mois. Devis gratuit en ligne.' ),
		array( 'Proposez-vous la sécurité informatique et l’audit de site ?', 'Oui. Alliance Groupe est un studio web ET cybersécurité : test de sécurité gratuit de votre site (score /100 + failles), audit approfondi et pentest sur mandat écrit, mise en conformité RGPD.' ),
		array( 'Intervenez-vous à Nantes et en Loire-Atlantique ?', 'Oui, nous accompagnons les entreprises de Nantes et toute la Loire-Atlantique (Saint-Herblain, Rezé, Saint-Nazaire, Vertou, Carquefou…), à distance partout en France, avec une présence à Naples.' ),
		array( 'Faites-vous le référencement naturel (SEO) ?', 'Oui : optimisation SEO technique, contenu, SEO local (Google Business Profile) et suivi, pour rendre votre site visible et durablement bien positionné sur Google.' ),
		array( 'Le test de sécurité de mon site est-il vraiment gratuit ?', 'Oui, le test « Tester mon site » est gratuit et non-intrusif : vous obtenez un score sur 100 et la liste des points à corriger, sans engagement.' ),
	);
	$items = array();
	foreach ( $faq as $qa ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => $qa[0],
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa[1] ),
		);
	}
	$schema = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

// ── 6. Organization + WebSite (SearchAction) — uniquement si AUCUN plugin SEO ──
//    (Yoast/Rank Math émettent déjà ce graphe → on évite le doublon.)
add_action( 'wp_head', function () {
	if ( ag_seo_plugin_active() || ! is_front_page() ) return;
	$home = home_url( '/' );
	$logo = get_stylesheet_directory_uri() . '/assets/images/pwa/icon-512.png';
	$org = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_bloginfo( 'name' ) ?: 'Alliance Groupe',
		'url'      => $home,
		'logo'     => $logo,
		'email'    => 'contact@alliancegroupe-inc.com',
		'telephone' => '+33744829516',
		'description' => 'Studio web & cybersécurité à Nantes (Loire-Atlantique) : création de sites internet rapides et sécurisés, audit de sécurité, conformité NIS2 et maintenance. Un seul interlocuteur, du conseil à la livraison.',
		'slogan'   => 'La beauté d’un site, la solidité d’un coffre-fort.',
		'areaServed' => array( 'Nantes', 'Saint-Nazaire', 'Loire-Atlantique', 'FR', 'IT' ),
		'knowsAbout' => array(
			'Création de site internet',
			'Refonte de site web',
			'Cybersécurité des PME',
			'Audit de sécurité de site web',
			'Conformité NIS2',
			'Référencement SEO local',
			'Maintenance de site WordPress',
		),
		'sameAs'   => array_values( array_filter( array(
			get_option( 'ag_social_facebook', '' ),
			get_option( 'ag_social_instagram', '' ),
			get_option( 'ag_social_linkedin', '' ),
		) ) ),
	);
	$site = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => get_bloginfo( 'name' ) ?: 'Alliance Groupe',
		'url'             => $home,
		'inLanguage'      => 'fr-FR',
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => $home . '?s={search_term_string}' ),
			'query-input' => 'required name=search_term_string',
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	echo '<script type="application/ld+json">' . wp_json_encode( $site, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

// ── Canonical de secours (blog / archives / recherche) ─────────────
// WordPress ajoute déjà rel=canonical sur les pages et articles (is_singular).
// On complète UNIQUEMENT là où il ne le fait pas, sans doublon.
add_action( 'wp_head', function () {
	if ( ag_seo_plugin_active() ) return;
	$url = '';
	if ( is_home() && ! is_front_page() ) {
		$pid = (int) get_option( 'page_for_posts' );
		if ( $pid ) $url = get_permalink( $pid );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_post_type_archive() ) {
		$url = get_post_type_archive_link( get_post_type() );
	}
	if ( $url && ! is_wp_error( $url ) ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}
}, 2 );
