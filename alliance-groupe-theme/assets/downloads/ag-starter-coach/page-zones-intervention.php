<?php
/**
 * Template page "Zones d'intervention".
 *
 * Layout 2 colonnes : carte Google Maps embed (iframe) + liste des zones
 * couvertes + carte de contact urgences.
 *
 * @package AG_Starter_Artisan
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_coach_metier_slug', '' );
$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );

// Adresse par defaut affichee sur la carte (modifiable via Customizer
// quand on aura ajoute le champ — pour l'instant Paris centre).
$map_query = urlencode( ag_coach_opt( 'ag_coach_address', 'Paris, France' ) );
$map_src   = 'https://maps.google.com/maps?q=' . $map_query . '&output=embed&z=12';

// Zones (villes/communes) génériques couvertes (un client peut éditer
// directement la page WP pour personnaliser ; ces valeurs sont des
// placeholders crédibles).
$zones_couvertes = array( 'Paris', 'Boulogne-Billancourt', 'Issy-les-Moulineaux', 'Vincennes', 'Saint-Mandé', 'Charenton-le-Pont', 'Montrouge', 'Levallois-Perret' );
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Couverture' ); ?></span>
				<h1 class="ag-page-title">Zones <em>d'intervention</em></h1>
				<p class="ag-page-hero-sub">Intervention rapide dans toute la région.</p>
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
				<iframe src="<?php echo esc_url( $map_src ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Carte zone d'intervention"></iframe>
			</div>
			<div class="ag-zones-list ag-anim">
				<h3>Communes couvertes</h3>
				<h2>Nous intervenons à</h2>
				<ul>
					<?php foreach ( $zones_couvertes as $zone ) : ?>
						<li><?php echo esc_html( $zone ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p style="color:#666;font-size:.9rem;">Hors zone ? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="color:var(--ag-color-accent,#F37A1F);font-weight:600;">Contactez-nous</a> — nous étudions chaque demande.</p>
				<div class="ag-zones-contact-card">
					<strong>📞 Urgences 7j/7</strong>
					<p style="margin:0;font-size:1.4rem;font-weight:700;">06 00 00 00 00</p>
					<p style="margin:6px 0 0;font-size:.88rem;opacity:.9;">Astreinte nuit + week-end pour les dépannages prioritaires.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="ag-cta-band">
		<div class="ag-container">
			<h2 class="ag-cta-band__title">Une intervention à prévoir ?</h2>
			<p class="ag-cta-band__lead">Devis gratuit + fourchette de prix instantanée selon votre code postal.</p>
			<a href="<?php echo esc_url( home_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Obtenir mon devis →</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
