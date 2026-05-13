<?php
/**
 * The header for our theme.
 *
 * @package AG_Starter_Coach
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

<a class="screen-reader-text" href="#ag-main"><?php esc_html_e( 'Aller au contenu principal', 'ag-starter-coach' ); ?></a>

<header class="ag-site-header" role="banner">
	<div class="ag-container ag-site-header__inner">
		<div class="ag-site-brand">
			<?php
			// Logo toujours cliquable vers l'accueil, meme sur is_front_page().
			// WP retire le wrap <a> par defaut sur la home — on construit
			// nous-memes pour garantir le lien partout.
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo_img       = $custom_logo_id ? wp_get_attachment_image( $custom_logo_id, 'full', false, array(
				'class' => 'custom-logo',
				'alt'   => get_bloginfo( 'name' ),
			) ) : '';
			?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="custom-logo-link" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — <?php esc_attr_e( 'Accueil', 'ag-starter-coach' ); ?>">
				<?php if ( $logo_img ) : ?>
					<?php echo $logo_img; ?>
				<?php else : ?>
					<span class="ag-site-brand__text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>
		</div>

		<nav class="ag-primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'ag-starter-coach' ); ?>">
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
