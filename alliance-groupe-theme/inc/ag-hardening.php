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
	// CSP « douce » : force les ressources en HTTPS sans rien bloquer (0 risque de casse).
	header( 'Content-Security-Policy: upgrade-insecure-requests' );
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

/* ---- 7. Durcissement .htaccess (niveau serveur, ce que PHP ne peut pas faire) :
 *        désactive le LISTING de répertoires (uploads, wp-content, wp-includes)
 *        et bloque readme.txt / license.txt / wp-config / .env / .git (fuite de version).
 *        Écrit UNE fois, dans un bloc balisé (réversible), seulement si le .htaccess
 *        est accessible en écriture. Gardes IfModule → jamais de 500 (Apache 2.2/2.4). */
add_action( 'admin_init', function () {
	if ( '3' === (string) get_option( 'ag_htaccess_hard', '' ) ) return; // déjà appliqué
	if ( ! function_exists( 'get_home_path' ) )       require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( ! function_exists( 'insert_with_markers' ) ) require_once ABSPATH . 'wp-admin/includes/misc.php';
	$home = function_exists( 'get_home_path' ) ? get_home_path() : ABSPATH;
	$file = rtrim( $home, '/\\' ) . '/.htaccess';
	if ( ! file_exists( $file ) || ! is_writable( $file ) ) {
		update_option( 'ag_htaccess_hard_note', 'manuel', false ); // à ajouter à la main (voir Sécurité)
		return;
	}
	$rules = array(
		'Options -Indexes',
		'<FilesMatch "(?i)^(readme\.html|readme\.txt|license\.txt|licence\.txt|wp-config\.php|\.env|\.git.*)$">',
		'<IfModule mod_authz_core.c>',
		'Require all denied',
		'</IfModule>',
		'<IfModule !mod_authz_core.c>',
		'Order allow,deny',
		'Deny from all',
		'</IfModule>',
		'</FilesMatch>',
	);
	if ( function_exists( 'insert_with_markers' ) && insert_with_markers( $file, 'AG Hardening', $rules ) ) {
		update_option( 'ag_htaccess_hard', '3', false );
		delete_option( 'ag_htaccess_hard_note' );
	}
} );

/* Petit rappel admin si le .htaccess n'était pas modifiable (à coller à la main). */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( 'manuel' !== (string) get_option( 'ag_htaccess_hard_note', '' ) ) return;
	echo '<div class="notice notice-warning"><p><strong>Sécurité :</strong> le fichier <code>.htaccess</code> n\'a pas pu être durci automatiquement (droits en écriture). Ajoute ces lignes en haut de ton <code>.htaccess</code> pour bloquer le listing de répertoires et la fuite de version :</p>'
		. '<pre style="background:#fff;padding:10px;border:1px solid #ccd0d4;">Options -Indexes' . "\n" . '&lt;FilesMatch "(?i)^(readme\.html|readme\.txt|license\.txt|wp-config\.php|\.env)$"&gt;' . "\n" . '  Require all denied' . "\n" . '&lt;/FilesMatch&gt;</pre></div>';
} );
