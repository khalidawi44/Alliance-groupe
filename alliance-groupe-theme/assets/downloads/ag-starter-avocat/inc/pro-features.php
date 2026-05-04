<?php
/**
 * Premium Features — AG Starter Avocat
 *
 * 3 tiers:
 *   free     → base theme + grosse pub AG animée dans le footer
 *   premium  → fonts, animations, sticky header+tel, témoignages, couleurs, pub logo
 *   business → tout Premium + WooCommerce ready + copyright minimal
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Pro_Features {

    private $tier;
    private $theme_slug;

    public function __construct( $theme_slug ) {
        $this->theme_slug = $theme_slug;
        $this->tier = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_tier() : 'free';

        // Always register tier body class + inline guard (also runs in Free,
        // so .ag-tier-free reaches the DOM and the toggle is hidden even if
        // header.php / style.css are served from a stale cache).
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
        add_action( 'wp_head', array( $this, 'print_tier_guard_style' ), 99 );
        add_action( 'wp_footer', array( $this, 'print_tier_guard_script' ), 99 );

        // Footer branding — always active (different per tier)
        add_action( 'wp_footer', array( $this, 'render_footer_branding' ), 5 );

        if ( 'free' === $this->tier ) return;

        // Pro+ features
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pro_assets' ) );
        add_action( 'customize_register', array( $this, 'register_pro_customizer' ), 20 );
        $this->__construct_business();
    }

    public function print_tier_guard_style() {
        if ( $this->is_at_least( 'premium' ) ) return;
        echo "<style id=\"ag-tier-guard\">body:not(.ag-tier-premium):not(.ag-tier-business) .ag-theme-toggle{display:none!important;}</style>\n";
    }

    public function print_tier_guard_script() {
        if ( $this->is_at_least( 'premium' ) ) return;
        echo "<script id=\"ag-tier-guard-js\">document.querySelectorAll('.ag-theme-toggle').forEach(function(el){el.parentNode&&el.parentNode.removeChild(el);});</script>\n";
    }

    public function get_tier() { return $this->tier; }

    public function is_at_least( $min ) {
        $order = array( 'free' => 0, 'premium' => 1, 'business' => 2 );
        return ( $order[ $this->tier ] ?? 0 ) >= ( $order[ $min ] ?? 0 );
    }

    // ─── PRO: Assets ──────────────────────────────────────────

    public function enqueue_pro_assets() {
        wp_enqueue_style( 'ag-google-fonts',
            'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap',
            array(), null );
        wp_enqueue_script( 'ag-premium-scripts',
            get_template_directory_uri() . '/inc/pro-scripts.js',
            array(), '4.0.' . time(), true );
        $skin = $this->get_skin();
        wp_localize_script( 'ag-premium-scripts', 'agSkin', array(
            'accent' => $skin['accent'],
            'light'  => ! empty( $skin['light'] ),
        ) );
        wp_add_inline_style( 'ag-starter-avocat-style', $this->get_pro_css() );
    }

    private function get_skin() {
        $skins = array(
            'navy-or' => array(
                'name'   => 'Classique — Mode Nuit',
                'accent' => '#D4B45C', 'accent_hover' => '#c5a44e', 'accent_rgb' => '212,180,92',
                'bg'     => '#080808', 'bg2' => '#0a0a0f', 'bg_card' => '#14141c',
                'tier'   => 'premium',
            ),
            'bordeaux' => array(
                'name'   => 'Bordeaux — Mode Jour',
                'accent' => '#7B2D3B', 'accent_hover' => '#6A2433', 'accent_rgb' => '123,45,59',
                'bg'     => '#F5F0EB', 'bg2' => '#EDE6DF', 'bg_card' => '#FFFFFF',
                'text'   => '#2A1A1E', 'text_soft' => '#3A2A2E', 'text_muted' => '#8A7478',
                'light'  => true,
                'tier'   => 'premium',
            ),
            'bronze' => array(
                'name'   => 'Bronze — Prestige',
                'accent' => '#B08D57', 'accent_hover' => '#9A7A4A', 'accent_rgb' => '176,141,87',
                'bg'     => '#09080a', 'bg2' => '#0d0c0e', 'bg_card' => '#191718',
                'tier'   => 'business',
            ),
            'ardoise' => array(
                'name'   => 'Ardoise — Sobriete',
                'accent' => '#7A9BAE', 'accent_hover' => '#688899', 'accent_rgb' => '122,155,174',
                'bg'     => '#080a0c', 'bg2' => '#0a0d10', 'bg_card' => '#141a1e',
                'tier'   => 'business',
            ),
        );
        $selected = get_theme_mod( 'ag_pro_skin', 'navy-or' );
        if ( ! isset( $skins[ $selected ] ) ) $selected = 'navy-or';
        $skin = $skins[ $selected ];
        if ( ! $this->is_at_least( $skin['tier'] ) ) $skin = $skins['navy-or'];
        return $skin;
    }

    public static function get_available_skins( $tier ) {
        $all = array(
            'navy-or'  => array( 'name' => 'Classique — Mode Nuit', 'tier' => 'premium', 'preview' => '#D4B45C' ),
            'bordeaux' => array( 'name' => 'Bordeaux — Mode Jour', 'tier' => 'premium', 'preview' => '#7B2D3B' ),
            'bronze'   => array( 'name' => 'Bronze — Prestige', 'tier' => 'business', 'preview' => '#B08D57' ),
            'ardoise'  => array( 'name' => 'Ardoise — Sobriete', 'tier' => 'business', 'preview' => '#7A9BAE' ),
        );
        $order = array( 'free' => 0, 'premium' => 1, 'business' => 2 );
        $level = $order[ $tier ] ?? 0;
        $available = array();
        foreach ( $all as $k => $v ) {
            if ( ( $order[ $v['tier'] ] ?? 0 ) <= $level ) {
                $available[ $k ] = $v['name'];
            }
        }
        return $available;
    }

    private function get_pro_css() {
        $s = $this->get_skin();
        $gold = $s['accent'];
        $gold_hover = $s['accent_hover'];
        $gold_rgb = $s['accent_rgb'];
        $bg = $s['bg'];
        $bg2 = $s['bg2'];
        $bg_card = $s['bg_card'];
        $is_light = ! empty( $s['light'] );
        $text_main = $is_light ? ( $s['text'] ?? '#2A1A1E' ) : '#e8e6e0';
        $text_soft = $is_light ? ( $s['text_soft'] ?? '#5A4549' ) : '#b0b0bc';
        $text_muted = $is_light ? ( $s['text_muted'] ?? '#8A7478' ) : '#8a8a95';
        $card_bg = $is_light ? '#FFFFFF' : 'rgba(255,255,255,.025)';
        $card_border = $is_light ? 'rgba(0,0,0,.08)' : 'rgba(255,255,255,.06)';
        $card_shadow = $is_light ? '0 4px 20px rgba(0,0,0,.06)' : 'none';
        $heading_color = $is_light ? ( $s['text'] ?? '#2A1A1E' ) : '#fff';
        $ease = 'cubic-bezier(.23,1,.32,1)';
        $css = '
/* ═══ PREMIUM DESIGN — Alliance Groupe Style ═══ */
body{font-family:"Manrope",system-ui,sans-serif !important;background:' . $bg . ' !important;color:' . $text_main . ' !important;line-height:1.7 !important;}
h1,h2,h3,h4{font-family:"Manrope",sans-serif !important;font-weight:800 !important;line-height:1.2 !important;color:' . $heading_color . ' !important;}
em,.ag-gold{font-family:"Playfair Display",serif !important;font-style:italic !important;color:' . $gold . ' !important;}

/* ── Hero ── */
.ag-hero{
    min-height:90vh !important;
    display:flex !important;align-items:center !important;justify-content:center !important;
    text-align:center !important;position:relative !important;overflow:hidden !important;
    background:linear-gradient(180deg,rgba(8,8,8,.2) 0%,rgba(8,8,8,.92) 100%),
               url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;
    background-attachment:fixed !important;
}
.ag-hero::before{
    content:"";position:absolute;inset:0;
    background:radial-gradient(ellipse 900px 600px at 85% 10%,rgba(' . $gold_rgb . ',.10),transparent 60%),
               radial-gradient(ellipse 700px 500px at 10% 95%,rgba(' . $gold_rgb . ',.06),transparent 60%);
    pointer-events:none;
}
.ag-hero .ag-container{position:relative;z-index:1;max-width:800px;}
.ag-hero__title{
    font-size:clamp(2.2rem,5.5vw,3.8rem) !important;
    line-height:1.2 !important;font-weight:800 !important;
    margin-bottom:16px !important;
}
.ag-hero__title span{color:' . $gold . ';font-family:"Playfair Display",serif !important;font-style:italic;display:block;}
.ag-hero__subtitle{
    font-size:1.05rem !important;color:#ddd !important;
    max-width:600px !important;margin:0 auto 32px !important;line-height:1.7 !important;
}
@keyframes ag-line-stack{0%{opacity:0;transform:translateY(-30px)}60%{transform:translateY(4px)}100%{opacity:1;transform:translateY(0)}}
.ag-hero__title{opacity:0;animation:ag-line-stack .6s ' . $ease . ' .3s forwards;}
.ag-hero__subtitle{opacity:0;animation:ag-line-stack .6s ' . $ease . ' .6s forwards;}
.ag-hero .ag-btn{opacity:0;animation:ag-line-stack .6s ' . $ease . ' .9s forwards;}

/* ── Buttons ── */
.ag-btn{
    display:inline-flex !important;align-items:center;gap:10px;
    background:' . $gold . ' !important;color:' . $bg . ' !important;
    padding:16px 36px !important;border-radius:12px !important;
    font-weight:700 !important;font-size:1.05rem !important;
    text-decoration:none !important;border:none !important;cursor:pointer !important;
    box-shadow:0 4px 25px rgba(' . $gold_rgb . ',.25) !important;
    transition:background .3s,transform .3s ' . $ease . ',box-shadow .3s !important;
}
.ag-btn:hover{background:' . $gold_hover . ' !important;transform:translateY(-2px) !important;box-shadow:0 8px 35px rgba(' . $gold_rgb . ',.35) !important;}

/* ── Sections ── */
.ag-section{padding:100px 0 !important;position:relative;overflow:hidden !important;}
.ag-section-title{
    font-size:clamp(1.5rem,3vw,2.2rem) !important;
    text-align:center !important;margin-bottom:12px !important;
    color:#fff !important;position:relative !important;display:inline-block !important;
    width:100% !important;
}
.ag-section-lead{
    text-align:center !important;color:#ddd !important;
    font-size:.95rem !important;max-width:650px !important;
    margin:0 auto 48px !important;line-height:1.7 !important;
}
/* Section backgrounds Alliance-style */
.ag-section:nth-of-type(odd){
    background:radial-gradient(ellipse 900px 600px at 85% 10%,rgba(' . $gold_rgb . ',.10),transparent 60%),
               radial-gradient(ellipse 700px 500px at 10% 95%,rgba(' . $gold_rgb . ',.06),transparent 60%),' . $bg . ' !important;
    border-top:1px solid rgba(' . $gold_rgb . ',.12) !important;
}
.ag-section:nth-of-type(even){
    background:linear-gradient(135deg,#15151c 0%,#1c1c26 45%,#16161e 100%) !important;
    border-top:1px solid rgba(' . $gold_rgb . ',.14) !important;
}
/* Gold divider */
.ag-section::before{
    content:"" !important;position:absolute !important;top:0 !important;left:50% !important;
    transform:translateX(-50%) !important;width:min(280px,40%) !important;height:1px !important;
    background:linear-gradient(90deg,transparent,rgba(' . $gold_rgb . ',.55),transparent) !important;
}

/* ── Domaines cards ── */
.ag-domaines__grid{
    display:grid !important;
    grid-template-columns:repeat(3,1fr) !important;
    gap:28px !important;
}
.ag-domaine-card{
    background:' . $card_bg . ' !important;
    border:1px solid ' . $card_border . ' !important;
    border-radius:16px !important;
    padding:36px 30px !important;
    text-decoration:none !important;color:inherit !important;
    display:flex !important;flex-direction:column !important;
    transition:border-color .4s ' . $ease . ',transform .4s ' . $ease . ',box-shadow .4s !important;
}
.ag-domaine-card:hover{
    border-color:rgba(' . $gold_rgb . ',.25) !important;
    transform:translateY(-4px) !important;
    box-shadow:0 12px 40px rgba(0,0,0,.3) !important;
}
.ag-domaine-card__icon{font-size:2.4rem !important;margin-bottom:14px !important;}
.ag-domaine-card__title{font-size:1.15rem !important;margin-bottom:8px !important;color:#fff !important;}
.ag-domaine-card__excerpt{color:rgba(255,255,255,.6) !important;font-size:.92rem !important;line-height:1.6 !important;}
.ag-domaine-card__more{
    display:inline-block !important;
    margin-top:14px !important;
    color:' . $gold . ' !important;
    font-weight:700 !important;
    font-size:.9rem !important;
    text-decoration:none !important;
}

/* ── Maître section ── */
.ag-maitre{
    background:linear-gradient(135deg,rgba(' . $gold_rgb . ',.04) 0%,rgba(10,14,26,.8) 100%) !important;
    border-top:1px solid rgba(' . $gold_rgb . ',.1) !important;
    border-bottom:1px solid rgba(' . $gold_rgb . ',.1) !important;
}
.ag-maitre__inner{
    display:grid !important;
    grid-template-columns:300px 1fr !important;
    gap:48px !important;
    align-items:center !important;
}
.ag-maitre__photo img{
    border-radius:16px !important;
    border:2px solid rgba(' . $gold_rgb . ',.2) !important;
    box-shadow:0 20px 50px rgba(0,0,0,.4) !important;
}
.ag-maitre__tag{
    display:inline-block !important;
    padding:4px 14px !important;
    background:rgba(' . $gold_rgb . ',.1) !important;
    color:' . $gold . ' !important;
    border:1px solid rgba(' . $gold_rgb . ',.25) !important;
    border-radius:100px !important;
    font-size:.78rem !important;
    font-weight:600 !important;
    text-transform:uppercase !important;
    letter-spacing:1px !important;
    margin-bottom:12px !important;
}
.ag-maitre__name{font-size:2rem !important;font-style:italic !important;margin-bottom:8px !important;}
.ag-maitre__meta{color:rgba(255,255,255,.5) !important;font-size:.88rem !important;margin-bottom:20px !important;}
.ag-maitre__bio{color:#fff !important;line-height:1.8 !important;font-size:1rem !important;}
.ag-maitre__specialties{color:' . $gold . ' !important;font-size:.92rem !important;margin-top:16px !important;}

/* ── Honoraires cards ── */
.ag-honoraires__grid{
    display:grid !important;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr)) !important;
    gap:24px !important;
    max-width:900px !important;
    margin:0 auto !important;
}
.ag-honoraires__card{
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(' . $gold_rgb . ',.15) !important;
    border-radius:18px !important;
    padding:36px 28px !important;
    text-align:center !important;
    transition:transform .3s,border-color .3s !important;
}
.ag-honoraires__card:hover{
    transform:translateY(-4px) !important;
    border-color:rgba(' . $gold_rgb . ',.4) !important;
}
.ag-honoraires__price{
    font-family:"Playfair Display",serif !important;
    font-size:2.2rem !important;
    font-weight:800 !important;
    color:' . $gold . ' !important;
    margin-bottom:8px !important;
}
.ag-honoraires__label{font-size:1.1rem !important;margin-bottom:12px !important;color:#fff !important;}
.ag-honoraires__desc{color:rgba(255,255,255,.6) !important;font-size:.92rem !important;line-height:1.6 !important;}
.ag-honoraires__note{
    text-align:center !important;
    color:rgba(255,255,255,.4) !important;
    font-size:.85rem !important;
    max-width:700px !important;
    margin:32px auto 0 !important;
    padding-top:24px !important;
    border-top:1px solid rgba(255,255,255,.06) !important;
}

/* ── Cabinet section avec background ── */
.ag-cabinet{
    background:linear-gradient(180deg,rgba(10,14,26,.95) 0%,rgba(10,14,26,.8) 100%),
               url("https://images.unsplash.com/photo-1497366216548-37526070297c?w=1920&q=80") center/cover !important;
}
.ag-cabinet__cards{
    display:grid !important;
    grid-template-columns:repeat(3,1fr) !important;
    gap:28px !important;
}
.ag-cabinet__grid{
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    gap:40px !important;
}
.ag-cabinet__block{
    background:rgba(255,255,255,.04) !important;
    border:1px solid rgba(255,255,255,.06) !important;
    border-radius:14px !important;
    padding:24px !important;
    margin-bottom:20px !important;
}
.ag-cabinet__block h3{color:' . $gold . ' !important;font-size:1rem !important;margin-bottom:10px !important;}
.ag-cabinet__block p{color:rgba(255,255,255,.7) !important;line-height:1.6 !important;}
.ag-cabinet__block a{color:' . $gold . ' !important;text-decoration:none !important;}
.ag-cabinet__emergency{
    background:rgba(220,53,69,.1) !important;
    border:1px solid rgba(220,53,69,.3) !important;
    border-radius:8px !important;
    padding:12px !important;
    margin-top:12px !important;
}

/* ── RDV form luxe ── */
.ag-rdv{
    background:linear-gradient(135deg,rgba(' . $gold_rgb . ',.04) 0%,rgba(10,14,26,.9) 100%) !important;
}
.ag-rdv__form{
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(' . $gold_rgb . ',.2) !important;
    border-radius:20px !important;
    padding:40px 36px !important;
}
.ag-rdv__row{display:grid !important;grid-template-columns:1fr 1fr !important;gap:20px !important;margin-bottom:20px !important;}
.ag-rdv__field label{display:block !important;color:rgba(255,255,255,.6) !important;font-size:.85rem !important;font-weight:600 !important;margin-bottom:6px !important;}
.ag-rdv__field input,.ag-rdv__field select,.ag-rdv__field textarea{
    width:100% !important;
    padding:14px 18px !important;
    background:rgba(10,14,26,.8) !important;
    border:1px solid rgba(' . $gold_rgb . ',.2) !important;
    border-radius:10px !important;
    color:#fff !important;
    font-size:1rem !important;
    outline:none !important;
    transition:border-color .3s,box-shadow .3s !important;
    font-family:inherit !important;
}
.ag-rdv__field select{appearance:none !important;background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\'%3E%3Cpath d=\'M1 1l5 5 5-5\' stroke=\'%23c9a96e\' stroke-width=\'2\' fill=\'none\'/%3E%3C/svg%3E") !important;background-repeat:no-repeat !important;background-position:right 16px center !important;padding-right:40px !important;}
.ag-rdv__field select option{background:#0a0e1a !important;color:#fff !important;}
.ag-rdv__field input::placeholder,.ag-rdv__field textarea::placeholder{color:rgba(255,255,255,.3) !important;}
.ag-rdv__field input:focus,.ag-rdv__field select:focus,.ag-rdv__field textarea:focus{
    border-color:' . $gold . ' !important;
    outline:2px solid ' . $gold . ' !important;
    outline-offset:2px !important;
}

/* ── Footer ── */
.ag-site-footer{
    background:#060609 !important;
    border-top:1px solid rgba(' . $gold_rgb . ',.1) !important;
    padding:60px 0 0 !important;
}
.ag-footer-grid{
    display:grid !important;
    grid-template-columns:repeat(3,1fr) !important;
    gap:40px !important;
    margin-bottom:40px !important;
}
.ag-footer-col h3{color:' . $gold . ' !important;font-size:1rem !important;margin-bottom:14px !important;}
.ag-footer-col p,.ag-footer-col li{color:rgba(255,255,255,.5) !important;font-size:.92rem !important;line-height:1.7 !important;}
.ag-footer-col a{color:' . $gold . ' !important;text-decoration:none !important;}
a.ag-footer-rdv,a.ag-footer-rdv:visited,a.ag-footer-rdv:hover{color:' . $bg . ' !important;}
.ag-footer-bottom{
    padding:24px 0 !important;
    border-top:1px solid rgba(255,255,255,.06) !important;
    text-align:center !important;
    color:rgba(255,255,255,.3) !important;
    font-size:.82rem !important;
}

/* ── Section dividers full-width ── */
.ag-section::before{
    content:"" !important;display:block !important;
    width:100vw !important;height:1px !important;
    margin-left:calc(-50vw + 50%) !important;
    background:linear-gradient(90deg,transparent 0%,rgba(' . $gold_rgb . ',.25) 50%,transparent 100%) !important;
    margin-bottom:80px !important;
}
.ag-section:first-of-type::before{display:none !important;}
.ag-domaines{
    background:linear-gradient(180deg,rgba(' . $gold_rgb . ',.03) 0%,rgba(10,14,26,1) 100%) !important;
}
.ag-honoraires{
    background:linear-gradient(180deg,rgba(10,14,26,1) 0%,rgba(' . $gold_rgb . ',.05) 50%,rgba(10,14,26,1) 100%) !important;
}
.ag-rdv{
    background:linear-gradient(180deg,rgba(10,14,26,.95) 0%,rgba(10,14,26,.8) 100%),
               url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover !important;
}

/* ── Footer RDV link ── */
.ag-footer-rdv{
    display:inline-block !important;margin-top:14px !important;
    background:' . $gold . ' !important;color:#080808 !important;text-shadow:none !important;
    padding:10px 22px !important;border-radius:8px !important;
    font-weight:700 !important;font-size:.85rem !important;
    text-decoration:none !important;transition:transform .3s,box-shadow .3s !important;
}
.ag-footer-rdv:hover{transform:translateY(-2px) !important;box-shadow:0 6px 20px rgba(' . $gold_rgb . ',.3) !important;}

/* ── Header luxe — transparent top → solid on scroll ── */
.ag-site-header{
    background:transparent !important;
    backdrop-filter:none !important;
    border-bottom:1px solid transparent !important;
    padding:18px 0 !important;
    transition:background .4s,backdrop-filter .4s,border-color .4s,box-shadow .4s,padding .3s !important;
}
.ag-site-header__inner{display:flex !important;align-items:center !important;justify-content:space-between !important;}
.ag-site-brand a{
    font-family:"Playfair Display",serif !important;
    font-size:1.3rem !important;
    font-weight:700 !important;
    font-style:italic !important;
    color:' . $gold . ' !important;
    text-decoration:none !important;
}
.ag-primary-menu{display:flex !important;gap:24px !important;list-style:none !important;}
.ag-primary-menu a{
    color:rgba(255,255,255,.7) !important;
    text-decoration:none !important;
    font-size:.9rem !important;
    font-weight:600 !important;
    transition:color .3s !important;
}
.ag-primary-menu a:hover{color:' . $gold . ' !important;}

/* ── Mobile menu toggle ── */
.ag-menu-toggle{
    display:none;background:none;border:none;cursor:pointer;
    padding:8px;flex-direction:column;gap:5px;z-index:1001;
}
.ag-menu-toggle span{
    display:block;width:24px;height:2px;background:#fff;
    border-radius:2px;transition:transform .3s,opacity .3s;
}
.ag-menu-toggle.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px);}
.ag-menu-toggle.active span:nth-child(2){opacity:0;}
.ag-menu-toggle.active span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px);}

/* ── Responsive ── */
@media(max-width:768px){
    .ag-hero{min-height:70vh !important;}
    .ag-hero__title{font-size:clamp(1.8rem,8vw,2.8rem) !important;}
    .ag-maitre__inner{grid-template-columns:1fr !important;text-align:center;}
    .ag-cabinet__cards{grid-template-columns:1fr !important;}
    .ag-cabinet__grid{grid-template-columns:1fr !important;}
    .ag-rdv__row{grid-template-columns:1fr !important;}
    .ag-footer-grid{grid-template-columns:1fr !important;}
    .ag-page-article{padding:32px 20px !important;}
    .ag-menu-toggle{display:flex !important;}
    .ag-primary-menu{
        display:none !important;
        position:fixed !important;top:0 !important;left:0 !important;
        width:100% !important;height:100vh !important;
        background:rgba(10,14,26,.98) !important;
        flex-direction:column !important;
        align-items:center !important;justify-content:center !important;
        gap:32px !important;z-index:1000 !important;
    }
    .ag-primary-menu.open{display:flex !important;}
    .ag-primary-menu.open a{font-size:1.3rem !important;color:#fff !important;}
    .ag-header__phone{display:none !important;}
}
';
        // Sticky header
        if ( get_theme_mod( 'ag_pro_sticky_header', true ) ) {
            $css .= '
.ag-site-header{position:fixed;top:0;left:0;right:0;z-index:1000;}
.ag-site-header.scrolled{
    background:' . ( $is_light ? 'rgba(245,240,235,.97)' : 'rgba(8,8,8,.97)' ) . ' !important;
    backdrop-filter:blur(16px) !important;
    border-bottom-color:rgba(' . $gold_rgb . ',.1) !important;
    box-shadow:0 4px 30px rgba(0,0,0,.4) !important;
    padding:10px 0 !important;
}
.ag-header__phone{display:inline-flex;align-items:center;gap:6px;background:' . $gold . ';color:#0a0e1a;padding:8px 16px;border-radius:6px;font-weight:700;font-size:.85rem;text-decoration:none;margin-left:12px;transition:transform .2s;}
.ag-header__phone:hover{transform:translateY(-2px);}
';
        }
        // Animations
        if ( get_theme_mod( 'ag_pro_animations', true ) ) {
            $css .= '
.ag-fade-in{opacity:0;transform:translateY(30px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1);}
.ag-fade-in.visible{opacity:1;transform:translateY(0);}
.ag-slide-left{opacity:0;transform:translateX(-60px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1);}
.ag-slide-left.visible{opacity:1;transform:translateX(0);}
.ag-slide-right{opacity:0;transform:translateX(60px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1);}
.ag-slide-right.visible{opacity:1;transform:translateX(0);}
.ag-scale-in{opacity:0;transform:scale(.85);transition:opacity .7s ease,transform .7s ease;}
.ag-scale-in.visible{opacity:1;transform:scale(1);}
.ag-hero__title{opacity:0;transform:translateY(40px);animation:agHeroIn 1s cubic-bezier(.16,1,.3,1) .2s forwards;}
.ag-hero__subtitle{opacity:0;transform:translateY(30px);animation:agHeroIn .9s cubic-bezier(.16,1,.3,1) .5s forwards;}
.ag-hero .ag-btn{opacity:0;transform:translateY(20px);animation:agHeroIn .8s cubic-bezier(.16,1,.3,1) .8s forwards;}
@keyframes agHeroIn{to{opacity:1;transform:translateY(0);}}
@media(prefers-reduced-motion:reduce){.ag-fade-in,.ag-slide-left,.ag-slide-right,.ag-scale-in,.ag-hero__title,.ag-hero__subtitle,.ag-hero .ag-btn{opacity:1 !important;transform:none !important;animation:none !important;transition:none !important;}}
';
        }
        // Testimonials
        $css .= '
.ag-testimonials{padding:80px 24px;background:rgba(' . $gold_rgb . ',.03);}
.ag-testimonials__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:1200px;margin:0 auto;}
.ag-testimonial-card{background:rgba(255,255,255,.04);border:1px solid rgba(' . $gold_rgb . ',.15);border-radius:16px;padding:28px;}
.ag-testimonial-card__stars{color:' . $gold . ';font-size:1.1rem;margin-bottom:12px;letter-spacing:2px;}
.ag-testimonial-card__text{color:rgba(255,255,255,.8);font-size:.95rem;line-height:1.7;font-style:italic;margin-bottom:16px;}
.ag-testimonial-card__author{font-weight:700;color:#fff;font-size:.9rem;}
';

        // Page template styling
        $css .= '
html{scroll-behavior:smooth;}
/* Page hero — default (generic pages) */
.ag-page-hero{
    padding:160px 0 80px !important;
    text-align:center !important;
    background:linear-gradient(180deg,rgba(10,14,26,.3) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center/cover !important;
    border-bottom:1px solid rgba(' . $gold_rgb . ',.15) !important;
}
/* Page hero — images par page */
.page-expertise .ag-page-hero,body.page-template-page-expertise .ag-page-hero{
    background:linear-gradient(180deg,rgba(10,14,26,.25) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1920&q=80") center/cover !important;
}
.page-honoraires .ag-page-hero,body.page-template-page-honoraires .ag-page-hero{
    background:linear-gradient(180deg,rgba(10,14,26,.3) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1920&q=80") center/cover !important;
}
.page-cabinet .ag-page-hero,body.page-template-page-cabinet .ag-page-hero{
    background:linear-gradient(180deg,rgba(10,14,26,.25) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1497366216548-37526070297c?w=1920&q=80") center/cover !important;
}
.page-rendez-vous .ag-page-hero,body.page-template-page-rendez-vous .ag-page-hero{
    background:linear-gradient(180deg,rgba(10,14,26,.3) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover !important;
}
.single-ag_domaine .ag-page-hero{
    background:linear-gradient(180deg,rgba(10,14,26,.3) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1920&q=80") center/cover !important;
}
.blog .ag-page-hero,.archive .ag-page-hero,.search .ag-page-hero{
    background:linear-gradient(180deg,rgba(10,14,26,.3) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=1920&q=80") center/cover !important;
}
.ag-page-hero__title{
    font-family:"Playfair Display",serif !important;
    font-size:clamp(2rem,5vw,3.2rem) !important;
    color:#fff !important;
    font-weight:700 !important;
    font-style:italic !important;
}
.ag-page-hero__title::after{
    content:"" !important;display:block !important;
    width:60px !important;height:3px !important;
    background:' . $gold . ' !important;
    margin:20px auto 0 !important;
    border-radius:2px !important;
}
.ag-page-hero__lead{
    color:rgba(255,255,255,.6) !important;
    font-size:1.1rem !important;
    max-width:650px !important;
    margin:20px auto 0 !important;
    line-height:1.7 !important;
    text-align:center !important;
}
.ag-page-content-wrap{
    max-width:800px !important;
    padding:60px 24px 80px !important;
}
.ag-page-article{
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(' . $gold_rgb . ',.12) !important;
    border-radius:18px !important;
    padding:48px 40px !important;
}
.ag-page-article .ag-entry-content{
    color:rgba(255,255,255,.8) !important;
    font-size:1.05rem !important;
    line-height:1.8 !important;
}
.ag-page-article .ag-entry-content h2,
.ag-page-article .ag-entry-content h3{
    color:#fff !important;
    font-family:"Playfair Display",serif !important;
    margin:32px 0 16px !important;
}
.ag-page-article .ag-entry-content a{color:' . $gold . ' !important;}
.ag-page-article .ag-entry-content ul,.ag-page-article .ag-entry-content ol{
    padding-left:24px !important;margin:16px 0 !important;
}
.ag-page-article .ag-entry-content li{margin-bottom:8px !important;}
.ag-page-article .ag-entry-thumb{margin-bottom:32px !important;}
.ag-page-article .ag-entry-thumb img{border-radius:14px !important;border:1px solid rgba(' . $gold_rgb . ',.15) !important;}
.ag-post-meta{color:rgba(255,255,255,.4) !important;font-size:.88rem !important;margin-top:12px !important;}
.ag-404-text{color:rgba(255,255,255,.7) !important;font-size:1.15rem !important;line-height:1.7 !important;}
.ag-entry-footer{margin-top:32px !important;padding-top:24px !important;border-top:1px solid rgba(255,255,255,.06) !important;}
.ag-entry-footer p{color:rgba(255,255,255,.5) !important;font-size:.88rem !important;}
.ag-entry-footer a{color:' . $gold . ' !important;text-decoration:none !important;}

/* ── Domaine single ── */
.ag-domaine-hero-icon{font-size:3.2rem !important;margin-bottom:8px !important;}
.ag-domaine-hero-tag{
    color:' . $gold . ' !important;
    font-size:.82rem !important;
    text-transform:uppercase !important;
    letter-spacing:1.5px !important;
    font-weight:600 !important;
    margin-bottom:8px !important;
}
.ag-domaine-examples{
    margin-top:36px !important;
    padding:28px !important;
    background:rgba(' . $gold_rgb . ',.04) !important;
    border:1px solid rgba(' . $gold_rgb . ',.15) !important;
    border-left:3px solid ' . $gold . ' !important;
    border-radius:14px !important;
}
.ag-domaine-examples__title{
    color:' . $gold . ' !important;
    font-size:1rem !important;
    text-transform:uppercase !important;
    letter-spacing:.5px !important;
    margin-bottom:16px !important;
    font-family:"Playfair Display",serif !important;
}
.ag-domaine-examples__list{list-style:none !important;padding:0 !important;margin:0 !important;}
.ag-domaine-examples__list li{
    padding:10px 0 10px 20px !important;
    color:rgba(255,255,255,.75) !important;
    position:relative !important;
    border-bottom:1px solid rgba(255,255,255,.04) !important;
}
.ag-domaine-examples__list li::before{
    content:"›" !important;position:absolute !important;left:0 !important;
    color:' . $gold . ' !important;font-weight:700 !important;font-size:1.1rem !important;
}
.ag-domaine-cta{
    text-align:center !important;margin-top:36px !important;
    padding-top:28px !important;border-top:1px solid rgba(255,255,255,.06) !important;
}
.ag-domaine-cta p{color:rgba(255,255,255,.6) !important;margin-bottom:16px !important;}
.ag-domaine-back{
    margin-top:16px !important;padding-top:14px !important;
    border-top:1px solid rgba(255,255,255,.08) !important;text-align:center !important;
}
.ag-domaine-back a{color:' . $gold . ' !important;text-decoration:none !important;font-weight:700 !important;}
.ag-domaine-back a:hover{text-decoration:underline !important;}

/* ── RDV extras ── */
.ag-rdv__status{padding:16px 20px !important;border-radius:10px !important;margin-bottom:24px !important;font-weight:600 !important;}
.ag-rdv__status--success{background:rgba(40,167,69,.1) !important;border:1px solid rgba(40,167,69,.3) !important;color:#28a745 !important;}
.ag-rdv__status--error{background:rgba(220,53,69,.1) !important;border:1px solid rgba(220,53,69,.3) !important;color:#dc3545 !important;}
.ag-rdv__rgpd{margin:20px 0 !important;}
.ag-rdv__rgpd label{display:flex !important;gap:10px !important;align-items:flex-start !important;color:#fff !important;font-size:.85rem !important;line-height:1.5 !important;cursor:pointer !important;}
.ag-rdv__rgpd input[type="checkbox"]{margin-top:3px !important;accent-color:' . $gold . ' !important;}
.ag-rdv__submit{width:100% !important;justify-content:center !important;margin-top:8px !important;font-size:1.05rem !important;padding:18px !important;}
.ag-rdv__legal{text-align:center !important;color:rgba(255,255,255,.3) !important;font-size:.8rem !important;margin-top:16px !important;}
.ag-rdv__honeypot{position:absolute !important;left:-9999px !important;height:0 !important;overflow:hidden !important;}
.ag-rdv-cta{padding:60px 0 !important;}

/* ── Back to top ── */
.ag-totop{
    position:fixed !important;bottom:40px !important;right:40px !important;
    width:48px !important;height:48px !important;border-radius:50% !important;
    background:' . $gold . ' !important;color:' . $bg . ' !important;
    font-size:1.4rem !important;font-weight:700 !important;
    display:flex !important;align-items:center !important;justify-content:center !important;
    text-decoration:none !important;
    box-shadow:0 4px 20px rgba(' . $gold_rgb . ',.3) !important;
    opacity:0 !important;pointer-events:none !important;
    transition:opacity .4s,transform .3s ' . $ease . ' !important;
    z-index:999 !important;
}
.ag-totop.visible{opacity:1 !important;pointer-events:auto !important;}
.ag-totop:hover{transform:translateY(-3px) !important;}

/* ── Tag pill (like Alliance) ── */
/* ── Theme toggle button ── */
.ag-header-actions{display:flex !important;align-items:center !important;gap:8px !important;}
.ag-theme-toggle{
    background:none !important;border:1px solid rgba(' . $gold_rgb . ',.3) !important;
    border-radius:50% !important;width:36px !important;height:36px !important;
    cursor:pointer !important;display:flex !important;align-items:center !important;justify-content:center !important;
    font-size:1rem !important;transition:border-color .3s,transform .3s !important;padding:0 !important;
}
.ag-theme-toggle:hover{border-color:' . $gold . ' !important;transform:scale(1.1) !important;}
body.ag-light .ag-hero{background:linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-light .ag-hero::before{background:none !important;}
body.ag-light .ag-page-hero{background:linear-gradient(180deg,rgba(245,240,235,.2) 0%,rgba(245,240,235,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
body.ag-light .ag-rdv__field input,body.ag-light .ag-rdv__field select,body.ag-light .ag-rdv__field textarea{background:#fff !important;border-color:rgba(0,0,0,.12) !important;color:#2A1A1E !important;}
body.ag-light .ag-footer-bottom{color:#8A7478 !important;}
body.ag-light .ag-pagination .page-numbers{background:rgba(0,0,0,.03) !important;border-color:rgba(0,0,0,.08) !important;color:#5A4549 !important;}
body.ag-light .ag-primary-menu.open{background:rgba(245,240,235,.98) !important;}
body.ag-light .ag-theme-toggle{border-color:rgba(123,45,59,.3) !important;}

.ag-tag{
    display:inline-block !important;padding:6px 18px !important;
    background:rgba(' . $gold_rgb . ',.1) !important;color:' . $gold . ' !important;
    border:1px solid rgba(' . $gold_rgb . ',.25) !important;border-radius:100px !important;
    font-size:.85rem !important;font-weight:600 !important;
    letter-spacing:.5px !important;text-transform:uppercase !important;
}


/* ── Business: Counters ── */
.ag-counters{padding:60px 0 !important;background:rgba(212,180,92,.03) !important;border-top:1px solid rgba(212,180,92,.1) !important;}
.ag-counters__grid{display:grid !important;grid-template-columns:repeat(4,1fr) !important;gap:24px !important;text-align:center !important;}
.ag-counter__number{display:block !important;font-family:"Playfair Display",serif !important;font-size:2.5rem !important;font-weight:700 !important;color:#D4B45C !important;margin-bottom:4px !important;}
.ag-counter__label{color:rgba(255,255,255,.6) !important;font-size:.88rem !important;}
body.ag-light .ag-counter__label{color:#5A4549 !important;}
body.ag-light .ag-counters{background:rgba(123,45,59,.03) !important;border-color:rgba(123,45,59,.1) !important;}
body.ag-light .ag-counter__number{color:#7B2D3B !important;}
@media(max-width:600px){.ag-counters__grid{grid-template-columns:repeat(2,1fr) !important;}}
/* ── Business: Trust bar ── */
.ag-trust-bar{padding:16px 0 !important;background:rgba(212,180,92,.06) !important;border-bottom:1px solid rgba(212,180,92,.1) !important;}
.ag-trust-bar__inner{display:flex !important;justify-content:center !important;gap:24px !important;flex-wrap:wrap !important;}
.ag-trust-badge{color:rgba(255,255,255,.6) !important;font-size:.82rem !important;font-weight:600 !important;}
body.ag-light .ag-trust-bar{background:rgba(123,45,59,.04) !important;border-color:rgba(123,45,59,.08) !important;}
body.ag-light .ag-trust-badge{color:#5A4549 !important;}
/* ── Page Cabinet full layout ── */
.ag-cabinet-full{display:grid !important;grid-template-columns:1.2fr 1fr !important;gap:32px !important;align-items:start !important;}
.ag-cabinet-full__map{border-radius:16px !important;overflow:hidden !important;box-shadow:0 20px 60px rgba(0,0,0,.4) !important;border:1px solid rgba(' . $gold_rgb . ',.15) !important;}
.ag-cabinet-full__map iframe{display:block !important;}
.ag-cabinet-full__cards{display:flex !important;flex-direction:column !important;gap:16px !important;}
.ag-cabinet__block-icon{font-size:1.5rem !important;margin-bottom:8px !important;}
.ag-cabinet-full__btn{width:100% !important;justify-content:center !important;margin-top:8px !important;}
@media(max-width:768px){.ag-cabinet-full{grid-template-columns:1fr !important;}}
.ag-cabinet-map-section{background:linear-gradient(180deg,rgba(10,14,26,1) 0%,rgba(' . $gold_rgb . ',.04) 100%) !important;}

/* ── RDV contact section : 2 colonnes + bg fixe ── */
.ag-rdv-contact{
    background:linear-gradient(180deg,rgba(8,8,8,.85) 0%,rgba(8,8,8,.9) 100%),
               url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover no-repeat !important;
    background-attachment:fixed !important;
    padding:80px 0 !important;
}
.ag-rdv-contact__grid{
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    gap:32px !important;
    max-width:800px !important;
    margin:0 auto !important;
}
.ag-rdv-contact .ag-cabinet__block{
    text-align:center !important;
    padding:40px 30px !important;
}
.ag-rdv-contact .ag-cabinet__block-icon{font-size:2rem !important;margin-bottom:12px !important;}
@media(max-width:600px){.ag-rdv-contact__grid{grid-template-columns:1fr !important;}}

/* ── Fixed backgrounds ── */
.ag-hero,.ag-page-hero,.ag-cabinet,.ag-rdv{background-attachment:fixed !important;}
@media(max-width:768px){.ag-hero,.ag-page-hero,.ag-cabinet,.ag-rdv{background-attachment:scroll !important;}}

/* ── Archive / Blog / Category : grille 3 colonnes ── */
.ag-archive-wrap{padding:60px 24px 80px !important;max-width:1200px !important;}
.ag-posts-grid{
    display:grid !important;
    grid-template-columns:repeat(3,1fr) !important;
    gap:28px !important;
}
.ag-post-card{
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(' . $gold_rgb . ',.12) !important;
    border-radius:16px !important;
    overflow:hidden !important;
    transition:transform .35s,border-color .35s,box-shadow .35s !important;
    display:flex !important;
    flex-direction:column !important;
}
.ag-post-card:hover{
    transform:translateY(-5px) !important;
    border-color:rgba(' . $gold_rgb . ',.35) !important;
    box-shadow:0 16px 40px rgba(0,0,0,.3) !important;
}
.ag-post-card__thumb{display:block !important;overflow:hidden !important;aspect-ratio:16/10 !important;}
.ag-post-card__thumb img{width:100% !important;height:100% !important;object-fit:cover !important;transition:transform .4s !important;}
.ag-post-card:hover .ag-post-card__thumb img{transform:scale(1.05) !important;}
.ag-post-card__body{padding:24px !important;flex:1 !important;display:flex !important;flex-direction:column !important;}
.ag-post-card__date{color:' . $gold . ' !important;font-size:.78rem !important;font-weight:600 !important;text-transform:uppercase !important;letter-spacing:.5px !important;margin-bottom:8px !important;}
.ag-post-card__title{font-size:1.1rem !important;line-height:1.35 !important;margin-bottom:10px !important;}
.ag-post-card__title a{color:#fff !important;text-decoration:none !important;}
.ag-post-card__title a:hover{color:' . $gold . ' !important;}
.ag-post-card__excerpt{color:rgba(255,255,255,.55) !important;font-size:.88rem !important;line-height:1.6 !important;flex:1 !important;}
.ag-post-card__more{
    display:inline-block !important;margin-top:14px !important;
    color:' . $gold . ' !important;font-weight:700 !important;font-size:.85rem !important;
    text-decoration:none !important;
}
.ag-post-card__more:hover{text-decoration:underline !important;}
.ag-no-results{color:rgba(255,255,255,.5) !important;text-align:center !important;font-size:1.1rem !important;padding:60px 0 !important;}
.ag-pagination{text-align:center !important;padding:40px 0 0 !important;}
.ag-pagination .page-numbers{
    display:inline-flex !important;align-items:center !important;justify-content:center !important;
    width:40px !important;height:40px !important;margin:0 4px !important;
    border-radius:8px !important;color:rgba(255,255,255,.6) !important;
    text-decoration:none !important;font-weight:600 !important;
    background:rgba(255,255,255,.04) !important;border:1px solid rgba(255,255,255,.08) !important;
    transition:background .2s,color .2s !important;
}
.ag-pagination .page-numbers.current,.ag-pagination .page-numbers:hover{
    background:' . $gold . ' !important;color:#0a0e1a !important;border-color:' . $gold . ' !important;
}
@media(max-width:900px){
    .ag-posts-grid{grid-template-columns:repeat(2,1fr) !important;}
}
@media(max-width:600px){
    .ag-posts-grid{grid-template-columns:1fr !important;}
}
';

        // Visitor toggle: mode jour (always last = highest priority)
        $css .= '
/* ── Visitor dark/light toggle — mode jour ── */
body.ag-light{background:#F5F0EB !important;color:#2A1A1E !important;}
body.ag-light h1,body.ag-light h2,body.ag-light h3,body.ag-light h4,body.ag-light .ag-section-title,body.ag-light .ag-page-hero__title,body.ag-light .ag-hero__title,body.ag-light .ag-honoraires__label,body.ag-light .ag-domaine-card__title,body.ag-light .ag-post-card__title a,body.ag-light .ag-page-article .ag-entry-content h2,body.ag-light .ag-page-article .ag-entry-content h3{color:#2A1A1E !important;}
body.ag-light .ag-hero__subtitle,body.ag-light .ag-section-lead,body.ag-light .ag-domaine-card__excerpt,body.ag-light .ag-honoraires__desc,body.ag-light .ag-cabinet__block p,body.ag-light .ag-rdv__field label,body.ag-light .ag-footer-col p,body.ag-light .ag-footer-col li,body.ag-light .ag-post-card__excerpt,body.ag-light .ag-testimonial-card__text,body.ag-light .ag-page-article .ag-entry-content,body.ag-light .ag-page-article .ag-entry-content p,body.ag-light .ag-page-article .ag-entry-content li,body.ag-light .ag-honoraires__note,body.ag-light .ag-rdv__legal,body.ag-light .ag-domaine-card__examples li{color:#2A1A1E !important;}
body.ag-light .ag-maitre__bio{color:#2A1A1E !important;}
body.ag-light .ag-maitre__meta{color:#5A4549 !important;}
body.ag-light .ag-maitre__name{color:#7B2D3B !important;}
body.ag-light .ag-maitre__tag{background:rgba(212,180,92,.1) !important;border-color:rgba(212,180,92,.25) !important;color:#D4B45C !important;}
body.ag-light .ag-maitre__specialties strong{color:#D4B45C !important;}
body.ag-light .ag-rdv__rgpd label,body.ag-light .ag-rdv__rgpd span{color:#2A1A1E !important;}
body.ag-light .ag-rdv__rgpd{background:rgba(0,0,0,.03) !important;border-color:rgba(0,0,0,.1) !important;}
body.ag-light .ag-rdv__form{background:#fff !important;border-color:rgba(0,0,0,.08) !important;}
body.ag-light .ag-rdv__submit{color:#fff !important;}
body.ag-light .ag-rdv__legal{color:#5A4549 !important;}
body.ag-light .ag-rdv__field label{color:#2A1A1E !important;}
body.ag-light .ag-page-hero__title{color:#7B2D3B !important;}
body.ag-light .ag-page-hero__lead{color:#5A4549 !important;}
body.ag-light .ag-domaine-examples{background:#F5F0EB !important;}
body.ag-light .ag-domaine-examples__title{color:#2A1A1E !important;}
body.ag-light .ag-domaine-examples__list li{color:#2A1A1E !important;}
body.ag-light .ag-domaine-examples__list li::before{color:#7B2D3B !important;}
body.ag-light .ag-domaine-cta p{color:#5A4549 !important;}
body.ag-light .ag-domaine-back a{color:#7B2D3B !important;}
body.ag-light .ag-page-article .ag-entry-content{color:#2A1A1E !important;}
body.ag-light .ag-page-article .ag-entry-content p{color:#3A2A2E !important;}
body.ag-light .ag-page-article .ag-entry-thumb img{border-color:rgba(0,0,0,.08) !important;}
body.ag-light .ag-maitre__photo img{box-shadow:0 10px 30px rgba(0,0,0,.1) !important;border-color:rgba(123,45,59,.2) !important;}
body.ag-light .ag-hero__title span,body.ag-light .ag-domaine-card__more,body.ag-light .ag-post-card__more,body.ag-light .ag-post-card__date,body.ag-light .ag-honoraires__price,body.ag-light .ag-domaine-examples__title,body.ag-light .ag-domaine-examples__list li::before,body.ag-light .ag-domaine-back a,body.ag-light .ag-entry-content a,body.ag-light .ag-footer-col a,body.ag-light .ag-cabinet__block h3,body.ag-light .ag-footer-col h3{color:#7B2D3B !important;}
body.ag-light .ag-btn{background:#7B2D3B !important;color:#fff !important;box-shadow:0 4px 25px rgba(123,45,59,.25) !important;}
body.ag-light .ag-btn:hover{background:#6A2433 !important;}
body.ag-light a.ag-footer-rdv,body.ag-light a.ag-footer-rdv:visited{background:#7B2D3B !important;color:#fff !important;}
body.ag-light .ag-site-brand a{color:#7B2D3B !important;}
body.ag-light .ag-primary-menu a{color:#5A4549 !important;}
body.ag-light .ag-primary-menu a:hover{color:#7B2D3B !important;}
body.ag-light .ag-section::before{background:linear-gradient(90deg,transparent,rgba(123,45,59,.2),transparent) !important;}
body.ag-light .ag-domaine-card,body.ag-light .ag-honoraires__card,body.ag-light .ag-cabinet__block,body.ag-light .ag-rdv__form,body.ag-light .ag-testimonial-card,body.ag-light .ag-post-card,body.ag-light .ag-page-article,body.ag-light .ag-domaine-examples{background:#fff !important;border-color:rgba(0,0,0,.08) !important;box-shadow:0 4px 20px rgba(0,0,0,.06) !important;}
body.ag-light .ag-section,body.ag-light .ag-section:nth-of-type(odd),body.ag-light .ag-domaines,body.ag-light .ag-honoraires{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-section:nth-of-type(even),body.ag-light .ag-maitre,body.ag-light .ag-rdv,body.ag-light .ag-testimonials{background:#EDE6DF !important;background-image:none !important;}
body.ag-light .ag-section.ag-maitre{background:#EDE6DF !important;background-image:none !important;border-color:rgba(123,45,59,.08) !important;}
body.ag-light .ag-maitre__inner{background:#fff !important;border-color:rgba(123,45,59,.1) !important;box-shadow:0 4px 20px rgba(0,0,0,.06) !important;}
body.ag-light .ag-maitre__bio{color:#2A1A1E !important;}
body.ag-light .ag-maitre__meta{color:#5A4549 !important;}
body.ag-light .ag-maitre__name{color:#7B2D3B !important;}
body.ag-light .ag-maitre__tag{background:rgba(212,180,92,.12) !important;border-color:rgba(212,180,92,.3) !important;color:#7B2D3B !important;}
body.ag-light .ag-maitre__specialties{color:#5A4549 !important;}
body.ag-light .ag-maitre__specialties strong{color:#7B2D3B !important;}
body.ag-light .ag-section.ag-rdv{background:#EDE6DF !important;background-image:none !important;}
body.ag-light .ag-section.ag-cabinet{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-section.ag-domaines{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-section.ag-honoraires{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-section.ag-cabinet-map-section{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-site-footer{background:#EDE6DF !important;}
body.ag-light .ag-cabinet,body.ag-light .ag-cabinet-map-section{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-rdv-contact{background:linear-gradient(180deg,rgba(237,230,223,.9) 0%,rgba(237,230,223,.95) 100%),url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-light .ag-site-header.scrolled{background:rgba(245,240,235,.97) !important;}
body.ag-light .ag-menu-toggle span{background:#2A1A1E !important;}
body.ag-light .ag-hero{background:linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-light .ag-hero::before{background:none !important;}
body.ag-light .ag-page-hero{background:linear-gradient(180deg,rgba(245,240,235,.2) 0%,rgba(245,240,235,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
body.ag-light .ag-rdv__field input,body.ag-light .ag-rdv__field select,body.ag-light .ag-rdv__field textarea{background:#fff !important;border-color:rgba(0,0,0,.12) !important;color:#2A1A1E !important;}
body.ag-light .ag-footer-bottom{color:#8A7478 !important;}
body.ag-light .ag-primary-menu.open{background:rgba(245,240,235,.98) !important;}
body.ag-light .ag-theme-toggle{border-color:rgba(123,45,59,.3) !important;}
body.ag-light .ag-totop{background:#7B2D3B !important;color:#fff !important;}
body.ag-light .ag-domaine-card .ag-domaine-card__title{color:#2A1A1E !important;}
body.ag-light .ag-domaine-card__icon+.ag-domaine-card__title{color:#2A1A1E !important;}
';
        // Mode nuit: RGPD checkbox en blanc
        $css .= '
.ag-rdv__rgpd label,.ag-rdv__rgpd span{color:#fff !important;}
';
        // Mode nuit (pas ag-light) : toujours doré, fond noir, texte blanc
        $css .= '
body:not(.ag-light) .ag-hero__title span{color:#D4B45C !important;}
body:not(.ag-light) .ag-maitre__name{color:#D4B45C !important;}
body:not(.ag-light) .ag-maitre__tag{color:#D4B45C !important;background:rgba(212,180,92,.1) !important;border-color:rgba(212,180,92,.25) !important;}
body:not(.ag-light) .ag-maitre__specialties strong{color:#D4B45C !important;}
body:not(.ag-light) .ag-maitre__bio{color:#ddd !important;}
body:not(.ag-light) .ag-maitre__meta{color:#b0b0bc !important;}
body:not(.ag-light) .ag-honoraires__price{color:#D4B45C !important;}
body:not(.ag-light) .ag-domaine-card__more{color:#D4B45C !important;}
body:not(.ag-light) .ag-domaine-card__title{color:#fff !important;}
body:not(.ag-light) .ag-post-card__date{color:#D4B45C !important;}
body:not(.ag-light) .ag-post-card__more{color:#D4B45C !important;}
body:not(.ag-light) .ag-cabinet__block h3{color:#D4B45C !important;}
body:not(.ag-light) .ag-footer-col h3{color:#D4B45C !important;}
body:not(.ag-light) .ag-footer-col a{color:#D4B45C !important;}
body:not(.ag-light) .ag-domaine-back a{color:#D4B45C !important;}
body:not(.ag-light) .ag-domaine-card__title{color:#fff !important;}
body:not(.ag-light) .ag-domaine-card__excerpt{color:#ddd !important;}
body:not(.ag-light) .ag-section-title{color:#fff !important;}
body:not(.ag-light) .ag-section-lead{color:#ddd !important;}
body:not(.ag-light) .ag-honoraires__label{color:#fff !important;}
body:not(.ag-light) .ag-honoraires__desc{color:#ddd !important;}
body:not(.ag-light) .ag-honoraires__note{color:#b0b0bc !important;}
body:not(.ag-light) .ag-page-hero__title{color:#fff !important;}
body:not(.ag-light) .ag-page-hero__lead{color:#b0b0bc !important;}
body:not(.ag-light) .ag-page-hero__title{color:#fff !important;}
body:not(.ag-light) .ag-page-hero__lead{color:#b0b0bc !important;}
body:not(.ag-light) .ag-hero__title{color:#fff !important;}
body:not(.ag-light) .ag-hero__subtitle{color:#b0b0bc !important;}
body:not(.ag-light) .ag-btn{background:#D4B45C !important;color:#080808 !important;}
body:not(.ag-light) .ag-btn:hover{background:#c5a44e !important;}
body:not(.ag-light) .ag-site-brand a{color:#D4B45C !important;}
body:not(.ag-light) a.ag-footer-rdv{background:#D4B45C !important;color:#080808 !important;}
body:not(.ag-light) .ag-totop{background:#D4B45C !important;color:#080808 !important;}
body:not(.ag-light){background:#080808 !important;color:#e8e6e0 !important;}
body:not(.ag-light) .ag-site-header.scrolled{background:rgba(8,8,8,.97) !important;}
body:not(.ag-light) .ag-hero{background:linear-gradient(180deg,rgba(8,8,8,.2) 0%,rgba(8,8,8,.92) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
body:not(.ag-light) .ag-site-footer{background:#060606 !important;}
body:not(.ag-light) .ag-section{background:#080808 !important;}
body:not(.ag-light) .ag-section:nth-of-type(odd){background:radial-gradient(ellipse 900px 600px at 85% 10%,rgba(212,180,92,.06),transparent 60%),#080808 !important;}
body:not(.ag-light) .ag-section:nth-of-type(even){background:linear-gradient(135deg,#12121a 0%,#18181f 45%,#12121a 100%) !important;}
body:not(.ag-light) .ag-maitre{background:linear-gradient(135deg,rgba(212,180,92,.04) 0%,rgba(10,14,26,.8) 100%) !important;}
body:not(.ag-light) .ag-cabinet{background:#080808 !important;}
body:not(.ag-light) .ag-cabinet-map-section{background:#080808 !important;}
body:not(.ag-light) .ag-rdv{background:linear-gradient(135deg,rgba(212,180,92,.04) 0%,rgba(8,8,8,.9) 100%) !important;}
body:not(.ag-light) .ag-rdv-contact{background:linear-gradient(180deg,rgba(8,8,8,.85) 0%,rgba(8,8,8,.9) 100%),url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover no-repeat !important;background-attachment:fixed !important;}
body:not(.ag-light) .ag-rdv__form{background:rgba(255,255,255,.03) !important;border-color:rgba(212,180,92,.2) !important;}
body:not(.ag-light) .ag-rdv__field input,body:not(.ag-light) .ag-rdv__field select,body:not(.ag-light) .ag-rdv__field textarea{background:rgba(10,14,26,.8) !important;border-color:rgba(212,180,92,.2) !important;color:#fff !important;}
body:not(.ag-light) .ag-domaine-card,body:not(.ag-light) .ag-honoraires__card,body:not(.ag-light) .ag-cabinet__block,body:not(.ag-light) .ag-testimonial-card,body:not(.ag-light) .ag-post-card,body:not(.ag-light) .ag-page-article{background:rgba(255,255,255,.025) !important;border-color:rgba(255,255,255,.06) !important;}
body:not(.ag-light) .ag-page-hero{background:linear-gradient(180deg,rgba(8,8,8,.3) 0%,rgba(8,8,8,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
body:not(.ag-light) .ag-primary-menu a{color:rgba(255,255,255,.7) !important;}
body:not(.ag-light) .ag-primary-menu a:hover{color:#D4B45C !important;}
body:not(.ag-light) .ag-menu-toggle span{background:#fff !important;}
body:not(.ag-light) .ag-cabinet__block p{color:#ddd !important;}
body:not(.ag-light) .ag-cabinet__block a{color:#D4B45C !important;}
body:not(.ag-light) .ag-rdv__field label{color:#ddd !important;}
body:not(.ag-light) .ag-rdv__legal{color:#b0b0bc !important;}
body:not(.ag-light) .ag-domaine-card__excerpt{color:#ddd !important;}
body:not(.ag-light) .ag-domaine-examples__title{color:#D4B45C !important;}
body:not(.ag-light) .ag-domaine-examples__list li{color:#ddd !important;}
body:not(.ag-light) .ag-domaine-hero-tag{color:#D4B45C !important;}
body:not(.ag-light) .ag-page-article .ag-entry-content{color:#ddd !important;}
body:not(.ag-light) .ag-page-article .ag-entry-content h2,body:not(.ag-light) .ag-page-article .ag-entry-content h3{color:#fff !important;}
body:not(.ag-light) .ag-page-article .ag-entry-content p{color:#ddd !important;}
body:not(.ag-light) .ag-footer-col p,body:not(.ag-light) .ag-footer-col li{color:#b0b0bc !important;}
body:not(.ag-light) .ag-footer-bottom{color:rgba(255,255,255,.3) !important;}
body:not(.ag-light) .ag-post-card__title a{color:#fff !important;}
body:not(.ag-light) .ag-post-card__excerpt{color:#ddd !important;}
body:not(.ag-light) .ag-testimonial-card__text{color:#ddd !important;}
body:not(.ag-light) .ag-testimonial-card__author{color:#fff !important;}
body:not(.ag-light) .ag-entry-footer p{color:#b0b0bc !important;}
body:not(.ag-light) .ag-entry-footer a{color:#D4B45C !important;}
';


        // Mode jour COMPLET (copie miroir du mode nuit)
        $css .= '
body.ag-light{background:#F5F0EB !important;color:#2A1A1E !important;}
body.ag-light h1,body.ag-light h2,body.ag-light h3,body.ag-light h4{color:#2A1A1E !important;}
body.ag-light .ag-hero__title span{color:#7B2D3B !important;}
body.ag-light .ag-maitre__name{color:#7B2D3B !important;}
body.ag-light .ag-maitre__tag{color:#D4B45C !important;background:rgba(212,180,92,.1) !important;border-color:rgba(212,180,92,.25) !important;}
body.ag-light .ag-maitre__specialties strong{color:#D4B45C !important;}
body.ag-light .ag-maitre__bio{color:#2A1A1E !important;}
body.ag-light .ag-maitre__meta{color:#5A4549 !important;}
body.ag-light .ag-honoraires__price{color:#7B2D3B !important;}
body.ag-light .ag-domaine-card__more{color:#7B2D3B !important;}
body.ag-light .ag-domaine-card__title{color:#2A1A1E !important;}
body.ag-light .ag-domaine-card__excerpt{color:#3A2A2E !important;}
body.ag-light .ag-post-card__date{color:#7B2D3B !important;}
body.ag-light .ag-post-card__more{color:#7B2D3B !important;}
body.ag-light .ag-cabinet__block h3{color:#7B2D3B !important;}
body.ag-light .ag-footer-col h3{color:#7B2D3B !important;}
body.ag-light .ag-footer-col a{color:#7B2D3B !important;}
body.ag-light .ag-domaine-back a{color:#7B2D3B !important;}
body.ag-light .ag-section-title{color:#2A1A1E !important;}
body.ag-light .ag-section-lead{color:#3A2A2E !important;}
body.ag-light .ag-honoraires__label{color:#2A1A1E !important;}
body.ag-light .ag-honoraires__desc{color:#3A2A2E !important;}
body.ag-light .ag-honoraires__note{color:#5A4549 !important;}
body.ag-light .ag-page-hero__title{color:#7B2D3B !important;}
body.ag-light .ag-page-hero__lead{color:#5A4549 !important;}
body.ag-light .ag-hero__title{color:#2A1A1E !important;}
body.ag-light .ag-hero__subtitle{color:#5A4549 !important;}
body.ag-light .ag-btn{background:#7B2D3B !important;color:#fff !important;box-shadow:0 4px 25px rgba(123,45,59,.25) !important;}
body.ag-light .ag-btn:hover{background:#6A2433 !important;}
body.ag-light .ag-site-brand a{color:#7B2D3B !important;}
body.ag-light a.ag-footer-rdv,body.ag-light a.ag-footer-rdv:visited{background:#7B2D3B !important;color:#fff !important;}
body.ag-light .ag-totop{background:#7B2D3B !important;color:#fff !important;}
body.ag-light .ag-site-footer{background:#EDE6DF !important;}
body.ag-light .ag-footer-bottom{color:#8A7478 !important;}
body.ag-light .ag-primary-menu a{color:#5A4549 !important;}
body.ag-light .ag-primary-menu a:hover{color:#7B2D3B !important;}
body.ag-light .ag-menu-toggle span{background:#2A1A1E !important;}
body.ag-light .ag-cabinet__block p{color:#3A2A2E !important;}
body.ag-light .ag-cabinet__block a{color:#7B2D3B !important;}
body.ag-light .ag-rdv__field label{color:#2A1A1E !important;}
body.ag-light .ag-rdv__legal{color:#5A4549 !important;}
body.ag-light .ag-rdv__submit{color:#fff !important;}
body.ag-light .ag-rdv__rgpd label,body.ag-light .ag-rdv__rgpd span{color:#2A1A1E !important;}
body.ag-light .ag-domaine-examples__title{color:#2A1A1E !important;}
body.ag-light .ag-domaine-examples__list li{color:#2A1A1E !important;}
body.ag-light .ag-domaine-hero-tag{color:#7B2D3B !important;}
body.ag-light .ag-page-article .ag-entry-content{color:#2A1A1E !important;}
body.ag-light .ag-page-article .ag-entry-content h2,body.ag-light .ag-page-article .ag-entry-content h3{color:#2A1A1E !important;}
body.ag-light .ag-page-article .ag-entry-content p{color:#3A2A2E !important;}
body.ag-light .ag-footer-col p,body.ag-light .ag-footer-col li{color:#5A4549 !important;}
body.ag-light .ag-post-card__title a{color:#2A1A1E !important;}
body.ag-light .ag-post-card__excerpt{color:#3A2A2E !important;}
body.ag-light .ag-testimonial-card__text{color:#3A2A2E !important;}
body.ag-light .ag-testimonial-card__author{color:#2A1A1E !important;}
body.ag-light .ag-entry-footer p{color:#5A4549 !important;}
body.ag-light .ag-entry-footer a{color:#7B2D3B !important;}
body.ag-light .ag-rdv__field input,body.ag-light .ag-rdv__field select,body.ag-light .ag-rdv__field textarea{background:#fff !important;border-color:rgba(0,0,0,.12) !important;color:#2A1A1E !important;}
body.ag-light .ag-section{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-section:nth-of-type(even){background:#EDE6DF !important;background-image:none !important;}
body.ag-light .ag-maitre{background:#EDE6DF !important;background-image:none !important;}
body.ag-light .ag-maitre__inner{background:#fff !important;border-color:rgba(123,45,59,.1) !important;box-shadow:0 4px 20px rgba(0,0,0,.06) !important;}
body.ag-light .ag-cabinet{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-cabinet-map-section{background:#F5F0EB !important;background-image:none !important;}
body.ag-light .ag-rdv{background:#EDE6DF !important;background-image:none !important;}
body.ag-light .ag-rdv__form{background:#fff !important;border-color:rgba(0,0,0,.08) !important;}
body.ag-light .ag-rdv__rgpd{background:rgba(0,0,0,.03) !important;}
body.ag-light .ag-domaine-card,body.ag-light .ag-honoraires__card,body.ag-light .ag-cabinet__block,body.ag-light .ag-testimonial-card,body.ag-light .ag-post-card,body.ag-light .ag-page-article,body.ag-light .ag-domaine-examples{background:#fff !important;border-color:rgba(0,0,0,.08) !important;box-shadow:0 4px 20px rgba(0,0,0,.06) !important;}
body.ag-light .ag-hero{background:linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-light .ag-hero::before{background:none !important;}
body.ag-light .ag-page-hero{background:linear-gradient(180deg,rgba(245,240,235,.2) 0%,rgba(245,240,235,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
body.ag-light .ag-site-header.scrolled{background:rgba(245,240,235,.97) !important;}
body.ag-light .ag-section::before{background:linear-gradient(90deg,transparent,rgba(123,45,59,.2),transparent) !important;}

/* ═══════════════════════════════════════════════════════════════
   LE MAÎTRE — bloc final consolidé (override toutes règles précédentes)
   Layout : photo (280px) + texte cote a cote, mobile : empile
   Mode sombre : carte fond #131826 + nom dore + bio blanche
   Mode clair  : carte fond #fff + nom bordeaux + bio noire
   ═══════════════════════════════════════════════════════════════ */

/* Layout commun (les deux modes) */
.ag-section.ag-maitre{padding:80px 0 !important;}
.ag-maitre__inner{
    display:grid !important;
    grid-template-columns:280px 1fr !important;
    gap:48px !important;
    align-items:center !important;
    padding:32px !important;
    border-radius:12px !important;
    border:1px solid transparent !important;
}
.ag-maitre__photo img{
    width:100% !important;
    height:auto !important;
    aspect-ratio:1/1 !important;
    object-fit:cover !important;
    border-radius:12px !important;
    border:2px solid rgba(' . $gold_rgb . ',.28) !important;
    display:block !important;
}
.ag-maitre__tag{
    display:inline-block !important;
    padding:4px 14px !important;
    border-radius:100px !important;
    font-size:.75rem !important;
    font-weight:600 !important;
    text-transform:uppercase !important;
    letter-spacing:.12em !important;
    margin-bottom:14px !important;
    border:1px solid transparent !important;
}
.ag-maitre__name{
    font-size:2rem !important;
    font-style:italic !important;
    margin-bottom:6px !important;
    line-height:1.2 !important;
}
.ag-maitre__meta{font-size:.9rem !important;margin-bottom:18px !important;}
.ag-maitre__bio{line-height:1.75 !important;margin-bottom:14px !important;font-size:1rem !important;}
.ag-maitre__specialties{font-size:.92rem !important;}

@media(max-width:720px){
    .ag-maitre__inner{grid-template-columns:1fr !important;text-align:center !important;padding:24px !important;gap:24px !important;}
    .ag-maitre__photo{max-width:240px !important;margin:0 auto !important;}
}

/* === Mode sombre (defaut) — Premium suit le skin choisi (NE PAS TOUCHER) === */
body:not(.ag-light) .ag-section.ag-maitre{background:linear-gradient(135deg,rgba(' . $gold_rgb . ',.04) 0%,#0F1320 100%) !important;}
body:not(.ag-light) .ag-maitre__inner{background:#131826 !important;border-color:rgba(' . $gold_rgb . ',.18) !important;box-shadow:0 10px 40px rgba(0,0,0,.35) !important;}
body:not(.ag-light) .ag-maitre__tag{background:rgba(' . $gold_rgb . ',.12) !important;color:' . $gold . ' !important;border-color:rgba(' . $gold_rgb . ',.3) !important;}
body:not(.ag-light) .ag-maitre__name{color:' . $gold . ' !important;}
body:not(.ag-light) .ag-maitre__meta{color:rgba(255,255,255,.6) !important;}
body:not(.ag-light) .ag-maitre__bio{color:#f3f3f3 !important;}
body:not(.ag-light) .ag-maitre__specialties{color:rgba(255,255,255,.7) !important;}
body:not(.ag-light) .ag-maitre__specialties strong{color:' . $gold . ' !important;}

/* === BUSINESS uniquement : DORE forcé pour identité Maître ===
   Le tier Business affiche TOUJOURS du doré sur la section Maître,
   peu importe le skin (Or, Bordeaux, Bronze, Ardoise). Identité du theme.
   Premium n\'est PAS affecté — il continue de suivre le skin choisi. */
body.ag-tier-business:not(.ag-light) .ag-section.ag-maitre{background:linear-gradient(135deg,rgba(212,180,92,.04) 0%,#0F1320 100%) !important;}
body.ag-tier-business:not(.ag-light) .ag-maitre__inner{border-color:rgba(212,180,92,.18) !important;}
body.ag-tier-business:not(.ag-light) .ag-maitre__photo img{border-color:rgba(212,180,92,.28) !important;}
body.ag-tier-business:not(.ag-light) .ag-maitre__tag{background:rgba(212,180,92,.12) !important;color:#D4B45C !important;border-color:rgba(212,180,92,.3) !important;}
body.ag-tier-business:not(.ag-light) .ag-maitre__name{color:#D4B45C !important;}
body.ag-tier-business:not(.ag-light) .ag-maitre__specialties strong{color:#D4B45C !important;}

/* ═══════════════════════════════════════════════════════════════
   BUSINESS — Parallax citations + Équipe + Boutique + Scintillation
   ═══════════════════════════════════════════════════════════════ */

/* ── Parallax citations (style alliance-groupe) ── */
.ag-parallax-business{position:relative !important;padding:120px 24px !important;background-attachment:fixed !important;background-size:cover !important;background-position:center !important;text-align:center !important;overflow:hidden !important;}
.ag-parallax-business .ag-parallax__overlay{position:absolute !important;inset:0 !important;background:linear-gradient(135deg,rgba(8,10,18,.88) 0%,rgba(20,15,8,.78) 100%) !important;}
.ag-parallax-business .ag-parallax__content{position:relative !important;z-index:2 !important;max-width:780px !important;margin:0 auto !important;}
.ag-parallax-business .ag-parallax__quote{font-style:italic !important;font-size:clamp(1.4rem,3vw,2.2rem) !important;color:#D4B45C !important;line-height:1.5 !important;margin:0 0 16px !important;text-shadow:0 2px 20px rgba(0,0,0,.5) !important;}
.ag-parallax-business .ag-parallax__caption{color:rgba(255,255,255,.7) !important;font-size:.9rem !important;letter-spacing:1.5px !important;text-transform:uppercase !important;margin:0 !important;}
@media(max-width:768px){.ag-parallax-business{background-attachment:scroll !important;padding:80px 24px !important;}}

/* ── Section Équipe ── */
.ag-section.ag-team{padding:100px 0 !important;background:linear-gradient(180deg,#0A0E1A 0%,#0F1320 100%) !important;}
body.ag-light .ag-section.ag-team{background:#F5F0EB !important;}
.ag-team__grid{display:grid !important;grid-template-columns:repeat(4,1fr) !important;gap:28px !important;margin-top:48px !important;}
.ag-team-card{background:#131826 !important;border:1px solid rgba(212,180,92,.15) !important;border-radius:14px !important;overflow:hidden !important;transition:transform .4s ease,box-shadow .4s ease,border-color .4s ease !important;display:block !important;text-decoration:none !important;cursor:pointer !important;color:inherit !important;}
.ag-team-card__more{display:inline-block !important;margin-top:10px !important;color:#D4B45C !important;font-weight:700 !important;font-size:.82rem !important;letter-spacing:.5px !important;}
body.ag-light .ag-team-card__more{color:#7B2D3B !important;}
body.ag-tier-business:not(.ag-light) .ag-team-card:hover .ag-team-card__more{color:#E07585 !important;}
body.ag-tier-business.ag-light .ag-team-card:hover .ag-team-card__more{color:#B8941F !important;}
.ag-team-card:hover{transform:translateY(-6px) !important;border-color:rgba(212,180,92,.4) !important;box-shadow:0 20px 50px rgba(0,0,0,.5),0 0 30px rgba(212,180,92,.15) !important;}
.ag-team-card__photo{aspect-ratio:1/1 !important;overflow:hidden !important;}
.ag-team-card__photo img{width:100% !important;height:100% !important;object-fit:cover !important;display:block !important;transition:transform .6s ease !important;}
.ag-team-card:hover .ag-team-card__photo img{transform:scale(1.05) !important;}
.ag-team-card__body{padding:20px 18px !important;}
.ag-team-card__name{color:#D4B45C !important;font-size:1.05rem !important;font-style:italic !important;margin:0 0 4px !important;}
.ag-team-card__role{color:rgba(255,255,255,.6) !important;font-size:.78rem !important;text-transform:uppercase !important;letter-spacing:1.2px !important;margin:0 0 12px !important;}
.ag-team-card__bio{color:#ddd !important;font-size:.88rem !important;line-height:1.6 !important;margin:0 !important;}
body.ag-light .ag-team-card{background:#fff !important;border-color:rgba(123,45,59,.1) !important;box-shadow:0 4px 20px rgba(0,0,0,.05) !important;}
body.ag-light .ag-team-card:hover{border-color:rgba(123,45,59,.3) !important;box-shadow:0 20px 50px rgba(0,0,0,.1) !important;}
body.ag-light .ag-team-card__name{color:#7B2D3B !important;}
body.ag-light .ag-team-card__role{color:#5A4549 !important;}
body.ag-light .ag-team-card__bio{color:#2A1A1E !important;}
@media(max-width:980px){.ag-team__grid{grid-template-columns:repeat(2,1fr) !important;}}
@media(max-width:560px){.ag-team__grid{grid-template-columns:1fr !important;}}

/* ── Section Boutique ── */
.ag-section.ag-boutique{padding:100px 0 !important;background:linear-gradient(180deg,#0F1320 0%,#0A0E1A 100%) !important;}
body.ag-light .ag-section.ag-boutique{background:#EDE6DF !important;}
/* Tag "BOUTIQUE" version clean : pas de border, pas de bg */
.ag-boutique .ag-tag,
.ag-boutique .ag-tag--clean{
    display:inline-block !important;
    padding:0 !important;
    background:transparent !important;
    border:none !important;
    color:#D4B45C !important;
    font-size:1rem !important;
    font-weight:700 !important;
    text-transform:uppercase !important;
    letter-spacing:6px !important;
    margin-bottom:8px !important;
}
body.ag-light .ag-boutique .ag-tag,
body.ag-light .ag-boutique .ag-tag--clean{
    background:transparent !important;
    border:none !important;
    color:#7B2D3B !important;
}

/* Titre "Boutique" XXL — Playfair italic */
.ag-boutique__title-xl{
    font-size:clamp(2.8rem,7vw,5.5rem) !important;
    font-style:italic !important;
    font-weight:700 !important;
    line-height:1.05 !important;
    margin:0 0 18px !important;
}

/* Effet etoiles qui pulsent depuis le centre vers le viewer
   Approche simple, garantie : pas de !important sur transform/opacity
   pour que l animation puisse les piloter sans conflit cascade */
.ag-section.ag-boutique{position:relative !important;overflow:hidden !important;}
.ag-boutique__stars{
    position:absolute !important;
    top:0;left:0;right:0;bottom:0;
    pointer-events:none !important;
    z-index:1 !important;
    overflow:hidden;
}
.ag-boutique__star{
    position:absolute;
    display:block;
    width:60px;
    height:60px;
    color:#D4B45C;
    opacity:0;
    transform:scale(0);
    transform-origin:center;
    pointer-events:none;
    filter:drop-shadow(0 0 20px rgba(255,229,160,.9)) drop-shadow(0 0 50px rgba(212,180,92,.65));
    animation:agStarBurst 4s ease-out infinite both;
    will-change:transform,opacity;
}
.ag-boutique__star svg{width:100%;height:100%;display:block;color:inherit;}
.ag-boutique__star--1{top:25%;left:50%;margin-left:-30px;margin-top:-30px;animation-delay:0s;}
.ag-boutique__star--2{top:55%;left:25%;margin-left:-30px;margin-top:-30px;animation-delay:1.3s;}
.ag-boutique__star--3{top:45%;left:75%;margin-left:-30px;margin-top:-30px;animation-delay:2.6s;}

@keyframes agStarBurst{
    0%   {transform:scale(0)  rotate(0deg);   opacity:0;}
    15%  {transform:scale(1)  rotate(20deg);  opacity:1;}
    50%  {transform:scale(8)  rotate(60deg);  opacity:.7;}
    80%  {transform:scale(20) rotate(90deg);  opacity:.15;}
    100% {transform:scale(35) rotate(120deg); opacity:0;}
}

/* Mode jour : étoiles plus contenues + halo subtil */
body.ag-light .ag-boutique__star{
    color:#D4B45C;
    filter:drop-shadow(0 0 18px rgba(212,180,92,.55)) drop-shadow(0 0 35px rgba(212,180,92,.25));
}

/* Le contenu doit passer au-dessus des étoiles */
.ag-section.ag-boutique .ag-container{position:relative !important;z-index:2 !important;}

/* Respect prefers-reduced-motion */
@media (prefers-reduced-motion:reduce){
    .ag-boutique__star{animation:none !important;display:none !important;}
}
.ag-boutique__grid{display:grid !important;grid-template-columns:repeat(3,1fr) !important;gap:28px !important;margin:48px 0 24px !important;}
.ag-boutique-card{background:#131826 !important;border:1px solid rgba(212,180,92,.18) !important;border-radius:14px !important;padding:32px 28px !important;text-align:center !important;transition:transform .4s ease,box-shadow .4s ease,border-color .4s ease !important;position:relative !important;overflow:hidden !important;}
.ag-boutique-card::before{content:"" !important;position:absolute !important;top:0 !important;left:-100% !important;width:100% !important;height:2px !important;background:linear-gradient(90deg,transparent,#D4B45C,transparent) !important;transition:left .8s ease !important;}
.ag-boutique-card:hover::before{left:100% !important;}
.ag-boutique-card:hover{transform:translateY(-8px) !important;border-color:rgba(212,180,92,.5) !important;box-shadow:0 30px 60px rgba(0,0,0,.5),0 0 40px rgba(212,180,92,.2) !important;}
.ag-boutique-card__icon{font-size:3rem !important;margin-bottom:14px !important;}
.ag-boutique-card__title{color:#fff !important;font-size:1.2rem !important;margin:0 0 12px !important;}
.ag-boutique-card__price{color:#D4B45C !important;font-size:2.4rem !important;font-weight:800 !important;font-style:italic !important;margin:0 0 16px !important;}
.ag-boutique-card__desc{color:#bbb !important;font-size:.92rem !important;line-height:1.65 !important;margin:0 0 24px !important;}
.ag-boutique-card__btn{width:100% !important;text-align:center !important;}
.ag-boutique-card__image{width:100% !important;aspect-ratio:4/3 !important;overflow:hidden !important;border-radius:10px !important;margin-bottom:18px !important;background:#0F1320 !important;}
.ag-boutique-card__image img{width:100% !important;height:100% !important;object-fit:cover !important;display:block !important;transition:transform .6s ease !important;}
.ag-boutique-card--with-image:hover .ag-boutique-card__image img{transform:scale(1.06) !important;}
.ag-boutique-card--with-image{text-align:left !important;}
.ag-boutique-card--with-image .ag-boutique-card__title{text-align:left !important;}
.ag-boutique-card--with-image .ag-boutique-card__price{text-align:left !important;}
.ag-boutique-card--with-image .ag-boutique-card__desc{text-align:left !important;}
.ag-boutique__more{text-align:center !important;margin:32px 0 12px !important;}
.ag-boutique__more-link{display:inline-block !important;color:#D4B45C !important;text-decoration:none !important;font-weight:700 !important;font-size:1rem !important;letter-spacing:1px !important;border-bottom:1px solid rgba(212,180,92,.4) !important;padding-bottom:2px !important;transition:color .3s,border-color .3s !important;}
.ag-boutique__more-link:hover{color:#FFE5A0 !important;border-color:#FFE5A0 !important;}
body.ag-light .ag-boutique__more-link{color:#7B2D3B !important;border-color:rgba(123,45,59,.4) !important;}
body.ag-light .ag-boutique__more-link:hover{color:#A03D4D !important;border-color:#A03D4D !important;}
.ag-boutique__note{text-align:center !important;color:#888 !important;font-size:.85rem !important;margin-top:24px !important;}
body.ag-light .ag-boutique-card{background:#fff !important;border-color:rgba(123,45,59,.1) !important;}
body.ag-light .ag-boutique-card:hover{border-color:rgba(123,45,59,.4) !important;box-shadow:0 30px 60px rgba(0,0,0,.1) !important;}
body.ag-light .ag-boutique-card__title{color:#2A1A1E !important;}
body.ag-light .ag-boutique-card__price{color:#7B2D3B !important;}
body.ag-light .ag-boutique-card__desc{color:#3A2A2E !important;}
body.ag-light .ag-boutique__note{color:#5A4549 !important;}
@media(max-width:980px){.ag-boutique__grid{grid-template-columns:1fr !important;gap:20px !important;}}

/* ── Scintillation Business : effets robustes (cross-browser) ── */
@keyframes agShimmerGold{
    0%,100%{color:#D4B45C;text-shadow:0 0 18px rgba(212,180,92,.3);}
    50%{color:#FFE5A0;text-shadow:0 0 35px rgba(255,229,160,.7),0 0 60px rgba(212,180,92,.45);}
}
@keyframes agShimmerBordeaux{
    0%,100%{color:#7B2D3B;text-shadow:0 0 14px rgba(123,45,59,.2);}
    50%{color:#A03D4D;text-shadow:0 0 28px rgba(160,61,77,.45),0 0 50px rgba(123,45,59,.25);}
}
@keyframes agPulseGlow{
    0%,100%{box-shadow:0 4px 25px rgba(212,180,92,.25);}
    50%{box-shadow:0 4px 25px rgba(212,180,92,.6),0 0 40px rgba(212,180,92,.35);}
}
@keyframes agSparkleFloat{
    0%,100%{transform:translateY(0) rotate(0deg);opacity:.6;}
    50%{transform:translateY(-3px) rotate(15deg);opacity:1;}
}
@keyframes agScanLine{
    0%{transform:translateX(-100%);}
    100%{transform:translateX(100%);}
}

/* Titre "Notre équipe" : couleur dorée pulsante avec halo */
body.ag-tier-business .ag-shimmer-text{
    color:#D4B45C !important;
    animation:agShimmerGold 2.8s ease-in-out infinite !important;
    font-style:italic !important;
}
body.ag-tier-business.ag-light .ag-shimmer-text{
    color:#7B2D3B !important;
    animation:agShimmerBordeaux 2.8s ease-in-out infinite !important;
}

/* Tag "🛍 BOUTIQUE" : sparkle ✨ visible (overflow:visible !) */
body.ag-tier-business .ag-shimmer{position:relative !important;display:inline-block !important;}
body.ag-tier-business .ag-shimmer::after{
    content:"✨" !important;
    position:absolute !important;
    top:-10px !important;
    right:-14px !important;
    font-size:1rem !important;
    animation:agSparkleFloat 1.8s ease-in-out infinite !important;
    pointer-events:none !important;
}

/* Tous les boutons en Business : hover = pulseGlow doré */
body.ag-tier-business .ag-btn:hover,
body.ag-tier-business .ag-boutique-card__btn:hover{
    animation:agPulseGlow 1.5s ease-in-out infinite !important;
    transform:translateY(-2px) !important;
}

/* Cartes Boutique + Honoraires + Menu nav : balayage au hover
   Mode NUIT  → BORDEAUX (#7B2D3B)
   Mode JOUR → DORE     (#D4B45C) */
@keyframes agGoldSweep{
    0%{transform:translateX(-100%) skewX(-15deg);}
    100%{transform:translateX(200%) skewX(-15deg);}
}

/* ─── MENU PRINCIPAL au hover (style Boutique) ─── */
body.ag-tier-business .ag-primary-menu{align-items:center !important;}
body.ag-tier-business .ag-primary-menu a{
    position:relative !important;
    display:inline-block !important;
    padding:10px 16px !important;
    border-radius:8px !important;
    overflow:hidden !important;
    isolation:isolate !important;
    transition:color .35s ease,background .35s ease,text-shadow .35s ease,transform .3s ease !important;
}
/* Balayage diagonal (::before) — derrière le texte */
body.ag-tier-business .ag-primary-menu a::before{
    content:"" !important;
    position:absolute !important;
    top:0 !important;
    left:0 !important;
    width:60% !important;
    height:100% !important;
    transform:translateX(-100%) skewX(-15deg) !important;
    pointer-events:none !important;
    z-index:-1 !important;
}
body.ag-tier-business .ag-primary-menu a:hover::before{animation:agGoldSweep 1.4s ease-in-out infinite !important;}

/* Underline qui pousse depuis le centre (::after) */
body.ag-tier-business .ag-primary-menu a::after{
    content:"" !important;
    position:absolute !important;
    bottom:4px !important;
    left:50% !important;
    width:0 !important;
    height:2px !important;
    transform:translateX(-50%) !important;
    transition:width .4s ease !important;
    pointer-events:none !important;
}
body.ag-tier-business .ag-primary-menu a:hover::after{width:calc(100% - 28px) !important;}
body.ag-tier-business .ag-primary-menu a:hover{transform:translateY(-2px) !important;}

/* Mode NUIT : balayage bordeaux + texte rosé clair */
body.ag-tier-business:not(.ag-light) .ag-primary-menu a::before{
    background:linear-gradient(110deg,transparent 0%,rgba(123,45,59,0) 30%,rgba(160,61,77,.45) 50%,rgba(123,45,59,0) 70%,transparent 100%) !important;
}
body.ag-tier-business:not(.ag-light) .ag-primary-menu a::after{
    background:linear-gradient(90deg,transparent,#A03D4D,transparent) !important;
    box-shadow:0 0 10px rgba(160,61,77,.7) !important;
}
body.ag-tier-business:not(.ag-light) .ag-primary-menu a:hover{
    color:#E07585 !important;
    background:rgba(123,45,59,.1) !important;
    text-shadow:0 0 14px rgba(160,61,77,.5) !important;
}

/* Mode JOUR : seulement underline dore + couleur du texte (PAS de hover de la case)
   Pas de balayage / box bg en mode jour. */
body.ag-tier-business.ag-light .ag-primary-menu a::before{display:none !important;}
body.ag-tier-business.ag-light .ag-primary-menu a{
    overflow:visible !important;
    isolation:auto !important;
    background:transparent !important;
    border-radius:0 !important;
    padding:10px 14px !important;
}
body.ag-tier-business.ag-light .ag-primary-menu a::after{
    background:linear-gradient(90deg,transparent,#D4B45C,transparent) !important;
    box-shadow:0 0 10px rgba(212,180,92,.7) !important;
}
body.ag-tier-business.ag-light .ag-primary-menu a:hover{
    color:#B8941F !important;
    background:transparent !important;
    transform:none !important;
    text-shadow:0 0 8px rgba(212,180,92,.4) !important;
}
@media(max-width:768px){
    body.ag-tier-business .ag-primary-menu a::after{display:none !important;}
}

/* ─── LOGO (custom_logo ou texte) au hover ─── */
@keyframes agLogoPulse{
    0%,100%{filter:drop-shadow(0 0 0 transparent);}
    50%{filter:drop-shadow(0 0 14px var(--ag-logo-glow));}
}
body.ag-tier-business .ag-site-brand{
    --ag-logo-glow:rgba(212,180,92,.5);
    transition:transform .35s ease !important;
}
body.ag-tier-business:not(.ag-light) .ag-site-brand{--ag-logo-glow:rgba(160,61,77,.55);}
body.ag-tier-business .ag-site-brand a,
body.ag-tier-business .ag-site-brand img,
body.ag-tier-business .ag-site-brand .custom-logo-link{
    display:inline-block !important;
    transition:transform .35s ease,filter .4s ease,text-shadow .35s ease,color .35s ease !important;
}
body.ag-tier-business .ag-site-brand:hover{transform:translateY(-2px) !important;}
body.ag-tier-business .ag-site-brand:hover img,
body.ag-tier-business .ag-site-brand:hover .custom-logo-link img{
    animation:agLogoPulse 1.6s ease-in-out infinite !important;
    transform:scale(1.04) !important;
}
/* Si fallback texte (pas de logo upload) → halo + couleur */
body.ag-tier-business:not(.ag-light) .ag-site-brand a:hover{color:#E07585 !important;text-shadow:0 0 16px rgba(160,61,77,.6) !important;}
body.ag-tier-business.ag-light .ag-site-brand a:hover{color:#B8941F !important;text-shadow:0 0 16px rgba(212,180,92,.6) !important;}

/* ─── COMPTEURS Business : chiffres scintillants ─── */
@keyframes agCounterShimmer{
    0%,100%{text-shadow:0 0 12px rgba(212,180,92,.25);}
    50%{text-shadow:0 0 24px rgba(212,180,92,.6),0 0 40px rgba(212,180,92,.3);}
}
@keyframes agCounterShimmerBordeaux{
    0%,100%{text-shadow:0 0 12px rgba(160,61,77,.25);}
    50%{text-shadow:0 0 24px rgba(160,61,77,.55),0 0 40px rgba(123,45,59,.3);}
}
body.ag-tier-business .ag-counter{
    position:relative !important;
    transition:transform .4s ease,background .4s ease,border-color .4s ease,box-shadow .4s ease !important;
    overflow:hidden !important;
}
body.ag-tier-business .ag-counter::before{
    content:"" !important;
    position:absolute !important;
    top:0 !important;left:0 !important;
    width:60% !important;height:100% !important;
    transform:translateX(-100%) skewX(-15deg) !important;
    pointer-events:none !important;z-index:1 !important;
}
body.ag-tier-business .ag-counter:hover::before{animation:agGoldSweep 1.6s ease-in-out infinite !important;}
body.ag-tier-business .ag-counter>*{position:relative !important;z-index:2 !important;}
body.ag-tier-business .ag-counter:hover{transform:translateY(-6px) !important;}
body.ag-tier-business .ag-counter__number{transition:transform .4s ease !important;}
body.ag-tier-business .ag-counter:hover .ag-counter__number{transform:scale(1.08) !important;}

/* Mode NUIT : compteurs bordeaux pulsant */
body.ag-tier-business:not(.ag-light) .ag-counter__number{
    color:#E07585 !important;
    animation:agCounterShimmerBordeaux 2.5s ease-in-out infinite !important;
}
body.ag-tier-business:not(.ag-light) .ag-counter::before{
    background:linear-gradient(110deg,transparent 0%,rgba(123,45,59,0) 30%,rgba(160,61,77,.4) 50%,rgba(123,45,59,0) 70%,transparent 100%) !important;
}
body.ag-tier-business:not(.ag-light) .ag-counter:hover{
    border-color:#A03D4D !important;
    box-shadow:0 20px 40px rgba(0,0,0,.4),0 0 35px rgba(160,61,77,.45) !important;
}

/* Mode JOUR : compteurs dorés pulsant */
body.ag-tier-business.ag-light .ag-counter__number{
    color:#B8941F !important;
    animation:agCounterShimmer 2.5s ease-in-out infinite !important;
}
body.ag-tier-business.ag-light .ag-counter::before{
    background:linear-gradient(110deg,transparent 0%,rgba(212,180,92,0) 30%,rgba(212,180,92,.45) 50%,rgba(212,180,92,0) 70%,transparent 100%) !important;
}
body.ag-tier-business.ag-light .ag-counter:hover{
    border-color:#D4B45C !important;
    box-shadow:0 20px 40px rgba(0,0,0,.1),0 0 35px rgba(212,180,92,.45) !important;
}

/* ─── BUSINESS : Logo SVG par défaut + fonts plume + signature ─── */

/* Fonts business : titres Playfair, body Cormorant, signature Allison */
body.ag-tier-business{font-family:"Cormorant Garamond","Playfair Display",Georgia,serif !important;font-size:1.05rem !important;}
body.ag-tier-business h1,body.ag-tier-business h2,body.ag-tier-business h3,body.ag-tier-business h4,body.ag-tier-business .ag-section-title,body.ag-tier-business .ag-page-hero__title,body.ag-tier-business .ag-hero__title{font-family:"Playfair Display","Cormorant Garamond",Georgia,serif !important;font-weight:700 !important;letter-spacing:.2px !important;}
body.ag-tier-business .ag-maitre__name{font-family:"Allison","Playfair Display",cursive !important;font-size:3.6rem !important;font-style:normal !important;font-weight:400 !important;letter-spacing:0 !important;line-height:1 !important;}
body.ag-tier-business .ag-domaine-card__title,body.ag-tier-business .ag-honoraires__label,body.ag-tier-business .ag-team-card__name,body.ag-tier-business .ag-boutique-card__title{font-family:"Playfair Display",serif !important;font-style:italic !important;}

/* Logo SVG par défaut quand pas de custom_logo (Business uniquement) */
body.ag-tier-business.ag-no-custom-logo .ag-site-brand a{display:flex !important;align-items:center !important;gap:10px !important;text-decoration:none !important;}
body.ag-tier-business.ag-no-custom-logo .ag-site-brand .ag-default-logo-svg{display:inline-block;width:64px;height:64px;line-height:0;filter:drop-shadow(0 4px 16px rgba(212,180,92,.35));transition:transform .35s ease,filter .35s ease;}
body.ag-tier-business.ag-no-custom-logo .ag-site-brand .ag-default-logo-svg svg{width:100%;height:100%;display:block;}
body.ag-tier-business.ag-no-custom-logo .ag-site-brand:hover .ag-default-logo-svg{transform:scale(1.06) rotate(-3deg);filter:drop-shadow(0 6px 24px rgba(212,180,92,.55));}
body.ag-tier-business.ag-no-custom-logo .ag-site-brand__text{display:none !important;}
body.ag-tier-business.ag-light.ag-no-custom-logo .ag-site-brand .ag-default-logo-svg{filter:drop-shadow(0 3px 10px rgba(123,45,59,.25));}
body.ag-tier-business.ag-light.ag-no-custom-logo .ag-site-brand:hover .ag-default-logo-svg{filter:drop-shadow(0 5px 18px rgba(123,45,59,.45));}
@media(max-width:768px){body.ag-tier-business.ag-no-custom-logo .ag-site-brand .ag-default-logo-svg{width:50px;height:50px;}}

/* Signature dans la section Maître */
.ag-maitre__signature{margin-top:18px;padding-top:14px;border-top:1px solid rgba(212,180,92,.15);}
.ag-maitre__signature img{max-width:240px;height:auto;display:block;opacity:.9;filter:none;}
body.ag-tier-business:not(.ag-light) .ag-maitre__signature img{filter:invert(1) brightness(1.3) contrast(1.2);}
body.ag-tier-business .ag-maitre__signature-text{font-family:"Allison","Mr Dafoe",cursive !important;font-size:3rem !important;line-height:1 !important;color:#D4B45C !important;display:inline-block;text-shadow:0 2px 8px rgba(212,180,92,.25);}
body.ag-tier-business.ag-light .ag-maitre__signature-text{color:#7B2D3B !important;text-shadow:0 2px 8px rgba(123,45,59,.18);}
body.ag-tier-business.ag-light .ag-maitre__signature{border-top-color:rgba(123,45,59,.15);}

/* ─── ICONES SVG inline (Domaines) — léger & vectoriel ─── */
.ag-icon-svg{display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;color:#D4B45C;transition:color .35s ease,transform .4s ease;}
.ag-icon-svg svg{width:100%;height:100%;}
.ag-icon-emoji{font-size:2.4rem;line-height:1;display:inline-block;}
body.ag-light .ag-icon-svg{color:#7B2D3B;}
.ag-domaine-card__icon{display:flex;justify-content:center;margin-bottom:14px;}
.ag-domaine-hero-icon .ag-icon-svg{width:64px;height:64px;}
body.ag-tier-business .ag-domaine-card:hover .ag-icon-svg{transform:scale(1.18) rotate(-5deg);}
body.ag-tier-business:not(.ag-light) .ag-domaine-card:hover .ag-icon-svg{color:#E07585;filter:drop-shadow(0 0 12px rgba(160,61,77,.55));}
body.ag-tier-business.ag-light .ag-domaine-card:hover .ag-icon-svg{color:#B8941F;filter:drop-shadow(0 0 12px rgba(212,180,92,.6));}

/* ─── DOMAINES cards : background image full + overlay (tous tiers) ─── */
.ag-domaines__grid{display:grid !important;grid-template-columns:repeat(auto-fit,minmax(280px,1fr)) !important;gap:24px !important;}
.ag-domaine-card--bg{
    position:relative !important;
    display:flex !important;
    flex-direction:column !important;
    justify-content:flex-end !important;
    min-height:340px !important;
    padding:0 !important;
    border-radius:14px !important;
    overflow:hidden !important;
    text-decoration:none !important;
    color:#fff !important;
    background:#0F1320 !important;
    transition:transform .4s ease,box-shadow .5s ease !important;
}
.ag-domaine-card--bg .ag-domaine-card__bg{
    position:absolute !important;
    inset:0 !important;
    background-size:cover !important;
    background-position:center !important;
    transition:transform .8s ease,filter .5s ease !important;
    z-index:1 !important;
}
.ag-domaine-card--bg .ag-domaine-card__overlay{
    position:absolute !important;
    inset:0 !important;
    background:linear-gradient(180deg,rgba(8,10,18,.15) 0%,rgba(8,10,18,.55) 50%,rgba(8,10,18,.92) 100%) !important;
    z-index:2 !important;
    transition:background .4s ease !important;
}
.ag-domaine-card--bg .ag-domaine-card__content{
    position:relative !important;
    z-index:3 !important;
    padding:28px 24px !important;
    margin-top:auto !important;
}
.ag-domaine-card--bg .ag-domaine-card__title{
    color:#fff !important;
    font-size:1.35rem !important;
    margin:0 0 8px !important;
    text-shadow:0 2px 12px rgba(0,0,0,.6) !important;
}
.ag-domaine-card--bg .ag-domaine-card__excerpt{
    color:rgba(255,255,255,.85) !important;
    font-size:.92rem !important;
    line-height:1.55 !important;
    margin:0 0 12px !important;
    text-shadow:0 1px 6px rgba(0,0,0,.7) !important;
}
.ag-domaine-card--bg .ag-domaine-card__examples{
    list-style:none !important;
    padding:0 !important;
    margin:0 0 14px !important;
    display:flex !important;
    flex-wrap:wrap !important;
    gap:6px !important;
}
.ag-domaine-card--bg .ag-domaine-card__examples li{
    background:rgba(212,180,92,.18) !important;
    border:1px solid rgba(212,180,92,.4) !important;
    color:#FFE5A0 !important;
    padding:3px 10px !important;
    border-radius:100px !important;
    font-size:.72rem !important;
    backdrop-filter:blur(4px) !important;
}
.ag-domaine-card--bg .ag-domaine-card__more{
    color:#D4B45C !important;
    font-weight:700 !important;
    font-size:.88rem !important;
    text-shadow:0 1px 6px rgba(0,0,0,.6) !important;
}

/* Hover (tous tiers) : zoom image + overlay plus dense */
.ag-domaine-card--bg:hover{transform:translateY(-6px) !important;box-shadow:0 30px 60px rgba(0,0,0,.5) !important;}
.ag-domaine-card--bg:hover .ag-domaine-card__bg{transform:scale(1.08) !important;}

/* Mode JOUR : overlay clair + texte sombre + chips bordeaux */
body.ag-light .ag-domaine-card--bg .ag-domaine-card__overlay{
    background:linear-gradient(180deg,rgba(245,240,235,.1) 0%,rgba(245,240,235,.55) 50%,rgba(245,240,235,.95) 100%) !important;
}
body.ag-light .ag-domaine-card--bg .ag-domaine-card__title{color:#2A1A1E !important;text-shadow:0 1px 4px rgba(255,255,255,.6) !important;}
body.ag-light .ag-domaine-card--bg .ag-domaine-card__excerpt{color:#3A2A2E !important;text-shadow:none !important;}
body.ag-light .ag-domaine-card--bg .ag-domaine-card__examples li{background:rgba(123,45,59,.1) !important;border-color:rgba(123,45,59,.3) !important;color:#7B2D3B !important;}
body.ag-light .ag-domaine-card--bg .ag-domaine-card__more{color:#7B2D3B !important;text-shadow:none !important;}

/* ─── DOMAINES cards Business : même balayage que Boutique ─── */
body.ag-tier-business .ag-domaine-card{
    position:relative !important;
    overflow:hidden !important;
    transition:transform .4s ease,box-shadow .5s ease,border-color .4s ease,background .4s ease !important;
}
body.ag-tier-business .ag-domaine-card::before{
    content:"" !important;
    position:absolute !important;
    top:0 !important;left:0 !important;
    width:60% !important;height:100% !important;
    transform:translateX(-100%) skewX(-15deg) !important;
    pointer-events:none !important;z-index:1 !important;
}
body.ag-tier-business .ag-domaine-card:hover::before{animation:agGoldSweep 1.6s ease-in-out infinite !important;}
body.ag-tier-business .ag-domaine-card>*{position:relative !important;z-index:2 !important;}
body.ag-tier-business .ag-domaine-card:hover{transform:translateY(-8px) !important;}
body.ag-tier-business .ag-domaine-card .ag-domaine-card__icon{transition:transform .4s ease !important;}
body.ag-tier-business .ag-domaine-card:hover .ag-domaine-card__icon{transform:scale(1.18) rotate(-5deg) !important;}

/* Mode NUIT : domaines bordeaux */
body.ag-tier-business:not(.ag-light) .ag-domaine-card::before{
    background:linear-gradient(110deg,transparent 0%,rgba(123,45,59,0) 30%,rgba(160,61,77,.45) 50%,rgba(123,45,59,0) 70%,transparent 100%) !important;
}
body.ag-tier-business:not(.ag-light) .ag-domaine-card:hover{
    border-color:#A03D4D !important;
    background:linear-gradient(135deg,rgba(123,45,59,.08) 0%,#131826 100%) !important;
    box-shadow:0 30px 60px rgba(0,0,0,.55),0 0 50px rgba(123,45,59,.5) !important;
}
body.ag-tier-business:not(.ag-light) .ag-domaine-card:hover .ag-domaine-card__title{color:#E07585 !important;text-shadow:0 0 14px rgba(160,61,77,.5) !important;}
body.ag-tier-business:not(.ag-light) .ag-domaine-card:hover .ag-domaine-card__more{color:#E07585 !important;}

/* Mode JOUR : domaines doré */
body.ag-tier-business.ag-light .ag-domaine-card::before{
    background:linear-gradient(110deg,transparent 0%,rgba(212,180,92,0) 30%,rgba(212,180,92,.5) 50%,rgba(212,180,92,0) 70%,transparent 100%) !important;
}
body.ag-tier-business.ag-light .ag-domaine-card:hover{
    border-color:#D4B45C !important;
    background:linear-gradient(135deg,rgba(212,180,92,.1) 0%,#fff 100%) !important;
    box-shadow:0 30px 60px rgba(0,0,0,.15),0 0 50px rgba(212,180,92,.45) !important;
}
body.ag-tier-business.ag-light .ag-domaine-card:hover .ag-domaine-card__title{color:#B8941F !important;text-shadow:0 0 14px rgba(212,180,92,.5) !important;}
body.ag-tier-business.ag-light .ag-domaine-card:hover .ag-domaine-card__more{color:#B8941F !important;}
body.ag-tier-business .ag-domaine-card__title,
body.ag-tier-business .ag-domaine-card__more{transition:color .35s ease,text-shadow .35s ease !important;}

/* ─── BOUTIQUE cards ─── */
.ag-boutique-card{position:relative !important;overflow:hidden !important;transition:transform .4s ease,box-shadow .5s ease,border-color .4s ease,background .4s ease !important;}
.ag-boutique-card::before{
    content:"" !important;
    position:absolute !important;
    top:0 !important;
    left:0 !important;
    width:60% !important;
    height:100% !important;
    transform:translateX(-100%) skewX(-15deg) !important;
    pointer-events:none !important;
    z-index:1 !important;
}
.ag-boutique-card:hover::before{animation:agGoldSweep 1.6s ease-in-out infinite !important;}
.ag-boutique-card>*{position:relative !important;z-index:2 !important;}
.ag-boutique-card__icon,.ag-boutique-card__title,.ag-boutique-card__price{transition:color .35s ease,transform .4s ease,text-shadow .35s ease !important;}
.ag-boutique-card:hover{transform:translateY(-8px) !important;}
.ag-boutique-card:hover .ag-boutique-card__icon{transform:scale(1.15) rotate(-5deg) !important;}

/* Mode NUIT : balayage + bordure + texte BORDEAUX */
body:not(.ag-light) .ag-boutique-card::before{
    background:linear-gradient(110deg,transparent 0%,rgba(123,45,59,0) 30%,rgba(160,61,77,.45) 50%,rgba(123,45,59,0) 70%,transparent 100%) !important;
}
body:not(.ag-light) .ag-boutique-card:hover{
    border-color:#A03D4D !important;
    box-shadow:0 30px 60px rgba(0,0,0,.55),0 0 50px rgba(123,45,59,.55) !important;
    background:linear-gradient(135deg,rgba(123,45,59,.08) 0%,#131826 100%) !important;
}
body:not(.ag-light) .ag-boutique-card:hover .ag-boutique-card__title{color:#E07585 !important;text-shadow:0 0 18px rgba(160,61,77,.55) !important;}
body:not(.ag-light) .ag-boutique-card:hover .ag-boutique-card__price{color:#E07585 !important;text-shadow:0 0 25px rgba(160,61,77,.7) !important;}

/* Mode JOUR : balayage + bordure + texte DORE */
body.ag-light .ag-boutique-card::before{
    background:linear-gradient(110deg,transparent 0%,rgba(212,180,92,0) 30%,rgba(212,180,92,.45) 50%,rgba(212,180,92,0) 70%,transparent 100%) !important;
}
body.ag-light .ag-boutique-card:hover{
    border-color:#D4B45C !important;
    box-shadow:0 30px 60px rgba(0,0,0,.15),0 0 50px rgba(212,180,92,.45) !important;
    background:linear-gradient(135deg,rgba(212,180,92,.1) 0%,#fff 100%) !important;
}
body.ag-light .ag-boutique-card:hover .ag-boutique-card__title{color:#B8941F !important;text-shadow:0 0 18px rgba(212,180,92,.5) !important;}
body.ag-light .ag-boutique-card:hover .ag-boutique-card__price{color:#B8941F !important;text-shadow:0 0 25px rgba(212,180,92,.7) !important;}

/* ─── HONORAIRES cards (Business uniquement) ─── */
body.ag-tier-business .ag-honoraires__card{
    position:relative !important;
    overflow:hidden !important;
    transition:transform .4s ease,box-shadow .5s ease,border-color .4s ease,background .4s ease !important;
}
body.ag-tier-business .ag-honoraires__card::before{
    content:"" !important;
    position:absolute !important;
    top:0 !important;
    left:0 !important;
    width:60% !important;
    height:100% !important;
    transform:translateX(-100%) skewX(-15deg) !important;
    pointer-events:none !important;
    z-index:1 !important;
}
body.ag-tier-business .ag-honoraires__card:hover::before{animation:agGoldSweep 1.6s ease-in-out infinite !important;}
body.ag-tier-business .ag-honoraires__card>*{position:relative !important;z-index:2 !important;}
body.ag-tier-business .ag-honoraires__price,
body.ag-tier-business .ag-honoraires__label{transition:color .35s ease,text-shadow .35s ease !important;}
body.ag-tier-business .ag-honoraires__card:hover{transform:translateY(-8px) !important;}

/* Mode NUIT : balayage + bordure + texte BORDEAUX */
body.ag-tier-business:not(.ag-light) .ag-honoraires__card::before{
    background:linear-gradient(110deg,transparent 0%,rgba(123,45,59,0) 30%,rgba(160,61,77,.5) 50%,rgba(123,45,59,0) 70%,transparent 100%) !important;
}
body.ag-tier-business:not(.ag-light) .ag-honoraires__card:hover{
    border-color:#A03D4D !important;
    background:linear-gradient(135deg,rgba(123,45,59,.1) 0%,#131826 100%) !important;
    box-shadow:0 30px 60px rgba(0,0,0,.55),0 0 50px rgba(123,45,59,.55) !important;
}
body.ag-tier-business:not(.ag-light) .ag-honoraires__card:hover .ag-honoraires__price,
body.ag-tier-business:not(.ag-light) .ag-honoraires__card:hover .ag-honoraires__label{
    color:#E07585 !important;
    text-shadow:0 0 18px rgba(160,61,77,.55) !important;
}

/* Mode JOUR : balayage + bordure + texte DORE */
body.ag-tier-business.ag-light .ag-honoraires__card::before{
    background:linear-gradient(110deg,transparent 0%,rgba(212,180,92,0) 30%,rgba(212,180,92,.45) 50%,rgba(212,180,92,0) 70%,transparent 100%) !important;
}
body.ag-tier-business.ag-light .ag-honoraires__card:hover{
    border-color:#D4B45C !important;
    background:linear-gradient(135deg,rgba(212,180,92,.12) 0%,#fff 100%) !important;
    box-shadow:0 30px 60px rgba(0,0,0,.15),0 0 50px rgba(212,180,92,.45) !important;
}
body.ag-tier-business.ag-light .ag-honoraires__card:hover .ag-honoraires__price,
body.ag-tier-business.ag-light .ag-honoraires__card:hover .ag-honoraires__label{
    color:#B8941F !important;
    text-shadow:0 0 18px rgba(212,180,92,.5) !important;
}

/* Cartes Équipe : halo doré pulsant au hover */
.ag-team-card{transition:transform .4s ease,box-shadow .5s ease,border-color .4s ease !important;}
.ag-team-card:hover{
    transform:translateY(-8px) !important;
    border-color:rgba(212,180,92,.55) !important;
    box-shadow:0 25px 60px rgba(0,0,0,.55),0 0 35px rgba(212,180,92,.35) !important;
}
body.ag-light .ag-team-card:hover{
    border-color:rgba(123,45,59,.4) !important;
    box-shadow:0 25px 60px rgba(0,0,0,.12),0 0 30px rgba(123,45,59,.18) !important;
}

/* === Mode clair === */
body.ag-light .ag-section.ag-maitre{background:#EDE6DF !important;background-image:none !important;}
body.ag-light .ag-maitre__inner{background:#fff !important;border-color:rgba(123,45,59,.12) !important;box-shadow:0 10px 30px rgba(0,0,0,.08) !important;}
body.ag-light .ag-maitre__photo img{border-color:rgba(123,45,59,.2) !important;}
body.ag-light .ag-maitre__tag{background:rgba(123,45,59,.08) !important;color:#7B2D3B !important;border-color:rgba(123,45,59,.25) !important;}
body.ag-light .ag-maitre__name{color:#7B2D3B !important;}
body.ag-light .ag-maitre__meta{color:#5A4549 !important;}
body.ag-light .ag-maitre__bio{color:#2A1A1E !important;}
body.ag-light .ag-maitre__specialties{color:#5A4549 !important;}
body.ag-light .ag-maitre__specialties strong{color:#7B2D3B !important;}
';
        return $css;
    }
    // ─── PRO: Customizer ──────────────────────────────────────

    public function register_pro_customizer( $wp_customize ) {
        $wp_customize->add_section( 'ag_pro_features', array( 'title' => '⭐ Fonctionnalites Premium', 'priority' => 25 ) );

        $wp_customize->add_setting( 'ag_pro_skin', array( 'default' => 'navy-or', 'sanitize_callback' => 'sanitize_key' ) );
        $wp_customize->add_control( 'ag_pro_skin', array(
            'label'       => 'Apparence du site',
            'description' => 'Changez le design en un clic. Premium : 2 styles. Business : 4 styles.',
            'section'     => 'ag_pro_features',
            'type'        => 'select',
            'choices'     => self::get_available_skins( $this->tier ),
            'priority'    => 1,
        ) );

        $wp_customize->add_setting( 'ag_pro_sticky_header', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_pro_sticky_header', array( 'label' => 'Header sticky', 'section' => 'ag_pro_features', 'type' => 'checkbox' ) );

        $wp_customize->add_setting( 'ag_pro_animations', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_pro_animations', array( 'label' => 'Animations scroll', 'section' => 'ag_pro_features', 'type' => 'checkbox' ) );

        $wp_customize->add_setting( 'ag_pro_header_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_pro_header_phone', array( 'label' => 'Telephone header', 'section' => 'ag_pro_features', 'type' => 'text', 'description' => 'Bouton cliquable dans le header.' ) );

        // Testimonials
        $wp_customize->add_section( 'ag_pro_testimonials', array( 'title' => '⭐ Témoignages', 'priority' => 26 ) );
        for ( $i = 1; $i <= 6; $i++ ) {
            $wp_customize->add_setting( "ag_testimonial_{$i}_text", array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
            $wp_customize->add_control( "ag_testimonial_{$i}_text", array( 'label' => "Témoignage {$i}", 'section' => 'ag_pro_testimonials', 'type' => 'textarea' ) );
            $wp_customize->add_setting( "ag_testimonial_{$i}_author", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_testimonial_{$i}_author", array( 'label' => "Auteur {$i}", 'section' => 'ag_pro_testimonials', 'type' => 'text' ) );
        }
    }

    public function add_body_classes( $classes ) {
        $classes[] = 'ag-tier-' . $this->tier;
        if ( ! $this->is_at_least( 'premium' ) ) {
            return $classes;
        }
        $classes[] = 'ag-premium';
        $classes[] = 'ag-skin-' . get_theme_mod( 'ag_pro_skin', 'navy-or' );
        $skin = $this->get_skin();
        if ( ! empty( $skin['light'] ) ) $classes[] = 'ag-light';
        if ( ! has_custom_logo() ) $classes[] = 'ag-no-custom-logo';
        if ( get_theme_mod( 'ag_pro_animations', true ) ) $classes[] = 'ag-has-animations';
        return $classes;
    }

    // ─── Render: phone in header ──────────────────────────────

    public function render_header_phone() {
        if ( ! $this->is_at_least( 'premium' ) ) return '';
        $phone = get_theme_mod( 'ag_pro_header_phone', '' );
        if ( ! $phone ) return '';
        return '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '" class="ag-header__phone">📞 ' . esc_html( $phone ) . '</a>';
    }

    // ─── Render: testimonials ─────────────────────────────────

    public function render_testimonials() {
        if ( ! $this->is_at_least( 'premium' ) ) return;
        $items = array();
        for ( $i = 1; $i <= 6; $i++ ) {
            $t = get_theme_mod( "ag_testimonial_{$i}_text", '' );
            $a = get_theme_mod( "ag_testimonial_{$i}_author", '' );
            if ( $t && $a ) $items[] = array( 'text' => $t, 'author' => $a );
        }
        if ( empty( $items ) ) return;
        echo '<section class="ag-testimonials"><div class="ag-container">';
        echo '<h2 style="text-align:center;margin-bottom:48px;font-size:1.8rem;color:#fff;font-family:\'Playfair Display\',serif;">Ce que disent <em style="color:#D4B45C;font-style:italic;">nos clients</em></h2>';
        echo '<div class="ag-testimonials__grid">';
        foreach ( $items as $t ) {
            echo '<div class="ag-testimonial-card ag-fade-in"><div class="ag-testimonial-card__stars">★★★★★</div>';
            echo '<p class="ag-testimonial-card__text">"' . esc_html( $t['text'] ) . '"</p>';
            echo '<div class="ag-testimonial-card__author">' . esc_html( $t['author'] ) . '</div></div>';
        }
        echo '</div></div></section>';
    }

    // ═══════════════════════════════════════════════════════════
    // BUSINESS-ONLY FEATURES
    // ═══════════════════════════════════════════════════════════

    public function __construct_business() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        add_action( 'wp_footer', array( $this, 'render_mobile_call_button' ), 8 );
        add_action( 'wp_head', array( $this, 'render_schema_org' ), 99 );
        add_action( 'ag_after_hero', array( $this, 'render_breadcrumb' ) );
        // Business : alternance citations parallax + équipe + boutique
        add_action( 'ag_after_domaines',   array( $this, 'render_parallax_quote_1' ) );
        add_action( 'ag_after_maitre',     array( $this, 'render_team_section' ) );
        add_action( 'ag_after_honoraires', array( $this, 'render_parallax_quote_2' ) );
        add_action( 'ag_after_cabinet',    array( $this, 'render_boutique' ) );
        add_action( 'ag_after_cabinet',    array( $this, 'render_parallax_quote_3' ), 20 );
        // Signature dans la section Maître + logo SVG par défaut
        add_action( 'ag_inside_maitre_body', array( $this, 'render_maitre_signature' ) );
        add_action( 'ag_brand_fallback',     array( $this, 'render_default_logo_svg' ) );
        // Fonts business (Cormorant Garamond + Allison signature)
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_business_fonts' ), 11 );
        // Customizer fields pour équipe (4 collaborateurs)
        add_action( 'customize_register',  array( $this, 'register_business_customizer' ), 30 );
    }

    public function enqueue_business_fonts() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        wp_enqueue_style( 'ag-business-fonts',
            'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Allison&display=swap',
            array(), '1.0'
        );
    }

    public function render_default_logo_svg() {
        // Logo balance de justice — silhouette pleine + dégradé or + halo
        // Visibilité maximale même à 30px : remplissages solides, pas de
        // traits fins, halo de fond pour ressortir sur n'importe quel bg.
        if ( ! $this->is_at_least( 'business' ) ) return;
        if ( has_custom_logo() ) return;
        ?>
        <span class="ag-default-logo-svg" aria-hidden="true">
            <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="agLogoGold" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#FFE5A0"/>
                        <stop offset="50%" stop-color="#D4B45C"/>
                        <stop offset="100%" stop-color="#9A7A2E"/>
                    </linearGradient>
                    <radialGradient id="agLogoHalo" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#FFE5A0" stop-opacity=".35"/>
                        <stop offset="60%" stop-color="#D4B45C" stop-opacity=".12"/>
                        <stop offset="100%" stop-color="#D4B45C" stop-opacity="0"/>
                    </radialGradient>
                </defs>
                <!-- Halo lumineux de fond -->
                <circle cx="32" cy="32" r="31" fill="url(#agLogoHalo)"/>
                <!-- Finial sphere top -->
                <circle cx="32" cy="6" r="3.5" fill="url(#agLogoGold)"/>
                <!-- Pillar (rectangle plein) -->
                <rect x="30" y="9" width="4" height="42" fill="url(#agLogoGold)" rx="1"/>
                <!-- Beam (rectangle plein) -->
                <rect x="9" y="14" width="46" height="3.5" fill="url(#agLogoGold)" rx="1.5"/>
                <!-- Beam end knobs -->
                <circle cx="10" cy="15.7" r="3" fill="url(#agLogoGold)"/>
                <circle cx="54" cy="15.7" r="3" fill="url(#agLogoGold)"/>
                <!-- Left chain -->
                <rect x="9" y="18" width="2" height="6" fill="url(#agLogoGold)"/>
                <!-- Right chain -->
                <rect x="53" y="18" width="2" height="6" fill="url(#agLogoGold)"/>
                <!-- Left pan (filled triangle) -->
                <path d="M2 24 L18 24 L10 32 Z" fill="url(#agLogoGold)"/>
                <!-- Right pan (filled triangle) -->
                <path d="M46 24 L62 24 L54 32 Z" fill="url(#agLogoGold)"/>
                <!-- Base trapezoid (filled) -->
                <path d="M22 51 L42 51 L46 58 L18 58 Z" fill="url(#agLogoGold)"/>
            </svg>
        </span>
        <?php
    }

    public function render_maitre_signature() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        $sig_url = get_theme_mod( 'ag_business_maitre_signature', '' );
        $name    = ag_starter_avocat_get_option( 'ag_maitre_name' );
        echo '<div class="ag-maitre__signature">';
        if ( $sig_url ) {
            echo '<img src="' . esc_url( $sig_url ) . '" alt="' . esc_attr__( 'Signature', 'ag-starter-avocat' ) . '" loading="lazy">';
        } elseif ( $name ) {
            echo '<span class="ag-maitre__signature-text">' . esc_html( str_replace( array( '[', ']' ), '', $name ) ) . '</span>';
        }
        echo '</div>';
    }

    // ─── Citations parallax (style alliance-groupe) ─────────────

    private function render_parallax_quote( $bg_url, $quote, $author ) {
        if ( ! $this->is_at_least( 'business' ) ) return;
        echo '<section class="ag-parallax ag-parallax-business" style="background-image:url(\'' . esc_url( $bg_url ) . '\');">';
        echo '<div class="ag-parallax__overlay"></div>';
        echo '<div class="ag-parallax__content">';
        echo '<p class="ag-parallax__quote">' . esc_html( $quote ) . '</p>';
        if ( $author ) {
            echo '<p class="ag-parallax__caption">— ' . esc_html( $author ) . '</p>';
        }
        echo '</div></section>';
    }

    public function render_parallax_quote_1() {
        $this->render_parallax_quote(
            'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1920&q=80',
            'La justice sans la force est impuissante. La force sans la justice est tyrannique.',
            'Blaise Pascal'
        );
    }

    public function render_parallax_quote_2() {
        $this->render_parallax_quote(
            'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1920&q=80',
            'Le droit est l\'art du bon et de l\'équitable.',
            'Celse, jurisconsulte romain'
        );
    }

    public function render_parallax_quote_3() {
        $this->render_parallax_quote(
            'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1920&q=80',
            'L\'avocat est la dernière conscience de la liberté.',
            'Robert Badinter'
        );
    }

    // ─── Section Équipe (collaborateurs) ────────────────────────

    public function get_cabinet_url() {
        // URL "Le cabinet" : page slug=cabinet, sinon front-page#ag-cabinet
        $page = get_page_by_path( 'cabinet' );
        if ( $page ) return get_permalink( $page );
        return home_url( '/#ag-cabinet' );
    }

    public function render_team_section() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        if ( ! get_theme_mod( 'ag_business_team_show', true ) ) return;
        $members = array();
        for ( $i = 1; $i <= 4; $i++ ) {
            $name  = get_theme_mod( "ag_business_team_{$i}_name", '' );
            $role  = get_theme_mod( "ag_business_team_{$i}_role", '' );
            $photo = get_theme_mod( "ag_business_team_{$i}_photo", '' );
            $bio   = get_theme_mod( "ag_business_team_{$i}_bio", '' );
            if ( $name ) {
                $members[] = compact( 'name', 'role', 'photo', 'bio' );
            }
        }
        // Defaults si rien configuré
        if ( empty( $members ) ) {
            $members = array(
                array( 'name' => '[Maître Dupont]', 'role' => 'Associée fondatrice', 'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80', 'bio' => 'Spécialiste droit des affaires. 15 ans au Barreau de Paris.' ),
                array( 'name' => '[Maître Martin]', 'role' => 'Associé', 'photo' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=400&q=80', 'bio' => 'Droit du travail et contentieux social. Ancien magistrat.' ),
                array( 'name' => '[Me Lefebvre]', 'role' => 'Collaboratrice', 'photo' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&q=80', 'bio' => 'Droit de la famille et droit pénal. Médiatrice agréée.' ),
                array( 'name' => '[Me Bernard]', 'role' => 'Collaborateur', 'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80', 'bio' => 'Droit immobilier et fiscalité. Maîtrise patrimoniale.' ),
            );
        }
        $title    = get_theme_mod( 'ag_business_team_title', __( 'Notre équipe', 'ag-starter-avocat' ) );
        $subtitle = get_theme_mod( 'ag_business_team_subtitle', __( "Une équipe d'avocats expérimentés à votre service. Chaque dossier traité avec rigueur, écoute et discrétion.", 'ag-starter-avocat' ) );
        ?>
        <section class="ag-section ag-team" id="ag-team">
            <div class="ag-container">
                <h2 class="ag-section-title ag-shimmer-text"><?php echo esc_html( $title ); ?></h2>
                <p class="ag-section-lead"><?php echo esc_html( $subtitle ); ?></p>
                <div class="ag-team__grid">
                    <?php $cab_url = $this->get_cabinet_url(); foreach ( $members as $m ) : ?>
                        <a class="ag-team-card" href="<?php echo esc_url( $cab_url ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'En savoir plus sur %s — Le cabinet', 'ag-starter-avocat' ), $m['name'] ) ); ?>">
                            <?php if ( ! empty( $m['photo'] ) ) : ?>
                                <div class="ag-team-card__photo">
                                    <img src="<?php echo esc_url( $m['photo'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy">
                                </div>
                            <?php endif; ?>
                            <div class="ag-team-card__body">
                                <h3 class="ag-team-card__name"><?php echo esc_html( $m['name'] ); ?></h3>
                                <p class="ag-team-card__role"><?php echo esc_html( $m['role'] ); ?></p>
                                <?php if ( ! empty( $m['bio'] ) ) : ?>
                                    <p class="ag-team-card__bio"><?php echo esc_html( $m['bio'] ); ?></p>
                                <?php endif; ?>
                                <span class="ag-team-card__more"><?php esc_html_e( 'Voir le cabinet →', 'ag-starter-avocat' ); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    // ─── Section Boutique (3 produits, ready WooCommerce) ───────

    public function render_boutique() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        if ( ! get_theme_mod( 'ag_business_shop_show', true ) ) return;

        $products       = array();
        $is_woocommerce = class_exists( 'WooCommerce' ) && function_exists( 'wc_get_products' );
        $cta_label      = __( 'Acheter →', 'ag-starter-avocat' );
        $shop_url       = '';

        // Source 1 : WooCommerce — récupère les 3 derniers produits publiés
        if ( $is_woocommerce ) {
            $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
            $wc_query = wc_get_products( array(
                'limit'   => 3,
                'status'  => 'publish',
                'orderby' => 'date',
                'order'   => 'DESC',
            ) );
            foreach ( (array) $wc_query as $wc ) {
                if ( ! is_object( $wc ) ) continue;
                $img_id  = $wc->get_image_id();
                $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
                $excerpt = $wc->get_short_description();
                if ( '' === $excerpt ) $excerpt = wp_trim_words( wp_strip_all_tags( $wc->get_description() ), 22 );
                $products[] = array(
                    'icon'  => '',
                    'image' => $img_url,
                    'title' => $wc->get_name(),
                    'price' => wp_strip_all_tags( $wc->get_price_html() ),
                    'desc'  => wp_strip_all_tags( $excerpt ),
                    'url'   => get_permalink( $wc->get_id() ),
                );
            }
        }

        // Source 2 : Customizer fallback (si pas de WC ou aucun produit)
        if ( empty( $products ) ) {
            $products = array(
                array(
                    'icon'  => '📞',
                    'image' => '',
                    'title' => get_theme_mod( 'ag_business_shop_1_title', '3 consultations téléphoniques' ),
                    'price' => get_theme_mod( 'ag_business_shop_1_price', '450 €' ),
                    'desc'  => get_theme_mod( 'ag_business_shop_1_desc', 'Pack de 3 consultations de 30 min, valables 6 mois. Conseil juridique sur tout domaine.' ),
                    'url'   => get_theme_mod( 'ag_business_shop_1_url', '#' ),
                ),
                array(
                    'icon'  => '📚',
                    'image' => '',
                    'title' => get_theme_mod( 'ag_business_shop_2_title', 'Guide juridique PDF' ),
                    'price' => get_theme_mod( 'ag_business_shop_2_price', '29 €' ),
                    'desc'  => get_theme_mod( 'ag_business_shop_2_desc', "Manuel pratique 80 pages : vos droits face au licenciement, à la séparation, aux litiges courants." ),
                    'url'   => get_theme_mod( 'ag_business_shop_2_url', '#' ),
                ),
                array(
                    'icon'  => '✍️',
                    'image' => '',
                    'title' => get_theme_mod( 'ag_business_shop_3_title', 'Audit contractuel' ),
                    'price' => get_theme_mod( 'ag_business_shop_3_price', '290 €' ),
                    'desc'  => get_theme_mod( 'ag_business_shop_3_desc', "Analyse complète d'un contrat (CDI, bail, partenariat) avec rapport écrit et recommandations." ),
                    'url'   => get_theme_mod( 'ag_business_shop_3_url', '#' ),
                ),
            );
        }

        $title    = get_theme_mod( 'ag_business_shop_title', __( 'Nos services à la carte', 'ag-starter-avocat' ) );
        $subtitle = get_theme_mod( 'ag_business_shop_subtitle', __( 'Achetez directement en ligne. Paiement sécurisé, livraison immédiate par email.', 'ag-starter-avocat' ) );

        // Inline <style> + <script> pour garantir que l animation marche
        // meme si le CSS principal est en cache, surcharge ou bloque.
        ?>
        <style id="ag-boutique-stars-css">
        @keyframes agBoutiqueStar {
            0%   { transform: scale(0.2) rotate(0deg);   opacity: 0; }
            10%  { transform: scale(1) rotate(20deg);    opacity: 1; }
            30%  { transform: scale(2.5) rotate(60deg);  opacity: 1; }
            55%  { transform: scale(8) rotate(150deg);   opacity: 0.6; }
            85%  { transform: scale(20) rotate(280deg);  opacity: 0.12; }
            100% { transform: scale(35) rotate(360deg);  opacity: 0; }
        }
        .ag-boutique-stars-host { position: absolute !important; inset: 0; pointer-events: none; z-index: 1; overflow: hidden; }
        .ag-boutique-shooting-star {
            position: absolute;
            width: 90px; height: 90px;
            color: #D4B45C;
            transform: scale(0);
            opacity: 0;
            transform-origin: center;
            pointer-events: none;
            filter: drop-shadow(0 0 25px rgba(255,229,160,.95)) drop-shadow(0 0 70px rgba(212,180,92,.85));
            animation: agBoutiqueStar 5s ease-out infinite;
            will-change: transform, opacity;
        }
        .ag-boutique-shooting-star svg { width: 100%; height: 100%; display: block; fill: currentColor; }
        .ag-boutique-shooting-star.s1 { top: 25%; left: 50%; margin: -45px 0 0 -45px; animation-delay: 0s; }
        .ag-boutique-shooting-star.s2 { top: 50%; left: 22%; margin: -45px 0 0 -45px; animation-delay: 1.6s; }
        .ag-boutique-shooting-star.s3 { top: 45%; left: 78%; margin: -45px 0 0 -45px; animation-delay: 3.2s; }

        /* Mode JOUR : etoiles bordeaux (contraste sur fond creme) */
        body.ag-light .ag-boutique-shooting-star {
            color: #7B2D3B;
            filter: drop-shadow(0 0 22px rgba(160,61,77,.85)) drop-shadow(0 0 55px rgba(123,45,59,.65));
        }

        @media (prefers-reduced-motion: reduce) { .ag-boutique-shooting-star { display: none !important; } }
        </style>
        <section class="ag-section ag-boutique" id="ag-boutique">
            <div class="ag-boutique-stars-host" aria-hidden="true">
                <span class="ag-boutique-shooting-star s1"><svg viewBox="0 0 24 24"><polygon points="12,2 14.9,8.6 22,9.3 16.7,14.1 18.2,21 12,17.5 5.8,21 7.3,14.1 2,9.3 9.1,8.6"/></svg></span>
                <span class="ag-boutique-shooting-star s2"><svg viewBox="0 0 24 24"><polygon points="12,2 14.9,8.6 22,9.3 16.7,14.1 18.2,21 12,17.5 5.8,21 7.3,14.1 2,9.3 9.1,8.6"/></svg></span>
                <span class="ag-boutique-shooting-star s3"><svg viewBox="0 0 24 24"><polygon points="12,2 14.9,8.6 22,9.3 16.7,14.1 18.2,21 12,17.5 5.8,21 7.3,14.1 2,9.3 9.1,8.6"/></svg></span>
            </div>
            <script>
            (function(){
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                if (typeof Element === 'undefined' || !Element.prototype.animate) return;
                var run = function(){
                    var stars = document.querySelectorAll('.ag-boutique-shooting-star');
                    if (!stars.length) return;
                    var keyframes = [
                        { transform: 'scale(0.2) rotate(0deg)',   opacity: 0 },
                        { transform: 'scale(1) rotate(20deg)',    opacity: 1, offset: 0.10 },
                        { transform: 'scale(2.5) rotate(60deg)',  opacity: 1, offset: 0.30 },
                        { transform: 'scale(8) rotate(150deg)',   opacity: 0.6, offset: 0.55 },
                        { transform: 'scale(20) rotate(280deg)',  opacity: 0.12, offset: 0.85 },
                        { transform: 'scale(35) rotate(360deg)',  opacity: 0 }
                    ];
                    var opts = { duration: 5000, easing: 'ease-out', iterations: Infinity };
                    Array.prototype.forEach.call(stars, function(star, i){
                        var delay = i * 1600;
                        setTimeout(function(){ star.animate(keyframes, opts); }, delay);
                    });
                };
                if (document.readyState !== 'loading') run();
                else document.addEventListener('DOMContentLoaded', run);
            })();
            </script>
            <div class="ag-container">
                <span class="ag-tag ag-tag--clean">BOUTIQUE</span>
                <h2 class="ag-section-title ag-boutique__title-xl"><?php echo esc_html( $title ); ?></h2>
                <p class="ag-section-lead"><?php echo esc_html( $subtitle ); ?></p>
                <div class="ag-boutique__grid">
                    <?php foreach ( $products as $p ) : ?>
                        <article class="ag-boutique-card<?php echo ! empty( $p['image'] ) ? ' ag-boutique-card--with-image' : ''; ?>">
                            <?php if ( ! empty( $p['image'] ) ) : ?>
                                <div class="ag-boutique-card__image"><img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $p['title'] ); ?>" loading="lazy"></div>
                            <?php elseif ( ! empty( $p['icon'] ) ) : ?>
                                <div class="ag-boutique-card__icon"><?php echo esc_html( $p['icon'] ); ?></div>
                            <?php endif; ?>
                            <h3 class="ag-boutique-card__title"><?php echo esc_html( $p['title'] ); ?></h3>
                            <div class="ag-boutique-card__price"><?php echo esc_html( $p['price'] ); ?></div>
                            <p class="ag-boutique-card__desc"><?php echo esc_html( $p['desc'] ); ?></p>
                            <a href="<?php echo esc_url( $p['url'] ); ?>" class="ag-btn ag-boutique-card__btn"><?php echo esc_html( $cta_label ); ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if ( $is_woocommerce && $shop_url ) : ?>
                    <p class="ag-boutique__more">
                        <a href="<?php echo esc_url( $shop_url ); ?>" class="ag-boutique__more-link"><?php esc_html_e( 'Voir tous les produits de la boutique →', 'ag-starter-avocat' ); ?></a>
                    </p>
                <?php endif; ?>
                <p class="ag-boutique__note"><?php
                    if ( $is_woocommerce ) {
                        esc_html_e( '🔒 Paiement sécurisé · Boutique WooCommerce', 'ag-starter-avocat' );
                    } else {
                        esc_html_e( '🔒 Installez WooCommerce pour activer la boutique automatique', 'ag-starter-avocat' );
                    }
                ?></p>
            </div>
        </section>
        <?php
    }

    // ─── Customizer Business (équipe + boutique) ────────────────

    public function register_business_customizer( $wp_customize ) {
        if ( ! $this->is_at_least( 'business' ) ) return;

        // Section Équipe
        $wp_customize->add_section( 'ag_business_team', array(
            'title'    => '👥 Équipe (Business)',
            'priority' => 27,
        ) );
        $wp_customize->add_setting( 'ag_business_team_show', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_business_team_show', array( 'type' => 'checkbox', 'label' => 'Afficher la section Équipe', 'section' => 'ag_business_team' ) );
        $wp_customize->add_setting( 'ag_business_team_title', array( 'default' => 'Notre équipe', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_business_team_title', array( 'type' => 'text', 'label' => 'Titre', 'section' => 'ag_business_team' ) );
        $wp_customize->add_setting( 'ag_business_team_subtitle', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_business_team_subtitle', array( 'type' => 'textarea', 'label' => 'Sous-titre', 'section' => 'ag_business_team' ) );
        for ( $i = 1; $i <= 4; $i++ ) {
            $wp_customize->add_setting( "ag_business_team_{$i}_name", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_business_team_{$i}_name", array( 'type' => 'text', 'label' => "Membre {$i} — Nom", 'section' => 'ag_business_team' ) );
            $wp_customize->add_setting( "ag_business_team_{$i}_role", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_business_team_{$i}_role", array( 'type' => 'text', 'label' => "Membre {$i} — Rôle", 'section' => 'ag_business_team' ) );
            $wp_customize->add_setting( "ag_business_team_{$i}_photo", array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
            $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "ag_business_team_{$i}_photo", array( 'label' => "Membre {$i} — Photo", 'section' => 'ag_business_team' ) ) );
            $wp_customize->add_setting( "ag_business_team_{$i}_bio", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_business_team_{$i}_bio", array( 'type' => 'textarea', 'label' => "Membre {$i} — Bio courte", 'section' => 'ag_business_team' ) );
        }

        // Section Signature Maître (Business uniquement)
        $wp_customize->add_section( 'ag_business_maitre', array(
            'title'    => '✒️ Signature Maître (Business)',
            'priority' => 26,
        ) );
        $wp_customize->add_setting( 'ag_business_maitre_signature', array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'ag_business_maitre_signature', array(
            'label'       => 'Image de signature (PNG transparent recommandé)',
            'description' => 'Si vide, le nom du Maître est affiché en écriture cursive (police Allison).',
            'section'     => 'ag_business_maitre',
        ) ) );

        // Section Boutique
        $wp_customize->add_section( 'ag_business_shop', array(
            'title'       => '🛍 Boutique (Business)',
            'priority'    => 28,
            'description' => 'Si WooCommerce est installé et activé, les 3 derniers produits publiés s\'affichent automatiquement (image, prix, description courte). Les champs ci-dessous ne servent que de fallback statique si WooCommerce n\'est pas installé.',
        ) );
        $wp_customize->add_setting( 'ag_business_shop_show', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_business_shop_show', array( 'type' => 'checkbox', 'label' => 'Afficher la Boutique', 'section' => 'ag_business_shop' ) );
        $wp_customize->add_setting( 'ag_business_shop_title', array( 'default' => 'Nos services à la carte', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_business_shop_title', array( 'type' => 'text', 'label' => 'Titre', 'section' => 'ag_business_shop' ) );
        $wp_customize->add_setting( 'ag_business_shop_subtitle', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_business_shop_subtitle', array( 'type' => 'textarea', 'label' => 'Sous-titre', 'section' => 'ag_business_shop' ) );
        for ( $i = 1; $i <= 3; $i++ ) {
            $wp_customize->add_setting( "ag_business_shop_{$i}_title", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_business_shop_{$i}_title", array( 'type' => 'text', 'label' => "Produit {$i} — Titre", 'section' => 'ag_business_shop' ) );
            $wp_customize->add_setting( "ag_business_shop_{$i}_price", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_business_shop_{$i}_price", array( 'type' => 'text', 'label' => "Produit {$i} — Prix (ex : 49 €)", 'section' => 'ag_business_shop' ) );
            $wp_customize->add_setting( "ag_business_shop_{$i}_desc", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_business_shop_{$i}_desc", array( 'type' => 'textarea', 'label' => "Produit {$i} — Description", 'section' => 'ag_business_shop' ) );
            $wp_customize->add_setting( "ag_business_shop_{$i}_url", array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
            $wp_customize->add_control( "ag_business_shop_{$i}_url", array( 'type' => 'url', 'label' => "Produit {$i} — Lien Stripe / WooCommerce", 'section' => 'ag_business_shop' ) );
        }
    }

    public function render_mobile_call_button() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        $phone = get_theme_mod( 'ag_pro_header_phone', '' );
        if ( ! $phone ) $phone = ag_starter_avocat_get_option( 'ag_cabinet_phone' );
        if ( ! $phone ) return;
        $clean = preg_replace( '/[^0-9+]/', '', $phone );
        ?>
        <a href="tel:<?php echo esc_attr( $clean ); ?>" class="ag-mobile-call" aria-label="<?php esc_attr_e( 'Appeler', 'ag-starter-avocat' ); ?>">
            <span class="ag-mobile-call__icon">📞</span>
            <span class="ag-mobile-call__text"><?php esc_html_e( 'Appeler', 'ag-starter-avocat' ); ?></span>
        </a>
        <style>
        .ag-mobile-call{display:none;}
        @media(max-width:768px){
            .ag-mobile-call{
                display:flex !important;position:fixed !important;bottom:0 !important;left:0 !important;right:0 !important;
                z-index:998 !important;background:#D4B45C !important;color:#080808 !important;
                text-decoration:none !important;font-weight:700 !important;font-size:1.1rem !important;
                padding:16px !important;justify-content:center !important;align-items:center !important;gap:8px !important;
                box-shadow:0 -4px 20px rgba(0,0,0,.3) !important;
            }
            .ag-mobile-call__icon{font-size:1.3rem;}
        }
        </style>
        <?php
    }

    public function render_counters() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        $counters = array(
            array( 'number' => '15+', 'label' => __( "Annees d'experience", 'ag-starter-avocat' ) ),
            array( 'number' => '500+', 'label' => __( 'Dossiers traites', 'ag-starter-avocat' ) ),
            array( 'number' => '98%', 'label' => __( 'Clients satisfaits', 'ag-starter-avocat' ) ),
            array( 'number' => '24/7', 'label' => __( 'Garde a vue', 'ag-starter-avocat' ) ),
        );
        echo '<section class="ag-section ag-counters"><div class="ag-container"><div class="ag-counters__grid">';
        foreach ( $counters as $c ) {
            echo '<div class="ag-counter"><span class="ag-counter__number">' . esc_html( $c['number'] ) . '</span>';
            echo '<span class="ag-counter__label">' . esc_html( $c['label'] ) . '</span></div>';
        }
        echo '</div></div></section>';
    }

    public function render_schema_org() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        if ( ! is_front_page() ) return;
        $name = get_bloginfo( 'name' );
        $phone = ag_starter_avocat_get_option( 'ag_cabinet_phone' );
        $email = ag_starter_avocat_get_option( 'ag_cabinet_email' );
        $address = ag_starter_avocat_get_option( 'ag_cabinet_address' );
        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Attorney',
            'name'     => $name,
            'url'      => home_url( '/' ),
            'telephone' => $phone,
            'email'    => $email,
            'address'  => array( '@type' => 'PostalAddress', 'streetAddress' => $address ),
            'priceRange' => '€€',
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }

    public function render_trust_badges() {
        if ( ! $this->is_at_least( 'business' ) ) return;
        $rpva = ag_starter_avocat_get_option( 'ag_cabinet_rpva' );
        echo '<div class="ag-trust-bar"><div class="ag-container ag-trust-bar__inner">';
        echo '<span class="ag-trust-badge">⚖️ ' . esc_html__( 'Barreau inscrit', 'ag-starter-avocat' ) . '</span>';
        if ( $rpva ) echo '<span class="ag-trust-badge">🔒 RPVA ' . esc_html( $rpva ) . '</span>';
        echo '<span class="ag-trust-badge">📋 ' . esc_html__( 'Convention d\'honoraires', 'ag-starter-avocat' ) . '</span>';
        echo '<span class="ag-trust-badge">🛡️ ' . esc_html__( 'Secret professionnel', 'ag-starter-avocat' ) . '</span>';
        echo '</div></div>';
    }

    // ═══════════════════════════════════════════════════════════
    // FOOTER BRANDING — never configurable by client
    // ═══════════════════════════════════════════════════════════

    public function render_footer_branding() {
        $url_templates = 'https://alliancegroupe-inc.com/templates-wordpress';
        $url_home      = 'https://alliancegroupe-inc.com';

        if ( 'business' === $this->tier ) {
            echo '<div style="text-align:center;padding:16px 24px;background:#060606;border-top:1px solid rgba(255,255,255,.04);">';
            echo '<p style="margin:0;color:rgba(255,255,255,.3);font-size:.75rem;">&copy; ' . esc_html( date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . ' — <a href="' . esc_url( $url_home ) . '" target="_blank" rel="noopener nofollow" style="color:rgba(255,255,255,.3);">Alliance Groupe</a></p>';
            echo '</div>';
            return;
        }

        if ( 'premium' === $this->tier ) {
            echo '<div style="text-align:center;padding:28px 24px;background:#060606;border-top:1px solid rgba(212,180,92,.1);">';
            echo '<style>.ag-prem-float{display:inline-block;animation:agFloat 2s ease-in-out infinite;font-size:1.2rem;}.ag-prem-float:nth-child(2){animation-delay:.4s}.ag-prem-float:nth-child(3){animation-delay:.8s}@keyframes agFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}</style>';
            echo '<div style="margin-bottom:12px;"><span class="ag-prem-float">⭐</span> <span class="ag-prem-float">💎</span> <span class="ag-prem-float">⭐</span></div>';
            echo '<a href="' . esc_url( $url_home ) . '" target="_blank" rel="noopener nofollow" style="display:inline-block;text-decoration:none;">';
            echo '<img src="https://alliancegroupe-inc.com/wp-content/uploads/2026/04/logo_site_alliance.jpg" alt="Alliance Groupe" style="height:48px;border-radius:8px;opacity:.7;transition:opacity .3s,transform .3s;" onmouseover="this.style.opacity=\'1\';this.style.transform=\'scale(1.05)\'" onmouseout="this.style.opacity=\'.7\';this.style.transform=\'scale(1)\'">';
            echo '</a>';
            echo '<p style="margin:10px 0 0;color:rgba(255,255,255,.3);font-size:.72rem;">Theme par <a href="' . esc_url( $url_home ) . '" target="_blank" rel="noopener nofollow" style="color:rgba(212,180,92,.5);text-decoration:none;">Alliance Groupe</a></p>';
            echo '</div>';
            return;
        }

        // FREE: big animated promo
        ?>
        <div style="background:#060606;border-top:3px solid #D4B45C;padding:60px 24px 40px;text-align:center;position:relative;overflow:hidden;">
            <style>
            @keyframes agPromoGlow{0%,100%{box-shadow:0 0 30px rgba(212,180,92,.1)}50%{box-shadow:0 0 60px rgba(212,180,92,.25)}}
            @keyframes agFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
            .ag-premiummo-emoji{display:inline-block;animation:agFloat 2s ease-in-out infinite;font-size:2rem;}
            .ag-premiummo-emoji:nth-child(2){animation-delay:.3s}
            .ag-premiummo-emoji:nth-child(3){animation-delay:.6s}
            .ag-premiummo-emoji:nth-child(4){animation-delay:.9s}
            .ag-premiummo-emoji:nth-child(5){animation-delay:1.2s}
            </style>
            <div style="max-width:480px;margin:0 auto;padding:44px 32px;background:linear-gradient(180deg,rgba(212,180,92,.08) 0%,#0a0a0f 100%);border:2px solid rgba(212,180,92,.35);border-radius:24px;animation:agPromoGlow 3s ease-in-out infinite;">
                <div style="margin-bottom:20px;"><span class="ag-premiummo-emoji">🚀</span> <span class="ag-premiummo-emoji">⭐</span> <span class="ag-premiummo-emoji">💎</span> <span class="ag-premiummo-emoji">✨</span> <span class="ag-premiummo-emoji">🏆</span></div>
                <img src="https://alliancegroupe-inc.com/wp-content/uploads/2026/04/logo_site_alliance.jpg" alt="Alliance Groupe" style="height:80px;border-radius:14px;margin-bottom:20px;border:2px solid rgba(212,180,92,.3);">
                <h3 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#fff;margin:0 0 8px;font-style:italic;">Alliance Groupe</h3>
                <p style="color:#D4B45C;font-size:1rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin:0 0 16px;">Agence Web & IA</p>
                <p style="color:rgba(255,255,255,.7);font-size:1rem;line-height:1.7;margin:0 0 8px;">Ce theme est offert par <strong style="color:#D4B45C;">Alliance Groupe</strong>.</p>
                <p style="color:rgba(255,255,255,.5);font-size:.9rem;font-style:italic;font-family:'Playfair Display',serif;margin:0 0 28px;">Nantes · Naples · Marrakech</p>
                <a href="<?php echo esc_url( $url_templates ); ?>" target="_blank" rel="noopener" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-weight:700;padding:16px 36px;border-radius:12px;text-decoration:none;font-size:1.05rem;box-shadow:0 4px 25px rgba(212,180,92,.3);">Decouvrir nos templates →</a>
                <p style="color:rgba(255,255,255,.3);font-size:.75rem;margin:20px 0 0;">Passez au <strong>Pack Premium</strong> pour reduire cette publicite</p>
            </div>
            <p style="color:rgba(255,255,255,.2);font-size:.7rem;margin:20px 0 0;">&copy; <?php echo esc_html( date('Y') ); ?> <?php bloginfo('name'); ?> — <a href="<?php echo esc_url( $url_home ); ?>" target="_blank" rel="noopener nofollow" style="color:rgba(255,255,255,.2);">Alliance Groupe</a></p>
        </div>
        <?php
    }
}
