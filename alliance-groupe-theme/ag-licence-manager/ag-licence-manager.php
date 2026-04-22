<?php
/**
 * Plugin Name:       AG Licence Manager
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Système de licence pour les thèmes AG Starter (Pro/Premium/Business). Gère les clés, l'activation, la vérification, les webhooks Stripe et la distribution des mises à jour Pro.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * Author URI:        https://alliancegroupe-inc.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ag-licence-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AG_LM_VERSION', '1.0.0' );
define( 'AG_LM_FILE', __FILE__ );
define( 'AG_LM_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_LM_URL', plugin_dir_url( __FILE__ ) );

// HMAC key for signing API responses (set in wp-config.php ideally)
if ( ! defined( 'AG_LICENCE_HMAC_KEY' ) ) {
    define( 'AG_LICENCE_HMAC_KEY', 'ag-default-hmac-change-me-in-wp-config' );
}

// Load classes
require_once AG_LM_DIR . 'includes/class-ag-licence-db.php';
require_once AG_LM_DIR . 'includes/class-ag-licence-api.php';
require_once AG_LM_DIR . 'includes/class-ag-licence-admin.php';
require_once AG_LM_DIR . 'includes/class-ag-licence-stripe.php';
require_once AG_LM_DIR . 'includes/class-ag-licence-email.php';

/**
 * Plugin activation: create DB table.
 */
register_activation_hook( AG_LM_FILE, array( 'AG_Licence_DB', 'install' ) );

/**
 * Auto-upgrade DB schema if version changed.
 */
add_action( 'admin_init', function () {
    $installed = get_option( 'ag_lm_db_version', '0' );
    if ( version_compare( $installed, AG_LM_VERSION, '<' ) ) {
        AG_Licence_DB::install();
    }
} );

/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', function () {
    AG_Licence_API::init();
    AG_Licence_Admin::init();
    AG_Licence_Stripe::init();
} );
