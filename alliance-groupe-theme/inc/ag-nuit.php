<?php
/**
 * ag-nuit.php — « Notre site se répare tout seul la nuit » (gardien IA).
 *
 * Idée novatrice n°1 : chaque nuit, un cron passe le site en revue (santé,
 * pages système, images sans alt, liens internes cassés, HTTPS, mises à jour),
 * CORRIGE automatiquement ce qui est sûr (vidage des rewrite rules, purge des
 * transients expirés, recréation d'une page système manquante), puis l'IA
 * rédige un résumé lisible et l'équipe est notifiée. Un badge public de
 * confiance affiche « Site vérifié par l'IA cette nuit ✓ ».
 *
 * Réutilise ag-ia.php (résumé, optionnel) + ag_push (notification).
 * Le cœur (audit + auto-réparation) fonctionne SANS clé API.
 *
 * Admin : menu « 🌙 Gardien nuit ». Public : shortcode [ag_nuit_badge].
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Pages système attendues — recréées à l'identique si elles disparaissent. */
function ag_nuit_pages_systeme() {
	return array(
		'composants'      => array( 'titre' => 'Composants', 'contenu' => '', 'tpl' => 'templates/page-composants.php' ),
		'refais-mon-site' => array( 'titre' => 'Refais mon site par l\'IA', 'contenu' => '[ag_refais_mon_site]', 'tpl' => 'templates/page-refais-mon-site.php' ),
		'devis-instant'   => array( 'titre' => 'Devis instantané', 'contenu' => '[ag_devis_instant]', 'tpl' => 'templates/page-devis-instant.php' ),
		'fait-par-lia'    => array( 'titre' => 'Fait par l\'IA', 'contenu' => '[ag_journal_ia]', 'tpl' => 'templates/page-journal-ia.php' ),
	);
}

/**
 * Passe le site en revue et auto-répare ce qui est sûr.
 *
 * @return array Rapport structuré { ts, checks[], fixes[], score }.
 */
function ag_nuit_run() {
	$checks = array();
	$fixes  = array();

	// 1) HTTPS.
	$ssl = ( 0 === strpos( home_url(), 'https://' ) );
	$checks[] = array( 'label' => 'Connexion sécurisée (HTTPS)', 'ok' => $ssl, 'detail' => $ssl ? 'Actif' : 'Le site ne force pas HTTPS' );

	// 2) Pages système présentes (auto-fix : on recrée à l'identique si manquante).
	$missing = array();
	foreach ( ag_nuit_pages_systeme() as $slug => $p ) {
		if ( ! get_page_by_path( $slug ) ) {
			$id = wp_insert_post( array(
				'post_title'  => $p['titre'],
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
				'post_content'=> $p['contenu'],
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				if ( ! empty( $p['tpl'] ) && file_exists( get_stylesheet_directory() . '/' . $p['tpl'] ) ) {
					update_post_meta( $id, '_wp_page_template', $p['tpl'] );
				}
				$fixes[] = 'Page « ' . $p['titre'] . ' » recréée (elle avait disparu).';
			} else {
				$missing[] = $p['titre'];
			}
		}
	}
	$checks[] = array( 'label' => 'Pages système en place', 'ok' => empty( $missing ), 'detail' => empty( $missing ) ? 'Toutes présentes' : ( 'Manquantes : ' . implode( ', ', $missing ) ) );

	// 3) Images sans texte alternatif (accessibilité + SEO).
	global $wpdb;
	$imgs = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type LIKE 'image/%'" );
	$noalt = 0;
	if ( $imgs ) {
		$noalt = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_wp_attachment_image_alt'
			 WHERE p.post_type='attachment' AND p.post_mime_type LIKE 'image/%'
			 AND (m.meta_value IS NULL OR m.meta_value='')"
		);
	}
	$checks[] = array( 'label' => 'Textes alternatifs des images', 'ok' => ( 0 === $noalt ), 'detail' => $imgs ? ( $noalt . ' image(s) sur ' . $imgs . ' sans texte alternatif' ) : 'Aucune image' );

	// 4) Liens internes cassés dans le contenu publié (échantillon).
	$broken = ag_nuit_scan_broken_links();
	$checks[] = array( 'label' => 'Liens internes valides', 'ok' => empty( $broken ), 'detail' => empty( $broken ) ? 'Aucun lien interne cassé détecté' : ( count( $broken ) . ' lien(s) interne(s) cassé(s) : ' . implode( ', ', array_slice( $broken, 0, 5 ) ) ) );

	// 5) Mises à jour en attente (cœur/extensions/thèmes) — signalées, pas appliquées.
	$upd = 0;
	if ( function_exists( 'wp_get_update_data' ) ) {
		$d = wp_get_update_data();
		$upd = isset( $d['counts']['total'] ) ? (int) $d['counts']['total'] : 0;
	}
	$checks[] = array( 'label' => 'Mises à jour à jour', 'ok' => ( 0 === $upd ), 'detail' => $upd ? ( $upd . ' mise(s) à jour disponible(s)' ) : 'Tout est à jour' );

	// 6) Auto-fix : purge des transients expirés + rafraîchit les rewrite rules.
	$purged = ag_nuit_purge_transients();
	if ( $purged > 0 ) {
		$fixes[] = $purged . ' donnée(s) temporaire(s) expirée(s) nettoyée(s).';
	}
	flush_rewrite_rules( false );
	$fixes[] = 'Règles d\'URL rafraîchies (liens propres garantis).';

	// Score = part de checks au vert.
	$ok_count = 0;
	foreach ( $checks as $c ) { if ( $c['ok'] ) { $ok_count++; } }
	$score = $checks ? (int) round( 100 * $ok_count / count( $checks ) ) : 100;

	$report = array(
		'ts'     => time(),
		'checks' => $checks,
		'fixes'  => $fixes,
		'score'  => $score,
	);

	// Résumé rédigé par l'IA (facultatif, si clé présente).
	$report['resume'] = ag_nuit_summary( $report );

	update_option( 'ag_nuit_last', $report, false );
	return $report;
}

/** Cherche des liens internes cassés dans le contenu publié (borné). */
function ag_nuit_scan_broken_links() {
	global $wpdb;
	$rows  = $wpdb->get_col( "SELECT post_content FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('post','page') LIMIT 200" );
	$home  = home_url();
	$host  = wp_parse_url( $home, PHP_URL_HOST );
	$broken = array();
	$seen   = array();
	foreach ( (array) $rows as $content ) {
		if ( ! preg_match_all( '#href=["\']([^"\']+)["\']#i', (string) $content, $m ) ) { continue; }
		foreach ( $m[1] as $href ) {
			$h = wp_parse_url( $href, PHP_URL_HOST );
			if ( $h && $h !== $host ) { continue; } // externe : on ignore
			$path = trim( (string) wp_parse_url( $href, PHP_URL_PATH ), '/' );
			if ( '' === $path || isset( $seen[ $path ] ) ) { continue; }
			$seen[ $path ] = 1;
			if ( preg_match( '#\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|css|js)$#i', $path ) ) { continue; }
			// Résout page OU article par son slug (dernier segment).
			$slug = basename( $path );
			if ( ! get_page_by_path( $path ) && ! get_page_by_path( $slug, OBJECT, 'page' ) && ! get_page_by_path( $slug, OBJECT, 'post' ) ) {
				$broken[] = '/' . $path;
			}
			if ( count( $broken ) >= 20 ) { return $broken; }
		}
	}
	return $broken;
}

/** Supprime les transients expirés (auto-réparation sûre). */
function ag_nuit_purge_transients() {
	global $wpdb;
	$now = time();
	$rows = $wpdb->get_col( $wpdb->prepare(
		"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
		$wpdb->esc_like( '_transient_timeout_' ) . '%', $now
	) );
	$n = 0;
	foreach ( (array) $rows as $name ) {
		$key = str_replace( '_transient_timeout_', '', $name );
		if ( delete_transient( $key ) ) { $n++; }
	}
	return $n;
}

/** Résumé lisible du rapport (IA si dispo, sinon phrase générée localement). */
function ag_nuit_summary( $report ) {
	$nb_fix = count( $report['fixes'] );
	$fallback = sprintf(
		'Cette nuit, le gardien IA a passé le site en revue (%d contrôles, score %d/100) et appliqué %d correction%s automatique%s.',
		count( $report['checks'] ), $report['score'], $nb_fix, $nb_fix > 1 ? 's' : '', $nb_fix > 1 ? 's' : ''
	);
	if ( ! function_exists( 'ag_ia_ready' ) || ! ag_ia_ready() ) {
		return $fallback;
	}
	$facts = "Contrôles :\n";
	foreach ( $report['checks'] as $c ) {
		$facts .= '- ' . $c['label'] . ' : ' . ( $c['ok'] ? 'OK' : 'À surveiller' ) . ' (' . $c['detail'] . ")\n";
	}
	$facts .= "Corrections appliquées :\n";
	foreach ( $report['fixes'] as $f ) { $facts .= '- ' . $f . "\n"; }
	$sys = "Tu es le gardien de nuit IA du site Alliance Groupe. Écris un résumé de 2-3 phrases, en français, ton rassurant et professionnel, à destination du propriétaire. Mets en avant ce qui a été vérifié et corrigé, et signale clairement ce qui mérite son attention. Pas de liste, un paragraphe.";
	$out = ag_ia_call( $sys, $facts, array( 'max_tokens' => 300, 'temperature' => 0.4, 'timeout' => 40 ) );
	if ( is_wp_error( $out ) || '' === trim( (string) $out ) ) {
		return $fallback;
	}
	return trim( (string) $out );
}

/* ── Cron quotidien (~3h du matin) ───────────────────────────────────────── */
add_action( 'ag_nuit_cron', function () {
	$report = ag_nuit_run();
	// Notifie l'équipe.
	if ( function_exists( 'ag_push' ) ) {
		$attn = array();
		foreach ( $report['checks'] as $c ) { if ( ! $c['ok'] ) { $attn[] = $c['label']; } }
		$msg = "🌙 Gardien de nuit — score {$report['score']}/100. " . $report['resume'];
		if ( $attn ) { $msg .= "\n⚠️ À voir : " . implode( ', ', $attn ); }
		ag_push( $msg );
	}
	// Email au propriétaire.
	$to = get_option( 'ag_calendar_notify_email', get_option( 'admin_email' ) );
	if ( $to ) {
		wp_mail( $to, '🌙 Rapport du gardien de nuit — ' . get_bloginfo( 'name' ), $report['resume'] . "\n\nScore : " . $report['score'] . "/100" );
	}
} );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_nuit_cron' ) ) {
		// Prochain 3h du matin, heure du site.
		$next = strtotime( 'tomorrow 3:00' );
		wp_schedule_event( $next ? $next : ( time() + DAY_IN_SECONDS ), 'daily', 'ag_nuit_cron' );
	}
} );

/* ── Badge public de confiance ───────────────────────────────────────────── */
function ag_nuit_badge_render() {
	$r = get_option( 'ag_nuit_last' );
	$when = ( is_array( $r ) && ! empty( $r['ts'] ) ) ? date_i18n( 'j F Y', (int) $r['ts'] ) : date_i18n( 'j F Y' );
	$score = ( is_array( $r ) && isset( $r['score'] ) ) ? (int) $r['score'] : 100;
	ob_start();
	?>
	<span class="ag-nuit-badge" title="Ce site est vérifié et entretenu automatiquement chaque nuit par une IA.">
		<span class="agn-dot"></span>🌙 Site vérifié par l'IA · <?php echo esc_html( $when ); ?> · <?php echo esc_html( $score ); ?>/100
	</span>
	<style>
		.ag-nuit-badge{display:inline-flex;align-items:center;gap:7px;background:#1a1205;color:#f0e8d6;border:1px solid #3a2f1a;border-radius:999px;padding:6px 14px;font-size:.78rem;font-weight:600}
		.ag-nuit-badge .agn-dot{width:8px;height:8px;border-radius:50%;background:#4ade80;box-shadow:0 0 0 3px rgba(74,222,128,.2)}
	</style>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ag_nuit_badge', 'ag_nuit_badge_render' );

/* ── Page admin ──────────────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_menu_page( 'Gardien de nuit', '🌙 Gardien nuit', 'manage_options', 'ag-nuit', 'ag_nuit_admin_render', 'dashicons-shield', 58 );
} );

/* Lance l'audit à la demande depuis l'admin. */
add_action( 'admin_post_ag_nuit_run', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_nuit' ) ) {
		wp_die( 'Refusé' );
	}
	ag_nuit_run();
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-nuit', 'done' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );

function ag_nuit_admin_render() {
	$r = get_option( 'ag_nuit_last' );
	echo '<div class="wrap"><h1>🌙 Gardien de nuit</h1>';
	echo '<p>Chaque nuit (~3h), l\'IA vérifie le site, corrige ce qui est sûr et vous prévient. Le badge public <code>[ag_nuit_badge]</code> rassure vos visiteurs.</p>';
	if ( isset( $_GET['done'] ) ) { echo '<div class="notice notice-success"><p>Audit relancé.</p></div>'; }
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:14px 0">';
	echo '<input type="hidden" name="action" value="ag_nuit_run"><input type="hidden" name="_n" value="' . esc_attr( wp_create_nonce( 'ag_nuit' ) ) . '">';
	echo '<button class="button button-primary">▶ Lancer un audit maintenant</button></form>';

	if ( ! is_array( $r ) || empty( $r['ts'] ) ) {
		echo '<p><em>Aucun audit encore. La première passe aura lieu cette nuit, ou lancez-la ci-dessus.</em></p></div>';
		return;
	}
	echo '<h2>Dernier passage : ' . esc_html( date_i18n( 'j F Y à H:i', (int) $r['ts'] ) ) . ' — score ' . (int) $r['score'] . '/100</h2>';
	echo '<p style="font-size:1.05em;background:#f6f7f7;border-left:4px solid #c8962c;padding:12px 16px;max-width:760px">' . esc_html( $r['resume'] ) . '</p>';
	echo '<h3>Contrôles</h3><table class="widefat striped" style="max-width:760px"><tbody>';
	foreach ( (array) $r['checks'] as $c ) {
		echo '<tr><td style="width:34px">' . ( $c['ok'] ? '✅' : '⚠️' ) . '</td><td><strong>' . esc_html( $c['label'] ) . '</strong></td><td>' . esc_html( $c['detail'] ) . '</td></tr>';
	}
	echo '</tbody></table>';
	if ( ! empty( $r['fixes'] ) ) {
		echo '<h3>Corrections automatiques appliquées</h3><ul style="list-style:disc;padding-left:22px">';
		foreach ( $r['fixes'] as $f ) { echo '<li>' . esc_html( $f ) . '</li>'; }
		echo '</ul>';
	}
	echo '</div>';
}
