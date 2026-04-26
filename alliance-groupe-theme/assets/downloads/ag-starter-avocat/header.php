<?php
/**
 * @package AG_Starter_Avocat
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

<a class="screen-reader-text" href="#ag-main"><?php esc_html_e( 'Aller au contenu principal', 'ag-starter-avocat' ); ?></a>

<header class="ag-site-header" role="banner">
	<div class="ag-container ag-site-header__inner">
		<div class="ag-site-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php do_action( 'ag_brand_fallback' ); ?>
					<span class="ag-site-brand__text"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<nav class="ag-primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'ag-starter-avocat' ); ?>">
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
			<button class="ag-menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'ag-starter-avocat' ); ?>" aria-expanded="false">
				<span></span><span></span><span></span>
			</button>
		</nav>
		<div class="ag-header-actions">
			<?php
			if ( class_exists( 'AG_Pro_Features' ) ) {
				global $ag_pro;
				if ( ! isset( $ag_pro ) ) $ag_pro = new AG_Pro_Features( 'ag-starter-avocat' );
				echo $ag_pro->render_header_phone();
			}
			?>
			<button class="ag-theme-toggle" aria-label="<?php esc_attr_e( 'Changer de theme', 'ag-starter-avocat' ); ?>" title="<?php esc_attr_e( 'Mode clair / sombre', 'ag-starter-avocat' ); ?>">
				<span class="ag-theme-toggle__icon">🌙</span>
			</button>
		</div>
	</div>
</header>
