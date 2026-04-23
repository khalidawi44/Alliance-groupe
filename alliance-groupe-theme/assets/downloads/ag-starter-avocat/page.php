<?php
/**
 * The template for displaying all pages.
 *
 * Section pages (expertise, honoraires, cabinet, rendez-vous) use
 * their own dedicated page-{slug}.php templates with rich content.
 * This template handles any other standalone page.
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<?php /* Page hero banner */ ?>
	<section class="ag-page-hero">
		<div class="ag-container">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<h1 class="ag-page-hero__title"><?php the_title(); ?></h1>
			<?php endwhile; rewind_posts(); ?>
		</div>
	</section>

	<div class="ag-container ag-page-content-wrap">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'ag-page-article' ); ?>>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ag-entry-thumb">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="ag-entry-content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="ag-page-links">' . esc_html__( 'Pages :', 'ag-starter-avocat' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</article>
			<?php

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}

		endwhile;
		?>

	</div>
</main>

<?php
get_sidebar();
get_footer();
