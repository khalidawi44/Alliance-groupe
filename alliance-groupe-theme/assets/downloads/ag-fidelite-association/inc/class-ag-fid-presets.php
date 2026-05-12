<?php
/**
 * Pack Fidélité — Presets de contenu prêts à appliquer en 1 clic.
 *
 * Permet a un client (ou un installateur Alliance Groupe) d'appliquer
 * en une fois un jeu de contenu cible (Customizer + pages + CPT)
 * sans avoir a copier-coller manuellement chaque champ.
 *
 * EXEMPLE : le preset 'lfi_nantes_clos_toreau' applique tous les textes
 * du groupe local LFI Nantes Sud Clos Toreau (logement, violences
 * policieres, services publics, justice climatique, democratie reelle,
 * solidarite internationale).
 *
 * Le preset par defaut 'generic_militant' = contenu generique deja
 * present par seed initial. Aucun preset n'est applique automatiquement.
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

			'lfi_nantes_clos_toreau' => array(
				'label' => '✊ LFI Nantes Sud Clos Toreau',
				'desc'  => 'Groupe local La France Insoumise — 3 axes : habitat, recueil de témoignages (violences/intimidations), accompagnement administratif et juridique des habitant·es.',
				'mods'  => array(
					'ag_asso_name'         => 'LFI Nantes Sud Clos Toreau',
					'ag_asso_baseline'     => 'Groupe local La France Insoumise',
					'ag_asso_slogan'       => 'LFI Nantes Sud Clos Toreau',
					'ag_asso_hero_title'   => 'Habitat digne, défense des droits, solidarité de quartier',
					'ag_asso_hero_sub'     => 'Notre groupe local accompagne les habitant·es de Nantes Sud Clos Toreau dans leurs démarches administratives et juridiques, recueille les témoignages de violences et porte le combat pour un habitat décent.',
					'ag_asso_cta_label'    => 'Rejoindre le groupe',
					'ag_asso_cta_url'      => '/adherer/',
					'ag_asso_cta2_label'   => 'Témoigner / demander de l\'aide',
					'ag_asso_cta2_url'     => '/signer/',
					'ag_asso_stat1_value'  => '1',
					'ag_asso_stat1_label'  => 'groupe local Clos Toreau',
					'ag_asso_stat2_value'  => '',
					'ag_asso_stat2_label'  => 'adhérent·es actif·ves',
					'ag_asso_stat3_value'  => '',
					'ag_asso_stat3_label'  => 'témoignages recueillis',
				),
				'pages' => array(
					'qui-sommes-nous' => array(
						'title'   => 'Qui sommes-nous',
						'content' => "<!-- wp:paragraph -->\n<p>Nous sommes le <strong>groupe local La France Insoumise de Nantes Sud Clos Toreau</strong>. Des habitant·es du quartier, voisin·es, locataires, salarié·es, chômeur·euses, étudiant·es, retraité·es engagé·es au quotidien pour défendre les droits de notre communauté.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Notre action concrète</h2>\n<!-- /wp:heading -->\n\n<!-- wp:list -->\n<ul><li><strong>L'habitat</strong> : nous luttons aux côtés des locataires pour un logement digne, contre les loyers abusifs et les expulsions injustes.</li><li><strong>Le recueil de témoignages</strong> : nous documentons les violences, intimidations et discriminations vécues dans le quartier, pour les rendre visibles et combattues.</li><li><strong>L'accompagnement administratif et juridique</strong> : nous aidons les habitant·es à faire valoir leurs droits — CAF, préfecture, bailleur social, démarches juridiques.</li></ul>\n<!-- /wp:list -->\n\n<!-- wp:heading -->\n<h2>Comment on fonctionne</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Nous nous réunissons régulièrement dans le quartier. Réunions ouvertes à tou·tes, sans condition de prise de carte. Décisions collectives. Aucun·e élu·e, aucun·e permanent·e — uniquement des bénévoles habitant·es du quartier.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Pour nous rejoindre ou nous solliciter, contactez-nous via le formulaire de la page <a href=\"/signer/\">Témoigner / demander de l'aide</a>.</p>\n<!-- /wp:paragraph -->",
					),
					'manifeste' => array(
						'title'   => 'Notre manifeste',
						'content' => "<!-- wp:paragraph -->\n<p>À Nantes Sud Clos Toreau, nous nous organisons autour de trois axes concrets et complémentaires : <strong>l'habitat digne, le recueil des témoignages, l'accompagnement administratif et juridique</strong>. Trois leviers pour défendre les droits des habitant·es de notre quartier.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>Nous sommes des habitant·es de Nantes Sud.</strong> Pas des élu·es, pas des permanent·es de parti. Des voisin·es, des locataires, des salarié·es, des étudiant·es, des retraité·es. Nous croyons qu'une autre société est possible — et nous nous organisons pour la construire à partir du quartier.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>L'habitat avant tout</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Quand des familles vivent dans des logements indignes — moisissures, infiltrations, ascenseurs en panne — quand les loyers explosent et que les expulsions se multiplient, la République trahit sa promesse d'égalité. Nous luttons pour un habitat digne pour tou·tes.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Documenter pour combattre</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Nous recueillons les témoignages des habitant·es : violences, intimidations, discriminations, contrôles abusifs. Pour les rendre visibles, les chiffrer, et porter publiquement nos demandes.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Aider concrètement</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>L'accompagnement administratif et juridique est notre troisième pilier. CAF, préfecture, bailleur social, recours juridique : nous épaulons celles et ceux qui en ont besoin pour faire valoir leurs droits.</p>\n<!-- /wp:paragraph -->",
					),
					'combats' => array(
						'title'   => 'Nos combats',
						'content' => "<!-- wp:paragraph -->\n<p>Trois axes prioritaires que nous portons concrètement dans le quartier du Clos Toreau : <strong>l'habitat</strong>, le <strong>recueil de témoignages</strong>, l'<strong>accompagnement administratif et juridique</strong>.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_combats]\n<!-- /wp:shortcode -->",
					),
					'evenements' => array(
						'title'   => 'Mobilisations à venir',
						'content' => "<!-- wp:paragraph -->\n<p>Réunions publiques, permanences d'accompagnement juridique, distributions de tracts, mobilisations sur le terrain. Retrouvez-nous dans le quartier du Clos Toreau et à Nantes Sud.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_evenements]\n<!-- /wp:shortcode -->",
					),
					'groupes' => array(
						'title'   => 'Le groupe Clos Toreau',
						'content' => "<!-- wp:paragraph -->\n<p>Notre groupe local LFI couvre Nantes Sud Clos Toreau et les quartiers voisins. Réunions ouvertes à tou·tes, sans condition de prise de carte.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_groupes]\n<!-- /wp:shortcode -->",
					),
					'actu' => array(
						'title'   => 'Actualités du quartier',
						'content' => "<!-- wp:paragraph -->\n<p>Suivez les actualités de notre groupe local : témoignages recueillis, actions sur l'habitat, accompagnements réussis, comptes rendus de réunions.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_actu]\n<!-- /wp:shortcode -->",
					),
					'signer' => array(
						'title'   => 'Témoigner / demander de l\'aide',
						'content' => "<!-- wp:paragraph -->\n<p>Vous avez subi une violence, une intimidation, une discrimination ? Vous avez besoin d'aide pour une démarche administrative ou juridique ? Vous voulez signaler un problème dans votre logement ? Remplissez le formulaire ci-dessous, nous reviendrons vers vous rapidement.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_signer]\n<!-- /wp:shortcode -->",
					),
					'don' => array(
						'title'   => 'Soutenir le groupe',
						'content' => "<!-- wp:paragraph -->\n<p>Indépendants des partis, des grandes entreprises et des grands donateurs, nous ne tenons que par vous. Chaque euro permet d'imprimer des tracts, louer des salles, financer les frais juridiques d'un dossier difficile.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_don]\n<!-- /wp:shortcode -->",
					),
					'adherer' => array(
						'title'   => 'Rejoindre le groupe',
						'content' => "<!-- wp:paragraph -->\n<p>Vous habitez Nantes Sud Clos Toreau ou un quartier voisin ? Vous voulez nous rejoindre, nous donner un coup de main, ou simplement venir voir ce qu'on fait ?</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>Nos réunions sont ouvertes à tou·tes</strong>, sans condition de prise de carte. Pas besoin d'être encarté·e à La France Insoumise pour participer — on accueille toutes les bonnes volontés.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Pour nous contacter : <a href=\"/signer/\">remplissez le formulaire</a> en précisant ce qui vous intéresse (réunion, accompagnement, témoignage, distribution de tracts...).</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_adhesion]\n<!-- /wp:shortcode -->",
					),
					'mon-compte' => array(
						'title'   => 'Espace adhérent·e',
						'content' => "<!-- wp:paragraph -->\n<p>Espace réservé aux adhérent·es du groupe local LFI Nantes Sud Clos Toreau : comptes rendus de réunions, ressources internes, badge numérique.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_compte]\n<!-- /wp:shortcode -->",
					),
					'petitions' => array(
						'title'   => 'Nos pétitions',
						'content' => "<!-- wp:paragraph -->\n<p>Pétitions portées par notre groupe local autour de l'habitat et de la défense des droits des habitant·es du Clos Toreau et de Nantes Sud.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_petitions]\n<!-- /wp:shortcode -->",
					),
					'reunion' => array(
						'title'   => 'Réunion en ligne',
						'content' => "<!-- wp:paragraph -->\n<p>Salle de visioconférence ouverte aux adhérent·es et sympathisant·es du groupe pour les réunions à distance (commission habitat, préparation d'actions, AG).</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_visio]\n<!-- /wp:shortcode -->",
					),
					'rendez-vous' => array(
						'title'   => 'Permanence — prendre rendez-vous',
						'content' => "<!-- wp:paragraph -->\n<p>Pour un accompagnement administratif ou juridique personnalisé, prenez rendez-vous avec un·e bénévole du groupe. Permanences confidentielles, sans rendez-vous obligatoire mais conseillé.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_rdv]\n<!-- /wp:shortcode -->",
					),
				),
				'combats' => array(
					array(
						'title'   => 'L\'habitat',
						'emoji'   => '🏠',
						'color'   => '#3B5998',
						'excerpt' => 'Lutte aux côtés des locataires pour un logement digne, contre les loyers abusifs et les expulsions injustes.',
						'content' => "Premier axe d'action de notre groupe local. À Clos Toreau comme dans tout Nantes Sud, des familles vivent dans des logements indignes : moisissures, infiltrations, ascenseurs en panne, attentes interminables de relogement.\n\nNous luttons concrètement aux côtés des locataires : interpellation des bailleurs sociaux, accompagnement des recours, mobilisations devant les logements insalubres, pétitions pour l'encadrement des loyers à Nantes Métropole.",
					),
					array(
						'title'   => 'Recueil de témoignages',
						'emoji'   => '🎙️',
						'color'   => '#E10F1A',
						'excerpt' => 'Nous documentons les violences, intimidations et discriminations vécues dans le quartier pour les rendre visibles.',
						'content' => "Deuxième axe : recueillir, documenter, faire entendre. Les habitant·es de Clos Toreau et Nantes Sud vivent au quotidien des situations qui doivent être rendues publiques : contrôles abusifs, intimidations, discriminations, violences institutionnelles, abus de bailleurs.\n\nNous recueillons leurs témoignages, les anonymisons si nécessaire, et les portons publiquement pour qu'ils soient entendus — par les médias, les élu·es, les administrations.",
					),
					array(
						'title'   => 'Accompagnement administratif et juridique',
						'emoji'   => '⚖️',
						'color'   => '#1F8A3D',
						'excerpt' => 'Nous aidons les habitant·es à faire valoir leurs droits : CAF, préfecture, bailleur social, recours juridiques.',
						'content' => "Troisième axe : aider concrètement. Beaucoup d'habitant·es se retrouvent seul·es face à des démarches administratives complexes, ou face à des décisions injustes qu'il faudrait contester en justice.\n\nNous accompagnons celles et ceux qui en ont besoin : rédaction de courriers à la CAF ou au bailleur, prise de rendez-vous avec un·e avocat·e partenaire, médiation préfecture, dossiers de surendettement, recours administratifs. Service entièrement bénévole et confidentiel.",
					),
				),

				// Evenements : UNIQUEMENT Nantes Sud Clos Toreau (pas Marseille,
				// pas Lyon, pas Paris). Trois evenements types qu'un groupe local
				// porte concretement sur son quartier.
				'events' => array(
					array(
						'title'   => 'Permanence accompagnement administratif',
						'date'    => '', 'time' => '14:00', 'end' => '17:00',
						'city'    => 'Nantes Sud Clos Toreau',
						'place'   => 'À préciser (lieu de permanence du groupe)',
						'content' => "Permanence ouverte à tou·tes les habitant·es du quartier qui ont besoin d'aide pour : courriers CAF, contestation de loyer, dossier bailleur social, démarches préfecture, surendettement, recours juridique.\n\n<strong>Sans rendez-vous, gratuit, confidentiel.</strong> Apportez vos documents (courriers, factures, contrats) si possible.",
					),
					array(
						'title'   => 'Recueil de témoignages — habitat',
						'date'    => '', 'time' => '18:00', 'end' => '20:00',
						'city'    => 'Nantes Sud Clos Toreau',
						'place'   => 'À préciser (lieu de réunion du groupe)',
						'content' => "Soirée d'écoute des habitant·es sur les conditions de logement dans le quartier : insalubrité, loyers, expulsions, charges abusives, problèmes avec le bailleur.\n\nNous recueillons vos témoignages (anonymisés sur demande) pour porter publiquement nos demandes auprès du bailleur, de la mairie, et des médias.",
					),
					array(
						'title'   => 'Réunion publique mensuelle du groupe',
						'date'    => '', 'time' => '19:00', 'end' => '21:00',
						'city'    => 'Nantes Sud Clos Toreau',
						'place'   => 'À préciser (lieu de réunion du groupe)',
						'content' => "Réunion mensuelle ouverte à tou·tes : actu du quartier, retour sur les actions menées, préparation des prochaines mobilisations.\n\nVenez même si vous n'êtes jamais venu·e — on vous explique tout.",
					),
				),

				// Groupes locaux : UNIQUEMENT le notre. Pas Lyon ni Marseille.
				'groupes' => array(
					array(
						'title'   => 'Nantes Sud Clos Toreau',
						'excerpt' => 'Notre seul groupe local : couvre Nantes Sud Clos Toreau et les quartiers voisins.',
						'content' => "Notre groupe local LFI couvre <strong>Nantes Sud Clos Toreau et les quartiers limitrophes</strong>.\n\nNous nous réunissons régulièrement dans le quartier (lieu et horaires précisés sur la page Événements).\n\nRéunions ouvertes à tou·tes les habitant·es, sans condition de prise de carte. Pas besoin d'être encarté·e à La France Insoumise pour participer.\n\nPour nous rejoindre ou nous contacter : <a href=\"/signer/\">formulaire de contact</a>.",
					),
				),

				// Petitions : axees habitat/droits, pas de petition climat globale
				// ou autre theme hors-perimeter du groupe.
				'petitions' => array(
					array(
						'title'   => 'Pour l\'encadrement effectif des loyers à Nantes Métropole',
						'excerpt' => 'Nantes Métropole peut activer le dispositif d\'encadrement des loyers depuis 2019. Demandons-le.',
						'content' => "Nantes Métropole est en zone tendue depuis 2019 et peut activer le <strong>dispositif d'encadrement des loyers</strong>. Lille, Lyon, Bordeaux, Montpellier l'ont fait. Nantes ne le fait toujours pas.\n\nNous demandons à Nantes Métropole d'activer immédiatement ce dispositif pour protéger les locataires de notre quartier et de toute l'agglomération.",
					),
					array(
						'title'   => 'Réfection des logements insalubres au Clos Toreau',
						'excerpt' => 'Plan de rénovation immédiat des logements signalés comme insalubres dans notre quartier.',
						'content' => "Nous interpellons Nantes Métropole Habitat et la Ville de Nantes : <strong>plusieurs logements du Clos Toreau présentent des problèmes documentés</strong> (moisissures, infiltrations, ascenseurs en panne récurrente).\n\nNous demandons un plan de rénovation chiffré et calendrié, avec représentation des locataires dans le suivi des travaux.",
					),
					array(
						'title'   => 'Permanence d\'accès au droit dans nos quartiers',
						'excerpt' => 'Permanences d\'avocat·es et juristes gratuites dans les quartiers populaires de Nantes.',
						'content' => "L'accès au droit est inégalement réparti dans la métropole. Nous demandons que la Ville et le Département financent des <strong>permanences d'accès au droit gratuites et hebdomadaires</strong> dans les quartiers populaires, dont Clos Toreau.\n\nLe groupe local fait sa part en bénévole — les institutions doivent prendre le relais.",
					),
				),

				// Articles : actu LFI Clos Toreau uniquement (pas national, pas
				// Saint-Etienne ou Toulouse). Trois articles types pour amorcer.
				'articles' => array(
					array(
						'title'   => 'Le groupe LFI Clos Toreau a démarré ses permanences',
						'excerpt' => 'Première permanence d\'accompagnement administratif et juridique tenue dans le quartier. Cinq habitant·es accueilli·es.',
						'content' => "<p>Notre groupe local a tenu sa première permanence d'accompagnement administratif et juridique cette semaine. <strong>Cinq habitant·es</strong> sont venu·es nous rencontrer, principalement pour des questions de loyer, dossier CAF et recours bailleur.</p><p>Nous renouvelons ces permanences chaque semaine. Sans rendez-vous, gratuit, confidentiel. Détails sur la page <a href=\"/evenements/\">Événements</a>.</p>",
					),
					array(
						'title'   => 'Encadrement des loyers : on relance Nantes Métropole',
						'excerpt' => 'Notre pétition pour l\'activation du dispositif d\'encadrement des loyers à Nantes Métropole prend de l\'ampleur.',
						'content' => "<p>Depuis 2019, Nantes Métropole peut activer le dispositif d'encadrement des loyers — comme l'ont fait Lille, Lyon, Bordeaux et Montpellier. Notre groupe a relancé cette demande auprès des élu·es métropolitain·es.</p><p>La <a href=\"/petitions/\">pétition</a> est ouverte à la signature. Pour celles et ceux qui veulent agir avec nous, <a href=\"/signer/\">contactez-nous</a>.</p>",
					),
					array(
						'title'   => 'Témoignages habitat : ce qu\'on a recueilli ce trimestre',
						'excerpt' => 'Synthèse des témoignages d\'habitant·es du Clos Toreau sur leurs conditions de logement.',
						'content' => "<p>Nous avons recueilli ce trimestre des témoignages d'habitant·es sur leurs conditions de logement au Clos Toreau : moisissures persistantes, ascenseurs en panne récurrente, charges contestables, lenteur des interventions du bailleur.</p><p>Une synthèse anonymisée sera transmise au bailleur et à la Ville de Nantes la semaine prochaine. Si vous voulez ajouter votre témoignage, <a href=\"/signer/\">le formulaire est ici</a>.</p>",
					),
				),
			),
		);
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
				if ( ! $page ) continue;
				wp_update_post( array(
					'ID'           => $page->ID,
					'post_title'   => $data['title'],
					'post_content' => $data['content'],
				) );
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

		// 4. Evenements CPT (Clos Toreau uniquement, pas Marseille / Lyon / etc.).
		if ( ! empty( $preset['events'] ) ) {
			$reseed_cpt( 'ag_evenement', $preset['events'], array(
				'date'  => '_ag_event_date',
				'time'  => '_ag_event_time',
				'end'   => '_ag_event_end',
				'city'  => '_ag_event_city',
				'place' => '_ag_event_place',
			) );
		}

		// 5. Groupes locaux (uniquement Clos Toreau).
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

		set_transient( 'ag_fid_preset_applied', $key, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=ag-fid-presets&applied=1' ) );
		exit;
	}

	public static function maybe_show_applied() {
		$applied = get_transient( 'ag_fid_preset_applied' );
		if ( ! $applied ) return;
		$presets = self::get_presets();
		$label = isset( $presets[ $applied ] ) ? $presets[ $applied ]['label'] : $applied;
		?>
		<div class="notice notice-success is-dismissible" style="border-left-color:#1F8A3D;padding:14px 18px;">
			<p style="margin:0;font-size:1.05rem;"><strong>✓ Preset appliqué : <?php echo esc_html( $label ); ?></strong></p>
			<p style="margin:6px 0 0;">Tous les textes et combats ont été mis à jour. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">👁 Voir le site</a> ou <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">🎨 personnaliser davantage</a>.</p>
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
					<form method="post" onsubmit="return confirm('⚠ Cela va REMPLACER le contenu actuel (Customizer hero/identité/stats + textes des pages + combats existants). Continuer ?');">
						<?php wp_nonce_field( 'ag_fid_apply_preset' ); ?>
						<input type="hidden" name="ag_fid_preset_apply" value="<?php echo esc_attr( $key ); ?>">
						<button type="submit" class="button button-primary" style="background:#E10F1A;border-color:#E10F1A;">
							🎯 Appliquer ce preset
						</button>
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
