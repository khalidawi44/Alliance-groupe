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
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-asso-header__logo">
            <?php if ( has_custom_logo() ) {
                the_custom_logo();
            } else {
                echo '<span class="ag-asso-header__name">' . esc_html( ag_asso_opt( 'ag_asso_name', '[Mouvement]' ) ) . '</span>';
            } ?>
        </a>
        <nav class="ag-asso-header__nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'ag-starter-association' ); ?>">
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
