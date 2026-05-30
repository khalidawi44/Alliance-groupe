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
			'price'    => '49',
			'pay_url'  => '',
			'raison'   => 'Alliance Groupe',
			'siret'    => '[SIRET à compléter dans Réglages → Tester / Audit]',
			'adresse'  => 'Nantes, France',
			'tva'      => 'TVA non applicable, art. 293 B du CGI',
			'email'    => 'contact@alliancegroupe-inc.com',
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
	foreach ( array( 'price', 'pay_url', 'raison', 'siret', 'adresse', 'tva', 'email' ) as $k ) {
		register_setting( 'ag_tester_group', 'ag_tester_' . $k );
	}
} );
if ( ! function_exists( 'ag_tester_settings_page' ) ) {
	function ag_tester_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$fields = array(
			'price'   => 'Prix du rapport complet (€ TTC)',
			'pay_url' => 'Lien de paiement (Stripe/PayPal) — vide = renvoie vers /contact',
			'raison'  => 'Raison sociale (mentions facture)',
			'siret'   => 'SIRET',
			'adresse' => 'Adresse',
			'tva'     => 'Mention TVA',
			'email'   => 'Email de contact (facture)',
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
if ( ! function_exists( 'ag_tester_run' ) ) {
	function ag_tester_run() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_tester' ) ) wp_die( 'Lien expiré.' );
		if ( ! empty( $_POST['hp_field'] ) ) wp_die( 'Spam détecté.' );
		if ( empty( $_POST['authorize'] ) ) wp_die( 'Vous devez autoriser le diagnostic pour continuer.' );

		$url    = esc_url_raw( wp_unslash( $_POST['site_url'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
		$tel    = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		if ( ! $url || ! is_email( $email ) ) wp_die( 'URL ou email invalide.' );

		$audit = function_exists( 'ag_audit_run' ) ? ag_audit_run( $url ) : array( 'url' => $url, 'checks' => array(), 'score' => 0, 'ts' => time() );
		$aid   = wp_hash( $url . '|' . $email . '|' . microtime() );
		set_transient( 'ag_tester_' . $aid, array(
			'audit'  => $audit,
			'email'  => $email,
			'prenom' => $prenom,
			'phone'  => $tel,
			'auth_at'=> current_time( 'mysql' ),
			'auth_ip'=> ag_tester_client_ip(),
			'unlocked' => false,
		), 30 * DAY_IN_SECONDS );

		if ( function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name' => $prenom ?: $email, 'email' => $email, 'phone' => $tel, 'website' => $url,
				'source' => 'tester-mon-site', 'status' => 'nouveau',
				'notes' => 'A testé son site le ' . current_time( 'd/m/Y H:i' ) . ' — score ' . ( $audit['score'] ?? 0 ) . '/100',
			) );
		}
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '🔍 Nouveau test de site', ( $prenom ?: $email ) . ' — ' . $url . ' — score ' . ( $audit['score'] ?? 0 ) . '/100' );
		}

		$base       = wp_get_referer() ?: home_url( '/tester-mon-site' );
		$result_url = add_query_arg( array( 'aid' => $aid ), $base );

		// Email d'aperçu (flouté) — AUCUNE facture ici, simple diagnostic.
		$score = (int) ( $audit['score'] ?? 0 );
		$nb_pb = 0;
		foreach ( ( $audit['checks'] ?? array() ) as $c ) { if ( 'ok' !== ( $c['status'] ?? '' ) ) $nb_pb++; }
		if ( function_exists( 'ag_email_wrap' ) ) {
			$inner  = '<p>Bonjour ' . esc_html( $prenom ) . ',</p>';
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
			ag_tester_render_teaser( $aid, $data );
		} else {
			ag_tester_render_form();
		}
		return ob_get_clean();
	}
}

if ( ! function_exists( 'ag_tester_render_form' ) ) {
	function ag_tester_render_form() {
		$in = 'padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem;width:100%;box-sizing:border-box';
		?>
		<section style="background:linear-gradient(180deg,#0a0a0f,#14141c);color:#fff;padding:80px 24px;min-height:78vh">
			<div style="max-width:620px;margin:0 auto;text-align:center">
				<span style="display:inline-block;padding:6px 14px;background:rgba(243,122,31,.15);border:1px solid rgba(243,122,31,.5);border-radius:999px;color:#F3D27A;font-size:.8rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px">🔍 Diagnostic gratuit · sans engagement</span>
				<h1 style="font-family:Georgia,serif;font-size:clamp(2rem,5vw,3.4rem);line-height:1.1;margin:0 0 14px">Testez la <em style="background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-style:italic">sécurité</em> de votre site</h1>
				<p style="color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.6;margin:0 0 32px">En 30 secondes : score global, nombre de failles détectées, et où vous en êtes vraiment. Diagnostic non-intrusif, avec votre autorisation.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="text-align:left;display:flex;flex-direction:column;gap:14px">
					<input type="hidden" name="action" value="ag_tester_run">
					<?php wp_nonce_field( 'ag_tester' ); ?>
					<input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
					<input type="url" name="site_url" required placeholder="https://monsite.fr" style="<?php echo $in; // phpcs:ignore ?>">
					<input type="text" name="prenom" placeholder="Votre prénom" style="<?php echo $in; // phpcs:ignore ?>">
					<input type="email" name="email" required placeholder="vous@email.fr" style="<?php echo $in; // phpcs:ignore ?>">
					<input type="tel" name="phone" placeholder="Téléphone (optionnel)" style="<?php echo $in; // phpcs:ignore ?>">
					<label style="display:flex;gap:10px;align-items:flex-start;font-size:.85rem;color:rgba(255,255,255,.72);line-height:1.45">
						<input type="checkbox" name="authorize" value="1" required style="margin-top:3px">
						<span>Je certifie être <strong>propriétaire ou mandaté</strong> pour ce site et j'autorise Alliance Groupe à réaliser un <strong>diagnostic non-intrusif</strong> (lecture des pages publiques).</span>
					</label>
					<button type="submit" style="margin-top:6px;padding:18px 36px;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;border:none;border-radius:999px;font-size:1.05rem;letter-spacing:1px;text-transform:uppercase;cursor:pointer;box-shadow:0 12px 32px rgba(243,122,31,.35)">🔍 Tester mon site →</button>
				</form>
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
} );

if ( ! function_exists( 'ag_audit_outreach_message' ) ) {
	/** Construit un message de démarchage à partir des failles trouvées. */
	function ag_audit_outreach_message( $audit ) {
		$url   = $audit['url'] ?? 'votre site';
		$score = (int) ( $audit['score'] ?? 0 );
		$pb    = array();
		foreach ( ( $audit['checks'] ?? array() ) as $c ) {
			if ( 'ok' !== ( $c['status'] ?? '' ) ) $pb[] = $c['name'];
		}
		$top = array_slice( $pb, 0, 3 );
		$liste = $top ? '- ' . implode( "\n- ", $top ) : '';
		$msg  = "Bonjour,\n\n";
		$msg .= "Je suis tombé sur votre site ($url) et j'ai fait un rapide diagnostic gratuit. ";
		$msg .= "Son score global est de $score/100, et j'ai relevé " . count( $pb ) . " point(s) à corriger, notamment :\n";
		$msg .= $liste ? $liste . "\n\n" : "\n";
		$msg .= "Ce sont des choses qui peuvent vous coûter des visiteurs (et vous exposer). Je peux vous envoyer le rapport complet avec les corrections, sans engagement.\n\n";
		$msg .= "Souhaitez-vous que je vous l'envoie ?\n\nFabrizio — Alliance Groupe\n";
		return $msg;
	}
}

if ( ! function_exists( 'ag_audit_prospect_page' ) ) {
	function ag_audit_prospect_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$results = array();
		$notice  = '';

		// Ajout au CRM (action ligne par ligne).
		if ( isset( $_POST['ag_add_crm'], $_POST['_agp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) ) {
			$u  = esc_url_raw( wp_unslash( $_POST['crm_url'] ?? '' ) );
			$sc = (int) ( $_POST['crm_score'] ?? 0 );
			if ( $u && function_exists( 'ag_prospect_add_record' ) ) {
				ag_prospect_add_record( array(
					'name' => wp_parse_url( $u, PHP_URL_HOST ) ?: $u, 'website' => $u,
					'source' => 'espace-audit', 'status' => 'nouveau',
					'notes'  => 'Audité depuis l\'Espace Audit le ' . current_time( 'd/m/Y H:i' ) . ' — score ' . $sc . '/100',
				) );
				$notice = '✅ ' . esc_html( $u ) . ' ajouté au CRM Prospection.';
			}
		}

		// Lancement des audits.
		if ( isset( $_POST['ag_run_audits'], $_POST['_agp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_agp_nonce'] ) ), 'ag_audit_prospect' ) ) {
			$raw  = sanitize_textarea_field( wp_unslash( $_POST['urls'] ?? '' ) );
			$urls = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', $raw ) ) );
			$urls = array_slice( array_unique( $urls ), 0, 8 ); // cap anti-timeout
			foreach ( $urls as $u ) {
				if ( function_exists( 'ag_audit_run' ) ) $results[] = ag_audit_run( $u );
			}
		}
		?>
		<div class="wrap">
			<h1>🔍 Espace Audit — prospection</h1>
			<p>Colle des URLs de sites à démarcher (une par ligne, max 8). Diagnostic <strong>non-intrusif</strong> : on lit les pages publiques, on relève les failles, et on génère un message de démarchage prêt à envoyer. Pour <em>trouver</em> des prospects sans bon site, utilise aussi le menu <strong>Prospection → Chasse</strong>.</p>
			<?php if ( $notice ) echo '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>'; ?>

			<form method="post" style="margin:18px 0;max-width:760px">
				<?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
				<textarea name="urls" rows="6" style="width:100%;font-family:monospace" placeholder="https://site-prospect-1.fr&#10;https://site-prospect-2.fr"><?php echo isset( $_POST['urls'] ) ? esc_textarea( wp_unslash( $_POST['urls'] ) ) : ''; ?></textarea>
				<p><button type="submit" name="ag_run_audits" value="1" class="button button-primary button-large">🔍 Lancer les audits</button></p>
			</form>

			<?php if ( $results ) : ?>
				<h2>Résultats (<?php echo count( $results ); ?>)</h2>
				<table class="widefat striped" style="max-width:1000px">
					<thead><tr><th>Site</th><th>Score</th><th>Failles</th><th>Principaux problèmes</th><th>Actions</th></tr></thead>
					<tbody>
					<?php foreach ( $results as $i => $a ) :
						$score = (int) ( $a['score'] ?? 0 );
						$col   = $score >= 75 ? '#1a7f37' : ( $score >= 50 ? '#bf6a02' : '#b91c1c' );
						$pb    = array();
						foreach ( ( $a['checks'] ?? array() ) as $c ) { if ( 'ok' !== ( $c['status'] ?? '' ) ) $pb[] = $c['name']; }
						$msg = ag_audit_outreach_message( $a ); ?>
						<tr>
							<td><a href="<?php echo esc_url( $a['url'] ?? '' ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $a['url'] ?? '' ); ?></a></td>
							<td><strong style="color:<?php echo esc_attr( $col ); ?>;font-size:16px"><?php echo $score; ?>/100</strong></td>
							<td><?php echo count( $pb ); ?></td>
							<td style="font-size:12px"><?php echo esc_html( implode( ' · ', array_slice( $pb, 0, 4 ) ) ); ?></td>
							<td style="white-space:nowrap">
								<form method="post" style="display:inline">
									<?php wp_nonce_field( 'ag_audit_prospect', '_agp_nonce' ); ?>
									<input type="hidden" name="crm_url" value="<?php echo esc_attr( $a['url'] ?? '' ); ?>">
									<input type="hidden" name="crm_score" value="<?php echo $score; ?>">
									<button type="submit" name="ag_add_crm" value="1" class="button button-small">➕ CRM</button>
								</form>
								<button type="button" class="button button-small" onclick="var d=document.getElementById('agmsg<?php echo $i; ?>');d.style.display=d.style.display==='none'?'block':'none'">✉️ Message</button>
								<div id="agmsg<?php echo $i; ?>" style="display:none;margin-top:8px">
									<textarea rows="9" style="width:420px;font-size:12px" onclick="this.select()"><?php echo esc_textarea( $msg ); ?></textarea>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p style="color:#666;font-size:12px;margin-top:10px">⚖️ Diagnostic passif (lecture publique). N'effectue aucune intrusion. Pour le démarchage : respecte l'opposition (Bloctel pour le tél, désinscription email).</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
