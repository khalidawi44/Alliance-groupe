<?php
/**
 * Template page "Réalisations" (variante coach).
 *
 * Galerie de 6 images Unsplash thématiques (selon la spécialité coach)
 * + 3 témoignages clients + CTA séance découverte.
 *
 * @package AG_Starter_Coach
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_coach_metier_slug', '' );
$ag_metier_nom = ag_coach_opt( 'ag_coach_metier_nom', '' );
$ag_hero_image = ag_coach_opt( 'ag_coach_hero_image', '' );

// Galerie d'images Unsplash par spécialité coach (parcours, transformations).
$gallery_by_metier = array(
	'coach_general' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=900&q=80', 'caption' => 'Reprise de confiance après burn-out' ),
		array( 'src' => 'https://images.unsplash.com/photo-1551836022-deb4988cc6c0?w=900&q=80', 'caption' => 'Transition de carrière à 45 ans' ),
		array( 'src' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=900&q=80', 'caption' => 'Équilibre vie pro / vie perso retrouvé' ),
		array( 'src' => 'https://images.unsplash.com/photo-1531574799647-d92ed25e91d6?w=900&q=80', 'caption' => 'Préparation entretien d\'embauche cadre' ),
		array( 'src' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=900&q=80', 'caption' => 'Création d\'entreprise post-licenciement' ),
		array( 'src' => 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=900&q=80', 'caption' => 'Gestion du stress chronique' ),
	),
	'coach_sportif' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=900&q=80', 'caption' => 'Préparation Marathon de Paris (sub-3h30)' ),
		array( 'src' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=900&q=80', 'caption' => 'Prise de masse musculaire (+8 kg en 6 mois)' ),
		array( 'src' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=900&q=80', 'caption' => 'Perte de poids durable (−18 kg en 1 an)' ),
		array( 'src' => 'https://images.unsplash.com/photo-1434596922112-19c563067271?w=900&q=80', 'caption' => 'Préparation triathlon Ironman' ),
		array( 'src' => 'https://images.unsplash.com/photo-1599058917212-d750089bc07e?w=900&q=80', 'caption' => 'Retour au sport après blessure (croisé)' ),
		array( 'src' => 'https://images.unsplash.com/photo-1591258739134-879e2b9dffa8?w=900&q=80', 'caption' => 'Préparation compétition CrossFit régionale' ),
	),
	'coach_vie' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=900&q=80', 'caption' => 'Reconversion professionnelle réussie' ),
		array( 'src' => 'https://images.unsplash.com/photo-1518609878373-06d740f60d8b?w=900&q=80', 'caption' => 'Sortie de burn-out, reprise sereine' ),
		array( 'src' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=900&q=80', 'caption' => 'Confiance en soi retrouvée' ),
		array( 'src' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=900&q=80', 'caption' => 'Reconstruction après séparation' ),
		array( 'src' => 'https://images.unsplash.com/photo-1517021897933-0e0319cfbc28?w=900&q=80', 'caption' => 'Trouver son ikigaï à 50 ans' ),
		array( 'src' => 'https://images.unsplash.com/photo-1490730141103-6cac27aaab94?w=900&q=80', 'caption' => 'Ateliers groupe trimestriels' ),
	),
	'coach_business' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=900&q=80', 'caption' => 'Coaching dirigeant scale-up Tech' ),
		array( 'src' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=900&q=80', 'caption' => 'Team building COMEX 12 personnes' ),
		array( 'src' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=900&q=80', 'caption' => 'Accompagnement reprise d\'entreprise' ),
		array( 'src' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=900&q=80', 'caption' => 'Co-développement entre managers' ),
		array( 'src' => 'https://images.unsplash.com/photo-1573164713988-8665fc963095?w=900&q=80', 'caption' => 'Coaching nouvelle prise de poste' ),
		array( 'src' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=900&q=80', 'caption' => 'Séminaire offsite stratégie 2 jours' ),
	),
	'coach_mental' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1545389336-cf090694435e?w=900&q=80', 'caption' => 'Préparation mentale compétition' ),
		array( 'src' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=900&q=80', 'caption' => 'Gestion anxiété pré-examen' ),
		array( 'src' => 'https://images.unsplash.com/photo-1593811167562-9cef47bfc4d7?w=900&q=80', 'caption' => 'Sortie de troubles du sommeil' ),
		array( 'src' => 'https://images.unsplash.com/photo-1474418397713-2f1091382189?w=900&q=80', 'caption' => 'Méditation pleine conscience MBSR' ),
		array( 'src' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=900&q=80', 'caption' => 'Préparation mentale prise de parole' ),
		array( 'src' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=900&q=80', 'caption' => 'Cohérence cardiaque + respiration' ),
	),
);
$gallery = isset( $gallery_by_metier[ $metier_slug ] ) ? $gallery_by_metier[ $metier_slug ] : $gallery_by_metier['coach_general'];

// Témoignages depuis le preset actif
$testimonials = array();
if ( class_exists( 'ag_coach_Presets' ) ) {
	foreach ( array( 1, 2, 3 ) as $i ) {
		$t = ag_coach_Presets::get_testimonial( $i );
		if ( $t && ! empty( $t['text'] ) ) $testimonials[] = $t;
	}
}
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Parcours clients' ); ?></span>
				<h1 class="ag-page-title">Histoires de <em>transformation</em></h1>
				<p class="ag-page-hero-sub">Découvrez des parcours d'accompagnement qui ont changé une vie.</p>
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

	<section class="ag-realisations-gallery">
		<div class="ag-container">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Quelques <em>parcours</em> récents</h2>
				<p class="ag-services-grid-lead">Cliquez sur une histoire pour en savoir plus.</p>
			</div>
			<div class="ag-realisations-grid">
				<?php foreach ( $gallery as $item ) : ?>
					<a href="<?php echo esc_url( $item['src'] ); ?>" target="_blank" rel="noopener" class="ag-realisations-card ag-anim" style="background-image:linear-gradient(rgba(0,0,0,.1),rgba(0,0,0,.5)),url('<?php echo esc_url( $item['src'] ); ?>');">
						<span class="ag-realisations-card__caption"><?php echo esc_html( $item['caption'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $testimonials ) : ?>
		<section class="ag-testi-wrap">
			<div class="ag-container">
				<div class="ag-services-grid-header ag-anim">
					<h2 class="ag-services-grid-title">Ce qu'ils <em>en disent</em></h2>
				</div>
				<div class="ag-testi-grid">
					<?php foreach ( $testimonials as $t ) : ?>
						<div class="ag-testi-card ag-anim">
							<div class="ag-testi-stars">★★★★★</div>
							<p class="ag-testi-text">« <?php echo esc_html( $t['text'] ); ?> »</p>
							<p class="ag-testi-author"><strong><?php echo esc_html( $t['name'] ); ?></strong><?php if ( $t['city'] ) : ?> · <?php echo esc_html( $t['city'] ); ?><?php endif; ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="ag-cta-band">
		<div class="ag-container">
			<h2 class="ag-cta-band__title">Votre parcours sera le prochain ?</h2>
			<p class="ag-cta-band__lead">Séance découverte offerte — 30 minutes pour clarifier vos besoins, sans engagement.</p>
			<a href="<?php echo esc_url( ag_coach_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Réserver ma séance découverte →</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
