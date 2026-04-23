<?php
/**
 * The template for displaying all pages.
 *
 * For the avocat one-page design, section pages (expertise, honoraires,
 * cabinet, rendez-vous) redirect to their front-page anchor instead
 * of rendering a bland standalone page.
 *
 * @package AG_Starter_Avocat
 */

$ag_section_anchors = array(
	'expertise'    => '#ag-domaines',
	'honoraires'   => '#ag-honoraires',
	'cabinet'      => '#ag-cabinet',
	'rendez-vous'  => '#ag-rdv',
);

$ag_current_slug = get_post_field( 'post_name', get_queried_object_id() );
if ( isset( $ag_section_anchors[ $ag_current_slug ] ) ) {
	wp_safe_redirect( home_url( '/' . $ag_section_anchors[ $ag_current_slug ] ) );
	exit;
}

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
