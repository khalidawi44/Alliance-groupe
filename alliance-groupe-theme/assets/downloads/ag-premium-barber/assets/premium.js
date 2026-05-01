/**
 * AG Premium Barber — JS runtime.
 *
 * Active uniquement le toggle du menu burger : intercepte le clic
 * sur .ag-header__cta (qui est devenu un hamburger via CSS) pour
 * ouvrir un overlay menu plein-ecran au lieu de naviguer vers
 * /?ag_queue=join. Click hors menu ferme.
 */
(function () {
	'use strict';

	function isActive() {
		return document.body.classList.contains('ag-bb-active');
	}

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
		if (!isActive()) return;
		var cta = document.querySelector('.ag-header__nav > .ag-header__cta');
		if (!cta) return;
		if (cta.dataset.bbBurger === '1') return; // deja fait
		cta.dataset.bbBurger = '1';

		// Recolte les liens existants du nav (hors cta lui-meme)
		var links = [];
		document.querySelectorAll('.ag-header__nav > a:not(.ag-header__cta)').forEach(function (a) {
			links.push({ href: a.href, label: a.textContent.trim() });
		});

		var ticketUrl = cta.href;
		var built = buildOverlayMenu(links, ticketUrl);
		document.body.appendChild(built.overlay);

		function open() {
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
		// Si un lien interne est clique, on ferme l'overlay
		built.overlay.querySelectorAll('a').forEach(function (a) {
			a.addEventListener('click', function () { close(); });
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupBurger);
	} else {
		setupBurger();
	}
})();
