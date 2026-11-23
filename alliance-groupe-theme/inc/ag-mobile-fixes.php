<?php
/**
 * Corrections mobile : telephone de l'en-tete, commandes natives de la video,
 * format de la video du hero.
 *
 * Demande de Fabrice, 2026-11-23 (capture iPhone) :
 *  1. Le bouton telephone de l'en-tete passait sous le bouton menu : masque
 *     jusqu'a 1024 px (le numero reste dans le menu et le pied de page).
 *  2. Sur iPhone, les commandes natives de lecture (pause, 10 s, barre de temps)
 *     s'affichaient sur la video du hero : rendues invisibles et intouchables.
 *  3. La video du hero est un film 16:9 (864 x 496) etire en plein ecran
 *     portrait : on ne voyait qu'un bout du lion. En portrait jusqu'a 900 px,
 *     la video occupe le haut du hero (58 % de la hauteur), sans zoom
 *     cinema, et se fond dans le noir du site sous le texte.
 *
 * Regle multi-supports (REGLES-CONCEPTION.md) : valeurs par support.
 *   Telephone et tablette portrait (<= 900 px) : video 58 % de la hauteur.
 *   Paysage et PC : hero d'origine, inchange.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_mobile_fixes_css' ) ) {

	function ag_mobile_fixes_css() {
		?>
<style id="ag-mobile-fixes">
/* 2. Commandes natives de lecture : jamais visibles, jamais touchables. */
.ag-lm .ag-lm__hbg,.ag-lm .ag-lm__hbg video{pointer-events:none}
video::-webkit-media-controls,
video::-webkit-media-controls-enclosure,
video::-webkit-media-controls-panel,
video::-webkit-media-controls-overlay-play-button,
video::-webkit-media-controls-start-playback-button,
video::-webkit-media-controls-play-button,
video::-webkit-media-controls-timeline,
video::-webkit-media-controls-current-time-display,
video::-webkit-media-controls-time-remaining-display{display:none !important;opacity:0 !important;-webkit-appearance:none !important}
/* 1. Telephone de l'en-tete masque (il passait sous le bouton menu). */
@media(max-width:1024px){.ag-nav .ag-nav__tel,.ag-nav__tel{display:none !important}}
/* 3. Video du hero au format telephone et tablette portrait. */
@media(max-width:900px) and (orientation:portrait){
	.ag-lm .ag-lm__hbg{bottom:auto;height:58vh;height:58svh}
	.ag-lm .ag-lm__hbg::after{content:"";position:absolute;left:0;right:0;bottom:0;height:46%;background:linear-gradient(180deg,rgba(5,5,10,0),#05050a 96%);z-index:2;pointer-events:none}
	.ag-lm .ag-lm__slide video{object-position:36% 50%;animation:none;transform:none}
	.ag-lm .ag-lm__sgrad{background:linear-gradient(180deg,rgba(5,5,10,.55),rgba(5,5,10,0) 22%)}
}
/* Tablette portrait : texte plus bas, la video descend pour ne pas laisser de bande noire. */
@media(min-width:601px) and (max-width:900px) and (orientation:portrait){
	.ag-lm .ag-lm__hbg{height:70vh;height:70svh}
}
</style>
		<?php
	}

	function ag_mobile_fixes_js() {
		?>
<script id="ag-mobile-fixes-js">
(function(){
	[].forEach.call(document.querySelectorAll('.ag-lm__hbg video'), function(v){
		v.controls = false; v.removeAttribute('controls');
		v.setAttribute('disablepictureinpicture', ''); v.setAttribute('disableremoteplayback', '');
		v.setAttribute('controlslist', 'nodownload nofullscreen noremoteplayback');
		v.setAttribute('x-webkit-airplay', 'deny'); v.setAttribute('tabindex', '-1');
	});
})();
</script>
		<?php
	}

	add_action( 'wp_head', 'ag_mobile_fixes_css', 99 );
	add_action( 'wp_footer', 'ag_mobile_fixes_js', 99 );
}
