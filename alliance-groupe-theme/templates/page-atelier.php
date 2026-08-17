<?php
/**
 * Template Name: Atelier IA
 *
 * Page /atelier : hero à question + galerie des outils IA & services
 * (bloc réutilisable template-parts/atelier-gallery.php, aussi utilisé sous
 * le hero de l'accueil).
 *
 * @package Alliance_Groupe
 */

get_header();
?>
<style>
.ag-atlh{position:relative;text-align:center;padding:clamp(70px,12vw,140px) 20px clamp(20px,4vw,40px);background:#07070c;color:#eef1f6;overflow:hidden;}
.ag-atlh::before,.ag-atlh::after{content:"";position:absolute;border-radius:50%;filter:blur(80px);opacity:.55;z-index:0;pointer-events:none;animation:agAtlhFloat 14s ease-in-out infinite;}
.ag-atlh::before{width:38vw;height:38vw;left:-8vw;top:-6vw;background:radial-gradient(circle,#7a5cff55,transparent 70%);}
.ag-atlh::after{width:42vw;height:42vw;right:-10vw;top:2vw;background:radial-gradient(circle,#d4b45c55,transparent 70%);animation-delay:-6s;}
@keyframes agAtlhFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(0,26px) scale(1.08)}}
.ag-atlh__eyebrow{position:relative;z-index:1;display:inline-block;font-size:.8rem;letter-spacing:.22em;text-transform:uppercase;color:#d4b45c;border:1px solid rgba(212,180,92,.35);border-radius:100px;padding:6px 16px;margin-bottom:22px;}
.ag-atlh__title{position:relative;z-index:1;font-size:clamp(2rem,6vw,4.2rem);line-height:1.05;font-weight:800;margin:0 auto 18px;max-width:16ch;text-wrap:balance;background:linear-gradient(120deg,#fff 30%,#f4d06f);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.ag-atlh__sub{position:relative;z-index:1;color:#aab3c5;max-width:60ch;margin:0 auto;font-size:clamp(1rem,2.2vw,1.2rem);line-height:1.55;}
@media(prefers-reduced-motion:reduce){.ag-atlh::before,.ag-atlh::after{animation:none}}
</style>

<section class="ag-atlh">
	<span class="ag-atlh__eyebrow">Alliance Groupe · Web · Sécurité · IA</span>
	<h1 class="ag-atlh__title">Que créons-nous aujourd'hui&nbsp;?</h1>
	<p class="ag-atlh__sub">Ton atelier propulsé par l'IA : chiffre un projet, refais ton site, sécurise-le, crée du contenu — chaque brique s'ouvre en un clic.</p>
</section>

<?php get_template_part( 'template-parts/atelier-gallery', null, array( 'heading' => '' ) ); ?>

<?php get_footer(); ?>
