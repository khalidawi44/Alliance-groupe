<?php
/**
 * Template page Contact (variante coach) : coordonnées + carte Google Maps +
 * lien direct vers le formulaire séance découverte.
 *
 * @package AG_Starter_Coach
 */

get_header();

$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );
$map_query     = urlencode( ag_coach_opt( 'ag_coach_address', 'Paris, France' ) );
$map_src       = 'https://maps.google.com/maps?q=' . $map_query . '&output=embed&z=15';
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag">Contact</span>
				<h1 class="ag-page-title">Nous <em>contacter</em></h1>
				<p class="ag-page-hero-sub">Séance découverte offerte, réponse sous 24h, à votre écoute.</p>
			</div>
		</section>

		<?php if ( trim( get_the_content() ) ) : ?>
			<section class="ag-page-intro">
				<div class="ag-container">
					<?php the_content(); ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>

	<section class="ag-zones-wrap">
		<div class="ag-zones-grid">
			<div class="ag-zones-map ag-anim">
				<iframe src="<?php echo esc_url( $map_src ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Localisation"></iframe>
			</div>
			<div class="ag-zones-list ag-anim">
				<h3>Nous trouver</h3>
				<h2>Coordonnées</h2>
				<ul style="list-style:none;padding:0;">
					<li style="border-bottom:1px solid #ececec;padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#2E86AB);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">📍 Cabinet</strong>
						<span style="color:#333;">[Votre cabinet]<br>12 rue de la Confiance<br>75001 Paris, France</span>
					</li>
					<li style="border-bottom:1px solid #ececec;padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#2E86AB);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">📞 Téléphone</strong>
						<a href="tel:0600000000" style="color:#333;font-size:1.1rem;font-weight:600;text-decoration:none;">06 00 00 00 00</a>
					</li>
					<li style="border-bottom:1px solid #ececec;padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#2E86AB);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">✉️ Email</strong>
						<a href="mailto:contact@votre-cabinet.fr" style="color:#333;text-decoration:none;">contact@votre-cabinet.fr</a>
					</li>
					<li style="padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#2E86AB);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">🕒 Horaires</strong>
						<span style="color:#333;">Lundi - Vendredi : 9h - 19h<br>Samedi : sur rendez-vous<br>Visio disponible</span>
					</li>
				</ul>
				<p style="margin-top:24px;">
					<a href="<?php echo esc_url( ag_coach_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro" style="display:inline-block;">🚀 Réserver une séance découverte →</a>
				</p>
			</div>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
