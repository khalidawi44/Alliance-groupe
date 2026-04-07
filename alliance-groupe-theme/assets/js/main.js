/**
 * Alliance Groupe Theme — main.js
 * Sticky nav, burger, FAQ, back-to-top, scroll animations (inline style.cssText)
 */

(function () {
    'use strict';

    /* ── Sticky Nav ───────────────────────────────────────────── */
    const nav = document.getElementById('ag-nav');
    window.addEventListener('scroll', function () {
        if (!nav) return;
        nav.classList.toggle('scrolled', window.scrollY > 60);
    });

    /* ── Burger Menu ──────────────────────────────────────────── */
    const burger = document.getElementById('ag-burger');
    const navList = document.getElementById('ag-nav-list');
    if (burger && navList) {
        burger.addEventListener('click', function () {
            burger.classList.toggle('open');
            navList.classList.toggle('open');
        });
        navList.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                burger.classList.remove('open');
                navList.classList.remove('open');
            });
        });
    }

    /* ── FAQ Accordion ────────────────────────────────────────── */
    document.querySelectorAll('.ag-faq-q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.ag-faq-item');
            var isOpen = item.classList.contains('open');

            // Close all
            document.querySelectorAll('.ag-faq-item').forEach(function (el) {
                el.classList.remove('open');
            });

            // Open clicked if it was closed
            if (!isOpen) {
                item.classList.add('open');
            }
        });
    });

    /* ── Back to Top ──────────────────────────────────────────── */
    var totop = document.getElementById('ag-totop');
    if (totop) {
        window.addEventListener('scroll', function () {
            totop.classList.toggle('visible', window.scrollY > 100);
        });
        totop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── Scroll Animations (inline style.cssText) ─────────────── */
    /*
     * We CANNOT rely on CSS classes for animations because Elementor's
     * reset.css overrides them. All animations are applied via
     * element.style.cssText using IntersectionObserver.
     */

    function animateOnScroll() {
        var animElements = document.querySelectorAll('.ag-anim');
        if (!animElements.length) return;

        // Set initial hidden state via inline style
        animElements.forEach(function (el) {
            var type = el.getAttribute('data-anim');
            switch (type) {
                case 'card':
                case 'step':
                case 'real':
                case 'faq-item':
                case 'desc':
                    el.style.cssText = 'opacity:0;transform:translateY(40px);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                    break;
                case 'valeur':
                    el.style.cssText = 'opacity:0;transform:translateX(60px);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                    break;
                case 'gain':
                    el.style.cssText = 'opacity:0;transform:scale(.88);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                    break;
                case 'tag':
                    el.style.cssText = 'opacity:0;transform:translateX(-30px);transition:opacity .6s cubic-bezier(.23,1,.32,1),transform .6s cubic-bezier(.23,1,.32,1);';
                    break;
                case 'title':
                    el.style.cssText = 'opacity:1;position:relative;display:inline-block;';
                    break;
                case 'parallax-text':
                    el.style.cssText = 'opacity:0;transform:scale(.92);transition:opacity .8s ease,transform .8s ease;';
                    break;
                default:
                    el.style.cssText = 'opacity:0;transform:translateY(30px);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
            }
        });

        // Stagger map: how many ms between siblings of each type
        var staggerMap = {
            'card': 120,
            'step': 130,
            'real': 150,
            'valeur': 100,
            'faq-item': 60,
            'gain': 130,
            'tag': 0,
            'desc': 0,
            'title': 0,
            'parallax-text': 0
        };

        // Track stagger groups by parent + type
        var staggerGroups = {};

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                var el = entry.target;
                var type = el.getAttribute('data-anim');
                var stagger = staggerMap[type] || 0;

                // Calculate delay for staggered items
                var delay = 0;
                if (stagger > 0 && el.parentElement) {
                    var groupKey = type + '-' + (el.parentElement.className || 'root');
                    if (!staggerGroups[groupKey]) {
                        staggerGroups[groupKey] = 0;
                    }
                    delay = staggerGroups[groupKey] * stagger;
                    staggerGroups[groupKey]++;
                }

                setTimeout(function () {
                    switch (type) {
                        case 'card':
                        case 'step':
                        case 'real':
                        case 'faq-item':
                        case 'desc':
                            el.style.cssText = 'opacity:1;transform:translateY(0);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                            break;
                        case 'valeur':
                            el.style.cssText = 'opacity:1;transform:translateX(0);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                            break;
                        case 'gain':
                            el.style.cssText = 'opacity:1;transform:scale(1);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                            break;
                        case 'tag':
                            el.style.cssText = 'opacity:1;transform:translateX(0);transition:opacity .6s cubic-bezier(.23,1,.32,1),transform .6s cubic-bezier(.23,1,.32,1);';
                            break;
                        case 'title':
                            // Flying gold underline
                            addFlyingUnderline(el);
                            break;
                        case 'parallax-text':
                            el.style.cssText = 'opacity:1;transform:scale(1);transition:opacity .8s ease,transform .8s ease;';
                            break;
                        default:
                            el.style.cssText = 'opacity:1;transform:translateY(0);transition:opacity .7s cubic-bezier(.23,1,.32,1),transform .7s cubic-bezier(.23,1,.32,1);';
                    }
                }, delay);

                observer.unobserve(el);
            });
        }, { threshold: 0.15 });

        animElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ── Flying Gold Underline ────────────────────────────────── */
    function addFlyingUnderline(titleEl) {
        // Check if already added
        if (titleEl.querySelector('.ag-underline')) return;

        var underline = document.createElement('span');
        underline.className = 'ag-underline';
        underline.style.cssText =
            'position:absolute;' +
            'bottom:-8px;' +
            'left:0;' +
            'width:0;' +
            'height:3px;' +
            'background:#D4B45C;' +
            'border-radius:2px;' +
            'transition:width .8s cubic-bezier(.23,1,.32,1);';

        titleEl.style.position = 'relative';
        titleEl.style.display = 'inline-block';
        titleEl.appendChild(underline);

        // Trigger animation after append
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                underline.style.cssText =
                    'position:absolute;' +
                    'bottom:-8px;' +
                    'left:0;' +
                    'width:55%;' +
                    'height:3px;' +
                    'background:#D4B45C;' +
                    'border-radius:2px;' +
                    'transition:width .8s cubic-bezier(.23,1,.32,1);';
            });
        });
    }

    /* ── Init on DOM ready ────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', animateOnScroll);
    } else {
        animateOnScroll();
    }
})();
