<?php
/**
 * Plugin Name:       AG Premium Barber
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Premium pour le thème AG Starter Barber. Statistiques du salon (visiteurs, pages vues, sources, tickets pris, heures de pointe, prestations les plus demandées) calculées sur votre hébergement, sans service externe ni cookie. Plus les enrichissements design. Active si tier === premium OU business.
 * Version:           0.5.3
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-premium-barber
 *
 * Convention CSS : classes prefixees `ag-bb-*` (partage avec Business
 * pour eviter duplication). Premium ajoute uniquement le design,
 * Business injecte en plus 5 sections supplementaires.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_PREMIUM_BARBER_VERSION', '0.5.3' );
define( 'AG_PREMIUM_BARBER_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_PREMIUM_BARBER_URL', plugin_dir_url( __FILE__ ) );

// === THEME GUARD === Ne pas booter si le theme actif n'est pas
// ag-starter-barber. Evite la pollution menus/CPT/admin_notices quand
// un autre theme Alliance Groupe est temporairement active.
if ( get_option( 'stylesheet' ) !== 'ag-starter-barber' && get_option( 'template' ) !== 'ag-starter-barber' ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) return;
		echo '<div class="notice notice-warning is-dismissible"><p><strong>AG Premium Barber</strong> nécessite le thème <code>ag-starter-barber</code> pour fonctionner. Désactivez ce plugin ou activez le thème compatible.</p></div>';
	} );
	return;
}

require_once AG_PREMIUM_BARBER_DIR . 'inc/class-ag-premium-barber.php';
require_once AG_PREMIUM_BARBER_DIR . 'inc/class-ag-pb-updater.php';
require_once AG_PREMIUM_BARBER_DIR . 'inc/class-ag-pb-stats.php';

add_action( 'after_setup_theme', array( 'AG_Premium_Barber', 'instance' ), 20 );

// Statistiques du salon : trafic + tickets. Voir inc/class-ag-pb-stats.php.
AG_PB_Stats::init();
