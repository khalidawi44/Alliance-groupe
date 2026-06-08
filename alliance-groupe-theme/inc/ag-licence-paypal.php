<?php
/**
 * Alliance Groupe — Vente de la licence Premium via PayPal.
 *
 * Modèle 2 niveaux : Gratuit + Premium. Le Premium = le design le plus
 * abouti (ancien « Business »). Quand un paiement PayPal vérifié correspond
 * au montant Premium, on génère la clé (tier interne « business », qui
 * débloque le plugin élaboré) et on l'envoie au client par email.
 *
 * Prix unique : option `ag_creator_price` (par défaut 69 €) — partagée avec
 * le créateur de site, les fiches métier et le flux Merchant.
 *
 * Dépendances : inc/ag-paypal.php (webhook + hook ag_paypal_payment_verified)
 * et le plugin ag-licence-manager actif (AG_Licence_DB / AG_Licence_Email).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ag_licence_prices' ) ) {
	/** Montant PayPal exact -> tier de licence. Un seul tier payant. */
	function ag_licence_prices() {
		$prix = function_exists( 'ag_creator_price' ) ? (float) ag_creator_price() : 69;
		// Tier interne « business » = il débloque le plugin au design le plus
		// abouti. Affiché « Premium » côté client.
		return apply_filters( 'ag_licence_prices', array(
			'business' => $prix,
		) );
	}
}

/**
 * Génère + envoie la clé de licence sur paiement PayPal vérifié.
 * Uniquement sur PAYMENT.CAPTURE.COMPLETED (paiement unique définitif).
 */
add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn, $type = '', $resource = array() ) {
	if ( 'PAYMENT.CAPTURE.COMPLETED' !== $type ) return;
	if ( ! class_exists( 'AG_Licence_DB' ) ) return; // plugin Licences AG inactif
	$email = sanitize_email( (string) $email );
	if ( '' === $email ) return;

	// 1) Tier : via custom_id PayPal (ag_licence:business) si présent, sinon via le montant.
	$tier = '';
	$cid  = is_array( $resource ) ? (string) ( $resource['custom_id'] ?? ( $resource['invoice_id'] ?? '' ) ) : '';
	if ( 0 === stripos( $cid, 'ag_licence:' ) ) {
		$tier = sanitize_key( substr( $cid, strlen( 'ag_licence:' ) ) );
	} else {
		foreach ( ag_licence_prices() as $t => $p ) {
			if ( $p > 0 && abs( (float) $amount - (float) $p ) < 0.5 ) { $tier = $t; break; }
		}
	}
	if ( '' === $tier ) return;

	// 2) Idempotence : jamais deux clés pour la même transaction PayPal.
	global $wpdb;
	$tbl    = AG_Licence_DB::table();
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$tbl} WHERE stripe_session = %s", $txn ) ); // colonne réutilisée pour l'ID PayPal
	if ( $exists ) return;

	// 3) Génère, stocke, envoie la clé.
	$key = AG_Licence_DB::generate_key( $tier );
	$id  = AG_Licence_DB::insert( $key, $tier, $email, $txn, '' );
	if ( $id && class_exists( 'AG_Licence_Email' ) ) {
		AG_Licence_Email::send_licence( $email, $key, $tier );
	}

	// 4) Notif interne.
	$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
	wp_mail( $to, '🔑 Licence Premium vendue (PayPal)', "Une licence Premium a été générée et envoyée à {$email}.\nTransaction PayPal : {$txn}\nMontant : {$amount} €" );
	if ( function_exists( 'ag_push' ) ) ag_push( '🔑 Licence vendue', "Premium → {$email} ({$amount} €)" );
}, 10, 5 );

/* ── Réglages : Réglages > Licences PayPal ──────────────────────── */
add_action( 'admin_menu', function () {
	add_options_page( 'Licences PayPal', 'Licences PayPal', 'manage_options', 'ag-licence-paypal', 'ag_licence_paypal_render' );
} );
add_action( 'admin_init', function () {
	register_setting( 'ag_licence_paypal', 'ag_creator_price', array( 'type' => 'number', 'sanitize_callback' => 'floatval', 'default' => 69 ) );
} );
if ( ! function_exists( 'ag_licence_paypal_render' ) ) {
	function ag_licence_paypal_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$plugin_ok = class_exists( 'AG_Licence_DB' );
		$paypal_ok = function_exists( 'ag_paypal_cfg' ) && ag_paypal_cfg( 'client_id' ) && ag_paypal_cfg( 'webhook_id' );
		$prix      = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69;
		?>
		<div class="wrap">
			<h1>🔑 Licence Premium (PayPal)</h1>
			<p style="max-width:800px;color:#50575e;">Modèle <strong>Gratuit + Premium</strong>. Quand un client paie le <strong>montant exact</strong> de la licence Premium via PayPal, sa <strong>clé est générée et envoyée par email automatiquement</strong>. Le paiement passe par ton <strong>webhook PayPal</strong> déjà configuré.</p>

			<div style="max-width:800px;margin:14px 0;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid <?php echo ( $plugin_ok && $paypal_ok ) ? '#46b450' : '#dba617'; ?>;">
				<p style="margin:0 0 6px;"><strong>État :</strong></p>
				<ul style="margin:0 0 0 18px;">
					<li><?php echo $paypal_ok ? '✅' : '⚠️'; ?> Webhook PayPal <?php echo $paypal_ok ? 'configuré' : '— à configurer dans <em>Réglages → PayPal automatique</em>'; ?></li>
					<li><?php echo $plugin_ok ? '✅' : '⚠️'; ?> Plugin « Licences AG » <?php echo $plugin_ok ? 'actif' : '— active-le dans Extensions (sinon les clés ne peuvent pas être générées)'; ?></li>
				</ul>
			</div>

			<div style="max-width:800px;margin:14px 0;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<strong>Comment ça marche (1 fois) :</strong>
				<ol style="margin:8px 0 0 22px;line-height:1.8;">
					<li>Dans <strong>PayPal</strong>, crée un <strong>lien de paiement</strong> pour la licence Premium (<strong><?php echo (int) $prix; ?> €</strong>).</li>
					<li>Renseigne ce lien dans <strong>Réglages → Liens de paiement</strong> (champ Premium) — c'est lui qu'utilisent le créateur de site et les fiches.</li>
					<li>Renseigne ci-dessous le <strong>montant exact</strong> (<?php echo (int) $prix; ?> €) — il sert à reconnaître l'achat.</li>
					<li>C'est tout : à chaque paiement, la clé part toute seule par email. ✅</li>
				</ol>
				<p style="margin:8px 0 0;color:#50575e;">Ce montant Premium est <strong>partagé partout</strong> (créateur de site, fiches métier, flux Google Merchant) : tu ne le règles qu'ici.</p>
			</div>

			<form method="post" action="options.php" style="max-width:800px;">
				<?php settings_fields( 'ag_licence_paypal' ); ?>
				<table class="form-table">
					<tr><th scope="row"><label for="ag_creator_price">Montant licence Premium (€)</label></th><td><input type="number" step="1" min="1" name="ag_creator_price" id="ag_creator_price" value="<?php echo esc_attr( get_option( 'ag_creator_price', 69 ) ); ?>" class="small-text"></td></tr>
				</table>
				<p class="description" style="max-width:800px;">⚠️ Utilise un montant <strong>unique</strong> (différent des packs de site 490/890/1490 et de la maintenance) pour éviter toute confusion au moment de reconnaître le paiement.</p>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
