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

/** Liste par défaut EDA + UJA, avec emails de contact officiels (vérifiés). */
function ag_ja_default_partners() {
	return array(
		array( 'EFB Paris', 'EDA', 'direction@efb.fr' ),
		array( 'HEDAC Versailles', 'EDA', 'contact@hedac.fr' ),
		array( 'IXAD Lille', 'EDA', 'ixadecole@ixad.fr' ),
		array( 'ERAGE Grand Est', 'EDA', 'contact@erage.eu' ),
		array( 'EDARA Lyon', 'EDA', '' ), // non publié → formulaire edara.fr/contact
		array( 'EDASE Sud-Est', 'EDA', 'communication@edase.fr' ),
		array( 'EDAGO Grand Ouest', 'EDA', 'contact@edago.fr' ),
		array( 'EDA Centre-Sud Clermont', 'EDA', 'clermontferrand@edacentresud.com' ),
		array( 'EDA Aliénor Bordeaux', 'EDA', 'info@crfpa-alienor.com' ),
		array( 'EFA Toulouse', 'EDA', 'contact@efa-toulouse.fr' ),
		array( 'EDA Montpellier', 'EDA', 'montpellier@avocats-efacs.com' ),
		array( 'UJA Nantes', 'UJA', 'ujadenantes@gmail.com' ),
		array( 'UJA Paris', 'UJA', 'info@uja.fr' ),
		array( 'UJA Lyon', 'UJA', 'uja@ujalyon.fr' ),
		array( 'UJA Marseille', 'UJA', 'contact@ujamarseille.org' ),
		array( 'FNUJA (national)', 'UJA', 'president@fnuja.com' ),
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

/** Objet + version TEXTE (pour copier/coller manuel) : array(subject, body, share). */
function ag_ja_build_email( $label, $code ) {
	$share   = add_query_arg( 'code', $code, ag_ja_url() );
	$subject = 'Un site professionnel offert 3 mois à vos jeunes avocats — ' . $label;
	$body  = "Madame, Monsieur,\n\n";
	$body .= "Je dirige Alliance Groupe, studio web indépendant spécialisé dans les sites des professions juridiques (conformes RGPD et déontologie RIN/CNB).\n\n";
	$body .= "Nous souhaitons offrir aux jeunes avocats de " . $label . " 3 mois de site professionnel gratuits pour les aider à lancer leur cabinet : site prêt à l'emploi, recherche Judilibre intégrée, espace client sécurisé. Sans aucun coût ni engagement pour votre établissement.\n\n";
	$body .= "Le principe est simple : un code dédié à " . $label . " que vous transmettez à vos diplômés. Ils l'activent ici :\n" . $share . "\n\n";
	$body .= "Je peux vous adresser une présentation d'une page. Seriez-vous disponible 15 minutes pour en échanger ?\n\n";
	$body .= "Bien confraternellement,\nFabrizio — Alliance Groupe — 07 44 82 95 16 — advise.alliance.group@gmail.com";
	return array( $subject, $body, $share );
}

/** Version HTML brandée (logo + bouton + signature) pour l'envoi automatique. */
function ag_ja_build_email_html( $label, $code ) {
	$share = add_query_arg( 'code', $code, ag_ja_url() );
	$logo  = get_stylesheet_directory_uri() . '/assets/images/logo-carte.jpg';
	$L     = esc_html( $label );

	$inner  = '<p>Madame, Monsieur,</p>';
	$inner .= '<p>Je dirige <strong style="color:#D4B45C;">Alliance Groupe</strong>, studio web indépendant spécialisé dans les sites des professions juridiques — conformes RGPD et déontologie (RIN / vade-mecum CNB).</p>';
	$inner .= '<p>Nous souhaitons offrir aux jeunes avocats de <strong>' . $L . '</strong> <strong style="color:#fff;">3 mois de site professionnel gratuits</strong> pour les aider à lancer leur cabinet :</p>';
	$inner .= '<ul style="padding-left:18px;margin:14px 0;color:#cfcfd6;">'
		. '<li>Site prêt à l\'emploi, 100&nbsp;% français</li>'
		. '<li>Recherche de jurisprudence <strong>Judilibre</strong> intégrée au back-office</li>'
		. '<li><strong>Espace client sécurisé</strong> (dépôt de pièces confidentiel)</li>'
		. '<li>Conforme <strong>RGPD &amp; secret professionnel</strong></li>'
		. '</ul>';

	$av = get_stylesheet_directory_uri() . '/assets/images/templates/avocat/';
	$inner .= '<p style="margin:18px 0 8px;color:#D4B45C;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Aperçu du site Premium</p>';
	$inner .= '<img src="' . esc_url( $av . 'email-apercu.jpg' ) . '" alt="Aperçu du site d\'avocat Premium" width="520" style="display:block;width:100%;max-width:520px;height:auto;border-radius:12px;border:1px solid rgba(212,180,92,.25);margin:0 0 18px;">';
	$inner .= '<div style="background:rgba(212,180,92,.07);border:1px solid rgba(212,180,92,.30);border-radius:12px;padding:16px;margin:0 0 20px;">'
		. '<p style="margin:0 0 10px;color:#fff;font-weight:bold;">⚖️ La recherche Judilibre, intégrée à leur site</p>'
		. '<img src="' . esc_url( $av . 'email-judilibre.jpg' ) . '" alt="Recherche de jurisprudence Judilibre intégrée au site" width="488" style="display:block;width:100%;max-width:488px;height:auto;border-radius:10px;">'
		. '<p style="margin:10px 0 0;color:#cfcfd6;font-size:13px;">Jurisprudence en direct (Cour de cassation + cours d\'appel), filtres « font jurisprudence », année, matière — sans quitter leur back-office.</p>'
		. '</div>';

	$inner .= '<p><strong style="color:#fff;">Sans aucun coût ni engagement</strong> pour votre établissement : un simple code dédié à ' . $L . ', que vous transmettez à vos diplômés.</p>';
	$inner .= ag_email_button( 'Voir l\'offre pour vos diplômés', $share );
	$inner .= '<p style="color:#9a9aa5;font-size:13px;">Je peux vous adresser une présentation d\'une page. Seriez-vous disponible 15 minutes pour en échanger&nbsp;?</p>';
	$inner .= '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:24px;border-top:1px solid rgba(255,255,255,.12);padding-top:18px;"><tr>'
		. '<td style="padding-right:16px;vertical-align:middle;"><img src="' . esc_url( $logo ) . '" alt="Alliance Groupe" width="150" style="display:block;width:150px;height:auto;border-radius:10px;"></td>'
		. '<td style="vertical-align:middle;font-family:Arial,sans-serif;color:#e8e8ee;font-size:13px;line-height:1.55;">'
		. '<strong style="color:#fff;font-size:15px;">Fabrizio</strong><br>'
		. '<span style="color:#D4B45C;">Fondateur — Alliance Groupe</span><br>'
		. '07 44 82 95 16 &nbsp;·&nbsp; advise.alliance.group@gmail.com<br>'
		. '<a href="https://alliancegroupe-inc.com" style="color:#D4B45C;text-decoration:none;">alliancegroupe-inc.com</a> &nbsp;·&nbsp; Nantes &amp; Naples'
		. '</td></tr></table>';

	return ag_email_wrap( 'Un site offert 3 mois à vos jeunes avocats', $inner );
}

/** En-têtes d'envoi : From sur le domaine + Reply-To vers Gmail (réponses). */
function ag_ja_mail_headers() {
	return array(
		'Content-Type: text/html; charset=UTF-8',
		'From: Alliance Groupe <contact@alliancegroupe-inc.com>',
		'Reply-To: Fabrizio <advise.alliance.group@gmail.com>',
	);
}

/** Envoie l'email à UN partenaire (par code) et le marque envoyé. */
function ag_ja_send_one( $code ) {
	$partners = ag_ja_partners();
	if ( empty( $partners[ $code ] ) || ! is_array( $partners[ $code ] ) ) {
		return false;
	}
	$email = trim( (string) ( $partners[ $code ]['contact'] ?? '' ) );
	if ( ! is_email( $email ) ) {
		return false;
	}
	$label = $partners[ $code ]['label'] ?? $code;
	list( $subject ) = ag_ja_build_email( $label, $code );
	$ok = wp_mail( $email, $subject, ag_ja_build_email_html( $label, $code ), ag_ja_mail_headers() );
	if ( $ok ) {
		$partners[ $code ]['sent'] = time();
		update_option( 'ag_ja_partners', $partners );
	}
	return $ok;
}

/** Planning cron « chaque minute » pour le goutte-à-goutte. */
add_filter( 'cron_schedules', function ( $s ) {
	if ( ! isset( $s['ag_minute'] ) ) {
		$s['ag_minute'] = array( 'interval' => 60, 'display' => 'AG — chaque minute' );
	}
	return $s;
} );

/** Goutte-à-goutte : envoie 1 email de la file par exécution (délai = 1/min). */
function ag_ja_drip_unschedule() {
	$ts = wp_next_scheduled( 'ag_ja_drip' );
	if ( $ts ) {
		wp_unschedule_event( $ts, 'ag_ja_drip' );
	}
}
add_action( 'ag_ja_drip', function () {
	$queue = array_values( (array) get_option( 'ag_ja_queue', array() ) );
	if ( empty( $queue ) ) {
		ag_ja_drip_unschedule();
		return;
	}
	$code = array_shift( $queue );
	update_option( 'ag_ja_queue', $queue );
	ag_ja_send_one( $code );
	if ( empty( $queue ) ) {
		ag_ja_drip_unschedule();
	}
} );

/** « Envoyer à tous » : file d'attente + 1er envoi immédiat + goutte-à-goutte. */
add_action( 'admin_post_ag_ja_sendall', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_ja_sendall' ) ) {
		wp_die( 'Accès refusé.' );
	}
	$resend   = ! empty( $_POST['resend'] );
	$partners = ag_ja_partners();
	$queue    = array();
	$skip     = 0;
	foreach ( $partners as $code => $info ) {
		$email = is_array( $info ) ? trim( (string) ( $info['contact'] ?? '' ) ) : '';
		if ( ! is_email( $email ) ) {
			$skip++;
			continue;
		}
		if ( ! $resend && ! empty( $info['sent'] ) ) {
			continue; // déjà envoyé → on ne renvoie pas (sauf option "tout renvoyer")
		}
		$queue[] = $code;
	}
	$total = count( $queue );
	$first = 0;
	if ( $queue ) {
		$c = array_shift( $queue ); // le 1er part tout de suite (retour immédiat)
		if ( ag_ja_send_one( $c ) ) {
			$first = 1;
		}
	}
	update_option( 'ag_ja_queue', $queue );
	if ( $queue && ! wp_next_scheduled( 'ag_ja_drip' ) ) {
		wp_schedule_event( time() + 60, 'ag_minute', 'ag_ja_drip' );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-jeune-avocat', 'queued' => $total, 'first' => $first ), admin_url( 'options-general.php' ) ) );
	exit;
} );

/** Vider la file d'attente d'envoi. */
add_action( 'admin_post_ag_ja_clearqueue', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_ja_clearqueue' ) ) {
		wp_die( 'Accès refusé.' );
	}
	delete_option( 'ag_ja_queue' );
	ag_ja_drip_unschedule();
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-jeune-avocat', 'cleared' => 1 ), admin_url( 'options-general.php' ) ) );
	exit;
} );

/** Aperçu (navigateur) de l'email HTML stylé d'un partenaire. */
add_action( 'admin_post_ag_ja_preview', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_ja_preview' ) ) {
		wp_die( 'Accès refusé.' );
	}
	$code     = strtoupper( sanitize_text_field( wp_unslash( $_GET['code'] ?? '' ) ) );
	$partners = ag_ja_partners();
	$label    = ( isset( $partners[ $code ] ) && is_array( $partners[ $code ] ) ) ? ( $partners[ $code ]['label'] ?? $code ) : $code;
	echo ag_ja_build_email_html( $label, $code ); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
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
		$filled   = 0;
		$by_label = array();
		foreach ( $partners as $c => $p ) {
			if ( is_array( $p ) ) {
				$by_label[ $p['label'] ?? '' ] = $c;
			}
		}
		foreach ( ag_ja_default_partners() as $d ) {
			$label = $d[0];
			$email = isset( $d[2] ) ? $d[2] : '';
			if ( isset( $by_label[ $label ] ) ) {
				$c = $by_label[ $label ];
				if ( '' === (string) ( $partners[ $c ]['contact'] ?? '' ) && $email ) {
					$partners[ $c ]['contact'] = $email;
					$filled++;
				}
				continue;
			}
			$code = ag_ja_make_code( $label );
			$partners[ $code ] = array( 'label' => $label, 'type' => $d[1], 'contact' => $email, 'created' => time() );
			$added++;
		}
		update_option( 'ag_ja_partners', $partners );
		echo '<div class="notice notice-success is-dismissible"><p>' . (int) $added . ' partenaire(s) créé(s) · ' . (int) $filled . ' email(s) complété(s) — codes + emails EDA &amp; UJA prêts.</p></div>';
	}
	if ( isset( $_POST['ag_ja_contacts'] ) && check_admin_referer( 'ag_ja_admin' ) ) {
		$partners = ag_ja_partners();
		$emails   = isset( $_POST['contact'] ) && is_array( $_POST['contact'] ) ? wp_unslash( $_POST['contact'] ) : array();
		foreach ( $emails as $code => $mail ) {
			$code = strtoupper( sanitize_text_field( $code ) );
			if ( isset( $partners[ $code ] ) && is_array( $partners[ $code ] ) ) {
				$partners[ $code ]['contact'] = sanitize_email( $mail );
			}
		}
		update_option( 'ag_ja_partners', $partners );
		echo '<div class="notice notice-success is-dismissible"><p>Emails de contact enregistrés.</p></div>';
	}
	if ( isset( $_GET['ag_ja_del'] ) && check_admin_referer( 'ag_ja_del' ) ) {
		$partners = ag_ja_partners();
		unset( $partners[ strtoupper( sanitize_text_field( wp_unslash( $_GET['ag_ja_del'] ) ) ) ] );
		update_option( 'ag_ja_partners', $partners );
		echo '<div class="notice notice-success is-dismissible"><p>Partenaire supprimé.</p></div>';
	}

	if ( isset( $_GET['queued'] ) ) {
		$q = (int) $_GET['queued'];
		$f = isset( $_GET['first'] ) ? (int) $_GET['first'] : 0;
		echo '<div class="notice notice-success is-dismissible"><p>📨 ' . $f . ' email envoyé immédiatement · <strong>' . max( 0, $q - $f ) . ' en file d\'attente</strong> (envoi automatique à raison d\'<strong>1 email/minute</strong> pour ne pas être pris pour du spam).</p></div>';
	}
	if ( isset( $_GET['cleared'] ) ) {
		echo '<div class="notice notice-warning is-dismissible"><p>File d\'attente vidée — envoi stoppé.</p></div>';
	}

	$partners = ag_ja_partners();
	$signups  = (array) get_option( 'ag_ja_signups', array() );
	$with_mail = 0;
	foreach ( $partners as $pi ) { if ( is_array( $pi ) && is_email( (string) ( $pi['contact'] ?? '' ) ) ) { $with_mail++; } }

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
	$queue_remaining = count( (array) get_option( 'ag_ja_queue', array() ) );
	$not_sent = 0;
	foreach ( $partners as $pi ) {
		if ( is_array( $pi ) && is_email( (string) ( $pi['contact'] ?? '' ) ) && empty( $pi['sent'] ) ) {
			$not_sent++;
		}
	}
	echo '<h2 style="margin-top:30px;">Partenaires &amp; codes (' . count( $partners ) . ')</h2>';
	echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:14px 18px;margin:0 0 14px;">';
	echo '<p style="margin:0 0 10px;"><strong>📨 Démarchage automatique</strong> — ' . (int) $with_mail . ' partenaire(s) avec email · ' . (int) $not_sent . ' pas encore contacté(s).';
	if ( $queue_remaining > 0 ) {
		echo ' <span style="color:#2271b1;font-weight:600;">⏳ ' . (int) $queue_remaining . ' en cours d\'envoi (1/min)…</span>';
	}
	echo '</p>';
	if ( $with_mail > 0 ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 8px 0 0;">';
		echo '<input type="hidden" name="action" value="ag_ja_sendall">';
		wp_nonce_field( 'ag_ja_sendall' );
		echo '<button class="button button-primary" onclick="return confirm(\'Lancer l\\\'envoi (1 email par minute) ?\');">Envoyer aux non-contactés (' . (int) $not_sent . ')</button>';
		echo ' <label style="margin-left:10px;font-size:.9em;"><input type="checkbox" name="resend" value="1"> tout renvoyer (' . (int) $with_mail . ')</label>';
		echo '</form>';
	} else {
		echo '<span class="description" style="color:#b32d2e;">Renseigne les emails de contact pour activer l\'envoi.</span>';
	}
	if ( $queue_remaining > 0 ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0;">';
		echo '<input type="hidden" name="action" value="ag_ja_clearqueue">';
		wp_nonce_field( 'ag_ja_clearqueue' );
		echo '<button class="button">⏹ Arrêter / vider la file</button>';
		echo '</form>';
	}
	echo '<p class="description" style="margin:8px 0 0;">Envoi en <strong>goutte-à-goutte : 1 email/minute</strong> (anti-spam). Le 1er part tout de suite, le reste suit automatiquement (via le cron WordPress — dépend du trafic du site).</p>';
	echo '</div>';
	if ( $partners ) {
		echo '<form method="post">';
		wp_nonce_field( 'ag_ja_admin' );
		echo '<table class="widefat striped"><thead><tr><th>Code</th><th>Partenaire</th><th>Email de contact</th><th>Insc.</th><th>Lien à partager</th><th>Email</th><th></th></tr></thead><tbody>';
		$page_url = ag_ja_url();
		$i = 0;
		foreach ( $partners as $code => $info ) {
			$label = is_array( $info ) ? ( $info['label'] ?? $code ) : (string) $info;
			$type  = is_array( $info ) ? ( $info['type'] ?? '' ) : '';
			$cont  = is_array( $info ) ? ( $info['contact'] ?? '' ) : '';
			$cnt   = (int) ( $by_ecole[ $label ] ?? 0 );
			$share = add_query_arg( 'code', $code, $page_url );
			$del   = wp_nonce_url( add_query_arg( 'ag_ja_del', $code ), 'ag_ja_del' );

			list( $subject, $body ) = ag_ja_build_email( $label, $code );
			$full    = 'Objet : ' . $subject . "\n\n" . $body;
			$mailto  = 'mailto:' . rawurlencode( $cont ) . '?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( $body );
			$rid     = 'agjm' . $i;
			$sent_ts = is_array( $info ) ? (int) ( $info['sent'] ?? 0 ) : 0;
			$sent_badge = $sent_ts ? '<br><span style="color:#2a7d2a;font-size:.82em;">✓ envoyé le ' . esc_html( wp_date( 'd/m/Y', $sent_ts ) ) . '</span>' : '';

			echo '<tr>';
			echo '<td><input type="text" readonly onclick="this.select()" value="' . esc_attr( $code ) . '" style="width:130px;font-family:monospace;font-weight:700;"></td>';
			echo '<td>' . esc_html( $label ) . ' <span style="color:#aaa;font-size:.82em;">(' . esc_html( $type ) . ')</span>' . $sent_badge . '</td>';
			echo '<td><input type="email" name="contact[' . esc_attr( $code ) . ']" value="' . esc_attr( $cont ) . '" placeholder="contact@…" style="width:100%;min-width:190px;"></td>';
			echo '<td><strong>' . $cnt . '</strong></td>';
			echo '<td><input type="text" readonly onclick="this.select()" value="' . esc_attr( $share ) . '" class="code" style="width:100%;min-width:220px;"></td>';
			echo '<td><button type="button" class="button" onclick="agJaMail(\'' . esc_js( $rid ) . '\')">✉️ Email</button></td>';
			echo '<td><a href="' . esc_url( $del ) . '" onclick="return confirm(\'Supprimer ce partenaire ?\');" style="color:#b32d2e;">Suppr.</a></td>';
			echo '</tr>';
			echo '<tr id="' . esc_attr( $rid ) . '" style="display:none;background:#f6f7f7;"><td colspan="7" style="padding:14px 18px;">';
			echo '<textarea readonly class="large-text code" rows="12" onclick="this.select()" style="font-family:inherit;">' . esc_textarea( $full ) . '</textarea>';
			$preview = wp_nonce_url( admin_url( 'admin-post.php?action=ag_ja_preview&code=' . rawurlencode( $code ) ), 'ag_ja_preview' );
			echo '<p style="margin:8px 0 0;"><a class="button button-primary" href="' . esc_url( $preview ) . '" target="_blank">👁 Aperçu de l\'email stylé</a> ';
			echo '<button type="button" class="button" onclick="agJaCopy(\'' . esc_js( $rid ) . '\')">Copier (version texte)</button> ';
			if ( $cont ) {
				echo '<a class="button" href="' . esc_url( $mailto ) . '">Ouvrir dans ma messagerie</a> ';
			}
			echo '<br><span class="description">L\'envoi automatique « Envoyer à tous » utilise la version <strong>HTML stylée</strong> (logo + signature). La version texte ci-dessus sert au copier/coller manuel.</span></p>';
			echo '</td></tr>';
			$i++;
		}
		echo '</tbody></table>';
		submit_button( '💾 Enregistrer les emails de contact', 'primary', 'ag_ja_contacts' );
		echo '</form>';
		?>
		<script>
		function agJaMail(id){var r=document.getElementById(id);if(r)r.style.display=(r.style.display==='none'?'table-row':'none');}
		function agJaCopy(id){var r=document.getElementById(id);if(!r)return;var t=r.querySelector('textarea');t.select();document.execCommand('copy');var b=event.target;var o=b.textContent;b.textContent='✓ Copié';setTimeout(function(){b.textContent=o;},1500);}
		</script>
		<?php
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
