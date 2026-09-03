<?php
/**
 * Plugin Name:       AG Premium Domicile
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Pack Premium pour le thème AG Starter Domicile (services à la personne). Apporte une couche de design plus travaillée : typographie élégante (Fraunces + Nunito Sans), cartes adoucies, hero enrichi avec bandeau de confiance (Agréé SAP · Crédit d'impôt 50% · 7j/7), témoignages animés, header collant et bouton « Devis gratuit » flottant. S'active si la licence est premium (ou via le mode test).
 * Version:           1.0.3
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-premium-domicile
 *
 * Convention CSS : classes ciblées sous `body.ag-dm-premium-active` — le
 * plugin n'ajoute QUE du design, il ne touche pas au thème gratuit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Durcissement securite (xmlrpc, enumeration auteur/REST, en-tetes, versions) — livre avec le plugin
$ag_hard = plugin_dir_path( __FILE__ ) . 'inc/ag-hardening.php';
if ( file_exists( $ag_hard ) ) { require_once $ag_hard; }
define( 'AG_PREMIUM_DOMICILE_VERSION', '1.0.3' );
define( 'AG_PREMIUM_DOMICILE_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_PREMIUM_DOMICILE_URL', plugin_dir_url( __FILE__ ) );

// === THEME GUARD === Ne pas booter si le thème actif n'est pas
// ag-starter-domicile (ni un fork basé dessus, ex. ag-gwen-services).
$ag_pd_theme = get_option( 'stylesheet' );
$ag_pd_tmpl  = get_option( 'template' );
if ( 0 !== strpos( (string) $ag_pd_theme, 'ag-starter-domicile' ) && 0 !== strpos( (string) $ag_pd_tmpl, 'ag-starter-domicile' )
	&& 0 !== strpos( (string) $ag_pd_theme, 'ag-gwen-services' ) && 0 !== strpos( (string) $ag_pd_tmpl, 'ag-gwen-services' ) ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p><strong>AG Premium Domicile</strong> nécessite le thème <code>AG Starter Domicile</code> (ou un site basé dessus) pour fonctionner. Désactivez ce plugin ou activez le thème compatible.</p></div>';
	} );
	return;
}

require_once AG_PREMIUM_DOMICILE_DIR . 'inc/class-ag-premium-domicile.php';
require_once AG_PREMIUM_DOMICILE_DIR . 'inc/class-ag-pd-updater.php';

add_action( 'plugins_loaded', function () {
	AG_Premium_Domicile::instance();
	AG_PD_Updater::init();
} );
