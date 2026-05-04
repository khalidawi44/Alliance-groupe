<?php
/**
 * Crée les pages séparées par défaut à l'activation : manifeste,
 * combats, événements, groupes locaux, signer, don, espace adhérent,
 * adhérer, mentions légales, politique de confidentialité.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Pages {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		// Rien à faire en runtime — la création des pages se fait à
		// l'activation du plugin (cf. register_activation_hook).
	}

	public static function create_default_pages() {
		$pages = array(
			'qui-sommes-nous' => array( 'title' => 'Qui sommes-nous',  'shortcode' => '[ag_fid_qui_sommes_nous]' ),
			'manifeste'    => array( 'title' => 'Manifeste',           'shortcode' => '[ag_fid_manifeste]' ),
			'combats'      => array( 'title' => 'Nos combats',         'shortcode' => '[ag_fid_combats]' ),
			'evenements'   => array( 'title' => 'Événements',          'shortcode' => '[ag_fid_evenements]' ),
			'groupes'      => array( 'title' => 'Groupes locaux',      'shortcode' => '[ag_fid_groupes]' ),
			'actu'         => array( 'title' => 'Actualités',          'shortcode' => '[ag_fid_actu]' ),
			'signer'       => array( 'title' => 'Signer l\'appel',     'shortcode' => '[ag_fid_signer]' ),
			'petitions'    => array( 'title' => 'Pétitions',           'shortcode' => '[ag_fid_petitions]' ),
			'don'          => array( 'title' => 'Faire un don',        'shortcode' => '[ag_fid_don]' ),
			'adherer'      => array( 'title' => 'Adhérer',             'shortcode' => '[ag_fid_adhesion]' ),
			'mon-compte'   => array( 'title' => 'Mon espace',          'shortcode' => '[ag_fid_compte]' ),
			'mentions'     => array( 'title' => 'Mentions légales',    'shortcode' => '[ag_fid_mentions]' ),
			'rgpd'         => array( 'title' => 'Confidentialité',     'shortcode' => '[ag_fid_rgpd]' ),
			'statuts'      => array( 'title' => 'Statuts',             'content'   => self::default_statuts_content() ),
		);
		foreach ( $pages as $slug => $page ) {
			if ( get_page_by_path( $slug ) ) continue;
			wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => isset( $page['shortcode'] ) ? $page['shortcode'] : $page['content'],
			) );
		}
		self::create_default_menus();
		self::create_default_cpts();
	}

	/**
	 * Statuts type association loi 1901, neutres et reutilisables.
	 */
	private static function default_statuts_content() {
		return '<h2>Article 1 — Constitution et dénomination</h2>
<p>Il est fondé entre les adhérent·es aux présents statuts une association régie par la loi du 1er juillet 1901 et le décret du 16 août 1901, ayant pour titre <strong>[Nom de l\'association]</strong>.</p>

<h2>Article 2 — Objet</h2>
<p>L\'association a pour but de promouvoir une société plus juste, écologique et démocratique par tous les moyens légaux : campagnes de sensibilisation, mobilisations citoyennes, productions intellectuelles, actions de terrain, plaidoyer institutionnel.</p>

<h2>Article 3 — Siège social</h2>
<p>Le siège social est fixé à <strong>[Adresse]</strong>. Il pourra être transféré sur simple décision du Conseil d\'administration.</p>

<h2>Article 4 — Durée</h2>
<p>La durée de l\'association est illimitée.</p>

<h2>Article 5 — Composition</h2>
<p>L\'association se compose de :</p>
<ul>
<li>Membres sympathisant·es (gratuit) : reçoivent les communications, signent les pétitions ;</li>
<li>Membres adhérent·es : à jour de leur cotisation annuelle, peuvent voter en AG ;</li>
<li>Membres militant·es : adhérent·es engagé·es dans une action ou un groupe local ;</li>
<li>Membres bienfaiteur·rices : versent une cotisation supérieure au montant minimum.</li>
</ul>

<h2>Article 6 — Cotisation</h2>
<p>Le montant de la cotisation annuelle est fixé chaque année par l\'Assemblée générale.</p>

<h2>Article 7 — Conseil d\'administration</h2>
<p>L\'association est administrée par un Conseil d\'administration de 7 à 15 membres, élu·es pour 2 ans par l\'AG. Le CA élit en son sein un bureau (président·e, trésorier·e, secrétaire). Le Conseil se réunit au minimum 4 fois par an.</p>

<h2>Article 8 — Assemblée générale ordinaire</h2>
<p>L\'AG ordinaire se réunit une fois par an. Elle approuve les comptes, vote le budget et oriente la stratégie. Tou·tes les adhérent·es à jour de cotisation y participent avec voix délibérative.</p>

<h2>Article 9 — Assemblée générale extraordinaire</h2>
<p>L\'AG extraordinaire peut être convoquée à la demande de 1/3 des adhérent·es ou du CA pour modifier les statuts ou prononcer la dissolution.</p>

<h2>Article 10 — Ressources</h2>
<p>Les ressources de l\'association comprennent : cotisations, dons manuels, subventions publiques, produit des manifestations, recettes de ses publications, et toute autre ressource autorisée par la loi.</p>

<h2>Article 11 — Règlement intérieur</h2>
<p>Un règlement intérieur, adopté par le CA et ratifié par l\'AG, précise les modalités d\'application des présents statuts.</p>

<h2>Article 12 — Dissolution</h2>
<p>En cas de dissolution prononcée en AG extraordinaire, un·e ou plusieurs liquidateur·rices sont nommé·es. L\'actif net est dévolu à une autre association poursuivant un objet similaire.</p>

<p><em>Statuts adoptés en Assemblée générale constitutive le [date].</em></p>';
	}

	/**
	 * Cree des CPT exemples concrets (combats, evenements, groupes,
	 * petitions) si aucune entree n\'existe deja. Ne duplique rien.
	 */
	public static function create_default_cpts( $force = false ) {
		if ( $force ) {
			foreach ( array( 'ag_combat', 'ag_evenement', 'ag_groupe', 'ag_petition' ) as $cpt ) {
				$old = get_posts( array( 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );
				foreach ( $old as $pid ) wp_delete_post( $pid, true );
			}
			// Articles : on supprime uniquement nos articles seedes (par titre exact)
			$seed_titles = array(
				"Hôpital public : nous publions notre contre-budget 2026",
				"Pétition climat : 47 000 signatures en 3 semaines",
				"Nouveau groupe local à Saint-Étienne — bienvenue !",
				"Logement : nos 12 propositions pour 2027",
				"AG 2026 : ce qui a été voté",
			);
			foreach ( $seed_titles as $t ) {
				$existing = get_page_by_title( $t, OBJECT, 'post' );
				if ( $existing ) wp_delete_post( $existing->ID, true );
			}
		}
		// Combats
		if ( ! get_posts( array( "post_type" => "ag_combat", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$combats = array(
				array( "Justice climatique",  "Pour une transition écologique qui ne pèse pas sur les plus modestes : rénovation thermique massive des passoires énergétiques, gratuité progressive des transports en commun, fin des aides publiques aux énergies fossiles.\n\nNos propositions chiffrées, validées par 12 économistes universitaires, sont disponibles dans notre <strong>contre-budget climat 2026</strong>." ),
				array( "Logement digne",      "Le logement est devenu inaccessible pour des millions de Français·es. Nous portons : encadrement effectif des loyers en zone tendue, réquisition des logements vacants depuis plus de 18 mois, plan de construction de 200 000 logements sociaux par an." ),
				array( "Service public fort", "Refonder l'hôpital, l'école, la SNCF. Stop aux fermetures de lits et de classes. Nous demandons un moratoire immédiat sur toute fermeture de service public en zone rurale, et un plan de recrutement à hauteur des besoins réels." ),
				array( "Démocratie réelle",   "Voter tous les 5 ans ne suffit plus. Nous portons le Référendum d'Initiative Citoyenne (RIC), des assemblées citoyennes tirées au sort sur les grands sujets, la reconnaissance du vote blanc, et la révocation des élu·es par leurs électeur·rices." ),
				array( "Transparence publique","Open data total des marchés publics, registre obligatoire des lobbies au Parlement et dans les ministères, traçabilité des amendements parlementaires. La démocratie sans transparence est une illusion." ),
				array( "Égalité réelle",      "Combattre activement toutes les discriminations : sexe, origine, handicap, orientation. Loi de parité réelle dans les exécutifs, statistiques ethniques anonymisées pour mesurer les discriminations, formation obligatoire dans la fonction publique." ),
			);
			foreach ( $combats as $c ) {
				wp_insert_post( array(
					"post_type"    => "ag_combat",
					"post_status"  => "publish",
					"post_title"   => $c[0],
					"post_excerpt" => wp_trim_words( $c[1], 25 ),
					"post_content" => $c[1],
				) );
			}
		}

		// Evenements (avec meta date/heure/lieu)
		if ( ! get_posts( array( "post_type" => "ag_evenement", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$events = array(
				array(
					"title"   => "Marche pour la justice climatique",
					"date"    => "2026-06-15", "time" => "14:00", "end" => "18:00",
					"city"    => "Paris", "place" => "République → Bastille",
					"content" => "Grande marche nationale. Départ 14h place de la République, arrivée Bastille. Plus de 50 organisations partenaires, fanfare militante, prises de parole. <strong>Apportez vos pancartes !</strong> Mobilisation pour une transition écologique juste et sociale.",
				),
				array(
					"title"   => "Assemblée générale annuelle",
					"date"    => "2026-06-22", "time" => "09:00", "end" => "18:00",
					"city"    => "Lyon", "place" => "Bourse du Travail",
					"content" => "AG ouverte à toutes les adhérent·es à jour de cotisation. <strong>Bilan moral, bilan financier, vote du programme stratégique 2027, élection du Conseil d'administration.</strong> Émargement dès 8h30. Repas partagé sur place.",
				),
				array(
					"title"   => "Université d'été du mouvement",
					"date"    => "2026-07-06", "time" => "10:00", "end" => "18:00",
					"city"    => "Marseille", "place" => "Friche Belle de Mai",
					"content" => "Trois jours de formations, ateliers, débats du 6 au 8 juillet. Inscription obligatoire (gratuit pour adhérent·es, 30€ pour les autres). Hébergement solidaire possible chez les adhérent·es marseillais·es.",
				),
				array(
					"title"   => "Atelier — Je crée un groupe local",
					"date"    => "2026-06-05", "time" => "19:00", "end" => "21:00",
					"city"    => "En visio", "place" => "Lien envoyé après inscription",
					"content" => "Soirée de formation pour celles et ceux qui veulent lancer un groupe dans leur ville. <strong>Méthodologie, outils numériques, cadrage juridique.</strong> Inscription gratuite. Replay envoyé aux inscrit·es.",
				),
				array(
					"title"   => "Conférence — Refonder l'hôpital public",
					"date"    => "2026-06-28", "time" => "20:00", "end" => "22:30",
					"city"    => "Toulouse", "place" => "Salle du Cabanis (centre-ville)",
					"content" => "Présentation publique de notre contre-budget hôpital, en présence de 3 médecins de terrain et d'un économiste de la santé. <strong>Entrée libre</strong>, débat avec la salle après les interventions.",
				),
			);
			foreach ( $events as $e ) {
				$post_id = wp_insert_post( array(
					"post_type"    => "ag_evenement",
					"post_status"  => "publish",
					"post_title"   => $e["title"],
					"post_excerpt" => wp_trim_words( wp_strip_all_tags( $e["content"] ), 25 ),
					"post_content" => $e["content"],
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, "_ag_event_date",  $e["date"] );
					update_post_meta( $post_id, "_ag_event_time",  $e["time"] );
					update_post_meta( $post_id, "_ag_event_end",   $e["end"] );
					update_post_meta( $post_id, "_ag_event_city",  $e["city"] );
					update_post_meta( $post_id, "_ag_event_place", $e["place"] );
				}
			}
		}

		// Groupes locaux
		if ( ! get_posts( array( "post_type" => "ag_groupe", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$groupes = array(
				array( "Paris 11e", "Animé par Yacine et Léa. Réunion publique le premier mardi de chaque mois, 19h, café associatif Le Lieu (rue Saint-Maur). Une trentaine d'adhérent·es actifs." ),
				array( "Lyon Croix-Rousse", "Animé par Sophie. Atelier hebdomadaire d'éducation populaire, samedi matin à la Maison des Associations. Mobilisation locale autour du logement et des transports." ),
				array( "Marseille centre", "Animé par Mehdi. Permanence chaque mercredi à la Friche Belle de Mai. Travail de terrain dans les quartiers Nord, soutien scolaire bénévole, plaidoyer auprès de la mairie." ),
				array( "Saint-Étienne", "Tout nouveau groupe ! Première réunion le 18 mai, Maison des Syndicats. Cherche ses bénévoles fondateurs — n'hésitez pas à venir." ),
				array( "Toulouse Mirail", "Animé par Aïcha. Engagement fort sur les droits des femmes et les discriminations. Rendez-vous mensuel ouvert à tou·tes le second jeudi soir." ),
			);
			foreach ( $groupes as $g ) {
				wp_insert_post( array(
					"post_type"    => "ag_groupe",
					"post_status"  => "publish",
					"post_title"   => $g[0],
					"post_excerpt" => wp_trim_words( $g[1], 20 ),
					"post_content" => $g[1],
				) );
			}
		}

		// Petitions
		if ( ! get_posts( array( "post_type" => "ag_petition", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$petitions = array(
				array( "Pour la transparence des marchés publics", "Aujourd'hui, l'attribution des marchés publics reste opaque. Nous demandons leur publication intégrale en open data, avec mise à disposition d'une API publique. Objectif : 50 000 signatures d'ici fin juillet." ),
				array( "Encadrement effectif des loyers en zone tendue", "Le dispositif actuel n'est pas appliqué dans 6 communes sur 10. Nous demandons des contrôles systématiques et des sanctions dissuasives pour les bailleurs en infraction." ),
				array( "Reconnaissance du vote blanc",            "Le vote blanc doit compter dans le total des suffrages exprimés. C'est une exigence démocratique élémentaire, portée depuis des années sans être appliquée." ),
			);
			foreach ( $petitions as $p ) {
				wp_insert_post( array(
					"post_type"    => "ag_petition",
					"post_status"  => "publish",
					"post_title"   => $p[0],
					"post_excerpt" => wp_trim_words( $p[1], 25 ),
					"post_content" => $p[1],
				) );
			}
		}

		// Articles (post standard) — 5 articles d'actualite
		$existing_posts = get_posts( array( "post_type" => "post", "posts_per_page" => 1, "post_status" => "any" ) );
		$has_seeded = false;
		foreach ( $existing_posts as $p ) {
			if ( strpos( $p->post_title, 'ours d\'eau' ) === false && strpos( $p->post_title, 'Hello world' ) === false ) {
				$has_seeded = true; break;
			}
		}
		if ( ! $has_seeded ) {
			$articles = array(
				array(
					"title"   => "Hôpital public : nous publions notre contre-budget 2026",
					"excerpt" => "Notre groupe de travail santé publie un rapport de 60 pages chiffrant un plan d'urgence pour l'hôpital. À télécharger librement.",
					"content" => "<p>Après 14 mois de travail, notre groupe santé publie aujourd'hui un <strong>contre-budget hôpital public</strong> de 60 pages, validé par 12 économistes universitaires et 35 médecins de terrain.</p>\n<h3>Ce que nous proposons</h3>\n<ul><li>Recrutement de 100 000 soignant·es sur 5 ans, financé par la suppression des aides aux complémentaires santé privées</li><li>Moratoire immédiat sur les fermetures de lits et de services en zone rurale</li><li>Revalorisation salariale ciblée pour les métiers en tension (infirmières, aides-soignantes)</li><li>Plan d'investissement de 12 milliards d'euros sur 5 ans pour rénover le bâti hospitalier</li></ul>\n<p>Le rapport est <strong>téléchargeable gratuitement</strong> sur notre site et a déjà été envoyé aux groupes parlementaires. Nous demanderons des auditions à la commission des Affaires sociales.</p>\n<p><em>Document écrit en licence libre Creative Commons. Diffusez-le.</em></p>",
					"date"    => "2026-05-12 09:00:00",
				),
				array(
					"title"   => "Pétition climat : 47 000 signatures en 3 semaines",
					"excerpt" => "L'objectif de 50 000 est désormais à portée. Le dépôt à l'Assemblée est prévu pour la fin du mois. Merci à tou·tes.",
					"content" => "<p>Lancée il y a tout juste 3 semaines, notre <strong>pétition pour la transparence des marchés publics</strong> vient de franchir la barre symbolique des <strong>47 000 signatures</strong>. À ce rythme, nous dépasserons l'objectif de 50 000 d'ici la fin du mois.</p>\n<h3>Et après ?</h3>\n<p>Nous prévoyons un dépôt officiel à l'Assemblée nationale le <strong>25 mai</strong>, en présence de plusieurs député·es de l'inter-groupe transparence. Une délégation du mouvement sera reçue à 14h dans les locaux du Palais Bourbon.</p>\n<p>Notre objectif : obtenir un débat parlementaire sur la <strong>publication open data systématique des marchés publics</strong> de plus de 25 000€, avec sanctions effectives en cas de non-respect.</p>\n<p>Vous n'avez pas encore signé ? <a href=\"/signer/\">C'est le moment.</a></p>",
					"date"    => "2026-05-05 14:30:00",
				),
				array(
					"title"   => "Nouveau groupe local à Saint-Étienne — bienvenue !",
					"excerpt" => "Le 47e groupe local du mouvement vient d'être officialisé. Première réunion publique le 18 mai à la Maison des Syndicats.",
					"content" => "<p>C'est officiel : le mouvement compte désormais <strong>47 groupes locaux</strong> partout en France. Le petit dernier vient d'être créé à <strong>Saint-Étienne</strong> par une équipe de 8 fondateurs et fondatrices.</p>\n<h3>Première rencontre publique</h3>\n<p>Vous habitez dans le bassin stéphanois ? Venez nous rencontrer le <strong>samedi 18 mai à 18h</strong>, à la Maison des Syndicats (Bourse du Travail, cours Victor Hugo).</p>\n<p>Au programme :</p>\n<ul><li>Présentation du mouvement et de ses combats</li><li>Tour de table : qu'est-ce qui vous fait venir ?</li><li>Identification des priorités locales (hôpital de Bellevue, transports en commun, logement étudiant)</li><li>Verre de l'amitié — bières locales offertes par la maison</li></ul>\n<p>Aucune adhésion requise pour la première réunion.</p>",
					"date"    => "2026-04-28 17:15:00",
				),
				array(
					"title"   => "Logement : nos 12 propositions pour 2027",
					"excerpt" => "Encadrement réel des loyers, réquisition des logements vacants, plan massif de construction sociale. Un livret de 40 pages disponible.",
					"content" => "<p>La crise du logement est devenue insupportable. Étudiant·es qui dorment dans leur voiture, familles qui consacrent 50% de leurs revenus au loyer, communes entières où il n'y a plus rien à louer. Nous publions aujourd'hui <strong>12 propositions chiffrées</strong> pour 2027.</p>\n<h3>Les axes principaux</h3>\n<ol><li><strong>Encadrement effectif des loyers</strong> en zone tendue avec contrôles aléatoires et sanctions dissuasives</li><li><strong>Réquisition des logements vacants</strong> depuis plus de 18 mois, indemnisation propriétaires</li><li><strong>200 000 logements sociaux par an</strong> pendant 5 ans, financés par un fléchage de l'épargne réglementée</li><li><strong>Plafonnement Airbnb</strong> à 90 jours/an en zone tendue (contre 120 actuellement)</li><li><strong>Garantie universelle des loyers</strong> à la charge de l'État</li></ol>\n<p>Le livret complet (40 pages) est disponible en PDF sur notre site. Nous le présenterons en avant-première lors de l'<strong>Université d'été</strong>, à Marseille du 6 au 8 juillet.</p>",
					"date"    => "2026-04-20 10:00:00",
				),
				array(
					"title"   => "AG 2026 : ce qui a été voté",
					"excerpt" => "Compte-rendu de l'Assemblée générale annuelle de Lyon. Bilan financier, élection du CA, programme stratégique 2027.",
					"content" => "<p>Le 22 juin dernier, plus de <strong>650 adhérent·es</strong> se sont réuni·es à la Bourse du Travail de Lyon pour notre Assemblée générale annuelle. Voici les principales décisions.</p>\n<h3>Bilan financier — adopté à 94%</h3>\n<p>Comptes 2025 certifiés sans réserve par notre commissaire aux comptes. Total des recettes : <strong>312 000€</strong> dont 78% de cotisations et dons d'adhérent·es. Aucun don de plus de 1 500€. Tous les comptes sont publiés en open data sur notre site.</p>\n<h3>Programme stratégique 2027 — adopté à 87%</h3>\n<ul><li>Lancement de 3 nouvelles campagnes thématiques : santé mentale, ruralité, handicap</li><li>Objectif de 60 groupes locaux d'ici fin 2027 (soit +28%)</li><li>Création d'un fonds d'aide juridique pour soutenir les actions des groupes locaux</li><li>Refonte du site internet et lancement d'une newsletter hebdomadaire</li></ul>\n<h3>Conseil d'administration — élu</h3>\n<p>Le nouveau CA compte 13 membres (parité respectée), élu·es pour 2 ans. Le bureau a été désigné le lendemain : Camille Lefèvre reconduite à la présidence, Léa Marchand trésorière, Mehdi El Amrani secrétaire général.</p>\n<p><a href=\"/mon-compte/\">Le PV intégral est consultable</a> par les adhérent·es à jour de cotisation.</p>",
					"date"    => "2026-04-10 16:00:00",
				),
			);
			foreach ( $articles as $a ) {
				wp_insert_post( array(
					"post_type"    => "post",
					"post_status"  => "publish",
					"post_title"   => $a["title"],
					"post_excerpt" => $a["excerpt"],
					"post_content" => $a["content"],
					"post_date"    => $a["date"],
					"post_date_gmt" => get_gmt_from_date( $a["date"] ),
				) );
			}
		}
	}

	/**
	 * Cree (idempotent) les menus principal et footer puis les assigne
	 * aux emplacements 'primary' et 'footer' du theme. Indispensable
	 * pour que le menu pointe vers les pages reelles et non vers des
	 * ancres.
	 */
	public static function create_default_menus() {
		$primary_items = array(
			'qui-sommes-nous' => 'Qui sommes-nous',
			'manifeste'       => 'Manifeste',
			'combats'         => 'Combats',
			'evenements'      => 'Événements',
			'groupes'         => 'Groupes locaux',
			'actu'            => 'Actualités',
			'don'             => 'Faire un don',
		);
		$footer_items = array(
			'manifeste' => 'Le manifeste',
			'groupes'   => 'Trouver mon groupe',
			'don'       => 'Faire un don',
			'mentions'  => 'Mentions légales',
			'rgpd'      => 'Confidentialité',
		);
		self::ensure_menu( 'AG Fidélité — Principal', 'primary', $primary_items );
		self::ensure_menu( 'AG Fidélité — Footer',    'footer',  $footer_items );
	}

	private static function ensure_menu( $menu_name, $location, $items ) {
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
			if ( is_wp_error( $menu_id ) ) return;
		} else {
			$menu_id = $menu->term_id;
			foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
		}
		foreach ( $items as $slug => $label ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) continue;
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
