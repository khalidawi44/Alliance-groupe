<?php
/**
 * Template Name: Expérience immersive (style theirisk.com)
 *
 * Page plein écran avec 5 "stages" navigables (swipe / clavier / clic).
 * Fond starfield CSS + objet 3D Three.js central qui morph à chaque
 * stage. Typographie serif élégante. Inspiré de theirisk.com/welcome.
 *
 * Pour activer : créer une page WordPress avec ce template, slug
 * /experience par exemple.
 *
 * @package Alliance_Groupe_Theme
 */

get_header(); ?>

<style>
/* On masque les éléments WP gênants pour profiter du plein écran */
body.page-template-page-experience { background: #000; }
body.page-template-page-experience .ag-nav,
body.page-template-page-experience footer,
body.page-template-page-experience .ag-fsm-toggle { display: none !important; }
</style>

<main class="ag-xp" id="ag-xp" role="region" aria-roledescription="carousel">
	<!-- Starfield CSS (étoiles fixes + petites étoiles qui scintillent) -->
	<div class="ag-xp__stars" aria-hidden="true"></div>
	<div class="ag-xp__stars ag-xp__stars--alt" aria-hidden="true"></div>

	<!-- Canvas 3D plein écran -->
	<canvas class="ag-xp__canvas" id="ag-xp-canvas" aria-hidden="true"></canvas>

	<!-- Stages — contenu textuel qui change à chaque étape -->
	<div class="ag-xp__stages" id="ag-xp-stages">
		<div class="ag-xp__stage is-active" data-stage="0">
			<div class="ag-xp__pre">HELLO, THIS IS —</div>
			<h1 class="ag-xp__title">ALLIANCE GROUPE</h1>
			<div class="ag-xp__sub">AGENCE WEB & IA · NANTES · NAPLES · MARRAKECH</div>
			<div class="ag-xp__line">Bienvenue dans notre atelier numérique.</div>
		</div>
		<div class="ag-xp__stage" data-stage="1">
			<div class="ag-xp__pre">WE BUILD —</div>
			<h1 class="ag-xp__title">VOTRE SITE</h1>
			<div class="ag-xp__sub">DESIGN PREMIUM · LIVRÉ EN 7 JOURS</div>
			<div class="ag-xp__line">Sites express, sur-mesure, e-commerce. Tout en clair, prix fixes.</div>
		</div>
		<div class="ag-xp__stage" data-stage="2">
			<div class="ag-xp__pre">WE CONNECT —</div>
			<h1 class="ag-xp__title">VOTRE RÉSEAU</h1>
			<div class="ag-xp__sub">AMBASSADEURS · 10 % À VIE</div>
			<div class="ag-xp__line">Présentez-nous des clients, touchez 10 % sur chaque vente. À vie.</div>
		</div>
		<div class="ag-xp__stage" data-stage="3">
			<div class="ag-xp__pre">WE GROW —</div>
			<h1 class="ag-xp__title">+340 %</h1>
			<div class="ag-xp__sub">DE LEADS EN MOYENNE</div>
			<div class="ag-xp__line">Nos sites ne sont pas des cartes de visite. Ils ramènent des clients.</div>
		</div>
		<div class="ag-xp__stage" data-stage="4">
			<div class="ag-xp__pre">BEGIN —</div>
			<h1 class="ag-xp__title">L'AVENTURE</h1>
			<div class="ag-xp__sub">DÉCOUVREZ NOS OFFRES</div>
			<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-xp__cta">Voir les packs →</a>
		</div>
	</div>

	<!-- Navigation : BACK | NEXT + compteur -->
	<div class="ag-xp__nav" aria-label="Navigation entre les stages">
		<button type="button" class="ag-xp__back" id="ag-xp-back" aria-label="Précédent">◂ BACK</button>
		<div class="ag-xp__counter"><span id="ag-xp-current">01</span> <span class="ag-xp__sep">/</span> <span id="ag-xp-total">05</span></div>
		<button type="button" class="ag-xp__next" id="ag-xp-next" aria-label="Suivant">NEXT ▸</button>
	</div>

	<!-- Hint swipe (visible sur mobile au début) -->
	<div class="ag-xp__hint" id="ag-xp-hint">← Glissez pour explorer →</div>
</main>

<style>
.ag-xp{position:fixed;inset:0;width:100vw;height:100vh;background:#000;color:#e8e6e0;overflow:hidden;z-index:50;touch-action:pan-y}
.ag-xp__stars,.ag-xp__stars--alt{position:absolute;inset:-50%;width:200%;height:200%;background-image:
	radial-gradient(1.5px 1.5px at 25% 15%, #fff 50%, transparent 100%),
	radial-gradient(1px 1px at 38% 62%, rgba(255,255,255,.85) 50%, transparent 100%),
	radial-gradient(1.5px 1.5px at 72% 28%, #fff 50%, transparent 100%),
	radial-gradient(1px 1px at 18% 80%, rgba(255,255,255,.7) 50%, transparent 100%),
	radial-gradient(1.8px 1.8px at 88% 75%, #fff 50%, transparent 100%),
	radial-gradient(1px 1px at 55% 45%, rgba(255,255,255,.6) 50%, transparent 100%),
	radial-gradient(1.2px 1.2px at 8% 35%, rgba(255,255,255,.9) 50%, transparent 100%),
	radial-gradient(1px 1px at 92% 12%, rgba(255,255,255,.75) 50%, transparent 100%),
	radial-gradient(1.4px 1.4px at 48% 92%, #fff 50%, transparent 100%),
	radial-gradient(1px 1px at 65% 8%, rgba(255,255,255,.55) 50%, transparent 100%);
	background-size:600px 600px,800px 800px,500px 500px,700px 700px,650px 650px,900px 900px,750px 750px,580px 580px,820px 820px,550px 550px;
	animation:ag-xp-stars-drift 240s linear infinite;opacity:.85}
.ag-xp__stars--alt{animation-duration:380s;animation-direction:reverse;opacity:.55;filter:blur(.3px)}
@keyframes ag-xp-stars-drift{from{transform:translate(0,0)}to{transform:translate(-20%,-12%)}}

.ag-xp__canvas{position:absolute;inset:0;width:100%;height:100%;display:block}

.ag-xp__stages{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;padding:0 24px;pointer-events:none}
.ag-xp__stage{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transform:translateX(40px);transition:opacity .9s ease,transform 1.1s cubic-bezier(.16,1,.3,1);pointer-events:none}
.ag-xp__stage.is-active{opacity:1;transform:none;pointer-events:auto;transition-delay:.15s}
.ag-xp__stage.is-prev{transform:translateX(-40px)}

.ag-xp__pre{font-family:'Helvetica Neue',Arial,sans-serif;font-size:clamp(.7rem,1.2vw,.9rem);letter-spacing:6px;color:rgba(232,230,224,.55);margin-bottom:18px;font-weight:400}
.ag-xp__title{font-family:Georgia,'Playfair Display','Times New Roman',serif;font-size:clamp(2.6rem,9vw,7rem);font-weight:400;letter-spacing:.5vw;line-height:1;margin:0 0 22px;color:#e8e6e0;background:linear-gradient(180deg,#fff 0%,#cfc8b8 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.ag-xp__sub{font-family:'Helvetica Neue',Arial,sans-serif;font-size:clamp(.7rem,1.1vw,.9rem);letter-spacing:4px;color:rgba(212,180,92,.85);font-weight:600;margin-bottom:32px}
.ag-xp__line{font-family:Georgia,serif;font-style:italic;font-size:clamp(.95rem,1.4vw,1.15rem);color:rgba(232,230,224,.7);max-width:520px;line-height:1.6}
.ag-xp__cta{display:inline-block;padding:14px 36px;border:1px solid rgba(212,180,92,.6);border-radius:999px;color:#D4B45C;font-family:'Helvetica Neue',Arial,sans-serif;font-weight:700;letter-spacing:2px;font-size:.85rem;text-transform:uppercase;text-decoration:none;transition:background .35s ease,color .35s ease,transform .35s ease;margin-top:8px}
.ag-xp__cta:hover{background:#D4B45C;color:#0a0a0f;text-decoration:none;transform:translateY(-2px)}

.ag-xp__nav{position:absolute;bottom:48px;left:0;right:0;display:flex;justify-content:center;align-items:center;gap:30px;font-family:'Helvetica Neue',Arial,sans-serif;letter-spacing:3px;font-size:.78rem;font-weight:600;color:rgba(232,230,224,.55);pointer-events:auto;z-index:5}
.ag-xp__back,.ag-xp__next{background:none;border:none;color:rgba(232,230,224,.55);font:inherit;letter-spacing:inherit;cursor:pointer;padding:8px 16px;transition:color .25s ease}
.ag-xp__back:hover,.ag-xp__back:focus,.ag-xp__next:hover,.ag-xp__next:focus{color:#fff}
.ag-xp__back:disabled,.ag-xp__next:disabled{opacity:.25;cursor:not-allowed}
.ag-xp__counter{color:rgba(232,230,224,.4);font-variant-numeric:tabular-nums}
.ag-xp__sep{opacity:.4;margin:0 4px}

.ag-xp__hint{position:absolute;top:32px;left:0;right:0;text-align:center;font-family:'Helvetica Neue',Arial,sans-serif;letter-spacing:4px;font-size:.7rem;color:rgba(232,230,224,.35);text-transform:uppercase;animation:ag-xp-hint-fade 6s ease-in-out;animation-fill-mode:forwards;pointer-events:none}
@keyframes ag-xp-hint-fade{0%{opacity:0}15%,75%{opacity:1}100%{opacity:0}}

@media (max-width:768px){
	.ag-xp__nav{bottom:34px;gap:18px}
	.ag-xp__hint{top:24px;letter-spacing:2px}
}
@media (prefers-reduced-motion:reduce){
	.ag-xp__stars,.ag-xp__stars--alt{animation:none}
	.ag-xp__stage{transition:opacity .3s ease}
}
</style>

<script>
(function(){
	var stages = document.querySelectorAll('.ag-xp__stage');
	var total = stages.length;
	var current = 0;
	var btnBack = document.getElementById('ag-xp-back');
	var btnNext = document.getElementById('ag-xp-next');
	var elCurrent = document.getElementById('ag-xp-current');
	var elTotal = document.getElementById('ag-xp-total');
	var canvas = document.getElementById('ag-xp-canvas');
	if (elTotal) elTotal.textContent = String(total).padStart(2, '0');

	function go(idx, dir){
		if (idx < 0 || idx >= total) return;
		var prev = current;
		current = idx;
		stages.forEach(function(st, i){
			st.classList.remove('is-active','is-prev');
			if (i === current) st.classList.add('is-active');
			else if (i < current) st.classList.add('is-prev');
		});
		if (elCurrent) elCurrent.textContent = String(current + 1).padStart(2, '0');
		if (btnBack) btnBack.disabled = (current === 0);
		if (btnNext) btnNext.disabled = (current === total - 1);
		if (window._agXpOnStage) window._agXpOnStage(current, prev);
	}

	btnBack && btnBack.addEventListener('click', function(){ go(current - 1); });
	btnNext && btnNext.addEventListener('click', function(){ go(current + 1); });
	go(0);

	// Clavier
	document.addEventListener('keydown', function(e){
		if (e.key === 'ArrowRight' || e.key === ' ') go(current + 1);
		else if (e.key === 'ArrowLeft') go(current - 1);
	});

	// Swipe tactile
	var tx0 = null, ty0 = null;
	var host = document.getElementById('ag-xp');
	host.addEventListener('touchstart', function(e){
		if (!e.touches[0]) return;
		tx0 = e.touches[0].clientX; ty0 = e.touches[0].clientY;
	}, { passive: true });
	host.addEventListener('touchend', function(e){
		if (tx0 === null) return;
		var t = e.changedTouches[0];
		var dx = t.clientX - tx0, dy = t.clientY - ty0;
		if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
			if (dx < 0) go(current + 1); else go(current - 1);
		}
		tx0 = null; ty0 = null;
	}, { passive: true });

	// Souris (drag horizontal)
	var mx0 = null;
	host.addEventListener('mousedown', function(e){ mx0 = e.clientX; });
	host.addEventListener('mouseup', function(e){
		if (mx0 === null) return;
		var dx = e.clientX - mx0;
		if (Math.abs(dx) > 80) { if (dx < 0) go(current + 1); else go(current - 1); }
		mx0 = null;
	});

	// ── Three.js : objet 3D central qui morph par stage ──
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

	function loadThree(cb){
		if (window.THREE) { cb(); return; }
		var existing = document.querySelector('script[data-ag-three]');
		if (existing) { existing.addEventListener('load', cb); return; }
		var s = document.createElement('script');
		s.src = 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js';
		s.async = true; s.dataset.agThree = '1';
		s.onload = cb;
		document.head.appendChild(s);
	}

	loadThree(function(){
		if (!window.THREE) return;
		var T = window.THREE;
		var W = host.clientWidth, H = host.clientHeight;
		var renderer = new T.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
		renderer.setSize(W, H, false);
		renderer.setClearColor(0x000000, 0);
		var scene = new T.Scene();
		var camera = new T.PerspectiveCamera(45, W/H, 0.1, 100);
		camera.position.set(0, 0, 18);

		scene.add(new T.AmbientLight(0xffffff, 0.5));
		var l1 = new T.PointLight(0xd4b45c, 2.0, 50, 1.6); l1.position.set(8, 6, 8); scene.add(l1);
		var l2 = new T.PointLight(0xf37a1f, 1.3, 45, 1.6); l2.position.set(-8, -4, 6); scene.add(l2);

		// 5 géométries pour les 5 stages
		var geos = [
			new T.IcosahedronGeometry(3.2, 1),       // 0 — sphère facettée (welcome)
			new T.BoxGeometry(4.6, 4.6, 0.4),        // 1 — "écran" (we build)
			new T.TorusKnotGeometry(2.4, 0.6, 90, 16),// 2 — knot (réseau)
			new T.DodecahedronGeometry(3.4, 0),       // 3 — gem (croissance)
			new T.OctahedronGeometry(3.5, 0)          // 4 — octaèdre (aventure)
		];
		var mat = new T.MeshStandardMaterial({
			color: 0xc9a866, metalness: 0.92, roughness: 0.22,
			emissive: 0x4a3c1e, emissiveIntensity: 0.35, flatShading: true
		});
		var mesh = new T.Mesh(geos[0], mat);
		scene.add(mesh);

		// Halo wireframe doré
		var halo = new T.Mesh(new T.IcosahedronGeometry(3.9, 0), new T.MeshBasicMaterial({ color: 0xd4b45c, wireframe: true, transparent: true, opacity: 0.22 }));
		scene.add(halo);

		new ResizeObserver(function(){
			var w = host.clientWidth, h = host.clientHeight;
			if (!w || !h) return;
			camera.aspect = w/h;
			camera.updateProjectionMatrix();
			renderer.setSize(w, h, false);
		}).observe(host);

		// Morph d'une géométrie à l'autre au changement de stage
		var targetGeo = 0;
		window._agXpOnStage = function(idx){ targetGeo = idx; mesh.geometry.dispose(); mesh.geometry = geos[idx]; halo.scale.set(0.6, 0.6, 0.6); };

		var t0 = performance.now();
		function loop(){
			var t = (performance.now() - t0) * 0.001;
			mesh.rotation.x = t * 0.18;
			mesh.rotation.y = t * 0.26;
			mesh.position.y = Math.sin(t * 0.7) * 0.25;
			halo.rotation.x = -t * 0.10;
			halo.rotation.y = -t * 0.16;
			// Halo scale lerp pour effet "pop" au changement de stage
			var s = halo.scale.x;
			s += (1 - s) * 0.08;
			halo.scale.setScalar(s * (1 + Math.sin(t * 1.2) * 0.04));
			renderer.render(scene, camera);
			requestAnimationFrame(loop);
		}
		loop();
	});
})();
</script>

<?php get_footer(); ?>
