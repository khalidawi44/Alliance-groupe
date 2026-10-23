<?php
/**
 * Template Name: Conformité accessibilité
 *
 * Page de vente de l'offre « Mise en conformité accessibilité » (filon EAA).
 * Les données (prix, délai, contenu, lien) viennent de ag_accessibilite_offre()
 * — source de vérité unique (inc/ag-offres.php), jamais écrites en dur ici.
 *
 * ⚠️ STRUCTURE à habiller : le visuel fin et la copy sont lane Cowork (COWORK.md).
 */

get_header();

$off = function_exists( 'ag_accessibilite_offre' ) ? ag_accessibilite_offre() : array();
$audit_url = home_url( '/audit-seo' );
?>
<section style="background:linear-gradient(180deg,#0a0a0f 0%,#14141c 100%);color:#fff;padding:72px 24px">
	<div style="max-width:820px;margin:0 auto">

		<div style="text-align:center;margin-bottom:40px">
			<span style="display:inline-block;padding:6px 14px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#D4B45C;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px">♿ Accessibilité numérique</span>
			<h1 style="font-family:Georgia,serif;font-size:clamp(2rem,5vw,3.2rem);line-height:1.15;margin:0 0 14px">Mettez votre site en <em style="background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-style:italic">conformité</em></h1>
			<p style="color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.6;margin:0 auto;max-width:640px">Depuis le 28 juin 2025, l'accessibilité numérique (RGAA) est une obligation pour les sites marchands. On teste votre site, on corrige les points bloquants, et on vous remet un rapport de conformité avant / après.</p>
		</div>

		<?php if ( $off ) : ?>
		<div style="padding:30px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.3);border-radius:16px;margin-bottom:30px">
			<div style="display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:8px;margin-bottom:18px">
				<h2 style="font-family:Georgia,serif;font-size:1.5rem;margin:0;color:#fff"><?php echo esc_html( $off['nom'] ); ?></h2>
				<div style="color:#D4B45C;font-weight:800;font-size:1.2rem"><?php echo esc_html( $off['prix'] ); ?> <span style="color:rgba(255,255,255,.55);font-weight:400;font-size:.9rem">· <?php echo esc_html( $off['delai'] ); ?></span></div>
			</div>
			<ul style="list-style:none;padding:0;margin:0 0 22px;display:flex;flex-direction:column;gap:10px">
				<?php foreach ( (array) ( $off['feats'] ?? array() ) as $f ) : ?>
					<li style="color:rgba(255,255,255,.85);font-size:.95rem;padding-left:26px;position:relative">
						<span style="position:absolute;left:0;color:#28a745">✔</span><?php echo esc_html( $f ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<div style="text-align:center">
				<a href="<?php echo esc_url( $off['url'] ); ?>" style="display:inline-block;padding:16px 36px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#0a0a0f;font-weight:800;border-radius:999px;text-decoration:none;letter-spacing:1px;text-transform:uppercase;font-size:.95rem"><?php echo ! empty( $off['payable'] ) ? 'Lancer ma mise en conformité →' : 'Demander mon devis →'; ?></a>
			</div>
		</div>
		<?php endif; ?>

		<div style="text-align:center;padding:24px;background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.22);border-radius:14px">
			<p style="color:rgba(255,255,255,.85);margin:0 0 14px">Vous ne connaissez pas encore votre niveau ? Obtenez votre <strong>score d'accessibilité /100</strong>, gratuitement et en 30 secondes.</p>
			<a href="<?php echo esc_url( $audit_url ); ?>" style="display:inline-block;padding:12px 26px;background:rgba(212,180,92,.15);border:1px solid #D4B45C;color:#D4B45C;font-weight:700;border-radius:999px;text-decoration:none">📊 Tester mon site →</a>
		</div>

		<p style="color:rgba(255,255,255,.45);font-size:.8rem;text-align:center;margin:26px auto 0;max-width:620px">Diagnostic réalisé en observation externe (sans intrusion). Le référentiel applicable est le RGAA 4.1 (WCAG 2.2 niveau AA).</p>
	</div>
</section>
<?php
get_footer();
