<?php
/**
 * Plugin Name:       AG Business Barber
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Business pour le thème AG Starter Barber. Ambiance vintage / industrielle inspirée des barbershops US (Back Alive vibe), en français. Section équipe, galerie, témoignages, réservation calendrier. Active uniquement si tier === business détecté via AG_Licence_Client.
 * Version:           0.5.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-business-barber
 *
 * Convention CSS : toutes les classes de ce plugin sont préfixées
 * `ag-bb-*` (ag-bb = ag-business-barber). Aucune classe Free ne doit
 * être surchargée — uniquement de nouvelles classes ou des sélecteurs
 * combinés `body.ag-business-active.ag-bb-active ...`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_BUSINESS_BARBER_VERSION', '0.5.0' );
define( 'AG_BUSINESS_BARBER_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_BUSINESS_BARBER_URL', plugin_dir_url( __FILE__ ) );

// === THEME GUARD === Ne pas booter si le theme actif n'est pas
// ag-starter-barber. Evite la pollution menus/CPT/admin_notices quand
// un autre theme Alliance Groupe est temporairement active.
if ( get_option( 'stylesheet' ) !== 'ag-starter-barber' && get_option( 'template' ) !== 'ag-starter-barber' ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) return;
		echo '<div class="notice notice-warning is-dismissible"><p><strong>AG Business Barber</strong> nécessite le thème <code>ag-starter-barber</code> pour fonctionner. Désactivez ce plugin ou activez le thème compatible.</p></div>';
	} );
	return;
}

require_once AG_BUSINESS_BARBER_DIR . 'inc/class-ag-business-barber.php';
require_once AG_BUSINESS_BARBER_DIR . 'inc/class-ag-bb-updater.php';

// Boot apres le theme charge (AG_Licence_Client necessaire).
add_action( 'after_setup_theme', array( 'AG_Business_Barber', 'instance' ), 20 );
