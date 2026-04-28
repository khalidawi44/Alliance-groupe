/**
 * AG Premium Avocat — runtime hardening.
 *
 * Le theme injecte du CSS via pro-scripts.js apres le chargement, ce qui
 * peut, dans certains scenarios de cache ou d'ordre d'injection, ecraser
 * nos overrides CSS. Ici on force par inline style (priorite maximale)
 * les 2 fixes Premium qui doivent gagner peu importe l'ordre.
 *
 * Convention : aucune selection sur classes Free directement, sauf via
 * verification explicite que body a la classe ag-premium-active /
 * ag-premium-only.
 */
(function () {
    'use strict';

    function isPremiumActive() {
        return document.body.classList.contains('ag-premium-active');
    }
    function isPremiumOnly() {
        return document.body.classList.contains('ag-premium-only');
    }
    function isLight() {
        return document.body.classList.contains('ag-light');
    }

    /* ── Fix 1 : background-attachment fixed en mode jour ─────────
       On force l'inline style sur .ag-hero et .ag-page-hero quand on
       est en Premium + light. On le fait au chargement et a chaque
       changement de classe sur body (toggle jour/nuit). */
    function applyBgFix() {
        if (!isPremiumActive()) return;
        var attachment = isLight() ? 'fixed' : '';
        document.querySelectorAll('.ag-hero, .ag-page-hero').forEach(function (el) {
            el.style.setProperty('background-attachment', attachment, 'important');
        });
    }

    /* ── Fix 2 : injection du logo SVG si manquant ────────────────
       Si on est en Premium-only, qu'il n'y a pas de custom_logo, et que
       le SVG Premium n'est pas deja present (ex: cache, sync incomplet),
       on l'injecte au runtime. */
    function injectLogoSvg() {
        if (!isPremiumOnly()) return;
        var brand = document.querySelector('.ag-site-brand a');
        if (!brand) return;
        if (brand.querySelector('img.custom-logo')) return; // user a un custom_logo
        if (brand.querySelector('.ag-premium-logo-svg, .ag-default-logo-svg')) return; // deja la

        var svgMarkup = '<span class="ag-premium-logo-svg" aria-hidden="true">' +
            '<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">' +
            '<defs>' +
            '<linearGradient id="agPremiumLogoGoldJs" x1="0%" y1="0%" x2="0%" y2="100%">' +
            '<stop offset="0%" stop-color="#FFE5A0"/>' +
            '<stop offset="50%" stop-color="#D4B45C"/>' +
            '<stop offset="100%" stop-color="#9A7A2E"/>' +
            '</linearGradient>' +
            '<radialGradient id="agPremiumLogoHaloJs" cx="50%" cy="50%" r="50%">' +
            '<stop offset="0%" stop-color="#FFE5A0" stop-opacity=".35"/>' +
            '<stop offset="60%" stop-color="#D4B45C" stop-opacity=".12"/>' +
            '<stop offset="100%" stop-color="#D4B45C" stop-opacity="0"/>' +
            '</radialGradient>' +
            '</defs>' +
            '<circle cx="32" cy="32" r="31" fill="url(#agPremiumLogoHaloJs)"/>' +
            '<circle cx="32" cy="6" r="3.5" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<rect x="30" y="9" width="4" height="42" fill="url(#agPremiumLogoGoldJs)" rx="1"/>' +
            '<rect x="9" y="14" width="46" height="3.5" fill="url(#agPremiumLogoGoldJs)" rx="1.5"/>' +
            '<circle cx="10" cy="15.7" r="3" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<circle cx="54" cy="15.7" r="3" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<rect x="9" y="18" width="2" height="6" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<rect x="53" y="18" width="2" height="6" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<path d="M2 24 L18 24 L10 32 Z" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<path d="M46 24 L62 24 L54 32 Z" fill="url(#agPremiumLogoGoldJs)"/>' +
            '<path d="M22 51 L42 51 L46 58 L18 58 Z" fill="url(#agPremiumLogoGoldJs)"/>' +
            '</svg>' +
            '</span>';
        brand.insertAdjacentHTML('afterbegin', svgMarkup);
    }

    /* Helper : recupere les overrides Customizer passes via wp_localize_script */
    function premiumData() {
        return (typeof agPremiumData !== 'undefined') ? agPremiumData : { texts: {}, faq: [] };
    }
    function premiumText(key, fallback) {
        var t = premiumData().texts || {};
        return (t[key] && String(t[key]).trim() !== '') ? t[key] : fallback;
    }

    /* ── Fix 3 : transforme la liste "Comment sont calcules..." en
       accordeon FAQ avec questions/reponses (page Honoraires).
       Les Q/R viennent du Customizer (agPremiumData.faq). */
    function injectHonorairesFaq() {
        if (!isPremiumActive()) return;
        var articles = document.querySelectorAll('.ag-page-article');
        var faqData = (premiumData().faq && premiumData().faq.length) ? premiumData().faq : [
            { q: 'Comment se passe la consultation initiale ?', a: 'Le premier rendez-vous (45 minutes a 1 heure) sert a analyser votre situation.' },
            { q: 'Qu’est-ce qu’un forfait ?',                   a: 'Un prix fixe convenu a l’avance pour traiter un dossier defini.' },
            { q: 'Comment fonctionne la facturation au temps passe ?', a: 'Le taux horaire est convenu a l’avance, releve detaille fourni.' },
            { q: 'Qu’est-ce qu’un honoraire de resultat ?',     a: 'Complement en pourcentage du gain obtenu, conditionne par la reussite.' },
            { q: 'L’aide juridictionnelle est-elle acceptee ?', a: 'Oui, le cabinet accepte les dossiers eligibles.' }
        ];

        articles.forEach(function (article) {
            if (article.querySelector('.ag-premium-faq')) return; // deja injecte
            var h2 = article.querySelector('h2');
            if (!h2 || h2.textContent.indexOf('Comment sont calcules') === -1) return;
            var ul = article.querySelector('ul');
            if (!ul) return;

            var faq = document.createElement('div');
            faq.className = 'ag-premium-faq';
            faqData.forEach(function (item, idx) {
                var entry = document.createElement('details');
                entry.className = 'ag-premium-faq__entry';
                if (idx === 0) entry.open = true;

                var summary = document.createElement('summary');
                summary.className = 'ag-premium-faq__question';
                summary.textContent = item.q;

                var answer = document.createElement('div');
                answer.className = 'ag-premium-faq__answer';
                answer.textContent = item.a;

                entry.appendChild(summary);
                entry.appendChild(answer);
                faq.appendChild(entry);
            });

            ul.parentNode.replaceChild(faq, ul);
        });
    }

    /* ── Fix 4 : override des titres/accroches de sections ──
       Les textes Free etant en dur dans les templates, on les remplace
       au runtime quand l'utilisateur a modifie la valeur dans le
       Customizer Premium. Detection robuste par texte original. */
    function overrideSectionTexts() {
        if (!isPremiumActive()) return;
        var rules = [
            // Domaines d'expertise
            { selector: '.ag-domaines .ag-section-title', defaultText: "Domaines d'expertise", key: 'domaines_title' },
            { selector: '.ag-domaines .ag-section-lead',  matchSubstr: 'principaux domaines', key: 'domaines_lead' },
            // Honoraires
            { selector: '.ag-honoraires .ag-section-title', defaultText: 'Honoraires', key: 'honoraires_title' },
            { selector: '.ag-honoraires .ag-section-lead',  matchSubstr: 'Transparence totale', key: 'honoraires_lead' },
            // Maitre tag
            { selector: '.ag-maitre__tag', defaultText: 'Le Maître', key: 'maitre_tag' },
            // FAQ Honoraires : H2 et intro
            { selector: '.ag-page-article h2', matchSubstr: 'Comment sont calcules', key: 'faq_h2' },
            { selector: '.ag-page-article p', matchSubstr: "convention d'honoraires", key: 'faq_intro' }
        ];

        rules.forEach(function (rule) {
            var el = null;
            var nodes = document.querySelectorAll(rule.selector);
            nodes.forEach(function (n) {
                if (el) return;
                var t = (n.textContent || '').trim();
                if (rule.defaultText && t === rule.defaultText) el = n;
                else if (rule.matchSubstr && t.indexOf(rule.matchSubstr) !== -1) el = n;
                else if (!rule.defaultText && !rule.matchSubstr) el = n;
            });
            if (!el) return;
            var newText = premiumText(rule.key, null);
            if (newText && newText !== el.textContent.trim()) {
                el.textContent = newText;
            }
        });
    }

    function run() {
        applyBgFix();
        injectLogoSvg();
        overrideSectionTexts();
        injectHonorairesFaq();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }

    /* Re-apply on body class changes (toggle jour/nuit). */
    if (typeof MutationObserver !== 'undefined') {
        new MutationObserver(applyBgFix).observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
})();
