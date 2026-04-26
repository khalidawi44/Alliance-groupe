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

        // Footer branding — always active (different per tier)
        add_action( 'wp_footer', array( $this, 'render_footer_branding' ), 5 );

        if ( 'free' === $this->tier ) return;

        // Pro+ features
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pro_assets' ) );
        add_action( 'customize_register', array( $this, 'register_pro_customizer' ), 20 );
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
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
            array(), '3.0.0', true );
        $skin = $this->get_skin();
        wp_localize_script( 'ag-premium-scripts', 'agSkin', array( 'accent' => $skin['accent'] ) );
        wp_add_inline_style( 'ag-starter-avocat-style', $this->get_pro_css() );
    }

    private function get_skin() {
        $skins = array(
            'navy-or' => array(
                'name'   => 'Classique — Navy & Or',
                'accent' => '#D4B45C', 'accent_hover' => '#c5a44e', 'accent_rgb' => '212,180,92',
                'bg'     => '#080808', 'bg2' => '#0a0a0f', 'bg_card' => '#14141c',
                'tier'   => 'premium',
            ),
            'bordeaux' => array(
                'name'   => 'Bordeaux — Mode Nuit',
                'accent' => '#D4B45C', 'accent_hover' => '#c5a44e', 'accent_rgb' => '212,180,92',
                'bg'     => '#0a0608', 'bg2' => '#0e090b', 'bg_card' => '#1a1216',
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
            'navy-or'  => array( 'name' => 'Classique — Navy & Or', 'tier' => 'premium', 'preview' => '#D4B45C' ),
            'bordeaux' => array( 'name' => 'Bordeaux — Mode Nuit', 'tier' => 'premium', 'preview' => '#D4B45C' ),
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
    margin-top:28px !important;padding-top:20px !important;
    border-top:1px solid rgba(255,255,255,.06) !important;text-align:center !important;
}
body.ag-visitor-light .ag-domaine-back{border-top-color:rgba(0,0,0,.06) !important;}
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
body.ag-visitor-light .ag-hero{background:linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-visitor-light .ag-hero::before{background:none !important;}
body.ag-visitor-light .ag-page-hero{background:linear-gradient(180deg,rgba(245,240,235,.2) 0%,rgba(245,240,235,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
body.ag-visitor-light .ag-rdv__field input,body.ag-visitor-light .ag-rdv__field select,body.ag-visitor-light .ag-rdv__field textarea{background:#fff !important;border-color:rgba(0,0,0,.12) !important;color:#2A1A1E !important;}
body.ag-visitor-light .ag-footer-bottom{color:#8A7478 !important;}
body.ag-visitor-light .ag-pagination .page-numbers{background:rgba(0,0,0,.03) !important;border-color:rgba(0,0,0,.08) !important;color:#5A4549 !important;}
body.ag-visitor-light .ag-primary-menu.open{background:rgba(245,240,235,.98) !important;}
body.ag-visitor-light .ag-theme-toggle{border-color:rgba(123,45,59,.3) !important;}

.ag-tag{
    display:inline-block !important;padding:6px 18px !important;
    background:rgba(' . $gold_rgb . ',.1) !important;color:' . $gold . ' !important;
    border:1px solid rgba(' . $gold_rgb . ',.25) !important;border-radius:100px !important;
    font-size:.85rem !important;font-weight:600 !important;
    letter-spacing:.5px !important;text-transform:uppercase !important;
}

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
        if ( $is_light ) {
            $css .= '
/* ═══ LIGHT MODE OVERRIDES ═══ */
/* Kill sidebar/widgets */
.ag-sidebar,.widget,.widget-area,#ag-sidebar{display:none !important;}
/* All body text */
body,.ag-hero__subtitle,.ag-section-lead,.ag-domaine-card__excerpt,.ag-honoraires__desc,.ag-maitre__meta,.ag-maitre__bio,.ag-maitre__specialties,.ag-cabinet__block p,.ag-rdv__field label,.ag-footer-col p,.ag-footer-col li,.ag-post-card__excerpt,.ag-testimonial-card__text,.ag-page-article .ag-entry-content,.ag-honoraires__note,.ag-entry-footer p,.ag-post-meta,.ag-404-text,.ag-page-hero__lead,.ag-domaine-hero-tag,.ag-domaine-examples__list li,.ag-domaine-cta p,.ag-rdv__rgpd label,.ag-rdv__legal{color:' . $text_soft . ' !important;}
/* All headings */
h1,h2,h3,h4,.ag-section-title,.ag-domaine-card__title,.ag-honoraires__label,.ag-maitre__name,.ag-hero__title,.ag-page-hero__title,.ag-testimonial-card__author,.ag-post-card__title a,.ag-entry-title,.ag-entry-title a,.ag-cabinet__block h3,.ag-footer-col h3,.ag-page-article .ag-entry-content h2,.ag-page-article .ag-entry-content h3,.ag-testimonials h2{color:' . $heading_color . ' !important;}
.ag-page-article .ag-entry-content,.ag-page-article .ag-entry-content p,.ag-page-article .ag-entry-content li{color:' . $text_soft . ' !important;}
/* Accent elements */
.ag-hero__title span,.ag-maitre__specialties strong,.ag-domaine-card__more,.ag-post-card__more,.ag-post-card__date,.ag-domaine-examples__title,.ag-domaine-examples__list li::before,.ag-domaine-back a,.ag-entry-content a,.ag-page-article .ag-entry-content a,.ag-entry-footer a,.ag-footer-col a,.ag-maitre__tag,.ag-honoraires__price{color:' . $gold . ' !important;}
/* Cards white */
.ag-domaine-card,.ag-honoraires__card,.ag-cabinet__block,.ag-rdv__form,.ag-testimonial-card,.ag-post-card,.ag-page-article,.ag-domaine-examples{background:#fff !important;border-color:rgba(0,0,0,.08) !important;box-shadow:0 4px 20px rgba(0,0,0,.06) !important;}
.ag-domaine-card:hover,.ag-honoraires__card:hover,.ag-post-card:hover{border-color:rgba(' . $gold_rgb . ',.3) !important;box-shadow:0 12px 30px rgba(0,0,0,.1) !important;}
/* Sections light bg */
.ag-section,.ag-section:nth-of-type(odd),.ag-domaines,.ag-honoraires{background:' . $bg . ' !important;border-top-color:rgba(' . $gold_rgb . ',.08) !important;background-image:none !important;}
.ag-section:nth-of-type(even){background:' . $bg2 . ' !important;background-image:none !important;}
.ag-section::before{background:linear-gradient(90deg,transparent,rgba(' . $gold_rgb . ',.2),transparent) !important;}
/* Hero light overlay */
.ag-hero{background:linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
.ag-hero::before{background:none !important;}
.ag-page-hero{background:linear-gradient(180deg,rgba(245,240,235,.2) 0%,rgba(245,240,235,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
/* Maitre */
.ag-maitre{background:' . $bg2 . ' !important;border-top:1px solid rgba(' . $gold_rgb . ',.08) !important;border-bottom:1px solid rgba(' . $gold_rgb . ',.08) !important;background-image:none !important;}
.ag-maitre__photo img{border-color:rgba(' . $gold_rgb . ',.2) !important;box-shadow:0 10px 30px rgba(0,0,0,.1) !important;}
.ag-maitre__tag{background:rgba(' . $gold_rgb . ',.1) !important;border-color:rgba(' . $gold_rgb . ',.25) !important;}
/* Cabinet */
.ag-cabinet,.ag-cabinet-map-section{background:' . $bg . ' !important;background-image:none !important;}
.ag-cabinet__block{background:' . $bg2 . ' !important;border-color:rgba(0,0,0,.06) !important;}
.ag-cabinet-full__map{box-shadow:0 10px 30px rgba(0,0,0,.1) !important;}
/* RDV */
.ag-rdv{background:' . $bg2 . ' !important;background-image:none !important;}
.ag-rdv__form{background:' . $bg . ' !important;}
.ag-rdv__field input,.ag-rdv__field select,.ag-rdv__field textarea{background:#fff !important;border:1px solid rgba(0,0,0,.12) !important;color:' . $heading_color . ' !important;}
.ag-rdv__field select option{background:#fff !important;color:' . $heading_color . ' !important;}
.ag-rdv__field input::placeholder,.ag-rdv__field textarea::placeholder{color:' . $text_muted . ' !important;}
.ag-rdv__field input:focus,.ag-rdv__field select:focus,.ag-rdv__field textarea:focus{border-color:' . $gold . ' !important;outline-color:' . $gold . ' !important;}
/* Nav */
.ag-primary-menu a{color:' . $text_soft . ' !important;}
.ag-primary-menu a:hover{color:' . $gold . ' !important;}
.ag-site-brand a{color:' . $gold . ' !important;}
.ag-menu-toggle span{background:' . $heading_color . ' !important;}
.ag-theme-toggle{border-color:rgba(' . $gold_rgb . ',.3) !important;}
.ag-primary-menu.open{background:rgba(245,240,235,.98) !important;}
.ag-primary-menu.open a{color:' . $heading_color . ' !important;}
/* Footer */
.ag-site-footer{background:' . $bg2 . ' !important;border-top-color:rgba(' . $gold_rgb . ',.15) !important;}
.ag-footer-bottom{color:' . $text_muted . ' !important;border-top-color:rgba(0,0,0,.06) !important;}
a.ag-footer-rdv,a.ag-footer-rdv:visited,a.ag-footer-rdv:hover{background:' . $gold . ' !important;color:#fff !important;}
/* Archive */
.ag-pagination .page-numbers{background:rgba(0,0,0,.03) !important;border-color:rgba(0,0,0,.08) !important;color:' . $text_soft . ' !important;}
.ag-no-results{color:' . $text_muted . ' !important;}
.ag-totop{color:#fff !important;}
.ag-testimonials{background:' . $bg2 . ' !important;}
.ag-post-card__thumb img{border-bottom:1px solid rgba(0,0,0,.05) !important;}
';
        }

        // Visitor toggle: mode jour (always last = highest priority)
        $css .= '
/* ── Visitor dark/light toggle — mode jour ── */
body.ag-visitor-light{background:#F5F0EB !important;color:#2A1A1E !important;}
body.ag-visitor-light h1,body.ag-visitor-light h2,body.ag-visitor-light h3,body.ag-visitor-light h4,body.ag-visitor-light .ag-section-title,body.ag-visitor-light .ag-page-hero__title,body.ag-visitor-light .ag-hero__title,body.ag-visitor-light .ag-honoraires__label,body.ag-visitor-light .ag-domaine-card__title,body.ag-visitor-light .ag-post-card__title a,body.ag-visitor-light .ag-page-article .ag-entry-content h2,body.ag-visitor-light .ag-page-article .ag-entry-content h3{color:#2A1A1E !important;}
body.ag-visitor-light .ag-hero__subtitle,body.ag-visitor-light .ag-section-lead,body.ag-visitor-light .ag-domaine-card__excerpt,body.ag-visitor-light .ag-honoraires__desc,body.ag-visitor-light .ag-cabinet__block p,body.ag-visitor-light .ag-rdv__field label,body.ag-visitor-light .ag-footer-col p,body.ag-visitor-light .ag-footer-col li,body.ag-visitor-light .ag-post-card__excerpt,body.ag-visitor-light .ag-testimonial-card__text,body.ag-visitor-light .ag-page-article .ag-entry-content,body.ag-visitor-light .ag-page-article .ag-entry-content p,body.ag-visitor-light .ag-page-article .ag-entry-content li,body.ag-visitor-light .ag-honoraires__note,body.ag-visitor-light .ag-rdv__legal,body.ag-visitor-light .ag-domaine-card__examples li{color:#2A1A1E !important;}
body.ag-visitor-light .ag-maitre__bio{color:#2A1A1E !important;}
body.ag-visitor-light .ag-maitre__meta{color:#5A4549 !important;}
body.ag-visitor-light .ag-maitre__name{color:#D4B45C !important;}
body.ag-visitor-light .ag-maitre__tag{background:rgba(212,180,92,.1) !important;border-color:rgba(212,180,92,.25) !important;color:#D4B45C !important;}
body.ag-visitor-light .ag-maitre__specialties strong{color:#D4B45C !important;}
body.ag-visitor-light .ag-rdv__rgpd label,body.ag-visitor-light .ag-rdv__rgpd span{color:#2A1A1E !important;}
body.ag-visitor-light .ag-rdv__rgpd{background:rgba(0,0,0,.03) !important;border-color:rgba(0,0,0,.1) !important;}
body.ag-visitor-light .ag-rdv__form{background:#fff !important;border-color:rgba(0,0,0,.08) !important;}
body.ag-visitor-light .ag-rdv__submit{color:#fff !important;}
body.ag-visitor-light .ag-rdv__legal{color:#5A4549 !important;}
body.ag-visitor-light .ag-rdv__field label{color:#2A1A1E !important;}
body.ag-visitor-light .ag-page-hero__title{color:#7B2D3B !important;}
body.ag-visitor-light .ag-page-hero__lead{color:#5A4549 !important;}
body.ag-visitor-light .ag-domaine-examples{background:#F5F0EB !important;}
body.ag-visitor-light .ag-domaine-examples__title{color:#2A1A1E !important;}
body.ag-visitor-light .ag-domaine-examples__list li{color:#2A1A1E !important;}
body.ag-visitor-light .ag-domaine-examples__list li::before{color:#7B2D3B !important;}
body.ag-visitor-light .ag-domaine-cta p{color:#5A4549 !important;}
body.ag-visitor-light .ag-domaine-back a{color:#7B2D3B !important;}
body.ag-visitor-light .ag-page-article .ag-entry-content{color:#2A1A1E !important;}
body.ag-visitor-light .ag-page-article .ag-entry-content p{color:#3A2A2E !important;}
body.ag-visitor-light .ag-page-article .ag-entry-thumb img{border-color:rgba(0,0,0,.08) !important;}
body.ag-visitor-light .ag-maitre__photo img{box-shadow:0 10px 30px rgba(0,0,0,.1) !important;border-color:rgba(123,45,59,.2) !important;}
body.ag-visitor-light .ag-hero__title span,body.ag-visitor-light .ag-domaine-card__more,body.ag-visitor-light .ag-post-card__more,body.ag-visitor-light .ag-post-card__date,body.ag-visitor-light .ag-honoraires__price,body.ag-visitor-light .ag-domaine-examples__title,body.ag-visitor-light .ag-domaine-examples__list li::before,body.ag-visitor-light .ag-domaine-back a,body.ag-visitor-light .ag-entry-content a,body.ag-visitor-light .ag-footer-col a,body.ag-visitor-light .ag-cabinet__block h3,body.ag-visitor-light .ag-footer-col h3{color:#7B2D3B !important;}
body.ag-visitor-light .ag-btn{background:#7B2D3B !important;color:#fff !important;box-shadow:0 4px 25px rgba(123,45,59,.25) !important;}
body.ag-visitor-light .ag-btn:hover{background:#6A2433 !important;}
body.ag-visitor-light a.ag-footer-rdv,body.ag-visitor-light a.ag-footer-rdv:visited{background:#7B2D3B !important;color:#fff !important;}
body.ag-visitor-light .ag-site-brand a{color:#7B2D3B !important;}
body.ag-visitor-light .ag-primary-menu a{color:#5A4549 !important;}
body.ag-visitor-light .ag-primary-menu a:hover{color:#7B2D3B !important;}
body.ag-visitor-light .ag-section::before{background:linear-gradient(90deg,transparent,rgba(123,45,59,.2),transparent) !important;}
body.ag-visitor-light .ag-domaine-card,body.ag-visitor-light .ag-honoraires__card,body.ag-visitor-light .ag-cabinet__block,body.ag-visitor-light .ag-rdv__form,body.ag-visitor-light .ag-testimonial-card,body.ag-visitor-light .ag-post-card,body.ag-visitor-light .ag-page-article,body.ag-visitor-light .ag-domaine-examples{background:#fff !important;border-color:rgba(0,0,0,.08) !important;box-shadow:0 4px 20px rgba(0,0,0,.06) !important;}
body.ag-visitor-light .ag-section,body.ag-visitor-light .ag-section:nth-of-type(odd),body.ag-visitor-light .ag-domaines,body.ag-visitor-light .ag-honoraires{background:#F5F0EB !important;background-image:none !important;}
body.ag-visitor-light .ag-section:nth-of-type(even),body.ag-visitor-light .ag-maitre,body.ag-visitor-light .ag-rdv,body.ag-visitor-light .ag-testimonials{background:#EDE6DF !important;background-image:none !important;}
body.ag-visitor-light .ag-site-footer{background:#EDE6DF !important;}
body.ag-visitor-light .ag-cabinet,body.ag-visitor-light .ag-cabinet-map-section{background:#F5F0EB !important;background-image:none !important;}
body.ag-visitor-light .ag-rdv-contact{background:linear-gradient(180deg,rgba(237,230,223,.9) 0%,rgba(237,230,223,.95) 100%),url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-visitor-light .ag-site-header.scrolled{background:rgba(245,240,235,.97) !important;}
body.ag-visitor-light .ag-menu-toggle span{background:#2A1A1E !important;}
body.ag-visitor-light .ag-hero{background:linear-gradient(180deg,rgba(245,240,235,.15) 0%,rgba(245,240,235,.93) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover no-repeat !important;background-attachment:fixed !important;}
body.ag-visitor-light .ag-hero::before{background:none !important;}
body.ag-visitor-light .ag-page-hero{background:linear-gradient(180deg,rgba(245,240,235,.2) 0%,rgba(245,240,235,.95) 100%),url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center 20%/cover !important;background-attachment:fixed !important;}
body.ag-visitor-light .ag-rdv__field input,body.ag-visitor-light .ag-rdv__field select,body.ag-visitor-light .ag-rdv__field textarea{background:#fff !important;border-color:rgba(0,0,0,.12) !important;color:#2A1A1E !important;}
body.ag-visitor-light .ag-footer-bottom{color:#8A7478 !important;}
body.ag-visitor-light .ag-primary-menu.open{background:rgba(245,240,235,.98) !important;}
body.ag-visitor-light .ag-theme-toggle{border-color:rgba(123,45,59,.3) !important;}
body.ag-visitor-light .ag-totop{background:#7B2D3B !important;color:#fff !important;}
body.ag-visitor-light .ag-domaine-card .ag-domaine-card__title{color:#2A1A1E !important;}
body.ag-visitor-light .ag-domaine-card__icon+.ag-domaine-card__title{color:#2A1A1E !important;}
';
        // Mode nuit: RGPD checkbox en blanc
        $css .= '
.ag-rdv__rgpd label,.ag-rdv__rgpd span{color:#fff !important;}
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
        $classes[] = 'ag-premium';
        $classes[] = 'ag-tier-' . $this->tier;
        $classes[] = 'ag-skin-' . get_theme_mod( 'ag_pro_skin', 'navy-or' );
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
