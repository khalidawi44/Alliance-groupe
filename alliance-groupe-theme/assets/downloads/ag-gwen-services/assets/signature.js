/* AG Gwen — signature.js : révélations au scroll (magazine).
   Ajoute .is-in aux éléments quand ils entrent dans le viewport. */
(function () {
	function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
	ready(function () {
		var sel = [
			'.ag-services-grid-header', '.ag-service-card', '.ag-howit-card', '.ag-testi-card',
			'.ag-faq-item', '.ag-gwenwhy__head', '.ag-gwenwhy__card', '.ag-gwengal__head',
			'.ag-gwengal__cat', '.ag-gwenstats', '.ag-gwentrust', '.ag-cta-band', '.ag-devis-wrap'
		];
		var els = [];
		sel.forEach(function (s) {
			document.querySelectorAll(s).forEach(function (el) {
				if (!el.hasAttribute('data-sig-reveal')) el.setAttribute('data-sig-reveal', '');
				els.push(el);
			});
		});
		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (x) {
					if (x.isIntersecting) { x.target.classList.add('is-in'); io.unobserve(x.target); }
				});
			}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
			els.forEach(function (el) { io.observe(el); });
		} else {
			els.forEach(function (el) { el.classList.add('is-in'); });
		}
	});
})();
