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

define( 'AG_PWA_VER', '1.0.2' );

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
		if ( $amb ) {
			$icons = array(
				array( 'src' => ag_pwa_icon( 'amb-192.png' ), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => ag_pwa_icon( 'amb-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => ag_pwa_icon( 'amb-maskable-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
			);
		} else {
			$icons = array(
				array( 'src' => ag_pwa_icon( 'icon-192.png' ), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => ag_pwa_icon( 'icon-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => ag_pwa_icon( 'maskable-512.png' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
			);
		}
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
	echo '<link rel="apple-touch-icon" href="' . esc_url( ag_pwa_icon( $amb ? 'amb-apple.png' : 'apple-touch.png' ) ) . '">' . "\n";
}, 2 );

/* ---------------------------------------------------------------- Bouton « Télécharger l'app » (shortcode) */
if ( ! function_exists( 'ag_pwa_button' ) ) {
	/** Beau bouton placeable n'importe où : [ag_install_app] / [telecharger_app]. */
	function ag_pwa_button( $atts = array() ) {
		$amb = function_exists( 'ag_pwa_is_amb_context' ) && ag_pwa_is_amb_context();
		$a = shortcode_atts( array(
			'texte'   => $amb ? 'Installer l’app Ambassadeurs' : 'Télécharger l’application',
			'sous'    => $amb ? 'Ton espace, en 1 tap · iPhone &amp; Android' : 'iPhone &amp; Android · gratuit, sans store',
		), $atts );
		$icon = esc_url( ag_pwa_icon( $amb ? 'amb-192.png' : 'icon-192.png' ) );
		return '<button type="button" class="ag-appdl" onclick="agInstallApp()">'
			. '<img src="' . $icon . '" alt="" width="40" height="40">'
			. '<span class="ag-appdl__txt"><strong>' . esc_html( $a['texte'] ) . '</strong><small>' . wp_kses_post( $a['sous'] ) . '</small></span>'
			. '<span class="ag-appdl__arrow">⤓</span></button>';
	}
}
add_shortcode( 'ag_install_app', 'ag_pwa_button' );
add_shortcode( 'telecharger_app', 'ag_pwa_button' );

/* Placement AUTO du bouton dans l'espace ambassadeur (en bas du contenu). */
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_main_query() || ! in_the_loop() || ! is_page() ) return $content;
	if ( ! ag_pwa_is_amb_context() ) return $content;
	if ( false !== strpos( $content, 'ag-appdl' ) ) return $content; // déjà présent (shortcode manuel)
	return $content . '<div style="text-align:center;margin:26px 0;">' . ag_pwa_button() . '</div>';
} );

/* ---------------------------------------------------------------- SW + bannière + modale iOS */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	$sw    = esc_url( home_url( '/ag-sw.js' ) );
	$amb   = ag_pwa_is_amb_context();
	$name  = $amb ? 'Alliance Ambassadeurs' : ( get_bloginfo( 'name' ) ?: 'Alliance Groupe' );
	$sub   = $amb ? 'Ton espace ambassadeur, en 1 tap' : 'iPhone & Android · gratuit';
	$icon  = esc_url( ag_pwa_icon( $amb ? 'amb-192.png' : 'icon-192.png' ) );
	?>
	<style>
	/* Bouton "Télécharger l'app" (shortcode) */
	.ag-appdl{display:inline-flex;align-items:center;gap:12px;padding:12px 18px;border:0;border-radius:16px;cursor:pointer;
		background:linear-gradient(135deg,#16161e,#0c0c12);color:#fff;font-family:inherit;box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid rgba(212,180,92,.45);text-align:left;}
	.ag-appdl:hover{transform:translateY(-1px);box-shadow:0 14px 34px rgba(0,0,0,.45)}
	.ag-appdl img{border-radius:10px;flex:none;background:#0a0a0f}
	.ag-appdl__txt{display:flex;flex-direction:column;line-height:1.2}
	.ag-appdl__txt strong{font-size:15px;color:#fff}
	.ag-appdl__txt small{font-size:11.5px;color:#D4B45C;margin-top:2px}
	.ag-appdl__arrow{margin-left:6px;font-size:20px;color:#D4B45C}
	/* Bannière flottante */
	.ag-appbn{position:fixed;left:12px;right:12px;bottom:calc(12px + env(safe-area-inset-bottom));z-index:99998;display:none;
		align-items:center;gap:12px;max-width:520px;margin:0 auto;padding:12px 14px;border-radius:18px;
		background:rgba(16,16,22,.96);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);box-shadow:0 16px 40px rgba(0,0,0,.5);font-family:-apple-system,Arial,sans-serif}
	.ag-appbn img{width:44px;height:44px;border-radius:11px;flex:none}
	.ag-appbn__b{flex:1;min-width:0;color:#fff}
	.ag-appbn__b strong{display:block;font-size:14px}
	.ag-appbn__b small{display:block;font-size:11.5px;color:#b9b9c4}
	.ag-appbn__go{flex:none;padding:9px 16px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:13px;cursor:pointer}
	.ag-appbn__x{flex:none;background:none;border:0;color:#8a8a96;font-size:20px;line-height:1;cursor:pointer;padding:0 2px}
	/* Modale iOS */
	.ag-appmodal{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.6);padding:20px}
	.ag-appmodal__c{max-width:340px;width:100%;background:#14141c;border:1px solid rgba(212,180,92,.4);border-radius:20px;padding:22px;color:#eee;font-family:-apple-system,Arial,sans-serif;text-align:center}
	.ag-appmodal__c img{width:64px;height:64px;border-radius:16px;margin-bottom:10px}
	.ag-appmodal__c h3{margin:0 0 12px;color:#D4B45C;font-family:Georgia,serif}
	.ag-appmodal__step{display:flex;align-items:center;gap:10px;text-align:left;background:#0e0e15;border-radius:12px;padding:10px 12px;margin:8px 0;font-size:14px}
	.ag-appmodal__step b{color:#D4B45C}
	.ag-appmodal__close{margin-top:14px;width:100%;padding:12px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;cursor:pointer}
	</style>

	<div class="ag-appbn" id="ag-appbn">
		<img src="<?php echo $icon; // phpcs:ignore ?>" alt="">
		<div class="ag-appbn__b"><strong><?php echo esc_html( $name ); ?></strong><small><?php echo esc_html( $sub ); ?></small></div>
		<button class="ag-appbn__go" onclick="agInstallApp()">Installer</button>
		<button class="ag-appbn__x" aria-label="Fermer" onclick="agDismissBanner()">×</button>
	</div>

	<div class="ag-appmodal" id="ag-appmodal" onclick="if(event.target===this)agCloseModal()">
		<div class="ag-appmodal__c">
			<img src="<?php echo $icon; // phpcs:ignore ?>" alt="">
			<h3>Installer <?php echo esc_html( $name ); ?></h3>
			<div class="ag-appmodal__step">1️⃣ Touche <b>Partager</b> <span style="font-size:18px">􀈂</span> en bas de Safari</div>
			<div class="ag-appmodal__step">2️⃣ Choisis <b>« Sur l’écran d’accueil »</b></div>
			<div class="ag-appmodal__step">3️⃣ <b>Ajouter</b> → l’app apparaît sur ton écran 🎉</div>
			<button class="ag-appmodal__close" onclick="agCloseModal()">J’ai compris</button>
		</div>
	</div>

	<script>
	(function(){
		if ('serviceWorker' in navigator) {
			window.addEventListener('load', function(){ navigator.serviceWorker.register('<?php echo $sw; // phpcs:ignore ?>', { scope: '/' }).catch(function(){}); });
		}
		var deferred = null;
		var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
		var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
		var bn = document.getElementById('ag-appbn');
		var modal = document.getElementById('ag-appmodal');

		window.agInstallApp = function(){
			if (deferred){ deferred.prompt(); deferred.userChoice.finally(function(){ deferred=null; agDismissBanner(true); }); return; }
			// iOS ou navigateur sans prompt → on montre les étapes.
			if (modal){ modal.style.display='flex'; }
		};
		window.agCloseModal = function(){ if(modal) modal.style.display='none'; };
		window.agDismissBanner = function(perm){ if(bn) bn.style.display='none'; if(perm!==true) localStorage.setItem('ag_appbn_off', String(Date.now())); };

		function showBanner(){
			if (standalone) return;                         // déjà installée
			var off = parseInt(localStorage.getItem('ag_appbn_off')||'0',10);
			if (off && (Date.now()-off) < 7*24*3600*1000) return; // masquée 7 jours après fermeture
			if (bn) bn.style.display='flex';
		}
		window.addEventListener('beforeinstallprompt', function(e){ e.preventDefault(); deferred = e; showBanner(); });
		window.addEventListener('appinstalled', function(){ agDismissBanner(true); });
		if (isIOS && !standalone) { window.addEventListener('load', showBanner); }
	})();
	</script>
	<?php
} );
