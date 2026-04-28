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

    function run() {
        applyBgFix();
        injectLogoSvg();
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
