<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- Hero -->
<section class="ag-hero" id="ag-main-content">
    <!-- Vidéo Naples en fond (remplace la photo du hero) -->
    <video class="ag-hero__video" autoplay muted loop playsinline preload="auto" poster="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/cities/naples-1.jpg' ); ?>">
        <source src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/video/naples.mp4' ); ?>" type="video/mp4">
    </video>
    <div class="ag-hero__video-veil" aria-hidden="true"></div>
    <style>
    /* Home : hero plus court (on enchaîne vite sur la menace) */
    body.home .ag-hero{min-height:74vh;padding-top:140px;padding-bottom:60px}
    @media(max-width:900px){body.home .ag-hero{min-height:auto;padding-top:120px}}
    .ag-hero__naples{display:none!important}
    /* Vidéo Naples : remplit sans trop zoomer (cadrage haut = baie + Vésuve) */
    .ag-hero__video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 35%;z-index:0}
    .ag-hero__video-veil{position:absolute;inset:0;z-index:0;background:linear-gradient(180deg,rgba(10,10,15,.5) 0%,rgba(10,10,15,.72) 100%)}
    </style>
    <!-- Photo Naples (fallback masqué) -->
    <div class="ag-hero__naples" aria-hidden="true"></div>
    <!-- Halo de soleil/Vésuve qui pulse champagne (visible partout) -->
    <div class="ag-hero__sunglow" aria-hidden="true"></div>
    <!-- Particules dorées montantes en CSS pur (visibles mobile + desktop) -->
    <div class="ag-hero__particles" aria-hidden="true">
        <span style="left:8%;  bottom:-10px; animation-duration:14s; animation-delay:0s;"></span>
        <span style="left:18%; bottom:-10px; animation-duration:18s; animation-delay:3s;"></span>
        <span style="left:27%; bottom:-10px; animation-duration:12s; animation-delay:6s;"></span>
        <span style="left:36%; bottom:-10px; animation-duration:16s; animation-delay:1.5s;"></span>
        <span style="left:44%; bottom:-10px; animation-duration:20s; animation-delay:4.5s;"></span>
        <span style="left:52%; bottom:-10px; animation-duration:13s; animation-delay:7s;"></span>
        <span style="left:60%; bottom:-10px; animation-duration:17s; animation-delay:2s;"></span>
        <span style="left:68%; bottom:-10px; animation-duration:19s; animation-delay:5s;"></span>
        <span style="left:76%; bottom:-10px; animation-duration:15s; animation-delay:8s;"></span>
        <span style="left:84%; bottom:-10px; animation-duration:21s; animation-delay:0.8s;"></span>
        <span style="left:92%; bottom:-10px; animation-duration:14s; animation-delay:3.6s;"></span>
        <span style="left:12%; bottom:-10px; animation-duration:23s; animation-delay:10s;"></span>
    </div>
    <!-- 🌋 Vésuve en éruption : halo seulement (fumée retirée sur demande user) -->
    <div class="ag-hero__vesuvius" aria-hidden="true"></div>
    <!-- 🚢 Bateaux qui glissent sur la baie -->
    <div class="ag-hero__boats" aria-hidden="true">
        <svg viewBox="0 0 50 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M2 18 L48 18 L42 22 L8 22 Z" fill="currentColor"/>
            <path d="M22 18 L22 4 L34 14 L22 14 Z" fill="currentColor" opacity=".7"/>
            <line x1="22" y1="4" x2="22" y2="18" stroke="currentColor" stroke-width="1"/>
        </svg>
        <svg viewBox="0 0 50 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M4 19 L46 19 L41 22 L9 22 Z" fill="currentColor"/>
            <path d="M25 19 L25 6 L36 16 L25 16 Z" fill="currentColor" opacity=".75"/>
        </svg>
        <svg viewBox="0 0 50 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M2 18 L48 18 L43 22 L7 22 Z" fill="currentColor"/>
            <path d="M20 18 L20 3 L33 14 L20 14 Z" fill="currentColor" opacity=".7"/>
            <line x1="20" y1="3" x2="20" y2="18" stroke="currentColor" stroke-width="1"/>
            <path d="M33 14 L20 14 L20 18 L33 18" fill="none" stroke="currentColor" stroke-width=".6" opacity=".5"/>
        </svg>
    </div>
    <!-- Téléphone CSS retiré sur demande user. -->
    <!-- Mesh gradient WebGL conservé en sur-couche très subtile (skippé sur mobile/<4 cores) -->
    <?php get_template_part('template-parts/mesh-gradient-bg'); ?>
    <!-- Grille tech high-tech parallax qui réagit à la souris -->
    <?php get_template_part('template-parts/hero-tech-grid'); ?>
    <!-- Scène 3D Three.js temporairement désactivée — on garde le téléphone CSS qui marche partout. -->
    <?php // get_template_part('template-parts/hero-3d-scene'); ?>
    <div class="ag-hero__bg">
        <div class="ag-hero__circles">
            <div class="ag-hero__circle"></div>
            <div class="ag-hero__circle"></div>
            <div class="ag-hero__circle"></div>
            <div class="ag-hero__circle"></div>
        </div>
        <div class="ag-hero__orb ag-hero__orb--1"></div>
        <div class="ag-hero__orb ag-hero__orb--2"></div>
    </div>

    <div class="ag-hero__content">
        <div class="ag-hero__badge">
            <span class="ag-hero__dot"></span>
            Audit · Création · Maintenance de sites web — Nantes
            <span class="ag-heritage-dots" aria-hidden="true"><span></span><span></span><span></span></span>
        </div>

        <h1 class="ag-hero__title">
            <span class="ag-line">Votre site web</span>
            <span class="ag-line"><em>est une cible.</em></span>
            <span class="ag-line">Mettons-le à l'abri.</span>
        </h1>

        <p class="ag-hero__sub">
            J'audite, je sécurise et je crée des sites qui inspirent confiance — et qui le restent. Chaque jour, <strong>30 000 sites sont piratés</strong>. On commence par révéler vos failles. Avant les autres.
        </p>

        <div class="ag-hero__buttons">
            <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>" class="ag-btn-gold">🔍 Tester mon site →</a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Parlons de votre projet</a>
        </div>

        <div class="ag-hero__metrics">
            <div class="ag-metric">
                <span class="ag-metric__value">48 h</span>
                <span class="ag-metric__label">Audit rendu</span>
            </div>
            <div class="ag-metric">
                <span class="ag-metric__value">24/7</span>
                <span class="ag-metric__label">Surveillance</span>
            </div>
            <div class="ag-metric">
                <span class="ag-metric__value">1</span>
                <span class="ag-metric__label">Interlocuteur unique</span>
            </div>
        </div>

        <div class="ag-hero__scroll">
            <span>Découvrir</span>
            <span class="ag-hero__scroll-line"></span>
            <span class="ag-hero__scroll-dot"></span>
        </div>
    </div>
</section>

<!-- ⚡ LA MENACE EN DIRECT — juste après le hero : on capte la peur, puis
       on donne la solution dans la même section (globe à gauche, audit à droite) -->
<?php get_template_part('template-parts/menace-live'); ?>

<!-- "Choisissez votre parcours" — 4 panneaux priorisés (audit / création / maintenance / templates) -->
<?php get_template_part('template-parts/paths-hero'); ?>

<!-- Preuve sociale REMONTÉE : avis Google (avant de demander d'acheter) -->
<?php get_template_part('template-parts/temoignages'); ?>

<!-- Bande de réassurance (remplace l'ancienne citation parallax) -->
<section class="ag-trust" aria-label="Garanties">
	<div class="ag-trust__inner">
		<span class="ag-trust__item">⏱️ Réponse sous 24 h</span>
		<span class="ag-trust__sep">·</span>
		<span class="ag-trust__item">📝 Devis gratuit</span>
		<span class="ag-trust__sep">·</span>
		<span class="ag-trust__item">💳 Paiement 4× sans frais</span>
		<span class="ag-trust__sep">·</span>
		<span class="ag-trust__item">🔐 Conforme RGPD</span>
		<span class="ag-trust__sep">·</span>
		<span class="ag-trust__item">👤 Interlocuteur unique</span>
	</div>
</section>
<style>
.ag-trust{background:#0a0a12;border-top:1px solid rgba(212,180,92,.16);border-bottom:1px solid rgba(212,180,92,.16);padding:18px 20px}
.ag-trust__inner{max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:8px 14px;text-align:center}
.ag-trust__item{color:rgba(255,255,255,.85);font-size:.92rem;font-weight:600;white-space:nowrap}
.ag-trust__sep{color:rgba(212,180,92,.5)}
@media(max-width:600px){.ag-trust__sep{display:none}.ag-trust__item{font-size:.85rem}}
</style>

<!-- P1/ VENDRE — offres création + maintenance (4x sans frais, sécurisé) -->
<?php get_template_part('template-parts/home-offres'); ?>

<!-- Templates métier — revenu passif, discret, plus bas -->
<?php get_template_part('template-parts/templates-cta'); ?>

<!-- About : studio solo (Fabrizio) — confiance / artisan unique -->
<?php get_template_part('template-parts/about'); ?>

<!-- FAQ -->
<?php get_template_part('template-parts/faq'); ?>

<!-- CTA -->
<?php get_template_part('template-parts/cta'); ?>

<!-- Bouton audit collant (sticky) + renversement du risque -->
<div class="ag-stickycta" id="ag-stickycta" aria-hidden="false">
	<div class="ag-stickycta__inner">
		<span class="ag-stickycta__txt">Si on ne trouve rien d'exploitable, <strong>on vous le dit.</strong></span>
		<a href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>" class="ag-stickycta__btn">🔍 Tester mon site →</a>
	</div>
</div>
<style>
.ag-stickycta{position:fixed;left:0;right:0;bottom:0;z-index:9990;transform:translateY(120%);transition:transform .4s cubic-bezier(.16,1,.3,1);pointer-events:none}
.ag-stickycta.is-on{transform:none;pointer-events:auto}
.ag-stickycta__inner{max-width:760px;margin:0 auto 14px;display:flex;align-items:center;gap:16px;justify-content:space-between;background:rgba(12,12,20,.92);backdrop-filter:blur(10px);border:1px solid rgba(212,180,92,.35);border-radius:100px;padding:10px 12px 10px 22px;box-shadow:0 16px 50px rgba(0,0,0,.5)}
.ag-stickycta__txt{color:rgba(255,255,255,.9);font-size:.92rem;line-height:1.3}
.ag-stickycta__txt strong{color:#F3D27A}
.ag-stickycta__btn{flex-shrink:0;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;text-decoration:none;padding:13px 22px;border-radius:100px;font-size:.95rem;white-space:nowrap;transition:transform .2s}
.ag-stickycta__btn:hover{transform:scale(1.03)}
@media(max-width:560px){
	.ag-stickycta__inner{margin:0 10px 10px;padding:8px 8px 8px 16px;gap:10px}
	.ag-stickycta__txt{display:none}
	.ag-stickycta__btn{flex:1;text-align:center;padding:14px}
}
</style>
<script>
(function(){
	var el = document.getElementById('ag-stickycta');
	if(!el) return;
	var hero = document.getElementById('ag-main-content');
	function onScroll(){
		// Apparaît dès qu'on a dépassé le hero, disparaît tout en bas (footer)
		var past = window.scrollY > (hero ? hero.offsetHeight * 0.8 : 600);
		var nearBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 220);
		if(past && !nearBottom){ el.classList.add('is-on'); }
		else { el.classList.remove('is-on'); }
	}
	window.addEventListener('scroll', onScroll, {passive:true});
	onScroll();
})();
</script>

<?php get_footer(); ?>
