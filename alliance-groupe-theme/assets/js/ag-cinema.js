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

	// Garde-fou LOW-END : on coupe toute la couche cine sur machines faibles
	// (peu de coeurs / peu de RAM) ou economie de data. Le site reste 100%
	// fonctionnel, juste sans le polish couteux (Lenis, curseur, parallaxe...).
	var AG_LOWEND = ( navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4 )
		|| ( navigator.deviceMemory && navigator.deviceMemory <= 4 )
		|| ( navigator.connection && navigator.connection.saveData );
	if ( AG_LOWEND ) return;

	var REDUCED  = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var FINE     = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var IS_TOUCH = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
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
		// detecte aussi un <script src> deja pose par un autre module (ex. scroll-fx
		// charge GSAP depuis la meme url) -> evite le double-chargement.
		var ex = document.querySelector('script[data-cine-src="' + src + '"], script[src="' + src + '"]');
		if (ex) {
			if (ex.dataset.loaded || ex.getAttribute('data-loaded')) cb();
			else ex.addEventListener('load', function () { cb(); });
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
		// On NE bouge QUE les orbes/cercles decoratifs (legers). Le gros titre
		// (.ag-hero__content) n'est PAS anime : le transformer a chaque frame
		// + son degrade = recompositions couteuses = lag.
		var layers = [
			{ el: hero.querySelector('.ag-hero__orb--1'),  d: 26 },
			{ el: hero.querySelector('.ag-hero__orb--2'),  d: -20 },
			{ el: hero.querySelector('.ag-hero__circles'), d: 12 }
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
	// CINESCENE — epinglage MANUEL (pas de pin GSAP) : le "stage" passe en
	// position:fixed pendant la traversee de la scene, sinon il reste cale en
	// haut/bas de la scene. Le fond image ne bouge pas, les slides s'enchainent
	// en fondu. Aucun spacer = aucun saut, et le stage ne masque jamais une
	// autre section (il n'est fixe que dans la plage de la scene).
	// =====================================================================
	function initCineScene() {
		if (IS_TOUCH || window.innerWidth <= 1024) return; // mobile = fallback empile (CSS)
		document.querySelectorAll('.ag-cinescene').forEach(function (scene) {
			var stage  = scene.querySelector('.ag-cinescene__stage');
			var slides = scene.querySelectorAll('.ag-cinescene__slide');
			var ghosts = scene.querySelectorAll('.ag-cinescene__ghost');
			var dots   = scene.querySelectorAll('.ag-cinescene__dots span');
			var img    = scene.querySelector('.ag-cinescene__bgimg');
			var n = slides.length;
			if (!stage || !n) return;

			scene.classList.add('is-cinematic');
			scene.style.height = (n * 100) + 'vh'; // longueur de scroll = 1 ecran par slide

			var cur = -1, state = '';
			function setActive(i) {
				if (i === cur) return; cur = i;
				slides.forEach(function (s, idx) { s.classList.toggle('is-active', idx === i); });
				ghosts.forEach(function (g, idx) { g.classList.toggle('is-active', idx === i); });
				dots.forEach(function (d, idx) { d.classList.toggle('is-active', idx === i); });
			}
			function update() {
				var rect = scene.getBoundingClientRect();
				var vh = window.innerHeight;
				var into = -rect.top;                       // px scrolles dans la scene
				var total = scene.offsetHeight - vh;        // distance utile pour les slides
				var st, prog;
				if (into <= 0) { st = 'before'; prog = 0; }
				else if (rect.bottom <= 0) { st = 'after'; prog = 1; }  // scene entierement passee
				else { st = 'fixed'; prog = total > 0 ? Math.min(1, into / total) : 0; }
				// -> reste 'fixed' meme apres la derniere slide : la section suivante
				//    (.ag-stackover, opaque, z-index superieur) remonte et la recouvre.
				if (st !== state) {
					scene.classList.toggle('is-fixed', st === 'fixed');
					scene.classList.toggle('is-after', st === 'after');
					state = st;
				}
				setActive(Math.min(n - 1, Math.floor(prog * n * 0.9999)));
				if (img) img.style.transform = 'scale(' + (1.06 + prog * 0.1).toFixed(3) + ')';
			}
			onTick(update);            // synchronise avec Lenis (gsap.ticker) ou rAF
			window.addEventListener('resize', function () { scene.style.height = (n * 100) + 'vh'; }, { passive: true });
			update();
		});
	}

	// =====================================================================
	// SCROLL HORIZONTAL — epinglage MANUEL (pas de pin GSAP) : le stage passe
	// en fixed pendant la traversee, la piste glisse selon la progression.
	// Cartes en 3D selon leur distance au centre. Auto-corrige chaque frame.
	// =====================================================================
	function initHScroll() {
		if (IS_TOUCH || window.innerWidth <= 1024) return; // mobile = scroll natif (CSS)
		document.querySelectorAll('.ag-hscroll').forEach(function (sec) {
			var pin   = sec.querySelector('.ag-hscroll__pin');
			var track = sec.querySelector('.ag-hscroll__track');
			if (!pin || !track) return;
			var cards = track.querySelectorAll('.ag-hscroll__card');
			sec.classList.add('is-cinematic', 'is-3d');

			var dist = 0, state = '';
			function measure() {
				dist = Math.max(0, track.scrollWidth - window.innerWidth);
				sec.style.height = (window.innerHeight + dist) + 'px';
			}
			function update() {
				var rect = sec.getBoundingClientRect();
				var vh = window.innerHeight;
				var into = -rect.top;
				var total = sec.offsetHeight - vh;
				var st, prog;
				if (into <= 0) { st = 'before'; prog = 0; }
				else if (into >= total) { st = 'after'; prog = 1; }
				else { st = 'fixed'; prog = total > 0 ? into / total : 0; }
				if (st !== state) {
					sec.classList.toggle('is-fixed', st === 'fixed');
					sec.classList.toggle('is-after', st === 'after');
					state = st;
				}
				track.style.transform = 'translate3d(' + (-prog * dist) + 'px,0,0)';
				// cartes en 3D selon la distance au centre du viewport
				var vw = window.innerWidth, mid = vw / 2;
				cards.forEach(function (card) {
					var r = card.getBoundingClientRect();
					if (r.right < -200 || r.left > vw + 200) return;
					var off = (r.left + r.width / 2 - mid) / vw;
					var rot = Math.max(-16, Math.min(16, off * 20));
					var sc  = 1 - Math.min(0.12, Math.abs(off) * 0.16);
					card.style.transform = 'perspective(1300px) rotateY(' + (-rot) + 'deg) scale(' + sc.toFixed(3) + ')';
				});
			}
			measure();
			onTick(update);
			window.addEventListener('resize', function () { measure(); }, { passive: true });
			update();
		});
	}

	// =====================================================================
	// EMPILEMENT AUTOMATIQUE (tout le site) — applique le stacking aux
	// sections de contenu des autres pages, avec garde-fous anti-casse.
	// =====================================================================
	function initSectionStack() {
		if ( IS_TOUCH || window.innerWidth <= 1024 ) return;        // desktop seulement
		var container = document.querySelector( 'main' ) || document.body; // accueil = pas de <main>
		var secs = [];
		for ( var i = 0; i < container.children.length; i++ ) {
			if ( container.children[ i ].tagName === 'SECTION' ) secs.push( container.children[ i ] );
		}
		if ( secs.length < 2 ) return;
		// si scroll-jack present (accueil) : on ne stacke que ce qui vient APRES lui
		var startIdx = 1; // saute le hero
		for ( var s = 0; s < secs.length; s++ ) {
			if ( secs[ s ].querySelector && secs[ s ].querySelector( '.ag-fx-fixed-section' ) ) startIdx = s + 1;
			if ( secs[ s ].classList.contains( 'ag-fx-fixed-section' ) ) startIdx = s + 1;
		}
		var vh = window.innerHeight, z = 2;
		for ( var j = startIdx; j < secs.length; j++ ) {
			var sec = secs[ j ];
			// uniquement les sections de contenu STANDARD (.ag-section) -> exclut
			// d'office hero, parallax, globe, cinescene, hscroll, teamrow...
			if ( ! sec.classList.contains( 'ag-section' ) ) continue;
			// deja gerees par leur propre effet -> ne pas doubler
			if ( sec.classList.contains( 'ag-stackover' ) || sec.classList.contains( 'ag-teamrow' ) ||
				sec.classList.contains( 'ag-cinescene' ) || sec.classList.contains( 'ag-hscroll' ) ) { z++; continue; }
			sec.classList.add( 'ag-autostack' );
			sec.style.zIndex = z++;
			// "tenir" (sticky) UNIQUEMENT si la section tient dans l'ecran. Une
			// section plus haute que le viewport, une fois epinglee, cacherait son
			// propre bas (boutons inaccessibles) -> on ne l'epingle pas.
			if ( j < secs.length - 1 && sec.offsetHeight >= vh * 0.6 && sec.offsetHeight <= vh * 1.05 ) {
				sec.classList.add( 'ag-autostack--hold' );
			}
		}
	}

	// =====================================================================
	// BOOT
	// =====================================================================
	// =====================================================================
	// ACCUEIL « cinematique » : titre mot a mot, approche de la main (le fond
	// video grossit + se fond au scroll), lion en filigrane, dissolution en
	// poussiere d'or (canvas). Porte depuis docs/cinematique/alliance-demo.html.
	// Scope strict a l'accueil via .ag-lm__hero. GSAP/ScrollTrigger sont deja
	// charges ; on ne re-init PAS Lenis (fait par initSmoothScroll).
	// =====================================================================
	function initHomeCine() {
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
		var hero = document.querySelector('.ag-lm__hero');
		if (!hero) return; // uniquement sur l'accueil
		document.documentElement.classList.add('ag-cine-on'); // active les styles animés (dissolution)

		// -- Titre : chaque mot monte (preserve <em> et <br>) --
		var title = document.querySelector('.ag-lm__hero [data-split]');
		if (title && !title.dataset.split) {
			title.dataset.split = '1';
			var frag = document.createDocumentFragment();
			function wrapWord(word, isEm) {
				var span = document.createElement('span'); span.className = 'ag-w';
				var it = document.createElement('i'); if (isEm) it.className = 'ag-w-em';
				it.textContent = word; span.appendChild(it); return span;
			}
			function pushWords(text, isEm) {
				text.split(/(\s+)/).forEach(function (p) {
					if (p === '') return;
					if (/^\s+$/.test(p)) frag.appendChild(document.createTextNode(' '));
					else frag.appendChild(wrapWord(p, isEm));
				});
			}
			Array.prototype.slice.call(title.childNodes).forEach(function (n) {
				if (n.nodeType === 3) pushWords(n.textContent, false);
				else if (n.nodeType === 1 && n.tagName === 'BR') frag.appendChild(document.createElement('br'));
				else if (n.nodeType === 1 && n.tagName === 'EM') pushWords(n.textContent, true);
				else if (n.nodeType === 1) pushWords(n.textContent, false);
			});
			title.innerHTML = ''; title.appendChild(frag);
			gsap.from(title.querySelectorAll('.ag-w i'), { yPercent: 115, duration: 1.1, ease: 'expo.out', stagger: 0.09, delay: 0.15 });
		}

		// -- Entrees sous-titre / actions --
		gsap.from('.ag-lm__hero .ag-lm__hsub', { y: 24, opacity: 0, duration: 1, ease: 'power3.out', delay: 0.5 });
		gsap.from('.ag-lm__hero .ag-lm__cta', { y: 24, opacity: 0, duration: 1, ease: 'power3.out', delay: 0.66 });

		// -- Approche : le fond video grossit et se fond quand le hero defile --
		var bg = hero.querySelector('.ag-lm__hbg');
		if (bg) {
			gsap.to(bg, {
				scale: 2.6, opacity: 0, ease: 'power1.in', transformOrigin: '70% 55%',
				scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.5 }
			});
		}
		gsap.to('.ag-lm__hero .ag-lm__hin', {
			y: -40, opacity: 0, ease: 'none',
			scrollTrigger: { trigger: hero, start: 'top top', end: '55% top', scrub: true }
		});
		var lionmark = hero.querySelector('.ag-lm__lionmark');
		if (lionmark) {
			gsap.to(lionmark, {
				scale: 1.25, opacity: 0.03, ease: 'none',
				scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true }
			});
		}

		// -- Revelations generiques --
		gsap.utils.toArray('[data-rv]').forEach(function (el) {
			gsap.from(el, { y: 34, opacity: 0, duration: 0.95, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 86%' } });
		});

		// -- Images de chapitres : reveal clip-path + parallaxe interne --
		gsap.utils.toArray('[data-media]').forEach(function (m) {
			var im = m.querySelector('img');
			if (im) {
				gsap.fromTo(im, { scale: 1.22, yPercent: -6 }, { scale: 1.02, yPercent: 6, ease: 'none',
					scrollTrigger: { trigger: m, start: 'top bottom', end: 'bottom top', scrub: true } });
			}
			gsap.from(m, { clipPath: 'inset(100% 0% 0% 0%)', duration: 1.25, ease: 'power4.out',
				scrollTrigger: { trigger: m, start: 'top 82%' } });
		});

		// -- Dissolution en poussiere d'or (canvas 2D) --
		var cv = document.getElementById('ag-cv');
		if (cv) {
			var ctx = cv.getContext('2d');
			var img = new Image(); img.crossOrigin = 'anonymous';
			var cell = 12, cols = 0, rows = 0, seed = [], progress = 0, dready = false;
			function build() {
				var dpr = Math.min(window.devicePixelRatio || 1, 2);
				cv.width = Math.floor(cv.clientWidth * dpr); cv.height = Math.floor(cv.clientHeight * dpr);
				if (!img.complete || !img.naturalWidth) return;
				cols = Math.ceil(cv.width / cell); rows = Math.ceil(cv.height / cell);
				seed = new Float32Array(cols * rows);
				for (var i = 0; i < seed.length; i++) seed[i] = Math.random();
				dready = true; draw();
			}
			function draw() {
				if (!dready) return;
				ctx.fillStyle = '#05050a'; ctx.fillRect(0, 0, cv.width, cv.height);
				var r = Math.max(cv.width / img.naturalWidth, cv.height / img.naturalHeight);
				var iw = img.naturalWidth * r, ih = img.naturalHeight * r;
				ctx.drawImage(img, (cv.width - iw) / 2, (cv.height - ih) / 2 * 1.1, iw, ih);
				var p = progress;
				for (var y = 0; y < rows; y++) {
					for (var x = 0; x < cols; x++) {
						var i = y * cols + x;
						var wave = (x / cols) * 0.45 + (y / rows) * 0.25;
						var th = wave + seed[i] * 0.55;
						var k = (p - th) / 0.35;
						if (k <= 0) continue;
						if (k < 1) {
							ctx.fillStyle = '#05050a'; ctx.fillRect(x * cell, y * cell, cell + 1, cell + 1);
							var a = 1 - k, dy = k * cell * 5 * (seed[i] - 0.3), dx = k * cell * 3 * (seed[i] - 0.5);
							var g = Math.round(212 + 32 * k), b = Math.round(92 + 20 * k);
							ctx.fillStyle = 'rgba(' + g + ',' + Math.round(180 + 28 * k) + ',' + b + ',' + (a * 0.95) + ')';
							var s = cell * (1 - k * 0.6);
							ctx.fillRect(x * cell + dx, y * cell - dy, s, s);
						} else {
							ctx.fillStyle = '#05050a'; ctx.fillRect(x * cell, y * cell, cell + 1, cell + 1);
						}
					}
				}
			}
			img.onload = build; img.src = cv.getAttribute('data-src') || '';
			window.addEventListener('resize', build, { passive: true });
			ScrollTrigger.create({ trigger: '.ag-dissolve', start: 'top top', end: 'bottom bottom', scrub: true,
				onUpdate: function (self) { progress = self.progress * 1.12; draw(); } });
		}
	}

	function boot() {
		if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
			gsap.registerPlugin(ScrollTrigger);
		}
		// Smooth scroll (Lenis) + reveal d'images (ScrollTrigger) + curseur.
		initSmoothScroll();
		initImageReveal();
		initHomeCine();
		if (DESKTOP_FX) {
			initCursor();
			initHeroDepth();
		}
	}

	ready(function () {
		if (REDUCED) return;
		HAS_SCROLLJACK = !!document.querySelector('.ag-fx-fixed-section');

		// PAGE AVEC SCROLL-JACKING MAISON (accueil) : ce composant possede SON
		// propre GSAP/ScrollTrigger. On NE charge PAS GSAP et on N'utilise PAS
		// ScrollTrigger ici (sinon conflit). MAIS les effets MANUELS (CSS sticky
		// / rAF, sans GSAP) sont surs et restent actifs : stacking + equipe.
		if (HAS_SCROLLJACK) {
			// PAS de stacking (initSectionStack) ici : l'accueil a des sections
			// complexes (carrousel pin, parallax, globe 3D, FAQ avec image de
			// fond) qui se chevauchent mal quand on les empile. On garde juste
			// les effets surs.
			initCineScene();
			initHScroll();
			if (DESKTOP_FX) { initCursor(); initHeroDepth(); }
			return;
		}

		// Pages normales : effets cinematographiques sur-mesure (cinescene /
		// hscroll de la page Racines). Le stacking AUTOMATIQUE (initSectionStack)
		// est DESACTIVE : trop de risques de chevauchement selon les sections.
		// La page Racines garde son stacking dedie (.ag-stackover, CSS pur).
		initCineScene();
		initHScroll();
		// GSAP + ScrollTrigger sont deja charges globalement (enqueue WP, <head>).
		// On ne charge dynamiquement que Lenis (smooth scroll).
		var need = [];
		if (typeof gsap === 'undefined') need.push(CDN.gsap);              // filet de secours
		if (typeof ScrollTrigger === 'undefined') need.push(CDN.st);      // filet de secours
		if (!IS_TOUCH && typeof Lenis === 'undefined') need.push(CDN.lenis);
		loadSeq(need, boot);
	});
})();
