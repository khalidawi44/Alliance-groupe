<?php
/**
 * Espaces membres — Clients & Commerciaux (ambassadeurs).
 *
 * S'appuie sur les comptes WordPress natifs (auth sécurisée, mots de passe
 * chiffrés, réinitialisation). Crée 2 rôles + 3 pages (connexion, espace
 * client, espace commercial), protège ces pages, et crée automatiquement
 * un compte quand un ambassadeur s'inscrit / un client envoie un brief.
 *
 * Données métier déjà existantes (réutilisées) :
 *   ag_ambassadeurs        => inscrits programme commercial
 *   ag_ambassadeur_ventes  => ventes déclarées
 *   ag_express_briefs      => briefs clients Sites Express
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── 1. Rôles ──────────────────────────────────────────────────────── */
add_action( 'after_setup_theme', function () {
	if ( ! get_role( 'ag_client' ) ) {
		add_role( 'ag_client', 'Client', array( 'read' => true ) );
	}
	if ( ! get_role( 'ag_ambassadeur' ) ) {
		add_role( 'ag_ambassadeur', 'Commercial (ambassadeur)', array( 'read' => true ) );
	}
} );

/* ── 2. Helpers ────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_espace_member_kind' ) ) {
	/** Retourne 'admin' | 'ambassadeur' | 'client' | 'other' | '' */
	function ag_espace_member_kind( $user = null ) {
		$user = $user ?: wp_get_current_user();
		if ( ! $user || ! $user->ID ) return '';
		if ( user_can( $user, 'manage_options' ) ) return 'admin';
		$roles = (array) $user->roles;
		if ( in_array( 'ag_ambassadeur', $roles, true ) ) return 'ambassadeur';
		if ( in_array( 'ag_client', $roles, true ) )      return 'client';
		return 'other';
	}
}
if ( ! function_exists( 'ag_espace_url' ) ) {
	function ag_espace_url( $kind = null ) {
		$kind = $kind ?: ag_espace_member_kind();
		if ( 'ambassadeur' === $kind ) return home_url( '/espace-ambassadeur' );
		if ( 'admin' === $kind )       return admin_url();
		return home_url( '/espace-client' );
	}
}
if ( ! function_exists( 'ag_ambassadeur_record' ) ) {
	function ag_ambassadeur_record( $email ) {
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( isset( $a['email'] ) && strtolower( $a['email'] ) === strtolower( $email ) ) return $a;
		}
		return null;
	}
}
if ( ! function_exists( 'ag_ambassadeur_ventes_for' ) ) {
	function ag_ambassadeur_ventes_for( $email ) {
		$out = array();
		foreach ( (array) get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			if ( isset( $v['email'] ) && strtolower( $v['email'] ) === strtolower( $email ) ) $out[] = $v;
		}
		return array_reverse( $out );
	}
}
if ( ! function_exists( 'ag_client_briefs_for' ) ) {
	function ag_client_briefs_for( $email ) {
		$out = array();
		foreach ( (array) get_option( 'ag_express_briefs', array() ) as $b ) {
			if ( isset( $b['email'] ) && strtolower( $b['email'] ) === strtolower( $email ) ) $out[] = $b;
		}
		return array_reverse( $out );
	}
}

/* ── 3. Création d'un compte membre + email "définir le mot de passe" ── */
if ( ! function_exists( 'ag_create_member' ) ) {
	function ag_create_member( $email, $name, $role ) {
		if ( ! is_email( $email ) ) return 0;
		$existing = get_user_by( 'email', $email );
		if ( $existing ) {
			// On n'altère jamais un admin ; sinon on s'assure du bon rôle.
			if ( ! user_can( $existing, 'manage_options' ) && ! in_array( $role, (array) $existing->roles, true ) ) {
				$existing->add_role( $role );
			}
			return $existing->ID;
		}
		$base  = sanitize_user( current( explode( '@', $email ) ), true );
		if ( '' === $base ) $base = 'membre';
		$login = $base;
		$n = 1;
		while ( username_exists( $login ) ) { $login = $base . $n; $n++; }

		$uid = wp_insert_user( array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24 ),
			'display_name' => $name ? $name : $login,
			'first_name'   => $name,
			'role'         => $role,
		) );
		if ( is_wp_error( $uid ) ) return 0;

		// Email standard WP : lien sécurisé pour définir son mot de passe.
		wp_new_user_notification( $uid, null, 'user' );
		return $uid;
	}
}

// Branche sur les flux existants (déclenchés dans ag-ambassadeurs.php / functions.php)
add_action( 'ag_ambassadeur_registered', function ( $email, $name ) {
	ag_create_member( $email, $name, 'ag_ambassadeur' );
}, 10, 2 );
add_action( 'ag_client_brief_submitted', function ( $email, $name ) {
	ag_create_member( $email, $name, 'ag_client' );
}, 10, 2 );

/* ── 4. Connexion (traitement du formulaire de /connexion) ─────────── */
add_action( 'template_redirect', function () {
	if ( empty( $_POST['ag_login_submit'] ) ) return;
	$back = home_url( '/connexion' );
	if ( ! isset( $_POST['ag_login_nonce'] ) || ! wp_verify_nonce( $_POST['ag_login_nonce'], 'ag_login' ) ) {
		wp_safe_redirect( add_query_arg( 'login', 'nonce', $back ) ); exit;
	}
	$creds = array(
		'user_login'    => sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) ),
		'user_password' => (string) ( $_POST['pwd'] ?? '' ),
		'remember'      => ! empty( $_POST['rememberme'] ),
	);
	$user = wp_signon( $creds, is_ssl() );
	if ( is_wp_error( $user ) ) {
		wp_safe_redirect( add_query_arg( 'login', 'failed', $back ) ); exit;
	}
	wp_set_current_user( $user->ID );
	$dest = '';
	if ( ! empty( $_POST['redirect_to'] ) ) {
		$dest = wp_validate_redirect( wp_unslash( $_POST['redirect_to'] ), '' );
	}
	if ( ! $dest ) $dest = ag_espace_url( ag_espace_member_kind( $user ) );
	wp_safe_redirect( $dest ); exit;
} );

/* ── 5. Protection des pages d'espace ──────────────────────────────── */
add_action( 'template_redirect', function () {
	// Déjà connecté sur /connexion -> direction son espace
	if ( is_page_template( 'templates/page-connexion.php' ) && is_user_logged_in() ) {
		wp_safe_redirect( ag_espace_url() ); exit;
	}
	$amb = is_page_template( 'templates/page-espace-ambassadeur.php' );
	$cli = is_page_template( 'templates/page-espace-client.php' );
	if ( ! $amb && ! $cli ) return;

	if ( ! is_user_logged_in() ) {
		$login = add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), home_url( '/connexion' ) );
		wp_safe_redirect( $login ); exit;
	}
	if ( $amb ) {
		$k = ag_espace_member_kind();
		if ( 'ambassadeur' !== $k && 'admin' !== $k ) {
			wp_safe_redirect( ag_espace_url( $k ) ); exit;
		}
	}
} );

/* ── 6. Redirection après connexion via wp-login.php (sécurité) ─────── */
add_filter( 'login_redirect', function ( $redirect, $requested, $user ) {
	if ( $user instanceof WP_User && ! user_can( $user, 'manage_options' ) ) {
		return ag_espace_url( ag_espace_member_kind( $user ) );
	}
	return $redirect;
}, 10, 3 );

/* ── 7. Auto-création des pages (une seule fois) ───────────────────── */
add_action( 'init', function () {
	if ( get_option( 'ag_espaces_pages_v1' ) ) return;
	$pages = array(
		'connexion'           => array( 'Connexion',          'templates/page-connexion.php' ),
		'espace-client'       => array( 'Espace Client',      'templates/page-espace-client.php' ),
		'espace-ambassadeur'  => array( 'Espace Commercial',  'templates/page-espace-ambassadeur.php' ),
	);
	foreach ( $pages as $slug => $d ) {
		if ( get_page_by_path( $slug ) ) continue;
		$id = wp_insert_post( array(
			'post_title'   => $d[0],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'page_template'=> $d[1],
		) );
	}
	update_option( 'ag_espaces_pages_v1', 1 );
} );

/* ── 8. Classement & récompenses (gamification commerciale) ────────── */
if ( ! function_exists( 'ag_ambassadeur_tiers' ) ) {
	/**
	 * Paliers de récompense (indicatifs, ajustables via le filtre).
	 * Les primes/cadeaux sont attribués par l'admin (non auto-calculés).
	 */
	function ag_ambassadeur_tiers() {
		return apply_filters( 'ag_ambassadeur_tiers', array(
			array( 'key' => 'bronze',  'label' => 'Bronze',  'emoji' => '🥉', 'min_ca' => 0,     'reward' => '10 % sur chaque vente + tous les outils de vente' ),
			array( 'key' => 'argent',  'label' => 'Argent',  'emoji' => '🥈', 'min_ca' => 3000,  'reward' => 'Prime de 100 € + mise en avant' ),
			array( 'key' => 'or',      'label' => 'Or',      'emoji' => '🥇', 'min_ca' => 10000, 'reward' => 'Prime de 300 € + leads fournis' ),
			array( 'key' => 'platine', 'label' => 'Platine', 'emoji' => '💎', 'min_ca' => 25000, 'reward' => 'Prime de 800 € + cadeau + statut VIP' ),
		) );
	}
}
if ( ! function_exists( 'ag_ambassadeur_tier_for' ) ) {
	function ag_ambassadeur_tier_for( $ca ) {
		$tiers = ag_ambassadeur_tiers();
		$cur   = $tiers[0];
		foreach ( $tiers as $t ) { if ( (float) $ca >= (float) $t['min_ca'] ) $cur = $t; }
		return $cur;
	}
}
if ( ! function_exists( 'ag_ambassadeur_short_name' ) ) {
	/** "Karim Benali" -> "Karim B." (respect vie privée pour l'affichage public). */
	function ag_ambassadeur_short_name( $full ) {
		$full = trim( (string) $full );
		if ( '' === $full ) return 'Ambassadeur';
		$parts = preg_split( '/\s+/', $full );
		$first = $parts[0];
		$init  = ( count( $parts ) > 1 ) ? mb_strtoupper( mb_substr( end( $parts ), 0, 1 ) ) . '.' : '';
		return trim( $first . ' ' . $init );
	}
}
if ( ! function_exists( 'ag_ambassadeur_leaderboard' ) ) {
	/** Classement par CA généré (ventes validées + payées), du + au -. */
	function ag_ambassadeur_leaderboard() {
		$agg = array();
		foreach ( (array) get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			$st = $v['statut'] ?? '';
			if ( ! in_array( $st, array( 'validee', 'payee' ), true ) ) continue;
			$k = strtolower( $v['email'] ?? '' );
			if ( '' === $k ) continue;
			if ( ! isset( $agg[ $k ] ) ) {
				$agg[ $k ] = array( 'email' => $v['email'], 'name' => $v['name'] ?: $v['email'], 'ca' => 0, 'ventes' => 0, 'commission' => 0 );
			}
			$agg[ $k ]['ca']         += (float) $v['montant'];
			$agg[ $k ]['ventes']     += 1;
			$agg[ $k ]['commission'] += (float) $v['commission'];
		}
		$agg = array_values( $agg );
		usort( $agg, function ( $a, $b ) { return $b['ca'] <=> $a['ca']; } );
		$rank = 1;
		foreach ( $agg as &$row ) { $row['rank'] = $rank++; }
		unset( $row );
		return $agg;
	}
}
