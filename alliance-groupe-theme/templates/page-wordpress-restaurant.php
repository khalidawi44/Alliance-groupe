<?php
/**
 * Template Name: Template WordPress — Restaurant
 *
 * Dedicated landing page for the AG Starter Restaurant theme.
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'restaurant',
    'slug_full'   => 'ag-starter-restaurant',
    'icon'        => '🍽️',
    'name'        => 'Restaurant',
    'audience_short' => 'restaurant',
    'palette'     => 'Or &amp; noir',

    'hero_title'    => 'Le thème WordPress pour les <em>restaurants</em>',
    'hero_subtitle' => 'Design sombre or & noir inspiré des restaurants gastronomiques. 100% français, hero, carte, réservation, horaires — tout est déjà en place. Juste à remplacer le nom et les photos.',

    'description_long' => 'AG Starter Restaurant est un thème WordPress gratuit pour bistrots, bars, cafés et restaurants gastronomiques francophones. La page d\'accueil arrive pré-remplie avec tout ce qu\'un client attend : sections carte, réservation, privatisation, histoire du restaurant et horaires d\'ouverture. Juste à remplacer les textes entre crochets.',

    'unique_features' => array(
        array(
            'icon'  => '🛵',
            'title' => 'Commande en ligne type Uber Eats — incluse',
            'desc'  => 'Vos clients commandent directement sur votre site : carte avec bouton « Ajouter », panier, livraison ou à emporter, validation. Vous recevez la commande par email + notification. Zéro commission, contrairement aux plateformes.',
            'vs'    => 'Les templates concurrents renvoient vers Uber Eats (30% de commission). Ici, c\'est chez vous, sans intermédiaire.',
        ),
        array(
            'icon'  => '🎁',
            'title' => 'Carte de fidélité digitale — incluse',
            'desc'  => 'Programme à tampons ou à points, carte digitale pour le client, crédit automatique à chaque commande, récompense paramétrable. Vous fidélisez sans appli ni carton à tampons perdu.',
            'vs'    => 'Aucun template restaurant gratuit du marché n\'intègre un vrai système de fidélité.',
        ),
        array(
            'icon'  => '🍽️',
            'title' => 'Vraie carte / menu élégante & éditable',
            'desc'  => 'Une carte au design « Versailles » sombre et or, parfaitement lisible, que vous modifiez en texte simple (section + plat + prix), sans toucher au code.',
            'vs'    => 'Chez les autres, la carte est une image figée ou un PDF à re-télécharger à chaque changement de prix.',
        ),
        array(
            'icon'  => '🎚️',
            'title' => 'Presets en 1 clic + tout éditable',
            'desc'  => 'Bistrot, pizzeria, gastronomique, crêperie, burger… le thème s\'adapte en un clic (couleurs, spécialités, textes). Ensuite, le client change absolument tout lui-même depuis l\'admin.',
            'vs'    => 'Les templates concurrents imposent un seul style et exigent du HTML/CSS pour le moindre changement.',
        ),
        array(
            'icon'  => '📅',
            'title' => 'Réservation + écran de choix intelligent',
            'desc'  => 'À l\'arrivée sur la carte, le client choisit : réserver une table, se faire livrer, emporter, ou juste consulter (avec horaires). Formulaire de réservation intégré, sans plugin.',
            'vs'    => 'Parcours pensé pour convertir, là où les autres se contentent d\'afficher un menu.',
        ),
    ),

    'free_features' => array(
        '<strong>🛵 Commande en ligne type Uber Eats incluse</strong> — panier, livraison / à emporter, sans commission',
        '<strong>🎁 Carte de fidélité digitale</strong> (tampons ou points) avec crédit auto à la commande',
        '<strong>🍽️ Vraie carte / menu élégante</strong>, lisible et éditable en texte simple',
        '<strong>Réservation de table intégrée</strong> + écran de choix (réserver / livrer / emporter / consulter)',
        '<strong>Presets en 1 clic</strong> (bistrot, pizzeria, gastro, crêperie, burger) — puis tout éditable par vous',
        'Page d\'accueil complète pré-remplie, textes 100% français natifs',
        'Horaires structurés, adresse + téléphone cliquable + email configurables',
        'Palette or & noir élégante, responsive, compatible Gutenberg',
        'Aucun plugin requis, installation en 2 minutes — Translation-ready (GPL v2+)',
    ),

    'premium_features' => array(
        '<strong>Hero plein écran avec vidéo de fond</strong> (atmosphère du restaurant)',
        'Animations douces sur la section carte (fade-in plats)',
        '10 blocs Gutenberg culinaires (menu du jour, plat du chef, promo happy hour, galerie photos)',
        '<strong>Formulaire de réservation intégré</strong> — date, heure, nombre de couverts, demandes spéciales',
        'Sticky header avec téléphone cliquable (essentiel pour mobile)',
        'Polices Google Fonts (Playfair pour les titres, Manrope pour le texte)',
        'Support email 60 jours',
        'Documentation vidéo complète',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h)',
        '<strong>Connexion optionnelle Deliveroo / Uber Eats / TheFork</strong> — en plus de votre commande sans commission incluse',
        '<strong>Paiement en ligne (Stripe)</strong> branché sur la commande du restaurant',
        '<strong>Maintenance WordPress 1 an incluse</strong>',
        'Audit SEO local (positionnement Google Maps, avis)',
        'Support prioritaire absolu (réponse sous 2h)',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        'Carte de fidélité connectée à votre CRM (HubSpot, Brevo)',
        'Appel stratégique avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Un template, c\'est bien pour démarrer. Mais un restaurant qui veut vraiment remplir sa salle a besoin d\'un site conçu pour convertir : menu photo-réaliste, avis Google intégrés, stratégie SEO locale ("restaurant italien Nantes"), réservation optimisée mobile, et campagnes Facebook/Instagram ciblées. Notre équipe conçoit ça sur-mesure. Premier appel gratuit avec Fabrizio.',
) );

get_template_part( 'template-parts/metier-page' );

get_footer();
