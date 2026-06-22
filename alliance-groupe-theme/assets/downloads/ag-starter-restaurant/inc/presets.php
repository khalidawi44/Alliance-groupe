<?php
/**
 * Presets metier — 5 configurations rapides pour adapter le theme a
 * differents corps de metier (electricien, macon, boulanger, etc.) en
 * 1 clic. Chaque preset configure le hero, la couleur d'accent et
 * la grille de services affichee sur la page d'accueil.
 *
 * Style visuel inspire de meilleur-restaurant.com : palette orange/blanc,
 * grille services 4x2 avec emojis, hero centre, CTA contrastant.
 *
 * Apparait dans : "Apparence > 🎯 Configuration metier".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Presets {

	/**
	 * Definition des presets. Chaque preset declare ses theme_mods et
	 * sa liste de services (8 max pour grille 4x2).
	 */
	public static function get_presets() {
		return array(

			'traditionnel' => array(
				'icon'  => '🍽️',
				'label' => 'Restaurant traditionnel',
				'desc'  => 'Cuisine française maison, produits frais et de saison. Pour bistrot, auberge, table traditionnelle.',
				'mods'  => array(
					// Versailles : sombre & or.
					'ag_color_accent'          => '#e3bb4f',
					'ag_color_accent2'         => '#c98a2e',
					'ag_color_background'      => '#13110c',
					'ag_color_panel'           => '#1c1813',
					'ag_color_border'          => '#3a2f1e',
					'ag_color_text'            => '#f4eedd',
					'ag_color_heading'         => '#fbf5e6',
					'ag_color_muted'           => '#d4c8a6',
					'ag_hero_prefix'            => 'Bienvenue chez',
					'ag_hero_brand'             => 'Votre Restaurant',
					'ag_hero_subtitle'          => 'Cuisine authentique faite maison, produits frais et de saison, au cœur de votre ville. Réservez votre table dès maintenant.',
					'ag_hero_button'            => 'Réserver une table',
					'ag_hero_button_url'        => '/carte/',
					'ag_restaurant_metier_nom'  => 'Restaurant',
					'ag_restaurant_hero_image'  => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1600&q=80',
					'ag_restaurant_testi_1'     => 'Une cuisine généreuse et savoureuse, un accueil chaleureux. Notre table préférée du quartier.|Sophie M.|Lyon',
					'ag_restaurant_testi_2'     => 'Produits frais, plats faits maison, rapport qualité-prix imbattable. On revient chaque semaine.|Karim B.|Marseille',
					'ag_restaurant_testi_3'     => 'Cadre chaleureux, service aux petits soins, desserts maison à tomber. Bravo !|Laure D.|Paris',
				),
				'services' => array(
					array( 'emoji' => '🍽️', 'title' => 'Menu du midi' ),
					array( 'emoji' => '🍷', 'title' => 'Carte des vins' ),
					array( 'emoji' => '📅', 'title' => 'Réservation' ),
					array( 'emoji' => '🎉', 'title' => 'Privatisation' ),
					array( 'emoji' => '🥗', 'title' => 'Produits frais' ),
					array( 'emoji' => '🍰', 'title' => 'Desserts maison' ),
					array( 'emoji' => '🥡', 'title' => 'À emporter' ),
					array( 'emoji' => '☀️', 'title' => 'Terrasse' ),
				),
				'how' => array(
					array( 'emoji' => '📅', 'title' => 'Réservez votre table', 'desc' => 'En ligne ou par téléphone, en quelques secondes, à l\'heure qui vous convient.' ),
					array( 'emoji' => '🍷', 'title' => 'Installez-vous', 'desc' => 'Accueil chaleureux, cadre soigné, et notre carte du moment à découvrir.' ),
					array( 'emoji' => '😋', 'title' => 'Régalez-vous', 'desc' => 'Une cuisine maison préparée chaque jour avec des produits frais et de saison.' ),
				),
				'faq' => array(
					array( 'q' => 'Faut-il réserver ?', 'a' => 'C\'est conseillé, surtout le week-end et le soir. Réservation en ligne ou par téléphone.' ),
					array( 'q' => 'Avez-vous des plats végétariens / sans gluten ?', 'a' => 'Oui, plusieurs options à la carte. Prévenez-nous de vos allergies, la cuisine s\'adapte.' ),
					array( 'q' => 'Peut-on privatiser la salle ?', 'a' => 'Oui, pour vos événements (anniversaire, repas d\'entreprise). Menu de groupe sur demande.' ),
				),
				'stats' => array(
					array( 'value' => '120',    'label' => 'Couverts / jour' ),
					array( 'value' => '4,8/5',  'label' => 'Avis Google' ),
					array( 'value' => '15 ans', 'label' => 'À votre service' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Restaurant familial au cœur de la ville depuis plus de 15 ans, nous défendons une cuisine française authentique, faite maison chaque jour à partir de produits frais et de saison.\n\nNotre chef sélectionne ses produits auprès de producteurs locaux. Carte courte qui change au fil des saisons, plats mijotés, desserts maison. Accueil chaleureux et service attentionné dans un cadre convivial.\n\nQue ce soit pour un déjeuner d'affaires, un dîner en amoureux ou un repas de famille, nous mettons un point d'honneur à vous régaler. Réservation conseillée.",
					'prestations'        => "Menu du midi (entrée + plat + dessert) à prix doux en semaine, carte complète le soir, suggestions du moment à l'ardoise. Carte des vins sélectionnée pour accompagner chaque plat.\n\nFormules groupes et privatisation de salle pour vos événements : anniversaires, repas d'entreprise, communions. Plats à emporter et terrasse aux beaux jours.\n\nProduits frais, faits maison, de saison : c'est notre engagement à chaque assiette.",
					'zones-intervention' => "Ouvert du mardi au dimanche, midi et soir (fermé le lundi). Service continu en terrasse l'été. Réservation en ligne ou par téléphone, fortement conseillée le week-end.\n\nAu cœur du centre-ville, accès facile, parking à proximité. Salle climatisée, terrasse ombragée, espace adapté aux familles.\n\nPour les groupes (à partir de 10 personnes) et la privatisation, contactez-nous pour un menu et un devis personnalisés.",
					'realisations'       => "Une carte qui évolue avec les saisons et un livre d'or qui parle pour nous : note moyenne 4,8/5 sur les avis clients vérifiés, des habitués fidèles depuis des années.\n\nNos incontournables : plats mijotés du terroir, suggestions du marché, desserts maison. Suivez-nous pour découvrir les plats du moment.\n\nEnvie de voir nos assiettes ? Demandez la carte ou suivez nos réseaux : on y poste les suggestions du jour.",
				),
			),

			'pizzeria' => array(
				'icon'  => '🍕',
				'label' => 'Pizzeria / Italien',
				'desc'  => 'Pizzas au feu de bois, pâtes fraîches, cuisine italienne. Pour pizzeria, trattoria, restaurant italien.',
				'mods'  => array(
					// Couleurs de l'Italie (tricolore) : fond creme clair, titres verts,
					// accent rouge. Ambiance trattoria.
					'ag_color_accent'          => '#CD212A',
					'ag_color_accent2'         => '#14843b',
					'ag_color_background'       => '#fbf7ee',
					'ag_color_panel'            => '#ffffff',
					'ag_color_border'           => '#e7decb',
					'ag_color_text'             => '#2b2a26',
					'ag_color_heading'          => '#14843b',
					'ag_color_muted'            => '#6e6a60',
					'ag_hero_prefix'            => 'Bienvenue à la',
					'ag_hero_brand'             => 'Pizzeria',
					'ag_hero_subtitle'          => 'Pizzas au feu de bois, pâtes fraîches maison, vraie cuisine italienne. Sur place, à emporter ou en livraison.',
					'ag_hero_button'            => 'Commander / Réserver',
					'ag_hero_button_url'        => '/carte/',
					'ag_restaurant_metier_nom'  => 'Pizzeria',
					'ag_restaurant_hero_image'  => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1600&q=80',
					'ag_restaurant_testi_1'     => 'La meilleure pizza de la ville, pâte fine et croustillante, ingrédients top. Un régal !|Marco P.|Nice',
					'ag_restaurant_testi_2'     => 'Pâtes fraîches maison comme en Italie, portions généreuses. On se croirait à Naples.|Julie R.|Lyon',
					'ag_restaurant_testi_3'     => 'Service rapide, accueil au top, et ce four à bois fait toute la différence. Bravo !|Sébastien L.|Paris',
				),
				'services' => array(
					array( 'emoji' => '🍕', 'title' => 'Pizzas au feu de bois' ),
					array( 'emoji' => '🍝', 'title' => 'Pâtes fraîches' ),
					array( 'emoji' => '🥗', 'title' => 'Antipasti' ),
					array( 'emoji' => '🛵', 'title' => 'Livraison' ),
					array( 'emoji' => '🥡', 'title' => 'À emporter' ),
					array( 'emoji' => '🍷', 'title' => 'Vins italiens' ),
					array( 'emoji' => '🍨', 'title' => 'Desserts maison' ),
					array( 'emoji' => '📅', 'title' => 'Réservation' ),
				),
				'how' => array(
					array( 'emoji' => '📱', 'title' => 'Commandez', 'desc' => 'Sur place, à emporter ou en livraison : en ligne ou par téléphone, en 1 minute.' ),
					array( 'emoji' => '🔥', 'title' => 'On prépare au feu de bois', 'desc' => 'Pâte levée maison, ingrédients frais, cuisson au four à bois traditionnel.' ),
					array( 'emoji' => '😋', 'title' => 'Buon appetito !', 'desc' => 'Chaude et croustillante, livrée chez vous ou servie à table en quelques minutes.' ),
				),
				'faq' => array(
					array( 'q' => 'Livrez-vous à domicile ?', 'a' => 'Oui, dans un rayon défini autour du restaurant. Commande en ligne ou par téléphone.' ),
					array( 'q' => 'Faites-vous des pizzas sans gluten / végétariennes ?', 'a' => 'Oui, pâte sans gluten sur demande et nombreuses pizzas végétariennes à la carte.' ),
					array( 'q' => 'Peut-on réserver pour un groupe ?', 'a' => 'Bien sûr, réservation conseillée pour les groupes. Menu de groupe possible.' ),
				),
				'stats' => array(
					array( 'value' => '200+',   'label' => 'Pizzas / jour' ),
					array( 'value' => '4,9/5',  'label' => 'Avis Google' ),
					array( 'value' => '100%',   'label' => 'Pâte maison' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Une vraie pizzeria italienne où la pâte est pétrie et levée chaque jour sur place, et les pizzas cuites dans notre four à bois traditionnel.\n\nNos recettes viennent d'Italie : mozzarella fior di latte, tomates San Marzano, basilic frais, huile d'olive extra-vierge. Pâtes fraîches maison, antipasti, tiramisu maison.\n\nSur place dans une ambiance conviviale, à emporter ou en livraison : la dolce vita à votre porte.",
					'prestations'        => "Pizzas au feu de bois (classiques et créations du chef), pâtes fraîches maison, antipasti, salades, desserts italiens maison. Sélection de vins et bières italiennes.\n\nService sur place, à emporter et livraison à domicile. Formules midi, menus groupes et soirées privatisées.\n\nIngrédients frais, recettes authentiques, cuisson au four à bois : la différence se goûte.",
					'zones-intervention' => "Ouvert 7j/7 midi et soir. Livraison dans un rayon autour du restaurant, commande en ligne ou par téléphone. À emporter en 15 minutes.\n\nSalle conviviale, terrasse aux beaux jours, idéal en famille ou entre amis. Réservation conseillée le week-end.\n\nGroupes et événements : menus dédiés et privatisation possibles, contactez-nous.",
					'realisations'       => "Plus de 200 pizzas servies chaque jour et une note de 4,9/5 sur les avis clients : la preuve que la pâte maison et le feu de bois font la différence.\n\nNos best-sellers : la Margherita, la Diavola, et les créations du chef qui changent chaque mois. Suivez-nous pour les nouveautés.\n\nUne envie ? Commandez en ligne ou passez nous voir, on s'occupe du reste.",
				),
			),

			'bistrot' => array(
				'icon'  => '🍷',
				'label' => 'Bistrot / Brasserie',
				'desc'  => 'Ambiance conviviale, cuisine de bistrot, plats du jour. Pour brasserie, bar à vins, bistrot de quartier.',
				'mods'  => array(
					// Bistrot : bois chaleureux, bordeaux, fond creme.
					'ag_color_accent'          => '#b5402f',
					'ag_color_accent2'         => '#8a6d3b',
					'ag_color_background'      => '#f6efe3',
					'ag_color_panel'           => '#fffaf2',
					'ag_color_border'          => '#e4d8c4',
					'ag_color_text'            => '#2c241c',
					'ag_color_heading'         => '#7a2d2d',
					'ag_color_muted'           => '#6f6453',
					'ag_hero_prefix'            => 'Bienvenue au',
					'ag_hero_brand'             => 'Bistrot',
					'ag_hero_subtitle'          => 'L\'ambiance d\'un vrai bistrot : plats du jour, planches à partager, vins de copains. Le bon endroit pour se retrouver.',
					'ag_hero_button'            => 'Réserver une table',
					'ag_hero_button_url'        => '/carte/',
					'ag_restaurant_metier_nom'  => 'Bistrot',
					'ag_restaurant_hero_image'  => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1600&q=80',
					'ag_restaurant_testi_1'     => 'L\'ambiance bistrot parfaite, des plats du jour qui changent, une belle carte de vins. Un coup de cœur.|Antoine D.|Bordeaux',
					'ag_restaurant_testi_2'     => 'Planches généreuses, patron sympa, vins au verre excellents. On y est comme à la maison.|Nadia K.|Lille',
					'ag_restaurant_testi_3'     => 'Le rendez-vous de notre bande depuis 2 ans. Cuisine simple et bonne, ambiance au top.|Thomas V.|Nantes',
				),
				'services' => array(
					array( 'emoji' => '🍽️', 'title' => 'Plat du jour' ),
					array( 'emoji' => '🧀', 'title' => 'Planches à partager' ),
					array( 'emoji' => '🍷', 'title' => 'Bar à vins' ),
					array( 'emoji' => '🍺', 'title' => 'Bières pression' ),
					array( 'emoji' => '☀️', 'title' => 'Terrasse' ),
					array( 'emoji' => '🎵', 'title' => 'Soirées à thème' ),
					array( 'emoji' => '📅', 'title' => 'Réservation' ),
					array( 'emoji' => '🎉', 'title' => 'Privatisation' ),
				),
				'how' => array(
					array( 'emoji' => '📅', 'title' => 'Réservez ou passez', 'desc' => 'Réservation conseillée le soir et le week-end, sinon poussez la porte !' ),
					array( 'emoji' => '🍷', 'title' => 'Détendez-vous', 'desc' => 'Ambiance chaleureuse, ardoise du jour, vins au verre et planches à partager.' ),
					array( 'emoji' => '🥂', 'title' => 'Profitez', 'desc' => 'Le bon endroit pour un afterwork, un dîner entre amis ou un verre tranquille.' ),
				),
				'faq' => array(
					array( 'q' => 'Le plat du jour change-t-il ?', 'a' => 'Oui, l\'ardoise change chaque jour selon le marché. Suivez-nous pour le découvrir.' ),
					array( 'q' => 'Servez-vous en continu ?', 'a' => 'Planches et vins en service continu l\'après-midi ; cuisine aux heures des repas.' ),
					array( 'q' => 'Peut-on privatiser pour une soirée ?', 'a' => 'Oui, le bistrot se privatise pour vos afterworks et anniversaires. Devis sur demande.' ),
				),
				'stats' => array(
					array( 'value' => '40+',    'label' => 'Vins à la carte' ),
					array( 'value' => '4,7/5',  'label' => 'Avis Google' ),
					array( 'value' => '7j/7',   'label' => 'Ouvert' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Un bistrot de quartier comme on les aime : convivial, sans chichi, où l'on mange bien et où l'on se sent chez soi.\n\nArdoise du jour selon le marché, planches de charcuterie et fromages à partager, belle sélection de vins au verre choisis chez de petits producteurs. Le tout dans une ambiance chaleureuse.\n\nLe rendez-vous idéal pour un déjeuner sur le pouce, un afterwork entre collègues ou un dîner détendu entre amis.",
					'prestations'        => "Plats du jour à l'ardoise, cuisine de bistrot généreuse, planches à partager (charcuterie, fromages, mixtes). Belle carte de vins au verre et à la bouteille, bières pression, cocktails.\n\nTerrasse aux beaux jours, soirées à thème, privatisation pour vos événements. Service continu pour les planches et l'apéro.\n\nUne cuisine simple et franche, des produits bien choisis, une ambiance qui donne envie de rester.",
					'zones-intervention' => "Ouvert 7j/7, du déjeuner jusqu'au soir, avec service continu pour l'apéro et les planches. Terrasse l'été. Réservation conseillée le soir et le week-end.\n\nEn plein cœur du quartier, facile d'accès. Ambiance décontractée, idéale en groupe.\n\nPrivatisation possible pour vos afterworks, anniversaires et événements : contactez-nous pour un devis.",
					'realisations'       => "Devenu le QG de tout un quartier : note 4,7/5, des habitués qui reviennent et une ambiance qui fait sa réputation.\n\nNos classiques : l'ardoise du jour, les planches généreuses, les vins de copains. Suivez-nous pour les soirées à thème.\n\nVenez vous faire votre propre avis autour d'un verre : la première tournée donne le ton.",
				),
			),

			'gastronomique' => array(
				'icon'  => '⭐',
				'label' => 'Gastronomique',
				'desc'  => 'Cuisine raffinée, dressage soigné, expérience d\'exception. Pour table gastronomique, restaurant étoilé.',
				'mods'  => array(
					// Gastronomique : noir profond, or champagne.
					'ag_color_accent'          => '#b89b5e',
					'ag_color_accent2'         => '#8c7340',
					'ag_color_background'      => '#0e0e10',
					'ag_color_panel'           => '#17171b',
					'ag_color_border'          => '#2a2a30',
					'ag_color_text'            => '#ece9e2',
					'ag_color_heading'         => '#f3ead4',
					'ag_color_muted'           => '#b9b3a6',
					'ag_hero_prefix'            => 'L\'expérience',
					'ag_hero_brand'             => 'Gastronomique',
					'ag_hero_subtitle'          => 'Une cuisine d\'auteur, des produits d\'exception, un dressage soigné. Vivez une expérience culinaire mémorable.',
					'ag_hero_button'            => 'Réserver',
					'ag_hero_button_url'        => '/carte/',
					'ag_restaurant_metier_nom'  => 'Restaurant gastronomique',
					'ag_restaurant_hero_image'  => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1600&q=80',
					'ag_restaurant_testi_1'     => 'Une expérience d\'exception, chaque plat est une œuvre. Service irréprochable. À vivre absolument.|Claire F.|Paris',
					'ag_restaurant_testi_2'     => 'Menu dégustation bluffant, accord mets-vins parfait. Un moment suspendu.|Olivier M.|Lyon',
					'ag_restaurant_testi_3'     => 'Raffinement, créativité, produits sublimes. Le dîner de notre anniversaire restera gravé.|Inès B.|Bordeaux',
				),
				'services' => array(
					array( 'emoji' => '⭐', 'title' => 'Menu dégustation' ),
					array( 'emoji' => '🍷', 'title' => 'Accord mets-vins' ),
					array( 'emoji' => '👨‍🍳', 'title' => 'Cuisine d\'auteur' ),
					array( 'emoji' => '🥂', 'title' => 'Carte des vins' ),
					array( 'emoji' => '📅', 'title' => 'Réservation' ),
					array( 'emoji' => '🎩', 'title' => 'Service à table' ),
					array( 'emoji' => '🎉', 'title' => 'Événements privés' ),
					array( 'emoji' => '🌿', 'title' => 'Produits d\'exception' ),
				),
				'how' => array(
					array( 'emoji' => '📅', 'title' => 'Réservez votre table', 'desc' => 'Réservation indispensable. Choisissez votre menu et vos éventuels accords.' ),
					array( 'emoji' => '🍽️', 'title' => 'Laissez-vous porter', 'desc' => 'Un service attentionné vous guide à travers chaque plat et chaque accord.' ),
					array( 'emoji' => '✨', 'title' => 'Vivez l\'expérience', 'desc' => 'Une succession de créations raffinées, pensées pour les sens et la mémoire.' ),
				),
				'faq' => array(
					array( 'q' => 'Faut-il réserver à l\'avance ?', 'a' => 'Oui, la réservation est indispensable, idéalement plusieurs jours avant.' ),
					array( 'q' => 'Proposez-vous un menu végétarien ?', 'a' => 'Oui, un menu dégustation végétarien sur demande, à préciser lors de la réservation.' ),
					array( 'q' => 'Y a-t-il un code vestimentaire ?', 'a' => 'Tenue élégante appréciée. Nous privilégions une atmosphère raffinée et feutrée.' ),
				),
				'stats' => array(
					array( 'value' => '7',      'label' => 'Plats au menu' ),
					array( 'value' => '4,9/5',  'label' => 'Avis Google' ),
					array( 'value' => '20 ans', 'label' => 'De passion' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Une table gastronomique où la cuisine devient un art. Notre chef compose une cuisine d'auteur, au rythme des saisons et des plus beaux produits.\n\nChaque assiette est pensée comme une œuvre : justesse des cuissons, finesse des accords, dressage soigné. Une carte des vins remarquable et un service attentionné subliment l'expérience.\n\nUn lieu pour les grandes occasions comme pour le simple plaisir d'une belle table. La réservation est indispensable.",
					'prestations'        => "Menu dégustation en plusieurs services, carte au gré des saisons, accords mets-vins proposés par notre sommelier. Cuisine d'auteur, produits d'exception, dressage raffiné.\n\nService à table soigné, cadre élégant et feutré. Événements privés et repas d'affaires haut de gamme sur demande.\n\nUne expérience culinaire complète, pensée dans le moindre détail.",
					'zones-intervention' => "Ouvert du mardi au samedi, le soir, et le midi sur réservation. Fermé dimanche et lundi. Réservation indispensable, plusieurs jours à l'avance recommandés.\n\nCadre élégant et intimiste, parking et voiturier à proximité. Salon privé pour vos événements.\n\nPour les repas d'affaires, demandes spéciales et privatisation, contactez-nous : nous composons une expérience sur mesure.",
					'realisations'       => "Une cuisine saluée par une clientèle exigeante : note 4,9/5, des convives qui reviennent pour chaque nouvelle carte de saison.\n\nNotre signature : un menu dégustation qui raconte une histoire, du premier amuse-bouche au dernier dessert. Accords mets-vins sur mesure.\n\nPour découvrir notre carte du moment, réservez : chaque saison apporte ses nouvelles créations.",
				),
			),

			'creperie' => array(
				'icon'  => '🥞',
				'label' => 'Crêperie',
				'desc'  => 'Galettes de sarrasin, crêpes sucrées, cidre. Pour crêperie, restaurant breton.',
				'mods'  => array(
					// Creperie : Bretagne (bleu/blanc) + caramel beurre.
					'ag_color_accent'          => '#d98e04',
					'ag_color_accent2'         => '#1f5e8b',
					'ag_color_background'      => '#f5f8fb',
					'ag_color_panel'           => '#ffffff',
					'ag_color_border'          => '#dce6ee',
					'ag_color_text'            => '#25313a',
					'ag_color_heading'         => '#1f5e8b',
					'ag_color_muted'           => '#6a7178',
					'ag_hero_prefix'            => 'Bienvenue à la',
					'ag_hero_brand'             => 'Crêperie',
					'ag_hero_subtitle'          => 'Galettes de sarrasin garnies, crêpes sucrées gourmandes, bon cidre. La Bretagne dans votre assiette.',
					'ag_hero_button'            => 'Réserver une table',
					'ag_hero_button_url'        => '/carte/',
					'ag_restaurant_metier_nom'  => 'Crêperie',
					'ag_restaurant_hero_image'  => 'https://images.unsplash.com/photo-1519676867240-f03562e64548?w=1600&q=80',
					'ag_restaurant_testi_1'     => 'Galettes croustillantes, garnitures généreuses, cidre parfait. On se croirait en Bretagne !|Yann L.|Rennes',
					'ag_restaurant_testi_2'     => 'Les crêpes sucrées sont une tuerie, le caramel beurre salé maison... un délice.|Camille R.|Nantes',
					'ag_restaurant_testi_3'     => 'Accueil adorable, prix doux, parfait en famille. Notre crêperie du dimanche !|Hélène M.|Brest',
				),
				'services' => array(
					array( 'emoji' => '🥞', 'title' => 'Galettes sarrasin' ),
					array( 'emoji' => '🍮', 'title' => 'Crêpes sucrées' ),
					array( 'emoji' => '🍏', 'title' => 'Cidre & boissons' ),
					array( 'emoji' => '🧈', 'title' => 'Caramel maison' ),
					array( 'emoji' => '🥗', 'title' => 'Salades' ),
					array( 'emoji' => '🥡', 'title' => 'À emporter' ),
					array( 'emoji' => '☀️', 'title' => 'Terrasse' ),
					array( 'emoji' => '📅', 'title' => 'Réservation' ),
				),
				'how' => array(
					array( 'emoji' => '📅', 'title' => 'Réservez ou passez', 'desc' => 'Réservation conseillée le week-end. En semaine, poussez la porte !' ),
					array( 'emoji' => '🥞', 'title' => 'Composez votre galette', 'desc' => 'Sarrasin garni au choix, crêpes sucrées gourmandes, le tout fait minute.' ),
					array( 'emoji' => '🍏', 'title' => 'Régalez-vous', 'desc' => 'Le tout accompagné d\'un bon bol de cidre, dans une ambiance chaleureuse.' ),
				),
				'faq' => array(
					array( 'q' => 'Les galettes sont-elles sans gluten ?', 'a' => 'La farine de sarrasin est naturellement sans gluten ; demandez-nous pour les détails de préparation.' ),
					array( 'q' => 'Faites-vous à emporter ?', 'a' => 'Oui, galettes et crêpes à emporter, commande sur place ou par téléphone.' ),
					array( 'q' => 'Avez-vous des options végétariennes ?', 'a' => 'Bien sûr, de nombreuses garnitures végétariennes salées et sucrées.' ),
				),
				'stats' => array(
					array( 'value' => '30+',    'label' => 'Garnitures' ),
					array( 'value' => '4,8/5',  'label' => 'Avis Google' ),
					array( 'value' => '100%',   'label' => 'Fait maison' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Une crêperie comme en Bretagne, où galettes et crêpes sont faites minute sur le billig, à partir de farines de qualité.\n\nGalettes de sarrasin garnies, crêpes sucrées gourmandes, caramel au beurre salé maison, et un bon cidre pour accompagner. Ambiance chaleureuse et prix doux.\n\nLe rendez-vous gourmand idéal en famille, entre amis ou pour une pause sucrée. Réservation conseillée le week-end.",
					'prestations'        => "Galettes de sarrasin (complète, du moment, créations), crêpes sucrées (caramel beurre salé maison, chocolat, fruits), salades, bolées de cidre, jus et boissons chaudes.\n\nSur place dans une ambiance conviviale, ou à emporter. Formules midi, menus enfants, terrasse aux beaux jours.\n\nDes produits simples et bons, faits maison, à des prix accessibles.",
					'zones-intervention' => "Ouvert du mardi au dimanche, midi et soir. Terrasse l'été. Réservation conseillée le week-end et pour les groupes.\n\nAu cœur du quartier, accueil familial, idéal avec les enfants. À emporter possible.\n\nGroupes et anniversaires : contactez-nous pour réserver et composer un menu adapté.",
					'realisations'       => "La crêperie préférée des familles du quartier : note 4,8/5 et des habitués qui reviennent chaque semaine.\n\nNos stars : la galette complète, la crêpe caramel beurre salé maison et les créations du moment. Suivez-nous pour les nouveautés.\n\nUne petite faim ? Réservez ou passez : ça sent bon le sarrasin et le caramel.",
				),
			),

			'burger' => array(
				'icon'  => '🍔',
				'label' => 'Burger / Street food',
				'desc'  => 'Burgers maison, frites fraîches, ambiance décontractée. Pour burger, food truck, street food.',
				'mods'  => array(
					// Burger : fast-food rouge + jaune (style McDo), fond blanc.
					'ag_color_accent'          => '#DA291C',
					'ag_color_accent2'         => '#FFC72C',
					'ag_color_background'      => '#fffdf5',
					'ag_color_panel'           => '#ffffff',
					'ag_color_border'          => '#efe7d2',
					'ag_color_text'            => '#2a2622',
					'ag_color_heading'         => '#c8102e',
					'ag_color_muted'           => '#6b655c',
					'ag_hero_prefix'            => 'Bienvenue chez',
					'ag_hero_brand'             => 'Le Burger',
					'ag_hero_subtitle'          => 'Burgers maison, viande fraîche, frites coupées maison, pain artisanal. Sur place, à emporter ou en livraison.',
					'ag_hero_button'            => 'Commander',
					'ag_hero_button_url'        => '/carte/',
					'ag_restaurant_metier_nom'  => 'Burger / Street food',
					'ag_restaurant_hero_image'  => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=1600&q=80',
					'ag_restaurant_testi_1'     => 'Le meilleur burger de la ville, viande juteuse, frites maison parfaites. Énorme !|Dylan G.|Lille',
					'ag_restaurant_testi_2'     => 'Pain artisanal, sauces maison, ça change tout. Livraison rapide en plus. Top.|Sarah B.|Lyon',
					'ag_restaurant_testi_3'     => 'Ambiance cool, portions généreuses, prix justes. Mon spot du vendredi soir.|Kevin M.|Paris',
				),
				'services' => array(
					array( 'emoji' => '🍔', 'title' => 'Burgers maison' ),
					array( 'emoji' => '🍟', 'title' => 'Frites fraîches' ),
					array( 'emoji' => '🥤', 'title' => 'Milkshakes' ),
					array( 'emoji' => '🛵', 'title' => 'Livraison' ),
					array( 'emoji' => '🥡', 'title' => 'À emporter' ),
					array( 'emoji' => '🌱', 'title' => 'Option veggie' ),
					array( 'emoji' => '🧒', 'title' => 'Menu enfant' ),
					array( 'emoji' => '🎉', 'title' => 'Privatisation' ),
				),
				'how' => array(
					array( 'emoji' => '📱', 'title' => 'Commandez', 'desc' => 'Sur place, à emporter ou en livraison : en ligne ou au comptoir, en 1 minute.' ),
					array( 'emoji' => '🔥', 'title' => 'On grille minute', 'desc' => 'Viande fraîche, pain artisanal, sauces maison, frites coupées sur place.' ),
					array( 'emoji' => '😋', 'title' => 'Dégustez', 'desc' => 'Chaud, généreux, fait minute : sur place dans une ambiance cool ou chez vous.' ),
				),
				'faq' => array(
					array( 'q' => 'Livrez-vous ?', 'a' => 'Oui, livraison dans un rayon autour du restaurant. Commande en ligne ou par téléphone.' ),
					array( 'q' => 'Avez-vous des options végétariennes ?', 'a' => 'Oui, plusieurs burgers veggie et un steak végétal maison.' ),
					array( 'q' => 'La viande est-elle fraîche ?', 'a' => 'Oui, viande fraîche de boucher, hachée maison, pain artisanal et frites coupées sur place.' ),
				),
				'stats' => array(
					array( 'value' => '300+',   'label' => 'Burgers / jour' ),
					array( 'value' => '4,8/5',  'label' => 'Avis Google' ),
					array( 'value' => '100%',   'label' => 'Fait maison' ),
				),
				'pages' => array(
					'qui-sommes-nous'    => "Du burger comme on l'aime : généreux, fait maison, avec des produits frais. Viande de boucher hachée maison, pain artisanal, frites fraîches coupées sur place, sauces maison.\n\nPas de surgelé, pas de chichi : du bon, du frais, du généreux, dans une ambiance décontractée. Options veggie et menu enfant pour régaler tout le monde.\n\nSur place, à emporter ou en livraison : le bon plan gourmand de votre quartier.",
					'prestations'        => "Burgers maison (classiques et créations), frites fraîches coupées sur place, milkshakes, desserts maison, boissons artisanales. Options végétariennes et menu enfant.\n\nService au comptoir, à emporter et livraison à domicile. Formules midi, menus groupes, privatisation pour vos événements.\n\nViande fraîche, pain artisanal, sauces maison : la street food version qualité.",
					'zones-intervention' => "Ouvert 7j/7 midi et soir. Livraison dans un rayon autour du restaurant, commande en ligne ou par téléphone. À emporter en quelques minutes.\n\nAmbiance décontractée, idéal entre amis ou en famille. Terrasse aux beaux jours.\n\nGroupes et événements : menus dédiés et privatisation possibles, contactez-nous.",
					'realisations'       => "Plus de 300 burgers servis chaque jour et une note de 4,8/5 : le fait maison, ça se goûte et ça se voit.\n\nNos best-sellers : le Classic, le Double cheese et les créations du mois. Suivez-nous pour les éditions limitées.\n\nUne faim de loup ? Commandez en ligne ou passez au comptoir, on s'occupe du reste.",
				),
			),

		);
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 22 );
		add_action( 'admin_post_ag_restaurant_apply_preset', array( __CLASS__, 'handle_apply' ) );
		// Rend la home immersive des l'activation : applique le preset par
		// defaut « traditionnel » si aucun type de restaurant n'est encore choisi.
		add_action( 'after_setup_theme', array( __CLASS__, 'maybe_apply_default' ) );
		// Le client edite lui-meme les 8 specialites (emoji, titre, lien).
		add_action( 'customize_register', array( __CLASS__, 'customize_services' ) );
	}

	/**
	 * Carte (menu) adaptée à chaque type de restaurant.
	 * Format : « ## Section » puis « Nom | Description | Prix » par ligne.
	 * @return array slug => texte de carte
	 */
	public static function default_cartes() {
		$trad = class_exists( 'AG_Restaurant_Carte' ) ? AG_Restaurant_Carte::default_menu() : '';
		return array(
			'traditionnel' => $trad,
			'pizzeria' => "## Pizzas\nMargherita | Tomate, mozzarella, basilic frais | 9 | https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=300&q=70\nReine | Tomate, mozzarella, jambon, champignons | 11 | https://images.unsplash.com/photo-1513104890138-7c749659a591?w=300&q=70\nDiavola | Tomate, mozzarella, salami piquant | 12 | https://images.unsplash.com/photo-1628840042765-356cda07504e?w=300&q=70\n4 Fromages | Mozzarella, gorgonzola, parmesan, chèvre | 12 | https://images.unsplash.com/photo-1593504049359-74330189a345?w=300&q=70\nCalzone | Chausson tomate, mozzarella, jambon | 12 | https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=300&q=70\n\n## Pâtes\nSpaghetti bolognese | Sauce bolognaise maison | 11 | https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=300&q=70\nTagliatelle carbonara | Crème, lardons, parmesan | 12 | https://images.unsplash.com/photo-1612874742237-6526221588e3?w=300&q=70\nLasagnes maison | Bœuf, béchamel, gratinées | 13 | https://images.unsplash.com/photo-1619895092538-128341789043?w=300&q=70\n\n## Antipasti\nBruschetta | Pain grillé, tomate, basilic | 6 | https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=300&q=70\nBurrata | Crémeuse, huile d'olive, roquette | 9 | https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=300&q=70\n\n## Desserts\nTiramisu maison | | 6 | https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=300&q=70\nPanna cotta | Coulis de fruits rouges | 6 | https://images.unsplash.com/photo-1488477181946-6428a0291777?w=300&q=70\n\n## Boissons\nSoda 33cl | | 3 | https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=300&q=70\nVin rouge (au verre) | | 4 | https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=300&q=70",
			'burger' => "## Burgers\nLe Classic | Steak, cheddar, salade, tomate, oignon | 9 | https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=70\nLe Bacon | Double steak, bacon, cheddar | 12 | https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=300&q=70\nLe Chicken | Poulet pané croustillant, sauce maison | 10 | https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=300&q=70\nLe Veggie | Galette de légumes, cheddar | 9 | https://images.unsplash.com/photo-1525059696034-4967a8e1dca2?w=300&q=70\n\n## Frites & accompagnements\nFrites maison | | 3 | https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=300&q=70\nFrites cheddar-bacon | | 5 | https://images.unsplash.com/photo-1630384060421-cb20d0e0649d?w=300&q=70\nOnion rings | | 4 | https://images.unsplash.com/photo-1639024471283-03518883512d?w=300&q=70\nNuggets (x6) | | 5 | https://images.unsplash.com/photo-1562967914-608f82629710?w=300&q=70\n\n## Menus\nMenu Classic | Burger + frites + boisson | 13 | https://images.unsplash.com/photo-1550547660-d9450f859349?w=300&q=70\nMenu Bacon | Burger Bacon + frites + boisson | 15 | https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=300&q=70\n\n## Desserts\nBrownie | | 4 | https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=300&q=70\nMilkshake | Vanille, fraise ou chocolat | 5 | https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=300&q=70\n\n## Boissons\nSoda 33cl | | 2.5 | https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=300&q=70\nEau | | 2 | https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&q=70",
			'bistrot' => "## Entrées\nŒuf mayo | | 5 | https://images.unsplash.com/photo-1482049016688-2d3e1b311543?w=300&q=70\nTerrine maison | Cornichons, pain grillé | 7 | https://images.unsplash.com/photo-1542528180-a1208c5169a5?w=300&q=70\nSoupe à l'oignon gratinée | | 7 | https://images.unsplash.com/photo-1547592180-85f173990554?w=300&q=70\n\n## Plats du jour\nSteak frites | Sauce au poivre | 15 | https://images.unsplash.com/photo-1546964124-0cce460f38ef?w=300&q=70\nConfit de canard | Pommes sarladaises | 17 | https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=300&q=70\nBlanquette de veau | Riz pilaf | 16 | https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=300&q=70\n\n## Planches à partager\nCharcuterie | Sélection du marché | 14 | https://images.unsplash.com/photo-1528607929212-2636ec44253e?w=300&q=70\nFromages affinés | | 12 | https://images.unsplash.com/photo-1452195100486-9cc805987862?w=300&q=70\n\n## Desserts\nCrème brûlée | | 7 | https://images.unsplash.com/photo-1470124182917-cc6e71b22ecc?w=300&q=70\nMousse au chocolat | | 6 | https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=300&q=70",
			'gastronomique' => "## Entrées\nFoie gras maison | Chutney de figues, brioche | 18 | https://images.unsplash.com/photo-1623595119708-26b1f7500ddd?w=300&q=70\nSaint-Jacques snackées | Purée de céleri, truffe | 22 | https://images.unsplash.com/photo-1559847844-d721426d6edc?w=300&q=70\nVelouté de saison | Émulsion, croûtons | 14 | https://images.unsplash.com/photo-1547592166-23ac45744acd?w=300&q=70\n\n## Plats\nFilet de bœuf | Sauce périgueux, légumes glacés | 32 | https://images.unsplash.com/photo-1558030006-450675393462?w=300&q=70\nRis de veau | Jus corsé, pomme fondante | 30 | https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=300&q=70\nHomard rôti | Beurre blanc, herbes fraîches | 38 | https://images.unsplash.com/photo-1559737558-2f5a35f4523b?w=300&q=70\n\n## Desserts\nSoufflé au chocolat | | 14 | https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=300&q=70\nAssiette de fromages affinés | | 16 | https://images.unsplash.com/photo-1452195100486-9cc805987862?w=300&q=70\n\n## Menu dégustation\nMenu en 5 services | Accord mets-vins en option | 89 | https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=300&q=70",
			'creperie' => "## Galettes salées\nComplète | Jambon, œuf, fromage | 9 | https://images.unsplash.com/photo-1565299543923-37dd37887442?w=300&q=70\nChèvre-miel | Chèvre, miel, noix | 9 | https://images.unsplash.com/photo-1559561853-08451507cbdf?w=300&q=70\nForestière | Champignons, crème, lardons | 10 | https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=300&q=70\nSaumon | Saumon fumé, crème citronnée | 12 | https://images.unsplash.com/photo-1485921325833-c519f76c4927?w=300&q=70\n\n## Crêpes sucrées\nBeurre-sucre | | 4 | https://images.unsplash.com/photo-1519676867240-f03562e64548?w=300&q=70\nCaramel beurre salé | | 6 | https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=300&q=70\nChocolat-banane | | 7 | https://images.unsplash.com/photo-1607920591413-4ec007e70023?w=300&q=70\nPomme caramélisée | Chantilly | 7 | https://images.unsplash.com/photo-1551024601-bec78aea704b?w=300&q=70\n\n## Boissons\nBolée de cidre | | 4 | https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=300&q=70\nJus de pomme artisanal | | 3 | https://images.unsplash.com/photo-1576673442511-7e39b6545c87?w=300&q=70",
		);
	}

	/**
	 * Signature d'une carte = sections + couples « plat=prix » (ignore la
	 * description, la photo et les espaces). Permet de reconnaître une carte
	 * issue d'un preset (même version d'avant les photos) sans confondre avec
	 * une carte réellement personnalisée par le client.
	 */
	public static function carte_signature( $raw ) {
		$sig = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) continue;
			if ( '##' === substr( $line, 0, 2 ) ) { $sig[] = '#' . trim( substr( $line, 2 ) ); continue; }
			$p = array_map( 'trim', explode( '|', $line ) );
			$name = isset( $p[0] ) ? $p[0] : '';
			if ( '' === $name ) continue;
			$sig[] = $name . '=' . ( isset( $p[2] ) ? $p[2] : '' );
		}
		return implode( '|', $sig );
	}

	/**
	 * Transforme une liste de services en texte editable (1 ligne par carte) :
	 * « emoji | Titre | lien (optionnel) ».
	 */
	public static function services_to_text( $services ) {
		$lines = array();
		foreach ( (array) $services as $svc ) {
			$emoji = isset( $svc['emoji'] ) ? $svc['emoji'] : '';
			$title = isset( $svc['title'] ) ? $svc['title'] : '';
			$url   = isset( $svc['url'] ) ? $svc['url'] : '';
			$line  = trim( $emoji . ' | ' . $title );
			if ( '' !== $url ) $line .= ' | ' . $url;
			$lines[] = $line;
		}
		return implode( "\n", $lines );
	}

	/** Section Personnaliser : édition des spécialités de l'accueil. */
	public static function customize_services( $wp ) {
		$wp->add_section( 'ag_restaurant_services', array(
			'title'       => '🍽️ ' . __( 'Spécialités (accueil)', 'ag-starter-restaurant' ),
			'priority'    => 26,
			'description' => __( 'Une carte par ligne : emoji | Titre | lien (le lien est facultatif). Sans lien, la carte mène automatiquement à la bonne page (réservation, carte, commande). Le lien peut être une adresse complète (https://…) ou interne (/carte/).', 'ag-starter-restaurant' ),
		) );
		$wp->add_setting( 'ag_restaurant_services_edit', array(
			'default'           => '',
			'sanitize_callback' => array( __CLASS__, 'sanitize_services_edit' ),
		) );
		$wp->add_control( 'ag_restaurant_services_edit', array(
			'label'       => __( 'Vos spécialités (8 max)', 'ag-starter-restaurant' ),
			'section'     => 'ag_restaurant_services',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 9 ),
		) );

		// ── Chiffres clés (hero) — remplace les stats du preset ──
		$wp->add_section( 'ag_restaurant_stats', array(
			'title'       => '📊 ' . __( 'Chiffres clés (accueil)', 'ag-starter-restaurant' ),
			'priority'    => 27,
			'description' => __( 'Une statistique par ligne : valeur | libellé (ex : 120 | Couverts par jour). Laissez vide pour garder les chiffres du preset choisi. Effacez le contenu d\'une ligne pour retirer la stat.', 'ag-starter-restaurant' ),
		) );
		$wp->add_setting( 'ag_restaurant_stats_edit', array(
			'default'           => '',
			'sanitize_callback' => array( __CLASS__, 'sanitize_services_edit' ),
		) );
		$wp->add_control( 'ag_restaurant_stats_edit', array(
			'label'       => __( 'Vos chiffres clés (3 recommandés)', 'ag-starter-restaurant' ),
			'section'     => 'ag_restaurant_stats',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 4 ),
		) );

		// ── Témoignages — éditables (étaient liés au preset) ──
		$wp->add_section( 'ag_restaurant_testi', array(
			'title'       => '💬 ' . __( 'Témoignages (accueil)', 'ag-starter-restaurant' ),
			'priority'    => 28,
			'description' => __( 'Trois avis clients. Format : texte | nom | ville. Videz un champ pour retirer le témoignage. N\'inventez pas de faux avis : mettez de vrais retours clients.', 'ag-starter-restaurant' ),
		) );
		$wp->add_setting( 'ag_restaurant_testi_title', array(
			'default'           => 'Ils nous font confiance',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp->add_control( 'ag_restaurant_testi_title', array(
			'label'   => __( 'Titre de la section', 'ag-starter-restaurant' ),
			'section' => 'ag_restaurant_testi',
			'type'    => 'text',
		) );
		$wp->add_setting( 'ag_restaurant_testi_subtitle', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp->add_control( 'ag_restaurant_testi_subtitle', array(
			'label'       => __( 'Sous-titre (laisser vide = aucun)', 'ag-starter-restaurant' ),
			'description' => __( 'N\'affichez un nombre d\'avis que s\'il est réel. Ex : « Les retours de nos clients ».', 'ag-starter-restaurant' ),
			'section'     => 'ag_restaurant_testi',
			'type'        => 'text',
		) );
		for ( $i = 1; $i <= 3; $i++ ) {
			$wp->add_setting( 'ag_restaurant_testi_' . $i, array(
				'default'           => '',
				'sanitize_callback' => array( __CLASS__, 'sanitize_services_edit' ),
			) );
			$wp->add_control( 'ag_restaurant_testi_' . $i, array(
				'label'       => sprintf( __( 'Témoignage %d (texte | nom | ville)', 'ag-starter-restaurant' ), $i ),
				'section'     => 'ag_restaurant_testi',
				'type'        => 'textarea',
				'input_attrs' => array( 'rows' => 2 ),
			) );
		}

		// ── FAQ — remplace la FAQ du preset ──
		$wp->add_section( 'ag_restaurant_faq', array(
			'title'       => '❓ ' . __( 'FAQ (accueil)', 'ag-starter-restaurant' ),
			'priority'    => 29,
			'description' => __( 'Une question par ligne : Question | Réponse. Laissez vide pour garder la FAQ du preset choisi.', 'ag-starter-restaurant' ),
		) );
		$wp->add_setting( 'ag_restaurant_faq_edit', array(
			'default'           => '',
			'sanitize_callback' => array( __CLASS__, 'sanitize_services_edit' ),
		) );
		$wp->add_control( 'ag_restaurant_faq_edit', array(
			'label'       => __( 'Votre FAQ', 'ag-starter-restaurant' ),
			'section'     => 'ag_restaurant_faq',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 6 ),
		) );
	}

	public static function sanitize_services_edit( $v ) {
		return wp_kses_post( $v );
	}

	/**
	 * Applique une fois le preset par defaut (hero photo + grille de
	 * specialites) si l'utilisateur n'a pas encore choisi de type de
	 * restaurant. N'ecrase pas les pages WP, seulement les reglages d'aspect.
	 */
	public static function maybe_apply_default() {
		// Normalisation couleurs (une fois) : palette sombre & or « Versailles »
		// (fond sombre, texte creme, accent dore) — corrige tout reglage herite.
		if ( ! get_option( 'ag_restaurant_versailles_v2' ) ) {
			$colors = array(
				'ag_color_accent'     => '#e3bb4f',
				'ag_color_background' => '#13110c',
				'ag_color_panel'      => '#1c1813',
				'ag_color_border'     => '#3a2f1e',
				'ag_color_text'       => '#f4eedd',
				'ag_color_heading'    => '#fbf5e6',
				'ag_color_muted'      => '#d4c8a6',
			);
			foreach ( $colors as $k => $v ) {
				set_theme_mod( $k, $v );
			}
			update_option( 'ag_restaurant_versailles_v2', 1 );
		}

		// Re-synchronise la PALETTE du preset actif quand le template est mis à
		// jour : sinon, après une mise à jour, les couleurs restent celles
		// d'avant (le client n'a pas à ré-appliquer le preset à la main).
		$active_slug = get_theme_mod( 'ag_restaurant_metier_slug', '' );
		if ( $active_slug && (int) get_option( 'ag_restaurant_palette_v', 0 ) < 3 ) {
			$presets = self::get_presets();
			if ( ! empty( $presets[ $active_slug ]['mods'] ) ) {
				foreach ( $presets[ $active_slug ]['mods'] as $k => $v ) {
					if ( 0 === strpos( $k, 'ag_color_' ) ) {
						set_theme_mod( $k, $v );
					}
				}
			}
			update_option( 'ag_restaurant_palette_v', 3 );
		}

		// Carte (menu) adaptee au type, AVEC photos. On l'applique pour le preset
		// actif SANS ecraser une carte personnalisee. On reconnait une carte
		// issue d'un preset (meme l'ancienne version sans photos) via sa
		// signature plats=prix, pour la rafraichir avec les images.
		if ( $active_slug && (int) get_option( 'ag_restaurant_carte_v', 0 ) < 2 ) {
			$cartes  = self::default_cartes();
			if ( ! empty( $cartes[ $active_slug ] ) ) {
				$default = class_exists( 'AG_Restaurant_Carte' ) ? AG_Restaurant_Carte::default_menu() : '';
				$current = (string) get_theme_mod( 'ag_restaurant_carte_menu', '' );
				$safe = ( '' === trim( $current ) )
					|| ( $current === $default )
					|| ( self::carte_signature( $current ) === self::carte_signature( $cartes[ $active_slug ] ) );
				if ( $safe ) {
					set_theme_mod( 'ag_restaurant_carte_menu', $cartes[ $active_slug ] );
				}
			}
			update_option( 'ag_restaurant_carte_v', 2 );
		}

		// Le bouton hero ouvre désormais la carte (= pop-up de choix : réserver /
		// livraison / à emporter / consulter). On bascule l'ancien lien direct
		// vers /reservation/ sans toucher à une URL personnalisée par le client.
		if ( ! get_option( 'ag_restaurant_herobtn_v' ) ) {
			if ( '/reservation/' === get_theme_mod( 'ag_hero_button_url', '' ) ) {
				set_theme_mod( 'ag_hero_button_url', '/carte/' );
			}
			update_option( 'ag_restaurant_herobtn_v', 1 );
		}

		// Migration douce : pré-remplit le champ éditable à partir des services
		// déjà en place, pour que le client voie et modifie ses 8 spécialités
		// (emoji/titre/lien) même sur une install existante.
		if ( '' === trim( (string) get_theme_mod( 'ag_restaurant_services_edit', '' ) ) ) {
			$cur = get_theme_mod( 'ag_restaurant_services_json', '' );
			$arr = $cur ? json_decode( $cur, true ) : array();
			if ( is_array( $arr ) && ! empty( $arr ) ) {
				set_theme_mod( 'ag_restaurant_services_edit', self::services_to_text( $arr ) );
			}
		}

		if ( get_theme_mod( 'ag_restaurant_metier_slug', '' ) ) return;
		if ( get_option( 'ag_restaurant_default_applied' ) ) return;

		$presets = self::get_presets();
		if ( empty( $presets['traditionnel'] ) ) return;
		$preset = $presets['traditionnel'];

		foreach ( $preset['mods'] as $key => $val ) {
			set_theme_mod( $key, $val );
		}
		set_theme_mod( 'ag_restaurant_metier_slug', 'traditionnel' );
		set_theme_mod( 'ag_restaurant_services_json', wp_json_encode( $preset['services'] ) );
		if ( '' === trim( (string) get_theme_mod( 'ag_restaurant_services_edit', '' ) ) ) {
			set_theme_mod( 'ag_restaurant_services_edit', self::services_to_text( $preset['services'] ) );
		}
		update_option( 'ag_restaurant_default_applied', 1 );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Type de restaurant', 'ag-starter-restaurant' ),
			'🎯 ' . __( 'Type de restaurant', 'ag-starter-restaurant' ),
			'manage_options',
			'ag-restaurant-presets',
			array( __CLASS__, 'render' )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$applied = isset( $_GET['applied'] ) ? sanitize_key( $_GET['applied'] ) : '';
		$current = get_theme_mod( 'ag_restaurant_metier_slug', '' );
		$presets = self::get_presets();
		?>
		<div class="wrap">
			<h1>🎯 <?php esc_html_e( 'Type de restaurant — AG Starter Restaurant', 'ag-starter-restaurant' ); ?></h1>
			<p style="font-size:1.05em;color:#50575e;max-width:820px;"><?php esc_html_e( 'Choisissez votre type de restaurant ci-dessous. En 1 clic, le thème adapte le hero, la couleur d\'accent et la grille des 8 services affichée sur la page d\'accueil. Vous pourrez ensuite affiner chaque texte dans Apparence > Personnaliser.', 'ag-starter-restaurant' ); ?></p>

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
						<input type="hidden" name="action" value="ag_restaurant_apply_preset" />
						<input type="hidden" name="preset" value="<?php echo esc_attr( $slug ); ?>" />
						<?php wp_nonce_field( 'ag_restaurant_apply_preset' ); ?>
						<button type="submit" class="button button-primary button-large" style="width:100%;background:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;border-color:<?php echo esc_attr( $preset['mods']['ag_color_accent'] ); ?>;color:#fff;font-weight:700;">
							<?php echo $is_current ? '🔄 Réappliquer' : '✨ Appliquer ce preset'; ?>
						</button>
					</form>
				</div>
				<?php endforeach; ?>
			</div>

			<p style="margin-top:32px;color:#777;font-size:.9em;">
				💡 <?php esc_html_e( 'Après application, fine-tunez via', 'ag-starter-restaurant' ); ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Apparence > Personnaliser', 'ag-starter-restaurant' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-restaurant-reset' ) ); ?>">🔄 <?php esc_html_e( 'Réinitialiser le thème', 'ag-starter-restaurant' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function handle_apply() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_restaurant_apply_preset' );
		$slug    = isset( $_POST['preset'] ) ? sanitize_key( $_POST['preset'] ) : '';
		$presets = self::get_presets();
		if ( ! isset( $presets[ $slug ] ) ) {
			wp_safe_redirect( admin_url( 'themes.php?page=ag-restaurant-presets' ) );
			exit;
		}
		$preset = $presets[ $slug ];

		// 1. Applique les theme_mods
		foreach ( $preset['mods'] as $key => $val ) {
			set_theme_mod( $key, $val );
		}

		// 2. Stocke le slug actif (pour affichage badge "actuellement applique")
		set_theme_mod( 'ag_restaurant_metier_slug', $slug );

		// 3. Stocke la liste des services en JSON (relu par front-page.php)
		set_theme_mod( 'ag_restaurant_services_json', wp_json_encode( $preset['services'] ) );
		// 3b. Renseigne le champ editable du client avec ces services (il pourra
		//     ensuite modifier emoji/titre/lien dans Personnaliser > Specialites).
		set_theme_mod( 'ag_restaurant_services_edit', self::services_to_text( $preset['services'] ) );

		// 3c. Carte (menu) adaptee au type de restaurant.
		$cartes = self::default_cartes();
		if ( ! empty( $cartes[ $slug ] ) ) {
			set_theme_mod( 'ag_restaurant_carte_menu', $cartes[ $slug ] );
		}

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

		wp_safe_redirect( admin_url( 'themes.php?page=ag-restaurant-presets&applied=' . $slug ) );
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
		// 1. Priorité à l'édition manuelle du client (Personnaliser > Spécialités).
		//    Format par ligne : « emoji | Titre | lien (optionnel) ».
		$edit = (string) get_theme_mod( 'ag_restaurant_services_edit', '' );
		if ( '' !== trim( $edit ) ) {
			$out = array();
			foreach ( preg_split( '/\r\n|\r|\n/', $edit ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) continue;
				$parts = array_map( 'trim', explode( '|', $line ) );
				$emoji = isset( $parts[0] ) ? $parts[0] : '';
				$title = isset( $parts[1] ) ? $parts[1] : '';
				$url   = isset( $parts[2] ) ? $parts[2] : '';
				if ( '' === $title && '' === $emoji ) continue;
				$out[] = array( 'emoji' => $emoji, 'title' => $title, 'url' => $url );
			}
			if ( ! empty( $out ) ) return $out;
		}
		// 2. Sinon, la grille du preset (JSON).
		$json = get_theme_mod( 'ag_restaurant_services_json', '' );
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
		$slug = get_theme_mod( 'ag_restaurant_metier_slug', '' );
		if ( ! $slug ) return null;
		$presets = self::get_presets();
		return isset( $presets[ $slug ] ) ? $presets[ $slug ] : null;
	}

	/** @return array [['value', 'label'], ...] */
	public static function get_active_stats() {
		// 1. Priorité à l'édition manuelle (Personnaliser > Chiffres clés).
		//    Format par ligne : « valeur | libellé ».
		$edit = (string) get_theme_mod( 'ag_restaurant_stats_edit', '' );
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
			return $out; // peut être vide volontairement (= masquer les stats)
		}
		// 2. Sinon, les stats du preset.
		$p = self::get_active_preset();
		return ( $p && isset( $p['stats'] ) ) ? $p['stats'] : array();
	}

	/** @return array [['emoji', 'title', 'desc'], ...] */
	public static function get_active_how() {
		$p = self::get_active_preset();
		return ( $p && isset( $p['how'] ) ) ? $p['how'] : array();
	}

	/** @return array [['q', 'a'], ...] */
	public static function get_active_faq() {
		// 1. Priorité à l'édition manuelle (Personnaliser > FAQ).
		//    Format par ligne : « Question | Réponse ».
		$edit = (string) get_theme_mod( 'ag_restaurant_faq_edit', '' );
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
		// 2. Sinon, la FAQ du preset.
		$p = self::get_active_preset();
		return ( $p && isset( $p['faq'] ) ) ? $p['faq'] : array();
	}

	/**
	 * Recupere un temoignage stocke en theme_mod (format "texte|nom|ville").
	 * @return array|null ['text', 'name', 'city']
	 */
	public static function get_testimonial( $i ) {
		$raw = get_theme_mod( 'ag_restaurant_testi_' . (int) $i, '' );
		if ( ! $raw ) return null;
		$parts = array_map( 'trim', explode( '|', $raw ) );
		return array(
			'text' => isset( $parts[0] ) ? $parts[0] : '',
			'name' => isset( $parts[1] ) ? $parts[1] : '',
			'city' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
}
AG_Restaurant_Presets::init();

/**
 * Quand un preset est applique, on ajoute la classe body 'ag-premium-mode'
 * pour permettre au CSS d'overrider le theme sombre par defaut et passer
 * en palette claire style meilleur-restaurant.com.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( class_exists( 'AG_Restaurant_Presets' ) && AG_Restaurant_Presets::get_active_preset() ) {
		$classes[] = 'ag-premium-mode';
		$slug = get_theme_mod( 'ag_restaurant_metier_slug', '' );
		if ( $slug ) {
			$classes[] = 'ag-metier-' . sanitize_html_class( $slug );
		}
	}
	return $classes;
} );
