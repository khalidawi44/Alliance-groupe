<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'customize_register', function ( $wp_customize ) {

    $wp_customize->add_section( 'ag_barber_general', array(
        'title' => esc_html__( '💈 Barbershop', 'ag-starter-barber' ),
        'priority' => 20,
    ) );

    // Hero title
    $wp_customize->add_setting( 'ag_barber_hero_title', array( 'default' => 'Votre coupe, sans rendez-vous.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_hero_title', array( 'label' => 'Titre hero', 'section' => 'ag_barber_general', 'type' => 'text' ) );

    $wp_customize->add_setting( 'ag_barber_hero_subtitle', array( 'default' => 'Sans attente inutile.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_hero_subtitle', array( 'label' => 'Sous-titre hero (italique doré)', 'section' => 'ag_barber_general', 'type' => 'text' ) );

    $wp_customize->add_setting( 'ag_barber_hero_text', array( 'default' => 'Scannez le QR code en vitrine, prenez votre ticket, et revenez quand c\'est votre tour.', 'sanitize_callback' => 'sanitize_textarea_field' ) );
    $wp_customize->add_control( 'ag_barber_hero_text', array( 'label' => 'Texte hero', 'section' => 'ag_barber_general', 'type' => 'textarea' ) );

    $wp_customize->add_setting( 'ag_barber_main_price', array( 'default' => '10€', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_main_price', array( 'label' => 'Prix principal affiché', 'section' => 'ag_barber_general', 'type' => 'text' ) );

    // Contact info
    $wp_customize->add_setting( 'ag_barber_address', array( 'default' => '12 Rue du Centre, 75001 Paris', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_address', array( 'label' => 'Adresse', 'section' => 'ag_barber_general', 'type' => 'text' ) );

    $wp_customize->add_setting( 'ag_barber_hours', array( 'default' => 'Lun-Sam : 9h-20h | Dim : fermé', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_hours', array( 'label' => 'Horaires', 'section' => 'ag_barber_general', 'type' => 'text' ) );

    $wp_customize->add_setting( 'ag_barber_phone', array( 'default' => '06 12 34 56 78', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_phone', array( 'label' => 'Téléphone', 'section' => 'ag_barber_general', 'type' => 'text' ) );

    // =====================================================================
    // Contenu accueil — textes (leads des sections)
    // =====================================================================
    $wp_customize->add_section( 'ag_barber_home_content', array(
        'title'       => esc_html__( 'Contenu accueil — textes', 'ag-starter-barber' ),
        'priority'    => 25,
        'description' => esc_html__( 'Personnalisez les phrases d\'introduction (sous les titres) de chaque section de l\'accueil.', 'ag-starter-barber' ),
    ) );
    $ag_barber_home_fields = array(
        // Hero
        'ag_barber_hero_price_suffix'  => array( 'label' => 'Hero — texte après le prix',         'default' => 'la coupe homme', 'type' => 'text' ),
        'ag_barber_hero_btn_primary'   => array( 'label' => 'Hero — bouton principal',           'default' => '📱 Prendre un ticket maintenant', 'type' => 'text' ),
        'ag_barber_hero_btn_secondary' => array( 'label' => 'Hero — bouton secondaire',          'default' => 'Voir les tarifs', 'type' => 'text' ),
        // File d'attente
        'ag_barber_queue_title_pre'    => array( 'label' => 'File d\'attente — début du titre',  'default' => 'File d\'attente', 'type' => 'text' ),
        'ag_barber_queue_title_em'     => array( 'label' => 'File d\'attente — mot en doré',     'default' => 'en direct', 'type' => 'text' ),
        'ag_barber_queue_lead'         => array( 'label' => 'File d\'attente — phrase d\'intro', 'default' => 'Mis à jour en temps réel. Scannez le QR code en vitrine ou cliquez ci-dessous.', 'type' => 'textarea' ),
        'ag_barber_queue_count_label'  => array( 'label' => 'File d\'attente — libellé compteur','default' => 'personne(s) en attente', 'type' => 'text' ),
        'ag_barber_queue_wait_label'   => array( 'label' => 'File d\'attente — libellé temps',   'default' => '⏱ Temps d\'attente estimé :', 'type' => 'text' ),
        'ag_barber_queue_next_label'   => array( 'label' => 'File d\'attente — libellé prochain','default' => 'prochain passage vers', 'type' => 'text' ),
        'ag_barber_queue_btn_label'    => array( 'label' => 'File d\'attente — bouton',          'default' => '📱 Rejoindre la file', 'type' => 'text' ),
        // Tarifs
        'ag_barber_services_title_pre' => array( 'label' => 'Tarifs — début du titre',           'default' => 'Nos', 'type' => 'text' ),
        'ag_barber_services_title_em'  => array( 'label' => 'Tarifs — mot en doré',              'default' => 'tarifs', 'type' => 'text' ),
        'ag_barber_services_lead'      => array( 'label' => 'Tarifs — phrase d\'intro',          'default' => 'Tarifs fixes, pas de surprise. Paiement en espèces ou carte bancaire.', 'type' => 'textarea' ),
        // Comment ça marche
        'ag_barber_howitworks_title_pre' => array( 'label' => 'Comment ça marche — début du titre', 'default' => 'Comment', 'type' => 'text' ),
        'ag_barber_howitworks_title_em'  => array( 'label' => 'Comment ça marche — mot en doré',    'default' => 'ça marche', 'type' => 'text' ),
        'ag_barber_howitworks_lead'      => array( 'label' => 'Comment ça marche — phrase d\'intro', 'default' => 'Plus besoin d\'attendre debout. 3 étapes, 30 secondes.', 'type' => 'textarea' ),
        'ag_barber_step1_title'          => array( 'label' => 'Étape 1 — titre',                  'default' => '1. Scannez le QR code', 'type' => 'text' ),
        'ag_barber_step1_text'           => array( 'label' => 'Étape 1 — texte',                  'default' => 'En vitrine du salon. Votre téléphone ouvre la page de réservation.', 'type' => 'textarea' ),
        'ag_barber_step2_title'          => array( 'label' => 'Étape 2 — titre',                  'default' => '2. Prenez votre ticket', 'type' => 'text' ),
        'ag_barber_step2_text'           => array( 'label' => 'Étape 2 — texte',                  'default' => 'Entrez votre prénom et choisissez votre prestation. Vous recevez une heure estimée.', 'type' => 'textarea' ),
        'ag_barber_step3_title'          => array( 'label' => 'Étape 3 — titre',                  'default' => '3. Revenez à l\'heure', 'type' => 'text' ),
        'ag_barber_step3_text'           => array( 'label' => 'Étape 3 — texte',                  'default' => 'Allez prendre un café, faites vos courses. Revenez quand c\'est votre tour.', 'type' => 'textarea' ),
    );
    foreach ( $ag_barber_home_fields as $ag_key => $ag_f ) {
        $ag_type = isset( $ag_f['type'] ) ? $ag_f['type'] : 'textarea';
        $wp_customize->add_setting( $ag_key, array(
            'default'           => $ag_f['default'],
            'sanitize_callback' => $ag_type === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field',
        ) );
        $wp_customize->add_control( $ag_key, array(
            'label'   => $ag_f['label'],
            'section' => 'ag_barber_home_content',
            'type'    => $ag_type,
        ) );
    }

    // Upgrade section
    $wp_customize->add_section( 'ag_barber_upgrade', array(
        'title' => esc_html__( '⭐ Améliorer mon thème', 'ag-starter-barber' ),
        'priority' => 200,
    ) );
    $wp_customize->add_setting( 'ag_barber_upgrade_info', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'ag_barber_upgrade_info', array(
        'label' => 'Packs Pro / Premium / Business',
        'section' => 'ag_barber_upgrade',
        'type' => 'hidden',
        'description' => '<p>Débloquez des fonctionnalités avancées :</p>'
            . '<ul style="margin:8px 0;padding-left:16px;">'
            . '<li><strong>Pro (49€)</strong> — Notifications SMS, statistiques avancées, multi-barbers</li>'
            . '<li><strong>Premium (99€)</strong> — Réservation en ligne, galerie Instagram, WooCommerce</li>'
            . '<li><strong>Business (149€)</strong> — White-label, multi-salons, session stratégique</li>'
            . '</ul>'
            . '<a href="https://alliancegroupe-inc.com/templates-wordpress?pack=pro" target="_blank" style="display:inline-block;background:#D4B45C;color:#000;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:8px;">Voir les packs →</a>',
    ) );
} );
