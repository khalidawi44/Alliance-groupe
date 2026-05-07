<?php
/**
 * Front page template — static landing page for the coach / consultant.
 *
 * @package AG_Starter_Coach
 */

get_header();

// Zone editable WP : contenu de la page "accueil" Gutenberg
if ( have_posts() ) : while ( have_posts() ) : the_post();
    if ( trim( get_the_content() ) ) :
        echo "<section class=\"ag-custom-content\" style=\"padding:50px 24px;background:#fff;\"><div style=\"max-width:1180px;margin:0 auto;\">";
        the_content();
        echo "</div></section>";
    endif;
endwhile; rewind_posts(); endif; ?>

<main id="ag-main" class="ag-main" role="main">

	<?php if ( ag_starter_coach_get_option( 'ag_hero_show' ) ) : ?>
	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php echo esc_html( ag_starter_coach_get_option( 'ag_hero_prefix' ) ); ?>
				<span><?php echo esc_html( ag_starter_coach_get_option( 'ag_hero_brand' ) ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php echo esc_html( ag_starter_coach_get_option( 'ag_hero_subtitle' ) ); ?>
			</p>
			<?php
			$ag_btn_label = ag_starter_coach_get_option( 'ag_hero_button' );
			$ag_btn_url   = ag_starter_coach_get_option( 'ag_hero_button_url' );
			if ( $ag_btn_label ) :
				?>
				<a href="<?php echo esc_url( $ag_btn_url ); ?>" class="ag-btn"><?php echo esc_html( $ag_btn_label ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<section class="ag-container" id="ag-services">
		<div class="ag-cards">
			<div class="ag-card">
				<h2>
					<?php
					$ag_coach_indiv_pre = ag_coach_opt( 'ag_coach_individuel_title_pre', 'Coaching' );
					$ag_coach_indiv_em  = ag_coach_opt( 'ag_coach_individuel_title_em',  'individuel' );
					if ( '' !== $ag_coach_indiv_pre ) :
						echo esc_html( $ag_coach_indiv_pre ) . ' ';
					endif;
					?>
					<span><?php echo esc_html( $ag_coach_indiv_em ); ?></span>
				</h2>
				<p><?php echo esc_html( ag_coach_opt( 'ag_coach_individuel_lead', 'Un accompagnement sur-mesure pour atteindre vos objectifs personnels ou professionnels. Seance d\'essai gratuite de 30 minutes.' ) ); ?></p>
			</div>
			<div class="ag-card">
				<h2>
					<?php
					$ag_coach_groupe_pre = ag_coach_opt( 'ag_coach_groupe_title_pre', 'Seances' );
					$ag_coach_groupe_em  = ag_coach_opt( 'ag_coach_groupe_title_em',  'de groupe' );
					if ( '' !== $ag_coach_groupe_pre ) :
						echo esc_html( $ag_coach_groupe_pre ) . ' ';
					endif;
					?>
					<span><?php echo esc_html( $ag_coach_groupe_em ); ?></span>
				</h2>
				<p><?php echo esc_html( ag_coach_opt( 'ag_coach_groupe_lead', 'Ateliers thematiques et formations collectives pour progresser ensemble dans un cadre bienveillant et structurant.' ) ); ?></p>
			</div>
			<div class="ag-card">
				<h2>
					<?php
					$ag_coach_temo_pre = ag_coach_opt( 'ag_coach_temoignages_title_pre', '' );
					$ag_coach_temo_em  = ag_coach_opt( 'ag_coach_temoignages_title_em',  'Temoignages' );
					if ( '' !== $ag_coach_temo_pre ) :
						echo esc_html( $ag_coach_temo_pre ) . ' ';
					endif;
					?>
					<span><?php echo esc_html( $ag_coach_temo_em ); ?></span>
				</h2>
				<p><?php echo esc_html( ag_coach_opt( 'ag_coach_temoignages_lead', 'Decouvrez les retours de mes clients : reconversion reussie, prise de parole debloquee, confiance retrouvee.' ) ); ?></p>
			</div>
		</div>
	</section>

	<section class="ag-info">
		<div class="ag-container">
			<h2>
				<?php
				$ag_coach_about_pre = ag_coach_opt( 'ag_coach_about_title_pre', 'Mon' );
				$ag_coach_about_em  = ag_coach_opt( 'ag_coach_about_title_em',  'parcours' );
				if ( '' !== $ag_coach_about_pre ) :
					echo esc_html( $ag_coach_about_pre ) . ' ';
				endif;
				?>
				<span><?php echo esc_html( $ag_coach_about_em ); ?></span>
			</h2>
			<p><?php echo esc_html( ag_coach_opt( 'ag_coach_about_p1', 'Coach professionnelle certifiee, j\'accompagne depuis plus de dix ans des dirigeants, entrepreneurs et particuliers dans leurs transformations. Mon approche combine ecoute active, methodes eprouvees et ancrage concret.' ) ); ?></p>
			<p><?php echo esc_html( ag_coach_opt( 'ag_coach_about_p2', 'Chaque accompagnement est unique : je m\'adapte a votre rythme et a vos objectifs, avec pour seule boussole votre progres. Premier rendez-vous toujours gratuit pour valider ensemble la meilleure formule.' ) ); ?></p>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
