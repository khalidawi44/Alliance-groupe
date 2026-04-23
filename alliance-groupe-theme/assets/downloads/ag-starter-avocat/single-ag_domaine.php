<?php
/**
 * Single domaine d'expertise template.
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<?php
	while ( have_posts() ) :
		the_post();
		$icon     = get_post_meta( get_the_ID(), '_ag_domaine_icon', true );
		$examples = get_post_meta( get_the_ID(), '_ag_domaine_examples', true );
	?>

	<section class="ag-page-hero">
		<div class="ag-container">
			<div class="ag-domaine-hero-icon"><?php echo esc_html( $icon ? $icon : '⚖️' ); ?></div>
			<p class="ag-domaine-hero-tag"><?php esc_html_e( 'Domaine d\'expertise', 'ag-starter-avocat' ); ?></p>
			<h1 class="ag-page-hero__title"><?php the_title(); ?></h1>
		</div>
	</section>

	<div class="ag-container ag-page-content-wrap">

			<article <?php post_class( 'ag-domaine-single ag-page-article' ); ?>>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ag-entry-thumb">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="ag-entry-content">
					<?php the_content(); ?>
				</div>

				<?php if ( $examples ) : ?>
					<aside class="ag-domaine-examples">
						<h2 class="ag-domaine-examples__title">
							<?php esc_html_e( 'Exemples de cas traités', 'ag-starter-avocat' ); ?>
						</h2>
						<ul class="ag-domaine-examples__list">
							<?php foreach ( array_filter( array_map( 'trim', explode( "\n", $examples ) ) ) as $ex ) : ?>
								<li><?php echo esc_html( $ex ); ?></li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>

				<div class="ag-domaine-cta">
					<p><?php esc_html_e( 'Vous avez un dossier dans ce domaine ?', 'ag-starter-avocat' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/#ag-rdv' ) ); ?>" class="ag-btn">
						<?php esc_html_e( 'Prendre rendez-vous →', 'ag-starter-avocat' ); ?>
					</a>
				</div>
			</article>

		<nav class="ag-domaine-back">
			<a href="<?php echo esc_url( home_url( '/#ag-domaines' ) ); ?>">
				← <?php esc_html_e( 'Tous les domaines d\'expertise', 'ag-starter-avocat' ); ?>
			</a>
		</nav>

		<?php endwhile; ?>
	</div>
</main>

<?php
get_footer();
