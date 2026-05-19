/**
 * Cinema FX — Pack d'animations innovantes pour Alliance Groupe.
 *
 *   A. Lenis smooth scroll
 *   C. Vanilla tilt 3D sur cards
 *   D. Cursor custom + magnetic buttons
 *   F. Text reveal split-words au scroll (vanilla, no GSAP SplitText paid)
 *   H. Particles connectées canvas 2D dans le hero
 *
 * (B = mesh gradient via template-part séparé, E = globe 3D Three.js
 *  lazy-load via template-part séparé)
 *
 * Compatible mobile (skip cursor + tilt sur touch devices).
 * Respect prefers-reduced-motion.
 */

(function () {
	'use strict';

	if (typeof window === 'undefined') return;
	var IS_TOUCH = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
	var REDUCED  = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// =====================================================================
	// UTILS
	// =====================================================================
	function loadScript(src, cb) {
		if (document.querySelector('script[data-src="' + src + '"]')) { cb && cb(); return; }
		var s = document.createElement('script');
		s.src = src; s.async = true; s.dataset.src = src;
		s.onload = function () { cb && cb(); };
		document.head.appendChild(s);
	}

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	function lerp(a, b, t) { return a + (b - a) * t; }

	// =====================================================================
	// A. LENIS SMOOTH SCROLL
	// =====================================================================
	function initLenis() {
		if (REDUCED) return;
		loadScript('https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js', function () {
			if (typeof Lenis === 'undefined') return;
			var lenis = new Lenis({
				duration: 1.2,
				easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); }, // ease-out expo
				smoothWheel: true,
				wheelMultiplier: 1,
				touchMultiplier: 2,
			});
			function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
			requestAnimationFrame(raf);

			// Bridge avec GSAP ScrollTrigger si présent (pour le scroll-fx existant)
			if (typeof ScrollTrigger !== 'undefined') {
				lenis.on('scroll', ScrollTrigger.update);
				if (typeof gsap !== 'undefined') {
					gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
					gsap.ticker.lagSmoothing(0);
				}
			}
			window.__agLenis = lenis;
		});
	}

	// =====================================================================
	// C. TILT 3D SUR CARDS
	// =====================================================================
	function initTilt() {
		if (IS_TOUCH || REDUCED) return;
		// Skip tilt sur petits écrans (perf + UX mobile)
		if (window.innerWidth < 1024) return;
		var selectors = '.ag-card, .ag-service-card, .ag-domaine-card, .ag-realisation-card, .ag-team-card, [data-tilt]';
		var els = document.querySelectorAll(selectors);
		if (!els.length) return;

		els.forEach(function (el) {
			el.style.transformStyle = 'preserve-3d';
			el.style.transition = 'transform .35s cubic-bezier(.16,1,.3,1)';
			var rect, cx, cy, maxTilt = 6, rafId = null, pendingX = 0, pendingY = 0;

			el.addEventListener('mouseenter', function () {
				rect = el.getBoundingClientRect();
				cx = rect.left + rect.width / 2;
				cy = rect.top + rect.height / 2;
				el.style.transition = 'transform .12s ease-out';
			}, { passive: true });

			el.addEventListener('mousemove', function (e) {
				if (!rect) return;
				pendingX = e.clientX; pendingY = e.clientY;
				if (rafId) return;
				rafId = requestAnimationFrame(function () {
					var dx = (pendingX - cx) / (rect.width / 2);
					var dy = (pendingY - cy) / (rect.height / 2);
					el.style.transform = 'perspective(900px) rotateX(' + (-dy * maxTilt).toFixed(2) + 'deg) rotateY(' + (dx * maxTilt).toFixed(2) + 'deg)';
					rafId = null;
				});
			}, { passive: true });

			el.addEventListener('mouseleave', function () {
				if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
				el.style.transition = 'transform .5s cubic-bezier(.16,1,.3,1)';
				el.style.transform = 'perspective(900px) rotateX(0) rotateY(0)';
			}, { passive: true });
		});
	}

	// =====================================================================
	// D. CURSOR CUSTOM + MAGNETIC BUTTONS
	// =====================================================================
	function initCursor() {
		// Désactivé sur demande user : cursor custom = perf + UX gênante.
		// Retour au cursor natif du système.
		return;
		// eslint-disable-next-line no-unreachable
		if (IS_TOUCH || REDUCED) return;

		// Création des éléments cursor
		var ring = document.createElement('div');
		ring.className = 'ag-cursor';
		var dot = document.createElement('div');
		dot.className = 'ag-cursor-dot';
		document.body.appendChild(ring);
		document.body.appendChild(dot);
		document.documentElement.classList.add('ag-has-custom-cursor');

		var mx = window.innerWidth / 2, my = window.innerHeight / 2;
		var rx = mx, ry = my, dx = mx, dy = my;
		document.addEventListener('mousemove', function (e) {
			mx = e.clientX; my = e.clientY;
			dx = mx; dy = my;
		}, { passive: true });

		function tick() {
			rx = lerp(rx, mx, 0.15);
			ry = lerp(ry, my, 0.15);
			ring.style.transform = 'translate3d(' + (rx - 20) + 'px,' + (ry - 20) + 'px,0)';
			dot.style.transform = 'translate3d(' + (dx - 3) + 'px,' + (dy - 3) + 'px,0)';
			requestAnimationFrame(tick);
		}
		tick();

		// Hover state sur éléments interactifs
		var hoverables = 'a, button, input[type=submit], .ag-btn-gold, .ag-btn-outline, .ag-card, .ag-service-card, [data-cursor=hover]';
		document.querySelectorAll(hoverables).forEach(function (el) {
			el.addEventListener('mouseenter', function () { ring.classList.add('is-hover'); });
			el.addEventListener('mouseleave', function () { ring.classList.remove('is-hover'); });
		});

		// MAGNETIC BUTTONS
		var magnetics = document.querySelectorAll('.ag-btn-gold, .ag-btn-outline, [data-magnetic]');
		magnetics.forEach(function (btn) {
			var strength = parseFloat(btn.dataset.magnetic) || 0.3;
			btn.style.transition = 'transform .3s cubic-bezier(.16,1,.3,1)';
			var r;
			btn.addEventListener('mouseenter', function () { r = btn.getBoundingClientRect(); });
			btn.addEventListener('mousemove', function (e) {
				if (!r) return;
				var bx = e.clientX - (r.left + r.width / 2);
				var by = e.clientY - (r.top + r.height / 2);
				btn.style.transform = 'translate3d(' + (bx * strength) + 'px,' + (by * strength) + 'px,0)';
			});
			btn.addEventListener('mouseleave', function () {
				btn.style.transform = 'translate3d(0,0,0)';
			});
		});
	}

	// =====================================================================
	// F. TEXT REVEAL SPLIT-WORDS AU SCROLL
	// =====================================================================
	function initTextReveal() {
		if (REDUCED) return;
		// Cible : tous les H1/H2 dans des sections + éléments avec data-reveal
		var els = document.querySelectorAll('h1.ag-hero__title, .ag-section h2, [data-reveal]');
		if (!els.length || !('IntersectionObserver' in window)) return;

		els.forEach(function (el) {
			if (el.dataset.revealed === '1') return;
			el.dataset.revealed = '1';

			// Split: garde les <span class="ag-line"> existants
			var html = el.innerHTML;
			// Si déjà splitté par le thème (ag-line), on split juste les mots dans chaque ag-line
			var lines = el.querySelectorAll('.ag-line');
			if (lines.length) {
				lines.forEach(function (line) { splitWordsInside(line); });
			} else {
				splitWordsInside(el);
			}
		});

		function splitWordsInside(node) {
			var text = node.textContent;
			var words = text.split(/(\s+)/);
			node.innerHTML = '';
			words.forEach(function (w) {
				if (/^\s+$/.test(w)) { node.appendChild(document.createTextNode(w)); return; }
				var wrap = document.createElement('span');
				wrap.className = 'ag-rv-mask';
				var inner = document.createElement('span');
				inner.className = 'ag-rv-word';
				inner.textContent = w;
				wrap.appendChild(inner);
				node.appendChild(wrap);
			});
		}

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					var words = entry.target.querySelectorAll('.ag-rv-word');
					words.forEach(function (w, i) {
						w.style.transitionDelay = (i * 0.06) + 's';
						w.classList.add('is-in');
					});
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.2, rootMargin: '0px 0px -10% 0px' });

		els.forEach(function (el) { io.observe(el); });
	}

	// =====================================================================
	// H. PARTICLES CONNECTÉES (canvas 2D, léger)
	// =====================================================================
	function initParticles() {
		if (REDUCED) return;
		// Skip mobile pour performance
		if (window.innerWidth < 768) return;
		var hero = document.querySelector('.ag-hero');
		if (!hero) return;
		if (hero.querySelector('canvas[data-ag-particles]')) return;

		var canvas = document.createElement('canvas');
		canvas.dataset.agParticles = '1';
		canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:1;mix-blend-mode:screen;opacity:.45';
		hero.insertBefore(canvas, hero.firstChild);
		var ctx = canvas.getContext('2d');

		var DPR = Math.min(window.devicePixelRatio || 1, 1.5);
		var W, H, particles = [];
		var COUNT = 35; // réduit de 70 à 35 (perf)
		var MAX_DIST = 120;

		function resize() {
			W = canvas.clientWidth;
			H = canvas.clientHeight;
			canvas.width = W * DPR;
			canvas.height = H * DPR;
			ctx.scale(DPR, DPR);
		}
		resize();
		new ResizeObserver(resize).observe(hero);

		// Init
		for (var i = 0; i < COUNT; i++) {
			particles.push({
				x: Math.random() * W,
				y: Math.random() * H,
				vx: (Math.random() - 0.5) * 0.3,
				vy: (Math.random() - 0.5) * 0.3,
				r: 1 + Math.random() * 2,
			});
		}

		var mouse = { x: -9999, y: -9999 };
		hero.addEventListener('mousemove', function (e) {
			var r = hero.getBoundingClientRect();
			mouse.x = e.clientX - r.left;
			mouse.y = e.clientY - r.top;
		});
		hero.addEventListener('mouseleave', function () { mouse.x = -9999; mouse.y = -9999; });

		function frame() {
			ctx.clearRect(0, 0, W, H);
			for (var i = 0; i < particles.length; i++) {
				var p = particles[i];
				p.x += p.vx;
				p.y += p.vy;
				if (p.x < 0 || p.x > W) p.vx *= -1;
				if (p.y < 0 || p.y > H) p.vy *= -1;

				// Attraction subtile vers la souris
				var dx = mouse.x - p.x, dy = mouse.y - p.y;
				var dist = Math.sqrt(dx * dx + dy * dy);
				if (dist < 150) {
					p.x += dx * 0.001;
					p.y += dy * 0.001;
				}

				ctx.beginPath();
				ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
				ctx.fillStyle = 'rgba(212,180,92,0.7)';
				ctx.fill();
			}
			// Connexions
			for (var i = 0; i < particles.length; i++) {
				for (var j = i + 1; j < particles.length; j++) {
					var a = particles[i], b = particles[j];
					var dx = a.x - b.x, dy = a.y - b.y;
					var d = Math.sqrt(dx * dx + dy * dy);
					if (d < MAX_DIST) {
						var alpha = (1 - d / MAX_DIST) * 0.25;
						ctx.beginPath();
						ctx.moveTo(a.x, a.y);
						ctx.lineTo(b.x, b.y);
						ctx.strokeStyle = 'rgba(212,180,92,' + alpha + ')';
						ctx.lineWidth = 0.6;
						ctx.stroke();
					}
				}
			}
			requestAnimationFrame(frame);
		}
		frame();
	}

	// =====================================================================
	// I. STATS COUNT-UP au scroll
	//    Cible : .ag-metric__value, .ag-gain-card__value, [data-count]
	//    Lit le textContent, extrait le nombre + suffixe (% / + / / ...),
	//    anime de 0 → cible en 1.5s avec easing.
	// =====================================================================
	function initCountUp() {
		if (REDUCED) return;
		var selectors = '.ag-metric__value, .ag-client__stat-value, .ag-gain-card__value, [data-count]';
		var els = document.querySelectorAll(selectors);
		if (!els.length || !('IntersectionObserver' in window)) return;

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (!e.isIntersecting) return;
				var el = e.target;
				var raw = el.dataset.count || el.textContent;
				// Extrait : optionnel - puis nombre puis suffixe
				var m = raw.match(/^(-?\d+(?:[.,]\d+)?)(.*)$/);
				if (!m) { io.unobserve(el); return; }
				var target = parseFloat(m[1].replace(',', '.'));
				var suffix = m[2] || '';
				var isInt = m[1].indexOf('.') === -1 && m[1].indexOf(',') === -1;
				var prefix = '';
				if (raw.charAt(0) === '+' && target >= 0) prefix = '+'; // ex: "+340%"
				var start = performance.now();
				var duration = 1500;
				function tick(now) {
					var t = Math.min(1, (now - start) / duration);
					// easing ease-out expo
					var eased = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
					var current = target * eased;
					var disp = isInt ? Math.floor(current).toString() : current.toFixed(1);
					el.textContent = prefix + disp + suffix;
					if (t < 1) requestAnimationFrame(tick);
				}
				requestAnimationFrame(tick);
				io.unobserve(el);
			});
		}, { threshold: 0.3 });

		els.forEach(function (el) {
			// Préserve la valeur originale dans data-count si pas déjà
			if (!el.dataset.count) el.dataset.count = el.textContent.trim();
			io.observe(el);
		});
	}

	// =====================================================================
	// J. PROCESS STEPS reveal au scroll (ajoute is-revealed)
	// =====================================================================
	function initProcessReveal() {
		var steps = document.querySelectorAll('.ag-pstep');
		if (!steps.length || !('IntersectionObserver' in window)) return;
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('is-revealed');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.4 });
		steps.forEach(function (s) { io.observe(s); });
	}

	// =====================================================================
	// K. SECTIONS reveal au scroll (ajoute ag-revealed)
	// =====================================================================
	function initSectionReveal() {
		if (REDUCED) return;
		var sections = document.querySelectorAll('.ag-section, .ag-services, .ag-process, .ag-realisations, .ag-mk-team, .ag-about, .ag-faq, .ag-cta');
		if (!sections.length || !('IntersectionObserver' in window)) return;
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('ag-revealed');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.1, rootMargin: '0px 0px -5% 0px' });
		sections.forEach(function (s) { io.observe(s); });
	}

	// =====================================================================
	// L. FAQ : convertit les <div.ag-faq-item> en <details> si pas déjà
	//    (rendre la rotation icon native, sans JS toggle)
	// =====================================================================
	function initFaq() {
		// Click toggle pour les ag-faq-item qui ne sont pas des <details>
		var items = document.querySelectorAll('.ag-faq-item:not(details)');
		items.forEach(function (item) {
			var q = item.querySelector('.ag-faq-q');
			if (!q) return;
			q.addEventListener('click', function () {
				item.toggleAttribute('open');
			});
		});
	}

	// =====================================================================
	// M. BACK-TO-TOP progress ring (met à jour --scroll en %)
	// =====================================================================
	function initBackToTopProgress() {
		var btn = document.getElementById('ag-totop');
		if (!btn) return;
		var ticking = false;
		function update() {
			var h = document.documentElement;
			var max = (h.scrollHeight - h.clientHeight) || 1;
			var p = Math.min(100, Math.max(0, (window.scrollY / max) * 100));
			btn.style.setProperty('--scroll', p + '%');
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) { requestAnimationFrame(update); ticking = true; }
		}, { passive: true });
		update();
		// Smooth scroll au clic (en plus du natif)
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			if (window.__agLenis && window.__agLenis.scrollTo) {
				window.__agLenis.scrollTo(0, { duration: 1.5 });
			} else {
				window.scrollTo({ top: 0, behavior: 'smooth' });
			}
		});
	}

	// =====================================================================
	// BOOT
	// =====================================================================
	ready(function () {
		initLenis();
		initTilt();
		initCursor();
		initTextReveal();
		initParticles();
		initCountUp();
		initProcessReveal();
		initSectionReveal();
		initFaq();
		initBackToTopProgress();
	});
})();
