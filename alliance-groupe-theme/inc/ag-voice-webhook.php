<?php
/**
 * AG Robot vocal — webhook de compte-rendu d'appel.
 *
 * Reçoit le résultat de chaque appel d'un robot vocal IA (Retell / Bland / Vapi /
 * Vocode…) et met à jour le prospect correspondant dans le CRM, tout seul :
 *   - intéressé / rappel / devis   → statut « intéressé » (+ alerte)
 *   - ne plus appeler / opt-out    → « ne plus contacter » (+ opt-out SMS global)
 *   - pas intéressé / refus        → « refus »
 *   - répondeur / pas de réponse   → « sans réponse » (à relancer)
 *   - (défaut)                     → « contacté »
 *
 * Endpoint : POST /wp-json/ag/v1/voice?token=…   (jeton = option ag_voice_token)
 * Champs acceptés (souples, on prend le 1er non vide) :
 *   phone|to|from|number|customer_number  = le numéro appelé
 *   outcome|result|status|disposition|call_status = résultat brut
 *   summary|transcript|analysis|notes     = texte libre (analysé aussi par mots-clés)
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* --------------------------------------------------------------- Utilitaires */
if ( ! function_exists( 'ag_voice_deep_find' ) ) {
	/** Cherche RÉCURSIVEMENT la 1re valeur scalaire non vide pour l'une des clés (insensible à la casse),
	 *  quel que soit le niveau d'imbrication (Retell niche outcome dans call.call_analysis.custom_analysis_data…). */
	function ag_voice_deep_find( $node, $keys_lc, $depth = 0 ) {
		if ( $depth > 8 || ! is_array( $node ) ) return null;
		// 1) clés directes de ce niveau
		foreach ( $node as $key => $val ) {
			if ( is_array( $val ) ) continue;
			if ( '' === (string) $val ) continue;
			if ( in_array( strtolower( (string) $key ), $keys_lc, true ) ) return (string) $val;
		}
		// 2) descente récursive
		foreach ( $node as $val ) {
			if ( is_array( $val ) ) {
				$found = ag_voice_deep_find( $val, $keys_lc, $depth + 1 );
				if ( null !== $found && '' !== $found ) return $found;
			}
		}
		return null;
	}
}
if ( ! function_exists( 'ag_voice_pick' ) ) {
	/** 1re valeur non vide parmi des alias : params de requête d'abord, puis recherche profonde dans le JSON. */
	function ag_voice_pick( $req, $keys ) {
		// a) query string / form params (au 1er niveau).
		foreach ( $keys as $k ) {
			$v = $req->get_param( $k );
			if ( null !== $v && '' !== $v && ! is_array( $v ) ) return $v;
		}
		// b) recherche récursive dans le corps JSON (gère l'imbrication profonde de Retell/Vapi/Bland).
		$json    = $req->get_json_params();
		$keys_lc = array_map( 'strtolower', $keys );
		$found   = ag_voice_deep_find( is_array( $json ) ? $json : array(), $keys_lc );
		return ( null !== $found ) ? $found : '';
	}
}
if ( ! function_exists( 'ag_voice_map_outcome' ) ) {
	/** Normalise un résultat d'appel → array( statut_CRM, est_optout ). */
	function ag_voice_map_outcome( $raw, $summary ) {
		$s = ' ' . strtolower( (string) $raw . ' ' . (string) $summary ) . ' ';
		if ( preg_match( '/(do.?not.?call|ne plus (l\')?(appel|rappel|contact)|listes? rouge|opt.?out|retire|desinscri|stop demarchage)/u', $s ) ) return array( 'ne_pas_contacter', true );
		// Répondeur / messagerie vocale (PRIORITAIRE : sinon le « rappeler » du message vocal = faux « intéressé »).
		if ( preg_match( '/(apr[eè]s le bip|bip sonore|laisser (un )?message|laisser votre message|messagerie( vocale)?|bo[iî]te vocale|r[eé]pondeur|voicemail|answering machine|taper di[eè]se|apr[eè]s la tonalit|votre correspondant|je vais laisser un message)/u', $s ) ) return array( 'repondeur', false );
		// Refus AVANT intéressé (sinon « pas intéressé » matche « intéress »).
		if ( preg_match( '/(pas (du tout )?int[eé]ress|pas interes|not.?interes|aucun int[eé]r[êe]t|ne (m\'|nous )?int[eé]resse pas|\brefus|d[eé]clin|not_interested|rejected|pas besoin|d[eé]j[àa] (un|une|quelqu|notre))/u', $s ) ) return array( 'refus', false );
		if ( preg_match( '/(interes|intéress|rappel|call.?back|rendez|\brdv\b|devis|\boui\b|positif|positive|chaud|\bhot\b|interested)/u', $s ) ) return array( 'interesse', false );
		if ( preg_match( '/(voicemail|repondeur|répondeur|no.?answer|pas de repon|sans repon|occup|\bbusy\b|failed|echec|échec|non abouti|injoign)/u', $s ) ) return array( 'sans_reponse', false );
		if ( preg_match( '/(not.?interes|pas interes|refus|declin|déclin|negative|négatif|not_interested|rejected)/u', $s ) ) return array( 'refus', false );
		return array( 'contacte', false );
	}
}
if ( ! function_exists( 'ag_voice_status_label' ) ) {
	function ag_voice_status_label( $st ) {
		$m = array( 'interesse' => '🔥 Intéressé', 'ne_pas_contacter' => '⛔ Ne plus contacter', 'refus' => '🚫 Pas intéressé', 'sans_reponse' => '🔇 Sans réponse', 'repondeur' => '📵 Répondeur (à rappeler)', 'contacte' => '📞 Contacté' );
		return $m[ $st ] ?? $st;
	}
}
if ( ! function_exists( 'ag_voice_parse_rappel' ) ) {
	/**
	 * Convertit la date de rappel dite au robot en timestamp Unix, en fuseau
	 * Europe/Paris, TOUJOURS dans le futur. Accepte l'ISO propre d'Emma
	 * ("AAAA-MM-JJ HH:MM") OU du texte FR relatif ("demain 11h", "après-demain
	 * matin", "lundi 15h", "midi"). Retourne 0 si rien d'exploitable.
	 */
	function ag_voice_parse_rappel( $str ) {
		$str = trim( function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $str ) : strtolower( (string) $str ) );
		if ( '' === $str ) return 0;
		// Enlève les accents → regex fiables (après-demain, après-midi…).
		$str = strtr( $str, array( 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c' ) );
		try {
			$tz  = new DateTimeZone( 'Europe/Paris' );
			$now = new DateTime( 'now', $tz );
		} catch ( Exception $e ) {
			return (int) strtotime( str_replace( '/', '-', $str ) );
		}
		// 1) ISO "AAAA-MM-JJ[ T]HH:MM" → interprété en heure de Paris.
		if ( preg_match( '#(\d{4})[-/](\d{1,2})[-/](\d{1,2})(?:[ t]+(\d{1,2})[:h](\d{2}))?#', $str, $m ) ) {
			$d = DateTime::createFromFormat( 'Y-n-j', $m[1] . '-' . $m[2] . '-' . $m[3], $tz );
			if ( $d ) {
				$d->setTime( isset( $m[4] ) ? (int) $m[4] : 10, isset( $m[5] ) ? (int) $m[5] : 0 );
				if ( $d->getTimestamp() > $now->getTimestamp() ) return $d->getTimestamp();
			}
		}
		// 2) Texte FR relatif : jour.
		$base = clone $now;
		if ( preg_match( '/apres.?demain/', $str ) )         $base->modify( '+2 days' );
		elseif ( preg_match( '/\bdemain\b/', $str ) )        $base->modify( '+1 day' );
		elseif ( preg_match( '/aujourd|ce soir|cet? apr/', $str ) ) { /* aujourd'hui : on garde */ }
		else {
			$jours = array( 'lundi' => 1, 'mardi' => 2, 'mercredi' => 3, 'jeudi' => 4, 'vendredi' => 5, 'samedi' => 6, 'dimanche' => 0 );
			foreach ( $jours as $nom => $dow ) {
				if ( false !== strpos( $str, $nom ) ) {
					do { $base->modify( '+1 day' ); } while ( (int) $base->format( 'w' ) !== $dow );
					break;
				}
			}
		}
		// Heure : "11h", "11h30", "11:00", "midi", "minuit", ou en lettres "onze heures".
		$hour = null; $min = 0;
		if ( preg_match( '/(\d{1,2})\s*[h:]\s*(\d{2})?/', $str, $mh ) ) {
			$hour = (int) $mh[1]; $min = ( isset( $mh[2] ) && '' !== $mh[2] ) ? (int) $mh[2] : 0;
		} elseif ( preg_match( '/\bmidi\b/', $str ) ) { $hour = 12; }
		elseif ( preg_match( '/\bminuit\b/', $str ) ) { $hour = 0; }
		else {
			$mots = array( 'une' => 1, 'deux' => 2, 'trois' => 3, 'quatre' => 4, 'cinq' => 5, 'six' => 6, 'sept' => 7, 'huit' => 8, 'neuf' => 9, 'dix' => 10, 'onze' => 11, 'douze' => 12 );
			foreach ( $mots as $mot => $val ) { if ( preg_match( '/\b' . $mot . '\b\s*heures?/u', $str ) ) { $hour = $val; break; } }
		}
		// matin / après-midi / soir → ajuste sur 24h.
		if ( null !== $hour && preg_match( '/(apres.?midi|\bsoir)/', $str ) && $hour < 12 ) $hour += 12;
		if ( null === $hour ) $hour = 10; // défaut si l'heure n'est pas précisée
		$base->setTime( $hour, $min );
		if ( $base->getTimestamp() <= $now->getTimestamp() ) $base->modify( '+1 day' ); // jamais dans le passé
		return $base->getTimestamp();
	}
}

/* -------------------------------------------------------- Route REST /voice */
add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/voice', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ag_voice_rest',
	) );
} );
if ( ! function_exists( 'ag_voice_rest' ) ) {
	function ag_voice_rest( $req ) {
		// Auth par jeton.
		$token = get_option( 'ag_voice_token', '' );
		$given = $req->get_param( 'token' );
		if ( '' === $given ) $given = $req->get_header( 'x-ag-token' );
		if ( '' === $given ) $given = ag_voice_pick( $req, array( 'token' ) );
		if ( '' === $token || ! hash_equals( (string) $token, (string) $given ) ) {
			return new WP_REST_Response( array( 'error' => 'unauthorized' ), 401 );
		}
		// Ne traiter QUE l'événement d'ANALYSE finale. Retell/Vapi envoient aussi
		// « call_started » et « call_ended » (souvent AVEC le transcript) → si on les traitait
		// on créait 2 RDV pour le même appel. On ignore start + ended ; on garde « analyzed »
		// (et le cas sans champ event = envoi unique).
		$event = strtolower( (string) ag_voice_pick( $req, array( 'event', 'event_type', 'type' ) ) );
		if ( '' !== $event && false === strpos( $event, 'analy' )
			&& ( false !== strpos( $event, 'start' ) || false !== strpos( $event, 'end' ) ) ) {
			return new WP_REST_Response( array( 'ignored' => $event ), 200 );
		}
		$phone   = sanitize_text_field( (string) ag_voice_pick( $req, array( 'phone', 'to', 'from', 'number', 'customer_number', 'to_number', 'called', 'contact' ) ) );
		// Appel ENTRANT (quelqu'un appelle → Emma répond) : l'interlocuteur = l'APPELANT.
		$direction  = strtolower( (string) ag_voice_pick( $req, array( 'direction', 'call_direction', 'call_type' ) ) );
		$is_inbound = ( false !== strpos( $direction, 'inbound' ) || false !== strpos( $direction, 'entrant' ) );
		if ( $is_inbound ) {
			$caller = sanitize_text_field( (string) ag_voice_pick( $req, array( 'from', 'from_number', 'caller', 'caller_number', 'customer_number' ) ) );
			if ( '' !== $caller ) $phone = $caller;
		}
		$outcome = sanitize_text_field( (string) ag_voice_pick( $req, array( 'outcome', 'result', 'disposition', 'call_status', 'user_sentiment', 'call_successful' ) ) );
		$summary = sanitize_textarea_field( (string) ag_voice_pick( $req, array( 'summary', 'call_summary', 'analysis', 'notes', 'reason' ) ) );
		$transcript = sanitize_textarea_field( (string) ag_voice_pick( $req, array( 'transcript', 'call_transcript', 'full_transcript', 'transcription' ) ) );
		if ( '' === $summary ) $summary = $transcript; // repli : si pas de résumé, on garde le transcript comme résumé
		// Date/heure de rappel demandée par le client (Emma la renvoie en ISO "AAAA-MM-JJ HH:MM").
		$rappel    = sanitize_text_field( (string) ag_voice_pick( $req, array( 'rappel', 'rappel_iso', 'callback', 'callback_time', 'date_rappel', 'rendez_vous', 'best_time' ) ) );
		$rappel_ts = ( '' !== $rappel && function_exists( 'ag_voice_parse_rappel' ) ) ? ag_voice_parse_rappel( $rappel ) : ( '' !== $rappel ? (int) strtotime( str_replace( '/', '-', $rappel ) ) : 0 );
		if ( '' === $phone ) return new WP_REST_Response( array( 'error' => 'no_phone' ), 400 );
		// Aucun signal exploitable (ni résultat ni résumé) → on n'invente pas de statut.
		if ( '' === $outcome && '' === $summary ) return new WP_REST_Response( array( 'ignored' => 'no_signal' ), 200 );

		list( $status, $optout ) = ag_voice_map_outcome( $outcome, $summary );
		$stamp = current_time( 'd/m/Y H:i' );
		$note  = '📞 Robot vocal (' . $stamp . ') → ' . ag_voice_status_label( $status ) . ( '' !== $outcome ? ' [' . $outcome . ']' : '' ) . ( '' !== $summary ? ' : ' . $summary : '' );

		// Anti-doublon : un même appel (call_id) ne crée qu'UN RDV / une alerte.
		$call_id    = sanitize_text_field( (string) ag_voice_pick( $req, array( 'call_id', 'callId', 'conversation_id', 'conversationId', 'id' ) ) );
		$dedupe_key = '' !== $call_id ? $call_id : md5( $phone . '|' . gmdate( 'Y-m-d' ) . '|' . $status );
		$seen = get_option( 'ag_voice_seen', array() );
		if ( ! is_array( $seen ) ) $seen = array();
		foreach ( $seen as $k => $t ) { if ( ( time() - (int) $t ) > 2 * DAY_IN_SECONDS ) unset( $seen[ $k ] ); }
		$already = isset( $seen[ $dedupe_key ] );
		$seen[ $dedupe_key ] = time();
		update_option( 'ag_voice_seen', $seen, false );

		if ( $optout && function_exists( 'ag_sms_add_optout' ) ) ag_sms_add_optout( $phone ); // opt-out global (SMS + prospect)
		$nm = function_exists( 'ag_prospect_set_status_by_phone' ) ? ag_prospect_set_status_by_phone( $phone, $status, $note, $optout ) : '';

		// Journal de TOUS les appels (intéressés, refus, sans réponse…) avec le transcript complet.
		if ( ! $already ) {
			$log = get_option( 'ag_voice_log', array() );
			if ( ! is_array( $log ) ) $log = array();
			array_unshift( $log, array(
				'ts'         => time(),
				'phone'      => $phone,
				'name'       => $nm,
				'status'     => $status,
				'outcome'    => $outcome,
				'summary'    => $summary,
				'transcript' => $transcript,
				'rappel'     => $rappel,
				'call_id'    => $call_id,
			) );
			if ( count( $log ) > 300 ) $log = array_slice( $log, 0, 300 ); // garde les 300 derniers
			update_option( 'ag_voice_log', $log, false );
		}

		// Alerte. Pour un appel ENTRANT : on prévient TOUJOURS (comme une standardiste).
		$who = ( '' !== $nm ) ? $nm : $phone;
		$tag = $is_inbound
			? '📞 Robot : APPEL ENTRANT (' . ag_voice_status_label( $status ) . ')'
			: ( ( 'interesse' === $status ) ? '🔥 Robot vocal : prospect INTÉRESSÉ' : '📞 Robot vocal : ' . ag_voice_status_label( $status ) );
		if ( ! $already && ( $is_inbound || 'interesse' === $status || $optout ) ) {
			if ( function_exists( 'ag_sms' ) )  ag_sms( $tag . ' : ' . $who . ' — ' . $phone . ( '' !== $summary ? ' — ' . $summary : '' ) );
			if ( function_exists( 'ag_push' ) ) ag_push( $tag, $who . "\n📞 " . $phone . ( '' !== $summary ? "\n💬 " . $summary : '' ) );
		}
		// Appel entrant d'un inconnu → on crée un lead (source « appel entrant »).
		if ( $is_inbound && ! $already && '' === $nm && ! $optout && function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name'   => ( '' !== $who && $who !== $phone ) ? $who : 'Appel entrant ' . $phone,
				'phone'  => $phone,
				'type'   => 'Appel entrant',
				'status' => ( 'interesse' === $status ) ? 'interesse' : 'contacte',
				'source' => 'appel_entrant',
				'notes'  => $note,
			) );
		}
		// Prospect INTÉRESSÉ → RDV dans Google Agenda À LA DATE demandée (+ numéro du client),
		// avec rappel pop-up. Si aucune date captée, rappel immédiat "à rappeler vite".
		if ( ! $already && 'interesse' === $status && function_exists( 'ag_calendar_notify' ) ) {
			$when  = ( $rappel_ts > time() ) ? ' — ' . date_i18n( 'd/m à H\hi', $rappel_ts ) : '';
			$descr = "Prospect intéressé (robot vocal).\n📞 " . $phone
				. ( '' !== $rappel ? "\n🗓️ Rappel souhaité : " . $rappel : '' )
				. ( '' !== $summary ? "\n💬 " . $summary : '' )
				. ( '' !== $transcript && $transcript !== $summary ? "\n\n📝 Retranscription :\n" . $transcript : '' )
				. ( $rappel_ts > time() ? '' : "\nÀ rappeler vite." );
			ag_calendar_notify( '🔥 Rappeler : ' . $who . $when, $descr, '', $rappel_ts );
		}
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( $tag . ' : ' . $who );

		return new WP_REST_Response( array( 'ok' => true, 'matched' => ( '' !== $nm ), 'status' => $status ), 200 );
	}
}

/* ------------------------------------------------- Lancer un appel via Retell */
if ( ! function_exists( 'ag_voice_ready' ) ) {
	/** L'appel sortant est-il configurable ? (clé API Retell + numéro émetteur). */
	function ag_voice_ready() {
		return '' !== trim( (string) get_option( 'ag_voice_api_key', '' ) ) && '' !== trim( (string) get_option( 'ag_voice_from', '' ) );
	}
}
if ( ! function_exists( 'ag_voice_call' ) ) {
	/**
	 * Déclenche un appel sortant du robot vocal (Retell) vers $to, en injectant des
	 * variables dynamiques ({{entreprise}}, {{ville}}, {{angle}}=creation|securite) pour
	 * qu'Emma adapte son discours. Respecte l'opt-out. Retourne true si l'appel est lancé.
	 */
	function ag_voice_call( $to, $vars = array() ) {
		$key  = trim( (string) get_option( 'ag_voice_api_key', '' ) );
		$from = trim( (string) get_option( 'ag_voice_from', '' ) );
		$to   = function_exists( 'ag_sms_to_e164' ) ? ag_sms_to_e164( $to ) : $to;
		if ( '' === $key || '' === $from || '' === $to ) return false;
		if ( function_exists( 'ag_sms_is_optout' ) && ag_sms_is_optout( $to ) ) return false; // jamais un opt-out
		// Date/heure du jour (Paris) → permet à Emma de calculer « demain 11h » correctement.
		if ( empty( $vars['now'] ) ) {
			$vars['now'] = function_exists( 'wp_date' ) ? wp_date( 'l j F Y, H\hi' ) : date_i18n( 'l j F Y, H\hi' );
		}
		// Accroche = TOUT PREMIER message d'Emma, différent selon l'angle choisi (bouton « Appel site » vs « Appel sécu »).
		if ( empty( $vars['accroche'] ) ) {
			$angle = $vars['angle'] ?? '';
			if ( 'securite' === $angle ) {
				$vars['accroche'] = "Bonjour, c'est Emma d'Alliance Groupe, un assistant automatique. Je vous appelle au sujet de la sécurité de votre site internet, j'ai une bonne nouvelle à vous annoncer. Vous avez trente secondes ?";
			} else {
				$vars['accroche'] = "Bonjour, c'est Emma d'Alliance Groupe, un assistant automatique. J'ai une belle surprise à vous faire découvrir pour votre établissement. Vous avez trente secondes ?";
			}
		}
		$body = array( 'from_number' => $from, 'to_number' => $to );
		if ( ! empty( $vars ) ) $body['retell_llm_dynamic_variables'] = array_map( 'strval', $vars );
		$r = wp_remote_post( 'https://api.retellai.com/v2/create-phone-call', array(
			'timeout' => 20,
			'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		) );
		return ! is_wp_error( $r ) && in_array( (int) wp_remote_retrieve_response_code( $r ), array( 200, 201 ), true );
	}
}
/* Appels groupés depuis la liste des prospects (sélection). */
/* Appel robot depuis l'APP (membre connecté) : un seul numéro, angle site/sécu. */
add_action( 'wp_ajax_ag_app_voice_call', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Session expirée.' ) );
	if ( ! function_exists( 'ag_voice_call' ) || ! ag_voice_ready() ) wp_send_json_error( array( 'm' => 'Robot vocal pas encore configuré.' ) );
	$to = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	if ( '' === $to ) wp_send_json_error( array( 'm' => 'Numéro manquant.' ) );
	$angle = sanitize_text_field( wp_unslash( $_POST['angle'] ?? '' ) );
	$angle = in_array( $angle, array( 'creation', 'securite' ), true ) ? $angle : 'creation';
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$ok = ag_voice_call( $to, array( 'angle' => $angle, 'entreprise' => ( '' !== $name ? $name : 'votre établissement' ) ) );
	$ok ? wp_send_json_success() : wp_send_json_error( array( 'm' => 'Appel refusé (numéro invalide ou en opt-out).' ) );
} );
add_action( 'wp_ajax_ag_prospect_voice_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	if ( ! function_exists( 'ag_voice_call' ) || ! ag_voice_ready() ) wp_send_json_error( array( 'msg' => 'robot vocal non configuré' ) );
	$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$ids = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $ids ) ) );
	if ( empty( $ids ) ) wp_send_json_error();
	// Angle forcé (creation|securite) ou 'auto' (déduit du site).
	$forced = sanitize_text_field( wp_unslash( $_POST['angle'] ?? '' ) );
	$forced = in_array( $forced, array( 'creation', 'securite' ), true ) ? $forced : '';
	$ok = 0; $ko = 0;
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
		if ( ! in_array( $p['id'] ?? '', $ids, true ) ) continue;
		if ( function_exists( 'ag_prospect_blocked' ) && ag_prospect_blocked( $p['status'] ?? '' ) ) continue; // jamais aux bloqués
		$to = $p['phone_intl'] ?? ''; if ( '' === $to ) $to = $p['phone'] ?? '';
		if ( '' === $to ) continue;
		$has_site = function_exists( 'ag_site_kind' ) && 'real' === ag_site_kind( $p['website'] ?? '' )[0];
		$vars = array(
			'entreprise' => $p['name'] ?? '',
			'ville'      => $p['city'] ?? '',
			'angle'      => '' !== $forced ? $forced : ( $has_site ? 'securite' : 'creation' ),
		);
		if ( ag_voice_call( $to, $vars ) ) $ok++; else $ko++;
		usleep( 300000 ); // 0,3 s entre 2 lancements
	}
	if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🤖 Robot vocal : ' . $ok . ' appel(s) lancé(s), ' . $ko . ' échec(s)' );
	wp_send_json_success( array( 'ok' => $ok, 'ko' => $ko ) );
} );

/* ----------------------------------------------------- Réglages POST */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_voice_save'] ) && check_admin_referer( 'ag_voice' ) ) {
		if ( isset( $_POST['ag_voice_api_key'] ) ) update_option( 'ag_voice_api_key', sanitize_text_field( wp_unslash( $_POST['ag_voice_api_key'] ) ) );
		if ( isset( $_POST['ag_voice_from'] ) )    update_option( 'ag_voice_from', sanitize_text_field( wp_unslash( $_POST['ag_voice_from'] ) ) );
		if ( isset( $_POST['ag_calendar_email'] ) ) { $ce = sanitize_email( wp_unslash( $_POST['ag_calendar_email'] ) ); if ( '' === $ce || is_email( $ce ) ) update_option( 'ag_calendar_email', $ce ); }
			if ( isset( $_POST['ag_calendar_webhook'] ) ) { $wh = esc_url_raw( trim( wp_unslash( $_POST['ag_calendar_webhook'] ) ) ); update_option( 'ag_calendar_webhook', $wh ); }
			if ( '' === get_option( 'ag_calendar_secret', '' ) ) update_option( 'ag_calendar_secret', wp_generate_password( 20, false ) );
		if ( isset( $_POST['ag_voice_regen'] ) || '' === get_option( 'ag_voice_token', '' ) ) {
			update_option( 'ag_voice_token', wp_generate_password( 24, false ) );
		}
	}
	// Appel de test vers un numéro libre (ex. ton mobile perso).
	if ( isset( $_POST['ag_voice_test'] ) && check_admin_referer( 'ag_voice' ) ) {
		$to = sanitize_text_field( wp_unslash( $_POST['ag_voice_test_to'] ?? '' ) );
		$ok = function_exists( 'ag_voice_call' ) && ag_voice_ready() && ag_voice_call( $to, array( 'entreprise' => 'votre établissement', 'angle' => 'creation' ) );
		add_settings_error( 'ag_voice', 'test', $ok ? '📞 Appel lancé vers ' . esc_html( $to ) . ' — décroche !' : 'Échec : vérifie la clé API + le numéro émetteur, et le format du numéro (+33…).', $ok ? 'updated' : 'error' );
	}
} );

/* ------------------------------------------------------------- Page d'admin */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Robot vocal IA', '🤖 Robot vocal', 'manage_options', 'ag-voice', 'ag_voice_render' );
	add_submenu_page( 'ag-prospects', 'Journal des appels', '📞 Journal des appels', 'manage_options', 'ag-voice-log', 'ag_voice_log_render' );
}, 20 );

/* Journal de tous les appels du robot (avec transcript complet). */
if ( ! function_exists( 'ag_voice_log_render' ) ) {
	function ag_voice_log_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		// Vider le journal.
		if ( isset( $_POST['ag_voice_log_clear'] ) && check_admin_referer( 'ag_voice_log' ) ) {
			delete_option( 'ag_voice_log' );
			echo '<div class="notice notice-success is-dismissible"><p>Journal vidé.</p></div>';
		}
		$log = get_option( 'ag_voice_log', array() );
		if ( ! is_array( $log ) ) $log = array();
		echo '<div class="wrap"><h1>📞 Journal des appels du robot</h1>';
		echo '<p class="description">Tous les appels d\'Emma (intéressés, refus, sans réponse…) avec la retranscription complète. Les 300 derniers.</p>';
		if ( empty( $log ) ) {
			echo '<p style="margin-top:20px;font-size:15px;">Aucun appel enregistré pour l\'instant. Dès qu\'Emma passe un appel (et que Retell envoie l\'analyse au webhook), il apparaîtra ici.</p></div>';
			return;
		}
		echo '<form method="post" style="margin:10px 0 18px;">' . wp_nonce_field( 'ag_voice_log', '_wpnonce', true, false );
		echo '<button class="button" name="ag_voice_log_clear" value="1" onclick="return confirm(\'Vider tout le journal des appels ?\');">🗑️ Vider le journal</button> <span style="color:#50575e;">' . count( $log ) . ' appel(s)</span></form>';
		foreach ( $log as $e ) {
			$lbl   = function_exists( 'ag_voice_status_label' ) ? ag_voice_status_label( $e['status'] ?? '' ) : ( $e['status'] ?? '' );
			$when  = ! empty( $e['ts'] ) ? date_i18n( 'd/m/Y à H\hi', (int) $e['ts'] ) : '';
			$who   = ! empty( $e['name'] ) ? $e['name'] : ( $e['phone'] ?? '' );
			$trans = trim( (string) ( $e['transcript'] ?? '' ) );
			if ( '' === $trans ) $trans = trim( (string) ( $e['summary'] ?? '' ) );
			echo '<div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid #2271b1;border-radius:8px;padding:12px 14px;margin-bottom:12px;max-width:900px;">';
			echo '<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;align-items:baseline;">';
			echo '<strong style="font-size:15px;">' . esc_html( $who ) . '</strong>';
			echo '<span style="color:#50575e;font-size:12px;">' . esc_html( $when ) . '</span></div>';
			echo '<div style="margin:4px 0 8px;"><span style="display:inline-block;background:#f0f0f1;border-radius:100px;padding:2px 10px;font-size:12px;font-weight:600;">' . esc_html( $lbl ) . '</span> ';
			echo '<span style="color:#50575e;font-size:12px;">📞 ' . esc_html( $e['phone'] ?? '' ) . ( ! empty( $e['rappel'] ) ? ' · 🗓️ rappel : ' . esc_html( $e['rappel'] ) : '' ) . '</span></div>';
			if ( '' !== $trans ) {
				echo '<details><summary style="cursor:pointer;color:#2271b1;font-weight:600;">📝 Voir la retranscription</summary>';
				echo '<pre style="white-space:pre-wrap;background:#f6f7f7;border-radius:6px;padding:10px;margin-top:8px;font-size:13px;line-height:1.5;">' . esc_html( $trans ) . '</pre></details>';
			} else {
				echo '<em style="color:#787c82;font-size:13px;">(pas de retranscription pour cet appel)</em>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
if ( ! function_exists( 'ag_voice_render' ) ) {
	function ag_voice_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$tok = get_option( 'ag_voice_token', '' );
		$url = rest_url( 'ag/v1/voice' );
		echo '<div class="wrap"><h1>🤖 Robot vocal IA — compte-rendu d\'appel</h1>';
		settings_errors( 'ag_voice' );
		echo '<p style="max-width:820px;color:#50575e;">Branche un robot vocal IA (Retell, Bland, Vapi, Vocode…) qui appelle les <strong>fixes 02/04</strong> (là où le SMS ne passe pas). À la fin de chaque appel, le robot envoie son compte-rendu à l\'URL ci-dessous → le <strong>prospect est mis à jour tout seul</strong> dans ton CRM.</p>';

		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">1. URL du webhook (à coller dans ton outil vocal)</h2>';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>URL (méthode POST)</th><td><input type="text" readonly value="' . esc_attr( add_query_arg( 'token', $tok ?: 'GENERE-LE-CI-DESSOUS', $url ) ) . '" style="width:640px" onclick="this.select()"></td></tr>';
		echo '<tr><th>Jeton de sécurité</th><td><code style="font-size:13px;">' . esc_html( $tok ?: '(génère-le en enregistrant)' ) . '</code>';
		echo '<form method="post" style="display:inline;margin-left:10px;">' . wp_nonce_field( 'ag_voice', '_wpnonce', true, false );
		echo '<label><input type="checkbox" name="ag_voice_regen" value="1"> régénérer</label> <button name="ag_voice_save" value="1" class="button button-small">Enregistrer</button></form></td></tr>';
		echo '</tbody></table>';
		echo '<p class="description">Champs attendus (souples) : <code>phone</code> (numéro appelé), <code>outcome</code> (résultat), <code>summary</code> (résumé/transcript). Le jeton passe en <code>?token=…</code> ou header <code>X-AG-Token</code>.</p>';
		echo '</div>';

		// ── Lancer des appels depuis WordPress (API Retell) ──
		$api  = get_option( 'ag_voice_api_key', '' );
		$from = get_option( 'ag_voice_from', '' );
		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">1 bis. Lancer les appels depuis la liste des prospects</h2>';
		echo '<p style="color:#50575e;">Renseigne ta <strong>clé API Retell</strong> et ton <strong>numéro émetteur</strong> → le bouton « 📞 Appeler au robot (sélection) » apparaît dans <strong>Prospection</strong>. Emma <strong>adapte son discours</strong> : prospect sans site → <em>création</em> ; avec site → <em>audit sécurité</em> (variable <code>{{angle}}</code>).</p>';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo wp_nonce_field( 'ag_voice', '_wpnonce', true, false );
		echo '<tr><th>Clé API Retell</th><td><input type="text" name="ag_voice_api_key" value="' . esc_attr( $api ) . '" style="width:420px" placeholder="key_..."><p class="description">Retell → Paramètres → API Keys.</p></td></tr>';
		echo '<tr><th>Numéro émetteur (le numéro Retell)</th><td><input type="text" name="ag_voice_from" value="' . esc_attr( $from ) . '" style="width:240px" placeholder="+14632982363"><p class="description">Le numéro acheté dans Retell, attaché à l\'agent Emma.</p></td></tr>';
		$cal_email = get_option( 'ag_calendar_email', 'advise.alliance.group@gmail.com' );
		echo '<tr><th>📅 Email de l\'agenda Google (RDV)</th><td><input type="email" name="ag_calendar_email" value="' . esc_attr( $cal_email ) . '" style="width:320px" placeholder="ton.email@gmail.com"><p class="description">Utilisé <strong>seulement</strong> si le pont ci-dessous est vide (repli par invitation .ics). ⚠️ Doit être le compte que tu <strong>consultes vraiment</strong>.</p></td></tr>';
		$cal_hook   = get_option( 'ag_calendar_webhook', '' );
		if ( '' === get_option( 'ag_calendar_secret', '' ) ) update_option( 'ag_calendar_secret', wp_generate_password( 20, false ) );
		$cal_secret = get_option( 'ag_calendar_secret', '' );
		echo '<tr><th>🗓️ Pont agenda direct (Google Script)</th><td>';
		echo '<input type="url" name="ag_calendar_webhook" value="' . esc_attr( $cal_hook ) . '" style="width:100%;max-width:560px" placeholder="https://script.google.com/macros/s/…/exec">';
		echo '<p class="description"><strong>Recommandé (fiable + gratuit).</strong> Colle ici l\'URL de ton script Google (déploiement « application web »). Les RDV s\'écrivent alors <strong>directement</strong> dans ton agenda, sans e-mail ni spam. ' . ( $cal_hook ? '<span style="color:#16a34a;font-weight:600">🟢 Pont actif</span>' : '<span style="color:#b91c1c;font-weight:600">🔴 Pont non configuré (repli e-mail)</span>' ) . '</p>';
		echo '<p class="description">🔑 Secret à recopier dans le script (variable <code>SECRET</code>) : <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;user-select:all">' . esc_html( $cal_secret ) . '</code></p>';
		echo '</td></tr>';
		echo '</tbody></table><button name="ag_voice_save" value="1" class="button button-primary">Enregistrer</button> ';
		echo '<span style="margin-left:10px;">État : ' . ( ag_voice_ready() ? '🟢 prêt à appeler' : '🔴 à configurer' ) . '</span></form>';
		// Appel de test vers un numéro libre (mobile perso).
		echo '<hr style="margin:16px 0;"><h3 style="margin:0 0 6px;">📞 Tester un appel</h3>';
		echo '<p class="description" style="margin:0 0 8px;">Emma appelle le numéro que tu indiques (ton mobile perso par ex.) — pour tester la voix en conditions réelles.</p>';
		echo '<form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">' . wp_nonce_field( 'ag_voice', '_wpnonce', true, false );
		echo '<input type="text" name="ag_voice_test_to" placeholder="+33612345678" style="width:220px"' . ( ag_voice_ready() ? '' : ' disabled' ) . '>';
		echo '<button name="ag_voice_test" value="1" class="button"' . ( ag_voice_ready() ? '' : ' disabled title="Configure d\'abord la clé API + le numéro"' ) . '>Lancer l\'appel de test</button>';
		echo '<span class="description">Format international : <code>+33…</code> (remplace le 0 par +33).</span></form>';
		echo '</div>';

		// ── Liste opt-out (STOP / ne plus contacter) : voir + retirer ──
		$optout = (array) get_option( 'ag_sms_optout', array() );
		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">📵 Liste « ne plus contacter » (opt-out)</h2>';
		echo '<p style="color:#50575e;">Numéros qui ont dit STOP (ou marqués « ne plus appeler ») : ils ne reçoivent <strong>plus aucun SMS ni appel</strong>. Tu peux en <strong>retirer un</strong> (ex. ton propre numéro ajouté pendant les tests).</p>';
		if ( isset( $_GET['optsync'] ) ) echo '<div class="notice notice-success inline"><p>✅ ' . (int) $_GET['optsync'] . ' prospect(s) bloqué(s) ajouté(s) à la liste opt-out.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0 0 12px;"><input type="hidden" name="action" value="ag_optout_sync"><input type="hidden" name="_n" value="' . esc_attr( wp_create_nonce( 'ag_optout' ) ) . '"><button class="button">🔄 Rapatrier ici les prospects déjà « ne plus contacter »</button></form>';
		if ( empty( $optout ) ) {
			echo '<p style="color:#50575e;"><em>Aucun numéro en opt-out pour l\'instant.</em></p>';
		} else {
			echo '<table class="widefat striped" style="max-width:520px;"><thead><tr><th>Numéro</th><th style="width:120px;"></th></tr></thead><tbody>';
			foreach ( $optout as $k ) {
				$disp = ( 0 === strpos( $k, '33' ) ) ? '+' . $k : $k;
				echo '<tr><td><code>' . esc_html( $disp ) . '</code></td><td>';
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0;">';
				echo '<input type="hidden" name="action" value="ag_optout_remove"><input type="hidden" name="_n" value="' . esc_attr( wp_create_nonce( 'ag_optout' ) ) . '"><input type="hidden" name="num" value="' . esc_attr( $disp ) . '">';
				echo '<button class="button button-small" onclick="return confirm(\'Autoriser à nouveau le contact de ' . esc_attr( $disp ) . ' ?\')">✅ Retirer</button></form>';
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}
		echo '<p class="description" style="margin-top:8px;">💡 Retirer d\'ici n\'annule pas le statut « ne plus contacter » d\'un prospect dans la liste : pour le réactiver, change aussi son statut dans <strong>Prospection</strong>.</p>';
		echo '</div>';

		echo '<div style="max-width:900px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">2. Comment le résultat met à jour le prospect</h2>';
		echo '<table class="widefat striped"><thead><tr><th>Le robot renvoie…</th><th>Le prospect passe en…</th></tr></thead><tbody>';
		echo '<tr><td>intéressé / rappel / devis / RDV</td><td><strong>🔥 Intéressé</strong> + alerte SMS/Telegram</td></tr>';
		echo '<tr><td>ne plus appeler / opt-out</td><td><strong>⛔ Ne plus contacter</strong> + opt-out global (SMS aussi)</td></tr>';
		echo '<tr><td>pas intéressé / refus</td><td>🚫 Pas intéressé</td></tr>';
		echo '<tr><td>répondeur / pas de réponse / occupé</td><td>🔇 Sans réponse (à relancer)</td></tr>';
		echo '<tr><td>(autre)</td><td>📞 Contacté</td></tr>';
		echo '</tbody></table>';
		echo '<p class="description">La correspondance se fait sur les <strong>9 derniers chiffres</strong> du numéro (tolère +33 vs 0). Un prospect « client » n\'est jamais modifié.</p>';
		echo '</div>';

		echo '<div style="max-width:900px;padding:14px 18px;background:#fff8e5;border:1px solid #f0c36d;border-radius:8px;margin:12px 0;">';
		echo '<h2 style="margin-top:0;">⚠️ Obligations légales (à respecter dans le script du robot)</h2>';
		echo '<ul style="margin:0;line-height:1.7;">';
		echo '<li><strong>Transparence IA</strong> : la voix doit annoncer dès le début qu\'elle est un <strong>assistant vocal automatique</strong>.</li>';
		echo '<li><strong>Numéro dédié démarchage</strong> (01 62/63, 09 48/49…) via un opérateur VoIP (OVH, Ringover…) — pas un mobile perso.</li>';
		echo '<li><strong>Opt-out</strong> : si l\'interlocuteur dit « ne me rappelez plus », le robot doit le marquer → « ne plus contacter » (auto ici).</li>';
		echo '<li>Cibler des <strong>professionnels</strong> (B2B) ; horaires légaux ; pas de harcèlement.</li>';
		echo '</ul></div>';
		echo '</div>';
	}
}
