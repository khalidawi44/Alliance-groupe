<?php
/**
 * Plugin Name:       AG Recherche Juridique (Avocat)
 * Plugin URI:        https://alliancegroupe-inc.com
 * Description:       Cabinet de recherche juridique pour l'avocat : méta-moteur vers toutes les sources officielles (Légifrance, Cour de cassation/Judilibre, Conseil d'État, Conseil constitutionnel, CEDH, CJUE, EUR-Lex, doctrine, fiscal…), recherche LIVE de jurisprudence via l'API Judilibre (open data, gratuite), salle d'analyse de dossiers (faits / problème de droit / textes / jurisprudence / arguments adverses / parade / stratégie), banque d'arguments réutilisables et assistant IA optionnel. Outil privé (réservé au cabinet, dans le tableau de bord WordPress).
 * Version:           1.3.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Alliance Groupe
 * License:           GPL-2.0-or-later
 * Text Domain:       ag-avocat-recherche
 *
 * Plugin AUTONOME et SÉPARÉ des plugins avocat verrouillés (Free / Premium /
 * Business). Aucune dépendance : il fonctionne dès qu'il est activé. Toutes les
 * classes/options sont préfixées `ag_jr_` / `ag-jr-` pour éviter toute collision.
 *
 * Déontologie : outil d'aide à la recherche et à l'analyse pour le
 * professionnel du droit. Il ne donne pas de consultation et n'engage pas la
 * responsabilité du cabinet : l'avocat vérifie toujours les sources primaires.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_JR_VERSION', '1.3.0' );
define( 'AG_JR_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_JR_URL', plugin_dir_url( __FILE__ ) );

require_once AG_JR_DIR . 'inc/class-ag-recherche-juridique.php';
require_once AG_JR_DIR . 'inc/class-ag-jr-updater.php';

add_action( 'plugins_loaded', array( 'AG_Recherche_Juridique', 'instance' ) );

// Création des pages / rôles à l'activation (rien de destructif).
register_activation_hook( __FILE__, array( 'AG_Recherche_Juridique', 'on_activate' ) );
