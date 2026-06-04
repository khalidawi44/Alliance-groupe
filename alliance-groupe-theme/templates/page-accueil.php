<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- Hero -->
<section class="ag-hero" id="ag-main-content">
    <?php
    // HERO SPLIT « ÉCLAIR » : 2 images coupées NET par un éclair doré en zigzag.
    //  - GAUCHE = SÉCURITÉ  : option img_secu  -> sinon img_menace -> sinon dégradé cyber.
    //  - DROITE = CRÉATION  : option img_creation -> sinon photo bureau Naples (toi) -> dégradé doré.
    $ag_secu_bg = function_exists( 'ag_tester_opt' ) ? ( ag_tester_opt( 'img_secu' ) ?: ag_tester_opt( 'img_menace' ) ) : '';
    if ( ! $ag_secu_bg ) {
        $p = get_stylesheet_directory() . '/assets/images/securite/secu.jpg';
        if ( file_exists( $p ) ) { $ag_secu_bg = get_stylesheet_directory_uri() . '/assets/images/securite/secu.jpg'; }
    }
    $ag_crea_bg = function_exists( 'ag_tester_opt' ) ? ag_tester_opt( 'img_creation' ) : '';
    if ( ! $ag_crea_bg ) {
        // Ta photo dans le bureau de Naples = visuel « création / l'humain derrière les sites ».
        $p = get_stylesheet_directory() . '/assets/images/team/1_bureau_naples.jpg';
        if ( file_exists( $p ) ) { $ag_crea_bg = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg'; }
    }
    $ag_secu_style = $ag_secu_bg
        ? "background-image:linear-gradient(0deg,rgba(5,7,14,.45),rgba(6,9,18,.35)),url('" . esc_url( $ag_secu_bg ) . "')"
        : 'background-image:radial-gradient(circle at 30% 35%,rgba(40,70,140,.32),transparent 60%),linear-gradient(160deg,#0a1024 0%,#070a14 70%,#05060c 100%)';
    $ag_crea_style = $ag_crea_bg
        ? "background-image:linear-gradient(0deg,rgba(10,8,4,.40),rgba(8,7,12,.32)),url('" . esc_url( $ag_crea_bg ) . "')"
        : 'background-image:radial-gradient(circle at 70% 40%,rgba(243,122,31,.30),transparent 60%),linear-gradient(160deg,#1a130a 0%,#120c08 70%,#0a0a0f 100%)';
    ?>
    <div class="ag-hero__split" aria-hidden="true">
        <div class="ag-hero__half ag-hero__half--secu" style="<?php echo $ag_secu_style; ?>">
            <span class="ag-hero__half-tag">🛡️ Sécurité</span>
        </div>
        <div class="ag-hero__half ag-hero__half--crea" style="<?php echo $ag_crea_style; ?>">
            <span class="ag-hero__half-tag ag-hero__half-tag--r">Création &amp; SEO ✨</span>
        </div>
        <!-- Éclair DORÉ tranchant posé pile sur la jointure (bords nets) -->
        <svg class="ag-hero__bolt" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="agBoltG" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#FFF3CF"/>
                    <stop offset=".5" stop-color="#F3D27A"/>
                    <stop offset="1" stop-color="#F37A1F"/>
                </linearGradient>
            </defs>
            <!-- halo large -->
            <polyline class="ag-bolt-glow" points="58,0 52,18 60,20 46,45 54,47 40,72 48,74 38,100"
                      fill="none" stroke="#F3D27A" stroke-width="2.4" stroke-linejoin="miter"/>
            <!-- trait net -->
            <polyline class="ag-bolt-core" points="58,0 52,18 60,20 46,45 54,47 40,72 48,74 38,100"
                      fill="none" stroke="url(#agBoltG)" stroke-width="0.7" stroke-linejoin="miter"/>
        </svg>
    </div>
    <div class="ag-hero__video-veil" aria-hidden="true"></div>
    <style>
    body.home .ag-hero{min-height:80vh;padding-top:140px;padding-bottom:60px}
    @media(max-width:900px){body.home .ag-hero{min-height:auto;padding-top:120px}}
    .ag-hero__naples,.ag-hero__boats,.ag-hero__particles,.ag-hero__vesuvius,.ag-hero__sunglow{display:none!important}
    /* ── SPLIT ÉCLAIR : la frontière des 2 images EST le zigzag (coupe nette) ── */
    .ag-hero__split{position:absolute;inset:0;z-index:0;overflow:hidden;background:#05060c}
    .ag-hero__half{position:absolute;inset:0;background-size:cover;background-position:center}
    .ag-hero__half--secu{clip-path:polygon(0 0, 58% 0, 52% 18%, 60% 20%, 46% 45%, 54% 47%, 40% 72%, 48% 74%, 38% 100%, 0 100%)}
    .ag-hero__half--crea{clip-path:polygon(58% 0, 100% 0, 100% 100%, 38% 100%, 48% 74%, 40% 72%, 54% 47%, 46% 45%, 60% 20%, 52% 18%)}
    .ag-hero__half-tag{position:absolute;top:20px;left:22px;font-size:.72rem;font-weight:800;letter-spacing:2px;
        text-transform:uppercase;color:#fff;background:rgba(0,0,0,.45);
        border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 13px}
    .ag-hero__half-tag--r{left:auto;right:22px;color:#F3D27A;border-color:rgba(243,210,122,.4)}
    .ag-hero__bolt{position:absolute;inset:0;width:100%;height:100%;z-index:2;pointer-events:none}
    .ag-bolt-glow{opacity:.5;filter:blur(2px)}
    .ag-bolt-core{filter:drop-shadow(0 0 6px rgba(255,220,140,.95));animation:agBoltFlash 3.6s ease-in-out infinite}
    @keyframes agBoltFlash{0%,100%{opacity:1}48%{opacity:1}50%{opacity:.55}52%{opacity:1}}
    /* voile bas léger pour la lisibilité du texte (séparation reste nette en haut) */
    .ag-hero__video-veil{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(5,6,12,.18) 0%,rgba(5,6,12,.30) 45%,rgba(5,6,12,.80) 100%)}
    @media (prefers-reduced-motion: reduce){.ag-bolt-core{animation:none}}
    @media(max-width:600px){.ag-hero__half-tag{top:12px;font-size:.6rem;padding:4px 9px}}
    </style>
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
            Audit · Création · Maintenance de sites web — Naples &amp; Nantes
            <span class="ag-heritage-dots" aria-hidden="true"><span></span><span></span><span></span></span>
        </div>

        <h1 class="ag-hero__title">
            <span class="ag-line">Je <em>crée</em> votre site.</span>
            <span class="ag-line">Je le <em>sécurise</em>.</span>
            <span class="ag-line">Vous, vous respirez.</span>
        </h1>

        <p class="ag-hero__sub">
            Deux métiers, un seul interlocuteur : je <strong>sécurise</strong> votre site (audit + protection) et je <strong>crée des sites pro référencés</strong> (SEO). Chaque jour, <strong>30 000 sites sont piratés</strong> — on commence par révéler vos failles. Avant les autres.
        </p>

        <div class="ag-hero__buttons">
            <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>" class="ag-btn-gold">🔍 Tester mon site →</a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Parlons de votre projet</a>
        </div>

        <!-- 2 PÔLES : ce que je fais concrètement (sécurité / création+SEO) -->
        <div class="ag-hero__poles" aria-label="Mes deux expertises">
            <a class="ag-pole" href="<?php echo esc_url(home_url('/tester-mon-site')); ?>">
                <span class="ag-pole__ic" aria-hidden="true">🛡️</span>
                <span class="ag-pole__txt">
                    <span class="ag-pole__t">Sécurité &amp; audit</span>
                    <span class="ag-pole__d">Je révèle vos failles, je corrige, je surveille. Dès le test gratuit.</span>
                </span>
                <span class="ag-pole__go" aria-hidden="true">→</span>
            </a>
            <a class="ag-pole" href="<?php echo esc_url(home_url('/sites-express')); ?>">
                <span class="ag-pole__ic" aria-hidden="true">✨</span>
                <span class="ag-pole__txt">
                    <span class="ag-pole__t">Création de site &amp; SEO</span>
                    <span class="ag-pole__d">Sites pro qui inspirent confiance et remontent sur Google. Dès 490 €.</span>
                </span>
                <span class="ag-pole__go" aria-hidden="true">→</span>
            </a>
        </div>
        <style>
        .ag-hero__poles{display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:680px;margin:26px 0 0}
        .ag-pole{display:flex;align-items:center;gap:12px;text-decoration:none;
            background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.28);border-radius:14px;
            padding:14px 16px;transition:transform .18s ease,border-color .18s ease,background .18s ease}
        .ag-pole:hover{transform:translateY(-2px);border-color:rgba(243,122,31,.65);background:rgba(255,255,255,.07)}
        .ag-pole__ic{font-size:1.7rem;line-height:1;flex:none}
        .ag-pole__txt{display:flex;flex-direction:column;gap:3px;min-width:0}
        .ag-pole__t{color:#fff;font-weight:800;font-size:1.02rem;line-height:1.15}
        .ag-pole__d{color:rgba(255,255,255,.72);font-size:.84rem;line-height:1.4}
        .ag-pole__go{margin-left:auto;color:#F3D27A;font-size:1.3rem;font-weight:800;flex:none;transition:transform .18s ease}
        .ag-pole:hover .ag-pole__go{transform:translateX(4px)}
        @media(max-width:680px){.ag-hero__poles{grid-template-columns:1fr;gap:10px;margin-top:20px}}
        </style>

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
