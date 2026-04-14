<?php
/**
 * AG Starter Coach — Customizer output.
 *
 * Translates the customizer settings into dynamic inline CSS that
 * overrides the base style.css values. Output via wp_add_inline_style
 * so the customizer values apply without editing the static CSS file.
 *
 * @package AG_Starter_Coach
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the dynamic CSS string from the customizer settings.
 *
 * @return string
 */
function ag_starter_coach_customizer_css() {
	$accent     = ag_starter_coach_get_option( 'ag_color_accent' );
	$background = ag_starter_coach_get_option( 'ag_color_background' );
	$panel      = ag_starter_coach_get_option( 'ag_color_panel' );
	$border     = ag_starter_coach_get_option( 'ag_color_border' );
	$text       = ag_starter_coach_get_option( 'ag_color_text' );
	$heading    = ag_starter_coach_get_option( 'ag_color_heading' );
	$muted      = ag_starter_coach_get_option( 'ag_color_muted' );

	$family = ag_starter_coach_get_option( 'ag_font_family' );
	$size   = absint( ag_starter_coach_get_option( 'ag_font_base_size' ) );
	$scale  = ag_starter_coach_get_option( 'ag_font_heading_scale' );

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
function ag_starter_coach_enqueue_dynamic_css() {
	$css = ag_starter_coach_customizer_css();
	if ( $css ) {
		wp_add_inline_style( 'ag-starter-coach-style', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'ag_starter_coach_enqueue_dynamic_css', 20 );
