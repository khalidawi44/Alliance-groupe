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

	/* ── Bouton recherche dans le header + overlay plein-ecran ── */
	function injectSearchButton() {
		if (!isBusinessActive()) return;
		var actions = document.querySelector('.ag-header-actions');
		if (!actions) return;
		if (actions.querySelector('.ag-business-search-btn')) return;

		var homeUrl = window.location.origin + (window.location.pathname.split('/').slice(0, -1).join('/') || '/');
		// fallback simple : on retourne juste a la racine
		var actionUrl = window.location.origin + '/';

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'ag-business-search-btn';
		btn.setAttribute('aria-label', 'Rechercher');
		btn.setAttribute('title', 'Rechercher');
		btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 21l-4.5-4.5M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		actions.appendChild(btn);

		var overlay = document.createElement('div');
		overlay.className = 'ag-business-search-overlay';
		overlay.setAttribute('role', 'dialog');
		overlay.setAttribute('aria-modal', 'true');
		overlay.setAttribute('aria-label', 'Recherche');
		overlay.innerHTML =
			'<form role="search" method="get" action="' + actionUrl + '" class="ag-business-search-form">' +
			'<input type="search" name="s" placeholder="Rechercher un domaine, un article..." aria-label="Rechercher">' +
			'<button type="submit" aria-label="Lancer la recherche">→</button>' +
			'</form>' +
			'<button type="button" class="ag-business-search-close" aria-label="Fermer la recherche">×</button>';
		document.body.appendChild(overlay);

		var input = overlay.querySelector('input[type="search"]');
		var closeBtn = overlay.querySelector('.ag-business-search-close');

		var open = function () {
			overlay.classList.add('is-open');
			setTimeout(function () { input && input.focus(); }, 50);
		};
		var close = function () {
			overlay.classList.remove('is-open');
		};

		btn.addEventListener('click', open);
		closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', function (e) {
			if (e.target === overlay) close();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
		});
	}

	/* ── Boutons Stripe sous chaque card Honoraires ──
	   URLs configurees via Customizer (3 fields). Si l'URL est vide,
	   aucun bouton injecte sur la card correspondante. */
	function injectStripeButtons() {
		if (!isBusinessActive()) return;
		var urls = dataValue('stripeUrls', []);
		if (!urls || !urls.length) return;
		var cards = document.querySelectorAll('.ag-honoraires__card');
		cards.forEach(function (card, idx) {
			var url = urls[idx];
			if (!url) return;
			if (card.querySelector('.ag-business-stripe-btn')) return;

			var btn = document.createElement('a');
			btn.className = 'ag-business-stripe-btn';
			btn.href = url;
			btn.target = '_blank';
			btn.rel = 'noopener noreferrer';
			btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="vertical-align:middle;margin-right:8px;"><path d="M13 9V3.5L18.5 9M6 2c-1.11 0-2 .89-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6m4 11h2v3h3v2h-3v3h-2v-3H7v-2h3v-3z"/></svg>Payer en ligne';
			btn.setAttribute('aria-label', 'Payer cette offre en ligne via Stripe');
			btn.addEventListener('click', function (e) {
				e.stopPropagation(); // ne pas declencher le click de la card
			});
			card.appendChild(btn);
		});
	}

	/* ── Injection equipe Cabinet en 2 groupes ───────────────────
	   Ordre cible sur la page Cabinet :
	     1. Page hero (titre)
	     2. Avocats associes (groupe 1)
	     3. Citation Ciceron (par injectPageCitations)
	     4. Collaborateurs (groupe 2)
	     5. Citation Pascal (par injectPageCitations)
	     6. Nous trouver (.ag-cabinet-map-section, deja dans le DOM Free)
	   Le .ag-maitre du theme Free est masque (le Maitre est deja
	   integre dans la card associates Sophie DUPONT). */
	function injectCabinetTeam() {
		if (!isBusinessActive()) return;
		var assocHtml = dataValue('cabinetAssociatesHtml', '');
		var collabHtml = dataValue('cabinetCollaboratorsHtml', '');
		if (!assocHtml && !collabHtml) return; // pas la page Cabinet

		// Cache la section Maitre du theme — son contenu est repris dans
		// la card de l'associee fondatrice.
		var maitre = document.querySelector('.ag-maitre');
		if (maitre) maitre.style.display = 'none';

		var hero = document.querySelector('.ag-page-hero');
		var anchor = hero || document.getElementById('ag-main') || document.querySelector('main');
		if (!anchor) return;

		// Inserer associates apres le hero
		if (assocHtml && !document.querySelector('.ag-business-team-full--associates')) {
			anchor.insertAdjacentHTML('afterend', assocHtml);
		}
		// Inserer collaborators apres associates (ou apres hero si echec)
		if (collabHtml && !document.querySelector('.ag-business-team-full--collaborators')) {
			var nextAnchor = document.querySelector('.ag-business-team-full--associates') || anchor;
			nextAnchor.insertAdjacentHTML('afterend', collabHtml);
		}
	}

	/* ── Citations parallax injectees entre sections (pages internes) ── */
	function injectPageCitations() {
		if (!isBusinessActive()) return;
		var citations = dataValue('pageCitations', []);
		if (!citations || !citations.length) return;
		citations.forEach(function (c, idx) {
			if (!c.insertAfter || !c.quote) return;
			var anchor = document.querySelector(c.insertAfter);
			if (!anchor) return;
			// Skip si deja injecte
			if (anchor.nextElementSibling && anchor.nextElementSibling.classList && anchor.nextElementSibling.classList.contains('ag-business-page-citation')) return;
			var html = '<section class="ag-parallax ag-parallax-business ag-business-page-citation" style="background-image:url(\'' +
				c.bg.replace(/'/g, '\\\'') + '\');">' +
				'<div class="ag-parallax__overlay"></div>' +
				'<div class="ag-parallax__content">' +
				'<p class="ag-parallax__quote">' + escapeHtml(c.quote) + '</p>' +
				'<p class="ag-parallax__caption">— ' + escapeHtml(c.author || '') + '</p>' +
				'</div>' +
				'</section>';
			anchor.insertAdjacentHTML('afterend', html);
		});
	}

	function escapeHtml(s) {
		return String(s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function run() {
		animateCounters();
		makeHonorairesClickable();
		makeDomainesClickable();
		applyBoutiqueSymbol();
		injectSearchButton();
		injectStripeButtons();
		injectCabinetTeam();
		injectPageCitations();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
