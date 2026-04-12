<?php
/**
 * Front page template — static landing page for the coach / consultant.
 *
 * @package AG_Starter_Coach
 */

get_header();
?>

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
				<h2><?php esc_html_e( 'Coaching individuel', 'ag-starter-coach' ); ?></h2>
				<p><?php esc_html_e( 'Un accompagnement sur-mesure pour atteindre vos objectifs personnels ou professionnels. Seance d\'essai gratuite de 30 minutes.', 'ag-starter-coach' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Seances de groupe', 'ag-starter-coach' ); ?></h2>
				<p><?php esc_html_e( 'Ateliers thematiques et formations collectives pour progresser ensemble dans un cadre bienveillant et structurant.', 'ag-starter-coach' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Temoignages', 'ag-starter-coach' ); ?></h2>
				<p><?php esc_html_e( 'Decouvrez les retours de mes clients : reconversion reussie, prise de parole debloquee, confiance retrouvee.', 'ag-starter-coach' ); ?></p>
			</div>
		</div>
	</section>

	<section class="ag-info">
		<div class="ag-container">
			<h2><?php esc_html_e( 'Mon parcours', 'ag-starter-coach' ); ?></h2>
			<p><?php esc_html_e( 'Coach professionnelle certifiee, j\'accompagne depuis plus de dix ans des dirigeants, entrepreneurs et particuliers dans leurs transformations. Mon approche combine ecoute active, methodes eprouvees et ancrage concret.', 'ag-starter-coach' ); ?></p>
			<p><?php esc_html_e( 'Chaque accompagnement est unique : je m\'adapte a votre rythme et a vos objectifs, avec pour seule boussole votre progres. Premier rendez-vous toujours gratuit pour valider ensemble la meilleure formule.', 'ag-starter-coach' ); ?></p>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
