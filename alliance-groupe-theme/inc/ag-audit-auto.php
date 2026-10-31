<?php
/**
 * AG — AUDIT AUTO (du début à la fin, 1 bouton).
 * Scanner de sécurité PASSIF côté serveur : on entre une URL, tout s'exécute
 * automatiquement (en-têtes, versions, xmlrpc, énumération, fichiers exposés,
 * SPF/DMARC, SEO, RGPD), puis findings + DEVIS + rapport client prêt.
 *
 * Non intrusif : uniquement des requêtes GET/HEAD/1 POST xmlrpc = ce qu'un
 * visiteur ou Google voit. AUCUN brute-force / fuzzing / injection ici (ça
 * reste sur Kali, sous mandat). Couche HTTP branchable : wp_remote_* dans
 * WordPress, cURL en CLI (pour tests hors WordPress).
 */

if ( ! defined( 'ABSPATH' ) && 'cli' !== php_sapi_name() ) { exit; }

/* ============================ COUCHE HTTP BRANCHABLE ============================ */
if ( ! function_exists( 'ag_aa_http' ) ) {
	function ag_aa_http( $method, $url, $body = null, $follow = false ) {
		$ua = 'Mozilla/5.0 (Alliance-Groupe-Audit; mandat)';
		if ( function_exists( 'wp_remote_request' ) ) {
			$args = array(
				'method' => $method, 'timeout' => 4,
				'redirection' => $follow ? 3 : 0,
				'user-agent' => $ua, 'sslverify' => true,
				'headers' => array(), 'body' => $body,
			);
			$r = wp_remote_request( $url, $args );
			if ( is_wp_error( $r ) ) { return array( 'code' => 0, 'headers' => array(), 'body' => '', 'loc' => '' ); }
			$h = array();
			foreach ( wp_remote_retrieve_headers( $r ) as $k => $v ) { $h[ strtolower( $k ) ] = is_array( $v ) ? implode( ', ', $v ) : $v; }
			return array(
				'code' => (int) wp_remote_retrieve_response_code( $r ),
				'headers' => $h,
				'body' => (string) wp_remote_retrieve_body( $r ),
				'loc' => (string) wp_remote_retrieve_header( $r, 'location' ),
			);
		}
		// Fallback cURL (CLI / hors WordPress)
		$ch = curl_init( $url );
		$opt = array(
			CURLOPT_RETURNTRANSFER => 1, CURLOPT_HEADER => 1,
			CURLOPT_CUSTOMREQUEST => $method, CURLOPT_NOBODY => ( 'HEAD' === $method ),
			CURLOPT_FOLLOWLOCATION => (bool) $follow, CURLOPT_MAXREDIRS => 3,
			CURLOPT_TIMEOUT => 4, CURLOPT_USERAGENT => $ua,
			CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0,
		);
		$px = getenv( 'HTTPS_PROXY' ); if ( $px ) { $opt[ CURLOPT_PROXY ] = $px; }
		if ( null !== $body ) { $opt[ CURLOPT_POSTFIELDS ] = $body; }
		curl_setopt_array( $ch, $opt );
		$resp = curl_exec( $ch );
		$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$hsz  = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		curl_close( $ch );
		$rawh = substr( (string) $resp, 0, $hsz );
		$bod  = substr( (string) $resp, $hsz );
		$h = array(); $loc = '';
		foreach ( preg_split( '/\r?\n/', $rawh ) as $line ) {
			if ( false !== strpos( $line, ':' ) ) {
				list( $k, $v ) = explode( ':', $line, 2);
				$k = strtolower( trim( $k ) ); $h[ $k ] = trim( $v );
				if ( 'location' === $k ) { $loc = trim( $v ); }
			}
		}
		return array( 'code' => (int) $code, 'headers' => $h, 'body' => $bod, 'loc' => $loc );
	}
}

/* ============================ PRIX PAR GRAVITÉ (éditables) ============================ */
if ( ! function_exists( 'ag_aa_prices' ) ) {
	function ag_aa_prices() {
		$d = array( 'critique' => 900, 'eleve' => 500, 'moyen' => 250, 'faible' => 90 );
		if ( function_exists( 'get_option' ) ) {
			$o = get_option( 'ag_aa_prices' );
			if ( is_array( $o ) ) { $d = array_merge( $d, array_map( 'intval', $o ) ); }
		}
		return $d;
	}
	function ag_aa_price( $sev ) { $p = ag_aa_prices(); return isset( $p[ $sev ] ) ? (int) $p[ $sev ] : 0; }
}

/* ============================ LE SCAN AUTO ============================ */
if ( ! function_exists( 'ag_audit_auto' ) ) {
	function ag_audit_auto( $url ) {
		if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 120 ); } // évite la coupure PHP (30s) sur cible lente
		$t0 = microtime( true );
		$deadline = $t0 + 22; // budget dur : au-delà on saute les sondes optionnelles et on renvoie ce qu'on a
		$url = trim( $url );
		if ( ! preg_match( '#^https?://#i', $url ) ) { $url = 'https://' . $url; }
		$host = preg_replace( '#^https?://#i', '', $url );
		$host = preg_replace( '#/.*$#', '', $host );
		$base = 'https://' . $host;

		$F = array();  // findings facturables
		$G = array();  // bons points
		$add  = function( $key, $label, $sev, $proof, $advice ) use ( &$F ) { $F[] = array( 'key' => $key, 'label' => $label, 'sev' => $sev, 'price' => ag_aa_price( $sev ), 'proof' => $proof, 'advice' => $advice ); };
		$good = function( $txt ) use ( &$G ) { $G[] = $txt; };

		// 0. Joignable + redirection + serveur
		$r = ag_aa_http( 'GET', $base . '/', null, true );
		if ( ! $r['code'] ) {
			// essai sans www / avec www
			$alt = ( 0 === stripos( $host, 'www.' ) ) ? substr( $host, 4 ) : ( 'www.' . $host );
			$r2 = ag_aa_http( 'GET', 'https://' . $alt . '/', null, true );
			if ( $r2['code'] ) { $host = $alt; $base = 'https://' . $host; $r = $r2; }
		}
		if ( ! $r['code'] ) { return array( 'ok' => false, 'host' => $host, 'error' => 'Cible injoignable (DNS/réseau).' ); }

		// Redirection http->https / www : on retient l'URL finale
		$nu = ag_aa_http( 'GET', 'http://' . $host . '/', null, false );
		$final_host = $host;
		if ( ! empty( $r['loc'] ) && preg_match( '#^https?://([^/]+)#i', $r['loc'], $lm ) ) { $final_host = $lm[1]; }
		elseif ( preg_match( '#^https?://([^/]+)#i', (string) $nu['loc'], $lm2 ) ) { $final_host = $lm2[1]; }
		if ( strtolower( $final_host ) !== strtolower( $host ) ) { $host = $final_host; $base = 'https://' . $host; $r = ag_aa_http( 'GET', $base . '/', null, true ); }

		$html    = (string) $r['body'];
		$headers = $r['headers'];
		$server  = isset( $headers['server'] ) ? $headers['server'] : '';

		// HTTP forcé ? On teste le domaine racine en http. On ne flague QUE si le site est servi EN CLAIR (200 sans redirection https) — un 3xx/4xx n'est pas une preuve d'absence de redirection.
		$root_h = preg_replace( '#^www\.#i', '', $host );
		$nuc = ag_aa_http( 'GET', 'http://' . $root_h . '/', null, false );
		if ( 200 === (int) $nuc['code'] && false === stripos( (string) $nuc['loc'], 'https://' ) ) { $add( 'tls', 'HTTPS non forcé (site servi en clair)', 'moyen', 'http://' . $root_h . '/ → 200 sans redirection HTTPS', 'Forcer la redirection 301 HTTP→HTTPS + HSTS.' ); }
		else { $good( 'HTTP redirigé vers HTTPS / non servi en clair.' ); }

		// 1. En-têtes de sécurité
		$want = array( 'strict-transport-security' => 'HSTS', 'content-security-policy' => 'CSP', 'x-frame-options' => 'X-Frame-Options', 'x-content-type-options' => 'X-Content-Type-Options', 'referrer-policy' => 'Referrer-Policy', 'permissions-policy' => 'Permissions-Policy' );
		$missing = array();
		foreach ( $want as $hk => $hn ) { if ( empty( $headers[ $hk ] ) ) { $missing[] = $hn; } }
		if ( $missing ) { $add( 'headers', count( $missing ) . ' en-tête(s) de sécurité manquant(s)', 'moyen', 'Manquants : ' . implode( ', ', $missing ), 'Ajouter ' . implode( ', ', $missing ) . ' (clickjacking, MIME-sniffing, HSTS).' ); }
		else { $good( 'En-têtes de sécurité présents.' ); }

		// 2. Techno + versions exposées
		$is_wp = ( false !== stripos( $html, 'wp-content' ) || false !== stripos( $html, 'wp-includes' ) || preg_match( '#generator[^>]+WordPress#i', $html ) );
		$vers = array();
		if ( preg_match_all( '#name=[\'"]generator[\'"][^>]+content=[\'"]([^\'"]+)[\'"]#i', $html, $gm ) ) { foreach ( $gm[1] as $g ) { $vers[] = trim( $g ); } }
		if ( $server && preg_match( '#[0-9]+\.[0-9]#', $server ) ) { $vers[] = 'Serveur: ' . $server; }
		$plugins = array();
		if ( preg_match_all( '#wp-content/plugins/([a-z0-9\-]+)#i', $html, $pm ) ) { $plugins = array_values( array_unique( $pm[1] ) ); }
		$beta = array_filter( $vers, function( $v ) { return (bool) preg_match( '#beta|alpha|rc[0-9]#i', $v ); } );
		if ( $vers || $plugins ) {
			$pr = trim( implode( ' | ', array_slice( $vers, 0, 6 ) ) . ( $plugins ? ' | plugins: ' . implode( ', ', array_slice( $plugins, 0, 10 ) ) : '' ) );
			$add( $beta ? 'version' : 'version', 'Versions / technologies exposées' . ( $beta ? ' (dont une version BETA en prod)' : '' ), $beta ? 'moyen' : 'faible', $pr, 'Masquer les versions (générateur, en-têtes serveur) ; mettre à jour les composants ; retirer les versions beta de la prod.' );
		} else { $good( 'Peu/pas de versions exposées dans le code.' ); }

		// 3. Énumération des comptes — REST + ?author
		$leak_users = array();
		$ru = ag_aa_http( 'GET', $base . '/wp-json/wp/v2/users', null, false );
		$rest_open = ( 200 === (int) $ru['code'] && preg_match( '#"slug"\s*:\s*"#', (string) $ru['body'] ) );
		if ( $rest_open && preg_match_all( '#"slug"\s*:\s*"([^"]+)"#', $ru['body'], $sm ) ) { $leak_users = array_merge( $leak_users, $sm[1] ); }
		$au = ag_aa_http( 'GET', $base . '/?author=1', null, false );
		if ( preg_match( '#/author/([^/]+)/?#i', (string) $au['loc'], $am ) ) { $leak_users[] = $am[1]; }
		$leak_users = array_values( array_unique( array_filter( $leak_users ) ) );
		if ( $rest_open || ! empty( $am[1] ) ) {
			$add( 'enum', 'Énumération des comptes ouverte (login admin exposé)', 'moyen', 'Logins : ' . ( $leak_users ? implode( ', ', array_slice( $leak_users, 0, 8 ) ) : '(exposés)' ), 'Fermer /wp-json/wp/v2/users aux non-connectés et bloquer la redirection ?author=.' );
		} elseif ( $is_wp ) { $good( 'Énumération des comptes fermée (REST + ?author).' ); }

		// 4. Fichiers sensibles / sauvegardes (sauté si on a dépassé le budget de temps)
		if ( microtime( true ) < $deadline ) {
			$bad_files = array();
			foreach ( array( '.git/HEAD', '.env', 'wp-config.php.bak', 'backup.zip', 'database.sql' ) as $f ) {
				if ( microtime( true ) > $deadline ) { break; }
				$rf = ag_aa_http( 'GET', $base . '/' . $f, null, false );
				if ( 200 === (int) $rf['code'] ) {
					$b0 = substr( (string) $rf['body'], 0, 200 );
					if ( false === stripos( $b0, '<html' ) && false === stripos( $b0, '<!doctype' ) ) { $bad_files[] = $f; }
				}
			}
			if ( $bad_files ) { $add( 'leakfile', 'Fichiers sensibles exposés', 'critique', 'Téléchargeables : ' . implode( ', ', $bad_files ), 'Supprimer/bloquer immédiatement (identifiants, base, code source).' ); }
			else { $good( 'Pas de .git/.env/sauvegardes accessibles.' ); }
		}

		// 5. xmlrpc (amplification brute / pingback)
		$xmlrpc_active = false;
		$xr = ag_aa_http( 'POST', $base . '/xmlrpc.php', '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>' );
		if ( 200 === (int) $xr['code'] && false !== stripos( (string) $xr['body'], 'methodResponse' ) ) {
			$xmlrpc_active = true;
			$ping = ( false !== stripos( $xr['body'], 'pingback.ping' ) );
			$add( 'xmlrpc', 'xmlrpc.php actif' . ( $ping ? ' (pingback = amplification DDoS/brute)' : '' ), 'moyen', 'system.listMethods répond' . ( $ping ? ', pingback.ping disponible' : '' ), 'Désactiver xmlrpc.php ou au minimum pingback.ping.' );
		} else { $good( 'xmlrpc.php non exploitable (désactivé/filtré).' ); }

		// 6. Email : SPF / DMARC via DNS-over-HTTPS (Google) — sauté si budget dépassé
		$root = preg_replace( '#^www\.#i', '', $host );
		if ( microtime( true ) < $deadline ) {
			$spf_r = ag_aa_http( 'GET', 'https://dns.google/resolve?name=' . rawurlencode( $root ) . '&type=TXT', null, true );
			$dm_r  = ag_aa_http( 'GET', 'https://dns.google/resolve?name=_dmarc.' . rawurlencode( $root ) . '&type=TXT', null, true );
			$has_spf  = (bool) preg_match( '#v=spf1#i', (string) $spf_r['body'] );
			$dmarc_pol = preg_match( '#v=DMARC1[^"]*p=([a-z]+)#i', (string) $dm_r['body'], $dmm ) ? strtolower( $dmm[1] ) : '';
			if ( ! $has_spf ) { $add( 'email', 'SPF absent (usurpation e-mail possible)', 'moyen', 'Aucun enregistrement v=spf1 sur ' . $root, 'Publier un SPF (-all) + DKIM + DMARC.' ); }
			else { $good( 'SPF présent.' ); }
			if ( ! $dmarc_pol ) { $add( 'email', 'DMARC absent', 'moyen', 'Aucun _dmarc sur ' . $root, 'Publier un DMARC (p=quarantine puis reject).' ); }
			elseif ( 'none' === $dmarc_pol ) { $add( 'email', 'DMARC en p=none (ne bloque pas l\'usurpation)', 'faible', '_dmarc = p=none (surveillance seule)', 'Passer DMARC en p=quarantine puis p=reject.' ); }
			else { $good( 'DMARC actif (p=' . $dmarc_pol . ').' ); }
		}

		// 7. RGPD (cookies / traceurs / Google Fonts / mentions)
		$rgpd = array();
		if ( false !== stripos( $html, 'fonts.googleapis.com' ) ) { $rgpd[] = 'Google Fonts chargées depuis Google (transfert d\'IP)'; }
		if ( preg_match( '#google-analytics|googletagmanager|gtag\(|facebook\.net|hotjar|clarity\.ms#i', $html ) && ! preg_match( '#cookie|consent|tarteaucitron|axeptio|cookiebot|didomi#i', $html ) ) { $rgpd[] = 'Traceurs sans bandeau de consentement visible'; }
		if ( ! preg_match( '#mentions.?l[eé]gales|politique.{0,3}confidentialit#i', $html ) ) { $rgpd[] = 'Pas de lien mentions légales / confidentialité en page d\'accueil'; }
		if ( $rgpd ) { $add( 'rgpd', 'Non-conformité RGPD à corriger', 'moyen', implode( ' ; ', $rgpd ), 'CMP bloquante, polices locales, politique de confidentialité + mentions légales.' ); }
		else { $good( 'RGPD : pas d\'anomalie évidente (fonts/traceurs/mentions).' ); }

		// 8. SEO technique de base
		$seo = array();
		if ( ! preg_match( '#<title[^>]*>[^<]{3,}#i', $html ) ) { $seo[] = 'title'; }
		if ( ! preg_match( '#name=[\'"]description[\'"]#i', $html ) ) { $seo[] = 'meta description'; }
		if ( ! preg_match( '#name=[\'"]viewport[\'"]#i', $html ) ) { $seo[] = 'viewport (mobile)'; }
		if ( $seo ) { $add( 'seo', 'SEO technique à corriger', 'faible', 'Manquant : ' . implode( ', ', $seo ), 'Ajouter title + meta description uniques + viewport.' ); }
		else { $good( 'SEO de base présent (title/description/viewport).' ); }

		// 9. Performance (compression / cache)
		$ce = isset( $headers['content-encoding'] ) ? $headers['content-encoding'] : '';
		$cc = isset( $headers['cache-control'] ) ? $headers['cache-control'] : '';
		$perf = array();
		if ( ! preg_match( '#gzip|br#i', $ce ) ) { $perf[] = 'compression (gzip/brotli) absente'; }
		if ( '' === $cc && empty( $headers['expires'] ) ) { $perf[] = 'cache navigateur non configuré'; }
		if ( strlen( $html ) > 1500000 ) { $perf[] = 'page HTML lourde (' . round( strlen( $html ) / 1024 ) . ' Ko)'; }
		if ( $perf ) { $add( 'perf', 'Performance à optimiser', 'faible', implode( ' ; ', $perf ), 'Activer compression + cache ; compresser images ; minifier.' ); }
		else { $good( 'Performance : compression/cache OK.' ); }

		// Dédoublonnage par clé+label, total devis, score
		$seen = array(); $uniq = array();
		foreach ( $F as $f ) { $sig = $f['key'] . '|' . $f['label']; if ( isset( $seen[ $sig ] ) ) { continue; } $seen[ $sig ] = 1; $uniq[] = $f; }
		$F = $uniq;
		$total = 0; foreach ( $F as $f ) { $total += (int) $f['price']; }
		$sev_rank = array( 'critique' => 4, 'eleve' => 3, 'moyen' => 2, 'faible' => 1 );
		usort( $F, function( $a, $b ) use ( $sev_rank ) { return ( $sev_rank[ $b['sev'] ] ?? 0 ) - ( $sev_rank[ $a['sev'] ] ?? 0 ); } );
		$score = max( 0, 100 - ( 18 * count( array_filter( $F, function( $f ) { return 'critique' === $f['sev']; } ) ) + 10 * count( array_filter( $F, function( $f ) { return 'moyen' === $f['sev'] || 'eleve' === $f['sev']; } ) ) + 4 * count( array_filter( $F, function( $f ) { return 'faible' === $f['sev']; } ) ) ) );

		return array(
			'ok' => true, 'host' => $host, 'url' => $base, 'tech' => $is_wp ? 'WordPress' : 'autre/custom',
			'findings' => array_values( $F ), 'good' => $G, 'devis' => $total, 'score' => $score,
			'time_ms' => (int) round( ( microtime( true ) - $t0 ) * 1000 ), 'ts' => time(),
			// Intel pour la PHASE ACTIVE ciblée (exécutée sur Kali, sous mandat)
			'intel' => array(
				'is_wp'    => $is_wp,
				'users'    => array_values( array_slice( $leak_users, 0, 12 ) ),
				'plugins'  => array_values( array_slice( $plugins, 0, 20 ) ),
				'versions' => array_values( array_slice( array_unique( $vers ), 0, 10 ) ),
				'xmlrpc'   => $xmlrpc_active,
			),
		);
	}
}

/* ============================ RENDU HTML (rapport brandé) ============================ */
if ( ! function_exists( 'ag_aa_render' ) ) {
	function ag_aa_esc( $s ) { return function_exists( 'esc_html' ) ? esc_html( $s ) : htmlspecialchars( (string) $s, ENT_QUOTES ); }
	function ag_aa_render( $res ) {
		if ( empty( $res['ok'] ) ) { return '<p style="color:#b91c1c">Audit impossible : ' . ag_aa_esc( $res['error'] ?? 'erreur' ) . '</p>'; }
		$sevcol = array( 'critique' => '#dc2626', 'eleve' => '#ea580c', 'moyen' => '#d97706', 'faible' => '#2563eb' );
		$sevlab = array( 'critique' => 'Critique', 'eleve' => 'Élevé', 'moyen' => 'Moyen', 'faible' => 'Faible' );
		$h  = '<div style="font-family:system-ui,Arial,sans-serif;max-width:860px">';
		$h .= '<h2 style="margin:0 0 4px">Audit de sécurité — ' . ag_aa_esc( $res['host'] ) . '</h2>';
		$h .= '<p style="color:#555;margin:0 0 12px">Socle : <b>' . ag_aa_esc( $res['tech'] ) . '</b> · Score sécurité : <b>' . (int) $res['score'] . '/100</b> · ' . count( $res['findings'] ) . ' point(s) à corriger · réalisé en ' . (int) $res['time_ms'] . ' ms (audit passif, non intrusif).</p>';
		// Devis
		$h .= '<div style="border:2px solid #E6A93C;border-radius:10px;padding:14px;margin:0 0 16px;background:#fff8ef">';
		$h .= '<h3 style="margin:0 0 8px;color:#b45309">Devis de remédiation</h3><table style="width:100%;border-collapse:collapse;font-size:14px">';
		foreach ( $res['findings'] as $f ) {
			$h .= '<tr><td style="padding:6px 4px;border-bottom:1px solid #eee"><span style="display:inline-block;min-width:62px;font-weight:700;color:' . $sevcol[ $f['sev'] ] . '">' . $sevlab[ $f['sev'] ] . '</span> ' . ag_aa_esc( $f['label'] ) . '</td><td style="padding:6px 4px;border-bottom:1px solid #eee;text-align:right;white-space:nowrap">' . (int) $f['price'] . ' €</td></tr>';
		}
		$h .= '<tr><td style="padding:8px 4px;font-weight:800">TOTAL remédiation</td><td style="padding:8px 4px;text-align:right;font-weight:800">' . (int) $res['devis'] . ' €</td></tr>';
		$h .= '</table></div>';
		// Détail findings
		$h .= '<h3 style="margin:0 0 6px">Ce qui a été trouvé (vérifiable)</h3>';
		foreach ( $res['findings'] as $f ) {
			$h .= '<div style="border-left:4px solid ' . $sevcol[ $f['sev'] ] . ';padding:4px 10px;margin:0 0 8px">';
			$h .= '<b>' . ag_aa_esc( $f['label'] ) . '</b> <span style="color:' . $sevcol[ $f['sev'] ] . '">(' . $sevlab[ $f['sev'] ] . ' · ' . (int) $f['price'] . ' €)</span><br>';
			$h .= '<span style="font-size:13px;color:#444">Constat : ' . ag_aa_esc( $f['proof'] ) . '</span><br>';
			$h .= '<span style="font-size:13px;color:#166534">Correctif : ' . ag_aa_esc( $f['advice'] ) . '</span></div>';
		}
		// Bons points
		if ( ! empty( $res['good'] ) ) {
			$h .= '<h3 style="margin:14px 0 6px">Points déjà conformes (bon signe)</h3><ul style="font-size:13px;color:#166534;margin:0">';
			foreach ( $res['good'] as $g ) { $h .= '<li>' . ag_aa_esc( $g ) . '</li>'; }
			$h .= '</ul>';
		}
		$h .= '<p style="font-size:12px;color:#888;margin-top:14px">Alliance Groupe — audit passif/non intrusif. Les tests actifs (brute-force, fuzzing, injections) se font séparément sous mandat écrit.</p></div>';
		return $h;
	}
}

/* ============================ GLUE WORDPRESS (admin + AJAX + rapport) ============================ */
if ( defined( 'ABSPATH' ) ) {

	add_action( 'admin_menu', function () {
		add_menu_page( 'Audit auto', '🔍 Audit auto', 'manage_options', 'ag-audit-auto', 'ag_aa_admin_page', 'dashicons-shield', 58 );
	} );

	function ag_aa_admin_page() {
		$manuel = get_stylesheet_directory_uri() . '/assets/tools/assistant-defi.html';
		$pref   = isset( $_GET['target'] ) ? sanitize_text_field( wp_unslash( $_GET['target'] ) ) : '';
		$auto   = ( '' !== $pref );
		echo '<div class="wrap"><h1>🔍 Audit de sécurité</h1>';
		echo '<p>Entre l\'URL de la cible, puis choisis <b>un des deux modes</b> :</p>';
		echo '<p><input type="text" id="aa_url" value="' . esc_attr( $pref ) . '" placeholder="exemple.fr" style="width:360px;padding:8px;font-size:15px"></p>';
		echo '<div style="display:flex;gap:16px;flex-wrap:wrap;max-width:900px">';
		// MODE 1 — AUTOMATIQUE
		echo '<div style="flex:1 1 380px;border:2px solid #E6A93C;border-radius:10px;padding:16px;background:#fffdf7">';
		echo '<h2 style="margin:0 0 6px">① Automatique <span style="font-weight:400;color:#777;font-size:13px">— Claude le fait pour toi</span></h2>';
		echo '<p style="margin:0 0 10px;color:#555">L\'audit passif complet s\'exécute <b>tout seul côté serveur</b> (en-têtes, versions, xmlrpc, énumération, SPF/DMARC, RGPD, SEO…). Rien à faire. Sort le rapport + le devis en ~15 s.</p>';
		echo '<button class="button button-primary button-hero" id="aa_go">▶ Lancer l\'audit automatique</button> ';
		echo '<label style="display:block;margin-top:8px"><input type="checkbox" id="aa_rep"> créer le rapport client (lien partageable)</label>';
		echo '</div>';
		// MODE 2 — MANUEL GUIDÉ
		echo '<div style="flex:1 1 380px;border:2px solid #2563eb;border-radius:10px;padding:16px;background:#f7faff">';
		echo '<h2 style="margin:0 0 6px">② Manuel guidé <span style="font-weight:400;color:#777;font-size:13px">— tu le fais, tu apprends</span></h2>';
		echo '<p style="margin:0 0 10px;color:#555">L\'assistant te donne les <b>commandes à lancer toi-même</b> (sur Kali), pas-à-pas, lit tes résultats et construit le rapport. Pour <b>aller au bout toi-même et apprendre</b>. Inclut les tests actifs (sous mandat).</p>';
		echo '<a class="button button-secondary button-hero" target="_blank" id="aa_manuel" href="' . esc_url( $manuel ) . '">▶ Ouvrir l\'assistant manuel</a>';
		echo '</div>';
		echo '</div>';
		echo '<div id="aa_out" style="margin-top:18px;max-width:900px"></div></div>';
		echo '<script>document.getElementById("aa_manuel").addEventListener("click",function(e){var u=document.getElementById("aa_url").value.trim();if(u){this.href="' . esc_js( $manuel ) . '?cible="+encodeURIComponent(u);}});</script>';
		$nonce = wp_create_nonce( 'ag_aa' );
		echo '<script>
		document.getElementById("aa_go").onclick=function(){
			var u=document.getElementById("aa_url").value.trim(); if(!u){alert("Entre une URL");return;}
			var o=document.getElementById("aa_out"); o.innerHTML="⏳ Audit en cours…";
			var fd=new FormData(); fd.append("action","ag_aa_run"); fd.append("nonce","' . $nonce . '"); fd.append("url",u); fd.append("rep",document.getElementById("aa_rep").checked?1:0);
			fetch(ajaxurl,{method:"POST",body:fd}).then(function(r){return r.json();}).then(function(j){
				o.innerHTML=(j&&j.html)?j.html:"Erreur."; if(j&&j.report_url){o.innerHTML+=\'<p><a class="button" target="_blank" href="\'+j.report_url+\'">Ouvrir le rapport client</a></p>\';}
				window.__aa=j||{};
				if(j&&j.ok&&j.can_active){ aaActiveForm(j); }
			}).catch(function(e){o.innerHTML="Erreur réseau : "+e;});
		};
		' . ( $auto ? 'setTimeout(function(){var b=document.getElementById("aa_go"); if(b&&document.getElementById("aa_url").value.trim()) b.click();},250);' : '' ) . '
		function aaActiveForm(j){
			var intel=j.intel||{};
			var sum="comptes: "+((intel.users||[]).join(", ")||"—")+" · plugins: "+((intel.plugins||[]).join(", ")||"—");
			var box=document.createElement("div");
			box.style.cssText="margin-top:18px;border:2px solid #7c2d12;border-radius:10px;padding:16px;background:#fff7f5";
			box.innerHTML=\'<h2 style="margin:0 0 6px">⚡ Passer à l\\\'audit ACTIF (sur Kali, SOUS MANDAT)</h2>\'
				+\'<p style="margin:0 0 8px;color:#555">La phase passive est finie. L\\\'actif (nmap, nuclei, wpscan ciblé, testssl, searchsploit…) s\\\'exécute <b>sur ta Kali</b> via la file de jobs — le site ne lance rien. Intel transmise : <code style="font-size:12px">\'+sum+\'</code></p>\'
				+\'<p style="margin:0 0 8px;color:#7c2d12;font-size:13px"><b>⚠️ Réservé aux cibles sous mandat écrit.</b> Tester sans autorisation = délit (art. 323-1).</p>\'
				+\'<p><input id="aa_client" placeholder="Nom du client" style="padding:7px;width:220px"> \'
				+\'<input id="aa_mandat" placeholder="Réf. mandat (ex: MANDAT-2026-001)" style="padding:7px;width:240px"> \'
				+\'<select id="aa_level" style="padding:7px"><option value="normal">normal (nmap+nuclei+wpscan)</option><option value="deep">deep (+ CVE, fichiers, brute contenu)</option><option value="max">max (+ OSINT, sous-domaines, sslyze)</option></select></p>\'
				+\'<p><label style="font-size:13px"><input type="checkbox" id="aa_mok"> Je confirme avoir un <b>mandat écrit signé</b> pour cette cible.</label></p>\'
				+\'<button class="button button-primary" id="aa_active_go">⚡ Créer le job actif pour Kali</button> <span id="aa_active_msg" style="margin-left:10px"></span>\';
			o.appendChild(box);
			document.getElementById("aa_active_go").onclick=function(){
				var msg=document.getElementById("aa_active_msg");
				if(!document.getElementById("aa_mok").checked){msg.textContent="Coche la confirmation de mandat.";return;}
				var cl=document.getElementById("aa_client").value.trim(), md=document.getElementById("aa_mandat").value.trim();
				if(!cl||!md){msg.textContent="Renseigne le client ET la référence de mandat.";return;}
				msg.textContent="⏳ Création du job…";
				var fd=new FormData(); fd.append("action","ag_aa_active"); fd.append("nonce","' . $nonce . '");
				fd.append("url",j.url||j.host); fd.append("client",cl); fd.append("mandat",md); fd.append("level",document.getElementById("aa_level").value);
				fd.append("intel",JSON.stringify(j.intel||{}));
				fetch(ajaxurl,{method:"POST",body:fd}).then(function(r){return r.json();}).then(function(x){
					msg.innerHTML=x&&x.ok?(\'✅ Job <b>\'+x.job+\'</b> créé. Sur ta Kali : lance <code>./ag-runner.sh</code> → il tire le job, lance ag-pentest.sh en local, et le rapport actif revient ici.\'):("Erreur : "+((x&&x.error)||"?"));
				}).catch(function(e){msg.textContent="Erreur réseau : "+e;});
			};
		}
		</script>';
	}

	add_action( 'wp_ajax_ag_aa_active', function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'ag_aa', 'nonce', false ) ) { wp_send_json( array( 'ok' => false, 'error' => 'Refusé.' ) ); }
		if ( ! function_exists( 'ag_pt_enqueue_job' ) ) { wp_send_json( array( 'ok' => false, 'error' => 'File de jobs pentest indisponible.' ) ); }
		$url    = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$client = isset( $_POST['client'] ) ? sanitize_text_field( wp_unslash( $_POST['client'] ) ) : '';
		$mandat = isset( $_POST['mandat'] ) ? sanitize_text_field( wp_unslash( $_POST['mandat'] ) ) : '';
		$level  = isset( $_POST['level'] ) ? sanitize_key( wp_unslash( $_POST['level'] ) ) : 'normal';
		$intel  = array();
		if ( isset( $_POST['intel'] ) ) { $intel = json_decode( wp_unslash( $_POST['intel'] ), true ); if ( ! is_array( $intel ) ) { $intel = array(); } }
		if ( ! $url || ! $client || ! $mandat ) { wp_send_json( array( 'ok' => false, 'error' => 'Client + mandat obligatoires.' ) ); }
		$id = ag_pt_enqueue_job( $url, $client, $mandat, $level, '', 'ag_audit_actif', $intel );
		wp_send_json( array( 'ok' => (bool) $id, 'job' => $id ) );
	} );

	add_action( 'wp_ajax_ag_aa_run', function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'ag_aa', 'nonce', false ) ) { wp_send_json( array( 'html' => 'Refusé.' ) ); }
		$url = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$res = ag_audit_auto( $url );
		$out = array( 'html' => ag_aa_render( $res ), 'ok' => ! empty( $res['ok'] ) );
		if ( ! empty( $res['ok'] ) ) {
			$out['host']  = $res['host'];
			$out['url']   = $res['url'];
			$out['intel'] = $res['intel'];
			$out['can_active'] = function_exists( 'ag_pt_enqueue_job' );
		}
		if ( ! empty( $_POST['rep'] ) && ! empty( $res['ok'] ) && function_exists( 'ag_rapport_create' ) ) {
			$client = isset( $_POST['client'] ) ? sanitize_text_field( wp_unslash( $_POST['client'] ) ) : '';
			$rep = ag_rapport_create( ag_aa_render( $res ), $client, $res['host'], (int) $res['devis'] );
			if ( $rep ) { update_post_meta( $rep['id'], 'ag_aa_data', wp_json_encode( $res ) ); $out['report_url'] = $rep['url']; }
		}
		wp_send_json( $out );
	} );

	/* ---- APPLICATION mobile (onglet Audit) : passif pour l'équipe, actif admin-only ---- */
	add_action( 'wp_ajax_ag_app_audit_auto', function () {
		if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) { wp_send_json_error( array( 'm' => 'Session expirée.' ) ); }
		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( '' === $url ) { wp_send_json_error( array( 'm' => 'URL manquante.' ) ); }
		$res = ag_audit_auto( $url );
		if ( empty( $res['ok'] ) ) { wp_send_json_error( array( 'm' => $res['error'] ?? 'Audit impossible.' ) ); }
		$prev = function_exists( 'ag_aa_hist_prev_score' ) ? ag_aa_hist_prev_score( $res['host'] ) : null; // score AVANT cet audit
		if ( function_exists( 'ag_aa_hist_add' ) ) { ag_aa_hist_add( $res ); }                             // enregistre dans l'historique
		$watch = function_exists( 'ag_aa_watch_list' ) ? ag_aa_watch_list() : array();
		$watched = isset( $watch[ strtolower( preg_replace( '#^www\.#i', '', $res['host'] ) ) ] );
		$report_url = '';
		if ( ! empty( $_POST['rep'] ) && function_exists( 'ag_rapport_create' ) ) {
			$client = isset( $_POST['client'] ) ? sanitize_text_field( wp_unslash( $_POST['client'] ) ) : '';
			$rep = ag_rapport_create( ag_aa_render( $res ), $client, $res['host'], (int) $res['devis'] );
			if ( $rep ) { update_post_meta( $rep['id'], 'ag_aa_data', wp_json_encode( $res ) ); $report_url = $rep['url']; }
		}
		wp_send_json_success( array(
			'html' => ag_aa_render( $res ),
			'host' => $res['host'], 'url' => $res['url'], 'intel' => $res['intel'],
			'devis' => $res['devis'], 'score' => $res['score'],
			'prev_score' => $prev, 'watched' => $watched, 'report_url' => $report_url,
			'can_active' => ( current_user_can( 'manage_options' ) && function_exists( 'ag_pt_enqueue_job' ) ),
			'can_watch'  => ( current_user_can( 'manage_options' ) && function_exists( 'ag_aa_watch_add' ) ),
			'can_report' => function_exists( 'ag_rapport_create' ),
		) );
	} );

	add_action( 'wp_ajax_ag_app_audit_active', function () {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) { wp_send_json_error( array( 'error' => 'Refusé (admin + mandat).' ) ); }
		if ( ! function_exists( 'ag_pt_enqueue_job' ) ) { wp_send_json_error( array( 'error' => 'File de jobs indisponible.' ) ); }
		$url    = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$client = isset( $_POST['client'] ) ? sanitize_text_field( wp_unslash( $_POST['client'] ) ) : '';
		$mandat = isset( $_POST['mandat'] ) ? sanitize_text_field( wp_unslash( $_POST['mandat'] ) ) : '';
		$level  = isset( $_POST['level'] ) ? sanitize_key( wp_unslash( $_POST['level'] ) ) : 'normal';
		$intel  = array();
		if ( isset( $_POST['intel'] ) ) { $intel = json_decode( wp_unslash( $_POST['intel'] ), true ); if ( ! is_array( $intel ) ) { $intel = array(); } }
		if ( ! $url || ! $client || ! $mandat ) { wp_send_json_error( array( 'error' => 'Client + mandat obligatoires.' ) ); }
		$id = ag_pt_enqueue_job( $url, $client, $mandat, $level, '', 'ag_audit_actif', $intel );
		if ( $id ) { wp_send_json_success( array( 'job' => $id ) ); }
		wp_send_json_error( array( 'error' => 'Échec création du job.' ) );
	} );

	/* ---- APPLICATION : SYNC GitHub + Purge cache (admin only) ---- */
	add_action( 'wp_ajax_ag_app_sync', function () {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
		if ( ! class_exists( 'AG_GitHub_Sync' ) ) { wp_send_json_error( array( 'm' => 'Module de sync indisponible.' ) ); }
		if ( method_exists( 'AG_GitHub_Sync', 'get_remote_sha' ) ) { AG_GitHub_Sync::get_remote_sha( 'theme', true ); } // rafraîchit le SHA distant
		$res = AG_GitHub_Sync::sync( 'theme' );
		if ( empty( $res['ok'] ) ) { wp_send_json_error( array( 'm' => $res['error'] ?? 'Erreur de sync.' ) ); }
		$total = (int) ( $res['stats']['updated'] ?? 0 ) + (int) ( $res['stats']['created'] ?? 0 );
		wp_send_json_success( array( 'total' => $total, 'm' => $total . ' fichier(s) mis à jour depuis GitHub.' ) );
	} );

	add_action( 'wp_ajax_ag_app_purge', function () {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
		global $wpdb;
		$deleted = (int) $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%ag_%' OR option_name LIKE '%transient%update_plugins%' OR option_name LIKE '%transient%update_themes%' OR option_name LIKE 'ag_licence_%' OR option_name LIKE 'ag_companion_%'" );
		if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
		if ( function_exists( 'opcache_reset' ) ) { @opcache_reset(); }
		wp_send_json_success( array( 'deleted' => $deleted, 'm' => $deleted . ' entrée(s) de cache purgée(s).' ) );
	} );

	add_action( 'wp_ajax_ag_app_prices_save', function () {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
		$in = array();
		if ( isset( $_POST['prices'] ) ) { $in = json_decode( wp_unslash( $_POST['prices'] ), true ); }
		if ( ! is_array( $in ) ) { wp_send_json_error( array( 'm' => 'Données invalides.' ) ); }
		$cur = function_exists( 'ag_aa_prices' ) ? ag_aa_prices() : array( 'critique' => 900, 'eleve' => 500, 'moyen' => 250, 'faible' => 90 );
		foreach ( array( 'critique', 'eleve', 'moyen', 'faible' ) as $k ) {
			if ( isset( $in[ $k ] ) ) { $cur[ $k ] = max( 0, min( 100000, (int) $in[ $k ] ) ); }
		}
		update_option( 'ag_aa_prices', $cur, false );
		wp_send_json_success( array( 'prices' => $cur, 'm' => 'ok' ) );
	} );

	add_action( 'wp_ajax_ag_app_paylinks_save', function () {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) { wp_send_json_error( array( 'm' => 'Refusé.' ) ); }
		$in = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : array();
		if ( ! is_array( $in ) ) { wp_send_json_error( array( 'm' => 'Données invalides.' ) ); }
		foreach ( array( 'rapport', 'correction', 'refonte' ) as $k ) {
			if ( isset( $in[ 'px_' . $k ] ) )  { update_option( 'ag_rap_px_' . $k, max( 0, (int) $in[ 'px_' . $k ] ), false ); }
			if ( isset( $in[ 'pay_' . $k ] ) ) { update_option( 'ag_rap_pay_' . $k, esc_url_raw( trim( (string) $in[ 'pay_' . $k ] ) ), false ); }
		}
		wp_send_json_success( array( 'm' => 'ok' ) );
	} );
}

/* ============================ TEST CLI (hors WordPress) ============================ */
if ( 'cli' === php_sapi_name() && isset( $argv[1] ) ) {
	$res = ag_audit_auto( $argv[1] );
	if ( empty( $res['ok'] ) ) { fwrite( STDOUT, "ERREUR: " . ( $res['error'] ?? '?' ) . "\n" ); exit( 1 ); }
	fwrite( STDOUT, "=== AUDIT AUTO: {$res['host']} ({$res['tech']}) — score {$res['score']}/100 — {$res['time_ms']}ms ===\n" );
	fwrite( STDOUT, "DEVIS TOTAL: {$res['devis']} €\n\n-- Findings (facturables) --\n" );
	foreach ( $res['findings'] as $f ) { fwrite( STDOUT, sprintf( "  [%-8s %4d€] %s\n      constat: %s\n", $f['sev'], $f['price'], $f['label'], $f['proof'] ) ); }
	fwrite( STDOUT, "\n-- Bons points --\n" );
	foreach ( $res['good'] as $g ) { fwrite( STDOUT, "  + $g\n" ); }
}
