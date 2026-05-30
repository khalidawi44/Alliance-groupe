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
			'popup_img'=> '',
			'phone'    => '',
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
	foreach ( array( 'price', 'pay_url', 'raison', 'siret', 'adresse', 'tva', 'email', 'popup_img', 'phone', 'img_audit', 'img_creation', 'img_maintenance', 'img_templates', 'img_menace', 'tg_sec', 'tg_crea' ) as $k ) {
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
			'popup_img' => 'Image 3D du pop-up d\'accueil (URL complète) — vide = dégradé',
			'phone'   => 'Téléphone pro (pour « rappelez-moi » dans les messages)',
			'img_audit'       => '🖼️ URL image carte « Audit » (accueil)',
			'img_creation'    => '🖼️ URL image carte « Création » (accueil)',
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
		$tg_lead = '🔍 Nouveau test de site' . "\n" . ( $prenom ?: $email ) . ' — ' . $url . ' — score ' . ( $audit['score'] ?? 0 ) . '/100' . ( $tel ? "\n📞 " . $tel : '' );
		$tg_sec_chat = ag_tester_opt( 'tg_sec' );
		if ( $tg_sec_chat && function_exists( 'ag_tg_send' ) ) {
			ag_tg_send( $tg_sec_chat, $tg_lead ); // lead sécurité = canal sécurité dédié
		} elseif ( function_exists( 'ag_push' ) ) {
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
					<input type="url" name="site_url" required placeholder="https://monsite.fr" style="<?php echo $in; // phpcs:ignore ?>">
					<input type="text" name="prenom" placeholder="Votre prénom" style="<?php echo $in; // phpcs:ignore ?>">
					<input type="email" name="email" required placeholder="vous@email.fr" style="<?php echo $in; // phpcs:ignore ?>">
					<input type="tel" name="phone" placeholder="Téléphone (optionnel)" style="<?php echo $in; // phpcs:ignore ?>">
					<label style="display:flex;gap:10px;align-items:flex-start;font-size:.85rem;color:rgba(255,255,255,.72);line-height:1.45">
						<input type="checkbox" name="authorize" value="1" required style="margin-top:3px">
						<span>Je certifie être <strong>propriétaire ou mandaté</strong> pour ce site et j'autorise Alliance Groupe à réaliser un <strong>diagnostic non-intrusif</strong> (lecture des pages publiques).</span>
					</label>
					<button type="submit" style="margin-top:6px;padding:18px 36px;background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;border:none;border-radius:999px;font-size:1.05rem;letter-spacing:1px;text-transform:uppercase;cursor:pointer;box-shadow:0 12px 32px rgba(243,122,31,.35)">🔍 Lancer mon test gratuit →</button>
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
							<div style="color:#fff;font-weight:800;letter-spacing:1px;font-size:.8rem;text-transform:uppercase">③ Pentest complet · sur devis</div>
							<p style="color:rgba(255,255,255,.8);font-size:.92rem;line-height:1.5;margin:10px 0 0">Test d'intrusion encadré par un <strong style="color:#fff">contrat signé</strong> (périmètre, autorisation, confidentialité). Pour les sites sensibles.</p>
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
		$m .= "j'ai détecté " . count( $S ) . " faille(s)" . ( $crit ? ", dont $crit CRITIQUE(S)" : '' ) . ". Score de sécurité : $score/100.\n\n";
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
		$t  = "ALERTE securite $host : " . count( $S ) . " faille(s), score $score/100. Risque : piratage, vol de vos donnees clients, site hors-ligne ou rancon. ";
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
					if ( $deep_mode && function_exists( 'ag_audit_run_deep' ) ) {
						$results[] = ag_audit_run_deep( $u );
					} elseif ( function_exists( 'ag_audit_run' ) ) {
						$results[] = ag_audit_run( $u );
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
