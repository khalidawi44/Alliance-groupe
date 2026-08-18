<?php
/**
 * Template Name: Template WordPress — Aide à domicile
 *
 * Dedicated landing page for the AG Starter Domicile theme (services à la personne).
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'domicile',
    'slug_full'   => 'ag-starter-domicile',
    'icon'        => '🏡',
    'name'        => 'Aide à domicile',
    'audience_short' => 'activité',
    'palette'     => 'Vert doux &amp; beige',

    'hero_title'    => 'Le thème WordPress pour les <em>services à la personne</em>',
    'hero_subtitle' => 'Aide à domicile pour seniors, familles et personnes en situation de handicap. Palette vert & beige rassurante. 100% français : prestations, zones d\'intervention, devis avec crédit d\'impôt et témoignages déjà en place.',

    'description_long' => 'AG Starter Domicile est un thème WordPress gratuit pour les services à la personne (SAP) et l\'aide à domicile : maintien à domicile des seniors, aide aux familles (ménage, garde d\'enfants), accompagnement du handicap et de la dépendance. La configuration métier en 1 clic adapte tout le site à votre spécialité, et un module de devis affiche automatiquement le prix après crédit d\'impôt de 50%.',

    'free_features' => array(
        '<strong>Configuration métier en 1 clic</strong> — 3 presets : seniors, familles, handicap (hero, services, témoignages, pages adaptés)',
        '<strong>Devis en ligne intégré</strong> avec fourchette de prix et montant après crédit d\'impôt de 50%',
        'Pages prêtes : Accueil, Prestations, Zones d\'intervention, Témoignages, Devis, À propos, Contact',
        'Textes 100% français au ton rassurant, spécifiques au secteur du soin',
        'Palette vert doux & beige chaleureuse, image d\'illustration incluse',
        'Adresse, secteur d\'intervention, téléphone et email configurables',
        '100% responsive, compatible Gutenberg, aucun plugin requis',
    ),

    'premium_features' => array(
        '<strong>Témoignages animés</strong> de familles (slider) + note globale',
        '<strong>Carte des zones d\'intervention</strong> interactive',
        '<strong>Prise de contact rapide</strong> (rappel immédiat, formulaire d\'évaluation à domicile)',
        'Animations douces sur les sections services et témoignages',
        '10 blocs Gutenberg SAP (prestation avec tarif, FAQ crédit d\'impôt, CTA "Évaluation gratuite")',
        'Sticky header avec bouton "Devis gratuit" toujours visible',
        'Polices Google Fonts (élégance + lisibilité)',
        'Support email 60 jours',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h)',
        '<strong>Intégration CRM / logiciel SAP</strong> — centralisez vos bénéficiaires, plannings et intervenants',
        '<strong>Maintenance WordPress 1 an incluse</strong>',
        'Audit SEO local (mots-clés type "aide à domicile Nantes", "auxiliaire de vie seniors")',
        'Support prioritaire (réponse sous 2h ouvrées)',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        'Rapport trimestriel de performance (demandes de devis générées)',
        'Appel stratégique avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Dans les services à la personne, gagner un client se joue sur la confiance et la proximité : un site qui rassure, un SEO local ciblé ("aide à domicile" + votre ville), la mise en avant du crédit d\'impôt de 50% et un parcours qui transforme un proche inquiet en demande de devis. Notre équipe conçoit ça sur-mesure, à votre marque. Premier appel gratuit.',
) );

get_template_part( 'template-parts/metier-page' );

get_footer();
