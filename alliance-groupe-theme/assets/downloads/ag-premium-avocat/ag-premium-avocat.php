<?php
/**
 * Plugin Name:       AG Premium Avocat
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Fonctionnalités Premium pour le thème AG Starter Avocat. Active uniquement si tier >= premium détecté via AG_Licence_Client.
 * Version:           0.1.0
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

define( 'AG_PREMIUM_AVOCAT_VERSION', '0.1.0' );
define( 'AG_PREMIUM_AVOCAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_PREMIUM_AVOCAT_URL', plugin_dir_url( __FILE__ ) );

require_once AG_PREMIUM_AVOCAT_DIR . 'inc/class-ag-premium-avocat.php';

add_action( 'plugins_loaded', array( 'AG_Premium_Avocat', 'instance' ), 20 );
