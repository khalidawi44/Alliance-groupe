<?php
/**
 * Alliance Groupe — Vente de licences de templates via PayPal.
 *
 * Remplace Stripe : quand un paiement PayPal vérifié correspond à un tier de
 * licence (Premium / Business), on génère la clé et on l'envoie au client par
 * email, en réutilisant le plugin « Licences AG » (AG_Licence_DB / _Email).
 *
 * Dépendances : inc/ag-paypal.php (webhook + hook ag_paypal_payment_verified)
 * et le plugin ag-licence-manager actif (classes AG_Licence_DB / AG_Licence_Email).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ag_licence_prices' ) ) {
	/** Montant PayPal exact -> tier de licence. */
	function ag_licence_prices() {
		return apply_filters( 'ag_licence_prices', array(
			'premium'  => (float) get_option( 'ag_licence_price_premium', 99 ),
			'business' => (float) get_option( 'ag_licence_price_business', 149 ),
		) );
	}
}

/**
 * Génère + envoie la clé de licence sur paiement PayPal vérifié.
 * On agit UNIQUEMENT sur PAYMENT.CAPTURE.COMPLETED (paiement unique définitif)
 * pour ne jamais créer deux clés pour un même achat.
 */
add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn, $type = '', $resource = array() ) {
	if ( 'PAYMENT.CAPTURE.COMPLETED' !== $type ) return;
	if ( ! class_exists( 'AG_Licence_DB' ) ) return; // plugin Licences AG inactif
	$email = sanitize_email( (string) $email );
	if ( '' === $email ) return;

	// 1) Tier : via custom_id PayPal (ag_licence:premium) si présent, sinon via le montant.
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
	wp_mail( $to, '🔑 Licence vendue (PayPal) — ' . $tier, "Une licence « {$tier} » a été générée et envoyée à {$email}.\nTransaction PayPal : {$txn}\nMontant : {$amount} €" );
	if ( function_exists( 'ag_push' ) ) ag_push( '🔑 Licence vendue', "Licence {$tier} → {$email} ({$amount} €)" );
}, 10, 5 );

/* ── Réglages : Réglages > Licences PayPal ──────────────────────── */
add_action( 'admin_menu', function () {
	add_options_page( 'Licences PayPal', 'Licences PayPal', 'manage_options', 'ag-licence-paypal', 'ag_licence_paypal_render' );
} );
add_action( 'admin_init', function () {
	register_setting( 'ag_licence_paypal', 'ag_licence_price_premium', array( 'type' => 'number', 'sanitize_callback' => 'floatval', 'default' => 99 ) );
	register_setting( 'ag_licence_paypal', 'ag_licence_price_business', array( 'type' => 'number', 'sanitize_callback' => 'floatval', 'default' => 149 ) );
} );
if ( ! function_exists( 'ag_licence_paypal_render' ) ) {
	function ag_licence_paypal_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$plugin_ok = class_exists( 'AG_Licence_DB' );
		$paypal_ok = function_exists( 'ag_paypal_cfg' ) && ag_paypal_cfg( 'client_id' ) && ag_paypal_cfg( 'webhook_id' );
		?>
		<div class="wrap">
			<h1>🔑 Licences de templates (PayPal)</h1>
			<p style="max-width:800px;color:#50575e;">Quand un client paie le <strong>montant exact</strong> d'une licence via PayPal, sa <strong>clé est générée et envoyée par email automatiquement</strong> — plus besoin de Stripe. Le paiement passe par ton <strong>webhook PayPal</strong> déjà configuré.</p>

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
					<li>Dans <strong>PayPal</strong>, crée un <strong>lien de paiement</strong> par licence (Premium 99 €, Business 149 €).</li>
					<li>Mets ces liens sur tes pages de templates (boutons « Acheter la licence »).</li>
					<li>Renseigne ci-dessous le <strong>montant exact</strong> de chaque licence — il sert à reconnaître l'achat.</li>
					<li>C'est tout : à chaque paiement, la clé part toute seule par email. ✅</li>
				</ol>
				<p style="margin:8px 0 0;color:#50575e;">Astuce (optionnel, plus précis) : si ton bouton PayPal envoie un <code>custom_id</code> du type <code>ag_licence:premium</code>, le tier est reconnu même si deux produits ont le même prix.</p>
			</div>

			<form method="post" action="options.php" style="max-width:800px;">
				<?php settings_fields( 'ag_licence_paypal' ); ?>
				<table class="form-table">
					<tr><th scope="row"><label for="ag_licence_price_premium">Montant licence Premium (€)</label></th><td><input type="number" step="0.01" name="ag_licence_price_premium" id="ag_licence_price_premium" value="<?php echo esc_attr( get_option( 'ag_licence_price_premium', 99 ) ); ?>" class="small-text"></td></tr>
					<tr><th scope="row"><label for="ag_licence_price_business">Montant licence Business (€)</label></th><td><input type="number" step="0.01" name="ag_licence_price_business" id="ag_licence_price_business" value="<?php echo esc_attr( get_option( 'ag_licence_price_business', 149 ) ); ?>" class="small-text"></td></tr>
				</table>
				<p class="description" style="max-width:800px;">⚠️ Utilise des montants <strong>uniques</strong> (différents des packs de site 490/890/1490 et de la maintenance) pour éviter toute confusion.</p>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
