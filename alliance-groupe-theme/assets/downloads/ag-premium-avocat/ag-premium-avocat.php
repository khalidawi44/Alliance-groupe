<?php
/**
 * Plugin Name:       AG Premium Avocat
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Fonctionnalités Premium pour le thème AG Starter Avocat. Active uniquement si tier >= premium détecté via AG_Licence_Client.
 * Version:           0.5.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-premium-avocat
 *
 * Convention CSS : toutes les classes de ce plugin sont préfixées `ag-premium-*`.
 * Aucune classe Free (`ag-section`, `ag-domaine-card`, etc.) ne doit être surchargée
 * dans ce plugin — uniquement de nouvelles classes ou des sélecteurs combinés
 * `body.ag-premium ...`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_PREMIUM_AVOCAT_VERSION', '0.5.0' );
define( 'AG_PREMIUM_AVOCAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_PREMIUM_AVOCAT_URL', plugin_dir_url( __FILE__ ) );

// === THEME GUARD === Ne pas booter si le theme actif n'est pas
// ag-starter-avocat. Evite la pollution menus/CPT/admin_notices quand
// un autre theme Alliance Groupe est temporairement active.
if ( get_option( 'stylesheet' ) !== 'ag-starter-avocat' && get_option( 'template' ) !== 'ag-starter-avocat' ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) return;
		echo '<div class="notice notice-warning is-dismissible"><p><strong>AG Premium Avocat</strong> nécessite le thème <code>ag-starter-avocat</code> pour fonctionner. Désactivez ce plugin ou activez le thème compatible.</p></div>';
	} );
	return;
}

require_once AG_PREMIUM_AVOCAT_DIR . 'inc/class-ag-premium-avocat.php';
require_once AG_PREMIUM_AVOCAT_DIR . 'inc/class-ag-pa-updater.php';

// Boot after the theme has loaded, so AG_Licence_Client (defined in the
// Free theme) is available for tier detection.
add_action( 'after_setup_theme', array( 'AG_Premium_Avocat', 'instance' ), 20 );
