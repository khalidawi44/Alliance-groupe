/**
 * AG Starter Association — Popups Action Populaire.
 *
 * Ouvre les liens Action Populaire (don, adhesion, groupe) dans une
 * fenetre popup centree au lieu de rediriger l'utilisateur. Garde
 * les visiteurs sur le site principal (lfi-nantes-clostoreau.fr).
 *
 * Usage cote HTML :
 *   <a href="https://actionpopulaire.fr/dons/..."
 *      data-ag-popup
 *      data-popup-width="980"
 *      data-popup-height="780">
 *     Faire un don
 *   </a>
 *
 * Fallback : si popup bloque (popup blocker), on ouvre dans le meme
 * onglet. L'utilisateur a quand meme acces a sa destination.
 */
( function () {
	'use strict';

	function openCenteredPopup( url, w, h, name ) {
		w = w || 980;
		h = h || 780;
		name = name || 'ag_asso_popup';
		// Centrer sur l'ecran (multi-monitor friendly)
		var dualScreenLeft = ( window.screenLeft !== undefined ) ? window.screenLeft : window.screenX;
		var dualScreenTop  = ( window.screenTop !== undefined )  ? window.screenTop  : window.screenY;
		var width  = window.innerWidth  || document.documentElement.clientWidth  || screen.width;
		var height = window.innerHeight || document.documentElement.clientHeight || screen.height;
		var left = ( ( width  - w ) / 2 ) + dualScreenLeft;
		var top  = ( ( height - h ) / 2 ) + dualScreenTop;
		var features = 'scrollbars=yes,resizable=yes,toolbar=no,location=yes,status=no,menubar=no'
			+ ',width=' + w + ',height=' + h + ',top=' + top + ',left=' + left;
		var win = window.open( url, name, features );
		if ( ! win || win.closed || typeof win.closed === 'undefined' ) {
			// Popup bloque par le navigateur — fallback : nouvel onglet.
			window.open( url, '_blank', 'noopener,noreferrer' );
			return false;
		}
		try { win.focus(); } catch ( e ) {}
		return true;
	}

	// Expose pour onclick="agAssoPopup('...')"
	window.agAssoPopup = function ( url, w, h ) {
		return openCenteredPopup( url, w, h );
	};

	// Auto-attache aux <a data-ag-popup>
	function attach() {
		var links = document.querySelectorAll( 'a[data-ag-popup]' );
		Array.prototype.forEach.call( links, function ( link ) {
			if ( link.dataset.agPopupBound ) return;
			link.dataset.agPopupBound = '1';
			link.addEventListener( 'click', function ( e ) {
				var url = link.getAttribute( 'href' );
				if ( ! url || url === '#' ) return;
				var w = parseInt( link.dataset.popupWidth, 10 )  || 980;
				var h = parseInt( link.dataset.popupHeight, 10 ) || 780;
				if ( openCenteredPopup( url, w, h ) ) {
					e.preventDefault();
				}
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', attach );
	} else {
		attach();
	}
	// Re-attach apres ajax/contenu dynamique
	document.addEventListener( 'ag-asso-content-loaded', attach );
} )();
