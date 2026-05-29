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

// Autorise les liens sms: (boutons de partage) que WordPress bloque par défaut.
add_filter( 'kses_allowed_protocols', function ( $protocols ) {
	$protocols[] = 'sms';
	return $protocols;
} );

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
			$amb_zones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $user->user_email ) : array();
			$dn        = function_exists( 'ag_dept_names' ) ? ag_dept_names() : array();
			$zone_html = ! empty( $amb_zones ) ? '<p>📍 <strong>Ta zone de prospection : ' . esc_html( $amb_zones[0] . ( isset( $dn[ $amb_zones[0] ] ) ? ' — ' . $dn[ $amb_zones[0] ] : '' ) ) . '</strong>. Les prospects de cette zone te sont envoyés <strong>automatiquement</strong>. Tu peux en changer dans ton espace (1 seule zone par ambassadeur).</p>' : '';
			$heading = 'Bienvenue dans l\'équipe commerciale 🤝';
			$inner   = '<p>Bonjour ' . esc_html( $name ) . ',</p>'
				. '<p>Ton inscription au <strong>programme commercial Alliance Groupe</strong> est enregistrée. Tu touches <strong style="color:#D4B45C;">10 % sur chaque vente</strong> que tu réalises.</p>'
				. '<p><strong>Première étape :</strong> définis ton mot de passe pour accéder à ton espace (lien de vente, suivi des commissions, classement).</p>'
				. ag_email_button( 'Définir mon mot de passe', $url )
				. '<p style="color:#9a9aa5;font-size:13px;">Si le bouton ne marche pas, copie ce lien :<br><span style="color:#cfc7b8;word-break:break-all;">' . esc_url( $url ) . '</span></p>'
				. '<p>📚 <strong>Découvre ton programme</strong> : comment vendre des sites et recruter ton équipe, étape par étape — <a href="' . esc_url( home_url( '/programme-ambassadeur' ) ) . '" style="color:#D4B45C;">ouvre le guide</a>.</p>'
				. ( ( function_exists( 'ag_tg_cfg' ) && ag_tg_cfg( 'group_link' ) ) ? '<p><strong>Étape 2 (obligatoire) :</strong> rejoins le <strong>groupe Telegram des ambassadeurs</strong> (annonces, entraide, prospects) :</p>' . ag_email_button( 'Rejoindre le groupe (obligatoire)', ag_tg_cfg( 'group_link' ) ) : '<p><strong>Étape 2 (obligatoire) :</strong> rejoins le groupe Telegram des ambassadeurs — le lien d\'invitation arrive très vite.</p>' )
				. $zone_html
				. '<p style="color:#9a9aa5;font-size:13px;">La déclaration de ventes s\'active une fois ton identité validée. On te prévient.</p>';
		} else {
			$heading = 'Bienvenue chez Alliance Groupe 👋';
			$inner   = '<p>Bonjour ' . esc_html( $name ) . ',</p>'
				. '<p>Ton compte client est créé. Définis ton mot de passe pour accéder à ton <strong>espace</strong> et suivre ton projet.</p>'
				. ag_email_button( 'Définir mon mot de passe', $url )
				. '<p style="color:#9a9aa5;font-size:13px;">Si le bouton ne marche pas, copie ce lien :<br><span style="color:#cfc7b8;word-break:break-all;">' . esc_url( $url ) . '</span></p>'
				. ( ( function_exists( 'ag_tg_cfg' ) && ag_tg_cfg( 'chan_link' ) ) ? '<p>📣 <strong>Rejoins notre canal Telegram</strong> pour nos offres et nouveautés :</p>' . ag_email_button( 'Rejoindre le canal', ag_tg_cfg( 'chan_link' ) ) : '' );
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
	$user = wp_signon( $creds, true ); // cookie secure forcé (site HTTPS)
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
	$amb = is_page_template( 'templates/page-espace-ambassadeur.php' ) || is_page_template( 'templates/page-guide-ambassadeur.php' );
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
	if ( get_option( 'ag_espaces_pages_v5' ) ) return;
	$pages = array(
		'connexion'             => array( 'Connexion',            'templates/page-connexion.php' ),
		'espace-client'         => array( 'Espace Client',        'templates/page-espace-client.php' ),
		'espace-ambassadeur'    => array( 'Espace Commercial',    'templates/page-espace-ambassadeur.php' ),
		'classement'            => array( 'Classement',           'templates/page-classement.php' ),
		'mot-de-passe'          => array( 'Mot de passe',         'templates/page-mot-de-passe.php' ),
		'studio'                => array( 'Studio',               'templates/page-studio.php' ),
		'programme-ambassadeur' => array( 'Programme Ambassadeur', 'templates/page-guide-ambassadeur.php' ),
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
	update_option( 'ag_espaces_pages_v5', 1 );
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
		// Filtre d'injection (ex. ambassadeurs démo pour montrer du monde
		// quand le site démarre — cf. inc/ag-demo-board.php).
		return apply_filters( 'ag_ambassadeur_leaderboard', $agg, $period );
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
if ( ! function_exists( 'ag_ambassadeur_parrain_link' ) ) {
	/** Lien de RECRUTEMENT (parrainage) à partager pour recruter d'autres ambassadeurs. */
	function ag_ambassadeur_parrain_link( $email ) {
		$ref = ag_ambassadeur_ref( $email );
		return $ref ? add_query_arg( 'parrain', $ref, home_url( '/ambassadeurs' ) ) : home_url( '/ambassadeurs' );
	}
}
if ( ! function_exists( 'ag_ensure_ambassador_for_user' ) ) {
	/** Garantit qu'un utilisateur (ex. l'admin) a une fiche ambassadeur -> il obtient un code de parrainage et compte au classement. Retourne le ref. */
	function ag_ensure_ambassador_for_user( $user ) {
		if ( ! $user || ! $user->ID ) return '';
		$email = $user->user_email;
		if ( ! is_email( $email ) ) return '';
		if ( ! ag_ambassadeur_record( $email ) ) {
			$list = get_option( 'ag_ambassadeurs', array() );
			if ( ! is_array( $list ) ) $list = array();
			$list[] = array(
				'email'  => $email,
				'name'   => $user->display_name ? $user->display_name : $user->user_login,
				'status' => 'actif',
				'ref'    => '',
				'date'   => current_time( 'd/m/Y H:i' ),
			);
			update_option( 'ag_ambassadeurs', $list );
		}
		return ag_ambassadeur_ref( $email );
	}
}

// Mémorise les codes en cookie (30 jours) : ?ref= (vente) et ?parrain= (recrutement).
add_action( 'init', function () {
	if ( ! empty( $_GET['ref'] ) ) {
		$ref = preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['ref'] ) );
		if ( $ref && ! headers_sent() ) setcookie( 'ag_ref', $ref, time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
	if ( ! empty( $_GET['parrain'] ) ) {
		$p = preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['parrain'] ) );
		if ( $p && ! headers_sent() ) setcookie( 'ag_parrain', $p, time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
} );

/* ── 9b. Parrainage : recruter une équipe + override sur leurs ventes ── */
if ( ! function_exists( 'ag_ambassadeur_recruit_link' ) ) {
	/** Lien à partager pour recruter de nouveaux ambassadeurs sous son parrainage. */
	function ag_ambassadeur_recruit_link( $email ) {
		$ref = ag_ambassadeur_ref( $email );
		return $ref ? add_query_arg( 'parrain', $ref, home_url( '/ambassadeurs' ) ) : home_url( '/ambassadeurs' );
	}
}
if ( ! function_exists( 'ag_ambassadeur_filleuls' ) ) {
	/** Ambassadeurs recrutés par ce parrain (par son code ref). */
	function ag_ambassadeur_filleuls( $parrain_ref ) {
		$out = array();
		$parrain_ref = strtoupper( (string) $parrain_ref );
		if ( '' === $parrain_ref ) return $out;
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( ! empty( $a['parrain'] ) && strtoupper( $a['parrain'] ) === $parrain_ref ) $out[] = $a;
		}
		return $out;
	}
}
if ( ! function_exists( 'ag_override_rate' ) ) {
	/** Taux de parrainage effectif (option modifiable en admin, défaut AG_OVERRIDE_RATE). */
	function ag_override_rate() {
		$def = defined( 'AG_OVERRIDE_RATE' ) ? AG_OVERRIDE_RATE : 0.20;
		$r   = (float) get_option( 'ag_override_rate', $def );
		return ( $r > 0 && $r <= 1 ) ? $r : $def;
	}
}
if ( ! function_exists( 'ag_ambassadeur_override_for' ) ) {
	/**
	 * Commissions de parrainage du parrain : un % de la commission de ses filleuls,
	 * sur leurs VENTES réelles (validées/payées) uniquement.
	 * Retourne array( team, generated, paid ).
	 */
	function ag_ambassadeur_override_for( $parrain_ref ) {
		$rate   = function_exists( 'ag_override_rate' ) ? ag_override_rate() : ( defined( 'AG_OVERRIDE_RATE' ) ? AG_OVERRIDE_RATE : 0.20 );
		$team   = ag_ambassadeur_filleuls( $parrain_ref );
		$emails = array();
		foreach ( $team as $a ) { $emails[] = strtolower( $a['email'] ?? '' ); }
		$generated = 0; $paid = 0;
		foreach ( (array) get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			if ( ! in_array( strtolower( $v['email'] ?? '' ), $emails, true ) ) continue;
			$st = $v['statut'] ?? '';
			if ( 'validee' === $st || 'payee' === $st ) $generated += (float) $v['commission'] * $rate;
			if ( 'payee' === $st ) $paid += (float) $v['commission'] * $rate;
		}
		return array( 'team' => count( $team ), 'generated' => $generated, 'paid' => $paid );
	}
}

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
		'client_email'  => strtolower( $email ),
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

/* ── 9. Inscription libre (email + mot de passe) ───────────────────── */
if ( ! function_exists( 'ag_register_client' ) ) {
	/** Crée un compte client avec le mot de passe choisi par la personne. */
	function ag_register_client( $email, $name, $password ) {
		if ( ! is_email( $email ) ) return 0;
		$base  = sanitize_user( current( explode( '@', $email ) ), true );
		if ( '' === $base ) $base = 'membre';
		$login = $base; $n = 1;
		while ( username_exists( $login ) ) { $login = $base . $n; $n++; }
		$uid = wp_insert_user( array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => $password,
			'display_name' => $name ? $name : $login,
			'first_name'   => $name,
			'role'         => 'ag_client',
		) );
		if ( is_wp_error( $uid ) ) return 0;
		ag_send_signup_welcome( get_user_by( 'id', $uid ) );
		return $uid;
	}
}
if ( ! function_exists( 'ag_send_signup_welcome' ) ) {
	/** Email de bienvenue (compte créé par la personne elle-même : pas de lien mot de passe). */
	function ag_send_signup_welcome( $user ) {
		if ( ! $user || ! $user->ID ) return;
		$name    = $user->display_name ? $user->display_name : $user->user_login;
		$heading = 'Bienvenue chez Alliance Groupe 👋';
		$inner   = '<p>Bonjour ' . esc_html( $name ) . ',</p>'
			. '<p>Ton compte est créé ✅ Tu peux dès maintenant accéder à ton espace.</p>'
			. ag_email_button( 'Accéder à mon espace', home_url( '/espace-client' ) )
			. ( ( function_exists( 'ag_tg_cfg' ) && ag_tg_cfg( 'chan_link' ) ) ? '<p>📣 <strong>Rejoins notre canal Telegram</strong> pour nos offres et nouveautés :</p>' . ag_email_button( 'Rejoindre le canal', ag_tg_cfg( 'chan_link' ) ) : '' )
			. '<p style="color:#9a9aa5;font-size:13px;">Tu t\'es connecté avec Google ? Tu peux aussi définir un mot de passe via « Mot de passe oublié » sur la page de connexion.</p>';
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Alliance Groupe <contact@alliancegroupe-inc.com>',
		);
		wp_mail( $user->user_email, 'Ton compte Alliance Groupe est créé', ag_email_wrap( $heading, $inner ), $headers );
	}
}

add_action( 'template_redirect', function () {
	if ( empty( $_POST['ag_register_submit'] ) ) return;
	$back = home_url( '/connexion' );
	$err  = function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( array( 'tab' => 'inscription', 'reg' => $code ), $back ) ); exit;
	};
	if ( ! isset( $_POST['ag_register_nonce'] ) || ! wp_verify_nonce( $_POST['ag_register_nonce'], 'ag_register' ) ) $err( 'nonce' );
	if ( ! empty( $_POST['ag_hp'] ) ) $err( 'spam' ); // pot de miel anti-bot
	$name  = sanitize_text_field( wp_unslash( $_POST['reg_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['reg_email'] ?? '' ) );
	$pwd   = (string) ( $_POST['reg_pwd'] ?? '' );
	$pwd2  = (string) ( $_POST['reg_pwd2'] ?? '' );
	if ( ! is_email( $email ) )      $err( 'email' );
	if ( strlen( $pwd ) < 8 )        $err( 'pwd' );
	if ( $pwd !== $pwd2 )            $err( 'match' );
	if ( get_user_by( 'email', $email ) ) $err( 'exists' );
	$uid = ag_register_client( $email, $name, $pwd );
	if ( ! $uid ) $err( 'fail' );
	$user = get_user_by( 'id', $uid );
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true, true ); // cookie secure forcé (site HTTPS)
	$dest = '';
	if ( ! empty( $_POST['redirect_to'] ) ) $dest = wp_validate_redirect( wp_unslash( $_POST['redirect_to'] ), '' );
	if ( ! $dest ) $dest = ag_espace_url( ag_espace_member_kind( $user ) );
	wp_safe_redirect( $dest ); exit;
} );

/* ── 10. Connexion Google (Sign in with Google) ────────────────────── */
if ( ! function_exists( 'ag_google_client_id' ) ) {
	function ag_google_client_id() { return trim( (string) get_option( 'ag_google_client_id', '994246329599-af9bgp7t7tsf7sofs6fkvnh4npb14ep2.apps.googleusercontent.com' ) ); }
}

// Page de réglages : Réglages > Connexion Google
add_action( 'admin_menu', function () {
	add_options_page( 'Connexion Google', 'Connexion Google', 'manage_options', 'ag-social-login', 'ag_social_login_render' );
} );
add_action( 'admin_init', function () {
	register_setting( 'ag_social_login', 'ag_google_client_id', array(
		'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '',
	) );
} );
if ( ! function_exists( 'ag_social_login_render' ) ) {
	function ag_social_login_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$cid    = ag_google_client_id();
		$origin = home_url();
		$cb     = admin_url( 'admin-post.php?action=ag_google_login' );
		?>
		<div class="wrap">
			<h1>Connexion Google</h1>
			<p style="max-width:760px;color:#50575e;">Active le bouton « <strong>Continuer avec Google</strong> » sur ta page de connexion. C'est <strong>gratuit</strong>. Il te faut juste un <strong>ID client OAuth</strong> créé dans Google Cloud.</p>
			<div style="max-width:760px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<strong>Comment obtenir l'ID client (5 min) :</strong>
				<ol style="margin:8px 0 0 22px;line-height:1.7;">
					<li>Va sur <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">console.cloud.google.com/apis/credentials</a> et crée (ou choisis) un projet.</li>
					<li>Configure l'« écran de consentement OAuth » (type <em>Externe</em>, nom « Alliance Groupe »).</li>
					<li>« Créer des identifiants » &gt; « <strong>ID client OAuth</strong> » &gt; type <strong>Application Web</strong>.</li>
					<li>Dans <strong>Origines JavaScript autorisées</strong>, ajoute :<br><code><?php echo esc_html( $origin ); ?></code></li>
					<li>Dans <strong>URI de redirection autorisés</strong>, ajoute :<br><code><?php echo esc_html( $cb ); ?></code></li>
					<li>Copie l'« <strong>ID client</strong> » (finit par <code>.apps.googleusercontent.com</code>) et colle-le ci-dessous.</li>
				</ol>
			</div>
			<form method="post" action="options.php" style="max-width:760px;">
				<?php settings_fields( 'ag_social_login' ); ?>
				<table class="form-table"><tr>
					<th scope="row"><label for="ag_google_client_id">ID client Google</label></th>
					<td>
						<input type="text" name="ag_google_client_id" id="ag_google_client_id" value="<?php echo esc_attr( $cid ); ?>" class="regular-text" style="width:100%;max-width:560px;" placeholder="xxxxxxxx.apps.googleusercontent.com">
						<p class="description"><?php echo $cid ? '✓ Bouton Google actif sur /connexion.' : 'Vide = bouton Google masqué (connexion par email/mot de passe uniquement).'; ?></p>
					</td>
				</tr></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

// Traitement du retour Google (POST sur admin-post.php).
if ( ! function_exists( 'ag_google_login_handler' ) ) {
	function ag_google_login_handler() {
		$back = home_url( '/connexion' );
		$fail = function ( $code ) use ( $back ) { wp_safe_redirect( add_query_arg( 'login', $code, $back ) ); exit; };
		$cid  = ag_google_client_id();
		if ( '' === $cid ) $fail( 'failed' );
		// Protection CSRF : double cookie g_csrf_token fourni par Google.
		$body_csrf   = isset( $_POST['g_csrf_token'] ) ? (string) $_POST['g_csrf_token'] : '';
		$cookie_csrf = isset( $_COOKIE['g_csrf_token'] ) ? (string) $_COOKIE['g_csrf_token'] : '';
		if ( '' === $body_csrf || '' === $cookie_csrf || ! hash_equals( $cookie_csrf, $body_csrf ) ) $fail( 'google' );
		$token = isset( $_POST['credential'] ) ? (string) $_POST['credential'] : '';
		if ( '' === $token ) $fail( 'google' );
		$resp = wp_remote_get( 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode( $token ), array( 'timeout' => 15 ) );
		if ( is_wp_error( $resp ) || 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) $fail( 'google' );
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! is_array( $data ) ) $fail( 'google' );
		if ( empty( $data['aud'] ) || ! hash_equals( $cid, (string) $data['aud'] ) ) $fail( 'google' );
		$iss = $data['iss'] ?? '';
		if ( ! in_array( $iss, array( 'accounts.google.com', 'https://accounts.google.com' ), true ) ) $fail( 'google' );
		$email    = sanitize_email( $data['email'] ?? '' );
		$verified = $data['email_verified'] ?? '';
		if ( ! is_email( $email ) || ! in_array( $verified, array( 'true', true, '1', 1 ), true ) ) $fail( 'google' );
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			$uid = ag_register_client( $email, sanitize_text_field( $data['name'] ?? '' ), wp_generate_password( 24 ) );
			if ( ! $uid ) $fail( 'google' );
			$user = get_user_by( 'id', $uid );
		}
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true, true ); // cookie secure forcé (site HTTPS)
		wp_safe_redirect( ag_espace_url( ag_espace_member_kind( $user ) ) ); exit;
	}
}
add_action( 'admin_post_nopriv_ag_google_login', 'ag_google_login_handler' );
add_action( 'admin_post_ag_google_login', 'ag_google_login_handler' );

/* ── 10b. Vidéo du programme ambassadeur (configurable) ─────────────── */
if ( ! function_exists( 'ag_amb_guide_video' ) ) {
	function ag_amb_guide_video() { return trim( (string) get_option( 'ag_amb_guide_video', '' ) ); }
}
if ( ! function_exists( 'ag_amb_guide_video_embed' ) ) {
	/** Rend la vidéo du programme (YouTube / Vimeo / MP4 / lien). Retourne du HTML déjà échappé. */
	function ag_amb_guide_video_embed() {
		$u = ag_amb_guide_video();
		if ( '' === $u ) return '';
		if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $u, $m ) ) {
			return '<div class="ag-guide-video"><iframe src="https://www.youtube.com/embed/' . esc_attr( $m[1] ) . '" title="Programme ambassadeur" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
		}
		if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~', $u, $m ) ) {
			return '<div class="ag-guide-video"><iframe src="https://player.vimeo.com/video/' . esc_attr( $m[1] ) . '" title="Programme ambassadeur" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
		}
		if ( preg_match( '~\.(mp4|webm|mov)(\?.*)?$~i', $u ) ) {
			return '<div class="ag-guide-video"><video src="' . esc_url( $u ) . '" controls playsinline></video></div>';
		}
		return '<p style="margin-top:24px;"><a class="ag-btn-gold" href="' . esc_url( $u ) . '" target="_blank" rel="noopener">▶ Voir la vidéo explicative</a></p>';
	}
}
add_action( 'admin_menu', function () {
	add_options_page( 'Programme ambassadeur', 'Programme ambassadeur', 'manage_options', 'ag-amb-programme', 'ag_amb_programme_render' );
} );
add_action( 'admin_init', function () {
	register_setting( 'ag_amb_programme', 'ag_amb_guide_video', array(
		'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '',
	) );
} );
if ( ! function_exists( 'ag_amb_programme_render' ) ) {
	function ag_amb_programme_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$v = ag_amb_guide_video();
		?>
		<div class="wrap">
			<h1>Programme ambassadeur — vidéo explicative</h1>
			<p style="max-width:760px;color:#50575e;">Colle ici le lien d'une <strong>vidéo explicative</strong> (YouTube, Vimeo, ou un fichier .mp4). Elle s'affichera en haut de la page <a href="<?php echo esc_url( home_url( '/programme-ambassadeur' ) ); ?>" target="_blank" rel="noopener">Programme</a> que voient les ambassadeurs. Tu peux la filmer simplement avec ton téléphone.</p>
			<form method="post" action="options.php" style="max-width:760px;">
				<?php settings_fields( 'ag_amb_programme' ); ?>
				<table class="form-table"><tr>
					<th scope="row"><label for="ag_amb_guide_video">Lien de la vidéo</label></th>
					<td>
						<input type="url" name="ag_amb_guide_video" id="ag_amb_guide_video" value="<?php echo esc_attr( $v ); ?>" class="regular-text" style="width:100%;max-width:560px;" placeholder="https://www.youtube.com/watch?v=… ou https://….mp4">
						<p class="description"><?php echo $v ? '✓ Vidéo affichée sur la page Programme.' : 'Vide = un message « la vidéo arrive bientôt » + les étapes écrites s\'affichent.'; ?></p>
					</td>
				</tr></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

/* ── 11. Membres connectés : jamais de page en cache ────────────────
   Empêche qu'après une purge de cache un membre se retrouve sur une
   version « déconnectée » du site (cache servi sans tenir compte du
   cookie de connexion). Les visiteurs anonymes gardent le cache rapide. */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
		nocache_headers();
	}
}, 0 );

/* ── 12. Notifications Google Agenda (invitation .ics avec rappel pop-up) ──
   À chaque inscription ambassadeur / achat de site, le site envoie une
   invitation d'agenda à ton adresse Gmail → Google l'ajoute à ton agenda
   avec un rappel (pop-up + sonnerie selon tes réglages Google Agenda). */
if ( ! function_exists( 'ag_calendar_notify' ) ) {
	function ag_calendar_notify( $summary, $description, $location = '' ) {
		$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
		if ( ! is_email( $to ) ) return;
		$now   = time();
		$start = $now + 120;          // 2 min plus tard : garantit le déclenchement du pop-up
		$end   = $start + 1800;       // 30 min
		$uid   = uniqid( 'ag_' ) . '@alliancegroupe-inc.com';
		$z     = function ( $t ) { return gmdate( 'Ymd\THis\Z', $t ); };
		$esc   = function ( $s ) { return preg_replace( '/([,;\\\\])/', '\\\\$1', str_replace( array( "\r\n", "\n" ), '\\n', (string) $s ) ); };
		$ics  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Alliance Groupe//Notifs//FR\r\nCALSCALE:GREGORIAN\r\nMETHOD:REQUEST\r\n";
		$ics .= "BEGIN:VEVENT\r\nUID:" . $uid . "\r\nDTSTAMP:" . $z( $now ) . "\r\nDTSTART:" . $z( $start ) . "\r\nDTEND:" . $z( $end ) . "\r\n";
		$ics .= "SUMMARY:" . $esc( $summary ) . "\r\nDESCRIPTION:" . $esc( $description ) . "\r\n";
		if ( $location ) $ics .= "LOCATION:" . $esc( $location ) . "\r\n";
		$ics .= "ORGANIZER;CN=Alliance Groupe:mailto:contact@alliancegroupe-inc.com\r\n";
		$ics .= "ATTENDEE;CN=Alliance Groupe;RSVP=TRUE:mailto:" . $to . "\r\n";
		$ics .= "STATUS:CONFIRMED\r\nTRANSP:OPAQUE\r\n";
		$ics .= "BEGIN:VALARM\r\nACTION:DISPLAY\r\nDESCRIPTION:" . $esc( $summary ) . "\r\nTRIGGER:-PT0M\r\nEND:VALARM\r\n";
		$ics .= "END:VEVENT\r\nEND:VCALENDAR\r\n";
		$headers = array(
			'Content-Type: text/calendar; charset=UTF-8; method=REQUEST',
			'From: Alliance Groupe <contact@alliancegroupe-inc.com>',
		);
		wp_mail( $to, $summary, $ics, $headers );
		if ( function_exists( 'ag_push' ) ) ag_push( $summary, $description ); // notif téléphone (Telegram)
	}
}
// Nouvel ambassadeur inscrit -> évènement agenda.
add_action( 'ag_ambassadeur_registered', function ( $email, $name ) {
	ag_calendar_notify(
		'🤝 Nouvel ambassadeur : ' . $name,
		"Inscription au programme commercial.\nNom : $name\nEmail : $email\nÀ valider (identité + contrat) dans l'admin."
	);
	if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🤝 Nouvel ambassadeur inscrit : ' . $name . ' (à valider)' );
}, 20, 2 );
// Achat de site (brief reçu = commande) -> évènement agenda.
add_action( 'ag_client_brief_submitted', function ( $email, $name, $pack = '' ) {
	$p = $pack ? ucfirst( $pack ) : '';
	ag_calendar_notify(
		'🎯 Vente site' . ( $p ? ' (' . $p . ')' : '' ) . ' : ' . $name,
		"Commande d'un site reçue (brief envoyé).\nClient : $name\nEmail : $email\nPack : " . ( $p ? $p : 'non précisé' ) . "\nVérifie le paiement PayPal puis valide la vente."
	);
	if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🎯 Commande de site : ' . $name . ( $p ? ' (' . $p . ')' : '' ) );
}, 20, 3 );
