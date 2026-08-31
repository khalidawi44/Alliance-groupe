<?php
/**
 * AG Redirections — 301 des anciennes pages vers une page vivante.
 *
 * But : sortir du site les pages obsolètes SANS créer de 404 ni perdre le SEO.
 * On redirige par CHEMIN (marche même si la page est mise à la corbeille ensuite).
 *
 * ┌─ POUR AJOUTER / RETIRER UNE REDIRECTION ──────────────────────────────────┐
 * │ Édite $AG_REDIR ci-dessous : 'ancien-slug' => '/page-de-destination'.      │
 * │ Réversible : retire la ligne et l'ancienne page redevient accessible.      │
 * │ NE JAMAIS rediriger une page interne encore utilisée (connexion, espace-   │
 * │ client, paiement, prospection…).                                           │
 * └────────────────────────────────────────────────────────────────────────────┘
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_redirections' ) ) {
	function ag_redirections() {
		return array(
			// Ancien slug (sans /)      => destination vivante
			'le-voyage'         => '/a-propos',
			'pourquoi-alliance' => '/a-propos',
			'programme-racines' => '/ambassadeurs',
			'jeune-avocat'      => '/wordpress-avocat',
			'guide-avocat'      => '/wordpress-avocat',
		);
	}
}

add_action( 'template_redirect', function () {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
	$slug = trim( $path, '/' );
	if ( '' === $slug ) {
		return;
	}
	$map = ag_redirections();
	if ( isset( $map[ $slug ] ) ) {
		$dest = home_url( $map[ $slug ] );
		// Sécurité : jamais de boucle (la destination ne doit pas être un slug redirigé).
		if ( untrailingslashit( $dest ) !== untrailingslashit( home_url( '/' . $slug ) ) ) {
			wp_safe_redirect( $dest, 301 );
			exit;
		}
	}
}, 1 );

// ── Chargement du module de nettoyage de l'index (noindex des pages
//    utilitaires/internes + archives fines). Chargé ici car ce fichier inc/
//    est déjà requis par functions.php ET suivi par la synchro auto.
$ag_noindex_mod = get_stylesheet_directory() . '/inc/ag-seo-noindex.php';
if ( file_exists( $ag_noindex_mod ) ) {
	require_once $ag_noindex_mod;
}

// ── Articles longform : étoffe le contenu d'articles existants (profondeur SEO).
$ag_longform_mod = get_stylesheet_directory() . '/inc/ag-articles-longform.php';
if ( file_exists( $ag_longform_mod ) ) {
	require_once $ag_longform_mod;
}
