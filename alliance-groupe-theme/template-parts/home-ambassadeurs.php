<?php
/**
 * Accueil — Priorité 2 : RECRUTER des commerciaux.
 * Teaser du programme ambassadeurs (10% par vente), lien vers /ambassadeurs.
 */
?>
<section class="ag-section ag-section--onyx" id="recrutement">
	<div class="ag-container">
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
.ag-home-amb__steps{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:46px;}
.ag-home-amb__step{padding:24px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.16);border-radius:16px;text-align:center;}
.ag-home-amb__step span{display:inline-block;font-family:var(--font-serif);font-size:1.6rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;margin-bottom:8px;}
.ag-home-amb__step p{margin:0;color:var(--color-text-soft);font-weight:600;font-size:.95rem;line-height:1.4;}
.ag-home-amb__cta{text-align:center;margin-top:40px;}
@media(max-width:760px){.ag-home-amb__steps{grid-template-columns:1fr 1fr;}}
</style>
