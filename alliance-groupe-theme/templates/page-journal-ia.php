<?php
/**
 * Template Name: Journal Fait par l'IA
 *
 * Page publique qui liste tout ce que l'IA (Claude Code) a construit sur le
 * site. Logique + rendu dans inc/ag-journal-ia.php.
 */

get_header();
if ( function_exists( 'ag_journal_ia_render' ) ) {
	echo ag_journal_ia_render();
}
get_footer();
