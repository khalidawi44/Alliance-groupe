<?php
/**
 * CTA "Audit gratuit" — bandeau à inclure sur les pages métier
 * (page-wordpress-*, page-service-*) pour capturer des leads sur-mesure.
 *
 * Args optionnels via global :
 *   $GLOBALS['ag_audit_cta_args'] = array(
 *       'title'    => '...',
 *       'subtitle' => '...',
 *       'cta'      => '...',
 *   );
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$ag_audit_args = isset( $GLOBALS['ag_audit_cta_args'] ) && is_array( $GLOBALS['ag_audit_cta_args'] )
	? $GLOBALS['ag_audit_cta_args'] : array();

$ag_audit_title    = $ag_audit_args['title']    ?? 'Audit gratuit de votre projet';
$ag_audit_subtitle = $ag_audit_args['subtitle'] ?? "30 minutes en visio avec notre équipe. Pas de blabla commercial : on regarde votre site / vos besoins et on vous donne 3 actions concrètes à mettre en place. Que vous deveniez client ou pas.";
$ag_audit_cta      = $ag_audit_args['cta']      ?? 'Réserver mon créneau →';
?>

<section class="ag-audit-cta">
	<div class="ag-container">
		<div class="ag-audit-cta__inner">
			<div class="ag-audit-cta__decor">
				<span class="ag-audit-cta__sparkle">✨</span>
			</div>
			<div class="ag-audit-cta__content">
				<span class="ag-audit-cta__tag">🎁 Offert sans engagement</span>
				<h2 class="ag-audit-cta__title"><?php echo esc_html( $ag_audit_title ); ?></h2>
				<p class="ag-audit-cta__sub"><?php echo esc_html( $ag_audit_subtitle ); ?></p>
				<div class="ag-audit-cta__features">
					<span>⏱️ 30 min</span>
					<span>📹 100% visio</span>
					<span>📝 3 actions concrètes</span>
					<span>🇫🇷 Équipe française</span>
				</div>
				<a href="<?php echo esc_url( home_url( '/sur-mesure' ) ); ?>" class="ag-audit-cta__btn">
					<?php echo esc_html( $ag_audit_cta ); ?>
				</a>
				<p class="ag-audit-cta__guarantee">🔒 Sans CB, sans inscription, sans relance commerciale agressive.</p>
			</div>
		</div>
	</div>
</section>

<style>
.ag-audit-cta{padding:80px 24px;background:linear-gradient(135deg,#0a0a0f 0%,#14141c 50%,#1a1a2e 100%);position:relative;overflow:hidden}
.ag-audit-cta::before{content:'';position:absolute;top:-50%;right:-20%;width:600px;height:600px;background:radial-gradient(circle,rgba(212,180,92,.15) 0%,transparent 60%);pointer-events:none}
.ag-audit-cta::after{content:'';position:absolute;bottom:-50%;left:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(243,122,31,.08) 0%,transparent 60%);pointer-events:none}
.ag-audit-cta__inner{position:relative;z-index:2;max-width:880px;margin:0 auto;background:rgba(255,255,255,.03);backdrop-filter:blur(20px);border:1px solid rgba(212,180,92,.25);border-radius:24px;padding:50px 40px;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.4),0 0 60px rgba(212,180,92,.08)}
.ag-audit-cta__decor{position:absolute;top:24px;right:32px;font-size:2rem;animation:agAuditSparkle 3s ease-in-out infinite}
@keyframes agAuditSparkle{0%,100%{transform:rotate(0deg) scale(1);opacity:.6}50%{transform:rotate(20deg) scale(1.2);opacity:1}}
.ag-audit-cta__tag{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.4);border-radius:999px;color:#22c55e;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:18px}
.ag-audit-cta__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:700;line-height:1.2;color:#fff;margin:0 0 16px}
.ag-audit-cta__sub{color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.7;margin:0 0 30px;max-width:620px;margin-left:auto;margin-right:auto}
.ag-audit-cta__features{display:flex;justify-content:center;flex-wrap:wrap;gap:14px;margin:0 0 32px;font-size:.9rem;color:rgba(212,180,92,.85);font-weight:600}
.ag-audit-cta__features span{padding:6px 14px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.2);border-radius:999px}
.ag-audit-cta__btn{display:inline-block;padding:18px 40px;background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f !important;font-weight:800;font-size:1.05rem;letter-spacing:1.5px;text-transform:uppercase;border-radius:999px;text-decoration:none;box-shadow:0 12px 32px rgba(212,180,92,.35);transition:transform .3s cubic-bezier(.16,1,.3,1),box-shadow .3s ease}
.ag-audit-cta__btn:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(212,180,92,.55);color:#0a0a0f !important;text-decoration:none}
.ag-audit-cta__guarantee{color:rgba(255,255,255,.45);font-size:.82rem;margin:18px 0 0;letter-spacing:.5px}
@media (max-width:680px){
	.ag-audit-cta__inner{padding:36px 24px}
	.ag-audit-cta__features{gap:8px;font-size:.8rem}
}
</style>
