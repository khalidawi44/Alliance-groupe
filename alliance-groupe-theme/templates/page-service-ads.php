<?php
/**
 * Template Name: Service — Publicité
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Publicité Digitale</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Des campagnes <em>rentables</em></span>
                <span class="ag-line">dès le premier mois</span>
            </h1>
            <p class="ag-hero__sub">Google Ads, Meta Ads, LinkedIn Ads — un ROI mesurable et optimisé en continu.</p>
        </div>
    </section>

    <section class="ag-section" style="background:#0c0c0f;">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gains concrets</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">La pub qui <em>rapporte</em></h2>
            <div class="ag-gains__grid">
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">x4.2</div>
                    <div class="ag-gain-card__label">ROAS moyen</div>
                    <div class="ag-gain-card__desc">Pour chaque euro investi, 4,20€ de retour en moyenne.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">-35%</div>
                    <div class="ag-gain-card__label">Coût par lead</div>
                    <div class="ag-gain-card__desc">Optimisation continue pour réduire le coût d'acquisition.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">48h</div>
                    <div class="ag-gain-card__label">Premiers résultats</div>
                    <div class="ag-gain-card__desc">Contrairement au SEO, la pub génère des leads immédiatement.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"La publicité sans stratégie, c'est jeter de l'argent par la fenêtre. Avec nous, chaque euro travaille."</p>
        </div>
    </section>

    <section class="ag-section" style="background:#101014;">
        <div class="ag-container">
            <div class="ag-sdetail">
                <div>
                    <span class="ag-tag ag-anim" data-anim="tag">En détail</span>
                    <h2 class="ag-section__title ag-anim" data-anim="title">Notre approche <em>Ads</em></h2>
                    <p class="ag-about__text ag-anim" data-anim="desc">Des campagnes publicitaires data-driven, optimisées chaque semaine pour maximiser votre retour sur investissement.</p>
                    <ul class="ag-sdetail__checklist">
                        <li>Audit du marché et des concurrents</li>
                        <li>Création de campagnes Google Ads</li>
                        <li>Campagnes Meta Ads (Facebook & Instagram)</li>
                        <li>Landing pages optimisées pour la conversion</li>
                        <li>A/B testing des créatives et audiences</li>
                        <li>Reporting hebdomadaire transparent</li>
                    </ul>
                </div>
                <div class="ag-sdetail__visual ag-sdetail__visual--grid">
                    <div class="ag-sdetail__feat">🎯<strong>Ciblage</strong><span>Ultra-précis</span></div>
                    <div class="ag-sdetail__feat">📢<strong>Google Ads</strong><span>Search + Display</span></div>
                    <div class="ag-sdetail__feat">📱<strong>Meta Ads</strong><span>FB + Instagram</span></div>
                    <div class="ag-sdetail__feat">🧪<strong>A/B Testing</strong><span>Continu</span></div>
                    <div class="ag-sdetail__feat">📊<strong>ROAS</strong><span>Optimisé</span></div>
                    <div class="ag-sdetail__feat">📋<strong>Reporting</strong><span>Hebdomadaire</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-section" style="background:#0c0c0f;">
        <div class="ag-container">
            <div class="ag-client ag-anim" data-anim="card">
                <span class="ag-client__tag">Étude de cas</span>
                <h3 class="ag-client__title">L.A Environnement — Google Ads local</h3>
                <p class="ag-client__text">Campagne Google Ads ciblant les recherches locales de paysagistes. Budget optimisé avec ciblage géographique précis, générant un flux constant de demandes de devis qualifiées.</p>
                <div class="ag-client__stats">
                    <span class="ag-client__stat">ROAS x5.1</span>
                    <span class="ag-client__stat">12€ / lead</span>
                    <span class="ag-client__stat">+320% devis</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
