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

    'free_features' => array(
        '<strong>Page d\'accueil complète pré-remplie</strong> — hero, 3 cartes (carte, réservation, privatisation), section histoire, footer',
        'Textes 100% français natifs, pas de Lorem ipsum à remplacer',
        '<strong>Horaires d\'ouverture structurés</strong> dans le footer (lun-ven, sam, dim)',
        'Adresse + téléphone cliquable + email — tout configurable',
        'Palette or & noir élégante, responsive',
        'Compatible Gutenberg (blog, commentaires, recherche)',
        'Aucun plugin requis, installation en 2 minutes',
        'Translation-ready (GPL v2+)',
    ),

    'pro_features' => array(
        '<strong>Hero plein écran avec vidéo de fond</strong> (atmosphère du restaurant)',
        'Animations douces sur la section carte (fade-in plats)',
        '10 blocs Gutenberg culinaires (menu du jour, plat du chef, promo happy hour, galerie photos)',
        '<strong>Formulaire de réservation intégré</strong> — date, heure, nombre de couverts, demandes spéciales',
        'Sticky header avec téléphone cliquable (essentiel pour mobile)',
        'Polices Google Fonts (Playfair pour les titres, Manrope pour le texte)',
        'Support email 60 jours',
        'Documentation vidéo complète',
    ),

    'premium_features' => array(
        '<strong>Tout Pro inclus</strong>',
        '<strong>Menu de réservation multilingue</strong> (FR, EN, ES, IT, DE, AR) — parfait pour les villes touristiques',
        '<strong>Module de commande en ligne WooCommerce</strong> — vendez vos plats à emporter',
        'Section "Menu du midi" automatique (change tous les jours)',
        'Intégration newsletter (inscription clients + envoi de la carte du jour)',
        'Support prioritaire 12 mois + appel expert 30 min',
        'Mises à jour à vie',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h)',
        '<strong>Intégration Deliveroo / Uber Eats / TheFork</strong>',
        '<strong>Maintenance WordPress 1 an incluse</strong>',
        'Audit SEO local (positionnement Google Maps, avis)',
        'Support prioritaire absolu (réponse sous 2h)',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        'Intégration CRM (HubSpot, Brevo) pour la fidélisation',
        'Appel stratégique avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Un template, c\'est bien pour démarrer. Mais un restaurant qui veut vraiment remplir sa salle a besoin d\'un site conçu pour convertir : menu photo-réaliste, avis Google intégrés, stratégie SEO locale ("restaurant italien Nantes"), réservation optimisée mobile, et campagnes Facebook/Instagram ciblées. Notre équipe conçoit ça sur-mesure. Premier appel gratuit avec Fabrizio.',
) );

get_template_part( 'template-parts/metier-page' );

get_footer();
