/* AG Premium Domicile — interactions premium (design only). */
(function () {
	'use strict';

	var D = (window.agPdData || {});
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function el(tag, cls, html) {
		var e = document.createElement(tag);
		if (cls) e.className = cls;
		if (html != null) e.innerHTML = html;
		return e;
	}

	var CHECK = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="#ffe9c2" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	var SPARK = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3v18M3 12h18" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg>';

	document.addEventListener('DOMContentLoaded', function () {

		/* 1) Bandeau de confiance dans le hero */
		if (D.showTrust !== false && Array.isArray(D.trust)) {
			var heroInner = document.querySelector('.ag-hero-pro .ag-container');
			if (heroInner && !heroInner.querySelector('.ag-pd-trust')) {
				var bar = el('div', 'ag-pd-trust');
				D.trust.forEach(function (t) {
					if (!t) return;
					var item = el('span', 'ag-pd-trust__item', CHECK + '<span>' + t + '</span>');
					bar.appendChild(item);
				});
				if (bar.children.length) heroInner.appendChild(bar);
			}
		}

		/* 2) Bouton "Devis" flottant */
		if (D.showFloat !== false && D.devisUrl) {
			var floatBtn = el('a', 'ag-pd-float', SPARK + '<span>' + (D.devisLabel || 'Devis gratuit') + '</span>');
			floatBtn.href = D.devisUrl;
			floatBtn.setAttribute('aria-label', D.devisLabel || 'Devis gratuit');
			document.body.appendChild(floatBtn);
			var reveal = function () {
				if (window.scrollY > 320) floatBtn.classList.add('ag-pd-in');
				else floatBtn.classList.remove('ag-pd-in');
			};
			window.addEventListener('scroll', reveal, { passive: true });
			reveal();
		}

		/* 3) Header collant raffiné */
		var header = document.querySelector('.ag-site-header');
		if (header) {
			var onScroll = function () {
				if (window.scrollY > 40) header.classList.add('ag-pd-stuck');
				else header.classList.remove('ag-pd-stuck');
			};
			window.addEventListener('scroll', onScroll, { passive: true });
			onScroll();
		}

		/* 4) Rotation douce de la mise en avant des témoignages */
		if (!reduce) {
			var cards = Array.prototype.slice.call(document.querySelectorAll('.ag-testi-card'));
			if (cards.length > 1) {
				var i = 0;
				setInterval(function () {
					cards.forEach(function (c) { c.classList.remove('ag-pd-hi'); });
					cards[i % cards.length].classList.add('ag-pd-hi');
					i++;
				}, 2600);
			}
		}
	});
})();
