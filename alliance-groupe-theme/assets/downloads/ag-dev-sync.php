<?php
/**
 * AG Dev Sync — One-click GitHub sync for testing.
 * Drop this file in wp-content/mu-plugins/ag-dev-sync.php
 * DELETE THIS FILE when the template is final.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'AG Sync', 'AG Sync', 'manage_options', 'ag-dev-sync', 'ag_dev_sync_page', 'dashicons-update', 2 );
} );

function ag_dev_sync_download( $url ) {
	$args = array( 'timeout' => 60, 'sslverify' => false );
	$resp = wp_remote_get( $url, $args );
	if ( is_wp_error( $resp ) ) return $resp;
	if ( 200 !== wp_remote_retrieve_response_code( $resp ) ) {
		return new WP_Error( 'http', 'HTTP ' . wp_remote_retrieve_response_code( $resp ) );
	}
	$tmp = wp_tempnam( basename( $url ) );
	file_put_contents( $tmp, wp_remote_retrieve_body( $resp ) );
	return $tmp;
}

function ag_dev_sync_run() {
	$results = array();
	$base = 'https://github.com/khalidawi44/Alliance-groupe/raw/main/alliance-groupe-theme/assets/downloads/';

	$items = array(
		// === Theme AVOCAT + plugins associes ===
		array(
			'name' => 'Theme AG Starter Avocat',
			'url'  => $base . 'ag-starter-avocat.zip',
			'dest' => get_theme_root() . '/ag-starter-avocat',
		),
		array(
			'name' => 'Plugin AG Starter Companion',
			'url'  => $base . 'ag-starter-companion.zip',
			'dest' => WP_PLUGIN_DIR . '/ag-starter-companion',
		),
		array(
			'name' => 'Plugin AG Premium Avocat',
			'url'  => $base . 'ag-premium-avocat.zip',
			'dest' => WP_PLUGIN_DIR . '/ag-premium-avocat',
		),
		array(
			'name' => 'Plugin AG Business Avocat',
			'url'  => $base . 'ag-business-avocat.zip',
			'dest' => WP_PLUGIN_DIR . '/ag-business-avocat',
		),
		// === Theme BARBER + plugin Business associe ===
		array(
			'name' => 'Theme AG Starter Barber',
			'url'  => $base . 'ag-starter-barber.zip',
			'dest' => get_theme_root() . '/ag-starter-barber',
		),
		array(
			'name' => 'Plugin AG Business Barber',
			'url'  => $base . 'ag-business-barber.zip',
			'dest' => WP_PLUGIN_DIR . '/ag-business-barber',
		),
	);

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

	foreach ( $items as $item ) {
		$results[] = '--- ' . $item['name'] . ' ---';
		$results[] = 'Telechargement...';

		$tmp = ag_dev_sync_download( $item['url'] );
		if ( is_wp_error( $tmp ) ) {
			$results[] = 'ERREUR: ' . $tmp->get_error_message();
			continue;
		}

		$size = filesize( $tmp );
		$results[] = 'OK — ' . round( $size / 1024 ) . ' Ko';

		$extract = sys_get_temp_dir() . '/ag-sync-' . uniqid();
		$unzip = unzip_file( $tmp, $extract );
		@unlink( $tmp );

		if ( is_wp_error( $unzip ) ) {
			$results[] = 'ERREUR unzip: ' . $unzip->get_error_message();
			continue;
		}

		$folders = glob( $extract . '/*', GLOB_ONLYDIR );
		$source  = ! empty( $folders ) ? $folders[0] : $extract;

		if ( is_dir( $item['dest'] ) ) {
			$wp_filesystem->delete( $item['dest'], true );
			$results[] = 'Ancien dossier supprime';
		}

		$copy = copy_dir( $source, $item['dest'] );
		$wp_filesystem->delete( $extract, true );

		if ( is_wp_error( $copy ) ) {
			$results[] = 'ERREUR copy: ' . $copy->get_error_message();
		} else {
			$results[] = 'INSTALLE';
		}
	}

	return $results;
}

function ag_dev_sync_page() {
	$results = null;

	if ( isset( $_POST['ag_dev_sync_run'] ) && current_user_can( 'manage_options' ) ) {
		check_admin_referer( 'ag_dev_sync' );
		$results = ag_dev_sync_run();
	}
	?>
	<div class="wrap" style="max-width:600px;">
		<h1>AG Dev Sync</h1>
		<p>Synchronise le theme + companion depuis GitHub en un clic.</p>
		<p><strong style="color:#d63638;">Supprimer ce fichier (mu-plugins/ag-dev-sync.php) quand le template est final.</strong></p>

		<?php if ( $results ) : ?>
			<div style="background:#fff;border:1px solid #ccc;border-left:4px solid #00a32a;padding:16px;margin:16px 0;font-family:monospace;font-size:13px;line-height:1.8;">
				<?php foreach ( $results as $r ) : ?>
					<div><?php echo esc_html( $r ); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:24px;">
			<?php wp_nonce_field( 'ag_dev_sync' ); ?>
			<button type="submit" name="ag_dev_sync_run" value="1" class="button button-hero button-primary" style="font-size:1.1rem;padding:10px 36px;">
				Synchroniser depuis GitHub
			</button>
		</form>
	</div>
	<?php
}
