/**
 * AG Cinema — couche cinematographique experimentale.
 *
 *   1. Curseur custom (desktop fine-pointer only) : follower + ring,
 *      grossit au survol des liens/boutons.
 *   2. Profondeur hero : les couches (orbs, cercles, contenu) reagissent
 *      a la souris (parallaxe multi-plan).
 *   3. Champ de particules canvas 2D dans le hero (desktop, >=4 coeurs).
 *   4. Revelations au scroll + expansion d'images via GSAP/ScrollTrigger
 *      (fade/slide/clip-path, easing cinematographique).
 *
 * Garde-fous perf : prefers-reduced-motion off, curseur/particules/parallaxe
 * desactives sur tactile et machines faibles. Module isole — supprimable
 * sans toucher au reste du site.
 */
(function () {
	'use strict';
	if (typeof window === 'undefined') return;

	var REDUCED  = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var FINE     = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var IS_TOUCH = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
	var CORES    = navigator.hardwareConcurrency || 4;
	var DESKTOP_FX = FINE && !IS_TOUCH && window.innerWidth >= 1025;

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}
	function loadScript(src, cb) {
		if (document.querySelector('script[data-cine-src="' + src + '"]')) {
			var ex = document.querySelector('script[data-cine-src="' + src + '"]');
			if (ex.dataset.loaded) { cb && cb(); } else { ex.addEventListener('load', function () { cb && cb(); }); }
			return;
		}
		var s = document.createElement('script');
		s.src = src; s.async = true; s.dataset.cineSrc = src;
		s.onload = function () { s.dataset.loaded = '1'; cb && cb(); };
		document.head.appendChild(s);
	}
	function lerp(a, b, t) { return a + (b - a) * t; }

	// =====================================================================
	// 1. CURSEUR CUSTOM
	// =====================================================================
	function initCursor() {
		var ring = document.createElement('div');
		ring.className = 'ag-cine-cursor';
		var dot = document.createElement('div');
		dot.className = 'ag-cine-cursor-dot';
		document.body.appendChild(ring);
		document.body.appendChild(dot);
		document.documentElement.classList.add('ag-cine-has-cursor');

		var mx = window.innerWidth / 2, my = window.innerHeight / 2;
		var rx = mx, ry = my;

		window.addEventListener('mousemove', function (e) {
			mx = e.clientX; my = e.clientY;
			dot.style.transform = 'translate3d(' + mx + 'px,' + my + 'px,0)';
		}, { passive: true });

		(function raf() {
			rx = lerp(rx, mx, 0.18); ry = lerp(ry, my, 0.18);
			ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
			requestAnimationFrame(raf);
		})();

		var hoverSel = 'a, button, [role="button"], input, textarea, select, .ag-scard, .ag-blog-card, .ag-rcard, .ag-tier-card, .ag-qpack__card';
		document.addEventListener('mouseover', function (e) {
			if (e.target.closest(hoverSel)) ring.classList.add('is-hover');
		});
		document.addEventListener('mouseout', function (e) {
			if (e.target.closest(hoverSel)) ring.classList.remove('is-hover');
		});
		document.addEventListener('mousedown', function () { ring.classList.add('is-down'); });
		document.addEventListener('mouseup',   function () { ring.classList.remove('is-down'); });
	}

	// =====================================================================
	// 2. PROFONDEUR HERO (parallaxe multi-plan a la souris)
	// =====================================================================
	function initHeroDepth() {
		var hero = document.querySelector('.ag-hero');
		if (!hero) return;
		var layers = [
			{ el: hero.querySelector('.ag-hero__orb--1'),   depth: 28 },
			{ el: hero.querySelector('.ag-hero__orb--2'),   depth: -22 },
			{ el: hero.querySelector('.ag-hero__circles'),  depth: 14 },
			{ el: hero.querySelector('.ag-hero__content'),  depth: -8 }
		].filter(function (l) { return l.el; });
		if (!layers.length) return;

		var tx = 0, ty = 0, cx = 0, cy = 0;
		hero.addEventListener('mousemove', function (e) {
			var r = hero.getBoundingClientRect();
			tx = ((e.clientX - r.left) / r.width - 0.5) * 2;
			ty = ((e.clientY - r.top) / r.height - 0.5) * 2;
		}, { passive: true });
		hero.addEventListener('mouseleave', function () { tx = 0; ty = 0; });

		(function raf() {
			cx = lerp(cx, tx, 0.06); cy = lerp(cy, ty, 0.06);
			layers.forEach(function (l) {
				l.el.style.transform = 'translate3d(' + (cx * l.depth) + 'px,' + (cy * l.depth) + 'px,0)';
			});
			requestAnimationFrame(raf);
		})();
	}

	// =====================================================================
	// 3. CHAMP DE PARTICULES (canvas 2D, hero uniquement)
	// =====================================================================
	function initParticles() {
		var hero = document.querySelector('.ag-hero');
		if (!hero) return;
		var canvas = document.createElement('canvas');
		canvas.className = 'ag-cine-particles';
		hero.appendChild(canvas);
		var ctx = canvas.getContext('2d');
		var w, h, dpr = Math.min(window.devicePixelRatio || 1, 2);
		var particles = [];
		var COUNT = 0;

		function resize() {
			w = hero.offsetWidth; h = hero.offsetHeight;
			canvas.width = w * dpr; canvas.height = h * dpr;
			canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			COUNT = Math.min(70, Math.round(w * h / 22000));
			particles = [];
			for (var i = 0; i < COUNT; i++) {
				particles.push({
					x: Math.random() * w, y: Math.random() * h,
					vx: (Math.random() - 0.5) * 0.25, vy: (Math.random() - 0.5) * 0.25,
					r: Math.random() * 1.6 + 0.4, a: Math.random() * 0.4 + 0.1
				});
			}
		}
		resize();
		window.addEventListener('resize', resize, { passive: true });

		var running = true;
		var io = new IntersectionObserver(function (e) { running = e[0].isIntersecting; if (running) raf(); }, { threshold: 0 });
		io.observe(hero);

		function raf() {
			if (!running) return;
			ctx.clearRect(0, 0, w, h);
			for (var i = 0; i < particles.length; i++) {
				var p = particles[i];
				p.x += p.vx; p.y += p.vy;
				if (p.x < 0 || p.x > w) p.vx *= -1;
				if (p.y < 0 || p.y > h) p.vy *= -1;
				ctx.beginPath();
				ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
				ctx.fillStyle = 'rgba(212,180,92,' + p.a + ')';
				ctx.fill();
			}
			// liens entre particules proches
			for (var a = 0; a < particles.length; a++) {
				for (var b = a + 1; b < particles.length; b++) {
					var dx = particles[a].x - particles[b].x, dy = particles[a].y - particles[b].y;
					var d2 = dx * dx + dy * dy;
					if (d2 < 12000) {
						ctx.beginPath();
						ctx.moveTo(particles[a].x, particles[a].y);
						ctx.lineTo(particles[b].x, particles[b].y);
						ctx.strokeStyle = 'rgba(212,180,92,' + (0.10 * (1 - d2 / 12000)) + ')';
						ctx.lineWidth = 0.5;
						ctx.stroke();
					}
				}
			}
			requestAnimationFrame(raf);
		}
		raf();
	}

	// =====================================================================
	// 4. REVELATIONS AU SCROLL + EXPANSION D'IMAGES (GSAP + ScrollTrigger)
	// =====================================================================
	function initReveals() {
		function run() {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
			gsap.registerPlugin(ScrollTrigger);

			// 4a. Revelation generique : titres, paragraphes, cartes, images
			var revealSel = [
				'.ag-section__title', '.ag-section__desc',
				'.ag-scard', '.ag-blog-card', '.ag-rcard', '.ag-gain-card',
				'.ag-tier-card', '.ag-qpack__card', '.ag-pstep',
				'.ag-racines__step', '.ag-racines__for-item',
				'.ag-article-links__box', '.ag-heritage-card'
			].join(',');

			var els = Array.prototype.slice.call(document.querySelectorAll(revealSel))
				.filter(function (el) { return !el.closest('.ag-fx-fixed'); }); // pas dans le scroll-jacking

			ScrollTrigger.batch(els, {
				start: 'top 88%',
				once: true,
				onEnter: function (batch) {
					gsap.to(batch, {
						opacity: 1, y: 0, duration: 0.9, stagger: 0.08,
						ease: 'power3.out', overwrite: true
					});
				}
			});
			gsap.set(els, { opacity: 0, y: 36 });

			// 4b. Expansion d'images au scroll (clip-path + scale)
			var imgWraps = document.querySelectorAll('.ag-article__featured, .ag-rcard__img, .ag-blog-card__img');
			imgWraps.forEach(function (wrap) {
				if (wrap.closest('.ag-fx-fixed')) return;
				gsap.fromTo(wrap, {
					clipPath: 'inset(12% 12% 12% 12% round 16px)'
				}, {
					clipPath: 'inset(0% 0% 0% 0% round 16px)',
					ease: 'none',
					scrollTrigger: { trigger: wrap, start: 'top 92%', end: 'top 55%', scrub: 0.6 }
				});
			});

			ScrollTrigger.refresh();
		}

		// GSAP peut deja etre charge par scroll-fx/process ; sinon on charge.
		if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') { run(); return; }
		loadScript('https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js', function () {
			loadScript('https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js', run);
		});
	}

	// =====================================================================
	// BOOT
	// =====================================================================
	ready(function () {
		if (REDUCED) return; // aucun mouvement si l'utilisateur le demande
		initReveals();
		if (DESKTOP_FX) {
			initCursor();
			initHeroDepth();
			if (CORES >= 4) initParticles();
		}
	});
})();
