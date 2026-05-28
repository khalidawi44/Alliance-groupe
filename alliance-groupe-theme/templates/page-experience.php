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
	<!-- Fond : bureau de Naples -->
	<div class="ag-xp__bg" aria-hidden="true" style="background-image:url('<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg' ); ?>');"></div>
	<div class="ag-xp__bg-overlay" aria-hidden="true"></div>
	<!-- Starfield CSS subtil par-dessus -->
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
			<div class="ag-xp__sub">CLIQUEZ POUR ENTRER</div>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-xp__cta" id="ag-xp-enter">Entrer dans le site →</a>
		</div>
	</div>

	<!-- Navigation : BACK | NEXT + compteur -->
	<div class="ag-xp__nav" aria-label="Navigation entre les stages">
		<button type="button" class="ag-xp__back" id="ag-xp-back" aria-label="Précédent">◂ BACK</button>
		<div class="ag-xp__counter"><span id="ag-xp-current">01</span> <span class="ag-xp__sep">/</span> <span id="ag-xp-total">05</span></div>
		<button type="button" class="ag-xp__next" id="ag-xp-next" aria-label="Suivant">NEXT ▸</button>
	</div>

	<!-- Hint : naviguer + entrer -->
	<div class="ag-xp__hint" id="ag-xp-hint">← Glissez pour explorer · cliquez l'écran pour entrer →</div>
</main>

<style>
.ag-xp{position:fixed;inset:0;width:100vw;height:100vh;background:#05060a;color:#e8e6e0;overflow:hidden;z-index:50;touch-action:pan-y}
.ag-xp__bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:saturate(1.05) brightness(.7);transform:scale(1.05);animation:ag-xp-bg-zoom 40s ease-in-out infinite alternate}
.ag-xp__bg-overlay{position:absolute;inset:0;background:radial-gradient(circle at 50% 45%,rgba(8,8,14,.35) 0%,rgba(6,7,12,.78) 70%,rgba(4,4,8,.95) 100%)}
@keyframes ag-xp-bg-zoom{from{transform:scale(1.05)}to{transform:scale(1.16)}}
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
	animation:ag-xp-stars-drift 240s linear infinite;opacity:.45}
.ag-xp__stars--alt{animation-duration:380s;animation-direction:reverse;opacity:.28;filter:blur(.3px)}
@keyframes ag-xp-stars-drift{from{transform:translate(0,0)}to{transform:translate(-20%,-12%)}}

.ag-xp__canvas{position:absolute;inset:0;width:100%;height:100%;display:block}

.ag-xp__stages{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;padding:0 24px;pointer-events:none}
.ag-xp__stage{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transform:translateX(40px);transition:opacity .9s ease,transform 1.1s cubic-bezier(.16,1,.3,1);pointer-events:none}
.ag-xp__stage.is-active{opacity:1;transform:none;transition-delay:.15s}
/* le texte ne capture pas les clics → le canvas (laptop) reste cliquable ;
   seuls les liens/boutons sont interactifs */
.ag-xp__cta{pointer-events:auto}
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
		camera.position.set(0, 1.5, 16);
		camera.lookAt(0, 0, 0);

		scene.add(new T.AmbientLight(0xffffff, 0.55));
		var l1 = new T.PointLight(0xd4b45c, 2.0, 60, 1.6); l1.position.set(8, 7, 10); scene.add(l1);
		var l2 = new T.PointLight(0xf37a1f, 1.3, 50, 1.6); l2.position.set(-9, -3, 7); scene.add(l2);
		var l3 = new T.PointLight(0x88aaff, 1.4, 40, 2); l3.position.set(0, 1, 7); scene.add(l3); // reflet écran

		// ── ORDINATEUR PORTABLE ──
		var laptop = new T.Group();
		var metal = new T.MeshStandardMaterial({ color: 0x1c1c24, metalness: 0.85, roughness: 0.3 });

		// Base / clavier (incliné légèrement)
		var base = new T.Mesh(new T.BoxGeometry(9, 0.4, 6), metal);
		base.position.y = -2.4;
		laptop.add(base);
		// Pavé clavier (légèrement plus clair)
		var kb = new T.Mesh(new T.BoxGeometry(8, 0.06, 4.6), new T.MeshStandardMaterial({ color: 0x2a2a34, metalness: 0.5, roughness: 0.6 }));
		kb.position.set(0, -2.18, 0.4);
		laptop.add(kb);
		// Trackpad
		var pad = new T.Mesh(new T.BoxGeometry(2.4, 0.04, 1.4), new T.MeshStandardMaterial({ color: 0x33333f, metalness: 0.4, roughness: 0.7 }));
		pad.position.set(0, -2.15, 2.0);
		laptop.add(pad);

		// Écran (cadre)
		var screenGroup = new T.Group();
		screenGroup.position.set(0, -2.4, -2.7);
		var frame = new T.Mesh(new T.BoxGeometry(9, 5.6, 0.3), new T.MeshStandardMaterial({ color: 0x14141a, metalness: 0.8, roughness: 0.35 }));
		frame.position.y = 2.8;
		screenGroup.add(frame);

		// Dalle écran : texture canvas (mockup site qui pulse)
		var sc = document.createElement('canvas'); sc.width = 512; sc.height = 320;
		var sctx = sc.getContext('2d');
		function paintScreen(p){
			var g = sctx.createLinearGradient(0,0,0,320);
			g.addColorStop(0,'#10101a'); g.addColorStop(.4,'#2a2012'); g.addColorStop(.7,'#b8975a'); g.addColorStop(1,'#f37a1f');
			sctx.fillStyle=g; sctx.fillRect(0,0,512,320);
			sctx.fillStyle='rgba(255,255,255,.85)'; sctx.fillRect(40,42,120,20); // logo
			sctx.fillRect(40,82,360,12); sctx.fillRect(40,104,300,12); // titres
			sctx.fillStyle='rgba(255,210,140,.95)'; sctx.fillRect(40,150,150,40); // CTA
			sctx.fillStyle='rgba(255,255,255,.4)';
			for(var i=0;i<4;i++) sctx.fillRect(40,230+i*18,380-(i%2)*60,8);
			sctx.fillStyle='rgba(212,180,92,'+(0.35+Math.abs(Math.sin(p))*0.3)+')'; sctx.fillRect(330,150,140,90); // image qui pulse
			tex.needsUpdate = true;
		}
		var tex = new T.CanvasTexture(sc); tex.minFilter = T.LinearFilter;
		var screen = new T.Mesh(new T.PlaneGeometry(8.4, 5.0), new T.MeshBasicMaterial({ map: tex }));
		screen.position.set(0, 2.8, 0.18);
		screenGroup.add(screen);

		// Inclinaison de l'écran (ouvert ~100°)
		screenGroup.rotation.x = -0.42;
		laptop.add(screenGroup);

		laptop.position.y = 0.5;
		scene.add(laptop);

		// Position monde de l'écran (cible du zoom)
		var screenTarget = new T.Vector3(0, 1.8, -1.2);

		new ResizeObserver(function(){
			var w = host.clientWidth, h = host.clientHeight;
			if (!w || !h) return;
			camera.aspect = w/h;
			camera.updateProjectionMatrix();
			renderer.setSize(w, h, false);
		}).observe(host);

		// ── CLIC : la caméra plonge dans l'écran puis entre sur le site ──
		var entering = false, enterT = 0;
		var camFrom = new T.Vector3(), camStart = camera.position.clone();
		function enterScreen(){
			if (entering) return;
			entering = true; enterT = 0; camFrom.copy(camera.position);
			// Voile qui apparaît
			var veil = document.createElement('div');
			veil.id = 'ag-xp-veil';
			veil.style.cssText = 'position:fixed;inset:0;background:#0a0a0f;opacity:0;z-index:60;transition:opacity .6s ease;pointer-events:none';
			document.body.appendChild(veil);
			setTimeout(function(){ veil.style.opacity = '1'; }, 700);
			// Redirection vers l'accueil après l'immersion
			setTimeout(function(){ window.location.href = '<?php echo esc_url( home_url( '/' ) ); ?>'; }, 1350);
		}
		canvas.addEventListener('click', enterScreen);
		var enterBtn = document.getElementById('ag-xp-enter');
		if (enterBtn) enterBtn.addEventListener('click', function(e){ e.preventDefault(); enterScreen(); });

		var t0 = performance.now();
		function loop(){
			var t = (performance.now() - t0) * 0.001;
			paintScreen(t * 1.4);

			if (entering) {
				enterT = Math.min(1, enterT + 0.018);
				var e = 1 - Math.pow(1 - enterT, 3); // easeOutCubic
				camera.position.lerpVectors(camFrom, screenTarget, e);
				camera.fov = 45 - e * 28; // l'écran remplit le champ
				camera.updateProjectionMatrix();
				camera.lookAt(screenTarget);
			} else {
				// Rotation douce + bobbing du laptop, léger suivi souris
				laptop.rotation.y = Math.sin(t * 0.4) * 0.18 + tiltX;
				laptop.rotation.x = -0.04 + Math.sin(t * 0.6) * 0.03 + tiltY;
				laptop.position.y = 0.5 + Math.sin(t * 0.8) * 0.18;
			}
			renderer.render(scene, camera);
			requestAnimationFrame(loop);
		}

		// Léger tilt souris (desktop)
		var tiltX = 0, tiltY = 0, ttX = 0, ttY = 0;
		host.addEventListener('mousemove', function(ev){
			var r = host.getBoundingClientRect();
			ttX = ((ev.clientX - r.left)/r.width - 0.5) * 0.5;
			ttY = ((ev.clientY - r.top)/r.height - 0.5) * 0.2;
		}, { passive: true });
		(function damp(){ tiltX += (ttX - tiltX)*0.05; tiltY += (ttY - tiltY)*0.05; requestAnimationFrame(damp); })();

		// Le laptop reste l'objet ; les stages ne changent plus la géométrie.
		window._agXpOnStage = function(){};

		loop();
	});
})();
</script>

<?php get_footer(); ?>
