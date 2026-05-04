<?php
/**
 * @package AG_Starter_Avocat
 */
get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<section class="ag-page-hero">
		<div class="ag-container">
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<h1 class="ag-page-hero__title"><?php single_post_title(); ?></h1>
			<?php else : ?>
				<h1 class="ag-page-hero__title"><?php esc_html_e( 'Actualites', 'ag-starter-avocat' ); ?></h1>
			<?php endif; ?>
		</div>
	</section>

	<div class="ag-container ag-archive-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="ag-posts-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'ag-post-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="ag-post-card__thumb">
								<?php the_post_thumbnail( 'medium_large' ); ?>
							</a>
						<?php endif; ?>
						<div class="ag-post-card__body">
							<time class="ag-post-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<h2 class="ag-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="ag-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
							<a href="<?php the_permalink(); ?>" class="ag-post-card__more"><?php esc_html_e( 'Lire la suite →', 'ag-starter-avocat' ); ?></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<?php
			the_posts_pagination( array(
				'class'     => 'ag-pagination',
				'prev_text' => '←',
				'next_text' => '→',
			) );
			?>

		<?php else : ?>
			<p class="ag-no-results"><?php esc_html_e( 'Aucun article pour le moment.', 'ag-starter-avocat' ); ?></p>
		<?php endif; ?>
	</div>

</main>

<?php get_footer();
