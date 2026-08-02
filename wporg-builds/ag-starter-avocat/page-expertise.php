<?php
/**
 * Template for the "Domaines d'expertise" page.
 * WordPress auto-loads this for the page with slug "expertise".
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<section class="ag-page-hero">
		<div class="ag-container">
			<h1 class="ag-page-hero__title"><?php esc_html_e( 'Practice areas', 'ag-starter-avocat' ); ?></h1>
			<p class="ag-page-hero__lead"><?php esc_html_e( 'Advice and representation for individuals and businesses across the main areas of law.', 'ag-starter-avocat' ); ?></p>
		</div>
	</section>

	<section class="ag-section ag-domaines">
		<div class="ag-container">

			<?php
			$domaines = ag_starter_avocat_get_domaines( 12 );
			if ( $domaines ) :
				?>
				<div class="ag-domaines__grid">
					<?php foreach ( $domaines as $d ) :
						$icon     = get_post_meta( $d->ID, '_ag_domaine_icon', true );
						$examples = get_post_meta( $d->ID, '_ag_domaine_examples', true );
						?>
						<div class="ag-domaine-card">
							<div class="ag-domaine-card__icon"><?php
								echo function_exists( 'ag_starter_avocat_get_domaine_icon_html' )
									? ag_starter_avocat_get_domaine_icon_html( $icon ) /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
									: esc_html( $icon ? $icon : '⚖️' );
							?></div>
							<h3 class="ag-domaine-card__title"><?php echo esc_html( get_the_title( $d ) ); ?></h3>
							<p class="ag-domaine-card__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $d ) ) ); ?></p>
							<?php if ( $examples ) : ?>
								<ul class="ag-domaine-card__examples">
									<?php foreach ( array_slice( array_filter( array_map( 'trim', explode( "\n", $examples ) ) ), 0, 3 ) as $ex ) : ?>
										<li><?php echo esc_html( $ex ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="ag-domaines__empty">
					<p><?php esc_html_e( 'No practice area has been published yet.', 'ag-starter-avocat' ); ?></p>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ag_domaine' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Add a first practice area', 'ag-starter-avocat' ); ?></a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</section>

	<section class="ag-section ag-rdv-cta" style="text-align:center;">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Need legal advice?', 'ag-starter-avocat' ); ?></h2>
			<p class="ag-section-lead"><?php esc_html_e( 'Book an appointment for a first confidential consultation.', 'ag-starter-avocat' ); ?></p>
			<a href="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Book an appointment', 'ag-starter-avocat' ); ?></a>
		</div>
	</section>

</main>

<?php
get_footer();
