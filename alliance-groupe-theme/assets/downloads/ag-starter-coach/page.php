<?php
/**
 * The template for displaying all pages.
 *
 * @package AG_Starter_Coach
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">
	<div class="ag-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<header>
					<h1 class="ag-entry-title"><?php the_title(); ?></h1>
				</header>

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
							'before' => '<div class="ag-page-links">' . esc_html__( 'Pages :', 'ag-starter-coach' ),
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
