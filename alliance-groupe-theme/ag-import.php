<?php
/**
 * Alliance Groupe — Import depuis GitHub (1 clic)
 *
 * Fonctionnement :
 * 1. Le contenu (articles, pages) est stocké en JSON sur GitHub
 * 2. Ce script lit le manifest.json depuis GitHub
 * 3. Il télécharge et importe chaque élément dans WordPress
 * 4. Les éléments existants ne sont jamais dupliqués
 *
 * Pour ajouter du contenu :
 * - Ajoute un fichier JSON dans content/articles/ ou content/pages/ sur GitHub
 * - Ajoute le chemin dans content/manifest.json
 * - Push sur GitHub
 * - Clique "Synchroniser depuis GitHub" dans WordPress
 *
 * Sécurité :
 * - Admin only (manage_options)
 * - Nonce CSRF
 * - Rate limiting 5 min entre syncs
 * - Logs complets
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Empêcher le double chargement
if (defined('AG_IMPORT_LOADED')) {
    return;
}
define('AG_IMPORT_LOADED', true);

/* ── Configuration GitHub ────────────────────────────────────── */
if (!defined('AG_GITHUB_REPO'))     define('AG_GITHUB_REPO', 'khalidawi44/Alliance-groupe');
if (!defined('AG_GITHUB_BRANCH'))   define('AG_GITHUB_BRANCH', 'claude/rebuild-alliance-theme-fl7ca');
if (!defined('AG_GITHUB_RAW_BASE')) define('AG_GITHUB_RAW_BASE', 'https://raw.githubusercontent.com/' . AG_GITHUB_REPO . '/' . AG_GITHUB_BRANCH . '/content');
if (!defined('AG_MANIFEST_URL'))    define('AG_MANIFEST_URL', AG_GITHUB_RAW_BASE . '/manifest.json');

/* ── Constantes ──────────────────────────────────────────────── */
if (!defined('AG_IMPORT_LOG_KEY'))  define('AG_IMPORT_LOG_KEY', 'ag_import_log');
if (!defined('AG_IMPORT_LAST_SYNC')) define('AG_IMPORT_LAST_SYNC', 'ag_import_last_sync');
if (!defined('AG_IMPORT_VERSION'))  define('AG_IMPORT_VERSION', 'ag_import_version');
if (!defined('AG_SYNC_RATE_LIMIT')) define('AG_SYNC_RATE_LIMIT', 300);

/* ── Admin menu ──────────────────────────────────────────────── */
add_action('admin_menu', function () {
    add_management_page(
        'Import Alliance Groupe',
        'Import AG',
        'manage_options',
        'ag-import',
        'ag_import_page'
    );
});

/* ── Fetch JSON depuis GitHub ────────────────────────────────── */
if (!function_exists('ag_fetch_github')) :
function ag_fetch_github($url) {
    $response = wp_remote_get($url, [
        'timeout'   => 30,
        'sslverify' => true,
        'headers'   => [
            'Accept'     => 'application/json',
            'User-Agent' => 'Alliance-Groupe-WP-Import/1.0',
        ],
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        return ['error' => 'HTTP ' . $code . ' — Fichier introuvable sur GitHub'];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'JSON invalide : ' . json_last_error_msg()];
    }

    return $data;
}
endif;

/* ── Page admin ──────────────────────────────────────────────── */
if (!function_exists('ag_import_page')) :
function ag_import_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé', 'Accès refusé', ['response' => 403]);
    }

    $last_sync   = get_option(AG_IMPORT_LAST_SYNC, 0);
    $last_version = get_option(AG_IMPORT_VERSION, 'jamais');

    echo '<div class="wrap">';
    echo '<h1>Alliance Groupe — Import depuis GitHub</h1>';

    // ── Action : Synchroniser ──
    if (isset($_POST['ag_sync_github']) && wp_verify_nonce($_POST['ag_sync_nonce'], 'ag_sync_action')) {
        // Rate limiting
        if ($last_sync && (time() - $last_sync) < AG_SYNC_RATE_LIMIT) {
            $remaining = ceil((AG_SYNC_RATE_LIMIT - (time() - $last_sync)) / 60);
            echo '<div class="notice notice-error"><p>Patientez encore ' . $remaining . ' min avant la prochaine synchronisation.</p></div>';
        } else {
            ag_add_log('Sync lancée par ' . wp_get_current_user()->user_login);
            ag_run_github_sync();
            $last_sync = time();
            $last_version = get_option(AG_IMPORT_VERSION, '?');
        }
    }

    // ── Action : Vider les logs ──
    if (isset($_POST['ag_clear_logs']) && wp_verify_nonce($_POST['ag_clear_nonce'], 'ag_clear_logs_action')) {
        delete_option(AG_IMPORT_LOG_KEY);
        echo '<div class="notice notice-success"><p>Logs vidés.</p></div>';
    }

    // ── Dashboard ──
    echo '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin:20px 0;">';

    // Card : Statut
    echo '<div style="background:#fff;padding:20px;border-left:4px solid #0073aa;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">Statut</h3>';
    echo '<p><strong>Dernière sync :</strong> ' . ($last_sync ? date('d/m/Y H:i', $last_sync) : 'Jamais') . '</p>';
    echo '<p><strong>Version :</strong> ' . esc_html($last_version) . '</p>';
    echo '<p><strong>Source :</strong> <code>' . esc_html(AG_GITHUB_REPO) . '</code></p>';
    echo '</div>';

    // Card : Dépôt GitHub
    echo '<div style="background:#fff;padding:20px;border-left:4px solid #6f42c1;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">Dépôt GitHub</h3>';
    echo '<p><strong>Branche :</strong> <code>' . esc_html(AG_GITHUB_BRANCH) . '</code></p>';
    echo '<p><strong>Manifest :</strong> <code>content/manifest.json</code></p>';
    echo '<p><a href="https://github.com/' . esc_attr(AG_GITHUB_REPO) . '/tree/' . esc_attr(AG_GITHUB_BRANCH) . '/content" target="_blank">Voir le contenu sur GitHub →</a></p>';
    echo '</div>';

    // Card : Comment ajouter
    echo '<div style="background:#fff;padding:20px;border-left:4px solid #28a745;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">Ajouter du contenu</h3>';
    echo '<ol style="margin-left:18px;line-height:2;">';
    echo '<li>Ajoute un JSON dans <code>content/articles/</code></li>';
    echo '<li>Ajoute le chemin dans <code>manifest.json</code></li>';
    echo '<li>Push sur GitHub</li>';
    echo '<li>Clique le bouton ci-dessous</li>';
    echo '</ol>';
    echo '</div>';

    echo '</div>';

    // ── Bouton Sync ──
    echo '<div style="background:#f0f6fc;padding:28px;border:2px solid #0073aa;border-radius:8px;margin:20px 0;text-align:center;">';
    echo '<h2 style="margin-top:0;">Synchroniser depuis GitHub</h2>';
    echo '<p>Récupère le <code>manifest.json</code> depuis GitHub, télécharge les pages et articles, et les importe dans WordPress.<br>Les éléments existants sont ignorés (pas de doublons).</p>';
    echo '<form method="post" style="display:inline;" onsubmit="return confirm(\'Lancer la synchronisation depuis GitHub ?\');">';
    wp_nonce_field('ag_sync_action', 'ag_sync_nonce');
    submit_button('Synchroniser depuis GitHub', 'primary large', 'ag_sync_github', false);
    echo '</form>';
    echo '</div>';

    // ── Journal ──
    $logs = get_option(AG_IMPORT_LOG_KEY, []);
    if (!empty($logs)) {
        echo '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin:20px 0;">';
        echo '<div style="display:flex;justify-content:space-between;align-items:center;">';
        echo '<h3 style="margin:0;">Journal d\'activité</h3>';
        echo '<form method="post" style="display:inline;">';
        wp_nonce_field('ag_clear_logs_action', 'ag_clear_nonce');
        echo '<button type="submit" name="ag_clear_logs" class="button button-small">Vider les logs</button>';
        echo '</form>';
        echo '</div>';
        echo '<table class="widefat striped" style="margin-top:12px;">';
        echo '<thead><tr><th style="width:160px;">Date</th><th>Événement</th></tr></thead><tbody>';
        foreach (array_reverse($logs) as $log) {
            echo '<tr><td style="white-space:nowrap;">' . esc_html($log['date']) . '</td><td>' . esc_html($log['message']) . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    echo '</div>';
}
endif;

/* ── Synchronisation principale ──────────────────────────────── */
if (!function_exists('ag_run_github_sync')) :
function ag_run_github_sync() {
    if (!current_user_can('manage_options') || !is_admin()) {
        wp_die('Accès refusé', 'Erreur', ['response' => 403]);
    }

    $results = [];

    // 1. Récupérer le manifest
    echo '<div style="background:#fff;padding:24px;border:1px solid #ddd;border-radius:8px;margin:20px 0;">';
    echo '<h2>Synchronisation en cours...</h2>';

    $manifest = ag_fetch_github(AG_MANIFEST_URL);
    if (isset($manifest['error'])) {
        $msg = 'ERREUR manifest.json : ' . $manifest['error'];
        echo '<p style="color:red;">' . esc_html($msg) . '</p></div>';
        ag_add_log($msg);
        return;
    }

    $version = isset($manifest['version']) ? $manifest['version'] : 'inconnue';
    $base_url = isset($manifest['base_url']) ? $manifest['base_url'] : AG_GITHUB_RAW_BASE;
    $results[] = 'Manifest v' . $version . ' récupéré depuis GitHub';

    // 2. Catégories
    $cat_ids = [];
    if (!empty($manifest['categories'])) {
        foreach ($manifest['categories'] as $cat) {
            $cat_ids[$cat['name']] = ag_get_or_create_category($cat['name'], $cat['slug']);
        }
        $results[] = count($manifest['categories']) . ' catégories vérifiées';
    }

    // 3. Pages
    $page_ids = [];
    if (!empty($manifest['pages'])) {
        foreach ($manifest['pages'] as $page_path) {
            $page_url = $base_url . '/' . $page_path;
            $page_data = ag_fetch_github($page_url);

            if (isset($page_data['error'])) {
                $results[] = 'ERREUR page ' . $page_path . ' : ' . $page_data['error'];
                continue;
            }

            $slug = $page_data['slug'];
            $existing = get_page_by_path($slug);
            if ($existing) {
                $page_ids[$slug] = $existing->ID;
                $results[] = 'Page "' . $page_data['title'] . '" existe déjà';
                continue;
            }

            $id = wp_insert_post([
                'post_title'    => sanitize_text_field($page_data['title']),
                'post_name'     => sanitize_title($slug),
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => '',
                'page_template' => sanitize_text_field($page_data['template']),
            ]);

            if ($id && !is_wp_error($id)) {
                $page_ids[$slug] = $id;
                $results[] = 'Page "' . $page_data['title'] . '" créée (ID: ' . $id . ')';
            }
        }
    }

    // 4. Réglages WordPress
    if (!empty($manifest['settings'])) {
        $s = $manifest['settings'];
        if (!empty($s['front_page']) && isset($page_ids[$s['front_page']])) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $page_ids[$s['front_page']]);
        }
        if (!empty($s['blog_page']) && isset($page_ids[$s['blog_page']])) {
            update_option('page_for_posts', $page_ids[$s['blog_page']]);
        }
        if (!empty($s['permalink_structure'])) {
            global $wp_rewrite;
            $wp_rewrite->set_permalink_structure($s['permalink_structure']);
            $wp_rewrite->flush_rules();
        }
        if (!empty($s['blogname'])) update_option('blogname', sanitize_text_field($s['blogname']));
        if (!empty($s['blogdescription'])) update_option('blogdescription', sanitize_text_field($s['blogdescription']));
        $results[] = 'Réglages WordPress configurés';
    }

    // 5. Menu
    if (!empty($manifest['menu'])) {
        $m = $manifest['menu'];
        $menu_exists = wp_get_nav_menu_object($m['name']);
        if (!$menu_exists) {
            $menu_id = wp_create_nav_menu($m['name']);
            $pos = 0;
            $labels = [
                'accueil' => 'Accueil', 'services' => 'Services', 'realisations' => 'Réalisations',
                'a-propos' => 'À propos', 'blog' => 'Blog', 'contact' => 'Contact'
            ];
            foreach ($m['items'] as $slug) {
                if (isset($page_ids[$slug])) {
                    wp_update_nav_menu_item($menu_id, 0, [
                        'menu-item-title'     => isset($labels[$slug]) ? $labels[$slug] : $slug,
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $page_ids[$slug],
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                        'menu-item-position'  => $pos++,
                    ]);
                }
            }
            if (!empty($m['location'])) {
                $locations = get_theme_mod('nav_menu_locations');
                $locations[$m['location']] = $menu_id;
                set_theme_mod('nav_menu_locations', $locations);
            }
            $results[] = 'Menu "' . $m['name'] . '" créé';
        } else {
            $results[] = 'Menu "' . $m['name'] . '" existe déjà';
        }
    }

    // 6. Articles
    $new_articles = 0;
    $skip_articles = 0;
    if (!empty($manifest['articles'])) {
        foreach ($manifest['articles'] as $article_path) {
            $article_url = $base_url . '/' . $article_path;
            $a = ag_fetch_github($article_url);

            if (isset($a['error'])) {
                $results[] = 'ERREUR article ' . $article_path . ' : ' . $a['error'];
                continue;
            }

            $slug = sanitize_title($a['slug']);
            $existing = get_page_by_path($slug, OBJECT, 'post');
            if ($existing) {
                $skip_articles++;
                continue;
            }

            $cat_name = isset($a['category']) ? $a['category'] : '';
            $cat_id = isset($cat_ids[$cat_name]) ? $cat_ids[$cat_name] : 0;

            $post_id = wp_insert_post([
                'post_title'    => sanitize_text_field($a['title']),
                'post_name'     => $slug,
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_content'  => wp_kses_post($a['content']),
                'post_excerpt'  => sanitize_text_field(isset($a['excerpt']) ? $a['excerpt'] : ''),
                'post_category' => $cat_id ? [$cat_id] : [],
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                if (!empty($a['tags'])) {
                    wp_set_post_tags($post_id, sanitize_text_field($a['tags']));
                }
                $new_articles++;
            }
        }
        if ($new_articles > 0) $results[] = $new_articles . ' nouveaux articles importés';
        if ($skip_articles > 0) $results[] = $skip_articles . ' articles existants ignorés';
    }

    // Sauvegarder la version et l'heure
    update_option(AG_IMPORT_VERSION, $version);
    update_option(AG_IMPORT_LAST_SYNC, time());

    // Logger
    foreach ($results as $r) {
        ag_add_log($r);
    }
    ag_add_log('Sync v' . $version . ' terminée');

    // Afficher résultats
    echo '<table class="widefat striped" style="margin-top:12px;">';
    echo '<tbody>';
    foreach ($results as $r) {
        $icon = (strpos($r, 'ERREUR') !== false) ? '❌' : '✅';
        echo '<tr><td>' . $icon . ' ' . esc_html($r) . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p style="margin-top:16px;color:#46b450;font-weight:bold;">Synchronisation v' . esc_html($version) . ' terminée !</p>';
    echo '</div>';
}
endif;

/* ── Helpers ──────────────────────────────────────────────────── */
if (!function_exists('ag_get_or_create_category')) :
function ag_get_or_create_category($name, $slug) {
    $term = get_term_by('slug', $slug, 'category');
    if ($term) return $term->term_id;
    $result = wp_insert_term($name, 'category', ['slug' => $slug]);
    return is_wp_error($result) ? 0 : $result['term_id'];
}
endif;

if (!function_exists('ag_add_log')) :
function ag_add_log($message) {
    $logs = get_option(AG_IMPORT_LOG_KEY, []);
    $logs[] = [
        'date'    => current_time('d/m/Y H:i:s'),
        'message' => sanitize_text_field($message),
    ];
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }
    update_option(AG_IMPORT_LOG_KEY, $logs);
}
endif;
