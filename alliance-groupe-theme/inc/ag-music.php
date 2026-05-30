<?php
/**
 * AG Musique — lecteur de fond persistant sur TOUT le site.
 *
 * But : la musique « ne s'arrête jamais » en naviguant sur le site, tout en
 * gardant des pages réelles (SEO intact) et l'expérience/compréhension client.
 *
 * Comment : un petit lecteur audio en pied de page, qui reprend la PISTE et la
 * POSITION via sessionStorage à chaque chargement. Le voyage (page-experience)
 * a son propre lecteur mais partage les MÊMES clés sessionStorage
 * (agxm_on / agxm_vol / agxm_src / agxm_t) -> continuité dans les deux sens.
 *
 * Contrôles : bouton son + volume (−/+), choix mémorisé (pas de harcèlement).
 * Volume doux par défaut. Démarre au 1er geste si l'autoplay sonore est bloqué.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'ag_music_playlist' ) ) {
	/** Playlist : fichiers du dossier son/ + liens directs (option ag_xp_music_urls). */
	function ag_music_playlist() {
		$son_dir = get_stylesheet_directory() . '/assets/images/son';
		$son_url = get_stylesheet_directory_uri() . '/assets/images/son';
		$list = array();
		if ( is_dir( $son_dir ) ) {
			foreach ( (array) glob( $son_dir . '/*.{m4a,mp3,ogg,wav,aac}', GLOB_BRACE ) as $f ) {
				$list[] = $son_url . '/' . rawurlencode( basename( $f ) );
			}
		}
		foreach ( preg_split( '/[\r\n,]+/', (string) get_option( 'ag_xp_music_urls', '' ) ) as $u ) {
			$u = trim( $u );
			if ( $u !== '' && preg_match( '#^https?://#i', $u ) ) $list[] = esc_url_raw( $u );
		}
		if ( empty( $list ) ) {
			$d = get_option( 'ag_xp_music', $son_url . '/Napoli_con_bizonnio2.m4a' );
			if ( $d ) $list[] = $d;
		}
		return array_values( array_unique( $list ) );
	}
}

add_action( 'wp_footer', 'ag_music_render', 60 );
function ag_music_render() {
	if ( is_admin() ) return;
	// Le voyage a son propre lecteur (état partagé via sessionStorage) -> on évite le doublon.
	if ( function_exists( 'is_page_template' ) && (
		is_page_template( 'templates/page-experience.php' )      // a son propre lecteur
		|| is_page_template( 'templates/page-audit-securite.php' ) // page audit : pas de musique
	) ) return;

	$list = ag_music_playlist();
	if ( empty( $list ) ) return;
	$first = $list[0];
	?>
	<div id="agm" class="agm">
		<button id="agm-sound" class="agm-btn" type="button" aria-label="Couper le son">♪</button>
		<button id="agm-down" class="agm-btn agm-mini" type="button" aria-label="Baisser le volume">−</button>
		<button id="agm-up" class="agm-btn agm-mini" type="button" aria-label="Monter le volume">+</button>
		<audio id="agm-audio" preload="none" src="<?php echo esc_url( $first ); ?>"></audio>
	</div>
	<style>
	.agm{position:fixed;left:16px;bottom:16px;z-index:9990;display:flex;align-items:center;gap:8px}
	.agm-btn{width:44px;height:44px;border-radius:50%;background:rgba(10,10,15,.55);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);border:1.5px solid rgba(212,180,92,.55);color:#D4B45C;font-size:20px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
	.agm-btn:hover{background:#D4B45C;color:#0a0a0f}
	.agm-mini{width:34px;height:34px;font-size:18px;opacity:.85}
	.agm.is-off .agm-mini{display:none}
	.agm.is-off #agm-sound{opacity:.6}
	@media(max-width:640px){.agm{left:12px;bottom:12px;gap:6px}.agm-btn{width:40px;height:40px}.agm-mini{width:30px;height:30px}}
	</style>
	<script>
	(function(){
		var PL = <?php echo wp_json_encode( $list ); ?>;
		if(!PL.length) return;
		var audio=document.getElementById('agm-audio'), box=document.getElementById('agm');
		var bSound=document.getElementById('agm-sound'), bDown=document.getElementById('agm-down'), bUp=document.getElementById('agm-up');
		var SS=window.sessionStorage;
		function gi(k,d){ try{var v=SS.getItem(k); return v===null?d:v;}catch(e){return d;} }
		function si(k,v){ try{SS.setItem(k,String(v));}catch(e){} }
		var on = gi('agxm_on','1')==='1';
		var vol = parseFloat(gi('agxm_vol','0.12')); if(isNaN(vol)) vol=0.12;
		var savedSrc = gi('agxm_src','');
		var startT = parseFloat(gi('agxm_t','0'))||0;
		if(savedSrc) audio.src=savedSrc;
		function curIdx(){ var s=audio.currentSrc||audio.src; var i=PL.indexOf(s); return i<0?0:i; }
		function save(){ si('agxm_src', audio.currentSrc||audio.src); si('agxm_t', audio.currentTime||0); si('agxm_on', on?'1':'0'); si('agxm_vol', vol); }
		function applyOff(){ box.classList.toggle('is-off',!on); bSound.textContent= on?'♪':'✕'; bSound.setAttribute('aria-label', on?'Couper le son':'Activer le son'); }
		var seeked=false;
		function start(){ if(!on) return; if(!seeked && startT>0){ var go=function(){ try{audio.currentTime=startT;}catch(e){} seeked=true; }; if(audio.readyState>0) go(); else audio.addEventListener('loadedmetadata', go, {once:true}); } audio.volume=vol; var p=audio.play(); if(p&&p.catch) p.catch(function(){}); }
		applyOff();
		start();
		var resume=function(){ if(on&&audio.paused) start(); };
		['pointerdown','touchstart','keydown'].forEach(function(ev){ window.addEventListener(ev, resume, {once:true}); });
		audio.addEventListener('timeupdate', save);
		window.addEventListener('pagehide', save);
		audio.addEventListener('ended', function(){ var n=(curIdx()+1)%PL.length; audio.src=PL[n]; si('agxm_src',PL[n]); si('agxm_t',0); seeked=true; if(on){ audio.volume=vol; var p=audio.play(); if(p&&p.catch)p.catch(function(){}); } });
		bSound.addEventListener('click', function(){ on=!on; applyOff(); save(); if(on) start(); else audio.pause(); });
		function setVol(v){ vol=Math.max(0.03,Math.min(0.6,v)); save(); if(on){ if(audio.paused) start(); else audio.volume=vol; } }
		bDown.addEventListener('click', function(){ setVol(vol-0.06); });
		bUp.addEventListener('click', function(){ setVol(vol+0.06); });
	})();
	</script>
	<?php
}
