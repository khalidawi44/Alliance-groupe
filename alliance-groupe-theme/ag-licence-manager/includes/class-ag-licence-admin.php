<?php
/**
 * Admin page for managing licences — full management system.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_post_ag_lm_generate', array( __CLASS__, 'handle_generate' ) );
        add_action( 'admin_post_ag_lm_revoke', array( __CLASS__, 'handle_revoke' ) );
        add_action( 'admin_post_ag_lm_reactivate', array( __CLASS__, 'handle_reactivate' ) );
        add_action( 'admin_post_ag_lm_delete', array( __CLASS__, 'handle_delete' ) );
        add_action( 'admin_post_ag_lm_resend', array( __CLASS__, 'handle_resend' ) );
        add_action( 'admin_post_ag_lm_edit', array( __CLASS__, 'handle_edit' ) );
        add_action( 'admin_post_ag_lm_reset_domain', array( __CLASS__, 'handle_reset_domain' ) );
        add_action( 'admin_post_ag_lm_upgrade_tier', array( __CLASS__, 'handle_upgrade_tier' ) );
        add_action( 'admin_post_ag_lm_add_note', array( __CLASS__, 'handle_add_note' ) );
        add_action( 'admin_post_ag_lm_save_versions', array( __CLASS__, 'handle_save_versions' ) );
    }

    public static function register_menu() {
        add_menu_page( 'Licences AG', 'Licences AG', 'manage_options', 'ag-licence-manager', array( __CLASS__, 'render_page' ), 'dashicons-admin-network', 58 );
    }

    private static function msg() {
        $msg = isset( $_GET['msg'] ) ? sanitize_text_field( $_GET['msg'] ) : '';
        if ( $msg ) {
            $type = strpos( $msg, 'Erreur' ) !== false ? 'error' : 'success';
            echo '<div class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
        }
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'licences';
        ?>
        <div class="wrap">
            <h1>Licences AG Starter</h1>
            <?php self::msg(); ?>
            <nav class="nav-tab-wrapper">
                <a href="?page=ag-licence-manager&tab=licences" class="nav-tab <?php echo 'licences' === $tab ? 'nav-tab-active' : ''; ?>">Licences</a>
                <a href="?page=ag-licence-manager&tab=generate" class="nav-tab <?php echo 'generate' === $tab ? 'nav-tab-active' : ''; ?>">Générer</a>
                <a href="?page=ag-licence-manager&tab=edit" class="nav-tab <?php echo 'edit' === $tab ? 'nav-tab-active' : ''; ?>">Modifier</a>
                <a href="?page=ag-licence-manager&tab=versions" class="nav-tab <?php echo 'versions' === $tab ? 'nav-tab-active' : ''; ?>">Versions Pro</a>
                <a href="?page=ag-licence-manager&tab=stats" class="nav-tab <?php echo 'stats' === $tab ? 'nav-tab-active' : ''; ?>">Stats</a>
            </nav>
            <div style="margin-top:20px;">
            <?php
            switch ( $tab ) {
                case 'generate': self::tab_generate(); break;
                case 'edit':     self::tab_edit(); break;
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
        $licences = AG_Licence_DB::get_all( array( 'status' => $filter_status, 'email' => $filter_email ) );
        ?>
        <form method="get" style="margin-bottom:16px;">
            <input type="hidden" name="page" value="ag-licence-manager">
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
            <thead><tr>
                <th>ID</th><th>Clé</th><th>Tier</th><th>Email</th><th>Domaine</th><th>Statut</th><th>Créée</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( empty( $licences ) ) : ?>
                <tr><td colspan="8" style="text-align:center;">Aucune licence.</td></tr>
            <?php else : foreach ( $licences as $l ) :
                $ck = ! empty( $l->licence_key_enc ) ? AG_Licence_DB::decrypt_key( $l->licence_key_enc ) : '';
                $colors = array( 'active' => '#28a745', 'inactive' => '#6c757d', 'expired' => '#dc3545', 'revoked' => '#dc3545' );
                $color = $colors[ $l->status ] ?? '#6c757d';
            ?>
                <tr>
                    <td><?php echo esc_html( $l->id ); ?></td>
                    <td>
                        <?php if ( $ck ) : ?>
                            <code id="k<?php echo $l->id; ?>" style="font-size:.78rem;display:none;word-break:break-all;"><?php echo esc_html( $ck ); ?></code>
                            <button type="button" class="button button-small" onclick="var e=document.getElementById('k<?php echo $l->id; ?>');e.style.display=e.style.display==='none'?'inline':'none';">👁</button>
                            <button type="button" class="button button-small" onclick="var e=document.getElementById('k<?php echo $l->id; ?>');e.style.display='inline';navigator.clipboard.writeText(e.textContent);this.textContent='✅';setTimeout(function(){this.textContent='📋'}.bind(this),1500);">📋</button>
                        <?php else : ?>
                            <em style="color:#999;font-size:.82rem;">Ancienne clé</em>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo esc_html( ucfirst( $l->tier ) ); ?></strong></td>
                    <td><?php echo esc_html( $l->email ); ?></td>
                    <td><?php echo $l->domain ? esc_html( $l->domain ) : '—'; ?></td>
                    <td><span style="color:<?php echo $color; ?>;font-weight:700;"><?php echo esc_html( ucfirst( $l->status ) ); ?></span></td>
                    <td><?php echo esc_html( substr( $l->created_at, 0, 10 ) ); ?></td>
                    <td style="white-space:nowrap;">
                        <a href="?page=ag-licence-manager&tab=edit&id=<?php echo $l->id; ?>" class="button button-small" title="Modifier">✏️</a>
                        <?php if ( $ck ) : ?>
                        <?php echo self::action_btn( 'resend', $l->id, '📧', 'Renvoyer email' ); ?>
                        <?php endif; ?>
                        <?php if ( 'active' === $l->status || 'inactive' === $l->status ) : ?>
                        <?php echo self::action_btn( 'revoke', $l->id, '⛔', 'Révoquer', 'Révoquer ?' ); ?>
                        <?php endif; ?>
                        <?php if ( 'revoked' === $l->status ) : ?>
                        <?php echo self::action_btn( 'reactivate', $l->id, '♻️', 'Réactiver' ); ?>
                        <?php endif; ?>
                        <?php if ( $l->domain ) : ?>
                        <?php echo self::action_btn( 'reset_domain', $l->id, '🔄', 'Libérer domaine', 'Libérer le domaine ?' ); ?>
                        <?php endif; ?>
                        <?php echo self::action_btn( 'delete', $l->id, '🗑', 'Supprimer', 'Supprimer définitivement ?' ); ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        <?php
    }

    private static function action_btn( $action, $id, $icon, $title, $confirm = '' ) {
        ob_start();
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
            <?php wp_nonce_field( 'ag_lm_' . $action . '_' . $id ); ?>
            <input type="hidden" name="action" value="ag_lm_<?php echo esc_attr( $action ); ?>">
            <input type="hidden" name="licence_id" value="<?php echo esc_attr( $id ); ?>">
            <button type="submit" class="button button-small" title="<?php echo esc_attr( $title ); ?>"<?php echo $confirm ? ' onclick="return confirm(\'' . esc_js( $confirm ) . '\');"' : ''; ?>><?php echo $icon; ?></button>
        </form>
        <?php
        return ob_get_clean();
    }

    // ─── TAB: Générer ─────────────────────────────────────────

    private static function tab_generate() {
        $generated_key = get_transient( 'ag_lm_generated_key' );
        if ( $generated_key ) {
            delete_transient( 'ag_lm_generated_key' );
            echo '<div class="notice notice-success"><p>Licence générée : <code style="font-size:1.1rem;padding:4px 10px;background:#f0f0f0;">' . esc_html( $generated_key ) . '</code> <button type="button" class="button button-small" onclick="navigator.clipboard.writeText(\'' . esc_js( $generated_key ) . '\');this.textContent=\'✅ Copié\';">📋 Copier</button></p></div>';
        }
        ?>
        <div style="max-width:600px;background:#fff;padding:24px;border:1px solid #ccd0d4;">
            <h2 style="margin-top:0;">Générer une licence</h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'ag_lm_generate' ); ?>
                <input type="hidden" name="action" value="ag_lm_generate">
                <table class="form-table">
                    <tr><th><label>Email *</label></th><td><input type="email" name="email" class="regular-text" required></td></tr>
                    <tr><th><label>Pack</label></th><td>
                        <select name="tier">
                            <option value="pro">Pro (49€)</option>
                            <option value="premium">Premium (99€)</option>
                            <option value="business">Business (149€)</option>
                        </select></td></tr>
                    <tr><th><label>Thème</label></th><td>
                        <select name="theme_slug">
                            <option value="">Tous</option>
                            <option value="ag-starter-restaurant">Restaurant</option>
                            <option value="ag-starter-artisan">Artisan</option>
                            <option value="ag-starter-coach">Coach</option>
                            <option value="ag-starter-avocat">Avocat</option>
                        </select></td></tr>
                    <tr><th>Email</th><td><label><input type="checkbox" name="send_email" value="1" checked> Envoyer la clé par email</label></td></tr>
                </table>
                <p><button type="submit" class="button button-primary button-hero">Générer la licence</button></p>
            </form>
        </div>
        <?php
    }

    // ─── TAB: Modifier une licence ────────────────────────────

    private static function tab_edit() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        if ( ! $id ) {
            echo '<p>Sélectionnez une licence à modifier depuis l\'onglet Licences (bouton ✏️).</p>';
            return;
        }
        $l = AG_Licence_DB::find_by_id( $id );
        if ( ! $l ) {
            echo '<div class="notice notice-error"><p>Licence #' . $id . ' introuvable.</p></div>';
            return;
        }
        $ck = ! empty( $l->licence_key_enc ) ? AG_Licence_DB::decrypt_key( $l->licence_key_enc ) : '';
        $notes = get_option( 'ag_lm_notes_' . $id, array() );
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:1100px;">

            <!-- Infos + modifier -->
            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;">
                <h2 style="margin-top:0;">Licence #<?php echo $id; ?></h2>

                <?php if ( $ck ) : ?>
                <div style="background:#f8f5ec;border:1px solid #D4B45C;border-radius:6px;padding:14px;margin-bottom:20px;">
                    <strong>Clé :</strong><br>
                    <code style="font-size:1rem;word-break:break-all;"><?php echo esc_html( $ck ); ?></code>
                    <button type="button" class="button button-small" style="margin-left:8px;" onclick="navigator.clipboard.writeText('<?php echo esc_js( $ck ); ?>');this.textContent='✅ Copié';">📋 Copier</button>
                </div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'ag_lm_edit_' . $id ); ?>
                    <input type="hidden" name="action" value="ag_lm_edit">
                    <input type="hidden" name="licence_id" value="<?php echo $id; ?>">
                    <table class="form-table">
                        <tr>
                            <th><label>Email client</label></th>
                            <td><input type="email" name="email" class="regular-text" value="<?php echo esc_attr( $l->email ); ?>" required>
                            <p class="description">Modifiable si le client s'est trompé d'email.</p></td>
                        </tr>
                        <tr>
                            <th><label>Pack / Tier</label></th>
                            <td><select name="tier">
                                <?php foreach ( array( 'pro' => 'Pro', 'business' => 'Business' ) as $k => $v ) : ?>
                                    <option value="<?php echo $k; ?>" <?php selected( $l->tier, $k ); ?>><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Upgrade/downgrade le tier sans changer la clé.</p></td>
                        </tr>
                        <tr>
                            <th><label>Statut</label></th>
                            <td><select name="status">
                                <?php foreach ( array( 'active', 'inactive', 'expired', 'revoked' ) as $s ) : ?>
                                    <option value="<?php echo $s; ?>" <?php selected( $l->status, $s ); ?>><?php echo ucfirst( $s ); ?></option>
                                <?php endforeach; ?>
                            </select></td>
                        </tr>
                        <tr>
                            <th><label>Domaine</label></th>
                            <td><input type="text" name="domain" class="regular-text" value="<?php echo esc_attr( $l->domain ); ?>" placeholder="example.com">
                            <p class="description">Vider le champ pour libérer le domaine (le client pourra réactiver ailleurs).</p></td>
                        </tr>
                        <tr>
                            <th><label>Expiration</label></th>
                            <td><input type="date" name="expires_at" value="<?php echo $l->expires_at ? esc_attr( substr( $l->expires_at, 0, 10 ) ) : ''; ?>">
                            <p class="description">Laisser vide = licence à vie (pas d'expiration).</p></td>
                        </tr>
                    </table>
                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button type="submit" class="button button-primary">Enregistrer les modifications</button>
                        <button type="submit" name="resend_after_edit" value="1" class="button">Enregistrer + renvoyer email au client</button>
                    </div>
                </form>
            </div>

            <!-- Actions rapides + Notes -->
            <div>
                <!-- Actions rapides -->
                <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;margin-bottom:20px;">
                    <h3 style="margin-top:0;">Actions rapides</h3>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <?php if ( $ck ) : ?>
                        <?php echo self::action_btn( 'resend', $id, '📧', '' ); ?>
                        <span style="font-size:.85rem;color:#666;">Renvoyer la clé par email à <?php echo esc_html( $l->email ); ?></span>
                        <?php endif; ?>

                        <?php echo self::action_btn( 'reset_domain', $id, '🔄', '' ); ?>
                        <span style="font-size:.85rem;color:#666;">Libérer le domaine (le client peut réactiver sur un autre site)</span>

                        <?php echo self::action_btn( 'revoke', $id, '⛔', '', 'Révoquer cette licence ?' ); ?>
                        <span style="font-size:.85rem;color:#666;">Révoquer (désactiver définitivement)</span>

                        <?php echo self::action_btn( 'delete', $id, '🗑', '', 'Supprimer DÉFINITIVEMENT ?' ); ?>
                        <span style="font-size:.85rem;color:#666;margin-bottom:8px;">Supprimer de la base de données</span>
                    </div>
                </div>

                <!-- Historique / Notes -->
                <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;">
                    <h3 style="margin-top:0;">Notes internes</h3>
                    <p class="description">Historique des échanges avec ce client (visible uniquement par l'admin).</p>

                    <?php if ( ! empty( $notes ) ) : ?>
                    <div style="max-height:300px;overflow-y:auto;margin:12px 0;">
                        <?php foreach ( array_reverse( $notes ) as $n ) : ?>
                        <div style="background:#f9f9f9;border-left:3px solid #D4B45C;padding:10px 14px;margin-bottom:8px;font-size:.88rem;">
                            <strong><?php echo esc_html( $n['date'] ); ?></strong><br>
                            <?php echo esc_html( $n['text'] ); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else : ?>
                    <p style="color:#999;font-size:.88rem;">Aucune note pour cette licence.</p>
                    <?php endif; ?>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;">
                        <?php wp_nonce_field( 'ag_lm_add_note_' . $id ); ?>
                        <input type="hidden" name="action" value="ag_lm_add_note">
                        <input type="hidden" name="licence_id" value="<?php echo $id; ?>">
                        <textarea name="note" rows="3" style="width:100%;" placeholder="Ex: Client a demandé changement d'email le 22/04..."></textarea>
                        <button type="submit" class="button" style="margin-top:6px;">Ajouter une note</button>
                    </form>
                </div>

                <!-- Infos techniques -->
                <div style="background:#f9f9f9;padding:16px;border:1px solid #e0e0e0;margin-top:20px;font-size:.85rem;color:#666;">
                    <strong>Infos techniques</strong><br>
                    Hash : <code style="font-size:.75rem;"><?php echo esc_html( substr( $l->licence_key_hash, 0, 16 ) ); ?>...</code><br>
                    Prefix : <?php echo esc_html( $l->licence_prefix ); ?><br>
                    Stripe session : <?php echo $l->stripe_session ? esc_html( $l->stripe_session ) : '—'; ?><br>
                    Activée le : <?php echo $l->activated_at ? esc_html( $l->activated_at ) : 'Jamais'; ?><br>
                    Dernier check : <?php echo $l->last_check ? esc_html( $l->last_check ) : 'Jamais'; ?>
                </div>
            </div>
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
            <p>Le fichier ZIP doit être dans <code>wp-content/uploads/ag-pro-packages/</code>.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'ag_lm_save_versions' ); ?>
                <input type="hidden" name="action" value="ag_lm_save_versions">
                <table class="widefat">
                    <thead><tr><th>Thème</th><th>Version Pro</th><th>Fichier ZIP</th></tr></thead>
                    <tbody>
                    <?php foreach ( $themes as $slug ) :
                        $v = $versions[ $slug ] ?? array( 'version' => '', 'file' => '' );
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
            <?php foreach ( array( 'active' => '🟢 Actives', 'inactive' => '⚪ Inactives', 'expired' => '🔴 Expirées', 'revoked' => '⛔ Révoquées' ) as $status => $label ) :
                $cnt = isset( $counts[ $status ] ) ? $counts[ $status ]->cnt : 0;
            ?>
            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;text-align:center;">
                <div style="font-size:2rem;font-weight:700;"><?php echo $cnt; ?></div>
                <div style="color:#666;margin-top:4px;"><?php echo esc_html( $label ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:16px;color:#666;">Total : <strong><?php echo $total; ?></strong> licences.</p>
        <?php
    }

    // ═══════════════════════════════════════════════════════════
    // HANDLERS
    // ═══════════════════════════════════════════════════════════

    public static function handle_generate() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_lm_generate' );
        $email = sanitize_email( $_POST['email'] );
        $tier  = sanitize_key( $_POST['tier'] );
        $theme = sanitize_key( $_POST['theme_slug'] ?? '' );
        $send  = ! empty( $_POST['send_email'] );
        if ( ! $email || ! in_array( $tier, array( 'pro', 'premium', 'business' ), true ) ) wp_die( 'Paramètres invalides.' );
        $key = AG_Licence_DB::generate_key( $tier );
        $id  = AG_Licence_DB::insert( $key, $tier, $email, '', $theme );
        if ( $id && $send ) AG_Licence_Email::send_licence( $email, $key, $tier );
        set_transient( 'ag_lm_generated_key', $key, 120 );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=generate&generated=1' ) );
        exit;
    }

    public static function handle_edit() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_edit_' . $id );
        $data = array(
            'email'  => sanitize_email( $_POST['email'] ),
            'tier'   => sanitize_key( $_POST['tier'] ),
            'status' => sanitize_key( $_POST['status'] ),
            'domain' => sanitize_text_field( $_POST['domain'] ) ?: null,
        );
        $exp = sanitize_text_field( $_POST['expires_at'] ?? '' );
        $data['expires_at'] = $exp ? $exp . ' 23:59:59' : null;

        // Update prefix to match tier
        $prefixes = array( 'pro' => 'AGPRO', 'premium' => 'AGPRM', 'business' => 'AGBUS' );
        if ( isset( $prefixes[ $data['tier'] ] ) ) $data['licence_prefix'] = $prefixes[ $data['tier'] ];

        AG_Licence_DB::update( $id, $data );

        // Auto-add note
        $notes = get_option( 'ag_lm_notes_' . $id, array() );
        $notes[] = array( 'date' => current_time( 'd/m/Y H:i' ), 'text' => 'Licence modifiée par l\'admin.' );
        update_option( 'ag_lm_notes_' . $id, $notes );

        if ( ! empty( $_POST['resend_after_edit'] ) ) {
            $l = AG_Licence_DB::find_by_id( $id );
            if ( $l && ! empty( $l->licence_key_enc ) ) {
                $ck = AG_Licence_DB::decrypt_key( $l->licence_key_enc );
                AG_Licence_Email::send_licence( $data['email'], $ck, $data['tier'] );
            }
        }

        $msg = 'Licence #' . $id . ' modifiée.';
        if ( ! empty( $_POST['resend_after_edit'] ) ) $msg .= ' Email renvoyé.';
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=edit&id=' . $id . '&msg=' . urlencode( $msg ) ) );
        exit;
    }

    public static function handle_revoke() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_revoke_' . $id );
        AG_Licence_DB::update( $id, array( 'status' => 'revoked', 'domain' => null ) );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences&msg=' . urlencode( 'Licence #' . $id . ' révoquée.' ) ) );
        exit;
    }

    public static function handle_reactivate() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_reactivate_' . $id );
        AG_Licence_DB::update( $id, array( 'status' => 'inactive', 'domain' => null ) );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences&msg=' . urlencode( 'Licence #' . $id . ' réactivée (inactive, prête à être activée).' ) ) );
        exit;
    }

    public static function handle_reset_domain() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_reset_domain_' . $id );
        AG_Licence_DB::update( $id, array( 'domain' => null, 'status' => 'inactive' ) );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences&msg=' . urlencode( 'Domaine libéré pour licence #' . $id . '.' ) ) );
        exit;
    }

    public static function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_delete_' . $id );
        AG_Licence_DB::delete( $id );
        delete_option( 'ag_lm_notes_' . $id );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences&msg=' . urlencode( 'Licence #' . $id . ' supprimée.' ) ) );
        exit;
    }

    public static function handle_resend() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_resend_' . $id );
        $l = AG_Licence_DB::find_by_id( $id );
        if ( ! $l || empty( $l->licence_key_enc ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences&msg=' . urlencode( 'Erreur : clé introuvable.' ) ) );
            exit;
        }
        $ck = AG_Licence_DB::decrypt_key( $l->licence_key_enc );
        AG_Licence_Email::send_licence( $l->email, $ck, $l->tier );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=licences&msg=' . urlencode( 'Email renvoyé à ' . $l->email ) ) );
        exit;
    }

    public static function handle_add_note() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        $id = absint( $_POST['licence_id'] );
        check_admin_referer( 'ag_lm_add_note_' . $id );
        $note = sanitize_textarea_field( $_POST['note'] ?? '' );
        if ( $note ) {
            $notes = get_option( 'ag_lm_notes_' . $id, array() );
            $notes[] = array( 'date' => current_time( 'd/m/Y H:i' ), 'text' => $note );
            update_option( 'ag_lm_notes_' . $id, $notes );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=edit&id=' . $id . '&msg=' . urlencode( 'Note ajoutée.' ) ) );
        exit;
    }

    public static function handle_save_versions() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_lm_save_versions' );
        $input = isset( $_POST['v'] ) && is_array( $_POST['v'] ) ? $_POST['v'] : array();
        $versions = array();
        foreach ( $input as $slug => $data ) {
            $slug    = sanitize_key( $slug );
            $version = sanitize_text_field( $data['version'] ?? '' );
            $file    = sanitize_file_name( $data['file'] ?? '' );
            if ( $version && $file ) $versions[ $slug ] = array( 'version' => $version, 'file' => $file );
        }
        update_option( 'ag_lm_pro_versions', $versions );
        wp_safe_redirect( admin_url( 'admin.php?page=ag-licence-manager&tab=versions&msg=' . urlencode( 'Versions enregistrées.' ) ) );
        exit;
    }
}
