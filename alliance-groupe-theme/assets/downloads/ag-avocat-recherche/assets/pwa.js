/* AG Recherche Juridique — PWA : installation + service worker. */
(function () {
	'use strict';
	if (!window.AGJR || !AGJR.pwa) { return; }

	/* Enregistre le service worker (mode hors-ligne + app installable). */
	if ('serviceWorker' in navigator) {
		window.addEventListener('load', function () {
			navigator.serviceWorker.register(AGJR.pwa.sw, { scope: AGJR.pwa.scope }).catch(function () {});
		});
	}

	var btn = document.getElementById('ag-jr-install');
	if (!btn) { return; }

	var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
	if (standalone) { btn.hidden = true; return; } // déjà installée

	var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
	var deferred = null;

	window.addEventListener('beforeinstallprompt', function (e) {
		e.preventDefault();
		deferred = e;
		btn.hidden = false;
	});
	window.addEventListener('appinstalled', function () { btn.hidden = true; });

	// iOS ne déclenche pas beforeinstallprompt : on montre le bouton + une notice.
	if (isIOS) { btn.hidden = false; }

	btn.addEventListener('click', function () {
		if (deferred) {
			deferred.prompt();
			deferred.userChoice.finally(function () { deferred = null; btn.hidden = true; });
			return;
		}
		showHelp();
	});

	function showHelp() {
		var id = 'ag-jr-install-help';
		if (document.getElementById(id)) { document.getElementById(id).scrollIntoView({ behavior: 'smooth' }); return; }
		var box = document.createElement('div');
		box.id = id;
		box.className = 'ag-jr-note';
		box.style.marginTop = '12px';
		if (isIOS) {
			box.innerHTML = '📲 <strong>Installer sur iPhone/iPad :</strong> appuyez sur le bouton <strong>Partager</strong> de Safari (le carré avec une flèche), puis choisissez <strong>« Sur l’écran d’accueil »</strong>.';
		} else {
			box.innerHTML = '📲 <strong>Installer :</strong> ouvrez le menu de votre navigateur (⋮) puis <strong>« Installer l’application »</strong> / <strong>« Ajouter à l’écran d’accueil »</strong>.';
		}
		var head = document.querySelector('.ag-jr-front-head') || document.querySelector('.ag-jr-front');
		if (head) { head.parentNode.insertBefore(box, head.nextSibling); box.scrollIntoView({ behavior: 'smooth' }); }
	}
})();
