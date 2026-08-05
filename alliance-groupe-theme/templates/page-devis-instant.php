<?php
/**
 * Template Name: Devis instantané par l'IA
 *
 * Outil public de devis instantané (voix ou texte).
 * Logique + rendu dans inc/ag-devis-instant.php.
 */

get_header();
if ( function_exists( 'ag_devis_render' ) ) {
	echo ag_devis_render();
}
get_footer();
