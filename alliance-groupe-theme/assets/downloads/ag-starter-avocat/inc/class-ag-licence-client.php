<?php
/**
 * Licence client: stores key, verifies with API, caches result.
 * Included in each AG Starter free theme.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_Client {

    /** API base on Alliance Groupe server. */
    const API_URL = 'https://alliancegroupe-inc.com/wp-json/ag/v1';

    /** Cache duration (4 hours — allows faster revocation). */
    const CACHE_TTL = 14400;

    /** Grace period if API unreachable (24 hours). */
    const GRACE_TTL = 86400;

    /** Option keys. */
    const OPT_KEY    = 'ag_licence_key';
    const OPT_CACHE  = 'ag_licence_cache';

    /**
     * Get the stored licence key.
     */
    public static function get_key() {
        return get_option( self::OPT_KEY, '' );
    }

    /**
     * Get the current domain (without www).
     */
    public static function get_domain() {
        $url = home_url();
        $host = wp_parse_url( $url, PHP_URL_HOST );
        return preg_replace( '/^www\./', '', $host );
    }

    /**
     * Is the current installation running a valid Pro+ licence?
     */
    public static function is_pro() {
        // On admin pages: always verify live (allows instant revocation)
        if ( is_admin() ) {
            $key = self::get_key();
            if ( empty( $key ) ) return false;
            $result = self::remote_verify( $key );
            if ( null !== $result ) {
                set_transient( self::OPT_CACHE, $result, self::CACHE_TTL );
                update_option( 'ag_licence_grace', array_merge( $result, array( 'ts' => time() ) ) );
                return ! empty( $result['valid'] );
            }
            // API unreachable in admin — fall through to cache
        }

        $cache = get_transient( self::OPT_CACHE );
        if ( false !== $cache ) {
            return ! empty( $cache['valid'] );
        }

        // No cache — try to verify
        $key = self::get_key();
        if ( empty( $key ) ) {
            return false;
        }

        $result = self::remote_verify( $key );
        if ( null === $result ) {
            // API unreachable — check grace period
            $grace = get_option( 'ag_licence_grace', array() );
            if ( ! empty( $grace['valid'] ) && ! empty( $grace['ts'] ) ) {
                if ( ( time() - $grace['ts'] ) < self::GRACE_TTL ) {
                    return true;
                }
            }
            return false;
        }

        // Cache the result
        set_transient( self::OPT_CACHE, $result, self::CACHE_TTL );
        update_option( 'ag_licence_grace', array_merge( $result, array( 'ts' => time() ) ) );

        return ! empty( $result['valid'] );
    }

    /**
     * Get the active tier (free, pro, premium, business).
     */
    public static function get_tier() {
        if ( ! self::is_pro() ) return 'free';
        $cache = get_transient( self::OPT_CACHE );
        return isset( $cache['tier'] ) ? $cache['tier'] : 'premium';
    }

    /**
     * Activate a licence key for this domain.
     *
     * @return array { success: bool, message: string, tier?: string }
     */
    public static function activate( $key ) {
        $key = strtoupper( trim( $key ) );
        $domain = self::get_domain();

        $resp = wp_remote_post( self::API_URL . '/licence/activate', array(
            'timeout' => 15,
            'body'    => array(
                'licence_key' => $key,
                'domain'      => $domain,
            ),
        ) );

        if ( is_wp_error( $resp ) ) {
            return array( 'success' => false, 'message' => 'Impossible de contacter le serveur de licence.' );
        }

        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( empty( $body ) ) {
            return array( 'success' => false, 'message' => 'Réponse invalide du serveur.' );
        }

        if ( ! empty( $body['success'] ) ) {
            update_option( self::OPT_KEY, $key );
            delete_transient( self::OPT_CACHE );
            // Cache immediately
            set_transient( self::OPT_CACHE, array( 'valid' => true, 'tier' => $body['tier'] ?? 'pro' ), self::CACHE_TTL );
            update_option( 'ag_licence_grace', array( 'valid' => true, 'tier' => $body['tier'] ?? 'pro', 'ts' => time() ) );
            return array( 'success' => true, 'message' => 'Licence activée !', 'tier' => $body['tier'] ?? 'pro' );
        }

        return array( 'success' => false, 'message' => $body['message'] ?? 'Activation échouée.' );
    }

    /**
     * Deactivate the current licence.
     */
    public static function deactivate() {
        $key    = self::get_key();
        $domain = self::get_domain();

        if ( $key ) {
            wp_remote_post( self::API_URL . '/licence/deactivate', array(
                'timeout' => 10,
                'body'    => array(
                    'licence_key' => $key,
                    'domain'      => $domain,
                ),
            ) );
        }

        delete_option( self::OPT_KEY );
        delete_transient( self::OPT_CACHE );
        delete_option( 'ag_licence_grace' );
    }

    /**
     * Call the verify endpoint.
     *
     * @return array|null  null if unreachable.
     */
    private static function remote_verify( $key ) {
        $resp = wp_remote_post( self::API_URL . '/licence/verify', array(
            'timeout' => 10,
            'body'    => array(
                'licence_key' => $key,
                'domain'      => self::get_domain(),
            ),
        ) );

        if ( is_wp_error( $resp ) ) return null;
        if ( 200 !== wp_remote_retrieve_response_code( $resp ) && 404 !== wp_remote_retrieve_response_code( $resp ) ) return null;

        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        return is_array( $body ) ? $body : null;
    }

    // ─── Admin page ───────────────────────────────────────────

    /**
     * Register the licence admin page under Appearance.
     */
    public static function register_admin() {
        add_action( 'admin_menu', function () {
            add_theme_page(
                esc_html__( 'Licence AG Starter', 'ag-starter-avocat' ),
                esc_html__( 'Licence AG', 'ag-starter-avocat' ),
                'manage_options',
                'ag-licence',
                array( 'AG_Licence_Client', 'render_admin_page' )
            );
        } );

        add_action( 'admin_post_ag_licence_activate', array( __CLASS__, 'handle_activate' ) );
        add_action( 'admin_post_ag_licence_deactivate', array( __CLASS__, 'handle_deactivate' ) );
    }

    public static function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $key  = self::get_key();
        $is_pro = self::is_pro();
        $tier = self::get_tier();
        $msg  = isset( $_GET['ag_msg'] ) ? sanitize_text_field( $_GET['ag_msg'] ) : '';
        ?>
        <div class="wrap">
            <h1>Licence AG Starter</h1>

            <?php if ( $msg ) : ?>
                <div class="notice notice-<?php echo strpos( $msg, 'Erreur' ) !== false ? 'error' : 'success'; ?> is-dismissible">
                    <p><?php echo esc_html( $msg ); ?></p>
                </div>
            <?php endif; ?>

            <div style="max-width:600px;margin-top:20px;padding:24px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid <?php echo $is_pro ? '#28a745' : '#D4B45C'; ?>;">

                <?php if ( $is_pro ) : ?>
                    <h2 style="margin-top:0;color:#28a745;">✅ Licence active — <?php echo esc_html( ucfirst( $tier ) ); ?></h2>
                    <p>Votre licence est activée pour <strong><?php echo esc_html( self::get_domain() ); ?></strong>.</p>
                    <p>WordPress vous proposera automatiquement les mises a jour Premium. Allez dans <strong>Apparence → Thèmes</strong> pour vérifier.</p>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'ag_licence_deactivate' ); ?>
                        <input type="hidden" name="action" value="ag_licence_deactivate">
                        <button type="submit" class="button" onclick="return confirm('Désactiver la licence ? Vous pourrez la réactiver sur un autre domaine.');">Désactiver la licence</button>
                    </form>

                <?php else : ?>
                    <h2 style="margin-top:0;">Activez votre licence Premium</h2>
                    <p>Collez votre clé de licence reçue par email après achat. Elle ressemble à : <code>AGPRO-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code></p>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'ag_licence_activate' ); ?>
                        <input type="hidden" name="action" value="ag_licence_activate">
                        <p>
                            <input type="text" name="licence_key" class="regular-text" placeholder="AGPRO-xxxxxxxx-xxxx-..." style="font-family:monospace;" required>
                        </p>
                        <p><button type="submit" class="button button-primary">Activer la licence</button></p>
                    </form>

                    <hr>
                    <p>Vous n'avez pas de licence ?
                        <a href="https://alliancegroupe-inc.com/templates-wordpress" target="_blank" rel="noopener" style="color:#D4B45C;font-weight:700;">
                            Voir les packs Premium →
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public static function handle_activate() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_licence_activate' );
        $key = sanitize_text_field( $_POST['licence_key'] ?? '' );
        $result = self::activate( $key );
        $msg = $result['message'];
        wp_safe_redirect( admin_url( 'themes.php?page=ag-licence&ag_msg=' . urlencode( $msg ) ) );
        exit;
    }

    public static function handle_deactivate() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_licence_deactivate' );
        self::deactivate();
        wp_safe_redirect( admin_url( 'themes.php?page=ag-licence&ag_msg=' . urlencode( 'Licence désactivée.' ) ) );
        exit;
    }
}
