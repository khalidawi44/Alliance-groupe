<?php
/**
 * Template Name: Accueil cinématique
 *
 * Accueil « scroll narratif » : hero égérie (vidéo + approche de la main), marquee
 * piloté par la vitesse de scroll, tableau allégorique, chapitres, dissolution en
 * pixels dorés (canvas), offres, atelier IA filtrable, révélation du lion.
 * Autonome : CSS et JS inline, aucune dépendance de plugin.
 * Libs : assets/js/lib/{gsap,ScrollTrigger,lenis}.min.js
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
  .w{display:inline-block;overflow:hidden;vertical-align:bottom}
  .w i{display:inline-block;font-style:inherit}
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
  .chs{padding:clamp(50px,7vh,90px) 0}
  .ch{display:grid;grid-template-columns:1fr 1fr;gap:clamp(24px,5vw,70px);align-items:center;margin-bottom:clamp(28px,5vh,64px)}
  .ch:nth-child(even) .ch__txt{order:2}
  .ch__media{position:relative;overflow:hidden;aspect-ratio:4/3;border:1px solid rgba(212,180,92,.2);will-change:transform}
  .ch__media img{width:100%;height:100%;object-fit:cover;transform:scale(1.16)}
  .ch__n{font-family:var(--serif);font-size:clamp(2.4rem,6vw,4.4rem);color:var(--gold);opacity:.8;line-height:1;will-change:transform}
  .ch__t{font-family:var(--serif);font-weight:500;font-size:clamp(1.8rem,4.4vw,3.1rem);line-height:1.06;margin:.2em 0 .5em}
  .ch__t em{font-style:italic;color:var(--gold-hi)}
  .ch__p{color:var(--muted);font-size:1.05rem;line-height:1.75;max-width:46ch}
  .ch__meta{margin-top:24px;display:flex;gap:24px;flex-wrap:wrap;font-size:.76rem;letter-spacing:.18em;text-transform:uppercase;color:var(--gold)}

  /* ---------- DISSOLUTION ---------- */
  .ds{position:relative;height:620svh}
  .ds__stick{position:sticky;top:0;height:100svh;overflow:hidden;display:grid;place-items:center}
  #cv{width:100%;height:100%;display:block}
  .ds__photo{display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:1}
  .ds__hand{position:absolute;z-index:4;width:min(52vw,620px);mix-blend-mode:screen;opacity:0;pointer-events:none}
  .ds__veil{position:absolute;inset:0;z-index:3;background:linear-gradient(180deg,rgba(5,5,10,.5),transparent 32%,rgba(5,5,10,.92))}
  .ds__cap{position:absolute;z-index:5;left:0;right:0;bottom:11vh;text-align:center;padding:0 26px}
  .ds__cap h2{font-family:var(--serif);font-weight:500;font-size:clamp(1.9rem,5.4vw,4rem);line-height:1.05}
  .ds__cap h2 em{font-style:italic;color:var(--gold-hi)}
  .ds__cap p{margin:16px auto 0;max-width:52ch;color:var(--muted);line-height:1.65}

  /* ---------- OFFRES (couche de la scène) ---------- */
  .of{position:absolute;inset:0;z-index:6;display:flex;flex-direction:column;justify-content:center;
      padding:clamp(84px,12vh,120px) 0 clamp(20px,4vh,50px);overflow:hidden;pointer-events:none}
  .of.is-live{pointer-events:auto}
  .of__bg{position:absolute;inset:0;opacity:0}
  .of__bg img{width:100%;height:100%;object-fit:cover}
  .of__in{position:relative;z-index:2;text-align:center;width:100%}
  .of__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin:26px 0 18px;text-align:left}
  @media(max-width:900px){.of__grid{grid-template-columns:1fr}}
  .pack{position:relative;background:#0c0c14;border:1px solid rgba(255,255,255,.09);border-radius:18px;overflow:hidden;
        transform-style:preserve-3d;transition:border-color .35s,box-shadow .35s}
  .pack:hover{border-color:rgba(212,180,92,.6);box-shadow:0 40px 90px -50px rgba(212,180,92,.8)}
  .pack.star{border-color:rgba(212,180,92,.55);box-shadow:0 34px 80px -46px rgba(212,180,92,.7)}
  .pack img{width:100%;aspect-ratio:4/3;object-fit:cover}
  .pack__body{padding:16px 18px 18px}
  .pack__body{padding:22px 24px 26px}
  .pack__cta{display:block;text-align:center;margin-top:6px}
  .of__note{position:relative;z-index:2;color:var(--muted);font-size:.95rem}
  .of__note a{color:var(--gold-hi)}

  /* ---------- ATELIER (couche de la scène) ---------- */
  .at{position:absolute;inset:0;z-index:7;display:flex;flex-direction:column;justify-content:center;
      padding:clamp(84px,12vh,120px) 0 clamp(18px,3vh,40px);opacity:0;pointer-events:none;overflow:hidden}
  .at .stitle{font-size:clamp(1.4rem,3.2vw,2.2rem);margin:8px 0 10px}
  .at .lead{font-size:clamp(.9rem,1.7vw,1rem);line-height:1.5}
  .at .at__head{margin:0 auto 14px}
  @media(max-height:900px){.at .card__d{display:none}.at .card__t{font-size:.92rem}.at .lead{display:none}}
  .at.is-live{pointer-events:auto}
  .at .wrap{position:relative;z-index:2;width:100%}
  .at__head{text-align:center;max-width:760px;margin:0 auto 18px}
  .at__filters{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:18px}
  .fbtn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.14);color:#cdd4e2;border-radius:999px;
        padding:9px 20px;font-size:.85rem;cursor:pointer;transition:.25s;font-family:inherit}
  .fbtn:hover{border-color:var(--gold)}
  .fbtn.is-on{background:linear-gradient(120deg,var(--gold),var(--gold-hi));color:#1a1206;font-weight:700;border-color:transparent}
  .at__grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
  @media(max-width:1100px){.at__grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(max-width:640px){.at__grid{grid-template-columns:1fr}}
  .card{position:relative;background:#0c0c14;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;
        text-decoration:none;display:block;transition:border-color .35s,transform .35s}
  .card:hover{border-color:rgba(212,180,92,.55);transform:translateY(-4px)}
  .card__media{position:relative;aspect-ratio:16/10;overflow:hidden}
  .card__media img{width:100%;height:100%;object-fit:cover;transition:transform .7s cubic-bezier(.2,.7,.2,1)}
  .card:hover .card__media img{transform:scale(1.07)}
  .card__badge{position:absolute;top:12px;left:12px;z-index:2;background:rgba(5,5,10,.72);border:1px solid rgba(212,180,92,.5);
    color:var(--gold-hi);font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;padding:5px 12px;border-radius:999px}
  .card__body{padding:13px 15px 15px}
  .card__t{font-size:1.02rem;font-weight:700;margin-bottom:6px}
  .card__d{color:var(--muted);font-size:.84rem;line-height:1.45;min-height:2.6em}
  .card__go{margin-top:12px;font-size:.82rem;color:var(--gold);letter-spacing:.06em}


  /* ---------- RÉALISATIONS (aperçu) ---------- */
  .rz{padding:clamp(70px,12vh,150px) 0;text-align:center;position:relative;overflow:hidden}
  .rz__glow{position:absolute;left:50%;top:34%;transform:translate(-50%,-50%);width:min(94vw,1200px);height:64vh;
    background:radial-gradient(closest-side,rgba(212,180,92,.12),transparent 72%);pointer-events:none}
  .rz__in{position:relative;z-index:2}
  .rz__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px;margin:38px 0 34px;text-align:left}
  .rz__card{background:#0b0b12;border:1px solid rgba(255,255,255,.09);border-radius:18px;overflow:hidden;
    display:flex;flex-direction:column;transition:border-color .35s,transform .35s,box-shadow .35s}
  .rz__card:hover{border-color:rgba(212,180,92,.55);transform:translateY(-5px);box-shadow:0 50px 100px -55px rgba(212,180,92,.7)}
  .rz__vue{display:block;position:relative;overflow:hidden;aspect-ratio:16/10;background:#07070d}
  .rz__vue img{width:100%;height:100%;object-fit:cover;object-position:center top;transition:transform .8s cubic-bezier(.2,.7,.2,1)}
  .rz__card:hover .rz__vue img{transform:scale(1.06)}
  .rz__body{padding:18px 20px 22px;display:flex;flex-direction:column;gap:9px;flex:1}
  .rz__meta{font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;color:var(--gold)}
  .rz__card h3{font-family:var(--serif);font-weight:500;font-size:1.42rem;line-height:1.1;margin:0}
  .rz__card p{color:var(--muted);font-size:.92rem;line-height:1.6;margin:0}
  .rz__pts{list-style:none;display:flex;flex-wrap:wrap;gap:7px;margin:2px 0 0;padding:0}
  .rz__pts li{border:1px solid rgba(212,180,92,.3);border-radius:999px;padding:5px 12px;font-size:.72rem;color:#cdd4e2}
  .rz__liens{margin-top:auto;padding-top:14px;display:flex;flex-wrap:wrap;gap:8px 16px;font-size:.84rem}
  .rz__liens a{text-decoration:none;color:var(--gold-hi);border-bottom:1px solid transparent;transition:border-color .25s}
  .rz__liens a:hover{border-color:var(--gold-hi)}
  .rz__liens .rz__avis{color:#cdd4e2}

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

  /* ======================================================
     TABLETTE ET MOBILE — les scènes épinglées deviennent
     un enchaînement classique, tout tient dans le cadre.
     ====================================================== */
  @media(max-width:1100px){
    .rz__grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .at__grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .of__grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .ch{gap:26px}
  }
  @media(max-width:960px){
    .wrap{padding:0 20px}
    .hd{padding:14px 0}
    .hd.is-stuck{padding:8px 0}
    .hd__brand img{width:38px;height:38px}
    .hd__nav .btn{padding:10px 15px;font-size:.8rem;letter-spacing:.04em}

    .hero{height:92svh;align-items:flex-end}
    .hero__eg{right:50%;left:auto;transform:translateX(50%);width:min(100vw,640px);height:70svh;
      -webkit-mask-image:radial-gradient(ellipse 72% 62% at 50% 54%,#000 46%,rgba(0,0,0,.86) 66%,rgba(0,0,0,.34) 84%,transparent 97%);
      mask-image:radial-gradient(ellipse 72% 62% at 50% 54%,#000 46%,rgba(0,0,0,.86) 66%,rgba(0,0,0,.34) 84%,transparent 97%)}
    .hero__eg video,.hero__eg img{object-position:center 30%}
    .hero__in{padding-bottom:30px;position:relative;isolation:isolate}
    .hero__in::before{content:"";position:absolute;inset:-40px -24px -60px;z-index:-1;pointer-events:none;
      background:linear-gradient(0deg,rgba(5,5,10,.95) 34%,rgba(5,5,10,.78) 62%,rgba(5,5,10,0) 100%)}
    .hero__t{font-size:clamp(2.3rem,11vw,3.6rem);line-height:1.02}
    .hero__sub{font-size:1rem;max-width:34ch}
    .hero__cta{margin-top:20px;gap:10px}
    .btn{padding:13px 24px;font-size:.92rem}
    .lionmark{width:92vw;opacity:.08;top:38%}
    .hero__scroll{display:none}

    .mq{padding:14px 0}
    .mq__in{gap:28px}
    .mq__in span{font-size:1.05rem}

    .tab{height:150svh}
    /* la peinture est en 16/9 : on la montre entière, tableau puis légende */
    .tab__stick{display:flex;flex-direction:column;justify-content:center;gap:clamp(18px,4svh,44px)}
    .tab__img{position:relative;inset:auto;width:100%;height:auto;aspect-ratio:16/9}
    .tab__img img{object-fit:cover;height:100%;transform:none!important}
    .tab__veil{display:none}
    .tab__cap{align-self:auto;padding:0 20px}
    .tab__cap h2{font-size:clamp(1.7rem,7.4vw,2.4rem)}
    .tab__cap p{font-size:.95rem;margin-top:12px}

    .chs{padding:44px 0}
    .ch{grid-template-columns:1fr;margin-bottom:44px}
    .ch:nth-child(even) .ch__txt{order:0}
    .ch__media{aspect-ratio:4/3}
    .ch__p{font-size:1rem}
    .ch__meta{gap:16px;font-size:.7rem}

    /* ── LA SCÈNE RESTE ÉPINGLÉE : les choses sortent de la paume ──
       Le canvas de dissolution (coûteux) est remplacé par une photo qui
       se dissout en flou, et les offres sortent de la main UNE PAR UNE
       pour tenir dans le cadre. */
    .ds{height:560svh}
    #cv{display:none}
    .ds__photo{display:block}
    .ds__hand{left:0;right:0;margin:0 auto;top:2svh;width:min(96vw,440px)}
    .ds__cap{bottom:9svh;padding:0 20px}
    .ds__cap h2{font-size:clamp(1.7rem,7.4vw,2.4rem)}
    .ds__cap p{font-size:.94rem;margin-top:12px}

    .of,.at{padding:0}
    .of__in{display:flex;flex-direction:column;justify-content:center;height:100%}
    .of .lead,.of__note{display:none}
    .of__bg{opacity:.3}
    .of__grid{display:block;position:relative;height:min(46svh,340px);margin:14px 0 0}
    .pack{position:absolute;inset:0;margin:auto;height:max-content;width:min(82vw,320px)}
    .pack__body{padding:14px 16px 16px}
    .pack__cta{padding:12px 18px;font-size:.86rem}

    .at .wrap{display:flex;flex-direction:column;justify-content:center;height:100%}
    .at .lead{display:none}
    .at .stitle{font-size:clamp(1.5rem,6.2vw,2rem);margin:8px 0 0}
    .at__head{margin:0 auto 12px}
    .at__filters{margin-bottom:12px;gap:7px}
    .fbtn{padding:7px 14px;font-size:.78rem}
    .at__grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .at .card__media{aspect-ratio:16/9}
    .at .card__d,.at .card__go{display:none}
    .at .card__t{font-size:.86rem;margin:0}
    .card__body{padding:9px 11px 11px}
    .card__badge{font-size:.6rem;padding:4px 9px;top:8px;left:8px}

    .rz{padding:56px 0}
    .rz__grid{grid-template-columns:1fr;gap:16px;margin:26px 0 24px}
    .rz__card h3{font-size:1.28rem}

    .lion{height:130svh}
    .lion__img{width:min(62vw,300px)}
    .lion__w{bottom:12vh;letter-spacing:.5em}
    .cta{padding:60px 0}
    .ft{flex-direction:column;align-items:center;text-align:center;gap:8px}
  }
  @media(max-width:600px){
    .mq__in span{font-size:.95rem}
    .tab{height:140svh}
  }
  /* tablette : la scène est épinglée comme en desktop, mais les 3 offres
     tiennent côte à côte — inutile de les montrer une par une. */
  @media(min-width:701px) and (max-width:960px){
    .ds__hand{width:min(62vw,520px);top:3svh}
    .of__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;
              height:auto;position:static;margin:22px 0 0}
    .pack{position:relative;inset:auto;margin:0;width:auto;height:auto}
    .of .lead{display:block;margin:14px auto 0}
    .at .lead{display:block;margin:0 auto}
    .at .card__t{font-size:.95rem}
    .card__body{padding:12px 14px 14px}
  }

  /* écrans courts : on resserre encore la grille de l'atelier */
  @media(max-width:960px) and (max-height:760px){
    .ds__hand{width:min(78vw,340px)}
    .of__grid{height:min(44svh,300px)}
    .pack{width:min(70vw,260px)}
    .at__grid{gap:8px}
    .at .card__t{font-size:.78rem}
    .card__body{padding:7px 9px 9px}
  }
  @media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>




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
      <h2 data-mots>Une alliance, <em>pas un prestataire</em></h2>
      <p data-txt>On ne vend pas des pages : on s'engage à côté de vous, du premier échange jusqu'au jour où le site rapporte. Naples pour l'atelier, Nantes pour le terrain.</p>
    </div>
  </div>
</section>

<section class="chs wrap" id="chapitres">
  <article class="ch">
    <div class="ch__txt">
      <div class="ch__n" data-rv>01</div>
      <h3 class="ch__t" data-mots>Un artisan, <em>pas une usine</em></h3>
      <p class="ch__p" data-txt>De Naples à Nantes, un seul interlocuteur : celui qui conçoit, celui qui code, celui qui livre. Vous ne parlez jamais à un service.</p>
      <div class="ch__meta" data-rv><span>Conseil</span><span>Design</span><span>Développement</span></div>
    </div>
    <div class="ch__media" data-media><img src="<?php echo esc_url( $dir . '/assets/images/team/1_bureau_naples.jpg' ); ?>" alt="Le bureau de Naples" loading="lazy"></div>
  </article>
  <article class="ch">
    <div class="ch__txt">
      <div class="ch__n" data-rv>02</div>
      <h3 class="ch__t" data-mots>La sécurité, <em>incluse</em></h3>
      <p class="ch__p" data-txt>Chaque site part d'un audit gratuit et arrive sécurisé — rare chez les agences classiques, systématique ici.</p>
      <div class="ch__meta" data-rv><span>Audit offert</span><span>Sauvegardes</span><span>Surveillance</span></div>
    </div>
    <div class="ch__media" data-media><img src="<?php echo esc_url( $dir . '/assets/images/team/reunion-naples.jpg' ); ?>" alt="Réunion au bureau de Naples" loading="lazy"></div>
  </article>
  <article class="ch">
    <div class="ch__txt">
      <div class="ch__n" data-rv>03</div>
      <h3 class="ch__t" data-mots>Propulsé <em>par l'IA</em></h3>
      <p class="ch__p" data-txt>Devis en trente secondes, maquettes régénérées, contenus rédigés : la machine fait le gros du travail, vous gardez le style et le contrôle.</p>
      <div class="ch__meta" data-rv><span>Devis instantané</span><span>Contenus</span><span>Images</span></div>
    </div>
    <div class="ch__media" data-media><img src="<?php echo esc_url( $dir . '/assets/images/team/naples-2.jpg' ); ?>" alt="Poste de travail avec vue sur la baie" loading="lazy"></div>
  </article>
</section>


<section class="ds" id="atelier">
  <div class="ds__stick">
    <canvas id="cv"></canvas>
    <img class="ds__photo" src="<?php echo esc_url( $dir . '/assets/images/team/1_bureau_naples.jpg' ); ?>" alt="">
    <img class="ds__hand" id="hand" src="<?php echo esc_url( $dir . '/assets/images/cinematique/main-poussiere-or.jpg' ); ?>" alt="">
    <div class="ds__veil"></div>
    <div class="ds__cap wrap" id="dsCap">
      <h2 data-mots>Ce qui compte ne se voit pas <em>tout de suite</em></h2>
      <p>Un site n'est pas une image. C'est une mécanique — vitesse, sécurité, référencement — qui se révèle quand on regarde de près.</p>
    </div>
<div class="at" id="atStage">
 
  
  <div class="wrap">
    <div class="at__head">
      <span class="eyebrow">Alliance Groupe · web · sécurité · IA</span>
      <h2 class="stitle" data-mots style="margin:12px 0 14px">Que créons-nous <em>aujourd'hui&nbsp;?</em></h2>
      <p class="lead" style="margin:0 auto">Votre atelier propulsé par l'IA : chiffrez un projet, refaites votre site, sécurisez-le, créez du contenu.</p>
    </div>
    <div class="at__filters">
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
</div>
    <div class="of" id="ofStage" data-ancre="offres">
  <div class="of__bg"><img src="<?php echo esc_url( $dir . '/assets/images/cinematique/marbre-noir-or.jpg' ); ?>" alt="" loading="lazy"></div>
  <div class="wrap of__in">
    <span class="eyebrow" data-rv>Nos formules</span>
    <h2 class="stitle" data-mots style="margin-top:10px">Des offres claires, <em>à prix fixe</em></h2>
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
</div>

  </div>
</section>


<section class="rz" id="realisations">
  <div class="rz__glow"></div>
  <div class="wrap rz__in">
    <span class="eyebrow" data-rv>Nos réalisations</span>
    <h2 class="stitle" data-mots style="margin:12px 0 16px">Ce qu'on livre, <em>pour de vrai</em></h2>
    <p class="lead" data-txt style="margin:0 auto">Des sites en ligne, des clients joignables, des avis vérifiables. Allez les voir — et allez lire ce qu'on en dit sur Google.</p>
    <div class="rz__grid">
      <article class="rz__card" data-rv>
        <a class="rz__vue" href="https://gwen-services.alliancegroupe-inc.com/" target="_blank" rel="noopener"><img src="<?php echo esc_url( $dir . '/assets/images/realisations/carte-gwen.jpg' ); ?>" alt="Le site Gwen Services" loading="lazy"></a>
        <div class="rz__body">
          <span class="rz__meta">Aide à domicile · Nantes</span>
          <h3>Gwen Services</h3>
          <p>Une auxiliaire de vie qui n'avait rien en ligne. Site, textes, images générées sur mesure, SEO local et sécurité — livré en cinq jours.</p>
          <ul class="rz__pts"><li>Livré en 5 jours</li><li>Images sur mesure</li><li>SEO local</li></ul>
          <div class="rz__liens"><a href="https://gwen-services.alliancegroupe-inc.com/" target="_blank" rel="noopener">Voir le site ↗</a><a href="<?php echo esc_url( home_url( '/realisation-gwen' ) ); ?>">L'étude de cas →</a><a class="rz__avis" href="https://www.google.com/maps/search/?api=1&amp;query=Gwen%20Services%20aide%20%C3%A0%20domicile%20Nantes" target="_blank" rel="noopener">★ Avis Google ↗</a></div>
        </div>
      </article>
      <article class="rz__card" data-rv>
        <a class="rz__vue" href="https://annaphoto.eu/" target="_blank" rel="noopener"><img src="<?php echo esc_url( $dir . '/assets/images/realisations/carte-anna.jpg' ); ?>" alt="Le site Anna Photo" loading="lazy"></a>
        <div class="rz__body">
          <span class="rz__meta">Photographie · Nantes</span>
          <h3>Anna Photo</h3>
          <p>Blog et portfolio pour une photographe portraitiste. Navigation fluide, mise en valeur des clichés, référencement travaillé article par article.</p>
          <ul class="rz__pts"><li>+180 % de trafic</li><li>23 articles</li><li>Portfolio complet</li></ul>
          <div class="rz__liens"><a href="https://annaphoto.eu/" target="_blank" rel="noopener">Voir le site ↗</a><a class="rz__avis" href="https://www.google.com/maps/search/?api=1&amp;query=Anna%20Photo%20photographe%20Nantes" target="_blank" rel="noopener">★ Avis Google ↗</a></div>
        </div>
      </article>
      <article class="rz__card" data-rv>
        <a class="rz__vue" href="https://www.paysagiste-environnement.com/" target="_blank" rel="noopener"><img src="<?php echo esc_url( $dir . '/assets/images/realisations/carte-la.jpg' ); ?>" alt="Le site L.A Environnement" loading="lazy"></a>
        <div class="rz__body">
          <span class="rz__meta">Paysagiste · Loire-Atlantique</span>
          <h3>L.A Environnement</h3>
          <p>Site vitrine et génération de devis pour un paysagiste. Formulaires optimisés et référencement local dominant sur son secteur.</p>
          <ul class="rz__pts"><li>+320 % de devis</li><li>Top 3 Google</li><li>15 devis/mois</li></ul>
          <div class="rz__liens"><a href="https://www.paysagiste-environnement.com/" target="_blank" rel="noopener">Voir le site ↗</a><a class="rz__avis" href="https://www.google.com/maps/search/?api=1&amp;query=L.A%20Environnement%20paysagiste%20Nantes" target="_blank" rel="noopener">★ Avis Google ↗</a></div>
        </div>
      </article>
    </div>
    <a class="btn" data-rv href="<?php echo esc_url( home_url( '/realisations' ) ); ?>">Toutes nos réalisations</a>
  </div>
</section>

<section class="lion">
  <div class="lion__stick">
    <img class="lion__img" id="lionImg" src="<?php echo esc_url( $dir . '/assets/images/cinematique/lion-or.jpg' ); ?>" alt="Le lion d'Alliance Groupe">
    <div class="lion__w" data-rv>Alliance Groupe</div>
  </div>
</section>

<section class="cta wrap">
  <h2 class="stitle" data-mots>Parlons de <em>votre site</em></h2>
  <p data-rv>Audit gratuit, devis en trente secondes, livraison en cinq jours. On commence par un échange, sans engagement.</p>
  <a class="btn" data-rv href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">Demander mon audit</a>
</section>

<footer class="wrap ft">
  <span>© Alliance Groupe — Naples · Nantes · Marrakech</span>
  <span><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales</a> · <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact</a></span>
</footer>

<script src="<?php echo esc_url( $dir . '/assets/js/lib/gsap.min.js' ); ?>"></script>
<script src="<?php echo esc_url( $dir . '/assets/js/lib/ScrollTrigger.min.js' ); ?>"></script>
<script src="<?php echo esc_url( $dir . '/assets/js/lib/lenis.min.js' ); ?>"></script>

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
  if (hd) addEventListener("scroll", function(){ hd.classList.toggle("is-stuck", scrollY > 60); }, { passive:true });

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
    if (!matchMedia("(max-width:960px)").matches)
      G.to("[data-eg]", { scale:1.6, ease:"none", scrollTrigger:{ trigger:".hero", start:"top top", end:"bottom top", scrub:.5 }});
    G.to(".hero__in", { y:-40, opacity:0, ease:"none", scrollTrigger:{ trigger:".hero", start:"top top", end:"55% top", scrub:true }});
    G.to(".lionmark", { scale:1.25, opacity:.02, ease:"none", scrollTrigger:{ trigger:".hero", start:"top top", end:"bottom top", scrub:true }});

    /* bandeau : boucle continue en rAF, accélérée par la vitesse de scroll */
    (function(){
      var mq = document.getElementById("mq");
      mq.innerHTML += mq.innerHTML;            // deux copies pour boucler sans trou
      var x = 0, boost = 1, half = 0, last = 0;
      function measure(){ half = mq.scrollWidth / 2; }
      measure(); addEventListener("resize", measure);
      ST.create({ onUpdate: function(self){
        boost = 1 + Math.min(5, Math.abs(self.getVelocity()) / 900);
      }});
      G.ticker.add(function(t){
        var dt = last ? Math.min(0.05, t - last) : 0.016; last = t;
        if (!half) measure();
        x -= 46 * dt * boost;                  // 46 px/s au repos
        if (half && x <= -half) x += half;     // rebouclage exact
        mq.style.transform = "translate3d(" + x + "px,0,0)";
        boost += (1 - boost) * 0.06;           // retour au calme
      });
    })();

    /* tableau : Ken Burns lent */
    if (!matchMedia("(max-width:960px)").matches)
      G.fromTo("[data-tabimg] img", { scale:1.02, yPercent:-3 }, { scale:1.16, yPercent:3, ease:"none",
        scrollTrigger:{ trigger:".tab", start:"top top", end:"bottom bottom", scrub:true }});

    /* titres : chaque mot monte derrière un masque */
    G.utils.toArray("[data-mots]").forEach(function(h){
      var html = h.innerHTML;
      h.innerHTML = html.replace(/(<em>|<\/em>)/g, "\u0001$1\u0001").split("\u0001").map(function(part){
        if (part.charAt(0) === "<") return part;
        return part.split(" ").map(function(w){ return w ? '<span class="w"><i>'+w+'</i></span>' : ""; }).join(" ");
      }).join("");
      G.from(h.querySelectorAll(".w i"), { yPercent:118, duration:1.05, ease:"expo.out", stagger:.055,
        scrollTrigger:{ trigger:h, start:"top 88%" }});
    });
    /* paragraphes : montée + léger flou qui se lève, puis dérive douce */
    G.utils.toArray("[data-txt]").forEach(function(p){
      G.from(p, { y:26, opacity:0, filter:"blur(6px)", duration:1, ease:"power3.out",
        scrollTrigger:{ trigger:p, start:"top 90%" }});
      G.to(p, { y:-22, ease:"none", scrollTrigger:{ trigger:p, start:"top bottom", end:"bottom top", scrub:true }});
    });

    /* révélations */
    G.utils.toArray("[data-rv]").forEach(function(el){
      G.from(el, { y:32, opacity:0, duration:.95, ease:"power3.out", scrollTrigger:{ trigger:el, start:"top 88%" }});
    });
    G.utils.toArray("[data-media]").forEach(function(m, i){
      var sens = i % 2 ? -1 : 1;
      /* l'image vit pendant tout son passage : zoom, glissement et redressement */
      G.fromTo(m.querySelector("img"), { scale:1.32, yPercent:-10 }, { scale:1.02, yPercent:10, ease:"none",
        scrollTrigger:{ trigger:m, start:"top bottom", end:"bottom top", scrub:.4 }});
      G.fromTo(m, { yPercent:9 * sens, rotate:1.4 * sens }, { yPercent:-9 * sens, rotate:0, ease:"none",
        scrollTrigger:{ trigger:m, start:"top bottom", end:"bottom top", scrub:.4 }});
      G.from(m, { clipPath:"inset(100% 0% 0% 0%)", duration:1.2, ease:"power4.out", scrollTrigger:{ trigger:m, start:"top 86%" }});
      /* la colonne de texte arrive par le côté opposé */
      var txt = m.parentNode.querySelector(".ch__txt");
      if (txt) G.fromTo(txt, { x:52 * -sens, opacity:.35 }, { x:0, opacity:1, ease:"power2.out",
        scrollTrigger:{ trigger:m.parentNode, start:"top 92%", end:"top 45%", scrub:.5 }});
      /* le numéro file plus vite */
      var n = m.parentNode.querySelector(".ch__n");
      if (n) G.to(n, { yPercent:-70, ease:"none", scrollTrigger:{ trigger:m.parentNode, start:"top bottom", end:"bottom top", scrub:true }});
    });
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

  var petitEcran = matchMedia("(max-width:960px)").matches;
  if (!petitEcran){ img.onload = build; addEventListener("resize", build); }
  if (ok && !petitEcran){
    ST.create({ trigger:".ds", start:"top top", end:"bottom bottom", scrub:true,
      onUpdate: function(self){ progress = Math.min(1.15, self.progress / .30 * 1.12); draw(); }});
  }
  if (ok){
    /* SCÈNE UNIQUE : dissolution -> main -> offres -> évaporation -> atelier */
    (function(){
      /* ---------------------------------------------------------------
         TABLETTE ET MOBILE : même scène, même main, mais les offres
         sortent de la paume UNE PAR UNE pour tenir dans le cadre, et la
         dissolution en pixels (canvas) laisse la place a un flou.
         --------------------------------------------------------------- */
      if (matchMedia("(max-width:960px)").matches){
        var stM   = document.querySelector(".ds__stick");
        var handM = document.getElementById("hand");
        var photo = document.querySelector(".ds__photo");
        var ofM   = document.getElementById("ofStage");
        var atM   = document.getElementById("atStage");
        var packM = G.utils.toArray("#ofStage [data-pack]");
        var cardM = G.utils.toArray("#atStage .card");

        /* la paume : mesurée sur la main réelle, insensible aux transforms */
        function paumeM(){ return { x: handM.offsetLeft + handM.offsetWidth / 2,
                                    y: handM.offsetTop  + handM.offsetHeight * .52 }; }
        function centreM(el){ var x=0,y=0,n=el; while(n && n!==stM){ x+=n.offsetLeft; y+=n.offsetTop; n=n.offsetParent; }
                              return { x:x+el.offsetWidth/2, y:y+el.offsetHeight/2 }; }

        G.set([ofM, atM], { opacity:0 });
        G.set(".at__filters", { opacity:0, y:12 });
        G.set(packM, { opacity:0 });

        var tm = G.timeline({ scrollTrigger:{ trigger:".ds", start:"top top", end:"bottom bottom",
          scrub:.5, invalidateOnRefresh:true,
          onUpdate:function(self){
            var p = self.progress;
            ofM.classList.toggle("is-live", p > .30 && p < .56);
            atM.classList.toggle("is-live", p > .64);
          }}});

        /* la photo se dissout, la main paraît */
        tm.to(photo, { opacity:0, scale:1.2, filter:"blur(20px)", duration:.16, ease:"power2.in" }, .04)
          .fromTo(handM, { opacity:0, scale:.8, yPercent:8 },
                         { opacity:.98, scale:1, yPercent:0, duration:.06, ease:"power2.out" }, .17)
          .to("#dsCap", { opacity:0, y:-26, duration:.05, ease:"power2.in" }, .20)
          .to(ofM, { opacity:1, duration:.02 }, .26)
          .fromTo("#ofStage .of__in > *:not(.of__grid)", { opacity:0, y:26 },
                  { opacity:1, y:0, duration:.05, stagger:.02, ease:"power3.out" }, .27)
          .to(handM, { opacity:.34, scale:1.16, duration:.10, ease:"none" }, .30);

        /* les offres jaillissent de la paume : une par une sur téléphone
           (elles ne tiendraient pas ensemble), toutes ensemble sur tablette */
        var unParUn = matchMedia("(max-width:700px)").matches;
        var T0 = .30, PAS = unParUn ? .085 : .022;
        packM.forEach(function(c, i){
          tm.fromTo(c,
            { opacity:0, scale:.08, rotation:(i - 1) * 26,
              x:function(){ return paumeM().x - centreM(c).x; },
              y:function(){ return paumeM().y - centreM(c).y; } },
            { keyframes:[
                { opacity:1, scale:.46, rotation:(i - 1) * 12, duration:.5,
                  x:function(){ return (paumeM().x - centreM(c).x) * .4; },
                  y:function(){ return (paumeM().y - centreM(c).y) * .5 - 30; } },
                { opacity:1, scale:1, rotation:0, x:0, y:0, duration:.5, ease:"power3.out" }
              ], ease:"none", duration:.06 },
            T0 + i * PAS);
          tm.to(c, { opacity:0, scale:.82, y:-60, filter:"blur(7px)", duration:.032, ease:"power2.in" },
            unParUn ? T0 + (i + 1) * PAS - .014 : .55 + i * .012);
        });

        /* les offres s'effacent, l'atelier prend la place */
        tm.to("#ofStage .of__in > *:not(.of__grid)", { opacity:0, y:-34, duration:.05, ease:"power2.in" }, .55)
          .to(ofM, { opacity:0, duration:.02 }, .61)
          .to(handM, { opacity:.95, scale:1, duration:.06, ease:"power2.out" }, .55)
          .to(atM, { opacity:1, duration:.02 }, .61)
          .fromTo(".at__head", { opacity:0, y:26 }, { opacity:1, y:0, duration:.06, ease:"power3.out" }, .62)
          .to(handM, { opacity:.18, scale:1.24, duration:.12, ease:"none" }, .66);

        cardM.forEach(function(c, i){
          var a0 = i * (Math.PI * 2 / cardM.length);
          tm.fromTo(c,
            { opacity:0, scale:.07, rotation:(i % 2 ? 1 : -1) * (240 + i * 30),
              x:function(){ return paumeM().x - centreM(c).x + Math.cos(a0) * 18; },
              y:function(){ return paumeM().y - centreM(c).y + Math.sin(a0) * 18; } },
            { keyframes:[
                { opacity:1, scale:.4, rotation:(i % 2 ? 1 : -1) * 100, duration:.5,
                  x:function(){ return (paumeM().x - centreM(c).x) * .42 + Math.cos(a0 + 2.2) * 70; },
                  y:function(){ return (paumeM().y - centreM(c).y) * .42 + Math.sin(a0 + 2.2) * 60; } },
                { opacity:1, scale:1, rotation:0, x:0, y:0, duration:.5, ease:"power3.out" }
              ], ease:"none", duration:.13 },
            .66 + i * .014);
        });

        tm.to(".at__filters", { opacity:1, y:0, duration:.05, ease:"power2.out" }, .88);
        return;
      }
      var stick = document.querySelector(".ds__stick");
      var of = document.getElementById("ofStage"), at = document.getElementById("atStage");
      var packs = G.utils.toArray("#ofStage [data-pack]");
      var cards = G.utils.toArray("#atStage .card");
      function paume(){ return { x: stick.clientWidth / 2, y: stick.clientHeight * 0.30 }; }
      function centre(el){ var x=0,y=0,n=el; while(n && n!==stick){ x+=n.offsetLeft; y+=n.offsetTop; n=n.offsetParent; }
        return { x:x+el.offsetWidth/2, y:y+el.offsetHeight/2 }; }

      G.set([of, at], { opacity:0 });
      G.set(".at__filters", { opacity:0, y:14 });

      var tl = G.timeline({ scrollTrigger:{ trigger:".ds", start:"top top", end:"bottom bottom",
        scrub:.5, invalidateOnRefresh:true,
        onUpdate:function(self){
          var p = self.progress;
          of.classList.toggle("is-live", p > .42 && p < .62);
          at.classList.toggle("is-live", p > .80);
        }}});

      /* la main ne paraît qu'une fois l'image entièrement dissoute */
      tl.fromTo("#hand", { opacity:0, scale:.78, yPercent:10 },
                         { opacity:.95, scale:1.05, yPercent:0, duration:.06, ease:"power2.out" }, .30)
        .to("#dsCap", { opacity:0, y:-30, duration:.04, ease:"power2.in" }, .30)
        /* les offres sortent de la paume */
        .to(of, { opacity:1, duration:.02 }, .36)
        .fromTo("#ofStage .of__in > *:not(.of__grid)", { opacity:0, y:34 },
                { opacity:1, y:0, duration:.06, stagger:.02, ease:"power3.out" }, .37)
        .to("#hand", { opacity:.2, scale:1.2, duration:.12, ease:"none" }, .40);

      packs.forEach(function(c, i){
        tl.fromTo(c,
          { opacity:0, scale:.07, rotation:(i - 1) * 26,
            x:function(){ return paume().x - centre(c).x; },
            y:function(){ return paume().y - centre(c).y; } },
          { keyframes:[
              { opacity:1, scale:.5, rotation:(i - 1) * 14, duration:.5,
                x:function(){ return (paume().x - centre(c).x) * .45 + (i - 1) * 150; },
                y:function(){ return (paume().y - centre(c).y) * .5 - 60; } },
              { opacity:1, scale:1, rotation:0, x:0, y:0, duration:.5, ease:"power3.out" }
            ], ease:"none", duration:.13 },
          .38 + i * .025);
      });

      /* les offres s'évaporent, la main se rallume */
      tl.to(packs, { opacity:0, scale:.86, y:-70, filter:"blur(8px)", duration:.09, stagger:.02, ease:"power2.in" }, .62)
        .to("#ofStage .of__in > *:not(.of__grid)", { opacity:0, y:-40, duration:.06, ease:"power2.in" }, .63)
        .to(of, { opacity:0, duration:.02 }, .72)
        .to("#hand", { opacity:.95, scale:1.05, duration:.06, ease:"power2.out" }, .64)
        /* l'atelier prend sa place */
        .to(at, { opacity:1, duration:.02 }, .70)
        .fromTo(".at__head", { opacity:0, y:30 }, { opacity:1, y:0, duration:.06, ease:"power3.out" }, .71)
        .to("#hand", { opacity:.16, scale:1.28, duration:.14, ease:"none" }, .74);

      cards.forEach(function(c, i){
        var a0 = i * (Math.PI * 2 / cards.length);
        tl.fromTo(c,
          { opacity:0, scale:.06, rotation:(i % 2 ? 1 : -1) * (300 + i * 40),
            x:function(){ return paume().x - centre(c).x + Math.cos(a0) * 26; },
            y:function(){ return paume().y - centre(c).y + Math.sin(a0) * 26; } },
          { keyframes:[
              { opacity:1, scale:.42, rotation:(i % 2 ? 1 : -1) * 120, duration:.5,
                x:function(){ return (paume().x - centre(c).x) * .45 + Math.cos(a0 + 2.2) * 200; },
                y:function(){ return (paume().y - centre(c).y) * .45 + Math.sin(a0 + 2.2) * 130; } },
              { opacity:1, scale:1, rotation:0, x:0, y:0, duration:.5, ease:"power3.out" }
            ], ease:"none", duration:.16 },
          .74 + i * .016);
      });

      tl.to(".at__filters", { opacity:1, y:0, duration:.05, ease:"power2.out" }, .93);
    })();
  }

  /* ── Ancres vers la scène épinglée ───────────────────────────
     #offres et #atelier ne sont pas des sections classiques : ce sont deux
     moments de la timeline de .ds. On vise donc la bonne progression du
     scroll plutôt que le haut de l'élément (sinon le lien semble mort). */
  (function(){
    var ds = document.querySelector(".ds");
    var reperes = { offres: .46, atelier: .84 };
    document.addEventListener("click", function(e){
      var a = e.target && e.target.closest ? e.target.closest('a[href^="#"]') : null;
      if (!a) return;
      var cle = a.getAttribute("href").slice(1);
      if (!(cle in reperes)) return;
      e.preventDefault();
      var cible;
      if (ds && !matchMedia("(max-width:960px)").matches){
        cible = ds.offsetTop + Math.max(0, ds.offsetHeight - innerHeight) * reperes[cle];
      } else {
        var el = document.getElementById(cle === "offres" ? "ofStage" : "atStage");
        cible = el ? el.getBoundingClientRect().top + (window.pageYOffset || 0) - 90 : 0;
      }
      if (typeof lenis !== "undefined" && lenis && lenis.scrollTo) lenis.scrollTo(cible, { duration: 1.7 });
      else scrollTo({ top: cible, behavior: "smooth" });
    });
  })();
})();
</script>

<?php get_footer();
