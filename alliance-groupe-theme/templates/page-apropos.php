<?php
/**
 * Template Name: À propos
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;--hero-photo:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/cities/baie_naples_nuit.jpg' ); ?>');">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">À propos</span>
            <h1 class="ag-hero__title" data-noreveal style="font-size:clamp(1.6rem,3.6vw,2.6rem);line-height:1.18;max-width:760px;margin-left:auto;margin-right:auto;text-wrap:balance;">
                <span class="ag-line" style="display:block;">Un <em>artisan du web</em>,</span>
                <span class="ag-line" style="display:block;">basé à <span style="white-space:nowrap">Naples &amp; Nantes</span></span>
            </h1>
            <span class="ag-heritage-strip ag-heritage-strip--center" aria-hidden="true"></span>
            <p class="ag-hero__sub">Alliance Groupe, c'est <strong>un studio indépendant</strong> : un interlocuteur unique, du conseil à la livraison. Sécurité, création de sites et SEO — sans intermédiaire, sans jargon.</p>
        </div>
    </section>

    <!-- Le studio -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div style="text-align:center;max-width:760px;margin:0 auto 48px;">
                <span class="ag-tag">Le studio</span>
                <h2 class="ag-section__title">Un seul interlocuteur,<br><em>de bout en bout</em></h2>
                <p class="ag-section__desc" style="margin:0 auto;">Né et basé à Naples, et aussi à Nantes, je conçois et sécurise des sites web pour des entreprises ambitieuses. Pas d'usine, pas de sous-traitance opaque : vous parlez directement à la personne qui fait le travail. Pour les projets plus larges, je m'appuie sur un réseau de <strong>partenaires freelances de confiance</strong>, choisis au cas par cas.</p>
                <figure style="max-width:560px;margin:36px auto 0;">
                    <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg' ); ?>" alt="Fabrizio dans son bureau à Naples" loading="lazy" style="width:100%;height:auto;display:block;border-radius:20px;border:1px solid rgba(212,180,92,.4);box-shadow:0 22px 55px rgba(0,0,0,.5);">
                    <figcaption style="text-align:center;color:var(--color-text-secondary);font-size:.85rem;letter-spacing:.5px;margin-top:12px;">Mon bureau, à Naples.</figcaption>
                </figure>
            </div>
            <div class="ag-heritage-grid">
                <div class="ag-heritage-card ag-heritage-card--fr">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🎯</span>
                    <h3>Direct &amp; clair</h3>
                    <p>Un interlocuteur unique, des devis lisibles, des délais tenus. Vous savez toujours où en est votre projet.</p>
                </div>
                <div class="ag-heritage-card ag-heritage-card--it">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🔒</span>
                    <h3>Sécurité d'abord</h3>
                    <p>L'audit de sécurité est mon point d'entrée : on met les choses au clair avant de construire ou corriger.</p>
                </div>
                <div class="ag-heritage-card ag-heritage-card--ma">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🤝</span>
                    <h3>Partenaires au besoin</h3>
                    <p>Pour un projet ambitieux, un réseau de freelances vérifiables (design, rédaction) — assemblé sur-mesure, jamais inventé.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/cities/naples-3.jpg' ); ?>');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"Notre mission : rendre la puissance du digital accessible à toutes les entreprises ambitieuses."</p>
            <p class="ag-parallax__caption" style="margin-top:14px;color:var(--color-text-secondary);font-size:.88rem;letter-spacing:.5px;text-transform:uppercase;">— Naples, le soir</p>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
