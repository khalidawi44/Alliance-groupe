<?php
/**
 * Customizer pour AG Starter Association.
 * Tous les textes par defaut sont des placeholders en crochets — a
 * personnaliser pour chaque mouvement, parti, association, syndicat, etc.
 *
 * @package AG_Starter_Association
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ag_asso_customize( $wp_customize ) {
	$wp_customize->add_panel( 'ag_asso_panel', array(
		'title'    => __( 'Mouvement militant', 'ag-starter-association' ),
		'priority' => 30,
	) );

	// Section : identite
	$wp_customize->add_section( 'ag_asso_identity', array(
		'title' => __( 'Identité du mouvement', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$identity_fields = array(
		'ag_asso_name'        => array( 'label' => 'Nom du mouvement',         'default' => '[Nom du mouvement]' ),
		'ag_asso_slogan'      => array( 'label' => 'Slogan court (header)',    'default' => '[Slogan en quelques mots]' ),
		'ag_asso_hero_title'  => array( 'label' => 'Titre hero',               'default' => '[Le grand titre de mobilisation]' ),
		'ag_asso_hero_sub'    => array( 'label' => 'Sous-titre hero',          'default' => '[Description courte du combat]' ),
		'ag_asso_cta_label'   => array( 'label' => 'Texte bouton principal',   'default' => 'Rejoindre le mouvement' ),
		'ag_asso_cta_url'     => array( 'label' => 'URL bouton principal',     'default' => '#signer' ),
	);
	foreach ( $identity_fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_asso_identity',
			'type'    => 'text',
		) );
	}

	// Section : contact
	$wp_customize->add_section( 'ag_asso_contact', array(
		'title' => __( 'Contact', 'ag-starter-association' ),
		'panel' => 'ag_asso_panel',
	) );
	$contact_fields = array(
		'ag_asso_address' => '[Adresse du local]',
		'ag_asso_email'   => '[contact@mouvement.fr]',
		'ag_asso_phone'   => '[01 00 00 00 00]',
	);
	foreach ( $contact_fields as $key => $default ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => str_replace( 'ag_asso_', '', $key ),
			'section' => 'ag_asso_contact',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'ag_asso_customize' );
