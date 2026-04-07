<?php
/**
 * Template Name: Services
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Services</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Nos <em>expertises</em></span>
                <span class="ag-line">à votre service</span>
            </h1>
            <p class="ag-hero__sub">Des solutions digitales complètes pour accélérer votre croissance.</p>
        </div>
    </section>

    <?php get_template_part('template-parts/services'); ?>
    <?php get_template_part('template-parts/process'); ?>
    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
