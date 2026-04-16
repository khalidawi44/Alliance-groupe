<?php
/**
 * Admin page for managing licences.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_post_ag_lm_generate', array( __CLASS__, 'handle_generate' ) );
        add_action( 'admin_post_ag_lm_revoke', array( __CLASS__, 'handle_revoke' ) );
        add_action( 'admin_post_ag_lm_save_versions', array( __CLASS__, 'handle_save_versions' ) );
    }

    public static function register_menu() {
        add_menu_page(
            'Licences AG',
            'Licences AG',
            'manage_options',
            'ag-licence-manager',
            array( __CLASS__, 'render_page' ),
            'dashicons-admin-network',
            58
        );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'licences';
        ?>
        <div class="wrap">
            <h1>Licences AG Starter</h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=ag-licence-manager&tab=licences" class="nav-tab <?php echo 'licences' === $tab ? 'nav-tab-active' : ''; ?>">Licences</a>
                <a href="?page=ag-licence-manager&tab=generate" class="nav-tab <?php echo 'generate' === $tab ? 'nav-tab-active' : ''; ?>">Générer</a>
                <a href="?page=ag-licence-manager&tab=versions" class="nav-tab <?php echo 'versions' === $tab ? 'nav-tab-active' : ''; ?>">Versions Pro</a>
                <a href="?page=ag-licence-manager&tab=stats" class="nav-tab <?php echo 'stats' === $tab ? 'nav-tab-active' : ''; ?>">Stats</a>
            </nav>

            <div style="margin-top:20px;">
                <?php
                switch ( $tab ) {
                    case 'generate': self::tab_generate(); break;
                    case 'versions': self::tab_versions(); break;
                    case 'stats':    self::tab_stats(); break;
                    default:         self::tab_licences(); break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    // ─── TAB: Liste des licences ──────────────────────────────

    private static function tab_licences() {
        $filter_status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
        $filter_email  = isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '';

        $licences = AG_Licence_DB::get_all( array(
            'status' => $filter_status,
            'email'  => $filter_email,
        ) );

        ?>
        <form method="get" style="margin-bottom:16px;">
            <input type="hidden" name="page" value="ag-licence-manager">
            <input type="hidden" name="tab" value="licences">
            <select name="status">
                <option value="">Tous les statuts</option>
                <?php foreach ( array( 'active', 'inactive', 'expired', 'revoked' ) as $s ) : ?>
                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $filter_status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="email" placeholder="Filtrer par email" value="<?php echo esc_attr( $filter_email ); ?>">
            <button type="submit" class="button">Filtrer</button>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Prefix</th>
                    <th>Tier</th>
                    <th>Email</th>
                    <th>Domaine</th>
                    <th>Statut</th>
                    <th>Créée</th>
                    <th>Activée</th>
                    <th>Expire</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $licences ) ) : ?>
                    <tr><td colspan="10" style="text-align:center;">Aucune licence trouvée.</td></tr>
                <?php else : ?>
                    <?php foreach ( $licences as $l ) : ?>
                    <tr>
                        <td><?php echo esc_html( $l->id ); ?></td>
                        <td><code><?php echo esc_html( $l->licence_prefix ); ?></code></td>
                        <td><strong><?php echo esc_html( ucfirst( $l->tier ) ); ?></strong></td>
                        <td><?php echo esc_html( $l->email ); ?></td>
                        <td><?php echo $l->domain ? esc_html( $l->domain ) : '<em>—</em>'; ?></td>
                        <td>
                            <?php
                            $colors = array( 'active' => '#28a745', 'inactive' => '#6c757d', 'expired' => '#dc3545', 'revoked' => '#dc3545' );
                            $color = isset( $colors[ $l->status ] ) ? $colors[ $l->status ] : '#6c757d';
                            ?>
                            <span style="color:<?php echo $color; ?>;font-weight:700;"><?php echo esc_html( ucfirst( $l->status ) ); ?></span>
                        </td>
                        <td><?php echo esc_html( $l->created_at ); ?></td>
                        <td><?php echo $l->activated_at ? esc_html( $l->activated_at ) : '—'; ?></td>
                        <td><?php echo $l->expires_at ? esc_html( $l->expires_at ) : 'Lifetime'; ?></td>
                        <td>
                            <?php if ( 'revoked' !== $l->status ) : ?>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
                                <?php wp_nonce_field( 'ag_lm_revoke_' . $l->id ); ?>
                                <input type="hidden" name="action" value="ag_lm_revoke">
                                <input type="hidden" name="licence_id" value="<?php echo esc_attr( $l->id ); ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('Révoquer cette licence ?');">Révoquer</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    // ─── TAB: Générer une licence ─────────────────────────────

    private static function tab_generate() {
        ?>
        <div style="max-width:600px;background:#fff;padding:24px;border:1px solid #ccd0d4;">
            <h2 style="margin-top:0;">Générer une licence manuellement</h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'ag_lm_generate' ); ?>
                <input type="hidden" name="action" value="ag_lm_generate">

                <table class="form-table">
                    <tr>
                        <th><label for="ag_email">Email client *</label></th>
                        <td><input type="email" name="email" id="ag_email" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="ag_tier">Pack</label></th>
                        <td>
                            <select name="tier" id="ag_tier">
                                <option value="pro">Pro (49 €)</option>
                                <option value="premium">Premium (99 €)</option>
                                <option value="business">Business (149 €)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ag_theme">Thème (optionnel)</label></th>
                        <td>
                            <select name="theme_slug" id="ag_theme">
                                <option value="">Tous les thèmes</option>
                                <option value="ag-starter-restaurant">Restaurant</option>
                                <option value="ag-starter-artisan">Artisan</option>
                                <option value="ag-starter-coach">Coach</option>
                                <option value="ag-starter-avocat">Avocat</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Envoi email</th>
                        <td><label><input type="checkbox" name="send_email" value="1" checked> Envoyer la clé par email au client</label></td>
                    </tr>
                </table>

                <p><button type="submit" class="button button-primary button-hero">Générer la licence</button></p>
            </form>
        </div>
        <?php
    }

    // ─── TAB: Versions Pro ────────────────────────────────────

    private static function tab_versions() {
        $versions = get_option( 'ag_lm_pro_versions', array() );
        $themes = array( 'ag-starter-restaurant', 'ag-starter-artisan', 'ag-starter-coach', 'ag-starter-avocat' );
        ?>
        <div style="max-width:700px;background:#fff;padding:24px;border:1px solid #ccd0d4;">
            <h2 style="margin-top:0;">Versions Pro disponibles</h2>
            <p>Configurez la version Pro actuelle de chaque thème. Le fichier ZIP doit être placé dans <code>wp-content/uploads/ag-pro-packages/</code>.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'ag_lm_save_versions' ); ?>
                <input type="hidden" name="action" value="ag_lm_save_versions">
                <table class="widefat">
                    <thead><tr><th>Thème</th><th>Version Pro</th><th>Nom du fichier ZIP</th></tr></thead>
                    <tbody>
                    <?php foreach ( $themes as $slug ) :
                        $v = isset( $versions[ $slug ] ) ? $versions[ $slug ] : array( 'version' => '', 'file' => '' );
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html( $slug ); ?></strong></td>
                            <td><input type="text" name="v[<?php echo esc_attr( $slug ); ?>][version]" value="<?php echo esc_attr( $v['version'] ); ?>" placeholder="2.0.0" style="width:100px;"></td>
                            <td><input type="text" name="v[<?php echo esc_attr( $slug ); ?>][file]" value="<?php echo esc_attr( $v['file'] ); ?>" placeholder="<?php echo esc_attr( $slug ); ?>-pro-2.0.0.zip" class="regular-text"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p><button type="submit" class="button button-primary">Enregistrer</button></p>
            </form>
        </div>
        <?php
    }

    // ─── TAB: Stats ───────────────────────────────────────────

    private static function tab_stats() {
        $counts = AG_Licence_DB::count_by_status();
        $total = 0;
        foreach ( $counts as $c ) $total += $c->cnt;
        ?>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;max-width:800px;">
            <?php
            $labels = array( 'active' => '🟢 Actives', 'inactive' => '⚪ Inactives', 'expired' => '🔴 Expirées', 'revoked' => '⛔ Révoquées' );
            foreach ( $labels as $status => $label ) :
                $cnt = isset( $counts[ $status ] ) ? $counts[ $status ]->cnt : 0;
            ?>
            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;text-align:center;">
                <div style="font-size:2rem;font-weight:700;"><?php echo esc_html( $cnt ); ?></div>
                <div style="color:#666;margin-top:4px;"><?php echo esc_html( $label ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:16px;color:#666;">Total : <strong><?php echo esc_html( $total ); ?></strong> licences générées.</p>
        <?php
    }

    // ─── HANDLERS ─────────────────────────────────────────────

    public static function handle_generate() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_lm_generate' );

        $email      = sanitize_email( $_POST['email'] );
        $tier       = sanitize_key( $_POST['tier'] );
        $theme_slug = sanitize_key( $_POST['theme_slug'] ?? '' );
        $send_email = ! empty( $_POST['send_email'] );

        if ( ! $email || ! in_array( $tier, array( 'pro', 'premium', 'business' ), true ) ) {
            wp_die( 'Paramètres invalides.' );
        }

        $clear_key = AG_Licence_DB::generate_key( $tier );
        $id = AG_Licence_DB::insert( $clear_key, $tier, $email, '', $theme_slug );

        if ( $id && $send_email ) {
            AG_Licence_Email::send_licence( $email, $clear_key, $tier );
        }

        // Show the key to the admin (one-time display)
        set_transient( 'ag_lm_generated_key', $clear_key, 60 );

        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=generate&generated=1' ) );
        exit;
    }

    public static function handle_revoke() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_revoke_' . $id );

        AG_Licence_DB::update( $id, array( 'status' => 'revoked', 'domain' => null ) );

        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences' ) );
        exit;
    }

    public static function handle_save_versions() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_lm_save_versions' );

        $input = isset( $_POST['v'] ) && is_array( $_POST['v'] ) ? $_POST['v'] : array();
        $versions = array();
        foreach ( $input as $slug => $data ) {
            $slug = sanitize_key( $slug );
            $version = sanitize_text_field( $data['version'] ?? '' );
            $file    = sanitize_file_name( $data['file'] ?? '' );
            if ( $version && $file ) {
                $versions[ $slug ] = array( 'version' => $version, 'file' => $file );
            }
        }
        update_option( 'ag_lm_pro_versions', $versions );

        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=versions&saved=1' ) );
        exit;
    }
}
