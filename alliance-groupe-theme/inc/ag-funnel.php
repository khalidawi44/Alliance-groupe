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
			't'        => time(),
			'type'     => $type,
			'metier'   => isset( $seg['metier'] ) ? sanitize_text_field( substr( (string) $seg['metier'], 0, 60 ) ) : '',
			'ville'    => isset( $seg['ville'] ) ? sanitize_text_field( substr( (string) $seg['ville'], 0, 60 ) ) : '',
			'etape'    => isset( $seg['etape'] ) ? (int) $seg['etape'] : 0,
			'canal'    => isset( $seg['canal'] ) ? sanitize_key( (string) $seg['canal'] ) : 'email',
			'montant'  => isset( $seg['montant'] ) ? ag_funnel_montant( $seg['montant'] ) : 0.0,
			'variante' => isset( $seg['variante'] ) ? sanitize_key( (string) $seg['variante'] ) : '',
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
	$metier = ''; $ville = ''; $variante = '';
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
		if ( (string) ( $p['id'] ?? '' ) === (string) $id ) {
			$metier = $p['type'] ?? ''; $ville = $p['city'] ?? ''; $variante = (string) ( $p['ab'] ?? '' ); break;
		}
	}
	// On reporte la variante A/B reçue par ce prospect → Max attribue la conversion.
	ag_funnel_log( $t, array( 'metier' => $metier, 'ville' => $ville, 'variante' => $variante ) );
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

/* ── Écran : Prospection → 🎯 Signaux du filon (go / pivot) ───────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Signaux du filon', '🎯 Signaux du filon', 'manage_options', 'ag-signaux', 'ag_signaux_render' );
}, 11 );

if ( ! function_exists( 'ag_signaux_render' ) ) {
	/**
	 * Tableau de bord des signaux du filon (seuils de docs/FILON.md) + verdict
	 * go / observation / pivot, pour décider d'un coup d'œil. Lecture seule.
	 */
	function ag_signaux_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$jours = isset( $_GET['j'] ) ? max( 1, (int) $_GET['j'] ) : 30;
		$since = time() - $jours * DAY_IN_SECONDS;

		$events = (array) get_option( 'ag_funnel_events', array() );
		$sent = 0; $interested = 0; $signed = 0; $optout = 0; $ca = 0.0;
		$seg = array();
		foreach ( $events as $e ) {
			if ( (int) ( $e['t'] ?? 0 ) < $since ) { continue; }
			$t = (string) ( $e['type'] ?? '' );
			$m = ( '' !== (string) ( $e['metier'] ?? '' ) ) ? (string) $e['metier'] : '(inconnu)';
			if ( 'sent' === $t )            { $sent++; }
			elseif ( 'interested' === $t )  { $interested++; }
			elseif ( 'signed' === $t )      { $signed++; $ca += (float) ( $e['montant'] ?? 0 ); }
			elseif ( 'optout' === $t )      { $optout++; }
			if ( in_array( $t, array( 'sent', 'interested', 'signed' ), true ) ) {
				if ( ! isset( $seg[ $m ] ) ) { $seg[ $m ] = array( 'sent' => 0, 'interested' => 0, 'signed' => 0 ); }
				$seg[ $m ][ $t ]++;
			}
		}

		// Demandes de rapport (lead magnet accessibilité/SEO) = prospects source audit-seo (cumulé).
		$reqs = 0;
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( 'audit-seo' === (string) ( $p['source'] ?? '' ) ) { $reqs++; }
		}

		$rate   = function ( $n, $d ) { return $d > 0 ? round( 100 * $n / $d, 1 ) : 0.0; };
		$tx_int = $rate( $interested, $sent );
		$tx_opt = $rate( $optout, $sent );
		$tx_sig = $rate( $signed, $sent );

		// Couleur d'un taux selon des seuils (haut = mieux, sauf opt-out inversé).
		$col_up   = function ( $v, $ok, $warn ) { return $v >= $ok ? '#28a745' : ( $v >= $warn ? '#F37A1F' : '#E10F1A' ); };
		$col_down = function ( $v, $ok, $warn ) { return $v <= $ok ? '#28a745' : ( $v <= $warn ? '#F37A1F' : '#E10F1A' ); };

		// Verdict go / observation / pivot (seuils docs/FILON.md).
		if ( $sent < 50 ) {
			$vcol = '#6c757d'; $verdict = 'Pas assez de données';
			$vwhy = 'Vise ~150 à 200 envois pour une première signature. Laisse la machine tourner.';
		} elseif ( $tx_int >= 5 ) {
			$vcol = '#28a745'; $verdict = '🟢 GO — le filon convertit';
			$vwhy = 'Taux envoyé→intéressé ≥ 5 %. On pousse le volume sur ce filon.';
		} elseif ( $sent >= 300 && $tx_int < 3 ) {
			$vcol = '#E10F1A'; $verdict = '🔴 PIVOT';
			$vwhy = '300+ envois et moins de 3 % d\'intéressés : basculer l\'effort sur l\'immobilier (angle refonte).';
		} else {
			$vcol = '#F37A1F'; $verdict = '🟠 En observation';
			$vwhy = 'Entre les seuils : continuer, et laisser Max (objets A/B) + le Scoreur affiner le ciblage.';
		}

		$card = function ( $label, $value, $sub = '', $color = '#1d2327' ) {
			echo '<div style="flex:1 1 150px;min-width:150px;background:#fff;border:1px solid #dcdcde;border-left:4px solid ' . esc_attr( $color ) . ';border-radius:8px;padding:14px 16px">';
			echo '<div style="font-size:12px;color:#646970;text-transform:uppercase;letter-spacing:.5px">' . esc_html( $label ) . '</div>';
			echo '<div style="font-size:26px;font-weight:700;color:' . esc_attr( $color ) . ';line-height:1.2">' . esc_html( $value ) . '</div>';
			if ( '' !== $sub ) { echo '<div style="font-size:12px;color:#646970">' . esc_html( $sub ) . '</div>'; }
			echo '</div>';
		};
		?>
		<div class="wrap">
			<h1>🎯 Signaux du filon</h1>
			<p>
				Fenêtre :
				<?php foreach ( array( 7 => '7 j', 30 => '30 j', 90 => '90 j' ) as $jv => $jl ) :
					$u = admin_url( 'admin.php?page=ag-signaux&j=' . $jv ); ?>
					<a href="<?php echo esc_url( $u ); ?>" style="margin-right:8px;<?php echo $jours === $jv ? 'font-weight:700;text-decoration:none' : ''; ?>"><?php echo esc_html( $jl ); ?></a>
				<?php endforeach; ?>
			</p>

			<div style="margin:14px 0 20px;padding:16px 20px;border-radius:10px;background:<?php echo esc_attr( $vcol ); ?>12;border:1px solid <?php echo esc_attr( $vcol ); ?>;">
				<div style="font-size:20px;font-weight:800;color:<?php echo esc_attr( $vcol ); ?>"><?php echo esc_html( $verdict ); ?></div>
				<div style="color:#1d2327;margin-top:4px"><?php echo esc_html( $vwhy ); ?></div>
			</div>

			<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:8px">
				<?php
				$card( 'Envoyés (' . $jours . ' j)', (string) $sent );
				$card( 'Intéressés', $interested . ' · ' . $tx_int . ' %', 'cible ≥ 5 %', $col_up( $tx_int, 5, 3 ) );
				$card( 'Désinscriptions', $optout . ' · ' . $tx_opt . ' %', 'cible ≤ 2 %', $col_down( $tx_opt, 2, 4 ) );
				$card( 'Demandes de rapport', (string) $reqs, 'cumulé (lead magnet)', '#2271b1' );
				$card( 'Signés', $signed . ' · ' . number_format_i18n( $ca, 0 ) . ' €', $tx_sig . ' % des envois', $signed > 0 ? '#28a745' : '#646970' );
				?>
			</div>

			<h2 style="margin-top:22px">Par métier</h2>
			<?php
			uasort( $seg, function ( $a, $b ) {
				return array( $b['signed'], $b['interested'], $b['sent'] ) <=> array( $a['signed'], $a['interested'], $a['sent'] );
			} );
			if ( empty( $seg ) ) {
				echo '<p>Pas encore de données segmentées sur la fenêtre. Laisse tourner.</p>';
			} else {
				echo '<table class="widefat striped" style="max-width:760px"><thead><tr><th>Métier</th><th>Envoyés</th><th>Intéressés</th><th>Signés</th><th>Envoyé→Intéressé</th><th></th></tr></thead><tbody>';
				foreach ( $seg as $m => $s ) {
					$tx  = $s['sent'] > 0 ? round( 100 * $s['interested'] / $s['sent'], 1 ) : 0;
					$bdg = '';
					if ( $s['sent'] >= 8 && 0 === $s['interested'] ) { $bdg = '<span style="color:#E10F1A">⚠️ fuite</span>'; }
					elseif ( $s['signed'] > 0 || ( $s['sent'] >= 8 && $tx >= 5 ) ) { $bdg = '<span style="color:#28a745">🏆 pépite</span>'; }
					echo '<tr><td>' . esc_html( $m ) . '</td><td>' . (int) $s['sent'] . '</td><td>' . (int) $s['interested'] . '</td><td>' . (int) $s['signed'] . '</td><td>' . esc_html( (string) $tx ) . ' %</td><td>' . wp_kses_post( $bdg ) . '</td></tr>';
				}
				echo '</tbody></table>';
			}
			?>

			<p class="description" style="margin-top:16px;max-width:760px">
				Seuils (voir <code>docs/FILON.md</code>) : <strong>intéressés ≥ 5 %</strong> = on pousse ;
				<strong>désinscriptions ≤ 2 %</strong> = délivrabilité saine (le Gardien freine au-delà de 4 %) ;
				demandes de rapport = intérêt réel pour le lead magnet. Première signature visée sous ~150-200 envois.
				Si 300-400 envois et intéressés &lt; 3 % → pivot vers l'immobilier (angle refonte).
			</p>
		</div>
		<?php
	}
}

/* ── Écran : Prospection → 💶 Suivi des ventes (vue par prospect) ─────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Suivi des ventes', '💶 Suivi des ventes', 'manage_options', 'ag-suivi', 'ag_suivi_render' );
}, 9 );

if ( ! function_exists( 'ag_suivi_render' ) ) {
	/**
	 * Vue PAR PROSPECT (pas par message) : où en est chacun, pour agir.
	 * Répond à « je veux toutes les infos et suivre tous les prospects » +
	 * « pour que les clients payent » (les chauds d'abord). Lecture seule.
	 */
	function ag_suivi_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$list = (array) get_option( 'ag_prospects', array() );

		$chauds = array(); $clients = array(); $muets = array(); $suspects = array();
		foreach ( $list as $p ) {
			$st = (string) ( $p['status'] ?? '' );
			if ( in_array( $st, array( 'client' ), true ) ) { $clients[] = $p; continue; }
			if ( 'interesse' === $st || ! empty( $p['replied'] ) ) {
				if ( ! in_array( $st, array( 'refus', 'ne_pas_contacter', 'ignore' ), true ) ) {
					if ( function_exists( 'ag_prospect_parasite' ) && ag_prospect_parasite( $p ) ) { $suspects[] = $p; }
					else { $chauds[] = $p; }
					continue;
				}
			}
			// Séquence d'Hugo terminée (4/4) sans réponse → à travailler autrement.
			if ( (int) ( $p['closer_step'] ?? 0 ) >= 4 && empty( $p['replied'] )
				&& ! in_array( $st, array( 'refus', 'ne_pas_contacter', 'ignore', 'client' ), true ) ) {
				$muets[] = $p;
			}
		}

		$tel = function ( $p ) {
			$t = (string) ( $p['phone'] ?? ( $p['phone_intl'] ?? '' ) );
			return $t ? '<a href="tel:' . esc_attr( $t ) . '">' . esc_html( $t ) . '</a>' : '—';
		};
		$mail = function ( $p ) {
			$e = (string) ( $p['email'] ?? '' );
			return $e ? '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>' : '—';
		};
		$table = function ( $rows, $vide ) use ( $tel, $mail ) {
			if ( empty( $rows ) ) { echo '<p style="color:#50575e"><em>' . esc_html( $vide ) . '</em></p>'; return; }
			echo '<table class="widefat striped" style="max-width:1000px"><thead><tr><th>Entreprise</th><th>Métier</th><th>Ville</th><th>Email</th><th>Téléphone</th><th>Statut</th><th>Étape</th><th>Dernier contact</th></tr></thead><tbody>';
			foreach ( $rows as $p ) {
				echo '<tr>'
					. '<td><strong>' . esc_html( (string) ( $p['name'] ?? '' ) ) . '</strong></td>'
					. '<td>' . esc_html( (string) ( $p['type'] ?? '' ) ) . '</td>'
					. '<td>' . esc_html( (string) ( $p['city'] ?? '' ) ) . '</td>'
					. '<td>' . wp_kses_post( $mail( $p ) ) . '</td>'
					. '<td>' . wp_kses_post( $tel( $p ) ) . '</td>'
					. '<td>' . esc_html( (string) ( $p['status'] ?? '' ) ) . '</td>'
					. '<td>' . (int) ( $p['closer_step'] ?? 0 ) . '/4</td>'
					. '<td>' . esc_html( (string) ( $p['last_contact'] ?? ( $p['date_reply'] ?? '' ) ) ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
		};
		?>
		<div class="wrap">
			<h1>💶 Suivi des ventes</h1>
			<p>Vue <strong>par prospect</strong> (le journal d'Hugo, lui, montre les messages). Agis d'abord sur les <strong>chauds</strong> : ce sont eux qui deviennent des clients qui paient.</p>
			<p style="font-size:1.05rem">🔥 <strong><?php echo count( $chauds ); ?></strong> chauds · ✅ <strong><?php echo count( $clients ); ?></strong> clients · 📬 <strong><?php echo count( $muets ); ?></strong> relancés 4/4 sans réponse · 🧹 <strong><?php echo count( $suspects ); ?></strong> suspects (faux intéressés)</p>

			<h2 style="color:#b91c1c">🔥 Chauds — à transformer en client (ont répondu ou intéressés)</h2>
			<p class="description">Appelle-les ou envoie le contrat / le lien de paiement. C'est là que se gagnent les ventes.</p>
			<?php $table( $chauds, 'Aucun prospect chaud pour l\'instant. Laisse la machine ramener des réponses.' ); ?>

			<h2 style="color:#16a34a;margin-top:26px">✅ Clients (affaires conclues)</h2>
			<?php $table( $clients, 'Pas encore de client enregistré.' ); ?>

			<h2 style="margin-top:26px">📬 Séquence terminée (4/4) sans réponse</h2>
			<p class="description">Hugo a fait ses 4 messages, sans réponse. L'email est épuisé pour eux : à travailler autrement (appel du robot vocal si joignable, courrier, ou on les laisse). <strong>C'est ce que tu voyais dans le journal.</strong></p>
			<?php $table( $muets, 'Personne en 4/4 sans réponse pour l\'instant.' ); ?>

			<h2 style="color:#8a6d00;margin-top:26px">🧹 Suspects — faux intéressés (à ignorer)</h2>
			<p class="description">Classés « intéressé » à tort : notre propre domaine, boîtes techniques, ou domaines de spam connus (ex. freeb2bdata.org). Ils sont <strong>exclus automatiquement</strong> des relances et de Hugo. Tu peux en ajouter via l'option <code>ag_prospect_blocklist</code>. Ils ne comptent pas comme de vrais prospects.</p>
			<?php $table( $suspects, 'Aucun faux intéressé détecté.' ); ?>
		</div>
		<?php
	}
}
