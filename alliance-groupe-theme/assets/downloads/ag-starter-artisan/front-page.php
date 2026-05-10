<?php
/**
 * Front page template — static landing page for the artisan business.
 *
 * @package AG_Starter_Artisan
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

	<?php
	$prest_fb_title = trim( ag_artisan_opt( 'ag_artisan_prestations_title_pre', 'Nos' ) . ' ' . ag_artisan_opt( 'ag_artisan_prestations_title_em', 'prestations' ) );
	list( $prest_title, $prest_lead ) = ag_artisan_page_section_text( 'prestations', $prest_fb_title, ag_artisan_opt( 'ag_artisan_prestations_lead', 'Renovation, installation, entretien : nous intervenons pour tous vos travaux avec serieux et precision. Devis gratuit sous 24h.' ) );

	$zones_fb_title = trim( ag_artisan_opt( 'ag_artisan_zones_title_pre', 'Zones' ) . ' ' . ag_artisan_opt( 'ag_artisan_zones_title_em', 'd\'intervention' ) );
	list( $zones_title, $zones_lead ) = ag_artisan_page_section_text( 'zones-intervention', $zones_fb_title, ag_artisan_opt( 'ag_artisan_zones_lead', 'Nous intervenons dans toute votre region, y compris en urgence. Appelez-nous au 06 00 00 00 00 pour toute demande rapide.' ) );

	$real_fb_title = trim( ag_artisan_opt( 'ag_artisan_realisations_title_pre', 'Nos' ) . ' ' . ag_artisan_opt( 'ag_artisan_realisations_title_em', 'realisations' ) );
	list( $real_title, $real_lead ) = ag_artisan_page_section_text( 'realisations', $real_fb_title, ag_artisan_opt( 'ag_artisan_realisations_lead', 'Decouvrez nos chantiers recents : renovations de maison, installations techniques et travaux sur-mesure pour particuliers et professionnels.' ) );
	?>
	<section class="ag-container" id="ag-services">
		<div class="ag-cards">
			<div class="ag-card">
				<h2><?php echo ag_artisan_render_split_title( $prest_title ); ?></h2>
				<p><?php echo esc_html( $prest_lead ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php echo ag_artisan_render_split_title( $zones_title ); ?></h2>
				<p><?php echo esc_html( $zones_lead ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php echo ag_artisan_render_split_title( $real_title ); ?></h2>
				<p><?php echo esc_html( $real_lead ); ?></p>
			</div>
		</div>
	</section>

	<?php
	$about_fb_title = trim( ag_artisan_opt( 'ag_artisan_about_title_pre', 'Qui' ) . ' ' . ag_artisan_opt( 'ag_artisan_about_title_em', 'sommes-nous' ) );
	list( $about_title, $about_lead ) = ag_artisan_page_section_text( 'qui-sommes-nous', $about_fb_title, ag_artisan_opt( 'ag_artisan_about_p1', 'Depuis plus de dix ans, notre equipe d\'artisans qualifies accompagne particuliers et professionnels dans tous leurs projets de travaux. Rigueur, transparence sur les prix et respect des delais : voila notre engagement.' ) );
	?>
	<section class="ag-info">
		<div class="ag-container">
			<h2><?php echo ag_artisan_render_split_title( $about_title ); ?></h2>
			<p><?php echo esc_html( $about_lead ); ?></p>
			<p><?php echo esc_html( ag_artisan_opt( 'ag_artisan_about_p2', 'Nous mettons un point d\'honneur a livrer des chantiers propres et conformes aux normes. Chaque intervention est suivie personnellement du devis a la livraison finale.' ) ); ?></p>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
