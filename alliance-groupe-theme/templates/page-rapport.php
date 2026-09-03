<?php
/**
 * Template Name: Rapport client (teaser audit)
 *
 * Page à ENVOYER au prospect : ?site=<url>&name=<nom>. Lance un audit passif,
 * montre la note + 2 failles visibles, CACHE les autres (à débloquer), met en
 * garde sur les conséquences, et propose de faire corriger (CTA). Public.
 */

$ag_site = isset( $_GET['site'] ) ? esc_url_raw( wp_unslash( $_GET['site'] ) ) : '';
$ag_name = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '';
get_header();
?>
<main id="ag-main-content" style="max-width:760px;margin:0 auto;padding:26px 16px 60px;">
<?php
if ( '' === $ag_site || ! preg_match( '#^https?://#i', $ag_site ) || ! function_exists( 'ag_audit_run' ) ) {
	echo '<h1 style="text-align:center;">Rapport de sécurité</h1><p style="text-align:center;color:#777;">Lien invalide ou audit indisponible.</p>';
} else {
	$a      = ag_audit_run( $ag_site );
	$score  = (int) ( $a['score'] ?? 0 );
	$tech   = (string) ( $a['tech'] ?? '' );
	$checks = (array) ( $a['checks'] ?? array() );
	$fails  = array();
	foreach ( $checks as $c ) {
		if ( in_array( $c['status'] ?? '', array( 'fail', 'warn' ), true ) ) { $fails[] = $c; }
	}
	$nb        = count( $fails );
	$visibles  = array_slice( $fails, 0, 2 );
	$caches    = array_slice( $fails, 2 );
	$host      = wp_parse_url( $ag_site, PHP_URL_HOST );
	$who       = '' !== $ag_name ? $ag_name : $host;
	$col       = $score < 50 ? '#e5484d' : ( $score < 75 ? '#e6a817' : '#2ecc71' );
	$shot      = 'https://s.wordpress.com/mshots/v1/' . rawurlencode( $ag_site ) . '?w=680';
	$cta       = home_url( '/audit-securite' );
	?>
	<div style="text-align:center;margin-bottom:8px;">
		<span style="display:inline-block;background:#111;color:#e6b35a;border:1px solid #e6b35a;border-radius:100px;padding:4px 14px;font-size:.8rem;font-weight:700;">AUDIT DE SÉCURITÉ & PERFORMANCE</span>
	</div>
	<h1 style="text-align:center;font-size:1.7rem;margin:6px 0 4px;">Rapport pour <?php echo esc_html( $who ); ?></h1>
	<p style="text-align:center;color:#888;margin:0 0 18px;word-break:break-all;"><?php echo esc_html( $ag_site ); ?></p>

	<div style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;justify-content:center;background:#0f0f14;border:1px solid #2a2a33;border-radius:16px;padding:20px;">
		<img src="<?php echo esc_url( $shot ); ?>" alt="aperçu du site" loading="lazy" style="width:260px;max-width:100%;border-radius:10px;border:1px solid #333;">
		<div style="text-align:center;">
			<div style="font-size:3rem;font-weight:800;color:<?php echo esc_attr( $col ); ?>;line-height:1;"><?php echo (int) $score; ?><span style="font-size:1.1rem;color:#888;">/100</span></div>
			<div style="color:#aaa;font-size:.9rem;margin-top:4px;">Note globale<?php echo $tech ? ' · ' . esc_html( $tech ) : ''; ?></div>
			<div style="color:<?php echo esc_attr( $col ); ?>;font-weight:700;margin-top:6px;"><?php echo $score < 50 ? 'Site à risque' : ( $score < 75 ? 'Des points à corriger' : 'Correct, mais perfectible' ); ?></div>
		</div>
	</div>

	<h2 style="margin:26px 0 10px;font-size:1.2rem;">Ce qu'on a détecté (<?php echo (int) $nb; ?> point<?php echo $nb > 1 ? 's' : ''; ?>)</h2>
	<?php foreach ( $visibles as $c ) : ?>
		<div style="background:#fff5f5;border:1px solid #f3c2c2;border-left:4px solid #e5484d;border-radius:10px;padding:12px 14px;margin-bottom:10px;">
			<strong><?php echo ( 'fail' === $c['status'] ? '' : '' ) . esc_html( $c['name'] ?? '' ); ?></strong>
			<?php if ( ! empty( $c['msg'] ) ) : ?><div style="color:#555;font-size:.9rem;margin-top:4px;"><?php echo esc_html( $c['msg'] ); ?></div><?php endif; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( $caches ) : ?>
		<div style="position:relative;background:#0f0f14;border:1px solid #2a2a33;border-radius:12px;padding:16px;margin-bottom:10px;overflow:hidden;">
			<div style="filter:blur(5px);opacity:.5;user-select:none;pointer-events:none;">
				<?php foreach ( $caches as $c ) : ?>
					<div style="padding:7px 0;border-bottom:1px solid #222;"><?php echo esc_html( $c['name'] ?? 'Faille détectée' ); ?></div>
				<?php endforeach; ?>
			</div>
			<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px;">
				<div style="font-size:1.6rem;"></div>
				<strong style="color:#fff;font-size:1.05rem;"><?php echo (int) count( $caches ); ?> autre<?php echo count( $caches ) > 1 ? 's' : ''; ?> point<?php echo count( $caches ) > 1 ? 's' : ''; ?> à corriger</strong>
				<span style="color:#aaa;font-size:.85rem;">Débloquez le rapport complet ci-dessous.</span>
			</div>
		</div>
	<?php endif; ?>

	<div style="background:#fff8e6;border:1px solid #e6c86a;border-radius:12px;padding:16px;margin:16px 0;color:#5a4a1a;">
		<strong>Pourquoi c'est important&nbsp;:</strong>
		<ul style="margin:8px 0 0;padding-left:18px;line-height:1.6;">
			<li>Un site vulnérable peut être <strong>piraté</strong> (page défigurée, spam, redirection).</li>
			<li>Risque de <strong>fuite des données de vos clients</strong> → responsabilité <strong>RGPD</strong> (amendes possibles).</li>
			<li>Un site lent ou mal sécurisé <strong>perd des clients</strong> et <strong>descend sur Google</strong>.</li>
		</ul>
	</div>

	<div style="text-align:center;margin-top:22px;">
		<a href="<?php echo esc_url( $cta ); ?>" style="display:inline-block;background:linear-gradient(135deg,#d4b45c,#b98f2f);color:#0b0b0f;font-weight:800;padding:15px 26px;border-radius:14px;text-decoration:none;font-size:1.05rem;">Débloquer le rapport complet &amp; faire corriger mon site</a>
		<p style="color:#888;font-size:.85rem;margin-top:12px;">Audit détaillé + correction par Alliance Groupe. Réponse rapide.</p>
	</div>
	<?php
}
?>
</main>
<?php
get_footer();
