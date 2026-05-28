<?php
/**
 * Template Name: Expérience immersive (style theirisk.com)
 *
 * Scène 1 "Bureau" : fond bureau de Naples cinématique + ordinateur 3D
 * raffiné + globes flottants (scène 1 uniquement).
 * Scène 2 "Menu constellation" : logo Alliance Groupe 3D au centre +
 * nœuds menu (texte épuré, sans emoji) cliquables.
 * Musique napolitaine douce optionnelle (bouton son).
 *
 * Slug conseillé : /alliance-groupe.
 *
 * @package Alliance_Groupe_Theme
 */

get_header();
$ag_office = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg';
$ag_logo   = get_stylesheet_directory_uri() . '/assets/images/ag-logo.png';
// Musique : déposer un MP3 napolitain instrumental dans assets/audio/naples.mp3
// OU définir l'option ag_xp_music (URL média).
$ag_music  = get_option( 'ag_xp_music', get_stylesheet_directory_uri() . '/assets/audio/naples.mp3' );
?>

<style>
body.page-template-page-experience{background:#05060a}
body.page-template-page-experience .ag-nav,
body.page-template-page-experience footer,
body.page-template-page-experience .ag-fsm-toggle{display:none!important}

.agx{position:fixed;inset:0;width:100vw;height:100vh;overflow:hidden;z-index:50;background:#05060a;color:#fff;touch-action:pan-y;font-family:'Helvetica Neue',Arial,sans-serif}
.agx__bg{position:absolute;inset:-3%;background-size:cover;background-position:center;transform:scale(1.05);animation:agx-bg 52s ease-in-out infinite alternate}
@keyframes agx-bg{from{transform:scale(1.05) translate(0,0)}to{transform:scale(1.14) translate(-1.5%,-1%)}}
/* Voile cinématique : net au centre, vignette riche sur les bords pour la profondeur */
.agx__bg-veil{position:absolute;inset:0;background:
	radial-gradient(ellipse 75% 70% at 50% 45%,transparent 0%,transparent 40%,rgba(5,6,10,.55) 80%,rgba(3,4,8,.92) 100%),
	linear-gradient(180deg,rgba(5,6,10,.45) 0%,transparent 25%,transparent 55%,rgba(4,4,8,.85) 100%)}
.agx__grain{position:absolute;inset:0;opacity:.05;pointer-events:none;background-image:radial-gradient(rgba(255,255,255,.6) .5px,transparent .5px);background-size:3px 3px;mix-blend-mode:overlay}
.agx__canvas{position:absolute;inset:0;width:100%;height:100%;display:block}

.agx__intro{position:absolute;top:12vh;left:0;right:0;text-align:center;padding:0 24px;pointer-events:none;transition:opacity .8s ease,transform .8s ease;z-index:4}
.agx__intro.is-hidden{opacity:0;transform:translateY(-20px)}
.agx__pre{font-size:clamp(.7rem,1.2vw,.9rem);letter-spacing:6px;color:rgba(255,255,255,.75);margin-bottom:14px;text-shadow:0 2px 14px rgba(0,0,0,.9)}
.agx__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2.4rem,8vw,6rem);font-weight:400;letter-spacing:.4vw;line-height:1;margin:0 0 16px;text-shadow:0 4px 40px rgba(0,0,0,.9);background:linear-gradient(180deg,#fff,#e8dcc0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.agx__sub{font-size:clamp(.72rem,1.1vw,.92rem);letter-spacing:4px;color:#D4B45C;font-weight:600;text-shadow:0 2px 14px rgba(0,0,0,.9)}

.agx__enter{position:absolute;bottom:15vh;left:50%;transform:translateX(-50%);z-index:6;display:inline-flex;align-items:center;gap:10px;padding:16px 40px;border:1px solid rgba(212,180,92,.7);border-radius:999px;background:rgba(10,10,15,.4);backdrop-filter:blur(8px);color:#D4B45C;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:.85rem;text-decoration:none;cursor:pointer;transition:background .3s,color .3s,transform .3s,opacity .6s}
.agx__enter:hover{background:#D4B45C;color:#0a0a0f;text-decoration:none;transform:translateX(-50%) translateY(-3px)}
.agx__enter.is-hidden{opacity:0;pointer-events:none}

.agx__menu-head{position:absolute;top:8vh;left:0;right:0;text-align:center;padding:0 24px;pointer-events:none;opacity:0;transform:translateY(20px);transition:opacity .8s ease .25s,transform .8s ease .25s;z-index:4}
.agx__menu-head.is-shown{opacity:1;transform:none}
.agx__menu-head h2{font-family:Georgia,serif;font-size:clamp(1.5rem,3.6vw,2.6rem);margin:0;text-shadow:0 2px 20px rgba(0,0,0,.9)}
.agx__menu-head p{color:rgba(255,255,255,.65);font-size:.82rem;letter-spacing:3px;margin:10px 0 0;text-transform:uppercase}

.agx__back{position:absolute;top:24px;left:24px;z-index:6;background:rgba(10,10,15,.45);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:999px;padding:10px 18px;font-size:.78rem;letter-spacing:2px;cursor:pointer;opacity:0;pointer-events:none;transition:opacity .5s ease}
.agx__back.is-shown{opacity:1;pointer-events:auto}
.agx__back:hover{border-color:#D4B45C;color:#D4B45C}

/* Bouton son (façon theirisk "UNMUTE") */
.agx__sound{position:absolute;top:24px;right:24px;z-index:6;background:rgba(10,10,15,.45);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);color:#D4B45C;border-radius:999px;padding:10px 18px;font-size:.74rem;letter-spacing:2px;cursor:pointer;text-transform:uppercase;transition:border-color .3s,color .3s}
.agx__sound:hover{border-color:#D4B45C;color:#fff}

.agx__hint{position:absolute;bottom:6vh;left:0;right:0;text-align:center;font-size:.7rem;letter-spacing:3px;color:rgba(255,255,255,.45);text-transform:uppercase;pointer-events:none;text-shadow:0 1px 10px rgba(0,0,0,.9)}

@media (prefers-reduced-motion:reduce){.agx__bg{animation:none}}
</style>

<main class="agx" id="agx">
	<div class="agx__bg" style="background-image:url('<?php echo esc_url( $ag_office ); ?>');" aria-hidden="true"></div>
	<div class="agx__bg-veil" aria-hidden="true"></div>
	<div class="agx__grain" aria-hidden="true"></div>
	<canvas class="agx__canvas" id="agx-canvas"></canvas>

	<div class="agx__intro" id="agx-intro">
		<div class="agx__pre">BIENVENUE CHEZ —</div>
		<h1 class="agx__title">Alliance Groupe</h1>
		<div class="agx__sub">AGENCE WEB & IA · NANTES · NAPLES · MARRAKECH</div>
	</div>

	<a href="#" class="agx__enter" id="agx-enter">Découvrir →</a>

	<div class="agx__menu-head" id="agx-menu-head">
		<h2>Par où commencer ?</h2>
		<p>Touchez une étoile</p>
	</div>

	<button class="agx__back" id="agx-back" type="button">◂ Retour</button>
	<button class="agx__sound" id="agx-sound" type="button">♪ Activer le son</button>
	<audio id="agx-audio" loop preload="none" src="<?php echo esc_url( $ag_music ); ?>"></audio>

	<div class="agx__hint" id="agx-hint">Cliquez sur l'ordinateur pour entrer</div>
</main>

<script>
(function(){
	var host = document.getElementById('agx');
	var canvas = document.getElementById('agx-canvas');
	if (!host || !canvas) return;
	var elIntro=document.getElementById('agx-intro'), elEnter=document.getElementById('agx-enter'),
	    elMenuHead=document.getElementById('agx-menu-head'), elBack=document.getElementById('agx-back'),
	    elHint=document.getElementById('agx-hint'), elSound=document.getElementById('agx-sound'),
	    audio=document.getElementById('agx-audio');

	// ── Musique : bouton son (autoplay bloqué tant que pas de clic) ──
	var soundOn=false;
	elSound && elSound.addEventListener('click', function(){
		soundOn=!soundOn;
		if (soundOn){ audio.volume=0; audio.play().then(function(){ var v=0; var f=setInterval(function(){ v=Math.min(.45,v+.03); audio.volume=v; if(v>=.45)clearInterval(f); },120); }).catch(function(){}); elSound.textContent='♪ Couper le son'; }
		else { audio.pause(); elSound.textContent='♪ Activer le son'; }
	});

	function loadThree(cb){
		if (window.THREE){cb();return;}
		var ex=document.querySelector('script[data-ag-three]');
		if (ex){ex.addEventListener('load',cb);return;}
		var s=document.createElement('script');
		s.src='https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js';
		s.async=true; s.dataset.agThree='1'; s.onload=cb; document.head.appendChild(s);
	}

	var MENU=[
		{label:'Sites Express', url:'<?php echo esc_url( home_url( '/sites-express' ) ); ?>'},
		{label:'Templates',     url:'<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>'},
		{label:'Cadeaux',       url:'<?php echo esc_url( home_url( '/audit-seo' ) ); ?>'},
		{label:'Ambassadeurs',  url:'<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>'},
		{label:'Sur-mesure',    url:'<?php echo esc_url( home_url( '/sur-mesure' ) ); ?>'},
		{label:'Prospection',   url:'<?php echo esc_url( home_url( '/systeme-prospection' ) ); ?>'},
		{label:'Contact',       url:'<?php echo esc_url( home_url( '/contact' ) ); ?>'}
	];

	loadThree(function(){
		if (!window.THREE) return;
		var T=window.THREE;
		var W=host.clientWidth, H=host.clientHeight;
		var lowSpec=(navigator.hardwareConcurrency||2)<4 || W<768;
		var renderer=new T.WebGLRenderer({canvas:canvas,antialias:!lowSpec,alpha:true});
		renderer.setPixelRatio(Math.min(window.devicePixelRatio||1, lowSpec?1.2:1.6));
		renderer.setSize(W,H,false); renderer.setClearColor(0x000000,0);
		var scene=new T.Scene();
		var camera=new T.PerspectiveCamera(42,W/H,0.1,200);
		camera.position.set(0,1.6,17); camera.lookAt(0,0.4,0);

		scene.add(new T.AmbientLight(0xffffff,0.55));
		var key=new T.PointLight(0xfff0d0,2.4,90,1.6); key.position.set(10,9,12); scene.add(key);
		var warm=new T.PointLight(0xf37a1f,1.3,70,1.6); warm.position.set(-11,-2,8); scene.add(warm);
		var rim=new T.PointLight(0x99bbff,1.6,60,2); rim.position.set(0,2,9); scene.add(rim);

		/* ───── ORDINATEUR PORTABLE raffiné ───── */
		var office=new T.Group();
		var alu=new T.MeshStandardMaterial({color:0x2b2d33,metalness:0.95,roughness:0.22});
		var aluDark=new T.MeshStandardMaterial({color:0x111318,metalness:0.7,roughness:0.4});

		// Coque base (fine + bord avant biseauté simulé par 2 box)
		var base=new T.Mesh(new T.BoxGeometry(9.2,0.30,6.2),alu); base.position.y=-2.7; office.add(base);
		var baseLip=new T.Mesh(new T.BoxGeometry(9.0,0.12,6.0),alu); baseLip.position.y=-2.55; office.add(baseLip);
		// Surface clavier (texture touches sombres, discrètes)
		var kc=document.createElement('canvas'); kc.width=512; kc.height=320; var kx=kc.getContext('2d');
		kx.fillStyle='#1b1d22'; kx.fillRect(0,0,512,320);
		kx.fillStyle='#0f1116';
		for(var ry=0;ry<5;ry++)for(var rx=0;rx<14;rx++){ kx.fillRect(20+rx*34,18+ry*38,28,30); }
		kx.fillStyle='#23252c'; kx.fillRect(176,208,160,84); // trackpad
		var ktex=new T.CanvasTexture(kc);
		var kb=new T.Mesh(new T.PlaneGeometry(8.6,5.6),new T.MeshStandardMaterial({map:ktex,metalness:0.3,roughness:0.85}));
		kb.rotation.x=-Math.PI/2; kb.position.set(0,-2.48,0.2); office.add(kb);
		// Charnière
		var hinge=new T.Mesh(new T.CylinderGeometry(0.18,0.18,9.0,20),aluDark); hinge.rotation.z=Math.PI/2; hinge.position.set(0,-2.62,-2.95); office.add(hinge);
		// Écran : coque arrière + bezel fin + dalle
		var sg=new T.Group(); sg.position.set(0,-2.62,-2.95);
		var shell=new T.Mesh(new T.BoxGeometry(9.4,5.9,0.18),alu); shell.position.set(0,3.0,-0.06); sg.add(shell);
		var bezel=new T.Mesh(new T.BoxGeometry(9.0,5.6,0.06),aluDark); bezel.position.set(0,3.0,0.05); sg.add(bezel);
		var dc=document.createElement('canvas'); dc.width=512; dc.height=320; var dx=dc.getContext('2d');
		function paintScreen(p){
			var g=dx.createLinearGradient(0,0,0,320);
			g.addColorStop(0,'#0d0f1a'); g.addColorStop(.42,'#2a2012'); g.addColorStop(.72,'#b8975a'); g.addColorStop(1,'#f37a1f');
			dx.fillStyle=g; dx.fillRect(0,0,512,320);
			dx.fillStyle='rgba(255,255,255,.92)'; dx.fillRect(44,42,120,18); dx.fillRect(44,82,360,12); dx.fillRect(44,104,300,12);
			dx.fillStyle='rgba(255,210,140,.95)'; dx.fillRect(44,150,150,38);
			dx.fillStyle='rgba(255,255,255,.42)'; for(var i=0;i<4;i++) dx.fillRect(44,230+i*18,380-(i%2)*60,8);
			dx.fillStyle='rgba(212,180,92,'+(0.4+Math.abs(Math.sin(p))*0.28)+')'; dx.fillRect(332,150,138,90);
			dtex.needsUpdate=true;
		}
		var dtex=new T.CanvasTexture(dc); dtex.minFilter=T.LinearFilter;
		var dalle=new T.Mesh(new T.PlaneGeometry(8.7,5.2),new T.MeshBasicMaterial({map:dtex}));
		dalle.position.set(0,3.0,0.10); sg.add(dalle);
		sg.rotation.x=-0.38; office.add(sg);
		// Ombre de contact (plane radial sombre sous le laptop)
		var shadowTex=(function(){ var c=document.createElement('canvas'); c.width=c.height=128; var x=c.getContext('2d'); var gr=x.createRadialGradient(64,64,4,64,64,64); gr.addColorStop(0,'rgba(0,0,0,.55)'); gr.addColorStop(1,'rgba(0,0,0,0)'); x.fillStyle=gr; x.fillRect(0,0,128,128); return new T.CanvasTexture(c); })();
		var shadow=new T.Mesh(new T.PlaneGeometry(13,9),new T.MeshBasicMaterial({map:shadowTex,transparent:true})); shadow.rotation.x=-Math.PI/2; shadow.position.set(0,-2.95,0.5); office.add(shadow);
		office.position.y=0.3; scene.add(office);

		// Globes retirés (rendu fil-de-fer jugé "bizarre"). Tableau vide
		// conservé pour ne rien casser dans les boucles ci-dessous.
		var globes=[];

		/* ───── MENU CONSTELLATION ───── */
		var menu=new T.Group(); menu.visible=false;
		// Halo doux derrière le logo (sprite radial — pas de wireframe)
		var glowTex=(function(){ var c=document.createElement('canvas'); c.width=c.height=256; var x=c.getContext('2d'); var gr=x.createRadialGradient(128,128,10,128,128,128); gr.addColorStop(0,'rgba(212,180,92,.55)'); gr.addColorStop(.4,'rgba(212,180,92,.18)'); gr.addColorStop(1,'rgba(212,180,92,0)'); x.fillStyle=gr; x.fillRect(0,0,256,256); return new T.CanvasTexture(c); })();
		var glow=new T.Sprite(new T.SpriteMaterial({map:glowTex,transparent:true,depthWrite:false})); glow.scale.set(12,12,1); menu.add(glow);
		// Logo AG (plane texturé double-face)
		var logoTex=new T.TextureLoader().load('<?php echo esc_url( $ag_logo ); ?>');
		logoTex.colorSpace = T.SRGBColorSpace;
		var logo=new T.Mesh(new T.PlaneGeometry(5.2,5.2),new T.MeshBasicMaterial({map:logoTex,transparent:true,side:T.DoubleSide})); menu.add(logo);
		// Nœuds : label texte épuré (SANS emoji) + petit point
		var nodes=[];
		var N=MENU.length;
		for(var i=0;i<N;i++){
			var ang=(i/N)*Math.PI*2 - Math.PI/2;
			var rad=8.8, nx=Math.cos(ang)*rad, ny=Math.sin(ang)*rad*0.66, nz=(i%2===0?1.2:-1.2);
			var lc=document.createElement('canvas'); lc.width=320; lc.height=80; var lx=lc.getContext('2d');
			lx.font='600 30px Georgia,serif'; lx.textAlign='center'; lx.textBaseline='middle';
			lx.shadowColor='rgba(0,0,0,.9)'; lx.shadowBlur=8;
			lx.fillStyle='#ffffff'; lx.fillText(MENU[i].label, 160, 40);
			var sp=new T.Sprite(new T.SpriteMaterial({map:new T.CanvasTexture(lc),transparent:true,depthWrite:false}));
			sp.scale.set(5,1.25,1); sp.position.set(nx,ny,nz); sp.userData={url:MENU[i].url};
			menu.add(sp); nodes.push(sp);
			var dot=new T.Mesh(new T.SphereGeometry(0.28,14,12),new T.MeshStandardMaterial({color:0xf37a1f,emissive:0xf37a1f,emissiveIntensity:0.9}));
			dot.position.set(nx,ny-0.95,nz); dot.userData={url:MENU[i].url};
			menu.add(dot); nodes.push(dot);
		}
		scene.add(menu);

		/* ───── Interaction ───── */
		var mode='office', ray=new T.Raycaster(), mouse=new T.Vector2();
		function showMenu(){ if(mode==='menu')return; mode='menu'; office.visible=false; globes.forEach(function(g){g.visible=false;}); menu.visible=true;
			elIntro&&elIntro.classList.add('is-hidden'); elEnter&&elEnter.classList.add('is-hidden');
			elMenuHead&&elMenuHead.classList.add('is-shown'); elBack&&elBack.classList.add('is-shown');
			if(elHint) elHint.textContent='Touchez une étoile pour naviguer'; }
		function showOffice(){ mode='office'; office.visible=true; globes.forEach(function(g){g.visible=true;}); menu.visible=false;
			elIntro&&elIntro.classList.remove('is-hidden'); elEnter&&elEnter.classList.remove('is-hidden');
			elMenuHead&&elMenuHead.classList.remove('is-shown'); elBack&&elBack.classList.remove('is-shown');
			if(elHint) elHint.textContent="Cliquez sur l'ordinateur pour entrer"; }
		elEnter&&elEnter.addEventListener('click',function(e){e.preventDefault();showMenu();});
		elBack&&elBack.addEventListener('click',showOffice);
		canvas.addEventListener('click',function(ev){
			var r=canvas.getBoundingClientRect();
			mouse.x=((ev.clientX-r.left)/r.width)*2-1; mouse.y=-((ev.clientY-r.top)/r.height)*2+1;
			ray.setFromCamera(mouse,camera);
			if(mode==='office'){ if(ray.intersectObject(office,true).length) showMenu(); }
			else { var h=ray.intersectObjects(nodes,true); if(h.length&&h[0].object.userData.url) window.location.href=h[0].object.userData.url; }
		});

		new ResizeObserver(function(){ var w=host.clientWidth,h=host.clientHeight; if(!w||!h)return; camera.aspect=w/h; camera.updateProjectionMatrix(); renderer.setSize(w,h,false); }).observe(host);

		var ttX=0,ttY=0,tX=0,tY=0;
		host.addEventListener('mousemove',function(ev){ var r=host.getBoundingClientRect(); ttX=((ev.clientX-r.left)/r.width-0.5)*0.5; ttY=((ev.clientY-r.top)/r.height-0.5)*0.25; },{passive:true});

		var t0=performance.now();
		function loop(){
			var t=(performance.now()-t0)*0.001;
			tX+=(ttX-tX)*0.05; tY+=(ttY-tY)*0.05;
			if(mode==='office'){
				paintScreen(t*1.4);
				office.rotation.y=Math.sin(t*0.32)*0.14+tX; office.rotation.x=-0.02+tY;
				office.position.y=0.3+Math.sin(t*0.8)*0.14;
				for(var g=0;g<globes.length;g++){ globes[g].rotation.y+=globes[g].userData.spin; globes[g].position.y+=Math.sin(t*0.6+globes[g].userData.bob)*0.004; }
			} else {
				menu.rotation.y=Math.sin(t*0.22)*0.22+tX*0.5; menu.rotation.x=tY*0.35;
				logo.rotation.y=Math.sin(t*0.4)*0.10;
				glow.material.opacity=0.85+Math.sin(t*1.4)*0.15;
			}
			renderer.render(scene,camera);
			requestAnimationFrame(loop);
		}
		loop();
	});
})();
</script>

<?php get_footer(); ?>
