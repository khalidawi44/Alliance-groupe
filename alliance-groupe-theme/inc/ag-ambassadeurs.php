<?php
/**
 * Programme Ambassadeurs — système autonome (sans Claude).
 *
 * - Inscription publique au programme (par email) -> validee par l'admin.
 * - Declaration de ventes par les ambassadeurs.
 * - Tableau de bord admin : ambassadeurs, ventes, commissions 10%, paiements
 *   (PayPal ou autre).
 *
 * Donnees stockees dans des options WP :
 *   ag_ambassadeurs        => liste des inscrits
 *   ag_ambassadeur_ventes  => liste des ventes declarees
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'AG_COMMISSION_RATE' ) ) {
	define( 'AG_COMMISSION_RATE', 0.10 ); // 10 % par vente
}
if ( ! defined( 'AG_OVERRIDE_RATE' ) ) {
	// Parrainage : part de la commission du FILLEUL reversée au PARRAIN, sur les
	// VENTES réelles uniquement (jamais une prime pour le simple recrutement).
	// 0.20 = le parrain touche 20% de la commission du filleul (soit 2% de la vente).
	define( 'AG_OVERRIDE_RATE', 0.20 );
}
if ( ! defined( 'AG_KYC_RETENTION_DAYS' ) ) {
	define( 'AG_KYC_RETENTION_DAYS', 30 ); // RGPD : suppression auto de la piece apres X jours
}

/**
 * Infos légales de la société (pour le contrat).
 * /!\ À COMPLÉTER par l'admin : SIRET, forme juridique et adresse exacts
 * (non récupérables automatiquement ici). Modifiables via le filtre
 * 'ag_company_legal' ou directement ci-dessous.
 */
if ( ! function_exists( 'ag_company_legal' ) ) {
	function ag_company_legal() {
		return apply_filters( 'ag_company_legal', array(
			'raison'   => 'Alliance Groupe',
			'dirigeant'=> 'la Direction',
			'forme'    => 'Entreprise individuelle',
			'siren'    => '513 593 921',
			'siret'    => '513 593 921 00010',
			'tva'      => 'FR19513593921',
			'rcs'      => 'RCS Nantes',
			'adresse'  => '14 rue de Saint Jean de Luz, 44200 Nantes',
			'email'    => 'contact@alliancegroupe-inc.com',
			'site'     => 'alliancegroupe-inc.com',
		) );
	}
}

/**
 * Stockage SECURISE d'une piece d'identite (KYC).
 * - Dossier uploads/ag-kyc protege (.htaccess deny + index) + nom aleatoire.
 * - Jamais d'URL publique : la piece n'est servie qu'aux admins (voir ag_kyc_view).
 * Retourne le nom de fichier stocke, ou '' en cas d'echec.
 */
if ( ! function_exists( 'ag_kyc_dir' ) ) {
	function ag_kyc_dir() {
		$up  = wp_upload_dir();
		$dir = trailingslashit( $up['basedir'] ) . 'ag-kyc';
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
			@file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
			@file_put_contents( $dir . '/index.html', '' );
			@file_put_contents( $dir . '/web.config', '<configuration><system.webServer><authorization><deny users="*"/></authorization></system.webServer></configuration>' );
		}
		return $dir;
	}
}
if ( ! function_exists( 'ag_kyc_store' ) ) {
	function ag_kyc_store( $file ) {
		if ( empty( $file ) || ! isset( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) return '';
		if ( ! empty( $file['error'] ) ) return '';
		if ( (int) $file['size'] > 5 * 1024 * 1024 ) return '';
		$check = wp_check_filetype( $file['name'] );
		$allowed = array( 'jpg' => 1, 'jpeg' => 1, 'png' => 1, 'pdf' => 1 );
		$ext = strtolower( $check['ext'] ?? '' );
		if ( ! isset( $allowed[ $ext ] ) ) return '';
		// double-check du contenu reel
		$real = function_exists( 'mime_content_type' ) ? mime_content_type( $file['tmp_name'] ) : ( $check['type'] ?? '' );
		$ok_mimes = array( 'image/jpeg', 'image/png', 'application/pdf' );
		if ( $real && ! in_array( $real, $ok_mimes, true ) ) return '';
		$name = wp_generate_password( 24, false, false ) . '.' . $ext;
		$dest = ag_kyc_dir() . '/' . $name;
		if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) return '';
		@chmod( $dest, 0640 );
		return $name;
	}
}
if ( ! function_exists( 'ag_kyc_store_dataurl' ) ) {
	/** Stocke une photo prise en direct (selfie, dataURL base64) de façon protégée. */
	function ag_kyc_store_dataurl( $dataurl ) {
		if ( ! is_string( $dataurl ) || '' === $dataurl ) return '';
		if ( ! preg_match( '#^data:image/(jpeg|jpg|png);base64,#i', $dataurl, $m ) ) return '';
		$ext = ( 'png' === strtolower( $m[1] ) ) ? 'png' : 'jpg';
		$pos = strpos( $dataurl, ',' );
		$bin = base64_decode( substr( $dataurl, $pos + 1 ), true );
		if ( false === $bin || strlen( $bin ) < 100 ) return '';
		if ( strlen( $bin ) > 5 * 1024 * 1024 ) return '';
		$info = function_exists( 'getimagesizefromstring' ) ? @getimagesizefromstring( $bin ) : false;
		if ( ! $info || ! in_array( $info[2], array( IMAGETYPE_JPEG, IMAGETYPE_PNG ), true ) ) return '';
		$name = wp_generate_password( 24, false, false ) . '.' . $ext;
		$dest = ag_kyc_dir() . '/' . $name;
		if ( false === file_put_contents( $dest, $bin ) ) return ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		@chmod( $dest, 0640 );
		return $name;
	}
}

/* =====================================================================
   1. INSCRIPTION AU PROGRAMME
   ===================================================================== */
add_action( 'admin_post_nopriv_ag_ambassadeur_signup', 'ag_ambassadeur_signup' );
add_action( 'admin_post_ag_ambassadeur_signup', 'ag_ambassadeur_signup' );

if ( ! function_exists( 'ag_ambassadeur_signup' ) ) {
	function ag_ambassadeur_signup() {
		if ( ! isset( $_POST['ag_amb_nonce'] ) || ! wp_verify_nonce( $_POST['ag_amb_nonce'], 'ag_amb_signup' ) ) {
			wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
		}
		$name      = sanitize_text_field( $_POST['name']    ?? '' );
		$email     = sanitize_email(      $_POST['email']   ?? '' );
		$phone     = sanitize_text_field( $_POST['phone']   ?? '' );
		$city      = sanitize_text_field( $_POST['city']    ?? '' );
		$birthdate = sanitize_text_field( $_POST['birthdate'] ?? '' );
		$address   = sanitize_text_field( $_POST['address'] ?? '' );
		$payout    = sanitize_text_field( $_POST['payout_method'] ?? 'PayPal' );
		$payout_id = sanitize_text_field( $_POST['payout_id'] ?? '' );
		$motiv     = sanitize_textarea_field( $_POST['motivation'] ?? '' );
		$signature = sanitize_text_field( $_POST['signature'] ?? '' );
		$accept    = ! empty( $_POST['accept_contract'] );
		$rgpd      = ! empty( $_POST['rgpd_consent'] );
		$cp        = sanitize_text_field( $_POST['cp'] ?? '' );
		$tg_join   = ! empty( $_POST['telegram_join'] );

		if ( empty( $name ) || ! is_email( $email ) || empty( $birthdate ) || empty( $address ) ) {
			wp_die( 'Merci d\'indiquer ton nom, email, date de naissance et adresse (identité requise).', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
		}
		if ( ! $accept || empty( $signature ) ) {
			wp_die( 'Tu dois accepter le contrat d\'apporteur d\'affaires et le signer (nom complet) pour rejoindre le programme.', 'Contrat requis', array( 'response' => 400, 'back_link' => true ) );
		}
		if ( ! $rgpd ) {
			wp_die( 'Tu dois donner ton consentement RGPD pour le traitement de ta pièce d\'identité.', 'Consentement requis', array( 'response' => 400, 'back_link' => true ) );
		}
		if ( ! $tg_join ) {
			wp_die( 'Pour rejoindre le programme, tu dois t\'engager à rejoindre le groupe Telegram des ambassadeurs (entraide, annonces, prospects).', 'Telegram requis', array( 'response' => 400, 'back_link' => true ) );
		}

		// KYC : piece d'identite obligatoire, stockee de facon protegee
		$kyc_file = ag_kyc_store( $_FILES['id_document'] ?? null );
		if ( $kyc_file === '' ) {
			wp_die( 'La pièce d\'identité est obligatoire et doit être un JPG, PNG ou PDF de 5 Mo maximum.', 'Pièce d\'identité requise', array( 'response' => 400, 'back_link' => true ) );
		}

		// Photo en direct (selfie) obligatoire : sert à comparer avec la pièce d'identité.
		$selfie_file = '';
		if ( ! empty( $_POST['selfie_data'] ) ) {
			$selfie_file = ag_kyc_store_dataurl( wp_unslash( $_POST['selfie_data'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
		if ( '' === $selfie_file && ! empty( $_FILES['selfie_file'] ) ) {
			$selfie_file = ag_kyc_store( $_FILES['selfie_file'] ); // repli : champ fichier (caméra mobile)
		}
		if ( '' === $selfie_file ) {
			wp_die( 'La photo en direct (selfie) est obligatoire : prends-toi en photo pour confirmer ton identité.', 'Photo en direct requise', array( 'response' => 400, 'back_link' => true ) );
		}

		// Parrainage : qui a recruté ce nouvel ambassadeur (cookie posé via ?parrain=CODE) ?
		$parrain_ref   = isset( $_COOKIE['ag_parrain'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_COOKIE['ag_parrain'] ) ) : '';
		$parrain       = ( $parrain_ref && function_exists( 'ag_ambassadeur_by_ref' ) ) ? ag_ambassadeur_by_ref( $parrain_ref ) : null;
		$parrain_store = ( $parrain && isset( $parrain['email'] ) && strtolower( $parrain['email'] ) !== strtolower( $email ) ) ? ( $parrain['ref'] ?? $parrain_ref ) : '';

		$list = get_option( 'ag_ambassadeurs', array() );
		if ( ! is_array( $list ) ) $list = array();

		// evite les doublons d'email
		foreach ( $list as $a ) {
			if ( isset( $a['email'] ) && strtolower( $a['email'] ) === strtolower( $email ) ) {
				wp_safe_redirect( add_query_arg( array( 'ambassadeur' => 'deja' ), home_url( '/ambassadeurs' ) ) . '#rejoindre' );
				exit;
			}
		}

		$list[] = array(
			'id'         => uniqid( 'amb_' ),
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'city'       => $city,
			'cp'         => $cp,
			'birthdate'  => $birthdate,
			'address'    => $address,
			'payout'     => $payout,
			'payout_id'  => $payout_id,
			'motivation' => $motiv,
			'status'     => 'en_attente',          // -> 'actif' apres verification identite par l'admin
			'identite'   => 'a_verifier',
			'kyc_file'   => $kyc_file,
			'selfie_file' => $selfie_file,
			'kyc_ts'     => time(),
			'parrain'    => $parrain_store,
			'rgpd'       => array( 'consent' => true, 'date' => current_time( 'd/m/Y H:i' ) ),
			'contrat'    => array(
				'accepte'   => true,
				'signature' => $signature,
				'pays'      => ( function_exists( 'ag_countries' ) && in_array( sanitize_text_field( wp_unslash( $_POST['pays'] ?? '' ) ), ag_countries(), true ) ) ? sanitize_text_field( wp_unslash( $_POST['pays'] ) ) : 'France',
				'date'      => current_time( 'd/m/Y H:i' ),
				'ip'        => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
			),
			'date'       => current_time( 'd/m/Y H:i' ),
		);
		update_option( 'ag_ambassadeurs', $list );

		// Zone de prospection auto-attribuée selon la ville / le code postal déclaré.
		$assigned_dept = function_exists( 'ag_dept_from_location' ) ? ag_dept_from_location( $city, $cp, $address ) : '';
		if ( $assigned_dept && function_exists( 'ag_dept_names' ) && isset( ag_dept_names()[ $assigned_dept ] )
			&& function_exists( 'ag_zone_add_owner' ) && function_exists( 'ag_zone_of_owner' )
			&& empty( ag_zone_of_owner( $email ) ) ) {
			ag_zone_add_owner( $assigned_dept, $email, $name );
		}

		// Crée le compte ambassadeur pour son espace réservé (connexion possible,
		// déclaration de ventes seulement une fois validé par l'admin).
		do_action( 'ag_ambassadeur_registered', $email, $name );

		// Numéro -> métadonnée (1 numéro = 1 ambassadeur) si fourni et non déjà pris.
		if ( '' !== $phone ) {
			$u_new = get_user_by( 'email', $email );
			if ( $u_new ) {
				$pn = preg_replace( '/[^0-9+]/', '', $phone );
				if ( strlen( preg_replace( '/[^0-9]/', '', $pn ) ) >= 9 ) {
					$dupe = get_users( array( 'meta_key' => 'ag_amb_phone', 'meta_value' => $pn, 'exclude' => array( $u_new->ID ), 'fields' => 'ID', 'number' => 1 ) );
					if ( ! $dupe ) update_user_meta( $u_new->ID, 'ag_amb_phone', $pn );
				}
			}
		}

		// Marque l'activité initiale (évite un retrait pour inactivité juste après inscription).
		$nu = get_user_by( 'email', $email );
		if ( $nu ) update_user_meta( $nu->ID, 'ag_amb_last_active', time() );

		// Notif admin
		$body  = "Nouvelle inscription Programme Ambassadeurs\n\n";
		$body .= "Nom : $name\nEmail : $email\nTel : $phone\nVille : $city\nZone : " . ( $assigned_dept ?: 'non détectée' ) . "\n";
		$body .= "Paiement : $payout ($payout_id)\n\nMotivation :\n$motiv\n\n";
		$body .= 'Date : ' . current_time( 'd/m/Y H:i' );
		wp_mail( 'contact@alliancegroupe-inc.com', 'Nouvel ambassadeur : ' . $name, $body );

		// L'email de bienvenue brandé (avec lien « définir mon mot de passe »)
		// est envoyé par ag_create_member() via le hook ag_ambassadeur_registered.

		wp_safe_redirect( add_query_arg( array( 'ambassadeur' => 'ok' ), home_url( '/ambassadeurs' ) ) . '#rejoindre' );
		exit;
	}
}

/* =====================================================================
   2. DECLARATION D'UNE VENTE
   ===================================================================== */
add_action( 'admin_post_nopriv_ag_ambassadeur_vente', 'ag_ambassadeur_vente' );
add_action( 'admin_post_ag_ambassadeur_vente', 'ag_ambassadeur_vente' );

if ( ! function_exists( 'ag_ambassadeur_vente' ) ) {
	function ag_ambassadeur_vente() {
		if ( ! isset( $_POST['ag_vente_nonce'] ) || ! wp_verify_nonce( $_POST['ag_vente_nonce'], 'ag_amb_vente' ) ) {
			wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
		}
		$email   = sanitize_email(      $_POST['email']   ?? '' );
		$client  = sanitize_text_field( $_POST['client']  ?? '' );
		$activite= sanitize_text_field( $_POST['activite']?? '' );
		$montant = (float) str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', $_POST['montant'] ?? '0' ) );

		if ( ! is_email( $email ) || empty( $client ) || $montant <= 0 ) {
			wp_die( 'Merci d\'indiquer ton email, le client et un montant valide.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
		}

		// L'ambassadeur doit etre inscrit ET valide (identite verifiee) pour vendre
		$amb_name = ''; $amb_ok = false;
		foreach ( get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( isset( $a['email'] ) && strtolower( $a['email'] ) === strtolower( $email ) ) {
				$amb_name = $a['name'];
				$amb_ok   = ( ( $a['status'] ?? '' ) === 'actif' );
				break;
			}
		}
		if ( ! $amb_ok ) {
			wp_die(
				'Ton compte ambassadeur doit d\'abord être inscrit et validé (identité vérifiée + contrat signé) avant de déclarer une vente. Inscris-toi ou attends la validation de ton inscription.',
				'Compte non validé',
				array( 'response' => 403, 'back_link' => true )
			);
		}

		$ventes = get_option( 'ag_ambassadeur_ventes', array() );
		if ( ! is_array( $ventes ) ) $ventes = array();
		$ventes[] = array(
			'id'         => uniqid( 'v_' ),
			'email'      => $email,
			'name'       => $amb_name,
			'client'     => $client,
			'activite'   => $activite,
			'montant'    => $montant,
			'commission' => round( $montant * AG_COMMISSION_RATE, 2 ),
			'statut'     => 'declaree', // declaree -> validee -> payee
			'date'       => current_time( 'd/m/Y H:i' ),
			'date_paiement' => '',
		);
		update_option( 'ag_ambassadeur_ventes', $ventes );

		$body  = "Nouvelle vente declaree\n\n";
		$body .= "Ambassadeur : " . ( $amb_name ? $amb_name : '(non inscrit)' ) . " <$email>\n";
		$body .= "Client : $client\nActivite : $activite\n";
		$body .= 'Montant : ' . number_format( $montant, 2, ',', ' ' ) . " EUR\n";
		$body .= 'Commission (10%) : ' . number_format( $montant * AG_COMMISSION_RATE, 2, ',', ' ' ) . " EUR\n";
		$body .= 'Date : ' . current_time( 'd/m/Y H:i' );
		wp_mail( 'contact@alliancegroupe-inc.com', 'Vente declaree : ' . $client, $body );

		wp_safe_redirect( add_query_arg( array( 'vente' => 'ok' ), home_url( '/ambassadeurs' ) ) . '#declarer' );
		exit;
	}
}

/* =====================================================================
   2b. CONSULTATION SECURISEE DE LA PIECE D'IDENTITE (admin only)
   ===================================================================== */
add_action( 'admin_post_ag_kyc_view', 'ag_kyc_view' );
if ( ! function_exists( 'ag_kyc_view' ) ) {
	function ag_kyc_view() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.', 'Erreur', array( 'response' => 403 ) );
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ag_kyc_view' ) ) wp_die( 'Lien invalide.', 'Erreur', array( 'response' => 403 ) );
		$id = sanitize_text_field( $_GET['id'] ?? '' );
		$which = ( isset( $_GET['which'] ) && 'selfie' === $_GET['which'] ) ? 'selfie' : 'piece';
		$file = '';
		foreach ( get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( ( $a['id'] ?? '' ) === $id ) { $file = ( 'selfie' === $which ) ? ( $a['selfie_file'] ?? '' ) : ( $a['kyc_file'] ?? '' ); break; }
		}
		if ( ! $file ) wp_die( 'Aucun document pour cet ambassadeur.', 'Introuvable', array( 'response' => 404 ) );
		$path = ag_kyc_dir() . '/' . basename( $file ); // basename : anti path-traversal
		if ( ! file_exists( $path ) ) wp_die( 'Fichier introuvable.', 'Introuvable', array( 'response' => 404 ) );
		$ft = wp_check_filetype( $path );
		nocache_headers();
		header( 'Content-Type: ' . ( $ft['type'] ?: 'application/octet-stream' ) );
		header( 'Content-Disposition: inline; filename="' . ( 'selfie' === $which ? 'photo-en-direct' : 'piece-identite' ) . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Robots-Tag: noindex, nofollow' );
		readfile( $path );
		exit;
	}
}

/* =====================================================================
   3. TABLEAU DE BORD ADMIN
   ===================================================================== */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Ambassadeurs',
		'Ambassadeurs',
		'manage_options',
		'ag-ambassadeurs',
		'ag_render_ambassadeurs_page',
		'dashicons-groups',
		28
	);
} );

/* Diffusion Telegram à toute l'équipe d'ambassadeurs (canal interne). */
add_action( 'admin_post_ag_amb_broadcast', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_tools' ) ) wp_die( 'no' );
	$msg = trim( (string) wp_unslash( $_POST['msg'] ?? '' ) );
	// Diffuse uniquement sur le groupe Telegram interne (équipe ambassadeurs).
	$ok  = ( '' !== $msg ) && function_exists( 'ag_tg_send' ) && function_exists( 'ag_tg_cfg' ) && ag_tg_send( ag_tg_cfg( 'chat' ), '📣 ' . $msg );
	$back = wp_get_referer() ?: admin_url( 'admin.php?page=ag-ambassadeurs' );
	wp_safe_redirect( add_query_arg( 'abc', $ok ? 1 : 0, $back ) ); exit;
} );
/* Enregistre le pseudo Telegram d'un ambassadeur (pour le bouton ✈️). */
add_action( 'admin_post_ag_amb_tg_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_tools' ) ) wp_die( 'no' );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$tg    = preg_replace( '/[^A-Za-z0-9_]/', '', (string) wp_unslash( $_POST['tg'] ?? '' ) );
	$u = $email ? get_user_by( 'email', $email ) : null;
	if ( $u ) update_user_meta( $u->ID, 'ag_amb_telegram', $tg );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-ambassadeurs#amb' ) ); exit;
} );

/* ── Prime de parrainage : montant fixe au RECRUTEUR à la 1re vente du filleul ── */
if ( ! function_exists( 'ag_parrain_prime_amount' ) ) {
	function ag_parrain_prime_amount() { return 0.0; /* niveau « recruteur » supprimé (MLM 2 niveaux interdit FR) */ }
}
if ( ! function_exists( 'ag_award_parrain_prime' ) ) {
	/** Crédite (une seule fois) la prime au parrain quand son filleul fait sa 1re vente. */
	function ag_award_parrain_prime( $filleul_email ) {
		$amount = ag_parrain_prime_amount();
		if ( $amount <= 0 ) return false;
		$filleul = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $filleul_email ) : null;
		if ( ! $filleul || empty( $filleul['parrain'] ) ) return false;
		$parrain = function_exists( 'ag_ambassadeur_by_ref' ) ? ag_ambassadeur_by_ref( $filleul['parrain'] ) : null;
		if ( ! $parrain || empty( $parrain['email'] ) ) return false;
		if ( strtolower( $parrain['email'] ) === strtolower( (string) $filleul_email ) ) return false; // pas d'auto-prime
		$primes = (array) get_option( 'ag_parrain_primes', array() );
		foreach ( $primes as $p ) { if ( strtolower( $p['filleul_email'] ?? '' ) === strtolower( (string) $filleul_email ) ) return false; } // 1 prime / filleul
		$primes[] = array(
			'id'            => uniqid( 'prime_' ),
			'parrain_email' => strtolower( $parrain['email'] ),
			'parrain_name'  => $parrain['name'] ?? $parrain['email'],
			'filleul_email' => strtolower( (string) $filleul_email ),
			'filleul_name'  => $filleul['name'] ?? $filleul_email,
			'amount'        => $amount,
			'date'          => current_time( 'd/m/Y' ),
			'ts'            => time(),
			'statut'        => 'due',
		);
		update_option( 'ag_parrain_primes', $primes );
		$nm = $parrain['name'] ?? $parrain['email'];
		if ( function_exists( 'ag_push' ) ) ag_push( '🎁 Prime de parrainage : ' . number_format( $amount, 0, ',', ' ' ) . ' €', $nm . ' a gagné une prime — son filleul ' . ( $filleul['name'] ?? '' ) . ' a fait sa 1re vente.' );
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🎁 Prime de parrainage ' . number_format( $amount, 0, ',', ' ' ) . ' € pour ' . $nm . ' (filleul : ' . ( $filleul['name'] ?? '' ) . ')' );
		wp_mail( $parrain['email'], '🎁 Tu as gagné une prime de parrainage !', "Bravo $nm,\n\nTon filleul " . ( $filleul['name'] ?? '' ) . " vient de réaliser sa première vente.\nTu gagnes une prime de parrainage de " . number_format( $amount, 0, ',', ' ' ) . " € (en plus de ton override sur ses ventes).\n\nMerci de faire grandir l'équipe 💪\nAlliance Groupe" );
		return true;
	}
}
if ( ! function_exists( 'ag_parrain_primes_for' ) ) {
	function ag_parrain_primes_for( $email ) {
		$email = strtolower( (string) $email ); $out = array();
		foreach ( (array) get_option( 'ag_parrain_primes', array() ) as $p ) { if ( strtolower( $p['parrain_email'] ?? '' ) === $email ) $out[] = $p; }
		return $out;
	}
}
if ( ! function_exists( 'ag_recruiter_leaderboard' ) ) {
	/** Classement des recruteurs : nb de filleuls, filleuls actifs (≥1 vente), primes gagnées. */
	function ag_recruiter_leaderboard() {
		$ambs = (array) get_option( 'ag_ambassadeurs', array() );
		// Vendeurs actifs (emails ayant ≥1 vente validée/payée).
		$sellers = array();
		foreach ( (array) get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			if ( in_array( $v['statut'] ?? '', array( 'validee', 'payee' ), true ) ) $sellers[ strtolower( $v['email'] ?? '' ) ] = 1;
		}
		// Primes par parrain.
		$primes = array();
		foreach ( (array) get_option( 'ag_parrain_primes', array() ) as $p ) {
			$pe = strtolower( $p['parrain_email'] ?? '' ); if ( $pe ) $primes[ $pe ] = ( $primes[ $pe ] ?? 0 ) + (float) ( $p['amount'] ?? 0 );
		}
		// Index ref -> email.
		$by_ref = array();
		foreach ( $ambs as $a ) { if ( ! empty( $a['ref'] ) ) $by_ref[ strtoupper( $a['ref'] ) ] = strtolower( $a['email'] ?? '' ); }
		$agg = array();
		foreach ( $ambs as $a ) {
			$pr = strtoupper( (string) ( $a['parrain'] ?? '' ) );
			if ( '' === $pr || empty( $by_ref[ $pr ] ) ) continue;
			$pe = $by_ref[ $pr ];
			if ( ! isset( $agg[ $pe ] ) ) $agg[ $pe ] = array( 'email' => $pe, 'name' => '', 'filleuls' => 0, 'actifs' => 0 );
			$agg[ $pe ]['filleuls']++;
			if ( ! empty( $sellers[ strtolower( $a['email'] ?? '' ) ] ) ) $agg[ $pe ]['actifs']++;
		}
		foreach ( $agg as $pe => &$row ) {
			$rec = ag_ambassadeur_record( $pe );
			$row['name']   = $rec['name'] ?? $pe;
			$row['primes'] = $primes[ $pe ] ?? 0;
		}
		unset( $row );
		$agg = array_values( $agg );
		usort( $agg, function ( $a, $b ) { return ( $b['actifs'] <=> $a['actifs'] ) ?: ( $b['filleuls'] <=> $a['filleuls'] ); } );
		$r = 1; foreach ( $agg as &$row ) { $row['rank'] = $r++; } unset( $row );
		// Filtre d'injection (ex. recruteurs démo — cf. inc/ag-demo-board.php).
		return apply_filters( 'ag_recruiter_leaderboard', $agg );
	}
}
/* Marquer une prime payée. */
add_action( 'admin_post_ag_prime_pay', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_tools' ) ) wp_die( 'no' );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$primes = (array) get_option( 'ag_parrain_primes', array() );
	foreach ( $primes as $k => $p ) { if ( ( $p['id'] ?? '' ) === $id ) { $primes[ $k ]['statut'] = 'payee'; $primes[ $k ]['date_paiement'] = current_time( 'd/m/Y' ); break; } }
	update_option( 'ag_parrain_primes', $primes );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-ambassadeurs#primes' ) ); exit;
} );
/* Réglage du montant de la prime. */
add_action( 'admin_post_ag_prime_amount_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_tools' ) ) wp_die( 'no' );
	update_option( 'ag_parrain_prime', max( 0, (float) ( $_POST['amount'] ?? 25 ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-ambassadeurs#primes' ) ); exit;
} );

if ( ! function_exists( 'ag_render_ambassadeurs_page' ) ) {
	function ag_render_ambassadeurs_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		// --- Actions admin (validees par nonce) ---
		if ( isset( $_GET['ag_action'], $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ag_amb_admin' ) ) {
			$act = sanitize_key( $_GET['ag_action'] );
			$id  = sanitize_text_field( $_GET['id'] ?? '' );

			if ( in_array( $act, array( 'amb_valider', 'amb_suppr', 'amb_kyc_suppr' ), true ) ) {
				$list = get_option( 'ag_ambassadeurs', array() );
				foreach ( $list as $k => $a ) {
					if ( $a['id'] === $id ) {
						if ( $act === 'amb_valider' ) { $list[ $k ]['status'] = 'actif'; $list[ $k ]['identite'] = 'verifiee'; }
						if ( $act === 'amb_kyc_suppr' || $act === 'amb_suppr' ) {
							if ( ! empty( $a['kyc_file'] ) ) { @unlink( ag_kyc_dir() . '/' . basename( $a['kyc_file'] ) ); }
							if ( ! empty( $a['selfie_file'] ) ) { @unlink( ag_kyc_dir() . '/' . basename( $a['selfie_file'] ) ); }
						}
						if ( $act === 'amb_kyc_suppr' ) { $list[ $k ]['kyc_file'] = ''; $list[ $k ]['selfie_file'] = ''; }
						if ( $act === 'amb_suppr' )      unset( $list[ $k ] );
					}
				}
				update_option( 'ag_ambassadeurs', array_values( $list ) );
			}

			if ( in_array( $act, array( 'v_valider', 'v_payer', 'v_suppr' ), true ) ) {
				$ventes = get_option( 'ag_ambassadeur_ventes', array() );
				$award_email = '';
				foreach ( $ventes as $k => $v ) {
					if ( $v['id'] === $id ) {
						if ( $act === 'v_valider' ) { $ventes[ $k ]['statut'] = 'validee'; $award_email = $v['email'] ?? ''; }
						if ( $act === 'v_payer' )  { $ventes[ $k ]['statut'] = 'payee'; $ventes[ $k ]['date_paiement'] = current_time( 'd/m/Y' ); $award_email = $v['email'] ?? ''; }
						if ( $act === 'v_suppr' )  unset( $ventes[ $k ] );
					}
				}
				update_option( 'ag_ambassadeur_ventes', array_values( $ventes ) );
				if ( $award_email && function_exists( 'ag_award_parrain_prime' ) ) ag_award_parrain_prime( $award_email );
			}
			echo '<div class="notice notice-success is-dismissible"><p>Action effectuée.</p></div>';
		}

		$ambs   = array_reverse( get_option( 'ag_ambassadeurs', array() ) );
		$ventes = array_reverse( get_option( 'ag_ambassadeur_ventes', array() ) );

		// --- Synthese commissions par ambassadeur ---
		$synthese = array();
		foreach ( get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			$key = strtolower( $v['email'] );
			if ( ! isset( $synthese[ $key ] ) ) {
				$synthese[ $key ] = array( 'name' => $v['name'] ?: $v['email'], 'email' => $v['email'], 'ventes' => 0, 'ca' => 0, 'due' => 0, 'payee' => 0 );
			}
			if ( in_array( $v['statut'], array( 'validee', 'payee' ), true ) ) {
				$synthese[ $key ]['ventes'] += 1;
				$synthese[ $key ]['ca']     += (float) $v['montant'];
			}
			if ( $v['statut'] === 'validee' ) $synthese[ $key ]['due']   += (float) $v['commission'];
			if ( $v['statut'] === 'payee' )   $synthese[ $key ]['payee'] += (float) $v['commission'];
		}
		uasort( $synthese, function ( $a, $b ) { return $b['ca'] <=> $a['ca']; } );

		$eur = function ( $n ) { return number_format( (float) $n, 2, ',', ' ' ) . ' €'; };
		$nonce_url = function ( $act, $id ) {
			return wp_nonce_url( admin_url( 'admin.php?page=ag-ambassadeurs&ag_action=' . $act . '&id=' . $id ), 'ag_amb_admin' );
		};

		echo '<div class="wrap"><h1>Programme Ambassadeurs</h1>';
		echo '<p>Commission : <strong>' . esc_html( (int) ( AG_COMMISSION_RATE * 100 ) ) . '%</strong> par vente. Les commissions « à payer » correspondent aux ventes validées non encore payées.</p>';

		// Réglage du taux de parrainage (override)
		if ( isset( $_POST['ag_save_override'] ) && check_admin_referer( 'ag_override' ) ) {
			$pct = max( 0, min( 100, (float) str_replace( ',', '.', $_POST['ag_override_pct'] ?? '' ) ) );
			update_option( 'ag_override_rate', $pct / 100 );
			echo '<div class="notice notice-success is-dismissible"><p>Taux de parrainage enregistré : ' . esc_html( $pct ) . '%.</p></div>';
		}
		$ov_rate = function_exists( 'ag_override_rate' ) ? ag_override_rate() : 0.20;
		echo '<form method="post" style="margin:6px 0 22px;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;max-width:560px;">';
		wp_nonce_field( 'ag_override' );
		echo '<strong>Taux de parrainage</strong> <small style="color:#646970;">— part de la commission du filleul reversée au parrain (sur ventes réelles uniquement)</small><br>';
		echo '<input type="number" step="1" min="0" max="100" name="ag_override_pct" value="' . esc_attr( round( $ov_rate * 100 ) ) . '" style="width:90px;margin-top:8px;"> %&nbsp; ';
		echo '<button class="button button-primary" name="ag_save_override" value="1">Enregistrer</button>';
		echo '</form>';

		// SYNTHESE
		echo '<h2>💰 Commissions par ambassadeur</h2>';
		if ( empty( $synthese ) ) {
			echo '<p>Aucune vente pour le moment.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Ambassadeur</th><th>Email</th><th>Ventes</th><th>CA généré</th><th>Commission à payer</th><th>Déjà payé</th></tr></thead><tbody>';
			$tot_due = 0; $tot_ca = 0;
			foreach ( $synthese as $s ) {
				$tot_due += $s['due']; $tot_ca += $s['ca'];
				echo '<tr><td><strong>' . esc_html( $s['name'] ) . '</strong></td>';
				echo '<td><a href="mailto:' . esc_attr( $s['email'] ) . '">' . esc_html( $s['email'] ) . '</a></td>';
				echo '<td>' . (int) $s['ventes'] . '</td>';
				echo '<td>' . esc_html( $eur( $s['ca'] ) ) . '</td>';
				echo '<td><strong style="color:#b8860b;">' . esc_html( $eur( $s['due'] ) ) . '</strong></td>';
				echo '<td>' . esc_html( $eur( $s['payee'] ) ) . '</td></tr>';
			}
			echo '<tr style="font-weight:700;background:#f6f7f7;"><td colspan="3">TOTAL</td><td>' . esc_html( $eur( $tot_ca ) ) . '</td><td>' . esc_html( $eur( $tot_due ) ) . '</td><td></td></tr>';
			echo '</tbody></table>';
		}

		// BONUS DE PARRAINAGE (override sur les ventes des filleuls)
		$parrains = array();
		foreach ( get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( empty( $a['ref'] ) ) continue;
			$ov = function_exists( 'ag_ambassadeur_override_for' ) ? ag_ambassadeur_override_for( $a['ref'] ) : array( 'team' => 0, 'generated' => 0, 'paid' => 0 );
			if ( $ov['team'] > 0 ) $parrains[] = array( 'name' => $a['name'] ?? $a['email'], 'email' => $a['email'], 'ov' => $ov );
		}
		echo '<h2 style="margin-top:30px;">🌐 Bonus de parrainage <small style="font-weight:400;color:#646970;">(' . esc_html( (int) round( ( function_exists( 'ag_override_rate' ) ? ag_override_rate() : 0.2 ) * 100 ) ) . '% de la commission des filleuls, sur ventes réelles)</small></h2>';
		if ( empty( $parrains ) ) {
			echo '<p>Aucun parrainage avec ventes pour le moment.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Parrain</th><th>Email</th><th>Filleuls</th><th>Bonus généré</th><th>Déjà payé</th></tr></thead><tbody>';
			foreach ( $parrains as $p ) {
				echo '<tr><td><strong>' . esc_html( $p['name'] ) . '</strong></td>';
				echo '<td><a href="mailto:' . esc_attr( $p['email'] ) . '">' . esc_html( $p['email'] ) . '</a></td>';
				echo '<td>' . (int) $p['ov']['team'] . '</td>';
				echo '<td><strong style="color:#b8860b;">' . esc_html( $eur( $p['ov']['generated'] ) ) . '</strong></td>';
				echo '<td>' . esc_html( $eur( $p['ov']['paid'] ) ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}

		// PRIMES DE PARRAINAGE (recruteurs).
		$primes    = array_reverse( (array) get_option( 'ag_parrain_primes', array() ) );
		$prime_amt = ag_parrain_prime_amount();
		echo '<h2 id="primes" style="margin-top:30px;">🎁 Primes de parrainage <small style="font-weight:400;color:#646970;">(prime fixe au recruteur à la 1re vente de son filleul)</small></h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0 0 12px;"><input type="hidden" name="action" value="ag_prime_amount_save">' . wp_nonce_field( 'ag_amb_tools', '_n', true, false ) . 'Montant de la prime : <input type="number" name="amount" value="' . esc_attr( $prime_amt ) . '" step="1" min="0" style="width:90px;"> € <button class="button">Enregistrer</button></form>';
		if ( empty( $primes ) ) {
			echo '<p>Aucune prime pour le moment. Dès qu\'un filleul fait sa 1re vente, le recruteur gagne ' . esc_html( number_format( $prime_amt, 0, ',', ' ' ) ) . ' €.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Recruteur (parrain)</th><th>Filleul</th><th>Prime</th><th>Statut</th><th></th></tr></thead><tbody>';
			foreach ( $primes as $pr ) {
				$pay = ( ( $pr['statut'] ?? '' ) === 'payee' );
				echo '<tr><td>' . esc_html( $pr['date'] ?? '' ) . '</td>';
				echo '<td><strong>' . esc_html( $pr['parrain_name'] ?? '' ) . '</strong><br><small>' . esc_html( $pr['parrain_email'] ?? '' ) . '</small></td>';
				echo '<td>' . esc_html( $pr['filleul_name'] ?? '' ) . '</td>';
				echo '<td><strong style="color:#b8860b;">' . esc_html( $eur( $pr['amount'] ?? 0 ) ) . '</strong></td>';
				echo '<td>' . ( $pay ? '<span style="color:#46b450;font-weight:700;">payée</span>' : '<span style="color:#b26a00;">à payer</span>' ) . '</td>';
				echo '<td>' . ( $pay ? '' : '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0;"><input type="hidden" name="action" value="ag_prime_pay"><input type="hidden" name="id" value="' . esc_attr( $pr['id'] ?? '' ) . '">' . wp_nonce_field( 'ag_amb_tools', '_n', true, false ) . '<button class="button button-small button-primary">Marquer payée</button></form>' ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}

		// VENTES
		echo '<h2 style="margin-top:30px;">🧾 Ventes déclarées</h2>';
		if ( empty( $ventes ) ) {
			echo '<p>Aucune vente.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Ambassadeur</th><th>Client</th><th>Activité</th><th>Montant</th><th>Commission</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
			foreach ( $ventes as $v ) {
				$badge = array( 'declaree' => '#999', 'validee' => '#2271b1', 'payee' => '#46b450' );
				$col = $badge[ $v['statut'] ] ?? '#999';
				echo '<tr>';
				echo '<td>' . esc_html( $v['date'] ) . '</td>';
				echo '<td>' . esc_html( $v['name'] ?: $v['email'] ) . '</td>';
				echo '<td>' . esc_html( $v['client'] ) . '</td>';
				echo '<td>' . esc_html( $v['activite'] ) . '</td>';
				echo '<td>' . esc_html( $eur( $v['montant'] ) ) . '</td>';
				echo '<td>' . esc_html( $eur( $v['commission'] ) ) . '</td>';
				echo '<td><span style="color:#fff;background:' . esc_attr( $col ) . ';padding:2px 8px;border-radius:10px;font-size:11px;">' . esc_html( strtoupper( $v['statut'] ) ) . '</span>';
				if ( $v['statut'] === 'payee' && ! empty( $v['date_paiement'] ) ) echo '<br><small>le ' . esc_html( $v['date_paiement'] ) . '</small>';
				echo '</td>';
				echo '<td>';
				if ( $v['statut'] === 'declaree' ) echo '<a class="button button-small" href="' . esc_url( $nonce_url( 'v_valider', $v['id'] ) ) . '">Valider</a> ';
				if ( $v['statut'] === 'validee' )  echo '<a class="button button-primary button-small" href="' . esc_url( $nonce_url( 'v_payer', $v['id'] ) ) . '">Marquer payé</a> ';
				echo '<a class="button button-small" style="color:#b32d2e;" href="' . esc_url( $nonce_url( 'v_suppr', $v['id'] ) ) . '" onclick="return confirm(\'Supprimer cette vente ?\')">✕</a>';
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}

		// AMBASSADEURS
		echo '<h2 id="amb" style="margin-top:30px;">🤝 Ambassadeurs inscrits</h2>';

		// Métriques (ventes par ambassadeur) pour tri/filtre.
		$ventes_by = array();
		foreach ( (array) get_option( 'ag_ambassadeur_ventes', array() ) as $vv ) { $oe = strtolower( $vv['email'] ?? '' ); if ( $oe ) $ventes_by[ $oe ] = ( $ventes_by[ $oe ] ?? 0 ) + 1; }
		$azone   = isset( $_GET['azone'] ) ? sanitize_text_field( wp_unslash( $_GET['azone'] ) ) : '';
		$asort   = isset( $_GET['asort'] ) ? sanitize_text_field( wp_unslash( $_GET['asort'] ) ) : 'recent';
		$afilter = isset( $_GET['afilter'] ) ? sanitize_text_field( wp_unslash( $_GET['afilter'] ) ) : '';

		// Outils : diffusion Telegram + filtres/tri.
		if ( isset( $_GET['abc'] ) ) echo '<div class="notice notice-' . ( $_GET['abc'] ? 'success' : 'error' ) . ' is-dismissible"><p>' . ( $_GET['abc'] ? '📣 Annonce envoyée sur le Telegram des ambassadeurs.' : 'Échec : configure le canal interne dans Notifications téléphone.' ) . '</p></div>';
		echo '<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;border-radius:8px;padding:14px 18px;max-width:980px;margin:10px 0 16px;">';
		echo '<strong>📣 Message Telegram à tous les ambassadeurs</strong> <span style="color:#646970;font-size:.85em;">(annonce, offre, relance — envoyé au groupe interne)</span>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:8px;"><input type="hidden" name="action" value="ag_amb_broadcast">' . wp_nonce_field( 'ag_amb_tools', '_n', true, false ) . '<textarea name="msg" rows="2" style="width:100%;" placeholder="Ex : 🔥 Nouvelle offre ce week-end : foncez ! Lien : ..."></textarea><button class="button button-primary" style="margin-top:6px;">Envoyer à tous</button></form>';
		echo '</div>';

		// Filtre / tri.
		$dn = function_exists( 'ag_dept_names' ) ? ag_dept_names() : array();
		echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '" style="margin:0 0 12px;"><input type="hidden" name="page" value="ag-ambassadeurs">';
		echo 'Zone : <select name="azone"><option value="">Toutes</option>';
		foreach ( $dn as $code => $nom ) { echo '<option value="' . esc_attr( $code ) . '" ' . selected( $azone, $code, false ) . '>' . esc_html( $code . ' ' . $nom ) . '</option>'; }
		echo '</select> ';
		echo 'Filtre : <select name="afilter"><option value="">Tous</option>'
			. '<option value="actif" ' . selected( $afilter, 'actif', false ) . '>Actifs</option>'
			. '<option value="attente" ' . selected( $afilter, 'attente', false ) . '>En attente de validation</option>'
			. '<option value="sanszone" ' . selected( $afilter, 'sanszone', false ) . '>Sans zone</option>'
			. '<option value="sansvente" ' . selected( $afilter, 'sansvente', false ) . '>Sans vente (à relancer / inactifs)</option>'
			. '</select> ';
		echo 'Tri : <select name="asort"><option value="recent" ' . selected( $asort, 'recent', false ) . '>Plus récents</option>'
			. '<option value="ventes" ' . selected( $asort, 'ventes', false ) . '>Travaillent le + (ventes)</option>'
			. '<option value="ventes_asc" ' . selected( $asort, 'ventes_asc', false ) . '>Travaillent le - (ventes)</option>'
			. '<option value="nom" ' . selected( $asort, 'nom', false ) . '>Nom</option></select> ';
		echo '<button class="button">Filtrer</button> <a class="button" href="' . esc_url( admin_url( 'admin.php?page=ag-ambassadeurs' ) ) . '">Réinitialiser</a></form>';

		// Application filtre/tri.
		$ambs = array_values( array_filter( $ambs, function ( $a ) use ( $azone, $afilter, $ventes_by ) {
			$em = strtolower( $a['email'] ?? '' );
			$zones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $em ) : array();
			$nv = $ventes_by[ $em ] ?? 0;
			$actif = ( ( $a['status'] ?? '' ) === 'actif' );
			if ( '' !== $azone && ! in_array( $azone, $zones, true ) ) return false;
			if ( 'actif' === $afilter && ! $actif ) return false;
			if ( 'attente' === $afilter && $actif ) return false;
			if ( 'sanszone' === $afilter && ! empty( $zones ) ) return false;
			if ( 'sansvente' === $afilter && $nv > 0 ) return false;
			return true;
		} ) );
		usort( $ambs, function ( $a, $b ) use ( $asort, $ventes_by ) {
			$na = $ventes_by[ strtolower( $a['email'] ?? '' ) ] ?? 0;
			$nb = $ventes_by[ strtolower( $b['email'] ?? '' ) ] ?? 0;
			if ( 'ventes' === $asort ) return $nb <=> $na;
			if ( 'ventes_asc' === $asort ) return $na <=> $nb;
			if ( 'nom' === $asort ) return strcasecmp( $a['name'] ?? '', $b['name'] ?? '' );
			return 0;
		} );

		if ( empty( $ambs ) ) {
			echo '<p>Aucun ambassadeur ne correspond.</p>';
		} else {
			echo '<p style="color:#646970;">« Activer » = identité vérifiée + contrat OK → l\'ambassadeur peut alors déclarer des ventes.</p>';
			echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Tél / Zone / Ventes</th><th>Contacter</th><th>Identité</th><th>Contrat signé</th><th>Paiement</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
			foreach ( $ambs as $a ) {
				$contrat = $a['contrat'] ?? array();
				$em  = strtolower( $a['email'] ?? '' );
				$ph  = $a['phone'] ?? '';
				$zones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $em ) : array();
				$nv  = $ventes_by[ $em ] ?? 0;
				echo '<tr>';
				echo '<td>' . esc_html( $a['date'] ?? '' ) . '</td>';
				echo '<td><strong>' . esc_html( $a['name'] ?? '' ) . '</strong>' . ( ! empty( $a['parrain'] ) ? '<br><small style="color:#646970;">parrain : ' . esc_html( $a['parrain'] ) . '</small>' : '' ) . '</td>';
				echo '<td><a href="mailto:' . esc_attr( $a['email'] ?? '' ) . '">' . esc_html( $a['email'] ?? '' ) . '</a></td>';
				echo '<td style="font-size:12px;">' . esc_html( ( $ph ?: '—' ) . ' · ' . ( $a['city'] ?? '' ) ) . '<br>🗺️ ' . ( $zones ? esc_html( implode( ', ', $zones ) ) : '<span style="color:#b32d2e;">aucune</span>' ) . ' · 💰 ' . (int) $nv . ' vente(s)</td>';
				// Contacter : SMS / WhatsApp / Telegram.
				$smsn = preg_replace( '/[^0-9+]/', '', $ph );
				$wan  = function_exists( 'ag_wa_number' ) ? ag_wa_number( $ph ) : preg_replace( '/[^0-9]/', '', $ph );
				$body = rawurlencode( 'Salut ' . ( $a['name'] ? explode( ' ', trim( $a['name'] ) )[0] : '' ) . ' ! ' );
				$tg_user = ( $u2 = get_user_by( 'email', $em ) ) ? get_user_meta( $u2->ID, 'ag_amb_telegram', true ) : '';
				echo '<td style="font-size:12px;white-space:nowrap;">';
				if ( $smsn ) echo '<a class="button button-small" href="sms:' . esc_attr( $smsn ) . '?body=' . $body . '">📱 SMS</a> ';
				if ( $wan ) echo '<a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/' . esc_attr( $wan ) . '?text=' . $body . '">🟢 WhatsApp</a> ';
				if ( $tg_user ) echo '<a class="button button-small" target="_blank" rel="noopener" href="https://t.me/' . esc_attr( $tg_user ) . '">✈️ Telegram</a>';
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:4px;display:flex;gap:4px;"><input type="hidden" name="action" value="ag_amb_tg_save">' . wp_nonce_field( 'ag_amb_tools', '_n', true, false ) . '<input type="hidden" name="email" value="' . esc_attr( $em ) . '"><input type="text" name="tg" value="' . esc_attr( $tg_user ) . '" placeholder="@pseudo Telegram" style="width:120px;font-size:11px;"><button class="button button-small">💾</button></form>';
				echo '</td>';
				echo '<td style="max-width:220px;white-space:normal;font-size:12px;">Né(e) : ' . esc_html( $a['birthdate'] ?? '—' ) . '<br>' . esc_html( $a['address'] ?? '—' );
				if ( ! empty( $a['kyc_file'] ) ) {
					$kyc_view = wp_nonce_url( admin_url( 'admin-post.php?action=ag_kyc_view&id=' . $a['id'] ), 'ag_kyc_view' );
					echo '<br>📄 <a href="' . esc_url( $kyc_view ) . '" target="_blank" rel="noopener">Voir la pièce</a> · <a href="' . esc_url( $nonce_url( 'amb_kyc_suppr', $a['id'] ) ) . '" style="color:#b32d2e;" onclick="return confirm(\'Supprimer la pièce ?\')">suppr.</a>';
				} else {
					echo '<br><span style="color:#b32d2e;">pas de pièce</span>';
				}
				if ( ! empty( $a['selfie_file'] ) ) {
					$selfie_view = wp_nonce_url( admin_url( 'admin-post.php?action=ag_kyc_view&which=selfie&id=' . $a['id'] ), 'ag_kyc_view' );
					echo '<br>🤳 <a href="' . esc_url( $selfie_view ) . '" target="_blank" rel="noopener"><strong>Voir la photo en direct</strong></a> <small style="color:#646970;">(à comparer avec la pièce)</small>';
				} else {
					echo '<br><span style="color:#b32d2e;">pas de photo en direct</span>';
				}
				echo '</td>';
				if ( ! empty( $contrat['accepte'] ) ) {
					echo '<td style="font-size:12px;">✅ Signé<br>' . esc_html( $contrat['signature'] ?? '' ) . '<br><small>' . esc_html( $contrat['date'] ?? '' ) . ' · IP ' . esc_html( $contrat['ip'] ?? '' ) . '</small></td>';
				} else {
					echo '<td style="color:#b32d2e;">non</td>';
				}
				echo '<td style="font-size:12px;">' . esc_html( ( $a['payout'] ?? '' ) . ' ' . ( $a['payout_id'] ?? '' ) ) . '</td>';
				$actif = ( ( $a['status'] ?? '' ) === 'actif' );
				echo '<td>' . ( $actif ? '<span style="color:#46b450;font-weight:700;">ACTIF</span>' : '<span style="color:#999;">à vérifier</span>' ) . '</td>';
				echo '<td>';
				if ( ! $actif ) echo '<a class="button button-primary button-small" href="' . esc_url( $nonce_url( 'amb_valider', $a['id'] ) ) . '">Activer</a> ';
				echo '<a class="button button-small" style="color:#b32d2e;" href="' . esc_url( $nonce_url( 'amb_suppr', $a['id'] ) ) . '" onclick="return confirm(\'Supprimer cet ambassadeur ?\')">✕</a>';
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}

		echo '</div>';
	}
}

/* =====================================================================
   4. RGPD — suppression automatique des pieces d'identite (cron quotidien)
   ===================================================================== */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_kyc_cleanup' ) ) {
		wp_schedule_event( time() + 3600, 'daily', 'ag_kyc_cleanup' );
	}
} );
add_action( 'ag_kyc_cleanup', 'ag_kyc_cleanup_run' );
if ( ! function_exists( 'ag_kyc_cleanup_run' ) ) {
	function ag_kyc_cleanup_run() {
		$list = get_option( 'ag_ambassadeurs', array() );
		if ( ! is_array( $list ) ) return;
		$changed = false;
		$limit = AG_KYC_RETENTION_DAYS * DAY_IN_SECONDS;
		foreach ( $list as $k => $a ) {
			if ( ! empty( $a['kyc_ts'] ) && ( time() - (int) $a['kyc_ts'] ) > $limit && ( ! empty( $a['kyc_file'] ) || ! empty( $a['selfie_file'] ) ) ) {
				if ( ! empty( $a['kyc_file'] ) )    @unlink( ag_kyc_dir() . '/' . basename( $a['kyc_file'] ) );
				if ( ! empty( $a['selfie_file'] ) ) @unlink( ag_kyc_dir() . '/' . basename( $a['selfie_file'] ) );
				$list[ $k ]['kyc_file']    = '';
				$list[ $k ]['selfie_file'] = '';
				$list[ $k ]['kyc_purged']  = current_time( 'd/m/Y' );
				$changed = true;
			}
		}
		if ( $changed ) update_option( 'ag_ambassadeurs', $list );
	}
}
