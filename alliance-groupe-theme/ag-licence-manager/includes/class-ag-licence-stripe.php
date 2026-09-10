<?php
/**
 * Stripe webhook handler: auto-generate licence on checkout.session.completed.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_Stripe {

    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_webhook' ) );
    }

    public static function register_webhook() {
        register_rest_route( 'ag/v1', '/stripe-webhook', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'handle' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public static function handle( WP_REST_Request $req ) {
        $payload = $req->get_body();
        $sig     = $req->get_header( 'stripe-signature' );

        // SECURITY: the signature is MANDATORY. Without it, anyone could POST a
        // forged "checkout.session.completed" and mint free licences.
        $secret = defined( 'AG_STRIPE_WEBHOOK_SECRET' ) ? AG_STRIPE_WEBHOOK_SECRET : '';
        if ( empty( $secret ) ) {
            // Misconfiguration: refuse rather than process unverified events.
            return new WP_REST_Response( array( 'error' => 'Webhook secret not configured' ), 503 );
        }
        if ( empty( $sig ) || ! self::verify_signature( $payload, $sig, $secret ) ) {
            return new WP_REST_Response( array( 'error' => 'Invalid signature' ), 403 );
        }

        $event = json_decode( $payload, true );
        if ( ! $event || ! isset( $event['type'] ) ) {
            return new WP_REST_Response( array( 'error' => 'Invalid payload' ), 400 );
        }

        /*
         * ABONNEMENTS. Stripe n'envoie « checkout.session.completed » qu'au
         * PREMIER paiement. Les mois suivants arrivent en « invoice.paid » /
         * « invoice.payment_succeeded » — que ce gestionnaire ignorait, donc
         * aucun renouvellement n'etait vu par le site.
         * On relaie l'evenement maison pour ces factures aussi : les modules
         * abonnes (maquettes IA, commissions, avis clients) savent deja
         * reconnaitre un renouvellement et ne recommandent rien deux fois.
         * La licence, elle, ne se genere QUE sur le checkout : on sort avant.
         */
        if ( in_array( $event['type'], array( 'invoice.paid', 'invoice.payment_succeeded' ), true ) ) {
            $inv    = $event['data']['object'] ?? array();
            $mail   = sanitize_email( $inv['customer_email'] ?? ( $inv['customer_details']['email'] ?? '' ) );
            $paye   = round( intval( $inv['amount_paid'] ?? 0 ) / 100, 2 );
            $ref    = sanitize_text_field( $inv['id'] ?? '' );
            $billing= (string) ( $inv['billing_reason'] ?? '' );

            // « subscription_create » est la facture du premier paiement : elle
            // double le checkout deja relaye. On ne compte pas deux fois.
            if ( $mail && $paye > 0 && 'subscription_create' !== $billing ) {
                do_action( 'ag_paypal_payment_verified', $paye, $mail, $ref, 'INVOICE.PAID', $inv );
            }
            return new WP_REST_Response( array( 'received' => true, 'renouvellement' => true ) );
        }

        // Only handle checkout.session.completed
        if ( 'checkout.session.completed' !== $event['type'] ) {
            return new WP_REST_Response( array( 'received' => true ) );
        }

        $session = $event['data']['object'] ?? array();
        $email   = sanitize_email( $session['customer_details']['email'] ?? $session['customer_email'] ?? '' );
        $sess_id = sanitize_text_field( $session['id'] ?? '' );

        if ( ! $email ) {
            return new WP_REST_Response( array( 'error' => 'No email in session' ), 400 );
        }

        // Tier : modèle 2 niveaux (Gratuit + Premium). Le seul tier payant est
        // « Premium » = design le plus abouti (interne « business »).
        $tier = 'business';
        $metadata = $session['metadata'] ?? array();
        if ( ! empty( $metadata['ag_tier'] ) ) {
            $tier = sanitize_key( $metadata['ag_tier'] );
        }
        $amount_eur = round( intval( $session['amount_total'] ?? 0 ) / 100, 2 );

        // Check if a licence already exists for this session (idempotency)
        global $wpdb;
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM " . AG_Licence_DB::table() . " WHERE stripe_session = %s",
            $sess_id
        ) );
        if ( $existing ) {
            return new WP_REST_Response( array( 'received' => true, 'duplicate' => true ) );
        }

        // Generate licence
        $clear_key  = AG_Licence_DB::generate_key( $tier );
        $theme_slug = sanitize_key( $metadata['ag_theme'] ?? '' );
        $id = AG_Licence_DB::insert( $clear_key, $tier, $email, $sess_id, $theme_slug );

        if ( $id ) {
            AG_Licence_Email::send_licence( $email, $clear_key, $tier );
        }

        // Évènement unifié « paiement vérifié » : déclenche les autres
        // automatismes (créateur de site -> ZIP perso, Google Avis clients).
        // La licence vient d'être insérée avec stripe_session = $sess_id, donc
        // le listener licence (ag-licence-paypal) verra le doublon et n'en
        // recréera pas -> pas de double clé.
        do_action( 'ag_paypal_payment_verified', $amount_eur, $email, $sess_id, 'PAYMENT.CAPTURE.COMPLETED', $session );

        return new WP_REST_Response( array( 'received' => true, 'licence_created' => (bool) $id ) );
    }

    /**
     * Verify Stripe webhook signature (simplified, no Stripe SDK needed).
     */
    private static function verify_signature( $payload, $sig_header, $secret ) {
        $parts = array();
        foreach ( explode( ',', $sig_header ) as $item ) {
            $kv = explode( '=', $item, 2 );
            if ( count( $kv ) === 2 ) {
                $parts[ trim( $kv[0] ) ] = trim( $kv[1] );
            }
        }

        if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) {
            return false;
        }

        $timestamp   = $parts['t'];
        $expected    = $parts['v1'];
        $signed_data = $timestamp . '.' . $payload;
        $computed    = hash_hmac( 'sha256', $signed_data, $secret );

        // Timing-safe comparison
        if ( ! hash_equals( $computed, $expected ) ) {
            return false;
        }

        // Reject events older than 5 minutes (replay protection)
        if ( abs( time() - intval( $timestamp ) ) > 300 ) {
            return false;
        }

        return true;
    }
}
