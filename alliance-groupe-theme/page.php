<?php
/**
 * Modèle PAR DÉFAUT des pages (page.php).
 *
 * Sans ce fichier, WordPress retombait sur index.php (le blog) pour toute page
 * n'ayant pas de « Template Name » dédié → les pages comme /candidature-ambassadeur
 * ou /ma-prospection affichaient « Nos derniers articles ». Ici on rend le VRAI
 * contenu de la page (donc les shortcodes : [ag_candidature], [ag_ma_prospection]…).
 *
 * @package Alliance_Groupe_Theme
 */

get_header(); ?>

<main class="ag-page" id="ag-main-content">
	<section class="ag-section ag-section--graphite">
		<div class="ag-container" style="max-width:900px;">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'ag-page__article' ); ?>>
					<?php if ( ! is_front_page() ) : ?>
						<h1 class="ag-section__title" style="margin-bottom:18px;"><?php the_title(); ?></h1>
					<?php endif; ?>
					<div class="ag-page__content ag-entry-content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<div class="ag-page__pages">' . esc_html__( 'Pages :', 'alliance-groupe' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>
	</section>
</main>

<?php
get_footer();
