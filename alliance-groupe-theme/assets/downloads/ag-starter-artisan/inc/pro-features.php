<?php
/**
 * Pro Features — AG Starter Avocat
 *
 * 3 tiers:
 *   free     → base theme + grosse pub AG animée dans le footer
 *   pro      → fonts, animations, sticky header+tel, témoignages, couleurs, pub "Fièrement créé par"
 *   business → tout Pro + WooCommerce ready + copyright minimal
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
        wp_add_inline_style( 'ag-starter-artisan-style', $this->get_pro_css() );
    }

    private function get_pro_css() {
        $css = '
body{font-family:"Manrope",system-ui,sans-serif !important;}
h1,h2,h3,h4,.ag-hero__title,.ag-section-title,.ag-domaine-card h3,.ag-honoraires__card h3{font-family:"Playfair Display",serif !important;}
';
        if ( get_theme_mod( 'ag_pro_sticky_header', true ) ) {
            $css .= '
.ag-site-header{position:sticky;top:0;z-index:1000;transition:background .3s,box-shadow .3s;}
.ag-site-header.scrolled{background:rgba(10,10,15,.97) !important;box-shadow:0 2px 20px rgba(0,0,0,.4);}
.ag-header__phone{display:inline-flex;align-items:center;gap:6px;background:#D4B45C;color:#0a0a0f;padding:8px 16px;border-radius:6px;font-weight:700;font-size:.85rem;text-decoration:none;margin-left:12px;transition:transform .2s;}
.ag-header__phone:hover{transform:translateY(-2px);}
';
        }
        if ( get_theme_mod( 'ag_pro_animations', true ) ) {
            $css .= '
.ag-fade-in{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease;}
.ag-fade-in.visible{opacity:1;transform:translateY(0);}
@media(prefers-reduced-motion:reduce){.ag-fade-in{opacity:1 !important;transform:none !important;transition:none !important;}}
';
        }
        $css .= '
.ag-testimonials{padding:80px 24px;background:rgba(255,255,255,.02);}
.ag-testimonials__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:1200px;margin:0 auto;}
.ag-testimonial-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px;}
.ag-testimonial-card__stars{color:#D4B45C;font-size:1.1rem;margin-bottom:12px;letter-spacing:2px;}
.ag-testimonial-card__text{color:rgba(255,255,255,.8);font-size:.95rem;line-height:1.7;font-style:italic;margin-bottom:16px;}
.ag-testimonial-card__author{font-weight:700;color:#fff;font-size:.9rem;}
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
