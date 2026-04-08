<?php
/**
 * Alliance Groupe — Import depuis GitHub (1 clic)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'AG_IMPORT_LOADED' ) ) {
    return;
}
define( 'AG_IMPORT_LOADED', true );

// ── Admin menu ──────────────────────────────────────────────────
add_action( 'admin_menu', function () {
    add_management_page(
        'Import Alliance Groupe',
        'Import AG',
        'manage_options',
        'ag-import',
        'ag_import_render_page'
    );
} );

// ── Page admin ──────────────────────────────────────────────────
function ag_import_render_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Accès refusé.' );
    }

    $last_sync = get_option( 'ag_last_sync', 0 );
    $last_ver  = get_option( 'ag_last_version', '-' );
    $repo      = 'khalidawi44/Alliance-groupe';
    $branch    = 'claude/rebuild-alliance-theme-fl7ca';
    $base      = 'https://raw.githubusercontent.com/' . $repo . '/' . $branch . '/content';

    echo '<div class="wrap">';
    echo '<h1>Alliance Groupe — Import depuis GitHub</h1>';

    // Action: Sync
    if ( isset( $_POST['ag_do_sync'] ) && check_admin_referer( 'ag_sync_nonce' ) ) {
        if ( $last_sync && ( time() - $last_sync ) < 300 ) {
            echo '<div class="notice notice-error"><p>Patientez 5 min entre chaque sync.</p></div>';
        } else {
            ag_do_sync( $base );
            $last_sync = time();
            $last_ver  = get_option( 'ag_last_version', '-' );
        }
    }

    // Action: Clear logs
    if ( isset( $_POST['ag_clear'] ) && check_admin_referer( 'ag_clear_nonce' ) ) {
        delete_option( 'ag_sync_log' );
        echo '<div class="notice notice-success"><p>Logs vidés.</p></div>';
    }

    // Dashboard
    echo '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin:20px 0;">';
    echo '<div style="background:#fff;padding:20px;border-left:4px solid #0073aa;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">Statut</h3>';
    echo '<p>Dernière sync : <strong>' . ( $last_sync ? date( 'd/m/Y H:i', $last_sync ) : 'Jamais' ) . '</strong></p>';
    echo '<p>Version : <strong>' . esc_html( $last_ver ) . '</strong></p>';
    echo '</div>';
    echo '<div style="background:#fff;padding:20px;border-left:4px solid #6f42c1;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">GitHub</h3>';
    echo '<p>Repo : <code>' . esc_html( $repo ) . '</code></p>';
    echo '<p><a href="https://github.com/' . esc_attr( $repo ) . '/tree/' . esc_attr( $branch ) . '/content" target="_blank">Voir sur GitHub</a></p>';
    echo '</div>';
    echo '<div style="background:#fff;padding:20px;border-left:4px solid #28a745;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">Ajouter du contenu</h3>';
    echo '<ol style="margin-left:18px;line-height:2;">';
    echo '<li>Ajoute un JSON dans <code>content/</code></li>';
    echo '<li>Ajoute-le dans <code>manifest.json</code></li>';
    echo '<li>Push sur GitHub</li>';
    echo '<li>Clique Synchroniser ci-dessous</li>';
    echo '</ol></div></div>';

    // Bouton Sync
    echo '<div style="background:#f0f6fc;padding:28px;border:2px solid #0073aa;border-radius:8px;margin:20px 0;text-align:center;">';
    echo '<h2 style="margin-top:0;">Synchroniser depuis GitHub</h2>';
    echo '<p>Récupère les pages et articles depuis GitHub et les importe. Pas de doublons.</p>';
    echo '<form method="post">';
    wp_nonce_field( 'ag_sync_nonce' );
    echo '<input type="submit" name="ag_do_sync" class="button button-primary button-hero" value="Synchroniser depuis GitHub">';
    echo '</form></div>';

    // Logs
    $logs = get_option( 'ag_sync_log', array() );
    if ( ! empty( $logs ) ) {
        echo '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin:20px 0;">';
        echo '<div style="display:flex;justify-content:space-between;align-items:center;">';
        echo '<h3 style="margin:0;">Journal</h3>';
        echo '<form method="post">';
        wp_nonce_field( 'ag_clear_nonce' );
        echo '<button type="submit" name="ag_clear" class="button button-small">Vider</button>';
        echo '</form></div>';
        echo '<table class="widefat striped" style="margin-top:12px;"><thead><tr><th>Date</th><th>Message</th></tr></thead><tbody>';
        foreach ( array_reverse( $logs ) as $l ) {
            echo '<tr><td>' . esc_html( $l['d'] ) . '</td><td>' . esc_html( $l['m'] ) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    echo '</div>';
}

// ── Sync principale ─────────────────────────────────────────────
function ag_do_sync( $base ) {
    $results = array();

    // 1. Manifest
    $manifest_url = $base . '/manifest.json';
    $manifest = ag_fetch_json( $manifest_url );
    if ( ! $manifest ) {
        echo '<div class="notice notice-error"><p>Impossible de charger manifest.json depuis GitHub.</p></div>';
        ag_log( 'ERREUR: manifest.json inaccessible' );
        return;
    }
    $version = isset( $manifest['version'] ) ? $manifest['version'] : '?';
    $results[] = 'Manifest v' . $version . ' OK';

    // 2. Catégories
    $cat_ids = array();
    if ( ! empty( $manifest['categories'] ) ) {
        foreach ( $manifest['categories'] as $c ) {
            $term = get_term_by( 'slug', $c['slug'], 'category' );
            if ( $term ) {
                $cat_ids[ $c['name'] ] = $term->term_id;
            } else {
                $r = wp_insert_term( $c['name'], 'category', array( 'slug' => $c['slug'] ) );
                $cat_ids[ $c['name'] ] = is_wp_error( $r ) ? 0 : $r['term_id'];
            }
        }
        $results[] = count( $manifest['categories'] ) . ' catégories OK';
    }

    // 3. Pages
    $page_ids = array();
    if ( ! empty( $manifest['pages'] ) ) {
        foreach ( $manifest['pages'] as $path ) {
            $data = ag_fetch_json( $base . '/' . $path );
            if ( ! $data ) { $results[] = 'ERREUR page: ' . $path; continue; }

            $existing = get_page_by_path( $data['slug'] );
            if ( $existing ) {
                $page_ids[ $data['slug'] ] = $existing->ID;
                continue;
            }

            $id = wp_insert_post( array(
                'post_title'    => sanitize_text_field( $data['title'] ),
                'post_name'     => sanitize_title( $data['slug'] ),
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => '',
                'page_template' => isset( $data['template'] ) ? sanitize_text_field( $data['template'] ) : '',
            ) );
            if ( $id && ! is_wp_error( $id ) ) {
                $page_ids[ $data['slug'] ] = $id;
                $results[] = 'Page "' . $data['title'] . '" créée';
            }
        }
    }

    // 4. Réglages
    if ( ! empty( $manifest['settings'] ) ) {
        $s = $manifest['settings'];
        if ( ! empty( $s['front_page'] ) && isset( $page_ids[ $s['front_page'] ] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $page_ids[ $s['front_page'] ] );
        }
        if ( ! empty( $s['blog_page'] ) && isset( $page_ids[ $s['blog_page'] ] ) ) {
            update_option( 'page_for_posts', $page_ids[ $s['blog_page'] ] );
        }
        if ( ! empty( $s['permalink_structure'] ) ) {
            global $wp_rewrite;
            $wp_rewrite->set_permalink_structure( $s['permalink_structure'] );
            $wp_rewrite->flush_rules();
        }
        if ( ! empty( $s['blogname'] ) ) update_option( 'blogname', $s['blogname'] );
        if ( ! empty( $s['blogdescription'] ) ) update_option( 'blogdescription', $s['blogdescription'] );
        $results[] = 'Réglages OK';
    }

    // 5. Menu
    if ( ! empty( $manifest['menu'] ) ) {
        $m = $manifest['menu'];
        if ( ! wp_get_nav_menu_object( $m['name'] ) ) {
            $menu_id = wp_create_nav_menu( $m['name'] );
            $labels  = array( 'accueil'=>'Accueil','services'=>'Services','realisations'=>'Réalisations','a-propos'=>'À propos','blog'=>'Blog','contact'=>'Contact' );
            $pos = 0;
            foreach ( $m['items'] as $slug ) {
                if ( isset( $page_ids[ $slug ] ) ) {
                    wp_update_nav_menu_item( $menu_id, 0, array(
                        'menu-item-title'     => isset( $labels[ $slug ] ) ? $labels[ $slug ] : $slug,
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $page_ids[ $slug ],
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                        'menu-item-position'  => $pos++,
                    ) );
                }
            }
            if ( ! empty( $m['location'] ) ) {
                $locs = get_theme_mod( 'nav_menu_locations' );
                if ( ! is_array( $locs ) ) $locs = array();
                $locs[ $m['location'] ] = $menu_id;
                set_theme_mod( 'nav_menu_locations', $locs );
            }
            $results[] = 'Menu créé';
        } else {
            $results[] = 'Menu existe déjà';
        }
    }

    // 6. Articles
    $new = 0; $skip = 0;
    if ( ! empty( $manifest['articles'] ) ) {
        foreach ( $manifest['articles'] as $path ) {
            $a = ag_fetch_json( $base . '/' . $path );
            if ( ! $a ) { $results[] = 'ERREUR article: ' . $path; continue; }

            $existing = get_page_by_path( $a['slug'], OBJECT, 'post' );
            if ( $existing ) { $skip++; continue; }

            $cat_id = 0;
            if ( ! empty( $a['category'] ) && isset( $cat_ids[ $a['category'] ] ) ) {
                $cat_id = $cat_ids[ $a['category'] ];
            }

            $pid = wp_insert_post( array(
                'post_title'    => sanitize_text_field( $a['title'] ),
                'post_name'     => sanitize_title( $a['slug'] ),
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_content'  => wp_kses_post( $a['content'] ),
                'post_excerpt'  => sanitize_text_field( isset( $a['excerpt'] ) ? $a['excerpt'] : '' ),
                'post_category' => $cat_id ? array( $cat_id ) : array(),
            ) );
            if ( $pid && ! is_wp_error( $pid ) ) {
                if ( ! empty( $a['tags'] ) ) wp_set_post_tags( $pid, $a['tags'] );
                $new++;
            }
        }
        if ( $new )  $results[] = $new . ' articles importés';
        if ( $skip ) $results[] = $skip . ' articles existants ignorés';
    }

    update_option( 'ag_last_version', $version );
    update_option( 'ag_last_sync', time() );

    foreach ( $results as $r ) ag_log( $r );
    ag_log( 'Sync v' . $version . ' terminée' );

    // Afficher
    echo '<div style="background:#d4edda;padding:24px;border-left:4px solid #28a745;border-radius:4px;margin:20px 0;">';
    echo '<h2>Sync terminée (v' . esc_html( $version ) . ')</h2><ul>';
    foreach ( $results as $r ) echo '<li>' . esc_html( $r ) . '</li>';
    echo '</ul></div>';
}

// ── Fetch JSON helper ───────────────────────────────────────────
function ag_fetch_json( $url ) {
    $resp = wp_remote_get( $url, array( 'timeout' => 30, 'sslverify' => true ) );
    if ( is_wp_error( $resp ) ) return false;
    if ( wp_remote_retrieve_response_code( $resp ) !== 200 ) return false;
    $data = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( json_last_error() !== JSON_ERROR_NONE ) return false;
    return $data;
}

// ── Logger ──────────────────────────────────────────────────────
function ag_log( $msg ) {
    $logs = get_option( 'ag_sync_log', array() );
    $logs[] = array( 'd' => current_time( 'd/m/Y H:i:s' ), 'm' => sanitize_text_field( $msg ) );
    if ( count( $logs ) > 100 ) $logs = array_slice( $logs, -100 );
    update_option( 'ag_sync_log', $logs );
}
