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
	public static function create_default_cpts() {
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

		// Evenements
		if ( ! get_posts( array( "post_type" => "ag_evenement", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$events = array(
				array( "Marche pour la justice climatique",        "Grande marche nationale samedi 15 juin. Départ 14h, place de la République à Paris, arrivée Bastille. Plus de 50 organisations partenaires, fanfare militante, prises de parole. Apportez vos pancartes !" ),
				array( "Assemblée générale annuelle",              "AG ouverte à toutes les adhérent·es à jour de cotisation. Bilan moral, bilan financier, vote du programme stratégique 2027, élection du Conseil d'administration. Lyon, Bourse du Travail, 22 juin de 9h à 18h." ),
				array( "Université d'été du mouvement",             "Trois jours de formations, ateliers, débats. Marseille, Friche Belle de Mai, du 6 au 8 juillet. Inscription obligatoire (gratuit pour adhérent·es, 30€ pour les autres). Hébergement solidaire possible." ),
				array( "Atelier 'Je crée un groupe local'",          "Soirée de formation pour celles et ceux qui veulent lancer un groupe dans leur ville. Méthodologie, outils numériques, cadrage juridique. En visio, jeudi 5 juin 19h-21h. Inscription gratuite." ),
			);
			foreach ( $events as $e ) {
				wp_insert_post( array(
					"post_type"    => "ag_evenement",
					"post_status"  => "publish",
					"post_title"   => $e[0],
					"post_excerpt" => wp_trim_words( $e[1], 25 ),
					"post_content" => $e[1],
				) );
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
