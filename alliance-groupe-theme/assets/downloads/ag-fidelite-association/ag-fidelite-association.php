<?php
/**
 * Plugin Name:       AG Fidélité Association
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Fidélité (top tier 99€) pour le thème AG Starter Association. Pages séparées, CPT (combats / événements / groupes locaux), rôles utilisateurs (adhérent / militant / sympathisant), outils de gestion d'association, recommandations d'extensions.
 * Version:           0.11.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-fidelite-association
 *
 * Convention : classes prefixees `ag-fid-*` (ag-fidelite).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_FID_VERSION', '0.11.0' );
define( 'AG_FID_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_FID_URL', plugin_dir_url( __FILE__ ) );

require_once AG_FID_DIR . 'inc/class-ag-fid-core.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-cpt.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-roles.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-pages.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-shortcodes.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-recommendations.php';

add_action( 'plugins_loaded', array( 'AG_Fid_Core', 'instance' ), 5 );
register_activation_hook( __FILE__, array( 'AG_Fid_Roles', 'create_roles' ) );
register_activation_hook( __FILE__, array( 'AG_Fid_Pages', 'create_default_pages' ) );
