<?php
/**
 * ag-reservation.php — Réservation en ligne pour Gwen Services (aide à domicile).
 *
 * Ce que fait ce module, et pourquoi :
 *
 * · Le SITE est la source de vérité des disponibilités. Un créneau réservé
 *   disparaît immédiatement pour tout le monde (temps réel), parce que la liste
 *   des rendez-vous est relue à chaque affichage — pas de cache, pas de double
 *   réservation possible (on revérifie au moment de valider).
 *
 * · L'AGENDA de Gwen ET celui du client reçoivent le rendez-vous par une
 *   invitation .ics (norme iCalendar) envoyée par email : un seul geste pour
 *   l'ajouter, et ça marche avec Google, Apple et Outlook. On n'écrit jamais
 *   dans l'agenda d'un tiers (impossible et intrusif) — on l'invite.
 *
 * · Gwen s'abonne UNE fois à un lien privé (`/wp-json/ag/v1/gwen.ics?token=…`) :
 *   son agenda affiche alors TOUS les rendez-vous et se met à jour tout seul,
 *   annulations comprises. Aucune configuration Google, aucun OAuth.
 *
 * · Le CLIENT a son espace (rôle `ag_client`, briques `inc/ag-espaces.php`) :
 *   il se connecte, réserve, voit ses rendez-vous, en annule, et renseigne son
 *   profil (téléphone, adresse) réutilisé à chaque réservation.
 *
 * Le planning de Gwen (matins = gestion, après-midis = interventions) est repris
 * de sa capture. Il est filtrable (`ag_resa_planning`) si ses horaires changent.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_RESA_VER' ) ) { define( 'AG_RESA_VER', '1.0.0' ); }

/* Horizon de réservation : on n'ouvre pas l'agenda à l'infini. */
if ( ! defined( 'AG_RESA_JOURS' ) ) { define( 'AG_RESA_JOURS', 21 ); }

/* ── 1. Réglages et données de référence ─────────────────────────────── */

if ( ! function_exists( 'ag_resa_opt' ) ) {
	function ag_resa_opt( $cle, $defaut = '' ) {
		$v = get_option( 'ag_resa_' . $cle, null );
		return ( null === $v || '' === $v ) ? $defaut : $v;
	}
}

if ( ! function_exists( 'ag_resa_gwen_email' ) ) {
	/** L'email de Gwen : celui réglé, sinon la boîte de notification de la maison. */
	function ag_resa_gwen_email() {
		$m = sanitize_email( (string) ag_resa_opt( 'gwen_email', '' ) );
		if ( is_email( $m ) ) { return $m; }
		return (string) apply_filters( 'ag_calendar_notify_email', get_option( 'ag_calendar_email', get_option( 'admin_email' ) ) );
	}
}

if ( ! function_exists( 'ag_resa_slot_min' ) ) {
	/** Durée d'un créneau, en minutes. */
	function ag_resa_slot_min() { return max( 30, (int) ag_resa_opt( 'slot_min', 60 ) ); }
}

if ( ! function_exists( 'ag_resa_services' ) ) {
	/** Les prestations réservables (reprises du planning de Gwen). */
	function ag_resa_services() {
		return apply_filters( 'ag_resa_services', array(
			'menage'          => 'Ménage',
			'courses'         => 'Courses',
			'accompagnement'  => 'Accompagnement (sorties, rendez-vous)',
			'aide_personne'   => 'Aide à la personne',
		) );
	}
}

if ( ! function_exists( 'ag_resa_planning' ) ) {
	/**
	 * Les fenêtres d'INTERVENTION, jour par jour (1 = lundi … 7 = dimanche).
	 * Les matins (gestion administrative) ne sont pas ouverts à la réservation :
	 * seules les après-midis d'intervention le sont, d'après le planning de Gwen.
	 *
	 * @return array day => array( array( 'HH:MM', 'HH:MM' ) )
	 */
	function ag_resa_planning() {
		return apply_filters( 'ag_resa_planning', array(
			1 => array( array( '14:00', '17:00' ) ), // lundi
			2 => array( array( '14:00', '17:00' ) ), // mardi
			3 => array( array( '14:00', '16:30' ) ), // mercredi
			4 => array( array( '14:00', '17:00' ) ), // jeudi
			5 => array( array( '14:00', '16:00' ) ), // vendredi
		) );
	}
}

/* ── 2. Les rendez-vous (stockage) ───────────────────────────────────── */

if ( ! function_exists( 'ag_resa_all' ) ) {
	function ag_resa_all() { return (array) get_option( 'ag_resa_bookings', array() ); }
}
if ( ! function_exists( 'ag_resa_save_all' ) ) {
	function ag_resa_save_all( $list ) { update_option( 'ag_resa_bookings', (array) $list, false ); }
}

if ( ! function_exists( 'ag_resa_feed_token' ) ) {
	/** Jeton secret du lien d'abonnement de Gwen. Créé une fois, non devinable. */
	function ag_resa_feed_token() {
		$t = (string) get_option( 'ag_resa_feed_token', '' );
		if ( '' === $t ) {
			$t = wp_generate_password( 24, false, false );
			update_option( 'ag_resa_feed_token', $t, false );
		}
		return $t;
	}
}

/* ── 3. Disponibilités (temps réel) ──────────────────────────────────── */

if ( ! function_exists( 'ag_resa_tz' ) ) {
	function ag_resa_tz() { return wp_timezone(); }
}

if ( ! function_exists( 'ag_resa_libre' ) ) {
	/**
	 * Ce créneau est-il libre ? Vrai si aucun rendez-vous confirmé ne le
	 * chevauche. C'est ici que se joue le « temps réel » : on relit la liste à
	 * chaque appel, donc un créneau pris est indisponible à la seconde même.
	 */
	function ag_resa_libre( $start_ts, $dur_min, $exclure_id = '' ) {
		$fin = $start_ts + $dur_min * 60;
		foreach ( ag_resa_all() as $b ) {
			if ( 'confirme' !== ( $b['status'] ?? '' ) ) { continue; }
			if ( $exclure_id && (string) ( $b['id'] ?? '' ) === (string) $exclure_id ) { continue; }
			$bs = (int) ( $b['start'] ?? 0 );
			$bf = $bs + (int) ( $b['dur'] ?? 60 ) * 60;
			if ( $start_ts < $bf && $bs < $fin ) { return false; } // chevauchement
		}
		return true;
	}
}

if ( ! function_exists( 'ag_resa_slots_jour' ) ) {
	/**
	 * Les créneaux DISPONIBLES d'une journée donnée (Y-m-d), en timestamps de
	 * début. On génère les créneaux dans les fenêtres du planning, on écarte
	 * ceux qui sont passés et ceux déjà pris.
	 *
	 * @return int[] timestamps de début, triés
	 */
	function ag_resa_slots_jour( $ymd ) {
		$tz  = ag_resa_tz();
		try { $d0 = new DateTime( $ymd . ' 00:00:00', $tz ); }
		catch ( Exception $e ) { return array(); }
		$dow = (int) $d0->format( 'N' ); // 1..7
		$plan = ag_resa_planning();
		if ( empty( $plan[ $dow ] ) ) { return array(); }

		$dur   = ag_resa_slot_min();
		$now   = time();
		$slots = array();

		foreach ( (array) $plan[ $dow ] as $fenetre ) {
			list( $h1, $h2 ) = $fenetre;
			try {
				$deb = new DateTime( $ymd . ' ' . $h1, $tz );
				$fin = new DateTime( $ymd . ' ' . $h2, $tz );
			} catch ( Exception $e ) { continue; }
			$t   = (int) $deb->getTimestamp();
			$tf  = (int) $fin->getTimestamp();
			while ( $t + $dur * 60 <= $tf ) {
				if ( $t > $now + 3600 && ag_resa_libre( $t, $dur ) ) { // au moins 1 h à l'avance
					$slots[] = $t;
				}
				$t += $dur * 60;
			}
		}
		return $slots;
	}
}

if ( ! function_exists( 'ag_resa_prochains_jours' ) ) {
	/** Les jours de l'horizon qui ont au moins un créneau libre. */
	function ag_resa_prochains_jours() {
		$tz  = ag_resa_tz();
		$out = array();
		for ( $i = 0; $i <= AG_RESA_JOURS; $i++ ) {
			try { $d = new DateTime( 'now', $tz ); } catch ( Exception $e ) { break; }
			$d->modify( '+' . $i . ' day' );
			$ymd   = $d->format( 'Y-m-d' );
			$slots = ag_resa_slots_jour( $ymd );
			if ( $slots ) { $out[ $ymd ] = $slots; }
		}
		return $out;
	}
}

/* ── 4. Créer / annuler un rendez-vous ───────────────────────────────── */

if ( ! function_exists( 'ag_resa_add' ) ) {
	/**
	 * Enregistre un rendez-vous après revérification que le créneau est LIBRE
	 * (course entre deux clients : le dernier contrôle gagne, personne n'est
	 * réservé en double). Envoie les invitations et prévient Gwen.
	 *
	 * @return array|WP_Error le rendez-vous, ou l'erreur.
	 */
	function ag_resa_add( $d ) {
		$start = (int) ( $d['start'] ?? 0 );
		$dur   = ag_resa_slot_min();
		$svc   = (string) ( $d['service'] ?? '' );
		$svcs  = ag_resa_services();

		if ( $start < time() )                    { return new WP_Error( 'passe', 'Ce créneau est déjà passé.' ); }
		if ( ! isset( $svcs[ $svc ] ) )           { return new WP_Error( 'service', 'Choisissez une prestation.' ); }
		if ( ! ag_resa_libre( $start, $dur ) )    { return new WP_Error( 'pris', 'Désolé, ce créneau vient d\'être pris. Choisissez-en un autre.' ); }

		// Le créneau doit tomber dans une vraie fenêtre du planning.
		$tz = ag_resa_tz();
		$ymd = wp_date( 'Y-m-d', $start, $tz );
		if ( ! in_array( $start, ag_resa_slots_jour( $ymd ), true ) ) {
			return new WP_Error( 'hors', 'Ce créneau n\'est pas proposé.' );
		}

		$id   = 'R' . strtoupper( wp_generate_password( 8, false, false ) );
		$book = array(
			'id'      => $id,
			'start'   => $start,
			'dur'     => $dur,
			'service' => $svc,
			'service_label' => (string) $svcs[ $svc ],
			'user'    => (int) ( $d['user'] ?? 0 ),
			'name'    => sanitize_text_field( (string) ( $d['name'] ?? '' ) ),
			'email'   => sanitize_email( (string) ( $d['email'] ?? '' ) ),
			'phone'   => sanitize_text_field( (string) ( $d['phone'] ?? '' ) ),
			'address' => sanitize_text_field( (string) ( $d['address'] ?? '' ) ),
			'notes'   => sanitize_textarea_field( (string) ( $d['notes'] ?? '' ) ),
			'status'  => 'confirme',
			'seq'     => 0,
			'created' => time(),
		);

		$list = ag_resa_all();
		$list[ $id ] = $book;
		ag_resa_save_all( $list );

		ag_resa_invitations( $book, 'REQUEST' );
		ag_resa_prevenir_gwen( $book, 'nouveau' );

		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '📅 Réservation ' . $book['service_label'] . ' — ' . $book['name'] . ' le ' . wp_date( 'd/m à H:i', $start ) );
		}
		return $book;
	}
}

if ( ! function_exists( 'ag_resa_annuler' ) ) {
	/**
	 * Annule un rendez-vous : libère le créneau (temps réel) et envoie une
	 * annulation d'agenda (.ics METHOD:CANCEL) au client et à Gwen.
	 */
	function ag_resa_annuler( $id, $par = 'client' ) {
		$list = ag_resa_all();
		if ( empty( $list[ $id ] ) ) { return new WP_Error( 'introuvable', 'Rendez-vous introuvable.' ); }
		if ( 'annule' === ( $list[ $id ]['status'] ?? '' ) ) { return $list[ $id ]; }

		$list[ $id ]['status'] = 'annule';
		$list[ $id ]['seq']    = (int) ( $list[ $id ]['seq'] ?? 0 ) + 1;
		$list[ $id ]['annule_le']  = time();
		$list[ $id ]['annule_par'] = (string) $par;
		ag_resa_save_all( $list );

		ag_resa_invitations( $list[ $id ], 'CANCEL' );
		ag_resa_prevenir_gwen( $list[ $id ], 'annule' );

		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '🗑️ Annulation RDV — ' . ( $list[ $id ]['name'] ?? '' ) . ' le ' . wp_date( 'd/m à H:i', (int) $list[ $id ]['start'] ) );
		}
		return $list[ $id ];
	}
}

/* ── 5. Invitations agenda (.ics) ────────────────────────────────────── */

if ( ! function_exists( 'ag_resa_ics_event' ) ) {
	/** Un VEVENT pour un rendez-vous, dans une enveloppe VCALENDAR complète. */
	function ag_resa_ics_event( $b, $method = 'REQUEST' ) {
		$z    = function ( $ts ) { return gmdate( 'Ymd\THis\Z', (int) $ts ); };
		$esc  = function ( $s ) { return preg_replace( '/([,;\\\\])/', '\\\\$1', str_replace( array( "\r\n", "\n" ), '\\n', (string) $s ) ); };
		$gwen = ag_resa_gwen_email();
		$site = wp_parse_url( home_url(), PHP_URL_HOST );
		$uid  = (string) ( $b['id'] ?? uniqid() ) . '@' . $site;
		$statut = ( 'CANCEL' === $method ) ? 'CANCELLED' : 'CONFIRMED';

		$titre = ( function_exists( 'get_bloginfo' ) ? get_bloginfo( 'name' ) : 'Gwen Services' ) . ' — ' . ( $b['service_label'] ?? 'Intervention' );
		$desc  = 'Prestation : ' . ( $b['service_label'] ?? '' )
			. ' | Client : ' . ( $b['name'] ?? '' )
			. ( ! empty( $b['phone'] ) ? ' (' . $b['phone'] . ')' : '' )
			. ( ! empty( $b['notes'] ) ? ' | Note : ' . $b['notes'] : '' )
			. ' | Réf : ' . ( $b['id'] ?? '' );

		$ics  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Alliance Groupe//Reservation//FR\r\nCALSCALE:GREGORIAN\r\nMETHOD:" . $method . "\r\n";
		$ics .= "BEGIN:VEVENT\r\n";
		$ics .= 'UID:' . $uid . "\r\n";
		$ics .= 'DTSTAMP:' . $z( time() ) . "\r\n";
		$ics .= 'DTSTART:' . $z( (int) $b['start'] ) . "\r\n";
		$ics .= 'DTEND:' . $z( (int) $b['start'] + (int) ( $b['dur'] ?? 60 ) * 60 ) . "\r\n";
		$ics .= 'SEQUENCE:' . (int) ( $b['seq'] ?? 0 ) . "\r\n";
		$ics .= 'SUMMARY:' . $esc( $titre ) . "\r\n";
		$ics .= 'DESCRIPTION:' . $esc( $desc ) . "\r\n";
		if ( ! empty( $b['address'] ) ) { $ics .= 'LOCATION:' . $esc( $b['address'] ) . "\r\n"; }
		$ics .= 'ORGANIZER;CN=' . $esc( get_bloginfo( 'name' ) ) . ':mailto:' . $gwen . "\r\n";
		if ( ! empty( $b['email'] ) && is_email( $b['email'] ) ) {
			$ics .= 'ATTENDEE;CN=' . $esc( $b['name'] ?? '' ) . ';RSVP=TRUE:mailto:' . $b['email'] . "\r\n";
		}
		$ics .= 'STATUS:' . $statut . "\r\n";
		$ics .= "END:VEVENT\r\nEND:VCALENDAR\r\n";
		return $ics;
	}
}

if ( ! function_exists( 'ag_resa_invitations' ) ) {
	/**
	 * Envoie l'invitation (ou l'annulation) d'agenda au client ET à Gwen. Le
	 * .ics part en pièce jointe, avec un email lisible à côté : le destinataire
	 * a une vraie information, pas seulement un fichier.
	 */
	function ag_resa_invitations( $b, $method = 'REQUEST' ) {
		$ics = ag_resa_ics_event( $b, $method );

		// Fichier temporaire à joindre (wp_mail veut un chemin).
		$tmp = trailingslashit( get_temp_dir() ) . 'rdv-' . sanitize_file_name( (string) ( $b['id'] ?? 'x' ) ) . '.ics';
		@file_put_contents( $tmp, $ics );

		$quand  = wp_date( 'l d F Y à H:i', (int) $b['start'] );
		$annule = ( 'CANCEL' === $method );
		$svc    = (string) ( $b['service_label'] ?? 'Intervention' );

		$bloc = function ( $intro ) use ( $quand, $svc, $b ) {
			$h = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">' . $intro . '</p>';
			$h .= '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.7;color:#e8e6e0;">'
				. '<strong>Prestation :</strong> ' . esc_html( $svc ) . '<br>'
				. '<strong>Quand :</strong> ' . esc_html( $quand ) . '<br>'
				. ( ! empty( $b['address'] ) ? '<strong>Adresse :</strong> ' . esc_html( $b['address'] ) . '<br>' : '' )
				. '<strong>Référence :</strong> ' . esc_html( (string) ( $b['id'] ?? '' ) ) . '</p>';
			$h .= '<p style="font-family:Arial,sans-serif;font-size:13px;color:#8a8a94;">Le fichier joint (.ics) ajoute le rendez-vous à votre agenda en un clic (Google, Apple, Outlook).</p>';
			return $h;
		};

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Au client.
		if ( ! empty( $b['email'] ) && is_email( $b['email'] ) ) {
			$sujet = ( $annule ? 'Annulation de votre rendez-vous — ' : 'Votre rendez-vous est confirmé — ' ) . $svc;
			$intro = $annule
				? 'Bonjour' . ( ! empty( $b['name'] ) ? ' ' . esc_html( $b['name'] ) : '' ) . ', votre rendez-vous a bien été annulé.'
				: 'Bonjour' . ( ! empty( $b['name'] ) ? ' ' . esc_html( $b['name'] ) : '' ) . ', votre rendez-vous est confirmé. À très bientôt !';
			$html  = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( $sujet, $bloc( $intro ) ) : $bloc( $intro );
			wp_mail( $b['email'], $sujet, $html, $headers, file_exists( $tmp ) ? array( $tmp ) : array() );
		}

		// À Gwen.
		$gwen  = ag_resa_gwen_email();
		$sujetg = ( $annule ? '🗑️ RDV annulé — ' : '📅 Nouveau RDV — ' ) . $svc . ' — ' . wp_date( 'd/m H:i', (int) $b['start'] );
		$introg = $annule
			? 'Un rendez-vous a été annulé.'
			: 'Un nouveau rendez-vous a été pris sur le site.';
		$htmlg  = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( $sujetg, $bloc( $introg ) ) : $bloc( $introg );
		wp_mail( $gwen, $sujetg, $htmlg, $headers, file_exists( $tmp ) ? array( $tmp ) : array() );

		if ( file_exists( $tmp ) ) { @unlink( $tmp ); }
	}
}

if ( ! function_exists( 'ag_resa_prevenir_gwen' ) ) {
	/** Alerte téléphone (push/SMS) en plus de l'email, si le canal existe. */
	function ag_resa_prevenir_gwen( $b, $type ) {
		if ( ! function_exists( 'ag_push' ) ) { return; }
		$quand = wp_date( 'd/m à H:i', (int) $b['start'] );
		if ( 'annule' === $type ) {
			ag_push( '🗑️ RDV annulé', ( $b['name'] ?? '' ) . ' — ' . ( $b['service_label'] ?? '' ) . ' le ' . $quand );
		} else {
			ag_push( '📅 Nouveau rendez-vous', ( $b['name'] ?? '' ) . ' — ' . ( $b['service_label'] ?? '' ) . ' le ' . $quand
				. ( ! empty( $b['phone'] ) ? "\n📞 " . $b['phone'] : '' )
				. ( ! empty( $b['address'] ) ? "\n📍 " . $b['address'] : '' ) );
		}
	}
}

/* ── 6. Le lien d'abonnement de Gwen (flux .ics) ─────────────────────── */

add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/gwen.ics', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'ag_resa_feed',
	) );
} );

if ( ! function_exists( 'ag_resa_feed' ) ) {
	/**
	 * Flux iCalendar de TOUS les rendez-vous confirmés à venir. Gwen s'y abonne
	 * une fois ; son agenda se met à jour tout seul. Protégé par un jeton.
	 */
	function ag_resa_feed( $req ) {
		$token = (string) $req->get_param( 'token' );
		if ( ! hash_equals( ag_resa_feed_token(), $token ) ) {
			status_header( 403 );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo 'Forbidden';
			exit;
		}
		$z   = function ( $ts ) { return gmdate( 'Ymd\THis\Z', (int) $ts ); };
		$esc = function ( $s ) { return preg_replace( '/([,;\\\\])/', '\\\\$1', str_replace( array( "\r\n", "\n" ), '\\n', (string) $s ) ); };
		$site = wp_parse_url( home_url(), PHP_URL_HOST );

		$ics  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Alliance Groupe//Gwen//FR\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
		$ics .= 'X-WR-CALNAME:' . $esc( get_bloginfo( 'name' ) . ' — Rendez-vous' ) . "\r\n";
		foreach ( ag_resa_all() as $b ) {
			if ( 'confirme' !== ( $b['status'] ?? '' ) ) { continue; }
			if ( (int) ( $b['start'] ?? 0 ) < time() - DAY_IN_SECONDS ) { continue; } // on garde la veille pour la marge
			$ics .= "BEGIN:VEVENT\r\n";
			$ics .= 'UID:' . (string) ( $b['id'] ?? uniqid() ) . '@' . $site . "\r\n";
			$ics .= 'DTSTAMP:' . $z( (int) ( $b['created'] ?? time() ) ) . "\r\n";
			$ics .= 'DTSTART:' . $z( (int) $b['start'] ) . "\r\n";
			$ics .= 'DTEND:' . $z( (int) $b['start'] + (int) ( $b['dur'] ?? 60 ) * 60 ) . "\r\n";
			$ics .= 'SUMMARY:' . $esc( ( $b['service_label'] ?? 'Intervention' ) . ' — ' . ( $b['name'] ?? '' ) ) . "\r\n";
			$desc = 'Client : ' . ( $b['name'] ?? '' ) . ( ! empty( $b['phone'] ) ? ' (' . $b['phone'] . ')' : '' )
				. ( ! empty( $b['notes'] ) ? ' | ' . $b['notes'] : '' );
			$ics .= 'DESCRIPTION:' . $esc( $desc ) . "\r\n";
			if ( ! empty( $b['address'] ) ) { $ics .= 'LOCATION:' . $esc( $b['address'] ) . "\r\n"; }
			$ics .= "END:VEVENT\r\n";
		}
		$ics .= "END:VCALENDAR\r\n";

		/* On sort le calendrier BRUT (pas via la sérialisation JSON de l'API REST,
		   qui produirait un .ics invalide entre guillemets). */
		status_header( 200 );
		header( 'Content-Type: text/calendar; charset=UTF-8' );
		header( 'Content-Disposition: inline; filename="gwen.ics"' );
		header( 'Cache-Control: no-cache' );
		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	}
}

/* ── 7. Réservation côté visiteur (AJAX) ─────────────────────────────── */

add_action( 'wp_ajax_ag_resa_book', 'ag_resa_ajax_book' );
if ( ! function_exists( 'ag_resa_ajax_book' ) ) {
	function ag_resa_ajax_book() {
		check_ajax_referer( 'ag_resa', '_n' );
		if ( ! is_user_logged_in() ) { wp_send_json_error( array( 'msg' => 'Connectez-vous pour réserver.' ) ); }
		$u = wp_get_current_user();

		$res = ag_resa_add( array(
			'start'   => (int) ( $_POST['start'] ?? 0 ),
			'service' => sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) ),
			'user'    => (int) $u->ID,
			'name'    => $u->display_name ? $u->display_name : $u->user_login,
			'email'   => $u->user_email,
			'phone'   => (string) get_user_meta( $u->ID, 'ag_resa_phone', true ),
			'address' => (string) get_user_meta( $u->ID, 'ag_resa_address', true ),
			'notes'   => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ),
		) );

		if ( is_wp_error( $res ) ) { wp_send_json_error( array( 'msg' => $res->get_error_message() ) ); }
		wp_send_json_success( array( 'msg' => 'C\'est réservé ! Vous recevez la confirmation par email, avec l\'ajout à votre agenda.' ) );
	}
}

add_action( 'wp_ajax_ag_resa_cancel', 'ag_resa_ajax_cancel' );
if ( ! function_exists( 'ag_resa_ajax_cancel' ) ) {
	function ag_resa_ajax_cancel() {
		check_ajax_referer( 'ag_resa', '_n' );
		if ( ! is_user_logged_in() ) { wp_send_json_error( array( 'msg' => 'Non autorisé.' ) ); }
		$u  = wp_get_current_user();
		$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$all = ag_resa_all();
		if ( empty( $all[ $id ] ) ) { wp_send_json_error( array( 'msg' => 'Rendez-vous introuvable.' ) ); }
		// On n'annule que SON propre rendez-vous (sauf admin).
		if ( (int) ( $all[ $id ]['user'] ?? 0 ) !== (int) $u->ID && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => 'Ce rendez-vous n\'est pas le vôtre.' ) );
		}
		$r = ag_resa_annuler( $id, current_user_can( 'manage_options' ) ? 'gwen' : 'client' );
		if ( is_wp_error( $r ) ) { wp_send_json_error( array( 'msg' => $r->get_error_message() ) ); }
		wp_send_json_success( array( 'msg' => 'Rendez-vous annulé. L\'agenda est mis à jour.' ) );
	}
}

/* ── 8. Le module de réservation (shortcode public) ──────────────────── */

if ( ! function_exists( 'ag_resa_shortcode' ) ) {
	/** [ag_reservation] — le calendrier de prise de rendez-vous. */
	function ag_resa_shortcode() {
		$jours = ag_resa_prochains_jours();
		$svcs  = ag_resa_services();
		$logge = is_user_logged_in();
		$nonce = wp_create_nonce( 'ag_resa' );
		$ajax  = admin_url( 'admin-ajax.php' );
		$connexion = add_query_arg( 'redirect_to', rawurlencode( get_permalink() ?: home_url( '/reserver' ) ), home_url( '/connexion' ) );
		$tz    = ag_resa_tz();

		ob_start(); ?>
		<div class="ag-resa" style="--or:#F37A1F;--gold:#D4B45C;max-width:760px;margin:0 auto;font-family:system-ui,Arial,sans-serif">
			<?php if ( ! $logge ) : ?>
				<div class="ag-resa-connect" style="background:#fff7e6;border:1px solid #e9c96a;border-radius:12px;padding:16px 18px;margin-bottom:18px">
					<strong>Pour réserver, connectez-vous ou créez votre compte</strong> — c'est gratuit et ça vous donne accès à votre espace personnel.
					<div style="margin-top:10px"><a href="<?php echo esc_url( $connexion ); ?>" style="display:inline-block;background:var(--or);color:#fff;text-decoration:none;padding:11px 20px;border-radius:9px;font-weight:700">Se connecter / créer un compte →</a></div>
				</div>
			<?php endif; ?>

			<?php if ( ! $jours ) : ?>
				<p>Aucun créneau disponible pour le moment. Revenez bientôt, ou contactez-nous directement.</p>
			<?php else : ?>
				<div id="ag-resa-msg" style="display:none;margin-bottom:14px;padding:12px 14px;border-radius:9px"></div>
				<div class="ag-resa-jours">
					<?php foreach ( $jours as $ymd => $slots ) :
						$label = wp_date( 'l j F', strtotime( $ymd . ' 12:00:00' ), $tz ); ?>
						<div class="ag-resa-jour" style="margin-bottom:16px">
							<h3 style="margin:0 0 8px;text-transform:capitalize;font-size:1.05rem"><?php echo esc_html( $label ); ?></h3>
							<div style="display:flex;flex-wrap:wrap;gap:8px">
								<?php foreach ( $slots as $ts ) : ?>
									<button type="button" class="ag-resa-slot" data-ts="<?php echo (int) $ts; ?>"
										<?php echo $logge ? '' : 'disabled'; ?>
										style="border:1px solid var(--gold);background:#fff;color:#16161a;border-radius:8px;padding:9px 13px;font-weight:600;cursor:<?php echo $logge ? 'pointer' : 'not-allowed'; ?>;opacity:<?php echo $logge ? '1' : '.55'; ?>">
										<?php echo esc_html( wp_date( 'H:i', $ts, $tz ) ); ?>
									</button>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $logge ) : ?>
				<div id="ag-resa-form" style="display:none;margin-top:18px;background:#15151b;color:#eee;border:1px solid rgba(212,180,92,.35);border-radius:12px;padding:18px">
					<p style="margin:0 0 12px">Rendez-vous le <strong id="ag-resa-when"></strong></p>
					<label style="display:block;font-size:.9rem;margin-bottom:6px">Prestation</label>
					<select id="ag-resa-service" style="width:100%;padding:11px;border-radius:8px;border:1px solid #3a3a45;background:#0e0e13;color:#fff;margin-bottom:12px">
						<?php foreach ( $svcs as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
					<label style="display:block;font-size:.9rem;margin-bottom:6px">Précisions (optionnel)</label>
					<textarea id="ag-resa-notes" rows="2" style="width:100%;padding:11px;border-radius:8px;border:1px solid #3a3a45;background:#0e0e13;color:#fff;margin-bottom:14px" placeholder="Étage, code, détails utiles…"></textarea>
					<button type="button" id="ag-resa-confirm" style="background:var(--gold);color:#191203;border:0;border-radius:9px;padding:13px 24px;font-weight:800;cursor:pointer">Confirmer le rendez-vous</button>
					<button type="button" id="ag-resa-annul" style="background:transparent;color:#bbb;border:0;margin-left:10px;cursor:pointer">Annuler</button>
					<p style="font-size:.8rem;color:#9aa3b4;margin:12px 0 0">Votre téléphone et votre adresse sont repris de votre profil. Vous pouvez les compléter dans votre espace.</p>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php if ( $logge && $jours ) : ?>
		<script>
		(function(){
			var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
			var form=document.getElementById('ag-resa-form'), when=document.getElementById('ag-resa-when');
			var msg=document.getElementById('ag-resa-msg'), sel=document.getElementById('ag-resa-service');
			var notes=document.getElementById('ag-resa-notes'), chosen=0;
			function show(t,ok){ msg.style.display='block'; msg.textContent=t; msg.style.background=ok?'#12301c':'#3a1414'; msg.style.color=ok?'#7CFFB0':'#ffb3b3'; msg.scrollIntoView({behavior:'smooth',block:'center'}); }
			document.querySelectorAll('.ag-resa-slot').forEach(function(b){
				b.addEventListener('click',function(){
					chosen=b.getAttribute('data-ts');
					when.textContent=b.closest('.ag-resa-jour').querySelector('h3').textContent+' à '+b.textContent.trim();
					form.style.display='block'; form.scrollIntoView({behavior:'smooth',block:'center'});
				});
			});
			var annul=document.getElementById('ag-resa-annul');
			if(annul) annul.addEventListener('click',function(){ form.style.display='none'; });
			var conf=document.getElementById('ag-resa-confirm');
			if(conf) conf.addEventListener('click',function(){
				if(!chosen) return;
				conf.disabled=true;
				var fd=new FormData(); fd.append('action','ag_resa_book'); fd.append('_n',N);
				fd.append('start',chosen); fd.append('service',sel.value); fd.append('notes',notes.value);
				fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
					conf.disabled=false;
					if(j&&j.success){ show(j.data.msg,true); form.style.display='none'; setTimeout(function(){location.reload();},1800); }
					else{ show((j&&j.data&&j.data.msg)||'Erreur, réessayez.',false); }
				}).catch(function(){ conf.disabled=false; show('Connexion interrompue, réessayez.',false); });
			});
		})();
		</script>
		<?php endif;
		return ob_get_clean();
	}
}
add_shortcode( 'ag_reservation', 'ag_resa_shortcode' );

/* ── 9. Espace client : mes rendez-vous + profil ─────────────────────── */

if ( ! function_exists( 'ag_resa_mes_rdv_shortcode' ) ) {
	/** [ag_mes_rendezvous] — la liste des rendez-vous du client connecté + profil. */
	function ag_resa_mes_rdv_shortcode() {
		if ( ! is_user_logged_in() ) { return ''; }
		$u     = wp_get_current_user();
		$nonce = wp_create_nonce( 'ag_resa' );
		$ajax  = admin_url( 'admin-ajax.php' );
		$tz    = ag_resa_tz();

		// Sauvegarde du profil (téléphone / adresse).
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['ag_resa_profil'] )
			&& check_admin_referer( 'ag_resa_profil', '_pn' ) ) {
			update_user_meta( $u->ID, 'ag_resa_phone', sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ) );
			update_user_meta( $u->ID, 'ag_resa_address', sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ) );
		}
		$phone = (string) get_user_meta( $u->ID, 'ag_resa_phone', true );
		$adr   = (string) get_user_meta( $u->ID, 'ag_resa_address', true );

		$mine = array();
		foreach ( ag_resa_all() as $b ) {
			if ( (int) ( $b['user'] ?? 0 ) === (int) $u->ID ) { $mine[] = $b; }
		}
		usort( $mine, function ( $a, $b ) { return (int) ( $a['start'] ?? 0 ) - (int) ( $b['start'] ?? 0 ); } );
		$now = time();

		ob_start(); ?>
		<div class="ag-mesrdv" style="max-width:760px;margin:24px auto;font-family:system-ui,Arial,sans-serif">
			<h2 style="font-size:1.3rem;margin:0 0 12px">Mes rendez-vous</h2>
			<div id="ag-mesrdv-msg" style="display:none;margin-bottom:12px;padding:10px 12px;border-radius:8px"></div>
			<?php
			$avenir = array_filter( $mine, function ( $b ) use ( $now ) { return 'confirme' === ( $b['status'] ?? '' ) && (int) $b['start'] >= $now; } );
			if ( ! $avenir ) : ?>
				<p style="color:#666">Aucun rendez-vous à venir. <a href="<?php echo esc_url( home_url( '/reserver' ) ); ?>">Prendre un rendez-vous →</a></p>
			<?php else : foreach ( $avenir as $b ) : ?>
				<div style="border:1px solid #e2e2e6;border-radius:10px;padding:14px 16px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
					<div>
						<strong><?php echo esc_html( (string) ( $b['service_label'] ?? 'Intervention' ) ); ?></strong><br>
						<span style="color:#444;text-transform:capitalize"><?php echo esc_html( wp_date( 'l j F Y à H:i', (int) $b['start'], $tz ) ); ?></span>
						<?php if ( ! empty( $b['address'] ) ) : ?><br><small style="color:#777"><?php echo esc_html( $b['address'] ); ?></small><?php endif; ?>
					</div>
					<button type="button" class="ag-rdv-cancel" data-id="<?php echo esc_attr( (string) $b['id'] ); ?>"
						style="border:1px solid #c0392b;color:#c0392b;background:#fff;border-radius:8px;padding:8px 14px;cursor:pointer">Annuler</button>
				</div>
			<?php endforeach; endif; ?>

			<h3 style="font-size:1.05rem;margin:22px 0 8px">Mes coordonnées</h3>
			<form method="post" style="border:1px solid #e2e2e6;border-radius:10px;padding:14px 16px">
				<?php wp_nonce_field( 'ag_resa_profil', '_pn' ); ?>
				<label style="display:block;font-size:.9rem;margin-bottom:6px">Téléphone</label>
				<input type="tel" name="phone" value="<?php echo esc_attr( $phone ); ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-bottom:10px">
				<label style="display:block;font-size:.9rem;margin-bottom:6px">Adresse d'intervention</label>
				<input type="text" name="address" value="<?php echo esc_attr( $adr ); ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-bottom:12px" placeholder="N°, rue, code, ville">
				<button type="submit" name="ag_resa_profil" value="1" style="background:#F37A1F;color:#fff;border:0;border-radius:8px;padding:10px 20px;font-weight:700;cursor:pointer">Enregistrer</button>
			</form>
		</div>
		<script>
		(function(){
			var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
			var msg=document.getElementById('ag-mesrdv-msg');
			function show(t,ok){ msg.style.display='block'; msg.textContent=t; msg.style.background=ok?'#eafaf0':'#fdecec'; msg.style.color=ok?'#1a7f37':'#c0392b'; }
			document.querySelectorAll('.ag-rdv-cancel').forEach(function(b){
				b.addEventListener('click',function(){
					if(!confirm('Annuler ce rendez-vous ?')) return;
					b.disabled=true;
					var fd=new FormData(); fd.append('action','ag_resa_cancel'); fd.append('_n',N); fd.append('id',b.getAttribute('data-id'));
					fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
						if(j&&j.success){ show(j.data.msg,true); setTimeout(function(){location.reload();},1400); }
						else{ b.disabled=false; show((j&&j.data&&j.data.msg)||'Erreur.',false); }
					}).catch(function(){ b.disabled=false; show('Connexion interrompue.',false); });
				});
			});
		})();
		</script>
		<?php
		return ob_get_clean();
	}
}
add_shortcode( 'ag_mes_rendezvous', 'ag_resa_mes_rdv_shortcode' );

/* On ajoute « Mes rendez-vous » dans l'espace client, sans toucher au reste. */
add_filter( 'the_content', function ( $c ) {
	if ( is_page_template( 'templates/page-espace-client.php' ) && is_main_query() && in_the_loop()
		&& false === strpos( $c, 'ag-mesrdv' ) ) {
		$c .= ag_resa_mes_rdv_shortcode();
	}
	return $c;
}, 20 );

/* ── 10. Page /reserver (créée automatiquement) ──────────────────────── */

add_action( 'init', function () {
	if ( (int) get_option( 'ag_resa_page_done', 0 ) >= 1 ) { return; }
	if ( ! get_page_by_path( 'reserver' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Réserver un rendez-vous',
			'post_name'    => 'reserver',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '[ag_reservation]',
		) );
	}
	update_option( 'ag_resa_page_done', 1, false );
} );

/* ── 11. Écran d'administration : les réservations + le lien d'abonnement ─ */

add_action( 'admin_menu', function () {
	add_menu_page(
		'Réservations Gwen', '📅 Réservations', 'manage_options',
		'ag-reservations', 'ag_resa_admin', 'dashicons-calendar-alt', 26
	);
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

		$feed = rest_url( 'ag/v1/gwen.ics' ) . '?token=' . ag_resa_feed_token();
		$webcal = preg_replace( '#^https?://#', 'webcal://', $feed );
		$all  = ag_resa_all();
		usort( $all, function ( $a, $b ) { return (int) ( $b['start'] ?? 0 ) - (int) ( $a['start'] ?? 0 ); } );
		$tz = ag_resa_tz();
		?>
		<div class="wrap">
			<h1>📅 Réservations — Gwen Services</h1>
			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<h2>L'agenda de Gwen (à faire UNE fois)</h2>
			<p class="description" style="max-width:60em">
				Abonnez l'agenda de Gwen à ce lien : tous les rendez-vous s'y afficheront et se
				mettront à jour tout seuls (annulations comprises). Dans Google Agenda →
				<em>Autres agendas → À partir de l'URL</em>, collez :
			</p>
			<p><code style="font-size:13px;user-select:all"><?php echo esc_html( $feed ); ?></code></p>
			<p><a class="button" href="<?php echo esc_url( $webcal ); ?>">S'abonner (Apple Agenda / Outlook)</a>
				<span class="description">Chaque réservation envoie AUSSI une invitation par email, à Gwen et au client.</span></p>

			<form method="post" style="margin:18px 0">
				<?php wp_nonce_field( 'ag_resa_admin' ); ?>
				<table class="form-table">
					<tr><th scope="row">Email de Gwen</th><td>
						<input type="email" name="gwen_email" class="regular-text" value="<?php echo esc_attr( ag_resa_gwen_email() ); ?>">
						<p class="description">Reçoit les invitations d'agenda. Par défaut : la boîte de notification de la maison.</p>
					</td></tr>
					<tr><th scope="row">Durée d'un créneau</th><td>
						<input type="number" name="slot_min" min="30" step="15" value="<?php echo (int) ag_resa_slot_min(); ?>" style="width:6em"> minutes
					</td></tr>
				</table>
				<p><button class="button button-primary" name="ag_resa_save" value="1">Enregistrer</button></p>
			</form>

			<h2>Les rendez-vous</h2>
			<table class="widefat striped" style="max-width:80em">
				<thead><tr><th>Quand</th><th>Prestation</th><th>Client</th><th>Contact</th><th>Adresse</th><th>État</th><th></th></tr></thead>
				<tbody>
				<?php if ( ! $all ) : ?>
					<tr><td colspan="7">Aucun rendez-vous pour l'instant.</td></tr>
				<?php else : foreach ( $all as $b ) : ?>
					<tr>
						<td><?php echo esc_html( wp_date( 'd/m/Y H:i', (int) ( $b['start'] ?? 0 ), $tz ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['service_label'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['name'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( trim( (string) ( $b['phone'] ?? '' ) . ' ' . (string) ( $b['email'] ?? '' ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['address'] ?? '' ) ); ?></td>
						<td><?php echo 'confirme' === ( $b['status'] ?? '' )
							? '<span style="color:#1a7f37;font-weight:600">confirmé</span>'
							: '<span style="color:#8a8a94">annulé</span>'; ?></td>
						<td><?php if ( 'confirme' === ( $b['status'] ?? '' ) ) : ?>
							<form method="post" onsubmit="return confirm('Annuler ce rendez-vous ?');" style="margin:0">
								<?php wp_nonce_field( 'ag_resa_admin' ); ?>
								<input type="hidden" name="rid" value="<?php echo esc_attr( (string) $b['id'] ); ?>">
								<button class="button-link" style="color:#b32d2e" name="ag_resa_cancel_admin" value="1">annuler</button>
							</form>
						<?php endif; ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
