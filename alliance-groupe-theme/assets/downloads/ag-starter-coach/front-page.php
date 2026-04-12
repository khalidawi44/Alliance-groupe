<?php
/**
 * Front page template — static landing page for the coach / consultant.
 *
 * @package AG_Starter_Coach
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">

	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php esc_html_e( 'Transformez votre potentiel avec', 'ag-starter-coach' ); ?>
				<span><?php esc_html_e( '[Votre Nom]', 'ag-starter-coach' ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php esc_html_e( 'Coaching sur-mesure pour avancer avec clarte, confiance et resultats mesurables.', 'ag-starter-coach' ); ?>
			</p>
			<a href="#ag-services" class="ag-btn"><?php esc_html_e( 'Prendre rendez-vous', 'ag-starter-coach' ); ?></a>
		</div>
	</section>

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
