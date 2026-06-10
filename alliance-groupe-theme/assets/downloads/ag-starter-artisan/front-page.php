<?php
/**
 * Front page template — static landing page for the artisan business.
 *
 * Si un preset metier est applique (Apparence > 🎯 Configuration metier),
 * affiche le rendu "premium" style meilleur-artisan.com : hero photo,
 * grille services 4x2, comment ca marche, temoignages, FAQ.
 * Sinon : fallback sur le rendu historique 3 cards + section about.
 *
 * @package AG_Starter_Artisan
 */

get_header();

$ag_has_preset = class_exists( 'AG_Artisan_Presets' ) && AG_Artisan_Presets::get_active_preset();
$ag_services   = $ag_has_preset ? AG_Artisan_Presets::get_active_services() : array();
$ag_how        = $ag_has_preset ? AG_Artisan_Presets::get_active_how() : array();
$ag_faq        = $ag_has_preset ? AG_Artisan_Presets::get_active_faq() : array();
$ag_stats      = $ag_has_preset ? AG_Artisan_Presets::get_active_stats() : array();
$ag_metier_nom = ag_artisan_opt( 'ag_artisan_metier_nom', '' );
$ag_hero_image = ag_artisan_opt( 'ag_artisan_hero_image', '' );

// Zone editable WP : contenu de la page "accueil" Gutenberg
if ( have_posts() ) : while ( have_posts() ) : the_post();
    if ( trim( get_the_content() ) ) :
        echo "<section class=\"ag-custom-content\" style=\"padding:50px 24px;background:#fff;\"><div style=\"max-width:1180px;margin:0 auto;\">";
        the_content();
        echo "</div></section>";
    endif;
endwhile; rewind_posts(); endif; ?>

<main id="ag-main" class="<?php echo $ag_has_preset ? 'ag-main ag-main--premium' : 'ag-main'; ?>" role="main">

	<?php if ( ag_starter_artisan_get_option( 'ag_hero_show' ) ) : ?>
	<?php if ( $ag_has_preset && $ag_hero_image ) : ?>
		<!-- Hero photo lifestyle style meilleur-artisan.com -->
		<section class="ag-hero-pro" style="background-image:linear-gradient(rgba(0,0,0,.35),rgba(0,0,0,.55)),url('<?php echo esc_url( $ag_hero_image ); ?>');">
			<div class="ag-container">
				<h1 class="ag-hero-pro__title">
					<?php echo esc_html( ag_starter_artisan_get_option( 'ag_hero_prefix' ) ); ?>
					<em><?php echo esc_html( ag_starter_artisan_get_option( 'ag_hero_brand' ) ); ?></em>
				</h1>
				<p class="ag-hero-pro__subtitle">
					<?php echo esc_html( ag_starter_artisan_get_option( 'ag_hero_subtitle' ) ); ?>
				</p>
				<?php
				$ag_btn_label = ag_starter_artisan_get_option( 'ag_hero_button' );
				$ag_btn_url   = ag_starter_artisan_get_option( 'ag_hero_button_url' );
				if ( $ag_btn_label ) : ?>
					<a href="<?php echo esc_url( $ag_btn_url ); ?>" class="ag-btn-pro"><?php echo esc_html( $ag_btn_label ); ?></a>
				<?php endif; ?>

				<?php if ( ! empty( $ag_stats ) ) : ?>
					<div class="ag-hero-stats">
						<?php foreach ( $ag_stats as $stat ) : ?>
							<div class="ag-hero-stat">
								<span class="ag-hero-stat__value"><?php echo esc_html( $stat['value'] ); ?></span>
								<span class="ag-hero-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php else : ?>
		<!-- Hero historique (back-compat) -->
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
				if ( $ag_btn_label ) : ?>
					<a href="<?php echo esc_url( $ag_btn_url ); ?>" class="ag-btn"><?php echo esc_html( $ag_btn_label ); ?></a>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! empty( $ag_services ) ) : ?>
		<!-- Grille services 4x2 style meilleur-artisan.com -->
		<section class="ag-container ag-services-grid-wrap" id="ag-services">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Nos <span>services</span></h2>
				<p class="ag-services-grid-lead">
					<?php if ( $ag_metier_nom ) : ?>
						Découvrez les prestations proposées par votre <?php echo esc_html( strtolower( $ag_metier_nom ) ); ?>.
					<?php else : ?>
						Tous nos services en un coup d'œil — devis gratuit sur demande.
					<?php endif; ?>
				</p>
			</div>
			<div class="ag-services-grid">
				<?php foreach ( $ag_services as $svc ) :
					// Chaque service est cliquable. URL : 'url' du service si defini,
					// sinon page Devis avec le slug service pre-rempli en query string.
					$svc_url = ! empty( $svc['url'] ) ? $svc['url'] : '';
					if ( ! $svc_url ) {
						$devis_page = get_page_by_path( 'devis' );
						$svc_url    = $devis_page ? get_permalink( $devis_page ) : home_url( '/devis/' );
						$svc_url    = add_query_arg( 'service', sanitize_title( $svc['title'] ), $svc_url );
					}
				?>
					<a class="ag-service-card ag-anim" href="<?php echo esc_url( $svc_url ); ?>">
						<div class="ag-service-card__icon"><?php echo esc_html( isset( $svc['emoji'] ) ? $svc['emoji'] : '🔧' ); ?></div>
						<h3 class="ag-service-card__title"><?php echo esc_html( isset( $svc['title'] ) ? $svc['title'] : '' ); ?></h3>
						</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php else : ?>
		<?php
		// Fallback historique : 3 cards textes depuis les pages WP
		$prest_fb_title = trim( ag_artisan_opt( 'ag_artisan_prestations_title_pre', 'Nos' ) . ' ' . ag_artisan_opt( 'ag_artisan_prestations_title_em', 'prestations' ) );
		list( $prest_title, $prest_lead ) = ag_artisan_page_section_text( 'prestations', $prest_fb_title, ag_artisan_opt( 'ag_artisan_prestations_lead', '' ) );
		$zones_fb_title = trim( ag_artisan_opt( 'ag_artisan_zones_title_pre', 'Zones' ) . ' ' . ag_artisan_opt( 'ag_artisan_zones_title_em', 'd\'intervention' ) );
		list( $zones_title, $zones_lead ) = ag_artisan_page_section_text( 'zones-intervention', $zones_fb_title, ag_artisan_opt( 'ag_artisan_zones_lead', '' ) );
		$real_fb_title = trim( ag_artisan_opt( 'ag_artisan_realisations_title_pre', 'Nos' ) . ' ' . ag_artisan_opt( 'ag_artisan_realisations_title_em', 'realisations' ) );
		list( $real_title, $real_lead ) = ag_artisan_page_section_text( 'realisations', $real_fb_title, ag_artisan_opt( 'ag_artisan_realisations_lead', '' ) );
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
	<?php endif; ?>

	<?php if ( ! empty( $ag_how ) ) : ?>
		<!-- Comment ça marche : 3 étapes -->
		<section class="ag-howit-wrap">
			<div class="ag-container">
				<div class="ag-services-grid-header ag-anim">
					<h2 class="ag-services-grid-title">Comment ça <span>marche</span> ?</h2>
					<p class="ag-services-grid-lead">3 étapes simples pour démarrer votre projet.</p>
				</div>
				<div class="ag-howit-grid">
					<?php foreach ( $ag_how as $i => $step ) : ?>
						<div class="ag-howit-card ag-anim">
							<div class="ag-howit-num"><?php echo (int) ( $i + 1 ); ?></div>
							<div class="ag-howit-emoji"><?php echo esc_html( $step['emoji'] ); ?></div>
							<h3 class="ag-howit-title"><?php echo esc_html( $step['title'] ); ?></h3>
							<p class="ag-howit-desc"><?php echo esc_html( $step['desc'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $ag_has_preset ) :
		$t1 = AG_Artisan_Presets::get_testimonial( 1 );
		$t2 = AG_Artisan_Presets::get_testimonial( 2 );
		$t3 = AG_Artisan_Presets::get_testimonial( 3 );
		if ( $t1 || $t2 || $t3 ) : ?>
		<!-- Témoignages clients -->
		<section class="ag-testi-wrap">
			<div class="ag-container">
				<div class="ag-services-grid-header ag-anim">
					<h2 class="ag-services-grid-title">Ils nous font <span>confiance</span></h2>
					<p class="ag-services-grid-lead">⭐⭐⭐⭐⭐ Plus de 200 avis clients vérifiés.</p>
				</div>
				<div class="ag-testi-grid">
					<?php foreach ( array( $t1, $t2, $t3 ) as $t ) :
						if ( ! $t ) continue; ?>
						<div class="ag-testi-card ag-anim">
							<div class="ag-testi-stars">★★★★★</div>
							<p class="ag-testi-text">« <?php echo esc_html( $t['text'] ); ?> »</p>
							<p class="ag-testi-author"><strong><?php echo esc_html( $t['name'] ); ?></strong><?php if ( $t['city'] ) : ?> · <?php echo esc_html( $t['city'] ); ?><?php endif; ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; endif; ?>

	<?php if ( ! empty( $ag_faq ) ) : ?>
		<!-- FAQ accordéon -->
		<section class="ag-faq-wrap">
			<div class="ag-container ag-faq-container">
				<div class="ag-services-grid-header ag-anim">
					<h2 class="ag-services-grid-title">Questions <span>fréquentes</span></h2>
				</div>
				<div class="ag-faq-list">
					<?php foreach ( $ag_faq as $item ) : ?>
						<details class="ag-faq-item ag-anim">
							<summary class="ag-faq-q"><?php echo esc_html( $item['q'] ); ?></summary>
							<div class="ag-faq-a"><?php echo esc_html( $item['a'] ); ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! $ag_has_preset ) :
		// Section "à propos" historique conservée uniquement en mode legacy
		$about_fb_title = trim( ag_artisan_opt( 'ag_artisan_about_title_pre', 'Qui' ) . ' ' . ag_artisan_opt( 'ag_artisan_about_title_em', 'sommes-nous' ) );
		list( $about_title, $about_lead ) = ag_artisan_page_section_text( 'qui-sommes-nous', $about_fb_title, ag_artisan_opt( 'ag_artisan_about_p1', '' ) );
		?>
		<section class="ag-info">
			<div class="ag-container">
				<h2><?php echo ag_artisan_render_split_title( $about_title ); ?></h2>
				<p><?php echo esc_html( $about_lead ); ?></p>
				<p><?php echo esc_html( ag_artisan_opt( 'ag_artisan_about_p2', '' ) ); ?></p>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
get_sidebar();
get_footer();
