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
	}
}
