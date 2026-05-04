<?php
/**
 * Rôles utilisateurs spécifiques association.
 * Termes hiérarchiques typiques :
 *   - sympathisant : compte simple, suit l'actu, signe des pétitions
 *   - adherent     : a payé sa cotisation, accès docs internes
 *   - militant     : adhérent actif, peut animer un groupe local
 *   - tresorier    : voit les finances
 *   - secretaire   : voit/édite les PV
 *   - president    : tout pouvoir association (sans toucher au tech)
 *
 * Slugs URL personnalisés :
 *   /sympathisant/jean-dupont
 *   /adherent/jean-dupont
 *   /militant/jean-dupont
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Roles {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'the_author', array( $this, 'maybe_set_role_in_url' ) );
	}

	public static function create_roles() {
		add_role( 'ag_sympathisant', __( 'Sympathisant', 'ag-fidelite-association' ), array(
			'read' => true,
		) );
		add_role( 'ag_adherent', __( 'Adhérent', 'ag-fidelite-association' ), array(
			'read'         => true,
			'edit_posts'   => false,
			'read_private_posts' => true, // accès docs internes
		) );
		add_role( 'ag_militant', __( 'Militant', 'ag-fidelite-association' ), array(
			'read'                => true,
			'edit_posts'          => true,
			'edit_published_posts'=> true,
			'publish_posts'       => false, // valide par admin
			'read_private_posts'  => true,
			'upload_files'        => true,
		) );
		add_role( 'ag_tresorier', __( 'Trésorier', 'ag-fidelite-association' ), array(
			'read'         => true,
			'manage_options' => false,
		) );
		add_role( 'ag_secretaire', __( 'Secrétaire', 'ag-fidelite-association' ), array(
			'read'        => true,
			'edit_posts'  => true,
			'publish_posts'=> true,
			'read_private_posts' => true,
			'upload_files'=> true,
		) );
		add_role( 'ag_president', __( 'Président·e', 'ag-fidelite-association' ), array(
			'read'         => true,
			'edit_posts'   => true,
			'publish_posts'=> true,
			'edit_others_posts' => true,
			'read_private_posts'=> true,
			'upload_files' => true,
		) );
	}

	/**
	 * Ajoute les rewrite rules pour avoir des URL avec terminaisons :
	 *   /adherent/<slug>
	 *   /militant/<slug>
	 *   /sympathisant/<slug>
	 */
	public function add_rewrite_rules() {
		foreach ( array( 'sympathisant', 'adherent', 'militant', 'tresorier', 'secretaire', 'president' ) as $role ) {
			add_rewrite_rule(
				'^' . $role . '/([^/]+)/?$',
				'index.php?author_name=$matches[1]&ag_role_slug=' . $role,
				'top'
			);
		}
		add_filter( 'query_vars', function ( $vars ) {
			$vars[] = 'ag_role_slug';
			return $vars;
		} );
	}

	public function maybe_set_role_in_url( $author ) {
		return $author;
	}
}
