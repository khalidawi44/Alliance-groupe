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

/** Liste des domaines/plateformes à ignorer (réseaux, tech, CDN, partenaires courants). */
if ( ! function_exists( 'ag_agency_skip_re' ) ) {
	function ag_agency_skip_re() {
		return '#(facebook|instagram|twitter|x\.com|linkedin|youtube|youtu\.be|google\.|gstatic|gravatar|w\.org|wordpress\.org|schema\.org|paypal|maps\.|wa\.me|whatsapp|tiktok|pinterest|snapchat|vimeo|fonts\.|cdn|jsdelivr|unpkg|cloudflare|cloudflareinsights|gmpg\.org|mozilla|apple\.com|microsoft|adobe|typekit|doubleclick|googletagmanager|analytics|frenchtech|cookiedatabase|shopify\.com|wix\.com|webflow\.(io|com)|elementor|wpengine|sentry|hotjar|matomo|plage-web|malt\.fr|sortlist|codeur\.com|trustpilot)#i';
	}
}

/** Nom lisible déduit d'un host (algosource.com -> Algosource). */
if ( ! function_exists( 'ag_agency_name_from_host' ) ) {
	function ag_agency_name_from_host( $host ) {
		$h = preg_replace( '#^www\.#', '', strtolower( (string) $host ) );
		$label = explode( '.', $h );
		$label = str_replace( array( '-', '_' ), ' ', (string) ( $label[0] ?? $h ) );
		return ucwords( trim( $label ) );
	}
}

/** Sur une FICHE PROJET, trouve le site CLIENT (priorité aux liens « découvrir / voir le site »). */
if ( ! function_exists( 'ag_agency_client_from_case' ) ) {
	function ag_agency_client_from_case( $body, $case_url, $agency_host ) {
		$skip = ag_agency_skip_re();
		$best = ''; $best_score = 0; $first = '';
		if ( preg_match_all( '#<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', (string) $body, $am, PREG_SET_ORDER ) ) {
			foreach ( $am as $a ) {
				$abs = ag_abs_url( $a[1], $case_url );
				if ( ! $abs || ! preg_match( '#^https?://#i', $abs ) ) continue;
				$ah = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $abs, PHP_URL_HOST ) ) );
				if ( '' === $ah || $ah === $agency_host || preg_match( $skip, $ah ) ) continue;
				if ( ! preg_match( '#\.[a-z]{2,}$#i', $ah ) ) continue;
				$txt = strtolower( trim( wp_strip_all_tags( $a[2] ) ) );
				$score = 1; // lien externe plausible
				if ( preg_match( '#d[ée]couvrir|voir le site|visiter|consulter|site (en ligne|web|internet)|le site|en ligne|visit#i', $txt ) ) $score += 5;
				if ( '' !== $ah && false !== strpos( $txt, $ah ) ) $score += 3; // le texte affiché EST le domaine
				if ( '' === $first ) $first = $abs;
				if ( $score > $best_score ) { $best_score = $score; $best = $abs; }
			}
		}
		// On ne retient le 1er lien externe « au hasard » que s'il n'y a pas mieux ET qu'il est unique/clair.
		$pick = ( $best_score >= 4 ) ? $best : ( ( $best_score >= 1 ) ? $best : $first );
		if ( '' === $pick ) return null;
		$ph = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $pick, PHP_URL_HOST ) ) );
		if ( '' === $ph ) return null;
		return array( 'url' => 'https://' . $ph . '/', 'name' => ag_agency_name_from_host( $ph ) );
	}
}

/**
 * Découvre les créations d'une agence. Les portfolios modernes ne mettent PAS le
 * lien du client sur la page liste : chaque projet pointe vers une FICHE interne
 * (/portfolio/slug/) où se trouve le vrai lien « découvrir le site ». On suit donc
 * les fiches projet et on en extrait le domaine client. On garde aussi les liens
 * clients mis en direct sur la liste (certaines agences le font).
 *
 * @return array liste de array('url'=>, 'name'=>) dédupliquée par domaine client.
 */
if ( ! function_exists( 'ag_agency_find_creations' ) ) {
	function ag_agency_find_creations( $url, $body = '' ) {
		if ( ! function_exists( 'ag_audit_fetch' ) ) return array();
		$host = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) );
		if ( '' === $body ) { $r0 = ag_audit_fetch( $url ); $body = $r0 ? (string) $r0['body'] : ''; }
		$skip = ag_agency_skip_re();

		// 1) Pages "liste" : la page donnée + pages portfolio/réalisations liées (même domaine).
		$listing = array( $url => $url );
		if ( preg_match_all( '#href=["\']([^"\']+)["\']#i', $body, $pm ) ) {
			foreach ( array_unique( $pm[1] ) as $rel ) {
				$abs = ag_abs_url( $rel, $url ); if ( ! $abs ) continue;
				$ah = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $abs, PHP_URL_HOST ) ) );
				if ( $ah !== $host ) continue;
				$path = strtolower( (string) wp_parse_url( $abs, PHP_URL_PATH ) );
				if ( preg_match( '#(portfolio|realisation|references?|nos-clients|nos-sites|nos-creations|book)#', $path ) ) { $listing[ $abs ] = $abs; }
			}
		}
		$listing = array_slice( array_values( $listing ), 0, 3 );

		// 2) Depuis les pages liste : fiches projet INTERNES + liens client DIRECTS éventuels.
		$cases  = array();  // URLs internes de fiches projet
		$found  = array();  // host => array(url,name)
		foreach ( $listing as $pu ) {
			$r  = ( $pu === $url && '' !== $body ) ? array( 'body' => $body ) : ag_audit_fetch( $pu );
			$pb = ( $r && ! empty( $r['body'] ) ) ? (string) $r['body'] : '';
			if ( '' === $pb ) continue;
			if ( preg_match_all( '#href=["\']([^"\']+)["\']#i', $pb, $lm ) ) {
				foreach ( array_unique( $lm[1] ) as $rel ) {
					$abs = ag_abs_url( $rel, $pu ); if ( ! $abs || ! preg_match( '#^https?://#i', $abs ) ) continue;
					$ah  = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $abs, PHP_URL_HOST ) ) );
					if ( '' === $ah ) continue;
					if ( $ah === $host ) {
						$path = strtolower( (string) wp_parse_url( $abs, PHP_URL_PATH ) );
						if ( preg_match( '#(portfolio|realisation|projet|reference|/case|/work|creation-|refonte-|developpement-|optimisation-)#', $path )
							&& ! preg_match( '#/(portfolio|realisations?|references?|projets?|works?|nos-clients|nos-sites|nos-creations)/?$#', $path ) ) {
							$cases[ $abs ] = $abs;
						}
					} elseif ( ! preg_match( $skip, $ah ) && preg_match( '#\.[a-z]{2,}$#i', $ah ) ) {
						if ( ! isset( $found[ $ah ] ) ) { $found[ $ah ] = array( 'url' => 'https://' . $ah . '/', 'name' => ag_agency_name_from_host( $ah ) ); }
					}
				}
			}
		}

		// 3) Suivre chaque fiche projet → extraire le site CLIENT (+ nom).
		foreach ( array_slice( array_values( $cases ), 0, 12 ) as $cu ) {
			if ( count( $found ) >= 14 ) break;
			$cr = ag_audit_fetch( $cu );
			$cb = ( $cr && ! empty( $cr['body'] ) ) ? (string) $cr['body'] : '';
			if ( '' === $cb ) continue;
			$client = ag_agency_client_from_case( $cb, $cu, $host );
			if ( $client ) {
				$ch = preg_replace( '#^www\.#', '', strtolower( (string) wp_parse_url( $client['url'], PHP_URL_HOST ) ) );
				if ( $ch && $ch !== $host && ! isset( $found[ $ch ] ) ) { $found[ $ch ] = $client; }
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

	$creations_data = ag_agency_find_creations( $url, $body );
	$creations = array();
	foreach ( array_slice( $creations_data, 0, 8 ) as $cd ) { // plafond : 8 audits par scan (temps/coût)
		$cu = is_array( $cd ) ? (string) ( $cd['url'] ?? '' ) : (string) $cd;
		if ( '' === $cu ) continue;
		$ca = ag_audit_get( $cu );
		$creations[] = array(
			'url'   => $cu,
			'host'  => preg_replace( '#^www\.#', '', (string) wp_parse_url( $cu, PHP_URL_HOST ) ),
			'name'  => is_array( $cd ) ? (string) ( $cd['name'] ?? '' ) : '',
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
