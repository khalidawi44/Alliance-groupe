/* AG Gwen — signature.js : révélations au scroll (magazine).
   Directions variées + cascade (stagger). Ajoute .is-in quand
   l'élément entre dans le viewport. */
(function () {
	function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
	ready(function () {
		var els = [];

		// Tag simple (une direction), sans cascade.
		function tag(sel, dir) {
			document.querySelectorAll(sel).forEach(function (el) {
				if (el.__sig) return; el.__sig = 1;
				el.setAttribute('data-sig-reveal', dir);
				els.push(el);
			});
		}
		// Tag avec cascade : chaque élément est décalé selon sa position
		// parmi ses frères (grilles de cartes → apparition en vague).
		function tagStagger(sel, dir) {
			document.querySelectorAll(sel).forEach(function (el) {
				if (el.__sig) return; el.__sig = 1;
				el.setAttribute('data-sig-reveal', dir);
				var i = 0, p = el.parentNode;
				if (p) i = Array.prototype.indexOf.call(p.children, el);
				el.style.transitionDelay = (Math.min(i, 7) * 75) + 'ms';
				els.push(el);
			});
		}

		// TITRES → arrivent de la droite
		tag('.ag-services-grid-title, .ag-gwenwhy__title, .ag-gwengal__title, .ag-page-title, .ag-cta-band__title, .ag-gwengal__cattitle, .ag-about-text h2, .ag-zones-list h2, .ag-devis-aside__title', 'right');
		// KICKERS / TAGS / SOUS-TITRES / LEADS → montent
		tag('.ag-hero-eyebrow, .ag-page-tag, .ag-services-grid-lead, .ag-page-hero-sub, .ag-gwenwhy__lead, .ag-gwengal__lead, .ag-gwengal__catsub, .ag-devis-kicker, .ag-cta-band__lead, .ag-page-intro p, .ag-about-text h3', 'up');
		// BLOCS latéraux
		tag('.ag-devis-wrap, .ag-about-text', 'left');
		tag('.ag-devis-aside', 'right');
		// IMAGES / MÉDIAS → zoom
		tag('.ag-gwengal__cell, .ag-about-photo, .ag-zones-map', 'scale');
		// CARTES (cascade)
		tagStagger('.ag-service-card', 'up');
		tagStagger('.ag-howit-card', 'up');
		tagStagger('.ag-testi-card', 'up');
		tagStagger('.ag-gwenwhy__card', 'up');
		tagStagger('.ag-faq-item', 'up');
		tagStagger('.ag-timeline-item', 'left');
		tagStagger('.ag-gwenstats__it', 'up');
		tagStagger('.ag-gwentrust__it', 'up');
		tagStagger('.ag-devis-benefits li', 'left');

		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (x) {
					if (x.isIntersecting) { x.target.classList.add('is-in'); io.unobserve(x.target); }
				});
			}, { threshold: 0.1, rootMargin: '0px 0px -6% 0px' });
			els.forEach(function (el) { io.observe(el); });
		} else {
			els.forEach(function (el) { el.classList.add('is-in'); });
		}

		// Vague organique en bas des en-têtes de page intérieures.
		document.querySelectorAll('.ag-page-hero').forEach(function (h) {
			if (h.querySelector('.ag-sig-wave')) return;
			var d = document.createElement('div');
			d.className = 'ag-sig-wave';
			d.setAttribute('aria-hidden', 'true');
			d.innerHTML = '<svg viewBox="0 0 1440 70" preserveAspectRatio="none"><path fill="#ffffff" d="M0,35 C240,72 480,5 720,28 C960,50 1200,14 1440,42 L1440,70 L0,70 Z"></path></svg>';
			h.appendChild(d);
		});
	});
})();
