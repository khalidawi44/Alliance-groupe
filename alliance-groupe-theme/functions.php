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
    return $templates;
} );

// ── 7. Reading time helper ──────────────────────────────────────
if ( ! function_exists( 'ag_reading_time' ) ) {
    function ag_reading_time() {
        $content = get_post_field( 'post_content', get_the_ID() );
        return max( 1, ceil( str_word_count( strip_tags( $content ) ) / 250 ) );
    }
}

// ── 8. SEO meta description ─────────────────────────────────────
add_action( 'wp_head', function () {
    if ( is_single() && has_excerpt() ) {
        echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( get_the_excerpt() ) ) . '">' . "\n";
    }
} );

// ── 8b. Robots.txt amélioration ─────────────────────────────────
add_filter( 'robots_txt', function ( $output, $public ) {
    if ( $public ) {
        $output  = "User-agent: *\n";
        $output .= "Allow: /\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Disallow: /wp-includes/\n";
        $output .= "Disallow: /?s=\n";
        $output .= "\n";
        $output .= "Sitemap: " . home_url( '/sitemap_index.xml' ) . "\n";
    }
    return $output;
}, 10, 2 );

// ── 8c. Force canonical URL on all pages ────────────────────────
add_action( 'wp_head', function () {
    if ( is_singular() ) {
        echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";
    } elseif ( is_front_page() ) {
        echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
    }
}, 1 );

// ── 8d. Open Graph basic tags ───────────────────────────────────
add_action( 'wp_head', function () {
    $title = wp_get_document_title();
    $desc  = get_bloginfo( 'description' );
    $url   = home_url( '/' );
    $img   = '';

    // Find logo for og:image
    $dir = get_stylesheet_directory() . '/assets/images/';
    $uri = get_stylesheet_directory_uri() . '/assets/images/';
    foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
        if ( file_exists( $dir . 'logo.' . $ext ) ) {
            $img = $uri . 'logo.' . $ext;
            break;
        }
    }

    if ( is_singular() ) {
        $title = get_the_title();
        $url   = get_permalink();
        if ( has_excerpt() ) $desc = wp_strip_all_tags( get_the_excerpt() );
        if ( has_post_thumbnail() ) $img = get_the_post_thumbnail_url( null, 'large' );
    }

    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    if ( $img ) echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
    echo '<meta property="og:site_name" content="Alliance Groupe">' . "\n";
    echo '<meta property="og:locale" content="fr_FR">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
    if ( $img ) echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
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

    // BreadcrumbList (single posts)
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

