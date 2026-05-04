<?php
/**
 * Custom Post Types : combat, evenement, groupe-local.
 * Permet d'avoir des pages séparées propres pour chaque entité.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_CPT {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}
	public function register() {
		// Combats / Thématiques
		register_post_type( 'ag_combat', array(
			'labels' => array(
				'name'          => __( 'Combats', 'ag-fidelite-association' ),
				'singular_name' => __( 'Combat', 'ag-fidelite-association' ),
				'add_new_item'  => __( 'Ajouter un combat', 'ag-fidelite-association' ),
				'edit_item'     => __( 'Modifier le combat', 'ag-fidelite-association' ),
			),
			'public'             => true,
			'has_archive'        => 'combats',
			'rewrite'            => array( 'slug' => 'combat' ),
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'          => 'dashicons-megaphone',
			'show_in_rest'       => true,
		) );

		// Événements
		register_post_type( 'ag_evenement', array(
			'labels' => array(
				'name'          => __( 'Événements', 'ag-fidelite-association' ),
				'singular_name' => __( 'Événement', 'ag-fidelite-association' ),
				'add_new_item'  => __( 'Ajouter un événement', 'ag-fidelite-association' ),
			),
			'public'        => true,
			'has_archive'   => 'evenements',
			'rewrite'       => array( 'slug' => 'evenement' ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'     => 'dashicons-calendar-alt',
			'show_in_rest'  => true,
		) );

		// Groupes locaux
		register_post_type( 'ag_groupe', array(
			'labels' => array(
				'name'          => __( 'Groupes locaux', 'ag-fidelite-association' ),
				'singular_name' => __( 'Groupe local', 'ag-fidelite-association' ),
				'add_new_item'  => __( 'Ajouter un groupe', 'ag-fidelite-association' ),
			),
			'public'        => true,
			'has_archive'   => 'groupes',
			'rewrite'       => array( 'slug' => 'groupe' ),
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'     => 'dashicons-location-alt',
			'show_in_rest'  => true,
		) );

		// Pétitions
		register_post_type( 'ag_petition', array(
			'labels' => array(
				'name'          => __( 'Pétitions', 'ag-fidelite-association' ),
				'singular_name' => __( 'Pétition', 'ag-fidelite-association' ),
			),
			'public'        => true,
			'has_archive'   => 'petitions',
			'rewrite'       => array( 'slug' => 'petition' ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'     => 'dashicons-edit-large',
			'show_in_rest'  => true,
		) );

		// Procès-verbaux & comptes-rendus (privé, accès adhérents)
		register_post_type( 'ag_pv', array(
			'labels' => array(
				'name'          => __( 'PV & Comptes-rendus', 'ag-fidelite-association' ),
				'singular_name' => __( 'PV', 'ag-fidelite-association' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'capability_type' => 'post',
			'supports'      => array( 'title', 'editor', 'author' ),
			'menu_icon'     => 'dashicons-media-document',
		) );
	}
}
