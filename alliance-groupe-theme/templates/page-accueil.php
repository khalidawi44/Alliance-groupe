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
        // Visuels sécurité fournis (dossier securite/). Par défaut : hacker masqué + mur LED (fort, coloré).
        foreach ( array( 'secu.jpg', 'max-bender-XIVDN9cxOVc-unsplash.jpg', 'kaptured-by-kasia-7Ss09bTO5Zo-unsplash.jpg' ) as $f ) {
            if ( file_exists( get_stylesheet_directory() . '/assets/images/securite/' . $f ) ) {
                $ag_secu_bg = get_stylesheet_directory_uri() . '/assets/images/securite/' . $f; break;
            }
        }
    }
    $ag_crea_bg = function_exists( 'ag_tester_opt' ) ? ag_tester_opt( 'img_creation' ) : '';
    if ( ! $ag_crea_bg ) {
        // Ta photo dans le bureau de Naples = visuel « création / l'humain derrière les sites ».
        $p = get_stylesheet_directory() . '/assets/images/team/1_bureau_naples.jpg';
        if ( file_exists( $p ) ) { $ag_crea_bg = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg'; }
    }
    $ag_secu_style = $ag_secu_bg
        ? "background-image:linear-gradient(0deg,rgba(5,7,14,.22),rgba(6,9,18,.12)),url('" . esc_url( $ag_secu_bg ) . "')"
        : 'background-image:radial-gradient(circle at 30% 35%,rgba(40,70,140,.32),transparent 60%),linear-gradient(160deg,#0a1024 0%,#070a14 70%,#05060c 100%)';
    $ag_crea_style = $ag_crea_bg
        ? "background-image:linear-gradient(0deg,rgba(10,8,4,.20),rgba(8,7,12,.12)),url('" . esc_url( $ag_crea_bg ) . "')"
        : 'background-image:radial-gradient(circle at 70% 40%,rgba(243,122,31,.30),transparent 60%),linear-gradient(160deg,#1a130a 0%,#120c08 70%,#0a0a0f 100%)';
    ?>
    <div class="ag-hero__split" aria-hidden="true">
        <div class="ag-hero__half ag-hero__half--secu" style="<?php echo $ag_secu_style; ?>">
            <span class="ag-hero__half-tag">🛡️ Sécurité</span>
        </div>
        <div class="ag-hero__half ag-hero__half--crea" style="<?php echo $ag_crea_style; ?>">
            <span class="ag-hero__half-tag ag-hero__half-tag--r">Création &amp; SEO ✨</span>
        </div>
        <!-- VRAI ÉCLAIR : ligne déchiquetée irrégulière + ramifications, halo lumineux -->
        <svg class="ag-hero__bolt" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="agBoltG" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#FFFDF5"/>
                    <stop offset=".5" stop-color="#F6D77A"/>
                    <stop offset="1" stop-color="#F37A1F"/>
                </linearGradient>
            </defs>
            <g fill="none" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke">
                <!-- halo large diffus (diagonale qui TRAVERSE le hero : haut-gauche -> bas-droite) -->
                <polyline class="ag-bolt-halo" points="20,0 35,15 24,29 46,43 34,56 58,69 47,81 74,93 64,100" stroke="#F6D77A" stroke-width="9"/>
                <!-- lueur moyenne doré/orange -->
                <polyline class="ag-bolt-glow" points="20,0 35,15 24,29 46,43 34,56 58,69 47,81 74,93 64,100" stroke="url(#agBoltG)" stroke-width="4"/>
                <!-- ramifications -->
                <polyline class="ag-bolt-glow" points="46,43 56,40 60,47" stroke="url(#agBoltG)" stroke-width="2.4"/>
                <polyline class="ag-bolt-glow" points="34,56 26,60 28,68" stroke="url(#agBoltG)" stroke-width="2.4"/>
                <!-- coeur blanc éclatant -->
                <polyline class="ag-bolt-core" points="20,0 35,15 24,29 46,43 34,56 58,69 47,81 74,93 64,100" stroke="#FFFFFF" stroke-width="1.4"/>
                <polyline class="ag-bolt-core" points="46,43 56,40 60,47" stroke="#FFFFFF" stroke-width="1"/>
                <polyline class="ag-bolt-core" points="34,56 26,60 28,68" stroke="#FFFFFF" stroke-width="1"/>
            </g>
        </svg>
    </div>
    <div class="ag-hero__video-veil" aria-hidden="true"></div>
    <style>
    body.home .ag-hero{min-height:80vh;padding-top:140px;padding-bottom:60px}
    @media(max-width:900px){body.home .ag-hero{min-height:auto;padding-top:120px}}
    .ag-hero__naples,.ag-hero__boats,.ag-hero__particles,.ag-hero__vesuvius,.ag-hero__sunglow{display:none!important}
    /* ── SPLIT ÉCLAIR : la frontière des 2 images suit la ligne déchiquetée ── */
    .ag-hero__split{position:absolute;inset:0;z-index:0;overflow:hidden;background:#05060c}
    .ag-hero__half{position:absolute;inset:0;background-size:cover;background-position:center}
    /* la frontière des 2 images suit la diagonale-éclair (mêmes points que la polyline) */
    .ag-hero__half--secu{clip-path:polygon(0 0, 20% 0, 35% 15%, 24% 29%, 46% 43%, 34% 56%, 58% 69%, 47% 81%, 74% 93%, 64% 100%, 0 100%)}
    .ag-hero__half--crea{clip-path:polygon(20% 0, 100% 0, 100% 100%, 64% 100%, 74% 93%, 47% 81%, 58% 69%, 34% 56%, 46% 43%, 24% 29%, 35% 15%)}
    .ag-hero__half-tag{position:absolute;top:20px;left:22px;font-size:.72rem;font-weight:800;letter-spacing:2px;
        text-transform:uppercase;color:#fff;background:rgba(0,0,0,.45);
        border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 13px}
    .ag-hero__half-tag--r{left:auto;right:22px;color:#F3D27A;border-color:rgba(243,210,122,.4)}
    .ag-hero__bolt{position:absolute;inset:0;width:100%;height:100%;z-index:2;pointer-events:none}
    .ag-bolt-halo{opacity:.45;filter:blur(5px)}
    .ag-bolt-glow{filter:drop-shadow(0 0 5px rgba(246,215,122,.9))}
    .ag-bolt-core{filter:drop-shadow(0 0 5px rgba(255,255,255,1)) drop-shadow(0 0 14px rgba(246,215,122,.85))}
    .ag-hero__bolt g{animation:agBoltFlash 5s ease-in-out infinite}
    @keyframes agBoltFlash{0%,7%,100%{opacity:1}3%{opacity:.55}4%{opacity:1}40%{opacity:1}42%{opacity:.7}44%{opacity:1}}
    /* voile bas léger pour la lisibilité du texte (séparation reste nette en haut) */
    .ag-hero__video-veil{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(5,6,12,.10) 0%,rgba(5,6,12,.22) 45%,rgba(5,6,12,.78) 100%)}
    @media (prefers-reduced-motion: reduce){.ag-hero__bolt g{animation:none}}
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

        <h1 class="ag-hero__title ag-hero__title--cols">
            <span class="ag-col ag-col--secu">
                <span class="ag-col__k">🛡️ Sécurité</span>
                <span class="ag-l--secu">Je le <em>sécurise</em>.</span>
            </span>
            <span class="ag-col ag-col--crea">
                <span class="ag-col__k">✨ Création &amp; SEO</span>
                <span class="ag-l--crea">Je <em>crée</em> votre site.</span>
            </span>
        </h1>
        <p class="ag-hero__title-zen ag-l--zen">Vous, vous respirez.</p>
        <style>
        /* Titre en 2 COLONNES : sécurité (gauche) / création (droite), de part et d'autre de l'éclair */
        .ag-hero__title--cols{display:grid;grid-template-columns:1fr 1fr;gap:10px 56px;align-items:end;margin:0 auto 6px;max-width:880px}
        .ag-hero__title--cols .ag-col{display:flex;flex-direction:column;gap:6px}
        .ag-col--secu{text-align:right;align-items:flex-end}
        .ag-col--crea{text-align:left;align-items:flex-start}
        .ag-col__k{font-size:.74rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;opacity:.85}
        .ag-col--secu .ag-col__k{color:#FF8A8A}
        .ag-col--crea .ag-col__k{color:#9CCBFF}
        .ag-hero__title--cols em{font-style:normal}
        .ag-l--crea{color:#5BA8FF;text-shadow:0 2px 18px rgba(40,110,220,.5)}
        .ag-l--crea em{color:#9CCBFF}
        .ag-l--secu{color:#FF5A5A;text-shadow:0 2px 18px rgba(225,15,26,.5)}
        .ag-l--secu em{color:#FF8A8A}
        .ag-hero__title-zen{text-align:center;font-family:Georgia,'Playfair Display',serif;
            font-size:clamp(1.7rem,4.5vw,3rem);line-height:1.1;margin:4px auto 22px;
            color:#F3D27A;text-shadow:0 2px 22px rgba(243,210,122,.45)}
        @media(max-width:760px){
            .ag-hero__title--cols{grid-template-columns:1fr;gap:14px;text-align:center}
            .ag-col--secu,.ag-col--crea{text-align:center;align-items:center}
        }
        </style>

        <p class="ag-hero__sub">
            Deux métiers, un seul interlocuteur : je <strong>sécurise</strong> votre site (audit + protection) et je <strong>crée des sites pro référencés</strong> (SEO). Chaque jour, <strong>30 000 sites sont piratés</strong> — on commence par révéler vos failles. Avant les autres.
        </p>

        <!-- TEST LÉGER INSTANTANÉ : URL -> score + nb failles, sans quitter la page -->
        <form class="ag-qt" id="ag-qt" autocomplete="off">
            <label class="ag-qt__lbl" for="ag-qt-url">🔍 Testez la sécurité de votre site — gratuit, instantané</label>
            <div class="ag-qt__row">
                <input type="text" id="ag-qt-url" name="url" inputmode="url" placeholder="monsite.fr" required>
                <button type="submit" class="ag-btn-gold ag-qt__btn">Analyser →</button>
            </div>
            <div class="ag-qt__hint">Diagnostic non-intrusif (lecture publique). Aucun mot de passe demandé.</div>
            <div class="ag-qt__result" id="ag-qt-result" hidden></div>
        </form>

        <div class="ag-hero__buttons">
            <a href="<?php echo esc_url(home_url('/sites-express')); ?>" class="ag-btn-outline">✨ Je veux un site qui me ressemble →</a>
            <a href="#" data-ag-hack class="ag-btn-ghost">👁️ Voir à quoi ressemble un piratage</a>
        </div>
        <style>
        .ag-qt{max-width:620px;margin:26px 0 0;background:rgba(255,255,255,.05);border:1px solid rgba(243,210,122,.35);
            border-radius:16px;padding:16px 18px}
        .ag-qt__lbl{display:block;color:#fff;font-weight:800;font-size:1.02rem;margin-bottom:10px}
        .ag-qt__row{display:flex;gap:10px}
        .ag-qt__row input{flex:1;min-width:0;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.22);
            border-radius:10px;padding:13px 15px;color:#fff;font-size:1rem}
        .ag-qt__row input::placeholder{color:rgba(255,255,255,.45)}
        .ag-qt__btn{white-space:nowrap}
        .ag-qt__hint{color:rgba(255,255,255,.55);font-size:.78rem;margin-top:8px}
        .ag-qt__result{margin-top:14px;border-radius:12px;padding:14px 16px;display:none}
        .ag-qt__result.is-show{display:block;animation:agQtIn .4s ease}
        @keyframes agQtIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
        .ag-qt__score{display:flex;align-items:center;gap:14px}
        .ag-qt__num{font-family:Georgia,serif;font-size:2.4rem;font-weight:800;line-height:1}
        .ag-qt__bar{flex:1;height:10px;border-radius:99px;background:rgba(255,255,255,.12);overflow:hidden}
        .ag-qt__bar i{display:block;height:100%;border-radius:99px;transition:width .8s cubic-bezier(.23,1,.32,1)}
        .ag-qt__txt{color:rgba(255,255,255,.85);font-size:.92rem;margin-top:10px;line-height:1.45}
        .ag-qt__cta{display:inline-block;margin-top:12px;background:linear-gradient(135deg,#F37A1F,#D4B45C);
            color:#0a0a0f;font-weight:800;text-decoration:none;padding:11px 20px;border-radius:99px;font-size:.95rem}
        .ag-btn-ghost{display:inline-flex;align-items:center;gap:6px;color:#fff;text-decoration:none;
            padding:14px 22px;border-radius:99px;border:1px dashed rgba(255,255,255,.35);font-weight:700;transition:.18s}
        .ag-btn-ghost:hover{border-color:#F3D27A;color:#F3D27A}
        @media(max-width:560px){.ag-qt__row{flex-direction:column}}
        </style>
        <script>
        (function(){
            var f=document.getElementById('ag-qt'); if(!f) return;
            var inp=document.getElementById('ag-qt-url'), out=document.getElementById('ag-qt-result');
            var btn=f.querySelector('.ag-qt__btn');
            var AJAX='<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
            var NONCE='<?php echo esc_js( wp_create_nonce( 'ag_quicktest' ) ); ?>';
            function col(s){return s>=75?'#28a745':(s>=50?'#F0A020':'#E10F1A');}
            f.addEventListener('submit',function(e){
                e.preventDefault();
                var url=(inp.value||'').trim(); if(!url) return;
                btn.disabled=true; var old=btn.textContent; btn.textContent='Analyse…';
                out.hidden=false; out.className='ag-qt__result is-show';
                out.style.background='rgba(255,255,255,.05)'; out.style.border='1px solid rgba(255,255,255,.15)';
                out.innerHTML='<div class="ag-qt__txt">⏳ Analyse de <strong>'+url.replace(/[<>]/g,'')+'</strong> en cours…</div>';
                var fd=new FormData(); fd.append('action','ag_quicktest'); fd.append('nonce',NONCE); fd.append('url',url);
                fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'})
                    .then(function(r){return r.json();})
                    .then(function(j){
                        btn.disabled=false; btn.textContent=old;
                        if(!j||!j.success){ out.innerHTML='<div class="ag-qt__txt">⚠️ '+((j&&j.data&&j.data.msg)||'Analyse impossible.')+'</div>'; return; }
                        var d=j.data, c=col(d.score);
                        out.style.border='1px solid '+c;
                        out.innerHTML=
                            '<div class="ag-qt__score">'
                            +'<span class="ag-qt__num" style="color:'+c+'">'+d.score+'<span style="font-size:1rem;color:rgba(255,255,255,.55)">/100</span></span>'
                            +'<span class="ag-qt__bar"><i style="width:'+d.score+'%;background:'+c+'"></i></span>'
                            +'</div>'
                            +'<div class="ag-qt__txt"><strong>'+d.host+'</strong> — '+d.secu+' faille(s) de sécurité'+(d.crit?' dont <strong style="color:#ff6b6b">'+d.crit+' critique(s)</strong>':'')+'. '+d.msg+'</div>'
                            +'<a class="ag-qt__cta" href="'+d.order+'">🔓 Voir le rapport complet + comment corriger →</a>';
                    })
                    .catch(function(){ btn.disabled=false; btn.textContent=old; out.innerHTML='<div class="ag-qt__txt">⚠️ Erreur réseau. Réessayez.</div>'; });
            });
        })();
        </script>

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
