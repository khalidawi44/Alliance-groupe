<?php
/**
 * Bloc accueil — Le Studio créatif (outil de création vidéo/visuels).
 * Met en avant l'outil à part : créer des vidéos, des visuels, son lien, partager en 1 clic.
 */
?>
<section class="ag-section ag-section--onyx ag-home-studio">
	<div class="ag-container">
		<span class="ag-tag ag-anim" data-anim="tag">L'outil exclusif 🎬</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Le <em>Studio</em> — crée &amp; partage en 1 clic</h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Un studio gratuit, directement sur le site : transforme une photo en <strong>vidéo verticale</strong> ou en <strong>visuel pro</strong>, ajoute ton accroche, et <strong>ton lien de vente s'intègre tout seul</strong>. Idéal pour vendre sur TikTok, Insta et Snap — sans aucune appli à installer.</p>

		<div class="ag-home-studio__grid">
			<div class="ag-home-studio__feat"><span class="ag-home-studio__ic">🎬</span><strong>Vidéos en 8 s</strong><span>Choisis un fond, écris ton accroche, génère.</span></div>
			<div class="ag-home-studio__feat"><span class="ag-home-studio__ic">🖼️</span><strong>Visuels brandés</strong><span>Des images prêtes à poster, à ta marque.</span></div>
			<div class="ag-home-studio__feat"><span class="ag-home-studio__ic">🔗</span><strong>Ton lien intégré</strong><span>Chaque vente passe par ton code.</span></div>
			<div class="ag-home-studio__feat"><span class="ag-home-studio__ic">⤴</span><strong>Partage direct</strong><span>TikTok, Snap, Insta, WhatsApp.</span></div>
		</div>

		<div class="ag-home-studio__cta">
			<a href="<?php echo esc_url( home_url( '/studio' ) ); ?>" class="ag-btn-gold">🎬 Ouvrir le Studio →</a>
			<a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-outline">Gagner 10 % en vendant →</a>
		</div>
	</div>
</section>
<style>
.ag-home-studio__grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:34px;}
.ag-home-studio__feat{padding:22px 18px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;display:flex;flex-direction:column;gap:6px;}
.ag-home-studio__ic{font-size:1.9rem;}
.ag-home-studio__feat strong{color:#fff;font-size:1.02rem;}
.ag-home-studio__feat span:not(.ag-home-studio__ic){color:var(--color-text-soft);font-size:.88rem;line-height:1.45;}
.ag-home-studio__cta{display:flex;flex-wrap:wrap;gap:14px;margin-top:30px;}
@media(max-width:860px){.ag-home-studio__grid{grid-template-columns:1fr 1fr;}}
@media(max-width:760px){.ag-home-studio__cta .ag-btn-gold,.ag-home-studio__cta .ag-btn-outline{width:100%;justify-content:center;text-align:center;}}
</style>
