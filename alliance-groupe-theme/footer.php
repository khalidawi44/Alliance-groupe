<footer class="ag-footer">
    <div class="ag-footer__inner">
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Alliance Groupe</h4>
            <p class="ag-footer__slogan" style="font-style:italic;color:#D4B45C;margin:.2em 0 .6em;">La beauté d'un site, la solidité d'un coffre-fort.</p>
            <span class="ag-heritage-strip" aria-hidden="true"></span>
            <p class="ag-footer__text">Studio web indépendant à Nantes : audit de sécurité, création de sites qui inspirent confiance, et maintenance. Un seul interlocuteur, du conseil à la livraison.</p>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Services</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>">Audit de sécurité</a></li>
                <li><a href="<?php echo esc_url(home_url('/sites-express')); ?>">Création de site</a></li>
                <li><a href="<?php echo esc_url(home_url('/maintenance')); ?>">Maintenance</a></li>
                <li><a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>">Templates métier</a></li>
                <li><a href="<?php echo esc_url(home_url('/devis-instant')); ?>">Devis instantané&nbsp;</a></li>
                <li><a href="<?php echo esc_url(home_url('/refais-mon-site')); ?>">Refais mon site&nbsp;</a></li>
            </ul>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Liens</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/realisations')); ?>">Réalisations</a></li>
                <li><a href="<?php echo esc_url(home_url('/a-propos')); ?>">À propos</a></li>
                <li><a href="<?php echo esc_url( function_exists( 'ag_geo_review_url' ) ? ag_geo_review_url() : home_url('/avis-clients') ); ?>" target="_blank" rel="noopener">Donnez votre avis</a></li>
                <li><a href="<?php echo esc_url(home_url('/fait-par-lia')); ?>">Fait par l'IA</a></li>
                <li><a href="<?php echo esc_url(home_url('/mon-espace-client')); ?>">Espace client</a></li>
                <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a></li>
            </ul>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Contact</h4>
            <ul>
                <li><a href="tel:+33744829516">07.44.82.95.16</a></li>
                <li><a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a></li>
            </ul>
            <h4 class="ag-footer__title" style="margin-top:1.2em;">Suivez-nous</h4>
            <div class="ag-footer__social">
                <a href="https://www.youtube.com/@advisealliance2078" target="_blank" rel="noopener" aria-label="YouTube Alliance Groupe" class="ag-footer__soc ag-footer__soc--yt">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.12 2.14C4.5 20.45 12 20.45 12 20.45s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z"/></svg>
                    <span>YouTube</span>
                </a>
            </div>
        </div>
    </div>
    <style>
    .ag-footer__social{display:flex;flex-wrap:wrap;gap:10px;margin-top:.3em}
    .ag-footer__soc{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;
        border:1px solid rgba(212,180,92,.35);color:#e8e8ee;text-decoration:none;font-weight:700;font-size:.9rem;transition:.2s}
    .ag-footer__soc:hover{border-color:#D4B45C;color:#fff;transform:translateY(-2px)}
    .ag-footer__soc--yt:hover{background:rgba(255,0,0,.14);border-color:#ff3d3d}
    .ag-footer__soc svg{flex:none}
    </style>
    <div class="ag-footer__bottom">
        <p>&copy; <?php echo date('Y'); ?> Alliance Groupe — Studio web &amp; sécurité, Nantes. Tous droits réservés.</p>
        <p class="ag-footer__legal">
            <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales & CGV</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( home_url( '/retours' ) ); ?>">Retours & remboursement</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( home_url( '/livraison' ) ); ?>">Livraison</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( home_url( '/confidentialite' ) ); ?>">Confidentialité</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( home_url( '/cookies' ) ); ?>">Cookies</a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url( home_url( '/plan-du-site' ) ); ?>">Plan du site</a>
            &nbsp;·&nbsp;
            <a href="#" onclick="event.preventDefault(); window.AGCookies && window.AGCookies.open();">Gérer mes préférences</a>
        </p>
    </div>
</footer>

<!-- Back to top — APRÈS </footer> -->
<button class="ag-totop" id="ag-totop" aria-label="Retour en haut"><svg class="ag-totop__fleche" viewBox="0 0 24 24" aria-hidden="true"><line x1="12" y1="20" x2="12" y2="5"/><polyline points="6,11 12,5 18,11"/></svg></button>

<!-- ── Cookie Consent Banner (RGPD / CNIL compliant) ─────────── -->
<div class="ag-cookie" id="ag-cookie" role="dialog" aria-live="polite" aria-labelledby="ag-cookie-title" hidden>
    <div class="ag-cookie__inner">
        <div class="ag-cookie__head">
            <h2 class="ag-cookie__title" id="ag-cookie-title">Votre vie privée, votre choix</h2>
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
                    <p class="ag-cookie__desc">Améliorent l'expérience : mémorisation de vos choix, intégration de Cal.com pour la prise de rendez-vous, lecture de vidéos intégrées.</p>
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
