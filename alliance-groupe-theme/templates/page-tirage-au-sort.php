<?php
/**
 * Template Name: Tirage au sort (site gratuit)
 *
 * Page publique du jeu concours mensuel. Logique dans
 * inc/ag-tirage-mensuel.php.
 */

get_header();
if ( function_exists( 'ag_tirage_render' ) ) ag_tirage_render();
get_footer();
