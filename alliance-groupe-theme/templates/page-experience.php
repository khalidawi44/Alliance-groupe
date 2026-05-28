<?php
/**
 * Template Name: Expérience immersive (style theirisk.com)
 *
 * "Le Voyage Alliance" — parcours immersif en 4 stations, modèles 3D
 * réels (.glb) chargés en lazy via GLTFLoader (modules ES) :
 *   0. Bureau   — macbook_pro_2021.glb (+ vidéo Naples en fond)
 *   1. Vésuve   — mt._vesuvius_italy.glb (lourd : lazy + spinner)
 *   2. Marrakech— marrakech-tower.glb + moroccan_street_light.glb
 *   3. Espace   — need_some_space.glb + menu de pages cliquables
 *
 * @package Alliance_Groupe_Theme
 */

get_header();
$base   = get_stylesheet_directory_uri() . '/assets/images/img_3d/';
$office = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg';
$vid    = get_stylesheet_directory_uri() . '/assets/images/video/naples.mp4';
$music  = get_option( 'ag_xp_music', get_stylesheet_directory_uri() . '/assets/audio/naples.mp3' );

$menu = array(
	array( 'l' => 'Sites Express', 'u' => home_url( '/sites-express' ) ),
	array( 'l' => 'Templates', 'u' => home_url( '/templates-wordpress' ) ),
	array( 'l' => 'Cadeaux', 'u' => home_url( '/audit-seo' ) ),
	array( 'l' => 'Ambassadeurs', 'u' => home_url( '/ambassadeurs' ) ),
	array( 'l' => 'Sur-mesure', 'u' => home_url( '/sur-mesure' ) ),
	array( 'l' => 'Contact', 'u' => home_url( '/contact' ) ),
);
?>

<style>
body.page-template-page-experience{background:#05060a}
body.page-template-page-experience .ag-nav,
body.page-template-page-experience footer,
body.page-template-page-experience .ag-fsm-toggle{display:none!important}

.agx{position:fixed;inset:0;width:100vw;height:100vh;overflow:hidden;z-index:50;background:#05060a;color:#fff;font-family:'Helvetica Neue',Arial,sans-serif;touch-action:pan-y}
.agx__media{position:absolute;inset:0;z-index:0}
.agx__media video,.agx__media img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity 1s ease}
.agx__media .is-on{opacity:1}
.agx__veil{position:absolute;inset:0;z-index:1;background:radial-gradient(ellipse 80% 75% at 50% 45%,transparent 0%,transparent 45%,rgba(5,6,10,.55) 82%,rgba(3,4,8,.95) 100%),linear-gradient(180deg,rgba(5,6,10,.35) 0%,transparent 30%,transparent 62%,rgba(4,4,8,.85) 100%)}
.agx__canvas{position:absolute;inset:0;width:100%;height:100%;z-index:2}

.agx__cap{position:absolute;left:0;right:0;top:10vh;text-align:center;padding:0 24px;z-index:5;pointer-events:none;transition:opacity .55s ease,transform .55s cubic-bezier(.22,1,.36,1)}
.agx__cap.is-out{opacity:0;transform:translateY(-26px)}
.agx__cap .pre,.agx__cap .ttl,.agx__cap .line{opacity:0;transform:translateY(22px);animation:agx-rise .7s cubic-bezier(.22,1,.36,1) forwards}
.agx__cap .ttl{animation-delay:.08s}.agx__cap .line{animation-delay:.16s}
@keyframes agx-rise{to{opacity:1;transform:translateY(0)}}
.agx__cap .pre{font-size:clamp(.7rem,1.2vw,.9rem);letter-spacing:6px;color:#D4B45C;text-shadow:0 2px 14px #000;margin-bottom:12px}
.agx__cap .ttl{font-family:Georgia,serif;font-size:clamp(2rem,6.5vw,4.6rem);line-height:1.05;margin:0;text-shadow:0 4px 40px rgba(0,0,0,.95);background:linear-gradient(180deg,#fff,#e8dcc0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.agx__cap .line{margin:14px auto 0;max-width:560px;font-family:Georgia,serif;font-style:italic;font-size:clamp(.95rem,1.5vw,1.2rem);color:rgba(255,255,255,.85);text-shadow:0 2px 16px #000}

/* Menu (station espace) */
.agx__menu{position:absolute;left:0;right:0;bottom:14vh;z-index:5;display:flex;justify-content:center;flex-wrap:wrap;gap:12px;padding:0 24px;opacity:0;transition:opacity .8s ease;pointer-events:none}
.agx__menu.is-on{opacity:1;pointer-events:auto}
.agx__menu a{padding:14px 24px;background:rgba(10,10,15,.5);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#fff;text-decoration:none;font-family:Georgia,serif;font-size:1rem;transition:transform .25s,background .25s,border-color .25s}
.agx__menu a:hover{transform:translateY(-3px);background:rgba(212,180,92,.18);border-color:#D4B45C;color:#fff;text-decoration:none}

.agx__enter{position:absolute;bottom:15vh;left:50%;transform:translateX(-50%);z-index:6;display:inline-flex;align-items:center;gap:10px;padding:16px 40px;border:1px solid rgba(212,180,92,.7);border-radius:999px;background:rgba(10,10,15,.4);backdrop-filter:blur(8px);color:#D4B45C;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:.85rem;text-decoration:none;cursor:pointer;transition:.3s}
.agx__enter:hover{background:#D4B45C;color:#0a0a0f;text-decoration:none;transform:translateX(-50%) translateY(-3px)}
.agx__enter.is-hidden{opacity:0;pointer-events:none}

.agx__nav{position:absolute;bottom:42px;left:0;right:0;display:flex;justify-content:center;align-items:center;gap:26px;z-index:7;font-size:.78rem;letter-spacing:3px;font-weight:600;color:rgba(255,255,255,.6)}
.agx__nav button{background:none;border:none;color:rgba(255,255,255,.6);font:inherit;letter-spacing:inherit;cursor:pointer;padding:8px 14px;transition:color .25s}
.agx__nav button:hover{color:#fff}
.agx__nav button:disabled{opacity:.25;cursor:not-allowed}
.agx__nav .ct{color:rgba(255,255,255,.4);font-variant-numeric:tabular-nums}
.agx__sound{position:absolute;top:22px;right:22px;z-index:8;background:rgba(10,10,15,.45);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);color:#D4B45C;border-radius:999px;padding:9px 16px;font-size:.72rem;letter-spacing:2px;cursor:pointer;text-transform:uppercase}
.agx__sound:hover{color:#fff;border-color:#D4B45C}

.agx__loader{position:absolute;inset:0;z-index:9;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;background:rgba(5,6,10,.55);opacity:0;visibility:hidden;transition:opacity .3s ease}
.agx__loader.is-on{opacity:1;visibility:visible}
.agx__spin{width:46px;height:46px;border:3px solid rgba(212,180,92,.25);border-top-color:#D4B45C;border-radius:50%;animation:agx-spin 1s linear infinite}
@keyframes agx-spin{to{transform:rotate(360deg)}}
.agx__loader p{color:rgba(255,255,255,.7);letter-spacing:2px;font-size:.8rem}
.agx__hint{position:absolute;bottom:8vh;left:0;right:0;text-align:center;font-size:.7rem;letter-spacing:3px;color:rgba(255,255,255,.4);text-transform:uppercase;pointer-events:none;text-shadow:0 1px 10px #000;z-index:6}
.agx__warp{position:absolute;inset:0;z-index:10;pointer-events:none;opacity:0;background:radial-gradient(circle at 50% 50%,rgba(255,245,225,.95) 0%,rgba(212,180,92,.7) 30%,rgba(243,122,31,.35) 55%,rgba(5,6,10,0) 78%);transition:opacity .5s ease}
.agx__warp.is-on{opacity:1}
</style>

<main class="agx" id="agx">
	<div class="agx__media" id="agx-media">
		<video id="agx-video" muted loop playsinline preload="none" poster="<?php echo esc_url( $office ); ?>">
			<source src="<?php echo esc_url( $vid ); ?>" type="video/mp4">
		</video>
		<img id="agx-img" src="<?php echo esc_url( $office ); ?>" alt="" class="is-on">
	</div>
	<div class="agx__veil"></div>
	<canvas class="agx__canvas" id="agx-canvas"></canvas>

	<div class="agx__cap" id="agx-cap">
		<div class="pre" id="agx-pre">BIENVENUE CHEZ —</div>
		<h1 class="ttl" id="agx-ttl">Alliance Groupe</h1>
		<div class="line" id="agx-line">Agence web &amp; IA · Nantes · Naples · Marrakech.</div>
	</div>

	<div class="agx__menu" id="agx-menu">
		<?php foreach ( $menu as $m ) : ?>
			<a href="<?php echo esc_url( $m['u'] ); ?>"><?php echo esc_html( $m['l'] ); ?></a>
		<?php endforeach; ?>
	</div>

	<a href="#" class="agx__enter" id="agx-enter">Commencer le voyage →</a>

	<div class="agx__warp" id="agx-warp"></div>
	<div class="agx__loader" id="agx-loader"><div class="agx__spin"></div><p>Chargement du modèle 3D…</p></div>

	<div class="agx__nav">
		<button id="agx-back">◂ BACK</button>
		<span class="ct"><span id="agx-cur">01</span> / 04</span>
		<button id="agx-next">NEXT ▸</button>
	</div>
	<button class="agx__sound" id="agx-sound" type="button">♪ Son</button>
	<audio id="agx-audio" loop preload="none" src="<?php echo esc_url( $music ); ?>"></audio>
	<div class="agx__hint" id="agx-hint">← Glissez ou NEXT →</div>
</main>

<script type="importmap">
{
  "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
  }
}
</script>

<script type="module">
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

const BASE = '<?php echo esc_js( $base ); ?>';
const STATIONS = [
	{ pre:'BIENVENUE CHEZ —', ttl:'Alliance Groupe', line:"C'est ici que votre projet prend vie.", model:'macbook_pro_2021.glb', size:7, media:'video' },
	{ pre:'🌋 NOTRE ÉNERGIE', ttl:'Le Vésuve', line:'La force napolitaine qui ne s’éteint jamais.', model:'mt._vesuvius_italy.glb', size:9, media:'dark' },
	{ pre:'🇲🇦 NOTRE PÔLE SUD', ttl:'Marrakech', line:'SEO, IA, création — notre studio au soleil.', model:'marrakech-tower.glb', extra:'moroccan_street_light.glb', size:9, media:'dark' },
	{ pre:'✦ À VOUS DE JOUER', ttl:'L’Univers Alliance', line:'Choisissez votre destination.', model:'need_some_space.glb', size:14, media:'space', menu:true }
];

const host = document.getElementById('agx');
const canvas = document.getElementById('agx-canvas');
const elPre = document.getElementById('agx-pre'), elTtl = document.getElementById('agx-ttl'), elLine = document.getElementById('agx-line');
const elCap = document.getElementById('agx-cap'), elMenu = document.getElementById('agx-menu'), elEnter = document.getElementById('agx-enter');
const elLoader = document.getElementById('agx-loader'), elCur = document.getElementById('agx-cur'), elWarp = document.getElementById('agx-warp');
const bN = document.getElementById('agx-next'), bB = document.getElementById('agx-back'), elHint = document.getElementById('agx-hint');
const elVideo = document.getElementById('agx-video'), elImg = document.getElementById('agx-img');
const elSound = document.getElementById('agx-sound'), audio = document.getElementById('agx-audio');

let cur = 0, current3D = null, busy = false;
const cache = {};
const W = () => host.clientWidth, H = () => host.clientHeight;
const wait = ms => new Promise(r => setTimeout(r, ms));
const ease = k => k < .5 ? 2*k*k : 1 - Math.pow(-2*k+2, 2)/2;
function tween(dur, onUpdate, onDone){
	const s = performance.now();
	(function f(t){ const k = Math.min(1, (t-s)/dur); onUpdate(ease(k)); if (k < 1) requestAnimationFrame(f); else onDone && onDone(); })(performance.now());
}
function replayCap(){ [elPre, elTtl, elLine].forEach(el => { el.style.animation = 'none'; void el.offsetWidth; el.style.animation = ''; }); }

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.6));
renderer.setSize(W(), H(), false);
renderer.outputColorSpace = THREE.SRGBColorSpace;
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(45, W()/H(), 0.1, 1000);
camera.position.set(0, 1, 16);

const pmrem = new THREE.PMREMGenerator(renderer);
scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
scene.add(new THREE.AmbientLight(0xffffff, 0.5));
const key = new THREE.DirectionalLight(0xfff0d8, 2.2); key.position.set(6, 9, 8); scene.add(key);
const warm = new THREE.PointLight(0xf37a1f, 60, 80); warm.position.set(-9, 2, 6); scene.add(warm);

const gltf = new GLTFLoader();
const draco = new DRACOLoader();
draco.setDecoderPath('https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/libs/draco/');
gltf.setDRACOLoader(draco);

function fit(obj, target){
	const box = new THREE.Box3().setFromObject(obj);
	const size = box.getSize(new THREE.Vector3());
	const center = box.getCenter(new THREE.Vector3());
	const maxDim = Math.max(size.x, size.y, size.z) || 1;
	const s = target / maxDim;
	obj.scale.setScalar(s);
	obj.position.x = -center.x * s;
	obj.position.y = -center.y * s;
	obj.position.z = -center.z * s;
}

function loadStation(i){
	const st = STATIONS[i];
	if (cache[i]) return Promise.resolve(cache[i]);
	elLoader.classList.add('is-on');
	const grp = new THREE.Group();
	const jobs = [ new Promise(res => gltf.load(BASE + st.model, g => { fit(g.scene, st.size); grp.add(g.scene); res(); }, undefined, () => res())) ];
	if (st.extra) jobs.push(new Promise(res => gltf.load(BASE + st.extra, g => { g.scene.scale.setScalar(0.05); g.scene.position.set(5, -4, 2); grp.add(g.scene); res(); }, undefined, () => res())));
	return Promise.all(jobs).then(() => { cache[i] = grp; elLoader.classList.remove('is-on'); return grp; });
}

function setMedia(mode){
	if (mode === 'video'){ elImg.classList.remove('is-on'); elVideo.classList.add('is-on'); elVideo.play().catch(()=>{}); }
	else { elVideo.classList.remove('is-on'); if (mode === 'space'){ elImg.classList.remove('is-on'); } else { elImg.classList.add('is-on'); } }
}

async function go(i, instant){
	if (busy) return;
	i = Math.max(0, Math.min(STATIONS.length-1, i));
	busy = true;
	const st = STATIONS[i];
	if (!instant){ elCap.classList.add('is-out'); elWarp.classList.add('is-on'); await wait(380); }
	cur = i;
	elPre.textContent = st.pre; elTtl.textContent = st.ttl; elLine.textContent = st.line;
	elCur.textContent = String(i+1).padStart(2,'0');
	bB.disabled = (i===0); bN.disabled = (i===STATIONS.length-1);
	elEnter.classList.toggle('is-hidden', i!==0);
	elMenu.classList.toggle('is-on', !!st.menu);
	elHint.style.opacity = (i===STATIONS.length-1) ? '0' : '';
	setMedia(st.media);
	if (current3D){ scene.remove(current3D); current3D = null; }
	const grp = await loadStation(i);
	if (cur === i){
		current3D = grp; grp.scale.setScalar(0.55); scene.add(grp);
		tween(720, k => grp.scale.setScalar(0.55 + 0.45*k), () => grp.scale.setScalar(1));
	}
	elCap.classList.remove('is-out'); replayCap();
	elWarp.classList.remove('is-on');
	busy = false;
}

function plunge(){
	if (busy) return;
	busy = true;
	const z0 = camera.position.z, y0 = camera.position.y;
	elHint.style.opacity = '0'; elEnter.classList.add('is-hidden'); elCap.classList.add('is-out');
	tween(950, k => {
		camera.position.z = z0 + (2.4 - z0)*k;
		camera.position.y = y0 + (0.25 - y0)*k;
		camera.fov = 45 - 13*k; camera.updateProjectionMatrix();
		if (k > 0.5) elWarp.classList.add('is-on');
	}, () => {
		camera.position.set(0, 1, 16); camera.fov = 45; camera.updateProjectionMatrix();
		busy = false; go(1);
	});
}

bN.addEventListener('click', ()=>go(cur+1));
bB.addEventListener('click', ()=>go(cur-1));
elEnter.addEventListener('click', e=>{ e.preventDefault(); plunge(); });
document.addEventListener('keydown', e=>{ if(e.key==='ArrowRight')go(cur+1); else if(e.key==='ArrowLeft')go(cur-1); });
let x0=null;
host.addEventListener('touchstart', e=>{ x0=e.touches[0].clientX; }, {passive:true});
host.addEventListener('touchend', e=>{ if(x0===null)return; const dx=e.changedTouches[0].clientX-x0; if(Math.abs(dx)>55) go(cur+(dx<0?1:-1)); x0=null; }, {passive:true});

let ttX=0,tX=0;
host.addEventListener('mousemove', e=>{ const r=host.getBoundingClientRect(); ttX=((e.clientX-r.left)/r.width-0.5)*0.6; }, {passive:true});

let on=false;
elSound.addEventListener('click', ()=>{ on=!on; if(on){ audio.volume=0; audio.play().then(()=>{ let v=0,f=setInterval(()=>{v=Math.min(.45,v+.03);audio.volume=v;if(v>=.45)clearInterval(f);},120); }).catch(()=>{}); elSound.textContent='♪ Couper'; } else { audio.pause(); elSound.textContent='♪ Son'; } });

new ResizeObserver(()=>{ const w=W(),h=H(); if(!w||!h)return; camera.aspect=w/h; camera.updateProjectionMatrix(); renderer.setSize(w,h,false); }).observe(host);

const t0 = performance.now();
function loop(){
	const t=(performance.now()-t0)*0.001;
	tX += (ttX-tX)*0.05;
	if (current3D){ current3D.rotation.y = t*0.25 + tX; current3D.position.y = Math.sin(t*0.6)*0.2; }
	renderer.render(scene, camera);
	requestAnimationFrame(loop);
}
go(0, true); loop();
</script>

<?php get_footer(); ?>
