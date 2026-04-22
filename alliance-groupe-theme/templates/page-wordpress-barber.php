<?php
/**
 * Template Name: WordPress — Barber Shop
 *
 * Dedicated landing page for the AG Starter Barber theme.
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'barber',
    'slug_full'   => 'ag-starter-barber',
    'icon'        => '💈',
    'name'        => 'Barber Shop',
    'audience_short' => 'barbershop',
    'palette'     => 'Or &amp; noir',

    'hero_title'    => 'Le thème WordPress pour les <em>barber shops</em>',
    'hero_subtitle' => 'Design sombre premium avec système de file d\'attente QR code intégré. Vos clients scannent, prennent un ticket, et reviennent quand c\'est leur tour. Fini la queue debout.',

    'description_long' => 'AG Starter Barber est le premier thème WordPress gratuit avec un système de file d\'attente par QR code intégré. Conçu pour les barbershops et salons de coiffure urbains qui font des coupes à prix mini en centre-ville. Le client scanne le QR en vitrine, choisit sa prestation, reçoit un ticket avec l\'heure estimée de passage. Le barber gère la file depuis son tableau de bord WordPress. Temps moyen par coupe configurable (10-15 min), nombre de barbers ajustable.',

    'free_features' => array(
        '<strong>Système de file d\'attente QR code</strong> — le client scanne, prend un ticket, reçoit une heure de passage',
        '<strong>Calcul automatique</strong> du temps d\'attente (nb clients × durée moyenne ÷ nb barbers)',
        'Tableau de bord admin — gestion de la file en temps réel (commencer / terminé / retirer)',
        'QR code téléchargeable en PNG et SVG pour impression en vitrine',
        '<strong>5 prestations configurables</strong> (coupe 10€, barbe 5€, dégradé 15€, etc.)',
        'Page d\'accueil complète : hero, tarifs, file d\'attente live, "comment ça marche"',
        'Design sombre premium responsive, palette or & noir',
        'Compatible AG Starter Companion (import demo 1 clic)',
    ),

    'pro_features' => array(
        '<strong>Notifications SMS</strong> — le client reçoit un SMS quand c\'est bientôt son tour',
        '<strong>Statistiques avancées</strong> — nb coupes/jour, CA quotidien, temps d\'attente moyen, barber le plus rapide',
        '<strong>Multi-barbers</strong> — affectation automatique du client au prochain barber disponible',
        'Animations scroll (fade-in, slide)',
        'Sticky header avec numéro de téléphone',
        'Couleur d\'accent secondaire personnalisable',
        'Footer personnalisable (couleur + texte)',
        'Support email 60 jours',
    ),

    'premium_features' => array(
        '<strong>Tout Pro inclus</strong>',
        '<strong>Réservation en ligne</strong> — le client peut réserver un créneau fixe (pas que walk-in)',
        '<strong>Galerie Instagram intégrée</strong> — vos meilleures coupes affichées automatiquement',
        '<strong>Boutique WooCommerce</strong> — vendez vos produits capillaires (cire, huile barbe, tondeuse)',
        '<strong>Programme fidélité</strong> — 10ème coupe offerte, points de fidélité',
        'Multilingue 6 langues (FR, EN, ES, AR, IT, DE)',
        'Section avis clients Google intégrée',
        'Support prioritaire 12 mois',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>White-label complet</strong> — aucun crédit Alliance Groupe',
        '<strong>Multi-salons</strong> — gérez plusieurs adresses depuis un seul WordPress',
        '<strong>Écran TV salon</strong> — page dédiée pour afficher la file sur un écran en salon',
        '<strong>API publique</strong> — intégrez la file d\'attente dans votre propre app mobile',
        'Template de flyer / carte de visite à imprimer',
        '<strong>Session stratégique 30 min</strong> avec un expert Alliance Groupe',
        'Support 2h ouvrées + appel Fabrizio',
    ),
) );

get_template_part( 'template-parts/metier-page' );
get_footer();
