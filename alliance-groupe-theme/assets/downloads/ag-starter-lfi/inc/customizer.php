<?php
/**
 * Customizer pour AG Starter LFI.
 * Tous les textes par defaut sont des placeholders en crochets — a
 * personnaliser pour chaque mouvement (LFI ou autre).
 *
 * @package AG_Starter_LFI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ag_lfi_customize( $wp_customize ) {
	$wp_customize->add_panel( 'ag_lfi_panel', array(
		'title'    => __( 'Mouvement militant', 'ag-starter-lfi' ),
		'priority' => 30,
	) );

	// Section : identite
	$wp_customize->add_section( 'ag_lfi_identity', array(
		'title' => __( 'Identité du mouvement', 'ag-starter-lfi' ),
		'panel' => 'ag_lfi_panel',
	) );
	$identity_fields = array(
		'ag_lfi_name'        => array( 'label' => 'Nom du mouvement',         'default' => '[Nom du mouvement]' ),
		'ag_lfi_slogan'      => array( 'label' => 'Slogan court (header)',    'default' => '[Slogan en quelques mots]' ),
		'ag_lfi_hero_title'  => array( 'label' => 'Titre hero',               'default' => '[Le grand titre de mobilisation]' ),
		'ag_lfi_hero_sub'    => array( 'label' => 'Sous-titre hero',          'default' => '[Description courte du combat]' ),
		'ag_lfi_cta_label'   => array( 'label' => 'Texte bouton principal',   'default' => 'Rejoindre le mouvement' ),
		'ag_lfi_cta_url'     => array( 'label' => 'URL bouton principal',     'default' => '#signer' ),
	);
	foreach ( $identity_fields as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f['default'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $f['label'],
			'section' => 'ag_lfi_identity',
			'type'    => 'text',
		) );
	}

	// Section : contact
	$wp_customize->add_section( 'ag_lfi_contact', array(
		'title' => __( 'Contact', 'ag-starter-lfi' ),
		'panel' => 'ag_lfi_panel',
	) );
	$contact_fields = array(
		'ag_lfi_address' => '[Adresse du local]',
		'ag_lfi_email'   => '[contact@mouvement.fr]',
		'ag_lfi_phone'   => '[01 00 00 00 00]',
	);
	foreach ( $contact_fields as $key => $default ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => str_replace( 'ag_lfi_', '', $key ),
			'section' => 'ag_lfi_contact',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'ag_lfi_customize' );
