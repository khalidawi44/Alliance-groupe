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
	$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
	$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );
	while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Mes accompagnements' ); ?></span>
				<h1 class="ag-page-title">Tous mes <em>accompagnements</em></h1>
				<p class="ag-page-hero-sub">Séance découverte offerte, formule sur-mesure selon votre objectif.</p>
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
	$ag_services = class_exists( 'ag_coach_Presets' ) ? ag_coach_Presets::get_active_services() : array();
	if ( ! empty( $ag_services ) ) : ?>
		<section class="ag-services-grid-wrap ag-anim" style="background:#fff;">
			<div class="ag-services-grid-header">
				<h2 class="ag-services-grid-title">Choisissez votre <em>accompagnement</em></h2>
				<p class="ag-services-grid-lead">Cliquez pour réserver votre séance ou demander un devis personnalisé.</p>
			</div>
			<div class="ag-services-grid">
				<?php foreach ( $ag_services as $svc ) :
					$slug    = sanitize_title( $svc['title'] );
					$svc_url = home_url( '/devis/?service=' . $slug );
				?>
					<a class="ag-service-card ag-anim" href="<?php echo esc_url( $svc_url ); ?>">
						<div class="ag-service-card__icon"><?php echo esc_html( $svc['emoji'] ); ?></div>
						<h3 class="ag-service-card__title"><?php echo esc_html( $svc['title'] ); ?></h3>
						<span class="ag-service-card__arrow">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="ag-cta-band">
			<div class="ag-container">
				<h2 class="ag-cta-band__title">Un projet en tête ?</h2>
				<p class="ag-cta-band__lead">Séance découverte offerte — 30 minutes pour clarifier vos besoins, sans engagement.</p>
				<a href="<?php echo esc_url( home_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Réserver ma séance découverte →</a>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
get_sidebar();
get_footer();
