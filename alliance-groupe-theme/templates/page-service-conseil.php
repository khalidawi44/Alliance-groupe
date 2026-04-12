<?php
/**
 * Template Name: Service — Conseil
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Conseil Stratégique</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Un regard <em>expert</em></span>
                <span class="ag-line">sur votre stratégie digitale</span>
            </h1>
            <p class="ag-hero__sub">Audit, stratégie de croissance et accompagnement sur-mesure pour accélérer votre transformation digitale.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gains concrets</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Le conseil qui <em>fait la différence</em></h2>
            <div class="ag-gains__grid">
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">360°</div>
                    <div class="ag-gain-card__label">Audit complet</div>
                    <div class="ag-gain-card__desc">Analyse exhaustive de votre présence digitale et de vos opportunités.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">12 mois</div>
                    <div class="ag-gain-card__label">Roadmap claire</div>
                    <div class="ag-gain-card__desc">Plan d'action priorisé avec jalons et KPIs mesurables.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">+200%</div>
                    <div class="ag-gain-card__label">ROI digital</div>
                    <div class="ag-gain-card__desc">Optimisation des investissements digitaux existants.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1920&q=80');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"La stratégie sans exécution est une hallucination. L'exécution sans stratégie est du chaos."</p>
        </div>
    </section>

    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <div class="ag-sdetail">
                <div>
                    <span class="ag-tag ag-anim" data-anim="tag">En détail</span>
                    <h2 class="ag-section__title ag-anim" data-anim="title">Notre accompagnement <em>conseil</em></h2>
                    <p class="ag-about__text ag-anim" data-anim="desc">Un accompagnement stratégique personnalisé pour prendre les bonnes décisions digitales.</p>
                    <ul class="ag-sdetail__checklist">
                        <li>Audit digital complet (site, SEO, réseaux, concurrents)</li>
                        <li>Définition de la stratégie digitale</li>
                        <li>Identification des leviers de croissance</li>
                        <li>Plan d'action priorisé et chiffré</li>
                        <li>Accompagnement mensuel et coaching</li>
                        <li>Formation de vos équipes</li>
                        <li>Reporting et suivi des KPIs</li>
                    </ul>
                </div>
                <div class="ag-sdetail__visual ag-sdetail__visual--grid">
                    <div class="ag-sdetail__feat">🔍<strong>Audit 360°</strong><span>Complet</span></div>
                    <div class="ag-sdetail__feat">📋<strong>Roadmap</strong><span>12 mois</span></div>
                    <div class="ag-sdetail__feat">🎯<strong>KPIs</strong><span>Mesurables</span></div>
                    <div class="ag-sdetail__feat">👥<strong>Formation</strong><span>Équipes</span></div>
                    <div class="ag-sdetail__feat">📊<strong>Reporting</strong><span>Mensuel</span></div>
                    <div class="ag-sdetail__feat">🤝<strong>Coaching</strong><span>Personnalisé</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <div class="ag-client ag-anim" data-anim="card">
                <span class="ag-client__tag">Étude de cas</span>
                <h3 class="ag-client__title">L.A Environnement — Stratégie digitale globale</h3>
                <p class="ag-client__text">Accompagnement stratégique complet : audit, refonte du site, SEO local, Google Ads. Une approche holistique qui a transformé un paysagiste local en leader digital de sa zone.</p>
                <div class="ag-client__stats">
                    <span class="ag-client__stat">Stratégie 360°</span>
                    <span class="ag-client__stat">+320% devis</span>
                    <span class="ag-client__stat">Top 3 Google</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
