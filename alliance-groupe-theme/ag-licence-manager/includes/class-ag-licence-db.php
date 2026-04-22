<?php
/**
 * Database schema and helpers for the licence table.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_DB {

    /** Table name (without prefix). */
    const TABLE = 'ag_licences';

    /**
     * Get the full table name with WP prefix.
     */
    public static function table() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    /**
     * Create / update the table on plugin activation.
     */
    public static function install() {
        global $wpdb;
        $table   = self::table();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            licence_key_hash VARCHAR(64) NOT NULL,
            licence_key_enc VARCHAR(255) DEFAULT NULL,
            licence_prefix VARCHAR(10) NOT NULL,
            tier VARCHAR(20) NOT NULL DEFAULT 'pro',
            theme_slug VARCHAR(100) DEFAULT NULL,
            email VARCHAR(191) NOT NULL,
            domain VARCHAR(191) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'inactive',
            stripe_session VARCHAR(128) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            activated_at DATETIME DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            last_check DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY idx_hash (licence_key_hash),
            KEY idx_email (email),
            KEY idx_domain (domain),
            KEY idx_status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'ag_lm_db_version', AG_LM_VERSION );
    }

    /**
     * Generate a licence key with tier prefix + UUID.
     *
     * @param string $tier pro|premium|business
     * @return string Clear-text licence key (store hash, send this to client ONCE)
     */
    public static function generate_key( $tier = 'pro' ) {
        $prefixes = array(
            'pro'      => 'AGPRO',
            'premium'  => 'AGPRM',
            'business' => 'AGBUS',
        );
        $prefix = isset( $prefixes[ $tier ] ) ? $prefixes[ $tier ] : 'AGPRO';
        return $prefix . '-' . wp_generate_uuid4();
    }

    /**
     * Hash a clear-text licence key for storage.
     */
    public static function hash_key( $key ) {
        return hash( 'sha256', strtoupper( trim( $key ) ) );
    }

    /**
     * Encrypt a clear-text key for admin retrieval.
     */
    public static function encrypt_key( $clear_key ) {
        if ( ! function_exists( 'openssl_encrypt' ) ) {
            return base64_encode( $clear_key );
        }
        $method = 'aes-256-cbc';
        $key    = substr( hash( 'sha256', AG_LICENCE_HMAC_KEY ), 0, 32 );
        $iv     = substr( hash( 'sha256', 'ag-iv-salt' ), 0, 16 );
        $encrypted = openssl_encrypt( $clear_key, $method, $key, 0, $iv );
        return $encrypted ? base64_encode( $encrypted ) : base64_encode( $clear_key );
    }

    /**
     * Decrypt an encrypted key for admin display.
     */
    public static function decrypt_key( $encrypted ) {
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return base64_decode( $encrypted );
        }
        $method = 'aes-256-cbc';
        $key    = substr( hash( 'sha256', AG_LICENCE_HMAC_KEY ), 0, 32 );
        $iv     = substr( hash( 'sha256', 'ag-iv-salt' ), 0, 16 );
        $decrypted = openssl_decrypt( base64_decode( $encrypted ), $method, $key, 0, $iv );
        return $decrypted ? $decrypted : base64_decode( $encrypted );
    }

    /**
     * Insert a new licence.
     *
     * @param string $clear_key   The clear-text licence key.
     * @param string $tier        pro|premium|business
     * @param string $email       Buyer email.
     * @param string $stripe_sess Optional Stripe checkout session ID.
     * @param string $theme_slug  Optional specific theme slug.
     * @return int|false  Inserted row ID or false on failure.
     */
    public static function insert( $clear_key, $tier, $email, $stripe_sess = '', $theme_slug = '' ) {
        global $wpdb;

        $prefix_map = array(
            'pro'      => 'AGPRO',
            'premium'  => 'AGPRM',
            'business' => 'AGBUS',
        );

        $data = array(
            'licence_key_hash' => self::hash_key( $clear_key ),
            'licence_key_enc'  => self::encrypt_key( $clear_key ),
            'licence_prefix'   => isset( $prefix_map[ $tier ] ) ? $prefix_map[ $tier ] : 'AGPRO',
            'tier'             => $tier,
            'email'            => sanitize_email( $email ),
            'status'           => 'inactive',
            'created_at'       => current_time( 'mysql' ),
        );
        $formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

        if ( $theme_slug ) {
            $data['theme_slug'] = $theme_slug;
            $formats[] = '%s';
        }
        if ( $stripe_sess ) {
            $data['stripe_session'] = $stripe_sess;
            $formats[] = '%s';
        }

        $result = $wpdb->insert( self::table(), $data, $formats );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Find a licence row by its clear-text key.
     *
     * @return object|null
     */
    /**
     * Find a licence by ID.
     */
    public static function find_by_id( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table() . " WHERE id = %d", absint( $id )
        ) );
    }

    /**
     * Delete a licence by ID.
     */
    public static function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
    }

    public static function find_by_key( $clear_key ) {
        global $wpdb;
        $hash = self::hash_key( $clear_key );
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table() . " WHERE licence_key_hash = %s",
            $hash
        ) );
    }

    /**
     * Update a licence row.
     *
     * @param int   $id   Row ID.
     * @param array $data Columns to update.
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $wpdb->update( self::table(), $data, array( 'id' => $id ) );
    }

    /**
     * Get all licences (admin listing).
     */
    public static function get_all( $args = array() ) {
        global $wpdb;
        $table = self::table();

        $where = '1=1';
        $params = array();

        if ( ! empty( $args['status'] ) ) {
            $where .= ' AND status = %s';
            $params[] = $args['status'];
        }
        if ( ! empty( $args['tier'] ) ) {
            $where .= ' AND tier = %s';
            $params[] = $args['tier'];
        }
        if ( ! empty( $args['email'] ) ) {
            $where .= ' AND email LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $args['email'] ) . '%';
        }

        $limit  = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 50;
        $offset = isset( $args['offset'] ) ? absint( $args['offset'] ) : 0;

        $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";

        if ( $params ) {
            $sql = $wpdb->prepare( $sql, ...$params );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Count licences by status.
     */
    public static function count_by_status() {
        global $wpdb;
        $table = self::table();
        return $wpdb->get_results(
            "SELECT status, COUNT(*) as cnt FROM {$table} GROUP BY status",
            OBJECT_K
        );
    }
}
