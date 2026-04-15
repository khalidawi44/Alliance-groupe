<footer class="ag-footer">
    <div class="ag-footer__inner">
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Alliance Groupe</h4>
            <span class="ag-heritage-strip" aria-hidden="true"></span>
            <p class="ag-footer__text">Agence Web &amp; IA basée en France. Racines franco-italo-marocaines, vision internationale. Nous transformons votre présence digitale en machine à générer des leads.</p>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Services</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/service-creation-web')); ?>">Création Web</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-ia')); ?>">IA & Automatisation</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-seo')); ?>">SEO</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-publicite')); ?>">Publicité</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-branding')); ?>">Branding</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-conseil')); ?>">Conseil</a></li>
            </ul>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Liens</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/realisations')); ?>">Réalisations</a></li>
                <li><a href="<?php echo esc_url(home_url('/a-propos')); ?>">À propos</a></li>
                <li><a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a></li>
                <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a></li>
            </ul>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Contact</h4>
            <ul>
                <li><a href="tel:+33623526074">06.23.52.60.74</a></li>
                <li><a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a></li>
            </ul>
        </div>
    </div>
    <div class="ag-footer__bottom">
        <p>&copy; <?php echo date('Y'); ?> Alliance Groupe. Tous droits réservés.</p>
        <p class="ag-footer__legal">
            <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales & CGV</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( home_url( '/cookies' ) ); ?>">Cookies</a>
            &nbsp;·&nbsp;
            <a href="#" onclick="event.preventDefault(); window.AGCookies && window.AGCookies.open();">Gérer mes préférences</a>
        </p>
    </div>
</footer>

<!-- Back to top — APRÈS </footer> -->
<button class="ag-totop" id="ag-totop" aria-label="Retour en haut">↑</button>

<!-- ── Cookie Consent Banner (RGPD / CNIL compliant) ─────────── -->
<div class="ag-cookie" id="ag-cookie" role="dialog" aria-live="polite" aria-labelledby="ag-cookie-title" hidden>
    <div class="ag-cookie__inner">
        <div class="ag-cookie__head">
            <h2 class="ag-cookie__title" id="ag-cookie-title">🍪 Votre vie privée, votre choix</h2>
            <p class="ag-cookie__text">
                Nous utilisons des cookies pour faire fonctionner le site, mesurer son audience et améliorer votre expérience. Vous pouvez accepter, refuser ou personnaliser vos choix. Votre décision est conservée 6 mois et modifiable à tout moment depuis la page <a href="<?php echo esc_url( home_url( '/cookies' ) ); ?>">Cookies</a>.
            </p>
        </div>

        <!-- Vue 1 : 3 boutons principaux -->
        <div class="ag-cookie__actions" id="ag-cookie-actions">
            <button type="button" class="ag-btn-outline ag-cookie__btn" data-ag-cookie="reject">Tout refuser</button>
            <button type="button" class="ag-btn-outline ag-cookie__btn" data-ag-cookie="customize">Personnaliser</button>
            <button type="button" class="ag-btn-gold ag-cookie__btn" data-ag-cookie="accept">Tout accepter</button>
        </div>

        <!-- Vue 2 : personnalisation (cachée par défaut) -->
        <div class="ag-cookie__panel" id="ag-cookie-panel" hidden>
            <ul class="ag-cookie__list">
                <li class="ag-cookie__item">
                    <label class="ag-cookie__label">
                        <input type="checkbox" checked disabled data-ag-cat="necessary">
                        <span class="ag-cookie__cat"><strong>Essentiels</strong> <em>(toujours actifs)</em></span>
                    </label>
                    <p class="ag-cookie__desc">Indispensables au fonctionnement du site : session, sécurité, préférences linguistiques. Sans eux, le site ne peut pas fonctionner correctement.</p>
                </li>
                <li class="ag-cookie__item">
                    <label class="ag-cookie__label">
                        <input type="checkbox" data-ag-cat="functional">
                        <span class="ag-cookie__cat"><strong>Fonctionnels</strong></span>
                    </label>
                    <p class="ag-cookie__desc">Améliorent l'expérience : mémorisation de vos choix, intégration de Calendly pour la prise de rendez-vous, lecture de vidéos intégrées.</p>
                </li>
                <li class="ag-cookie__item">
                    <label class="ag-cookie__label">
                        <input type="checkbox" data-ag-cat="analytics">
                        <span class="ag-cookie__cat"><strong>Mesure d'audience</strong></span>
                    </label>
                    <p class="ag-cookie__desc">Nous permettent de comprendre de façon anonyme comment vous utilisez le site (pages vues, temps passé) afin de l'améliorer.</p>
                </li>
                <li class="ag-cookie__item">
                    <label class="ag-cookie__label">
                        <input type="checkbox" data-ag-cat="marketing">
                        <span class="ag-cookie__cat"><strong>Marketing</strong></span>
                    </label>
                    <p class="ag-cookie__desc">Personnalisent les publicités sur ce site et sur d'autres sites, mesurent l'efficacité de nos campagnes.</p>
                </li>
            </ul>
            <div class="ag-cookie__actions">
                <button type="button" class="ag-btn-outline ag-cookie__btn" data-ag-cookie="reject">Tout refuser</button>
                <button type="button" class="ag-btn-gold ag-cookie__btn" data-ag-cookie="save">Enregistrer mes choix</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    var KEY = 'ag_cookie_consent';
    var DAYS = 180; // 6 mois (recommandation CNIL)
    var $root = document.getElementById('ag-cookie');
    var $panel = document.getElementById('ag-cookie-panel');
    var $actions = document.getElementById('ag-cookie-actions');
    if (!$root) return;

    function readCookie(name){
        var m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return m ? decodeURIComponent(m.pop()) : null;
    }
    function writeCookie(name, value, days){
        var d = new Date();
        d.setTime(d.getTime() + days*24*60*60*1000);
        document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }
    function getConsent(){
        var raw = readCookie(KEY) || localStorage.getItem(KEY);
        if (!raw) return null;
        try { return JSON.parse(raw); } catch(e) { return null; }
    }
    function saveConsent(obj){
        obj.timestamp = Date.now();
        obj.version = 1;
        var s = JSON.stringify(obj);
        writeCookie(KEY, s, DAYS);
        try { localStorage.setItem(KEY, s); } catch(e) {}
        document.dispatchEvent(new CustomEvent('ag:consent', { detail: obj }));
    }
    function show(){ $root.hidden = false; document.body.classList.add('ag-cookie-open'); }
    function hide(){ $root.hidden = true; document.body.classList.remove('ag-cookie-open'); }

    // Affiche la bannière si pas encore de choix
    if (!getConsent()) {
        // Petit délai pour ne pas bloquer le LCP
        setTimeout(show, 600);
    }

    // Clics sur les boutons
    $root.addEventListener('click', function(e){
        var btn = e.target.closest('[data-ag-cookie]');
        if (!btn) return;
        var action = btn.getAttribute('data-ag-cookie');

        if (action === 'accept') {
            saveConsent({ necessary:true, functional:true, analytics:true, marketing:true });
            hide();
        } else if (action === 'reject') {
            saveConsent({ necessary:true, functional:false, analytics:false, marketing:false });
            hide();
        } else if (action === 'customize') {
            $actions.hidden = true;
            $panel.hidden = false;
        } else if (action === 'save') {
            var choices = { necessary:true };
            $panel.querySelectorAll('input[data-ag-cat]').forEach(function(cb){
                var cat = cb.getAttribute('data-ag-cat');
                if (cat === 'necessary') return;
                choices[cat] = cb.checked;
            });
            saveConsent(choices);
            hide();
        }
    });

    // API publique pour rouvrir depuis la page /cookies ou un lien footer
    window.AGCookies = {
        open: function(){ $actions.hidden = false; $panel.hidden = true; show(); },
        openCustomize: function(){
            var c = getConsent() || {};
            $panel.querySelectorAll('input[data-ag-cat]').forEach(function(cb){
                var cat = cb.getAttribute('data-ag-cat');
                if (cat === 'necessary') return;
                cb.checked = !!c[cat];
            });
            $actions.hidden = true;
            $panel.hidden = false;
            show();
        },
        get: getConsent,
        reset: function(){
            document.cookie = KEY + '=;expires=Thu,01 Jan 1970 00:00:00 GMT;path=/';
            try { localStorage.removeItem(KEY); } catch(e) {}
            window.AGCookies.open();
        }
    };
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
