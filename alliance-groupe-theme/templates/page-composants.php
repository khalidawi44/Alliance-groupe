<?php
/**
 * Template Name: Composants (app autonome)
 *
 * Page « canvas » INDÉPENDANTE : n'utilise PAS get_header()/get_footer(),
 * donc aucun en-tête, menu ni pied de page du site — uniquement l'espace
 * Composants, en plein écran, comme une app dédiée.
 * Toute la logique est dans inc/ag-composants.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// App épurée : pas de barre d'admin WordPress par-dessus.
add_filter( 'show_admin_bar', '__return_false' );

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<style>
		/* Reset app : on neutralise le chrome/typo du thème, l'app gère son propre style. */
		html,body{margin:0;padding:0;background:#0a0e1a;color:#e7ecf5;min-height:100%}
		body.agc-app{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
		body.agc-app #wpadminbar{display:none!important}
		body.agc-app .agc-appbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 18px;background:rgba(10,14,26,.92);border-bottom:1px solid #1b2440}
		body.agc-app .agc-appbar a.agc-back{color:#9fb0c8;text-decoration:none;font:600 14px system-ui;display:inline-flex;align-items:center;gap:6px}
		body.agc-app .agc-appbar a.agc-back:hover{color:#c9a96e}
		body.agc-app .agc-appbar .agc-appbar__brand{font:800 15px system-ui;background:linear-gradient(120deg,#f0d99a,#c9a96e);-webkit-background-clip:text;background-clip:text;color:transparent}
		body.agc-app .agc-appbar a.agc-cta{color:#08101f;background:#c9a96e;text-decoration:none;font:700 13px system-ui;padding:8px 14px;border-radius:999px}
	</style>
</head>
<body <?php body_class( 'agc-app' ); ?>>

	<div class="agc-appbar">
		<a class="agc-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
		<span class="agc-appbar__brand">Composants</span>
		<a class="agc-cta" href="#agc-propose">+ Proposer</a>
	</div>

	<?php
	if ( function_exists( 'ag_composants_render' ) ) {
		ag_composants_render();
	}
	wp_footer();
	?>
</body>
</html>
