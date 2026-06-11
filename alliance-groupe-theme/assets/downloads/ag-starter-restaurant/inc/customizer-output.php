<?php
/**
 * AG Starter Restaurant — Customizer output.
 *
 * Translates the customizer settings into dynamic inline CSS that
 * overrides the base style.css values. Output via wp_add_inline_style
 * so the customizer values apply without editing the static CSS file.
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renvoie une couleur de texte lisible (noir ou blanc) à poser sur une
 * couleur de fond donnée, selon sa luminance perçue.
 *
 * @param string $hex Couleur de fond (#rrggbb).
 * @return string '#1a1a1a' (texte foncé) ou '#ffffff' (texte clair).
 */
function ag_starter_restaurant_contrast_color( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return '#ffffff';
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	// Luminance perçue (0–255).
	$lum = ( 0.299 * $r + 0.587 * $g + 0.114 * $b );
	return $lum > 150 ? '#1a1a1a' : '#ffffff';
}

/**
 * Build the dynamic CSS string from the customizer settings.
 *
 * @return string
 */
function ag_starter_restaurant_customizer_css() {
	$accent     = ag_starter_restaurant_get_option( 'ag_color_accent' );
	$background = ag_starter_restaurant_get_option( 'ag_color_background' );
	$panel      = ag_starter_restaurant_get_option( 'ag_color_panel' );
	$border     = ag_starter_restaurant_get_option( 'ag_color_border' );
	$text       = ag_starter_restaurant_get_option( 'ag_color_text' );
	$heading    = ag_starter_restaurant_get_option( 'ag_color_heading' );
	$muted      = ag_starter_restaurant_get_option( 'ag_color_muted' );
	// Accent secondaire (2e couleur de marque : ex. vert pour l'Italie, jaune
	// pour le burger). Vide -> on retombe sur l'accent principal.
	$accent2    = ag_starter_restaurant_get_option( 'ag_color_accent2' );
	if ( ! $accent2 ) { $accent2 = $accent; }
	// Couleur de texte lisible POSÉE SUR l'accent (noir ou blanc selon la teinte).
	$on_accent  = ag_starter_restaurant_contrast_color( $accent );
	$on_accent2 = ag_starter_restaurant_contrast_color( $accent2 );

	$family = ag_starter_restaurant_get_option( 'ag_font_family' );
	$size   = absint( ag_starter_restaurant_get_option( 'ag_font_base_size' ) );
	$scale  = ag_starter_restaurant_get_option( 'ag_font_heading_scale' );

	if ( $size < 14 ) {
		$size = 14;
	}
	if ( $size > 20 ) {
		$size = 20;
	}

	$family_stacks = array(
		'system'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif',
		'sans'      => 'Arial, Helvetica, sans-serif',
		'serif'     => 'Georgia, "Times New Roman", Times, serif',
		'monospace' => 'Menlo, Consolas, "Courier New", monospace',
	);
	$family_css = isset( $family_stacks[ $family ] ) ? $family_stacks[ $family ] : $family_stacks['system'];

	$scale_map = array(
		'small'   => 0.88,
		'default' => 1.0,
		'large'   => 1.18,
	);
	$scale_factor = isset( $scale_map[ $scale ] ) ? $scale_map[ $scale ] : 1.0;

	// Hero title size in rem, scaled.
	$hero_size    = round( 1.8 * $scale_factor, 2 );
	$entry_size   = round( 1.5 * $scale_factor, 2 );
	$section_size = round( 1.4 * $scale_factor, 2 );

	ob_start();
	?>
:root {
	--ag-color-accent: <?php echo esc_html( $accent ); ?>;
	--ag-color-accent2: <?php echo esc_html( $accent2 ); ?>;
	--ag-color-on-accent: <?php echo esc_html( $on_accent ); ?>;
	--ag-color-on-accent2: <?php echo esc_html( $on_accent2 ); ?>;
	--ag-color-background: <?php echo esc_html( $background ); ?>;
	--ag-color-panel: <?php echo esc_html( $panel ); ?>;
	--ag-color-border: <?php echo esc_html( $border ); ?>;
	--ag-color-heading: <?php echo esc_html( $heading ); ?>;
	--ag-color-text: <?php echo esc_html( $text ); ?>;
	--ag-color-muted: <?php echo esc_html( $muted ); ?>;
}
body {
	font-family: <?php echo esc_html( $family_css ); ?>;
	font-size: <?php echo esc_html( $size ); ?>px;
	color: <?php echo esc_html( $text ); ?>;
	background: <?php echo esc_html( $background ); ?>;
}
a { color: <?php echo esc_html( $accent ); ?>; }
h1, h2, h3, h4, h5, h6 { color: <?php echo esc_html( $heading ); ?>; }

.ag-site-header { border-bottom-color: <?php echo esc_html( $border ); ?>; }
.ag-site-brand { color: <?php echo esc_html( $accent ); ?>; }
.ag-primary-menu a { color: <?php echo esc_html( $muted ); ?>; }

.ag-hero { background: <?php echo esc_html( $panel ); ?>; border-bottom-color: <?php echo esc_html( $border ); ?>; }
.ag-hero__title { font-size: <?php echo esc_html( $hero_size ); ?>rem; }
.ag-hero__title span { color: <?php echo esc_html( $accent ); ?>; }
.ag-hero__subtitle { color: <?php echo esc_html( $muted ); ?>; }
.ag-btn { background: <?php echo esc_html( $accent ); ?>; color: <?php echo esc_html( $background ); ?>; }

.ag-card { background: <?php echo esc_html( $panel ); ?>; border-color: <?php echo esc_html( $border ); ?>; }
.ag-card h2 { color: <?php echo esc_html( $accent ); ?>; }
.ag-card p { color: <?php echo esc_html( $muted ); ?>; }

.ag-info { border-top-color: <?php echo esc_html( $border ); ?>; border-bottom-color: <?php echo esc_html( $border ); ?>; }
.ag-info h2 { color: <?php echo esc_html( $accent ); ?>; }
.ag-info p { color: <?php echo esc_html( $muted ); ?>; }

.ag-main article { border-bottom-color: <?php echo esc_html( $border ); ?>; }
.ag-entry-title { font-size: <?php echo esc_html( $entry_size ); ?>rem; }
.ag-entry-title a { color: <?php echo esc_html( $heading ); ?>; }
.ag-entry-meta { color: <?php echo esc_html( $muted ); ?>; }
.ag-entry-content { color: <?php echo esc_html( $text ); ?>; }
.ag-entry-content a { color: <?php echo esc_html( $accent ); ?>; }
.ag-entry-content blockquote { border-left-color: <?php echo esc_html( $accent ); ?>; color: <?php echo esc_html( $muted ); ?>; }
.ag-entry-content pre, .ag-entry-content code { background: <?php echo esc_html( $panel ); ?>; }

.ag-comments { border-top-color: <?php echo esc_html( $border ); ?>; }
.ag-comments .comment { border-bottom-color: <?php echo esc_html( $border ); ?>; }
.ag-comments .children { border-left-color: <?php echo esc_html( $border ); ?>; }
.ag-comments .comment-author { color: <?php echo esc_html( $accent ); ?>; }
.ag-comments input[type="text"],
.ag-comments input[type="email"],
.ag-comments input[type="url"],
.ag-comments textarea { background: <?php echo esc_html( $panel ); ?>; border-color: <?php echo esc_html( $border ); ?>; color: <?php echo esc_html( $text ); ?>; }
.ag-comments input[type="submit"] { background: <?php echo esc_html( $accent ); ?>; color: <?php echo esc_html( $background ); ?>; }

.ag-sidebar { border-top-color: <?php echo esc_html( $border ); ?>; }
.ag-widget-title { color: <?php echo esc_html( $accent ); ?>; }
.ag-widget li { border-bottom-color: <?php echo esc_html( $border ); ?>; color: <?php echo esc_html( $muted ); ?>; }

.ag-search-form input[type="search"] { background: <?php echo esc_html( $panel ); ?>; border-color: <?php echo esc_html( $border ); ?>; color: <?php echo esc_html( $text ); ?>; }
.ag-search-form input[type="submit"] { background: <?php echo esc_html( $accent ); ?>; color: <?php echo esc_html( $background ); ?>; }

.ag-pagination a, .ag-pagination span { border-color: <?php echo esc_html( $border ); ?>; color: <?php echo esc_html( $text ); ?>; }
.ag-pagination .current { background: <?php echo esc_html( $accent ); ?>; color: <?php echo esc_html( $background ); ?>; border-color: <?php echo esc_html( $accent ); ?>; }

.ag-site-footer { border-top-color: <?php echo esc_html( $border ); ?>; }
.ag-footer-col h3 { color: <?php echo esc_html( $accent ); ?>; }
.ag-footer-col p, .ag-footer-col li { color: <?php echo esc_html( $muted ); ?>; }
.ag-footer-bottom { border-top-color: <?php echo esc_html( $border ); ?>; }
	<?php
	return trim( ob_get_clean() );
}

/**
 * Inject the dynamic CSS after the main stylesheet.
 */
function ag_starter_restaurant_enqueue_dynamic_css() {
	$css = ag_starter_restaurant_customizer_css();
	if ( $css ) {
		wp_add_inline_style( 'ag-starter-restaurant-style', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'ag_starter_restaurant_enqueue_dynamic_css', 20 );
