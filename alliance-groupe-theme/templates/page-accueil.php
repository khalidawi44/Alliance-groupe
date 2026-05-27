<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- Hero -->
<section class="ag-hero" id="ag-main-content">
    <!-- Photo Naples — Vésuve coucher de soleil -->
    <div class="ag-hero__naples" aria-hidden="true"></div>
    <!-- Halo de soleil/Vésuve qui pulse champagne (visible partout) -->
    <div class="ag-hero__sunglow" aria-hidden="true"></div>
    <!-- Particules dorées montantes en CSS pur (visibles mobile + desktop) -->
    <div class="ag-hero__particles" aria-hidden="true">
        <span style="left:8%;  bottom:-10px; animation-duration:14s; animation-delay:0s;"></span>
        <span style="left:18%; bottom:-10px; animation-duration:18s; animation-delay:3s;"></span>
        <span style="left:27%; bottom:-10px; animation-duration:12s; animation-delay:6s;"></span>
        <span style="left:36%; bottom:-10px; animation-duration:16s; animation-delay:1.5s;"></span>
        <span style="left:44%; bottom:-10px; animation-duration:20s; animation-delay:4.5s;"></span>
        <span style="left:52%; bottom:-10px; animation-duration:13s; animation-delay:7s;"></span>
        <span style="left:60%; bottom:-10px; animation-duration:17s; animation-delay:2s;"></span>
        <span style="left:68%; bottom:-10px; animation-duration:19s; animation-delay:5s;"></span>
        <span style="left:76%; bottom:-10px; animation-duration:15s; animation-delay:8s;"></span>
        <span style="left:84%; bottom:-10px; animation-duration:21s; animation-delay:0.8s;"></span>
        <span style="left:92%; bottom:-10px; animation-duration:14s; animation-delay:3.6s;"></span>
        <span style="left:12%; bottom:-10px; animation-duration:23s; animation-delay:10s;"></span>
    </div>
    <!-- 🌋 Vésuve en éruption : halo + fumée (CSS pur, visible partout) -->
    <div class="ag-hero__vesuvius" aria-hidden="true"></div>
    <div class="ag-hero__vesuvius-smoke" aria-hidden="true"></div>
    <!-- 🚢 Bateaux qui glissent sur la baie -->
    <div class="ag-hero__boats" aria-hidden="true">
        <svg viewBox="0 0 50 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M2 18 L48 18 L42 22 L8 22 Z" fill="currentColor"/>
            <path d="M22 18 L22 4 L34 14 L22 14 Z" fill="currentColor" opacity=".7"/>
            <line x1="22" y1="4" x2="22" y2="18" stroke="currentColor" stroke-width="1"/>
        </svg>
        <svg viewBox="0 0 50 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M4 19 L46 19 L41 22 L9 22 Z" fill="currentColor"/>
            <path d="M25 19 L25 6 L36 16 L25 16 Z" fill="currentColor" opacity=".75"/>
        </svg>
        <svg viewBox="0 0 50 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M2 18 L48 18 L43 22 L7 22 Z" fill="currentColor"/>
            <path d="M20 18 L20 3 L33 14 L20 14 Z" fill="currentColor" opacity=".7"/>
            <line x1="20" y1="3" x2="20" y2="18" stroke="currentColor" stroke-width="1"/>
            <path d="M33 14 L20 14 L20 18 L33 18" fill="none" stroke="currentColor" stroke-width=".6" opacity=".5"/>
        </svg>
    </div>
    <!-- Mesh gradient WebGL conservé en sur-couche très subtile (skippé sur mobile/<4 cores) -->
    <?php get_template_part('template-parts/mesh-gradient-bg'); ?>
    <!-- Grille tech high-tech parallax qui réagit à la souris -->
    <?php get_template_part('template-parts/hero-tech-grid'); ?>
    <!-- Scène 3D Three.js (style Vexik) — desktop ≥1100px et ≥4 cores -->
    <?php get_template_part('template-parts/hero-3d-scene'); ?>
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

<!-- P1/ VENDRE — offres + confiance (4x sans frais, sécurisé) + avis -->
<?php get_template_part('template-parts/home-offres'); ?>

<!-- P2/ GAGNER — Ambassadeur + Studio fusionnés (2 colonnes) -->
<?php get_template_part('template-parts/home-gagner'); ?>

<!-- P3/ SOLIDAIRE — Racines + Associations fusionnés (2 colonnes) -->
<?php get_template_part('template-parts/home-solidaire'); ?>

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
