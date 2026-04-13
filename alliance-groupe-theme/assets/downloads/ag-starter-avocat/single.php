<?php
/**
 * The template for displaying all single posts.
 *
 * @package AG_Starter_Avocat
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
					<div class="ag-entry-meta">
						<?php
						/* translators: 1: date, 2: author. */
						printf(
							esc_html__( 'Publie le %1$s par %2$s', 'ag-starter-avocat' ),
							'<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>',
							'<span>' . esc_html( get_the_author() ) . '</span>'
						);
						?>
					</div>
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
							'before' => '<div class="ag-page-links">' . esc_html__( 'Pages :', 'ag-starter-avocat' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>

				<footer class="ag-entry-footer">
					<?php
					$categories = get_the_category_list( ', ' );
					if ( $categories ) {
						printf( '<p>%s %s</p>', esc_html__( 'Categories :', 'ag-starter-avocat' ), wp_kses_post( $categories ) );
					}
					$tags = get_the_tag_list( '', ', ' );
					if ( $tags ) {
						printf( '<p>%s %s</p>', esc_html__( 'Etiquettes :', 'ag-starter-avocat' ), wp_kses_post( $tags ) );
					}
					?>
				</footer>
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
