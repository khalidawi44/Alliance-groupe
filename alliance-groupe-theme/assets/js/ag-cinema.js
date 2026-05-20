/**
 * AG Cinema — couche cinematographique (rendu fluide type "weblove").
 *
 * Cle de la fluidite :
 *   - Lenis : smooth scroll a inertie (LE rendu Awwwards/weblove).
 *   - Tout pilote par un SEUL gsap.ticker (lenis.raf + curseur + parallaxe
 *     + particules) => pas de boucles rAF concurrentes = pas de saccades.
 *   - ScrollTrigger synchronise sur Lenis (scrub d'images beurre).
 *
 * Anti-conflit :
 *   - Les reveals de sections sont deja geres par main.js (.ag-anim) et
 *     cinema-fx (sections/texte) => ce module ne les refait PAS.
 *   - Lenis desactive sur les pages a scroll-jacking maison
 *     (.ag-fx-fixed-section, ex. accueil) pour ne pas casser le snap.
 *   - Curseur custom uniquement (cinema-fx l'a desactive).
 *
 * Garde-fous perf : prefers-reduced-motion off ; curseur/parallaxe/
 * particules desktop fine-pointer >=1025px ; particules >=4 coeurs.
 */
(function () {
	'use strict';
	if (typeof window === 'undefined') return;

	var REDUCED  = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var FINE     = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var IS_TOUCH = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
	var CORES    = navigator.hardwareConcurrency || 4;
	var DESKTOP_FX    = FINE && !IS_TOUCH && window.innerWidth >= 1025;
	var HAS_SCROLLJACK = false; // calcule au boot (DOM pret)

	var CDN = {
		gsap:  'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js',
		st:    'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js',
		lenis: 'https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js'
	};

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}
	function lerp(a, b, t) { return a + (b - a) * t; }
	function loadScript(src, cb) {
		var ex = document.querySelector('script[data-cine-src="' + src + '"]');
		if (ex) {
			if (ex.dataset.loaded) cb();
			else ex.addEventListener('load', cb);
			return;
		}
		var s = document.createElement('script');
		s.src = src; s.async = false; s.dataset.cineSrc = src;
		s.onload = function () { s.dataset.loaded = '1'; cb(); };
		s.onerror = function () { cb(); }; // on continue meme si une lib echoue (CDN/VPN)
		document.head.appendChild(s);
	}
	function loadSeq(list, cb) {
		var i = 0;
		(function next() { if (i >= list.length) { cb(); return; } loadScript(list[i++], next); })();
	}
	// boucle frame unifiee : gsap.ticker si dispo, sinon rAF
	function onTick(fn) {
		if (typeof gsap !== 'undefined' && gsap.ticker) gsap.ticker.add(fn);
		else (function raf() { fn(); requestAnimationFrame(raf); })();
	}

	// =====================================================================
	// SMOOTH SCROLL (Lenis) — le coeur du rendu fluide
	// =====================================================================
	function initSmoothScroll() {
		if (IS_TOUCH || HAS_SCROLLJACK) return;       // scroll natif sur tactile + pages scroll-jack
		if (typeof Lenis === 'undefined' || typeof gsap === 'undefined') return;

		var lenis = new Lenis({
			duration: 1.15,
			easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); },
			smoothWheel: true,
			wheelMultiplier: 1,
			touchMultiplier: 1.6
		});
		window.__agLenis = lenis;

		if (typeof ScrollTrigger !== 'undefined') {
			lenis.on('scroll', ScrollTrigger.update);
		}
		gsap.ticker.add(function (time) { lenis.raf(time * 1000); });
		gsap.ticker.lagSmoothing(0);

		// Ancres internes en scroll fluide
		document.addEventListener('click', function (e) {
			var a = e.target.closest('a[href^="#"]');
			if (!a) return;
			var id = a.getAttribute('href');
			if (!id || id.length < 2) return;
			var target = document.querySelector(id);
			if (!target) return;
			e.preventDefault();
			lenis.scrollTo(target, { offset: -84, duration: 1.4 });
		});
	}

	// =====================================================================
	// REVELATION / EXPANSION D'IMAGES (ScrollTrigger scrub) — unique
	// =====================================================================
	function initImageReveal() {
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
		var wraps = document.querySelectorAll('.ag-article__featured, .ag-rcard__img, .ag-blog-card__img');
		wraps.forEach(function (wrap) {
			if (wrap.closest('.ag-fx-fixed')) return; // pas dans le scroll-jack
			gsap.fromTo(wrap,
				{ clipPath: 'inset(8% 8% 8% 8% round 16px)' },
				{
					clipPath: 'inset(0% 0% 0% 0% round 16px)',
					ease: 'none',
					scrollTrigger: { trigger: wrap, start: 'top 92%', end: 'top 58%', scrub: 0.8 }
				}
			);
		});
		ScrollTrigger.refresh();
	}

	// =====================================================================
	// CURSEUR CUSTOM (desktop) — pilote par le ticker unifie
	// =====================================================================
	function initCursor() {
		var ring = document.createElement('div'); ring.className = 'ag-cine-cursor';
		var dot  = document.createElement('div'); dot.className  = 'ag-cine-cursor-dot';
		document.body.appendChild(ring); document.body.appendChild(dot);
		document.documentElement.classList.add('ag-cine-has-cursor');

		var mx = window.innerWidth / 2, my = window.innerHeight / 2, rx = mx, ry = my;
		window.addEventListener('mousemove', function (e) {
			mx = e.clientX; my = e.clientY;
			dot.style.transform = 'translate3d(' + mx + 'px,' + my + 'px,0)';
		}, { passive: true });

		onTick(function () {
			rx = lerp(rx, mx, 0.2); ry = lerp(ry, my, 0.2);
			ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
		});

		var hoverSel = 'a, button, [role="button"], input, textarea, select, .ag-scard, .ag-blog-card, .ag-rcard, .ag-tier-card, .ag-qpack__card';
		document.addEventListener('mouseover', function (e) { if (e.target.closest(hoverSel)) ring.classList.add('is-hover'); });
		document.addEventListener('mouseout',  function (e) { if (e.target.closest(hoverSel)) ring.classList.remove('is-hover'); });
		document.addEventListener('mousedown', function () { ring.classList.add('is-down'); });
		document.addEventListener('mouseup',   function () { ring.classList.remove('is-down'); });
	}

	// =====================================================================
	// PROFONDEUR HERO (parallaxe multi-plan) — pilote par le ticker unifie
	// =====================================================================
	function initHeroDepth() {
		var hero = document.querySelector('.ag-hero');
		if (!hero) return;
		var layers = [
			{ el: hero.querySelector('.ag-hero__orb--1'),  d: 26 },
			{ el: hero.querySelector('.ag-hero__orb--2'),  d: -20 },
			{ el: hero.querySelector('.ag-hero__circles'), d: 12 },
			{ el: hero.querySelector('.ag-hero__content'), d: -7 }
		].filter(function (l) { return l.el; });
		if (!layers.length) return;

		var tx = 0, ty = 0, cx = 0, cy = 0;
		hero.addEventListener('mousemove', function (e) {
			var r = hero.getBoundingClientRect();
			tx = ((e.clientX - r.left) / r.width - 0.5) * 2;
			ty = ((e.clientY - r.top) / r.height - 0.5) * 2;
		}, { passive: true });
		hero.addEventListener('mouseleave', function () { tx = 0; ty = 0; });

		onTick(function () {
			cx = lerp(cx, tx, 0.06); cy = lerp(cy, ty, 0.06);
			for (var i = 0; i < layers.length; i++) {
				layers[i].el.style.transform = 'translate3d(' + (cx * layers[i].d) + 'px,' + (cy * layers[i].d) + 'px,0)';
			}
		});
	}

	// =====================================================================
	// PARTICULES HERO (canvas 2D leger) — pilote par le ticker unifie
	// =====================================================================
	function initParticles() {
		var hero = document.querySelector('.ag-hero');
		if (!hero) return;
		var canvas = document.createElement('canvas');
		canvas.className = 'ag-cine-particles';
		hero.appendChild(canvas);
		var ctx = canvas.getContext('2d');
		var w, h, dpr = Math.min(window.devicePixelRatio || 1, 2);
		var pts = [], COUNT = 0, LINK2 = 9000;

		function resize() {
			w = hero.offsetWidth; h = hero.offsetHeight;
			canvas.width = w * dpr; canvas.height = h * dpr;
			canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			COUNT = Math.min(42, Math.round(w * h / 26000));
			pts = [];
			for (var i = 0; i < COUNT; i++) {
				pts.push({
					x: Math.random() * w, y: Math.random() * h,
					vx: (Math.random() - 0.5) * 0.22, vy: (Math.random() - 0.5) * 0.22,
					r: Math.random() * 1.5 + 0.4, a: Math.random() * 0.4 + 0.12
				});
			}
		}
		resize();
		window.addEventListener('resize', resize, { passive: true });

		var visible = true;
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (e) { visible = e[0].isIntersecting; }, { threshold: 0 }).observe(hero);
		}

		onTick(function () {
			if (!visible) return;
			ctx.clearRect(0, 0, w, h);
			var i, p;
			for (i = 0; i < pts.length; i++) {
				p = pts[i];
				p.x += p.vx; p.y += p.vy;
				if (p.x < 0 || p.x > w) p.vx *= -1;
				if (p.y < 0 || p.y > h) p.vy *= -1;
				ctx.beginPath();
				ctx.arc(p.x, p.y, p.r, 0, 6.283);
				ctx.fillStyle = 'rgba(212,180,92,' + p.a + ')';
				ctx.fill();
			}
			for (i = 0; i < pts.length; i++) {
				for (var j = i + 1; j < pts.length; j++) {
					var dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y, d2 = dx * dx + dy * dy;
					if (d2 < LINK2) {
						ctx.beginPath();
						ctx.moveTo(pts[i].x, pts[i].y); ctx.lineTo(pts[j].x, pts[j].y);
						ctx.strokeStyle = 'rgba(212,180,92,' + (0.09 * (1 - d2 / LINK2)) + ')';
						ctx.lineWidth = 0.5; ctx.stroke();
					}
				}
			}
		});
	}

	// =====================================================================
	// BOOT
	// =====================================================================
	function boot() {
		if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
			gsap.registerPlugin(ScrollTrigger);
		}
		initSmoothScroll();
		initImageReveal();
		if (DESKTOP_FX) {
			initCursor();
			initHeroDepth();
			if (CORES >= 4) initParticles();
		}
	}

	ready(function () {
		if (REDUCED) return;
		HAS_SCROLLJACK = !!document.querySelector('.ag-fx-fixed-section');
		// On charge gsap (si absent) -> ScrollTrigger -> Lenis, puis boot.
		var need = [];
		if (typeof gsap === 'undefined') need.push(CDN.gsap);
		need.push(CDN.st);
		if (!HAS_SCROLLJACK && !IS_TOUCH) need.push(CDN.lenis);
		loadSeq(need, boot);
	});
})();
