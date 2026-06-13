<?php
/**
 * AG SEO Blog — publie automatiquement (idempotent) des articles de blog
 * optimisés SEO/GEO qui ciblent des requêtes informationnelles et drainent du
 * trafic vers les pages piliers et le test gratuit.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_SEO_BLOG_VER', 1 );

if ( ! function_exists( 'ag_seo_blog_cat' ) ) {
	/** Récupère/crée une catégorie et renvoie son ID. */
	function ag_seo_blog_cat( $name ) {
		$t = term_exists( $name, 'category' );
		if ( ! $t ) $t = wp_insert_term( $name, 'category' );
		return ( ! is_wp_error( $t ) && isset( $t['term_id'] ) ) ? (int) $t['term_id'] : 0;
	}
}
if ( ! function_exists( 'ag_seo_blog_defs' ) ) {
	function ag_seo_blog_defs() {
		$audit = home_url( '/tester-mon-site' );
		$cyber = home_url( '/securite-informatique-pme-nantes' );
		$web   = home_url( '/creation-site-internet-nantes' );
		$ctc   = home_url( '/contact' );
		return array(
			'nis2-pme-concernee-2026' => array(
				'title'   => 'NIS2 : votre PME est-elle concernée ? (guide clair 2026)',
				'cat'     => 'Cybersécurité',
				'excerpt' => 'NIS2 en 2026 : qui est concerné, ce qu’il faut faire, et comment vérifier votre exposition en 1 minute. Guide clair pour PME.',
				'content' =>
					"<p><strong>En bref :</strong> la directive européenne <strong>NIS2</strong> impose à beaucoup plus d'entreprises un niveau minimal de cybersécurité. De nombreuses PME et sous-traitants sont concernés <em>sans le savoir</em>. Voici comment savoir si c'est votre cas et quoi faire.</p>\n"
					. "<h2>Qu'est-ce que NIS2 ?</h2>\n<p>NIS2 élargit le périmètre de la réglementation cybersécurité européenne. Elle vise à renforcer la résilience des entreprises face aux cyberattaques, avec des obligations de gestion du risque, de notification d'incident et de responsabilité de la direction.</p>\n"
					. "<h2>Êtes-vous concerné ?</h2>\n<p>Au-delà des grands secteurs « essentiels », de nombreuses ETI et PME (et leurs <strong>sous-traitants</strong>) entrent dans le champ. Si vous travaillez avec de grands donneurs d'ordre, ils vous demanderont des garanties. Les <strong>cyber-assureurs</strong>, eux, exigent déjà un audit récent.</p>\n"
					. "<h2>Les 4 chantiers prioritaires</h2>\n<ul><li>Cartographier vos risques et vos données sensibles.</li><li>Sécuriser le périmètre exposé (site, formulaires, accès).</li><li>Mettre en place sauvegardes + plan de réaction à incident.</li><li>Documenter (la conformité se prouve).</li></ul>\n"
					. "<h2>Par où commencer ? Le diagnostic</h2>\n<p>Inutile de tout faire d'un coup. La première étape est un <strong>diagnostic</strong> de votre exposition. <a href=\"" . esc_url( $audit ) . "\">Testez gratuitement la sécurité de votre site</a> (score /100 + failles), puis voyez notre <a href=\"" . esc_url( $cyber ) . "\">accompagnement sécurité &amp; NIS2 pour PME</a>.</p>\n"
					. "<p><a href=\"" . esc_url( $ctc ) . "\">Parler à un expert &rarr;</a></p>\n",
			),
			'prix-site-internet-nantes-2026' => array(
				'title'   => 'Combien coûte un site internet à Nantes en 2026 ?',
				'cat'     => 'Conseils Digital',
				'excerpt' => 'Prix d’un site internet à Nantes en 2026 : fourchettes réelles, ce qui fait varier le tarif, et comment éviter les mauvaises surprises.',
				'content' =>
					"<p><strong>En bref :</strong> un site vitrine professionnel se situe généralement entre <strong>490 € et 2 000 €</strong> ; un projet sur-mesure ou e-commerce au-delà. Le bon prix dépend surtout de vos objectifs.</p>\n"
					. "<h2>Les fourchettes de prix</h2>\n<ul><li><strong>490 €</strong> — vitrine express, idéale pour démarrer vite.</li><li><strong>890 €</strong> — site pro multi-pages, design soigné, SEO de base.</li><li><strong>1 490 € et +</strong> — sur-mesure, fonctionnalités avancées, e-commerce.</li><li><strong>29–99 €/mois</strong> — maintenance (sécurité, sauvegardes, mises à jour).</li></ul>\n"
					. "<h2>Ce qui fait varier le prix</h2>\n<p>Nombre de pages, design sur-mesure vs template, fonctionnalités (réservation, paiement), rédaction du contenu, SEO, et surtout la <strong>sécurité et la maintenance</strong> — trop souvent oubliées dans les devis low-cost.</p>\n"
					. "<h2>Attention au « pas cher » qui coûte cher</h2>\n<p>Un site non maintenu se fait pirater ; un site invisible sur Google ne rapporte rien. Le vrai coût, c'est le résultat. Chez <a href=\"" . esc_url( $web ) . "\">Alliance Groupe à Nantes</a>, la sécurité est incluse dès la conception.</p>\n"
					. "<h2>Obtenez un devis clair</h2>\n<p><a href=\"" . esc_url( $ctc ) . "\">Demander un devis gratuit &rarr;</a> ou <a href=\"" . esc_url( $audit ) . "\">tester votre site actuel gratuitement</a>.</p>\n",
			),
			'site-web-securise-comment-savoir' => array(
				'title'   => 'Comment savoir si votre site web est sécurisé ? (5 signes)',
				'cat'     => 'Cybersécurité',
				'excerpt' => '5 signes qui montrent que votre site web est (ou non) sécurisé, et un test gratuit pour obtenir un score sur 100 en 1 minute.',
				'content' =>
					"<p><strong>En bref :</strong> voici 5 vérifications rapides. Pour un diagnostic complet, un <a href=\"" . esc_url( $audit ) . "\">test gratuit</a> vous donne un score /100 et la liste des failles.</p>\n"
					. "<h2>1. Le cadenas HTTPS</h2>\n<p>Votre adresse commence par <code>https://</code> ? Sans HTTPS, les données transitent en clair et Google vous pénalise.</p>\n"
					. "<h2>2. Les mises à jour</h2>\n<p>WordPress, thème et extensions à jour ? Les failles connues non corrigées sont la 1re porte d'entrée des pirates.</p>\n"
					. "<h2>3. Le formulaire de contact</h2>\n<p>Où vont les messages ? Sont-ils protégés (anti-spam, chiffrement, conservation conforme RGPD) ?</p>\n"
					. "<h2>4. Les sauvegardes</h2>\n<p>Une sauvegarde récente et testée permet de tout remettre d'aplomb après un incident. Sans elle, une attaque peut être fatale.</p>\n"
					. "<h2>5. L'exposition publique</h2>\n<p>Page de connexion exposée, fichiers sensibles accessibles, versions visibles… autant d'indices qu'un pirate cherche en premier.</p>\n"
					. "<h2>Le plus simple : testez</h2>\n<p><a href=\"" . esc_url( $audit ) . "\">🔍 Tester la sécurité de mon site (gratuit) &rarr;</a> — puis découvrez notre <a href=\"" . esc_url( $cyber ) . "\">accompagnement sécurité PME</a>.</p>\n",
			),
		);
	}
}

add_action( 'admin_init', function () {
	if ( (int) get_option( 'ag_seo_blog_done', 0 ) >= AG_SEO_BLOG_VER ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	foreach ( ag_seo_blog_defs() as $slug => $def ) {
		if ( get_page_by_path( $slug, OBJECT, 'post' ) ) continue;
		$cat = ag_seo_blog_cat( $def['cat'] );
		wp_insert_post( array(
			'post_title'    => $def['title'],
			'post_name'     => $slug,
			'post_content'  => $def['content'],
			'post_excerpt'  => $def['excerpt'],
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_author'   => get_current_user_id() ?: 1,
			'post_category' => $cat ? array( $cat ) : array(),
		) );
	}
	update_option( 'ag_seo_blog_done', AG_SEO_BLOG_VER );
} );
