<?php
/**
 * LE RELANCEUR DE CHAUD — rattraper ce qui allait signer
 * ---------------------------------------------------------------------------
 * Le constat qui a déclenché ce module : le froid (jamais répondu) est
 * industrialisé par Hugo, mais le CHAUD meurt en silence. Trois pistes, les
 * plus proches de l'argent, n'étaient rattrapées par rien :
 *
 *   A. Un contrat ENVOYÉ mais NON SIGNÉ. Le prospect a dit oui, a reçu le
 *      document, et ne clique pas. Aucun rappel. La fuite la plus chère.
 *   B. Un « intéressé » SILENCIEUX. Il a écrit « ça m'intéresse », puis plus
 *      rien. Hugo l'exclut, le cron de relance l'exclut, Enzo ne le reprend
 *      que s'il ré-écrit. Angle mort total.
 *   C. Un devis / une maquette demandés, SANS SUITE. Même statut « interesse »,
 *      même abandon.
 *
 * Ce module les relance — par E-MAIL, et par SMS quand le numéro est un mobile.
 * Le SMS (~98 % lu) est branché LÀ où il gagne : sur un contact qui a déjà
 * engagé la conversation, jamais à froid.
 *
 * ─────────────────────────────────────────────────────────────────────────
 * CE QUI PROTÈGE (tenu par le code) :
 *  · Opt-out respecté : `ag_sms_is_optout` pour le SMS ; statut refus /
 *    ne_pas_contacter exclu pour l'e-mail.
 *  · JAMAIS de harcèlement : deux relances maximum par piste, puis silence.
 *  · Délais : première relance à J+2, seconde à J+5. Pas avant.
 *  · Chaque relance est horodatée sur la piste : jamais deux fois la même.
 *  · Éteint par défaut. Rien ne part tant que ce n'est pas armé.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_RC_VER' ) ) { define( 'AG_RC_VER', '1.0.0' ); }

/** Délais avant chaque relance (jours depuis le dernier contact). */
if ( ! defined( 'AG_RC_J1' ) ) { define( 'AG_RC_J1', 2 ); }
if ( ! defined( 'AG_RC_J2' ) ) { define( 'AG_RC_J2', 5 ); }

/* ── 1. Réglages ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_rc_on' ) ) {
	function ag_rc_on() { return (bool) get_option( 'ag_rc_on', 0 ); }
}
if ( ! function_exists( 'ag_rc_cap' ) ) {
	function ag_rc_cap() { return max( 1, (int) get_option( 'ag_rc_cap', 40 ) ); }
}
if ( ! function_exists( 'ag_rc_sms_ok' ) ) {
	/** Le SMS est-il utilisable ? (passerelle prête ET numéro mobile ET pas opt-out) */
	function ag_rc_sms_ok( $tel ) {
		if ( '' === trim( (string) $tel ) ) { return false; }
		if ( ! function_exists( 'ag_sms_gateway_ready' ) || ! ag_sms_gateway_ready() ) { return false; }
		if ( function_exists( 'ag_phone_is_mobile' ) && ! ag_phone_is_mobile( $tel ) ) { return false; }
		if ( function_exists( 'ag_sms_is_optout' ) && ag_sms_is_optout( $tel ) ) { return false; }
		return true;
	}
}

/* ── 2. A — Contrats envoyés, non signés ─────────────────────────────── */

if ( ! function_exists( 'ag_rc_relance_contrats' ) ) {
	/**
	 * Balaye les dossiers de signature en attente et relance ceux qui le
	 * méritent. Retourne le nombre de relances envoyées.
	 */
	function ag_rc_relance_contrats( $reste ) {
		if ( ! function_exists( 'ag_sign_all' ) || ! function_exists( 'ag_sign_put' ) ) { return 0; }
		$envoyees = 0;

		foreach ( ag_sign_all() as $d ) {
			if ( $envoyees >= $reste ) { break; }

			$statut = (string) ( $d['statut'] ?? '' );
			if ( in_array( $statut, array( 'signe', 'contresigne' ), true ) ) { continue; } // déjà signé
			$email = sanitize_email( (string) ( $d['client_email'] ?? '' ) );
			if ( ! is_email( $email ) ) { continue; }

			$age  = ( time() - (int) ( $d['created'] ?? time() ) ) / DAY_IN_SECONDS;
			$etape = (int) ( $d['rc_step'] ?? 0 );
			$last  = (int) ( $d['rc_last'] ?? ( $d['created'] ?? 0 ) );
			$depuis = ( time() - $last ) / DAY_IN_SECONDS;

			// Quelle relance est due ?
			if ( 0 === $etape && $age >= AG_RC_J1 )      { $etape_a_faire = 1; }
			elseif ( 1 === $etape && $depuis >= ( AG_RC_J2 - AG_RC_J1 ) ) { $etape_a_faire = 2; }
			else { continue; } // pas encore l'heure, ou déjà 2 relances

			$lien = add_query_arg( 't', (string) ( $d['token'] ?? '' ), home_url( '/signer' ) );
			$nom  = (string) ( $d['client_nom'] ?? '' );

			$sujet = 2 === $etape_a_faire
				? 'Votre contrat vous attend toujours'
				: 'Il vous reste un contrat à signer';
			$corps = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
				. 'Bonjour' . ( $nom ? ' ' . esc_html( $nom ) : '' ) . ',</p>'
				. '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
				. 'Votre contrat pour <strong>' . esc_html( (string) ( $d['objet'] ?? 'votre projet' ) ) . '</strong> '
				. 'est prêt et vous attend. La signature se fait en ligne, en une minute.</p>'
				. ( function_exists( 'ag_email_button' ) ? ag_email_button( 'Lire et signer le contrat', $lien )
					: '<p><a href="' . esc_url( $lien ) . '">' . esc_html( $lien ) . '</a></p>' )
				. '<p style="font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#b0b0bc;">'
				. 'Si quelque chose vous retient, répondez simplement à ce message : on en parle avant de signer.</p>';

			$html = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( $sujet, $corps ) : $corps;
			$ok   = wp_mail( $email, $sujet, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );

			// SMS en complément si mobile.
			$tel = (string) ( $d['client_tel'] ?? '' );
			if ( ag_rc_sms_ok( $tel ) ) {
				ag_sms_send( $tel, 'Bonjour, votre contrat Alliance Groupe vous attend, il se signe en ligne en 1 min : '
					. $lien . ' — STOP pour ne plus recevoir.' );
			}

			if ( $ok ) {
				// Marque le dossier (relire la liste pour ne rien écraser).
				$all = ag_sign_all();
				foreach ( $all as $i => $x ) {
					if ( (string) ( $x['token'] ?? '' ) === (string) ( $d['token'] ?? '' ) ) {
						$all[ $i ]['rc_step'] = $etape_a_faire;
						$all[ $i ]['rc_last'] = time();
						break;
					}
				}
				update_option( 'ag_signatures', $all, false );
				$envoyees++;
				if ( function_exists( 'ag_push' ) && 2 === $etape_a_faire ) {
					ag_push( '📄 Contrat relancé (2e)', ( $nom ?: $email ) . ' — dernière relance envoyée.' );
				}
			}
		}
		return $envoyees;
	}
}

/* ── 3. B/C — Intéressés silencieux (devis, maquette, « ça m'intéresse ») ─ */

if ( ! function_exists( 'ag_rc_est_silencieux' ) ) {
	/**
	 * Une piste chaude qui mérite une relance : statut « interesse », qui a un
	 * moyen de contact, qui n'a pas d'opposition, et qui n'a rien reçu depuis
	 * le délai. On NE relance PAS quelqu'un qui a déjà un contrat en cours
	 * (c'est la relance A qui s'en charge).
	 */
	function ag_rc_est_silencieux( $p ) {
		if ( 'interesse' !== (string) ( $p['status'] ?? '' ) ) { return 0; }
		if ( ! empty( $p['closer_stop'] ) ) { return 0; }
		$email = sanitize_email( (string) ( $p['email'] ?? '' ) );
		$tel   = (string) ( $p['phone'] ?? '' );
		if ( ! is_email( $email ) && '' === trim( $tel ) ) { return 0; } // aucun moyen de contact

		$etape = (int) ( $p['rc_step'] ?? 0 );
		if ( $etape >= 2 ) { return 0; } // deux relances déjà faites, on arrête

		// Depuis quand plus rien ? On prend le dernier signe de vie connu.
		$last = 0;
		foreach ( array( 'rc_last', 'date_reply', 'last_contact', 'date_contact' ) as $f ) {
			$v = (string) ( $p[ $f ] ?? '' );
			if ( '' === $v ) { continue; }
			$t = is_numeric( $v ) ? (int) $v : strtotime( str_replace( '/', '-', $v ) );
			if ( $t && $t > $last ) { $last = $t; }
		}
		if ( 0 === $last ) { $last = (int) ( $p['rc_last'] ?? 0 ); }
		$depuis = $last ? ( time() - $last ) / DAY_IN_SECONDS : 999;

		$seuil = ( 0 === $etape ) ? AG_RC_J1 : ( AG_RC_J2 - AG_RC_J1 );
		return ( $depuis >= $seuil ) ? ( $etape + 1 ) : 0;
	}
}

if ( ! function_exists( 'ag_rc_relance_interesses' ) ) {
	function ag_rc_relance_interesses( $reste ) {
		if ( $reste <= 0 ) { return 0; }
		$list = (array) get_option( 'ag_prospects', array() );
		$envoyees = 0;

		foreach ( $list as $i => $p ) {
			if ( $envoyees >= $reste ) { break; }
			$etape = ag_rc_est_silencieux( $p );
			if ( ! $etape ) { continue; }

			$nom   = (string) ( $p['name'] ?? '' );
			$email = sanitize_email( (string) ( $p['email'] ?? '' ) );
			$tel   = (string) ( $p['phone'] ?? '' );
			$lien_devis = home_url( '/refais-mon-site' );
			$fait = false;

			if ( is_email( $email ) ) {
				$sujet = 2 === $etape ? 'On reste à votre disposition' : 'Votre projet de site — on en est où ?';
				$corps = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
					. 'Bonjour' . ( $nom ? ' ' . esc_html( $nom ) : '' ) . ',</p>'
					. '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
					. 'Vous vous étiez intéressé à un site pour votre activité. On voulait juste savoir si '
					. 'le moment est bon pour en reparler — sans engagement, à votre rythme.</p>'
					. ( function_exists( 'ag_email_button' ) ? ag_email_button( 'Voir une maquette de mon site', $lien_devis )
						: '<p><a href="' . esc_url( $lien_devis ) . '">' . esc_html( $lien_devis ) . '</a></p>' )
					. '<p style="font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#b0b0bc;">'
					. 'Si ce n\'est plus d\'actualité, répondez « stop » et on n\'en reparle plus.</p>';
				$html = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( $sujet, $corps ) : $corps;
				$fait = wp_mail( $email, $sujet, $html, array( 'Content-Type: text/html; charset=UTF-8' ) ) || $fait;
			}

			if ( ag_rc_sms_ok( $tel ) ) {
				$sms = 'Bonjour' . ( $nom ? ' ' . $nom : '' ) . ', Alliance Groupe : toujours partant pour votre projet de site ? '
					. 'Une maquette gratuite ici : ' . $lien_devis . ' — STOP pour arrêter.';
				$fait = ag_sms_send( $tel, $sms ) || $fait;
			}

			if ( $fait ) {
				$list[ $i ]['rc_step'] = $etape;
				$list[ $i ]['rc_last'] = time();
				$envoyees++;
			}
		}

		if ( $envoyees ) { update_option( 'ag_prospects', $list, false ); }
		return $envoyees;
	}
}

/* ── 4. Le tour ──────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_rc_tour' ) ) {
	function ag_rc_tour() {
		$cap = ag_rc_cap();
		$c   = ag_rc_relance_contrats( $cap );          // priorité : le plus proche du chiffre
		$i   = ag_rc_relance_interesses( $cap - $c );
		$bilan = array( 'contrats' => $c, 'interesses' => $i, 'total' => $c + $i );
		if ( $bilan['total'] ) {
			update_option( 'ag_rc_derniere', array( 'ts' => time(), 'bilan' => $bilan ), false );
			if ( function_exists( 'ag_activity_log' ) ) {
				ag_activity_log( '🔥 Relance chaud : ' . $c . ' contrat(s) + ' . $i . ' intéressé(s).' );
			}
		}
		return $bilan;
	}
}

add_action( 'init', function () {
	if ( ag_rc_on() && ! wp_next_scheduled( 'ag_rc_cron' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 10:00' ), 'daily', 'ag_rc_cron' );
	}
	if ( ! ag_rc_on() && wp_next_scheduled( 'ag_rc_cron' ) ) {
		wp_clear_scheduled_hook( 'ag_rc_cron' );
	}
} );
add_action( 'ag_rc_cron', function () { if ( ag_rc_on() ) { ag_rc_tour(); } } );

/* ── 5. L'écran ──────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'Relance du chaud', '🔥 Relance du chaud',
		'manage_options', 'ag-relance-chaud', 'ag_rc_ecran'
	);
}, 34 );

if ( ! function_exists( 'ag_rc_ecran' ) ) {
	function ag_rc_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$msg = '';

		if ( isset( $_POST['ag_rc_save'] ) && check_admin_referer( 'ag_rc' ) ) {
			update_option( 'ag_rc_on', isset( $_POST['on'] ) ? 1 : 0, false );
			update_option( 'ag_rc_cap', max( 1, absint( $_POST['cap'] ?? 40 ) ), false );
			$msg = 'Réglages enregistrés.';
		}
		$tour = null;
		if ( isset( $_POST['ag_rc_now'] ) && check_admin_referer( 'ag_rc' ) ) {
			$tour = ag_rc_tour();
			$msg  = $tour['contrats'] . ' contrat(s) relancé(s) · ' . $tour['interesses'] . ' intéressé(s) relancé(s).';
		}

		// Compte de ce qui attend une relance, là, maintenant.
		$c_att = 0;
		if ( function_exists( 'ag_sign_all' ) ) {
			foreach ( ag_sign_all() as $d ) {
				if ( in_array( (string) ( $d['statut'] ?? '' ), array( 'signe', 'contresigne' ), true ) ) { continue; }
				if ( ! is_email( (string) ( $d['client_email'] ?? '' ) ) ) { continue; }
				if ( (int) ( $d['rc_step'] ?? 0 ) < 2 ) { $c_att++; }
			}
		}
		$i_att = 0;
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( ag_rc_est_silencieux( $p ) ) { $i_att++; }
		}
		$last = get_option( 'ag_rc_derniere', array() );
		$sms  = function_exists( 'ag_sms_gateway_ready' ) && ag_sms_gateway_ready();
		?>
		<div class="wrap">
			<h1>🔥 Relance du chaud</h1>
			<p class="description" style="max-width:58em">
				Rattrape ce qui allait signer : un contrat envoyé et pas encore signé, un « intéressé »
				retombé silencieux, un devis sans suite. Par e-mail, et par SMS quand le numéro est un mobile.
				Deux relances maximum par piste, à J+<?php echo (int) AG_RC_J1; ?> puis J+<?php echo (int) AG_RC_J2; ?>, puis on n'insiste plus.
			</p>

			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>
			<?php if ( ! $sms ) : ?>
				<div class="notice notice-warning"><p>La passerelle SMS n'est pas prête : les relances partiront
					<strong>par e-mail seulement</strong> tant que le téléphone passerelle n'est pas en ligne.</p></div>
			<?php endif; ?>

			<table class="widefat" style="max-width:44em;margin:16px 0"><tbody>
				<tr><td>Contrats non signés en attente de relance</td><td><strong style="color:#b26b00"><?php echo (int) $c_att; ?></strong></td></tr>
				<tr><td>Intéressés silencieux à relancer</td><td><strong style="color:#b26b00"><?php echo (int) $i_att; ?></strong></td></tr>
				<?php if ( ! empty( $last['ts'] ) ) : ?>
				<tr><td>Dernier tour</td><td><?php echo esc_html( date_i18n( 'd/m/Y H:i', (int) $last['ts'] ) ); ?></td></tr>
				<?php endif; ?>
			</tbody></table>

			<form method="post">
				<?php wp_nonce_field( 'ag_rc' ); ?>
				<table class="form-table">
					<tr><th scope="row">Relancer le chaud chaque jour</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( ag_rc_on() ); ?>> Oui — tous les jours à 10h</label>
					</td></tr>
					<tr><th scope="row">Maximum par jour</th><td>
						<input type="number" name="cap" min="1" style="width:7em" value="<?php echo esc_attr( ag_rc_cap() ); ?>">
						<p class="description">Priorité aux contrats non signés : ce sont les ventes déjà presque conclues.</p>
					</td></tr>
				</table>
				<p>
					<button class="button button-primary" name="ag_rc_save" value="1">Enregistrer</button>
					<button class="button" name="ag_rc_now" value="1">Relancer maintenant</button>
				</p>
			</form>

			<h2>Ce que le relanceur respecte</h2>
			<ul style="max-width:58em;list-style:disc;padding-left:22px">
				<li><strong>Opt-out&nbsp;:</strong> un numéro qui a répondu STOP ne reçoit plus jamais de SMS. Un
					prospect « ne plus contacter » est exclu.</li>
				<li><strong>Pas de harcèlement&nbsp;:</strong> deux relances par piste, puis silence définitif.</li>
				<li><strong>SMS seulement sur mobile&nbsp;:</strong> un fixe ne reçoit rien par SMS.</li>
				<li><strong>Que du chaud&nbsp;:</strong> ces gens ont déjà dit oui, demandé un devis, ou reçu un
					contrat. Aucun froid n'est relancé ici.</li>
			</ul>
		</div>
		<?php
	}
}
