<?php
/**
 * Template Name: Composants (bibliothèque)
 *
 * Espace public dédié aux composants web (façon uiverse.io).
 * Toute la logique est dans inc/ag-composants.php.
 */

get_header();
if ( function_exists( 'ag_composants_render' ) ) {
	ag_composants_render();
}
get_footer();
