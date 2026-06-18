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

/** Partenaires : code => array(label, type, contact, created). */
function ag_ja_partners() {
	$p = get_option( 'ag_ja_partners', array() );
	return is_array( $p ) ? $p : array();
}

/** Génère un code unique, lisible, à partir d'un nom de partenaire. */
function ag_ja_make_code( $label ) {
	$base = strtoupper( remove_accents( (string) $label ) );
	$base = preg_replace( '/[^A-Z0-9]/', '', $base );
	$base = substr( $base, 0, 10 );
	if ( '' === $base ) {
		$base = 'PART';
	}
	$existing = array_keys( ag_ja_partners() );
	do {
		$code = $base . '-' . strtoupper( wp_generate_password( 4, false, false ) );
	} while ( in_array( $code, $existing, true ) );
	return $code;
}

/** Carte des codes (partenaires + ancienne liste texte) : CODE => libellé. */
function ag_ja_codes_map() {
	$map = array();
	foreach ( ag_ja_partners() as $code => $info ) {
		$map[ strtoupper( $code ) ] = is_array( $info ) ? ( $info['label'] ?? $code ) : (string) $info;
	}
	$raw = (string) get_option( 'ag_ja_codes', '' );
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$code  = strtoupper( $parts[0] );
		if ( ! isset( $map[ $code ] ) ) {
			$map[ $code ] = ( isset( $parts[1] ) && '' !== $parts[1] ) ? $parts[1] : $code;
		}
	}
	return $map;
}

/** Liste par défaut des écoles d'avocats (EDA) + grandes UJA, pour pré-remplir. */
function ag_ja_default_partners() {
	return array(
		array( 'EFB Paris', 'EDA' ),
		array( 'HEDAC Versailles', 'EDA' ),
		array( 'IXAD Lille', 'EDA' ),
		array( 'ERAGE Grand Est', 'EDA' ),
		array( 'EDARA Lyon', 'EDA' ),
		array( 'EDASE Sud-Est', 'EDA' ),
		array( 'EDAGO Grand Ouest', 'EDA' ),
		array( 'EDA Centre-Sud', 'EDA' ),
		array( 'EDA Aliénor Bordeaux', 'EDA' ),
		array( 'EDA Sud-Ouest Pyrénées', 'EDA' ),
		array( 'CRFPA Montpellier', 'EDA' ),
		array( 'UJA Nantes', 'UJA' ),
		array( 'UJA Paris', 'UJA' ),
		array( 'UJA Lyon', 'UJA' ),
		array( 'UJA Marseille', 'UJA' ),
		array( 'FNUJA (national)', 'UJA' ),
	);
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

/** Export CSV des inscrits. */
add_action( 'admin_post_ag_ja_export', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_ja_export' ) ) {
		wp_die( 'Accès refusé.' );
	}
	$signups = (array) get_option( 'ag_ja_signups', array() );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=jeunes-avocats-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fputs( $out, "\xEF\xBB\xBF" ); // BOM Excel
	fputcsv( $out, array( 'Date', 'Nom', 'Email', 'Barreau', 'Serment', 'Ecole/Code', 'Cle', 'Expire le' ), ';' );
	foreach ( array_reverse( $signups ) as $s ) {
		$ts  = (int) ( $s['ts'] ?? 0 );
		$exp = $ts ? gmdate( 'd/m/Y', strtotime( '+3 months', $ts ) ) : '';
		fputcsv( $out, array( $ts ? gmdate( 'd/m/Y', $ts ) : '', $s['name'] ?? '', $s['email'] ?? '', $s['barreau'] ?? '', $s['serment'] ?? '', $s['ecole'] ?? '', $s['key'] ?? '', $exp ), ';' );
	}
	fclose( $out );
	exit;
} );

function ag_ja_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// ── Actions ──
	if ( isset( $_POST['ag_ja_add'] ) && check_admin_referer( 'ag_ja_admin' ) ) {
		$label   = sanitize_text_field( wp_unslash( $_POST['p_label'] ?? '' ) );
		$type    = sanitize_text_field( wp_unslash( $_POST['p_type'] ?? 'Autre' ) );
		$contact = sanitize_text_field( wp_unslash( $_POST['p_contact'] ?? '' ) );
		if ( $label ) {
			$partners = ag_ja_partners();
			$code     = ag_ja_make_code( $label );
			$partners[ $code ] = array( 'label' => $label, 'type' => $type, 'contact' => $contact, 'created' => time() );
			update_option( 'ag_ja_partners', $partners );
			echo '<div class="notice notice-success is-dismissible"><p>Partenaire ajouté — code généré : <strong>' . esc_html( $code ) . '</strong></p></div>';
		}
	}
	if ( isset( $_POST['ag_ja_seed'] ) && check_admin_referer( 'ag_ja_admin' ) ) {
		$partners = ag_ja_partners();
		$added    = 0;
		$existing_labels = array_map( function ( $p ) { return is_array( $p ) ? ( $p['label'] ?? '' ) : ''; }, $partners );
		foreach ( ag_ja_default_partners() as $d ) {
			if ( in_array( $d[0], $existing_labels, true ) ) {
				continue;
			}
			$code = ag_ja_make_code( $d[0] );
			$partners[ $code ] = array( 'label' => $d[0], 'type' => $d[1], 'contact' => '', 'created' => time() );
			$added++;
		}
		update_option( 'ag_ja_partners', $partners );
		echo '<div class="notice notice-success is-dismissible"><p>' . (int) $added . ' partenaires pré-remplis (EDA + UJA) avec un code généré chacun.</p></div>';
	}
	if ( isset( $_GET['ag_ja_del'] ) && check_admin_referer( 'ag_ja_del' ) ) {
		$partners = ag_ja_partners();
		unset( $partners[ strtoupper( sanitize_text_field( wp_unslash( $_GET['ag_ja_del'] ) ) ) ] );
		update_option( 'ag_ja_partners', $partners );
		echo '<div class="notice notice-success is-dismissible"><p>Partenaire supprimé.</p></div>';
	}

	$partners = ag_ja_partners();
	$signups  = (array) get_option( 'ag_ja_signups', array() );

	// Comptage par école.
	$by_ecole = array();
	$this_month = 0;
	$month_key  = gmdate( 'Y-m' );
	foreach ( $signups as $s ) {
		$e = $s['ecole'] ?? '—';
		$by_ecole[ $e ] = ( $by_ecole[ $e ] ?? 0 ) + 1;
		if ( gmdate( 'Y-m', (int) ( $s['ts'] ?? 0 ) ) === $month_key ) {
			$this_month++;
		}
	}

	echo '<div class="wrap"><h1>🎓 Jeunes avocats — console de gestion</h1>';
	echo '<p>Page publique : <a href="' . esc_url( ag_ja_url() ) . '" target="_blank">' . esc_html( ag_ja_url() ) . '</a></p>';

	// KPIs.
	echo '<div style="display:flex;gap:14px;flex-wrap:wrap;margin:14px 0 24px;">';
	$kpi = function ( $n, $l ) { echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:16px 22px;min-width:150px;"><div style="font-size:1.9rem;font-weight:800;color:#2271b1;line-height:1;">' . esc_html( $n ) . '</div><div style="color:#666;font-size:.82rem;margin-top:4px;text-transform:uppercase;letter-spacing:.5px;">' . esc_html( $l ) . '</div></div>'; };
	$kpi( count( $signups ), 'Inscrits total' );
	$kpi( $this_month, 'Ce mois-ci' );
	$kpi( count( $partners ), 'Partenaires' );
	echo '</div>';

	// Ajout + pré-remplissage.
	echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">';
	echo '<form method="post" style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px;"><h2 style="margin-top:0;">➕ Ajouter un partenaire</h2>';
	wp_nonce_field( 'ag_ja_admin' );
	echo '<p><label>Nom (école / UJA)<br><input type="text" name="p_label" class="regular-text" placeholder="UJA Nantes" required></label></p>';
	echo '<p><label>Type<br><select name="p_type"><option>EDA</option><option>UJA</option><option>Université</option><option>Incubateur</option><option>Autre</option></select></label></p>';
	echo '<p><label>Contact (email, facultatif)<br><input type="email" name="p_contact" class="regular-text"></label></p>';
	submit_button( 'Générer le code', 'primary', 'ag_ja_add' );
	echo '</form>';

	echo '<form method="post" style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px;"><h2 style="margin-top:0;">⚡ Pré-remplir les écoles &amp; UJA</h2>';
	wp_nonce_field( 'ag_ja_admin' );
	echo '<p class="description">Crée d\'un coup les 11 EDA (écoles d\'avocats) + les grandes UJA, avec un code généré pour chacune. Tu pourras supprimer/éditer ensuite.</p>';
	submit_button( 'Générer tous les codes EDA + UJA', 'secondary', 'ag_ja_seed' );
	echo '</form>';
	echo '</div>';

	// Table partenaires.
	echo '<h2 style="margin-top:30px;">Partenaires &amp; codes (' . count( $partners ) . ')</h2>';
	if ( $partners ) {
		echo '<table class="widefat striped"><thead><tr><th>Code</th><th>Partenaire</th><th>Type</th><th>Contact</th><th>Inscrits</th><th>Lien à partager</th><th></th></tr></thead><tbody>';
		$page_url = ag_ja_url();
		foreach ( $partners as $code => $info ) {
			$label = is_array( $info ) ? ( $info['label'] ?? $code ) : (string) $info;
			$type  = is_array( $info ) ? ( $info['type'] ?? '' ) : '';
			$cont  = is_array( $info ) ? ( $info['contact'] ?? '' ) : '';
			$cnt   = (int) ( $by_ecole[ $label ] ?? 0 );
			$share = add_query_arg( 'code', $code, $page_url );
			$del   = wp_nonce_url( add_query_arg( 'ag_ja_del', $code ), 'ag_ja_del' );
			echo '<tr>';
			echo '<td><input type="text" readonly onclick="this.select()" value="' . esc_attr( $code ) . '" style="width:130px;font-family:monospace;font-weight:700;"></td>';
			echo '<td>' . esc_html( $label ) . '</td><td>' . esc_html( $type ) . '</td><td>' . esc_html( $cont ) . '</td>';
			echo '<td><strong>' . $cnt . '</strong></td>';
			echo '<td><input type="text" readonly onclick="this.select()" value="' . esc_attr( $share ) . '" class="code" style="width:100%;min-width:240px;"></td>';
			echo '<td><a href="' . esc_url( $del ) . '" onclick="return confirm(\'Supprimer ce partenaire ?\');" style="color:#b32d2e;">Suppr.</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	} else {
		echo '<p>Aucun partenaire. Ajoute-en un ou clique « Générer tous les codes EDA + UJA ».</p>';
	}

	// Inscrits + export.
	echo '<h2 style="margin-top:30px;">Inscrits (' . count( $signups ) . ') ';
	if ( $signups ) {
		echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ag_ja_export' ), 'ag_ja_export' ) ) . '" class="button" style="vertical-align:middle;">⬇ Export CSV</a>';
	}
	echo '</h2>';
	if ( $signups ) {
		echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Barreau</th><th>Serment</th><th>École</th><th>Clé</th><th>Expire</th></tr></thead><tbody>';
		foreach ( array_slice( array_reverse( $signups ), 0, 200 ) as $s ) {
			$ts  = (int) ( $s['ts'] ?? 0 );
			$exp = $ts ? gmdate( 'd/m/Y', strtotime( '+3 months', $ts ) ) : '';
			echo '<tr><td>' . esc_html( $ts ? wp_date( 'd/m/Y', $ts ) : '' ) . '</td><td>' . esc_html( $s['name'] ?? '' ) . '</td><td>' . esc_html( $s['email'] ?? '' ) . '</td><td>' . esc_html( $s['barreau'] ?? '' ) . '</td><td>' . esc_html( $s['serment'] ?? '' ) . '</td><td>' . esc_html( $s['ecole'] ?? '' ) . '</td><td><code>' . esc_html( $s['key'] ?: '—' ) . '</code></td><td>' . esc_html( $exp ) . '</td></tr>';
		}
		echo '</tbody></table>';
	} else {
		echo '<p>Aucune inscription pour le moment.</p>';
	}
	echo '</div>';
}
