<?php
/**
 * Front page template — static landing page for the restaurant.
 *
 * @package AG_Starter_Restaurant
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">

	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php esc_html_e( 'Bienvenue chez', 'ag-starter-restaurant' ); ?>
				<span><?php esc_html_e( '[Votre Restaurant]', 'ag-starter-restaurant' ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php esc_html_e( 'Cuisine authentique faite maison, produits frais et de saison, au coeur de votre ville.', 'ag-starter-restaurant' ); ?>
			</p>
			<a href="#ag-carte" class="ag-btn"><?php esc_html_e( 'Decouvrir la carte', 'ag-starter-restaurant' ); ?></a>
		</div>
	</section>

	<section class="ag-container" id="ag-carte">
		<div class="ag-cards">
			<div class="ag-card">
				<h2><?php esc_html_e( 'Notre carte', 'ag-starter-restaurant' ); ?></h2>
				<p><?php esc_html_e( 'Entrees, plats et desserts prepares chaque jour avec des produits locaux. Menu du midi a 18 euros, formule du soir a 32 euros.', 'ag-starter-restaurant' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Reservation', 'ag-starter-restaurant' ); ?></h2>
				<p><?php esc_html_e( 'Reservez votre table en ligne ou par telephone au 01 23 45 67 89. Groupes jusqu\'a 20 personnes.', 'ag-starter-restaurant' ); ?></p>
			</div>
			<div class="ag-card">
				<h2><?php esc_html_e( 'Privatisation', 'ag-starter-restaurant' ); ?></h2>
				<p><?php esc_html_e( 'Organisez vos evenements professionnels ou familiaux dans un cadre elegant. Devis gratuit sur demande.', 'ag-starter-restaurant' ); ?></p>
			</div>
		</div>
	</section>

	<section class="ag-info">
		<div class="ag-container">
			<h2><?php esc_html_e( 'Notre histoire', 'ag-starter-restaurant' ); ?></h2>
			<p><?php esc_html_e( 'Depuis 2010, notre equipe passionnee vous accueille dans un cadre chaleureux pour vous faire decouvrir une cuisine authentique inspiree du terroir. Chaque plat est prepare avec soin, a partir de produits selectionnes aupres de producteurs locaux.', 'ag-starter-restaurant' ); ?></p>
			<p><?php esc_html_e( 'Notre chef compose chaque semaine une carte renouvelee au rythme des saisons. Une cuisine genereuse, des saveurs franches et une ambiance conviviale : voila ce qui fait la difference depuis plus de dix ans.', 'ag-starter-restaurant' ); ?></p>
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
		?>
		<section class="ag-container ag-main">
			<h2 class="ag-entry-title"><?php esc_html_e( 'Actualites du restaurant', 'ag-starter-restaurant' ); ?></h2>
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
