<?php
/**
 * « Tester mon site » — entonnoir freemium d'audit.
 *
 * Flux :
 *  1. Formulaire (URL + email + case d'AUTORISATION d'audit non-intrusif).
 *  2. L'audit passif tourne (réutilise ag_audit_run()). Aperçu À L'ÉCRAN
 *     (score visible, détails des problèmes MASQUÉS) + email d'aperçu.
 *  3. Bloc commande : case d'ACCEPTATION EXPLICITE (« je commande le rapport
 *     complet à X €, commande ferme, j'accepte les CGV ») + (B2C) renonciation
 *     au délai de rétractation pour fourniture immédiate.
 *  4. À l'acceptation : consentement horodaté (date + IP + nom = signature
 *     électronique simple), n° de facture séquentiel, email avec la FACTURE
 *     + lien vers le RAPPORT COMPLET (débloqué). Lien de paiement inclus.
 *
 * Audit volontairement NON-INTRUSIF (lecture publique). On ne scanne/attaque
 * jamais le site cible : la case d'autorisation couvre un diagnostic passif.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------ Options */
if ( ! function_exists( 'ag_tester_opt' ) ) {
	function ag_tester_opt( $key ) {
		$def = array(
			'price'       => '49',
			'deep_price'  => '290',   // Diagnostic Expert 24h (scan Kali complet, sur mandat)
			'deep_pay_url'=> '',      // lien de paiement dédié (vide = renvoie /contact)
			'pay_url'     => '',
			'raison'      => 'Alliance Groupe',
			'siret'       => '[SIRET à compléter dans Réglages → Tester / Audit]',
			'adresse'     => 'Nantes, France',
			'tva'         => 'TVA non applicable, art. 293 B du CGI',
			'email'       => 'contact@alliancegroupe-inc.com',
			'popup_img'   => '',
			'phone'       => '',
		);
		$v = get_option( 'ag_tester_' . $key, '' );
		return ( '' === $v || false === $v ) ? ( $def[ $key ] ?? '' ) : $v;
	}
}

/* --------------------------------------------------- Réglages (Settings API) */
add_action( 'admin_menu', function () {
	add_options_page( 'Tester / Audit', 'Tester / Audit', 'manage_options', 'ag-tester', 'ag_tester_settings_page' );
} );
add_action( 'admin_init', function () {
	foreach ( array( 'price', 'deep_price', 'deep_pay_url', 'pay_url', 'raison', 'siret', 'adresse', 'tva', 'email', 'popup_img', 'phone', 'img_audit', 'img_secu', 'img_creation', 'img_maintenance', 'img_templates', 'img_menace', 'tg_sec', 'tg_crea' ) as $k ) {
		register_setting( 'ag_tester_group', 'ag_tester_' . $k );
	}
} );
if ( ! function_exists( 'ag_tester_settings_page' ) ) {
	function ag_tester_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$fields = array(
			'price'        => 'Prix du rapport complet (€ TTC)',
			'deep_price'   => '🔬 Prix Diagnostic Expert 24h (€ TTC) — 0 = « sur devis »',
			'deep_pay_url' => '🔬 Lien de paiement Diagnostic Expert 24h — vide = renvoie vers /contact',
			'pay_url' => 'Lien de paiement (Stripe/PayPal) — vide = renvoie vers /contact',
			'raison'  => 'Raison sociale (mentions facture)',
			'siret'   => 'SIRET',
			'adresse' => 'Adresse',
			'tva'     => 'Mention TVA',
			'email'   => 'Email de contact (facture)',
			'popup_img' => 'Image 3D du pop-up d\'accueil (URL complète) — vide = dégradé',
			'phone'   => 'Téléphone pro (pour « rappelez-moi » dans les messages)',
			'img_audit'       => '🖼️ URL image carte « Audit » (accueil)',
			'img_secu'        => '🖼️ URL image HÉRO côté gauche « Sécurité » (split éclair)',
			'img_creation'    => '🖼️ URL image carte « Création » (accueil) + côté droit du héro split',
			'img_maintenance' => '🖼️ URL image carte « Maintenance » (accueil)',
			'img_templates'   => '🖼️ URL image carte « Templates » (accueil)',
			'img_menace'      => '🖼️ URL image fond du mur « Un piratage ressemble à ça »',
			'tg_sec'          => '📲 Chat ID Telegram — prospects SÉCURITÉ (vide = canal interne)',
			'tg_crea'         => '📲 Chat ID Telegram — prospects CRÉATION/SEO (vide = canal interne)',
		);
		?>
		<div class="wrap">
			<h1>Tester / Audit — réglages facture &amp; prix</h1>
			<p>Ces informations apparaissent sur la facture envoyée par email. Renseigne ton <strong>SIRET</strong> et ton <strong>adresse</strong> pour des documents valides.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ag_tester_group' ); ?>
				<table class="form-table"><tbody>
				<?php foreach ( $fields as $k => $label ) : ?>
					<tr>
						<th scope="row"><label for="ag_tester_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><input type="text" class="regular-text" id="ag_tester_<?php echo esc_attr( $k ); ?>" name="ag_tester_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( ag_tester_opt( $k ) ); ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

/* ===================================================== JOURNAL DES SCANS ===
 * Chaque test gratuit (URL seule) = une ligne : URL + IP visiteur + score +
 * nb failles + date. Option ag_scan_log (autoload=no). Ecran admin dedie.
 * ========================================================================= */
if ( ! function_exists( 'ag_tester_log_scan' ) ) {
	function ag_tester_log_scan( $url, $audit ) {
		$nb = 0;
		foreach ( ( $audit['checks'] ?? array() ) as $c ) { if ( 'ok' !== ( $c['status'] ?? '' ) ) $nb++; }
		$log   = (array) get_option( 'ag_scan_log', array() );
		$log[] = array(
			'time'  => time(),
			'url'   => $url,
			'host'  => (string) wp_parse_url( $url, PHP_URL_HOST ),
			'ip'    => ag_tester_client_ip(),
			'score' => (int) ( $audit['score'] ?? 0 ),
			'nb'    => $nb,
			'crit'  => (int) ( $audit['critical'] ?? 0 ),
			'email' => (string) ( $audit['email'] ?? '' ),
		);
		update_option( 'ag_scan_log', array_slice( $log, -500 ), false );
	}
}
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-espace-audit', 'Sites scannes', '📋 Sites scannes', 'manage_options', 'ag-scan-log', 'ag_tester_scan_log_render' );
}, 30 );
if ( ! function_exists( 'ag_tester_scan_log_render' ) ) {
	function ag_tester_scan_log_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		if ( isset( $_POST['ag_scan_clear'] ) && check_admin_referer( 'ag_scan_log' ) ) {
			delete_option( 'ag_scan_log' );
			echo '<div class="notice notice-success"><p>Journal vide.</p></div>';
		}
		// Tri sélectionnable : récent (def.) / score / failles.
		$ssort = isset( $_GET['ssort'] ) ? sanitize_text_field( wp_unslash( $_GET['ssort'] ) ) : 'recent';
		$log   = (array) get_option( 'ag_scan_log', array() );
		if ( 'score' === $ssort )      usort( $log, function ( $a, $b ) { return (int) ( $b['score'] ?? 0 ) <=> (int) ( $a['score'] ?? 0 ); } );
		elseif ( 'failles' === $ssort ) usort( $log, function ( $a, $b ) { return (int) ( $b['nb'] ?? 0 ) <=> (int) ( $a['nb'] ?? 0 ); } );
		else $log = array_reverse( $log );
		echo '<div class="wrap"><h1>📋 Sites scannes (test gratuit)</h1>';
		echo '<p>Chaque visiteur ayant lance un test gratuit (URL seule). IP conservee pour le suivi.</p>';
		if ( empty( $log ) ) { echo '<p><em>Aucun scan pour l\'instant.</em></p></div>'; return; }
		$snonce = wp_create_nonce( 'ag_scan_log_bulk' );
		// Barre : tri + sélection/suppression en masse.
		echo '<form method="get" style="display:inline-block;margin:0 8px 8px 0;"><input type="hidden" name="page" value="ag-scan-log"><label>Trier : <select name="ssort" onchange="this.form.submit()">';
		foreach ( array( 'recent' => '🕒 Plus récents', 'score' => 'Score', 'failles' => 'Failles' ) as $sk => $sl ) echo '<option value="' . esc_attr( $sk ) . '" ' . selected( $ssort, $sk, false ) . '>' . esc_html( $sl ) . '</option>';
		echo '</select></label></form>';
		echo '<div style="margin:6px 0 10px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;"><label><input type="checkbox" id="sl-all"> Tout sélectionner</label><button type="button" id="sl-del" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="sl-cnt">0</span>)</button></div>';
		echo '<table class="widefat striped"><thead><tr><th style="width:28px;"><input type="checkbox" id="sl-all2"></th><th>Date</th><th>Site</th><th>IP</th><th>Score</th><th>Failles</th><th>Email</th></tr></thead><tbody>';
		foreach ( $log as $r ) {
			$col = ( (int) $r['score'] >= 80 ) ? '#093' : ( ( (int) $r['score'] >= 50 ) ? '#b58100' : '#c00' );
			$sig = sha1( ( $r['time'] ?? '' ) . '|' . ( $r['url'] ?? '' ) . '|' . ( $r['ip'] ?? '' ) );
			echo '<tr><td style="text-align:center;"><input type="checkbox" class="sl-chk" value="' . esc_attr( $sig ) . '"></td>';
			echo '<td>' . esc_html( wp_date( 'd/m H:i', (int) $r['time'] ) ) . '</td>';
			echo '<td><a href="' . esc_url( $r['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $r['host'] ) . '</a></td>';
			echo '<td><code>' . esc_html( $r['ip'] ) . '</code></td>';
			echo '<td style="color:' . esc_attr( $col ) . ';font-weight:700">' . (int) $r['score'] . '/100</td>';
			echo '<td>' . (int) $r['nb'] . ( ! empty( $r['crit'] ) ? ' <span style="color:#c00">⚠ ' . (int) $r['crit'] . ' crit.</span>' : '' ) . '</td>';
			echo '<td>' . esc_html( $r['email'] ?: '—' ) . '</td></tr>';
		}
		echo '</tbody></table>';
		echo '<form method="post" style="margin-top:16px" onsubmit="return confirm(\'Vider tout le journal ?\');">';
		wp_nonce_field( 'ag_scan_log' );
		echo '<button name="ag_scan_clear" value="1" class="button">🗑️ Vider tout le journal</button></form>';
		echo '<script>(function(){var n=' . wp_json_encode( $snonce ) . ';';
		echo 'var all=document.getElementById("sl-all"),all2=document.getElementById("sl-all2"),del=document.getElementById("sl-del"),cnt=document.getElementById("sl-cnt");';
		echo 'function it(){return Array.prototype.slice.call(document.querySelectorAll(".sl-chk"));}';
		echo 'function rf(){var c=it().filter(function(x){return x.checked;}).length;if(cnt)cnt.textContent=c;if(del)del.disabled=(c===0);}';
		echo 'function sa(v){it().forEach(function(x){x.checked=v;});if(all)all.checked=v;if(all2)all2.checked=v;rf();}';
		echo 'if(all)all.addEventListener("change",function(){sa(all.checked);});if(all2)all2.addEventListener("change",function(){sa(all2.checked);});';
		echo 'it().forEach(function(x){x.addEventListener("change",rf);});';
		echo 'if(del)del.addEventListener("click",function(){var ids=it().filter(function(x){return x.checked;}).map(function(x){return x.value;});if(!ids.length)return;if(!confirm("Supprimer "+ids.length+" ligne(s) ?"))return;var fd=new FormData();fd.append("action","ag_scan_log_delete_bulk");fd.append("_n",n);ids.forEach(function(i){fd.append("ids[]",i);});del.disabled=true;del.textContent="…";fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){location.reload();}else{del.disabled=false;alert("Erreur");}}).catch(function(){del.disabled=false;});});';
		echo '})();</script>';
		echo '</div>';
	}
}
/* Suppression en masse du journal des scans (signatures time|url|ip). */
add_action( 'wp_ajax_ag_scan_log_delete_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_scan_log_bulk' ) ) wp_send_json_error();
	$sigs = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$sigs = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $sigs ) ) );
	if ( empty( $sigs ) ) wp_send_json_error();
	$log    = (array) get_option( 'ag_scan_log', array() );
	$before = count( $log );
	$log    = array_values( array_filter( $log, function ( $r ) use ( $sigs ) {
		return ! in_array( sha1( ( $r['time'] ?? '' ) . '|' . ( $r['url'] ?? '' ) . '|' . ( $r['ip'] ?? '' ) ), $sigs, true );
	} ) );
	update_option( 'ag_scan_log', $log, false );
	wp_send_json_success( array( 'removed' => $before - count( $log ) ) );
} );

/* ----------------------------------------------------------------- Helpers */
if ( ! function_exists( 'ag_tester_client_ip' ) ) {
	function ag_tester_client_ip() {
		// REMOTE_ADDR par défaut (anti-spoof). Proxy seulement si configuré.
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( defined( 'AG_TRUST_PROXY' ) && AG_TRUST_PROXY && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) )[0] );
		}
		return $ip;
	}
}

/* --------------------------------------------------- 1) Handler : lancer l'audit */
add_action( 'admin_post_nopriv_ag_tester_run', 'ag_tester_run' );
add_action( 'admin_post_ag_tester_run', 'ag_tester_run' );

/* ── Test LÉGER INSTANTANÉ (AJAX) pour le hero : URL -> score + nb failles ──
 * Public, sans login. Rate-limit par IP. Renvoie JSON {score, fails, secu, host}.
 * Verse aussi dans l'historique + journal comme un scan normal (réutilisation). */
add_action( 'wp_ajax_ag_quicktest', 'ag_quicktest' );
add_action( 'wp_ajax_nopriv_ag_quicktest', 'ag_quicktest' );
if ( ! function_exists( 'ag_quicktest' ) ) {
	function ag_quicktest() {
		check_ajax_referer( 'ag_quicktest', 'nonce' );
		$url = isset( $_POST['url'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['url'] ) ) ) : '';
		if ( '' === $url ) wp_send_json_error( array( 'msg' => 'Entrez l’adresse de votre site.' ), 400 );
		if ( ! preg_match( '#^https?://#i', $url ) ) $url = 'https://' . $url;
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host || ! preg_match( '#\.[a-z]{2,}$#i', $host ) ) wp_send_json_error( array( 'msg' => 'Adresse invalide. Ex : monsite.fr' ), 400 );

		// Rate-limit : 6 tests / 10 min / IP.
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '#[^0-9a-f:.]#i', '', $_SERVER['REMOTE_ADDR'] ) : '0';
		$key = 'ag_qt_' . md5( $ip );
		$n   = (int) get_transient( $key );
		if ( $n >= 6 ) wp_send_json_error( array( 'msg' => 'Trop de tests. Réessayez dans quelques minutes.' ), 429 );
		set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );

		if ( ! function_exists( 'ag_audit_run' ) ) wp_send_json_error( array( 'msg' => 'Service indisponible.' ), 500 );
		$a = ag_audit_run( $url );
		if ( empty( $a ) || ! isset( $a['score'] ) ) wp_send_json_error( array( 'msg' => 'Site inaccessible ou injoignable.' ), 200 );

		$score = (int) $a['score'];
		$fails = 0; $secu = 0;
		$sec_names = function_exists( 'ag_audit_sec_names' ) ? ag_audit_sec_names() : array();
		foreach ( (array) ( $a['checks'] ?? array() ) as $c ) {
			if ( 'ok' === ( $c['status'] ?? '' ) ) continue;
			$fails++;
			if ( $sec_names && in_array( $c['name'], $sec_names, true ) ) $secu++;
		}
		// Verse dans l'historique + journal (comme un vrai scan) si dispo.
		// push_crm = false : test public anonyme, pas d'ajout CRM automatique ici.
		if ( function_exists( 'ag_audit_hist_upsert' ) ) {
			$ct = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $url ) : array();
			ag_audit_hist_upsert( $a, $ct, 'passive', false, 'inbound' );
		}
		$msg = $score >= 80 ? 'Plutôt bien protégé — mais un audit complet révèle ce qui ne se voit pas.'
			: ( $score >= 50 ? 'Des failles exploitables sont visibles. À corriger vite.'
			: 'Site exposé : des failles critiques sont à la portée d’un robot.' );
		wp_send_json_success( array(
			'host'  => $host,
			'score' => $score,
			'fails' => $fails,
			'secu'  => $secu,
			'crit'  => (int) ( $a['critical'] ?? 0 ),
			'msg'   => $msg,
			'order' => home_url( '/tester-mon-site?u=' . rawurlencode( $url ) ),
		) );
	}
}
if ( ! function_exists( 'ag_tester_run' ) ) {
	function ag_tester_run() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_tester' ) ) wp_die( 'Lien expiré.' );
		if ( ! empty( $_POST['hp_field'] ) ) wp_die( 'Spam détecté.' );

		// URL SEULE : l'email/le prénom ne sont plus demandés ici (optionnels, hérités si fournis).
		$url    = esc_url_raw( wp_unslash( $_POST['site_url'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
		$tel    = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		if ( ! $url ) wp_die( 'Merci d\'indiquer l\'adresse de votre site.' );

		$audit = function_exists( 'ag_audit_run' ) ? ag_audit_run( $url ) : array( 'url' => $url, 'checks' => array(), 'score' => 0, 'ts' => time() );
		$aid   = wp_hash( $url . '|' . microtime() );
		set_transient( 'ag_tester_' . $aid, array(
			'audit'  => $audit,
			'email'  => $email,
			'prenom' => $prenom,
			'phone'  => $tel,
			'auth_at'=> current_time( 'mysql' ),
			'auth_ip'=> ag_tester_client_ip(),
			'unlocked' => false,
		), 30 * DAY_IN_SECONDS );

		// Journal des sites scannés (URL + IP du visiteur + score) — pour suivi / relance.
		if ( function_exists( 'ag_tester_log_scan' ) ) { ag_tester_log_scan( $url, $audit ); }

		// Verse aussi le scan dans l'HISTORIQUE de l'Espace Audit (mêmes fiches que
		// mes audits manuels : coordonnées publiques extraites + boutons + image rapport).
		// push_crm = false : le visiteur (test public) a son propre ajout CRM
		// plus bas, avec le prénom/email/téléphone qu'il a saisis.
		if ( function_exists( 'ag_audit_hist_upsert' ) ) {
			$ct_pub = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $url ) : array();
			ag_audit_hist_upsert( $audit, $ct_pub, 'passive', false, 'inbound' );
		}

		// CRM seulement si on a un email (URL seule = visiteur anonyme).
		if ( $email && function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name' => $prenom ?: $email, 'email' => $email, 'phone' => $tel, 'website' => $url,
				'source' => 'tester-mon-site', 'status' => 'nouveau',
				'notes' => 'A testé son site le ' . current_time( 'd/m/Y H:i' ) . ' — score ' . ( $audit['score'] ?? 0 ) . '/100',
			) );
		}
		$lead_lbl = $prenom ?: ( $email ?: ( wp_parse_url( $url, PHP_URL_HOST ) ?: $url ) );
		$tg_lead = '🔍 Nouveau test de site' . "\n" . $lead_lbl . ' — ' . $url . ' — score ' . ( $audit['score'] ?? 0 ) . '/100' . ( $tel ? "\n📞 " . $tel : '' );
		$tg_sec_chat = ag_tester_opt( 'tg_sec' );
		if ( $tg_sec_chat && function_exists( 'ag_tg_send' ) ) {
			ag_tg_send( $tg_sec_chat, $tg_lead ); // lead sécurité = canal sécurité dédié
		} elseif ( function_exists( 'ag_push' ) ) {
			ag_push( '🔍 Nouveau test de site', $lead_lbl . ' — ' . $url . ' — score ' . ( $audit['score'] ?? 0 ) . '/100' );
		}

		$base       = ! empty( $_POST['result_page'] ) ? esc_url_raw( wp_unslash( $_POST['result_page'] ) ) : ( wp_get_referer() ?: home_url( '/tester-mon-site' ) );
		$result_url = add_query_arg( array( 'aid' => $aid ), $base );

		// Email d'aperçu (flouté) — AUCUNE facture ici, simple diagnostic.
		$score = (int) ( $audit['score'] ?? 0 );
		$nb_pb = 0;
		foreach ( ( $audit['checks'] ?? array() ) as $c ) { if ( 'ok' !== ( $c['status'] ?? '' ) ) $nb_pb++; }
		if ( $email && function_exists( 'ag_email_wrap' ) ) {
			$inner  = '<p>Bonjour ' . esc_html( $prenom ?: '' ) . ',</p>';
			$inner .= '<p>Voici le diagnostic de <strong style="color:#D4B45C">' . esc_html( $url ) . '</strong> :</p>';
			$inner .= '<p style="font-size:30px;font-weight:bold;color:' . ( $score >= 75 ? '#28a745' : ( $score >= 50 ? '#F37A1F' : '#E10F1A' ) ) . '">' . $score . ' / 100</p>';
			$inner .= '<p><strong>' . $nb_pb . ' point(s)</strong> à corriger ont été détectés. Le détail précis (lesquels, et comment les corriger) est dans votre <strong>rapport complet</strong>.</p>';
			$inner .= ag_email_button( 'Voir mon diagnostic →', $result_url );
			$inner .= '<p style="font-size:12px;color:#9a9aa5">Diagnostic non-intrusif, réalisé avec votre autorisation. Données utilisées uniquement pour vous transmettre l\'audit.</p>';
			wp_mail(
				$email,
				'Votre diagnostic : ' . $score . '/100 — ' . $url,
				ag_email_wrap( 'Votre diagnostic est prêt', $inner ),
				array( 'Content-Type: text/html; charset=UTF-8' )
			);
		}

		wp_safe_redirect( $result_url );
		exit;
	}
}

/* --------------------------------------------- 2) Handler : accepter la commande */
add_action( 'admin_post_nopriv_ag_tester_order', 'ag_tester_order' );
add_action( 'admin_post_ag_tester_order', 'ag_tester_order' );
if ( ! function_exists( 'ag_tester_order' ) ) {
	function ag_tester_order() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_tester_order' ) ) wp_die( 'Lien expiré.' );
		$aid  = sanitize_text_field( wp_unslash( $_POST['aid'] ?? '' ) );
		$data = $aid ? get_transient( 'ag_tester_' . $aid ) : false;
		if ( ! $data ) wp_die( 'Diagnostic introuvable ou expiré. Relancez un test.' );
		if ( empty( $_POST['order_accept'] ) ) wp_die( 'Vous devez accepter la commande pour débloquer le rapport.' );

		$nom   = sanitize_text_field( wp_unslash( $_POST['nom'] ?? ( $data['prenom'] ?? '' ) ) );
		$email = $data['email'];
		$url   = $data['audit']['url'] ?? '';
		$price = (float) ag_tester_opt( 'price' );
		$waive = ! empty( $_POST['waive'] );

		// Consentement = signature électronique simple (horodatage + IP + nom).
		$consent = array(
			'aid' => $aid, 'nom' => $nom, 'email' => $email, 'url' => $url, 'price' => $price,
			'accepted_at' => current_time( 'mysql' ), 'ip' => ag_tester_client_ip(),
			'ua' => substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 200 ),
			'waive_retractation' => $waive,
		);
		$orders = get_option( 'ag_tester_orders', array() );
		if ( ! is_array( $orders ) ) $orders = array();
		$orders[] = $consent;
		update_option( 'ag_tester_orders', $orders, false );

		// Numéro de facture séquentiel.
		$seq = (int) get_option( 'ag_tester_invoice_seq', 0 ) + 1;
		update_option( 'ag_tester_invoice_seq', $seq, false );
		$invoice_no = 'AG-' . wp_date( 'Y' ) . '-' . str_pad( (string) $seq, 4, '0', STR_PAD_LEFT );

		// Débloque le rapport.
		$data['unlocked']   = true;
		$data['invoice_no'] = $invoice_no;
		$data['consent']    = $consent;
		set_transient( 'ag_tester_' . $aid, $data, 60 * DAY_IN_SECONDS );

		$base       = wp_get_referer() ?: home_url( '/tester-mon-site' );
		$report_url = add_query_arg( array( 'rapport' => $aid ), $base );
		$pay_url    = ag_tester_opt( 'pay_url' ) ?: home_url( '/contact' );

		// Email : FACTURE + lien rapport complet + paiement.
		if ( function_exists( 'ag_email_wrap' ) ) {
			$inner  = '<p>Bonjour ' . esc_html( $nom ) . ',</p>';
			$inner .= '<p>Merci pour votre commande. Votre <strong>rapport d\'audit complet</strong> de ' . esc_html( $url ) . ' est débloqué :</p>';
			$inner .= ag_email_button( 'Voir mon rapport complet →', $report_url );
			$inner .= ag_tester_facture_html( $invoice_no, $nom, $email, $url, $price );
			$inner .= '<p>Règlement :</p>' . ag_email_button( 'Payer ma facture (' . number_format_i18n( $price, 0 ) . ' €) →', $pay_url );
			$inner .= '<p style="font-size:12px;color:#9a9aa5">Commande acceptée électroniquement le ' . esc_html( $consent['accepted_at'] ) . ' (IP ' . esc_html( $consent['ip'] ) . ').'
				. ( $waive ? ' Vous avez demandé la fourniture immédiate et renoncé au délai de rétractation de 14 jours.' : '' ) . '</p>';
			wp_mail(
				$email,
				'Facture ' . $invoice_no . ' + votre rapport d\'audit complet',
				ag_email_wrap( 'Votre facture et votre rapport', $inner ),
				array( 'Content-Type: text/html; charset=UTF-8' )
			);
		}
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '🧾 Commande rapport audit', $nom . ' (' . $email . ') — ' . $url . ' — facture ' . $invoice_no . ' — ' . $price . ' €' );
		}

		wp_safe_redirect( add_query_arg( array( 'rapport' => $aid, 'ok' => '1' ), $base ) );
		exit;
	}
}

/* --------------------------------------------------------- Facture (HTML email) */
if ( ! function_exists( 'ag_tester_facture_html' ) ) {
	function ag_tester_facture_html( $invoice_no, $nom, $email, $url, $price ) {
		$s = 'style="font-family:Arial,sans-serif;font-size:13px;color:#cfcfd6;padding:6px 0;"';
		$h = '<div style="border:1px solid rgba(212,180,92,.3);border-radius:12px;padding:18px 20px;margin:18px 0;background:#0f0f16;">';
		$h .= '<div style="font-family:Georgia,serif;color:#D4B45C;font-size:16px;font-weight:bold;margin-bottom:10px;">FACTURE ' . esc_html( $invoice_no ) . '</div>';
		$h .= '<div ' . $s . '><strong>Émetteur :</strong> ' . esc_html( ag_tester_opt( 'raison' ) ) . ' — ' . esc_html( ag_tester_opt( 'adresse' ) ) . '<br>'
			. 'SIRET : ' . esc_html( ag_tester_opt( 'siret' ) ) . ' · ' . esc_html( ag_tester_opt( 'tva' ) ) . '</div>';
		$h .= '<div ' . $s . '><strong>Client :</strong> ' . esc_html( $nom ) . ' (' . esc_html( $email ) . ')</div>';
		$h .= '<div ' . $s . '><strong>Date :</strong> ' . esc_html( wp_date( 'd/m/Y' ) ) . '</div>';
		$h .= '<table role="presentation" width="100%" style="margin-top:10px;border-top:1px solid rgba(255,255,255,.1);">'
			. '<tr><td ' . $s . '>Rapport d\'audit complet — ' . esc_html( $url ) . '</td>'
			. '<td ' . $s . ' align="right">' . number_format_i18n( $price, 2 ) . ' €</td></tr>'
			. '<tr><td ' . $s . '><strong>Total TTC</strong></td><td ' . $s . ' align="right"><strong>' . number_format_i18n( $price, 2 ) . ' €</strong></td></tr>'
			. '</table>';
		$h .= '<div style="font-family:Arial,sans-serif;font-size:11px;color:#8a8a93;margin-top:8px;">Paiement à réception. ' . esc_html( ag_tester_opt( 'tva' ) ) . '.</div>';
		$h .= '</div>';
		return $h;
	}
}

/* --------------------------------------------------------- Shortcode / rendu front */
add_shortcode( 'ag_tester', 'ag_tester_render' );
if ( ! function_exists( 'ag_tester_render' ) ) {
	function ag_tester_render() {
		$aid     = isset( $_GET['aid'] ) ? sanitize_text_field( wp_unslash( $_GET['aid'] ) ) : '';
		$rapport = isset( $_GET['rapport'] ) ? sanitize_text_field( wp_unslash( $_GET['rapport'] ) ) : '';
		ob_start();
		if ( $rapport ) {
			$data = get_transient( 'ag_tester_' . $rapport );
			if ( $data && ! empty( $data['unlocked'] ) && function_exists( 'ag_audit_render_report' ) ) {
				if ( isset( $_GET['ok'] ) ) echo '<p style="max-width:920px;margin:18px auto;color:#28a745;text-align:center;font-weight:700">✅ Commande enregistrée — votre facture vient de vous être envoyée par email.</p>';
				ag_audit_render_report( $data['audit'], $data['prenom'] ?? '' );
			} else {
				echo '<section style="background:#0a0a0f;color:#fff;padding:80px 24px;text-align:center"><p>🔒 Ce rapport est verrouillé. Lancez un test ou débloquez votre rapport.</p></section>';
			}
		} elseif ( $aid && ( $data = get_transient( 'ag_tester_' . $aid ) ) && ! empty( $data['audit'] ) ) {
			if ( isset( $_GET['expert'] ) ) { ag_tester_render_expert( $aid, $data ); }
			else { ag_tester_render_teaser( $aid, $data ); }
		} else {
			ag_tester_render_form();
		}
		return ob_get_clean();
	}
}

/* ---- Page commande « Diagnostic Expert 24h » (scan Kali, paiement avant) ---- */
if ( ! function_exists( 'ag_tester_render_expert' ) ) {
	function ag_tester_render_expert( $aid, $data ) {
		$a     = $data['audit'] ?? array();
		$url   = $a['url'] ?? '';
		$host  = (string) wp_parse_url( $url, PHP_URL_HOST );
		$price = (float) ag_tester_opt( 'deep_price' );
		?>
		<section style="background:linear-gradient(180deg,#0a0a0f,#14141c);color:#fff;padding:60px 24px;min-height:70vh">
			<div style="max-width:640px;margin:0 auto">
				<span style="display:inline-block;padding:6px 14px;background:rgba(225,15,26,.15);border:1px solid rgba(225,15,26,.5);border-radius:999px;color:#ff6b6b;font-size:.8rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;margin-bottom:16px">🔬 Diagnostic Expert 24h</span>
				<h1 style="font-family:Georgia,serif;font-size:clamp(1.8rem,4vw,2.6rem);line-height:1.12;margin:0 0 12px">Audit approfondi de <em style="color:#F3D27A;font-style:italic"><?php echo esc_html( $host ); ?></em></h1>
				<p style="color:rgba(255,255,255,.82);line-height:1.6;margin:0 0 24px">Scan en profondeur (simulation d'attaque réelle, ports, vulnérabilités connues, plugins, énumération, SSL/TLS). Vous recevez par email le <strong>rapport complet + un plan de correction chiffré</strong>, <strong style="color:#F3D27A">en moins de 24 h</strong>.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;flex-direction:column;gap:14px;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.3);border-radius:16px;padding:24px">
					<input type="hidden" name="action" value="ag_tester_expert_order">
					<input type="hidden" name="aid" value="<?php echo esc_attr( $aid ); ?>">
					<?php wp_nonce_field( 'ag_tester_expert' ); ?>
					<input type="email" name="email" required placeholder="Votre email (pour recevoir le rapport)" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					<input type="text" name="nom" placeholder="Nom / société (optionnel)" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					<label style="display:flex;gap:10px;align-items:flex-start;font-size:.85rem;color:rgba(255,255,255,.85);line-height:1.45">
						<input type="checkbox" name="mandat_ok" value="1" required style="margin-top:3px">
						<span>⚠️ Je certifie être <strong>propriétaire ou dûment mandaté</strong> pour <?php echo esc_html( $host ); ?> et j'<strong>autorise expressément Alliance Groupe</strong> à réaliser un audit de sécurité approfondi (test d'intrusion non-destructif). Sans cette autorisation, l'audit est illégal (art. 323-1 C. pénal).</span>
					</label>
					<label style="display:flex;gap:10px;align-items:flex-start;font-size:.82rem;color:rgba(255,255,255,.7);line-height:1.45">
						<input type="checkbox" name="cgv_ok" value="1" required style="margin-top:3px">
						<span>Je commande le <strong>Diagnostic Expert 24h</strong><?php echo $price > 0 ? ' au prix de <strong>' . esc_html( number_format_i18n( $price, 0 ) ) . ' € TTC</strong>' : ''; ?>, paiement à la commande. J'accepte les <a href="<?php echo esc_url( home_url( '/cgv' ) ); ?>" target="_blank" style="color:#D4B45C">CGV</a>.</span>
					</label>
					<button type="submit" style="margin-top:4px;padding:18px 30px;background:linear-gradient(135deg,#E10F1A,#F37A1F);color:#fff;font-weight:900;border:none;border-radius:999px;font-size:1.05rem;cursor:pointer;box-shadow:0 12px 32px rgba(225,15,26,.4)">🔬 Commander &amp; payer<?php echo $price > 0 ? ' — ' . esc_html( number_format_i18n( $price, 0 ) ) . ' €' : ''; ?> →</button>
					<p style="color:rgba(255,255,255,.5);font-size:.76rem;text-align:center;margin:2px 0 0">Après paiement, l'audit est lancé et le rapport vous est envoyé sous 24 h. Autorisation horodatée (date + IP).</p>
				</form>
			</div>
		</section>
		<?php
	}
}

/* Handler : commande Diagnostic Expert 24h -> crée le job pentest + redirige paiement */
add_action( 'admin_post_nopriv_ag_tester_expert_order', 'ag_tester_expert_order' );
add_action( 'admin_post_ag_tester_expert_order', 'ag_tester_expert_order' );
if ( ! function_exists( 'ag_tester_expert_order' ) ) {
	function ag_tester_expert_order() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_tester_expert' ) ) wp_die( 'Lien expiré.' );
		if ( empty( $_POST['mandat_ok'] ) || empty( $_POST['cgv_ok'] ) ) wp_die( 'Vous devez cocher l\'autorisation et les CGV.' );
		$aid   = sanitize_text_field( wp_unslash( $_POST['aid'] ?? '' ) );
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$nom   = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
		$data  = get_transient( 'ag_tester_' . $aid );
		$url   = $data['audit']['url'] ?? '';
		if ( ! $url || ! is_email( $email ) ) wp_die( 'Données invalides.' );
		$host  = (string) wp_parse_url( $url, PHP_URL_HOST );

		// Crée le job pentest (file Kali). Mandat = référence en ligne horodatée.
		$mandat = 'WEB-' . gmdate( 'Ymd-His' ) . '-' . substr( md5( $email . $url ), 0, 6 );
		if ( function_exists( 'ag_pt_enqueue_job' ) ) {
			ag_pt_enqueue_job( $url, ( $nom ?: $host ), $mandat, 'complet', $email, 'expert24' );
		}

		// Notif admin : nouvelle commande Expert.
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '🔬 Commande Diagnostic Expert 24h', $host . ' — ' . $email . ' — mandat ' . $mandat . ' — LANCER LE RUNNER KALI' );
		}

		// Email de confirmation au client (paiement + délai 24h).
		$pay = ag_tester_opt( 'deep_pay_url' ) ?: ( ag_tester_opt( 'pay_url' ) ?: home_url( '/contact' ) );
		if ( $email && function_exists( 'ag_email_wrap' ) ) {
			$price = (float) ag_tester_opt( 'deep_price' );
			$inner  = '<p>Bonjour ' . esc_html( $nom ?: '' ) . ',</p>';
			$inner .= '<p>Merci pour votre commande du <strong>Diagnostic Expert 24h</strong> pour <strong style="color:#D4B45C">' . esc_html( $host ) . '</strong>.</p>';
			if ( $price > 0 ) {
				$inner .= '<p>Pour lancer l\'audit, merci de finaliser le paiement (' . esc_html( number_format_i18n( $price, 0 ) ) . ' € TTC) :</p>';
				$inner .= ag_email_button( 'Payer et lancer mon audit →', $pay );
			}
			$inner .= '<p>Dès réception du paiement, notre expert réalise l\'audit approfondi et vous envoie le <strong>rapport complet + plan de correction chiffré sous 24 h</strong>.</p>';
			$inner .= '<p style="font-size:12px;color:#9a9aa5">Audit réalisé avec votre autorisation (référence ' . esc_html( $mandat ) . '), enregistrée le ' . esc_html( current_time( 'd/m/Y H:i' ) ) . '.</p>';
			wp_mail( $email, 'Votre Diagnostic Expert 24h — ' . $host, ag_email_wrap( 'Commande reçue', $inner ), array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		// Redirige vers le paiement (ou /contact si pas de lien configuré).
		wp_safe_redirect( $pay );
		exit;
	}
}

if ( ! function_exists( 'ag_tester_render_form' ) ) {
	function ag_tester_render_form() {
		$in = 'padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem;width:100%;box-sizing:border-box';
		// Image 3D du gate : option, sinon fichier par défaut du thème (à fournir).
		$gate_img = ag_tester_opt( 'popup_img' );
		if ( ! $gate_img ) $gate_img = get_stylesheet_directory_uri() . '/assets/images/tester-3d.png';
		?>
		<!-- GATE plein écran : seul « Tester mon site » est cliquable. Le clic vaut
		     acceptation de l'autorisation de diagnostic + des conditions (clic-wrap). -->
		<div id="ag-tester-gate" role="dialog" aria-modal="true" aria-label="Tester mon site">
			<div class="ag-gate__img" style="background-image:linear-gradient(180deg,rgba(8,8,14,.45),rgba(8,8,14,.82)),url('<?php echo esc_url( $gate_img ); ?>');"></div>
			<div class="ag-gate__inner">
				<span class="ag-gate__tag">🔒 Diagnostic de sécurité</span>
				<h1 class="ag-gate__title">Votre site est-il <em>une cible</em> ?</h1>
				<p class="ag-gate__sub">Découvrez en 30 secondes le score de sécurité de votre site et le nombre de failles exposées. Gratuit, non-intrusif.</p>
				<button type="button" id="ag-gate__btn" class="ag-gate__btn">🔍 Tester mon site →</button>
				<p class="ag-gate__legal">En cliquant, je certifie être <strong>propriétaire ou mandaté</strong> pour ce site, j'autorise le <strong>diagnostic non-intrusif</strong> et j'accepte les <a href="<?php echo esc_url( home_url( '/cgv' ) ); ?>" target="_blank" rel="noopener">conditions</a>.</p>
			</div>
		</div>
		<style>
		#ag-tester-gate{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:#06060c;overflow:hidden;animation:agGateIn .4s ease}
		@keyframes agGateIn{from{opacity:0}to{opacity:1}}
		.ag-gate__img{position:absolute;inset:0;background-size:cover;background-position:center;z-index:0}
		.ag-gate__inner{position:relative;z-index:1;max-width:620px;text-align:center;padding:32px 24px;color:#fff}
		.ag-gate__tag{display:inline-block;padding:7px 16px;background:rgba(243,122,31,.18);border:1px solid rgba(243,122,31,.55);border-radius:999px;color:#F3D27A;font-size:.82rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;margin-bottom:20px}
		.ag-gate__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2.1rem,6vw,3.8rem);line-height:1.08;margin:0 0 16px;text-shadow:0 6px 30px rgba(0,0,0,.6)}
		.ag-gate__title em{font-style:italic;background:linear-gradient(135deg,#F37A1F,#ff5252);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
		.ag-gate__sub{color:rgba(255,255,255,.85);font-size:1.1rem;line-height:1.6;margin:0 0 30px;text-shadow:0 2px 12px rgba(0,0,0,.6)}
		.ag-gate__btn{padding:20px 44px;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:900;border:none;border-radius:999px;font-size:1.2rem;letter-spacing:.5px;cursor:pointer;box-shadow:0 16px 50px rgba(243,122,31,.5);transition:transform .2s;animation:agGatePulse 2.4s ease-in-out infinite}
		.ag-gate__btn:hover{transform:translateY(-3px) scale(1.03)}
		@keyframes agGatePulse{0%,100%{box-shadow:0 16px 50px rgba(243,122,31,.45)}50%{box-shadow:0 16px 70px rgba(243,122,31,.8)}}
		.ag-gate__legal{color:rgba(255,255,255,.62);font-size:.78rem;line-height:1.5;margin:22px auto 0;max-width:460px}
		.ag-gate__legal a{color:#D4B45C}
		body.ag-gate-open{overflow:hidden}
		@media(prefers-reduced-motion:reduce){.ag-gate__btn{animation:none}#ag-tester-gate{animation:none}}
		</style>
		<script>
		(function(){
			document.body.classList.add('ag-gate-open');
			var gate = document.getElementById('ag-tester-gate');
			var btn  = document.getElementById('ag-gate__btn');
			if(!gate||!btn) return;
			btn.addEventListener('click', function(){
				gate.style.display='none';
				document.body.classList.remove('ag-gate-open');
				try{ sessionStorage.setItem('ag_gate_ok','1'); }catch(e){}
				var f = document.querySelector('input[name="site_url"]'); if(f) f.focus();
			});
		})();
		</script>

		<section style="background:linear-gradient(180deg,#0a0a0f,#14141c);color:#fff;padding:80px 24px;min-height:78vh">
			<div style="max-width:620px;margin:0 auto;text-align:center">
				<span style="display:inline-block;padding:6px 14px;background:rgba(243,122,31,.15);border:1px solid rgba(243,122,31,.5);border-radius:999px;color:#F3D27A;font-size:.8rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px">🔍 Diagnostic gratuit · sans engagement</span>
				<h1 style="font-family:Georgia,serif;font-size:clamp(2rem,5vw,3.4rem);line-height:1.1;margin:0 0 14px">Testez la <em style="background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-style:italic">sécurité</em> de votre site</h1>
				<p style="color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.6;margin:0 0 32px">En 30 secondes : score global, nombre de failles détectées, et où vous en êtes vraiment. Diagnostic non-intrusif, avec votre autorisation.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="text-align:left;display:flex;flex-direction:column;gap:14px">
					<input type="hidden" name="action" value="ag_tester_run">
					<?php wp_nonce_field( 'ag_tester' ); ?>
					<input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
					<input type="url" name="site_url" required autofocus placeholder="https://monsite.fr" style="<?php echo $in; // phpcs:ignore ?>">
					<button type="submit" style="margin-top:6px;padding:18px 36px;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;border:none;border-radius:999px;font-size:1.05rem;letter-spacing:1px;text-transform:uppercase;cursor:pointer;box-shadow:0 12px 32px rgba(243,122,31,.35)">🔍 Analyser mon site gratuitement →</button>
					<p style="color:rgba(255,255,255,.5);font-size:.8rem;text-align:center;margin:12px 0 0">Diagnostic <strong style="color:rgba(255,255,255,.7)">non-intrusif</strong> : on lit uniquement vos pages publiques (comme un visiteur). Résultat immédiat, sans inscription.</p>
				</form>

				<!-- Les 3 niveaux d'audit (escalier) -->
				<div style="margin:46px auto 0;max-width:980px;text-align:left">
					<h2 style="text-align:center;font-family:Georgia,serif;font-size:1.6rem;color:#fff;margin:0 0 22px">3 niveaux d'audit, selon vos besoins</h2>
					<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
						<div style="background:rgba(40,167,69,.08);border:1px solid rgba(40,167,69,.4);border-radius:14px;padding:20px">
							<div style="color:#28a745;font-weight:800;letter-spacing:1px;font-size:.8rem;text-transform:uppercase">① Test léger · gratuit</div>
							<p style="color:rgba(255,255,255,.8);font-size:.92rem;line-height:1.5;margin:10px 0 0">Diagnostic non-intrusif immédiat : score, failles visibles, en-têtes, SSL, xmlrpc. <strong style="color:#fff">C'est le test ci-dessus.</strong></p>
						</div>
						<div style="background:rgba(243,122,31,.08);border:1px solid rgba(243,122,31,.4);border-radius:14px;padding:20px">
							<div style="color:#F37A1F;font-weight:800;letter-spacing:1px;font-size:.8rem;text-transform:uppercase">② Audit approfondi · client</div>
							<p style="color:rgba(255,255,255,.8);font-size:.92rem;line-height:1.5;margin:10px 0 0">Analyse complète (sauvegardes exposées, versions de plugins, énumération…). Réalisée <strong style="color:#fff">avec votre autorisation écrite</strong>, dans le cadre d'une prestation.</p>
							<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" style="display:inline-block;margin-top:12px;color:#F3D27A;font-weight:700;text-decoration:none">Demander un devis →</a>
						</div>
						<div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.2);border-radius:14px;padding:20px">
							<div style="color:#fff;font-weight:800;letter-spacing:1px;font-size:.8rem;text-transform:uppercase">③ Diagnostic Expert 24h · sur mandat</div>
							<p style="color:rgba(255,255,255,.8);font-size:.92rem;line-height:1.5;margin:10px 0 0">Scan en profondeur (simulation d'attaque réelle, ports, vulnérabilités, plugins) sous votre <strong style="color:#fff">autorisation</strong>. <strong style="color:#F3D27A">Rapport complet + plan de correction chiffré, livré en moins de 24 h.</strong></p>
							<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" style="display:inline-block;margin-top:12px;color:#F3D27A;font-weight:700;text-decoration:none">Nous contacter →</a>
						</div>
					</div>
					<p style="text-align:center;color:rgba(255,255,255,.45);font-size:.78rem;margin:14px 0 0">L'audit approfondi et le pentest ne sont jamais réalisés sans autorisation écrite du propriétaire (art. 323-1 C. pénal).</p>
				</div>

				<!-- Pas qu'un audit : les autres offres -->
				<div style="margin:42px auto 0;max-width:980px">
					<p style="text-align:center;color:rgba(255,255,255,.6);font-size:.9rem;margin:0 0 16px">Vous cherchez autre chose ?</p>
					<div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
						<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" style="flex:1;min-width:200px;text-align:center;background:rgba(255,255,255,.05);border:1px solid rgba(212,180,92,.3);border-radius:12px;padding:18px;color:#fff;text-decoration:none">✨ <strong>Créer un site</strong><br><span style="color:rgba(255,255,255,.6);font-size:.85rem">Vitrine, e-commerce, dès 490 €</span></a>
						<a href="<?php echo esc_url( home_url( '/maintenance' ) ); ?>" style="flex:1;min-width:200px;text-align:center;background:rgba(255,255,255,.05);border:1px solid rgba(212,180,92,.3);border-radius:12px;padding:18px;color:#fff;text-decoration:none">🛡️ <strong>Maintenance</strong><br><span style="color:rgba(255,255,255,.6);font-size:.85rem">Sérénité, dès 49 €/mois</span></a>
						<a href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>" style="flex:1;min-width:200px;text-align:center;background:rgba(255,255,255,.05);border:1px solid rgba(212,180,92,.3);border-radius:12px;padding:18px;color:#fff;text-decoration:none">📦 <strong>Templates métier</strong><br><span style="color:rgba(255,255,255,.6);font-size:.85rem">Prêts à l'emploi, gratuits</span></a>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}

if ( ! function_exists( 'ag_tester_render_teaser' ) ) {
	function ag_tester_render_teaser( $aid, $data ) {
		$a     = $data['audit'];
		$score = (int) ( $a['score'] ?? 0 );
		$color = $score >= 75 ? '#28a745' : ( $score >= 50 ? '#F37A1F' : '#E10F1A' );
		$price = (float) ag_tester_opt( 'price' );
		$nb_pb = 0;
		foreach ( ( $a['checks'] ?? array() ) as $c ) { if ( 'ok' !== ( $c['status'] ?? '' ) ) $nb_pb++; }
		?>
		<section style="background:linear-gradient(180deg,#0a0a0f,#14141c);color:#fff;padding:60px 24px">
			<div style="max-width:760px;margin:0 auto">
				<div style="text-align:center;margin-bottom:30px">
					<span style="color:rgba(255,255,255,.6);letter-spacing:3px;font-size:.82rem;text-transform:uppercase">Diagnostic · <?php echo esc_html( $a['url'] ?? '' ); ?></span>
					<div style="margin:22px auto 8px;width:170px;height:170px;position:relative">
						<svg viewBox="0 0 120 120" style="width:100%;height:100%;transform:rotate(-90deg)">
							<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="10"/>
							<circle cx="60" cy="60" r="52" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="10" stroke-dasharray="<?php echo (int) ( $score * 3.27 ); ?> 327" stroke-linecap="round"/>
						</svg>
						<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
							<div style="font-size:2.6rem;font-weight:900;color:<?php echo esc_attr( $color ); ?>"><?php echo $score; ?></div>
							<div style="font-size:.8rem;color:rgba(255,255,255,.6)">/ 100</div>
						</div>
					</div>
					<p style="font-size:1.15rem;margin:8px 0 0"><strong style="color:<?php echo esc_attr( $color ); ?>"><?php echo (int) $nb_pb; ?> problème(s)</strong> détecté(s) sur votre site.</p>
					<?php if ( ! empty( $a['critical'] ) ) : ?>
						<p style="margin:10px 0 0;color:#E10F1A;font-weight:800;font-size:1.05rem">🚨 <?php echo (int) $a['critical']; ?> faille(s) critique(s) — à corriger en priorité.</p>
					<?php endif; ?>
				</div>

				<!-- Liste : noms visibles, détails MASQUÉS -->
				<div style="display:flex;flex-direction:column;gap:8px;margin-bottom:30px">
					<?php foreach ( ( $a['checks'] ?? array() ) as $c ) :
						$st = $c['status'] ?? 'warn';
						$ic = 'ok' === $st ? '✅' : ( 'warn' === $st ? '⚠️' : '❌' );
						$cl = 'ok' === $st ? '#28a745' : ( 'warn' === $st ? '#F37A1F' : '#E10F1A' ); ?>
						<div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-left:3px solid <?php echo esc_attr( $cl ); ?>;border-radius:10px;padding:12px 16px">
							<span><?php echo $ic; // phpcs:ignore ?></span>
							<span style="flex:1;font-weight:600"><?php echo esc_html( $c['name'] ?? '' ); ?></span>
							<?php if ( 'ok' === $st ) : ?>
								<span style="color:#28a745;font-size:.85rem">OK</span>
							<?php else : ?>
								<span style="color:rgba(255,255,255,.4);font-size:.85rem;letter-spacing:2px">🔒 ●●●●●</span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<?php $deep_price = (float) ag_tester_opt( 'deep_price' ); $deep_pay = ag_tester_opt( 'deep_pay_url' ); if ( ! empty( $a['critical'] ) ) : ?>
				<!-- ESCALADE : faille critique detectee -> Diagnostic Expert 24h prioritaire -->
				<div style="background:linear-gradient(180deg,rgba(225,15,26,.18),rgba(225,15,26,.06));border:2px solid #E10F1A;border-radius:18px;padding:26px 22px;margin:0 0 20px;text-align:center">
					<div style="font-size:2rem">🚨</div>
					<h2 style="font-family:Georgia,serif;font-size:1.5rem;color:#fff;margin:6px 0 8px">Faille critique detectee : agissez maintenant</h2>
					<p style="color:rgba(255,255,255,.9);margin:0 0 16px;line-height:1.55">Votre site presente <strong style="color:#ff6b6b"><?php echo (int) $a['critical']; ?> faille(s) critique(s)</strong>. Un attaquant peut en profiter <strong>maintenant</strong>. Notre <strong>Diagnostic Expert 24h</strong> simule une vraie attaque (sur votre autorisation), identifie tout, et vous livre le <strong>rapport complet + plan de correction chiffre en moins de 24 h</strong>.</p>
					<a href="<?php echo esc_url( add_query_arg( array( 'aid' => $aid, 'expert' => '1' ), home_url( '/tester-mon-site' ) ) ); ?>" style="display:inline-block;background:linear-gradient(135deg,#E10F1A,#F37A1F);color:#fff;font-weight:900;text-decoration:none;padding:18px 34px;border-radius:999px;font-size:1.1rem;box-shadow:0 12px 36px rgba(225,15,26,.45)">🔬 Diagnostic Expert 24h<?php echo $deep_price > 0 ? ' — ' . esc_html( number_format_i18n( $deep_price, 0 ) ) . ' EUR' : ''; ?> →</a>
					<p style="color:rgba(255,255,255,.55);font-size:.78rem;margin:12px 0 0">Rapport detaille envoye par email. Realise avec votre autorisation (mandat).</p>
				</div>
				<?php endif; ?>
				<!-- Bloc commande : acceptation EXPLICITE -->
				<div style="background:linear-gradient(180deg,rgba(212,180,92,.1),rgba(243,122,31,.05));border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:28px 24px">
					<h2 style="font-family:Georgia,serif;font-size:1.5rem;margin:0 0 10px">Débloquez votre rapport complet</h2>
					<p style="color:rgba(255,255,255,.82);margin:0 0 16px">Le détail des <strong><?php echo (int) $nb_pb; ?> problèmes</strong>, leur gravité et <strong>comment les corriger</strong> — rapport clair, sans jargon. <strong style="color:#F3D27A"><?php echo esc_html( number_format_i18n( $price, 0 ) ); ?> € TTC</strong>.</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;flex-direction:column;gap:14px">
						<input type="hidden" name="action" value="ag_tester_order">
						<input type="hidden" name="aid" value="<?php echo esc_attr( $aid ); ?>">
						<?php wp_nonce_field( 'ag_tester_order' ); ?>
						<input type="text" name="nom" required placeholder="Nom et prénom" value="<?php echo esc_attr( $data['prenom'] ?? '' ); ?>" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
						<label style="display:flex;gap:10px;align-items:flex-start;font-size:.85rem;color:rgba(255,255,255,.8);line-height:1.45">
							<input type="checkbox" name="order_accept" value="1" required style="margin-top:3px">
							<span>Je commande mon <strong>rapport d'audit complet</strong> de <?php echo esc_html( $a['url'] ?? '' ); ?> au prix de <strong><?php echo esc_html( number_format_i18n( $price, 0 ) ); ?> € TTC</strong>. Je reconnais que <strong>cette commande m'engage</strong> et que le paiement est dû. J'ai lu et j'accepte les <a href="<?php echo esc_url( home_url( '/cgv' ) ); ?>" target="_blank" style="color:#D4B45C">CGV</a>.</span>
						</label>
						<label style="display:flex;gap:10px;align-items:flex-start;font-size:.82rem;color:rgba(255,255,255,.65);line-height:1.45">
							<input type="checkbox" name="waive" value="1" style="margin-top:3px">
							<span>(Particulier) Je demande la <strong>fourniture immédiate</strong> du rapport et renonce expressément à mon délai de rétractation de 14 jours (art. L221-28 C. conso).</span>
						</label>
						<button type="submit" style="margin-top:4px;padding:17px 30px;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;border:none;border-radius:999px;font-size:1.02rem;cursor:pointer">🔓 Débloquer mon rapport complet →</button>
						<p style="color:rgba(255,255,255,.5);font-size:.76rem;text-align:center;margin:4px 0 0">Facture envoyée par email. Acceptation horodatée (date + IP) faisant office de signature électronique.</p>
					</form>
				</div>
			</div>
		</section>
		<?php
	}
}

/* =========================================================================
 * ESPACE AUDIT (admin) — lancer des audits sur des sites à démarcher.
 * Audit NON-INTRUSIF (lecture des pages publiques, comme une visite). On ne
 * scanne/attaque jamais : on lit ce qui est public et on en tire un argumentaire.
 * ====================================================================== */
add_action( 'admin_menu', function () {
	add_menu_page( 'Espace Audit', '🔍 Espace Audit', 'manage_options', 'ag-espace-audit', 'ag_audit_prospect_page', 'dashicons-shield', 58 );
	add_submenu_page( 'ag-espace-audit', 'Générateur d\'audit', '🧰 Générateur d\'audit', 'manage_options', 'ag-audit-gen', 'ag_audit_gen_render' );
} );

if ( ! function_exists( 'ag_audit_gen_render' ) ) {
	/** Outil « Générateur & analyseur d'audit » (mono-fichier hors ligne) rendu en iframe. */
	function ag_audit_gen_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$base = get_template_directory_uri() . '/assets/tools/';
		$gen  = esc_url( $base . 'audit-generateur.html' );
		$ass  = esc_url( $base . 'assistant-pentest.html' );
		$tab  = ( isset( $_GET['outil'] ) && 'assistant' === $_GET['outil'] ) ? 'assistant' : 'generateur';
		$src  = 'assistant' === $tab ? $ass : $gen;
		?>
		<div class="wrap">
			<h1>🧰 Générateur d'audit sécurité</h1>
			<p style="max-width:820px;color:#444">Saisis le site à auditer → l'outil sort <b>toutes les commandes pré-remplies</b>, puis analyse tes résultats et produit la <b>note honnête</b> + le <b>rapport</b> + la <b>synthèse à coller</b>. Tout est <b>hors ligne</b> (aucune donnée envoyée). Usage sous <b>mandat écrit</b> uniquement.</p>
			<h2 class="nav-tab-wrapper" style="margin-bottom:0">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-audit-gen&outil=generateur' ) ); ?>" class="nav-tab <?php echo 'generateur' === $tab ? 'nav-tab-active' : ''; ?>">🧰 Générateur</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-audit-gen&outil=assistant' ) ); ?>" class="nav-tab <?php echo 'assistant' === $tab ? 'nav-tab-active' : ''; ?>">🛰️ Assistant guidé</a>
				<a href="<?php echo $gen; ?>" target="_blank" rel="noopener" class="nav-tab" style="float:right">↗ Ouvrir en plein écran</a>
			</h2>
			<iframe src="<?php echo esc_url( $src ); ?>" style="width:100%;height:82vh;border:1px solid #c3c4c7;border-top:0;background:#0e1016" title="Générateur d'audit"></iframe>
			<p style="font-size:12px;color:#777;margin-top:8px">💡 Le bouton « Copier » fonctionne ici (page sécurisée). Version fichier local : <code>pentest-bridge/audit-generateur.html</code> (double-clic hors ligne — mais « Copier » peut être bloqué en <code>file://</code>, d'où cette page).</p>
		</div>
		<?php
	}
}


if ( ! function_exists( 'ag_audit_fail_list' ) ) {
	/** Liste des intitulés de checks non-OK, critiques en tête. */
	function ag_audit_fail_list( $audit ) {
		$crit = array(); $other = array();
		foreach ( ( $audit['checks'] ?? array() ) as $c ) {
			if ( 'ok' === ( $c['status'] ?? '' ) ) continue;
			if ( ! empty( $c['critical'] ) && 'fail' === $c['status'] ) $crit[] = $c['name'];
			else $other[] = $c['name'];
		}
		return array_merge( $crit, $other );
	}
}
if ( ! function_exists( 'ag_audit_sec_names' ) ) {
	/** Intitulés considérés « sécurité ». */
	function ag_audit_sec_names() {
		return array(
			'Connexion sécurisée (HTTPS)', 'xmlrpc.php (force brute / DDoS)', 'En-têtes de sécurité HTTP',
			'Divulgation de version (techno)', 'Énumération des comptes (wp-json)', 'Fichiers sensibles exposés (.git/.env)',
			'Certificat SSL', 'Listing de répertoire (uploads)', 'Énumération d\'auteur (?author=1)',
			'Sauvegardes / config exposées', 'Listing de répertoires (système)', 'Versions de plugins exposées',
			'xmlrpc : pingback (amplification)',
		);
	}
}
if ( ! function_exists( 'ag_audit_split_fails' ) ) {
	/** Sépare les failles en [sécurité, seo]. */
	function ag_audit_split_fails( $audit ) {
		$sec = ag_audit_sec_names(); $S = array(); $O = array();
		foreach ( ( $audit['checks'] ?? array() ) as $c ) {
			if ( 'ok' === ( $c['status'] ?? '' ) ) continue;
			if ( in_array( $c['name'], $sec, true ) ) $S[] = $c; else $O[] = $c;
		}
		return array( $S, $O );
	}
}
if ( ! function_exists( 'ag_audit_segment' ) ) {
	/** Oriente un prospect : 'securite' (failles) ou 'creation' (site faible/à refaire). */
	function ag_audit_segment( $a ) {
		if ( ! empty( $a['critical'] ) ) return 'securite';
		$sec = ag_audit_sec_names(); $n = 0;
		foreach ( ( $a['checks'] ?? array() ) as $c ) {
			if ( 'ok' !== ( $c['status'] ?? '' ) && in_array( $c['name'], $sec, true ) ) $n++;
		}
		return ( $n >= 2 || (int) ( $a['score'] ?? 0 ) < 55 ) ? 'securite' : 'creation';
	}
}

/* ---- Historique PERSISTANT des audits (option ag_audit_history) ---- */
if ( ! function_exists( 'ag_audit_hist_id' ) )  { function ag_audit_hist_id( $url, $mode = 'passive' ) { return substr( md5( strtolower( trim( $url ) ) . '|' . $mode ), 0, 12 ); } }
if ( ! function_exists( 'ag_audit_hist_normalize' ) ) {
	/**
	 * COHÉRENCE ENTRE NIVEAUX (par site). Un scan plus PROFOND creuse davantage : il
	 * trouve AU MOINS autant de failles qu'un scan plus léger du même site. On impose
	 * donc, par host et par ordre de profondeur (léger → approfondi → expert Kali) :
	 *   1) NOTE : un scan plus profond ne peut JAMAIS afficher une meilleure note qu'un
	 *      scan plus léger (léger ≥ approfondi ≥ expert) — il prend en compte la note
	 *      des autres scans (plafonnée à la plus basse des scans plus légers).
	 *   2) FAILLES : un scan plus profond hérite AU MOINS des failles des scans plus
	 *      légers (union par nom) + son nombre de critiques ≥ celui des plus légers.
	 * Idempotent : on conserve les failles PROPRES du scan dans `own_checks`, et on
	 * recompose `checks` (= cumul) à chaque passage, sans jamais accumuler de doublon
	 * ni de faille périmée.
	 */
	function ag_audit_hist_normalize( $h ) {
		if ( ! is_array( $h ) || ! $h ) { return $h; }
		$rank  = array( 'passive' => 0, 'deep' => 1, 'expert' => 2 );
		$hosts = array();
		foreach ( $h as $k => $rec ) {
			$ho = $rec['host'] ?? '';
			if ( '' !== $ho ) { $hosts[ $ho ][] = $k; }
		}
		foreach ( $hosts as $keys ) {
			usort( $keys, function ( $x, $y ) use ( $h, $rank ) {
				$rx = $rank[ $h[ $x ]['mode'] ?? 'passive' ] ?? 0;
				$ry = $rank[ $h[ $y ]['mode'] ?? 'passive' ] ?? 0;
				if ( $rx === $ry ) { return (int) ( $h[ $x ]['ts'] ?? 0 ) <=> (int) ( $h[ $y ]['ts'] ?? 0 ); }
				return $rx <=> $ry;
			} );
			$cap      = 100;     // la note ne peut que baisser quand on creuse plus
			$acc      = array(); // failles cumulées (clé = nom) des scans plus légers
			$acc_crit = 0;
			foreach ( $keys as $k ) {
				// --- 2) Failles : on part des failles PROPRES + on hérite des plus légers ---
				$own = $h[ $k ]['own_checks'] ?? ( is_array( $h[ $k ]['checks'] ?? null ) ? $h[ $k ]['checks'] : array() );
				$h[ $k ]['own_checks'] = $own; // mémorise les failles propres (idempotence)
				$merged = $acc;
				foreach ( $own as $c ) {
					$nm = $c['name'] ?? '';
					if ( '' === $nm ) { continue; }
					$merged[ $nm ] = $c;
				}
				$list = array_values( $merged );
				$crit = 0;
				foreach ( $list as $c ) { if ( ! empty( $c['critical'] ) && 'fail' === ( $c['status'] ?? '' ) ) { $crit++; } }
				$crit = max( $crit, $acc_crit, (int) ( $h[ $k ]['critical'] ?? 0 ) );
				$h[ $k ]['checks']   = $list;
				$h[ $k ]['critical'] = $crit;
				// --- 1) Note : jamais meilleure qu'un scan plus léger du même site ---
				if ( isset( $h[ $k ]['score'] ) ) {
					$h[ $k ]['score'] = min( (int) $h[ $k ]['score'], $cap );
					$cap = (int) $h[ $k ]['score'];
				}
				$acc      = $merged;
				$acc_crit = $crit;
			}
		}
		return $h;
	}
}
if ( ! function_exists( 'ag_audit_hist_get' ) ) { function ag_audit_hist_get() { $h = get_option( 'ag_audit_history', array() ); $h = is_array( $h ) ? $h : array(); return ag_audit_hist_normalize( $h ); } }
if ( ! function_exists( 'ag_audit_hist_save' ) ){ function ag_audit_hist_save( $h ) { update_option( 'ag_audit_history', $h, false ); } }
if ( ! function_exists( 'ag_audit_hist_upsert' ) ) {
	function ag_audit_hist_upsert( $a, $ct, $mode = 'passive', $push_crm = true, $src = 'self' ) {
		$url = $a['url'] ?? ''; if ( ! $url ) return '';
		// src = qui a lancé l'audit :
		//   'inbound' = un visiteur via « Tester mon site » (= demande client)
		//   'auto'    = l'agent de prospection automatique (cron), pas moi
		//   'self'    = moi, manuellement (prospection / scan Kali)
		// Une demande client reste « inbound » même re-scannée ensuite.
		$src = in_array( $src, array( 'inbound', 'auto' ), true ) ? $src : 'self';
		// 3 niveaux : passive (léger) · deep (approfondi) · expert (Kali, scan réel).
		$mode = in_array( $mode, array( 'deep', 'expert' ), true ) ? $mode : 'passive';
		$id = ag_audit_hist_id( $url, $mode );
		$h  = ag_audit_hist_get();
		// Contrôles SUPPLÉMENTAIRES de l'audit approfondi (4 sondes que le léger ne fait pas).
		// On garde leur statut MÊME quand ils sont OK, pour PROUVER que l'approfondi a creusé plus.
		$deep_names = array(
			'Énumération d\'auteur (?author=1)',
			'Sauvegardes / config exposées',
			'Listing de répertoires (système)',
			'Versions de plugins exposées',
			'xmlrpc : pingback (amplification)',
		);
		$checks      = array();
		$deep_checks = array();
		foreach ( ( $a['checks'] ?? array() ) as $c ) {
			$nm = $c['name'] ?? '';
			if ( 'ok' !== ( $c['status'] ?? '' ) ) $checks[] = array( 'name' => $nm, 'status' => $c['status'], 'critical' => ! empty( $c['critical'] ) );
			if ( in_array( $nm, $deep_names, true ) ) $deep_checks[] = array( 'name' => $nm, 'status' => $c['status'] ?? 'ok' );
		}
		$prev = $h[ $id ] ?? array();
		$h[ $id ] = array(
			'url' => $url, 'host' => wp_parse_url( $url, PHP_URL_HOST ),
			'ts' => time(), 'score' => (int) ( $a['score'] ?? 0 ), 'critical' => (int) ( $a['critical'] ?? 0 ),
			'seg' => function_exists( 'ag_audit_segment' ) ? ag_audit_segment( $a ) : 'securite',
			'mode' => $mode,
			'checks' => $checks,
			'deep_checks' => $deep_checks,
			'company' => $ct['company'] ?? '', 'email' => $ct['emails'][0] ?? '', 'phone' => $ct['phones'][0] ?? '',
			'address' => $ct['address'] ?? '', 'siret' => $ct['siret'] ?? '', 'socials' => array_values( $ct['socials'] ?? array() ),
			'actions' => isset( $prev['actions'] ) && is_array( $prev['actions'] ) ? $prev['actions'] : array(),
			// Source : une demande client déjà enregistrée le reste (ne pas rétrograder en « self »).
			'src' => ( 'inbound' === ( $prev['src'] ?? '' ) ) ? 'inbound' : $src,
		);
		if ( count( $h ) > 150 ) { uasort( $h, function ( $x, $y ) { return (int) ( $y['ts'] ?? 0 ) <=> (int) ( $x['ts'] ?? 0 ); } ); $h = array_slice( $h, 0, 150, true ); }

		// Cohérence des notes entre niveaux (léger ≥ approfondi ≥ expert) pour le même site.
		$h = ag_audit_hist_normalize( $h );

		ag_audit_hist_save( $h );

		// Verse AUTOMATIQUEMENT le site scanné dans le CRM Prospection (option
		// ag_prospects), comme un prospect "site". Ainsi un site prospecté via la
		// SÉCURITÉ (Espace Audit, scan manuel/expert) apparaît AUSSI dans la
		// Prospection, pas seulement dans l'historique des audits. L'anti-doublon
		// de ag_prospect_add_record empêche les répétitions. On NE pousse PAS pour
		// les tests publics anonymes ($push_crm = false) : ils ont leur propre
		// ajout CRM (avec le prénom/email saisis) dans ag_tester_run().
		if ( $push_crm && function_exists( 'ag_prospect_add_record' ) ) {
			$seg   = $h[ $id ]['seg'];
			$vnames = array();
			foreach ( $checks as $c ) { if ( ! empty( $c['name'] ) ) { $vnames[] = $c['name']; } }
			$vstr  = implode( ', ', array_slice( $vnames, 0, 6 ) );
			ag_prospect_add_record( array(
				'name'    => ( $ct['company'] ?? '' ) ?: ( wp_parse_url( $url, PHP_URL_HOST ) ?: $url ),
				'website' => $url,
				'email'   => $ct['emails'][0] ?? '',
				'phone'   => $ct['phones'][0] ?? '',
				'address' => $ct['address'] ?? '',
				'source'  => 'audit-' . $seg,
				'status'  => 'nouveau',
				'notes'   => '[' . strtoupper( $seg ) . '] Scan ' . strtoupper( $mode ) . ' ' . current_time( 'd/m/Y H:i' )
					. ' — score ' . (int) ( $a['score'] ?? 0 ) . '/100.'
					. ( ! empty( $ct['address'] ) ? ' Adresse : ' . $ct['address'] . '.' : '' )
					. ( $vstr ? ' Failles : ' . $vstr . '.' : '' ),
			) );
		}

		return $id;
	}
}
if ( ! function_exists( 'ag_audit_risk_detail' ) ) {
	/** Détail technique « qui fait peur » par type de faille. */
	function ag_audit_risk_detail( $name ) {
		$map = array(
			'xmlrpc.php (force brute / DDoS)'        => 'porte ouverte au bourrage d\'identifiants (des milliers de tentatives/minute) et au DDoS par pingback',
			'xmlrpc : pingback (amplification)'      => 'votre serveur peut être utilisé comme arme dans une attaque DDoS contre d\'autres',
			'Énumération des comptes (wp-json)'      => 'vos identifiants de connexion sont publics — la moitié du travail d\'un pirate est déjà faite',
			'Énumération d\'auteur (?author=1)'      => 'le login administrateur est exposé publiquement',
			'Fichiers sensibles exposés (.git/.env)' => 'code source, mots de passe et clés d\'API téléchargeables par n\'importe qui',
			'Sauvegardes / config exposées'          => 'votre base de données et vos mots de passe sont téléchargeables directement',
			'Certificat SSL'                         => 'connexion non chiffrée, données interceptables, site signalé « non sécurisé » par Google',
			'En-têtes de sécurité HTTP'              => 'aucune protection contre le détournement de page (clickjacking) et l\'injection de scripts',
			'Versions de plugins exposées'           => 'les versions exactes sont visibles : un pirate applique directement les exploits connus (CVE)',
			'Divulgation de version (techno)'        => 'la version du CMS/serveur est exposée et guide les attaquants vers les failles connues',
			'Listing de répertoire (uploads)'        => 'le contenu de vos dossiers est listable publiquement',
			'Listing de répertoires (système)'       => 'vos répertoires système sont explorables publiquement',
		);
		return $map[ $name ] ?? 'point de sécurité à corriger';
	}
}
if ( ! function_exists( 'ag_audit_risk_scenario' ) ) {
	/** Conséquence CONCRÈTE et alarmiste (mais factuelle) par type de faille. */
	function ag_audit_risk_scenario( $name ) {
		$map = array(
			'xmlrpc.php (force brute / DDoS)'        => "des robots testent des milliers de mots de passe par minute sur votre compte admin. Le jour où ils entrent, votre site est modifié, redirigé vers un site douteux, ou effacé — souvent un matin, sans prévenir.",
			'xmlrpc : pingback (amplification)'      => "votre serveur peut être utilisé à votre insu pour attaquer d'autres sites. Vous pourriez en être tenu responsable, et votre hébergeur peut suspendre votre site.",
			'Énumération des comptes (wp-json)'      => "la liste de vos identifiants de connexion est déjà publique : il ne reste plus qu'à deviner le mot de passe. Le travail du pirate est à moitié fait.",
			'Énumération d\'auteur (?author=1)'      => "votre identifiant administrateur s'affiche en clair pour n'importe qui.",
			'Fichiers sensibles exposés (.git/.env)' => "vos mots de passe de base de données et vos clés sont téléchargeables par un inconnu. Il peut copier TOUTE votre base clients (emails, téléphones, commandes) en quelques secondes.",
			'Sauvegardes / config exposées'          => "une copie complète de votre site et de votre base de données est téléchargeable directement depuis l'extérieur.",
			'Certificat SSL'                         => "Google affiche un écran rouge « Site non sécurisé » à chaque visiteur. La plupart font demi-tour aussitôt : vous perdez des clients sans même le voir.",
			'Connexion sécurisée (HTTPS)'            => "les données saisies sur votre site (formulaires, mots de passe) circulent en clair et peuvent être interceptées.",
			'En-têtes de sécurité HTTP'              => "un pirate peut superposer une fausse page à la vôtre pour voler les coordonnées de vos visiteurs — ils croient être chez vous.",
			'Versions de plugins exposées'           => "vos extensions affichent leur version exacte. Si l'une a une faille connue, des outils automatiques l'exploitent en masse, sans vous cibler personnellement.",
			'Divulgation de version (techno)'        => "la version de votre site et de votre serveur est visible : elle indique aux attaquants exactement quelles failles tester.",
			'Listing de répertoire (uploads)'        => "n'importe qui peut parcourir vos dossiers comme un répertoire ouvert et y trouver des fichiers que vous pensiez privés.",
			'Listing de répertoires (système)'       => "vos répertoires système sont explorables publiquement, fichier par fichier.",
		);
		return $map[ $name ] ?? ag_audit_risk_detail( $name );
	}
}
if ( ! function_exists( 'ag_audit_has_data_leak' ) ) {
	/** Vrai si une faille détectée expose potentiellement des DONNÉES personnelles (→ volet RGPD). */
	function ag_audit_has_data_leak( $S ) {
		$leak = array( 'Fichiers sensibles exposés (.git/.env)', 'Sauvegardes / config exposées', 'Énumération des comptes (wp-json)', 'Listing de répertoire (uploads)', 'Listing de répertoires (système)' );
		foreach ( $S as $c ) { if ( in_array( $c['name'], $leak, true ) ) return true; }
		return false;
	}
}
if ( ! function_exists( 'ag_audit_public_label' ) ) {
	/**
	 * Libellé PUBLIC d'une faille pour le RAPPORT TEASER (image) : on dit la
	 * NATURE du risque (ça fait peur) mais PAS l'emplacement/le correctif exact
	 * (sinon le prospect corrige seul sans payer). Le détail technique reste
	 * réservé au rapport complet payant.
	 */
	function ag_audit_public_label( $name ) {
		$map = array(
			'xmlrpc.php (force brute / DDoS)'        => "Porte d'entrée d'attaque ouverte",
			'xmlrpc : pingback (amplification)'      => 'Serveur exploitable pour attaquer autrui',
			'Énumération des comptes (wp-json)'      => 'Identifiants de connexion exposés',
			'Énumération d\'auteur (?author=1)'      => 'Identifiant administrateur exposé',
			'Fichiers sensibles exposés (.git/.env)' => 'Fichiers confidentiels accessibles',
			'Sauvegardes / config exposées'          => 'Sauvegarde / base de données accessible',
			'Certificat SSL'                         => 'Connexion non sécurisée',
			'Connexion sécurisée (HTTPS)'            => 'Connexion non chiffrée',
			'En-têtes de sécurité HTTP'              => 'Protections navigateur manquantes',
			'Versions de plugins exposées'           => 'Versions logicielles exposées',
			'Divulgation de version (techno)'        => 'Version du système exposée',
			'Listing de répertoire (uploads)'        => 'Dossiers explorables publiquement',
			'Listing de répertoires (système)'       => 'Répertoires système explorables',
		);
		return $map[ $name ] ?? 'Point de sécurité à corriger';
	}
}
if ( ! function_exists( 'ag_audit_deep_projection' ) ) {
	/**
	 * Score PROJETÉ de l'audit approfondi (toujours pire que le simple) : sert de
	 * référence pour montrer au prospect que le simple ne montre que la surface.
	 * Déterministe (même site = même projection).
	 */
	function ag_audit_deep_projection( $score, $crit, $nb ) {
		$score = (int) $score; $crit = (int) $crit; $nb = (int) $nb;
		$deep = (int) round( $score * 0.55 ) - $crit * 6 - $nb * 2;
		if ( $deep >= $score ) $deep = $score - 12;
		return max( 3, min( 100, $deep ) );
	}
}
if ( ! function_exists( 'ag_audit_report_payload' ) ) {
	/**
	 * Données du RAPPORT TEASER (image partageable) : score simple, projection
	 * approfondie, failles MASQUÉES (nature seule), CTA. Renvoie un tableau
	 * sérialisable en JSON pour le générateur d'image côté navigateur.
	 */
	function ag_audit_report_payload( $audit, $host = '', $order_link = '' ) {
		$host  = $host ?: wp_parse_url( $audit['url'] ?? '', PHP_URL_HOST );
		$score = (int) ( $audit['score'] ?? 0 );
		$crit  = (int) ( $audit['critical'] ?? 0 );
		list( $S ) = ag_audit_split_fails( $audit );
		$fails = array();
		foreach ( $S as $c ) {
			$fails[] = array(
				'label' => ag_audit_public_label( $c['name'] ),
				'crit'  => ( ! empty( $c['critical'] ) && 'fail' === ( $c['status'] ?? '' ) ) ? 1 : 0,
			);
		}
		// Note approfondie : si un VRAI scan Kali existe pour ce domaine, on prend SA note
		// réelle (ag_pt_summary_fr). Sinon, projection estimée (le scan Kali n'a pas tourné).
		$deep_real = null;
		if ( function_exists( 'ag_tester_kali_report_for' ) && function_exists( 'ag_pt_summary_fr' ) ) {
			$kali = ag_tester_kali_report_for( $audit['url'] ?? '' );
			if ( $kali ) {
				$an = ag_pt_summary_fr( $kali );
				// REGLE : la note Kali ne peut JAMAIS etre meilleure que le leger. On part du
				// score leger et on RETIRE les penalites des failles Kali supplementaires.
				$malus     = $an['crit'] * 28 + $an['high'] * 14 + $an['med'] * 6 + $an['low'] * 2;
				$deep_real = max( 3, min( $score, $score - $malus ) );
			}
		}
		$deep = ( null !== $deep_real ) ? $deep_real : ag_audit_deep_projection( $score, $crit, count( $S ) );
		if ( $deep > $score ) { $deep = $score; } // garde-fou : approfondi jamais meilleur que leger
		return array(
			'host'    => $host,
			'score'   => $score,
			'crit'    => $crit,
			'nb'      => count( $S ),
			'deep'    => $deep,
			'deep_real' => ( null !== $deep_real ) ? 1 : 0, // 1 = vraie note Kali, 0 = projection
			'fails'   => $fails,
			'phone'   => ag_tester_opt( 'phone' ),
			'order'   => $order_link,
			'brand'   => 'Alliance Groupe — Sécurité & création web',
		);
	}
}
if ( ! function_exists( 'ag_wa_phone' ) ) {
	/** Normalise un téléphone FR en chiffres internationaux pour wa.me. */
	function ag_wa_phone( $phone ) {
		$d = preg_replace( '#\D#', '', $phone );
		if ( '' === $d ) return '';
		if ( 0 === strpos( $d, '33' ) ) return $d;
		if ( '0' === ( $d[0] ?? '' ) ) return '33' . substr( $d, 1 );
		return $d;
	}
}

/* ----- Message SÉCURITÉ : alerte risque + détails techniques + lien commande ----- */
if ( ! function_exists( 'ag_tester_kali_report_for' ) ) {
	/** Synthese du dernier job Kali TERMINE pour ce domaine (ou ''). */
	function ag_tester_kali_report_for( $url ) {
		if ( ! function_exists( 'ag_pt_jobs' ) ) { return ''; }
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( '' === $host ) { return ''; }
		$best = null;
		foreach ( ag_pt_jobs() as $j ) {
			if ( 'done' !== ( $j['status'] ?? '' ) || empty( $j['summary'] ) ) { continue; }
			$jh = strtolower( (string) wp_parse_url( $j['target'] ?? '', PHP_URL_HOST ) );
			if ( $jh !== $host ) { continue; }
			if ( null === $best || (int) ( $j['finished'] ?? 0 ) > (int) ( $best['finished'] ?? 0 ) ) { $best = $j; }
		}
		return $best ? (string) $best['summary'] : '';
	}
}
if ( ! function_exists( 'ag_audit_msg_global' ) ) {
	/** Message GLOBAL : securite + creation/refonte fusionnes en un seul texte. */
	function ag_audit_msg_global( $a, $ct = array() ) {
		$host  = wp_parse_url( $a['url'] ?? '', PHP_URL_HOST );
		$S     = ag_audit_split_fails( $a )[0];
		$crit  = (int) ( $a['critical'] ?? 0 );
		$score = (int) ( $a['score'] ?? 0 );
		$m  = "Bonjour,\n\n";
		$m .= "je viens d'analyser votre site (" . $host . ") et je me permets de vous contacter.\n\n";
		$m .= "SECURITE - j'ai detecte " . count( $S ) . " faille(s)" . ( $crit ? ", dont $crit CRITIQUE(S)" : '' ) . " (score " . $score . "/100). Par exemple :\n";
		foreach ( array_slice( $S, 0, 2 ) as $c ) {
			$m .= "- " . ( $c['name'] ?? '' ) . "\n";
		}
		$m .= "\nSITE - je concois aussi des sites modernes, rapides et securises des le depart (des 490 EUR, payables en 4x). Selon votre cas : corriger l'existant ou repartir sur une base saine.\n\n";
		$m .= "Je vous propose un echange gratuit de 10 min pour " . $host . " (failles + idees concretes). Reponse sous 24 h, sans jargon.\n\n";
		$m .= "- Fabrizio, Alliance Groupe";
		return $m;
	}
}
if ( ! function_exists( 'ag_audit_msg_securite' ) ) {
	function ag_audit_msg_securite( $audit, $contacts = array(), $order_link = '' ) {
		$host  = wp_parse_url( $audit['url'] ?? '', PHP_URL_HOST );
		$score = (int) ( $audit['score'] ?? 0 );
		$crit  = (int) ( $audit['critical'] ?? 0 );
		$hi    = ! empty( $contacts['company'] ) ? ' ' . $contacts['company'] : '';
		$phone = ag_tester_opt( 'phone' );
		list( $S ) = ag_audit_split_fails( $audit );

		$m  = "Bonjour$hi,\n\n";
		$m .= "⚠️ Je viens d'analyser la sécurité de votre site ($host) et je dois vous alerter : ";
		$m .= "j'ai détecté " . count( $S ) . " faille(s) de sécurité" . ( $crit ? ", dont $crit CRITIQUE(S)" : '' ) . ". Score global : $score/100.\n\n";
		$m .= "Concrètement, voici ce que ça veut dire pour vous :\n\n";
		$n = 0;
		foreach ( $S as $c ) {
			if ( $n >= 4 ) break; $n++;
			$flag = ( ! empty( $c['critical'] ) && 'fail' === $c['status'] ) ? '🔴' : '🟠';
			$m .= "$flag " . $c['name'] . "\n   → " . ag_audit_risk_scenario( $c['name'] ) . "\n\n";
		}
		$m .= "Ce n'est pas théorique : ces failles sont scannées en continu par des robots automatisés, 24h/24, qui ne ciblent personne en particulier — ils ramassent tous les sites vulnérables qu'ils croisent.\n\n";
		$m .= "Le scénario classique : un matin, votre site affiche une page de pirates ou réclame une rançon en bitcoins pour rendre vos données. Vos clients ne peuvent plus vous joindre, votre référencement Google s'effondre, et la confiance, elle, ne revient pas.\n\n";
		if ( ag_audit_has_data_leak( $S ) ) {
			$m .= "⚖️ Et ce n'est pas qu'un problème technique : si les données personnelles de vos clients fuient (emails, téléphones, commandes), la loi vous oblige à le déclarer à la CNIL sous 72 h, avec à la clé des sanctions financières — sans parler de votre réputation.\n\n";
		}
		$m .= "La bonne nouvelle : tout ça se corrige. Je peux sécuriser votre site et vous remettre le rapport complet (toutes les failles + comment les corriger).\n\n";
		if ( $phone ) $m .= "📞 Le plus simple : rappelez-moi au $phone.\n";
		if ( $order_link ) $m .= "🔓 Ou débloquez votre rapport de sécurité complet maintenant : $order_link\n";
		$m .= "\nFabrizio — Alliance Groupe\n— STOP pour ne plus être contacté.";
		return $m;
	}
}
if ( ! function_exists( 'ag_audit_sms_securite' ) ) {
	function ag_audit_sms_securite( $audit, $order_link = '' ) {
		$host  = wp_parse_url( $audit['url'] ?? '', PHP_URL_HOST );
		$score = (int) ( $audit['score'] ?? 0 );
		list( $S ) = ag_audit_split_fails( $audit );
		$f1 = ! empty( $S[0]['name'] ) ? $S[0]['name'] : 'failles de securite';
		$t  = "ALERTE securite $host : " . count( $S ) . " faille(s) de securite, score global $score/100. Risque : piratage, vol de vos donnees clients, site hors-ligne ou rancon. ";
		$t .= $order_link ? "Votre rapport complet : $order_link" : "Je peux vous aider a corriger.";
		$t .= " Fabrizio-Alliance Groupe. STOP pour stop.";
		return $t;
	}
}

/* ----- Message CRÉATION : refonte / site moderne (pas de peur) ----- */
if ( ! function_exists( 'ag_audit_msg_creation' ) ) {
	function ag_audit_msg_creation( $audit, $contacts = array() ) {
		$host  = wp_parse_url( $audit['url'] ?? '', PHP_URL_HOST );
		$hi    = ! empty( $contacts['company'] ) ? ' ' . $contacts['company'] : '';
		$phone = ag_tester_opt( 'phone' );
		list( , $O ) = ag_audit_split_fails( $audit );
		$pts = array();
		foreach ( $O as $c ) { $pts[] = $c['name']; if ( count( $pts ) >= 3 ) break; }
		$liste = $pts ? '- ' . implode( "\n- ", $pts ) : '';

		$m  = "Bonjour$hi,\n\n";
		$m .= "J'ai regardé votre site ($host). Il y a de bonnes bases, mais aussi des points qui vous coûtent probablement des clients :\n";
		$m .= $liste ? $liste . "\n\n" : "(affichage, mobile, référencement…)\n\n";
		$m .= "Je conçois des sites modernes, rapides et qui inspirent confiance — dès 490 €, payables en 4× sans frais. Sécurisés dès le départ.\n\n";
		$m .= "Envie de voir des exemples adaptés à votre activité ?\n";
		if ( $phone ) $m .= "📞 Rappelez-moi au $phone, ";
		$m .= "ou répondez simplement à ce message.\n\n";
		$m .= "Fabrizio — Alliance Groupe (Nantes)\n— STOP pour ne plus être contacté.";
		return $m;
	}
}

if ( ! function_exists( 'ag_audit_prospect_page' ) ) {
	function ag_audit_prospect_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$results = array();
		$notice  = '';
		if ( isset( $_GET['agmsg'] ) ) {
			$gm = sanitize_key( $_GET['agmsg'] );
			if ( 'del1' === $gm )       $notice = '🗑️ Audit supprimé.';
			elseif ( 'delnone' === $gm ) $notice = '⚠️ Audit introuvable (déjà supprimé ?).';
			elseif ( 'cleared' === $gm ) $notice = '🗑️ Historique vidé : tous les audits ont été supprimés.';
			elseif ( 'rescanned' === $gm ) $notice = '🔄 ' . (int) ( $_GET['agn'] ?? 0 ) . ' site(s) réaudité(s) avec l\'algo à jour — notes, messages et images recalculés.';
		}

		// Ajout au CRM (action ligne par ligne) avec coordonnées extraites.
		if ( isset( $_POST['ag_add_crm'], $_POST['_agp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) ) {
			$u    = esc_url_raw( wp_unslash( $_POST['crm_url'] ?? '' ) );
			$sc   = (int) ( $_POST['crm_score'] ?? 0 );
			$cm   = sanitize_text_field( wp_unslash( $_POST['crm_name'] ?? '' ) );
			$ce   = sanitize_email( wp_unslash( $_POST['crm_email'] ?? '' ) );
			$cp   = sanitize_text_field( wp_unslash( $_POST['crm_phone'] ?? '' ) );
			$ca   = sanitize_text_field( wp_unslash( $_POST['crm_addr'] ?? '' ) );
			$cv   = sanitize_text_field( wp_unslash( $_POST['crm_vulns'] ?? '' ) );
			$seg  = ( 'creation' === ( $_POST['crm_segment'] ?? 'securite' ) ) ? 'creation' : 'securite';
			if ( $u && function_exists( 'ag_prospect_add_record' ) ) {
				ag_prospect_add_record( array(
					'name'    => $cm ?: ( wp_parse_url( $u, PHP_URL_HOST ) ?: $u ),
					'website' => $u, 'email' => $ce, 'phone' => $cp,
					'source'  => 'audit-' . $seg, 'status' => 'nouveau',
					'notes'   => '[' . strtoupper( $seg ) . '] Audit Espace Audit ' . current_time( 'd/m/Y H:i' ) . ' — score ' . $sc . '/100.'
						. ( $ca ? ' Adresse : ' . $ca . '.' : '' )
						. ( $cv ? ' Failles : ' . $cv . '.' : '' ),
				) );
				$notice = '✅ ' . esc_html( $cm ?: $u ) . ' ajouté au CRM (segment ' . $seg . ').';
			}
		}

		// Envoi Telegram du prospect (bouton 📲 Telegram ou lors de l'ajout CRM).
		if ( isset( $_POST['_agp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) && ( isset( $_POST['ag_send_tg'] ) || isset( $_POST['ag_add_crm'] ) ) && function_exists( 'ag_push' ) ) {
			$tu  = esc_url_raw( wp_unslash( $_POST['crm_url'] ?? '' ) );
			$ts  = (int) ( $_POST['crm_score'] ?? 0 );
			$tn  = sanitize_text_field( wp_unslash( $_POST['crm_name'] ?? '' ) ) ?: ( wp_parse_url( $tu, PHP_URL_HOST ) ?: $tu );
			$te  = sanitize_email( wp_unslash( $_POST['crm_email'] ?? '' ) );
			$tp  = sanitize_text_field( wp_unslash( $_POST['crm_phone'] ?? '' ) );
			$taa = sanitize_text_field( wp_unslash( $_POST['crm_addr'] ?? '' ) );
			$tvv = sanitize_text_field( wp_unslash( $_POST['crm_vulns'] ?? '' ) );
			$tsg = ( 'creation' === ( $_POST['crm_segment'] ?? 'securite' ) ) ? 'creation' : 'securite';
			if ( $tu ) {
				$tico  = ( 'securite' === $tsg ) ? '🛡️' : '✨';
				$tbody = $tn . "\n" . $tu . "\nScore " . $ts . '/100 · ' . strtoupper( $tsg )
					. ( $te ? "\n📧 " . $te : '' ) . ( $tp ? "\n📞 " . $tp : '' )
					. ( $taa ? "\n📍 " . $taa : '' ) . ( $tvv ? "\n⚠️ " . $tvv : '' );
				$ttitle = $tico . ' Prospect ' . strtoupper( $tsg );
				$tchat  = ( 'securite' === $tsg ) ? ag_tester_opt( 'tg_sec' ) : ag_tester_opt( 'tg_crea' );
				if ( $tchat && function_exists( 'ag_tg_send' ) ) {
					ag_tg_send( $tchat, $ttitle . "\n\n" . $tbody ); // canal dédié au segment
				} else {
					ag_push( $ttitle, $tbody ); // repli : canal interne
				}
				$notice = isset( $_POST['ag_add_crm'] ) ? ( '✅ ' . esc_html( $tn ) . ' → CRM + Telegram (' . $tsg . ').' ) : ( '📲 ' . esc_html( $tn ) . ' envoyé sur Telegram (' . $tsg . ').' );
			}
		}

		// Historique : marquer un canal contacté, ou supprimer une entrée.
		if ( isset( $_POST['_agp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) ) {
			if ( isset( $_POST['ag_hist_mark'], $_POST['hist_id'], $_POST['hist_ch'] ) ) {
				$hh = ag_audit_hist_get(); $hid = sanitize_text_field( wp_unslash( $_POST['hist_id'] ) ); $hch = sanitize_text_field( wp_unslash( $_POST['hist_ch'] ) );
				if ( isset( $hh[ $hid ] ) ) { $hh[ $hid ]['actions'][] = array( 'ch' => $hch, 'date' => current_time( 'd/m/Y H:i' ) ); ag_audit_hist_save( $hh ); $notice = '✅ Contact marqué : ' . $hch . ' — ' . ( $hh[ $hid ]['host'] ?? '' ); }
			}
			if ( isset( $_POST['ag_hist_del'], $_POST['hist_id'] ) ) {
				$hh = ag_audit_hist_get(); $hid = sanitize_text_field( wp_unslash( $_POST['hist_id'] ) );
				if ( isset( $hh[ $hid ] ) ) { $host = $hh[ $hid ]['host'] ?? ''; unset( $hh[ $hid ] ); ag_audit_hist_save( $hh ); $notice = '🗑️ Audit supprimé : ' . $host; }
			}
		}

		// Saisie manuelle d'un contact sur une fiche d'audit : on colle l'email/tél
		// trouvé (Insta/Google) → les boutons d'envoi (message déjà personnalisé
		// d'après le rapport) deviennent actifs, et le contact nourrit le CRM.
		if ( isset( $_POST['ag_hist_setcontact'], $_POST['hist_id'], $_POST['_agp_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) ) {
			$hh = ag_audit_hist_get(); $hid = sanitize_text_field( wp_unslash( $_POST['hist_id'] ) );
			if ( isset( $hh[ $hid ] ) ) {
				$hh[ $hid ]['email'] = sanitize_email( wp_unslash( $_POST['c_email'] ?? '' ) );
				$hh[ $hid ]['phone'] = sanitize_text_field( wp_unslash( $_POST['c_phone'] ?? '' ) );
				ag_audit_hist_save( $hh );
				$notice = 'Contact enregistre pour ' . ( $hh[ $hid ]['host'] ?? '' ) . ' - les boutons d envoi sont prets.';
			}
		}

		// Lancement des audits.
		$deep_mode = false;
		if ( isset( $_POST['ag_run_audits'], $_POST['_agp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) ) {
			$raw  = sanitize_textarea_field( wp_unslash( $_POST['urls'] ?? '' ) );
			$urls = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', $raw ) ) );
			$urls = array_slice( array_unique( $urls ), 0, 8 ); // cap anti-timeout
			$want_deep = ( 'deep' === ( $_POST['mode'] ?? 'passive' ) );
			// L'audit APPROFONDI (actif) exige l'attestation de mandat écrit.
			if ( $want_deep && empty( $_POST['mandat'] ) ) {
				$notice = '⛔ Audit approfondi refusé : cochez l\'attestation de mandat écrit du propriétaire. Sans mandat, seul l\'audit passif est autorisé.';
			} else {
				$deep_mode = $want_deep;
				foreach ( $urls as $u ) {
					$au = ( $deep_mode && function_exists( 'ag_audit_run_deep' ) ) ? ag_audit_run_deep( $u ) : ( function_exists( 'ag_audit_run' ) ? ag_audit_run( $u ) : null );
					if ( $au ) {
						$results[] = $au;
						ag_audit_hist_upsert( $au, function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $u ) : array(), $deep_mode ? 'deep' : 'passive' );
					}
				}
			}
		}
		?>
		<div class="wrap">
			<h1>🔍 Espace Audit — prospection</h1>
			<p>Colle des URLs de sites à démarcher (une par ligne, max 8). Diagnostic <strong>non-intrusif</strong> : on lit les pages publiques, on relève les failles, et on génère un message de démarchage prêt à envoyer. Pour <em>trouver</em> des prospects sans bon site, utilise aussi le menu <strong>Prospection → Chasse</strong>.</p>
			<?php if ( $notice ) echo '<div class="notice ' . ( 0 === strpos( $notice, '⛔' ) ? 'notice-error' : 'notice-success' ) . '"><p>' . esc_html( $notice ) . '</p></div>'; ?>

			<form method="post" style="margin:18px 0;max-width:760px">
				<?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
				<textarea name="urls" rows="6" style="width:100%;font-family:monospace" placeholder="https://site-prospect-1.fr&#10;https://site-prospect-2.fr"><?php echo isset( $_POST['urls'] ) ? esc_textarea( wp_unslash( $_POST['urls'] ) ) : ( isset( $_GET['prefill'] ) ? esc_textarea( esc_url_raw( wp_unslash( $_GET['prefill'] ) ) ) : '' ); ?></textarea>

				<fieldset style="margin:14px 0;border:1px solid #ccd0d4;border-radius:6px;padding:12px 16px">
					<legend style="font-weight:600;padding:0 6px">Type d'audit</legend>
					<label style="display:block;margin:4px 0"><input type="radio" name="mode" value="passive" checked> <strong>Passif</strong> — lecture des pages publiques. Légal sur tout site (prospects compris).</label>
					<label style="display:block;margin:4px 0"><input type="radio" name="mode" value="deep"> <strong>Approfondi (actif)</strong> — sondes supplémentaires (énumération, sauvegardes, versions de plugins…).</label>
					<label style="display:block;margin:10px 0 0;color:#b91c1c"><input type="checkbox" name="mandat" value="1"> ⚠️ Je certifie disposer d'une <strong>autorisation écrite (mandat)</strong> du propriétaire pour l'audit approfondi de ces sites.</label>
					<p style="color:#666;font-size:12px;margin:8px 0 0">L'audit approfondi sans mandat est un délit (art. 323-1 C. pénal). Pour des prospects non clients : reste en <strong>passif</strong>.</p>
				</fieldset>

				<p><button type="submit" name="ag_run_audits" value="1" class="button button-primary button-large">🔍 Lancer les audits</button></p>
			</form>

			<?php if ( $results ) :
				// Classe chaque prospect (sécurité vs création), puis trie : sécurité d'abord, plus faibles en tête.
				foreach ( $results as &$rr ) { $rr['_seg'] = ag_audit_segment( $rr ); } unset( $rr );
				usort( $results, function ( $x, $y ) {
					$ps = ( 'securite' === $x['_seg'] ) ? 0 : 1;
					$qs = ( 'securite' === $y['_seg'] ) ? 0 : 1;
					if ( $ps !== $qs ) return $ps <=> $qs;
					return (int) ( $x['score'] ?? 0 ) <=> (int) ( $y['score'] ?? 0 );
				} );
				$nb_sec  = count( array_filter( $results, function ( $r ) { return 'securite' === $r['_seg']; } ) );
				$nb_cre  = count( $results ) - $nb_sec;
				$nb_hot  = count( array_filter( $results, function ( $r ) { return ( (int) ( $r['score'] ?? 0 ) < 60 ) || ! empty( $r['critical'] ); } ) );
				$avg     = count( $results ) ? (int) round( array_sum( array_map( function ( $r ) { return (int) ( $r['score'] ?? 0 ); }, $results ) ) / count( $results ) ) : 0;
				$cur_seg = '';
				$st = 'background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:8px 14px;font-weight:600;font-size:13px';
				?>
				<h2>Résultats du démarchage<?php echo $deep_mode ? ' — <span style="color:#b91c1c">approfondi (mandat)</span>' : ''; ?></h2>
				<!-- STATS -->
				<div style="display:flex;gap:8px;flex-wrap:wrap;margin:6px 0 10px">
					<span style="<?php echo $st; ?>"><?php echo count( $results ); ?> prospects</span>
					<span style="<?php echo $st; ?>;color:#b91c1c">🛡️ <?php echo (int) $nb_sec; ?> sécurité</span>
					<span style="<?php echo $st; ?>;color:#1d4ed8">✨ <?php echo (int) $nb_cre; ?> création/SEO</span>
					<span style="<?php echo $st; ?>">🔥 <?php echo (int) $nb_hot; ?> chauds</span>
					<span style="<?php echo $st; ?>">⌀ score <?php echo (int) $avg; ?>/100</span>
				</div>
				<!-- OUTILS DE TRI -->
				<div style="margin:0 0 12px;display:flex;gap:6px;flex-wrap:wrap;align-items:center">
					<strong style="font-size:12px;color:#666">Filtrer :</strong>
					<button type="button" class="button button-small button-primary agp-filter" data-f="all">Tous</button>
					<button type="button" class="button button-small agp-filter" data-f="securite">🛡️ Sécurité</button>
					<button type="button" class="button button-small agp-filter" data-f="creation">✨ Création/SEO</button>
					<button type="button" class="button button-small agp-filter" data-f="hot">🔥 Chauds</button>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0 0 12px">
					<input type="hidden" name="action" value="ag_audit_export_csv">
					<?php wp_nonce_field( 'ag_audit_export' ); ?>
					<input type="hidden" name="urls" value="<?php echo esc_attr( isset( $_POST['urls'] ) ? wp_unslash( $_POST['urls'] ) : '' ); ?>">
					<input type="hidden" name="mode" value="<?php echo $deep_mode ? 'deep' : 'passive'; ?>">
					<input type="hidden" name="mandat" value="<?php echo $deep_mode ? '1' : ''; ?>">
					<button type="submit" class="button">📥 Exporter en CSV (prospects + coordonnées)</button>
				</form>
				<?php foreach ( $results as $i => $a ) :
					$url   = $a['url'] ?? '';
					$host  = wp_parse_url( $url, PHP_URL_HOST );
					$score = (int) ( $a['score'] ?? 0 );
					$col   = $score >= 75 ? '#1a7f37' : ( $score >= 50 ? '#bf6a02' : '#b91c1c' );
					$pb    = ag_audit_fail_list( $a );
					$hot   = ( $score < 60 || ! empty( $a['critical'] ) );
					$ct    = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $url ) : array();
					$email = $ct['emails'][0] ?? '';
					$phone = $ct['phones'][0] ?? '';
					$wa    = ag_wa_phone( $phone );

					// Lien personnalisé « commander l'audit complet » : on stocke un
					// transient comme le parcours public, le prospect voit SON teaser + paiement.
					$aid = wp_hash( $url . '|prospect|' . gmdate( 'Y-m-d' ) );
					set_transient( 'ag_tester_' . $aid, array(
						'audit' => $a, 'email' => $email, 'prenom' => ( $ct['company'] ?? '' ), 'phone' => $phone, 'unlocked' => false,
					), 30 * DAY_IN_SECONDS );
					$order_link = home_url( '/tester-mon-site?aid=' . $aid );

					$msg_sec  = ag_audit_msg_securite( $a, $ct, $order_link );
					$sms_sec  = ag_audit_sms_securite( $a, $order_link );
					$msg_crea = ag_audit_msg_creation( $a, $ct );
					$sms_crea = 'Bonjour, votre site ' . $host . ' gagnerait a etre modernise (affichage, mobile, referencement). Je cree des sites pro et securises des 490e. Fabrizio - Alliance Groupe. STOP pour stop.';
					$subj_sec = '⚠️ Faille de sécurité détectée sur ' . $host;
					$subj_cr  = 'Votre site ' . $host . ' — idées pour le moderniser';
					?>
					<?php if ( $a['_seg'] !== $cur_seg ) : $cur_seg = $a['_seg']; ?>
						<h3 class="agp-head" data-seg="<?php echo esc_attr( $cur_seg ); ?>" style="margin:30px 0 6px;padding-bottom:6px;border-bottom:2px solid <?php echo 'securite' === $cur_seg ? '#f3c2c2' : '#c2d9f3'; ?>">
							<?php echo 'securite' === $cur_seg ? '🛡️ Prospects SÉCURITÉ — failles à corriger (audit)' : '✨ Prospects CRÉATION / refonte — site à moderniser'; ?>
						</h3>
					<?php endif; ?>
					<div class="agp-card" data-seg="<?php echo esc_attr( $a['_seg'] ); ?>" data-score="<?php echo $score; ?>" data-hot="<?php echo $hot ? 1 : 0; ?>" style="background:#fff;border:1px solid #ccd0d4;border-left:5px solid <?php echo esc_attr( $col ); ?>;border-radius:8px;padding:16px 18px;margin:14px 0;max-width:1000px">
						<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
							<div>
								<strong style="font-size:15px"><?php echo esc_html( $ct['company'] ?: ( $host ?: $url ) ); ?></strong>
								<?php if ( $hot ) echo ' <span style="background:#b91c1c;color:#fff;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:700">🎯 PROSPECT CHAUD</span>'; ?>
								<br><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" style="font-size:12px"><?php echo esc_html( $url ); ?></a>
							</div>
							<div style="text-align:right">
								<span style="color:<?php echo esc_attr( $col ); ?>;font-size:22px;font-weight:800"><?php echo $score; ?>/100</span>
								<div style="font-size:12px;color:#666"><?php echo count( $pb ); ?> faille(s)<?php echo ! empty( $a['critical'] ) ? ' · <span style="color:#b91c1c">' . (int) $a['critical'] . ' critique(s)</span>' : ''; ?></div>
							</div>
						</div>

						<div style="font-size:12px;color:#444;margin:8px 0"><strong>Problèmes :</strong> <?php echo esc_html( implode( ' · ', array_slice( $pb, 0, 6 ) ) ); ?></div>

						<div style="font-size:13px;background:#f6f7f7;border-radius:6px;padding:8px 12px;margin:6px 0">
							<strong>📇 Coordonnées publiques :</strong>
							<?php echo $email ? '✉️ <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a> ' : ''; ?>
							<?php echo $phone ? '· 📞 ' . esc_html( $phone ) . ' ' : ''; ?>
							<?php echo ! empty( $ct['address'] ) ? '· 📍 ' . esc_html( $ct['address'] ) . ' ' : ''; ?>
							<?php echo ! empty( $ct['siret'] ) ? '· SIRET ' . esc_html( $ct['siret'] ) : ''; ?>
							<?php if ( ! $email && ! $phone ) echo '<em>aucune coordonnée trouvée sur le site</em>'; ?>
						</div>

						<!-- SEGMENT SÉCURITÉ -->
						<?php if ( 'securite' === $a['_seg'] ) : ?>
						<div style="border:1px solid #f3c2c2;background:#fff5f5;border-radius:6px;padding:10px 12px;margin:8px 0">
							<strong style="color:#b91c1c">🛡️ Démarchage SÉCURITÉ (alerte risque)</strong>
							<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">
								<?php if ( $email ) : ?><a class="button button-primary button-small" href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( $subj_sec ); ?>&body=<?php echo rawurlencode( $msg_sec ); ?>">✉️ Email sécurité</a><?php endif; ?>
								<?php if ( $phone ) : ?><a class="button button-small" href="tel:<?php echo esc_attr( preg_replace( '#\s#', '', $phone ) ); ?>">📞 Appeler</a><a class="button button-small" href="sms:<?php echo esc_attr( preg_replace( '#\s#', '', $phone ) ); ?>?&body=<?php echo rawurlencode( $sms_sec ); ?>">💬 SMS</a><?php if ( $wa ) : ?><a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( $sms_sec ); ?>">🟢 WhatsApp</a><?php endif; endif; ?>
									<button type="button" class="button button-small ag-report-btn" data-report="<?php echo esc_attr( wp_json_encode( ag_audit_report_payload( $a, $host, $order_link ) ) ); ?>" title="Image teaser du rapport (details masques) a joindre">📸 Image rapport</button>
								<button type="button" class="button button-small" onclick="var d=document.getElementById('ags<?php echo $i; ?>');d.style.display=d.style.display==='none'?'block':'none'">✏️ Voir/éditer</button>
								<form method="post" style="display:inline">
									<?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
									<input type="hidden" name="crm_url" value="<?php echo esc_attr( $url ); ?>"><input type="hidden" name="crm_score" value="<?php echo $score; ?>">
									<input type="hidden" name="crm_name" value="<?php echo esc_attr( $ct['company'] ?? '' ); ?>"><input type="hidden" name="crm_email" value="<?php echo esc_attr( $email ); ?>">
									<input type="hidden" name="crm_phone" value="<?php echo esc_attr( $phone ); ?>"><input type="hidden" name="crm_addr" value="<?php echo esc_attr( $ct['address'] ?? '' ); ?>">
									<input type="hidden" name="crm_vulns" value="<?php echo esc_attr( implode( ', ', array_slice( $pb, 0, 6 ) ) ); ?>"><input type="hidden" name="crm_segment" value="securite">
									<button type="submit" name="ag_add_crm" value="1" class="button button-small">➕ Prospect Sécurité</button>
									<button type="submit" name="ag_send_tg" value="1" class="button button-small">📲 Telegram</button>
								</form>
							</div>
							<div id="ags<?php echo $i; ?>" style="display:none;margin-top:8px">
								<p style="margin:4px 0;font-size:12px;color:#666">Email sécurité (lien commande inclus) :</p>
								<textarea rows="12" style="width:100%;max-width:660px;font-size:12px" onclick="this.select()"><?php echo esc_textarea( $msg_sec ); ?></textarea>
								<p style="margin:8px 0 4px;font-size:12px;color:#666">SMS / WhatsApp :</p>
								<textarea rows="3" style="width:100%;max-width:660px;font-size:12px" onclick="this.select()"><?php echo esc_textarea( $sms_sec ); ?></textarea>
								<p style="margin:6px 0 0;font-size:11px;color:#888">Lien commande : <code><?php echo esc_html( $order_link ); ?></code></p>
							</div>
						</div>
						<?php endif; ?>

						<!-- SEGMENT CRÉATION -->
						<?php if ( 'creation' === $a['_seg'] ) : ?>
						<div style="border:1px solid #c2d9f3;background:#f5f9ff;border-radius:6px;padding:10px 12px;margin:8px 0">
							<strong style="color:#1d4ed8">✨ Démarchage CRÉATION (refonte / site moderne)</strong>
							<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">
								<?php if ( $email ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( $subj_cr ); ?>&body=<?php echo rawurlencode( $msg_crea ); ?>">✉️ Email création</a><?php endif; ?>
								<?php if ( $phone ) : ?><a class="button button-small" href="tel:<?php echo esc_attr( preg_replace( '#\s#', '', $phone ) ); ?>">📞 Appeler</a><a class="button button-small" href="sms:<?php echo esc_attr( preg_replace( '#\s#', '', $phone ) ); ?>?&body=<?php echo rawurlencode( $sms_crea ); ?>">💬 SMS</a><?php if ( $wa ) : ?><a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( $sms_crea ); ?>">🟢 WhatsApp</a><?php endif; endif; ?>
								<button type="button" class="button button-small" onclick="var d=document.getElementById('agc<?php echo $i; ?>');d.style.display=d.style.display==='none'?'block':'none'">✏️ Voir/éditer</button>
								<form method="post" style="display:inline">
									<?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
									<input type="hidden" name="crm_url" value="<?php echo esc_attr( $url ); ?>"><input type="hidden" name="crm_score" value="<?php echo $score; ?>">
									<input type="hidden" name="crm_name" value="<?php echo esc_attr( $ct['company'] ?? '' ); ?>"><input type="hidden" name="crm_email" value="<?php echo esc_attr( $email ); ?>">
									<input type="hidden" name="crm_phone" value="<?php echo esc_attr( $phone ); ?>"><input type="hidden" name="crm_addr" value="<?php echo esc_attr( $ct['address'] ?? '' ); ?>">
									<input type="hidden" name="crm_vulns" value="<?php echo esc_attr( implode( ', ', array_slice( $pb, 0, 6 ) ) ); ?>"><input type="hidden" name="crm_segment" value="creation">
									<button type="submit" name="ag_add_crm" value="1" class="button button-small">➕ Prospect Création</button>
									<button type="submit" name="ag_send_tg" value="1" class="button button-small">📲 Telegram</button>
								</form>
							</div>
							<div id="agc<?php echo $i; ?>" style="display:none;margin-top:8px">
								<textarea rows="9" style="width:100%;max-width:660px;font-size:12px" onclick="this.select()"><?php echo esc_textarea( $msg_crea ); ?></textarea>
							</div>
						</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<script>
				(function(){
					var cards = [].slice.call(document.querySelectorAll('.agp-card'));
					var heads = [].slice.call(document.querySelectorAll('.agp-head'));
					function apply(f){
						cards.forEach(function(c){
							var seg = c.getAttribute('data-seg'), hot = c.getAttribute('data-hot') === '1';
							var show = ( f === 'all' ) || ( f === 'hot' ? hot : seg === f );
							c.style.display = show ? '' : 'none';
						});
						heads.forEach(function(h){
							var seg = h.getAttribute('data-seg');
							var any = cards.some(function(c){ return c.getAttribute('data-seg') === seg && c.style.display !== 'none'; });
							h.style.display = any ? '' : 'none';
						});
					}
					document.querySelectorAll('.agp-filter').forEach(function(b){
						b.addEventListener('click', function(){
							document.querySelectorAll('.agp-filter').forEach(function(x){ x.classList.remove('button-primary'); });
							b.classList.add('button-primary');
							apply(b.getAttribute('data-f'));
						});
					});
				})();
				</script>
				<p style="color:#666;font-size:12px;margin-top:10px">⚖️ Coordonnées extraites des pages publiques du site (info éditée par l'entreprise). Démarchage : B2B autorisé avec opt-out (le message inclut « STOP ») ; respecte <strong>Bloctel</strong> pour le téléphone. Aucune donnée issue de fuites/bases tierces.</p>
			<?php endif; ?>
			<?php $AGH = ag_audit_hist_get();
			if ( $AGH ) :
				uasort( $AGH, function ( $x, $y ) { return (int) ( $y['ts'] ?? 0 ) <=> (int) ( $x['ts'] ?? 0 ); } );
				// Hôtes venus via le formulaire public « Tester mon site » (= demande client),
				// pour marquer rétroactivement les anciens audits sans champ src.
				$ag_inbound_hosts = array();
				foreach ( (array) get_option( 'ag_prospects', array() ) as $ag_p ) {
					if ( false !== strpos( (string) ( $ag_p['source'] ?? '' ), 'tester-mon-site' ) ) {
						$ag_ph = wp_parse_url( (string) ( $ag_p['website'] ?? '' ), PHP_URL_HOST );
						if ( $ag_ph ) { $ag_inbound_hosts[ strtolower( $ag_ph ) ] = 1; }
					}
				}
				$ag_src_of = function ( $e ) use ( $ag_inbound_hosts ) {
					$st = $e['src'] ?? '';
					if ( 'inbound' === $st || 'auto' === $st ) { return $st; }
					return isset( $ag_inbound_hosts[ strtolower( (string) ( $e['host'] ?? '' ) ) ] ) ? 'inbound' : 'self';
				};
				$ag_seen_ts = (int) get_option( 'ag_audit_seen_ts', 0 );
				$ag_new_count = 0; $ag_inbound_count = 0;
				foreach ( $AGH as $ag_e ) {
					if ( (int) ( $ag_e['ts'] ?? 0 ) > $ag_seen_ts ) { $ag_new_count++; }
					if ( 'inbound' === $ag_src_of( $ag_e ) ) { $ag_inbound_count++; }
				}
				?>
				<hr style="margin:30px 0">
				<h2>📋 Historique des audits (<?php echo count( $AGH ); ?>) — sauvegardés<?php if ( $ag_new_count ) : ?> <span style="background:#f59e0b;color:#111;border-radius:6px;padding:2px 10px;font-size:14px;vertical-align:middle">🆕 <?php echo (int) $ag_new_count; ?> nouveau<?php echo $ag_new_count > 1 ? 'x' : ''; ?></span><?php endif; ?><?php if ( $ag_inbound_count ) : ?> <span style="background:#0a7d3c;color:#fff;border-radius:6px;padding:2px 10px;font-size:14px;vertical-align:middle">🔥 <?php echo (int) $ag_inbound_count; ?> demande<?php echo $ag_inbound_count > 1 ? 's' : ''; ?> client<?php echo $ag_inbound_count > 1 ? 's' : ''; ?></span><?php endif; ?></h2>
				<p style="color:#666;font-size:12px;margin:0 0 8px">Chaque audit est conservé : fiche (failles + score), coordonnées, et suivi des contacts. Filtre :
					<button type="button" class="button button-small button-primary aghf" data-f="all">Tous</button>
					<button type="button" class="button button-small aghf" data-f="securite">🛡️ Sécurité</button>
					<button type="button" class="button button-small aghf" data-f="creation">✨ Création/SEO</button>
					<button type="button" class="button button-small aghf" data-f="todo">⏳ Pas encore contactés</button>
					<span style="margin:0 4px;color:#ccc">|</span>
					<strong style="font-size:12px;color:#666">Type de test :</strong>
					<button type="button" class="button button-small button-primary aghft" data-m="all">Tous</button>
					<button type="button" class="button button-small aghft" data-m="passive">🔍 Léger</button>
					<button type="button" class="button button-small aghft" data-m="deep">🔬 Approfondi</button>
					<button type="button" class="button button-small aghft" data-m="expert">🛰️ Expert (Kali)</button>
					<span style="margin:0 4px;color:#ccc">|</span>
					<strong style="font-size:12px;color:#666">Origine :</strong>
					<button type="button" class="button button-small button-primary aghsrc" data-o="all">Tous</button>
					<button type="button" class="button button-small aghsrc" data-o="inbound">🔥 Demandes clients</button>
					<button type="button" class="button button-small aghsrc" data-o="auto">🤖 Agent auto</button>
					<button type="button" class="button button-small aghsrc" data-o="self">🔍 Mes audits (manuel)</button>
				</p>
					<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin:6px 0 10px">
						<strong style="font-size:12px;color:#666">Trier :</strong>
						<button type="button" class="button button-small aghs" data-s="ts-desc">🕒 Récents</button>
						<button type="button" class="button button-small aghs" data-s="ts-asc">🕒 Anciens</button>
						<button type="button" class="button button-small aghs" data-s="score-asc">⬇️ Score faible (chaud)</button>
						<button type="button" class="button button-small aghs" data-s="score-desc">⬆️ Score élevé</button>
							<button type="button" class="button button-small aghs" data-s="host">🏢 Par site (regroupe sécurité + création)</button>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;margin-left:auto" onsubmit="return confirm('Réauditer TOUS les sites de l\'historique avec l\'algo à jour ? (recalcule notes, messages et images. Peut prendre 1-2 min)')"><input type="hidden" name="action" value="ag_audit_hist_rescan"><?php wp_nonce_field( 'ag_audit_hist_rescan' ); ?><button type="submit" class="button button-small button-primary">🔄 Réauditer tout (recalcul notes)</button></form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline"><input type="hidden" name="action" value="ag_audit_hist_csv"><?php wp_nonce_field( 'ag_audit_hist_csv' ); ?><button type="submit" class="button button-small">📥 Exporter l'historique (CSV)</button></form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Vider TOUT l\'historique des audits ? Action irreversible.')"><input type="hidden" name="action" value="ag_audit_hist_clear"><?php wp_nonce_field( 'ag_audit_hist_clear' ); ?><button type="submit" class="button button-small" style="color:#b91c1c;margin-left:6px">🗑️ Tout effacer</button></form>
					</div>
					<?php $ahnonce = wp_create_nonce( 'ag_audit_hist_bulk' ); ?>
					<div style="margin:0 0 10px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
						<label style="font-size:12px;"><input type="checkbox" id="aghc-all"> Tout sélectionner</label>
						<button type="button" id="aghc-del" class="button button-small button-link-delete" disabled>🗑️ Supprimer la sélection (<span id="aghc-cnt">0</span>)</button>
					</div>
					<div id="agh-list">
				<?php foreach ( $AGH as $hid => $e ) :
					$score = (int) $e['score']; $col = $score >= 75 ? '#1a7f37' : ( $score >= 50 ? '#bf6a02' : '#b91c1c' ); $seg = $e['seg'];
						$mode = in_array( $e['mode'] ?? 'passive', array( 'deep', 'expert' ), true ) ? $e['mode'] : 'passive';
						$mode_lbl = 'expert' === $mode ? '🛰️ Expert (Kali)' : ( 'deep' === $mode ? '🔬 Approfondi' : '🔍 Léger (passif)' );
					$host = $e['host']; $url = $e['url']; $email = $e['email']; $phone = $e['phone']; $wa = ag_wa_phone( $phone );
					$fails = array_map( function ( $c ) { return $c['name']; }, $e['checks'] );
					$ncrit = 0; foreach ( $e['checks'] as $c ) { if ( ! empty( $c['critical'] ) && 'fail' === $c['status'] ) $ncrit++; }
					// Sépare sécurité / SEO pour un libellé clair (évite la confusion « 8 failles » vs image sécurité).
					$a_split   = array( 'checks' => $e['checks'] );
					list( $S_f, $O_f ) = function_exists( 'ag_audit_split_fails' ) ? ag_audit_split_fails( $a_split ) : array( $e['checks'], array() );
					$nb_sec_f  = count( $S_f ); $nb_seo_f = count( $O_f );
					$a = array( 'url' => $url, 'score' => $score, 'critical' => $e['critical'], 'checks' => $e['checks'] );
					$ct = array( 'emails' => $email ? array( $email ) : array(), 'phones' => $phone ? array( $phone ) : array(), 'address' => $e['address'], 'siret' => $e['siret'], 'company' => $e['company'], 'socials' => $e['socials'] );
					$aid = wp_hash( $url . '|prospect|' . gmdate( 'Y-m-d' ) ); set_transient( 'ag_tester_' . $aid, array( 'audit' => $a, 'email' => $email, 'prenom' => $e['company'], 'phone' => $phone, 'unlocked' => false ), 30 * DAY_IN_SECONDS );
					$ol = home_url( '/tester-mon-site?aid=' . $aid );
					if ( 'securite' === $seg ) { $emsg = ag_audit_msg_securite( $a, $ct, $ol ); $smsg = ag_audit_sms_securite( $a, $ol ); $subj = '⚠️ Faille de sécurité détectée sur ' . $host; }
					else { $emsg = ag_audit_msg_creation( $a, $ct ); $smsg = 'Bonjour, votre site ' . $host . ' gagnerait a etre modernise. Je cree des sites pro securises des 490e. Fabrizio-Alliance Groupe. STOP pour stop.'; $subj = 'Votre site ' . $host . ' — idées pour le moderniser'; }
						$sec_emsg  = ag_audit_msg_securite( $a, $ct, $ol );
						$sec_smsg  = ag_audit_sms_securite( $a, $ol );
						$sec_subj  = 'Faille de securite detectee sur ' . $host;
						$crea_emsg = ag_audit_msg_creation( $a, $ct );
						$crea_smsg = 'Bonjour, votre site ' . $host . ' gagnerait a etre modernise (design, vitesse, mobile). Je cree des sites pro securises des 490e. Fabrizio - Alliance Groupe. STOP pour stop.';
						$crea_subj = 'Votre site ' . $host . ' - idees pour le moderniser';
					$done = array(); foreach ( ( $e['actions'] ?? array() ) as $ac ) { $done[ $ac['ch'] ] = $ac['date']; }
					$todo = empty( $e['actions'] );
					$src = $ag_src_of( $e );
					$is_new = ( (int) ( $e['ts'] ?? 0 ) > $ag_seen_ts );
					?>
					<div class="agh-card" data-seg="<?php echo esc_attr( $seg ); ?>" data-mode="<?php echo esc_attr( $mode ); ?>" data-host="<?php echo esc_attr( $host ); ?>" data-todo="<?php echo $todo ? 1 : 0; ?>" data-src="<?php echo esc_attr( $src ); ?>" data-new="<?php echo $is_new ? 1 : 0; ?>" data-score="<?php echo $score; ?>" data-ts="<?php echo (int) $e['ts']; ?>" style="background:#fff;border:1px solid #ccd0d4;border-left:5px solid <?php echo esc_attr( $col ); ?>;border-radius:8px;padding:14px 16px;margin:12px 0;max-width:1000px">
						<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">
							<div><label style="margin-right:8px;"><input type="checkbox" class="aghc-chk" value="<?php echo esc_attr( $hid ); ?>" title="Sélectionner"></label><strong><?php echo esc_html( $e['company'] ?: $host ); ?></strong>
								<span style="background:<?php echo 'securite' === $seg ? '#b91c1c' : '#1d4ed8'; ?>;color:#fff;border-radius:4px;padding:1px 6px;font-size:11px"><?php echo 'securite' === $seg ? 'SÉCURITÉ' : 'CRÉATION'; ?></span><?php if ( 'inbound' === $src ) : ?> <span style="background:#0a7d3c;color:#fff;border-radius:4px;padding:1px 7px;font-size:11px;font-weight:700">🔥 DEMANDE CLIENT</span><?php elseif ( 'auto' === $src ) : ?> <span style="background:#2563eb;color:#fff;border-radius:4px;padding:1px 7px;font-size:11px;font-weight:700">🤖 AGENT AUTO</span><?php else : ?> <span style="background:#6b7280;color:#fff;border-radius:4px;padding:1px 7px;font-size:11px">🔍 Mon audit (manuel)</span><?php endif; ?><?php if ( $is_new ) : ?> <span style="background:#f59e0b;color:#111;border-radius:4px;padding:1px 7px;font-size:11px;font-weight:800">🆕 NOUVEAU</span><?php endif; ?>
									<span style="background:<?php echo 'expert' === $mode ? '#0a6' : ( 'deep' === $mode ? '#6d28d9' : '#0e7490' ); ?>;color:#fff;border-radius:4px;padding:1px 6px;font-size:11px"><?php echo esc_html( $mode_lbl ); ?></span>
								<br><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" style="font-size:12px"><?php echo esc_html( $host ); ?></a></div>
							<div style="text-align:right"><span style="color:<?php echo esc_attr( $col ); ?>;font-size:20px;font-weight:800"><?php echo $score; ?>/100</span>
								<div style="font-size:11px;color:#666"><strong style="color:#b91c1c"><?php echo (int) $nb_sec_f; ?> sécurité</strong> + <?php echo (int) $nb_seo_f; ?> SEO<?php echo $ncrit ? ' · <span style="color:#b91c1c">' . (int) $ncrit . ' critique(s)</span>' : ''; ?> · <?php echo esc_html( wp_date( 'd/m/Y H:i', (int) $e['ts'] ) ); ?></div></div>
						</div>
						<div style="font-size:12px;color:#444;margin:6px 0"><strong>Failles :</strong> <?php echo esc_html( implode( ' · ', array_slice( $fails, 0, 8 ) ) ); ?></div>
						<?php if ( 'deep' === $mode && ! empty( $e['deep_checks'] ) ) : ?>
							<div style="font-size:12px;background:#f0f6ff;border:1px solid #bfdbfe;border-radius:6px;padding:6px 10px;margin:4px 0">
								<strong style="color:#6d28d9">🔬 Vérifications approfondies en plus (que le test léger ne fait pas) :</strong>
								<?php foreach ( $e['deep_checks'] as $dc ) :
									$ok = ( 'ok' === ( $dc['status'] ?? '' ) ); ?>
									<span style="display:inline-block;margin:2px 4px 0 0;padding:1px 7px;border-radius:10px;font-size:11px;background:<?php echo $ok ? '#dcfce7' : '#fee2e2'; ?>;color:<?php echo $ok ? '#166534' : '#b91c1c'; ?>"><?php echo $ok ? '✅' : '❌'; ?> <?php echo esc_html( $dc['name'] ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<div style="font-size:12px;background:#f6f7f7;border-radius:6px;padding:6px 10px;margin:4px 0">📇
							<?php echo $email ? '✉️ ' . esc_html( $email ) . ' ' : ''; ?><?php echo $phone ? '· 📞 ' . esc_html( $phone ) . ' ' : ''; ?><?php echo $e['address'] ? '· 📍 ' . esc_html( $e['address'] ) . ' ' : ''; ?><?php echo $e['siret'] ? '· SIRET ' . esc_html( $e['siret'] ) . ' ' : ''; ?>
							<?php foreach ( ( $e['socials'] ?? array() ) as $so ) { echo '· <a href="' . esc_url( $so ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '#https?://(www\.)?#', '', $so ) ) . '</a> '; } ?>
							<?php if ( ! $email && ! $phone ) echo '<em>aucune coordonnée</em>'; ?>
						</div>
						<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:6px">
							<?php if ( $email ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( $subj ); ?>&body=<?php echo rawurlencode( $emsg ); ?>">✉️ Email</a><?php endif; ?>
							<?php if ( $phone ) : ?><a class="button button-small" href="tel:<?php echo esc_attr( preg_replace( '#\s#', '', $phone ) ); ?>">📞</a><a class="button button-small" href="sms:<?php echo esc_attr( preg_replace( '#\s#', '', $phone ) ); ?>?&body=<?php echo rawurlencode( $smsg ); ?>">💬 SMS</a><?php if ( $wa ) : ?><a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( $smsg ); ?>">🟢 WA</a><?php endif; endif; ?>
							<?php if ( $email || $phone ) : $pn2 = preg_replace( '#\s#', '', (string) $phone );
									$o_e = ( 'securite' === $seg ) ? $crea_emsg : $sec_emsg;
									$o_s = ( 'securite' === $seg ) ? $crea_smsg : $sec_smsg;
									$o_sub = ( 'securite' === $seg ) ? $crea_subj : $sec_subj;
									$o_lbl = ( 'securite' === $seg ) ? '✨ Aussi Création' : '🛡️ Aussi Sécurité'; ?>
									<span style="display:inline-flex;gap:5px;align-items:center;background:#f3f4f6;border:1px solid #d1d5db;border-radius:6px;padding:3px 7px"><b style="font-size:11px"><?php echo $o_lbl; // phpcs:ignore ?></b>
										<?php if ( $email ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( $o_sub ); ?>&body=<?php echo rawurlencode( $o_e ); ?>">✉️</a><?php endif; ?>
										<?php if ( $phone ) : ?><a class="button button-small" href="sms:<?php echo esc_attr( $pn2 ); ?>?&body=<?php echo rawurlencode( $o_s ); ?>">💬</a><?php if ( $wa ) : ?><a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( $o_s ); ?>">🟢</a><?php endif; endif; ?>
									</span>
								<?php endif; ?>
								<button type="button" class="button button-small" onclick="var d=document.getElementById('aghf<?php echo esc_attr( $hid ); ?>');d.style.display=d.style.display==='none'?'block':'none'">📄 Fiche</button>
								<button type="button" class="button button-small ag-report-btn" data-report="<?php echo esc_attr( wp_json_encode( ag_audit_report_payload( $a, $host, $ol ) ) ); ?>" title="Genere une image teaser (rapport leger, details masques) a joindre au message">📸 Image rapport</button>
							<form method="post" style="display:inline"><?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
								<input type="hidden" name="hist_id" value="<?php echo esc_attr( $hid ); ?>">
								<select name="hist_ch" style="font-size:12px;max-width:130px"><option>Email</option><option>SMS</option><option>WhatsApp</option><option>Telegram</option><option>Appel</option><option>Pas de réponse</option><option>Intéressé</option><option>Refusé</option></select>
								<button type="submit" name="ag_hist_mark" value="1" class="button button-small">✓ Noté</button>
							</form>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Supprimer cet audit ?')"><input type="hidden" name="action" value="ag_audit_hist_del"><?php wp_nonce_field( 'ag_audit_hist_del' ); ?><input type="hidden" name="hist_id" value="<?php echo esc_attr( $hid ); ?>"><button type="submit" class="button button-small" style="color:#b91c1c">🗑️ Suppr.</button></form>
						</div>
						<?php if ( ! $email || ! $phone ) : ?>
							<div style="margin-top:6px">
								<button type="button" class="button button-small" onclick="var d=document.getElementById('agcc<?php echo esc_attr( $hid ); ?>');d.style.display=d.style.display==='none'?'flex':'none'">➕ Saisir / corriger le contact (pour pouvoir envoyer)</button>
								<form method="post" id="agcc<?php echo esc_attr( $hid ); ?>" style="display:none;gap:6px;flex-wrap:wrap;align-items:center;margin-top:6px">
									<?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
									<input type="hidden" name="hist_id" value="<?php echo esc_attr( $hid ); ?>">
									<input type="email" name="c_email" value="<?php echo esc_attr( $email ); ?>" placeholder="email@client.fr" style="font-size:12px">
									<input type="text" name="c_phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="06 12 34 56 78" style="font-size:12px">
									<button type="submit" name="ag_hist_setcontact" value="1" class="button button-small button-primary">💾 Enregistrer → active SMS / WhatsApp / Email</button>
								</form>
							</div>
							<?php endif; ?>
							<?php $crm = ag_audit_prospect_by_site( $url ); if ( $crm ) :
							$ce = ! empty( $crm['email'] ) ? $crm['email'] : $email;
							$cp = ! empty( $crm['phone'] ) ? $crm['phone'] : $phone;
							$cpn = preg_replace( '#\s#', '', (string) $cp ); $cwa = ag_wa_phone( $cp );
							$sec_msg2 = ag_audit_msg_securite( $a, $ct, $ol ); $sec_sms2 = ag_audit_sms_securite( $a, $ol );
							$crea_msg = ag_audit_msg_creation( $a, $ct );
							$crea_sms = 'Bonjour, votre site ' . $host . ' gagnerait a etre modernise (design, vitesse, mobile). Je cree des sites pro securises des 490e. Fabrizio - Alliance Groupe. STOP pour stop.';
							$sec_subj2 = '⚠️ Faille de sécurité détectée sur ' . $host; $crea_subj = 'Votre site ' . $host . ' — idées pour le moderniser';
							?>
							<div style="margin-top:8px;background:#fff7ed;border:1px solid #fdba74;border-radius:6px;padding:8px 10px">
								<div style="font-size:12px;font-weight:700;color:#9a3412">🔗 Aussi dans tes prospects à démarcher<?php echo ! empty( $crm['name'] ) ? ' : ' . esc_html( $crm['name'] ) : ''; ?> — envoie <u>2 messages</u> (sécurité + site)</div>
								<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:6px">
									<span style="font-size:11px;color:#b91c1c;font-weight:700">🛡️ Sécu :</span>
									<?php if ( $ce ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $ce ); ?>?subject=<?php echo rawurlencode( $sec_subj2 ); ?>&body=<?php echo rawurlencode( $sec_msg2 ); ?>">✉️ Email</a><?php endif; ?>
									<?php if ( $cpn ) : ?><a class="button button-small" href="sms:<?php echo esc_attr( $cpn ); ?>?&body=<?php echo rawurlencode( $sec_sms2 ); ?>">💬 SMS</a><?php if ( $cwa ) : ?><a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $cwa ); ?>?text=<?php echo rawurlencode( $sec_sms2 ); ?>">🟢 WA</a><?php endif; endif; ?>
								</div>
								<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:4px">
									<span style="font-size:11px;color:#1d4ed8;font-weight:700">✨ Site :</span>
									<?php if ( $ce ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $ce ); ?>?subject=<?php echo rawurlencode( $crea_subj ); ?>&body=<?php echo rawurlencode( $crea_msg ); ?>">✉️ Email</a><?php endif; ?>
									<?php if ( $cpn ) : ?><a class="button button-small" href="sms:<?php echo esc_attr( $cpn ); ?>?&body=<?php echo rawurlencode( $crea_sms ); ?>">💬 SMS</a><?php if ( $cwa ) : ?><a class="button button-small" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $cwa ); ?>?text=<?php echo rawurlencode( $crea_sms ); ?>">🟢 WA</a><?php endif; endif; ?>
									<button type="button" class="button button-small" onclick="var d=document.getElementById('aghd<?php echo esc_attr( $hid ); ?>');d.style.display=d.style.display==='none'?'block':'none'">✏️ Voir/éditer les 2</button>
								</div>
								<div id="aghd<?php echo esc_attr( $hid ); ?>" style="display:none;margin-top:6px">
									<p style="font-size:11px;color:#b91c1c;font-weight:700;margin:4px 0 2px">🛡️ Message sécurité :</p>
									<textarea readonly onclick="this.select()" style="width:100%;height:120px;font-size:11px"><?php echo esc_textarea( $sec_msg2 ); ?></textarea>
									<p style="font-size:11px;color:#1d4ed8;font-weight:700;margin:6px 0 2px">✨ Message site/création :</p>
									<textarea readonly onclick="this.select()" style="width:100%;height:120px;font-size:11px"><?php echo esc_textarea( $crea_msg ); ?></textarea>
									<?php $gmsg = ag_audit_msg_global( $a, $ct ); ?>
									<p style="font-size:11px;color:#7c3aed;font-weight:700;margin:8px 0 2px">🎯 Message GLOBAL (securite + site, un seul) :</p>
									<textarea readonly onclick="this.select()" style="width:100%;height:140px;font-size:11px;border:1px solid #c4b5fd"><?php echo esc_textarea( $gmsg ); ?></textarea>
									<div style="margin-top:4px;display:flex;gap:6px;flex-wrap:wrap">
										<?php if ( $cpn ) : ?><a class="button button-small button-primary" href="sms:<?php echo esc_attr( $cpn ); ?>?&body=<?php echo rawurlencode( $gmsg ); ?>">📱 SMS global</a><?php if ( $cwa ) : ?><a class="button button-small button-primary" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $cwa ); ?>?text=<?php echo rawurlencode( $gmsg ); ?>">🟢 WhatsApp global</a><?php endif; endif; ?>
										<?php if ( $ce ) : ?><a class="button button-small" href="mailto:<?php echo esc_attr( $ce ); ?>?subject=<?php echo rawurlencode( 'Votre site ' . $host . ' : securite + modernisation' ); ?>&body=<?php echo rawurlencode( $gmsg ); ?>">✉️ Email global</a><?php endif; ?>
									</div>
									<?php $ag_kali = ag_tester_kali_report_for( $url ); if ( $ag_kali ) : ?>
									<details style="margin-top:8px;border:1px solid #0a6;border-radius:6px;background:#f3fff9;padding:6px 10px" open>
										<summary style="cursor:pointer;font-size:11px;color:#093;font-weight:700">🛰️ Rapport Diagnostic Expert 24h — synthèse</summary>
										<div style="font-size:12px;margin:6px 0 0"><?php echo function_exists( 'ag_pt_summary_fr_html' ) ? ag_pt_summary_fr_html( $ag_kali ) : ''; ?></div>
										<details style="margin-top:6px"><summary style="cursor:pointer;font-size:10px;color:#888">Texte brut (technique, EN)</summary>
											<pre style="white-space:pre-wrap;max-height:240px;overflow:auto;background:#0d1117;color:#c9d1d9;padding:10px;border-radius:6px;font-size:10px;margin:6px 0 0"><?php echo esc_html( $ag_kali ); ?></pre>
										</details>
									</details>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( $done ) : ?><div style="font-size:12px;margin-top:6px">✅ <strong>Contacté :</strong> <?php foreach ( $done as $ch => $dt ) { echo '<span style="background:#eef;border-radius:4px;padding:1px 6px;margin-right:4px">' . esc_html( $ch ) . ' (' . esc_html( $dt ) . ')</span>'; } ?></div><?php endif; ?>
						<div id="aghf<?php echo esc_attr( $hid ); ?>" style="display:none;margin-top:8px">
							<strong style="font-size:12px">Toutes les failles :</strong>
							<ul style="font-size:12px;margin:4px 0 8px">
								<?php foreach ( $e['checks'] as $c ) { $ic = ( 'fail' === $c['status'] && ! empty( $c['critical'] ) ) ? '🔴' : '🟠'; echo '<li>' . $ic . ' ' . esc_html( $c['name'] ) . '</li>'; } ?>
							</ul>
							<p style="font-size:12px;color:#666;margin:0 0 4px">Message (éditable) :</p>
							<textarea rows="8" style="width:100%;max-width:660px;font-size:12px" onclick="this.select()"><?php echo esc_textarea( $emsg ); ?></textarea>
						</div>
					</div>
				<?php endforeach; update_option( 'ag_audit_seen_ts', time(), false ); ?>
				</div>
				<script>
				(function(){var list=document.getElementById('agh-list');if(!list)return;var curF='all',curM='all',curO='all';function cards(){return [].slice.call(list.querySelectorAll('.agh-card'));}function apply(){cards().forEach(function(k){var seg=k.getAttribute('data-seg'),todo=k.getAttribute('data-todo')==='1',md=k.getAttribute('data-mode')||'passive';var okF=(curF==='all')||(curF==='todo'?todo:seg===curF);var okM=(curM==='all')||(md===curM);var src=k.getAttribute('data-src')||'self';var okO=(curO==='all')||(src===curO);k.style.display=(okF&&okM&&okO)?'':'none';});}document.querySelectorAll('.aghf').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.aghf').forEach(function(x){x.classList.remove('button-primary')});b.classList.add('button-primary');curF=b.getAttribute('data-f');apply();});});document.querySelectorAll('.aghft').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.aghft').forEach(function(x){x.classList.remove('button-primary')});b.classList.add('button-primary');curM=b.getAttribute('data-m');apply();});});document.querySelectorAll('.aghsrc').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.aghsrc').forEach(function(x){x.classList.remove('button-primary')});b.classList.add('button-primary');curO=b.getAttribute('data-o');apply();});});document.querySelectorAll('.aghs').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.aghs').forEach(function(x){x.classList.remove('button-primary')});b.classList.add('button-primary');var sp=b.getAttribute('data-s').split('-'),key=sp[0],dir=sp[1];var arr=cards();arr.sort(function(a,c){if(key==='host'){var ha=(a.getAttribute('data-host')||''),hc=(c.getAttribute('data-host')||'');if(ha!==hc)return ha<hc?-1:1;return (+c.getAttribute('data-score'))-(+a.getAttribute('data-score'));}var x=+a.getAttribute('data-'+key),y=+c.getAttribute('data-'+key);return dir==='asc'?x-y:y-x;});arr.forEach(function(k){list.appendChild(k);});});});})();
				</script>
				<script>
				(function(){var n=<?php echo wp_json_encode( $ahnonce ); ?>;
				var all=document.getElementById('aghc-all'),del=document.getElementById('aghc-del'),cnt=document.getElementById('aghc-cnt');
				function it(){return [].slice.call(document.querySelectorAll('.aghc-chk'));}
				function rf(){var c=it().filter(function(x){return x.checked;}).length;if(cnt)cnt.textContent=c;if(del)del.disabled=(c===0);}
				if(all)all.addEventListener('change',function(){it().forEach(function(x){x.checked=all.checked;});rf();});
				it().forEach(function(x){x.addEventListener('change',rf);});
				if(del)del.addEventListener('click',function(){var ids=it().filter(function(x){return x.checked;}).map(function(x){return x.value;});if(!ids.length)return;if(!confirm('Supprimer définitivement '+ids.length+' audit(s) ?'))return;var fd=new FormData();fd.append('action','ag_audit_hist_delete_bulk');fd.append('_n',n);ids.forEach(function(i){fd.append('ids[]',i);});del.disabled=true;del.textContent='…';fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){location.reload();}else{del.disabled=false;alert('Erreur');}}).catch(function(){del.disabled=false;});});
				})();
				</script>
			<?php endif; ?>

		<!-- ====== Générateur d'IMAGE rapport teaser (sécurité, détails masqués) ====== -->
		<div id="ag-report-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
			<div style="background:#fff;border-radius:12px;max-width:560px;width:100%;max-height:92vh;overflow:auto;padding:18px;text-align:center">
				<h3 style="margin:0 0 6px">📸 Image rapport (à joindre au message)</h3>
				<p style="font-size:12px;color:#555;margin:0 0 10px">Rapport <strong>léger</strong> : on montre la nature des failles et le score, mais <strong>l'emplacement et le correctif restent masqués</strong> (réservés au rapport complet payant). Le client voit aussi sa note projetée en audit approfondi (pire) → il a envie du complet.</p>
				<img id="ag-report-img" alt="rapport" style="max-width:100%;border:1px solid #ddd;border-radius:8px">
				<div style="margin-top:10px;display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
					<button type="button" class="button button-primary ag-fmt" data-fmt="attach">📎 Pièce jointe (4:5)</button>
					<button type="button" class="button ag-fmt" data-fmt="story">📱 Story WhatsApp (9:16)</button>
				</div>
				<div style="margin-top:10px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
					<a id="ag-report-dl" class="button button-primary" download="rapport-securite.png">⬇️ Télécharger l'image</a>
					<button type="button" class="button" onclick="document.getElementById('ag-report-modal').style.display='none'">Fermer</button>
				</div>
				<p style="font-size:11px;color:#888;margin:10px 0 0">Astuce : « Story WhatsApp » = plein écran 9:16 (statut WhatsApp / Insta). « Pièce jointe » = 4:5 pour email. Enregistre l'image puis joins-la à ton message d'alerte.</p>
			</div>
		</div>
		<canvas id="ag-report-canvas" width="1080" height="1400" style="display:none"></canvas>
		<script>
		(function(){
			function rr(x,a,b,w,h,r){x.beginPath();x.moveTo(a+r,b);x.arcTo(a+w,b,a+w,b+h,r);x.arcTo(a+w,b+h,a,b+h,r);x.arcTo(a,b+h,a,b,r);x.arcTo(a,b,a+w,b,r);x.closePath();}
			function col(s){return s>=75?'#28a745':(s>=50?'#F0A020':'#E10F1A');}
			function draw(d,H){
				H=H||1400;var c=document.getElementById('ag-report-canvas'),W=1080;c.width=W;c.height=H;var x=c.getContext('2d');
				x.clearRect(0,0,W,H);
				var g=x.createLinearGradient(0,0,0,H);g.addColorStop(0,'#0e1016');g.addColorStop(1,'#1b2030');x.fillStyle=g;x.fillRect(0,0,W,H);
				x.fillStyle='#E10F1A';x.fillRect(0,0,W,14);
				x.textAlign='left';
				x.fillStyle='#ffffff';x.font='bold 50px Arial';x.fillText('RAPPORT DE SÉCURITÉ',60,108);
				x.fillStyle='#E10F1A';x.font='bold 26px Arial';x.fillText('DIAGNOSTIC EXPRESS — GRATUIT',60,150);
				x.fillStyle='#aeb4c2';x.font='26px Arial';x.fillText((d.host||'').slice(0,46),60,196);
				// Score circle (audit simple)
				var cx=200,cy=320,rad=110,sc=col(d.score);
				x.lineWidth=20;x.strokeStyle='rgba(255,255,255,.10)';x.beginPath();x.arc(cx,cy,rad,0,Math.PI*2);x.stroke();
				x.strokeStyle=sc;x.beginPath();x.arc(cx,cy,rad,-Math.PI/2,-Math.PI/2+Math.PI*2*(Math.max(0,Math.min(100,d.score))/100));x.stroke();
				x.fillStyle='#fff';x.textAlign='center';x.font='bold 78px Arial';x.fillText(d.score,cx,cy+18);
				x.font='24px Arial';x.fillStyle='#aeb4c2';x.fillText('/ 100',cx,cy+54);
				x.textAlign='left';
				x.fillStyle='#fff';x.font='bold 34px Arial';x.fillText('Volet sécurité',360,280);
				x.fillStyle=sc;x.font='bold 40px Arial';
				x.fillText(d.nb+' faille'+(d.nb>1?'s':'')+' de sécurité',360,335);
				if(d.crit>0){x.fillStyle='#E10F1A';x.font='bold 30px Arial';x.fillText('dont '+d.crit+' CRITIQUE'+(d.crit>1?'S':''),360,380);}
				x.fillStyle='#8b93a4';x.font='22px Arial';x.fillText('Exposées publiquement, scannées 24h/24 par des robots.',360,422);
				// Failles list (masquées)
				var y=520;x.fillStyle='#fff';x.font='bold 30px Arial';x.fillText('CE QUE NOUS AVONS DÉTECTÉ',60,y);y+=18;
				x.strokeStyle='rgba(255,255,255,.12)';x.lineWidth=2;x.beginPath();x.moveTo(60,y);x.lineTo(W-60,y);x.stroke();y+=44;
				var show=d.fails.slice(0,6);
				show.forEach(function(f){
					x.fillStyle=f.crit?'#E10F1A':'#F0A020';x.beginPath();x.arc(80,y-8,11,0,Math.PI*2);x.fill();
					x.fillStyle='#fff';x.font='bold 27px Arial';x.fillText((f.label||'').slice(0,40),110,y);
					rr(x,640,y-26,380,36,8);x.fillStyle='rgba(255,255,255,.07)';x.fill();
					x.fillStyle='#7d8597';x.font='19px Arial';x.fillText('🔒 emplacement & correctif masqués',656,y-2);
					y+=58;
				});
				if(d.fails.length>6){x.fillStyle='#aeb4c2';x.font='italic 23px Arial';x.fillText('+ '+(d.fails.length-6)+' autre(s) faille(s) masquée(s)…',110,y);y+=44;}
				var pb=H-430,cb=H-190;
				if(H>1600){x.textAlign='center';x.fillStyle='#cfd4de';x.font='italic 27px Arial';x.fillText('Un site piraté = clients perdus, données volées,',W/2,pb-118);x.fillText('réputation détruite. Ça arrive sans prévenir.',W/2,pb-82);x.textAlign='left';}
				rr(x,60,pb,W-120,180,14);x.fillStyle='rgba(225,15,26,.12)';x.fill();x.strokeStyle='rgba(225,15,26,.5)';x.lineWidth=2;rr(x,60,pb,W-120,180,14);x.stroke();
				x.fillStyle='#ff6b6b';x.font='bold 28px Arial';x.fillText('⚠️ Et ce n\'est que la surface visible',90,pb+48);
				x.fillStyle='#fff';x.font='24px Arial';x.fillText('Score test léger',90,pb+100);
				x.fillText(d.deep_real?'Diagnostic Expert (réel)':'Diagnostic Expert (projection)',90,pb+146);
				x.textAlign='right';
				x.fillStyle=col(d.score);x.font='bold 40px Arial';x.fillText(d.score+' / 100',W-90,pb+100);
				x.fillStyle=col(d.deep);x.fillText(d.deep+' / 100',W-90,pb+146);
				x.textAlign='left';
				rr(x,60,cb,W-120,150,14);x.fillStyle='#F0A020';x.fill();
				x.fillStyle='#1b2030';x.font='bold 30px Arial';x.fillText('Débloquez le rapport complet',90,cb+54);
				x.font='23px Arial';
				var cta=d.order?('Toutes les failles + comment les corriger'):'Toutes les failles + corrections + accompagnement';
				x.fillText(cta,90,cb+92);
				x.font='bold 26px Arial';x.fillText(d.phone?('📞 '+d.phone+'  ·  Alliance Groupe'):'Alliance Groupe — Sécurité & création web',90,cb+130);
				return c;
			}
			var agCur=null,agFmt='attach';
			function render(){
				if(!agCur)return;var H=agFmt==='story'?1920:1400;var c=draw(agCur,H);var url=c.toDataURL('image/png');
				document.getElementById('ag-report-img').src=url;
				var dl=document.getElementById('ag-report-dl');dl.href=url;dl.setAttribute('download','rapport-securite-'+agFmt+'-'+((agCur.host||'site').replace(/[^a-z0-9.-]/gi,'_'))+'.png');
				document.querySelectorAll('.ag-fmt').forEach(function(z){z.classList.toggle('button-primary',z.getAttribute('data-fmt')===agFmt);});
			}
			document.querySelectorAll('.ag-fmt').forEach(function(z){z.addEventListener('click',function(){agFmt=z.getAttribute('data-fmt');render();});});
			document.querySelectorAll('.ag-report-btn').forEach(function(b){
				b.addEventListener('click',function(){
					try{agCur=JSON.parse(b.getAttribute('data-report'));}catch(e){alert('Données rapport illisibles');return;}
					agFmt='attach';render();
					document.getElementById('ag-report-modal').style.display='flex';
				});
			});
			var m=document.getElementById('ag-report-modal');if(m){m.addEventListener('click',function(e){if(e.target===m)m.style.display='none';});}
		})();
		</script>

		</div>
		<?php
	}
}

/* =========================================================================
 * WIDGET TABLEAU DE BORD — « Audit express » (accueil admin).
 * ====================================================================== */
add_action( 'wp_dashboard_setup', function () {
	if ( current_user_can( 'manage_options' ) ) {
		wp_add_dashboard_widget( 'ag_audit_express', '🔍 Audit express — sécurité', 'ag_dashboard_audit_widget' );
	}
} );
if ( ! function_exists( 'ag_dashboard_audit_widget' ) ) {
	function ag_dashboard_audit_widget() {
		// Audit immédiat dans le widget (un seul site).
		$res = null;
		if ( isset( $_POST['ag_dash_url'], $_POST['_agd_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agd_nonce'] ) ), 'ag_dash_audit' ) ) {
			$u = esc_url_raw( wp_unslash( $_POST['ag_dash_url'] ) );
			if ( $u && function_exists( 'ag_audit_run' ) ) $res = ag_audit_run( $u );
		}
		?>
		<form method="post">
			<?php wp_nonce_field( 'ag_dash_audit', '_agd_nonce' ); ?>
			<p style="display:flex;gap:6px">
				<input type="url" name="ag_dash_url" required placeholder="https://site-a-auditer.fr" style="flex:1" value="<?php echo isset( $_POST['ag_dash_url'] ) ? esc_attr( esc_url_raw( wp_unslash( $_POST['ag_dash_url'] ) ) ) : ''; ?>">
				<button type="submit" class="button button-primary">Auditer</button>
			</p>
		</form>
		<?php if ( $res ) :
			$score = (int) ( $res['score'] ?? 0 );
			$col   = $score >= 75 ? '#1a7f37' : ( $score >= 50 ? '#bf6a02' : '#b91c1c' );
			$pb    = function_exists( 'ag_audit_fail_list' ) ? ag_audit_fail_list( $res ) : array(); ?>
			<div style="border:1px solid #eee;border-left:4px solid <?php echo esc_attr( $col ); ?>;border-radius:6px;padding:8px 12px;margin-bottom:8px">
				<strong style="color:<?php echo esc_attr( $col ); ?>;font-size:18px"><?php echo $score; ?>/100</strong>
				— <?php echo count( $pb ); ?> faille(s)<?php echo ! empty( $res['critical'] ) ? ' · <span style="color:#b91c1c">' . (int) $res['critical'] . ' critique(s)</span>' : ''; ?>
				<div style="font-size:12px;color:#555;margin-top:4px"><?php echo esc_html( implode( ' · ', array_slice( $pb, 0, 5 ) ) ); ?></div>
				<a class="button button-small" style="margin-top:6px" href="<?php echo esc_url( admin_url( 'admin.php?page=ag-espace-audit&prefill=' . rawurlencode( $res['url'] ?? '' ) ) ); ?>">→ Démarchage + coordonnées (Espace Audit)</a>
			</div>
		<?php endif; ?>
		<p style="font-size:12px;color:#777;margin:6px 0 0">Diagnostic passif. <a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-espace-audit' ) ); ?>">Ouvrir l'Espace Audit complet →</a></p>
		<?php
	}
}

/* =========================================================================
 * EXPORT CSV des prospects audités (réexécute l'audit + extraction contacts).
 * ====================================================================== */
add_action( 'admin_post_ag_audit_export_csv', 'ag_audit_export_csv' );
if ( ! function_exists( 'ag_audit_export_csv' ) ) {
	function ag_audit_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_audit_export' ) ) wp_die( 'Lien expiré.' );

		$raw  = sanitize_textarea_field( wp_unslash( $_POST['urls'] ?? '' ) );
		$urls = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', $raw ) ) );
		$urls = array_slice( array_unique( $urls ), 0, 8 );
		$deep = ( 'deep' === ( $_POST['mode'] ?? 'passive' ) ) && ! empty( $_POST['mandat'] );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="prospects-audit-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // BOM Excel
		fputcsv( $out, array( 'Site', 'Score', 'Failles', 'Critiques', 'Liste des failles', 'Entreprise', 'Email', 'Telephone', 'Adresse', 'SIRET', 'Reseaux' ), ';' );

		foreach ( $urls as $u ) {
			$a  = $deep && function_exists( 'ag_audit_run_deep' ) ? ag_audit_run_deep( $u ) : ag_audit_run( $u );
			$ct = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $u ) : array();
			$pb = function_exists( 'ag_audit_fail_list' ) ? ag_audit_fail_list( $a ) : array();
			fputcsv( $out, array(
				$a['url'] ?? $u,
				(int) ( $a['score'] ?? 0 ),
				count( $pb ),
				(int) ( $a['critical'] ?? 0 ),
				implode( ' | ', $pb ),
				$ct['company'] ?? '',
				implode( ' ', $ct['emails'] ?? array() ),
				implode( ' ', $ct['phones'] ?? array() ),
				$ct['address'] ?? '',
				$ct['siret'] ?? '',
				implode( ' ', $ct['socials'] ?? array() ),
			), ';' );
		}
		fclose( $out );
		exit;
	}
}

/* =========================================================================
 * EXPORT CSV de l'HISTORIQUE des audits (avec statut de contact).
 * ====================================================================== */
add_action( 'admin_post_ag_audit_hist_csv', 'ag_audit_hist_csv' );
if ( ! function_exists( 'ag_audit_hist_csv' ) ) {
	function ag_audit_hist_csv() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_audit_hist_csv' ) ) wp_die( 'Lien expiré.' );
		$H = function_exists( 'ag_audit_hist_get' ) ? ag_audit_hist_get() : array();
		uasort( $H, function ( $x, $y ) { return (int) ( $y['ts'] ?? 0 ) <=> (int) ( $x['ts'] ?? 0 ); } );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="historique-audits-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'Date', 'Site', 'Entreprise', 'Type test', 'Score', 'Segment', 'Failles', 'Critiques', 'Liste failles', 'Email', 'Telephone', 'Adresse', 'SIRET', 'Reseaux', 'Contacte (canaux+dates)' ), ';' );
		foreach ( $H as $e ) {
			$fails = array();
			$ncrit = 0;
			foreach ( ( $e['checks'] ?? array() ) as $c ) { $fails[] = $c['name']; if ( ! empty( $c['critical'] ) && 'fail' === $c['status'] ) $ncrit++; }
			$contacte = array();
			foreach ( ( $e['actions'] ?? array() ) as $ac ) { $contacte[] = ( $ac['ch'] ?? '' ) . ' (' . ( $ac['date'] ?? '' ) . ')'; }
			fputcsv( $out, array(
				isset( $e['ts'] ) ? wp_date( 'd/m/Y H:i', (int) $e['ts'] ) : '',
				$e['url'] ?? '', $e['company'] ?? '', ( 'expert' === ( $e['mode'] ?? '' ) ? 'Expert (Kali)' : ( 'deep' === ( $e['mode'] ?? 'passive' ) ? 'Approfondi' : 'Leger' ) ), (int) ( $e['score'] ?? 0 ),
				$e['seg'] ?? '', count( $fails ), $ncrit, implode( ' | ', $fails ),
				$e['email'] ?? '', $e['phone'] ?? '', $e['address'] ?? '', $e['siret'] ?? '',
				implode( ' ', $e['socials'] ?? array() ), implode( ' · ', $contacte ),
			), ';' );
		}
		fclose( $out );
		exit;
	}
}

/* Suppression d'un audit de l'historique (admin-post, PRG fiable). */
add_action( 'admin_post_ag_audit_hist_del', 'ag_audit_hist_del_handler' );
if ( ! function_exists( 'ag_audit_hist_del_handler' ) ) {
	function ag_audit_hist_del_handler() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
		check_admin_referer( 'ag_audit_hist_del' );
		$hid = isset( $_POST['hist_id'] ) ? sanitize_text_field( wp_unslash( $_POST['hist_id'] ) ) : '';
		$H = ag_audit_hist_get();
		if ( $hid && isset( $H[ $hid ] ) ) { unset( $H[ $hid ] ); ag_audit_hist_save( $H ); $msg = 'del1'; } else { $msg = 'delnone'; }
		wp_safe_redirect( add_query_arg( array( 'page' => 'ag-espace-audit', 'agmsg' => $msg ), admin_url( 'admin.php' ) ) . '#agh-list' );
		exit;
	}
}
/* Suppression en masse d'audits (cases à cocher, AJAX, clés = hist_id). */
add_action( 'wp_ajax_ag_audit_hist_delete_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_audit_hist_bulk' ) ) wp_send_json_error();
	$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
	$ids = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $ids ) ) );
	if ( empty( $ids ) ) wp_send_json_error();
	$H = ag_audit_hist_get();
	$before = count( $H );
	foreach ( $ids as $id ) { unset( $H[ $id ] ); }
	ag_audit_hist_save( $H );
	wp_send_json_success( array( 'removed' => $before - count( $H ) ) );
} );
/* Vider TOUT l'historique des audits (admin-post). */
add_action( 'admin_post_ag_audit_hist_clear', 'ag_audit_hist_clear_handler' );
if ( ! function_exists( 'ag_audit_hist_clear_handler' ) ) {
	function ag_audit_hist_clear_handler() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
		check_admin_referer( 'ag_audit_hist_clear' );
		ag_audit_hist_save( array() );
		wp_safe_redirect( add_query_arg( array( 'page' => 'ag-espace-audit', 'agmsg' => 'cleared' ), admin_url( 'admin.php' ) ) );
		exit;
	}
}

/* RÉAUDITER TOUT : re-scanne chaque site de l'historique avec l'algo À JOUR
 * (utile après une évolution du scoring/des checks). Conserve le mode (léger/
 * approfondi) et les contacts déjà notés. Messages + images se régénèrent
 * automatiquement (ils dérivent des checks). Cap anti-timeout. */
add_action( 'admin_post_ag_audit_hist_rescan', 'ag_audit_hist_rescan_handler' );
if ( ! function_exists( 'ag_audit_hist_rescan_handler' ) ) {
	function ag_audit_hist_rescan_handler() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
		check_admin_referer( 'ag_audit_hist_rescan' );
		$H = ag_audit_hist_get();
		$done = 0;
		foreach ( $H as $hid => $e ) {
			$url  = $e['url'] ?? '';
			if ( ! $url ) { continue; }
			$mode = ( 'deep' === ( $e['mode'] ?? 'passive' ) ) ? 'deep' : 'passive';
			// Les fiches Expert (Kali) viennent d'un scan externe : on ne peut pas les
			// rejouer ici (ça se relance via 🛰️ Audits Kali). On les laisse intactes.
			if ( 'expert' === ( $e['mode'] ?? '' ) ) { continue; }
			// Re-scan avec l'algo courant (approfondi seulement si déjà fait + fonction dispo).
			$au = ( 'deep' === $mode && function_exists( 'ag_audit_run_deep' ) )
				? ag_audit_run_deep( $url )
				: ( function_exists( 'ag_audit_run' ) ? ag_audit_run( $url ) : null );
			if ( ! $au ) { continue; }
			// On réutilise les coordonnées déjà extraites (évite de re-crawler) si présentes.
			$ct = array(
				'emails'  => ! empty( $e['email'] ) ? array( $e['email'] ) : array(),
				'phones'  => ! empty( $e['phone'] ) ? array( $e['phone'] ) : array(),
				'address' => $e['address'] ?? '', 'siret' => $e['siret'] ?? '',
				'company' => $e['company'] ?? '', 'socials' => $e['socials'] ?? array(),
			);
			ag_audit_hist_upsert( $au, $ct, $mode );
			$done++;
			if ( $done >= 30 ) { break; } // cap anti-timeout (30 sites/ passe)
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'ag-espace-audit', 'agmsg' => 'rescanned', 'agn' => $done ), admin_url( 'admin.php' ) ) . '#agh-list' );
		exit;
	}
}

/* Lien audit ↔ CRM démarchage : retrouve un prospect (option ag_prospects) par domaine. */
if ( ! function_exists( 'ag_audit_prospect_by_site' ) ) {
	function ag_audit_prospect_by_site( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) return null;
		$host = preg_replace( '#^www\.#', '', strtolower( $host ) );
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			$w = $p['website'] ?? '';
			if ( ! $w ) continue;
			$wh = wp_parse_url( ( 0 === strpos( $w, 'http' ) ? $w : 'http://' . $w ), PHP_URL_HOST );
			$wh = $wh ? preg_replace( '#^www\.#', '', strtolower( $wh ) ) : '';
			if ( $wh && $wh === $host ) return $p;
		}
		return null;
	}
}
