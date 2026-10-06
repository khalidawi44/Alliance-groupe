<?php
/**
 * ag-funnel.php — LE TUNNEL QUI APPREND.
 *
 * Problème : les agents commerciaux ENVOIENT, mais personne ne MESURE où ça
 * convertit et où ça fuit. Sans chiffres par étape, impossible d'orienter la
 * stratégie autrement qu'au doigt mouillé.
 *
 * Ce module pose la FONDATION de la boucle d'apprentissage :
 *   Terrain → Données → Analyste → (Architecte) → Terrain
 *
 * 1) Un JOURNAL d'événements : chaque étape du tunnel laisse une trace datée et
 *    segmentée (métier, ville). Branché sur les actions existantes + 2 émetteurs.
 * 2) Un ANALYSTE automatique (« Léa ») : calcule les taux de conversion par
 *    étape et par segment, repère les pépites et les fuites, pond une
 *    ORIENTATION. Tourne TOUT SEUL chaque jour (via le pilote auto) et pousse
 *    un résumé (Telegram/SMS) — actif en continu, sans rien à installer.
 * 3) Un TABLEAU DE BORD (Prospection → 📈 Tunnel & Analyste).
 *
 * Étapes du tunnel : sourced → sent → interested → (client|refused) → signed → paid
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Les étapes, dans l'ordre, avec un libellé lisible ───────────────── */
if ( ! function_exists( 'ag_funnel_etapes' ) ) {
	function ag_funnel_etapes() {
		return array(
			'sourced'    => 'Prospects repérés',
			'sent'       => 'Mails envoyés',
			'interested' => 'Intéressés',
			'client'     => 'Clients',
			'signed'     => 'Contrats signés',
			'paid'       => 'Payés',
			'refused'    => 'Refus / stop',
		);
	}
}

/* ── « 490 € » → 490.0 ───────────────────────────────────────────────── */
if ( ! function_exists( 'ag_funnel_montant' ) ) {
	function ag_funnel_montant( $s ) {
		$s = str_replace( array( ' ', "\xc2\xa0", '€', 'EUR', 'eur' ), '', (string) $s );
		$s = str_replace( ',', '.', $s );
		if ( preg_match( '/-?\d+(\.\d+)?/', $s, $m ) ) { return (float) $m[0]; }
		return 0.0;
	}
}

/* ── Le journal : on enregistre un événement (plafonné, non autoloadé) ── */
if ( ! function_exists( 'ag_funnel_log' ) ) {
	function ag_funnel_log( $type, $seg = array() ) {
		$type = sanitize_key( (string) $type );
		if ( '' === $type ) { return; }
		$seg = (array) $seg;
		$ev  = array(
			't'       => time(),
			'type'    => $type,
			'metier'  => isset( $seg['metier'] ) ? sanitize_text_field( substr( (string) $seg['metier'], 0, 60 ) ) : '',
			'ville'   => isset( $seg['ville'] ) ? sanitize_text_field( substr( (string) $seg['ville'], 0, 60 ) ) : '',
			'etape'   => isset( $seg['etape'] ) ? (int) $seg['etape'] : 0,
			'canal'   => isset( $seg['canal'] ) ? sanitize_key( (string) $seg['canal'] ) : 'email',
			'montant' => isset( $seg['montant'] ) ? ag_funnel_montant( $seg['montant'] ) : 0.0,
		);
		$j   = (array) get_option( 'ag_funnel_events', array() );
		$j[] = $ev;
		if ( count( $j ) > 6000 ) { $j = array_slice( $j, -6000 ); } // ~plusieurs mois, borné
		update_option( 'ag_funnel_events', $j, false );
	}
}

/* ── Les émetteurs : on se branche sur l'existant, sans rien casser ───── */

// Émetteur générique (utilisé par Hugo et la signature via do_action).
add_action( 'ag_funnel_event', 'ag_funnel_log', 10, 2 );

// Nouveau prospect repéré (chasse / import / lead).
add_action( 'ag_prospect_added', function ( $rec ) {
	$rec = (array) $rec;
	ag_funnel_log( 'sourced', array( 'metier' => $rec['type'] ?? '', 'ville' => $rec['city'] ?? '' ) );
}, 10, 1 );

// Changement de statut d'un prospect → étapes d'engagement / conversion.
add_action( 'ag_prospect_status_changed', function ( $id, $st ) {
	$map = array(
		'interesse'        => 'interested',
		'client'           => 'client',
		'refus'            => 'refused',
		'ne_pas_contacter' => 'refused',
	);
	$t = $map[ (string) $st ] ?? '';
	if ( '' === $t ) { return; } // 'contacte'/'relance'/… ne sont pas des étapes de conversion
	$metier = ''; $ville = '';
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
		if ( (string) ( $p['id'] ?? '' ) === (string) $id ) { $metier = $p['type'] ?? ''; $ville = $p['city'] ?? ''; break; }
	}
	ag_funnel_log( $t, array( 'metier' => $metier, 'ville' => $ville ) );
}, 10, 2 );

// Paiement vérifié (PayPal/Stripe) → encaissement.
add_action( 'ag_paypal_payment_verified', function ( $amount = 0, $payer = '' ) {
	ag_funnel_log( 'paid', array( 'montant' => (string) $amount ) );
}, 10, 2 );

/* ── L'ANALYSTE (« Léa ») : lit le journal, calcule, oriente ──────────── */
if ( ! function_exists( 'ag_funnel_analyse' ) ) {
	/**
	 * @param int $jours Fenêtre d'analyse (0 = tout l'historique).
	 * @return array compteurs, taux, segments, texte d'orientation.
	 */
	function ag_funnel_analyse( $jours = 7 ) {
		$events = (array) get_option( 'ag_funnel_events', array() );
		$since  = $jours > 0 ? time() - $jours * DAY_IN_SECONDS : 0;

		$count   = array_fill_keys( array_keys( ag_funnel_etapes() ), 0 );
		$ca_sig  = 0.0; $ca_pay = 0.0;
		$seg     = array(); // metier => array(sent,interested,signed)

		foreach ( $events as $e ) {
			if ( ( (int) ( $e['t'] ?? 0 ) ) < $since ) { continue; }
			$t = (string) ( $e['type'] ?? '' );
			if ( isset( $count[ $t ] ) ) { $count[ $t ]++; }
			if ( 'signed' === $t ) { $ca_sig += (float) ( $e['montant'] ?? 0 ); }
			if ( 'paid' === $t )   { $ca_pay += (float) ( $e['montant'] ?? 0 ); }
			if ( in_array( $t, array( 'sent', 'interested', 'signed' ), true ) ) {
				$m = ( '' !== (string) ( $e['metier'] ?? '' ) ) ? (string) $e['metier'] : '(inconnu)';
				if ( ! isset( $seg[ $m ] ) ) { $seg[ $m ] = array( 'sent' => 0, 'interested' => 0, 'signed' => 0 ); }
				$seg[ $m ][ $t ]++;
			}
		}

		$taux = function ( $num, $den ) { return $den > 0 ? round( 100 * $num / $den, 1 ) : 0.0; };
		$rates = array(
			'sent_to_interested'   => $taux( $count['interested'], $count['sent'] ),
			'interested_to_signed' => $taux( $count['signed'], $count['interested'] ),
			'sent_to_signed'       => $taux( $count['signed'], $count['sent'] ),
		);

		// Classement des métiers : d'abord par signés, puis par intéressés, puis volume.
		uasort( $seg, function ( $a, $b ) {
			return array( $b['signed'], $b['interested'], $b['sent'] ) <=> array( $a['signed'], $a['interested'], $a['sent'] );
		} );

		// Fuite : beaucoup envoyé, zéro intéressé (au moins 8 mails pour être significatif).
		$fuite = array();
		foreach ( $seg as $m => $s ) {
			if ( $s['sent'] >= 8 && 0 === $s['interested'] ) { $fuite[ $m ] = $s['sent']; }
		}
		arsort( $fuite );

		// Texte d'orientation (court, actionnable).
		$lignes = array();
		$lignes[] = sprintf(
			'%d repérés · %d envoyés · %d intéressés · %d signés · %s € signés',
			$count['sourced'], $count['sent'], $count['interested'], $count['signed'], number_format_i18n( $ca_sig, 0 )
		);
		$lignes[] = sprintf(
			'Taux : envoyé→intéressé %s%% · intéressé→signé %s%% · global envoyé→signé %s%%',
			$rates['sent_to_interested'], $rates['interested_to_signed'], $rates['sent_to_signed']
		);
		$top = array_slice( $seg, 0, 3, true );
		if ( $top ) {
			$bouts = array();
			foreach ( $top as $m => $s ) { $bouts[] = sprintf( '%s (%d env./%d int./%d sig.)', $m, $s['sent'], $s['interested'], $s['signed'] ); }
			$lignes[] = '🏆 Meilleurs métiers : ' . implode( ' · ', $bouts );
		}
		if ( $fuite ) {
			$bouts = array();
			foreach ( array_slice( $fuite, 0, 3, true ) as $m => $n ) { $bouts[] = sprintf( '%s (%d env., 0 intéressé)', $m, $n ); }
			$lignes[] = '⚠️ Fuites (revoir message/cible) : ' . implode( ' · ', $bouts );
		}
		if ( $count['sent'] < 10 ) {
			$lignes[] = 'ℹ️ Encore peu de données : les orientations se préciseront avec le volume.';
		}

		return array(
			'jours'       => $jours,
			'count'       => $count,
			'rates'       => $rates,
			'ca_signe'    => $ca_sig,
			'ca_paye'     => $ca_pay,
			'segments'    => $seg,
			'fuites'      => $fuite,
			'orientation' => implode( "\n", $lignes ),
			'genere_le'   => time(),
		);
	}
}

/* ── Cadence : l'analyste tourne 1×/jour, poussé par le pilote auto ───── */
if ( ! function_exists( 'ag_funnel_cron_maybe' ) ) {
	function ag_funnel_cron_maybe() {
		$last = (int) get_option( 'ag_funnel_last_day', 0 );
		if ( time() - $last < 72000 ) { return false; } // ~20 h de garde
		$a = ag_funnel_analyse( 7 );
		update_option( 'ag_funnel_orientation', $a, false );
		update_option( 'ag_funnel_last_day', time(), false );
		if ( function_exists( 'ag_push' ) && ( $a['count']['sent'] > 0 || $a['count']['sourced'] > 0 ) ) {
			ag_push( '📈 Analyste — orientation du jour', $a['orientation'] );
		}
		return true;
	}
}

/* ── Écran : Prospection → 📈 Tunnel & Analyste ──────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Tunnel & Analyste', '📈 Tunnel & Analyste', 'manage_options', 'ag-funnel', 'ag_funnel_render' );
}, 26 );

if ( ! function_exists( 'ag_funnel_render' ) ) {
	function ag_funnel_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$jours = isset( $_GET['j'] ) ? max( 0, (int) $_GET['j'] ) : 7; // 0 = tout
		$a     = ag_funnel_analyse( $jours );
		$et    = ag_funnel_etapes();

		echo '<div class="wrap"><h1>📈 Tunnel commercial & Analyste</h1>';
		echo '<p class="description">La boucle qui fait apprendre l\'équipe : on mesure chaque étape, l\'analyste repère ce qui convertit, l\'architecte oriente. Le journal se remplit au fil des actions (il démarre aujourd\'hui).</p>';

		// Sélecteur de période.
		$base = admin_url( 'admin.php?page=ag-funnel' );
		echo '<p>Période : ';
		foreach ( array( 7 => '7 jours', 30 => '30 jours', 0 => 'Tout' ) as $k => $lbl ) {
			$cur = ( (int) $jours === (int) $k );
			echo '<a class="button' . ( $cur ? ' button-primary' : '' ) . '" href="' . esc_url( add_query_arg( 'j', $k, $base ) ) . '" style="margin-right:6px">' . esc_html( $lbl ) . '</a>';
		}
		echo '</p>';

		// Orientation (le mot de l'analyste).
		echo '<div style="background:#f0f6fc;border-left:4px solid #0073aa;padding:12px 16px;max-width:900px;margin:12px 0">';
		echo '<strong>🧠 Orientation de l\'analyste</strong><br><pre style="white-space:pre-wrap;margin:8px 0 0;font-family:inherit">' . esc_html( $a['orientation'] ) . '</pre></div>';

		// Entonnoir : compteurs + taux de passage.
		echo '<h2>Entonnoir</h2><table class="widefat striped" style="max-width:620px"><tbody>';
		$ordre = array( 'sourced', 'sent', 'interested', 'signed', 'paid', 'refused' );
		$prev  = null;
		foreach ( $ordre as $st ) {
			$n   = (int) ( $a['count'][ $st ] ?? 0 );
			$pct = '';
			if ( null !== $prev && $prev > 0 && 'refused' !== $st ) { $pct = ' <span style="color:#666">(' . round( 100 * $n / $prev ) . '% de l\'étape précédente)</span>'; }
			echo '<tr><th style="text-align:left">' . esc_html( $et[ $st ] ) . '</th><td><strong>' . esc_html( (string) $n ) . '</strong>' . $pct . '</td></tr>';
			if ( 'refused' !== $st ) { $prev = $n; }
		}
		echo '<tr><th style="text-align:left">CA signé</th><td><strong>' . esc_html( number_format_i18n( $a['ca_signe'], 0 ) ) . ' €</strong></td></tr>';
		echo '<tr><th style="text-align:left">CA encaissé</th><td>' . esc_html( number_format_i18n( $a['ca_paye'], 0 ) ) . ' €</td></tr>';
		echo '</tbody></table>';

		// Par métier.
		echo '<h2 style="margin-top:22px">Par métier</h2>';
		if ( empty( $a['segments'] ) ) {
			echo '<p>Pas encore de données segmentées. Laisse tourner : chaque envoi alimente ce tableau.</p>';
		} else {
			echo '<table class="widefat striped" style="max-width:720px"><thead><tr><th>Métier</th><th>Envoyés</th><th>Intéressés</th><th>Signés</th><th>Envoyé→Intéressé</th></tr></thead><tbody>';
			foreach ( $a['segments'] as $m => $s ) {
				$tx = $s['sent'] > 0 ? round( 100 * $s['interested'] / $s['sent'], 1 ) : 0;
				echo '<tr><td>' . esc_html( $m ) . '</td><td>' . (int) $s['sent'] . '</td><td>' . (int) $s['interested'] . '</td><td>' . (int) $s['signed'] . '</td><td>' . esc_html( (string) $tx ) . ' %</td></tr>';
			}
			echo '</tbody></table>';
		}

		echo '<p class="description" style="margin-top:16px">🤖 L\'analyste tourne tout seul une fois par jour (poussé par le pilote automatique) et t\'envoie son orientation par Telegram/SMS. Tu peux aussi relire ici à tout moment.</p>';
		echo '</div>';
	}
}
