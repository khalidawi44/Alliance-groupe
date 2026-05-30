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
						<iframe src="https://cybermap.kaspersky.com/fr/widget/dynamic/dark" loading="lazy" title="Carte mondiale des cyberattaques en temps réel" allowfullscreen></iframe>
					</div>
					<p class="ag-menace__cap">Carte mondiale des cyberattaques · flux <strong>live</strong> · source Kaspersky</p>
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
.ag-menace__stars--2{animation-duration:200s;opacity:.6;filter:blur(.4px)}
.ag-menace__stars--3{animation-duration:90s;opacity:.45;filter:blur(.8px)}
@keyframes ag-menace-spin{from{transform:translate(-50%,-50%) rotateZ(0deg)}to{transform:translate(-50%,-50%) rotateZ(360deg)}}
.ag-menace__glow{position:absolute;inset:0;background:radial-gradient(circle at 35% 45%,rgba(243,122,31,.10) 0%,transparent 55%);animation:ag-menace-pulse 6s ease-in-out infinite}
@keyframes ag-menace-pulse{0%,100%{opacity:.5}50%{opacity:1}}

/* --- En-tête --- */
.ag-menace__head{max-width:760px;margin:0 auto 36px;text-align:center}
.ag-menace__tag{display:inline-block;color:#F37A1F;font-size:.8rem;letter-spacing:3px;text-transform:uppercase;font-weight:800;margin-bottom:12px}
.ag-menace__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.7rem,4vw,2.8rem);line-height:1.12;margin:0 0 12px}
.ag-menace__title em{font-style:italic;background:linear-gradient(135deg,#F37A1F,#ff5252);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.ag-menace__lead{color:rgba(255,255,255,.76);font-size:1rem;line-height:1.55;margin:0}

/* --- 2 colonnes : globe (gauche) / audit (droite) --- */
.ag-menace__cols{display:grid;grid-template-columns:1.45fr 1fr;gap:30px;align-items:start}

/* Compteurs au-dessus du globe */
.ag-menace__counters{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px}
.ag-menace__counter{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:12px;padding:14px 10px;text-align:center;backdrop-filter:blur(4px)}
.ag-menace__num{font-family:Georgia,serif;font-size:clamp(1.25rem,2.4vw,1.7rem);font-weight:700;line-height:1;color:#F3D27A;font-variant-numeric:tabular-nums;letter-spacing:-.5px}
.ag-menace__pct{font-size:.6em;margin-left:1px}
.ag-menace__label{color:rgba(255,255,255,.74);font-size:.72rem;line-height:1.35;margin-top:7px}
.ag-menace__label strong{color:#fff}
.ag-menace__src{color:rgba(255,255,255,.38);font-size:.6rem;letter-spacing:.03em;margin-top:6px;text-transform:uppercase}

/* Globe */
.ag-menace__map{margin:0}
.ag-menace__frame{position:relative;border:1px solid rgba(243,122,31,.28);border-radius:14px;overflow:hidden;background:transparent;box-shadow:0 30px 90px rgba(0,0,0,.6),0 0 70px rgba(243,122,31,.12);aspect-ratio:16/10}
.ag-menace__frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0;background:transparent}
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
</script>

<!-- Pop-up déclenché à l'arrivée sur le globe Kaspersky (1×/visite) -->
<div id="ag-menace-pop" class="ag-mpop" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Testez votre site">
	<div class="ag-mpop__backdrop" data-close></div>
	<div class="ag-mpop__card">
		<button class="ag-mpop__x" type="button" data-close aria-label="Fermer">×</button>
		<span class="ag-mpop__tag">🚨 En direct</span>
		<h3 class="ag-mpop__title">Ces attaques sont réelles. <em>Et votre site ?</em></h3>
		<p class="ag-mpop__text">Pendant que vous regardez cette carte, des milliers de sites sont scannés par des robots. Vérifiez le vôtre en 30 secondes — gratuit, sans engagement.</p>
		<a href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>" class="ag-mpop__btn">🔍 Tester mon site →</a>
		<button class="ag-mpop__later" type="button" data-close>Continuer à regarder</button>
	</div>
</div>
<style>
.ag-mpop{position:fixed;inset:0;z-index:99998;display:none;align-items:center;justify-content:center;padding:20px}
.ag-mpop.is-on{display:flex;animation:agMpopIn .35s ease}
@keyframes agMpopIn{from{opacity:0}to{opacity:1}}
.ag-mpop__backdrop{position:absolute;inset:0;background:rgba(4,4,10,.78);backdrop-filter:blur(3px)}
.ag-mpop__card{position:relative;z-index:1;max-width:440px;width:100%;background:linear-gradient(180deg,#12121c,#0b0b12);border:1px solid rgba(243,122,31,.4);border-radius:20px;padding:30px 26px 26px;text-align:center;color:#fff;box-shadow:0 30px 90px rgba(0,0,0,.6),0 0 60px rgba(243,122,31,.15);animation:agMpopPop .4s cubic-bezier(.16,1,.3,1)}
@keyframes agMpopPop{from{transform:translateY(20px) scale(.96);opacity:0}to{transform:none;opacity:1}}
.ag-mpop__x{position:absolute;top:12px;right:16px;background:none;border:none;color:rgba(255,255,255,.5);font-size:26px;line-height:1;cursor:pointer}
.ag-mpop__x:hover{color:#fff}
.ag-mpop__tag{display:inline-block;padding:5px 13px;background:rgba(225,15,26,.18);border:1px solid rgba(225,15,26,.5);border-radius:999px;color:#ff6b6b;font-size:.74rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;margin-bottom:14px}
.ag-mpop__title{font-family:Georgia,'Playfair Display',serif;font-size:1.7rem;line-height:1.15;margin:0 0 12px}
.ag-mpop__title em{font-style:italic;background:linear-gradient(135deg,#F37A1F,#ff5252);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.ag-mpop__text{color:rgba(255,255,255,.8);font-size:.98rem;line-height:1.55;margin:0 0 22px}
.ag-mpop__btn{display:block;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;text-decoration:none;padding:15px 24px;border-radius:999px;font-size:1.05rem;box-shadow:0 10px 30px rgba(243,122,31,.45);transition:transform .2s}
.ag-mpop__btn:hover{transform:translateY(-2px) scale(1.02)}
.ag-mpop__later{margin-top:14px;background:none;border:none;color:rgba(255,255,255,.55);font-size:.85rem;cursor:pointer;text-decoration:underline}
.ag-mpop__later:hover{color:#fff}
@media(prefers-reduced-motion:reduce){.ag-mpop.is-on,.ag-mpop__card{animation:none}}
</style>
<script>
(function(){
	var pop = document.getElementById('ag-menace-pop');
	var sec = document.querySelector('.ag-menace');
	if(!pop||!sec) return;
	var shown = false;
	try{ if(sessionStorage.getItem('ag_menace_pop')==='1') shown = true; }catch(e){}
	function open(){
		if(shown) return; shown = true;
		try{ sessionStorage.setItem('ag_menace_pop','1'); }catch(e){}
		pop.classList.add('is-on'); pop.setAttribute('aria-hidden','false');
	}
	function close(){ pop.classList.remove('is-on'); pop.setAttribute('aria-hidden','true'); }
	pop.addEventListener('click', function(e){ if(e.target.hasAttribute('data-close')) close(); });
	document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
	if('IntersectionObserver' in window){
		var io = new IntersectionObserver(function(entries){
			entries.forEach(function(en){ if(en.isIntersecting){ open(); io.disconnect(); } });
		}, { threshold: 0.45 });
		io.observe(sec);
	}
})();
</script>
