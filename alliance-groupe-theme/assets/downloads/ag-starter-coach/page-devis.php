<?php
/**
 * Template page Devis : hero photo + shortcode formulaire.
 *
 * @package AG_Starter_Artisan
 */

get_header();

$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
		<div class="ag-container">
			<span class="ag-page-tag">Devis en ligne</span>
			<h1 class="ag-page-title">Estimation <em>instantanée</em></h1>
			<p class="ag-page-hero-sub">Fourchette de prix basée sur les tarifs moyens dans votre région.</p>
		</div>
	</section>

	<section style="padding:40px 24px;background:#fff;">
		<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
	</section>

</main>

<?php
get_sidebar();
get_footer();
