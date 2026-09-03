<?php
/**
 * Accueil — Priorité 3 : le côté CARITATIF.
 * Section immersive plein écran : fond Naples FIXE bien visible, accent vert,
 * la section remonte sur la précédente. Lien vers /programme-racines.
 */
$ag_racines_bg = get_stylesheet_directory_uri() . '/assets/images/cities/naples-1.jpg';
?>
<section class="ag-home-racines ag-rise" id="racines" style="--racines-bg:url('<?php echo esc_url( $ag_racines_bg ); ?>');">
	<div class="ag-home-racines__overlay" aria-hidden="true"></div>
	<div class="ag-container ag-home-racines__inner">
		<span class="ag-tag ag-tag--green ag-anim" data-anim="tag">Programme Racines · notre engagement</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Le talent est partout, <em>les opportunités non.</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Tu as un projet et de l'énergie mais pas les moyens d'une agence ? On ne te vend pas un site — on devient <strong>ton associé</strong>. On construit ton entreprise ensemble, des quartiers populaires jusqu'au succès.</p>
		<div class="ag-home-racines__cta">
			<a href="<?php echo esc_url( home_url( '/programme-racines' ) ); ?>" class="ag-btn-gold">Découvrir le Programme Racines →</a>
			<a href="<?php echo esc_url( home_url( '/programme-racines#racines-candidature' ) ); ?>" class="ag-btn-outline">Candidater</a>
		</div>
	</div>
</section>

<style>
.ag-rise{position:relative;z-index:4;margin-top:-58px;border-top-left-radius:44px;border-top-right-radius:44px;box-shadow:0 -50px 90px rgba(0,0,0,.55);overflow:hidden;}
.ag-home-racines{min-height:94vh;display:flex;align-items:center;background:#07140d;text-align:center;}
.ag-home-racines::before{content:"";position:absolute;inset:0;background-image:var(--racines-bg);background-size:cover;background-position:center;background-attachment:fixed;opacity:1;}
.ag-home-racines__overlay{position:absolute;inset:0;background:radial-gradient(ellipse at 50% 42%,rgba(7,20,13,.55),rgba(0,132,61,.22) 55%,transparent 75%),linear-gradient(180deg,rgba(7,20,13,.66),rgba(7,20,13,.62) 45%,rgba(7,20,13,.92));}
.ag-home-racines__inner{position:relative;z-index:2;max-width:820px;margin:0 auto;}
.ag-home-racines .ag-section__title{margin-top:14px;text-shadow:0 2px 20px rgba(0,0,0,.8);}
.ag-home-racines .ag-section__desc{color:#fff;text-shadow:0 2px 16px rgba(0,0,0,.95);}
.ag-home-racines__cta{margin-top:36px;display:flex;flex-wrap:wrap;gap:16px;justify-content:center;}
@media(max-width:768px){.ag-home-racines{min-height:auto;padding:84px 0;}.ag-home-racines::before{background-attachment:scroll;}.ag-rise{margin-top:-38px;border-top-left-radius:30px;border-top-right-radius:30px;}}
</style>
