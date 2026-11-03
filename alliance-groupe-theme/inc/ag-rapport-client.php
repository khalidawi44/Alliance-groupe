<?php
/**
 * AG — Rapport client privé + paiement (la page derrière les QR de l'assistant).
 *
 * Fabrice crée un « Rapport client » dans wp-admin (colle le HTML du rapport
 * généré par assistant-defi + les liens de paiement Stripe/PayPal par formule).
 * Chaque rapport a une URL PRIVÉE non devinable : /rapport/<jeton> (non indexée).
 * Les QR / boutons de l'assistant pointent dessus, avec ?offre=rapport|correction|refonte.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---- 1. Type de contenu « Rapport client » (admin uniquement) ---- */
add_action( 'init', function () {
	register_post_type( 'ag_rapport', array(
		'labels' => array(
			'name' => 'Rapports client', 'singular_name' => 'Rapport client',
			'add_new_item' => 'Nouveau rapport client', 'edit_item' => 'Modifier le rapport',
			'menu_name' => '🛡️ Rapports client',
		),
		'public' => false, 'show_ui' => true, 'show_in_menu' => true,
		'menu_icon' => 'dashicons-shield', 'menu_position' => 58,
		'supports' => array( 'title' ), 'exclude_from_search' => true,
		'capability_type' => 'post',
	) );
	add_rewrite_rule( '^rapport/([^/]+)/?$', 'index.php?ag_rapport_token=$matches[1]', 'top' );
	if ( get_option( 'ag_rapport_rw' ) !== '2' ) { flush_rewrite_rules( false ); update_option( 'ag_rapport_rw', '2' ); }
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'ag_rapport_token'; return $v; } );

/* ---- 2. Champs (métabox) ---- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ag_rap_box', 'Contenu du rapport & paiement', 'ag_rap_box_render', 'ag_rapport', 'normal', 'high' );
} );
function ag_rap_field( $post, $k ) { return esc_attr( (string) get_post_meta( $post->ID, $k, true ) ); }
function ag_rap_box_render( $post ) {
	wp_nonce_field( 'ag_rap_save', 'ag_rap_nonce' );
	$tok = get_post_meta( $post->ID, '_ag_rap_token', true );
	$url = $tok ? home_url( '/rapport/' . $tok ) : '(enregistrez pour générer le lien privé)';
	$html = (string) get_post_meta( $post->ID, '_ag_rap_html', true );
	echo '<style>.agr label{display:block;font-weight:600;margin:12px 0 4px}.agr input,.agr textarea{width:100%;padding:8px;border:1px solid #ccd0d4;border-radius:5px}.agr .row2{display:flex;gap:12px}.agr .row2>div{flex:1}</style>';
	echo '<div class="agr">';
	echo '<p><b>Lien privé du rapport :</b> <a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a><br><span style="color:#666">C\'est ce lien (sans indexation) que tu mets dans l\'assistant, champ « Lien de paiement ». Les QR pointeront dessus.</span></p>';
	$upd = (int) get_post_meta( $post->ID, '_ag_rap_updated', true );
	if ( $upd ) { echo '<p style="color:#1a7f37;font-size:13px;margin:-4px 0 8px">🕒 Dernière mise à jour : <b>' . esc_html( wp_date( 'd/m/Y à H:i', $upd ) ) . '</b> (heure du site)</p>'; }
	echo '<div class="row2"><div><label>Client</label><input type="text" name="ag_rap_client" value="' . ag_rap_field( $post, '_ag_rap_client' ) . '"></div>';
	echo '<div><label>Cible (domaine)</label><input type="text" name="ag_rap_cible" value="' . ag_rap_field( $post, '_ag_rap_cible' ) . '"></div></div>';
	echo '<div class="row2"><div><label>Email client</label><input type="email" name="ag_rap_email" value="' . ag_rap_field( $post, '_ag_rap_email' ) . '" placeholder="contact@client.fr"></div>';
	echo '<div><label>Tél client (SMS)</label><input type="text" name="ag_rap_phone" value="' . ag_rap_field( $post, '_ag_rap_phone' ) . '" placeholder="+33…"></div></div>';
	$is_paid = (string) get_post_meta( $post->ID, '_ag_rap_paid', true ) === '1';
	echo '<label style="display:block;margin:12px 0;padding:10px 12px;border:2px solid ' . ( $is_paid ? '#16a34a' : '#f0a020' ) . ';border-radius:6px;background:' . ( $is_paid ? '#eafaf0' : '#fff8ec' ) . '"><input type="checkbox" name="ag_rap_paid" value="1" ' . checked( $is_paid, true, false ) . '> <b>✅ Rapport PAYÉ — débloquer le certificat officiel + le code sceau (backlink)</b><br><span style="color:#666;font-size:12px">Coche dès réception du règlement. Le client voit alors son certificat complet et le code à coller sur son site (qui pointe vers alliancegroupe-inc.com).</span></label>';
	$certno = get_post_meta( $post->ID, '_ag_rap_cert', true );
	if ( $certno ) { echo '<p style="color:#666;font-size:12px">N° de certificat : <code>' . esc_html( $certno ) . '</code></p>'; }
	if ( $tok ) {
		$gw = function_exists( 'ag_sms_gateway_ready' ) && ag_sms_gateway_ready();
		echo '<p style="margin-top:10px"><button type="button" class="button button-primary" onclick="agRapSend(' . (int) $post->ID . ',\'mail\',this)">✉️ Envoyer le lien par email</button> ';
		echo '<button type="button" class="button" onclick="agRapSend(' . (int) $post->ID . ',\'sms\',this)"' . ( $gw ? '' : ' title="Passerelle SMS en pause (SIM requise)"' ) . '>📱 Envoyer par SMS' . ( $gw ? '' : ' (passerelle en pause)' ) . '</button> ';
		echo '<span id="agRapSendOut" style="margin-left:8px;color:#666"></span></p>';
		echo '<script>function agRapSend(id,ch,btn){var o=document.getElementById("agRapSendOut");o.style.color="#666";o.textContent=(ch==="sms"?"Envoi SMS…":"Envoi email…");btn.disabled=true;var b=new URLSearchParams();b.set("id",id);b.set("channel",ch);b.set("_wpnonce","' . esc_js( wp_create_nonce( 'wp_rest' ) ) . '");fetch("' . esc_url_raw( rest_url( 'ag/v1/rapport-send' ) ) . '",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded","X-WP-Nonce":"' . esc_js( wp_create_nonce( 'wp_rest' ) ) . '"},body:b.toString()}).then(function(r){return r.json();}).then(function(j){btn.disabled=false;o.style.color=(j&&j.ok)?"#1a7f37":"#b32d2e";o.textContent=(j&&j.ok)?("✅ "+(j.msg||"envoyé")):("❌ "+((j&&j.error)||"échec"));}).catch(function(e){btn.disabled=false;o.style.color="#b32d2e";o.textContent="❌ "+e.message;});}</script>';
	}
	echo '<label>HTML du rapport (colle le rapport « version client » généré par assistant-defi)</label>';
	echo '<textarea name="ag_rap_html" rows="10" placeholder="Colle ici le contenu du cadre blanc du rapport (bouton clic droit → Inspecter → copier, ou laisse vide pour n\'afficher que les formules)">' . esc_textarea( $html ) . '</textarea>';
	echo '<p style="color:#666">Astuce : tu peux laisser vide et n\'afficher que le bloc paiement ci-dessous.</p>';
	echo '<div class="row2"><div><label>Prix Rapport seul (€)</label><input type="number" name="ag_rap_px_rapport" value="' . ag_rap_field( $post, '_ag_rap_px_rapport' ) . '" placeholder="149"></div>';
	echo '<div><label>Prix Rapport + Correction (€)</label><input type="number" name="ag_rap_px_correction" value="' . ag_rap_field( $post, '_ag_rap_px_correction' ) . '" placeholder="1299"></div>';
	echo '<div><label>Prix Refonte (à partir de €)</label><input type="number" name="ag_rap_px_refonte" value="' . ag_rap_field( $post, '_ag_rap_px_refonte' ) . '" placeholder="1490"></div></div>';
	echo '<label>Lien de paiement — Rapport seul (Stripe/PayPal)</label><input type="url" name="ag_rap_pay_rapport" value="' . ag_rap_field( $post, '_ag_rap_pay_rapport' ) . '" placeholder="https://buy.stripe.com/...">';
	echo '<label>Lien de paiement — Rapport + Correction</label><input type="url" name="ag_rap_pay_correction" value="' . ag_rap_field( $post, '_ag_rap_pay_correction' ) . '" placeholder="https://buy.stripe.com/...">';
	echo '<label>Lien de paiement — Refonte complète</label><input type="url" name="ag_rap_pay_refonte" value="' . ag_rap_field( $post, '_ag_rap_pay_refonte' ) . '" placeholder="https://buy.stripe.com/...">';
	echo '</div>';
}

/* ---- 3. Enregistrement ---- */
add_action( 'save_post_ag_rapport', function ( $post_id ) {
	if ( ! isset( $_POST['ag_rap_nonce'] ) || ! wp_verify_nonce( $_POST['ag_rap_nonce'], 'ag_rap_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$txt = array( '_ag_rap_client'=>'ag_rap_client', '_ag_rap_cible'=>'ag_rap_cible', '_ag_rap_phone'=>'ag_rap_phone',
		'_ag_rap_px_rapport'=>'ag_rap_px_rapport', '_ag_rap_px_correction'=>'ag_rap_px_correction', '_ag_rap_px_refonte'=>'ag_rap_px_refonte' );
	foreach ( $txt as $meta=>$field ) { if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) ); }
	if ( isset( $_POST['ag_rap_email'] ) ) update_post_meta( $post_id, '_ag_rap_email', sanitize_email( wp_unslash( $_POST['ag_rap_email'] ) ) );
	update_post_meta( $post_id, '_ag_rap_paid', ! empty( $_POST['ag_rap_paid'] ) ? '1' : '0' );
	$urls = array( '_ag_rap_pay_rapport'=>'ag_rap_pay_rapport', '_ag_rap_pay_correction'=>'ag_rap_pay_correction', '_ag_rap_pay_refonte'=>'ag_rap_pay_refonte' );
	foreach ( $urls as $meta=>$field ) { if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, $meta, esc_url_raw( wp_unslash( $_POST[ $field ] ) ) ); }
	// HTML : autorisé seulement pour un auteur qui a le droit unfiltered_html (admin)
	if ( isset( $_POST['ag_rap_html'] ) ) {
		$raw = wp_unslash( $_POST['ag_rap_html'] );
		update_post_meta( $post_id, '_ag_rap_html', current_user_can( 'unfiltered_html' ) ? $raw : wp_kses_post( $raw ) );
	}
	if ( ! get_post_meta( $post_id, '_ag_rap_token', true ) ) {
		update_post_meta( $post_id, '_ag_rap_token', wp_generate_password( 22, false, false ) );
	}
	if ( ! get_post_meta( $post_id, '_ag_rap_cert', true ) ) {
		update_post_meta( $post_id, '_ag_rap_cert', 'AG-SEC-' . gmdate( 'Y' ) . '-' . strtoupper( substr( wp_generate_password( 8, false, false ), 0, 6 ) ) );
	}
	update_post_meta( $post_id, '_ag_rap_updated', current_time( 'timestamp' ) );
	// Édition manuelle du rapport → Espace Audit prend le lien + le score (fiche du site mise à jour).
	if ( function_exists( 'ag_rap_link_into_history' ) ) {
		$tok0 = get_post_meta( $post_id, '_ag_rap_token', true );
		$cib0 = (string) get_post_meta( $post_id, '_ag_rap_cible', true );
		$hn0  = preg_replace( '#^www\\.#', '', strtolower( (string) ( wp_parse_url( ( 0 === stripos( $cib0, 'http' ) ? $cib0 : 'https://' . $cib0 ), PHP_URL_HOST ) ?: $cib0 ) ) );
		$sc0  = get_post_meta( $post_id, '_ag_rap_score', true );
		if ( $tok0 && $hn0 ) {
			ag_rap_link_into_history( $hn0, home_url( '/rapport/' . $tok0 ), ( '' !== $sc0 ? (int) $sc0 : null ), array(
				'company' => (string) get_post_meta( $post_id, '_ag_rap_client', true ),
				'email'   => (string) get_post_meta( $post_id, '_ag_rap_email', true ),
				'phone'   => (string) get_post_meta( $post_id, '_ag_rap_phone', true ),
			) );
		}
	}
} );

/* ---- 4. Colonne « Lien privé » dans la liste ---- */
add_filter( 'manage_ag_rapport_posts_columns', function ( $c ) { $c['ag_lien'] = 'Lien privé'; return $c; } );
add_action( 'manage_ag_rapport_posts_custom_column', function ( $col, $id ) {
	if ( 'ag_lien' === $col ) { $t = get_post_meta( $id, '_ag_rap_token', true ); if ( $t ) { $u = home_url( '/rapport/' . $t ); echo '<a href="' . esc_url( $u ) . '" target="_blank">/rapport/' . esc_html( substr( $t, 0, 8 ) ) . '…</a>'; } }
}, 10, 2 );

/* ---- 5. Rendu de la page publique privée /rapport/<jeton> ---- */
add_action( 'template_redirect', function () {
	$tok = get_query_var( 'ag_rapport_token' );
	if ( ! $tok ) return;
	$q = get_posts( array( 'post_type'=>'ag_rapport', 'post_status'=>'publish', 'numberposts'=>1,
		'meta_key'=>'_ag_rap_token', 'meta_value'=>$tok, 'fields'=>'ids' ) );
	if ( empty( $q ) ) { status_header( 404 ); nocache_headers(); echo '<!doctype html><meta charset="utf-8"><p style="font:16px sans-serif;padding:40px">Rapport introuvable ou expiré.</p>'; exit; }
	$id = $q[0];
	$client = get_post_meta( $id, '_ag_rap_client', true );
	$cible  = get_post_meta( $id, '_ag_rap_cible', true );
	$html   = (string) get_post_meta( $id, '_ag_rap_html', true );
	$score  = get_post_meta( $id, '_ag_rap_score', true );
	$cert   = get_post_meta( $id, '_ag_rap_cert', true );
	if ( ! $cert ) { $cert = 'AG-SEC-' . gmdate( 'Y' ) . '-' . strtoupper( substr( wp_generate_password( 8, false, false ), 0, 6 ) ); update_post_meta( $id, '_ag_rap_cert', $cert ); }
	$paid   = (string) get_post_meta( $id, '_ag_rap_paid', true ) === '1';
	// SOURCE UNIQUE : score + corps RECALCULÉS en direct (mêmes failles qu'Espace Audit ET l'assistant),
	// au lieu du HTML/score figés à l'enregistrement → plus jamais « rien n'est pareil ».
	if ( function_exists( 'ag_rap_all_findings' ) && '' !== (string) $cible ) {
		$live = ag_rap_all_findings( $cible );
		if ( is_array( $live ) && null !== $live['score'] ) { $score = (int) $live['score']; }
		if ( function_exists( 'ag_tester_rapport_html' ) ) {
			$live_html = ag_tester_rapport_html( array( 'host' => $cible, 'url' => ( 0 === stripos( (string) $cible, 'http' ) ? $cible : 'https://' . $cible ), 'score' => (int) $score ) );
			$live_html = preg_replace( '#<p><strong>Score de securite[^<]*</strong>[^<]*</p>#u', '', (string) $live_html, 1 );
			if ( '' !== trim( (string) $live_html ) ) { $html = $live_html; }
		}
	}
	$offre  = isset( $_GET['offre'] ) ? sanitize_key( $_GET['offre'] ) : '';
	// Prix : par-rapport → option globale → défaut raisonnable (zéro config nécessaire).
	$defpx = array( 'rapport' => 290, 'correction' => 1299, 'refonte' => 1490 );
	$rpx = function ( $id, $k ) use ( $defpx ) {
		$m = get_post_meta( $id, '_ag_rap_px_' . $k, true ); if ( '' !== $m ) { return $m; }
		$o = get_option( 'ag_rap_px_' . $k, '' ); if ( '' !== $o ) { return $o; }
		return isset( $defpx[ $k ] ) ? $defpx[ $k ] : '';
	};
	// Lien de paiement : par-rapport → option → sinon GÉNÉRÉ depuis le PayPal déjà configuré (ag_paypal_email).
	$rpy = function ( $id, $k, $price, $label ) use ( $cert ) {
		$m = get_post_meta( $id, '_ag_rap_pay_' . $k, true ); if ( '' !== $m ) { return $m; }
		$o = get_option( 'ag_rap_pay_' . $k, '' ); if ( '' !== $o ) { return $o; }
		$pp = sanitize_email( (string) get_option( 'ag_paypal_email', '' ) );
		if ( $pp && '' !== $price && (float) $price > 0 ) {
			// custom = ag_rapport:<N° certificat> → le webhook PayPal débloque AUTOMATIQUEMENT ce rapport.
			return 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . rawurlencode( $pp )
				. '&item_name=' . rawurlencode( 'Alliance Groupe — ' . $label . ' — ' . $cert )
				. '&amount=' . number_format( (float) $price, 2, '.', '' )
				. '&custom=' . rawurlencode( 'ag_rapport:' . $cert )
				. '&currency_code=EUR&no_shipping=1&no_note=1';
		}
		return '';
	};
	$lbl = array( 'rapport' => 'Rapport seul', 'correction' => 'Rapport + Correction', 'refonte' => 'Refonte complète sécurisée' );
	$px = array(
		'rapport'    => array( $lbl['rapport'],    $rpx( $id, 'rapport' ),    $rpy( $id, 'rapport', $rpx( $id, 'rapport' ), $lbl['rapport'] ),       'Le rapport complet, à vous de corriger.' ),
		'correction' => array( $lbl['correction'], $rpx( $id, 'correction' ), $rpy( $id, 'correction', $rpx( $id, 'correction' ), $lbl['correction'] ), 'On corrige tout + re-test de vérification.' ),
		'refonte'    => array( $lbl['refonte'],    $rpx( $id, 'refonte' ),    $rpy( $id, 'refonte', $rpx( $id, 'refonte' ), $lbl['refonte'] ),       'Site reconstruit propre et durci.' ),
	);
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	$ag_shield = esc_url( get_stylesheet_directory_uri() . '/assets/images/atelier/securite.webp' );
	$logo = '<img src="' . $ag_shield . '" alt="Sceau sécurité Alliance Groupe" style="height:54px;width:auto;display:block;border-radius:8px">';
	echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Rapport d’audit de sécurité — Alliance Groupe</title>';
	echo '<style>body{margin:0;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f4f5f7;color:#141414}.wrap{max-width:820px;margin:0 auto;background:#fff}.hd{display:flex;align-items:center;gap:14px;background:linear-gradient(135deg,#0e1016,#1b2030);padding:20px 24px}.hd .t{color:#fff;font-weight:800;font-size:19px}.hd .s{color:#E6A93C;font-size:13px}.body{padding:8px 24px 28px}.offers{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}.card{flex:1;min-width:210px;border:1px solid #e3e3e3;border-radius:10px;padding:16px}.card.hi{border:2px solid #F37A1F;background:#fff8ec}.card .p{font-size:24px;font-weight:900;margin:6px 0}.btn{display:inline-block;margin-top:8px;background:linear-gradient(135deg,#E6A93C,#F37A1F);color:#241200;font-weight:800;text-decoration:none;padding:12px 18px;border-radius:9px}.btn.d{background:#0e1016;color:#fff}.foot{padding:16px 24px;color:#777;font-size:12px;border-top:1px solid #eee}.agseal{display:flex;gap:16px;align-items:center;background:linear-gradient(135deg,#0e1016,#1b2030);color:#fff;border-radius:12px;padding:16px 18px;margin:18px 0}.agseal-ring{width:104px;height:104px;flex:0 0 auto;border-radius:50%;background:radial-gradient(circle at 50% 38%,#20242f,#0b0d12);border:3px solid #E6A93C;box-shadow:0 0 0 3px #0e1016,0 0 18px rgba(230,169,60,.5);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;overflow:hidden}.agseal-ring:after{content:"";position:absolute;top:0;left:-60%;width:55%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);transform:skewX(-20deg);animation:agshine 3.6s ease-in-out infinite}@keyframes agshine{0%,72%{left:-60%}100%{left:150%}}.agseal-top{font-size:8px;letter-spacing:2px;color:#E6A93C;font-weight:700}.agseal-brand{font-size:13px;font-weight:900;line-height:1;margin:3px 0;background:linear-gradient(135deg,#E6A93C,#F37A1F);-webkit-background-clip:text;background-clip:text;color:transparent}.agseal-bot{font-size:8px;letter-spacing:.5px;color:#cfd4e0;max-width:92px}.agseal-txt{font-size:13.5px;line-height:1.45}.agseal-img{width:108px;height:auto;flex:0 0 auto;border-radius:12px;box-shadow:0 0 20px rgba(230,169,60,.4)}@media(max-width:520px){.agseal{flex-direction:column;text-align:center}}</style></head><body><div class="wrap">';
	echo '<div class="hd">' . $logo . '<div><div class="t">Rapport d’audit de sécurité</div><div class="s">Alliance Groupe · Cybersécurité &amp; Conformité<br>' . esc_html( $client ? $client : 'Client' ) . ( $cible ? ' · ' . esc_html( $cible ) : '' ) . '</div></div></div>';
	echo '<div class="body">';
	if ( '' !== (string) $score ) {
		$sc = max( 0, min( 100, (int) $score ) );
		$col = $sc >= 75 ? '#16a34a' : ( $sc >= 50 ? '#ea580c' : '#dc2626' );
		$lab = $sc >= 75 ? 'Bon niveau' : ( $sc >= 50 ? 'A ameliorer' : 'A risque' );
		$circ = 326.726; $off = $circ * ( 1 - $sc / 100 );
		echo '<div style="display:flex;align-items:center;gap:22px;flex-wrap:wrap;margin:6px 0 20px">';
		echo '<svg width="132" height="132" viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="52" fill="none" stroke="#eee" stroke-width="12"/><circle cx="60" cy="60" r="52" fill="none" stroke="' . $col . '" stroke-width="12" stroke-linecap="round" stroke-dasharray="' . $circ . '" stroke-dashoffset="' . $off . '" transform="rotate(-90 60 60)"/><text x="60" y="58" text-anchor="middle" font-size="30" font-weight="800" fill="' . $col . '">' . $sc . '</text><text x="60" y="77" text-anchor="middle" font-size="12" fill="#999">/ 100</text></svg>';
		echo '<div><div style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:1px">Score de securite</div><div style="font-size:22px;font-weight:800;color:' . $col . '">' . esc_html( $lab ) . '</div><div style="font-size:13px;color:#555;max-width:400px;margin-top:4px">Ce score mesure l&rsquo;exposition de votre site. Chaque faille corrigee le fait monter &mdash; et protege vos donnees, vos clients et votre reputation.</div></div>';
		echo '</div>';
	}
	if ( $html !== '' ) { echo $html; } // contenu authoré en admin (trusted)
	$seal_dom = esc_html( $cible ? $cible : 'votre site' );
	echo '<div class="agseal"><img class="agseal-img" src="' . $ag_shield . '" alt="Certificat de sécurité Alliance Groupe" />'
		. '<div class="agseal-txt">'
		. '<div style="font-size:11px;letter-spacing:2.5px;color:#E6A93C;font-weight:800">CERTIFICAT D\'AUDIT DE SÉCURITÉ</div>'
		. '<div style="font-size:19px;font-weight:900;margin:3px 0 2px">Alliance Groupe</div>'
		. '<div style="font-size:13.5px;line-height:1.6">Site audité : <b>' . $seal_dom . '</b><br>Année : <b>' . esc_html( gmdate( 'Y' ) ) . '</b> &nbsp;·&nbsp; N° : <b style="letter-spacing:.5px">' . esc_html( $cert ) . '</b></div>'
		. '<div style="font-size:12px;color:#9aa0b0;margin-top:8px">Après correction, ce certificat atteste que <b>' . $seal_dom . '</b> a été audité et sécurisé par Alliance Groupe. Affichez le sceau sur votre site (pied de page) pour rassurer vos visiteurs — un gage de sérieux que vos concurrents n\'ont pas.</div>'
		. '</div></div>';
	echo '<h3>Choisissez votre formule</h3><div class="offers">';
	foreach ( $px as $key => $o ) {
		$hi = ( $key === 'correction' || $key === $offre ) ? ' hi' : '';
		$prix = $o[1] !== '' ? number_format_i18n( (float) $o[1], 0 ) . ' €' . ( $key === 'refonte' ? ' et +' : '' ) : 'Sur devis';
		echo '<div class="card' . $hi . '" id="offre-' . esc_attr( $key ) . '">' . ( $hi ? '<div style="display:inline-block;background:#F37A1F;color:#fff;font-size:11px;font-weight:800;padding:2px 10px;border-radius:20px;margin-bottom:6px">⭐ RECOMMANDE</div>' : '' ) . '<div style="font-weight:800">' . esc_html( $o[0] ) . '</div><div class="p">' . esc_html( $prix ) . '</div><div style="font-size:13px;color:#555">' . esc_html( $o[3] ) . '</div>';
		if ( $o[2] ) { echo '<a class="btn' . ( $key === 'correction' ? '' : ' d' ) . '" href="' . esc_url( $o[2] ) . '">Payer en ligne</a>'; }
		else { echo '<div style="margin-top:8px;font-size:12px;color:#888">Nous contacter : 07 44 82 95 16</div>'; }
		echo '</div>';
	}
	echo '</div></div>';
	// ===== Certificat officiel + sceau à intégrer (backlink SEO) — débloqué APRÈS paiement =====
	$home_seo = home_url( '/' ) . '?utm_source=sceau&utm_medium=certificat&utm_campaign=' . rawurlencode( $cert );
	$embed = '<a href="' . esc_url( $home_seo ) . '" target="_blank" rel="noopener" title="Site audité et sécurisé par Alliance Groupe — certificat ' . esc_attr( $cert ) . '">'
		. '<img src="' . $ag_shield . '" alt="Certifié sécurisé par Alliance Groupe — N° ' . esc_attr( $cert ) . '" width="120" style="height:auto;border:0"></a>';
	if ( $paid ) {
		echo '<div style="margin:18px 0;padding:18px;border:2px solid #16a34a;border-radius:12px;background:#f3fbf6">';
		echo '<h3 style="margin:0 0 6px;color:#0a7d3c">✅ Certificat débloqué — merci de votre confiance</h3>';
		echo '<p style="margin:0 0 10px">Votre site <b>' . $seal_dom . '</b> est <b>certifié sécurisé</b> par Alliance Groupe (certificat N° <b>' . esc_html( $cert ) . '</b> · ' . esc_html( gmdate( 'Y' ) ) . '). Collez le sceau ci-dessous dans le <b>pied de page</b> de votre site : il rassure vos visiteurs et prouve votre sérieux.</p>';
		echo '<div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap">';
		echo '<div style="flex:0 0 auto;text-align:center">' . $embed . '<div style="font-size:11px;color:#888;margin-top:4px">aperçu du sceau</div></div>';
		echo '<div style="flex:1;min-width:260px"><label style="font-weight:700;font-size:13px">Code à copier (collez-le dans votre site) :</label>'
			. '<textarea id="agEmbed" readonly rows="4" onclick="this.select()" style="width:100%;font-family:monospace;font-size:12px;border:1px solid #ccd;border-radius:6px;padding:8px;box-sizing:border-box">' . esc_textarea( $embed ) . '</textarea>'
			. '<button type="button" id="agEmbedBtn" onclick="var t=document.getElementById(\'agEmbed\');t.select();try{document.execCommand(\'copy\');}catch(e){}this.textContent=\'✅ Code copié\';" style="margin-top:6px;background:#0e1016;color:#fff;border:0;border-radius:8px;padding:9px 16px;font-weight:700;cursor:pointer">📋 Copier le code</button></div>';
		echo '</div></div>';
	} else {
		echo '<div style="margin:18px 0;padding:16px;border:2px dashed #f0a020;border-radius:12px;background:#fff8ec">'
			. '<h3 style="margin:0 0 4px">🔒 Votre certificat officiel + sceau pour votre site</h3>'
			. '<p style="margin:0;color:#555">Dès réception de votre règlement, vous débloquez votre <b>certificat officiel N° ' . esc_html( $cert ) . '</b> et un <b>code prêt à coller</b> sur votre site — un sceau « Sécurisé par Alliance Groupe » qui rassure vos visiteurs et valorise votre sérieux. Choisissez une formule ci-dessus.</p></div>';
	}
	echo '<div class="foot">Document confidentiel remis dans le cadre d\'un mandat / programme autorisé. Alliance Groupe — 07 44 82 95 16 · alliancegroupe-inc.com/contact</div>';
	echo '</div>';
	if ( $offre ) { echo '<script>var e=document.getElementById("offre-' . esc_js( $offre ) . '");if(e)e.scrollIntoView({behavior:"smooth",block:"center"});</script>'; }
	echo '</body></html>';
	exit;
} );

/* ===================== Helper : créer un rapport privé depuis un audit (agents) ===================== */
if ( ! function_exists( 'ag_rapport_create' ) ) {
	/**
	 * Crée un « Rapport client » privé (lien /rapport/<jeton> non indexé) à partir
	 * d'un résultat d'audit. Les boutons de paiement viennent des liens globaux
	 * (options ag_rap_pay_*) si non définis par rapport. Renvoie l'URL privée.
	 *
	 * @param string $html   Le rapport HTML (ag_aa_render d'un audit).
	 * @param string $client Nom du client/prospect.
	 * @param string $cible  Domaine audité.
	 * @param int    $devis  Montant indicatif (métadonnée).
	 * @return array{id:int,url:string}|null
	 */
	function ag_rapport_create( $html, $client = '', $cible = '', $devis = 0, $email = '', $phone = '', $score = null ) {
		if ( ! post_type_exists( 'ag_rapport' ) ) { return null; }
		// IDEMPOTENT : si un rapport existe deja pour ce domaine, on le MET A JOUR (pas de doublon).
		$hostn = preg_replace( '#^www\\.#', '', strtolower( (string) ( wp_parse_url( ( 0 === stripos( (string) $cible, 'http' ) ? $cible : 'https://' . $cible ), PHP_URL_HOST ) ?: $cible ) ) );
		$id = 0;
		if ( $hostn ) {
			// Plus ANCIEN d'abord → on garde le rapport canonique (lien /rapport/<jeton> deja partage) et on le rafraichit.
			$ex = get_posts( array( 'post_type' => 'ag_rapport', 'post_status' => 'publish', 'numberposts' => 50, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'ASC' ) );
			$matches = array();
			foreach ( $ex as $pid ) {
				// On parse le HOST du cible stocke (qui peut etre une URL complete « https://site » OU « site ») exactement comme $hostn.
				$raw = (string) get_post_meta( $pid, '_ag_rap_cible', true );
				$ph  = preg_replace( '#^www\\.#', '', strtolower( (string) ( wp_parse_url( ( 0 === stripos( $raw, 'http' ) ? $raw : 'https://' . $raw ), PHP_URL_HOST ) ?: $raw ) ) );
				if ( $ph && $ph === $hostn ) { $matches[] = (int) $pid; }
			}
			if ( $matches ) {
				$id = (int) $matches[0]; // le plus ancien = canonique (on garde son lien /rapport/<jeton>)
				// 1 SEUL rapport par site : les doublons (ancien bug de rapprochement http/https) partent a la corbeille.
				for ( $k = 1; $k < count( $matches ); $k++ ) { wp_trash_post( $matches[ $k ] ); }
			}
		}
		// Heure LOCALE du site (Reglages > General > Fuseau horaire), avec l'heure — pas l'UTC.
		$now   = current_time( 'mysql' );
		$nowg  = current_time( 'mysql', true );
		$title = 'Audit sécurité — ' . ( $cible ? $cible : 'cible' ) . ' (' . current_time( 'Y-m-d H:i' ) . ')';
		if ( $id ) {
			// On garde le rapport canonique mais on REMET sa date a maintenant → l'admin affiche la vraie heure d'enregistrement.
			wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_date' => $now, 'post_date_gmt' => $nowg, 'edit_date' => true ) );
		} else {
			$id = wp_insert_post( array( 'post_type' => 'ag_rapport', 'post_status' => 'publish', 'post_title' => $title ) );
		}
		update_post_meta( $id, '_ag_rap_updated', current_time( 'timestamp' ) );
		if ( ! $id || is_wp_error( $id ) ) { return null; }
		update_post_meta( $id, '_ag_rap_html', wp_kses_post( $html ) );
		update_post_meta( $id, '_ag_rap_client', sanitize_text_field( $client ) );
		update_post_meta( $id, '_ag_rap_cible', sanitize_text_field( $cible ) );
		if ( $email ) { update_post_meta( $id, '_ag_rap_email', sanitize_email( $email ) ); }
		if ( $phone ) { update_post_meta( $id, '_ag_rap_phone', sanitize_text_field( $phone ) ); }
		if ( $devis ) { update_post_meta( $id, '_ag_rap_devis', (int) $devis ); }
		if ( null !== $score && '' !== $score ) { update_post_meta( $id, '_ag_rap_score', (int) $score ); }
		$tok = get_post_meta( $id, '_ag_rap_token', true );
		if ( ! $tok ) { $tok = wp_generate_password( 22, false, false ); update_post_meta( $id, '_ag_rap_token', $tok ); }
		// N° de certificat UNIQUE (stable) : AG-SEC-<année>-<6 hex>.
		if ( ! get_post_meta( $id, '_ag_rap_cert', true ) ) {
			update_post_meta( $id, '_ag_rap_cert', 'AG-SEC-' . gmdate( 'Y' ) . '-' . strtoupper( substr( wp_generate_password( 8, false, false ), 0, 6 ) ) );
		}
		$pub = home_url( '/rapport/' . $tok );
		// Écrit en RETOUR dans Espace Audit : la fiche du site prend le lien + le score du rapport (sans écraser les failles).
		ag_rap_link_into_history( $hostn, $pub, $score, array( 'company' => $client, 'email' => $email, 'phone' => $phone ) );
		return array( 'id' => (int) $id, 'url' => $pub );
	}
}

/* Relie un rapport client à sa fiche Espace Audit : pose rapport_url (+ score) sur les entrées
 * d'historique du même domaine ; crée une fiche légère si le site n'a pas encore d'audit.
 * NE réécrit JAMAIS les checks/failles existants (on ne touche que le lien, le score et les contacts). */
if ( ! function_exists( 'ag_rap_link_into_history' ) ) {
	function ag_rap_link_into_history( $host, $url_pub, $score = null, $contacts = array() ) {
		$root = preg_replace( '#^www\\.#', '', strtolower( (string) $host ) );
		if ( '' === $root || ! function_exists( 'get_option' ) ) { return; }
		$h = get_option( 'ag_audit_history', array() ); if ( ! is_array( $h ) ) { $h = array(); }
		$found = false;
		foreach ( $h as $id => $e ) {
			if ( ! is_array( $e ) ) { continue; }
			$eh = preg_replace( '#^www\\.#', '', strtolower( (string) ( $e['host'] ?? wp_parse_url( (string) ( $e['url'] ?? '' ), PHP_URL_HOST ) ) ) );
			if ( $eh !== $root ) { continue; }
			$h[ $id ]['rapport_url'] = $url_pub;
			if ( null !== $score && '' !== $score ) { $h[ $id ]['score'] = min( (int) ( $e['score'] ?? 100 ), (int) $score ); }
			if ( ! empty( $contacts['company'] ) && empty( $e['company'] ) ) { $h[ $id ]['company'] = sanitize_text_field( (string) $contacts['company'] ); }
			if ( ! empty( $contacts['email'] )   && empty( $e['email'] ) )   { $h[ $id ]['email']   = sanitize_email( (string) $contacts['email'] ); }
			if ( ! empty( $contacts['phone'] )   && empty( $e['phone'] ) )   { $h[ $id ]['phone']   = sanitize_text_field( (string) $contacts['phone'] ); }
			$found = true;
		}
		if ( ! $found ) {
			// Aucun audit encore pour ce site → fiche légère pour qu'Espace Audit l'affiche (failles tirées du stock à l'affichage).
			$h[ 'client_' . md5( $root ) ] = array(
				'url' => 'https://' . $root, 'host' => $root, 'ts' => time(),
				'score' => ( null !== $score && '' !== $score ) ? (int) $score : 100, 'critical' => 0,
				'seg' => 'securite', 'mode' => 'passive', 'checks' => array(), 'deep_checks' => array(),
				'company' => sanitize_text_field( (string) ( $contacts['company'] ?? '' ) ),
				'email' => sanitize_email( (string) ( $contacts['email'] ?? '' ) ),
				'phone' => sanitize_text_field( (string) ( $contacts['phone'] ?? '' ) ),
				'rapport_url' => $url_pub, 'src' => 'self',
			);
		}
		update_option( 'ag_audit_history', $h, false );
	}
}

/* ===================== REST : outil (assistant-defi) & agents → rapport ===================== */
if ( ! function_exists( 'ag_rap_rest_authed' ) ) {
	/** Autorise : admin connecté (cookie + nonce REST) OU jeton du site (= ag_pentest_secret). */
	function ag_rap_rest_authed( $req ) {
		if ( current_user_can( 'manage_options' ) ) return true;
		$secret = (string) get_option( 'ag_pentest_secret', '' );
		if ( $secret !== '' ) {
			$tok = (string) $req->get_param( 'token' );
			if ( hash_equals( $secret, $tok ) ) return true;
		}
		return new WP_Error( 'ag_forbidden', 'Non autorisé : connecte-toi en admin OU fournis le jeton du site.', array( 'status' => 403 ) );
	}
}

add_action( 'rest_api_init', function () {
	// 1) Ranger un rapport généré par l'outil → crée la page privée /rapport/<jeton>.
	register_rest_route( 'ag/v1', '/rapport-save', array(
		'methods'             => 'POST',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function ( $req ) {
			if ( ! function_exists( 'ag_rapport_create' ) ) {
				return new WP_Error( 'ag_nofn', 'Module rapport indisponible.', array( 'status' => 500 ) );
			}
			$html   = (string) $req->get_param( 'html' );
			if ( trim( $html ) === '' ) return new WP_Error( 'ag_empty', 'Rapport vide (génère-le d\'abord).', array( 'status' => 400 ) );
			// HTML de confiance seulement si admin ; sinon on nettoie.
			$safe   = current_user_can( 'unfiltered_html' ) ? $html : wp_kses_post( $html );
			$client = sanitize_text_field( (string) $req->get_param( 'client' ) );
			$cible  = sanitize_text_field( (string) $req->get_param( 'cible' ) );
			$email  = sanitize_email( (string) $req->get_param( 'email' ) );
			$phone  = sanitize_text_field( (string) $req->get_param( 'phone' ) );
			$devis  = (int) $req->get_param( 'devis' );
			$raw0 = (string) $req->get_param( 'findings' ); $fnd0 = json_decode( $raw0, true ); $pen0 = 0;
				if ( is_array( $fnd0 ) ) { foreach ( $fnd0 as $f0 ) { $s0 = (float) ( $f0['score'] ?? 0 ); $pen0 += ( $s0 >= 9 ? 28 : ( $s0 >= 7 ? 14 : ( $s0 >= 4 ? 6 : 2 ) ) ); } }
				$score0 = max( 5, min( 100, 100 - $pen0 ) );
				$r = ag_rapport_create( $safe, $client, $cible, $devis, $email, $phone, $score0 );
			if ( ! $r ) return new WP_Error( 'ag_fail', 'Création impossible.', array( 'status' => 500 ) );
				if ( function_exists( 'ag_rap_store_findings' ) ) { ag_rap_store_findings( $cible, (string) $req->get_param( 'findings' ) ); }
				// L'audit est finalisé dans l'outil → clôt automatiquement le job Kali « en attente » de cette cible.
			$closed = '';
			if ( $cible && function_exists( 'ag_pt_autoclose_target' ) ) {
				$closed = ag_pt_autoclose_target( $cible, $r['url'] );
			}
			// Faire APPARAITRE la cible dans Espace Audit (historique) avec ses failles + le lien du rapport.
			$fiche = false;
			if ( $cible && function_exists( 'ag_audit_hist_upsert' ) && function_exists( 'ag_audit_hist_get' ) && function_exists( 'ag_audit_hist_save' ) ) {
				$url = ( 0 === stripos( $cible, 'http' ) ) ? $cible : ( 'https://' . $cible );
				$raw = (string) $req->get_param( 'findings' );
				$fnd = json_decode( $raw, true );
				$checks = array(); $crit = 0; $penal = 0;
				if ( is_array( $fnd ) ) {
					foreach ( $fnd as $f ) {
						$nm = sanitize_text_field( (string) ( $f['name'] ?? 'Faille' ) );
						$sc = (float) ( $f['score'] ?? 0 );
						$isc = ( $sc >= 9 );
						if ( $isc ) { $crit++; $penal += 28; } elseif ( $sc >= 7 ) { $penal += 14; } elseif ( $sc >= 4 ) { $penal += 6; } else { $penal += 2; }
						$checks[] = array( 'name' => $nm, 'status' => 'fail', 'critical' => $isc );
					}
				}
				$score = max( 5, min( 100, 100 - $penal ) );
				$a  = array( 'url' => $url, 'score' => $score, 'critical' => $crit, 'checks' => $checks );
				$ct = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $url ) : array();
				if ( $email ) { $ct['emails'] = array( $email ); }
				if ( $phone ) { $ct['phones'] = array( $phone ); }
				ag_audit_hist_upsert( $a, $ct, 'expert' );
				$hostn = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) );
				$hh = ag_audit_hist_get();
				foreach ( $hh as $k => $e ) {
					if ( preg_replace( '#^www\.#', '', strtolower( (string) ( $e['host'] ?? '' ) ) ) === $hostn ) { $hh[ $k ]['rapport_url'] = $r['url']; }
				}
				ag_audit_hist_save( $hh );
				$fiche = true;
			}
			return array( 'ok' => true, 'id' => $r['id'], 'url' => $r['url'], 'job_closed' => $closed, 'fiche' => $fiche );
		},
	) );

	// 2) Envoyer le lien privé du rapport au client : email (actif) / SMS (si passerelle prête).
	register_rest_route( 'ag/v1', '/rapport-send', array(
		'methods'             => 'POST',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function ( $req ) {
			$id = (int) $req->get_param( 'id' );
			$ch = sanitize_key( (string) $req->get_param( 'channel' ) );
			if ( ! $id || get_post_type( $id ) !== 'ag_rapport' ) return new WP_Error( 'ag_noid', 'Rapport introuvable.', array( 'status' => 404 ) );
			$tok = get_post_meta( $id, '_ag_rap_token', true );
			if ( ! $tok ) return new WP_Error( 'ag_notok', 'Rapport sans lien privé.', array( 'status' => 400 ) );
			$url    = home_url( '/rapport/' . $tok );
			$client = (string) get_post_meta( $id, '_ag_rap_client', true );
			$cible  = (string) get_post_meta( $id, '_ag_rap_cible', true );
			$bonjour = $client ? ( 'Bonjour ' . $client ) : 'Bonjour';
			if ( 'sms' === $ch ) {
				$phone = (string) get_post_meta( $id, '_ag_rap_phone', true );
				if ( ! $phone ) return new WP_Error( 'ag_nophone', 'Aucun numéro enregistré pour ce rapport.', array( 'status' => 400 ) );
				if ( ! function_exists( 'ag_sms_send' ) || ! function_exists( 'ag_sms_gateway_ready' ) || ! ag_sms_gateway_ready() ) {
					return new WP_Error( 'ag_gw', 'Passerelle SMS en pause (SIM dédiée requise). Utilise l\'email pour l\'instant.', array( 'status' => 409 ) );
				}
				$msg = 'Alliance Groupe — votre rapport de sécurité' . ( $cible ? ' (' . $cible . ')' : '' ) . ' : ' . $url;
				$ok  = ag_sms_send( $phone, $msg );
				return $ok ? array( 'ok' => true, 'msg' => 'SMS envoyé à ' . $phone ) : new WP_Error( 'ag_smsfail', 'Échec de l\'envoi SMS.', array( 'status' => 500 ) );
			}
			// email par défaut
			$to = sanitize_email( (string) get_post_meta( $id, '_ag_rap_email', true ) );
			if ( ! $to ) return new WP_Error( 'ag_nomail', 'Aucun email enregistré pour ce rapport.', array( 'status' => 400 ) );
			$inner  = '<p>' . esc_html( $bonjour ) . ',</p>';
			$inner .= '<p>Votre rapport de sécurité' . ( $cible ? ' concernant <b>' . esc_html( $cible ) . '</b>' : '' ) . ' est prêt. Il est confidentiel et accessible uniquement via ce lien privé :</p>';
			$inner .= function_exists( 'ag_email_button' ) ? ag_email_button( 'Voir mon rapport', $url ) : ( '<p><a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a></p>' );
			$inner .= '<p style="color:#666;font-size:13px">Vous y trouverez le détail des points à corriger et les formules de remédiation (paiement en ligne sécurisé).</p>';
			$body   = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Votre rapport de sécurité', $inner ) : $inner;
			$subject = 'Alliance Groupe — votre rapport de sécurité' . ( $cible ? ' (' . $cible . ')' : '' );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			$ok = wp_mail( $to, $subject, $body, $headers );
			return $ok ? array( 'ok' => true, 'msg' => 'Email envoyé à ' . $to ) : new WP_Error( 'ag_mailfail', 'Échec de l\'envoi email (vérifie la config mail du site).', array( 'status' => 500 ) );
		},
	) );

	// 3) Etat CONNU d'un site (tout ce qui a deja ete audite) -> l'assistant se pre-remplit + trace ce qui manque.
	// SOURCE UNIQUE DES PRIX (repli REST + jeton, hors-ligne) : même table que Réglages / audit auto.
	register_rest_route( 'ag/v1', '/aa-prices', array(
		'methods'             => 'GET',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function () {
			$p = function_exists( 'ag_aa_prices' ) ? ag_aa_prices() : array( 'critique' => 900, 'eleve' => 500, 'moyen' => 250, 'faible' => 90 );
			return array(
				'ok'      => true,
				'crit'    => (int) $p['critique'],
				'eleve'   => (int) $p['eleve'],
				'moyen'   => (int) $p['moyen'],
				'faible'  => (int) $p['faible'],
				'report'  => (int) get_option( 'ag_rap_px_rapport', 149 ),
				'refonte' => (int) get_option( 'ag_rap_px_refonte', 1490 ),
			);
		},
	) );

	// Retirer UNE faille du stock d'un site (ex. faux positif) → disparaît partout (assistant, rapport, /rapport).
	register_rest_route( 'ag/v1', '/find-drop', array(
		'methods'             => 'POST',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function ( $req ) {
			$cible = (string) $req->get_param( 'cible' );
			$name  = trim( (string) $req->get_param( 'name' ) );
			if ( '' === $cible || '' === $name ) { return array( 'ok' => false, 'error' => 'cible + name requis' ); }
			if ( function_exists( 'ag_rap_find_key' ) ) {
				$key = ag_rap_find_key( $cible );
				if ( $key ) {
					$cur = get_option( $key, array() );
					if ( is_array( $cur ) ) {
						$cur = array_values( array_filter( $cur, function ( $f ) use ( $name ) { return trim( (string) ( $f['name'] ?? '' ) ) !== $name; } ) );
						update_option( $key, $cur, false );
					}
				}
			}
			return array( 'ok' => true, 'dropped' => $name );
		},
	) );

	register_rest_route( 'ag/v1', '/site-state', array(
		'methods'             => 'GET',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function ( $req ) {
			$cible = (string) $req->get_param( 'cible' );
			$host  = preg_replace( '#^www\\.#', '', strtolower( (string) wp_parse_url( ( 0 === stripos( $cible, 'http' ) ? $cible : 'https://' . $cible ), PHP_URL_HOST ) ) );
			if ( '' === $host ) { return array( 'ok' => false, 'host' => '', 'modes' => array(), 'findings' => array(), 'rapport_url' => '' ); }
			// SOURCE UNIQUE : même collecte que le rapport client (historique + Kali + stock riche).
			$agg = ag_rap_all_findings( $host );
			$sess = get_option( 'ag_aa_sess_' . md5( $host ), null );
			return array(
				'ok' => ( ! empty( $agg['modes'] ) || ! empty( $agg['findings'] ) || ! empty( $sess ) ),
				'host' => $host,
				'modes' => $agg['modes'],
				'score' => $agg['score'],
				'critical' => (int) $agg['critical'],
				'findings' => $agg['findings'],
				'rapport_url' => $agg['rapport_url'],
				'session' => ( is_array( $sess ) ? $sess : null ),
			);
		},
	) );

	register_rest_route( 'ag/v1', '/session-save', array(
		'methods'             => 'POST',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function ( $req ) {
			$cible = (string) $req->get_param( 'cible' );
			$host  = preg_replace( '#^www\\.#', '', strtolower( (string) wp_parse_url( ( 0 === stripos( $cible, 'http' ) ? $cible : 'https://' . $cible ), PHP_URL_HOST ) ) );
			if ( '' === $host ) { return array( 'ok' => false, 'error' => 'cible invalide' ); }
			$data = json_decode( (string) $req->get_param( 'data' ), true );
			if ( ! is_array( $data ) ) { return array( 'ok' => false, 'error' => 'donnees invalides' ); }
			$keep = array(
				'sig'      => array_slice( (array) ( $data['sig'] ?? array() ), 0, 120 ),
				'steps'    => (array) ( $data['steps'] ?? array() ),
				'verdicts' => (array) ( $data['verdicts'] ?? array() ),
				'gen'      => sanitize_text_field( (string) ( $data['gen'] ?? '' ) ),
				'ts'       => time(),
			);
			update_option( 'ag_aa_sess_' . md5( $host ), $keep, false );
			return array( 'ok' => true, 'host' => $host );
		},
	) );

	// 4) SYNCHRO AUTO : l'assistant pousse ses failles -> met a jour la FICHE Espace Audit (sans doublon, sans rapport force).
	register_rest_route( 'ag/v1', '/site-upsert', array(
		'methods'             => 'POST',
		'permission_callback' => 'ag_rap_rest_authed',
		'callback'            => function ( $req ) {
			if ( ! function_exists( 'ag_audit_hist_upsert' ) ) { return array( 'ok' => false, 'msg' => 'hist indispo' ); }
			$cible = (string) $req->get_param( 'cible' );
			$url   = ( 0 === stripos( $cible, 'http' ) ) ? $cible : ( 'https://' . $cible );
			$host  = (string) wp_parse_url( $url, PHP_URL_HOST );
			if ( '' === $host ) { return array( 'ok' => false, 'msg' => 'cible invalide' ); }
			$fnd = json_decode( (string) $req->get_param( 'findings' ), true );
			$checks = array(); $crit = 0; $pen = 0;
			if ( is_array( $fnd ) ) {
				foreach ( $fnd as $f ) {
					$nm = sanitize_text_field( (string) ( $f['name'] ?? 'Faille' ) );
					$sc = (float) ( $f['score'] ?? 0 ); $isc = ( $sc >= 9 );
					if ( $isc ) { $crit++; $pen += 28; } elseif ( $sc >= 7 ) { $pen += 14; } elseif ( $sc >= 4 ) { $pen += 6; } else { $pen += 2; }
					$checks[] = array( 'name' => $nm, 'status' => 'fail', 'critical' => $isc );
				}
			}
			if ( empty( $checks ) ) { return array( 'ok' => true, 'msg' => 'rien a synchroniser' ); }
			$score = max( 5, min( 100, 100 - $pen ) );
			$a  = array( 'url' => $url, 'score' => $score, 'critical' => $crit, 'checks' => $checks );
			$ct = function_exists( 'ag_audit_extract_contacts' ) ? ag_audit_extract_contacts( $url ) : array();
			ag_audit_hist_upsert( $a, $ct, 'expert' );
			// Lien du rapport existant pour ce host (s'il y en a un) -> l'assistant peut l'afficher.
			$rap = '';
			if ( function_exists( 'ag_audit_hist_get' ) ) {
				$hn = preg_replace( '#^www\\.#', '', strtolower( $host ) );
				foreach ( ag_audit_hist_get() as $e ) { if ( preg_replace( '#^www\\.#', '', strtolower( (string) ( $e['host'] ?? '' ) ) ) === $hn && ! empty( $e['rapport_url'] ) ) { $rap = (string) $e['rapport_url']; } }
			}
			return array( 'ok' => true, 'host' => $host, 'score' => $score, 'critical' => $crit, 'rapport_url' => $rap );
		},
	) );
} );

/* ===================== Auto-déblocage du certificat au paiement PayPal ===================== */
add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn, $type = '', $resource = array() ) {
	if ( $type && 'PAYMENT.CAPTURE.COMPLETED' !== $type ) { return; }
	// 1) Retrouver le N° de certificat : via custom_id "ag_rapport:AG-SEC-..." ou dans le JSON (item_name).
	$cid  = is_array( $resource ) ? (string) ( $resource['custom_id'] ?? ( $resource['invoice_id'] ?? '' ) ) : '';
	$cert = '';
	if ( 0 === stripos( $cid, 'ag_rapport:' ) ) { $cert = trim( substr( $cid, strlen( 'ag_rapport:' ) ) ); }
	if ( '' === $cert ) {
		$blob = (string) wp_json_encode( $resource );
		if ( preg_match( '/AG-SEC-\d{4}-[A-Z0-9]{4,}/', $blob, $mm ) ) { $cert = $mm[0]; }
	}
	if ( '' === $cert ) { return; }
	// 2) Rapport correspondant.
	$q = get_posts( array( 'post_type' => 'ag_rapport', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_ag_rap_cert', 'meta_value' => $cert ) );
	if ( empty( $q ) ) { return; }
	$rid = (int) $q[0];
	// 3) Idempotence (même transaction PayPal).
	if ( (string) get_post_meta( $rid, '_ag_rap_paid_txn', true ) === (string) $txn ) { return; }
	update_post_meta( $rid, '_ag_rap_paid', '1' );
	update_post_meta( $rid, '_ag_rap_paid_txn', (string) $txn );
	update_post_meta( $rid, '_ag_rap_paid_at', time() );
	// 4) Email client : certificat débloqué + lien.
	$tok = get_post_meta( $rid, '_ag_rap_token', true );
	$to  = sanitize_email( (string) ( $email ? $email : get_post_meta( $rid, '_ag_rap_email', true ) ) );
	if ( $to && $tok ) {
		$url   = home_url( '/rapport/' . $tok );
		$inner = '<p>Merci pour votre confiance !</p><p>Votre <b>certificat de sécurité</b> (N° ' . esc_html( $cert ) . ') est <b>débloqué</b>. Retrouvez-le, ainsi que le code du sceau à coller sur votre site, ici :</p>';
		$inner .= function_exists( 'ag_email_button' ) ? ag_email_button( 'Voir mon certificat', $url ) : ( '<p><a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a></p>' );
		$body   = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Certificat débloqué', $inner ) : $inner;
		wp_mail( $to, 'Alliance Groupe — votre certificat de sécurité est débloqué', $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}
	// 5) Notif interne (Fabrice).
	$admin = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
	wp_mail( $admin, '💰 Rapport payé (PayPal) — ' . $cert, "Paiement reçu : {$amount} €" . ( $to ? " de {$to}" : '' ) . "\nCertificat débloqué : {$cert}\nTransaction PayPal : {$txn}" );
	if ( function_exists( 'ag_push' ) ) { ag_push( '💰 Rapport payé', $cert . ( $to ? ' → ' . $to : '' ) . ' (' . $amount . ' €)' ); }
}, 10, 5 );

/* ===================== Pont admin-ajax (auth par COOKIE admin, SANS nonce) =====================
 * L'assistant est un fichier statique (pas de nonce REST). Quand il est ouvert DEPUIS le site
 * par un admin connecté, admin-ajax authentifie par cookie → on rejoue la route REST en interne.
 * Résultat : enregistrement/synchro AUTOMATIQUES sans coller de jeton. (Repli REST+jeton hors-ligne.)
 */
if ( ! function_exists( 'ag_rap_ajax_proxy' ) ) {
	function ag_rap_ajax_proxy() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json( array( 'ok' => false, 'error' => 'non autorisé (admin requis)' ), 403 ); }
		$map = array(
			'ag_rap_save'       => array( 'POST', '/ag/v1/rapport-save' ),
			'ag_rap_siteupsert' => array( 'POST', '/ag/v1/site-upsert' ),
			'ag_rap_sitestate'  => array( 'GET',  '/ag/v1/site-state' ),
			'ag_rap_send'       => array( 'POST', '/ag/v1/rapport-send' ),
			'ag_rap_finddrop'   => array( 'POST', '/ag/v1/find-drop' ),
			'ag_rap_sesssave'   => array( 'POST', '/ag/v1/session-save' ),
		);
		$a = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( ! isset( $map[ $a ] ) ) { wp_send_json( array( 'ok' => false, 'error' => 'action inconnue' ), 400 ); }
		$r = new WP_REST_Request( $map[ $a ][0], $map[ $a ][1] );
		foreach ( $_REQUEST as $k => $v ) { if ( 'action' === $k ) { continue; } $r->set_param( $k, is_string( $v ) ? wp_unslash( $v ) : $v ); }
		$res = rest_do_request( $r );
		if ( $res->is_error() ) { $e = $res->as_error(); wp_send_json( array( 'ok' => false, 'error' => $e->get_error_message() ) ); }
		wp_send_json( $res->get_data() );
	}
	add_action( 'wp_ajax_ag_rap_save', 'ag_rap_ajax_proxy' );
	add_action( 'wp_ajax_ag_rap_siteupsert', 'ag_rap_ajax_proxy' );
	add_action( 'wp_ajax_ag_rap_sitestate', 'ag_rap_ajax_proxy' );
	add_action( 'wp_ajax_ag_rap_send', 'ag_rap_ajax_proxy' );
	add_action( 'wp_ajax_ag_rap_finddrop', 'ag_rap_ajax_proxy' );
	add_action( 'wp_ajax_ag_rap_sesssave', 'ag_rap_ajax_proxy' );
}

/* ===================== Failles riches PAR SITE (tous outils) — source unique du rapport ===================== */
if ( ! function_exists( 'ag_rap_find_key' ) ) {
	function ag_rap_find_key( $host ) {
		$h = preg_replace( '#^www\.#', '', strtolower( (string) ( wp_parse_url( ( 0 === stripos( (string) $host, 'http' ) ? $host : 'https://' . $host ), PHP_URL_HOST ) ?: $host ) ) );
		return $h ? 'ag_aa_find_' . md5( $h ) : '';
	}
	/** Fusionne des failles (JSON de l'outil) dans le stock du site, dédup par nom, garde le pire score. */
	function ag_rap_store_findings( $host, $json ) {
		$key = ag_rap_find_key( $host ); if ( '' === $key ) { return; }
		$fnd = json_decode( (string) $json, true ); if ( ! is_array( $fnd ) ) { return; }
		$cur = get_option( $key, array() ); if ( ! is_array( $cur ) ) { $cur = array(); }
		$GEN = 'Point de sécurité à corriger'; // libellé générique parasite (ancien bug) : on le rejette + purge l'existant
		$by = array();
		foreach ( $cur as $f ) { if ( ! empty( $f['name'] ) && $f['name'] !== $GEN ) { $by[ $f['name'] ] = $f; } }
		foreach ( $fnd as $f ) {
			$n = sanitize_text_field( (string) ( $f['name'] ?? '' ) ); if ( '' === $n || $n === $GEN ) { continue; }
			$sc = (float) ( $f['score'] ?? 0 );
			if ( isset( $by[ $n ] ) && (float) ( $by[ $n ]['score'] ?? 0 ) > $sc ) { $sc = (float) $by[ $n ]['score']; }
			$by[ $n ] = array(
				'name' => $n, 'score' => $sc,
				'type' => sanitize_key( (string) ( $f['type'] ?? '' ) ),
				'cvss' => sanitize_text_field( (string) ( $f['cvss'] ?? '' ) ),
				'rem'  => sanitize_text_field( (string) ( $f['rem'] ?? '' ) ),
				'proof'=> sanitize_textarea_field( (string) ( $f['proof'] ?? '' ) ),
			);
		}
		update_option( $key, array_slice( array_values( $by ), 0, 60 ), false );
	}
	function ag_rap_get_findings( $host ) {
		$key = ag_rap_find_key( $host ); if ( '' === $key ) { return array(); }
		$v = get_option( $key, array() ); return is_array( $v ) ? $v : array();
	}
}

if ( ! function_exists( 'ag_rap_is_false_xss' ) ) {
	/** Vrai si une faille « XSS » stockée est un FAUX POSITIF : sa preuve est une page HTML normale
	 *  (longue / <!doctype / <html) SANS la charge de test injectée (alert(1337)/alert(document.domain)/
	 *  <svg|<img ... onload|onerror=alert( ). Une vraie XSS a toujours ce marqueur dans sa preuve. */
	function ag_rap_is_false_xss( $f ) {
		$t = strtolower( (string) ( $f['type'] ?? '' ) );
		$n = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) ( $f['name'] ?? '' ) ) : strtolower( (string) ( $f['name'] ?? '' ) );
		$is_xss = ( 'xss' === $t || 'xssr' === $t || false !== strpos( $n, 'xss' ) );
		if ( ! $is_xss ) { return false; }
		$proof = (string) ( $f['proof'] ?? '' );
		$looks_page = ( strlen( $proof ) > 200 ) || ( false !== stripos( $proof, '<!doctype' ) ) || ( false !== stripos( $proof, '<html' ) );
		if ( ! $looks_page ) { return false; } // pas de preuve / preuve courte → on ne juge pas (on garde)
		if ( preg_match( '#alert\(1337\)|alert\(document\.domain\)|<svg[\s/][^>]*onload\s*=\s*alert\(|<img[^>]*onerror\s*=\s*alert\(#i', $proof ) ) { return false; } // vraie XSS
		return true; // page normale sans charge = faux positif
	}
}

if ( ! function_exists( 'ag_rap_all_findings' ) ) {
	/**
	 * SOURCE UNIQUE des failles d'un site : fusionne l'historique (VRAIS noms, pas le libellé teaser)
	 * + la synthèse Kali (job terminé) + le stock riche (toutes sources : assistant, Kali collé…).
	 * Dédoublonné par nom. Utilisée par site-state (assistant) ET le rapport client → jamais de divergence.
	 * Retour : array( findings[{name,score,type?,cvss?,rem?}], modes[], score|null, critical, rapport_url ).
	 */
	function ag_rap_all_findings( $host ) {
		$host = preg_replace( '#^www\\.#', '', strtolower( (string) ( wp_parse_url( ( 0 === stripos( (string) $host, 'http' ) ? $host : 'https://' . $host ), PHP_URL_HOST ) ?: $host ) ) );
		if ( '' === $host ) { return array( 'findings' => array(), 'modes' => array(), 'score' => null, 'critical' => 0, 'rapport_url' => '' ); }
		$GEN = 'Point de sécurité à corriger'; // libellé générique parasite (ancien bug) : on l'ignore partout
		$modes = array(); $labels = array(); $worst = 101; $crit = 0; $rap = '';
		if ( function_exists( 'ag_audit_hist_get' ) ) {
			foreach ( ag_audit_hist_get() as $e ) {
				$eh = preg_replace( '#^www\\.#', '', strtolower( (string) ( $e['host'] ?? wp_parse_url( (string) ( $e['url'] ?? '' ), PHP_URL_HOST ) ) ) );
				if ( $eh !== $host ) { continue; }
				$modes[ (string) ( $e['mode'] ?? 'passive' ) ] = true;
				$sc = (int) ( $e['score'] ?? 100 ); if ( $sc < $worst ) { $worst = $sc; }
				$crit = max( $crit, (int) ( $e['critical'] ?? 0 ) );
				if ( ! empty( $e['rapport_url'] ) ) { $rap = (string) $e['rapport_url']; }
				foreach ( (array) ( $e['checks'] ?? array() ) as $c ) {
					$stt = $c['status'] ?? '';
					if ( 'fail' === $stt || 'warn' === $stt ) {
						$lab = trim( (string) ( $c['name'] ?? '' ) ); // VRAI nom (RGPD, DMARC, mdp, XXE…)
						if ( '' !== $lab && $lab !== $GEN ) { $labels[ $lab ] = ! empty( $c['critical'] ) ? 9.0 : 5.0; }
					}
				}
			}
		}
		// Synthèse Kali (job terminé) → failles FR distinctes.
		$kalitxt = function_exists( 'ag_tester_kali_report_for' ) ? ag_tester_kali_report_for( 'https://' . $host ) : '';
		if ( $kalitxt && function_exists( 'ag_pt_summary_fr' ) ) {
			$modes['expert'] = true;
			$an = ag_pt_summary_fr( $kalitxt );
			foreach ( (array) ( $an['findings'] ?? array() ) as $kf ) {
				$nm = (string) ( $kf['label'] ?? '' ); if ( '' === $nm || $nm === $GEN ) { continue; }
				$sev = (string) ( $kf['sev'] ?? '' );
				$labels[ $nm ] = ( 'crit' === $sev ? 9.0 : ( 'high' === $sev ? 7.0 : ( 'med' === $sev ? 5.0 : 3.0 ) ) );
				if ( 'crit' === $sev ) { $crit = max( $crit, 1 ); }
			}
		} elseif ( $kalitxt ) { $modes['expert'] = true; }
		// Stock riche par site (assistant + Kali collé) : garde type/cvss/rem.
		// + AUTO-NETTOYAGE : une « XSS » dont la preuve est une page normale (sans charge alert(1337)/
		//   alert(document.domain)) = faux positif → purgée automatiquement du stock (aucune action requise).
		$rich = array();
		if ( function_exists( 'ag_rap_get_findings' ) ) {
			$stock = ag_rap_get_findings( $host );
			$purge = array();
			foreach ( $stock as $rf ) {
				$nm = (string) ( $rf['name'] ?? '' ); if ( '' === $nm  || $nm === $GEN ) { continue; }
				if ( ag_rap_is_false_xss( $rf ) ) { $purge[] = $nm; continue; }
				$rich[ $nm ] = array( 'name' => $nm, 'score' => (float) ( $rf['score'] ?? 0 ), 'type' => (string) ( $rf['type'] ?? '' ), 'cvss' => (string) ( $rf['cvss'] ?? '' ), 'rem' => (string) ( $rf['rem'] ?? '' ) );
				if ( (float) ( $rf['score'] ?? 0 ) >= 9 ) { $crit = max( $crit, 1 ); }
			}
			if ( $purge && function_exists( 'ag_rap_find_key' ) ) {
				$key = ag_rap_find_key( $host );
				if ( $key ) {
					$cur = get_option( $key, array() );
					if ( is_array( $cur ) ) {
						$cur = array_values( array_filter( $cur, function ( $f ) use ( $purge ) { return ! in_array( (string) ( $f['name'] ?? '' ), $purge, true ); } ) );
						update_option( $key, $cur, false );
					}
				}
			}
		}
		$findings = array();
		foreach ( $labels as $nm => $sc ) { if ( ! isset( $rich[ $nm ] ) ) { $findings[] = array( 'name' => $nm, 'score' => $sc ); } }
		foreach ( $rich as $rf ) { $findings[] = $rf; }
		return array(
			'findings'    => $findings,
			'modes'       => array_keys( $modes ),
			'score'       => ( $worst <= 100 ? (int) $worst : null ),
			'critical'    => (int) $crit,
			'rapport_url' => $rap,
		);
	}
}

if ( ! function_exists( 'ag_rap_novice_info' ) ) {
	/** Texte client « Ce que c'est / Le risque / Recommandation » par faille — MÊMES textes que l'assistant. */
	function ag_rap_novice_info( $name, $type = '', $rem_in = '' ) {
		$N = array(
			'rce'     => array( 'Un intrus peut exécuter ses propres commandes sur votre serveur.', 'Contrôle total possible : vol de données, sabotage, rançon.' ),
			'sqli'    => array( 'Votre base de données est accessible en lecture/écriture par un intrus.', 'Fuite de vos clients, de leurs mots de passe et de vos commandes.' ),
			'lfi'     => array( 'Des fichiers internes du serveur peuvent être lus à distance.', 'Fuite de configurations et d\'identifiants de connexion.' ),
			'ssrf'    => array( 'Votre serveur peut être détourné pour attaquer votre réseau interne ou votre cloud.', 'Accès à des services privés, vol de clés cloud.' ),
			'idor'    => array( 'Un client peut accéder aux données d\'un autre client.', 'Violation de données personnelles (RGPD).' ),
			'enum'    => array( 'On peut deviner quels emails/comptes existent chez vous.', 'Facilite le hameçonnage ciblé et le bourrage d\'identifiants.' ),
			'xss'     => array( 'Du code malveillant peut s\'exécuter dans le navigateur de vos visiteurs et admins.', 'Vol de session administrateur, prise de contrôle du site.' ),
			'xssr'    => array( 'Un lien piégé peut exécuter du code chez votre visiteur.', 'Vol de session, hameçonnage crédible.' ),
			'upload'  => array( 'Un fichier piégé peut être déposé puis exécuté sur le serveur.', 'Prise de contrôle du serveur.' ),
			'xxe'     => array( 'Un document XML piégé peut lire des fichiers du serveur.', 'Fuite de configurations et de secrets.' ),
			'ssti'    => array( 'Une injection dans les gabarits permet d\'exécuter du code.', 'Prise de contrôle du serveur.' ),
			'auth'    => array( 'L\'authentification présente une faiblesse (jetons, mots de passe).', 'Usurpation de comptes, y compris administrateur.' ),
			'info'    => array( 'Des informations techniques sensibles sont exposées.', 'Facilite la préparation d\'une attaque.' ),
			'leak'    => array( 'Des identifiants liés à vous circulent dans des fuites connues.', 'Connexion frauduleuse possible si des mots de passe sont réutilisés.' ),
			'cookies' => array( 'Un cookie de connexion n\'est pas correctement protégé (HttpOnly/Secure/SameSite).', 'Un intrus pourrait voler une session et se faire passer pour un utilisateur ou un administrateur.' ),
			'cors'    => array( 'La politique CORS du site autorise des origines non prévues.', 'Un site tiers pourrait lire des données de vos utilisateurs connectés.' ),
			'cve'     => array( 'Une extension ou un thème de votre site a une faille de sécurité publiquement connue.', 'Exploitation automatisée par des robots : défaçage, vol de données, prise de contrôle du site.' ),
			'headers' => array( 'Votre site n\'envoie pas les en-têtes de sécurité recommandés.', 'Facilite le détournement d\'affichage (clickjacking) et certaines attaques sur vos visiteurs.' ),
			'tls'     => array( 'La connexion sécurisée (HTTPS) n\'est pas pleinement forcée.', 'Risque d\'interception et perte de confiance (navigateur, référencement).' ),
			'email'   => array( 'Votre domaine n\'est pas protégé contre l\'usurpation d\'e-mail (SPF/DKIM/DMARC).', 'Des e-mails frauduleux peuvent être envoyés en votre nom (phishing à vos clients).' ),
			'rgpd'    => array( 'Votre site n\'est pas conforme au RGPD (cookies, traceurs, mentions).', 'Risque de sanction CNIL et de perte de confiance des visiteurs.' ),
			'perf'    => array( 'Votre site est ralenti (pas de compression/cache, pages lourdes).', 'Visiteurs qui partent, moins de ventes, moins bien référencé.' ),
			'seo'     => array( 'Des bases du référencement technique manquent (titres, descriptions, mobile).', 'Moins de visibilité sur Google, moins de clients potentiels.' ),
			'dns'     => array( 'La configuration DNS de sécurité est incomplète (DNSSEC / CAA).', 'Risque d\'usurpation de domaine ou de certificat pirate.' ),
		);
		$REM = array(
			'headers' => 'Ajouter HSTS, Content-Security-Policy, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy (protège du clickjacking, du MIME-sniffing, des fuites de référent).',
			'leak'    => 'Forcer la réinitialisation des mots de passe des comptes concernés ; activer la 2FA ; interdire les mots de passe déjà vus dans des fuites (vérif type HIBP à l\'inscription) ; surveillance de fuites sur le domaine.',
			'email'   => 'Publier un SPF (-all), signer en DKIM, poser une politique DMARC (p=quarantine puis p=reject).',
			'rgpd'    => 'Bandeau de consentement (CMP) bloquant AVANT tout cookie ; polices hébergées en local ; mentions légales + politique de confidentialité à jour.',
			'seo'     => 'Title + meta description uniques par page ; balise viewport (mobile) ; sitemap + robots.',
			'cookies' => 'Marquer les cookies de session HttpOnly + Secure + SameSite=Lax/Strict ; expiration courte.',
			'cors'    => 'Restreindre Access-Control-Allow-Origin à une liste blanche ; jamais * avec credentials.',
			'tls'     => 'Forcer la redirection HTTP→HTTPS (301), activer HSTS, charger toutes les ressources en https.',
			'cve'     => 'Mettre à jour ou retirer l\'extension/thème vulnérable ; activer les mises à jour automatiques.',
			'xss'     => 'Échapper la sortie selon le contexte ; Content-Security-Policy ; assainir côté serveur.',
			'xssr'    => 'Échapper la sortie ; CSP ; ne jamais réfléchir l\'entrée sans encodage.',
			'sqli'    => 'Requêtes préparées (paramétrées) partout ; ORM ; moindre privilège sur le compte SQL.',
			'xxe'     => 'Désactiver les entités externes / DTD dans le parseur XML.',
			'lfi'     => 'Whitelist des fichiers ; interdire ../ ; désactiver les wrappers dangereux.',
			'rce'     => 'Ne pas construire de commande shell à partir d\'entrées ; API sûres ; whitelist stricte.',
			'info'    => 'Retirer le fichier/endpoint exposé ; contrôle d\'accès ; masquer les versions.',
			'dns'     => 'Activer DNSSEC et publier un enregistrement CAA chez l\'hébergeur DNS.',
		);
		$t = sanitize_key( (string) $type );
		if ( ! isset( $N[ $t ] ) ) {
			$n = mb_strtolower( (string) $name );
			$t = 'info';
			if ( preg_match( '#injection sql|\bsqli\b#u', $n ) ) { $t = 'sqli'; }
			elseif ( preg_match( '#xxe|entité externe#u', $n ) ) { $t = 'xxe'; }
			elseif ( strpos( $n, 'xss' ) !== false ) { $t = 'xss'; }
			elseif ( preg_match( '#mot de passe|identifiant|fuite|compromis|pwned#u', $n ) ) { $t = 'leak'; }
			elseif ( preg_match( '#lecture de fichiers|traversal|\blfi\b#u', $n ) ) { $t = 'lfi'; }
			elseif ( preg_match( '#\brce\b|exécution de code|command#u', $n ) ) { $t = 'rce'; }
			elseif ( strpos( $n, 'ssrf' ) !== false ) { $t = 'ssrf'; }
			elseif ( preg_match( '#en-tête|header#u', $n ) ) { $t = 'headers'; }
			elseif ( preg_match( '#rgpd|conformité#u', $n ) ) { $t = 'rgpd'; }
			elseif ( preg_match( '#dmarc|spf|dkim|usurpation|e-?mail#u', $n ) ) { $t = 'email'; }
			elseif ( strpos( $n, 'seo' ) !== false ) { $t = 'seo'; }
			elseif ( strpos( $n, 'cookie' ) !== false ) { $t = 'cookies'; }
			elseif ( strpos( $n, 'cors' ) !== false ) { $t = 'cors'; }
			elseif ( preg_match( '#contenu mixte|https|tls|en clair|non chiffr#u', $n ) ) { $t = 'tls'; }
			elseif ( preg_match( '#\bcve\b|vulnérab|extension|thème#u', $n ) ) { $t = 'cve'; }
			elseif ( preg_match( '#énumération|comptes#u', $n ) ) { $t = 'enum'; }
			elseif ( preg_match( '#upload|téléversement|fichier non filtr#u', $n ) ) { $t = 'upload'; }
			elseif ( preg_match( '#dnssec|caa#u', $n ) ) { $t = 'dns'; }
			elseif ( preg_match( '#performance#u', $n ) ) { $t = 'perf'; }
		}
		$info = isset( $N[ $t ] ) ? $N[ $t ] : $N['info'];
		$rem  = ( '' !== trim( (string) $rem_in ) ) ? (string) $rem_in : ( isset( $REM[ $t ] ) ? $REM[ $t ] : 'Corriger le point identifié selon les bonnes pratiques ; re-test après correction.' );
		return array( 'r' => $info[0], 'i' => $info[1], 'rem' => $rem );
	}
}

if ( ! function_exists( 'ag_rap_render_client_html' ) ) {
	/** Rendu RICHE du rapport client (MÊME présentation que l'assistant) depuis la source unique. */
	function ag_rap_render_client_html( $host ) {
		if ( ! function_exists( 'ag_rap_all_findings' ) ) { return ''; }
		$agg = ag_rap_all_findings( $host );
		$F = (array) ( $agg['findings'] ?? array() );
		if ( empty( $F ) ) { return ''; }
		// Tri par gravité décroissante.
		usort( $F, function ( $a, $b ) { return ( (float) ( $b['score'] ?? 0 ) ) <=> ( (float) ( $a['score'] ?? 0 ) ); } );
		$sev = function ( $s ) {
			$s = (float) $s;
			if ( $s >= 9 ) { return array( 'Critique', '#dc2626' ); }
			if ( $s >= 7 ) { return array( 'Élevé', '#ea580c' ); }
			if ( $s >= 4 ) { return array( 'Moyen', '#d97706' ); }
			return array( 'Faible', '#2563eb' );
		};
		$worst = 0; foreach ( $F as $f ) { $worst = max( $worst, (float) ( $f['score'] ?? 0 ) ); }
		$ws = $sev( $worst );
		$e = function ( $x ) { return function_exists( 'esc_html' ) ? esc_html( $x ) : htmlspecialchars( (string) $x, ENT_QUOTES ); };
		$h  = '<p style="color:#141414;font-size:14px"><b>' . count( $F ) . ' point(s) de sécurité relevé(s)</b> &mdash; gravité maximale : <b style="color:' . $ws[1] . '">' . $e( $ws[0] ) . '</b>. Voici ce que nous avons trouvé et ce que cela signifie concrètement pour vous.</p>';
		foreach ( $F as $f ) {
			$s  = $sev( $f['score'] ?? 0 );
			$nv = ag_rap_novice_info( (string) ( $f['name'] ?? '' ), (string) ( $f['type'] ?? '' ), (string) ( $f['rem'] ?? '' ) );
			$h .= '<div style="border:1px solid #eee;border-left:5px solid ' . $s[1] . ';border-radius:8px;padding:12px 14px;margin:12px 0">'
				. '<div style="font-weight:800;color:' . $s[1] . '">' . $e( $s[0] ) . ' &mdash; ' . $e( $f['name'] ?? '' ) . '</div>'
				. '<div style="font-size:13.5px;color:#222;margin-top:5px"><b>Ce que c\'est :</b> ' . $e( $nv['r'] ) . '</div>'
				. '<div style="font-size:13.5px;color:#222;margin-top:3px"><b>Le risque pour vous :</b> ' . $e( $nv['i'] ) . '</div>'
				. '<div style="font-size:13.5px;color:#0a7a3f;margin-top:3px"><b>Notre recommandation :</b> ' . $e( $nv['rem'] ) . '</div></div>';
		}
		// Devis de remédiation (MÊME barème que Réglages / l'assistant).
		$px = function_exists( 'ag_aa_prices' ) ? ag_aa_prices() : array( 'critique' => 900, 'eleve' => 500, 'moyen' => 250, 'faible' => 90 );
		$price = function ( $s ) use ( $px ) {
			$s = (float) $s;
			if ( $s >= 9 ) { return (int) $px['critique']; }
			if ( $s >= 7 ) { return (int) $px['eleve']; }
			if ( $s >= 4 ) { return (int) $px['moyen']; }
			return (int) $px['faible'];
		};
		$tot = 0;
		$h .= '<h3 style="color:#141414;border-top:2px solid #eee;padding-top:16px;margin:18px 0 8px">Devis de remédiation</h3>';
		$h .= '<table style="width:100%;border-collapse:collapse;font-size:13px"><thead><tr style="background:#f4f5f7"><th style="text-align:left;padding:8px">Correctif</th><th style="text-align:left;padding:8px">Gravité</th><th style="text-align:right;padding:8px">Prix</th></tr></thead><tbody>';
		foreach ( $F as $f ) {
			$s = $sev( $f['score'] ?? 0 ); $p = $price( $f['score'] ?? 0 ); $tot += $p;
			$h .= '<tr style="border-bottom:1px solid #eee"><td style="padding:8px">' . $e( $f['name'] ?? '' ) . '</td><td style="padding:8px;color:' . $s[1] . ';font-weight:700">' . $e( $s[0] ) . '</td><td style="padding:8px;text-align:right;white-space:nowrap">' . number_format_i18n( $p ) . ' €</td></tr>';
		}
		$h .= '<tr><td colspan="2" style="padding:11px 8px;text-align:right;font-weight:800">Total remédiation</td><td style="padding:11px 8px;text-align:right;font-weight:900;color:#F37A1F;white-space:nowrap">' . number_format_i18n( $tot ) . ' €</td></tr></tbody></table>';
		$h .= '<p style="font-size:11px;color:#888;margin-top:6px">Devis indicatif, valable 30 jours. Correctifs appliqués par Alliance Groupe, re-test inclus.</p>';
		return $h;
	}
}
