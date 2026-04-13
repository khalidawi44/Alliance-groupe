<?php
/**
 * Front page template — static landing page for the law firm.
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">

	<?php if ( ag_starter_avocat_get_option( 'ag_hero_show' ) ) : ?>
	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php echo esc_html( ag_starter_avocat_get_option( 'ag_hero_prefix' ) ); ?>
				<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_hero_brand' ) ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php echo esc_html( ag_starter_avocat_get_option( 'ag_hero_subtitle' ) ); ?>
			</p>
			<?php
			$ag_btn_label = ag_starter_avocat_get_option( 'ag_hero_button' );
			$ag_btn_url   = ag_starter_avocat_get_option( 'ag_hero_button_url' );
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
				<h2><?php esc_html_e( 'Domaines d\'expertise', 'ag-starter-avocat' ); ?></h2>
				<p><?php esc_html_e( 'Droit des affaires, droit du travail, droit de la famille, droit immobilier. Conseil et representation devant les juridictions civiles et commerciales.', 'ag-starter-avocat' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Honoraires', 'ag-starter-avocat' ); ?></h2>
				<p><?php esc_html_e( 'Premier rendez-vous a 80 EUR HT. Forfaits, honoraires au temps passe ou de resultat selon le dossier. Devis transparent avant tout engagement.', 'ag-starter-avocat' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Prendre rendez-vous', 'ag-starter-avocat' ); ?></h2>
				<p><?php esc_html_e( 'Consultation au cabinet ou en visio. Reservez en ligne, par telephone au 01 23 45 67 89 ou par email. Confidentialite garantie.', 'ag-starter-avocat' ); ?></p>
			</div>
		</div>
	</section>

	<section class="ag-info">
		<div class="ag-container">
			<h2><?php esc_html_e( 'Notre cabinet', 'ag-starter-avocat' ); ?></h2>
			<p><?php esc_html_e( 'Inscrit au barreau depuis plus de quinze ans, le cabinet defend les interets des particuliers et des entreprises avec rigueur, ecoute et discretion. Notre approche : analyser chaque dossier en profondeur, vous expliquer clairement vos options, et batir avec vous la strategie la plus efficace.', 'ag-starter-avocat' ); ?></p>
			<p><?php esc_html_e( 'Nous mettons un point d\'honneur a la transparence sur les honoraires, au respect strict des delais et a la confidentialite absolue des echanges. Premier rendez-vous toujours en presence du Maitre.', 'ag-starter-avocat' ); ?></p>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
