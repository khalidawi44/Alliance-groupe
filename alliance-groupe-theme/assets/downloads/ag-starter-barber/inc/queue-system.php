<?php
/**
 * AG Barber Queue System — QR code based walk-in queue management.
 *
 * Stores queue in wp_options as a lightweight JSON array.
 * No external dependencies, no database table needed.
 *
 * @package AG_Starter_Barber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Barber_Queue {

    const OPTION_KEY   = 'ag_barber_queue';
    const SETTINGS_KEY = 'ag_barber_settings';

    /**
     * Get queue settings.
     */
    public static function get_settings() {
        return wp_parse_args( get_option( self::SETTINGS_KEY, array() ), array(
            'shop_name'     => get_bloginfo( 'name' ),
            'avg_cut_time'  => 15,
            'num_barbers'   => 2,
            'opening_hour'  => 9,
            'closing_hour'  => 20,
            'services'      => array(
                array( 'name' => 'Coupe homme',       'price' => 10, 'time' => 15 ),
                array( 'name' => 'Coupe + barbe',     'price' => 15, 'time' => 25 ),
                array( 'name' => 'Barbe seule',       'price' => 5,  'time' => 10 ),
                array( 'name' => 'Coupe enfant (-12)', 'price' => 8, 'time' => 10 ),
                array( 'name' => 'Dégradé premium',   'price' => 15, 'time' => 20 ),
            ),
        ) );
    }

    /**
     * Get the current queue (array of tickets).
     */
    public static function get_queue() {
        $queue = get_option( self::OPTION_KEY, array() );
        if ( ! is_array( $queue ) ) return array();
        // Auto-purge tickets older than 4 hours
        $cutoff = time() - ( 4 * 3600 );
        $queue  = array_filter( $queue, function ( $t ) use ( $cutoff ) {
            return ( $t['timestamp'] ?? 0 ) > $cutoff && ( $t['status'] ?? '' ) !== 'done';
        } );
        return array_values( $queue );
    }

    /**
     * Count people waiting (status = waiting).
     */
    public static function count_waiting() {
        $queue = self::get_queue();
        return count( array_filter( $queue, function ( $t ) {
            return ( $t['status'] ?? '' ) === 'waiting';
        } ) );
    }

    /**
     * Estimate wait time in minutes for the next person joining.
     */
    public static function estimate_wait() {
        $settings = self::get_settings();
        $waiting  = self::count_waiting();
        $barbers  = max( 1, intval( $settings['num_barbers'] ) );
        $avg_time = max( 5, intval( $settings['avg_cut_time'] ) );
        return max( 0, ceil( ( $waiting * $avg_time ) / $barbers ) );
    }

    /**
     * Estimate the appointment time (H:i) for a new joiner.
     */
    public static function estimate_time() {
        $wait = self::estimate_wait();
        $estimated = time() + ( $wait * 60 );
        return date_i18n( 'H:i', $estimated );
    }

    /**
     * Add a customer to the queue.
     *
     * @param string $name    Customer first name.
     * @param string $phone   Phone number (optional).
     * @param string $service Service name chosen.
     * @return array  The ticket.
     */
    public static function join( $name, $phone, $service ) {
        $queue    = self::get_queue();
        $settings = self::get_settings();
        $wait     = self::estimate_wait();

        // Find the service time
        $svc_time = intval( $settings['avg_cut_time'] );
        foreach ( $settings['services'] as $s ) {
            if ( $s['name'] === $service ) {
                $svc_time = intval( $s['time'] );
                break;
            }
        }

        $ticket = array(
            'id'             => 'T' . strtoupper( substr( md5( uniqid( '', true ) ), 0, 6 ) ),
            'number'         => count( $queue ) + 1,
            'name'           => sanitize_text_field( $name ),
            'phone'          => sanitize_text_field( $phone ),
            'service'        => sanitize_text_field( $service ),
            'service_time'   => $svc_time,
            'status'         => 'waiting',
            'estimated_wait' => $wait,
            'estimated_time' => date_i18n( 'H:i', time() + ( $wait * 60 ) ),
            'timestamp'      => time(),
            'joined_at'      => current_time( 'H:i' ),
        );

        $queue[] = $ticket;
        update_option( self::OPTION_KEY, $queue );

        return $ticket;
    }

    /**
     * Mark a ticket as "in progress" or "done".
     */
    public static function update_status( $ticket_id, $new_status ) {
        $queue = self::get_queue();
        foreach ( $queue as &$t ) {
            if ( $t['id'] === $ticket_id ) {
                $t['status'] = $new_status;
                if ( 'in_progress' === $new_status ) {
                    $t['started_at'] = current_time( 'H:i' );
                }
                if ( 'done' === $new_status ) {
                    $t['finished_at'] = current_time( 'H:i' );
                }
                break;
            }
        }
        update_option( self::OPTION_KEY, $queue );
    }

    /**
     * Remove a ticket.
     */
    public static function remove( $ticket_id ) {
        $queue = self::get_queue();
        $queue = array_filter( $queue, function ( $t ) use ( $ticket_id ) {
            return $t['id'] !== $ticket_id;
        } );
        update_option( self::OPTION_KEY, array_values( $queue ) );
    }

    /**
     * Clear the entire queue (daily reset).
     */
    public static function clear_all() {
        update_option( self::OPTION_KEY, array() );
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN PAGE (Barber dashboard)
    // ═══════════════════════════════════════════════════════════

    public static function register_admin() {
        add_action( 'admin_menu', function () {
            add_menu_page(
                esc_html__( 'File d\'attente', 'ag-starter-barber' ),
                esc_html__( 'File d\'attente', 'ag-starter-barber' ),
                'edit_posts',
                'ag-barber-queue',
                array( 'AG_Barber_Queue', 'render_admin' ),
                'dashicons-groups',
                3
            );
            add_submenu_page(
                'ag-barber-queue',
                esc_html__( 'Réglages Barber', 'ag-starter-barber' ),
                esc_html__( 'Réglages', 'ag-starter-barber' ),
                'manage_options',
                'ag-barber-settings',
                array( 'AG_Barber_Queue', 'render_settings' )
            );
        } );

        // AJAX handlers
        add_action( 'wp_ajax_ag_queue_action', array( __CLASS__, 'ajax_action' ) );
        add_action( 'wp_ajax_ag_queue_refresh', array( __CLASS__, 'ajax_refresh' ) );

        // Public AJAX (for customers checking their ticket)
        add_action( 'wp_ajax_nopriv_ag_queue_join', array( __CLASS__, 'ajax_join' ) );
        add_action( 'wp_ajax_ag_queue_join', array( __CLASS__, 'ajax_join' ) );
        add_action( 'wp_ajax_nopriv_ag_queue_status', array( __CLASS__, 'ajax_public_status' ) );
        add_action( 'wp_ajax_ag_queue_status', array( __CLASS__, 'ajax_public_status' ) );

        // Save settings
        add_action( 'admin_post_ag_barber_save_settings', array( __CLASS__, 'handle_save_settings' ) );
        add_action( 'admin_post_ag_barber_clear_queue', array( __CLASS__, 'handle_clear_queue' ) );
    }

    // ─── Admin Dashboard ──────────────────────────────────────

    public static function render_admin() {
        $queue    = self::get_queue();
        $waiting  = array_filter( $queue, function ( $t ) { return $t['status'] === 'waiting'; } );
        $progress = array_filter( $queue, function ( $t ) { return $t['status'] === 'in_progress'; } );
        $done     = array_filter( $queue, function ( $t ) { return $t['status'] === 'done'; } );
        $settings = self::get_settings();
        $qr_url   = home_url( '/?ag_queue=join' );
        ?>
        <div class="wrap">
            <h1>💈 <?php esc_html_e( 'File d\'attente — Tableau de bord', 'ag-starter-barber' ); ?></h1>

            <!-- Stats rapides -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:20px 0;">
                <div style="background:#fff;padding:20px;border:1px solid #ddd;border-left:4px solid #D4B45C;text-align:center;">
                    <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;"><?php echo count( $waiting ); ?></div>
                    <div style="color:#666;">En attente</div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #ddd;border-left:4px solid #2196F3;text-align:center;">
                    <div style="font-size:2.4rem;font-weight:800;color:#2196F3;"><?php echo count( $progress ); ?></div>
                    <div style="color:#666;">En cours</div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #ddd;border-left:4px solid #28a745;text-align:center;">
                    <div style="font-size:2.4rem;font-weight:800;color:#28a745;"><?php echo count( $done ); ?></div>
                    <div style="color:#666;">Terminés</div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #ddd;border-left:4px solid #666;text-align:center;">
                    <div style="font-size:2.4rem;font-weight:800;">~<?php echo self::estimate_wait(); ?> min</div>
                    <div style="color:#666;">Temps d'attente</div>
                </div>
            </div>

            <!-- QR Code -->
            <div style="background:#fff;padding:20px;border:1px solid #ddd;margin-bottom:20px;display:flex;align-items:center;gap:20px;">
                <div>
                    <h3 style="margin:0 0 8px;">QR Code à afficher en vitrine</h3>
                    <p style="color:#666;margin:0 0 12px;">Les clients scannent ce code pour rejoindre la file d'attente depuis leur téléphone.</p>
                    <code style="font-size:.85rem;background:#f5f5f5;padding:6px 12px;border-radius:4px;"><?php echo esc_html( $qr_url ); ?></code>
                    <p style="margin-top:8px;">
                        <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?php echo urlencode( $qr_url ); ?>" target="_blank" class="button">📥 Télécharger le QR Code (PNG)</a>
                        <a href="https://api.qrserver.com/v1/create-qr-code/?size=800x800&format=svg&data=<?php echo urlencode( $qr_url ); ?>" target="_blank" class="button">📥 Format SVG (impression)</a>
                    </p>
                </div>
                <div style="flex-shrink:0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode( $qr_url ); ?>" alt="QR Code" width="150" height="150" style="border-radius:8px;">
                </div>
            </div>

            <!-- Liste de la file -->
            <div style="display:flex;gap:16px;margin-bottom:16px;">
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;">
                    <?php wp_nonce_field( 'ag_barber_clear_queue' ); ?>
                    <input type="hidden" name="action" value="ag_barber_clear_queue">
                    <button type="submit" class="button" onclick="return confirm('Vider toute la file ?');">🗑 Vider la file</button>
                </form>
                <button type="button" class="button button-primary" onclick="location.reload();">🔄 Rafraîchir</button>
            </div>

            <table class="widefat striped">
                <thead><tr>
                    <th>#</th><th>Ticket</th><th>Client</th><th>Service</th><th>Arrivé</th><th>Heure estimée</th><th>Statut</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php if ( empty( $queue ) ) : ?>
                    <tr><td colspan="8" style="text-align:center;padding:24px;color:#999;">Aucun client dans la file. Le QR code est prêt.</td></tr>
                <?php else : foreach ( $queue as $t ) :
                    $status_label = array( 'waiting' => '🟡 Attente', 'in_progress' => '🔵 En cours', 'done' => '✅ Terminé' );
                ?>
                    <tr<?php echo $t['status'] === 'done' ? ' style="opacity:.5;"' : ''; ?>>
                        <td><strong><?php echo esc_html( $t['number'] ); ?></strong></td>
                        <td><code><?php echo esc_html( $t['id'] ); ?></code></td>
                        <td><strong><?php echo esc_html( $t['name'] ); ?></strong><?php echo $t['phone'] ? '<br><small>' . esc_html( $t['phone'] ) . '</small>' : ''; ?></td>
                        <td><?php echo esc_html( $t['service'] ); ?><br><small><?php echo esc_html( $t['service_time'] ); ?> min</small></td>
                        <td><?php echo esc_html( $t['joined_at'] ); ?></td>
                        <td><strong><?php echo esc_html( $t['estimated_time'] ); ?></strong></td>
                        <td><?php echo $status_label[ $t['status'] ] ?? $t['status']; ?></td>
                        <td style="white-space:nowrap;">
                            <?php if ( 'waiting' === $t['status'] ) : ?>
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ag-barber-queue&do=start&ticket=' . $t['id'] ), 'ag_queue_action' ); ?>" class="button button-small button-primary">▶ Commencer</a>
                            <?php endif; ?>
                            <?php if ( 'in_progress' === $t['status'] ) : ?>
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ag-barber-queue&do=done&ticket=' . $t['id'] ), 'ag_queue_action' ); ?>" class="button button-small" style="background:#28a745;color:#fff;">✅ Terminé</a>
                            <?php endif; ?>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ag-barber-queue&do=remove&ticket=' . $t['id'] ), 'ag_queue_action' ); ?>" class="button button-small" onclick="return confirm('Retirer ce client ?');">✕</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        // Handle inline actions
        if ( isset( $_GET['do'], $_GET['ticket'], $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ag_queue_action' ) ) {
            $tid = sanitize_text_field( $_GET['ticket'] );
            switch ( $_GET['do'] ) {
                case 'start':  self::update_status( $tid, 'in_progress' ); break;
                case 'done':   self::update_status( $tid, 'done' ); break;
                case 'remove': self::remove( $tid ); break;
            }
            echo '<script>location.href="' . esc_url( admin_url( 'admin.php?page=ag-barber-queue' ) ) . '";</script>';
        }
    }

    // ─── Settings Page ────────────────────────────────────────

    public static function render_settings() {
        $s = self::get_settings();
        ?>
        <div class="wrap">
            <h1>⚙️ <?php esc_html_e( 'Réglages du Barbershop', 'ag-starter-barber' ); ?></h1>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <?php wp_nonce_field( 'ag_barber_save_settings' ); ?>
                <input type="hidden" name="action" value="ag_barber_save_settings">
                <table class="form-table">
                    <tr><th>Nom du salon</th><td><input type="text" name="shop_name" class="regular-text" value="<?php echo esc_attr( $s['shop_name'] ); ?>"></td></tr>
                    <tr><th>Temps moyen par coupe (min)</th><td><input type="number" name="avg_cut_time" value="<?php echo esc_attr( $s['avg_cut_time'] ); ?>" min="5" max="60" style="width:80px;"> <p class="description">Utilisé pour calculer le temps d'attente estimé.</p></td></tr>
                    <tr><th>Nombre de barbers en service</th><td><input type="number" name="num_barbers" value="<?php echo esc_attr( $s['num_barbers'] ); ?>" min="1" max="10" style="width:80px;"> <p class="description">Plus il y a de barbers, plus la file avance vite.</p></td></tr>
                    <tr><th>Heure d'ouverture</th><td><input type="number" name="opening_hour" value="<?php echo esc_attr( $s['opening_hour'] ); ?>" min="0" max="23" style="width:80px;">h</td></tr>
                    <tr><th>Heure de fermeture</th><td><input type="number" name="closing_hour" value="<?php echo esc_attr( $s['closing_hour'] ); ?>" min="0" max="23" style="width:80px;">h</td></tr>
                </table>
                <h2>Prestations</h2>
                <p class="description">Nom, prix (€) et durée (min) de chaque prestation. Laissez vide pour supprimer une ligne.</p>
                <table class="widefat" style="max-width:700px;">
                    <thead><tr><th>Prestation</th><th>Prix (€)</th><th>Durée (min)</th></tr></thead>
                    <tbody>
                    <?php for ( $i = 0; $i < 8; $i++ ) :
                        $svc = $s['services'][ $i ] ?? array( 'name' => '', 'price' => '', 'time' => '' );
                    ?>
                        <tr>
                            <td><input type="text" name="svc_name[]" value="<?php echo esc_attr( $svc['name'] ); ?>" class="regular-text"></td>
                            <td><input type="number" name="svc_price[]" value="<?php echo esc_attr( $svc['price'] ); ?>" min="0" style="width:80px;"></td>
                            <td><input type="number" name="svc_time[]" value="<?php echo esc_attr( $svc['time'] ); ?>" min="5" style="width:80px;"></td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
                <p><button type="submit" class="button button-primary button-hero">Enregistrer</button></p>
            </form>
        </div>
        <?php
    }

    // ─── Handlers ─────────────────────────────────────────────

    public static function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
        check_admin_referer( 'ag_barber_save_settings' );

        $services = array();
        $names  = $_POST['svc_name'] ?? array();
        $prices = $_POST['svc_price'] ?? array();
        $times  = $_POST['svc_time'] ?? array();
        for ( $i = 0; $i < count( $names ); $i++ ) {
            $n = sanitize_text_field( $names[ $i ] );
            if ( $n ) {
                $services[] = array(
                    'name'  => $n,
                    'price' => absint( $prices[ $i ] ?? 10 ),
                    'time'  => max( 5, absint( $times[ $i ] ?? 15 ) ),
                );
            }
        }

        update_option( self::SETTINGS_KEY, array(
            'shop_name'    => sanitize_text_field( $_POST['shop_name'] ?? '' ),
            'avg_cut_time' => max( 5, absint( $_POST['avg_cut_time'] ?? 15 ) ),
            'num_barbers'  => max( 1, absint( $_POST['num_barbers'] ?? 2 ) ),
            'opening_hour' => absint( $_POST['opening_hour'] ?? 9 ),
            'closing_hour' => absint( $_POST['closing_hour'] ?? 20 ),
            'services'     => $services,
        ) );

        wp_safe_redirect( admin_url( 'admin.php?page=ag-barber-settings&saved=1' ) );
        exit;
    }

    public static function handle_clear_queue() {
        check_admin_referer( 'ag_barber_clear_queue' );
        self::clear_all();
        wp_safe_redirect( admin_url( 'admin.php?page=ag-barber-queue' ) );
        exit;
    }

    // ─── Public AJAX: join queue ──────────────────────────────

    public static function ajax_join() {
        check_ajax_referer( 'ag_queue_nonce', 'nonce' );
        $name    = sanitize_text_field( $_POST['name'] ?? '' );
        $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
        $service = sanitize_text_field( $_POST['service'] ?? '' );

        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => 'Prénom requis.' ) );
        }

        $ticket = self::join( $name, $phone, $service );
        wp_send_json_success( $ticket );
    }

    public static function ajax_public_status() {
        wp_send_json_success( array(
            'waiting'       => self::count_waiting(),
            'estimated_wait'=> self::estimate_wait(),
            'estimated_time'=> self::estimate_time(),
        ) );
    }
}
