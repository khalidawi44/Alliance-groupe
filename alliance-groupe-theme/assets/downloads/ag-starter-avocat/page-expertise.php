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
			<h1 class="ag-page-hero__title"><?php esc_html_e( 'Domaines d\'expertise', 'ag-starter-avocat' ); ?></h1>
			<p class="ag-page-hero__lead"><?php esc_html_e( 'Conseil et representation pour particuliers et entreprises. Cliquez sur un domaine pour decouvrir les cas que nous traitons.', 'ag-starter-avocat' ); ?></p>
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
						<a href="<?php echo esc_url( get_permalink( $d->ID ) ); ?>" class="ag-domaine-card">
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
							<span class="ag-domaine-card__more"><?php esc_html_e( 'En savoir plus →', 'ag-starter-avocat' ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="ag-domaines__empty">
					<p><?php esc_html_e( 'Aucun domaine d\'expertise n\'est encore publie.', 'ag-starter-avocat' ); ?></p>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ag_domaine' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Ajouter un premier domaine', 'ag-starter-avocat' ); ?></a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</section>

	<section class="ag-section ag-rdv-cta" style="text-align:center;">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Besoin d\'un conseil ?', 'ag-starter-avocat' ); ?></h2>
			<p class="ag-section-lead"><?php esc_html_e( 'Prenez rendez-vous pour une premiere consultation confidentielle.', 'ag-starter-avocat' ); ?></p>
			<a href="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Prendre rendez-vous →', 'ag-starter-avocat' ); ?></a>
		</div>
	</section>

</main>

<?php
get_footer();
