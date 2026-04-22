<?php
/**
 * Pro Features Module — conditionally enables features based on licence tier.
 *
 * Tiers (cumulative):
 *   free     → base theme (no Pro features)
 *   pro      → sticky header, animations, extra colors, footer custom, 2 extra fonts
 *   premium  → pro + testimonials section, gallery section, pricing table, WooCommerce ready
 *   business → premium + white-label (remove AG credits), extra page templates
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Pro_Features {

    private $tier;
    private $theme_slug;

    public function __construct( $theme_slug ) {
        $this->theme_slug = $theme_slug;
        $this->tier = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_tier() : 'free';

        if ( 'free' === $this->tier ) return;

        // Pro+ features
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pro_assets' ) );
        add_action( 'customize_register', array( $this, 'register_pro_customizer' ), 20 );
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );

        // Premium+ features
        if ( in_array( $this->tier, array( 'premium', 'business' ), true ) ) {
            add_action( 'customize_register', array( $this, 'register_premium_customizer' ), 21 );
        }

        // Business features
        if ( 'business' === $this->tier ) {
            add_filter( 'ag_show_credits', '__return_false' );
        }
    }

    public function get_tier() {
        return $this->tier;
    }

    public function is_at_least( $min_tier ) {
        $order = array( 'free' => 0, 'pro' => 1, 'premium' => 2, 'business' => 3 );
        $current = isset( $order[ $this->tier ] ) ? $order[ $this->tier ] : 0;
        $min     = isset( $order[ $min_tier ] ) ? $order[ $min_tier ] : 0;
        return $current >= $min;
    }

    // ─── PRO: Assets ──────────────────────────────────────────

    public function enqueue_pro_assets() {
        wp_add_inline_style( wp_get_theme()->get_stylesheet(), $this->get_pro_css() );
    }

    private function get_pro_css() {
        $css = '';

        // Sticky header
        if ( get_theme_mod( 'ag_pro_sticky_header', true ) ) {
            $css .= '
.ag-header{position:sticky;top:0;z-index:1000;transition:background .3s,box-shadow .3s;}
.ag-header.scrolled{background:rgba(10,10,10,.97);box-shadow:0 2px 20px rgba(0,0,0,.4);}
';
        }

        // Scroll animations
        if ( get_theme_mod( 'ag_pro_animations', true ) ) {
            $css .= '
.ag-fade-in{opacity:0;transform:translateY(20px);transition:opacity .6s ease,transform .6s ease;}
.ag-fade-in.visible{opacity:1;transform:translateY(0);}
.ag-slide-left{opacity:0;transform:translateX(-30px);transition:opacity .6s ease,transform .6s ease;}
.ag-slide-left.visible{opacity:1;transform:translateX(0);}
.ag-slide-right{opacity:0;transform:translateX(30px);transition:opacity .6s ease,transform .6s ease;}
.ag-slide-right.visible{opacity:1;transform:translateX(0);}
.ag-scale-in{opacity:0;transform:scale(.95);transition:opacity .5s ease,transform .5s ease;}
.ag-scale-in.visible{opacity:1;transform:scale(1);}
@media(prefers-reduced-motion:reduce){
    .ag-fade-in,.ag-slide-left,.ag-slide-right,.ag-scale-in{opacity:1;transform:none;transition:none;}
}
';
        }

        // Custom footer background
        $footer_bg = get_theme_mod( 'ag_pro_footer_bg', '' );
        if ( $footer_bg ) {
            $css .= '.ag-footer{background-color:' . esc_attr( $footer_bg ) . ';}';
        }

        // Extra accent color
        $accent2 = get_theme_mod( 'ag_pro_accent_secondary', '' );
        if ( $accent2 ) {
            $css .= ':root{--ag-accent-secondary:' . esc_attr( $accent2 ) . ';}';
        }

        // Premium: testimonials & gallery
        if ( $this->is_at_least( 'premium' ) ) {
            $css .= '
.ag-testimonials{padding:80px 0;}
.ag-testimonials__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;}
.ag-testimonial-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px;position:relative;}
.ag-testimonial-card__stars{color:#D4B45C;font-size:1.1rem;margin-bottom:12px;}
.ag-testimonial-card__text{color:rgba(255,255,255,.8);font-size:.95rem;line-height:1.7;font-style:italic;margin-bottom:16px;}
.ag-testimonial-card__author{font-weight:700;color:#fff;font-size:.9rem;}
.ag-testimonial-card__role{color:rgba(255,255,255,.5);font-size:.82rem;}
.ag-gallery{padding:80px 0;}
.ag-gallery__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;}
.ag-gallery__item{border-radius:12px;overflow:hidden;aspect-ratio:4/3;}
.ag-gallery__item img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.ag-gallery__item:hover img{transform:scale(1.06);}
.ag-pricing-table{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;padding:80px 0;}
.ag-pricing-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:36px 28px;text-align:center;transition:transform .3s,border-color .3s;}
.ag-pricing-card:hover{transform:translateY(-6px);border-color:rgba(212,180,92,.3);}
.ag-pricing-card--featured{border-color:#D4B45C;background:rgba(212,180,92,.04);}
.ag-pricing-card__price{font-size:2.4rem;font-weight:800;color:#fff;margin:16px 0 8px;}
.ag-pricing-card__price small{font-size:.9rem;color:rgba(255,255,255,.5);font-weight:400;}
';
        }

        return $css;
    }

    // ─── PRO: Customizer ──────────────────────────────────────

    public function register_pro_customizer( $wp_customize ) {
        $wp_customize->add_section( 'ag_pro_features', array(
            'title'    => esc_html__( '⭐ Fonctionnalités Pro', $this->theme_slug ),
            'priority' => 25,
        ) );

        // Sticky header
        $wp_customize->add_setting( 'ag_pro_sticky_header', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_pro_sticky_header', array(
            'label'   => esc_html__( 'Header sticky au scroll', $this->theme_slug ),
            'section' => 'ag_pro_features',
            'type'    => 'checkbox',
        ) );

        // Animations
        $wp_customize->add_setting( 'ag_pro_animations', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
        $wp_customize->add_control( 'ag_pro_animations', array(
            'label'   => esc_html__( 'Animations au scroll (fade, slide)', $this->theme_slug ),
            'section' => 'ag_pro_features',
            'type'    => 'checkbox',
        ) );

        // Secondary accent color
        $wp_customize->add_setting( 'ag_pro_accent_secondary', array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ag_pro_accent_secondary', array(
            'label'   => esc_html__( 'Couleur d\'accent secondaire', $this->theme_slug ),
            'section' => 'ag_pro_features',
        ) ) );

        // Footer background
        $wp_customize->add_setting( 'ag_pro_footer_bg', array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ag_pro_footer_bg', array(
            'label'   => esc_html__( 'Couleur de fond du footer', $this->theme_slug ),
            'section' => 'ag_pro_features',
        ) ) );

        // Footer text
        $wp_customize->add_setting( 'ag_pro_footer_text', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
        $wp_customize->add_control( 'ag_pro_footer_text', array(
            'label'   => esc_html__( 'Texte personnalisé du footer', $this->theme_slug ),
            'section' => 'ag_pro_features',
            'type'    => 'text',
        ) );
    }

    // ─── PREMIUM: Customizer ──────────────────────────────────

    public function register_premium_customizer( $wp_customize ) {
        $wp_customize->add_section( 'ag_premium_features', array(
            'title'    => esc_html__( '💎 Fonctionnalités Premium', $this->theme_slug ),
            'priority' => 26,
        ) );

        // Testimonials
        for ( $i = 1; $i <= 6; $i++ ) {
            $wp_customize->add_setting( "ag_testimonial_{$i}_text", array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
            $wp_customize->add_control( "ag_testimonial_{$i}_text", array(
                'label'   => sprintf( esc_html__( 'Témoignage %d — Texte', $this->theme_slug ), $i ),
                'section' => 'ag_premium_features',
                'type'    => 'textarea',
            ) );
            $wp_customize->add_setting( "ag_testimonial_{$i}_author", array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
            $wp_customize->add_control( "ag_testimonial_{$i}_author", array(
                'label'   => sprintf( esc_html__( 'Témoignage %d — Auteur', $this->theme_slug ), $i ),
                'section' => 'ag_premium_features',
                'type'    => 'text',
            ) );
        }
    }

    // ─── Body classes ─────────────────────────────────────────

    public function add_body_classes( $classes ) {
        $classes[] = 'ag-pro';
        $classes[] = 'ag-tier-' . $this->tier;
        if ( get_theme_mod( 'ag_pro_animations', true ) ) {
            $classes[] = 'ag-has-animations';
        }
        return $classes;
    }

    // ─── Template helpers (called from templates) ─────────────

    public function render_testimonials() {
        if ( ! $this->is_at_least( 'premium' ) ) return;
        $testimonials = array();
        for ( $i = 1; $i <= 6; $i++ ) {
            $text   = get_theme_mod( "ag_testimonial_{$i}_text", '' );
            $author = get_theme_mod( "ag_testimonial_{$i}_author", '' );
            if ( $text && $author ) {
                $testimonials[] = array( 'text' => $text, 'author' => $author );
            }
        }
        if ( empty( $testimonials ) ) return;
        ?>
        <section class="ag-testimonials">
            <div class="ag-container">
                <h2 style="text-align:center;margin-bottom:48px;font-size:clamp(1.5rem,3vw,2.2rem);color:#fff;">
                    Ce que disent <em style="color:#D4B45C;font-style:italic;">nos clients</em>
                </h2>
                <div class="ag-testimonials__grid">
                    <?php foreach ( $testimonials as $t ) : ?>
                    <div class="ag-testimonial-card ag-fade-in">
                        <div class="ag-testimonial-card__stars">★★★★★</div>
                        <p class="ag-testimonial-card__text">"<?php echo esc_html( $t['text'] ); ?>"</p>
                        <div class="ag-testimonial-card__author"><?php echo esc_html( $t['author'] ); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Render footer credit/promo based on tier.
     *
     * free     → big animated promo widget linking to templates page
     * pro      → small "Fièrement créé par" with logo + Alliance Groupe
     * premium  → tiny copyright line with link
     * business → simple copyright, just © + site name + small AG link
     */
    public function render_footer_credit() {
        $url = 'https://alliancegroupe-inc.com/templates-wordpress';

        if ( 'business' === $this->tier ) {
            // Business: just a copyright with discreet link
            echo '<p style="text-align:center;color:rgba(255,255,255,.35);font-size:.78rem;margin-top:16px;">';
            echo '&copy; ' . esc_html( date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . ' — ';
            echo '<a href="' . esc_url( $url ) . '" rel="nofollow" style="color:rgba(255,255,255,.35);">Alliance Groupe</a>';
            echo '</p>';
            return;
        }

        if ( 'premium' === $this->tier ) {
            // Premium: small elegant line
            echo '<div style="text-align:center;padding:20px 0 0;border-top:1px solid rgba(255,255,255,.06);margin-top:24px;">';
            echo '<p style="color:rgba(255,255,255,.5);font-size:.82rem;">';
            echo '&copy; ' . esc_html( date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . ' · ';
            echo 'Propulsé par <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener nofollow" style="color:#D4B45C;text-decoration:none;">Alliance Groupe</a>';
            echo '</p></div>';
            return;
        }

        if ( 'pro' === $this->tier ) {
            // Pro: "Fièrement créé par" with logo text
            echo '<div style="text-align:center;padding:24px 0 0;border-top:1px solid rgba(255,255,255,.06);margin-top:24px;">';
            echo '<p style="color:rgba(255,255,255,.5);font-size:.85rem;margin:0 0 6px;">Fièrement créé par</p>';
            echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener nofollow" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:#D4B45C;font-weight:700;font-size:1rem;font-family:\'Playfair Display\',serif;font-style:italic;">';
            echo '<span style="font-size:1.4rem;">🦁</span> Alliance Groupe';
            echo '</a>';
            echo '</div>';
            return;
        }

        // Free: big animated promo widget
        echo '<div style="margin-top:32px;padding:28px 24px;background:linear-gradient(135deg,rgba(212,180,92,.08) 0%,rgba(10,10,15,.95) 100%);border:1px solid rgba(212,180,92,.3);border-radius:16px;text-align:center;animation:agPulseGlow 3s ease-in-out infinite;">';
        echo '<style>@keyframes agPulseGlow{0%,100%{box-shadow:0 0 20px rgba(212,180,92,.1)}50%{box-shadow:0 0 30px rgba(212,180,92,.25)}}</style>';
        echo '<p style="font-size:1.4rem;font-weight:800;color:#fff;margin:0 0 8px;font-family:\'Playfair Display\',serif;">';
        echo '<span style="font-size:1.8rem;">🦁</span> Alliance Groupe';
        echo '</p>';
        echo '<p style="color:rgba(255,255,255,.7);font-size:.92rem;margin:0 0 16px;line-height:1.5;">';
        echo 'Ce thème gratuit est offert par <strong style="color:#D4B45C;">Alliance Groupe</strong><br>';
        echo 'Agence Web & IA — Créez votre site professionnel en 5 minutes';
        echo '</p>';
        echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" style="display:inline-block;background:#D4B45C;color:#0a0a0f;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:.95rem;transition:transform .2s;" onmouseover="this.style.transform=\'translateY(-2px)\'" onmouseout="this.style.transform=\'none\'">';
        echo 'Découvrir nos templates gratuits →';
        echo '</a>';
        echo '<p style="color:rgba(255,255,255,.35);font-size:.75rem;margin:12px 0 0;">Passez au Pack Pro pour réduire cette publicité</p>';
        echo '</div>';
    }
}
