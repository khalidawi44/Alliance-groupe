<?php
/**
 * Accueil — Priorité 2 : RECRUTER des commerciaux.
 * Section immersive : fond bureau Naples FIXE bien visible, la section
 * remonte sur la précédente. Lien vers /ambassadeurs.
 */
$ag_amb_bg = get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg';
?>
<section class="ag-section ag-section--onyx ag-home-amb ag-rise" id="recrutement" style="--amb-bg:url('<?php echo esc_url( $ag_amb_bg ); ?>');">
	<div class="ag-home-amb__bg" aria-hidden="true"></div>
	<div class="ag-container ag-home-amb__inner">
		<span class="ag-tag ag-anim" data-anim="tag">Programme Ambassadeurs 🤝</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Vends nos services, <em>touche 10 %</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Rejoins notre équipe de vente. Pour chaque client que tu ramènes, tu gagnes 10 % du montant, payé via PayPal. Pas besoin d'être expert : on te donne tous les outils (scripts, pitch, supports).</p>

		<div class="ag-home-amb__steps">
			<div class="ag-home-amb__step ag-anim" data-anim="card"><span>01</span><p>Tu t'inscris (1 min)</p></div>
			<div class="ag-home-amb__step ag-anim" data-anim="card"><span>02</span><p>Tu prospectes autour de toi</p></div>
			<div class="ag-home-amb__step ag-anim" data-anim="card"><span>03</span><p>Tu déclares la vente</p></div>
			<div class="ag-home-amb__step ag-anim" data-anim="card"><span>04</span><p>Tu es payé 10 %</p></div>
		</div>

		<div class="ag-home-amb__cta">
			<a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold">Rejoindre le programme →</a>
		</div>
	</div>
</section>

<style>
.ag-rise{position:relative;z-index:3;margin-top:-58px;border-top-left-radius:44px;border-top-right-radius:44px;box-shadow:0 -50px 90px rgba(0,0,0,.55);overflow:hidden;}
.ag-home-amb{background:#0a0a0c;min-height:88vh;display:flex;align-items:center;}
.ag-home-amb__bg{position:absolute;inset:0;background-image:var(--amb-bg);background-size:cover;background-position:center;background-attachment:fixed;opacity:1;}
.ag-home-amb__bg::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,10,12,.58),rgba(10,10,12,.5) 45%,rgba(10,10,12,.84));}
.ag-home-amb__inner{position:relative;z-index:2;width:100%;}
.ag-home-amb__steps{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:46px;}
.ag-home-amb__step{padding:26px 20px;background:rgba(18,16,22,.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);border:1px solid rgba(212,180,92,.22);border-radius:16px;text-align:center;}
.ag-home-amb__step span{display:inline-block;font-family:var(--font-serif);font-size:1.7rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;margin-bottom:8px;}
.ag-home-amb__step p{margin:0;color:#fff;font-weight:600;font-size:.95rem;line-height:1.4;}
.ag-home-amb__cta{text-align:center;margin-top:40px;}
@media(max-width:760px){.ag-home-amb__steps{grid-template-columns:1fr 1fr;}.ag-home-amb{min-height:auto;}}
@media(max-width:768px){.ag-home-amb__bg{background-attachment:scroll;}.ag-rise{margin-top:-38px;border-top-left-radius:30px;border-top-right-radius:30px;}}
</style>
