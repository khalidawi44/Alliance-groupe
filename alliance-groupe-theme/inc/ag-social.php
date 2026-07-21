<?php
/**
 * AG Social — publication automatique Facebook Page + Instagram Pro (API Graph Meta).
 *
 * Réutilise le pattern des autres briques (options + page d'admin + cron).
 * NE FAIT RIEN tant que les jetons ne sont pas renseignés (dégradation propre).
 *
 * Prérequis Meta (à faire UNE fois par Fabrice, guidé dans la page d'admin) :
 *  - Instagram en compte PRO (Business/Créateur) LIÉ à une Page Facebook.
 *  - Une app sur developers.facebook.com (produits : Facebook Login + Instagram Graph API).
 *  - Permissions : pages_manage_posts, pages_read_engagement, instagram_basic,
 *    instagram_content_publish, business_management.
 *  - Un Page Access Token LONGUE DURÉE + l'ID de la Page + l'ID du compte IG Business.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'AG_SOCIAL_API' ) ) define( 'AG_SOCIAL_API', 'https://graph.facebook.com/v19.0/' );

/* ── Options ─────────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_social_opt' ) ) {
	function ag_social_opt( $k, $d = '' ) { return get_option( 'ag_social_' . $k, $d ); }
}

/* ── Publication FACEBOOK PAGE ───────────────────────────────────────────── */
if ( ! function_exists( 'ag_social_fb_publish' ) ) {
	/** Publie sur la Page FB. Avec image (photos) ou texte+lien (feed). */
	function ag_social_fb_publish( $message, $image_url = '', $link = '' ) {
		$token = trim( (string) ag_social_opt( 'fb_token' ) );
		$page  = trim( (string) ag_social_opt( 'fb_page' ) );
		if ( ! $token || ! $page ) return array( 'ok' => false, 'err' => 'Page FB / jeton manquant.' );
		if ( $image_url ) {
			$ep   = AG_SOCIAL_API . rawurlencode( $page ) . '/photos';
			$body = array( 'url' => $image_url, 'caption' => $message, 'access_token' => $token );
		} else {
			$ep   = AG_SOCIAL_API . rawurlencode( $page ) . '/feed';
			$body = array( 'message' => $message, 'access_token' => $token );
			if ( $link ) $body['link'] = $link;
		}
		$r = wp_remote_post( $ep, array( 'timeout' => 45, 'body' => $body ) );
		if ( is_wp_error( $r ) ) return array( 'ok' => false, 'err' => $r->get_error_message() );
		$j = json_decode( wp_remote_retrieve_body( $r ), true );
		if ( ! empty( $j['id'] ) || ! empty( $j['post_id'] ) ) return array( 'ok' => true, 'id' => $j['id'] ?? $j['post_id'] );
		return array( 'ok' => false, 'err' => $j['error']['message'] ?? 'Erreur FB inconnue.' );
	}
}

/* ── Publication INSTAGRAM (Business) — image obligatoire (URL publique) ──── */
if ( ! function_exists( 'ag_social_ig_publish' ) ) {
	function ag_social_ig_publish( $caption, $image_url ) {
		$token = trim( (string) ag_social_opt( 'fb_token' ) );
		$ig    = trim( (string) ag_social_opt( 'ig_id' ) );
		if ( ! $token || ! $ig ) return array( 'ok' => false, 'err' => 'Compte IG / jeton manquant.' );
		if ( ! $image_url )      return array( 'ok' => false, 'err' => 'Instagram exige une image (URL publique).' );
		// 1) Conteneur média
		$r1 = wp_remote_post( AG_SOCIAL_API . rawurlencode( $ig ) . '/media', array(
			'timeout' => 45, 'body' => array( 'image_url' => $image_url, 'caption' => $caption, 'access_token' => $token ),
		) );
		if ( is_wp_error( $r1 ) ) return array( 'ok' => false, 'err' => $r1->get_error_message() );
		$j1 = json_decode( wp_remote_retrieve_body( $r1 ), true );
		$cid = $j1['id'] ?? '';
		if ( ! $cid ) return array( 'ok' => false, 'err' => $j1['error']['message'] ?? 'Création média IG échouée.' );
		// 2) Publication du conteneur
		$r2 = wp_remote_post( AG_SOCIAL_API . rawurlencode( $ig ) . '/media_publish', array(
			'timeout' => 45, 'body' => array( 'creation_id' => $cid, 'access_token' => $token ),
		) );
		if ( is_wp_error( $r2 ) ) return array( 'ok' => false, 'err' => $r2->get_error_message() );
		$j2 = json_decode( wp_remote_retrieve_body( $r2 ), true );
		if ( ! empty( $j2['id'] ) ) return array( 'ok' => true, 'id' => $j2['id'] );
		return array( 'ok' => false, 'err' => $j2['error']['message'] ?? 'Publication IG échouée.' );
	}
}

/* ── Publication combinée (réseaux activés) + journal ────────────────────── */
if ( ! function_exists( 'ag_social_publish' ) ) {
	function ag_social_publish( $message, $image_url = '', $link = '', $force = array() ) {
		$out = array();
		$do_fb = $force ? in_array( 'fb', $force, true ) : ( '1' === ag_social_opt( 'on_fb', '1' ) );
		$do_ig = $force ? in_array( 'ig', $force, true ) : ( '1' === ag_social_opt( 'on_ig', '1' ) );
		if ( $do_fb ) $out['fb'] = ag_social_fb_publish( $message, $image_url, $link );
		if ( $do_ig ) $out['ig'] = ag_social_ig_publish( $message, $image_url ); // IG ignore le lien
		// Journal (20 derniers)
		$log = get_option( 'ag_social_log', array() );
		array_unshift( $log, array( 'ts' => time(), 'msg' => mb_substr( $message, 0, 120 ), 'res' => $out ) );
		update_option( 'ag_social_log', array_slice( $log, 0, 20 ), false );
		return $out;
	}
}

/* ── Banque de messages promo (rotation quotidienne) ─────────────────────── */
if ( ! function_exists( 'ag_social_promo_bank' ) ) {
	function ag_social_promo_bank() {
		$site = home_url( '/' );
		return apply_filters( 'ag_social_promo_bank', array(
			"🔒 Votre site est-il vraiment sécurisé ? 30 000 sites sont piratés chaque jour. Testez le vôtre GRATUITEMENT en 2 min : " . $site . "tester-mon-site\n\n#cybersécurité #sitweb #PME #sécurité",
			"✨ Un site pro, rapide et sécurisé dès 490 €, payable en 4× sans frais. Vitrine, e-commerce, sur-mesure. Devis gratuit 👉 " . $site . "sites-express\n\n#créationsite #webdesign #entrepreneur",
			"🛡️ Et si un ransomware frappait votre entreprise demain ? On teste votre résilience SANS toucher à vos données. " . $site . "resilience-ransomware\n\n#ransomware #sauvegarde #cybersécurité",
			"📊 Audit SEO + sécurité OFFERT : note /100 + rapport clair de votre site. " . $site . "tester-mon-site\n\n#SEO #référencement #auditweb",
			"🎁 Templates de sites métier GRATUITS (avocat, resto, artisan, coach…). À installer en 2 min : " . $site . "templates-wordpress\n\n#wordpress #templategratuit",
		) );
	}
}

/* ── Cron : 1 post promo / jour (si activé) ──────────────────────────────── */
add_action( 'ag_social_daily', function () {
	if ( '1' !== ag_social_opt( 'daily_on', '0' ) ) return;
	$bank = ag_social_promo_bank();
	$i    = (int) get_option( 'ag_social_rot', 0 ) % max( 1, count( $bank ) );
	update_option( 'ag_social_rot', $i + 1, false );
	$img  = trim( (string) ag_social_opt( 'default_img' ) );
	if ( ! $img ) $img = get_stylesheet_directory_uri() . '/assets/images/securite/hero-secu.jpg';
	ag_social_publish( $bank[ $i ], $img, home_url( '/' ) );
} );
add_action( 'init', function () {
	if ( '1' === ag_social_opt( 'daily_on', '0' ) && ! wp_next_scheduled( 'ag_social_daily' ) ) {
		wp_schedule_event( time() + 300, 'daily', 'ag_social_daily' );
	}
} );

/* ── Auto-post sur nouvel article publié (si activé) ─────────────────────── */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( 'publish' !== $new || 'publish' === $old ) return;
	if ( 'post' !== $post->post_type ) return;
	if ( '1' !== ag_social_opt( 'autopost_article', '0' ) ) return;
	$img = get_the_post_thumbnail_url( $post->ID, 'large' );
	if ( ! $img ) $img = trim( (string) ag_social_opt( 'default_img' ) ) ?: get_stylesheet_directory_uri() . '/assets/images/securite/hero-secu.jpg';
	$msg = '📰 ' . $post->post_title . "\n\n" . wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ) . "\n\n👉 " . get_permalink( $post->ID );
	ag_social_publish( $msg, $img, get_permalink( $post->ID ) );
}, 10, 3 );

/* ── Menu admin ──────────────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_menu_page( 'Marketing', '📣 Marketing', 'manage_options', 'ag-social', 'ag_social_admin_render', 'dashicons-megaphone', 32 );
	add_submenu_page( 'ag-social', 'Réseaux sociaux', '📣 Réseaux sociaux', 'manage_options', 'ag-social', 'ag_social_admin_render' );
} );

/* ── Enregistrement des réglages + composer ──────────────────────────────── */
add_action( 'admin_post_ag_social_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_social' ) ) wp_die( 'no' );
	foreach ( array( 'fb_token', 'fb_page', 'ig_id', 'default_img' ) as $k ) {
		update_option( 'ag_social_' . $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ?? '' ) ), false );
	}
	foreach ( array( 'on_fb', 'on_ig', 'daily_on', 'autopost_article' ) as $k ) {
		update_option( 'ag_social_' . $k, isset( $_POST[ $k ] ) ? '1' : '0', false );
	}
	if ( '1' === ag_social_opt( 'daily_on', '0' ) && ! wp_next_scheduled( 'ag_social_daily' ) ) wp_schedule_event( time() + 300, 'daily', 'ag_social_daily' );
	if ( '1' !== ag_social_opt( 'daily_on', '0' ) ) wp_clear_scheduled_hook( 'ag_social_daily' );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-social&saved=1' ) );
	exit;
} );

add_action( 'admin_post_ag_social_postnow', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_social' ) ) wp_die( 'no' );
	$msg   = (string) wp_unslash( $_POST['msg'] ?? '' );
	$img   = esc_url_raw( wp_unslash( $_POST['img'] ?? '' ) );
	$link  = esc_url_raw( wp_unslash( $_POST['link'] ?? '' ) );
	$force = array();
	if ( isset( $_POST['to_fb'] ) ) $force[] = 'fb';
	if ( isset( $_POST['to_ig'] ) ) $force[] = 'ig';
	$res = $msg ? ag_social_publish( $msg, $img, $link, $force ) : array();
	set_transient( 'ag_social_lastpost', $res, 120 );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-social&posted=1' ) );
	exit;
} );

/* ── Rendu de la page d'admin ────────────────────────────────────────────── */
if ( ! function_exists( 'ag_social_admin_render' ) ) {
	function ag_social_admin_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$last   = get_transient( 'ag_social_lastpost' );
		?>
		<div class="wrap">
			<h1>📣 Réseaux sociaux — publication auto (Facebook + Instagram)</h1>
			<?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Réglages enregistrés.</p></div><?php endif; ?>
			<?php if ( isset( $_GET['posted'] ) && is_array( $last ) ) : ?>
				<div class="notice notice-info is-dismissible"><p><?php
					foreach ( $last as $net => $r ) echo strtoupper( $net ) . ' : ' . ( ! empty( $r['ok'] ) ? '✅ publié (' . esc_html( $r['id'] ?? '' ) . ')' : '❌ ' . esc_html( $r['err'] ?? '' ) ) . ' &nbsp; ';
				?></p></div>
			<?php endif; ?>

			<h2>1. Connexion Meta</h2>
			<form method="post" action="<?php echo $action; ?>" style="background:#fff;border:1px solid #ddd;padding:16px;max-width:780px;border-radius:8px">
				<input type="hidden" name="action" value="ag_social_save"><?php wp_nonce_field( 'ag_social' ); ?>
				<table class="form-table">
					<tr><th>Page Access Token (longue durée)</th><td><input type="text" name="fb_token" value="<?php echo esc_attr( ag_social_opt( 'fb_token' ) ); ?>" class="large-text" placeholder="EAAB..."></td></tr>
					<tr><th>ID de la Page Facebook</th><td><input type="text" name="fb_page" value="<?php echo esc_attr( ag_social_opt( 'fb_page' ) ); ?>" class="regular-text"></td></tr>
					<tr><th>ID du compte Instagram Business</th><td><input type="text" name="ig_id" value="<?php echo esc_attr( ag_social_opt( 'ig_id' ) ); ?>" class="regular-text"></td></tr>
					<tr><th>Image par défaut (URL publique)</th><td><input type="url" name="default_img" value="<?php echo esc_attr( ag_social_opt( 'default_img' ) ); ?>" class="large-text" placeholder="https://…/visuel.jpg"><p class="description">Utilisée pour le post promo quotidien et Instagram (qui exige toujours une image).</p></td></tr>
				</table>
				<h2>2. Automatisations</h2>
				<p><label><input type="checkbox" name="on_fb" <?php checked( ag_social_opt( 'on_fb', '1' ), '1' ); ?>> Publier sur Facebook</label> &nbsp;
				   <label><input type="checkbox" name="on_ig" <?php checked( ag_social_opt( 'on_ig', '1' ), '1' ); ?>> Publier sur Instagram</label></p>
				<p><label><input type="checkbox" name="daily_on" <?php checked( ag_social_opt( 'daily_on', '0' ), '1' ); ?>> <strong>1 post promo automatique par jour</strong> (rotation : sécurité, création, audit gratuit, templates…)</label></p>
				<p><label><input type="checkbox" name="autopost_article" <?php checked( ag_social_opt( 'autopost_article', '0' ), '1' ); ?>> Publier automatiquement chaque <strong>nouvel article</strong> (titre + extrait + image à la une)</label></p>
				<p><button class="button button-primary">💾 Enregistrer</button></p>
			</form>

			<h2 style="margin-top:28px">3. Composer un post (publier maintenant)</h2>
			<form method="post" action="<?php echo $action; ?>" style="background:#fff;border:1px solid #ddd;padding:16px;max-width:780px;border-radius:8px">
				<input type="hidden" name="action" value="ag_social_postnow"><?php wp_nonce_field( 'ag_social' ); ?>
				<p><textarea name="msg" rows="5" class="large-text" placeholder="Votre message… (légende)"></textarea></p>
				<p><input type="url" name="img" class="large-text" placeholder="Image (URL publique) — obligatoire pour Instagram"></p>
				<p><input type="url" name="link" class="large-text" placeholder="Lien (Facebook uniquement, optionnel)"></p>
				<p><label><input type="checkbox" name="to_fb" checked> Facebook</label> &nbsp; <label><input type="checkbox" name="to_ig" checked> Instagram</label></p>
				<p><button class="button button-primary">🚀 Publier maintenant</button></p>
			</form>

			<h2 style="margin-top:28px">Guide express (à faire 1 fois)</h2>
			<ol style="max-width:780px;line-height:1.7">
				<li>Mets ton <strong>Instagram en compte Pro</strong> (Réglages Insta → Compte → Passer en compte pro) et <strong>lie-le à ta Page Facebook</strong> (Meta Business Suite).</li>
				<li>Crée une app sur <a href="https://developers.facebook.com" target="_blank" rel="noopener">developers.facebook.com</a> → ajoute « Instagram Graph API ».</li>
				<li>Génère un <strong>Page Access Token longue durée</strong> + récupère l'<strong>ID de Page</strong> et l'<strong>ID du compte IG Business</strong> (via l'Explorateur d'API Graph).</li>
				<li>Colle-les ci-dessus → coche les automatisations → teste avec « Publier maintenant ».</li>
			</ol>
			<p style="color:#666;max-width:780px">⚠️ Tant que les jetons ne sont pas valides, rien n'est publié (et le bouton renvoie l'erreur Meta exacte pour corriger). Les jetons « longue durée » expirent ~60 j → à renouveler.</p>

			<?php $log = get_option( 'ag_social_log', array() ); if ( $log ) : ?>
				<h2 style="margin-top:28px">Journal (20 derniers)</h2>
				<table class="widefat striped" style="max-width:900px"><thead><tr><th>Date</th><th>Message</th><th>Résultat</th></tr></thead><tbody>
				<?php foreach ( $log as $l ) : ?>
					<tr><td><?php echo esc_html( wp_date( 'd/m H:i', (int) $l['ts'] ) ); ?></td>
						<td><?php echo esc_html( $l['msg'] ); ?></td>
						<td><?php foreach ( (array) ( $l['res'] ?? array() ) as $net => $r ) echo strtoupper( $net ) . ':' . ( ! empty( $r['ok'] ) ? '✅' : '❌' ) . ' '; ?></td></tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>
		</div>
		<?php
	}
}
