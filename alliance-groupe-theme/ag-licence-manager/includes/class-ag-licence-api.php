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
        $info = get_option( 'ag_lm_companion_version', array(
            'version'      => '1.4.0',
            'download_url' => home_url( '/wp-content/themes/alliance-groupe-theme/assets/downloads/ag-starter-companion.zip' ),
            'url'          => home_url( '/templates-wordpress' ),
            'tested'       => '6.5',
            'requires'     => '6.0',
            'requires_php' => '7.4',
            'changelog'    => '<h4>v1.4.0</h4><ul>'
                . '<li>Nouveau : liens d\'achat directs vers Stripe (Pro/Premium/Business)</li>'
                . '<li>Nouveau : widget tableau de bord avec comparatif des 3 packs</li>'
                . '<li>Nouveau : 4 sections verrouillées dans le Customizer (aperçu des fonctionnalités Pro)</li>'
                . '<li>Nouveau : barre footer fixe "Passez à Pro" sur toutes les pages admin</li>'
                . '<li>Nouveau : auto-patch du thème (licence client + updater + pro-features)</li>'
                . '<li>Nouveau : mise à jour automatique du plugin depuis le serveur Alliance Groupe</li>'
                . '</ul>'
                . '<h4>v1.3.0</h4><ul>'
                . '<li>Auto-patch des thèmes avec fichiers licence et pro-features</li>'
                . '<li>Correction de compatibilité WordPress 6.5</li>'
                . '</ul>'
                . '<h4>v1.2.0</h4><ul>'
                . '<li>Import demo pour le thème Avocat (domaines d\'expertise CPT)</li>'
                . '<li>Bouton de réinitialisation du contenu demo</li>'
                . '</ul>'
                . '<h4>v1.0.0</h4><ul>'
                . '<li>Import en 1 clic des pages, menu et réglages</li>'
                . '<li>Support des 4 thèmes : Restaurant, Artisan, Coach, Avocat</li>'
                . '</ul>',
            'description'  => '<p><strong>AG Starter Companion</strong> est le plugin compagnon gratuit pour les thèmes AG Starter.</p>'
                . '<p>Il permet d\'installer en un clic tout le contenu demo : pages, menu principal, page d\'accueil, permaliens.</p>'
                . '<h4>Fonctionnalités</h4><ul>'
                . '<li>✅ Import demo en 1 clic (pages, menu, réglages)</li>'
                . '<li>✅ Compatible avec les 4 thèmes AG Starter (Restaurant, Artisan, Coach, Avocat)</li>'
                . '<li>✅ Réinitialisation du contenu demo</li>'
                . '<li>✅ Auto-patch : installe automatiquement le système de licence et les fonctionnalités Pro</li>'
                . '<li>✅ Mise à jour automatique depuis le serveur Alliance Groupe</li>'
                . '<li>✅ 100% gratuit, aucune inscription requise</li>'
                . '</ul>'
                . '<h4>Packs payants disponibles</h4>'
                . '<p>Débloquez des fonctionnalités avancées avec un paiement unique :</p><ul>'
                . '<li><strong>Pro (49€)</strong> — Header sticky, animations, couleurs avancées, footer personnalisable</li>'
                . '<li><strong>Premium (99€)</strong> — Tout Pro + témoignages, galerie, boutique WooCommerce, 6 langues</li>'
                . '<li><strong>Business (149€)</strong> — Tout Premium + white-label, templates extra, session stratégique 30 min</li>'
                . '</ul>',
            'banners'      => array(
                'high' => home_url( '/wp-content/themes/alliance-groupe-theme/assets/images/promo-cards/ag-pro-card.png' ),
            ),
        ) );

        $info['stripe_urls'] = array(
            'pro'      => get_option( 'ag_stripe_pro_url', '' ),
            'premium'  => get_option( 'ag_stripe_premium_url', '' ),
            'business' => get_option( 'ag_stripe_business_url', '' ),
        );

        return new WP_REST_Response( $info );
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
            // Generate a signed temporary download token (valid 1 hour)
            $token = wp_generate_uuid4();
            set_transient( 'ag_dl_' . $token, array(
                'theme' => $theme_slug,
                'file'  => $pro['file'],
            ), HOUR_IN_SECONDS );
            $data['download_url'] = rest_url( 'ag/v1/download/' . $theme_slug . '?token=' . $token );
        } else {
            $data['download_url'] = null;
            $data['upgrade_url']  = 'https://alliancegroupe-inc.com/templates-wordpress';
        }

        return self::signed_response( $data );
    }
}
