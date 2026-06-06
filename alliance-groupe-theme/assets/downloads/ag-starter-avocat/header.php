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
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="custom-logo-link" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — <?php esc_attr_e( 'Accueil', 'ag-starter-avocat' ); ?>">
				<?php if ( $logo_img ) : ?>
					<?php echo $logo_img; ?>
				<?php else : ?>
					<?php do_action( 'ag_brand_fallback' ); ?>
					<span class="ag-site-brand__text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>
		</div>

		<nav class="ag-primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'ag-starter-avocat' ); ?>">
			<?php
			// Menu principal — avec menu de secours (liste des pages) quand
			// aucun menu n'est assigne, pour que le menu ne soit jamais vide
			// (ex. juste apres un changement de theme).
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ag-primary-menu',
					'depth'          => 1,
					'fallback_cb'    => 'ag_starter_avocat_menu_fallback',
				)
			);
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
				if ( $ag_pro->is_at_least( 'premium' ) ) {
					echo $ag_pro->render_header_phone();
					?>
					<button class="ag-theme-toggle" aria-label="<?php esc_attr_e( 'Changer de theme', 'ag-starter-avocat' ); ?>" title="<?php esc_attr_e( 'Mode clair / sombre', 'ag-starter-avocat' ); ?>">
						<span class="ag-theme-toggle__icon">🌙</span>
					</button>
					<?php
				}
			}
			?>
		</div>
	</div>
</header>
