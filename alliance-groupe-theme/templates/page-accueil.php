<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- Hero -->
<section class="ag-hero" id="ag-main-content">
    <div class="ag-hero__bg">
        <div class="ag-hero__circles">
            <div class="ag-hero__circle"></div>
            <div class="ag-hero__circle"></div>
            <div class="ag-hero__circle"></div>
            <div class="ag-hero__circle"></div>
        </div>
        <div class="ag-hero__orb ag-hero__orb--1"></div>
        <div class="ag-hero__orb ag-hero__orb--2"></div>
    </div>

    <div class="ag-hero__content">
        <div class="ag-hero__badge">
            <span class="ag-hero__dot"></span>
            Agence Web &amp; IA — Alliance Groupe
            <span class="ag-heritage-dots" aria-hidden="true"><span></span><span></span><span></span></span>
        </div>

        <h1 class="ag-hero__title">
            <span class="ag-line">Arrêtez de payer</span>
            <span class="ag-line"><em>des commerciaux.</em></span>
            <span class="ag-line">Votre site le fait mieux.</span>
        </h1>

        <p class="ag-hero__sub">
            Nous créons des sites web qui génèrent des leads 24h/24, automatisent votre prospection et réduisent vos coûts commerciaux grâce à l'IA.
        </p>

        <div class="ag-hero__buttons">
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-gold">Parlons de votre projet →</a>
            <a href="<?php echo esc_url(home_url('/realisations')); ?>" class="ag-btn-outline">Voir nos réalisations</a>
        </div>

        <div class="ag-hero__metrics">
            <div class="ag-metric">
                <span class="ag-metric__value">+340%</span>
                <span class="ag-metric__label">Leads générés</span>
            </div>
            <div class="ag-metric">
                <span class="ag-metric__value">24/7</span>
                <span class="ag-metric__label">Disponibilité</span>
            </div>
            <div class="ag-metric">
                <span class="ag-metric__value">-60%</span>
                <span class="ag-metric__label">Coûts commerciaux</span>
            </div>
        </div>

        <div class="ag-hero__scroll">
            <span>Découvrir</span>
            <span class="ag-hero__scroll-line"></span>
            <span class="ag-hero__scroll-dot"></span>
        </div>
    </div>
</section>

<!-- Marquee -->
<?php get_template_part('template-parts/marquee'); ?>

<!-- Services -->
<?php get_template_part('template-parts/services'); ?>

<!-- Process -->
<?php get_template_part('template-parts/process'); ?>

<!-- Parallax 1 (transition) -->
<section class="ag-parallax" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/cities/naples-1.jpg' ); ?>');">
    <div class="ag-parallax__overlay"></div>
    <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
        <p class="ag-parallax__quote">"La technologie seule ne suffit pas. C'est la technologie mariée au design qui nous donne le résultat qui fait chanter notre cœur."</p>
    </div>
</section>

<!-- Templates WordPress gratuits — scroll-jacking GSAP plein écran style Apple
     (remplace l'ancienne section "Promo Templates" carte simple) -->
<?php get_template_part('template-parts/alliance-scroll-fx'); ?>

<!-- Réalisations -->
<?php get_template_part('template-parts/realisations'); ?>

<!-- Parallax 2 — slideshow des 3 bureaux -->
<?php
$parallax_slides = array();
$cities_path = get_stylesheet_directory() . '/assets/images/cities/';
$cities_uri  = get_stylesheet_directory_uri() . '/assets/images/cities/';
foreach ( array( 'nantes-1.jpg', 'naples-1.jpg', 'marrakech-1.jpg' ) as $slide ) {
    if ( file_exists( $cities_path . $slide ) ) {
        $parallax_slides[] = $cities_uri . $slide;
    }
}
// Fallback : photo locale de Naples si aucune photo de ville n'est encore uploadée
if ( empty( $parallax_slides ) ) {
    $fallback = get_stylesheet_directory() . '/assets/images/team/1_bureau_naples.jpg';
    if ( file_exists( $fallback ) ) {
        $parallax_slides[] = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg';
    }
}
$parallax_first = ! empty( $parallax_slides ) ? $parallax_slides[0] : '';
?>
<section class="ag-parallax ag-parallax--slideshow"<?php echo $parallax_first ? ' style="background-image:url(\'' . esc_url( $parallax_first ) . '\');"' : ''; ?>>
    <?php if ( count( $parallax_slides ) > 1 ) : ?>
    <div class="ag-hero__slideshow" aria-hidden="true">
        <?php foreach ( $parallax_slides as $src ) : ?>
            <div class="ag-hero__slide" style="background-image:url('<?php echo esc_url( $src ); ?>');"></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="ag-parallax__overlay"></div>
    <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
        <p class="ag-parallax__quote">"Votre site web est votre meilleur commercial. Il travaille 24h/24, ne prend jamais de vacances et ne demande pas de commission."</p>
        <p class="ag-parallax__caption" style="margin-top:14px;color:var(--color-text-secondary);font-size:.88rem;letter-spacing:.5px;text-transform:uppercase;">— Nantes · Naples · Marrakech</p>
    </div>
</section>

<!-- About -->
<?php get_template_part('template-parts/about'); ?>

<!-- FAQ -->
<?php get_template_part('template-parts/faq'); ?>

<!-- CTA -->
<?php get_template_part('template-parts/cta'); ?>

<?php get_footer(); ?>
