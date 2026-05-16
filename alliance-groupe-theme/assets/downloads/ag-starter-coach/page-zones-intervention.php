<?php
/**
 * Template page "Zones d'intervention" (variante coach).
 *
 * Layout 2 colonnes : carte Google Maps (cabinet) + liste des modes
 * de séance (présentiel, visio, domicile) + zones couvertes + contact rapide.
 *
 * @package AG_Starter_Coach
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_coach_metier_slug', '' );
$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );

// Adresse cabinet (par défaut Paris centre, modifiable via Customizer).
$map_query = urlencode( ag_coach_opt( 'ag_coach_address', 'Paris, France' ) );
$map_src   = 'https://maps.google.com/maps?q=' . $map_query . '&output=embed&z=13';

// Modes de séance dispo par spécialité coach
$modes_by_metier = array(
	'coach_general'  => array( 'En cabinet', 'En visio (toute la France + DOM-TOM + International)', 'À domicile sur demande (zones proches)' ),
	'coach_sportif'  => array( 'En salle de sport partenaire', 'À domicile (matériel inclus)', 'En extérieur (running, vélo, natation)', 'En visio (suivi programme)' ),
	'coach_vie'      => array( 'En cabinet', 'En extérieur (marche-coaching)', 'En visio (toute la France + International)', 'Ateliers de groupe trimestriels' ),
	'coach_business' => array( 'En entreprise (sur site)', 'En cabinet', 'En visio (équipes distribuées)', 'Séminaire offsite (1-3 jours)' ),
	'coach_mental'   => array( 'En cabinet', 'En visio (toute la France + International)', 'Sur site sportif/entreprise (préparation compétition/évènement)', 'Groupes méditation hebdomadaires' ),
);
$modes_session = isset( $modes_by_metier[ $metier_slug ] ) ? $modes_by_metier[ $metier_slug ] : $modes_by_metier['coach_general'];

// Zones (villes/communes) couvertes en présentiel
$zones_couvertes = array( 'Paris', 'Boulogne-Billancourt', 'Issy-les-Moulineaux', 'Vincennes', 'Saint-Mandé', 'Charenton-le-Pont', 'Montrouge', 'Levallois-Perret', 'Neuilly-sur-Seine' );
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Où nous rencontrer' ); ?></span>
				<h1 class="ag-page-title">Modes <em>de séance</em></h1>
				<p class="ag-page-hero-sub">Présentiel, visio ou à domicile — la solution qui s'adapte à votre vie.</p>
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
				<iframe src="<?php echo esc_url( $map_src ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Cabinet — localisation"></iframe>
			</div>
			<div class="ag-zones-list ag-anim">
				<h3>Comment se déroule une séance</h3>
				<h2>Plusieurs <em>formats</em></h2>
				<ul>
					<?php foreach ( $modes_session as $mode ) : ?>
						<li><?php echo esc_html( $mode ); ?></li>
					<?php endforeach; ?>
				</ul>
				<h3 style="margin-top:32px;">Zones couvertes en présentiel</h3>
				<ul>
					<?php foreach ( $zones_couvertes as $zone ) : ?>
						<li><?php echo esc_html( $zone ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p style="color:#666;font-size:.9rem;">Hors zone ? <a href="<?php echo esc_url( ag_coach_resolve_cta_url( '/contact/' ) ); ?>" style="color:var(--ag-color-accent,#2E86AB);font-weight:600;">Visio disponible partout</a> — France, DOM-TOM, international.</p>
				<div class="ag-zones-contact-card">
					<strong>📞 Premier contact</strong>
					<p style="margin:0;font-size:1.4rem;font-weight:700;">06 00 00 00 00</p>
					<p style="margin:6px 0 0;font-size:.88rem;opacity:.9;">Séance découverte offerte — 30 minutes pour échanger sur vos besoins.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="ag-cta-band">
		<div class="ag-container">
			<h2 class="ag-cta-band__title">Prêt à démarrer ?</h2>
			<p class="ag-cta-band__lead">Séance découverte offerte, sans engagement.</p>
			<a href="<?php echo esc_url( ag_coach_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Réserver ma séance</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
