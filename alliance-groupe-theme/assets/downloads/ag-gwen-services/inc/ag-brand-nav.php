<?php
/**
 * ag-brand-nav.php — Favicon + navigation (correctifs SANS toucher au style.css.
 *
 * Pourquoi un module a part : la lane DESIGN (Cowork) edite style.css. Pour ne
 * pas ecraser son travail, ces correctifs fonctionnels sont injectes ici, en
 * surcharge (priorite tardive), et restent faciles a retirer. Trois choses :
 *
 *  1. FAVICON : le site n'en avait pas. On sert l'icone livree avec le theme
 *     (assets/favicon.ico), sauf si un « site icon » WordPress est deja defini.
 *  2. HAMBURGER A TOUTES LES TAILLES (comme Alliance) : le menu debordait sur
 *     grand ecran. On le passe en panneau plein ecran, ouvert par le burger,
 *     a toutes les largeurs.
 *  3. CONTRASTE AU SCROLL : l'en-tete devenait blanc sur blanc. On lui donne un
 *     fond clair et un texte sombre des qu'on defile (classe .scrolled).
 *
 * Le style fin (teintes exactes, animations) reste a la lane DESIGN.
 *
 * @package ag-gwen-services
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── 1. Favicon ──────────────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) { return; } // respecte un icone deja regle
	$ico = get_theme_file_uri( 'assets/favicon.ico' );
	$ap  = get_theme_file_uri( 'assets/apple-touch-icon.png' );
	echo '<link rel="icon" href="' . esc_url( $ico ) . '" sizes="any">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $ap ) . '">' . "\n";
}, 5 );

/* ── 2 & 3. Surcharge nav + contraste (CSS injecte apres le theme) ───── */
add_action( 'wp_head', function () {
	?>
<style id="ag-brand-nav">
/* Burger visible a TOUTES les tailles (comme Alliance) */
.ag-navtoggle{display:flex!important;flex-direction:column;justify-content:center;gap:5px;width:44px;height:44px;background:transparent;border:0;cursor:pointer;position:relative;z-index:10001;padding:0}
.ag-navtoggle span{display:block;width:26px;height:3px;border-radius:2px;background:#fff;margin:0 auto;transition:transform .25s ease,opacity .2s ease,background .25s ease;box-shadow:0 0 2px rgba(0,0,0,.25)}
/* Menu = panneau plein ecran, cache par defaut, ouvert via .nav-open */
.ag-primary-nav{position:fixed;inset:0;background:rgba(13,13,16,.97);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transform:translateY(-6px);transition:opacity .25s ease,transform .25s ease,visibility .25s ease;z-index:10000}
.ag-site-header.nav-open .ag-primary-nav{opacity:1;visibility:visible;transform:none}
.ag-primary-nav .ag-primary-menu{flex-direction:column;align-items:center;gap:1.5rem;list-style:none;margin:0;padding:0}
.ag-primary-nav .ag-primary-menu a{color:#fff!important;font-size:1.35rem;font-weight:600}
.ag-primary-nav .ag-primary-menu a:hover{color:var(--ag-color-accent,#F37A1F)!important}
/* Burger -> croix quand ouvert */
.ag-site-header.nav-open .ag-navtoggle span{background:#fff}
.ag-site-header.nav-open .ag-navtoggle span:nth-child(1){transform:translateY(8px) rotate(45deg)}
.ag-site-header.nav-open .ag-navtoggle span:nth-child(2){opacity:0}
.ag-site-header.nav-open .ag-navtoggle span:nth-child(3){transform:translateY(-8px) rotate(-45deg)}
/* Contraste : en-tete lisible au scroll (plus de blanc sur blanc) */
.ag-site-header{transition:background .25s ease}
.ag-site-header.scrolled{background:#fff;box-shadow:0 2px 14px rgba(0,0,0,.10)}
.ag-site-header.scrolled .ag-navtoggle span{background:#222;box-shadow:none}
.ag-site-header.scrolled.nav-open .ag-navtoggle span{background:#fff}
.ag-site-header.scrolled .ag-site-brand,.ag-site-header.scrolled .ag-site-brand__text{color:#222}
</style>
	<?php
}, 99 );

/* ── Le declencheur .scrolled ────────────────────────────────────────── */
add_action( 'wp_footer', function () {
	?>
<script>
(function(){
	var h=document.querySelector('.ag-site-header'); if(!h) return;
	function o(){ h.classList.toggle('scrolled', window.scrollY>40); }
	o(); window.addEventListener('scroll', o, {passive:true});
})();
</script>
	<?php
}, 99 );
