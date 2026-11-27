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
                    <div id="ag-contact-retour" style="scroll-margin-top:110px;">
                        <div id="ag-contact-ok" role="status" hidden style="border:1px solid rgba(212,175,55,.55);background:rgba(212,175,55,.08);border-radius:14px;padding:24px 20px;">
                            <p style="margin:0 0 8px;font-size:1.25rem;font-weight:700;color:#d4af37;">Message bien reçu.</p>
                            <p style="margin:0 0 16px;line-height:1.6;">Fabrizio vous répond sous 24 h ouvrées, à l'adresse email que vous avez indiquée. Pensez à regarder vos courriers indésirables.</p>
                            <p style="margin:0 0 16px;line-height:1.6;">Vous préférez en parler tout de suite ?</p>
                            <a href="tel:+33744829516" class="ag-btn-gold" style="max-width:100%;box-sizing:border-box;justify-content:center;">Appeler le 07.44.82.95.16</a>
                        </div>
                        <div id="ag-contact-ko" role="alert" hidden style="border:1px solid rgba(224,90,70,.7);background:rgba(224,90,70,.1);border-radius:14px;padding:18px 20px;margin-bottom:20px;">
                            <p style="margin:0 0 6px;font-weight:700;">Votre message n'est pas parti.</p>
                            <p id="ag-contact-ko-txt" style="margin:0;line-height:1.6;">Vérifiez votre nom, votre email et votre message, puis renvoyez le formulaire. Vous pouvez aussi appeler le <a href="tel:+33744829516">07.44.82.95.16</a>.</p>
                        </div>
                    </div>
                    <form class="ag-form" id="ag-contact-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                        <input type="hidden" name="action" value="ag_contact_form">
                        <?php wp_nonce_field('ag_contact_nonce', 'ag_nonce'); ?>

                        <div class="ag-form__row">
                            <div class="ag-form__group">
                                <label for="ag-name">Nom complet</label>
                                <input type="text" id="ag-name" name="name" required placeholder="Votre nom">
                            </div>
                            <div class="ag-form__group">
                                <label for="ag-email">Email</label>
                                <input type="email" id="ag-email" name="email" required placeholder="votre@email.com" autocomplete="email">
                            </div>
                        </div>

                        <div class="ag-form__group">
                            <label for="ag-phone">Téléphone (facultatif, pour être rappelé)</label>
                            <input type="tel" id="ag-phone" name="phone" placeholder="06 12 34 56 78" autocomplete="tel" inputmode="tel">
                        </div>
                        <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
                            <input type="text" name="ag_confirm_x" tabindex="-1" autocomplete="off" value="">
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
                                <option value="maintenance-serenite">Maintenance : formule Sérénité</option>
                                <option value="maintenance-protection">Maintenance : formule Protection</option>
                                <option value="maintenance-conformite">Maintenance : formule Conformité</option>
                            </select>
                        </div>

                        <div class="ag-form__group">
                            <label for="ag-message">Message</label>
                            <textarea id="ag-message" name="message" required placeholder="Décrivez votre projet..."></textarea>
                        </div>

                        <button type="submit" class="ag-btn-gold">Envoyer le message →</button>
                    </form>
                    <script>
                    /* Arrivée depuis /maintenance (?offre=serenite|protection|conformite) :
                       la formule est déjà choisie et le message est amorcé. Fait côté
                       navigateur pour rester juste même si la page est servie depuis un cache. */
                    (function () {
                        /* Retour après envoi : confirmation ou erreur, lue dans l'adresse. */
                        var q = null;
                        try { q = new URLSearchParams(window.location.search); } catch (e) {}
                        var ok = document.getElementById('ag-contact-ok');
                        var ko = document.getElementById('ag-contact-ko');
                        var form = document.getElementById('ag-contact-form');
                        if (q && q.get('envoye') === '1' && ok) {
                            ok.hidden = false;
                            if (form) { form.style.display = 'none'; }
                            if (typeof window.gtag === 'function') { window.gtag('event', 'generate_lead', { form: 'contact' }); }
                            try { ok.scrollIntoView({ block: 'center' }); } catch (e) {}
                            return;
                        }
                        if (q && q.get('err') && ko) {
                            if (q.get('err') === '2') {
                                var t = document.getElementById('ag-contact-ko-txt');
                                if (t) { t.innerHTML = 'Trop de messages envoyés depuis votre connexion. Appelez le <a href="tel:+33744829516">07.44.82.95.16</a> ou écrivez à <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a>.'; }
                            }
                            ko.hidden = false;
                            try { ko.scrollIntoView({ block: 'center' }); } catch (e) {}
                        }
                        var noms = { serenite: 'Sérénité', protection: 'Protection', conformite: 'Conformité' };
                        var offre = '';
                        try { offre = (new URLSearchParams(window.location.search).get('offre') || '').toLowerCase(); } catch (e) {}
                        if (!noms[offre]) { return; }
                        var sel = document.getElementById('ag-service');
                        var msg = document.getElementById('ag-message');
                        if (sel) { sel.value = 'maintenance-' + offre; }
                        if (msg && !msg.value) {
                            msg.value = 'Bonjour, je souhaite la formule ' + noms[offre] + ' pour mon site.\nAdresse de mon site : ';
                        }
                    })();
                    </script>
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
