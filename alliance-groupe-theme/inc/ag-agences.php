<?php
/**
 * Cartographie « en chaîne » des agences web (conception de sites).
 *
 * Idée : quand on audite le site d'un CRÉATEUR de sites, on découvre ses
 * RÉALISATIONS (les sites de ses clients, listés dans son portfolio / liens
 * sortants), on audite chacune, et on stocke la chaîne :
 *      Agence → [ création1 (note), création2 (note), … ]
 * Les agences sont ensuite classées du PIRE au MEILLEUR (selon leur propre
 * note + la note moyenne de leurs créations). Chaque création est cliquable
 * (voir le site, auditer, prospecter le client).
 *
 * Découverte = heuristique publique (lecture des pages portfolio + liens
 * sortants). Ce n'est jamais exhaustif, mais ça relie l'essentiel.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Le site ressemble-t-il à une agence / un créateur de sites web ? */
if ( ! function_exists( 'ag_is_web_agency' ) ) {
	function ag_is_web_agency( $body ) {
		$b = function_exists( 'remove_accents' ) ? strtolower( remove_accents( (string) $body ) ) : strtolower( (string) $body );
		$kw = array( 'creation de site', 'creation site', 'agence web', 'agence digitale', 'web design', 'webdesign', 'developpeur web', 'developpement web', 'sites internet', 'site internet sur mesure', 'creation de sites', 'refonte de site', 'studio web', 'agence de communication', 'freelance web' );
		$hits = 0;
		foreach ( $kw as $k ) { if ( false !== strpos( $b, $k ) ) $hits++; }
		return $hits >= 2;
	}
}

/** Résout une URL éventuellement relative en URL absolue. */
if ( ! function_exists( 'ag_abs_url' ) ) {
	function ag_abs_url( $link, $base ) {
		$link = trim( (string) $link );
		if ( '' === $link || 0 === strpos( $link, '#' ) || 0 === stripos( $link, 'mailto:' ) || 0 === stripos( $link, 'tel:' ) || 0 === stripos( $link, 'javascript:' ) ) return '';
		if ( preg_match( '#^https?://#i', $link ) ) return $link;
		$p = wp_parse_url( $base );
		if ( empty( $p['scheme'] ) || empty( $p['host'] ) ) return '';
		$root = $p['scheme'] . '://' . $p['host'];
		if ( 0 === strpos( $link, '//' ) ) return $p['scheme'] . ':' . $link;
		if ( 0 === strpos( $link, '/' ) ) return $root . $link;
		return $root . '/' . ltrim( $link, './' );
	}
}

/** Découvre les créations d'une agence : liens externes des pages portfolio/réalisations. */
if ( ! function_exists( 'ag_agency_find_creations' ) ) {
	function ag_agency_find_creations( $url, $body = '' ) {
		if ( ! function_exists( 'ag_audit_fetch' ) ) return array();
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$host = preg_replace( '#^www\.#', '', $host );
		if ( '' === $body ) { $r0 = ag_audit_fetch( $url ); $body = $r0 ? (string) $r0['body'] : ''; }

		// 1) Repère les pages portfolio / réalisations.
		$pages = array( $url );
		if ( preg_match_all( '#href=["\']([^"\']*(?:realisation|portfolio|reference|projet|nos-clients|clients|nos-sites|book)[^"\']*)["\']#i', $body, $pm ) ) {
			foreach ( array_unique( $pm[1] ) as $rel ) {
				$abs = ag_abs_url( $rel, $url );
				if ( $abs ) $pages[] = $abs;
			}
		}
		$pages = array_slice( array_values( array_unique( $pages ) ), 0, 3 );

		// 2) Extrait les liens SORTANTS (autre domaine) de ces pages = créations probables.
		$skip = '#(facebook|instagram|twitter|x\.com|linkedin|youtube|youtu\.be|google\.|gstatic|gravatar|w\.org|wordpress\.org|schema\.org|paypal|maps\.|wa\.me|whatsapp|tiktok|pinterest|snapchat|fonts\.|cdn|jsdelivr|unpkg|cloudflare|gmpg\.org|mozilla|apple\.com|microsoft|adobe|typekit|doubleclick|googletagmanager|analytics)#i';
		$found = array();
		foreach ( $pages as $pu ) {
			$r = ( $pu === $url && '' !== $body ) ? array( 'body' => $body ) : ag_audit_fetch( $pu );
			if ( ! $r || empty( $r['body'] ) ) continue;
			if ( preg_match_all( '#href=["\'](https?://[^"\']+)["\']#i', (string) $r['body'], $lm ) ) {
				foreach ( $lm[1] as $lnk ) {
					$lh = strtolower( (string) wp_parse_url( $lnk, PHP_URL_HOST ) );
					$lh = preg_replace( '#^www\.#', '', $lh );
					if ( '' === $lh || $lh === $host ) continue;
					if ( preg_match( $skip, $lh ) ) continue;
					if ( ! preg_match( '#\.[a-z]{2,}$#i', $lh ) ) continue;
					$found[ $lh ] = 'https://' . $lh . '/';
					if ( count( $found ) >= 12 ) break 2;
				}
			}
		}
		return array_values( $found );
	}
}

/* --------------------------------------------------- AJAX : scanner une agence */
add_action( 'wp_ajax_ag_app_agency_scan', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error( array( 'm' => 'Session expirée.' ) );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	if ( '' === $url || ! function_exists( 'ag_audit_get' ) ) wp_send_json_error( array( 'm' => 'URL invalide.' ) );
	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );

	$r0   = function_exists( 'ag_audit_fetch' ) ? ag_audit_fetch( $url ) : null;
	$body = $r0 ? (string) $r0['body'] : '';
	$a    = ag_audit_get( $url );
	$agency_score = (int) ( $a['score'] ?? 0 );

	$creations_urls = ag_agency_find_creations( $url, $body );
	$creations = array();
	foreach ( array_slice( $creations_urls, 0, 8 ) as $cu ) { // plafond : 8 audits par scan (temps/coût)
		$ca = ag_audit_get( $cu );
		$creations[] = array(
			'url'   => $cu,
			'host'  => preg_replace( '#^www\.#', '', (string) wp_parse_url( $cu, PHP_URL_HOST ) ),
			'score' => (int) ( $ca['score'] ?? 0 ),
			'crit'  => (int) ( $ca['critical'] ?? 0 ),
		);
	}
	usort( $creations, function ( $x, $y ) { return $x['score'] <=> $y['score']; } ); // pires d'abord

	$avg = 0; if ( $creations ) { $s = 0; foreach ( $creations as $c ) $s += $c['score']; $avg = (int) round( $s / count( $creations ) ); }

	$store = (array) get_option( 'ag_agencies', array() );
	$key   = md5( strtolower( trim( $url ) ) );
	$store[ $key ] = array(
		'url'    => $url,
		'host'   => preg_replace( '#^www\.#', '', (string) wp_parse_url( $url, PHP_URL_HOST ) ),
		'name'   => $name,
		'score'  => $agency_score,
		'avg'    => $avg,
		'n'      => count( $creations ),
		'creations' => $creations,
		'ts'     => time(),
	);
	if ( count( $store ) > 120 ) { uasort( $store, function ( $x, $y ) { return (int) ( $y['ts'] ?? 0 ) <=> (int) ( $x['ts'] ?? 0 ); } ); $store = array_slice( $store, 0, 120, true ); }
	update_option( 'ag_agencies', $store, false );

	if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🕸️ Agence cartographiée : ' . ( $name ?: $url ) . ' — ' . count( $creations ) . ' création(s)' );
	wp_send_json_success( array( 'key' => $key, 'agency' => $store[ $key ] ) );
} );

/* AJAX : liste des agences cartographiées (classées du PIRE au MEILLEUR). */
add_action( 'wp_ajax_ag_app_agencies', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$store = (array) get_option( 'ag_agencies', array() );
	$rows  = array();
	foreach ( $store as $k => $row ) { if ( is_array( $row ) ) { $row['key'] = $k; $rows[] = $row; } }
	// Tri : pire d'abord (on combine la note de l'agence et la moyenne de ses créations).
	usort( $rows, function ( $x, $y ) {
		$sx = min( (int) ( $x['score'] ?? 100 ), $x['n'] ? (int) ( $x['avg'] ?? 100 ) : 100 );
		$sy = min( (int) ( $y['score'] ?? 100 ), $y['n'] ? (int) ( $y['avg'] ?? 100 ) : 100 );
		return $sx <=> $sy;
	} );
	wp_send_json_success( array( 'agencies' => array_slice( $rows, 0, 120 ) ) );
} );

add_action( 'wp_ajax_ag_app_agency_del', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$key   = (string) wp_unslash( $_POST['key'] ?? '' );
	$store = (array) get_option( 'ag_agencies', array() );
	if ( isset( $store[ $key ] ) ) { unset( $store[ $key ] ); update_option( 'ag_agencies', $store, false ); }
	wp_send_json_success();
} );
