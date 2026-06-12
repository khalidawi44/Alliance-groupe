<?php
/**
 * Plugin Name:       AG Business Avocat
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Fonctionnalités Business pour le thème AG Starter Avocat. Active uniquement si tier === business détecté via AG_Licence_Client.
 * Version:           0.51.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-business-avocat
 *
 * Convention CSS : toutes les classes de ce plugin sont préfixées `ag-business-*`.
 * Aucune classe Free ou Premium ne doit être surchargée — uniquement de
 * nouvelles classes ou des sélecteurs combinés `body.ag-business-active ...`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_BUSINESS_AVOCAT_VERSION', '0.51.1' );
define( 'AG_BUSINESS_AVOCAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_BUSINESS_AVOCAT_URL', plugin_dir_url( __FILE__ ) );

// === THEME GUARD === Ne pas booter si le theme actif n'est pas
// ag-starter-avocat. Evite la pollution menus/CPT/admin_notices quand
// un autre theme Alliance Groupe est temporairement active.
if ( get_option( 'stylesheet' ) !== 'ag-starter-avocat' && get_option( 'template' ) !== 'ag-starter-avocat' ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) return;
		echo '<div class="notice notice-warning is-dismissible"><p><strong>AG Business Avocat</strong> nécessite le thème <code>ag-starter-avocat</code> pour fonctionner. Désactivez ce plugin ou activez le thème compatible.</p></div>';
	} );
	return;
}

require_once AG_BUSINESS_AVOCAT_DIR . 'inc/class-ag-business-avocat.php';
require_once AG_BUSINESS_AVOCAT_DIR . 'inc/class-ag-ba-updater.php';

// Boot after the theme has loaded, so AG_Licence_Client (defined in the
// Free theme) is available for tier detection.
add_action( 'after_setup_theme', array( 'AG_Business_Avocat', 'instance' ), 20 );
