<?php
/**
 * Alliance Groupe Theme — functions.php
 * Thème autonome (pas besoin d'Elementor)
 */

// ── 1. Charger ag-import.php ────────────────────────────────────
$ag_import_file = get_stylesheet_directory() . '/ag-import.php';
if ( file_exists( $ag_import_file ) ) {
    require_once $ag_import_file;
}

// ── 1b. Charger ag-stripe-admin.php (page Liens de paiement) ─────
if ( is_admin() ) {
    $ag_stripe_admin_file = get_stylesheet_directory() . '/ag-stripe-admin.php';
    if ( file_exists( $ag_stripe_admin_file ) ) {
        require_once $ag_stripe_admin_file;
    }
}

// ── 1c. Charger ag-calendly-admin.php (page de config Cal.com — filename legacy) ─
$ag_calendly_admin_file = get_stylesheet_directory() . '/ag-calendly-admin.php';
if ( file_exists( $ag_calendly_admin_file ) ) {
    require_once $ag_calendly_admin_file;
}

// ── 1c2. Auto-sync GitHub : Apparence > 🚀 Sync GitHub ─────────
$ag_github_sync_file = get_stylesheet_directory() . '/inc/ag-github-sync.php';
if ( file_exists( $ag_github_sync_file ) ) {
    require_once $ag_github_sync_file;
}

// ── 1c3. Données détaillées des 6 services (consommées par template-parts/service-detail.php)
$ag_services_data_file = get_stylesheet_directory() . '/inc/services-data.php';
if ( file_exists( $ag_services_data_file ) ) {
    require_once $ag_services_data_file;
}

// ── 1c4. SEO meta (titres + descriptions tunes par mot-cle, LocalBusiness bureaux, Offers templates)
$ag_seo_meta_file = get_stylesheet_directory() . '/inc/ag-seo-meta.php';
if ( file_exists( $ag_seo_meta_file ) ) {
    require_once $ag_seo_meta_file;
}

// ── 1c5. Programme Ambassadeurs (inscriptions, ventes, commissions 10%, paiements)
$ag_ambassadeurs_file = get_stylesheet_directory() . '/inc/ag-ambassadeurs.php';
if ( file_exists( $ag_ambassadeurs_file ) ) {
    require_once $ag_ambassadeurs_file;
}

// ── 1c6. PayPal automatique (webhooks : credit auto des commissions au paiement)
$ag_paypal_file = get_stylesheet_directory() . '/inc/ag-paypal.php';
if ( file_exists( $ag_paypal_file ) ) {
    require_once $ag_paypal_file;
}

// ── 1c7. Pop-up d'incitation "devenir ambassadeur" (visiteurs non-membres)
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    $kind = function_exists( 'ag_espace_member_kind' ) ? ag_espace_member_kind() : '';
    if ( in_array( $kind, array( 'ambassadeur', 'admin' ), true ) ) return; // pas pour les vendeurs/admin
    $exclude = array(
        'templates/page-connexion.php', 'templates/page-ambassadeurs.php',
        'templates/page-espace-ambassadeur.php', 'templates/page-espace-client.php',
        'templates/page-studio.php', 'templates/page-mot-de-passe.php',
        'templates/page-guide-ambassadeur.php',
    );
    foreach ( $exclude as $tpl ) { if ( is_page_template( $tpl ) ) return; }
    get_template_part( 'template-parts/ambassador-popup' );
}, 50 );

// ── 1c6. Espaces membres (clients & commerciaux) : comptes, connexion, dashboards
$ag_espaces_file = get_stylesheet_directory() . '/inc/ag-espaces.php';
if ( file_exists( $ag_espaces_file ) ) {
    require_once $ag_espaces_file;
}

// ── 1d. Shortcode [ag_promo_video] : insère la vidéo promo Alliance ─────
// Usage Gutenberg : ajouter un block "Shortcode" et taper [ag_promo_video]
// Attributs : title="..." lead="..." cta_label="..." cta_url="..."
//             poster="..." video_url="..." (tous optionnels)
add_shortcode( 'ag_promo_video', function ( $atts ) {
    $atts = shortcode_atts( array(
        'title'     => '',
        'lead'      => '',
        'cta_label' => '',
        'cta_url'   => '',
        'poster'    => '',
        'video_url' => '',
    ), $atts, 'ag_promo_video' );
    ob_start();
    // get_template_part avec args nécessite WP 5.5+. On expose aussi $args
    // en variable globale pour compatibilité.
    $GLOBALS['ag_promo_video_args'] = $atts;
    get_template_part( 'template-parts/promo-video', null, $atts );
    unset( $GLOBALS['ag_promo_video_args'] );
    return ob_get_clean();
} );

// ── 2. Enqueue styles & scripts ─────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    // ── GSAP + ScrollTrigger : charges UNE FOIS, dans le <head>, AVANT tous les
    //    autres scripts -> fini les chargements dynamiques concurrents (= bugs
    //    d'animation). Utilise les fichiers LOCAUX si presents
    //    (assets/js/lib/gsap.min.js + ScrollTrigger.min.js), sinon le CDN.
    //    NB : on n'utilise PAS "vendor" (ignore par .gitignore).
    $ag_vendor_dir = get_stylesheet_directory() . '/assets/js/lib/';
    $ag_vendor_uri = get_stylesheet_directory_uri() . '/assets/js/lib/';
    $ag_gsap_ok = file_exists( $ag_vendor_dir . 'gsap.min.js' )         && filesize( $ag_vendor_dir . 'gsap.min.js' )         > 1000;
    $ag_st_ok   = file_exists( $ag_vendor_dir . 'ScrollTrigger.min.js' ) && filesize( $ag_vendor_dir . 'ScrollTrigger.min.js' ) > 1000;
    wp_enqueue_script(
        'ag-gsap',
        $ag_gsap_ok ? $ag_vendor_uri . 'gsap.min.js' : 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js',
        array(),
        $ag_gsap_ok ? filemtime( $ag_vendor_dir . 'gsap.min.js' ) : '3.13.0',
        false
    );
    wp_enqueue_script(
        'ag-gsap-st',
        $ag_st_ok ? $ag_vendor_uri . 'ScrollTrigger.min.js' : 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js',
        array( 'ag-gsap' ),
        $ag_st_ok ? filemtime( $ag_vendor_dir . 'ScrollTrigger.min.js' ) : '3.13.0',
        false
    );

    // Style du thème (style.css obligatoire pour WordPress)
    wp_enqueue_style(
        'ag-theme-style',
        get_stylesheet_uri(),
        array(),
        '2.0.0'
    );

    wp_enqueue_style(
        'ag-google-fonts',
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'ag-main-css',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        array( 'ag-theme-style' ),
        file_exists( get_stylesheet_directory() . '/assets/css/main.css' )
            ? filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
            : '2.0.2'
    );

    // Enrichissements ciné : menu glassmorphism + hero pages internes
    $ag_cinema_css = get_stylesheet_directory() . '/assets/css/cinema-upgrades.css';
    if ( file_exists( $ag_cinema_css ) ) {
        wp_enqueue_style(
            'ag-cinema-upgrades',
            get_stylesheet_directory_uri() . '/assets/css/cinema-upgrades.css',
            array( 'ag-main-css' ),
            filemtime( $ag_cinema_css )
        );
    }
    // Pack JS ciné : Lenis + tilt + cursor + reveal + particles
    $ag_cinema_js = get_stylesheet_directory() . '/assets/js/cinema-fx.js';
    if ( file_exists( $ag_cinema_js ) ) {
        wp_enqueue_script(
            'ag-cinema-fx',
            get_stylesheet_directory_uri() . '/assets/js/cinema-fx.js',
            array(),
            filemtime( $ag_cinema_js ),
            true
        );
    }

    // ── Couche cinématographique expérimentale (module isolé) ──
    $ag_cine_css = get_stylesheet_directory() . '/assets/css/ag-cinema.css';
    if ( file_exists( $ag_cine_css ) ) {
        wp_enqueue_style(
            'ag-cinema',
            get_stylesheet_directory_uri() . '/assets/css/ag-cinema.css',
            array( 'ag-main-css' ),
            filemtime( $ag_cine_css )
        );
    }
    $ag_cine_js = get_stylesheet_directory() . '/assets/js/ag-cinema.js';
    if ( file_exists( $ag_cine_js ) ) {
        wp_enqueue_script(
            'ag-cinema',
            get_stylesheet_directory_uri() . '/assets/js/ag-cinema.js',
            array( 'ag-gsap', 'ag-gsap-st' ),
            filemtime( $ag_cine_js ),
            true
        );
    }

    // ── Couche immersive (intro, transitions, grain, sons) — module isolé ──
    $ag_imm_css = get_stylesheet_directory() . '/assets/css/ag-immersive.css';
    if ( file_exists( $ag_imm_css ) ) {
        wp_enqueue_style(
            'ag-immersive',
            get_stylesheet_directory_uri() . '/assets/css/ag-immersive.css',
            array( 'ag-main-css' ),
            filemtime( $ag_imm_css )
        );
    }
    $ag_imm_js = get_stylesheet_directory() . '/assets/js/ag-immersive.js';
    if ( file_exists( $ag_imm_js ) ) {
        wp_enqueue_script(
            'ag-immersive',
            get_stylesheet_directory_uri() . '/assets/js/ag-immersive.js',
            array(),
            filemtime( $ag_imm_js ),
            true
        );
    }
} );

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'ag-main-js',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        array(),
        file_exists( get_stylesheet_directory() . '/assets/js/main.js' )
            ? filemtime( get_stylesheet_directory() . '/assets/js/main.js' )
            : '2.0.1',
        true
    );
} );

// ── 3. Theme support ────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
    register_nav_menus( array( 'primary' => 'Menu principal' ) );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption' ) );
}, 5 );

// ── 3b. Force search engine indexation ─────────────────────────
// Force blog_public=1 ET supprime le header X-Robots-Tag noindex
add_action( 'init', function () {
    if ( get_option( 'blog_public' ) == '0' ) {
        update_option( 'blog_public', '1' );
    }
}, 1 );

// Supprimer le hook WordPress qui envoie X-Robots-Tag: noindex
add_action( 'template_redirect', function () {
    header_remove( 'X-Robots-Tag' );
}, 99 );

// Empêcher wp_robots d'ajouter noindex
add_filter( 'wp_robots', function ( $robots ) {
    unset( $robots['noindex'] );
    unset( $robots['nofollow'] );
    return $robots;
}, 9999 );

// ── 3c. Custom XML sitemap (indépendant de Yoast / WP natif) ───
// /sitemap.xml + /ag-sitemap.xml + /wp-sitemap.xml  →  urlset (toutes les URLs)
// /sitemap_index.xml                                 →  sitemapindex (vrai format index)
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    $allowed = array( 'sitemap.xml', 'sitemap_index.xml', 'wp-sitemap.xml', 'ag-sitemap.xml' );
    if ( ! in_array( $path, $allowed, true ) ) {
        return;
    }

    // Nettoie tout output deja envoye (au cas ou un plugin a deja imprime
    // quelque chose) — un sitemap doit etre pur XML, pas un mot avant.
    if ( ob_get_level() ) {
        while ( ob_get_level() ) ob_end_clean();
    }

    // Headers : XML strict, pas de cache navigateur, pas de noindex
    // (Google a parfois refuse les sitemaps avec X-Robots-Tag noindex)
    header( 'Content-Type: application/xml; charset=UTF-8' );
    header( 'X-Robots-Tag: noindex, follow' ); // OK pour le sitemap lui-meme
    header( 'Cache-Control: public, max-age=3600' );
    status_header( 200 ); // force 200 (pas 404 / WP par defaut)

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

    // ── Cas 1 : /sitemap_index.xml = vrai sitemapindex ──
    if ( $path === 'sitemap_index.xml' ) {
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        echo '<sitemap><loc>' . esc_url( home_url( '/sitemap.xml' ) ) . '</loc><lastmod>' . esc_html( wp_date( 'Y-m-d' ) ) . '</lastmod></sitemap>' . "\n";
        echo '</sitemapindex>';
        exit;
    }

    // ── Cas 2 : sitemap classique = urlset ──
    // Pages a NE PAS indexer
    $excluded = array( 'merci-rdv', 'merci-achat' );

    // Collecter toutes les pages publiees
    $pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'post_modified', 'sort_order' => 'DESC' ) );

    // Collecter tous les articles publies
    $posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ) );

    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Homepage
    echo '<url><loc>' . esc_url( home_url( '/' ) ) . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";

    // Pages
    if ( is_array( $pages ) ) {
        foreach ( $pages as $page ) {
            if ( in_array( $page->post_name, $excluded, true ) ) continue;
            $mod = get_the_modified_date( 'Y-m-d', $page );
            echo '<url><loc>' . esc_url( get_permalink( $page ) ) . '</loc><lastmod>' . $mod . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
        }
    }

    // Articles
    if ( is_array( $posts ) ) {
        foreach ( $posts as $post ) {
            $mod = get_the_modified_date( 'Y-m-d', $post );
            echo '<url><loc>' . esc_url( get_permalink( $post ) ) . '</loc><lastmod>' . $mod . '</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>' . "\n";
        }
    }

    echo '</urlset>';
    exit;
}, 0 ); // priority 0 = avant tout autre handler

// Désactiver le sitemap natif WP (on gère tout nous-mêmes)
add_filter( 'wp_sitemaps_enabled', '__return_false' );

// ── 3d. Redirection 301 /blog → /articles (conserve le SEO des anciennes URLs)
// L'ancienne page blog a ete renommee en "articles". On redirige proprement
// /blog et /blog/xxx pour ne pas perdre le jus des liens deja indexes par Google.
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    if ( $path === 'blog' || strpos( $path, 'blog/' ) === 0 ) {
        $new = home_url( '/' . preg_replace( '#^blog#', 'articles', $path ) . '/' );
        wp_redirect( $new, 301 );
        exit;
    }
}, 1 );

// ── 4. Auto-create categories ───────────────────────────────────
add_action( 'init', function () {
    if ( ! term_exists( 'Tech & IA', 'category' ) ) wp_insert_term( 'Tech & IA', 'category' );
    if ( ! term_exists( 'Conseils Digital', 'category' ) ) wp_insert_term( 'Conseils Digital', 'category' );
} );

// ── 5. Favicon ──────────────────────────────────────────────────
add_action( 'wp_head', function () {
    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
        if ( file_exists( $dir . 'logo.' . $ext ) ) {
            $url = $uri . 'logo.' . $ext;
            echo '<link rel="icon" href="' . esc_url( $url ) . '">' . "\n";
            echo '<link rel="apple-touch-icon" href="' . esc_url( $url ) . '">' . "\n";
            break;
        }
    }
} );

// ── 6. Register page templates ──────────────────────────────────
add_filter( 'theme_page_templates', function ( $templates ) {
    $templates['templates/page-accueil.php']         = 'Accueil';
    $templates['templates/page-services.php']        = 'Services';
    $templates['templates/page-realisations.php']    = 'Réalisations';
    $templates['templates/page-apropos.php']         = 'À propos';
    $templates['templates/page-contact.php']         = 'Contact';
    $templates['templates/page-service-web.php']     = 'Service — Création Web';
    $templates['templates/page-service-ia.php']      = 'Service — IA & Automatisation';
    $templates['templates/page-service-seo.php']     = 'Service — SEO';
    $templates['templates/page-service-ads.php']     = 'Service — Publicité';
    $templates['templates/page-service-brand.php']   = 'Service — Branding';
    $templates['templates/page-service-conseil.php'] = 'Service — Conseil';
    $templates['templates/page-fondateur.php']       = 'Notre Fondateur';
    $templates['templates/page-templates.php']       = 'Templates WordPress';
    $templates['templates/page-rdv.php']             = 'Prise de rendez-vous';
    $templates['templates/page-questions-flash.php'] = 'Questions Flash';
    $templates['templates/page-merci-rdv.php']       = 'Merci — Rendez-vous confirmé';
    $templates['templates/page-cookies.php']         = 'Cookies & Préférences';
    $templates['templates/page-mentions-legales.php']= 'Mentions légales & CGV';
    $templates['templates/page-confidentialite.php'] = 'Politique de confidentialité';
    $templates['templates/page-bureau-marrakech.php']= 'Bureau — Marrakech';
    $templates['templates/page-bureau-naples.php']   = 'Bureau — Naples';
    $templates['templates/page-bureau-nantes.php']   = 'Bureau — Nantes';
    $templates['templates/page-wordpress-barber.php']= 'Template WordPress — Barber Shop';
    $templates['templates/page-wordpress-association.php'] = 'Template WordPress — Association';
    $templates['templates/page-programme-racines.php'] = 'Programme Racines';
    $templates['templates/page-ambassadeurs.php']    = 'Programme Ambassadeurs';
    $templates['templates/page-contrat-ambassadeur.php'] = 'Contrat Ambassadeur';
    $templates['templates/page-sites-express.php']   = 'Sites Express';
    return $templates;
} );

// ── 7. Reading time helper ──────────────────────────────────────
if ( ! function_exists( 'ag_reading_time' ) ) {
    function ag_reading_time() {
        $content = get_post_field( 'post_content', get_the_ID() );
        return max( 1, ceil( str_word_count( strip_tags( $content ) ) / 250 ) );
    }
}

// ── 8. SEO meta description (fallback — utilise excerpt/contenu si pas d'override custom)
//    Si inc/ag-seo-meta.php a deja sorti une description tunee mot-cle, ce hook skippe
//    (via filter ag_skip_legacy_meta_description) pour eviter les doublons.
add_action( 'wp_head', function () {
    if ( apply_filters( 'ag_skip_legacy_meta_description', false ) ) return;
    $description = '';
    if ( is_singular() ) {
        if ( has_excerpt() ) {
            $description = wp_strip_all_tags( get_the_excerpt() );
        } else {
            $description = wp_trim_words( wp_strip_all_tags( get_the_content() ), 25 );
        }
    } elseif ( is_front_page() ) {
        $description = get_bloginfo( 'description' );
    } elseif ( is_archive() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            $description = $term->description;
        } elseif ( $term && ! empty( $term->name ) ) {
            $description = 'Articles dans la catégorie : ' . $term->name;
        }
    }
    if ( $description ) {
        echo '<meta name="description" content="' . esc_attr( substr( $description, 0, 160 ) ) . '">' . "\n";
    }
} );

// ── 8b. Noindex on thank-you & legal pages ─────────────────────
add_action( 'wp_head', function () {
    $noindex_slugs = array( 'merci-rdv', 'merci-achat' );
    $current_slug  = get_post_field( 'post_name' );
    if ( in_array( $current_slug, $noindex_slugs, true ) ) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}, 1 );

// ── 8b. Robots.txt amélioration ─────────────────────────────────
add_filter( 'robots_txt', function ( $output, $public ) {
    if ( $public ) {
        $home   = home_url( '/' );
        $output  = "User-agent: *\n";
        $output .= "Allow: /\n";
        $output .= "Allow: /sitemap.xml\n";
        $output .= "Allow: /sitemap_index.xml\n";
        $output .= "Allow: /ag-sitemap.xml\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Disallow: /wp-includes/\n";
        $output .= "Disallow: /?s=\n";
        $output .= "Disallow: /merci-rdv\n";
        $output .= "Disallow: /merci-achat\n";
        $output .= "\n";
        // Whitelist explicite Googlebot pour eviter blocage serveur (mod_security)
        $output .= "User-agent: Googlebot\n";
        $output .= "Allow: /\n";
        $output .= "Allow: /sitemap.xml\n";
        $output .= "\n";
        $output .= "User-agent: Bingbot\n";
        $output .= "Allow: /\n";
        $output .= "Allow: /sitemap.xml\n";
        $output .= "\n";
        $output .= "Sitemap: " . $home . "sitemap.xml\n";
    }
    return $output;
}, 10, 2 );

// ── 8b2. Ping Google & Bing on publish ──────────────────────────
add_action( 'publish_post', function () {
    $sitemap = home_url( '/sitemap.xml' );
    wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
    wp_remote_get( 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
} );
add_action( 'publish_page', function () {
    $sitemap = home_url( '/sitemap.xml' );
    wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
    wp_remote_get( 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
} );

// ── 8c. Canonical URL — WordPress le gère nativement, pas de doublon ──

// ── 8d. Open Graph + Twitter Card (enrichi)
//    Note : title + description peuvent etre overrides plus tot par inc/ag-seo-meta.php
//    via le hook priority 1. Dans ce cas on n'emet PAS de doublon ici (skip via filter).
add_action( 'wp_head', function () {
    $has_override = apply_filters( 'ag_skip_legacy_meta_description', false );

    $title   = wp_get_document_title();
    $desc    = get_bloginfo( 'description' );
    $url     = home_url( '/' );
    $img     = '';
    $og_type = 'website';

    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
        if ( file_exists( $dir . 'logo.' . $ext ) ) { $img = $uri . 'logo.' . $ext; break; }
    }

    if ( is_singular() ) {
        $title = get_the_title();
        $url   = get_permalink();
        if ( is_single() ) $og_type = 'article';
        if ( has_excerpt() ) $desc = wp_strip_all_tags( get_the_excerpt() );
        if ( has_post_thumbnail() ) $img = get_the_post_thumbnail_url( null, 'large' );
    }

    echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
    if ( ! $has_override ) {
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    }
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    if ( $img ) {
        echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    echo '<meta property="og:site_name" content="Alliance Groupe">' . "\n";
    echo '<meta property="og:locale" content="fr_FR">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    if ( ! $has_override ) {
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
    }
    if ( $img ) {
        echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
    }
}, 2 );

// ── 9. JSON-LD Structured Data (SEO) ────────────────────────────
add_action( 'wp_head', function () {
    $site_url  = home_url('/');
    $logo_url  = '';
    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    foreach ( array('jpg','jpeg','png','webp') as $ext ) {
        if ( file_exists( $dir . 'logo.' . $ext ) ) { $logo_url = $uri . 'logo.' . $ext; break; }
    }

    // Organization schema (all pages)
    $org = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Alliance Groupe',
        'url' => $site_url,
        'logo' => $logo_url,
        'description' => 'Agence Web & IA basée en France — Naples, Nantes, Marrakech',
        'telephone' => '+33623526074',
        'email' => 'contact@alliancegroupe-inc.com',
        'address' => array(
            array( '@type' => 'PostalAddress', 'addressLocality' => 'Naples', 'addressCountry' => 'IT' ),
            array( '@type' => 'PostalAddress', 'addressLocality' => 'Nantes', 'addressCountry' => 'FR' ),
            array( '@type' => 'PostalAddress', 'addressLocality' => 'Marrakech', 'addressCountry' => 'MA' ),
        ),
        'sameAs' => array( $site_url ),
        'founder' => array( '@type' => 'Person', 'name' => 'Fabrizio' ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

    // WebSite schema with search (homepage)
    if ( is_front_page() ) {
        $website = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Alliance Groupe',
            'url' => $site_url,
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => $site_url . '?s={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // BreadcrumbList (single posts + pages)
    if ( is_single() ) {
        $breadcrumb = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $site_url ),
                array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Articles', 'item' => $site_url . 'articles/' ),
                array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title() ),
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    } elseif ( is_page() ) {
        $breadcrumb = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $site_url ),
                array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() ),
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // Article schema enrichi (single posts)
    if ( is_single() ) {
        $cats = get_the_category();
        $article = array(
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'description'   => has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : wp_trim_words( wp_strip_all_tags( get_the_content() ), 25 ),
            'datePublished' => get_the_date( 'c' ),
            'dateModified'  => get_the_modified_date( 'c' ),
            'author'        => array( '@type' => 'Organization', 'name' => 'Alliance Groupe' ),
            'publisher'     => array(
                '@type' => 'Organization',
                'name'  => 'Alliance Groupe',
                'logo'  => array( '@type' => 'ImageObject', 'url' => $logo_url ),
            ),
            'mainEntityOfPage' => get_permalink(),
            'articleSection'   => $cats ? $cats[0]->name : 'Blog',
        );
        if ( has_post_thumbnail() ) {
            $article['image'] = array(
                '@type'  => 'ImageObject',
                'url'    => get_the_post_thumbnail_url( null, 'large' ),
                'width'  => 1200,
                'height' => 630,
            );
        }
        echo '<script type="application/ld+json">' . wp_json_encode( $article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // Service schema (pages de services)
    if ( is_page() ) {
        $service_map = array(
            'service-creation-web' => array( 'Création Web & Sites WordPress', 'Création de sites vitrines et e-commerce performants sur WordPress.' ),
            'service-seo'          => array( 'SEO & Référencement Naturel', 'Stratégie SEO complète pour dominer Google en local et national.' ),
            'service-ia'           => array( 'IA & Automatisation', 'Chatbots, workflows et automatisations IA pour gagner du temps.' ),
            'service-publicite'    => array( 'Publicité Digitale', 'Campagnes Google Ads et Meta Ads optimisées pour le ROI.' ),
            'service-branding'     => array( 'Branding & Identité Visuelle', 'Logos, chartes graphiques et identités visuelles premium.' ),
            'service-conseil'      => array( 'Conseil Stratégique Digital', 'Audit et accompagnement stratégique pour votre transformation digitale.' ),
        );
        $slug = get_post_field( 'post_name' );
        if ( isset( $service_map[ $slug ] ) ) {
            $svc = array(
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => $service_map[ $slug ][0],
                'description' => $service_map[ $slug ][1],
                'url'         => get_permalink(),
                'provider'    => array( '@type' => 'Organization', 'name' => 'Alliance Groupe', 'url' => $site_url ),
                'areaServed'  => array( '@type' => 'Country', 'name' => 'France' ),
            );
            echo '<script type="application/ld+json">' . wp_json_encode( $svc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }

    // LocalBusiness schema (front page — SEO local)
    if ( is_front_page() ) {
        $offices = array(
            array( 'Alliance Groupe — Nantes', 'Nantes', 'Pays de la Loire', 'FR', '+33623526074', 47.2173, -1.5534 ),
            array( 'Alliance Groupe — Naples', 'Naples', 'Campania', 'IT', '+33623526074', 40.8518, 14.2681 ),
            array( 'Alliance Groupe — Marrakech', 'Marrakech', 'Marrakech-Safi', 'MA', '+33623526074', 31.6295, -8.0088 ),
        );
        foreach ( $offices as $o ) {
            $lb = array(
                '@context'  => 'https://schema.org',
                '@type'     => 'LocalBusiness',
                'name'      => $o[0],
                'url'       => $site_url,
                'telephone' => $o[4],
                'email'     => 'contact@alliancegroupe-inc.com',
                'address'   => array(
                    '@type'            => 'PostalAddress',
                    'addressLocality'  => $o[1],
                    'addressRegion'    => $o[2],
                    'addressCountry'   => $o[3],
                ),
                'geo' => array( '@type' => 'GeoCoordinates', 'latitude' => $o[5], 'longitude' => $o[6] ),
            );
            echo '<script type="application/ld+json">' . wp_json_encode( $lb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }
}, 5 );

// ── 10. Save template download leads ────────────────────────────
add_action( 'wp_ajax_ag_save_lead', 'ag_save_lead' );
add_action( 'wp_ajax_nopriv_ag_save_lead', 'ag_save_lead' );

if ( ! function_exists( 'ag_save_lead' ) ) {
    function ag_save_lead() {
        check_ajax_referer( 'ag_lead_nonce', 'ag_lead_nonce' );

        $name     = sanitize_text_field( isset( $_POST['name'] ) ? $_POST['name'] : '' );
        $email    = sanitize_email( isset( $_POST['email'] ) ? $_POST['email'] : '' );
        $phone    = sanitize_text_field( isset( $_POST['phone'] ) ? $_POST['phone'] : '' );
        $template = sanitize_text_field( isset( $_POST['template'] ) ? $_POST['template'] : '' );

        if ( empty( $name ) || empty( $email ) ) {
            wp_send_json_error( 'Champs requis manquants.' );
        }

        $leads = get_option( 'ag_template_leads', array() );
        $leads[] = array(
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'template' => $template,
            'date'     => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_template_leads', $leads );

        wp_mail(
            'contact@alliancegroupe-inc.com',
            'Nouveau lead template : ' . $name,
            "Nom : $name\nEmail : $email\nTel : $phone\nTemplate : $template\nDate : " . current_time( 'd/m/Y H:i' )
        );

        wp_send_json_success();
    }
}

// ── 11. Questions Flash submission (post-paiement) ────────────────
add_action( 'admin_post_nopriv_ag_submit_question', 'ag_submit_question' );
add_action( 'admin_post_ag_submit_question', 'ag_submit_question' );

if ( ! function_exists( 'ag_submit_question' ) ) {
    function ag_submit_question() {
        if ( ! isset( $_POST['ag_question_nonce'] ) || ! wp_verify_nonce( $_POST['ag_question_nonce'], 'ag_question_nonce' ) ) {
            wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        }

        $name     = sanitize_text_field( isset( $_POST['name'] )     ? $_POST['name']     : '' );
        $email    = sanitize_email(      isset( $_POST['email'] )    ? $_POST['email']    : '' );
        $activity = sanitize_text_field( isset( $_POST['activity'] ) ? $_POST['activity'] : '' );
        $question = sanitize_textarea_field( isset( $_POST['question'] ) ? $_POST['question'] : '' );
        $context  = sanitize_textarea_field( isset( $_POST['context'] )  ? $_POST['context']  : '' );
        $pack     = sanitize_key(        isset( $_POST['pack'] )     ? $_POST['pack']     : '' );

        if ( empty( $name ) || empty( $email ) || empty( $question ) ) {
            wp_die( 'Merci de remplir nom, email et question.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        }
        if ( ! is_email( $email ) ) {
            wp_die( 'Email invalide.', 'Erreur', array( 'response' => 400, 'back_link' => true ) );
        }

        $pack_labels = array(
            'single' => '1 Question Flash (45€)',
            'pack'   => 'Pack 3 Questions (120€)',
            'sub'    => 'Abonnement Expert (199€/mois)',
        );
        $pack_label = isset( $pack_labels[ $pack ] ) ? $pack_labels[ $pack ] : 'Non précisé';

        // Save to DB
        $questions = get_option( 'ag_questions_submitted', array() );
        $questions[] = array(
            'name'     => $name,
            'email'    => $email,
            'activity' => $activity,
            'question' => $question,
            'context'  => $context,
            'pack'     => $pack,
            'date'     => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_questions_submitted', $questions );

        // Email to Fabrizio
        $admin_subject = '💬 Nouvelle Question Flash : ' . $name . ' (' . $pack_label . ')';
        $admin_body    = "Nouvelle Question Flash reçue\n"
                       . "================================\n\n"
                       . "Pack : $pack_label\n"
                       . "Nom : $name\n"
                       . "Email : $email\n"
                       . "Activité : $activity\n\n"
                       . "-- Question --\n$question\n\n"
                       . "-- Contexte --\n$context\n\n"
                       . "Reçue le : " . current_time( 'd/m/Y H:i' ) . "\n"
                       . "Délai de réponse attendu : 48h ouvrées\n";
        wp_mail( 'contact@alliancegroupe-inc.com', $admin_subject, $admin_body );

        // Confirmation to the buyer
        $client_subject = 'Votre Question Flash a bien été reçue — Alliance Groupe';
        $client_body    = "Bonjour $name,\n\n"
                       . "Merci ! Votre question a bien été reçue et nous travaillons déjà dessus.\n\n"
                       . "Pack : $pack_label\n"
                       . "Question posée :\n$question\n\n"
                       . "Vous recevrez une analyse écrite détaillée à cette adresse ($email) sous 48h ouvrées.\n\n"
                       . "Si vous avez besoin d'ajouter du contexte entre-temps, répondez simplement à cet email.\n\n"
                       . "À très vite,\nFabrizio — Alliance Groupe\n"
                       . "contact@alliancegroupe-inc.com\n"
                       . "06.23.52.60.74\n";
        $headers = array( 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' );
        wp_mail( $email, $client_subject, $client_body, $headers );

        // Redirect back with success
        wp_safe_redirect( add_query_arg( array( 'question_sent' => '1' ), home_url( '/questions-flash' ) ) );
        exit;
    }
}

// ── 11a2. Programme Racines — candidatures ──────────────────────
add_action( 'admin_post_nopriv_ag_submit_racines', 'ag_submit_racines' );
add_action( 'admin_post_ag_submit_racines', 'ag_submit_racines' );

if ( ! function_exists( 'ag_submit_racines' ) ) {
    function ag_submit_racines() {
        if ( ! isset( $_POST['ag_racines_nonce'] ) || ! wp_verify_nonce( $_POST['ag_racines_nonce'], 'ag_racines_nonce' ) ) {
            wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        }

        $name    = sanitize_text_field(     isset( $_POST['name'] )    ? $_POST['name']    : '' );
        $email   = sanitize_email(          isset( $_POST['email'] )   ? $_POST['email']   : '' );
        $phone   = sanitize_text_field(     isset( $_POST['phone'] )   ? $_POST['phone']   : '' );
        $city    = sanitize_text_field(     isset( $_POST['city'] )    ? $_POST['city']    : '' );
        $project = sanitize_textarea_field( isset( $_POST['project'] ) ? $_POST['project'] : '' );
        $why     = sanitize_textarea_field( isset( $_POST['why'] )     ? $_POST['why']     : '' );

        if ( empty( $name ) || empty( $email ) || empty( $project ) ) {
            wp_die( 'Merci de remplir au minimum nom, email et projet.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        }
        if ( ! is_email( $email ) ) {
            wp_die( 'Email invalide.', 'Erreur', array( 'response' => 400, 'back_link' => true ) );
        }

        // Save to DB
        $apps = get_option( 'ag_racines_applications', array() );
        if ( ! is_array( $apps ) ) $apps = array();
        $apps[] = array(
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'city'    => $city,
            'project' => $project,
            'why'     => $why,
            'date'    => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_racines_applications', $apps );

        // Notify admin
        $admin_body  = "Nouvelle candidature Programme Racines\n\n";
        $admin_body .= "Nom : $name\nEmail : $email\nTel : $phone\nVille/Quartier : $city\n\n";
        $admin_body .= "Projet :\n$project\n\nMotivation :\n$why\n\n";
        $admin_body .= 'Date : ' . current_time( 'd/m/Y H:i' );
        wp_mail( 'contact@alliancegroupe-inc.com', 'Candidature Programme Racines : ' . $name, $admin_body );

        // Confirmation candidat
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        $client_body  = "Bonjour $name,\n\n";
        $client_body .= "Merci d'avoir candidaté au Programme Racines d'Alliance Groupe. 🌱\n\n";
        $client_body .= "On lit chaque candidature avec attention. Si ton projet correspond au programme, ";
        $client_body .= "on te recontacte sous 7 jours pour un premier échange.\n\n";
        $client_body .= "En attendant, prépare bien ton idée — c'est ton énergie qui fera la différence.\n\n";
        $client_body .= "L'équipe Alliance Groupe\ncontact@alliancegroupe-inc.com";
        wp_mail( $email, 'Ta candidature Programme Racines est bien reçue 🌱', $client_body, $headers );

        wp_safe_redirect( add_query_arg( array( 'racines' => 'ok' ), home_url( '/programme-racines' ) ) . '#racines-candidature' );
        exit;
    }
}

// ── 11a3. Sites Express — brief projet (post-paiement) ──────────
add_action( 'admin_post_nopriv_ag_submit_brief', 'ag_submit_brief' );
add_action( 'admin_post_ag_submit_brief', 'ag_submit_brief' );

if ( ! function_exists( 'ag_submit_brief' ) ) {
    function ag_submit_brief() {
        if ( ! isset( $_POST['ag_brief_nonce'] ) || ! wp_verify_nonce( $_POST['ag_brief_nonce'], 'ag_brief_nonce' ) ) {
            wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
        }
        $pack     = sanitize_text_field(     $_POST['pack']     ?? '' );
        $business = sanitize_text_field(     $_POST['business'] ?? '' );
        $name     = sanitize_text_field(     $_POST['name']     ?? '' );
        $email    = sanitize_email(          $_POST['email']    ?? '' );
        $phone    = sanitize_text_field(     $_POST['phone']    ?? '' );
        $sector   = sanitize_text_field(     $_POST['sector']   ?? '' );
        $domain   = sanitize_text_field(     $_POST['domain']   ?? '' );
        $content  = sanitize_textarea_field( $_POST['content']  ?? '' );
        $inspi    = sanitize_textarea_field( $_POST['inspiration'] ?? '' );

        if ( empty( $name ) || ! is_email( $email ) || empty( $business ) ) {
            wp_die( 'Merci d\'indiquer ton nom, email et le nom de ton activité.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
        }

        $briefs = get_option( 'ag_express_briefs', array() );
        if ( ! is_array( $briefs ) ) $briefs = array();
        $briefs[] = compact( 'pack', 'business', 'name', 'email', 'phone', 'sector', 'domain', 'content', 'inspi' )
            + array( 'date' => current_time( 'd/m/Y H:i' ) );
        update_option( 'ag_express_briefs', $briefs );

        // Crée (ou retrouve) le compte client pour son espace réservé,
        // et déclenche l'attribution auto à l'ambassadeur (si lien de parrainage).
        do_action( 'ag_client_brief_submitted', $email, $name, $pack );

        $body  = "Nouveau brief Site Express\n\n";
        $body .= "Pack : $pack\nActivité : $business\nContact : $name <$email> $phone\n";
        $body .= "Secteur : $sector\nNom de domaine souhaité : $domain\n\n";
        $body .= "Contenu/textes :\n$content\n\nInspirations :\n$inspi\n\nDate : " . current_time( 'd/m/Y H:i' );
        wp_mail( 'contact@alliancegroupe-inc.com', 'Brief Site Express : ' . $business, $body );

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        $c  = "Bonjour $name,\n\nMerci ! On a bien reçu le brief de ton site ($business).\n";
        $c .= "On démarre la production et on t'envoie une première version rapidement, ";
        $c .= "avec une vidéo de présentation. Tout se fait par écrit, sans rendez-vous.\n\n";
        $c .= "----------------------------------------\n";
        $c .= "👉 GARDE TON SITE AU TOP CHAQUE MOIS\n";
        $c .= "Hébergement, sécurité, sauvegardes, retouches et référencement :\n";
        $c .= "nos forfaits maintenance à partir de 29€/mois.\n";
        $c .= home_url( '/sites-express#maintenance' ) . "\n";
        $c .= "----------------------------------------\n\n";
        $c .= "L'équipe Alliance Groupe\ncontact@alliancegroupe-inc.com";
        wp_mail( $email, 'On a reçu le brief de ton site 🚀', $c, $headers );

        wp_safe_redirect( add_query_arg( array( 'brief' => 'ok' ), home_url( '/sites-express' ) ) . '#brief' );
        exit;
    }
}

// ── 11a4. Admin : voir les briefs Sites Express ─────────────────
add_action( 'admin_menu', function () {
    add_menu_page( 'Sites Express', 'Sites Express', 'manage_options', 'ag-express-briefs', 'ag_render_express_briefs', 'dashicons-laptop', 29 );
} );
if ( ! function_exists( 'ag_render_express_briefs' ) ) {
    function ag_render_express_briefs() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $briefs = array_reverse( get_option( 'ag_express_briefs', array() ) );
        echo '<div class="wrap"><h1>Briefs Sites Express</h1>';
        if ( empty( $briefs ) ) { echo '<p>Aucun brief pour le moment.</p></div>'; return; }
        echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Pack</th><th>Activité</th><th>Contact</th><th>Secteur</th><th>Domaine</th><th>Contenu</th><th>Inspi</th></tr></thead><tbody>';
        foreach ( $briefs as $b ) {
            echo '<tr><td>' . esc_html( $b['date'] ?? '' ) . '</td><td>' . esc_html( $b['pack'] ?? '' ) . '</td>';
            echo '<td><strong>' . esc_html( $b['business'] ?? '' ) . '</strong></td>';
            echo '<td>' . esc_html( $b['name'] ?? '' ) . '<br><a href="mailto:' . esc_attr( $b['email'] ?? '' ) . '">' . esc_html( $b['email'] ?? '' ) . '</a><br>' . esc_html( $b['phone'] ?? '' ) . '</td>';
            echo '<td>' . esc_html( $b['sector'] ?? '' ) . '</td><td>' . esc_html( $b['domain'] ?? '' ) . '</td>';
            echo '<td style="max-width:280px;white-space:normal;">' . esc_html( $b['content'] ?? '' ) . '</td>';
            echo '<td style="max-width:200px;white-space:normal;">' . esc_html( $b['inspi'] ?? '' ) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}

// ── 11b. Admin page to view submitted questions ─────────────────
add_action( 'admin_menu', function () {
    add_menu_page(
        'Questions Flash',
        'Questions Flash',
        'manage_options',
        'ag-questions',
        'ag_render_questions_page',
        'dashicons-format-chat',
        27
    );
} );

if ( ! function_exists( 'ag_render_questions_page' ) ) {
    function ag_render_questions_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $questions = get_option( 'ag_questions_submitted', array() );
        echo '<div class="wrap"><h1>Questions Flash reçues</h1>';
        if ( empty( $questions ) ) {
            echo '<p>Aucune question pour le moment.</p></div>';
            return;
        }
        $questions = array_reverse( $questions );
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>Date</th><th>Nom</th><th>Email</th><th>Pack</th><th>Activité</th><th>Question</th><th>Contexte</th>';
        echo '</tr></thead><tbody>';
        foreach ( $questions as $q ) {
            echo '<tr>';
            echo '<td>' . esc_html( isset($q['date']) ? $q['date'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($q['name']) ? $q['name'] : '' ) . '</td>';
            echo '<td><a href="mailto:' . esc_attr( isset($q['email']) ? $q['email'] : '' ) . '">' . esc_html( isset($q['email']) ? $q['email'] : '' ) . '</a></td>';
            echo '<td>' . esc_html( isset($q['pack']) ? $q['pack'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($q['activity']) ? $q['activity'] : '' ) . '</td>';
            echo '<td style="max-width:300px;white-space:normal;">' . esc_html( isset($q['question']) ? $q['question'] : '' ) . '</td>';
            echo '<td style="max-width:200px;white-space:normal;">' . esc_html( isset($q['context']) ? $q['context'] : '' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}

