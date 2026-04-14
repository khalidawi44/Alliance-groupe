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
        '2.0.1'
    );

    wp_enqueue_script(
        'ag-main-js',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        array(),
        '2.0.1',
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

// ── 10b. Integrated appointment booking ─────────────────────────
add_action( 'wp_ajax_ag_book_appointment', 'ag_book_appointment' );
add_action( 'wp_ajax_nopriv_ag_book_appointment', 'ag_book_appointment' );

if ( ! function_exists( 'ag_book_appointment' ) ) {
    function ag_book_appointment() {
        check_ajax_referer( 'ag_booking_nonce', 'ag_booking_nonce' );

        $name    = sanitize_text_field( isset( $_POST['name'] )    ? $_POST['name']    : '' );
        $email   = sanitize_email(      isset( $_POST['email'] )   ? $_POST['email']   : '' );
        $phone   = sanitize_text_field( isset( $_POST['phone'] )   ? $_POST['phone']   : '' );
        $service = sanitize_text_field( isset( $_POST['service'] ) ? $_POST['service'] : '' );
        $message = sanitize_textarea_field( isset( $_POST['message'] ) ? $_POST['message'] : '' );
        $date    = sanitize_text_field( isset( $_POST['date'] )    ? $_POST['date']    : '' );
        $time    = sanitize_text_field( isset( $_POST['time'] )    ? $_POST['time']    : '' );

        if ( empty( $name ) || empty( $email ) || empty( $phone ) ) {
            wp_send_json_error( 'Nom, email et téléphone sont requis.' );
        }
        if ( ! is_email( $email ) ) {
            wp_send_json_error( 'Email invalide.' );
        }
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
            wp_send_json_error( 'Date ou créneau invalide.' );
        }

        // Prevent same slot being booked twice
        $appointments = get_option( 'ag_appointments', array() );
        foreach ( $appointments as $a ) {
            if ( isset( $a['date'], $a['time'] ) && $a['date'] === $date && $a['time'] === $time ) {
                wp_send_json_error( 'Ce créneau vient d\'être réservé. Merci d\'en choisir un autre.' );
            }
        }

        $appointments[] = array(
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone,
            'service'   => $service,
            'message'   => $message,
            'date'      => $date,
            'time'      => $time,
            'booked_at' => current_time( 'd/m/Y H:i' ),
        );
        update_option( 'ag_appointments', $appointments );

        // Format date for display (FR)
        $timestamp = strtotime( $date );
        $date_fr   = $timestamp ? date_i18n( 'l j F Y', $timestamp ) : $date;

        // Email to the agency
        $admin_subject = 'Nouveau rendez-vous : ' . $name . ' — ' . $date_fr . ' ' . $time;
        $admin_body    = "Nouveau rendez-vous réservé sur le site\n\n"
                       . "Nom : $name\n"
                       . "Email : $email\n"
                       . "Téléphone : $phone\n"
                       . "Service : $service\n"
                       . "Date : $date_fr\n"
                       . "Heure : $time\n\n"
                       . "Message :\n$message\n";
        wp_mail( 'contact@alliancegroupe-inc.com', $admin_subject, $admin_body );

        // Confirmation to the client
        $client_subject = 'Confirmation de votre rendez-vous — Alliance Groupe';
        $client_body    = "Bonjour $name,\n\n"
                       . "Votre rendez-vous avec Alliance Groupe est confirmé :\n\n"
                       . "Date : $date_fr\n"
                       . "Heure : $time\n\n"
                       . "Nous vous contacterons par téléphone ou visio à l'horaire prévu. "
                       . "Si vous avez besoin de reporter, répondez simplement à cet email ou appelez-nous au 06.23.52.60.74.\n\n"
                       . "À très vite,\nL'équipe Alliance Groupe\n"
                       . "contact@alliancegroupe-inc.com\n";
        $headers = array( 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' );
        wp_mail( $email, $client_subject, $client_body, $headers );

        wp_send_json_success( array(
            'date' => $date_fr,
            'time' => $time,
        ) );
    }
}

// ── 10c. Admin page: view appointments ──────────────────────────
add_action( 'admin_menu', function () {
    add_menu_page(
        'Rendez-vous',
        'Rendez-vous',
        'manage_options',
        'ag-appointments',
        'ag_render_appointments_page',
        'dashicons-calendar-alt',
        26
    );
} );

if ( ! function_exists( 'ag_render_appointments_page' ) ) {
    function ag_render_appointments_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $appointments = get_option( 'ag_appointments', array() );
        echo '<div class="wrap"><h1>Rendez-vous réservés</h1>';
        if ( empty( $appointments ) ) {
            echo '<p>Aucun rendez-vous pour le moment.</p></div>';
            return;
        }
        // Newest first
        $appointments = array_reverse( $appointments );
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>Date</th><th>Heure</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Service</th><th>Message</th><th>Réservé le</th>';
        echo '</tr></thead><tbody>';
        foreach ( $appointments as $a ) {
            echo '<tr>';
            echo '<td>' . esc_html( isset($a['date']) ? $a['date'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($a['time']) ? $a['time'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($a['name']) ? $a['name'] : '' ) . '</td>';
            echo '<td><a href="mailto:' . esc_attr( isset($a['email']) ? $a['email'] : '' ) . '">' . esc_html( isset($a['email']) ? $a['email'] : '' ) . '</a></td>';
            echo '<td>' . esc_html( isset($a['phone']) ? $a['phone'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($a['service']) ? $a['service'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($a['message']) ? $a['message'] : '' ) . '</td>';
            echo '<td>' . esc_html( isset($a['booked_at']) ? $a['booked_at'] : '' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}
