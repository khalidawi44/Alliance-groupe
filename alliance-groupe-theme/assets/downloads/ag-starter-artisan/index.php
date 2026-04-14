<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme.
 *
 * @package AG_Starter_Artisan
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">
	<div class="ag-container">

		<?php if ( have_posts() ) : ?>

			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header>
					<h1 class="ag-entry-title"><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?>>
					<header>
						<?php the_title( sprintf( '<h2 class="ag-entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
						<div class="ag-entry-meta">
							<?php
							/* translators: 1: date, 2: author. */
							printf(
								esc_html__( 'Publie le %1$s par %2$s', 'ag-starter-artisan' ),
								'<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>',
								'<span>' . esc_html( get_the_author() ) . '</span>'
							);
							?>
						</div>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="ag-entry-thumb">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large' ); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="ag-entry-content">
						<?php the_excerpt(); ?>
					</div>
				</article>
				<?php
			endwhile;

			the_posts_pagination(
				array(
					'class'     => 'ag-pagination',
					'prev_text' => esc_html__( 'Precedent', 'ag-starter-artisan' ),
					'next_text' => esc_html__( 'Suivant', 'ag-starter-artisan' ),
				)
			);

		else :
			?>
			<p><?php esc_html_e( 'Aucun contenu a afficher.', 'ag-starter-artisan' ); ?></p>
			<?php
		endif;
		?>

	</div>
</main>

<?php
get_sidebar();
get_footer();
