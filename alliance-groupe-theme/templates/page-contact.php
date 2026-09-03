<?php
/**
 * Template Name: Contact
 */
get_header();
?>

<main>
    <section class="ag-hero" style="min-height:50vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Contact</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Parlons de votre <em>projet</em></span>
            </h1>
            <p class="ag-hero__sub">Réservez un appel stratégique gratuit ou envoyez-nous un message.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div class="ag-contact__grid">
                <!-- Left: contact cards -->
                <div>
                    <div class="ag-contact-cards">
                        <div class="ag-contact-card ag-anim" data-anim="card">
                            <div class="ag-contact-card__icon"></div>
                            <div class="ag-contact-card__label">Téléphone</div>
                            <div class="ag-contact-card__value">
                                <a href="tel:+33744829516">07.44.82.95.16</a>
                            </div>
                        </div>
                        <div class="ag-contact-card ag-anim" data-anim="card">
                            <div class="ag-contact-card__icon"></div>
                            <div class="ag-contact-card__label">Email</div>
                            <div class="ag-contact-card__value">
                                <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a>
                            </div>
                        </div>
                        <div class="ag-contact-card ag-anim" data-anim="card">
                            <div class="ag-contact-card__icon"></div>
                            <div class="ag-contact-card__label">Basé à</div>
                            <div class="ag-contact-card__value">Naples (Italie) &amp; Nantes</div>
                        </div>
                    </div>
                </div>

                <!-- Right: contact form -->
                <div>
                    <?php if (shortcode_exists('wpforms')) : ?>
                        <?php echo do_shortcode('[wpforms id="123"]'); ?>
                    <?php else : ?>
                    <form class="ag-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                        <input type="hidden" name="action" value="ag_contact_form">
                        <?php wp_nonce_field('ag_contact_nonce', 'ag_nonce'); ?>

                        <div class="ag-form__row">
                            <div class="ag-form__group">
                                <label for="ag-name">Nom complet</label>
                                <input type="text" id="ag-name" name="name" required placeholder="Votre nom">
                            </div>
                            <div class="ag-form__group">
                                <label for="ag-email">Email</label>
                                <input type="email" id="ag-email" name="email" required placeholder="votre@email.com">
                            </div>
                        </div>

                        <div class="ag-form__group">
                            <label for="ag-service">Service souhaité</label>
                            <select id="ag-service" name="service">
                                <option value="">Sélectionnez un service</option>
                                <option value="creation-web">Création Web</option>
                                <option value="ia">IA & Automatisation</option>
                                <option value="seo">SEO</option>
                                <option value="publicite">Publicité Digitale</option>
                                <option value="branding">Branding</option>
                                <option value="conseil">Conseil Stratégique</option>
                            </select>
                        </div>

                        <div class="ag-form__group">
                            <label for="ag-message">Message</label>
                            <textarea id="ag-message" name="message" required placeholder="Décrivez votre projet..."></textarea>
                        </div>

                        <button type="submit" class="ag-btn-gold">Envoyer le message →</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CTA — pointe vers l'offre sur-mesure ── -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <div class="ag-rdv-cta">
                <div class="ag-rdv-cta__content">
                    <span class="ag-tag ag-anim" data-anim="tag">Projet ambitieux ?</span>
                    <h2 class="ag-rdv-cta__title">Un site <em>sur-mesure</em>, sur devis</h2>
                    <p class="ag-rdv-cta__sub">
                        E-commerce complexe, plateforme, fonctionnalités avancées, IA &amp; automatisations.
                        On conçoit tout sur-mesure. Devis gratuit, sans engagement.
                    </p>
                    <a href="<?php echo esc_url( home_url( '/sur-mesure' ) ); ?>" class="ag-btn-gold">
                        Découvrir le sur-mesure <span>→</span>
                    </a>
                </div>
                <div class="ag-rdv-cta__visual" aria-hidden="true">
                    <div class="ag-rdv-cta__calendar">
                        <div class="ag-rdv-cta__calendar-head"></div>
                        <div class="ag-rdv-cta__calendar-dot"></div>
                        <div class="ag-rdv-cta__calendar-dot"></div>
                        <div class="ag-rdv-cta__calendar-dot ag-rdv-cta__calendar-dot--active"></div>
                        <div class="ag-rdv-cta__calendar-dot"></div>
                        <div class="ag-rdv-cta__calendar-dot"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
