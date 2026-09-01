<?php
/**
 * Template Name: Template WordPress — Barber Shop
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
    'palette'     => 'Bleu électrique &amp; charbon',

    'hero_title'    => 'Le thème WordPress pour les <em>barber shops</em>',
    'hero_subtitle' => 'Un design de barbershop haut de gamme, et un système de file d\'attente par QR code intégré. Vos clients scannent en vitrine, prennent leur ticket, et reviennent quand c\'est leur tour. Fini la queue debout — et fini les clients qui repartent en voyant du monde.',

    'description_long' => 'AG Starter Barber est le premier thème WordPress gratuit avec un système de file d\'attente par QR code intégré. Conçu pour les barbershops et salons de coiffure urbains. Le client scanne le QR en vitrine, choisit sa prestation, reçoit un ticket avec l\'heure estimée de passage : il va prendre un café et revient à l\'heure, au lieu de repartir. Le barber gère la file depuis son tableau de bord WordPress. Temps moyen par coupe configurable, nombre de barbers ajustable. Le design complet est inclus dans la version gratuite : rien à acheter pour avoir un site qui donne envie.',

    'unique_features' => array(
        array(
            'icon'  => '🎫',
            'title' => 'File d\'attente par QR code',
            'desc'  => 'Le client scanne le QR en vitrine, prend un ticket et reçoit son heure de passage estimée. Il va prendre un café et revient à l\'heure — au lieu de repartir en voyant du monde.',
            'vs'    => 'Aucun thème de barbershop gratuit du marché n\'intègre une vraie file d\'attente.',
        ),
        array(
            'icon'  => '⏱️',
            'title' => 'Temps d\'attente calculé tout seul',
            'desc'  => 'Nombre de clients dans la file × durée moyenne d\'une coupe ÷ nombre de barbers : l\'attente affichée est réaliste et se met à jour en direct.',
            'vs'    => 'Les concurrents affichent au mieux une prise de RDV rigide, jamais une file en temps réel.',
        ),
        array(
            'icon'  => '📊',
            'title' => 'Vos stats, sur votre serveur (sans Google)',
            'desc'  => 'Visiteurs, sources de trafic, heures de pointe, prestations les plus demandées — calculés sur votre hébergement. Aucun cookie, aucune IP conservée : RGPD sans bandeau à installer.',
            'vs'    => 'Ailleurs, il faut brancher Google Analytics (cookies, RGPD, vos données chez un tiers).',
        ),
        array(
            'icon'  => '🖥️',
            'title' => 'Tableau de bord de gestion de file',
            'desc'  => 'Le barber pilote la file depuis WordPress : commencer, terminer, retirer un client, ajuster le nombre de coiffeurs. QR téléchargeable en PNG et SVG pour l\'imprimer en vitrine.',
            'vs'    => 'Un template classique s\'arrête au design : ici, l\'outil tourne vraiment.',
        ),
    ),

    'free_features' => array(
        '<strong>Le design complet, sans rien payer</strong> — hero plein écran, typographie capitale, titres géants, menu plein écran, cartes épurées',
        '<strong>Système de file d\'attente QR code</strong> — le client scanne, prend un ticket, reçoit une heure de passage',
        '<strong>Calcul automatique</strong> du temps d\'attente (nb clients × durée moyenne ÷ nb barbers)',
        'Tableau de bord admin — gestion de la file en temps réel (commencer / terminé / retirer)',
        'QR code téléchargeable en PNG et SVG pour impression en vitrine',
        '<strong>5 prestations configurables</strong> (coupe 10€, barbe 5€, dégradé 15€, etc.)',
        'Page d\'accueil complète : hero, tarifs, file d\'attente live, "comment ça marche"',
        'Logo et libellés du menu personnalisables',
        'Responsive mobile, tablette, ordinateur',
        'Compatible AG Starter Companion (import demo 1 clic)',
    ),

    'premium_features' => array(
        '<strong>Vos visiteurs, jour par jour</strong> — combien de personnes visitent votre site, combien de pages elles regardent, sur 7, 30 ou 90 jours',
        '<strong>D\'où viennent vos clients</strong> — Google, Instagram, Google Maps, bouche-à-oreille : vous savez enfin ce qui vous amène du monde',
        '<strong>Vos heures de pointe</strong> — à quelles heures les tickets sont pris, pour organiser vos plannings',
        '<strong>Vos prestations les plus demandées</strong> — ce qui marche vraiment chez vous, en chiffres',
        '<strong>Aucun service extérieur</strong> — pas de Google Analytics : les chiffres sont calculés sur votre hébergement, ils vous appartiennent',
        '<strong>Aucun cookie, aucune adresse IP conservée</strong> — conforme RGPD sans bandeau à installer, rien à faire de votre côté',
        'Support email 60 jours',
    ),

    'business_features' => array(
        '<strong>Tout Premium inclus</strong> — statistiques comprises',
        '<strong>Votre galerie de coupes</strong> — vous ajoutez et retirez vos photos vous-même, sans nous appeler',
        '<strong>Votre équipe</strong> — présentez vos barbers avec photo, nom et spécialité',
        '<strong>Les avis de vos clients</strong> — affichez vos témoignages sur la page d\'accueil',
        '<strong>Vos horaires détaillés</strong> — jour par jour, y compris les fermetures exceptionnelles',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        '<strong>Session stratégique 30 min</strong> avec un expert Alliance Groupe',
        'Support 2h ouvrées + appel Fabrizio',
    ),

    'upsell_text' => 'Le thème vous donne déjà une longueur d\'avance. Pour vraiment remplir le fauteuil, il faut être trouvé sur « barbier + votre quartier », des photos de coupes qui donnent envie, des avis Google mis en avant et, si vous voulez, une petite campagne locale rentable. Notre équipe s\'en occupe, avec la sécurité incluse. Premier appel gratuit avec Fabrizio.',
) );

get_template_part( 'template-parts/metier-page' );
get_footer();
