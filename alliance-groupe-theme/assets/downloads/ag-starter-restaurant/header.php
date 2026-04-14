<?php
/**
 * The header for our theme.
 *
 * @package AG_Starter_Restaurant
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#ag-main"><?php esc_html_e( 'Aller au contenu principal', 'ag-starter-restaurant' ); ?></a>

<header class="ag-site-header" role="banner">
	<div class="ag-container ag-site-header__inner">
		<div class="ag-site-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>

		<nav class="ag-primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'ag-starter-restaurant' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'ag-primary-menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			}
			?>
		</nav>
	</div>
</header>
