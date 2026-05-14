<?php
/**
 * Presets metier — 5 configurations rapides pour adapter le theme a
 * differents corps de metier (electricien, macon, boulanger, etc.) en
 * 1 clic. Chaque preset configure le hero, la couleur d'accent et
 * la grille de services affichee sur la page d'accueil.
 *
 * Style visuel inspire de meilleur-artisan.com : palette orange/blanc,
 * grille services 4x2 avec emojis, hero centre, CTA contrastant.
 *
 * Apparait dans : "Apparence > 🎯 Configuration metier".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Artisan_Presets {

	/**
	 * Definition des presets. Chaque preset declare ses theme_mods et
	 * sa liste de services (8 max pour grille 4x2).
	 */
	public static function get_presets() {
		return array(

			'generaliste' => array(
				'icon'  => '🔧',
				'label' => 'Artisan généraliste',
				'desc'  => 'Tous travaux du bâtiment : rénovation, dépannage, petits travaux. Pour artisan multi-compétences.',
				'mods'  => array(
					'ag_color_accent'        => '#F37A1F',
					'ag_hero_prefix'         => 'Vos travaux avec',
					'ag_hero_brand'          => 'Votre Entreprise',
					'ag_hero_subtitle'       => 'Devis gratuit, intervention rapide, satisfaction garantie. Plus de 10 ans d\'expérience à votre service.',
					'ag_hero_button'         => 'Demander un devis',
					'ag_hero_button_url'     => '/devis/',
					'ag_artisan_metier_nom'  => 'Artisan généraliste',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Travail soigné, équipe ponctuelle, devis respecté à l\'euro près. Je recommande.|Sophie M.|Lyon',
					'ag_artisan_testi_2'     => 'Intervenu en urgence un dimanche pour une fuite, et tout réparé en 2h. Top !|Karim B.|Marseille',
					'ag_artisan_testi_3'     => 'Rénovation complète de mon appart, chantier impeccable et finitions parfaites.|Laure D.|Paris',
				),
				'services' => array(
					array( 'emoji' => '🔧', 'title' => 'Petits travaux' ),
					array( 'emoji' => '🛠️', 'title' => 'Rénovation' ),
					array( 'emoji' => '⚡', 'title' => 'Dépannage' ),
					array( 'emoji' => '📐', 'title' => 'Mise aux normes' ),
					array( 'emoji' => '🏠', 'title' => 'Travaux intérieur' ),
					array( 'emoji' => '🚜', 'title' => 'Travaux extérieur' ),
					array( 'emoji' => '💼', 'title' => 'Devis sur mesure' ),
					array( 'emoji' => '🔍', 'title' => 'Diagnostic' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Décrivez vos travaux', 'desc' => 'Appelez-nous ou remplissez le formulaire pour expliquer votre besoin.' ),
					array( 'emoji' => '📋', 'title' => 'Devis gratuit sous 24h', 'desc' => 'Visite technique offerte si nécessaire, puis devis détaillé sans engagement.' ),
					array( 'emoji' => '✅', 'title' => 'Travaux réalisés', 'desc' => 'Intervention rapide, chantier propre, garantie sur les travaux réalisés.' ),
				),
				'faq' => array(
					array( 'q' => 'Combien coûte un devis ?', 'a' => 'Nos devis sont 100% gratuits et sans engagement, même après visite technique.' ),
					array( 'q' => 'Sous quel délai intervenez-vous ?', 'a' => 'En général sous 48h pour un devis, et 7 à 21 jours pour démarrer les travaux selon leur ampleur.' ),
					array( 'q' => 'Êtes-vous assurés ?', 'a' => 'Oui, garantie décennale + responsabilité civile professionnelle. Attestations sur demande.' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Artisan généraliste du bâtiment depuis plus de 10 ans, nous accompagnons particuliers et professionnels dans tous leurs projets de travaux : rénovation, dépannage, petits travaux, mise aux normes.\n\nNotre force, c'est la polyvalence : une équipe pluridisciplinaire (maçon, plombier, électricien, peintre) qui coordonne pour vous tous les corps de métier. Plus besoin de jongler entre plusieurs entreprises — nous sommes votre interlocuteur unique.\n\nGarantie décennale + responsabilité civile professionnelle. Devis gratuits sous 24h, transparence totale sur les tarifs, chantier propre, satisfaction client comme priorité. Plus de 600 chantiers réalisés à ce jour.",
					'prestations'        => "Tous travaux du bâtiment : petits travaux d'entretien (étagères, fixations, rebouchage), rénovation partielle ou totale (cuisine, salle de bain, pièce à vivre), dépannage urgent toutes spécialités, mise aux normes électricité/plomberie pour vente immobilière.\n\nTravaux intérieurs : peinture, revêtements, cloisons, plafonds, électricité, plomberie, carrelage. Travaux extérieurs : ravalement, terrasse, clôture, abri, allée. Pose et installation de tout type d'équipement.\n\nDevis détaillé gratuit sous 24h après visite technique. Travaux soignés, finitions impeccables, chantier propre quotidien. Garantie sur l'ensemble des prestations.",
					'zones-intervention' => "Zone d'intervention principale : votre ville et toutes les communes dans un rayon de 30 km. Pour les chantiers de plus grande ampleur (rénovation complète, gros travaux), nous nous déplaçons jusqu'à 60 km avec frais de déplacement transparents.\n\nUrgences (fuite, panne, dégât) : intervention prioritaire sous 24h dans la zone principale. Pour les autres demandes, devis envoyé sous 24h, RDV de chantier dans les 7 à 21 jours selon l'ampleur.\n\nContactez-nous au 06 00 00 00 00 pour toute demande, urgence ou simple renseignement. Réponse rapide assurée même le week-end.",
					'realisations'       => "Plus de 600 chantiers terminés en 10 ans : rénovations complètes d'appartements et maisons, mises aux normes pour vente, dépannages plomberie/électricité, créations de terrasses, ravalements, peintures intérieures.\n\nNos clients reviennent en moyenne 2 à 3 fois sur 5 ans pour de nouveaux travaux — c'est notre meilleure preuve de qualité. Note moyenne 4,8/5 sur les avis clients vérifiés.\n\nVous voulez un retour sur un chantier similaire au vôtre ? Demandez-nous lors du devis, nous serons heureux de vous communiquer des références de clients récents qui ont accepté de témoigner.",
				),
			),

			'electricien' => array(
				'icon'  => '⚡',
				'label' => 'Électricien',
				'desc'  => 'Installation, dépannage, mise aux normes, domotique. Tableau électrique, éclairage, bornes de recharge.',
				'mods'  => array(
					'ag_color_accent'        => '#FFB400',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Électricien',
					'ag_hero_subtitle'       => 'Installation, dépannage, mise aux normes — intervention 7j/7, devis gratuit. Certification Qualifelec.',
					'ag_hero_button'         => 'Dépannage urgent',
					'ag_hero_button_url'     => '/devis/',
					'ag_artisan_metier_nom'  => 'Électricien',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Tableau électrique refait en 1 journée, propre et conforme. Excellent.|Pierre L.|Toulouse',
					'ag_artisan_testi_2'     => 'Dépannage un dimanche soir : arrivé en 40 min, panne réglée. Sauveur !|Aïcha R.|Lille',
					'ag_artisan_testi_3'     => 'Installation domotique complète : éclairage, volets, alarme. Travail au top.|Julien G.|Nantes',
				),
				'services' => array(
					array( 'emoji' => '⚡', 'title' => 'Dépannage urgent' ),
					array( 'emoji' => '🔌', 'title' => 'Tableau électrique' ),
					array( 'emoji' => '💡', 'title' => 'Éclairage' ),
					array( 'emoji' => '📐', 'title' => 'Mise aux normes' ),
					array( 'emoji' => '🏠', 'title' => 'Domotique' ),
					array( 'emoji' => '🚗', 'title' => 'Bornes de recharge' ),
					array( 'emoji' => '🔍', 'title' => 'Diagnostic électrique' ),
					array( 'emoji' => '🛠️', 'title' => 'Rénovation totale' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Diagnostic au téléphone', 'desc' => 'Décrivez votre panne ou besoin, on évalue tout de suite avec vous.' ),
					array( 'emoji' => '⚡', 'title' => 'Intervention rapide', 'desc' => 'Dépannage sous 2h en urgence, RDV planifié pour les travaux programmés.' ),
					array( 'emoji' => '📜', 'title' => 'Attestation Consuel', 'desc' => 'Pour toute nouvelle installation : mise en service après contrôle Consuel.' ),
				),
				'faq' => array(
					array( 'q' => 'Êtes-vous certifié Qualifelec ?', 'a' => 'Oui, certification Qualifelec à jour. Garantie décennale + assurance pro.' ),
					array( 'q' => 'Acceptez-vous les urgences nuit/week-end ?', 'a' => 'Oui, ligne d\'astreinte 7j/7. Majoration tarif nuit/dimanche selon barème transparent.' ),
					array( 'q' => 'Puis-je bénéficier d\'aides MaPrimeRénov\' ?', 'a' => 'Pour rénovation électrique avec gain énergétique (chauffage, isolation), oui. On vous guide dans le montage du dossier.' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Électricien indépendant installé depuis 12 ans, certifié Qualifelec (renouvelé chaque année) et IRVE pour l'installation de bornes de recharge véhicules électriques.\n\nNotre équipe de 4 électriciens diplômés (CAP, Bac Pro ELEEC, Brevet Pro) couvre l'ensemble des prestations : dépannage urgent, rénovation totale, mise aux normes NF C 15-100, domotique, bornes de recharge VE. Tous nos compagnons sont salariés (pas de sous-traitance).\n\nGarantie décennale obligatoire + responsabilité civile pro + Consuel pour toute nouvelle installation. Devis gratuit, intervention 7j/7 pour les urgences, transparence totale sur les tarifs.",
					'prestations'        => "Dépannage électrique 7j/7 (déplacement sous 2h en urgence), refonte de tableau électrique conforme NF C 15-100, installation et changement de luminaires, mise aux normes obligatoire pour vente/location, domotique connectée (volets, chauffage, éclairage), installation de bornes de recharge véhicule électrique (certifié IRVE).\n\nDiagnostic électrique complet pour vente immobilière, rénovation totale d'installation, pré-câblage VDI pour box internet, prise de terre, parafoudre, télérupteur, va-et-vient.\n\nMatériel professionnel Schneider, Legrand, Hager. Câbles aux normes NF, gaines ICTA, boîtiers DCL pour DCL pour suspensions. Attestation Consuel délivrée pour toute nouvelle installation.",
					'zones-intervention' => "Intervention dans toute la métropole et les communes limitrophes pour tous types de travaux électriques. Pour les dépannages urgents (panne totale, court-circuit, fuite à la terre), zone d'astreinte 7j/7 avec arrivée sous 2h dans un rayon de 25 km.\n\nPour les chantiers programmés (rénovation, domotique, mise aux normes), nous nous déplaçons dans tout le département. Frais de déplacement transparents annoncés au devis.\n\nLigne urgence directe : 06 00 00 00 00. Disponible nuits et week-ends avec majoration selon barème publié (consultable dans les mentions légales).",
					'realisations'       => "Plus de 1 200 interventions réalisées ces 12 dernières années : 280 refontes complètes de tableaux électriques, 95 mises aux normes pour vente immobilière, 64 installations domotique complète, 31 bornes de recharge VE.\n\nChantier marquant 2025 : rénovation électrique complète d'une maison ancienne de 180m² incluant tableau, prises, éclairage et domotique — livré en 9 jours ouvrés, validé Consuel du premier coup.\n\nDécouvrez les témoignages de nos clients sur la page d'accueil. Vous souhaitez en savoir plus sur un chantier précis ? Contactez-nous, nous serons heureux de vous fournir des références similaires à votre projet.",
				),
			),

			'macon' => array(
				'icon'  => '🧱',
				'label' => 'Maçon',
				'desc'  => 'Construction, rénovation, extension, façades. Dalles béton, murets, terrassement, carrelage.',
				'mods'  => array(
					'ag_color_accent'        => '#A0522D',
					'ag_hero_prefix'         => 'Votre',
					'ag_hero_brand'          => 'Maçon',
					'ag_hero_subtitle'       => 'Construction, rénovation, extension. Devis sous 48h, travaux soignés, garantie décennale.',
					'ag_hero_button'         => 'Devis travaux',
					'ag_hero_button_url'     => '/devis/',
					'ag_artisan_metier_nom'  => 'Maçon',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Extension de 35m² livrée dans les délais, finitions impeccables. Équipe pro.|Bruno T.|Bordeaux',
					'ag_artisan_testi_2'     => 'Ravalement de façade complet, échafaudage propre, voisinage respecté.|Catherine L.|Strasbourg',
					'ag_artisan_testi_3'     => 'Dalle béton 80m² coulée en 1 journée, parfaitement nivelée. Excellent rapport qualité-prix.|Marc V.|Rennes',
				),
				'services' => array(
					array( 'emoji' => '🏠', 'title' => 'Construction maison' ),
					array( 'emoji' => '🏗️', 'title' => 'Extension' ),
					array( 'emoji' => '🧱', 'title' => 'Rénovation murs' ),
					array( 'emoji' => '⬜', 'title' => 'Dalles béton' ),
					array( 'emoji' => '🏛️', 'title' => 'Façades' ),
					array( 'emoji' => '🪨', 'title' => 'Murets / clôtures' ),
					array( 'emoji' => '🚜', 'title' => 'Terrassement' ),
					array( 'emoji' => '◼️', 'title' => 'Carrelage' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Visite gratuite du chantier', 'desc' => 'Sur place, on évalue le projet et on échange sur vos contraintes.' ),
					array( 'emoji' => '📐', 'title' => 'Devis détaillé sous 48h', 'desc' => 'Métré précis, matériaux chiffrés, planning prévisionnel.' ),
					array( 'emoji' => '🏗️', 'title' => 'Chantier livré', 'desc' => 'Suivi quotidien, propreté du chantier, garantie décennale signée.' ),
				),
				'faq' => array(
					array( 'q' => 'Avez-vous la garantie décennale ?', 'a' => 'Oui, garantie décennale obligatoire + biennale + responsabilité civile. Attestations fournies au devis.' ),
					array( 'q' => 'Combien de temps pour une extension ?', 'a' => 'Comptez 6 à 12 semaines selon m² + complexité (permis de construire à anticiper si > 20m²).' ),
					array( 'q' => 'Faites-vous le permis de construire ?', 'a' => 'Oui, nous accompagnons le dépôt en mairie et travaillons avec un architecte si > 150m².' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Entreprise familiale de maçonnerie fondée il y a plus de 25 ans, transmise de père en fils. Nous portons un savoir-faire artisanal traditionnel et une exigence de qualité qui font notre réputation.\n\nNotre équipe de 8 compagnons qualifiés (CAP, BP Maçonnerie, Brevet Pro) intervient sur tous types de chantiers : construction neuve, extension, rénovation lourde, ravalement. Chaque chantier est suivi personnellement par le chef d'entreprise, du devis à la livraison finale.\n\nNous sommes assurés en garantie décennale (Maaf Pro), biennale et responsabilité civile professionnelle. Attestations disponibles sur simple demande. Membres de la FFB (Fédération Française du Bâtiment) et certifiés Qualibat.",
					'prestations'        => "Toute notre maçonnerie générale : construction de maison neuve, extension agrandissement, rénovation de murs porteurs, dalle béton coulée, ravalement de façade, murets et clôtures, terrassement et pose de carrelage.\n\nNous intervenons en gros œuvre comme en finition. Chaque chantier démarre par une visite technique gratuite suivie d'un devis détaillé sous 48h. Travaux réalisés en interne (pas de sous-traitance non déclarée).\n\nMatériaux de qualité : parpaings normés CE, béton dosé chantier, ciments Lafarge, isolants ACERMI. Chantier propre quotidien, livraison aux délais annoncés, garantie décennale signée à réception.",
					'zones-intervention' => "Nous intervenons dans toute la région et les départements limitrophes pour tous travaux de maçonnerie. Pour les gros chantiers (construction neuve, extension > 20m²), nous nous déplaçons dans un rayon de 80 km autour de notre siège.\n\nPour les petits travaux (ravalement, muret, dalle), zone d'intervention de 30 km. Au-delà, nous restons disponibles avec frais de déplacement transparents.\n\nUrgences (mur effondré, dégât structurel) : intervention sous 24-48h dans notre zone principale. Contactez-nous au 06 00 00 00 00 pour un dépannage prioritaire.",
					'realisations'       => "Plus de 200 chantiers menés ces 25 dernières années : maisons individuelles neuves (du T3 à la grande villa), extensions toutes surfaces, ravalements complets, rénovation de granges et corps de ferme.\n\nNos chantiers récents incluent une extension de 45m² livrée en 11 semaines, un ravalement de façade 240m² avec isolation extérieure, une dalle de terrasse de 80m² coulée en 1 journée.\n\nDevenir une de nos prochaines références ? Demandez un devis gratuit — nous serons heureux de vous présenter nos chantiers similaires au vôtre lors de notre visite.",
				),
			),

			'boulanger' => array(
				'icon'  => '🥖',
				'label' => 'Boulanger / Pâtissier',
				'desc'  => 'Pains, viennoiseries, pâtisseries artisanales. Gâteaux sur commande, sandwich, buffet réception.',
				'mods'  => array(
					'ag_color_accent'        => '#C8923C',
					'ag_hero_prefix'         => 'Boulangerie',
					'ag_hero_brand'          => 'artisanale',
					'ag_hero_subtitle'       => 'Pains traditionnels, viennoiseries, pâtisseries — fait maison chaque jour avec des produits frais et locaux.',
					'ag_hero_button'         => 'Commander',
					'ag_hero_button_url'     => '/devis/',
					'ag_artisan_metier_nom'  => 'Boulanger / Pâtissier',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Le meilleur pain au levain du quartier, croûte parfaite. J\'y vais tous les matins.|Émilie F.|Lyon',
					'ag_artisan_testi_2'     => 'Gâteau d\'anniversaire sur commande : magnifique et délicieux. Tout le monde a adoré.|Thomas D.|Paris',
					'ag_artisan_testi_3'     => 'Buffet de 80 personnes livré à l\'heure, fraîcheur exceptionnelle, prestation au top.|Nora M.|Nice',
				),
				'services' => array(
					array( 'emoji' => '🥖', 'title' => 'Pains traditionnels' ),
					array( 'emoji' => '🥐', 'title' => 'Viennoiseries' ),
					array( 'emoji' => '🍰', 'title' => 'Pâtisseries fines' ),
					array( 'emoji' => '🎂', 'title' => 'Gâteaux sur commande' ),
					array( 'emoji' => '🥪', 'title' => 'Sandwich / snacking' ),
					array( 'emoji' => '🥧', 'title' => 'Tartes maison' ),
					array( 'emoji' => '🧁', 'title' => 'Macarons / petits fours' ),
					array( 'emoji' => '🍞', 'title' => 'Buffet réception' ),
				),
				'how' => array(
					array( 'emoji' => '🌾', 'title' => 'Farines locales', 'desc' => 'Nous travaillons avec des meuniers de la région. Traçabilité totale.' ),
					array( 'emoji' => '👨‍🍳', 'title' => 'Fait maison chaque jour', 'desc' => 'Pétrissage manuel, longue fermentation, cuisson au four à sole.' ),
					array( 'emoji' => '🛍️', 'title' => 'Disponible en boutique', 'desc' => 'Ouverture 6h–20h du mardi au dimanche. Commandes sur mesure 48h à l\'avance.' ),
				),
				'faq' => array(
					array( 'q' => 'Acceptez-vous les commandes sur mesure ?', 'a' => 'Oui — pièces montées, gâteaux personnalisés, buffets : passez commande 48h à l\'avance.' ),
					array( 'q' => 'Avez-vous du pain sans gluten ?', 'a' => 'Oui, une gamme sans gluten et une gamme bio. Demandez la liste complète en boutique.' ),
					array( 'q' => 'Livrez-vous pour les événements ?', 'a' => 'Oui, livraison locale pour mariages, baptêmes, événements pro. Devis sur demande.' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Boulangerie-pâtisserie artisanale familiale, ouverte depuis 18 ans dans le quartier. Trois générations de boulangers passionnés qui perpétuent les techniques traditionnelles : pétrissage manuel, longue fermentation, cuisson au four à sole.\n\nNotre équipe de 6 personnes (2 boulangers, 2 pâtissiers, 2 vendeurs) travaille chaque jour dès 4h du matin pour vous proposer du pain frais, des viennoiseries chaudes et des pâtisseries gourmandes.\n\nMembres de la Confédération Nationale de la Boulangerie Française. Label \"Boulanger de France\" obtenu en 2024. Farines moulues localement, beurre AOP, œufs fermiers, fruits de saison français.",
					'prestations'        => "Pains traditionnels au levain, baguettes tradition, pain de campagne, pain complet, pain aux céréales, pain sans gluten (gamme certifiée). Viennoiseries pur beurre AOP : croissants, pains au chocolat, brioches, chaussons aux pommes.\n\nPâtisseries fines : éclairs, religieuses, mille-feuilles, tartelettes saison. Gâteaux sur commande pour vos événements (anniversaires, mariages, baptêmes). Salé : sandwichs maison, quiches, fougasses, pizzas, formules midi à emporter.\n\nBuffets sucrés et salés livrés pour entreprises, réunions, événements privés. Devis personnalisé sur demande, à partir de 12 €/personne. Commandes sur mesure 48h à l'avance.",
					'zones-intervention' => "Boutique ouverte à votre adresse, du mardi au dimanche de 6h à 20h (fermé le lundi). Pour les commandes sur mesure et buffets, livraison locale dans un rayon de 15 km.\n\nLivraisons pour mariages et grands événements : possible sur tout le département avec devis préalable (frais de transport selon distance).\n\nClick & Collect disponible : passez commande la veille, retirez en boutique sans attente. Service traiteur sur réservation : visite préalable possible pour grands événements.",
					'realisations'       => "Plus de 5 000 mariages, baptêmes et événements approvisionnés ces 18 dernières années. Spécialité maison : la pièce montée traditionnelle (choux caramel), désormais déclinée en versions modernes (chocolat-passion, citron-yuzu).\n\nClients récents : 4 entreprises locales en contrat livraison hebdomadaire, 2 écoles en buffet de fin d'année, 60+ mariages par an.\n\nDécouvrez nos créations sur notre Instagram (@votreboulangerie) ou venez en boutique : vitrine renouvelée chaque saison. Pour un devis sur mesure, contactez-nous au 06 00 00 00 00.",
				),
			),

			'multiservice' => array(
				'icon'  => '🛠️',
				'label' => 'Multiservices',
				'desc'  => 'Plombier, électricien, peintre, jardinier — un seul interlocuteur pour tous types de travaux.',
				'mods'  => array(
					'ag_color_accent'        => '#2E86AB',
					'ag_hero_prefix'         => 'Tous travaux',
					'ag_hero_brand'          => 'multiservices',
					'ag_hero_subtitle'       => 'Plomberie, électricité, peinture, jardinage, bricolage. Un seul interlocuteur, intervention rapide.',
					'ag_hero_button'         => 'Demander un devis',
					'ag_hero_button_url'     => '/devis/',
					'ag_artisan_metier_nom'  => 'Multiservices',
					'ag_artisan_hero_image'  => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1600&q=80',
					'ag_artisan_testi_1'     => 'Plomberie, peinture, montage de meubles : tout fait en 1 jour. Pratique !|Anne C.|Montpellier',
					'ag_artisan_testi_2'     => 'Équipe sympa, devis transparent, travail soigné. Je rappelle pour la suite.|Mehdi A.|Lille',
					'ag_artisan_testi_3'     => 'Jardin réaménagé + clôture + abri : un seul artisan pour tout. Super expérience.|Valérie P.|Toulouse',
				),
				'services' => array(
					array( 'emoji' => '🚿', 'title' => 'Plomberie' ),
					array( 'emoji' => '⚡', 'title' => 'Électricité' ),
					array( 'emoji' => '🎨', 'title' => 'Peinture' ),
					array( 'emoji' => '🌿', 'title' => 'Jardinage' ),
					array( 'emoji' => '🔧', 'title' => 'Bricolage' ),
					array( 'emoji' => '📦', 'title' => 'Déménagement' ),
					array( 'emoji' => '🧹', 'title' => 'Nettoyage' ),
					array( 'emoji' => '🛠️', 'title' => 'Réparations' ),
				),
				'how' => array(
					array( 'emoji' => '📞', 'title' => 'Un seul interlocuteur', 'desc' => 'Décrivez l\'ensemble de vos besoins, on coordonne tout pour vous.' ),
					array( 'emoji' => '📋', 'title' => 'Devis groupé', 'desc' => 'Devis unique pour plusieurs prestations = économies + planning optimisé.' ),
					array( 'emoji' => '🛠️', 'title' => 'Intervention coordonnée', 'desc' => 'Notre équipe enchaîne les prestations, vous gagnez du temps.' ),
				),
				'faq' => array(
					array( 'q' => 'Travaillez-vous pour les pros aussi ?', 'a' => 'Oui — bureaux, commerces, copropriétés. Facture et devis professionnels sur demande.' ),
					array( 'q' => 'Quels types de travaux refusez-vous ?', 'a' => 'Très gros œuvre (charpente, terrassement lourd) — pour cela on vous oriente vers un confrère spécialisé.' ),
					array( 'q' => 'Acceptez-vous les chèques emploi-service (CESU) ?', 'a' => 'Oui pour les particuliers, sur les prestations éligibles (jardinage, petit bricolage, ménage).' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Société de multiservices créée il y a 8 ans, nous coordonnons pour vous tous les corps de métier du bâtiment et de l'entretien : plombier, électricien, peintre, jardinier, menuisier, déménageur.\n\nNotre équipe permanente de 12 personnes (toutes spécialités confondues) intervient sur particuliers et professionnels. Plus besoin de chercher plusieurs artisans : un seul devis, un seul interlocuteur, une équipe coordonnée pour vos travaux.\n\nAssurances : RC pro + décennale + protection juridique professionnelle. Membres CESU+. Travail déclaré, factures aux normes, devis transparents. Plus de 1 800 missions réalisées avec un taux de satisfaction client de 96%.",
					'prestations'        => "Plomberie (fuite, robinetterie, sanitaires, chauffe-eau), électricité (dépannage, prises, luminaires), peinture intérieure (murs, plafonds, boiseries), jardinage (tonte, taille, débroussaillage, entretien régulier).\n\nBricolage : montage de meubles, fixations, pose d'étagères, petits travaux. Déménagement local (studio à T4) avec emballage et démontage/remontage. Nettoyage de fin de chantier ou ménage récurrent. Réparations diverses (vitrage, serrurerie, dépannage urgences).\n\nDevis groupé pour plusieurs prestations = économies + un seul planning. Intervention coordonnée par notre chef de projet pour minimiser votre temps perdu.",
					'zones-intervention' => "Zone d'intervention principale : agglomération + 25 km autour. Pour les chantiers programmés (rénovation multi-corps, déménagement), nous nous déplaçons dans tout le département.\n\nUrgences (fuite, panne) : intervention prioritaire 7j/7 dans la zone principale, arrivée sous 4h en jour ouvré, sous 2h en astreinte (avec majoration).\n\nContrats d'entretien récurrent pour copropriétés, bureaux et résidences secondaires : passage hebdomadaire, mensuel ou trimestriel selon vos besoins. Tarifs dégressifs pour contrat annuel.",
					'realisations'       => "Plus de 1 800 missions réalisées en 8 ans, de la simple intervention plomberie à la rénovation multi-corps complète. Spécialité maison : la rénovation d'appartement de location entre 2 locataires (peinture + petite plomberie + électricité + ménage) en 5 jours.\n\nClients récurrents : 14 copropriétés en contrat d'entretien, 8 bureaux/commerces en contrat annuel, des centaines de particuliers qui nous appellent au fil des besoins.\n\nVous voulez voir nos chantiers en images ? Page Réalisations en construction. En attendant, demandez-nous des références similaires à votre projet lors du devis — nous communiquons volontiers.",
				),
			),

		);
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 22 );
		add_action( 'admin_post_ag_artisan_apply_preset', array( __CLASS__, 'handle_apply' ) );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Configuration métier', 'ag-starter-artisan' ),
			'🎯 ' . __( 'Configuration métier', 'ag-starter-artisan' ),
			'manage_options',
			'ag-artisan-presets',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$applied = isset( $_GET['applied'] ) ? sanitize_key( $_GET['applied'] ) : '';
		$current = get_theme_mod( 'ag_artisan_metier_slug', '' );
		$presets = self::get_presets();
		?>
		<div class="wrap">
			<h1>🎯 <?php esc_html_e( 'Configuration métier — AG Starter Artisan', 'ag-starter-artisan' ); ?></h1>
			<p style="font-size:1.05em;color:#50575e;max-width:820px;"><?php esc_html_e( 'Choisissez votre corps de métier ci-dessous. En 1 clic, le thème adapte le hero, la couleur d\'accent et la grille des 8 services affichée sur la page d\'accueil. Vous pourrez ensuite affiner chaque texte dans Apparence > Personnaliser.', 'ag-starter-artisan' ); ?></p>

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
						<input type="hidden" name="action" value="ag_artisan_apply_preset" />
						<input type="hidden" name="preset" value="<?php echo esc_attr( $slug ); ?>" />
						<?php wp_nonce_field( 'ag_artisan_apply_preset' ); ?>
						<button type="submit" class="button button-primary button-large" style="width:100%;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;border-color:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;font-weight:700;">
							<?php echo $is_current ? '🔄 Réappliquer' : '✨ Appliquer ce preset'; ?>
						</button>
					</form>
				</div>
				<?php endforeach; ?>
			</div>

			<p style="margin-top:32px;color:#777;font-size:.9em;">
				💡 <?php esc_html_e( 'Après application, fine-tunez via', 'ag-starter-artisan' ); ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Apparence > Personnaliser', 'ag-starter-artisan' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-artisan-reset' ) ); ?>">🔄 <?php esc_html_e( 'Réinitialiser le thème', 'ag-starter-artisan' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function handle_apply() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_artisan_apply_preset' );
		$slug    = isset( $_POST['preset'] ) ? sanitize_key( $_POST['preset'] ) : '';
		$presets = self::get_presets();
		if ( ! isset( $presets[ $slug ] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-presets' ) );
			exit;
		}
		$preset = $presets[ $slug ];

		// 1. Applique les theme_mods
		foreach ( $preset['mods'] as $key => $val ) {
			set_theme_mod( $key, $val );
		}

		// 2. Stocke le slug actif (pour affichage badge "actuellement applique")
		set_theme_mod( 'ag_artisan_metier_slug', $slug );

		// 3. Stocke la liste des services en JSON (relu par front-page.php)
		set_theme_mod( 'ag_artisan_services_json', wp_json_encode( $preset['services'] ) );

		// 4. Update les pages WP avec le contenu metier-specifique
		// (qui-sommes-nous, prestations, zones-intervention, realisations).
		// On preserve les pages user qui ont ete editees manuellement :
		// si la page actuelle ne contient PAS un texte placeholder generique
		// connu, on ne l'ecrase pas.
		if ( ! empty( $preset['pages'] ) && is_array( $preset['pages'] ) ) {
			foreach ( $preset['pages'] as $slug => $content ) {
				$page = get_page_by_path( $slug );
				if ( ! $page ) continue;
				// Wrap dans des blocks Gutenberg paragraph (split par \n\n).
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

		// 5. Purge les caches (LiteSpeed front + transients WP) pour que le
		// nouveau hero / la nouvelle grille soit visible immediatement sur
		// la home, sans attendre l'expiration de cache.
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		wp_cache_flush();
		delete_site_transient( 'update_themes' );

		wp_safe_redirect( admin_url( 'themes.php?page=ag-artisan-presets&applied=' . $slug ) );
		exit;
	}

	/**
	 * Helper appele depuis front-page.php pour recuperer la liste des
	 * services a afficher dans la grille (decodee depuis le JSON).
	 *
	 * @return array Tableau de services [['emoji' => '⚡', 'title' => '...'], ...]
	 *               ou tableau vide si aucun preset applique.
	 */
	public static function get_active_services() {
		$json = get_theme_mod( 'ag_artisan_services_json', '' );
		if ( ! $json ) return array();
		$arr = json_decode( $json, true );
		return is_array( $arr ) ? $arr : array();
	}

	/**
	 * Recupere le preset actuellement applique (data complete, pas que mods).
	 *
	 * @return array|null
	 */
	public static function get_active_preset() {
		$slug = get_theme_mod( 'ag_artisan_metier_slug', '' );
		if ( ! $slug ) return null;
		$presets = self::get_presets();
		return isset( $presets[ $slug ] ) ? $presets[ $slug ] : null;
	}

	/** @return array [['emoji', 'title', 'desc'], ...] */
	public static function get_active_how() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['how'] ) ) ? $p['how'] : array();
	}

	/** @return array [['q', 'a'], ...] */
	public static function get_active_faq() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['faq'] ) ) ? $p['faq'] : array();
	}

	/**
	 * Recupere un temoignage stocke en theme_mod (format "texte|nom|ville").
	 * @return array|null ['text', 'name', 'city']
	 */
	public static function get_testimonial( $i ) {
		$raw = get_theme_mod( 'ag_artisan_testi_' . (int) $i, '' );
		if ( ! $raw ) return null;
		$parts = array_map( 'trim', explode( '|', $raw ) );
		return array(
			'text' => isset( $parts[0] ) ? $parts[0] : '',
			'name' => isset( $parts[1] ) ? $parts[1] : '',
			'city' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
}
AG_Artisan_Presets::init();

/**
 * Quand un preset est applique, on ajoute la classe body 'ag-premium-mode'
 * pour permettre au CSS d'overrider le theme sombre par defaut et passer
 * en palette claire style meilleur-artisan.com.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( class_exists( 'AG_Artisan_Presets' ) && AG_Artisan_Presets::get_active_preset() ) {
		$classes[] = 'ag-premium-mode';
		$slug = get_theme_mod( 'ag_artisan_metier_slug', '' );
		if ( $slug ) {
			$classes[] = 'ag-metier-' . sanitize_html_class( $slug );
		}
	}
	return $classes;
} );
