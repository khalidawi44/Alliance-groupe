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

// ── 1b. Charger ag-stripe-admin.php (page de config Stripe) ─────
if ( is_admin() ) {
    $ag_stripe_admin_file = get_stylesheet_directory() . '/ag-stripe-admin.php';
    if ( file_exists( $ag_stripe_admin_file ) ) {
        require_once $ag_stripe_admin_file;
    }
}

// ── 1c. Charger ag-calendly-admin.php (page de config Calendly) ─
$ag_calendly_admin_file = get_stylesheet_directory() . '/ag-calendly-admin.php';
if ( file_exists( $ag_calendly_admin_file ) ) {
    require_once $ag_calendly_admin_file;
}

// ── 2. Enqueue styles & scripts ─────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
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

// ── 3c. Exclude thank-you pages from WordPress native sitemap ──
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $post_type ) {
    if ( 'page' === $post_type ) {
        $excluded_ids = array();
        $slugs = array( 'merci-rdv', 'merci-achat' );
        foreach ( $slugs as $slug ) {
            $page = get_page_by_path( $slug );
            if ( $page ) $excluded_ids[] = $page->ID;
        }
        if ( ! empty( $excluded_ids ) ) {
            $args['post__not_in'] = isset( $args['post__not_in'] ) ? array_merge( $args['post__not_in'], $excluded_ids ) : $excluded_ids;
        }
    }
    return $args;
}, 10, 2 );

// ── 3d. Redirect old Yoast sitemap URLs to WP native sitemap ───
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    if ( in_array( $path, array( 'sitemap.xml', 'sitemap_index.xml' ), true ) ) {
        wp_redirect( home_url( '/wp-sitemap.xml' ), 301 );
        exit;
    }
} );

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
    $templates['templates/page-bureau-marrakech.php']= 'Bureau — Marrakech';
    $templates['templates/page-bureau-naples.php']   = 'Bureau — Naples';
    $templates['templates/page-bureau-nantes.php']   = 'Bureau — Nantes';
    return $templates;
} );

// ── 7. Reading time helper ──────────────────────────────────────
if ( ! function_exists( 'ag_reading_time' ) ) {
    function ag_reading_time() {
        $content = get_post_field( 'post_content', get_the_ID() );
        return max( 1, ceil( str_word_count( strip_tags( $content ) ) / 250 ) );
    }
}

// ── 8. SEO meta description (toutes les pages) ────────────────
add_action( 'wp_head', function () {
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
        $output  = "User-agent: *\n";
        $output .= "Allow: /\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Disallow: /wp-includes/\n";
        $output .= "Disallow: /?s=\n";
        $output .= "Disallow: /merci-rdv\n";
        $output .= "Disallow: /merci-achat\n";
        $output .= "\n";
        $output .= "Sitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
    }
    return $output;
}, 10, 2 );

// ── 8b2. Ping Google & Bing on publish ──────────────────────────
add_action( 'publish_post', function () {
    $sitemap = home_url( '/wp-sitemap.xml' );
    wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
    wp_remote_get( 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
} );
add_action( 'publish_page', function () {
    $sitemap = home_url( '/wp-sitemap.xml' );
    wp_remote_get( 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
    wp_remote_get( 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap ), array( 'timeout' => 10, 'blocking' => false ) );
} );

// ── 8c. Canonical URL — WordPress le gère nativement, pas de doublon ──

// ── 8d. Open Graph + Twitter Card (enrichi) ────────────────────
add_action( 'wp_head', function () {
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
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    if ( $img ) {
        echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    echo '<meta property="og:site_name" content="Alliance Groupe">' . "\n";
    echo '<meta property="og:locale" content="fr_FR">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
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
                array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $site_url . 'blog/' ),
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

// ── 11. Questions Flash submission (post-Stripe) ────────────────
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

