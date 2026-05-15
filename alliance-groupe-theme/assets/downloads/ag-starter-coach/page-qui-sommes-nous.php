<?php
/**
 * Template page "Qui sommes-nous" (variante coach).
 *
 * Layout 2 colonnes (texte + photo coach) + timeline parcours +
 * témoignages + CTA. Photo et timeline dépendent de la spécialité
 * coach active (général, sportif, vie, business, mental).
 *
 * @package AG_Starter_Coach
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_coach_metier_slug', '' );
$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );

// Photo "coach en séance / cabinet" Unsplash par spécialité coach
$about_photos = array(
	'coach_general'  => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=900&q=80',
	'coach_sportif'  => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=900&q=80',
	'coach_vie'      => 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=900&q=80',
	'coach_business' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=900&q=80',
	'coach_mental'   => 'https://images.unsplash.com/photo-1545389336-cf090694435e?w=900&q=80',
);
$about_photo = isset( $about_photos[ $metier_slug ] ) ? $about_photos[ $metier_slug ] : $about_photos['coach_general'];

// Timeline parcours par spécialité coach (4-5 étapes : formation, certif, exp)
$timelines = array(
	'coach_general' => array(
		array( 'year' => '2014', 'text' => "Première formation coach professionnel certifiée (RNCP niveau 6, école Linkup)." ),
		array( 'year' => '2017', 'text' => "Certification ICF (International Coach Federation) — Associate Certified Coach." ),
		array( 'year' => '2020', 'text' => "Spécialisation accompagnement de transition : 80 personnes coachées sur 3 ans." ),
		array( 'year' => '2024', 'text' => "Plus de 200 personnes accompagnées. Note 4,9/5 sur Google Avis vérifiés." ),
		array( 'year' => "Aujourd'hui", 'text' => "Cabinet présentiel + visio. Séance découverte offerte sur simple demande." ),
	),
	'coach_sportif' => array(
		array( 'year' => '2012', 'text' => "Diplôme BPJEPS Activités de la Forme + Licence STAPS Entraînement Sportif." ),
		array( 'year' => '2016', 'text' => "Certification préparation mentale Sport (INSEP). 1er athlète coaché en compétition." ),
		array( 'year' => '2019', 'text' => "Spécialisation nutrition sportive (DU Université Paris-Cité)." ),
		array( 'year' => '2023', 'text' => "Plus de 300 athlètes accompagnés. 2 podiums marathon France, 1 record perso semi." ),
		array( 'year' => "Aujourd'hui", 'text' => "Programmes individualisés, suivi en salle + à domicile + visio." ),
	),
	'coach_vie' => array(
		array( 'year' => '2013', 'text' => "Reconversion après 10 ans en entreprise. Formation Coach de vie (RNCP niveau 6)." ),
		array( 'year' => '2016', 'text' => "Praticien certifié PNL (Programmation Neuro-Linguistique)." ),
		array( 'year' => '2019', 'text' => "Spécialisation accompagnement burn-out et reconversion professionnelle." ),
		array( 'year' => '2024', 'text' => "Plus de 250 personnes accompagnées. Auteur de l'ouvrage 'Se retrouver'." ),
		array( 'year' => "Aujourd'hui", 'text' => "Approche systémique, séances individuelles, ateliers groupe trimestriels." ),
	),
	'coach_business' => array(
		array( 'year' => '2010', 'text' => "MBA HEC + 12 ans en cabinet conseil (McKinsey, BCG)." ),
		array( 'year' => '2016', 'text' => "Certification Master Coach EMCC. Premier accompagnement de COMEX." ),
		array( 'year' => '2019', 'text' => "Référencé OPCO (Atlas, Constructys, Akto). Datadock + Qualiopi." ),
		array( 'year' => '2024', 'text' => "Plus de 150 dirigeants accompagnés. 40 équipes en coaching collectif." ),
		array( 'year' => "Aujourd'hui", 'text' => "Coaching individuel dirigeants + team building + co-développement." ),
	),
	'coach_mental' => array(
		array( 'year' => '2011', 'text' => "Master Psychologie Clinique + DU Préparation Mentale (Paris-Saclay)." ),
		array( 'year' => '2015', 'text' => "Formation MBSR (Mindfulness Based Stress Reduction) Université Massachusetts." ),
		array( 'year' => '2019', 'text' => "Spécialisation gestion stress sportifs haut niveau et chefs d'entreprise." ),
		array( 'year' => '2024', 'text' => "Plus de 400 personnes accompagnées en présentiel + visio." ),
		array( 'year' => "Aujourd'hui", 'text' => "Cabinet + visio. Séances individuelles + groupes de méditation hebdo." ),
	),
);
$timeline = isset( $timelines[ $metier_slug ] ) ? $timelines[ $metier_slug ] : $timelines['coach_general'];
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Votre coach' ); ?></span>
				<h1 class="ag-page-title">Qui <em>suis-je</em></h1>
				<p class="ag-page-hero-sub">Une approche bienveillante, des résultats concrets, à vos côtés sur la durée.</p>
			</div>
		</section>
	<?php endwhile; ?>

	<section class="ag-about-wrap">
		<div class="ag-about-grid">
			<div class="ag-about-text ag-anim">
				<h3>Mon engagement</h3>
				<h2>Vous accompagner avec écoute, sans jugement, vers vos objectifs.</h2>
				<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
			</div>
			<div class="ag-about-photo ag-anim" style="background-image:url('<?php echo esc_url( $about_photo ); ?>');"></div>
		</div>
	</section>

	<section class="ag-timeline">
		<div class="ag-container">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Mon <em>parcours</em></h2>
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
			<h2 class="ag-cta-band__title">Parlons de votre projet</h2>
			<p class="ag-cta-band__lead">Séance découverte offerte — sans engagement, pour clarifier vos besoins.</p>
			<a href="<?php echo esc_url( ag_coach_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Réserver ma séance découverte →</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
