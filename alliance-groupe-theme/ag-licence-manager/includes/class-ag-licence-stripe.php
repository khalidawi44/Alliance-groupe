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

        // Verify signature if secret is configured
        $secret = defined( 'AG_STRIPE_WEBHOOK_SECRET' ) ? AG_STRIPE_WEBHOOK_SECRET : '';
        if ( $secret && $sig ) {
            if ( ! self::verify_signature( $payload, $sig, $secret ) ) {
                return new WP_REST_Response( array( 'error' => 'Invalid signature' ), 403 );
            }
        }

        $event = json_decode( $payload, true );
        if ( ! $event || ! isset( $event['type'] ) ) {
            return new WP_REST_Response( array( 'error' => 'Invalid payload' ), 400 );
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

        // Determine tier from metadata or amount
        $tier = 'pro'; // default
        $metadata = $session['metadata'] ?? array();
        if ( ! empty( $metadata['ag_tier'] ) ) {
            $tier = sanitize_key( $metadata['ag_tier'] );
        } else {
            // Fallback: determine from amount (in cents)
            $amount = intval( $session['amount_total'] ?? 0 );
            if ( $amount >= 14900 ) {
                $tier = 'business';
            
                
            } else {
                $tier = 'pro';
            }
        }

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
