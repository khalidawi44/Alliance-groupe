<?php
/**
 * Template page "Zones d'intervention" (variante services a la personne).
 *
 * Layout 2 colonnes : carte Google Maps (secteur) + modes d'intervention
 * (journee, nuit, ponctuel, regulier) + communes couvertes + contact rapide.
 *
 * @package AG_Starter_Domicile
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_domicile_metier_slug', '' );
$ag_metier_nom = ag_domicile_opt( 'ag_domicile_metier_nom', '' );
$ag_hero_image = ag_domicile_hero_url();
if ( ! $ag_hero_image ) {
	$ag_hero_image = get_template_directory_uri() . '/assets/hero.jpg';
}

// Adresse / secteur (par defaut Nantes, modifiable via Customizer).
$map_query = urlencode( ag_domicile_opt( 'ag_domicile_address', 'Nantes, France' ) );
$map_src   = 'https://maps.google.com/maps?q=' . $map_query . '&output=embed&z=12';

// Types d'intervention proposes par specialite.
$modes_by_metier = array(
	'domicile_seniors'  => array( 'En journée (aide au quotidien)', 'La nuit (garde de nuit, présence)', 'Sortie d’hospitalisation', 'Interventions régulières ou ponctuelles' ),
	'domicile_familles' => array( 'Ménage & repassage', 'Garde d’enfants & sortie d’école', 'Aide aux seniors', 'Interventions ponctuelles ou régulières' ),
	'domicile_handicap' => array( 'En journée (aide aux gestes essentiels)', 'La nuit (présence, garde renforcée)', 'Accompagnement aux sorties & rendez-vous', 'Présence continue possible' ),
);
$modes_session = isset( $modes_by_metier[ $metier_slug ] ) ? $modes_by_metier[ $metier_slug ] : $modes_by_metier['domicile_seniors'];

// Communes couvertes (par defaut : Nantes Metropole).
$zones_couvertes = array( 'Nantes', 'Rezé', 'Saint-Herblain', 'Orvault', 'Vertou', 'Bouguenais', 'Saint-Sébastien-sur-Loire', 'Carquefou', 'La Chapelle-sur-Erdre' );
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Où nous intervenons' ); ?></span>
				<h1 class="ag-page-title">Zones <em>d'intervention</em></h1>
				<p class="ag-page-hero-sub">À votre domicile, 7j/7 — de jour comme de nuit.</p>
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
				<iframe src="<?php echo esc_url( $map_src ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Secteur d'intervention — localisation"></iframe>
			</div>
			<div class="ag-zones-list ag-anim">
				<h3>Nos interventions</h3>
				<h2>À votre <em>domicile</em></h2>
				<ul>
					<?php foreach ( $modes_session as $mode ) : ?>
						<li><?php echo esc_html( $mode ); ?></li>
					<?php endforeach; ?>
				</ul>
				<h3 style="margin-top:32px;">Communes couvertes</h3>
				<ul>
					<?php foreach ( $zones_couvertes as $zone ) : ?>
						<li><?php echo esc_html( $zone ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p style="color:#666;font-size:.9rem;">Votre commune n'apparaît pas ? <a href="<?php echo esc_url( ag_domicile_resolve_cta_url( '/contact/' ) ); ?>" style="color:var(--ag-color-accent,#4f9d6b);font-weight:600;">Contactez-nous</a> — nous étendons régulièrement notre secteur.</p>
				<div class="ag-zones-contact-card">
					<strong>📞 Premier contact</strong>
					<p style="margin:0;font-size:1.4rem;font-weight:700;"><?php echo esc_html( ag_domicile_opt( 'ag_domicile_footer_phone', '06 26 14 28 45' ) ); ?></p>
					<p style="margin:6px 0 0;font-size:.88rem;opacity:.9;">Évaluation des besoins à domicile gratuite, sans engagement.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="ag-cta-band">
		<div class="ag-container">
			<h2 class="ag-cta-band__title">Besoin d'aide à domicile ?</h2>
			<p class="ag-cta-band__lead">Évaluation gratuite, crédit d'impôt de 50 %, sans engagement.</p>
			<a href="<?php echo esc_url( ag_domicile_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Demander un devis</a>
		</div>
	</section>

</main>

<?php
// get_sidebar(); // retiré : pas de barre de widgets par défaut sur ce site vitrine
get_footer();
