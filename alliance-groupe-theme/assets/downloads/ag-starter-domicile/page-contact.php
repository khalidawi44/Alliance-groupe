<?php
/**
 * Template page Contact (variante domicile) : coordonnées + carte Google Maps +
 * lien direct vers le formulaire séance découverte.
 *
 * @package AG_Starter_Domicile
 */

get_header();

$ag_metier_nom = ag_domicile_opt( 'ag_domicile_metier_nom', '' );
$ag_hero_image = ag_domicile_hero_url();
$map_query     = urlencode( ag_domicile_opt( 'ag_domicile_address', 'Nantes, France' ) );
$map_src       = 'https://maps.google.com/maps?q=' . $map_query . '&output=embed&z=15';
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag">Contact</span>
				<h1 class="ag-page-title">Nous <em>contacter</em></h1>
				<p class="ag-page-hero-sub">Évaluation à domicile gratuite, réponse rapide, à votre écoute.</p>
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
						<strong style="display:block;color:var(--ag-color-accent,#4f9d6b);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">📍 Adresse</strong>
						<span style="color:#333;">Douceur de Vie<br>1 rue des Tilleuls<br>44000 Nantes, France</span>
					</li>
					<li style="border-bottom:1px solid #ececec;padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#4f9d6b);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">📞 Téléphone</strong>
						<a href="tel:0600000000" style="color:#333;font-size:1.1rem;font-weight:600;text-decoration:none;">06 00 00 00 00</a>
					</li>
					<li style="border-bottom:1px solid #ececec;padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#4f9d6b);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">✉️ Email</strong>
						<a href="mailto:contact@douceur-de-vie.fr" style="color:#333;text-decoration:none;">contact@douceur-de-vie.fr</a>
					</li>
					<li style="padding:14px 0;">
						<strong style="display:block;color:var(--ag-color-accent,#4f9d6b);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">🕒 Horaires</strong>
						<span style="color:#333;">Interventions 7j/7, jour et nuit<br>Accueil téléphonique : Lun-Ven 8h-19h<br>Samedi : sur rendez-vous</span>
					</li>
				</ul>
				<p style="margin-top:24px;">
					<a href="<?php echo esc_url( ag_domicile_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro" style="display:inline-block;">🚀 Demander un devis gratuit</a>
				</p>
			</div>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
