(function(){
    'use strict';

    var header = document.querySelector('.ag-site-header');

    // Sticky header: transparent → solid
    if (header) {
        var scrolled = false;
        window.addEventListener('scroll', function(){
            var s = window.scrollY > 80;
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

    // Parallax on hero background
    var hero = document.querySelector('.ag-hero');
    if (hero) {
        window.addEventListener('scroll', function(){
            var y = window.scrollY;
            if (y < window.innerHeight) {
                hero.style.backgroundPositionY = (50 + y * 0.15) + '%';
            }
        }, {passive:true});
    }

    // Animations
    if (!document.body.classList.contains('ag-has-animations')) return;

    // Apply animation classes based on element type
    var fadeSelectors = '.ag-section-title,.ag-section-lead,.ag-honoraires__card,.ag-testimonial-card,.ag-footer-col,.ag-page-article,.ag-post-card,.ag-page-hero__lead';
    var slideLeftSelectors = '.ag-maitre__photo,.ag-cabinet__info,.ag-domaine-examples';
    var slideRightSelectors = '.ag-maitre__body,.ag-cabinet__map,.ag-domaine-cta';
    var scaleSelectors = '.ag-domaine-card,.ag-cabinet__block,.ag-rdv__form';

    document.querySelectorAll(fadeSelectors).forEach(function(el, i) {
        el.classList.add('ag-fade-in');
        el.style.transitionDelay = Math.min(i * 0.08, 0.5) + 's';
    });
    document.querySelectorAll(slideLeftSelectors).forEach(function(el) {
        el.classList.add('ag-slide-left');
    });
    document.querySelectorAll(slideRightSelectors).forEach(function(el) {
        el.classList.add('ag-slide-right');
    });
    document.querySelectorAll(scaleSelectors).forEach(function(el, i) {
        el.classList.add('ag-scale-in');
        el.style.transitionDelay = Math.min(i * 0.1, 0.6) + 's';
    });

    // IntersectionObserver
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.ag-fade-in,.ag-slide-left,.ag-slide-right,.ag-scale-in').forEach(function(el){ el.classList.add('visible'); });
        return;
    }

    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.ag-fade-in,.ag-slide-left,.ag-slide-right,.ag-scale-in').forEach(function(el){ obs.observe(el); });
})();
