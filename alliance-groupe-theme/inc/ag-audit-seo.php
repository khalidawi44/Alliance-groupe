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
		$rob = wp_remote_head( $host . '/robots.txt', array( 'timeout' => 8 ) );
		$rob_ok = ! is_wp_error( $rob ) && 200 === wp_remote_retrieve_response_code( $rob );
		$checks[] = array(
			'name'   => 'Fichier robots.txt',
			'status' => $rob_ok ? 'ok' : 'warn',
			'msg'    => $rob_ok ? 'Présent.' : 'Absent ou inaccessible.',
			'advice' => $rob_ok ? 'Indique à Google ce qui doit être indexé.' : 'Créer un robots.txt à la racine (même minimal) — aide Google à crawler.',
		);

		// 10. Sitemap.xml
		$sm = wp_remote_head( $host . '/sitemap.xml', array( 'timeout' => 8 ) );
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

		// Score : ok=10, warn=5, fail=0 / max
		$score = 0; $max = count( $checks ) * 10;
		foreach ( $checks as $c ) {
			$score += 'ok' === $c['status'] ? 10 : ( 'warn' === $c['status'] ? 5 : 0 );
		}
		$score_pct = $max > 0 ? round( $score * 100 / $max ) : 0;

		return array( 'url' => $url, 'checks' => $checks, 'score' => $score_pct, 'time_ms' => $elapsed, 'ts' => time() );
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
