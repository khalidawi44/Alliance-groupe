<?php
/**
 * Template page "Réalisations / Témoignages" (variante services a la personne).
 *
 * Grille de situations accompagnees (sans images externes) + 3 temoignages
 * issus du preset actif + CTA devis.
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

// Situations accompagnees par specialite (sans image externe = zero lien casse).
$situations_by_metier = array(
	'domicile_seniors' => array(
		array( 'emoji' => '🏡', 'title' => 'Maintien à domicile', 'text' => 'Rester chez soi en sécurité, avec une aide quotidienne adaptée.' ),
		array( 'emoji' => '🏥', 'title' => 'Retour d’hospitalisation', 'text' => 'Une présence renforcée les premiers jours pour une convalescence sereine.' ),
		array( 'emoji' => '🌙', 'title' => 'Garde de nuit', 'text' => 'Une présence rassurante la nuit, pour la personne et pour la famille.' ),
		array( 'emoji' => '🤝', 'title' => 'Répit des aidants', 'text' => 'Du temps pour souffler, en toute confiance, pendant qu’on prend le relais.' ),
		array( 'emoji' => '💬', 'title' => 'Lutte contre l’isolement', 'text' => 'De la compagnie, des sorties et du lien social au quotidien.' ),
		array( 'emoji' => '🍲', 'title' => 'Aide aux repas', 'text' => 'Des repas préparés et partagés, pour bien manger chaque jour.' ),
	),
	'domicile_familles' => array(
		array( 'emoji' => '🧹', 'title' => 'Maison entretenue', 'text' => 'Ménage et repassage réguliers, une maison toujours nette.' ),
		array( 'emoji' => '👶', 'title' => 'Garde d’enfants', 'text' => 'Une personne de confiance après l’école ou en journée.' ),
		array( 'emoji' => '🌅', 'title' => 'Aide à un parent âgé', 'text' => 'Le même partenaire pour toute la famille, un vrai soulagement.' ),
		array( 'emoji' => '♿', 'title' => 'Aide au handicap', 'text' => 'Un accompagnement respectueux, adapté à chaque besoin.' ),
		array( 'emoji' => '🛒', 'title' => 'Courses & démarches', 'text' => 'Un coup de main pour les courses et le quotidien administratif.' ),
		array( 'emoji' => '⏱️', 'title' => 'Du temps retrouvé', 'text' => 'Moins de charge mentale, plus de moments en famille.' ),
	),
	'domicile_handicap' => array(
		array( 'emoji' => '🤝', 'title' => 'Gestes essentiels', 'text' => 'Une aide compétente et respectueuse pour les actes du quotidien.' ),
		array( 'emoji' => '🧠', 'title' => 'Stimulation & activités', 'text' => 'Des activités adaptées pour maintenir les capacités et le moral.' ),
		array( 'emoji' => '🌙', 'title' => 'Présence de nuit', 'text' => 'Une présence fiable la nuit, sécurisante pour tous.' ),
		array( 'emoji' => '🦽', 'title' => 'Aide aux déplacements', 'text' => 'Des sorties et rendez-vous accompagnés en toute sécurité.' ),
		array( 'emoji' => '🗂️', 'title' => 'Démarches PCH / APA', 'text' => 'Un accompagnement dans les dossiers et le quotidien administratif.' ),
		array( 'emoji' => '💚', 'title' => 'Répit des aidants', 'text' => 'Du relais et du soutien pour les proches aidants.' ),
	),
);
$situations = isset( $situations_by_metier[ $metier_slug ] ) ? $situations_by_metier[ $metier_slug ] : $situations_by_metier['domicile_seniors'];

// Témoignages depuis le preset actif.
$testimonials = array();
if ( class_exists( 'ag_domicile_Presets' ) ) {
	foreach ( array( 1, 2, 3 ) as $i ) {
		$t = ag_domicile_Presets::get_testimonial( $i );
		if ( $t && ! empty( $t['text'] ) ) $testimonials[] = $t;
	}
}
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero ag-page-hero--full"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Ils nous font confiance' ); ?></span>
				<h1 class="ag-page-title">Témoignages <em>de familles</em></h1>
				<p class="ag-page-hero-sub">Un proche qui reste chez lui, en sécurité et entouré.</p>
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
				<h2 class="ag-services-grid-title">Les situations que nous <em>accompagnons</em></h2>
				<p class="ag-services-grid-lead">Chaque histoire est unique — voici celles que nous rencontrons le plus souvent.</p>
			</div>
			<div class="ag-services-grid">
				<?php foreach ( $situations as $s ) : ?>
					<div class="ag-service-card ag-anim" style="cursor:default;">
						<div class="ag-service-card__icon"><?php echo esc_html( $s['emoji'] ); ?></div>
						<h3 class="ag-service-card__title"><?php echo esc_html( $s['title'] ); ?></h3>
						<p style="margin:8px 0 0;font-size:.92rem;opacity:.85;"><?php echo esc_html( $s['text'] ); ?></p>
					</div>
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
			<h2 class="ag-cta-band__title">Et si c'était au tour de votre proche ?</h2>
			<p class="ag-cta-band__lead">Évaluation à domicile gratuite — crédit d'impôt de 50 %, sans engagement.</p>
			<a href="<?php echo esc_url( ag_domicile_resolve_cta_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Demander un devis gratuit</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
