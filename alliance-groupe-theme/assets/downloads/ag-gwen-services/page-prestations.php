<?php
/**
 * Template pour la page Prestations.
 *
 * Affiche le contenu Gutenberg de la page (texte d'intro metier-specifique
 * applique par le preset) PUIS injecte automatiquement la grille des
 * services du preset actif + CTA vers la page devis.
 *
 * Active automatiquement par WP via la hierarchie : page slug 'prestations'
 * → page-prestations.php.
 *
 * @package AG_Starter_Artisan
 */

get_header();
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php
	// Hero compact en haut de page (titre + lead intro)
	$ag_metier_nom = ag_domicile_opt( 'ag_domicile_metier_nom', '' );
	$ag_hero_image = ag_domicile_hero_url();
	while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Nos prestations' ); ?></span>
				<h1 class="ag-page-title">Nos <em>prestations</em></h1>
				<p class="ag-page-hero-sub">Éligibles au crédit d’impôt de 50 %, adaptées à chaque situation.</p>
			</div>
		</section>

		<?php if ( trim( get_the_content() ) ) : ?>
			<section class="ag-page-intro">
				<div class="ag-container">
					<?php the_content(); ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>

	<?php
	$ag_services = class_exists( 'ag_domicile_Presets' ) ? ag_domicile_services() : array();
	if ( ! empty( $ag_services ) ) : ?>
		<section class="ag-services-grid-wrap ag-anim" style="background:#fff;">
			<div class="ag-services-grid-header">
				<h2 class="ag-services-grid-title">Choisissez votre <em>prestation</em></h2>
				<p class="ag-services-grid-lead">Cliquez sur une prestation pour demander un devis personnalisé.</p>
			</div>
			<div class="ag-services-grid">
				<?php foreach ( $ag_services as $svc ) :
					$slug    = sanitize_title( $svc['title'] );
					$svc_url = home_url( '/devis/?service=' . $slug );
				?>
					<a class="ag-service-card ag-anim" href="<?php echo esc_url( $svc_url ); ?>">
						<div class="ag-service-card__icon"><?php echo esc_html( $svc['emoji'] ); ?></div>
						<h3 class="ag-service-card__title"><?php echo esc_html( $svc['title'] ); ?></h3>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="ag-cta-band">
			<div class="ag-container">
				<h2 class="ag-cta-band__title">Un besoin à domicile ?</h2>
				<p class="ag-cta-band__lead">Évaluation à domicile gratuite — crédit d’impôt de 50 %, sans engagement.</p>
				<a href="<?php echo esc_url( ag_domicile_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Demander un devis gratuit</a>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
// get_sidebar(); // retiré : pas de barre de widgets par défaut sur ce site vitrine
get_footer();
