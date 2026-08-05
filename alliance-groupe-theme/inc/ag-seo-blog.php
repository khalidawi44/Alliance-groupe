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

define( 'AG_SEO_BLOG_VER', 2 );

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
		$seo   = home_url( '/service-seo' );
		$avocat = home_url( '/wordpress-avocat' );
		$resto  = home_url( '/wordpress-restaurant' );
		$artisan = home_url( '/wordpress-artisan' );
		$coach  = home_url( '/wordpress-coach' );
		$compo  = home_url( '/composants' );
		$meilleure = home_url( '/meilleure-agence-web-nantes' );
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

			// ── Lot 2 : longue traîne locale + métiers (gagnable, converti) ──
			'creation-site-avocat-nantes' => array(
				'title'   => 'Créer le site internet d\'un cabinet d\'avocat à Nantes (guide 2026)',
				'cat'     => 'Métiers',
				'excerpt' => 'Site internet pour avocat à Nantes : ce qu\'exige la déontologie (RIN/CNB), les sections indispensables, et un template prêt à l\'emploi.',
				'content' =>
					"<p><strong>En bref :</strong> le site d'un avocat doit inspirer confiance ET respecter la déontologie (RIN, CNB). Voici les règles, les sections qui convertissent, et un modèle prêt à l'emploi.</p>\n"
					. "<h2>Ce qu'impose la déontologie</h2>\n<p>Pas de témoignages clients ni de comparaison tarifaire trompeuse, mention du barreau, transparence des honoraires, respect du secret professionnel. Un site d'avocat sobre et clair vaut mieux qu'un site tape-à-l'œil non conforme.</p>\n"
					. "<h2>Les sections indispensables</h2>\n<ul><li>Domaines d'expertise clairs (droit du travail, famille, affaires…).</li><li>Présentation du cabinet et du barreau d'inscription.</li><li>Honoraires transparents + prise de rendez-vous.</li><li>Mentions légales, RGPD, hébergement en UE.</li></ul>\n"
					. "<h2>Un modèle prêt à l'emploi</h2>\n<p>Nous avons conçu un <a href=\"" . esc_url( $avocat ) . "\">template WordPress pour avocat</a>, sobre et conforme, personnalisable en quelques minutes. Pour un site sur-mesure, voyez notre offre de <a href=\"" . esc_url( $web ) . "\">création de site à Nantes</a>.</p>\n"
					. "<p><a href=\"" . esc_url( $ctc ) . "\">Demander un devis gratuit &rarr;</a> · <a href=\"" . esc_url( $audit ) . "\">tester un site existant</a>.</p>\n",
			),
			'site-internet-restaurant-nantes' => array(
				'title'   => 'Site internet pour restaurant à Nantes : ce qui fait venir des clients',
				'cat'     => 'Métiers',
				'excerpt' => 'Menu en ligne, réservation, avis, référencement local : ce qui transforme le site d\'un restaurant en machine à réservations.',
				'content' =>
					"<p><strong>En bref :</strong> un site de restaurant sert à une chose — <strong>remplir la salle</strong>. Menu à jour, réservation facile et présence sur Google local font 90 % du résultat.</p>\n"
					. "<h2>Les 4 leviers qui comptent</h2>\n<ul><li><strong>Menu en ligne</strong> lisible sur mobile (pas un PDF illisible).</li><li><strong>Réservation</strong> en 2 clics.</li><li><strong>Photos</strong> soignées + avis Google.</li><li><strong>SEO local</strong> : « restaurant + quartier » doit vous trouver.</li></ul>\n"
					. "<h2>Le piège du « tout réseaux sociaux »</h2>\n<p>Instagram ne remplace pas un site : vous ne maîtrisez ni le référencement, ni les données, ni l'algorithme. Un site + une fiche Google, c'est vous qui gardez le contrôle.</p>\n"
					. "<h2>Démarrer vite</h2>\n<p>Notre <a href=\"" . esc_url( $resto ) . "\">template WordPress pour restaurant</a> est prêt à l'emploi. Besoin de sur-mesure ? Voyez la <a href=\"" . esc_url( $web ) . "\">création de site à Nantes</a> ou <a href=\"" . esc_url( $ctc ) . "\">demandez un devis</a>.</p>\n",
			),
			'site-internet-artisan-prix' => array(
				'title'   => 'Site internet pour artisan : combien ça coûte et ce qui est utile',
				'cat'     => 'Métiers',
				'excerpt' => 'Menuisier, plombier, électricien : le vrai prix d\'un site d\'artisan, ce qui apporte des devis, et ce qui ne sert à rien.',
				'content' =>
					"<p><strong>En bref :</strong> pour un artisan, un site sert à <strong>obtenir des demandes de devis</strong>. Pas besoin d'usine à gaz : une vitrine claire, des photos de chantiers et un formulaire suffisent souvent.</p>\n"
					. "<h2>Ce qui apporte des devis</h2>\n<ul><li>Zone d'intervention + spécialités visibles tout de suite.</li><li>Galerie de réalisations (les photos rassurent).</li><li>Bouton « Demander un devis » et numéro cliquable.</li><li>Référencement local pour « métier + ville ».</li></ul>\n"
					. "<h2>Le prix réel</h2>\n<p>Une vitrine d'artisan démarre autour de <strong>490–890 €</strong>, maintenance ~29 €/mois. Méfiez-vous du gratuit : un site non maintenu se fait pirater ou disparaît de Google. Détails dans notre article <a href=\"" . esc_url( home_url( '/prix-site-internet-nantes-2026' ) ) . "\">prix d'un site à Nantes</a>.</p>\n"
					. "<h2>Prêt à démarrer</h2>\n<p>Voyez le <a href=\"" . esc_url( $artisan ) . "\">template WordPress pour artisan</a> ou <a href=\"" . esc_url( $ctc ) . "\">demandez un devis gratuit</a>.</p>\n",
			),
			'template-wordpress-ou-sur-mesure' => array(
				'title'   => 'Template WordPress ou site sur-mesure : comment choisir ?',
				'cat'     => 'Conseils Digital',
				'excerpt' => 'Template prêt à l\'emploi ou site sur-mesure ? Les vrais critères pour choisir sans se tromper (budget, délai, image, évolutivité).',
				'content' =>
					"<p><strong>En bref :</strong> le template va vite et coûte moins cher ; le sur-mesure offre une image unique et des fonctions spécifiques. Le bon choix dépend de vos objectifs, pas de la mode.</p>\n"
					. "<h2>Quand choisir un template</h2>\n<p>Budget serré, besoin d'être en ligne vite, activité « standard » (vitrine, prise de RDV). Un bon <a href=\"" . esc_url( $web ) . "\">template métier</a> bien configuré fait largement le job.</p>\n"
					. "<h2>Quand passer au sur-mesure</h2>\n<p>Image de marque forte, fonctionnalités spécifiques (réservation complexe, espace client, e-commerce), volume et évolutivité. Là, le sur-mesure se rentabilise.</p>\n"
					. "<h2>Astuce de pro</h2>\n<p>On peut démarrer avec un template puis évoluer. Et pour donner du caractère à un template, piochez dans notre <a href=\"" . esc_url( $compo ) . "\">bibliothèque de composants gratuits</a> (boutons, cartes, animations à copier).</p>\n"
					. "<p><a href=\"" . esc_url( $ctc ) . "\">On en parle ? Devis gratuit &rarr;</a></p>\n",
			),
			'pourquoi-mon-site-napparait-pas-google' => array(
				'title'   => 'Pourquoi mon site n\'apparaît pas sur Google (et comment corriger)',
				'cat'     => 'SEO',
				'excerpt' => 'Site invisible sur Google ? Les 6 causes les plus fréquentes et les correctifs concrets pour enfin remonter dans les résultats.',
				'content' =>
					"<p><strong>En bref :</strong> un site invisible, c'est presque toujours l'une de ces 6 causes. Bonne nouvelle : la plupart se corrigent vite.</p>\n"
					. "<h2>Les 6 causes fréquentes</h2>\n<ul><li><strong>Site trop récent</strong> ou pas encore indexé (pas de sitemap soumis).</li><li><strong>Contenu trop mince</strong> ou dupliqué.</li><li><strong>Mots-clés trop concurrentiels</strong> (viser d'abord la longue traîne locale).</li><li><strong>Aucun backlink</strong> (pas d'autorité).</li><li><strong>Problème technique</strong> (lenteur, mobile, balises absentes).</li><li><strong>Pas de fiche Google Business</strong> pour le local.</li></ul>\n"
					. "<h2>Le plan pour remonter</h2>\n<p>Soumettez votre sitemap (Search Console), créez des pages ciblées « métier + ville », gagnez des liens, et optimisez la vitesse. Détail dans notre <a href=\"" . esc_url( $seo ) . "\">accompagnement SEO à Nantes</a>.</p>\n"
					. "<h2>Commencez par un diagnostic</h2>\n<p><a href=\"" . esc_url( $audit ) . "\">Testez votre site gratuitement</a> (score /100) ou <a href=\"" . esc_url( $ctc ) . "\">demandez un audit SEO</a>.</p>\n",
			),
			'refonte-site-internet-nantes' => array(
				'title'   => 'Refonte de site internet à Nantes : quand (et pourquoi) refaire son site',
				'cat'     => 'Création Web',
				'excerpt' => 'Site vieillissant, lent ou pas mobile ? Les signes qu\'il faut une refonte, et comment la réussir sans perdre son référencement.',
				'content' =>
					"<p><strong>En bref :</strong> une refonte se justifie quand le site ne convertit plus, n'est pas mobile, ou n'est plus sécurisé. Bien menée, elle fait gagner en clients ET en référencement.</p>\n"
					. "<h2>Les signes qu'il faut refondre</h2>\n<ul><li>Design daté, pas responsive (mauvais sur mobile).</li><li>Site lent (les visiteurs partent avant 3 s).</li><li>Non sécurisé / non maintenu.</li><li>Invisible sur Google, aucune demande entrante.</li></ul>\n"
					. "<h2>Réussir sa refonte sans perdre son SEO</h2>\n<p>Conservez vos URLs (ou faites des redirections 301), gardez le contenu qui marche, améliorez la vitesse et la structure. Une refonte bâclée peut faire <em>chuter</em> le référencement — d'où l'importance d'un pro.</p>\n"
					. "<h2>On regarde votre site ensemble ?</h2>\n<p><a href=\"" . esc_url( $audit ) . "\">Testez-le gratuitement</a>, puis voyez notre <a href=\"" . esc_url( $web ) . "\">offre de création/refonte à Nantes</a>. <a href=\"" . esc_url( $ctc ) . "\">Demander un devis &rarr;</a></p>\n",
			),
			'combien-de-temps-creer-site-web' => array(
				'title'   => 'Combien de temps pour créer un site web ? (délais réels 2026)',
				'cat'     => 'Conseils Digital',
				'excerpt' => 'Délai réel pour créer un site : d\'une vitrine express en quelques jours à un sur-mesure en quelques semaines. Ce qui accélère ou ralentit.',
				'content' =>
					"<p><strong>En bref :</strong> une vitrine express peut être en ligne en <strong>quelques jours</strong> ; un site sur-mesure prend <strong>2 à 6 semaines</strong>. Le contenu (textes, photos) est souvent ce qui prend le plus de temps.</p>\n"
					. "<h2>Les fourchettes de délai</h2>\n<ul><li><strong>Template configuré</strong> : quelques jours.</li><li><strong>Site pro multi-pages</strong> : 1 à 2 semaines.</li><li><strong>Sur-mesure / e-commerce</strong> : 3 à 6 semaines.</li></ul>\n"
					. "<h2>Ce qui accélère</h2>\n<p>Avoir ses textes et photos prêts, un objectif clair, un seul interlocuteur côté client. Un <a href=\"" . esc_url( $web ) . "\">studio qui gère tout de A à Z</a> évite les allers-retours qui font traîner un projet.</p>\n"
					. "<h2>Envie d'aller vite ?</h2>\n<p>Un <a href=\"" . esc_url( $avocat ) . "\">template métier</a> vous met en ligne très vite. <a href=\"" . esc_url( $ctc ) . "\">Parlons de votre projet &rarr;</a></p>\n",
			),
			'boutons-composants-css-gratuits' => array(
				'title'   => 'Boutons & composants CSS gratuits à copier (bibliothèque)',
				'cat'     => 'Création Web',
				'excerpt' => 'Une bibliothèque de composants web gratuits — boutons, cartes, loaders, fonds — configurables et à copier/télécharger. HTML + CSS prêts.',
				'content' =>
					"<p><strong>En bref :</strong> nous avons ouvert une <a href=\"" . esc_url( $compo ) . "\">bibliothèque de composants web gratuits</a> : boutons animés, cartes, loaders, badges, fonds… Configurez la couleur, copiez le HTML/CSS ou téléchargez le ZIP.</p>\n"
					. "<h2>À qui ça sert</h2>\n<p>Aux développeurs, intégrateurs et curieux qui veulent un composant propre en 30 secondes, sans réinventer la roue. Chaque composant est <strong>configurable</strong> (couleur, rayon, taille) et livré prêt à coller.</p>\n"
					. "<h2>Vous êtes codeur ?</h2>\n<p>Proposez vos créations : les meilleures entrent au classement <strong>« Créateur du mois »</strong>. C'est gratuit, et c'est une vitrine pour votre travail.</p>\n"
					. "<h2>Explorer</h2>\n<p><a href=\"" . esc_url( $compo ) . "\">Ouvrir la bibliothèque de composants &rarr;</a> — et si vous voulez un site complet, voyez la <a href=\"" . esc_url( $web ) . "\">création de site à Nantes</a>.</p>\n",
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
