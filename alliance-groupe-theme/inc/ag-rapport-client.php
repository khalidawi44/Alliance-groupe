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
	echo '<div class="row2"><div><label>Client</label><input type="text" name="ag_rap_client" value="' . ag_rap_field( $post, '_ag_rap_client' ) . '"></div>';
	echo '<div><label>Cible (domaine)</label><input type="text" name="ag_rap_cible" value="' . ag_rap_field( $post, '_ag_rap_cible' ) . '"></div></div>';
	echo '<div class="row2"><div><label>Email client</label><input type="email" name="ag_rap_email" value="' . ag_rap_field( $post, '_ag_rap_email' ) . '" placeholder="contact@client.fr"></div>';
	echo '<div><label>Tél client (SMS)</label><input type="text" name="ag_rap_phone" value="' . ag_rap_field( $post, '_ag_rap_phone' ) . '" placeholder="+33…"></div></div>';
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
	$offre  = isset( $_GET['offre'] ) ? sanitize_key( $_GET['offre'] ) : '';
	// Prix : par-rapport → option globale → défaut raisonnable (zéro config nécessaire).
	$defpx = array( 'rapport' => 290, 'correction' => 1299, 'refonte' => 1490 );
	$rpx = function ( $id, $k ) use ( $defpx ) {
		$m = get_post_meta( $id, '_ag_rap_px_' . $k, true ); if ( '' !== $m ) { return $m; }
		$o = get_option( 'ag_rap_px_' . $k, '' ); if ( '' !== $o ) { return $o; }
		return isset( $defpx[ $k ] ) ? $defpx[ $k ] : '';
	};
	// Lien de paiement : par-rapport → option → sinon GÉNÉRÉ depuis le PayPal déjà configuré (ag_paypal_email).
	$rpy = function ( $id, $k, $price, $label ) {
		$m = get_post_meta( $id, '_ag_rap_pay_' . $k, true ); if ( '' !== $m ) { return $m; }
		$o = get_option( 'ag_rap_pay_' . $k, '' ); if ( '' !== $o ) { return $o; }
		$pp = sanitize_email( (string) get_option( 'ag_paypal_email', '' ) );
		if ( $pp && '' !== $price && (float) $price > 0 ) {
			return 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . rawurlencode( $pp )
				. '&item_name=' . rawurlencode( 'Alliance Groupe — ' . $label )
				. '&amount=' . number_format( (float) $price, 2, '.', '' )
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
	$logo = '<svg viewBox="0 0 120 120" width="46" height="46" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="agg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#E6A93C"/><stop offset="1" stop-color="#F37A1F"/></linearGradient></defs><circle cx="60" cy="60" r="55" fill="#0e1016" stroke="url(#agg)" stroke-width="5"/><text x="60" y="77" text-anchor="middle" font-family="Georgia,serif" font-weight="800" font-size="48" fill="url(#agg)">AG</text></svg>';
	echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Rapport de sécurité — Alliance Groupe</title>';
	echo '<style>body{margin:0;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f4f5f7;color:#141414}.wrap{max-width:820px;margin:0 auto;background:#fff}.hd{display:flex;align-items:center;gap:14px;background:linear-gradient(135deg,#0e1016,#1b2030);padding:20px 24px}.hd .t{color:#fff;font-weight:800;font-size:19px}.hd .s{color:#E6A93C;font-size:13px}.body{padding:8px 24px 28px}.offers{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}.card{flex:1;min-width:210px;border:1px solid #e3e3e3;border-radius:10px;padding:16px}.card.hi{border:2px solid #F37A1F;background:#fff8ec}.card .p{font-size:24px;font-weight:900;margin:6px 0}.btn{display:inline-block;margin-top:8px;background:linear-gradient(135deg,#E6A93C,#F37A1F);color:#241200;font-weight:800;text-decoration:none;padding:12px 18px;border-radius:9px}.btn.d{background:#0e1016;color:#fff}.foot{padding:16px 24px;color:#777;font-size:12px;border-top:1px solid #eee}</style></head><body><div class="wrap">';
	echo '<div class="hd">' . $logo . '<div><div class="t">Alliance Groupe — Rapport de sécurité</div><div class="s">' . esc_html( $client ? $client : 'Client' ) . ( $cible ? ' · ' . esc_html( $cible ) : '' ) . '</div></div></div>';
	echo '<div class="body">';
	if ( $html !== '' ) { echo $html; } // contenu authoré en admin (trusted)
	echo '<h3>Choisissez votre formule</h3><div class="offers">';
	foreach ( $px as $key => $o ) {
		$hi = ( $key === 'correction' || $key === $offre ) ? ' hi' : '';
		$prix = $o[1] !== '' ? number_format_i18n( (float) $o[1], 0 ) . ' €' . ( $key === 'refonte' ? ' et +' : '' ) : 'Sur devis';
		echo '<div class="card' . $hi . '" id="offre-' . esc_attr( $key ) . '"><div style="font-weight:800">' . esc_html( $o[0] ) . '</div><div class="p">' . esc_html( $prix ) . '</div><div style="font-size:13px;color:#555">' . esc_html( $o[3] ) . '</div>';
		if ( $o[2] ) { echo '<a class="btn' . ( $key === 'correction' ? '' : ' d' ) . '" href="' . esc_url( $o[2] ) . '">Payer en ligne</a>'; }
		else { echo '<div style="margin-top:8px;font-size:12px;color:#888">Nous contacter : 07 44 82 95 16</div>'; }
		echo '</div>';
	}
	echo '</div></div>';
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
	function ag_rapport_create( $html, $client = '', $cible = '', $devis = 0, $email = '', $phone = '' ) {
		if ( ! post_type_exists( 'ag_rapport' ) ) { return null; }
		$title = 'Audit sécurité — ' . ( $cible ? $cible : 'cible' ) . ' (' . gmdate( 'Y-m-d' ) . ')';
		$id = wp_insert_post( array(
			'post_type' => 'ag_rapport', 'post_status' => 'publish',
			'post_title' => $title,
		) );
		if ( ! $id || is_wp_error( $id ) ) { return null; }
		update_post_meta( $id, '_ag_rap_html', wp_kses_post( $html ) );
		update_post_meta( $id, '_ag_rap_client', sanitize_text_field( $client ) );
		update_post_meta( $id, '_ag_rap_cible', sanitize_text_field( $cible ) );
		if ( $email ) { update_post_meta( $id, '_ag_rap_email', sanitize_email( $email ) ); }
		if ( $phone ) { update_post_meta( $id, '_ag_rap_phone', sanitize_text_field( $phone ) ); }
		if ( $devis ) { update_post_meta( $id, '_ag_rap_devis', (int) $devis ); }
		$tok = get_post_meta( $id, '_ag_rap_token', true );
		if ( ! $tok ) { $tok = wp_generate_password( 22, false, false ); update_post_meta( $id, '_ag_rap_token', $tok ); }
		return array( 'id' => (int) $id, 'url' => home_url( '/rapport/' . $tok ) );
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
			$r = ag_rapport_create( $safe, $client, $cible, $devis, $email, $phone );
			if ( ! $r ) return new WP_Error( 'ag_fail', 'Création impossible.', array( 'status' => 500 ) );
			return array( 'ok' => true, 'id' => $r['id'], 'url' => $r['url'] );
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
} );
