<?php
/**
 * Template Name: Service — Branding
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Branding</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Une identité <em>mémorable</em></span>
                <span class="ag-line">qui inspire confiance</span>
            </h1>
            <p class="ag-hero__sub">Logo, charte graphique, direction artistique — une image de marque cohérente et impactante.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gains concrets</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Le branding en <em>impact</em></h2>
            <div class="ag-gains__grid">
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">+80%</div>
                    <div class="ag-gain-card__label">Reconnaissance</div>
                    <div class="ag-gain-card__desc">Une marque cohérente est reconnue 80% plus vite.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">x3</div>
                    <div class="ag-gain-card__label">Confiance client</div>
                    <div class="ag-gain-card__desc">Les clients font 3x plus confiance à une marque professionnelle.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">+23%</div>
                    <div class="ag-gain-card__label">Revenus</div>
                    <div class="ag-gain-card__desc">Un branding cohérent augmente les revenus de 23% en moyenne.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . "/assets/images/cities/marrakech-2.jpg" ); ?>');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"Votre marque est ce que les gens disent de vous quand vous n'êtes pas dans la pièce."</p>
        </div>
    </section>

    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <div class="ag-sdetail">
                <div>
                    <span class="ag-tag ag-anim" data-anim="tag">En détail</span>
                    <h2 class="ag-section__title ag-anim" data-anim="title">Nos livrables <em>branding</em></h2>
                    <p class="ag-about__text ag-anim" data-anim="desc">Une identité visuelle complète qui reflète vos valeurs et vous différencie de la concurrence.</p>
                    <ul class="ag-sdetail__checklist">
                        <li>Création de logo et déclinaisons</li>
                        <li>Charte graphique complète</li>
                        <li>Palette de couleurs et typographies</li>
                        <li>Templates réseaux sociaux</li>
                        <li>Supports print (cartes de visite, flyers)</li>
                        <li>Guide de marque et tonalité</li>
                    </ul>
                </div>
                <div class="ag-sdetail__visual ag-sdetail__visual--grid">
                    <div class="ag-sdetail__feat">✏️<strong>Logo</strong><span>+ déclinaisons</span></div>
                    <div class="ag-sdetail__feat">🎨<strong>Charte</strong><span>Graphique</span></div>
                    <div class="ag-sdetail__feat">🔤<strong>Typographies</strong><span>Sur-mesure</span></div>
                    <div class="ag-sdetail__feat">📱<strong>Réseaux</strong><span>Templates</span></div>
                    <div class="ag-sdetail__feat">🖨️<strong>Print</strong><span>Cartes, flyers</span></div>
                    <div class="ag-sdetail__feat">📖<strong>Brand book</strong><span>Guide complet</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <div class="ag-client ag-anim" data-anim="card">
                <span class="ag-client__tag">Étude de cas</span>
                <h3 class="ag-client__title">Anna Photo — Identité visuelle premium</h3>
                <p class="ag-client__text">Création d'une identité visuelle élégante pour une photographe portraitiste. Logo, charte graphique et direction artistique du blog, cohérents avec son univers artistique.</p>
                <div class="ag-client__stats">
                    <span class="ag-client__stat">Logo + Charte</span>
                    <span class="ag-client__stat">+180% trafic</span>
                    <span class="ag-client__stat">Image premium</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
