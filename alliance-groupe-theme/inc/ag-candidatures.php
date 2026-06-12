<?php
/**
 * AG Candidatures Ambassadeurs — « agent » de gestion des candidatures.
 *
 * - Formulaire public [ag_candidature] (prénom, email, tél, ville, motivation).
 * - À chaque candidature : RÉPONSE AUTOMATIQUE au candidat (email brandé) +
 *   ALERTE SMS à l'admin (API Free) + push Telegram. Toi tu reçois le résumé
 *   de chaque inscription/candidature directement par SMS.
 * - Écran admin « 🎯 Candidatures » : liste (tri + cases + suppression de masse),
 *   Accepter / Refuser (envoie un email type au candidat), Relancer, contact
 *   1 clic (SMS/WhatsApp/Email vers le candidat), notes, statut.
 *
 * NB : l'API SMS Free n'envoie que vers TA ligne (pas vers le candidat) → la
 * réponse auto au candidat passe par EMAIL ; pour lui écrire en SMS/WhatsApp,
 * un bouton ouvre l'app pré-remplie (manuel, 1 clic).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------- Stockage */
if ( ! function_exists( 'ag_cand_get' ) ) {
	function ag_cand_get() { $d = get_option( 'ag_candidatures', array() ); return is_array( $d ) ? $d : array(); }
}
if ( ! function_exists( 'ag_cand_save' ) ) {
	function ag_cand_save( $list ) { update_option( 'ag_candidatures', array_values( $list ), false ); }
}
if ( ! function_exists( 'ag_cand_statuses' ) ) {
	function ag_cand_statuses() {
		return array( 'nouveau' => '🆕 Nouveau', 'relance' => '🔁 Relancé', 'accepte' => '✅ Accepté', 'refuse' => '⛔ Refusé' );
	}
}

/* ---------------------------------------------------------------- Emails */
if ( ! function_exists( 'ag_cand_inscription_link' ) ) {
	function ag_cand_inscription_link() { return function_exists( 'home_url' ) ? home_url( '/ambassadeurs' ) : '/ambassadeurs'; }
}
if ( ! function_exists( 'ag_cand_mail' ) ) {
	function ag_cand_mail( $to, $subject, $heading, $inner ) {
		if ( '' === trim( (string) $to ) || ! function_exists( 'ag_email_wrap' ) ) return;
		wp_mail( $to, $subject, ag_email_wrap( $heading, $inner ), array( 'Content-Type: text/html; charset=UTF-8' ) );
	}
}
if ( ! function_exists( 'ag_cand_email_recu' ) ) {
	function ag_cand_email_recu( $c ) {
		$p = $c['prenom'] ?: '';
		$inner  = '<p>Bonjour ' . esc_html( $p ) . ',</p>';
		$inner .= '<p>Merci pour ta candidature pour devenir <strong>ambassadeur Alliance Groupe</strong> ! Elle est bien reçue. 🎉</p>';
		$inner .= '<p>Le principe : tu présentes nos sites web, et tu touches <strong>10 % de commission à vie</strong> sur chaque vente — sans plafond, payé sur PayPal, 100 % en ligne et gratuit.</p>';
		$inner .= '<p>On revient vers toi très vite. En attendant, tu peux déjà découvrir le programme et le simulateur de gains :</p>';
		$inner .= ag_email_button( 'Découvrir le programme', ag_cand_inscription_link() );
		$inner .= '<p style="color:#9a9aa5;font-size:12px;">Tu reçois cet email car tu as candidaté sur notre site. Si ce n\'est pas toi, ignore ce message.</p>';
		ag_cand_mail( $c['email'] ?? '', 'Ta candidature ambassadeur est bien reçue 🎉', 'Bienvenue, ' . $p . ' !', $inner );
	}
}
if ( ! function_exists( 'ag_cand_email_accept' ) ) {
	function ag_cand_email_accept( $c ) {
		$p = $c['prenom'] ?: '';
		$inner  = '<p>Bonjour ' . esc_html( $p ) . ',</p>';
		$inner .= '<p><strong>Bonne nouvelle : ta candidature est acceptée !</strong> Bienvenue dans l\'équipe des ambassadeurs Alliance Groupe. 🙌</p>';
		$inner .= '<p>Dernière étape pour activer ton compte et commencer à gagner : finalise ton inscription (2 minutes) ici :</p>';
		$inner .= ag_email_button( 'Finaliser mon inscription', ag_cand_inscription_link() );
		$inner .= '<p>Une question ? Réponds simplement à cet email.</p>';
		ag_cand_mail( $c['email'] ?? '', 'Ta candidature ambassadeur est acceptée ✅', 'Félicitations ' . $p . ' !', $inner );
	}
}
if ( ! function_exists( 'ag_cand_email_refus' ) ) {
	function ag_cand_email_refus( $c ) {
		$p = $c['prenom'] ?: '';
		$inner  = '<p>Bonjour ' . esc_html( $p ) . ',</p>';
		$inner .= '<p>Merci pour ton intérêt pour le programme ambassadeur Alliance Groupe. Pour le moment, nous ne donnons pas suite à ta candidature.</p>';
		$inner .= '<p>Cela ne remet rien en cause : n\'hésite pas à retenter plus tard. On te souhaite le meilleur.</p>';
		ag_cand_mail( $c['email'] ?? '', 'Ta candidature ambassadeur', 'Merci ' . $p, $inner );
	}
}

/* ---------------------------------------------------------------- Formulaire (shortcode) */
add_shortcode( 'ag_candidature', function () {
	$ok = isset( $_GET['cand'] ) && 'ok' === $_GET['cand'];
	ob_start();
	if ( $ok ) {
		echo '<div style="max-width:560px;margin:24px auto;padding:22px;border-radius:14px;background:#0f1f14;border:1px solid #1e7e34;color:#d6f5df;text-align:center;font-family:Arial,sans-serif;">✅ <strong>Candidature envoyée !</strong> On t\'a envoyé un email de confirmation et on revient vers toi très vite.</div>';
	}
	$action = esc_url( admin_url( 'admin-post.php' ) );
	?>
	<form method="post" action="<?php echo $action; // phpcs:ignore ?>" style="max-width:560px;margin:24px auto;padding:24px;border-radius:16px;background:#14141c;border:1px solid rgba(212,180,92,.3);font-family:Arial,sans-serif;color:#e8e8ee;">
		<input type="hidden" name="action" value="ag_candidature_submit">
		<?php wp_nonce_field( 'ag_candidature', '_agc' ); ?>
		<h3 style="font-family:Georgia,serif;color:#D4B45C;margin:0 0 6px;">Deviens ambassadeur 💸</h3>
		<p style="color:#9a9aa5;font-size:14px;margin:0 0 16px;">10 % de commission à vie sur chaque site vendu. Gratuit, 100 % en ligne. Candidate en 30 secondes :</p>
		<input type="text" name="prenom" placeholder="Prénom *" required style="width:100%;padding:11px;margin:6px 0;border-radius:8px;border:1px solid #333;background:#0d0d12;color:#fff;">
		<input type="email" name="email" placeholder="Email *" required style="width:100%;padding:11px;margin:6px 0;border-radius:8px;border:1px solid #333;background:#0d0d12;color:#fff;">
		<input type="tel" name="phone" placeholder="Téléphone" style="width:100%;padding:11px;margin:6px 0;border-radius:8px;border:1px solid #333;background:#0d0d12;color:#fff;">
		<input type="text" name="ville" placeholder="Ville" style="width:100%;padding:11px;margin:6px 0;border-radius:8px;border:1px solid #333;background:#0d0d12;color:#fff;">
		<textarea name="motivation" rows="3" placeholder="Pourquoi toi ? (optionnel)" style="width:100%;padding:11px;margin:6px 0;border-radius:8px;border:1px solid #333;background:#0d0d12;color:#fff;"></textarea>
		<button type="submit" style="width:100%;padding:14px;margin-top:10px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:bold;font-size:15px;cursor:pointer;">Envoyer ma candidature →</button>
	</form>
	<?php
	return ob_get_clean();
} );

/* ---------------------------------------------------------------- Réception candidature */
add_action( 'admin_post_nopriv_ag_candidature_submit', 'ag_candidature_submit' );
add_action( 'admin_post_ag_candidature_submit', 'ag_candidature_submit' );
if ( ! function_exists( 'ag_candidature_submit' ) ) {
	function ag_candidature_submit() {
		if ( ! isset( $_POST['_agc'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agc'] ) ), 'ag_candidature' ) ) {
			wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) ); exit;
		}
		$c = array(
			'id'         => uniqid( 'c_' ),
			'prenom'     => sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) ),
			'email'      => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'phone'      => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'ville'      => sanitize_text_field( wp_unslash( $_POST['ville'] ?? '' ) ),
			'motivation' => sanitize_textarea_field( wp_unslash( $_POST['motivation'] ?? '' ) ),
			'status'     => 'nouveau',
			'date'       => current_time( 'd/m/Y H:i' ),
			'last'       => '',
			'notes'      => '',
			'ts'         => time(),
		);
		if ( '' === $c['prenom'] || '' === $c['email'] ) {
			wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) ); exit;
		}
		// AUTO-ACCEPTATION (par défaut ON, pour te décharger) : le candidat est
		// accepté tout de suite et reçoit le lien d'inscription. Sinon, simple
		// accusé de réception et tu valides à la main.
		$auto = '1' === get_option( 'ag_cand_autoaccept', '1' );
		if ( $auto ) { $c['status'] = 'accepte'; $c['last'] = current_time( 'd/m/Y' ); }

		$list   = ag_cand_get();
		$list[] = $c;
		ag_cand_save( $list );

		// 1) Réponse automatique au candidat (email).
		if ( $auto ) { ag_cand_email_accept( $c ); } else { ag_cand_email_recu( $c ); }
		// 2) Alerte SMS à l'admin (ligne pro) + Telegram.
		$tag    = $auto ? '✅ Candidature auto-acceptée' : '🆕 Candidature ambassadeur';
		$resume = $tag . ' : ' . $c['prenom'] . ( $c['ville'] ? ' (' . $c['ville'] . ')' : '' ) . ( $c['phone'] ? ' — ' . $c['phone'] : '' ) . ' — ' . $c['email'];
		if ( function_exists( 'ag_sms' ) ) ag_sms( $resume );
		if ( function_exists( 'ag_push' ) ) ag_push( $tag, $c['prenom'] . ( $c['ville'] ? ' · ' . $c['ville'] : '' ) . "\n" . ( $c['phone'] ? '📞 ' . $c['phone'] . "\n" : '' ) . '✉️ ' . $c['email'] . ( $c['motivation'] ? "\n« " . $c['motivation'] . ' »' : '' ) );

		$back = wp_get_referer() ?: home_url( '/' );
		wp_safe_redirect( add_query_arg( 'cand', 'ok', remove_query_arg( 'cand', $back ) ) . '#candidature' ); exit;
	}
}

/* ---------------------------------------------------------------- Actions admin (AJAX) */
add_action( 'wp_ajax_ag_cand_action', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_cand' ) ) wp_send_json_error();
	$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$act = sanitize_text_field( wp_unslash( $_POST['act'] ?? '' ) );
	$val = sanitize_textarea_field( wp_unslash( $_POST['val'] ?? '' ) );
	$list = ag_cand_get();
	foreach ( $list as $k => $c ) {
		if ( ( $c['id'] ?? '' ) !== $id ) continue;
		$today = current_time( 'd/m/Y' );
		switch ( $act ) {
			case 'accept':
				$list[ $k ]['status'] = 'accepte'; $list[ $k ]['last'] = $today;
				ag_cand_email_accept( $c );
				break;
			case 'refuse':
				$list[ $k ]['status'] = 'refuse'; $list[ $k ]['last'] = $today;
				ag_cand_email_refus( $c );
				break;
			case 'relance':
				$list[ $k ]['status'] = 'relance'; $list[ $k ]['last'] = $today;
				break;
			case 'status':
				if ( array_key_exists( $val, ag_cand_statuses() ) ) $list[ $k ]['status'] = $val;
				break;
			case 'note':
				$list[ $k ]['notes'] = $val;
				break;
			default:
				wp_send_json_error();
		}
		ag_cand_save( $list );
		wp_send_json_success( array( 'status' => $list[ $k ]['status'], 'date' => $today ) );
	}
	wp_send_json_error();
} );
add_action( 'wp_ajax_ag_cand_delete_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_cand' ) ) wp_send_json_error();
	$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$ids = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $ids ) ) );
	if ( empty( $ids ) ) wp_send_json_error();
	$list   = ag_cand_get();
	$before = count( $list );
	$list   = array_filter( $list, function ( $c ) use ( $ids ) { return ! in_array( $c['id'] ?? '', $ids, true ); } );
	ag_cand_save( $list );
	wp_send_json_success( array( 'removed' => $before - count( $list ) ) );
} );

/* ---------------------------------------------------------------- Réglages auto */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_cand_save_settings'] ) && check_admin_referer( 'ag_cand_settings' ) ) {
		update_option( 'ag_cand_autoaccept', isset( $_POST['ag_cand_autoaccept'] ) ? '1' : '0' );
		update_option( 'ag_cand_digest', isset( $_POST['ag_cand_digest'] ) ? '1' : '0' );
		update_option( 'ag_cand_autorelance', isset( $_POST['ag_cand_autorelance'] ) ? '1' : '0' );
	}
} );

/* ---------------------------------------------------------------- Crons (déchargement) */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_cand_daily_cron' ) ) {
		wp_schedule_event( time() + 300, 'daily', 'ag_cand_daily_cron' );
	}
} );
add_action( 'ag_cand_daily_cron', 'ag_cand_daily_tasks' );
if ( ! function_exists( 'ag_cand_daily_tasks' ) ) {
	function ag_cand_daily_tasks() {
		$list   = ag_cand_get();
		$now    = time();
		$changed = false;

		// 1) Relance automatique des candidats restés « nouveau » > 3 jours (1 seule fois).
		if ( '1' === get_option( 'ag_cand_autorelance', '1' ) ) {
			foreach ( $list as $k => $c ) {
				if ( 'nouveau' !== ( $c['status'] ?? '' ) ) continue;
				if ( ! empty( $c['auto_relanced'] ) ) continue;
				if ( ( $now - (int) ( $c['ts'] ?? $now ) ) < 3 * DAY_IN_SECONDS ) continue;
				$p = $c['prenom'] ?: '';
				$inner  = '<p>Bonjour ' . esc_html( $p ) . ',</p><p>On n\'a pas encore eu de tes nouvelles ! Ta place d\'ambassadeur Alliance Groupe t\'attend toujours : <strong>10 % de commission à vie</strong>, gratuit, 100 % en ligne.</p>';
				$inner .= ag_email_button( 'Rejoindre maintenant', ag_cand_inscription_link() );
				ag_cand_mail( $c['email'] ?? '', 'Ta place d\'ambassadeur t\'attend 👋', 'Toujours partant, ' . $p . ' ?', $inner );
				$list[ $k ]['status']        = 'relance';
				$list[ $k ]['last']          = current_time( 'd/m/Y' );
				$list[ $k ]['auto_relanced'] = 1;
				$changed = true;
			}
		}
		if ( $changed ) ag_cand_save( $list );

		// 2) Digest SMS quotidien des candidatures des dernières 24 h.
		if ( '1' === get_option( 'ag_cand_digest', '1' ) && function_exists( 'ag_sms' ) ) {
			$since = $now - DAY_IN_SECONDS;
			$today = array_filter( $list, function ( $c ) use ( $since ) { return (int) ( $c['ts'] ?? 0 ) >= $since; } );
			$nb    = count( $today );
			if ( $nb > 0 ) {
				$acc = count( array_filter( $today, function ( $c ) { return 'accepte' === ( $c['status'] ?? '' ); } ) );
				$noms = implode( ', ', array_slice( array_map( function ( $c ) { return $c['prenom'] ?: '?'; }, $today ), 0, 8 ) );
				ag_sms( '🎯 Candidatures (24h) : ' . $nb . ' reçue(s), ' . $acc . ' acceptée(s). ' . $noms );
			}
		}
	}
}

/* ---------------------------------------------------------------- Page admin */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-ambassadeurs', 'Candidatures', '🎯 Candidatures', 'manage_options', 'ag-candidatures', 'ag_cand_render' );
}, 20 );

if ( ! function_exists( 'ag_cand_render' ) ) {
	function ag_cand_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$list   = ag_cand_get();
		$sort   = isset( $_GET['csort'] ) ? sanitize_text_field( wp_unslash( $_GET['csort'] ) ) : 'recent';
		$fstat  = isset( $_GET['cstat'] ) ? sanitize_text_field( wp_unslash( $_GET['cstat'] ) ) : '';
		if ( '' !== $fstat ) $list = array_filter( $list, function ( $c ) use ( $fstat ) { return ( $c['status'] ?? 'nouveau' ) === $fstat; } );
		$list = array_values( $list );
		usort( $list, function ( $a, $b ) use ( $sort ) {
			if ( 'nom' === $sort )    return strcasecmp( $a['prenom'] ?? '', $b['prenom'] ?? '' );
			if ( 'statut' === $sort ) return strcmp( $a['status'] ?? '', $b['status'] ?? '' );
			return ( $b['ts'] ?? 0 ) <=> ( $a['ts'] ?? 0 );
		} );
		$labels = ag_cand_statuses();
		$nonce  = wp_create_nonce( 'ag_cand' );
		$all    = ag_cand_get();
		$counts = array();
		foreach ( $all as $c ) { $s = $c['status'] ?? 'nouveau'; $counts[ $s ] = ( $counts[ $s ] ?? 0 ) + 1; }
		echo '<div class="wrap"><h1>🎯 Candidatures ambassadeurs (' . count( $all ) . ')</h1>';
		echo '<p style="max-width:840px;color:#50575e;">Chaque candidature reçue déclenche un <strong>email automatique au candidat</strong> + un <strong>SMS d\'alerte sur ta ligne</strong>. Formulaire public : shortcode <code>[ag_candidature]</code> (page « Devenir ambassadeur » créée automatiquement).</p>';
		// Réglages d'automatisation (déchargement).
		$auto = '1' === get_option( 'ag_cand_autoaccept', '1' );
		$dig  = '1' === get_option( 'ag_cand_digest', '1' );
		$rel  = '1' === get_option( 'ag_cand_autorelance', '1' );
		echo '<div style="max-width:840px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #1e7e34;border-radius:8px;padding:12px 16px;margin:10px 0;">';
		echo '<form method="post" style="margin:0;">';
		wp_nonce_field( 'ag_cand_settings' );
		echo '<strong>🤖 Pilote automatique (te décharge) :</strong><br>';
		echo '<label style="display:block;margin:6px 0;"><input type="checkbox" name="ag_cand_autoaccept" ' . checked( $auto, true, false ) . '> <strong>Auto-accepter</strong> : chaque candidat est accepté tout de suite et reçoit son lien d\'inscription (tu es juste informé par SMS).</label>';
		echo '<label style="display:block;margin:6px 0;"><input type="checkbox" name="ag_cand_autorelance" ' . checked( $rel, true, false ) . '> <strong>Relance auto</strong> : email de relance aux candidats restés sans suite après 3 jours.</label>';
		echo '<label style="display:block;margin:6px 0;"><input type="checkbox" name="ag_cand_digest" ' . checked( $dig, true, false ) . '> <strong>Digest SMS quotidien</strong> : un résumé par jour (nombre de candidatures + acceptées).</label>';
		echo '<button name="ag_cand_save_settings" value="1" class="button button-primary button-small">Enregistrer</button>';
		echo '</form></div>';
		// Filtres statut.
		$base = admin_url( 'admin.php?page=ag-candidatures' );
		echo '<p>';
		echo '<a class="button button-small ' . ( '' === $fstat ? 'button-primary' : '' ) . '" href="' . esc_url( $base ) . '">Tous (' . count( $all ) . ')</a> ';
		foreach ( $labels as $k => $lab ) {
			echo '<a class="button button-small ' . ( $fstat === $k ? 'button-primary' : '' ) . '" href="' . esc_url( add_query_arg( 'cstat', $k, $base ) ) . '">' . esc_html( $lab ) . ' (' . (int) ( $counts[ $k ] ?? 0 ) . ')</a> ';
		}
		echo '</p>';
		if ( empty( $list ) ) { echo '<p><em>Aucune candidature' . ( $fstat ? ' avec ce statut' : '' ) . '.</em></p></div>'; return; }
		// Barre tri + suppression masse.
		echo '<form method="get" style="display:inline-block;margin:0 8px 6px 0;"><input type="hidden" name="page" value="ag-candidatures"><input type="hidden" name="cstat" value="' . esc_attr( $fstat ) . '"><label>Trier : <select name="csort" onchange="this.form.submit()">';
		foreach ( array( 'recent' => '🕒 Récents', 'nom' => 'A→Z (prénom)', 'statut' => 'Statut' ) as $sk => $sl ) echo '<option value="' . esc_attr( $sk ) . '" ' . selected( $sort, $sk, false ) . '>' . esc_html( $sl ) . '</option>';
		echo '</select></label></form>';
		echo '<div style="margin:6px 0 10px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;"><label><input type="checkbox" id="cand-all"> Tout sélectionner</label><button type="button" id="cand-del" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="cand-cnt">0</span>)</button></div>';
		echo '<table class="widefat striped"><thead><tr><th style="width:28px;"><input type="checkbox" id="cand-all2"></th><th>Date</th><th>Candidat</th><th>Contact</th><th>Motivation</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $list as $c ) {
			$id    = $c['id'] ?? '';
			$ph    = $c['phone'] ?? '';
			$em    = $c['email'] ?? '';
			$msg   = 'Bonjour ' . ( $c['prenom'] ?: '' ) . ', merci pour ta candidature ambassadeur Alliance Groupe ! On peut en discuter quand tu veux. ';
			$digits = function_exists( 'ag_wa_number' ) ? ag_wa_number( $ph, '' ) : preg_replace( '/[^0-9]/', '', $ph );
			$wa    = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
			$sms   = $ph ? 'sms:' . preg_replace( '/[^0-9+]/', '', $ph ) . '?body=' . rawurlencode( $msg ) : '';
			echo '<tr>';
			echo '<td style="text-align:center;"><input type="checkbox" class="cand-chk" value="' . esc_attr( $id ) . '"></td>';
			echo '<td style="font-size:.85em;">' . esc_html( $c['date'] ?? '' ) . ( ! empty( $c['last'] ) ? '<br><small style="color:#888;">maj ' . esc_html( $c['last'] ) . '</small>' : '' ) . '</td>';
			echo '<td><strong>' . esc_html( $c['prenom'] ?? '' ) . '</strong>' . ( ! empty( $c['ville'] ) ? '<br><small>' . esc_html( $c['ville'] ) . '</small>' : '' ) . '</td>';
			echo '<td style="font-size:.85em;">' . ( $em ? '<a href="mailto:' . esc_attr( $em ) . '">' . esc_html( $em ) . '</a>' : '' ) . ( $ph ? '<br><a href="tel:' . esc_attr( $ph ) . '">' . esc_html( $ph ) . '</a>' : '' ) . '</td>';
			echo '<td style="font-size:.85em;max-width:220px;color:#50575e;">' . esc_html( $c['motivation'] ?? '' ) . '</td>';
			echo '<td><select class="cand-status" data-id="' . esc_attr( $id ) . '">';
			foreach ( $labels as $k => $lab ) echo '<option value="' . esc_attr( $k ) . '" ' . selected( $c['status'] ?? 'nouveau', $k, false ) . '>' . esc_html( $lab ) . '</option>';
			echo '</select></td>';
			echo '<td>';
			echo '<button type="button" class="button button-small button-primary cand-act" data-id="' . esc_attr( $id ) . '" data-act="accept" title="Accepter + email d\'acceptation">✅ Accepter</button> ';
			echo '<button type="button" class="button button-small cand-act" data-id="' . esc_attr( $id ) . '" data-act="refuse" title="Refuser + email">⛔ Refuser</button> ';
			echo '<button type="button" class="button button-small cand-act" data-id="' . esc_attr( $id ) . '" data-act="relance">🔁 Relancer</button> ';
			if ( $wa ) echo '<a class="button button-small" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">WhatsApp</a> ';
			if ( $sms ) echo '<a class="button button-small" href="' . esc_attr( $sms ) . '">SMS</a> ';
			if ( $em ) echo '<a class="button button-small" href="mailto:' . esc_attr( $em ) . '">Email</a>';
			echo '<details style="margin-top:6px;"><summary class="button button-small">📝 Note</summary><textarea class="cand-note" data-id="' . esc_attr( $id ) . '" rows="2" style="width:260px;margin-top:4px;">' . esc_textarea( $c['notes'] ?? '' ) . '</textarea><br><button type="button" class="button button-small cand-note-save" data-id="' . esc_attr( $id ) . '">💾</button></details>';
			echo '</td></tr>';
		}
		echo '</tbody></table>';
		// JS.
		echo '<script>(function(){var n=' . wp_json_encode( $nonce ) . ';';
		echo 'function post(id,act,val,cb){var fd=new FormData();fd.append("action","ag_cand_action");fd.append("_n",n);fd.append("id",id);fd.append("act",act);if(val!=null)fd.append("val",val);fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){if(cb)cb(j);});}';
		echo 'document.querySelectorAll(".cand-act").forEach(function(b){b.addEventListener("click",function(){var act=b.getAttribute("data-act");if((act==="accept"||act==="refuse")&&!confirm("Confirmer : "+act+" ? Un email sera envoyé au candidat."))return;b.disabled=true;post(b.getAttribute("data-id"),act,null,function(j){b.disabled=false;if(j&&j.success){b.textContent="✓";}});});});';
		echo 'document.querySelectorAll(".cand-status").forEach(function(s){s.addEventListener("change",function(){post(s.getAttribute("data-id"),"status",s.value,null);});});';
		echo 'document.querySelectorAll(".cand-note-save").forEach(function(b){b.addEventListener("click",function(){var ta=document.querySelector(".cand-note[data-id=\'"+b.getAttribute("data-id")+"\']");post(b.getAttribute("data-id"),"note",ta?ta.value:"",function(){b.textContent="✓";});});});';
		// bulk delete.
		echo 'var all=document.getElementById("cand-all"),all2=document.getElementById("cand-all2"),del=document.getElementById("cand-del"),cnt=document.getElementById("cand-cnt");';
		echo 'function it(){return [].slice.call(document.querySelectorAll(".cand-chk"));}function rf(){var c=it().filter(function(x){return x.checked;}).length;if(cnt)cnt.textContent=c;if(del)del.disabled=(c===0);}';
		echo 'function sa(v){it().forEach(function(x){x.checked=v;});if(all)all.checked=v;if(all2)all2.checked=v;rf();}';
		echo 'if(all)all.addEventListener("change",function(){sa(all.checked);});if(all2)all2.addEventListener("change",function(){sa(all2.checked);});it().forEach(function(x){x.addEventListener("change",rf);});';
		echo 'if(del)del.addEventListener("click",function(){var ids=it().filter(function(x){return x.checked;}).map(function(x){return x.value;});if(!ids.length)return;if(!confirm("Supprimer "+ids.length+" candidature(s) ?"))return;var fd=new FormData();fd.append("action","ag_cand_delete_bulk");fd.append("_n",n);ids.forEach(function(i){fd.append("ids[]",i);});del.disabled=true;fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){location.reload();}else{del.disabled=false;}});});';
		echo '})();</script>';
		echo '</div>';
	}
}

/* -------------------------------------------------- Auto-création page candidature */
add_action( 'admin_init', function () {
	if ( get_option( 'ag_cand_page_v1' ) ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! get_page_by_path( 'candidature-ambassadeur' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Devenir ambassadeur',
			'post_name'    => 'candidature-ambassadeur',
			'post_content' => '<!-- wp:shortcode -->[ag_candidature]<!-- /wp:shortcode -->',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
	}
	update_option( 'ag_cand_page_v1', 1 );
} );
