<?php
/**
 * AG — Offre « Jeune avocat » : 3 mois de Premium offerts aux avocats
 * fraîchement diplômés, via un code distribué par une école / un Jeune Barreau.
 *
 * - Page publique `/jeune-avocat` (template page-jeune-avocat.php), créée auto.
 * - Formulaire → admin-post `ag_jeune_avocat` :
 *     • vérifie le code école (whitelist `ag_ja_codes` ; vide = mode ouvert) ;
 *     • crée un lead CRM (source « jeune-avocat », école taguée) ;
 *     • génère une LICENCE Premium valable 3 mois (si licence manager présent) ;
 *     • envoie l'email avec la clé + la marche à suivre ;
 *     • notifie le cabinet (ag_push) + enregistre l'inscription (suivi par école).
 * - Réglages → 🎓 Jeunes avocats : gérer les codes + voir les inscrits.
 *
 * Déonto : c'est l'AGENCE qui offre un outil (pas l'avocat qui fait sa pub).
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ag_ja_slug() {
	return 'jeune-avocat';
}
function ag_ja_url() {
	$p = get_page_by_path( ag_ja_slug() );
	return $p ? get_permalink( $p ) : home_url( '/' . ag_ja_slug() );
}

/** Carte des codes école : "CODE" ou "CODE|Nom de l'école", un par ligne. */
function ag_ja_codes_map() {
	$raw = (string) get_option( 'ag_ja_codes', '' );
	$map = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$code  = strtoupper( $parts[0] );
		$map[ $code ] = isset( $parts[1] ) && $parts[1] !== '' ? $parts[1] : $code;
	}
	return $map;
}

/** Valide un code → retourne le libellé école, ou false. Mode ouvert si aucun code défini. */
function ag_ja_check_code( $code ) {
	$code = strtoupper( trim( (string) $code ) );
	if ( '' === $code ) {
		return false;
	}
	$map = ag_ja_codes_map();
	if ( empty( $map ) ) {
		return $code; // mode ouvert : tout code non vide est accepté, tagué tel quel
	}
	return isset( $map[ $code ] ) ? $map[ $code ] : false;
}

/**
 * Génère une licence Premium valable 3 mois. Retourne la clé ou ''.
 * Best-effort : nécessite le plugin licence (AG_Licence_DB).
 */
function ag_ja_grant_trial( $email ) {
	if ( ! class_exists( 'AG_Licence_DB' ) ) {
		return '';
	}
	$key = AG_Licence_DB::generate_key( 'premium' );
	$id  = AG_Licence_DB::insert( $key, 'premium', $email, '', 'ag-starter-avocat' );
	if ( ! $id ) {
		return '';
	}
	AG_Licence_DB::update( $id, array(
		'expires_at' => gmdate( 'Y-m-d H:i:s', strtotime( '+3 months' ) ),
	) );
	return $key;
}

/** Traitement du formulaire. */
function ag_ja_handle() {
	$back = ag_ja_url();
	if ( ! isset( $_POST['ag_ja_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_ja_nonce'] ), 'ag_jeune_avocat' ) ) {
		wp_safe_redirect( add_query_arg( 'ag_ja', 'err', $back ) );
		exit;
	}
	if ( ! empty( $_POST['site_web_hp'] ) ) { // honeypot
		wp_safe_redirect( add_query_arg( 'ag_ja', 'ok', $back ) );
		exit;
	}
	$prenom  = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
	$nom     = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$barreau = sanitize_text_field( wp_unslash( $_POST['barreau'] ?? '' ) );
	$serment = sanitize_text_field( wp_unslash( $_POST['serment'] ?? '' ) );
	$code    = sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) );
	$rgpd    = ! empty( $_POST['rgpd'] );

	if ( ! $prenom || ! $nom || ! is_email( $email ) || ! $rgpd ) {
		wp_safe_redirect( add_query_arg( 'ag_ja', 'champs', $back ) );
		exit;
	}
	$ecole = ag_ja_check_code( $code );
	if ( false === $ecole ) {
		wp_safe_redirect( add_query_arg( 'ag_ja', 'code', $back ) );
		exit;
	}

	// 1) Licence Premium 3 mois.
	$key = ag_ja_grant_trial( $email );

	// 2) Lead CRM.
	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'   => trim( $prenom . ' ' . $nom ),
			'type'   => 'avocat',
			'email'  => $email,
			'status' => 'interesse',
			'source' => 'jeune-avocat',
			'notes'  => sprintf( 'Jeune avocat — école/code : %s · barreau : %s · serment : %s%s', $ecole, $barreau ?: 'n/c', $serment ?: 'n/c', $key ? ' · licence 3 mois émise' : ' · licence À ÉMETTRE' ),
		) );
	}

	// 3) Enregistrement (suivi par école).
	$signups = (array) get_option( 'ag_ja_signups', array() );
	$signups[] = array( 'ts' => time(), 'name' => trim( $prenom . ' ' . $nom ), 'email' => $email, 'barreau' => $barreau, 'serment' => $serment, 'ecole' => $ecole, 'key' => $key );
	update_option( 'ag_ja_signups', array_slice( $signups, -2000 ) );

	// 4) Email au jeune avocat.
	ag_ja_send_email( $prenom, $email, $key );

	// 5) Notif interne.
	if ( function_exists( 'ag_push' ) ) {
		ag_push( '🎓 Jeune avocat — 3 mois offerts', sprintf( '%s (%s) · %s · serment %s', trim( $prenom . ' ' . $nom ), $email, $ecole, $serment ?: '?' ) );
	}

	wp_safe_redirect( add_query_arg( 'ag_ja', 'ok', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_ag_jeune_avocat', 'ag_ja_handle' );
add_action( 'admin_post_ag_jeune_avocat', 'ag_ja_handle' );

/** Email de bienvenue + clé + marche à suivre. */
function ag_ja_send_email( $prenom, $email, $key ) {
	$dl   = home_url( '/wordpress-avocat' );
	$blog = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$inner  = '<p style="font-size:16px;color:#222;">Félicitations Maître ' . esc_html( $prenom ) . ', et bienvenue !</p>';
	$inner .= '<p style="font-size:15px;color:#444;line-height:1.6;">Pour vous aider à lancer votre cabinet, voici <strong>3 mois de Pack Premium offerts</strong> sur le thème WordPress Avocat (recherche Judilibre, espace client sécurisé, RGPD &amp; déontologie).</p>';
	if ( $key ) {
		$inner .= '<p style="font-size:15px;color:#444;">Votre clé d\'activation (valable 3 mois) :</p>';
		$inner .= '<p style="font-size:18px;font-weight:700;letter-spacing:1px;background:#faf7ee;border:1px dashed #c8a85a;border-radius:8px;padding:14px;text-align:center;color:#7a5c00;">' . esc_html( $key ) . '</p>';
		$inner .= '<p style="font-size:14px;color:#555;line-height:1.6;">Marche à suivre : 1) installez le thème gratuit <em>AG Starter Avocat</em> (WordPress.org) ; 2) activez le module Premium ; 3) collez cette clé. Tout est expliqué ici : ' . esc_html( $dl ) . '</p>';
	} else {
		$inner .= '<p style="font-size:15px;color:#444;">Nous activons votre accès Premium et vous recontactons sous 24 h avec votre clé.</p>';
	}
	$inner .= '<p style="font-size:13px;color:#888;">Sans engagement, sans carte bancaire. À l\'issue des 3 mois, vous choisissez de continuer ou non.</p>';

	if ( function_exists( 'ag_email_wrap' ) ) {
		$html = ag_email_wrap( '🎓 Vos 3 mois de Premium offerts', $inner );
	} else {
		$html = '<html><body>' . $inner . '</body></html>';
	}
	wp_mail( $email, 'Jeune avocat : vos 3 mois de site offerts', $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
}

/** Création auto de la page. */
function ag_ja_ensure_page() {
	if ( get_option( 'ag_ja_page_done' ) ) {
		return;
	}
	if ( ! get_page_by_path( ag_ja_slug() ) ) {
		wp_insert_post( array(
			'post_title'    => 'Jeune avocat — 3 mois offerts',
			'post_name'     => ag_ja_slug(),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => '',
			'page_template' => 'templates/page-jeune-avocat.php',
		) );
	}
	update_option( 'ag_ja_page_done', 1 );
}
add_action( 'init', 'ag_ja_ensure_page' );

/* ── Réglages admin : codes école + inscrits ─────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'options-general.php', 'Jeunes avocats', '🎓 Jeunes avocats', 'manage_options', 'ag-jeune-avocat', 'ag_ja_settings_page' );
} );

function ag_ja_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['ag_ja_save'] ) && check_admin_referer( 'ag_ja_admin' ) ) {
		update_option( 'ag_ja_codes', sanitize_textarea_field( wp_unslash( $_POST['ag_ja_codes'] ?? '' ) ) );
		echo '<div class="notice notice-success is-dismissible"><p>Codes enregistrés.</p></div>';
	}
	$codes   = (string) get_option( 'ag_ja_codes', '' );
	$signups = array_reverse( (array) get_option( 'ag_ja_signups', array() ) );
	echo '<div class="wrap"><h1>🎓 Offre Jeunes avocats — 3 mois offerts</h1>';
	echo '<p>Page de l\'offre : <a href="' . esc_url( ag_ja_url() ) . '" target="_blank">' . esc_html( ag_ja_url() ) . '</a></p>';
	echo '<form method="post"><h2>Codes école / Jeune Barreau</h2>';
	wp_nonce_field( 'ag_ja_admin' );
	echo '<p class="description">Un code par ligne. Format : <code>CODE</code> ou <code>CODE|Nom de l\'école</code>. <strong>Si vide = tout code est accepté</strong> (mode ouvert). Donne un code différent par partenaire pour suivre qui ramène qui.</p>';
	echo '<textarea name="ag_ja_codes" rows="8" class="large-text code" placeholder="EFB2026|EFB Paris&#10;UJANANTES|UJA Nantes">' . esc_textarea( $codes ) . '</textarea>';
	submit_button( 'Enregistrer les codes', 'primary', 'ag_ja_save' );
	echo '</form>';

	echo '<h2>Inscrits (' . count( $signups ) . ')</h2>';
	if ( $signups ) {
		echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Barreau</th><th>Serment</th><th>École</th><th>Clé</th></tr></thead><tbody>';
		foreach ( array_slice( $signups, 0, 200 ) as $s ) {
			echo '<tr><td>' . esc_html( wp_date( 'd/m/Y', (int) ( $s['ts'] ?? 0 ) ) ) . '</td><td>' . esc_html( $s['name'] ?? '' ) . '</td><td>' . esc_html( $s['email'] ?? '' ) . '</td><td>' . esc_html( $s['barreau'] ?? '' ) . '</td><td>' . esc_html( $s['serment'] ?? '' ) . '</td><td>' . esc_html( $s['ecole'] ?? '' ) . '</td><td><code>' . esc_html( $s['key'] ?: '—' ) . '</code></td></tr>';
		}
		echo '</tbody></table>';
	} else {
		echo '<p>Aucune inscription pour le moment.</p>';
	}
	echo '</div>';
}
