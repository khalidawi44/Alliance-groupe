<?php
/**
 * AG — Kit Print (supports promotionnels imprimables).
 *
 * Genere des aperçus imprimables (HTML + CSS @page) pour :
 *  - Cartes de visite (8.5×5.4 cm, 4 par feuille A4 recto + 4 verso)
 *  - Flyers A5 (148×210 mm, 2 par feuille A4 recto)
 *  - Autocollants ronds (50 mm, 12 par feuille A4)
 *  - Affiches A4 (210×297 mm)
 *
 * Chaque visuel embarque automatiquement le QR code du lien de
 * parrainage de l'ambassadeur (genere via api.qrserver.com — gratuit,
 * sans cle). Si tu veux un QR self-hosted, switcher vers une lib JS
 * (qrcodejs2) en v2.
 *
 * Page admin : Ambassadeurs > 🎨 Kit Print.
 * Aperçus imprimables ouverts en nouvel onglet sur admin-post.php?
 * action=ag_kp_print&type=...&rec=...
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------------------------------------------------------------------------
 * Données : offres mises en avant sur les supports
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_kp_offers' ) ) {
	function ag_kp_offers() {
		return array(
			array(
				'name'      => 'PRO',
				'subtitle'  => 'Transformez votre thème gratuit en thème professionnel',
				'features'  => array( 'Animations & Dégradés', '10 Blocs Gutenberg', 'Customizer Avancé (50+ réglages)', 'Polices Google Fonts', 'Header Sticky', 'Support 60 jours' ),
				'compatible'=> 'Restaurant / Artisan / Coach',
				'badge'     => 'Paiement Unique',
				'stat'      => '',
			),
			array(
				'name'      => 'PREMIUM',
				'subtitle'  => 'Plugin Multilingue & WooCommerce Complet',
				'features'  => array( '6 Langues : FR · EN · IT · ES · DE · AR', 'Témoignages & Galerie', 'Intégration WooCommerce', 'Support Prioritaire 12 Mois', 'Mises à Jour à Vie', 'Appel Expert 30 Min' ),
				'compatible'=> 'Restaurant / Artisan / Coach',
				'badge'     => 'Pour un site sur-mesure ?',
				'stat'      => '+340 % de Leads',
				'featured'  => true,
			),
			array(
				'name'      => 'BUSINESS',
				'subtitle'  => 'Pack Tout-en-Un « Clé en Main »',
				'features'  => array( 'Installation Assistée 1h', 'Maintenance & SEO', 'Rapports Trimestriels', 'Support 2h & White-Label', 'CRM : HubSpot · Pipedrive · Brevo', 'Appel Stratégique Fabrizio' ),
				'compatible'=> 'Sur-mesure',
				'badge'     => 'Paiement Unique',
				'stat'      => 'Site Sur-Mesure · +340 % de Leads',
			),
		);
	}
}

if ( ! function_exists( 'ag_kp_qr_url' ) ) {
	/** URL d'une image QR (PNG) via api.qrserver.com — gratuit, sans cle. */
	function ag_kp_qr_url( $data, $size = 320 ) {
		return 'https://api.qrserver.com/v1/create-qr-code/?size=' . (int) $size . 'x' . (int) $size . '&margin=2&data=' . rawurlencode( $data );
	}
}

if ( ! function_exists( 'ag_kp_kits' ) ) {
	function ag_kp_kits() {
		return array(
			'cartes'   => array( 'label' => '🪪 Cartes de visite', 'desc' => 'Format 85 × 54 mm. Jusqu\'à 4 cartes recto + 4 verso par feuille A4.', 'qty_options' => array( 1, 2, 4 ), 'qty_default' => 4 ),
			'flyer'    => array( 'label' => '📄 Flyer A5', 'desc' => '148 × 210 mm. Jusqu\'à 2 flyers par feuille A4. Idéal boîtes aux lettres / commerces.', 'qty_options' => array( 1, 2 ), 'qty_default' => 2 ),
			'sticker'  => array( 'label' => '🟡 Autocollants ronds', 'desc' => '50 mm. Jusqu\'à 12 par feuille A4. Vitrines, ordis, voitures.', 'qty_options' => array( 1, 4, 6, 12 ), 'qty_default' => 12 ),
			'affiche'  => array( 'label' => '🖼️ Affiche A4', 'desc' => '210 × 297 mm. Toujours 1 par page (pleine page). Vitrines pro.', 'qty_options' => array( 1 ), 'qty_default' => 1 ),
		);
	}
}

/* ---------------------------------------------------------------------------
 * Menu admin (sous-menu Ambassadeurs)
 * ------------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-ambassadeurs', 'Kit Print', '🎨 Kit Print', 'manage_options', 'ag-kit-print', 'ag_kp_render' );
}, 30 );

if ( ! function_exists( 'ag_kp_render' ) ) {
	function ag_kp_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$current = wp_get_current_user();
		$ambs    = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( ! empty( $a['email'] ) ) $ambs[ strtolower( $a['email'] ) ] = $a['name'] ?? $a['email'];
		}
		$mode = isset( $_GET['mode'] ) && 'brand' === $_GET['mode'] ? 'brand' : 'ambassador';

		if ( 'brand' === $mode ) {
			// Mode société : QR sans ?parrain= (promotion directe de la marque).
			$picked      = '';
			$picked_name = 'Alliance Groupe';
			$picked_ref  = '';
			$link        = home_url( '/sites-express' );
		} else {
			$picked      = isset( $_GET['rec'] ) ? strtolower( sanitize_email( wp_unslash( $_GET['rec'] ) ) ) : strtolower( $current->user_email );
			$picked_name = $ambs[ $picked ] ?? $current->display_name;
			$picked_ref  = function_exists( 'ag_ambassadeur_ref' ) ? ag_ambassadeur_ref( $picked ) : '';
			$link        = $picked_ref
				? add_query_arg( array( 'parrain' => $picked_ref ), home_url( '/ambassadeurs' ) )
				: home_url( '/sites-express' );
		}

		$kits = ag_kp_kits();
		?>
		<div class="wrap">
			<h1>🎨 Kit Print — supports à imprimer</h1>
			<p style="max-width:980px;color:#50575e;">Sélectionne un ambassadeur ci-dessous : tous les visuels affichent <strong>son nom et son QR code de parrainage personnel</strong> (chaque vente faite via le QR lui revient avec 10 % de commission).</p>

			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin:14px 0;">
				<input type="hidden" name="page" value="ag-kit-print">
				<label><strong>Pack pour :</strong> </label>
				<select name="mode" onchange="this.form.submit()" style="margin-right:10px;">
					<option value="ambassador" <?php selected( $mode, 'ambassador' ); ?>>👤 Un ambassadeur (avec code parrain)</option>
					<option value="brand" <?php selected( $mode, 'brand' ); ?>>🏢 Ma société (sans code parrain — promo directe)</option>
				</select>
				<?php if ( 'ambassador' === $mode ) : ?>
					<select name="rec" onchange="this.form.submit()">
						<option value="<?php echo esc_attr( $current->user_email ); ?>" <?php selected( $picked, strtolower( $current->user_email ) ); ?>>Moi (<?php echo esc_html( $current->display_name ); ?>)</option>
						<?php foreach ( $ambs as $em => $nm ) : if ( strtolower( $em ) === strtolower( $current->user_email ) ) continue; ?>
							<option value="<?php echo esc_attr( $em ); ?>" <?php selected( $picked, $em ); ?>><?php echo esc_html( $nm . ' — ' . $em ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( ! $picked_ref ) : ?><span style="color:#b32d2e;margin-left:8px;">⚠ Pas de code parrain — le QR pointe sur la page générale.</span><?php endif; ?>
				<?php else : ?>
					<span style="color:#1e7e34;margin-left:6px;">✓ Mode société : QR direct vers <code>/sites-express</code>, branding Alliance Groupe (pas d'ambassadeur affiché).</span>
				<?php endif; ?>
			</form>

			<div style="background:#fff;border:1px solid #ccd0d4;border-radius:10px;padding:14px 18px;max-width:980px;margin:10px 0 18px;">
				<strong>Lien parrain inséré dans tous les QR :</strong><br>
				<code style="font-size:.92em;word-break:break-all;"><?php echo esc_html( $link ); ?></code>
			</div>

			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;max-width:980px;">
			<?php foreach ( $kits as $slug => $kit ) :
				$qty_opts = isset( $kit['qty_options'] ) ? $kit['qty_options'] : array( 1 );
				$qty_def  = isset( $kit['qty_default'] ) ? $kit['qty_default'] : 1;
			?>
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px;">
					<h2 style="margin-top:0;font-size:1.15rem;"><?php echo esc_html( $kit['label'] ); ?></h2>
					<p style="color:#50575e;font-size:.9rem;min-height:48px;"><?php echo esc_html( $kit['desc'] ); ?></p>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" target="_blank" rel="noopener" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
						<input type="hidden" name="action" value="ag_kp_print">
						<input type="hidden" name="type" value="<?php echo esc_attr( $slug ); ?>">
						<?php if ( 'ambassador' === $mode && '' !== $picked ) : ?><input type="hidden" name="rec" value="<?php echo esc_attr( $picked ); ?>"><?php endif; ?>
						<input type="hidden" name="mode" value="<?php echo esc_attr( $mode ); ?>">
						<?php wp_nonce_field( 'ag_kp_print', '_wpnonce', false, true ); ?>
						<?php if ( count( $qty_opts ) > 1 ) : ?>
							<label style="font-size:.85rem;color:#50575e;">Quantité :</label>
							<select name="qty">
								<?php foreach ( $qty_opts as $q ) : ?><option value="<?php echo (int) $q; ?>" <?php selected( $qty_def, $q ); ?>><?php echo (int) $q; ?>×</option><?php endforeach; ?>
							</select>
						<?php else : ?><input type="hidden" name="qty" value="1"><?php endif; ?>
						<?php if ( 'flyer' === $slug ) : ?>
							<label style="font-size:.85rem;color:#50575e;">Tier :</label>
							<select name="tier">
								<option value="PRO">PRO</option>
								<option value="PREMIUM" selected>PREMIUM</option>
								<option value="BUSINESS">BUSINESS</option>
							</select>
						<?php endif; ?>
						<button type="submit" class="button button-primary">🖨️ Aperçu imprimable</button>
					</form>
					<p style="margin-top:8px;color:#50575e;font-size:.82em;">Dans le nouvel onglet : Ctrl+P (Cmd+P) pour imprimer. Choisis <em>« Sans en-têtes ni pieds de page »</em> et marges <em>« Aucune »</em>.</p>
				</div>
			<?php endforeach; ?>
			</div>

			<p style="max-width:980px;color:#50575e;font-size:13px;margin-top:18px;">
				💡 <strong>Astuce impression</strong> : pour les <strong>cartes de visite</strong> et <strong>autocollants</strong>, passe par un imprimeur en ligne (Vistaprint, Helloprint, Onlineprinters) — colle l'URL « aperçu imprimable » ou enregistre la page en PDF (Ctrl+P → « Enregistrer au format PDF »), puis upload chez eux. Pour les <strong>flyers/affiches A4</strong>, ton imprimante perso suffit.
			</p>
		</div>
		<?php
	}
}

/* ---------------------------------------------------------------------------
 * Aperçus imprimables — sortie HTML brute (page complète sans chrome WP)
 * ------------------------------------------------------------------------- */
add_action( 'admin_post_ag_kp_print', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorise' );
	check_admin_referer( 'ag_kp_print' );

	$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'cartes';
	$mode = isset( $_GET['mode'] ) && 'brand' === $_GET['mode'] ? 'brand' : 'ambassador';

	if ( 'brand' === $mode ) {
		$name = 'Alliance Groupe';
		$ref  = '';
		$link = home_url( '/sites-express' );
	} else {
		$rec  = isset( $_GET['rec'] ) ? strtolower( sanitize_email( wp_unslash( $_GET['rec'] ) ) ) : strtolower( wp_get_current_user()->user_email );
		$ambs = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) { if ( ! empty( $a['email'] ) ) $ambs[ strtolower( $a['email'] ) ] = $a['name'] ?? $a['email']; }
		$name = $ambs[ $rec ] ?? wp_get_current_user()->display_name;
		$ref  = function_exists( 'ag_ambassadeur_ref' ) ? ag_ambassadeur_ref( $rec ) : '';
		$link = $ref ? add_query_arg( 'parrain', $ref, home_url( '/ambassadeurs' ) ) : home_url( '/sites-express' );
	}

	if ( ! in_array( $type, array( 'cartes', 'flyer', 'sticker', 'affiche' ), true ) ) $type = 'cartes';

	// Quantité demandée (bornée selon le format).
	$qty_max = array( 'cartes' => 4, 'flyer' => 2, 'sticker' => 12, 'affiche' => 1 );
	$qty     = isset( $_GET['qty'] ) ? max( 1, min( (int) $qty_max[ $type ], (int) $_GET['qty'] ) ) : (int) $qty_max[ $type ];

	// Tier sélectionné pour le flyer A5 (PRO / PREMIUM / BUSINESS).
	$tier_pick = isset( $_GET['tier'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['tier'] ) ) ) : 'PREMIUM';
	if ( ! in_array( $tier_pick, array( 'PRO', 'PREMIUM', 'BUSINESS' ), true ) ) $tier_pick = 'PREMIUM';

	nocache_headers();
	header( 'Content-Type: text/html; charset=UTF-8' );
	?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Kit Print — <?php echo esc_html( ucfirst( $type ) ); ?> — <?php echo esc_html( $name ); ?></title>
<style>
	* { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	body { margin: 0; font-family: 'Helvetica Neue', Arial, sans-serif; color: #0a0a0f; background: #2a2a2a; }
	.toolbar { position: sticky; top: 0; z-index: 9; background: #1a1a2e; color: #fff; padding: 10px 16px; display: flex; gap: 12px; align-items: center; box-shadow: 0 2px 12px rgba(0,0,0,.3); }
	.toolbar h1 { margin: 0; font-size: 1rem; font-weight: 600; }
	.toolbar button, .toolbar a { background: #D4B45C; color: #0a0a0f; padding: 8px 16px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; text-decoration: none; font-size: .9rem; }
	.toolbar .muted { color: rgba(255,255,255,.65); font-size: .85rem; margin-left: auto; }
	.sheet { width: 210mm; min-height: 297mm; margin: 18px auto; background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,.4); position: relative; overflow: hidden; }

	/* Branding tokens (style AG Starter — dark + champagne) */
	:root { --gold: #D4B45C; --gold2: #B8975A; --orange: #F37A1F; --black: #0a0a0f; --navy: #14141c; }
	.brand-grad { background: linear-gradient(160deg, #050507 0%, var(--navy) 55%, #0a0a0f 100%); color: #fff; }
	.gold-grad  { background: linear-gradient(135deg, var(--gold) 0%, var(--orange) 100%); color: var(--black); }
	.gold-border { box-shadow: inset 0 0 0 .25mm var(--gold), inset 0 0 0 1mm rgba(212,180,92,.15); }
	.serif { font-family: 'Playfair Display', Georgia, 'Times New Roman', serif; }
	.diamond { color: var(--gold); }
	.diamond::before { content: '◆'; }
	.stat-badge { display: inline-block; background: linear-gradient(135deg, var(--gold), var(--orange)); color: var(--black); padding: 1.5mm 4mm; border-radius: 100px; font-weight: 900; letter-spacing: .5pt; font-size: 8pt; }

	@page { size: A4 portrait; margin: 0; }
	@media print {
		body { background: #fff; }
		.toolbar { display: none; }
		.sheet { margin: 0; box-shadow: none; page-break-after: always; }
		.sheet:last-of-type { page-break-after: auto; }
	}

	/* ====================================================================
	   CARTES DE VISITE — 1 à 4 par feuille A4 (recto + verso)
	   8.5 × 5.4 cm (standard FR) — style AG Starter dark + champagne
	   ==================================================================== */
	.cards-grid { display: grid; grid-template-columns: repeat(2, 85mm); grid-template-rows: repeat(4, 54mm); gap: 4mm; padding: 14mm 16mm; justify-content: center; align-content: start; }
	.card { width: 85mm; height: 54mm; border-radius: 2mm; overflow: hidden; position: relative; }
	.card-front { padding: 4mm 5mm; display: flex; flex-direction: column; color: #fff; }
	.card-front .head { text-align: center; font-family: 'Playfair Display', Georgia, serif; color: var(--gold); font-size: 7pt; letter-spacing: 3pt; font-weight: 700; }
	.card-front .head::before, .card-front .head::after { content: ' ◆ '; opacity: .8; }
	.card-front .logo { text-align: center; font-family: 'Playfair Display', Georgia, serif; font-size: 14pt; font-weight: 800; letter-spacing: .5pt; color: var(--gold); margin-top: 1.5mm; line-height: 1; }
	.card-front .tier { text-align: center; font-size: 6pt; color: rgba(255,255,255,.55); letter-spacing: 4pt; margin-top: 1mm; font-weight: 400; }
	.card-front .divider { width: 16mm; height: .25mm; background: var(--gold); margin: 2mm auto; opacity: .6; }
	.card-front .name { font-size: 11pt; font-weight: 800; text-align: center; }
	.card-front .role { font-size: 6.5pt; color: var(--gold); text-align: center; margin-top: .8mm; letter-spacing: 1.5pt; }
	.card-front .qr-line { display: flex; align-items: center; gap: 2mm; margin-top: auto; padding-top: 2mm; font-size: 5.8pt; color: rgba(255,255,255,.7); border-top: .15mm solid rgba(212,180,92,.3); }
	.card-front .qr-line img { width: 11mm; height: 11mm; border-radius: 1mm; background: #fff; padding: .5mm; }
	.card-back { padding: 4mm 5mm; display: flex; flex-direction: column; color: #fff; }
	.card-back h3 { margin: 0; font-size: 8.5pt; color: var(--gold); font-family: 'Playfair Display', Georgia, serif; text-align: center; letter-spacing: 1.5pt; text-transform: uppercase; }
	.card-back .offers { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5mm; margin-top: 2mm; }
	.card-back .offer { background: rgba(212,180,92,.06); border: .2mm solid rgba(212,180,92,.5); border-radius: 1mm; padding: 1.5mm 1mm; text-align: center; color: #fff; }
	.card-back .offer.featured { background: linear-gradient(135deg, var(--gold), var(--orange)); color: var(--black); border-color: var(--orange); }
	.card-back .offer .n { font-size: 6pt; font-weight: 800; letter-spacing: 1pt; }
	.card-back .offer .p { font-size: 9pt; font-weight: 900; margin-top: .3mm; }
	.card-back .footer { display: flex; justify-content: space-between; align-items: center; font-size: 5.8pt; margin-top: auto; padding-top: 1.5mm; color: rgba(255,255,255,.7); }
	.card-back .footer .url { color: var(--gold); font-weight: 700; }
	.card-back .footer img { width: 14mm; height: 14mm; background: #fff; padding: .5mm; border-radius: 1mm; }

	/* ====================================================================
	   FLYER A5 — 1 ou 2 par feuille A4 — style AG Starter PRO
	   ==================================================================== */
	.a5-flex { display: flex; flex-direction: column; gap: 0; padding: 0; height: 297mm; }
	.flyer { width: 210mm; height: 148.5mm; padding: 8mm 10mm; position: relative; box-sizing: border-box; }
	.flyer-inner { width: 100%; height: 100%; border: .3mm solid rgba(212,180,92,.55); border-radius: 2mm; padding: 8mm 10mm; display: grid; grid-template-columns: 1.4fr 1fr; gap: 6mm; box-sizing: border-box; position: relative; }
	.flyer .head-tier { text-align: center; font-family: 'Playfair Display', Georgia, serif; color: var(--gold); font-size: 8pt; letter-spacing: 4pt; font-weight: 700; grid-column: 1/3; margin-bottom: 2mm; }
	.flyer .head-tier::before, .flyer .head-tier::after { content: ' ◆ '; opacity: .8; }
	.flyer .lead h2 { margin: 0; font-size: 22pt; line-height: 1.05; font-family: 'Playfair Display', Georgia, serif; }
	.flyer .lead h2 .accent { color: var(--gold); }
	.flyer .lead .stat-badge { margin-top: 3mm; }
	.flyer .lead p { margin: 3mm 0 0; font-size: 10pt; line-height: 1.4; color: rgba(255,255,255,.82); }
	.flyer .lead ul { list-style: none; padding: 0; margin: 3mm 0 0; font-size: 9pt; color: #fff; }
	.flyer .lead ul li { padding: 1mm 0; }
	.flyer .lead ul li::before { content: '◆  '; color: var(--gold); font-size: 8pt; }
	.flyer .lead .offers { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2mm; margin-top: 4mm; }
	.flyer .lead .offer { background: rgba(255,255,255,.04); border: .25mm solid rgba(212,180,92,.5); border-radius: 1.5mm; padding: 3mm 2mm; text-align: center; }
	.flyer .lead .offer.featured { background: linear-gradient(135deg, var(--gold), var(--orange)); color: var(--black); border-color: var(--orange); }
	.flyer .lead .offer .n { font-size: 8pt; font-weight: 800; letter-spacing: .8pt; }
	.flyer .lead .offer .p { font-size: 14pt; font-weight: 900; margin-top: .5mm; }
	.flyer .lead .offer .t { font-size: 6.5pt; opacity: .85; margin-top: .3mm; }
	.flyer .qr-block { text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; }
	.flyer .qr-block .scan { color: var(--gold); font-size: 11pt; font-weight: 800; letter-spacing: 2pt; margin-bottom: 2.5mm; font-family: 'Playfair Display', Georgia, serif; }
	.flyer .qr-block img { width: 50mm; height: 50mm; background: #fff; padding: 1.5mm; border-radius: 2mm; }
	.flyer .qr-block .label { color: #fff; font-size: 8pt; margin-top: 2.5mm; letter-spacing: .5pt; }
	.flyer .qr-block .name { color: var(--gold); font-size: 9pt; font-weight: 700; margin-top: 1mm; }
	.flyer .footer-line { position: absolute; bottom: 4mm; left: 16mm; right: 16mm; display: flex; justify-content: space-between; align-items: center; color: rgba(255,255,255,.55); font-size: 7pt; padding-top: 2mm; border-top: .15mm solid rgba(212,180,92,.25); }
	.flyer .footer-line .url { color: var(--gold); font-weight: 700; }

	/* ====================================================================
	   AUTOCOLLANTS ronds 50 mm — 12 par feuille A4 (4 × 3)
	   ==================================================================== */
	.stickers-grid { display: grid; grid-template-columns: repeat(3, 50mm); grid-template-rows: repeat(4, 50mm); gap: 8mm; padding: 18mm; justify-content: center; align-content: start; }
	.sticker { width: 50mm; height: 50mm; border-radius: 50%; padding: 4mm; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
	.sticker .top { font-size: 7pt; font-weight: 800; color: var(--gold); letter-spacing: 1pt; }
	.sticker .price { font-size: 11pt; font-weight: 900; color: #fff; margin: 1mm 0; }
	.sticker img { width: 22mm; height: 22mm; background: #fff; padding: 1mm; border-radius: 1mm; }
	.sticker .scan { font-size: 5.5pt; color: rgba(255,255,255,.8); margin-top: 1mm; letter-spacing: .5pt; }

	/* ====================================================================
	   TIER CARD — bloc vertical "AG Starter [TIER]" réutilisable
	   (utilisé dans affiche A4 triptyque + flyer A5)
	   ==================================================================== */
	.tier-card { background: linear-gradient(165deg, #060608 0%, #14141c 55%, #060608 100%); color: #fff; border: .35mm solid rgba(212,180,92,.6); border-radius: 3mm; padding: 5mm 5mm 4mm; display: flex; flex-direction: column; position: relative; overflow: hidden; }
	.tier-card .ag-starter { font-family: 'Playfair Display', Georgia, serif; font-size: 21pt; font-weight: 800; color: var(--gold); text-align: center; line-height: 1; letter-spacing: .5pt; }
	.tier-card .tier-name { font-family: 'Playfair Display', Georgia, serif; font-size: 15pt; font-weight: 700; color: var(--gold); text-align: center; margin-top: .5mm; letter-spacing: 1.2pt; }
	.tier-card .tier-name-row { display: flex; align-items: center; justify-content: center; gap: 3mm; margin-top: 1mm; }
	.tier-card .tier-name-row .line { flex: 0 0 6mm; height: .25mm; background: var(--gold); opacity: .65; }
	.tier-card .subtitle { color: #fff; font-size: 9pt; text-align: center; margin: 3mm 2mm 2mm; line-height: 1.35; font-weight: 500; }
	.tier-card .diam { color: var(--gold); text-align: center; margin: 2mm 0 1mm; font-size: 9pt; }
	.tier-card ul.features { list-style: none; padding: 0; margin: 2mm 0; flex: 1; }
	.tier-card ul.features li { padding: 1mm 0 1mm 6mm; color: #fff; font-size: 8.8pt; position: relative; line-height: 1.35; }
	.tier-card ul.features li::before { content: '✓'; position: absolute; left: 0; color: var(--gold); font-weight: 900; font-size: 10pt; }
	.tier-card .compat { text-align: center; color: rgba(255,255,255,.92); font-size: 8.2pt; border-top: .2mm solid rgba(212,180,92,.4); padding: 2mm 0 0; margin-top: 2mm; font-style: italic; }
	.tier-card .stat-line { text-align: center; color: var(--gold); font-size: 10.5pt; font-weight: 900; margin: 2mm 0 1mm; font-family: 'Playfair Display', Georgia, serif; }
	.tier-card .pay-badge { text-align: center; color: var(--gold); border-top: .2mm solid var(--gold); border-bottom: .2mm solid var(--gold); padding: 1.5mm 0; font-size: 7.5pt; letter-spacing: 1.5pt; margin: 2mm 0 3mm; font-weight: 700; text-transform: uppercase; }
	.tier-card .pay-badge::before, .tier-card .pay-badge::after { content: ' ◆ '; opacity: .85; }
	.tier-card .qr-footer { display: flex; align-items: flex-end; gap: 2mm; margin-top: 1mm; }
	.tier-card .qr-footer .qr-box { background: #fff; padding: .8mm; border-radius: 1mm; position: relative; flex: 0 0 auto; }
	.tier-card .qr-footer .qr-box img { width: 18mm; height: 18mm; display: block; }
	.tier-card .qr-footer .qr-box .scan-me { position: absolute; left: 0; right: 0; bottom: -3mm; text-align: center; color: var(--gold); font-size: 5.5pt; font-weight: 700; letter-spacing: .8pt; text-transform: uppercase; }
	.tier-card .qr-footer .contact { flex: 1; text-align: right; padding-bottom: 2mm; }
	.tier-card .qr-footer .contact .phone { color: var(--gold); font-weight: 800; font-size: 11pt; font-family: 'Playfair Display', Georgia, serif; }
	.tier-card .qr-footer .contact .url { color: rgba(255,255,255,.8); font-size: 7pt; margin-top: .5mm; }

	/* ====================================================================
	   AFFICHE A4 — triptyque AG Starter PRO · PREMIUM · BUSINESS
	   ==================================================================== */
	.affiche { width: 210mm; height: 297mm; padding: 10mm 8mm; display: flex; flex-direction: column; box-sizing: border-box; }
	.affiche .top-head { text-align: center; color: var(--gold); font-family: 'Playfair Display', Georgia, serif; font-size: 11pt; letter-spacing: 4pt; font-weight: 700; margin-bottom: 6mm; }
	.affiche .top-head::before, .affiche .top-head::after { content: ' ◆ '; opacity: .85; }
	.affiche .triptyque { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; flex: 1; }
	.affiche .triptyque .tier-card { padding: 6mm 5mm 5mm; }
	.affiche .offers { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5mm; margin-bottom: 10mm; }
	.affiche .offer { background: rgba(255,255,255,.06); border: .4mm solid rgba(212,180,92,.5); border-radius: 3mm; padding: 6mm 4mm; text-align: center; color: #fff; }
	.affiche .offer.featured { background: linear-gradient(135deg, var(--gold), var(--orange)); color: var(--black); border-color: var(--orange); }
	.affiche .offer .n { font-size: 11pt; font-weight: 800; letter-spacing: 1pt; }
	.affiche .offer .p { font-size: 26pt; font-weight: 900; margin: 2mm 0; }
	.affiche .offer .t { font-size: 9pt; opacity: .9; }
	.affiche .offer ul { margin: 3mm 0 0; padding-left: 3mm; text-align: left; font-size: 9pt; list-style: none; }
	.affiche .offer li::before { content: "✓ "; color: var(--gold); font-weight: 700; }
	.affiche .offer.featured li::before { color: var(--black); }
	.affiche .qr-row { display: flex; align-items: center; gap: 8mm; margin-top: auto; padding-top: 6mm; border-top: .3mm solid rgba(212,180,92,.3); }
	.affiche .qr-row img { width: 50mm; height: 50mm; background: #fff; padding: 2mm; border-radius: 3mm; }
	.affiche .qr-row .text { color: #fff; flex: 1; }
	.affiche .qr-row .scan { color: var(--gold); font-size: 18pt; font-weight: 900; margin-bottom: 2mm; letter-spacing: 1pt; }
	.affiche .qr-row .name { font-size: 11pt; }
	.affiche .qr-row .url { color: var(--gold); font-size: 10pt; word-break: break-all; margin-top: 1mm; }
</style>
</head>
<body>

<div class="toolbar">
	<h1>🖨️ Kit Print — <?php echo esc_html( ucfirst( $type ) ); ?> — <?php echo esc_html( $name ); ?></h1>
	<button onclick="window.print()">Imprimer maintenant</button>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-kit-print' ) ); ?>">← Retour au Kit Print</a>
	<span class="muted">Astuce : Ctrl+P → marges « Aucune » → décocher en-têtes/pieds de page.</span>
</div>

<?php
$offers = ag_kp_offers();
$qr_small  = ag_kp_qr_url( $link, 200 );
$qr_medium = ag_kp_qr_url( $link, 320 );
$qr_big    = ag_kp_qr_url( $link, 600 );

if ( 'cartes' === $type ) :
	// ── Feuille recto : 4 cartes "façade" ──
	?>
	<div class="sheet brand-grad">
		<div class="cards-grid">
			<?php for ( $i = 0; $i < $qty; $i++ ) : ?>
				<div class="card card-front brand-grad gold-border">
					<div class="head">ALLIANCE GROUPE</div>
					<div class="logo">AG</div>
					<div class="tier">AGENCE WEB &amp; IA</div>
					<div class="divider"></div>
					<?php if ( 'brand' === $mode ) : ?>
						<div class="name">Votre site pro<br>en 7 jours</div>
						<div class="role">DÈS 490 € · PAIEMENT 4× SANS FRAIS</div>
					<?php else : ?>
						<div class="name"><?php echo esc_html( $name ); ?></div>
						<div class="role">AMBASSADEUR OFFICIEL</div>
					<?php endif; ?>
					<div class="qr-line">
						<img src="<?php echo esc_url( $qr_small ); ?>" alt="QR">
						<span><?php if ( 'brand' === $mode ) : ?>📞 07 44 82 95 16<br>alliancegroupe-inc.com<?php else : ?>Scanne → devis gratuit<br>Site pro dès 490 €<?php endif; ?></span>
					</div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<!-- Verso : offres AG Starter -->
	<div class="sheet brand-grad">
		<div class="cards-grid">
			<?php for ( $i = 0; $i < $qty; $i++ ) : ?>
				<div class="card card-back brand-grad gold-border">
					<h3>◆ AG STARTER ◆</h3>
					<div class="offers">
						<?php foreach ( $offers as $o ) : ?>
							<div class="offer <?php echo ! empty( $o['featured'] ) ? 'featured' : ''; ?>">
								<div class="n"><?php echo esc_html( $o['name'] ); ?></div>
								<div class="p" style="font-size:7pt;font-weight:700;margin-top:.3mm;line-height:1.15;">✓</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div style="text-align:center;color:var(--gold);font-size:7pt;font-weight:800;margin-top:1.5mm;letter-spacing:1pt;">+340 % DE LEADS</div>
					<div class="footer">
						<div>
							<div class="url">alliancegroupe-inc.com/contact</div>
							<div>📞 07 44 82 95 16</div>
						</div>
						<img src="<?php echo esc_url( $qr_medium ); ?>" alt="QR">
					</div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<?php

elseif ( 'flyer' === $type ) :
	// Récupère le tier choisi parmi les offres.
	$pick = null;
	foreach ( $offers as $o ) { if ( $o['name'] === $tier_pick ) { $pick = $o; break; } }
	if ( ! $pick ) $pick = $offers[1];
	?>
	<div class="sheet brand-grad">
		<div style="display:flex;flex-direction:column;height:297mm;padding:0;">
			<?php for ( $i = 0; $i < $qty; $i++ ) : ?>
				<div style="width:210mm;height:148.5mm;padding:6mm 30mm;box-sizing:border-box;">
					<div class="tier-card" style="height:100%;padding:8mm 10mm;">
						<div class="ag-starter">AG Starter</div>
						<div class="tier-name-row"><span class="line"></span><span class="tier-name" style="font-size:18pt;"><?php echo esc_html( $pick['name'] ); ?></span><span class="line"></span></div>
						<div class="subtitle" style="font-size:10pt;margin:4mm 4mm 2mm;"><?php echo esc_html( $pick['subtitle'] ); ?></div>
						<div class="diam">◆</div>
						<ul class="features" style="font-size:10pt;">
							<?php foreach ( (array) $pick['features'] as $f ) : ?>
								<li style="padding:1.5mm 0 1.5mm 7mm;font-size:10pt;"><?php echo esc_html( $f ); ?></li>
							<?php endforeach; ?>
						</ul>
						<div class="compat" style="font-size:9pt;">Compatible <?php echo esc_html( $pick['compatible'] ); ?></div>
						<div class="diam" style="margin-top:2mm;">◆</div>
						<?php if ( ! empty( $pick['stat'] ) ) : ?><div class="stat-line" style="font-size:12pt;"><?php echo esc_html( $pick['stat'] ); ?></div><?php endif; ?>
						<div class="pay-badge" style="font-size:8.5pt;"><?php echo esc_html( $pick['badge'] ); ?></div>
						<div class="qr-footer">
							<div class="qr-box"><img src="<?php echo esc_url( $qr_medium ); ?>" alt="QR" style="width:24mm;height:24mm;"><div class="scan-me">Scan Me</div></div>
							<div class="contact">
								<div class="phone" style="font-size:13pt;">07 44 82 95 16</div>
								<div class="url" style="font-size:8pt;">alliancegroupe-inc.com/contact</div>
								<?php if ( 'brand' !== $mode ) : ?><div style="color:var(--gold);font-size:8pt;margin-top:1mm;font-weight:700;"><?php echo esc_html( $name ); ?> · ambassadeur</div><?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<?php

elseif ( 'sticker' === $type ) :
	?>
	<div class="sheet" style="background:#fff;">
		<div class="stickers-grid">
			<?php for ( $i = 0; $i < $qty; $i++ ) : ?>
				<div class="sticker brand-grad">
					<div class="top">Alliance Groupe</div>
					<div class="price">Site web<br>dès 490 €</div>
					<img src="<?php echo esc_url( $qr_small ); ?>" alt="QR">
					<div class="scan">SCANNE-MOI</div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<?php

elseif ( 'affiche' === $type ) :
	?>
	<div class="sheet brand-grad">
		<div class="affiche">
			<div class="top-head">ALLIANCE GROUPE · AGENCE WEB &amp; IA</div>
			<div class="triptyque">
				<?php foreach ( $offers as $o ) : ?>
					<div class="tier-card">
						<div class="ag-starter">AG Starter</div>
						<div class="tier-name-row"><span class="line"></span><span class="tier-name"><?php echo esc_html( $o['name'] ); ?></span><span class="line"></span></div>
						<div class="subtitle"><?php echo esc_html( $o['subtitle'] ); ?></div>
						<div class="diam">◆</div>
						<ul class="features">
							<?php foreach ( (array) $o['features'] as $f ) : ?>
								<li><?php echo esc_html( $f ); ?></li>
							<?php endforeach; ?>
						</ul>
						<div class="compat">Compatible <?php echo esc_html( $o['compatible'] ); ?></div>
						<div class="diam" style="margin-top:1mm;">◆</div>
						<?php if ( ! empty( $o['stat'] ) ) : ?><div class="stat-line"><?php echo esc_html( $o['stat'] ); ?></div><?php endif; ?>
						<div class="pay-badge"><?php echo esc_html( $o['badge'] ); ?></div>
						<div class="qr-footer">
							<div class="qr-box"><img src="<?php echo esc_url( $qr_small ); ?>" alt="QR"><div class="scan-me">Scan Me</div></div>
							<div class="contact">
								<div class="phone">07 44 82 95 16</div>
								<div class="url">alliancegroupe-inc.com/contact</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
endif;
?>

</body>
</html>
	<?php
	exit;
} );
