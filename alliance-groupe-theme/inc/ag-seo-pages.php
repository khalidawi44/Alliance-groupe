<?php
/**
 * AG SEO Pages — pages piliers SEO créées automatiquement (idempotent), avec
 * contenu optimisé + schema LocalBusiness, pour viser les requêtes
 * transactionnelles locales sans dupliquer ce que le site a déjà.
 *
 *  - /creation-site-internet-nantes  (pilier WEB local)
 *  - /securite-informatique-pme-nantes (pilier CYBER + hub NIS2)
 *
 * Création unique (flag de version) ; jamais écrasées si tu les édites ensuite.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_SEO_PAGES_VER', 2 );

if ( ! function_exists( 'ag_seo_pages_defs' ) ) {
	function ag_seo_pages_defs() {
		$audit = home_url( '/tester-mon-site' );
		$expr  = home_url( '/sites-express' );
		$ctc   = home_url( '/contact' );
		return array(
			'creation-site-internet-nantes' => array(
				'title'   => 'Création de site internet à Nantes — Alliance Groupe',
				'content' =>
					"<h1>Création de site internet à Nantes</h1>\n"
					. "<p><strong>Alliance Groupe</strong> est un studio web <strong>et cybersécurité</strong> basé à Nantes. Nous créons des sites internet professionnels, rapides et <strong>sécurisés dès la conception</strong> pour les artisans, commerçants, professions libérales et PME de Nantes et de toute la Loire-Atlantique.</p>\n"
					. "<h2>Un site qui inspire confiance — et qui est protégé</h2>\n"
					. "<p>La plupart des agences livrent un site joli. Nous livrons un site joli <em>et</em> sécurisé : HTTPS, formulaires protégés, conformité RGPD, sauvegardes et maintenance. C'est notre différence — on connaît les sites web ET la sécurité informatique.</p>\n"
					. "<h2>Nos offres de création de site</h2>\n<ul>"
					. "<li><strong>Site Express — 490 €</strong> : vitrine pro prête vite, idéale pour démarrer.</li>"
					. "<li><strong>Site Pro — 890 €</strong> : design sur-mesure, plusieurs pages, SEO de base.</li>"
					. "<li><strong>Site sur-mesure — 1 490 €</strong> : projet complet, fonctionnalités avancées.</li>"
					. "<li><strong>Maintenance — 29 à 99 €/mois</strong> : mises à jour, sécurité, sauvegardes, tranquillité.</li></ul>\n"
					. "<p><a href=\"" . esc_url( $expr ) . "\">Voir les offres Sites Express &rarr;</a></p>\n"
					. "<h2>Référencement local à Nantes inclus</h2>\n"
					. "<p>Un beau site invisible ne sert à rien. Nous optimisons votre site pour Google (SEO technique, contenu, SEO local Google Business Profile) afin que vos clients de Nantes vous trouvent.</p>\n"
					. "<h2>Zones d'intervention</h2>\n"
					. "<p>Nantes, Saint-Herblain, Rezé, Saint-Nazaire, Vertou, Carquefou, Orvault — et partout en France à distance.</p>\n"
					. "<h2>Testez gratuitement votre site actuel</h2>\n"
					. "<p>Vous avez déjà un site ? Obtenez en 1 minute un <strong>diagnostic gratuit</strong> (score sur 100 + points à corriger), sans engagement.</p>\n"
					. "<p><a href=\"" . esc_url( $audit ) . "\">🔍 Tester mon site gratuitement &rarr;</a> &nbsp; <a href=\"" . esc_url( $ctc ) . "\">Demander un devis &rarr;</a></p>\n",
				'geo'     => array( 'Nantes', 'Pays de la Loire', 47.2184, -1.5536 ),
			),
			'securite-informatique-pme-nantes' => array(
				'title'   => 'Sécurité informatique & conformité NIS2 pour PME — Nantes',
				'content' =>
					"<h1>Sécurité informatique pour PME à Nantes</h1>\n"
					. "<p>38 % des PME françaises ont déjà subi une cyberattaque. <strong>Alliance Groupe</strong> protège les TPE/PME de Nantes : audit de sécurité de votre site et de vos outils, mise en conformité RGPD, et accompagnement vers la directive <strong>NIS2</strong>.</p>\n"
					. "<h2>NIS2 : êtes-vous concerné ? (échéance 2026)</h2>\n"
					. "<p>La directive européenne <strong>NIS2</strong> élargit fortement le nombre d'entreprises devant prouver un niveau de cybersécurité minimal. Beaucoup de PME et de sous-traitants sont concernés sans le savoir, et les cyber-assureurs exigent désormais un audit récent.</p>\n"
					. "<ul><li>Diagnostic de votre exposition</li><li>Plan de mise en conformité concret et priorisé</li><li>Sécurisation de votre site, vos formulaires et vos données</li></ul>\n"
					. "<h2>Notre approche en 3 niveaux</h2>\n<ul>"
					. "<li><strong>Test gratuit</strong> : analyse publique non-intrusive de votre site (score /100 + failles).</li>"
					. "<li><strong>Audit approfondi</strong> : examen complet, rapport et recommandations.</li>"
					. "<li><strong>Test d'intrusion (pentest)</strong> : sur mandat écrit uniquement.</li></ul>\n"
					. "<h2>Pourquoi Alliance Groupe</h2>\n"
					. "<p>Nous sommes à la fois créateurs de sites web et spécialistes sécurité : on comprend votre site de l'intérieur. Un seul interlocuteur, à Nantes, du diagnostic à la protection.</p>\n"
					. "<h2>Commencez par le diagnostic gratuit</h2>\n"
					. "<p><a href=\"" . esc_url( $audit ) . "\">🔍 Tester la sécurité de mon site (gratuit) &rarr;</a> &nbsp; <a href=\"" . esc_url( $ctc ) . "\">Parler à un expert &rarr;</a></p>\n",
				'geo'     => array( 'Nantes', 'Pays de la Loire', 47.2184, -1.5536 ),
			),
			'creation-site-internet-saint-nazaire' => array(
				'title'   => 'Création de site internet à Saint-Nazaire — Alliance Groupe',
				'content' =>
					"<h1>Création de site internet à Saint-Nazaire</h1>\n"
					. "<p>De la zone industrialo-portuaire aux commerces du centre et aux PME du bassin nazairien, <strong>Alliance Groupe</strong> crée des sites internet professionnels et <strong>sécurisés</strong> pour les entreprises de Saint-Nazaire et de la presqu'île (Pornichet, La Baule, Trignac, Montoir-de-Bretagne).</p>\n"
					. "<h2>Des sites adaptés au tissu local nazairien</h2>\n"
					. "<p>Sous-traitants industriels (naval, aéronautique), artisans, restaurants et commerces touristiques : chaque secteur a ses besoins. Nous concevons un site clair, rapide et qui convertit vos visiteurs en clients — avec la sécurité intégrée dès le départ.</p>\n"
					. "<h2>Nos offres</h2>\n<ul>"
					. "<li><strong>Site Express — 490 €</strong> · <strong>Site Pro — 890 €</strong> · <strong>Sur-mesure — 1 490 €</strong></li>"
					. "<li><strong>Maintenance 29–99 €/mois</strong> : sécurité, sauvegardes, mises à jour.</li></ul>\n"
					. "<p><a href=\"" . esc_url( $expr ) . "\">Voir les offres &rarr;</a></p>\n"
					. "<h2>Visible sur Google à Saint-Nazaire</h2>\n"
					. "<p>SEO local (fiche Google Business Profile, contenu géolocalisé) pour ressortir quand un client de Saint-Nazaire cherche votre activité.</p>\n"
					. "<h2>Diagnostic gratuit de votre site</h2>\n"
					. "<p><a href=\"" . esc_url( $audit ) . "\">🔍 Tester mon site gratuitement &rarr;</a> &nbsp; <a href=\"" . esc_url( $ctc ) . "\">Demander un devis &rarr;</a></p>\n"
					. "<p style=\"font-size:.9em\">Voir aussi : <a href=\"" . esc_url( home_url( '/creation-site-internet-nantes' ) ) . "\">création de site à Nantes</a>.</p>\n",
				'geo'     => array( 'Saint-Nazaire', 'Pays de la Loire', 47.2735, -2.2135 ),
			),
		);
	}
}

/* Création idempotente des pages piliers. */
add_action( 'admin_init', function () {
	if ( (int) get_option( 'ag_seo_pages_done', 0 ) >= AG_SEO_PAGES_VER ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	foreach ( ag_seo_pages_defs() as $slug => $def ) {
		if ( get_page_by_path( $slug ) ) continue;
		wp_insert_post( array(
			'post_title'   => $def['title'],
			'post_name'    => $slug,
			'post_content' => $def['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id() ?: 1,
		) );
	}
	update_option( 'ag_seo_pages_done', AG_SEO_PAGES_VER );
} );

/* Schema LocalBusiness sur ces pages piliers (Google : signal local fort). */
add_action( 'wp_head', function () {
	if ( ! is_page() ) return;
	$slug = get_post_field( 'post_name' );
	$defs = ag_seo_pages_defs();
	if ( ! isset( $defs[ $slug ]['geo'] ) ) return;
	list( $loc, $region, $lat, $lng ) = $defs[ $slug ]['geo'];
	$lb = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ProfessionalService',
		'name'        => 'Alliance Groupe — ' . $loc,
		'url'         => get_permalink(),
		'image'       => get_stylesheet_directory_uri() . '/assets/images/pwa/icon-512.png',
		'telephone'   => '+33744829516',
		'email'       => 'contact@alliancegroupe-inc.com',
		'priceRange'  => '€€',
		'areaServed'  => array( $loc, 'Loire-Atlantique', 'France' ),
		'address'     => array( '@type' => 'PostalAddress', 'addressLocality' => $loc, 'addressRegion' => $region, 'addressCountry' => 'FR' ),
		'geo'         => array( '@type' => 'GeoCoordinates', 'latitude' => $lat, 'longitude' => $lng ),
		'sameAs'      => array_values( array_filter( array( get_option( 'ag_social_facebook', '' ), get_option( 'ag_social_instagram', '' ), get_option( 'ag_social_linkedin', '' ) ) ) ),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $lb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

/* Fil d'Ariane (BreadcrumbList) — si aucun plugin SEO (Yoast le fait déjà). */
add_action( 'wp_head', function () {
	if ( function_exists( 'ag_seo_plugin_active' ) && ag_seo_plugin_active() ) return;
	if ( is_front_page() || ! is_singular() ) return;
	$items = array(
		array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => home_url( '/' ) ),
		array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() ),
	);
	$bc = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	echo '<script type="application/ld+json">' . wp_json_encode( $bc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

/* Schema Service sur les pages /service-* (offres + zone). */
add_action( 'wp_head', function () {
	if ( ! is_page() ) return;
	$slug = get_post_field( 'post_name' );
	if ( 0 !== strpos( (string) $slug, 'service-' ) && ! in_array( $slug, array( 'creation-site-internet-nantes', 'securite-informatique-pme-nantes' ), true ) ) return;
	$svc = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'serviceType' => get_the_title(),
		'provider'    => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ?: 'Alliance Groupe', 'url' => home_url( '/' ) ),
		'areaServed'  => array( 'Nantes', 'Loire-Atlantique', 'France' ),
		'url'         => get_permalink(),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $svc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

/* Bloc de liens internes discret (jus SEO vers les piliers) — front public. */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	// Pas dans les apps installées (clutter), pas si déjà sur une de ces pages.
	$links = array();
	foreach ( array(
		'creation-site-internet-nantes'    => 'Création de site internet à Nantes',
		'securite-informatique-pme-nantes' => 'Sécurité informatique PME (NIS2)',
	) as $s => $lbl ) {
		$p = get_page_by_path( $s );
		if ( $p && 'publish' === $p->post_status && ( ! is_page() || get_post_field( 'post_name' ) !== $s ) ) {
			$links[] = '<a href="' . esc_url( get_permalink( $p ) ) . '" style="color:#9a9aa5;text-decoration:none;border-bottom:1px dotted #555;">' . esc_html( $lbl ) . '</a>';
		}
	}
	if ( empty( $links ) ) return;
	echo '<div style="max-width:1100px;margin:0 auto;padding:14px 18px;text-align:center;font-family:-apple-system,Arial,sans-serif;font-size:12.5px;color:#6f6f7a;">';
	echo '<span style="opacity:.7">Nos expertises à Nantes :</span> ' . implode( ' &nbsp;·&nbsp; ', $links );
	echo '</div>';
}, 50 );
