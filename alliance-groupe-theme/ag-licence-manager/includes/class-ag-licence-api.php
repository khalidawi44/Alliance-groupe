<?php
/**
 * REST API endpoints for licence operations.
 *
 * POST /wp-json/ag/v1/licence/activate
 * POST /wp-json/ag/v1/licence/verify
 * POST /wp-json/ag/v1/licence/deactivate
 * GET  /wp-json/ag/v1/update-check
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_API {

    /** Rate limit: max requests per minute per IP. */
    const RATE_LIMIT = 15;

    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    public static function register_routes() {
        $ns = 'ag/v1';

        register_rest_route( $ns, '/licence/activate', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'activate' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/licence/resend', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'resend_key' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/companion-update', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'companion_update' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/licence/verify', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'verify' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/licence/deactivate', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'deactivate' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/update-check', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'update_check' ),
            'permission_callback' => '__return_true',
        ) );

        // Secure package download (token-gated). Was referenced by update_check
        // but never registered -> now exists with strict validation.
        register_rest_route( $ns, '/download/(?P<slug>[a-z0-9\-]+)', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'download' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * Per-email rate-limit (anti-abuse on email-triggered actions).
     * Stricter than the IP limit: max 3 / hour / email.
     */
    private static function rate_limit_email( $email ) {
        $key  = 'ag_rle_' . md5( strtolower( $email ) );
        $hits = (int) get_transient( $key );
        if ( $hits >= 3 ) {
            return new WP_REST_Response(
                array( 'success' => false, 'error' => 'rate_limit', 'message' => 'Too many requests.' ),
                429
            );
        }
        set_transient( $key, $hits + 1, HOUR_IN_SECONDS );
        return null;
    }

    /**
     * Rate-limit check.
     */
    private static function rate_limit() {
        $ip  = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
        $key = 'ag_rl_' . md5( $ip );
        $hits = (int) get_transient( $key );
        if ( $hits >= self::RATE_LIMIT ) {
            return new WP_REST_Response(
                array( 'success' => false, 'error' => 'rate_limit', 'message' => 'Too many requests.' ),
                429
            );
        }
        set_transient( $key, $hits + 1, 60 );
        return null;
    }

    /**
     * Sign a response body with HMAC.
     */
    private static function signed_response( $data, $status = 200 ) {
        $body = wp_json_encode( $data );
        $sig  = hash_hmac( 'sha256', $body, AG_LICENCE_HMAC_KEY );
        $resp = new WP_REST_Response( $data, $status );
        $resp->header( 'X-AG-Signature', $sig );
        return $resp;
    }

    // ─── COMPANION UPDATE CHECK ─────────────────────────────────

    public static function companion_update( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $info = get_option( 'ag_lm_companion_version', array(
            'version'      => '1.9.0',
            'download_url' => home_url( '/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip' ),
            'url'          => home_url( '/templates-wordpress' ),
            'tested'       => '6.5',
            'requires'     => '6.0',
            'requires_php' => '7.4',
            'changelog'    => '<h4>v1.4.0</h4><ul>'
                . '<li>Nouveau : liens d\'achat directs vers Stripe (Premium/Business)</li>'
                . '<li>Nouveau : widget tableau de bord avec comparatif des 2 packs</li>'
                . '<li>Nouveau : 4 sections verrouillées dans le Customizer (aperçu des fonctionnalités Premium)</li>'
                . '<li>Nouveau : barre footer fixe "Passez à Premium" sur toutes les pages admin</li>'
                . '<li>Nouveau : auto-patch du thème (licence client + updater + premium-features)</li>'
                . '<li>Nouveau : mise à jour automatique du plugin depuis le serveur Alliance Groupe</li>'
                . '</ul>'
                . '<h4>v1.3.0</h4><ul>'
                . '<li>Auto-patch des thèmes avec fichiers licence et premium-features</li>'
                . '<li>Correction de compatibilité WordPress 6.5</li>'
                . '</ul>'
                . '<h4>v1.2.0</h4><ul>'
                . '<li>Import demo pour le thème Avocat (domaines d\'expertise CPT)</li>'
                . '<li>Bouton de réinitialisation du contenu demo</li>'
                . '</ul>'
                . '<h4>v1.0.0</h4><ul>'
                . '<li>Import en 1 clic des pages, menu et réglages</li>'
                . '<li>Support des 5 thèmes : Restaurant, Artisan, Coach, Avocat</li>'
                . '</ul>',
            'description'  => '<p><strong>AG Starter Companion</strong> est le plugin compagnon gratuit pour les thèmes AG Starter.</p>'
                . '<p>Il permet d\'installer en un clic tout le contenu demo : pages, menu principal, page d\'accueil, permaliens.</p>'
                . '<h4>Fonctionnalités</h4><ul>'
                . '<li>✅ Import demo en 1 clic (pages, menu, réglages)</li>'
                . '<li>✅ Compatible avec les 5 thèmes AG Starter (Restaurant, Artisan, Coach, Avocat, Barber)</li>'
                . '<li>✅ Réinitialisation du contenu demo</li>'
                . '<li>✅ Auto-patch : installe automatiquement le système de licence et les fonctionnalités Premium</li>'
                . '<li>✅ Mise à jour automatique depuis le serveur Alliance Groupe</li>'
                . '<li>✅ 100% gratuit, aucune inscription requise</li>'
                . '</ul>'
                . '<h4>Packs payants disponibles</h4>'
                . '<p>Débloquez des fonctionnalités avancées avec un paiement unique :</p><ul>'
                . '<li><strong>Premium (99€)</strong> — Header sticky, animations, couleurs avancées, footer personnalisable</li>'
                . '<li><strong>Business (149€)</strong> — Tout Premium + WooCommerce, multi-langue, pub reduite, session stratégique 30 min</li>'
                . '</ul>',
            'banners'      => array(
                'high' => home_url( '/wp-content/themes/alliance-groupe-theme/assets/images/promo-cards/ag-premium-card.png' ),
            ),
        ) );

        $info['stripe_urls'] = array(
            'premium'  => get_option( 'ag_stripe_premium_url', '' ),
            
            'business' => get_option( 'ag_stripe_business_url', '' ),
        );

        return new WP_REST_Response( $info );
    }

    // ─── RESEND KEY BY EMAIL ─────────────────────────────────

    public static function resend_key( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $email = sanitize_email( $req->get_param( 'email' ) );
        if ( empty( $email ) ) {
            return self::signed_response( array( 'success' => false, 'message' => 'Email requis.' ), 400 );
        }

        // Per-email throttle (in addition to the per-IP limit above).
        $rle = self::rate_limit_email( $email );
        if ( $rle ) return $rle;

        global $wpdb;
        $table = AG_Licence_DB::table();
        $licences = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE email = %s AND licence_key_enc IS NOT NULL ORDER BY created_at DESC",
            $email
        ) );

        // Send the keys only if the email actually has licences.
        if ( ! empty( $licences ) ) {
            foreach ( $licences as $l ) {
                $clear_key = AG_Licence_DB::decrypt_key( $l->licence_key_enc );
                if ( $clear_key ) {
                    AG_Licence_Email::send_licence( $l->email, $clear_key, $l->tier );
                }
            }
        }

        // SECURITY: always return the SAME generic response (and never the count)
        // so an attacker cannot tell whether an email is a customer (no enumeration).
        return self::signed_response( array(
            'success' => true,
            'message' => 'Si une licence est associée à cet email, elle vient d\'être renvoyée.',
        ) );
    }

    // ─── ACTIVATE ─────────────────────────────────────────────

    public static function activate( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $key    = sanitize_text_field( $req->get_param( 'licence_key' ) );
        $domain = sanitize_text_field( $req->get_param( 'domain' ) );

        if ( empty( $key ) || empty( $domain ) ) {
            return self::signed_response( array(
                'success' => false,
                'error'   => 'missing_params',
                'message' => 'licence_key and domain are required.',
            ), 400 );
        }

        $licence = AG_Licence_DB::find_by_key( $key );
        if ( ! $licence ) {
            return self::signed_response( array(
                'success' => false,
                'error'   => 'invalid_key',
                'message' => 'Clé de licence invalide.',
            ), 404 );
        }

        if ( 'revoked' === $licence->status ) {
            return self::signed_response( array(
                'success' => false,
                'error'   => 'revoked',
                'message' => 'Cette licence a été révoquée.',
            ), 403 );
        }

        if ( $licence->expires_at && strtotime( $licence->expires_at ) < time() ) {
            AG_Licence_DB::update( $licence->id, array( 'status' => 'expired' ) );
            return self::signed_response( array(
                'success' => false,
                'error'   => 'expired',
                'message' => 'Cette licence a expiré.',
            ), 403 );
        }

        // Already activated on a different domain?
        if ( $licence->domain && $licence->domain !== $domain && 'active' === $licence->status ) {
            return self::signed_response( array(
                'success' => false,
                'error'   => 'already_active',
                'message' => 'Licence déjà activée sur un autre domaine. Désactivez-la d\'abord.',
                'active_domain' => $licence->domain,
            ), 409 );
        }

        // Activate
        AG_Licence_DB::update( $licence->id, array(
            'domain'       => $domain,
            'status'       => 'active',
            'activated_at' => current_time( 'mysql' ),
            'last_check'   => current_time( 'mysql' ),
        ) );

        return self::signed_response( array(
            'success' => true,
            'tier'    => $licence->tier,
            'expires' => $licence->expires_at,
        ) );
    }

    // ─── VERIFY ───────────────────────────────────────────────

    public static function verify( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $key    = sanitize_text_field( $req->get_param( 'licence_key' ) );
        $domain = sanitize_text_field( $req->get_param( 'domain' ) );

        if ( empty( $key ) ) {
            return self::signed_response( array( 'valid' => false, 'error' => 'missing_key' ), 400 );
        }

        $licence = AG_Licence_DB::find_by_key( $key );
        if ( ! $licence ) {
            return self::signed_response( array( 'valid' => false, 'error' => 'invalid_key' ), 404 );
        }

        // Check expiration
        if ( $licence->expires_at && strtotime( $licence->expires_at ) < time() ) {
            AG_Licence_DB::update( $licence->id, array( 'status' => 'expired' ) );
            return self::signed_response( array( 'valid' => false, 'error' => 'expired' ) );
        }

        // Check domain match
        $valid = ( 'active' === $licence->status && $licence->domain === $domain );

        // Update last_check
        if ( $valid ) {
            AG_Licence_DB::update( $licence->id, array( 'last_check' => current_time( 'mysql' ) ) );
        }

        return self::signed_response( array(
            'valid'   => $valid,
            'tier'    => $licence->tier,
            'expires' => $licence->expires_at,
        ) );
    }

    // ─── DEACTIVATE ───────────────────────────────────────────

    public static function deactivate( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $key    = sanitize_text_field( $req->get_param( 'licence_key' ) );
        $domain = sanitize_text_field( $req->get_param( 'domain' ) );

        $licence = AG_Licence_DB::find_by_key( $key );
        if ( ! $licence ) {
            return self::signed_response( array( 'success' => false, 'error' => 'invalid_key' ), 404 );
        }

        if ( $licence->domain && $licence->domain !== $domain ) {
            return self::signed_response( array( 'success' => false, 'error' => 'domain_mismatch' ), 403 );
        }

        AG_Licence_DB::update( $licence->id, array(
            'domain' => null,
            'status' => 'inactive',
        ) );

        return self::signed_response( array( 'success' => true ) );
    }

    // ─── UPDATE CHECK ─────────────────────────────────────────

    public static function update_check( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $theme_slug = sanitize_key( $req->get_param( 'theme_slug' ) );
        $current_v  = sanitize_text_field( $req->get_param( 'current_version' ) );
        $key        = sanitize_text_field( $req->get_param( 'licence_key' ) );
        $domain     = sanitize_text_field( $req->get_param( 'domain' ) );

        if ( empty( $theme_slug ) || empty( $current_v ) ) {
            return self::signed_response( array( 'update_available' => false, 'error' => 'missing_params' ), 400 );
        }

        // Pro version info (stored as wp_option for easy management)
        $versions = get_option( 'ag_lm_pro_versions', array() );
        // Format: array( 'ag-starter-restaurant' => array( 'version' => '2.0.0', 'file' => 'ag-starter-restaurant-pro-2.0.0.zip' ), ... )

        if ( ! isset( $versions[ $theme_slug ] ) ) {
            return self::signed_response( array( 'update_available' => false ) );
        }

        $pro = $versions[ $theme_slug ];
        if ( version_compare( $current_v, $pro['version'], '>=' ) ) {
            return self::signed_response( array( 'update_available' => false ) );
        }

        // Check licence
        $has_licence = false;
        $tier = 'free';
        if ( $key && $domain ) {
            $licence = AG_Licence_DB::find_by_key( $key );
            if ( $licence && 'active' === $licence->status && $licence->domain === $domain ) {
                if ( ! $licence->expires_at || strtotime( $licence->expires_at ) > time() ) {
                    $has_licence = true;
                    $tier = $licence->tier;
                }
            }
        }

        $data = array(
            'update_available' => true,
            'new_version'      => $pro['version'],
            'requires'         => '6.0',
            'requires_php'     => '7.4',
            'tier'             => $tier,
        );

        if ( $has_licence ) {
            // Single-use, short-lived (15 min), domain-bound download token.
            $token = wp_generate_uuid4();
            set_transient( 'ag_dl_' . $token, array(
                'theme'  => $theme_slug,
                'file'   => $pro['file'],
                'domain' => $domain,
            ), 15 * MINUTE_IN_SECONDS );
            $data['download_url'] = rest_url( 'ag/v1/download/' . $theme_slug . '?token=' . $token );
        } else {
            $data['download_url'] = null;
            $data['upgrade_url']  = 'https://alliancegroupe-inc.com/templates-wordpress';
        }

        return self::signed_response( $data );
    }

    // ─── SECURE PACKAGE DOWNLOAD ──────────────────────────────

    public static function download( WP_REST_Request $req ) {
        $rl = self::rate_limit();
        if ( $rl ) return $rl;

        $slug  = sanitize_key( $req->get_param( 'slug' ) );
        $token = sanitize_text_field( $req->get_param( 'token' ) );

        if ( empty( $token ) ) {
            return new WP_REST_Response( array( 'error' => 'missing_token' ), 400 );
        }

        $tk = get_transient( 'ag_dl_' . $token );
        // Token must exist and match the requested theme slug.
        if ( ! is_array( $tk ) || empty( $tk['theme'] ) || $tk['theme'] !== $slug ) {
            return new WP_REST_Response( array( 'error' => 'invalid_token' ), 403 );
        }
        // Single use: burn the token immediately.
        delete_transient( 'ag_dl_' . $token );

        // Validate the file: basename only (no path traversal), .zip only.
        $file = basename( (string) $tk['file'] );
        if ( '' === $file || ! preg_match( '/\.zip$/i', $file ) ) {
            return new WP_REST_Response( array( 'error' => 'bad_file' ), 400 );
        }

        // Resolve against the downloads directory and confirm containment.
        $base = trailingslashit( apply_filters(
            'ag_lm_download_dir',
            trailingslashit( get_theme_root() ) . 'alliance-groupe-theme/assets/downloads/'
        ) );
        $real_base = realpath( $base );
        $path      = realpath( $base . $file );
        if ( ! $real_base || ! $path || 0 !== strpos( $path, $real_base ) || ! is_file( $path ) ) {
            return new WP_REST_Response( array( 'error' => 'not_found' ), 404 );
        }

        // Stream the package.
        nocache_headers();
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="' . $file . '"' );
        header( 'Content-Length: ' . filesize( $path ) );
        readfile( $path );
        exit;
    }
}
