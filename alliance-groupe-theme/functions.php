<?php
/**
 * Alliance Groupe Theme — functions.php
 * Thème autonome (pas besoin d'Elementor)
 */

// ── 1. Charger ag-import.php ────────────────────────────────────
$ag_import_file = get_stylesheet_directory() . '/ag-import.php';
if ( file_exists( $ag_import_file ) ) {
    require_once $ag_import_file;
}

// ── 1b. Charger ag-stripe-admin.php (page Liens de paiement) ─────
if ( is_admin() ) {
    $ag_stripe_admin_file = get_stylesheet_directory() . '/ag-stripe-admin.php';
    if ( file_exists( $ag_stripe_admin_file ) ) {
        require_once $ag_stripe_admin_file;
    }
}

// ── 1c. Cal.com (prise de RDV payante) retiré : remplacé par l'offre sur-mesure gratuite.
//        On ne charge plus ag-calendly-admin.php ; /rendez-vous redirige vers /sur-mesure.

// Crée les pages maison (sur-mesure, consultation) si elles n'existent pas.
add_action( 'admin_init', function () {
    if ( get_option( 'ag_auto_pages_v9' ) ) return;
    $pages = array(
        'sur-mesure'    => array( 'Projet sur-mesure', 'templates/page-sur-mesure.php' ),
        'consultation'  => array( 'Consultation payante', 'templates/page-consultation.php' ),
        'contrat-client' => array( 'Contrat Client', 'templates/page-contrat-client.php' ),
        'systeme-prospection' => array( 'Système de prospection', 'templates/page-systeme-prospection.php' ),
        'maintenance'   => array( 'Maintenance & Sérénité', 'templates/page-maintenance.php' ),
        'accueil-v2'    => array( 'Accueil (audit-first)', 'templates/page-accueil-epuree.php' ),
        'tester-mon-site' => array( 'Tester mon site', 'templates/page-tester.php' ),
        'le-voyage'     => array( 'Le Voyage', 'templates/page-experience.php' ),
        'retours'       => array( 'Politique de retour', 'templates/page-retours.php' ),
        'livraison'     => array( 'Politique de livraison', 'templates/page-livraison.php' ),
    );
    foreach ( $pages as $slug => $p ) {
        if ( get_page_by_path( $slug ) ) continue;
        wp_insert_post( array(
            'post_title'    => $p[0],
            'post_name'     => $slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '',
            'page_template' => $p[1],
        ) );
    }
    update_option( 'ag_auto_pages_v9', 1 );
} );

// Retire « Le Voyage » de TOUS les menus (page conservée « en réserve » :
// elle existe toujours et reste accessible par URL, mais n'apparaît plus
// dans la navigation). Pas besoin de toucher au menu en base.
add_filter( 'wp_nav_menu_objects', function ( $items ) {
    if ( ! is_array( $items ) ) return $items;
    foreach ( $items as $k => $it ) {
        $url   = isset( $it->url ) ? (string) $it->url : '';
        $title = isset( $it->title ) ? trim( wp_strip_all_tags( (string) $it->title ) ) : '';
        if ( false !== strpos( $url, '/le-voyage' ) || 'Le Voyage' === $title ) {
            unset( $items[ $k ] );
        }
    }
    return $items;
}, 20 );

// L'ancienne page de prise de RDV (Cal.com) redirige vers l'offre sur-mesure.
add_action( 'template_redirect', function () {
    if ( is_page( 'rendez-vous' ) || is_page_template( 'templates/page-rdv.php' ) ) {
        wp_safe_redirect( home_url( '/sur-mesure' ), 301 );
        exit;
    }
} );

// ── 1c2. Auto-sync GitHub : Apparence > 🚀 Sync GitHub ─────────
$ag_github_sync_file = get_stylesheet_directory() . '/inc/ag-github-sync.php';
if ( file_exists( $ag_github_sync_file ) ) {
    require_once $ag_github_sync_file;
}

// ── 1c3. Données détaillées des 6 services (consommées par template-parts/service-detail.php)
$ag_services_data_file = get_stylesheet_directory() . '/inc/services-data.php';
if ( file_exists( $ag_services_data_file ) ) {
    require_once $ag_services_data_file;
}

// ── 1c4. SEO meta (titres + descriptions tunes par mot-cle, LocalBusiness bureaux, Offers templates)
$ag_seo_meta_file = get_stylesheet_directory() . '/inc/ag-seo-meta.php';
if ( file_exists( $ag_seo_meta_file ) ) {
    require_once $ag_seo_meta_file;
}

// ── 1c4a-bis. SEO local : NAP centralisé + schema LocalBusiness (accueil/contact) + checklist Fiche Google
$ag_seo_localbiz_file = get_stylesheet_directory() . '/inc/ag-seo-localbiz.php';
if ( file_exists( $ag_seo_localbiz_file ) ) {
    require_once $ag_seo_localbiz_file;
}

// ── 1c4a-ter. Réalisations (portfolio éditable) : « Nos projets récents » + liens fiche Google
$ag_portfolio_file = get_stylesheet_directory() . '/inc/ag-portfolio.php';
if ( file_exists( $ag_portfolio_file ) ) {
    require_once $ag_portfolio_file;
}

// ── 1c4a-quater. Tunnels sans appel : audit payant (mandat signé) + RDV site personnalisé (Google Agenda)
$ag_mandat_file = get_stylesheet_directory() . '/inc/ag-mandat.php';
if ( file_exists( $ag_mandat_file ) ) {
    require_once $ag_mandat_file;
}

// ── 1c4a-quinquies. Cartographie « en chaîne » des agences web (agence → ses créations, classées pire→meilleur)
$ag_agences_file = get_stylesheet_directory() . '/inc/ag-agences.php';
if ( file_exists( $ag_agences_file ) ) {
    require_once $ag_agences_file;
}

// ── 1c4a-sexies. Veille « Appels d'offres publics » (BOAMP open data) : leads chauds à budget voté
$ag_appels_offres_file = get_stylesheet_directory() . '/inc/ag-appels-offres.php';
if ( file_exists( $ag_appels_offres_file ) ) {
    require_once $ag_appels_offres_file;
}

// ── 1c4a-septies. Missions ambassadeurs (« ton BeMyEye ») + Plateformes de missions web
$ag_missions_file = get_stylesheet_directory() . '/inc/ag-missions.php';
if ( file_exists( $ag_missions_file ) ) {
    require_once $ag_missions_file;
}

// ── 1c4a-octies. Espace Composants (bibliothèque web façon uiverse.io) : configurateur + copie/ZIP + créateurs du mois
$ag_composants_file = get_stylesheet_directory() . '/inc/ag-composants.php';
if ( file_exists( $ag_composants_file ) ) {
    require_once $ag_composants_file;
}

// ── 1c4a-octies-bis. Marketplace des composants : créateur gratuit/payant, encaissement vendeur, commission plateforme, verrou de téléchargement payant.
$ag_composants_market_file = get_stylesheet_directory() . '/inc/ag-composants-market.php';
if ( file_exists( $ag_composants_market_file ) ) {
    require_once $ag_composants_market_file;
}

// ── 1c4a-octies-ter. Atelier IA (/atelier) : landing galerie des outils IA + services (vignettes générées par IA).
$ag_atelier_file = get_stylesheet_directory() . '/inc/ag-atelier.php';
if ( file_exists( $ag_atelier_file ) ) {
    require_once $ag_atelier_file;
}

// ── 1c4a-nonies. Noyau IA partagé (clé ag_ai_key) : réutilisé par les modules « nouveauté » ci-dessous.
$ag_ia_file = get_stylesheet_directory() . '/inc/ag-ia.php';
if ( file_exists( $ag_ia_file ) ) {
    require_once $ag_ia_file;
}

// ── 1c4a-decies. Journal public « Fait par l'IA » (transparence · sans clé API).
$ag_journal_ia_file = get_stylesheet_directory() . '/inc/ag-journal-ia.php';
if ( file_exists( $ag_journal_ia_file ) ) {
    require_once $ag_journal_ia_file;
}

// ── 1c4a-undecies. « Vois ton site refait par l'IA en 60 s » (maquette live + lead).
$ag_refais_file = get_stylesheet_directory() . '/inc/ag-refais-mon-site.php';
if ( file_exists( $ag_refais_file ) ) {
    require_once $ag_refais_file;
}

// ── 1c4a-duodecies. Concierge IA (assistant flottant + capture de leads en tool-use).
$ag_concierge_file = get_stylesheet_directory() . '/inc/ag-concierge.php';
if ( file_exists( $ag_concierge_file ) ) {
    require_once $ag_concierge_file;
}

// ── 1c4a-terdecies. Devis instantané par l'IA (voix ou texte, sortie structurée).
$ag_devis_file = get_stylesheet_directory() . '/inc/ag-devis-instant.php';
if ( file_exists( $ag_devis_file ) ) {
    require_once $ag_devis_file;
}

// ── 1c4a-quaterdecies. Gardien de nuit (« le site se répare tout seul la nuit »).
$ag_nuit_file = get_stylesheet_directory() . '/inc/ag-nuit.php';
if ( file_exists( $ag_nuit_file ) ) {
    require_once $ag_nuit_file;
}

// ── 1c4a-quindecies. Suivi comportement visiteurs « venus seuls » (inbound) + page 👣 Visiteurs.
$ag_visiteurs_file = get_stylesheet_directory() . '/inc/ag-visiteurs.php';
if ( file_exists( $ag_visiteurs_file ) ) {
    require_once $ag_visiteurs_file;
}

// ── 1c4b. SEO pages piliers (création site Nantes + cybersécurité/NIS2) + LocalBusiness/Breadcrumb/Service
$ag_seo_pages_file = get_stylesheet_directory() . '/inc/ag-seo-pages.php';
if ( file_exists( $ag_seo_pages_file ) ) {
    require_once $ag_seo_pages_file;
}

// ── 1c4c. SEO blog (articles NIS2 / prix site Nantes / site sécurisé) auto-publiés
$ag_seo_blog_file = get_stylesheet_directory() . '/inc/ag-seo-blog.php';
if ( file_exists( $ag_seo_blog_file ) ) {
    require_once $ag_seo_blog_file;
}

// ── 1c4d. SEO blog auto-pilote (1 article/semaine depuis une banque, cron)
$ag_seo_autopub_file = get_stylesheet_directory() . '/inc/ag-seo-autopub.php';
if ( file_exists( $ag_seo_autopub_file ) ) {
    require_once $ag_seo_autopub_file;
}

// ── 1c4e. Avis Google (QR + relance auto depuis le CRM, conforme — pas de faux avis)
$ag_avis_file = get_stylesheet_directory() . '/inc/ag-avis.php';
if ( file_exists( $ag_avis_file ) ) {
    require_once $ag_avis_file;
}

// ── 1c4f. Témoignages clients sur le site (formulaire + schema, en attendant la fiche Google)
$ag_temoignages_file = get_stylesheet_directory() . '/inc/ag-temoignages.php';
if ( file_exists( $ag_temoignages_file ) ) {
    require_once $ag_temoignages_file;
}

// ── 1c4g. GEO — être cité par les IA (llms.txt + page comparatif + FAQ + schema)
$ag_geo_file = get_stylesheet_directory() . '/inc/ag-geo.php';
if ( file_exists( $ag_geo_file ) ) {
    require_once $ag_geo_file;
}

// ── 1c4h. Relais Judilibre mutualisé (clé PISTE unique → clients sans config)
$ag_jrp_file = get_stylesheet_directory() . '/inc/ag-judilibre-proxy.php';
if ( file_exists( $ag_jrp_file ) ) {
    require_once $ag_jrp_file;
}

// ── Section TARIFS PREMIUM (shortcode [ag_pricing_pro]) — design ui-ux-pro-max
$ag_pricing_file = get_stylesheet_directory() . '/inc/ag-pricing-pro.php';
if ( file_exists( $ag_pricing_file ) ) {
    require_once $ag_pricing_file;
}

// ── Tableau de bord Bug Bounty (perso, admin) : programmes rémunérés + suivi
$ag_bb_file = get_stylesheet_directory() . '/inc/ag-bugbounty.php';
if ( file_exists( $ag_bb_file ) ) {
    require_once $ag_bb_file;
}

// ── Durcissement sécurité de NOTRE site (xmlrpc, énumération, en-têtes, version)
$ag_hardening_file = get_stylesheet_directory() . '/inc/ag-hardening.php';
if ( file_exists( $ag_hardening_file ) ) {
    require_once $ag_hardening_file;
}

// ── 1c5. Programme Ambassadeurs (inscriptions, ventes, commissions 10%, paiements)
$ag_ambassadeurs_file = get_stylesheet_directory() . '/inc/ag-ambassadeurs.php';
if ( file_exists( $ag_ambassadeurs_file ) ) {
    require_once $ag_ambassadeurs_file;
}

// ── 1c5b. Recrutement International (manuel assisté, 10 pays francophones)
$ag_recrut_intl_file = get_stylesheet_directory() . '/inc/ag-recrut-intl.php';
if ( file_exists( $ag_recrut_intl_file ) ) {
    require_once $ag_recrut_intl_file;
}

// ── 1c5b-bis. Robot Recrutement Ambassadeurs (diffusion + API France Travail + Places)
$ag_recrut_robot_file = get_stylesheet_directory() . '/inc/ag-recrut-robot.php';
if ( file_exists( $ag_recrut_robot_file ) ) {
    require_once $ag_recrut_robot_file;
}

// ── 1c5b-ter. Candidatures Ambassadeurs (formulaire + réponse auto + alerte SMS + gestion)
$ag_candidatures_file = get_stylesheet_directory() . '/inc/ag-candidatures.php';
if ( file_exists( $ag_candidatures_file ) ) {
    require_once $ag_candidatures_file;
}

// ── 1c5b-quater. Passerelle SMS (Android + SIM) : ag_sms_send() vers tout numéro
$ag_sms_gateway_file = get_stylesheet_directory() . '/inc/ag-sms-gateway.php';
if ( file_exists( $ag_sms_gateway_file ) ) {
    require_once $ag_sms_gateway_file;
}

// ── 1c5b-quater-2. Robot vocal IA : webhook de compte-rendu d'appel → CRM
$ag_voice_file = get_stylesheet_directory() . '/inc/ag-voice-webhook.php';
if ( file_exists( $ag_voice_file ) ) {
    require_once $ag_voice_file;
}

// ── 1c5b-quinquies. Ma prospection (page front-end simple pour un proche/ambassadeur)
$ag_ma_prospection_file = get_stylesheet_directory() . '/inc/ag-ma-prospection.php';
if ( file_exists( $ag_ma_prospection_file ) ) {
    require_once $ag_ma_prospection_file;
}

// ── 1c5b-sexies. PWA : rend le site + l'espace ambassadeur installables en app (iOS/Android)
$ag_pwa_file = get_stylesheet_directory() . '/inc/ag-pwa.php';
if ( file_exists( $ag_pwa_file ) ) {
    require_once $ag_pwa_file;
}

// ── 1c5b-septies. Badge flottant discret « On recrute » → page candidature ambassadeur
$ag_recrut_badge_file = get_stylesheet_directory() . '/inc/ag-recrut-badge.php';
if ( file_exists( $ag_recrut_badge_file ) ) {
    require_once $ag_recrut_badge_file;
}

// ── 1c5b-octies. Annonces / Promos in-app (bandeau piloté depuis wp-admin, auto-MAJ)
$ag_app_promo_file = get_stylesheet_directory() . '/inc/ag-app-promo.php';
if ( file_exists( $ag_app_promo_file ) ) {
    require_once $ag_app_promo_file;
}

// ── 1c5b-nonies. Notifications push (Web Push VAPID — reçues même app fermée)
$ag_push_file = get_stylesheet_directory() . '/inc/ag-push.php';
if ( file_exists( $ag_push_file ) ) {
    require_once $ag_push_file;
}

// ── 1c5c. Kit Print (cartes, flyers, autocollants, affiches A4 avec QR)
$ag_kit_print_file = get_stylesheet_directory() . '/inc/ag-kit-print.php';
if ( file_exists( $ag_kit_print_file ) ) {
    require_once $ag_kit_print_file;
}

// ── 1c5d. Demo leaderboard (4 ambassadeurs + 6 recruteurs démo pour social proof)
$ag_demo_board_file = get_stylesheet_directory() . '/inc/ag-demo-board.php';
if ( file_exists( $ag_demo_board_file ) ) {
    require_once $ag_demo_board_file;
}

// ── 1c5e. Audit SEO gratuit (lead magnet)
$ag_audit_seo_file = get_stylesheet_directory() . '/inc/ag-audit-seo.php';
if ( file_exists( $ag_audit_seo_file ) ) {
    require_once $ag_audit_seo_file;
}

// ── 1c5e-bis. « Tester mon site » (freemium) + Espace Audit (prospection admin)
$ag_tester_file = get_stylesheet_directory() . '/inc/ag-tester.php';
if ( file_exists( $ag_tester_file ) ) {
    require_once $ag_tester_file;
}

// ── 1c5e-ter. Pont « Audit approfondi » wp-admin ↔ Kali (file d'attente sécurisée)
$ag_pentest_bridge_file = get_stylesheet_directory() . '/inc/ag-pentest-bridge.php';
if ( file_exists( $ag_pentest_bridge_file ) ) {
    require_once $ag_pentest_bridge_file;
}

// ── 1c5e-quater. Réseaux sociaux : publication auto Facebook Page + Instagram Pro
$ag_social_file = get_stylesheet_directory() . '/inc/ag-social.php';
if ( file_exists( $ag_social_file ) ) {
    require_once $ag_social_file;
}

// ── 1c5f. Tirage au sort mensuel (1 site gratuit / mois)
$ag_tirage_file = get_stylesheet_directory() . '/inc/ag-tirage-mensuel.php';
if ( file_exists( $ag_tirage_file ) ) {
    require_once $ag_tirage_file;
}

// ── 1c6. PayPal automatique (webhooks : credit auto des commissions au paiement)
$ag_paypal_file = get_stylesheet_directory() . '/inc/ag-paypal.php';
if ( file_exists( $ag_paypal_file ) ) {
    require_once $ag_paypal_file;
}

// ── 1c6b. Vente de licences de templates via PayPal (remplace Stripe)
$ag_licence_paypal_file = get_stylesheet_directory() . '/inc/ag-licence-paypal.php';
if ( file_exists( $ag_licence_paypal_file ) ) {
    require_once $ag_licence_paypal_file;
}

// ── 1c6c. Créateur de site (« Mon métier n'est pas là ») : formulaire +
//          paiement Business + génération ZIP personnalisé (base Avocat Business)
$ag_site_creator_file = get_stylesheet_directory() . '/inc/ag-site-creator.php';
if ( file_exists( $ag_site_creator_file ) ) {
    require_once $ag_site_creator_file;
}

// ── 1c6d. Flux produits Google Merchant Center (templates Premium)
$ag_merchant_feed_file = get_stylesheet_directory() . '/inc/ag-merchant-feed.php';
if ( file_exists( $ag_merchant_feed_file ) ) {
    require_once $ag_merchant_feed_file;
}

// ── 1c6d-bis. Flux produits Meta (Facebook/Instagram) : /meta-catalog-feed.xml
$ag_meta_feed_file = get_stylesheet_directory() . '/inc/ag-meta-feed.php';
if ( file_exists( $ag_meta_feed_file ) ) {
    require_once $ag_meta_feed_file;
}

// ── 1c6d-ter. Pixel Meta (Facebook/Instagram) avec Consent Mode RGPD
$ag_meta_pixel_file = get_stylesheet_directory() . '/inc/ag-meta-pixel.php';
if ( file_exists( $ag_meta_pixel_file ) ) {
    require_once $ag_meta_pixel_file;
}

// ── 1c6e. Google Avis clients (badge + opt-in enquête sur /merci-achat)
$ag_google_reviews_file = get_stylesheet_directory() . '/inc/ag-google-reviews.php';
if ( file_exists( $ag_google_reviews_file ) ) {
    require_once $ag_google_reviews_file;
}

// ── 1c7. Pop-up d'incitation "devenir ambassadeur" (visiteurs non-membres)
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    // Pop-up DESACTIVEE (demande Fabrice 05/06). Pour la reactiver :
    // definir l'option ag_ambassador_popup_on = 1 (Reglages / base).
    if ( ! get_option( 'ag_ambassador_popup_on', 0 ) ) return;
    $kind = function_exists( 'ag_espace_member_kind' ) ? ag_espace_member_kind() : '';
    if ( in_array( $kind, array( 'ambassadeur', 'admin' ), true ) ) return; // pas pour les vendeurs/admin
    $exclude = array(
        'templates/page-connexion.php', 'templates/page-ambassadeurs.php',
        'templates/page-espace-ambassadeur.php', 'templates/page-espace-client.php',
        'templates/page-studio.php', 'templates/page-mot-de-passe.php',
        'templates/page-guide-ambassadeur.php',
    );
    foreach ( $exclude as $tpl ) { if ( is_page_template( $tpl ) ) return; }
    get_template_part( 'template-parts/ambassador-popup' );
}, 50 );

// ── 1c8. Prospection : moteur (capture prospects + admin) + chat sur le site
$ag_prospection_file = get_stylesheet_directory() . '/inc/ag-prospection.php';
if ( file_exists( $ag_prospection_file ) ) {
    require_once $ag_prospection_file;
}

// ── Capture lead « Guide gratuit avocat » (entonnoir freemium .org → site mère)
$ag_lead_avocat_file = get_stylesheet_directory() . '/inc/ag-lead-avocat.php';
if ( file_exists( $ag_lead_avocat_file ) ) {
    require_once $ag_lead_avocat_file;
}

// ── Réception des leads Google Ads (Lead Form webhook) → CRM
$ag_google_lead_file = get_stylesheet_directory() . '/inc/ag-google-lead.php';
if ( file_exists( $ag_google_lead_file ) ) {
    require_once $ag_google_lead_file;
}

// ── Réglage des images « sécurité » (pop-up piratage + hero) sans code
$ag_images_secu_file = get_stylesheet_directory() . '/inc/ag-images-secu.php';
if ( is_admin() && file_exists( $ag_images_secu_file ) ) {
    require_once $ag_images_secu_file;
}

// ── Offre « Jeune avocat » : 3 mois de Premium offerts (codes école)
$ag_jeune_avocat_file = get_stylesheet_directory() . '/inc/ag-jeune-avocat.php';
if ( file_exists( $ag_jeune_avocat_file ) ) {
    require_once $ag_jeune_avocat_file;
}

// ── 1c9. Centre de contrôle admin (un seul écran pour tout piloter)
$ag_admin_hub_file = get_stylesheet_directory() . '/inc/ag-admin-hub.php';
if ( is_admin() && file_exists( $ag_admin_hub_file ) ) {
    require_once $ag_admin_hub_file;
}

// ── 1c10. Zones ambassadeurs (territoires par département + enchères)
$ag_zones_file = get_stylesheet_directory() . '/inc/ag-zones.php';
if ( file_exists( $ag_zones_file ) ) {
    require_once $ag_zones_file;
}

// ── 1c11. Agent Coach (feuille de route quotidienne + rappels)
$ag_coach_file = get_stylesheet_directory() . '/inc/ag-coach.php';
if ( file_exists( $ag_coach_file ) ) {
    require_once $ag_coach_file;
}
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    $kind = function_exists( 'ag_espace_member_kind' ) ? ag_espace_member_kind() : '';
    if ( in_array( $kind, array( 'ambassadeur', 'admin' ), true ) ) return;
    $exclude = array(
        'templates/page-espace-ambassadeur.php', 'templates/page-espace-client.php',
        'templates/page-studio.php', 'templates/page-mot-de-passe.php',
        'templates/page-guide-ambassadeur.php',
    );
    foreach ( $exclude as $tpl ) { if ( is_page_template( $tpl ) ) return; }
    // Chat d'équipe fictive (Léo/Sofia/Karim/Nadia) désactivé : studio solo.
    // get_template_part( 'template-parts/prospect-chat' );
}, 60 );

// ── 1c6. Espaces membres (clients & commerciaux) : comptes, connexion, dashboards
$ag_espaces_file = get_stylesheet_directory() . '/inc/ag-espaces.php';
if ( file_exists( $ag_espaces_file ) ) {
    require_once $ag_espaces_file;
}

// ── Musique de fond : DÉSACTIVÉE sur demande user (lecteur retiré du site). ──
// $ag_music_file = get_stylesheet_directory() . '/inc/ag-music.php';
// if ( file_exists( $ag_music_file ) ) {
//     require_once $ag_music_file;
// }

// ── Landing "Audit sécurité" (home v2) : styles dédiés + déchargement des
//    styles du thème sur ce template pour un rendu propre, sans conflit. ──
add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page_template( 'templates/page-audit-securite.php' ) && ! is_page_template( 'templates/page-accueil-epuree.php' ) ) {
        return;
    }
    foreach ( array( 'ag-theme-style', 'ag-main-css', 'ag-cinema-upgrades', 'ag-google-fonts' ) as $h ) {
        wp_dequeue_style( $h );
        wp_deregister_style( $h );
    }
    wp_enqueue_style(
        'ag-audit-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600;700;900&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap',
        array(),
        null
    );
    $ag_audit_css = get_stylesheet_directory() . '/assets/css/audit-home.css';
    wp_enqueue_style(
        'ag-audit-home',
        get_stylesheet_directory_uri() . '/assets/css/audit-home.css',
        array( 'ag-audit-fonts' ),
        file_exists( $ag_audit_css ) ? filemtime( $ag_audit_css ) : '1.0'
    );
}, 100 );

// ── 1d. Shortcode [ag_promo_video] : insère la vidéo promo Alliance ─────
// Usage Gutenberg : ajouter un block "Shortcode" et taper [ag_promo_video]
// Attributs : title="..." lead="..." cta_label="..." cta_url="..."
//             poster="..." video_url="..." (tous optionnels)
add_shortcode( 'ag_promo_video', function ( $atts ) {
    $atts = shortcode_atts( array(
        'title'     => '',
        'lead'      => '',
        'cta_label' => '',
        'cta_url'   => '',
        'poster'    => '',
        'video_url' => '',
    ), $atts, 'ag_promo_video' );
    ob_start();
    // get_template_part avec args nécessite WP 5.5+. On expose aussi $args
    // en variable globale pour compatibilité.
    $GLOBALS['ag_promo_video_args'] = $atts;
    get_template_part( 'template-parts/promo-video', null, $atts );
    unset( $GLOBALS['ag_promo_video_args'] );
    return ob_get_clean();
} );

// ── 2. Enqueue styles & scripts ─────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    // ── GSAP + ScrollTrigger : charges UNE FOIS, dans le <head>, AVANT tous les
    //    autres scripts -> fini les chargements dynamiques concurrents (= bugs
    //    d'animation). Utilise les fichiers LOCAUX si presents
    //    (assets/js/lib/gsap.min.js + ScrollTrigger.min.js), sinon le CDN.
    //    NB : on n'utilise PAS "vendor" (ignore par .gitignore).
    $ag_vendor_dir = get_stylesheet_directory() . '/assets/js/lib/';
    $ag_vendor_uri = get_stylesheet_directory_uri() . '/assets/js/lib/';
    $ag_gsap_ok = file_exists( $ag_vendor_dir . 'gsap.min.js' )         && filesize( $ag_vendor_dir . 'gsap.min.js' )         > 1000;
    $ag_st_ok   = file_exists( $ag_vendor_dir . 'ScrollTrigger.min.js' ) && filesize( $ag_vendor_dir . 'ScrollTrigger.min.js' ) > 1000;
    wp_enqueue_script(
        'ag-gsap',
        $ag_gsap_ok ? $ag_vendor_uri . 'gsap.min.js' : 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js',
        array(),
        $ag_gsap_ok ? filemtime( $ag_vendor_dir . 'gsap.min.js' ) : '3.13.0',
        false
    );
    wp_enqueue_script(
        'ag-gsap-st',
        $ag_st_ok ? $ag_vendor_uri . 'ScrollTrigger.min.js' : 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js',
        array( 'ag-gsap' ),
        $ag_st_ok ? filemtime( $ag_vendor_dir . 'ScrollTrigger.min.js' ) : '3.13.0',
        false
    );

    // Style du thème (style.css obligatoire pour WordPress)
    wp_enqueue_style(
        'ag-theme-style',
        get_stylesheet_uri(),
        array(),
        '2.0.0'
    );

    wp_enqueue_style(
        'ag-google-fonts',
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'ag-main-css',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        array( 'ag-theme-style' ),
        file_exists( get_stylesheet_directory() . '/assets/css/main.css' )
            ? filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
            : '2.0.2'
    );

    // Enrichissements ciné : menu glassmorphism + hero pages internes
    $ag_cinema_css = get_stylesheet_directory() . '/assets/css/cinema-upgrades.css';
    if ( file_exists( $ag_cinema_css ) ) {
        wp_enqueue_style(
            'ag-cinema-upgrades',
            get_stylesheet_directory_uri() . '/assets/css/cinema-upgrades.css',
            array( 'ag-main-css' ),
            filemtime( $ag_cinema_css )
        );
    }
    // Pack JS ciné : Lenis + tilt + cursor + reveal + particles
    $ag_cinema_js = get_stylesheet_directory() . '/assets/js/cinema-fx.js';
    if ( file_exists( $ag_cinema_js ) ) {
        wp_enqueue_script(
            'ag-cinema-fx',
            get_stylesheet_directory_uri() . '/assets/js/cinema-fx.js',
            array(),
            filemtime( $ag_cinema_js ),
            true
        );
    }

    // ── Couche cinématographique expérimentale (module isolé) ──
    $ag_cine_css = get_stylesheet_directory() . '/assets/css/ag-cinema.css';
    if ( file_exists( $ag_cine_css ) ) {
        wp_enqueue_style(
            'ag-cinema',
            get_stylesheet_directory_uri() . '/assets/css/ag-cinema.css',
            array( 'ag-main-css' ),
            filemtime( $ag_cine_css )
        );
    }
    $ag_cine_js = get_stylesheet_directory() . '/assets/js/ag-cinema.js';
    if ( file_exists( $ag_cine_js ) ) {
        wp_enqueue_script(
            'ag-cinema',
            get_stylesheet_directory_uri() . '/assets/js/ag-cinema.js',
            array( 'ag-gsap', 'ag-gsap-st' ),
            filemtime( $ag_cine_js ),
            true
        );
    }

    // ── Couche immersive (intro, transitions, grain, sons) — module isolé ──
    $ag_imm_css = get_stylesheet_directory() . '/assets/css/ag-immersive.css';
    if ( file_exists( $ag_imm_css ) ) {
        wp_enqueue_style(
            'ag-immersive',
            get_stylesheet_directory_uri() . '/assets/css/ag-immersive.css',
            array( 'ag-main-css' ),
            filemtime( $ag_imm_css )
        );
    }
    $ag_imm_js = get_stylesheet_directory() . '/assets/js/ag-immersive.js';
    if ( file_exists( $ag_imm_js ) ) {
        wp_enqueue_script(
            'ag-immersive',
            get_stylesheet_directory_uri() . '/assets/js/ag-immersive.js',
            array(),
            filemtime( $ag_imm_js ),
            true
        );
    }
} );

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'ag-main-js',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        array(),
        file_exists( get_stylesheet_directory() . '/assets/js/main.js' )
            ? filemtime( get_stylesheet_directory() . '/assets/js/main.js' )
            : '2.0.1',
        true
    );
} );

// NB : on ne défère PAS en masse les scripts d'animation du thème (GSAP,
// ScrollTrigger, ciné, immersif, main). Testé le 05/08 → régression : exécution
// groupée après chargement = TBT en hausse + animations tardives = gros CLS
// (0,329). Ces scripts pilotent la mise en page, ils doivent rester au rendu.
// Les gros tiers (Analytics, AdSense) sont différés ailleurs, sans effet CLS.

// ── PERF (CSS critique) : rendre NON bloquantes les feuilles d'ENRICHISSEMENT
//    (glassmorphism menu, effets ciné, immersif). Le premier écran (en-tête +
//    héro) est déjà stylé par main.css (bloquant, conservé) + le CSS inline du
//    template → ces 3 feuilles ne servent qu'à embellir après coup. Technique
//    media="print" → onload="all" : le rendu ne les attend plus. Elles ne
//    touchent PAS la mise en page (cosmétique) → aucun risque de CLS.
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
    $async = array( 'ag-cinema-upgrades', 'ag-cinema', 'ag-immersive' );
    if ( ! in_array( $handle, $async, true ) ) {
        return $tag;
    }
    $noscript = '';
    if ( preg_match( '/href=([\'"])(.*?)\1/', $tag, $m ) ) {
        $noscript = '<noscript><link rel="stylesheet" href="' . esc_url( $m[2] ) . '"></noscript>';
    }
    // Bascule media 'all' → 'print' (non bloquant), repasse en 'all' au chargement.
    $tag = preg_replace( "/media=(['\"])all\\1/", "media=\$1print\$1 onload=\"this.media='all'\"", $tag );
    return $tag . $noscript;
}, 10, 2 );

// ── 3. Theme support ────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
    register_nav_menus( array( 'primary' => 'Menu principal' ) );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption' ) );
}, 5 );

// ── 3b. Force search engine indexation ─────────────────────────
// Force blog_public=1 ET supprime le header X-Robots-Tag noindex
add_action( 'init', function () {
    if ( get_option( 'blog_public' ) == '0' ) {
        update_option( 'blog_public', '1' );
    }
}, 1 );

// Supprimer le hook WordPress qui envoie X-Robots-Tag: noindex
add_action( 'template_redirect', function () {
    header_remove( 'X-Robots-Tag' );
}, 99 );

// Empêcher wp_robots d'ajouter noindex
add_filter( 'wp_robots', function ( $robots ) {
    unset( $robots['noindex'] );
    unset( $robots['nofollow'] );
    return $robots;
}, 9999 );

// ── 3c. Custom XML sitemap (indépendant de Yoast / WP natif) ───
// /sitemap.xml + /ag-sitemap.xml + /wp-sitemap.xml  →  urlset (toutes les URLs)
// /sitemap_index.xml                                 →  sitemapindex (vrai format index)
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    $allowed = array( 'sitemap.xml', 'sitemap_index.xml', 'wp-sitemap.xml', 'ag-sitemap.xml' );
    if ( ! in_array( $path, $allowed, true ) ) {
        return;
    }

    // Nettoie tout output deja envoye (au cas ou un plugin a deja imprime
    // quelque chose) — un sitemap doit etre pur XML, pas un mot avant.
    if ( ob_get_level() ) {
        while ( ob_get_level() ) ob_end_clean();
    }

    // Headers : XML strict, pas de cache navigateur, pas de noindex
    // (Google a parfois refuse les sitemaps avec X-Robots-Tag noindex)
    header( 'Content-Type: application/xml; charset=UTF-8' );
    header( 'X-Robots-Tag: noindex, follow' ); // OK pour le sitemap lui-meme
    header( 'Cache-Control: public, max-age=3600' );
    status_header( 200 ); // force 200 (pas 404 / WP par defaut)

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

    // ── Cas 1 : /sitemap_index.xml = vrai sitemapindex ──
    if ( $path === 'sitemap_index.xml' ) {
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        echo '<sitemap><loc>' . esc_url( home_url( '/sitemap.xml' ) ) . '</loc><lastmod>' . esc_html( wp_date( 'Y-m-d' ) ) . '</lastmod></sitemap>' . "\n";
        echo '</sitemapindex>';
        exit;
    }

    // ── Cas 2 : sitemap classique = urlset ──
    // Pages a NE PAS indexer
    $excluded = array( 'merci-rdv', 'merci-achat' );

    // Collecter toutes les pages publiees
    $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'post_modified', 'sort_order' => 'DESC' ) );

    // Collecter tous les articles publies
    $posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ) );

    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Homepage
    echo '<url><loc>' . esc_url( home_url( '/' ) ) . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";

    // Pages
    if ( is_array( $pages ) ) {
        foreach ( $pages as $page ) {
            if ( in_array( $page->post_name, $excluded, true ) ) continue;
            $mod = get_the_modified_date( 'Y-m-d', $page );
            echo '<url><loc>' . esc_url( get_permalink( $page ) ) . '</loc><lastmod>' . $mod . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
        }
    }

    // Articles
    if ( is_array( $posts ) ) {
        foreach ( $posts as $post ) {
            $mod = get_the_modified_date( 'Y-m-d', $post );
            echo '<url><loc>' . esc_url( get_permalink( $post ) ) . '</loc><lastmod>' . $mod . '</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>' . "\n";
        }
    }

    echo '</urlset>';
    exit;
}, 0 ); // priority 0 = avant tout autre handler

// Désactiver le sitemap natif WP (on gère tout nous-mêmes)
add_filter( 'wp_sitemaps_enabled', '__return_false' );

// ── 3d. Redirection 301 /blog → /articles (conserve le SEO des anciennes URLs)
// L'ancienne page blog a ete renommee en "articles". On redirige proprement
// /blog et /blog/xxx pour ne pas perdre le jus des liens deja indexes par Google.
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    if ( $path === 'blog' || strpos( $path, 'blog/' ) === 0 ) {
        $new = home_url( '/' . preg_replace( '#^blog#', 'articles', $path ) . '/' );
        wp_redirect( $new, 301 );
        exit;
    }
}, 1 );

// ── 4. Auto-create categories ───────────────────────────────────
add_action( 'init', function () {
    if ( ! term_exists( 'Tech & IA', 'category' ) ) wp_insert_term( 'Tech & IA', 'category' );
    if ( ! term_exists( 'Conseils Digital', 'category' ) ) wp_insert_term( 'Conseils Digital', 'category' );
} );

// ── 5. Favicon ──────────────────────────────────────────────────
add_action( 'wp_head', function () {
    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
        if ( file_exists( $dir . 'logo.' . $ext ) ) {
            $url = $uri . 'logo.' . $ext;
            echo '<link rel="icon" href="' . esc_url( $url ) . '">' . "\n";
            echo '<link rel="apple-touch-icon" href="' . esc_url( $url ) . '">' . "\n";
            break;
        }
    }
} );

// ── 6. Register page templates ──────────────────────────────────
add_filter( 'theme_page_templates', function ( $templates ) {
    $templates['templates/page-accueil.php']         = 'Accueil';
    $templates['templates/page-accueil-cinema.php']  = 'Accueil cinématique';
    $templates['templates/page-services.php']        = 'Services';
    $templates['templates/page-realisations.php']    = 'Réalisations';
    $templates['templates/page-apropos.php']         = 'À propos';
    $templates['templates/page-contact.php']         = 'Contact';
    $templates['templates/page-service-web.php']     = 'Service — Création Web';
    $templates['templates/page-service-ia.php']      = 'Service — IA & Automatisation';
    $templates['templates/page-service-seo.php']     = 'Service — SEO';
    $templates['templates/page-service-ads.php']     = 'Service — Publicité';
    $templates['templates/page-service-brand.php']   = 'Service — Branding';
    $templates['templates/page-service-conseil.php'] = 'Service — Conseil';
    $templates['templates/page-fondateur.php']       = 'Notre Fondateur';
    $templates['templates/page-templates.php']       = 'Templates WordPress';
    $templates['templates/page-sur-mesure.php']      = 'Projet sur-mesure';
    $templates['templates/page-contrat-client.php']  = 'Contrat Client';
    $templates['templates/page-systeme-prospection.php'] = 'Système de prospection';
    $templates['templates/page-maintenance.php']     = 'Maintenance & Sérénité';
    $templates['templates/page-tester.php']          = 'Tester mon site';
    $templates['templates/page-experience.php']      = 'Expérience immersive (Le Voyage)';
    $templates['templates/page-accueil-epuree.php']  = 'Accueil épurée (audit-first)';
    $templates['templates/page-retours.php']         = 'Politique de retour';
    $templates['templates/page-livraison.php']       = 'Politique de livraison';
    $templates['templates/page-consultation.php']    = 'Consultation payante';
    $templates['templates/page-rdv.php']             = 'Prise de rendez-vous (déprécié)';
    $templates['templates/page-questions-flash.php'] = 'Questions Flash';
    $templates['templates/page-merci-rdv.php']       = 'Merci — Rendez-vous confirmé';
    $templates['templates/page-cookies.php']         = 'Cookies & Préférences';
    $templates['templates/page-mentions-legales.php']= 'Mentions légales & CGV';
    $templates['templates/page-confidentialite.php'] = 'Politique de confidentialité';
    $templates['templates/page-bureau-marrakech.php']= 'Bureau — Marrakech';
    $templates['templates/page-bureau-naples.php']   = 'Bureau — Naples';
    $templates['templates/page-bureau-nantes.php']   = 'Bureau — Nantes';
    $templates['templates/page-wordpress-barber.php']= 'Template WordPress — Barber Shop';
    $templates['templates/page-wordpress-association.php'] = 'Template WordPress — Association';
    $templates['templates/page-programme-racines.php'] = 'Programme Racines';
    $templates['templates/page-ambassadeurs.php']    = 'Programme Ambassadeurs';
    $templates['templates/page-contrat-ambassadeur.php'] = 'Contrat Ambassadeur';
    $templates['templates/page-sites-express.php']   = 'Sites Express';
    return $templates;
} );

// ── 7. Reading time helper ──────────────────────────────────────
if ( ! function_exists( 'ag_reading_time' ) ) {
    function ag_reading_time() {
        $content = get_post_field( 'post_content', get_the_ID() );
        return max( 1, ceil( str_word_count( strip_tags( $content ) ) / 250 ) );
    }
}

// ── 8. SEO meta description (fallback — utilise excerpt/contenu si pas d'override custom)
//    Si inc/ag-seo-meta.php a deja sorti une description tunee mot-cle, ce hook skippe
//    (via filter ag_skip_legacy_meta_description) pour eviter les doublons.
add_action( 'wp_head', function () {
    if ( apply_filters( 'ag_skip_legacy_meta_description', false ) ) return;
    $description = '';
    if ( is_singular() ) {
        if ( has_excerpt() ) {
            $description = wp_strip_all_tags( get_the_excerpt() );
        } else {
            $description = wp_trim_words( wp_strip_all_tags( get_the_content() ), 25 );
        }
    } elseif ( is_front_page() ) {
        $description = get_bloginfo( 'description' );
    } elseif ( is_archive() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            $description = $term->description;
        } elseif ( $term && ! empty( $term->name ) ) {
            $description = 'Articles dans la catégorie : ' . $term->name;
        }
    }
    if ( $description ) {
        echo '<meta name="description" content="' . esc_attr( substr( $description, 0, 160 ) ) . '">' . "\n";
    }
} );

// ── 8b. Noindex on thank-you & legal pages ─────────────────────
add_action( 'wp_head', function () {
    $noindex_slugs = array( 'merci-rdv', 'merci-achat' );
    $current_slug  = get_post_field( 'post_name' );
    if ( in_array( $current_slug, $noindex_slugs, true ) ) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}, 1 );

// ── 8b. Robots.txt amélioration ─────────────────────────────────
add_filter( 'robots_txt', function ( $output, $public ) {
    if ( $public ) {
        $home   = home_url( '/' );
        // Tous les robots : exploration complète du contenu (seuls l'admin/recherche/pages de remerciement sont exclus, ce qui est la norme SEO).
        $output  = "User-agent: *\n";
        $output .= "Allow: /\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Allow: /wp-admin/admin-ajax.php\n";
        $output .= "Disallow: /wp-includes/\n";
        $output .= "Disallow: /?s=\n";
        $output .= "Disallow: /merci-rdv\n";
        $output .= "Disallow: /merci-achat\n";
        $output .= "\n";
        // AdSense : le robot doit pouvoir lire TOUTES les pages pour servir des pubs pertinentes.
        $output .= "User-agent: Mediapartners-Google\n";
        $output .= "Allow: /\n";
        $output .= "\n";
        // Google Ads : vérification des pages de destination.
        $output .= "User-agent: AdsBot-Google\n";
        $output .= "Allow: /\n";
        $output .= "User-agent: AdsBot-Google-Mobile\n";
        $output .= "Allow: /\n";
        $output .= "\n";
        // Whitelist explicite des grands moteurs (rassurance / anti-blocage serveur).
        foreach ( array( 'Googlebot', 'Googlebot-Image', 'Bingbot', 'DuckDuckBot', 'YandexBot', 'Baiduspider', 'Applebot', 'facebookexternalhit', 'Twitterbot', 'LinkedInBot', 'WhatsApp', 'Slurp' ) as $bot ) {
            $output .= "User-agent: $bot\n";
            $output .= "Allow: /\n";
        }
        $output .= "\n";
        $output .= "Sitemap: " . $home . "sitemap.xml\n";
        $output .= "Sitemap: " . $home . "sitemap_index.xml\n";
    }
    return $output;
}, 10, 2 );

// ── 8b2. Ping Google & Bing on publish ──────────────────────────
add_action( 'publish_post', function () {
    $sitemap = home_url( '/sitemap.xml' );
    wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
    wp_remote_get( 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
} );
add_action( 'publish_page', function () {
    $sitemap = home_url( '/sitemap.xml' );
    wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
    wp_remote_get( 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
} );

// ── 8c. Canonical URL — WordPress le gère nativement, pas de doublon ──

// ── 8d. Open Graph + Twitter Card (enrichi)
//    Note : title + description peuvent etre overrides plus tot par inc/ag-seo-meta.php
//    via le hook priority 1. Dans ce cas on n'emet PAS de doublon ici (skip via filter).
add_action( 'wp_head', function () {
    $has_override = apply_filters( 'ag_skip_legacy_meta_description', false );

    $title   = wp_get_document_title();
    $desc    = get_bloginfo( 'description' );
    $url     = home_url( '/' );
    $img     = '';
    $og_type = 'website';

    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    $is_banner = false; // true = vraie bannière paysage 1200x630 -> grande carte ; false = logo -> petite vignette
    foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
        if ( file_exists( $dir . 'og-banner.' . $ext ) ) { $img = $uri . 'og-banner.' . $ext; $is_banner = true; break; }
    }
    if ( ! $img ) {
        foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
            if ( file_exists( $dir . 'logo.' . $ext ) ) { $img = $uri . 'logo.' . $ext; break; }
        }
    }

    if ( is_singular() ) {
        $title = get_the_title();
        $url   = get_permalink();
        if ( is_single() ) $og_type = 'article';
        if ( has_excerpt() ) $desc = wp_strip_all_tags( get_the_excerpt() );
        if ( has_post_thumbnail() ) { $img = get_the_post_thumbnail_url( null, 'large' ); $is_banner = true; }
    }

    echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
    if ( ! $has_override ) {
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    }
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    if ( $img ) {
        echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
        if ( $is_banner ) {
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }
    }
    echo '<meta property="og:site_name" content="Alliance Groupe">' . "\n";
    echo '<meta property="og:locale" content="fr_FR">' . "\n";
    // Grande carte seulement pour une vraie bannière paysage ; sinon petite vignette (logo pas géant).
    echo '<meta name="twitter:card" content="' . ( $is_banner ? 'summary_large_image' : 'summary' ) . '">' . "\n";
    if ( ! $has_override ) {
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
    }
    if ( $img ) {
        echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
    }
}, 2 );

// ── 8b. Google Analytics (GA4) + Google Ads, avec Consent Mode (RGPD) ──
if ( ! function_exists( 'ag_output_gtag' ) ) {
    function ag_output_gtag() {
        // GA4 géré par le Google tag externe (G-49BX81NSZ0, via GTM) → on n'émet PAS de 2e GA4 ici
        // pour éviter le double comptage. Renseigner l'option/filtre 'ag_ga4_id' pour réactiver le GA4 du thème.
        $ga  = trim( (string) apply_filters( 'ag_ga4_id', get_option( 'ag_ga4_id', '' ) ) );
        $ads = trim( (string) apply_filters( 'ag_ads_id', get_option( 'ag_ads_id', 'AW-18188842206' ) ) );
        if ( '' === $ga && '' === $ads ) return;
        $primary = $ga ?: $ads;
        ?>
<!-- Google tag (gtag.js) — Alliance Groupe, déclenché après consentement -->
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied'});
(function(){try{var c=JSON.parse(localStorage.getItem('ag_cookie_consent')||'null');if(c){gtag('consent','update',{analytics_storage:c.analytics?'granted':'denied',ad_storage:c.marketing?'granted':'denied',ad_user_data:c.marketing?'granted':'denied',ad_personalization:c.marketing?'granted':'denied'});}}catch(e){}})();
document.addEventListener('ag:consent',function(e){var c=e.detail||{};gtag('consent','update',{analytics_storage:c.analytics?'granted':'denied',ad_storage:c.marketing?'granted':'denied',ad_user_data:c.marketing?'granted':'denied',ad_personalization:c.marketing?'granted':'denied'});});
</script>
<script>
/* PERF : le tag Google (gtag.js) est chargé au 1er geste du visiteur OU peu après
   l'affichage — jamais dans le chemin critique (réduit le Total Blocking Time).
   Le Consent Mode ci-dessus reste actif immédiatement ; les événements de
   conversion sont mis en file dans dataLayer et partent dès le chargement. */
(function(){var done=false;function agLoadGtag(){if(done)return;done=true;var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $primary ); ?>';s.onload=function(){gtag('js',new Date());<?php if ( $ga ) : ?>gtag('config','<?php echo esc_js( $ga ); ?>');<?php endif; ?><?php if ( $ads ) : ?>gtag('config','<?php echo esc_js( $ads ); ?>');<?php endif; ?>};document.head.appendChild(s);}
var evs=['scroll','mousemove','touchstart','keydown','click'];function agOnce(){evs.forEach(function(e){window.removeEventListener(e,agOnce);});agLoadGtag();}evs.forEach(function(e){window.addEventListener(e,agOnce,{passive:true});});window.addEventListener('load',function(){setTimeout(agLoadGtag,3500);});})();
</script>
        <?php
    }
}

/**
 * Conversion Google Ads sur les VRAIS leads (best practice) plutôt que sur chaque page vue :
 *  - envoi d'un formulaire de contact (Contact Form 7, form .ag-form, ou form sur la page /contact)
 *  - clic sur un lien téléphone (tel:) ou email (mailto:)
 * Envoie aussi l'événement GA4 « generate_lead ». Respecte le Consent Mode (gtag ne déclenche
 * réellement qu'après consentement marketing).
 */
add_action( 'wp_footer', function () {
	$ads  = trim( (string) apply_filters( 'ag_ads_id', get_option( 'ag_ads_id', 'AW-18188842206' ) ) );
	$conv = trim( (string) apply_filters( 'ag_ads_conversion_label', get_option( 'ag_ads_conversion_label', 'ZCMWCPfQvcEcEN7pjuFD' ) ) );
	if ( '' === $ads || '' === $conv ) return;
	$send = $ads . '/' . $conv;
	?>
<script>
(function(){
  var fired=false;
  function agLead(){ if(typeof gtag!=='function')return; try{gtag('event','conversion',{'send_to':'<?php echo esc_js( $send ); ?>'});gtag('event','generate_lead',{'currency':'EUR','value':1});}catch(e){} }
  // Envoi de formulaire de contact
  document.addEventListener('submit',function(e){
    var f=e.target; if(!f||!f.matches)return;
    var path=(location.pathname||'').toLowerCase();
    if(f.matches('.wpcf7-form, form.ag-form, #ag-contact-form, form[action*="contact"]') || path.indexOf('contact')>-1){ agLead(); }
  },true);
  // Clic téléphone / email
  document.addEventListener('click',function(e){
    var a=e.target.closest?e.target.closest('a[href^="tel:"],a[href^="mailto:"]'):null;
    if(a){ agLead(); }
  },true);
})();
</script>
	<?php
}, 99 );
add_action( 'wp_head', 'ag_output_gtag', 1 );      // pages publiques
add_action( 'login_head', 'ag_output_gtag', 1 );   // wp-login.php (couverture balise « propre »)

// ── 8c. Google AdSense (balise de validation + pubs) ───────────────
add_action( 'wp_head', function () {
    $pub = trim( (string) apply_filters( 'ag_adsense_pub', get_option( 'ag_adsense_pub', 'ca-pub-4272988112057548' ) ) );
    if ( '' === $pub ) return;
    if ( 0 !== strpos( $pub, 'ca-pub-' ) ) $pub = 'ca-pub-' . preg_replace( '/[^0-9]/', '', $pub );
    // NB : AdSense chargé en async « normal » (pas différé au 1er geste). Différer
    // ce script fait injecter les pubs auto APRÈS stabilisation de la page → gros
    // CLS (constaté le 05/08). async classique = les emplacements sont réservés tôt.
    echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr( $pub ) . '" crossorigin="anonymous"></script>' . "\n";
}, 2 );

// ── 8c2. ads.txt (exigé par Google AdSense) ───────────────────────
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    if ( 'ads.txt' !== $path ) return;
    $pub = trim( (string) apply_filters( 'ag_adsense_pub', get_option( 'ag_adsense_pub', 'ca-pub-4272988112057548' ) ) );
    $num = preg_replace( '/[^0-9]/', '', $pub ); // ads.txt utilise "pub-XXXX" (sans ca-)
    if ( ob_get_level() ) { while ( ob_get_level() ) ob_end_clean(); }
    header( 'Content-Type: text/plain; charset=UTF-8' );
    status_header( 200 );
    if ( $num ) echo 'google.com, pub-' . $num . ', DIRECT, f08c47fec0942fa0' . "\n";
    exit;
}, 0 );

// ── 9. JSON-LD Structured Data (SEO) ────────────────────────────
add_action( 'wp_head', function () {
    $site_url  = home_url('/');
    $logo_url  = '';
    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    foreach ( array('jpg','jpeg','png','webp') as $ext ) {
        if ( file_exists( $dir . 'logo.' . $ext ) ) { $logo_url = $uri . 'logo.' . $ext; break; }
    }

    // Organization schema (all pages)
    $org = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Alliance Groupe',
        'url' => $site_url,
        'logo' => $logo_url,
        'description' => 'Studio web indépendant à Nantes : audit de sécurité, création de sites et maintenance.',
        'telephone' => '+33744829516',
        'email' => 'contact@alliancegroupe-inc.com',
        'address' => array(
            array( '@type' => 'PostalAddress', 'addressLocality' => 'Naples', 'addressCountry' => 'IT' ),
            array( '@type' => 'PostalAddress', 'addressLocality' => 'Nantes', 'addressCountry' => 'FR' ),
            array( '@type' => 'PostalAddress', 'addressLocality' => 'Marrakech', 'addressCountry' => 'MA' ),
        ),
        'sameAs' => array( $site_url ),
        'founder' => array( '@type' => 'Person', 'name' => 'Fabrizio' ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

    // WebSite schema with search (homepage)
    if ( is_front_page() ) {
        $website = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Alliance Groupe',
            'url' => $site_url,
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => $site_url . '?s={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // BreadcrumbList (single posts + pages)
    if ( is_single() ) {
        $breadcrumb = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $site_url ),
                array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Articles', 'item' => $site_url . 'articles/' ),
                array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title() ),
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    } elseif ( is_page() ) {
        $breadcrumb = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $site_url ),
                array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() ),
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // Article schema enrichi (single posts)
    if ( is_single() ) {
        $cats = get_the_category();
        $article = array(
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'description'   => has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : wp_trim_words( wp_strip_all_tags( get_the_content() ), 25 ),
            'datePublished' => get_the_date( 'c' ),
            'dateModified'  => get_the_modified_date( 'c' ),
            'author'        => array( '@type' => 'Organization', 'name' => 'Alliance Groupe' ),
            'publisher'     => array(
                '@type' => 'Organization',
                'name'  => 'Alliance Groupe',
                'logo'  => array( '@type' => 'ImageObject', 'url' => $logo_url ),
            ),
            'mainEntityOfPage' => get_permalink(),
            'articleSection'   => $cats ? $cats[0]->name : 'Blog',
        );
        if ( has_post_thumbnail() ) {
            $article['image'] = array(
                '@type'  => 'ImageObject',
                'url'    => get_the_post_thumbnail_url( null, 'large' ),
                'width'  => 1200,
                'height' => 630,
            );
        }
        echo '<script type="application/ld+json">' . wp_json_encode( $article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // Service schema (pages de services)
    if ( is_page() ) {
        $service_map = array(
            'service-creation-web' => array( 'Création Web & Sites WordPress', 'Création de sites vitrines et e-commerce performants sur WordPress.' ),
            'service-seo'          => array( 'SEO & Référencement Naturel', 'Stratégie SEO complète pour dominer Google en local et national.' ),
            'service-ia'           => array( 'IA & Automatisation', 'Chatbots, workflows et automatisations IA pour gagner du temps.' ),
            'service-publicite'    => array( 'Publicité Digitale', 'Campagnes Google Ads et Meta Ads optimisées pour le ROI.' ),
            'service-branding'     => array( 'Branding & Identité Visuelle', 'Logos, chartes graphiques et identités visuelles premium.' ),
            'service-conseil'      => array( 'Conseil Stratégique Digital', 'Audit et accompagnement stratégique pour votre transformation digitale.' ),
        );
        $slug = get_post_field( 'post_name' );
        if ( isset( $service_map[ $slug ] ) ) {
            $svc = array(
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => $service_map[ $slug ][0],
                'description' => $service_map[ $slug ][1],
                'url'         => get_permalink(),
                'provider'    => array( '@type' => 'Organization', 'name' => 'Alliance Groupe', 'url' => $site_url ),
                'areaServed'  => array( '@type' => 'Country', 'name' => 'France' ),
            );
            echo '<script type="application/ld+json">' . wp_json_encode( $svc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }

    // LocalBusiness schema (front page — SEO local)
    if ( is_front_page() ) {
        $offices = array(
            array( 'Alliance Groupe — Nantes', 'Nantes', 'Pays de la Loire', 'FR', '+33744829516', 47.2173, -1.5534 ),
            array( 'Alliance Groupe — Naples', 'Naples', 'Campania', 'IT', '+33744829516', 40.8518, 14.2681 ),
            array( 'Alliance Groupe — Marrakech', 'Marrakech', 'Marrakech-Safi', 'MA', '+33744829516', 31.6295, -8.0088 ),
        );
        foreach ( $offices as $o ) {
            $lb = array(
                '@context'  => 'https://schema.org',
                '@type'     => 'LocalBusiness',
                'name'      => $o[0],
                'url'       => $site_url,
                'telephone' => $o[4],
                'email'     => 'contact@alliancegroupe-inc.com',
                'address'   => array(
                    '@type'            => 'PostalAddress',
                    'addressLocality'  => $o[1],
                    'addressRegion'    => $o[2],
                    'addressCountry'   => $o[3],
                ),
                'geo' => array( '@type' => 'GeoCoordinates', 'latitude' => $o[5], 'longitude' => $o[6] ),
            );
            echo '<script type="application/ld+json">' . wp_json_encode( $lb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }
}, 5 );

// ── 10. Save template download leads ────────────────────────────
add_action( 'wp_ajax_ag_save_lead', 'ag_save_lead' );
add_action( 'wp_ajax_nopriv_ag_save_lead', 'ag_save_lead' );

if ( ! function_exists( 'ag_save_lead' ) ) {
    function ag_save_lead() {
        check_ajax_referer( 'ag_lead_nonce', 'ag_lead_nonce' );

        $name     = sanitize_text_field( isset( $_POST['name'] ) ? $_POST['name'] : '' );
        $email    = sanitize_email( isset( $_POST['email'] ) ? $_POST['email'] : '' );
        $phone    = sanitize_text_field( isset( $_POST['phone'] ) ? $_POST['phone'] : '' );
        $template = sanitize_text_field( isset( $_POST['template'] ) ? $_POST['template'] : '' );

        if ( empty( $name ) || empty( $email ) ) {
            wp_send_json_error( 'Champs requis manquants.' );
        }

        $leads = get_option( 'ag_template_leads', array() );
        $leads[] = array(
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'template' => $template,
            'date'     => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_template_leads', $leads );

        // Route AUSSI le lead dans le CRM Prospection, pour le retrouver au même
        // endroit que tous les autres (avant, ag_template_leads n'était affiché
        // nulle part → leads « invisibles » dans l'admin).
        if ( function_exists( 'ag_prospect_add_record' ) ) {
            ag_prospect_add_record( array(
                'name'   => $name,
                'email'  => $email,
                'phone'  => $phone,
                'status' => 'nouveau',
                'source' => 'template-' . ( $template ? $template : 'metier' ),
                'notes'  => 'A demandé le template « ' . ( $template ? $template : '?' ) . ' » (téléchargement).',
            ) );
        }

        wp_mail(
            'contact@alliancegroupe-inc.com',
            'Nouveau lead template : ' . $name,
            "Nom : $name\nEmail : $email\nTel : $phone\nTemplate : $template\nDate : " . current_time( 'd/m/Y H:i' )
        );
        if ( function_exists( 'ag_calendar_notify' ) ) {
            ag_calendar_notify( '📩 Nouveau message client : ' . $name, "Template : $template\nEmail : $email\nTél : $phone" );
        }
        if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '📩 Nouveau message client : ' . $name . ( $template ? ' (' . $template . ')' : '' ) );

        wp_send_json_success();
    }
}

// Migration ponctuelle : fait remonter les anciens leads « template » (dont
// Maurice) dans le CRM Prospection. ag_prospect_add_record déduplique (nom/tél),
// donc l'import est sûr. Idempotent via le drapeau ag_tpl_leads_migrated_v1.
add_action( 'init', function () {
    if ( get_option( 'ag_tpl_leads_migrated_v1' ) ) { return; }
    if ( function_exists( 'ag_prospect_add_record' ) ) {
        foreach ( (array) get_option( 'ag_template_leads', array() ) as $l ) {
            if ( empty( $l['name'] ) ) { continue; }
            ag_prospect_add_record( array(
                'name'   => $l['name'],
                'email'  => isset( $l['email'] ) ? $l['email'] : '',
                'phone'  => isset( $l['phone'] ) ? $l['phone'] : '',
                'status' => 'nouveau',
                'source' => 'template-' . ( ! empty( $l['template'] ) ? $l['template'] : 'metier' ),
                'notes'  => 'A demandé le template « ' . ( isset( $l['template'] ) ? $l['template'] : '?' ) . ' » (import).',
            ) );
        }
    }
    update_option( 'ag_tpl_leads_migrated_v1', 1 );
}, 20 );

// ── 11. Questions Flash submission (post-paiement) ────────────────
add_action( 'admin_post_nopriv_ag_submit_question', 'ag_submit_question' );
add_action( 'admin_post_ag_submit_question', 'ag_submit_question' );

if ( ! function_exists( 'ag_submit_question' ) ) {
    function ag_submit_question() {
        if ( ! isset( $_POST['ag_question_nonce'] ) || ! wp_verify_nonce( $_POST['ag_question_nonce'], 'ag_question_nonce' ) ) {
            wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        }

        $name     = sanitize_text_field( isset( $_POST['name'] )     ? $_POST['name']     : '' );
        $email    = sanitize_email(      isset( $_POST['email'] )    ? $_POST['email']    : '' );
        $activity = sanitize_text_field( isset( $_POST['activity'] ) ? $_POST['activity'] : '' );
        $question = sanitize_textarea_field( isset( $_POST['question'] ) ? $_POST['question'] : '' );
        $context  = sanitize_textarea_field( isset( $_POST['context'] )  ? $_POST['context']  : '' );
        $pack     = sanitize_key(        isset( $_POST['pack'] )     ? $_POST['pack']     : '' );

        if ( empty( $name ) || empty( $email ) || empty( $question ) ) {
            wp_die( 'Merci de remplir nom, email et question.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        }
        if ( ! is_email( $email ) ) {
            wp_die( 'Email invalide.', 'Erreur', array( 'response' => 400, 'back_link' => true ) );
        }

        $pack_labels = array(
            'single' => '1 Question Flash (45€)',
            'pack'   => 'Pack 3 Questions (120€)',
            'sub'    => 'Abonnement Expert (199€/mois)',
        );
        $pack_label = isset( $pack_labels[ $pack ] ) ? $pack_labels[ $pack ] : 'Non précisé';

        // Save to DB
        $questions = get_option( 'ag_questions_submitted', array() );
        $questions[] = array(
            'name'     => $name,
            'email'    => $email,
            'activity' => $activity,
            'question' => $question,
            'context'  => $context,
            'pack'     => $pack,
            'date'     => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_questions_submitted', $questions );

        // Email to Fabrizio
        $admin_subject = '💬 Nouvelle Question Flash : ' . $name . ' (' . $pack_label . ')';
        $admin_body    = "Nouvelle Question Flash reçue\n"
                       . "================================\n\n"
                       . "Pack : $pack_label\n"
                       . "Nom : $name\n"
                       . "Email : $email\n"
                       . "Activité : $activity\n\n"
                       . "-- Question --\n$question\n\n"
                       . "-- Contexte --\n$context\n\n"
                       . "Reçue le : " . current_time( 'd/m/Y H:i' ) . "\n"
                       . "Délai de réponse attendu : 48h ouvrées\n";
        wp_mail( 'contact@alliancegroupe-inc.com', $admin_subject, $admin_body );
        if ( function_exists( 'ag_calendar_notify' ) ) {
            ag_calendar_notify( '💬 Question Flash : ' . $name . ' (' . $pack_label . ')', "Nom : $name\nEmail : $email\nActivité : $activity\nQuestion : $question" );
        }
        if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '💬 Question Flash : ' . $name . ' (' . $pack_label . ')' );

        // Confirmation to the buyer
        $client_subject = 'Votre Question Flash a bien été reçue — Alliance Groupe';
        $client_body    = "Bonjour $name,\n\n"
                       . "Merci ! Votre question a bien été reçue et nous travaillons déjà dessus.\n\n"
                       . "Pack : $pack_label\n"
                       . "Question posée :\n$question\n\n"
                       . "Vous recevrez une analyse écrite détaillée à cette adresse ($email) sous 48h ouvrées.\n\n"
                       . "Si vous avez besoin d'ajouter du contexte entre-temps, répondez simplement à cet email.\n\n"
                       . "À très vite,\nFabrizio — Alliance Groupe\n"
                       . "contact@alliancegroupe-inc.com\n"
                       . "07.44.82.95.16\n";
        $headers = array( 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' );
        wp_mail( $email, $client_subject, $client_body, $headers );

        // Redirect back with success
        wp_safe_redirect( add_query_arg( array( 'question_sent' => '1' ), home_url( '/questions-flash' ) ) );
        exit;
    }
}

// ── 11a2. Programme Racines — candidatures ──────────────────────
add_action( 'admin_post_nopriv_ag_submit_racines', 'ag_submit_racines' );
add_action( 'admin_post_ag_submit_racines', 'ag_submit_racines' );

if ( ! function_exists( 'ag_submit_racines' ) ) {
    function ag_submit_racines() {
        if ( ! isset( $_POST['ag_racines_nonce'] ) || ! wp_verify_nonce( $_POST['ag_racines_nonce'], 'ag_racines_nonce' ) ) {
            wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        }

        $name    = sanitize_text_field(     isset( $_POST['name'] )    ? $_POST['name']    : '' );
        $email   = sanitize_email(          isset( $_POST['email'] )   ? $_POST['email']   : '' );
        $phone   = sanitize_text_field(     isset( $_POST['phone'] )   ? $_POST['phone']   : '' );
        $city    = sanitize_text_field(     isset( $_POST['city'] )    ? $_POST['city']    : '' );
        $project = sanitize_textarea_field( isset( $_POST['project'] ) ? $_POST['project'] : '' );
        $why     = sanitize_textarea_field( isset( $_POST['why'] )     ? $_POST['why']     : '' );

        if ( empty( $name ) || empty( $email ) || empty( $project ) ) {
            wp_die( 'Merci de remplir au minimum nom, email et projet.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        }
        if ( ! is_email( $email ) ) {
            wp_die( 'Email invalide.', 'Erreur', array( 'response' => 400, 'back_link' => true ) );
        }

        // Save to DB
        $apps = get_option( 'ag_racines_applications', array() );
        if ( ! is_array( $apps ) ) $apps = array();
        $apps[] = array(
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'city'    => $city,
            'project' => $project,
            'why'     => $why,
            'date'    => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_racines_applications', $apps );

        // Notify admin
        $admin_body  = "Nouvelle candidature Programme Racines\n\n";
        $admin_body .= "Nom : $name\nEmail : $email\nTel : $phone\nVille/Quartier : $city\n\n";
        $admin_body .= "Projet :\n$project\n\nMotivation :\n$why\n\n";
        $admin_body .= 'Date : ' . current_time( 'd/m/Y H:i' );
        wp_mail( 'contact@alliancegroupe-inc.com', 'Candidature Programme Racines : ' . $name, $admin_body );

        // Confirmation candidat
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        $client_body  = "Bonjour $name,\n\n";
        $client_body .= "Merci d'avoir candidaté au Programme Racines d'Alliance Groupe. 🌱\n\n";
        $client_body .= "On lit chaque candidature avec attention. Si ton projet correspond au programme, ";
        $client_body .= "on te recontacte sous 7 jours pour un premier échange.\n\n";
        $client_body .= "En attendant, prépare bien ton idée — c'est ton énergie qui fera la différence.\n\n";
        $client_body .= "L'équipe Alliance Groupe\ncontact@alliancegroupe-inc.com";
        wp_mail( $email, 'Ta candidature Programme Racines est bien reçue 🌱', $client_body, $headers );

        wp_safe_redirect( add_query_arg( array( 'racines' => 'ok' ), home_url( '/programme-racines' ) ) . '#racines-candidature' );
        exit;
    }
}

// ── 11a3. Sites Express — brief projet (post-paiement) ──────────
add_action( 'admin_post_nopriv_ag_submit_brief', 'ag_submit_brief' );
add_action( 'admin_post_ag_submit_brief', 'ag_submit_brief' );

if ( ! function_exists( 'ag_submit_brief' ) ) {
    function ag_submit_brief() {
        if ( ! isset( $_POST['ag_brief_nonce'] ) || ! wp_verify_nonce( $_POST['ag_brief_nonce'], 'ag_brief_nonce' ) ) {
            wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        }
        $pack     = sanitize_text_field(     $_POST['pack']     ?? '' );
        $business = sanitize_text_field(     $_POST['business'] ?? '' );
        $name     = sanitize_text_field(     $_POST['name']     ?? '' );
        $email    = sanitize_email(          $_POST['email']    ?? '' );
        $phone    = sanitize_text_field(     $_POST['phone']    ?? '' );
        $sector   = sanitize_text_field(     $_POST['sector']   ?? '' );
        $domain   = sanitize_text_field(     $_POST['domain']   ?? '' );
        $content  = sanitize_textarea_field( $_POST['content']  ?? '' );
        $inspi    = sanitize_textarea_field( $_POST['inspiration'] ?? '' );

        if ( empty( $name ) || ! is_email( $email ) || empty( $business ) ) {
            wp_die( 'Merci d\'indiquer ton nom, email et le nom de ton activité.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        }
        if ( empty( $_POST['cgv'] ) ) {
            wp_die( 'Tu dois accepter le contrat de prestation / les CGV pour continuer.', 'Contrat requis', array( 'response' => 400, 'back_link' => true ) );
        }
        $pays = sanitize_text_field( wp_unslash( $_POST['pays'] ?? 'France' ) );
        if ( function_exists( 'ag_countries' ) && ! in_array( $pays, ag_countries(), true ) ) $pays = 'France';
        $cgv  = array(
            'accepted'  => 1,
            'pays'      => $pays,
            'ip'        => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
            'contract'  => home_url( '/contrat-client?pays=' . rawurlencode( $pays ) ),
            'date'      => current_time( 'd/m/Y H:i' ),
        );

        $briefs = get_option( 'ag_express_briefs', array() );
        if ( ! is_array( $briefs ) ) $briefs = array();
        $briefs[] = compact( 'pack', 'business', 'name', 'email', 'phone', 'sector', 'domain', 'content', 'inspi' )
            + array( 'date' => current_time( 'd/m/Y H:i' ), 'cgv' => $cgv );
        update_option( 'ag_express_briefs', $briefs );

        // Crée (ou retrouve) le compte client pour son espace réservé,
        // et déclenche l'attribution auto à l'ambassadeur (si lien de parrainage).
        do_action( 'ag_client_brief_submitted', $email, $name, $pack );

        $body  = "Nouveau brief Site Express\n\n";
        $body .= "Pack : $pack\nActivité : $business\nContact : $name <$email> $phone\n";
        $body .= "Secteur : $sector\nNom de domaine souhaité : $domain\n\n";
        $body .= "Contenu/textes :\n$content\n\nInspirations :\n$inspi\n\nDate : " . current_time( 'd/m/Y H:i' );
        wp_mail( 'contact@alliancegroupe-inc.com', 'Brief Site Express : ' . $business, $body );

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        $c  = "Bonjour $name,\n\nMerci ! On a bien reçu le brief de ton site ($business).\n";
        $c .= "On démarre la production et on t'envoie une première version rapidement, ";
        $c .= "avec une vidéo de présentation. Tout se fait par écrit, sans rendez-vous.\n\n";
        $c .= "----------------------------------------\n";
        $c .= "👉 GARDE TON SITE AU TOP CHAQUE MOIS\n";
        $c .= "Hébergement, sécurité, sauvegardes, retouches et référencement :\n";
        $c .= "nos forfaits maintenance à partir de 29€/mois.\n";
        $c .= home_url( '/sites-express#maintenance' ) . "\n";
        $c .= "----------------------------------------\n\n";
        $c .= "L'équipe Alliance Groupe\ncontact@alliancegroupe-inc.com";
        wp_mail( $email, 'On a reçu le brief de ton site 🚀', $c, $headers );

        wp_safe_redirect( add_query_arg( array( 'brief' => 'ok' ), home_url( '/sites-express' ) ) . '#brief' );
        exit;
    }
}

// ── 11a4. Admin : voir les briefs Sites Express ─────────────────
add_action( 'admin_menu', function () {
    add_menu_page( 'Sites Express', 'Sites Express', 'manage_options', 'ag-express-briefs', 'ag_render_express_briefs', 'dashicons-laptop', 29 );
} );
if ( ! function_exists( 'ag_render_express_briefs' ) ) {
    function ag_render_express_briefs() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $briefs = array_reverse( get_option( 'ag_express_briefs', array() ) );
        echo '<div class="wrap"><h1>Briefs Sites Express</h1>';
        if ( empty( $briefs ) ) { echo '<p>Aucun brief pour le moment.</p></div>'; return; }
        echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Pack</th><th>Activité</th><th>Contact</th><th>Secteur</th><th>Domaine</th><th>Contenu</th><th>Inspi</th></tr></thead><tbody>';
        foreach ( $briefs as $b ) {
            echo '<tr><td>' . esc_html( $b['date'] ?? '' ) . '</td><td>' . esc_html( $b['pack'] ?? '' ) . '</td>';
            echo '<td><strong>' . esc_html( $b['business'] ?? '' ) . '</strong></td>';
            echo '<td>' . esc_html( $b['name'] ?? '' ) . '<br><a href="mailto:' . esc_attr( $b['email'] ?? '' ) . '">' . esc_html( $b['email'] ?? '' ) . '</a><br>' . esc_html( $b['phone'] ?? '' ) . '</td>';
            echo '<td>' . esc_html( $b['sector'] ?? '' ) . '</td><td>' . esc_html( $b['domain'] ?? '' ) . '</td>';
            echo '<td style="max-width:280px;white-space:normal;">' . esc_html( $b['content'] ?? '' ) . '</td>';
            echo '<td style="max-width:200px;white-space:normal;">' . esc_html( $b['inspi'] ?? '' ) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}

// ── 11b. Admin page to view submitted questions ─────────────────
add_action( 'admin_menu', function () {
    add_menu_page(
        'Questions Flash',
        'Questions Flash',
        'manage_options',
        'ag-questions',
        'ag_render_questions_page',
        'dashicons-format-chat',
        27
    );
} );

if ( ! function_exists( 'ag_render_questions_page' ) ) {
    function ag_render_questions_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $questions = get_option( 'ag_questions_submitted', array() );
        echo '<div class="wrap"><h1>Questions Flash reçues</h1>';
        if ( empty( $questions ) ) {
            echo '<p>Aucune question pour le moment.</p></div>';
            return;
        }
        $questions = array_reverse( $questions );
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>Date</th><th>Nom</th><th>Email</th><th>Pack</th><th>Activité</th><th>Question</th><th>Contexte</th>';
        echo '</tr></thead><tbody>';
        foreach ( $questions as $q ) {
            echo '<tr>';
            echo '<td>' . esc_html( isset($q['date']) ? $q['date'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($q['name']) ? $q['name'] : '' ) . '</td>';
            echo '<td><a href="mailto:' . esc_attr( isset($q['email']) ? $q['email'] : '' ) . '">' . esc_html( isset($q['email']) ? $q['email'] : '' ) . '</a></td>';
            echo '<td>' . esc_html( isset($q['pack']) ? $q['pack'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($q['activity']) ? $q['activity'] : '' ) . '</td>';
            echo '<td style="max-width:300px;white-space:normal;">' . esc_html( isset($q['question']) ? $q['question'] : '' ) . '</td>';
            echo '<td style="max-width:200px;white-space:normal;">' . esc_html( isset($q['context']) ? $q['context'] : '' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}


// ── Liste mondiale des pays (noms FR) pour les contrats/sélecteurs ──
if ( ! function_exists( 'ag_countries' ) ) {
    function ag_countries() {
        return array(
            'France','Belgique','Suisse','Luxembourg','Monaco','Canada','Italie','Espagne','Portugal','Allemagne',
            'Royaume-Uni','Irlande','Pays-Bas','Autriche','Danemark','Suède','Norvège','Finlande','Islande','Pologne',
            'République tchèque','Slovaquie','Hongrie','Roumanie','Bulgarie','Grèce','Croatie','Slovénie','Serbie','Bosnie-Herzégovine',
            'Albanie','Macédoine du Nord','Monténégro','Kosovo','Lituanie','Lettonie','Estonie','Ukraine','Biélorussie','Moldavie',
            'Russie','Turquie','Chypre','Malte','Andorre','Saint-Marin','Liechtenstein',
            'Maroc','Algérie','Tunisie','Libye','Égypte','Mauritanie','Sénégal','Mali','Burkina Faso','Niger',
            'Côte d\'Ivoire','Guinée','Guinée-Bissau','Sierra Leone','Liberia','Ghana','Togo','Bénin','Nigeria','Cameroun',
            'Tchad','Centrafrique','Gabon','Congo','République démocratique du Congo','Angola','Soudan','Soudan du Sud','Éthiopie','Érythrée',
            'Djibouti','Somalie','Kenya','Ouganda','Rwanda','Burundi','Tanzanie','Mozambique','Madagascar','Maurice',
            'Comores','Seychelles','Zambie','Zimbabwe','Malawi','Namibie','Botswana','Afrique du Sud','Lesotho','Eswatini',
            'Cap-Vert','Gambie','São Tomé-et-Principe','Guinée équatoriale',
            'États-Unis','Mexique','Guatemala','Belize','Honduras','Salvador','Nicaragua','Costa Rica','Panama','Cuba',
            'Haïti','République dominicaine','Jamaïque','Trinité-et-Tobago','Bahamas','Barbade',
            'Colombie','Venezuela','Équateur','Pérou','Bolivie','Brésil','Paraguay','Uruguay','Argentine','Chili','Guyana','Suriname',
            'Chine','Japon','Corée du Sud','Corée du Nord','Mongolie','Taïwan','Hong Kong','Inde','Pakistan','Bangladesh',
            'Sri Lanka','Népal','Bhoutan','Birmanie','Thaïlande','Laos','Cambodge','Vietnam','Malaisie','Singapour',
            'Indonésie','Philippines','Brunei','Timor oriental',
            'Arabie saoudite','Émirats arabes unis','Qatar','Koweït','Bahreïn','Oman','Yémen','Irak','Iran','Syrie',
            'Liban','Jordanie','Israël','Palestine','Afghanistan','Kazakhstan','Ouzbékistan','Turkménistan','Kirghizistan','Tadjikistan',
            'Azerbaïdjan','Arménie','Géorgie',
            'Australie','Nouvelle-Zélande','Fidji','Papouasie-Nouvelle-Guinée','Nouvelle-Calédonie','Polynésie française',
            'Guadeloupe','Martinique','Guyane','La Réunion','Mayotte','Autre',
        );
    }
}

// ── Demande de devis SUR-MESURE (configurateur premium) ─────────
add_action( 'admin_post_nopriv_ag_sur_mesure_submit', 'ag_sur_mesure_submit' );
add_action( 'admin_post_ag_sur_mesure_submit', 'ag_sur_mesure_submit' );
if ( ! function_exists( 'ag_sur_mesure_submit' ) ) {
    function ag_sur_mesure_submit() {
        if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_sur_mesure' ) ) wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        $name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        if ( empty( $name ) || ! is_email( $email ) ) wp_die( 'Nom et email requis.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        if ( empty( $_POST['cgv'] ) ) wp_die( 'Tu dois accepter le contrat / les CGV.', 'Contrat requis', array( 'response' => 400, 'back_link' => true ) );
        $feat = array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['features'] ?? array() ) );
        $req  = array(
            'name'        => $name,
            'email'       => $email,
            'phone'       => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
            'business'    => sanitize_text_field( wp_unslash( $_POST['business'] ?? '' ) ),
            'type'        => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
            'style'       => sanitize_text_field( wp_unslash( $_POST['style'] ?? '' ) ),
            'couleurs'    => sanitize_text_field( wp_unslash( $_POST['couleurs'] ?? '' ) ),
            'features'    => implode( ', ', $feat ),
            'pages'       => sanitize_text_field( wp_unslash( $_POST['pages'] ?? '' ) ),
            'budget'      => sanitize_text_field( wp_unslash( $_POST['budget'] ?? '' ) ),
            'delai'       => sanitize_text_field( wp_unslash( $_POST['delai'] ?? '' ) ),
            'description' => sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ),
            'pays'        => sanitize_text_field( wp_unslash( $_POST['pays'] ?? 'France' ) ),
            'date'        => current_time( 'd/m/Y H:i' ),
            'ip'          => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
        );
        $list = (array) get_option( 'ag_sur_mesure_requests', array() );
        $list[] = $req;
        update_option( 'ag_sur_mesure_requests', array_slice( $list, -500 ) );

        $body  = "Nouvelle demande de devis SUR-MESURE\n\n";
        $body .= "Nom : {$req['name']}\nEmail : {$req['email']}\nTél : {$req['phone']}\nEntreprise : {$req['business']}\nPays : {$req['pays']}\n\n";
        $body .= "Type : {$req['type']}\nStyle : {$req['style']}\nCouleurs : {$req['couleurs']}\nFonctionnalités : {$req['features']}\nPages : {$req['pages']}\nBudget : {$req['budget']}\nDélai : {$req['delai']}\n\nDescription :\n{$req['description']}\n\nDate : {$req['date']}";
        wp_mail( apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' ), '✦ Devis sur-mesure : ' . $name . ' (' . $req['budget'] . ')', $body );
        if ( function_exists( 'ag_calendar_notify' ) ) {
            ag_calendar_notify( '✦ Demande de devis sur-mesure : ' . $name, $name . ' — ' . $req['type'] . ' · budget ' . $req['budget'] . "\nEmail : " . $req['email'] . "\nTél : " . $req['phone'] );
        } elseif ( function_exists( 'ag_push' ) ) {
            ag_push( '✦ Demande de devis sur-mesure', $name . ' — ' . $req['type'] . ' · budget ' . $req['budget'] . ' · ' . $req['email'] );
        }
        if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '✦ Demande de devis sur-mesure : ' . $name . ' (budget ' . $req['budget'] . ')' );

        wp_safe_redirect( home_url( '/sur-mesure?envoye=1#configurateur' ) );
        exit;
    }
}

// ── Galerie d'aperçus par template métier ─────────────────────────────
//    Lit assets/images/templates/<slug>/*.{jpg,png,webp} (sous-dossier par
//    métier). Retourne une liste [url,label] triée par nom de fichier.
//    La 1re image sert d'aperçu sur la carte de la grille Templates.
if ( ! function_exists( 'ag_template_gallery_images' ) ) {
    function ag_template_gallery_images( $slug ) {
        $slug = sanitize_key( $slug );
        if ( '' === $slug ) return array();
        $dir = get_stylesheet_directory() . '/assets/images/templates/' . $slug;
        $url = get_stylesheet_directory_uri() . '/assets/images/templates/' . $slug;
        if ( ! is_dir( $dir ) ) return array();
        $files = glob( $dir . '/*.{jpg,jpeg,png,webp,JPG,PNG,WEBP}', GLOB_BRACE );
        if ( ! $files ) return array();
        // On n'affiche QUE les fichiers aux noms propres (minuscules + chiffres + tirets).
        // Tout fichier contenant majuscules / underscores / espaces / apostrophes est
        // ignoré : ce sont typiquement des restes d'upload non nettoyés (le SYNC GitHub
        // ne supprime pas les orphelins), ce qui évite les doublons dans la galerie.
        $files = array_filter( $files, function ( $f ) {
            return (bool) preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*\.[A-Za-z]+$/', basename( $f ) );
        } );
        if ( ! $files ) return array();
        sort( $files ); // ordre alphabétique stable (accueil-* d'abord)
        $out = array();
        foreach ( $files as $f ) {
            $name  = basename( $f );
            $label = preg_replace( '/\.[a-z]+$/i', '', $name );           // retire l'extension
            $label = preg_replace( '/^\d+[\s\-_]*/', '', $label );        // retire un préfixe d'ordre éventuel (1-, 02_, …)
            $label = trim( str_replace( array( '-', '_' ), ' ', $label ) );
            // Suffixe « jour »/« nuit » (mode clair/sombre des maquettes) → présentation soignée.
            if ( preg_match( '/^(.*?)\s+(jour|nuit)$/i', $label, $m ) ) {
                $label = ucfirst( trim( $m[1] ) ) . ' — ' . strtolower( $m[2] );
            } else {
                $label = ucfirst( $label );
            }
            $out[] = array(
                'url'   => $url . '/' . rawurlencode( $name ),
                'label' => $label,
            );
        }
        return $out;
    }
}

// ── Blocage public optionnel de certaines fiches template.
//    Par DÉFAUT : aucune fiche bloquée (les 6 fiches sont publiques).
//    Pour re-bloquer temporairement une fiche en travaux, hook le filtre
//    'ag_templates_blocked' et retourne ses slugs (ex. 'wordpress-coach').
add_action( 'template_redirect', function () {
    if ( current_user_can( 'manage_options' ) ) return; // toi (admin) vois tout
    $blocked = apply_filters( 'ag_templates_blocked', array() );
    if ( empty( $blocked ) ) return;
    foreach ( $blocked as $slug ) {
        if ( is_page( $slug ) ) { wp_safe_redirect( home_url( '/templates-wordpress' ), 302 ); exit; }
    }
} );

/* ── AG : mises à jour AUTOMATIQUES de tous les composants Alliance Groupe ──
 * Plus aucun update manuel : WordPress installe en arrière-plan toute MAJ d'un
 * thème ou plugin dont le slug commence par « ag- ». (Nécessite FS_METHOD
 * 'direct' sur l'hébergement pour écrire les fichiers sans demander de FTP.) */
if ( ! function_exists( 'ag_force_auto_updates' ) ) {
	function ag_force_auto_updates( $update, $item ) {
		$slug = ( is_object( $item ) && ! empty( $item->slug ) ) ? (string) $item->slug : '';
		if ( 0 !== strpos( $slug, 'ag-' ) ) {
			return $update;
		}
		// Pas d'auto-MAJ sur un site local/dev : le HTTPS y échoue souvent
		// (XAMPP sans bundle CA) → WordPress annule et restaure l'ancienne
		// version (« ça revient »), en boucle. Les vrais domaines clients OK.
		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( preg_replace( '/:\d+$/', '', (string) $_SERVER['HTTP_HOST'] ) ) : '';
		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true )
			|| ( function_exists( 'wp_get_environment_type' ) && in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) ) {
			return false;
		}
		return true;
	}
	add_filter( 'auto_update_plugin', 'ag_force_auto_updates', 10, 2 );
	add_filter( 'auto_update_theme',  'ag_force_auto_updates', 10, 2 );
	add_action( 'admin_init', function () {
		if ( ! wp_next_scheduled( 'wp_update_plugins' ) ) { wp_schedule_single_event( time() + 60, 'wp_update_plugins' ); }
		if ( ! wp_next_scheduled( 'wp_update_themes' ) )  { wp_schedule_single_event( time() + 60, 'wp_update_themes' ); }
	} );
}

// ── ACCUEIL CINEMATIQUE : la page d'accueil sert le template « scroll narratif »
//    (hero egerie + approche de la main, marquee, tableau allegorique, chapitres,
//    dissolution doree, offres, atelier, revelation du lion).
//    Pour revenir a l'ancien accueil : supprimer ce filtre (ou definir la
//    constante AG_ACCUEIL_CLASSIQUE a true dans wp-config.php).
add_filter( 'template_include', function ( $template ) {
    if ( ! is_front_page() || is_admin() ) {
        return $template;
    }
    if ( defined( 'AG_ACCUEIL_CLASSIQUE' ) && AG_ACCUEIL_CLASSIQUE ) {
        return $template;
    }
    $cine = get_stylesheet_directory() . '/templates/page-accueil-cinema.php';
    return file_exists( $cine ) ? $cine : $template;
}, 99 );
