<?php
/**
 * Données détaillées par service — alimente template-parts/service-detail.php.
 *
 * Centralisé ici pour éviter la duplication entre les 6 page-service-*.php.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function ag_get_service_detail( $slug ) {
	$data = array(

		'web' => array(
			'slug'  => 'web',
			'title' => 'Création Web',
			'intro' => 'Un site qui convertit, pas juste qui affiche. Conçu pour générer des leads pendant que vous dormez — adapté à votre métier, optimisé mobile, prêt pour le SEO local.',
			'deliverables' => array(
				'<strong>Site WordPress sur-mesure</strong> avec design unique adapté à votre identité',
				'<strong>5 à 20 pages</strong> entièrement rédigées avec votre voix (pas du Lorem Ipsum)',
				'<strong>Optimisation mobile</strong> au pixel près (90%+ du trafic vient du mobile)',
				'<strong>Performance Google Core Web Vitals</strong> : score 90+/100 obligatoire',
				'<strong>Formulaires intelligents</strong> avec scoring lead + intégration CRM',
				'<strong>Page de remerciement</strong> avec tracking conversion Google Ads/Meta',
				'<strong>Sécurité</strong> : HTTPS, anti-spam, sauvegarde quotidienne automatique',
				'<strong>Formation Live</strong> 1h pour gérer votre site en autonomie',
			),
			'process' => array(
				array( 'title' => 'Audit & cadrage',          'desc' => 'Visio 1h pour comprendre votre métier, votre client idéal et vos objectifs business. Livrable : cahier des charges + budget précis.', 'duration' => '3-5 jours' ),
				array( 'title' => 'Wireframes & maquettes',   'desc' => 'On dessine la structure puis le design. Validation par étapes pour éviter les surprises en fin de projet.', 'duration' => '1-2 semaines' ),
				array( 'title' => 'Développement WordPress',  'desc' => 'On code le thème custom sur la base de votre maquette validée. Tests sur 5 navigateurs + 3 tailles d\'écran.', 'duration' => '2-4 semaines' ),
				array( 'title' => 'Rédaction & contenus',     'desc' => 'On intègre vos textes (ou on les rédige en supplément). Images optimisées WebP, structure SEO friendly.', 'duration' => '1 semaine' ),
				array( 'title' => 'Mise en ligne & formation','desc' => 'Migration sur votre hébergeur, certificat HTTPS, redirections 301 si refonte, formation 1h en visio.', 'duration' => '2-3 jours' ),
			),
			'pricing' => array(
				'from'     => 1500, 'to' => 15000,
				'duration' => '4 à 12 semaines selon ambition',
				'includes' => array( 'Domaine 1 an offert', 'Hébergement 12 mois inclus', 'Formation 1h', 'Support 30 jours post-livraison', 'Refonte SEO si site existant' ),
			),
			'faqs' => array(
				array( 'q' => 'Combien de pages pour démarrer ?',     'a' => 'On recommande 5 pages minimum : Accueil, Services, À propos, Réalisations, Contact. Un site coach ou artisan peut très bien démarrer avec ça et grandir ensuite.' ),
				array( 'q' => 'Hébergement inclus ?',                  'a' => 'Oui — on offre 12 mois d\'hébergement chez o2switch (France, énergie verte). À l\'année suivante, vous payez ~6€/mois directement à eux. Vous restez propriétaire.' ),
				array( 'q' => 'Combien de modifications incluses ?',   'a' => '3 cycles de retours sur les maquettes + 2 cycles sur le développement. Au-delà, c\'est facturé à l\'heure (80€/h). En pratique, c\'est rarement nécessaire.' ),
				array( 'q' => 'Vous gérez la rédaction ?',             'a' => 'On peut. Forfait rédaction +800€ pour 8 pages avec interview client + recherches SEO. Sinon vous pouvez fournir vos textes (template fourni).' ),
				array( 'q' => 'Et après la livraison ?',               'a' => 'Support 30 jours inclus. Ensuite : forfait maintenance optionnel à 50€/mois (mises à jour WP + sauvegardes + 1h modif/mois).' ),
			),
		),

		'ia' => array(
			'slug'  => 'ia',
			'title' => 'IA & Automatisation',
			'intro' => 'Automatisez ce qui vous prend du temps : tri d\'emails, qualification de leads, génération de devis, support client 24/7. Notre approche : <strong>l\'IA libère, elle ne remplace pas.</strong>',
			'deliverables' => array(
				'<strong>Chatbot sur-mesure</strong> entraîné sur votre activité (FAQ, prise de RDV, devis simples)',
				'<strong>Automatisations Make.com / Zapier</strong> intégrées à vos outils existants',
				'<strong>Workflow lead → CRM</strong> avec scoring automatique et alertes WhatsApp/email',
				'<strong>Générateur de devis IA</strong> qui rédige les propositions commerciales en 1 clic',
				'<strong>Agent vocal téléphone</strong> qui prend les RDV en dehors de vos heures (option)',
				'<strong>Tri d\'emails et tickets</strong> automatique (urgent/normal/spam)',
				'<strong>Génération de contenu SEO</strong> pour votre blog (10 articles/mois automatiques)',
				'<strong>Documentation</strong> et formation pour reprendre en interne',
			),
			'process' => array(
				array( 'title' => 'Cartographie des tâches',     'desc' => 'On liste vos tâches répétitives chronophages avec leurs volumes et coûts. Priorisation ROI.', 'duration' => '1 semaine' ),
				array( 'title' => 'POC sur la tâche n°1',         'desc' => 'On automatise UNE tâche en preuve de concept pour valider l\'approche avant d\'investir plus.', 'duration' => '2 semaines' ),
				array( 'title' => 'Industrialisation',            'desc' => 'On déploie les autres automatisations en parallèle, avec tableau de bord de monitoring.', 'duration' => '3-6 semaines' ),
				array( 'title' => 'Formation & autonomie',        'desc' => 'Vous reprenez la main. Documentation Notion, vidéos tutos, ateliers en visio.', 'duration' => '1 semaine' ),
			),
			'pricing' => array(
				'from'     => 2500, 'to' => 20000,
				'duration' => '4 à 10 semaines',
				'includes' => array( 'Audit gratuit 1h', 'POC sur 1 tâche en 2 semaines', 'Documentation complète', 'Formation équipe 2h', 'Support 60 jours' ),
			),
			'faqs' => array(
				array( 'q' => 'Quelle IA vous utilisez ?',                'a' => 'On choisit en fonction du besoin : OpenAI (GPT-4o, GPT-5), Anthropic (Claude), ou modèles open source hébergés en Europe (Mistral) pour les données sensibles. Vos données ne servent jamais à entraîner les modèles publics.' ),
				array( 'q' => 'Mes données vont être partagées ?',         'a' => 'Non. On signe un accord de confidentialité, on désactive le training côté provider, et pour les données ultra-sensibles on déploie sur des modèles auto-hébergés (Mistral on premise, Llama).' ),
				array( 'q' => 'Combien ça coûte par mois après ?',         'a' => 'L\'API IA coûte 20 à 200€/mois selon volume. On vous donne une estimation précise dès le cadrage. À comparer aux 2000€+ d\'un employé sur ces tâches.' ),
				array( 'q' => 'Et si l\'IA fait une erreur ?',             'a' => 'On met en place du human-in-the-loop sur les actions critiques (validation manuelle avant envoi). Les automatisations bas-enjeu (tri mail, brouillon devis) s\'auto-corrigent.' ),
				array( 'q' => 'Vous restez disponibles après ?',            'a' => 'Oui, forfait maintenance IA 150€/mois : monitoring, mises à jour, ajustements prompts. Optionnel.' ),
			),
		),

		'seo' => array(
			'slug'  => 'seo',
			'title' => 'SEO — Référencement naturel',
			'intro' => 'Apparaître en première page Google sur vos vraies requêtes business. Pas du SEO chapeau noir : du contenu utile, du maillage interne propre, des backlinks naturels.',
			'deliverables' => array(
				'<strong>Audit technique complet</strong> : Core Web Vitals, indexation, structure HTML, mobile',
				'<strong>Étude sémantique</strong> : 50-200 mots-clés priorisés avec volume + difficulté',
				'<strong>Optimisation on-page</strong> de toutes vos pages (titles, H1, meta, alt, schema)',
				'<strong>Stratégie de contenu</strong> sur 12 mois avec calendrier éditorial',
				'<strong>10 à 30 backlinks</strong> qualitatifs (presse, partenaires, annuaires métier)',
				'<strong>Pages SEO locales</strong> par ville/zone d\'intervention (effet immédiat)',
				'<strong>Google Business Profile</strong> optimisé + multilocalisation si applicable',
				'<strong>Tableau de bord</strong> Looker Studio avec suivi positions/trafic/conversions',
			),
			'process' => array(
				array( 'title' => 'Audit + benchmark concurrents', 'desc' => 'État des lieux complet de votre site et analyse des 5 concurrents qui vous dépassent. Identification des opportunités quick-win.', 'duration' => '2 semaines' ),
				array( 'title' => 'Optimisation on-page',          'desc' => 'Réécriture des balises, contenus, structure interne. Première vague de quick-wins visible en 4-8 semaines.', 'duration' => '3-4 semaines' ),
				array( 'title' => 'Production de contenu',         'desc' => '4 à 12 articles/mois ciblés sur les mots-clés à fort potentiel. Rédigés par humains, validés par vous.', 'duration' => 'récurrent' ),
				array( 'title' => 'Netlinking propre',             'desc' => 'Acquisition de 2-5 backlinks/mois (presse spécialisée, partenariats, contenu sponsorisé chez gros sites).', 'duration' => 'récurrent' ),
			),
			'pricing' => array(
				'from'     => 1200, 'to' => 8000,
				'duration' => 'Audit one-shot 2 sem. + suivi mensuel optionnel',
				'includes' => array( 'Audit technique', 'Étude sémantique', 'Roadmap 12 mois', 'Optimisation on-page', 'Suivi positions Looker Studio' ),
			),
			'faqs' => array(
				array( 'q' => 'En combien de temps des résultats ?',     'a' => 'Quick-wins SEO local : 4-8 semaines. Top 3 sur requêtes concurrentielles : 6-12 mois. C\'est un marathon, pas un sprint — méfiez-vous de qui promet la 1re place en 30 jours.' ),
				array( 'q' => 'Vous garantissez la 1re position ?',      'a' => 'Non, et personne ne peut. Google change son algo en permanence. Ce qu\'on garantit : amélioration mesurable du trafic organique mois par mois.' ),
				array( 'q' => 'Vous faites du linkbuilding agressif ?',  'a' => 'Non. Que des liens naturels (presse, partenaires, métier). Le black-hat = sanction Google à terme = retour à 0.' ),
				array( 'q' => 'Suivi mensuel obligatoire ?',              'a' => 'Non. On peut faire juste un audit one-shot à 1200€ et vous appliquez les recos vous-même. Mais sans contenu régulier, les gains s\'érodent vite.' ),
			),
		),

		'ads' => array(
			'slug'  => 'ads',
			'title' => 'Publicité Digitale',
			'intro' => 'Campagnes Google Ads, Meta (Facebook/Instagram), LinkedIn et TikTok Ads. Notre obsession : <strong>baisser le CPL</strong> (coût par lead) jusqu\'à ce que ça soit rentable, pas dépenser pour dépenser.',
			'deliverables' => array(
				'<strong>Stratégie multi-plateforme</strong> selon votre cible (B2B → LinkedIn, B2C local → Meta+GoogleAds)',
				'<strong>Comptes publicitaires</strong> setup avec pixel Meta, GTM, Conversions API',
				'<strong>10-30 visuels publicitaires</strong> sur-mesure (image + vidéo courte)',
				'<strong>15-50 variantes de textes</strong> testées en A/B',
				'<strong>Landing pages dédiées</strong> par campagne (conversion 2-3× supérieure à la home)',
				'<strong>Reporting hebdomadaire</strong> avec ROAS, CPL, CPC, taux de conversion',
				'<strong>Optimisation continue</strong> : pause des annonces perdantes, scale des gagnantes',
				'<strong>Retargeting</strong> + lookalike audiences pour amplifier les gagnants',
			),
			'process' => array(
				array( 'title' => 'Setup tracking',              'desc' => 'Pixel Meta, Google Analytics 4, Conversions API, GTM. Sans tracking propre, toute campagne ads est gaspillée.', 'duration' => '1 semaine' ),
				array( 'title' => 'Créations & tests',           'desc' => 'On produit visuels + textes en plusieurs variantes. Lancement à petit budget pour identifier les gagnants.', 'duration' => '2-3 semaines' ),
				array( 'title' => 'Scale des gagnants',          'desc' => 'On augmente le budget sur les annonces qui convertissent. On coupe les perdantes. Itération continue.', 'duration' => 'récurrent' ),
				array( 'title' => 'Optimisation budgets',        'desc' => 'Réallocation hebdomadaire entre plateformes. Pause si saisonnalité défavorable. Objectif : maximiser le ROI.', 'duration' => 'récurrent' ),
			),
			'pricing' => array(
				'from'     => 800, 'to' => 5000,
				'duration' => 'Setup 2-4 semaines + suivi mensuel',
				'includes' => array( 'Setup tracking complet', '10 visuels publicitaires', 'Landing page dédiée', 'Reporting hebdo', 'Optimisation continue' ),
			),
			'faqs' => array(
				array( 'q' => 'Quel budget media en plus ?',          'a' => 'Minimum 30€/jour pour avoir des données exploitables. En B2B local on commence à 50€/j, en e-commerce 100€/j+. Budget media payé directement par vous à la plateforme.' ),
				array( 'q' => 'Combien de temps avant rentabilité ?', 'a' => 'On vise un ROAS positif dès le mois 2. Premier mois = data collection + tests. Si à 3 mois c\'est pas rentable, on remet en cause toute la stratégie sans surfacturer.' ),
				array( 'q' => 'Engagement de durée ?',                'a' => 'Non. On bosse au mois, vous arrêtez quand vous voulez. On vous laisse l\'accès aux comptes pubs pour reprendre en interne si besoin.' ),
				array( 'q' => 'Vous gérez aussi TikTok ?',            'a' => 'Oui — si votre cible y est. On est bons aussi sur LinkedIn (B2B), Meta (B2C local), Google (intentionnaliste).' ),
			),
		),

		'brand' => array(
			'slug'  => 'brand',
			'title' => 'Branding & Identité',
			'intro' => 'Une marque qui dit qui vous êtes en 3 secondes. Logo, palette, typographies, ton, charte graphique. Pas du logo Canva — un système visuel complet qui vit sur tous vos supports.',
			'deliverables' => array(
				'<strong>Logo principal</strong> + 3 variantes (monochrome, blanc, favicon)',
				'<strong>Palette de couleurs</strong> : 5 couleurs avec codes Hex/RGB/CMJN/Pantone',
				'<strong>Typographies</strong> sélectionnées (1 titrage, 1 courante) + alternatives Google Fonts',
				'<strong>Charte graphique</strong> PDF 40-60 pages : usages, do/don\'t, exemples',
				'<strong>Templates réseaux sociaux</strong> : Insta posts, stories, Facebook cover, LinkedIn banner',
				'<strong>Templates print</strong> : carte de visite, papier en-tête, signature email, plaquette A4',
				'<strong>Photos & icônes</strong> : direction artistique + sélection bibliothèque ou shoot custom',
				'<strong>Naming & tagline</strong> : 3 propositions de slogan / promesse de marque',
			),
			'process' => array(
				array( 'title' => 'Brief & immersion',           'desc' => 'Atelier 2h : qui vous êtes, vos valeurs, vos concurrents, votre client idéal. Tests verbatim + mood-boards.', 'duration' => '1 semaine' ),
				array( 'title' => '3 pistes créatives',          'desc' => 'On présente 3 directions distinctes (pas 3 variations d\'une seule). Vous choisissez celle qui vous parle.', 'duration' => '2 semaines' ),
				array( 'title' => 'Itération + finalisation',    'desc' => 'On affine la piste choisie en 2-3 cycles. Présentation finale avec mise en situation (mockups).', 'duration' => '2 semaines' ),
				array( 'title' => 'Déclinaisons',                'desc' => 'On produit tous les fichiers, templates et la charte PDF. Livraison sur Notion ou Dropbox.', 'duration' => '1 semaine' ),
			),
			'pricing' => array(
				'from'     => 1800, 'to' => 8000,
				'duration' => '4-6 semaines',
				'includes' => array( 'Logo + 3 variantes', 'Palette + typo', 'Charte 40+ pages', '10 templates digital', '5 templates print', 'Cession droits totale' ),
			),
			'faqs' => array(
				array( 'q' => 'Logo seul, c\'est possible ?',         'a' => 'Oui — forfait à 600€ pour logo seul avec 2 variantes. Mais un logo isolé sans charte ni système visuel a peu de puissance. Réfléchissez à l\'usage long terme.' ),
				array( 'q' => 'Cession des droits ?',                  'a' => 'Cession totale et exclusive à la livraison. Vous êtes propriétaire à 100%, pas de redevance, pas de license à renouveler. On garde juste le droit de citer le projet en portfolio.' ),
				array( 'q' => 'Vous faites du print aussi ?',          'a' => 'Conception oui, impression non. On vous fournit les fichiers HD prêts pour imprimeur (cromalin, BAT). On peut recommander des imprimeurs partenaires (Onlineprinters, Vistaprint Pro).' ),
				array( 'q' => 'Et si je n\'aime aucune des 3 pistes ?', 'a' => 'Ça arrive sur 2% des projets. On refait un cycle de 3 nouvelles pistes sans surcoût. Si ça coince toujours, on rembourse 50% du brief et on arrête.' ),
			),
		),

		'conseil' => array(
			'slug'  => 'conseil',
			'title' => 'Conseil Stratégique',
			'intro' => 'Avant de coder ou de lancer des pubs : <strong>une stratégie claire</strong>. Audit, positionnement, plan d\'acquisition, choix de stack. Pour décider sereinement avec données + recul.',
			'deliverables' => array(
				'<strong>Audit complet</strong> : site, SEO, ads, brand, social, taux de conversion',
				'<strong>Étude de marché</strong> : 5-10 concurrents analysés en détail',
				'<strong>Persona client</strong> : 2-3 profils avec leurs douleurs, motivations, objections',
				'<strong>Positionnement</strong> : votre promesse unique, votre angle, votre tagline',
				'<strong>Plan d\'acquisition 12 mois</strong> : SEO, ads, content, partenariats, événements',
				'<strong>Stack technologique</strong> recommandée : WordPress, CRM, automation, IA',
				'<strong>Budget prévisionnel</strong> et ROI attendu par canal',
				'<strong>Rapport PDF 30-50 pages</strong> + présentation orale en visio',
			),
			'process' => array(
				array( 'title' => 'Diagnostic',                  'desc' => 'Visio approfondie 2h + analyse de votre business actuel, P&L, outils, taux de conversion historiques.', 'duration' => '1 semaine' ),
				array( 'title' => 'Recherche & benchmark',       'desc' => 'On creuse votre marché, vos concurrents, vos personas. Étude SEO, social listening, tests utilisateurs.', 'duration' => '2 semaines' ),
				array( 'title' => 'Recommandations',             'desc' => 'Synthèse des leviers prioritaires avec ROI estimé et timeline réaliste.', 'duration' => '1 semaine' ),
				array( 'title' => 'Présentation & plan d\'action','desc' => 'Visio 2h pour défendre les recos et répondre à vos questions. Plan d\'action concret livré à la fin.', 'duration' => '1 jour' ),
			),
			'pricing' => array(
				'from'     => 1500, 'to' => 6000,
				'duration' => '3-5 semaines',
				'includes' => array( 'Audit complet', 'Étude marché 5 concurrents', '2 personas', 'Plan 12 mois', 'Rapport PDF', 'Présentation orale 2h' ),
			),
			'faqs' => array(
				array( 'q' => 'Pourquoi pas un cabinet de conseil classique ?', 'a' => 'Les gros cabinets vous facturent 15-50k€ et livrent du PowerPoint générique. Nous, on connaît le digital concret (on l\'opère au quotidien) et on facture 5-10x moins.' ),
				array( 'q' => 'Vous appliquez aussi les recos après ?',         'a' => 'Optionnel. Vous pouvez prendre l\'audit seul à 1500€ et tout faire en interne. Ou continuer avec nous sur la création web/SEO/Ads (les recos s\'appliquent au tarif normal).' ),
				array( 'q' => 'Combien de temps avant retour sur invest ?',    'a' => 'Le rapport seul ne génère pas de revenu — c\'est sa mise en œuvre qui paye. En moyenne, les clients qui suivent les recos voient un impact à 3-6 mois.' ),
				array( 'q' => 'Confidentialité ?',                              'a' => 'NDA signé d\'office au démarrage. Aucune donnée partagée, jamais. On peut citer la mission en référence sans mentionner votre nom si vous préférez.' ),
			),
		),

	);

	return $data[ $slug ] ?? null;
}
