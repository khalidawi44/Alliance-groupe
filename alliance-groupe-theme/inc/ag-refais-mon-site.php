<?php
/**
 * ag-refais-mon-site.php — « Vois ton site refait par l'IA en 60 secondes ».
 *
 * Idée novatrice n°2 : le visiteur colle l'adresse de SON site actuel ; l'IA
 * le lit en direct et génère une maquette d'accueil AMÉLIORÉE, affichée à côté
 * de l'ancienne. Effet « waouh » immédiat → capture du lead dans le CRM.
 *
 * Réutilise le noyau ag-ia.php (clé `ag_ai_key`) et le CRM
 * (ag_prospect_add_record) + ag_push. Dégrade proprement sans clé.
 * Anti-abus : limite par IP (l'API a un coût).
 *
 * Page : /refais-mon-site (auto-créée). Shortcode : [ag_refais_mon_site].
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Limite d'appels IA par IP et par heure (protège le budget API). */
if ( ! defined( 'AG_REFAIS_MAX_HEURE' ) ) { define( 'AG_REFAIS_MAX_HEURE', 4 ); }

/** IP du visiteur (best effort). */
function ag_refais_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	return $ip ? md5( $ip ) : 'anon';
}

/** Vrai si l'IP a dépassé le quota horaire. */
function ag_refais_rate_hit() {
	$k = 'ag_refais_rl_' . ag_refais_ip();
	$n = (int) get_transient( $k );
	if ( $n >= AG_REFAIS_MAX_HEURE ) { return true; }
	set_transient( $k, $n + 1, HOUR_IN_SECONDS );
	return false;
}

/* ── Génération de la maquette (AJAX public, avec anti-abus) ─────────────── */
function ag_refais_generate() {
	if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_refais' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée, recharge la page.' ) );
	}
	if ( ! ag_ia_ready() ) {
		wp_send_json_error( array( 'msg' => "L'outil IA n'est pas encore activé. Reviens très vite !" ) );
	}
	if ( ag_refais_rate_hit() ) {
		wp_send_json_error( array( 'msg' => 'Tu as déjà testé plusieurs sites. Réessaie dans une heure ' ) );
	}
	$url  = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$page = ag_ia_fetch_page( $url, 5000 );
	if ( is_wp_error( $page ) ) {
		wp_send_json_error( array( 'msg' => $page->get_error_message() ) );
	}

	$system = "Tu es un directeur artistique web senior. On te donne le contenu texte d'un site d'entreprise existant (souvent daté). "
		. "Tu produis UNE page d'accueil moderne, unique et crédible pour CETTE entreprise, en te basant sur son vrai métier et sa vraie ville. "
		. "Contraintes STRICTES de sortie : renvoie UNIQUEMENT un fragment HTML (pas de <html>, <head>, <body>, pas de commentaire, pas de texte hors HTML). "
		. "Tout le style est en ligne (attribut style=\"...\") ou dans un seul <style> en tête du fragment. AUCUN script, AUCune image externe, AUCUN lien externe. "
		. "Utilise des dégradés, une belle typographie système, un hero avec titre accrocheur + sous-titre + 2 boutons, une bande de 3 atouts, une section services (3 cartes), et un bloc d'appel à l'action. "
		. "Palette élégante cohérente avec le métier. Textes en français, concrets, orientés bénéfice client. Reste sobre et pro (pas de lorem ipsum).";

	$user = "Voici le site actuel à moderniser.\nTitre : " . $page['title'] . "\nURL : " . $page['url'] . "\nContenu :\n" . $page['text'];

	$html = ag_ia_call( $system, $user, array( 'model' => ag_ia_model( 'fast' ), 'max_tokens' => 3200, 'temperature' => 0.7, 'timeout' => 90 ) );
	if ( is_wp_error( $html ) ) {
		wp_send_json_error( array( 'msg' => 'L\'IA n\'a pas pu générer la maquette : ' . $html->get_error_message() ) );
	}
	// Sécurise (défense en profondeur — l'iframe est de toute façon en sandbox
	// sans scripts) : retire script/iframe/object/embed/svg, les gestionnaires
	// d'événements (quotés ET non quotés) et les URI javascript:.
	$html = preg_replace( '#<(script|iframe|object|embed|svg)[^>]*>.*?</\1>#is', '', (string) $html );
	$html = preg_replace( '#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', (string) $html );
	$html = preg_replace( '#(href|src|xlink:href)\s*=\s*("|\')?\s*javascript:[^"\'>\s]*("|\')?#i', '', (string) $html );

	// On mémorise le contexte pour la capture du lead qui suit.
	set_transient( 'ag_refais_ctx_' . ag_refais_ip(), array( 'url' => $page['url'], 'title' => $page['title'] ), 2 * HOUR_IN_SECONDS );

	$payload = array( 'html' => $html, 'title' => $page['title'], 'src' => $page['url'] );

	/**
	 * Permet d'enrichir la réponse sans toucher au générateur.
	 * `inc/ag-refais-acces.php` s'y branche pour conserver la maquette et
	 * renvoyer un jeton : la version nette et le lien partageable ne sont
	 * délivrés qu'après confirmation de l'adresse email.
	 *
	 * @param array  $payload Réponse envoyée au navigateur.
	 * @param string $html    Maquette générée (déjà désinfectée).
	 * @param array  $page    Page source lue : url, title, text.
	 */
	$payload = (array) apply_filters( 'ag_refais_result_payload', $payload, $html, $page );

	wp_send_json_success( $payload );
}
add_action( 'wp_ajax_ag_refais_generate', 'ag_refais_generate' );
add_action( 'wp_ajax_nopriv_ag_refais_generate', 'ag_refais_generate' );

/* ── Capture du lead (le visiteur veut sa vraie maquette) ────────────────── */
function ag_refais_lead() {
	if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_refais' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée.' ) );
	}
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$site  = esc_url_raw( wp_unslash( $_POST['site'] ?? '' ) );
	if ( '' === $name || ( '' === $email && '' === $phone ) ) {
		wp_send_json_error( array( 'msg' => 'Indique ton nom et un email ou un téléphone.' ) );
	}
	$ctx = (array) get_transient( 'ag_refais_ctx_' . ag_refais_ip() );
	if ( '' === $site && ! empty( $ctx['url'] ) ) { $site = $ctx['url']; }

	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'website' => $site,
			'status'  => 'interesse',
			'source'  => 'refais-mon-site',
			'notes'   => 'Lead « Refais mon site par l\'IA ». Site actuel : ' . ( $site ? $site : '—' ),
		) );
	}
	if ( function_exists( 'ag_push' ) ) {
		ag_push( "Nouveau lead « Refais mon site » : " . $name . ' — ' . ( $email ? $email : $phone ) . ( $site ? ' — ' . $site : '' ) );
	}
	wp_send_json_success( array( 'msg' => 'Reçu ! On te recontacte très vite avec ta vraie maquette ' ) );
}
add_action( 'wp_ajax_ag_refais_lead', 'ag_refais_lead' );
add_action( 'wp_ajax_nopriv_ag_refais_lead', 'ag_refais_lead' );

/* ── Rendu de la page ────────────────────────────────────────────────────── */
function ag_refais_render() {
	$nonce = wp_create_nonce( 'ag_refais' );
	$ready = ag_ia_ready();
	$ajax  = admin_url( 'admin-ajax.php' );
	ob_start();
	?>
	<style>
		.agr-wrap{max-width:1040px;margin:0 auto;padding:28px 18px 72px}
		.agr-head{text-align:center;margin-bottom:26px}
		.agr-badge{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-weight:800;padding:7px 16px;border-radius:999px;font-size:.8rem;letter-spacing:.03em;text-transform:uppercase}
		.agr-head h1{font-size:clamp(1.7rem,5vw,2.5rem);margin:16px 0 8px;line-height:1.14}
		.agr-head p{color:#5a5348;max-width:600px;margin:0 auto;font-size:1.02rem;line-height:1.55}
		.agr-form{display:flex;gap:10px;max-width:600px;margin:22px auto 0;flex-wrap:wrap;justify-content:center}
		.agr-form input[type=url]{flex:1;min-width:240px;padding:14px 16px;border:2px solid #eadfc6;border-radius:12px;font-size:1rem}
		.agr-btn{background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-weight:800;border:0;border-radius:12px;padding:14px 24px;font-size:1rem;cursor:pointer}
		.agr-btn:disabled{opacity:.5;cursor:not-allowed}
		.agr-note{font-size:.82rem;color:#8a8272;text-align:center;margin-top:10px}
		.agr-status{text-align:center;margin:22px 0;color:#8a6a1e;font-weight:600;min-height:24px}
		.agr-cmp{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:22px}
		@media(max-width:760px){.agr-cmp{grid-template-columns:1fr}}
		.agr-col h3{margin:0 0 8px;font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#7a7266}
		.agr-col.new h3{color:#c8962c}
		.agr-frame{width:100%;height:520px;border:1px solid #efe7d6;border-radius:14px;background:#fff;box-shadow:0 6px 20px rgba(0,0,0,.06)}
		.agr-lead{display:none;max-width:520px;margin:30px auto 0;background:#1a1205;color:#fdf6e6;border-radius:16px;padding:24px 20px;text-align:center}
		.agr-lead h2{margin:0 0 6px;font-size:1.3rem}
		.agr-lead p{margin:0 auto 14px;max-width:420px;color:#e9dcc0;line-height:1.5}
		.agr-lead input{width:100%;box-sizing:border-box;padding:12px 14px;border:0;border-radius:10px;margin:6px 0;font-size:1rem}
		.agr-lead .agr-btn{width:100%;margin-top:8px}
		.agr-ok{display:none;text-align:center;color:#1f7a3d;font-weight:700;margin-top:14px}
		@media (prefers-color-scheme:dark){.agr-head p{color:#c9c0b0}.agr-form input[type=url]{background:#211a0e;color:#f0e8d6}}
	</style>
	<div class="agr-wrap">
		<div class="agr-head">
			<span class="agr-badge">Nouveau · propulsé par l'IA</span>
			<h1>Vois ton site refait par l'IA en 60 secondes</h1>
			<p>Colle l'adresse de ton site actuel. Notre IA le lit et te montre <strong>tout de suite</strong> à quoi il pourrait ressembler, modernisé. Gratuit, sans inscription.</p>
		</div>

		<?php if ( ! $ready ) : ?>
			<p class="agr-note">L'outil est en cours d'activation. Reviens très bientôt !</p>
		<?php endif; ?>

		<form class="agr-form" id="agr-form" <?php echo $ready ? '' : 'style="opacity:.5;pointer-events:none"'; ?>>
			<input type="url" id="agr-url" placeholder="https://mon-site-actuel.fr" required>
			<button type="submit" class="agr-btn" id="agr-go">Moderniser</button>
		</form>
		<p class="agr-note">On ne modifie jamais ton vrai site. C'est une simulation.</p>

		<div class="agr-status" id="agr-status"></div>

		<div class="agr-cmp" id="agr-cmp" style="display:none">
			<div class="agr-col"><h3>Ton site aujourd'hui</h3><iframe class="agr-frame" id="agr-old" title="Site actuel"></iframe></div>
			<div class="agr-col new"><h3>Proposé par l'IA</h3><iframe class="agr-frame" id="agr-new" title="Maquette IA" sandbox referrerpolicy="no-referrer"></iframe></div>
		</div>

		<div class="agr-lead" id="agr-lead">
			<h2>Ça te plaît ?</h2>
			<p>Laisse tes coordonnées : on te renvoie une vraie maquette sur-mesure, gratuitement.</p>
			<input type="text" id="agr-name" placeholder="Ton prénom / entreprise" autocomplete="name">
			<input type="email" id="agr-email" placeholder="Ton email" autocomplete="email">
			<input type="tel" id="agr-phone" placeholder="Ton téléphone (optionnel)" autocomplete="tel">
			<button class="agr-btn" id="agr-send">Je veux ma maquette →</button>
			<div class="agr-ok" id="agr-ok"></div>
		</div>
	</div>

	<script>
	(function(){
		var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
		var form=document.getElementById('agr-form'), go=document.getElementById('agr-go');
		var st=document.getElementById('agr-status'), cmp=document.getElementById('agr-cmp');
		var oldF=document.getElementById('agr-old'), newF=document.getElementById('agr-new');
		var lead=document.getElementById('agr-lead');
		if(!form) return;
		var steps=['L\'IA lit ton site…','Elle repense le design…','Elle construit la maquette…','Presque prêt…'], si=0, tmr=null;
		function tick(){ st.textContent=steps[si%steps.length]; si++; }
		form.addEventListener('submit',function(e){
			e.preventDefault();
			var url=document.getElementById('agr-url').value.trim();
			if(!url) return;
			go.disabled=true; cmp.style.display='none'; lead.style.display='none';
			si=0; tick(); tmr=setInterval(tick,2200);
			var fd=new FormData(); fd.append('action','ag_refais_generate'); fd.append('_n',N); fd.append('url',url);
			fetch(AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
				clearInterval(tmr); go.disabled=false;
				if(!j||!j.success){ st.textContent=''+((j&&j.data&&j.data.msg)||'Une erreur est survenue.'); return; }
				st.textContent='';
				try{ oldF.src=j.data.src; }catch(e){ oldF.removeAttribute('src'); }
				newF.srcdoc='<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'+j.data.html;
				cmp.style.display='grid';
				/* Le mur d'email (inc/ag-refais-acces.php) ecoute cet evenement
				   et se pose sur la maquette. S'il n'est pas charge, rien ne se
				   passe : la page fonctionne comme avant. */
				document.dispatchEvent(new CustomEvent('ag-refais:result',{detail:{token:j.data.token,frame:newF}}));
				if(!j.data.token){ lead.style.display='block'; lead.scrollIntoView({behavior:'smooth',block:'center'}); }
			}).catch(function(){ clearInterval(tmr); go.disabled=false; st.textContent='Connexion interrompue, réessaie.'; });
		});
		var send=document.getElementById('agr-send'), ok=document.getElementById('agr-ok');
		if(send){ send.addEventListener('click',function(){
			var name=document.getElementById('agr-name').value.trim();
			var email=document.getElementById('agr-email').value.trim();
			var phone=document.getElementById('agr-phone').value.trim();
			var site=document.getElementById('agr-url').value.trim();
			if(!name||(!email&&!phone)){ ok.style.display='block'; ok.style.color='#c0392b'; ok.textContent='Indique ton nom + un email ou téléphone.'; return; }
			send.disabled=true;
			var fd=new FormData(); fd.append('action','ag_refais_lead'); fd.append('_n',N);
			fd.append('name',name); fd.append('email',email); fd.append('phone',phone); fd.append('site',site);
			fetch(AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
				send.disabled=false; ok.style.display='block';
				if(j&&j.success){ ok.style.color='#7CFFB0'; ok.textContent=j.data.msg; send.style.display='none'; }
				else{ ok.style.color='#ffb3b3'; ok.textContent=(j&&j.data&&j.data.msg)||'Erreur, réessaie.'; }
			}).catch(function(){ send.disabled=false; });
		}); }
	})();
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ag_refais_mon_site', 'ag_refais_render' );

/** Titre SEO. */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_page( 'refais-mon-site' ) ) {
		$parts['title'] = "Vois ton site refait par l'IA en 60 secondes — Alliance Groupe";
	}
	return $parts;
}, 20 );

/** Auto-création de la page /refais-mon-site. */
add_action( 'init', function () {
	if ( get_option( 'ag_refais_page_v1' ) ) { return; }
	if ( ! get_page_by_path( 'refais-mon-site' ) ) {
		$id = wp_insert_post( array(
			'post_title'   => 'Refais mon site par l\'IA',
			'post_name'    => 'refais-mon-site',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '[ag_refais_mon_site]',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$tpl = 'templates/page-refais-mon-site.php';
			if ( file_exists( get_stylesheet_directory() . '/' . $tpl ) ) {
				update_post_meta( $id, '_wp_page_template', $tpl );
			}
		}
	}
	update_option( 'ag_refais_page_v1', 1 );
} );
