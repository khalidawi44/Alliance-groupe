<?php
/**
 * Template Name: Espace Client (app)
 *
 * App AUTONOME réservée aux CLIENTS (indépendante du thème). Outils : mon site +
 * demande de modif, mon rapport de sécurité, support WhatsApp, factures/maintenance
 * + parrainage. Installable en icône iPhone (PWA).
 */

if ( ! is_user_logged_in() ) {
	$back = add_query_arg( 'redirect_to', rawurlencode( home_url( '/mon-espace-client' ) ), home_url( '/connexion' ) );
	wp_safe_redirect( $back );
	exit;
}
$u        = wp_get_current_user();
$email    = $u->user_email;
$prenom   = $u->first_name ? $u->first_name : ( $u->display_name ? $u->display_name : 'vous' );
$icon     = get_stylesheet_directory_uri() . '/assets/images/logo-carte-square.jpg';
$site     = (string) get_user_meta( $u->ID, 'ag_client_site', true );
$wapro    = preg_replace( '/[^0-9]/', '', (string) get_option( 'ag_wa_pro', '' ) );
$tel_pro  = preg_replace( '/[^0-9+]/', '', (string) ( get_option( 'ag_tester_phone', '0744829516' ) ) );
$ajax     = admin_url( 'admin-ajax.php' );
$nonce    = wp_create_nonce( 'ag_client' );
$rapport  = $site ? ( function_exists( 'ag_rapport_full_url' ) ? ag_rapport_full_url( $site, $u->display_name ?: $email ) : add_query_arg( array( 'ag_rapport' => 1, 'site' => rawurlencode( $site ), 'name' => rawurlencode( $u->display_name ?: $email ) ), home_url( '/' ) ) ) : '';
$reco_msg = rawurlencode( "Je te recommande Alliance Groupe pour ton site web / ta sécurité : " . home_url( '/' ) );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="Alliance Client">
	<meta name="theme-color" content="#0b0b0f">
	<link rel="apple-touch-icon" href="<?php echo esc_url( $icon ); ?>">
	<title>Alliance Groupe — Client</title>
	<style>
		:root{ --gold:#d4b45c; --bg:#0b0b0f; --card:#16161d; --line:rgba(255,255,255,.09); --soft:#9a9aa2; }
		*{ box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
		html,body{ margin:0; background:var(--bg); color:#fff; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
		a{ color:inherit; text-decoration:none; }
		.appbar{ position:sticky; top:0; z-index:20; display:flex; align-items:center; gap:10px; padding:calc(env(safe-area-inset-top) + 10px) 16px 10px; background:rgba(11,11,15,.86); backdrop-filter:blur(12px); border-bottom:1px solid var(--line); }
		.appbar img{ width:30px; height:30px; border-radius:8px; } .appbar b{ font-size:1.05rem; } .appbar b span{ color:var(--gold); }
		.wrap{ max-width:640px; margin:0 auto; padding:16px 14px 100px; }
		.view{ display:none; } .view.on{ display:block; animation:fade .25s ease; }
		@keyframes fade{ from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }
		.hi{ font-size:1.35rem; font-weight:800; margin:4px 0 2px; } .hi span{ color:var(--gold); }
		.sub{ color:var(--soft); font-size:.9rem; margin:0 0 16px; }
		h2.sec{ font-size:1.15rem; margin:2px 0 12px; }
		.card{ background:var(--card); border:1px solid var(--line); border-radius:18px; padding:16px; margin-bottom:14px; }
		.card h3{ margin:0 0 10px; font-size:1.02rem; }
		input,textarea{ width:100%; background:rgba(0,0,0,.3); color:#fff; border:1px solid rgba(212,180,92,.32); border-radius:13px; padding:13px; font-size:1rem; }
		textarea{ margin-top:8px; min-height:90px; resize:vertical; }
		.b{ display:block; text-align:center; padding:15px; border-radius:14px; font-weight:800; border:none; cursor:pointer; width:100%; margin-top:10px; font-size:1rem; }
		.b.gold{ background:linear-gradient(135deg,#d4b45c,#b98f2f); color:#0b0b0f; }
		.b.wa{ background:#25d366; color:#04210f; } .b.ghost{ background:transparent; border:1px solid var(--line); color:#fff; }
		.launch{ display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:14px; }
		.launch a{ background:rgba(212,180,92,.09); border:1px solid rgba(212,180,92,.28); border-radius:16px; padding:16px 6px; text-align:center; font-weight:700; font-size:.9rem; display:flex; flex-direction:column; align-items:center; gap:7px; }
		.launch a i{ font-size:1.8rem; font-style:normal; }
		.plan{ display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--line); font-size:.92rem; }
		.plan b{ color:var(--gold); }
		.tabbar{ position:fixed; left:0; right:0; bottom:0; z-index:30; display:flex; background:rgba(16,16,22,.94); backdrop-filter:blur(12px); border-top:1px solid var(--line); padding-bottom:env(safe-area-inset-bottom); }
		.tabbar a{ flex:1; text-align:center; padding:9px 2px 8px; color:var(--soft); font-size:.68rem; font-weight:600; }
		.tabbar a i{ display:block; font-size:1.35rem; font-style:normal; margin-bottom:2px; filter:grayscale(1) opacity(.7); }
		.tabbar a.active{ color:var(--gold); } .tabbar a.active i{ filter:none; }
		#toast{ position:fixed; left:50%; transform:translateX(-50%); bottom:calc(84px + env(safe-area-inset-bottom)); z-index:40; background:#222; color:#fff; padding:11px 16px; border-radius:12px; font-size:.9rem; opacity:0; transition:opacity .2s; pointer-events:none; max-width:90%; text-align:center; }
		#toast.on{ opacity:1; }
		.logout{ text-align:center; color:var(--soft); font-size:.85rem; margin:22px 0 0; }
	</style>
</head>
<body>
<header class="appbar"><img src="<?php echo esc_url( $icon ); ?>" alt=""><b>Alliance <span>Client</span></b></header>
<div class="wrap">

	<!-- ACCUEIL -->
	<section class="view on" id="v-accueil">
		<p class="hi">Bonjour <span><?php echo esc_html( $prenom ); ?></span> 👋</p>
		<p class="sub">Votre espace client Alliance Groupe.</p>
		<div class="launch">
			<a href="#" onclick="C.tab('site');return false"><i>🌐</i>Mon site</a>
			<a href="#" onclick="C.tab('secu');return false"><i>🛡️</i>Ma sécurité</a>
			<a href="#" onclick="C.tab('support');return false"><i>💬</i>Support</a>
			<a href="#" onclick="C.tab('compte');return false"><i>🧾</i>Factures</a>
		</div>
		<div class="card">
			<h3>🎁 Parrainage</h3>
			<p class="sub" style="margin:-2px 0 8px;">Recommandez Alliance Groupe autour de vous — on vous remercie sur votre prochaine facture.</p>
			<a class="b wa" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo $reco_msg; ?>">🟢 Recommander sur WhatsApp</a>
			<a class="b ghost" href="sms:?&body=<?php echo $reco_msg; ?>">💬 Recommander par SMS</a>
		</div>
		<p class="logout"><a href="<?php echo esc_url( wp_logout_url( home_url( '/connexion' ) ) ); ?>">🚪 Se déconnecter</a></p>
		<p class="sub" style="text-align:center;font-size:.8rem;">💡 Installer : Safari → Partager ↑ → « Sur l'écran d'accueil ».</p>
	</section>

	<!-- MON SITE -->
	<section class="view" id="v-site">
		<h2 class="sec">🌐 Mon site</h2>
		<div class="card">
			<h3>Adresse de mon site</h3>
			<input type="url" id="c-site" value="<?php echo esc_attr( $site ); ?>" placeholder="https://mon-site.fr">
			<button type="button" id="c-site-save" class="b gold">💾 Enregistrer</button>
			<?php if ( $site ) : ?><a class="b ghost" target="_blank" rel="noopener" href="<?php echo esc_url( $site ); ?>">🔗 Ouvrir mon site</a><?php endif; ?>
		</div>
		<div class="card">
			<h3>Demander une modification</h3>
			<p class="sub" style="margin:-2px 0 6px;">Un texte à changer, une photo, un horaire, une nouvelle page… décrivez, on s'en occupe.</p>
			<textarea id="c-modif" placeholder="Ex : changer le numéro de téléphone en page contact…"></textarea>
			<button type="button" class="b gold c-send" data-type="modif" data-src="c-modif">📩 Envoyer ma demande</button>
		</div>
	</section>

	<!-- SÉCURITÉ -->
	<section class="view" id="v-secu">
		<h2 class="sec">🛡️ Ma sécurité</h2>
		<div class="card">
			<?php if ( $rapport ) : ?>
				<h3>Mon rapport de sécurité</h3>
				<p class="sub" style="margin:-2px 0 8px;">L'état de sécurité de votre site + le plan de correction.</p>
				<a class="b gold" target="_blank" rel="noopener" href="<?php echo esc_url( $rapport ); ?>">🛡️ Voir mon rapport</a>
			<?php else : ?>
				<h3>Audit de sécurité</h3>
				<p class="sub" style="margin:-2px 0 8px;">Renseignez d'abord l'adresse de votre site dans « Mon site » pour générer votre rapport.</p>
				<a class="b ghost" href="#" onclick="C.tab('site');return false">🌐 Renseigner mon site</a>
			<?php endif; ?>
			<textarea id="c-secu" placeholder="Une question sur ma sécurité ? Demander la correction…"></textarea>
			<button type="button" class="b gold c-send" data-type="securite" data-src="c-secu">🛠️ Demander la correction / poser une question</button>
		</div>
	</section>

	<!-- SUPPORT -->
	<section class="view" id="v-support">
		<h2 class="sec">💬 Support</h2>
		<div class="card">
			<h3>Nous contacter</h3>
			<?php if ( $wapro ) : ?><a class="b wa" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr( $wapro ); ?>?text=<?php echo rawurlencode( 'Bonjour, ' . ( $u->display_name ?: '' ) . ', j\'ai une question :' ); ?>">🟢 WhatsApp</a><?php endif; ?>
			<a class="b ghost" href="tel:<?php echo esc_attr( $tel_pro ); ?>">📞 Appeler</a>
			<a class="b ghost" href="mailto:contact@alliancegroupe-inc.com">✉️ Email</a>
		</div>
		<div class="card">
			<h3>Envoyer un message</h3>
			<textarea id="c-sup" placeholder="Votre message…"></textarea>
			<button type="button" class="b gold c-send" data-type="support" data-src="c-sup">📩 Envoyer</button>
		</div>
	</section>

	<!-- COMPTE / FACTURES -->
	<section class="view" id="v-compte">
		<h2 class="sec">🧾 Factures &amp; maintenance</h2>
		<div class="card">
			<h3>Ma maintenance</h3>
			<div class="plan"><span>Essentielle</span><b>29 €/mois</b></div>
			<div class="plan"><span>Pro</span><b>59 €/mois</b></div>
			<div class="plan" style="border:0;"><span>Premium</span><b>99 €/mois</b></div>
			<a class="b gold c-send" data-type="support" data-src="c-fact-hidden" style="margin-top:14px;">💬 Gérer mon abonnement / mes factures</a>
			<textarea id="c-fact-hidden" style="display:none;">Demande concernant mes factures / mon abonnement de maintenance.</textarea>
		</div>
		<p class="logout"><a href="<?php echo esc_url( wp_logout_url( home_url( '/connexion' ) ) ); ?>">🚪 Se déconnecter</a></p>
	</section>

</div>

<div id="toast"></div>
<nav class="tabbar">
	<a href="#" data-t="accueil" class="active" onclick="C.tab('accueil');return false"><i>🏠</i>Accueil</a>
	<a href="#" data-t="site" onclick="C.tab('site');return false"><i>🌐</i>Mon site</a>
	<a href="#" data-t="secu" onclick="C.tab('secu');return false"><i>🛡️</i>Sécurité</a>
	<a href="#" data-t="support" onclick="C.tab('support');return false"><i>💬</i>Support</a>
	<a href="#" data-t="compte" onclick="C.tab('compte');return false"><i>🧾</i>Compte</a>
</nav>

<script>
var C = (function(){
	var AJAX='<?php echo esc_js( $ajax ); ?>', N='<?php echo esc_js( $nonce ); ?>';
	function tab(id){ document.querySelectorAll('.view').forEach(function(v){v.classList.remove('on');}); var el=document.getElementById('v-'+id); if(el)el.classList.add('on'); document.querySelectorAll('.tabbar a').forEach(function(a){a.classList.toggle('active',a.getAttribute('data-t')===id);}); window.scrollTo(0,0); }
	var tt; function toast(m){ var t=document.getElementById('toast'); t.textContent=m; t.classList.add('on'); clearTimeout(tt); tt=setTimeout(function(){t.classList.remove('on');},3200); }
	function post(action,data){ var fd=new FormData(); fd.append('action',action); fd.append('_n',N); Object.keys(data||{}).forEach(function(k){fd.append(k,data[k]);}); return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}); }
	return { tab:tab, toast:toast, post:post };
})();
(function(){
	var ss=document.getElementById('c-site-save');
	if(ss){ ss.addEventListener('click',function(){ ss.textContent='…'; C.post('ag_client_save_site',{site:document.getElementById('c-site').value}).then(function(j){ ss.textContent='💾 Enregistrer'; C.toast(j&&j.success?'✅ Site enregistré':'❌ Erreur'); }).catch(function(){ ss.textContent='💾 Enregistrer'; C.toast('❌ Erreur'); }); }); }
	document.querySelectorAll('.c-send').forEach(function(b){
		b.addEventListener('click',function(){
			var src=document.getElementById(b.getAttribute('data-src')), msg=src?src.value:'', type=b.getAttribute('data-type');
			if(!msg.trim()){ C.toast('Écrivez votre message'); return; }
			var lbl=b.textContent; b.textContent='…';
			C.post('ag_client_request',{type:type,msg:msg}).then(function(j){ b.textContent=lbl; if(j&&j.success){ C.toast('✅ Envoyé ! On vous répond vite.'); if(src&&src.id!=='c-fact-hidden')src.value=''; } else { C.toast('❌ '+((j&&j.data&&j.data.m)||'Erreur')); } }).catch(function(){ b.textContent=lbl; C.toast('❌ Erreur réseau'); });
		});
	});
})();
</script>
</body>
</html>
<?php
exit;
