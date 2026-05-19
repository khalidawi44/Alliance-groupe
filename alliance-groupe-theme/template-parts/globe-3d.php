<?php
/**
 * Globe 3D Three.js avec markers Nantes / Marrakech / Naples.
 *
 * Lazy-load Three.js depuis CDN au moment où le globe entre dans le viewport.
 * Sphère wireframe + 3 markers pulsants + rotation lente auto.
 *
 * Inclure via `get_template_part('template-parts/globe-3d')`.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_globe_uid = 'agglobe-' . wp_rand( 1000, 9999 );

$ag_globe_markers = apply_filters( 'ag_globe_markers', array(
	array( 'lat' => 47.2184, 'lon' => -1.5536, 'name' => 'Nantes',    'url' => home_url( '/bureau-nantes' ) ),
	array( 'lat' => 31.6295, 'lon' => -7.9811, 'name' => 'Marrakech', 'url' => home_url( '/bureau-marrakech' ) ),
	array( 'lat' => 40.8518, 'lon' => 14.2681, 'name' => 'Naples',    'url' => home_url( '/bureau-naples' ) ),
) );
?>

<section class="ag-globe-section">
	<div class="ag-globe-inner">
		<div class="ag-globe-text">
			<span class="ag-globe-tag">🌍 Nos bureaux</span>
			<h2 class="ag-globe-title">Une présence <em>internationale</em></h2>
			<p class="ag-globe-lead">3 bureaux, 3 cultures, 1 vision. France, Maroc, Italie — l'agence qui pense votre projet à l'échelle de l'Europe et de l'Afrique.</p>
			<ul class="ag-globe-list">
				<?php foreach ( $ag_globe_markers as $i => $m ) : ?>
					<li><a href="<?php echo esc_url( $m['url'] ); ?>" data-marker="<?php echo (int) $i; ?>">
						<span class="ag-globe-dot"></span>
						<strong><?php echo esc_html( $m['name'] ); ?></strong>
						<span class="ag-globe-arrow">→</span>
					</a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="ag-globe-canvas-wrap" id="<?php echo esc_attr( $ag_globe_uid ); ?>"
		     data-markers="<?php echo esc_attr( wp_json_encode( $ag_globe_markers ) ); ?>">
			<canvas></canvas>
		</div>
	</div>
</section>

<style>
.ag-globe-section{background:linear-gradient(180deg,#0a0a0f 0%,#1a1a2e 100%);color:#fff;padding:100px 24px;position:relative;overflow:hidden}
.ag-globe-section::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 50%,rgba(212,180,92,.08) 0%,transparent 50%);pointer-events:none}
.ag-globe-inner{max-width:1300px;margin:0 auto;display:grid;grid-template-columns:1fr 1.2fr;gap:60px;align-items:center;position:relative;z-index:1}
@media (max-width:900px){.ag-globe-inner{grid-template-columns:1fr;gap:40px}}
.ag-globe-tag{display:inline-block;padding:6px 14px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#D4B45C;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:20px}
.ag-globe-title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2rem,4vw,3.4rem);font-weight:700;line-height:1.1;margin:0 0 18px;color:#fff}
.ag-globe-title em{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-globe-lead{font-size:1.05rem;line-height:1.7;color:rgba(255,255,255,.78);margin:0 0 32px;max-width:520px}
.ag-globe-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px}
.ag-globe-list a{display:flex;align-items:center;gap:14px;padding:14px 18px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:#fff;text-decoration:none;transition:all .25s ease;font-size:1rem}
.ag-globe-list a:hover{background:rgba(212,180,92,.1);border-color:rgba(212,180,92,.4);transform:translateX(6px);color:#fff;text-decoration:none}
.ag-globe-dot{width:10px;height:10px;background:#D4B45C;border-radius:50%;box-shadow:0 0 12px #D4B45C;flex-shrink:0;animation:agGlobeDot 2s ease-in-out infinite}
@keyframes agGlobeDot{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.6}}
.ag-globe-list strong{flex:1;font-weight:700;letter-spacing:.5px}
.ag-globe-arrow{color:#D4B45C;font-weight:700;transition:transform .3s ease}
.ag-globe-list a:hover .ag-globe-arrow{transform:translateX(6px)}
.ag-globe-canvas-wrap{aspect-ratio:1;max-width:600px;margin:0 auto;width:100%;position:relative}
.ag-globe-canvas-wrap canvas{display:block;width:100%;height:100%;cursor:grab}
.ag-globe-canvas-wrap canvas:active{cursor:grabbing}
</style>

<script>
(function(){
    var wrap = document.getElementById('<?php echo esc_js( $ag_globe_uid ); ?>');
    if (!wrap) return;
    var canvas = wrap.querySelector('canvas');
    var markers = JSON.parse(wrap.dataset.markers);
    var loaded = false;

    function loadThree(cb){
        if (window.THREE) { cb(); return; }
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    function init(){
        if (loaded) return; loaded = true;
        loadThree(function(){
            var THREE = window.THREE;
            var scene = new THREE.Scene();
            var aspect = canvas.clientWidth / canvas.clientHeight;
            var cam = new THREE.PerspectiveCamera(40, aspect, 0.1, 1000);
            cam.position.z = 4.2;
            var renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio||1, 2));
            renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);

            var globeGroup = new THREE.Group();
            scene.add(globeGroup);

            // Lights pour le rendu texturé Terre
            scene.add(new THREE.AmbientLight(0xffffff, 0.65));
            var dirLight = new THREE.DirectionalLight(0xffffff, 1.0);
            dirLight.position.set(5, 3, 5);
            scene.add(dirLight);

            // Terre texturée (jour, vraie texture earth)
            var loader = new THREE.TextureLoader();
            loader.crossOrigin = 'anonymous';
            var earthTexture = loader.load('https://threejs.org/examples/textures/planets/earth_atmos_2048.jpg');
            var bumpTexture = loader.load('https://threejs.org/examples/textures/planets/earth_normal_2048.jpg');
            var specTexture = loader.load('https://threejs.org/examples/textures/planets/earth_specular_2048.jpg');

            var sphere = new THREE.Mesh(
                new THREE.SphereGeometry(1.5, 64, 64),
                new THREE.MeshPhongMaterial({
                    map: earthTexture,
                    normalMap: bumpTexture,
                    specularMap: specTexture,
                    specular: new THREE.Color(0x333333),
                    shininess: 18
                })
            );
            globeGroup.add(sphere);

            // Subtle wireframe champagne overlay pour garder l'identité Alliance
            var grid = new THREE.Mesh(
                new THREE.SphereGeometry(1.51, 24, 24),
                new THREE.MeshBasicMaterial({ color: 0xD4B45C, wireframe: true, transparent: true, opacity: 0.06 })
            );
            globeGroup.add(grid);

            // Halo atmosphère orange (rétro-éclairage)
            var halo = new THREE.Mesh(
                new THREE.SphereGeometry(1.7, 32, 32),
                new THREE.ShaderMaterial({
                    transparent: true,
                    side: THREE.BackSide,
                    uniforms: { c: { value: new THREE.Color(0xF37A1F) } },
                    vertexShader: 'varying float i;void main(){vec3 n=normalize(normalMatrix*normal);i=pow(.6-dot(n,vec3(0.,0.,1.)),3.);gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}',
                    fragmentShader: 'uniform vec3 c;varying float i;void main(){gl_FragColor=vec4(c,1.)*i*.45;}'
                })
            );
            globeGroup.add(halo);

            // Markers
            function latLonToVec3(lat, lon, r){
                var phi = (90-lat) * Math.PI/180;
                var theta = (lon+180) * Math.PI/180;
                return new THREE.Vector3(
                    -r * Math.sin(phi) * Math.cos(theta),
                    r * Math.cos(phi),
                    r * Math.sin(phi) * Math.sin(theta)
                );
            }
            var markerMeshes = [];
            markers.forEach(function(m, i){
                var pos = latLonToVec3(m.lat, m.lon, 1.52);
                var pin = new THREE.Mesh(
                    new THREE.SphereGeometry(0.04, 16, 16),
                    new THREE.MeshBasicMaterial({ color: 0xF37A1F })
                );
                pin.position.copy(pos);
                globeGroup.add(pin);
                // Ring autour du pin
                var ring = new THREE.Mesh(
                    new THREE.RingGeometry(0.05, 0.09, 24),
                    new THREE.MeshBasicMaterial({ color: 0xF37A1F, side: THREE.DoubleSide, transparent: true })
                );
                ring.position.copy(pos);
                ring.lookAt(0,0,0);
                globeGroup.add(ring);
                markerMeshes.push({ pin: pin, ring: ring, delay: i * 0.7 });
            });

            // Drag rotation
            var auto = true;
            var dragging = false, lastX = 0, lastY = 0;
            var rotY = 0, rotX = 0.3;
            var targetRotY = 0, targetRotX = 0.3;

            canvas.addEventListener('mousedown', function(e){
                dragging = true; auto = false;
                lastX = e.clientX; lastY = e.clientY;
            });
            window.addEventListener('mouseup', function(){ dragging = false; });
            window.addEventListener('mousemove', function(e){
                if (!dragging) return;
                var dx = e.clientX - lastX, dy = e.clientY - lastY;
                targetRotY += dx * 0.005;
                targetRotX = Math.max(-1.2, Math.min(1.2, targetRotX + dy * 0.005));
                lastX = e.clientX; lastY = e.clientY;
            });
            canvas.addEventListener('touchstart', function(e){
                dragging = true; auto = false;
                lastX = e.touches[0].clientX; lastY = e.touches[0].clientY;
            }, { passive: true });
            canvas.addEventListener('touchmove', function(e){
                if (!dragging) return;
                var dx = e.touches[0].clientX - lastX, dy = e.touches[0].clientY - lastY;
                targetRotY += dx * 0.005;
                targetRotX = Math.max(-1.2, Math.min(1.2, targetRotX + dy * 0.005));
                lastX = e.touches[0].clientX; lastY = e.touches[0].clientY;
            }, { passive: true });
            canvas.addEventListener('touchend', function(){ dragging = false; });

            // Resize
            new ResizeObserver(function(){
                renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);
                cam.aspect = canvas.clientWidth / canvas.clientHeight;
                cam.updateProjectionMatrix();
            }).observe(canvas);

            // Animation (paused quand sort du viewport pour économiser CPU)
            var t = 0, visible = true, rafId = null;
            new IntersectionObserver(function(entries){
                visible = entries[0].isIntersecting;
                if (visible && !rafId) loop();
            }, { threshold: 0 }).observe(canvas);

            function loop(){
                if (!visible) { rafId = null; return; }
                t += 0.01;
                if (auto){ targetRotY += 0.002; }
                rotY += (targetRotY - rotY) * 0.08;
                rotX += (targetRotX - rotX) * 0.08;
                globeGroup.rotation.y = rotY;
                globeGroup.rotation.x = rotX;

                markerMeshes.forEach(function(mk, i){
                    var pulse = 1 + 0.4 * Math.sin(t * 2 + mk.delay);
                    mk.ring.scale.set(pulse, pulse, 1);
                    mk.ring.material.opacity = 1 - (pulse - 0.6) * 1.5;
                });

                renderer.render(scene, cam);
                rafId = requestAnimationFrame(loop);
            }
            loop();
        });
    }

    if ('IntersectionObserver' in window){
        var io = new IntersectionObserver(function(entries){
            entries.forEach(function(e){ if (e.isIntersecting){ init(); io.disconnect(); }});
        }, { rootMargin: '200px' });
        io.observe(wrap);
    } else {
        init();
    }
})();
</script>
