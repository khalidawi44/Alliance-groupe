<?php
/**
 * Template page "Qui sommes-nous".
 *
 * Layout 2 colonnes (texte + photo équipe) + timeline historique +
 * témoignages + CTA. Photo et timeline dependent du metier actif.
 *
 * @package AG_Starter_Artisan
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_artisan_metier_slug', '' );
$ag_metier_nom = ag_artisan_opt( 'ag_artisan_metier_nom', '' );
$ag_hero_image = ag_artisan_opt( 'ag_artisan_hero_image', '' );

// Photo "équipe / atelier" Unsplash par metier
$about_photos = array(
	'macon'        => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=900&q=80',
	'electricien'  => 'https://images.unsplash.com/photo-1581092334651-ddf26d9a09d0?w=900&q=80',
	'traiteur'     => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=900&q=80',
	'multiservice' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=900&q=80',
	'generaliste'  => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=900&q=80',
);
$about_photo = isset( $about_photos[ $metier_slug ] ) ? $about_photos[ $metier_slug ] : $about_photos['generaliste'];

// Override Personnaliser : votre vraie photo d'équipe / d'atelier.
$custom_about_photo = get_theme_mod( 'ag_artisan_about_photo', '' );
if ( $custom_about_photo ) {
	$about_photo = $custom_about_photo;
}

// Timeline historique par metier (4-5 etapes)
$timelines = array(
	'macon' => array(
		array( 'year' => '2001', 'text' => "Création de l'entreprise par Pierre Martin, compagnon maçon depuis 15 ans." ),
		array( 'year' => '2008', 'text' => "Embauche du 3e compagnon. Premier chantier d'extension d'envergure (120m²)." ),
		array( 'year' => '2015', 'text' => "Certification Qualibat. Entrée dans la FFB (Fédération Française du Bâtiment)." ),
		array( 'year' => '2022', 'text' => "Reprise par son fils Thomas. 8 compagnons salariés, chantiers dans 4 départements." ),
		array( 'year' => 'Aujourd\'hui', 'text' => "Plus de 200 chantiers livrés, label Qualibat renouvelé, garantie décennale Maaf Pro." ),
	),
	'electricien' => array(
		array( 'year' => '2013', 'text' => "Lancement de l'activité par Sami, électricien diplômé Bac Pro ELEEC." ),
		array( 'year' => '2017', 'text' => "Obtention de la certification Qualifelec. Premier gros chantier domotique." ),
		array( 'year' => '2021', 'text' => "Agrément IRVE (Infrastructure de Recharge VE). 1ère borne de recharge installée." ),
		array( 'year' => '2024', 'text' => "Équipe de 4 électriciens. 1 200 interventions, astreinte 7j/7." ),
		array( 'year' => 'Aujourd\'hui', 'text' => "Spécialiste mise aux normes NF C 15-100 + domotique + bornes VE." ),
	),
	'traiteur' => array(
		array( 'year' => '2007', 'text' => "Création par le chef Olivier, formé au Plaza Athénée et chez Drouant." ),
		array( 'year' => '2012', 'text' => "Premier mariage de 200 invités. Acquisition d'un camion frigorifique." ),
		array( 'year' => '2018', 'text' => "Référencé sur Mariages.net et Zankyou. Top 10 traiteurs de la région." ),
		array( 'year' => '2023', 'text' => "Équipe de 12 personnes. 1 200 événements à notre actif." ),
		array( 'year' => 'Aujourd\'hui', 'text' => "Spécialités mariages, séminaires entreprise, cocktails de prestige." ),
	),
	'multiservice' => array(
		array( 'year' => '2017', 'text' => "Lancement de l'activité — coordination de tous corps de métier en 1 contact." ),
		array( 'year' => '2020', 'text' => "Embauche du 5e compagnon. Premier contrat annuel avec une copropriété." ),
		array( 'year' => '2023', 'text' => "Équipe de 12 polyvalents (plomberie, élec, peinture, jardin)." ),
		array( 'year' => '2025', 'text' => "1 800 missions réalisées. 14 copropriétés en contrat d'entretien." ),
		array( 'year' => 'Aujourd\'hui', 'text' => "Un seul interlocuteur pour tous vos travaux — 96% satisfaction client." ),
	),
	'generaliste' => array(
		array( 'year' => '2015', 'text' => "Création de l'entreprise sur le principe : un artisan polyvalent, partout." ),
		array( 'year' => '2018', 'text' => "Premier gros chantier de rénovation complète d'appartement (T3)." ),
		array( 'year' => '2022', 'text' => "Équipe stabilisée, 600 chantiers livrés, note 4,8/5 sur avis clients." ),
		array( 'year' => 'Aujourd\'hui', 'text' => "Devis 24h, intervention sous 7 jours, satisfaction garantie." ),
	),
);
$timeline = isset( $timelines[ $metier_slug ] ) ? $timelines[ $metier_slug ] : $timelines['generaliste'];

// Override Personnaliser → « Notre histoire (timeline) » : une étape par
// ligne au format « Année | Texte ». Remplace la timeline de démo.
$timeline_edit = (string) get_theme_mod( 'ag_artisan_timeline_edit', '' );
if ( '' !== trim( $timeline_edit ) ) {
	$custom_timeline = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $timeline_edit ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) continue;
		$parts = array_map( 'trim', explode( '|', $line ) );
		$year = isset( $parts[0] ) ? $parts[0] : '';
		$text = isset( $parts[1] ) ? $parts[1] : '';
		if ( '' === $year && '' === $text ) continue;
		$custom_timeline[] = array( 'year' => $year, 'text' => $text );
	}
	if ( ! empty( $custom_timeline ) ) {
		$timeline = $custom_timeline;
	}
}
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'L\'équipe' ); ?></span>
				<h1 class="ag-page-title">Qui <em>sommes-nous</em></h1>
				<p class="ag-page-hero-sub">Un savoir-faire artisanal, transmis et entretenu chaque jour.</p>
			</div>
		</section>
	<?php endwhile; ?>

	<section class="ag-about-wrap">
		<div class="ag-about-grid">
			<div class="ag-about-text ag-anim">
				<h3>Notre engagement</h3>
				<h2>Un savoir-faire artisanal, transmis et entretenu chaque jour.</h2>
				<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
			</div>
			<div class="ag-about-photo ag-anim" style="background-image:url('<?php echo esc_url( $about_photo ); ?>');"></div>
		</div>
	</section>

	<section class="ag-timeline">
		<div class="ag-container">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Notre <em>histoire</em></h2>
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
			<h2 class="ag-cta-band__title">Travaillons ensemble</h2>
			<p class="ag-cta-band__lead">Devis gratuit, contact direct, estimation instantanée.</p>
			<a href="<?php echo esc_url( home_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Obtenir mon devis</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
