<?php
/**
 * Section "La menace en direct" — disposition 2 colonnes.
 *
 * Gauche  : compteurs dynamiques (au-dessus) + carte mondiale des
 *           cyberattaques Kaspersky en temps réel, posée dans un champ
 *           d'étoiles 3D qui tourne (même esprit que le Voyage).
 * Droite  : « Tout commence par un audit » — la solution à la peur qu'on
 *           vient de créer (CTA principal vers /ag-audit).
 *
 * Les compteurs sont des ESTIMATIONS honnêtement étiquetées (taux
 * d'attaque publics : ~30 000 sites/jour piratés — Sucuri/Kaspersky/IBM).
 * La carte Kaspersky est le vrai flux live.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="ag-menace" aria-label="Cyberattaques en temps réel">
	<div class="ag-menace__space" aria-hidden="true">
		<div class="ag-menace__stars ag-menace__stars--1"></div>
		<div class="ag-menace__stars ag-menace__stars--2"></div>
		<div class="ag-menace__stars ag-menace__stars--3"></div>
		<div class="ag-menace__glow"></div>
	</div>

	<div class="ag-menace__inner">
		<div class="ag-menace__head">
			<span class="ag-menace__tag">⟶ Pourquoi maintenant</span>
			<h2 class="ag-menace__title">Pendant que vous lisez ceci, <em>ça n'arrête pas.</em></h2>
			<p class="ag-menace__lead">La carte est <strong>en direct</strong> : chaque trait est une cyberattaque réelle, à la seconde. La question n'est pas <em>si</em> votre site sera visé, mais <em>quand</em>.</p>
		</div>

		<!-- Ce que ça peut vous coûter, concrètement -->
		<div class="ag-menace__risks">
			<div class="ag-menace__risk">
				<span class="ag-menace__risk-ic">💰</span>
				<strong>Rançon</strong>
				<p>Vos fichiers chiffrés du jour au lendemain, une rançon en bitcoins réclamée pour les récupérer.</p>
			</div>
			<div class="ag-menace__risk">
				<span class="ag-menace__risk-ic">🗄️</span>
				<strong>Vol de vos données clients</strong>
				<p>Emails, téléphones, commandes copiés et revendus. Fuite = déclaration <strong>CNIL sous 72 h</strong> + sanctions.</p>
			</div>
			<div class="ag-menace__risk">
				<span class="ag-menace__risk-ic">⛔</span>
				<strong>Site hors-ligne</strong>
				<p>Site inaccessible ou redirigé vers un site douteux. Votre référencement Google s'effondre en quelques jours.</p>
			</div>
			<div class="ag-menace__risk">
				<span class="ag-menace__risk-ic">🪪</span>
				<strong>Réputation</strong>
				<p>Vos clients reçoivent des arnaques en votre nom. La confiance, elle, ne revient pas.</p>
			</div>
		</div>

		<div class="ag-menace__cols">
			<!-- GAUCHE : compteurs au-dessus + globe des attaques -->
			<div class="ag-menace__threat">
				<div class="ag-menace__counters" id="ag-menace-counters">
					<div class="ag-menace__counter">
						<div class="ag-menace__num" data-rate="0.347" data-seed="0">0</div>
						<div class="ag-menace__label">sites piratés <strong>aujourd'hui</strong></div>
						<div class="ag-menace__src">~30 000/j · est. Sucuri</div>
					</div>
					<div class="ag-menace__counter">
						<div class="ag-menace__num" data-rate="106" data-seed="0">0</div>
						<div class="ag-menace__label">attaques <strong>depuis l'ouverture</strong></div>
						<div class="ag-menace__src">~9,1 M/j · est. Kaspersky</div>
					</div>
					<div class="ag-menace__counter">
						<div class="ag-menace__num" data-static="196">196</div>
						<div class="ag-menace__label">jours avant de <strong>détecter</strong> l'intrusion</div>
						<div class="ag-menace__src">IBM · Cost of a Breach</div>
					</div>
					<div class="ag-menace__counter">
						<div class="ag-menace__num" data-static="43">43<span class="ag-menace__pct">%</span></div>
						<div class="ag-menace__label">des attaques visent les <strong>petites structures</strong></div>
						<div class="ag-menace__src">Verizon · DBIR</div>
					</div>
				</div>

				<div class="ag-menace__map">
					<div class="ag-menace__frame">
						<canvas id="ag-attackmap" class="ag-menace__canvas" aria-hidden="true"></canvas>
						<div class="ag-menace__live">● LIVE</div>
					</div>
					<p class="ag-menace__cap">Carte des cyberattaques · <strong>simulation</strong> en continu (sources Sucuri / Kaspersky / IBM)</p>
				</div>
			</div>

			<!-- DROITE : la solution à la peur = l'audit -->
			<aside class="ag-menace__solution">
				<span class="ag-menace__sol-tag">⟶ La solution</span>
				<h3 class="ag-menace__sol-title">Tout commence par <em>un audit.</em></h3>
				<p class="ag-menace__sol-text">Pas de panique : on regarde d'abord où vous en êtes <strong>vraiment</strong>. Je révèle les failles de votre site en 48 h — rapport clair, sans jargon, recommandations chiffrées.</p>
				<ul class="ag-menace__sol-list">
					<li>🔍 J'audite vos failles en 48 h</li>
					<li>🛠️ Je corrige ou je (re)crée votre site</li>
					<li>🛡️ Je veille dessus, chaque mois</li>
				</ul>
				<a href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>" class="ag-menace__cta">🔍 Tester mon site →</a>
				<span class="ag-menace__sol-note">Réponse sous 24 h · Devis gratuit. <strong>Si on ne trouve rien d'exploitable, on vous le dit honnêtement.</strong></span>
			</aside>
		</div>
	</div>
	<!-- capteur : déclenche le mur quand l'utilisateur atteint le BAS de la section -->
	<div id="ag-menace-end" aria-hidden="true" style="height:1px"></div>
</section>

<style>
.ag-menace{position:relative;overflow:hidden;padding:72px 22px 84px;background:radial-gradient(ellipse at 50% -10%,#0d1424 0%,#05060c 60%,#030308 100%);color:#fff}
.ag-menace__inner{position:relative;z-index:2;max-width:1200px;margin:0 auto}

/* --- Champ d'étoiles 3D qui tourne --- */
.ag-menace__space{position:absolute;inset:0;z-index:0;perspective:600px;pointer-events:none}
.ag-menace__stars{position:absolute;top:50%;left:50%;width:2px;height:2px;border-radius:50%;
	transform:translate(-50%,-50%);
	box-shadow:
		120px -240px #fff,-300px 120px rgba(255,255,255,.7),420px 180px rgba(212,180,92,.8),
		-180px -360px rgba(255,255,255,.6),540px -120px #fff,-480px 300px rgba(255,255,255,.5),
		260px 420px rgba(243,122,31,.7),-360px -180px #fff,640px 60px rgba(255,255,255,.6),
		-600px -300px rgba(212,180,92,.7),60px -480px #fff,-120px 480px rgba(255,255,255,.55),
		700px -360px rgba(255,255,255,.6),-680px -60px rgba(255,255,255,.5),360px -540px #fff,
		-420px 420px rgba(243,122,31,.6),520px 360px #fff,-560px 180px rgba(255,255,255,.6),
		180px 540px rgba(212,180,92,.6),-260px -540px #fff;
	animation:ag-menace-spin linear infinite}
.ag-menace__stars--1{animation-duration:120s;opacity:.9}
.ag-menace__stars--2{animation-duration:200s;opacity:.6}
.ag-menace__stars--3{animation-duration:90s;opacity:.45}
@keyframes ag-menace-spin{from{transform:translate(-50%,-50%) rotateZ(0deg)}to{transform:translate(-50%,-50%) rotateZ(360deg)}}
.ag-menace__glow{position:absolute;inset:0;background:radial-gradient(circle at 35% 45%,rgba(243,122,31,.10) 0%,transparent 55%);animation:ag-menace-pulse 6s ease-in-out infinite}
@keyframes ag-menace-pulse{0%,100%{opacity:.5}50%{opacity:1}}

/* --- En-tête --- */
.ag-menace__head{max-width:760px;margin:0 auto 36px;text-align:center}
.ag-menace__tag{display:inline-block;color:#F37A1F;font-size:.8rem;letter-spacing:3px;text-transform:uppercase;font-weight:800;margin-bottom:12px}
.ag-menace__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.7rem,4vw,2.8rem);line-height:1.12;margin:0 0 12px}
.ag-menace__title em{font-style:italic;background:linear-gradient(135deg,#F37A1F,#ff5252);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.ag-menace__lead{color:rgba(255,255,255,.76);font-size:1rem;line-height:1.55;margin:0}
/* Conséquences concrètes */
.ag-menace__risks{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;max-width:1100px;margin:0 auto 40px}
.ag-menace__risk{background:rgba(225,15,26,.06);border:1px solid rgba(225,15,26,.25);border-radius:14px;padding:18px 16px}
.ag-menace__risk-ic{font-size:1.6rem;line-height:1}
.ag-menace__risk strong{display:block;margin:8px 0 6px;color:#fff;font-size:1rem}
.ag-menace__risk p{margin:0;color:rgba(255,255,255,.72);font-size:.84rem;line-height:1.45}
.ag-menace__risk p strong{display:inline;color:#ff8a8a}
@media(max-width:860px){.ag-menace__risks{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.ag-menace__risks{grid-template-columns:1fr}}

/* --- 2 colonnes : globe (gauche) / audit (droite) --- */
.ag-menace__cols{display:grid;grid-template-columns:1.45fr 1fr;gap:30px;align-items:start}

/* Compteurs au-dessus du globe */
.ag-menace__counters{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px}
.ag-menace__counter{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:12px;padding:14px 10px;text-align:center}
.ag-menace__num{font-family:Georgia,serif;font-size:clamp(1.25rem,2.4vw,1.7rem);font-weight:700;line-height:1;color:#F3D27A;font-variant-numeric:tabular-nums;letter-spacing:-.5px}
.ag-menace__pct{font-size:.6em;margin-left:1px}
.ag-menace__label{color:rgba(255,255,255,.74);font-size:.72rem;line-height:1.35;margin-top:7px}
.ag-menace__label strong{color:#fff}
.ag-menace__src{color:rgba(255,255,255,.38);font-size:.6rem;letter-spacing:.03em;margin-top:6px;text-transform:uppercase}

/* Globe */
.ag-menace__map{margin:0}
.ag-menace__frame{position:relative;border:1px solid rgba(243,122,31,.28);border-radius:14px;overflow:hidden;background:transparent;box-shadow:0 30px 90px rgba(0,0,0,.6),0 0 70px rgba(243,122,31,.12);aspect-ratio:16/10}
.ag-menace__canvas{position:absolute;inset:0;width:100%;height:100%;display:block;background:radial-gradient(ellipse at 50% 50%,#0a1326 0%,#060a16 70%,#04060e 100%)}
.ag-menace__live{position:absolute;top:10px;right:12px;color:#ff5252;font-family:"Courier New",monospace;font-size:.72rem;font-weight:700;letter-spacing:2px;background:rgba(0,0,0,.45);border:1px solid rgba(255,82,82,.4);border-radius:999px;padding:3px 10px;animation:ag-menace-pulse 2s ease-in-out infinite}
.ag-menace__cap{text-align:center;margin:12px 0 0;color:rgba(255,255,255,.5);font-size:.78rem;letter-spacing:.04em}
.ag-menace__cap strong{color:#ff6b6b}

/* Colonne solution (audit) */
.ag-menace__solution{position:sticky;top:90px;background:linear-gradient(180deg,rgba(212,180,92,.10),rgba(243,122,31,.05));border:1px solid rgba(212,180,92,.34);border-radius:20px;padding:30px 26px}
.ag-menace__sol-tag{display:inline-block;color:#F3D27A;font-weight:700;letter-spacing:1px;font-size:.8rem;margin-bottom:10px;text-transform:uppercase}
.ag-menace__sol-title{font-family:Georgia,serif;font-size:clamp(1.5rem,2.6vw,2.1rem);color:#fff;margin:0 0 12px;line-height:1.15}
.ag-menace__sol-title em{font-style:italic;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.ag-menace__sol-text{color:rgba(255,255,255,.82);font-size:1rem;line-height:1.55;margin:0 0 18px}
.ag-menace__sol-text strong{color:#fff}
.ag-menace__sol-list{list-style:none;margin:0 0 22px;padding:0}
.ag-menace__sol-list li{color:rgba(255,255,255,.9);font-size:.95rem;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.08)}
.ag-menace__sol-list li:last-child{border-bottom:0}
.ag-menace__cta{display:block;text-align:center;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;text-decoration:none;padding:16px 24px;border-radius:100px;font-size:1.05rem;box-shadow:0 10px 32px rgba(243,122,31,.42);transition:transform .2s}
.ag-menace__cta:hover{transform:translateY(-2px) scale(1.02)}
.ag-menace__sol-note{display:block;text-align:center;color:rgba(255,255,255,.55);font-size:.78rem;margin-top:12px}

/* --- Responsive --- */
@media(max-width:980px){
	.ag-menace__cols{grid-template-columns:1fr;gap:24px}
	.ag-menace__solution{position:static}
	.ag-menace__frame{aspect-ratio:16/11}
}
@media(max-width:560px){
	.ag-menace__counters{grid-template-columns:repeat(2,1fr)}
	.ag-menace__frame{aspect-ratio:1/1}
}
@media(prefers-reduced-motion:reduce){
	.ag-menace__stars,.ag-menace__glow{animation:none}
}
</style>

<script>
(function(){
	var root = document.getElementById('ag-menace-counters');
	if(!root) return;
	var nums = root.querySelectorAll('.ag-menace__num[data-rate]');
	if(!nums.length) return;

	// Sème les compteurs « aujourd'hui » sur le temps écoulé depuis minuit,
	// pour qu'au chargement le chiffre soit déjà crédible (pas reparti de 0).
	var now = new Date();
	var midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
	var secsToday = (now - midnight) / 1000;

	var state = [];
	nums.forEach(function(el){
		var rate = parseFloat(el.getAttribute('data-rate')); // unités/seconde
		var seedMode = el.getAttribute('data-seed');
		var start = (seedMode === '0') ? rate * secsToday : 0;
		state.push({ el: el, rate: rate, val: start });
		el.textContent = Math.floor(start).toLocaleString('fr-FR');
	});

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion:reduce)').matches;
	var last = performance.now();
	function tick(t){
		var dt = (t - last) / 1000;
		last = t;
		state.forEach(function(s){
			s.val += s.rate * dt;
			s.el.textContent = Math.floor(s.val).toLocaleString('fr-FR');
		});
		if(!reduce) requestAnimationFrame(tick);
	}
	if('IntersectionObserver' in window){
		var io = new IntersectionObserver(function(entries){
			entries.forEach(function(e){
				if(e.isIntersecting){ last = performance.now(); requestAnimationFrame(tick); io.disconnect(); }
			});
		}, { threshold: 0.2 });
		io.observe(root);
	} else {
		requestAnimationFrame(tick);
	}
})();

/* === Carte d'attaques LOCALE (canvas léger, 0 requête externe) === */
(function(){
	var cv = document.getElementById('ag-attackmap');
	if(!cv || !cv.getContext) return;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var ctx = cv.getContext('2d'), W=0, H=0, dpr=Math.min(window.devicePixelRatio||1, 1.5);
	// Masque "continents" : 1 = terre. Grille 60x30 (planisphère plus fidèle :
	// Amériques à gauche, Europe/Afrique au centre, Asie/Océanie à droite).
	// Dessiné en pointillés -> silhouette du monde réaliste, coût quasi nul.
	var MAP=[
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000111000000000000000001111111111110000000000000000000",
		"000000011111110000000000011111111111111111111000000000000000",
		"000000111111111000000000111111111111111111111110000000000000",
		"000001111111111000000001111111111111111111111111000000000000",
		"000011111111110000000011111111111111111111111111110000000000",
		"000011111111000000000011111111111111111111111111111100000000",
		"000001111111000000000001111111111111111111111111111110000000",
		"000000111111000000000000011111111111111111111111111000000000",
		"000000011111000000000000001111111111111111111100111000000000",
		"000000001111000000000000000011111111111111111000000000000000",
		"000000001111100000000000000011111111111111100000000000000000",
		"000000000111100000000000000001111111111111000000000000000000",
		"000000000111000000000000000000111111111110000000000110000000",
		"000000000111000000000000000000111111111100000000011111000000",
		"000000000011100000000000000000011111111000000000011111100000",
		"000000000011100000000000000000011111111000000000001111000000",
		"000000000011110000000000000000001111110000000000000110000000",
		"000000000001110000000000000000001111100000000000000000000000",
		"000000000001110000000000000000001111000000000000000000000000",
		"000000000001100000000000000000000111000000000000011000000000",
		"000000000011000000000000000000000110000000000000111100000000",
		"000000000010000000000000000000000100000000000000011000000000",
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000000000000000000000000000000000000000000000000000000",
		"000000000000000000000000000000000000000000000000000000000000"
	];
	var MR=MAP.length, MC=MAP[0].length;
	// Points "villes" sur de vraies métropoles (col,row dans la grille 60x30).
	var GNODES=[[12,6],[10,5],[14,8],[13,11],[11,16],[15,21],
		[30,4],[31,5],[29,6],[33,6],[28,8],[34,9],[30,12],[33,16],[31,20],
		[42,5],[45,7],[48,8],[44,10],[50,11],[46,14],[51,16]];
	var NODES = GNODES.map(function(g){ return [ (g[0]+0.5)/MC, (g[1]+0.5)/MR ]; });
	function resize(){
		var r=cv.getBoundingClientRect(); W=r.width; H=r.height;
		cv.width=Math.round(W*dpr); cv.height=Math.round(H*dpr);
		ctx.setTransform(dpr,0,0,dpr,0,0);
	}
	function drawWorld(){
		var cw=W/MC, ch=H/MR, rad=Math.max(0.6, Math.min(cw,ch)*0.34);
		ctx.fillStyle='rgba(120,150,210,.20)';
		for(var y=0;y<MR;y++){ var row=MAP[y]; for(var x=0;x<MC;x++){ if(row.charAt(x)==='1'){
			ctx.beginPath(); ctx.arc((x+0.5)*cw,(y+0.5)*ch,rad,0,6.2832); ctx.fill();
		} } }
	}
	function px(n){ return [n[0]*W, n[1]*H]; }
	var arcs=[];
	function spawn(){
		var a=NODES[(Math.random()*NODES.length)|0], b=NODES[(Math.random()*NODES.length)|0];
		if(a===b) return;
		arcs.push({a:a,b:b,t:0,sp:0.008+Math.random()*0.014,hue:Math.random()<0.5?'243,122,31':'225,15,26'});
		if(arcs.length>26) arcs.shift();
	}
	function dot(p,c,r){ ctx.beginPath(); ctx.arc(p[0],p[1],r,0,6.2832); ctx.fillStyle=c; ctx.fill(); }
	var running=false, lastSpawn=0;
	function frame(ts){
		if(!running) return;
		ctx.clearRect(0,0,W,H);
		drawWorld();
		// nodes (villes)
		for(var i=0;i<NODES.length;i++){ var p=px(NODES[i]); dot(p,'rgba(255,255,255,.45)',1.8); }
		if(ts-lastSpawn>420){ spawn(); lastSpawn=ts; }
		for(var k=0;k<arcs.length;k++){
			var ar=arcs[k], A=px(ar.a), B=px(ar.b);
			var mx=(A[0]+B[0])/2, my=(A[1]+B[1])/2 - Math.hypot(B[0]-A[0],B[1]-A[1])*0.28;
			ar.t+=ar.sp; if(ar.t>1){ arcs.splice(k,1); k--; continue; }
			// trail
			ctx.beginPath();
			for(var s=0;s<=ar.t;s+=0.05){
				var u=1-s, x=u*u*A[0]+2*u*s*mx+s*s*B[0], y=u*u*A[1]+2*u*s*my+s*s*B[1];
				if(s===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
			}
			ctx.strokeStyle='rgba('+ar.hue+','+(0.5*(1-ar.t)+0.15).toFixed(2)+')'; ctx.lineWidth=1.4; ctx.stroke();
			// head
			var u2=1-ar.t, hx=u2*u2*A[0]+2*u2*ar.t*mx+ar.t*ar.t*B[0], hy=u2*u2*A[1]+2*u2*ar.t*my+ar.t*ar.t*B[1];
			dot([hx,hy],'rgba('+ar.hue+',.95)',2.2);
			if(ar.t>0.96) dot(B,'rgba('+ar.hue+',.5)',5);
		}
		requestAnimationFrame(frame);
	}
	function start(){ if(running) return; running=true; lastSpawn=0; requestAnimationFrame(frame); }
	function stop(){ running=false; }
	resize(); window.addEventListener('resize', resize);
	if(reduce){ // image figée : planisphère + nodes (sans animation)
		ctx.clearRect(0,0,W,H);
		drawWorld();
		for(var i=0;i<NODES.length;i++){ var p=px(NODES[i]); dot(p,'rgba(255,255,255,.5)',1.8); }
		return;
	}
	if('IntersectionObserver' in window){
		var io2=new IntersectionObserver(function(es){ es.forEach(function(e){ e.isIntersecting?start():stop(); }); }, {threshold:0.1});
		io2.observe(cv);
	} else { start(); }
})();
</script>

<!-- Mur PLEIN ÉCRAN « un piratage ressemble à ça » — seule issue : AUDITER -->
<div id="ag-menace-pop" class="ag-hack" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Un piratage ressemble à ça">
	<?php $ag_menace_bg = get_option( 'ag_tester_img_menace', '' ) ?: get_stylesheet_directory_uri() . '/assets/images/securite/menace.jpg'; ?>
	<div class="ag-hack__photo" aria-hidden="true" style="background-image:url('<?php echo esc_url( $ag_menace_bg ); ?>');"></div>
	<div class="ag-hack__term" aria-hidden="true">
		<div class="ag-hack__scroll">
			<p>root@target:~# nmap -p- cible.fr</p>
			<p class="g">[+] 443/tcp open  https</p>
			<p class="g">[+] xmlrpc.php  EXPOSÉ</p>
			<p>root@target:~# hydra -l admin -P rockyou.txt cible.fr</p>
			<p class="g">[+] tentative 1842... admin:123456</p>
			<p class="r">[!] MOT DE PASSE TROUVÉ — accès administrateur</p>
			<p>&gt; export base de données clients...</p>
			<p class="r">&gt; 12 480 emails + mots de passe exfiltrés</p>
			<p>&gt; chiffrement des fichiers... 41% ... 88% ... 100%</p>
			<p class="r">&gt;&gt; RANSOM : payez 2 BTC pour récupérer vos données</p>
			<p class="r">&gt;&gt; site mis hors-ligne</p>
			<p>root@target:~# _</p>
			<!-- duplication pour boucle continue -->
			<p>root@target:~# nmap -p- cible.fr</p>
			<p class="g">[+] 443/tcp open  https</p>
			<p class="g">[+] xmlrpc.php  EXPOSÉ</p>
			<p>root@target:~# hydra -l admin -P rockyou.txt cible.fr</p>
			<p class="g">[+] tentative 1842... admin:123456</p>
			<p class="r">[!] MOT DE PASSE TROUVÉ — accès administrateur</p>
			<p>&gt; export base de données clients...</p>
			<p class="r">&gt; 12 480 emails + mots de passe exfiltrés</p>
			<p>&gt; chiffrement des fichiers... 41% ... 88% ... 100%</p>
			<p class="r">&gt;&gt; RANSOM : payez 2 BTC pour récupérer vos données</p>
			<p class="r">&gt;&gt; site mis hors-ligne</p>
			<p>root@target:~# _</p>
		</div>
	</div>
	<div class="ag-hack__veil" aria-hidden="true"></div>
	<button class="ag-hack__close" id="ag-hack-close" type="button" aria-label="Fermer">Non merci, continuer ✕</button>
	<div class="ag-hack__center">
		<span class="ag-hack__tag">⚠️ Simulation</span>
		<h2 class="ag-hack__title" data-text="Un piratage ressemble à ça.">Un piratage ressemble à ça.</h2>
		<p class="ag-hack__sub">Le jour où ça arrive, il est déjà trop tard. Voilà ce qui peut tomber sur votre site, sans prévenir :</p>
		<ul class="ag-hack__list">
			<li>💰 Vos fichiers <strong>chiffrés</strong>, une <strong>rançon</strong> réclamée pour les récupérer.</li>
			<li>🗄️ Votre <strong>base clients volée</strong> (emails, téléphones) — et la CNIL sur le dos.</li>
			<li>⛔ <strong>Site hors-ligne</strong> ou défiguré, votre <strong>Google qui s'effondre</strong>.</li>
		</ul>
		<p class="ag-hack__sub" style="margin-top:6px">Le seul moyen de savoir si vous êtes exposé : un audit.</p>
		<a href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>" class="ag-hack__btn" id="ag-hack-btn">🔍 AUDITER MON SITE →</a>
	</div>
</div>
<style>
.ag-hack{position:fixed;inset:0;z-index:100000;display:none;background:#000;overflow:hidden}
.ag-hack.is-on{display:block;animation:agHackIn .3s ease}
.ag-hack__close{position:absolute;top:18px;right:20px;z-index:3;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.25);color:rgba(255,255,255,.75);font-size:.85rem;padding:9px 16px;border-radius:999px;cursor:pointer;opacity:0;pointer-events:none;transition:opacity .4s ease}
.ag-hack__close.is-ready{opacity:1;pointer-events:auto}
.ag-hack__close:hover{color:#fff;border-color:#fff}
@keyframes agHackIn{from{opacity:0}to{opacity:1}}
body.ag-hack-lock{overflow:hidden}
.ag-hack__photo{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.4;z-index:0;filter:grayscale(.2) contrast(1.05)}
.ag-hack__term{position:absolute;inset:0;font-family:"Courier New",monospace;font-size:15px;line-height:1.9;color:#39ff14;text-shadow:0 0 6px rgba(57,255,20,.5);padding:24px 18px;opacity:.55}
.ag-hack__scroll{animation:agHackScroll 22s linear infinite}
.ag-hack__scroll p{margin:0;white-space:nowrap}
.ag-hack__term .g{color:#39ff14}
.ag-hack__term .r{color:#ff2d2d;text-shadow:0 0 8px rgba(255,45,45,.7)}
@keyframes agHackScroll{from{transform:translateY(0)}to{transform:translateY(-50%)}}
.ag-hack__veil{position:absolute;inset:0;background:radial-gradient(ellipse at center,rgba(0,0,0,.55) 0%,rgba(0,0,0,.86) 70%);animation:agHackFlicker 4s steps(2) infinite}
@keyframes agHackFlicker{0%,97%,100%{opacity:1}98%{opacity:.85}99%{opacity:.95}}
.ag-hack__center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:24px;color:#fff}
.ag-hack__tag{display:inline-block;padding:5px 14px;border:1px solid rgba(255,45,45,.6);border-radius:999px;color:#ff6b6b;font-family:"Courier New",monospace;font-size:.78rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:22px}
.ag-hack__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2.1rem,7vw,4.4rem);line-height:1.05;margin:0 0 18px;color:#fff;position:relative;text-shadow:0 0 30px rgba(255,45,45,.45)}
.ag-hack__title::before,.ag-hack__title::after{content:attr(data-text);position:absolute;left:0;top:0;width:100%;overflow:hidden}
.ag-hack__title::before{color:#ff2d2d;animation:agGlitch 2.6s infinite;clip-path:inset(0 0 55% 0);transform:translateX(-2px)}
.ag-hack__title::after{color:#00e5ff;animation:agGlitch 3.4s infinite reverse;clip-path:inset(55% 0 0 0);transform:translateX(2px)}
@keyframes agGlitch{0%,92%,100%{transform:translateX(0)}93%{transform:translateX(-3px)}95%{transform:translateX(3px)}97%{transform:translateX(-2px)}}
.ag-hack__sub{max-width:560px;color:rgba(255,255,255,.85);font-size:1.08rem;line-height:1.6;margin:0 0 18px}
.ag-hack__list{list-style:none;margin:0 0 24px;padding:0;max-width:560px;text-align:left}
.ag-hack__list li{color:rgba(255,255,255,.9);font-size:1rem;line-height:1.5;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.08)}
.ag-hack__list li:last-child{border-bottom:0}
.ag-hack__list strong{color:#ff8a8a}
.ag-hack__btn{display:inline-block;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:900;text-decoration:none;padding:28px 64px;border-radius:999px;font-size:clamp(1.3rem,3.4vw,1.9rem);letter-spacing:.5px;box-shadow:0 16px 50px rgba(243,122,31,.55);animation:agHackPulse 2s ease-in-out infinite}
@media(max-width:600px){.ag-hack__btn{display:block;width:100%;padding:26px 20px}}
.ag-hack__btn:hover{transform:translateY(-2px) scale(1.03)}
@keyframes agHackPulse{0%,100%{box-shadow:0 16px 50px rgba(243,122,31,.45)}50%{box-shadow:0 16px 80px rgba(243,122,31,.9)}}
.ag-hack__loading{display:none;margin:20px 0 0;color:#39ff14;font-family:"Courier New",monospace;font-size:1rem;letter-spacing:1px;text-shadow:0 0 8px rgba(57,255,20,.6)}
.ag-hack.is-loading .ag-hack__loading{display:block;animation:agHackBlink 1s steps(2) infinite}
.ag-hack.is-loading .ag-hack__btn{opacity:.4;pointer-events:none}
@keyframes agHackBlink{0%,100%{opacity:1}50%{opacity:.35}}
@media(prefers-reduced-motion:reduce){.ag-hack__scroll,.ag-hack__veil,.ag-hack__title::before,.ag-hack__title::after,.ag-hack__btn{animation:none}}
</style>
<script>
(function(){
	var pop = document.getElementById('ag-menace-pop');
	var sec = document.querySelector('.ag-menace');
	if(!pop||!sec) return;
	var closeBtn = document.getElementById('ag-hack-close');
	var shown = false;
	// Mode test : ajouter ?testpop=1 à l'URL force le re-déclenchement (ignore le "déjà vu").
	var TEST = /[?&]testpop=1/.test(location.search);
	try{ if(!TEST && sessionStorage.getItem('ag_menace_pop')==='1') shown = true; }catch(e){}

	function open(){
		if(shown) return; shown = true;
		try{ sessionStorage.setItem('ag_menace_pop','1'); }catch(e){}
		pop.classList.add('is-on'); pop.setAttribute('aria-hidden','false');
		document.body.classList.add('ag-hack-lock');
		// Effet choc d'abord, sortie possible après 5 s (jamais de piège).
		setTimeout(function(){ if(closeBtn) closeBtn.classList.add('is-ready'); }, 5000);
	}
	function close(){
		pop.classList.remove('is-on'); pop.setAttribute('aria-hidden','true');
		document.body.classList.remove('ag-hack-lock');
	}
	if(closeBtn) closeBtn.addEventListener('click', close);
	document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });

	// ── DÉCLENCHEMENT = INTENTION DE QUITTER (exit-intent) ──────────────
	// La simulation n'apparaît PLUS au scroll. Elle se déclenche quand
	// l'utilisateur s'apprête à PARTIR : souris qui remonte hors de la page
	// (vers l'onglet/la croix) sur desktop, OU page masquée/quittée (changement
	// d'onglet, fermeture). Dernière chance de le convaincre avant qu'il file.
	var armed = false;
	// On "arme" après 3 s sur la page (évite un déclenchement involontaire dès l'arrivée).
	setTimeout(function(){ armed = true; }, 3000);

	// Desktop : la souris SORT du document par le haut. On écoute sur
	// documentElement (mouseleave = quitte la zone visible) ET mouseout (filet
	// pour les navigateurs où mouseleave ne porte pas la position).
	function maybeExit(y){ if(armed && !shown && y <= 0) open(); }
	document.documentElement.addEventListener('mouseleave', function(e){ maybeExit(e.clientY); });
	document.addEventListener('mouseout', function(e){
		if(shown || !armed) return;
		if(e.relatedTarget || e.toElement) return;   // sortie réelle (pas un survol interne)
		if((e.clientY||0) <= 5) open();               // vers le haut = part du site
	});

	// Onglet masqué / app quittée (desktop + mobile) : on ouvre avant de cacher
	// la page (visible au retour si l'utilisateur revient).
	document.addEventListener('visibilitychange', function(){
		if(document.visibilityState === 'hidden' && armed && !shown) open();
	});

	// Bouton "retour" navigateur (souvent = on s'en va) : intercepte une fois.
	try{
		history.pushState({agMenace:1}, '');
		window.addEventListener('popstate', function(){
			if(armed && !shown){ open(); history.pushState({agMenace:1}, ''); }
		});
	}catch(e){}
})();
</script>
