<?php
/**
 * ag-video-presentation.php — « Alliance Groupe en 52 secondes » sur l'accueil.
 *
 * Insère, juste sous le hero (après le bandeau défilant .mq, avant le tableau
 * « Une alliance, pas un prestataire »), une section vidéo présentée par
 * l'égérie : téléphone 9:16 à droite, argumentaire + CTA à gauche.
 *
 * Le template page-accueil-cinema.php n'est PAS modifié : la section est
 * imprimée dans un <template> en pied de page et déplacée par JS au bon
 * endroit. Si le JS est coupé, rien ne casse (la section reste invisible).
 *
 * Vidéo : /assets/videos/presentation-egerie.mp4 (720x1280, ~3,4 Mo) +
 * poster /assets/videos/presentation-egerie-poster.jpg. Chargement différé :
 * la source n'est injectée que lorsque la section approche de l'écran,
 * lecture auto muette en boucle, bouton « Écouter avec le son » qui relance
 * depuis le début avec le son (geste utilisateur = autorisé partout).
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Vrai uniquement sur l'accueil cinématique (ou la page d'accueil). */
function ag_video_pres_on_home() {
	if ( is_admin() ) { return false; }
	if ( function_exists( 'ag_is_accueil_cinema' ) && ag_is_accueil_cinema() ) { return true; }
	return is_front_page();
}

/** Chemins vidéo / poster (avec version anti-cache basée sur la date du fichier). */
function ag_video_pres_assets() {
	$dir  = get_stylesheet_directory();
	$uri  = get_stylesheet_directory_uri();
	$rel  = '/assets/videos/presentation-egerie.mp4';
	$relp = '/assets/videos/presentation-egerie-poster.jpg';
	if ( ! file_exists( $dir . $rel ) ) { return null; }
	$v  = (string) filemtime( $dir . $rel );
	$vp = file_exists( $dir . $relp ) ? (string) filemtime( $dir . $relp ) : $v;
	return array(
		'mp4'    => $uri . $rel . '?v=' . $v,
		'poster' => file_exists( $dir . $relp ) ? $uri . $relp . '?v=' . $vp : '',
	);
}

add_action( 'wp_head', function () {
	if ( ! ag_video_pres_on_home() || ! ag_video_pres_assets() ) { return; }
	?>
<style id="ag-video-pres">
.agv{position:relative;padding:clamp(72px,9vw,128px) 0;background:
 radial-gradient(ellipse at 78% 50%,rgba(212,180,92,.14),rgba(0,0,0,0) 55%),
 linear-gradient(180deg,#05050a 0%,#0b0a12 50%,#05050a 100%);overflow:hidden;color:#f0ecdf}
.agv::before{content:"";position:absolute;inset:0;background-image:radial-gradient(rgba(212,180,92,.12) 1px,transparent 1.2px);background-size:34px 34px;opacity:.55;pointer-events:none}
.agv__in{position:relative;z-index:1;display:grid;grid-template-columns:1.1fr .9fr;gap:clamp(36px,6vw,96px);align-items:center}
.agv__txt .eyebrow{display:block;margin-bottom:18px}
.agv__t{font-family:var(--serif,Georgia,serif);font-weight:400;font-size:clamp(2.1rem,4.4vw,3.6rem);line-height:1.04;letter-spacing:-.01em;margin:0 0 22px}
.agv__t em{font-style:italic;color:var(--gold-hi,#f4d06f)}
.agv__p{font-size:clamp(1rem,1.25vw,1.15rem);line-height:1.6;color:#cfcabd;max-width:52ch;margin:0 0 26px}
.agv__pts{list-style:none;padding:0;margin:0 0 34px;display:grid;gap:12px}
.agv__pts li{position:relative;padding-left:30px;font-size:1rem;line-height:1.45;color:#e8e2d2}
.agv__pts li::before{content:"";position:absolute;left:0;top:.42em;width:16px;height:16px;border-radius:50%;background:linear-gradient(135deg,var(--gold-hi,#f4d06f),var(--gold,#d4b45c));box-shadow:0 0 0 4px rgba(212,180,92,.15)}
.agv__cta{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
.agv__ph{display:flex;justify-content:center}
.agv__frame{position:relative;width:min(340px,78vw);aspect-ratio:9/16;border-radius:38px;padding:10px;background:linear-gradient(160deg,#2a2518,#0d0c0a 40%,#1d1a10);box-shadow:0 0 0 1.5px rgba(212,180,92,.55),0 40px 90px rgba(0,0,0,.65),0 0 120px rgba(212,180,92,.18);transform:rotate(-1.5deg)}
.agv__frame::after{content:"";position:absolute;left:50%;top:18px;width:84px;height:8px;margin-left:-42px;border-radius:4px;background:#000;opacity:.9;z-index:3}
.agv__frame video{display:block;width:100%;height:100%;object-fit:cover;border-radius:30px;background:#000}
.agv__snd{position:absolute;left:50%;bottom:26px;transform:translateX(-50%);z-index:4;display:inline-flex;align-items:center;gap:10px;padding:14px 22px;border:0;border-radius:999px;cursor:pointer;font:700 .95rem/1 var(--sans,system-ui,sans-serif);letter-spacing:.02em;color:#120f08;background:linear-gradient(120deg,var(--gold-hi,#f4d06f),var(--gold,#d4b45c));box-shadow:0 14px 40px rgba(0,0,0,.5),0 0 0 6px rgba(212,180,92,.18);white-space:nowrap;transition:transform .2s,box-shadow .2s}
.agv__snd:hover{transform:translateX(-50%) scale(1.04)}
.agv__snd svg{width:18px;height:18px}
.agv__frame.is-sound .agv__snd{padding:12px;border-radius:50%;left:auto;right:22px;bottom:22px;transform:none;background:rgba(5,5,10,.72);color:#f4d06f;box-shadow:0 0 0 1.5px rgba(212,180,92,.6)}
.agv__frame.is-sound .agv__snd span{display:none}
.agv__bar{position:absolute;left:28px;right:28px;bottom:14px;height:3px;border-radius:2px;background:rgba(255,255,255,.14);overflow:hidden;z-index:4}
.agv__bar i{display:block;height:100%;width:0;background:var(--gold,#d4b45c)}
.agv__cap{margin-top:18px;text-align:center;font-size:.8rem;letter-spacing:.22em;text-transform:uppercase;color:#9a927f}
@media (max-width:960px){.agv__in{grid-template-columns:1fr;gap:44px}.agv__ph{order:-1}.agv__frame{transform:none}.agv__t{font-size:clamp(1.9rem,7vw,2.6rem)}}
@media (prefers-reduced-motion:reduce){.agv__snd{transition:none}}
</style>
	<?php
}, 40 );

add_action( 'wp_footer', function () {
	if ( ! ag_video_pres_on_home() ) { return; }
	$a = ag_video_pres_assets();
	if ( ! $a ) { return; }
	$sites = esc_url( home_url( '/sites-express' ) );
	?>
<template id="agv-tpl">
<section class="agv" id="presentation" aria-label="Alliance Groupe en 52 secondes">
  <div class="agv__in wrap">
    <div class="agv__txt">
      <span class="eyebrow">La présentation · 52 secondes</span>
      <h2 class="agv__t">Alliance Groupe, <em>raconté en moins d'une minute</em></h2>
      <p class="agv__p">De la recherche Google jusqu'au client qui vous appelle&nbsp;: ce qu'on construit pour votre entreprise, et pourquoi ça change tout. Présenté par notre égérie, de Nantes à Napoli.</p>
      <ul class="agv__pts">
        <li>Un site sur mesure, moderne, <strong>livré en 5 jours</strong>.</li>
        <li>Visible sur Google, Maps et dans ChatGPT — <strong>sécurité incluse</strong>.</li>
        <li>Déjà un site&nbsp;? Sa <strong>version 2026, gratuite</strong>, en deux minutes.</li>
      </ul>
      <div class="agv__cta">
        <a class="btn" href="#refais-mon-site"><span class="ag-l">Tester ma maquette gratuite</span></a>
        <a class="btn btn--ghost" href="<?php echo $sites; ?>">Voir les 3 prix</a>
      </div>
    </div>
    <div class="agv__ph">
      <div>
        <div class="agv__frame" id="agvFrame">
          <video id="agvVid" playsinline muted loop preload="none" poster="<?php echo esc_url( $a['poster'] ); ?>" data-src="<?php echo esc_url( $a['mp4'] ); ?>" aria-label="Vidéo de présentation Alliance Groupe"></video>
          <button class="agv__snd" id="agvSnd" type="button" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M19 5a9 9 0 0 1 0 14"/></svg>
            <span>Écouter avec le son</span>
          </button>
          <div class="agv__bar" aria-hidden="true"><i id="agvBar"></i></div>
        </div>
        <div class="agv__cap">Voix française · touche italienne</div>
      </div>
    </div>
  </div>
</section>
</template>
<script id="ag-video-pres-js">
(function(){
  var tpl=document.getElementById('agv-tpl');if(!tpl)return;
  var anchor=document.querySelector('.mq')||document.getElementById('top');if(!anchor)return;
  var node=tpl.content.firstElementChild.cloneNode(true);
  anchor.parentNode.insertBefore(node,anchor.nextSibling);
  var v=document.getElementById('agvVid'),btn=document.getElementById('agvSnd'),fr=document.getElementById('agvFrame'),bar=document.getElementById('agvBar');
  var loaded=false;
  function load(){if(loaded)return;loaded=true;v.src=v.getAttribute('data-src');v.load();}
  function tryPlay(){var p=v.play();if(p&&p.catch){p.catch(function(){});}}
  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(es){es.forEach(function(e){
      if(e.isIntersecting){load();tryPlay();}
      else if(!v.paused&&v.muted){v.pause();}
    });},{rootMargin:'300px 0px',threshold:.25});
    io.observe(node);
  }else{load();tryPlay();}
  btn.addEventListener('click',function(){
    load();
    if(v.muted){v.muted=false;v.currentTime=0;fr.classList.add('is-sound');btn.setAttribute('aria-pressed','true');btn.setAttribute('aria-label','Couper le son');tryPlay();}
    else{v.muted=true;fr.classList.remove('is-sound');btn.setAttribute('aria-pressed','false');btn.removeAttribute('aria-label');}
  });
  v.addEventListener('timeupdate',function(){if(v.duration){bar.style.width=(v.currentTime/v.duration*100)+'%';}});
  var lastT=0;
  v.addEventListener('timeupdate',function(){
    if(!v.muted&&v.currentTime<lastT-1){v.muted=true;fr.classList.remove('is-sound');btn.setAttribute('aria-pressed','false');btn.removeAttribute('aria-label');}
    lastT=v.currentTime;
  });
})();
</script>
	<?php
}, 30 );
