<?php
/**
 * Accueil — Priorité 3 : le côté CARITATIF.
 * Teaser du Programme Racines, traitement visuel distinct (accent vert +
 * fond Naples), lien vers /programme-racines.
 */
$ag_racines_bg = get_stylesheet_directory_uri() . '/assets/images/cities/naples-1.jpg';
?>
<section class="ag-home-racines" id="racines" style="--racines-bg:url('<?php echo esc_url( $ag_racines_bg ); ?>');">
	<div class="ag-home-racines__overlay" aria-hidden="true"></div>
	<div class="ag-container ag-home-racines__inner">
		<span class="ag-tag ag-tag--green ag-anim" data-anim="tag">Programme Racines 🌱 · notre engagement</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Le talent est partout, <em>les opportunités non.</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Tu as un projet et de l'énergie mais pas les moyens d'une agence ? On ne te vend pas un site — on devient <strong>ton associé</strong>. On construit ton entreprise ensemble, des quartiers populaires jusqu'au succès.</p>
		<div class="ag-home-racines__cta">
			<a href="<?php echo esc_url( home_url( '/programme-racines' ) ); ?>" class="ag-btn-gold">Découvrir le Programme Racines →</a>
			<a href="<?php echo esc_url( home_url( '/programme-racines#racines-candidature' ) ); ?>" class="ag-btn-outline">Candidater</a>
		</div>
	</div>
</section>

<style>
.ag-home-racines{position:relative;padding:120px 0;background:#07140d;overflow:hidden;text-align:center;}
.ag-home-racines::before{content:"";position:absolute;inset:0;background-image:var(--racines-bg);background-size:cover;background-position:center;opacity:.22;}
.ag-home-racines__overlay{position:absolute;inset:0;background:radial-gradient(ellipse at 50% 38%,rgba(0,132,61,.28),transparent 70%),linear-gradient(180deg,rgba(7,20,13,.82),rgba(7,20,13,.95));}
.ag-home-racines__inner{position:relative;z-index:2;max-width:780px;margin:0 auto;}
.ag-home-racines .ag-section__title{margin-top:14px;}
.ag-home-racines__cta{margin-top:36px;display:flex;flex-wrap:wrap;gap:16px;justify-content:center;}
@media(max-width:760px){.ag-home-racines{padding:84px 0;}}
</style>
