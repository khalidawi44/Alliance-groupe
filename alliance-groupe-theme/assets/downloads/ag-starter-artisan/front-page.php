<?php
/**
 * Front page template — static landing page for the artisan business.
 *
 * @package AG_Starter_Artisan
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">

	<?php if ( ag_starter_artisan_get_option( 'ag_hero_show' ) ) : ?>
	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php echo esc_html( ag_starter_artisan_get_option( 'ag_hero_prefix' ) ); ?>
				<span><?php echo esc_html( ag_starter_artisan_get_option( 'ag_hero_brand' ) ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php echo esc_html( ag_starter_artisan_get_option( 'ag_hero_subtitle' ) ); ?>
			</p>
			<?php
			$ag_btn_label = ag_starter_artisan_get_option( 'ag_hero_button' );
			$ag_btn_url   = ag_starter_artisan_get_option( 'ag_hero_button_url' );
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
				<h2><?php esc_html_e( 'Nos prestations', 'ag-starter-artisan' ); ?></h2>
				<p><?php esc_html_e( 'Renovation, installation, entretien : nous intervenons pour tous vos travaux avec serieux et precision. Devis gratuit sous 24h.', 'ag-starter-artisan' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Zones d\'intervention', 'ag-starter-artisan' ); ?></h2>
				<p><?php esc_html_e( 'Nous intervenons dans toute votre region, y compris en urgence. Appelez-nous au 06 00 00 00 00 pour toute demande rapide.', 'ag-starter-artisan' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Nos realisations', 'ag-starter-artisan' ); ?></h2>
				<p><?php esc_html_e( 'Decouvrez nos chantiers recents : renovations de maison, installations techniques et travaux sur-mesure pour particuliers et professionnels.', 'ag-starter-artisan' ); ?></p>
			</div>
		</div>
	</section>

	<section class="ag-info">
		<div class="ag-container">
			<h2><?php esc_html_e( 'Qui sommes-nous', 'ag-starter-artisan' ); ?></h2>
			<p><?php esc_html_e( 'Depuis plus de dix ans, notre equipe d\'artisans qualifies accompagne particuliers et professionnels dans tous leurs projets de travaux. Rigueur, transparence sur les prix et respect des delais : voila notre engagement.', 'ag-starter-artisan' ); ?></p>
			<p><?php esc_html_e( 'Nous mettons un point d\'honneur a livrer des chantiers propres et conformes aux normes. Chaque intervention est suivie personnellement du devis a la livraison finale.', 'ag-starter-artisan' ); ?></p>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
