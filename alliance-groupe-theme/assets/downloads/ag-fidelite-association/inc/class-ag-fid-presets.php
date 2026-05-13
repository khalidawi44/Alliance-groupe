<?php
/**
 * Pack Fidélité — Presets de contenu prêts à appliquer en 1 clic.
 *
 * Permet a un client (ou un installateur Alliance Groupe) d'appliquer
 * en une fois un jeu de contenu cible (Customizer + pages + CPT)
 * sans avoir a copier-coller manuellement chaque champ.
 *
 * EXEMPLE : le preset 'association_generique' applique des textes neutres
 * pour une association citoyenne (justice sociale, ecologie, democratie
 * locale), avec 3 combats / 3 evenements / 3 petitions / 3 articles
 * placeholder. Ideal pour decouvrir le template ou demarrer rapidement.
 *
 * Aucun preset n'est applique automatiquement.
 *
 * REGLE INTERNE : pour ajouter un nouveau preset, etendre le tableau
 * dans self::get_presets(). Chaque preset declare ses theme_mods,
 * ses pages (slug => [title, first_paragraph]), et ses CPT (combats).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Presets {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 28 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_apply_preset' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_rebuild_menu' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_applied' ) );
	}

	public static function register_menu() {
		add_submenu_page(
			'ag-fid-recommendations',
			__( 'Presets de contenu', 'ag-fidelite-association' ),
			'🎯 ' . __( 'Presets de contenu', 'ag-fidelite-association' ),
			'manage_options',
			'ag-fid-presets',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Definition des presets disponibles. Chaque preset = un identifiant
	 * + label + description + payload (theme_mods, pages, combats).
	 */
	private static function get_presets() {
		return array(

			// Preset générique de démonstration : utilisable pour n'importe quelle
			// association citoyenne. Contenu placeholder neutre — pas de marque,
			// pas de référence géographique, pas d'UUID Action Populaire (le sticky
			// Soutenir basculera automatiquement sur /don/).
			'association_generique' => array(
				'label' => '🌱 Association citoyenne (démo)',
				'desc'  => 'Preset générique pour démarrer rapidement : 3 combats (justice sociale, écologie, démocratie locale), 3 événements, 3 pétitions, 3 articles, toutes les pages remplies avec contenu placeholder neutre. Idéal pour découvrir le template ou comme starter avant personnalisation.',
				'mods'  => array(
					'ag_asso_name'         => 'Mon Association',
					'ag_asso_baseline'     => 'Collectif citoyen engagé',
					'ag_asso_slogan'       => 'Pour une société plus juste',
					'ag_asso_hero_title'   => 'Ensemble, changeons les choses',
					'ag_asso_hero_sub'     => 'Un collectif d\'habitant·es engagé·es pour la justice sociale, l\'écologie et la démocratie locale.',
					'ag_asso_cta_label'    => 'Rejoindre le collectif',
					'ag_asso_cta_url'      => '/adherer/',
					'ag_asso_cta2_label'   => 'Faire un don',
					'ag_asso_cta2_url'     => '/don/',
					'ag_asso_stat1_value'  => '320+',
					'ag_asso_stat1_label'  => 'adhérent·es',
					'ag_asso_stat2_value'  => '12',
					'ag_asso_stat2_label'  => 'actions menées en 2026',
					'ag_asso_stat3_value'  => '4',
					'ag_asso_stat3_label'  => 'années d\'existence',
				),
				'pages' => array(
					'qui-sommes-nous' => array(
						'title'   => 'Qui sommes-nous',
						'content' => "<!-- wp:paragraph -->\n<p>Nous sommes un <strong>collectif citoyen indépendant</strong> de toute formation politique. Habitant·es, bénévoles, militant·es du quotidien, nous nous organisons autour de causes concrètes qui touchent notre territoire.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Notre fonctionnement</h2>\n<!-- /wp:heading -->\n\n<!-- wp:list -->\n<ul><li><strong>Démocratie interne</strong> : décisions collectives en assemblée générale, aucun chef</li><li><strong>Transparence</strong> : comptes publiés chaque année, ressources humaines bénévoles</li><li><strong>Action concrète</strong> : nous privilégions le terrain, l'écoute, l'accompagnement</li></ul>\n<!-- /wp:list -->\n\n<!-- wp:paragraph -->\n<p>Pour nous rejoindre ou en savoir plus : <a href=\"/signer/\">contactez-nous</a>.</p>\n<!-- /wp:paragraph -->",
					),
					'manifeste' => array(
						'title'   => 'Notre manifeste',
						'content' => "<!-- wp:paragraph -->\n<p>Nous croyons qu'<strong>une autre société est possible</strong> et qu'elle se construit à partir du local, dans la durée, par l'action collective. Trois axes structurent notre engagement.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Justice sociale</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Lutter contre les inégalités qui détruisent le tissu social : précarité, accès inégal aux droits, discriminations. Nous soutenons celles et ceux qui n'ont pas la voix au chapitre.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Transition écologique</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Préserver le vivant, transformer notre rapport au monde. Sobriété, partage des ressources, justice environnementale : la transition est sociale ou ne sera pas.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Démocratie locale</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Donner le pouvoir aux habitant·es. Conseils de quartier vivants, budgets participatifs, contrôle citoyen des décisions publiques. La démocratie ne s'arrête pas à l'élection.</p>\n<!-- /wp:paragraph -->",
					),
					'combats' => array(
						'title'   => 'Nos combats',
						'content' => "<!-- wp:paragraph -->\n<p>Nos trois axes d'engagement, portés concrètement par nos bénévoles et nos partenaires.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_combats]\n<!-- /wp:shortcode -->",
					),
					'evenements' => array(
						'title'   => 'Événements à venir',
						'content' => "<!-- wp:paragraph -->\n<p>Rencontres publiques, ateliers, mobilisations, assemblées : retrouvez-nous sur le terrain.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_evenements]\n<!-- /wp:shortcode -->",
					),
					'groupes' => array(
						'title'   => 'Nos groupes',
						'content' => "<!-- wp:paragraph -->\n<p>Liste de nos groupes locaux et thématiques. Rejoignez celui qui correspond à votre quartier ou à votre cause.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_groupes]\n<!-- /wp:shortcode -->",
					),
					'actu' => array(
						'title'   => 'Actualités',
						'content' => "<!-- wp:paragraph -->\n<p>Nos dernières publications : comptes rendus d'actions, prises de position, événements à venir.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_actu]\n<!-- /wp:shortcode -->",
					),
					'signer' => array(
						'title'   => 'Nous contacter',
						'content' => "<!-- wp:paragraph -->\n<p>Une question, un témoignage, une demande d'aide ? Remplissez le formulaire ci-dessous, un·e bénévole vous recontactera rapidement.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_signer]\n<!-- /wp:shortcode -->",
					),
					'don' => array(
						'title'   => 'Faire un don',
						'content' => "<!-- wp:paragraph -->\n<p>Notre association est indépendante de tout pouvoir économique ou politique. Nous ne tenons que par les dons de nos sympathisant·es.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>66% de votre don est déductible</strong> de votre impôt sur le revenu (loi française). Reçu fiscal envoyé automatiquement par email.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:html -->\n<p style=\"text-align:center;margin:24px 0;\"><a href=\"#\" class=\"ag-asso-btn ag-asso-btn--primary\">💛 Faire un don</a></p>\n<p style=\"text-align:center;font-size:.9em;color:#888;\">Configurez votre solution de don (HelloAsso, Stripe, Action Populaire) dans <em>Apparence → Personnaliser</em>.</p>\n<!-- /wp:html -->",
					),
					'adherer' => array(
						'title'   => 'Adhérer / rejoindre',
						'content' => "<!-- wp:paragraph -->\n<p>Vous voulez rejoindre notre collectif, participer aux décisions, nous donner un coup de main ?</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>Nos assemblées sont ouvertes à tou·tes</strong>, sans condition. On ne demande pas de prise de carte pour participer — uniquement de l'envie et un peu de temps.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_adhesion]\n<!-- /wp:shortcode -->",
					),
					'mon-compte' => array(
						'title'   => 'Espace adhérent·e',
						'content' => "<!-- wp:paragraph -->\n<p>Espace réservé aux adhérent·es : comptes rendus, ressources internes, badge numérique.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_compte]\n<!-- /wp:shortcode -->",
					),
					'petitions' => array(
						'title'   => 'Nos pétitions',
						'content' => "<!-- wp:paragraph -->\n<p>Pétitions actives portées par notre collectif. Chaque signature compte.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_petitions]\n<!-- /wp:shortcode -->",
					),
					'reunion' => array(
						'title'   => 'Réunion en ligne',
						'content' => "<!-- wp:paragraph -->\n<p>Salle de visioconférence sécurisée pour nos réunions à distance (commissions, AG, formations).</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_visio]\n<!-- /wp:shortcode -->",
					),
					'rendez-vous' => array(
						'title'   => 'Prendre rendez-vous',
						'content' => "<!-- wp:paragraph -->\n<p>Pour un échange individuel avec un·e bénévole : prenez rendez-vous via notre agenda en ligne.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_rdv]\n<!-- /wp:shortcode -->",
					),
				),
				'combats' => array(
					array(
						'title'   => 'Justice sociale',
						'emoji'   => '🤝',
						'color'   => '#3B5998',
						'excerpt' => 'Lutter contre les inégalités, défendre l\'accès aux droits, soutenir les plus précaires.',
						'content' => "Notre premier axe d'engagement. Nous luttons contre toutes les formes d'inégalités qui fragilisent le tissu social : précarité, mal-logement, accès inégal à la santé, à l'éducation, aux droits.\n\nNous accompagnons les personnes en difficulté dans leurs démarches, nous portons leurs voix devant les institutions, et nous mobilisons sur les politiques publiques qui aggravent ou réduisent ces inégalités.",
					),
					array(
						'title'   => 'Transition écologique',
						'emoji'   => '🌱',
						'color'   => '#1F8A3D',
						'excerpt' => 'Préserver le vivant, sortir des énergies fossiles, repenser nos modes de vie collectifs.',
						'content' => "Deuxième axe : nous croyons que la transition écologique est indissociable de la justice sociale. Pas de planète vivable sans répartition équitable des efforts et des bénéfices.\n\nNous portons des projets concrets sur notre territoire : ateliers de sensibilisation, jardins partagés, mobilisation contre les projets nuisibles à l'environnement, plaidoyer pour des politiques publiques ambitieuses.",
					),
					array(
						'title'   => 'Démocratie locale',
						'emoji'   => '🗳️',
						'color'   => '#7B2D8E',
						'excerpt' => 'Donner le pouvoir aux habitant·es : conseils de quartier, budgets participatifs, contrôle citoyen.',
						'content' => "Troisième axe : faire vivre la démocratie au-delà des élections. Trop souvent, les décisions qui nous concernent sont prises sans nous, par des élu·es qui consultent peu et expliquent encore moins.\n\nNous animons des conseils de quartier ouverts, nous suivons les budgets municipaux, nous proposons des outils de contrôle citoyen. L'objectif : que les habitant·es reprennent la main sur leur territoire.",
					),
				),
				'evenements' => array(
					array(
						'title'   => 'Assemblée générale annuelle',
						'date'    => '2026-06-15', 'time' => '18:00', 'end' => '21:00',
						'city'    => 'À définir',
						'place'   => 'Salle à confirmer (voir événement)',
						'content' => "Notre assemblée générale annuelle, ouverte à tou·tes les adhérent·es à jour de cotisation.\n\nOrdre du jour : bilan financier 2025, vote du programme stratégique 2027, élection du conseil d'administration.\n\nUn buffet partagé suivra l'AG. Apportez ce qui vous plaît.",
					),
					array(
						'title'   => 'Réunion publique mensuelle',
						'date'    => '2026-06-04', 'time' => '19:00', 'end' => '21:00',
						'city'    => 'À définir',
						'place'   => 'Maison des associations',
						'content' => "Réunion publique mensuelle ouverte à tou·tes — adhérent·es comme sympathisant·es.\n\nAu programme : retour sur les actions du mois, préparation des prochaines mobilisations, débat thématique.\n\nPremière venue ? Aucun souci, on vous accueille avec un café.",
					),
					array(
						'title'   => 'Atelier de formation citoyenne',
						'date'    => '2026-06-22', 'time' => '14:00', 'end' => '17:00',
						'city'    => 'À définir',
						'place'   => 'À confirmer',
						'content' => "Atelier de formation gratuit : comprendre les institutions locales, savoir s'adresser à un·e élu·e, monter un dossier de subvention, organiser une réunion publique.\n\nNiveau débutant. Inscription conseillée (places limitées à 25 personnes).",
					),
				),
				'groupes' => array(
					array(
						'title'   => 'Commission Justice sociale',
						'excerpt' => 'Notre commission qui anime nos actions d\'accompagnement et de plaidoyer sur les inégalités.',
						'content' => "Cette commission rassemble une dizaine de bénévoles qui se réunissent chaque mois. Trois activités régulières :\n\n• Permanences d'accompagnement administratif (samedis matins)\n• Veille sur les politiques sociales locales\n• Organisation de campagnes de sensibilisation\n\nPour rejoindre la commission : <a href=\"/signer/\">contactez-nous</a>.",
					),
				),
				'petitions' => array(
					array(
						'title'   => 'Pour un budget participatif municipal',
						'excerpt' => 'Demandons à notre commune d\'instaurer un vrai budget participatif, abondé d\'au moins 5% du budget d\'investissement.',
						'content' => "Trop de communes affichent un « budget participatif » qui ne représente que 1% ou moins de leurs investissements, avec des règles d'éligibilité opaques.\n\nNous demandons un budget participatif <strong>réellement consistant</strong> (minimum 5% des investissements), avec des règles claires, des jurys citoyens tirés au sort, et un suivi public des projets réalisés.",
				),
				array(
						'title'   => 'Plus de pistes cyclables sécurisées',
						'excerpt' => 'Pour un plan vélo ambitieux avec des aménagements protégés sur les axes principaux.',
						'content' => "Notre commune fait des annonces vélo mais sur le terrain, les aménagements restent dérisoires : peintures au sol, discontinuités dangereuses, partage forcé avec les voitures.\n\nNous demandons un plan vélo chiffré, avec un objectif clair (par exemple 50 km de pistes protégées en 3 ans), et un comité de suivi avec des associations de cyclistes.",
					),
					array(
						'title'   => 'Transparence des décisions municipales',
						'excerpt' => 'Publication des comptes rendus de conseil municipal, des notes de service, des conventions avec des opérateurs privés.',
						'content' => "L'accès aux documents administratifs est un droit, garanti par la CADA. En pratique, il faut souvent insister, attendre des mois, ou ne rien recevoir.\n\nNous demandons une politique de transparence proactive : publication automatique des comptes rendus, des conventions avec des opérateurs privés (eau, déchets, transports), des notes de service éclairant les décisions municipales.",
					),
				),
				'articles' => array(
					array(
						'title'   => 'Bienvenue sur notre nouveau site',
						'excerpt' => 'Nous lançons notre site internet pour mieux communiquer nos actions et faciliter votre engagement.',
						'content' => "<p>Bienvenue ! Vous lisez le premier article publié sur notre tout nouveau site. Nous l'avons mis en ligne pour <strong>mieux partager nos actions</strong>, <strong>faciliter votre engagement</strong>, et donner accès à nos ressources.</p><p>Au programme dans les prochaines semaines : comptes rendus de nos AG, calendrier des événements, formulaire de contact direct, et lancement de nos premières pétitions.</p><p>Bonne visite — et <a href=\"/signer/\">contactez-nous</a> pour toute question.</p>",
					),
					array(
						'title'   => 'Trois axes pour 2026',
						'excerpt' => 'Notre conseil d\'administration a défini les priorités d\'action pour 2026 : justice sociale, écologie, démocratie locale.',
						'content' => "<p>Réuni en janvier, notre conseil d'administration a validé les <strong>trois axes prioritaires</strong> qui guideront notre action cette année.</p><p><strong>1. Justice sociale</strong> — Permanences d'accompagnement renforcées, plaidoyer sur les politiques sociales locales, campagne contre la précarité énergétique.</p><p><strong>2. Transition écologique</strong> — Ateliers de sensibilisation dans les écoles, plaidoyer pour un plan vélo ambitieux, mobilisation contre les projets nuisibles à l'environnement.</p><p><strong>3. Démocratie locale</strong> — Animation de conseils de quartier ouverts, suivi du budget municipal, outils de contrôle citoyen.</p><p>Pour participer à l'une de ces actions : <a href=\"/adherer/\">rejoignez-nous</a>.</p>",
					),
					array(
						'title'   => 'Nos comptes 2025 sont publiés',
						'excerpt' => 'Transparence totale : nos comptes annuels sont en accès libre. Recettes, dépenses, bilan détaillé.',
						'content' => "<p>Comme chaque année, nous publions <strong>nos comptes annuels en accès libre</strong>. Vous pouvez les consulter, les commenter, nous interroger.</p><p>Total des recettes 2025 : <strong>42 800€</strong>, dont 78% de cotisations et dons d'adhérent·es (aucun don supérieur à 500€). Le reste vient de subventions publiques (Conseil départemental, Région) et d'un partenariat avec une fondation reconnue d'utilité publique.</p><p>Total des dépenses : <strong>41 100€</strong>, principalement frais de fonctionnement (salle, communication, frais juridiques) et soutien à nos actions de terrain.</p><p>Pour le détail complet : <a href=\"/signer/\">demandez-nous le rapport financier</a>.</p>",
					),
				),
			),
		);
	}

	public static function maybe_rebuild_menu() {
		if ( empty( $_POST['ag_fid_rebuild_menu'] ) || ! current_user_can( 'manage_options' ) ) return;
		check_admin_referer( 'ag_fid_rebuild_menu' );
		if ( method_exists( 'AG_Fid_Pages', 'rebuild_primary_menu' ) ) {
			AG_Fid_Pages::rebuild_primary_menu();
		}
		set_transient( 'ag_fid_menu_rebuilt', 1, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=ag-fid-presets&menu_rebuilt=1' ) );
		exit;
	}

	public static function maybe_apply_preset() {
		if ( empty( $_POST['ag_fid_preset_apply'] ) || ! current_user_can( 'manage_options' ) ) return;
		check_admin_referer( 'ag_fid_apply_preset' );
		$key = sanitize_key( $_POST['ag_fid_preset_apply'] );
		$presets = self::get_presets();
		if ( ! isset( $presets[ $key ] ) ) return;
		$preset = $presets[ $key ];

		// 1. Theme mods (Customizer).
		if ( ! empty( $preset['mods'] ) ) {
			foreach ( $preset['mods'] as $mod => $val ) {
				set_theme_mod( $mod, $val );
			}
		}

		// 2. Pages : update title + content.
		if ( ! empty( $preset['pages'] ) ) {
			foreach ( $preset['pages'] as $slug => $data ) {
				$page = get_page_by_path( $slug );
				if ( $page ) {
					wp_update_post( array(
						'ID'           => $page->ID,
						'post_title'   => $data['title'],
						'post_content' => $data['content'],
					) );
				} else {
					// Cree la page si elle n'existe pas (nouveaux slugs preset).
					wp_insert_post( array(
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'post_title'   => $data['title'],
						'post_name'    => $slug,
						'post_content' => $data['content'],
					) );
				}
			}
		}

		// Helper : wipe + reseed un CPT a partir d'un array.
		$reseed_cpt = function( $cpt, $items, $meta_map = array() ) {
			$old = get_posts( array( 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );
			foreach ( $old as $pid ) wp_delete_post( $pid, true );
			foreach ( $items as $it ) {
				$pid = wp_insert_post( array(
					'post_type'    => $cpt,
					'post_status'  => 'publish',
					'post_title'   => isset( $it['title'] ) ? $it['title'] : '',
					'post_excerpt' => isset( $it['excerpt'] ) ? $it['excerpt'] : '',
					'post_content' => isset( $it['content'] ) ? $it['content'] : '',
				) );
				if ( $pid && ! is_wp_error( $pid ) ) {
					foreach ( $meta_map as $key => $meta_key ) {
						if ( isset( $it[ $key ] ) && $it[ $key ] !== '' ) {
							update_post_meta( $pid, $meta_key, $it[ $key ] );
						}
					}
				}
			}
		};

		// 3. Combats CPT (avec emoji + couleur).
		if ( ! empty( $preset['combats'] ) ) {
			$reseed_cpt( 'ag_combat', $preset['combats'], array(
				'emoji' => '_ag_combat_emoji',
				'color' => '_ag_combat_color',
			) );
		}

		// 4. Evenements CPT.
		if ( ! empty( $preset['events'] ) ) {
			$reseed_cpt( 'ag_evenement', $preset['events'], array(
				'date'  => '_ag_event_date',
				'time'  => '_ag_event_time',
				'end'   => '_ag_event_end',
				'city'  => '_ag_event_city',
				'place' => '_ag_event_place',
			) );
		}

		// 5. Groupes locaux.
		if ( ! empty( $preset['groupes'] ) ) {
			$reseed_cpt( 'ag_groupe', $preset['groupes'] );
		}

		// 6. Petitions (axees habitat/droits).
		if ( ! empty( $preset['petitions'] ) ) {
			$reseed_cpt( 'ag_petition', $preset['petitions'] );
		}

		// 7. Articles standard WP (uniquement les seeds Alliance Groupe par
		// titre, pour preserver les vrais articles de l'utilisateur).
		if ( ! empty( $preset['articles'] ) ) {
			$seed_titles = array(
				'Hôpital public : nous publions notre contre-budget',
				'Hôpital public : nous publions notre contre-budget 2026',
				'Pétition climat : 47 000 signatures en 3 semaines',
				'Nouveau groupe local à Saint-Étienne — bienvenue !',
				'Logement : nos 12 propositions pour 2027',
				'AG 2026 : ce qui a été voté',
			);
			foreach ( $seed_titles as $t ) {
				$existing = get_page_by_title( $t, OBJECT, 'post' );
				if ( $existing ) wp_delete_post( $existing->ID, true );
			}
			foreach ( $preset['articles'] as $a ) {
				wp_insert_post( array(
					'post_type'    => 'post',
					'post_status'  => 'publish',
					'post_title'   => $a['title'],
					'post_excerpt' => isset( $a['excerpt'] ) ? $a['excerpt'] : '',
					'post_content' => isset( $a['content'] ) ? $a['content'] : '',
				) );
			}
		}

		// 8. Rebuild menu principal proprement (evite items dupliques /
		// anciens slugs apres preset applique).
		if ( method_exists( 'AG_Fid_Pages', 'rebuild_primary_menu' ) ) {
			AG_Fid_Pages::rebuild_primary_menu();
		}

		// 9. Force re-check des MAJ theme + plugin (vide les transients)
		delete_site_transient( 'update_themes' );
		delete_site_transient( 'update_plugins' );
		delete_transient( 'ag_asso_theme_remote' );
		delete_transient( 'ag_fid_remote_info' );

		// 10. Purge cache LiteSpeed (si actif)
		if ( defined( 'LSCWP_V' ) && class_exists( 'LiteSpeed\Purge' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		// Purge autres caches communs
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		// Hook generique pour autres plugins de cache
		do_action( 'ag_fid_preset_applied_hook' );

		set_transient( 'ag_fid_preset_applied', $key, 60 );
		// Memorise de maniere persistante (option) le preset applique :
		// empeche l'auto-reseed CPT de wiper ce contenu a chaque MAJ plugin.
		update_option( 'ag_fid_preset_applied', $key );
		wp_safe_redirect( admin_url( 'admin.php?page=ag-fid-presets&applied=1' ) );
		exit;
	}

	public static function maybe_show_applied() {
		$applied = get_transient( 'ag_fid_preset_applied' );
		if ( ! $applied ) return;
		$presets = self::get_presets();
		$label = isset( $presets[ $applied ] ) ? $presets[ $applied ]['label'] : $applied;
		?>
		<div class="notice notice-success is-dismissible" style="border-left-color:#8B1A8B;padding:14px 18px;">
			<p style="margin:0;font-size:1.1rem;"><strong>✓ TOUT RESET terminé : <?php echo esc_html( $label ); ?></strong></p>
			<p style="margin:6px 0 0;">Effectué en 1 clic : <strong>textes Customizer · contenu des pages · combats/événements/groupes/pétitions/articles · menu reset · force update theme+plugin · cache LiteSpeed vidé</strong>.</p>
			<p style="margin:8px 0 0;"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button button-primary">👁 Voir le site</a> <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button">🎨 Customizer</a></p>
		</div>
		<?php
		delete_transient( 'ag_fid_preset_applied' );
	}

	public static function render() {
		$presets = self::get_presets();
		?>
		<div class="wrap" style="max-width:1100px;">
			<h1>🎯 Presets de contenu</h1>
			<p style="font-size:1.05rem;color:#555;">Appliquez en 1 clic un jeu de contenu prêt-à-l'emploi (textes Customizer + pages + combats). <strong>⚠ Cela remplace le contenu actuel</strong> — sauvegardez avant si vous avez beaucoup personnalisé.</p>

			<style>
				.ag-fid-preset-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:20px 24px; margin-bottom:18px; }
				.ag-fid-preset-card h2 { margin:0 0 6px; font-size:1.3rem; }
				.ag-fid-preset-card .desc { color:#555; margin:0 0 14px; }
				.ag-fid-preset-card .meta { font-size:.9em; color:#888; margin:8px 0 14px; }
				.ag-fid-preset-card .meta strong { color:#444; }
			</style>

			<?php foreach ( $presets as $key => $preset ) :
				$mods_count    = isset( $preset['mods'] ) ? count( $preset['mods'] ) : 0;
				$pages_count   = isset( $preset['pages'] ) ? count( $preset['pages'] ) : 0;
				$combats_count = isset( $preset['combats'] ) ? count( $preset['combats'] ) : 0;
				?>
				<div class="ag-fid-preset-card">
					<h2><?php echo esc_html( $preset['label'] ); ?></h2>
					<p class="desc"><?php echo esc_html( $preset['desc'] ); ?></p>
					<p class="meta">
						<strong>Inclus :</strong>
						<?php echo (int) $mods_count; ?> champs Customizer ·
						<?php echo (int) $pages_count; ?> pages ·
						<?php echo (int) $combats_count; ?> combats
					</p>
					<form method="post" onsubmit="return confirm('⚠ Ce bouton fait TOUT en 1 clic :\n• Remplace les textes (Customizer hero/identité/stats)\n• Remplace le contenu des pages\n• Remplace les combats / événements / groupes / pétitions / articles\n• Reset le menu principal\n• Force la vérification des MAJ thème + plugin\n• Vide le cache LiteSpeed\n\nContinuer ?');">
						<?php wp_nonce_field( 'ag_fid_apply_preset' ); ?>
						<input type="hidden" name="ag_fid_preset_apply" value="<?php echo esc_attr( $key ); ?>">
						<button type="submit" class="button button-primary button-hero" style="background:#8B1A8B;border-color:#8B1A8B;font-size:1.15em;padding:14px 28px;height:auto;">
							🚀 TOUT RESET + appliquer ce preset
						</button>
						<p style="font-size:.85em;color:#555;margin-top:10px;">Inclus : preset content + reset menu + force update theme/plugin + purge cache LiteSpeed — <strong>tout en 1 clic</strong>, plus rien d'autre à faire après.</p>
					</form>
				</div>
			<?php endforeach; ?>

			<div class="ag-fid-preset-card" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 Créer votre propre preset</h2>
				<p>Les presets sont définis dans <code>inc/class-ag-fid-presets.php</code> (méthode <code>get_presets()</code>). Pour ajouter le vôtre, copiez le tableau d'un preset existant et adaptez les valeurs.</p>
				<p>Ou plus simple : utilisez le <strong>📖 Guide d'utilisation</strong> pour modifier chaque champ manuellement dans l'admin WordPress.</p>
			</div>
		</div>
		<?php
	}
}

AG_Fid_Presets::init();
