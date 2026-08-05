<?php
/**
 * ag-concierge.php — Concierge IA (assistant flottant sur tout le site).
 *
 * Idée novatrice n°4 : un assistant IA disponible en bas à droite de chaque
 * page. Il répond aux questions, conseille la bonne offre, et — quand le
 * visiteur est chaud — capture le lead dans le CRM (via l'outil `capturer_lead`
 * en tool-use) + alerte l'équipe. C'est un vrai commercial IA 24/7.
 *
 * Réutilise ag-ia.php (clé `ag_ai_key`), ag_prospect_add_record, ag_push.
 * Ne s'affiche QUE si la clé est branchée et l'option `ag_concierge_on` active.
 * Anti-abus : quota par IP et par jour.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_CONCIERGE_MAX_JOUR' ) ) { define( 'AG_CONCIERGE_MAX_JOUR', 30 ); }

/** Le concierge est-il actif ? (clé présente + option + pas en admin). */
function ag_concierge_active() {
	if ( is_admin() ) { return false; }
	if ( ! function_exists( 'ag_ia_ready' ) || ! ag_ia_ready() ) { return false; }
	return '1' === (string) get_option( 'ag_concierge_on', '1' );
}

/** Consigne système du concierge (personnalité + offres réelles). */
function ag_concierge_system() {
	return "Tu es « Léa », la conseillère IA d'Alliance Groupe, une agence web française (Nantes) qui crée des sites et sécurise les entreprises. "
		. "Ton style : chaleureux, tutoiement, phrases COURTES, une émoji max de temps en temps. Tu réponds en français. "
		. "Ton but : comprendre le besoin du visiteur, le rassurer, et l'orienter. Nos offres réelles : "
		. "Site Express (packs 490 € / 890 € / 1490 € selon l'ambition), maintenance (29/59/99 €/mois), audit de sécurité, référencement Google. "
		. "N'invente JAMAIS d'autres prix ni de promesses. Si tu ne sais pas, propose d'être recontacté par un humain. "
		. "Dès que le visiteur montre de l'intérêt ET te donne son prénom + un email ou un téléphone, appelle l'outil capturer_lead pour que l'équipe le rappelle. "
		. "Ne redemande pas des infos déjà données. Après avoir capturé un lead, remercie chaleureusement et dis qu'on le recontacte vite.";
}

/** Définition de l'outil de capture de lead (tool-use). */
function ag_concierge_tool() {
	return array( array(
		'name'        => 'capturer_lead',
		'description' => 'Enregistre le contact d\'un visiteur intéressé pour qu\'un conseiller le rappelle. À appeler dès que tu as son prénom et un email OU un téléphone.',
		'input_schema' => array(
			'type'       => 'object',
			'properties' => array(
				'nom'       => array( 'type' => 'string', 'description' => 'Prénom ou nom de l\'entreprise du visiteur.' ),
				'email'     => array( 'type' => 'string', 'description' => 'Email si donné.' ),
				'telephone' => array( 'type' => 'string', 'description' => 'Téléphone si donné.' ),
				'besoin'    => array( 'type' => 'string', 'description' => 'Résumé en une phrase de ce que veut le visiteur.' ),
			),
			'required'   => array( 'nom' ),
		),
	) );
}

/** Enregistre le lead capturé par l'IA. */
function ag_concierge_save_lead( $input ) {
	$nom    = sanitize_text_field( (string) ( $input['nom'] ?? '' ) );
	$email  = sanitize_email( (string) ( $input['email'] ?? '' ) );
	$phone  = sanitize_text_field( (string) ( $input['telephone'] ?? '' ) );
	$besoin = sanitize_text_field( (string) ( $input['besoin'] ?? '' ) );
	if ( '' === $nom ) { return false; }
	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'   => $nom,
			'email'  => $email,
			'phone'  => $phone,
			'status' => 'interesse',
			'source' => 'concierge-ia',
			'notes'  => 'Lead capturé par le concierge IA. Besoin : ' . ( $besoin ? $besoin : '—' ),
		) );
	}
	if ( function_exists( 'ag_push' ) ) {
		ag_push( "💬 Concierge IA — nouveau lead : " . $nom . ' — ' . ( $email ? $email : $phone ) . ( $besoin ? ' · ' . $besoin : '' ) );
	}
	return true;
}

/* ── AJAX : un tour de conversation ──────────────────────────────────────── */
function ag_concierge_msg() {
	if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_concierge' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée.' ) );
	}
	if ( ! ag_ia_ready() ) {
		wp_send_json_error( array( 'msg' => 'Assistant indisponible pour le moment.' ) );
	}
	// Quota par IP / jour.
	$rlk = 'ag_conc_rl_' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? md5( (string) $_SERVER['REMOTE_ADDR'] ) : 'anon' );
	$n   = (int) get_transient( $rlk );
	if ( $n >= AG_CONCIERGE_MAX_JOUR ) {
		wp_send_json_error( array( 'msg' => 'On a beaucoup discuté aujourd\'hui 😊 Laisse-moi ton email, un humain te répondra.' ) );
	}
	set_transient( $rlk, $n + 1, DAY_IN_SECONDS );

	// Historique fourni par le client, borné et nettoyé.
	$raw = json_decode( (string) wp_unslash( $_POST['history'] ?? '[]' ), true );
	$msgs = array();
	if ( is_array( $raw ) ) {
		foreach ( array_slice( $raw, -12 ) as $m ) {
			$role = ( isset( $m['role'] ) && 'assistant' === $m['role'] ) ? 'assistant' : 'user';
			$txt  = sanitize_textarea_field( (string) ( $m['content'] ?? '' ) );
			if ( '' !== $txt ) {
				$msgs[] = array( 'role' => $role, 'content' => mb_substr( $txt, 0, 1500 ) );
			}
		}
	}
	if ( empty( $msgs ) ) {
		wp_send_json_error( array( 'msg' => 'Message vide.' ) );
	}

	$opts = array(
		'model'      => ag_ia_model( 'fast' ),
		'max_tokens' => 700,
		'temperature' => 0.6,
		'messages'   => $msgs,
		'tools'      => ag_concierge_tool(),
		'raw'        => true,
	);
	$resp = ag_ia_call( ag_concierge_system(), '', $opts );
	if ( is_wp_error( $resp ) ) {
		wp_send_json_error( array( 'msg' => 'Petit souci technique, réessaie.' ) );
	}

	$lead_saved = false;
	// Si l'IA appelle l'outil, on l'exécute puis on relance pour le message final.
	if ( isset( $resp['stop_reason'] ) && 'tool_use' === $resp['stop_reason'] ) {
		$tool_results = array();
		foreach ( (array) ( $resp['content'] ?? array() ) as $blk ) {
			if ( ( $blk['type'] ?? '' ) === 'tool_use' && ( $blk['name'] ?? '' ) === 'capturer_lead' ) {
				$ok = ag_concierge_save_lead( (array) ( $blk['input'] ?? array() ) );
				$lead_saved = $lead_saved || $ok;
				$tool_results[] = array(
					'type'        => 'tool_result',
					'tool_use_id' => $blk['id'] ?? '',
					'content'     => $ok ? 'Lead enregistré, un conseiller rappellera.' : 'Infos incomplètes.',
				);
			}
		}
		$msgs[] = array( 'role' => 'assistant', 'content' => $resp['content'] );
		$msgs[] = array( 'role' => 'user', 'content' => $tool_results );
		$opts2  = array( 'model' => ag_ia_model( 'fast' ), 'max_tokens' => 500, 'temperature' => 0.6, 'messages' => $msgs, 'tools' => ag_concierge_tool(), 'raw' => true );
		$resp   = ag_ia_call( ag_concierge_system(), '', $opts2 );
		if ( is_wp_error( $resp ) ) {
			wp_send_json_success( array( 'reply' => 'Merci ! Un conseiller te recontacte très vite 🙌', 'lead' => $lead_saved ) );
		}
	}

	$txt = '';
	foreach ( (array) ( $resp['content'] ?? array() ) as $blk ) {
		if ( ( $blk['type'] ?? '' ) === 'text' ) { $txt .= $blk['text']; }
	}
	if ( '' === trim( $txt ) ) { $txt = 'Je suis là pour t\'aider 🙂'; }
	wp_send_json_success( array( 'reply' => $txt, 'lead' => $lead_saved ) );
}
add_action( 'wp_ajax_ag_concierge_msg', 'ag_concierge_msg' );
add_action( 'wp_ajax_nopriv_ag_concierge_msg', 'ag_concierge_msg' );

/* ── Bulle flottante injectée en pied de page ────────────────────────────── */
add_action( 'wp_footer', function () {
	if ( ! ag_concierge_active() ) { return; }
	$nonce = wp_create_nonce( 'ag_concierge' );
	$ajax  = admin_url( 'admin-ajax.php' );
	?>
	<div id="ag-conc" aria-live="polite">
		<button id="ag-conc-btn" aria-label="Ouvrir l'assistant" title="Une question ? Léa, l'assistante IA">💬</button>
		<div id="ag-conc-box" role="dialog" aria-label="Assistant Alliance Groupe">
			<div class="ag-conc-head"><span>💬 Léa · assistante IA</span><button id="ag-conc-x" aria-label="Fermer">×</button></div>
			<div id="ag-conc-msgs"></div>
			<form id="ag-conc-form">
				<input id="ag-conc-in" type="text" placeholder="Pose ta question…" autocomplete="off" maxlength="600">
				<button type="submit" aria-label="Envoyer">➤</button>
			</form>
			<div class="ag-conc-foot">Réponses générées par une IA · Alliance Groupe</div>
		</div>
	</div>
	<style>
		#ag-conc-btn{position:fixed;right:18px;bottom:18px;z-index:99998;width:60px;height:60px;border-radius:50%;border:0;background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-size:26px;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.28)}
		#ag-conc-box{position:fixed;right:18px;bottom:88px;z-index:99999;width:min(370px,calc(100vw - 28px));height:min(520px,70vh);background:#fff;border-radius:18px;box-shadow:0 18px 50px rgba(0,0,0,.3);display:none;flex-direction:column;overflow:hidden}
		#ag-conc-box.on{display:flex}
		.ag-conc-head{background:#1a1205;color:#fdf6e6;padding:13px 16px;font-weight:700;display:flex;justify-content:space-between;align-items:center;font-size:.95rem}
		.ag-conc-head button{background:none;border:0;color:#fdf6e6;font-size:22px;cursor:pointer;line-height:1}
		#ag-conc-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:#faf6ee}
		.ag-conc-m{max-width:82%;padding:9px 13px;border-radius:14px;font-size:.92rem;line-height:1.4;white-space:pre-wrap}
		.ag-conc-m.u{align-self:flex-end;background:#c8962c;color:#1a1205;border-bottom-right-radius:4px}
		.ag-conc-m.a{align-self:flex-start;background:#fff;color:#2a2317;border:1px solid #ece3d2;border-bottom-left-radius:4px}
		.ag-conc-m.typing{color:#8a8272;font-style:italic}
		#ag-conc-form{display:flex;gap:6px;padding:10px;border-top:1px solid #eee;background:#fff}
		#ag-conc-in{flex:1;padding:11px 13px;border:1px solid #e2d8c4;border-radius:12px;font-size:.95rem}
		#ag-conc-form button{background:#c8962c;color:#1a1205;border:0;border-radius:12px;width:44px;font-size:1.05rem;cursor:pointer}
		.ag-conc-foot{font-size:.66rem;color:#a89f8e;text-align:center;padding:0 0 8px}
		@media (prefers-color-scheme:dark){#ag-conc-box{background:#1c160c}#ag-conc-msgs{background:#231b10}.ag-conc-m.a{background:#2a2114;color:#efe6d4;border-color:#3a2f1a}#ag-conc-form{background:#1c160c;border-color:#332a18}#ag-conc-in{background:#2a2114;color:#efe6d4;border-color:#3a2f1a}}
	</style>
	<script>
	(function(){
		var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
		var btn=document.getElementById('ag-conc-btn'), box=document.getElementById('ag-conc-box');
		var x=document.getElementById('ag-conc-x'), form=document.getElementById('ag-conc-form');
		var input=document.getElementById('ag-conc-in'), wrap=document.getElementById('ag-conc-msgs');
		var hist=[], started=false;
		function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
		function add(role,txt){var d=document.createElement('div');d.className='ag-conc-m '+(role==='user'?'u':'a');d.innerHTML=esc(txt);wrap.appendChild(d);wrap.scrollTop=wrap.scrollHeight;return d;}
		function open(){box.classList.add('on');if(!started){started=true;add('assistant','Bonjour 👋 Je suis Léa, l\'assistante IA d\'Alliance Groupe. Tu veux un site, le sécuriser, ou juste un conseil ?');input.focus();}}
		btn.addEventListener('click',function(){ box.classList.contains('on')?box.classList.remove('on'):open(); });
		x.addEventListener('click',function(){box.classList.remove('on');});
		form.addEventListener('submit',function(e){
			e.preventDefault();
			var t=input.value.trim(); if(!t) return;
			add('user',t); hist.push({role:'user',content:t}); input.value='';
			var typing=add('assistant','Léa écrit…'); typing.classList.add('typing');
			var fd=new FormData(); fd.append('action','ag_concierge_msg'); fd.append('_n',N); fd.append('history',JSON.stringify(hist));
			fetch(AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
				typing.remove();
				var msg=(j&&j.success)?j.data.reply:((j&&j.data&&j.data.msg)||'Réessaie dans un instant.');
				add('assistant',msg); hist.push({role:'assistant',content:msg});
			}).catch(function(){ typing.remove(); add('assistant','Connexion interrompue, réessaie.'); });
		});
	})();
	</script>
	<?php
}, 99 );

/* ── Réglage admin : activer/couper le concierge ─────────────────────────── */
add_action( 'admin_init', function () {
	register_setting( 'general', 'ag_concierge_on', array( 'type' => 'string', 'default' => '1', 'sanitize_callback' => function ( $v ) { return $v ? '1' : '0'; } ) );
	add_settings_field( 'ag_concierge_on', 'Concierge IA (assistant flottant)', function () {
		$on = '1' === (string) get_option( 'ag_concierge_on', '1' );
		echo '<label><input type="checkbox" name="ag_concierge_on" value="1" ' . checked( $on, true, false ) . '> Afficher l\'assistant IA en bas de chaque page';
		echo '</label><p class="description">Nécessite la clé API Claude (Réglages → Appels d\'offres). Sans clé, l\'assistant reste caché.</p>';
	}, 'general' );
} );
