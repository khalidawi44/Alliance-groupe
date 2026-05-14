<?php
/**
 * Plugin Name:       AG Fidélité Association
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Fidélité (top tier 99€) pour le thème AG Starter Association. Pages séparées, CPT (combats / événements / groupes locaux), rôles utilisateurs (adhérent / militant / sympathisant), outils de gestion d'association, recommandations d'extensions.
 * Version:           0.50.0
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

define( 'AG_FID_VERSION', '0.50.0' );
define( 'AG_FID_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_FID_URL', plugin_dir_url( __FILE__ ) );

// === THEME GUARD === Ne pas booter le plugin si le theme actif n'est
// pas AG Starter Association (eviter les bandeaux/CPT/menus polluants
// quand l'utilisateur teste un autre theme Alliance Groupe sur le meme
// site). Un seul admin_notice apparait pour orienter.
if ( get_option( 'stylesheet' ) !== 'ag-starter-association' && get_option( 'template' ) !== 'ag-starter-association' ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) return;
		echo '<div class="notice notice-warning is-dismissible"><p><strong>AG Fidélité Association</strong> nécessite le thème <code>ag-starter-association</code> pour fonctionner. Désactivez ce plugin ou activez le thème compatible (<em>Apparence &rsaquo; Thèmes</em>).</p></div>';
	} );
	return;
}

require_once AG_FID_DIR . 'inc/class-ag-fid-core.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-cpt.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-roles.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-pages.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-shortcodes.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-recommendations.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-updater.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-guide.php';
require_once AG_FID_DIR . 'inc/class-ag-fid-presets.php';

add_action( 'plugins_loaded', array( 'AG_Fid_Core', 'instance' ), 5 );
register_activation_hook( __FILE__, array( 'AG_Fid_Roles', 'create_roles' ) );
register_activation_hook( __FILE__, array( 'AG_Fid_Pages', 'create_default_pages' ) );
