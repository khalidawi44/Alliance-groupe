<?php
/**
 * ag-rapport-soir.php — RAPPORT DU SOIR (20h) de toute l'équipe.
 *
 * Fabrice ne veut plus se connecter au wp-admin pour voir ce que les agents ont
 * fait. Chaque soir à 20h, ce module compile la journée (prospection, Hugo,
 * réponses, contrats, Max, Gardien, journal d'activité) et l'envoie :
 *   - par EMAIL (rapport détaillé, HTML brandé) ;
 *   - sur TELEGRAM interne (même détail, en texte) ;
 *   - par SMS (résumé court, juste pour le ping).
 *
 * Déclenché par le pilote automatique (cron-job.org /15 min) : à partir de 20h,
 * il part une seule fois dans la journée. Aucune action requise.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ag_rapport_on' ) ) {
	function ag_rapport_on() { return (bool) get_option( 'ag_rapport_soir_on', 1 ); }
}
if ( ! function_exists( 'ag_rapport_heure' ) ) {
	function ag_rapport_heure() { return max( 0, min( 23, (int) get_option( 'ag_rapport_heure', 20 ) ) ); }
}
if ( ! function_exists( 'ag_rapport_jour_debut' ) ) {
	function ag_rapport_jour_debut() {
		$d = new DateTime( 'now', wp_timezone() ); $d->setTime( 0, 0, 0 );
		return $d->getTimestamp();
	}
}

/* ── Les chiffres de la journée ──────────────────────────────────────── */
if ( ! function_exists( 'ag_rapport_data' ) ) {
	function ag_rapport_data() {
		$start = ag_rapport_jour_debut();

		// Tunnel du jour.
		$f = array( 'sourced' => 0, 'sent' => 0, 'interested' => 0, 'client' => 0, 'signed' => 0, 'paid' => 0, 'optout' => 0 );
		$ca = 0.0;
		foreach ( (array) get_option( 'ag_funnel_events', array() ) as $e ) {
			if ( (int) ( $e['t'] ?? 0 ) < $start ) { continue; }
			$t = (string) ( $e['type'] ?? '' );
			if ( isset( $f[ $t ] ) ) { $f[ $t ]++; }
			if ( 'signed' === $t ) { $ca += (float) ( $e['montant'] ?? 0 ); }
		}

		// Hugo (envois du jour / plafond).
		$jour = (array) get_option( 'ag_closer_jour', array() );
		$sent_hugo = ( ( $jour['d'] ?? '' ) === gmdate( 'Ymd' ) ) ? (int) ( $jour['n'] ?? 0 ) : 0;
		$cap = function_exists( 'ag_closer_cap' ) ? ag_closer_cap() : 0;

		// CRM (photo actuelle).
		$pros = (array) get_option( 'ag_prospects', array() );
		$tot = count( $pros ); $avec_mail = 0; $elig = 0;
		$peut = function_exists( 'ag_closer_eligible' );
		foreach ( $pros as $p ) {
			if ( ! empty( $p['email'] ) && is_email( $p['email'] ) ) { $avec_mail++; }
			if ( $peut && ag_closer_eligible( $p ) ) { $elig++; }
		}

		// Journal d'activité du jour.
		$acts = array();
		foreach ( (array) get_option( 'ag_activity', array() ) as $a ) {
			if ( (int) ( $a['ts'] ?? 0 ) >= $start ) { $acts[] = $a; }
		}

		// Agents intelligents.
		$ab_winner = (string) get_option( 'ag_ab_winner', '' );
		$gardien   = function_exists( 'ag_gardien_etat' ) ? ag_gardien_etat() : array();
		$diag      = function_exists( 'ag_auto_diag' ) ? ag_auto_diag() : array();

		return array(
			'date'       => wp_date( 'l j F Y' ),
			'funnel'     => $f,
			'ca_signe'   => $ca,
			'hugo'       => array( 'sent' => $sent_hugo, 'cap' => $cap ),
			'crm'        => array( 'total' => $tot, 'avec_mail' => $avec_mail, 'eligibles' => $elig ),
			'activite'   => $acts,
			'ab_winner'  => $ab_winner,
			'gardien'    => $gardien,
			'bloquants'  => (array) ( $diag['bloquants'] ?? array() ),
			'dernier'    => (int) get_option( 'ag_auto_last', 0 ),
		);
	}
}

/* ── Version TEXTE (Telegram) ────────────────────────────────────────── */
if ( ! function_exists( 'ag_rapport_texte' ) ) {
	function ag_rapport_texte( $d = null ) {
		$d = $d ?: ag_rapport_data();
		$f = $d['funnel'];
		$L = array();
		$L[] = '📊 RAPPORT DU SOIR — ' . $d['date'];
		$L[] = '';
		$L[] = '🎯 Prospection';
		$L[] = '• Nouveaux prospects repérés : ' . $f['sourced'];
		$L[] = '• Mails de démarchage envoyés : ' . $d['hugo']['sent'] . ' / ' . $d['hugo']['cap'];
		$L[] = '• CRM : ' . $d['crm']['total'] . ' prospects (' . $d['crm']['avec_mail'] . ' avec email, ' . $d['crm']['eligibles'] . ' prêts à démarcher)';
		$L[] = '';
		$L[] = '🤝 Réponses & ventes';
		$L[] = '• Intéressés aujourd\'hui : ' . $f['interested'];
		$L[] = '• Nouveaux clients : ' . $f['client'];
		$L[] = '• Contrats signés : ' . $f['signed'] . ( $d['ca_signe'] > 0 ? ' (' . number_format_i18n( $d['ca_signe'], 0 ) . ' €)' : '' );
		$L[] = '• Désinscriptions : ' . $f['optout'];
		$L[] = '';
		$L[] = '🧠 Agents';
		$L[] = '• Max (A/B objet) : ' . ( '' !== $d['ab_winner'] ? 'variante « ' . $d['ab_winner'] . ' » gagnante' : 'exploration en cours' );
		if ( ! empty( $d['gardien'] ) ) {
			$g = $d['gardien'];
			$L[] = '• Gardien réputation : plafond ' . (int) $g['cap'] . '/j, désinscriptions ' . $g['taux'] . '% (feu ' . $g['feu'] . ')';
		}
		$L[] = '';
		if ( ! empty( $d['bloquants'] ) ) {
			$L[] = '⚠️ À débloquer :';
			foreach ( $d['bloquants'] as $b ) { $L[] = '• ' . $b; }
			$L[] = '';
		}
		// Journal du jour (12 dernières lignes).
		$acts = array_slice( $d['activite'], -12 );
		if ( $acts ) {
			$L[] = '📋 Journal du jour';
			foreach ( $acts as $a ) {
				$L[] = '• ' . wp_date( 'H:i', (int) $a['ts'] ) . ' — ' . (string) $a['t'];
			}
		} else {
			$L[] = '📋 Journal : aucune action enregistrée aujourd\'hui.';
		}
		$L[] = '';
		$L[] = 'Dernier passage auto : ' . ( $d['dernier'] ? human_time_diff( $d['dernier'], time() ) . ' avant maintenant' : 'jamais' );
		return implode( "\n", $L );
	}
}

/* ── Version HTML (email) ────────────────────────────────────────────── */
if ( ! function_exists( 'ag_rapport_html' ) ) {
	function ag_rapport_html( $d = null ) {
		$d = $d ?: ag_rapport_data();
		$f = $d['funnel'];
		$row = function ( $k, $v ) { return '<tr><td style="padding:6px 14px;color:#b0b0bc">' . esc_html( $k ) . '</td><td style="padding:6px 14px;color:#fff;font-weight:600">' . $v . '</td></tr>'; };
		$h  = '<h2 style="font-family:Arial,sans-serif;color:#D4B45C;margin:0 0 4px">📊 Rapport du soir</h2>';
		$h .= '<p style="font-family:Arial,sans-serif;color:#b0b0bc;margin:0 0 18px">' . esc_html( $d['date'] ) . '</p>';
		$h .= '<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;background:#15151c;border-radius:10px;overflow:hidden">';
		$h .= '<tr><td colspan="2" style="padding:10px 14px;background:#1f1f29;color:#D4B45C;font-weight:700">🎯 Prospection</td></tr>';
		$h .= $row( 'Nouveaux prospects repérés', (string) $f['sourced'] );
		$h .= $row( 'Mails de démarchage envoyés', $d['hugo']['sent'] . ' / ' . $d['hugo']['cap'] );
		$h .= $row( 'CRM', $d['crm']['total'] . ' prospects · ' . $d['crm']['avec_mail'] . ' avec email · ' . $d['crm']['eligibles'] . ' prêts' );
		$h .= '<tr><td colspan="2" style="padding:10px 14px;background:#1f1f29;color:#D4B45C;font-weight:700">🤝 Réponses & ventes</td></tr>';
		$h .= $row( 'Intéressés aujourd\'hui', (string) $f['interested'] );
		$h .= $row( 'Nouveaux clients', (string) $f['client'] );
		$h .= $row( 'Contrats signés', $f['signed'] . ( $d['ca_signe'] > 0 ? ' (' . number_format_i18n( $d['ca_signe'], 0 ) . ' €)' : '' ) );
		$h .= $row( 'Désinscriptions', (string) $f['optout'] );
		$h .= '<tr><td colspan="2" style="padding:10px 14px;background:#1f1f29;color:#D4B45C;font-weight:700">🧠 Agents</td></tr>';
		$h .= $row( 'Max (A/B objet)', '' !== $d['ab_winner'] ? 'variante « ' . esc_html( $d['ab_winner'] ) . ' » gagnante' : 'exploration en cours' );
		if ( ! empty( $d['gardien'] ) ) {
			$g = $d['gardien'];
			$h .= $row( 'Gardien réputation', 'plafond ' . (int) $g['cap'] . '/j · opt-out ' . esc_html( (string) $g['taux'] ) . '% · feu ' . esc_html( $g['feu'] ) );
		}
		$h .= '</table>';
		if ( ! empty( $d['bloquants'] ) ) {
			$h .= '<div style="font-family:Arial,sans-serif;background:#3a2e0a;border-left:4px solid #dba617;padding:10px 14px;margin:14px 0;border-radius:6px"><strong style="color:#ffd76a">À débloquer :</strong><ul style="margin:6px 0 0;color:#e8e6e0">';
			foreach ( $d['bloquants'] as $b ) { $h .= '<li>' . esc_html( $b ) . '</li>'; }
			$h .= '</ul></div>';
		}
		$acts = array_slice( $d['activite'], -20 );
		$h .= '<h3 style="font-family:Arial,sans-serif;color:#D4B45C;margin:18px 0 6px">📋 Journal du jour</h3>';
		if ( $acts ) {
			$h .= '<ul style="font-family:Arial,sans-serif;font-size:13px;color:#d0d0d8;line-height:1.7;margin:0;padding-left:18px">';
			foreach ( $acts as $a ) { $h .= '<li><span style="color:#8a8a94">' . wp_date( 'H:i', (int) $a['ts'] ) . '</span> — ' . esc_html( (string) $a['t'] ) . '</li>'; }
			$h .= '</ul>';
		} else {
			$h .= '<p style="font-family:Arial,sans-serif;color:#b0b0bc">Aucune action enregistrée aujourd\'hui.</p>';
		}
		$h .= '<p style="font-family:Arial,sans-serif;color:#8a8a94;font-size:12px;margin-top:16px">Dernier passage automatique : ' . ( $d['dernier'] ? human_time_diff( $d['dernier'], time() ) . ' avant l\'envoi' : 'jamais' ) . '.</p>';
		return $h;
	}
}

/* ── Envoi (email + Telegram + SMS court) ────────────────────────────── */
if ( ! function_exists( 'ag_rapport_envoyer' ) ) {
	function ag_rapport_envoyer() {
		$d     = ag_rapport_data();
		$sujet = '📊 Rapport du soir — ' . $d['date'];

		// Destinataires email : boîte maison + Gmail de Fabrice (si réglé).
		$dests = array();
		$maison = apply_filters( 'ag_calendar_notify_email', get_option( 'ag_calendar_email', 'advise.alliance.group@gmail.com' ) );
		if ( is_email( $maison ) ) { $dests[] = $maison; }
		$gmail = trim( (string) get_option( 'ag_contrat_gmail', '' ) );
		if ( is_email( $gmail ) ) { $dests[] = $gmail; }
		$over = trim( (string) get_option( 'ag_rapport_email', '' ) );
		if ( is_email( $over ) ) { $dests[] = $over; }
		$dests = array_values( array_unique( array_filter( $dests ) ) );

		$html = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Rapport du soir', ag_rapport_html( $d ) ) : ag_rapport_html( $d );
		if ( $dests ) {
			wp_mail( $dests, $sujet, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		// Telegram interne : rapport détaillé en texte.
		if ( function_exists( 'ag_tg_send' ) && function_exists( 'ag_tg_cfg' ) ) {
			ag_tg_send( ag_tg_cfg( 'chat' ), ag_rapport_texte( $d ) );
		}

		// SMS : résumé court (le détail est sur Telegram + email).
		if ( function_exists( 'ag_sms' ) ) {
			$f = $d['funnel'];
			ag_sms( '📊 Rapport du soir : ' . $d['hugo']['sent'] . ' mails, ' . $f['interested'] . ' intéressés, ' . $f['signed'] . ' signé(s). Détail : Telegram + email.' );
		}
		return true;
	}
}

/* ── Cadence : une fois par jour, à partir de l'heure réglée (20h) ───── */
if ( ! function_exists( 'ag_rapport_cron_maybe' ) ) {
	function ag_rapport_cron_maybe() {
		if ( ! ag_rapport_on() ) { return false; }
		$auj = wp_date( 'Y-m-d' );
		if ( (string) get_option( 'ag_rapport_soir_jour', '' ) === $auj ) { return false; } // déjà envoyé aujourd'hui
		$heure = (int) wp_date( 'G' );
		if ( $heure < ag_rapport_heure() ) { return false; } // pas encore l'heure
		update_option( 'ag_rapport_soir_jour', $auj, false );
		ag_rapport_envoyer();
		return true;
	}
}

/* ── Écran admin : aperçu + test + réglages ──────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Rapport du soir', '📊 Rapport du soir', 'manage_options', 'ag-rapport', 'ag_rapport_render' );
}, 24 );

add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_rapport_save'] ) && check_admin_referer( 'ag_rapport' ) && current_user_can( 'manage_options' ) ) {
		update_option( 'ag_rapport_soir_on', isset( $_POST['on'] ) ? 1 : 0, false );
		update_option( 'ag_rapport_heure', max( 0, min( 23, (int) ( $_POST['heure'] ?? 20 ) ) ), false );
		update_option( 'ag_rapport_email', sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ), false );
	}
	if ( isset( $_POST['ag_rapport_test'] ) && check_admin_referer( 'ag_rapport' ) && current_user_can( 'manage_options' ) ) {
		ag_rapport_envoyer();
		set_transient( 'ag_rapport_test_ok', 1, 60 );
	}
} );

if ( ! function_exists( 'ag_rapport_render' ) ) {
	function ag_rapport_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		echo '<div class="wrap"><h1>📊 Rapport du soir</h1>';
		echo '<p class="description">Chaque soir à ' . (int) ag_rapport_heure() . 'h, un rapport détaillé de ce que les agents ont fait part par <strong>email + Telegram</strong> (+ un SMS court). Plus besoin d\'ouvrir le wp-admin.</p>';
		if ( get_transient( 'ag_rapport_test_ok' ) ) { delete_transient( 'ag_rapport_test_ok' ); echo '<div style="background:#edfaef;border-left:4px solid #1e7e34;padding:10px 14px;max-width:820px;margin:10px 0">✅ Rapport de test envoyé (email + Telegram + SMS).</div>'; }

		echo '<form method="post" style="margin:10px 0 20px;max-width:620px">';
		wp_nonce_field( 'ag_rapport' );
		echo '<p><label><input type="checkbox" name="on" ' . checked( ag_rapport_on(), true, false ) . '> Rapport du soir actif</label></p>';
		echo '<p>Heure d\'envoi : <input type="number" name="heure" min="0" max="23" value="' . esc_attr( (string) ag_rapport_heure() ) . '" style="width:70px"> h</p>';
		echo '<p>Email supplémentaire (optionnel) : <input type="email" name="email" value="' . esc_attr( (string) get_option( 'ag_rapport_email', '' ) ) . '" style="width:320px" placeholder="en plus de la boîte maison + ton Gmail"></p>';
		echo '<button class="button button-primary" name="ag_rapport_save" value="1">Enregistrer</button> ';
		echo '<button class="button" name="ag_rapport_test" value="1">📤 Envoyer un test maintenant</button>';
		echo '</form>';

		echo '<h2>Aperçu du rapport (aujourd\'hui)</h2>';
		echo '<div style="max-width:860px;background:#0f0f14;padding:18px;border-radius:12px">' . ag_rapport_html() . '</div>';
		echo '</div>';
	}
}
