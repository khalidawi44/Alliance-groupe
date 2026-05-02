<?php
/**
 * Plugin Name:       AG Business Barber
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Business pour le thème AG Starter Barber. Ambiance vintage / industrielle inspirée des barbershops US (Back Alive vibe), en français. Section équipe, galerie, témoignages, réservation calendrier. Active uniquement si tier === business détecté via AG_Licence_Client.
 * Version:           0.1.0
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

define( 'AG_BUSINESS_BARBER_VERSION', '0.6.0' );
define( 'AG_BUSINESS_BARBER_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_BUSINESS_BARBER_URL', plugin_dir_url( __FILE__ ) );

require_once AG_BUSINESS_BARBER_DIR . 'inc/class-ag-business-barber.php';

// Boot apres le theme charge (AG_Licence_Client necessaire).
add_action( 'after_setup_theme', array( 'AG_Business_Barber', 'instance' ), 20 );
