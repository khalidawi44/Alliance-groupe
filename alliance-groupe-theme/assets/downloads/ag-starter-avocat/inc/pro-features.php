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
            array(), '2.0.0', true );
        wp_add_inline_style( 'ag-starter-avocat-style', $this->get_pro_css() );
    }

    private function get_pro_css() {
        $gold = '#D4B45C';
        $gold_hover = '#c5a44e';
        $bg = '#080808';
        $bg2 = '#0a0a0f';
        $card_bg = 'rgba(255,255,255,.025)';
        $card_border = 'rgba(255,255,255,.06)';
        $ease = 'cubic-bezier(.23,1,.32,1)';
        $css = '
/* ═══ PREMIUM DESIGN — Alliance Groupe Style ═══ */
body{font-family:"Manrope",system-ui,sans-serif !important;background:' . $bg . ' !important;color:#e8e6e0 !important;line-height:1.7 !important;}
h1,h2,h3,h4{font-family:"Manrope",sans-serif !important;font-weight:800 !important;line-height:1.2 !important;}
em,.ag-gold{font-family:"Playfair Display",serif !important;font-style:italic !important;color:' . $gold . ' !important;}

/* ── Hero ── */
.ag-hero{
    min-height:90vh !important;
    display:flex !important;align-items:center !important;justify-content:center !important;
    text-align:center !important;position:relative !important;overflow:hidden !important;
    background:linear-gradient(180deg,rgba(8,8,8,.2) 0%,rgba(8,8,8,.92) 100%),
               url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center/cover no-repeat !important;
    background-attachment:fixed !important;
}
.ag-hero::before{
    content:"";position:absolute;inset:0;
    background:radial-gradient(ellipse 900px 600px at 85% 10%,rgba(212,180,92,.10),transparent 60%),
               radial-gradient(ellipse 700px 500px at 10% 95%,rgba(212,180,92,.06),transparent 60%);
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
    font-size:1.05rem !important;color:#b0b0bc !important;
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
    box-shadow:0 4px 25px rgba(212,180,92,.25) !important;
    transition:background .3s,transform .3s ' . $ease . ',box-shadow .3s !important;
}
.ag-btn:hover{background:' . $gold_hover . ' !important;transform:translateY(-2px) !important;box-shadow:0 8px 35px rgba(212,180,92,.35) !important;}

/* ── Sections ── */
.ag-section{padding:100px 0 !important;position:relative;overflow:hidden !important;}
.ag-section-title{
    font-size:clamp(1.5rem,3vw,2.2rem) !important;
    text-align:center !important;margin-bottom:12px !important;
    color:#fff !important;position:relative !important;display:inline-block !important;
    width:100% !important;
}
.ag-section-lead{
    text-align:center !important;color:#b0b0bc !important;
    font-size:.95rem !important;max-width:650px !important;
    margin:0 auto 48px !important;line-height:1.7 !important;
}
/* Section backgrounds Alliance-style */
.ag-section:nth-of-type(odd){
    background:radial-gradient(ellipse 900px 600px at 85% 10%,rgba(212,180,92,.10),transparent 60%),
               radial-gradient(ellipse 700px 500px at 10% 95%,rgba(212,180,92,.06),transparent 60%),' . $bg . ' !important;
    border-top:1px solid rgba(212,180,92,.12) !important;
}
.ag-section:nth-of-type(even){
    background:linear-gradient(135deg,#15151c 0%,#1c1c26 45%,#16161e 100%) !important;
    border-top:1px solid rgba(212,180,92,.14) !important;
}
/* Gold divider */
.ag-section::before{
    content:"" !important;position:absolute !important;top:0 !important;left:50% !important;
    transform:translateX(-50%) !important;width:min(280px,40%) !important;height:1px !important;
    background:linear-gradient(90deg,transparent,rgba(212,180,92,.55),transparent) !important;
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
    border-color:rgba(212,180,92,.25) !important;
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
    background:linear-gradient(135deg,rgba(212,180,92,.04) 0%,rgba(10,14,26,.8) 100%) !important;
    border-top:1px solid rgba(212,180,92,.1) !important;
    border-bottom:1px solid rgba(212,180,92,.1) !important;
}
.ag-maitre__inner{
    display:grid !important;
    grid-template-columns:300px 1fr !important;
    gap:48px !important;
    align-items:center !important;
}
.ag-maitre__photo img{
    border-radius:16px !important;
    border:2px solid rgba(212,180,92,.2) !important;
    box-shadow:0 20px 50px rgba(0,0,0,.4) !important;
}
.ag-maitre__tag{
    display:inline-block !important;
    padding:4px 14px !important;
    background:rgba(212,180,92,.1) !important;
    color:' . $gold . ' !important;
    border:1px solid rgba(212,180,92,.25) !important;
    border-radius:100px !important;
    font-size:.78rem !important;
    font-weight:600 !important;
    text-transform:uppercase !important;
    letter-spacing:1px !important;
    margin-bottom:12px !important;
}
.ag-maitre__name{font-size:2rem !important;font-style:italic !important;margin-bottom:8px !important;}
.ag-maitre__meta{color:rgba(255,255,255,.5) !important;font-size:.88rem !important;margin-bottom:20px !important;}
.ag-maitre__bio{color:rgba(255,255,255,.75) !important;line-height:1.8 !important;font-size:1rem !important;}
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
    border:1px solid rgba(212,180,92,.15) !important;
    border-radius:18px !important;
    padding:36px 28px !important;
    text-align:center !important;
    transition:transform .3s,border-color .3s !important;
}
.ag-honoraires__card:hover{
    transform:translateY(-4px) !important;
    border-color:rgba(212,180,92,.4) !important;
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
    background:linear-gradient(135deg,rgba(212,180,92,.04) 0%,rgba(10,14,26,.9) 100%) !important;
}
.ag-rdv__form{
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(212,180,92,.2) !important;
    border-radius:20px !important;
    padding:40px 36px !important;
}
.ag-rdv__row{display:grid !important;grid-template-columns:1fr 1fr !important;gap:20px !important;margin-bottom:20px !important;}
.ag-rdv__field label{display:block !important;color:rgba(255,255,255,.6) !important;font-size:.85rem !important;font-weight:600 !important;margin-bottom:6px !important;}
.ag-rdv__field input,.ag-rdv__field select,.ag-rdv__field textarea{
    width:100% !important;
    padding:14px 18px !important;
    background:rgba(10,14,26,.8) !important;
    border:1px solid rgba(212,180,92,.2) !important;
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
    border-top:1px solid rgba(212,180,92,.1) !important;
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
    background:linear-gradient(90deg,transparent 0%,rgba(212,180,92,.25) 50%,transparent 100%) !important;
    margin-bottom:80px !important;
}
.ag-section:first-of-type::before{display:none !important;}
.ag-domaines{
    background:linear-gradient(180deg,rgba(212,180,92,.03) 0%,rgba(10,14,26,1) 100%) !important;
}
.ag-honoraires{
    background:linear-gradient(180deg,rgba(10,14,26,1) 0%,rgba(212,180,92,.05) 50%,rgba(10,14,26,1) 100%) !important;
}
.ag-rdv{
    background:linear-gradient(180deg,rgba(10,14,26,.95) 0%,rgba(10,14,26,.8) 100%),
               url("https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1920&q=80") center/cover !important;
}

/* ── Footer RDV link ── */
.ag-footer-rdv{
    display:inline-block !important;margin-top:14px !important;
    background:' . $gold . ' !important;color:#0a0e1a !important;
    padding:10px 22px !important;border-radius:8px !important;
    font-weight:700 !important;font-size:.85rem !important;
    text-decoration:none !important;transition:transform .3s,box-shadow .3s !important;
}
.ag-footer-rdv:hover{transform:translateY(-2px) !important;box-shadow:0 6px 20px rgba(212,180,92,.3) !important;}

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
    background:rgba(10,14,26,.97) !important;
    backdrop-filter:blur(16px) !important;
    border-bottom-color:rgba(212,180,92,.1) !important;
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
.ag-testimonials{padding:80px 24px;background:rgba(212,180,92,.03);}
.ag-testimonials__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:1200px;margin:0 auto;}
.ag-testimonial-card{background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.15);border-radius:16px;padding:28px;}
.ag-testimonial-card__stars{color:' . $gold . ';font-size:1.1rem;margin-bottom:12px;letter-spacing:2px;}
.ag-testimonial-card__text{color:rgba(255,255,255,.8);font-size:.95rem;line-height:1.7;font-style:italic;margin-bottom:16px;}
.ag-testimonial-card__author{font-weight:700;color:#fff;font-size:.9rem;}
';

        // Page template styling
        $css .= '
html{scroll-behavior:smooth;}
/* Page hero — default (generic pages) */
.ag-page-hero{
    padding:140px 0 70px !important;
    text-align:center !important;
    background:linear-gradient(180deg,rgba(10,14,26,.3) 0%,rgba(10,14,26,.95) 100%),
               url("https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80") center/cover !important;
    border-bottom:1px solid rgba(212,180,92,.15) !important;
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
    border:1px solid rgba(212,180,92,.12) !important;
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
.ag-page-article .ag-entry-thumb img{border-radius:14px !important;border:1px solid rgba(212,180,92,.15) !important;}
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
    background:rgba(212,180,92,.04) !important;
    border:1px solid rgba(212,180,92,.15) !important;
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
    margin-top:32px !important;padding-top:20px !important;
    border-top:1px solid rgba(255,255,255,.06) !important;text-align:center !important;
}
.ag-domaine-back a{color:' . $gold . ' !important;text-decoration:none !important;font-weight:700 !important;}
.ag-domaine-back a:hover{text-decoration:underline !important;}

/* ── RDV extras ── */
.ag-rdv__status{padding:16px 20px !important;border-radius:10px !important;margin-bottom:24px !important;font-weight:600 !important;}
.ag-rdv__status--success{background:rgba(40,167,69,.1) !important;border:1px solid rgba(40,167,69,.3) !important;color:#28a745 !important;}
.ag-rdv__status--error{background:rgba(220,53,69,.1) !important;border:1px solid rgba(220,53,69,.3) !important;color:#dc3545 !important;}
.ag-rdv__rgpd{margin:20px 0 !important;}
.ag-rdv__rgpd label{display:flex !important;gap:10px !important;align-items:flex-start !important;color:rgba(255,255,255,.5) !important;font-size:.85rem !important;line-height:1.5 !important;cursor:pointer !important;}
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
    box-shadow:0 4px 20px rgba(212,180,92,.3) !important;
    opacity:0 !important;pointer-events:none !important;
    transition:opacity .4s,transform .3s ' . $ease . ' !important;
    z-index:999 !important;
}
.ag-totop.visible{opacity:1 !important;pointer-events:auto !important;}
.ag-totop:hover{transform:translateY(-3px) !important;}

/* ── Tag pill (like Alliance) ── */
.ag-tag{
    display:inline-block !important;padding:6px 18px !important;
    background:rgba(212,180,92,.1) !important;color:' . $gold . ' !important;
    border:1px solid rgba(212,180,92,.25) !important;border-radius:100px !important;
    font-size:.85rem !important;font-weight:600 !important;
    letter-spacing:.5px !important;text-transform:uppercase !important;
}

/* ── Page Cabinet full layout ── */
.ag-cabinet-full{display:grid !important;grid-template-columns:1.2fr 1fr !important;gap:32px !important;align-items:start !important;}
.ag-cabinet-full__map{border-radius:16px !important;overflow:hidden !important;box-shadow:0 20px 60px rgba(0,0,0,.4) !important;border:1px solid rgba(212,180,92,.15) !important;}
.ag-cabinet-full__map iframe{display:block !important;}
.ag-cabinet-full__cards{display:flex !important;flex-direction:column !important;gap:16px !important;}
.ag-cabinet__block-icon{font-size:1.5rem !important;margin-bottom:8px !important;}
.ag-cabinet-full__btn{width:100% !important;justify-content:center !important;margin-top:8px !important;}
@media(max-width:768px){.ag-cabinet-full{grid-template-columns:1fr !important;}}
.ag-cabinet-map-section{background:linear-gradient(180deg,rgba(10,14,26,1) 0%,rgba(212,180,92,.04) 100%) !important;}

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
    border:1px solid rgba(212,180,92,.12) !important;
    border-radius:16px !important;
    overflow:hidden !important;
    transition:transform .35s,border-color .35s,box-shadow .35s !important;
    display:flex !important;
    flex-direction:column !important;
}
.ag-post-card:hover{
    transform:translateY(-5px) !important;
    border-color:rgba(212,180,92,.35) !important;
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
        return $css;
    }

    // ─── PRO: Customizer ──────────────────────────────────────

    public function register_pro_customizer( $wp_customize ) {
        $wp_customize->add_section( 'ag_pro_features', array( 'title' => '⭐ Fonctionnalités Premium', 'priority' => 25 ) );

        $wp_customize->add_setting( 'ag_pro_sticky_header', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_pro_sticky_header', array( 'label' => 'Header sticky', 'section' => 'ag_pro_features', 'type' => 'checkbox' ) );

        $wp_customize->add_setting( 'ag_pro_animations', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_pro_animations', array( 'label' => 'Animations scroll', 'section' => 'ag_pro_features', 'type' => 'checkbox' ) );

        $wp_customize->add_setting( 'ag_pro_accent_secondary', array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ag_pro_accent_secondary', array( 'label' => 'Couleur accent secondaire', 'section' => 'ag_pro_features' ) ) );

        $wp_customize->add_setting( 'ag_pro_header_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_pro_header_phone', array( 'label' => 'Téléphone header', 'section' => 'ag_pro_features', 'type' => 'text', 'description' => 'Bouton doré cliquable dans le header.' ) );

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
            echo '<div style="text-align:center;padding:16px 24px;background:#060606;border-top:1px solid rgba(255,255,255,.06);">';
            echo '<a href="' . esc_url( $url_home ) . '" target="_blank" rel="noopener nofollow" style="display:inline-block;text-decoration:none;">';
            echo '<img src="https://alliancegroupe-inc.com/wp-content/uploads/2026/04/logo_site_alliance.jpg" alt="Alliance Groupe" style="height:28px;border-radius:4px;opacity:.6;transition:opacity .3s;" onmouseover="this.style.opacity=\'1\'" onmouseout="this.style.opacity=\'.6\'">';
            echo '</a></div>';
            return;
        }

        // FREE: big animated promo
        ?>
        <div style="background:#060606;border-top:2px solid #D4B45C;padding:48px 24px;text-align:center;">
            <div style="max-width:400px;margin:0 auto;padding:40px 28px;background:linear-gradient(180deg,rgba(212,180,92,.08) 0%,#0a0a0f 100%);border:1px solid rgba(212,180,92,.35);border-radius:20px;overflow:hidden;animation:agPromoGlow 3s ease-in-out infinite;">
                <style>
                @keyframes agPromoGlow{0%,100%{box-shadow:0 0 30px rgba(212,180,92,.1)}50%{box-shadow:0 0 50px rgba(212,180,92,.25)}}
                @keyframes agFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
                .ag-premiummo-emoji{display:inline-block;animation:agFloat 2s ease-in-out infinite;font-size:1.6rem;}
                .ag-premiummo-emoji:nth-child(2){animation-delay:.3s}
                .ag-premiummo-emoji:nth-child(3){animation-delay:.6s}
                </style>
                <div style="margin-bottom:16px;"><span class="ag-premiummo-emoji">🚀</span> <span class="ag-premiummo-emoji">⭐</span> <span class="ag-premiummo-emoji">💎</span></div>
                <img src="https://alliancegroupe-inc.com/wp-content/uploads/2026/04/logo_site_alliance.jpg" alt="Alliance Groupe" style="height:56px;border-radius:10px;margin-bottom:16px;">
                <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#fff;margin:0 0 8px;font-style:italic;">Alliance Groupe</h3>
                <p style="color:#D4B45C;font-size:.88rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin:0 0 12px;">Agence Web & IA</p>
                <p style="color:rgba(255,255,255,.7);font-size:.92rem;line-height:1.6;margin:0 0 24px;">Ce thème gratuit est offert par Alliance Groupe.<br>Créez votre site professionnel en 5 minutes.</p>
                <a href="<?php echo esc_url( $url_templates ); ?>" target="_blank" rel="noopener" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-weight:700;padding:14px 32px;border-radius:10px;text-decoration:none;font-size:1rem;">Découvrir nos templates →</a>
                <p style="color:rgba(255,255,255,.3);font-size:.72rem;margin:16px 0 0;">Passez au Pack Premium pour réduire cette publicité</p>
            </div>
            <p style="color:rgba(255,255,255,.25);font-size:.72rem;margin:20px 0 0;">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> — <a href="<?php echo esc_url( $url_home ); ?>" target="_blank" rel="noopener nofollow" style="color:rgba(255,255,255,.25);">Alliance Groupe</a></p>
        </div>
        <?php
    }
}
