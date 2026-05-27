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

	// Garde-fous perf : on n'embarque même pas Three.js si la machine ne suit pas.
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	if (window.innerWidth < 1100) return;
	if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) return;

	function loadThree(cb) {
		if (window.THREE) { cb(); return; }
		// Si déjà en cours de chargement par globe-3d.php, attendre.
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
		camera.position.set(0, 0, 32);
		var renderer = new T.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
		renderer.setSize(host.clientWidth, host.clientHeight, false);
		renderer.setClearColor(0x000000, 0);

		// ── Lumières (champagne + orange chaud) ──
		scene.add(new T.AmbientLight(0xffffff, 0.18));
		var keyLight = new T.PointLight(0xd4b45c, 2.6, 80, 1.8); keyLight.position.set(14, 10, 14); scene.add(keyLight);
		var rimLight = new T.PointLight(0xf37a1f, 1.7, 70, 1.8); rimLight.position.set(-16, -8, 8); scene.add(rimLight);
		var topLight = new T.DirectionalLight(0xffffff, 0.25); topLight.position.set(0, 20, 4); scene.add(topLight);

		// ── Objet central : icosaèdre champagne brillant ──
		var coreGeo = new T.IcosahedronGeometry(5.5, 1);
		var coreMat = new T.MeshStandardMaterial({
			color: 0xb8975a,
			metalness: 0.85,
			roughness: 0.22,
			emissive: 0x4a3c1e,
			emissiveIntensity: 0.45,
			flatShading: true
		});
		var core = new T.Mesh(coreGeo, coreMat);
		scene.add(core);

		// Halo wireframe autour du core (effet bloom léger)
		var haloGeo = new T.IcosahedronGeometry(6.1, 1);
		var haloMat = new T.MeshBasicMaterial({ color: 0xd4b45c, wireframe: true, transparent: true, opacity: 0.18 });
		var halo = new T.Mesh(haloGeo, haloMat);
		scene.add(halo);

		// ── Shards en orbite (tétraèdres dorés) ──
		var shards = [];
		var SHARD_COUNT = 7;
		for (var i = 0; i < SHARD_COUNT; i++) {
			var g = new T.TetrahedronGeometry(0.9 + Math.random() * 0.5, 0);
			var m = new T.MeshStandardMaterial({
				color: i % 2 === 0 ? 0xd4b45c : 0xf37a1f,
				metalness: 0.9,
				roughness: 0.3,
				emissive: i % 2 === 0 ? 0x6a5630 : 0x6a3a14,
				emissiveIntensity: 0.4,
				flatShading: true
			});
			var sh = new T.Mesh(g, m);
			sh.userData = {
				radius: 10 + Math.random() * 5,
				angle: Math.random() * Math.PI * 2,
				speed: 0.0025 + Math.random() * 0.004,
				yOffset: (Math.random() - 0.5) * 6,
				spin: { x: 0.005 + Math.random() * 0.01, y: 0.006 + Math.random() * 0.01, z: 0.003 + Math.random() * 0.008 },
				tilt: Math.random() * 0.6 - 0.3
			};
			scene.add(sh);
			shards.push(sh);
		}

		// ── Particules (poussière dorée) ──
		var PCOUNT = 180;
		var pPositions = new Float32Array(PCOUNT * 3);
		for (var p = 0; p < PCOUNT; p++) {
			var r = 16 + Math.random() * 14;
			var theta = Math.random() * Math.PI * 2;
			var phi = Math.acos(2 * Math.random() - 1);
			pPositions[p * 3]     = r * Math.sin(phi) * Math.cos(theta);
			pPositions[p * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
			pPositions[p * 3 + 2] = r * Math.cos(phi);
		}
		var pGeo = new T.BufferGeometry();
		pGeo.setAttribute('position', new T.BufferAttribute(pPositions, 3));
		var pMat = new T.PointsMaterial({ color: 0xd4b45c, size: 0.07, transparent: true, opacity: 0.65, sizeAttenuation: true });
		var particles = new T.Points(pGeo, pMat);
		scene.add(particles);

		// ── État interaction souris (tilt très doux) ──
		var targetTilt = { x: 0, y: 0 }, currentTilt = { x: 0, y: 0 };
		host.parentElement && host.parentElement.addEventListener('mousemove', function (e) {
			var rect = host.getBoundingClientRect();
			targetTilt.x = ((e.clientX - rect.left) / rect.width - 0.5) * 0.18;
			targetTilt.y = ((e.clientY - rect.top) / rect.height - 0.5) * 0.12;
		}, { passive: true });
		host.parentElement && host.parentElement.addEventListener('mouseleave', function () { targetTilt.x = 0; targetTilt.y = 0; });

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
		function frame() {
			if (!visible) { rafId = null; return; }
			var t = (performance.now() - t0) * 0.001;

			// Damping du tilt souris
			currentTilt.x += (targetTilt.x - currentTilt.x) * 0.06;
			currentTilt.y += (targetTilt.y - currentTilt.y) * 0.06;
			scene.rotation.y = currentTilt.x + t * 0.08;
			scene.rotation.x = currentTilt.y + Math.sin(t * 0.3) * 0.05;

			core.rotation.x += 0.0025;
			core.rotation.y += 0.0035;
			core.position.y = Math.sin(t * 0.7) * 0.4;
			halo.rotation.x -= 0.0015;
			halo.rotation.y -= 0.002;
			halo.scale.setScalar(1 + Math.sin(t * 1.1) * 0.02);

			for (var k = 0; k < shards.length; k++) {
				var s = shards[k], d = s.userData;
				d.angle += d.speed;
				s.position.x = Math.cos(d.angle) * d.radius;
				s.position.z = Math.sin(d.angle) * d.radius;
				s.position.y = d.yOffset + Math.sin(t * 0.6 + k) * 0.7;
				s.rotation.x += d.spin.x;
				s.rotation.y += d.spin.y;
				s.rotation.z += d.spin.z;
			}

			particles.rotation.y = t * 0.02;
			particles.rotation.x = Math.sin(t * 0.15) * 0.05;

			renderer.render(scene, camera);
			rafId = requestAnimationFrame(frame);
		}

		host.classList.add('is-ready');
		frame();
	}

	loadThree(start);
})();
</script>
