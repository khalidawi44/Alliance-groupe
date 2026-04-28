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
	function honorairesUrl() {
		return (typeof agBusinessData !== 'undefined' && agBusinessData.honorairesUrl)
			? agBusinessData.honorairesUrl
			: '/honoraires/';
	}

	/* ── Compteurs : count-up animation au scroll-in ────────────────
	   Les compteurs sont rendus en dur par pro-features.php (Business)
	   avec des valeurs comme "15+", "500+", "98%", "24/7". On les
	   detecte, parse le nombre + le suffixe, et on anime de 0 -> target
	   quand l'element entre dans le viewport. */
	function animateCounters() {
		if (!isBusinessActive()) return;
		var nodes = document.querySelectorAll('.ag-counter__number');
		if (!nodes.length) return;
		if (typeof IntersectionObserver === 'undefined') return;

		var animate = function (el) {
			var raw = (el.textContent || '').trim();
			var m = raw.match(/^(\d+)(.*)$/);
			if (!m) return; // ex: "24/7" — laisse statique
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

	/* ── Cartes Honoraires : cliquables vers la page dediee ─────────
	   Sur la home, .ag-honoraires__card est un <div> non interactif.
	   On le rend cliquable (curseur, navigation, accessible clavier). */
	function makeHonorairesClickable() {
		if (!isBusinessActive()) return;
		var url = honorairesUrl();
		var cards = document.querySelectorAll('.ag-honoraires .ag-honoraires__card');
		cards.forEach(function (card) {
			if (card.dataset.agBusinessClickable === '1') return;
			card.dataset.agBusinessClickable = '1';
			card.style.cursor = 'pointer';
			card.setAttribute('role', 'link');
			card.setAttribute('tabindex', '0');
			card.setAttribute('aria-label', 'Voir les details des honoraires');
			card.addEventListener('click', function (e) {
				if (e.target.closest('a')) return; // si lien interne, laisse-le
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

	function run() {
		animateCounters();
		makeHonorairesClickable();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
