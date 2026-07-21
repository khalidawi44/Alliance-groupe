<?php
/**
 * Alliance Groupe — PayPal automatique (webhooks REST)
 *
 * Quand un client paie via PayPal, PayPal envoie un webhook au site. On vérifie
 * la signature, puis on rapproche le paiement d'une vente en attente (créée via
 * le lien de l'ambassadeur) et on crédite automatiquement la commission.
 *
 * Réglages : Réglages > PayPal automatique (Client ID, Secret, Webhook ID...).
 * URL du webhook à coller dans PayPal : admin-post.php?action=ag_paypal_webhook
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Helpers config ─────────────────────────────────────────────── */
if ( ! function_exists( 'ag_paypal_cfg' ) ) {
	function ag_paypal_cfg( $key, $default = '' ) { return trim( (string) get_option( 'ag_paypal_' . $key, $default ) ); }
}
if ( ! function_exists( 'ag_paypal_api_base' ) ) {
	function ag_paypal_api_base() {
		return ( 'sandbox' === ag_paypal_cfg( 'mode', 'live' ) ) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
	}
}
if ( ! function_exists( 'ag_paypal_token' ) ) {
	/** Jeton d'accès OAuth (client credentials). */
	function ag_paypal_token() {
		$id  = ag_paypal_cfg( 'client_id' );
		$sec = ag_paypal_cfg( 'secret' );
		if ( '' === $id || '' === $sec ) return '';
		$resp = wp_remote_post( ag_paypal_api_base() . '/v1/oauth2/token', array(
			'timeout' => 20,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $id . ':' . $sec ),
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'body'    => 'grant_type=client_credentials',
		) );
		if ( is_wp_error( $resp ) ) return '';
		$d = json_decode( wp_remote_retrieve_body( $resp ), true );
		return is_array( $d ) && ! empty( $d['access_token'] ) ? $d['access_token'] : '';
	}
}

/* ── Page de réglages ───────────────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-hub', 'PayPal automatique', '💳 PayPal auto', 'manage_options', 'ag-paypal-cfg', 'ag_paypal_cfg_render' );
}, 20 );
add_action( 'admin_init', function () {
	foreach ( array( 'mode', 'client_id', 'secret', 'webhook_id', 'email' ) as $k ) {
		register_setting( 'ag_paypal_cfg', 'ag_paypal_' . $k, array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => ( 'mode' === $k ? 'live' : '' ) ) );
	}
} );
if ( ! function_exists( 'ag_paypal_cfg_render' ) ) {
	function ag_paypal_cfg_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$hook_url = admin_url( 'admin-post.php?action=ag_paypal_webhook' );
		$active   = ag_paypal_cfg( 'client_id' ) && ag_paypal_cfg( 'webhook_id' );
		?>
		<div class="wrap">
			<h1>PayPal automatique</h1>
			<p style="max-width:780px;color:#50575e;">Crédite <strong>automatiquement</strong> la commission de l'ambassadeur dès que le client paie. Il faut créer une <strong>app PayPal</strong> (gratuit) pour obtenir les identifiants, puis ajouter un <strong>webhook</strong> qui pointe vers ton site.</p>
			<div style="max-width:780px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<strong>Étapes (10 min) :</strong>
				<ol style="margin:8px 0 0 22px;line-height:1.8;">
					<li>Va sur <a href="https://developer.paypal.com/dashboard/applications/live" target="_blank" rel="noopener">developer.paypal.com → Apps &amp; Credentials</a> (mode <strong>Live</strong>).</li>
					<li>« Create App » → nom « Alliance Groupe » → copie le <strong>Client ID</strong> et le <strong>Secret</strong> ci-dessous.</li>
					<li>Dans l'app, section <strong>Webhooks</strong> → « Add Webhook ». URL à coller :<br><code style="user-select:all;"><?php echo esc_html( $hook_url ); ?></code></li>
					<li>Coche les évènements : <strong>Payment capture completed</strong>, <strong>Payment sale completed</strong>, <strong>Checkout order approved</strong>.</li>
					<li>Enregistre, puis copie le <strong>Webhook ID</strong> affiché et colle-le ci-dessous.</li>
					<li>Mets ton <strong>email PayPal</strong> (celui qui reçoit les paiements) pour sécuriser.</li>
				</ol>
			</div>
			<form method="post" action="options.php" style="max-width:780px;">
				<?php settings_fields( 'ag_paypal_cfg' ); ?>
				<table class="form-table">
					<tr><th scope="row">Mode</th><td>
						<label><input type="radio" name="ag_paypal_mode" value="live" <?php checked( ag_paypal_cfg( 'mode', 'live' ), 'live' ); ?>> Live (réel)</label>
						&nbsp;&nbsp;<label><input type="radio" name="ag_paypal_mode" value="sandbox" <?php checked( ag_paypal_cfg( 'mode', 'live' ), 'sandbox' ); ?>> Sandbox (test)</label>
					</td></tr>
					<tr><th scope="row"><label for="ag_paypal_client_id">Client ID</label></th><td><input type="text" name="ag_paypal_client_id" id="ag_paypal_client_id" value="<?php echo esc_attr( ag_paypal_cfg( 'client_id' ) ); ?>" class="regular-text" style="width:100%;max-width:560px;"></td></tr>
					<tr><th scope="row"><label for="ag_paypal_secret">Secret</label></th><td><input type="password" name="ag_paypal_secret" id="ag_paypal_secret" value="<?php echo esc_attr( ag_paypal_cfg( 'secret' ) ); ?>" class="regular-text" style="width:100%;max-width:560px;" autocomplete="new-password"></td></tr>
					<tr><th scope="row"><label for="ag_paypal_webhook_id">Webhook ID</label></th><td><input type="text" name="ag_paypal_webhook_id" id="ag_paypal_webhook_id" value="<?php echo esc_attr( ag_paypal_cfg( 'webhook_id' ) ); ?>" class="regular-text" style="width:100%;max-width:560px;"></td></tr>
					<tr><th scope="row"><label for="ag_paypal_email">Email PayPal (réception)</label></th><td><input type="email" name="ag_paypal_email" id="ag_paypal_email" value="<?php echo esc_attr( ag_paypal_cfg( 'email' ) ); ?>" class="regular-text" style="width:100%;max-width:560px;"><p class="description"><?php echo $active ? '✓ PayPal automatique actif.' : 'Tant que Client ID + Webhook ID sont vides, le crédit reste manuel.'; ?></p></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

/* ── Rapprochement paiement -> vente -> commission ──────────────── */
if ( ! function_exists( 'ag_paypal_apply_payment' ) ) {
	/**
	 * Applique un paiement confirmé : crédite la vente en attente correspondante,
	 * ou le met en réserve si la vente n'existe pas encore (brief pas encore envoyé).
	 */
	function ag_paypal_apply_payment( $amount, $email, $txn ) {
		$amount = (float) $amount;
		$email  = strtolower( trim( (string) $email ) );
		$txn    = (string) $txn;

		// Idempotence : ne traite jamais deux fois la même transaction.
		$done = (array) get_option( 'ag_paypal_processed', array() );
		if ( $txn && in_array( $txn, $done, true ) ) return 'duplicate';
		if ( $txn ) { $done[] = $txn; update_option( 'ag_paypal_processed', array_slice( $done, -500 ) ); }

		// Zone supplémentaire ambassadeur (paiement unique) : +1 zone au quota, automatiquement.
		if ( function_exists( 'ag_zone_extra_activate_by_email' ) && ag_zone_extra_activate_by_email( $email, $amount ) ) return 'zone_extra';

		// Abonnement Chasseur Pro (~19 €) : débloque la recherche de l'ambassadeur, automatiquement.
		if ( function_exists( 'ag_chasseur_activate_by_email' ) && ag_chasseur_activate_by_email( $email, $amount ) ) return 'chasseur';

		if ( ag_paypal_credit_matching_sale( $amount, $email ) ) return 'credited';

		// Pas de vente en attente correspondante (email) : on réserve le paiement
		// et on alerte pour rapprochement manuel (évite de créditer au hasard).
		$prices = array_map( 'floatval', (array) apply_filters( 'ag_express_prices', array( 'essentiel' => 490, 'pro' => 890, 'boutique' => 1490 ) ) );
		if ( in_array( round( $amount ), array_map( 'round', $prices ), true ) ) {
			$pending = (array) get_option( 'ag_paypal_payments', array() );
			$pending[] = array( 'amount' => $amount, 'email' => $email, 'txn' => $txn, 'ts' => time() );
			update_option( 'ag_paypal_payments', array_slice( $pending, -200 ) );
			if ( function_exists( 'ag_calendar_notify' ) ) {
				ag_calendar_notify( '🕓 Paiement PayPal à rapprocher manuellement', 'Paiement de ' . $amount . ' € (' . $email . ') reçu sans vente déclarée correspondante. À rapprocher dans Réglages → PayPal.' );
			}
			return 'reserved';
		}
		return 'ignored';
	}
}
if ( ! function_exists( 'ag_paypal_credit_matching_sale' ) ) {
	/** Trouve une vente 'declaree' qui colle (montant + email si possible) et la passe 'payee'. */
	function ag_paypal_credit_matching_sale( $amount, $email ) {
		$ventes = get_option( 'ag_ambassadeur_ventes', array() );
		if ( ! is_array( $ventes ) ) return false;
		// SECURITY: ne créditer QUE sur correspondance email (montant + email).
		// Le rapprochement « montant seul » créditait potentiellement la mauvaise
		// vente / le mauvais ambassadeur. Sans email correspondant, on retourne
		// false : le paiement part en réserve pour validation manuelle.
		$best = -1;
		foreach ( $ventes as $i => $v ) {
			if ( ( $v['statut'] ?? '' ) !== 'declaree' ) continue;
			if ( abs( (float) ( $v['montant'] ?? 0 ) - $amount ) > 0.5 ) continue;
			$ve = strtolower( (string) ( $v['client_email'] ?? '' ) );
			if ( $email && $ve && $ve === $email ) { $best = $i; break; } // correspondance email = requise
		}
		if ( $best < 0 ) return false;
		$ventes[ $best ]['statut']        = 'payee';
		$ventes[ $best ]['date_paiement'] = current_time( 'd/m/Y H:i' );
		update_option( 'ag_ambassadeur_ventes', $ventes );
		$v = $ventes[ $best ];
		wp_mail(
			apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' ),
			'💰 Commission créditée (paiement PayPal) : ' . ( $v['name'] ?? '' ),
			"Paiement confirmé par PayPal.\nAmbassadeur : " . ( $v['name'] ?? '' ) . "\nClient : " . ( $v['client'] ?? '' ) . "\nMontant : " . ( $v['montant'] ?? 0 ) . " €\nCommission créditée : " . ( $v['commission'] ?? 0 ) . " €"
		);
		if ( function_exists( 'ag_calendar_notify' ) ) {
			ag_calendar_notify( '💰 Commission créditée : ' . ( $v['name'] ?? '' ), 'Paiement PayPal confirmé. Commission ' . ( $v['commission'] ?? 0 ) . ' € créditée pour ' . ( $v['client'] ?? '' ) . '.' );
		}
		if ( function_exists( 'ag_award_parrain_prime' ) && ! empty( $v['email'] ) ) ag_award_parrain_prime( $v['email'] );
		return true;
	}
}
// Rapprochement inverse : à la création d'une vente (brief), si un paiement était déjà en réserve.
add_action( 'ag_client_brief_submitted', function ( $email, $name, $pack = '' ) {
	$pending = (array) get_option( 'ag_paypal_payments', array() );
	if ( empty( $pending ) ) return;
	$email  = strtolower( (string) $email );
	$prices = array_map( 'floatval', (array) apply_filters( 'ag_express_prices', array( 'essentiel' => 490, 'pro' => 890, 'boutique' => 1490 ) ) );
	$amount = isset( $prices[ $pack ] ) ? (float) $prices[ $pack ] : 0;
	foreach ( $pending as $k => $p ) {
		$okAmount = $amount > 0 ? ( abs( (float) $p['amount'] - $amount ) < 0.5 ) : true;
		$okEmail  = ( $email && strtolower( (string) $p['email'] ) === $email ) || ! $p['email'];
		if ( $okAmount && $okEmail && ag_paypal_credit_matching_sale( (float) $p['amount'], $email ) ) {
			unset( $pending[ $k ] );
			update_option( 'ag_paypal_payments', array_values( $pending ) );
			break;
		}
	}
}, 30, 3 );

/* ── Listener webhook ───────────────────────────────────────────── */
if ( ! function_exists( 'ag_paypal_webhook_handler' ) ) {
	function ag_paypal_webhook_handler() {
		$raw = file_get_contents( 'php://input' );
		$ev  = json_decode( $raw, true );
		if ( ! is_array( $ev ) ) { status_header( 400 ); exit; }

		// Vérification de signature côté PayPal.
		$h = function ( $name ) {
			$key = 'HTTP_' . strtoupper( str_replace( '-', '_', $name ) );
			return isset( $_SERVER[ $key ] ) ? $_SERVER[ $key ] : '';
		};
		$token = ag_paypal_token();
		if ( '' === $token || '' === ag_paypal_cfg( 'webhook_id' ) ) { status_header( 503 ); exit; }
		$verify = wp_remote_post( ag_paypal_api_base() . '/v1/notifications/verify-webhook-signature', array(
			'timeout' => 20,
			'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'auth_algo'         => $h( 'PAYPAL-AUTH-ALGO' ),
				'cert_url'          => $h( 'PAYPAL-CERT-URL' ),
				'transmission_id'   => $h( 'PAYPAL-TRANSMISSION-ID' ),
				'transmission_sig'  => $h( 'PAYPAL-TRANSMISSION-SIG' ),
				'transmission_time' => $h( 'PAYPAL-TRANSMISSION-TIME' ),
				'webhook_id'        => ag_paypal_cfg( 'webhook_id' ),
				'webhook_event'     => $ev,
			) ),
		) );
		if ( is_wp_error( $verify ) ) { status_header( 500 ); exit; }
		$vd = json_decode( wp_remote_retrieve_body( $verify ), true );
		if ( ! is_array( $vd ) || ( $vd['verification_status'] ?? '' ) !== 'SUCCESS' ) { status_header( 400 ); exit; }

		$type = $ev['event_type'] ?? '';
		$r    = $ev['resource'] ?? array();
		$amount = 0; $payer = '';
		if ( 'PAYMENT.CAPTURE.COMPLETED' === $type ) {
			$amount = (float) ( $r['amount']['value'] ?? 0 );
			$payer  = $r['payer']['email_address'] ?? ( $r['payer_email'] ?? '' );
		} elseif ( 'PAYMENT.SALE.COMPLETED' === $type ) {
			$amount = (float) ( $r['amount']['total'] ?? 0 );
			$payer  = $r['payer']['email_address'] ?? '';
		} elseif ( 'CHECKOUT.ORDER.APPROVED' === $type ) {
			$pu     = $r['purchase_units'][0] ?? array();
			$amount = (float) ( $pu['amount']['value'] ?? 0 );
			$payer  = $r['payer']['email_address'] ?? '';
		} else {
			status_header( 200 ); exit; // évènement non géré : on accuse réception.
		}
		do_action( 'ag_paypal_payment_verified', $amount, $payer, (string) ( $r['id'] ?? '' ), $type, $r );
		ag_paypal_apply_payment( $amount, $payer, (string) ( $r['id'] ?? '' ) );
		status_header( 200 ); exit;
	}
}
add_action( 'admin_post_nopriv_ag_paypal_webhook', 'ag_paypal_webhook_handler' );
add_action( 'admin_post_ag_paypal_webhook', 'ag_paypal_webhook_handler' );
