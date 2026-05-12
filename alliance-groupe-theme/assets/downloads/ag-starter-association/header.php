<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#main" class="screen-reader-text"><?php esc_html_e( 'Aller au contenu', 'ag-starter-association' ); ?></a>

<?php if ( ag_asso_opt( 'ag_asso_alert_active', 0 ) ) : ?>
    <div class="ag-asso-alert" role="alert">
        <span class="ag-asso-alert__text"><?php echo esc_html( ag_asso_opt( 'ag_asso_alert_text', '' ) ); ?></span>
        <?php if ( $alert_url = ag_asso_opt( 'ag_asso_alert_link_url', '' ) ) : ?>
            <a class="ag-asso-alert__link" href="<?php echo esc_url( $alert_url ); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_alert_link_label', 'En savoir plus' ) ); ?> →</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<header class="ag-asso-header">
    <div class="ag-asso-header__inner">
        <?php if ( has_custom_logo() ) {
            // Force le logo a etre toujours clickable vers l'accueil,
            // meme quand on est deja sur la home (sinon WP retire le
            // wrap <a> par defaut quand is_front_page() && is_home()).
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo_img = wp_get_attachment_image( $custom_logo_id, 'full', false, array(
                'class' => 'custom-logo',
                'alt'   => get_bloginfo( 'name' ),
            ) );
            if ( $logo_img ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="ag-asso-header__logolink custom-logo-link">
                    <?php echo $logo_img; ?>
                </a>
            <?php endif;
        } else { ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-asso-header__logo">
                <span class="ag-asso-header__name"><?php echo esc_html( ag_asso_opt( 'ag_asso_name', '[Mouvement]' ) ); ?></span>
            </a>
        <?php } ?>

        <button type="button" class="ag-asso-burger" id="agAssoBurger" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'ag-starter-association' ); ?>" aria-expanded="false" aria-controls="agAssoNav">
            <span></span><span></span><span></span>
        </button>

        <nav class="ag-asso-header__nav" id="agAssoNav" aria-label="<?php esc_attr_e( 'Navigation principale', 'ag-starter-association' ); ?>">
            <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <?php wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'ag-asso-header__menu',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ) ); ?>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/manifeste/' ) ); ?>"><?php esc_html_e( 'Manifeste', 'ag-starter-association' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/combats/' ) ); ?>"><?php esc_html_e( 'Combats', 'ag-starter-association' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>"><?php esc_html_e( 'Événements', 'ag-starter-association' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/groupes/' ) ); ?>"><?php esc_html_e( 'Groupes locaux', 'ag-starter-association' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/actu/' ) ); ?>"><?php esc_html_e( 'Actualités', 'ag-starter-association' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/don/' ) ); ?>" class="ag-asso-cta"><?php esc_html_e( 'Faire un don', 'ag-starter-association' ); ?></a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script>
(function(){
    var b=document.getElementById('agAssoBurger'),h=document.querySelector('.ag-asso-header');
    if(!b||!h)return;
    b.addEventListener('click',function(){
        var open=h.classList.toggle('is-mobile-open');
        b.setAttribute('aria-expanded',open?'true':'false');
    });
    document.querySelectorAll('.ag-asso-header__menu a, .ag-asso-header__nav > a').forEach(function(a){
        a.addEventListener('click',function(){h.classList.remove('is-mobile-open');b.setAttribute('aria-expanded','false');});
    });
})();
</script>
