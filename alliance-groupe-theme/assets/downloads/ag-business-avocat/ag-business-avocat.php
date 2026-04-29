<?php
/**
 * Plugin Name:       AG Business Avocat
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Fonctionnalités Business pour le thème AG Starter Avocat. Active uniquement si tier === business détecté via AG_Licence_Client.
 * Version:           0.1.0
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

define( 'AG_BUSINESS_AVOCAT_VERSION', '0.22.0' );
define( 'AG_BUSINESS_AVOCAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_BUSINESS_AVOCAT_URL', plugin_dir_url( __FILE__ ) );

require_once AG_BUSINESS_AVOCAT_DIR . 'inc/class-ag-business-avocat.php';

// Boot after the theme has loaded, so AG_Licence_Client (defined in the
// Free theme) is available for tier detection.
add_action( 'after_setup_theme', array( 'AG_Business_Avocat', 'instance' ), 20 );
