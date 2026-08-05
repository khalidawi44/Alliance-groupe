<?php
/**
 * ag-devis-instant.php — Devis instantané par l'IA (voix ou texte).
 *
 * Idée novatrice n°5 : le visiteur décrit son projet (à la voix ou au clavier)
 * et reçoit EN DIRECT un devis structuré (pack conseillé, fourchette de prix,
 * postes, délai) rédigé par l'IA — puis peut laisser ses coordonnées. Zéro
 * attente, zéro rendez-vous pour un premier chiffrage.
 *
 * Réutilise ag-ia.php (structured output), le CRM et ag_push. Dégrade sans clé.
 * Anti-abus : quota par IP / heure.
 *
 * Page : /devis-instant (auto-créée). Shortcode : [ag_devis_instant].
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_DEVIS_MAX_HEURE' ) ) { define( 'AG_DEVIS_MAX_HEURE', 5 ); }

/** Quota par IP / heure. */
function ag_devis_rate_hit() {
	$k = 'ag_devis_rl_' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? md5( (string) $_SERVER['REMOTE_ADDR'] ) : 'anon' );
	$n = (int) get_transient( $k );
	if ( $n >= AG_DEVIS_MAX_HEURE ) { return true; }
	set_transient( $k, $n + 1, HOUR_IN_SECONDS );
	return false;
}

/* ── AJAX : génère le devis structuré ────────────────────────────────────── */
function ag_devis_generate() {
	if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_devis' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée, recharge la page.' ) );
	}
	if ( ! ag_ia_ready() ) {
		wp_send_json_error( array( 'msg' => 'Le devis IA n\'est pas encore activé. Reviens vite !' ) );
	}
	if ( ag_devis_rate_hit() ) {
		wp_send_json_error( array( 'msg' => 'Tu as déjà fait plusieurs devis. Réessaie dans une heure 🙏' ) );
	}
	$besoin = sanitize_textarea_field( wp_unslash( $_POST['besoin'] ?? '' ) );
	if ( mb_strlen( trim( $besoin ) ) < 8 ) {
		wp_send_json_error( array( 'msg' => 'Décris ton projet en quelques mots 🙂' ) );
	}

	$system = "Tu es chiffreur chez Alliance Groupe, agence web à Nantes. À partir de la description d'un projet, tu établis un devis RÉALISTE et honnête. "
		. "Nos packs Site Express : Essentiel 490 € (site vitrine simple), Pro 890 € (multi-pages + SEO + formulaire), Premium 1490 € (sur-mesure, réservation/boutique, animations). "
		. "Maintenance possible : 29/59/99 €/mois. Choisis LE pack le plus adapté, donne une fourchette cohérente avec nos prix (ne dépasse pas 2500 € sauf projet vraiment complexe, et dis-le), liste 3 à 6 postes concrets, un délai réaliste, et un petit mot chaleureux. Reste en français, ton pro et rassurant.";

	$schema = array(
		'type' => 'object', 'additionalProperties' => false,
		'required' => array( 'pack', 'prix_min', 'prix_max', 'postes', 'delai', 'mot' ),
		'properties' => array(
			'pack'     => array( 'type' => 'string', 'description' => 'Nom du pack conseillé (Essentiel, Pro, Premium ou Sur-mesure).' ),
			'prix_min' => array( 'type' => 'integer' ),
			'prix_max' => array( 'type' => 'integer' ),
			'maintenance' => array( 'type' => 'string', 'description' => 'Suggestion de maintenance mensuelle, ex "59 €/mois".' ),
			'postes'   => array( 'type' => 'array', 'items' => array(
				'type' => 'object', 'additionalProperties' => false,
				'required' => array( 'poste', 'detail' ),
				'properties' => array( 'poste' => array( 'type' => 'string' ), 'detail' => array( 'type' => 'string' ) ),
			) ),
			'delai'    => array( 'type' => 'string' ),
			'mot'      => array( 'type' => 'string' ),
		),
	);

	$out = ag_ia_json( $system, "Projet du client : " . $besoin, $schema, array( 'model' => ag_ia_model( 'fast' ), 'max_tokens' => 1500, 'timeout' => 60 ) );
	if ( is_wp_error( $out ) ) {
		wp_send_json_error( array( 'msg' => 'L\'IA n\'a pas pu chiffrer : ' . $out->get_error_message() ) );
	}
	set_transient( 'ag_devis_ctx_' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? md5( (string) $_SERVER['REMOTE_ADDR'] ) : 'anon' ), $besoin, 2 * HOUR_IN_SECONDS );
	wp_send_json_success( $out );
}
add_action( 'wp_ajax_ag_devis_generate', 'ag_devis_generate' );
add_action( 'wp_ajax_nopriv_ag_devis_generate', 'ag_devis_generate' );

/* ── AJAX : capture du lead + son devis ──────────────────────────────────── */
function ag_devis_lead() {
	if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_devis' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée.' ) );
	}
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$pack  = sanitize_text_field( wp_unslash( $_POST['pack'] ?? '' ) );
	if ( '' === $name || ( '' === $email && '' === $phone ) ) {
		wp_send_json_error( array( 'msg' => 'Indique ton nom et un email ou un téléphone.' ) );
	}
	$besoin = (string) get_transient( 'ag_devis_ctx_' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? md5( (string) $_SERVER['REMOTE_ADDR'] ) : 'anon' ) );
	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'   => $name,
			'email'  => $email,
			'phone'  => $phone,
			'status' => 'interesse',
			'source' => 'devis-instant',
			'notes'  => 'Devis instantané IA' . ( $pack ? ' (pack ' . $pack . ')' : '' ) . '. Besoin : ' . ( $besoin ? $besoin : '—' ),
		) );
	}
	if ( function_exists( 'ag_push' ) ) {
		ag_push( "🧾 Devis instantané — nouveau lead : " . $name . ' — ' . ( $email ? $email : $phone ) . ( $pack ? ' · pack ' . $pack : '' ) );
	}
	wp_send_json_success( array( 'msg' => 'Parfait ! On t\'envoie ton devis détaillé et on te rappelle 🎉' ) );
}
add_action( 'wp_ajax_ag_devis_lead', 'ag_devis_lead' );
add_action( 'wp_ajax_nopriv_ag_devis_lead', 'ag_devis_lead' );

/* ── Rendu de la page ────────────────────────────────────────────────────── */
function ag_devis_render() {
	$nonce = wp_create_nonce( 'ag_devis' );
	$ready = ag_ia_ready();
	$ajax  = admin_url( 'admin-ajax.php' );
	ob_start();
	?>
	<style>
		.agd-wrap{max-width:760px;margin:0 auto;padding:28px 18px 72px}
		.agd-head{text-align:center;margin-bottom:24px}
		.agd-badge{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-weight:800;padding:7px 16px;border-radius:999px;font-size:.8rem;letter-spacing:.03em;text-transform:uppercase}
		.agd-head h1{font-size:clamp(1.7rem,5vw,2.5rem);margin:16px 0 8px;line-height:1.14}
		.agd-head p{color:#5a5348;max-width:560px;margin:0 auto;font-size:1.02rem;line-height:1.55}
		.agd-box{margin-top:22px;background:#fff;border:1px solid #efe7d6;border-radius:16px;padding:16px}
		.agd-box textarea{width:100%;box-sizing:border-box;min-height:120px;border:1px solid #e2d8c4;border-radius:12px;padding:13px;font-size:1rem;resize:vertical;font-family:inherit}
		.agd-row{display:flex;gap:10px;margin-top:10px;flex-wrap:wrap}
		.agd-btn{background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-weight:800;border:0;border-radius:12px;padding:13px 22px;font-size:1rem;cursor:pointer}
		.agd-btn:disabled{opacity:.5;cursor:not-allowed}
		.agd-mic{background:#1a1205;color:#fdf6e6;border:0;border-radius:12px;padding:13px 18px;font-size:1rem;cursor:pointer}
		.agd-mic.rec{background:#c0392b;animation:agdpulse 1s infinite}
		@keyframes agdpulse{50%{opacity:.6}}
		.agd-status{text-align:center;margin:18px 0;color:#8a6a1e;font-weight:600;min-height:22px}
		.agd-result{display:none;margin-top:22px;background:#1a1205;color:#fdf6e6;border-radius:18px;padding:24px}
		.agd-result h2{margin:0 0 4px;font-size:1.5rem;color:#e6b84a}
		.agd-price{font-size:1.9rem;font-weight:800;margin:6px 0 2px}
		.agd-delai{color:#e9dcc0;font-size:.95rem;margin-bottom:14px}
		.agd-postes{list-style:none;padding:0;margin:0 0 14px}
		.agd-postes li{padding:9px 0;border-bottom:1px solid #33291700;border-bottom-color:rgba(255,255,255,.12)}
		.agd-postes b{color:#f0e8d6}
		.agd-postes span{display:block;color:#c9c0b0;font-size:.9rem}
		.agd-mot{background:rgba(255,255,255,.07);border-radius:12px;padding:12px 14px;font-style:italic;color:#f0e8d6;margin-bottom:16px}
		.agd-lead input{width:100%;box-sizing:border-box;padding:12px 14px;border:0;border-radius:10px;margin:6px 0;font-size:1rem}
		.agd-ok{display:none;text-align:center;font-weight:700;margin-top:10px}
		.agd-note{font-size:.8rem;color:#8a8272;text-align:center;margin-top:10px}
		@media (prefers-color-scheme:dark){.agd-box{background:#211a0e;border-color:#3a2f1a}.agd-box textarea{background:#2a2114;color:#efe6d4;border-color:#3a2f1a}.agd-head p{color:#c9c0b0}}
	</style>
	<div class="agd-wrap">
		<div class="agd-head">
			<span class="agd-badge">⚡ Devis en 30 secondes · par l'IA</span>
			<h1>Ton devis, tout de suite</h1>
			<p>Décris ton projet — <strong>à la voix ou au clavier</strong>. Notre IA te propose le bon pack, une fourchette de prix et un délai. Sans rendez-vous, sans attente.</p>
		</div>

		<?php if ( ! $ready ) : ?>
			<p class="agd-note">🔧 L'outil est en cours d'activation. Reviens très bientôt !</p>
		<?php endif; ?>

		<div class="agd-box" <?php echo $ready ? '' : 'style="opacity:.5;pointer-events:none"'; ?>>
			<textarea id="agd-in" placeholder="Ex : Je suis coiffeur à Nantes, je veux un site avec prise de rendez-vous en ligne et mes photos…"></textarea>
			<div class="agd-row">
				<button class="agd-btn" id="agd-go">🧾 Obtenir mon devis</button>
				<button class="agd-mic" id="agd-mic" type="button" title="Dicter à la voix">🎤 Dicter</button>
			</div>
		</div>

		<div class="agd-status" id="agd-status"></div>

		<div class="agd-result" id="agd-result">
			<h2 id="agd-pack"></h2>
			<div class="agd-price" id="agd-price"></div>
			<div class="agd-delai" id="agd-delai"></div>
			<ul class="agd-postes" id="agd-postes"></ul>
			<div class="agd-mot" id="agd-mot"></div>
			<div class="agd-lead">
				<strong>Reçois ton devis détaillé :</strong>
				<input type="text" id="agd-name" placeholder="Ton prénom / entreprise" autocomplete="name">
				<input type="email" id="agd-email" placeholder="Ton email" autocomplete="email">
				<input type="tel" id="agd-phone" placeholder="Ton téléphone (optionnel)" autocomplete="tel">
				<button class="agd-btn" id="agd-send" style="width:100%;margin-top:8px">Envoyez-moi mon devis →</button>
				<div class="agd-ok" id="agd-ok"></div>
			</div>
		</div>
		<p class="agd-note">Estimation indicative générée par une IA. Le devis final est confirmé par un conseiller.</p>
	</div>

	<script>
	(function(){
		var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
		var go=document.getElementById('agd-go'), inp=document.getElementById('agd-in');
		var st=document.getElementById('agd-status'), res=document.getElementById('agd-result');
		var mic=document.getElementById('agd-mic'), curPack='';
		if(!go) return;
		function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
		// Dictée vocale (Web Speech API), si le navigateur la supporte.
		var SR=window.SpeechRecognition||window.webkitSpeechRecognition, rec=null;
		if(!SR){ mic.style.display='none'; }
		else{
			rec=new SR(); rec.lang='fr-FR'; rec.interimResults=true; rec.continuous=true;
			var base='';
			rec.onresult=function(e){var t='';for(var i=e.resultIndex;i<e.results.length;i++){t+=e.results[i][0].transcript;}inp.value=(base+' '+t).trim();};
			rec.onend=function(){mic.classList.remove('rec');mic.textContent='🎤 Dicter';};
			mic.addEventListener('click',function(){
				if(mic.classList.contains('rec')){rec.stop();return;}
				base=inp.value; mic.classList.add('rec'); mic.textContent='⏹ Stop'; try{rec.start();}catch(e){}
			});
		}
		var steps=['🤔 L\'IA analyse ton projet…','📐 Elle chiffre les postes…','🧾 Elle prépare ton devis…'], si=0, tmr=null;
		function tick(){ st.textContent=steps[si%steps.length]; si++; }
		go.addEventListener('click',function(){
			var b=inp.value.trim(); if(b.length<8){ st.textContent='Décris ton projet en quelques mots 🙂'; return; }
			go.disabled=true; res.style.display='none'; si=0; tick(); tmr=setInterval(tick,2000);
			var fd=new FormData(); fd.append('action','ag_devis_generate'); fd.append('_n',N); fd.append('besoin',b);
			fetch(AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
				clearInterval(tmr); go.disabled=false;
				if(!j||!j.success){ st.textContent='⚠️ '+((j&&j.data&&j.data.msg)||'Erreur.'); return; }
				st.textContent=''; var d=j.data; curPack=d.pack||'';
				document.getElementById('agd-pack').textContent='Pack conseillé : '+(d.pack||'');
				var pr=(d.prix_min&&d.prix_max&&d.prix_min!==d.prix_max)?(d.prix_min+' € – '+d.prix_max+' €'):((d.prix_max||d.prix_min||'')+' €');
				document.getElementById('agd-price').textContent=pr;
				document.getElementById('agd-delai').textContent='⏱ '+(d.delai||'')+(d.maintenance?(' · maintenance '+d.maintenance):'');
				var ul=document.getElementById('agd-postes'); ul.innerHTML='';
				(d.postes||[]).forEach(function(p){var li=document.createElement('li');li.innerHTML='<b>'+esc(p.poste)+'</b><span>'+esc(p.detail)+'</span>';ul.appendChild(li);});
				document.getElementById('agd-mot').textContent=d.mot||'';
				res.style.display='block'; res.scrollIntoView({behavior:'smooth',block:'start'});
			}).catch(function(){ clearInterval(tmr); go.disabled=false; st.textContent='⚠️ Connexion interrompue.'; });
		});
		var send=document.getElementById('agd-send'), ok=document.getElementById('agd-ok');
		send.addEventListener('click',function(){
			var name=document.getElementById('agd-name').value.trim();
			var email=document.getElementById('agd-email').value.trim();
			var phone=document.getElementById('agd-phone').value.trim();
			if(!name||(!email&&!phone)){ ok.style.display='block';ok.style.color='#ffb3b3';ok.textContent='Nom + email ou téléphone.'; return; }
			send.disabled=true;
			var fd=new FormData(); fd.append('action','ag_devis_lead'); fd.append('_n',N);
			fd.append('name',name); fd.append('email',email); fd.append('phone',phone); fd.append('pack',curPack);
			fetch(AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
				send.disabled=false; ok.style.display='block';
				if(j&&j.success){ ok.style.color='#7CFFB0'; ok.textContent=j.data.msg; send.style.display='none'; }
				else{ ok.style.color='#ffb3b3'; ok.textContent=(j&&j.data&&j.data.msg)||'Erreur.'; }
			}).catch(function(){ send.disabled=false; });
		});
	})();
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ag_devis_instant', 'ag_devis_render' );

/** Titre SEO. */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_page( 'devis-instant' ) ) {
		$parts['title'] = "Devis site internet instantané par l'IA — Alliance Groupe";
	}
	return $parts;
}, 20 );

/** Auto-création de la page /devis-instant. */
add_action( 'init', function () {
	if ( get_option( 'ag_devis_page_v1' ) ) { return; }
	if ( ! get_page_by_path( 'devis-instant' ) ) {
		$id = wp_insert_post( array(
			'post_title'   => 'Devis instantané',
			'post_name'    => 'devis-instant',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '[ag_devis_instant]',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$tpl = 'templates/page-devis-instant.php';
			if ( file_exists( get_stylesheet_directory() . '/' . $tpl ) ) {
				update_post_meta( $id, '_wp_page_template', $tpl );
			}
		}
	}
	update_option( 'ag_devis_page_v1', 1 );
} );
