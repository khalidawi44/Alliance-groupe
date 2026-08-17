<?php
/**
 * AG GitHub Sync — Moteur de synchronisation fichiers depuis GitHub.
 *
 * Pas de page admin propre : le moteur est consommé depuis
 * `Outils > Import AG` (ag-import.php) qui agrège l'UI de toutes les
 * sources de sync (contenu + fichiers thème + futurs repos).
 *
 * Pour ajouter un nouveau repo à synchroniser, hook le filter
 * `ag_github_sync_repos` :
 *
 *   add_filter( 'ag_github_sync_repos', function ( $repos ) {
 *       $repos['mon-repo'] = array(
 *           'label'      => 'Mon Repo',
 *           'repo'       => 'user/repo',
 *           'branch'     => 'main',
 *           'subdir'     => 'theme',  // sous-dossier dans le repo
 *           'target_dir' => get_stylesheet_directory(), // où écrire localement
 *       );
 *       return $repos;
 *   } );
 *
 * Sécurité :
 *   - manage_options uniquement
 *   - nonce sur chaque action
 *   - whitelist extensions (.php, .css, .js, .json, .md, images, fonts, vidéos)
 *   - blacklist : wp-config.php, .env, .htaccess JAMAIS écrasés
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_GitHub_Sync {

	const OPT_PREFIX    = 'ag_github_sync_'; // préfixe options (sha, time, log) par slug
	const TRANSIENT_TTL = 5 * MINUTE_IN_SECONDS;

	/** Extensions autorisées à être écrasées par la sync. */
	const ALLOWED_EXT = array( 'php', 'css', 'js', 'json', 'md', 'html', 'htm', 'mp4', 'webm', 'png', 'jpg', 'jpeg', 'svg', 'webp', 'gif', 'ico', 'woff', 'woff2', 'ttf', 'otf', 'txt', 'glb', 'gltf', 'bin', 'hdr', 'ktx2', 'mp3', 'wav', 'm4a', 'ogg' );

	/** Fichiers/dossiers à NE JAMAIS écraser même s'ils sont dans le repo. */
	const PROTECTED = array( 'wp-config.php', '.env', '.htaccess', '.user.ini', 'php.ini', '.htpasswd', 'web.config', '.git' );

	/**
	 * SECURITY: dépôts de confiance autorisés pour la sync (RCE = on écrit du PHP
	 * exécuté ensuite). On ne synchronise JAMAIS depuis une autre source, même si
	 * le filtre `ag_github_sync_repos` en injecte une. Étendre cette liste est une
	 * décision de code délibérée (pas data-driven).
	 */
	const TRUSTED_REPOS = array( 'khalidawi44/Alliance-groupe' );

	/** Le repo est-il dans la liste de confiance ? */
	public static function is_trusted_repo( $repo ) {
		return in_array( (string) $repo, self::TRUSTED_REPOS, true );
	}

	const CRON_HOOK     = 'ag_github_sync_cron';
	const CRON_INTERVAL = 'ag_every_five_minutes';
	const CRON_LOG_OPT  = 'ag_github_sync_cron_log';

	public static function init() {
		// Pas de page admin propre — l'UI est dans ag-import.php
		// Garde admin-post pour le standalone si besoin futur
		add_action( 'admin_post_ag_github_sync_run', array( __CLASS__, 'handle_run' ) );

		// Auto-rattrapage : garde le plugin ag-licence-manager (wp-content/plugins)
		// aligné sur la copie livrée dans le thème (sinon la sync thème ne met
		// jamais à jour le plugin actif). Throttlé, vide l'OPcache si MAJ.
		add_action( 'admin_init', array( __CLASS__, 'maybe_sync_plugin' ) );

		// ── Cron auto-sync : toutes les 5 min, en arriere-plan, zero clic ──
		// Declenche par n'importe quelle visite du site (WP cron classique).
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'cron_run' ) );
		// Auto-schedule si pas deja en place
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, self::CRON_INTERVAL, self::CRON_HOOK );
		}
	}

	/** Ajoute l'intervalle 5 min aux schedules WP-Cron disponibles. */
	public static function add_cron_interval( $schedules ) {
		if ( ! isset( $schedules[ self::CRON_INTERVAL ] ) ) {
			$schedules[ self::CRON_INTERVAL ] = array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display'  => 'AG : Toutes les 5 minutes',
			);
		}
		return $schedules;
	}

	/**
	 * Handler du cron : pour chaque repo configure, compare SHA distant vs local
	 * et lance la sync si different. Tourne en arriere-plan, sans UI.
	 */
	public static function cron_run() {
		if ( ! class_exists( 'AG_GitHub_Sync' ) ) return;
		$log = array();
		$log[] = '[' . wp_date( 'Y-m-d H:i:s' ) . '] Cron auto-sync demarre';
		$any_synced = false;
		foreach ( array_keys( self::get_repos() ) as $slug ) {
			$remote = self::get_remote_sha( $slug, true );
			$local  = self::get_local_sha( $slug );
			if ( ! $remote ) {
				$log[] = $slug . ' : API GitHub injoignable, skip';
				continue;
			}
			if ( $remote === $local ) {
				$log[] = $slug . ' : deja a jour (' . $remote . ')';
				continue;
			}
			$log[] = $slug . ' : MAJ detectee (' . substr( $local, 0, 7 ) . ' -> ' . substr( $remote, 0, 7 ) . ') — sync...';
			$result = self::sync( $slug );
			if ( $result['ok'] ) {
				$n = (int) ( $result['stats']['updated'] ?? 0 ) + (int) ( $result['stats']['created'] ?? 0 );
				$log[] = $slug . ' : OK, ' . $n . ' fichiers mis a jour';
				$any_synced = true;
			} else {
				$log[] = $slug . ' : ERREUR — ' . $result['error'];
			}
		}
		if ( ! $any_synced ) {
			$log[] = 'Rien a sync (theme).';
		}

		// ── Import auto des NOUVEAUX articles/pages depuis le manifest ──
		$content = self::import_new_content();
		if ( $content['articles'] > 0 || $content['pages'] > 0 ) {
			$log[] = 'Contenu : ' . $content['articles'] . ' articles + ' . $content['pages'] . ' pages importes';
		}
		if ( ! empty( $content['error'] ) ) {
			$log[] = 'Contenu : ' . $content['error'];
		}

		// Garde les 50 dernieres entrees (taille raisonnable)
		$prev = get_option( self::CRON_LOG_OPT, array() );
		if ( ! is_array( $prev ) ) $prev = array();
		$merged = array_merge( $log, $prev );
		if ( count( $merged ) > 50 ) $merged = array_slice( $merged, 0, 50 );
		update_option( self::CRON_LOG_OPT, $merged );
	}

	/**
	 * Importe les NOUVEAUX articles + pages depuis le manifest GitHub.
	 * Idempotent : un article/page deja present (meme slug) est ignore.
	 * Leger quand rien de neuf (1 requete manifest + checks DB par slug).
	 *
	 * @return array{articles:int, pages:int, error:string}
	 */
	public static function import_new_content() {
		$out = array( 'articles' => 0, 'pages' => 0, 'error' => '' );

		// Le helper ag_gh_json vient de ag-import.php (charge globalement)
		if ( ! function_exists( 'ag_gh_json' ) ) {
			$out['error'] = 'ag_gh_json indisponible';
			return $out;
		}

		$cfg = self::get_repos();
		$repo   = isset( $cfg['theme']['repo'] ) ? $cfg['theme']['repo'] : 'khalidawi44/Alliance-groupe';
		$branch = isset( $cfg['theme']['branch'] ) ? $cfg['theme']['branch'] : 'main';
		$content_base = 'https://raw.githubusercontent.com/' . $repo . '/' . $branch . '/content';

		$manifest = ag_gh_json( $content_base . '/manifest.json' );
		if ( ! $manifest ) {
			$out['error'] = 'manifest injoignable';
			return $out;
		}

		// Map categories par nom -> id (creees au besoin)
		$cat_ids = array();
		if ( ! empty( $manifest['categories'] ) ) {
			foreach ( $manifest['categories'] as $c ) {
				$term = term_exists( $c['name'], 'category' );
				if ( ! $term ) $term = wp_insert_term( $c['name'], 'category' );
				if ( ! is_wp_error( $term ) ) $cat_ids[ $c['name'] ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			}
		}

		// ── Pages : importe seulement celles dont le slug n'existe pas ──
		if ( ! empty( $manifest['pages'] ) ) {
			foreach ( $manifest['pages'] as $path ) {
				$p = ag_gh_json( $content_base . '/' . $path );
				if ( ! $p || empty( $p['slug'] ) ) continue;
				if ( get_page_by_path( $p['slug'] ) ) continue; // existe deja
				$pid = wp_insert_post( array(
					'post_title'    => $p['title'],
					'post_name'     => $p['slug'],
					'post_status'   => 'publish',
					'post_type'     => 'page',
					'post_content'  => '',
					'page_template' => isset( $p['template'] ) ? $p['template'] : '',
				) );
				if ( $pid && ! is_wp_error( $pid ) ) $out['pages']++;
			}
		}

		// ── Articles : importe seulement les nouveaux slugs ──
		if ( ! empty( $manifest['articles'] ) ) {
			foreach ( $manifest['articles'] as $path ) {
				$a = ag_gh_json( $content_base . '/' . $path );
				if ( ! $a || empty( $a['slug'] ) ) continue;
				if ( get_page_by_path( $a['slug'], OBJECT, 'post' ) ) continue; // existe deja
				$cat_name = isset( $a['category'] ) ? $a['category'] : 'Conseils Digital';
				$cid = isset( $cat_ids[ $cat_name ] ) ? $cat_ids[ $cat_name ] : 0;
				$pid = wp_insert_post( array(
					'post_title'   => $a['title'],
					'post_name'    => $a['slug'],
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'post_content' => wp_kses_post( $a['content'] ),
					'post_excerpt' => isset( $a['excerpt'] ) ? $a['excerpt'] : '',
					'post_category' => $cid ? array( $cid ) : array(),
				) );
				if ( $pid && ! is_wp_error( $pid ) ) {
					if ( ! empty( $a['tags'] ) ) wp_set_post_tags( $pid, $a['tags'] );
					$out['articles']++;
				}
			}
		}

		return $out;
	}

	/** Recupere l'historique des derniers passages cron. */
	public static function get_cron_log() {
		$log = get_option( self::CRON_LOG_OPT, array() );
		return is_array( $log ) ? $log : array();
	}

	/** Heure prevue du prochain run cron (timestamp Unix), ou false si pas planifie. */
	public static function get_next_cron_run() {
		return wp_next_scheduled( self::CRON_HOOK );
	}

	/**
	 * Liste des repos à synchroniser (extensible via filter `ag_github_sync_repos`).
	 *
	 * Chaque entrée : array( label, repo, branch, subdir, target_dir )
	 *
	 * @return array<string, array> map slug => config
	 */
	public static function get_repos() {
		$default = array(
			'theme' => array(
				'label'      => 'Fichiers du thème',
				'repo'       => 'khalidawi44/Alliance-groupe',
				'branch'     => 'main',
				'subdir'     => 'alliance-groupe-theme',
				'target_dir' => get_stylesheet_directory(),
			),
		);
		return apply_filters( 'ag_github_sync_repos', $default );
	}

	/**
	 * Récupère le SHA du dernier commit sur la branche distante d'un repo.
	 * Cache 5 min en transient (clé par slug).
	 *
	 * @param string $slug  Slug du repo dans get_repos()
	 * @param bool   $force Force refresh sans cache
	 * @return string|false SHA court (12 chars) ou false si API KO
	 */
	public static function get_remote_sha( $slug, $force = false ) {
		$repos = self::get_repos();
		if ( ! isset( $repos[ $slug ] ) ) return false;
		$cfg = $repos[ $slug ];
		// SECURITY: ne contacte jamais un dépôt hors liste de confiance.
		if ( ! self::is_trusted_repo( $cfg['repo'] ?? '' ) ) return false;

		$cache_key = 'ag_gh_sha_' . $slug;
		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( $cached ) return $cached;
		}
		$url  = 'https://api.github.com/repos/' . $cfg['repo'] . '/commits/' . $cfg['branch'];
		$resp = wp_remote_get( $url, array(
			'timeout' => 12,
			'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'WordPress AG-Sync' ),
		) );
		if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) !== 200 ) {
			return false;
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		$sha  = isset( $body['sha'] ) ? substr( $body['sha'], 0, 12 ) : false;
		if ( $sha ) set_transient( $cache_key, $sha, self::TRANSIENT_TTL );
		return $sha;
	}

	/** Récupère le SHA local stocké (dernière sync réussie). */
	public static function get_local_sha( $slug ) {
		return get_option( self::OPT_PREFIX . $slug . '_sha', '' );
	}

	/** Date Unix du dernier sync. */
	public static function get_last_time( $slug ) {
		return (int) get_option( self::OPT_PREFIX . $slug . '_time', 0 );
	}

	/** Log de la dernière sync. */
	public static function get_last_log( $slug ) {
		$log = get_option( self::OPT_PREFIX . $slug . '_log', array() );
		return is_array( $log ) ? $log : array();
	}

	/**
	 * Lance la sync d'un repo. Méthode unitaire — utilisable depuis n'importe
	 * quel handler (ag-import.php agrège l'UI).
	 *
	 * @param string $slug Slug du repo dans get_repos()
	 * @return array{ok:bool, error:string, log:array, sha:string, stats:array}
	 */
	public static function sync( $slug ) {
		$repos = self::get_repos();
		if ( ! isset( $repos[ $slug ] ) ) {
			return array( 'ok' => false, 'error' => 'Repo inconnu : ' . $slug, 'log' => array(), 'sha' => '', 'stats' => array() );
		}
		$cfg = $repos[ $slug ];
		$log = array();
		$log[] = '[' . wp_date( 'H:i:s' ) . '] Sync ' . $cfg['repo'] . '@' . $cfg['branch'] . ' (' . $slug . ')';

		// 0. SECURITY: refuse toute source hors liste de confiance (anti-RCE).
		if ( ! self::is_trusted_repo( $cfg['repo'] ?? '' ) ) {
			$log[] = 'ERREUR : dépôt non autorisé : ' . ( $cfg['repo'] ?? '?' );
			return array( 'ok' => false, 'error' => 'Dépôt non autorisé', 'log' => $log, 'sha' => '', 'stats' => array() );
		}

		// 1. SHA distant
		$remote_sha = self::get_remote_sha( $slug, true );
		if ( ! $remote_sha ) {
			$log[] = 'ERREUR : API GitHub injoignable';
			return array( 'ok' => false, 'error' => 'API GitHub injoignable', 'log' => $log, 'sha' => '', 'stats' => array() );
		}
		$log[] = 'SHA distant : ' . $remote_sha;

		// 1b. SYNC INCRÉMENTAL : si on connaît déjà un SHA local, ne récupère QUE
		//     les fichiers modifiés depuis (compare API + raw). Évite le tarball
		//     complet, trop lourd pour l'hébergement quand le dépôt grossit.
		$local_sha = self::get_local_sha( $slug );
		if ( '' !== $local_sha && $local_sha === $remote_sha ) {
			$log[] = 'Déjà à jour (SHA identique).';
			update_option( self::OPT_PREFIX . $slug . '_time', time() );
			update_option( self::OPT_PREFIX . $slug . '_log', $log );
			return array( 'ok' => true, 'error' => '', 'log' => $log, 'sha' => $remote_sha, 'stats' => array( 'updated' => 0, 'created' => 0, 'skipped' => 0 ) );
		}
		if ( '' !== $local_sha ) {
			$inc = self::sync_incremental( $slug, $cfg, $local_sha, $remote_sha, $log );
			if ( is_array( $inc ) && empty( $inc['fallback'] ) ) {
				if ( 'theme' === $slug ) {
					$n = self::mirror_licence_plugin( true );
					if ( $n > 0 ) { $log[] = 'Plugin ag-licence-manager : ' . $n . ' fichier(s) mis à jour + OPcache vidé'; }
				}
				update_option( self::OPT_PREFIX . $slug . '_time', time() );
				update_option( self::OPT_PREFIX . $slug . '_log', $log );
				if ( ! empty( $inc['ok'] ) ) {
					update_option( self::OPT_PREFIX . $slug . '_sha', $remote_sha );
					return array( 'ok' => true, 'error' => '', 'log' => $log, 'sha' => $remote_sha, 'stats' => $inc['stats'] );
				}
				// Échecs partiels : on n'avance PAS le SHA → la prochaine SYNC réessaiera.
				return array( 'ok' => false, 'error' => $inc['error'], 'log' => $log, 'sha' => $local_sha, 'stats' => $inc['stats'] );
			}
			$log[] = 'Incrémental indisponible → bascule tarball complet.';
		}

		// 2. Tarball
		$tarball_url = 'https://api.github.com/repos/' . $cfg['repo'] . '/tarball/' . $cfg['branch'];
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$tmp_file = download_url( $tarball_url, 60 );
		if ( is_wp_error( $tmp_file ) ) {
			$log[] = 'ERREUR téléchargement : ' . $tmp_file->get_error_message();
			return array( 'ok' => false, 'error' => 'Téléchargement échoué', 'log' => $log, 'sha' => '', 'stats' => array() );
		}
		$log[] = 'Tarball téléchargé (' . size_format( filesize( $tmp_file ) ) . ')';

		// 3. Extraction
		$upload = wp_upload_dir();
		$work   = trailingslashit( $upload['basedir'] ) . 'ag-sync-' . $slug . '-' . time();
		if ( ! wp_mkdir_p( $work ) ) {
			@unlink( $tmp_file );
			$log[] = 'ERREUR : impossible de créer dossier work';
			return array( 'ok' => false, 'error' => 'Dossier work non créable', 'log' => $log, 'sha' => '', 'stats' => array() );
		}
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
			return array( 'ok' => false, 'error' => 'Extraction échouée', 'log' => $log, 'sha' => '', 'stats' => array() );
		}

		// Source = REPO-SHA/<subdir>
		$dirs = glob( $work . '/*', GLOB_ONLYDIR );
		if ( empty( $dirs ) ) {
			self::rm_recursive( $work );
			return array( 'ok' => false, 'error' => 'Structure tarball inattendue', 'log' => $log, 'sha' => '', 'stats' => array() );
		}
		// SECURITY: le dossier racine du tarball est nommé "owner-repo-<sha>".
		// On vérifie qu'il correspond bien au commit attendu (intégrité de base :
		// le tarball n'est pas un contenu décorrélé du SHA annoncé).
		$root_name = basename( $dirs[0] );
		if ( false === strpos( $root_name, substr( $remote_sha, 0, 7 ) ) ) {
			self::rm_recursive( $work );
			$log[] = 'ERREUR : tarball incohérent avec le SHA ' . $remote_sha . ' (racine : ' . $root_name . ')';
			return array( 'ok' => false, 'error' => 'Intégrité tarball', 'log' => $log, 'sha' => '', 'stats' => array() );
		}
		$source_root = $dirs[0];
		if ( ! empty( $cfg['subdir'] ) ) $source_root .= '/' . $cfg['subdir'];
		if ( ! is_dir( $source_root ) ) {
			self::rm_recursive( $work );
			$log[] = 'ERREUR : sous-dossier introuvable : ' . $cfg['subdir'];
			return array( 'ok' => false, 'error' => 'Sous-dossier introuvable', 'log' => $log, 'sha' => '', 'stats' => array() );
		}
		$log[] = 'Source extraite : ' . ( $cfg['subdir'] ?: '<root>' );

		// 4. Backup
		$backup_dir = trailingslashit( $upload['basedir'] ) . 'ag-backups/' . $slug . '-' . wp_date( 'Y-m-d_His' );
		wp_mkdir_p( $backup_dir );

		// 5. Sync récursif
		$stats = array( 'updated' => 0, 'created' => 0, 'skipped' => 0 );
		self::sync_recursive( $source_root, $cfg['target_dir'], $backup_dir, '', $stats, $log );
		$log[] = sprintf( '%d mis à jour, %d créés, %d ignorés', $stats['updated'], $stats['created'], $stats['skipped'] );
		$log[] = 'Backup : ' . str_replace( ABSPATH, '', $backup_dir );

		// 5b. Redéploie le plugin ag-licence-manager (il tourne depuis
		//     wp-content/plugins, pas depuis le thème) + vide l'OPcache.
		if ( 'theme' === $slug ) {
			$n = self::mirror_licence_plugin( true );
			if ( $n > 0 ) $log[] = 'Plugin ag-licence-manager : ' . $n . ' fichier(s) mis à jour + OPcache vidé';
		}

		// 6. Cleanup
		self::rm_recursive( $work );

		// 7. Persist state
		update_option( self::OPT_PREFIX . $slug . '_sha', $remote_sha );
		update_option( self::OPT_PREFIX . $slug . '_time', time() );
		update_option( self::OPT_PREFIX . $slug . '_log', $log );

		return array( 'ok' => true, 'error' => '', 'log' => $log, 'sha' => $remote_sha, 'stats' => $stats );
	}

	/** Standalone POST handler (admin-post.php?action=ag_github_sync_run&slug=theme) */
	public static function handle_run() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'ag_github_sync_run' );
		$slug   = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : 'theme';
		$result = self::sync( $slug );
		$total  = isset( $result['stats']['updated'] ) ? ( (int) $result['stats']['updated'] + (int) $result['stats']['created'] ) : 0;
		$qs     = $result['ok']
			? 'tools.php?page=ag-import&ag_synced=' . $total
			: 'tools.php?page=ag-import&ag_err=' . rawurlencode( $result['error'] );
		wp_safe_redirect( admin_url( $qs ) );
		exit;
	}

	/**
	 * Aligne le plugin actif wp-content/plugins/ag-licence-manager sur la copie
	 * livrée dans le thème (get_template_directory()/ag-licence-manager).
	 * Ne met à jour QUE si le plugin est déjà installé et qu'un fichier diffère.
	 * Vide l'OPcache si au moins un fichier change.
	 *
	 * @return int nombre de fichiers mis à jour.
	 */
	public static function mirror_licence_plugin( $force = false ) {
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) return 0;
		$src = get_template_directory() . '/ag-licence-manager';
		$dst = WP_PLUGIN_DIR . '/ag-licence-manager';
		if ( ! is_dir( $src ) || ! is_dir( $dst ) ) return 0; // plugin pas installé : on ne crée pas

		$files = array(
			'ag-licence-manager.php',
			'includes/class-ag-licence-db.php',
			'includes/class-ag-licence-api.php',
			'includes/class-ag-licence-admin.php',
			'includes/class-ag-licence-stripe.php',
			'includes/class-ag-licence-email.php',
		);
		$changed = 0;
		foreach ( $files as $f ) {
			$s = $src . '/' . $f;
			$d = $dst . '/' . $f;
			if ( ! file_exists( $s ) ) continue;
			if ( ! file_exists( $d ) || md5_file( $s ) !== md5_file( $d ) ) {
				if ( ! is_dir( dirname( $d ) ) ) wp_mkdir_p( dirname( $d ) );
				if ( @copy( $s, $d ) ) $changed++; // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}
		}
		if ( $changed > 0 && function_exists( 'opcache_reset' ) ) {
			@opcache_reset(); // phpcs:ignore
		}
		return $changed;
	}

	/** admin_init : auto-rattrapage throttlé (1×/5 min) du plugin licence. */
	public static function maybe_sync_plugin() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( get_transient( 'ag_lm_mirror_check' ) ) return;
		set_transient( 'ag_lm_mirror_check', 1, 5 * MINUTE_IN_SECONDS );
		self::mirror_licence_plugin();
	}

	/**
	 * Sync récursif source → dest avec backup auto.
	 */
	/**
	 * Sync INCRÉMENTAL : télécharge uniquement les fichiers modifiés entre deux
	 * commits (API compare de GitHub), un par un via leur raw_url. Sûr et léger.
	 * Retourne array('fallback'=>true) quand l'incrémental n'est pas applicable
	 * (compare KO, >300 fichiers tronqués) → l'appelant bascule sur le tarball.
	 * Sinon array('ok'=>bool, 'error'=>string, 'stats'=>array).
	 */
	private static function sync_incremental( $slug, $cfg, $base, $head, &$log ) {
		$repo   = $cfg['repo'];
		$subdir = isset( $cfg['subdir'] ) ? trim( (string) $cfg['subdir'], '/' ) : '';
		$target = rtrim( (string) $cfg['target_dir'], '/' );
		$prefix = '' !== $subdir ? $subdir . '/' : '';

		$api  = 'https://api.github.com/repos/' . $repo . '/compare/' . rawurlencode( $base ) . '...' . rawurlencode( $head );
		$resp = wp_remote_get( $api, array(
			'timeout' => 20,
			'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'WordPress AG-Sync' ),
		) );
		if ( is_wp_error( $resp ) ) { $log[] = 'Incrémental: compare KO (' . $resp->get_error_message() . ')'; return array( 'fallback' => true ); }
		$code = (int) wp_remote_retrieve_response_code( $resp );
		if ( 200 !== $code ) { $log[] = 'Incrémental: compare HTTP ' . $code . ' → tarball'; return array( 'fallback' => true ); }
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! isset( $body['files'] ) || ! is_array( $body['files'] ) ) { $log[] = 'Incrémental: réponse inattendue → tarball'; return array( 'fallback' => true ); }
		if ( count( $body['files'] ) >= 300 ) { $log[] = 'Incrémental: >300 fichiers (tronqué) → tarball'; return array( 'fallback' => true ); }

		$upload     = wp_upload_dir();
		$backup_dir = trailingslashit( $upload['basedir'] ) . 'ag-backups/' . $slug . '-inc-' . wp_date( 'Y-m-d_His' );
		$stats = array( 'updated' => 0, 'created' => 0, 'skipped' => 0, 'deleted' => 0 );
		$fails = array();

		foreach ( $body['files'] as $f ) {
			$path   = isset( $f['filename'] ) ? (string) $f['filename'] : '';
			$status = isset( $f['status'] ) ? (string) $f['status'] : '';
			if ( '' === $path ) { continue; }
			if ( '' !== $prefix && 0 !== strpos( $path, $prefix ) ) { $stats['skipped']++; continue; } // hors thème
			$rel = '' !== $prefix ? substr( $path, strlen( $prefix ) ) : $path;
			if ( '' === $rel || false !== strpos( $rel, '..' ) ) { $stats['skipped']++; continue; }
			if ( in_array( basename( $rel ), self::PROTECTED, true ) ) { $stats['skipped']++; continue; }
			$dst = $target . '/' . $rel;

			// Suppression d'un fichier.
			if ( 'removed' === $status ) {
				if ( file_exists( $dst ) ) {
					wp_mkdir_p( dirname( $backup_dir . '/' . $rel ) );
					@copy( $dst, $backup_dir . '/' . $rel );
					@unlink( $dst );
					$stats['deleted']++;
				}
				continue;
			}

			$ext = strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, self::ALLOWED_EXT, true ) ) { $stats['skipped']++; continue; }

			// Renommage : retire l'ancien fichier.
			if ( 'renamed' === $status && ! empty( $f['previous_filename'] ) && '' !== $prefix && 0 === strpos( $f['previous_filename'], $prefix ) ) {
				$prev_rel = substr( $f['previous_filename'], strlen( $prefix ) );
				if ( false === strpos( $prev_rel, '..' ) && file_exists( $target . '/' . $prev_rel ) ) { @unlink( $target . '/' . $prev_rel ); }
			}

			// Téléchargement du contenu via raw_url (host GitHub vérifié).
			$raw     = isset( $f['raw_url'] ) ? (string) $f['raw_url'] : '';
			$ok_host = ( 0 === strpos( $raw, 'https://github.com/' . $repo . '/' ) || 0 === strpos( $raw, 'https://raw.githubusercontent.com/' . $repo . '/' ) );
			if ( ! $ok_host ) { $fails[] = $rel . ' (url non fiable)'; continue; }
			$dl = wp_remote_get( $raw, array( 'timeout' => 45, 'headers' => array( 'User-Agent' => 'WordPress AG-Sync' ) ) );
			if ( is_wp_error( $dl ) || 200 !== (int) wp_remote_retrieve_response_code( $dl ) ) { $fails[] = $rel; continue; }
			$data = wp_remote_retrieve_body( $dl );

			$existed = file_exists( $dst );
			if ( $existed && md5( $data ) === md5_file( $dst ) ) { continue; } // identique : rien à faire
			if ( $existed ) { wp_mkdir_p( dirname( $backup_dir . '/' . $rel ) ); @copy( $dst, $backup_dir . '/' . $rel ); }
			wp_mkdir_p( dirname( $dst ) );
			if ( false !== file_put_contents( $dst, $data ) ) {
				if ( $existed ) { $stats['updated']++; } else { $stats['created']++; }
			} else {
				$fails[] = $rel . ' (écriture)';
			}
		}

		$log[] = sprintf( 'Incrémental: %d créé(s), %d mis à jour, %d supprimé(s), %d ignoré(s)', $stats['created'], $stats['updated'], $stats['deleted'], $stats['skipped'] );
		if ( ! empty( $fails ) ) {
			$log[] = 'Incrémental: ' . count( $fails ) . ' échec(s) : ' . implode( ', ', array_slice( $fails, 0, 8 ) );
			return array( 'ok' => false, 'error' => count( $fails ) . ' fichier(s) non récupéré(s) — relance la SYNC', 'stats' => $stats );
		}
		return array( 'ok' => true, 'stats' => $stats );
	}

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
