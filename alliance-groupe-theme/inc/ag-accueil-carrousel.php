<?php
/**
 * Accueil : carrousel des offres (Essentiel, Pro, Boutique).
 *
 * Les cartes d'offres de templates/page-accueil.php (.ag-lm__packs) defilent
 * lentement toutes seules, en boucle sans couture. Pause des que la souris est
 * dessus (PC) ou des que le doigt se pose (mobile), reprise un peu apres.
 * Aucun changement dans le gabarit : le style et le script sont poses par ce
 * module, uniquement sur l'accueil.
 *
 * REGLE MULTI-SUPPORTS (voir REGLES-CONCEPTION.md) : une configuration par
 * support, marges respectees, aucun debordement horizontal de la page,
 * mouvement fluide.
 *   PC       >= 1025 px : cartes 380 px, ecart 22 px, 30 px/s
 *   Tablette 601-1024 px : cartes 340 px, ecart 18 px, 26 px/s
 *   Mobile   <= 600 px  : cartes 80 % de l'ecran (max 320 px), ecart 14 px, 22 px/s
 * Tailles : ce fichier (CSS). Vitesses : fonction cfg() du script ci-dessous.
 * Mouvement reduit (prefers-reduced-motion) : pas de defilement, grille d'origine.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_accueil_carrousel_css' ) ) {

	function ag_accueil_carrousel_css() {
		if ( ! is_front_page() ) {
			return;
		}
		?>
<style id="ag-car-css">
/* Le defilement occupe exactement la largeur utile du conteneur (marge negative = son rembourrage de 20 px) : jamais de debordement de la page. Le rembourrage haut et bas laisse la place a l'etoile "Le plus choisi" et aux ombres. */
.ag-lm .ag-lm__packs{--car-w:380px;--car-gap:22px;position:relative;display:flex;align-items:stretch;gap:var(--car-gap);overflow-x:auto;overflow-y:hidden;overscroll-behavior-x:contain;-webkit-overflow-scrolling:touch;scrollbar-width:none;-ms-overflow-style:none;padding:26px 20px 40px;margin:-26px -20px -40px;-webkit-mask-image:linear-gradient(90deg,transparent 0,#000 20px,#000 calc(100% - 20px),transparent 100%);mask-image:linear-gradient(90deg,transparent 0,#000 20px,#000 calc(100% - 20px),transparent 100%)}
.ag-lm .ag-lm__packs::-webkit-scrollbar{display:none}
.ag-lm .ag-lm__packs>.ag-lm__pack{flex:0 0 var(--car-w);width:var(--car-w);box-sizing:border-box}
@media(max-width:1024px){.ag-lm .ag-lm__packs{--car-w:340px;--car-gap:18px}}
@media(max-width:600px){.ag-lm .ag-lm__packs{--car-w:min(80vw,320px);--car-gap:14px}.ag-lm .ag-lm__packs .ag-lm__pcta{padding:15px 12px;font-size:.92rem;white-space:nowrap}}
@media(prefers-reduced-motion:reduce){.ag-lm .ag-lm__packs{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:22px;overflow:visible;padding:0;margin:0;-webkit-mask-image:none;mask-image:none}.ag-lm .ag-lm__packs>.ag-lm__pack{width:auto}}
</style>
		<?php
	}

	function ag_accueil_carrousel_js() {
		if ( ! is_front_page() ) {
			return;
		}
		?>
<script id="ag-car-js">
(function(){
	var box = document.querySelector('.ag-lm__packs');
	if(!box) return;
	box.setAttribute('data-ag-car', ''); box.setAttribute('role', 'region'); box.setAttribute('aria-roledescription', 'carrousel'); box.setAttribute('aria-label', 'Nos offres');
	var cards = [].slice.call(box.children);
	// les cartes ne doivent pas attendre l'animation d'apparition (elles defilent hors ecran)
	cards.forEach(function(c){ c.classList.remove('ag-rv'); c.classList.add('in'); });
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion:reduce)').matches;
	if(reduce || cards.length < 2) return;
	// Une vitesse par support, en pixels par seconde (lent : le temps de lire une carte)
	function cfg(){ var w = window.innerWidth; return w <= 600 ? { speed: 22 } : ( w <= 1024 ? { speed: 26 } : { speed: 30 } ); }
	var HOLD = 3000; // ms de pause apres qu'un doigt se leve ou qu'un geste se termine
	var n = cards.length, setW = 0, m = 1, pos = 0, lastSet = 0;
	var paused = false, visible = true, holdUntil = 0, needSync = false, last = 0, lastW = 0, idle = 0, rz = 0;
	function clear(){ [].slice.call(box.querySelectorAll('.is-clone')).forEach(function(c){ box.removeChild(c); }); }
	function clone(c){
		var k = c.cloneNode(true);
		k.className += ' is-clone'; k.setAttribute('aria-hidden', 'true');
		[].forEach.call(k.querySelectorAll('a,button'), function(a){ a.setAttribute('tabindex', '-1'); });
		return k;
	}
	// Boucle sans couture : des jeux de cartes clones avant et apres, on reste dans le jeu du milieu
	function build(){
		clear();
		var gap = cards[1].offsetLeft - cards[0].offsetLeft - cards[0].offsetWidth;
		setW = cards[n-1].offsetLeft - cards[0].offsetLeft + cards[n-1].offsetWidth + gap;
		m = Math.max(1, Math.ceil(box.clientWidth / setW));
		var i, j, before = document.createDocumentFragment(), after = document.createDocumentFragment();
		for(i = 0; i < m; i++){ for(j = 0; j < n; j++){ before.appendChild(clone(cards[j])); after.appendChild(clone(cards[j])); } }
		box.insertBefore(before, cards[0]);
		box.appendChild(after);
		pos = m * setW; lastSet = pos; box.scrollLeft = pos; lastW = window.innerWidth;
	}
	function wrap(v){
		var lo = m * setW, hi = (m + 1) * setW;
		while(v >= hi) v -= setW;
		while(v < lo) v += setW;
		return v;
	}
	function tick(t){
		requestAnimationFrame(tick);
		var dt = Math.min(t - last, 64) / 1000; last = t;
		if(paused || !visible || t < holdUntil || document.hidden){ needSync = true; return; }
		if(needSync){ pos = wrap(box.scrollLeft); needSync = false; }
		pos = wrap(pos + cfg().speed * dt);
		lastSet = pos; box.scrollLeft = pos;
	}
	function hold(){ holdUntil = performance.now() + HOLD; }
	// Geste de l'utilisateur : on repart d'ou il a laisse, apres une pause
	box.addEventListener('scroll', function(){
		if(Math.abs(box.scrollLeft - lastSet) > 2){
			hold(); needSync = true;
			clearTimeout(idle);
			idle = setTimeout(function(){ var v = wrap(box.scrollLeft); if(Math.abs(v - box.scrollLeft) > 0.5){ box.scrollLeft = v; lastSet = v; } }, 180);
		}
	}, { passive: true });
	// PC : pause tant que la souris est dessus
	box.addEventListener('pointerenter', function(e){ if(e.pointerType !== 'touch'){ paused = true; } });
	box.addEventListener('pointerleave', function(e){ if(e.pointerType !== 'touch'){ paused = false; needSync = true; } });
	// Mobile : pause des que le doigt se pose, reprise un peu apres qu'il se leve
	box.addEventListener('touchstart', function(){ paused = true; }, { passive: true });
	function release(){ paused = false; needSync = true; hold(); }
	box.addEventListener('touchend', release, { passive: true });
	box.addEventListener('touchcancel', release, { passive: true });
	// Clavier : pause quand une carte a le focus au clavier (pas apres un simple toucher ou clic)
	box.addEventListener('focusin', function(e){ var k = false; try{ k = e.target.matches(':focus-visible'); }catch(x){ k = true; } if(k){ paused = true; } });
	box.addEventListener('focusout', function(){ paused = false; needSync = true; });
	if('IntersectionObserver' in window){
		new IntersectionObserver(function(es){ visible = es[0].isIntersecting; }, { threshold: 0 }).observe(box);
	}
	window.addEventListener('resize', function(){
		clearTimeout(rz);
		rz = setTimeout(function(){ if(window.innerWidth !== lastW){ build(); } }, 200);
	});
	build();
	requestAnimationFrame(function(t){ last = t; tick(t); });
})();
</script>
		<?php
	}

	add_action( 'wp_head', 'ag_accueil_carrousel_css', 99 );
	add_action( 'wp_footer', 'ag_accueil_carrousel_js', 99 );
}
