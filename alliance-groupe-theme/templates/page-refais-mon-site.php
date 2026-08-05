<?php
/**
 * Template Name: Refais mon site par l'IA
 *
 * Outil public « Vois ton site refait par l'IA en 60 secondes ».
 * Logique + rendu dans inc/ag-refais-mon-site.php.
 */

get_header();
if ( function_exists( 'ag_refais_render' ) ) {
	echo ag_refais_render();
}
get_footer();
