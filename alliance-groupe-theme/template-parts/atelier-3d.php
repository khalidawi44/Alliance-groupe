<?php
/**
 * Section "L'atelier" : joyau 3D Three.js (crystal champagne qui tourne
 * avec cristaux satellites en orbite). Calqué sur la structure de
 * template-parts/globe-3d.php — DEDIÉE à sa propre section, lazy-loadée
 * via IntersectionObserver, Three.js partagé via jsDelivr.
 *
 * Différent du globe : objet abstrait premium (Dodecahedron + Octahedrons
 * satellites) avec matériaux métalliques champagne/orange.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_atelier_uid = 'agatelier-' . wp_rand( 1000, 9999 );
?>

<section class="ag-atelier-section">
	<div class="ag-atelier-inner">
		<div class="ag-atelier-text">
			<span class="ag-atelier-tag">L'atelier</span>
			<h2 class="ag-atelier-title">L'<em>artisanat</em> du web</h2>
			<p class="ag-atelier-lead">Chaque site qui sort de l'atelier est taillé à la main, poli, ajusté. Ni template générique, ni copier-coller — du <strong>sur-mesure premium</strong>, livré en 7 jours.</p>
			<ul class="ag-atelier-list">
				<li><span class="ag-atelier-dot"></span><strong>Code propre, sans bloat</strong> — site rapide qui charge en 1s.</li>
				<li><span class="ag-atelier-dot"></span><strong>SEO natif & RGPD ready</strong> — Google et la CNIL contents.</li>
				<li><span class="ag-atelier-dot"></span><strong>Design unique par projet</strong> — votre identité, pas un thème en vrac.</li>
				<li><span class="ag-atelier-dot"></span><strong>Hébergement & maintenance inclus</strong> — vous ne touchez à rien.</li>
			</ul>
			<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-atelier-cta">Voir nos offres <span>→</span></a>
		</div>
		<div class="ag-atelier-canvas-wrap" id="<?php echo esc_attr( $ag_atelier_uid ); ?>">
			<canvas></canvas>
		</div>
	</div>
</section>

<style>
.ag-atelier-section{background:linear-gradient(180deg,#0a0a0f 0%,#14141c 60%,#080810 100%);color:#fff;padding:100px 24px;position:relative;overflow:hidden}
.ag-atelier-section::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(243,122,31,.07) 0%,transparent 55%),radial-gradient(circle at 75% 30%,rgba(212,180,92,.10) 0%,transparent 60%);pointer-events:none}
.ag-atelier-inner{max-width:1300px;margin:0 auto;display:grid;grid-template-columns:1.1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1}
@media (max-width:900px){.ag-atelier-inner{grid-template-columns:1fr;gap:30px}}
.ag-atelier-tag{display:inline-block;padding:6px 14px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#D4B45C;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:20px}
.ag-atelier-title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2rem,4vw,3.4rem);font-weight:700;line-height:1.1;margin:0 0 18px;color:#fff}
.ag-atelier-title em{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-atelier-lead{font-size:1.05rem;line-height:1.7;color:rgba(255,255,255,.78);margin:0 0 26px;max-width:520px}
.ag-atelier-list{list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px}
.ag-atelier-list li{display:flex;align-items:flex-start;gap:12px;font-size:.96rem;line-height:1.5;color:rgba(255,255,255,.92)}
.ag-atelier-list strong{color:#fff;font-weight:700}
.ag-atelier-dot{width:9px;height:9px;background:#D4B45C;border-radius:50%;box-shadow:0 0 10px #D4B45C;flex-shrink:0;margin-top:7px;animation:agAtelierDot 2.4s ease-in-out infinite}
@keyframes agAtelierDot{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.35);opacity:.55}}
.ag-atelier-cta{display:inline-flex;align-items:center;gap:10px;padding:14px 30px;background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f;font-weight:800;border-radius:999px;text-decoration:none;letter-spacing:1px;text-transform:uppercase;font-size:.88rem;box-shadow:0 10px 30px rgba(212,180,92,.3);transition:transform .25s,box-shadow .25s}
.ag-atelier-cta:hover{transform:translateY(-2px);box-shadow:0 14px 40px rgba(212,180,92,.5);color:#0a0a0f;text-decoration:none}
.ag-atelier-cta span{transition:transform .25s}
.ag-atelier-cta:hover span{transform:translateX(4px)}
.ag-atelier-canvas-wrap{aspect-ratio:1;max-width:520px;margin:0 auto;width:100%;position:relative}
.ag-atelier-canvas-wrap canvas{display:block;width:100%;height:100%}
</style>

<script>
(function(){
	var wrap = document.getElementById('<?php echo esc_js( $ag_atelier_uid ); ?>');
	if (!wrap) return;
	var canvas = wrap.querySelector('canvas');

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

	var loaded = false;
	function init(){
		if (loaded) return; loaded = true;
		loadThree(function(){
			if (!window.THREE) return;
			var T = window.THREE;
			var W = wrap.clientWidth, H = wrap.clientHeight;
			var renderer = new T.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
			renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
			renderer.setSize(W, H, false);
			renderer.setClearColor(0x000000, 0);

			var scene = new T.Scene();
			var camera = new T.PerspectiveCamera(45, W/H, 0.1, 100);
			camera.position.set(0, 0, 18);

			// Lumières
			scene.add(new T.AmbientLight(0xffffff, 0.35));
			var l1 = new T.PointLight(0xd4b45c, 2.4, 50, 1.6); l1.position.set(8, 6, 8); scene.add(l1);
			var l2 = new T.PointLight(0xf37a1f, 1.6, 45, 1.6); l2.position.set(-8, -4, 6); scene.add(l2);
			scene.add(new T.DirectionalLight(0xffffff, 0.4));

			// Joyau central — Dodécaèdre champagne métal
			var gemGeo = new T.DodecahedronGeometry(3.5, 0);
			var gemMat = new T.MeshStandardMaterial({
				color: 0xc9a866,
				metalness: 0.95,
				roughness: 0.18,
				emissive: 0x4a3c1e,
				emissiveIntensity: 0.30,
				flatShading: true
			});
			var gem = new T.Mesh(gemGeo, gemMat);
			scene.add(gem);

			// Halo wireframe doré autour du gem
			var haloGeo = new T.DodecahedronGeometry(4.2, 0);
			var haloMat = new T.MeshBasicMaterial({ color: 0xd4b45c, wireframe: true, transparent: true, opacity: 0.22 });
			var halo = new T.Mesh(haloGeo, haloMat);
			scene.add(halo);

			// Cristaux satellites (octaèdres) en orbite
			var sats = [];
			for (var i = 0; i < 5; i++) {
				var sg = new T.OctahedronGeometry(0.55 + Math.random() * 0.25, 0);
				var sm = new T.MeshStandardMaterial({
					color: i % 2 === 0 ? 0xd4b45c : 0xf37a1f,
					metalness: 0.92, roughness: 0.22,
					emissive: i % 2 === 0 ? 0x6a5630 : 0x6a3a14, emissiveIntensity: 0.35,
					flatShading: true
				});
				var sat = new T.Mesh(sg, sm);
				sat.userData = {
					radius: 6 + Math.random() * 1.5,
					angle: (i / 5) * Math.PI * 2 + Math.random() * 0.5,
					speed: 0.004 + Math.random() * 0.003,
					tiltY: Math.random() * 1.5 - 0.75,
					spinX: 0.01 + Math.random() * 0.015,
					spinY: 0.012 + Math.random() * 0.015
				};
				scene.add(sat);
				sats.push(sat);
			}

			// Particules dorées
			var PCOUNT = 90;
			var pPos = new Float32Array(PCOUNT * 3);
			for (var p = 0; p < PCOUNT; p++) {
				var r = 8 + Math.random() * 6;
				var th = Math.random() * Math.PI * 2;
				var ph = Math.acos(2 * Math.random() - 1);
				pPos[p*3]   = r * Math.sin(ph) * Math.cos(th);
				pPos[p*3+1] = r * Math.sin(ph) * Math.sin(th);
				pPos[p*3+2] = r * Math.cos(ph);
			}
			var pGeo = new T.BufferGeometry();
			pGeo.setAttribute('position', new T.BufferAttribute(pPos, 3));
			var pMat = new T.PointsMaterial({ color: 0xd4b45c, size: 0.06, transparent: true, opacity: 0.6, sizeAttenuation: true });
			var particles = new T.Points(pGeo, pMat);
			scene.add(particles);

			// Resize
			new ResizeObserver(function(){
				var w = wrap.clientWidth, h = wrap.clientHeight;
				if (!w || !h) return;
				camera.aspect = w/h;
				camera.updateProjectionMatrix();
				renderer.setSize(w, h, false);
			}).observe(wrap);

			// Pause hors viewport
			var visible = true, raf = null;
			new IntersectionObserver(function(e){
				visible = e[0].isIntersecting;
				if (visible && !raf) loop();
			}, { threshold: 0 }).observe(wrap);

			var t0 = performance.now();
			function loop(){
				if (!visible) { raf = null; return; }
				var t = (performance.now() - t0) * 0.001;
				gem.rotation.x = t * 0.20;
				gem.rotation.y = t * 0.28;
				gem.position.y = Math.sin(t * 0.7) * 0.25;
				halo.rotation.x = -t * 0.10;
				halo.rotation.y = -t * 0.16;
				halo.scale.setScalar(1 + Math.sin(t * 1.2) * 0.04);
				for (var k = 0; k < sats.length; k++) {
					var s = sats[k], d = s.userData;
					d.angle += d.speed;
					s.position.x = Math.cos(d.angle) * d.radius;
					s.position.z = Math.sin(d.angle) * d.radius;
					s.position.y = d.tiltY + Math.sin(t * 0.8 + k) * 0.45;
					s.rotation.x += d.spinX;
					s.rotation.y += d.spinY;
				}
				particles.rotation.y = t * 0.03;
				renderer.render(scene, camera);
				raf = requestAnimationFrame(loop);
			}
			loop();
		});
	}

	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function(entries){
			if (entries[0].isIntersecting) { io.disconnect(); init(); }
		}, { threshold: 0.05 });
		io.observe(wrap);
	} else {
		init();
	}
})();
</script>
