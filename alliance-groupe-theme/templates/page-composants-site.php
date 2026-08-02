<?php
/**
 * Template Name: Composants (intégré au site)
 *
 * MÊME bibliothèque que l'app autonome, mais INTÉGRÉE au site : avec
 * l'en-tête, le menu et le pied de page du thème. C'est le « composant du
 * site web ». (L'app autonome plein écran = templates/page-composants.php.)
 * Logique dans inc/ag-composants.php.
 */

get_header();
if ( function_exists( 'ag_composants_render' ) ) {
	ag_composants_render();
}
get_footer();
