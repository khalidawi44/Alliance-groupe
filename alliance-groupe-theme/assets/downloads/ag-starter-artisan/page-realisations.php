<?php
/**
 * Template pour la page Realisations.
 *
 * Affiche le contenu Gutenberg (texte intro metier-specifique) PUIS une
 * galerie de 6 images Unsplash thematiques (selon le metier actif),
 * les 3 temoignages clients du preset, et un CTA devis.
 *
 * @package AG_Starter_Artisan
 */

get_header();

$metier_slug   = get_theme_mod( 'ag_artisan_metier_slug', '' );
$ag_metier_nom = ag_artisan_opt( 'ag_artisan_metier_nom', '' );
$ag_hero_image = ag_artisan_opt( 'ag_artisan_hero_image', '' );

// Galerie d'images Unsplash par metier (6 photos lifestyle differentes
// que le hero pour eviter la redite). Photos libres Unsplash.
$gallery_by_metier = array(
	'macon' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=900&q=80', 'caption' => 'Construction maison neuve · 145m²' ),
		array( 'src' => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=900&q=80', 'caption' => 'Extension contemporaine · 38m²' ),
		array( 'src' => 'https://images.unsplash.com/photo-1517581177682-a085bb7ffb15?w=900&q=80', 'caption' => 'Ravalement de façade complet' ),
		array( 'src' => 'https://images.unsplash.com/photo-1503328427499-d92d1ac3d174?w=900&q=80', 'caption' => 'Dalle béton terrasse · 80m²' ),
		array( 'src' => 'https://images.unsplash.com/photo-1592928038509-95eb6c11d4f3?w=900&q=80', 'caption' => 'Mur de soutènement en pierre' ),
		array( 'src' => 'https://images.unsplash.com/photo-1581094288338-2314dddb7ece?w=900&q=80', 'caption' => 'Carrelage extérieur grand format' ),
	),
	'electricien' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=900&q=80', 'caption' => 'Refonte tableau électrique' ),
		array( 'src' => 'https://images.unsplash.com/photo-1551710029-09fe48bbf6a2?w=900&q=80', 'caption' => 'Installation domotique salon' ),
		array( 'src' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=900&q=80', 'caption' => 'Éclairage architectural sur-mesure' ),
		array( 'src' => 'https://images.unsplash.com/photo-1606767341197-cdf926c3a3b6?w=900&q=80', 'caption' => 'Borne de recharge VE installée' ),
		array( 'src' => 'https://images.unsplash.com/photo-1601814933824-fd0b574dd592?w=900&q=80', 'caption' => 'Mise aux normes NF C 15-100' ),
		array( 'src' => 'https://images.unsplash.com/photo-1626885930974-4b69aa21bbf9?w=900&q=80', 'caption' => 'Câblage VDI réseau bureau' ),
	),
	'traiteur' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=900&q=80', 'caption' => 'Cocktail dînatoire 80 invités' ),
		array( 'src' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=900&q=80', 'caption' => 'Buffet sucré-salé · mariage' ),
		array( 'src' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=900&q=80', 'caption' => 'Repas assis 3 services · gala' ),
		array( 'src' => 'https://images.unsplash.com/photo-1555244162-803834f70033?w=900&q=80', 'caption' => 'Pièce montée traditionnelle' ),
		array( 'src' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=900&q=80', 'caption' => 'Brunch d\'entreprise' ),
		array( 'src' => 'https://images.unsplash.com/photo-1564844536311-de546a28c87d?w=900&q=80', 'caption' => 'Service à table en salle' ),
	),
	'multiservice' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=900&q=80', 'caption' => 'Rénovation appartement complet' ),
		array( 'src' => 'https://images.unsplash.com/photo-1581094288338-2314dddb7ece?w=900&q=80', 'caption' => 'Pose de cuisine équipée' ),
		array( 'src' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=900&q=80', 'caption' => 'Aménagement jardin + clôture' ),
		array( 'src' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=900&q=80', 'caption' => 'Réparations plomberie urgentes' ),
		array( 'src' => 'https://images.unsplash.com/photo-1556910631-c0c1a1f3fda3?w=900&q=80', 'caption' => 'Peinture intérieure 4 pièces' ),
		array( 'src' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=900&q=80', 'caption' => 'Montage de mobilier sur mesure' ),
	),
	'generaliste' => array(
		array( 'src' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=900&q=80', 'caption' => 'Rénovation salle de bain' ),
		array( 'src' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=900&q=80', 'caption' => 'Travaux de maçonnerie légère' ),
		array( 'src' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=900&q=80', 'caption' => 'Création terrasse extérieure' ),
		array( 'src' => 'https://images.unsplash.com/photo-1556910631-c0c1a1f3fda3?w=900&q=80', 'caption' => 'Peinture neuve appartement' ),
		array( 'src' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=900&q=80', 'caption' => 'Mise aux normes électrique' ),
		array( 'src' => 'https://images.unsplash.com/photo-1581094288338-2314dddb7ece?w=900&q=80', 'caption' => 'Cuisine équipée installée' ),
	),
);
$gallery = isset( $gallery_by_metier[ $metier_slug ] ) ? $gallery_by_metier[ $metier_slug ] : $gallery_by_metier['generaliste'];

// Temoignages
$testimonials = array();
if ( class_exists( 'AG_Artisan_Presets' ) ) {
	foreach ( array( 1, 2, 3 ) as $i ) {
		$t = AG_Artisan_Presets::get_testimonial( $i );
		if ( $t && ! empty( $t['text'] ) ) $testimonials[] = $t;
	}
}
?>

<main id="ag-main" class="ag-main ag-main--premium" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="ag-page-hero"<?php if ( $ag_hero_image ) : ?> style="background-image:url('<?php echo esc_url( $ag_hero_image ); ?>');"<?php endif; ?>>
			<div class="ag-container">
				<span class="ag-page-tag"><?php echo esc_html( $ag_metier_nom ?: 'Nos réalisations' ); ?></span>
				<h1 class="ag-page-title">Nos <em>réalisations</em></h1>
				<div class="ag-page-lead">
					<?php the_content(); ?>
				</div>
			</div>
		</section>
	<?php endwhile; ?>

	<section class="ag-realisations-gallery">
		<div class="ag-container">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Quelques <em>projets</em> récents</h2>
				<p class="ag-services-grid-lead">Cliquez sur une image pour la voir en grand.</p>
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
			<h2 class="ag-cta-band__title">Votre projet sera le prochain ?</h2>
			<p class="ag-cta-band__lead">Devis gratuit + fourchette de prix instantanée selon votre code postal.</p>
			<a href="<?php echo esc_url( home_url( '/devis/' ) ); ?>" class="ag-btn-pro">🚀 Obtenir mon devis →</a>
		</div>
	</section>

</main>

<?php
get_sidebar();
get_footer();
