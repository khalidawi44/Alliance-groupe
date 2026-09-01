<?php
/**
 * Template Name: Template WordPress — Coach
 *
 * Dedicated landing page for the AG Starter Coach theme.
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'coach',
    'slug_full'   => 'ag-starter-coach',
    'icon'        => '💼',
    'name'        => 'Coach',
    'audience_short' => 'activité',
    'palette'     => 'Bleu teal &amp; marine',

    'hero_title'    => 'Le thème WordPress pour les <em>coachs & consultants</em>',
    'hero_subtitle' => 'Coach, consultant, formateur, thérapeute. Design bleu teal & marine bienveillant. 100% français, sections accompagnements / témoignages / prise de rendez-vous déjà en place.',

    'description_long' => 'AG Starter Coach est un thème WordPress gratuit pour les professionnels de l\'accompagnement humain : coachs, consultants, formateurs, thérapeutes. La page d\'accueil inspire confiance et met l\'accent sur ce qui compte : vos offres, vos résultats clients, et la prise de rendez-vous. Ton bienveillant, design professionnel.',

    'unique_features' => array(
        array(
            'icon'  => '📅',
            'title' => 'Prise de rendez-vous intégrée',
            'desc'  => 'Un écran de réservation directement sur le site (séance découverte, visio ou présentiel) : le visiteur réserve un créneau sans quitter la page, sans appel, sans friction.',
            'vs'    => 'Les templates concurrents renvoient vers un outil tiers payant ou un simple formulaire de contact.',
        ),
        array(
            'icon'  => '📈',
            'title' => 'Témoignages avec résultat chiffré',
            'desc'  => 'Chaque avis client met en avant un résultat concret (avant/après, objectif atteint) — la preuve sociale qui convertit vraiment un prospect hésitant.',
            'vs'    => 'Ailleurs, les témoignages sont de simples citations sans preuve ni contexte.',
        ),
        array(
            'icon'  => '🎯',
            'title' => 'Tunnel « séance découverte gratuite »',
            'desc'  => 'Un parcours pensé pour transformer : le visiteur passe de « je découvre » à « je réserve un premier échange » en quelques secondes, CTA visible en permanence.',
            'vs'    => 'Un template classique se contente d\'afficher vos offres, sans guider vers l\'action.',
        ),
        array(
            'icon'  => '🔒',
            'title' => 'Sécurisé & conforme RGPD dès le départ',
            'desc'  => 'Formulaires protégés, données clients chiffrées, mentions conformes : essentiel quand on traite des informations sensibles sur des personnes.',
            'vs'    => 'La sécurité est notre métier — la plupart des thèmes gratuits l\'ignorent totalement.',
        ),
    ),

    'free_features' => array(
        '<strong>Page d\'accueil complète pré-remplie</strong> — hero avec CTA RDV, 3 cartes (coaching individuel, séances de groupe, témoignages), section "Mon parcours"',
        'Textes 100% français au ton bienveillant et structurant',
        '<strong>Horaires d\'ouverture + mention "Visio disponible"</strong>',
        'Palette bleu teal & marine qui inspire sérénité et confiance',
        'Adresse cabinet + téléphone + email configurables',
        '100% responsive',
        'Compatible Gutenberg (blog pour articles + commentaires)',
        'Aucun plugin requis, installation en 2 minutes',
    ),

    'premium_features' => array(
        '<strong>Témoignages animés</strong> avec photo client + résultat obtenu (slider)',
        '<strong>Bloc "Parcours de transformation"</strong> (timeline avant/après)',
        '<strong>Booking Cal.com intégré</strong> — réservation directe depuis le site',
        'Animations douces sur les sections offres et témoignages',
        '10 blocs Gutenberg coaching (offre avec prix, FAQ, CTA "Séance découverte", citation inspirante)',
        'Sticky header avec bouton "Prendre rendez-vous" toujours visible',
        'Polices Google Fonts (Playfair pour le côté premium, Inter pour la lisibilité)',
        'Support email 60 jours',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h)',
        '<strong>Intégration CRM complète</strong> (HubSpot, Pipedrive, Brevo) — centralisez vos prospects et clients',
        '<strong>Maintenance WordPress 1 an incluse</strong>',
        'Audit SEO coaching (mots-clés type "coach vie Nantes", "consultant stratégie Paris")',
        'Support prioritaire absolu (réponse sous 2h ouvrées)',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        'Rapport trimestriel de performance (nouveaux clients générés)',
        'Appel stratégique avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Les coachs qui remplissent leur agenda ont un point commun : un site qui raconte leur histoire avec impact, un SEO ciblé sur leur niche, et un tunnel de conversion pensé pour transformer un visiteur en client en 3 clics. Notre équipe conçoit ça sur-mesure, avec votre marque personnelle. Premier appel gratuit.',
) );

get_template_part( 'template-parts/metier-page' );

get_footer();
