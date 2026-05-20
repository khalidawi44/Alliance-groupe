<?php
/**
 * Menu fullscreen animé — port vanilla JS du composant 21st.dev
 * (kousthubha_sky_/animated-menu) adapté pour pleine page.
 *
 * Burger en haut à droite. Au clic : overlay 100vw/100vh révélé via
 * clip-path circle (effet expansion), items staggered (clip + fade up
 * spring). Burger morph en croix.
 *
 * Inclus dans header.php juste après le logo.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Items menu — duplique la nav primary pour rester cohérent
$ag_fsm_items = array(
	array( 'url' => home_url( '/' ),                       'label' => 'Accueil',           'sub' => '01' ),
	array( 'url' => home_url( '/services' ),               'label' => 'Services',          'sub' => '02' ),
	array( 'url' => home_url( '/sites-express' ),          'label' => 'Sites Express ⚡',   'sub' => '03' ),
	array( 'url' => home_url( '/templates-wordpress' ),    'label' => 'Templates',         'sub' => '04' ),
	array( 'url' => home_url( '/pourquoi-alliance' ),      'label' => 'Pourquoi Alliance', 'sub' => '05' ),
	array( 'url' => home_url( '/realisations' ),           'label' => 'Réalisations',      'sub' => '06' ),
	array( 'url' => home_url( '/programme-racines' ),      'label' => 'Programme Racines', 'sub' => '07' ),
	array( 'url' => home_url( '/ambassadeurs' ),           'label' => 'Ambassadeurs',      'sub' => '08' ),
	array( 'url' => home_url( '/articles' ),               'label' => 'Articles',          'sub' => '09' ),
	array( 'url' => home_url( '/a-propos' ),               'label' => 'À propos',          'sub' => '10' ),
	array( 'url' => home_url( '/contact' ),                'label' => 'Contact',           'sub' => '11' ),
);
?>

<!-- BURGER : positionné dans le header par CSS via .ag-fsm-toggle -->
<button class="ag-fsm-toggle" type="button" aria-label="Ouvrir le menu" aria-expanded="false" id="ag-fsm-toggle">
	<svg viewBox="0 0 28 28" width="28" height="28" aria-hidden="true">
		<path class="ag-fsm-path ag-fsm-path-1" d="M 4 8 L 24 8"></path>
		<path class="ag-fsm-path ag-fsm-path-2" d="M 4 14 L 24 14"></path>
		<path class="ag-fsm-path ag-fsm-path-3" d="M 4 20 L 24 20"></path>
	</svg>
</button>

<!-- OVERLAY fullscreen -->
<div class="ag-fsm-overlay" id="ag-fsm-overlay" aria-hidden="true" role="dialog">
	<div class="ag-fsm-bg" aria-hidden="true"></div>

	<nav class="ag-fsm-nav" aria-label="Menu principal plein écran">
		<ul class="ag-fsm-list">
			<?php foreach ( $ag_fsm_items as $i => $it ) : ?>
				<li class="ag-fsm-item" style="--i:<?php echo (int) $i; ?>;">
					<a href="<?php echo esc_url( $it['url'] ); ?>" class="ag-fsm-link">
						<span class="ag-fsm-num"><?php echo esc_html( $it['sub'] ); ?></span>
						<span class="ag-fsm-label" data-text="<?php echo esc_attr( $it['label'] ); ?>"><?php echo esc_html( $it['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="ag-fsm-footer">
			<div class="ag-fsm-tagline">Agence Web &amp; IA — Alliance Groupe</div>
			<div class="ag-fsm-contact">
				<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-fsm-cta">Prendre RDV →</a>
				<a href="mailto:contact@alliancegroupe-inc.com" class="ag-fsm-mail">contact@alliancegroupe-inc.com</a>
			</div>
		</div>
	</nav>
</div>

<style>
/* BURGER (visible en permanence en haut à droite, au-dessus du menu nav) */
.ag-fsm-toggle{
	position: fixed;
	top: 24px; right: 24px;
	z-index: 100000;
	width: 54px; height: 54px;
	border-radius: 50%;
	background: rgba(10,10,15,.6);
	border: 1px solid rgba(212,180,92,.4);
	backdrop-filter: blur(14px);
	-webkit-backdrop-filter: blur(14px);
	color: #D4B45C;
	cursor: pointer;
	display: flex; align-items: center; justify-content: center;
	transition: transform .3s cubic-bezier(.16,1,.3,1), border-color .3s ease, background .3s ease, box-shadow .3s ease;
	box-shadow: 0 6px 24px rgba(0,0,0,.35);
}
.ag-fsm-toggle:hover{
	transform: scale(1.06);
	border-color: rgba(212,180,92,.85);
	box-shadow: 0 8px 32px rgba(212,180,92,.35);
}
.ag-fsm-toggle svg{ width: 26px; height: 26px; }
.ag-fsm-path{
	fill: none;
	stroke: #D4B45C;
	stroke-width: 2.2;
	stroke-linecap: round;
	transition: d .45s cubic-bezier(.5,0,.2,1), opacity .25s ease, transform .45s cubic-bezier(.5,0,.2,1);
	transform-origin: 50% 50%;
}
.ag-fsm-toggle.is-open .ag-fsm-path-1{ d: path("M 6 22 L 22 6"); }
.ag-fsm-toggle.is-open .ag-fsm-path-2{ opacity: 0; }
.ag-fsm-toggle.is-open .ag-fsm-path-3{ d: path("M 6 6 L 22 22"); }
/* Fallback pour Firefox qui ne supporte pas encore d: path() — rotation des paths */
@supports not (d: path("M 0 0")){
	.ag-fsm-toggle.is-open .ag-fsm-path-1{ transform: rotate(45deg) translate(0, 5.5px); }
	.ag-fsm-toggle.is-open .ag-fsm-path-3{ transform: rotate(-45deg) translate(0, -5.5px); }
}

/* OVERLAY fullscreen */
.ag-fsm-overlay{
	position: fixed; inset: 0;
	z-index: 99999;
	pointer-events: none;
}
.ag-fsm-overlay.is-open{ pointer-events: auto; }
.ag-fsm-bg{
	position: absolute; inset: 0;
	background:
		radial-gradient(ellipse at top right, rgba(212,180,92,.12) 0%, transparent 50%),
		radial-gradient(ellipse at bottom left, rgba(243,122,31,.08) 0%, transparent 50%),
		linear-gradient(135deg, #0a0a0f 0%, #14141c 50%, #0a0a0f 100%);
	clip-path: circle(0px at calc(100% - 50px) 50px);
	transition: clip-path .8s cubic-bezier(.86,0,.07,1);
}
.ag-fsm-overlay.is-open .ag-fsm-bg{
	clip-path: circle(220vmax at calc(100% - 50px) 50px);
}

/* NAV (items centrés vertical) */
.ag-fsm-nav{
	position: relative;
	width: 100%; height: 100%;
	display: flex; flex-direction: column; justify-content: center;
	padding: 100px 8vw 120px;
	opacity: 0;
	transition: opacity .35s ease .2s;
	overflow-y: auto;
}
.ag-fsm-overlay.is-open .ag-fsm-nav{ opacity: 1; }
.ag-fsm-list{
	list-style: none;
	padding: 0; margin: 0;
	display: flex; flex-direction: column; gap: 0;
}

/* ITEM (animé en stagger) */
.ag-fsm-item{
	opacity: 0;
	transform: translateY(60px) rotateX(-30deg);
	transition: opacity .55s cubic-bezier(.16,1,.3,1), transform .65s cubic-bezier(.16,1,.3,1);
	transition-delay: 0ms;
	transform-origin: bottom center;
}
.ag-fsm-overlay.is-open .ag-fsm-item{
	opacity: 1;
	transform: translateY(0) rotateX(0);
	transition-delay: calc(var(--i) * 80ms + 300ms);
}
.ag-fsm-link{
	display: flex; align-items: baseline; gap: 20px;
	padding: 8px 0;
	color: #fff;
	text-decoration: none;
	font-family: Georgia, 'Playfair Display', serif;
	font-size: clamp(1.6rem, 4.2vw, 3.4rem);
	font-weight: 700;
	line-height: 1.1;
	letter-spacing: -0.025em;
	transition: color .35s ease, padding-left .35s ease;
	position: relative;
}
.ag-fsm-link:hover{
	color: #D4B45C;
	padding-left: 24px;
	text-decoration: none;
}
.ag-fsm-link::before{
	content: '';
	position: absolute;
	left: 0; top: 50%;
	width: 0; height: 3px;
	background: linear-gradient(90deg, #D4B45C, #F37A1F);
	transform: translateY(-50%);
	transition: width .4s cubic-bezier(.16,1,.3,1);
	box-shadow: 0 0 12px rgba(212,180,92,.6);
}
.ag-fsm-link:hover::before{ width: 16px; }
.ag-fsm-num{
	font-family: 'Manrope', system-ui, sans-serif;
	font-size: .85rem;
	color: rgba(212,180,92,.6);
	font-weight: 700;
	letter-spacing: 4px;
	min-width: 40px;
}
.ag-fsm-label{
	position: relative;
	display: inline-block;
	background: linear-gradient(90deg, #fff 0%, #fff 50%, #D4B45C 50%, #D4B45C 100%);
	background-size: 200% 100%;
	background-position: 0% 0;
	-webkit-background-clip: text; background-clip: text;
	-webkit-text-fill-color: transparent;
	transition: background-position .5s cubic-bezier(.7,0,.3,1);
}
.ag-fsm-link:hover .ag-fsm-label{ background-position: -100% 0; }

/* FOOTER overlay */
.ag-fsm-footer{
	position: absolute;
	left: 8vw; right: 8vw; bottom: 28px;
	display: flex; justify-content: space-between; align-items: center;
	flex-wrap: wrap; gap: 16px;
	opacity: 0;
	transition: opacity .4s ease .9s;
	background: linear-gradient(180deg, transparent 0%, rgba(10,10,15,.95) 50%);
	padding-top: 30px;
}
.ag-fsm-overlay.is-open .ag-fsm-footer{ opacity: 1; }
.ag-fsm-tagline{
	color: rgba(255,255,255,.4);
	font-size: .85rem;
	letter-spacing: 3px;
	text-transform: uppercase;
}
.ag-fsm-contact{
	display: flex; align-items: center; gap: 24px;
	flex-wrap: wrap;
}
.ag-fsm-cta{
	display: inline-block;
	padding: 14px 28px;
	background: linear-gradient(135deg, #D4B45C 0%, #F37A1F 100%);
	color: #0a0a0f;
	font-weight: 800;
	font-size: .92rem;
	letter-spacing: 1.5px;
	text-transform: uppercase;
	border-radius: 999px;
	text-decoration: none;
	box-shadow: 0 8px 24px rgba(212,180,92,.3);
	transition: transform .3s ease, box-shadow .3s ease;
}
.ag-fsm-cta:hover{
	transform: translateY(-2px);
	box-shadow: 0 12px 32px rgba(212,180,92,.5);
	color: #0a0a0f; text-decoration: none;
}
.ag-fsm-mail{
	color: rgba(212,180,92,.7);
	text-decoration: none;
	font-size: .9rem;
	letter-spacing: .5px;
	transition: color .3s ease;
}
.ag-fsm-mail:hover{ color: #D4B45C; text-decoration: none; }

/* Body lock quand menu ouvert */
body.ag-fsm-open{ overflow: hidden; }

@media (max-width: 768px){
	.ag-fsm-toggle{ top: 16px; right: 16px; width: 48px; height: 48px; }
	.ag-fsm-link{ font-size: clamp(1.4rem, 6vw, 2.4rem); gap: 14px; padding: 6px 0; }
	.ag-fsm-num{ min-width: 30px; font-size: .65rem; }
	.ag-fsm-nav{ padding: 90px 6vw 130px; }
	.ag-fsm-footer{ flex-direction: column; align-items: flex-start; bottom: 20px; }
}
/* Hauteur très réduite (laptop 13") : font encore plus serrée */
@media (max-height: 720px){
	.ag-fsm-link{ font-size: clamp(1.4rem, 3.4vw, 2.4rem); padding: 6px 0; }
	.ag-fsm-nav{ padding: 80px 8vw 100px; }
}

@media (prefers-reduced-motion: reduce){
	.ag-fsm-bg, .ag-fsm-nav, .ag-fsm-item, .ag-fsm-footer{
		transition: opacity .2s ease !important;
	}
	.ag-fsm-bg{ clip-path: none !important; opacity: 0; }
	.ag-fsm-overlay.is-open .ag-fsm-bg{ opacity: 1; }
	.ag-fsm-item{ transform: none !important; }
}
</style>

<script>
(function(){
	var toggle = document.getElementById('ag-fsm-toggle');
	var overlay = document.getElementById('ag-fsm-overlay');
	if (!toggle || !overlay) return;

	function setOpen(open){
		toggle.classList.toggle('is-open', open);
		overlay.classList.toggle('is-open', open);
		overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
		document.body.classList.toggle('ag-fsm-open', open);
	}

	toggle.addEventListener('click', function(){
		setOpen(!toggle.classList.contains('is-open'));
	});
	// Escape pour fermer
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape' && toggle.classList.contains('is-open')) setOpen(false);
	});
	// Click sur un lien : ferme le menu (sauf ancre #)
	overlay.querySelectorAll('.ag-fsm-link').forEach(function(link){
		link.addEventListener('click', function(){ setOpen(false); });
	});
})();
</script>
