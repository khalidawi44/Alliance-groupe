<?php
/**
 * Template Name: Prospection Mobile (app)
 *
 * Outil de prospection pensé pour le téléphone (iPhone/Android), installable
 * en icône « app » sur l'écran d'accueil (PWA). Réutilise le moteur CRM :
 *  - Pavé « Numéro rapide » : coller un numéro → 📞 Appel direct / 💬 SMS prêt / 🟢 WhatsApp.
 *  - Mes prospects : liste attribuée (ag_prospects_for_owner) avec Appel/SMS/WhatsApp + statut.
 *  - Raccourci vers l'Espace Ambassadeur (recherche Google, zones, classement).
 *
 * Réservé aux membres connectés (ambassadeur / admin).
 */

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/connexion' ) );
	exit;
}
$ag_u = wp_get_current_user();
if ( function_exists( 'ag_ensure_ambassador_for_user' ) ) {
	ag_ensure_ambassador_for_user( $ag_u );
}
$ag_email = $ag_u->user_email;

/* En-tête « app » (icône écran d'accueil iOS + plein écran). */
add_action( 'wp_head', function () {
	$icon = get_stylesheet_directory_uri() . '/assets/images/logo-carte-square.jpg';
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
	echo '<meta name="apple-mobile-web-app-title" content="Prospection">' . "\n";
	echo '<meta name="theme-color" content="#0b0b0f">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $icon ) . '">' . "\n";
}, 5 );

get_header();

$ag_sale_link = function_exists( 'ag_ambassadeur_sale_link' ) ? ag_ambassadeur_sale_link( $ag_email ) : home_url( '/sites-express' );
$ag_default_msg = "Bonjour, je suis de l'agence Alliance Groupe. On aide les entreprises à avoir un site web pro (et on en offre un chaque mois). Est-ce que je peux vous en dire plus ? " . $ag_sale_link;

$ag_my_prospects = function_exists( 'ag_prospects_for_owner' ) ? ag_prospects_for_owner( $ag_email ) : array();
$ag_pstat        = function_exists( 'ag_prospect_statuses' ) ? ag_prospect_statuses() : array();
$ag_ppost        = admin_url( 'admin-post.php' );
$ag_pnonce       = wp_nonce_field( 'ag_amb_prospect', '_n', true, false );
$ag_ajax_url     = admin_url( 'admin-ajax.php' );
$ag_ajax_nonce   = wp_create_nonce( 'ag_amb_prospect' );
?>
<style>
	#agpm { max-width:640px; margin:0 auto; padding:14px 12px 90px; }
	#agpm h1 { font-size:1.5rem; margin:6px 0 2px; text-align:center; }
	#agpm .agpm-sub { text-align:center; color:var(--color-text-soft,#9a9aa2); font-size:.9rem; margin-bottom:16px; }
	#agpm .agpm-card { background:rgba(255,255,255,.05); border:1px solid rgba(212,180,92,.28); border-radius:16px; padding:16px; margin-bottom:16px; }
	#agpm .agpm-card h2 { font-size:1.05rem; margin:0 0 12px; }
	#agpm input[type=tel], #agpm textarea { width:100%; box-sizing:border-box; background:rgba(0,0,0,.25); color:#fff; border:1px solid rgba(212,180,92,.35); border-radius:12px; padding:13px; font-size:1.05rem; }
	#agpm textarea { margin-top:10px; min-height:92px; resize:vertical; font-size:.95rem; }
	#agpm .agpm-actions { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-top:12px; }
	#agpm .agpm-btn { display:flex; align-items:center; justify-content:center; gap:6px; padding:15px 6px; border-radius:14px; font-weight:800; font-size:1rem; text-decoration:none; border:none; cursor:pointer; color:#0b0b0f; }
	#agpm .agpm-btn.call { background:#2ecc71; color:#062814; }
	#agpm .agpm-btn.sms  { background:#3aa3ff; color:#04203f; }
	#agpm .agpm-btn.wa   { background:#25d366; color:#062814; }
	#agpm .agpm-btn[disabled]{ opacity:.4; pointer-events:none; }
	#agpm .agpm-plist { list-style:none; margin:0; padding:0; }
	#agpm .agpm-p { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:14px; padding:12px; margin-bottom:10px; }
	#agpm .agpm-p .nm { font-weight:800; }
	#agpm .agpm-p .why { font-size:.82rem; color:var(--color-text-soft,#9a9aa2); margin:3px 0 9px; }
	#agpm .agpm-p .row { display:flex; flex-wrap:wrap; gap:7px; align-items:center; }
	#agpm .agpm-p a.mini, #agpm .agpm-p select { padding:9px 12px; border-radius:11px; font-weight:700; font-size:.9rem; text-decoration:none; border:1px solid rgba(212,180,92,.4); color:#fff; background:rgba(212,180,92,.12); }
	#agpm .agpm-p select { background:#15151b; }
	#agpm .agpm-hint { text-align:center; font-size:.82rem; color:var(--color-text-soft,#9a9aa2); background:rgba(58,163,255,.1); border:1px solid rgba(58,163,255,.3); border-radius:12px; padding:11px; }
	#agpm .agpm-big { display:block; text-align:center; background:linear-gradient(135deg,#d4b45c,#b98f2f); color:#0b0b0f; font-weight:800; padding:15px; border-radius:14px; text-decoration:none; }
	#agpm .agpm-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:14px; }
	#agpm .agpm-stat { text-decoration:none; color:#fff; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:14px; padding:12px 4px; text-align:center; display:flex; flex-direction:column; gap:2px; }
	#agpm .agpm-stat b { font-size:1.5rem; line-height:1; }
	#agpm .agpm-stat span { font-size:.66rem; color:var(--color-text-soft,#9a9aa2); }
	#agpm .agpm-stat.s-todo b { color:#ff6b6b; } #agpm .agpm-stat.s-done b { color:#3aa3ff; }
	#agpm .agpm-stat.s-hot b { color:#e6b35a; } #agpm .agpm-stat.s-cli b { color:#2ecc71; }
	#agpm .agpm-launch { display:grid; grid-template-columns:repeat(3,1fr); gap:9px; margin-bottom:18px; }
	#agpm .agpm-launch a { text-decoration:none; color:#fff; background:rgba(212,180,92,.1); border:1px solid rgba(212,180,92,.32); border-radius:15px; padding:14px 4px; text-align:center; font-weight:700; font-size:.8rem; display:flex; flex-direction:column; align-items:center; gap:6px; }
	#agpm .agpm-launch a span { font-size:1.6rem; line-height:1; }
</style>

<main id="agpm">

	<h1>🎯 Prospection</h1>
	<p class="agpm-sub">Appelle, envoie un SMS prêt, ou cherche des clients — direct depuis ton téléphone.</p>

	<!-- Tableau de bord -->
	<?php
	$ag_cnt = array( 'total' => 0, 'a_contacter' => 0, 'contacte' => 0, 'interesse' => 0, 'client' => 0 );
	foreach ( $ag_my_prospects as $ppc ) {
		$sc = $ppc['status'] ?? 'nouveau';
		if ( in_array( $sc, array( 'refus', 'ne_pas_contacter' ), true ) ) { continue; }
		$ag_cnt['total']++;
		if ( 'nouveau' === $sc ) { $ag_cnt['a_contacter']++; }
		elseif ( in_array( $sc, array( 'contacte', 'relance' ), true ) ) { $ag_cnt['contacte']++; }
		elseif ( 'interesse' === $sc ) { $ag_cnt['interesse']++; }
		elseif ( 'client' === $sc ) { $ag_cnt['client']++; }
	}
	?>
	<div class="agpm-stats">
		<a class="agpm-stat s-todo" href="#prospects"><b><?php echo (int) $ag_cnt['a_contacter']; ?></b><span>À contacter</span></a>
		<a class="agpm-stat s-done" href="#prospects"><b><?php echo (int) $ag_cnt['contacte']; ?></b><span>Contactés</span></a>
		<a class="agpm-stat s-hot"  href="#prospects"><b><?php echo (int) $ag_cnt['interesse']; ?></b><span>🔥 Intéressés</span></a>
		<a class="agpm-stat s-cli"  href="#prospects"><b><?php echo (int) $ag_cnt['client']; ?></b><span>✅ Clients</span></a>
	</div>
	<div class="agpm-launch">
		<a href="#numero"><span>📞</span>Numéro rapide</a>
		<a href="#prospects"><span>🎯</span>Mes prospects</a>
		<a href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>#chercher"><span>🔎</span>Chercher</a>
		<a href="https://calendar.google.com/" target="_blank" rel="noopener"><span>📅</span>Mon agenda</a>
		<a href="<?php echo esc_url( home_url( '/classement' ) ); ?>"><span>🏆</span>Classement</a>
		<a href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>"><span>🏠</span>Mon espace</a>
	</div>

	<!-- Pavé numéro rapide -->
	<div class="agpm-card" id="numero">
		<h2>📱 Numéro rapide</h2>
		<input type="tel" id="agpm-num" inputmode="tel" placeholder="Ex : 06 12 34 56 78" autocomplete="off">
		<textarea id="agpm-msg" placeholder="Message SMS / WhatsApp"><?php echo esc_textarea( $ag_default_msg ); ?></textarea>
		<div class="agpm-actions">
			<a href="#" id="agpm-call" class="agpm-btn call" disabled>📞 Appeler</a>
			<a href="#" id="agpm-sms"  class="agpm-btn sms"  disabled>💬 SMS</a>
			<a href="#" id="agpm-wa"   class="agpm-btn wa"   disabled>🟢 WhatsApp</a>
		</div>
	</div>

	<!-- Mes prospects -->
	<div class="agpm-card" id="prospects">
		<h2>Mes prospects à contacter (<?php echo (int) $ag_cnt['total']; ?>)</h2>
		<?php if ( ! $ag_my_prospects ) : ?>
			<p class="agpm-sub" style="margin:0;">Aucun prospect pour l'instant. Utilise la recherche dans l'Espace, ou tes prospects de zone arriveront automatiquement.</p>
		<?php else : ?>
			<ul class="agpm-plist">
			<?php foreach ( $ag_my_prospects as $pp ) :
				$pstatus = $pp['status'] ?? 'nouveau';
				if ( in_array( $pstatus, array( 'refus', 'ne_pas_contacter' ), true ) ) { continue; }
				$pid   = $pp['id'] ?? '';
				$pmsg  = function_exists( 'ag_prospect_message' ) ? ag_prospect_message( $pp, $ag_sale_link ) : $ag_default_msg;
				$pdig  = function_exists( 'ag_wa_number' ) ? ag_wa_number( $pp['phone'] ?? '', $pp['phone_intl'] ?? '' ) : preg_replace( '/[^0-9]/', '', $pp['phone'] ?? '' );
				$pwa   = $pdig ? 'https://wa.me/' . $pdig . '?text=' . rawurlencode( $pmsg ) : '';
				$psnum = preg_replace( '/[^0-9+]/', '', $pp['phone_intl'] ?? '' ) ?: preg_replace( '/[^0-9+]/', '', $pp['phone'] ?? '' );
				$psms  = $psnum ? 'sms:' . $psnum . '?body=' . rawurlencode( $pmsg ) : '';
				$ptel  = ! empty( $pp['phone'] ) ? 'tel:' . preg_replace( '/[^0-9+]/', '', $pp['phone'] ) : '';
			?>
				<li class="agpm-p">
					<div class="nm"><?php echo esc_html( $pp['name'] ?? '' ); ?></div>
					<div class="why"><?php echo esc_html( ( ! empty( $pp['city'] ) ? $pp['city'] . ' · ' : '' ) . ( function_exists( 'ag_prospect_why' ) ? ag_prospect_why( $pp ) : '' ) ); ?></div>
					<div class="row">
						<?php if ( $ptel ) : ?><a class="mini ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="Appel" href="<?php echo esc_attr( $ptel ); ?>">📞 Appeler</a><?php endif; ?>
						<?php if ( $psms ) : ?><a class="mini ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="SMS" href="<?php echo esc_attr( $psms ); ?>">💬 SMS</a><?php endif; ?>
						<?php if ( $pwa ) : ?><a class="mini ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="WhatsApp" href="<?php echo esc_url( $pwa ); ?>" target="_blank" rel="noopener">🟢 WhatsApp</a><?php endif; ?>
						<form method="post" action="<?php echo esc_url( $ag_ppost ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="ag_amb_prospect_status">
							<?php echo $ag_pnonce; // phpcs:ignore ?>
							<input type="hidden" name="id" value="<?php echo esc_attr( $pid ); ?>">
							<select name="status" onchange="this.form.submit()">
								<?php foreach ( $ag_pstat as $sk => $sl ) : ?><option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $pstatus, $sk ); ?>><?php echo esc_html( $sl ); ?></option><?php endforeach; ?>
							</select>
						</form>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<!-- Recherche : renvoi vers l'espace complet -->
	<div class="agpm-card">
		<h2>🔎 Chercher de nouveaux prospects</h2>
		<p class="agpm-sub" style="margin:0 0 12px;">Recherche Google par métier + ville, zones, classement et outils complets dans ton Espace.</p>
		<a class="agpm-big" href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>#chercher">Ouvrir l'Espace Ambassadeur →</a>
	</div>

	<p class="agpm-hint">💡 <strong>Installer en app sur ton iPhone :</strong> ouvre cette page dans <strong>Safari</strong> → bouton <strong>Partager</strong> (carré avec flèche ↑) → <strong>« Sur l'écran d'accueil »</strong>. Une icône « Prospection » apparaît, en plein écran comme une vraie app.</p>

</main>

<script>
(function(){
	var num = document.getElementById('agpm-num'),
	    msg = document.getElementById('agpm-msg'),
	    bCall = document.getElementById('agpm-call'),
	    bSms  = document.getElementById('agpm-sms'),
	    bWa   = document.getElementById('agpm-wa');

	function telNum(v){ return (v||'').replace(/[^0-9+]/g,''); }
	function waNum(v){ var n=(v||'').replace(/[^0-9]/g,''); if(n.charAt(0)==='0'){ n='33'+n.substring(1); } return n; }
	function refresh(){
		var t=telNum(num.value), ok=t.replace(/\D/g,'').length>=6, m=encodeURIComponent(msg.value||'');
		[bCall,bSms,bWa].forEach(function(b){ if(ok){ b.removeAttribute('disabled'); } else { b.setAttribute('disabled','disabled'); } });
		if(!ok){ return; }
		bCall.setAttribute('href','tel:'+t);
		bSms.setAttribute('href','sms:'+t+'?body='+m);
		bWa.setAttribute('href','https://wa.me/'+waNum(num.value)+'?text='+m);
	}
	num.addEventListener('input', refresh);
	msg.addEventListener('input', refresh);
	refresh();

	// Note automatique du contact (réutilise l'AJAX de l'espace).
	var AJAX='<?php echo esc_js( $ag_ajax_url ); ?>', N='<?php echo esc_js( $ag_ajax_nonce ); ?>';
	document.querySelectorAll('.ag-amb-touch').forEach(function(a){
		a.addEventListener('click', function(){
			var id=a.getAttribute('data-id'), ch=a.getAttribute('data-channel');
			if(!id){ return; }
			var fd=new FormData(); fd.append('action','ag_amb_touch'); fd.append('_n',N); fd.append('id',id); fd.append('channel',ch);
			fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).catch(function(){});
		});
	});
})();
</script>

<?php
get_footer();
