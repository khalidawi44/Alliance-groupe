<?php
/**
 * AG PWA — rend le site INSTALLABLE comme une application (iOS + Android),
 * SANS rien changer au site. Ajoute :
 *  - 2 manifestes : l'app publique « Alliance Groupe » + l'app dédiée
 *    « Alliance Ambassadeurs » (s'ouvre direct sur l'espace ambassadeur).
 *  - un service worker (offline minimal + base pour notifications).
 *  - les balises <head> (manifest, theme-color, icônes Apple) via wp_head.
 *  - un bouton flottant « 📲 Installer l'app ».
 *
 * Endpoints (réécriture, scope racine pour le service worker) :
 *   /ag-sw.js · /ag-app.webmanifest · /ag-amb.webmanifest
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_PWA_VER', '1.0.1' );

/* ---------------------------------------------------------------- Helpers */
if ( ! function_exists( 'ag_pwa_icon' ) ) {
	function ag_pwa_icon( $f ) { return get_stylesheet_directory_uri() . '/assets/images/pwa/' . $f; }
}
if ( ! function_exists( 'ag_pwa_amb_start' ) ) {
	/** URL d'ouverture de l'app Ambassadeurs (espace dédié). */
	function ag_pwa_amb_start() {
		// 1) page avec le modèle Espace Commercial, 2) ma-prospection, 3) ambassadeurs.
		$q = new WP_Query( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/page-espace-ambassadeur.php' ) );
		if ( ! empty( $q->posts ) ) return get_permalink( (int) $q->posts[0] );
		foreach ( array( 'ma-prospection', 'espace-ambassadeur', 'ambassadeurs' ) as $slug ) {
			$p = get_page_by_path( $slug );
			if ( $p && 'publish' === $p->post_status ) return get_permalink( $p );
		}
		return home_url( '/' );
	}
}
if ( ! function_exists( 'ag_pwa_is_amb_context' ) ) {
	/** Sommes-nous dans l'espace ambassadeur (→ proposer l'app Ambassadeurs) ? */
	function ag_pwa_is_amb_context() {
		if ( ! function_exists( 'is_page' ) ) return false;
		if ( is_page( array( 'ma-prospection', 'espace-ambassadeur', 'espace-commercial', 'guide-ambassadeur', 'candidature-ambassadeur', 'devenir-ambassadeur', 'classement' ) ) ) return true;
		if ( is_page() ) {
			$tpl = get_page_template_slug( get_queried_object_id() );
			if ( in_array( $tpl, array( 'templates/page-espace-ambassadeur.php', 'templates/page-guide-ambassadeur.php', 'templates/page-ambassadeurs.php', 'templates/page-classement.php' ), true ) ) return true;
		}
		return false;
	}
}

/* ---------------------------------------------------------------- Réécriture */
add_action( 'init', function () {
	add_rewrite_rule( '^ag-sw\.js$', 'index.php?ag_pwa=sw', 'top' );
	add_rewrite_rule( '^ag-app\.webmanifest$', 'index.php?ag_pwa=manifest', 'top' );
	add_rewrite_rule( '^ag-amb\.webmanifest$', 'index.php?ag_pwa=manifest_amb', 'top' );
	if ( get_option( 'ag_pwa_rules' ) !== AG_PWA_VER ) {
		flush_rewrite_rules( false );
		update_option( 'ag_pwa_rules', AG_PWA_VER );
	}
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'ag_pwa'; return $v; } );

add_action( 'template_redirect', function () {
	$what = get_query_var( 'ag_pwa' );
	if ( ! $what ) return;
	nocache_headers();
	if ( 'sw' === $what ) {
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: /' );
		echo ag_pwa_sw_js(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	echo wp_json_encode( ag_pwa_manifest( 'manifest_amb' === $what ) );
	exit;
} );

/* ---------------------------------------------------------------- Manifest */
if ( ! function_exists( 'ag_pwa_manifest' ) ) {
	function ag_pwa_manifest( $amb = false ) {
		$icons = array(
			array( 'src' => ag_pwa_icon( 'icon-192.png' ), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
			array( 'src' => ag_pwa_icon( 'icon-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
			array( 'src' => ag_pwa_icon( 'maskable-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
		);
		if ( $amb ) {
			$start = add_query_arg( 'app', '1', ag_pwa_amb_start() );
			return array(
				'id'               => '/?ag-app=amb',
				'name'             => 'Alliance Ambassadeurs',
				'short_name'       => 'Ambassadeurs',
				'description'      => 'Espace ambassadeur Alliance Groupe : prospection, gains, candidatures.',
				'start_url'        => $start,
				'scope'            => '/',
				'display'          => 'standalone',
				'orientation'      => 'portrait',
				'background_color' => '#0a0a0f',
				'theme_color'      => '#D4B45C',
				'lang'             => 'fr',
				'icons'            => $icons,
			);
		}
		return array(
			'id'               => '/?ag-app=site',
			'name'             => get_bloginfo( 'name' ) ?: 'Alliance Groupe',
			'short_name'       => 'Alliance',
			'description'      => get_bloginfo( 'description' ) ?: 'Studio web & sécurité.',
			'start_url'        => home_url( '/?app=1' ),
			'scope'            => '/',
			'display'          => 'standalone',
			'background_color' => '#0a0a0f',
			'theme_color'      => '#0a0a0f',
			'lang'             => 'fr',
			'icons'            => $icons,
		);
	}
}

/* ---------------------------------------------------------------- Service Worker */
if ( ! function_exists( 'ag_pwa_sw_js' ) ) {
	function ag_pwa_sw_js() {
		$ver  = AG_PWA_VER;
		$home = home_url( '/' );
		$off  = esc_js( $home );
		return <<<JS
/* AG Service Worker v{$ver} — cache léger + fallback offline (n'altère pas le site). */
const AG_CACHE = 'ag-pwa-{$ver}';
self.addEventListener('install', e => { self.skipWaiting(); });
self.addEventListener('activate', e => {
  e.waitUntil(caches.keys().then(ks => Promise.all(ks.filter(k => k !== AG_CACHE).map(k => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;
  // Ne pas mettre en cache l'admin / l'API / les requêtes authentifiées.
  if (url.pathname.startsWith('/wp-admin') || url.pathname.startsWith('/wp-json') || url.pathname.startsWith('/wp-login')) return;
  // Réseau d'abord, repli cache (toujours du contenu frais quand en ligne).
  e.respondWith(
    fetch(req).then(res => {
      if (res && res.status === 200 && res.type === 'basic') {
        const copy = res.clone();
        caches.open(AG_CACHE).then(c => c.put(req, copy));
      }
      return res;
    }).catch(() => caches.match(req).then(r => r || caches.match('{$off}')))
  );
});
JS;
	}
}

/* ---------------------------------------------------------------- <head> */
add_action( 'wp_head', function () {
	$amb  = ag_pwa_is_amb_context();
	$man  = $amb ? home_url( '/ag-amb.webmanifest' ) : home_url( '/ag-app.webmanifest' );
	$name = $amb ? 'Alliance Ambassadeurs' : ( get_bloginfo( 'name' ) ?: 'Alliance Groupe' );
	$tc   = $amb ? '#D4B45C' : '#0a0a0f';
	echo "\n<!-- AG PWA -->\n";
	echo '<link rel="manifest" href="' . esc_url( $man ) . '">' . "\n";
	echo '<meta name="theme-color" content="' . esc_attr( $tc ) . '">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
	echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr( $name ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( ag_pwa_icon( 'apple-touch.png' ) ) . '">' . "\n";
}, 2 );

/* ---------------------------------------------------------------- Enregistrement SW + bouton installer */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	$sw     = esc_url( home_url( '/ag-sw.js' ) );
	$amb    = ag_pwa_is_amb_context();
	$label  = $amb ? '📲 Installer l’app Ambassadeurs' : '📲 Installer l’application';
	?>
	<script>
	(function(){
		if ('serviceWorker' in navigator) {
			window.addEventListener('load', function(){ navigator.serviceWorker.register('<?php echo $sw; // phpcs:ignore ?>', { scope: '/' }).catch(function(){}); });
		}
		// Bouton d'installation (Android/Chrome : beforeinstallprompt ; iOS : consigne).
		var deferred = null;
		var btn = document.createElement('button');
		btn.textContent = <?php echo wp_json_encode( $label ); ?>;
		btn.setAttribute('style','position:fixed;left:50%;transform:translateX(-50%);bottom:16px;z-index:99998;display:none;padding:12px 20px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:700;font-family:Arial,sans-serif;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.35);cursor:pointer');
		document.addEventListener('DOMContentLoaded', function(){ document.body.appendChild(btn); });
		window.addEventListener('beforeinstallprompt', function(e){ e.preventDefault(); deferred = e; btn.style.display='block'; });
		btn.addEventListener('click', function(){
			if (deferred){ deferred.prompt(); deferred.userChoice.finally(function(){ deferred=null; btn.style.display='none'; }); }
		});
		window.addEventListener('appinstalled', function(){ btn.style.display='none'; });
		// iOS : pas de prompt natif → on affiche une consigne une seule fois.
		var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
		var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
		if (isIOS && !standalone && !localStorage.getItem('ag_ios_pwa_seen')) {
			btn.textContent = '📲 Ajouter à l’écran d’accueil : Partager → « Sur l’écran d’accueil »';
			btn.style.display='block';
			btn.addEventListener('click', function(){ localStorage.setItem('ag_ios_pwa_seen','1'); btn.style.display='none'; });
		}
	})();
	</script>
	<?php
} );
