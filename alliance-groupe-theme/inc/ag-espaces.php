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

		// Email de bienvenue brandé + lien sécurisé pour définir le mot de passe.
		$role_kind = ( 'ag_ambassadeur' === $role ) ? 'ambassadeur' : 'client';
		ag_send_member_welcome( get_user_by( 'id', $uid ), $role_kind );
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

/* ── 3b. Emails brandés (HTML) + expéditeur « Alliance Groupe » ────── */
// L'expéditeur par défaut « WordPress » devient « Alliance Groupe ».
add_filter( 'wp_mail_from_name', function ( $name ) {
	return ( 'WordPress' === $name || '' === $name ) ? 'Alliance Groupe' : $name;
}, 9 );
add_filter( 'wp_mail_from', function ( $email ) {
	return ( is_string( $email ) && strpos( $email, 'wordpress@' ) === 0 ) ? 'contact@alliancegroupe-inc.com' : $email;
}, 9 );

if ( ! function_exists( 'ag_email_wrap' ) ) {
	/** Gabarit HTML d'email à la marque (dark + or). */
	function ag_email_wrap( $heading, $inner ) {
		return '<!DOCTYPE html><html lang="fr"><body style="margin:0;padding:0;background:#0a0a0f;">'
			. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0a0a0f;padding:28px 12px;"><tr><td align="center">'
			. '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#14141c;border-radius:18px;overflow:hidden;border:1px solid rgba(212,180,92,.25);">'
			. '<tr><td style="padding:28px 36px;background:#0f0f16;border-bottom:1px solid rgba(212,180,92,.25);">'
			. '<div style="font-family:Georgia,serif;font-size:23px;font-weight:bold;color:#D4B45C;letter-spacing:1px;">ALLIANCE GROUPE</div>'
			. '<div style="font-family:Arial,sans-serif;font-size:11px;color:#9a9aa5;letter-spacing:2px;text-transform:uppercase;margin-top:4px;">Agence Web &amp; IA</div>'
			. '</td></tr>'
			. '<tr><td style="padding:34px 36px;font-family:Arial,sans-serif;color:#e8e8ee;font-size:15px;line-height:1.65;">'
			. '<h1 style="font-family:Georgia,serif;color:#ffffff;font-size:22px;margin:0 0 16px;">' . $heading . '</h1>'
			. $inner
			. '</td></tr>'
			. '<tr><td style="padding:20px 36px;background:#0f0f16;border-top:1px solid rgba(255,255,255,.06);font-family:Arial,sans-serif;color:#6f6f7a;font-size:12px;">'
			. 'Alliance Groupe — <a href="https://alliancegroupe-inc.com" style="color:#D4B45C;text-decoration:none;">alliancegroupe-inc.com</a><br>contact@alliancegroupe-inc.com'
			. '</td></tr>'
			. '</table></td></tr></table></body></html>';
	}
}
if ( ! function_exists( 'ag_email_button' ) ) {
	function ag_email_button( $label, $url ) {
		return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;"><tr><td style="border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);">'
			. '<a href="' . esc_url( $url ) . '" style="display:inline-block;padding:15px 34px;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;color:#10100a;text-decoration:none;border-radius:999px;">' . esc_html( $label ) . '</a>'
			. '</td></tr></table>';
	}
}
if ( ! function_exists( 'ag_send_member_welcome' ) ) {
	/** Email de bienvenue brandé avec lien sécurisé pour définir le mot de passe. */
	function ag_send_member_welcome( $user, $kind ) {
		if ( ! $user || ! $user->ID ) return;
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) { wp_new_user_notification( $user->ID, null, 'user' ); return; }
		$url  = add_query_arg( array( 'key' => $key, 'login' => $user->user_login ), home_url( '/mot-de-passe' ) );
		$name = $user->display_name ? $user->display_name : $user->user_login;

		if ( 'ambassadeur' === $kind ) {
			$heading = 'Bienvenue dans l\'équipe commerciale 🤝';
			$inner   = '<p>Bonjour ' . esc_html( $name ) . ',</p>'
				. '<p>Ton inscription au <strong>programme commercial Alliance Groupe</strong> est enregistrée. Tu touches <strong style="color:#D4B45C;">10 % sur chaque vente</strong> que tu réalises.</p>'
				. '<p><strong>Première étape :</strong> définis ton mot de passe pour accéder à ton espace (lien de vente, suivi des commissions, classement).</p>'
				. ag_email_button( 'Définir mon mot de passe', $url )
				. '<p style="color:#9a9aa5;font-size:13px;">Si le bouton ne marche pas, copie ce lien :<br><span style="color:#cfc7b8;word-break:break-all;">' . esc_url( $url ) . '</span></p>'
				. '<p style="color:#9a9aa5;font-size:13px;">La déclaration de ventes s\'active une fois ton identité validée. On te prévient.</p>';
		} else {
			$heading = 'Bienvenue chez Alliance Groupe 👋';
			$inner   = '<p>Bonjour ' . esc_html( $name ) . ',</p>'
				. '<p>Ton compte client est créé. Définis ton mot de passe pour accéder à ton <strong>espace</strong> et suivre ton projet.</p>'
				. ag_email_button( 'Définir mon mot de passe', $url )
				. '<p style="color:#9a9aa5;font-size:13px;">Si le bouton ne marche pas, copie ce lien :<br><span style="color:#cfc7b8;word-break:break-all;">' . esc_url( $url ) . '</span></p>';
		}

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Alliance Groupe <contact@alliancegroupe-inc.com>',
		);
		wp_mail( $user->user_email, 'Active ton compte Alliance Groupe', ag_email_wrap( $heading, $inner ), $headers );
	}
}

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
	if ( get_option( 'ag_espaces_pages_v3' ) ) return;
	$pages = array(
		'connexion'           => array( 'Connexion',          'templates/page-connexion.php' ),
		'espace-client'       => array( 'Espace Client',      'templates/page-espace-client.php' ),
		'espace-ambassadeur'  => array( 'Espace Commercial',  'templates/page-espace-ambassadeur.php' ),
		'classement'          => array( 'Classement',         'templates/page-classement.php' ),
		'mot-de-passe'        => array( 'Mot de passe',       'templates/page-mot-de-passe.php' ),
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
	update_option( 'ag_espaces_pages_v3', 1 );
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
if ( ! function_exists( 'ag_vente_ts' ) ) {
	/** Timestamp unix d'une vente (champ 'ts' sinon parse du champ 'date'). */
	function ag_vente_ts( $v ) {
		if ( ! empty( $v['ts'] ) ) return (int) $v['ts'];
		if ( ! empty( $v['date'] ) ) {
			$dt = DateTime::createFromFormat( 'd/m/Y H:i', $v['date'], wp_timezone() );
			if ( $dt ) return $dt->getTimestamp();
		}
		return 0;
	}
}
if ( ! function_exists( 'ag_ambassadeur_leaderboard' ) ) {
	/** Classement par CA (ventes validées + payées). $period : 'all' | 'month' | 'day'. */
	function ag_ambassadeur_leaderboard( $period = 'all' ) {
		$today = wp_date( 'Y-m-d' );
		$month = wp_date( 'Y-m' );
		$agg = array();
		foreach ( (array) get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			$st = $v['statut'] ?? '';
			if ( ! in_array( $st, array( 'validee', 'payee' ), true ) ) continue;
			if ( 'all' !== $period ) {
				$ts = ag_vente_ts( $v );
				if ( ! $ts ) continue;
				if ( 'day' === $period && wp_date( 'Y-m-d', $ts ) !== $today ) continue;
				if ( 'month' === $period && wp_date( 'Y-m', $ts ) !== $month ) continue;
			}
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

/* ── 9. Lien de vente (parrainage) + attribution automatique ───────── */
if ( ! function_exists( 'ag_ambassadeur_ref' ) ) {
	/** Code de parrainage stable de l'ambassadeur (généré + stocké si absent). */
	function ag_ambassadeur_ref( $email ) {
		$list = get_option( 'ag_ambassadeurs', array() );
		if ( ! is_array( $list ) ) return '';
		foreach ( $list as $k => $a ) {
			if ( isset( $a['email'] ) && strtolower( $a['email'] ) === strtolower( $email ) ) {
				if ( empty( $a['ref'] ) ) {
					$list[ $k ]['ref'] = strtoupper( substr( md5( $a['email'] . wp_salt() ), 0, 8 ) );
					update_option( 'ag_ambassadeurs', $list );
					return $list[ $k ]['ref'];
				}
				return $a['ref'];
			}
		}
		return '';
	}
}
if ( ! function_exists( 'ag_ambassadeur_by_ref' ) ) {
	function ag_ambassadeur_by_ref( $ref ) {
		$ref = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $ref ) );
		if ( '' === $ref ) return null;
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( ! empty( $a['ref'] ) && strtoupper( $a['ref'] ) === $ref ) return $a;
		}
		return null;
	}
}
if ( ! function_exists( 'ag_ambassadeur_sale_link' ) ) {
	/** Lien de vente à partager par l'ambassadeur. */
	function ag_ambassadeur_sale_link( $email ) {
		$ref = ag_ambassadeur_ref( $email );
		return $ref ? add_query_arg( 'ref', $ref, home_url( '/sites-express' ) ) : home_url( '/sites-express' );
	}
}

// Mémorise le code de parrainage en cookie (30 jours) dès qu'un visiteur arrive via un lien.
add_action( 'init', function () {
	if ( empty( $_GET['ref'] ) ) return;
	$ref = preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['ref'] ) );
	if ( $ref && ! headers_sent() ) {
		setcookie( 'ag_ref', $ref, time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
} );

// Attribution automatique : à l'envoi du brief, si un cookie de parrainage existe,
// crée une vente (en attente) au crédit de l'ambassadeur — sans déclaration manuelle.
add_action( 'ag_client_brief_submitted', function ( $email, $name, $pack = '' ) {
	$ref = isset( $_COOKIE['ag_ref'] ) ? $_COOKIE['ag_ref'] : '';
	$amb = ag_ambassadeur_by_ref( $ref );
	if ( ! $amb ) return;
	if ( isset( $amb['email'] ) && strtolower( $amb['email'] ) === strtolower( $email ) ) return; // pas d'auto-parrainage
	$prices  = apply_filters( 'ag_express_prices', array( 'essentiel' => 490, 'pro' => 890, 'boutique' => 1490 ) );
	$montant = isset( $prices[ $pack ] ) ? (float) $prices[ $pack ] : 0;
	if ( $montant <= 0 ) return;
	$rate   = defined( 'AG_COMMISSION_RATE' ) ? AG_COMMISSION_RATE : 0.10;
	$ventes = get_option( 'ag_ambassadeur_ventes', array() );
	if ( ! is_array( $ventes ) ) $ventes = array();
	$ventes[] = array(
		'id'            => uniqid( 'v_' ),
		'email'         => $amb['email'],
		'name'          => $amb['name'] ?? $amb['email'],
		'client'        => $name . ' (' . $email . ')',
		'activite'      => 'Site Express ' . ucfirst( $pack ),
		'montant'       => $montant,
		'commission'    => round( $montant * $rate, 2 ),
		'statut'        => 'declaree',
		'source'        => 'lien',
		'date'          => current_time( 'd/m/Y H:i' ),
		'ts'            => time(),
		'date_paiement' => '',
	);
	update_option( 'ag_ambassadeur_ventes', $ventes );
	wp_mail(
		'contact@alliancegroupe-inc.com',
		'Vente via lien ambassadeur : ' . ( $amb['name'] ?? '' ),
		"Attribution automatique (à valider après encaissement PayPal)\n\nAmbassadeur : " . ( $amb['name'] ?? '' ) . " <" . $amb['email'] . ">\nClient : $name <$email>\nPack : $pack ($montant €)\nCommission : " . round( $montant * $rate, 2 ) . " €\nDate : " . current_time( 'd/m/Y H:i' )
	);
}, 10, 3 );

/* ── 10. Page mot de passe sur-mesure + blocage wp-login / wp-admin ── */

// Traitement des formulaires de /mot-de-passe (avant tout affichage).
add_action( 'template_redirect', function () {
	if ( ! is_page_template( 'templates/page-mot-de-passe.php' ) ) return;

	// (a) Demande de réinitialisation (mot de passe oublié)
	if ( ! empty( $_POST['ag_lost_submit'] ) ) {
		if ( isset( $_POST['ag_lost_nonce'] ) && wp_verify_nonce( $_POST['ag_lost_nonce'], 'ag_lost' ) ) {
			$email = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
			$user  = $email ? get_user_by( 'email', $email ) : false;
			if ( $user && function_exists( 'ag_email_wrap' ) ) {
				$key = get_password_reset_key( $user );
				if ( ! is_wp_error( $key ) ) {
					$url   = add_query_arg( array( 'key' => $key, 'login' => $user->user_login ), home_url( '/mot-de-passe' ) );
					$inner = '<p>Bonjour ' . esc_html( $user->display_name ) . ',</p>'
						. '<p>Tu as demandé à réinitialiser ton mot de passe. Clique sur le bouton ci-dessous.</p>'
						. ag_email_button( 'Choisir un nouveau mot de passe', $url )
						. '<p style="color:#9a9aa5;font-size:13px;">Si tu n\'es pas à l\'origine de cette demande, ignore cet email.</p>';
					wp_mail( $user->user_email, 'Réinitialiser ton mot de passe — Alliance Groupe', ag_email_wrap( 'Réinitialisation du mot de passe', $inner ), array( 'Content-Type: text/html; charset=UTF-8', 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' ) );
				}
			}
			wp_safe_redirect( add_query_arg( 'lost', 'sent', home_url( '/mot-de-passe' ) ) ); exit;
		}
	}

	// (b) Définition du nouveau mot de passe (via clé sécurisée)
	if ( ! empty( $_POST['ag_setpass_submit'] ) ) {
		if ( isset( $_POST['ag_setpass_nonce'] ) && wp_verify_nonce( $_POST['ag_setpass_nonce'], 'ag_setpass' ) ) {
			$login = sanitize_text_field( wp_unslash( $_POST['login'] ?? '' ) );
			$key   = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
			$p1    = (string) ( $_POST['pass1'] ?? '' );
			$p2    = (string) ( $_POST['pass2'] ?? '' );
			$base  = add_query_arg( array( 'key' => $key, 'login' => $login ), home_url( '/mot-de-passe' ) );
			$user  = check_password_reset_key( $key, $login );
			if ( is_wp_error( $user ) ) { wp_safe_redirect( add_query_arg( 'pw', 'expired', home_url( '/mot-de-passe' ) ) ); exit; }
			if ( strlen( $p1 ) < 8 ) { wp_safe_redirect( add_query_arg( 'pw', 'short', $base ) ); exit; }
			if ( $p1 !== $p2 ) { wp_safe_redirect( add_query_arg( 'pw', 'mismatch', $base ) ); exit; }
			reset_password( $user, $p1 );
			wp_safe_redirect( add_query_arg( 'reset', 'ok', home_url( '/connexion' ) ) ); exit;
		}
	}
} );

// Bloque l'accès direct à wp-login.php : tout est redirigé vers les pages custom
// (on laisse passer la déconnexion et la protection par mot de passe d'article).
add_action( 'login_init', function () {
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : 'login';
	if ( in_array( $action, array( 'logout', 'postpass' ), true ) ) return;
	if ( in_array( $action, array( 'rp', 'resetpass' ), true ) ) {
		$key   = isset( $_REQUEST['key'] ) ? wp_unslash( $_REQUEST['key'] ) : '';
		$login = isset( $_REQUEST['login'] ) ? wp_unslash( $_REQUEST['login'] ) : '';
		wp_safe_redirect( add_query_arg( array( 'key' => $key, 'login' => $login ), home_url( '/mot-de-passe' ) ) ); exit;
	}
	if ( 'lostpassword' === $action || 'retrievepassword' === $action ) {
		wp_safe_redirect( home_url( '/mot-de-passe?action=lost' ) ); exit;
	}
	wp_safe_redirect( home_url( '/connexion' ) ); exit;
} );

// Bloque wp-admin pour les non-admins -> leur espace.
add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_options' ) ) return;
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;
	$pagenow = $GLOBALS['pagenow'] ?? '';
	if ( in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) return;
	wp_safe_redirect( is_user_logged_in() ? ag_espace_url() : home_url( '/connexion' ) ); exit;
} );

// Masque la barre d'admin WordPress pour les non-admins.
add_filter( 'show_admin_bar', function ( $show ) {
	return current_user_can( 'manage_options' ) ? $show : false;
} );
