<?php
/**
 * AG — Audit SEO gratuit (lead magnet).
 *
 * Le visiteur saisit son URL + email → on lance des checks SEO côté
 * serveur, on génère un rapport HTML imprimable en PDF (Ctrl+P), on
 * sauve le lead dans le CRM Prospection.
 *
 * Pour activer la page publique : créer une page WordPress avec le
 * template "Audit SEO gratuit" (slug /audit-seo par exemple).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------------------------------------------------------------------------
 * Helpers de checks SEO
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_audit_fetch' ) ) {
	function ag_audit_fetch( $url ) {
		// Anti-SSRF : refuse les URL pointant vers un hôte privé/loopback/réservé.
		if ( ! wp_http_validate_url( $url ) ) return null;
		$resp = wp_remote_get( $url, array(
			'timeout'            => 12,
			'redirection'        => 3,
			'user-agent'         => 'Alliance-Groupe-Audit/1.0',
			'reject_unsafe_urls' => true,
		) );
		if ( is_wp_error( $resp ) ) return null;
		return array(
			'code'    => wp_remote_retrieve_response_code( $resp ),
			'body'    => wp_remote_retrieve_body( $resp ),
			'headers' => wp_remote_retrieve_headers( $resp ),
			'time'    => (float) ( $resp['http_response'] ?? null ? 0 : 0 ),
		);
	}
}

/** Lit la date d'expiration du certificat SSL (timestamp) ou null. */
if ( ! function_exists( 'ag_audit_ssl_expiry' ) ) {
	function ag_audit_ssl_expiry( $host ) {
		if ( ! function_exists( 'stream_socket_client' ) || ! function_exists( 'openssl_x509_parse' ) ) return null;
		$ctx = stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false, 'SNI_enabled' => true ) ) );
		$cl  = @stream_socket_client( 'ssl://' . $host . ':443', $en, $es, 6, STREAM_CLIENT_CONNECT, $ctx );
		if ( ! $cl ) return null;
		$p = stream_context_get_params( $cl );
		fclose( $cl );
		if ( empty( $p['options']['ssl']['peer_certificate'] ) ) return null;
		$c = openssl_x509_parse( $p['options']['ssl']['peer_certificate'] );
		return isset( $c['validTo_time_t'] ) ? (int) $c['validTo_time_t'] : null;
	}
}

/** Score pondéré (ok=10/warn=5/fail=0 × poids) + plafond 60 si faille critique. */
if ( ! function_exists( 'ag_audit_score' ) ) {
	function ag_audit_score( $checks ) {
		$score = 0; $max = 0; $crit = 0;
		foreach ( $checks as $c ) {
			$w = isset( $c['weight'] ) ? (int) $c['weight'] : 1;
			$max   += 10 * $w;
			$score += ( 'ok' === $c['status'] ? 10 : ( 'warn' === $c['status'] ? 5 : 0 ) ) * $w;
			if ( ! empty( $c['critical'] ) && 'fail' === $c['status'] ) $crit++;
		}
		$pct = $max > 0 ? round( $score * 100 / $max ) : 0;
		if ( $crit > 0 && $pct > 60 ) $pct = 60;
		return array( $pct, $crit );
	}
}

/**
 * Extrait les COORDONNÉES PUBLIQUES publiées sur le site lui-même
 * (accueil + /mentions-legales + /contact) : email, téléphone, adresse,
 * nom, réseaux, SIRET. Uniquement de l'info publique éditée par le site.
 */
if ( ! function_exists( 'ag_audit_extract_contacts' ) ) {
	function ag_audit_extract_contacts( $url ) {
		$out  = array( 'emails' => array(), 'phones' => array(), 'address' => '', 'company' => '', 'siret' => '', 'socials' => array() );
		$host = wp_parse_url( $url, PHP_URL_SCHEME ) . '://' . wp_parse_url( $url, PHP_URL_HOST );
		$pages = array( $url, $host . '/mentions-legales', $host . '/mentions-legales-cgu', $host . '/contact', $host . '/mentions' );
		$blob = ''; $count = 0;
		foreach ( $pages as $p ) {
			if ( $count >= 3 ) break;
			$r = ag_audit_fetch( $p );
			if ( ! $r || (int) $r['code'] >= 400 ) continue;
			$count++; $blob .= ' ' . (string) $r['body'];
		}

		// Emails (mailto + texte), filtrage du bruit.
		if ( preg_match_all( '#[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}#i', $blob, $m ) ) {
			foreach ( array_unique( $m[0] ) as $e ) {
				$e = strtolower( $e );
				if ( preg_match( '#\.(png|jpe?g|gif|svg|webp|css|js)$#', $e ) ) continue;
				if ( preg_match( '#(sentry|wixpress|example\.|@2x|sentry\.io|\.w3\.org|domain\.tld|email@)#', $e ) ) continue;
				$out['emails'][] = $e;
			}
		}
		$out['emails'] = array_slice( array_values( array_unique( $out['emails'] ) ), 0, 5 );

		// Téléphones (tel: + format FR).
		if ( preg_match_all( '#tel:\+?([0-9 ().\-]{8,})#i', $blob, $tm ) ) { foreach ( $tm[1] as $t ) $out['phones'][] = trim( $t ); }
		if ( preg_match_all( '#(?:\+33|0)\s?[1-9](?:[\s.\-]?\d{2}){4}#', $blob, $pm ) ) { foreach ( $pm[0] as $t ) $out['phones'][] = trim( $t ); }
		$out['phones'] = array_slice( array_values( array_unique( $out['phones'] ) ), 0, 4 );

		// SIRET (souvent en mentions légales).
		if ( preg_match( '#\b(\d{3}[\s.]?\d{3}[\s.]?\d{3}[\s.]?\d{5})\b#', $blob, $sm ) ) $out['siret'] = trim( $sm[1] );

		// JSON-LD (nom, tel, adresse).
		if ( preg_match_all( '#<script[^>]+application/ld\+json[^>]*>(.*?)</script>#is', $blob, $jm ) ) {
			foreach ( $jm[1] as $j ) {
				$data = json_decode( trim( $j ), true );
				if ( ! is_array( $data ) ) continue;
				$nodes = isset( $data[0] ) ? $data : array( $data );
				foreach ( $nodes as $node ) {
					if ( ! is_array( $node ) ) continue;
					if ( ! $out['company'] && ! empty( $node['name'] ) && is_string( $node['name'] ) ) $out['company'] = $node['name'];
					if ( ! empty( $node['telephone'] ) && is_string( $node['telephone'] ) ) $out['phones'][] = trim( $node['telephone'] );
					if ( ! $out['address'] && ! empty( $node['address'] ) ) {
						$a = $node['address'];
						if ( is_array( $a ) ) $out['address'] = trim( ( $a['streetAddress'] ?? '' ) . ' ' . ( $a['postalCode'] ?? '' ) . ' ' . ( $a['addressLocality'] ?? '' ) );
						elseif ( is_string( $a ) ) $out['address'] = $a;
					}
				}
			}
		}
		$out['phones'] = array_slice( array_values( array_unique( $out['phones'] ) ), 0, 4 );

		// Nom : fallback og:site_name / title.
		if ( ! $out['company'] ) {
			if ( preg_match( '#property=[\'"]og:site_name[\'"][^>]+content=[\'"]([^\'"]+)#i', $blob, $cm ) ) $out['company'] = trim( $cm[1] );
			elseif ( preg_match( '#<title[^>]*>(.*?)</title>#is', $blob, $cm ) ) $out['company'] = trim( wp_strip_all_tags( $cm[1] ) );
		}
		$out['company'] = mb_substr( $out['company'], 0, 80 );

		// Adresse : fallback motif postal FR.
		if ( ! $out['address'] && preg_match( '#\d{1,4}\s+(?:rue|avenue|av\.|bd|boulevard|impasse|chemin|place|all[ée]e|route)\s+[^,<\n]{3,40}[,\s]+\d{5}\s+[A-Za-zÀ-ÿ \-]{2,30}#i', $blob, $am ) ) {
			$out['address'] = trim( preg_replace( '#\s+#', ' ', $am[0] ) );
		}

		// Réseaux sociaux.
		foreach ( array( 'facebook.com', 'instagram.com', 'linkedin.com' ) as $s ) {
			if ( preg_match( '#https?://(?:www\.)?' . preg_quote( $s, '#' ) . '/[A-Za-z0-9_.\-/]+#i', $blob, $sm ) ) $out['socials'][] = $sm[0];
		}
		return $out;
	}
}

if ( ! function_exists( 'ag_audit_run' ) ) {
	function ag_audit_run( $url ) {
		$url = trim( $url );
		if ( ! preg_match( '#^https?://#i', $url ) ) $url = 'https://' . $url;

		$t0 = microtime( true );
		$res = ag_audit_fetch( $url );
		$elapsed = round( ( microtime( true ) - $t0 ) * 1000 );

		$checks = array();

		// 1. Accessibilité
		if ( ! $res ) {
			$checks[] = array( 'name' => 'Site accessible', 'status' => 'fail', 'msg' => 'Impossible de récupérer le site. Erreur réseau ou serveur HS.' );
			return array( 'url' => $url, 'checks' => $checks, 'score' => 0, 'ts' => time() );
		}
		$checks[] = array(
			'name'   => 'Site accessible',
			'status' => 200 === (int) $res['code'] ? 'ok' : ( $res['code'] >= 300 && $res['code'] < 400 ? 'warn' : 'fail' ),
			'msg'    => 'HTTP ' . $res['code'] . ' · réponse en ' . $elapsed . ' ms',
			'advice' => $elapsed > 1500 ? 'Site lent (>1.5s). Optimiser images + cache.' : ( $elapsed > 800 ? 'Temps de réponse correct mais améliorable.' : 'Vitesse excellente.' ),
		);

		$body = (string) $res['body'];

		// Détection de la TECHNO : l'audit ne doit PAS partir du principe que c'est WordPress.
		// On repère WordPress (wp-content, wp-includes, generator WordPress, wp-json).
		$hdr_str = '';
		if ( ! empty( $res['headers'] ) && ( is_array( $res['headers'] ) || is_object( $res['headers'] ) ) ) {
			foreach ( $res['headers'] as $hk => $hv ) { $hdr_str .= $hk . ':' . ( is_array( $hv ) ? implode( ',', $hv ) : $hv ) . "\n"; }
		}
		$is_wp = (bool) ( preg_match( '#/wp-(content|includes|json)/#i', $body )
			|| preg_match( '#<meta[^>]+name=[\'"]generator[\'"][^>]+WordPress#i', $body )
			|| stripos( $hdr_str, 'wp-json' ) !== false );

		// 2. HTTPS
		$is_https = 0 === strpos( $url, 'https://' );
		$checks[] = array(
			'name'   => 'Connexion sécurisée (HTTPS)',
			'status' => $is_https ? 'ok' : 'fail',
			'msg'    => $is_https ? 'HTTPS activé.' : 'Site en HTTP non-sécurisé.',
			'advice' => $is_https ? 'Bien.' : 'Activer un certificat SSL (Let\'s Encrypt gratuit).',
		);

		// 3. Title
		preg_match( '#<title[^>]*>(.*?)</title>#is', $body, $m );
		$title = isset( $m[1] ) ? trim( wp_strip_all_tags( $m[1] ) ) : '';
		$len_t = mb_strlen( $title );
		$checks[] = array(
			'name'   => 'Balise <title>',
			'status' => ( $len_t >= 30 && $len_t <= 65 ) ? 'ok' : ( $len_t > 0 ? 'warn' : 'fail' ),
			'msg'    => $title ? '"' . esc_html( $title ) . '" (' . $len_t . ' caractères)' : 'Aucune balise <title> détectée.',
			'advice' => $len_t < 30 ? 'Trop court — vise 30-65 caractères avec mot-clé principal.' : ( $len_t > 65 ? 'Trop long — sera tronqué dans Google.' : 'Longueur idéale.' ),
		);

		// 4. Meta description
		preg_match( '#<meta\s+name=[\'"]description[\'"]\s+content=[\'"]([^\'"]*)[\'"]#is', $body, $m );
		$desc = isset( $m[1] ) ? trim( $m[1] ) : '';
		$len_d = mb_strlen( $desc );
		$checks[] = array(
			'name'   => 'Meta description',
			'status' => ( $len_d >= 80 && $len_d <= 160 ) ? 'ok' : ( $len_d > 0 ? 'warn' : 'fail' ),
			'msg'    => $desc ? esc_html( mb_substr( $desc, 0, 80 ) ) . '... (' . $len_d . ' caractères)' : 'Aucune meta description.',
			'advice' => $len_d < 80 ? 'Vise 80-160 caractères avec un appel à l\'action.' : ( $len_d > 160 ? 'Trop longue — sera coupée.' : 'Longueur idéale.' ),
		);

		// 5. H1
		preg_match_all( '#<h1[^>]*>(.*?)</h1>#is', $body, $h1s );
		$nb_h1 = count( $h1s[0] ?? array() );
		$checks[] = array(
			'name'   => 'Titre H1',
			'status' => 1 === $nb_h1 ? 'ok' : ( 0 === $nb_h1 ? 'fail' : 'warn' ),
			'msg'    => $nb_h1 . ' balise(s) H1 détectée(s).',
			'advice' => 1 === $nb_h1 ? 'Parfait.' : ( 0 === $nb_h1 ? 'Ajoute UN H1 par page (titre principal).' : 'Trop de H1 — un seul par page (le titre principal).' ),
		);

		// 6. Mobile (viewport)
		$has_viewport = (bool) preg_match( '#<meta\s+name=[\'"]viewport[\'"]#i', $body );
		$checks[] = array(
			'name'   => 'Compatible mobile (viewport)',
			'status' => $has_viewport ? 'ok' : 'fail',
			'msg'    => $has_viewport ? 'Meta viewport présente.' : 'Pas de meta viewport.',
			'advice' => $has_viewport ? 'Site responsive.' : 'Ajouter <meta name="viewport" content="width=device-width, initial-scale=1"> dans le <head>.',
		);

		// 7. Open Graph
		$og_t = (bool) preg_match( '#property=[\'"]og:title[\'"]#i', $body );
		$og_d = (bool) preg_match( '#property=[\'"]og:description[\'"]#i', $body );
		$og_i = (bool) preg_match( '#property=[\'"]og:image[\'"]#i', $body );
		$og_count = (int) $og_t + (int) $og_d + (int) $og_i;
		$checks[] = array(
			'name'   => 'Partage social (Open Graph)',
			'status' => $og_count === 3 ? 'ok' : ( $og_count >= 1 ? 'warn' : 'fail' ),
			'msg'    => $og_count . '/3 balises Open Graph détectées (titre / description / image).',
			'advice' => $og_count === 3 ? 'Le partage sur Facebook/LinkedIn affichera bien.' : 'Ajouter les 3 og:title / og:description / og:image pour de jolis partages.',
		);

		// 8. Images sans alt
		preg_match_all( '#<img\b([^>]*)>#is', $body, $imgs );
		$nb_img = count( $imgs[0] ?? array() );
		$nb_no_alt = 0;
		foreach ( ( $imgs[1] ?? array() ) as $attrs ) {
			if ( ! preg_match( '#\salt\s*=\s*[\'"][^\'"]+[\'"]#i', $attrs ) ) $nb_no_alt++;
		}
		$checks[] = array(
			'name'   => 'Images avec attribut alt',
			'status' => $nb_no_alt === 0 ? 'ok' : ( $nb_no_alt < 5 ? 'warn' : 'fail' ),
			'msg'    => ( $nb_img - $nb_no_alt ) . '/' . $nb_img . ' images avec alt.',
			'advice' => $nb_no_alt > 0 ? 'Ajouter un attribut alt descriptif sur chaque image (SEO + accessibilité).' : 'Toutes tes images ont un alt — top.',
		);

		// 9. Robots.txt
		$host = wp_parse_url( $url, PHP_URL_SCHEME ) . '://' . wp_parse_url( $url, PHP_URL_HOST );
		$rob = wp_remote_head( $host . '/robots.txt', array( 'timeout' => 8, 'reject_unsafe_urls' => true ) );
		$rob_ok = ! is_wp_error( $rob ) && 200 === wp_remote_retrieve_response_code( $rob );
		$checks[] = array(
			'name'   => 'Fichier robots.txt',
			'status' => $rob_ok ? 'ok' : 'warn',
			'msg'    => $rob_ok ? 'Présent.' : 'Absent ou inaccessible.',
			'advice' => $rob_ok ? 'Indique à Google ce qui doit être indexé.' : 'Créer un robots.txt à la racine (même minimal) — aide Google à crawler.',
		);

		// 10. Sitemap.xml
		$sm = wp_remote_head( $host . '/sitemap.xml', array( 'timeout' => 8, 'reject_unsafe_urls' => true ) );
		$sm_ok = ! is_wp_error( $sm ) && 200 === wp_remote_retrieve_response_code( $sm );
		$checks[] = array(
			'name'   => 'Sitemap XML',
			'status' => $sm_ok ? 'ok' : 'fail',
			'msg'    => $sm_ok ? 'Présent à /sitemap.xml.' : 'Absent ou inaccessible.',
			'advice' => $sm_ok ? 'Soumets-le à Google Search Console.' : 'Créer un sitemap.xml et le soumettre à Google Search Console.',
		);

		// 11. Schema.org / JSON-LD
		$has_jsonld = (bool) preg_match( '#<script\s+type=[\'"]application/ld\+json[\'"]#i', $body );
		$checks[] = array(
			'name'   => 'Données structurées (Schema.org)',
			'status' => $has_jsonld ? 'ok' : 'warn',
			'msg'    => $has_jsonld ? 'JSON-LD détecté.' : 'Aucune donnée structurée.',
			'advice' => $has_jsonld ? 'Bien.' : 'Ajouter du Schema.org (Organization, LocalBusiness) pour les rich snippets Google.',
		);

		// 12. Favicon
		$has_favicon = (bool) preg_match( '#<link[^>]+rel=[\'"](?:shortcut\s+)?icon[\'"]#i', $body );
		$checks[] = array(
			'name'   => 'Favicon',
			'status' => $has_favicon ? 'ok' : 'warn',
			'msg'    => $has_favicon ? 'Présente.' : 'Pas de favicon référencée.',
			'advice' => $has_favicon ? 'Bien.' : 'Ajouter une favicon (visible dans les onglets navigateur).',
		);

		/* ====================== CONTRÔLES DE SÉCURITÉ (passifs) ====================== */
		$probe = array( 'timeout' => 7, 'user-agent' => 'Alliance-Groupe-Audit/1.0', 'reject_unsafe_urls' => true );

		// Entêtes en minuscules.
		$hh = array();
		$hdrs = $res['headers'] ?? array();
		if ( is_array( $hdrs ) || is_object( $hdrs ) ) {
			foreach ( $hdrs as $hk => $hv ) { $hh[ strtolower( $hk ) ] = is_array( $hv ) ? implode( ', ', $hv ) : (string) $hv; }
		}

		// S1. xmlrpc.php exposé (force brute / DDoS pingback). — SPÉCIFIQUE WORDPRESS
		if ( $is_wp ) {
			$xr   = wp_remote_get( $host . '/xmlrpc.php', $probe );
			$xc   = is_wp_error( $xr ) ? 0 : (int) wp_remote_retrieve_response_code( $xr );
			$xb   = is_wp_error( $xr ) ? '' : wp_remote_retrieve_body( $xr );
			$xopen = ( 405 === $xc ) || ( false !== stripos( $xb, 'XML-RPC server accepts POST' ) );
			$checks[] = array(
				'name' => 'xmlrpc.php (force brute / DDoS)',
				'status' => $xopen ? 'fail' : 'ok',
				'msg' => $xopen ? 'xmlrpc.php est exposé et actif.' : 'Non exposé / désactivé.',
				'advice' => $xopen ? 'Bloquer ou désactiver xmlrpc.php : amplificateur d\'attaques par force brute et de DDoS via pingback.' : 'Bien.',
				'weight' => 3, 'critical' => $xopen,
			);
		}

		// S2. En-têtes de sécurité HTTP.
		$want = array( 'strict-transport-security', 'content-security-policy', 'x-frame-options', 'x-content-type-options', 'referrer-policy' );
		$present = 0; foreach ( $want as $w ) { if ( ! empty( $hh[ $w ] ) ) $present++; }
		$checks[] = array(
			'name' => 'En-têtes de sécurité HTTP',
			'status' => $present >= 4 ? 'ok' : ( $present >= 2 ? 'warn' : 'fail' ),
			'msg' => $present . '/5 en-têtes de protection présents (HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy).',
			'advice' => $present >= 4 ? 'Bonne protection.' : 'Ajouter les en-têtes manquants (anti-clickjacking, anti-injection, HTTPS forcé).',
			'weight' => 2,
		);

		// S3. Divulgation de version / technologie.
		$gen = preg_match( '#<meta[^>]+name=[\'"]generator[\'"][^>]+content=[\'"]([^\'"]*)[\'"]#i', $body, $gm ) ? trim( $gm[1] ) : '';
		$leak = array();
		if ( $gen ) $leak[] = $gen;
		if ( ! empty( $hh['x-powered-by'] ) ) $leak[] = 'X-Powered-By: ' . $hh['x-powered-by'];
		if ( ! empty( $hh['server'] ) && preg_match( '#\d#', $hh['server'] ) ) $leak[] = 'Server: ' . $hh['server'];
		$checks[] = array(
			'name' => 'Divulgation de version (techno)',
			'status' => empty( $leak ) ? 'ok' : 'warn',
			'msg' => empty( $leak ) ? 'Aucune version sensible exposée.' : implode( ' · ', array_map( 'esc_html', $leak ) ),
			'advice' => empty( $leak ) ? 'Bien.' : 'Masquer les versions (CMS / PHP / serveur) : elles guident les attaquants vers les exploits connus.',
			'weight' => 1,
		);

		// S4. Énumération des comptes WordPress (REST /wp-json/wp/v2/users). — SPÉCIFIQUE WORDPRESS
		if ( $is_wp ) {
			$ur   = wp_remote_get( $host . '/wp-json/wp/v2/users', $probe );
			$uc   = is_wp_error( $ur ) ? 0 : (int) wp_remote_retrieve_response_code( $ur );
			$ub   = is_wp_error( $ur ) ? '' : wp_remote_retrieve_body( $ur );
			$enum = ( 200 === $uc && false !== strpos( $ub, '"slug"' ) );
			$checks[] = array(
				'name' => 'Énumération des comptes (wp-json)',
				'status' => $enum ? 'fail' : 'ok',
				'msg' => $enum ? 'Les identifiants/auteurs sont publics via /wp-json/wp/v2/users.' : 'Non exposée.',
				'advice' => $enum ? 'Bloquer l\'endpoint REST users : il livre les logins, la moitié d\'un piratage par force brute.' : 'Bien.',
				'weight' => 2, 'critical' => $enum,
			);
		}

		// S5. Fichiers sensibles exposés (.git / .env).
		$exposed = array();
		foreach ( array( '/.git/HEAD' => 'ref:', '/.env' => '=' ) as $pf => $needle ) {
			$r = wp_remote_get( $host . $pf, $probe );
			if ( ! is_wp_error( $r ) && 200 === (int) wp_remote_retrieve_response_code( $r ) && false !== strpos( wp_remote_retrieve_body( $r ), $needle ) ) {
				$exposed[] = $pf;
			}
		}
		$checks[] = array(
			'name' => 'Fichiers sensibles exposés (.git/.env)',
			'status' => $exposed ? 'fail' : 'ok',
			'msg' => $exposed ? 'Accessibles publiquement : ' . implode( ', ', $exposed ) : 'Aucun fichier sensible accessible.',
			'advice' => $exposed ? 'Bloquer immédiatement : ces fichiers exposent code source, mots de passe et clés.' : 'Bien.',
			'weight' => 3, 'critical' => (bool) $exposed,
		);

		// S6. Certificat SSL (validité / expiration).
		if ( 0 === strpos( $url, 'https://' ) ) {
			$exp = ag_audit_ssl_expiry( wp_parse_url( $url, PHP_URL_HOST ) );
			if ( null === $exp ) {
				$checks[] = array( 'name' => 'Certificat SSL', 'status' => 'warn', 'msg' => 'Certificat illisible.', 'advice' => 'Vérifier la configuration SSL.', 'weight' => 2 );
			} else {
				$days = (int) floor( ( $exp - time() ) / 86400 );
				$st = $days < 0 ? 'fail' : ( $days < 15 ? 'warn' : 'ok' );
				$checks[] = array(
					'name' => 'Certificat SSL',
					'status' => $st,
					'msg' => $days < 0 ? 'EXPIRÉ depuis ' . abs( $days ) . ' j.' : 'Valide encore ' . $days . ' j.',
					'advice' => $days < 0 ? 'Renouveler immédiatement le certificat (site signalé « non sécurisé »).' : ( $days < 15 ? 'Renouveler bientôt (activer l\'auto-renouvellement).' : 'Bien.' ),
					'weight' => 2, 'critical' => ( $days < 0 ),
				);
			}
		} else {
			$checks[] = array( 'name' => 'Certificat SSL', 'status' => 'fail', 'msg' => 'Aucun HTTPS.', 'advice' => 'Activer un certificat SSL (Let\'s Encrypt gratuit).', 'weight' => 2, 'critical' => true );
		}

		// S7. Listing de répertoire — universel (uploads si WP, sinon dossiers génériques).
		$dir_test = $is_wp ? array( '/wp-content/uploads/' ) : array( '/uploads/', '/images/', '/files/', '/assets/' );
		$listing = false; $listing_path = '';
		foreach ( $dir_test as $dp ) {
			$dr = wp_remote_get( $host . $dp, $probe );
			$db = is_wp_error( $dr ) ? '' : wp_remote_retrieve_body( $dr );
			if ( false !== stripos( $db, 'Index of /' ) ) { $listing = true; $listing_path = $dp; break; }
		}
		$checks[] = array(
			'name' => 'Listing de répertoire (uploads)',
			'status' => $listing ? 'warn' : 'ok',
			'msg' => $listing ? 'Le contenu de ' . $listing_path . ' est listable publiquement.' : 'Non listable.',
			'advice' => $listing ? 'Désactiver l\'autoindex (Options -Indexes) : expose vos fichiers.' : 'Bien.',
			'weight' => 1,
		);

		/* ====================== Score pondéré + plafond si faille critique ====================== */
		list( $score_pct, $nb_crit ) = ag_audit_score( $checks );

		return array( 'url' => $url, 'checks' => $checks, 'score' => $score_pct, 'critical' => $nb_crit, 'time_ms' => $elapsed, 'ts' => time(), 'tech' => $is_wp ? 'WordPress' : 'autre/custom' );
	}
}

/* ---------------------------------------------------------------------------
 * AUDIT APPROFONDI (actif) — sondes supplémentaires.
 * ⚠️ À N'UTILISER QU'AVEC AUTORISATION ÉCRITE DU PROPRIÉTAIRE (mandat).
 * Reste de la reconnaissance (requêtes HTTP sur endpoints connus) : AUCUNE
 * exploitation, aucun brute-force, aucune injection de payload.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_audit_run_deep' ) ) {
	function ag_audit_run_deep( $url ) {
		$base   = ag_audit_run( $url );           // toute la base passive
		$url    = $base['url'];
		$checks = $base['checks'];
		$host   = wp_parse_url( $url, PHP_URL_SCHEME ) . '://' . wp_parse_url( $url, PHP_URL_HOST );
		$probe  = array( 'timeout' => 7, 'user-agent' => 'Alliance-Groupe-Audit/1.0', 'reject_unsafe_urls' => true );

		// Techno détectée par l'audit de base (les sondes WordPress ci-dessous n'ont de
		// sens que sur un site WordPress — sinon elles affichent du vide trompeur).
		$is_wp = ( 'WordPress' === ( $base['tech'] ?? '' ) );

		// D1. Énumération d'auteur via ?author=1 (révèle un identifiant). — WORDPRESS
		if ( $is_wp ) {
			$au  = wp_remote_get( $host . '/?author=1', array_merge( $probe, array( 'redirection' => 0 ) ) );
			$loc = is_wp_error( $au ) ? '' : (string) wp_remote_retrieve_header( $au, 'location' );
			$leak_user = preg_match( '#/author/([^/]+)/?#i', $loc, $am ) ? $am[1] : '';
			$checks[] = array(
				'name' => 'Énumération d\'auteur (?author=1)',
				'status' => $leak_user ? 'fail' : 'ok',
				'msg' => $leak_user ? 'Identifiant exposé : « ' . esc_html( $leak_user ) . ' ».' : 'Pas de fuite d\'identifiant.',
				'advice' => $leak_user ? 'Bloquer la redirection ?author= : elle donne le login, moitié d\'un piratage.' : 'Bien.',
				'weight' => 2, 'critical' => (bool) $leak_user,
			);
		}

		// D2. Fichiers de sauvegarde / config exposés.
		$bak_paths = array( '/wp-config.php.bak', '/wp-config.php~', '/wp-config.php.save', '/wp-config.php.txt', '/.wp-config.php.swp', '/backup.zip', '/backup.sql', '/database.sql', '/dump.sql', '/debug.log' );
		$found_bak = array();
		foreach ( $bak_paths as $bp ) {
			$r = wp_remote_get( $host . $bp, $probe );
			if ( ! is_wp_error( $r ) && 200 === (int) wp_remote_retrieve_response_code( $r ) ) {
				$rb = wp_remote_retrieve_body( $r );
				// Évite les faux positifs (page 404 HTML déguisée).
				if ( $rb && false === stripos( substr( $rb, 0, 200 ), '<html' ) && false === stripos( substr( $rb, 0, 200 ), '<!doctype' ) ) {
					$found_bak[] = $bp;
				}
			}
		}
		$checks[] = array(
			'name' => 'Sauvegardes / config exposées',
			'status' => $found_bak ? 'fail' : 'ok',
			'msg' => $found_bak ? 'Téléchargeables : ' . implode( ', ', $found_bak ) : 'Aucun fichier de sauvegarde/config accessible.',
			'advice' => $found_bak ? 'Supprimer/bloquer ces fichiers IMMÉDIATEMENT : ils contiennent souvent identifiants et base de données.' : 'Bien.',
			'weight' => 3, 'critical' => (bool) $found_bak,
		);

		// D3/D4/D5 : sondes SPÉCIFIQUES WORDPRESS (listing wp-content, versions plugins,
		// pingback xmlrpc). Ne s'exécutent que sur un site WordPress.
		if ( $is_wp ) {
			// D3. Listing de répertoires sensibles.
			$dir_open = array();
			foreach ( array( '/wp-content/', '/wp-content/plugins/', '/wp-includes/' ) as $d ) {
				$r = wp_remote_get( $host . $d, $probe );
				if ( ! is_wp_error( $r ) && false !== stripos( wp_remote_retrieve_body( $r ), 'Index of /' ) ) $dir_open[] = $d;
			}
			$checks[] = array(
				'name' => 'Listing de répertoires (système)',
				'status' => $dir_open ? 'warn' : 'ok',
				'msg' => $dir_open ? 'Listables : ' . implode( ', ', $dir_open ) : 'Répertoires système non listables.',
				'advice' => $dir_open ? 'Désactiver l\'autoindex (Options -Indexes).' : 'Bien.',
				'weight' => 1,
			);

			// D4. Versions de plugins exposées (readme.txt) → matching CVE facile.
			$res  = ag_audit_fetch( $url );
			$body = $res ? (string) $res['body'] : '';
			$plugins = array();
			if ( preg_match_all( '#/wp-content/plugins/([a-z0-9\-_]+)/#i', $body, $pm ) ) {
				$plugins = array_slice( array_unique( $pm[1] ), 0, 4 );
			}
			$plug_vers = array();
			foreach ( $plugins as $slug ) {
				$r = wp_remote_get( $host . '/wp-content/plugins/' . $slug . '/readme.txt', $probe );
				if ( ! is_wp_error( $r ) && 200 === (int) wp_remote_retrieve_response_code( $r ) && preg_match( '#Stable tag:\s*([0-9.]+)#i', wp_remote_retrieve_body( $r ), $vm ) ) {
					$plug_vers[] = $slug . ' ' . $vm[1];
				}
			}
			$checks[] = array(
				'name' => 'Versions de plugins exposées',
				'status' => $plug_vers ? 'warn' : 'ok',
				'msg' => $plug_vers ? implode( ' · ', array_map( 'esc_html', $plug_vers ) ) : ( $plugins ? 'Plugins détectés, versions non exposées.' : 'Aucun plugin détecté.' ),
				'advice' => $plug_vers ? 'Masquer les readme.txt : un attaquant compare la version aux failles connues (CVE).' : 'Bien.',
				'weight' => 1,
			);

			// D5. xmlrpc : pingback disponible (amplification réelle).
			$pb = wp_remote_post( $host . '/xmlrpc.php', array_merge( $probe, array(
				'headers' => array( 'Content-Type' => 'text/xml' ),
				'body'    => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName><params></params></methodCall>',
			) ) );
			$pbb = is_wp_error( $pb ) ? '' : wp_remote_retrieve_body( $pb );
			$ping = ( false !== stripos( $pbb, 'pingback.ping' ) );
			$checks[] = array(
				'name' => 'xmlrpc : pingback (amplification)',
				'status' => $ping ? 'fail' : 'ok',
				'msg' => $ping ? 'La méthode pingback.ping est active (DDoS par réflexion possible).' : 'Pingback non disponible.',
				'advice' => $ping ? 'Désactiver pingback.ping / filtrer xmlrpc.' : 'Bien.',
				'weight' => 2, 'critical' => $ping,
			);
		}

		list( $score_pct, $nb_crit ) = ag_audit_score( $checks );

		// COHÉRENCE DES NIVEAUX : l'approfondi ne peut JAMAIS être meilleur que le
		// léger. On part de la note du léger (base passive) et on RETIRE des points
		// pour chaque sonde approfondie en défaut. Si les sondes en plus sont toutes
		// OK → approfondi = léger (le score % brut, lui, gonflerait à tort car ajouter
		// des contrôles « ok » remonte la moyenne — on l'ignore donc).
		$base_score = (int) ( $base['score'] ?? 0 );
		$deep_only  = array_slice( $checks, count( $base['checks'] ) );
		$malus = 0;
		foreach ( $deep_only as $dc ) {
			$w = isset( $dc['weight'] ) ? (int) $dc['weight'] : 1;
			$st = $dc['status'] ?? 'ok';
			if ( 'fail' === $st )      $malus += 6 + 2 * $w;   // faille = grosse pénalité
			elseif ( 'warn' === $st )  $malus += 2 + $w;       // alerte = petite pénalité
		}
		$deep_score = max( 3, min( $base_score, $base_score - $malus ) );

		return array( 'url' => $url, 'checks' => $checks, 'score' => $deep_score, 'critical' => $nb_crit, 'deep' => true, 'ts' => time(), 'tech' => $base['tech'] ?? '' );
	}
}

/* ---------------------------------------------------------------------------
 * Handler de soumission
 * ------------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_ag_audit_request', 'ag_audit_handle' );
add_action( 'admin_post_ag_audit_request', 'ag_audit_handle' );
if ( ! function_exists( 'ag_audit_handle' ) ) {
	function ag_audit_handle() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_audit' ) ) wp_die( 'Lien expiré.' );
		// Honeypot anti-spam
		if ( ! empty( $_POST['hp_field'] ) ) wp_die( 'Spam détecté.' );

		$url    = esc_url_raw( wp_unslash( $_POST['site_url'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
		$tel    = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );

		if ( ! $url || ! is_email( $email ) ) wp_die( 'URL ou email invalide.' );

		// Sauvegarde du lead dans le CRM Prospection (si dispo)
		if ( function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name'   => $prenom ?: $email,
				'email'  => $email,
				'phone'  => $tel,
				'website'=> $url,
				'source' => 'audit-seo',
				'notes'  => 'A demandé un audit SEO gratuit le ' . current_time( 'd/m/Y H:i' ),
				'status' => 'nouveau',
			) );
		}

		// Notification équipe
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '📊 Nouvel audit SEO', ( $prenom ?: $email ) . ' demande un audit pour ' . $url );
		}

		// Lance l'audit, stocke le résultat dans une option temporaire (ID = hash)
		$audit = ag_audit_run( $url );
		$aid   = wp_hash( $url . '|' . $email . '|' . time() );
		set_transient( 'ag_audit_' . $aid, array( 'audit' => $audit, 'email' => $email, 'prenom' => $prenom ), 7 * DAY_IN_SECONDS );

		// Email au prospect avec le lien du rapport
		$report_url = add_query_arg( array( 'aid' => $aid ), wp_get_referer() ?: home_url( '/audit-seo' ) );
		$subject = 'Votre audit SEO Alliance Groupe — score ' . $audit['score'] . ' / 100';
		$body    = "Bonjour " . ( $prenom ?: '' ) . ",\n\nVotre audit SEO pour " . $url . " est prêt.\n\nScore global : " . $audit['score'] . " / 100.\n\nConsultez le rapport complet ici :\n" . $report_url . "\n\nVous pouvez l'imprimer ou l'enregistrer en PDF (Ctrl+P).\n\nUne question ? Répondez à ce mail.\n\nAlliance Groupe\n";
		wp_mail( $email, $subject, $body );

		wp_safe_redirect( $report_url );
		exit;
	}
}

/* ---------------------------------------------------------------------------
 * Rendu : formulaire ou rapport (à appeler depuis le template)
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_audit_render' ) ) {
	function ag_audit_render() {
		$aid = isset( $_GET['aid'] ) ? sanitize_text_field( wp_unslash( $_GET['aid'] ) ) : '';
		if ( $aid ) {
			$data = get_transient( 'ag_audit_' . $aid );
			if ( $data && ! empty( $data['audit'] ) ) {
				ag_audit_render_report( $data['audit'], $data['prenom'] ?? '' );
				return;
			}
		}
		ag_audit_render_form();
	}
}

if ( ! function_exists( 'ag_audit_render_form' ) ) {
	function ag_audit_render_form() { ?>
		<section style="background:linear-gradient(180deg,#0a0a0f 0%,#14141c 100%);color:#fff;padding:80px 24px;min-height:80vh">
			<div style="max-width:680px;margin:0 auto;text-align:center">
				<span style="display:inline-block;padding:6px 14px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#D4B45C;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px">🎁 Cadeau · 0 €</span>
				<h1 style="font-family:Georgia,serif;font-size:clamp(2rem,5vw,3.6rem);line-height:1.1;margin:0 0 14px">Audit SEO <em style="background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-style:italic">gratuit</em> de votre site</h1>
				<p style="color:rgba(255,255,255,.75);font-size:1.05rem;line-height:1.6;margin:0 0 36px">12 points d'analyse, livré sous 30 secondes : vitesse, mobile, Google, partage social, données structurées. PDF imprimable.</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="text-align:left;display:flex;flex-direction:column;gap:14px;max-width:520px;margin:0 auto">
					<input type="hidden" name="action" value="ag_audit_request">
					<?php wp_nonce_field( 'ag_audit' ); ?>
					<input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

					<label style="display:flex;flex-direction:column;gap:6px;font-size:.9rem;color:rgba(255,255,255,.7)">URL de votre site *
						<input type="url" name="site_url" required placeholder="https://monsite.fr" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					</label>
					<label style="display:flex;flex-direction:column;gap:6px;font-size:.9rem;color:rgba(255,255,255,.7)">Votre prénom
						<input type="text" name="prenom" placeholder="Marc" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					</label>
					<label style="display:flex;flex-direction:column;gap:6px;font-size:.9rem;color:rgba(255,255,255,.7)">Email *
						<input type="email" name="email" required placeholder="vous@email.fr" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					</label>
					<label style="display:flex;flex-direction:column;gap:6px;font-size:.9rem;color:rgba(255,255,255,.7)">Téléphone (optionnel — pour un appel de conseil offert)
						<input type="tel" name="phone" placeholder="06 12 34 56 78" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					</label>
					<button type="submit" style="margin-top:8px;padding:18px 36px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#0a0a0f;font-weight:800;border:none;border-radius:999px;font-size:1rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;box-shadow:0 12px 32px rgba(212,180,92,.35)">📊 Lancer mon audit gratuit →</button>
					<p style="color:rgba(255,255,255,.5);font-size:.78rem;text-align:center;margin:8px 0 0">Vos données sont utilisées uniquement pour vous transmettre l'audit. Aucune revente, désabonnement en 1 clic.</p>
				</form>
			</div>
		</section>
	<?php }
}

if ( ! function_exists( 'ag_audit_render_report' ) ) {
	function ag_audit_render_report( $a, $prenom = '' ) {
		$score = (int) ( $a['score'] ?? 0 );
		$color = $score >= 75 ? '#28a745' : ( $score >= 50 ? '#F37A1F' : '#E10F1A' );
		$verdict = $score >= 75 ? 'Très bon site.' : ( $score >= 50 ? 'Site correct mais améliorable.' : 'Plusieurs points critiques.' );
		?>
		<section style="background:linear-gradient(180deg,#0a0a0f 0%,#14141c 100%);color:#fff;padding:60px 24px">
			<div style="max-width:920px;margin:0 auto">
				<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:12px">
					<a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>" style="color:#D4B45C;text-decoration:none;font-size:.9rem">← Nouvel audit</a>
					<button onclick="window.print()" style="padding:10px 20px;background:rgba(212,180,92,.15);border:1px solid #D4B45C;color:#D4B45C;border-radius:8px;font-weight:700;cursor:pointer">🖨️ Imprimer / Sauver en PDF</button>
				</div>

				<div style="text-align:center;margin-bottom:40px">
					<span style="color:rgba(255,255,255,.6);letter-spacing:3px;font-size:.85rem;text-transform:uppercase">Audit SEO · Alliance Groupe</span>
					<h1 style="font-family:Georgia,serif;font-size:2.2rem;line-height:1.2;margin:14px 0 8px">
						<?php if ( $prenom ) echo esc_html( $prenom ) . ', voici votre rapport'; else echo 'Votre rapport SEO'; ?>
					</h1>
					<p style="color:rgba(255,255,255,.7);margin:0">Pour <strong style="color:#D4B45C"><?php echo esc_html( $a['url'] ); ?></strong> · <?php echo wp_date( 'd/m/Y', $a['ts'] ?? time() ); ?></p>
				</div>

				<div style="text-align:center;margin:30px 0 50px">
					<div style="display:inline-block;position:relative;width:200px;height:200px">
						<svg viewBox="0 0 120 120" style="width:100%;height:100%;transform:rotate(-90deg)">
							<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="10"/>
							<circle cx="60" cy="60" r="52" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="10" stroke-dasharray="<?php echo (int) ( $score * 3.27 ); ?> 327" stroke-linecap="round"/>
						</svg>
						<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
							<div style="font-size:3rem;font-weight:900;color:<?php echo esc_attr( $color ); ?>"><?php echo $score; ?></div>
							<div style="font-size:.85rem;color:rgba(255,255,255,.6)">/ 100</div>
						</div>
					</div>
					<p style="margin:18px 0 0;color:#fff;font-size:1.1rem"><?php echo esc_html( $verdict ); ?></p>
				</div>

				<h2 style="font-family:Georgia,serif;font-size:1.5rem;margin:0 0 18px;color:#D4B45C">Détail des 12 points</h2>
				<div style="display:flex;flex-direction:column;gap:10px">
					<?php foreach ( ( $a['checks'] ?? array() ) as $c ) :
						$ico = 'ok' === $c['status'] ? '✅' : ( 'warn' === $c['status'] ? '⚠️' : '❌' );
						$col = 'ok' === $c['status'] ? '#28a745' : ( 'warn' === $c['status'] ? '#F37A1F' : '#E10F1A' );
					?>
						<div style="background:rgba(255,255,255,.03);border-left:3px solid <?php echo esc_attr( $col ); ?>;padding:14px 18px;border-radius:8px">
							<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:6px">
								<strong style="color:#fff;font-size:1rem"><?php echo $ico; ?> <?php echo esc_html( $c['name'] ); ?></strong>
								<span style="color:<?php echo esc_attr( $col ); ?>;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px"><?php echo esc_html( $c['status'] ); ?></span>
							</div>
							<p style="color:rgba(255,255,255,.78);font-size:.9rem;margin:0 0 4px"><?php echo esc_html( $c['msg'] ); ?></p>
							<?php if ( ! empty( $c['advice'] ) ) : ?>
								<p style="color:rgba(212,180,92,.85);font-size:.85rem;margin:0;font-style:italic">→ <?php echo esc_html( $c['advice'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div style="margin-top:50px;padding:30px;background:linear-gradient(135deg,rgba(212,180,92,.10) 0%,rgba(20,20,22,.6) 100%);border:2px solid rgba(212,180,92,.4);border-radius:16px;text-align:center">
					<h3 style="font-family:Georgia,serif;font-size:1.6rem;margin:0 0 10px;color:#fff">Envie qu'on s'en occupe ?</h3>
					<p style="color:rgba(255,255,255,.8);margin:0 0 22px">On corrige tous ces points, on optimise votre site et on vous remet un PDF de suivi sous 7 jours.</p>
					<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#0a0a0f;font-weight:800;border-radius:999px;text-decoration:none;letter-spacing:1px;text-transform:uppercase;font-size:.9rem">Voir nos packs →</a>
				</div>
			</div>
		</section>
		<style>@media print { body { background:#fff!important;color:#000!important } section { background:#fff!important;color:#000!important } a, button { display:none!important } h1,h2,h3,strong { color:#000!important } div, p { color:#222!important } }</style>
	<?php }
}
