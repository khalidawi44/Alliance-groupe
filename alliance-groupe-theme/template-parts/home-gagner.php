<?php
/**
 * Accueil — GAGNER : Ambassadeur + Studio fusionnés (2 colonnes, une seule section).
 */
?>
<section class="ag-section ag-section--graphite ag-gagner" id="recrutement">
	<div class="ag-container">
		<span class="ag-tag ag-anim" data-anim="tag">Gagne de l'argent 💸</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Vends nos sites, <em>touche 10 %</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Rejoins l'équipe et crée tes contenus en 1 clic. Pas besoin d'être expert : on te donne tous les outils.</p>
		<div class="ag-gagner-grid">
			<div class="ag-gagner-col">
				<span class="ag-gagner-ic">🤝</span>
				<h3>Deviens ambassadeur</h3>
				<p>10 % sur chaque vente, payé via PayPal. Lien de vente perso, suivi des commissions et classement.</p>
				<a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold">Rejoindre le programme →</a>
			</div>
			<div class="ag-gagner-col">
				<span class="ag-gagner-ic">🎬</span>
				<h3>Le Studio créatif</h3>
				<p>Crée des vidéos &amp; visuels pros en quelques secondes, ton lien intégré, partage direct sur TikTok / Insta / Snap.</p>
				<a href="<?php echo esc_url( home_url( '/studio' ) ); ?>" class="ag-btn-outline">Ouvrir le Studio →</a>
			</div>
		</div>
	</div>
</section>
<style>
.ag-gagner-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:34px;}
.ag-gagner-col{padding:30px 26px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.22);border-radius:18px;display:flex;flex-direction:column;align-items:flex-start;gap:10px;}
.ag-gagner-ic{font-size:2.4rem;}
.ag-gagner-col h3{color:#fff;font-family:var(--font-serif);font-size:1.4rem;margin:0;}
.ag-gagner-col p{color:var(--color-text-soft);line-height:1.55;margin:0 0 6px;}
.ag-gagner-col .ag-btn-gold,.ag-gagner-col .ag-btn-outline{margin-top:auto;}
@media(max-width:760px){.ag-gagner-grid{grid-template-columns:1fr;}}
</style>
