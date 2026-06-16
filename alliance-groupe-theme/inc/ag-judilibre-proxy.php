<?php
/**
 * AG Judilibre — RELAIS MUTUALISÉ (site mère).
 *
 * Alliance Groupe héberge UNE clé PISTE (production) ici ; tous les sites
 * clients du plugin « AG Recherche Juridique » qui n'ont PAS leur propre clé
 * interrogent ce relais → recherche jurisprudence incluse, AUCUNE config client.
 *
 * Endpoints (POST) :
 *   /wp-json/ag/v1/judilibre            { q, jur, sort, order, ymin, ymax, solution, theme, pub }
 *   /wp-json/ag/v1/judilibre-decision   { id }
 * Réponse : { "judilibre": <corps brut Judilibre> }  ou  { "error": "…" }.
 *
 * Sécurité/coût : limite par IP + cache des résultats (la jurisprudence est de
 * l'open data ; partager l'accès via un relais est conforme). Force toujours la
 * PRODUCTION (le sandbox a un index vide).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_jrp_opt' ) ) {
	function ag_jrp_opt( $k, $d = '' ) {
		// Priorité aux constantes wp-config (secrets), sinon options admin.
		$const = 'AG_JRP_' . strtoupper( $k );
		if ( defined( $const ) && '' !== (string) constant( $const ) ) {
			return trim( (string) constant( $const ) );
		}
		return trim( (string) get_option( 'ag_jrp_' . $k, $d ) );
	}
}

/* ── OAuth PISTE (corps puis Basic), token caché, URLs forcées production ── */
if ( ! function_exists( 'ag_jrp_token' ) ) {
	function ag_jrp_token() {
		$id     = ag_jrp_opt( 'id' );
		$secret = ag_jrp_opt( 'secret' );
		if ( ! $id || ! $secret ) {
			return new WP_Error( 'cfg', 'Relais Judilibre non configuré (clé PISTE manquante côté Alliance Groupe).' );
		}
		$cached = get_transient( 'ag_jrp_token' );
		if ( $cached ) {
			return $cached;
		}
		$oauth = ag_jrp_opt( 'oauth', 'https://oauth.piste.gouv.fr/api/oauth/token' );
		$oauth = str_replace( array( 'sandbox-oauth.piste.gouv.fr', 'aife.economie.gouv.fr' ), array( 'oauth.piste.gouv.fr', 'piste.gouv.fr' ), $oauth );
		$scope = ag_jrp_opt( 'scope', 'openid' );
		$attempts = array(
			array( 'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ), 'body' => array( 'grant_type' => 'client_credentials', 'client_id' => $id, 'client_secret' => $secret, 'scope' => $scope ) ),
			array( 'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded', 'Authorization' => 'Basic ' . base64_encode( $id . ':' . $secret ) ), 'body' => array( 'grant_type' => 'client_credentials', 'scope' => $scope ) ),
		);
		$last = '';
		foreach ( $attempts as $a ) {
			$r = wp_remote_post( $oauth, array( 'timeout' => 20, 'headers' => $a['headers'], 'body' => $a['body'] ) );
			if ( is_wp_error( $r ) ) {
				$last = $r->get_error_message();
				continue;
			}
			$raw = wp_remote_retrieve_body( $r );
			$d   = json_decode( $raw, true );
			if ( ! empty( $d['access_token'] ) ) {
				$ttl = isset( $d['expires_in'] ) ? max( 60, intval( $d['expires_in'] ) - 60 ) : 1800;
				set_transient( 'ag_jrp_token', $d['access_token'], $ttl );
				return $d['access_token'];
			}
			$last = $raw;
			if ( false === strpos( (string) $raw, 'invalid_client' ) ) {
				break;
			}
		}
		return new WP_Error( 'oauth', 'Relais : authentification PISTE refusée (OAuth ' . $oauth . '). Réponse : ' . substr( (string) $last, 0, 220 ) );
	}
}

/* ── Diagnostic : GET /wp-json/ag/v1/judilibre-status ── */
if ( ! function_exists( 'ag_jrp_rest_status' ) ) {
	function ag_jrp_rest_status( WP_REST_Request $req ) {
		$id     = ag_jrp_opt( 'id' );
		$secret = ag_jrp_opt( 'secret' );
		$out = array(
			'relais'     => 'AG Judilibre',
			'configure'  => ( $id && $secret ) ? true : false,
			'client_id'  => $id ? ( substr( $id, 0, 6 ) . '…' ) : 'ABSENT',
			'keyid'      => ag_jrp_opt( 'apikey' ) ? 'présent' : 'absent',
			'oauth_url'  => ag_jrp_opt( 'oauth', 'https://oauth.piste.gouv.fr/api/oauth/token' ),
		);
		if ( ! $out['configure'] ) {
			$out['token'] = '⛔ Clé PISTE manquante côté relais (Réglages → ⚖️ Relais Judilibre).';
			return $out;
		}
		$t = ag_jrp_token();
		$out['token'] = is_wp_error( $t ) ? ( '❌ ' . $t->get_error_message() ) : ( '✅ OK (' . substr( $t, 0, 6 ) . '…)' );
		return $out;
	}
}

/* ── Limite anti-abus par IP (protège le quota PISTE mutualisé) ── */
if ( ! function_exists( 'ag_jrp_rate_limited' ) ) {
	function ag_jrp_rate_limited() {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-f:.]/i', '', wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
		$k   = 'ag_jrp_rl_' . md5( $ip );
		$n   = (int) get_transient( $k );
		$max = (int) ag_jrp_opt( 'rate', '120' );
		if ( $max < 1 ) { $max = 120; }
		if ( $n >= $max ) {
			return true;
		}
		set_transient( $k, $n + 1, HOUR_IN_SECONDS );
		return false;
	}
}

/* ── Endpoint : RECHERCHE ── */
if ( ! function_exists( 'ag_jrp_rest_search' ) ) {
	function ag_jrp_rest_search( WP_REST_Request $req ) {
		if ( ag_jrp_rate_limited() ) {
			return new WP_REST_Response( array( 'error' => 'Trop de requêtes, réessayez dans un instant.' ), 429 );
		}
		$q = sanitize_text_field( (string) $req->get_param( 'q' ) );
		if ( '' === $q ) {
			return array( 'judilibre' => array( 'results' => array(), 'total' => 0 ) );
		}
		$p = array(
			'q'        => $q,
			'jur'      => sanitize_text_field( (string) $req->get_param( 'jur' ) ),
			'sort'     => sanitize_text_field( (string) $req->get_param( 'sort' ) ),
			'order'    => sanitize_text_field( (string) $req->get_param( 'order' ) ),
			'ymin'     => intval( $req->get_param( 'ymin' ) ),
			'ymax'     => intval( $req->get_param( 'ymax' ) ),
			'solution' => sanitize_text_field( (string) $req->get_param( 'solution' ) ),
			'theme'    => sanitize_text_field( (string) $req->get_param( 'theme' ) ),
			'pub'      => $req->get_param( 'pub' ) ? '1' : '',
		);
		$ck     = 'ag_jrp_s_' . md5( wp_json_encode( $p ) );
		$cached = get_transient( $ck );
		if ( false !== $cached ) {
			return array( 'judilibre' => $cached );
		}
		$token = ag_jrp_token();
		if ( is_wp_error( $token ) ) {
			return new WP_REST_Response( array( 'error' => $token->get_error_message() ), 502 );
		}
		$base = ag_jrp_opt( 'search_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' );
		$base = str_replace( 'sandbox-api.piste.gouv.fr', 'api.piste.gouv.fr', $base );
		$args = array( 'query' => $p['q'], 'page_size' => 20, 'resolve_references' => 'true' );
		if ( 'date' === $p['sort'] ) {
			$args['sort']  = 'date';
			$args['order'] = ( 'asc' === $p['order'] ) ? 'asc' : 'desc';
		}
		if ( $p['ymin'] >= 1900 && $p['ymin'] <= 2100 ) { $args['date_start'] = sprintf( '%04d-01-01', $p['ymin'] ); }
		if ( $p['ymax'] >= 1900 && $p['ymax'] <= 2100 ) { $args['date_end'] = sprintf( '%04d-12-31', $p['ymax'] ); }
		$url = add_query_arg( $args, $base );
		if ( $p['jur'] )      { $url .= '&jurisdiction=' . rawurlencode( $p['jur'] ); }
		if ( $p['pub'] )      { $url .= '&publication=b&publication=r'; }
		if ( $p['solution'] ) { $url .= '&solution=' . rawurlencode( $p['solution'] ); }
		if ( $p['theme'] )    { $url .= '&theme=' . rawurlencode( $p['theme'] ); }
		$headers = array( 'Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json' );
		$apikey  = ag_jrp_opt( 'apikey' );
		if ( $apikey ) { $headers['KeyId'] = $apikey; }
		$resp = wp_remote_get( $url, array( 'timeout' => 25, 'headers' => $headers ) );
		if ( is_wp_error( $resp ) ) {
			return new WP_REST_Response( array( 'error' => 'Judilibre momentanément indisponible.' ), 502 );
		}
		$code = wp_remote_retrieve_response_code( $resp );
		$raw  = wp_remote_retrieve_body( $resp );
		$body = json_decode( $raw, true );
		if ( $code >= 400 || ! is_array( $body ) ) {
			return new WP_REST_Response( array( 'error' => 'Judilibre a renvoyé une erreur (HTTP ' . $code . ') : ' . substr( (string) $raw, 0, 200 ) ), 502 );
		}
		set_transient( $ck, $body, 6 * HOUR_IN_SECONDS );
		return array( 'judilibre' => $body );
	}
}

/* ── Endpoint : TEXTE INTÉGRAL D'UNE DÉCISION ── */
if ( ! function_exists( 'ag_jrp_rest_decision' ) ) {
	function ag_jrp_rest_decision( WP_REST_Request $req ) {
		if ( ag_jrp_rate_limited() ) {
			return new WP_REST_Response( array( 'error' => 'Trop de requêtes, réessayez dans un instant.' ), 429 );
		}
		$id = sanitize_text_field( (string) $req->get_param( 'id' ) );
		if ( '' === $id ) {
			return new WP_REST_Response( array( 'error' => 'Identifiant manquant.' ), 400 );
		}
		$ck     = 'ag_jrp_d_' . md5( $id );
		$cached = get_transient( $ck );
		if ( false !== $cached ) {
			return array( 'judilibre' => $cached );
		}
		$token = ag_jrp_token();
		if ( is_wp_error( $token ) ) {
			return new WP_REST_Response( array( 'error' => $token->get_error_message() ), 502 );
		}
		$base = ag_jrp_opt( 'search_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' );
		$base = str_replace( array( 'sandbox-api.piste.gouv.fr', '/search' ), array( 'api.piste.gouv.fr', '/decision' ), $base );
		$headers = array( 'Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json' );
		$apikey  = ag_jrp_opt( 'apikey' );
		if ( $apikey ) { $headers['KeyId'] = $apikey; }
		$resp = wp_remote_get( add_query_arg( array( 'id' => $id ), $base ), array( 'timeout' => 25, 'headers' => $headers ) );
		if ( is_wp_error( $resp ) ) {
			return new WP_REST_Response( array( 'error' => 'Judilibre momentanément indisponible.' ), 502 );
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! is_array( $body ) ) {
			return new WP_REST_Response( array( 'error' => 'Réponse invalide.' ), 502 );
		}
		set_transient( $ck, $body, DAY_IN_SECONDS );
		return array( 'judilibre' => $body );
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/judilibre', array(
		'methods'             => 'POST',
		'callback'            => 'ag_jrp_rest_search',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'ag/v1', '/judilibre-decision', array(
		'methods'             => 'POST',
		'callback'            => 'ag_jrp_rest_decision',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'ag/v1', '/judilibre-status', array(
		'methods'             => 'GET',
		'callback'            => 'ag_jrp_rest_status',
		'permission_callback' => '__return_true',
	) );
} );

/* ── Réglages (site mère) : Réglages → ⚖️ Relais Judilibre ── */
add_action( 'admin_menu', function () {
	add_options_page( 'Relais Judilibre', '⚖️ Relais Judilibre', 'manage_options', 'ag-jrp', 'ag_jrp_settings_page' );
} );
if ( ! function_exists( 'ag_jrp_settings_page' ) ) {
	function ag_jrp_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_POST['ag_jrp_save'] ) && check_admin_referer( 'ag_jrp' ) ) {
			update_option( 'ag_jrp_id',         sanitize_text_field( wp_unslash( $_POST['ag_jrp_id'] ?? '' ) ) );
			update_option( 'ag_jrp_secret',     sanitize_text_field( wp_unslash( $_POST['ag_jrp_secret'] ?? '' ) ) );
			update_option( 'ag_jrp_apikey',     sanitize_text_field( wp_unslash( $_POST['ag_jrp_apikey'] ?? '' ) ) );
			update_option( 'ag_jrp_oauth',      esc_url_raw( wp_unslash( $_POST['ag_jrp_oauth'] ?? '' ) ) );
			update_option( 'ag_jrp_search_url', esc_url_raw( wp_unslash( $_POST['ag_jrp_search_url'] ?? '' ) ) );
			update_option( 'ag_jrp_rate',       max( 1, intval( $_POST['ag_jrp_rate'] ?? 120 ) ) );
			delete_transient( 'ag_jrp_token' );
			echo '<div class="notice notice-success"><p>✅ Relais enregistré.</p></div>';
		}
		$g = function ( $k, $d = '' ) { return esc_attr( get_option( 'ag_jrp_' . $k, $d ) ); };
		echo '<div class="wrap"><h1>⚖️ Relais Judilibre (clé mutualisée)</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Colle ICI ta clé PISTE <strong>production</strong> <u>une seule fois</u>. Tous les sites clients du plugin « AG Recherche Juridique » sans clé propre passeront par ce relais → recherche jurisprudence incluse, <strong>aucune config côté client</strong>.</p>';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo '<tr><th>Client ID (OAuth)</th><td><input type="text" name="ag_jrp_id" value="' . $g( 'id' ) . '" class="regular-text"></td></tr>';
		echo '<tr><th>Client Secret (OAuth)</th><td><input type="text" name="ag_jrp_secret" value="' . $g( 'secret' ) . '" class="regular-text"><p class="description">Bouton « Consulter le secret » de la section <em>Identifiants OAuth</em> sur PISTE.</p></td></tr>';
		echo '<tr><th>KeyId / clé API</th><td><input type="text" name="ag_jrp_apikey" value="' . $g( 'apikey' ) . '" class="regular-text"></td></tr>';
		echo '<tr><th>OAuth URL</th><td><input type="text" name="ag_jrp_oauth" value="' . $g( 'oauth', 'https://oauth.piste.gouv.fr/api/oauth/token' ) . '" class="large-text"></td></tr>';
		echo '<tr><th>Judilibre URL</th><td><input type="text" name="ag_jrp_search_url" value="' . $g( 'search_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' ) . '" class="large-text"></td></tr>';
		echo '<tr><th>Limite / heure / IP</th><td><input type="number" name="ag_jrp_rate" value="' . esc_attr( get_option( 'ag_jrp_rate', 120 ) ) . '" min="1" style="width:90px"> <span class="description">requêtes max par client et par heure (anti-abus).</span></td></tr>';
		echo '</tbody></table>';
		wp_nonce_field( 'ag_jrp' );
		echo '<button name="ag_jrp_save" value="1" class="button button-primary">Enregistrer</button>';
		echo ' <a href="' . esc_url( rest_url( 'ag/v1/judilibre-status' ) ) . '" target="_blank" rel="noopener" class="button" style="margin-left:8px;">🔎 Tester le relais (diagnostic)</a>';
		echo '</form>';
		echo '<p style="color:#50575e;font-size:.9em;margin-top:14px;">💡 Pour plus de sécurité, tu peux définir <code>AG_JRP_ID</code> / <code>AG_JRP_SECRET</code> / <code>AG_JRP_APIKEY</code> dans <code>wp-config.php</code> (les constantes priment sur ces champs).</p>';
		echo '</div>';
	}
}
