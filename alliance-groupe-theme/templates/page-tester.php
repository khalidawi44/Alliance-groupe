<?php
/**
 * Template Name: Tester mon site
 *
 * Entonnoir freemium « Tester mon site » (shortcode [ag_tester]).
 * Crée une page WP avec ce modèle, slug conseillé : /tester-mon-site.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
echo do_shortcode( '[ag_tester]' );
get_footer();
