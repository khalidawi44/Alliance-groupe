<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- Hero -->
<section class="ag-hero" id="ag-main-content">
    <?php
    // HERO SPLIT LÉGER : 2 images (compressées) côte à côte, séparées par un trait doré droit.
    //  - GAUCHE = SÉCURITÉ  : option img_secu -> img_menace -> hero-secu.jpg (hacker, compressé).
    //  - DROITE = CRÉATION  : option img_creation -> photo bureau Naples (compressée).
    $ag_secu_bg = function_exists( 'ag_tester_opt' ) ? ( ag_tester_opt( 'img_secu' ) ?: ag_tester_opt( 'img_menace' ) ) : '';
    if ( ! $ag_secu_bg ) {
        foreach ( array( 'secu.jpg', 'hero-secu.jpg' ) as $f ) {
            if ( file_exists( get_stylesheet_directory() . '/assets/images/securite/' . $f ) ) {
                $ag_secu_bg = get_stylesheet_directory_uri() . '/assets/images/securite/' . $f; break;
            }
        }
    }
    $ag_crea_bg = function_exists( 'ag_tester_opt' ) ? ag_tester_opt( 'img_creation' ) : '';
    if ( ! $ag_crea_bg ) {
        $p = get_stylesheet_directory() . '/assets/images/team/1_bureau_naples.jpg';
        if ( file_exists( $p ) ) { $ag_crea_bg = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg'; }
    }
    // Voile ROUGE-NOIR sombre sur le hacker (gauche) ; voile sombre neutre à droite.
    $ag_secu_style = $ag_secu_bg
        ? "background-image:linear-gradient(180deg,rgba(40,4,6,.45),rgba(8,4,6,.62)),linear-gradient(90deg,rgba(120,12,16,.28),rgba(5,5,8,.2)),url('" . esc_url( $ag_secu_bg ) . "')"
        : 'background-image:linear-gradient(160deg,#240a0c 0%,#120608 70%,#080406 100%)';
    $ag_crea_style = $ag_crea_bg
        ? "background-image:linear-gradient(180deg,rgba(8,8,12,.32),rgba(6,6,10,.5)),url('" . esc_url( $ag_crea_bg ) . "')"
        : 'background-image:linear-gradient(160deg,#1a130a 0%,#120c08 70%,#0a0a0f 100%)';
    ?>
    <div class="ag-hero__split" aria-hidden="true">
        <div class="ag-hero__half ag-hero__half--secu" style="<?php echo $ag_secu_style; ?>">
            <span class="ag-hero__half-tag">🛡️ Sécurité</span>
        </div>
        <div class="ag-hero__half ag-hero__half--crea" style="<?php echo $ag_crea_style; ?>">
            <span class="ag-hero__half-tag ag-hero__half-tag--r">Création &amp; SEO ✨</span>
        </div>
        <!-- Ligne diagonale dorée FINE : 2px max (non-scaling), 0 filtre, 0 animation -->
        <svg class="ag-hero__line" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <line x1="58" y1="0" x2="42" y2="100" stroke="#F6D77A" stroke-width="2" vector-effect="non-scaling-stroke"/>
        </svg>
    </div>
    <div class="ag-hero__video-veil" aria-hidden="true"></div>
    <style>
    body.home .ag-hero{min-height:78vh;padding-top:140px;padding-bottom:60px}
    @media(max-width:900px){body.home .ag-hero{min-height:auto;padding-top:120px}}
    .ag-hero__naples,.ag-hero__boats,.ag-hero__particles,.ag-hero__vesuvius,.ag-hero__sunglow{display:none!important}
    /* SPLIT DIAGONAL LÉGER : clip-path (pas cher) + ligne SVG, 0 filtre, 0 animation */
    .ag-hero__split{position:absolute;inset:0;z-index:0;overflow:hidden;background:#05060c}
    .ag-hero__half{position:absolute;inset:0;background-size:cover;background-position:center}
    .ag-hero__half--secu{clip-path:polygon(0 0, 58% 0, 42% 100%, 0 100%)}
    .ag-hero__half--crea{clip-path:polygon(58% 0, 100% 0, 100% 100%, 42% 100%)}
    .ag-hero__line{position:absolute;inset:0;width:100%;height:100%;z-index:2;pointer-events:none}
    .ag-hero__half-tag{position:absolute;top:20px;left:22px;font-size:.72rem;font-weight:800;letter-spacing:2px;
        text-transform:uppercase;color:#fff;background:rgba(0,0,0,.5);
        border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 13px}
    .ag-hero__half-tag--r{left:auto;right:22px;color:#F3D27A;border-color:rgba(243,210,122,.4)}
    .ag-hero__video-veil{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(5,6,12,.12) 0%,rgba(5,6,12,.24) 45%,rgba(5,6,12,.80) 100%)}
    @media(max-width:600px){.ag-hero__half-tag{top:12px;font-size:.6rem;padding:4px 9px}}
    </style>
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

        <h1 class="ag-hero__title ag-hero__title--2c">
            <span class="ag-2c ag-2c--secu">
                <span class="ag-2c__k">🛡️ Sécurité</span>
                <span class="ag-2c__l">Je le sécurise.</span>
            </span>
            <span class="ag-2c ag-2c--crea">
                <span class="ag-2c__k">✨ Création &amp; SEO</span>
                <span class="ag-2c__l">Je crée votre site.</span>
            </span>
        </h1>
        <p class="ag-hero__zen">Vous, vous respirez.</p>
        <style>
        /* Titre 2 COLONNES : gauche SÉCU (rouge), droite CRÉA (bleu), chacune sur 1 LIGNE.
           Police FORCÉE Manrope partout (plus aucun mélange serif). L'éclair passe entre. */
        .ag-hero__title--2c{display:grid;grid-template-columns:1fr 1fr;gap:0 64px;align-items:center;
            max-width:940px;margin:0 auto 4px;font-family:var(--font-sans),'Manrope',sans-serif;font-weight:800;line-height:1.08}
        .ag-2c{display:flex;flex-direction:column;gap:4px}
        .ag-2c--secu{text-align:right;align-items:flex-end}
        .ag-2c--crea{text-align:left;align-items:flex-start}
        .ag-2c__k{font-size:.7rem;font-weight:800;letter-spacing:2px;text-transform:uppercase}
        .ag-2c--secu .ag-2c__k{color:#FF8A8A}
        .ag-2c--crea .ag-2c__k{color:#9CCBFF}
        .ag-2c__l{white-space:nowrap;font-size:clamp(1.15rem,2.4vw,2rem);line-height:1.05;letter-spacing:-.01em}
        .ag-2c--secu .ag-2c__l{color:#FF5A5A;text-shadow:0 2px 18px rgba(225,15,26,.5)}
        .ag-2c--crea .ag-2c__l{color:#5BA8FF;text-shadow:0 2px 18px rgba(40,110,220,.5)}
        .ag-hero__zen{text-align:center;font-family:var(--font-sans),'Manrope',sans-serif;font-weight:800;
            color:#F3D27A;text-shadow:0 2px 20px rgba(243,210,122,.4);
            font-size:clamp(1.15rem,2.5vw,2rem);line-height:1.1;white-space:nowrap;margin:6px auto 20px}
        @media(max-width:700px){
            .ag-hero__title--2c{grid-template-columns:1fr;gap:12px;text-align:center}
            .ag-2c--secu,.ag-2c--crea{text-align:center;align-items:center}
            .ag-2c__l,.ag-hero__zen{white-space:normal}
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

<!-- Effet « remontée » : le HERO reste épinglé (sticky, images fixes), tout le
     contenu suivant REMONTE par-dessus au scroll (au lieu que la page descende). -->
<div class="ag-rise">

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

</div><!-- /.ag-rise -->
<style>
/* HERO ÉPINGLÉ : reste en place (images fixes) pendant que le contenu remonte au-dessus. */
body.home .ag-hero{position:sticky;top:0;z-index:0}
body.home .ag-rise{position:relative;z-index:1;background:var(--color-bg)}
/* le footer doit aussi passer au-dessus du hero épinglé tout en bas */
body.home .ag-footer{position:relative;z-index:1}
</style>

<?php get_footer(); ?>
