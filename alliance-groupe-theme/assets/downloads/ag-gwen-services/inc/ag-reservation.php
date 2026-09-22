<?php
/**
 * ag-reservation.php — Réservation en ligne + espace client (thème Gwen Services).
 *
 * Autonome : ce thème n'a ni compte client ni helpers d'email de la maison, donc
 * tout est ici. Trois choses :
 *
 * 1. RÉSERVATION EN TEMPS RÉEL, calée sur le planning de Gwen (matins = gestion,
 *    non réservables ; après-midis = interventions, réservables). Un créneau
 *    pris disparaît à la seconde ; on revérifie au moment de valider — pas de
 *    double réservation.
 *
 * 2. AGENDA : chaque réservation envoie une invitation .ics au client ET à Gwen
 *    (un clic pour l'ajouter, Google/Apple/Outlook). Gwen s'abonne UNE fois à un
 *    lien privé (`/wp-json/ag/v1/gwen.ics?token=…`) : son agenda se remplit et
 *    se met à jour tout seul, annulations comprises. Aucun OAuth.
 *
 * 3. ESPACE CLIENT : inscription / connexion en façade (pas wp-login), espace
 *    perso avec ses rendez-vous (voir / annuler) et ses coordonnées, réutilisées
 *    à chaque réservation.
 *
 * Le visuel reste sobre et neutre : l'habillage « à l'image du site » relève de
 * la lane design. Ici, la structure et la mécanique.
 *
 * @package ag-gwen-services
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_RESA_VER' ) ) { define( 'AG_RESA_VER', '1.0.0' ); }
if ( ! defined( 'AG_RESA_JOURS' ) ) { define( 'AG_RESA_JOURS', 21 ); }

/* ── 1. Réglages & référentiel ───────────────────────────────────────── */

if ( ! function_exists( 'ag_resa_opt' ) ) {
	function ag_resa_opt( $cle, $defaut = '' ) {
		$v = get_option( 'ag_resa_' . $cle, null );
		return ( null === $v || '' === $v ) ? $defaut : $v;
	}
}
if ( ! function_exists( 'ag_resa_gwen_email' ) ) {
	function ag_resa_gwen_email() {
		$m = sanitize_email( (string) ag_resa_opt( 'gwen_email', '' ) );
		return is_email( $m ) ? $m : (string) get_option( 'admin_email' );
	}
}
if ( ! function_exists( 'ag_resa_slot_min' ) ) {
	function ag_resa_slot_min() { return max( 30, (int) ag_resa_opt( 'slot_min', 60 ) ); }
}
if ( ! function_exists( 'ag_resa_services' ) ) {
	function ag_resa_services() {
		return apply_filters( 'ag_resa_services', array(
			'menage'         => 'Ménage',
			'courses'        => 'Courses',
			'accompagnement' => 'Accompagnement (sorties, rendez-vous)',
			'aide_personne'  => 'Aide à la personne',
		) );
	}
}
if ( ! function_exists( 'ag_resa_planning' ) ) {
	/** Fenêtres d'intervention (1=lundi … 7=dimanche). Matins = gestion, non ouverts. */
	function ag_resa_planning() {
		return apply_filters( 'ag_resa_planning', array(
			1 => array( array( '14:00', '17:00' ) ),
			2 => array( array( '14:00', '17:00' ) ),
			3 => array( array( '14:00', '16:30' ) ),
			4 => array( array( '14:00', '17:00' ) ),
			5 => array( array( '14:00', '16:00' ) ),
		) );
	}
}
if ( ! function_exists( 'ag_resa_tz' ) ) {
	function ag_resa_tz() { return wp_timezone(); }
}

/* ── 2. Stockage ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_resa_all' ) ) {
	function ag_resa_all() { return (array) get_option( 'ag_resa_bookings', array() ); }
}
if ( ! function_exists( 'ag_resa_save_all' ) ) {
	function ag_resa_save_all( $l ) { update_option( 'ag_resa_bookings', (array) $l, false ); }
}
if ( ! function_exists( 'ag_resa_feed_token' ) ) {
	function ag_resa_feed_token() {
		$t = (string) get_option( 'ag_resa_feed_token', '' );
		if ( '' === $t ) { $t = wp_generate_password( 24, false, false ); update_option( 'ag_resa_feed_token', $t, false ); }
		return $t;
	}
}

/* ── 3. Disponibilités (temps réel) ──────────────────────────────────── */

if ( ! function_exists( 'ag_resa_libre' ) ) {
	function ag_resa_libre( $start, $dur, $excl = '' ) {
		$fin = $start + $dur * 60;
		foreach ( ag_resa_all() as $b ) {
			if ( 'confirme' !== ( $b['status'] ?? '' ) ) { continue; }
			if ( $excl && (string) ( $b['id'] ?? '' ) === (string) $excl ) { continue; }
			$bs = (int) ( $b['start'] ?? 0 ); $bf = $bs + (int) ( $b['dur'] ?? 60 ) * 60;
			if ( $start < $bf && $bs < $fin ) { return false; }
		}
		return true;
	}
}
if ( ! function_exists( 'ag_resa_slots_jour' ) ) {
	function ag_resa_slots_jour( $ymd ) {
		$tz = ag_resa_tz();
		try { $d0 = new DateTime( $ymd . ' 00:00:00', $tz ); } catch ( Exception $e ) { return array(); }
		$dow  = (int) $d0->format( 'N' );
		$plan = ag_resa_planning();
		if ( empty( $plan[ $dow ] ) ) { return array(); }
		$dur = ag_resa_slot_min(); $now = time(); $slots = array();
		foreach ( (array) $plan[ $dow ] as $f ) {
			try { $deb = new DateTime( $ymd . ' ' . $f[0], $tz ); $fin = new DateTime( $ymd . ' ' . $f[1], $tz ); }
			catch ( Exception $e ) { continue; }
			$t = (int) $deb->getTimestamp(); $tf = (int) $fin->getTimestamp();
			while ( $t + $dur * 60 <= $tf ) {
				if ( $t > $now + 3600 && ag_resa_libre( $t, $dur ) ) { $slots[] = $t; }
				$t += $dur * 60;
			}
		}
		return $slots;
	}
}
if ( ! function_exists( 'ag_resa_prochains_jours' ) ) {
	function ag_resa_prochains_jours() {
		$tz = ag_resa_tz(); $out = array();
		for ( $i = 0; $i <= AG_RESA_JOURS; $i++ ) {
			try { $d = new DateTime( 'now', $tz ); } catch ( Exception $e ) { break; }
			$d->modify( '+' . $i . ' day' );
			$ymd = $d->format( 'Y-m-d' );
			$s   = ag_resa_slots_jour( $ymd );
			if ( $s ) { $out[ $ymd ] = $s; }
		}
		return $out;
	}
}

/* ── 4. Emails (autonomes) + .ics ────────────────────────────────────── */

if ( ! function_exists( 'ag_gwen_mail_wrap' ) ) {
	function ag_gwen_mail_wrap( $inner ) {
		$name = get_bloginfo( 'name' ) ?: 'Gwen Services';
		return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden">'
			. '<div style="background:#F37A1F;color:#fff;padding:16px 20px;font-weight:700;font-size:16px">' . esc_html( $name ) . '</div>'
			. '<div style="padding:22px;color:#333;font-size:15px;line-height:1.6">' . $inner . '</div>'
			. '<div style="padding:14px 20px;color:#999;font-size:12px;border-top:1px solid #eee">' . esc_html( $name ) . '</div></div>';
	}
}
if ( ! function_exists( 'ag_resa_ics_event' ) ) {
	function ag_resa_ics_event( $b, $method = 'REQUEST' ) {
		$z    = function ( $ts ) { return gmdate( 'Ymd\THis\Z', (int) $ts ); };
		$esc  = function ( $s ) { return preg_replace( '/([,;\\\\])/', '\\\\$1', str_replace( array( "\r\n", "\n" ), '\\n', (string) $s ) ); };
		$gwen = ag_resa_gwen_email();
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$statut = ( 'CANCEL' === $method ) ? 'CANCELLED' : 'CONFIRMED';
		$titre  = ( get_bloginfo( 'name' ) ?: 'Gwen Services' ) . ' — ' . ( $b['service_label'] ?? 'Intervention' );
		$desc   = 'Prestation : ' . ( $b['service_label'] ?? '' ) . ' | Client : ' . ( $b['name'] ?? '' )
			. ( ! empty( $b['phone'] ) ? ' (' . $b['phone'] . ')' : '' )
			. ( ! empty( $b['notes'] ) ? ' | Note : ' . $b['notes'] : '' ) . ' | Réf : ' . ( $b['id'] ?? '' );

		$ics  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Gwen Services//Reservation//FR\r\nCALSCALE:GREGORIAN\r\nMETHOD:" . $method . "\r\n";
		$ics .= "BEGIN:VEVENT\r\nUID:" . (string) ( $b['id'] ?? uniqid() ) . '@' . $host . "\r\n";
		$ics .= 'DTSTAMP:' . $z( time() ) . "\r\nDTSTART:" . $z( (int) $b['start'] ) . "\r\nDTEND:" . $z( (int) $b['start'] + (int) ( $b['dur'] ?? 60 ) * 60 ) . "\r\n";
		$ics .= 'SEQUENCE:' . (int) ( $b['seq'] ?? 0 ) . "\r\nSUMMARY:" . $esc( $titre ) . "\r\nDESCRIPTION:" . $esc( $desc ) . "\r\n";
		if ( ! empty( $b['address'] ) ) { $ics .= 'LOCATION:' . $esc( $b['address'] ) . "\r\n"; }
		$ics .= 'ORGANIZER;CN=' . $esc( get_bloginfo( 'name' ) ) . ':mailto:' . $gwen . "\r\n";
		if ( ! empty( $b['email'] ) && is_email( $b['email'] ) ) { $ics .= 'ATTENDEE;CN=' . $esc( $b['name'] ?? '' ) . ';RSVP=TRUE:mailto:' . $b['email'] . "\r\n"; }
		$ics .= 'STATUS:' . $statut . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
		return $ics;
	}
}
if ( ! function_exists( 'ag_resa_invitations' ) ) {
	function ag_resa_invitations( $b, $method = 'REQUEST' ) {
		$ics = ag_resa_ics_event( $b, $method );
		$tmp = trailingslashit( get_temp_dir() ) . 'rdv-' . sanitize_file_name( (string) ( $b['id'] ?? 'x' ) ) . '.ics';
		@file_put_contents( $tmp, $ics );
		$quand  = wp_date( 'l j F Y à H:i', (int) $b['start'], ag_resa_tz() );
		$annule = ( 'CANCEL' === $method );
		$svc    = (string) ( $b['service_label'] ?? 'Intervention' );
		$bloc = function ( $intro ) use ( $quand, $svc, $b ) {
			return '<p>' . $intro . '</p><p><strong>Prestation :</strong> ' . esc_html( $svc ) . '<br>'
				. '<strong>Quand :</strong> ' . esc_html( $quand ) . '<br>'
				. ( ! empty( $b['address'] ) ? '<strong>Adresse :</strong> ' . esc_html( $b['address'] ) . '<br>' : '' )
				. '<strong>Référence :</strong> ' . esc_html( (string) ( $b['id'] ?? '' ) ) . '</p>'
				. '<p style="font-size:13px;color:#888">Le fichier joint (.ics) ajoute le rendez-vous à votre agenda en un clic.</p>';
		};
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$att = file_exists( $tmp ) ? array( $tmp ) : array();
		if ( ! empty( $b['email'] ) && is_email( $b['email'] ) ) {
			$sujet = ( $annule ? 'Annulation de votre rendez-vous — ' : 'Votre rendez-vous est confirmé — ' ) . $svc;
			$intro = ( $annule ? 'Bonjour, votre rendez-vous a bien été annulé.' : 'Bonjour, votre rendez-vous est confirmé. À très bientôt !' );
			wp_mail( $b['email'], $sujet, ag_gwen_mail_wrap( $bloc( $intro ) ), $headers, $att );
		}
		$sujetg = ( $annule ? '🗑️ RDV annulé — ' : '📅 Nouveau RDV — ' ) . $svc . ' — ' . wp_date( 'd/m H:i', (int) $b['start'], ag_resa_tz() );
		wp_mail( ag_resa_gwen_email(), $sujetg, ag_gwen_mail_wrap( $bloc( $annule ? 'Un rendez-vous a été annulé.' : 'Un nouveau rendez-vous a été pris sur le site.' ) ), $headers, $att );
		if ( file_exists( $tmp ) ) { @unlink( $tmp ); }
		if ( function_exists( 'ag_push' ) ) {
			ag_push( $annule ? '🗑️ RDV annulé' : '📅 Nouveau rendez-vous', ( $b['name'] ?? '' ) . ' — ' . $svc . ' le ' . wp_date( 'd/m H:i', (int) $b['start'], ag_resa_tz() ) );
		}
	}
}

/* ── 5. Créer / annuler ──────────────────────────────────────────────── */

if ( ! function_exists( 'ag_resa_add' ) ) {
	function ag_resa_add( $d ) {
		$start = (int) ( $d['start'] ?? 0 );
		$dur   = ag_resa_slot_min();
		$svc   = (string) ( $d['service'] ?? '' );
		$svcs  = ag_resa_services();
		if ( $start < time() )                 { return new WP_Error( 'passe', 'Ce créneau est déjà passé.' ); }
		if ( ! isset( $svcs[ $svc ] ) )        { return new WP_Error( 'service', 'Choisissez une prestation.' ); }
		if ( ! ag_resa_libre( $start, $dur ) ) { return new WP_Error( 'pris', 'Désolé, ce créneau vient d\'être pris. Choisissez-en un autre.' ); }
		$ymd = wp_date( 'Y-m-d', $start, ag_resa_tz() );
		if ( ! in_array( $start, ag_resa_slots_jour( $ymd ), true ) ) { return new WP_Error( 'hors', 'Ce créneau n\'est pas proposé.' ); }

		$id = 'R' . strtoupper( wp_generate_password( 8, false, false ) );
		$b  = array(
			'id' => $id, 'start' => $start, 'dur' => $dur, 'service' => $svc, 'service_label' => (string) $svcs[ $svc ],
			'user' => (int) ( $d['user'] ?? 0 ), 'name' => sanitize_text_field( (string) ( $d['name'] ?? '' ) ),
			'email' => sanitize_email( (string) ( $d['email'] ?? '' ) ), 'phone' => sanitize_text_field( (string) ( $d['phone'] ?? '' ) ),
			'address' => sanitize_text_field( (string) ( $d['address'] ?? '' ) ), 'notes' => sanitize_textarea_field( (string) ( $d['notes'] ?? '' ) ),
			'status' => 'confirme', 'seq' => 0, 'created' => time(),
		);
		$list = ag_resa_all(); $list[ $id ] = $b; ag_resa_save_all( $list );
		ag_resa_invitations( $b, 'REQUEST' );
		return $b;
	}
}
if ( ! function_exists( 'ag_resa_annuler' ) ) {
	function ag_resa_annuler( $id, $par = 'client' ) {
		$list = ag_resa_all();
		if ( empty( $list[ $id ] ) ) { return new WP_Error( 'introuvable', 'Rendez-vous introuvable.' ); }
		if ( 'annule' === ( $list[ $id ]['status'] ?? '' ) ) { return $list[ $id ]; }
		$list[ $id ]['status'] = 'annule';
		$list[ $id ]['seq']    = (int) ( $list[ $id ]['seq'] ?? 0 ) + 1;
		$list[ $id ]['annule_le'] = time(); $list[ $id ]['annule_par'] = (string) $par;
		ag_resa_save_all( $list );
		ag_resa_invitations( $list[ $id ], 'CANCEL' );
		return $list[ $id ];
	}
}

/* ── 6. Flux d'abonnement de Gwen ────────────────────────────────────── */

add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/gwen.ics', array(
		'methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => 'ag_resa_feed',
	) );
} );
if ( ! function_exists( 'ag_resa_feed' ) ) {
	function ag_resa_feed( $req ) {
		if ( ! hash_equals( ag_resa_feed_token(), (string) $req->get_param( 'token' ) ) ) {
			status_header( 403 ); header( 'Content-Type: text/plain; charset=UTF-8' ); echo 'Forbidden'; exit;
		}
		$z    = function ( $ts ) { return gmdate( 'Ymd\THis\Z', (int) $ts ); };
		$esc  = function ( $s ) { return preg_replace( '/([,;\\\\])/', '\\\\$1', str_replace( array( "\r\n", "\n" ), '\\n', (string) $s ) ); };
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$ics  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Gwen Services//Agenda//FR\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
		$ics .= 'X-WR-CALNAME:' . $esc( ( get_bloginfo( 'name' ) ?: 'Gwen Services' ) . ' — Rendez-vous' ) . "\r\n";
		foreach ( ag_resa_all() as $b ) {
			if ( 'confirme' !== ( $b['status'] ?? '' ) ) { continue; }
			if ( (int) ( $b['start'] ?? 0 ) < time() - DAY_IN_SECONDS ) { continue; }
			$ics .= "BEGIN:VEVENT\r\nUID:" . (string) ( $b['id'] ?? uniqid() ) . '@' . $host . "\r\n";
			$ics .= 'DTSTAMP:' . $z( (int) ( $b['created'] ?? time() ) ) . "\r\nDTSTART:" . $z( (int) $b['start'] ) . "\r\nDTEND:" . $z( (int) $b['start'] + (int) ( $b['dur'] ?? 60 ) * 60 ) . "\r\n";
			$ics .= 'SUMMARY:' . $esc( ( $b['service_label'] ?? 'Intervention' ) . ' — ' . ( $b['name'] ?? '' ) ) . "\r\n";
			$ics .= 'DESCRIPTION:' . $esc( 'Client : ' . ( $b['name'] ?? '' ) . ( ! empty( $b['phone'] ) ? ' (' . $b['phone'] . ')' : '' ) . ( ! empty( $b['notes'] ) ? ' | ' . $b['notes'] : '' ) ) . "\r\n";
			if ( ! empty( $b['address'] ) ) { $ics .= 'LOCATION:' . $esc( $b['address'] ) . "\r\n"; }
			$ics .= "END:VEVENT\r\n";
		}
		$ics .= "END:VCALENDAR\r\n";
		status_header( 200 );
		header( 'Content-Type: text/calendar; charset=UTF-8' );
		header( 'Content-Disposition: inline; filename="gwen.ics"' );
		header( 'Cache-Control: no-cache' );
		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	}
}

/* ── 6 bis. Rappel automatique la veille du rendez-vous ──────────────── */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_resa_rappel_cron' ) ) {
		wp_schedule_event( time() + 3600, 'daily', 'ag_resa_rappel_cron' );
	}
} );
add_action( 'ag_resa_rappel_cron', 'ag_resa_rappels' );

if ( ! function_exists( 'ag_resa_rappels' ) ) {
	/** Envoie un rappel pour chaque RDV confirmé qui approche (une seule fois). */
	function ag_resa_rappels() {
		$now = time(); $list = ag_resa_all(); $changed = false;
		foreach ( $list as $id => $b ) {
			if ( 'confirme' !== ( $b['status'] ?? '' ) ) { continue; }
			if ( ! empty( $b['rappel'] ) ) { continue; }
			$start = (int) ( $b['start'] ?? 0 );
			$delta = $start - $now;
			if ( $delta >= 3 * 3600 && $delta <= 40 * 3600 ) { // dans les ~prochaines 3 à 40 h
				ag_resa_envoi_rappel( $b );
				$list[ $id ]['rappel'] = time();
				$changed = true;
			}
		}
		if ( $changed ) { ag_resa_save_all( $list ); }
	}
}

if ( ! function_exists( 'ag_resa_envoi_rappel' ) ) {
	/** Rappel au client : email (toujours) + SMS (si la passerelle existe sur ce site). */
	function ag_resa_envoi_rappel( $b ) {
		$tz    = ag_resa_tz();
		$svc   = (string) ( $b['service_label'] ?? 'Intervention' );
		$quand = wp_date( 'l j F à H:i', (int) $b['start'], $tz );

		if ( ! empty( $b['email'] ) && is_email( $b['email'] ) ) {
			$inner = '<p>Bonjour' . ( ! empty( $b['name'] ) ? ' ' . esc_html( $b['name'] ) : '' ) . ',</p>'
				. '<p>Petit rappel : votre rendez-vous <b>' . esc_html( $svc ) . '</b> est prévu <b>' . esc_html( $quand ) . '</b>'
				. ( ! empty( $b['address'] ) ? ' à ' . esc_html( $b['address'] ) : '' ) . '.</p>'
				. '<p>À très bientôt !</p>';
			wp_mail( $b['email'], 'Rappel : votre rendez-vous ' . $svc, ag_gwen_mail_wrap( $inner ), array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		if ( function_exists( 'ag_sms_send' ) ) {
			$tel = trim( (string) ( $b['phone'] ?? '' ) );
			if ( '' === $tel && ! empty( $b['email'] ) ) {
				$u = get_user_by( 'email', $b['email'] );
				if ( $u ) { $tel = (string) get_user_meta( $u->ID, 'ag_resa_phone', true ); }
			}
			$tel = preg_replace( '/[^0-9+]/', '', (string) $tel );
			if ( strlen( preg_replace( '/[^0-9]/', '', $tel ) ) >= 9 ) {
				ag_sms_send( $tel, 'Rappel : votre RDV ' . $svc . ' le ' . wp_date( 'd/m à H:i', (int) $b['start'], $tz ) . '. A bientot !' );
			}
		}
	}
}

/* ── 7. Comptes clients (inscription / connexion en façade) ──────────── */

add_action( 'admin_post_nopriv_ag_gwen_register', 'ag_gwen_register' );
add_action( 'admin_post_ag_gwen_register', 'ag_gwen_register' );
if ( ! function_exists( 'ag_gwen_register' ) ) {
	function ag_gwen_register() {
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_gwen_auth' ) ) { wp_safe_redirect( home_url( '/mon-espace' ) ); exit; }
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$pass  = (string) ( $_POST['pass'] ?? '' );
		$back  = wp_get_referer() ?: home_url( '/mon-espace' );
		if ( ! is_email( $email ) || strlen( $pass ) < 6 || '' === $name ) {
			wp_safe_redirect( add_query_arg( 'ag_err', 'saisie', $back ) ); exit;
		}
		if ( email_exists( $email ) ) { wp_safe_redirect( add_query_arg( 'ag_err', 'existe', $back ) ); exit; }
		$uid = wp_create_user( $email, $pass, $email );
		if ( is_wp_error( $uid ) ) { wp_safe_redirect( add_query_arg( 'ag_err', 'creation', $back ) ); exit; }
		wp_update_user( array( 'ID' => $uid, 'display_name' => $name, 'first_name' => $name, 'role' => 'subscriber' ) );
		wp_set_current_user( $uid );
		wp_set_auth_cookie( $uid, true );
		wp_safe_redirect( home_url( '/mon-espace' ) ); exit;
	}
}

add_action( 'admin_post_nopriv_ag_gwen_login', 'ag_gwen_login' );
add_action( 'admin_post_ag_gwen_login', 'ag_gwen_login' );
if ( ! function_exists( 'ag_gwen_login' ) ) {
	function ag_gwen_login() {
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_gwen_auth' ) ) { wp_safe_redirect( home_url( '/mon-espace' ) ); exit; }
		$back = wp_get_referer() ?: home_url( '/mon-espace' );
		$user = wp_signon( array(
			'user_login'    => sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) ),
			'user_password' => (string) ( $_POST['pass'] ?? '' ),
			'remember'      => true,
		), is_ssl() );
		if ( is_wp_error( $user ) ) { wp_safe_redirect( add_query_arg( 'ag_err', 'login', $back ) ); exit; }
		wp_safe_redirect( home_url( '/mon-espace' ) ); exit;
	}
}

if ( ! function_exists( 'ag_gwen_auth_forms' ) ) {
	/** Formulaires connexion + inscription (façade, pas wp-login). */
	function ag_gwen_auth_forms() {
		$post = esc_url( admin_url( 'admin-post.php' ) );
		$n    = wp_create_nonce( 'ag_gwen_auth' );
		$err  = isset( $_GET['ag_err'] ) ? sanitize_key( $_GET['ag_err'] ) : '';
		$msg  = array(
			'saisie'   => 'Vérifiez vos informations (email valide, nom, mot de passe d\'au moins 6 caractères).',
			'existe'   => 'Un compte existe déjà avec cet email — connectez-vous.',
			'creation' => 'Création impossible, réessayez.',
			'login'    => 'Email ou mot de passe incorrect.',
		);
		$in = 'width:100%;padding:11px;border:1px solid #ccc;border-radius:8px;margin-bottom:10px;box-sizing:border-box';
		$bt = 'background:#F37A1F;color:#fff;border:0;border-radius:8px;padding:12px 22px;font-weight:700;cursor:pointer';
		ob_start(); ?>
		<div class="ag-gwen-auth" style="max-width:420px;margin:32px auto;font-family:system-ui,Arial,sans-serif">
			<?php if ( $err && isset( $msg[ $err ] ) ) : ?>
				<p style="background:#fdecec;color:#c0392b;padding:10px 12px;border-radius:8px"><?php echo esc_html( $msg[ $err ] ); ?></p>
			<?php endif; ?>
			<h3 style="margin:0 0 10px">Se connecter</h3>
			<form method="post" action="<?php echo $post; ?>" style="margin-bottom:26px">
				<input type="hidden" name="action" value="ag_gwen_login">
				<input type="hidden" name="_n" value="<?php echo esc_attr( $n ); ?>">
				<input type="email" name="email" placeholder="Votre email" required style="<?php echo $in; ?>">
				<input type="password" name="pass" placeholder="Votre mot de passe" required style="<?php echo $in; ?>">
				<button type="submit" style="<?php echo $bt; ?>">Se connecter</button>
			</form>
			<h3 style="margin:0 0 10px">Créer un compte</h3>
			<form method="post" action="<?php echo $post; ?>">
				<input type="hidden" name="action" value="ag_gwen_register">
				<input type="hidden" name="_n" value="<?php echo esc_attr( $n ); ?>">
				<input type="text" name="name" placeholder="Votre nom et prénom" required style="<?php echo $in; ?>">
				<input type="email" name="email" placeholder="Votre email" required style="<?php echo $in; ?>">
				<input type="password" name="pass" placeholder="Mot de passe (6 caractères min.)" required minlength="6" style="<?php echo $in; ?>">
				<button type="submit" style="<?php echo $bt; ?>">Créer mon compte</button>
			</form>
		</div>
		<?php return ob_get_clean();
	}
}

/* ── 8. Réservation (AJAX) ───────────────────────────────────────────── */

add_action( 'wp_ajax_ag_resa_book', 'ag_resa_ajax_book' );
if ( ! function_exists( 'ag_resa_ajax_book' ) ) {
	function ag_resa_ajax_book() {
		check_ajax_referer( 'ag_resa', '_n' );
		if ( ! is_user_logged_in() ) { wp_send_json_error( array( 'msg' => 'Connectez-vous pour réserver.' ) ); }
		$u = wp_get_current_user();
		$r = ag_resa_add( array(
			'start'   => (int) ( $_POST['start'] ?? 0 ),
			'service' => sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) ),
			'user'    => (int) $u->ID,
			'name'    => $u->display_name ? $u->display_name : $u->user_login,
			'email'   => $u->user_email,
			'phone'   => (string) get_user_meta( $u->ID, 'ag_resa_phone', true ),
			'address' => (string) get_user_meta( $u->ID, 'ag_resa_address', true ),
			'notes'   => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ),
		) );
		if ( is_wp_error( $r ) ) { wp_send_json_error( array( 'msg' => $r->get_error_message() ) ); }
		wp_send_json_success( array( 'msg' => 'C\'est réservé ! Vous recevez la confirmation par email, avec l\'ajout à votre agenda.' ) );
	}
}
add_action( 'wp_ajax_ag_resa_cancel', 'ag_resa_ajax_cancel' );
if ( ! function_exists( 'ag_resa_ajax_cancel' ) ) {
	function ag_resa_ajax_cancel() {
		check_ajax_referer( 'ag_resa', '_n' );
		if ( ! is_user_logged_in() ) { wp_send_json_error( array( 'msg' => 'Non autorisé.' ) ); }
		$u = wp_get_current_user();
		$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$all = ag_resa_all();
		if ( empty( $all[ $id ] ) ) { wp_send_json_error( array( 'msg' => 'Rendez-vous introuvable.' ) ); }
		if ( (int) ( $all[ $id ]['user'] ?? 0 ) !== (int) $u->ID && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => 'Ce rendez-vous n\'est pas le vôtre.' ) );
		}
		$r = ag_resa_annuler( $id, current_user_can( 'manage_options' ) ? 'gwen' : 'client' );
		if ( is_wp_error( $r ) ) { wp_send_json_error( array( 'msg' => $r->get_error_message() ) ); }
		wp_send_json_success( array( 'msg' => 'Rendez-vous annulé. L\'agenda est mis à jour.' ) );
	}
}

/* ── 9. Le calendrier de réservation (shortcode) ─────────────────────── */

if ( ! function_exists( 'ag_resa_shortcode' ) ) {
	function ag_resa_shortcode() {
		$jours = ag_resa_prochains_jours();
		$svcs  = ag_resa_services();
		$logge = is_user_logged_in();
		$nonce = wp_create_nonce( 'ag_resa' );
		$ajax  = admin_url( 'admin-ajax.php' );
		$tz    = ag_resa_tz();
		ob_start(); ?>
		<div class="ag-resa" style="max-width:760px;margin:32px auto;font-family:system-ui,Arial,sans-serif">
			<?php if ( ! $logge ) : ?>
				<div style="background:#fff7e6;border:1px solid #e9c96a;border-radius:12px;padding:16px 18px;margin-bottom:18px">
					<strong>Pour réserver, connectez-vous ou créez votre compte.</strong>
					<div style="margin-top:10px"><a href="<?php echo esc_url( home_url( '/mon-espace' ) ); ?>" style="display:inline-block;background:#F37A1F;color:#fff;text-decoration:none;padding:11px 20px;border-radius:9px;font-weight:700">Se connecter / créer un compte →</a></div>
					<p style="margin:12px 0 0;font-size:.9rem">Pas encore client ? <a href="<?php echo esc_url( home_url( '/devis' ) ); ?>">Demandez d'abord un devis gratuit →</a></p>
				</div>
			<?php endif; ?>
			<?php if ( ! $jours ) : ?>
				<p>Aucun créneau disponible pour le moment. Revenez bientôt, ou contactez-nous directement.</p>
			<?php else : ?>
				<div id="ag-resa-msg" style="display:none;margin-bottom:14px;padding:12px 14px;border-radius:9px"></div>
				<?php foreach ( $jours as $ymd => $slots ) :
					$label = wp_date( 'l j F', strtotime( $ymd . ' 12:00:00' ), $tz ); ?>
					<div class="ag-resa-jour" style="margin-bottom:16px">
						<h3 style="margin:0 0 8px;text-transform:capitalize;font-size:1.05rem"><?php echo esc_html( $label ); ?></h3>
						<div style="display:flex;flex-wrap:wrap;gap:8px">
							<?php foreach ( $slots as $ts ) : ?>
								<button type="button" class="ag-resa-slot" data-ts="<?php echo (int) $ts; ?>" <?php echo $logge ? '' : 'disabled'; ?>
									style="border:1px solid #D4B45C;background:#fff;color:#16161a;border-radius:8px;padding:9px 13px;font-weight:600;cursor:<?php echo $logge ? 'pointer' : 'not-allowed'; ?>;opacity:<?php echo $logge ? '1' : '.55'; ?>">
									<?php echo esc_html( wp_date( 'H:i', $ts, $tz ) ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if ( $logge ) : ?>
				<div id="ag-resa-form" style="display:none;margin-top:18px;background:#15151b;color:#eee;border:1px solid rgba(212,180,92,.35);border-radius:12px;padding:18px">
					<p style="margin:0 0 12px">Rendez-vous le <strong id="ag-resa-when"></strong></p>
					<label style="display:block;font-size:.9rem;margin-bottom:6px">Prestation</label>
					<select id="ag-resa-service" style="width:100%;padding:11px;border-radius:8px;border:1px solid #3a3a45;background:#0e0e13;color:#fff;margin-bottom:12px">
						<?php foreach ( $svcs as $k => $lab ) : ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $lab ); ?></option><?php endforeach; ?>
					</select>
					<label style="display:block;font-size:.9rem;margin-bottom:6px">Précisions (optionnel)</label>
					<textarea id="ag-resa-notes" rows="2" style="width:100%;padding:11px;border-radius:8px;border:1px solid #3a3a45;background:#0e0e13;color:#fff;margin-bottom:14px" placeholder="Étage, code, détails utiles…"></textarea>
					<button type="button" id="ag-resa-confirm" style="background:#D4B45C;color:#191203;border:0;border-radius:9px;padding:13px 24px;font-weight:800;cursor:pointer">Confirmer le rendez-vous</button>
					<button type="button" id="ag-resa-annul" style="background:transparent;color:#bbb;border:0;margin-left:10px;cursor:pointer">Annuler</button>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php if ( $logge && $jours ) : ?>
		<script>
		(function(){
			var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
			var form=document.getElementById('ag-resa-form'), when=document.getElementById('ag-resa-when'), msg=document.getElementById('ag-resa-msg');
			var sel=document.getElementById('ag-resa-service'), notes=document.getElementById('ag-resa-notes'), chosen=0;
			function show(t,ok){ msg.style.display='block'; msg.textContent=t; msg.style.background=ok?'#12301c':'#3a1414'; msg.style.color=ok?'#7CFFB0':'#ffb3b3'; msg.scrollIntoView({behavior:'smooth',block:'center'}); }
			document.querySelectorAll('.ag-resa-slot').forEach(function(b){ b.addEventListener('click',function(){
				chosen=b.getAttribute('data-ts');
				when.textContent=b.closest('.ag-resa-jour').querySelector('h3').textContent+' à '+b.textContent.trim();
				form.style.display='block'; form.scrollIntoView({behavior:'smooth',block:'center'});
			}); });
			var a=document.getElementById('ag-resa-annul'); if(a) a.addEventListener('click',function(){ form.style.display='none'; });
			var c=document.getElementById('ag-resa-confirm'); if(c) c.addEventListener('click',function(){
				if(!chosen) return; c.disabled=true;
				var fd=new FormData(); fd.append('action','ag_resa_book'); fd.append('_n',N); fd.append('start',chosen); fd.append('service',sel.value); fd.append('notes',notes.value);
				fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
					c.disabled=false;
					if(j&&j.success){ show(j.data.msg,true); form.style.display='none'; setTimeout(function(){location.reload();},1800); }
					else{ show((j&&j.data&&j.data.msg)||'Erreur, réessayez.',false); }
				}).catch(function(){ c.disabled=false; show('Connexion interrompue, réessayez.',false); });
			});
		})();
		</script>
		<?php endif;
		return ob_get_clean();
	}
}
add_shortcode( 'ag_reservation', 'ag_resa_shortcode' );

/* ── 10. Espace client (shortcode) ───────────────────────────────────── */

if ( ! function_exists( 'ag_gwen_espace_shortcode' ) ) {
	function ag_gwen_espace_shortcode() {
		if ( ! is_user_logged_in() ) { return ag_gwen_auth_forms(); }
		$u = wp_get_current_user();
		$nonce = wp_create_nonce( 'ag_resa' );
		$ajax  = admin_url( 'admin-ajax.php' );
		$tz    = ag_resa_tz();

		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['ag_resa_profil'] ) && check_admin_referer( 'ag_resa_profil', '_pn' ) ) {
			update_user_meta( $u->ID, 'ag_resa_phone', sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ) );
			update_user_meta( $u->ID, 'ag_resa_address', sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ) );
		}
		$phone = (string) get_user_meta( $u->ID, 'ag_resa_phone', true );
		$adr   = (string) get_user_meta( $u->ID, 'ag_resa_address', true );

		$mine = array();
		foreach ( ag_resa_all() as $b ) { if ( (int) ( $b['user'] ?? 0 ) === (int) $u->ID ) { $mine[] = $b; } }
		usort( $mine, function ( $a, $b ) { return (int) ( $a['start'] ?? 0 ) - (int) ( $b['start'] ?? 0 ); } );
		$now = time();
		$avenir = array_filter( $mine, function ( $b ) use ( $now ) { return 'confirme' === ( $b['status'] ?? '' ) && (int) $b['start'] >= $now; } );

		ob_start(); ?>
		<div class="ag-espace" style="max-width:760px;margin:32px auto;font-family:system-ui,Arial,sans-serif">
			<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px">
				<h2 style="margin:0">Bonjour <?php echo esc_html( $u->display_name ?: $u->user_login ); ?></h2>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" style="color:#666">Déconnexion</a>
			</div>
			<p><a href="<?php echo esc_url( home_url( '/reserver' ) ); ?>" style="display:inline-block;background:#F37A1F;color:#fff;text-decoration:none;padding:11px 20px;border-radius:9px;font-weight:700">+ Prendre un rendez-vous</a></p>

			<h3 style="margin:22px 0 10px">Mes rendez-vous</h3>
			<div id="ag-mesrdv-msg" style="display:none;margin-bottom:12px;padding:10px 12px;border-radius:8px"></div>
			<?php if ( ! $avenir ) : ?>
				<p style="color:#666">Aucun rendez-vous à venir.</p>
			<?php else : foreach ( $avenir as $b ) : ?>
				<div style="border:1px solid #e2e2e6;border-radius:10px;padding:14px 16px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
					<div><strong><?php echo esc_html( (string) ( $b['service_label'] ?? 'Intervention' ) ); ?></strong><br>
						<span style="color:#444;text-transform:capitalize"><?php echo esc_html( wp_date( 'l j F Y à H:i', (int) $b['start'], $tz ) ); ?></span></div>
					<button type="button" class="ag-rdv-cancel" data-id="<?php echo esc_attr( (string) $b['id'] ); ?>" style="border:1px solid #c0392b;color:#c0392b;background:#fff;border-radius:8px;padding:8px 14px;cursor:pointer">Annuler</button>
				</div>
			<?php endforeach; endif; ?>

			<h3 style="margin:22px 0 8px">Mes coordonnées</h3>
			<form method="post" style="border:1px solid #e2e2e6;border-radius:10px;padding:14px 16px">
				<?php wp_nonce_field( 'ag_resa_profil', '_pn' ); ?>
				<label style="display:block;font-size:.9rem;margin-bottom:6px">Téléphone</label>
				<input type="tel" name="phone" value="<?php echo esc_attr( $phone ); ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-bottom:10px;box-sizing:border-box">
				<label style="display:block;font-size:.9rem;margin-bottom:6px">Adresse d'intervention</label>
				<input type="text" name="address" value="<?php echo esc_attr( $adr ); ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-bottom:12px;box-sizing:border-box" placeholder="N°, rue, code, ville">
				<button type="submit" name="ag_resa_profil" value="1" style="background:#F37A1F;color:#fff;border:0;border-radius:8px;padding:10px 20px;font-weight:700;cursor:pointer">Enregistrer</button>
			</form>
		</div>
		<script>
		(function(){
			var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
			var msg=document.getElementById('ag-mesrdv-msg');
			function show(t,ok){ msg.style.display='block'; msg.textContent=t; msg.style.background=ok?'#eafaf0':'#fdecec'; msg.style.color=ok?'#1a7f37':'#c0392b'; }
			document.querySelectorAll('.ag-rdv-cancel').forEach(function(b){ b.addEventListener('click',function(){
				if(!confirm('Annuler ce rendez-vous ?')) return; b.disabled=true;
				var fd=new FormData(); fd.append('action','ag_resa_cancel'); fd.append('_n',N); fd.append('id',b.getAttribute('data-id'));
				fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
					if(j&&j.success){ show(j.data.msg,true); setTimeout(function(){location.reload();},1400); }
					else{ b.disabled=false; show((j&&j.data&&j.data.msg)||'Erreur.',false); }
				}).catch(function(){ b.disabled=false; show('Connexion interrompue.',false); });
			}); });
		})();
		</script>
		<?php return ob_get_clean();
	}
}
add_shortcode( 'ag_gwen_espace', 'ag_gwen_espace_shortcode' );

/* ── 11. Pages auto-créées : /reserver et /mon-espace ────────────────── */

add_action( 'init', function () {
	/* Version 2 : on RÉPARE aussi une page qui existait déjà sans le shortcode
	   (cas rencontré : une page « reserver » préexistante n'affichait rien). */
	if ( (int) get_option( 'ag_resa_pages_v', 0 ) >= 2 ) { return; }
	$pages = array(
		'reserver'   => array( 'Réserver un rendez-vous', '[ag_reservation]' ),
		'mon-espace' => array( 'Mon espace', '[ag_gwen_espace]' ),
	);
	foreach ( $pages as $slug => $p ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			wp_insert_post( array( 'post_title' => $p[0], 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => $p[1] ) );
		} elseif ( false === strpos( (string) $page->post_content, $p[1] ) ) {
			// La page existe mais sans le shortcode : on l'ajoute au lieu de la laisser vide.
			wp_update_post( array( 'ID' => $page->ID, 'post_content' => trim( (string) $page->post_content . "\n\n" . $p[1] ) ) );
		}
	}
	update_option( 'ag_resa_pages_v', 2, false );
} );

/* Ajoute « Réserver » au menu principal — UNE seule fois, juste après « Devis ».
   On ne force pas : si le menu n'existe pas encore, ou si l'entrée est déjà là,
   on ne touche à rien. Le placement fin et le style restent à la lane design. */
add_action( 'init', function () {
	if ( get_option( 'ag_resa_menu_done' ) ) { return; }
	if ( ! function_exists( 'wp_get_nav_menu_object' ) ) { return; }

	$menu = wp_get_nav_menu_object( 'AG Domicile — Principal' );
	if ( ! $menu ) {
		$loc = get_nav_menu_locations();
		if ( ! empty( $loc['primary'] ) ) { $menu = wp_get_nav_menu_object( (int) $loc['primary'] ); }
	}
	$page = get_page_by_path( 'reserver' );
	if ( ! $menu || ! $page ) { return; } // pas encore prêt : on réessaiera au prochain chargement

	$devis     = get_page_by_path( 'devis' );
	$devis_pos = 0;
	foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $it ) {
		if ( (int) ( $it->object_id ?? 0 ) === (int) $page->ID || false !== strpos( (string) ( $it->url ?? '' ), '/reserver' ) ) {
			update_option( 'ag_resa_menu_done', 1, false ); // déjà présent
			return;
		}
		if ( $devis && (int) ( $it->object_id ?? 0 ) === (int) $devis->ID ) { $devis_pos = (int) $it->menu_order; }
	}

	$args = array(
		'menu-item-title'     => 'Réserver',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page->ID,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	);
	if ( $devis_pos ) { $args['menu-item-position'] = $devis_pos + 1; }
	wp_update_nav_menu_item( $menu->term_id, 0, $args );
	update_option( 'ag_resa_menu_done', 1, false );
}, 30 );

/* Liens de compte dans le menu principal, en DYNAMIQUE (ils changent selon l'état
   de connexion) : « Se connecter » pour un visiteur, « Mon espace » + « Déconnexion »
   pour un client connecté. Ajoutés au menu 'primary' — le style reste au thème. */
add_filter( 'wp_nav_menu_items', function ( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) { return $items; }
	$espace = esc_url( home_url( '/mon-espace' ) );
	if ( is_user_logged_in() ) {
		$logout  = esc_url( wp_logout_url( home_url( '/' ) ) );
		$items  .= '<li class="menu-item ag-resa-account"><a href="' . $espace . '">Mon espace</a></li>';
		$items  .= '<li class="menu-item ag-resa-account"><a href="' . $logout . '">Déconnexion</a></li>';
	} else {
		$items  .= '<li class="menu-item ag-resa-account"><a href="' . $espace . '">Se connecter</a></li>';
	}
	return $items;
}, 10, 2 );

/* ── 12. Écran admin : les réservations + le lien d'abonnement ───────── */

add_action( 'admin_menu', function () {
	add_menu_page( 'Réservations', '📅 Réservations', 'manage_options', 'ag-reservations', 'ag_resa_admin', 'dashicons-calendar-alt', 26 );
} );
if ( ! function_exists( 'ag_resa_admin' ) ) {
	function ag_resa_admin() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$msg = '';
		if ( isset( $_POST['ag_resa_save'] ) && check_admin_referer( 'ag_resa_admin' ) ) {
			update_option( 'ag_resa_gwen_email', sanitize_email( wp_unslash( $_POST['gwen_email'] ?? '' ) ), false );
			update_option( 'ag_resa_slot_min', max( 30, (int) ( $_POST['slot_min'] ?? 60 ) ), false );
			$msg = 'Réglages enregistrés.';
		}
		if ( isset( $_POST['ag_resa_cancel_admin'] ) && check_admin_referer( 'ag_resa_admin' ) ) {
			$r = ag_resa_annuler( sanitize_text_field( wp_unslash( $_POST['rid'] ?? '' ) ), 'gwen' );
			$msg = is_wp_error( $r ) ? $r->get_error_message() : 'Rendez-vous annulé.';
		}
		$feed   = rest_url( 'ag/v1/gwen.ics' ) . '?token=' . ag_resa_feed_token();
		$webcal = preg_replace( '#^https?://#', 'webcal://', $feed );
		$all    = ag_resa_all();
		usort( $all, function ( $a, $b ) { return (int) ( $b['start'] ?? 0 ) - (int) ( $a['start'] ?? 0 ); } );
		$tz = ag_resa_tz(); ?>
		<div class="wrap">
			<h1>📅 Réservations</h1>
			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>
			<h2>L'agenda de Gwen (à faire UNE fois)</h2>
			<p class="description" style="max-width:60em">Abonnez l'agenda de Gwen à ce lien (Google Agenda → <em>Autres agendas → À partir de l'URL</em>) : tous les rendez-vous s'y afficheront et se mettront à jour tout seuls.</p>
			<p><code style="font-size:13px;user-select:all"><?php echo esc_html( $feed ); ?></code></p>
			<p><a class="button" href="<?php echo esc_url( $webcal ); ?>">S'abonner (Apple / Outlook)</a></p>
			<form method="post" style="margin:18px 0">
				<?php wp_nonce_field( 'ag_resa_admin' ); ?>
				<table class="form-table">
					<tr><th scope="row">Email de Gwen</th><td><input type="email" name="gwen_email" class="regular-text" value="<?php echo esc_attr( ag_resa_gwen_email() ); ?>"><p class="description">Reçoit les invitations d'agenda.</p></td></tr>
					<tr><th scope="row">Durée d'un créneau</th><td><input type="number" name="slot_min" min="30" step="15" value="<?php echo (int) ag_resa_slot_min(); ?>" style="width:6em"> minutes</td></tr>
				</table>
				<p><button class="button button-primary" name="ag_resa_save" value="1">Enregistrer</button></p>
			</form>
			<h2>Les rendez-vous</h2>
			<table class="widefat striped" style="max-width:80em">
				<thead><tr><th>Quand</th><th>Prestation</th><th>Client</th><th>Contact</th><th>Adresse</th><th>État</th><th></th></tr></thead>
				<tbody>
				<?php if ( ! $all ) : ?><tr><td colspan="7">Aucun rendez-vous pour l'instant.</td></tr>
				<?php else : foreach ( $all as $b ) : ?>
					<tr>
						<td><?php echo esc_html( wp_date( 'd/m/Y H:i', (int) ( $b['start'] ?? 0 ), $tz ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['service_label'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['name'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( trim( (string) ( $b['phone'] ?? '' ) . ' ' . (string) ( $b['email'] ?? '' ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['address'] ?? '' ) ); ?></td>
						<td><?php echo 'confirme' === ( $b['status'] ?? '' ) ? '<span style="color:#1a7f37;font-weight:600">confirmé</span>' : '<span style="color:#8a8a94">annulé</span>'; ?></td>
						<td><?php if ( 'confirme' === ( $b['status'] ?? '' ) ) : ?>
							<form method="post" onsubmit="return confirm('Annuler ce rendez-vous ?');" style="margin:0"><?php wp_nonce_field( 'ag_resa_admin' ); ?><input type="hidden" name="rid" value="<?php echo esc_attr( (string) $b['id'] ); ?>"><button class="button-link" style="color:#b32d2e" name="ag_resa_cancel_admin" value="1">annuler</button></form>
						<?php endif; ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
