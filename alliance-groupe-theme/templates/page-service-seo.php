<?php
/**
 * Template Name: Service — SEO
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">SEO</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Dominez <em>Google</em></span>
                <span class="ag-line">sans payer de publicité</span>
            </h1>
            <p class="ag-hero__sub">Référencement naturel stratégique pour attirer un trafic qualifié et durable.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gains concrets</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Le SEO en <em>résultats</em></h2>
            <div class="ag-gains__grid">
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">Top 3</div>
                    <div class="ag-gain-card__label">Positions Google</div>
                    <div class="ag-gain-card__desc">Sur vos mots-clés stratégiques en 3 à 6 mois.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">+250%</div>
                    <div class="ag-gain-card__label">Trafic organique</div>
                    <div class="ag-gain-card__desc">Augmentation moyenne du trafic sur 12 mois.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">0€</div>
                    <div class="ag-gain-card__label">Coût par clic</div>
                    <div class="ag-gain-card__desc">Le trafic organique est gratuit, à vie.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1920&q=80');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"Le meilleur endroit pour cacher un cadavre, c'est la page 2 de Google."</p>
        </div>
    </section>

    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <div class="ag-sdetail">
                <div>
                    <span class="ag-tag ag-anim" data-anim="tag">En détail</span>
                    <h2 class="ag-section__title ag-anim" data-anim="title">Notre approche <em>SEO</em></h2>
                    <p class="ag-about__text ag-anim" data-anim="desc">Une stratégie SEO complète et sur-mesure qui combine technique, contenu et autorité.</p>
                    <ul class="ag-sdetail__checklist">
                        <li>Audit SEO technique complet</li>
                        <li>Recherche de mots-clés stratégiques</li>
                        <li>Optimisation on-page (titres, metas, structure)</li>
                        <li>Création de contenu optimisé</li>
                        <li>Netlinking et autorité de domaine</li>
                        <li>SEO local (Google My Business)</li>
                        <li>Reporting mensuel détaillé</li>
                    </ul>
                </div>
                <div class="ag-sdetail__visual ag-sdetail__visual--grid">
                    <div class="ag-sdetail__feat">🔍<strong>Audit technique</strong><span>Complet</span></div>
                    <div class="ag-sdetail__feat">🎯<strong>Mots-clés</strong><span>Stratégiques</span></div>
                    <div class="ag-sdetail__feat">📝<strong>Contenu</strong><span>Optimisé</span></div>
                    <div class="ag-sdetail__feat">🔗<strong>Netlinking</strong><span>Autorité</span></div>
                    <div class="ag-sdetail__feat">📍<strong>SEO Local</strong><span>Google Maps</span></div>
                    <div class="ag-sdetail__feat">📊<strong>Reporting</strong><span>Mensuel</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <div class="ag-client ag-anim" data-anim="card">
                <span class="ag-client__tag">Étude de cas</span>
                <h3 class="ag-client__title">L.A Environnement — SEO local dominant</h3>
                <p class="ag-client__text">Stratégie SEO locale complète pour un paysagiste en Loire-Atlantique. En 4 mois, le site est passé de la page 3 au Top 3 Google sur "paysagiste Loire-Atlantique" et génère maintenant 15+ devis par mois.</p>
                <div class="ag-client__stats">
                    <span class="ag-client__stat">Top 3 Google local</span>
                    <span class="ag-client__stat">+320% devis</span>
                    <span class="ag-client__stat">4 mois pour résultats</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
