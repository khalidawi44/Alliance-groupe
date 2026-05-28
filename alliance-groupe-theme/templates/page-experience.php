<?php
/**
 * Template Name: Expérience immersive (style theirisk.com)
 *
 * Scène 1 "Bureau" : fond bureau de Naples net + ordinateur 3D
 * réaliste + globes flottants. Au clic / "Découvrir" → transition.
 * Scène 2 "Menu constellation" : logo Alliance Groupe 3D au centre +
 * nœuds menu en constellation (cliquables) à la place de l'ordinateur.
 *
 * Slug conseillé : /alliance-groupe.
 *
 * @package Alliance_Groupe_Theme
 */

get_header();
$ag_office = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg';
$ag_logo   = get_stylesheet_directory_uri() . '/assets/images/ag-logo.png';
?>

<style>
body.page-template-page-experience{background:#05060a}
body.page-template-page-experience .ag-nav,
body.page-template-page-experience footer,
body.page-template-page-experience .ag-fsm-toggle{display:none!important}

.agx{position:fixed;inset:0;width:100vw;height:100vh;overflow:hidden;z-index:50;background:#05060a;color:#fff;touch-action:pan-y;font-family:'Helvetica Neue',Arial,sans-serif}
.agx__bg{position:absolute;inset:0;background-size:cover;background-position:center;transform:scale(1.04);animation:agx-bg 48s ease-in-out infinite alternate}
@keyframes agx-bg{from{transform:scale(1.04)}to{transform:scale(1.12)}}
/* Overlay LÉGER : on veut voir le bureau net. Juste un vignettage bas + bords. */
.agx__bg-veil{position:absolute;inset:0;background:
	linear-gradient(180deg,rgba(5,6,10,.25) 0%,rgba(5,6,10,.05) 30%,rgba(5,6,10,.15) 60%,rgba(5,6,10,.75) 100%),
	radial-gradient(circle at 50% 42%,transparent 35%,rgba(5,6,10,.45) 100%)}
.agx__canvas{position:absolute;inset:0;width:100%;height:100%;display:block}

/* Texte scène 1 (intro) en haut */
.agx__intro{position:absolute;top:11vh;left:0;right:0;text-align:center;padding:0 24px;pointer-events:none;transition:opacity .8s ease,transform .8s ease;z-index:4}
.agx__intro.is-hidden{opacity:0;transform:translateY(-20px);pointer-events:none}
.agx__pre{font-size:clamp(.7rem,1.2vw,.9rem);letter-spacing:6px;color:rgba(255,255,255,.7);margin-bottom:14px;text-shadow:0 2px 12px rgba(0,0,0,.8)}
.agx__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2.4rem,8vw,6rem);font-weight:400;letter-spacing:.4vw;line-height:1;margin:0 0 16px;text-shadow:0 4px 30px rgba(0,0,0,.85);background:linear-gradient(180deg,#fff,#e8dcc0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.agx__sub{font-size:clamp(.72rem,1.1vw,.92rem);letter-spacing:4px;color:#D4B45C;font-weight:600;text-shadow:0 2px 12px rgba(0,0,0,.8)}

/* CTA "Découvrir" scène 1 */
.agx__enter{position:absolute;bottom:16vh;left:50%;transform:translateX(-50%);z-index:6;display:inline-flex;align-items:center;gap:10px;padding:16px 38px;border:1px solid rgba(212,180,92,.6);border-radius:999px;background:rgba(10,10,15,.35);backdrop-filter:blur(6px);color:#D4B45C;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:.85rem;text-decoration:none;cursor:pointer;transition:background .3s,color .3s,transform .3s,opacity .6s}
.agx__enter:hover{background:#D4B45C;color:#0a0a0f;text-decoration:none;transform:translateX(-50%) translateY(-2px)}
.agx__enter.is-hidden{opacity:0;pointer-events:none}

/* Titre scène 2 (menu) */
.agx__menu-head{position:absolute;top:9vh;left:0;right:0;text-align:center;padding:0 24px;pointer-events:none;opacity:0;transform:translateY(20px);transition:opacity .8s ease .3s,transform .8s ease .3s;z-index:4}
.agx__menu-head.is-shown{opacity:1;transform:none}
.agx__menu-head h2{font-family:Georgia,serif;font-size:clamp(1.4rem,3.4vw,2.4rem);margin:0;text-shadow:0 2px 18px rgba(0,0,0,.8)}
.agx__menu-head p{color:rgba(255,255,255,.7);font-size:.9rem;letter-spacing:2px;margin:8px 0 0}

/* Bouton retour scène 2 → scène 1 */
.agx__back{position:absolute;top:24px;left:24px;z-index:6;background:rgba(10,10,15,.4);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:999px;padding:10px 18px;font-size:.78rem;letter-spacing:2px;cursor:pointer;opacity:0;pointer-events:none;transition:opacity .5s ease}
.agx__back.is-shown{opacity:1;pointer-events:auto}
.agx__back:hover{border-color:#D4B45C;color:#D4B45C}

.agx__hint{position:absolute;bottom:6vh;left:0;right:0;text-align:center;font-size:.7rem;letter-spacing:3px;color:rgba(255,255,255,.4);text-transform:uppercase;pointer-events:none;text-shadow:0 1px 8px rgba(0,0,0,.8)}

@media (prefers-reduced-motion:reduce){.agx__bg{animation:none}}
</style>

<main class="agx" id="agx">
	<div class="agx__bg" style="background-image:url('<?php echo esc_url( $ag_office ); ?>');" aria-hidden="true"></div>
	<div class="agx__bg-veil" aria-hidden="true"></div>
	<canvas class="agx__canvas" id="agx-canvas"></canvas>

	<div class="agx__intro" id="agx-intro">
		<div class="agx__pre">BIENVENUE CHEZ —</div>
		<h1 class="agx__title">Alliance Groupe</h1>
		<div class="agx__sub">AGENCE WEB & IA · NANTES · NAPLES · MARRAKECH</div>
	</div>

	<a href="#" class="agx__enter" id="agx-enter">Découvrir →</a>

	<div class="agx__menu-head" id="agx-menu-head">
		<h2>Par où commencer ?</h2>
		<p>TOUCHEZ UNE ÉTOILE</p>
	</div>

	<button class="agx__back" id="agx-back" type="button">◂ Retour</button>

	<div class="agx__hint" id="agx-hint">Cliquez sur l'ordinateur pour entrer</div>
</main>

<script>
(function(){
	var host = document.getElementById('agx');
	var canvas = document.getElementById('agx-canvas');
	if (!host || !canvas) return;

	var elIntro = document.getElementById('agx-intro');
	var elEnter = document.getElementById('agx-enter');
	var elMenuHead = document.getElementById('agx-menu-head');
	var elBack = document.getElementById('agx-back');
	var elHint = document.getElementById('agx-hint');

	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		// Pas d'animation : affiche au moins le menu en liens simples.
	}

	function loadThree(cb){
		if (window.THREE) { cb(); return; }
		var ex = document.querySelector('script[data-ag-three]');
		if (ex) { ex.addEventListener('load', cb); return; }
		var s = document.createElement('script');
		s.src = 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js';
		s.async = true; s.dataset.agThree = '1'; s.onload = cb;
		document.head.appendChild(s);
	}

	var MENU = [
		{ label: 'Sites Express', emoji: '🛒', url: '<?php echo esc_url( home_url( '/sites-express' ) ); ?>' },
		{ label: 'Templates',     emoji: '📂', url: '<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>' },
		{ label: 'Cadeaux',       emoji: '🎁', url: '<?php echo esc_url( home_url( '/audit-seo' ) ); ?>' },
		{ label: 'Ambassadeurs',  emoji: '🚀', url: '<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>' },
		{ label: 'Sur-mesure',    emoji: '✦', url: '<?php echo esc_url( home_url( '/sur-mesure' ) ); ?>' },
		{ label: 'Prospection',   emoji: '🤖', url: '<?php echo esc_url( home_url( '/systeme-prospection' ) ); ?>' },
		{ label: 'Contact',       emoji: '📞', url: '<?php echo esc_url( home_url( '/contact' ) ); ?>' }
	];

	loadThree(function(){
		if (!window.THREE) return;
		var T = window.THREE;
		var W = host.clientWidth, H = host.clientHeight;
		var lowSpec = (navigator.hardwareConcurrency || 2) < 4 || W < 768;
		var renderer = new T.WebGLRenderer({ canvas: canvas, antialias: !lowSpec, alpha: true });
		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, lowSpec ? 1.2 : 1.6));
		renderer.setSize(W, H, false);
		renderer.setClearColor(0x000000, 0);
		var scene = new T.Scene();
		var camera = new T.PerspectiveCamera(45, W/H, 0.1, 200);
		camera.position.set(0, 1.5, 17);
		camera.lookAt(0, 0.5, 0);

		scene.add(new T.AmbientLight(0xffffff, 0.6));
		var l1 = new T.PointLight(0xd4b45c, 2.2, 80, 1.6); l1.position.set(9, 8, 11); scene.add(l1);
		var l2 = new T.PointLight(0xf37a1f, 1.4, 60, 1.6); l2.position.set(-10, -2, 8); scene.add(l2);
		var l3 = new T.PointLight(0x99bbff, 1.6, 50, 2); l3.position.set(0, 1, 7); scene.add(l3);

		/* ───────── SCÈNE 1 : ORDINATEUR + GLOBES ───────── */
		var officeGroup = new T.Group();
		var bodyMat   = new T.MeshStandardMaterial({ color: 0x202028, metalness: 0.9, roughness: 0.28 });
		var darkMat   = new T.MeshStandardMaterial({ color: 0x0d0d12, metalness: 0.6, roughness: 0.5 });

		// Base (avec léger biseau via 2 box empilées)
		var base = new T.Mesh(new T.BoxGeometry(9, 0.45, 6.2), bodyMat); base.position.y = -2.6; officeGroup.add(base);
		var baseTop = new T.Mesh(new T.BoxGeometry(8.6, 0.08, 5.8), new T.MeshStandardMaterial({ color: 0x2a2a34, metalness: 0.5, roughness: 0.6 })); baseTop.position.set(0, -2.36, 0); officeGroup.add(baseTop);
		// Clavier (grille de touches via texture)
		var kbCanvas = document.createElement('canvas'); kbCanvas.width = 512; kbCanvas.height = 288;
		var kctx = kbCanvas.getContext('2d');
		kctx.fillStyle = '#1a1a22'; kctx.fillRect(0,0,512,288);
		kctx.fillStyle = '#33333f';
		for (var ry=0; ry<5; ry++) for (var rx=0; rx<14; rx++){ kctx.fillRect(14 + rx*35, 14 + ry*40, 30, 34); }
		kctx.fillStyle = '#2a2a34'; kctx.fillRect(150, 220, 210, 50); // trackpad
		var kbTex = new T.CanvasTexture(kbCanvas);
		var kb = new T.Mesh(new T.PlaneGeometry(8.4, 5.4), new T.MeshStandardMaterial({ map: kbTex, metalness: 0.3, roughness: 0.8 }));
		kb.rotation.x = -Math.PI/2; kb.position.set(0, -2.31, 0.2); officeGroup.add(kb);
		// Charnière
		var hinge = new T.Mesh(new T.CylinderGeometry(0.22, 0.22, 8.8, 16), darkMat);
		hinge.rotation.z = Math.PI/2; hinge.position.set(0, -2.5, -2.9); officeGroup.add(hinge);
		// Écran (cadre + dalle)
		var screenG = new T.Group(); screenG.position.set(0, -2.5, -2.9);
		var bezel = new T.Mesh(new T.BoxGeometry(9.2, 5.8, 0.28), new T.MeshStandardMaterial({ color: 0x16161c, metalness: 0.85, roughness: 0.3 }));
		bezel.position.y = 2.9; screenG.add(bezel);
		var dCanvas = document.createElement('canvas'); dCanvas.width = 512; dCanvas.height = 320;
		var dctx = dCanvas.getContext('2d');
		function paintScreen(p){
			var g = dctx.createLinearGradient(0,0,0,320);
			g.addColorStop(0,'#10101a'); g.addColorStop(.4,'#2a2012'); g.addColorStop(.72,'#b8975a'); g.addColorStop(1,'#f37a1f');
			dctx.fillStyle=g; dctx.fillRect(0,0,512,320);
			dctx.fillStyle='rgba(255,255,255,.9)'; dctx.fillRect(42,40,120,18); dctx.fillRect(42,80,360,12); dctx.fillRect(42,102,300,12);
			dctx.fillStyle='rgba(255,210,140,.95)'; dctx.fillRect(42,148,150,38);
			dctx.fillStyle='rgba(255,255,255,.4)'; for(var i=0;i<4;i++) dctx.fillRect(42,228+i*18,380-(i%2)*60,8);
			dctx.fillStyle='rgba(212,180,92,'+(0.35+Math.abs(Math.sin(p))*0.3)+')'; dctx.fillRect(330,148,140,90);
			dTex.needsUpdate = true;
		}
		var dTex = new T.CanvasTexture(dCanvas); dTex.minFilter = T.LinearFilter;
		var dalle = new T.Mesh(new T.PlaneGeometry(8.6, 5.1), new T.MeshBasicMaterial({ map: dTex }));
		dalle.position.set(0, 2.9, 0.16); screenG.add(dalle);
		screenG.rotation.x = -0.40; officeGroup.add(screenG);
		officeGroup.position.y = 0.4;
		scene.add(officeGroup);

		// Globes flottants (wireframe doré)
		var globes = [];
		var gpos = [ [-10, 4, -2], [11, 5.5, -3], [9, -3, 2] ];
		for (var gi=0; gi<(lowSpec?2:3); gi++){
			var gg = new T.Group();
			var sph = new T.Mesh(new T.SphereGeometry(1.5, 18, 14), new T.MeshBasicMaterial({ color: 0xd4b45c, wireframe: true, transparent: true, opacity: 0.32 }));
			var core = new T.Mesh(new T.SphereGeometry(1.2, 16, 12), new T.MeshStandardMaterial({ color: 0x1a2a4a, metalness: 0.6, roughness: 0.4, emissive: 0x16223a, emissiveIntensity: 0.4 }));
			gg.add(sph); gg.add(core);
			gg.position.set(gpos[gi][0], gpos[gi][1], gpos[gi][2]);
			gg.userData = { spin: 0.003 + gi*0.001, bob: gi };
			scene.add(gg); globes.push(gg);
		}

		/* ───────── SCÈNE 2 : MENU CONSTELLATION ───────── */
		var menuGroup = new T.Group();
		menuGroup.visible = false;
		// Logo AG 3D au centre (plane texturé recto-verso)
		var logoTex = new T.TextureLoader().load('<?php echo esc_url( $ag_logo ); ?>');
		var logo = new T.Mesh(new T.PlaneGeometry(5, 5), new T.MeshBasicMaterial({ map: logoTex, transparent: true, side: T.DoubleSide }));
		menuGroup.add(logo);
		var logoGlow = new T.Mesh(new T.SphereGeometry(3.6, 24, 18), new T.MeshBasicMaterial({ color: 0xd4b45c, wireframe: true, transparent: true, opacity: 0.12 }));
		menuGroup.add(logoGlow);

		// Nœuds menu en constellation autour du logo
		var nodes = [];
		var lineGeoPos = [];
		var N = MENU.length;
		for (var i=0; i<N; i++){
			var ang = (i / N) * Math.PI * 2;
			var rad = 8.5;
			var nx = Math.cos(ang) * rad;
			var ny = Math.sin(ang) * rad * 0.62;
			var nz = (i % 2 === 0 ? 1.5 : -1.5);
			// Sprite label (emoji + texte)
			var lc = document.createElement('canvas'); lc.width = 256; lc.height = 96;
			var lx = lc.getContext('2d');
			lx.fillStyle = 'rgba(212,180,92,1)'; lx.font = '600 26px Helvetica,Arial'; lx.textAlign='center';
			lx.fillText(MENU[i].emoji, 128, 38);
			lx.fillStyle = '#fff'; lx.font = '700 22px Helvetica,Arial';
			lx.fillText(MENU[i].label, 128, 74);
			var lt = new T.CanvasTexture(lc);
			var sprite = new T.Sprite(new T.SpriteMaterial({ map: lt, transparent: true }));
			sprite.scale.set(4, 1.5, 1);
			sprite.position.set(nx, ny, nz);
			sprite.userData = { url: MENU[i].url };
			menuGroup.add(sprite);
			// Petit point lumineux (cliquable aussi)
			var dot = new T.Mesh(new T.SphereGeometry(0.35, 12, 10), new T.MeshStandardMaterial({ color: 0xf37a1f, emissive: 0xf37a1f, emissiveIntensity: 0.8 }));
			dot.position.set(nx, ny - 1.0, nz);
			dot.userData = { url: MENU[i].url };
			menuGroup.add(dot);
			nodes.push(sprite); nodes.push(dot);
			// Ligne du centre vers le nœud
			lineGeoPos.push(0,0,0, nx, ny, nz);
		}
		var lineGeo = new T.BufferGeometry();
		lineGeo.setAttribute('position', new T.Float32BufferAttribute(lineGeoPos, 3));
		var lines = new T.LineSegments(lineGeo, new T.LineBasicMaterial({ color: 0xd4b45c, transparent: true, opacity: 0.25 }));
		menuGroup.add(lines);
		scene.add(menuGroup);

		/* ───────── Interaction ───────── */
		var mode = 'office'; // 'office' | 'menu'
		var ray = new T.Raycaster();
		var mouse = new T.Vector2();

		function showMenu(){
			if (mode === 'menu') return;
			mode = 'menu';
			officeGroup.visible = false;
			menuGroup.visible = true;
			elIntro && elIntro.classList.add('is-hidden');
			elEnter && elEnter.classList.add('is-hidden');
			elMenuHead && elMenuHead.classList.add('is-shown');
			elBack && elBack.classList.add('is-shown');
			if (elHint) elHint.textContent = 'Touchez une étoile pour naviguer';
		}
		function showOffice(){
			mode = 'office';
			officeGroup.visible = true;
			menuGroup.visible = false;
			elIntro && elIntro.classList.remove('is-hidden');
			elEnter && elEnter.classList.remove('is-hidden');
			elMenuHead && elMenuHead.classList.remove('is-shown');
			elBack && elBack.classList.remove('is-shown');
			if (elHint) elHint.textContent = "Cliquez sur l'ordinateur pour entrer";
		}

		elEnter && elEnter.addEventListener('click', function(e){ e.preventDefault(); showMenu(); });
		elBack && elBack.addEventListener('click', function(){ showOffice(); });

		canvas.addEventListener('click', function(ev){
			var r = canvas.getBoundingClientRect();
			mouse.x = ((ev.clientX - r.left) / r.width) * 2 - 1;
			mouse.y = -((ev.clientY - r.top) / r.height) * 2 + 1;
			ray.setFromCamera(mouse, camera);
			if (mode === 'office') {
				// clic sur l'ordi → ouvre le menu
				var hitO = ray.intersectObject(officeGroup, true);
				if (hitO.length) showMenu();
			} else {
				var hits = ray.intersectObjects(nodes, true);
				if (hits.length && hits[0].object.userData.url) {
					window.location.href = hits[0].object.userData.url;
				}
			}
		});

		new ResizeObserver(function(){
			var w = host.clientWidth, h = host.clientHeight;
			if (!w || !h) return;
			camera.aspect = w/h; camera.updateProjectionMatrix();
			renderer.setSize(w, h, false);
		}).observe(host);

		// Tilt souris
		var ttX=0, ttY=0, tX=0, tY=0;
		host.addEventListener('mousemove', function(ev){
			var r = host.getBoundingClientRect();
			ttX = ((ev.clientX-r.left)/r.width-0.5)*0.5; ttY=((ev.clientY-r.top)/r.height-0.5)*0.25;
		}, { passive:true });

		var t0 = performance.now();
		function loop(){
			var t = (performance.now()-t0)*0.001;
			tX += (ttX-tX)*0.05; tY += (ttY-tY)*0.05;

			if (mode === 'office') {
				paintScreen(t*1.4);
				officeGroup.rotation.y = Math.sin(t*0.35)*0.16 + tX;
				officeGroup.rotation.x = -0.03 + tY;
				officeGroup.position.y = 0.4 + Math.sin(t*0.8)*0.16;
				for (var g=0; g<globes.length; g++){ globes[g].rotation.y += globes[g].userData.spin; globes[g].position.y += Math.sin(t*0.6 + globes[g].userData.bob)*0.004; }
			} else {
				menuGroup.rotation.y = Math.sin(t*0.25)*0.25 + tX*0.6;
				menuGroup.rotation.x = tY*0.4;
				logo.rotation.y = Math.sin(t*0.4)*0.12;
				logoGlow.rotation.y -= 0.004; logoGlow.rotation.x += 0.002;
				logoGlow.scale.setScalar(1 + Math.sin(t*1.3)*0.05);
			}
			renderer.render(scene, camera);
			requestAnimationFrame(loop);
		}
		loop();
	});
})();
</script>

<?php get_footer(); ?>
