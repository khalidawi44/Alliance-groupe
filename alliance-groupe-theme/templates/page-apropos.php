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
            <span class="ag-heritage-strip ag-heritage-strip--center" aria-hidden="true"></span>
            <p class="ag-hero__sub">Une équipe aux racines <strong>franco-italo-marocaines</strong>, passionnée par le digital et l'innovation, au service de votre réussite.</p>
        </div>
    </section>

    <?php get_template_part('template-parts/about'); ?>

    <!-- Heritage : nos racines -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div style="text-align:center;max-width:720px;margin:0 auto 56px;">
                <span class="ag-tag">Nos racines</span>
                <h2 class="ag-section__title">Trois cultures, <em>une même exigence</em></h2>
                <p class="ag-section__desc" style="margin:0 auto;">Alliance Groupe est né de la rencontre de trois héritages qui façonnent notre regard sur le travail bien fait, la relation client et le goût du détail.</p>
            </div>
            <div class="ag-heritage-grid">
                <a href="<?php echo esc_url( home_url( '/bureau-nantes' ) ); ?>" class="ag-heritage-card ag-heritage-card--fr" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇫🇷</span>
                    <h3>Nantes</h3>
                    <p>La rigueur française, le goût du produit fini, le respect du client. Notre cœur opérationnel et commercial — 4 profils clés.</p>
                </a>
                <a href="<?php echo esc_url( home_url( '/bureau-naples' ) ); ?>" class="ag-heritage-card ag-heritage-card--it" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇮🇹</span>
                    <h3>Naples</h3>
                    <p>D'où tout a commencé. L'élégance du style, le feu du quartier, notre pôle technique mené par Carlito.</p>
                </a>
                <a href="<?php echo esc_url( home_url( '/bureau-marrakech' ) ); ?>" class="ag-heritage-card ag-heritage-card--ma" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇲🇦</span>
                    <h3>Marrakech</h3>
                    <p>L'hospitalité, la patience artisanale, le long terme. Notre pôle SEO et IA avec Halim et Amina.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg' ); ?>');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"Notre mission : rendre la puissance du digital accessible à toutes les entreprises ambitieuses."</p>
            <p class="ag-parallax__caption" style="margin-top:14px;color:var(--color-text-secondary);font-size:.88rem;letter-spacing:.5px;text-transform:uppercase;">— Quartieri Spagnoli, Naples</p>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
