<?php
/**
 * AG Push — VRAIES notifications Web Push (VAPID + RFC 8291/8188), reçues même
 * application fermée, sur Android et iOS 16.4+ (app installée).
 *
 * - Génère une paire de clés VAPID (une fois) et la conserve.
 * - REST : récupérer la clé publique, s'abonner, se désabonner.
 * - Écran admin « 🔔 Notifications » : envoyer un titre + message + lien à tous
 *   les abonnés (ou par audience), avec compteur d'envois.
 * - Chiffrement Web Push fait en PHP natif (openssl + hash_hkdf), sans librairie.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------- Base64 URL */
if ( ! function_exists( 'ag_b64u' ) ) {
	function ag_b64u( $d ) { return rtrim( strtr( base64_encode( $d ), '+/', '-_' ), '=' ); }
}
if ( ! function_exists( 'ag_b64u_dec' ) ) {
	function ag_b64u_dec( $d ) { return base64_decode( strtr( $d, '-_', '+/' ) . str_repeat( '=', ( 4 - strlen( $d ) % 4 ) % 4 ) ); }
}

/* ---------------------------------------------------------------- Clés VAPID */
if ( ! function_exists( 'ag_vapid_keys' ) ) {
	/** Renvoie [public_b64u, private_pem] ; les génère une fois si absentes. */
	function ag_vapid_keys() {
		$pub = get_option( 'ag_vapid_pub', '' );
		$prv = get_option( 'ag_vapid_priv', '' );
		if ( $pub && $prv ) return array( $pub, $prv );
		$res = openssl_pkey_new( array( 'private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1' ) );
		if ( ! $res ) return array( '', '' );
		openssl_pkey_export( $res, $prv );
		$d   = openssl_pkey_get_details( $res );
		$x   = str_pad( $d['ec']['x'], 32, "\0", STR_PAD_LEFT );
		$y   = str_pad( $d['ec']['y'], 32, "\0", STR_PAD_LEFT );
		$pub = ag_b64u( "\x04" . $x . $y );
		update_option( 'ag_vapid_pub', $pub, false );
		update_option( 'ag_vapid_priv', $prv, false );
		return array( $pub, $prv );
	}
}

/* ---------------------------------------------------------------- Abonnements */
if ( ! function_exists( 'ag_push_subs_get' ) ) {
	function ag_push_subs_get() { $d = get_option( 'ag_push_subs', array() ); return is_array( $d ) ? $d : array(); }
}
if ( ! function_exists( 'ag_push_sub_key' ) ) {
	function ag_push_sub_key( $endpoint ) { return substr( hash( 'sha256', $endpoint ), 0, 24 ); }
}

/* ---------------------------------------------------------------- REST */
add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/vapid', array(
		'methods' => 'GET', 'permission_callback' => '__return_true',
		'callback' => function () { list( $pub ) = ag_vapid_keys(); return array( 'key' => $pub ); },
	) );
	register_rest_route( 'ag/v1', '/push-subscribe', array(
		'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'ag_push_subscribe_rest',
	) );
	register_rest_route( 'ag/v1', '/push-unsubscribe', array(
		'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'ag_push_unsubscribe_rest',
	) );
} );
if ( ! function_exists( 'ag_push_subscribe_rest' ) ) {
	function ag_push_subscribe_rest( $req ) {
		$sub = $req->get_param( 'sub' );
		if ( is_string( $sub ) ) $sub = json_decode( $sub, true );
		$endpoint = isset( $sub['endpoint'] ) ? esc_url_raw( $sub['endpoint'] ) : '';
		$p256dh   = isset( $sub['keys']['p256dh'] ) ? sanitize_text_field( $sub['keys']['p256dh'] ) : '';
		$auth     = isset( $sub['keys']['auth'] ) ? sanitize_text_field( $sub['keys']['auth'] ) : '';
		if ( '' === $endpoint || '' === $p256dh || '' === $auth ) return new WP_REST_Response( array( 'error' => 'bad' ), 400 );
		$aud  = sanitize_text_field( (string) $req->get_param( 'app' ) ) ?: 'site';
		$list = ag_push_subs_get();
		$list[ ag_push_sub_key( $endpoint ) ] = array(
			'endpoint' => $endpoint, 'p256dh' => $p256dh, 'auth' => $auth,
			'aud' => ( 'amb' === $aud ? 'amb' : 'site' ), 'uid' => get_current_user_id(), 'ts' => time(),
		);
		update_option( 'ag_push_subs', $list, false );
		return array( 'ok' => true );
	}
}
if ( ! function_exists( 'ag_push_unsubscribe_rest' ) ) {
	function ag_push_unsubscribe_rest( $req ) {
		$endpoint = esc_url_raw( (string) $req->get_param( 'endpoint' ) );
		if ( '' === $endpoint ) return new WP_REST_Response( array( 'error' => 'bad' ), 400 );
		$list = ag_push_subs_get();
		unset( $list[ ag_push_sub_key( $endpoint ) ] );
		update_option( 'ag_push_subs', $list, false );
		return array( 'ok' => true );
	}
}

/* ---------------------------------------------------------------- Envoi Web Push */
if ( ! function_exists( 'ag_der_to_raw_sig' ) ) {
	/** Signature ECDSA DER → 64 octets bruts (R||S) pour ES256. */
	function ag_der_to_raw_sig( $der ) {
		$off = 0;
		if ( "\x30" !== $der[ $off++ ] ) return ''; // SEQUENCE
		if ( ord( $der[ $off ] ) & 0x80 ) { $off += ( ord( $der[ $off ] ) & 0x7f ) + 1; } else { $off++; }
		$read = function () use ( $der, &$off ) {
			if ( "\x02" !== $der[ $off++ ] ) return '';
			$len = ord( $der[ $off++ ] );
			$v   = substr( $der, $off, $len ); $off += $len;
			$v   = ltrim( $v, "\x00" );
			return str_pad( $v, 32, "\x00", STR_PAD_LEFT );
		};
		return $read() . $read();
	}
}
if ( ! function_exists( 'ag_vapid_jwt' ) ) {
	function ag_vapid_jwt( $audience, $priv_pem, $sub_email = '' ) {
		$header = ag_b64u( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'ES256' ) ) );
		$claims = ag_b64u( wp_json_encode( array(
			'aud' => $audience,
			'exp' => time() + 12 * HOUR_IN_SECONDS,
			'sub' => $sub_email ?: ( 'mailto:' . get_option( 'admin_email' ) ),
		) ) );
		$input = $header . '.' . $claims;
		$der   = '';
		openssl_sign( $input, $der, $priv_pem, OPENSSL_ALGO_SHA256 );
		return $input . '.' . ag_b64u( ag_der_to_raw_sig( $der ) );
	}
}
if ( ! function_exists( 'ag_webpush_send' ) ) {
	/** Envoie 1 notification chiffrée (aes128gcm) à un abonnement. */
	function ag_webpush_send( $sub, $payload_json ) {
		list( $vpub, $vpriv ) = ag_vapid_keys();
		if ( '' === $vpriv ) return array( 0, 'no_vapid' );
		$endpoint = $sub['endpoint'];
		$ua_pub   = ag_b64u_dec( $sub['p256dh'] );      // 65 octets
		$auth     = ag_b64u_dec( $sub['auth'] );         // 16 octets
		if ( 65 !== strlen( $ua_pub ) ) return array( 0, 'badkey' );

		// Clé éphémère serveur (P-256).
		$as = openssl_pkey_new( array( 'private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1' ) );
		$ad = openssl_pkey_get_details( $as );
		$as_pub = "\x04" . str_pad( $ad['ec']['x'], 32, "\0", STR_PAD_LEFT ) . str_pad( $ad['ec']['y'], 32, "\0", STR_PAD_LEFT );

		// PEM de la clé publique de l'abonné (préfixe SPKI P-256 fixe).
		$spki = hex2bin( '3059301306072a8648ce3d020106082a8648ce3d030107034200' ) . $ua_pub;
		$ua_pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----\n";

		$ecdh = openssl_pkey_derive( $ua_pem, $as, 32 );
		if ( ! $ecdh ) return array( 0, 'ecdh' );

		$salt = random_bytes( 16 );
		$ikm  = hash_hkdf( 'sha256', $ecdh, 32, "WebPush: info\x00" . $ua_pub . $as_pub, $auth );
		$cek  = hash_hkdf( 'sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt );
		$nonce = hash_hkdf( 'sha256', $ikm, 12, "Content-Encoding: nonce\x00", $salt );

		$plain = $payload_json . "\x02"; // délimiteur de dernier enregistrement (RFC 8188)
		$tag   = '';
		$cipher = openssl_encrypt( $plain, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag );
		if ( false === $cipher ) return array( 0, 'enc' );

		// Bloc d'en-tête aes128gcm : salt(16) | rs(4) | idlen(1)=65 | keyid(65)
		$body = $salt . pack( 'N', 4096 ) . chr( 65 ) . $as_pub . $cipher . $tag;

		$aud = wp_parse_url( $endpoint, PHP_URL_SCHEME ) . '://' . wp_parse_url( $endpoint, PHP_URL_HOST );
		$jwt = ag_vapid_jwt( $aud, $vpriv );

		$resp = wp_remote_post( $endpoint, array(
			'timeout' => 20,
			'headers' => array(
				'Authorization'    => 'vapid t=' . $jwt . ', k=' . $vpub,
				'Content-Encoding' => 'aes128gcm',
				'Content-Type'     => 'application/octet-stream',
				'TTL'              => '2419200',
				'Urgency'          => 'normal',
			),
			'body'    => $body,
		) );
		if ( is_wp_error( $resp ) ) return array( 0, $resp->get_error_message() );
		$code = (int) wp_remote_retrieve_response_code( $resp );
		return array( in_array( $code, array( 200, 201, 202 ), true ) ? 1 : 0, $code );
	}
}
if ( ! function_exists( 'ag_push_broadcast' ) ) {
	/** Envoie à tous les abonnés d'une audience. Nettoie les abonnements morts (404/410). */
	function ag_push_broadcast( $title, $body, $url = '', $audience = 'all' ) {
		$subs = ag_push_subs_get();
		$payload = wp_json_encode( array( 'title' => $title, 'body' => $body, 'url' => $url ?: home_url( '/' ), 'icon' => ag_pwa_icon( 'icon-192.png' ) ) );
		$ok = 0; $ko = 0; $dead = array();
		foreach ( $subs as $k => $s ) {
			if ( 'all' !== $audience && ( $s['aud'] ?? 'site' ) !== $audience ) continue;
			list( $sent, $code ) = ag_webpush_send( $s, $payload );
			if ( $sent ) { $ok++; } else { $ko++; if ( in_array( (int) $code, array( 404, 410 ), true ) ) $dead[] = $k; }
			usleep( 120000 );
		}
		if ( $dead ) { foreach ( $dead as $k ) unset( $subs[ $k ] ); update_option( 'ag_push_subs', $subs, false ); }
		return array( $ok, $ko );
	}
}

/* ---------------------------------------------------------------- Admin : envoyer */
add_action( 'admin_menu', function () {
	add_menu_page( 'Notifications', '🔔 Notifications', 'manage_options', 'ag-push', 'ag_push_admin', 'dashicons-bell', 32 );
} );
if ( ! function_exists( 'ag_push_admin' ) ) {
	function ag_push_admin() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$notice = '';
		if ( isset( $_POST['ag_push_send'] ) && check_admin_referer( 'ag_push' ) ) {
			$t = sanitize_text_field( wp_unslash( $_POST['t'] ?? '' ) );
			$b = sanitize_text_field( wp_unslash( $_POST['b'] ?? '' ) );
			$u = esc_url_raw( wp_unslash( $_POST['u'] ?? '' ) );
			$a = sanitize_text_field( wp_unslash( $_POST['a'] ?? 'all' ) );
			if ( $t || $b ) {
				list( $ok, $ko ) = ag_push_broadcast( $t ?: 'Alliance Groupe', $b, $u, in_array( $a, array( 'all', 'site', 'amb' ), true ) ? $a : 'all' );
				$notice = '✅ Envoyé : ' . $ok . ' · échecs : ' . $ko;
			}
		}
		list( $vpub ) = ag_vapid_keys();
		$subs = ag_push_subs_get();
		$nb_amb = count( array_filter( $subs, function ( $s ) { return 'amb' === ( $s['aud'] ?? '' ); } ) );
		echo '<div class="wrap"><h1>🔔 Notifications push</h1>';
		if ( $notice ) echo '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>';
		echo '<p style="max-width:820px;color:#50575e;">Envoie une vraie notification (reçue <strong>même app fermée</strong>) à ceux qui ont activé les notifs. Fonctionne sur Android et iPhone (iOS 16.4+, app installée).</p>';
		echo '<p><strong>' . count( $subs ) . '</strong> abonné(s) · dont <strong>' . (int) $nb_amb . '</strong> via l’app Ambassadeurs.' . ( $vpub ? '' : ' <span style="color:#b32d2e">⚠ Clé VAPID non générée.</span>' ) . '</p>';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo '<tr><th>Titre</th><td><input type="text" name="t" style="width:480px" placeholder="🔥 Nouvelle offre !"></td></tr>';
		echo '<tr><th>Message</th><td><input type="text" name="b" style="width:560px" placeholder="-20% sur les Sites Express ce week-end."></td></tr>';
		echo '<tr><th>Lien à l’ouverture</th><td><input type="text" name="u" style="width:560px" placeholder="https://alliancegroupe-inc.com/sites-express"></td></tr>';
		echo '<tr><th>Destinataires</th><td><select name="a"><option value="all">Tous les abonnés</option><option value="site">App du site</option><option value="amb">App Ambassadeurs</option></select></td></tr>';
		echo '</tbody></table>';
		wp_nonce_field( 'ag_push' );
		echo '<button name="ag_push_send" value="1" class="button button-primary">Envoyer la notification</button></form>';
		echo '</div>';
	}
}

/* ---------------------------------------------------------------- Client : activer + s'abonner */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	list( $vpub ) = ag_vapid_keys();
	if ( '' === $vpub ) return;
	?>
	<script>
	(function(){
		window.AG_VAPID = <?php echo wp_json_encode( $vpub ); ?>;
		function u8(b){ b=(b+'='.repeat((4-b.length%4)%4)).replace(/-/g,'+').replace(/_/g,'/'); var r=atob(b),a=new Uint8Array(r.length); for(var i=0;i<r.length;i++)a[i]=r.charCodeAt(i); return a; }
		var amb=''; try{ amb=new URLSearchParams(location.search).get('ag_app')||sessionStorage.getItem('ag_app')||''; }catch(e){}
		window.agEnableNotifs = function(){
			if (!('serviceWorker' in navigator) || !('PushManager' in window)) { alert('Notifications non supportées sur ce navigateur. Sur iPhone : installe d’abord l’app (écran d’accueil).'); return; }
			Notification.requestPermission().then(function(p){
				if (p !== 'granted') { alert('Notifications refusées. Tu peux les réactiver dans les réglages.'); return; }
				navigator.serviceWorker.ready.then(function(reg){
					return reg.pushManager.subscribe({ userVisibleOnly:true, applicationServerKey: u8(window.AG_VAPID) });
				}).then(function(sub){
					var fd=new FormData(); fd.append('sub', JSON.stringify(sub)); fd.append('app', amb==='amb'?'amb':'site');
					return fetch('<?php echo esc_url( rest_url( 'ag/v1/push-subscribe' ) ); ?>', { method:'POST', body:fd, credentials:'same-origin' });
				}).then(function(){ alert('🔔 Notifications activées ! Tu recevras nos offres et nouveautés.'); })
				.catch(function(){ alert('Erreur lors de l’activation. Réessaie.'); });
			});
		};
	})();
	</script>
	<?php
}, 6 );

/* Bouton « Activer les notifications » (shortcode + auto dans l'app ambassadeur). */
add_shortcode( 'ag_notif', function () {
	return '<button type="button" class="ag-notif-btn" onclick="agEnableNotifs()" style="display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;cursor:pointer;font-family:inherit">🔔 Activer les notifications</button>';
} );
