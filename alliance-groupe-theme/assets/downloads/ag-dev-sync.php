<?php
/**
 * AG Dev Sync — One-click GitHub sync for testing.
 * Drop this file in wp-content/mu-plugins/ag-dev-sync.php
 * DELETE THIS FILE when the template is final.
 *
 * @package AG_Dev_Sync
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
	add_menu_page(
		'AG Sync',
		'AG Sync',
		'manage_options',
		'ag-dev-sync',
		'ag_dev_sync_page',
		'dashicons-update',
		2
	);
} );

add_action( 'admin_init', function () {
	if ( ! isset( $_POST['ag_dev_sync_run'] ) || ! current_user_can( 'manage_options' ) ) return;
	check_admin_referer( 'ag_dev_sync' );

	$results = array();
	$base    = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/';

	$items = array(
		array(
			'name' => 'AG Starter Avocat (theme)',
			'url'  => $base . 'ag-starter-avocat.zip',
			'dest' => get_theme_root() . '/ag-starter-avocat',
		),
		array(
			'name' => 'AG Starter Companion (plugin)',
			'url'  => $base . 'ag-starter-companion.zip',
			'dest' => WP_PLUGIN_DIR . '/ag-starter-companion',
		),
	);

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

	foreach ( $items as $item ) {
		$tmp = download_url( $item['url'], 30 );
		if ( is_wp_error( $tmp ) ) {
			$results[] = $item['name'] . ' : ERREUR download — ' . $tmp->get_error_message();
			continue;
		}

		$extract_dir = sys_get_temp_dir() . '/ag-sync-' . uniqid();
		$unzip = unzip_file( $tmp, $extract_dir );
		unlink( $tmp );

		if ( is_wp_error( $unzip ) ) {
			$results[] = $item['name'] . ' : ERREUR unzip — ' . $unzip->get_error_message();
			continue;
		}

		$folders = glob( $extract_dir . '/*', GLOB_ONLYDIR );
		$source  = ! empty( $folders ) ? $folders[0] : $extract_dir;

		if ( is_dir( $item['dest'] ) ) {
			$wp_filesystem->delete( $item['dest'], true );
		}

		$copy = copy_dir( $source, $item['dest'] );
		$wp_filesystem->delete( $extract_dir, true );

		if ( is_wp_error( $copy ) ) {
			$results[] = $item['name'] . ' : ERREUR copy — ' . $copy->get_error_message();
		} else {
			$results[] = $item['name'] . ' : OK';
		}
	}

	set_transient( 'ag_dev_sync_results', $results, 60 );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-dev-sync&synced=1' ) );
	exit;
} );

function ag_dev_sync_page() {
	$results = get_transient( 'ag_dev_sync_results' );
	if ( $results ) delete_transient( 'ag_dev_sync_results' );
	?>
	<div class="wrap" style="max-width:600px;">
		<h1 style="font-size:1.8rem;">AG Dev Sync</h1>
		<p style="color:#666;">Synchronise le theme + companion depuis GitHub en un clic.<br>
		<strong style="color:#d63638;">Supprimer ce fichier quand le template est final.</strong></p>

		<?php if ( $results ) : ?>
			<div style="background:#fff;border-left:4px solid #00a32a;padding:12px 16px;margin:16px 0;">
				<?php foreach ( $results as $r ) : ?>
					<p style="margin:4px 0;"><code><?php echo esc_html( $r ); ?></code></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:24px;">
			<?php wp_nonce_field( 'ag_dev_sync' ); ?>
			<button type="submit" name="ag_dev_sync_run" value="1" class="button button-hero button-primary" style="font-size:1.2rem;padding:12px 40px;background:#D4B45C;border-color:#c5a44e;color:#080808;">
				Synchroniser depuis GitHub
			</button>
		</form>

		<p style="margin-top:16px;color:#888;font-size:.85rem;">
			Source : <code>khalidawi44/Alliance-groupe</code> branche <code>main</code><br>
			Theme → <code>wp-content/themes/ag-starter-avocat/</code><br>
			Plugin → <code>wp-content/plugins/ag-starter-companion/</code>
		</p>
	</div>
	<?php
}
