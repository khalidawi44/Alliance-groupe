<?php
/**
 * Template Name: Template WordPress — Artisan
 *
 * Dedicated landing page for the AG Starter Artisan theme.
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'artisan',
    'slug_full'   => 'ag-starter-artisan',
    'icon'        => '🔨',
    'name'        => 'Artisan',
    'audience_short' => 'entreprise',
    'palette'     => 'Bronze &amp; noir',

    'hero_title'    => 'Le thème WordPress pour les <em>artisans du bâtiment</em>',
    'hero_subtitle' => 'Plombier, électricien, menuisier, maçon, chauffagiste, BTP. Design sombre bronze & noir, textes 100% français, prestations et zones d\'intervention déjà en place.',

    'description_long' => 'AG Starter Artisan est un thème WordPress gratuit pensé pour les artisans du bâtiment francophones. Il contient tout ce qu\'un particulier cherche avant d\'appeler un artisan : vos prestations, vos zones d\'intervention, vos horaires (avec mention urgences), et un moyen facile de demander un devis. Design sérieux et rassurant pour inspirer confiance.',

    'free_features' => array(
        '<strong>Page d\'accueil complète pré-remplie</strong> — hero "Demander un devis", 3 cartes (prestations, zones d\'intervention, réalisations), section "Qui sommes-nous"',
        'Textes 100% français adaptés au ton pro du BTP',
        '<strong>Horaires avec mention "Urgences 7/7"</strong> dans le footer',
        'Palette bronze & noir qui inspire sérieux et savoir-faire',
        'Adresse + téléphone cliquable + email configurables',
        '100% responsive (crucial : vos clients vous cherchent depuis leur smartphone)',
        'Compatible Gutenberg (blog pour articles conseils, commentaires)',
        'Aucun plugin requis, installation en 2 minutes',
    ),

    'pro_features' => array(
        '<strong>Formulaire de devis avancé</strong> — type de travaux dropdown, surface m², code postal, urgence, budget estimé',
        '<strong>Galerie avant/après</strong> des chantiers avec slider (utilise les images à la une)',
        'Animations sur la section réalisations',
        '10 blocs Gutenberg BTP (liste prestations avec icônes, témoignages clients, badges certifications, tarifs)',
        'Sticky header avec téléphone toujours cliquable (essentiel mobile)',
        'Polices Google Fonts premium',
        'Support email 60 jours',
        'Documentation vidéo complète',
    ),

    'premium_features' => array(
        '<strong>Tout Pro inclus</strong>',
        '<strong>Réservation d\'interventions en ligne</strong> (WooCommerce) — les clients peuvent prendre rdv et payer l\'acompte',
        '<strong>Planning Google Calendar intégré</strong> — synchro automatique avec votre agenda pro',
        'Multi-langue 6 langues (utile en zone touristique ou frontalière)',
        'Section "Garanties & certifications" (RGE, Qualibat, décennale) avec logos',
        'Calculateur de devis indicatif (par exemple : m² × tarif/m²)',
        'Support prioritaire 12 mois',
        'Mises à jour à vie + appel expert 30 min',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h)',
        '<strong>Audit SEO local</strong> pour dominer votre zone d\'intervention (ex : "plombier Nantes urgence")',
        '<strong>Maintenance WordPress 1 an incluse</strong>',
        'Rapport de performance trimestriel (nombre de devis reçus, positions Google)',
        'Support prioritaire absolu (réponse sous 2h ouvrées)',
        'White-label complet',
        'Intégration CRM (HubSpot, Pipedrive, Brevo) pour suivre vos prospects',
        'Appel stratégique avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Un template, c\'est un point de départ. Un vrai site d\'artisan qui cartonne a besoin d\'un SEO local agressif ("plombier 24/24 Paris 15"), de photos professionnelles de vos chantiers, d\'un système de devis bien pensé, et d\'une stratégie Google Ads rentable. Nos clients artisans génèrent +320% de devis en moyenne. Premier appel gratuit.',
) );

get_template_part( 'template-parts/metier-page' );

get_footer();
