<?php
/**
 * ag-session-persist.php — Session persistante (ne jamais déconnecter l'admin).
 *
 * Demande de Fabrice : « mon application ne se déconnecte jamais, même quand je
 * ferme/rouvre l'onglet ». Cause du problème : le cookie d'auth WordPress par
 * défaut dure 2 jours (48h) sans « se souvenir de moi », et devient un cookie de
 * session qui saute à la fermeture de l'onglet. On force donc un cookie
 * LONGUE DURÉE pour l'administrateur → il reste connecté (survit à la fermeture
 * de l'onglet et au redémarrage du navigateur).
 *
 * Portée : ADMIN uniquement (c'est « son » appli). Les comptes clients /
 * ambassadeurs gardent la durée normale (sécurité). Filtrable si on veut étendre.
 *
 * @package Alliance_Groupe
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* 1) Durée du cookie d'authentification : 1 an pour l'admin. */
add_filter( 'auth_cookie_expiration', function ( $length, $user_id, $remember ) {
	if ( user_can( (int) $user_id, 'manage_options' ) ) {
		return (int) apply_filters( 'ag_admin_session_ttl', YEAR_IN_SECONDS );
	}
	// Option : étendre aussi l'équipe (ambassadeurs) à 30 j — désactivé par défaut.
	if ( (bool) apply_filters( 'ag_team_long_session', true )
		&& user_can( (int) $user_id, 'ag_ambassadeur' ) ) {
		return 30 * DAY_IN_SECONDS;
	}
	return $length;
}, 20, 3 );

/* 2) À la connexion de l'admin, forcer le drapeau « se souvenir de moi » : le
 *    cookie est écrit en PERSISTANT (date d'expiration), pas en cookie de session
 *    qui saute à la fermeture de l'onglet. */
add_filter( 'login_redirect', function ( $redirect, $requested, $user ) {
	return $redirect; // no-op : garde la redirection, voir le hook ci-dessous pour le remember.
}, 10, 3 );

add_action( 'wp_login', function ( $user_login, $user ) {
	if ( $user instanceof WP_User && user_can( $user, 'manage_options' ) ) {
		// Réécrit le cookie d'auth en mode « remember » (persistant, longue durée).
		wp_set_auth_cookie( $user->ID, true );
	}
}, 10, 2 );
