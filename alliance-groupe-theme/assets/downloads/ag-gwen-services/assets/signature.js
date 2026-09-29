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

/* Réservation : le créneau cliqué reste visiblement sélectionné. */
document.addEventListener('click', function (e) {
	var b = e.target.closest && e.target.closest('.ag-resa-slot');
	if (!b || b.disabled) return;
	document.querySelectorAll('.ag-resa-slot.is-picked').forEach(function (x) { x.classList.remove('is-picked'); });
	b.classList.add('is-picked');
});

/* Bandeau de confiance : horaires réels de Gwen.
   Le libellé par défaut (« 7j/7, jour & nuit ») est écrit dans functions.php,
   côté lane CODE. En attendant qu'il y soit corrigé à la source, on remet le
   texte juste ici — c'est ce que voit le visiteur, donc lane DESIGN. */
(function () {
	function corriger() {
		document.querySelectorAll('.ag-pd-trust__item span').forEach(function (s) {
			if (/7\s*j\s*\/\s*7/i.test(s.textContent)) {
				s.textContent = 'Lun–Ven, l’après-midi';
			}
		});
	}
	if (document.readyState !== 'loading') { corriger(); } else { document.addEventListener('DOMContentLoaded', corriger); }
	setTimeout(corriger, 400);
	setTimeout(corriger, 1500);
})();

/* Émojis retirés des libellés (boutons, coordonnées) : ils vivent dans le PHP
   côté lane CODE, on les enlève à l'affichage — demande de Fabrice, 29/09. */
(function () {
	var SEL = '.ag-btn-pro, .ag-btn, .ag-gwenwhy__btn, .ag-footer-cta, .ag-zones-list strong, .ag-zones-contact-card strong, .ag-devis-callcard__tx strong, .ag-page-tag';
	var EMOJI = /[\u{1F300}-\u{1FAFF}\u{1F004}\u{1F0CF}\u{2600}-\u{26FF}\u{2728}\u{2705}\u{274C}\u{2B50}\u{2764}\u{FE0F}\u{200D}]/gu;
	function nettoyer() {
		document.querySelectorAll(SEL).forEach(function (el) {
			Array.prototype.forEach.call(el.childNodes, function (n) {
				if (n.nodeType !== 3) { return; }
				EMOJI.lastIndex = 0;
				var propre = n.nodeValue.replace(EMOJI, '').replace(/\s{2,}/g, ' ').replace(/^\s+/, '');
				if (propre !== n.nodeValue) { n.nodeValue = propre; }
			});
		});
	}
	if (document.readyState !== 'loading') { nettoyer(); } else { document.addEventListener('DOMContentLoaded', nettoyer); }
	setTimeout(nettoyer, 500);
})();
