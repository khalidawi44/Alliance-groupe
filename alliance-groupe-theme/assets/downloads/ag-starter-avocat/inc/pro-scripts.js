/**
 * AG Starter Avocat Pro — animations + sticky header + smooth scroll.
 * Targets .ag-site-header (avocat-specific class).
 */
(function(){
    'use strict';

    // Sticky header
    var header = document.querySelector('.ag-site-header');
    if (header) {
        var scrolled = false;
        window.addEventListener('scroll', function(){
            var s = window.scrollY > 60;
            if (s !== scrolled) {
                scrolled = s;
                header.classList.toggle('scrolled', s);
            }
        }, {passive:true});
    }

    // Smooth scroll for anchor links
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a[href*="#"]');
        if (!link) return;
        var href = link.getAttribute('href');
        var hash = href.indexOf('#') !== -1 ? href.substring(href.indexOf('#')) : '';
        if (!hash || hash === '#') return;
        var target = document.querySelector(hash);
        if (!target) return;
        e.preventDefault();
        var offset = header ? header.offsetHeight + 10 : 0;
        var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
        if (history.pushState) history.pushState(null, null, hash);
    });

    // Mobile menu toggle
    var toggle = document.querySelector('.ag-menu-toggle');
    var menu = document.querySelector('.ag-primary-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            var open = menu.classList.toggle('open');
            toggle.classList.toggle('active', open);
            toggle.setAttribute('aria-expanded', open);
        });
        menu.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                menu.classList.remove('open');
                toggle.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Auto-apply animation classes
    if (!document.body.classList.contains('ag-has-animations')) return;

    var selectors = [
        '.ag-hero__title', '.ag-hero__subtitle', '.ag-hero .ag-btn',
        '.ag-domaine-card', '.ag-honoraires__card',
        '.ag-maitre', '.ag-cabinet', '.ag-rdv',
        '.ag-info', '.ag-testimonial-card',
        '.ag-section > .ag-container > h2',
        '.ag-section > .ag-container > p',
        '.ag-footer-col'
    ].join(',');

    var elements = document.querySelectorAll(selectors);
    elements.forEach(function(el, i) {
        if (!el.classList.contains('ag-fade-in')) {
            el.classList.add('ag-fade-in');
            el.style.transitionDelay = Math.min(i * 0.06, 0.6) + 's';
        }
    });

    // IntersectionObserver
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.ag-fade-in').forEach(function(el){ el.classList.add('visible'); });
        return;
    }

    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.ag-fade-in').forEach(function(el){ obs.observe(el); });
})();
