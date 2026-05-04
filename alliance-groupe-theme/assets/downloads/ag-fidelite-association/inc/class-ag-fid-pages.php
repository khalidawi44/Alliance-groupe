<?php
/**
 * Crée les pages séparées par défaut à l'activation : manifeste,
 * combats, événements, groupes locaux, signer, don, espace adhérent,
 * adhérer, mentions légales, politique de confidentialité.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Pages {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		// Rien à faire en runtime — la création des pages se fait à
		// l'activation du plugin (cf. register_activation_hook).
	}

	public static function create_default_pages() {
		$pages = array(
			'manifeste'    => array( 'title' => 'Manifeste',           'shortcode' => '[ag_fid_manifeste]' ),
			'combats'      => array( 'title' => 'Nos combats',         'shortcode' => '[ag_fid_combats]' ),
			'evenements'   => array( 'title' => 'Événements',          'shortcode' => '[ag_fid_evenements]' ),
			'groupes'      => array( 'title' => 'Groupes locaux',      'shortcode' => '[ag_fid_groupes]' ),
			'actu'         => array( 'title' => 'Actualités',          'shortcode' => '[ag_fid_actu]' ),
			'signer'       => array( 'title' => 'Signer l\'appel',     'shortcode' => '[ag_fid_signer]' ),
			'petitions'    => array( 'title' => 'Pétitions',           'shortcode' => '[ag_fid_petitions]' ),
			'don'          => array( 'title' => 'Faire un don',        'shortcode' => '[ag_fid_don]' ),
			'adherer'      => array( 'title' => 'Adhérer',             'shortcode' => '[ag_fid_adhesion]' ),
			'mon-compte'   => array( 'title' => 'Mon espace',          'shortcode' => '[ag_fid_compte]' ),
			'mentions'     => array( 'title' => 'Mentions légales',    'shortcode' => '[ag_fid_mentions]' ),
			'rgpd'         => array( 'title' => 'Confidentialité',     'shortcode' => '[ag_fid_rgpd]' ),
			'statuts'      => array( 'title' => 'Statuts',             'content'   => '<p>[Texte des statuts de l\'association — copier ici depuis le PDF officiel.]</p>' ),
		);
		foreach ( $pages as $slug => $page ) {
			if ( get_page_by_path( $slug ) ) continue;
			wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => isset( $page['shortcode'] ) ? $page['shortcode'] : $page['content'],
			) );
		}
		self::create_default_menus();
	}

	/**
	 * Cree (idempotent) les menus principal et footer puis les assigne
	 * aux emplacements 'primary' et 'footer' du theme. Indispensable
	 * pour que le menu pointe vers les pages reelles et non vers des
	 * ancres.
	 */
	public static function create_default_menus() {
		$primary_items = array(
			'manifeste'  => 'Manifeste',
			'combats'    => 'Combats',
			'evenements' => 'Événements',
			'groupes'    => 'Groupes locaux',
			'actu'       => 'Actualités',
			'don'        => 'Faire un don',
		);
		$footer_items = array(
			'manifeste' => 'Le manifeste',
			'groupes'   => 'Trouver mon groupe',
			'don'       => 'Faire un don',
			'mentions'  => 'Mentions légales',
			'rgpd'      => 'Confidentialité',
		);
		self::ensure_menu( 'AG Fidélité — Principal', 'primary', $primary_items );
		self::ensure_menu( 'AG Fidélité — Footer',    'footer',  $footer_items );
	}

	private static function ensure_menu( $menu_name, $location, $items ) {
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
			if ( is_wp_error( $menu_id ) ) return;
		} else {
			$menu_id = $menu->term_id;
			foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
		}
		foreach ( $items as $slug => $label ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) continue;
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
