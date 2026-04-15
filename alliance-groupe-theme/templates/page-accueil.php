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
<section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80');">
    <div class="ag-parallax__overlay"></div>
    <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
        <p class="ag-parallax__quote">"La technologie seule ne suffit pas. C'est la technologie mariée au design qui nous donne le résultat qui fait chanter notre cœur."</p>
    </div>
</section>

<!-- Promo Templates -->
<section class="ag-section ag-promo-tpl ag-section--onyx">
    <div class="ag-container">
        <div class="ag-promo-tpl__inner ag-anim" data-anim="card">
            <div class="ag-promo-tpl__content">
                <span class="ag-tag ag-tag--green">Nouveau — Gratuit</span>
                <h2 style="font-size:clamp(1.6rem,3vw,2.2rem);margin-bottom:12px;">Téléchargez nos templates <em>WordPress gratuits</em></h2>
                <p style="color:#b0b0bc;font-size:1.05rem;line-height:1.7;margin-bottom:16px;">Pas encore prêt pour un site sur-mesure ? Commencez dès maintenant avec l'un de nos trois <strong style="color:#e8e6e0;">thèmes WordPress 100% français</strong> : restaurant, artisan ou coach. Contenu déjà rédigé, design sombre premium, aucun plugin requis — il ne vous reste qu'à remplacer quelques éléments entre crochets et votre site est en ligne.</p>
                <p style="color:#b0b0bc;font-size:1.05rem;line-height:1.7;margin-bottom:24px;">Vous hésitez sur le choix du template, ou vous bloquez sur l'installation ? <a href="<?php echo esc_url(home_url('/contact')); ?>" style="color:#D4B45C;font-weight:700;">Contactez-nous</a> — on vous aide gratuitement à démarrer, même si vous avez zéro compétence technique.</p>
                <div class="ag-promo-tpl__features">
                    <span>🎨 Design premium</span>
                    <span>📱 100% responsive</span>
                    <span>⚡ Installation en 2 min</span>
                    <span>🆓 Totalement gratuit</span>
                </div>
                <div class="ag-hero__buttons" style="margin-top:24px;">
                    <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>" class="ag-btn-gold">Découvrir les templates gratuits →</a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Besoin d'aide ? On en parle</a>
                </div>
            </div>
            <div class="ag-promo-tpl__visual">
                <div class="ag-promo-tpl__mock">
                    <div class="ag-promo-tpl__mock-bar">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="ag-promo-tpl__mock-content">
                        <div style="font-size:1.8rem;margin-bottom:8px;">🍽️</div>
                        <strong style="color:#D4B45C;font-size:1rem;">AG Starter Restaurant</strong>
                        <small style="color:#b0b0bc;font-size:.75rem;display:block;margin-top:4px;">Thème WordPress gratuit</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Réalisations -->
<?php get_template_part('template-parts/realisations'); ?>

<!-- Parallax 2 -->
<section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1920&q=80');">
    <div class="ag-parallax__overlay"></div>
    <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
        <p class="ag-parallax__quote">"Votre site web est votre meilleur commercial. Il travaille 24h/24, ne prend jamais de vacances et ne demande pas de commission."</p>
    </div>
</section>

<!-- About -->
<?php get_template_part('template-parts/about'); ?>

<!-- FAQ -->
<?php get_template_part('template-parts/faq'); ?>

<!-- CTA -->
<?php get_template_part('template-parts/cta'); ?>

<?php get_footer(); ?>
