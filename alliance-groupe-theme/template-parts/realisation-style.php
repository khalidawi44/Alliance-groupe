<?php
/**
 * realisation-style.php — l'habillage commun des etudes de cas du portfolio.
 *
 * « Nos creations » d'Alliance Groupe : chaque site livre a droit a sa page
 * detaillee, et toutes partagent cette feuille. Une seule a corriger le jour
 * ou l'habillage bouge, au lieu d'une copie par page.
 *
 * Le bloc `.rp` porte tout — heros, fiche en trois volets, galerie, mise en
 * avant mobile, bande finale. Chaque page peut redefinir `--halo` pour que le
 * halo du heros prenne la couleur de la marque qu'elle presente.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<style>
  .rp{--gold:#d4b45c;--gold-hi:#f4d06f;--ink:#05050a;--panel:#0b0b12;--text:#eef1f6;--muted:#9aa3b4;
      --serif:"Playfair Display",Georgia,serif;--vert:#3f9d6d;
      background:var(--ink);color:var(--text);overflow-x:hidden}
  /* `overflow-x:hidden` fabrique un conteneur de defilement, ce qui ANNULE
     tout `position:sticky` a l'interieur — la demo de l'etude L.A ne tenait
     pas a l'ecran. `clip` coupe pareil sans creer ce conteneur. Le
     @supports garde le comportement d'avant la ou `clip` manque. */
  @supports (overflow-x: clip){ .rp{overflow-x:clip} }
  .rp *{box-sizing:border-box}
  .rp img{display:block;max-width:100%}
  .rp .wr{max-width:1180px;margin:0 auto;padding:0 26px}
  .rp .eb{font-size:.7rem;letter-spacing:.34em;text-transform:uppercase;color:var(--gold);font-weight:700}
  .rp h1,.rp h2,.rp h3{font-family:var(--serif);font-weight:500;line-height:1.05;margin:0}
  .rp em{font-style:italic;color:var(--gold-hi)}
  .rp p{margin:0;line-height:1.7;color:var(--muted)}
  .rp a{color:inherit}
  .rp .bt{display:inline-block;background:linear-gradient(120deg,var(--gold),var(--gold-hi));color:#1a1206;font-weight:800;
     text-decoration:none;border-radius:999px;padding:15px 32px;font-size:.96rem;
     box-shadow:0 18px 44px -18px rgba(212,180,92,.75);transition:transform .25s,box-shadow .25s}
  .rp .bt:hover{transform:translateY(-2px);box-shadow:0 24px 54px -20px rgba(212,180,92,.95)}
  .rp .bt--g{background:transparent;color:var(--text);border:1px solid rgba(255,255,255,.22);box-shadow:none;font-weight:600}
  .rp .bt--g:hover{border-color:var(--gold)}

  /* héros */
  .rp__hero{position:relative;padding:clamp(96px,15vh,170px) 0 clamp(40px,7vh,80px);text-align:center;overflow:hidden}
  .rp__halo{position:absolute;left:50%;top:34%;transform:translate(-50%,-50%);width:min(96vw,1200px);height:70vh;pointer-events:none;
     background:radial-gradient(closest-side,var(--halo,rgba(63,157,109,.18)),transparent 70%)}
  .rp__hero h1{font-size:clamp(2.6rem,7vw,5.4rem);letter-spacing:-.02em;margin:14px 0 18px}
  .rp__lead{max-width:60ch;margin:0 auto;font-size:clamp(1rem,2.1vw,1.14rem)}
  .rp__tags{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:26px 0 34px}
  .rp__tags span{border:1px solid rgba(212,180,92,.34);border-radius:999px;padding:8px 18px;font-size:.8rem;color:#cdd4e2}
  .rp__maq{position:relative;z-index:2;max-width:1080px;margin:0 auto;border-radius:20px;overflow:hidden;
     border:1px solid rgba(212,180,92,.22);box-shadow:0 70px 130px -60px rgba(0,0,0,.95)}
  .rp__cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-top:34px}

  /* fiche */
  .rp__fiche{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:26px;
     padding:clamp(56px,9vh,110px) 0 clamp(30px,5vh,60px)}
  .rp__fiche article{background:var(--panel);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:26px 24px 28px}
  .rp__fiche h3{font-size:1.3rem;margin:10px 0 12px}
  .rp__fiche p{font-size:.97rem}
  .rp__fiche ul{margin:12px 0 0;padding-left:18px;color:var(--muted);font-size:.95rem;line-height:1.75}
  .rp__fiche li::marker{color:var(--gold)}

  /* galerie */
  .rp__gal{padding:clamp(40px,7vh,80px) 0 clamp(60px,10vh,120px)}
  .rp__gal h2{font-size:clamp(1.8rem,4.6vw,3rem);text-align:center;margin-bottom:10px}
  .rp__gal>.wr>p{text-align:center;max-width:56ch;margin:0 auto 44px}
  .rp__shots{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:26px}
  .rp__shot{background:var(--panel);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;
     transition:border-color .35s,transform .35s}
  .rp__shot:hover{border-color:rgba(212,180,92,.5);transform:translateY(-4px)}
  .rp__shot img{width:100%;height:auto}
  .rp__shot div{padding:16px 18px 20px}
  .rp__shot b{display:block;font-size:1rem;margin-bottom:6px}
  .rp__shot span{color:var(--muted);font-size:.88rem;line-height:1.5}

  /* mobile mis en avant */
  .rp__mob{display:grid;grid-template-columns:1.1fr .9fr;gap:clamp(26px,5vw,70px);align-items:center;
     padding:clamp(50px,8vh,100px) 0;border-top:1px solid rgba(255,255,255,.07)}
  .rp__mob h2{font-size:clamp(1.7rem,4.2vw,2.8rem);margin-bottom:16px}
  .rp__phone{justify-self:center;width:min(74vw,300px);border:10px solid #0e0e12;border-radius:38px;overflow:hidden;
     box-shadow:0 60px 110px -50px rgba(0,0,0,.95),0 0 0 1px rgba(212,180,92,.25)}

  /* bande finale */
  .rp__fin{text-align:center;padding:clamp(60px,11vh,140px) 0;border-top:1px solid rgba(255,255,255,.07)}
  .rp__fin h2{font-size:clamp(1.9rem,5vw,3.4rem);margin-bottom:18px}
  .rp__fin p{max-width:50ch;margin:0 auto 30px}

  [data-r]{opacity:0;transform:translateY(28px);transition:opacity .8s cubic-bezier(.22,1,.3,1),transform .8s cubic-bezier(.22,1,.3,1)}
  [data-r].vu{opacity:1;transform:none}

  @media(max-width:960px){
    .rp__fiche{grid-template-columns:1fr;gap:18px;padding-top:44px}
    .rp__shots{grid-template-columns:1fr;gap:18px}
    .rp__mob{grid-template-columns:1fr;gap:28px;text-align:center}
    .rp__maq{border-radius:14px}
    .rp__hero{padding-top:clamp(84px,12vh,120px)}
  }
  @media(prefers-reduced-motion:reduce){[data-r]{opacity:1;transform:none;transition:none}}
</style>
