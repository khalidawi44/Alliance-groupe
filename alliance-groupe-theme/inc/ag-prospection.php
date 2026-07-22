<?php
/**
 * Alliance Groupe — Prospection.
 *  - Capture des prospects entrants depuis le chat du site (AJAX).
 *  - Poste de travail admin "Prospection" : chasse aux entreprises (Google Places,
 *    repère celles SANS site web), suivi, et outils pour prospecter soi-même
 *    (message prêt, appel, email, WhatsApp). 100% piloté par l'humain.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── 0. Pages auto-créées : app mobile + rapport client ───────────── */
add_action( 'after_setup_theme', function () {
	$pages = array(
		'prospection-mobile' => array( 'Prospection', 'templates/page-prospection-mobile.php' ),
		'rapport'            => array( 'Rapport', 'templates/page-rapport.php' ),
		'mon-espace-client'  => array( 'Mon espace', 'templates/page-espace-client-app.php' ),
	);
	if ( 3 === (int) get_option( 'ag_prospection_mobile_page' ) ) { return; }
	foreach ( $pages as $slug => $def ) {
		if ( ! get_page_by_path( $slug ) ) {
			$id = wp_insert_post( array(
				'post_title'   => $def[0],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', $def[1] );
			}
		}
	}
	update_option( 'ag_prospection_mobile_page', 3 );
} );

/* ── 1. Prospects entrants (chat du site) ───────────────────────── */
if ( ! function_exists( 'ag_lead_handler' ) ) {
	function ag_lead_handler() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ag_lead' ) ) wp_send_json_error( 'nonce', 400 );
		if ( ! empty( $_POST['company'] ) ) wp_send_json_success(); // pot de miel
		$lead = array(
			'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'email'    => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'phone'    => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'interest' => sanitize_text_field( wp_unslash( $_POST['interest'] ?? '' ) ),
			'message'  => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
			'date'     => current_time( 'd/m/Y H:i' ),
			'ts'       => time(),
		);
		if ( ! $lead['email'] && ! $lead['phone'] ) wp_send_json_error( 'contact', 400 );
		$leads = (array) get_option( 'ag_leads', array() );
		$leads[] = $lead;
		update_option( 'ag_leads', array_slice( $leads, -1000 ) );
		ag_activity_log( '📩 Nouveau message (chat) : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ) . ( $lead['interest'] ? ' — ' . $lead['interest'] : '' ) );
		$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
		wp_mail( $to, '🎯 Nouveau prospect (chat) : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ), "Intérêt : {$lead['interest']}\nNom : {$lead['name']}\nEmail : {$lead['email']}\nTél : {$lead['phone']}\nMessage : {$lead['message']}\nDate : {$lead['date']}" );
		if ( function_exists( 'ag_calendar_notify' ) ) ag_calendar_notify( '🎯 Prospect à rappeler : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ), "Intérêt : {$lead['interest']}\nEmail : {$lead['email']}\nTél : {$lead['phone']}\n{$lead['message']}" );
		wp_send_json_success();
	}
}
add_action( 'wp_ajax_nopriv_ag_lead', 'ag_lead_handler' );
add_action( 'wp_ajax_ag_lead', 'ag_lead_handler' );

/* ── 2. Réglages : clé Google Places ────────────────────────────── */
add_action( 'admin_init', function () {
	register_setting( 'ag_prospection_cfg', 'ag_places_key', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
} );
if ( ! function_exists( 'ag_places_key' ) ) {
	function ag_places_key() { return trim( (string) get_option( 'ag_places_key', '' ) ); }
}
if ( ! function_exists( 'ag_places_usage' ) ) {
	/** Usage Google Places du mois : nb d'appels + coût estimé (~0,04 €/appel, hors palier gratuit Google). */
	function ag_places_usage() {
		$n = (int) get_option( 'ag_places_calls_' . gmdate( 'Ym' ), 0 );
		return array( 'calls' => $n, 'cost' => round( $n * 0.04, 2 ) );
	}
}
if ( ! function_exists( 'ag_search_history_key' ) ) {
	function ag_search_history_key( $q, $city ) { return strtolower( trim( (string) $q ) . '|' . trim( (string) $city ) ); }
}
if ( ! function_exists( 'ag_search_history_add' ) ) {
	/**
	 * Garde l'historique des recherches faites ET met en cache leurs résultats,
	 * pour pouvoir les REVOIR gratuitement (sans nouvel appel payant à Google).
	 */
	function ag_search_history_add( $q, $city, $count, $results = null ) {
		$city = trim( (string) $city ); $q = trim( (string) $q );
		if ( '' === $city && '' === $q ) return;
		$key  = ag_search_history_key( $q, $city );
		$hist = (array) get_option( 'ag_search_history', array() );
		$entry = array( 'q' => $q, 'city' => $city, 'count' => (int) $count, 'ts' => time() );
		if ( is_array( $results ) ) {
			// On ne garde que l'essentiel (limité à 60) pour ne pas gonfler la base.
			$slim = array();
			foreach ( array_slice( $results, 0, 60 ) as $r ) {
				if ( empty( $r['name'] ) ) continue;
				$slim[] = array(
					'name'       => $r['name'] ?? '',
					'type'       => $r['type'] ?? $q,
					'city'       => $r['city'] ?? $city,
					'address'    => $r['address'] ?? '',
					'phone'      => $r['phone'] ?? '',
					'phone_intl' => $r['phone_intl'] ?? '',
					'website'    => $r['website'] ?? '',
					'maps_uri'   => $r['maps_uri'] ?? '',
					'rating'     => $r['rating'] ?? 0,
					'reviews'    => $r['reviews'] ?? 0,
				);
			}
			$entry['results'] = $slim;
		} elseif ( isset( $hist[ $key ]['results'] ) ) {
			$entry['results'] = $hist[ $key ]['results']; // on conserve l'ancien cache si pas de nouveaux résultats
		}
		$hist[ $key ] = $entry;
		if ( count( $hist ) > 200 ) $hist = array_slice( $hist, -200, null, true );
		update_option( 'ag_search_history', $hist, false ); // autoload=no (cache volumineux)
	}
}
if ( ! function_exists( 'ag_search_history_get' ) ) {
	/** Récupère une recherche en cache (résultats déjà payés) par métier + ville. */
	function ag_search_history_get( $q, $city ) {
		$hist = (array) get_option( 'ag_search_history', array() );
		$key  = ag_search_history_key( $q, $city );
		return $hist[ $key ] ?? null;
	}
}

/* ── Journal d'activité (« quoi de neuf depuis ma dernière visite ») ── */
if ( ! function_exists( 'ag_activity_log' ) ) {
	function ag_activity_log( $text ) {
		$text = trim( wp_strip_all_tags( (string) $text ) );
		if ( '' === $text ) return;
		$log   = (array) get_option( 'ag_activity', array() );
		$log[] = array( 'ts' => time(), 't' => $text );
		if ( count( $log ) > 200 ) $log = array_slice( $log, -200 );
		update_option( 'ag_activity', $log, false );
	}
}
/* ── Relance : un prospect contacté, sans réponse, depuis N jours (def. 7) ── */
if ( ! function_exists( 'ag_prospect_relance_due' ) ) {
	function ag_prospect_relance_due( $p, $days = 7 ) {
		if ( ! empty( $p['replied'] ) ) return false;
		$st = $p['status'] ?? 'nouveau';
		if ( in_array( $st, array( 'nouveau', 'interesse', 'client', 'refus', 'ne_pas_contacter', 'ignore' ), true ) ) return false;
		$ts = (int) ( $p['last_contact_ts'] ?? 0 );
		if ( ! $ts ) {
			foreach ( array( 'last_contact', 'date_contact' ) as $f ) {
				if ( ! empty( $p[ $f ] ) ) { $dt = DateTime::createFromFormat( 'd/m/Y', $p[ $f ], wp_timezone() ); if ( $dt ) { $ts = $dt->getTimestamp(); break; } }
			}
		}
		if ( ! $ts ) return false;
		return ( time() - $ts ) >= $days * DAY_IN_SECONDS;
	}
}

if ( ! function_exists( 'ag_wa_number' ) ) {
	/**
	 * Normalise un numéro pour WhatsApp (wa.me) : chiffres uniquement, au
	 * format international SANS « + » ni « 0 » initial. wa.me refuse les
	 * numéros nationaux (06…) → il ouvre l'accueil au lieu de la conversation.
	 * On privilégie un numéro déjà international ; sinon on suppose la France.
	 */
	function ag_wa_number( $raw, $intl = '' ) {
		$src = ( '' !== trim( (string) $intl ) ) ? $intl : $raw;
		$d   = preg_replace( '/[^0-9]/', '', (string) $src );
		if ( '' === $d ) return '';
		if ( 0 === strpos( $d, '00' ) ) $d = substr( $d, 2 );   // 0033… -> 33…
		if ( '0' === substr( $d, 0, 1 ) ) $d = '33' . ltrim( $d, '0' ); // national FR 06… -> 336…
		return $d;
	}
}

/* ── Lien Google (fiche + avis) et diagnostic « marche bien / a besoin de clients » ── */
if ( ! function_exists( 'ag_google_link' ) ) {
	function ag_google_link( $p ) {
		if ( ! empty( $p['maps_uri'] ) ) return $p['maps_uri'];
		$q = trim( ( $p['name'] ?? '' ) . ' ' . ( $p['address'] ?? ( $p['city'] ?? '' ) ) );
		return $q ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $q ) : '';
	}
}
if ( ! function_exists( 'ag_prospect_diagnostic' ) ) {
	/** Lecture rapide : l'entreprise marche-t-elle bien ou a-t-elle besoin de clients ? */
	function ag_prospect_diagnostic( $p ) {
		$r = (float) ( $p['rating'] ?? 0 );
		$n = (int) ( $p['reviews'] ?? 0 );
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		$bits = array();
		if ( 'real' !== $kind ) $bits[] = '❗ pas de vrai site';
		if ( 0 === $n )       $bits[] = '🆕 aucun avis (peu visible)';
		elseif ( $n < 10 )    $bits[] = '🔎 peu d\'avis (' . $n . ')';
		elseif ( $n < 50 )    $bits[] = '📈 ' . $n . ' avis';
		else                  $bits[] = '⭐ ' . $n . ' avis (très visible)';
		if ( $r > 0 ) {
			if ( $r < 3.5 )       $bits[] = '⚠ note ' . number_format( $r, 1, ',', '' ) . ' (réputation à redresser)';
			elseif ( $r >= 4.5 )  $bits[] = '👍 note ' . number_format( $r, 1, ',', '' );
		}
		if ( 'real' !== $kind || $n < 10 ) $concl = '🎯 a besoin de clients';
		elseif ( $r >= 4.5 && $n >= 50 )   $concl = '💪 marche bien (vise refonte/SEO)';
		else                               $concl = '🟡 potentiel à creuser';
		return implode( ' · ', $bits ) . ' — ' . $concl;
	}
}


if ( ! function_exists( 'ag_places_search' ) ) {
	function ag_places_search( $query ) {
		$key = ag_places_key();
		if ( '' === $key || '' === trim( $query ) ) return null;
		// Compteur d'appels (transparence du coût Google : ~0,04 € / appel).
		$ck = 'ag_places_calls_' . gmdate( 'Ym' );
		update_option( $ck, (int) get_option( $ck, 0 ) + 1, false );
		$resp = wp_remote_post( 'https://places.googleapis.com/v1/places:searchText', array(
			'timeout' => 20,
			'headers' => array(
				'Content-Type'     => 'application/json',
				'X-Goog-Api-Key'   => $key,
				'X-Goog-FieldMask' => 'places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.id,places.rating,places.userRatingCount,places.businessStatus,places.googleMapsUri',
			),
			'body'    => wp_json_encode( array( 'textQuery' => $query, 'languageCode' => 'fr', 'maxResultCount' => 20 ) ),
		) );
		if ( is_wp_error( $resp ) ) return array( 'error' => $resp->get_error_message() );
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code ) return array( 'error' => ( $data['error']['message'] ?? ( 'Erreur ' . $code ) ) );
		$out = array();
		foreach ( (array) ( $data['places'] ?? array() ) as $p ) {
			if ( isset( $p['businessStatus'] ) && 'OPERATIONAL' !== $p['businessStatus'] ) continue; // on ignore les fermés
			$out[] = array(
				'name'       => $p['displayName']['text'] ?? '',
				'address'    => $p['formattedAddress'] ?? '',
				'phone'      => $p['nationalPhoneNumber'] ?? '',
				'phone_intl' => $p['internationalPhoneNumber'] ?? '',
				'website'    => $p['websiteUri'] ?? '',
				'maps_uri'   => $p['googleMapsUri'] ?? '',
				'rating'  => $p['rating'] ?? 0,
				'reviews' => $p['userRatingCount'] ?? 0,
			);
		}
		return $out;
	}
}

/* ── 4. Enregistrement / suivi des prospects ────────────────────── */
add_action( 'admin_post_ag_prospect_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$ok = ag_prospect_add_record( array(
		'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'type'    => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
		'city'    => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'website' => esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) ),
		'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
	) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-prospects', 'saved' => $ok ? 1 : 0, 'dup' => $ok ? 0 : 1 ), admin_url( 'admin.php' ) ) ); exit;
} );
add_action( 'admin_post_ag_prospect_update', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$del = ! empty( $_POST['delete'] );
	$has_status = array_key_exists( 'status', $_POST );
	$has_owner  = array_key_exists( 'owner', $_POST );
	$st = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
	$owner = sanitize_email( wp_unslash( $_POST['owner'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	$pname = '';
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			$pname = $p['name'] ?? '';
			if ( $del ) { unset( $list[ $k ] ); break; }
			if ( $has_status ) $list[ $k ]['status'] = $st;
			// « Ne plus contacter » = opt-out global (SMS + robot) → apparait dans la liste opt-out.
			if ( $has_status && 'ne_pas_contacter' === $st && function_exists( 'ag_sms_add_optout' ) ) {
				$pph = $list[ $k ]['phone_intl'] ?? ''; if ( '' === $pph ) $pph = $list[ $k ]['phone'] ?? '';
				if ( '' !== $pph ) ag_sms_add_optout( $pph );
			}
			if ( $has_owner ) {
				$list[ $k ]['owner_email'] = $owner;
				$rec = ( $owner && function_exists( 'ag_ambassadeur_record' ) ) ? ag_ambassadeur_record( $owner ) : null;
				$list[ $k ]['owner_name'] = $rec['name'] ?? '';
			}
			break;
		}
	}
	update_option( 'ag_prospects', array_values( $list ) );
	if ( $has_status && in_array( $st, array( 'interesse', 'client' ), true ) && function_exists( 'ag_push' ) ) {
		ag_push( ( 'client' === $st ? '✅ Nouveau client : ' : '🔥 Prospect intéressé : ' ) . $pname, 'Mis à jour dans le tableau de prospection.' );
	}
	if ( $has_status ) {
		if ( function_exists( 'ag_activity_log' ) ) {
			$lab = ag_prospect_statuses()[ $st ] ?? $st;
			ag_activity_log( $lab . ' : ' . $pname );
		}
		do_action( 'ag_prospect_status_changed', $id, $st );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-prospects' ), admin_url( 'admin.php' ) ) ); exit;
} );

/* ── 4b. Type de présence en ligne (réseau social = pas un vrai site) ─ */
if ( ! function_exists( 'ag_site_kind' ) ) {
	function ag_site_kind( $url ) {
		$u = strtolower( trim( (string) $url ) );
		if ( '' === $u ) return array( 'none', '❗ Pas de site' );
		$social = array( 'facebook.com', 'fb.com', 'fb.me', 'instagram.com', 'tiktok.com', 'twitter.com', 'x.com', 'linktr.ee', 'snapchat.com', 'business.site', 'linkedin.com', 'youtube.com', 'wa.me', 'pages.', 'google.com/maps' );
		foreach ( $social as $s ) { if ( false !== strpos( $u, $s ) ) return array( 'social', '⚠ Réseau social' ); }
		// Plateformes de réservation / annuaires = PAS un vrai site possédé → prioritaire.
		$platforms = array( 'planity.', 'doctolib.', 'treatwell.', 'resalib.', 'fresha.', 'booksy.', 'kiute.', 'leciseau.', 'balinea.', 'pagesjaunes.', 'yelp.', 'tripadvisor.', 'lafourchette.', 'thefork.', 'ubereats.', 'deliveroo.', 'just-eat.', 'justeat.' );
		foreach ( $platforms as $pf ) { if ( false !== strpos( $u, $pf ) ) return array( 'social', '⚠ Plateforme (pas de vrai site)' ); }
		return array( 'real', 'site ✓' );
	}
}

/* ── 4d. CRM partagé : statuts, anti-doublon, ajout centralisé ──── */
if ( ! function_exists( 'ag_prospect_statuses' ) ) {
	function ag_prospect_statuses() {
		return array(
			'nouveau'          => '🆕 Nouveau',
			'contacte'         => '📞 Contacté',
			'relance'          => '🔁 Relancé',
			'sans_reponse'     => '🔇 Sans réponse',
			'repondeur'        => '📵 Répondeur (à rappeler)',
			'interesse'        => '🔥 Intéressé',
			'client'           => '✅ Client',
			'refus'            => '✋ Refusé',
			'ne_pas_contacter' => '🚫 Ne plus contacter',
			'ignore'           => '🙈 Ignoré',
		);
	}
}
if ( ! function_exists( 'ag_prospect_blocked' ) ) {
	/** Statuts qui sortent le prospect du circuit (personne ne le recontacte). */
	function ag_prospect_blocked( $status ) { return in_array( $status, array( 'refus', 'ne_pas_contacter', 'client', 'ignore' ), true ); }
}
if ( ! function_exists( 'ag_phone_is_mobile' ) ) {
	/** Vrai si le numéro est un MOBILE français (06/07 / +336 / +337) — seuls les mobiles reçoivent des SMS. */
	function ag_phone_is_mobile( $phone, $intl = '' ) {
		$d = preg_replace( '/[^0-9]/', '', (string) ( $intl ?: $phone ) );
		if ( '' === $d ) return false;
		if ( '0033' === substr( $d, 0, 4 ) ) $d = '0' . substr( $d, 4 );
		elseif ( '33' === substr( $d, 0, 2 ) && 11 === strlen( $d ) ) $d = '0' . substr( $d, 2 );
		$p2 = substr( $d, 0, 2 );
		return ( '06' === $p2 || '07' === $p2 );
	}
}
if ( ! function_exists( 'ag_prospect_mark_optout_by_phone' ) ) {
	/** Passe en « ne plus contacter » le(s) prospect(s) dont le numéro correspond (STOP reçu). */
	function ag_prospect_mark_optout_by_phone( $phone ) {
		$k = preg_replace( '/[^0-9]/', '', (string) $phone );
		if ( strlen( $k ) < 6 ) return 0;
		$list = (array) get_option( 'ag_prospects', array() ); $n = 0;
		foreach ( $list as $i => $p ) {
			foreach ( array( 'phone', 'phone_intl' ) as $f ) {
				$pk = preg_replace( '/[^0-9]/', '', (string) ( $p[ $f ] ?? '' ) );
				if ( '' === $pk ) continue;
				if ( $pk === $k || substr( $pk, -9 ) === substr( $k, -9 ) ) { // tolère +33 vs 0
					if ( ( $list[ $i ]['status'] ?? '' ) !== 'ne_pas_contacter' ) { $list[ $i ]['status'] = 'ne_pas_contacter'; $n++; }
					break;
				}
			}
		}
		if ( $n ) update_option( 'ag_prospects', $list );
		return $n;
	}
}
if ( ! function_exists( 'ag_prospect_set_status_by_phone' ) ) {
	/** Met a jour le statut + note d'un prospect trouve par telephone (robot vocal, etc.).
	 *  N'ecrase jamais 'client' ; n'ecrase 'refus/ne_pas_contacter/ignore' QUE pour un opt-out.
	 *  Retourne le nom du prospect (ou '' si aucun match). Match sur les 9 derniers chiffres. */
	function ag_prospect_set_status_by_phone( $phone, $status, $note = '', $force = false ) {
		$k = preg_replace( '/[^0-9]/', '', (string) $phone );
		if ( strlen( $k ) < 6 ) return '';
		$list = (array) get_option( 'ag_prospects', array() ); $nm = '';
		foreach ( $list as $i => $p ) {
			foreach ( array( 'phone', 'phone_intl' ) as $ff ) {
				$pk = preg_replace( '/[^0-9]/', '', (string) ( $p[ $ff ] ?? '' ) );
				if ( '' === $pk ) continue;
				if ( $pk === $k || substr( $pk, -9 ) === substr( $k, -9 ) ) {
					$cur = $p['status'] ?? '';
					$is_final = in_array( $cur, array( 'client', 'refus', 'ne_pas_contacter', 'ignore' ), true );
					if ( $force || 'client' !== $cur && ! $is_final ) { $list[ $i ]['status'] = $status; }
					if ( $note ) $list[ $i ]['notes'] = trim( $note . "\n" . (string) ( $p['notes'] ?? '' ) );
					$list[ $i ]['last'] = current_time( 'd/m/Y H:i' );
					$nm = $p['name'] ?? (string) $phone;
					break 2;
				}
			}
		}
		if ( $nm ) update_option( 'ag_prospects', $list );
		return $nm;
	}
}
if ( ! function_exists( 'ag_prospect_register_reply' ) ) {
	/**
	 * Une réponse ENTRANTE (SMS ou rappel) d'un prospect connu : met à jour le CRM.
	 *  - intention positive (oui / intéressé / infos / prix / rappel…) → statut « intéressé »
	 *  - sinon (autre réponse, ou rappel) → au moins « contacté » (a répondu)
	 * Journalise le texte dans les notes + alerte Fabrice (SMS/Telegram) et l'ambassadeur.
	 * Ne touche pas un statut final (client/refus/ne_pas_contacter/ignore).
	 * Retourne true si le numéro correspondait bien à un prospect (⇒ ce n'est PAS une candidature).
	 */
	function ag_prospect_register_reply( $phone, $text, $type = 'sms' ) {
		$k = preg_replace( '/[^0-9]/', '', (string) $phone );
		if ( strlen( $k ) < 6 ) return false;
		$list = (array) get_option( 'ag_prospects', array() ); $hit = -1;
		foreach ( $list as $i => $p ) {
			foreach ( array( 'phone', 'phone_intl' ) as $f ) {
				$pk = preg_replace( '/[^0-9]/', '', (string) ( $p[ $f ] ?? '' ) );
				if ( '' !== $pk && ( $pk === $k || substr( $pk, -9 ) === substr( $k, -9 ) ) ) { $hit = $i; break 2; }
			}
		}
		if ( $hit < 0 ) return false;
		$p        = $list[ $hit ];
		$positive = ( 'call' === $type ) || (bool) preg_match( '/(oui|interes|intéress|d.accord|\bok\b|\binfo|rappel|rappelez|quand|combien|prix|tarif|devis|\brdv\b|rendez)/iu', (string) $text );
		$blocked  = in_array( $p['status'] ?? '', array( 'client', 'refus', 'ne_pas_contacter', 'ignore' ), true );
		if ( ! $blocked ) {
			if ( $positive ) { $list[ $hit ]['status'] = 'interesse'; }
			elseif ( in_array( $p['status'] ?? '', array( '', 'nouveau', 'sans_reponse' ), true ) ) { $list[ $hit ]['status'] = 'contacte'; }
		}
		$stamp = current_time( 'd/m/Y H:i' );
		$rtxt  = ( 'call' === $type ) ? '(rappel téléphonique)' : trim( wp_strip_all_tags( (string) $text ) );
		$list[ $hit ]['notes'] = trim( '📩 ' . $stamp . ' — Réponse : ' . $rtxt . "\n" . (string) ( $p['notes'] ?? '' ) );
		$list[ $hit ]['last']  = $stamp;
		update_option( 'ag_prospects', $list );

		$nm  = $p['name'] ?? (string) $phone;
		$tag = $positive ? '🔥 Prospect INTÉRESSÉ (réponse SMS)' : '📩 Réponse d\'un prospect';
		$body = $nm . ' — ' . $phone . ( '' !== $rtxt ? ' : ' . mb_substr( $rtxt, 0, 140 ) : '' );
		if ( function_exists( 'ag_sms' ) ) ag_sms( $tag . ' : ' . $body );
		if ( function_exists( 'ag_push' ) ) ag_push( $tag, $nm . "\n📞 " . $phone . "\n💬 " . $rtxt );
		if ( $positive && function_exists( 'ag_calendar_notify' ) ) ag_calendar_notify( '🔥 Rappeler : ' . $nm, "Prospect intéressé (réponse SMS).\n📞 " . $phone . "\n💬 " . $rtxt . "\nÀ rappeler vite." );
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( $tag . ' : ' . $nm );
		// Notifie aussi l'ambassadeur propriétaire du prospect (si assigné).
		$oe = strtolower( (string) ( $p['owner_email'] ?? '' ) );
		if ( $oe && is_email( $oe ) ) {
			wp_mail( $oe, ( $positive ? '🔥 Un prospect intéressé te répond' : '📩 Un prospect te répond' ),
				"Bonne nouvelle !\n\n" . $nm . " (" . $phone . ") vient de répondre :\n« " . $rtxt . " »\n\nContacte-le vite : " . admin_url( 'admin.php?page=ag-prospects' ) );
		}
		return true;
	}
}
if ( ! function_exists( 'ag_prospect_sig' ) ) {
	function ag_prospect_sig( $name, $city ) { return strtolower( trim( preg_replace( '/\s+/', ' ', (string) $name ) ) ) . '|' . strtolower( trim( (string) $city ) ); }
}
if ( ! function_exists( 'ag_prospect_is_ignored' ) ) {
	/** Liste "🚫 ignorés" (signatures) : pour ne plus les réafficher en recherche. */
	function ag_prospect_is_ignored( $name, $city ) {
		$sig = ag_prospect_sig( $name, $city );
		return in_array( $sig, (array) get_option( 'ag_prospect_ignored', array() ), true );
	}
}
if ( ! function_exists( 'ag_prospect_find' ) ) {
	/** Cherche un prospect existant (anti-doublon) par nom+ville ou téléphone. */
	function ag_prospect_find( $name, $city, $phone = '' ) {
		$sig    = ag_prospect_sig( $name, $city );
		$digits = preg_replace( '/[^0-9]/', '', (string) $phone );
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( ag_prospect_sig( $p['name'] ?? '', $p['city'] ?? '' ) === $sig ) return $p;
			if ( $digits && strlen( $digits ) >= 6 && preg_replace( '/[^0-9]/', '', $p['phone'] ?? '' ) === $digits ) return $p;
		}
		return null;
	}
}
if ( ! function_exists( 'ag_prospect_add_record' ) ) {
	/** Ajoute un prospect SANS doublon. Retourne true si ajouté, false si déjà présent. */
	function ag_prospect_add_record( $data ) {
		if ( empty( $data['name'] ) ) return false;
		if ( ag_prospect_is_ignored( $data['name'], $data['city'] ?? '' ) ) return false; // 🚫 ignoré : ne pas réintroduire
		if ( ag_prospect_find( $data['name'], $data['city'] ?? '', $data['phone'] ?? '' ) ) return false;
		$rec = array_merge( array(
			'id' => uniqid( 'p_' ), 'name' => '', 'type' => '', 'city' => '', 'phone' => '', 'phone_intl' => '', 'email' => '',
			'website' => '', 'address' => '', 'maps_uri' => '', 'rating' => 0, 'reviews' => 0, 'status' => 'nouveau',
			'date_contact' => '', 'last_contact' => '', 'contact_count' => 0, 'replied' => 0, 'date_reply' => '',
			'owner_email' => '', 'owner_name' => '', 'notes' => '', 'bodacc' => '', 'last_relance' => '', 'source' => 'manuel', 'ts' => time(),
		), $data );
		// Auto-assignation à l'ambassadeur propriétaire de la zone (département).
		if ( '' === $rec['owner_email'] && function_exists( 'ag_zone_next_owner' ) ) {
			$dept = function_exists( 'ag_prospect_dept' ) ? ag_prospect_dept( $rec ) : '';
			$zo   = $dept ? ag_zone_next_owner( $dept ) : '';
			if ( $zo ) {
				$rec['owner_email'] = $zo;
				$zrec = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $zo ) : null;
				$rec['owner_name'] = $zrec['name'] ?? '';
			}
		}
		$list   = (array) get_option( 'ag_prospects', array() );
		$list[] = $rec;
		update_option( 'ag_prospects', array_slice( $list, -5000 ) );
		do_action( 'ag_prospect_added', $rec );
		return true;
	}
}

/* ── 4c. Ajout d'un prospect en AJAX (depuis les résultats, sans recharger) ─ */
add_action( 'wp_ajax_ag_prospect_add', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$city    = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	$website = esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) );
	$source  = sanitize_text_field( wp_unslash( $_POST['source'] ?? 'recherche' ) );
	$ok = ag_prospect_add_record( array(
		'name'    => $name,
		'type'    => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
		'city'    => $city,
		'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'website' => $website,
		'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
		'maps_uri' => esc_url_raw( wp_unslash( $_POST['maps'] ?? '' ) ),
		'rating'  => (float) ( $_POST['rating'] ?? 0 ),
		'reviews' => (int) ( $_POST['reviews'] ?? 0 ),
		'notes'   => sanitize_text_field( wp_unslash( $_POST['notes'] ?? '' ) ),
		'source'  => $source,
		'bodacc'  => sanitize_text_field( wp_unslash( $_POST['bodacc'] ?? '' ) ),
	) );
	// Lien Prospection ↔ Sécurité : on tente de trouver le site (si manquant) et
	// de lancer un audit passif tout de suite, pour que la fiche arrive complète.
	$audit_score = null;
	if ( $ok ) { $audit_score = ag_prospect_autolink_audit( $name, $city, $website, $source ); }
	wp_send_json_success( array( 'added' => $ok, 'audit' => $audit_score ) );
} );

/* Enregistre un CONTACT (clic WhatsApp/Email/Tél) : date + compteur + statut auto. */
add_action( 'wp_ajax_ag_prospect_touch', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$ch   = sanitize_text_field( wp_unslash( $_POST['channel'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			$now = current_time( 'd/m/Y' );
			$cnt = (int) ( $p['contact_count'] ?? 0 ) + 1;
			$list[ $k ]['contact_count'] = $cnt;
			$list[ $k ]['last_contact']  = $now;
			$list[ $k ]['last_contact_ts'] = time();
			if ( $ch ) $list[ $k ]['last_channel'] = $ch;
			if ( empty( $p['date_contact'] ) ) $list[ $k ]['date_contact'] = $now;
			$cur = $p['status'] ?? 'nouveau';
			if ( 'nouveau' === $cur ) $list[ $k ]['status'] = 'contacte';
			elseif ( in_array( $cur, array( 'contacte', 'sans_reponse' ), true ) ) $list[ $k ]['status'] = 'relance';
			update_option( 'ag_prospects', array_values( $list ) );
			wp_send_json_success( array( 'count' => $cnt, 'date' => $now, 'status' => $list[ $k ]['status'], 'channel' => $ch ) );
		}
	}
	wp_send_json_error();
} );

/* Marque qu'un prospect A RÉPONDU (ou annule). */
add_action( 'wp_ajax_ag_prospect_reply', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$val  = ! empty( $_POST['replied'] );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			$list[ $k ]['replied']    = $val ? 1 : 0;
			$list[ $k ]['date_reply'] = $val ? current_time( 'd/m/Y' ) : '';
			update_option( 'ag_prospects', array_values( $list ) );
			if ( $val ) ag_activity_log( '💬 ' . ( $p['name'] ?? 'Un prospect' ) . ' a répondu' );
			wp_send_json_success( array( 'replied' => $val ? 1 : 0, 'date' => $list[ $k ]['date_reply'] ) );
		}
	}
	wp_send_json_error();
} );

/* Classe le RÉSULTAT d'un contact en 1 clic : bloqué / sans réponse / intéressé. */
add_action( 'wp_ajax_ag_prospect_outcome', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$out = sanitize_text_field( wp_unslash( $_POST['outcome'] ?? '' ) );
	$map = array( 'bloque' => 'ne_pas_contacter', 'sans_reponse' => 'sans_reponse', 'interesse' => 'interesse' );
	if ( ! isset( $map[ $out ] ) ) wp_send_json_error();
	$st   = $map[ $out ];
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			$list[ $k ]['status'] = $st;
			if ( 'interesse' === $st ) { $list[ $k ]['replied'] = 1; if ( empty( $list[ $k ]['date_reply'] ) ) $list[ $k ]['date_reply'] = current_time( 'd/m/Y' ); }
			update_option( 'ag_prospects', array_values( $list ) );
			$nm = $p['name'] ?? 'Un prospect';
			ag_activity_log( ( 'ne_pas_contacter' === $st ? '⛔ ' . $nm . ' a bloqué / refusé' : ( 'sans_reponse' === $st ? '🔇 ' . $nm . ' : sans réponse' : '🔥 ' . $nm . ' intéressé' ) ) );
			wp_send_json_success( array( 'status' => $st ) );
		}
	}
	wp_send_json_error();
} );
/* RELANCE en 1 clic : marque le prospect comme « relancé » aujourd'hui
   (date + compteur de contacts), pour le suivi et les rappels 7j+. */
add_action( 'wp_ajax_ag_prospect_relance', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	$today = current_time( 'd/m/Y' );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			// Un prospect bloqué/refusé/client ne se relance pas.
			if ( ag_prospect_blocked( $p['status'] ?? '' ) ) wp_send_json_error( array( 'msg' => 'bloqué' ) );
			$list[ $k ]['status']        = 'relance';
			$list[ $k ]['last_relance']  = $today;
			$list[ $k ]['last_contact']  = $today;
			$list[ $k ]['last_channel']  = 'Relance';
			$list[ $k ]['contact_count'] = (int) ( $p['contact_count'] ?? 0 ) + 1;
			if ( empty( $list[ $k ]['date_contact'] ) ) $list[ $k ]['date_contact'] = $today;
			update_option( 'ag_prospects', array_values( $list ) );
			if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🔁 ' . ( $p['name'] ?? 'Un prospect' ) . ' relancé le ' . $today );
			wp_send_json_success( array( 'date' => $today, 'count' => $list[ $k ]['contact_count'] ) );
		}
	}
	wp_send_json_error();
} );
/* SUPPRESSION EN LOT : efface plusieurs prospects sélectionnés (cases à cocher). */
add_action( 'wp_ajax_ag_prospect_delete_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$ids = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $ids ) ) );
	if ( empty( $ids ) ) wp_send_json_error( array( 'msg' => 'aucune sélection' ) );
	$list   = (array) get_option( 'ag_prospects', array() );
	$before = count( $list );
	$list   = array_values( array_filter( $list, function ( $p ) use ( $ids ) {
		return ! in_array( $p['id'] ?? '', $ids, true );
	} ) );
	$removed = $before - count( $list );
	update_option( 'ag_prospects', $list );
	if ( $removed && function_exists( 'ag_activity_log' ) ) ag_activity_log( '🗑️ ' . $removed . ' prospect(s) supprimé(s) en lot' );
	wp_send_json_success( array( 'removed' => $removed ) );
} );
/* Envoi SMS en masse aux prospects sélectionnés (via passerelle SMS). */
add_action( 'wp_ajax_ag_prospect_sms_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	if ( ! function_exists( 'ag_sms_send_bulk' ) ) wp_send_json_error( array( 'msg' => 'passerelle absente' ) );
	$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$ids = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $ids ) ) );
	if ( empty( $ids ) ) wp_send_json_error();
	$pairs = array();
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
		if ( ! in_array( $p['id'] ?? '', $ids, true ) ) continue;
		if ( ag_prospect_blocked( $p['status'] ?? '' ) ) continue; // jamais aux bloqués/refusés
		$to = $p['phone_intl'] ?? '';
		if ( '' === $to ) $to = $p['phone'] ?? '';
		if ( '' === $to ) continue;
		$pairs[] = array( 'to' => $to, 'msg' => ag_prospect_message( $p ) );
	}
	list( $ok, $ko ) = ag_sms_send_bulk( $pairs );
	if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '📲 SMS groupé prospects : ' . $ok . ' envoyé(s), ' . $ko . ' échec(s)' );
	wp_send_json_success( array( 'ok' => $ok, 'ko' => $ko ) );
} );
/** Signature stable d'un lead (pas d'id natif) : pour la suppression en lot. */
if ( ! function_exists( 'ag_lead_sig' ) ) {
	function ag_lead_sig( $l ) {
		return sha1( ( $l['date'] ?? '' ) . '|' . ( $l['name'] ?? '' ) . '|' . ( $l['email'] ?? '' ) . '|' . ( $l['phone'] ?? '' ) . '|' . ( $l['message'] ?? '' ) );
	}
}
/* Suppression en masse des LEADS (prospects entrants du chat). */
add_action( 'wp_ajax_ag_leads_delete_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$sigs = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$sigs = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $sigs ) ) );
	if ( empty( $sigs ) ) wp_send_json_error();
	$list   = (array) get_option( 'ag_leads', array() );
	$before = count( $list );
	$list   = array_values( array_filter( $list, function ( $l ) use ( $sigs ) {
		return ! in_array( ag_lead_sig( $l ), $sigs, true );
	} ) );
	update_option( 'ag_leads', $list );
	wp_send_json_success( array( 'removed' => $before - count( $list ) ) );
} );
/* Suppression en masse de l'HISTORIQUE des recherches (clés q|city). */
add_action( 'wp_ajax_ag_search_history_delete_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$keys = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$keys = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $keys ) ) );
	if ( empty( $keys ) ) wp_send_json_error();
	$hist   = (array) get_option( 'ag_search_history', array() );
	$before = count( $hist );
	foreach ( $keys as $k ) { unset( $hist[ $k ] ); }
	update_option( 'ag_search_history', $hist, false );
	wp_send_json_success( array( 'removed' => $before - count( $hist ) ) );
} );
add_action( 'wp_ajax_ag_prospect_note', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$note = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			$list[ $k ]['notes'] = $note;
			$list[ $k ]['msg_custom'] = ''; // régénération = on repart du modèle (oublie l'ancienne version éditée)
			update_option( 'ag_prospects', array_values( $list ) );
			$msg = function_exists( 'ag_prospect_message' ) ? ag_prospect_message( $list[ $k ] ) : '';
			wp_send_json_success( array( 'message' => $msg ) ); // message régénéré d'après la note
		}
	}
	wp_send_json_error();
} );

/* Sauvegarde du message ÉDITÉ à la main (prend le dessus sur le modèle auto). */
add_action( 'wp_ajax_ag_prospect_msg_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$msg = sanitize_textarea_field( wp_unslash( $_POST['msg'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) { $list[ $k ]['msg_custom'] = $msg; update_option( 'ag_prospects', array_values( $list ) ); wp_send_json_success(); }
	}
	wp_send_json_error();
} );

/* 🙈 Ignorer une entreprise : on l'enregistre avec le statut "ignore"
   (consultable + triable dans Mes prospects) et exclue des futures recherches. */
add_action( 'wp_ajax_ag_prospect_ignore', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$city = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	if ( '' === $name ) wp_send_json_error();
	$ex = ag_prospect_find( $name, $city, $_POST['phone'] ?? '' );
	if ( $ex ) {
		// déjà en liste : on bascule juste son statut en "ignore".
		$list = (array) get_option( 'ag_prospects', array() );
		foreach ( $list as $k => $p ) { if ( ( $p['id'] ?? '' ) === ( $ex['id'] ?? '' ) ) { $list[ $k ]['status'] = 'ignore'; break; } }
		update_option( 'ag_prospects', array_values( $list ) );
	} else {
		ag_prospect_add_record( array(
			'name' => $name, 'type' => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ), 'city' => $city,
			'phone' => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ), 'website' => esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) ),
			'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ), 'rating' => (float) ( $_POST['rating'] ?? 0 ), 'reviews' => (int) ( $_POST['reviews'] ?? 0 ),
			'status' => 'ignore', 'source' => 'ignore',
		) );
	}
	wp_send_json_success();
} );

/* Réinitialiser la liste des ignorés. */
add_action( 'admin_post_ag_prospect_ignore_reset', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	delete_option( 'ag_prospect_ignored' );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );

/* Effacer l'historique des recherches. */
add_action( 'admin_post_ag_search_history_clear', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	delete_option( 'ag_search_history' );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );

/* Supprimer UNE recherche de l'historique (identifiée par sa clé q|city). */
add_action( 'admin_post_ag_search_history_delete', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$key  = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
	if ( '' === $key ) wp_die( 'no key' );
	$hist = (array) get_option( 'ag_search_history', array() );
	$hist = array_values( array_filter( $hist, function ( $h ) use ( $key ) {
		return ag_search_history_key( $h['q'] ?? '', $h['city'] ?? '' ) !== $key;
	} ) );
	update_option( 'ag_search_history', $hist, false );
	$back = isset( $_POST['_back'] ) ? esc_url_raw( wp_unslash( $_POST['_back'] ) ) : admin_url( 'admin.php?page=ag-prospects' );
	wp_safe_redirect( $back ); exit;
} );

/* ── 5. Priorité (qui en a vraiment besoin), pourquoi, et message émotionnel ─ */
if ( ! function_exists( 'ag_prospect_score' ) ) {
	/** Score 0-100 de PROBABILITÉ D'ACHAT : un commerce actif & populaire SANS vrai site = acheteur idéal. */
	function ag_prospect_score( $p ) {
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		// Le manque (pas de vrai site) = le plus gros levier d'achat → priorité forte.
		$s = ( 'none' === $kind ) ? 62 : ( ( 'social' === $kind ) ? 55 : 6 );
		// Activité / demande réelle : beaucoup d'avis = clients + budget = plus susceptible d'acheter.
		$rev = (int) ( $p['reviews'] ?? 0 );
		if ( $rev >= 300 )      $s += 25;
		elseif ( $rev >= 100 )  $s += 20;
		elseif ( $rev >= 30 )   $s += 13;
		elseif ( $rev >= 8 )    $s += 7;
		// Note correcte = établissement sérieux qui investit dans son image.
		$rating = (float) ( $p['rating'] ?? 0 );
		if ( $rating >= 4.2 )      $s += 8;
		elseif ( $rating >= 3.5 )  $s += 4;
		// Joignable tout de suite.
		if ( ! empty( $p['phone'] ) ) $s += 6;
		// Léger bonus secteurs où le web convertit fort (mais TOUS secteurs sont gardés).
		$type = strtolower( $p['type'] ?? '' );
		$hot  = array( 'restaurant', 'coiffeur', 'barbier', 'institut', 'beauté', 'plombier', 'électricien', 'garagiste', 'artisan', 'boulangerie', 'bar', 'coach', 'photographe', 'fleuriste', 'immobil', 'dentiste', 'opticien' );
		foreach ( $hot as $h ) { if ( false !== strpos( $type, $h ) ) { $s += 6; break; } }
		return max( 0, min( 100, $s ) );
	}
}
if ( ! function_exists( 'ag_prospect_categories' ) ) {
	/** Secteurs balayés pour « toutes les entreprises » d'une ville. */
	function ag_prospect_categories() {
		return apply_filters( 'ag_prospect_categories', array(
			'restaurant', 'pizzeria', 'bar', 'boulangerie', 'traiteur', 'coiffeur', 'barbier', 'institut de beauté', 'onglerie', 'spa',
			'plombier', 'électricien', 'garagiste', 'menuisier', 'maçon', 'peintre', 'serrurier', 'chauffagiste', 'artisan',
			'fleuriste', 'opticien', 'dentiste', 'kiné', 'ostéopathe', 'coach sportif', 'salle de sport', 'photographe',
			'agence immobilière', 'auto-école', 'pressing', 'toiletteur', 'avocat', 'notaire', 'comptable', 'assurance',
			'agence de voyage', 'vétérinaire', 'pharmacie', 'tatoueur', 'food truck',
		) );
	}
}
if ( ! function_exists( 'ag_places_sweep' ) ) {
	/** Balaye tous les secteurs d'une ville, fusionne (anti-doublon) et trie par probabilité d'achat. */
	function ag_places_sweep( $city ) {
		$city = trim( (string) $city );
		if ( '' === $city || '' === ag_places_key() ) return array();
		$seen = array(); $out = array();
		foreach ( ag_prospect_categories() as $cat ) {
			$res = ag_places_search( $cat . ' ' . $city );
			if ( ! is_array( $res ) || isset( $res['error'] ) ) continue;
			foreach ( $res as $r ) {
				if ( empty( $r['name'] ) ) continue;
				$sig = ag_prospect_sig( $r['name'], $city );
				if ( isset( $seen[ $sig ] ) ) continue;
				$seen[ $sig ] = 1;
				$r['type'] = $cat; $r['city'] = $city;
				$out[] = $r;
			}
		}
		usort( $out, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
		return $out;
	}
}

/* ── Entreprises au tribunal (procédures collectives) via BODACC open data ──
   Gratuit, sans clé. On vise le REDRESSEMENT / la SAUVEGARDE (l'entreprise
   se bat pour rebondir → elle a besoin de regagner des clients vite) et on
   EXCLUT la liquidation (l'entreprise ferme : inutile de la prospecter). */
if ( ! function_exists( 'ag_bodacc_sector_keywords' ) ) {
	/** Mots-clés d'activité par secteur (post-filtre des résultats BODACC). */
	function ag_bodacc_sector_keywords( $sector ) {
		$map = array(
			'juridique'    => array( 'avocat', 'juridique', 'notaire', 'huissier', 'commissaire de justice', 'juriste', 'selarl', 'scp', 'selas', 'conseil juridique', 'droit' ),
			'restauration' => array( 'restaurant', 'brasserie', 'pizzeria', 'pizza', 'traiteur', 'bar', 'café', 'cafe', 'restauration', 'crêperie', 'creperie', 'snack', 'kebab', 'food', 'glacier', 'salon de thé' ),
			'batiment'     => array( 'maçon', 'macon', 'plomb', 'électric', 'electric', 'menuis', 'peinture', 'bâtiment', 'batiment', 'couvreur', 'couverture', 'charpent', 'artisan', 'rénovation', 'renovation', 'carrelage', 'isolation', 'serrur', 'chauffag', 'terrassement', 'btp' ),
			'beaute'       => array( 'coiffure', 'coiffeur', 'esthétique', 'esthetique', 'beauté', 'beaute', 'salon', 'spa', 'institut', 'barber', 'ongler', 'manucure', 'tatouage' ),
			'sante'        => array( 'kiné', 'kine', 'ostéo', 'osteo', 'dentaire', 'dentiste', 'pharmacie', 'opticien', 'infirmier', 'podologue', 'orthop', 'médical', 'medical', 'paramédical' ),
			'commerce'     => array( 'boutique', 'magasin', 'commerce', 'prêt-à-porter', 'pret-a-porter', 'vente', 'épicerie', 'epicerie', 'fleuriste', 'bijouterie', 'librairie', 'boulang', 'patiss', 'boucher', 'primeur', 'mode' ),
			'auto'         => array( 'garage', 'automobile', 'carrosserie', 'mécanique', 'mecanique', 'pneu', 'auto-école', 'auto ecole', 'auto-ecole', 'moto', 'véhicule', 'vehicule' ),
			'immobilier'   => array( 'immobili', 'agence immobilière', 'agence immobiliere', 'syndic', 'transaction', 'gestion locative' ),
			'tourisme'     => array( 'hôtel', 'hotel', 'camping', 'gîte', 'gite', 'tourisme', 'chambre d', 'hébergement', 'hebergement', 'location saisonnière' ),
		);
		return $map[ $sector ] ?? array();
	}
}
if ( ! function_exists( 'ag_bodacc_sector_labels' ) ) {
	/** Libellés affichés dans le menu déroulant secteur. */
	function ag_bodacc_sector_labels() {
		return array(
			''             => 'Tous secteurs',
			'juridique'    => '⚖️ Avocats / juridique',
			'restauration' => '🍽️ Restauration',
			'batiment'     => '🔨 Bâtiment / artisans',
			'beaute'       => '💈 Beauté / coiffure',
			'sante'        => '🩺 Santé / paramédical',
			'commerce'     => '🛍️ Commerces',
			'auto'         => '🚗 Auto / garages',
			'immobilier'   => '🏠 Immobilier',
			'tourisme'     => '🏨 Hôtel / tourisme',
		);
	}
}
if ( ! function_exists( 'ag_bodacc_search' ) ) {
	function ag_bodacc_search( $city, $sector = '' ) {
		$city = trim( (string) $city );
		if ( '' === $city ) return array();
		$where = rawurlencode( 'search(jugement, "redressement") and search("' . $city . '")' );
		$url   = 'https://bodacc-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/annonces-commerciales/records?where=' . $where . '&order_by=dateparution%20desc&limit=60';
		$resp  = wp_remote_get( $url, array( 'timeout' => 25, 'headers' => array( 'Accept' => 'application/json' ) ) );
		if ( is_wp_error( $resp ) ) return array( 'error' => $resp->get_error_message() );
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code ) return array( 'error' => ( $data['message'] ?? ( 'Erreur BODACC ' . $code ) ) );
		$out = array(); $seen = array();
		foreach ( (array) ( $data['results'] ?? array() ) as $rec ) {
			$jug = $rec['jugement'] ?? '';
			if ( is_string( $jug ) && '' !== $jug ) { $jd = json_decode( $jug, true ); if ( is_array( $jd ) ) $jug = $jd; }
			$nature = is_array( $jug ) ? trim( ( $jug['nature'] ?? '' ) . ' ' . ( $jug['famille'] ?? '' ) ) : (string) $jug;
			$compl  = is_array( $jug ) ? ( $jug['complementJugement'] ?? '' ) : '';
			$low    = mb_strtolower( $nature . ' ' . $compl );
			if ( false !== strpos( $low, 'liquidation' ) ) continue; // l'entreprise ferme → on ignore
			if ( false === strpos( $low, 'redressement' ) && false === strpos( $low, 'sauvegarde' ) ) continue;
			$lp = $rec['listepersonnes'] ?? '';
			if ( is_string( $lp ) && '' !== $lp ) { $pd = json_decode( $lp, true ); if ( is_array( $pd ) ) $lp = $pd; }
			$pers = array();
			if ( is_array( $lp ) ) { $pers = $lp['personne'] ?? $lp; if ( isset( $pers['denomination'] ) || isset( $pers['nom'] ) ) $pers = array( $pers ); }
			$name = ''; $activite = ''; $siren = '';
			if ( ! empty( $pers[0] ) && is_array( $pers[0] ) ) {
				$f0 = $pers[0];
				$name = $f0['denomination'] ?? trim( ( $f0['nom'] ?? '' ) . ' ' . ( $f0['prenom'] ?? '' ) );
				$activite = is_array( $f0['activite'] ?? '' ) ? '' : ( $f0['activite'] ?? '' );
				$siren = is_array( $f0['numeroImmatriculation'] ?? '' ) ? ( $f0['numeroImmatriculation']['numeroIdentificationRCS'] ?? '' ) : ( $f0['numeroImmatriculation'] ?? '' );
			}
			if ( '' === $name ) $name = is_array( $rec['commercant'] ?? '' ) ? '' : ( $rec['commercant'] ?? '' );
			if ( '' === $name ) continue;
			$ville = is_array( $rec['ville'] ?? '' ) ? '' : ( $rec['ville'] ?? '' );
			$sig = mb_strtolower( $name . '|' . $ville );
			if ( isset( $seen[ $sig ] ) ) continue; $seen[ $sig ] = 1;
			$out[] = array(
				'name' => $name, 'city' => $ville ?: $city, 'activite' => $activite, 'siren' => $siren,
				'nature' => $nature ?: 'Procédure collective', 'date' => $rec['dateparution'] ?? '', 'tribunal' => is_array( $rec['tribunal'] ?? '' ) ? '' : ( $rec['tribunal'] ?? '' ),
				'complement' => is_string( $compl ) ? $compl : '',
			);
		}
		// Filtre SECTEUR (ex. juridique/avocats) : on garde les fiches dont le nom
		// OU l'activité contient un mot-clé du secteur. Évite de remonter tout le tribunal.
		$kw = ag_bodacc_sector_keywords( $sector );
		if ( ! empty( $kw ) ) {
			$out = array_values( array_filter( $out, function ( $c ) use ( $kw ) {
				$h = mb_strtolower( ( $c['name'] ?? '' ) . ' ' . ( $c['activite'] ?? '' ) );
				foreach ( $kw as $k ) { if ( false !== mb_strpos( $h, $k ) ) return true; }
				return false;
			} ) );
		}
		return $out;
	}
}
if ( ! function_exists( 'ag_bodacc_message' ) ) {
	/** Message pour une entreprise en redressement : on n'essaie PAS de vendre,
	    on apporte son expertise (pro-bono / partenariat) pour l'aider à rebondir,
	    en coordination avec l'administrateur / mandataire judiciaire. */
	function ag_bodacc_message( $c ) {
		$name = $c['name'] ?? 'votre entreprise';
		$act  = trim( (string) ( $c['activite'] ?? '' ) );
		$acc  = $act ? "votre activité ($act)" : 'votre activité';
		return "Bonjour,\n\nJe sais que $name traverse une période difficile (redressement judiciaire). Je ne viens pas vous vendre quelque chose : je viens proposer mon aide.\n\nJe dirige Alliance Groupe, une agence web & IA. Je peux apporter gratuitement mon expertise pour vous aider à rebondir avec $acc : un site professionnel, de la visibilité sur Google et des outils qui ramènent des clients — sans coût pour votre trésorerie.\n\nPour les projets que je crois prometteurs, je m'engage dans la durée, en accompagnement (pas en simple prestation). Tout peut se mettre en place proprement, en coordination avec votre administrateur / mandataire judiciaire, dans le cadre du plan de redressement.\n\nSi vous êtes ouvert·e, on en parle 10 minutes, sans aucun engagement.\n\nBien à vous,\nAlliance Groupe — advise.alliance.group@gmail.com\n(Si vous préférez ne pas être recontacté·e, dites-le-moi, j'en prends note immédiatement.)";
	}
}

/* ── AGENT D'ANALYSE « redressement judiciaire » ─────────────────────
   Lit les prospects déjà enregistrés (option ag_prospects), repère ceux
   en procédure collective (source 'tribunal' / champ bodacc / mention
   redressement|sauvegarde dans les notes) et produit, pour chacun, une
   analyse : type de procédure, POURQUOI il est probablement en difficulté,
   QUOI FAIRE (approche solidaire + déontologie pour les avocats), priorité. */
if ( ! function_exists( 'ag_redr_is_target' ) ) {
	function ag_redr_is_target( $p ) {
		$src  = strtolower( (string) ( $p['source'] ?? '' ) );
		$txt  = strtolower( ( $p['bodacc'] ?? '' ) . ' ' . ( $p['notes'] ?? '' ) );
		if ( 'tribunal' === $src ) return true;
		if ( ! empty( $p['bodacc'] ) ) return true;
		return ( false !== strpos( $txt, 'redressement' ) || false !== strpos( $txt, 'sauvegarde' ) || false !== strpos( $txt, 'bodacc' ) );
	}
}
if ( ! function_exists( 'ag_redr_is_avocat' ) ) {
	function ag_redr_is_avocat( $p ) {
		$h = strtolower( ( $p['name'] ?? '' ) . ' ' . ( $p['type'] ?? '' ) . ' ' . ( $p['source'] ?? '' ) );
		foreach ( array( 'avocat', 'avocats', 'cabinet', 'juridique', 'selarl', 'scp ', 'barreau', 'notaire', 'juriste', 'huissier', 'commissaire de justice' ) as $kw ) {
			if ( false !== strpos( $h, $kw ) ) return true;
		}
		return false;
	}
}
if ( ! function_exists( 'ag_redr_analyze' ) ) {
	/** Analyse 1 prospect en difficulté → tableau structuré (français, prêt à lire). */
	function ag_redr_analyze( $p ) {
		$txt   = strtolower( ( $p['bodacc'] ?? '' ) . ' ' . ( $p['notes'] ?? '' ) );
		$is_sauvegarde = ( false !== strpos( $txt, 'sauvegarde' ) );
		$is_redr       = ( false !== strpos( $txt, 'redressement' ) );
		$avocat        = ag_redr_is_avocat( $p );

		// 1) Procédure détectée.
		if ( $is_sauvegarde ) {
			$proc = 'Sauvegarde';
			$proc_def = 'Procédure préventive : l’entreprise n’est PAS encore en cessation des paiements mais anticipe des difficultés. C’est un dirigeant lucide qui agit tôt → cible la PLUS saine du lot.';
		} elseif ( $is_redr ) {
			$proc = 'Redressement judiciaire';
			$proc_def = 'L’entreprise est en cessation des paiements (elle ne peut plus régler ses dettes exigibles avec sa trésorerie disponible) MAIS le tribunal la juge redressable → période d’observation, puis plan de continuation possible.';
		} else {
			$proc = 'Procédure collective';
			$proc_def = 'Procédure collective en cours (détail dans la fiche BODACC). On vise le rebond, pas la liquidation.';
		}

		// 2) Pourquoi (causes probables) — adaptées au métier.
		if ( $avocat ) {
			$why = array(
				'Baisse du nombre de dossiers / de la clientèle (départ d’un associé emportant sa clientèle, concurrence locale, moins de visibilité en ligne).',
				'Charges fixes lourdes : loyer du cabinet, collaborateurs, secrétariat, assurances RCP, cotisations ordinales, outils (RPVA, doc juridique).',
				'Trésorerie tendue : honoraires payés tard, dossiers à l’aide juridictionnelle peu/mal rémunérés, provisions non réglées.',
				'Peu de génération de nouveaux clients : pas de site conforme/visible, absence de SEO local, déontologie mal maîtrisée (peur de “faire de la pub”).',
			);
		} else {
			$why = array(
				'Trésorerie insuffisante : impayés clients, délais de paiement, BFR mal financé.',
				'Charges fixes trop lourdes vs chiffre d’affaires (loyers, salaires, emprunts).',
				'Perte de marché / de clients : baisse d’activité, concurrence, manque de visibilité.',
				'Coup dur ponctuel (gros impayé, litige, conjoncture) sur une structure déjà fragile.',
			);
		}

		// 3) Quoi faire — approche solidaire + déontologie si avocat.
		$todo = array(
			'Approche SOLIDAIRE, pas de vente frontale : on propose de l’aide pour regagner des clients (site pro, visibilité Google, outils qui ramènent des demandes) — sans coût immédiat sur la trésorerie.',
			'Coordonner avec l’administrateur / mandataire judiciaire : toute prestation s’inscrit dans le plan, proprement.',
			'Proposer un montage compatible trésorerie : étalement, ou partenariat/accompagnement sur les projets prometteurs.',
		);
		if ( $avocat ) {
			$todo[] = 'DÉONTOLOGIE avocat : contact par EMAIL ou COURRIER uniquement (jamais appel/SMS à froid), ton confraternel ; site SANS témoignages ni widget d’avis Google ; insister sur conformité RGPD + secret professionnel.';
			$angle  = 'Angle : « regagner des clients en restant 100 % conforme au RIN ». L’audit sécurité/RGPD gratuit reste la porte d’entrée légitime.';
		} else {
			$angle  = 'Angle : « ramener des clients vite et sans alourdir la trésorerie » — visibilité + site qui convertit.';
		}

		// 4) Priorité (score 0-100).
		$score = 40;
		if ( $is_sauvegarde ) $score += 30;           // agit tôt = plus sain
		elseif ( $is_redr )   $score += 20;           // redressable
		if ( ! empty( $p['email'] ) ) $score += 12;   // joignable proprement (email)
		if ( ! empty( $p['phone'] ) ) $score += 4;
		$site_kind = function_exists( 'ag_site_kind' ) ? ag_site_kind( $p['website'] ?? '' ) : array( 'real' );
		if ( 'real' !== ( $site_kind[0] ?? 'real' ) ) $score += 14; // pas de vrai site = gros levier
		if ( $avocat ) $score += 6;                   // niche prioritaire
		$score = max( 0, min( 100, $score ) );

		return array(
			'proc' => $proc, 'proc_def' => $proc_def, 'avocat' => $avocat,
			'why' => $why, 'todo' => $todo, 'angle' => $angle, 'score' => $score,
		);
	}
}
/* ── LIENS ENTRE OUTILS : Prospection ↔ Analyse redressement ↔ Espace Audit ── */
if ( ! function_exists( 'ag_prospect_host' ) ) {
	function ag_prospect_host( $p ) {
		$w = $p['website'] ?? '';
		if ( '' === $w ) return '';
		$h = wp_parse_url( $w, PHP_URL_HOST );
		return strtolower( preg_replace( '/^www\./', '', (string) $h ) );
	}
}
if ( ! function_exists( 'ag_audit_find_by_host' ) ) {
	/** Cherche dans l'historique des audits une fiche pour ce host (lien Sécurité). */
	function ag_audit_find_by_host( $host ) {
		if ( '' === $host || ! function_exists( 'ag_audit_hist_get' ) ) return null;
		$host = strtolower( preg_replace( '/^www\./', '', (string) $host ) );
		foreach ( ag_audit_hist_get() as $id => $e ) {
			$h = strtolower( preg_replace( '/^www\./', '', (string) ( $e['host'] ?? '' ) ) );
			if ( $h && $h === $host ) { $e['id'] = $id; return $e; }
		}
		return null;
	}
}
if ( ! function_exists( 'ag_prospect_enrich' ) ) {
	/** Complète un prospect existant (site/tel) trouvés après coup — par nom+ville. */
	function ag_prospect_enrich( $name, $city, $website = '', $phone = '', $phone_intl = '' ) {
		$list = (array) get_option( 'ag_prospects', array() );
		$sig  = ag_prospect_sig( $name, $city );
		$ch   = false;
		foreach ( $list as $k => $p ) {
			if ( ag_prospect_sig( $p['name'] ?? '', $p['city'] ?? '' ) === $sig ) {
				if ( $website && empty( $p['website'] ) )       { $list[ $k ]['website'] = $website; $ch = true; }
				if ( $phone && empty( $p['phone'] ) )           { $list[ $k ]['phone'] = $phone; $ch = true; }
				if ( $phone_intl && empty( $p['phone_intl'] ) ) { $list[ $k ]['phone_intl'] = $phone_intl; $ch = true; }
				break;
			}
		}
		if ( $ch ) update_option( 'ag_prospects', array_values( $list ) );
	}
}
if ( ! function_exists( 'ag_prospect_autolink_audit' ) ) {
	/**
	 * Après un « + Suivre » : si le prospect n'a pas de site (cas tribunal/BODACC),
	 * on le cherche via Google Places (1 appel), on enrichit la fiche, puis on
	 * lance un AUDIT PASSIF (non-intrusif, gratuit) qui se relie tout seul à la
	 * fiche par le nom de domaine. Retourne le score d'audit ou null.
	 */
	function ag_prospect_autolink_audit( $name, $city, $website = '', $source = '' ) {
		// Pas de site fourni → on tente de le trouver (uniquement si clé Places).
		if ( '' === $website && '' !== trim( (string) $name ) && function_exists( 'ag_places_search' ) && function_exists( 'ag_places_key' ) && '' !== ag_places_key() ) {
			$r = ag_places_search( trim( $name . ' ' . $city ) );
			if ( is_array( $r ) && empty( $r['error'] ) && ! empty( $r[0] ) && is_array( $r[0] ) ) {
				$first   = $r[0];
				$website = $first['website'] ?? '';
				ag_prospect_enrich( $name, $city, $website, $first['phone'] ?? '', $first['phone_intl'] ?? '' );
			}
		}
		if ( '' === $website ) return null;
		// Réseau social ≠ vrai site → pas d'audit.
		if ( function_exists( 'ag_site_kind' ) ) { $k = ag_site_kind( $website ); if ( 'real' !== ( $k[0] ?? 'real' ) ) return null; }
		if ( ! function_exists( 'ag_audit_run' ) || ! function_exists( 'ag_audit_hist_upsert' ) ) return null;
		$audit = ag_audit_run( $website );
		if ( ! is_array( $audit ) ) return null;
		$ct = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $website ) : array();
		// push_crm=false : le prospect est déjà au CRM, on évite tout doublon.
		// Origine : si c'est le CRON (agent auto) qui scanne, on marque 'auto' ; sinon 'self' (moi, manuel).
		$ag_src_scan = ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) ? 'auto' : 'self';
		ag_audit_hist_upsert( $audit, $ct, 'passive', false, $ag_src_scan );
		return (int) ( $audit['score'] ?? 0 );
	}
}
if ( ! function_exists( 'ag_prospect_links_block' ) ) {
	/** Boutons/volets reliant un prospect à son analyse redressement + son audit sécurité. */
	function ag_prospect_links_block( $p ) {
		$out  = '';
		$host = ag_prospect_host( $p );
		// 1) Analyse redressement (si concerné) — résumé + lien vers la page dédiée.
		if ( function_exists( 'ag_redr_is_target' ) && ag_redr_is_target( $p ) ) {
			$a = ag_redr_analyze( $p );
			$redr_url = admin_url( 'admin.php?page=ag-redr-analyse' );
			$out .= '<details style="display:block;margin-top:6px;"><summary class="button button-small" style="border-color:#7a4ed4;color:#5a2ca0;">🔎 Analyse redressement (' . (int) $a['score'] . ')</summary>';
			$out .= '<div style="font-size:.85em;color:#444;margin-top:6px;max-width:520px;"><strong>' . esc_html( $a['proc'] ) . '.</strong> ' . esc_html( $a['proc_def'] );
			$out .= '<br><em>Quoi faire :</em> ' . esc_html( $a['todo'][0] );
			$out .= '<br><strong style="color:#1d4f8b;">' . esc_html( $a['angle'] ) . '</strong>';
			$out .= '<br><a href="' . esc_url( $redr_url ) . '">→ Ouvrir l’analyse complète</a></div></details>';
		}
		// 2) Sécurité (Espace Audit) : audit existant (score) ou lancer un audit.
		if ( $host ) {
			$audit_page = admin_url( 'admin.php?page=ag-espace-audit' );
			$au = ag_audit_find_by_host( $host );
			if ( $au ) {
				$sc = (int) ( $au['score'] ?? 0 ); $seg = $au['seg'] ?? '';
				$out .= ' <a class="button button-small" style="border-color:#0e7490;color:#0e7490;" href="' . esc_url( $audit_page . '#agh-list' ) . '" title="Audit de sécurité déjà réalisé pour ce site">🛡️ Audit ' . $sc . '/100' . ( $seg ? ' (' . esc_html( $seg ) . ')' : '' ) . '</a>';
			} else {
				$out .= ' <a class="button button-small" href="' . esc_url( add_query_arg( array( 'page' => 'ag-espace-audit', 'prefill' => $p['website'] ?? '' ), admin_url( 'admin.php' ) ) ) . '" title="Lancer un audit de sécurité de ce site">🛡️ Auditer ce site</a>';
			}
		}
		// 3) Robot vocal : DEUX boutons distincts (création web / sécurité), 1er message différent
		//    selon le choix (variable {{accroche}}). Jamais un avocat (déonto).
		$is_avocat = false !== stripos( ( $p['type'] ?? '' ) . ' ' . ( $p['name'] ?? '' ), 'avocat' );
		if ( ! empty( $p['phone'] ) && ! $is_avocat && function_exists( 'ag_voice_ready' ) && ag_voice_ready() ) {
			$pid = esc_attr( $p['id'] ?? '' );
			$out .= ' <button type="button" class="button button-small agr-robot" data-id="' . $pid . '" data-angle="creation" style="border-color:#7c3aed;color:#7c3aed;background:#f5f0ff;font-weight:600;" title="C\'est le ROBOT Emma qui appelle (pas toi) — angle création de site">🤖 Robot · site 🌐</button>';
			$out .= ' <button type="button" class="button button-small agr-robot" data-id="' . $pid . '" data-angle="securite" style="border-color:#7c3aed;color:#7c3aed;background:#f5f0ff;font-weight:600;" title="C\'est le ROBOT Emma qui appelle (pas toi) — angle audit sécurité">🤖 Robot · sécu 🔒</button>';
		}
		return $out;
	}
}
// Priorité 20 : on s'enregistre APRÈS le menu parent « ag-prospects » (déclaré
// plus bas dans le fichier, priorité 10) — sinon le sous-menu n'a pas encore de
// parent au moment de son ajout et la page renvoie un 404.
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Analyse redressement', '🔎 Analyse redressement', 'manage_options', 'ag-redr-analyse', 'ag_redr_analyse_render' );
}, 20 );
if ( ! function_exists( 'ag_redr_analyse_render' ) ) {
	function ag_redr_analyse_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$all     = (array) get_option( 'ag_prospects', array() );
		$targets = array_values( array_filter( $all, 'ag_redr_is_target' ) );
		// Tri (sélectionnable) : priorité (def.) / avocats d'abord / nom / récent.
		$rsort = isset( $_GET['rsort'] ) ? sanitize_text_field( wp_unslash( $_GET['rsort'] ) ) : 'prio';
		usort( $targets, function ( $a, $b ) use ( $rsort ) {
			if ( 'nom' === $rsort )  return strcasecmp( $a['name'] ?? '', $b['name'] ?? '' );
			if ( 'date' === $rsort ) return ( $b['ts'] ?? 0 ) <=> ( $a['ts'] ?? 0 );
			if ( 'avocat' === $rsort ) {
				$av = (int) ag_redr_is_avocat( $b ) <=> (int) ag_redr_is_avocat( $a );
				if ( 0 !== $av ) return $av;
			}
			return ag_redr_analyze( $b )['score'] <=> ag_redr_analyze( $a )['score'];
		} );
		$nb_av = count( array_filter( $targets, 'ag_redr_is_avocat' ) );
		echo '<div class="wrap">';
		echo '<h1>🔎 Analyse des cibles en redressement</h1>';
		echo '<p style="max-width:840px;color:#50575e;">L’agent lit <strong>tes prospects enregistrés</strong> et isole ceux en <strong>procédure collective</strong> (tribunal / BODACC). Pour chacun : pourquoi il est probablement en difficulté, et quoi faire — dans une logique <strong>solidaire</strong> (on aide à rebondir) et <strong>déontologique</strong> pour les avocats. <em>Données publiques BODACC ; analyse indicative, à confirmer au contact.</em></p>';
		echo '<p style="font-weight:600;">' . count( $targets ) . ' cible(s) en difficulté' . ( $nb_av ? ' · dont <span style="color:#5a2ca0;">' . $nb_av . ' avocat(s)/juridique</span>' : '' ) . '.</p>';
		if ( ! empty( $targets ) ) {
			// Barre d'outils : tri + sélection/suppression en masse.
			$sort_opts = array( 'prio' => '🔥 Priorité', 'avocat' => '⚖️ Avocats d’abord', 'nom' => 'A→Z (nom)', 'date' => '🕒 Plus récents' );
			echo '<form method="get" style="display:inline-block;margin:0 0 6px;"><input type="hidden" name="page" value="ag-redr-analyse"><label style="font-size:.9em;">Trier : <select name="rsort" onchange="this.form.submit()">';
			foreach ( $sort_opts as $sk => $sl ) echo '<option value="' . esc_attr( $sk ) . '" ' . selected( $rsort, $sk, false ) . '>' . esc_html( $sl ) . '</option>';
			echo '</select></label></form>';
			echo '<div class="agr-bulkbar" style="margin:6px 0 12px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">';
			echo '<label style="font-size:.9em;"><input type="checkbox" id="agr-check-all"> Tout sélectionner</label>';
			echo '<button type="button" id="agr-del-selected" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="agr-sel-count">0</span>)</button>';
			echo '</div>';
		}
		if ( empty( $targets ) ) {
			echo '<div class="notice notice-info" style="padding:12px;"><p>Aucune cible en redressement dans tes prospects pour l’instant. Va dans <strong>Prospection → 🏛️ Entreprises au tribunal</strong>, cherche une ville, puis « + Suivre » les cabinets en redressement/sauvegarde : ils s’analyseront ici automatiquement.</p></div></div>';
			return;
		}
		foreach ( $targets as $p ) {
			$a    = ag_redr_analyze( $p );
			$col  = $a['score'] >= 75 ? '#b32d2e' : ( $a['score'] >= 55 ? '#bd7b00' : '#50575e' );
			$badge = $a['avocat'] ? '<span style="background:#f3eefc;color:#5a2ca0;border-radius:10px;padding:1px 9px;font-size:.8em;font-weight:700;">⚖️ Avocat / juridique</span> ' : '';
			echo '<div style="max-width:880px;margin:14px 0;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #7a4ed4;border-radius:8px;padding:16px 18px;">';
			echo '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">';
			echo '<input type="checkbox" class="agr-check" value="' . esc_attr( $p['id'] ?? '' ) . '" title="Sélectionner">';
			echo '<span style="display:inline-block;min-width:40px;text-align:center;font-weight:800;color:#fff;background:' . esc_attr( $col ) . ';border-radius:6px;padding:3px 9px;">' . (int) $a['score'] . '</span>';
			echo '<strong style="font-size:1.08em;">' . esc_html( $p['name'] ?? '' ) . '</strong>';
			echo '<span style="color:#646970;">' . esc_html( trim( ( $p['type'] ?? '' ) . ( ! empty( $p['city'] ) ? ' · ' . $p['city'] : '' ), ' ·' ) ) . '</span>';
			echo $badge;
			echo '<span style="margin-left:auto;background:#efe7fb;color:#5a2ca0;border-radius:6px;padding:2px 10px;font-weight:700;">' . esc_html( $a['proc'] ) . '</span>';
			echo '</div>';
			if ( ! empty( $p['bodacc'] ) ) echo '<p style="margin:8px 0 0;font-size:.85em;color:#5a2ca0;">🏛️ Fiche : ' . esc_html( $p['bodacc'] ) . '</p>';
			echo '<p style="margin:10px 0 4px;"><strong>Ce que ça veut dire :</strong> ' . esc_html( $a['proc_def'] ) . '</p>';
			echo '<p style="margin:8px 0 2px;"><strong>Pourquoi probablement en difficulté :</strong></p><ul style="margin:0 0 4px 18px;list-style:disc;">';
			foreach ( $a['why'] as $w ) echo '<li style="color:#444;">' . esc_html( $w ) . '</li>';
			echo '</ul>';
			echo '<p style="margin:8px 0 2px;"><strong>Quoi faire :</strong></p><ul style="margin:0 0 4px 18px;list-style:disc;">';
			foreach ( $a['todo'] as $t ) echo '<li style="color:#1d4f1d;">' . esc_html( $t ) . '</li>';
			echo '</ul>';
			echo '<p style="margin:8px 0 0;color:#1d4f8b;"><strong>' . esc_html( $a['angle'] ) . '</strong></p>';

			// Liens vers les autres outils (prospection + sécurité) pour CETTE entreprise.
			$prosp_url = add_query_arg( array( 'page' => 'ag-prospects', 'fq' => $p['name'] ?? '' ), admin_url( 'admin.php' ) );
			echo '<p style="margin:8px 0 0;">';
			echo '<a class="button button-small" href="' . esc_url( $prosp_url ) . '" title="Voir cette fiche dans la prospection">👤 Ouvrir dans Prospection</a> ';
			$rhost = ag_prospect_host( $p );
			if ( $rhost ) {
				$rau = ag_audit_find_by_host( $rhost );
				if ( $rau ) echo '<a class="button button-small" style="border-color:#0e7490;color:#0e7490;" href="' . esc_url( admin_url( 'admin.php?page=ag-espace-audit#agh-list' ) ) . '">🛡️ Audit ' . (int) ( $rau['score'] ?? 0 ) . '/100</a>';
				else echo '<a class="button button-small" href="' . esc_url( add_query_arg( array( 'page' => 'ag-espace-audit', 'prefill' => $p['website'] ?? '' ), admin_url( 'admin.php' ) ) ) . '">🛡️ Auditer ce site</a>';
			}
			echo '</p>';

			// ── Suivi du contact (date/canal) + boutons d'action prêts à l'emploi ──
			$pid    = $p['id'] ?? '';
			$msg    = ag_bodacc_message( array( 'name' => $p['name'] ?? '', 'activite' => $p['type'] ?? '' ) );
			$digits = function_exists( 'ag_wa_number' ) ? ag_wa_number( $p['phone'] ?? '', $p['phone_intl'] ?? '' ) : '';
			$smsnum = preg_replace( '/[^0-9+]/', '', $p['phone_intl'] ?? '' ) ?: preg_replace( '/[^0-9+]/', '', $p['phone'] ?? '' );
			$wa     = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
			$sms    = $smsnum ? 'sms:' . $smsnum . '?body=' . rawurlencode( $msg ) : '';
			$mailto = ! empty( $p['email'] ) ? 'mailto:' . rawurlencode( $p['email'] ) . '?subject=' . rawurlencode( 'Un coup de main pour rebondir' ) . '&body=' . rawurlencode( $msg ) : '';

			echo '<div style="margin-top:10px;padding-top:10px;border-top:1px dashed #e0d7f3;">';
			// Suivi.
			if ( ! empty( $p['date_contact'] ) ) {
				echo '<div style="font-size:.82em;color:#50575e;margin-bottom:6px;">📨 Contacté' . ( ! empty( $p['last_channel'] ) ? ' par <strong>' . esc_html( $p['last_channel'] ) . '</strong>' : '' ) . ' le <strong>' . esc_html( $p['date_contact'] ) . '</strong> (×' . (int) ( $p['contact_count'] ?? 1 ) . ')' . ( ! empty( $p['last_relance'] ) ? ' · 🔁 relancé le ' . esc_html( $p['last_relance'] ) : '' ) . '</div>';
			} else {
				echo '<div style="font-size:.82em;color:#b26a00;margin-bottom:6px;">⏳ Pas encore contacté</div>';
			}
			// Boutons. Avocat = email/courrier UNIQUEMENT (déontologie) : pas de SMS/WhatsApp/appel.
			if ( $mailto ) echo '<a class="button button-small agr-touch" data-id="' . esc_attr( $pid ) . '" data-channel="Email" href="' . esc_url( $mailto ) . '">✉️ Email</a> ';
			if ( ! $a['avocat'] ) {
				if ( $wa ) echo '<a class="button button-small agr-touch" data-id="' . esc_attr( $pid ) . '" data-channel="WhatsApp" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">WhatsApp</a> ';
				if ( $sms ) echo '<a class="button button-small agr-touch" data-id="' . esc_attr( $pid ) . '" data-channel="SMS" href="' . esc_attr( $sms ) . '">📱 SMS</a> ';
				if ( ! empty( $p['phone'] ) ) echo '<a class="button button-small agr-touch" data-id="' . esc_attr( $pid ) . '" data-channel="Appel" href="tel:' . esc_attr( $p['phone'] ) . '">📞 ' . esc_html( $p['phone'] ) . '</a> ';
			} elseif ( ! empty( $p['phone'] ) ) {
				echo '<span style="font-size:.82em;color:#b32d2e;">📞 ' . esc_html( $p['phone'] ) . ' — <em>avocat : pas d’appel/SMS à froid, email ou courrier</em></span> ';
			}
			echo '<button type="button" class="button button-small agr-relance" data-id="' . esc_attr( $pid ) . '">🔁 Relancer</button> ';
			// Message éditable prêt.
			echo '<details style="display:inline-block;margin-top:6px;"><summary class="button button-small">✍️ Voir le message</summary><textarea readonly rows="8" style="width:520px;max-width:100%;margin-top:6px;">' . esc_textarea( $msg ) . '</textarea></details>';
			echo '</div>';
			echo '</div>';
		}
		// Nonce + JS de suivi (réutilise les AJAX ag_prospect_touch / ag_prospect_relance).
		$rn = wp_create_nonce( 'ag_prospect' );
		echo '<script>(function(){var nonce=' . wp_json_encode( $rn ) . ';';
		echo 'document.querySelectorAll(".agr-touch").forEach(function(a){a.addEventListener("click",function(){var id=a.getAttribute("data-id");if(!id)return;var fd=new FormData();fd.append("action","ag_prospect_touch");fd.append("_n",nonce);fd.append("id",id);fd.append("channel",a.getAttribute("data-channel")||"");fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin",keepalive:true}).catch(function(){});});});';
		echo 'document.querySelectorAll(".agr-relance").forEach(function(b){b.addEventListener("click",function(){var id=b.getAttribute("data-id");if(!id)return;var fd=new FormData();fd.append("action","ag_prospect_relance");fd.append("_n",nonce);fd.append("id",id);var old=b.textContent;b.disabled=true;b.textContent="…";fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){b.textContent="✓ Relancé le "+j.data.date;b.style.color="#1e7e34";}else{b.textContent=old;b.disabled=false;}}).catch(function(){b.textContent=old;b.disabled=false;});});});';
		// Sélection multiple + suppression en masse (réutilise ag_prospect_delete_bulk).
		echo 'var allc=document.getElementById("agr-check-all"),delb=document.getElementById("agr-del-selected"),cnt=document.getElementById("agr-sel-count");';
		echo 'function aChk(){return Array.prototype.slice.call(document.querySelectorAll(".agr-check"));}';
		echo 'function aRef(){var n=aChk().filter(function(c){return c.checked;}).length;if(cnt)cnt.textContent=n;if(delb)delb.disabled=(n===0);}';
		echo 'if(allc)allc.addEventListener("change",function(){aChk().forEach(function(c){c.checked=allc.checked;});aRef();});';
		echo 'aChk().forEach(function(c){c.addEventListener("change",aRef);});';
		echo 'if(delb)delb.addEventListener("click",function(){var ids=aChk().filter(function(c){return c.checked;}).map(function(c){return c.value;});if(!ids.length)return;if(!confirm("Supprimer définitivement "+ids.length+" prospect(s) ?"))return;var fd=new FormData();fd.append("action","ag_prospect_delete_bulk");fd.append("_n",nonce);ids.forEach(function(id){fd.append("ids[]",id);});delb.disabled=true;delb.textContent="…";fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){location.reload();}else{delb.disabled=false;alert("Suppression impossible.");}}).catch(function(){delb.disabled=false;});});';
		echo '})();</script>';
		echo '</div>';
	}
}
if ( ! function_exists( 'ag_prospect_why' ) ) {
	/** Pourquoi cette entreprise a besoin d'un site + ce que ça lui apporte. */
	function ag_prospect_why( $p ) {
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		if ( 'none' === $kind )   $gap = "n'a aucun site web : elle est quasi invisible sur Google, et perd les clients qui la cherchent en ligne.";
		elseif ( 'social' === $kind ) $gap = "n'a qu'une page de réseau social : pas de vraie vitrine, pas de référencement Google, dépendante d'une plateforme qui peut fermer son compte.";
		else $gap = "a un site, mais il peut être modernisé (mobile, rapidité, réservation/commande) pour convertir plus.";
		$type = strtolower( $p['type'] ?? '' );
		$bene = "être trouvée 24h/24 sur Google, rassurer les clients, et recevoir des demandes en automatique";
		if ( false !== strpos( $type, 'restaurant' ) || false !== strpos( $type, 'bar' ) ) $bene = "afficher son menu, prendre des réservations en ligne et remplir sa salle même la nuit";
		elseif ( false !== strpos( $type, 'coiffeur' ) || false !== strpos( $type, 'barbier' ) || false !== strpos( $type, 'institut' ) || false !== strpos( $type, 'beauté' ) ) $bene = "permettre la prise de rendez-vous en ligne 24h/24 et réduire les trous dans le planning";
		elseif ( false !== strpos( $type, 'plombier' ) || false !== strpos( $type, 'électricien' ) || false !== strpos( $type, 'garagiste' ) || false !== strpos( $type, 'artisan' ) || false !== strpos( $type, 'btp' ) ) $bene = "capter les demandes de devis urgentes et apparaître avant les concurrents sur Google";
		return 'Cette entreprise ' . $gap . ' Un site lui permettrait de ' . $bene . '.';
	}
}
if ( ! function_exists( 'ag_proposal_from_notes' ) ) {
	/** Traduit MES notes (problèmes/raccourcis) en PROPOSITIONS client. Ex : "pas un vrai site" -> "un vrai site qui vous appartient". */
	function ag_proposal_from_notes( $notes ) {
		$n = mb_strtolower( (string) $notes );
		if ( '' === trim( $n ) ) return array();
		$map = array(
			array( array( 'pas un vrai site', 'pas de vrai site', 'faux site', 'pas de site', 'aucun site', 'planity', 'doctolib', 'treatwell', 'reseau', 'réseau', 'facebook', 'insta', 'page externe', 'que les reseaux', 'social' ), 'un *vrai site professionnel qui VOUS appartient* (au lieu d\'une simple page externe)' ),
			array( array( 'reserv', 'réserv', 'rdv', 'rendez-vous', 'rendez vous', 'agenda', 'booking', 'prise de' ), 'une *vraie réservation / prise de RDV en ligne* 24h/24' ),
			array( array( 'menu' ), 'un *menu clair et facile à utiliser*' ),
			array( array( 'lent', 'rapide', 'vitesse', 'charge' ), 'un site *rapide à charger*' ),
			array( array( 'mobile', 'téléphone', 'telephone', 'responsive', 'smartphone' ), 'un affichage *parfait sur mobile*' ),
			array( array( 'remont', 'flèche', 'fleche', 'retour en haut', 'navigation', 'défil', 'defil', 'scroll' ), 'une *navigation fluide* (bouton retour en haut, défilement propre)' ),
			array( array( 'seo', 'google', 'référenc', 'referenc', 'visib', 'introuvable', 'pas trouv' ), 'un site *optimisé pour être trouvé sur Google*' ),
			array( array( 'photo', 'image', 'visuel', 'galerie' ), 'de *beaux visuels* qui valorisent votre travail' ),
			array( array( 'vieux', 'daté', 'date', 'moche', 'vieillot', 'design', 'refonte', 'ancien', 'pas beau' ), 'un *design moderne et premium* à votre image' ),
			array( array( 'avis', 'témoignage', 'temoignage' ), 'vos *avis clients* bien mis en avant' ),
			array( array( 'contact', 'formulaire' ), 'un *formulaire de contact* qui capte les demandes' ),
			array( array( 'paiement', 'boutique', 'ecommerce', 'e-commerce', 'vente', 'panier' ), 'une *boutique en ligne / paiement* intégré' ),
		);
		$out = array();
		foreach ( $map as $row ) {
			foreach ( $row[0] as $kw ) { if ( false !== strpos( $n, $kw ) ) { $out[ $row[1] ] = $row[1]; break; } }
		}
		return array_values( $out );
	}
}
if ( ! function_exists( 'ag_prospect_bucket' ) ) {
	/** Famille de métier (pour adapter le ton : on ne parle pas pareil à un avocat ou un garagiste). */
	function ag_prospect_bucket( $type ) {
		$t = mb_strtolower( (string) $type );
		$has = function ( $arr ) use ( $t ) { foreach ( $arr as $k ) { if ( false !== mb_strpos( $t, $k ) ) return true; } return false; };
		if ( $has( array( 'avocat', 'notaire', 'juriste', 'huissier', 'comptable', 'expert-comptable', 'assurance', 'conseil', 'cabinet' ) ) ) return 'juridique';
		if ( $has( array( 'garagiste', 'mécanic', 'mecanic', 'garage', 'carross', 'pneu', 'automobile', 'auto-' ) ) ) return 'auto';
		if ( $has( array( 'restaurant', 'pizz', 'brasserie', 'traiteur', 'boulang', 'crêperie', 'creperie', 'snack', 'kebab', 'sushi', 'food', 'bar ', 'café', 'cafe' ) ) ) return 'resto';
		if ( $has( array( 'coiffeur', 'barbier', 'institut', 'beaut', 'onglerie', 'spa', 'esthé', 'esthe', 'massage', 'masseu', 'tatou', 'manucure', 'maquill' ) ) ) return 'beaute';
		if ( $has( array( 'plombier', 'électr', 'electr', 'chauffag', 'serrur', 'maçon', 'macon', 'menuis', 'peintre', 'couvreur', 'artisan', 'carrel', 'vitrier', 'jardin', 'paysag', 'btp' ) ) ) return 'artisan';
		if ( $has( array( 'coach', 'sport', 'fitness', 'yoga', 'pilates', 'salle de sport' ) ) ) return 'coach';
		if ( $has( array( 'photograph', 'vidéaste', 'videaste' ) ) ) return 'photo';
		if ( $has( array( 'immobil', 'agence immo' ) ) ) return 'immo';
		if ( $has( array( 'dentiste', 'kiné', 'kine', 'ostéo', 'osteo', 'médecin', 'medecin', 'vétérin', 'veterin', 'opticien', 'pharmac', 'infirmier', 'psycho', 'santé', 'sante', 'podo' ) ) ) return 'sante';
		return 'general';
	}
}
if ( ! function_exists( 'ag_prospect_message' ) ) {
	/** Message personnalisé et humain, adapté au métier et au manque constaté. Éditable (msg_custom). */
	function ag_prospect_message( $p, $link = '' ) {
		if ( ! empty( $p['msg_custom'] ) ) return (string) $p['msg_custom']; // version écrite/éditée par l'admin
		$site = $link ? $link : home_url( '/sites-express' );
		$nom  = $p['name'] ? $p['name'] : 'votre établissement';
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		$type = strtolower( $p['type'] ?? '' );
		$bucket = ag_prospect_bucket( $type );

		// Ouvertures humaines, variées, par métier (none = pas de site, social = réseaux, real = a un site).
		$A = array(
			'juridique' => array(
				'none'   => "en cherchant un avocat dans votre secteur, je suis tombé sur {$nom}… et je n'ai trouvé aucun site. Aujourd'hui, un justiciable choisit souvent son conseil après avoir consulté son site : sans présence en ligne, ce choix se fait chez un confrère.",
				'social' => "je suis tombé sur {$nom} sur les réseaux, mais sans véritable site. Pour une profession où la confiance est tout, une simple page ne suffit pas à rassurer un futur client qui vous découvre.",
				'real'   => "j'ai consulté le site de {$nom}. Il est correct, mais je pense qu'avec quelques ajustements il inspirerait davantage confiance et générerait plus de prises de contact.",
				'prom'   => "Imaginez un site sobre et crédible qui présente clairement vos domaines d'expertise et reçoit les demandes de rendez-vous, pendant que vous êtes en audience.",
			),
			'auto' => array(
				'none'   => "je cherchais un bon garage dans le coin et je suis tombé sur {$nom}… mais impossible de trouver votre site. Le problème : quand une voiture lâche, les gens tapent « garage + ville » sur Google et appellent le premier qui a l'air sérieux.",
				'social' => "j'ai vu {$nom} sur les réseaux, mais pas de vrai site. Or un automobiliste pressé ne fouille pas Facebook : il cherche sur Google un garage avec horaires, avis et numéro à portée de clic.",
				'real'   => "j'ai jeté un œil au site de {$nom}. Avec deux-trois améliorations (avis, demande de devis, mobile), il pourrait vous ramener nettement plus d'appels.",
				'prom'   => "Imaginez recevoir les demandes de devis directement sur votre téléphone, avant que le client n'appelle le garage d'à côté.",
			),
			'resto' => array(
				'none'   => "j'avais envie de réserver et je vous ai cherché en ligne… {$nom} n'a pas de site. Beaucoup de clients abandonnent quand ils ne trouvent ni la carte, ni les horaires, ni un moyen de réserver.",
				'social' => "je vous ai trouvé sur les réseaux, mais sans vrai site. Le souci : un client qui a faim veut voir la carte et réserver en 2 clics, pas scroller un fil d'actu.",
				'real'   => "j'ai regardé le site de {$nom}. Sympa, mais je pense qu'avec une vraie réservation en ligne et une carte plus claire, vous rempliriez davantage la salle.",
				'prom'   => "Imaginez votre salle qui se remplit grâce aux réservations en ligne, même tard le soir, sans toucher votre téléphone.",
			),
			'beaute' => array(
				'none'   => "je vous ai cherché pour prendre rendez-vous et… {$nom} n'a pas de site. C'est dommage : vos clientes veulent réserver en ligne, voir vos prestations et vos réalisations.",
				'social' => "je vous ai vu sur les réseaux, mais sans vrai site. Vos plus belles photos méritent une vraie vitrine — et surtout un agenda de réservation en ligne.",
				'real'   => "j'ai regardé votre site, {$nom}. Avec une vraie prise de RDV en ligne et une belle galerie, il travaillerait beaucoup plus pour vous.",
				'prom'   => "Imaginez un agenda qui se remplit tout seul : vos clientes réservent en ligne 24h/24, même quand le salon est fermé.",
			),
			'artisan' => array(
				'none'   => "j'avais besoin d'un pro et j'ai cherché {$nom} en ligne… aucun site. Quand les gens ont une urgence, ils appellent le premier artisan qu'ils trouvent sur Google avec des avis rassurants.",
				'social' => "je vous ai trouvé sur les réseaux, mais pas de vrai site. Un client qui a une fuite ou une panne ne cherche pas sur Facebook : il veut un site clair avec vos services et votre numéro.",
				'real'   => "j'ai regardé le site de {$nom}. Avec une page « devis » efficace et vos avis en avant, il vous amènerait plus de chantiers.",
				'prom'   => "Imaginez recevoir les demandes de devis directement sur votre téléphone, avant le concurrent.",
			),
			'sante' => array(
				'none'   => "j'ai cherché {$nom} pour prendre rendez-vous et je n'ai trouvé aucun site. Beaucoup de patients choisissent un praticien qui a un site clair (horaires, accès, prise de RDV).",
				'social' => "je vous ai vu en ligne, mais sans vrai site professionnel. Pour rassurer un nouveau patient, une page réseau ne remplace pas un vrai site.",
				'real'   => "j'ai regardé votre site. Avec quelques améliorations (prise de RDV, infos pratiques, mobile), il faciliterait la vie de vos patients et la vôtre.",
				'prom'   => "Imaginez un site qui informe vos patients et gère les rendez-vous, pendant que vous vous occupez d'eux.",
			),
			'general' => array(
				'none'   => "en cherchant un {$type} par chez vous, je suis tombé sur {$nom}… et j'ai été surpris de ne trouver aucun site. Vos futurs clients aussi cherchent en ligne — et passent peut-être à côté de vous.",
				'social' => "je suis tombé sur {$nom} sur les réseaux, mais pas de vrai site. Tout votre travail repose sur une page que vous ne possédez pas vraiment, alors que beaucoup de clients vous cherchent sur Google.",
				'real'   => "j'ai pris le temps de regarder le site de {$nom}, et je pense sincèrement qu'avec quelques améliorations il pourrait vous ramener bien plus de clients.",
				'prom'   => "Imaginez : pendant que vous travaillez (ou que vous dormez), votre site présente votre savoir-faire, rassure les clients et reçoit des demandes tout seul.",
			),
		);
		$set = $A[ $bucket ] ?? $A['general'];
		$k2  = ( 'none' === $kind ) ? 'none' : ( ( 'social' === $kind ) ? 'social' : 'real' );
		$accroche = $set[ $k2 ];
		$promesse = $set['prom'];

		$phone = apply_filters( 'ag_contact_phone', '07 44 82 95 16' );
		$notes = trim( (string) ( $p['notes'] ?? '' ) );
		$barter = false;
		foreach ( array( 'echange', 'échange', 'troc', 'contre des', 'contre du', 'contre vos', 'contre un', 'en echange', 'en échange', 'barter', 'service contre', 'prestation contre' ) as $bkw ) {
			if ( false !== mb_strpos( mb_strtolower( $notes ), $bkw ) ) { $barter = true; break; }
		}

		$msg  = "✨ Bonjour,\n\n{$accroche}\n\n";
		if ( $barter ) {
			$msg .= "💡 Et si on faisait un *échange de services*, sans que vous sortiez d'argent ?\nJe vous crée un *site professionnel complet* et je m'occupe de toute sa *gestion*, et en échange vous me faites profiter de *vos prestations*. Gagnant-gagnant 🤝\n\n";
		}
		$props = ag_proposal_from_notes( $notes );
		if ( ! empty( $props ) ) {
			// Mes notes (problèmes) traduites en propositions concrètes.
			$msg .= "👉 Voici ce que je vous propose, sur-mesure pour vous :\n";
			foreach ( $props as $pr ) $msg .= "✅ " . $pr . "\n";
			$msg .= "\nJe m'occupe de tout, de A à Z.\n\n";
		} elseif ( '' !== $notes ) {
			$msg .= "👀 J'ai repéré quelques points à améliorer chez vous — je m'en occupe de A à Z : refonte, *vraie réservation en ligne*, navigation claire, fiche Google.\n\n";
		} else {
			$msg .= "Chez *Alliance Groupe*, on crée des sites pros 📱 qui travaillent pour vous 24h/24 :\n";
			$msg .= "✅ Refonte complète, navigation claire, *vraie réservation en ligne*\n";
			$msg .= "✅ Optimisé Google + parfait sur mobile\n\n";
			$msg .= "{$promesse}\n\n";
		}
		$msg .= $barter
			? "💳 (Et si vous préférez payer en classique : prix fixe dès *490 €*, en 4× sans frais.)\n\n"
			: "💳 Prix fixe dès *490 €* (payable en 4× sans frais), livré en quelques jours, sans rendez-vous.\n\n";
		$msg .= "👉 Voir ce qu'on fait : {$site}\n";
		if ( ! empty( $props ) || '' !== $notes ) $msg .= "✦ Refonte sur-mesure : " . home_url( '/sur-mesure' ) . "\n";
		$msg .= "\nOn en parle 5 minutes, sans engagement ? 🙂\n📞 {$phone}\n";
		$tg = function_exists( 'ag_tg_cfg' ) ? ag_tg_cfg( 'chan_link' ) : '';
		if ( $tg ) $msg .= "📣 Notre canal clients (offres & nouveautés) : {$tg}\n";
		$msg .= "\n🤝 Alliance Groupe\n📩 contact@alliancegroupe-inc.com";
		if ( ! empty( $p['id'] ) && function_exists( 'ag_prospect_unsub_url' ) ) $msg .= "\n\n🚫 Ne plus être contacté (1 clic) : " . ag_prospect_unsub_url( $p );
		return $msg;
	}
}

/* ── Désinscription en 1 clic : bloque le prospect pour TOUTE l'équipe (admin inclus) ── */
if ( ! function_exists( 'ag_prospect_unsub_token' ) ) {
	function ag_prospect_unsub_token( $id ) { return substr( wp_hash( 'ag_unsub|' . $id ), 0, 20 ); }
}
if ( ! function_exists( 'ag_prospect_unsub_url' ) ) {
	function ag_prospect_unsub_url( $p ) {
		$id = $p['id'] ?? '';
		if ( '' === $id ) return '';
		return add_query_arg( array( 'ag_unsub' => rawurlencode( $id ), 't' => ag_prospect_unsub_token( $id ) ), home_url( '/' ) );
	}
}
add_action( 'template_redirect', function () {
	if ( empty( $_GET['ag_unsub'] ) || empty( $_GET['t'] ) ) return;
	$id = sanitize_text_field( wp_unslash( $_GET['ag_unsub'] ) );
	$t  = sanitize_text_field( wp_unslash( $_GET['t'] ) );
	if ( ! hash_equals( ag_prospect_unsub_token( $id ), $t ) ) wp_die( 'Lien invalide ou expiré.' );
	$list = (array) get_option( 'ag_prospects', array() ); $name = '';
	foreach ( $list as $k => $p ) { if ( ( $p['id'] ?? '' ) === $id ) { $list[ $k ]['status'] = 'ne_pas_contacter'; $name = $p['name'] ?? ''; break; } }
	update_option( 'ag_prospects', array_values( $list ) );
	if ( function_exists( 'ag_push' ) ) ag_push( '🚫 Désinscription', ( $name ?: $id ) . ' a demandé à ne plus être contacté — bloqué pour toute l\'équipe (toi compris).' );
	wp_die( '<div style="font-family:sans-serif;max-width:520px;margin:60px auto;text-align:center;"><h1 style="color:#1e7e34;">C\'est noté ✓</h1><p style="font-size:1.1rem;color:#333;">Vous ne serez plus contacté·e par Alliance Groupe. Toutes nos excuses pour le dérangement.</p></div>', 'Désinscription', array( 'response' => 200 ) );
} );

/* ── 6. Page admin "Prospection" ────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_menu_page( 'Prospection', 'Prospection', 'manage_options', 'ag-prospects', 'ag_prospects_render', 'dashicons-search', 30 );
} );
if ( ! function_exists( 'ag_prospects_render' ) ) {
	function ag_prospects_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$nonce  = wp_create_nonce( 'ag_prospect' );
		$key    = ag_places_key();
		$q      = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$city   = isset( $_GET['city'] ) ? sanitize_text_field( wp_unslash( $_GET['city'] ) ) : '';
		$allsec = ! empty( $_GET['all'] );
		$force  = ! empty( $_GET['refresh'] ); // forcer un NOUVEL appel payant (sinon : cache gratuit)
		$from_cache = false;
		$cached = ( $q || $city ) ? ag_search_history_get( $q, $city ) : null;
		if ( ! $force && $cached && ! empty( $cached['results'] ) && is_array( $cached['results'] ) ) {
			// Réaffichage GRATUIT depuis le cache : 0 appel Google, 0 €.
			$results    = $cached['results'];
			$from_cache = true;
		} elseif ( ( $allsec || '' === trim( $q ) ) && $city ) {
			$results = ag_places_sweep( $city ); // déjà trié par probabilité d'achat
		} else {
			$results = ( $q || $city ) ? ag_places_search( trim( $q . ' ' . $city ) ) : null;
			if ( is_array( $results ) && ! isset( $results['error'] ) ) {
				foreach ( $results as &$_r ) { $_r['type'] = $q; } unset( $_r );
				usort( $results, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
			}
		}
		// Sauvegarde / met en cache uniquement après un VRAI appel (pas en relecture cache).
		if ( ! $from_cache && is_array( $results ) && ! isset( $results['error'] ) ) ag_search_history_add( $q, $city, count( $results ), $results );
		$prospects = (array) get_option( 'ag_prospects', array() );
		$leads     = array_reverse( (array) get_option( 'ag_leads', array() ) );
		$labels    = ag_prospect_statuses();
		$post      = admin_url( 'admin-post.php' );
		$ambs = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) { if ( ! empty( $a['email'] ) ) $ambs[ $a['email'] ] = $a['name'] ?? $a['email']; }
		$f_status = isset( $_GET['fstatus'] ) ? sanitize_text_field( wp_unslash( $_GET['fstatus'] ) ) : '';
		$f_q      = isset( $_GET['fq'] ) ? sanitize_text_field( wp_unslash( $_GET['fq'] ) ) : '';
		$sortby   = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'besoin';
		if ( '' !== $f_status ) $prospects = array_filter( $prospects, function ( $p ) use ( $f_status ) {
			if ( 'relance7' === $f_status ) return ag_prospect_relance_due( $p ); // à relancer (sans réponse 7j+)
			$st = $p['status'] ?? 'nouveau';
			if ( 'contacte' === $f_status ) return in_array( $st, array( 'contacte', 'relance' ), true ); // "Contactés" = contactés + relancés
			if ( 'ne_pas_contacter' === $f_status ) return in_array( $st, array( 'ne_pas_contacter', 'refus' ), true ); // "Bloqués" = refusés + ne plus contacter
			return $st === $f_status;
		} );
		if ( '' !== $f_q ) { $needle = strtolower( $f_q ); $prospects = array_filter( $prospects, function ( $p ) use ( $needle ) { return false !== strpos( strtolower( ( $p['name'] ?? '' ) . ' ' . ( $p['city'] ?? '' ) . ' ' . ( $p['type'] ?? '' ) ), $needle ); } ); }
		$prospects = array_values( $prospects );
		usort( $prospects, function ( $a, $b ) use ( $sortby ) {
			if ( 'nom' === $sortby )  return strcasecmp( $a['name'] ?? '', $b['name'] ?? '' );
			if ( 'date' === $sortby ) return ( $b['ts'] ?? 0 ) <=> ( $a['ts'] ?? 0 );
			return ag_prospect_score( $b ) <=> ag_prospect_score( $a );
		} );
		?>
		<div class="wrap ag-prospect-wrap">
			<h1 style="display:flex;align-items:center;gap:10px;">🎯 Prospection <span style="font-size:.5em;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;padding:3px 10px;border-radius:100px;">Alliance Groupe</span></h1>
			<p style="max-width:820px;color:#50575e;">Trouve des entreprises qui ont besoin d'un site (Google Maps repère celles <strong>sans site web</strong>), ajoute-les à ta liste, puis prospecte-les toi-même avec le message prêt, l'appel, l'email ou WhatsApp. Tu gardes la main sur tout.</p>

			<!-- Quoi de neuf depuis ma dernière visite -->
			<?php
			$ag_uid  = get_current_user_id();
			$ag_seen = (int) get_user_meta( $ag_uid, 'ag_activity_seen', true );
			$ag_acts = array_reverse( (array) get_option( 'ag_activity', array() ) );
			$ag_new  = 0; foreach ( $ag_acts as $a ) { if ( (int) ( $a['ts'] ?? 0 ) > $ag_seen ) $ag_new++; }
			?>
			<div style="max-width:980px;margin:14px 0;border:1px solid #ccd0d4;border-left:4px solid #d63638;border-radius:6px;background:#fff;">
				<details <?php echo $ag_new ? 'open' : ''; ?>>
					<summary style="cursor:pointer;padding:14px 18px;font-weight:700;font-size:1.05rem;">🔔 Quoi de neuf<?php if ( $ag_new ) : ?> <span style="background:#d63638;color:#fff;border-radius:100px;padding:1px 10px;font-size:.8rem;"><?php echo (int) $ag_new; ?> nouveau<?php echo $ag_new > 1 ? 'x' : ''; ?></span><?php else : ?> <span style="color:#646970;font-weight:400;font-size:.85rem;">— rien de neuf depuis ta dernière visite</span><?php endif; ?></summary>
					<div style="padding:0 18px 14px;">
						<?php if ( empty( $ag_acts ) ) : ?>
							<p style="color:#646970;">Aucune activité pour l'instant. Dès qu'un prospect répond, qu'un message arrive ou qu'un ambassadeur s'inscrit, ça apparaît ici.</p>
						<?php else : ?>
							<ul style="margin:0;padding:0;list-style:none;line-height:1.6;max-height:340px;overflow:auto;">
								<?php foreach ( array_slice( $ag_acts, 0, 40 ) as $a ) : $is_new = (int) ( $a['ts'] ?? 0 ) > $ag_seen; ?>
									<li style="padding:6px 8px;border-bottom:1px solid #f0f0f1;<?php echo $is_new ? 'background:#fff6f6;' : ''; ?>"><?php echo $is_new ? '<strong style="color:#d63638;">● </strong>' : ''; ?><?php echo esc_html( $a['t'] ?? '' ); ?> <span style="color:#646970;font-size:.82em;">— <?php echo esc_html( ! empty( $a['ts'] ) ? wp_date( 'd/m H:i', (int) $a['ts'] ) : '' ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</details>
			</div>
			<?php update_user_meta( $ag_uid, 'ag_activity_seen', time() ); ?>

			<!-- Chasse Google Places -->
			<div style="max-width:980px;margin-top:14px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;border-radius:6px;">
				<h2 style="margin-top:0;">🔎 Trouver des entreprises</h2>
				<?php $ag_use = ag_places_usage(); ?>
				<div style="display:inline-block;background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:8px 14px;margin-bottom:10px;font-size:.9rem;">
					📊 <strong><?php echo (int) $ag_use['calls']; ?></strong> recherche(s) ce mois-ci · coût estimé Google ≈ <strong><?php echo esc_html( number_format( $ag_use['cost'], 2, ',', ' ' ) ); ?> €</strong>
					<span style="color:#646970;"> (1 recherche ≈ 0,04 € · 1 balayage « tous secteurs » ≈ 1,60 € · <strong>Google offre un palier gratuit/mois</strong>, donc souvent 0 € réel).</span>
				</div>
				<?php $ag_ign = count( (array) get_option( 'ag_prospect_ignored', array() ) ); if ( $ag_ign ) : ?>
					<div style="display:inline-block;background:#fdeeee;border:1px solid #f0c2c2;border-radius:8px;padding:6px 12px;margin:0 0 10px 8px;font-size:.85rem;">
						🚫 <strong><?php echo (int) $ag_ign; ?></strong> entreprise(s) ignorée(s) (masquées des recherches)
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:inline;margin-left:6px;" onsubmit="return confirm('Réafficher toutes les entreprises ignorées ?');">
							<input type="hidden" name="action" value="ag_prospect_ignore_reset"><input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
							<button class="button-link" style="color:#2271b1;">réinitialiser</button>
						</form>
					</div>
				<?php endif; ?>
				<?php if ( '' === $key ) : ?>
					<p>Pour la recherche automatique, ajoute ta <strong>clé Google Places (New)</strong> ci-dessous (dans ton projet Google Cloud → active « Places API (New) » → crée une clé API). En attendant, tu peux ajouter des prospects à la main plus bas, ou chercher sur <a href="https://www.google.com/maps" target="_blank" rel="noopener">Google Maps</a> (ex. « restaurant Nantes ») et copier les infos.</p>
					<form method="post" action="options.php">
						<?php settings_fields( 'ag_prospection_cfg' ); ?>
						<input type="text" name="ag_places_key" value="" class="regular-text" placeholder="Clé API Google Places" style="width:420px;">
						<?php submit_button( 'Enregistrer la clé', 'secondary', 'submit', false ); ?>
					</form>
				<?php else : ?>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="ag-search-form">
						<input type="hidden" name="page" value="ag-prospects">
						<input type="text" name="q" id="ag-q" value="<?php echo esc_attr( $q ); ?>" placeholder="Type d'activité (ex : restaurant, coiffeur, plombier)" style="width:320px;">
						<input type="text" name="city" value="<?php echo esc_attr( $city ); ?>" placeholder="Ville (ex : Nantes)" style="width:200px;">
						<?php submit_button( 'Chercher', 'primary', 'submit', false ); ?>
						<button type="submit" name="all" value="1" class="button" title="Balaye tous les secteurs de la ville (plusieurs requêtes) et trie par probabilité d'achat">🌍 Toutes entreprises de la ville</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-prospects' ) ); ?>" class="button">Réinitialiser</a>
					</form>
					<div style="margin:8px 0 0;color:#50575e;font-size:.9rem;">Idées rapides :
						<?php foreach ( array( 'restaurant', 'coiffeur', 'barbier', 'plombier', 'électricien', 'garagiste', 'boulangerie', 'bar', 'institut de beauté', 'artisan', 'coach sportif', 'photographe', 'fleuriste', 'avocat' ) as $c ) : ?>
							<button type="button" class="button button-small ag-chip" data-q="<?php echo esc_attr( $c ); ?>"><?php echo esc_html( $c ); ?></button>
						<?php endforeach; ?>
					</div>
					<?php if ( is_array( $results ) && isset( $results['error'] ) ) : ?>
						<p style="color:#b32d2e;">Erreur Places : <?php echo esc_html( $results['error'] ); ?> (vérifie que « Places API (New) » est activée et la facturation aussi.)</p>
					<?php elseif ( is_array( $results ) ) :
						$ag_refresh_url = add_query_arg( array_filter( array( 'page' => 'ag-prospects', 'q' => $q, 'city' => $city, 'all' => $allsec ? 1 : null, 'refresh' => 1 ) ), admin_url( 'admin.php' ) );
					?>
						<?php if ( $from_cache ) : ?>
							<div style="margin-top:12px;background:#eaf6ec;border:1px solid #b6e0bf;border-radius:8px;padding:8px 14px;font-size:.9rem;">
								💾 <strong>Résultats en cache</strong> (dernier appel le <?php echo esc_html( ! empty( $cached['ts'] ) ? wp_date( 'd/m/Y à H:i', (int) $cached['ts'] ) : '—' ); ?>) — affichage <strong>gratuit, 0 appel Google</strong>.
								<a href="<?php echo esc_url( $ag_refresh_url ); ?>" class="button button-small" style="margin-left:8px;" onclick="return confirm('Relancer un appel Google (≈ 0,04 € la recherche, ≈ 1,60 € le balayage) pour chercher du nouveau ?');">🔄 Actualiser (nouvel appel)</a>
							</div>
						<?php else : ?>
							<div style="margin-top:12px;background:#fef6e7;border:1px solid #f0d9a8;border-radius:8px;padding:8px 14px;font-size:.9rem;">
								🌐 <strong>Nouvel appel Google</strong> effectué — résultats mis en cache : les prochaines fois, « Revoir » sera <strong>gratuit</strong>.
							</div>
						<?php endif; ?>
						<p style="color:#50575e;margin-top:14px;"><?php echo count( $results ); ?> résultat(s), triés par <strong>probabilité d'achat</strong>. <label style="margin-left:8px;"><input type="checkbox" id="ag-onlyno"> N'afficher que ceux <strong>sans vrai site</strong></label></p>
						<table class="widefat striped" id="ag-results"><thead><tr><th>Achat</th><th>Entreprise</th><th>Avis</th><th>Téléphone</th><th>Présence en ligne</th><th></th></tr></thead><tbody>
						<?php foreach ( $results as $r ) : if ( empty( $r['name'] ) ) continue;
								if ( ag_prospect_is_ignored( $r['name'], $r['city'] ?? $city ) ) continue; // 🚫 ignoré → ne plus réafficher
								$ex = ag_prospect_find( $r['name'], $r['city'] ?? $city, $r['phone'] ?? '' );
								if ( $ex && ag_prospect_blocked( $ex['status'] ?? '' ) ) continue; // refusé / ne plus contacter / client → masqué
								$kind = ag_site_kind( $r['website'] ); $rsc = ag_prospect_score( $r ); $rcol = $rsc >= 80 ? '#b32d2e' : ( $rsc >= 60 ? '#bd7b00' : '#50575e' ); ?>
							<tr data-kind="<?php echo esc_attr( $kind[0] ); ?>">
								<td><span style="display:inline-block;min-width:32px;text-align:center;font-weight:800;color:#fff;background:<?php echo esc_attr( $rcol ); ?>;border-radius:6px;padding:2px 6px;"><?php echo (int) $rsc; ?></span></td>
								<td><strong><?php echo esc_html( $r['name'] ); ?></strong><?php if ( $ex ) : $labs = ag_prospect_statuses(); ?> <span style="background:#e7eef7;color:#1d4f8b;border-radius:10px;padding:1px 8px;font-size:.78em;">déjà en liste · <?php echo esc_html( $labs[ $ex['status'] ?? 'nouveau' ] ?? '' ); ?></span><?php endif; ?><br><small><?php echo esc_html( ( $r['type'] ?? '' ) . ' · ' . ( $r['address'] ?? '' ) ); ?></small><br><small style="color:#50575e;"><?php echo esc_html( ag_prospect_diagnostic( $r ) ); ?></small></td>
								<td><?php $glink = ag_google_link( $r ); $av = ( $r['reviews'] ?? 0 ) ? ( (int) $r['reviews'] . ' avis · ' . number_format( (float) ( $r['rating'] ?? 0 ), 1 ) . '★' ) : 'Voir avis'; echo $glink ? '<a href="' . esc_url( $glink ) . '" target="_blank" rel="noopener" title="Ouvrir la fiche Google + avis">📍 ' . esc_html( $av ) . '</a>' : esc_html( $av ); ?></td>
								<td><?php echo esc_html( $r['phone'] ); ?></td>
								<td><?php echo ( 'real' === $kind[0] ) ? '<a href="' . esc_url( $r['website'] ) . '" target="_blank" rel="noopener">site ✓</a>' : '<strong style="color:#b32d2e;">' . esc_html( $kind[1] ) . '</strong>'; ?></td>
								<td>
									<textarea class="ag-add-note" rows="2" placeholder="📝 Ma note (ce que je peux faire pour eux…)" style="width:210px;font-size:.82em;margin-bottom:4px;display:block;"></textarea>
									<button type="button" class="button button-primary ag-add"
										data-name="<?php echo esc_attr( $r['name'] ); ?>" data-type="<?php echo esc_attr( $r['type'] ?? $q ); ?>"
										data-city="<?php echo esc_attr( $r['city'] ?? $city ); ?>" data-phone="<?php echo esc_attr( $r['phone'] ); ?>"
										data-website="<?php echo esc_attr( $r['website'] ); ?>" data-address="<?php echo esc_attr( $r['address'] ); ?>" data-maps="<?php echo esc_attr( $r['maps_uri'] ?? '' ); ?>"
										data-rating="<?php echo esc_attr( $r['rating'] ?? 0 ); ?>" data-reviews="<?php echo esc_attr( $r['reviews'] ?? 0 ); ?>">+ Suivre (avec ma note)</button>
									<button type="button" class="button button-small ag-ignore" data-name="<?php echo esc_attr( $r['name'] ); ?>" data-city="<?php echo esc_attr( $r['city'] ?? $city ); ?>" data-type="<?php echo esc_attr( $r['type'] ?? $q ); ?>" data-phone="<?php echo esc_attr( $r['phone'] ); ?>" data-website="<?php echo esc_attr( $r['website'] ); ?>" data-address="<?php echo esc_attr( $r['address'] ); ?>" data-rating="<?php echo esc_attr( $r['rating'] ?? 0 ); ?>" data-reviews="<?php echo esc_attr( $r['reviews'] ?? 0 ); ?>" title="Garde l'entreprise en statut 'Ignoré' (consultable, mais retirée des recherches)" style="margin-top:4px;color:#b32d2e;">🙈 Ignorer</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody></table>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<!-- Mes recherches (historique) -->
			<?php
			$ag_hist = (array) get_option( 'ag_search_history', array() );
			if ( $ag_hist ) :
				$hsort = isset( $_GET['hsort'] ) ? sanitize_text_field( wp_unslash( $_GET['hsort'] ) ) : 'city';
				$hsort_options = array(
					'city'       => 'Ville (A→Z), puis métier',
					'q'          => 'Métier (A→Z), puis ville',
					'date_desc'  => 'Plus récentes d\'abord',
					'date_asc'   => 'Plus anciennes d\'abord',
					'count_desc' => 'Plus de résultats d\'abord',
					'count_asc'  => 'Moins de résultats d\'abord',
				);
				usort( $ag_hist, function ( $a, $b ) use ( $hsort ) {
					switch ( $hsort ) {
						case 'q':
							$c = strcasecmp( $a['q'] ?? '', $b['q'] ?? '' );
							return $c !== 0 ? $c : strcasecmp( $a['city'] ?? '', $b['city'] ?? '' );
						case 'date_desc':
							return ( (int) ( $b['ts'] ?? 0 ) ) <=> ( (int) ( $a['ts'] ?? 0 ) );
						case 'date_asc':
							return ( (int) ( $a['ts'] ?? 0 ) ) <=> ( (int) ( $b['ts'] ?? 0 ) );
						case 'count_desc':
							return ( (int) ( $b['count'] ?? 0 ) ) <=> ( (int) ( $a['count'] ?? 0 ) );
						case 'count_asc':
							return ( (int) ( $a['count'] ?? 0 ) ) <=> ( (int) ( $b['count'] ?? 0 ) );
						case 'city':
						default:
							$c = strcasecmp( $a['city'] ?? '', $b['city'] ?? '' );
							return $c !== 0 ? $c : strcasecmp( $a['q'] ?? '', $b['q'] ?? '' );
					}
				} );
				$back_url = add_query_arg( array_filter( array( 'page' => 'ag-prospects', 'hsort' => 'city' === $hsort ? null : $hsort ) ), admin_url( 'admin.php' ) );
			?>
			<div style="max-width:980px;margin-top:18px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #2271b1;border-radius:6px;">
				<h2 style="margin-top:0;">📂 Mes recherches (<?php echo count( $ag_hist ); ?>)</h2>
				<p style="color:#50575e;font-size:.9rem;"><strong>« Revoir » est GRATUIT</strong> : ça réaffiche les résultats déjà trouvés (aucun appel Google). « Actualiser » relance un appel payant uniquement si tu veux chercher du nouveau.</p>
				<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin:6px 0 10px;">
					<input type="hidden" name="page" value="ag-prospects">
					<label style="font-size:.9rem;color:#50575e;">Trier par : </label>
					<select name="hsort" onchange="this.form.submit()">
						<?php foreach ( $hsort_options as $hk => $hlabel ) : ?>
							<option value="<?php echo esc_attr( $hk ); ?>" <?php selected( $hsort, $hk ); ?>><?php echo esc_html( $hlabel ); ?></option>
						<?php endforeach; ?>
					</select>
					<noscript><button class="button button-small">OK</button></noscript>
				</form>
				<div class="aghist-bulkbar" style="margin:8px 0 4px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
					<label style="font-size:.9em;"><input type="checkbox" id="aghist-check-all"> Tout sélectionner</label>
					<button type="button" id="aghist-del-selected" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="aghist-sel-count">0</span>)</button>
				</div>
				<table class="widefat striped" style="margin-top:4px;"><thead><tr><th style="width:28px;"><input type="checkbox" id="aghist-check-all2"></th><th>Ville</th><th>Métier</th><th>Résultats</th><th>Dernière fois</th><th></th></tr></thead><tbody>
				<?php foreach ( $ag_hist as $h ) :
					$hq = $h['q'] ?? ''; $hc = $h['city'] ?? ''; $hall = ( '' === trim( $hq ) );
					$url  = add_query_arg( array_filter( array( 'page' => 'ag-prospects', 'q' => $hq, 'city' => $hc ) ), admin_url( 'admin.php' ) );
					$rurl = add_query_arg( array_filter( array( 'page' => 'ag-prospects', 'q' => $hq, 'city' => $hc, 'all' => $hall ? 1 : null, 'refresh' => 1 ) ), admin_url( 'admin.php' ) );
					$has_cache = ! empty( $h['results'] );
					$hkey = ag_search_history_key( $hq, $hc );
				?>
					<tr>
						<td style="text-align:center;"><input type="checkbox" class="aghist-check" value="<?php echo esc_attr( $hkey ); ?>"></td>
						<td><strong><?php echo esc_html( $hc ?: '—' ); ?></strong></td>
						<td><?php echo esc_html( $hq !== '' ? $hq : '🌍 Tous secteurs' ); ?></td>
						<td><?php echo (int) ( $h['count'] ?? 0 ); ?><?php echo $has_cache ? ' <span title="Résultats en cache, revoir gratuit">💾</span>' : ''; ?></td>
						<td><?php echo esc_html( ! empty( $h['ts'] ) ? wp_date( 'd/m/Y', (int) $h['ts'] ) : '—' ); ?></td>
						<td>
							<?php if ( $has_cache ) : ?><a class="button button-small button-primary" href="<?php echo esc_url( $url ); ?>" title="Réaffiche les résultats déjà trouvés (gratuit)">👁 Revoir (gratuit)</a> <?php else : ?><span style="color:#b26a00;font-size:.82em;">⚠ Pas en cache (faite avant la mise en cache) — clique « Actualiser » 1 fois pour activer « Revoir ».</span><br><?php endif; ?>
							<a class="button button-small" href="<?php echo esc_url( $rurl ); ?>" title="Relance un appel Google payant pour chercher du nouveau" onclick="return confirm('Relancer un appel Google payant (≈ 0,04 € · balayage ≈ 1,60 €) ?');">🔄 Actualiser</a>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:inline-block;margin:0;" onsubmit="return confirm('Supprimer cette recherche de l\'historique ?');">
								<input type="hidden" name="action" value="ag_search_history_delete">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="key" value="<?php echo esc_attr( $hkey ); ?>">
								<input type="hidden" name="_back" value="<?php echo esc_attr( $back_url ); ?>">
								<button class="button button-small" title="Supprimer cette recherche">🗑️</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
				<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-top:8px;" onsubmit="return confirm('Effacer TOUT l\'historique des recherches ?');">
					<input type="hidden" name="action" value="ag_search_history_clear"><input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
					<button class="button button-small">Effacer tout l'historique</button>
				</form>
			</div>
			<?php endif; ?>

			<!-- Entreprises au tribunal (redressement judiciaire) -->
			<div style="max-width:980px;margin-top:18px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #7a4ed4;border-radius:6px;">
				<h2 style="margin-top:0;">🏛️ Entreprises au tribunal (redressement judiciaire)</h2>
				<p style="color:#50575e;max-width:760px;">Données <strong>publiques BODACC</strong> (gratuit). On cible celles en <strong>redressement / sauvegarde</strong> (elles se battent pour rebondir → elles ont besoin de regagner des clients) et on <strong>exclut les liquidations</strong> (qui ferment). Le robot génère un <strong>message empathique adapté</strong>. ⚙️ <strong>« + Suivre » cherche automatiquement le site de l'entreprise (1 appel Google) puis lance un audit de sécurité passif</strong> : la fiche arrive complète (prospection + analyse + sécurité reliées).</p>
				<?php $tsector = isset( $_GET['tsector'] ) ? sanitize_text_field( wp_unslash( $_GET['tsector'] ) ) : ''; ?>
				<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
					<input type="hidden" name="page" value="ag-prospects">
					<input type="text" name="tcity" value="<?php echo esc_attr( isset( $_GET['tcity'] ) ? sanitize_text_field( wp_unslash( $_GET['tcity'] ) ) : '' ); ?>" placeholder="Ville (ex : Nantes)" style="width:220px;">
					<select name="tsector" style="margin:0 6px;">
						<?php foreach ( ag_bodacc_sector_labels() as $sk => $sl ) : ?>
							<option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $tsector, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php submit_button( '🏛️ Chercher au tribunal', 'secondary', 'submit', false ); ?>
				</form>
				<p style="color:#646970;font-size:.84em;margin:6px 0 0;">💡 Pour les cibles avocats : choisis « ⚖️ Avocats / juridique » + une grande ville (Nantes, Saint-Nazaire…). On filtre les fiches du tribunal sur le secteur. <strong>Attention</strong> : un « mandataire » ou « administrateur judiciaire » n'est PAS une cible (il gère les procédures des autres) — c'est un partenaire « coordination ».</p>
				<?php
				$tcity = isset( $_GET['tcity'] ) ? sanitize_text_field( wp_unslash( $_GET['tcity'] ) ) : '';
				if ( '' !== $tcity ) :
					$tres = ag_bodacc_search( $tcity, $tsector );
					if ( is_array( $tres ) && isset( $tres['error'] ) ) : ?>
						<p style="color:#b32d2e;">Erreur BODACC : <?php echo esc_html( $tres['error'] ); ?></p>
					<?php elseif ( empty( $tres ) ) : ?>
						<p style="color:#50575e;">Aucune entreprise en redressement trouvée pour « <?php echo esc_html( $tcity ); ?> » (ou aucune annonce récente). Essaie une grande ville proche.</p>
					<?php else : ?>
						<p style="color:#50575e;margin-top:12px;"><strong><?php echo count( $tres ); ?></strong> entreprise(s) en redressement / sauvegarde. 💡 Approche <strong>solidaire</strong> (comme l'offre quartiers/assos) : tu apportes ton <strong>expertise gratuitement</strong> (ou en partenariat sur les projets prometteurs), à coordonner avec l'<strong>administrateur / mandataire judiciaire</strong>. Le message ci-dessous est déjà rédigé dans cet esprit.</p>
						<table class="widefat striped"><thead><tr><th>Entreprise</th><th>Activité</th><th>Procédure</th><th>Contact</th><th>Message</th><th></th></tr></thead><tbody>
						<?php foreach ( $tres as $tc ) :
							$tex = ag_prospect_find( $tc['name'], $tc['city'], '' );
							// Fiche de jugement COMPLÈTE — elle doit SUIVRE le prospect quand on l'ajoute.
							$tjug = trim( (string) $tc['nature'] )
								. ( ! empty( $tc['tribunal'] ) ? ' · ' . $tc['tribunal'] : '' )
								. ( ! empty( $tc['date'] ) ? ' · paru le ' . $tc['date'] : '' )
								. ( ! empty( $tc['siren'] ) ? ' · SIREN ' . $tc['siren'] : '' )
								. ( ! empty( $tc['complement'] ) ? ' — ' . wp_strip_all_tags( (string) $tc['complement'] ) : '' );
							$tnotes = '🏛️ BODACC (procédure collective) : ' . $tjug;
							$tmsg = ag_bodacc_message( $tc );
							$tsearch = add_query_arg( array( 'page' => 'ag-prospects', 'q' => $tc['name'], 'city' => $tc['city'] ), admin_url( 'admin.php' ) );
						?>
							<tr>
								<td><strong><?php echo esc_html( $tc['name'] ); ?></strong><br><small><?php echo esc_html( $tc['city'] ); ?></small><?php if ( $tex ) : ?> <span style="background:#e7eef7;color:#1d4f8b;border-radius:10px;padding:1px 8px;font-size:.78em;">déjà en liste</span><?php endif; ?></td>
								<td style="font-size:.85em;"><?php echo esc_html( $tc['activite'] ?: '—' ); ?></td>
								<td style="font-size:.82em;color:#7a4ed4;font-weight:600;max-width:240px;"><?php echo esc_html( $tc['nature'] ); ?><?php echo $tc['date'] ? '<br><span style="color:#50575e;font-weight:400;">' . esc_html( $tc['date'] ) . '</span>' : ''; ?><?php if ( ! empty( $tc['complement'] ) ) : ?><br><span style="color:#50575e;font-weight:400;font-style:italic;">⚖️ <?php echo esc_html( wp_trim_words( $tc['complement'], 30 ) ); ?></span><?php endif; ?></td>
								<td><a class="button button-small" href="<?php echo esc_url( $tsearch ); ?>" title="Trouver le téléphone via Google Places">🔎 Trouver le tel</a></td>
								<td><details><summary class="button button-small">Message</summary><textarea readonly rows="9" style="width:340px;margin-top:6px;"><?php echo esc_textarea( $tmsg ); ?></textarea></details></td>
								<td><?php if ( $tex ) : ?><span style="color:#50575e;font-size:.85em;">✓ suivi</span><?php else : ?><button type="button" class="button button-primary ag-add" data-name="<?php echo esc_attr( $tc['name'] ); ?>" data-type="<?php echo esc_attr( $tc['activite'] ); ?>" data-city="<?php echo esc_attr( $tc['city'] ); ?>" data-phone="" data-website="" data-address="" data-rating="0" data-reviews="0" data-notes="<?php echo esc_attr( $tnotes ); ?>" data-bodacc="<?php echo esc_attr( $tjug ); ?>" data-source="tribunal">+ Suivre</button><?php endif; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody></table>
					<?php endif;
				endif;
				?>
			</div>

			<!-- Agent automatique -->
			<div style="max-width:980px;margin-top:18px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #F37A1F;border-radius:6px;">
				<h2 style="margin-top:0;">🤖 Agent automatique</h2>
				<?php if ( '' === $key ) : ?>
					<p>Ajoute d'abord ta <strong>clé Google Places</strong> ci-dessus pour activer la recherche automatique.</p>
				<?php else :
					$auto = (array) get_option( 'ag_auto_searches', array() );
					$lr   = (array) get_option( 'ag_prospect_lastrun', array() );
					?>
					<p>L'agent cherche <strong>tout seul</strong> les recherches ci-dessous et ajoute les nouvelles entreprises à ta liste (tu reçois un email récap). <strong>Il ne contacte personne automatiquement</strong> — tu prospectes toi-même, en 1 clic.</p>
					<?php $ag_cap = (int) get_option( 'ag_places_cap', 1000 ); $ag_freq = get_option( 'ag_auto_freq', 'weekly' ); $ag_u2 = ag_places_usage(); ?>
					<div style="background:#fff7e6;border:1px solid #e9c96a;border-radius:8px;padding:12px 16px;margin-bottom:12px;">
						<strong>💰 Maîtrise du coût Google</strong>
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
							<input type="hidden" name="action" value="ag_auto_settings">
							<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
							<label>Fréquence :
								<select name="freq">
									<option value="weekly" <?php selected( $ag_freq, 'weekly' ); ?>>1×/semaine (recommandé)</option>
									<option value="daily" <?php selected( $ag_freq, 'daily' ); ?>>1×/jour (plus cher)</option>
									<option value="off" <?php selected( $ag_freq, 'off' ); ?>>Désactivé</option>
								</select>
							</label>
							<label>Plafond d'appels / mois : <input type="number" name="cap" min="0" value="<?php echo (int) $ag_cap; ?>" style="width:110px;"></label>
							<?php submit_button( 'Enregistrer', 'secondary', 'submit', false ); ?>
							<span style="color:#7a5b00;">Utilisé : <strong><?php echo (int) $ag_u2['calls']; ?></strong> / <?php echo $ag_cap ? (int) $ag_cap : '∞'; ?> ce mois (≈ <?php echo esc_html( number_format( $ag_u2['cost'], 2, ',', ' ' ) ); ?> €). Au plafond, l'agent s'arrête.</span>
						</form>
					</div>
					<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:10px;">
						<input type="hidden" name="action" value="ag_autosearch_add">
						<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
						<input type="text" name="q" placeholder="Type (laisse vide = TOUS secteurs)" style="width:240px;">
						<input type="text" name="city" placeholder="Ville *" required style="width:160px;">
						<?php submit_button( '+ Ajouter une recherche auto', 'primary', 'submit', false ); ?>
						<p class="description" style="margin:4px 0 0;">Astuce : laisse le <strong>métier vide</strong> et mets juste la <strong>ville</strong> → l'agent balaie <strong>tous les secteurs</strong> et garde les plus susceptibles d'acheter.</p>
					</form>
					<?php if ( $auto ) : ?>
						<ul style="margin:0 0 10px;">
						<?php foreach ( $auto as $i => $a ) : ?>
							<li style="margin-bottom:4px;">🔁 <strong><?php echo esc_html( ( '' !== trim( $a['q'] ?? '' ) ? $a['q'] : '🌍 Tous secteurs' ) . ( ! empty( $a['city'] ) ? ' — ' . $a['city'] : '' ) ); ?></strong>
								<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:inline;">
									<input type="hidden" name="action" value="ag_autosearch_del"><input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>"><input type="hidden" name="i" value="<?php echo (int) $i; ?>">
									<button class="button-link" style="color:#b32d2e;">retirer</button>
								</form>
							</li>
						<?php endforeach; ?>
						</ul>
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:inline;">
							<input type="hidden" name="action" value="ag_autosearch_run"><input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
							<button class="button">▶ Lancer maintenant</button>
						</form>
						<?php if ( ! empty( $lr['ts'] ) ) : ?><span style="color:#50575e;margin-left:10px;">Dernier passage : <?php echo esc_html( date_i18n( 'd/m/Y H:i', $lr['ts'] ) ); ?> (+<?php echo (int) ( $lr['added'] ?? 0 ); ?>)</span><?php endif; ?>
					<?php else : ?>
						<p style="color:#50575e;">Ajoute au moins une recherche pour activer l'agent.</p>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<!-- Ajout manuel -->
			<div style="max-width:980px;margin-top:18px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:6px;">
				<h2 style="margin-top:0;">➕ Ajouter un prospect à la main</h2>
				<form method="post" action="<?php echo esc_url( $post ); ?>">
					<input type="hidden" name="action" value="ag_prospect_save">
					<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
					<input type="text" name="name" placeholder="Nom de l'entreprise *" required style="width:240px;">
					<input type="text" name="type" placeholder="Type (resto, artisan…)" style="width:160px;">
					<input type="text" name="city" placeholder="Ville" style="width:140px;">
					<input type="text" name="phone" placeholder="Téléphone" style="width:140px;">
					<input type="email" name="email" placeholder="Email" style="width:200px;">
					<input type="url" name="website" placeholder="Site web (si existant)" style="width:200px;">
					<?php submit_button( 'Ajouter', 'secondary', 'submit', false ); ?>
				</form>
			</div>

			<!-- Mes prospects -->
			<h2 style="margin-top:26px;">📋 Mes prospects (<?php echo count( $prospects ); ?>) — triés par <strong>besoin</strong></h2>
			<?php
			$ag_all = (array) get_option( 'ag_prospects', array() );
			$cnt = array( 'nouveau' => 0, 'contacte' => 0, 'sans_reponse' => 0, 'interesse' => 0, 'client' => 0, 'bloque' => 0, 'ignore' => 0, 'relance7' => 0 );
			foreach ( $ag_all as $pp ) {
				$s = $pp['status'] ?? 'nouveau';
				if ( 'nouveau' === $s ) $cnt['nouveau']++;
				elseif ( in_array( $s, array( 'contacte', 'relance' ), true ) ) $cnt['contacte']++;
				elseif ( 'sans_reponse' === $s ) $cnt['sans_reponse']++;
				elseif ( 'interesse' === $s ) $cnt['interesse']++;
				elseif ( 'client' === $s ) $cnt['client']++;
				elseif ( 'ignore' === $s ) $cnt['ignore']++;
				elseif ( in_array( $s, array( 'refus', 'ne_pas_contacter' ), true ) ) $cnt['bloque']++;
				if ( ag_prospect_relance_due( $pp ) ) $cnt['relance7']++;
			}
			$ag_chip = function ( $label, $n, $f, $bg ) use ( $f_status ) {
				$url = add_query_arg( array( 'page' => 'ag-prospects', 'fstatus' => $f ), admin_url( 'admin.php' ) );
				$on  = ( (string) $f_status === (string) $f );
				return '<a href="' . esc_url( $url ) . '" style="text-decoration:none;display:inline-block;margin:0 6px 6px 0;padding:5px 12px;border-radius:100px;font-weight:700;font-size:.82rem;background:' . $bg . ';color:#fff;opacity:' . ( $on ? '1' : '.82' ) . ';border:' . ( $on ? '2px solid #1d2327' : '2px solid transparent' ) . ';">' . esc_html( $label ) . ' ' . (int) $n . '</a>';
			};
			?>
			<div style="margin:8px 0 14px;">
				<?php
				echo $ag_chip( '🆕 À contacter', $cnt['nouveau'], 'nouveau', '#b32d2e' );
				echo $ag_chip( '📞 Contactés', $cnt['contacte'], 'contacte', '#2271b1' );
				echo $ag_chip( '🔇 Sans réponse', $cnt['sans_reponse'], 'sans_reponse', '#8a6d1f' );
				echo $ag_chip( '🔥 Intéressés', $cnt['interesse'], 'interesse', '#bd7b00' );
				echo $ag_chip( '✅ Clients', $cnt['client'], 'client', '#1e7e34' );
				echo $ag_chip( '🔁 À relancer (7j+)', $cnt['relance7'], 'relance7', '#c2410c' );
				echo $ag_chip( '🚫 Bloqués', $cnt['bloque'], 'ne_pas_contacter', '#50575e' );
				echo $ag_chip( '🙈 Ignorés', $cnt['ignore'], 'ignore', '#7a4ed4' );
				?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ag-prospects' ), admin_url( 'admin.php' ) ) ); ?>" style="text-decoration:none;font-size:.82rem;color:#2271b1;margin-left:6px;">Tout afficher</a>
			</div>
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin-bottom:10px;">
				<input type="hidden" name="page" value="ag-prospects">
				<input type="search" name="fq" value="<?php echo esc_attr( $f_q ); ?>" placeholder="Filtrer (nom, ville, métier)" style="width:240px;">
				<select name="fstatus"><option value="">Tous les statuts</option><?php foreach ( $labels as $k => $lab ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $f_status, $k ); ?>><?php echo esc_html( $lab ); ?></option><?php endforeach; ?></select>
				<select name="sort"><option value="besoin" <?php selected( $sortby, 'besoin' ); ?>>Tri : besoin (priorité)</option><option value="date" <?php selected( $sortby, 'date' ); ?>>Tri : plus récents</option><option value="nom" <?php selected( $sortby, 'nom' ); ?>>Tri : nom</option></select>
				<?php submit_button( 'Filtrer', 'secondary', 'submit', false ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-prospects' ) ); ?>" class="button">Tout voir</a>
			</form>
			<?php if ( empty( $prospects ) ) : ?>
				<p>Aucun prospect (avec ces filtres). Cherche des entreprises ci-dessus ou ajoute-en à la main.</p>
			<?php else : ?>
				<?php $ag_gw = function_exists( 'ag_sms_gateway_ready' ) && ag_sms_gateway_ready(); ?>
				<div class="ag-bulkbar" style="margin:10px 0;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
					<span style="display:inline-flex;gap:4px;align-items:center;border-right:1px solid #dcdcde;padding-right:10px;"><strong style="font-size:.82em;color:#50575e;">Afficher :</strong> <button type="button" class="button button-small button-primary ag-fltr" data-f="all">Tous</button> <button type="button" class="button button-small ag-fltr" data-f="0" title="Entreprises sans vrai site — angle CREATION de site">🆕 Sans site</button> <button type="button" class="button button-small ag-fltr" data-f="1" title="Entreprises avec un site — angle AUDIT securite / refonte">🔒 Avec site</button></span> <span style="display:inline-flex;gap:4px;align-items:center;border-right:1px solid #dcdcde;padding-right:10px;"><strong style="font-size:.82em;color:#50575e;">Tél :</strong> <button type="button" class="button button-small button-primary ag-fltrm" data-m="all">Tous</button> <button type="button" class="button button-small ag-fltrm" data-m="1" title="Afficher seulement les mobiles (SMS + Robot)">📱 Mobiles</button> <button type="button" class="button button-small ag-fltrm" data-m="0" title="Afficher seulement les fixes (Robot vocal)">☎️ Fixes</button></span>
					<label style="font-size:.9em;"><input type="checkbox" id="ag-check-all"> Tout sélectionner</label>
					<button type="button" id="ag-pick-mob" class="button button-small" title="Cocher tous les mobiles 06/07 (envoi SMS)">📱 Cocher les mobiles</button>
					<button type="button" id="ag-pick-fix" class="button button-small" title="Cocher tous les fixes (pas de SMS — appel/robot vocal)">☎️ Cocher les fixes</button>
					<button type="button" id="ag-sms-selected" class="button button-small" disabled <?php echo $ag_gw ? '' : 'title="Configure la Passerelle SMS"'; ?>>📲 Envoyer SMS (sélection)</button>
					<?php $ag_voice_ok = function_exists( 'ag_voice_ready' ) && ag_voice_ready(); ?>
					<label style="font-size:.82em;color:#50575e;">Angle : <select id="ag-voice-angle" style="font-size:.85em;padding:1px 4px;"><option value="auto">Auto (selon site)</option><option value="creation">🆕 Création web</option><option value="securite">🔒 Sécurité</option></select></label>
					<button type="button" id="ag-voice-selected" class="button button-small" style="background:#f5f0ff;border-color:#7c3aed;color:#7c3aed;font-weight:600;" disabled <?php echo $ag_voice_ok ? '' : 'title="Configure le Robot vocal (Prospection → 🤖 Robot vocal)"'; ?>>🤖 Robot : appeler la sélection</button>
					<button type="button" id="ag-del-selected" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="ag-sel-count">0</span>)</button>
					<?php if ( ! $ag_gw ) : ?><span style="font-size:.8em;color:#b26a00;">📲 <a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-sms-gateway' ) ); ?>">Passerelle SMS</a> à activer pour l'envoi groupé.</span><?php endif; ?>
				</div>
				<table class="widefat striped"><thead><tr><th style="width:28px;"><input type="checkbox" id="ag-check-all2" title="Tout sélectionner"></th><th>Priorité</th><th>Entreprise</th><th>Zone</th><th>Pourquoi (besoin)</th><th>Contact</th><th>Statut</th><th>Assigné à</th><th>Prospecter</th><th></th></tr></thead><tbody>
				<?php
				// Regroupe les MOBILES (06/07) en haut (SMS = mobile only), ordre 'par besoin' conservé dans chaque groupe.
				$ag_mob = array(); $ag_fix = array();
				foreach ( $prospects as $ppz ) { if ( ag_phone_is_mobile( $ppz['phone'] ?? '', $ppz['phone_intl'] ?? '' ) ) $ag_mob[] = $ppz; else $ag_fix[] = $ppz; }
				$prospects = array_merge( $ag_mob, $ag_fix );
				?>
				<?php foreach ( $prospects as $p ) :
					$digits = ag_wa_number( $p['phone'] ?? '', $p['phone_intl'] ?? '' );
					$msg    = ag_prospect_message( $p );
					$mailto = $p['email'] ? 'mailto:' . rawurlencode( $p['email'] ) . '?subject=' . rawurlencode( 'Votre site web — Alliance Groupe' ) . '&body=' . rawurlencode( $msg ) : '';
					$wa     = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
					$smsnum = preg_replace( '/[^0-9+]/', '', $p['phone_intl'] ?? '' ) ?: preg_replace( '/[^0-9+]/', '', $p['phone'] ?? '' );
					$sms    = $smsnum ? 'sms:' . $smsnum . '?body=' . rawurlencode( $msg ) : '';
					$score  = ag_prospect_score( $p );
					$scol   = $score >= 80 ? '#b32d2e' : ( $score >= 60 ? '#bd7b00' : '#50575e' );
					$blocked = ag_prospect_blocked( $p['status'] ?? '' );
					?>
					<tr class="ag-prow" data-site="<?php echo ( 'real' === ag_site_kind( $p['website'] ?? '' )[0] ) ? '1' : '0'; ?>" data-mob="<?php echo ag_phone_is_mobile( $p['phone'] ?? '', $p['phone_intl'] ?? '' ) ? '1' : '0'; ?>"<?php echo $blocked ? ' style="opacity:.72;background:#fbf3f3;"' : ''; ?>>
						<td style="text-align:center;"><input type="checkbox" class="ag-check" data-mob="<?php echo ag_phone_is_mobile( $p['phone'] ?? '', $p['phone_intl'] ?? '' ) ? '1' : '0'; ?>" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>"></td>
						<td><span style="display:inline-block;min-width:34px;text-align:center;font-weight:800;color:#fff;background:<?php echo esc_attr( $scol ); ?>;border-radius:6px;padding:2px 6px;"><?php echo (int) $score; ?></span></td>
						<td><strong><?php echo esc_html( $p['name'] ?? '' ); ?></strong><?php $pk = ag_site_kind( $p['website'] ?? '' ); echo ( 'real' !== $pk[0] ) ? ' <span style="color:#b32d2e;" title="' . esc_attr( $pk[1] ) . '">❗</span>' : ''; ?><br><small><?php echo esc_html( ( $p['type'] ?? '' ) . ( ! empty( $p['city'] ) ? ' · ' . $p['city'] : '' ) ); ?></small>
								<?php if ( ! empty( $p['bodacc'] ) ) : ?>
									<div style="margin-top:5px;font-size:.78em;color:#5a2ca0;background:#f3eefc;border-left:3px solid #7a4ed4;border-radius:4px;padding:4px 7px;line-height:1.4;" title="Fiche de jugement BODACC conservée avec le prospect">🏛️ <strong>Tribunal</strong> — <?php echo esc_html( $p['bodacc'] ); ?></div>
								<?php endif; ?></td>
						<td style="font-size:.85em;white-space:nowrap;"><?php
							$pdept = function_exists( 'ag_prospect_dept' ) ? ag_prospect_dept( $p ) : '';
							if ( $pdept ) {
								$dn = function_exists( 'ag_dept_names' ) ? ( ag_dept_names()[ $pdept ] ?? '' ) : '';
								$owned = function_exists( 'ag_zone_owners' ) && ! empty( ag_zone_owners( $pdept ) );
								echo '<span style="display:inline-block;background:' . ( $owned ? '#e9f7ee' : '#f0f0f1' ) . ';border-radius:6px;padding:2px 8px;font-weight:700;">' . esc_html( $pdept ) . '</span>';
								if ( $dn ) echo '<br><small style="color:#646970;">' . esc_html( $dn ) . '</small>';
							} else { echo '<span style="color:#b9b9c0;">—</span>'; }
						?></td>
						<td style="max-width:280px;font-size:.85em;color:#50575e;"><?php echo esc_html( ag_prospect_why( $p ) ); ?><br><span style="color:#1d4f8b;"><?php echo esc_html( ag_prospect_diagnostic( $p ) ); ?></span></td>
						<td>
							<?php if ( ! empty( $p['phone'] ) ) : $ag_ismob = ag_phone_is_mobile( $p['phone'], $p['phone_intl'] ?? '' ); ?><a href="tel:<?php echo esc_attr( $p['phone'] ); ?>" title="<?php echo $ag_ismob ? 'Mobile (SMS OK)' : 'Fixe (pas de SMS)'; ?>"><?php echo $ag_ismob ? '📱' : '☎️'; ?> <?php echo esc_html( $p['phone'] ); ?></a><br><?php endif; ?>
							<?php if ( ! empty( $p['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a><?php endif; ?>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_prospect_update">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="id" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>">
								<select name="status" onchange="this.form.submit()">
									<?php foreach ( $labels as $k => $lab ) : ?>
										<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $p['status'] ?? 'nouveau', $k ); ?>><?php echo esc_html( $lab ); ?></option>
									<?php endforeach; ?>
								</select>
							</form>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_prospect_update">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="id" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>">
								<select name="owner" onchange="this.form.submit()">
									<option value="">— personne —</option>
									<?php foreach ( $ambs as $em => $nm ) : ?><option value="<?php echo esc_attr( $em ); ?>" <?php selected( $p['owner_email'] ?? '', $em ); ?>><?php echo esc_html( $nm ); ?></option><?php endforeach; ?>
								</select>
							</form>
						</td>
						<td>
							<?php if ( $blocked ) : ?>
								<div style="font-size:.8em;color:#b32d2e;font-weight:700;margin-bottom:6px;">⛔ À ne pas recontacter — fiche conservée (consultable)</div>
								<?php if ( ! empty( $p['phone'] ) ) : ?><a class="button button-small" href="tel:<?php echo esc_attr( $p['phone'] ); ?>">📞 <?php echo esc_html( $p['phone'] ); ?></a> <?php endif; ?>
								<?php if ( ! empty( $p['email'] ) ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $p['email'] ); ?>">✉️ <?php echo esc_html( $p['email'] ); ?></a> <?php endif; ?>
								<?php if ( ! empty( $p['website'] ) ) : ?><a class="button button-small" href="<?php echo esc_url( $p['website'] ); ?>" target="_blank" rel="noopener">🔗 Voir le site</a> <?php endif; ?>
								<?php $pg = ag_google_link( $p ); if ( $pg ) : ?><a class="button button-small" href="<?php echo esc_url( $pg ); ?>" target="_blank" rel="noopener" title="Fiche Google + avis">📍 Avis Google</a> <?php endif; ?>
								<?php echo ag_prospect_links_block( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<details style="display:block;margin-top:6px;"><summary class="button button-small">📝 Ma fiche (notes)</summary>
									<textarea class="ag-note-field" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" rows="4" style="width:360px;margin-top:6px;" placeholder="Notes…"><?php echo esc_textarea( $p['notes'] ?? '' ); ?></textarea><br>
									<button type="button" class="button button-small button-primary ag-note-save" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>">💾 Enregistrer</button>
									<span class="ag-note-ok" style="color:#1e7e34;display:none;">✓ enregistré</span>
								</details>
							<?php else : ?>
							<div class="ag-suivi" style="font-size:.8em;margin-bottom:6px;line-height:1.5;<?php echo empty( $p['date_contact'] ) ? 'color:#b26a00;' : 'color:#50575e;'; ?>">
								<?php if ( ! empty( $p['date_contact'] ) ) : ?>
									📨 Contacté<?php echo ! empty( $p['last_channel'] ) ? ' par <strong>' . esc_html( $p['last_channel'] ) . '</strong>' : ''; ?> le <strong><?php echo esc_html( $p['date_contact'] ); ?></strong> (×<?php echo (int) ( $p['contact_count'] ?? 1 ); ?>)<br>
									<?php if ( ! empty( $p['replied'] ) ) : ?>
										<span style="color:#1e7e34;font-weight:700;">✅ A répondu<?php echo $p['date_reply'] ? ' le ' . esc_html( $p['date_reply'] ) : ''; ?></span>
									<?php else : ?>
										<button type="button" class="button button-small ag-reply" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>">A répondu ?</button>
									<?php endif; ?>
									<br><span style="color:#646970;">Résultat :</span>
									<button type="button" class="button button-small ag-outcome" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" data-outcome="bloque" title="Il a bloqué / refusé">⛔ Bloqué</button>
									<button type="button" class="button button-small ag-outcome" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" data-outcome="sans_reponse" title="Pas de réponse">🔇 Sans réponse</button>
								<?php else : ?>⏳ Pas encore contacté<?php endif; ?>
							</div>
							<?php
							// Bouton RELANCE — présent sur TOUS les prospects (non bloqués).
							$relance_due = ag_prospect_relance_due( $p );
							$rlabel = ! empty( $p['last_relance'] ) ? '🔁 Relancé le ' . $p['last_relance'] : '🔁 Relancer';
							?>
							<button type="button" class="button button-small ag-relance" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" style="<?php echo $relance_due ? 'border-color:#c2410c;color:#c2410c;font-weight:700;' : ''; ?>" title="Marque ce prospect comme relancé aujourd'hui (date + suivi)"><?php echo esc_html( $rlabel ); ?></button>
							<?php $ag_row_mob = ag_phone_is_mobile( $p['phone'] ?? '', $p['phone_intl'] ?? '' ); ?>
							<?php if ( $wa && $ag_row_mob ) : ?><a class="button button-small ag-touch" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" data-channel="WhatsApp" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener">WhatsApp</a> <?php endif; ?>
							<?php if ( $sms && $ag_row_mob ) : ?><a class="button button-small ag-touch" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" data-channel="SMS" href="<?php echo esc_attr( $sms ); ?>" title="Ouvre Messages sur ton ordi (lié à ton tél) -> envoi depuis ton numéro">📱 SMS</a> <?php endif; ?>
							<?php if ( ! $ag_row_mob && ! empty( $p['phone'] ) ) : ?><span class="dashicons dashicons-phone" style="color:#7c3aed;font-size:16px;width:16px;height:16px;vertical-align:text-bottom;" title="Numéro fixe : pas de SMS → utilise le Robot vocal"></span><span style="font-size:.78em;color:#7c3aed;">fixe → Robot</span> <?php endif; ?>
							<?php if ( $mailto ) : ?><a class="button button-small ag-touch" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" data-channel="Email" href="<?php echo esc_url( $mailto ); ?>">Email</a> <?php endif; ?>
							<details style="display:inline-block;margin-top:4px;"><summary class="button button-small">✍️ Message (éditable)</summary>
								<textarea class="ag-msg-field" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" rows="10" style="width:360px;margin-top:6px;"><?php echo esc_textarea( $msg ); ?></textarea><br>
								<button type="button" class="button button-small button-primary ag-msg-save" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>">💾 Enregistrer mon message</button>
								<span class="ag-msg-ok" style="color:#1e7e34;display:none;">✓ enregistré</span>
							</details>
							<?php if ( ! empty( $p['website'] ) ) : ?><a class="button button-small" href="<?php echo esc_url( $p['website'] ); ?>" target="_blank" rel="noopener">🔗 Voir le site</a> <?php endif; ?>
							<?php $pg = ag_google_link( $p ); if ( $pg ) : ?><a class="button button-small" href="<?php echo esc_url( $pg ); ?>" target="_blank" rel="noopener" title="Fiche Google + avis">📍 Avis Google</a> <?php endif; ?>
							<?php echo ag_prospect_links_block( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<details style="display:block;margin-top:6px;"><summary class="button button-small">📝 Ma fiche (notes)</summary>
								<textarea class="ag-note-field" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>" rows="4" style="width:360px;margin-top:6px;" placeholder="Ce que je peux faire pour eux, points à dire, idées…"><?php echo esc_textarea( $p['notes'] ?? '' ); ?></textarea><br>
								<button type="button" class="button button-small button-primary ag-note-save" data-id="<?php echo esc_attr( $p['id'] ?? '' ); ?>">💾 Enregistrer + régénérer le message</button>
								<span class="ag-note-ok" style="color:#1e7e34;display:none;">✓ enregistré, message mis à jour</span>
							</details>
							<?php endif; ?>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;" onsubmit="return confirm('Supprimer ce prospect ?');">
								<input type="hidden" name="action" value="ag_prospect_update">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="id" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>">
								<input type="hidden" name="delete" value="1">
								<button class="button button-small button-link-delete">✕</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>

			<!-- Prospects entrants (chat) -->
			<h2 style="margin-top:26px;">💬 Prospects entrants (chat du site) (<?php echo count( $leads ); ?>)</h2>
			<?php if ( empty( $leads ) ) : ?>
				<p>Aucun pour l'instant. Ils arrivent dès qu'un visiteur laisse ses coordonnées dans le chat.</p>
			<?php else : ?>
				<div class="agld-bulkbar" style="margin:8px 0 4px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
					<label style="font-size:.9em;"><input type="checkbox" id="agld-check-all"> Tout sélectionner</label>
					<button type="button" id="agld-del-selected" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="agld-sel-count">0</span>)</button>
				</div>
				<table class="widefat striped"><thead><tr><th style="width:28px;"><input type="checkbox" id="agld-check-all2"></th><th>Date</th><th>Nom</th><th>Email</th><th>Tél</th><th>Intérêt</th><th>Message</th></tr></thead><tbody>
				<?php foreach ( $leads as $l ) : ?>
					<tr><td style="text-align:center;"><input type="checkbox" class="agld-check" value="<?php echo esc_attr( ag_lead_sig( $l ) ); ?>"></td><td><?php echo esc_html( $l['date'] ?? '' ); ?></td><td><?php echo esc_html( $l['name'] ?? '' ); ?></td>
					<td><?php echo ! empty( $l['email'] ) ? '<a href="mailto:' . esc_attr( $l['email'] ) . '">' . esc_html( $l['email'] ) . '</a>' : ''; ?></td>
					<td><?php echo ! empty( $l['phone'] ) ? '<a href="tel:' . esc_attr( $l['phone'] ) . '">' . esc_html( $l['phone'] ) . '</a>' : ''; ?></td>
					<td><?php echo esc_html( $l['interest'] ?? '' ); ?></td><td><?php echo esc_html( $l['message'] ?? '' ); ?></td></tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>
		</div>
		<script>
		(function(){
			var nonce=<?php echo wp_json_encode( $nonce ); ?>;
			// Helper générique : sélection multiple + suppression en masse pour une liste.
			// prefix = base des id HTML (ex. 'aghist'), action = AJAX, checkClass = classe des cases.
			function agBulk(prefix, action, checkClass){
				var all=document.getElementById(prefix+'-check-all'), all2=document.getElementById(prefix+'-check-all2');
				var del=document.getElementById(prefix+'-del-selected'), cnt=document.getElementById(prefix+'-sel-count');
				function items(){ return Array.prototype.slice.call(document.querySelectorAll('.'+checkClass)); }
				function ref(){ var n=items().filter(function(c){return c.checked;}).length; if(cnt)cnt.textContent=n; if(del)del.disabled=(n===0); }
				function setAll(v){ items().forEach(function(c){ c.checked=v; }); if(all)all.checked=v; if(all2)all2.checked=v; ref(); }
				if(all) all.addEventListener('change',function(){ setAll(all.checked); });
				if(all2) all2.addEventListener('change',function(){ setAll(all2.checked); });
				items().forEach(function(c){ c.addEventListener('change',ref); });
				if(del) del.addEventListener('click',function(){
					var ids=items().filter(function(c){return c.checked;}).map(function(c){return c.value;});
					if(!ids.length) return;
					if(!confirm('Supprimer définitivement '+ids.length+' élément(s) ?')) return;
					var fd=new FormData(); fd.append('action',action); fd.append('_n',nonce);
					ids.forEach(function(id){ fd.append('ids[]',id); });
					del.disabled=true; del.textContent='…';
					fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.success){ location.reload(); } else { del.disabled=false; alert('Suppression impossible.'); } }).catch(function(){ del.disabled=false; });
				});
			}
			agBulk('aghist','ag_search_history_delete_bulk','aghist-check');
			agBulk('agld','ag_leads_delete_bulk','agld-check');
			// Raccourcis métiers : remplit la recherche et lance.
			document.querySelectorAll('.ag-chip').forEach(function(b){ b.addEventListener('click',function(){ var i=document.getElementById('ag-q'); if(i){ i.value=b.getAttribute('data-q'); document.getElementById('ag-search-form').submit(); } }); });
			// Filtre "sans vrai site".
			var only=document.getElementById('ag-onlyno'); if(only){ only.addEventListener('change',function(){ document.querySelectorAll('#ag-results tbody tr').forEach(function(tr){ tr.style.display=(only.checked && tr.getAttribute('data-kind')==='real')?'none':''; }); }); }
			// Ajout en AJAX (sans recharger -> la recherche reste).
			document.querySelectorAll('.ag-add').forEach(function(b){ b.addEventListener('click',function(){
				var fd=new FormData(); fd.append('action','ag_prospect_add'); fd.append('_n',nonce);
				['name','type','city','phone','website','address','rating','reviews','notes','source','maps','bodacc'].forEach(function(k){ fd.append(k, b.getAttribute('data-'+k)||''); });
					var nt=b.closest('td')?b.closest('td').querySelector('.ag-add-note'):null; if(nt&&nt.value){ fd.set('notes', nt.value); }
				b.disabled=true; b.textContent='…';
				fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.success){ b.textContent='✓ Ajouté'+((j.data&&j.data.audit!=null)?(' · 🛡️ '+j.data.audit+'/100'):''); } else { b.textContent='Erreur'; } }).catch(function(){ b.textContent='Erreur'; b.disabled=false; });
			}); });
			// 🚫 Ignorer : ne plus réafficher cette entreprise dans les recherches.
			document.querySelectorAll('.ag-ignore').forEach(function(b){ b.addEventListener('click',function(){
				var fd=new FormData(); fd.append('action','ag_prospect_ignore'); fd.append('_n',nonce);
				['name','city','type','phone','website','address','rating','reviews'].forEach(function(k){ fd.append(k, b.getAttribute('data-'+k)||''); });
				b.disabled=true; b.textContent='…';
				fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ var tr=b.closest('tr'); if(j&&j.success&&tr){ tr.style.opacity='.4'; tr.style.display='none'; } else { b.textContent='Erreur'; b.disabled=false; } }).catch(function(){ b.textContent='Erreur'; b.disabled=false; });
			}); });
			// ☑️ Sélection multiple + suppression en lot.
			(function(){
				var all=document.getElementById('ag-check-all'), all2=document.getElementById('ag-check-all2');
				var delBtn=document.getElementById('ag-del-selected'), cntEl=document.getElementById('ag-sel-count');
				var smsBtn=document.getElementById('ag-sms-selected'), gwReady=<?php echo ( function_exists( 'ag_sms_gateway_ready' ) && ag_sms_gateway_ready() ) ? 'true' : 'false'; ?>;
				var voiceBtn=document.getElementById('ag-voice-selected'), voiceReady=<?php echo ( function_exists( 'ag_voice_ready' ) && ag_voice_ready() ) ? 'true' : 'false'; ?>;
				function checks(){ return Array.prototype.slice.call(document.querySelectorAll('.ag-check')); }
				function refresh(){ var n=checks().filter(function(c){return c.checked;}).length; if(cntEl)cntEl.textContent=n; if(delBtn)delBtn.disabled=(n===0); if(smsBtn)smsBtn.disabled=(n===0||!gwReady); if(voiceBtn)voiceBtn.disabled=(n===0||!voiceReady); }
				if(smsBtn) smsBtn.addEventListener('click',function(){
					var ids=checks().filter(function(c){return c.checked;}).map(function(c){return c.value;});
					if(!ids.length) return;
					if(!confirm('Envoyer un SMS aux '+ids.length+' prospect(s) sélectionné(s) ? (message personnalisé par fiche)')) return;
					var fd=new FormData(); fd.append('action','ag_prospect_sms_bulk'); fd.append('_n',nonce);
					ids.forEach(function(id){ fd.append('ids[]',id); });
					smsBtn.disabled=true; smsBtn.textContent='…';
					fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ smsBtn.textContent='📲 Envoyer SMS (sélection)'; smsBtn.disabled=false; alert(j&&j.success?('SMS envoyés : '+j.data.ok+' / échecs : '+j.data.ko):'Erreur'); }).catch(function(){ smsBtn.disabled=false; });
				});
				function setAll(v){ checks().forEach(function(c){ c.checked = v && rowVisible(c); }); if(all)all.checked=v; if(all2)all2.checked=v; refresh(); }
				if(all) all.addEventListener('change',function(){ setAll(all.checked); });
				if(all2) all2.addEventListener('change',function(){ setAll(all2.checked); });
				checks().forEach(function(c){ c.addEventListener('change',refresh); });
				function rowVisible(c){ var tr=c.closest('tr'); return tr && tr.style.display!=='none'; }
				function pickBy(mob){ checks().forEach(function(c){ c.checked=(rowVisible(c) && c.getAttribute('data-mob')===(mob?'1':'0')); }); if(all)all.checked=false; if(all2)all2.checked=false; refresh(); }
				var pm=document.getElementById('ag-pick-mob'); if(pm) pm.addEventListener('click',function(){ pickBy(true); });
				var pf=document.getElementById('ag-pick-fix'); if(pf) pf.addEventListener('click',function(){ pickBy(false); });
				document.addEventListener('click',function(e){ var b=e.target.closest?e.target.closest('.agr-robot'):null; if(!b) return; var id=b.getAttribute('data-id'); if(!id) return; var ang=b.getAttribute('data-angle')||'auto'; if(!confirm('Lancer un appel du robot vocal ('+(ang==='securite'?'sécurité':'création de site')+') vers ce prospect ?')) return; var fd=new FormData(); fd.append('action','ag_prospect_voice_bulk'); fd.append('_n',nonce); fd.append('angle',ang); fd.append('ids[]',id); var old=b.textContent; b.disabled=true; b.textContent='…'; fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ b.disabled=false; b.textContent=old; alert(j&&j.success?('Appel lance : '+j.data.ok+' / echec : '+j.data.ko):(j&&j.data&&j.data.msg?j.data.msg:'Erreur')); }).catch(function(){ b.disabled=false; b.textContent=old; }); });
				var curSite='all', curMob='all';
				function applyFilters(){
					document.querySelectorAll('tr.ag-prow').forEach(function(tr){ var okS=(curSite==='all'||tr.getAttribute('data-site')===curSite); var okM=(curMob==='all'||tr.getAttribute('data-mob')===curMob); tr.style.display=(okS&&okM)?'':'none'; });
					checks().forEach(function(c){ if(!rowVisible(c)) c.checked=false; }); if(all)all.checked=false; if(all2)all2.checked=false; refresh();
				}
				document.querySelectorAll('.ag-fltr').forEach(function(b){ b.addEventListener('click',function(){ curSite=b.getAttribute('data-f'); document.querySelectorAll('.ag-fltr').forEach(function(x){ x.classList.toggle('button-primary', x===b); }); applyFilters(); }); });
				document.querySelectorAll('.ag-fltrm').forEach(function(b){ b.addEventListener('click',function(){ curMob=b.getAttribute('data-m'); document.querySelectorAll('.ag-fltrm').forEach(function(x){ x.classList.toggle('button-primary', x===b); }); applyFilters(); }); });
				if(voiceBtn) voiceBtn.addEventListener('click',function(){
					var ids=checks().filter(function(c){return c.checked;}).map(function(c){return c.value;});
					if(!ids.length) return;
					if(!confirm('Lancer un appel du robot vocal vers les '+ids.length+' prospect(s) sélectionné(s) ? (Emma adapte création/sécurité selon le site)')) return;
					var fd=new FormData(); fd.append('action','ag_prospect_voice_bulk'); fd.append('_n',nonce);
					var angSel=document.getElementById('ag-voice-angle'); fd.append('angle', angSel?angSel.value:'auto');
					ids.forEach(function(id){ fd.append('ids[]',id); });
					voiceBtn.disabled=true; voiceBtn.textContent='…';
					fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ voiceBtn.textContent='🤖 Robot : appeler la sélection'; voiceBtn.disabled=false; alert(j&&j.success?('Appels lancés : '+j.data.ok+' / échecs : '+j.data.ko):(j&&j.data&&j.data.msg?j.data.msg:'Erreur')); }).catch(function(){ voiceBtn.disabled=false; });
				});
				if(delBtn) delBtn.addEventListener('click',function(){
					var ids=checks().filter(function(c){return c.checked;}).map(function(c){return c.value;});
					if(!ids.length) return;
					if(!confirm('Supprimer définitivement '+ids.length+' prospect(s) ?')) return;
					var fd=new FormData(); fd.append('action','ag_prospect_delete_bulk'); fd.append('_n',nonce);
					ids.forEach(function(id){ fd.append('ids[]',id); });
					delBtn.disabled=true; delBtn.textContent='…';
					fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
						if(j&&j.success){ checks().forEach(function(c){ if(c.checked){ var tr=c.closest('tr'); if(tr) tr.parentNode.removeChild(tr); } }); if(all)all.checked=false; if(all2)all2.checked=false; refresh(); delBtn.innerHTML='🗑️ Supprimer la sélection (<span id="ag-sel-count">0</span>)'; }
						else { delBtn.disabled=false; alert('Suppression impossible.'); }
					}).catch(function(){ delBtn.disabled=false; });
				});
			})();
			// 🔁 Relancer : marque le prospect comme relancé aujourd'hui (date + suivi).
			document.querySelectorAll('.ag-relance').forEach(function(b){ b.addEventListener('click',function(){
				var id=b.getAttribute('data-id'); if(!id) return;
				var fd=new FormData(); fd.append('action','ag_prospect_relance'); fd.append('_n',nonce); fd.append('id',id);
				var old=b.textContent; b.disabled=true; b.textContent='…';
				fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
					if(j&&j.success){ b.textContent='✓ Relancé le '+j.data.date; b.style.borderColor='#1e7e34'; b.style.color='#1e7e34'; b.style.fontWeight='700'; }
					else { b.textContent=old; b.disabled=false; }
				}).catch(function(){ b.textContent=old; b.disabled=false; });
			}); });
			// Clic WhatsApp/Email = enregistre le contact (date + compteur + statut auto). Le lien s'ouvre normalement.
			document.querySelectorAll('.ag-touch').forEach(function(a){ a.addEventListener('click',function(){
				var id=a.getAttribute('data-id'); if(!id) return;
				var ch=a.getAttribute('data-channel')||'';
				var fd=new FormData(); fd.append('action','ag_prospect_touch'); fd.append('_n',nonce); fd.append('id',id); fd.append('channel',ch);
				fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin',keepalive:true}).then(function(r){return r.json();}).then(function(j){
					if(j&&j.success){ var d=a.closest('td').querySelector('.ag-suivi'); if(d){ var par=j.data.channel?(' par <strong>'+j.data.channel+'</strong>'):''; d.style.color='#50575e'; d.innerHTML='📨 Contacté'+par+' le <strong>'+j.data.date+'</strong> (×'+j.data.count+')<br><button type="button" class="button button-small ag-reply" data-id="'+id+'">A répondu ?</button>'; bindReply(d.querySelector('.ag-reply')); } }
				}).catch(function(){});
			}); });
			// Marquer "a répondu".
			function bindReply(btn){ if(!btn) return; btn.addEventListener('click',function(){
				var id=btn.getAttribute('data-id'); var fd=new FormData(); fd.append('action','ag_prospect_reply'); fd.append('_n',nonce); fd.append('id',id); fd.append('replied','1');
				btn.disabled=true; fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.success){ btn.outerHTML='<span style="color:#1e7e34;font-weight:700;">✅ A répondu le '+j.data.date+'</span>'; } else { btn.disabled=false; } }).catch(function(){ btn.disabled=false; });
			}); }
			document.querySelectorAll('.ag-reply').forEach(bindReply);
			// Résultat du contact en 1 clic (bloqué / sans réponse).
			document.querySelectorAll('.ag-outcome').forEach(function(btn){ btn.addEventListener('click',function(){
				var id=btn.getAttribute('data-id'); var out=btn.getAttribute('data-outcome');
				var fd=new FormData(); fd.append('action','ag_prospect_outcome'); fd.append('_n',nonce); fd.append('id',id); fd.append('outcome',out);
				btn.disabled=true; fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.success){ location.reload(); } else { btn.disabled=false; } }).catch(function(){ btn.disabled=false; });
			}); });
			// Enregistrer une note (ma fiche) sur un prospect existant.
			document.querySelectorAll('.ag-note-save').forEach(function(btn){ btn.addEventListener('click',function(){
				var id=btn.getAttribute('data-id'); var box=btn.closest('details'); var ta=box?box.querySelector('.ag-note-field'):null; if(!ta) return;
				var fd=new FormData(); fd.append('action','ag_prospect_note'); fd.append('_n',nonce); fd.append('id',id); fd.append('note',ta.value);
				btn.disabled=true; fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ btn.disabled=false; var ok=box.querySelector('.ag-note-ok'); if(j&&j.success){ if(ok){ ok.style.display='inline'; setTimeout(function(){ok.style.display='none';},2500);} var td=btn.closest('td'); var mf=td?td.querySelector('.ag-msg-field'):null; if(mf&&j.data&&j.data.message){ mf.value=j.data.message; } } }).catch(function(){ btn.disabled=false; });
			}); });
			// Enregistrer le message édité à la main.
			document.querySelectorAll('.ag-msg-save').forEach(function(btn){ btn.addEventListener('click',function(){
				var id=btn.getAttribute('data-id'); var box=btn.closest('details'); var ta=box?box.querySelector('.ag-msg-field'):null; if(!ta) return;
				var fd=new FormData(); fd.append('action','ag_prospect_msg_save'); fd.append('_n',nonce); fd.append('id',id); fd.append('msg',ta.value);
				btn.disabled=true; fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ btn.disabled=false; var ok=box.querySelector('.ag-msg-ok'); if(ok&&j&&j.success){ ok.style.display='inline'; setTimeout(function(){ok.style.display='none';},2500);} }).catch(function(){ btn.disabled=false; });
			}); });
		})();
		</script>
		<?php
	}
}

/* ── 7. Agent automatique (cron quotidien : recherche planifiée) ─── */
add_action( 'admin_post_ag_autosearch_add', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
	$city = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	if ( $q || $city ) { $s = (array) get_option( 'ag_auto_searches', array() ); $s[] = array( 'q' => $q, 'city' => $city ); update_option( 'ag_auto_searches', $s ); }
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );
add_action( 'admin_post_ag_autosearch_del', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$i = (int) ( $_POST['i'] ?? -1 ); $s = (array) get_option( 'ag_auto_searches', array() );
	if ( isset( $s[ $i ] ) ) { unset( $s[ $i ] ); update_option( 'ag_auto_searches', array_values( $s ) ); }
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );
add_action( 'admin_post_ag_autosearch_run', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	ag_run_auto_prospection();
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects&ran=1' ) ); exit;
} );

add_action( 'init', function () {
	$freq = get_option( 'ag_auto_freq', 'weekly' );
	$next = wp_next_scheduled( 'ag_prospect_cron' );
	if ( 'off' === $freq ) { if ( $next ) wp_unschedule_event( $next, 'ag_prospect_cron' ); return; }
	if ( ! $next ) wp_schedule_event( time() + 600, ( 'daily' === $freq ? 'daily' : 'weekly' ), 'ag_prospect_cron' );
} );
add_action( 'admin_post_ag_auto_settings', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	update_option( 'ag_places_cap', max( 0, (int) ( $_POST['cap'] ?? 0 ) ) );
	$freq = in_array( $_POST['freq'] ?? '', array( 'daily', 'weekly', 'off' ), true ) ? $_POST['freq'] : 'weekly';
	update_option( 'ag_auto_freq', $freq );
	$next = wp_next_scheduled( 'ag_prospect_cron' ); if ( $next ) wp_unschedule_event( $next, 'ag_prospect_cron' );
	if ( 'off' !== $freq ) wp_schedule_event( time() + 600, ( 'daily' === $freq ? 'daily' : 'weekly' ), 'ag_prospect_cron' );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );
add_action( 'ag_prospect_cron', 'ag_run_auto_prospection' );

/* ── Relance auto : rappel quotidien des prospects sans réponse depuis 7j ── */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_relance_cron' ) ) wp_schedule_event( strtotime( 'tomorrow 9:30' ), 'daily', 'ag_relance_cron' );
} );
add_action( 'ag_relance_cron', function () {
	$due = 0;
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) { if ( ag_prospect_relance_due( $p ) ) $due++; }
	if ( $due > 0 ) {
		if ( function_exists( 'ag_push' ) ) ag_push( '🔁 ' . $due . ' prospect(s) à relancer', 'Sans réponse depuis 7 jours et plus. Va dans Prospection → puce « 🔁 À relancer ».' );
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🔁 ' . $due . ' prospect(s) à relancer (7 jours sans réponse)' );
	}
} );
if ( ! function_exists( 'ag_run_auto_prospection' ) ) {
	function ag_run_auto_prospection() {
		$searches = (array) get_option( 'ag_auto_searches', array() );
		if ( empty( $searches ) || '' === ag_places_key() ) return;
		$cap = (int) get_option( 'ag_places_cap', 1000 );
		$ck  = 'ag_places_calls_' . gmdate( 'Ym' );
		if ( $cap > 0 && (int) get_option( $ck, 0 ) >= $cap ) { update_option( 'ag_prospect_lastrun', array( 'ts' => time(), 'added' => 0, 'capped' => 1 ) ); return; }
		$added = 0; $nosite = 0;
		// Compte les nouveaux prospects par ambassadeur (pour les prévenir).
		$by_owner = array();
		$collector = function ( $rec ) use ( &$by_owner ) {
			$oe = strtolower( $rec['owner_email'] ?? '' );
			if ( $oe ) $by_owner[ $oe ] = ( $by_owner[ $oe ] ?? 0 ) + 1;
		};
		add_action( 'ag_prospect_added', $collector );
		foreach ( $searches as $s ) {
			if ( $cap > 0 && (int) get_option( $ck, 0 ) >= $cap ) break; // plafond atteint en cours de route
			$res = ( '' === trim( $s['q'] ?? '' ) && ! empty( $s['city'] ) ) ? ag_places_sweep( $s['city'] ) : ag_places_search( trim( ( $s['q'] ?? '' ) . ' ' . ( $s['city'] ?? '' ) ) );
			if ( ! is_array( $res ) || isset( $res['error'] ) ) continue;
			foreach ( $res as $r ) {
				if ( empty( $r['name'] ) ) continue;
				$ok = ag_prospect_add_record( array(
					'name' => $r['name'], 'type' => $r['type'] ?? ( $s['q'] ?? '' ), 'city' => $s['city'] ?? '',
					'phone' => $r['phone'] ?? '', 'phone_intl' => $r['phone_intl'] ?? '', 'website' => $r['website'] ?? '', 'address' => $r['address'] ?? '', 'maps_uri' => $r['maps_uri'] ?? '',
					'rating' => $r['rating'] ?? 0, 'reviews' => $r['reviews'] ?? 0, 'source' => 'robot',
				) );
				if ( $ok ) { $added++; if ( 'real' !== ag_site_kind( $r['website'] ?? '' )[0] ) $nosite++; }
			}
		}
		remove_action( 'ag_prospect_added', $collector );
		update_option( 'ag_prospect_lastrun', array( 'ts' => time(), 'added' => $added ) );
		if ( $added ) ag_activity_log( '🤖 Robot : ' . (int) $added . ' nouveau(x) prospect(s) trouvé(s)' );

		// Notifie chaque ambassadeur des nouveaux prospects de SA zone (auto, mains-libres).
		$tg_lines = '';
		foreach ( $by_owner as $oe => $cnt ) {
			$u = get_user_by( 'email', $oe );
			$nm = $u ? ( $u->display_name ?: $oe ) : $oe;
			if ( $u ) {
				wp_mail(
					$oe,
					"🎯 $cnt nouveau(x) prospect(s) à contacter dans ta zone",
					"Salut $nm,\n\nLe robot vient de déposer $cnt nouveau(x) prospect(s) dans ta zone. Ils t'attendent dans ton espace.\n\n👉 " . home_url( '/espace-ambassadeur#prospects' ) . "\n\nPlus tu contactes vite, plus tu vends. 💪\nAlliance Groupe"
				);
			}
			$tg_lines .= '• ' . $nm . ' : ' . $cnt . "\n";
		}
		if ( $tg_lines && function_exists( 'ag_push' ) ) {
			ag_push( '🎯 Prospects répartis aux ambassadeurs', "Le robot a assigné de nouveaux prospects :\n" . $tg_lines );
		}
		if ( $added ) {
			$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
			wp_mail( $to, "🤖 $added nouveaux prospects trouvés (dont $nosite sans vrai site)", "L'agent automatique a ajouté $added entreprises a ta liste ($nosite sans vrai site web — prioritaires).\n\nVa les prospecter : " . admin_url( 'admin.php?page=ag-prospects' ) );
		}
	}
}

/* ── 8. Côté ambassadeur : ses prospects assignés (liste partagée) ─ */
if ( ! function_exists( 'ag_prospects_for_owner' ) ) {
	function ag_prospects_for_owner( $email ) {
		$out = array(); $email = strtolower( (string) $email );
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( strtolower( $p['owner_email'] ?? '' ) !== $email ) continue;
			// Exclut les prospects bloqués (refus / ne_pas_contacter / client / ignore) :
			// si l'entreprise a demandé à ne plus être contactée, elle disparaît de la
			// file de l'ambassadeur immédiatement.
			if ( ag_prospect_blocked( $p['status'] ?? '' ) ) continue;
			$out[] = $p;
		}
		usort( $out, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
		return $out;
	}
}
add_action( 'admin_post_ag_amb_prospect_status', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_die( 'no' );
	$email = strtolower( wp_get_current_user()->user_email );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$st = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	$pname = '';
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id && strtolower( $p['owner_email'] ?? '' ) === $email ) { $pname = $p['name'] ?? ''; $list[ $k ]['status'] = $st; break; }
	}
	update_option( 'ag_prospects', array_values( $list ) );
	if ( in_array( $st, array( 'interesse', 'client' ), true ) && function_exists( 'ag_push' ) ) {
		ag_push( ( 'client' === $st ? '✅ Vente : ' : '🔥 Intéressé : ' ) . $pname, 'Mis à jour par ' . ( wp_get_current_user()->display_name ?: $email ) . '.' );
	}
	$ref = wp_get_referer(); wp_safe_redirect( $ref ? $ref : home_url( '/espace-ambassadeur#prospects' ) ); exit;
} );

/* Ambassadeur : enregistre un contact (AJAX, sur SES prospects uniquement). */
add_action( 'wp_ajax_ag_amb_touch', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$email = strtolower( wp_get_current_user()->user_email );
	$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$ch    = sanitize_text_field( wp_unslash( $_POST['channel'] ?? '' ) );
	$list  = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id && ( current_user_can( 'manage_options' ) || strtolower( $p['owner_email'] ?? '' ) === $email ) ) {
			$now = current_time( 'd/m/Y' );
			$cnt = (int) ( $p['contact_count'] ?? 0 ) + 1;
			$list[ $k ]['contact_count'] = $cnt;
			$list[ $k ]['last_contact']  = $now;
			$list[ $k ]['last_contact_ts'] = time();
			if ( $ch ) $list[ $k ]['last_channel'] = $ch;
			if ( empty( $p['date_contact'] ) ) $list[ $k ]['date_contact'] = $now;
			$cur = $p['status'] ?? 'nouveau';
			if ( 'nouveau' === $cur ) $list[ $k ]['status'] = 'contacte';
			elseif ( in_array( $cur, array( 'contacte', 'sans_reponse' ), true ) ) $list[ $k ]['status'] = 'relance';
			update_option( 'ag_prospects', array_values( $list ) );
			wp_send_json_success( array( 'count' => $cnt, 'date' => $now, 'channel' => $ch ) );
		}
	}
	wp_send_json_error();
} );

/* Ambassadeur : marque "a répondu" (AJAX, sur SES prospects uniquement). */
add_action( 'wp_ajax_ag_amb_reply', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$email = strtolower( wp_get_current_user()->user_email );
	$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$list  = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id && ( current_user_can( 'manage_options' ) || strtolower( $p['owner_email'] ?? '' ) === $email ) ) {
			$list[ $k ]['replied']    = 1;
			$list[ $k ]['date_reply'] = current_time( 'd/m/Y' );
			update_option( 'ag_prospects', array_values( $list ) );
			wp_send_json_success( array( 'date' => $list[ $k ]['date_reply'] ) );
		}
	}
	wp_send_json_error();
} );

/* ── Chasseur Pro (abonnement) : recherche à la demande dans SA zone ── */
if ( ! function_exists( 'ag_is_chasseur' ) ) {
	function ag_is_chasseur( $uid = 0 ) {
		$uid = $uid ?: get_current_user_id();
		if ( ! $uid ) return false;
		if ( user_can( $uid, 'manage_options' ) ) return true;
		return (int) get_user_meta( $uid, 'ag_chasseur_until', true ) > time();
	}
}
if ( ! function_exists( 'ag_chasseur_activate_by_email' ) ) {
	/** Active/prolonge Chasseur Pro pour l'utilisateur correspondant à l'email (paiement ~19 €). */
	function ag_chasseur_activate_by_email( $email, $amount ) {
		$price = (float) get_option( 'ag_chasseur_price', 19 );
		if ( $price > 0 && abs( (float) $amount - $price ) > 1.5 ) return false;
		$user = get_user_by( 'email', trim( (string) $email ) );
		if ( ! $user ) return false;
		$base = max( time(), (int) get_user_meta( $user->ID, 'ag_chasseur_until', true ) );
		update_user_meta( $user->ID, 'ag_chasseur_until', $base + 35 * DAY_IN_SECONDS );
		if ( function_exists( 'ag_push' ) ) ag_push( '💎 Chasseur Pro activé', ( $user->display_name ?: $email ) . ' a payé l\'abonnement — recherche débloquée automatiquement.' );
		return true;
	}
}
if ( ! function_exists( 'ag_chasseur_quota_left' ) ) {
	function ag_chasseur_quota_left( $uid = 0 ) {
		$uid = $uid ?: get_current_user_id();
		if ( user_can( $uid, 'manage_options' ) ) return 9999;
		$cap = (int) apply_filters( 'ag_chasseur_quota', 300 );
		return max( 0, $cap - (int) get_user_meta( $uid, 'ag_chasseur_n_' . gmdate( 'Ym' ), true ) );
	}
}
add_action( 'wp_ajax_ag_amb_search', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Session expirée.' ) );
	$uid = get_current_user_id();
	if ( ! ag_is_chasseur( $uid ) ) wp_send_json_error( array( 'm' => 'Abonnement Chasseur Pro requis.' ) );
	if ( ag_chasseur_quota_left( $uid ) <= 0 ) wp_send_json_error( array( 'm' => 'Quota du mois atteint.' ) );
	$email   = strtolower( wp_get_current_user()->user_email );
	$myzones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $email ) : array();
	$admin   = user_can( $uid, 'manage_options' );
	if ( ! $admin && empty( $myzones ) ) wp_send_json_error( array( 'm' => "Prends d'abord ta zone." ) );
	$city = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	if ( '' === $city ) wp_send_json_error( array( 'm' => 'Indique une ville de ta zone.' ) );
	$res = ag_places_search( $city );
	if ( ! $admin ) update_user_meta( $uid, 'ag_chasseur_n_' . gmdate( 'Ym' ), (int) get_user_meta( $uid, 'ag_chasseur_n_' . gmdate( 'Ym' ), true ) + 1 );
	if ( ! is_array( $res ) || isset( $res['error'] ) ) wp_send_json_error( array( 'm' => ( $res['error'] ?? 'Erreur recherche.' ) ) );
	$out = array();
	foreach ( $res as $r ) {
		if ( empty( $r['name'] ) ) continue;
		$d = function_exists( 'ag_prospect_dept' ) ? ag_prospect_dept( $r ) : '';
		if ( ! $admin && $myzones && $d && ! in_array( $d, $myzones, true ) ) continue; // hors de sa zone
		if ( ag_prospect_is_ignored( $r['name'], $r['city'] ?? $city ) ) continue; // 🚫 ignoré
		$ex = ag_prospect_find( $r['name'], $r['city'] ?? $city, $r['phone'] ?? '' );
		if ( $ex && ag_prospect_blocked( $ex['status'] ?? '' ) ) continue;
		$kind = ag_site_kind( $r['website'] ?? '' );
		$out[] = array(
			'name' => $r['name'], 'type' => $r['type'] ?? '', 'city' => $r['city'] ?? $city, 'phone' => $r['phone'] ?? '', 'phone_intl' => $r['phone_intl'] ?? '',
			'website' => $r['website'] ?? '', 'address' => $r['address'] ?? '', 'rating' => $r['rating'] ?? 0, 'reviews' => $r['reviews'] ?? 0,
			'kind' => $kind[1], 'real' => ( 'real' === $kind[0] ), 'score' => ag_prospect_score( $r ), 'exists' => (bool) $ex,
		);
	}
	usort( $out, function ( $a, $b ) { return $b['score'] <=> $a['score']; } );
	wp_send_json_success( array( 'items' => $out, 'left' => ag_chasseur_quota_left( $uid ) ) );
} );
add_action( 'wp_ajax_ag_amb_add', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$uid   = get_current_user_id();
	$email = strtolower( wp_get_current_user()->user_email );
	$name  = wp_get_current_user()->display_name ?: $email;
	$myzones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $email ) : array();
	$data = array(
		'name' => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'type' => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
		'city' => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'phone' => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'phone_intl' => sanitize_text_field( wp_unslash( $_POST['phone_intl'] ?? '' ) ),
		'website' => esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) ),
		'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
		'rating' => (float) ( $_POST['rating'] ?? 0 ), 'reviews' => (int) ( $_POST['reviews'] ?? 0 ),
		'owner_email' => $email, 'owner_name' => $name, 'source' => 'ambassadeur',
	);
	$d = function_exists( 'ag_prospect_dept' ) ? ag_prospect_dept( $data ) : '';
	if ( ! user_can( $uid, 'manage_options' ) && $myzones && $d && ! in_array( $d, $myzones, true ) ) wp_send_json_error( array( 'm' => 'Hors de ta zone.' ) );
	wp_send_json_success( array( 'added' => ag_prospect_add_record( $data ) ) );
} );

/* RAPPORT CLIENT (teaser) — rendu autonome via ?ag_rapport=1 (aucune page WP requise → jamais de 404). */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['ag_rapport'] ) ) { return; }
	$site = isset( $_GET['site'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['site'] ) ) ) : '';
	$name = isset( $_GET['name'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['name'] ) ) ) : '';
	ag_render_client_report( $site, $name );
	exit;
} );
if ( ! function_exists( 'ag_render_client_report' ) ) {
	/* Traduit les libellés techniques d'audit en clair pour le client (fini « Balise <title> »). */
	function ag_report_label( $name ) {
		$name = (string) $name;
		$map = array(
			'Balise <title>'              => 'Titre de la page (référencement Google)',
			'Meta description'            => 'Description Google (le résumé sous le titre)',
			'Balise H1'                   => 'Titre principal de la page',
			'Images avec attribut alt'    => 'Descriptions des images (SEO + accessibilité)',
			'Connexion sécurisée (HTTPS)' => 'Connexion sécurisée (cadenas HTTPS)',
			'Site accessible'             => 'Accès au site',
		);
		if ( isset( $map[ $name ] ) ) return $map[ $name ];
		$n = preg_replace( '/<[^>]+>/', '', $name );          // retire les <...> techniques
		$n = trim( str_ireplace( 'Balise', 'Élément', $n ) ); // « Balise » → « Élément »
		return '' !== $n ? $n : $name;
	}
	function ag_render_client_report( $site, $name = '' ) {
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		$ok = ( '' !== $site && preg_match( '#^https?://#i', $site ) && function_exists( 'ag_audit_run' ) );
		$a = $ok ? ag_audit_get( $site ) : array();
		$score  = (int) ( $a['score'] ?? 0 );
		$tech   = (string) ( $a['tech'] ?? '' );
		$fails  = array();
		foreach ( (array) ( $a['checks'] ?? array() ) as $c ) {
			if ( in_array( $c['status'] ?? '', array( 'fail', 'warn' ), true ) ) { $fails[] = $c; }
		}
		$vis   = array_slice( $fails, 0, 2 );
		$hid   = array_slice( $fails, 2 );
		// Cas « site non analysable » (fetch échoué) : score 0, ou seul « Accès au site » en échec.
		$fetch_failed = $ok && ( 0 === $score || ( 1 === count( (array) ( $a['checks'] ?? array() ) ) && ! empty( $a['checks'] ) && false !== stripos( (string) ( $a['checks'][0]['name'] ?? '' ), 'accessible' ) ) );
		$no_issues    = $ok && ! $fetch_failed && 0 === count( $fails );
		$kali_all = get_option( 'ag_kali', array() );
		$kali  = ( is_array( $kali_all ) ) ? trim( (string) ( $kali_all[ md5( strtolower( (string) $site ) ) ] ?? '' ) ) : '';
		$kali_n = $kali ? max( 1, substr_count( $kali, "\n" ) + 1 ) : 0;
		$host  = $ok ? wp_parse_url( $site, PHP_URL_HOST ) : '';
		$who   = '' !== $name ? $name : $host;
		$col   = $score < 50 ? '#e5484d' : ( $score < 75 ? '#e6a817' : '#2ecc71' );
		$shot  = 'https://s.wordpress.com/mshots/v1/' . rawurlencode( (string) $site ) . '?w=680';
		$price = function_exists( 'ag_tester_opt' ) ? (float) ag_tester_opt( 'price' ) : 49;
		$payurl = function_exists( 'ag_tester_opt' ) ? trim( (string) ag_tester_opt( 'pay_url' ) ) : '';
		$token    = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
		$unlocked = function_exists( 'ag_rapport_is_unlocked' ) ? ag_rapport_is_unlocked( $site, $token ) : false;
		$cta   = '' !== $payurl ? $payurl : home_url( '/audit-securite?site=' . rawurlencode( (string) $site ) );
		$ctalabel = '🔓 Débloquer mon rapport complet' . ( $price > 0 ? ' — ' . number_format_i18n( $price, 0 ) . ' €' : '' );
		$logo  = get_stylesheet_directory_uri() . '/assets/images/logo-carte-square.jpg';

		// MODE CARTE (?card=1) : tout sur UN écran, à screenshoter et envoyer en image (MMS).
		if ( isset( $_GET['card'] ) && $ok ) {
			?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Audit <?php echo esc_html( $who ); ?></title>
			<style>
				*{box-sizing:border-box;margin:0}body{background:#0b0b0f;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
				.card{max-width:430px;margin:0 auto;padding:16px 16px 20px;}
				.hd{display:flex;align-items:center;gap:8px;margin-bottom:8px;}.hd img{width:26px;height:26px;border-radius:6px}.hd b{font-size:.92rem}.hd b span{color:#d4b45c}
				.tag{float:right;background:#e5484d;color:#fff;border-radius:100px;padding:2px 9px;font-size:.62rem;font-weight:800;letter-spacing:.5px}
				h1{font-size:1.05rem;margin:2px 0 1px}.u{color:#8a8a92;font-size:.72rem;word-break:break-all;margin-bottom:8px}
				.row{display:flex;align-items:center;gap:12px;background:#14141a;border:1px solid #26262f;border-radius:12px;padding:11px 13px;margin-bottom:9px}
				.big{font-size:2.3rem;font-weight:800;line-height:1;color:<?php echo esc_attr( $col ); ?>}.big small{font-size:.85rem;color:#8a8a92}
				.rl{font-size:.82rem;color:#cfcfd6;line-height:1.35}
				.fl{background:#1b1113;border-left:3px solid #e5484d;border-radius:8px;padding:6px 10px;margin-bottom:5px;font-size:.8rem}
				.lock{background:#14141a;border:1px dashed #4a4a55;border-radius:9px;padding:8px 10px;margin:5px 0 9px;text-align:center;font-size:.8rem;color:#c9a24a;font-weight:700}
				.warn{background:#241f0e;border:1px solid #6a5a1f;border-radius:10px;padding:10px 12px;color:#e6d9a8;font-size:.78rem;line-height:1.45}
				.warn b{color:#ffd873}.warn div{margin-top:4px}
				.ft{margin-top:10px;text-align:center;background:linear-gradient(135deg,#d4b45c,#b98f2f);color:#0b0b0f;font-weight:800;border-radius:11px;padding:11px;font-size:.9rem}
			</style></head><body>
			<div style="max-width:430px;margin:0 auto;padding:8px 16px 0;"><a href="#" onclick="if(history.length>1){history.back();return false}location.href='<?php echo esc_url( home_url( '/prospection-mobile' ) ); ?>';return false" style="color:#d4b45c;font-weight:700;text-decoration:none;font-size:.9rem;">← Retour à l'app</a> <span style="color:#666;font-size:.72rem;">(ne pas inclure dans la capture)</span></div>
			<div class="card">
				<div class="hd"><img src="<?php echo esc_url( $logo ); ?>" alt=""><b>Alliance <span>Groupe</span></b><span class="tag">AUDIT SÉCURITÉ</span></div>
				<h1><?php echo esc_html( $who ); ?></h1><div class="u"><?php echo esc_html( $host ); ?></div>
				<div class="row"><div class="big"><?php echo (int) $score; ?><small>/100</small></div><div class="rl"><strong style="color:<?php echo esc_attr( $col ); ?>"><?php echo $score < 50 ? '⚠️ Site à risque' : 'À améliorer'; ?></strong><br><?php echo count( $fails ); ?> problème(s) détecté(s)<?php echo $tech ? ' · ' . esc_html( $tech ) : ''; ?></div></div>
				<?php foreach ( array_slice( $fails, 0, 3 ) as $c ) : ?><div class="fl"><?php echo ( 'fail' === $c['status'] ? '❌ ' : '⚠️ ' ) . esc_html( ag_report_label( $c['name'] ?? '' ) ); ?></div><?php endforeach; ?>
				<?php if ( count( $fails ) > 3 ) : ?><div class="lock">🔒 + <?php echo count( $fails ) - 3; ?> autres failles dans le rapport complet</div><?php endif; ?>
				<div class="warn"><b>⚠️ Si ce n'est pas corrigé :</b><div>• Piratage / site défiguré ou bloqué (rançongiciel)</div><div>• Vol des données clients → amende CNIL possible</div><div>• Site signalé « dangereux » → chute sur Google</div></div>
				<div class="ft">Rapport complet + correction<?php echo $price > 0 ? ' — ' . esc_html( number_format_i18n( $price, 0 ) ) . ' €' : ''; ?> · Alliance Groupe</div>
			</div></body></html><?php
			return;
		}
		?><!DOCTYPE html><html lang="fr"><head>
		<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
		<title>Rapport de sécurité<?php echo $who ? ' — ' . esc_html( $who ) : ''; ?></title>
		<?php
		$og_title = $ok ? ( '🛡️ ' . $who . ' — votre audit sécurité est prêt (ne tardez pas)' ) : 'Votre audit de sécurité — Alliance Groupe';
		$og_desc  = $ok
			? ( ( count( $fails ) > 0 ? count( $fails ) . ' point(s) à corriger sur votre site. ' : '' ) . '👉 Découvrez votre rapport avant qu\'un pirate ne trouve la faille avant vous. Analyse offerte par Alliance Groupe.' )
			: 'Découvrez l\'audit de sécurité de votre site.';
		// Image de l'aperçu : bannière brandée FIABLE (le screenshot mShots échoue souvent → pas d'image).
		$og_img   = get_stylesheet_directory_uri() . '/assets/images/og-banner.png';
		$og_url   = ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '' );
		?>
		<meta property="og:type" content="website">
		<meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
		<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
		<meta property="og:image" content="<?php echo esc_url( $og_img ); ?>">
		<meta property="og:url" content="<?php echo esc_url( $og_url ); ?>">
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
		<meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>">
		<meta name="twitter:image" content="<?php echo esc_url( $og_img ); ?>">
		<style>
			*{box-sizing:border-box}body{margin:0;background:#0b0b0f;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
			.wrap{max-width:640px;margin:0 auto;padding:22px 16px 60px;}
			.top{display:flex;align-items:center;gap:10px;margin-bottom:14px;}.top img{width:34px;height:34px;border-radius:8px;}.top b{font-size:1.05rem;}.top b span{color:#d4b45c;}
			.tag{display:inline-block;background:#15151b;color:#e6b35a;border:1px solid #e6b35a;border-radius:100px;padding:4px 13px;font-size:.75rem;font-weight:700;}
			h1{font-size:1.5rem;margin:10px 0 3px;}.url{color:#8a8a92;font-size:.85rem;word-break:break-all;margin:0 0 16px;}
			.hero{display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:center;background:#14141a;border:1px solid #26262f;border-radius:16px;padding:18px;}
			.hero img{width:240px;max-width:100%;height:160px;object-fit:cover;object-position:top;border-radius:10px;border:1px solid #333;background:#0e0e13;}
			.sc{font-size:2.8rem;font-weight:800;line-height:1;}.scl{color:#9a9aa2;font-size:.85rem;margin-top:4px;}
			h2{font-size:1.15rem;margin:24px 0 10px;}
			.f{background:#1b1113;border:1px solid #4a2327;border-left:4px solid #e5484d;border-radius:10px;padding:11px 13px;margin-bottom:9px;}
			.f .m{color:#c9b0b2;font-size:.85rem;margin-top:3px;}
			.lock{position:relative;background:#14141a;border:1px solid #26262f;border-radius:12px;padding:16px;overflow:hidden;margin-bottom:10px;}
			.lock .bl{filter:blur(5px);opacity:.5;user-select:none;}
			.lock .ov{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px;}
			.warn{background:#241f0e;border:1px solid #6a5a1f;border-radius:12px;padding:15px;margin:16px 0;color:#e6d9a8;}
			.warn ul{margin:8px 0 0;padding-left:18px;line-height:1.6;}
			.cta{display:block;text-align:center;background:linear-gradient(135deg,#d4b45c,#b98f2f);color:#0b0b0f;font-weight:800;padding:16px;border-radius:14px;text-decoration:none;font-size:1.05rem;margin-top:20px;}
		</style></head><body>
		<a href="#" onclick="if(history.length>1){history.back();return false}location.href='<?php echo esc_url( home_url( '/prospection-mobile' ) ); ?>';return false" style="display:inline-block;margin:12px 0 0 14px;color:#d4b45c;font-weight:700;text-decoration:none;">← Retour</a>
		<div class="wrap">
		<div class="top"><img src="<?php echo esc_url( $logo ); ?>" alt=""><b>Alliance <span>Groupe</span></b></div>
		<?php if ( ! $ok ) : ?>
			<h1>Rapport de sécurité</h1><p class="url">Lien invalide. Contactez Alliance Groupe.</p>
		<?php else : ?>
			<span class="tag">🛡️ AUDIT SÉCURITÉ &amp; PERFORMANCE</span>
			<h1>Rapport pour <?php echo esc_html( $who ); ?></h1>
			<p class="url"><?php echo esc_html( $site ); ?></p>
			<div class="hero">
				<img id="shot" src="<?php echo esc_url( $shot ); ?>" data-base="<?php echo esc_url( $shot ); ?>" data-try="0" onload="agShot(this)" onerror="agShotErr(this)" alt="">
				<div style="text-align:center;">
					<div class="sc" style="color:<?php echo esc_attr( $col ); ?>;"><?php echo (int) $score; ?><span style="font-size:1rem;color:#8a8a92;">/100</span></div>
					<div class="scl">Note globale<?php echo $tech ? ' · ' . esc_html( $tech ) : ''; ?></div>
					<div style="color:<?php echo esc_attr( $col ); ?>;font-weight:700;margin-top:6px;"><?php echo $score < 50 ? '⚠️ Site à risque' : ( $score < 75 ? 'À améliorer' : 'Perfectible' ); ?></div>
				</div>
			</div>
			<?php if ( $unlocked ) : ?>
			<div style="background:#0e1f13;border:1px solid #2e6a3f;border-radius:12px;padding:13px 15px;margin:16px 0;color:#bfe6c9;">
				<strong style="color:#4ade80;">✅ Rapport complet débloqué</strong>
				<div style="font-size:.86rem;margin-top:3px;">Merci pour votre confiance. Voici l'intégralité des points détectés et notre plan de correction.</div>
			</div>
			<?php endif; ?>
			<?php if ( $fetch_failed ) : ?>
			<div style="background:#241f0e;border:1px solid #6a5a1f;border-radius:12px;padding:15px;margin:16px 0;color:#e6d9a8;">
				<strong style="color:#ffd873;">🔎 Analyse automatique impossible pour l'instant</strong>
				<div style="font-size:.9rem;margin-top:6px;line-height:1.5;">Votre site protège son accès aux outils automatiques (ou il était momentanément indisponible). C'est justement un point à vérifier : nous pouvons faire un <strong>audit manuel approfondi</strong> et vous dire précisément ce qui va et ce qui ne va pas.</div>
			</div>
			<?php else : ?>
			<h2>Ce qu'on a détecté (<?php echo count( $fails ); ?>)</h2>
			<?php if ( $no_issues ) : ?>
			<div class="f" style="background:#0e1f13;border-color:#2e6a3f;border-left-color:#2ecc71;"><strong>✅ Les points de base sont en ordre.</strong><div class="m">Bonne nouvelle sur les vérifications rapides. Mais un audit de surface ne voit pas tout : failles de sécurité en profondeur, mises à jour, sauvegardes, RGPD… c'est ce que couvre le rapport complet.</div></div>
			<?php endif; ?>
			<?php foreach ( ( $unlocked ? $fails : $vis ) as $c ) : ?>
				<div class="f"><strong><?php echo ( 'fail' === $c['status'] ? '❌ ' : '⚠️ ' ) . esc_html( ag_report_label( $c['name'] ?? '' ) ); ?></strong><?php echo ! empty( $c['msg'] ) ? '<div class="m">' . esc_html( $c['msg'] ) . '</div>' : ''; ?></div>
			<?php endforeach; ?>
			<?php if ( $hid && ! $unlocked ) : ?>
			<div class="lock"><div class="bl"><?php foreach ( $hid as $c ) : ?><div style="padding:6px 0;border-bottom:1px solid #222;">❌ <?php echo esc_html( ag_report_label( $c['name'] ?? 'Faille' ) ); ?></div><?php endforeach; ?></div>
				<div class="ov"><div style="font-size:1.6rem;">🔒</div><strong><?php echo count( $hid ); ?> autre<?php echo count( $hid ) > 1 ? 's' : ''; ?> faille<?php echo count( $hid ) > 1 ? 's' : ''; ?> détectée<?php echo count( $hid ) > 1 ? 's' : ''; ?></strong><span style="color:#9a9aa2;font-size:.85rem;">À découvrir dans le rapport complet<?php echo $price > 0 ? ' — ' . esc_html( number_format_i18n( $price, 0 ) ) . ' €' : ''; ?></span></div>
			</div>
			<?php endif; ?>
			<?php endif; // fetch_failed ?>
			<?php if ( $kali ) : ?>
			<div style="background:#101826;border:1px solid #24405f;border-radius:12px;padding:14px;margin-bottom:10px;">
				<strong style="color:#7fb4ff;">🔬 Scan de sécurité approfondi réalisé</strong>
				<?php if ( $unlocked ) : ?>
				<div style="color:#cfe0f5;font-size:.86rem;margin-top:6px;white-space:pre-wrap;word-break:break-word;line-height:1.5;"><?php echo esc_html( $kali ); ?></div>
				<?php else : ?>
				<div style="color:#aac4e6;font-size:.86rem;margin-top:4px;"><?php echo (int) $kali_n; ?> anomalie(s) technique(s) supplémentaire(s) détectée(s) par notre expert. Détail complet dans le rapport débloqué.</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="warn"><strong>⚠️ Ce qui peut arriver si ce n'est pas corrigé&nbsp;:</strong><ul>
				<li><strong>Piratage &amp; défiguration</strong> : votre site affiche du spam ou redirige vos clients vers un autre site (arrivé à des milliers de PME en 2024).</li>
				<li><strong>Vol des données clients</strong> (noms, emails, réservations) → vous êtes responsable devant la <strong>CNIL / RGPD</strong> : jusqu'à <strong>4 % du chiffre d'affaires</strong> d'amende.</li>
				<li><strong>Rançongiciel</strong> : votre site est bloqué et on vous demande de payer pour le récupérer.</li>
				<li><strong>Déréférencement Google</strong> : un site signalé « dangereux » disparaît des résultats → <strong>perte directe de clients</strong>.</li>
			</ul></div>
			<?php if ( $unlocked ) : ?>
				<a class="cta" href="<?php echo esc_url( home_url( '/mon-espace-client' ) ); ?>">🔧 Faire corriger mon site par Alliance Groupe</a>
			<?php else : ?>
				<?php if ( isset( $_GET['err'] ) ) : ?><p style="text-align:center;color:#e5484d;font-size:.85rem;margin-top:14px;"><?php echo 'email' === $_GET['err'] ? 'Merci de saisir un email valide.' : 'Session expirée, réessayez.'; ?></p><?php endif; ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:20px;background:#14141a;border:1px solid #26262f;border-radius:14px;padding:16px;">
					<input type="hidden" name="action" value="ag_rapport_checkout">
					<input type="hidden" name="_n" value="<?php echo esc_attr( wp_create_nonce( 'ag_rapport_pay' ) ); ?>">
					<input type="hidden" name="site" value="<?php echo esc_attr( $site ); ?>">
					<input type="hidden" name="name" value="<?php echo esc_attr( $name ); ?>">
					<label style="display:block;color:#cfcfd6;font-size:.9rem;font-weight:600;margin-bottom:8px;">Votre email pour recevoir le rapport complet :</label>
					<input type="email" name="email" required placeholder="vous@exemple.com" value="<?php echo esc_attr( is_user_logged_in() ? wp_get_current_user()->user_email : '' ); ?>" style="width:100%;padding:13px;border-radius:10px;border:1px solid #333;background:#0e0e13;color:#fff;font-size:1rem;margin-bottom:12px;">
					<button type="submit" class="cta" style="width:100%;border:0;cursor:pointer;"><?php echo esc_html( $ctalabel ); ?></button>
				</form>
			<?php endif; ?>
			<?php $wapro = preg_replace( '/[^0-9]/', '', (string) get_option( 'ag_wa_pro', '' ) ); if ( $wapro ) : ?>
			<a class="cta" style="background:#25d366;color:#04210f;margin-top:10px;" href="https://wa.me/<?php echo esc_attr( $wapro ); ?>?text=<?php echo rawurlencode( 'Bonjour, j\'ai vu le rapport de sécurité de mon site ' . $host . '. J\'aimerais en savoir plus.' ); ?>">💬 Poser une question sur WhatsApp</a>
			<?php endif; ?>
			<p style="text-align:center;color:#8a8a92;font-size:.82rem;margin-top:12px;"><?php echo $unlocked ? 'Rapport complet Alliance Groupe. On vous accompagne pour tout corriger.' : 'Rapport complet + plan de correction par Alliance Groupe. Paiement sécurisé, déblocage immédiat.'; ?></p>
		<?php endif; ?>
		</div>
		<script>window.agShot=function(i){if(i.naturalWidth>50&&!i.getAttribute('data-fail'))return;var n=+(i.getAttribute('data-try')||0);if(n>=5){i.style.display='none';return;}i.setAttribute('data-try',n+1);i.removeAttribute('data-fail');setTimeout(function(){i.src=i.getAttribute('data-base')+'&r='+Date.now();},3200);};window.agShotErr=function(i){i.setAttribute('data-fail','1');window.agShot(i);};</script>
		</body></html><?php
	}
}

/* ============================================================
   PAYWALL DU RAPPORT CLIENT
   checkout email → paiement PayPal vérifié → déblocage AUTO :
   compte ag_client créé + rapport complet débloqué + email d'accès.
   Décidé avec Fabrice : compte créé APRÈS paiement ; le montant du
   rapport (ag_tester_opt 'price', 49 €) ne doit PAS égaler un tier
   de licence (99/149) pour éviter tout conflit de hook.
   ============================================================ */

/* Clé d'unlock d'un site (indépendante du protocole/casse). */
if ( ! function_exists( 'ag_rapport_key' ) ) {
	function ag_rapport_key( $site ) { return md5( strtolower( trim( (string) $site ) ) ); }
}

/* Le rapport de ce site est-il débloqué pour ce visiteur ?
 * - jeton d'accès valide (?k=) reçu par email après paiement, OU
 * - admin connecté (aperçu), OU
 * - client connecté dont l'email correspond à l'achat. */
if ( ! function_exists( 'ag_rapport_is_unlocked' ) ) {
	function ag_rapport_is_unlocked( $site, $token = '' ) {
		if ( '' === (string) $site ) return false;
		if ( current_user_can( 'manage_options' ) ) return true;
		$all = get_option( 'ag_audit_unlocked', array() );
		$rec = is_array( $all ) ? ( $all[ ag_rapport_key( $site ) ] ?? null ) : null;
		if ( ! is_array( $rec ) ) return false;
		if ( '' !== $token && hash_equals( (string) ( $rec['token'] ?? '' ), (string) $token ) ) return true;
		if ( is_user_logged_in() ) {
			$u = wp_get_current_user();
			if ( $u && strtolower( $u->user_email ) === strtolower( (string) ( $rec['email'] ?? '' ) ) ) return true;
		}
		return false;
	}
}

/* Lien du rapport COMPLET débloqué (avec jeton d'accès). */
if ( ! function_exists( 'ag_rapport_full_url' ) ) {
	function ag_rapport_full_url( $site, $name = '' ) {
		$all = get_option( 'ag_audit_unlocked', array() );
		$rec = is_array( $all ) ? ( $all[ ag_rapport_key( $site ) ] ?? array() ) : array();
		$tok = (string) ( $rec['token'] ?? '' );
		return add_query_arg( array_filter( array(
			'ag_rapport' => 1,
			'site'       => rawurlencode( (string) $site ),
			'name'       => '' !== (string) $name ? rawurlencode( (string) $name ) : null,
			'k'          => '' !== $tok ? $tok : null,
		) ), home_url( '/' ) );
	}
}

/* Checkout : le client saisit son email sur le rapport → commande en attente
 * → on l'envoie payer (lien PayPal). Le déblocage se fait au paiement vérifié. */
add_action( 'admin_post_nopriv_ag_rapport_checkout', 'ag_rapport_checkout' );
add_action( 'admin_post_ag_rapport_checkout', 'ag_rapport_checkout' );
if ( ! function_exists( 'ag_rapport_checkout' ) ) {
	function ag_rapport_checkout() {
		$site = isset( $_POST['site'] ) ? esc_url_raw( wp_unslash( $_POST['site'] ) ) : '';
		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$back = add_query_arg( array( 'ag_rapport' => 1, 'site' => rawurlencode( $site ), 'name' => rawurlencode( $name ) ), home_url( '/' ) );
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_rapport_pay' ) ) {
			wp_safe_redirect( add_query_arg( 'err', 'nonce', $back ) ); exit;
		}
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		if ( '' === $site || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'err', 'email', $back ) ); exit;
		}
		$price = function_exists( 'ag_tester_opt' ) ? (float) ag_tester_opt( 'price' ) : 49;
		$pend  = get_option( 'ag_audit_pending', array() );
		if ( ! is_array( $pend ) ) $pend = array();
		// Dé-doublonne : une seule commande en attente par (site + email).
		$exists = false;
		foreach ( $pend as $p ) {
			if ( empty( $p['paid'] ) && ag_rapport_key( $p['site'] ?? '' ) === ag_rapport_key( $site ) && strtolower( (string) ( $p['email'] ?? '' ) ) === strtolower( $email ) ) { $exists = true; break; }
		}
		if ( ! $exists ) {
			$pend[] = array( 'site' => $site, 'name' => $name, 'email' => $email, 'price' => $price, 'ts' => time(), 'paid' => 0, 'txn' => '' );
			if ( count( $pend ) > 300 ) $pend = array_slice( $pend, -300 );
			update_option( 'ag_audit_pending', $pend, false );
		}
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '🛒 Rapport : commande en attente', $email . ' — ' . $site . ' (' . number_format_i18n( $price, 0 ) . ' €)' );
		}
		$payurl = function_exists( 'ag_tester_opt' ) ? trim( (string) ag_tester_opt( 'pay_url' ) ) : '';
		if ( '' !== $payurl ) { wp_redirect( $payurl ); exit; }
		// Pas de lien de paiement configuré : on confirme l'enregistrement de la demande.
		wp_safe_redirect( add_query_arg( 'ok', 'pending', $back ) ); exit;
	}
}

/* Paiement vérifié → si une commande de rapport correspond (montant + email) :
 * crée le compte client, débloque le rapport, envoie l'accès par email. */
add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn = '', $type = '', $resource = array() ) {
	$amount = (float) $amount;
	$price  = function_exists( 'ag_tester_opt' ) ? (float) ag_tester_opt( 'price' ) : 49;
	if ( $price <= 0 || abs( $amount - $price ) > 0.5 ) return; // pas le montant d'un rapport
	$email = sanitize_email( (string) $email );
	$pend  = get_option( 'ag_audit_pending', array() );
	if ( ! is_array( $pend ) ) return;
	$hit = -1;
	// 1) correspondance email exacte
	foreach ( $pend as $i => $p ) {
		if ( empty( $p['paid'] ) && strtolower( (string) ( $p['email'] ?? '' ) ) === strtolower( $email ) ) { $hit = $i; break; }
	}
	// 2) sinon : une seule commande en attente à ce montant → on l'attribue
	if ( $hit < 0 ) {
		$cands = array();
		foreach ( $pend as $i => $p ) { if ( empty( $p['paid'] ) ) $cands[] = $i; }
		if ( 1 === count( $cands ) ) $hit = $cands[0];
	}
	if ( $hit < 0 ) return; // aucune commande de rapport → laisse les autres hooks agir

	$order = $pend[ $hit ];
	$site  = (string) $order['site'];
	$name  = (string) ( $order['name'] ?? '' );
	$to    = is_email( $email ) ? $email : (string) $order['email'];

	// Idempotence : déjà débloqué pour cette transaction ? on ne refait rien.
	$unl = get_option( 'ag_audit_unlocked', array() );
	if ( ! is_array( $unl ) ) $unl = array();
	$key = ag_rapport_key( $site );
	if ( isset( $unl[ $key ]['txn'] ) && '' !== (string) $txn && (string) $unl[ $key ]['txn'] === (string) $txn ) return;

	// Débloque (conserve le jeton s'il existe déjà).
	$token = isset( $unl[ $key ]['token'] ) && '' !== $unl[ $key ]['token'] ? $unl[ $key ]['token'] : wp_generate_password( 20, false );
	$unl[ $key ] = array( 'email' => $to, 'token' => $token, 'ts' => time(), 'txn' => (string) $txn, 'site' => $site, 'name' => $name );
	update_option( 'ag_audit_unlocked', $unl, false );

	$pend[ $hit ]['paid'] = 1;
	$pend[ $hit ]['txn']  = (string) $txn;
	update_option( 'ag_audit_pending', $pend, false );

	// Crée le compte client (envoie l'email « définis ton mot de passe »).
	if ( function_exists( 'ag_create_member' ) ) {
		$uid = (int) ag_create_member( $to, $name, 'ag_client' );
		if ( $uid ) {
			if ( '' === (string) get_user_meta( $uid, 'ag_client_site', true ) && '' !== $site ) update_user_meta( $uid, 'ag_client_site', $site );
			$reps = (array) get_user_meta( $uid, 'ag_client_reports', true );
			$reps[ $key ] = array( 'site' => $site, 'name' => $name, 'ts' => time() );
			update_user_meta( $uid, 'ag_client_reports', $reps );
		}
	}

	// Email : accès direct au rapport complet.
	$full = ag_rapport_full_url( $site, $name );
	if ( function_exists( 'ag_email_wrap' ) && function_exists( 'ag_email_button' ) ) {
		$inner = '<p>Bonjour' . ( '' !== $name ? ' ' . esc_html( $name ) : '' ) . ',</p>'
			. '<p>Merci pour votre confiance 🙏 Votre <strong>rapport de sécurité complet</strong> est débloqué.</p>'
			. ag_email_button( 'Voir mon rapport complet', $full )
			. '<p style="color:#9a9aa5;font-size:13px;">Ou copiez ce lien :<br><span style="color:#cfc7b8;word-break:break-all;">' . esc_url( $full ) . '</span></p>'
			. '<p>Un <strong>compte client</strong> a aussi été créé pour vous : vous recevez un second email pour définir votre mot de passe et accéder à votre espace.</p>'
			. '<p>On vous accompagne pour tout corriger — répondez simplement à cet email pour lancer la mise en sécurité.</p>';
		wp_mail( $to, 'Votre rapport de sécurité complet 🛡️', ag_email_wrap( 'Rapport débloqué', $inner ), array( 'Content-Type: text/html; charset=UTF-8', 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' ) );
	} else {
		wp_mail( $to, 'Votre rapport de sécurité complet', "Bonjour,\n\nVotre rapport complet est débloqué :\n" . $full . "\n\nAlliance Groupe" );
	}
	if ( function_exists( 'ag_push' ) ) ag_push( '✅ Rapport payé + débloqué', $to . ' — ' . $site . ' (' . number_format_i18n( $amount, 0 ) . ' €)' );
}, 15, 5 );

/* ADMIN : réconciliation des rapports (commandes en attente + débloqués),
 * avec déblocage MANUEL (au cas où l'email PayPal du payeur diffère). */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-espace-audit', 'Rapports payés', '🔓 Rapports (paiements)', 'manage_options', 'ag-rapports-pay', 'ag_rapports_pay_render' );
}, 25 );

/* Déblocage / relance manuels depuis l'admin. */
add_action( 'admin_post_ag_rapport_unlock_manual', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
	check_admin_referer( 'ag_rapport_unlock_manual' );
	$site  = esc_url_raw( wp_unslash( $_POST['site'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$price = function_exists( 'ag_tester_opt' ) ? (float) ag_tester_opt( 'price' ) : 49;
	if ( '' !== $site && is_email( $email ) ) {
		// Réutilise exactement le même flux que le webhook (idempotent).
		do_action( 'ag_paypal_payment_verified', $price, $email, 'MANUAL-' . time(), 'PAYMENT.CAPTURE.COMPLETED', array() );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-rapports-pay', 'done' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );

if ( ! function_exists( 'ag_rapports_pay_render' ) ) {
	function ag_rapports_pay_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$pend  = (array) get_option( 'ag_audit_pending', array() );
		$unl   = (array) get_option( 'ag_audit_unlocked', array() );
		$price = function_exists( 'ag_tester_opt' ) ? (float) ag_tester_opt( 'price' ) : 49;
		echo '<div class="wrap"><h1>🔓 Rapports — paiements &amp; déblocages</h1>';
		if ( isset( $_GET['done'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Déblocage effectué + email envoyé au client.</p></div>';
		echo '<p style="max-width:820px;color:#50575e;">Quand un client paie le <strong>' . esc_html( number_format_i18n( $price, 0 ) ) . ' €</strong> du rapport (lien PayPal), son <strong>compte est créé</strong> et le <strong>rapport se débloque automatiquement</strong>. Si l\'email PayPal du payeur diffère de celui saisi, débloque ici à la main.</p>';

		// Commandes en attente.
		echo '<h2>⏳ Commandes en attente</h2>';
		$open = array_reverse( array_filter( $pend, function ( $p ) { return empty( $p['paid'] ); } ) );
		if ( ! $open ) {
			echo '<p><em>Aucune commande en attente.</em></p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Site</th><th>Nom</th><th>Email</th><th>Montant</th><th>Date</th><th>Débloquer</th></tr></thead><tbody>';
			foreach ( $open as $p ) {
				echo '<tr><td>' . esc_html( $p['site'] ?? '' ) . '</td><td>' . esc_html( $p['name'] ?? '' ) . '</td><td>' . esc_html( $p['email'] ?? '' ) . '</td><td>' . esc_html( number_format_i18n( (float) ( $p['price'] ?? $price ), 0 ) ) . ' €</td><td>' . esc_html( ! empty( $p['ts'] ) ? wp_date( 'd/m/Y H\hi', (int) $p['ts'] ) : '' ) . '</td><td>';
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Confirmer le paiement de ce client et débloquer son rapport ?\');" style="margin:0;">';
				wp_nonce_field( 'ag_rapport_unlock_manual' );
				echo '<input type="hidden" name="action" value="ag_rapport_unlock_manual">';
				echo '<input type="hidden" name="site" value="' . esc_attr( $p['site'] ?? '' ) . '">';
				echo '<input type="hidden" name="email" value="' . esc_attr( $p['email'] ?? '' ) . '">';
				echo '<input type="hidden" name="name" value="' . esc_attr( $p['name'] ?? '' ) . '">';
				echo '<button class="button button-primary button-small">✅ Payé → débloquer</button></form></td></tr>';
			}
			echo '</tbody></table>';
		}

		// Rapports débloqués.
		echo '<h2 style="margin-top:24px;">✅ Rapports débloqués</h2>';
		if ( ! $unl ) {
			echo '<p><em>Aucun rapport débloqué pour l\'instant.</em></p>';
		} else {
			$rows = array();
			foreach ( $unl as $r ) { if ( is_array( $r ) ) $rows[] = $r; }
			usort( $rows, function ( $a, $b ) { return (int) ( $b['ts'] ?? 0 ) - (int) ( $a['ts'] ?? 0 ); } );
			echo '<table class="widefat striped"><thead><tr><th>Site</th><th>Client (email)</th><th>Date</th><th>Transaction</th><th>Lien</th></tr></thead><tbody>';
			foreach ( $rows as $r ) {
				$full = function_exists( 'ag_rapport_full_url' ) ? ag_rapport_full_url( $r['site'] ?? '', $r['name'] ?? '' ) : '';
				echo '<tr><td>' . esc_html( $r['site'] ?? '' ) . '</td><td>' . esc_html( $r['email'] ?? '' ) . '</td><td>' . esc_html( ! empty( $r['ts'] ) ? wp_date( 'd/m/Y H\hi', (int) $r['ts'] ) : '' ) . '</td><td>' . esc_html( $r['txn'] ?? '' ) . '</td><td>' . ( $full ? '<a href="' . esc_url( $full ) . '" target="_blank">Ouvrir</a>' : '' ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}
}

/* Métier → lien du template démo correspondant (pour illustrer une création/refonte). */
if ( ! function_exists( 'ag_demo_link_for' ) ) {
	function ag_demo_link_for( $txt ) {
		$t = ' ' . strtolower( (string) $txt ) . ' ';
		$map = array(
			'restaurant' => 'wordpress-restaurant', 'resto' => 'wordpress-restaurant', 'pizz' => 'wordpress-restaurant', 'brasserie' => 'wordpress-restaurant', 'traiteur' => 'wordpress-restaurant', 'creperie' => 'wordpress-restaurant', 'bistro' => 'wordpress-restaurant',
			'avocat' => 'wordpress-avocat', 'juri' => 'wordpress-avocat', 'notaire' => 'wordpress-avocat', 'huissier' => 'wordpress-avocat', 'cabinet' => 'wordpress-avocat', 'expert-comptable' => 'wordpress-avocat', 'comptable' => 'wordpress-avocat',
			'association' => 'wordpress-association', 'asso' => 'wordpress-association', 'club ' => 'wordpress-association',
			'barber' => 'wordpress-barber', 'coiff' => 'wordpress-barber', 'salon' => 'wordpress-barber', 'esthe' => 'wordpress-barber', 'beaute' => 'wordpress-barber', 'ongle' => 'wordpress-barber',
			'coach' => 'wordpress-coach', 'sport' => 'wordpress-coach', 'fitness' => 'wordpress-coach', 'yoga' => 'wordpress-coach', 'pilates' => 'wordpress-coach',
			'artisan' => 'wordpress-artisan', 'plomb' => 'wordpress-artisan', 'menuis' => 'wordpress-artisan', 'electric' => 'wordpress-artisan', 'macon' => 'wordpress-artisan', 'maçon' => 'wordpress-artisan', 'peintre' => 'wordpress-artisan', 'btp' => 'wordpress-artisan', 'chauffag' => 'wordpress-artisan', 'serrur' => 'wordpress-artisan', 'couvreur' => 'wordpress-artisan', 'garage' => 'wordpress-artisan',
		);
		foreach ( $map as $kw => $slug ) { if ( false !== strpos( $t, $kw ) ) return home_url( '/' . $slug ); }
		return '';
	}
}

/* Audit UNIFIÉ : le "vrai" audit complet (approfondi passif), mis en CACHE 1h par URL
 * → même site = MÊME note partout (app, rapport, carte). Fini les 47 puis 60. */
if ( ! function_exists( 'ag_audit_get' ) ) {
	function ag_audit_get( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url || ! function_exists( 'ag_audit_run' ) ) return array();
		$key = 'ag_auditc_' . md5( strtolower( $url ) );
		$c = get_transient( $key );
		if ( is_array( $c ) && isset( $c['score'] ) ) return $c;
		$a = function_exists( 'ag_audit_run_deep' ) ? ag_audit_run_deep( $url ) : ag_audit_run( $url );
		if ( is_array( $a ) && isset( $a['score'] ) ) set_transient( $key, $a, HOUR_IN_SECONDS );
		return is_array( $a ) ? $a : array();
	}
}

/* Audit d'un site depuis l'APP (léger = passif, avancé = approfondi ; JAMAIS Kali/local).
 * Rend une note /100 + le nombre de failles + des SMS prêts (refonte / sécurité / mixte). */
add_action( 'wp_ajax_ag_app_audit', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Session expirée.' ) );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	if ( '' === $url ) wp_send_json_error( array( 'm' => 'Ce prospect n\'a pas de site.' ) );
	$mode = 'complet';
	if ( ! function_exists( 'ag_audit_run' ) ) wp_send_json_error( array( 'm' => 'Module audit indisponible.' ) );
	$a = ag_audit_get( $url ); // audit complet unifié + caché → note homogène partout
	$score = (int) ( $a['score'] ?? 0 );
	$crit  = (int) ( $a['critical'] ?? 0 );
	$tech  = (string) ( $a['tech'] ?? '' );
	$fails = array();
	foreach ( (array) ( $a['checks'] ?? array() ) as $c ) {
		$st = $c['status'] ?? '';
		// Libellé en clair + SANS balises <...> (sinon l'app les avale via innerHTML → « Balise » tronqué).
		$lbl = function_exists( 'ag_report_label' ) ? ag_report_label( $c['name'] ?? '' ) : preg_replace( '/<[^>]+>/', '', (string) ( $c['name'] ?? '' ) );
		if ( 'fail' === $st || 'warn' === $st ) { $fails[] = ( 'fail' === $st ? '❌ ' : '⚠️ ' ) . $lbl; }
	}
	$fails = array_slice( $fails, 0, 8 );
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$who   = '' !== $name ? $name : 'votre établissement';
	$link  = function_exists( 'ag_ambassadeur_sale_link' ) ? ag_ambassadeur_sale_link( strtolower( wp_get_current_user()->user_email ) ) : home_url( '/sites-express' );
	// Reco : site pourri (note basse) → refonte ; sinon failles → sécurité ; sinon refonte douce.
	$reco = ( $score < 50 ) ? 'refonte' : ( $crit > 0 ? 'securite' : 'refonte' );
	$demo = function_exists( 'ag_demo_link_for' ) ? ag_demo_link_for( $name . ' ' . ( $_POST['type'] ?? '' ) ) : '';
	$ex   = '' !== $demo ? " Exemple d'un site moderne pour votre activité : " . $demo : '';
	$msg  = array(
		'refonte'  => "Bonjour, j'ai analysé le site de " . $who . " : note " . $score . "/100. Il gagnerait beaucoup à être refait (rapidité, mobile, référencement Google)." . $ex . " On offre justement un site chaque mois — je vous montre ? " . $link,
		'securite' => "Bonjour, audit du site de " . $who . " : " . ( $crit > 0 ? $crit . " point(s) de sécurité à corriger" : "quelques points à sécuriser" ) . " (données clients, mises à jour). On peut le sécuriser rapidement. Audit détaillé offert : " . $link,
		'mixte'    => "Bonjour, j'ai audité le site de " . $who . " : note " . $score . "/100" . ( $crit > 0 ? " et " . $crit . " faille(s) de sécurité" : "" ) . "." . $ex . " Refonte ou sécurisation, je vous conseille — on offre un site ce mois-ci : " . $link,
	);
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	// Lien du RAPPORT CLIENT (page teaser à envoyer au prospect).
	$report = add_query_arg( array( 'ag_rapport' => 1, 'site' => rawurlencode( $url ), 'name' => rawurlencode( $name ) ), home_url( '/' ) );
	// SMS « rapport » : accroche + lien (l'aperçu du lien montre déjà titre + image brandée).
	$msg['rapport'] = ( $score > 0 )
		? "🔒 " . $who . ", votre audit de sécurité est prêt (note " . $score . "/100). Ne tardez pas : découvrez ce qu'il faut corriger avant qu'une faille ne soit exploitée 👉 " . $report
		: "🔒 " . $who . ", votre audit de sécurité est prêt. Découvrez les points à surveiller sur votre site 👉 " . $report;
	// Persiste la note sur la fiche prospect (si id fourni).
	$pid = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	if ( '' !== $pid ) {
		$list = (array) get_option( 'ag_prospects', array() );
		foreach ( $list as $i => $p ) {
			if ( ( $p['id'] ?? '' ) === $pid ) {
				$list[ $i ]['audit_score'] = $score;
				$list[ $i ]['audit_crit']  = $crit;
				$list[ $i ]['audit_ts']    = time();
				update_option( 'ag_prospects', $list );
				break;
			}
		}
	}
	// Historique des sites audités (pour l'onglet Audit de l'app).
	$audits = get_option( 'ag_app_audits', array() ); if ( ! is_array( $audits ) ) { $audits = array(); }
	$audits[ md5( strtolower( trim( $url ) ) ) ] = array( 'url' => $url, 'name' => $who, 'phone' => $phone, 'score' => $score, 'crit' => $crit, 'fails' => $fails, 'ts' => time() );
	uasort( $audits, function ( $x, $y ) { return (int) ( $y['ts'] ?? 0 ) <=> (int) ( $x['ts'] ?? 0 ); } );
	if ( count( $audits ) > 60 ) { $audits = array_slice( $audits, 0, 60, true ); }
	update_option( 'ag_app_audits', $audits, false );

	wp_send_json_success( array( 'score' => $score, 'critical' => $crit, 'tech' => $tech, 'mode' => $mode, 'reco' => $reco, 'fails' => $fails, 'report' => $report, 'report_card' => add_query_arg( 'card', 1, $report ), 'msg' => $msg ) );
} );

/* Envoi SMS EN MASSE aux ambassadeurs depuis l'app (admin) + journal de suivi. */
add_action( 'wp_ajax_ag_app_bulk_sms', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Refusé.' ) );
	$msg = trim( wp_strip_all_tags( (string) wp_unslash( $_POST['msg'] ?? '' ) ) );
	$raw = (string) wp_unslash( $_POST['numbers'] ?? '' );
	if ( '' === $msg ) wp_send_json_error( array( 'm' => 'Message vide.' ) );
	$nums = array();
	foreach ( preg_split( '/[\s,;]+/', $raw ) as $p ) {
		$e = function_exists( 'ag_sms_to_e164' ) ? ag_sms_to_e164( $p ) : preg_replace( '/[^0-9+]/', '', $p );
		if ( '' !== $e && strlen( preg_replace( '/[^0-9]/', '', $e ) ) >= 9 && ! in_array( $e, $nums, true ) ) { $nums[] = $e; }
	}
	$nums = array_slice( $nums, 0, 100 );
	if ( empty( $nums ) ) wp_send_json_error( array( 'm' => 'Aucun numéro valide.' ) );
	if ( ! function_exists( 'ag_sms_send' ) || '' === (string) get_option( 'ag_smsgw_mode', '' ) ) {
		wp_send_json_error( array( 'm' => 'Passerelle SMS pas encore active (Android + SIM à brancher). ' . count( $nums ) . ' numéro(s) prêts.' ) );
	}
	$detail = array(); $ok = 0; $ko = 0;
	foreach ( $nums as $to ) {
		$sent = ag_sms_send( $to, $msg );
		$detail[] = array( 'to' => $to, 'st' => $sent ? 'envoyé' : 'échec' );
		$sent ? $ok++ : $ko++;
		usleep( 150000 );
	}
	$log = get_option( 'ag_bulk_sms_log', array() ); if ( ! is_array( $log ) ) { $log = array(); }
	array_unshift( $log, array( 'ts' => time(), 'msg' => $msg, 'total' => count( $nums ), 'ok' => $ok, 'ko' => $ko, 'detail' => $detail ) );
	if ( count( $log ) > 40 ) { $log = array_slice( $log, 0, 40 ); }
	update_option( 'ag_bulk_sms_log', $log, false );
	wp_send_json_success( array( 'ok' => $ok, 'ko' => $ko, 'total' => count( $nums ) ) );
} );

/* App CLIENT : enregistrer son site. */
add_action( 'wp_ajax_ag_client_save_site', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_client' ) ) wp_send_json_error();
	$site = esc_url_raw( wp_unslash( $_POST['site'] ?? '' ) );
	update_user_meta( get_current_user_id(), 'ag_client_site', $site );
	wp_send_json_success();
} );
/* App CLIENT : demande (modif site / support / sécurité) → notifie Fabrice + journalise. */
add_action( 'wp_ajax_ag_client_request', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_client' ) ) wp_send_json_error();
	$u    = wp_get_current_user();
	$type = sanitize_key( wp_unslash( $_POST['type'] ?? 'demande' ) );
	$msg  = sanitize_textarea_field( wp_unslash( $_POST['msg'] ?? '' ) );
	if ( '' === $msg ) wp_send_json_error( array( 'm' => 'Message vide.' ) );
	$labels = array( 'modif' => '🌐 Modif site', 'support' => '💬 Support', 'securite' => '🛡️ Sécurité' );
	$tag  = ( $labels[ $type ] ?? '📩 Demande client' );
	$who  = $u->display_name ?: $u->user_email;
	$full = $tag . ' — ' . $who . ' (' . $u->user_email . ')' . "\n" . $msg;
	// Journal
	$log = get_option( 'ag_client_requests', array() ); if ( ! is_array( $log ) ) { $log = array(); }
	array_unshift( $log, array( 'ts' => time(), 'type' => $type, 'name' => $who, 'email' => $u->user_email, 'msg' => $msg ) );
	if ( count( $log ) > 200 ) { $log = array_slice( $log, 0, 200 ); }
	update_option( 'ag_client_requests', $log, false );
	if ( function_exists( 'ag_sms' ) )  ag_sms( $tag . ' : ' . $who . ' — ' . $msg );
	if ( function_exists( 'ag_push' ) ) ag_push( $tag . ' (client)', $full );
	if ( function_exists( 'ag_calendar_notify' ) && 'support' !== $type ) ag_calendar_notify( $tag . ' : ' . $who, $full );
	wp_send_json_success();
} );

/* ADMIN : demandes des clients (app CLIENT) — liste + « traité » + réponse rapide. */
add_action( 'admin_menu', function () {
	$open = 0;
	foreach ( (array) get_option( 'ag_client_requests', array() ) as $r ) { if ( empty( $r['done'] ) ) $open++; }
	$badge = $open ? ' <span class="update-plugins count-' . $open . '"><span class="update-count">' . $open . '</span></span>' : '';
	$parent = menu_page_url( 'ag-hub', false ) ? 'ag-hub' : 'ag-prospects';
	add_submenu_page( $parent, 'Demandes clients', '📩 Demandes clients' . $badge, 'manage_options', 'ag-client-requests', 'ag_client_requests_render' );
}, 21 );

add_action( 'admin_post_ag_client_req_done', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
	check_admin_referer( 'ag_client_req_done' );
	$ts   = (int) ( $_POST['ts'] ?? 0 );
	$mode = sanitize_key( $_POST['mode'] ?? 'done' );
	$log  = (array) get_option( 'ag_client_requests', array() );
	foreach ( $log as $i => $r ) {
		if ( (int) ( $r['ts'] ?? 0 ) === $ts ) {
			if ( 'delete' === $mode ) { unset( $log[ $i ] ); }
			else { $log[ $i ]['done'] = empty( $r['done'] ) ? 1 : 0; }
			break;
		}
	}
	update_option( 'ag_client_requests', array_values( $log ), false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-client-requests' ), admin_url( 'admin.php' ) ) );
	exit;
} );

if ( ! function_exists( 'ag_client_requests_render' ) ) {
	function ag_client_requests_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$log    = (array) get_option( 'ag_client_requests', array() );
		$labels = array( 'modif' => '🌐 Modif site', 'support' => '💬 Support', 'securite' => '🛡️ Sécurité', 'demande' => '📩 Demande' );
		$show   = isset( $_GET['show'] ) ? sanitize_key( $_GET['show'] ) : 'open';
		$open   = 0; foreach ( $log as $r ) { if ( empty( $r['done'] ) ) $open++; }
		echo '<div class="wrap"><h1>📩 Demandes des clients</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Les demandes envoyées depuis l\'<strong>app client</strong> (modif de site, support, sécurité). Tu es aussi notifié par SMS/Telegram à chaque envoi.</p>';
		echo '<ul class="subsubsub"><li><a href="' . esc_url( add_query_arg( array( 'page' => 'ag-client-requests', 'show' => 'open' ), admin_url( 'admin.php' ) ) ) . '" class="' . ( 'open' === $show ? 'current' : '' ) . '">À traiter <span class="count">(' . (int) $open . ')</span></a> | </li>';
		echo '<li><a href="' . esc_url( add_query_arg( array( 'page' => 'ag-client-requests', 'show' => 'all' ), admin_url( 'admin.php' ) ) ) . '" class="' . ( 'all' === $show ? 'current' : '' ) . '">Toutes <span class="count">(' . count( $log ) . ')</span></a></li></ul>';

		$rows = array_filter( $log, function ( $r ) use ( $show ) { return 'all' === $show || empty( $r['done'] ); } );
		if ( ! $rows ) { echo '<p style="margin-top:20px;"><em>Aucune demande ' . ( 'open' === $show ? 'à traiter' : '' ) . '.</em></p></div>'; return; }
		echo '<table class="widefat striped" style="margin-top:12px;"><thead><tr><th style="width:150px;">Date</th><th style="width:120px;">Type</th><th>Client</th><th>Message</th><th style="width:210px;">Actions</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$ts   = (int) ( $r['ts'] ?? 0 );
			$done = ! empty( $r['done'] );
			$type = $labels[ $r['type'] ?? 'demande' ] ?? '📩 Demande';
			$mail = sanitize_email( $r['email'] ?? '' );
			echo '<tr' . ( $done ? ' style="opacity:.55;"' : '' ) . '>';
			echo '<td>' . esc_html( $ts ? wp_date( 'd/m/Y H\hi', $ts ) : '' ) . '</td>';
			echo '<td>' . esc_html( $type ) . ( $done ? '<br><span style="color:#1e7e34;font-size:.82em;">✓ traité</span>' : '' ) . '</td>';
			echo '<td><strong>' . esc_html( $r['name'] ?? '' ) . '</strong><br><a href="mailto:' . esc_attr( $mail ) . '">' . esc_html( $mail ) . '</a></td>';
			echo '<td style="white-space:pre-wrap;">' . esc_html( $r['msg'] ?? '' ) . '</td>';
			echo '<td>';
			echo '<a class="button button-small button-primary" href="mailto:' . esc_attr( $mail ) . '?subject=' . rawurlencode( 'Votre demande — Alliance Groupe' ) . '">✉️ Répondre</a> ';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;margin:4px 0 0;">';
			wp_nonce_field( 'ag_client_req_done' );
			echo '<input type="hidden" name="action" value="ag_client_req_done"><input type="hidden" name="ts" value="' . $ts . '">';
			echo '<button class="button button-small" name="mode" value="done">' . ( $done ? '↩︎ Rouvrir' : '✅ Traité' ) . '</button> ';
			echo '<button class="button button-small button-link-delete" name="mode" value="delete" onclick="return confirm(\'Supprimer cette demande ?\');">🗑️</button>';
			echo '</form></td></tr>';
		}
		echo '</tbody></table></div>';
	}
}

/* Résultats du scan Kali (PC) attachés à une URL → enrichissent le rapport client (admin). */
add_action( 'wp_ajax_ag_app_kali_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Refusé.' ) );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	if ( '' === $url ) wp_send_json_error( array( 'm' => 'URL manquante.' ) );
	$txt = sanitize_textarea_field( wp_unslash( $_POST['kali'] ?? '' ) );
	$all = get_option( 'ag_kali', array() ); if ( ! is_array( $all ) ) { $all = array(); }
	$k = md5( strtolower( $url ) );
	if ( '' === $txt ) { unset( $all[ $k ] ); } else { $all[ $k ] = $txt; }
	update_option( 'ag_kali', $all, false );
	wp_send_json_success();
} );

/* Suppression d'un prospect depuis l'app (propriétaire ou admin). */
add_action( 'wp_ajax_ag_app_prospect_delete', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Refusé.' ) );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	if ( '' === $id ) wp_send_json_error();
	$email = strtolower( wp_get_current_user()->user_email );
	$admin = current_user_can( 'manage_options' );
	$list  = (array) get_option( 'ag_prospects', array() );
	$new   = array(); $done = false;
	foreach ( $list as $p ) {
		if ( ( $p['id'] ?? '' ) === $id && ( $admin || strtolower( (string) ( $p['owner_email'] ?? '' ) ) === $email ) ) { $done = true; continue; }
		$new[] = $p;
	}
	if ( $done ) { update_option( 'ag_prospects', $new ); wp_send_json_success(); }
	wp_send_json_error( array( 'm' => 'Introuvable ou non autorisé.' ) );
} );

/* ── 9. Notifications téléphone (Telegram, gratuit & instantané) ──── */
if ( ! function_exists( 'ag_tg_cfg' ) ) {
	function ag_tg_cfg( $k ) { return trim( (string) get_option( 'ag_tg_' . $k, '' ) ); }
}
if ( ! function_exists( 'ag_tg_send' ) ) {
	function ag_tg_send( $chat_id, $text ) {
		$token = ag_tg_cfg( 'token' );
		if ( '' === $token || '' === trim( (string) $chat_id ) ) return false;
		$resp = wp_remote_post( 'https://api.telegram.org/bot' . $token . '/sendMessage', array(
			'timeout' => 15,
			'body'    => array( 'chat_id' => $chat_id, 'text' => $text, 'disable_web_page_preview' => 'true' ),
		) );
		return ! is_wp_error( $resp ) && 200 === (int) wp_remote_retrieve_response_code( $resp );
	}
}
if ( ! function_exists( 'ag_sms' ) ) {
	/**
	 * Envoie un SMS sur TON téléphone. Deux options gratuites/simples :
	 *  - Free Mobile (gratuit, vers ta propre ligne Free) : user + clé.
	 *  - Webhook générique (autre opérateur / Twilio via Make/Zapier) : URL avec {msg}.
	 */
	function ag_sms( $text ) {
		$text = wp_strip_all_tags( (string) $text );
		if ( '' === trim( $text ) ) return false;
		$sent = false;
		$fu = trim( (string) get_option( 'ag_sms_free_user', '' ) );
		$fk = trim( (string) get_option( 'ag_sms_free_key', '' ) );
		if ( $fu && $fk ) {
			$resp = wp_remote_get( 'https://smsapi.free-mobile.fr/sendmsg?user=' . rawurlencode( $fu ) . '&pass=' . rawurlencode( $fk ) . '&msg=' . rawurlencode( $text ), array( 'timeout' => 15 ) );
			if ( ! is_wp_error( $resp ) && 200 === (int) wp_remote_retrieve_response_code( $resp ) ) $sent = true;
		}
		$hook = trim( (string) get_option( 'ag_sms_webhook', '' ) );
		if ( $hook && false !== strpos( $hook, '{msg}' ) ) {
			$resp = wp_remote_get( str_replace( '{msg}', rawurlencode( $text ), $hook ), array( 'timeout' => 15 ) );
			if ( ! is_wp_error( $resp ) && (int) wp_remote_retrieve_response_code( $resp ) < 400 ) $sent = true;
		}
		return $sent;
	}
}
if ( ! function_exists( 'ag_push' ) ) {
	/**
	 * Alerte PERSO admin : SMS (ton tél) + WhatsApp (CallMeBot, 1:1).
	 * Le groupe Telegram interne ne reçoit ces alertes QUE si $group = true
	 * (sinon il ne garde que les messages d'équipe : message quotidien + annonces).
	 */
	function ag_push( $title, $body = '', $group = false ) {
		$text = $title . ( $body ? "\n\n" . $body : '' );
		$sent = false;
		if ( ag_sms( $text ) ) $sent = true;
		$wa_phone = preg_replace( '/[^0-9]/', '', (string) get_option( 'ag_wa_phone', '' ) );
		$wa_key   = trim( (string) get_option( 'ag_wa_apikey', '' ) );
		if ( $wa_phone && $wa_key ) {
			$resp = wp_remote_get( 'https://api.callmebot.com/whatsapp.php?phone=' . rawurlencode( $wa_phone ) . '&text=' . rawurlencode( $text ) . '&apikey=' . rawurlencode( $wa_key ), array( 'timeout' => 15 ) );
			if ( ! is_wp_error( $resp ) ) $sent = true;
		}
		if ( $group && ag_tg_send( ag_tg_cfg( 'chat' ), $text ) ) $sent = true;
		return $sent;
	}
}
if ( ! function_exists( 'ag_push_clients' ) ) {
	/** Diffusion vers le CANAL GÉNÉRAL clients (Telegram). */
	function ag_push_clients( $title, $body = '' ) {
		return ag_tg_send( ag_tg_cfg( 'chan' ), $title . ( $body ? "\n\n" . $body : '' ) );
	}
}
add_action( 'admin_init', function () {
	foreach ( array( 'ag_tg_token', 'ag_tg_chat', 'ag_tg_chan', 'ag_tg_chan_link', 'ag_tg_group_link', 'ag_wa_phone', 'ag_wa_apikey', 'ag_wa_pro', 'ag_sms_free_user', 'ag_sms_free_key', 'ag_sms_webhook' ) as $opt ) {
		register_setting( 'ag_tg_cfg', $opt, array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	}
} );
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-push', 'Notifications téléphone', '📞 Notifs téléphone', 'manage_options', 'ag-notify', 'ag_notify_render' );
}, 20 );
add_action( 'admin_post_ag_push_test', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_push_test' ) ) wp_die( 'no' );
	$ok = ag_push( '✅ Test Alliance Groupe', 'Si tu lis ça, les alertes (SMS + groupe Telegram) marchent !', true );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-notify', 'test' => $ok ? 1 : 0 ), admin_url( 'options-general.php' ) ) ); exit;
} );
add_action( 'admin_post_ag_sms_test', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_sms_test' ) ) wp_die( 'no' );
	$ok = function_exists( 'ag_sms' ) && ag_sms( 'Test SMS Alliance Groupe : si tu lis ce SMS, tes alertes (inscriptions, messages, devis) fonctionnent !' );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-notify', 'smstest' => $ok ? 1 : 0 ), admin_url( 'options-general.php' ) ) ); exit;
} );
add_action( 'admin_post_ag_push_clients', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_push_clients' ) ) wp_die( 'no' );
	$msg = sanitize_textarea_field( wp_unslash( $_POST['msg'] ?? '' ) );
	$ok = ( '' !== $msg ) && ag_push_clients( $msg );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-notify', 'bc' => $ok ? 1 : 0 ), admin_url( 'options-general.php' ) ) ); exit;
} );
add_action( 'admin_post_ag_tg_detect', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_tg_detect' ) ) wp_die( 'no' );
	$token = ag_tg_cfg( 'token' ); $found = array();
	if ( $token ) {
		$resp = wp_remote_get( 'https://api.telegram.org/bot' . $token . '/getUpdates', array( 'timeout' => 15 ) );
		if ( ! is_wp_error( $resp ) ) {
			$d = json_decode( wp_remote_retrieve_body( $resp ), true );
			foreach ( (array) ( $d['result'] ?? array() ) as $u ) {
				$chat = $u['message']['chat'] ?? ( $u['my_chat_member']['chat'] ?? ( $u['channel_post']['chat'] ?? null ) );
				if ( $chat && isset( $chat['id'] ) ) {
					$name = $chat['title'] ?? trim( ( $chat['first_name'] ?? '' ) . ' ' . ( $chat['last_name'] ?? '' ) );
					$found[ (string) $chat['id'] ] = ( $name ?: ( $chat['username'] ?? '' ) ) . ' [' . ( $chat['type'] ?? '' ) . ']';
				}
			}
		}
	}
	set_transient( 'ag_tg_detected', $found, 300 );
	wp_safe_redirect( admin_url( 'options-general.php?page=ag-notify' ) ); exit;
} );
if ( ! function_exists( 'ag_notify_render' ) ) {
	function ag_notify_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$active = ( ag_tg_cfg( 'token' ) && ag_tg_cfg( 'chat' ) ) || ( get_option( 'ag_wa_phone' ) && get_option( 'ag_wa_apikey' ) ) || ( get_option( 'ag_sms_free_user' ) && get_option( 'ag_sms_free_key' ) ) || get_option( 'ag_sms_webhook' );
		?>
		<div class="wrap">
			<h1>📲 Notifications téléphone</h1>
			<p style="max-width:780px;color:#50575e;">Reçois une <strong>notification instantanée</strong> dès qu'un prospect répond (chat), passe « intéressé », ou qu'une vente tombe. Choisis <strong>WhatsApp</strong> et/ou <strong>Telegram</strong>.</p>
			<?php if ( isset( $_GET['test'] ) ) : ?>
				<div class="notice notice-<?php echo $_GET['test'] ? 'success' : 'error'; ?>"><p><?php echo $_GET['test'] ? 'Test envoyé ✅ Regarde ton téléphone.' : 'Échec : vérifie tes identifiants.'; ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['smstest'] ) ) : ?>
				<div class="notice notice-<?php echo $_GET['smstest'] ? 'success' : 'error'; ?>"><p><?php echo $_GET['smstest'] ? '📩 SMS envoyé ✅ Regarde ton téléphone (numéro Free).' : '❌ SMS non envoyé : vérifie l\'identifiant + la clé Free (ou le webhook).'; ?></p></div>
			<?php endif; ?>
			<div style="max-width:780px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #25D366;">
				<strong>📲 Option A — WhatsApp (sur TON numéro, gratuit via CallMeBot) :</strong>
				<ol style="margin:8px 0 0 22px;line-height:1.8;">
					<li>Ouvre la page officielle <a href="https://www.callmebot.com/blog/free-api-whatsapp-messages/" target="_blank" rel="noopener"><strong>callmebot.com (API WhatsApp)</strong></a> et récupère le <strong>numéro CallMeBot du moment</strong> (il change parfois — c'est pour ça qu'on ne le fige pas ici).</li>
					<li>Ajoute ce numéro en contact sur ton téléphone, puis envoie-lui sur WhatsApp : <code>I allow callmebot to send me messages</code></li>
					<li>Il te répond avec une <strong>API key</strong>. Mets ton <strong>numéro</strong> (format international, ex : 33612345678) + l'<strong>API key</strong> ci-dessous, puis « Envoyer un test ».</li>
				</ol>
				<p style="margin:10px 0 0;color:#b32d2e;"><strong>Important :</strong> WhatsApp n'autorise l'envoi automatique que vers <strong>ton numéro personnel</strong>, <strong>pas vers un groupe</strong>. Pour notifier <strong>toute l'équipe dans un groupe</strong>, utilise <strong>Telegram</strong> (option B) : crée un groupe Telegram, ajoute ton bot dedans, et mets le <strong>Chat ID du groupe</strong> (un nombre négatif).</p>
			</div>
			<div style="max-width:780px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<strong>✈ Option B — Telegram (canal interne équipe + canal général clients) :</strong>
				<ol style="margin:8px 0 0 22px;line-height:1.8;">
					<li><strong>@BotFather</strong> → <code>/newbot</code> → récupère le <strong>token</strong> → colle-le ci-dessous → <strong>Enregistre</strong>.</li>
					<li><strong>Ajoute ton bot dans le groupe/canal</strong> (comme membre pour un groupe ; comme <strong>administrateur</strong> pour un canal).</li>
					<li><strong>Récupérer l'ID (méthode infaillible) :</strong> ajoute <strong>@getidsbot</strong> (ou <strong>@RawDataBot</strong>) au groupe/canal → il affiche aussitôt l'<strong>ID</strong> (un nombre, négatif pour un groupe/canal) → copie-le → puis tu peux retirer ce bot.</li>
					<li>Colle l'ID dans <strong>« Canal interne »</strong> (équipe) et/ou <strong>« Canal général »</strong> (clients) → Enregistre → <strong>« Envoyer un test »</strong>.</li>
				</ol>
				<p style="margin:8px 0 0;color:#50575e;">Le bouton « 🔎 Détecter » marche aussi : écris <code>/start@TonBot</code> dans le groupe, puis clique Détecter. (S'il ne trouve rien : @BotFather → <code>/setprivacy</code> → ton bot → <strong>Disable</strong>.)</p>
			</div>
			<div style="max-width:780px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #6c5ce7;">
				<strong>📩 Option C — SMS sur ton téléphone (alertes inscriptions, messages, devis) :</strong>
				<p style="margin:8px 0 0;color:#b32d2e;"><strong>⚠ L'API SMS gratuite n'existe que chez Free Mobile.</strong> Sur <strong>SFR / Orange / Bouygues</strong>, pas d'API SMS perso gratuite.</p>
				<ul style="margin:6px 0 0 22px;line-height:1.8;">
					<li><strong>Chez Free Mobile</strong> → espace abonné Free → <em>Gérer mon compte → Mes options → Notifications par SMS</em> → active, copie l'<strong>identifiant</strong> + la <strong>clé</strong> ci-dessous. Gratuit, sur ta ligne.</li>
					<li><strong>Chez SFR / Orange / Bouygues</strong> → deux choix :
						<ul style="margin:4px 0 0 18px;">
							<li><strong>Gratuit (recommandé)</strong> : utilise <strong>Telegram</strong> (option B) ou <strong>WhatsApp</strong> (option A) — même effet qu'un SMS, notif instantanée sur ton tél, gratuit.</li>
							<li><strong>Vrai SMS</strong> : passerelle payante (≈0,05–0,08 €/SMS : Brevo, OVH SMS, smsmode, spot-hit, Twilio) reliée par <strong>webhook</strong>. Le plus simple : <strong>Make.com</strong> (ou Zapier) → « Catch hook » → action SMS. Colle l'URL avec <code>{msg}</code> ci-dessous.</li>
						</ul>
					</li>
				</ul>
				<p style="margin:8px 0 0;color:#50575e;">Dans tous les cas, <strong>chaque inscription ambassadeur, message client et demande de devis</strong> déclenche l'alerte (SMS / Telegram / WhatsApp selon ce que tu remplis) — et s'ajoute aussi à ton Google Agenda.</p>
			</div>
			<form method="post" action="options.php" style="max-width:780px;">
				<?php settings_fields( 'ag_tg_cfg' ); ?>
				<table class="form-table">
					<tr><th colspan="2" style="padding-bottom:0;"><span style="color:#6c5ce7;">SMS (alertes sur ton téléphone)</span></th></tr>
					<tr><th scope="row"><label for="ag_sms_free_user">Free Mobile — Identifiant</label></th><td><input type="text" name="ag_sms_free_user" id="ag_sms_free_user" value="<?php echo esc_attr( get_option( 'ag_sms_free_user', '' ) ); ?>" class="regular-text" style="width:260px;" placeholder="8 chiffres (login Free)"></td></tr>
					<tr><th scope="row"><label for="ag_sms_free_key">Free Mobile — Clé</label></th><td><input type="text" name="ag_sms_free_key" id="ag_sms_free_key" value="<?php echo esc_attr( get_option( 'ag_sms_free_key', '' ) ); ?>" class="regular-text" style="width:260px;" placeholder="clé d'API SMS Free"></td></tr>
					<tr><th scope="row"><label for="ag_sms_webhook">Webhook SMS (autre opérateur)</label></th><td><input type="url" name="ag_sms_webhook" id="ag_sms_webhook" value="<?php echo esc_attr( get_option( 'ag_sms_webhook', '' ) ); ?>" class="regular-text" style="width:100%;max-width:520px;" placeholder="https://…/send?text={msg}"><p class="description">Optionnel. L'URL doit contenir <code>{msg}</code> (remplacé par le message).</p></td></tr>
					<tr><th colspan="2" style="padding-bottom:0;"><span style="color:#25D366;">WhatsApp (CallMeBot)</span></th></tr>
					<tr><th scope="row"><label for="ag_wa_phone">Mon numéro WhatsApp</label></th><td><input type="text" name="ag_wa_phone" id="ag_wa_phone" value="<?php echo esc_attr( get_option( 'ag_wa_phone', '' ) ); ?>" class="regular-text" style="width:260px;" placeholder="33612345678 (format international)"></td></tr>
					<tr><th scope="row"><label for="ag_wa_apikey">API key CallMeBot</label></th><td><input type="text" name="ag_wa_apikey" id="ag_wa_apikey" value="<?php echo esc_attr( get_option( 'ag_wa_apikey', '' ) ); ?>" class="regular-text" style="width:260px;"></td></tr>
					<tr><th scope="row"><label for="ag_wa_pro">📱 Numéro WhatsApp Business (pro)</label></th><td><input type="text" name="ag_wa_pro" id="ag_wa_pro" value="<?php echo esc_attr( get_option( 'ag_wa_pro', '' ) ); ?>" class="regular-text" style="width:260px;" placeholder="33744829516 (format international, sans +)"><p class="description">Ton numéro WhatsApp Business (ex. ton 07 → <code>337…</code>). Sert au bouton « 💬 WhatsApp » du <strong>rapport client</strong> pour que le prospect te contacte directement.</p></td></tr>
					<tr><th colspan="2" style="padding-bottom:0;"><span style="color:#D4B45C;">Telegram</span></th></tr>
					<tr><th scope="row"><label for="ag_tg_token">Token du bot</label></th><td><input type="text" name="ag_tg_token" id="ag_tg_token" value="<?php echo esc_attr( ag_tg_cfg( 'token' ) ); ?>" class="regular-text" style="width:100%;max-width:520px;"></td></tr>
					<tr><th scope="row"><label for="ag_tg_chat">🔒 Canal INTERNE (équipe) — Chat ID</label></th><td><input type="text" name="ag_tg_chat" id="ag_tg_chat" value="<?php echo esc_attr( ag_tg_cfg( 'chat' ) ); ?>" class="regular-text" style="width:260px;"><p class="description">Reçoit les alertes prospects / ventes / leads. Groupe d'équipe (ID négatif).</p></td></tr>
					<tr><th scope="row"><label for="ag_tg_group_link">Lien d'invitation du groupe ÉQUIPE</label></th><td><input type="url" name="ag_tg_group_link" id="ag_tg_group_link" value="<?php echo esc_attr( ag_tg_cfg( 'group_link' ) ); ?>" class="regular-text" style="width:360px;" placeholder="https://t.me/+xxxxx (lien d'invitation du groupe privé)"><p class="description">Envoyé aux nouveaux ambassadeurs pour qu'ils rejoignent le groupe interne.</p></td></tr>
					<tr><th scope="row"><label for="ag_tg_chan">📣 Canal GÉNÉRAL (clients) — Chat ID</label></th><td><input type="text" name="ag_tg_chan" id="ag_tg_chan" value="<?php echo esc_attr( ag_tg_cfg( 'chan' ) ); ?>" class="regular-text" style="width:260px;"><p class="description">Pour diffuser des annonces aux clients (canal Telegram public, le bot doit en être admin).</p></td></tr>
					<tr><th scope="row"><label for="ag_tg_chan_link">Lien d'invitation du canal clients</label></th><td><input type="url" name="ag_tg_chan_link" id="ag_tg_chan_link" value="<?php echo esc_attr( ag_tg_cfg( 'chan_link' ) ); ?>" class="regular-text" style="width:360px;" placeholder="https://t.me/ton_canal"><p class="description"><?php echo $active ? '✓ Notifications internes actives.' : 'Tout vide = pas de notif (les emails continuent).'; ?></p></td></tr>
				</table>
				<h2 style="margin-top:20px;">📅 Message quotidien automatique aux clients</h2>
				<p class="description" style="max-width:780px;">Un message <strong>percutant</strong> (urgence + offre limitée + exclusivité) part <strong>chaque jour à 9h</strong> dans le canal clients. Tu peux ajouter les tiens (sépare chaque message par une ligne <code>---</code>).</p>
				<label style="display:block;margin:8px 0;"><input type="checkbox" name="ag_client_daily_on" value="1" <?php checked( get_option( 'ag_client_daily_on' ), '1' ); ?>> <strong>Activer le message quotidien automatique</strong></label>
				<textarea name="ag_client_msgs_custom" rows="6" style="width:100%;max-width:780px;" placeholder="Tes propres messages (optionnel). Sépare-les par une ligne contenant seulement : ---"><?php echo esc_textarea( get_option( 'ag_client_msgs_custom', '' ) ); ?></textarea>

				<h2 style="margin-top:20px;">🤝 Message quotidien automatique aux AMBASSADEURS</h2>
				<p class="description" style="max-width:780px;">Un message <strong>motivant</strong> (objectif du jour, rappel des gains, astuce de vente) part <strong>chaque jour à 8h30</strong> dans le <strong>groupe interne équipe</strong>. Tu peux ajouter les tiens (séparés par une ligne <code>---</code>).</p>
				<label style="display:block;margin:8px 0;"><input type="checkbox" name="ag_amb_daily_on" value="1" <?php checked( get_option( 'ag_amb_daily_on' ), '1' ); ?>> <strong>Activer le message quotidien automatique aux ambassadeurs</strong> (Telegram groupe)</label>
				<label style="display:block;margin:8px 0;"><input type="checkbox" name="ag_amb_sms_daily_on" value="1" <?php checked( get_option( 'ag_amb_sms_daily_on' ), '1' ); ?>> <strong>Envoyer aussi ce message par SMS</strong> (via la passerelle, à chaque ambassadeur qui a un numéro — motivation directe même sans Telegram)</label>
				<textarea name="ag_amb_msgs_custom" rows="6" style="width:100%;max-width:780px;" placeholder="Tes propres messages d'équipe (optionnel). Sépare-les par une ligne contenant seulement : ---"><?php echo esc_textarea( get_option( 'ag_amb_msgs_custom', '' ) ); ?></textarea>
				<?php submit_button(); ?>
			</form>
			<?php if ( isset( $_GET['bc'] ) ) : ?><div class="notice notice-<?php echo $_GET['bc'] ? 'success' : 'error'; ?>"><p><?php echo $_GET['bc'] ? 'Annonce envoyée aux clients ✅' : 'Échec : configure le canal clients.'; ?></p></div><?php endif; ?>
			<?php if ( isset( $_GET['sent'] ) ) : ?><div class="notice notice-<?php echo $_GET['sent'] ? 'success' : 'error'; ?>"><p><?php echo $_GET['sent'] ? 'Message du jour envoyé aux clients ✅' : 'Échec : configure le canal clients.'; ?></p></div><?php endif; ?>
			<?php if ( ag_tg_cfg( 'chan' ) ) : ?>
			<div style="max-width:780px;margin:10px 0;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;">
				<strong>Aperçu du message d'aujourd'hui :</strong>
				<p style="white-space:pre-wrap;background:#f6f7f7;padding:12px;border-radius:8px;margin:8px 0;"><?php echo esc_html( ag_client_message_today() ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ag_client_send_now">
					<?php wp_nonce_field( 'ag_client_now', '_n' ); ?>
					<?php submit_button( '📅 Envoyer celui d\'aujourd\'hui maintenant', 'secondary', 'submit', false ); ?>
				</form>
			</div>
			<?php endif; ?>
			<?php if ( ag_tg_cfg( 'chan' ) ) : ?>
			<div style="max-width:780px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #5ab0ff;">
				<strong>📣 Publier une annonce aux clients (canal général)</strong>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px;">
					<input type="hidden" name="action" value="ag_push_clients">
					<?php wp_nonce_field( 'ag_push_clients', '_n' ); ?>
					<textarea name="msg" rows="3" style="width:100%;" placeholder="Ex : 🎉 Nouvelle offre du mois — site pro dès 490 €, payable en 4× !"></textarea>
					<?php submit_button( '📣 Publier aux clients', 'primary', 'submit', false ); ?>
				</form>
			</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['asent'] ) ) : ?><div class="notice notice-<?php echo $_GET['asent'] ? 'success' : 'error'; ?>"><p><?php echo $_GET['asent'] ? 'Message du jour envoyé aux ambassadeurs ✅' : 'Échec : configure le canal interne (équipe).'; ?></p></div><?php endif; ?>
			<?php if ( isset( $_GET['abc'] ) ) : ?><div class="notice notice-<?php echo $_GET['abc'] ? 'success' : 'error'; ?>"><p><?php echo $_GET['abc'] ? 'Annonce envoyée aux ambassadeurs ✅' : 'Échec : configure le canal interne (équipe).'; ?></p></div><?php endif; ?>
			<?php if ( ag_tg_cfg( 'chat' ) ) : ?>
			<div style="max-width:780px;margin:10px 0;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<strong>Aperçu du message ambassadeurs d'aujourd'hui :</strong>
				<p style="white-space:pre-wrap;background:#f6f7f7;padding:12px;border-radius:8px;margin:8px 0;"><?php echo esc_html( ag_amb_message_today() ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ag_amb_send_now">
					<?php wp_nonce_field( 'ag_amb_now', '_n' ); ?>
					<?php submit_button( '🤝 Envoyer aux ambassadeurs maintenant', 'secondary', 'submit', false ); ?>
				</form>
			</div>
			<div style="max-width:780px;margin:16px 0;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #1e7e34;">
				<strong>📣 Publier une annonce aux ambassadeurs (groupe équipe)</strong>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px;">
					<input type="hidden" name="action" value="ag_amb_broadcast">
					<?php wp_nonce_field( 'ag_amb_tools', '_n' ); ?>
					<textarea name="msg" rows="3" style="width:100%;" placeholder="Ex : 🔥 Défi du week-end : 3 ventes = bonus surprise !"></textarea>
					<?php submit_button( '📣 Publier aux ambassadeurs', 'primary', 'submit', false ); ?>
				</form>
			</div>
			<?php endif; ?>
			<?php if ( $active ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:780px;display:inline;">
				<input type="hidden" name="action" value="ag_push_test">
				<?php wp_nonce_field( 'ag_push_test', '_n' ); ?>
				<?php submit_button( '📲 Test (canal interne)', 'secondary', 'submit', false ); ?>
			</form>
			<?php endif; ?>
			<?php if ( ( get_option( 'ag_sms_free_user' ) && get_option( 'ag_sms_free_key' ) ) || get_option( 'ag_sms_webhook' ) ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="ag_sms_test">
				<?php wp_nonce_field( 'ag_sms_test', '_n' ); ?>
				<?php submit_button( '📩 Test SMS', 'primary', 'submit', false ); ?>
			</form>
			<?php endif; ?>
			<?php if ( ag_tg_cfg( 'token' ) ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
				<input type="hidden" name="action" value="ag_tg_detect">
				<?php wp_nonce_field( 'ag_tg_detect', '_n' ); ?>
				<?php submit_button( '🔎 Détecter mon Chat ID', 'secondary', 'submit', false ); ?>
			</form>
			<p class="description" style="max-width:780px;">Pour un <strong>groupe</strong> : crée le groupe Telegram, ajoute ton bot dedans, écris un message dans le groupe, puis clique « Détecter ». Pour toi tout seul : écris « Bonjour » à ton bot puis « Détecter ».</p>
			<?php $detected = get_transient( 'ag_tg_detected' ); if ( is_array( $detected ) && $detected ) : delete_transient( 'ag_tg_detected' ); ?>
				<div style="max-width:780px;margin-top:8px;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;">
					<strong>Chats détectés (copie l'ID dans le champ « Chat ID » ci-dessus) :</strong>
					<ul style="margin:8px 0 0 18px;">
						<?php foreach ( $detected as $cid => $cname ) : ?>
							<li><code style="user-select:all;font-size:1.05em;"><?php echo esc_html( $cid ); ?></code> — <?php echo esc_html( $cname ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php endif; // fin bloc Telegram (token configuré) ?>
		</div>
		<?php
	}
}

/* ── 10. Message QUOTIDIEN automatique au canal clients ──────────── */
add_action( 'admin_init', function () {
	register_setting( 'ag_tg_cfg', 'ag_client_daily_on', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'ag_tg_cfg', 'ag_client_msgs_custom', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ) );
	register_setting( 'ag_tg_cfg', 'ag_amb_daily_on', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'ag_tg_cfg', 'ag_amb_sms_daily_on', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'ag_tg_cfg', 'ag_amb_msgs_custom', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ) );
} );
if ( ! function_exists( 'ag_client_messages' ) ) {
	/** Banque de messages pour CLIENTS EXISTANTS : fidélité, réductions, invitations privées, exclusivités. */
	function ag_client_messages() {
		$phone   = apply_filters( 'ag_contact_phone', '07 44 82 95 16' );
		$contact = home_url( '/contact' );
		$amb     = home_url( '/ambassadeurs' );
		$base = array(
			"🎁 Offre fidélité réservée à nos clients : -20% ce mois-ci sur l'ajout de pages, le SEO ou la maintenance de ton site. Réponds à ce message ou appelle le $phone.",
			"🔒 Invitation privée : on ouvre quelques places pour booster ton site (référencement Google + vitesse). Réservé à nos clients du canal. Intéressé ? Réponds ici.",
			"⭐ Exclu clients : passe ton site au niveau supérieur avec un module IA / automatisation (prise de RDV, chatbot, relances auto) à tarif préférentiel. Dis-moi si je t'en parle.",
			"📈 Tu veux plus de clients via ton site actuel ? On t'offre un mini-audit SEO gratuit (15 min) — réservé aux clients du canal. Réponds « AUDIT ».",
			"🤝 Tu connais un pro qui a besoin d'un site ? Recommande-le et gagne une récompense. C'est par ici 👉 $amb",
			"🥂 Merci d'être client Alliance Groupe. Cadeau du mois : une retouche/évolution offerte sur ton site (dans la limite du raisonnable). Réponds pour en profiter.",
			"🎟️ Offre flash 48h, clients uniquement : -15% sur une refonte ou un nouveau design. Places limitées. Appelle le $phone.",
			"🚀 Nouveau : on peut connecter ton site à WhatsApp / un agenda / un paiement en ligne. Nos clients d'abord. Tu veux voir ce que ça donne pour toi ?",
			"💡 Astuce clients : une page bien optimisée = plus d'appels. On te montre quoi améliorer en priorité, gratuitement. Réponds ici.",
			"📣 En avant-première pour le canal : nouvelle prestation dispo ce mois. Tarif lancement réservé aux clients existants. Je t'envoie les détails ?",
			"🔧 Ton site mérite d'être à jour (sécurité, vitesse, contenus). Pack maintenance en promo pour les clients du canal cette semaine. Infos : $contact",
			"⏳ Offre privée de la semaine, réservée à nos clients fidèles : -25% sur un service au choix (SEO, pages, IA). Premier arrivé, premier servi — réponds vite !",
		);
		$custom = array_filter( array_map( 'trim', preg_split( '/\n-{2,}\n/', (string) get_option( 'ag_client_msgs_custom', '' ) ) ) );
		return apply_filters( 'ag_client_messages', array_values( array_merge( $base, $custom ) ) );
	}
}
if ( ! function_exists( 'ag_client_message_today' ) ) {
	function ag_client_message_today() {
		$m = ag_client_messages();
		if ( empty( $m ) ) return '';
		return $m[ (int) gmdate( 'z' ) % count( $m ) ]; // rotation 1/jour
	}
}
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_client_daily' ) ) wp_schedule_event( strtotime( 'tomorrow 9:00' ), 'daily', 'ag_client_daily' );
} );
add_action( 'ag_client_daily', function () {
	if ( get_option( 'ag_client_daily_on' ) && ag_tg_cfg( 'chan' ) ) ag_push_clients( ag_client_message_today() );
} );
add_action( 'admin_post_ag_client_send_now', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_client_now' ) ) wp_die( 'no' );
	$ok = ag_push_clients( ag_client_message_today() );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-notify', 'sent' => $ok ? 1 : 0 ), admin_url( 'options-general.php' ) ) ); exit;
} );

/* ── Message QUOTIDIEN automatique au GROUPE AMBASSADEURS (motivation / vente) ── */
if ( ! function_exists( 'ag_amb_messages' ) ) {
	/** Banque de messages d'équipe : motivation, rappels de prospection, recrutement, studio. */
	function ag_amb_messages() {
		$base = array(
			"💪 Objectif du jour : 5 contacts. 5 messages = des ventes qui tombent. On y va, l'équipe !",
			"🔥 Rappel : 10% sur CHAQUE vente, sans plafond. Un site à 890 € = 89 € pour toi. Partage ton lien aujourd'hui.",
			"🎬 Pas d'idée de contenu ? Le Studio te génère une vidéo/visuel en 1 clic avec ton lien intégré. Poste-en une aujourd'hui.",
			"🎯 Fixe-toi 1 vente cette semaine : un site vendu = 10 % de commission directe pour toi. Relance tes prospects chauds.",
			"🗺️ Des prospects t'attendent dans ta zone — va les contacter avant qu'un autre le fasse.",
			"📞 La règle d'or : le 1er qui contacte gagne. Sois rapide, sois pro, sois toi.",
			"⭐ Le classement se joue cette semaine — grimpe en haut, fais-toi remarquer !",
			"🚀 1 vente change ta semaine. 1 par jour change ta vie. Aujourd'hui, qui est chaud ?",
			"💬 Un « non » n'est jamais définitif. Relance avec le sourire : beaucoup de « oui » sont juste des « pas encore ».",
			"🎯 Astuce : commence par les commerces SANS vrai site. Les plus faciles à convaincre.",
			"📈 Partage ton lien en story aujourd'hui : 1 story = des dizaines de vues = des ventes potentielles.",
			"🏆 Team Alliance, on joue collectif : entraide et bons plans dans le groupe. Ensemble on va plus loin.",
		);
		$custom = array_filter( array_map( 'trim', preg_split( '/\n-{2,}\n/', (string) get_option( 'ag_amb_msgs_custom', '' ) ) ) );
		return apply_filters( 'ag_amb_messages', array_values( array_merge( $base, $custom ) ) );
	}
}
if ( ! function_exists( 'ag_amb_message_today' ) ) {
	function ag_amb_message_today() {
		$m = ag_amb_messages();
		if ( empty( $m ) ) return '';
		return $m[ (int) gmdate( 'z' ) % count( $m ) ];
	}
}
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_amb_daily' ) ) wp_schedule_event( strtotime( 'tomorrow 8:30' ), 'daily', 'ag_amb_daily' );
} );
add_action( 'ag_amb_daily', function () {
	$msg_today = ag_amb_message_today();
	if ( get_option( 'ag_amb_daily_on' ) && ag_tg_cfg( 'chat' ) ) ag_tg_send( ag_tg_cfg( 'chat' ), $msg_today );
	// Motivation quotidienne AUSSI par SMS (via la passerelle) à chaque ambassadeur qui a un numéro.
	if ( '' !== trim( (string) $msg_today ) && get_option( 'ag_amb_sms_daily_on' )
		&& function_exists( 'ag_sms_send_bulk' ) && function_exists( 'ag_sms_gateway_ready' ) && ag_sms_gateway_ready() ) {
		$pairs = array(); $seen = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) {
			$ph = trim( (string) ( $a['phone'] ?? '' ) );
			if ( '' === $ph ) continue;
			$k = preg_replace( '/[^0-9]/', '', $ph );
			if ( '' === $k || isset( $seen[ $k ] ) ) continue; // anti-doublon numéro
			$seen[ $k ] = 1;
			$pairs[] = array( 'to' => $ph, 'msg' => $msg_today . ' STOP pour stop.' );
		}
		if ( $pairs ) ag_sms_send_bulk( $pairs );
	}
} );
add_action( 'admin_post_ag_amb_send_now', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_now' ) ) wp_die( 'no' );
	$ok = function_exists( 'ag_tg_send' ) && ag_tg_send( ag_tg_cfg( 'chat' ), ag_amb_message_today() );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-notify', 'asent' => $ok ? 1 : 0 ), admin_url( 'options-general.php' ) ) ); exit;
} );
