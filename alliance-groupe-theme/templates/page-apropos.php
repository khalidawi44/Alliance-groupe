<?php
/**
 * Template Name: À propos
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">À propos</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">L'agence <em>Alliance Groupe</em></span>
            </h1>
            <p class="ag-hero__sub">Une équipe passionnée par le digital et l'innovation, au service de votre réussite.</p>
        </div>
    </section>

    <?php get_template_part('template-parts/about'); ?>

    <section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1920&q=80');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"Notre mission : rendre la puissance du digital accessible à toutes les entreprises ambitieuses."</p>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
