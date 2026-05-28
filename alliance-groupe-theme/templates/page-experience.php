<?php
/**
 * Template Name: Expérience immersive (style theirisk.com)
 *
 * "Le Voyage Alliance" — parcours immersif en 4 stations, modèles 3D
 * réels (.glb) chargés en lazy via GLTFLoader (modules ES) :
 *   0. Bureau   — macbook_pro_2021.glb (+ vidéo Naples en fond)
 *   1. Vésuve   — mt._vesuvius_italy.glb (lourd : lazy + spinner)
 *   2. Marrakech— marrakech-tower.glb + moroccan_street_light.glb
 *   3. Espace   — need_some_space.glb + menu de pages cliquables
 *
 * @package Alliance_Groupe_Theme
 */

get_header();
$base   = get_stylesheet_directory_uri() . '/assets/images/img_3d/';
$imgb   = get_stylesheet_directory_uri() . '/assets/images/';
$poster = get_stylesheet_directory_uri() . '/assets/images/cities/naples-1.jpg'; // Naples (pas le bureau) : visible avant le chargement de la vidéo
$logo   = get_stylesheet_directory_uri() . '/assets/images/logo.png';            // tête de lion, au centre de la constellation
$vid    = get_stylesheet_directory_uri() . '/assets/images/video/naples.mp4';
$music  = get_option( 'ag_xp_music', get_stylesheet_directory_uri() . '/assets/images/son/Napoli_con_bizonnio2.m4a' );

// Constellation de la station "Univers" : chaque étoile ouvre un panneau de
// cartes flottantes (offres) au lieu d'un menu basique.
// x / y = position en % dans la zone ; les liens utilisent les vrais slugs.
$orbs = array(
	array(
		'label' => 'Sites & Offres', 'x' => 16, 'y' => 30,
		'sub'   => array(
			array( 'l' => 'Sites Express',       'u' => home_url( '/sites-express' ),       'd' => 'Site pro livré en 7 jours, prix fixe.', 'ico' => 'rocket' ),
			array( 'l' => 'Templates WordPress', 'u' => home_url( '/templates-wordpress' ), 'd' => '6 thèmes métier prêts à installer.',     'ico' => 'layout' ),
			array( 'l' => 'Sur-mesure',          'u' => home_url( '/sur-mesure' ),          'd' => 'Projet ambitieux, design unique.',       'ico' => 'gem' ),
		),
	),
	array(
		'label' => 'Gagner', 'x' => 50, 'y' => 9,
		'sub'   => array(
			array( 'l' => 'Programme ambassadeurs', 'u' => home_url( '/programme-ambassadeur' ), 'd' => 'Gagnez 10 % sur chaque vente.',     'ico' => 'coins' ),
			array( 'l' => 'Devenir recruteur',      'u' => home_url( '/recruteur' ),             'd' => 'Recrutez et touchez des primes.',   'ico' => 'megaphone' ),
			array( 'l' => 'Classement',             'u' => home_url( '/classement' ),            'd' => 'Grimpez dans le top des vendeurs.', 'ico' => 'trophy' ),
		),
	),
	array(
		'label' => 'Cadeaux', 'x' => 84, 'y' => 30,
		'sub'   => array(
			array( 'l' => 'Audit SEO offert',      'u' => home_url( '/audit-seo' ),           'd' => '12 points analysés, note /100.', 'ico' => 'search' ),
			array( 'l' => '1 site gratuit / mois', 'u' => home_url( '/tirage-au-sort' ),      'd' => 'Tirage au sort chaque mois.',    'ico' => 'gift' ),
			array( 'l' => 'Templates gratuits',    'u' => home_url( '/templates-wordpress' ), 'd' => 'Téléchargement immédiat.',       'ico' => 'package' ),
		),
	),
	array(
		'label' => 'Solidaire', 'x' => 29, 'y' => 76,
		'sub'   => array(
			array( 'l' => 'Programme Racines', 'u' => home_url( '/programme-racines' ),    'd' => 'On reverse à des causes qui comptent.', 'ico' => 'sprout' ),
			array( 'l' => 'Site asso gratuit', 'u' => home_url( '/wordpress-association' ), 'd' => 'Un site offert aux associations.',      'ico' => 'heart' ),
		),
	),
	array(
		'label' => 'Studio & Contact', 'x' => 71, 'y' => 76,
		'sub'   => array(
			array( 'l' => 'Studio créatif', 'u' => home_url( '/studio' ),        'd' => 'Créez vos visuels & vidéos.',  'ico' => 'film' ),
			array( 'l' => 'Nous contacter', 'u' => home_url( '/contact' ),       'd' => 'Parlons de votre projet.',     'ico' => 'mail' ),
			array( 'l' => 'Espace client',  'u' => home_url( '/espace-client' ), 'd' => 'Suivez vos projets en cours.', 'ico' => 'user' ),
		),
	),
);
// Paires d'orbes reliées par une ligne (index 0-based) pour dessiner la constellation.
$orb_links = array( array(0,1), array(1,2), array(0,3), array(3,4), array(4,2), array(1,4) );

// Icônes SVG dorées (remplacent les émojis sur les cartes). Trait = couleur du conteneur.
if ( ! function_exists( 'agx_icon' ) ) {
	function agx_icon( $key ) {
		$p = array(
			'rocket'    => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09Z"/><path d="M12 15 9 12a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2Z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
			'layout'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>',
			'gem'       => '<path d="M6 3h12l4 6-10 12L2 9Z"/><path d="M2 9h20"/><path d="m8 3 4 18 4-18"/>',
			'coins'     => '<circle cx="12" cy="12" r="9"/><path d="M15.5 8.5a4 4 0 1 0 0 7"/><path d="M7.5 11h5.5"/><path d="M7.5 13.5h4.5"/>',
			'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
			'trophy'    => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.7V17c0 .6-.5 1-1 1.2C7.8 18.8 7 20.2 7 22"/><path d="M14 14.7V17c0 .6.5 1 1 1.2 1.2.6 2 2 2 3.8"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
			'search'    => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
			'gift'      => '<path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7Z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7Z"/>',
			'package'   => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
			'sprout'    => '<path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8Z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2Z"/>',
			'heart'     => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
			'film'      => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 3v18"/><path d="M17 3v18"/><path d="M3 7.5h4"/><path d="M3 12h18"/><path d="M3 16.5h4"/><path d="M17 7.5h4"/><path d="M17 16.5h4"/>',
			'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>',
			'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
		);
		$svg = isset( $p[ $key ] ) ? $p[ $key ] : '<circle cx="12" cy="12" r="9"/>';
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $svg . '</svg>';
	}
}
?>

<style>
body.page-template-page-experience{background:#05060a}
body.page-template-page-experience .ag-nav,
body.page-template-page-experience footer,
body.page-template-page-experience .ag-fsm-toggle{display:none!important}

.agx{position:fixed;inset:0;width:100vw;height:100vh;overflow:hidden;z-index:50;background:#05060a;color:#fff;font-family:'Helvetica Neue',Arial,sans-serif;touch-action:pan-y}
.agx__media{position:absolute;inset:0;z-index:0}
.agx__media video,.agx__media img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity 1.1s ease}
.agx__media img{transform:scale(1.08);filter:saturate(1.08) contrast(1.04)}
.agx__media img.is-on{animation:agx-kenburns 26s ease-in-out infinite alternate}
@keyframes agx-kenburns{from{transform:scale(1.06) translate(-1%,1%)}to{transform:scale(1.18) translate(2%,-2%)}}
.agx__media .is-on{opacity:1}
/* Ciel étoilé plein écran (station Univers) : on est "dans les étoiles" */
.agx__sky{position:absolute;inset:0;z-index:1;opacity:0;transition:opacity 1.2s ease;pointer-events:none}
.agx.is-menu .agx__sky{opacity:1}
/* Galaxie Sketchfab embarquée (station Univers) : décor qui tourne tout seul, derrière la constellation */
.agx__sky3d{position:absolute;inset:0;width:100%;height:100%;border:0;z-index:1;opacity:0;visibility:hidden;transition:opacity 1.4s ease;pointer-events:none;background:#05060a}
.agx.is-menu .agx__sky3d{opacity:1;visibility:visible}
.agx__sky::before,.agx__sky::after{content:'';position:absolute;inset:-50%;background-repeat:repeat;background-image:
	radial-gradient(1.5px 1.5px at 12% 22%,#fff,transparent),radial-gradient(1.5px 1.5px at 28% 64%,#fff,transparent),
	radial-gradient(2px 2px at 44% 33%,#fff,transparent),radial-gradient(1.5px 1.5px at 61% 78%,#fff,transparent),
	radial-gradient(1.5px 1.5px at 73% 18%,#fff,transparent),radial-gradient(2px 2px at 87% 52%,#fff,transparent),
	radial-gradient(1.5px 1.5px at 19% 88%,#FCEFC4,transparent),radial-gradient(1.5px 1.5px at 53% 7%,#fff,transparent),
	radial-gradient(2px 2px at 92% 84%,#fff,transparent),radial-gradient(1.5px 1.5px at 36% 50%,#fff,transparent);
	background-size:340px 340px;animation:agx-drift 90s linear infinite,agx-tw 4s ease-in-out infinite}
.agx__sky::after{background-size:520px 520px;opacity:.6;animation-duration:140s,6s;animation-direction:reverse,normal}
@keyframes agx-drift{from{transform:translate(0,0)}to{transform:translate(-340px,-340px)}}
@keyframes agx-tw{0%,100%{opacity:1}50%{opacity:.55}}
.agx__veil{position:absolute;inset:0;z-index:1;background:radial-gradient(ellipse 80% 75% at 50% 45%,transparent 0%,transparent 45%,rgba(5,6,10,.55) 82%,rgba(3,4,8,.95) 100%),linear-gradient(180deg,rgba(5,6,10,.35) 0%,transparent 30%,transparent 62%,rgba(4,4,8,.85) 100%)}
.agx__canvas{position:absolute;inset:0;width:100%;height:100%;z-index:2}

.agx__cap{position:absolute;left:0;right:0;top:10vh;text-align:center;padding:0 24px;z-index:5;pointer-events:none;transition:opacity .55s ease,transform .55s cubic-bezier(.22,1,.36,1)}
.agx__cap.is-out{opacity:0;transform:translateY(-26px)}
.agx__cap .pre,.agx__cap .ttl,.agx__cap .line{opacity:0;transform:translateY(22px);animation:agx-rise .7s cubic-bezier(.22,1,.36,1) forwards}
.agx__cap .ttl{animation-delay:.08s}.agx__cap .line{animation-delay:.16s}
@keyframes agx-rise{to{opacity:1;transform:translateY(0)}}
.agx__cap .pre{font-size:clamp(.7rem,1.2vw,.9rem);letter-spacing:6px;color:#D4B45C;text-shadow:0 2px 14px #000;margin-bottom:12px}
.agx__cap .ttl{font-family:Georgia,serif;font-size:clamp(2rem,6.5vw,4.6rem);line-height:1.05;margin:0;text-shadow:0 4px 40px rgba(0,0,0,.95);background:linear-gradient(180deg,#fff,#e8dcc0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.agx__cap .line{margin:14px auto 0;max-width:560px;font-family:Georgia,serif;font-style:italic;font-size:clamp(.95rem,1.5vw,1.2rem);color:rgba(255,255,255,.85);text-shadow:0 2px 16px #000}

/* Station Univers : on réduit le gros titre pour laisser la place à la constellation */
.agx.is-menu .agx__cap{top:5vh}
.agx.is-menu .agx__cap .ttl{font-size:clamp(1.5rem,4.4vw,2.8rem)}
.agx.is-menu .agx__cap .line{margin-top:8px;font-size:clamp(.85rem,1.4vw,1.05rem)}

/* Constellation (station Univers) : confinée sous le titre, au-dessus de la nav */
.agx__constel{position:absolute;left:0;right:0;top:26vh;bottom:13vh;z-index:6;opacity:0;visibility:hidden;transition:opacity .9s ease;pointer-events:none}
.agx__constel.is-on{opacity:1;visibility:visible;pointer-events:auto}
.agx__lines{position:absolute;inset:0;width:100%;height:100%}
.agx__lines line{stroke:rgba(212,180,92,.3);stroke-width:.18;stroke-dasharray:1.4 1.4;animation:agx-dash 18s linear infinite}
@keyframes agx-dash{to{stroke-dashoffset:-40}}
.agx__core{position:absolute;left:50%;top:48%;transform:translate(-50%,-50%);text-align:center;display:flex;flex-direction:column;font-family:Georgia,serif;line-height:1.02;pointer-events:none}
.agx__core span:first-child{font-size:clamp(1.3rem,3.1vw,2.1rem);color:#fff;text-shadow:0 2px 24px #000}
.agx__core span:last-child{font-size:clamp(.72rem,1.6vw,1rem);letter-spacing:6px;text-transform:uppercase;color:#D4B45C}
/* Tête de lion (logo) au centre de la galaxie */
.agx__logo{position:absolute;left:50%;top:47%;transform:translate(-50%,-50%);width:clamp(96px,11vw,168px);height:auto;z-index:3;pointer-events:none;filter:drop-shadow(0 0 26px rgba(212,180,92,.6)) drop-shadow(0 8px 22px rgba(0,0,0,.75));animation:agx-logo-glow 5s ease-in-out infinite}
@keyframes agx-logo-glow{0%,100%{transform:translate(-50%,-50%) scale(1);filter:drop-shadow(0 0 22px rgba(212,180,92,.5)) drop-shadow(0 8px 22px rgba(0,0,0,.75))}50%{transform:translate(-50%,-50%) scale(1.06);filter:drop-shadow(0 0 46px rgba(212,180,92,.88)) drop-shadow(0 8px 22px rgba(0,0,0,.75))}}
.agx__orb{position:absolute;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;gap:14px}

/* Vraie étoile dorée : cœur lumineux + halo + branches de diffraction qui scintillent */
.agx__orb-dot{position:relative;width:13px;height:13px;border:0;border-radius:50%;cursor:pointer;background:radial-gradient(circle at 50% 50%,#fff 0%,#FCEFC4 32%,#F3D27A 58%,#D4B45C 82%,rgba(212,180,92,0) 100%);box-shadow:0 0 10px 3px rgba(252,239,196,.9),0 0 26px 8px rgba(212,180,92,.5),0 0 60px 16px rgba(212,180,92,.22);transition:transform .25s ease;animation:agx-twinkle 3.6s ease-in-out infinite}
.agx__orb-dot::before,.agx__orb-dot::after{content:'';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);pointer-events:none}
.agx__orb-dot::before{width:2px;height:52px;background:linear-gradient(rgba(255,240,205,0),rgba(255,240,205,.95),rgba(255,240,205,0))}
.agx__orb-dot::after{width:52px;height:2px;background:linear-gradient(90deg,rgba(255,240,205,0),rgba(255,240,205,.95),rgba(255,240,205,0))}
@keyframes agx-twinkle{0%,100%{box-shadow:0 0 8px 2px rgba(252,239,196,.75),0 0 20px 6px rgba(212,180,92,.4),0 0 48px 12px rgba(212,180,92,.18)}50%{box-shadow:0 0 15px 4px rgba(252,239,196,1),0 0 36px 12px rgba(212,180,92,.7),0 0 74px 22px rgba(212,180,92,.3)}}
.agx__orb-dot:hover{transform:translate(0,0) scale(1.35)}
.agx__orb.is-open .agx__orb-dot{transform:scale(1.4);background:radial-gradient(circle at 50% 50%,#fff 0%,#FFE3B0 35%,#F37A1F 75%,rgba(243,122,31,0) 100%);box-shadow:0 0 16px 5px rgba(255,225,170,1),0 0 44px 16px rgba(243,122,31,.7)}
.agx__orb-label{font-family:Georgia,serif;font-size:clamp(.82rem,1.5vw,1.05rem);color:#fff;text-shadow:0 2px 12px #000,0 0 20px rgba(0,0,0,.8);white-space:nowrap;letter-spacing:.5px;transition:color .25s}
.agx__orb.is-open .agx__orb-label{color:#F3D27A}

/* "Dans l'étoile" : cartes d'offres flottant dans l'espace 3D */
.agx__detail{position:absolute;inset:0;z-index:9;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;padding:9vh 24px 13vh;opacity:0;visibility:hidden;transform:scale(1.18);transition:opacity .6s ease,transform .6s cubic-bezier(.22,1,.36,1);perspective:1300px;pointer-events:none}
.agx__detail.is-on{opacity:1;visibility:visible;transform:scale(1);pointer-events:auto}
.agx__detail-back{position:absolute;top:22px;left:22px;z-index:3;background:rgba(10,10,15,.5);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);color:#D4B45C;border-radius:999px;padding:10px 18px;font-size:.78rem;letter-spacing:1px;cursor:pointer;transition:.25s}
.agx__detail-back:hover{background:#D4B45C;color:#0a0a0f}
.agx__detail-title{font-family:Georgia,serif;font-size:clamp(1.6rem,4vw,2.8rem);margin:0;text-align:center;background:linear-gradient(180deg,#fff,#e8dcc0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;text-shadow:0 4px 30px rgba(0,0,0,.8)}
.agx__cards{display:flex;gap:26px;justify-content:center;align-items:stretch;flex-wrap:wrap;transform-style:preserve-3d;max-width:1000px}
.agx__card{width:262px;max-width:80vw;display:flex;flex-direction:column;background:rgba(12,14,22,.55);backdrop-filter:blur(14px);border:1px solid rgba(212,180,92,.32);border-radius:18px;overflow:hidden;text-decoration:none;color:#fff;box-shadow:0 30px 70px rgba(0,0,0,.55);transition:transform .35s ease,border-color .35s ease,box-shadow .35s ease;animation:agx-float 6s ease-in-out infinite;animation-delay:calc(var(--i) * -2s);transform:translateZ(0)}
.agx__detail.is-on .agx__card{animation:agx-card-in .7s cubic-bezier(.22,1,.36,1) backwards,agx-float 6s ease-in-out infinite;animation-delay:calc(var(--i) * .12s),calc(var(--i) * -2s + .7s)}
@keyframes agx-card-in{from{opacity:0;transform:translateY(40px) scale(.8)}to{opacity:1;transform:none}}
@keyframes agx-float{0%,100%{transform:translateY(-7px)}50%{transform:translateY(9px)}}
.agx__card:hover{transform:translateY(-6px) scale(1.04);border-color:#D4B45C;box-shadow:0 40px 90px rgba(0,0,0,.7),0 0 40px rgba(212,180,92,.25);color:#fff;text-decoration:none}
.agx__card-icon{display:flex;align-items:center;justify-content:center;height:118px;line-height:1;color:#F3D27A;background:linear-gradient(145deg,rgba(212,180,92,.22),rgba(243,122,31,.10) 45%,rgba(12,14,22,.55));border-bottom:1px solid rgba(212,180,92,.28)}
.agx__card-icon svg{width:54px;height:54px;display:block;filter:drop-shadow(0 4px 14px rgba(243,122,31,.4))}
.agx__card-body{display:flex;flex-direction:column;gap:7px;padding:18px}
.agx__card-ttl{font-family:Georgia,serif;font-size:1.18rem;color:#fff}
.agx__card-desc{font-size:.9rem;line-height:1.5;color:rgba(255,255,255,.72)}
.agx__card-cta{margin-top:6px;color:#D4B45C;font-weight:800;letter-spacing:1px;text-transform:uppercase;font-size:.78rem}
@media (max-width:720px){
	.agx__orb-label{font-size:.72rem}
	.agx__cards{gap:16px}
	.agx__card{width:84vw;flex-direction:row}
	.agx__card-icon{height:auto;width:34%;flex-shrink:0;padding:18px 0}
	.agx__card-icon svg{width:42px;height:42px}
	.agx__card-body{flex:1}
}

.agx__enter{position:absolute;bottom:15vh;left:50%;transform:translateX(-50%);z-index:6;display:inline-flex;align-items:center;gap:10px;padding:16px 40px;border:1px solid rgba(212,180,92,.7);border-radius:999px;background:rgba(10,10,15,.4);backdrop-filter:blur(8px);color:#D4B45C;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:.85rem;text-decoration:none;cursor:pointer;transition:.3s}
.agx__enter:hover{background:#D4B45C;color:#0a0a0f;text-decoration:none;transform:translateX(-50%) translateY(-3px)}
.agx__enter.is-hidden{opacity:0;pointer-events:none}

.agx__nav{position:absolute;bottom:38px;left:0;right:0;display:flex;justify-content:center;align-items:center;gap:18px;z-index:7;font-size:.8rem;letter-spacing:2px;font-weight:700}
.agx__nav button{display:inline-flex;align-items:center;gap:8px;background:rgba(212,180,92,.12);border:1.5px solid rgba(212,180,92,.65);color:#F3D27A;font:inherit;letter-spacing:inherit;cursor:pointer;padding:13px 26px;border-radius:999px;backdrop-filter:blur(8px);transition:background .25s,color .25s,border-color .25s,transform .2s;box-shadow:0 8px 30px rgba(0,0,0,.4)}
.agx__nav button:hover:not(:disabled){background:#D4B45C;color:#0a0a0f;border-color:#D4B45C;transform:translateY(-2px)}
.agx__nav button:disabled{opacity:.28;cursor:not-allowed}
.agx__nav .ct{color:rgba(255,255,255,.75);font-variant-numeric:tabular-nums;letter-spacing:3px;padding:0 4px}
.agx__sound{position:absolute;top:20px;right:20px;z-index:8;width:46px;height:46px;display:flex;align-items:center;justify-content:center;background:rgba(10,10,15,.5);backdrop-filter:blur(8px);border:1.5px solid rgba(212,180,92,.5);color:#D4B45C;border-radius:50%;cursor:pointer;transition:.25s}
.agx__sound:hover{color:#fff;border-color:#D4B45C;background:rgba(212,180,92,.2)}
.agx__sound svg{width:22px;height:22px;display:block}
.agx__sound .wave{transition:opacity .2s}
.agx__sound.is-off .wave{opacity:.25}
.agx__sound.is-off .cross{opacity:1}
.agx__sound .cross{opacity:0}

.agx__loader{position:absolute;inset:0;z-index:9;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;background:rgba(5,6,10,.55);opacity:0;visibility:hidden;transition:opacity .3s ease}
.agx__loader.is-on{opacity:1;visibility:visible}
.agx__spin{width:46px;height:46px;border:3px solid rgba(212,180,92,.25);border-top-color:#D4B45C;border-radius:50%;animation:agx-spin 1s linear infinite}
@keyframes agx-spin{to{transform:rotate(360deg)}}
.agx__loader p{color:rgba(255,255,255,.7);letter-spacing:2px;font-size:.8rem}
.agx__hint{position:absolute;bottom:8vh;left:0;right:0;text-align:center;font-size:.7rem;letter-spacing:3px;color:rgba(255,255,255,.4);text-transform:uppercase;pointer-events:none;text-shadow:0 1px 10px #000;z-index:6}
.agx__warp{position:absolute;inset:0;z-index:10;pointer-events:none;opacity:0;background:radial-gradient(circle at 50% 50%,rgba(255,245,225,.95) 0%,rgba(212,180,92,.7) 30%,rgba(243,122,31,.35) 55%,rgba(5,6,10,0) 78%);transition:opacity .5s ease}
.agx__warp.is-on{opacity:1}
/* Quand on est "dans l'étoile", on masque la constellation + l'UI de navigation */
.agx.is-detail .agx__constel,.agx.is-detail .agx__cap,.agx.is-detail .agx__nav,.agx.is-detail .agx__hint{opacity:0!important;pointer-events:none!important;transition:opacity .4s ease}
.agx__orb.is-diving .agx__orb-dot{animation:none;transform:scale(7);opacity:0;transition:transform .45s ease,opacity .45s ease}
.agx__orb.is-diving .agx__orb-label{opacity:0;transition:opacity .25s ease}
</style>

<main class="agx" id="agx">
	<div class="agx__media" id="agx-media">
		<video id="agx-video" class="is-on" muted loop playsinline autoplay preload="auto" poster="<?php echo esc_url( $poster ); ?>">
			<source src="<?php echo esc_url( $vid ); ?>" type="video/mp4">
		</video>
		<img id="agx-img" src="<?php echo esc_url( $poster ); ?>" alt="">
	</div>
	<div class="agx__veil"></div>
	<div class="agx__sky" id="agx-sky" aria-hidden="true"></div>
	<iframe class="agx__sky3d" id="agx-sky3d" title="Galaxie Alliance" frameborder="0" allow="autoplay; fullscreen; xr-spatial-tracking" allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true" loading="lazy" aria-hidden="true"></iframe>
	<canvas class="agx__canvas" id="agx-canvas"></canvas>

	<div class="agx__cap" id="agx-cap">
		<div class="pre" id="agx-pre">BIENVENUE CHEZ —</div>
		<h1 class="ttl" id="agx-ttl">Alliance Groupe</h1>
		<div class="line" id="agx-line">Agence web &amp; IA · Nantes · Naples · Marrakech.</div>
	</div>

	<div class="agx__constel" id="agx-menu">
		<?php foreach ( $orbs as $idx => $o ) : ?>
			<div class="agx__orb" style="left:<?php echo (int) $o['x']; ?>%;top:<?php echo (int) $o['y']; ?>%" data-orb="<?php echo (int) $idx; ?>">
				<button class="agx__orb-dot" type="button" aria-label="<?php echo esc_attr( $o['label'] ); ?>"></button>
				<span class="agx__orb-label"><?php echo esc_html( $o['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Panneaux "dans l'étoile" : cartes d'offres flottantes en 3D -->
	<?php foreach ( $orbs as $idx => $o ) : ?>
		<div class="agx__detail" data-detail="<?php echo (int) $idx; ?>">
			<button class="agx__detail-back" type="button">← Retour aux étoiles</button>
			<h2 class="agx__detail-title"><?php echo esc_html( $o['label'] ); ?></h2>
			<div class="agx__cards">
				<?php foreach ( $o['sub'] as $n => $s ) : ?>
					<a class="agx__card" href="<?php echo esc_url( $s['u'] ); ?>" style="--i:<?php echo (int) $n; ?>">
						<span class="agx__card-icon" aria-hidden="true"><?php echo agx_icon( $s['ico'] ); // SVG de confiance (pas de saisie utilisateur) ?></span>
						<span class="agx__card-body">
							<span class="agx__card-ttl"><?php echo esc_html( $s['l'] ); ?></span>
							<span class="agx__card-desc"><?php echo esc_html( $s['d'] ); ?></span>
							<span class="agx__card-cta">Découvrir →</span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>

	<a href="#" class="agx__enter" id="agx-enter">Commencer le voyage →</a>

	<div class="agx__warp" id="agx-warp"></div>
	<div class="agx__loader" id="agx-loader"><div class="agx__spin"></div><p>Chargement du modèle 3D…</p></div>

	<div class="agx__nav">
		<button id="agx-back">◂ BACK</button>
		<span class="ct"><span id="agx-cur">01</span> / 04</span>
		<button id="agx-next">NEXT ▸</button>
	</div>
	<button class="agx__sound is-off" id="agx-sound" type="button" aria-label="Activer le son">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<path d="M4 9 L8 9 L13 5 L13 19 L8 15 L4 15 Z" fill="currentColor" stroke="none"/>
			<path class="wave" d="M16 8.8a4.5 4.5 0 0 1 0 6.4"/>
			<path class="wave" d="M18.6 6.2a8 8 0 0 1 0 11.6"/>
			<path class="cross" d="M17.5 9.5l5 5M22.5 9.5l-5 5"/>
		</svg>
	</button>
	<audio id="agx-audio" loop preload="none" src="<?php echo esc_url( $music ); ?>"></audio>
	<div class="agx__hint" id="agx-hint">← Glissez ou NEXT →</div>
</main>

<script type="importmap">
{
  "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
  }
}
</script>

<script type="module">
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

const BASE = '<?php echo esc_js( $base ); ?>';
const STATIONS = [
	{ pre:'✦ À VOUS DE JOUER', ttl:'L’Univers Alliance', line:'Touchez une étoile pour explorer.', media:'space', menu:true, iframe:'https://sketchfab.com/models/d6521362b37b48e3a82bce4911409303/embed?autospin=0.2&autostart=1&preload=1&ui_theme=dark&ui_help=0&ui_infos=0&ui_controls=0&ui_stop=0&ui_inspector=0&ui_ar=0&ui_vr=0&ui_fullscreen=0&ui_annotations=0&ui_watermark=0&dnt=1&scrollwheel=0' },
	{ pre:'BIENVENUE CHEZ —', ttl:'Alliance Groupe', line:"C'est ici que votre projet prend vie.", model:'macbook_pro_2021.glb', media:'video', size:4.6, front:true, rotY:0.6, baseY:-0.6 },
	{ pre:'✦ NOTRE ÉNERGIE', ttl:'Le Vésuve', line:'La force napolitaine qui ne s’éteint jamais.', model:'mt._vesuvius_italy.glb', media:'photo', bg:'<?php echo esc_js( $imgb . 'cities/naples-1.jpg' ); ?>' },
	{ pre:'✦ NOTRE PÔLE SUD', ttl:'Marrakech', line:'SEO, IA, création — notre studio au soleil.', model:'marrakech-tower.glb', media:'photo', bg:'<?php echo esc_js( $imgb . 'cities/marrakech-1.jpg' ); ?>', baseY:-2.2 }
];

const host = document.getElementById('agx');
const canvas = document.getElementById('agx-canvas');
const elPre = document.getElementById('agx-pre'), elTtl = document.getElementById('agx-ttl'), elLine = document.getElementById('agx-line');
const elCap = document.getElementById('agx-cap'), elMenu = document.getElementById('agx-menu'), elEnter = document.getElementById('agx-enter');
const elLoader = document.getElementById('agx-loader'), elCur = document.getElementById('agx-cur'), elWarp = document.getElementById('agx-warp');
const bN = document.getElementById('agx-next'), bB = document.getElementById('agx-back'), elHint = document.getElementById('agx-hint');
const elVideo = document.getElementById('agx-video'), elImg = document.getElementById('agx-img');
const elSound = document.getElementById('agx-sound'), audio = document.getElementById('agx-audio');
const elSky3d = document.getElementById('agx-sky3d');

let cur = 0, current3D = null, busy = false;
const cache = {};
const W = () => host.clientWidth, H = () => host.clientHeight;
const wait = ms => new Promise(r => setTimeout(r, ms));
const ease = k => k < .5 ? 2*k*k : 1 - Math.pow(-2*k+2, 2)/2;
function tween(dur, onUpdate, onDone){
	const s = performance.now();
	(function f(t){ const k = Math.min(1, (t-s)/dur); onUpdate(ease(k)); if (k < 1) requestAnimationFrame(f); else onDone && onDone(); })(performance.now());
}
function replayCap(){ [elPre, elTtl, elLine].forEach(el => { el.style.animation = 'none'; void el.offsetWidth; el.style.animation = ''; }); }

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.6));
renderer.setSize(W(), H(), false);
renderer.outputColorSpace = THREE.SRGBColorSpace;
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(45, W()/H(), 0.1, 1000);
camera.position.set(0, 0, 15);

const pmrem = new THREE.PMREMGenerator(renderer);
scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
scene.add(new THREE.AmbientLight(0xffffff, 0.5));
const key = new THREE.DirectionalLight(0xfff0d8, 2.2); key.position.set(6, 9, 8); scene.add(key);
const warm = new THREE.PointLight(0xf37a1f, 60, 80); warm.position.set(-9, 2, 6); scene.add(warm);

const gltf = new GLTFLoader();
const draco = new DRACOLoader();
draco.setDecoderPath('https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/libs/draco/');
gltf.setDRACOLoader(draco);

const loadOne = url => new Promise(res => gltf.load(url, g => res(g.scene), undefined, err => { console.warn('[XP] échec du modèle', url, err); res(null); }));
// Essaie plusieurs modèles dans l'ordre (1er trouvé gagne) : permet d'ajouter
// volcano.glb plus tard sans rien casser (fallback sur la carte actuelle).
async function loadFirst(models){
	const list = Array.isArray(models) ? models : [models];
	for (const m of list){ const g = await loadOne(BASE + m); if (g) return g; }
	return null;
}

// Charge une station : centre + cadre n'importe quel modèle (terrain plat incliné,
// nuage de points = étoiles visibles), peu importe sa taille d'origine.
async function loadStation(i){
	if (cache[i]) return cache[i];
	const st = STATIONS[i];
	elLoader.classList.add('is-on');
	const centered = new THREE.Group();
	const main = await loadFirst(st.model);
	if (main) centered.add(main);
	if (st.extra){ const ex = await loadOne(BASE + st.extra); if (ex){ ex.scale.setScalar(0.5); ex.position.set(2.6, -2.2, 1.2); centered.add(ex); } }

	let isPoints = false;
	centered.traverse(o => {
		if (o.isPoints){ isPoints = true; o.material.size = 2.8; o.material.sizeAttenuation = false; o.material.vertexColors = true; o.material.transparent = true; o.material.opacity = 0.95; o.material.depthWrite = false; o.material.blending = THREE.AdditiveBlending; o.material.needsUpdate = true; }
		else if (o.isMesh && o.material){ o.material.side = THREE.DoubleSide; }
	});

	const inner = new THREE.Group(); inner.add(centered);
	const outer = new THREE.Group(); outer.add(inner);
	let box = new THREE.Box3().setFromObject(centered);
	if (!box.isEmpty()){
		let size = box.getSize(new THREE.Vector3());
		const maxHoriz = Math.max(size.x, size.z);
		const flat = !isPoints && size.y < 0.32 * maxHoriz;      // carte de terrain (Vésuve)
		if (flat){
			centered.scale.y = 1.4;                              // accentue le relief du volcan (carte aplatie)
			box = new THREE.Box3().setFromObject(centered);      // recalcul après exagération
			size = box.getSize(new THREE.Vector3());
		}
		const sph = box.getBoundingSphere(new THREE.Sphere());
		centered.position.sub(sph.center);                       // recentre l'objet à l'origine
		const target = st.size || (isPoints ? 22 : (flat ? 8.8 : 6.4));
		inner.scale.setScalar(target / (sph.radius || 1));
		if (flat){ outer.userData.flat = true; } // carte posée à plat : rotation sur place + inclinaison fixe gérées dans la boucle
		if (isPoints) outer.position.set(0, -0.5, -1);           // galaxie qui remplit l'écran
	}
	outer.userData.points = isPoints;
	outer.userData.spinNode = inner;                             // nœud qui tourne sur lui-même (carte à plat / galaxie de face)
	outer.userData.drag = !!st.drag;
	outer.userData.spin = !!st.spin;
	outer.userData.front = !!st.front;
	outer.userData.rotY = st.rotY || 0;
	outer.userData.baseY = st.baseY || 0;
	outer.userData.dragX = 0; outer.userData.dragY = 0;
	cache[i] = outer; elLoader.classList.remove('is-on'); return outer;
}

function setMedia(st){
	const mode = st.media;
	if (mode === 'video'){ elImg.classList.remove('is-on'); elVideo.classList.add('is-on'); elVideo.play().catch(()=>{}); }
	else if (mode === 'photo' && st.bg){ elVideo.classList.remove('is-on'); if (elImg.getAttribute('src') !== st.bg) elImg.src = st.bg; elImg.classList.add('is-on'); }
	else { elVideo.classList.remove('is-on'); elImg.classList.remove('is-on'); } // 'space' : scène sombre étoilée
}

async function go(i, instant){
	if (busy) return;
	i = Math.max(0, Math.min(STATIONS.length-1, i));
	busy = true;
	const st = STATIONS[i];
	if (!instant){ elCap.classList.add('is-out'); elWarp.classList.add('is-on'); await wait(380); }
	cur = i;
	elPre.textContent = st.pre; elTtl.textContent = st.ttl; elLine.textContent = st.line;
	elCur.textContent = String(i+1).padStart(2,'0');
	bB.disabled = (i===0); bN.disabled = (i===STATIONS.length-1);
	elEnter.classList.toggle('is-hidden', i!==0);
	elMenu.classList.toggle('is-on', !!st.menu);
	host.classList.toggle('is-menu', !!st.menu);
	host.style.cursor = st.drag ? 'grab' : 'default';
	elHint.textContent = st.drag ? '↔ Faites glisser pour tourner' : '← Glissez ou NEXT →';
	if (!st.menu) closeOrbs();
	elHint.style.opacity = (i===STATIONS.length-1) ? '0' : '';
	setMedia(st);
	if (st.iframe && !elSky3d.getAttribute('src')) elSky3d.setAttribute('src', st.iframe); // galaxie Sketchfab chargée à la 1re visite (la visibilité suit la classe is-menu)
	if (current3D){ scene.remove(current3D); current3D = null; }
	if (!st.iframe){
		const grp = await loadStation(i);
		if (cur === i){
			current3D = grp; grp.scale.setScalar(0.55); scene.add(grp);
			tween(720, k => grp.scale.setScalar(0.55 + 0.45*k), () => grp.scale.setScalar(1));
		}
	}
	elCap.classList.remove('is-out'); replayCap();
	elWarp.classList.remove('is-on');
	busy = false;
}

function plunge(){
	if (busy) return;
	busy = true;
	const z0 = camera.position.z, y0 = camera.position.y;
	elHint.style.opacity = '0'; elEnter.classList.add('is-hidden'); elCap.classList.add('is-out');
	tween(950, k => {
		camera.position.z = z0 + (2.4 - z0)*k;
		camera.position.y = y0 + (0.25 - y0)*k;
		camera.fov = 45 - 13*k; camera.updateProjectionMatrix();
		if (k > 0.5) elWarp.classList.add('is-on');
	}, () => {
		camera.position.set(0, 0, 15); camera.fov = 45; camera.updateProjectionMatrix();
		busy = false; go(1);
	});
}

bN.addEventListener('click', ()=>go(cur+1));
bB.addEventListener('click', ()=>go(cur-1));
elEnter.addEventListener('click', e=>{ e.preventDefault(); plunge(); });
document.addEventListener('keydown', e=>{ if(e.key==='ArrowRight')go(cur+1); else if(e.key==='ArrowLeft')go(cur-1); });
let x0=null;
host.addEventListener('touchstart', e=>{ x0=e.touches[0].clientX; }, {passive:true});
host.addEventListener('touchend', e=>{ if(current3D&&current3D.userData.drag){ x0=null; return; } if(x0===null)return; const dx=e.changedTouches[0].clientX-x0; if(Math.abs(dx)>55) go(cur+(dx<0?1:-1)); x0=null; }, {passive:true});

/* Drag pour faire tourner soi-même le modèle (Vésuve, tour de Marrakech) */
let dragging=false, dpx=0, dpy=0;
host.addEventListener('pointerdown', e=>{ if(current3D&&current3D.userData.drag){ dragging=true; dpx=e.clientX; dpy=e.clientY; host.style.cursor='grabbing'; } });
window.addEventListener('pointermove', e=>{ if(!dragging||!current3D)return; const u=current3D.userData; u.dragY += (e.clientX-dpx)*0.008; u.dragX = Math.max(-0.7, Math.min(0.7, u.dragX + (e.clientY-dpy)*0.006)); dpx=e.clientX; dpy=e.clientY; });
window.addEventListener('pointerup', ()=>{ dragging=false; if(current3D&&current3D.userData.drag) host.style.cursor='grab'; });

let ttX=0,tX=0;
host.addEventListener('mousemove', e=>{ const r=host.getBoundingClientRect(); ttX=((e.clientX-r.left)/r.width-0.5)*0.6; }, {passive:true});

let on=false;
elSound.addEventListener('click', ()=>{ on=!on; elSound.classList.toggle('is-off', !on); elSound.setAttribute('aria-label', on ? 'Couper le son' : 'Activer le son'); if(on){ audio.volume=0; audio.play().then(()=>{ let v=0,f=setInterval(()=>{v=Math.min(.45,v+.03);audio.volume=v;if(v>=.45)clearInterval(f);},120); }).catch(()=>{}); } else { audio.pause(); } });

/* Constellation : cliquer une étoile -> plongée dans l'étoile -> cartes d'offres flottantes */
const details = Array.from(document.querySelectorAll('.agx__detail'));
function closeOrbs(){ details.forEach(d => d.classList.remove('is-on')); host.classList.remove('is-detail'); }
function openDetail(idx){
	const orb = elMenu.querySelector('.agx__orb[data-orb="'+idx+'"]');
	if (orb) orb.classList.add('is-diving');
	elWarp.classList.add('is-on');
	setTimeout(() => {
		host.classList.add('is-detail');
		details.forEach(d => d.classList.toggle('is-on', d.dataset.detail === String(idx)));
		elWarp.classList.remove('is-on');
		if (orb) orb.classList.remove('is-diving');
	}, 430);
}
elMenu.querySelectorAll('.agx__orb-dot').forEach(btn => {
	btn.addEventListener('click', e => { e.stopPropagation(); openDetail(btn.closest('.agx__orb').dataset.orb); });
});
details.forEach(d => {
	d.querySelector('.agx__detail-back').addEventListener('click', e => { e.preventDefault(); closeOrbs(); });
	d.addEventListener('click', e => { if (e.target === d || e.target.classList.contains('agx__cards') || e.target.classList.contains('agx__detail-title')) closeOrbs(); });
});

new ResizeObserver(()=>{ const w=W(),h=H(); if(!w||!h)return; camera.aspect=w/h; camera.updateProjectionMatrix(); renderer.setSize(w,h,false); }).observe(host);

const t0 = performance.now();
function loop(){
	const t=(performance.now()-t0)*0.001;
	tX += (ttX-tX)*0.05;
	if (current3D){
		const u = current3D.userData;
		if (u.points){ current3D.rotation.x = 1.2; current3D.rotation.y = 0; u.spinNode.rotation.y = t*0.05 + tX*0.25; } // galaxie DE FACE en orbite (on voit la spirale)
		else if (u.flat){ current3D.rotation.x = 1.32 + u.dragX; current3D.rotation.y = 0; u.spinNode.rotation.y = u.dragY + Math.sin(t*0.18)*0.30; current3D.position.y = u.baseY + Math.sin(t*0.5)*0.04; } // carte bien à plat, oscillation douce (pas de rotation complète => pas d'effet "diamant")
		else if (u.drag){ current3D.rotation.y = u.dragY + (u.spin ? t*0.18 : 0); current3D.rotation.x = u.dragX; current3D.position.y = u.baseY + Math.sin(t*0.5)*0.05; } // l'utilisateur tourne l'objet
		else if (u.front){ current3D.rotation.y = u.rotY + Math.sin(t*0.45)*0.12; current3D.position.y = u.baseY + Math.sin(t*0.6)*0.07; } // toujours de face, léger balancement
		else { current3D.rotation.y = tX*0.5; current3D.position.y = u.baseY + Math.sin(t*0.5)*0.1; }
	}
	renderer.render(scene, camera);
	requestAnimationFrame(loop);
}
go(0, true); loop();
</script>

<?php get_footer(); ?>
