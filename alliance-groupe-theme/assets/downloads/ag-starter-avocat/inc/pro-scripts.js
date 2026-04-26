(function(){
    'use strict';
    var ease = 'cubic-bezier(.23,1,.32,1)';
    var header = document.querySelector('.ag-site-header');

    // Header: transparent → solid
    if (header) {
        window.addEventListener('scroll', function(){
            header.classList.toggle('scrolled', window.scrollY > 60);
        }, {passive:true});
    }

    // Smooth scroll
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
        window.scrollTo({ top: target.getBoundingClientRect().top + window.pageYOffset - offset, behavior: 'smooth' });
        if (history.pushState) history.pushState(null, null, hash);
    });

    // Mobile menu
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

    // Theme toggle — visitor can switch light/dark
    var themeBtn = document.querySelector('.ag-theme-toggle');
    if (themeBtn) {
        var icon = themeBtn.querySelector('.ag-theme-toggle__icon');
        var saved = localStorage.getItem('ag_mode');
        if (saved === 'dark') document.body.classList.remove('ag-light');
        else if (saved === 'light') document.body.classList.add('ag-light');

        function applyMode() {
            var light = document.body.classList.contains('ag-light');
            if (icon) icon.textContent = light ? '☀️' : '🌙';
            // Force backgrounds directly on elements
            var cream1 = '#F5F0EB', cream2 = '#EDE6DF';
            var dark1 = '#080808', dark2 = '#12121a';
            document.querySelectorAll('.ag-section').forEach(function(s, i) {
                if (light) {
                    s.style.setProperty('background', (i % 2 === 0 ? cream1 : cream2), 'important');
                    s.style.setProperty('background-image', 'none', 'important');
                } else {
                    s.style.removeProperty('background');
                    s.style.removeProperty('background-image');
                }
            });
            // Force hero/page-hero
            document.querySelectorAll('.ag-hero,.ag-page-hero').forEach(function(el) {
                if (light) {
                    el.style.setProperty('background', 'linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat', 'important');
                } else {
                    el.style.removeProperty('background');
                }
            });
        }
        applyMode();

        themeBtn.addEventListener('click', function() {
            document.body.classList.toggle('ag-light');
            localStorage.setItem('ag_mode', document.body.classList.contains('ag-light') ? 'light' : 'dark');
            applyMode();
        });
    }

    // Back to top
    var totop = document.createElement('a');
    totop.href = '#';
    totop.className = 'ag-totop';
    totop.innerHTML = '↑';
    totop.setAttribute('aria-label', 'Haut de page');
    document.body.appendChild(totop);
    window.addEventListener('scroll', function(){
        totop.classList.toggle('visible', window.scrollY > 400);
    }, {passive:true});
    totop.addEventListener('click', function(e){
        e.preventDefault();
        window.scrollTo({top:0, behavior:'smooth'});
    });

    // Animations
    if (!document.body.classList.contains('ag-has-animations')) return;

    var stagger = {card:120, domaine:120, honoraire:130, step:130, col:100};

    function setInitial(el, type) {
        var t = 'transition:opacity .7s '+ease+',transform .7s '+ease+';';
        switch(type) {
            case 'slide-right':
                el.style.cssText += 'opacity:0;transform:translateX(60px);'+t; break;
            case 'slide-left':
                el.style.cssText += 'opacity:0;transform:translateX(-60px);'+t; break;
            case 'scale':
                el.style.cssText += 'opacity:0;transform:scale(.88);'+t; break;
            case 'title':
                el.style.cssText += 'opacity:0;transform:translateY(30px);'+t;
                // Flying gold underline
                el.style.position = 'relative';
                var line = document.createElement('span');
                line.className = 'ag-underline';
                var accentColor = (typeof agSkin !== 'undefined') ? agSkin.accent : '#D4B45C';
                line.style.cssText = 'position:absolute;bottom:-8px;left:0;width:0;height:3px;background:'+accentColor+';border-radius:2px;transition:width .8s '+ease+';';
                el.appendChild(line);
                el._underline = line;
                break;
            default:
                el.style.cssText += 'opacity:0;transform:translateY(40px);'+t;
        }
        el._animType = type;
    }

    function reveal(el) {
        el.style.opacity = '1';
        el.style.transform = 'translateY(0) translateX(0) scale(1)';
        if (el._underline) el._underline.style.width = '55%';
    }

    // Map elements to animation types
    var mappings = [
        {sel: '.ag-section-title', type: 'title'},
        {sel: '.ag-section-lead', type: 'default'},
        {sel: '.ag-domaine-card', type: 'default', stag: 120},
        {sel: '.ag-honoraires__card', type: 'default', stag: 130},
        {sel: '.ag-maitre__photo', type: 'slide-left'},
        {sel: '.ag-maitre__body', type: 'slide-right'},
        {sel: '.ag-cabinet__block', type: 'scale', stag: 130},
        {sel: '.ag-cabinet-full__map', type: 'slide-left'},
        {sel: '.ag-cabinet-full__cards', type: 'slide-right'},
        {sel: '.ag-testimonial-card', type: 'default', stag: 150},
        {sel: '.ag-footer-col', type: 'default', stag: 100},
        {sel: '.ag-post-card', type: 'default', stag: 120},
        {sel: '.ag-page-article', type: 'default'},
        {sel: '.ag-page-hero__lead', type: 'default'},
        {sel: '.ag-rdv__form', type: 'scale'},
        {sel: '.ag-domaine-examples', type: 'slide-left'},
        {sel: '.ag-domaine-cta', type: 'default'},
    ];

    var allAnimated = [];
    mappings.forEach(function(m) {
        var els = document.querySelectorAll(m.sel);
        els.forEach(function(el, i) {
            setInitial(el, m.type);
            if (m.stag) el.style.transitionDelay = (i * m.stag) + 'ms';
            allAnimated.push(el);
        });
    });

    if (!('IntersectionObserver' in window)) {
        allAnimated.forEach(reveal);
        return;
    }

    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if (e.isIntersecting) { reveal(e.target); obs.unobserve(e.target); }
        });
    }, { threshold: 0.15 });

    allAnimated.forEach(function(el){ obs.observe(el); });
})();
