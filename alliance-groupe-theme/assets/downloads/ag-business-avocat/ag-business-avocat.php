<?php
/**
 * Plugin Name:       AG Premium Avocat — Boutique & Cabinet
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Fonctionnalités Premium (boutique, équipe, comptes clients) pour le thème AG Starter Avocat. Active sur toute licence Premium via AG_Licence_Client.
 * Version:           0.55.0
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

define( 'AG_BUSINESS_AVOCAT_VERSION', '0.55.0' );
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

// === FUSION (v0.53.0) === Base design Premium intégrée ici (ex-plugin
// AG Premium Avocat). On expose les constantes attendues par la classe
// Premium en les pointant sur CE plugin (où premium.css/js ont été copiés),
// puis on charge et boote la classe. Garde class_exists : si l'ancien
// plugin tourne encore, il se désactive de lui-même (voir son guard), donc
// pas de double déclaration.
if ( ! defined( 'AG_PREMIUM_AVOCAT_VERSION' ) ) {
	define( 'AG_PREMIUM_AVOCAT_VERSION', AG_BUSINESS_AVOCAT_VERSION );
	define( 'AG_PREMIUM_AVOCAT_DIR', AG_BUSINESS_AVOCAT_DIR );
	define( 'AG_PREMIUM_AVOCAT_URL', AG_BUSINESS_AVOCAT_URL );
}
if ( ! class_exists( 'AG_Premium_Avocat' ) ) {
	require_once AG_BUSINESS_AVOCAT_DIR . 'inc/class-ag-premium-avocat.php';
}

// Boot after the theme has loaded, so AG_Licence_Client (defined in the
// Free theme) is available for tier detection.
add_action( 'after_setup_theme', array( 'AG_Business_Avocat', 'instance' ), 20 );
add_action( 'after_setup_theme', array( 'AG_Premium_Avocat', 'instance' ), 20 );
