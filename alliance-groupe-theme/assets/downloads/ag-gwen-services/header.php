<?php
/**
 * The header for our theme.
 *
 * @package AG_Starter_Domicile
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

<a class="screen-reader-text" href="#ag-main"><?php esc_html_e( 'Aller au contenu principal', 'ag-gwen-services' ); ?></a>

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
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="custom-logo-link" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — <?php esc_attr_e( 'Accueil', 'ag-gwen-services' ); ?>">
				<?php if ( $logo_img ) : ?>
					<?php echo $logo_img; ?>
				<?php elseif ( file_exists( get_theme_file_path( 'assets/logo.png' ) ) ) : ?>
					<?php // Logo de marque livre avec le theme : aucun reglage a faire. ?>
					<img class="ag-site-brand__logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/logo.png' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="357" height="140" style="height:clamp(38px,5.2vw,54px);width:auto;max-width:62vw;display:block;" />
				<?php else : ?>
					<span class="ag-site-brand__text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>
		</div>

		<button class="ag-navtoggle" type="button" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'ag-gwen-services' ); ?>" aria-expanded="false" aria-controls="ag-primary-nav">
			<span></span><span></span><span></span>
		</button>

		<nav id="ag-primary-nav" class="ag-primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'ag-gwen-services' ); ?>">
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
<script>
/* Menu burger (mobile) : ouvre/ferme le panneau de navigation. */
(function(){
	var h = document.querySelector('.ag-site-header'),
		b = document.querySelector('.ag-navtoggle');
	if ( ! h || ! b ) { return; }
	b.addEventListener('click', function(){
		var open = h.classList.toggle('nav-open');
		b.setAttribute('aria-expanded', open ? 'true' : 'false');
	});
	// Referme au clic sur un lien du menu.
	h.querySelectorAll('.ag-primary-menu a').forEach(function(a){
		a.addEventListener('click', function(){
			h.classList.remove('nav-open');
			b.setAttribute('aria-expanded', 'false');
		});
	});
})();
</script>
