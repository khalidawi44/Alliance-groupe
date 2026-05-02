/**
 * AG Premium Barber — JS runtime.
 *
 * 1. Smart header : transparent au top, dark on scroll, auto-hide
 *    on scroll-down, reapparait on scroll-up.
 * 2. Burger menu overlay (intercepte le CTA top-right).
 * 3. Hero logo centre (gros crest barber).
 * 4. QR code dans la section file d'attente (lien kiosk).
 */
(function () {
	'use strict';

	function isActive() {
		return document.body.classList.contains('ag-bb-active');
	}
	function customLogoUrl() {
		return (typeof agPbData !== 'undefined' && agPbData.logoUrl) ? agPbData.logoUrl : '';
	}

	/* ── Logo personnalise : remplace l'icone SVG ciseaux par
	   l'image fournie via Customizer (header gauche + hero centre). */
	function applyCustomLogo() {
		var url = customLogoUrl();
		if (!url) return;
		var safe = url.replace(/'/g, '%27').replace(/"/g, '%22');
		var css =
			'body.ag-bb-active .ag-header__logo::before{' +
				'background-color:transparent !important;' +
				'-webkit-mask:none !important;mask:none !important;' +
				'background:url(\'' + safe + '\') center/contain no-repeat !important;' +
				'filter:drop-shadow(0 0 10px var(--bb-accent-glow));' +
			'}' +
			'.ag-bb-hero-logo{' +
				'background-color:transparent !important;' +
				'-webkit-mask:none !important;mask:none !important;' +
				'background:url(\'' + safe + '\') center/contain no-repeat !important;' +
				'filter:drop-shadow(0 0 28px var(--bb-accent-glow)) drop-shadow(0 0 8px rgba(0,0,0,.6));' +
			'}';
		var style = document.createElement('style');
		style.id = 'ag-bb-custom-logo';
		style.textContent = css;
		document.head.appendChild(style);
	}

	/* ── Smart header : scroll-aware ───────────────────────── */
	function setupSmartHeader() {
		var header = document.querySelector('.ag-header');
		if (!header) return;
		var lastScroll = window.scrollY;
		var ticking = false;
		function onScroll() {
			var current = window.scrollY;
			// Dark bg quand on n'est plus tout en haut
			if (current > 30) {
				header.classList.add('ag-bb-header--scrolled');
			} else {
				header.classList.remove('ag-bb-header--scrolled');
			}
			// Auto-hide on scroll-down (page descend, on cache),
			// reapparait on scroll-up. Threshold a 100px pour eviter
			// des cache/show parasites en debut de page.
			if (current > lastScroll && current > 100) {
				header.classList.add('ag-bb-header--hidden');
			} else if (current < lastScroll) {
				header.classList.remove('ag-bb-header--hidden');
			}
			lastScroll = current;
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) {
				window.requestAnimationFrame(onScroll);
				ticking = true;
			}
		}, { passive: true });
	}

	/* ── Logo hero centre ──────────────────────────────────── */
	function injectHeroLogo() {
		var hero = document.querySelector('.ag-hero');
		if (!hero) return;
		if (hero.querySelector('.ag-bb-hero-logo')) return;
		var content = hero.querySelector('div'); // 1er div interne
		if (!content) return;
		var logo = document.createElement('div');
		logo.className = 'ag-bb-hero-logo';
		logo.setAttribute('aria-hidden', 'true');
		// Insere avant le 1er enfant du content (au-dessus du tag)
		content.insertBefore(logo, content.firstChild);
	}

	/* ── QR code pour file d'attente ───────────────────────── */
	function injectQrCode() {
		var queue = document.getElementById('queue');
		if (!queue) return;
		if (queue.querySelector('.ag-bb-qr-wrap')) return;
		var status = queue.querySelector('.ag-queue-status');
		if (!status) return;
		var qrUrl = (window.location.origin || '') + '/?ag_queue=join';
		var qrImg = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=' + encodeURIComponent(qrUrl);
		var wrap = document.createElement('div');
		wrap.className = 'ag-bb-qr-wrap';
		wrap.innerHTML =
			'<div class="ag-bb-qr"><img src="' + qrImg + '" alt="QR code pour rejoindre la file"></div>' +
			'<p class="ag-bb-qr-caption">Scannez pour prendre votre ticket</p>';
		status.appendChild(wrap);
	}

	/* ── Burger menu overlay ───────────────────────────────── */
	function buildOverlayMenu(navLinks, ticketUrl) {
		var overlay = document.createElement('div');
		overlay.className = 'ag-bb-menu-overlay';
		overlay.setAttribute('role', 'dialog');
		overlay.setAttribute('aria-modal', 'true');
		overlay.setAttribute('aria-hidden', 'true');

		var inner = document.createElement('div');
		inner.className = 'ag-bb-menu-overlay__inner';

		var close = document.createElement('button');
		close.className = 'ag-bb-menu-overlay__close';
		close.setAttribute('aria-label', 'Fermer le menu');
		close.innerHTML = '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
		inner.appendChild(close);

		var ul = document.createElement('ul');
		ul.className = 'ag-bb-menu-overlay__list';
		navLinks.forEach(function (l) {
			var li = document.createElement('li');
			var a = document.createElement('a');
			a.href = l.href;
			a.textContent = l.label;
			li.appendChild(a);
			ul.appendChild(li);
		});
		inner.appendChild(ul);

		if (ticketUrl) {
			var cta = document.createElement('a');
			cta.className = 'ag-bb-menu-overlay__cta';
			cta.href = ticketUrl;
			cta.textContent = 'Prendre un ticket maintenant';
			inner.appendChild(cta);
		}

		overlay.appendChild(inner);
		return { overlay: overlay, close: close };
	}

	function setupBurger() {
		var cta = document.querySelector('.ag-header__nav > .ag-header__cta');
		if (!cta) return;
		if (cta.dataset.bbBurger === '1') return;
		cta.dataset.bbBurger = '1';

		var ticketUrl = cta.href;

		// Liste statique des sections potentielles (par ID DOM).
		// On filtre au moment du clic pour n'afficher que celles qui
		// existent reellement (Business plugin peut ne pas etre actif).
		var sectionAnchors = [
			{ id: 'queue',              label: "File d'attente" },
			{ id: 'services',           label: 'Tarifs' },
			{ id: 'ag-bb-team',         label: 'Notre équipe' },
			{ id: 'ag-bb-gallery',      label: 'La galerie' },
			{ id: 'ag-bb-testimonials', label: 'Témoignages' },
			{ id: 'ag-bb-booking',      label: 'Réserver' },
			{ id: 'ag-bb-contact',      label: 'Nous trouver' }
		];

		function buildLinks() {
			var seen = {};
			var links = [];
			sectionAnchors.forEach(function (s) {
				if (document.getElementById(s.id) && !seen[s.id]) {
					seen[s.id] = 1;
					links.push({ href: '#' + s.id, label: s.label });
				}
			});
			return links;
		}

		// Build overlay une fois (sera mis a jour avant chaque ouverture)
		var built = buildOverlayMenu(buildLinks(), ticketUrl);
		document.body.appendChild(built.overlay);

		function refreshLinks() {
			var ul = built.overlay.querySelector('.ag-bb-menu-overlay__list');
			if (!ul) return;
			ul.innerHTML = '';
			buildLinks().forEach(function (l) {
				var li = document.createElement('li');
				var a = document.createElement('a');
				a.href = l.href;
				a.textContent = l.label;
				a.addEventListener('click', function (e) {
					// Smooth scroll vers l'ancre
					var target = document.querySelector(l.href);
					if (target) {
						e.preventDefault();
						close();
						setTimeout(function () {
							target.scrollIntoView({ behavior: 'smooth', block: 'start' });
						}, 350);
					} else {
						close();
					}
				});
				li.appendChild(a);
				ul.appendChild(li);
			});
		}

		function open() {
			refreshLinks();
			built.overlay.classList.add('is-open');
			built.overlay.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
		}
		function close() {
			built.overlay.classList.remove('is-open');
			built.overlay.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}

		cta.addEventListener('click', function (e) {
			e.preventDefault();
			open();
		});
		built.close.addEventListener('click', function (e) {
			e.preventDefault();
			close();
		});
		built.overlay.addEventListener('click', function (e) {
			if (e.target === built.overlay) close();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && built.overlay.classList.contains('is-open')) close();
		});
	}

	function run() {
		if (!isActive()) return;
		applyCustomLogo();
		setupSmartHeader();
		injectHeroLogo();
		injectQrCode();
		setupBurger();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
