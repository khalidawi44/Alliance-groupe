<?php
/**
 * Template Name: Template WordPress — Avocat
 *
 * Dedicated landing page for the AG Starter Avocat theme.
 * The actual rendering lives in template-parts/metier-page.php.
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'avocat',
    'slug_full'   => 'ag-starter-avocat',
    'icon'        => '⚖️',
    'name'        => 'Avocat',
    'audience_short' => 'cabinet',
    'palette'     => 'Navy &amp; champagne',

    'hero_title'    => 'Le thème WordPress pour les <em>cabinets d\'avocats</em>',
    'hero_subtitle' => 'Design sombre navy & champagne, 100% français, RGPD ready, formulaire RDV confidentiel intégré. Installation en 2 minutes, aucun plugin requis.',

    'description_long' => 'AG Starter Avocat est un thème WordPress gratuit spécifiquement pensé pour les cabinets d\'avocats, juristes et notaires francophones. Il inclut tout ce qu\'un professionnel du droit a besoin pour lancer sa vitrine en ligne : Custom Post Type pour gérer les domaines d\'expertise, formulaire de prise de rendez-vous confidentiel RGPD-compliant, présentation du Maître, honoraires transparents, et intégration Google Maps.',

    'free_features' => array(
        '<strong>CPT Domaines d\'expertise</strong> — gérez vos domaines depuis <code>Articles &gt; Domaines</code> avec icône, exemples de cas et image',
        '<strong>Formulaire RDV RGPD-compliant</strong> — nonce + honeypot + consentement explicite, envoi via wp_mail()',
        '<strong>Section "Le Maître"</strong> — photo, barreau, année, biographie, spécialités (configurable)',
        '<strong>Honoraires transparents</strong> — 3 paliers tarifaires (1er RDV, forfait, temps passé) + mention légale',
        '<strong>Section Cabinet</strong> — adresse, horaires, Google Maps embed, numéro garde à vue 24/7',
        '<strong>6 sections personnalisables</strong> depuis Apparence → Personnaliser → AG Starter',
        '<strong>Template single-domaine dédié</strong> — chaque domaine a sa propre page avec exemples',
        '100% responsive, blog, commentaires, recherche, compatible Gutenberg',
    ),

    'premium_features' => array(
        'Animations douces sur les domaines d\'expertise (fade-in + hover)',
        '<strong>Sticky header</strong> avec téléphone toujours visible — facilite l\'appel depuis mobile',
        '10 blocs Gutenberg juridiques (FAQ, timeline, témoignages anonymisés, "Avant/Après procédure")',
        'Customizer étendu : 50+ réglages (couleurs secondaires, bordures, espacements)',
        'Polices Google Fonts premium (Playfair, Cormorant — typographie sérieuse avocat)',
        'Notification par email plus stylée + copie automatique au demandeur',
        'Support email prioritaire 60 jours',
        'Documentation vidéo complète',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h avec notre équipe)',
        '<strong>Audit SEO juridique ciblé</strong> — mots-clés type "avocat divorce Paris", "avocat droit travail Lyon"',
        '<strong>Maintenance WordPress 1 an incluse</strong> (mises à jour, sauvegardes, sécurité)',
        'Rapport de performance trimestriel (trafic, conversion, position Google)',
        '<strong>Support prioritaire absolu</strong> (réponse sous 2h ouvrées)',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        'Intégration CRM (HubSpot, Pipedrive, Brevo) pour centraliser vos leads',
        'Appel stratégique de lancement avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Un template, même Premium, reste un template. Pour un cabinet qui joue pour gagner — défense pénale, gros dossiers d\'affaires, clientèle internationale — un site sur-mesure conçu par notre équipe va beaucoup plus loin : SEO juridique ciblé, stratégie de conversion éprouvée, et intégration IA pour automatiser la qualification des prospects. Premier appel avec Fabrizio gratuit, sans engagement.',
) );

get_template_part( 'template-parts/metier-page' );

get_footer();
