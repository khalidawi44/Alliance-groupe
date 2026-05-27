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
			array( 'name' => 'STARTER',  'price' => '490 €',  'tag' => 'site vitrine 1 page', 'highlights' => array( 'Design pro', 'Mobile parfait', 'Livré en 7 jours' ) ),
			array( 'name' => 'PRO',      'price' => '890 €',  'tag' => 'site complet 5 pages', 'highlights' => array( 'Formulaire', 'SEO de base', 'Hébergement 1 an' ), 'featured' => false ),
			array( 'name' => 'BUSINESS', 'price' => '1 490 €','tag' => 'e-commerce / sur-mesure', 'highlights' => array( 'Paiement en ligne', 'SEO avancé', 'Maintenance 3 mois' ), 'featured' => true ),
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
			'cartes'   => array( 'label' => '🪪 Cartes de visite', 'desc' => '4 cartes recto + 4 verso par feuille A4 (8.5 × 5.4 cm)' ),
			'flyer'    => array( 'label' => '📄 Flyer A5', 'desc' => '2 flyers par feuille A4 (148 × 210 mm). Idéal pour boîtes aux lettres / commerces.' ),
			'sticker'  => array( 'label' => '🟡 Autocollants', 'desc' => '12 autocollants ronds 50 mm par feuille A4. Pour vitrines, ordis, voitures.' ),
			'affiche'  => array( 'label' => '🖼️ Affiche A4', 'desc' => '1 affiche pleine page A4 (210 × 297 mm). Pour vitrines pro, commerces partenaires.' ),
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
				$preview_url = wp_nonce_url( add_query_arg( array_filter( array( 'action' => 'ag_kp_print', 'type' => $slug, 'rec' => $picked, 'mode' => $mode ), function ( $v ) { return '' !== $v && null !== $v; } ), admin_url( 'admin-post.php' ) ), 'ag_kp_print' );
			?>
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px;">
					<h2 style="margin-top:0;font-size:1.15rem;"><?php echo esc_html( $kit['label'] ); ?></h2>
					<p style="color:#50575e;font-size:.9rem;min-height:48px;"><?php echo esc_html( $kit['desc'] ); ?></p>
					<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener" class="button button-primary">🖨️ Ouvrir l'aperçu imprimable</a>
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

	/* Branding tokens */
	:root { --gold: #D4B45C; --orange: #F37A1F; --black: #0a0a0f; --navy: #1a1a2e; }
	.brand-grad { background: linear-gradient(135deg, var(--black) 0%, var(--navy) 50%, var(--black) 100%); color: #fff; }
	.gold-grad  { background: linear-gradient(135deg, var(--gold) 0%, var(--orange) 100%); color: var(--black); }

	@page { size: A4 portrait; margin: 0; }
	@media print {
		body { background: #fff; }
		.toolbar { display: none; }
		.sheet { margin: 0; box-shadow: none; page-break-after: always; }
		.sheet:last-of-type { page-break-after: auto; }
	}

	/* ====================================================================
	   CARTES DE VISITE — 4 par feuille A4 (recto + verso)
	   8.5 × 5.4 cm  (standard FR)
	   ==================================================================== */
	.cards-grid { display: grid; grid-template-columns: repeat(2, 85mm); grid-template-rows: repeat(4, 54mm); gap: 4mm; padding: 14mm 16mm; justify-content: center; align-content: start; }
	.card { width: 85mm; height: 54mm; border-radius: 3mm; overflow: hidden; position: relative; box-shadow: 0 0 0 .1mm #ddd; }
	.card-front { padding: 5mm; display: flex; flex-direction: column; justify-content: space-between; color: #fff; }
	.card-front .logo { font-family: Georgia, serif; font-size: 14pt; font-weight: 800; letter-spacing: .5pt; color: var(--gold); }
	.card-front .logo small { display: block; font-size: 6.5pt; color: rgba(255,255,255,.65); letter-spacing: 2pt; margin-top: 1mm; font-weight: 400; font-family: Arial, sans-serif; }
	.card-front .name { font-size: 12pt; font-weight: 800; margin-top: auto; }
	.card-front .tagline { font-size: 7.5pt; color: var(--gold); margin-top: 1mm; }
	.card-front .qr-line { display: flex; align-items: center; gap: 2mm; margin-top: 2mm; font-size: 6pt; color: rgba(255,255,255,.7); }
	.card-front .qr-line img { width: 12mm; height: 12mm; border-radius: 1mm; background: #fff; padding: 1mm; }
	.card-back { padding: 4mm 5mm; display: flex; flex-direction: column; justify-content: space-between; }
	.card-back h3 { margin: 0; font-size: 10pt; color: var(--black); }
	.card-back .offers { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5mm; margin-top: 2mm; }
	.card-back .offer { background: rgba(10,10,15,.05); border: .2mm solid rgba(212,180,92,.4); border-radius: 1.5mm; padding: 1.5mm; text-align: center; }
	.card-back .offer.featured { background: var(--gold); color: var(--black); border-color: var(--orange); }
	.card-back .offer .n { font-size: 6.5pt; font-weight: 700; letter-spacing: .3pt; }
	.card-back .offer .p { font-size: 9pt; font-weight: 800; margin-top: .5mm; }
	.card-back .footer { display: flex; justify-content: space-between; align-items: flex-end; font-size: 6.5pt; color: #666; margin-top: 2mm; }
	.card-back .footer .url { color: var(--black); font-weight: 700; }
	.card-back .footer img { width: 14mm; height: 14mm; background: #fff; padding: 0; border-radius: 1mm; }

	/* ====================================================================
	   FLYER A5 — 2 par feuille A4
	   ==================================================================== */
	.a5-flex { display: flex; flex-direction: column; gap: 0; padding: 0; height: 297mm; }
	.flyer { width: 210mm; height: 148.5mm; padding: 12mm 14mm; display: grid; grid-template-columns: 1.4fr 1fr; gap: 8mm; align-items: center; position: relative; }
	.flyer .lead h2 { margin: 0; font-size: 24pt; line-height: 1.05; font-family: Georgia, serif; }
	.flyer .lead h2 .accent { color: var(--gold); }
	.flyer .lead p { margin: 4mm 0 0; font-size: 11pt; line-height: 1.45; color: rgba(255,255,255,.85); }
	.flyer .lead .offers { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3mm; margin-top: 6mm; }
	.flyer .lead .offer { background: rgba(255,255,255,.06); border: .3mm solid rgba(212,180,92,.5); border-radius: 2mm; padding: 4mm 3mm; text-align: center; }
	.flyer .lead .offer.featured { background: linear-gradient(135deg, var(--gold), var(--orange)); color: var(--black); border-color: var(--orange); }
	.flyer .lead .offer .n { font-size: 9pt; font-weight: 800; letter-spacing: .8pt; }
	.flyer .lead .offer .p { font-size: 16pt; font-weight: 900; margin-top: 1mm; }
	.flyer .lead .offer .t { font-size: 7pt; opacity: .85; margin-top: .5mm; }
	.flyer .qr-block { text-align: center; }
	.flyer .qr-block .scan { color: var(--gold); font-size: 12pt; font-weight: 800; letter-spacing: 1pt; margin-bottom: 3mm; }
	.flyer .qr-block img { width: 56mm; height: 56mm; background: #fff; padding: 2mm; border-radius: 3mm; }
	.flyer .qr-block .label { color: #fff; font-size: 9pt; margin-top: 3mm; }
	.flyer .qr-block .name { color: var(--gold); font-size: 10pt; font-weight: 700; margin-top: 1mm; }
	.flyer .footer-line { position: absolute; bottom: 6mm; left: 14mm; right: 14mm; display: flex; justify-content: space-between; align-items: center; color: rgba(255,255,255,.5); font-size: 7.5pt; }
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
	   AFFICHE A4 — 1 par feuille
	   ==================================================================== */
	.affiche { width: 210mm; height: 297mm; padding: 20mm 18mm; display: flex; flex-direction: column; }
	.affiche .head { color: var(--gold); font-family: Georgia, serif; font-size: 14pt; letter-spacing: 2pt; text-align: center; font-weight: 800; }
	.affiche h1 { margin: 4mm 0 2mm; font-size: 40pt; line-height: 1.05; text-align: center; font-family: Georgia, serif; color: #fff; }
	.affiche h1 .accent { color: var(--gold); }
	.affiche .sub { text-align: center; color: rgba(255,255,255,.85); font-size: 13pt; margin: 4mm 0 8mm; }
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
			<?php for ( $i = 0; $i < 4; $i++ ) : ?>
				<div class="card card-front brand-grad">
					<div class="logo">Alliance Groupe<small>AGENCE WEB &amp; IA</small></div>
					<div>
						<?php if ( 'brand' === $mode ) : ?>
							<div class="name">Votre site pro</div>
							<div class="tagline">en 7 jours — dès 490 €</div>
							<div class="qr-line">
								<img src="<?php echo esc_url( $qr_small ); ?>" alt="QR">
								<span>📞 07 44 82 95 16<br>alliancegroupe-inc.com</span>
							</div>
						<?php else : ?>
							<div class="name"><?php echo esc_html( $name ); ?></div>
							<div class="tagline">Ambassadeur officiel</div>
							<div class="qr-line">
								<img src="<?php echo esc_url( $qr_small ); ?>" alt="QR">
								<span>Scanne — site web pro dès 490 €<br>10 % offert à ton parrain</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<!-- Feuille verso : 4 cartes "offres" -->
	<div class="sheet" style="background:#fff;">
		<div class="cards-grid">
			<?php for ( $i = 0; $i < 4; $i++ ) : ?>
				<div class="card card-back">
					<h3>Site web pro <span style="color:var(--orange);">dès 490 €</span></h3>
					<div class="offers">
						<?php foreach ( $offers as $o ) : ?>
							<div class="offer <?php echo ! empty( $o['featured'] ) ? 'featured' : ''; ?>">
								<div class="n"><?php echo esc_html( $o['name'] ); ?></div>
								<div class="p"><?php echo esc_html( $o['price'] ); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="footer">
						<div>
							<div class="url">alliancegroupe-inc.com</div>
							<div>Scanne →</div>
						</div>
						<img src="<?php echo esc_url( $qr_medium ); ?>" alt="QR">
					</div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<?php

elseif ( 'flyer' === $type ) :
	?>
	<div class="sheet">
		<div class="a5-flex">
			<?php for ( $i = 0; $i < 2; $i++ ) : ?>
				<div class="flyer brand-grad">
					<div class="lead">
						<h2>Votre site web pro <span class="accent">en 7 jours.</span></h2>
						<p>Alliance Groupe — agence web &amp; IA. Site clé en main, design premium, optimisé Google.</p>
						<div class="offers">
							<?php foreach ( $offers as $o ) : ?>
								<div class="offer <?php echo ! empty( $o['featured'] ) ? 'featured' : ''; ?>">
									<div class="n"><?php echo esc_html( $o['name'] ); ?></div>
									<div class="p"><?php echo esc_html( $o['price'] ); ?></div>
									<div class="t"><?php echo esc_html( $o['tag'] ); ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="qr-block">
						<div class="scan">📱 SCANNE</div>
						<img src="<?php echo esc_url( $qr_big ); ?>" alt="QR">
						<div class="label">Devis gratuit en 1 min</div>
						<?php if ( 'brand' !== $mode ) : ?>
							<div class="name"><?php echo esc_html( $name ); ?></div>
						<?php else : ?>
							<div class="name">alliancegroupe-inc.com</div>
						<?php endif; ?>
					</div>
					<div class="footer-line">
						<div>alliancegroupe-inc.com · contact@alliancegroupe-inc.com</div>
						<div class="url">07 44 82 95 16</div>
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
			<?php for ( $i = 0; $i < 12; $i++ ) : ?>
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
			<div class="head">ALLIANCE GROUPE — AGENCE WEB &amp; IA</div>
			<h1>Votre site web pro<br><span class="accent">en 7 jours.</span></h1>
			<div class="sub">Design premium · 100 % responsive · livré clé en main · paiement en 4 × sans frais</div>
			<div class="offers">
				<?php foreach ( $offers as $o ) : ?>
					<div class="offer <?php echo ! empty( $o['featured'] ) ? 'featured' : ''; ?>">
						<div class="n"><?php echo esc_html( $o['name'] ); ?></div>
						<div class="p"><?php echo esc_html( $o['price'] ); ?></div>
						<div class="t"><?php echo esc_html( $o['tag'] ); ?></div>
						<ul>
							<?php foreach ( (array) $o['highlights'] as $h ) : ?>
								<li><?php echo esc_html( $h ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="qr-row">
				<img src="<?php echo esc_url( $qr_big ); ?>" alt="QR">
				<div class="text">
					<div class="scan">📱 SCANNE</div>
					<?php if ( 'brand' === $mode ) : ?>
						<div class="name">Devis gratuit · réponse sous 24 h · <strong>07 44 82 95 16</strong></div>
					<?php else : ?>
						<div class="name">Devis gratuit · réponse sous 24 h · <strong><?php echo esc_html( $name ); ?></strong></div>
					<?php endif; ?>
					<div class="url"><?php echo esc_html( $link ); ?></div>
				</div>
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
