<?php
/**
 * Section "La menace en direct" — carte mondiale des cyberattaques
 * Kaspersky en temps réel, posée dans un champ d'étoiles 3D qui tourne
 * (même esprit que le Voyage / page-experience.php), avec des compteurs
 * dynamiques qui s'incrémentent en direct pour faire ressentir l'urgence.
 *
 * Les compteurs sont des ESTIMATIONS honnêtement étiquetées (semées sur
 * les taux d'attaque quotidiens publics : ~30 000 sites/jour piratés,
 * sources Sucuri / Kaspersky / IBM). La carte Kaspersky, elle, est le
 * vrai flux live. On ne maquille pas une donnée temps réel qu'on n'a pas.
 *
 * Pur CSS + un peu de JS (compteurs). Aucune dépendance externe hormis
 * l'iframe Kaspersky.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="ag-menace" aria-label="Cyberattaques en temps réel">
	<!-- Champ d'étoiles 3D qui tourne (CSS pur, derrière la carte) -->
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
			<p class="ag-menace__lead">La carte ci-dessous est <strong>en direct</strong> : chaque trait est une cyberattaque réelle, à la seconde. La question n'est pas <em>si</em> votre site sera visé, mais <em>quand</em>.</p>
		</div>

		<!-- La carte Kaspersky, posée dans les étoiles -->
		<div class="ag-menace__map">
			<div class="ag-menace__frame">
				<iframe src="https://cybermap.kaspersky.com/fr/widget/dynamic/dark" loading="lazy" title="Carte mondiale des cyberattaques en temps réel" allowfullscreen></iframe>
			</div>
			<p class="ag-menace__cap">Carte mondiale des cyberattaques · flux <strong>live</strong> · source Kaspersky</p>
		</div>

		<!-- Compteurs dynamiques (estimations honnêtes, qui montent en direct) -->
		<div class="ag-menace__counters" id="ag-menace-counters">
			<div class="ag-menace__counter">
				<div class="ag-menace__num" data-rate="0.347" data-seed="0" data-decimals="0">0</div>
				<div class="ag-menace__label">sites piratés <strong>aujourd'hui</strong> dans le monde</div>
				<div class="ag-menace__src">~30 000/jour · est. Sucuri</div>
			</div>
			<div class="ag-menace__counter">
				<div class="ag-menace__num" data-rate="106" data-seed="0" data-decimals="0">0</div>
				<div class="ag-menace__label">attaques détectées <strong>depuis l'ouverture</strong> de cette page</div>
				<div class="ag-menace__src">~9,1 M/jour · est. Kaspersky</div>
			</div>
			<div class="ag-menace__counter">
				<div class="ag-menace__num" data-static="196">196</div>
				<div class="ag-menace__label">jours en moyenne avant qu'une PME détecte l'intrusion</div>
				<div class="ag-menace__src">IBM · Cost of a Data Breach</div>
			</div>
			<div class="ag-menace__counter">
				<div class="ag-menace__num" data-static="43">43<span class="ag-menace__pct">%</span></div>
				<div class="ag-menace__label">des cyberattaques visent les <strong>petites structures</strong></div>
				<div class="ag-menace__src">Verizon · DBIR</div>
			</div>
		</div>

		<div class="ag-menace__cta-wrap">
			<p class="ag-menace__reassure">Pas de panique : on commence par regarder où vous en êtes vraiment.</p>
			<a href="<?php echo esc_url( home_url( '/ag-audit' ) ); ?>" class="ag-menace__cta">🔒 Auditer mon site maintenant →</a>
		</div>
	</div>
</section>

<style>
.ag-menace{position:relative;overflow:hidden;padding:90px 22px 100px;background:radial-gradient(ellipse at 50% -10%,#0d1424 0%,#05060c 60%,#030308 100%);color:#fff}
.ag-menace__inner{position:relative;z-index:2;max-width:1100px;margin:0 auto}

/* --- Champ d'étoiles 3D qui tourne (3 plans à vitesses différentes) --- */
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
.ag-menace__glow{position:absolute;inset:0;background:radial-gradient(circle at 50% 35%,rgba(243,122,31,.10) 0%,transparent 55%);animation:ag-menace-pulse 6s ease-in-out infinite}
@keyframes ag-menace-pulse{0%,100%{opacity:.5}50%{opacity:1}}

/* --- En-tête --- */
.ag-menace__head{max-width:740px;margin:0 auto 44px;text-align:center}
.ag-menace__tag{display:inline-block;color:#F37A1F;font-size:.82rem;letter-spacing:3px;text-transform:uppercase;font-weight:800;margin-bottom:14px}
.ag-menace__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.8rem,4.4vw,3.1rem);line-height:1.12;margin:0 0 14px}
.ag-menace__title em{font-style:italic;background:linear-gradient(135deg,#F37A1F,#ff5252);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.ag-menace__lead{color:rgba(255,255,255,.78);font-size:1.06rem;line-height:1.6;margin:0}

/* --- La carte dans les étoiles --- */
.ag-menace__map{max-width:880px;margin:0 auto 50px}
.ag-menace__frame{position:relative;border:1px solid rgba(243,122,31,.28);border-radius:14px;overflow:hidden;background:transparent;box-shadow:0 30px 90px rgba(0,0,0,.6),0 0 70px rgba(243,122,31,.12);aspect-ratio:16/10}
.ag-menace__frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0;background:transparent}
.ag-menace__cap{text-align:center;margin:14px 0 0;color:rgba(255,255,255,.55);font-size:.82rem;letter-spacing:.04em}
.ag-menace__cap strong{color:#ff6b6b}
@media(max-width:700px){.ag-menace__frame{aspect-ratio:1/1}}

/* --- Compteurs --- */
.ag-menace__counters{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;max-width:1000px;margin:0 auto 46px}
.ag-menace__counter{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:24px 18px;text-align:center;backdrop-filter:blur(4px)}
.ag-menace__num{font-family:Georgia,serif;font-size:clamp(1.7rem,3.4vw,2.5rem);font-weight:700;line-height:1;color:#F3D27A;font-variant-numeric:tabular-nums;letter-spacing:-.5px}
.ag-menace__pct{font-size:.6em;margin-left:1px}
.ag-menace__label{color:rgba(255,255,255,.78);font-size:.84rem;line-height:1.4;margin-top:10px}
.ag-menace__label strong{color:#fff}
.ag-menace__src{color:rgba(255,255,255,.4);font-size:.68rem;letter-spacing:.04em;margin-top:8px;text-transform:uppercase}
@media(max-width:860px){.ag-menace__counters{grid-template-columns:repeat(2,1fr)}}

/* --- CTA --- */
.ag-menace__cta-wrap{text-align:center}
.ag-menace__reassure{color:rgba(255,255,255,.7);font-size:1rem;margin:0 0 18px;font-style:italic}
.ag-menace__cta{display:inline-block;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;text-decoration:none;padding:16px 32px;border-radius:100px;font-size:1.05rem;box-shadow:0 10px 32px rgba(243,122,31,.42);transition:transform .2s}
.ag-menace__cta:hover{transform:translateY(-2px) scale(1.02)}

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
	// Démarre seulement quand la section est visible (perf + impact).
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
