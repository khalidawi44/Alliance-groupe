<?php
/**
 * Presets metier coach — 5 specialites pour adapter le theme en 1 clic
 * (coach sportif, coach de vie, coach business, coach mental, coach
 * generaliste). Chaque preset configure le hero, la couleur d'accent,
 * la grille de services + page contents.
 *
 * Apparait dans : "Apparence > 🎯 Configuration metier".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Coach_Presets {

	public static function get_presets() {
		return array(

			'coach_general' => array(
				'icon'  => '✨',
				'label' => 'Coach généraliste',
				'desc'  => 'Coach polyvalent : développement personnel, transition de vie, objectifs perso/pro. Pour coach multi-spécialités.',
				'mods'  => array(
					'ag_color_accent'        => '#2E86AB',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Coach',
					'ag_hero_subtitle'       => 'Atteignez vos objectifs avec un accompagnement personnalisé. Séance découverte offerte, suivi en présentiel ou en visio.',
					'ag_hero_button'         => 'Réserver une séance',
					'ag_hero_button_url'     => '/devis/',
					'ag_coach_metier_nom'    => 'Coach généraliste',
					'ag_coach_hero_image'    => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=1600&q=80',
					'ag_coach_testi_1'       => "Accompagnement humain et bienveillant. En 6 mois, j'ai retrouvé clarté et énergie. Merci.|Sophie M.|Lyon",
					'ag_coach_testi_2'       => "Démarche structurée, outils concrets, j'ai atteint mes objectifs pros et perso. Top.|Karim B.|Marseille",
					'ag_coach_testi_3'       => "Coach à l'écoute, qui pose les bonnes questions. Transformation profonde.|Laure D.|Paris",
				),
				'services' => array(
					array( 'emoji' => '🎯', 'title' => 'Séance découverte' ),
					array( 'emoji' => '💬', 'title' => 'Coaching individuel' ),
					array( 'emoji' => '📞', 'title' => 'Coaching en visio' ),
					array( 'emoji' => '📦', 'title' => 'Forfait 10 séances' ),
					array( 'emoji' => '🚀', 'title' => 'Programme 3 mois' ),
					array( 'emoji' => '👥', 'title' => 'Coaching de groupe' ),
					array( 'emoji' => '🌟', 'title' => 'Bilan personnel' ),
					array( 'emoji' => '✉️', 'title' => 'Suivi messagerie' ),
				),
				'how' => array(
					array( 'emoji' => '☎️', 'title' => 'Séance découverte gratuite', 'desc' => '30 minutes pour faire connaissance, comprendre votre objectif et voir si nous sommes alignés.' ),
					array( 'emoji' => '📋', 'title' => 'Plan d\'accompagnement sur mesure', 'desc' => 'Objectifs SMART, étapes, outils, calendrier de séances adapté à votre rythme.' ),
					array( 'emoji' => '🎉', 'title' => 'Atteinte de vos objectifs', 'desc' => 'Suivi régulier, mesure des progrès, ajustements. Vous repartez avec des outils pour durer.' ),
				),
				'faq' => array(
					array( 'q' => 'Combien coûte une séance ?', 'a' => 'Entre 80 et 150€/h selon la formule. Forfaits dégressifs pour 5 ou 10 séances. Séance découverte gratuite.' ),
					array( 'q' => 'Présentiel ou visio ?', 'a' => 'Les deux. La majorité de mes coachés alternent — flexibilité totale, même tarif.' ),
					array( 'q' => 'Sur combien de temps s\'étale un accompagnement ?', 'a' => 'En moyenne 3 à 6 mois (8 à 12 séances). Tout dépend de votre objectif et de votre rythme.' ),
				),
				'stats' => array(
					array( 'value' => '200+',  'label' => 'Personnes accompagnées' ),
					array( 'value' => '4,9/5', 'label' => 'Note moyenne' ),
					array( 'value' => '8 ans', 'label' => "D'expérience" ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Coach professionnel certifié depuis 8 ans, je vous accompagne dans votre développement personnel et professionnel. Mon approche : pragmatique, bienveillante, structurée. Pas de psycho-bla-bla, juste des outils qui marchent.\n\nCertifications : ICF (International Coaching Federation), Coach professionnel niveau ACC. Formation continue : PNL, Analyse Transactionnelle, Communication Non Violente.\n\nPartenariats : grandes entreprises (séances de coaching dirigeants), particuliers en transition de vie, étudiants en orientation. Confidentialité totale (RGPD + secret pro). Devis gratuit sous 24h.",
					'prestations'        => "Séances individuelles en présentiel ou en visio (Zoom/Meet sécurisé), forfaits dégressifs (5, 10, 20 séances), programmes thématiques (3 ou 6 mois), coaching de groupe, ateliers en entreprise.\n\nThématiques traitées : transition professionnelle, gestion du stress, leadership, communication, équilibre vie pro/perso, confiance en soi, projets de vie, prise de décision.\n\nApproche éclectique : PNL, AT, CNV, mindfulness. Outils concrets remis à chaque séance. Suivi entre les séances par messagerie sécurisée. Devis et plan d'accompagnement gratuits.",
					'zones-intervention' => "Cabinet à Paris (75008) — séances présentielles sur rendez-vous. Pour les sessions en visio : disponible dans toute la France et la francophonie (Belgique, Suisse, Canada, Afrique).\n\nDéplacements possibles en entreprise (séminaires, coaching d'équipe) — région parisienne sans frais, autres régions avec devis (frais de transport au km).\n\nUrgence (gestion crise pro, prise de décision rapide) : créneau dédié sous 48h selon disponibilité. Pour réservation rapide, contactez-moi au 06 00 00 00 00.",
					'realisations'       => "Plus de 200 personnes accompagnées en 8 ans : cadres en burn-out, entrepreneurs, étudiants, professionnels en reconversion, équipes de direction. 96% des coachés atteignent leur objectif principal en moins de 6 mois.\n\nClients récurrents : 8 entreprises en contrat coaching dirigeants annuel, des dizaines de particuliers revenant pour des phases ultérieures de leur vie.\n\nÉtudes de cas, témoignages et certifications disponibles sur demande. Pour un retour d'expérience similaire à votre situation, contactez-moi.",
				),
			),

			'coach_sportif' => array(
				'icon'  => '💪',
				'label' => 'Coach sportif',
				'desc'  => 'Coach sportif : perte de poids, prise de masse, préparation marathon, remise en forme. Présentiel + visio.',
				'mods'  => array(
					'ag_color_accent'        => '#E74C3C',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Coach sportif',
					'ag_hero_subtitle'       => 'Atteignez votre objectif physique avec un programme sur mesure. Bilan + séance d\'essai offerte. Salle, domicile ou visio.',
					'ag_hero_button'         => 'Bilan gratuit',
					'ag_hero_button_url'     => '/devis/',
					'ag_coach_metier_nom'    => 'Coach sportif',
					'ag_coach_hero_image'    => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?w=1600&q=80',
					'ag_coach_testi_1'       => "−12 kg en 5 mois, programme adapté, motivation au top. Méthode qui marche !|Émilie F.|Lyon",
					'ag_coach_testi_2'       => "Préparation marathon en 4 mois : objectif atteint sub-4h. Coaching pro.|Thomas D.|Paris",
					'ag_coach_testi_3'       => "Reprise sport après opération : progression douce, sécurisée, efficace.|Nora M.|Nice",
				),
				'services' => array(
					array( 'emoji' => '🎯', 'title' => 'Bilan + séance d\'essai' ),
					array( 'emoji' => '🏃', 'title' => 'Perte de poids' ),
					array( 'emoji' => '💪', 'title' => 'Prise de masse' ),
					array( 'emoji' => '🏅', 'title' => 'Prépa course/triathlon' ),
					array( 'emoji' => '🧘', 'title' => 'Remise en forme' ),
					array( 'emoji' => '🩹', 'title' => 'Reprise post-blessure' ),
					array( 'emoji' => '🏠', 'title' => 'Coaching à domicile' ),
					array( 'emoji' => '💻', 'title' => 'Programme visio' ),
				),
				'how' => array(
					array( 'emoji' => '📋', 'title' => 'Bilan physique + objectif', 'desc' => 'Évaluation initiale (mensurations, condition cardio, mobilité) + définition de l\'objectif chiffré et réaliste.' ),
					array( 'emoji' => '📝', 'title' => 'Programme personnalisé', 'desc' => 'Plan d\'entraînement hebdomadaire adapté à votre niveau, votre planning et votre matériel.' ),
					array( 'emoji' => '📈', 'title' => 'Séances + suivi', 'desc' => 'Séances en présentiel ou visio, ajustements réguliers, mesure des progrès, conseils nutrition.' ),
				),
				'faq' => array(
					array( 'q' => 'Quel tarif pour une séance ?', 'a' => 'Entre 50 et 90€/h selon le format (salle, domicile, visio). Forfaits 10 séances avec −15%.' ),
					array( 'q' => 'Avez-vous un matériel chez moi ?', 'a' => 'Travail avec ce que vous avez (poids du corps possible). Liste matos minimum : tapis + 1-2 haltères + élastiques.' ),
					array( 'q' => 'Combien de temps pour des résultats ?', 'a' => 'Premiers résultats visibles à 4-6 semaines avec 2-3 séances/sem + alimentation cohérente. Patience et régularité = clé.' ),
				),
				'stats' => array(
					array( 'value' => '350+',  'label' => 'Élèves accompagnés' ),
					array( 'value' => '−8 kg', 'label' => 'Perte moyenne / 4 mois' ),
					array( 'value' => '10 ans','label' => "D'expérience" ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Coach sportif diplômé d'État (BPJEPS AGFF) depuis 10 ans, je vous accompagne vers votre objectif physique : perte de poids, prise de masse, préparation course/triathlon, remise en forme post-grossesse, reprise post-blessure.\n\nFormations complémentaires : nutrition sportive, préparation physique générale, mobilité fonctionnelle, gestion mentale de l'effort. Carte pro à jour, assurance RC pro, attestation sécurité.\n\nApproche progressive et sécuritaire : pas de méthodes extrêmes, pas de régimes drastiques. Pédagogie bienveillante, objectifs SMART, suivi nutritionnel inclus. Plus de 350 élèves accompagnés.",
					'prestations'        => "Bilan initial gratuit (mensurations, condition cardio, objectif). Séances individuelles en salle de sport partenaire, à votre domicile (mobile) ou en visio. Forfaits 5, 10, 20 séances avec tarif dégressif.\n\nProgrammes spécifiques : perte de poids (12 sem), prise de masse (16 sem), préparation marathon/semi/10km, triathlon, reprise post-natale, remobilisation post-opération.\n\nCoaching collectif : groupes de 4 à 8 personnes (running club, cross-training). Suivi nutritionnel inclus : plan alimentaire, suivi macros, conseils courses. Outils digitaux : application de suivi des entraînements et progrès.",
					'zones-intervention' => "Zone d'intervention principale : votre ville et toutes les communes dans un rayon de 20 km (déplacement à domicile). Salle de sport partenaire au centre-ville (accès inclus dans les forfaits).\n\nVisio : disponible dans toute la France et francophonie. Format : appel vidéo + envoi de programme + suivi messagerie.\n\nGroupes outdoor : park runs hebdomadaires, séances en plein air (parcs, plages, sentiers). Tarif solo dès 50€/séance, groupe dès 15€/personne.",
					'realisations'       => "Plus de 350 élèves accompagnés en 10 ans : perte moyenne −8 kg en 4 mois pour les programmes minceur, 12 marathons préparés (toutes en sub-4h pour ceux qui visaient ce temps), 80+ retours en forme post-grossesse, des dizaines de reprises post-opérations.\n\nÉquipes accompagnées : 3 clubs sportifs amateurs (sections running et cross-training), 2 entreprises en programme bien-être au travail.\n\nTransformations photos avec accord client disponibles sur demande. Suivez aussi nos progrès clients sur Instagram (@votrecoach).",
				),
			),

			'coach_vie' => array(
				'icon'  => '🌱',
				'label' => 'Coach de vie',
				'desc'  => 'Coach de vie : développement personnel, gestion du stress, confiance en soi, transitions de vie, équilibre.',
				'mods'  => array(
					'ag_color_accent'        => '#8E44AD',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Coach de vie',
					'ag_hero_subtitle'       => 'Reprenez les rênes de votre vie. Confiance en soi, gestion du stress, équilibre, transitions — accompagnement bienveillant.',
					'ag_hero_button'         => 'Séance découverte',
					'ag_hero_button_url'     => '/devis/',
					'ag_coach_metier_nom'    => 'Coach de vie',
					'ag_coach_hero_image'    => 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=1600&q=80',
					'ag_coach_testi_1'       => "Sorti d'une période de burn-out grâce à son accompagnement bienveillant et structuré.|Émilie F.|Lyon",
					'ag_coach_testi_2'       => "Reconversion pro réussie après 5 mois de coaching. Clarté, courage, action.|Thomas D.|Paris",
					'ag_coach_testi_3'       => "Confiance retrouvée. Pose enfin mes limites et fais des choix alignés.|Nora M.|Nice",
				),
				'services' => array(
					array( 'emoji' => '☕', 'title' => 'Séance découverte' ),
					array( 'emoji' => '💼', 'title' => 'Transition pro' ),
					array( 'emoji' => '😌', 'title' => 'Gestion du stress' ),
					array( 'emoji' => '💎', 'title' => 'Confiance en soi' ),
					array( 'emoji' => '⚖️', 'title' => 'Équilibre vie pro/perso' ),
					array( 'emoji' => '🎯', 'title' => 'Définition objectifs' ),
					array( 'emoji' => '👨‍👩‍👧', 'title' => 'Relations & famille' ),
					array( 'emoji' => '🌅', 'title' => 'Sens & valeurs' ),
				),
				'how' => array(
					array( 'emoji' => '☕', 'title' => 'Première rencontre gratuite', 'desc' => '45 min pour comprendre ce qui vous amène, vos attentes, et voir si nous sommes alignés.' ),
					array( 'emoji' => '🗺️', 'title' => 'Cartographie objective', 'desc' => 'Bilan situation actuelle, identification des freins, vision claire de l\'objectif et chemin.' ),
					array( 'emoji' => '🌿', 'title' => 'Séances + intersessions', 'desc' => 'Séances bimensuelles + outils pratiques entre séances, ajustements selon vos progrès.' ),
				),
				'faq' => array(
					array( 'q' => 'Coach de vie ou thérapeute ?', 'a' => 'Le coaching est orienté futur/objectif/action. La thérapie travaille sur le passé/blessures. Je vous redirige si besoin thérapeutique.' ),
					array( 'q' => 'Confidentialité ?', 'a' => 'Totale. Secret professionnel. Aucun partage d\'information, RGPD respecté, données protégées.' ),
					array( 'q' => 'Combien de séances faut-il ?', 'a' => 'En moyenne 6 à 12 séances étalées sur 3-6 mois. Cela dépend de la profondeur de votre demande.' ),
				),
				'stats' => array(
					array( 'value' => '180+',  'label' => 'Personnes accompagnées' ),
					array( 'value' => '94%',   'label' => 'Objectif atteint' ),
					array( 'value' => '7 ans', 'label' => "D'expérience" ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Coach de vie certifiée RNCP depuis 7 ans, je vous accompagne dans les transitions et défis personnels : reconversion, gestion du stress, confiance en soi, équilibre vie pro/perso, relations, projets de vie.\n\nMon approche : humaniste, bienveillante, sans jugement. Outils éprouvés : Analyse Transactionnelle, PNL, Communication Non Violente, mindfulness. Pas de prêt-à-penser : votre solution est en vous.\n\nCertifications : RNCP niveau 6 (Coach professionnel), supervisée régulièrement, formation continue. Code déontologique strict (confidentialité, intégrité, professionnalisme).",
					'prestations'        => "Séance découverte gratuite 45 min (en visio ou cabinet), séances individuelles 1h (en présentiel ou visio), forfaits 5 ou 10 séances avec tarif dégressif, programmes thématiques (3 ou 6 mois).\n\nThèmes abordés : transition professionnelle, gestion du stress et de l'anxiété, confiance en soi, affirmation de soi, équilibre vie pro/perso, projets de vie, prise de décision, deuil et changements.\n\nFormats disponibles : présentiel cabinet (Paris 11e), visio Zoom/Meet, marche-coaching en extérieur (Buttes-Chaumont, Parc Monceau). Outils remis : carnet de bord, exercices entre séances, méditation guidées.",
					'zones-intervention' => "Cabinet à Paris 11e (métro République/Oberkampf) — séances présentielles sur rendez-vous mardi-vendredi 10h-19h, samedi 10h-13h.\n\nVisio : disponible dans toute la France et francophonie (Belgique, Suisse, Canada, DOM-TOM). Mêmes tarifs qu'en présentiel.\n\nMarche-coaching : Buttes-Chaumont, Parc Monceau, Bois de Vincennes. Format apprécié pour libérer la parole et avancer en mouvement (mêmes tarifs).",
					'realisations'       => "Plus de 180 personnes accompagnées en 7 ans : 94% atteignent leur objectif initial en moins de 12 séances. Reconversions réussies, sorties de burn-out, transitions de vie réussies, restauration de la confiance en soi.\n\nTémoignages anonymisés et études de cas disponibles. Suivez aussi mes réflexions et exercices gratuits sur Instagram et newsletter mensuelle.",
				),
			),

			'coach_business' => array(
				'icon'  => '💼',
				'label' => 'Coach business / pro',
				'desc'  => 'Coach professionnel : entrepreneuriat, leadership, gestion d\'équipe, performance commerciale, prise de parole.',
				'mods'  => array(
					'ag_color_accent'        => '#1ABC9C',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Coach business',
					'ag_hero_subtitle'       => 'Développez vos performances pro : leadership, vente, communication, stratégie. Coaching dirigeants, équipes et entrepreneurs.',
					'ag_hero_button'         => 'Demander un devis',
					'ag_hero_button_url'     => '/devis/',
					'ag_coach_metier_nom'    => 'Coach business',
					'ag_coach_hero_image'    => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80',
					'ag_coach_testi_1'       => "Coaching dirigeant 6 mois : meilleure prise de décision, équipe alignée, CA +28%.|Pierre L.|Toulouse",
					'ag_coach_testi_2'       => "Prise de parole en public : confiance gagnée, présentations qui convertissent.|Aïcha R.|Lille",
					'ag_coach_testi_3'       => "Lancement d'entreprise réussi grâce à ses outils stratégiques et son réseau.|Julien G.|Nantes",
				),
				'services' => array(
					array( 'emoji' => '📊', 'title' => 'Coaching dirigeants' ),
					array( 'emoji' => '👥', 'title' => 'Coaching d\'équipe' ),
					array( 'emoji' => '🚀', 'title' => 'Accompagnement entrepreneurs' ),
					array( 'emoji' => '🎤', 'title' => 'Prise de parole en public' ),
					array( 'emoji' => '💰', 'title' => 'Performance commerciale' ),
					array( 'emoji' => '🧭', 'title' => 'Leadership & vision' ),
					array( 'emoji' => '⚡', 'title' => 'Gestion du temps' ),
					array( 'emoji' => '🤝', 'title' => 'Négociation' ),
				),
				'how' => array(
					array( 'emoji' => '🔍', 'title' => 'Diagnostic stratégique', 'desc' => 'Analyse de vos enjeux pros, identification des leviers de progrès, définition du plan d\'action.' ),
					array( 'emoji' => '🎯', 'title' => 'Objectifs SMART + KPI', 'desc' => 'Objectifs chiffrés et mesurables, indicateurs de suivi, calendrier de séances aligné sur votre rythme pro.' ),
					array( 'emoji' => '📈', 'title' => 'Séances + reporting', 'desc' => 'Séances bimensuelles, outils opérationnels, points d\'avancement, ajustements stratégiques.' ),
				),
				'faq' => array(
					array( 'q' => 'Tarif d\'un coaching dirigeant ?', 'a' => 'Devis sur mesure selon enjeux. En moyenne 250-450€/h, forfait 10 séances 2 200-3 800€. DPC/FNE éligible selon profil.' ),
					array( 'q' => 'Coaching d\'équipe possible ?', 'a' => 'Oui : team building, alignement vision, gestion conflits, performance collective. Format atelier 1/2 journée ou journée complète.' ),
					array( 'q' => 'OPCO / formation pro éligible ?', 'a' => 'Oui, je suis certifié Qualiopi. Prise en charge possible par les OPCO selon votre statut (salarié, indépendant, dirigeant).' ),
				),
				'stats' => array(
					array( 'value' => '120+',  'label' => 'Dirigeants accompagnés' ),
					array( 'value' => 'Qualiopi','label' => 'Certifié' ),
					array( 'value' => '12 ans','label' => "D'expérience" ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Coach professionnel certifié ICF (PCC) et Qualiopi depuis 12 ans, j'accompagne dirigeants, entrepreneurs et équipes vers la performance durable. Parcours pro : 15 ans en direction d'entreprise (PME et grand groupe) avant de devenir coach à temps plein.\n\nSpécialités : coaching de dirigeants, accompagnement entrepreneurs, performance commerciale, prise de parole en public, leadership et gestion d'équipe.\n\nCertifications : ICF (International Coaching Federation) niveau PCC, Qualiopi pour la formation, certifié MBTI, DiSC, 360°. Plus de 120 dirigeants accompagnés.",
					'prestations'        => "Coaching individuel dirigeants/entrepreneurs : séances bimensuelles 1h30, 10-15 séances sur 6-9 mois. Format présentiel (bureaux Paris ou vos locaux) ou visio.\n\nCoaching d'équipe : team building (1/2 ou 1 journée), accompagnement comités de direction, séminaires stratégiques. Outils : MBTI équipe, 360° leadership, ateliers vision.\n\nFormations courtes : prise de parole en public (2 jours), négociation (1 jour), gestion du temps (1/2 jour). Toutes éligibles OPCO via Qualiopi.\n\nTarifs sur devis. Indicatif : 250-450€/h selon profil, packages préférentiels pour les engagements longue durée.",
					'zones-intervention' => "Bureaux à Paris 8e — séances présentielles sur rendez-vous lundi-vendredi 9h-19h. Déplacements possibles dans toute la France (frais transport + temps en sus selon distance).\n\nVisio : disponible dans toute la francophonie. Format Zoom enterprise, confidentialité garantie, enregistrement possible sur demande.\n\nFormation en entreprise : intervention sur site partout en France. Pour les groupes internationaux, possibilité en anglais (formation continue).",
					'realisations'       => "Plus de 120 dirigeants accompagnés en 12 ans : CEOs de PME (50 à 500 collaborateurs), entrepreneurs en croissance, comités de direction, responsables commerciaux.\n\nÉtudes de cas marquantes : +28% CA en 18 mois pour un cabinet de conseil, restructuration réussie d'un comité de direction, lancement de 3 entreprises tech accompagnées dès la création.\n\nFormations Qualiopi délivrées à 200+ professionnels (prise de parole, négociation, leadership). Témoignages clients vérifiés et études de cas anonymisées disponibles sous NDA.",
				),
			),

			'coach_mental' => array(
				'icon'  => '🧘',
				'label' => 'Coach mental & bien-être',
				'desc'  => 'Coach mental : performance mentale, méditation, gestion émotionnelle, sportifs de haut niveau, dirigeants sous pression.',
				'mods'  => array(
					'ag_color_accent'        => '#16A085',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Coach mental',
					'ag_hero_subtitle'       => 'Performance mentale, gestion du stress, méditation, équilibre émotionnel. Pour sportifs, dirigeants, étudiants, particuliers exigeants.',
					'ag_hero_button'         => 'Séance d\'essai',
					'ag_hero_button_url'     => '/devis/',
					'ag_coach_metier_nom'    => 'Coach mental',
					'ag_coach_hero_image'    => 'https://images.unsplash.com/photo-1545389336-cf090694435e?w=1600&q=80',
					'ag_coach_testi_1'       => "Avant compétition, je gérais mal le stress. Avec son aide, médaille de bronze.|Anne C.|Montpellier",
					'ag_coach_testi_2'       => "Méditation + sophrologie : sommeil retrouvé, énergie quotidienne nouvelle.|Mehdi A.|Lille",
					'ag_coach_testi_3'       => "Préparation mentale aux examens : zéro panique, top performance.|Valérie P.|Toulouse",
				),
				'services' => array(
					array( 'emoji' => '🎯', 'title' => 'Préparation mentale' ),
					array( 'emoji' => '🧘', 'title' => 'Méditation guidée' ),
					array( 'emoji' => '🌬️', 'title' => 'Sophrologie' ),
					array( 'emoji' => '😌', 'title' => 'Gestion du stress' ),
					array( 'emoji' => '💤', 'title' => 'Sommeil & récupération' ),
					array( 'emoji' => '🎓', 'title' => 'Prépa examens/concours' ),
					array( 'emoji' => '🏆', 'title' => 'Sportifs haut niveau' ),
					array( 'emoji' => '💼', 'title' => 'Dirigeants sous pression' ),
				),
				'how' => array(
					array( 'emoji' => '🧠', 'title' => 'Évaluation mentale', 'desc' => 'Bilan de votre fonctionnement mental, identification des leviers de progression, objectif concret.' ),
					array( 'emoji' => '🛠️', 'title' => 'Boîte à outils sur mesure', 'desc' => 'Techniques adaptées : visualisation, ancrage, respiration, méditation. Pratiques quotidiennes courtes.' ),
					array( 'emoji' => '📅', 'title' => 'Séances + suivi', 'desc' => 'Séances bimensuelles, audio personnalisés entre séances, ajustement selon vos retours et progrès.' ),
				),
				'faq' => array(
					array( 'q' => 'Coach mental vs psy ?', 'a' => 'Le coach mental travaille la performance et l\'équilibre dans un cadre orienté objectif. Le psy traite des troubles mentaux. Si besoin, je vous redirige.' ),
					array( 'q' => 'Méditation : je n\'y arrive pas !', 'a' => 'Normal au début. Mes techniques sont progressives (commencer par 3 min/jour). 95% de mes clients y arrivent en 4-6 semaines.' ),
					array( 'q' => 'Combien de temps pour les premiers effets ?', 'a' => 'Pratique quotidienne : effets perceptibles à 3-4 semaines (sommeil, énergie). Objectifs profonds : 3-6 mois.' ),
				),
				'stats' => array(
					array( 'value' => '150+',  'label' => 'Coachés' ),
					array( 'value' => '95%',   'label' => 'Progression mesurable' ),
					array( 'value' => '6 ans', 'label' => "D'expérience" ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Coach mental certifié, sophrologue (RNCP) et instructeur de méditation (MBSR) depuis 6 ans. Mon parcours : 10 ans en sport de haut niveau (basket pro) puis reconversion vers l'accompagnement mental après formation continue.\n\nApproche : techniques validées scientifiquement (sophrologie, méditation, ACT, hypnose Ericksonienne, neurosciences appliquées). Pas d'ésotérisme, juste des outils qui marchent et qui sont mesurables.\n\nCertifications : sophrologue RNCP, instructeur MBSR (Mindfulness-Based Stress Reduction), formation continue en neurosciences. Plus de 150 personnes accompagnées : sportifs, dirigeants, étudiants, particuliers en quête d'équilibre.",
					'prestations'        => "Séance individuelle 1h (sophrologie, méditation, préparation mentale). Forfaits 5 ou 10 séances dégressifs. Programmes thématiques 3 mois (sommeil, gestion stress, prépa compétition).\n\nGroupes : ateliers méditation hebdomadaires (10-15 pers), retraites week-end (8-12 pers), méditation en entreprise (formations 1/2 journée). Tarifs collectifs préférentiels.\n\nSportifs haut niveau : programmes prépa compétition individualisés, accompagnement de saison complète, présence sur événements (championnats, finales). Suivi via app dédiée + audio personnalisés.",
					'zones-intervention' => "Cabinet à Lyon 2e — séances présentielles sur rendez-vous mardi-samedi 9h-19h. Espace calme dédié à la pratique.\n\nVisio : disponible dans toute la francophonie. Format Zoom + envoi d'audios méditation personnalisés. Mêmes tarifs.\n\nDéplacements pour sportifs et entreprises : événements sportifs (championnats, stages), séminaires d'entreprise. Devis selon distance et durée.",
					'realisations'       => "Plus de 150 personnes accompagnées en 6 ans : sportifs amateurs et pro (athlétisme, natation, tennis, équitation), dirigeants/entrepreneurs sous pression, étudiants en classes prépa, particuliers en burn-out ou insomnie.\n\nÉtudes de cas : médaille bronze championnat Europe junior (préparation mentale 12 mois), sortie d'insomnie chronique pour 24 personnes (programme sommeil 8 sem), réussite concours grandes écoles pour 18 étudiants.\n\nAteliers méditation hebdomadaires en cabinet (10-15 pers). Retraites week-end 4× par an (8-12 pers). Newsletter mensuelle gratuite avec exercices et conseils.",
				),
			),

		);
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 22 );
		add_action( 'admin_post_ag_coach_apply_preset', array( __CLASS__, 'handle_apply' ) );
		add_action( 'customize_register', array( __CLASS__, 'customize_content' ) );
	}

	/** Sanitize commun aux champs texte multi-lignes éditables. */
	public static function sanitize_edit( $v ) {
		return wp_kses_post( $v );
	}

	/**
	 * Réglages Personnaliser pour rendre éditables (et donc modifiables
	 * après application d'un preset) les chiffres clés, les témoignages
	 * et la FAQ. Override manuel : prime sur le contenu du preset.
	 */
	public static function customize_content( $wp ) {
		// ── Chiffres clés (hero) ──
		$wp->add_section( 'ag_coach_stats', array(
			'title'       => '📊 ' . __( 'Chiffres clés (accueil)', 'ag-starter-coach' ),
			'priority'    => 27,
			'description' => __( 'Une statistique par ligne : valeur | libellé (ex : 200+ | Personnes accompagnées). Laissez vide pour garder les chiffres du preset choisi.', 'ag-starter-coach' ),
		) );
		$wp->add_setting( 'ag_coach_stats_edit', array(
			'default'           => '',
			'sanitize_callback' => array( __CLASS__, 'sanitize_edit' ),
		) );
		$wp->add_control( 'ag_coach_stats_edit', array(
			'label'       => __( 'Vos chiffres clés (3 recommandés)', 'ag-starter-coach' ),
			'section'     => 'ag_coach_stats',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 4 ),
		) );

		// ── Témoignages ──
		$wp->add_section( 'ag_coach_testi', array(
			'title'       => '💬 ' . __( 'Témoignages (accueil)', 'ag-starter-coach' ),
			'priority'    => 28,
			'description' => __( 'Trois avis clients. Format : texte | nom | ville. Videz un champ pour retirer le témoignage. N\'inventez pas de faux avis.', 'ag-starter-coach' ),
		) );
		for ( $i = 1; $i <= 3; $i++ ) {
			$wp->add_setting( 'ag_coach_testi_' . $i, array(
				'default'           => '',
				'sanitize_callback' => array( __CLASS__, 'sanitize_edit' ),
			) );
			$wp->add_control( 'ag_coach_testi_' . $i, array(
				'label'       => sprintf( __( 'Témoignage %d (texte | nom | ville)', 'ag-starter-coach' ), $i ),
				'section'     => 'ag_coach_testi',
				'type'        => 'textarea',
				'input_attrs' => array( 'rows' => 2 ),
			) );
		}

		// ── FAQ ──
		$wp->add_section( 'ag_coach_faq', array(
			'title'       => '❓ ' . __( 'FAQ (accueil)', 'ag-starter-coach' ),
			'priority'    => 29,
			'description' => __( 'Une question par ligne : Question | Réponse. Laissez vide pour garder la FAQ du preset choisi.', 'ag-starter-coach' ),
		) );
		$wp->add_setting( 'ag_coach_faq_edit', array(
			'default'           => '',
			'sanitize_callback' => array( __CLASS__, 'sanitize_edit' ),
		) );
		$wp->add_control( 'ag_coach_faq_edit', array(
			'label'       => __( 'Votre FAQ', 'ag-starter-coach' ),
			'section'     => 'ag_coach_faq',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 6 ),
		) );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Configuration métier', 'ag-starter-coach' ),
			'🎯 ' . __( 'Configuration métier', 'ag-starter-coach' ),
			'manage_options',
			'ag-coach-presets',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$applied = isset( $_GET['applied'] ) ? sanitize_key( $_GET['applied'] ) : '';
		$current = get_theme_mod( 'ag_coach_metier_slug', '' );
		$presets = self::get_presets();
		?>
		<div class="wrap">
			<h1>🎯 <?php esc_html_e( 'Configuration métier — AG Starter Coach', 'ag-starter-coach' ); ?></h1>
			<p style="font-size:1.05em;color:#50575e;max-width:820px;"><?php esc_html_e( 'Choisissez votre spécialité de coaching ci-dessous. En 1 clic, le thème adapte le hero, la couleur d\'accent et la grille des 8 services. Affinez ensuite chaque texte dans Apparence > Personnaliser.', 'ag-starter-coach' ); ?></p>

			<?php if ( $applied && isset( $presets[ $applied ] ) ) : ?>
				<div class="notice notice-success"><p><strong>✅ Preset « <?php echo esc_html( $presets[ $applied ]['icon'] . ' ' . $presets[ $applied ]['label'] ); ?> » appliqué.</strong> Visitez la page d'accueil pour voir le résultat.</p></div>
			<?php endif; ?>

			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px;margin-top:24px;max-width:1200px;">
				<?php foreach ( $presets as $slug => $preset ) :
					$is_current = ( $slug === $current );
				?>
				<div style="background:#fff;border:2px solid <?php echo $is_current ? esc_attr( $preset['mods']['ag_color_accent'] ) : '#ccd0d4'; ?>;border-radius:10px;padding:24px;<?php echo $is_current ? 'box-shadow:0 4px 16px rgba(0,0,0,.08);' : ''; ?>">
					<div style="font-size:3em;margin-bottom:8px;"><?php echo esc_html( $preset['icon'] ); ?></div>
					<h2 style="margin:0 0 8px;font-size:1.4em;"><?php echo esc_html( $preset['label'] ); ?></h2>
					<?php if ( $is_current ) : ?>
						<span style="display:inline-block;padding:2px 10px;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;border-radius:12px;font-size:.78em;font-weight:700;margin-bottom:10px;">✓ ACTUELLEMENT APPLIQUÉ</span>
					<?php endif; ?>
					<p style="color:#50575e;font-size:.95em;line-height:1.5;margin:8px 0 14px;"><?php echo esc_html( $preset['desc'] ); ?></p>
					<p style="font-size:.85em;color:#777;margin:0 0 14px;"><strong>8 services inclus :</strong></p>
					<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;">
						<?php foreach ( $preset['services'] as $svc ) : ?>
							<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:14px;font-size:.78em;">
								<?php echo esc_html( $svc['emoji'] ); ?> <?php echo esc_html( $svc['title'] ); ?>
							</span>
						<?php endforeach; ?>
					</div>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Appliquer le preset « <?php echo esc_js( $preset['label'] ); ?> » ? Cela remplacera votre hero, couleur d\'accent et grille services actuels.');">
						<input type="hidden" name="action" value="ag_coach_apply_preset" />
						<input type="hidden" name="preset" value="<?php echo esc_attr( $slug ); ?>" />
						<?php wp_nonce_field( 'ag_coach_apply_preset' ); ?>
						<button type="submit" class="button button-primary button-large" style="width:100%;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;border-color:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;font-weight:700;">
							<?php echo $is_current ? '🔄 Réappliquer' : '✨ Appliquer ce preset'; ?>
						</button>
					</form>
				</div>
				<?php endforeach; ?>
			</div>

			<p style="margin-top:32px;color:#777;font-size:.9em;">
				💡 <?php esc_html_e( 'Après application, fine-tunez via', 'ag-starter-coach' ); ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Apparence > Personnaliser', 'ag-starter-coach' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-coach-reset' ) ); ?>">🔄 <?php esc_html_e( 'Réinitialiser le thème', 'ag-starter-coach' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function handle_apply() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_coach_apply_preset' );
		$slug    = isset( $_POST['preset'] ) ? sanitize_key( $_POST['preset'] ) : '';
		$presets = self::get_presets();
		if ( ! isset( $presets[ $slug ] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-coach-presets' ) );
			exit;
		}
		$preset = $presets[ $slug ];

		foreach ( $preset['mods'] as $key => $val ) {
			set_theme_mod( $key, $val );
		}
		set_theme_mod( 'ag_coach_metier_slug', $slug );
		set_theme_mod( 'ag_coach_services_json', wp_json_encode( $preset['services'] ) );

		if ( ! empty( $preset['pages'] ) && is_array( $preset['pages'] ) ) {
			foreach ( $preset['pages'] as $slug => $content ) {
				$page = get_page_by_path( $slug );
				if ( ! $page ) continue;
				$paragraphs = explode( "\n\n", trim( $content ) );
				$wrapped = '';
				foreach ( $paragraphs as $p ) {
					$p = trim( $p );
					if ( $p === '' ) continue;
					$wrapped .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
				}
				wp_update_post( array(
					'ID'           => $page->ID,
					'post_content' => trim( $wrapped ),
				) );
			}
		}

		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();
		delete_site_transient( 'update_themes' );

		wp_safe_redirect( admin_url( 'themes.php?page=ag-coach-presets&applied=' . $slug ) );
		exit;
	}

	public static function get_active_services() {
		$json = get_theme_mod( 'ag_coach_services_json', '' );
		if ( ! $json ) return array();
		$arr = json_decode( $json, true );
		return is_array( $arr ) ? $arr : array();
	}

	public static function get_active_preset() {
		$slug = get_theme_mod( 'ag_coach_metier_slug', '' );
		if ( ! $slug ) return null;
		$presets = self::get_presets();
		return isset( $presets[ $slug ] ) ? $presets[ $slug ] : null;
	}

	public static function get_active_stats() {
		$edit = (string) get_theme_mod( 'ag_coach_stats_edit', '' );
		if ( '' !== trim( $edit ) ) {
			$out = array();
			foreach ( preg_split( '/\r\n|\r|\n/', $edit ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) continue;
				$parts = array_map( 'trim', explode( '|', $line ) );
				$value = isset( $parts[0] ) ? $parts[0] : '';
				$label = isset( $parts[1] ) ? $parts[1] : '';
				if ( '' === $value && '' === $label ) continue;
				$out[] = array( 'value' => $value, 'label' => $label );
			}
			return $out;
		}
		$p = self::get_active_preset();
		return ( $p && isset( $p['stats'] ) ) ? $p['stats'] : array();
	}

	public static function get_active_how() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['how'] ) ) ? $p['how'] : array();
	}

	public static function get_active_faq() {
		$edit = (string) get_theme_mod( 'ag_coach_faq_edit', '' );
		if ( '' !== trim( $edit ) ) {
			$out = array();
			foreach ( preg_split( '/\r\n|\r|\n/', $edit ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) continue;
				$parts = array_map( 'trim', explode( '|', $line ) );
				$q = isset( $parts[0] ) ? $parts[0] : '';
				$a = isset( $parts[1] ) ? $parts[1] : '';
				if ( '' === $q ) continue;
				$out[] = array( 'q' => $q, 'a' => $a );
			}
			return $out;
		}
		$p = self::get_active_preset();
		return ( $p && isset( $p['faq'] ) ) ? $p['faq'] : array();
	}

	public static function get_testimonial( $i ) {
		$raw = get_theme_mod( 'ag_coach_testi_' . (int) $i, '' );
		if ( ! $raw ) return null;
		$parts = array_map( 'trim', explode( '|', $raw ) );
		return array(
			'text' => isset( $parts[0] ) ? $parts[0] : '',
			'name' => isset( $parts[1] ) ? $parts[1] : '',
			'city' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
}
AG_Coach_Presets::init();

add_filter( 'body_class', function ( $classes ) {
	if ( class_exists( 'AG_Coach_Presets' ) && AG_Coach_Presets::get_active_preset() ) {
		$classes[] = 'ag-premium-mode';
		$slug = get_theme_mod( 'ag_coach_metier_slug', '' );
		if ( $slug ) {
			$classes[] = 'ag-metier-' . sanitize_html_class( $slug );
		}
	}
	return $classes;
} );
