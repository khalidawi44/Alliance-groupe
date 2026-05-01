<?php
/**
 * Plugin Name:       AG Premium Barber
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Premium pour le thème AG Starter Barber. Apporte les enrichissements design (typo industrielle, palette charbon/rouge sang/or laiton, hero plein image, service cards stylées) sans les sections supplémentaires Business. Active si tier === premium OU business.
 * Version:           0.1.0
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

define( 'AG_PREMIUM_BARBER_VERSION', '0.3.0' );
define( 'AG_PREMIUM_BARBER_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_PREMIUM_BARBER_URL', plugin_dir_url( __FILE__ ) );

require_once AG_PREMIUM_BARBER_DIR . 'inc/class-ag-premium-barber.php';

add_action( 'after_setup_theme', array( 'AG_Premium_Barber', 'instance' ), 20 );
