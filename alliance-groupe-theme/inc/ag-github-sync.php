<?php
/**
 * AG GitHub Sync — Page admin "Sync depuis GitHub" pour le thème vitrine.
 *
 * Permet de mettre à jour le thème alliance-groupe-theme directement
 * depuis l'admin WordPress en téléchargeant le tarball GitHub du repo
 * khalidawi44/Alliance-groupe (branche main), sans passer par FTP.
 *
 * Workflow :
 *   1. Admin > Apparence > 🚀 Sync GitHub
 *   2. Affiche le SHA distant + le SHA local stocké en option
 *   3. Bouton "SYNC MAINTENANT" → télécharge tarball, extrait, remplace
 *   4. Log des fichiers modifiés + backup auto dans /uploads/ag-backups/
 *
 * Sécurité :
 *   - manage_options uniquement
 *   - nonce sur chaque action
 *   - whitelist des extensions à écraser (.php, .css, .js, .json, .md)
 *   - blacklist des fichiers à NE JAMAIS écraser (wp-config.php, etc.)
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_GitHub_Sync {

	const REPO            = 'khalidawi44/Alliance-groupe';
	const BRANCH          = 'main';
	const SUBDIR          = 'alliance-groupe-theme'; // sous-dossier du repo à syncer
	const OPT_LAST_SHA    = 'ag_github_sync_last_sha';
	const OPT_LAST_TIME   = 'ag_github_sync_last_time';
	const OPT_LAST_LOG    = 'ag_github_sync_last_log';
	const TRANSIENT_SHA   = 'ag_github_sync_remote_sha';

	/** Extensions autorisées à être écrasées par la sync. */
	const ALLOWED_EXT = array( 'php', 'css', 'js', 'json', 'md', 'mp4', 'webm', 'png', 'jpg', 'jpeg', 'svg', 'webp', 'gif', 'ico', 'woff', 'woff2', 'ttf', 'otf', 'txt' );

	/** Fichiers/dossiers à NE JAMAIS écraser même s'ils sont dans le repo. */
	const PROTECTED = array(
		'wp-config.php', '.env', '.htaccess',
	);

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 30 );
		add_action( 'admin_post_ag_github_sync_run', array( __CLASS__, 'handle_run' ) );
		add_action( 'admin_post_ag_github_sync_check', array( __CLASS__, 'handle_check' ) );
	}

	public static function register_menu() {
		add_theme_page(
			'Sync GitHub',
			'🚀 Sync GitHub',
			'manage_options',
			'ag-github-sync',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Récupère le SHA du dernier commit sur la branche distante.
	 * Cache 5 min en transient.
	 */
	public static function get_remote_sha( $force = false ) {
		if ( ! $force ) {
			$cached = get_transient( self::TRANSIENT_SHA );
			if ( $cached ) return $cached;
		}
		$url  = 'https://api.github.com/repos/' . self::REPO . '/commits/' . self::BRANCH;
		$resp = wp_remote_get( $url, array(
			'timeout' => 12,
			'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'WordPress AG-Sync' ),
		) );
		if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) !== 200 ) {
			return false;
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		$sha  = isset( $body['sha'] ) ? substr( $body['sha'], 0, 12 ) : false;
		if ( $sha ) set_transient( self::TRANSIENT_SHA, $sha, 5 * MINUTE_IN_SECONDS );
		return $sha;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$remote_sha = self::get_remote_sha();
		$local_sha  = get_option( self::OPT_LAST_SHA, '' );
		$last_time  = get_option( self::OPT_LAST_TIME, 0 );
		$last_log   = get_option( self::OPT_LAST_LOG, array() );
		$done       = isset( $_GET['done'] ) ? (int) $_GET['done'] : 0;
		$err        = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';
		$up_to_date = $remote_sha && $local_sha && $remote_sha === $local_sha;
		?>
		<div class="wrap">
			<h1>🚀 Sync depuis GitHub</h1>
			<p>Synchronise le thème <code>alliance-groupe-theme</code> directement depuis le repo GitHub <code><?php echo esc_html( self::REPO ); ?></code> (branche <code><?php echo esc_html( self::BRANCH ); ?></code>) — sans FTP.</p>

			<?php if ( $done ) : ?>
				<div class="notice notice-success"><p><strong>✅ Sync terminée.</strong> <?php echo (int) $done; ?> fichier(s) mis à jour. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">Voir le site →</a></p></div>
			<?php endif; ?>
			<?php if ( $err ) : ?>
				<div class="notice notice-error"><p><strong>❌ Erreur :</strong> <?php echo esc_html( $err ); ?></p></div>
			<?php endif; ?>

			<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px;margin-top:24px;">
				<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #2271b1;padding:16px 20px;border-radius:4px;">
					<h3 style="margin-top:0;">📍 État local</h3>
					<p><strong>Dernier commit syncé :</strong> <code><?php echo $local_sha ? esc_html( $local_sha ) : '— jamais syncé —'; ?></code></p>
					<p><strong>Date :</strong> <?php echo $last_time ? esc_html( wp_date( 'd/m/Y H:i:s', $last_time ) ) : '—'; ?></p>
				</div>
				<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #00a32a;padding:16px 20px;border-radius:4px;">
					<h3 style="margin-top:0;">🌐 État distant</h3>
					<p><strong>Dernier commit GitHub :</strong> <code><?php echo $remote_sha ? esc_html( $remote_sha ) : '<span style="color:red;">API GitHub injoignable</span>'; ?></code></p>
					<p><strong>Status :</strong>
						<?php if ( $up_to_date ) : ?>
							<span style="color:#00a32a;font-weight:700;">✓ À jour</span>
						<?php elseif ( $remote_sha ) : ?>
							<span style="color:#d63638;font-weight:700;">⚠️ Mise à jour disponible</span>
						<?php else : ?>
							<span style="color:#999;">—</span>
						<?php endif; ?>
					</p>
				</div>
			</div>

			<div style="background:linear-gradient(135deg,#0a0a0f,#1a1a2e);color:#fff;padding:28px 32px;border-radius:12px;margin:24px 0;max-width:900px;">
				<h2 style="color:#fff;margin:0 0 10px;font-size:1.3em;">🚀 Synchroniser maintenant</h2>
				<p style="color:rgba(255,255,255,.8);margin:0 0 20px;line-height:1.55;">Télécharge le tarball GitHub, extrait le sous-dossier <code style="background:rgba(255,255,255,.1);padding:2px 6px;border-radius:3px;color:#D4B45C;"><?php echo esc_html( self::SUBDIR ); ?>/</code> et remplace les fichiers du thème. Backup automatique avant écrasement (dans <code style="background:rgba(255,255,255,.1);padding:2px 6px;border-radius:3px;color:#D4B45C;">/wp-content/uploads/ag-backups/</code>).</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Lancer la sync GitHub ? Les fichiers du thème seront remplacés (backup auto).');" style="display:flex;gap:12px;flex-wrap:wrap;">
					<input type="hidden" name="action" value="ag_github_sync_run">
					<?php wp_nonce_field( 'ag_github_sync_run' ); ?>
					<button type="submit" class="button button-primary button-hero" style="background:#F37A1F;border-color:#F37A1F;color:#fff;font-weight:700;padding:0 32px;">🚀 SYNC MAINTENANT</button>
					<button type="submit" form="ag-check-form" class="button button-secondary button-hero" style="padding:0 24px;">🔄 Vérifier mises à jour</button>
				</form>
				<form id="ag-check-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ag_github_sync_check">
					<?php wp_nonce_field( 'ag_github_sync_check' ); ?>
				</form>
			</div>

			<?php if ( ! empty( $last_log ) && is_array( $last_log ) ) : ?>
				<h2>📜 Dernier log de sync</h2>
				<div style="background:#1d2327;color:#0f0;font-family:monospace;font-size:12px;padding:16px;border-radius:4px;max-width:900px;max-height:400px;overflow:auto;line-height:1.5;">
					<?php foreach ( $last_log as $line ) : ?>
						<div><?php echo esc_html( $line ); ?></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function handle_check() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_github_sync_check' );
		delete_transient( self::TRANSIENT_SHA );
		self::get_remote_sha( true );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync' ) );
		exit;
	}

	public static function handle_run() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_github_sync_run' );

		$log = array();
		$log[] = '[' . wp_date( 'H:i:s' ) . '] Démarrage sync depuis ' . self::REPO . '@' . self::BRANCH;

		// 1. Récupère SHA distant
		$remote_sha = self::get_remote_sha( true );
		if ( ! $remote_sha ) {
			$log[] = 'ERREUR : impossible de joindre l\'API GitHub';
			update_option( self::OPT_LAST_LOG, $log );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&err=' . rawurlencode( 'API GitHub injoignable' ) ) );
			exit;
		}
		$log[] = 'SHA distant : ' . $remote_sha;

		// 2. Télécharge le tarball
		$tarball_url = 'https://api.github.com/repos/' . self::REPO . '/tarball/' . self::BRANCH;
		$tmp_file    = download_url( $tarball_url, 60 );
		if ( is_wp_error( $tmp_file ) ) {
			$log[] = 'ERREUR : téléchargement tarball - ' . $tmp_file->get_error_message();
			update_option( self::OPT_LAST_LOG, $log );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&err=' . rawurlencode( 'Téléchargement échoué' ) ) );
			exit;
		}
		$log[] = 'Tarball téléchargé (' . size_format( filesize( $tmp_file ) ) . ')';

		// 3. Extraction
		$upload = wp_upload_dir();
		$work   = trailingslashit( $upload['basedir'] ) . 'ag-sync-' . time();
		if ( ! wp_mkdir_p( $work ) ) {
			$log[] = 'ERREUR : impossible de créer dossier travail';
			@unlink( $tmp_file );
			update_option( self::OPT_LAST_LOG, $log );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&err=' . rawurlencode( 'Dossier work non créable' ) ) );
			exit;
		}

		// Extract tar.gz via PharData
		$ok = false;
		try {
			$phar = new PharData( $tmp_file );
			$phar->extractTo( $work, null, true );
			$ok = true;
		} catch ( Exception $e ) {
			$log[] = 'ERREUR PharData : ' . $e->getMessage();
		}
		@unlink( $tmp_file );
		if ( ! $ok ) {
			self::rm_recursive( $work );
			update_option( self::OPT_LAST_LOG, $log );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&err=' . rawurlencode( 'Extraction échouée' ) ) );
			exit;
		}

		// Trouve le dossier extrait (commence par REPO-SHA/)
		$dirs = glob( $work . '/*', GLOB_ONLYDIR );
		if ( empty( $dirs ) ) {
			$log[] = 'ERREUR : aucun dossier extrait';
			self::rm_recursive( $work );
			update_option( self::OPT_LAST_LOG, $log );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&err=' . rawurlencode( 'Structure tarball inattendue' ) ) );
			exit;
		}
		$source_root = $dirs[0] . '/' . self::SUBDIR;
		if ( ! is_dir( $source_root ) ) {
			$log[] = 'ERREUR : sous-dossier ' . self::SUBDIR . ' introuvable';
			self::rm_recursive( $work );
			update_option( self::OPT_LAST_LOG, $log );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&err=' . rawurlencode( 'Sous-dossier theme introuvable' ) ) );
			exit;
		}
		$log[] = 'Source extraite : ' . self::SUBDIR;

		// 4. Backup avant overwrite
		$backup_dir = trailingslashit( $upload['basedir'] ) . 'ag-backups/' . wp_date( 'Y-m-d_His' );
		wp_mkdir_p( $backup_dir );

		// 5. Sync récursif
		$theme_dir = get_stylesheet_directory();
		$stats = array( 'updated' => 0, 'created' => 0, 'skipped' => 0 );
		self::sync_recursive( $source_root, $theme_dir, $backup_dir, '', $stats, $log );

		$log[] = sprintf( '%d fichiers mis à jour, %d créés, %d ignorés', $stats['updated'], $stats['created'], $stats['skipped'] );
		$log[] = 'Backup dans : ' . str_replace( ABSPATH, '', $backup_dir );

		// 6. Cleanup
		self::rm_recursive( $work );

		// 7. Save state
		update_option( self::OPT_LAST_SHA, $remote_sha );
		update_option( self::OPT_LAST_TIME, time() );
		update_option( self::OPT_LAST_LOG, $log );

		$total = $stats['updated'] + $stats['created'];
		wp_safe_redirect( admin_url( 'themes.php?page=ag-github-sync&done=' . $total ) );
		exit;
	}

	/**
	 * Sync récursif source → dest avec backup auto.
	 */
	private static function sync_recursive( $src, $dst, $backup, $rel, &$stats, &$log ) {
		if ( ! is_dir( $src ) ) return;
		$items = scandir( $src );
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) continue;
			$src_path = $src . '/' . $item;
			$dst_path = $dst . '/' . $item;
			$rel_path = $rel === '' ? $item : $rel . '/' . $item;

			// Protection : skip fichiers/dossiers sensibles
			if ( in_array( $item, self::PROTECTED, true ) ) {
				$stats['skipped']++;
				continue;
			}

			if ( is_dir( $src_path ) ) {
				if ( ! is_dir( $dst_path ) ) {
					wp_mkdir_p( $dst_path );
				}
				self::sync_recursive( $src_path, $dst_path, $backup . '/' . $item, $rel_path, $stats, $log );
			} else {
				$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, self::ALLOWED_EXT, true ) ) {
					$stats['skipped']++;
					continue;
				}
				$existed = file_exists( $dst_path );
				// Skip si contenu identique
				if ( $existed && md5_file( $src_path ) === md5_file( $dst_path ) ) {
					continue;
				}
				// Backup si existe
				if ( $existed ) {
					wp_mkdir_p( dirname( $backup . '/' . $rel_path ) );
					@copy( $dst_path, $backup . '/' . $rel_path );
				}
				// Copy
				if ( copy( $src_path, $dst_path ) ) {
					if ( $existed ) {
						$stats['updated']++;
						$log[] = 'UPD ' . $rel_path;
					} else {
						$stats['created']++;
						$log[] = 'NEW ' . $rel_path;
					}
				}
			}
		}
	}

	private static function rm_recursive( $dir ) {
		if ( ! is_dir( $dir ) ) return;
		$items = scandir( $dir );
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) continue;
			$p = $dir . '/' . $item;
			if ( is_dir( $p ) ) self::rm_recursive( $p );
			else @unlink( $p );
		}
		@rmdir( $dir );
	}
}

AG_GitHub_Sync::init();
