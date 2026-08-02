<?php
/**
 * @package AG_Starter_Avocat
 */
get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

	<section class="ag-page-hero">
		<div class="ag-container">
			<h1 class="ag-page-hero__title"><?php the_title(); ?></h1>
			<div class="ag-post-meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</div>
		</div>
	</section>

	<div class="ag-container ag-page-content-wrap">
		<article <?php post_class( 'ag-page-article' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="ag-entry-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<div class="ag-entry-content">
				<?php
				the_content();
				wp_link_pages( array(
					'before' => '<div class="ag-page-links">' . esc_html__( 'Pages:', 'ag-starter-avocat' ) . ' ',
					'after'  => '</div>',
				) );
				?>
			</div>
			<footer class="ag-entry-footer">
				<?php
				$categories = get_the_category_list( ', ' );
				if ( $categories ) {
					printf( '<p>%s %s</p>', esc_html__( 'Categories:', 'ag-starter-avocat' ), wp_kses_post( $categories ) );
				}
				$tags = get_the_tag_list( '', ', ' );
				if ( $tags ) {
					printf( '<p>%s %s</p>', esc_html__( 'Tags:', 'ag-starter-avocat' ), wp_kses_post( $tags ) );
				}
				?>
			</footer>
		</article>
		<div class="ag-container ag-comments-wrap">
			<?php comments_template(); ?>
		</div>
	<?php endwhile; ?>
	</div>

</main>

<?php get_footer();
