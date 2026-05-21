<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- Hero -->
<section class="ag-hero" id="ag-main-content">
    <!-- Mesh gradient WebGL animé (dégradé animé moderne) -->
    <?php get_template_part('template-parts/mesh-gradient-bg'); ?>
    <!-- Grille tech high-tech parallax qui réagit à la souris -->
    <?php get_template_part('template-parts/hero-tech-grid'); ?>
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
            <a href="<?php echo esc_url(home_url('/sites-express')); ?>" class="ag-btn-gold">Voir les offres →</a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Parlons de votre projet</a>
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

<!-- 🎯 ENTONNOIR PAR PRIORITE :
       P1) VENDRE      → offres Sites Express (prix fixes)
       P1b) sur-mesure → services
       P2) RECRUTER    → programme ambassadeurs (10%)
       P3) CARITATIF   → Programme Racines
       puis : preuves & contenu (templates, metiers, equipe, process, realisations, FAQ) -->

<!-- P1/ VENDRE — offres claires a prix fixe -->
<?php get_template_part('template-parts/home-offres'); ?>

<!-- P1b/ Services sur-mesure (ticket plus eleve) -->
<?php get_template_part('template-parts/services'); ?>

<!-- Bande parallax (fond ville fixe, nuit) : punchline vente -->
<section class="ag-parallax ag-parallax--punch" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/cities/nantes-3.jpg' ); ?>');">
    <div class="ag-parallax__overlay" style="background:linear-gradient(180deg,rgba(6,8,16,.62),rgba(6,8,16,.46) 45%,rgba(6,8,16,.74));"></div>
    <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
        <p class="ag-parallax__quote">« Pendant que tu dors, ton site travaille.<br>Un client peut commander à 3 h du matin. »</p>
    </div>
</section>
<style>
.ag-parallax--punch{min-height:74vh;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.ag-parallax--punch .ag-parallax__content{position:relative;z-index:1;width:100%;max-width:920px;margin:0 auto;padding:0 24px;text-align:center;}
.ag-parallax--punch .ag-parallax__quote{max-width:860px;margin:0 auto;line-height:1.45;text-shadow:0 2px 26px rgba(0,0,0,.7);}
@media(max-width:768px){.ag-parallax--punch{min-height:58vh;}.ag-parallax--punch .ag-parallax__quote br{display:none;}}
</style>

<!-- P2/ RECRUTER — programme ambassadeurs 10% -->
<?php get_template_part('template-parts/home-ambassadeurs'); ?>

<!-- P3/ CARITATIF — Programme Racines -->
<?php get_template_part('template-parts/home-racines'); ?>

<!-- Templates gratuits (aimant a prospects) -->
<?php get_template_part('template-parts/templates-cta'); ?>

<!-- Section "metiers" — scroll-jacking GSAP plein ecran style Apple -->
<?php get_template_part('template-parts/alliance-scroll-fx'); ?>

<!-- About : Qui sommes-nous + valeurs + equipe -->
<?php get_template_part('template-parts/about'); ?>

<!-- Process -->
<?php get_template_part('template-parts/process'); ?>

<!-- Parallax 1 (transition) -->
<section class="ag-parallax" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/cities/naples-1.jpg' ); ?>');">
    <div class="ag-parallax__overlay"></div>
    <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
        <p class="ag-parallax__quote">"La technologie seule ne suffit pas. C'est la technologie mariée au design qui nous donne le résultat qui fait chanter notre cœur."</p>
    </div>
</section>

<!-- Réalisations -->
<?php get_template_part('template-parts/realisations'); ?>

<!-- Globe 3D : présence internationale Nantes / Marrakech / Naples -->
<?php get_template_part('template-parts/globe-3d'); ?>

<!-- FAQ -->
<?php get_template_part('template-parts/faq'); ?>

<!-- CTA -->
<?php get_template_part('template-parts/cta'); ?>

<?php get_footer(); ?>
