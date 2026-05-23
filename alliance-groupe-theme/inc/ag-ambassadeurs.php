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
			'raison'   => 'Entreprise individuelle Fabrice Doucet (enseigne « Alliance Groupe »)',
			'dirigeant'=> 'Fabrice Doucet',
			'forme'    => 'Entreprise individuelle (artisan)',
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

		if ( empty( $name ) || ! is_email( $email ) || empty( $birthdate ) || empty( $address ) ) {
			wp_die( 'Merci d\'indiquer ton nom, email, date de naissance et adresse (identité requise).', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
		}
		if ( ! $accept || empty( $signature ) ) {
			wp_die( 'Tu dois accepter le contrat d\'apporteur d\'affaires et le signer (nom complet) pour rejoindre le programme.', 'Contrat requis', array( 'response' => 400, 'back_link' => true ) );
		}
		if ( ! $rgpd ) {
			wp_die( 'Tu dois donner ton consentement RGPD pour le traitement de ta pièce d\'identité.', 'Consentement requis', array( 'response' => 400, 'back_link' => true ) );
		}

		// KYC : piece d'identite obligatoire, stockee de facon protegee
		$kyc_file = ag_kyc_store( $_FILES['id_document'] ?? null );
		if ( $kyc_file === '' ) {
			wp_die( 'La pièce d\'identité est obligatoire et doit être un JPG, PNG ou PDF de 5 Mo maximum.', 'Pièce d\'identité requise', array( 'response' => 400, 'back_link' => true ) );
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
			'birthdate'  => $birthdate,
			'address'    => $address,
			'payout'     => $payout,
			'payout_id'  => $payout_id,
			'motivation' => $motiv,
			'status'     => 'en_attente',          // -> 'actif' apres verification identite par l'admin
			'identite'   => 'a_verifier',
			'kyc_file'   => $kyc_file,
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

		// Crée le compte ambassadeur pour son espace réservé (connexion possible,
		// déclaration de ventes seulement une fois validé par l'admin).
		do_action( 'ag_ambassadeur_registered', $email, $name );

		// Notif admin
		$body  = "Nouvelle inscription Programme Ambassadeurs\n\n";
		$body .= "Nom : $name\nEmail : $email\nTel : $phone\nVille : $city\n";
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
		$file = '';
		foreach ( get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( ( $a['id'] ?? '' ) === $id ) { $file = $a['kyc_file'] ?? ''; break; }
		}
		if ( ! $file ) wp_die( 'Aucune pièce pour cet ambassadeur.', 'Introuvable', array( 'response' => 404 ) );
		$path = ag_kyc_dir() . '/' . basename( $file ); // basename : anti path-traversal
		if ( ! file_exists( $path ) ) wp_die( 'Fichier introuvable.', 'Introuvable', array( 'response' => 404 ) );
		$ft = wp_check_filetype( $path );
		nocache_headers();
		header( 'Content-Type: ' . ( $ft['type'] ?: 'application/octet-stream' ) );
		header( 'Content-Disposition: inline; filename="piece-identite"' );
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
						}
						if ( $act === 'amb_kyc_suppr' ) $list[ $k ]['kyc_file'] = '';
						if ( $act === 'amb_suppr' )      unset( $list[ $k ] );
					}
				}
				update_option( 'ag_ambassadeurs', array_values( $list ) );
			}

			if ( in_array( $act, array( 'v_valider', 'v_payer', 'v_suppr' ), true ) ) {
				$ventes = get_option( 'ag_ambassadeur_ventes', array() );
				foreach ( $ventes as $k => $v ) {
					if ( $v['id'] === $id ) {
						if ( $act === 'v_valider' ) $ventes[ $k ]['statut'] = 'validee';
						if ( $act === 'v_payer' )  { $ventes[ $k ]['statut'] = 'payee'; $ventes[ $k ]['date_paiement'] = current_time( 'd/m/Y' ); }
						if ( $act === 'v_suppr' )  unset( $ventes[ $k ] );
					}
				}
				update_option( 'ag_ambassadeur_ventes', array_values( $ventes ) );
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
		echo '<h2 style="margin-top:30px;">🤝 Ambassadeurs inscrits</h2>';
		if ( empty( $ambs ) ) {
			echo '<p>Aucun inscrit.</p>';
		} else {
			echo '<p style="color:#646970;">« Activer » = identité vérifiée + contrat OK → l\'ambassadeur peut alors déclarer des ventes.</p>';
			echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Tél / Ville</th><th>Identité</th><th>Contrat signé</th><th>Paiement</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
			foreach ( $ambs as $a ) {
				$contrat = $a['contrat'] ?? array();
				echo '<tr>';
				echo '<td>' . esc_html( $a['date'] ?? '' ) . '</td>';
				echo '<td><strong>' . esc_html( $a['name'] ?? '' ) . '</strong>' . ( ! empty( $a['parrain'] ) ? '<br><small style="color:#646970;">parrain : ' . esc_html( $a['parrain'] ) . '</small>' : '' ) . '</td>';
				echo '<td><a href="mailto:' . esc_attr( $a['email'] ?? '' ) . '">' . esc_html( $a['email'] ?? '' ) . '</a></td>';
				echo '<td>' . esc_html( ( $a['phone'] ?? '' ) . ' · ' . ( $a['city'] ?? '' ) ) . '</td>';
				echo '<td style="max-width:220px;white-space:normal;font-size:12px;">Né(e) : ' . esc_html( $a['birthdate'] ?? '—' ) . '<br>' . esc_html( $a['address'] ?? '—' );
				if ( ! empty( $a['kyc_file'] ) ) {
					$kyc_view = wp_nonce_url( admin_url( 'admin-post.php?action=ag_kyc_view&id=' . $a['id'] ), 'ag_kyc_view' );
					echo '<br>📄 <a href="' . esc_url( $kyc_view ) . '" target="_blank" rel="noopener">Voir la pièce</a> · <a href="' . esc_url( $nonce_url( 'amb_kyc_suppr', $a['id'] ) ) . '" style="color:#b32d2e;" onclick="return confirm(\'Supprimer la pièce ?\')">suppr.</a>';
				} else {
					echo '<br><span style="color:#b32d2e;">pas de pièce</span>';
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
			if ( ! empty( $a['kyc_file'] ) && ! empty( $a['kyc_ts'] ) && ( time() - (int) $a['kyc_ts'] ) > $limit ) {
				@unlink( ag_kyc_dir() . '/' . basename( $a['kyc_file'] ) );
				$list[ $k ]['kyc_file']   = '';
				$list[ $k ]['kyc_purged'] = current_time( 'd/m/Y' );
				$changed = true;
			}
		}
		if ( $changed ) update_option( 'ag_ambassadeurs', $list );
	}
}
