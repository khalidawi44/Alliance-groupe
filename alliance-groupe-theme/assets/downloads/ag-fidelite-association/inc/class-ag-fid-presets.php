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
					// Stats accueil : nombre groupes LFI (national + Loire-Atlantique
					// + notre groupe local Clos Toreau). Editable plus tard via
					// Customizer > Contenu accueil pour ajuster avec les vrais
					// chiffres a jour.
					'ag_asso_stat1_value'  => '1 200+',
					'ag_asso_stat1_label'  => 'groupes LFI en France',
					'ag_asso_stat2_value'  => '30+',
					'ag_asso_stat2_label'  => 'groupes en Loire-Atlantique',
					'ag_asso_stat3_value'  => '1',
					'ag_asso_stat3_label'  => 'groupe au Clos Toreau',
					// UUID du groupe sur Action Populaire — utilise dans
					// les boutons popup don/adhesion (garde l'utilisateur
					// visuellement sur le site, popup centree)
					'ag_asso_ap_group_uuid' => '3f07362c-8238-4a63-9b0c-4128e9ec6ede',
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
						'title'   => 'Trouver mon groupe local LFI',
						'content' => "<!-- wp:paragraph -->\n<p>La carte officielle Action Populaire ci-dessous liste tous les groupes locaux LFI en France. Zoomez sur votre ville pour trouver le vôtre, ou rejoignez directement notre groupe Nantes Sud Clos Toreau.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:html -->\n<p style=\"text-align:center;margin:24px 0;\"><a href=\"https://actionpopulaire.fr/groupes/3f07362c-8238-4a63-9b0c-4128e9ec6ede/\" class=\"ag-asso-btn ag-asso-btn--primary\" data-ag-popup data-popup-width=\"1000\" data-popup-height=\"800\" rel=\"noopener\">✊ Rejoindre le groupe LFI Nantes Sud Clos Toreau</a></p>\n<!-- /wp:html -->\n\n<!-- wp:heading -->\n<h2>🗺️ Carte des groupes locaux LFI</h2>\n<!-- /wp:heading -->\n\n<!-- wp:html -->\n<div style=\"max-width:1100px;margin:0 auto;text-align:center;\">\n<p style=\"margin-bottom:14px;\"><a href=\"https://actionpopulaire.fr/groupes/carte/\" class=\"ag-asso-btn ag-asso-btn--primary\" data-ag-popup data-popup-width=\"1100\" data-popup-height=\"800\" rel=\"noopener\">🗺️ Ouvrir la carte plein écran</a></p>\n<div style=\"position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;background:#f5f5f5;\">\n<iframe src=\"https://actionpopulaire.fr/groupes/carte/\" width=\"100%\" height=\"600\" style=\"border:0;display:block;\" loading=\"lazy\" title=\"Carte des groupes locaux La France Insoumise\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>\n<noscript><p style=\"padding:40px;text-align:center;color:#666;\">La carte interactive nécessite JavaScript. <a href=\"https://actionpopulaire.fr/groupes/carte/\" target=\"_blank\" rel=\"noopener\">Ouvrir la carte sur Action Populaire</a></p></noscript>\n</div>\n<p style=\"margin-top:10px;font-size:.85em;color:#888;\">Si la carte ne s'affiche pas, cliquez sur le bouton ci-dessus pour l'ouvrir dans une fenêtre dédiée.</p>\n</div>\n<!-- /wp:html -->\n\n<!-- wp:heading -->\n<h2>📧 Pas de groupe près de chez vous ?</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Si vous habitez le quartier Clos Toreau ou Nantes Sud, rejoignez-nous directement (bouton ci-dessus). Sinon, contactez-nous via le formulaire suivant et nous vous orienterons vers le groupe le plus proche ou vous accompagnerons pour en créer un.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_signer]\n<!-- /wp:shortcode -->",
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
						'content' => "<!-- wp:paragraph -->\n<p>Indépendants des partis, des grandes entreprises et des grands donateurs, nous ne tenons que par vous. Chaque euro permet d'imprimer des tracts, louer des salles, financer les frais juridiques d'un dossier difficile.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>📌 Don sécurisé via Action Populaire (LFI)</strong> — le don est attribué à notre groupe local. 66% de votre don est déductible de vos impôts, reçu fiscal envoyé automatiquement par email.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:html -->\n<p style=\"text-align:center;margin:24px 0;\"><a href=\"https://actionpopulaire.fr/dons/?group=3f07362c-8238-4a63-9b0c-4128e9ec6ede\" class=\"ag-asso-btn ag-asso-btn--primary\" data-ag-popup data-popup-width=\"980\" data-popup-height=\"820\" rel=\"noopener\">💛 Faire un don à notre groupe</a></p>\n<p style=\"text-align:center;font-size:.9em;color:#888;\">Une fenêtre sécurisée Action Populaire s'ouvrira. Vous restez sur notre site pendant tout le processus.</p>\n<!-- /wp:html -->\n\n<!-- wp:heading {\"level\":3} -->\n<h3>Pour les dons par chèque</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Vous pouvez aussi envoyer un chèque à l'ordre de <strong>AFLFI</strong> en précisant au dos : « Pour le groupe Nantes Sud Clos Toreau » (adresse postale sur demande via le <a href=\"/signer/\">formulaire de contact</a>).</p>\n<!-- /wp:paragraph -->",
					),
					'adherer' => array(
						'title'   => 'Rejoindre le groupe',
						'content' => "<!-- wp:paragraph -->\n<p>Vous habitez Nantes Sud Clos Toreau ou un quartier voisin ? Vous voulez nous rejoindre, nous donner un coup de main, ou simplement venir voir ce qu'on fait ?</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>Nos réunions sont ouvertes à tou·tes</strong>, sans condition de prise de carte. Pas besoin d'être encarté·e à La France Insoumise pour participer — on accueille toutes les bonnes volontés.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading {\"level\":3} -->\n<h3>Option 1 — Rejoindre officiellement le groupe sur Action Populaire</h3>\n<!-- /wp:heading -->\n\n<!-- wp:html -->\n<p style=\"text-align:center;margin:20px 0;\"><a href=\"https://actionpopulaire.fr/groupes/3f07362c-8238-4a63-9b0c-4128e9ec6ede/\" class=\"ag-asso-btn ag-asso-btn--primary\" data-ag-popup data-popup-width=\"1000\" data-popup-height=\"800\" rel=\"noopener\">✊ S'inscrire sur Action Populaire</a></p>\n<p style=\"text-align:center;font-size:.9em;color:#888;\">Une fenêtre s'ouvre — vous restez sur notre site.</p>\n<!-- /wp:html -->\n\n<!-- wp:heading {\"level\":3} -->\n<h3>Option 2 — Nous contacter directement</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p><a href=\"/signer/\">Remplissez le formulaire de contact</a> en précisant ce qui vous intéresse (réunion, accompagnement, témoignage, distribution de tracts, etc.) — un·e bénévole du groupe vous recontactera rapidement.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_adhesion]\n<!-- /wp:shortcode -->",
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
					// NOUVELLE PAGE (slug different, zero cache herite). Contient
					// la carte AP + le bouton popup d'inscription au groupe LFI.
					'rejoindre-lfi' => array(
						'title'   => 'Rejoindre LFI Clos Toreau',
						'content' => "<!-- wp:paragraph -->\n<p style=\"text-align:center;font-size:1.1em;\">Rejoignez notre groupe LFI Nantes Sud Clos Toreau ou trouvez le groupe le plus proche de chez vous sur la carte officielle Action Populaire. <strong>Aucun compte requis pour voir la carte.</strong></p>\n<!-- /wp:paragraph -->\n\n<!-- wp:html -->\n<p style=\"text-align:center;margin:40px 0;\"><a href=\"https://actionpopulaire.fr/groupes/3f07362c-8238-4a63-9b0c-4128e9ec6ede/\" class=\"ag-asso-btn ag-asso-btn--primary\" data-ag-popup data-popup-width=\"1000\" data-popup-height=\"800\" rel=\"noopener\" style=\"display:inline-block;padding:22px 44px;font-size:1.3em;border-radius:8px;\">✊ Rejoindre LFI Nantes Sud Clos Toreau</a></p>\n<p style=\"text-align:center;font-size:.9em;color:#666;margin-top:-20px;\">→ S'ouvre dans une petite fenêtre. Vous restez sur ce site.</p>\n<!-- /wp:html -->\n\n<!-- wp:heading -->\n<h2 style=\"text-align:center;\">🗺️ Carte de tous les groupes LFI en France</h2>\n<!-- /wp:heading -->\n\n<!-- wp:html -->\n<div style=\"max-width:1100px;margin:20px auto;\">\n<p style=\"text-align:center;margin-bottom:14px;\"><a href=\"https://actionpopulaire.fr/groupes/carte/\" class=\"ag-asso-btn ag-asso-btn--primary\" data-ag-popup data-popup-width=\"1100\" data-popup-height=\"800\" rel=\"noopener\" style=\"display:inline-block;padding:14px 28px;\">🗺️ Ouvrir la carte plein écran</a></p>\n<div style=\"position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;background:#f5f5f5;\"><iframe src=\"https://actionpopulaire.fr/groupes/carte/\" width=\"100%\" height=\"600\" style=\"border:0;display:block;\" loading=\"lazy\" title=\"Carte des groupes LFI\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe></div>\n<p style=\"text-align:center;margin-top:10px;font-size:.85em;color:#888;\">Si la carte ne s'affiche pas, cliquez sur le bouton ci-dessus.</p>\n</div>\n<!-- /wp:html -->",
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
				// pas Lyon, pas Paris). Trois evenements types recurrents qu'un
				// groupe local porte concretement sur son quartier.
				// Dates : 2-3 semaines apres l'install (rythme realiste).
				// L'utilisateur peut editer chaque evenement pour mettre les
				// vraies dates/lieux dans Evenements > admin.
				'events' => array(
					array(
						'title'   => 'Permanence accompagnement (tous les samedis)',
						'date'    => '2026-05-24', 'time' => '14:00', 'end' => '17:00',
						'city'    => 'Nantes Sud Clos Toreau',
						'place'   => 'Maison de quartier du Clos Toreau (à confirmer)',
						'content' => "Permanence ouverte à tou·tes les habitant·es du quartier qui ont besoin d'aide pour : courriers CAF, contestation de loyer, dossier bailleur social, démarches préfecture, surendettement, recours juridique.\n\n<strong>Sans rendez-vous, gratuit, confidentiel.</strong> Apportez vos documents (courriers, factures, contrats) si possible.\n\nPour préparer votre venue ou prendre un créneau prioritaire : voir page <a href=\"/rendez-vous/\">Rendez-vous</a>.",
					),
					array(
						'title'   => 'Recueil de témoignages habitat',
						'date'    => '2026-05-30', 'time' => '18:00', 'end' => '20:00',
						'city'    => 'Nantes Sud Clos Toreau',
						'place'   => 'Centre social du Clos Toreau (à confirmer)',
						'content' => "Soirée d'écoute des habitant·es sur les conditions de logement dans le quartier : insalubrité, loyers, expulsions, charges abusives, problèmes avec le bailleur.\n\nNous recueillons vos témoignages (anonymisés sur demande) pour porter publiquement nos demandes auprès du bailleur (Nantes Métropole Habitat), de la mairie et des médias.\n\n<strong>Café et thé offerts.</strong>",
					),
					array(
						'title'   => 'Réunion publique mensuelle du groupe',
						'date'    => '2026-06-03', 'time' => '19:00', 'end' => '21:00',
						'city'    => 'Nantes Sud Clos Toreau',
						'place'   => 'Maison de quartier du Clos Toreau (à confirmer)',
						'content' => "Réunion mensuelle ouverte à tou·tes : actu du quartier, retour sur les actions menées (permanences, témoignages, mobilisations), préparation des prochaines initiatives.\n\nVenez même si vous n'êtes jamais venu·e — on vous explique tout en début de séance.\n\n<strong>Réunion ouverte, sans condition de prise de carte.</strong>",
					),
				),

				// Groupes locaux : UNIQUEMENT le notre. Pas Lyon ni Marseille.
				'groupes' => array(
					array(
						'title'   => 'LFI Nantes Sud Clos Toreau',
						'excerpt' => 'Groupe local LFI couvrant le quartier Clos Toreau et Nantes Sud. Permanences hebdomadaires + réunions publiques mensuelles.',
						'content' => "<strong>Notre groupe local LFI couvre Nantes Sud Clos Toreau et les quartiers limitrophes.</strong>\n\n<strong>Trois activités régulières :</strong>\n• Permanences d'accompagnement administratif et juridique (tous les samedis 14h-17h)\n• Recueil de témoignages habitat (jeudis soir mensuels)\n• Réunion publique mensuelle (premier mardi du mois)\n\n<strong>Trois axes prioritaires :</strong>\n• 🏠 L'habitat — encadrement loyers, lutte contre l'insalubrité, défense des locataires\n• 🎙️ Recueil de témoignages — documenter violences et discriminations vécues dans le quartier\n• ⚖️ Accompagnement administratif et juridique — CAF, préfecture, bailleur, recours\n\nRéunions ouvertes à tou·tes les habitant·es, sans condition de prise de carte. Pas besoin d'être encarté·e à La France Insoumise pour participer.\n\n<strong>📍 Notre fiche officielle sur Action Populaire :</strong>\n<a href=\"https://actionpopulaire.fr/groupes/3f07362c-8238-4a63-9b0c-4128e9ec6ede/\" target=\"_blank\" rel=\"noopener\">→ actionpopulaire.fr/groupes/...</a>\n\n<strong>Pour nous rejoindre :</strong> <a href=\"/signer/\">formulaire de contact</a> ou venez directement à une permanence.",
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
