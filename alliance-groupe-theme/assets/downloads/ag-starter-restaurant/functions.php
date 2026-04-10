<?php
/**
 * AG Starter Restaurant — functions.php
 *
 * @package AG_Starter_Restaurant
 */

add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );

/**
 * Enqueue the main stylesheet.
 */
function ag_starter_restaurant_enqueue_styles() {
    wp_enqueue_style( 'ag-starter-restaurant-style', get_stylesheet_uri(), array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'ag_starter_restaurant_enqueue_styles' );
