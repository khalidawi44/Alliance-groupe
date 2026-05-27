<?php
/**
 * Hero tech grid — overlay parallax high-tech qui réagit à la souris.
 *
 * Architecture :
 *   - Plan de fond : grille perspective (lignes champagne + lignes
 *     horizontales façon HUD)
 *   - 2 layers parallax : grid 3D + halo qui suivent la souris à des
 *     vitesses différentes
 *   - Scanline horizontale qui descend en boucle
 *   - 3 dots flottants animés
 *
 * Inclus dans le hero d'accueil (par-dessus le mesh-gradient WebGL).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$uid = 'agtg-' . wp_rand( 1000, 9999 );
?>

<div class="ag-techgrid" id="<?php echo esc_attr( $uid ); ?>" aria-hidden="true">
	<!-- Plan grille perspective -->
	<div class="ag-techgrid__plane"></div>
	<!-- Halo qui suit la souris -->
	<div class="ag-techgrid__cursor-halo"></div>
	<!-- Scanline -->
	<div class="ag-techgrid__scanline"></div>
	<!-- Dots flottants -->
	<div class="ag-techgrid__dots">
		<span></span><span></span><span></span><span></span><span></span>
	</div>
	<!-- Grille HUD (vertical/horizontal lignes plus marquées) -->
	<div class="ag-techgrid__hud"></div>
</div>

<style>
#<?php echo esc_attr( $uid ); ?>.ag-techgrid{
	position:absolute; inset:0;
	pointer-events:none;
	z-index:1;
	overflow:hidden;
	perspective:600px;
	perspective-origin: 50% 50%;
	--mx: 50%; --my: 50%;
	--pmx: 0; --pmy: 0;
}
/* Plan perspective avec gridlines */
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__plane{
	position:absolute;
	left:-25%; right:-25%; bottom:-25%;
	height:120%;
	background-image:
		linear-gradient(to right, rgba(212,180,92,.12) 1px, transparent 1px),
		linear-gradient(to bottom, rgba(212,180,92,.10) 1px, transparent 1px);
	background-size: 80px 80px;
	transform: rotateX(60deg) translateY(20%) translateZ(calc(var(--pmy) * 20px));
	transform-origin: 50% 100%;
	mask-image: linear-gradient(to top, #000 0%, rgba(0,0,0,.6) 50%, transparent 100%);
	-webkit-mask-image: linear-gradient(to top, #000 0%, rgba(0,0,0,.6) 50%, transparent 100%);
	animation: agTGScroll 18s linear infinite;
	opacity: .65;
}
@keyframes agTGScroll {
	from { background-position: 0 0; }
	to   { background-position: 0 80px; }
}
/* Halo qui suit la souris */
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__cursor-halo{
	position:absolute;
	left: var(--mx); top: var(--my);
	width: 700px; height: 700px;
	transform: translate(-50%, -50%);
	background: radial-gradient(circle, rgba(212,180,92,.18) 0%, rgba(243,122,31,.08) 30%, transparent 65%);
	filter: blur(20px);
	opacity: .7;
	transition: opacity .4s ease;
}
/* Scanline horizontale */
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__scanline{
	position:absolute;
	left:0; right:0; height:2px;
	top: 0;
	background: linear-gradient(90deg, transparent 0%, rgba(212,180,92,.6) 30%, rgba(243,122,31,.7) 50%, rgba(212,180,92,.6) 70%, transparent 100%);
	box-shadow: 0 0 16px rgba(212,180,92,.6);
	animation: agTGScan 6s linear infinite;
	opacity: .8;
}
@keyframes agTGScan { from{ top: -2px; } to{ top: 100%; } }
/* Dots flottants */
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots{ position:absolute; inset:0; }
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span{
	position:absolute; width:4px; height:4px; border-radius:50%;
	background:#D4B45C;
	box-shadow: 0 0 10px #D4B45C, 0 0 20px rgba(212,180,92,.5);
	animation: agTGDot var(--d,8s) ease-in-out infinite;
}
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span:nth-child(1){ top: 20%; left: 15%; --d: 7s;  animation-delay: 0s; }
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span:nth-child(2){ top: 40%; left: 88%; --d: 9s;  animation-delay: 1.5s; }
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span:nth-child(3){ top: 70%; left: 30%; --d: 11s; animation-delay: 3s; }
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span:nth-child(4){ top: 25%; left: 65%; --d: 8s;  animation-delay: 0.8s; }
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span:nth-child(5){ top: 80%; left: 75%; --d: 10s; animation-delay: 2.2s; }
@keyframes agTGDot {
	0%, 100% { transform: translate(0, 0) scale(1); opacity: .7; }
	33%      { transform: translate(60px, -40px) scale(1.4); opacity: 1; }
	66%      { transform: translate(-40px, 30px) scale(.8); opacity: .5; }
}
/* HUD avec corner brackets */
#<?php echo esc_attr( $uid ); ?> .ag-techgrid__hud{
	position:absolute; inset:24px;
	pointer-events:none;
	background:
		linear-gradient(to right, rgba(212,180,92,.3) 28px, transparent 28px) top left/30px 1px no-repeat,
		linear-gradient(to bottom, rgba(212,180,92,.3) 28px, transparent 28px) top left/1px 30px no-repeat,
		linear-gradient(to left,  rgba(212,180,92,.3) 28px, transparent 28px) top right/30px 1px no-repeat,
		linear-gradient(to bottom, rgba(212,180,92,.3) 28px, transparent 28px) top right/1px 30px no-repeat,
		linear-gradient(to right, rgba(212,180,92,.3) 28px, transparent 28px) bottom left/30px 1px no-repeat,
		linear-gradient(to top,   rgba(212,180,92,.3) 28px, transparent 28px) bottom left/1px 30px no-repeat,
		linear-gradient(to left,  rgba(212,180,92,.3) 28px, transparent 28px) bottom right/30px 1px no-repeat,
		linear-gradient(to top,   rgba(212,180,92,.3) 28px, transparent 28px) bottom right/1px 30px no-repeat;
}

@media (prefers-reduced-motion: reduce){
	#<?php echo esc_attr( $uid ); ?> .ag-techgrid__plane,
	#<?php echo esc_attr( $uid ); ?> .ag-techgrid__scanline,
	#<?php echo esc_attr( $uid ); ?> .ag-techgrid__dots span{ animation: none !important; }
}
</style>

<script>
(function(){
	var el = document.getElementById('<?php echo esc_js( $uid ); ?>');
	if (!el) return;
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	// Skip mobile + low-end pour fluidité (animations CSS coûteuses)
	if (window.innerWidth < 1024) { el.style.display='none'; return; }
	if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) { el.style.display='none'; return; }
	var hero = el.closest('.ag-hero, section') || el.parentElement;
	if (!hero) return;
	var rect, raf=null, mx=50, my=50, tx=50, ty=50;
	function update(){
		mx += (tx - mx) * 0.08;
		my += (ty - my) * 0.08;
		el.style.setProperty('--mx', mx + '%');
		el.style.setProperty('--my', my + '%');
		el.style.setProperty('--pmx', ((mx-50)/50).toFixed(3));
		el.style.setProperty('--pmy', ((my-50)/50).toFixed(3));
		raf = (Math.abs(tx-mx) > .05 || Math.abs(ty-my) > .05) ? requestAnimationFrame(update) : null;
	}
	// Parallax souris désactivé : trop coûteux (repaint perspective à chaque
	// mousemove) — la grille reste visible mais ne suit plus la souris.
	// Si jamais on veut le réactiver : décommenter les 2 listeners ci-dessous.
	/*
	hero.addEventListener('mousemove', function(e){
		rect = hero.getBoundingClientRect();
		tx = ((e.clientX - rect.left) / rect.width) * 100;
		ty = ((e.clientY - rect.top) / rect.height) * 100;
		if (!raf) raf = requestAnimationFrame(update);
	}, { passive: true });
	hero.addEventListener('mouseleave', function(){
		tx = 50; ty = 50;
		if (!raf) raf = requestAnimationFrame(update);
	});
	*/
})();
</script>
