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
                            <div class="ag-contact-card__icon">📞</div>
                            <div class="ag-contact-card__label">Téléphone</div>
                            <div class="ag-contact-card__value">
                                <a href="tel:+33623526074">06.23.52.60.74</a>
                            </div>
                        </div>
                        <div class="ag-contact-card ag-anim" data-anim="card">
                            <div class="ag-contact-card__icon">✉️</div>
                            <div class="ag-contact-card__label">Email</div>
                            <div class="ag-contact-card__value">
                                <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a>
                            </div>
                        </div>
                        <div class="ag-contact-card ag-anim" data-anim="card">
                            <div class="ag-contact-card__icon">📍</div>
                            <div class="ag-contact-card__label">Bureaux</div>
                            <div class="ag-contact-card__value">Naples · Nantes · Marrakech</div>
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

    <!-- ── Integrated Booking Widget ──────────────────────────────── -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <div class="ag-booking">
                <div class="ag-booking__header">
                    <span class="ag-tag ag-anim" data-anim="tag">Prise de rendez-vous</span>
                    <h2 class="ag-booking__title">Réservez votre <em>appel stratégique gratuit</em></h2>
                    <p class="ag-booking__sub">30 minutes en visio pour analyser votre projet et identifier les leviers prioritaires. Sans engagement.</p>
                </div>

                <form id="ag-booking-form" class="ag-booking__form" novalidate>
                    <?php wp_nonce_field('ag_booking_nonce', 'ag_booking_nonce'); ?>
                    <input type="hidden" name="action" value="ag_book_appointment">
                    <input type="hidden" name="date" id="ag-booking-date" value="">
                    <input type="hidden" name="time" id="ag-booking-time" value="">

                    <!-- Step 1: Date -->
                    <div class="ag-booking__step">
                        <div class="ag-booking__step-label"><span class="ag-booking__num">1</span> Choisissez un jour</div>
                        <div class="ag-booking__days" id="ag-booking-days"></div>
                    </div>

                    <!-- Step 2: Time -->
                    <div class="ag-booking__step">
                        <div class="ag-booking__step-label"><span class="ag-booking__num">2</span> Choisissez un créneau</div>
                        <div class="ag-booking__slots" id="ag-booking-slots">
                            <p class="ag-booking__placeholder">Sélectionnez d'abord un jour.</p>
                        </div>
                    </div>

                    <!-- Step 3: Details -->
                    <div class="ag-booking__step">
                        <div class="ag-booking__step-label"><span class="ag-booking__num">3</span> Vos coordonnées</div>
                        <div class="ag-booking__fields">
                            <div class="ag-form__row">
                                <div class="ag-form__group">
                                    <label for="ag-book-name">Nom complet</label>
                                    <input type="text" id="ag-book-name" name="name" required placeholder="Votre nom">
                                </div>
                                <div class="ag-form__group">
                                    <label for="ag-book-email">Email</label>
                                    <input type="email" id="ag-book-email" name="email" required placeholder="votre@email.com">
                                </div>
                            </div>
                            <div class="ag-form__row">
                                <div class="ag-form__group">
                                    <label for="ag-book-phone">Téléphone</label>
                                    <input type="tel" id="ag-book-phone" name="phone" required placeholder="06 12 34 56 78">
                                </div>
                                <div class="ag-form__group">
                                    <label for="ag-book-service">Sujet de l'appel</label>
                                    <select id="ag-book-service" name="service">
                                        <option value="">Sélectionnez un service</option>
                                        <option value="creation-web">Création Web</option>
                                        <option value="ia">IA & Automatisation</option>
                                        <option value="seo">SEO</option>
                                        <option value="publicite">Publicité Digitale</option>
                                        <option value="branding">Branding</option>
                                        <option value="conseil">Conseil Stratégique</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ag-form__group">
                                <label for="ag-book-message">Votre projet (facultatif)</label>
                                <textarea id="ag-book-message" name="message" rows="3" placeholder="Quelques mots sur votre projet..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="ag-booking__summary" id="ag-booking-summary" hidden>
                        <span class="ag-booking__summary-icon">📅</span>
                        <span class="ag-booking__summary-text"></span>
                    </div>

                    <button type="submit" class="ag-btn-gold ag-booking__submit" disabled>
                        Confirmer le rendez-vous →
                    </button>

                    <div class="ag-booking__result" id="ag-booking-result" hidden></div>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
(function(){
    var ajaxUrl = '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>';
    var form = document.getElementById('ag-booking-form');
    if (!form) return;

    var daysEl    = document.getElementById('ag-booking-days');
    var slotsEl   = document.getElementById('ag-booking-slots');
    var dateInput = document.getElementById('ag-booking-date');
    var timeInput = document.getElementById('ag-booking-time');
    var summary   = document.getElementById('ag-booking-summary');
    var summaryTxt = summary.querySelector('.ag-booking__summary-text');
    var submitBtn = form.querySelector('.ag-booking__submit');
    var result    = document.getElementById('ag-booking-result');

    var MONTHS = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    var DAYS   = ['dim','lun','mar','mer','jeu','ven','sam'];
    var SLOTS  = ['09:00','10:00','11:00','14:00','15:00','16:00','17:00'];

    // Generate next 14 business days
    function generateDays(){
        daysEl.innerHTML = '';
        var count = 0, d = new Date();
        d.setHours(0,0,0,0);
        d.setDate(d.getDate() + 1); // start tomorrow
        while (count < 14) {
            var dow = d.getDay();
            if (dow !== 0 && dow !== 6) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ag-booking__day';
                var iso = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                btn.dataset.date = iso;
                btn.dataset.label = DAYS[dow] + ' ' + d.getDate() + ' ' + MONTHS[d.getMonth()];
                btn.innerHTML = '<span class="ag-booking__day-dow">' + DAYS[dow] + '</span>' +
                                '<span class="ag-booking__day-num">' + d.getDate() + '</span>' +
                                '<span class="ag-booking__day-month">' + MONTHS[d.getMonth()].slice(0,4) + '</span>';
                btn.addEventListener('click', function(){
                    Array.prototype.forEach.call(daysEl.children, function(c){ c.classList.remove('is-active'); });
                    this.classList.add('is-active');
                    dateInput.value = this.dataset.date;
                    timeInput.value = '';
                    renderSlots(this.dataset.label);
                    updateState();
                });
                daysEl.appendChild(btn);
                count++;
            }
            d.setDate(d.getDate() + 1);
        }
    }

    function renderSlots(dayLabel){
        slotsEl.innerHTML = '';
        SLOTS.forEach(function(slot){
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ag-booking__slot';
            btn.textContent = slot;
            btn.dataset.time = slot;
            btn.addEventListener('click', function(){
                Array.prototype.forEach.call(slotsEl.children, function(c){ c.classList.remove('is-active'); });
                this.classList.add('is-active');
                timeInput.value = this.dataset.time;
                updateState(dayLabel);
            });
            slotsEl.appendChild(btn);
        });
    }

    function updateState(dayLabel){
        if (dateInput.value && timeInput.value) {
            var activeDay = daysEl.querySelector('.is-active');
            var label = dayLabel || (activeDay ? activeDay.dataset.label : '');
            summary.hidden = false;
            summaryTxt.textContent = 'RDV sélectionné : ' + label + ' à ' + timeInput.value;
            submitBtn.disabled = false;
        } else {
            summary.hidden = true;
            submitBtn.disabled = true;
        }
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();
        result.hidden = true;
        result.className = 'ag-booking__result';

        var name  = document.getElementById('ag-book-name').value.trim();
        var email = document.getElementById('ag-book-email').value.trim();
        var phone = document.getElementById('ag-book-phone').value.trim();
        if (!name || !email || !phone) {
            result.hidden = false;
            result.classList.add('is-error');
            result.textContent = 'Merci de remplir nom, email et téléphone.';
            return;
        }
        if (!dateInput.value || !timeInput.value) {
            result.hidden = false;
            result.classList.add('is-error');
            result.textContent = 'Merci de sélectionner un jour et un créneau.';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Envoi en cours...';

        var data = new FormData(form);

        fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(json){
                if (json && json.success) {
                    form.querySelector('.ag-booking__fields').style.opacity = '0.4';
                    result.hidden = false;
                    result.classList.add('is-success');
                    result.innerHTML = '✓ Votre rendez-vous est confirmé. Vous recevrez un email de confirmation dans quelques instants.';
                    submitBtn.textContent = 'Rendez-vous confirmé';
                } else {
                    throw new Error((json && json.data) || 'Erreur');
                }
            })
            .catch(function(err){
                result.hidden = false;
                result.classList.add('is-error');
                result.textContent = 'Erreur : ' + (err.message || 'impossible d\'enregistrer le rendez-vous.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmer le rendez-vous →';
            });
    });

    generateDays();
})();
</script>

<?php get_footer(); ?>
