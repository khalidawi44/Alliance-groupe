<?php
/**
 * Accueil — SOLIDAIRE : Programme Racines + Associations fusionnés (2 colonnes).
 */
?>
<section class="ag-section ag-section--light ag-soli" id="racines">
	<div class="ag-container">
		<span class="ag-tag ag-anim" data-anim="tag">Notre engagement </span>
		<h2 class="ag-section__title ag-anim" data-anim="title">On rend au <em>quartier</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Le talent est partout, les opportunités non. On soutient celles et ceux qui en ont besoin.</p>
		<div class="ag-soli-grid">
			<div class="ag-soli-col">
				<span class="ag-soli-ic"></span>
				<h3>Programme Racines</h3>
				<p>On forme et on accompagne les jeunes du quartier vers les métiers du digital. Donne-leur leur chance.</p>
				<a href="<?php echo esc_url( home_url( '/programme-racines' ) ); ?>" class="ag-btn-gold">Découvrir le Programme →</a>
			</div>
			<div class="ag-soli-col">
				<span class="ag-soli-badge">100% GRATUIT</span>
				<h3>Associations : site offert</h3>
				<p>Loi 1901, syndicat, collectif citoyen : on vous crée un vrai site professionnel, <strong>entièrement gratuit</strong>.</p>
				<a href="<?php echo esc_url( home_url( '/wordpress-association' ) ); ?>" class="ag-btn-outline">Demander mon site gratuit →</a>
			</div>
		</div>
	</div>
</section>
<style>
.ag-soli-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:34px;}
.ag-soli-col{padding:30px 26px;background:#fff;border:1px solid rgba(212,180,92,.4);border-radius:18px;display:flex;flex-direction:column;align-items:flex-start;gap:10px;box-shadow:0 12px 34px rgba(120,100,40,.12);}
.ag-soli-ic{font-size:2.4rem;}
.ag-soli-badge{display:inline-block;padding:5px 14px;border-radius:100px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:.78rem;}
.ag-soli-col h3{color:#17150f;font-family:var(--font-serif);font-size:1.4rem;margin:0;}
.ag-soli-col p{color:#5b5446;line-height:1.55;margin:0 0 6px;}
.ag-soli-col p strong{color:#17150f;}
.ag-soli-col .ag-btn-gold,.ag-soli-col .ag-btn-outline{margin-top:auto;}
@media(max-width:760px){.ag-soli-grid{grid-template-columns:1fr;}}
</style>
