<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#main" class="screen-reader-text"><?php esc_html_e( 'Aller au contenu', 'ag-starter-barber' ); ?></a>

<?php $settings = AG_Barber_Queue::get_settings(); ?>
<header class="ag-header">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-header__logo">
        <?php echo esc_html( $settings['shop_name'] ); ?>
    </a>
    <nav class="ag-header__nav">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>#services"><?php esc_html_e( 'Tarifs', 'ag-starter-barber' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>#queue"><?php esc_html_e( 'File d\'attente', 'ag-starter-barber' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/?ag_queue=join' ) ); ?>" class="ag-header__cta">📱 <?php esc_html_e( 'Prendre un ticket', 'ag-starter-barber' ); ?></a>
    </nav>
</header>
