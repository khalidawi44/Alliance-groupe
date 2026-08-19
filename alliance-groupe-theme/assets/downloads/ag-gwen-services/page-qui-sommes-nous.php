<?php
/**
 * Template page "Qui sommes-nous" (variante services a la personne).
 *
 * Layout 2 colonnes (texte + photo) + reperes/engagements + CTA.
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

// Photo section "à propos" : 1) image Customizer, 2) convention assets/about.jpg
// (il suffit de déposer ce fichier dans le thème), 3) fallback assets/hero.jpg.
$about_photo = ag_domicile_opt( 'ag_domicile_about_image', '' );
if ( ! $about_photo && file_exists( get_theme_file_path( 'assets/about.jpg' ) ) ) {
	$about_photo = get_template_directory_uri() . '/assets/about.jpg';
}
if ( ! $about_photo ) {
	$about_photo = get_template_directory_uri() . '/assets/hero.jpg';
}

// Repères / engagements par spécialité.
$timelines = array(
	'domicile_seniors' => array(
		array( 'year' => 'Agréé', 'text' => "Service déclaré au titre des services à la personne — crédit d'impôt de 50 %." ),
		array( 'year' => 'Qualifié', 'text' => "Auxiliaires de vie et aides à domicile sélectionnés, formés et suivis dans la durée." ),
		array( 'year' => 'Référent', 'text' => "Un intervenant référent par famille, avec une doublure formée pour la continuité." ),
		array( 'year' => '7j/7', 'text' => "Interventions de jour comme de nuit, jours fériés compris, remplacements assurés." ),
		array( 'year' => "Sur-mesure", 'text' => "Un plan d'aide adapté à chaque personne, réévalué régulièrement avec la famille." ),
	),
	'domicile_familles' => array(
		array( 'year' => 'Agréé', 'text' => "Service déclaré services à la personne — crédit d'impôt de 50 % sur toutes les prestations." ),
		array( 'year' => 'Polyvalent', 'text' => "Ménage, garde d'enfants, aide aux seniors, aide au handicap : un seul interlocuteur." ),
		array( 'year' => 'Qualifié', 'text' => "Des intervenants recrutés avec exigence, formés et encadrés." ),
		array( 'year' => 'Souple', 'text' => "Interventions ponctuelles ou régulières, ajustables à tout moment." ),
		array( 'year' => "Simple", 'text' => "Un seul contact, une seule facture, l'avance immédiate du crédit d'impôt." ),
	),
	'domicile_handicap' => array(
		array( 'year' => 'Agréé', 'text' => "Service déclaré services à la personne — crédit d'impôt de 50 %." ),
		array( 'year' => 'Formé', 'text' => "Accompagnants formés au handicap, à la dépendance et à la communication adaptée." ),
		array( 'year' => 'Coordonné', 'text' => "Travail en lien avec la famille et l'équipe médico-sociale (PCH, APA, MDPH)." ),
		array( 'year' => 'Continu', 'text' => "Présence de jour, de nuit ou continue, avec remplacements garantis." ),
		array( 'year' => "Respect", 'text' => "Autonomie et dignité au cœur de chaque accompagnement." ),
	),
);
$timeline = isset( $timelines[ $metier_slug ] ) ? $timelines[ $metier_slug ] : $timelines['domicile_seniors'];
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Notre maison' ); ?></span>
				<h1 class="ag-page-title">Qui <em>sommes-nous</em></h1>
				<p class="ag-page-hero-sub">Une présence humaine et fiable, à vos côtés sur la durée.</p>
			</div>
		</section>
	<?php endwhile; ?>

	<section class="ag-about-wrap">
		<div class="ag-about-grid">
			<div class="ag-about-text ag-anim">
				<h3>Notre engagement</h3>
				<h2>Permettre à chacun de bien vivre chez soi, entouré et en sécurité.</h2>
				<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
			</div>
			<div class="ag-about-photo ag-anim" style="background-image:url('<?php echo esc_url( $about_photo ); ?>');"></div>
		</div>
	</section>

	<section class="ag-timeline">
		<div class="ag-container">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Nos <em>engagements</em></h2>
			</div>
			<div class="ag-timeline-grid">
				<?php foreach ( $timeline as $step ) : ?>
					<div class="ag-timeline-item ag-anim">
						<p class="ag-timeline-year"><?php echo esc_html( $step['year'] ); ?></p>
						<p class="ag-timeline-text"><?php echo esc_html( $step['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="ag-cta-band">
		<div class="ag-container">
			<h2 class="ag-cta-band__title">Parlons de votre situation</h2>
			<p class="ag-cta-band__lead">Évaluation à domicile gratuite — sans engagement, pour cerner vos besoins.</p>
			<a href="<?php echo esc_url( ag_domicile_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Demander un devis gratuit</a>
		</div>
	</section>

</main>

<?php
// get_sidebar(); // retiré : pas de barre de widgets par défaut sur ce site vitrine
get_footer();
