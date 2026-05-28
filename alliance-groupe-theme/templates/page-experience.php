<?php
/**
 * Template Name: Expérience immersive (style theirisk.com)
 *
 * "Le Voyage Alliance" — parcours immersif en 4 scènes :
 *   0. Bureau (ordi 3D Three.js, intro)
 *   1. Vésuve (modèle Sketchfab réel, iframe)
 *   2. Castello di Lettere (modèle Sketchfab réel, iframe)
 *   3. Menu (cartes cliquables vers les pages)
 *
 * Navigation : swipe / flèches / boutons NEXT-BACK. Les iframes
 * Sketchfab sont lazy-loadés (src posé au moment où la scène devient
 * active) pour ne pas charger 2 viewers lourds d'un coup.
 *
 * Slug conseillé : /alliance-groupe.
 *
 * @package Alliance_Groupe_Theme
 */

get_header();
$ag_office = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg';
$ag_music  = get_option( 'ag_xp_music', get_stylesheet_directory_uri() . '/assets/audio/naples.mp3' );

// Modèles Sketchfab (IDs fournis par le user).
$sk_vesuvius = 'ea2112ab36124cf28ce25f7a4dc19952';
$sk_castle   = 'f0423c7be6794a45b6105ccd08a8ac89';
$sk_params   = 'autostart=1&autospin=0.3&preload=1&ui_infos=0&ui_controls=1&ui_stop=0&ui_watermark=0&ui_hint=0&ui_ar=0&dnt=1';
$sk_vesuvius_url = 'https://sketchfab.com/models/' . $sk_vesuvius . '/embed?' . $sk_params;
$sk_castle_url   = 'https://sketchfab.com/models/' . $sk_castle . '/embed?' . $sk_params;

$ag_menu = array(
	array( 'l' => 'Sites Express', 'u' => home_url( '/sites-express' ) ),
	array( 'l' => 'Templates gratuits', 'u' => home_url( '/templates-wordpress' ) ),
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
.agx-scene{position:absolute;inset:0;opacity:0;visibility:hidden;transition:opacity .9s ease;z-index:1}
.agx-scene.is-active{opacity:1;visibility:visible;z-index:2}
.agx-scene iframe{position:absolute;inset:0;width:100%;height:100%;border:0;background:#05060a}

/* Fond bureau (scène 0) */
.agx-bg{position:absolute;inset:-3%;background-size:cover;background-position:center;transform:scale(1.05);animation:agx-bg 52s ease-in-out infinite alternate}
@keyframes agx-bg{from{transform:scale(1.05)}to{transform:scale(1.13) translate(-1.5%,-1%)}}
.agx-veil{position:absolute;inset:0;background:radial-gradient(ellipse 75% 70% at 50% 45%,transparent 0%,transparent 42%,rgba(5,6,10,.5) 80%,rgba(3,4,8,.92) 100%),linear-gradient(180deg,rgba(5,6,10,.4) 0%,transparent 28%,transparent 60%,rgba(4,4,8,.8) 100%)}
.agx-canvas{position:absolute;inset:0;width:100%;height:100%}

/* Overlays texte par scène */
.agx-cap{position:absolute;left:0;right:0;top:11vh;text-align:center;padding:0 24px;pointer-events:none;z-index:5}
.agx-cap .pre{font-size:clamp(.7rem,1.2vw,.9rem);letter-spacing:6px;color:#D4B45C;text-shadow:0 2px 14px rgba(0,0,0,.9);margin-bottom:12px}
.agx-cap .ttl{font-family:Georgia,serif;font-size:clamp(2rem,6.5vw,4.6rem);line-height:1.05;margin:0;text-shadow:0 4px 40px rgba(0,0,0,.95);background:linear-gradient(180deg,#fff,#e8dcc0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.agx-cap .line{margin:14px auto 0;max-width:560px;font-family:Georgia,serif;font-style:italic;font-size:clamp(.95rem,1.5vw,1.2rem);color:rgba(255,255,255,.85);text-shadow:0 2px 16px rgba(0,0,0,.9)}

/* Scène 3 — menu cartes */
.agx-menu{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 24px 120px;background:radial-gradient(circle at 50% 30%,#14141c 0%,#070710 70%)}
.agx-menu h2{font-family:Georgia,serif;font-size:clamp(1.8rem,4.5vw,3rem);margin:0 0 6px;text-align:center}
.agx-menu .sub{color:rgba(255,255,255,.6);letter-spacing:3px;text-transform:uppercase;font-size:.8rem;margin-bottom:34px}
.agx-menu__grid{display:grid;grid-template-columns:repeat(3,minmax(150px,210px));gap:16px}
@media(max-width:680px){.agx-menu__grid{grid-template-columns:1fr 1fr;gap:12px}}
.agx-menu__grid a{display:flex;align-items:center;justify-content:center;text-align:center;min-height:96px;padding:20px;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.3);border-radius:14px;color:#fff;text-decoration:none;font-family:Georgia,serif;font-size:1.05rem;transition:transform .3s,border-color .3s,background .3s}
.agx-menu__grid a:hover{transform:translateY(-4px);border-color:#D4B45C;background:rgba(212,180,92,.1);color:#D4B45C;text-decoration:none}

/* CTA "Découvrir" scène 0 */
.agx-enter{position:absolute;bottom:15vh;left:50%;transform:translateX(-50%);z-index:6;display:inline-flex;align-items:center;gap:10px;padding:16px 40px;border:1px solid rgba(212,180,92,.7);border-radius:999px;background:rgba(10,10,15,.4);backdrop-filter:blur(8px);color:#D4B45C;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:.85rem;text-decoration:none;cursor:pointer;transition:background .3s,color .3s,transform .3s}
.agx-enter:hover{background:#D4B45C;color:#0a0a0f;text-decoration:none;transform:translateX(-50%) translateY(-3px)}

/* Nav globale */
.agx-nav{position:absolute;bottom:42px;left:0;right:0;display:flex;justify-content:center;align-items:center;gap:26px;z-index:7;font-size:.78rem;letter-spacing:3px;font-weight:600;color:rgba(255,255,255,.6)}
.agx-nav button{background:none;border:none;color:rgba(255,255,255,.6);font:inherit;letter-spacing:inherit;cursor:pointer;padding:8px 14px;transition:color .25s}
.agx-nav button:hover{color:#fff}
.agx-nav button:disabled{opacity:.25;cursor:not-allowed}
.agx-nav .ct{color:rgba(255,255,255,.4);font-variant-numeric:tabular-nums}
.agx-sound{position:absolute;top:22px;right:22px;z-index:8;background:rgba(10,10,15,.45);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);color:#D4B45C;border-radius:999px;padding:9px 16px;font-size:.72rem;letter-spacing:2px;cursor:pointer;text-transform:uppercase}
.agx-sound:hover{color:#fff;border-color:#D4B45C}
.agx-hint{position:absolute;bottom:8vh;left:0;right:0;text-align:center;font-size:.7rem;letter-spacing:3px;color:rgba(255,255,255,.4);text-transform:uppercase;pointer-events:none;text-shadow:0 1px 10px rgba(0,0,0,.9);z-index:6}
@media (prefers-reduced-motion:reduce){.agx-bg{animation:none}.agx-scene{transition:opacity .25s}}
</style>

<main class="agx" id="agx">

	<!-- Scène 0 : LE BUREAU -->
	<section class="agx-scene is-active" data-scene="0">
		<div class="agx-bg" style="background-image:url('<?php echo esc_url( $ag_office ); ?>')" aria-hidden="true"></div>
		<div class="agx-veil" aria-hidden="true"></div>
		<canvas class="agx-canvas" id="agx-canvas"></canvas>
		<div class="agx-cap">
			<div class="pre">BIENVENUE CHEZ —</div>
			<h1 class="ttl">Alliance Groupe</h1>
			<div class="line">Agence web &amp; IA · Nantes · Naples · Marrakech. C'est ici que votre projet prend vie.</div>
		</div>
		<a href="#" class="agx-enter" id="agx-enter">Commencer le voyage →</a>
	</section>

	<!-- Scène 1 : LE VÉSUVE -->
	<section class="agx-scene" data-scene="1">
		<iframe data-src="<?php echo esc_url( $sk_vesuvius_url ); ?>" title="Le Vésuve" allow="autoplay; fullscreen; xr-spatial-tracking" allowfullscreen loading="lazy"></iframe>
		<div class="agx-cap">
			<div class="pre">🌋 NOTRE ÉNERGIE</div>
			<h1 class="ttl">Le Vésuve</h1>
			<div class="line">La force qui ne s'éteint jamais. Faites-le tourner du doigt.</div>
		</div>
	</section>

	<!-- Scène 2 : LE CASTELLO DI LETTERE -->
	<section class="agx-scene" data-scene="2">
		<iframe data-src="<?php echo esc_url( $sk_castle_url ); ?>" title="Castello di Lettere" allow="autoplay; fullscreen; xr-spatial-tracking" allowfullscreen loading="lazy"></iframe>
		<div class="agx-cap">
			<div class="pre">🏰 NOTRE MÉTHODE</div>
			<h1 class="ttl">Bâtir solide</h1>
			<div class="line">On construit votre présence comme une forteresse : durable, qui résiste au temps.</div>
		</div>
	</section>

	<!-- Scène 3 : LE MENU -->
	<section class="agx-scene" data-scene="3">
		<div class="agx-menu">
			<h2>Par où commencer ?</h2>
			<div class="sub">Choisissez votre destination</div>
			<div class="agx-menu__grid">
				<?php foreach ( $ag_menu as $m ) : ?>
					<a href="<?php echo esc_url( $m['u'] ); ?>"><?php echo esc_html( $m['l'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Navigation + son -->
	<div class="agx-nav">
		<button id="agx-back" aria-label="Précédent">◂ BACK</button>
		<span class="ct"><span id="agx-cur">01</span> / 04</span>
		<button id="agx-next" aria-label="Suivant">NEXT ▸</button>
	</div>
	<button class="agx-sound" id="agx-sound" type="button">♪ Son</button>
	<audio id="agx-audio" loop preload="none" src="<?php echo esc_url( $ag_music ); ?>"></audio>
	<div class="agx-hint" id="agx-hint">← Glissez ou utilisez NEXT →</div>
</main>

<script>
(function(){
	var host=document.getElementById('agx');
	var scenes=Array.prototype.slice.call(host.querySelectorAll('.agx-scene'));
	var total=scenes.length, cur=0;
	var bN=document.getElementById('agx-next'), bB=document.getElementById('agx-back'),
	    elCur=document.getElementById('agx-cur'), elHint=document.getElementById('agx-hint'),
	    elEnter=document.getElementById('agx-enter'), elSound=document.getElementById('agx-sound'),
	    audio=document.getElementById('agx-audio');

	function activate(idx){
		idx=Math.max(0,Math.min(total-1,idx));
		cur=idx;
		scenes.forEach(function(s,i){ s.classList.toggle('is-active', i===idx); });
		// Lazy-load de l'iframe Sketchfab quand la scène devient active.
		var ifr=scenes[idx].querySelector('iframe[data-src]');
		if (ifr && !ifr.src) ifr.src=ifr.getAttribute('data-src');
		if (elCur) elCur.textContent=String(idx+1).padStart(2,'0');
		if (bB) bB.disabled=(idx===0);
		if (bN) bN.disabled=(idx===total-1);
		if (elHint) elHint.style.opacity = (idx===total-1) ? '0' : '';
	}
	bN&&bN.addEventListener('click',function(){activate(cur+1);});
	bB&&bB.addEventListener('click',function(){activate(cur-1);});
	elEnter&&elEnter.addEventListener('click',function(e){e.preventDefault();activate(1);});
	document.addEventListener('keydown',function(e){ if(e.key==='ArrowRight')activate(cur+1); else if(e.key==='ArrowLeft')activate(cur-1); });
	// Swipe (hors zone iframe pour ne pas gêner la rotation du modèle)
	var x0=null;
	host.addEventListener('touchstart',function(e){ if(e.target.tagName==='IFRAME')return; x0=e.touches[0].clientX; },{passive:true});
	host.addEventListener('touchend',function(e){ if(x0===null)return; var dx=e.changedTouches[0].clientX-x0; if(Math.abs(dx)>55){ activate(cur+(dx<0?1:-1)); } x0=null; },{passive:true});

	// Son
	var on=false;
	elSound&&elSound.addEventListener('click',function(){
		on=!on;
		if(on){ audio.volume=0; audio.play().then(function(){ var v=0,f=setInterval(function(){v=Math.min(.45,v+.03);audio.volume=v;if(v>=.45)clearInterval(f);},120); }).catch(function(){}); elSound.textContent='♪ Couper'; }
		else { audio.pause(); elSound.textContent='♪ Son'; }
	});

	activate(0);

	/* ── Scène 0 : ordinateur 3D Three.js (intro) ── */
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	var canvas=document.getElementById('agx-canvas');
	function loadThree(cb){ if(window.THREE){cb();return;} var ex=document.querySelector('script[data-ag-three]'); if(ex){ex.addEventListener('load',cb);return;} var s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js'; s.async=true; s.dataset.agThree='1'; s.onload=cb; document.head.appendChild(s); }
	loadThree(function(){
		if(!window.THREE) return; var T=window.THREE;
		var W=host.clientWidth,H=host.clientHeight, low=(navigator.hardwareConcurrency||2)<4||W<768;
		var renderer=new T.WebGLRenderer({canvas:canvas,antialias:!low,alpha:true});
		renderer.setPixelRatio(Math.min(window.devicePixelRatio||1,low?1.2:1.6)); renderer.setSize(W,H,false); renderer.setClearColor(0,0);
		var scene=new T.Scene(), camera=new T.PerspectiveCamera(42,W/H,0.1,200); camera.position.set(0,1.6,17); camera.lookAt(0,0.4,0);
		scene.add(new T.AmbientLight(0xffffff,0.55));
		var k=new T.PointLight(0xfff0d0,2.4,90,1.6); k.position.set(10,9,12); scene.add(k);
		var w=new T.PointLight(0xf37a1f,1.3,70,1.6); w.position.set(-11,-2,8); scene.add(w);
		var office=new T.Group();
		var alu=new T.MeshStandardMaterial({color:0x2b2d33,metalness:.95,roughness:.22}), dk=new T.MeshStandardMaterial({color:0x111318,metalness:.7,roughness:.4});
		office.add(Object.assign(new T.Mesh(new T.BoxGeometry(9.2,.30,6.2),alu),{position:new T.Vector3(0,-2.7,0)}));
		var kc=document.createElement('canvas');kc.width=512;kc.height=320;var kx=kc.getContext('2d');kx.fillStyle='#1b1d22';kx.fillRect(0,0,512,320);kx.fillStyle='#0f1116';for(var ry=0;ry<5;ry++)for(var rx=0;rx<14;rx++)kx.fillRect(20+rx*34,18+ry*38,28,30);kx.fillStyle='#23252c';kx.fillRect(176,208,160,84);
		var kb=new T.Mesh(new T.PlaneGeometry(8.6,5.6),new T.MeshStandardMaterial({map:new T.CanvasTexture(kc),metalness:.3,roughness:.85}));kb.rotation.x=-Math.PI/2;kb.position.set(0,-2.5,.2);office.add(kb);
		var hg=new T.Mesh(new T.CylinderGeometry(.18,.18,9,20),dk);hg.rotation.z=Math.PI/2;hg.position.set(0,-2.62,-2.95);office.add(hg);
		var sg=new T.Group();sg.position.set(0,-2.62,-2.95);
		sg.add(Object.assign(new T.Mesh(new T.BoxGeometry(9.4,5.9,.18),alu),{position:new T.Vector3(0,3,-.06)}));
		var dc=document.createElement('canvas');dc.width=512;dc.height=320;var dx=dc.getContext('2d');var dtex;
		function paint(p){var g=dx.createLinearGradient(0,0,0,320);g.addColorStop(0,'#0d0f1a');g.addColorStop(.42,'#2a2012');g.addColorStop(.72,'#b8975a');g.addColorStop(1,'#f37a1f');dx.fillStyle=g;dx.fillRect(0,0,512,320);dx.fillStyle='rgba(255,255,255,.92)';dx.fillRect(44,42,120,18);dx.fillRect(44,82,360,12);dx.fillRect(44,104,300,12);dx.fillStyle='rgba(255,210,140,.95)';dx.fillRect(44,150,150,38);dx.fillStyle='rgba(255,255,255,.42)';for(var i=0;i<4;i++)dx.fillRect(44,230+i*18,380-(i%2)*60,8);dx.fillStyle='rgba(212,180,92,'+(.4+Math.abs(Math.sin(p))*.28)+')';dx.fillRect(332,150,138,90);dtex.needsUpdate=true;}
		dtex=new T.CanvasTexture(dc);dtex.minFilter=T.LinearFilter;
		var dalle=new T.Mesh(new T.PlaneGeometry(8.7,5.2),new T.MeshBasicMaterial({map:dtex}));dalle.position.set(0,3,.1);sg.add(dalle);sg.rotation.x=-.38;office.add(sg);
		office.position.y=.3;scene.add(office);
		new ResizeObserver(function(){var ww=host.clientWidth,hh=host.clientHeight;if(!ww||!hh)return;camera.aspect=ww/hh;camera.updateProjectionMatrix();renderer.setSize(ww,hh,false);}).observe(host);
		var ttX=0,tX=0;host.addEventListener('mousemove',function(ev){var r=host.getBoundingClientRect();ttX=((ev.clientX-r.left)/r.width-.5)*.5;},{passive:true});
		var t0=performance.now();
		function loop(){var t=(performance.now()-t0)*.001;tX+=(ttX-tX)*.05;
			if(cur===0){ paint(t*1.4); office.rotation.y=Math.sin(t*.32)*.14+tX; office.position.y=.3+Math.sin(t*.8)*.14; renderer.render(scene,camera); }
			requestAnimationFrame(loop);
		}
		loop();
	});
})();
</script>

<?php get_footer(); ?>
