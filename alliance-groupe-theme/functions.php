<?php
/**
 * Alliance Groupe Theme — functions.php
 * Enqueue assets, register menus, page templates, override Elementor reset.css
 */

/* ── 1. Enqueue styles & scripts ─────────────────────────────────── */
add_action('wp_enqueue_scripts', function () {
    // Parent theme
    wp_enqueue_style('hello-elementor', get_template_directory_uri() . '/style.css');

    // Google Fonts
    wp_enqueue_style(
        'ag-google-fonts',
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap',
        [],
        null
    );

    // Main CSS
    wp_enqueue_style(
        'ag-main-css',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        ['hello-elementor'],
        '1.0.0'
    );

    // Main JS
    wp_enqueue_script(
        'ag-main-js',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        [],
        '1.0.0',
        true
    );
}, 20);

/* ── 2. Register navigation menus ────────────────────────────────── */
add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary' => __('Menu principal', 'alliance-groupe'),
    ]);
});

/* ── 3. Register custom page templates ───────────────────────────── */
add_filter('theme_page_templates', function ($templates) {
    $templates['templates/page-accueil.php']          = 'Accueil';
    $templates['templates/page-services.php']         = 'Services';
    $templates['templates/page-realisations.php']     = 'Réalisations';
    $templates['templates/page-apropos.php']          = 'À propos';
    $templates['templates/page-contact.php']          = 'Contact';
    $templates['templates/page-service-web.php']      = 'Service — Création Web';
    $templates['templates/page-service-ia.php']       = 'Service — IA & Automatisation';
    $templates['templates/page-service-seo.php']      = 'Service — SEO';
    $templates['templates/page-service-ads.php']      = 'Service — Publicité';
    $templates['templates/page-service-brand.php']    = 'Service — Branding';
    $templates['templates/page-service-conseil.php']  = 'Service — Conseil';
    return $templates;
});

/* ── 4. Auto-create blog categories ──────────────────────────────── */
add_action('init', function () {
    if (!term_exists('Tech & IA', 'category')) {
        wp_insert_term('Tech & IA', 'category');
    }
    if (!term_exists('Conseils Digital', 'category')) {
        wp_insert_term('Conseils Digital', 'category');
    }
});

/* ── 5. Override Elementor reset.css via HEREDOC in wp_footer ─────── */
add_action('wp_footer', function () {
    echo <<<AGSTYLE
<style id="ag-elementor-overrides">
/* ── Reset buttons ─────────────────────────────────────────── */
button,
[type=button],
[type=submit],
[type=reset]{
    background:transparent !important;
    border:none !important;
    cursor:pointer !important;
    padding:0 !important;
    font-family:inherit !important;
    font-size:inherit !important;
    color:inherit !important;
}

/* ── Logo nav ──────────────────────────────────────────────── */
.ag-nav__logo,
.ag-nav__logo:hover,
.ag-nav__logo:focus,
.ag-nav__logo:visited{
    text-decoration:none !important;
    color:#D4B45C !important;
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
}

/* ── Nav links ─────────────────────────────────────────────── */
.ag-nav__list li a,
.ag-nav__list li a:hover,
.ag-nav__list li a:focus,
.ag-nav__list li a:visited{
    text-decoration:none !important;
    color:#e8e6e0 !important;
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
    transition:color .3s !important;
}
.ag-nav__list li a:hover{
    color:#D4B45C !important;
}

/* ── Nav CTA ───────────────────────────────────────────────── */
.ag-nav__cta,
.ag-nav__cta:hover,
.ag-nav__cta:focus,
.ag-nav__cta:visited{
    display:inline-flex !important;
    align-items:center !important;
    gap:8px !important;
    padding:10px 24px !important;
    background:#D4B45C !important;
    color:#080808 !important;
    border-radius:8px !important;
    font-weight:700 !important;
    font-size:.95rem !important;
    text-decoration:none !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
    transition:background .3s,transform .3s !important;
}
.ag-nav__cta:hover{
    background:#c5a44e !important;
    transform:translateY(-2px) !important;
}

/* ── Burger mobile ─────────────────────────────────────────── */
.ag-nav__burger,
.ag-nav__burger:hover,
.ag-nav__burger:focus{
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
    cursor:pointer !important;
    padding:4px !important;
}

/* ── Back to top ───────────────────────────────────────────── */
button.ag-totop,
button.ag-totop:hover,
button.ag-totop:focus{
    position:fixed !important;
    bottom:40px !important;
    right:40px !important;
    width:48px !important;
    height:48px !important;
    border-radius:50% !important;
    background:#D4B45C !important;
    color:#080808 !important;
    font-size:1.4rem !important;
    font-weight:700 !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    border:none !important;
    outline:none !important;
    box-shadow:0 4px 20px rgba(212,180,92,.3) !important;
    cursor:pointer !important;
    z-index:9999 !important;
    opacity:0 !important;
    pointer-events:none !important;
    transition:opacity .4s,transform .3s !important;
}
button.ag-totop.visible{
    opacity:1 !important;
    pointer-events:auto !important;
}
button.ag-totop:hover{
    transform:translateY(-3px) !important;
}

/* ── FAQ accordion button ──────────────────────────────────── */
button.ag-faq-q,
button.ag-faq-q:hover,
button.ag-faq-q:focus{
    width:100% !important;
    text-align:left !important;
    padding:22px 28px !important;
    background:rgba(255,255,255,.03) !important;
    color:#fff !important;
    font-family:'Manrope',sans-serif !important;
    font-size:1.05rem !important;
    font-weight:700 !important;
    border:1px solid rgba(255,255,255,.08) !important;
    border-radius:12px !important;
    outline:none !important;
    box-shadow:none !important;
    cursor:pointer !important;
    display:flex !important;
    justify-content:space-between !important;
    align-items:center !important;
    transition:background .3s,border-color .3s !important;
}
button.ag-faq-q:hover{
    background:rgba(255,255,255,.06) !important;
    border-color:rgba(212,180,92,.3) !important;
}
button.ag-faq-q .ag-faq-icon{
    font-size:1.3rem !important;
    color:#D4B45C !important;
    transition:transform .3s !important;
}
.ag-faq-item.open button.ag-faq-q .ag-faq-icon{
    transform:rotate(45deg) !important;
}

/* ── Gold & outline buttons ────────────────────────────────── */
.ag-btn-gold,
.ag-btn-gold:hover,
.ag-btn-gold:focus,
.ag-btn-gold:visited{
    display:inline-flex !important;
    align-items:center !important;
    gap:10px !important;
    padding:16px 36px !important;
    background:#D4B45C !important;
    color:#080808 !important;
    border:none !important;
    border-radius:12px !important;
    font-family:'Manrope',sans-serif !important;
    font-size:1.05rem !important;
    font-weight:700 !important;
    text-decoration:none !important;
    outline:none !important;
    box-shadow:0 4px 25px rgba(212,180,92,.25) !important;
    cursor:pointer !important;
    transition:background .3s,transform .3s,box-shadow .3s !important;
}
.ag-btn-gold:hover{
    background:#c5a44e !important;
    transform:translateY(-2px) !important;
    box-shadow:0 8px 35px rgba(212,180,92,.35) !important;
}

.ag-btn-outline,
.ag-btn-outline:hover,
.ag-btn-outline:focus,
.ag-btn-outline:visited{
    display:inline-flex !important;
    align-items:center !important;
    gap:10px !important;
    padding:16px 36px !important;
    background:transparent !important;
    color:#D4B45C !important;
    border:2px solid #D4B45C !important;
    border-radius:12px !important;
    font-family:'Manrope',sans-serif !important;
    font-size:1.05rem !important;
    font-weight:700 !important;
    text-decoration:none !important;
    outline:none !important;
    box-shadow:none !important;
    cursor:pointer !important;
    transition:background .3s,color .3s,transform .3s !important;
}
.ag-btn-outline:hover{
    background:rgba(212,180,92,.1) !important;
    transform:translateY(-2px) !important;
}

/* ── Form inputs ───────────────────────────────────────────── */
.ag-form__group input,
.ag-form__group select,
.ag-form__group textarea{
    width:100% !important;
    padding:14px 18px !important;
    background:rgba(255,255,255,.04) !important;
    border:1px solid rgba(255,255,255,.1) !important;
    border-radius:10px !important;
    color:#fff !important;
    font-family:'Manrope',sans-serif !important;
    font-size:1rem !important;
    outline:none !important;
    box-shadow:none !important;
    transition:border-color .3s !important;
}
.ag-form__group input:focus,
.ag-form__group select:focus,
.ag-form__group textarea:focus{
    border-color:#D4B45C !important;
}
.ag-form__group textarea{
    min-height:140px !important;
    resize:vertical !important;
}

/* ── Link overrides ────────────────────────────────────────── */
.ag-scard__arrow,
.ag-scard__arrow:hover,
.ag-scard__arrow:focus,
.ag-scard__arrow:visited{
    text-decoration:none !important;
    color:#D4B45C !important;
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
}

.ag-rcard__link,
.ag-rcard__link:hover,
.ag-rcard__link:focus,
.ag-rcard__link:visited{
    text-decoration:none !important;
    color:#D4B45C !important;
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
    font-weight:700 !important;
    transition:color .3s !important;
}

.ag-footer__col a,
.ag-footer__col a:hover,
.ag-footer__col a:focus,
.ag-footer__col a:visited{
    text-decoration:none !important;
    color:#b0b0bc !important;
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
    transition:color .3s !important;
}
.ag-footer__col a:hover{
    color:#D4B45C !important;
}

.ag-contact-card a,
.ag-contact-card a:hover,
.ag-contact-card a:focus,
.ag-contact-card a:visited{
    text-decoration:none !important;
    color:#D4B45C !important;
    background:transparent !important;
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
}
</style>
AGSTYLE;
}, 999);
