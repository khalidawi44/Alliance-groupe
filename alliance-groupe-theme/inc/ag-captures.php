<?php
/**
 * LES CAPTURES — ne plus jamais perdre un lead entrant
 * ---------------------------------------------------------------------------
 * Deux fuites vérifiées dans le code du tunnel :
 *
 *   1. Le formulaire de la page Contact poste vers une action
 *      `admin_post_ag_contact_form` qui N'EXISTAIT PAS. Face à une action
 *      admin-post non enregistrée, WordPress rend une page blanche : le
 *      message part dans le vide, aucun e-mail, aucune trace. (Ce fallback ne
 *      sert que si WPForms est absent — mais alors il perd 100 % des leads.)
 *
 *   2. L'anti-doublon du CRM JETTE un lead entrant chaud s'il correspond à une
 *      fiche déjà présente (même téléphone qu'un prospect déjà scrapé). La
 *      fiche existante n'est pas mise à jour, l'e-mail n'est pas capté, et le
 *      contact peut continuer à recevoir des relances FROIDES alors qu'il vient
 *      de demander un devis.
 *
 * Ce module corrige les deux : un handler pour le formulaire de secours, et un
 * `ag_prospect_upsert()` qui ENRICHIT la fiche existante au lieu de jeter
 * l'info. Un lead chaud qui existait déjà en froid passe « interesse » et
 * gagne son e-mail — donc il sort du démarchage froid et entre dans la relance
 * chaude.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_CAPT_VER' ) ) { define( 'AG_CAPT_VER', '1.0.0' ); }

/* ── 1. Upsert : ajouter OU enrichir, jamais jeter ───────────────────── */

if ( ! function_exists( 'ag_prospect_upsert' ) ) {
	/**
	 * Enregistre un lead. S'il existe déjà (anti-doublon), on met la fiche à
	 * jour au lieu de l'abandonner : statut « interesse » quand c'est un lead
	 * chaud, e-mail ajouté s'il manquait, note horodatée. Rien n'est écrasé
	 * qui vaut mieux que la nouvelle donnée.
	 *
	 * @param array $data  au moins name ; email, phone, city, notes optionnels
	 * @param bool  $chaud true = ce lead a montré une intention (devis, contact…)
	 * @return string 'added' | 'updated' | 'ignored'
	 */
	function ag_prospect_upsert( $data, $chaud = true ) {
		$name = trim( (string) ( $data['name'] ?? '' ) );
		if ( '' === $name ) { return 'ignored'; }

		$existe = function_exists( 'ag_prospect_find' )
			? ag_prospect_find( $name, (string) ( $data['city'] ?? '' ), (string) ( $data['phone'] ?? '' ) )
			: null;

		// Nouveau : on ajoute normalement (le froid entrant reste 'nouveau').
		if ( ! $existe ) {
			if ( $chaud ) {
				$data['status']     = 'interesse';
				$data['replied']    = 1;
				$data['date_reply'] = current_time( 'd/m/Y' );
			}
			if ( function_exists( 'ag_prospect_add_record' ) && ag_prospect_add_record( $data ) ) {
				return 'added';
			}
			// add_record a refusé (ignoré, ou doublon apparu entre-temps) → on tente l'enrichissement.
			$existe = function_exists( 'ag_prospect_find' )
				? ag_prospect_find( $name, (string) ( $data['city'] ?? '' ), (string) ( $data['phone'] ?? '' ) )
				: null;
			if ( ! $existe ) { return 'ignored'; }
		}

		// Existe déjà : on ENRICHIT plutôt que de jeter.
		$list = (array) get_option( 'ag_prospects', array() );
		foreach ( $list as $i => $p ) {
			if ( (string) ( $p['id'] ?? '' ) !== (string) ( $existe['id'] ?? '' ) ) { continue; }

			// Un opt-out / refus reste respecté : on n'enrichit pas pour re-solliciter.
			if ( in_array( (string) ( $p['status'] ?? '' ), array( 'refus', 'ne_pas_contacter' ), true ) ) {
				return 'ignored';
			}

			if ( '' === trim( (string) ( $p['email'] ?? '' ) ) && is_email( (string) ( $data['email'] ?? '' ) ) ) {
				$list[ $i ]['email'] = sanitize_email( (string) $data['email'] );
			}
			if ( '' === trim( (string) ( $p['phone'] ?? '' ) ) && '' !== trim( (string) ( $data['phone'] ?? '' ) ) ) {
				$list[ $i ]['phone'] = sanitize_text_field( (string) $data['phone'] );
			}
			if ( $chaud ) {
				// Ne rétrograde jamais un client ; sinon passe en chaud.
				if ( 'client' !== (string) ( $p['status'] ?? '' ) ) {
					$list[ $i ]['status']     = 'interesse';
					$list[ $i ]['replied']    = 1;
					$list[ $i ]['date_reply'] = current_time( 'd/m/Y' );
				}
			}
			$note = trim( (string) ( $data['notes'] ?? '' ) );
			if ( '' !== $note ) {
				$list[ $i ]['notes'] = trim( (string) ( $p['notes'] ?? '' ) . "\n" . current_time( 'd/m/Y' ) . ' — ' . $note );
			}
			update_option( 'ag_prospects', $list, false );
			return 'updated';
		}
		return 'ignored';
	}
}

/* ── 2. Handler du formulaire Contact (le trou de la page blanche) ───── */

if ( ! function_exists( 'ag_contact_form_handler' ) ) {
	function ag_contact_form_handler() {
		if ( ! isset( $_POST['ag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_nonce'] ), 'ag_contact_nonce' ) ) {
			wp_safe_redirect( home_url( '/contact?err=1' ) ); exit;
		}
		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$service = sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

		if ( '' === $name || ! is_email( $email ) ) {
			wp_safe_redirect( home_url( '/contact?err=1' ) ); exit;
		}

		// 1) Le lead entre dans le CRM, chaud (il a écrit de lui-même).
		ag_prospect_upsert( array(
			'name'   => $name,
			'email'  => $email,
			'source' => 'contact',
			'notes'  => 'Formulaire contact' . ( $service ? ' (' . $service . ')' : '' ) . ' : ' . $message,
		), true );

		// 2) La maison est prévenue là où arrivent toutes ses alertes.
		$to   = apply_filters( 'ag_calendar_notify_email', get_option( 'ag_calendar_email', get_option( 'admin_email' ) ) );
		$body = "Nouveau message depuis la page Contact\n\n"
			. "Nom : $name\nEmail : $email\n" . ( $service ? "Service : $service\n" : '' )
			. "\nMessage :\n$message\n";
		wp_mail( $to, '✉️ Contact : ' . $name, $body, array( 'Reply-To: ' . $email ) );
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '✉️ Message de contact', $name . ' — ' . mb_substr( $message, 0, 120 ) );
		}

		wp_safe_redirect( home_url( '/contact?envoye=1' ) );
		exit;
	}
	add_action( 'admin_post_ag_contact_form', 'ag_contact_form_handler' );
	add_action( 'admin_post_nopriv_ag_contact_form', 'ag_contact_form_handler' );
}

/* ── 3. Injection des demandes SUR-MESURE dans le CRM ────────────────── */

/* Le handler sur-mesure (functions.php) écrit dans une option isolée + envoie
   un e-mail, mais n'entre JAMAIS dans le CRM : le plus gros panier (2 500 à
   20 000 €+) vivait hors pipeline, sans relance possible. Cet action, déclenché
   par functions.php après enregistrement, l'y fait entrer. */
add_action( 'ag_sur_mesure_saved', function ( $req ) {
	if ( ! function_exists( 'ag_prospect_upsert' ) ) { return; }
	ag_prospect_upsert( array(
		'name'   => (string) ( $req['name'] ?? '' ),
		'email'  => (string) ( $req['email'] ?? '' ),
		'phone'  => (string) ( $req['phone'] ?? '' ),
		'city'   => '',
		'type'   => (string) ( $req['type'] ?? 'sur-mesure' ),
		'source' => 'sur-mesure',
		'notes'  => 'Devis sur-mesure — budget ' . (string) ( $req['budget'] ?? '?' )
			. ' · ' . (string) ( $req['type'] ?? '' ) . ' · ' . mb_substr( (string) ( $req['description'] ?? '' ), 0, 300 ),
	), true );
}, 10, 1 );
