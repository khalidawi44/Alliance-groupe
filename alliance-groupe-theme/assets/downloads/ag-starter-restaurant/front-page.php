<?php
/**
 * Front page template — static landing page for the restaurant.
 *
 * @package AG_Starter_Restaurant
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

	<?php if ( ag_starter_restaurant_get_option( 'ag_hero_show' ) ) : ?>
	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php echo esc_html( ag_starter_restaurant_get_option( 'ag_hero_prefix' ) ); ?>
				<span><?php echo esc_html( ag_starter_restaurant_get_option( 'ag_hero_brand' ) ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php echo esc_html( ag_starter_restaurant_get_option( 'ag_hero_subtitle' ) ); ?>
			</p>
			<?php
			$ag_btn_label = ag_starter_restaurant_get_option( 'ag_hero_button' );
			$ag_btn_url   = ag_starter_restaurant_get_option( 'ag_hero_button_url' );
			if ( $ag_btn_label ) :
				?>
				<a href="<?php echo esc_url( $ag_btn_url ); ?>" class="ag-btn"><?php echo esc_html( $ag_btn_label ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php
	// Fallback titres (concatenation pre + em du Customizer pour preserver
	// le mot italique d'origine si la page n'existe pas).
	$carte_fb_title = trim( ag_resto_opt( 'ag_resto_carte_title_pre', 'Notre' ) . ' ' . ag_resto_opt( 'ag_resto_carte_title_em', 'carte' ) );
	list( $carte_title, $carte_lead ) = ag_resto_page_section_text( 'carte', $carte_fb_title, ag_resto_opt( 'ag_resto_carte_lead', 'Entrees, plats et desserts prepares chaque jour avec des produits locaux. Menu du midi a 18 euros, formule du soir a 32 euros.' ) );

	$reserv_fb_title = trim( ag_resto_opt( 'ag_resto_reservation_title_pre', '' ) . ' ' . ag_resto_opt( 'ag_resto_reservation_title_em', 'Reservation' ) );
	list( $reserv_title, $reserv_lead ) = ag_resto_page_section_text( 'reservation', $reserv_fb_title, ag_resto_opt( 'ag_resto_reservation_lead', 'Reservez votre table en ligne ou par telephone au 01 23 45 67 89. Groupes jusqu\'a 20 personnes.' ) );

	$priv_fb_title = trim( ag_resto_opt( 'ag_resto_privatisation_title_pre', '' ) . ' ' . ag_resto_opt( 'ag_resto_privatisation_title_em', 'Privatisation' ) );
	list( $priv_title, $priv_lead ) = ag_resto_page_section_text( 'privatisation', $priv_fb_title, ag_resto_opt( 'ag_resto_privatisation_lead', 'Organisez vos evenements professionnels ou familiaux dans un cadre elegant. Devis gratuit sur demande.' ) );
	?>
	<section class="ag-container" id="ag-carte">
		<div class="ag-cards">
			<div class="ag-card">
				<h2><?php echo ag_resto_render_split_title( $carte_title ); ?></h2>
				<p><?php echo esc_html( $carte_lead ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php echo ag_resto_render_split_title( $reserv_title ); ?></h2>
				<p><?php echo esc_html( $reserv_lead ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php echo ag_resto_render_split_title( $priv_title ); ?></h2>
				<p><?php echo esc_html( $priv_lead ); ?></p>
			</div>
		</div>
	</section>

	<?php
	$hist_fb_title = trim( ag_resto_opt( 'ag_resto_histoire_title_pre', 'Notre' ) . ' ' . ag_resto_opt( 'ag_resto_histoire_title_em', 'histoire' ) );
	list( $hist_title, $hist_lead ) = ag_resto_page_section_text( 'histoire', $hist_fb_title, ag_resto_opt( 'ag_resto_histoire_p1', 'Depuis 2010, notre equipe passionnee vous accueille dans un cadre chaleureux pour vous faire decouvrir une cuisine authentique inspiree du terroir. Chaque plat est prepare avec soin, a partir de produits selectionnes aupres de producteurs locaux.' ) );
	?>
	<section class="ag-info">
		<div class="ag-container">
			<h2><?php echo ag_resto_render_split_title( $hist_title ); ?></h2>
			<p><?php echo esc_html( $hist_lead ); ?></p>
			<p><?php echo esc_html( ag_resto_opt( 'ag_resto_histoire_p2', 'Notre chef compose chaque semaine une carte renouvelee au rythme des saisons. Une cuisine genereuse, des saveurs franches et une ambiance conviviale : voila ce qui fait la difference depuis plus de dix ans.' ) ); ?></p>
		</div>
	</section>

	<?php
	// Show recent posts if there are any (e.g. news, events).
	$recent_posts = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'ignore_sticky_posts' => true,
		)
	);

	if ( $recent_posts->have_posts() ) :
		$actu_fb_title = trim( ag_resto_opt( 'ag_resto_actu_title_pre', 'Actualites' ) . ' ' . ag_resto_opt( 'ag_resto_actu_title_em', 'du restaurant' ) );
		list( $actu_title, $actu_lead ) = ag_resto_page_section_text( 'actualites', $actu_fb_title );
		?>
		<section class="ag-container ag-main">
			<h2 class="ag-entry-title"><?php echo ag_resto_render_split_title( $actu_title ); ?></h2>
			<?php if ( $actu_lead ) : ?>
				<p class="ag-section-lead"><?php echo esc_html( $actu_lead ); ?></p>
			<?php endif; ?>
			<?php
			while ( $recent_posts->have_posts() ) :
				$recent_posts->the_post();
				?>
				<article <?php post_class(); ?>>
					<h3 class="ag-entry-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<div class="ag-entry-meta">
						<?php echo esc_html( get_the_date() ); ?>
					</div>
					<div class="ag-entry-content">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</section>
		<?php
		wp_reset_postdata();
	endif;
	?>

</main>

<?php
get_sidebar();
get_footer();
