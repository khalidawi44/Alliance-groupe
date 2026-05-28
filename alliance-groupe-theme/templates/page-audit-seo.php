<?php
/**
 * Template Name: Audit SEO gratuit
 *
 * Page publique. Affiche le formulaire OU le rapport (si ?aid=…).
 * Logique dans inc/ag-audit-seo.php.
 */

get_header();
if ( function_exists( 'ag_audit_render' ) ) ag_audit_render();
get_footer();
