<?php
/**
 * ag-composants-market.php — Marketplace des composants (créateurs vendeurs).
 *
 * Étend l'Espace Composants : un créateur peut proposer son composant en
 * GRATUIT ou en PAYANT (paliers abordables). L'acheteur paie, le créateur
 * reçoit sa part sur SON compte, et Alliance Groupe prélève une commission
 * (AG_COMPO_COMMISSION %). Les frais du processeur (Stripe/PayPal) sont à la
 * charge du vendeur, donc la commission est nette pour la plateforme.
 *
 * PHASE 1 (ce fichier) : modèle de prix, choix créateur, compte d'encaissement
 * du vendeur, verrouillage du téléchargement payant, droits d'accès (achats),
 * réglages admin. Le CŒUR de paiement partagé (Stripe Connect + PayPal Commerce)
 * = PHASE 2, branché quand les comptes plateforme sont configurés (gate
 * `ag_compo_market_ready()`), sur le modèle des autres outils : tant que ce
 * n'est pas configuré, les composants payants affichent « paiement bientôt actif ».
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Commission de la plateforme, en % (choix Fabrice : 8 %). */
if ( ! defined( 'AG_COMPO_COMMISSION' ) ) { define( 'AG_COMPO_COMMISSION', 8 ); }

/** Paliers de prix (en centimes). 0 = gratuit. Abordables vs Envato/Gumroad. */
function ag_compo_tiers() {
	return array( 0, 199, 299, 499, 999 );
}
/** Formatte un prix (centimes) en « 2,99 € » / « Gratuit ». */
function ag_compo_price_label( $cents ) {
	$cents = (int) $cents;
	if ( $cents <= 0 ) { return 'Gratuit'; }
	return number_format( $cents / 100, 2, ',', ' ' ) . ' €';
}

/** La marketplace est-elle prête à encaisser ? (Stripe Connect OU PayPal configuré + activée). */
function ag_compo_market_ready() {
	if ( '1' !== (string) get_option( 'ag_compo_market_on', '0' ) ) { return false; }
	$stripe = '' !== trim( (string) get_option( 'ag_compo_stripe_secret', '' ) ) && '' !== trim( (string) get_option( 'ag_compo_stripe_client_id', '' ) );
	$paypal = '' !== trim( (string) get_option( 'ag_compo_paypal_client_id', '' ) ) && '' !== trim( (string) get_option( 'ag_compo_paypal_secret', '' ) );
	return $stripe || $paypal;
}

/* ── Compte d'encaissement du vendeur (user meta) ────────────────────────── */
/** Le créateur a-t-il un moyen de recevoir l'argent connecté ? */
function ag_compo_seller_ready( $user_id ) {
	$user_id = (int) $user_id;
	$stripe  = get_user_meta( $user_id, 'ag_compo_stripe_acct', true );
	$paypal  = get_user_meta( $user_id, 'ag_compo_paypal_email', true );
	return ( $stripe || $paypal );
}

/** Enregistre le moyen d'encaissement choisi par le créateur (AJAX, connecté). */
add_action( 'wp_ajax_ag_compo_payout_save', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_composants' ) ) {
		wp_send_json_error( array( 'msg' => 'Connexion requise.' ) );
	}
	$uid    = get_current_user_id();
	$paypal = sanitize_email( wp_unslash( $_POST['paypal'] ?? '' ) );
	if ( $paypal ) { update_user_meta( $uid, 'ag_compo_paypal_email', $paypal ); }
	wp_send_json_success( array( 'msg' => 'Moyen de paiement enregistré. Tu peux maintenant vendre.' ) );
} );

/* ── Droits d'accès (achats) ─────────────────────────────────────────────── */
/** Clé d'identité d'un acheteur (user id si connecté, sinon email). */
function ag_compo_buyer_key( $email = '' ) {
	if ( is_user_logged_in() ) { return 'u' . get_current_user_id(); }
	$email = sanitize_email( $email );
	return $email ? 'e' . md5( strtolower( $email ) ) : '';
}
/** L'acheteur a-t-il accès à ce composant (acheté, ou auteur, ou admin) ? */
function ag_compo_has_access( $comp_id, $email = '' ) {
	if ( current_user_can( 'manage_options' ) ) { return true; }
	// Auteur du composant = accès à son propre composant.
	if ( is_user_logged_in() ) {
		$me = wp_get_current_user();
		foreach ( (array) get_option( 'ag_composants_user', array() ) as $c ) {
			if ( ( $c['id'] ?? '' ) === $comp_id && ( ( $c['author_email'] ?? '' ) === $me->user_email ) ) { return true; }
		}
	}
	$key = ag_compo_buyer_key( $email );
	if ( '' === $key ) { return false; }
	$buys = (array) get_option( 'ag_compo_purchases', array() );
	return ! empty( $buys[ $key ][ $comp_id ] );
}
/** Enregistre un achat (appelé par le webhook de paiement en Phase 2). */
function ag_compo_grant_access( $comp_id, $buyer_key, $txn = '' ) {
	if ( ! $comp_id || ! $buyer_key ) { return; }
	$buys = (array) get_option( 'ag_compo_purchases', array() );
	if ( ! isset( $buys[ $buyer_key ] ) ) { $buys[ $buyer_key ] = array(); }
	$buys[ $buyer_key ][ $comp_id ] = array( 'ts' => time(), 'txn' => $txn );
	update_option( 'ag_compo_purchases', $buys, false );
}

/** Retrouve un composant utilisateur par id (avec ses champs prix/vendeur). */
function ag_compo_find( $comp_id ) {
	foreach ( (array) get_option( 'ag_composants_user', array() ) as $c ) {
		if ( ( $c['id'] ?? '' ) === $comp_id ) { return $c; }
	}
	return null;
}
/** Prix (centimes) d'un composant ; 0 si gratuit/inconnu. */
function ag_compo_price_of( $comp_id ) {
	$c = ag_compo_find( $comp_id );
	if ( ! $c ) { return 0; } // les composants « seed » du thème sont gratuits
	return ( ( $c['mode'] ?? 'free' ) === 'paid' ) ? (int) ( $c['price'] ?? 0 ) : 0;
}

/* ── Verrou de téléchargement des composants PAYANTS ─────────────────────── */
// On intercepte AVANT le stream ZIP (priorité 9 < 10) : si le composant est
// payant et que le visiteur n'y a pas accès (pas acheté / pas auteur / pas admin),
// on bloque proprement au lieu de livrer le fichier.
add_action( 'template_redirect', function () {
	if ( empty( $_GET['ag_composant_zip'] ) ) { return; }
	$id    = sanitize_text_field( wp_unslash( $_GET['ag_composant_zip'] ) );
	$price = ag_compo_price_of( $id );
	if ( $price <= 0 ) { return; } // gratuit → laisse passer
	if ( ag_compo_has_access( $id ) ) { return; } // acheté → laisse passer
	wp_die(
		'<div style="font-family:sans-serif;max-width:520px;margin:60px auto;text-align:center">'
		. '<h1>🔒 Composant payant</h1><p>Ce composant coûte <strong>' . esc_html( ag_compo_price_label( $price ) )
		. '</strong>. Achète-le pour débloquer le téléchargement.</p>'
		. '<p><a href="' . esc_url( home_url( '/composants' ) ) . '">← Retour aux composants</a></p></div>',
		'Composant payant', array( 'response' => 402 )
	);
}, 9 );

/* ── Réglages admin : Marketplace composants ─────────────────────────────── */
add_action( 'admin_menu', function () {
	add_options_page( 'Marketplace composants', '🧩 Marketplace', 'manage_options', 'ag-compo-market', 'ag_compo_market_settings' );
} );

function ag_compo_market_settings() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( isset( $_POST['ag_compo_market_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ag_compo_market_nonce'] ) ), 'ag_compo_market' ) ) {
		foreach ( array( 'ag_compo_stripe_secret', 'ag_compo_stripe_pub', 'ag_compo_stripe_client_id', 'ag_compo_paypal_client_id', 'ag_compo_paypal_secret' ) as $opt ) {
			update_option( $opt, sanitize_text_field( wp_unslash( $_POST[ $opt ] ?? '' ) ) );
		}
		update_option( 'ag_compo_market_on', isset( $_POST['ag_compo_market_on'] ) ? '1' : '0' );
		echo '<div class="notice notice-success is-dismissible"><p>Réglages enregistrés.</p></div>';
	}
	$on = '1' === (string) get_option( 'ag_compo_market_on', '0' );
	?>
	<div class="wrap">
		<h1>🧩 Marketplace composants</h1>
		<p>Les créateurs vendent leurs composants ; Alliance Groupe prélève <strong><?php echo (int) AG_COMPO_COMMISSION; ?> %</strong> (les frais Stripe/PayPal sont à la charge du vendeur). État :
		<strong style="color:<?php echo ag_compo_market_ready() ? '#1f7a3d">✓ prête à encaisser' : '#b32d2e">non configurée'; ?></strong>.</p>
		<p style="background:#fff8e5;border-left:4px solid #dba617;padding:10px 14px;max-width:760px">
			<strong>Phase 2 :</strong> pour encaisser réellement, il faut activer <strong>Stripe Connect</strong> (dashboard.stripe.com → Connect) et/ou <strong>PayPal Commerce Platform</strong>, puis coller les clés ci-dessous. Tant que ce n'est pas fait, les composants payants affichent « paiement bientôt actif » (rien n'est cassé).
		</p>
		<form method="post">
			<?php wp_nonce_field( 'ag_compo_market', 'ag_compo_market_nonce' ); ?>
			<table class="form-table">
				<tr><th>Activer la marketplace</th><td><label><input type="checkbox" name="ag_compo_market_on" value="1" <?php checked( $on ); ?>> Autoriser les composants payants</label></td></tr>
				<tr><th colspan="2" style="padding-top:18px"><h2 style="margin:0">Stripe Connect</h2></th></tr>
				<tr><th><label>Clé secrète (sk_…)</label></th><td><input type="password" name="ag_compo_stripe_secret" value="<?php echo esc_attr( get_option( 'ag_compo_stripe_secret', '' ) ); ?>" class="regular-text" style="width:100%;max-width:520px"></td></tr>
				<tr><th><label>Clé publique (pk_…)</label></th><td><input type="text" name="ag_compo_stripe_pub" value="<?php echo esc_attr( get_option( 'ag_compo_stripe_pub', '' ) ); ?>" class="regular-text" style="width:100%;max-width:520px"></td></tr>
				<tr><th><label>Client ID Connect (ca_…)</label></th><td><input type="text" name="ag_compo_stripe_client_id" value="<?php echo esc_attr( get_option( 'ag_compo_stripe_client_id', '' ) ); ?>" class="regular-text" style="width:100%;max-width:520px"></td></tr>
				<tr><th colspan="2" style="padding-top:18px"><h2 style="margin:0">PayPal Commerce</h2></th></tr>
				<tr><th><label>Client ID</label></th><td><input type="text" name="ag_compo_paypal_client_id" value="<?php echo esc_attr( get_option( 'ag_compo_paypal_client_id', '' ) ); ?>" class="regular-text" style="width:100%;max-width:520px"></td></tr>
				<tr><th><label>Secret</label></th><td><input type="password" name="ag_compo_paypal_secret" value="<?php echo esc_attr( get_option( 'ag_compo_paypal_secret', '' ) ); ?>" class="regular-text" style="width:100%;max-width:520px"></td></tr>
			</table>
			<?php submit_button( 'Enregistrer' ); ?>
		</form>
	</div>
	<?php
}
