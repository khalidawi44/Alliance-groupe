/**
 * AG Starter Association — animations légères via anime.js
 *
 * Trois effets uniquement, opt-in via Customizer (ag_asso_enable_animations) :
 *   1. Stats counters anim (0 → valeur cible quand scroll into view)
 *   2. Sections reveal (fade-up subtle au scroll)
 *   3. CTA hero pulse (subtil, attire l'œil)
 *
 * Respecte prefers-reduced-motion (animations OFF auto pour utilisateurs
 * sensibles, vestibulaires, ou config système). Charge anime.js depuis
 * CDN (16 KB gzip), aucun impact perf cumulé.
 */
( function () {
	'use strict';
	if ( typeof anime === 'undefined' ) return;

	// Honneur l'accessibilité : si l'utilisateur préfère pas de mouvement, on skip TOUT.
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( reduceMotion ) return;

	// ─── Helper : observer pour déclencher au scroll ─────────────
	function onceVisible( els, cb, threshold ) {
		threshold = threshold || 0.2;
		if ( ! ( 'IntersectionObserver' in window ) ) {
			els.forEach( cb );
			return;
		}
		var io = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					cb( entry.target );
					obs.unobserve( entry.target );
				}
			} );
		}, { threshold: threshold } );
		els.forEach( function ( el ) { io.observe( el ); } );
	}

	// ─── 1. Counters animés (47, 2 130, 12 480, signataires hero) ─
	function animateCounters() {
		var counters = document.querySelectorAll( '.ag-asso-stats strong, .ag-asso-hero__counter strong' );
		if ( ! counters.length ) return;
		onceVisible( Array.prototype.slice.call( counters ), function ( el ) {
			var raw = el.textContent.replace( /\s| /g, '' );
			var target = parseInt( raw, 10 );
			if ( isNaN( target ) || target <= 0 ) return;
			var prefix = el.textContent.match( /^[^\d]*/ )[ 0 ] || '';
			var obj = { v: 0 };
			anime( {
				targets: obj,
				v: target,
				round: 1,
				duration: Math.min( 1800, 800 + target * 0.05 ),
				easing: 'easeOutCubic',
				update: function () {
					// Format avec espace comme séparateur de milliers (style FR)
					el.textContent = prefix + obj.v.toString().replace( /\B(?=(\d{3})+(?!\d))/g, ' ' );
				}
			} );
		} );
	}

	// ─── 2. Sections : fade-up au scroll ──────────────────────────
	function revealSections() {
		var sections = document.querySelectorAll( '.ag-asso-section, .ag-asso-parallax' );
		if ( ! sections.length ) return;
		// Pré-état : invisible + décalé.
		sections.forEach( function ( s ) {
			s.style.opacity = '0';
			s.style.transform = 'translateY(28px)';
			s.style.transition = 'none';
		} );
		onceVisible( Array.prototype.slice.call( sections ), function ( el ) {
			anime( {
				targets: el,
				opacity: [ 0, 1 ],
				translateY: [ 28, 0 ],
				duration: 700,
				easing: 'easeOutQuad'
			} );
		}, 0.12 );
	}

	// ─── 3. CTA hero : pulse subtil ───────────────────────────────
	function pulseHeroCta() {
		var cta = document.querySelector( '.ag-asso-hero .ag-asso-btn--primary' );
		if ( ! cta ) return;
		anime( {
			targets: cta,
			scale: [ 1, 1.045, 1 ],
			duration: 2400,
			loop: true,
			delay: 1500,
			easing: 'easeInOutSine'
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
	function init() {
		animateCounters();
		revealSections();
		pulseHeroCta();
	}
} )();
