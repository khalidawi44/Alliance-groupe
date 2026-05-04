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

	/* ── Injecte les ancres de toutes les sections dans le nav header.
	   (Plus d'overlay/burger : tous les onglets sont visibles direct.)
	   Smooth-scroll au clic. */
	function injectNavAnchors() {
		var nav = document.querySelector('.ag-header__nav');
		if (!nav) return;
		var cta = nav.querySelector('.ag-header__cta');

		var sectionAnchors = [
			{ id: 'services',           label: 'Tarifs' },
			{ id: 'queue',              label: "File d'attente" },
			{ id: 'ag-bb-team',         label: 'Équipe' },
			{ id: 'ag-bb-gallery',      label: 'Galerie' },
			{ id: 'ag-bb-testimonials', label: 'Avis' },
			{ id: 'ag-bb-booking',      label: 'Réserver' },
			{ id: 'ag-bb-contact',      label: 'Contact' }
		];

		// Retire les liens existants (sauf le CTA)
		nav.querySelectorAll('a:not(.ag-header__cta)').forEach(function (a) {
			a.remove();
		});

		// Insere les ancres trouvees avant le CTA
		sectionAnchors.forEach(function (s) {
			if (!document.getElementById(s.id)) return;
			var a = document.createElement('a');
			a.href = '#' + s.id;
			a.textContent = s.label;
			a.addEventListener('click', function (e) {
				var target = document.getElementById(s.id);
				if (target) {
					e.preventDefault();
					target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
			if (cta) {
				nav.insertBefore(a, cta);
			} else {
				nav.appendChild(a);
			}
		});
	}

	function run() {
		if (!isActive()) return;
		applyCustomLogo();
		setupSmartHeader();
		injectHeroLogo();
		injectQrCode();
		// Defer pour laisser Business plugin injecter ses sections
		// avant qu'on construise le nav qui les reference.
		setTimeout(injectNavAnchors, 200);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
