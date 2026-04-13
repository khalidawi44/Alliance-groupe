<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">
	<div class="ag-container" style="text-align:center; padding: 4rem 1.5rem;">
		<h1 class="ag-entry-title"><?php esc_html_e( 'Page introuvable', 'ag-starter-avocat' ); ?></h1>
		<p><?php esc_html_e( 'La page que vous cherchez n\'existe pas ou a ete deplacee.', 'ag-starter-avocat' ); ?></p>
		<p><a class="ag-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour a l\'accueil', 'ag-starter-avocat' ); ?></a></p>
		<div style="margin-top: 2rem;"><?php get_search_form(); ?></div>
	</div>
</main>

<?php
get_footer();
