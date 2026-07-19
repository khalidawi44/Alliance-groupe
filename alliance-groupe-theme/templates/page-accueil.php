<?php
/**
 * Template Name: Accueil
 */
get_header();
?>

<!-- 🎬 Bandeau vidéo (header) — autoplay muet en boucle + bouton son -->
<section class="ag-headvid" aria-label="Vidéo de présentation Alliance Groupe">
	<video class="ag-headvid__v" autoplay muted loop playsinline preload="auto"
	       poster="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/videos/ads/promo-poster.jpg' ); ?>">
		<source src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/videos/ads/alliance-promo-web.mp4' ); ?>" type="video/mp4">
	</video>
	<div class="ag-headvid__veil"></div>
	<div class="ag-headvid__in">
		<span class="ag-headvid__eyebrow">DE NAPLES À NANTES</span>
		<h1 class="ag-headvid__title">Je <span class="ag-uni--secu">sécurise</span> &amp; <span class="ag-uni--crea">je crée</span> votre site web</h1>
		<form class="ag-headvid__scan" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="var i=this.site_url; if(i.value && !/^https?:\/\//i.test(i.value)) i.value='https://'+i.value;">
			<input type="hidden" name="action" value="ag_tester_run">
			<?php wp_nonce_field( 'ag_tester' ); ?>
			<input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px">
			<input type="hidden" name="result_page" value="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">
			<input type="text" name="site_url" inputmode="url" placeholder="monsite.fr" required class="ag-headvid__scanin" aria-label="Adresse de votre site">
			<button type="submit" class="ag-headvid__scanbtn">🔍 Tester mon site</button>
		</form>
		<div class="ag-headvid__ticker"><span class="ag-headvid__live"><i></i> En direct</span> <b id="ag-hv-count">30&nbsp;000</b> sites piratés aujourd'hui</div>
		<div class="ag-headvid__cta">
			<a class="ag-btn-gold" href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Devis gratuit</a>
			<button type="button" class="ag-headvid__sound" aria-label="Activer le son">🔇 Activer le son</button>
		</div>
		<div class="ag-headvid__metrics"><span><b>48 h</b> Audit</span><span><b>24/7</b> Surveillance</span><span><b>1</b> Interlocuteur</span></div>
	</div>
</section>
<style>
.ag-headvid{position:sticky;top:0;z-index:0;width:100%;height:100vh;height:100svh;overflow:hidden;background:#0a0a0f}
.ag-headvid__v{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0}
.ag-headvid__veil{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(10,10,15,.35) 0%,rgba(10,10,15,.78) 100%)}
.ag-headvid__in{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px}
.ag-headvid__eyebrow{letter-spacing:.3em;font-size:.82rem;color:#D4B45C;font-weight:700}
.ag-headvid__title{font-size:clamp(1.7rem,4.4vw,3rem);color:#fff;margin:.25em 0 .55em;line-height:1.12;max-width:16ch;font-weight:800}
.ag-headvid .ag-uni--secu{color:#ff5c5c;text-shadow:0 2px 18px rgba(225,15,26,.5)}
.ag-headvid .ag-uni--crea{color:#5ba8ff;text-shadow:0 2px 18px rgba(40,110,220,.5)}
/* mini-test "Tester mon site" fondu sur la vidéo */
.ag-headvid__scan{display:flex;gap:8px;width:100%;max-width:440px;margin:0 auto;background:rgba(255,255,255,.08);border:1px solid rgba(255,90,90,.45);border-radius:999px;padding:6px 6px 6px 16px;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
.ag-headvid__scanin{flex:1;min-width:0;background:transparent;border:0;outline:none;color:#fff;font-size:1rem}
.ag-headvid__scanin::placeholder{color:rgba(255,255,255,.6)}
.ag-headvid__scanbtn{white-space:nowrap;border:0;border-radius:999px;padding:11px 18px;font-weight:800;cursor:pointer;background:rgba(255,90,90,.9);color:#fff;transition:.18s}
.ag-headvid__scanbtn:hover{background:#ff5a5a}
/* compteur de menaces "en direct" */
.ag-headvid__ticker{margin:14px auto 0;display:inline-flex;align-items:center;gap:10px;padding:7px 14px;border-radius:12px;background:rgba(255,60,60,.1);border:1px solid rgba(255,92,92,.3);color:rgba(255,255,255,.9);font-size:.9rem}
.ag-headvid__ticker b{color:#fff;font-weight:800;font-variant-numeric:tabular-nums}
.ag-headvid__live{display:inline-flex;align-items:center;gap:6px;font-size:.66rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#ff5c5c}
.ag-headvid__live i{width:8px;height:8px;border-radius:50%;background:#ff5c5c;animation:agHvPulse 1.8s infinite}
@keyframes agHvPulse{0%{box-shadow:0 0 0 0 rgba(255,92,92,.5)}70%{box-shadow:0 0 0 9px rgba(255,92,92,0)}100%{box-shadow:0 0 0 0 rgba(255,92,92,0)}}
.ag-headvid__cta{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;align-items:center;margin-top:16px}
.ag-headvid__sound{cursor:pointer;border:2px solid rgba(212,180,92,.8);background:rgba(0,0,0,.35);color:#D4B45C;font-weight:700;padding:11px 18px;border-radius:999px}
.ag-headvid__sound:hover{background:rgba(212,180,92,.15)}
.ag-headvid__metrics{display:flex;flex-wrap:wrap;gap:8px 20px;justify-content:center;margin-top:18px;color:rgba(255,255,255,.72);font-size:.82rem}
.ag-headvid__metrics b{color:#f4d98b;font-weight:800}
@media(prefers-reduced-motion:reduce){.ag-headvid__live i{animation:none}}
/* MOBILE : on N'épingle PAS et on N'agrandit PAS la vidéo en plein écran (sinon elle est rognée).
   Elle s'affiche ENTIÈRE en 16:9 (toute l'image), le titre + CTA passent EN DESSOUS. */
@media(max-width:768px){
	.ag-headvid{position:relative;top:auto;height:auto;overflow:visible}
	.ag-headvid__v{position:relative;inset:auto;width:100%;height:auto;object-fit:contain;display:block;background:#0a0a0f}
	.ag-headvid__veil{display:none}
	.ag-headvid__in{position:relative;z-index:2;height:auto;justify-content:flex-start;padding:18px 16px 26px}
	/* Le titre vidéo est désormais le H1 unique de l'accueil (2e héros retiré) : on l'affiche, compact. */
	.ag-headvid__title{font-size:1.5rem;margin:.15em 0 .6em;max-width:none}
	.ag-headvid__scanbtn{padding:11px 14px;font-size:.9rem}
	.ag-headvid__metrics{font-size:.78rem;gap:6px 16px}
}
</style>
<script>
(function(){var s=document.querySelector('.ag-headvid');if(!s)return;var v=s.querySelector('.ag-headvid__v'),b=s.querySelector('.ag-headvid__sound');
v&&v.play&&v.play().catch(function(){});
b&&b.addEventListener('click',function(){if(!v)return;v.muted=!v.muted;if(!v.muted){v.play().catch(function(){});b.textContent='🔊 Couper le son';}else{b.textContent='🔇 Activer le son';}});
/* Compteur de menaces "en direct" : monte vers 30 000 (chiffre réel cité sur le site). */
var cnt=document.getElementById('ag-hv-count');
if(cnt){
	var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var goal=30000;
	function f(n){return Math.round(n).toLocaleString('fr-FR');}
	if(reduce){cnt.textContent=f(goal);}
	else{var t0=null;requestAnimationFrame(function step(ts){if(!t0)t0=ts;var p=Math.min((ts-t0)/1600,1);cnt.textContent=f(goal*(1-Math.pow(1-p,3)));if(p<1)requestAnimationFrame(step);});}
}})();
</script>

<?php /* HÉROS SPLIT DÉSACTIVÉ (désencombrement) : la VIDÉO est désormais le héros unique. Pour le réactiver, repasser if ( false ) → if ( true ). */ if ( false ) : ?>
<!-- Hero (désactivé — réactivable) -->
<section class="ag-hero" id="ag-main-content">
    <?php
    // HERO SPLIT LÉGER : 2 images (compressées) côte à côte, séparées par un trait doré droit.
    //  - GAUCHE = SÉCURITÉ  : option img_secu -> img_menace -> hero-secu.jpg (hacker, compressé).
    //  - DROITE = CRÉATION  : option img_creation -> photo bureau Naples (compressée).
    $ag_secu_bg = function_exists( 'ag_tester_opt' ) ? ( ag_tester_opt( 'img_secu' ) ?: ag_tester_opt( 'img_menace' ) ) : '';
    if ( ! $ag_secu_bg ) {
        foreach ( array( 'nantes-cyber.jpg', 'secu.jpg', 'hero-secu.jpg' ) as $f ) {
            if ( file_exists( get_stylesheet_directory() . '/assets/images/securite/' . $f ) ) {
                $ag_secu_bg = get_stylesheet_directory_uri() . '/assets/images/securite/' . $f; break;
            }
        }
    }
    $ag_crea_bg = function_exists( 'ag_tester_opt' ) ? ag_tester_opt( 'img_creation' ) : '';
    if ( ! $ag_crea_bg ) {
        foreach ( array( 'naples-1.jpg', 'fabrizio-nantes.jpg', '1_bureau_naples.jpg' ) as $f ) {
            $p = get_stylesheet_directory() . '/assets/images/team/' . $f;
            if ( file_exists( $p ) ) { $ag_crea_bg = get_stylesheet_directory_uri() . '/assets/images/team/' . $f; break; }
        }
    }
    // Voile ROUGE-NOIR sombre sur le hacker (gauche) ; voile sombre neutre à droite.
    $ag_secu_style = $ag_secu_bg
        ? "background-image:linear-gradient(180deg,rgba(20,4,6,.22),rgba(8,4,6,.40)),linear-gradient(90deg,rgba(120,12,16,.18),rgba(5,5,8,.10)),url('" . esc_url( $ag_secu_bg ) . "')"
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
    body.home .ag-hero{min-height:auto;padding-top:120px;padding-bottom:88px}
    @media(max-width:900px){body.home .ag-hero{padding-top:96px;padding-bottom:64px}}
    .ag-hero__naples,.ag-hero__boats,.ag-hero__particles,.ag-hero__vesuvius,.ag-hero__sunglow{display:none!important}
    /* SPLIT DIAGONAL LÉGER : clip-path (pas cher) + ligne SVG, 0 filtre, 0 animation */
    .ag-hero__split{position:absolute;inset:0;z-index:0;overflow:hidden;background:#05060c}
    .ag-hero__half{position:absolute;inset:0;background-size:cover;background-position:center}
    .ag-hero__half--secu{clip-path:polygon(0 0, 58% 0, 42% 100%, 0 100%);background-position:center}
    .ag-hero__half--crea{clip-path:polygon(58% 0, 100% 0, 100% 100%, 42% 100%);background-size:contain;background-repeat:no-repeat;background-position:center right}
    @media(max-width:760px){.ag-hero__half--crea{background-size:cover}}
    .ag-hero__line{position:absolute;inset:0;width:100%;height:100%;z-index:2;pointer-events:none}
    .ag-hero__half-tag{position:absolute;top:20px;left:22px;font-size:.72rem;font-weight:800;letter-spacing:2px;
        text-transform:uppercase;color:#fff;background:rgba(0,0,0,.5);
        border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 13px}
    .ag-hero__half-tag--r{left:auto;right:22px;color:#F3D27A;border-color:rgba(243,210,122,.4)}
    .ag-hero__video-veil{position:absolute;inset:0;z-index:1;background:radial-gradient(ellipse 78% 62% at 50% 44%,rgba(5,6,12,.62) 0%,rgba(5,6,12,.34) 58%,rgba(5,6,12,.06) 100%),linear-gradient(180deg,rgba(5,6,12,.30) 0%,rgba(5,6,12,.42) 45%,rgba(5,6,12,.86) 100%)}
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
            <span class="ag-badge-desk">Audit · Création · Maintenance de sites web — Naples &amp; Nantes</span>
            <span class="ag-badge-mob">Sites web &amp; sécurité · Naples &amp; Nantes</span>
            <span class="ag-heritage-dots" aria-hidden="true"><span></span><span></span><span></span></span>
        </div>

        <h1 class="ag-hero__title ag-hero__title--uni" data-noreveal>
            <span class="ag-title-desk">
                <span class="ag-duo">
                    <span class="ag-duo__col">
                        <span class="ag-uni ag-uni--secu">Sécurité</span>
                        <span class="ag-duo__txt">J'audite &amp; je protège votre site contre le piratage.</span>
                    </span>
                    <span class="ag-duo__sep" aria-hidden="true"></span>
                    <span class="ag-duo__col">
                        <span class="ag-uni ag-uni--crea">Création de site web IA&amp;SEO</span>
                        <span class="ag-duo__txt">Un site pro, rapide et bien référencé sur Google.</span>
                    </span>
                </span>
            </span>
            <span class="ag-title-mob"><span class="ag-uni--crea">Création</span> de site web<br>&amp; <span class="ag-uni--secu">sécurité</span></span>
        </h1>
        <style>
        /* Titre UNIQUE : "sécurise" en ROUGE, "crée" en BLEU, "Vous, vous respirez." en DORÉ.
           Police forcée Manrope. Compact pour laisser voir les boutons. */
        .ag-hero__title--uni{max-width:920px;margin:0 auto 10px;text-align:center;
            font-family:var(--font-sans),'Manrope',sans-serif;font-weight:800;
            font-size:clamp(1.55rem,4.2vw,2.9rem);line-height:1.14;letter-spacing:-.01em;color:#fff}
        .ag-uni{white-space:nowrap}
        .ag-uni--secu{color:#FF5A5A;text-shadow:0 2px 18px rgba(225,15,26,.55)}
        .ag-uni--crea{color:#5BA8FF;text-shadow:0 2px 18px rgba(40,110,220,.55)}
        .ag-hero__zen{display:inline-block;margin-top:4px;color:#F3D27A;
            text-shadow:0 2px 20px rgba(243,210,122,.4);font-weight:800;white-space:nowrap}
        @media(max-width:600px){.ag-uni,.ag-hero__zen{white-space:normal}}

        /* ── PC + TABLETTE (>600px) : titre en 2 colonnes —
              gauche = Sécurité (rouge) + court texte ; droite = Création IA&SEO (bleu) + court texte. ── */
        .ag-duo{display:flex;align-items:stretch;justify-content:center;gap:clamp(20px,4vw,52px);flex-wrap:wrap}
        .ag-duo__col{display:flex;flex-direction:column;align-items:center;gap:8px;flex:1 1 0;min-width:230px;max-width:430px;text-align:center}
        .ag-duo__col .ag-uni{white-space:normal;font-size:clamp(1.5rem,3vw,2.5rem);line-height:1.08;display:block}
        .ag-duo__txt{font-weight:600;font-size:clamp(.92rem,1.4vw,1.08rem);line-height:1.5;color:rgba(255,255,255,.95);text-shadow:0 2px 14px rgba(0,0,0,.75)}
        .ag-hero__title--uni{margin-bottom:22px}
        .ag-duo{margin-bottom:6px}
        .ag-duo__sep{flex:0 0 1px;align-self:stretch;background:linear-gradient(180deg,transparent,rgba(255,255,255,.28),transparent)}
        /* Tablette portrait : si ça passe à la ligne, le séparateur devient horizontal. */
        @media(max-width:780px) and (min-width:601px){
            .ag-duo__sep{flex-basis:auto;width:55%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.28),transparent)}
        }

        /* ── MOBILE UNIQUEMENT (≤600px) : titre simple + clair, paragraphe
              raccourci, badge Naples/Nantes abrégé. Tablette/PC inchangés. ── */
        .ag-title-mob,.ag-badge-mob,.ag-sub-mob{display:none}
        @media(max-width:600px){
            .ag-title-desk,.ag-badge-desk{display:none!important}
            .ag-sub-desk{display:none!important}
            .ag-title-mob{display:block}
            .ag-badge-mob{display:inline}
            .ag-sub-mob{display:block}
            .ag-hero__title--uni{font-size:clamp(1.7rem,7vw,2.2rem);line-height:1.18}
            .ag-title-mob .ag-uni--secu{color:#FF5A5A;text-shadow:0 2px 18px rgba(225,15,26,.55)}
            .ag-title-mob .ag-uni--crea{color:#5BA8FF;text-shadow:0 2px 18px rgba(40,110,220,.55)}
        }
        </style>

        <p class="ag-hero__sub ag-sub-desk">
            Deux métiers, un seul interlocuteur : je <strong>sécurise</strong> votre site (audit + protection) et je <strong>crée des sites pro référencés</strong> (SEO). Chaque jour, <strong>30 000 sites sont piratés</strong> — on commence par révéler vos failles. Avant les autres.
        </p>
        <p class="ag-hero__sub ag-sub-mob">
            Je <strong>crée des sites pro référencés</strong> (SEO) et je les <strong>sécurise</strong>. On commence par révéler vos failles.
        </p>

        <!-- Gauche (sous « Sécurité ») : champ URL -> test direct -> rapport léger.
             Droite (sous « Création ») : bouton discret. Tout se fond dans le décor. -->
        <div class="ag-hero__cta2">
            <div class="ag-hero__cta2col">
                <form class="ag-herotest" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="var i=this.site_url; if(i.value && !/^https?:\/\//i.test(i.value)) i.value='https://'+i.value;">
                    <input type="hidden" name="action" value="ag_tester_run">
                    <?php wp_nonce_field( 'ag_tester' ); ?>
                    <input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px">
                    <input type="hidden" name="result_page" value="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">
                    <input type="text" name="site_url" inputmode="url" placeholder="monsite.fr" required class="ag-herotest__in">
                    <button type="submit" class="ag-herotest__btn">🔍 Tester mon site</button>
                </form>
                <span class="ag-herotest__hint">Diagnostic gratuit & non-intrusif — résultat immédiat.</span>
            </div>
            <div class="ag-hero__cta2col ag-hero__cta2col--crea">
                <a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-cta-ghost ag-cta-ghost--crea">✨ Je veux un site qui me ressemble</a>
            </div>
        </div>
        <style>
        .ag-hero__cta2{display:flex;gap:clamp(20px,4vw,52px);justify-content:center;align-items:center;max-width:760px;margin:22px auto 0;flex-wrap:wrap}
        .ag-hero__cta2col{flex:1 1 0;min-width:230px;max-width:430px;display:flex;flex-direction:column;align-items:center;gap:6px}
        /* Mini-test URL fondu dans le décor */
        .ag-herotest{display:flex;width:100%;gap:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,90,90,.45);border-radius:999px;padding:6px 6px 6px 16px;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
        .ag-herotest__in{flex:1;min-width:0;background:transparent;border:0;outline:none;color:#fff;font-size:1rem}
        .ag-herotest__in::placeholder{color:rgba(255,255,255,.55)}
        .ag-herotest__btn{white-space:nowrap;border:0;border-radius:999px;padding:11px 20px;font-weight:800;cursor:pointer;background:rgba(255,90,90,.85);color:#fff;transition:.18s}
        .ag-herotest__btn:hover{background:#ff5a5a}
        .ag-herotest__hint{font-size:.78rem;color:rgba(255,255,255,.6);text-shadow:0 1px 3px rgba(0,0,0,.5)}
        /* Bouton création discret */
        .ag-cta-ghost{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;border-radius:999px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.06);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);color:#fff!important;font-weight:700;text-decoration:none!important;text-shadow:0 1px 3px rgba(0,0,0,.45);transition:.18s;text-align:center}
        .ag-cta-ghost--crea{border-color:rgba(91,168,255,.55)}
        .ag-cta-ghost--crea:hover{background:rgba(91,168,255,.18);border-color:#8ec3ff;transform:translateY(-2px)}
        @media(max-width:600px){.ag-cta-ghost{width:100%;justify-content:center}}
        </style>
        <style>
        /* Sous-texte resserre pour remonter le test + les boutons */
        body.home .ag-hero__sub{max-width:760px;margin:8px auto 0!important;font-size:.98rem;line-height:1.5}
        body.home .ag-hero__buttons{margin-top:14px!important}
        .ag-qt{max-width:620px;margin:16px 0 0;background:rgba(255,255,255,.05);border:1px solid rgba(243,210,122,.35);
            border-radius:16px;padding:14px 16px}
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
<?php endif; // fin héros split désactivé ?>

<!-- La VIDÉO (ag-headvid) est le héros épinglé. Le contenu remonte par-dessus au scroll. -->
<div class="ag-pinwrap" id="ag-main-content">

<!-- ⚡ LA MENACE EN DIRECT — juste après le hero : on capte la peur, puis
       on donne la solution dans la même section (globe à gauche, audit à droite) -->
<?php get_template_part('template-parts/menace-live'); ?>

<!-- "Choisissez votre parcours" — RETIRÉ de l'accueil (désencombrement). Réactivable en décommentant. -->
<?php /* get_template_part('template-parts/paths-hero'); */ ?>

<!-- Exemples d'audits anonymisés (preuve sociale sécurité) -->
<?php get_template_part('template-parts/audit-examples'); ?>

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

<!-- 2 FAÇONS de concevoir son site (sur-mesure / templates) — split, puis les packs juste dessous -->
<?php get_template_part('template-parts/templates-cta'); ?>

<!-- P1/ VENDRE — offres création + maintenance (4x sans frais, sécurisé) -->
<?php get_template_part('template-parts/home-offres'); ?>

<!-- ON RECRUTE — RETIRÉ de l'accueil (public différent). Vit sur sa page dédiée (menu « 🚀 Gagner »). Réactivable en décommentant. -->
<?php /* get_template_part('template-parts/home-ambassadeurs'); */ ?>

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
/* Bandeau « Installer l'app » affiché → on MASQUE la barre CTA (jamais les deux barres en même temps). */
body.ag-appbn-on .ag-stickycta{opacity:0 !important;visibility:hidden !important;pointer-events:none !important;transform:translateY(120%) !important}
/* Barre CTA visible : sur écrans étroits (≤1100px) elle atteindrait les boutons flottants du bas-gauche
   (On recrute / musique / retour-haut) → on les remonte AU-DESSUS d'elle. Au-delà, la CTA est centrée = aucun contact. */
@media(max-width:1100px){
	body.ag-stickycta-on .ag-recrute{bottom:90px}
	body.ag-stickycta-on .agm{bottom:138px}
	body.ag-stickycta-on .ag-totop{bottom:196px !important;left:18px !important}
}
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
		document.body.classList.toggle('ag-stickycta-on', el.classList.contains('is-on'));
	}
	window.addEventListener('scroll', onScroll, {passive:true});
	onScroll();
})();
</script>

</div><!-- /.ag-pinwrap -->
<style>
/* VIDÉO ÉPINGLÉE en header : reste fixe pendant que TOUT le contenu remonte par-dessus. */
body.home .ag-hero{position:relative;z-index:1;background:var(--color-bg)}
body.home .ag-pinwrap{position:relative;z-index:1;background:var(--color-bg)}
/* le footer doit aussi passer au-dessus du hero épinglé tout en bas */
body.home .ag-footer{position:relative;z-index:1}
/* ANTI-DÉBORDEMENT LATÉRAL : aucune animation/section ne doit créer de scroll
   horizontal. 'clip' coupe le débordement SANS créer de conteneur de défilement
   (donc n'empêche PAS position:sticky des sections, contrairement à 'hidden'). */
html{overflow-x:clip}
body.home{overflow-x:clip;max-width:100%}
body.home .ag-pinwrap,body.home .ag-pinwrap > section,body.home .ag-headvid{max-width:100vw}
/* DÉSENCOMBREMENT MOBILE (accueil) : on masque musique / bandeau "Installer l'app" /
   pastille "On recrute". On GARDE la flèche "remonter en haut" (.ag-totop, bas-droite). */
@media(max-width:768px){
	body.home .agm,
	body.home .ag-appbn,
	body.home .ag-recrute{display:none !important}
	/* flèche remonter en haut : bien visible en bas à droite */
	body.home .ag-totop{display:flex !important;bottom:20px !important;right:20px !important;left:auto !important;z-index:99997 !important}
}
/* ── EMPILEMENT AU SCROLL (PC + MOBILE) : chaque section s'ÉPINGLE en haut quand elle
   y arrive, et la suivante REMONTE par-dessus (la précédente reste fixe derrière).
   Sur mobile, la VIDÉO du héros reste ENTIÈRE et défile normalement ; l'empilement
   commence aux sections de contenu (sous la vidéo). Les sections trop hautes ou trop
   courtes NE s'épinglent PAS — géré en JS — pour ne jamais masquer de contenu. */
body.home .ag-pinwrap > section{position:sticky;top:0}
</style>
<script>
(function(){
	// Empilement actif sur PC ET mobile (la vidéo héros reste entière au-dessus).
	var secs=[].slice.call(document.querySelectorAll('body.home .ag-pinwrap > section'));
	if(!secs.length) return;
	function transparent(c){return !c||c==='transparent'||c==='rgba(0, 0, 0, 0)';}
	function apply(){
		var vh=window.innerHeight;
		secs.forEach(function(s){
			// Fond opaque de secours : on ne doit jamais voir au travers d'une section épinglée.
			var cs=getComputedStyle(s);
			if(transparent(cs.backgroundColor)&&cs.backgroundImage==='none'){
				s.style.backgroundColor='var(--color-bg,#0a0a0f)';
			}
			var h=s.offsetHeight;
			if(h<vh*0.5){
				// Trop courte (barre de réassurance) : pas d'épinglage (sinon fine barre flottante).
				s.style.position='relative'; s.style.top='auto';
			} else if(h<=vh+4){
				// Tient dans l'écran : s'épingle en haut quand elle y arrive.
				s.style.position='sticky'; s.style.top='0';
			} else {
				// Plus haute qu'un écran : on la laisse défiler entièrement, puis elle
				// s'épingle quand son BAS atteint le bas de l'écran (top négatif), donc
				// tout son contenu a été vu — et la suivante remonte par-dessus.
				s.style.position='sticky'; s.style.top=(vh-h)+'px';
			}
		});
	}
	apply();
	var t;window.addEventListener('resize',function(){clearTimeout(t);t=setTimeout(apply,150);},{passive:true});
	window.addEventListener('load',apply);
})();
</script>

<!-- ══ EFFETS D'ENTRÉE PAR SECTION (éclair / onde de choc / tornade / glitch) ══
     AJOUT VISUEL UNIQUEMENT — aucun texte/contenu n'est modifié. Amélioration
     progressive : l'état "caché" n'est posé QUE par le JS ; sans JS (ou si
     "animations réduites"), tout reste visible. Filet de sécurité : au pire, tout
     est ré-affiché après 4 s. 100 % transform/opacité (carte graphique) → fluide. -->
<style>
/* état armé (posé par JS) : la section est prête à apparaître */
body.home .ag-pinwrap > section.agfx-armed{opacity:0}
body.home .ag-pinwrap > section.agfx-in{opacity:1}
/* overlays décoratifs (injectés par JS) — n'affectent JAMAIS la mise en page */
.agfx-flash{position:absolute;inset:0;background:#fff;opacity:0;pointer-events:none;z-index:60;mix-blend-mode:screen}
.agfx-flash.go{animation:agfxFlash .5s ease-out both}
@keyframes agfxFlash{0%{opacity:0}8%{opacity:.8}16%{opacity:.1}24%{opacity:.55}100%{opacity:0}}
.agfx-bolt{position:absolute;top:0;left:50%;height:56%;transform:translateX(-50%);z-index:59;opacity:0;pointer-events:none}
.agfx-bolt path{stroke:#f4d98b;stroke-width:3;fill:none;filter:drop-shadow(0 0 8px #e8c66a);stroke-dasharray:600;stroke-dashoffset:600}
.agfx-bolt.go{opacity:1;animation:agfxBoltFade .6s ease-out both}
.agfx-bolt.go path{animation:agfxDraw .35s ease-out both}
@keyframes agfxDraw{to{stroke-dashoffset:0}}
@keyframes agfxBoltFade{0%,60%{opacity:1}100%{opacity:0}}
.agfx-ring{position:absolute;top:50%;left:50%;width:38px;height:38px;border:2px solid #e8c66a;border-radius:50%;
	transform:translate(-50%,-50%) scale(0);opacity:0;z-index:1;pointer-events:none}
.agfx-ring.go{animation:agfxRing .7s ease-out both}
@keyframes agfxRing{0%{opacity:.6;transform:translate(-50%,-50%) scale(0)}100%{opacity:0;transform:translate(-50%,-50%) scale(28)}}
/* les 4 effets — uniquement transform/opacité, retour à transform:none à la fin */
body.home .agfx--bolt.agfx-in{animation:agfxShake .55s cubic-bezier(.36,.07,.19,.97) both}
@keyframes agfxShake{0%{opacity:0;transform:translateY(16px)}20%{opacity:1}32%{transform:translateX(-6px)}48%{transform:translateX(6px)}64%{transform:translateX(-4px)}80%{transform:translateX(2px)}100%{transform:none;opacity:1}}
body.home .agfx--shock.agfx-in{animation:agfxPop .55s cubic-bezier(.16,1,.3,1) both}
@keyframes agfxPop{0%{opacity:0;transform:scale(.92)}60%{opacity:1;transform:scale(1)}100%{transform:none;opacity:1}}
body.home .agfx--twist.agfx-in{animation:agfxTwist .65s cubic-bezier(.16,1,.3,1) both}
/* "déploiement" vertical (remplace la tornade qui débordait horizontalement : une
   rotation sur une section pleine largeur élargit sa boîte → scroll latéral). scaleY = 0 débordement horizontal. */
@keyframes agfxTwist{0%{opacity:0;transform:scaleY(.62)}70%{opacity:1;transform:scaleY(1)}100%{transform:none;opacity:1}}
body.home .agfx--glitch.agfx-in{animation:agfxGlitch .5s steps(2) both}
@keyframes agfxGlitch{0%{opacity:0;transform:translateX(-7px);text-shadow:3px 0 #ff5c5c,-3px 0 #5ba8ff}
	45%{opacity:1;transform:translateX(6px);text-shadow:-3px 0 #ff5c5c,3px 0 #5ba8ff}
	72%{transform:translateX(-2px);text-shadow:2px 0 #ff5c5c,-2px 0 #5ba8ff}
	100%{transform:none;opacity:1;text-shadow:none}}
@media(prefers-reduced-motion:reduce){
	body.home .ag-pinwrap > section.agfx-armed{opacity:1 !important}
	.agfx-flash,.agfx-bolt,.agfx-ring{display:none !important}
}
</style>
<script>
(function(){
	if(!('IntersectionObserver' in window)) return;
	if(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	var secs=[].slice.call(document.querySelectorAll('body.home .ag-pinwrap > section'));
	if(!secs.length) return;
	var TYPES=['bolt','shock','twist','glitch'];

	function replay(sel){ if(!sel) return; sel.classList.remove('go'); void sel.offsetWidth; sel.classList.add('go'); }
	function reveal(s){
		if(s.classList.contains('agfx-in')) return;
		s.classList.remove('agfx-armed'); s.classList.add('agfx-in');
		var fx=s.dataset.agfx;
		if(fx==='bolt'){ replay(s.querySelector('.agfx-flash')); replay(s.querySelector('.agfx-bolt')); }
		else if(fx==='shock'){ replay(s.querySelector('.agfx-ring')); }
	}
	// FILET DE SÉCURITÉ armé EN PREMIER : quoi qu'il arrive, rien ne reste caché.
	setTimeout(function(){ secs.forEach(function(s){ if(s.classList.contains('agfx-armed')) reveal(s); }); }, 4000);

	secs.forEach(function(s,i){
		var fx=TYPES[i % TYPES.length];
		s.dataset.agfx=fx;
		s.classList.add('agfx','agfx--'+fx,'agfx-armed');
		if(getComputedStyle(s).position==='static'){ s.style.position='relative'; } // ancre les overlays
		if(fx==='bolt'){
			var fl=document.createElement('div'); fl.className='agfx-flash'; s.appendChild(fl);
			var b=document.createElementNS('http://www.w3.org/2000/svg','svg');
			b.setAttribute('class','agfx-bolt'); b.setAttribute('viewBox','0 0 100 300'); b.setAttribute('preserveAspectRatio','none');
			var p=document.createElementNS('http://www.w3.org/2000/svg','path'); p.setAttribute('d','M55 0 L40 120 L60 120 L35 300');
			b.appendChild(p); s.appendChild(b);
		} else if(fx==='shock'){
			var r=document.createElement('div'); r.className='agfx-ring'; s.appendChild(r);
		}
	});

	var io=new IntersectionObserver(function(entries){
		entries.forEach(function(e){
			if(e.isIntersecting){ reveal(e.target); }
			else { e.target.classList.remove('agfx-in'); e.target.classList.add('agfx-armed'); } // se rejoue au retour
		});
	},{threshold:0.12,rootMargin:'0px 0px -8% 0px'});
	secs.forEach(function(s){ io.observe(s); });
})();
</script>

<?php get_footer(); ?>
