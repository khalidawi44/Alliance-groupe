/**
 * AG Immersive — couche "jeu video / film" (module isole, desactivable).
 *
 *   1. Intro cinematique (1x / session) : revelation de la marque.
 *   2. Transitions de page (fondu au clic sur liens internes).
 *   3. Barre de chargement en haut pendant les transitions.
 *   4. Boutons magnetiques (desktop) : le bouton suit legerement le curseur.
 *   5. Sons d'interface (WebAudio synthetise, AUCUN fichier) avec bouton
 *      on/off, OFF par defaut, memorise (localStorage).
 *   (Grain + vignette + letterbox = CSS pur.)
 *
 * Garde-fous : prefers-reduced-motion -> tout off. Filets de securite pour
 * ne jamais bloquer la page (timeouts). Liens externes / _blank / # / fichiers
 * jamais interceptes.
 */
(function () {
	'use strict';
	if ( typeof window === 'undefined' ) return;
	var REDUCED  = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var FINE     = window.matchMedia && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
	var IS_TOUCH = ( 'ontouchstart' in window ) || navigator.maxTouchPoints > 0;

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) fn();
		else document.addEventListener( 'DOMContentLoaded', fn );
	}

	// =====================================================================
	// 1. INTRO CINEMATIQUE (1x / session)
	// =====================================================================
	function initIntro() {
		if ( REDUCED ) return;
		try { if ( sessionStorage.getItem( 'ag_intro' ) ) return; sessionStorage.setItem( 'ag_intro', '1' ); } catch ( e ) {}
		var el = document.createElement( 'div' );
		el.className = 'ag-intro';
		el.innerHTML = '<div class="ag-intro__inner"><span class="ag-intro__brand">Alliance Groupe</span><span class="ag-intro__bar"><i></i></span></div>';
		document.body.appendChild( el );
		document.documentElement.classList.add( 'ag-intro-lock' );
		function done() {
			el.classList.add( 'is-out' );
			document.documentElement.classList.remove( 'ag-intro-lock' );
			setTimeout( function () { if ( el && el.parentNode ) el.parentNode.removeChild( el ); }, 800 );
		}
		// se retire au load, avec un mini delai pour laisser jouer l'anim ; filet a 2.6s
		var fired = false;
		var go = function () { if ( fired ) return; fired = true; setTimeout( done, 600 ); };
		window.addEventListener( 'load', go );
		setTimeout( go, 2600 );
	}

	// =====================================================================
	// 2/3. TRANSITIONS DE PAGE + BARRE DE CHARGEMENT
	// =====================================================================
	function initPageTransitions() {
		if ( REDUCED ) return;
		var cover = document.createElement( 'div' );
		cover.className = 'ag-trans';
		cover.innerHTML = '<span class="ag-trans__bar"></span>';
		document.body.appendChild( cover );

		// fondu d'entree (depuis le noir) au chargement + filets de securite
		requestAnimationFrame( function () { cover.classList.add( 'is-ready' ); } );
		setTimeout( function () { cover.classList.add( 'is-ready' ); }, 120 );
		window.addEventListener( 'load', function () { cover.classList.add( 'is-ready' ); } );
		// gere le retour arriere (bfcache)
		window.addEventListener( 'pageshow', function ( e ) { if ( e.persisted ) cover.classList.remove( 'is-active' ), cover.classList.add( 'is-ready' ); } );

		document.addEventListener( 'click', function ( e ) {
			if ( e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ) return;
			var a = e.target.closest( 'a' );
			if ( ! a ) return;
			var href = a.getAttribute( 'href' ) || '';
			if ( ! href || href.charAt( 0 ) === '#' ) return;                 // ancres : gerees ailleurs
			if ( a.target === '_blank' || a.hasAttribute( 'download' ) ) return;
			if ( /^(mailto:|tel:|javascript:)/i.test( href ) ) return;
			var url;
			try { url = new URL( a.href, window.location.href ); } catch ( err ) { return; }
			if ( url.origin !== window.location.origin ) return;              // liens externes (Stripe...) : nav normale
			if ( url.pathname === window.location.pathname && url.hash ) return; // meme page + ancre
			e.preventDefault();
			cover.classList.remove( 'is-ready' );
			cover.classList.add( 'is-active' );
			setTimeout( function () { window.location.href = a.href; }, 380 );
			setTimeout( function () { cover.classList.remove( 'is-active' ); }, 4000 ); // filet
		} );
	}

	// =====================================================================
	// 4. BOUTONS MAGNETIQUES (desktop)
	// =====================================================================
	function initMagnetic() {
		if ( REDUCED || IS_TOUCH || ! FINE ) return;
		var sel = '.ag-btn-gold, .ag-btn-outline, .ag-fsm-toggle';
		document.querySelectorAll( sel ).forEach( function ( btn ) {
			btn.style.willChange = 'transform';
			btn.addEventListener( 'mousemove', function ( e ) {
				var r = btn.getBoundingClientRect();
				var x = ( e.clientX - r.left - r.width / 2 ) * 0.25;
				var y = ( e.clientY - r.top - r.height / 2 ) * 0.35;
				btn.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px)';
			} );
			btn.addEventListener( 'mouseleave', function () { btn.style.transform = ''; } );
		} );
	}

	// =====================================================================
	// 5. SONS D'INTERFACE (WebAudio, OFF par defaut)
	// =====================================================================
	function initSound() {
		if ( IS_TOUCH ) return; // pas de son sur tactile
		var on = false;
		try { on = localStorage.getItem( 'ag_sound' ) === '1'; } catch ( e ) {}
		var ctx = null;
		function ac() { if ( ! ctx ) { var C = window.AudioContext || window.webkitAudioContext; if ( C ) ctx = new C(); } return ctx; }
		function blip( freq, dur, vol ) {
			if ( ! on ) return;
			var c = ac(); if ( ! c ) return;
			if ( c.state === 'suspended' ) c.resume();
			var o = c.createOscillator(), g = c.createGain();
			o.type = 'sine'; o.frequency.value = freq;
			g.gain.value = vol || 0.04;
			o.connect( g ); g.connect( c.destination );
			var t = c.currentTime;
			g.gain.setValueAtTime( g.gain.value, t );
			g.gain.exponentialRampToValueAtTime( 0.0001, t + ( dur || 0.08 ) );
			o.start( t ); o.stop( t + ( dur || 0.08 ) );
		}
		// toggle UI
		var btn = document.createElement( 'button' );
		btn.className = 'ag-sound-toggle';
		btn.type = 'button';
		btn.setAttribute( 'aria-label', 'Activer / couper les sons' );
		function paint() { btn.innerHTML = on ? '🔊' : '🔇'; btn.classList.toggle( 'is-on', on ); }
		paint();
		btn.addEventListener( 'click', function () {
			on = ! on;
			try { localStorage.setItem( 'ag_sound', on ? '1' : '0' ); } catch ( e ) {}
			paint();
			if ( on ) blip( 660, 0.09 );
		} );
		document.body.appendChild( btn );
		// sons sur interactions
		document.addEventListener( 'click', function ( e ) { if ( e.target.closest( 'a, button, [role="button"]' ) && e.target !== btn && ! btn.contains( e.target ) ) blip( 520, 0.07 ); } );
		var hoverSel = 'a, button, [role="button"], .ag-scard, .ag-xpress__card';
		document.addEventListener( 'mouseover', function ( e ) { if ( e.target.closest && e.target.closest( hoverSel ) ) blip( 880, 0.04, 0.02 ); } );
	}

	// =====================================================================
	ready( function () {
		initIntro();
		initPageTransitions();
		initMagnetic();
		initSound();
	} );
})();
