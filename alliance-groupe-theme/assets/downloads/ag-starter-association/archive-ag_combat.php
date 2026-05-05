<?php
/**
 * Archive Combats (CPT ag_combat) — grille avec visuels SVG colores
 * thematiques (climat, logement, etc.) generes automatiquement.
 *
 * @package AG_Starter_Association
 */
get_header();
?>
<main id="main">
	<section class="ag-asso-section" style="padding-top:60px;">
		<div class="ag-asso-container">
			<h1 class="ag-asso-section__title">Nos <em>combats</em></h1>
			<p class="ag-asso-section__lead">Six grandes campagnes que nous portons sur le terrain et dans les institutions.</p>

			<div class="ag-asso-combats-grid ag-asso-combats-grid--archive">
				<?php
				$q = new WP_Query( array( 'post_type' => 'ag_combat', 'posts_per_page' => 30 ) );
				if ( $q->have_posts() ) :
					while ( $q->have_posts() ) : $q->the_post(); ?>
						<a class="ag-asso-combat ag-asso-combat--card" href="<?php the_permalink(); ?>">
							<?php echo ag_asso_post_visual_html( get_the_ID(), 'large', 'ag-asso-combat__visual' ); ?>
							<div class="ag-asso-combat__body">
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
								<span class="ag-asso-combat__cta">En savoir plus →</span>
							</div>
						</a>
					<?php endwhile;
					wp_reset_postdata();
				else : ?>
					<p>Aucun combat publié pour le moment.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
