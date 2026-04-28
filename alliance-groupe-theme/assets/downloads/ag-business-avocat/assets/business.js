/**
 * AG Business Avocat — runtime hardening.
 *
 * Convention : aucune selection sur classes Free / Premium uniquement
 * via verification que body porte bien `ag-business-active`.
 */
(function () {
	'use strict';

	function isBusinessActive() {
		return document.body.classList.contains('ag-business-active');
	}
	function dataValue(key, fallback) {
		return (typeof agBusinessData !== 'undefined' && agBusinessData[key] != null)
			? agBusinessData[key]
			: fallback;
	}

	/* ── Compteurs : count-up animation au scroll-in ────────────── */
	function animateCounters() {
		if (!isBusinessActive()) return;
		var nodes = document.querySelectorAll('.ag-counter__number');
		if (!nodes.length) return;
		if (typeof IntersectionObserver === 'undefined') return;

		var animate = function (el) {
			var raw = (el.textContent || '').trim();
			var m = raw.match(/^(\d+)(.*)$/);
			if (!m) return;
			var target = parseInt(m[1], 10);
			var suffix = m[2] || '';
			if (!isFinite(target) || target <= 0) return;
			var duration = 1600;
			var start = 0;
			el.setAttribute('data-counting', '1');
			el.textContent = '0' + suffix;

			function step(now) {
				if (!start) start = now;
				var t = Math.min(1, (now - start) / duration);
				var eased = 1 - Math.pow(1 - t, 3);
				el.textContent = Math.floor(target * eased) + suffix;
				if (t < 1) requestAnimationFrame(step);
				else {
					el.textContent = target + suffix;
					el.removeAttribute('data-counting');
				}
			}
			requestAnimationFrame(step);
		};

		var obs = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					animate(e.target);
					obs.unobserve(e.target);
				}
			});
		}, { threshold: 0.4 });

		nodes.forEach(function (n) { obs.observe(n); });
	}

	/* ── Cartes Honoraires : cliquables vers la page dediee ───── */
	function makeHonorairesClickable() {
		if (!isBusinessActive()) return;
		var url = dataValue('honorairesUrl', '/honoraires/');
		document.querySelectorAll('.ag-honoraires .ag-honoraires__card').forEach(function (card) {
			if (card.dataset.agBusinessClickable === '1') return;
			card.dataset.agBusinessClickable = '1';
			card.style.cursor = 'pointer';
			card.setAttribute('role', 'link');
			card.setAttribute('tabindex', '0');
			card.setAttribute('aria-label', 'Voir les details des honoraires');
			card.addEventListener('click', function (e) {
				if (e.target.closest('a')) return;
				window.location.href = url;
			});
			card.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					window.location.href = url;
				}
			});
		});
	}

	/* ── Cartes Domaines (.ag-domaine-card) : cliquables vers leur
	   page dediee. Les URLs sont passees par PHP via wp_localize_script
	   dans le meme ordre que :nth-child. */
	function makeDomainesClickable() {
		if (!isBusinessActive()) return;
		var urls = dataValue('domaineUrls', []);
		if (!urls || !urls.length) return;
		var cards = document.querySelectorAll('.ag-domaines .ag-domaine-card');
		cards.forEach(function (card, idx) {
			var href = urls[idx];
			if (!href) return;
			if (card.dataset.agBusinessClickable === '1') return;
			card.dataset.agBusinessClickable = '1';
			card.style.cursor = 'pointer';
			card.setAttribute('role', 'link');
			card.setAttribute('tabindex', '0');
			var title = card.querySelector('.ag-domaine-card__title');
			if (title) card.setAttribute('aria-label', title.textContent.trim());
			card.addEventListener('click', function (e) {
				if (e.target.closest('a')) return;
				window.location.href = href;
			});
			card.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					window.location.href = href;
				}
			});
		});
	}

	/* ── Boutique : motif anime configurable via Customizer ──
	   - 'stars'  : laisse les SVG d'origine (etoiles de pro-features.php)
	   - 'scales' : balance de la justice
	   - 'gavel'  : marteau de juge
	   - 'pillar' : colonne classique
	   - 'none'   : masque la host (zero animation) */
	var BOUTIQUE_SYMBOLS = {
		scales: '<svg viewBox="0 0 24 24"><path d="M12 2c.55 0 1 .45 1 1v1.07c2.84.49 5 2.97 5 5.93h2v2H4v-2h2c0-2.96 2.16-5.44 5-5.93V3c0-.55.45-1 1-1zm-7 9 3 6H2l3-6zm14 0 3 6h-6l3-6zm-7 8h-2v3h-3v1h8v-1h-3v-3z"/></svg>',
		gavel:  '<svg viewBox="0 0 24 24"><path d="M14.34 5.66 9.41 10.59 5.17 6.34l4.95-4.95 4.22 4.27zM12 16l-1.41 1.41 2.83 2.83L14.83 19 12 16zm6.66-7.66-1.41-1.41-3.54 3.54 1.41 1.41 3.54-3.54zM3 22h18v-2H3v2zM10.83 12 4.46 18.37l1.41 1.41 6.37-6.37-1.41-1.41z"/></svg>',
		pillar: '<svg viewBox="0 0 24 24"><path d="M3 3h18v3H3V3zm2 4h2v11h2V7h2v11h2V7h2v11h2V7h2v11h2V7h-2V6H7v1H5v11H3v3h18v-3h-2V7z"/></svg>'
	};

	function applyBoutiqueSymbol() {
		if (!isBusinessActive()) return;
		var choice = String(dataValue('boutiqueSymbol', 'stars')).toLowerCase();

		if (choice === 'none') {
			var host = document.querySelector('.ag-boutique-stars-host');
			if (host) host.style.display = 'none';
			return;
		}
		if (choice === 'stars') {
			return; // garde le SVG d'origine (etoiles)
		}
		var svg = BOUTIQUE_SYMBOLS[choice];
		if (!svg) return;
		document.querySelectorAll('.ag-boutique-shooting-star').forEach(function (star) {
			star.innerHTML = svg;
		});
	}

	function run() {
		animateCounters();
		makeHonorairesClickable();
		makeDomainesClickable();
		applyBoutiqueSymbol();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
