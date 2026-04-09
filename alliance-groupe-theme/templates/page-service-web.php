<?php
/**
 * Template Name: Service — Création Web
 */
get_header();
?>

<main>
    <!-- Hero -->
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Création Web</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Un site web qui <em>vend</em></span>
                <span class="ag-line">pendant que vous dormez</span>
            </h1>
            <p class="ag-hero__sub">Sites vitrines et e-commerce ultra-performants, conçus pour convertir vos visiteurs en clients.</p>
        </div>
    </section>

    <!-- Gains concrets -->
    <section class="ag-section" style="background:#0c0c0f;">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gains concrets</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Des résultats <em>mesurables</em></h2>
            <div class="ag-gains__grid">
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">+340%</div>
                    <div class="ag-gain-card__label">Leads générés</div>
                    <div class="ag-gain-card__desc">En moyenne sur les 12 premiers mois après la mise en ligne.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">18 000€</div>
                    <div class="ag-gain-card__label">Économisés / an</div>
                    <div class="ag-gain-card__desc">Vs. un commercial à temps plein avec charges sociales.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">1.2s</div>
                    <div class="ag-gain-card__label">Temps de chargement</div>
                    <div class="ag-gain-card__desc">Sites optimisés pour la vitesse et le SEO technique.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Parallax -->
    <section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"Un bon site web ne coûte pas, il rapporte."</p>
        </div>
    </section>

    <!-- Détail -->
    <section class="ag-section" style="background:#101014;">
        <div class="ag-container">
            <div class="ag-sdetail">
                <div>
                    <span class="ag-tag ag-anim" data-anim="tag">En détail</span>
                    <h2 class="ag-section__title ag-anim" data-anim="title">Ce que nous <em>livrons</em></h2>
                    <p class="ag-about__text ag-anim" data-anim="desc">Chaque site est conçu sur-mesure avec une approche centrée sur la conversion. Design premium, code propre, performances maximales.</p>
                    <ul class="ag-sdetail__checklist">
                        <li>Design UX/UI sur-mesure et responsive</li>
                        <li>Développement WordPress optimisé</li>
                        <li>Intégration SEO technique dès le départ</li>
                        <li>Formulaires de contact et génération de leads</li>
                        <li>Analytics et suivi des conversions</li>
                        <li>Formation à l'utilisation du site</li>
                        <li>Hébergement premium inclus</li>
                    </ul>
                </div>
                <div class="ag-sdetail__visual ag-sdetail__visual--grid">
                    <div class="ag-sdetail__feat">🎨<strong>Design UX/UI</strong><span>Sur-mesure</span></div>
                    <div class="ag-sdetail__feat">⚡<strong>Performance</strong><span>Score A+</span></div>
                    <div class="ag-sdetail__feat">📱<strong>Responsive</strong><span>100% mobile</span></div>
                    <div class="ag-sdetail__feat">🔒<strong>Sécurité</strong><span>SSL inclus</span></div>
                    <div class="ag-sdetail__feat">📊<strong>Analytics</strong><span>Suivi intégré</span></div>
                    <div class="ag-sdetail__feat">🚀<strong>SEO Ready</strong><span>Dès le départ</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Exemple client -->
    <section class="ag-section" style="background:#0c0c0f;">
        <div class="ag-container">
            <div class="ag-client ag-anim" data-anim="card">
                <span class="ag-client__tag">Étude de cas</span>
                <h3 class="ag-client__title">L.A Environnement — Paysagiste Loire-Atlantique</h3>
                <p class="ag-client__text">Site vitrine conçu pour un paysagiste local. Objectif : dominer le référencement local et automatiser la génération de devis. Résultat : le client est passé de 3 devis/mois à 15 devis/mois en 4 mois.</p>
                <div class="ag-client__stats">
                    <span class="ag-client__stat">+320% devis</span>
                    <span class="ag-client__stat">Top 3 Google local</span>
                    <span class="ag-client__stat">ROI en 3 mois</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
