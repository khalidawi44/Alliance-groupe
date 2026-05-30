<?php
/**
 * CTA "Téléchargez votre template WordPress" — extrait du scroll-fx
 * pour pouvoir l'insérer AVANT la section métier (scroll-fx).
 *
 * Tag vert + heading + lead + 5 features pills + 2 boutons.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="ag-tpl-cta-block">
	<div class="ag-tpl-cta-inner">
		<span class="ag-tpl-cta-tag">🎁 Nouveau — 100% gratuit</span>
		<h2 class="ag-tpl-cta-heading">Téléchargez votre template <em>WordPress</em></h2>
		<p class="ag-tpl-cta-lead">Pas encore prêt pour un site sur-mesure ? Commencez dès maintenant avec l'un de nos <strong>6 thèmes 100% français</strong> : Coach, Artisan, Avocat, Barber, Restaurant, Association. Contenu déjà rédigé, design sombre premium, aucun plugin requis — il ne vous reste qu'à remplacer quelques éléments entre crochets.</p>
		<div class="ag-tpl-cta-features">
			<span>🎨 Design premium</span>
			<span>📱 100% responsive</span>
			<span>⚡ Installation 2 min</span>
			<span>🇫🇷 Support français</span>
			<span>🆓 Totalement gratuit</span>
		</div>
		<div class="ag-tpl-cta-buttons">
			<a href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>" class="ag-tpl-cta-btn ag-tpl-cta-btn--primary">Découvrir les templates →</a>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-tpl-cta-btn ag-tpl-cta-btn--ghost">Besoin d'aide ? On en parle</a>
		</div>
	</div>
</section>

<style>
.ag-tpl-cta-block{padding:80px 24px 60px;background:linear-gradient(180deg,#0a0a0f 0%,#14141c 50%,#0a0a0f 100%);position:relative;overflow:hidden}
.ag-tpl-cta-block::before{content:'';position:absolute;top:-30%;left:50%;transform:translateX(-50%);width:800px;height:800px;background:radial-gradient(circle,rgba(243,122,31,.1) 0%,transparent 60%);pointer-events:none}
.ag-tpl-cta-inner{position:relative;z-index:2;max-width:820px;margin:0 auto;text-align:center}
.ag-tpl-cta-tag{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.4);border-radius:999px;color:#22c55e;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:20px}
.ag-tpl-cta-heading{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.8rem,4vw,3rem);font-weight:700;line-height:1.15;color:#fff;margin:0 0 18px}
.ag-tpl-cta-heading em{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-tpl-cta-lead{color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.7;margin:0 0 28px}
.ag-tpl-cta-lead strong{color:#D4B45C}
.ag-tpl-cta-features{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin:0 0 36px}
.ag-tpl-cta-features span{padding:8px 16px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.25);border-radius:999px;color:#D4B45C;font-size:.86rem;font-weight:600;letter-spacing:.3px}
.ag-tpl-cta-buttons{display:flex;justify-content:center;gap:14px;flex-wrap:wrap;margin-bottom:40px}
.ag-tpl-cta-btn{display:inline-block;padding:16px 30px;font-weight:800;font-size:.92rem;letter-spacing:1.5px;text-transform:uppercase;border-radius:999px;text-decoration:none;transition:transform .3s cubic-bezier(.16,1,.3,1),box-shadow .3s ease}
.ag-tpl-cta-btn--primary{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f !important;box-shadow:0 12px 32px rgba(212,180,92,.35)}
.ag-tpl-cta-btn--primary:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(212,180,92,.55);color:#0a0a0f !important;text-decoration:none}
.ag-tpl-cta-btn--ghost{background:transparent;color:#D4B45C !important;border:1.5px solid rgba(212,180,92,.4)}
.ag-tpl-cta-btn--ghost:hover{border-color:#D4B45C;background:rgba(212,180,92,.08);color:#D4B45C !important;text-decoration:none}
.ag-tpl-cta-arrow{display:inline-flex;flex-direction:column;align-items:center;gap:8px;color:rgba(212,180,92,.7);font-size:.78rem;letter-spacing:2px;text-transform:uppercase;font-weight:600;animation:agTplArrowBounce 2s ease-in-out infinite}
.ag-tpl-cta-arrow svg{color:#D4B45C}
@keyframes agTplArrowBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(8px)}}
@media (prefers-reduced-motion:reduce){.ag-tpl-cta-arrow{animation:none}}
</style>
