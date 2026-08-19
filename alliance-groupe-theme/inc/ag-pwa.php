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

define( 'AG_PWA_VER', '1.0.6' );

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
			$start = add_query_arg( array( 'app' => '1', 'ag_app' => 'amb' ), ag_pwa_amb_start() );
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
			'start_url'        => add_query_arg( array( 'app' => '1', 'ag_app' => 'site' ), home_url( '/' ) ),
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
		$icon = esc_js( ag_pwa_icon( 'icon-192.png' ) );
		return <<<JS
/* AG Service Worker v{$ver} — cache léger + fallback offline + notifications push. */
const AG_CACHE = 'ag-pwa-{$ver}';
const AG_ICON  = '{$icon}';
self.addEventListener('install', e => { self.skipWaiting(); });
self.addEventListener('activate', e => {
  e.waitUntil(caches.keys().then(ks => Promise.all(ks.filter(k => k !== AG_CACHE).map(k => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;
  if (url.pathname.startsWith('/wp-admin') || url.pathname.startsWith('/wp-json') || url.pathname.startsWith('/wp-login')) return;
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
/* Notification reçue (même app fermée). */
self.addEventListener('push', e => {
  let d = {};
  try { d = e.data ? e.data.json() : {}; } catch (err) { d = { title: 'Alliance Groupe', body: e.data ? e.data.text() : '' }; }
  const title = d.title || 'Alliance Groupe';
  const opts = {
    body: d.body || '',
    icon: d.icon || AG_ICON,
    badge: AG_ICON,
    image: d.image || undefined,
    vibrate: [80, 40, 80],
    data: { url: d.url || '/' },
    tag: d.tag || 'ag-push'
  };
  e.waitUntil(self.registration.showNotification(title, opts));
});
self.addEventListener('notificationclick', e => {
  e.notification.close();
  const url = (e.notification.data && e.notification.data.url) || '/';
  e.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then(cl => {
    for (let i = 0; i < cl.length; i++) { if (cl[i].url.indexOf(url) > -1 && 'focus' in cl[i]) return cl[i].focus(); }
    if (clients.openWindow) return clients.openWindow(url);
  }));
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
	// Pose très tôt la classe « app ambassadeur » (évite le flash du menu site).
	echo '<script>(function(){try{var sa=matchMedia("(display-mode: standalone)").matches||navigator.standalone;var p=new URLSearchParams(location.search).get("ag_app");if(p){sessionStorage.setItem("ag_app",p);}var w=p||sessionStorage.getItem("ag_app");if(sa&&w==="amb"){document.documentElement.classList.add("ag-amb-app");}}catch(e){}})();</script>' . "\n";
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

/* ---------------------------------------------------------------- SW + bannière + choix d'app + modale iOS */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	$sw      = esc_url( home_url( '/ag-sw.js' ) );
	$amb     = ag_pwa_is_amb_context();
	$name    = $amb ? 'Alliance Ambassadeurs' : ( get_bloginfo( 'name' ) ?: 'Alliance Groupe' );
	$sub     = $amb ? 'Ton espace ambassadeur, en 1 tap' : 'iPhone & Android · gratuit';
	$icon    = esc_url( ag_pwa_icon( $amb ? 'amb-192.png' : 'icon-192.png' ) );
	$ico_site = esc_url( ag_pwa_icon( 'icon-192.png' ) );
	$ico_amb  = esc_url( ag_pwa_icon( 'amb-192.png' ) );
	$amb_url  = esc_url( add_query_arg( 'install', 'amb', ag_pwa_amb_start() ) );
	?>
	<style>
	.ag-appdl{display:inline-flex;align-items:center;gap:12px;padding:12px 18px;border:0;border-radius:16px;cursor:pointer;
		background:linear-gradient(135deg,#16161e,#0c0c12);color:#fff;font-family:inherit;box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid rgba(212,180,92,.45);text-align:left;}
	.ag-appdl:hover{transform:translateY(-1px);box-shadow:0 14px 34px rgba(0,0,0,.45)}
	.ag-appdl img{border-radius:10px;flex:none;background:#0a0a0f}
	.ag-appdl__txt{display:flex;flex-direction:column;line-height:1.2}
	.ag-appdl__txt strong{font-size:15px;color:#fff}
	.ag-appdl__txt small{font-size:11.5px;color:#D4B45C;margin-top:2px}
	.ag-appdl__arrow{margin-left:6px;font-size:20px;color:#D4B45C}
	.ag-appbn{position:fixed;left:12px;right:12px;bottom:calc(12px + env(safe-area-inset-bottom));z-index:99998;display:none;
		align-items:center;gap:12px;max-width:520px;margin:0 auto;padding:12px 14px;border-radius:18px;
		background:rgba(16,16,22,.96);backdrop-filter:blur(8px);border:1px solid rgba(212,180,92,.4);box-shadow:0 16px 40px rgba(0,0,0,.5);font-family:-apple-system,Arial,sans-serif}
	.ag-appbn img{width:44px;height:44px;border-radius:11px;flex:none}
	.ag-appbn__b{flex:1;min-width:0;color:#fff}
	.ag-appbn__b strong{display:block;font-size:14px}
	.ag-appbn__b small{display:block;font-size:11.5px;color:#b9b9c4}
	.ag-appbn__go{flex:none;padding:9px 16px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:13px;cursor:pointer}
	.ag-appbn__x{flex:none;background:none;border:0;color:#8a8a96;font-size:20px;line-height:1;cursor:pointer;padding:0 2px}
	.ag-ov{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.65);padding:20px}
	.ag-ov__c{max-width:380px;width:100%;background:#14141c;border:1px solid rgba(212,180,92,.4);border-radius:20px;padding:22px;color:#eee;font-family:-apple-system,Arial,sans-serif;text-align:center}
	.ag-ov__c h3{margin:0 0 4px;color:#D4B45C;font-family:Georgia,serif}
	.ag-ov__c p.sub{margin:0 0 14px;color:#9a9aa5;font-size:13px}
	.ag-choice{display:flex;align-items:center;gap:12px;width:100%;text-align:left;background:#0e0e15;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:12px;margin:10px 0;cursor:pointer}
	.ag-choice:hover{border-color:rgba(212,180,92,.6)}
	.ag-choice img{width:46px;height:46px;border-radius:12px;flex:none}
	.ag-choice b{display:block;font-size:14px;color:#fff}
	.ag-choice small{display:block;font-size:11.5px;color:#9a9aa5}
	.ag-choice .go{margin-left:auto;color:#D4B45C;font-weight:800;font-size:18px}
	.ag-appmodal__step{display:flex;align-items:center;gap:10px;text-align:left;background:#0e0e15;border-radius:12px;padding:10px 12px;margin:8px 0;font-size:14px}
	.ag-appmodal__step b{color:#D4B45C}
	.ag-ov__close{margin-top:8px;width:100%;padding:12px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;cursor:pointer}
	.ag-ov__x{background:none;border:0;color:#8a8a96;font-size:13px;margin-top:8px;cursor:pointer}
	</style>

	<div class="ag-appbn" id="ag-appbn">
		<img src="<?php echo $icon; // phpcs:ignore ?>" alt="">
		<div class="ag-appbn__b"><strong><?php echo esc_html( $name ); ?></strong><small><?php echo esc_html( $sub ); ?></small></div>
		<button class="ag-appbn__go" onclick="agInstallApp()">Installer</button>
		<button class="ag-appbn__x" aria-label="Fermer" onclick="agDismissBanner()">×</button>
	</div>

	<!-- Choix entre les 2 apps (site public) -->
	<div class="ag-ov" id="ag-choice" onclick="if(event.target===this)agCloseChoice()">
		<div class="ag-ov__c">
			<h3>Quelle application ?</h3>
			<p class="sub">Installe celle que tu veux (gratuit).</p>
			<div class="ag-choice" onclick="agPickSite()">
				<img src="<?php echo $ico_site; // phpcs:ignore ?>" alt="">
				<span><b>Application du site</b><small>Alliance Groupe — web &amp; sécurité</small></span><span class="go">⤓</span>
			</div>
			<a class="ag-choice" href="<?php echo $amb_url; // phpcs:ignore ?>">
				<img src="<?php echo $ico_amb; // phpcs:ignore ?>" alt="">
				<span><b>Application Ambassadeurs</b><small>Gagner / prospecter avec nous</small></span><span class="go">⤓</span>
			</a>
			<button class="ag-ov__x" onclick="agCloseChoice()">Plus tard</button>
		</div>
	</div>

	<!-- Étapes iOS (Ajouter à l'écran d'accueil) -->
	<div class="ag-ov" id="ag-appmodal" onclick="if(event.target===this)agCloseModal()">
		<div class="ag-ov__c">
			<img src="<?php echo $icon; // phpcs:ignore ?>" alt="" style="width:64px;height:64px;border-radius:16px;margin-bottom:8px">
			<h3>Installer <?php echo esc_html( $name ); ?></h3>
			<div class="ag-appmodal__step">1️⃣ Touche <b>Partager</b> <span style="font-size:18px">􀈂</span> en bas de Safari</div>
			<div class="ag-appmodal__step">2️⃣ Choisis <b>« Sur l’écran d’accueil »</b></div>
			<div class="ag-appmodal__step">3️⃣ <b>Ajouter</b> → l’app apparaît sur ton écran 🎉</div>
			<button class="ag-ov__close" onclick="agCloseModal()">J’ai compris</button>
		</div>
	</div>

	<script>
	(function(){
		if ('serviceWorker' in navigator) {
			// Auto-update : un nouveau service worker s'active tout seul et l'app se
			// recharge → mise à jour SANS désinstaller/réinstaller.
			var refreshing = false;
			navigator.serviceWorker.addEventListener('controllerchange', function(){
				if (refreshing) return; refreshing = true; location.reload();
			});
			window.addEventListener('load', function(){
				navigator.serviceWorker.register('<?php echo $sw; // phpcs:ignore ?>', { scope: '/' }).then(function(reg){
					if (!reg) return;
					// Re-vérifie les mises à jour à chaque ouverture/retour sur l'app.
					document.addEventListener('visibilitychange', function(){ if (!document.hidden) reg.update(); });
					setInterval(function(){ reg.update(); }, 60*60*1000);
				}).catch(function(){});
			});
		}
		var AMB = <?php echo $amb ? 'true' : 'false'; ?>;
		var AMB_URL = <?php echo wp_json_encode( $amb_url ); ?>;
		var deferred = null;
		var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
		var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
		var bn = document.getElementById('ag-appbn');
		var modal = document.getElementById('ag-appmodal');
		var choice = document.getElementById('ag-choice');

		function doInstall(){
			if (deferred){ deferred.prompt(); deferred.userChoice.finally(function(){ deferred=null; agDismissBanner(true); }); return; }
			if (modal){ modal.style.display='flex'; } // iOS / sans prompt → étapes (app du contexte courant)
		}
		// Sur le site public : proposer le CHOIX. Dans l'espace ambassadeur : installer direct l'app amb.
		window.agInstallApp = function(){ if (AMB) { doInstall(); } else if (choice) { choice.style.display='flex'; } else { doInstall(); } };
		window.agPickSite = function(){ agCloseChoice(); doInstall(); };
		window.agCloseChoice = function(){ if(choice) choice.style.display='none'; };
		window.agCloseModal = function(){ if(modal) modal.style.display='none'; };
		window.agDismissBanner = function(perm){ if(bn) bn.style.display='none'; try{document.body.classList.remove('ag-appbn-on');}catch(e){} if(perm!==true) localStorage.setItem('ag_appbn_off', String(Date.now())); };

		function showBanner(){
			// Bandeau d'installation DESACTIVE (demande du user 19/08) : il masquait
			// le contenu au chargement sur iPhone/Android. Le site reste installable
			// via le bouton « Telecharger l'application » (shortcode ag_pwa_button).
			return;
			/* eslint-disable no-unreachable */
			if (standalone) return;
			var off = parseInt(localStorage.getItem('ag_appbn_off')||'0',10);
			if (off && (Date.now()-off) < 7*24*3600*1000) return;
			if (bn) bn.style.display='flex';
			try{document.body.classList.add('ag-appbn-on');}catch(e){} // la barre collante CTA remonte au-dessus
		}
		window.addEventListener('beforeinstallprompt', function(e){
			e.preventDefault(); deferred = e; showBanner();
			// Arrivé sur l'espace amb via "Application Ambassadeurs" → on lance l'install direct.
			if (AMB && /[?&]install=amb/.test(location.search)) { doInstall(); }
		});
		window.addEventListener('appinstalled', function(){ agDismissBanner(true); });
		window.addEventListener('load', function(){
			if (isIOS && !standalone) showBanner();
			// iOS arrivé sur l'espace amb avec ?install=amb → montrer les étapes (app amb).
			if (AMB && isIOS && !standalone && /[?&]install=amb/.test(location.search) && modal) { setTimeout(function(){ modal.style.display='flex'; }, 400); }
		});
	})();
	</script>
	<?php
} );

/* ---------------------------------------------------------------- Menu DÉDIÉ de l'app Ambassadeurs (mode standalone) */
if ( ! function_exists( 'ag_pwa_amb_nav_items' ) ) {
	/** Onglets du menu de l'app Ambassadeurs (URL résolues sur les pages réelles). */
	function ag_pwa_amb_nav_items() {
		$find = function ( $slugs, $fallback ) {
			foreach ( (array) $slugs as $s ) { $p = get_page_by_path( $s ); if ( $p && 'publish' === $p->post_status ) return get_permalink( $p ); }
			return $fallback;
		};
		$home = ag_pwa_amb_start();
		return array(
			array( 'ic' => '🏠', 'lbl' => 'Accueil',    'url' => $home ),
			array( 'ic' => '🎯', 'lbl' => 'Prospecter', 'url' => $find( array( 'ma-prospection' ), $home ) ),
			array( 'ic' => '🏆', 'lbl' => 'Classement', 'url' => $find( array( 'classement' ), $home ) ),
			array( 'ic' => '🤝', 'lbl' => 'Recruter',   'url' => $find( array( 'candidature-ambassadeur', 'devenir-ambassadeur', 'guide-ambassadeur' ), $home ) ),
			array( 'ic' => '👤', 'lbl' => 'Compte',     'url' => $find( array( 'mon-compte', 'compte', 'connexion' ), $home ) ),
		);
	}
}
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	$items = ag_pwa_amb_nav_items();
	$cur   = trailingslashit( strtok( $_SERVER['REQUEST_URI'] ?? '/', '?' ) );
	?>
	<style>
	/* Visible UNIQUEMENT dans l'app Ambassadeurs installée (body.ag-amb-app). */
	.ag-ambnav{display:none}
	html.ag-amb-app .ag-nav{display:none !important}                 /* masque le menu du site */
	html.ag-amb-app body{padding-bottom:78px !important}
	html.ag-amb-app .ag-ambnav{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:99996;
		justify-content:space-around;align-items:stretch;gap:2px;padding:6px 6px calc(6px + env(safe-area-inset-bottom));
		background:rgba(12,12,18,.98);backdrop-filter:blur(10px);border-top:1px solid rgba(212,180,92,.35);font-family:-apple-system,Arial,sans-serif}
	html.ag-amb-app .ag-ambnav a{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;
		padding:6px 2px;border-radius:12px;text-decoration:none;color:#9a9aa5;font-size:10.5px;font-weight:600}
	html.ag-amb-app .ag-ambnav a .i{font-size:20px;line-height:1}
	html.ag-amb-app .ag-ambnav a.on{color:#D4B45C;background:rgba(212,180,92,.12)}
	/* petit en-tête d'app (nom) à la place du header masqué */
	html.ag-amb-app .ag-ambtop{display:flex}
	.ag-ambtop{display:none;align-items:center;gap:8px;position:sticky;top:0;z-index:99995;padding:10px 14px calc(10px);
		background:#0c0c12;border-bottom:1px solid rgba(212,180,92,.25);color:#fff;font-family:Georgia,serif}
	.ag-ambtop img{width:26px;height:26px;border-radius:7px}
	.ag-ambtop b{font-size:15px;color:#D4B45C}
	</style>

	<div class="ag-ambtop"><img src="<?php echo esc_url( ag_pwa_icon( 'amb-192.png' ) ); ?>" alt=""><b>Alliance Ambassadeurs</b><button type="button" onclick="if(window.agEnableNotifs)agEnableNotifs()" title="Activer les notifications" style="margin-left:auto;background:none;border:0;font-size:20px;cursor:pointer">🔔</button></div>

	<nav class="ag-ambnav" aria-label="Menu ambassadeur">
		<?php foreach ( $items as $it ) :
			$active = ( '#' !== $it['url'] && false !== strpos( trailingslashit( wp_parse_url( $it['url'], PHP_URL_PATH ) ?: '/' ), $cur ) && '/' !== $cur ) ? ' on' : '';
			?>
			<a class="agn<?php echo esc_attr( $active ); ?>" href="<?php echo esc_url( $it['url'] ); ?>"><span class="i"><?php echo esc_html( $it['ic'] ); ?></span><?php echo esc_html( $it['lbl'] ); ?></a>
		<?php endforeach; ?>
	</nav>

	<script>
	(function(){
		var sa = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
		var p = new URLSearchParams(location.search);
		var tag = p.get('ag_app');
		try { if (tag) sessionStorage.setItem('ag_app', tag); } catch(e){}
		var which = tag; try { if (!which) which = sessionStorage.getItem('ag_app'); } catch(e){}
		if (sa && which === 'amb') {
			document.documentElement.classList.add('ag-amb-app');
			document.addEventListener('DOMContentLoaded', function(){ document.body.classList.add('ag-amb-app'); });
			if (document.body) document.body.classList.add('ag-amb-app');
			// Met en surbrillance l'onglet correspondant à la page courante.
			document.addEventListener('DOMContentLoaded', function(){
				var here = location.pathname.replace(/\/+$/,'');
				document.querySelectorAll('.ag-ambnav a').forEach(function(a){
					try{ if (new URL(a.href).pathname.replace(/\/+$/,'') === here) a.classList.add('on'); }catch(e){}
				});
			});
		}
	})();
	</script>
	<?php
}, 5 );
