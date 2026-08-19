<?php
/**
 * Template Name: Accueil cinématique
 *
 * Accueil « scroll narratif » : hero égérie (vidéo + approche de la main), marquee
 * piloté par la vitesse de scroll, tableau allégorique, chapitres, dissolution en
 * pixels dorés (canvas), offres, atelier IA filtrable, révélation du lion.
 * Autonome : CSS et JS inline, aucune dépendance de plugin.
 * Libs : assets/js/vendor/{gsap,ScrollTrigger,lenis}.min.js
 *
 * @package Alliance_Groupe
 */

get_header();
$dir = get_stylesheet_directory_uri();
?>
<style>
  :root{
    --gold:#d4b45c; --gold-hi:#f4d06f; --ink:#05050a; --panel:#0b0b12;
    --text:#eef1f6; --muted:#9aa3b4;
    --serif:"Playfair Display",Georgia,"Times New Roman",serif;
    --sans:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:auto}
  body{background:var(--ink);color:var(--text);font-family:var(--sans);-webkit-font-smoothing:antialiased;overflow-x:hidden}
  img{display:block;max-width:100%}
  a{color:inherit}
  .wrap{max-width:1240px;margin:0 auto;padding:0 28px}
  .eyebrow{font-size:.72rem;letter-spacing:.34em;text-transform:uppercase;color:var(--gold);font-weight:700}
  .stitle{font-family:var(--serif);font-weight:500;font-size:clamp(1.9rem,5vw,3.4rem);line-height:1.06;letter-spacing:-.01em}
  .stitle em{font-style:italic;color:var(--gold-hi)}
  .lead{color:var(--muted);font-size:clamp(1rem,2.2vw,1.12rem);line-height:1.65;max-width:56ch}
  .btn{display:inline-block;background:linear-gradient(120deg,var(--gold),var(--gold-hi));color:#1a1206;font-weight:800;
       text-decoration:none;border-radius:999px;padding:15px 34px;font-size:.98rem;
       box-shadow:0 18px 44px -18px rgba(212,180,92,.75);transition:transform .25s,box-shadow .25s}
  .btn:hover{transform:translateY(-2px);box-shadow:0 24px 54px -20px rgba(212,180,92,.95)}
  .btn--ghost{background:transparent;color:var(--text);border:1px solid rgba(255,255,255,.22);box-shadow:none;font-weight:600}
  .btn--ghost:hover{border-color:var(--gold)}

  /* ---------- EN-TÊTE ---------- */
  .hd{position:fixed;top:0;left:0;right:0;z-index:40;padding:20px 0;transition:padding .4s ease,background .4s ease,backdrop-filter .4s}
  .hd.is-stuck{padding:10px 0;background:rgba(5,5,10,.72);backdrop-filter:blur(12px);border-bottom:1px solid rgba(212,180,92,.18)}
  .hd__in{display:flex;align-items:center;justify-content:space-between;gap:20px}
  .hd__brand{display:flex;align-items:center;gap:12px;text-decoration:none}
  .hd__brand img{width:44px;height:44px;border-radius:50%;transition:width .4s,height .4s}
  .hd.is-stuck .hd__brand img{width:34px;height:34px}
  .hd__name{font-family:var(--serif);font-size:1.05rem;letter-spacing:.14em;text-transform:uppercase;color:var(--text)}
  .hd__nav{display:flex;gap:26px;align-items:center;font-size:.86rem;letter-spacing:.06em}
  .hd__nav a{text-decoration:none;color:#cdd4e2;transition:color .25s}
  .hd__nav a:hover{color:var(--gold)}
  @media(max-width:900px){.hd__nav a:not(.btn){display:none}.hd__name{display:none}}

  /* ---------- HERO ---------- */
  .hero{position:relative;height:100svh;overflow:hidden;display:flex;align-items:flex-end}
  .hero__bg{position:absolute;inset:-6% 0 0;z-index:0}
  .hero__bg img{width:100%;height:112%;object-fit:cover;object-position:center 45%}
  .hero__veil{position:absolute;inset:0;z-index:1;
    background:linear-gradient(180deg,rgba(5,5,10,.78),rgba(5,5,10,.12) 34%,rgba(5,5,10,.45) 68%,rgba(5,5,10,.97)),
               radial-gradient(120% 80% at 22% 60%,transparent 38%,rgba(5,5,10,.62))}
  .hero__eg{position:absolute;z-index:2;right:0;bottom:0;height:100svh;width:min(46vw,640px);pointer-events:none;
    transform-origin:62% 74%;
    -webkit-mask-image:linear-gradient(90deg,transparent 0%,#000 30%);mask-image:linear-gradient(90deg,transparent 0%,#000 30%)}
  .hero__eg video,.hero__eg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 38%}
  .hero__eg video{z-index:1}
  .hero__in{position:relative;z-index:3;width:100%;padding-bottom:clamp(52px,10vh,120px)}
  .hero__t{font-family:var(--serif);font-weight:500;line-height:.97;font-size:clamp(2.9rem,8.4vw,7rem);letter-spacing:-.022em;margin:.18em 0 0}
  .hero__t em{font-style:italic;color:var(--gold-hi)}
  .hero__t .w{display:inline-block;overflow:hidden;vertical-align:bottom}
  .hero__t .w i{display:inline-block;font-style:inherit}
  .hero__sub{margin-top:20px;max-width:44ch;color:#cfd6e4;font-size:clamp(1rem,2.2vw,1.14rem);line-height:1.6}
  .hero__cta{margin-top:28px;display:flex;gap:14px;flex-wrap:wrap}
  .hero__scroll{position:absolute;left:50%;bottom:16px;transform:translateX(-50%);z-index:4;font-size:.68rem;
    letter-spacing:.32em;color:rgba(255,255,255,.5);animation:bob 2.2s ease-in-out infinite}
  @keyframes bob{0%,100%{transform:translate(-50%,0)}50%{transform:translate(-50%,7px)}}
  .lionmark{position:absolute;z-index:2;left:50%;top:42%;transform:translate(-50%,-50%);width:min(62vw,600px);opacity:.10;mix-blend-mode:screen}

  /* ---------- MARQUEE ---------- */
  .mq{position:relative;border-top:1px solid rgba(212,180,92,.22);border-bottom:1px solid rgba(212,180,92,.22);
      overflow:hidden;white-space:nowrap;padding:22px 0;background:#07070d}
  .mq__bg{position:absolute;inset:0;opacity:.5}
  .mq__bg img{width:100%;height:100%;object-fit:cover}
  .mq__in{position:relative;display:inline-flex;gap:46px;will-change:transform}
  .mq__in span{font-family:var(--serif);font-size:clamp(1.1rem,2.4vw,1.7rem);color:#f0ecdf}
  .mq__in b{color:var(--gold)}

  /* ---------- TABLEAU ---------- */
  .tab{position:relative;height:230svh}
  .tab__stick{position:sticky;top:0;height:100svh;overflow:hidden;display:grid;place-items:center}
  .tab__img{position:absolute;inset:0}
  .tab__img img{width:100%;height:100%;object-fit:cover;transform:scale(1.12)}
  .tab__veil{position:absolute;inset:0;background:linear-gradient(180deg,rgba(5,5,10,.62),rgba(5,5,10,.25) 40%,rgba(5,5,10,.9))}
  .tab__cap{position:relative;z-index:2;text-align:center;padding:0 26px;max-width:900px}
  .tab__cap h2{font-family:var(--serif);font-weight:500;font-size:clamp(2rem,6.4vw,4.6rem);line-height:1.02}
  .tab__cap h2 em{font-style:italic;color:var(--gold-hi)}
  .tab__cap p{margin:20px auto 0;color:#d7dae2;max-width:52ch;line-height:1.7}

  /* ---------- CHAPITRES ---------- */
  .chs{padding:clamp(80px,13vh,160px) 0}
  .ch{display:grid;grid-template-columns:1fr 1fr;gap:clamp(28px,6vw,84px);align-items:center;margin-bottom:clamp(70px,12vh,150px)}
  .ch:nth-child(even) .ch__txt{order:2}
  .ch__media{position:relative;overflow:hidden;aspect-ratio:4/5;border:1px solid rgba(212,180,92,.2)}
  .ch__media img{width:100%;height:100%;object-fit:cover;transform:scale(1.16)}
  .ch__n{font-family:var(--serif);font-size:clamp(2.4rem,6vw,4.4rem);color:var(--gold);opacity:.8;line-height:1}
  .ch__t{font-family:var(--serif);font-weight:500;font-size:clamp(1.8rem,4.4vw,3.1rem);line-height:1.06;margin:.2em 0 .5em}
  .ch__t em{font-style:italic;color:var(--gold-hi)}
  .ch__p{color:var(--muted);font-size:1.05rem;line-height:1.75;max-width:46ch}
  .ch__meta{margin-top:24px;display:flex;gap:24px;flex-wrap:wrap;font-size:.76rem;letter-spacing:.18em;text-transform:uppercase;color:var(--gold)}

  /* ---------- DISSOLUTION ---------- */
  .ds{position:relative;height:260svh}
  .ds__stick{position:sticky;top:0;height:100svh;overflow:hidden;display:grid;place-items:center}
  #cv{width:100%;height:100%;display:block}
  .ds__hand{position:absolute;z-index:2;width:min(52vw,620px);mix-blend-mode:screen;opacity:0;pointer-events:none}
  .ds__veil{position:absolute;inset:0;z-index:3;background:linear-gradient(180deg,rgba(5,5,10,.5),transparent 32%,rgba(5,5,10,.92))}
  .ds__cap{position:absolute;z-index:4;left:0;right:0;bottom:11vh;text-align:center;padding:0 26px}
  .ds__cap h2{font-family:var(--serif);font-weight:500;font-size:clamp(1.9rem,5.4vw,4rem);line-height:1.05}
  .ds__cap h2 em{font-style:italic;color:var(--gold-hi)}
  .ds__cap p{margin:16px auto 0;max-width:52ch;color:var(--muted);line-height:1.65}

  /* ---------- OFFRES ---------- */
  .of{position:relative;padding:clamp(80px,13vh,160px) 0;overflow:hidden}
  .of__bg{position:absolute;inset:0;opacity:.34}
  .of__bg img{width:100%;height:100%;object-fit:cover}
  .of__in{position:relative;z-index:2;text-align:center}
  .of__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:26px;margin:46px 0 26px;text-align:left}
  .pack{position:relative;background:#0c0c14;border:1px solid rgba(255,255,255,.09);border-radius:18px;overflow:hidden;
        transform-style:preserve-3d;transition:border-color .35s,box-shadow .35s}
  .pack:hover{border-color:rgba(212,180,92,.6);box-shadow:0 40px 90px -50px rgba(212,180,92,.8)}
  .pack.star{border-color:rgba(212,180,92,.55);box-shadow:0 34px 80px -46px rgba(212,180,92,.7)}
  .pack img{width:100%;aspect-ratio:4/3;object-fit:cover}
  .pack__body{padding:22px 24px 26px}
  .pack__cta{display:block;text-align:center;margin-top:6px}
  .of__note{position:relative;z-index:2;color:var(--muted);font-size:.95rem}
  .of__note a{color:var(--gold-hi)}

  /* ---------- ATELIER ---------- */
  .at{padding:clamp(80px,13vh,160px) 0;background:linear-gradient(180deg,#05050a,#0a0a12)}
  .at__head{text-align:center;max-width:760px;margin:0 auto 34px}
  .at__filters{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:34px}
  .fbtn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.14);color:#cdd4e2;border-radius:999px;
        padding:9px 20px;font-size:.85rem;cursor:pointer;transition:.25s;font-family:inherit}
  .fbtn:hover{border-color:var(--gold)}
  .fbtn.is-on{background:linear-gradient(120deg,var(--gold),var(--gold-hi));color:#1a1206;font-weight:700;border-color:transparent}
  .at__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px}
  .card{position:relative;background:#0c0c14;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;
        text-decoration:none;display:block;transition:border-color .35s,transform .35s}
  .card:hover{border-color:rgba(212,180,92,.55);transform:translateY(-4px)}
  .card__media{position:relative;aspect-ratio:16/9;overflow:hidden}
  .card__media img{width:100%;height:100%;object-fit:cover;transition:transform .7s cubic-bezier(.2,.7,.2,1)}
  .card:hover .card__media img{transform:scale(1.07)}
  .card__badge{position:absolute;top:12px;left:12px;z-index:2;background:rgba(5,5,10,.72);border:1px solid rgba(212,180,92,.5);
    color:var(--gold-hi);font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;padding:5px 12px;border-radius:999px}
  .card__body{padding:16px 18px 20px}
  .card__t{font-size:1.02rem;font-weight:700;margin-bottom:6px}
  .card__d{color:var(--muted);font-size:.88rem;line-height:1.5;min-height:2.6em}
  .card__go{margin-top:12px;font-size:.82rem;color:var(--gold);letter-spacing:.06em}

  /* ---------- LION ---------- */
  .lion{position:relative;height:210svh}
  .lion__stick{position:sticky;top:0;height:100svh;display:grid;place-items:center;overflow:hidden;
    background:radial-gradient(60% 60% at 50% 45%,#12121c,#05050a)}
  .lion__img{width:min(40vw,380px);opacity:0;border-radius:50%;
    box-shadow:0 0 0 1px rgba(212,180,92,.5),0 70px 150px -50px rgba(212,180,92,.55)}
  .lion__w{position:absolute;bottom:15vh;font-size:clamp(.68rem,1.5vw,.92rem);letter-spacing:.7em;color:var(--gold);text-transform:uppercase}

  /* ---------- CTA + PIED ---------- */
  .cta{padding:clamp(80px,15vh,170px) 0;text-align:center}
  .cta p{color:var(--muted);margin:18px auto 32px;max-width:48ch;line-height:1.7}
  .ft{border-top:1px solid rgba(255,255,255,.08);padding:38px 0 56px;color:#5f6675;font-size:.82rem;
      display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .ft a{color:#8a90a0;text-decoration:none}
  .ft a:hover{color:var(--gold)}

  @media(max-width:820px){
    .ch{grid-template-columns:1fr}
    .ch:nth-child(even) .ch__txt{order:0}
    .hero__eg{right:50%;transform:translateX(50%);height:64svh;opacity:.95}
  }
  @media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>


<header class="hd" id="hd">
  <div class="wrap hd__in">
    <a class="hd__brand" href="#top"><img src="<?php echo esc_url( $dir . '/assets/images/ag-logo.png' ); ?>" alt="Alliance Groupe"><span class="hd__name">Alliance Groupe</span></a>
    <nav class="hd__nav">
      <a href="#offres">Offres</a><a href="#atelier">Atelier IA</a><a href="#chapitres">Notre façon</a>
      <a class="btn" href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">Audit gratuit</a>
    </nav>
  </div>
</header>

<section class="hero" id="top">
  <div class="hero__bg" data-parallax><img src="<?php echo esc_url( $dir . '/assets/images/cities/baie_naples_nuit.jpg' ); ?>" alt="La baie de Naples" fetchpriority="high"></div>
  <img class="lionmark" src="<?php echo esc_url( $dir . '/assets/images/ag-logo.png' ); ?>" alt="">
  <div class="hero__veil"></div>
  <div class="hero__eg" data-eg>
    <img src="<?php echo esc_url( $dir . '/assets/videos/hero-egerie-poster.jpg' ); ?>" alt="">
    <video src="<?php echo esc_url( $dir . '/assets/videos/hero-egerie-court.mp4' ); ?>" autoplay muted loop playsinline poster="<?php echo esc_url( $dir . '/assets/videos/hero-egerie-poster.jpg' ); ?>"></video>
  </div>
  <div class="hero__in wrap">
    <span class="eyebrow" data-hl>Agence web · sécurité · IA</span>
    <h1 class="hero__t" data-split>De Naples <em>à Nantes</em></h1>
    <p class="hero__sub" data-hl>Un artisan du web, pas une usine. Du conseil à la livraison, une seule personne au bout du fil — et un site qui vous ressemble.</p>
    <div class="hero__cta" data-hl>
      <a class="btn" href="#offres">Voir les offres</a>
      <a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">Auditer mon site</a>
    </div>
  </div>
  <div class="hero__scroll">DÉFILEZ</div>
</section>

<div class="mq">
  <div class="mq__bg"><img src="<?php echo esc_url( $dir . '/assets/images/cinematique/marbre-noir-or.jpg' ); ?>" alt=""></div>
  <div class="mq__in" id="mq">
    <span>Sites sécurisés <b>·</b></span><span>Livré en 5 jours <b>·</b></span><span>SEO Google <b>·</b></span>
    <span>De Naples à Nantes <b>·</b></span><span>Un seul interlocuteur <b>·</b></span><span>Audit gratuit <b>·</b></span>
    <span>Propulsé par l'IA <b>·</b></span><span>Design sur-mesure <b>·</b></span>
  </div>
</div>

<section class="tab">
  <div class="tab__stick">
    <div class="tab__img" data-tabimg><img src="<?php echo esc_url( $dir . '/assets/images/cinematique/allegorie-naples.jpg' ); ?>" alt="Allégorie de l'alliance devant la baie de Naples"></div>
    <div class="tab__veil"></div>
    <div class="tab__cap">
      <span class="eyebrow" data-rv>Depuis 2019</span>
      <h2 data-rv>Une alliance, <em>pas un prestataire</em></h2>
      <p data-rv>On ne vend pas des pages : on s'engage à côté de vous, du premier échange jusqu'au jour où le site rapporte. Naples pour l'atelier, Nantes pour le terrain.</p>
    </div>
  </div>
</section>

<section class="chs wrap" id="chapitres">
  <article class="ch">
    <div class="ch__txt">
      <div class="ch__n" data-rv>01</div>
      <h3 class="ch__t" data-rv>Un artisan, <em>pas une usine</em></h3>
      <p class="ch__p" data-rv>De Naples à Nantes, un seul interlocuteur : celui qui conçoit, celui qui code, celui qui livre. Vous ne parlez jamais à un service.</p>
      <div class="ch__meta" data-rv><span>Conseil</span><span>Design</span><span>Développement</span></div>
    </div>
    <div class="ch__media" data-media><img src="<?php echo esc_url( $dir . '/assets/images/team/1_bureau_naples.jpg' ); ?>" alt="Le bureau de Naples" loading="lazy"></div>
  </article>
  <article class="ch">
    <div class="ch__txt">
      <div class="ch__n" data-rv>02</div>
      <h3 class="ch__t" data-rv>La sécurité, <em>incluse</em></h3>
      <p class="ch__p" data-rv>Chaque site part d'un audit gratuit et arrive sécurisé — rare chez les agences classiques, systématique ici.</p>
      <div class="ch__meta" data-rv><span>Audit offert</span><span>Sauvegardes</span><span>Surveillance</span></div>
    </div>
    <div class="ch__media" data-media><img src="<?php echo esc_url( $dir . '/assets/images/team/reunion-naples.jpg' ); ?>" alt="Réunion au bureau de Naples" loading="lazy"></div>
  </article>
  <article class="ch">
    <div class="ch__txt">
      <div class="ch__n" data-rv>03</div>
      <h3 class="ch__t" data-rv>Propulsé <em>par l'IA</em></h3>
      <p class="ch__p" data-rv>Devis en trente secondes, maquettes régénérées, contenus rédigés : la machine fait le gros du travail, vous gardez le style et le contrôle.</p>
      <div class="ch__meta" data-rv><span>Devis instantané</span><span>Contenus</span><span>Images</span></div>
    </div>
    <div class="ch__media" data-media><img src="<?php echo esc_url( $dir . '/assets/images/team/naples-2.jpg' ); ?>" alt="Poste de travail avec vue sur la baie" loading="lazy"></div>
  </article>
</section>

<section class="ds">
  <div class="ds__stick">
    <canvas id="cv"></canvas>
    <img class="ds__hand" id="hand" src="<?php echo esc_url( $dir . '/assets/images/cinematique/main-poussiere-or.jpg' ); ?>" alt="">
    <div class="ds__veil"></div>
    <div class="ds__cap wrap">
      <h2>Ce qui compte ne se voit pas <em>tout de suite</em></h2>
      <p>Un site n'est pas une image. C'est une mécanique — vitesse, sécurité, référencement — qui se révèle quand on regarde de près.</p>
    </div>
  </div>
</section>

<section class="of" id="offres">
  <div class="of__bg"><img src="<?php echo esc_url( $dir . '/assets/images/cinematique/marbre-noir-or.jpg' ); ?>" alt="" loading="lazy"></div>
  <div class="wrap of__in">
    <span class="eyebrow" data-rv>Nos formules</span>
    <h2 class="stitle" data-rv style="margin-top:10px">Des offres claires, <em>à prix fixe</em></h2>
    <p class="lead" data-rv style="margin:16px auto 0">Pas de devis interminable. Vous choisissez, on livre — vite.</p>
    <div class="of__grid">
      <article class="pack" data-pack>
        <img src="<?php echo esc_url( $dir . '/assets/images/offres/offre-essentiel.jpg' ); ?>" alt="Pack Essentiel — 490 €" loading="lazy">
        <div class="pack__body"><a class="btn pack__cta" href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">Choisir Essentiel — 490 €</a></div>
      </article>
      <article class="pack star" data-pack>
        <img src="<?php echo esc_url( $dir . '/assets/images/offres/offre-pro.jpg' ); ?>" alt="Pack Pro — 890 €" loading="lazy">
        <div class="pack__body"><a class="btn pack__cta" href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">Choisir Pro — 890 €</a></div>
      </article>
      <article class="pack" data-pack>
        <img src="<?php echo esc_url( $dir . '/assets/images/offres/offre-boutique.jpg' ); ?>" alt="Pack Boutique — 1 490 €" loading="lazy">
        <div class="pack__body"><a class="btn pack__cta" href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">Choisir Boutique — 1 490 €</a></div>
      </article>
    </div>
    <p class="of__note" data-rv>+ Maintenance &amp; hébergement à partir de <strong>29 €/mois</strong> — <a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">voir les formules</a>.</p>
  </div>
</section>

<section class="at" id="atelier">
  <div class="wrap">
    <div class="at__head">
      <span class="eyebrow" data-rv>Alliance Groupe · web · sécurité · IA</span>
      <h2 class="stitle" data-rv style="margin:12px 0 14px">Que créons-nous <em>aujourd'hui&nbsp;?</em></h2>
      <p class="lead" data-rv style="margin:0 auto">Votre atelier propulsé par l'IA : chiffrez un projet, refaites votre site, sécurisez-le, créez du contenu.</p>
    </div>
    <div class="at__filters" data-rv>
      <button class="fbtn is-on" data-f="tous">Tous</button>
      <button class="fbtn" data-f="ia">IA</button>
      <button class="fbtn" data-f="creer">Créer</button>
      <button class="fbtn" data-f="securite">Sécurité</button>
    </div>
    <div class="at__grid" id="grid">
      <a class="card" data-cat="ia" href="<?php echo esc_url( home_url( '/devis-instant' ) ); ?>">
        <div class="card__media"><span class="card__badge">IA</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/devis.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Devis instantané</div><div class="card__d">Décrivez votre projet, l'IA le chiffre en 30 secondes.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="ia" href="<?php echo esc_url( home_url( '/refais-mon-site' ) ); ?>">
        <div class="card__media"><span class="card__badge">IA</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/refais.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Refais mon site</div><div class="card__d">Collez votre URL : l'IA génère une maquette modernisée.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="ia" href="<?php echo esc_url( home_url( '/fait-par-lia' ) ); ?>">
        <div class="card__media"><span class="card__badge">IA</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/ia.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Fait par l'IA</div><div class="card__d">Le journal en direct de ce que l'IA fait pour vous.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="creer" href="<?php echo esc_url( home_url( '/studio' ) ); ?>">
        <div class="card__media"><span class="card__badge">Gratuit</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/studio.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Studio créatif</div><div class="card__d">Créez des vidéos et visuels prêts à publier.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="creer" href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">
        <div class="card__media"><span class="card__badge">Dès 490 €</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/sites.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Création de sites</div><div class="card__d">Votre site pro, sécurisé, livré en quelques jours.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="securite" href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">
        <div class="card__media"><span class="card__badge">Gratuit</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/securite.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Audit de sécurité</div><div class="card__d">Testez votre site : note /100 et failles détectées.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="creer" href="<?php echo esc_url( home_url( '/composants' ) ); ?>">
        <div class="card__media"><span class="card__badge">Gratuit</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/composants.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Composants web</div><div class="card__d">Boutons et effets à copier ou télécharger.</div><div class="card__go">Ouvrir →</div></div>
      </a>
      <a class="card" data-cat="creer" href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>">
        <div class="card__media"><span class="card__badge">Gratuit</span><img src="<?php echo esc_url( $dir . '/assets/images/atelier/templates.webp' ); ?>" alt="" loading="lazy"></div>
        <div class="card__body"><div class="card__t">Templates WordPress</div><div class="card__d">Six thèmes métier gratuits, prêts à installer.</div><div class="card__go">Ouvrir →</div></div>
      </a>
    </div>
  </div>
</section>

<section class="lion">
  <div class="lion__stick">
    <img class="lion__img" id="lionImg" src="<?php echo esc_url( $dir . '/assets/images/cinematique/lion-or.jpg' ); ?>" alt="Le lion d'Alliance Groupe">
    <div class="lion__w" data-rv>Alliance Groupe</div>
  </div>
</section>

<section class="cta wrap">
  <h2 class="stitle" data-rv>Parlons de <em>votre site</em></h2>
  <p data-rv>Audit gratuit, devis en trente secondes, livraison en cinq jours. On commence par un échange, sans engagement.</p>
  <a class="btn" data-rv href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">Demander mon audit</a>
</section>

<footer class="wrap ft">
  <span>© Alliance Groupe — Naples · Nantes · Marrakech</span>
  <span><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales</a> · <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact</a></span>
</footer>

<script src="<?php echo esc_url( $dir . '/assets/js/vendor/gsap.min.js' ); ?>"></script>
<script src="<?php echo esc_url( $dir . '/assets/js/vendor/ScrollTrigger.min.js' ); ?>"></script>
<script src="<?php echo esc_url( $dir . '/assets/js/vendor/lenis.min.js' ); ?>"></script>

<script>
(function(){
  var G = window.gsap, ST = window.ScrollTrigger;
  var ok = !!(G && ST); if (ok) G.registerPlugin(ST);

  /* défilement amorti */
  var L = window.Lenis || (window.lenis && window.lenis.default);
  if (L) {
    var lenis = new L({ duration: 1.15, smoothWheel: true });
    (function raf(t){ lenis.raf(t); requestAnimationFrame(raf); })();
    if (ok) lenis.on("scroll", ST.update);
  }

  /* en-tête compact */
  var hd = document.getElementById("hd");
  addEventListener("scroll", function(){ hd.classList.toggle("is-stuck", scrollY > 60); }, { passive:true });

  if (ok) {
    /* titre mot à mot */
    var t = document.querySelector("[data-split]");
    if (t) {
      t.innerHTML = t.innerHTML.split(" ").map(function(w){ return '<span class="w"><i>'+w+'</i></span>'; }).join(" ");
      G.from(t.querySelectorAll(".w i"), { yPercent:118, duration:1.15, ease:"expo.out", stagger:.09, delay:.15 });
    }
    G.from("[data-hl]", { y:26, opacity:0, duration:1, ease:"power3.out", stagger:.12, delay:.5 });
    G.from("[data-eg]", { y:44, opacity:0, duration:1.3, ease:"power3.out", delay:.25 });

    /* hero : parallaxe + on se rapproche de la main */
    G.to("[data-parallax] img", { yPercent:12, ease:"none", scrollTrigger:{ trigger:".hero", start:"top top", end:"bottom top", scrub:true }});
    G.to("[data-eg]", { scale:2.9, opacity:0, ease:"power1.in", scrollTrigger:{ trigger:".hero", start:"top top", end:"bottom top", scrub:.5 }});
    G.to(".hero__in", { y:-40, opacity:0, ease:"none", scrollTrigger:{ trigger:".hero", start:"top top", end:"55% top", scrub:true }});
    G.to(".lionmark", { scale:1.25, opacity:.02, ease:"none", scrollTrigger:{ trigger:".hero", start:"top top", end:"bottom top", scrub:true }});

    /* marquee : vitesse pilotée par le scroll */
    var mq = document.getElementById("mq");
    mq.innerHTML += mq.innerHTML;
    var tween = G.to(mq, { xPercent:-50, ease:"none", repeat:-1, duration:26 });
    ST.create({ onUpdate: function(self){
      var v = self.getVelocity() / 400;
      tween.timeScale(Math.max(-4, Math.min(6, 1 + v)));
      clearTimeout(mq._t); mq._t = setTimeout(function(){ G.to(tween, { timeScale:1, duration:.6, overwrite:true }); }, 90);
    }});

    /* tableau : Ken Burns lent */
    G.fromTo("[data-tabimg] img", { scale:1.02, yPercent:-3 }, { scale:1.16, yPercent:3, ease:"none",
      scrollTrigger:{ trigger:".tab", start:"top top", end:"bottom bottom", scrub:true }});

    /* révélations */
    G.utils.toArray("[data-rv]").forEach(function(el){
      G.from(el, { y:32, opacity:0, duration:.95, ease:"power3.out", scrollTrigger:{ trigger:el, start:"top 88%" }});
    });
    G.utils.toArray("[data-media]").forEach(function(m){
      G.fromTo(m.querySelector("img"), { scale:1.24, yPercent:-6 }, { scale:1.02, yPercent:6, ease:"none",
        scrollTrigger:{ trigger:m, start:"top bottom", end:"bottom top", scrub:true }});
      G.from(m, { clipPath:"inset(100% 0% 0% 0%)", duration:1.25, ease:"power4.out", scrollTrigger:{ trigger:m, start:"top 84%" }});
    });
    /* offres en cascade */
    G.from("[data-pack]", { y:56, opacity:0, duration:1, ease:"power3.out", stagger:.12,
      scrollTrigger:{ trigger:".of__grid", start:"top 82%" }});
    /* cartes atelier */
    G.from("#grid .card", { y:40, opacity:0, duration:.85, ease:"power3.out", stagger:.06,
      scrollTrigger:{ trigger:"#grid", start:"top 85%" }});
    /* lion */
    G.fromTo("#lionImg", { scale:.5, opacity:0, filter:"blur(14px)" },
      { scale:1, opacity:1, filter:"blur(0px)", ease:"none",
        scrollTrigger:{ trigger:".lion", start:"top top", end:"bottom bottom", scrub:.6 }});
  }

  /* filtres de l'atelier */
  var cards = Array.prototype.slice.call(document.querySelectorAll("#grid .card"));
  document.querySelectorAll(".fbtn").forEach(function(b){
    b.addEventListener("click", function(){
      document.querySelectorAll(".fbtn").forEach(function(x){ x.classList.remove("is-on"); });
      b.classList.add("is-on");
      var f = b.dataset.f;
      cards.forEach(function(c){
        var show = (f === "tous" || c.dataset.cat === f);
        if (ok) {
          G.to(c, { opacity: show ? 1 : 0, scale: show ? 1 : .96, duration:.35, ease:"power2.out",
            onStart: function(){ if (show) c.style.display = ""; },
            onComplete: function(){ if (!show) c.style.display = "none"; if (ST) ST.refresh(); }});
        } else { c.style.display = show ? "" : "none"; }
      });
    });
  });

  /* ---- dissolution en pixels dorés ---- */
  var cv = document.getElementById("cv"), ctx = cv.getContext("2d");
  var img = new Image(); img.src = "<?php echo esc_url( $dir . '/assets/images/team/1_bureau_naples.jpg' ); ?>";
  var cell = 12, cols = 0, rows = 0, data = null, seed = null, progress = 0, ready = false;

  function build(){
    var dpr = Math.min(devicePixelRatio || 1, 2);
    cv.width = Math.floor(cv.clientWidth * dpr); cv.height = Math.floor(cv.clientHeight * dpr);
    if (!img.complete || !img.naturalWidth) return;
    cols = Math.ceil(cv.width / cell); rows = Math.ceil(cv.height / cell);
    var c = document.createElement("canvas"); c.width = cols; c.height = rows;
    var cx = c.getContext("2d");
    var r = Math.max(cols / img.naturalWidth, rows / img.naturalHeight);
    var w = img.naturalWidth * r, h = img.naturalHeight * r;
    cx.drawImage(img, (cols - w) / 2, (rows - h) / 2 * 1.1, w, h);
    data = cx.getImageData(0, 0, cols, rows).data;
    seed = new Float32Array(cols * rows);
    for (var i = 0; i < seed.length; i++) seed[i] = Math.random();
    ready = true; draw();
  }

  function draw(){
    if (!ready) return;
    ctx.fillStyle = "#05050a"; ctx.fillRect(0, 0, cv.width, cv.height);
    var r = Math.max(cv.width / img.naturalWidth, cv.height / img.naturalHeight);
    var iw = img.naturalWidth * r, ih = img.naturalHeight * r;
    ctx.drawImage(img, (cv.width - iw) / 2, (cv.height - ih) / 2 * 1.1, iw, ih);
    var p = progress;
    for (var y = 0; y < rows; y++) for (var x = 0; x < cols; x++){
      var i = y * cols + x;
      var th = (x / cols) * .45 + (y / rows) * .25 + seed[i] * .55;
      var k = (p - th) / .35;
      if (k <= 0) continue;
      ctx.fillStyle = "#05050a"; ctx.fillRect(x * cell, y * cell, cell + 1, cell + 1);
      if (k < 1){
        var a = 1 - k, dy = k * cell * 5 * (seed[i] - .3), dx = k * cell * 3 * (seed[i] - .5);
        ctx.fillStyle = "rgba(" + Math.round(212 + 32 * k) + "," + Math.round(180 + 28 * k) + "," + Math.round(92 + 20 * k) + "," + (a * .95) + ")";
        var s = cell * (1 - k * .6);
        ctx.fillRect(x * cell + dx, y * cell - dy, s, s);
      }
    }
  }

  img.onload = build; addEventListener("resize", build);
  if (ok){
    ST.create({ trigger:".ds", start:"top top", end:"bottom bottom", scrub:true,
      onUpdate: function(self){ progress = self.progress * 1.12; draw(); }});
    G.fromTo("#hand", { opacity:0, scale:.8 }, { opacity:.85, scale:1.05, ease:"none",
      scrollTrigger:{ trigger:".ds", start:"28% top", end:"85% bottom", scrub:true }});
  }
})();
</script>

<?php get_footer();
