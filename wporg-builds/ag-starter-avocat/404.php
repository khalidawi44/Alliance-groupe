<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<section class="ag-page-hero">
		<div class="ag-container">
			<h1 class="ag-page-hero__title"><?php esc_html_e( 'Page not found', 'ag-starter-avocat' ); ?></h1>
		</div>
	</section>

	<div class="ag-container ag-page-content-wrap" style="text-align:center;">
		<div class="ag-page-article" style="padding:60px 40px;">
			<p class="ag-404-text"><?php esc_html_e( 'The page you are looking for does not exist or has been moved.', 'ag-starter-avocat' ); ?></p>
			<p style="margin-top:32px;"><a class="ag-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'ag-starter-avocat' ); ?></a></p>
		</div>
	</div>
</main>

<?php
get_footer();
