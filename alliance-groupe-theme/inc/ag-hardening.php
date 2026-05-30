<?php
/**
 * AG — Durcissement sécurité du SITE LUI-MÊME (alliancegroupe-inc.com).
 *
 * Corrige les failles que notre propre audit détecte : xmlrpc exposé,
 * énumération des comptes (wp-json) et d'auteur (?author=), pingback,
 * en-têtes de sécurité manquants, divulgation de version.
 *
 * 100% défensif, sur NOTRE site. Aucune incidence sur les visiteurs.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---- 1. xmlrpc.php : bloqué entièrement (force brute + DDoS pingback) ---- */
add_action( 'init', function () {
	$sf  = isset( $_SERVER['SCRIPT_FILENAME'] ) ? basename( (string) $_SERVER['SCRIPT_FILENAME'] ) : '';
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	if ( 'xmlrpc.php' === $sf || false !== strpos( $uri, '/xmlrpc.php' ) ) {
		status_header( 403 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		exit( 'Forbidden.' );
	}
}, 0 );
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

/* ---- 2. Énumération des comptes via l'API REST (non connectés) ---- */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route ) {
			if ( isset( $endpoints[ $route ] ) ) unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

/* ---- 3. Énumération d'auteur ?author=N et archives /author/ ----
 * IMPORTANT : on intercepte ?author= dès 'init' (priorité 1), AVANT le
 * redirect_canonical de WordPress (template_redirect, priorité 10) qui, lui,
 * révélait le login en redirigeant vers /author/{login}/. */
add_action( 'init', function () {
	if ( ! is_admin() && isset( $_GET['author'] ) && '' !== $_GET['author'] ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );
add_action( 'template_redirect', function () {
	if ( ! is_admin() && is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 0 );

/* ---- 4. En-têtes de sécurité HTTP + retrait des divulgations ---- */
add_action( 'send_headers', function () {
	if ( headers_sent() ) return;
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	if ( function_exists( 'is_ssl' ) && is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
	}
	header_remove( 'X-Pingback' );
	header_remove( 'X-Powered-By' );
}, 99 );

/* ---- 5. Masque la version de WordPress ---- */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* ---- 6. Retire les liens de découverte inutiles (RSD/xmlrpc, manifest) ---- */
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
