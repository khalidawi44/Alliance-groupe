<?php
/**
 * AG — Durcissement securite (livre avec le theme).
 *
 * xmlrpc, enumeration auteur/REST, en-tetes de securite, versions (WP + plugins).
 * 100% defensif, aucune incidence visiteur.
 *
 * @package AG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * VERROU. Un meme site peut recevoir plusieurs de nos briques a la fois : le
 * theme, son pack Premium, son pack Business. Chacune embarque ce fichier.
 * Sans ce verrou, `ag_hard_strip_ver()` serait declaree deux fois et le site
 * tomberait en erreur fatale des l'activation du second pack. Le premier
 * charge fait le travail, les suivants s'effacent.
 */
if ( defined( 'AG_HARDENING_LOADED' ) ) {
	return;
}
define( 'AG_HARDENING_LOADED', '1.0.0' );

/* 1. xmlrpc.php bloque (force brute + DDoS pingback) */
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
add_filter( 'xmlrpc_methods', function ( $m ) {
	unset( $m['pingback.ping'], $m['pingback.extensions.getPingbacks'] );
	return $m;
} );

/* 2. Enumeration des comptes via REST (non connectes) */
add_filter( 'rest_endpoints', function ( $e ) {
	if ( ! is_user_logged_in() ) {
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $r ) {
			if ( isset( $e[ $r ] ) ) {
				unset( $e[ $r ] );
			}
		}
	}
	return $e;
} );

/* 3. Enumeration d'auteur ?author=N + /author/ (fuite du login) */
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

/* 4. En-tetes de securite + retrait divulgations */
add_action( 'send_headers', function () {
	if ( headers_sent() ) {
		return;
	}
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

/* 5. Masque la version de WordPress */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* 6. Retire RSD / manifest */
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

/*
 * 7. Retire ?ver= des CSS/JS pour les VISITEURS (masque les versions de
 * plugins, qui indiquent a un attaquant quelles failles connues tenter).
 *
 * A SAVOIR : ce parametre sert aussi de casse-cache. Sans lui, le navigateur
 * d'un visiteur deja venu peut garder l'ancienne feuille de style apres une
 * mise a jour, jusqu'a expiration de son cache. Les personnes CONNECTEES
 * gardent le ?ver= : cote administration, on voit donc toujours le site a
 * jour. En cas de doute apres une mise a jour, purger le cache du site.
 */
if ( ! function_exists( 'ag_hard_strip_ver' ) ) {
	function ag_hard_strip_ver( $src ) {
		if ( is_user_logged_in() ) {
			return $src;
		}
		if ( $src && false !== strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}
}
add_filter( 'style_loader_src', 'ag_hard_strip_ver', 9999 );
add_filter( 'script_loader_src', 'ag_hard_strip_ver', 9999 );
