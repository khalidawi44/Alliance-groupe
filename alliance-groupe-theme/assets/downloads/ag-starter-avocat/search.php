<?php
/**
 * The template for displaying search results pages.
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">
	<div class="ag-container">

		<?php if ( have_posts() ) : ?>

			<header>
				<h1 class="ag-entry-title">
					<?php
					/* translators: %s: search query. */
					printf( esc_html__( 'Resultats de recherche pour : %s', 'ag-starter-avocat' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
					?>
				</h1>
			</header>

			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?>>
					<header>
						<?php the_title( sprintf( '<h2 class="ag-entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
						<div class="ag-entry-meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						</div>
					</header>
					<div class="ag-entry-content">
						<?php the_excerpt(); ?>
					</div>
				</article>
				<?php
			endwhile;

			the_posts_pagination(
				array(
					'class'     => 'ag-pagination',
					'prev_text' => esc_html__( 'Precedent', 'ag-starter-avocat' ),
					'next_text' => esc_html__( 'Suivant', 'ag-starter-avocat' ),
				)
			);

		else :
			?>
			<p><?php esc_html_e( 'Aucun resultat pour votre recherche. Essayez avec d\'autres mots-cles.', 'ag-starter-avocat' ); ?></p>
			<?php get_search_form(); ?>
			<?php
		endif;
		?>

	</div>
</main>

<?php
get_sidebar();
get_footer();
