<?php
/**
 * Scène 3D du HERO (page d'accueil uniquement) — style Vexik.
 *
 * Icosaèdre central + tétraèdres en orbite + particules + lumières
 * gold/orange. Rendue avec Three.js (lib partagée avec globe-3d.php,
 * via jsDelivr).
 *
 * Garde-fous perf :
 *  - skip si prefers-reduced-motion
 *  - skip si innerWidth < 1100
 *  - skip si navigator.hardwareConcurrency < 4
 *  - pixel ratio capé à 1.5
 *  - rendu pausé via IntersectionObserver dès que le hero quitte le viewport
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ag-hero-3d" aria-hidden="true">
	<canvas id="ag-hero-3d-canvas"></canvas>
</div>

<style>
.ag-hero-3d {
	position: absolute;
	inset: 0;
	z-index: 1;
	overflow: hidden;
	pointer-events: none;
	opacity: 0;
	transition: opacity 1.2s ease;
}
.ag-hero-3d.is-ready { opacity: 1; }
.ag-hero-3d canvas { width: 100%; height: 100%; display: block; }
.ag-hero__content, .ag-hero__bg { position: relative; z-index: 3; }
</style>

<script>
(function () {
	var host = document.querySelector('.ag-hero-3d');
	var canvas = document.getElementById('ag-hero-3d-canvas');
	if (!host || !canvas) return;

	// Skip uniquement si vraiment trop faible (mobile très ancien / reduced-motion).
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	var cores = navigator.hardwareConcurrency || 2;
	if (cores < 2) return;

	// Qualité adaptative : mobile / vieux CPU = version allégée mais visible.
	var lowSpec = cores < 4 || window.innerWidth < 768;
	var SHARD_COUNT = lowSpec ? 5 : 7;
	var PCOUNT = lowSpec ? 70 : 180;
	var ICOS_DETAIL = lowSpec ? 0 : 1;
	var MAX_DPR = lowSpec ? 1.2 : 1.5;

	function loadThree(cb) {
		if (window.THREE) { cb(); return; }
		var existing = document.querySelector('script[data-ag-three]');
		if (existing) { existing.addEventListener('load', cb); return; }
		var s = document.createElement('script');
		s.src = 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js';
		s.async = true;
		s.dataset.agThree = '1';
		s.onload = cb;
		document.head.appendChild(s);
	}

	function start() {
		if (!window.THREE) return;
		var T = window.THREE;
		var scene = new T.Scene();
		var camera = new T.PerspectiveCamera(45, host.clientWidth / host.clientHeight, 0.1, 200);
		// Caméra plus reculée + scène plus petite sur mobile : laisse de l'air pour le texte du hero
		camera.position.set(0, 0, lowSpec ? 55 : 38);
		var renderer = new T.WebGLRenderer({ canvas: canvas, antialias: !lowSpec, alpha: true });
		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, MAX_DPR));
		renderer.setSize(host.clientWidth, host.clientHeight, false);
		renderer.setClearColor(0x000000, 0);

		// ── Lumières ──
		scene.add(new T.AmbientLight(0xffffff, 0.35));
		var keyLight = new T.PointLight(0xd4b45c, 2.2, 80, 1.8); keyLight.position.set(14, 10, 14); scene.add(keyLight);
		var rimLight = new T.PointLight(0xf37a1f, 1.4, 70, 1.8); rimLight.position.set(-16, -8, 8); scene.add(rimLight);
		var topLight = new T.DirectionalLight(0xffffff, 0.4);    topLight.position.set(0, 20, 4);   scene.add(topLight);

		// ── Objet principal : SMARTPHONE 3D flottant (corps + écran lumineux) ──
		var phoneGroup = new T.Group();
		// Position décalée à droite + en bas pour ne pas chevaucher le texte du hero
		phoneGroup.position.set(lowSpec ? 8 : 12, lowSpec ? -3 : -2, 0);
		phoneGroup.rotation.set(-0.18, -0.42, 0.08);

		// Corps du téléphone (rectangle arrondi via BoxGeometry biseautée)
		var bodyGeo = new T.BoxGeometry(5.2, 10.4, 0.55);
		var bodyMat = new T.MeshStandardMaterial({
			color: 0x16161e,
			metalness: 0.85,
			roughness: 0.30
		});
		var body = new T.Mesh(bodyGeo, bodyMat);
		phoneGroup.add(body);

		// Liseré champagne sur le pourtour (cadre)
		var frameGeo = new T.BoxGeometry(5.4, 10.6, 0.45);
		var frameMat = new T.MeshStandardMaterial({
			color: 0xd4b45c, metalness: 0.95, roughness: 0.2,
			emissive: 0x4a3c1e, emissiveIntensity: 0.35
		});
		var frame = new T.Mesh(frameGeo, frameMat);
		frame.position.z = -0.06;
		phoneGroup.add(frame);

		// Écran (plane légèrement devant) avec gradient lumineux animé
		var screenCanvas = document.createElement('canvas');
		screenCanvas.width = 256; screenCanvas.height = 512;
		var sctx = screenCanvas.getContext('2d');
		function paintScreen(progress) {
			var g = sctx.createLinearGradient(0, 0, 0, 512);
			g.addColorStop(0, '#1a1a26');
			g.addColorStop(0.45 + Math.sin(progress * 2) * 0.08, '#3a2c12');
			g.addColorStop(0.6,  '#b8975a');
			g.addColorStop(0.85, '#f37a1f');
			g.addColorStop(1, '#1a1a26');
			sctx.fillStyle = g;
			sctx.fillRect(0, 0, 256, 512);
			// Lignes simulant le contenu d'un site
			sctx.fillStyle = 'rgba(255,255,255,.7)';
			sctx.fillRect(24, 48, 96, 18);   // logo
			sctx.fillRect(24, 84, 208, 8);   // title
			sctx.fillRect(24, 100, 184, 8);  // title 2
			sctx.fillStyle = 'rgba(255,220,140,.85)';
			sctx.fillRect(24, 140, 110, 28); // CTA button
			sctx.fillStyle = 'rgba(255,255,255,.32)';
			for (var i = 0; i < 6; i++) sctx.fillRect(24, 220 + i * 26, 208 - (i % 2) * 30, 6);
			sctx.fillStyle = 'rgba(212,180,92,.45)';
			sctx.fillRect(24, 400, 86, 76);
			sctx.fillRect(132, 400, 100, 76);
			screenTex.needsUpdate = true;
		}
		var screenTex = new T.CanvasTexture(screenCanvas);
		screenTex.minFilter = T.LinearFilter;
		var screenMat = new T.MeshBasicMaterial({ map: screenTex, transparent: false });
		var screenGeo = new T.PlaneGeometry(4.8, 10.0);
		var screen = new T.Mesh(screenGeo, screenMat);
		screen.position.z = 0.30;
		phoneGroup.add(screen);

		scene.add(phoneGroup);

		// ── Shards d'accent autour du téléphone ──
		var shards = [];
		for (var i = 0; i < SHARD_COUNT; i++) {
			var g = new T.TetrahedronGeometry(0.55 + Math.random() * 0.35, 0);
			var m = new T.MeshStandardMaterial({
				color: i % 2 === 0 ? 0xd4b45c : 0xf37a1f,
				metalness: 0.9, roughness: 0.3,
				emissive: i % 2 === 0 ? 0x6a5630 : 0x6a3a14, emissiveIntensity: 0.4,
				flatShading: true
			});
			var sh = new T.Mesh(g, m);
			sh.userData = {
				baseRadius: 9 + Math.random() * 4,
				angle: Math.random() * Math.PI * 2,
				speed: 0.003 + Math.random() * 0.004,
				yOffset: (Math.random() - 0.5) * 8,
				spin: { x: 0.005 + Math.random() * 0.01, y: 0.006 + Math.random() * 0.01, z: 0.003 + Math.random() * 0.008 }
			};
			scene.add(sh);
			shards.push(sh);
		}

		// ── Particules ──
		var pPositions = new Float32Array(PCOUNT * 3);
		for (var p = 0; p < PCOUNT; p++) {
			var r = 14 + Math.random() * 14;
			var theta = Math.random() * Math.PI * 2;
			var phi = Math.acos(2 * Math.random() - 1);
			pPositions[p * 3]     = r * Math.sin(phi) * Math.cos(theta);
			pPositions[p * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
			pPositions[p * 3 + 2] = r * Math.cos(phi);
		}
		var pGeo = new T.BufferGeometry();
		pGeo.setAttribute('position', new T.BufferAttribute(pPositions, 3));
		var pMat = new T.PointsMaterial({ color: 0xd4b45c, size: 0.06, transparent: true, opacity: 0.55, sizeAttenuation: true });
		var particles = new T.Points(pGeo, pMat);
		scene.add(particles);

		// ── État scroll + tilt souris ──
		var targetTilt = { x: 0, y: 0 }, currentTilt = { x: 0, y: 0 };
		var scrollProgress = 0;
		if (!lowSpec && host.parentElement) {
			host.parentElement.addEventListener('mousemove', function (e) {
				var rect = host.getBoundingClientRect();
				targetTilt.x = ((e.clientX - rect.left) / rect.width - 0.5) * 0.25;
				targetTilt.y = ((e.clientY - rect.top) / rect.height - 0.5) * 0.18;
			}, { passive: true });
			host.parentElement.addEventListener('mouseleave', function () { targetTilt.x = 0; targetTilt.y = 0; });
		}
		function updateScroll() {
			var r = host.getBoundingClientRect();
			var h = r.height || 1;
			scrollProgress = Math.max(0, Math.min(1.2, -r.top / h));
		}
		window.addEventListener('scroll', updateScroll, { passive: true });
		updateScroll();

		// ── Visibilité (pause hors viewport) ──
		var visible = true, rafId = null;
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				visible = entries[0].isIntersecting;
				if (visible && !rafId) frame();
			}, { threshold: 0 }).observe(host);
		}

		// ── Resize ──
		var ro = new ResizeObserver(function () {
			var w = host.clientWidth, h = host.clientHeight;
			if (!w || !h) return;
			camera.aspect = w / h;
			camera.updateProjectionMatrix();
			renderer.setSize(w, h, false);
		});
		ro.observe(host);

		// ── Boucle d'animation ──
		var t0 = performance.now();
		var lastScreenPaint = 0;
		paintScreen(0);
		function frame() {
			if (!visible) { rafId = null; return; }
			var t = (performance.now() - t0) * 0.001;
			var sp = scrollProgress;

			currentTilt.x += (targetTilt.x - currentTilt.x) * 0.06;
			currentTilt.y += (targetTilt.y - currentTilt.y) * 0.06;

			// Phone flottant : rotation douce + bobbing
			phoneGroup.rotation.y = -0.42 + currentTilt.x + Math.sin(t * 0.35) * 0.14 + sp * 0.6;
			phoneGroup.rotation.x = -0.18 + currentTilt.y + Math.sin(t * 0.5) * 0.07;
			phoneGroup.position.y = (lowSpec ? -3 : -2) + Math.sin(t * 0.7) * 0.5;

			// Repaint écran toutes les 0.15s (pulse "site qui vit")
			if (t - lastScreenPaint > 0.15) { paintScreen(t); lastScreenPaint = t; }

			// Shards en orbite (rapprochement au scroll = "assemblage")
			var assembly = 1 - sp * 0.4;
			for (var k = 0; k < shards.length; k++) {
				var s = shards[k], d = s.userData;
				d.angle += d.speed;
				var radius = d.baseRadius * assembly;
				s.position.x = Math.cos(d.angle) * radius;
				s.position.z = Math.sin(d.angle) * radius;
				s.position.y = d.yOffset * assembly + Math.sin(t * 0.6 + k) * 0.7;
				s.rotation.x += d.spin.x;
				s.rotation.y += d.spin.y;
				s.rotation.z += d.spin.z;
			}

			particles.rotation.y = t * 0.02 + sp * 0.5;
			particles.rotation.x = Math.sin(t * 0.15) * 0.05;

			// Caméra : très léger zoom-in au scroll
			camera.position.z = (lowSpec ? 55 : 38) - sp * 6;

			renderer.render(scene, camera);
			rafId = requestAnimationFrame(frame);
		}

		host.classList.add('is-ready');
		frame();
	}

	loadThree(start);
})();
</script>
