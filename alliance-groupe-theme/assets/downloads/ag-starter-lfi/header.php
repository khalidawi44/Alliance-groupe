<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#main" class="screen-reader-text"><?php esc_html_e( 'Aller au contenu', 'ag-starter-lfi' ); ?></a>

<header class="ag-lfi-header">
    <div class="ag-lfi-header__inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-lfi-header__logo">
            <?php if ( has_custom_logo() ) {
                the_custom_logo();
            } else {
                echo '<span class="ag-lfi-header__name">' . esc_html( ag_lfi_opt( 'ag_lfi_name', '[Mouvement]' ) ) . '</span>';
            } ?>
        </a>
        <nav class="ag-lfi-header__nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'ag-starter-lfi' ); ?>">
            <a href="#manifeste"><?php esc_html_e( 'Manifeste', 'ag-starter-lfi' ); ?></a>
            <a href="#combats"><?php esc_html_e( 'Combats', 'ag-starter-lfi' ); ?></a>
            <a href="#evenements"><?php esc_html_e( 'Événements', 'ag-starter-lfi' ); ?></a>
            <a href="#groupes"><?php esc_html_e( 'Groupes locaux', 'ag-starter-lfi' ); ?></a>
            <a href="#actu"><?php esc_html_e( 'Actualités', 'ag-starter-lfi' ); ?></a>
            <a href="#don" class="ag-lfi-cta"><?php esc_html_e( 'Faire un don', 'ag-starter-lfi' ); ?></a>
        </nav>
    </div>
</header>
