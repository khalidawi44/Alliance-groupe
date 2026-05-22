<?php
/**
 * Bloc accueil — Offre associations : site 100% GRATUIT.
 * Met en avant le geste solidaire (assos loi 1901, syndicats, mouvements militants).
 */
?>
<section class="ag-section ag-section--graphite ag-asso">
	<div class="ag-container">
		<div class="ag-asso__card">
			<span class="ag-asso__badge">100% GRATUIT 🤝</span>
			<h2 class="ag-asso__title">Vous êtes une <em>association</em> ? Votre site est offert.</h2>
			<p class="ag-asso__txt">Loi 1901, syndicat, mouvement militant, collectif citoyen : on vous crée un <strong>vrai site professionnel, entièrement gratuit</strong>, pour donner de la voix à votre cause. C'est notre engagement solidaire.</p>
			<ul class="ag-asso__list">
				<li>Site vitrine pro, à votre image</li>
				<li>Adhésions &amp; dons en ligne possibles</li>
				<li>Optimisé mobile, prêt à partager</li>
			</ul>
			<a href="<?php echo esc_url( home_url( '/wordpress-association' ) ); ?>" class="ag-btn-gold">Demander mon site gratuit →</a>
		</div>
	</div>
</section>
<style>
.ag-asso__card{max-width:760px;margin:0 auto;padding:34px 30px;text-align:center;background:linear-gradient(160deg,rgba(225,15,26,.10),rgba(212,180,92,.06));border:1px solid rgba(212,180,92,.4);border-radius:20px;}
.ag-asso__badge{display:inline-block;padding:6px 16px;border-radius:100px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:.8rem;letter-spacing:.5px;}
.ag-asso__title{font-family:var(--font-serif);color:#fff;font-size:clamp(1.5rem,4vw,2.1rem);margin:16px 0 12px;line-height:1.25;}
.ag-asso__title em{color:#e8c766;font-style:normal;}
.ag-asso__txt{color:var(--color-text-soft);font-size:1.02rem;line-height:1.6;max-width:600px;margin:0 auto 18px;}
.ag-asso__txt strong{color:#fff;}
.ag-asso__list{list-style:none;padding:0;margin:0 auto 24px;display:inline-flex;flex-direction:column;gap:8px;text-align:left;}
.ag-asso__list li{position:relative;padding-left:26px;color:var(--color-text-soft);}
.ag-asso__list li::before{content:'✓';position:absolute;left:0;color:#4bbf77;font-weight:800;}
</style>
