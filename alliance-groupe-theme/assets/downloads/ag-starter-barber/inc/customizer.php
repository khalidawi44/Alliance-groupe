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
