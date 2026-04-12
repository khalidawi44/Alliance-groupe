<?php
/**
 * Template Name: Service — IA & Automatisation
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">IA & Automatisation</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">L'<em>intelligence artificielle</em></span>
                <span class="ag-line">au service de votre croissance</span>
            </h1>
            <p class="ag-hero__sub">Chatbots, automatisation des process, analyse de données — intégrez l'IA pour gagner du temps et de l'argent.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gains concrets</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">L'IA en <em>chiffres</em></h2>
            <div class="ag-gains__grid">
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">-70%</div>
                    <div class="ag-gain-card__label">Temps de réponse</div>
                    <div class="ag-gain-card__desc">Chatbot IA disponible 24/7 pour qualifier vos prospects.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">15h</div>
                    <div class="ag-gain-card__label">Gagnées / semaine</div>
                    <div class="ag-gain-card__desc">Automatisation des tâches répétitives et du reporting.</div>
                </div>
                <div class="ag-gain-card ag-anim" data-anim="gain">
                    <div class="ag-gain-card__value">+45%</div>
                    <div class="ag-gain-card__label">Taux de conversion</div>
                    <div class="ag-gain-card__desc">Personnalisation IA de l'expérience utilisateur.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-parallax" style="background-image:url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80');">
        <div class="ag-parallax__overlay"></div>
        <div class="ag-parallax__content ag-anim" data-anim="parallax-text">
            <p class="ag-parallax__quote">"L'IA ne remplace pas l'humain. Elle le libère pour qu'il se concentre sur ce qui compte vraiment."</p>
        </div>
    </section>

    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <div class="ag-sdetail">
                <div>
                    <span class="ag-tag ag-anim" data-anim="tag">En détail</span>
                    <h2 class="ag-section__title ag-anim" data-anim="title">Nos solutions <em>IA</em></h2>
                    <p class="ag-about__text ag-anim" data-anim="desc">De la conception à l'intégration, nous créons des solutions IA sur-mesure adaptées à votre métier.</p>
                    <ul class="ag-sdetail__checklist">
                        <li>Chatbots intelligents pour le service client</li>
                        <li>Automatisation des workflows (Zapier, Make, n8n)</li>
                        <li>Analyse prédictive de données</li>
                        <li>Personnalisation dynamique du contenu</li>
                        <li>Intégration CRM automatisée</li>
                        <li>Génération de contenu assistée par IA</li>
                    </ul>
                </div>
                <div class="ag-sdetail__visual ag-sdetail__visual--grid">
                    <div class="ag-sdetail__feat">🤖<strong>Chatbots IA</strong><span>24/7 actifs</span></div>
                    <div class="ag-sdetail__feat">⚙️<strong>Workflows</strong><span>Automatisés</span></div>
                    <div class="ag-sdetail__feat">📈<strong>Analytics</strong><span>Prédictifs</span></div>
                    <div class="ag-sdetail__feat">🔗<strong>Intégrations</strong><span>CRM, API</span></div>
                    <div class="ag-sdetail__feat">✉️<strong>Emails</strong><span>Auto-réponse</span></div>
                    <div class="ag-sdetail__feat">🧠<strong>IA générative</strong><span>Contenu auto</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <div class="ag-client ag-anim" data-anim="card">
                <span class="ag-client__tag">Étude de cas</span>
                <h3 class="ag-client__title">Anna Photo — Photographe à Nantes</h3>
                <p class="ag-client__text">Intégration d'un système de réservation automatisé et d'un chatbot pour gérer les demandes de devis. La photographe a pu se concentrer sur son art tout en doublant son nombre de bookings.</p>
                <div class="ag-client__stats">
                    <span class="ag-client__stat">+180% trafic</span>
                    <span class="ag-client__stat">Booking automatisé</span>
                    <span class="ag-client__stat">2x plus de sessions</span>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>
</main>

<?php get_footer(); ?>
