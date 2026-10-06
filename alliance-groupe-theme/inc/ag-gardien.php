<?php
/**
 * ag-gardien.php — LE GARDIEN DE RÉPUTATION (sécurité + chauffe, autonome).
 *
 * Son seul client : la boîte de réception. Un domaine grillé = TOUS les mails
 * d'Alliance Groupe (et de Gwen) en spam. Le Gardien agit tout seul, 1×/jour :
 *  - si le taux de désinscription monte → il BAISSE le plafond d'Hugo (frein) ;
 *  - si tout va bien (peu d'opt-out, assez d'envois) → il MONTE le plafond d'un
 *    cran (chauffe progressive du domaine), jusqu'à un maximum réglable.
 *
 * Il ne demande rien à personne. Bornes dures : plancher 5, plafond réglable
 * (défaut 50). Chaque décision est notifiée (Telegram/SMS).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ag_gardien_on' ) ) {
	function ag_gardien_on() { return (bool) get_option( 'ag_gardien_on', 1 ); } // autonome par défaut
}
if ( ! function_exists( 'ag_gardien_cap_max' ) ) {
	function ag_gardien_cap_max() { return max( 5, (int) get_option( 'ag_gardien_cap_max', 50 ) ); }
}

/* ── Compte un type d'événement sur une fenêtre ──────────────────────── */
if ( ! function_exists( 'ag_gardien_compte' ) ) {
	function ag_gardien_compte( $type, $jours = 7 ) {
		$since = time() - $jours * DAY_IN_SECONDS;
		$n     = 0;
		foreach ( (array) get_option( 'ag_funnel_events', array() ) as $e ) {
			if ( (int) ( $e['t'] ?? 0 ) >= $since && (string) ( $e['type'] ?? '' ) === $type ) { $n++; }
		}
		return $n;
	}
}

/* ── État courant (pour l'écran et la décision) ──────────────────────── */
if ( ! function_exists( 'ag_gardien_etat' ) ) {
	function ag_gardien_etat() {
		$sent = ag_gardien_compte( 'sent', 7 );
		$opt  = ag_gardien_compte( 'optout', 7 );
		$cap  = function_exists( 'ag_closer_cap' ) ? ag_closer_cap() : (int) get_option( 'ag_closer_cap_jour', 20 );
		$taux = $sent > 0 ? round( 100 * $opt / $sent, 2 ) : 0.0;
		$feu  = 'vert';
		if ( $taux > 4.0 ) { $feu = 'rouge'; }
		elseif ( $taux > 2.0 ) { $feu = 'orange'; }
		return array(
			'sent7' => $sent, 'optout7' => $opt, 'taux' => $taux,
			'cap'   => $cap, 'cap_max' => ag_gardien_cap_max(), 'feu' => $feu,
			'dernier' => (array) get_option( 'ag_gardien_dernier', array() ),
		);
	}
}

/* ── La décision autonome, 1×/jour (branchée sur le pilote auto) ─────── */
if ( ! function_exists( 'ag_gardien_cron_maybe' ) ) {
	function ag_gardien_cron_maybe() {
		if ( ! ag_gardien_on() ) { return false; }
		if ( time() - (int) get_option( 'ag_gardien_last', 0 ) < 72000 ) { return false; } // ~20 h
		update_option( 'ag_gardien_last', time(), false );

		$plancher = 5;
		$e    = ag_gardien_etat();
		$cap  = (int) $e['cap']; $max = (int) $e['cap_max'];
		$sent = (int) $e['sent7']; $taux = (float) $e['taux'];
		if ( $sent < 10 ) { return false; } // pas assez de volume pour juger

		$action = 'hold'; $new = $cap;
		if ( $taux > 4.0 && $cap > $plancher ) {
			$new = max( $plancher, $cap - 5 ); $action = 'frein';
		} elseif ( $taux < 1.5 && $cap < $max ) {
			$new = min( $max, $cap + 5 ); $action = 'chauffe';
		}

		if ( 'hold' !== $action && $new !== $cap ) {
			update_option( 'ag_closer_cap_jour', $new, false );
			update_option( 'ag_gardien_dernier', array( 'quand' => time(), 'action' => $action, 'de' => $cap, 'vers' => $new, 'taux' => $taux ), false );
			if ( function_exists( 'ag_push' ) ) {
				$msg = ( 'frein' === $action )
					? '🛡️ Frein : désinscriptions à ' . $taux . '% → plafond Hugo ' . $cap . ' → ' . $new . '/jour. On protège la délivrabilité.'
					: '🛡️ Chauffe : tout va bien (' . $taux . '% d\'opt-out) → plafond Hugo ' . $cap . ' → ' . $new . '/jour.';
				ag_push( '🛡️ Gardien de réputation', $msg );
			}
			return true;
		}
		return false;
	}
}

/* ── Écran : Prospection → 🛡️ Réputation ─────────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Réputation', '🛡️ Réputation', 'manage_options', 'ag-gardien', 'ag_gardien_render' );
}, 28 );

add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_gardien_save'] ) && check_admin_referer( 'ag_gardien' ) && current_user_can( 'manage_options' ) ) {
		update_option( 'ag_gardien_on', isset( $_POST['on'] ) ? 1 : 0, false );
		update_option( 'ag_gardien_cap_max', max( 5, (int) ( $_POST['cap_max'] ?? 50 ) ), false );
	}
} );

if ( ! function_exists( 'ag_gardien_render' ) ) {
	function ag_gardien_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$e   = ag_gardien_etat();
		$coul = array( 'vert' => '#1e7e34', 'orange' => '#996800', 'rouge' => '#b32d2e' );
		echo '<div class="wrap"><h1>🛡️ Gardien de réputation</h1>';
		echo '<p class="description">Protège la délivrabilité (9,5/10). Agit tout seul 1×/jour : frein si les désinscriptions montent, chauffe progressive sinon. Plancher 5/jour, plafond réglable.</p>';

		echo '<p>Feu : <strong style="color:' . esc_attr( $coul[ $e['feu'] ] ?? '#333' ) . '">' . esc_html( strtoupper( $e['feu'] ) ) . '</strong></p>';
		echo '<table class="widefat striped" style="max-width:520px"><tbody>';
		echo '<tr><th style="text-align:left">Mails envoyés (7 j)</th><td>' . (int) $e['sent7'] . '</td></tr>';
		echo '<tr><th style="text-align:left">Désinscriptions (7 j)</th><td>' . (int) $e['optout7'] . '</td></tr>';
		echo '<tr><th style="text-align:left">Taux de désinscription</th><td><strong>' . esc_html( (string) $e['taux'] ) . ' %</strong> (frein > 4 %, chauffe < 1,5 %)</td></tr>';
		echo '<tr><th style="text-align:left">Plafond Hugo actuel</th><td>' . (int) $e['cap'] . ' / jour</td></tr>';
		echo '</tbody></table>';

		if ( ! empty( $e['dernier'] ) ) {
			$d = $e['dernier'];
			echo '<p class="description">Dernière décision : <strong>' . esc_html( (string) ( $d['action'] ?? '' ) ) . '</strong> — plafond ' . (int) ( $d['de'] ?? 0 ) . ' → ' . (int) ( $d['vers'] ?? 0 ) . ' (' . esc_html( (string) ( $d['taux'] ?? 0 ) ) . '% d\'opt-out), le ' . esc_html( date_i18n( 'd/m/Y H:i', (int) ( $d['quand'] ?? time() ) ) ) . '.</p>';
		}

		echo '<form method="post" style="margin-top:14px">';
		wp_nonce_field( 'ag_gardien' );
		echo '<p><label><input type="checkbox" name="on" ' . checked( ag_gardien_on(), true, false ) . '> Gardien actif (autonome)</label></p>';
		echo '<p>Plafond maximum autorisé : <input type="number" name="cap_max" min="5" max="500" value="' . esc_attr( (string) ag_gardien_cap_max() ) . '" style="width:90px"> mails/jour</p>';
		echo '<button class="button button-primary" name="ag_gardien_save" value="1">Enregistrer</button>';
		echo '</form></div>';
	}
}
