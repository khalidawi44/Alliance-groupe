<?php
/**
 * Template Name: Prospection Mobile (app)
 *
 * Application autonome (indépendante du thème WordPress, aucun lien vers wp-admin
 * ni vers les pages WordPress). Onglets : Accueil / Prospecter / Chercher / Audit.
 * Outils : numéro rapide (appel/SMS/WhatsApp + APPEL ROBOT), mes prospects,
 * recherche Google intégrée, audits. Pull-to-refresh + bouton remonter. PWA iPhone.
 *
 * Réservé aux membres connectés (ambassadeur / admin).
 */

if ( ! is_user_logged_in() ) {
	$ag_back = add_query_arg( 'redirect_to', rawurlencode( home_url( '/prospection-mobile' ) ), home_url( '/connexion' ) );
	wp_safe_redirect( $ag_back );
	exit;
}
$ag_u = wp_get_current_user();
if ( function_exists( 'ag_ensure_ambassador_for_user' ) ) {
	ag_ensure_ambassador_for_user( $ag_u );
}
$ag_email  = $ag_u->user_email;
$ag_prenom = $ag_u->first_name ? $ag_u->first_name : ( $ag_u->display_name ? $ag_u->display_name : 'toi' );
$ag_icon   = get_stylesheet_directory_uri() . '/assets/images/logo-carte-square.jpg';
$ag_robot_ok = function_exists( 'ag_voice_ready' ) && ag_voice_ready();

$ag_sale_link   = function_exists( 'ag_ambassadeur_sale_link' ) ? ag_ambassadeur_sale_link( $ag_email ) : home_url( '/sites-express' );
$ag_default_msg = "Bonjour, je suis de l'agence Alliance Groupe. On aide les entreprises à avoir un site web pro (et on en offre un chaque mois). Est-ce que je peux vous en dire plus ? " . $ag_sale_link;

$ag_my_prospects = function_exists( 'ag_prospects_for_owner' ) ? ag_prospects_for_owner( $ag_email ) : array();
$ag_pstat        = function_exists( 'ag_prospect_statuses' ) ? ag_prospect_statuses() : array();
$ag_ppost        = admin_url( 'admin-post.php' );
$ag_pnonce       = wp_nonce_field( 'ag_amb_prospect', '_n', true, false );
$ag_ajax_url     = admin_url( 'admin-ajax.php' );
$ag_ajax_nonce   = wp_create_nonce( 'ag_amb_prospect' );

$ag_is_admin = current_user_can( 'manage_options' );
$ag_calls    = $ag_is_admin ? array_slice( (array) get_option( 'ag_voice_log', array() ), 0, 40 ) : array();
$ag_audits   = array_slice( (array) get_option( 'ag_app_audits', array() ), 0, 40, true );
$ag_ambs     = array();
$ag_bulklog  = array();
if ( $ag_is_admin ) {
	foreach ( get_users( array( 'meta_key' => 'ag_amb_phone', 'number' => 300 ) ) as $uu ) {
		$ph = get_user_meta( $uu->ID, 'ag_amb_phone', true );
		if ( $ph ) { $ag_ambs[] = array( 'name' => $uu->display_name, 'phone' => (string) $ph ); }
	}
	$ag_bulklog = array_slice( (array) get_option( 'ag_bulk_sms_log', array() ), 0, 20 );
}

$ag_cnt = array( 'total' => 0, 'a_contacter' => 0, 'contacte' => 0, 'repondeur' => 0, 'interesse' => 0, 'client' => 0 );
foreach ( $ag_my_prospects as $ppc ) {
	$sc = $ppc['status'] ?? 'nouveau';
	if ( in_array( $sc, array( 'refus', 'ne_pas_contacter' ), true ) ) { continue; }
	$ag_cnt['total']++;
	if ( 'nouveau' === $sc ) { $ag_cnt['a_contacter']++; }
	elseif ( in_array( $sc, array( 'contacte', 'relance' ), true ) ) { $ag_cnt['contacte']++; }
	elseif ( 'repondeur' === $sc ) { $ag_cnt['repondeur']++; }
	elseif ( 'interesse' === $sc ) { $ag_cnt['interesse']++; }
	elseif ( 'client' === $sc ) { $ag_cnt['client']++; }
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="Alliance Amb">
	<meta name="theme-color" content="#0b0b0f">
	<link rel="apple-touch-icon" href="<?php echo esc_url( $ag_icon ); ?>">
	<title>Alliance Groupe — Ambassadeur</title>
	<style>
		:root{ --gold:#d4b45c; --bg:#0b0b0f; --card:#16161d; --line:rgba(255,255,255,.09); --soft:#9a9aa2; }
		*{ box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
		html,body{ margin:0; padding:0; background:var(--bg); color:#fff; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
		a{ color:inherit; text-decoration:none; }
		#ptr{ height:0; overflow:hidden; display:flex; align-items:flex-end; justify-content:center; color:var(--soft); font-size:.85rem; transition:height .15s,opacity .15s; opacity:0; padding-bottom:6px; }
		#ptr.ready{ color:var(--gold); }
		.appbar{ position:sticky; top:0; z-index:20; display:flex; align-items:center; gap:10px; padding:calc(env(safe-area-inset-top) + 10px) 16px 10px; background:rgba(11,11,15,.86); backdrop-filter:blur(12px); border-bottom:1px solid var(--line); }
		.appbar img{ width:30px; height:30px; border-radius:8px; }
		.appbar b{ font-size:1.05rem; letter-spacing:.3px; } .appbar b span{ color:var(--gold); }
		.wrap{ max-width:680px; margin:0 auto; padding:16px 14px 100px; }
		.view{ display:none; } .view.on{ display:block; animation:fade .25s ease; }
		@keyframes fade{ from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }
		h2.sec{ font-size:1.15rem; margin:2px 0 12px; }
		.hi{ font-size:1.35rem; font-weight:800; margin:4px 0 2px; } .hi span{ color:var(--gold); }
		.sub{ color:var(--soft); font-size:.9rem; margin:0 0 16px; }
		.stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:9px; margin-bottom:16px; }
		.stat{ background:var(--card); border:1px solid var(--line); border-radius:16px; padding:13px 6px; text-align:center; }
		.stat b{ display:block; font-size:1.6rem; line-height:1; } .stat span{ font-size:.66rem; color:var(--soft); }
		.stat.todo b{color:#ff6b6b} .stat.done b{color:#3aa3ff} .stat.rep b{color:#c58bff} .stat.hot b{color:#e6b35a} .stat.cli b{color:#2ecc71}
		.launch{ display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
		.launch a{ background:rgba(212,180,92,.09); border:1px solid rgba(212,180,92,.28); border-radius:16px; padding:16px 6px; text-align:center; font-weight:700; font-size:.9rem; display:flex; flex-direction:column; align-items:center; gap:7px; }
		.launch a i{ font-size:1.8rem; font-style:normal; }
		.card{ background:var(--card); border:1px solid var(--line); border-radius:18px; padding:16px; margin-bottom:14px; }
		.card h3{ margin:0 0 12px; font-size:1.02rem; }
		input,textarea,select{ width:100%; background:rgba(0,0,0,.3); color:#fff; border:1px solid rgba(212,180,92,.32); border-radius:13px; padding:13px; font-size:1.02rem; }
		textarea{ margin-top:10px; min-height:84px; resize:vertical; font-size:.95rem; }
		.acts{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-top:12px; }
		.b{ display:flex; align-items:center; justify-content:center; gap:5px; padding:15px 4px; border-radius:14px; font-weight:800; font-size:.96rem; border:none; cursor:pointer; color:#fff; }
		.b.call{ background:#2ecc71; color:#062814; } .b.sms{ background:#3aa3ff; color:#04203f; } .b.wa{ background:#25d366; color:#062814; }
		.b[disabled]{ opacity:.38; pointer-events:none; }
		.b.gold{ background:linear-gradient(135deg,#d4b45c,#b98f2f); color:#0b0b0f; width:100%; }
		.b.robot{ background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff; }
		.p{ background:rgba(255,255,255,.03); border:1px solid var(--line); border-radius:14px; padding:12px; margin-bottom:10px; }
		.p .nm{ font-weight:800; } .p .why{ font-size:.8rem; color:var(--soft); margin:3px 0 9px; }
		.p .row{ display:flex; flex-wrap:wrap; gap:7px; align-items:center; }
		.p a.mini,.p select{ padding:9px 11px; border-radius:11px; font-weight:700; font-size:.88rem; border:1px solid rgba(212,180,92,.4); color:#fff; background:rgba(212,180,92,.12); width:auto; }
		.p select{ background:#15151b; }
		.mini.robot{ border-color:#a855f7; color:#c58bff; background:rgba(124,58,237,.14); }
		.chips{ display:flex; flex-wrap:wrap; gap:7px; margin:0 0 14px; }
		.chips .chip{ border:1px solid var(--line); background:rgba(255,255,255,.04); color:var(--soft); border-radius:100px; padding:7px 13px; font-size:.8rem; font-weight:700; cursor:pointer; }
		.chips .chip.on{ background:var(--gold); color:#0b0b0f; border-color:var(--gold); }
		.link{ display:flex; align-items:center; gap:12px; background:var(--card); border:1px solid var(--line); border-radius:14px; padding:15px; margin-bottom:10px; font-weight:600; }
		.link i{ font-style:normal; font-size:1.4rem; } .link b{ display:block; } .link .arr{ margin-left:auto; color:var(--soft); }
		.hint{ text-align:center; font-size:.8rem; color:var(--soft); background:rgba(58,163,255,.09); border:1px solid rgba(58,163,255,.28); border-radius:13px; padding:12px; margin-top:6px; }
		.grev{ font-size:.8rem; color:var(--soft); margin-top:9px; } .grev span{ font-size:.74rem; } .grev a{ color:var(--gold); font-weight:600; }
		.audit-wrap{ margin-top:8px; border-top:1px dashed var(--line); padding-top:9px; }
		.audit-res{ margin-top:2px; } .audit-res .sc{ font-weight:800; }
		.ag-audit{ cursor:pointer; }
		.res{ background:rgba(255,255,255,.03); border:1px solid var(--line); border-radius:13px; padding:11px; margin-bottom:9px; }
		.res .rn{ font-weight:700; } .res .rk{ font-size:.78rem; color:var(--soft); margin:2px 0 8px; }
		.tabbar{ position:fixed; left:0; right:0; bottom:0; z-index:30; display:flex; background:rgba(16,16,22,.94); backdrop-filter:blur(12px); border-top:1px solid var(--line); padding-bottom:env(safe-area-inset-bottom); }
		.tabbar a{ flex:1; text-align:center; padding:9px 2px 8px; color:var(--soft); font-size:.66rem; font-weight:600; }
		.tabbar a i{ display:block; font-size:1.35rem; font-style:normal; margin-bottom:2px; filter:grayscale(1) opacity(.7); }
		.tabbar a.active{ color:var(--gold); } .tabbar a.active i{ filter:none; }
		#toTop{ position:fixed; right:16px; bottom:calc(70px + env(safe-area-inset-bottom)); z-index:25; width:46px; height:46px; border-radius:50%; background:var(--gold); color:#0b0b0f; border:none; font-size:1.3rem; display:none; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(0,0,0,.4); }
		#toast{ position:fixed; left:50%; transform:translateX(-50%); bottom:calc(84px + env(safe-area-inset-bottom)); z-index:40; background:#222; color:#fff; padding:11px 16px; border-radius:12px; font-size:.9rem; opacity:0; transition:opacity .2s; pointer-events:none; max-width:90%; text-align:center; }
		#toast.on{ opacity:1; }
		.logout{ text-align:center; color:var(--soft); font-size:.85rem; margin:22px 0 0; }
	</style>
</head>
<body>
<div id="ptr">↓ Tire pour rafraîchir</div>

<header class="appbar">
	<img src="<?php echo esc_url( $ag_icon ); ?>" alt="">
	<b>Alliance <span>Ambassadeur</span></b>
</header>

<div class="wrap">

	<!-- ACCUEIL -->
	<section class="view on" id="view-accueil">
		<p class="hi">Salut <span><?php echo esc_html( $ag_prenom ); ?></span> 👋</p>
		<p class="sub">Ton QG de prospection.</p>
		<div class="stats">
			<a class="stat todo" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['a_contacter']; ?></b><span>À contacter</span></a>
			<a class="stat done" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['contacte']; ?></b><span>Contactés</span></a>
			<a class="stat rep" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['repondeur']; ?></b><span>📵 Répondeur</span></a>
			<a class="stat hot" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['interesse']; ?></b><span>🔥 Intéressés</span></a>
			<a class="stat cli" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['client']; ?></b><span>✅ Clients</span></a>
			<a class="stat" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['total']; ?></b><span>Total actifs</span></a>
		</div>
		<div class="launch">
			<a href="#" onclick="AG.tab('prospecter');return false"><i>📞</i>Numéro rapide</a>
			<a href="#" onclick="AG.tab('prospecter');return false"><i>🎯</i>Mes prospects</a>
			<a href="#" onclick="AG.tab('chercher');return false"><i>🔎</i>Chercher</a>
			<a href="#" onclick="AG.tab('audit');return false"><i>🛡️</i>Audit</a>
		</div>
		<div class="card" style="margin-top:14px;">
			<h3>🔳 Générateur de QR code</h3>
			<p class="sub" style="margin:-4px 0 10px;">Ton lien de vente, un réseau, un numéro… → QR à imprimer/partager (flyer, carte, vitrine).</p>
			<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:9px;">
				<button type="button" class="mini qr-preset" data-v="<?php echo esc_attr( $ag_sale_link ); ?>" style="cursor:pointer;">🔗 Mon lien</button>
				<button type="button" class="mini qr-preset" data-v="https://wa.me/33744829516" style="cursor:pointer;">🟢 WhatsApp</button>
				<button type="button" class="mini qr-preset" data-v="https://www.instagram.com/" style="cursor:pointer;">📸 Insta</button>
				<button type="button" class="mini qr-preset" data-v="https://www.snapchat.com/add/" style="cursor:pointer;">👻 Snap</button>
				<button type="button" class="mini qr-preset" data-v="https://www.tiktok.com/@" style="cursor:pointer;">🎵 TikTok</button>
			</div>
			<input type="text" id="ag-qr-in" value="<?php echo esc_attr( $ag_sale_link ); ?>" placeholder="Lien ou texte">
			<div style="text-align:center;margin-top:12px;"><img id="ag-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=<?php echo rawurlencode( $ag_sale_link ); ?>" alt="QR" style="width:200px;height:200px;background:#fff;border-radius:14px;padding:8px;"></div>
			<a id="ag-qr-open" class="b gold" style="margin-top:12px;" target="_blank" rel="noopener" href="https://api.qrserver.com/v1/create-qr-code/?size=800x800&margin=20&data=<?php echo rawurlencode( $ag_sale_link ); ?>">⬇️ Ouvrir en grand (appui long → Enregistrer l'image)</a>
			<a class="link" style="margin-top:10px;" href="<?php echo esc_url( home_url( '/studio' ) ); ?>"><i>🎨</i><span><b>Studio créatif</b><span>Vidéos &amp; visuels de pub</span></span><span class="arr">→</span></a>
		</div>
		<p class="logout"><a href="<?php echo esc_url( wp_logout_url( home_url( '/connexion' ) ) ); ?>">🚪 Se déconnecter</a></p>
		<p class="hint">💡 Installer l'app : Safari → Partager ↑ → « Sur l'écran d'accueil ».</p>
	</section>

	<!-- PROSPECTER -->
	<section class="view" id="view-prospecter">
		<h2 class="sec">🎯 Prospecter</h2>
		<div class="card">
			<h3>📱 Numéro rapide</h3>
			<input type="tel" id="ag-num" inputmode="tel" placeholder="Ex : 06 12 34 56 78" autocomplete="off">
			<textarea id="ag-msg"><?php echo esc_textarea( $ag_default_msg ); ?></textarea>
			<div class="acts">
				<a href="#" id="ag-call" class="b call" disabled>📞 Appeler</a>
				<a href="#" id="ag-sms" class="b sms" disabled>💬 SMS</a>
				<a href="#" id="ag-wa" class="b wa" disabled>🟢 WA</a>
			</div>
			<?php if ( $ag_robot_ok ) : ?>
			<div style="display:flex; gap:8px; margin-top:10px;">
				<select id="ag-angle" style="flex:1;"><option value="creation">🌐 Angle site</option><option value="securite">🛡️ Angle sécurité</option></select>
				<button type="button" id="ag-robot" class="b robot" style="flex:1;" disabled>🤖 Appel robot</button>
			</div>
			<p class="sub" style="margin:8px 0 0; font-size:.78rem;">Le robot Emma appelle ce numéro à ta place et te renvoie le résultat.</p>
			<?php endif; ?>
		</div>
		<div class="card">
			<h3>Mes prospects (<?php echo (int) $ag_cnt['total']; ?>)</h3>
			<?php if ( ! $ag_my_prospects ) : ?>
				<p class="sub" style="margin:0;">Aucun prospect. Va dans « Chercher » ou attends tes prospects de zone.</p>
			<?php else : ?>
				<div class="chips" id="ag-chips">
					<button class="chip on" data-f="grp" data-v="all">Tous</button>
					<button class="chip" data-f="grp" data-v="todo">🆕 À contacter</button>
					<button class="chip" data-f="grp" data-v="done">📞 Contactés</button>
					<button class="chip" data-f="grp" data-v="rappel">📵 À rappeler</button>
					<button class="chip" data-f="grp" data-v="hot">🔥 Intéressés</button>
					<button class="chip" data-f="kind" data-v="site">🔒 Sécurité (site)</button>
					<button class="chip" data-f="kind" data-v="crea">🌐 Site web</button>
				</div>
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
					$pnum  = preg_replace( '/[^0-9+]/', '', $pp['phone_intl'] ?? ( $pp['phone'] ?? '' ) );
					$has_site = '' !== trim( (string) ( $pp['website'] ?? '' ) );
					$pangle   = $has_site ? 'securite' : 'creation';
					$fgrp     = ( 'nouveau' === $pstatus ) ? 'todo' : ( in_array( $pstatus, array( 'contacte', 'relance' ), true ) ? 'done' : ( 'repondeur' === $pstatus ? 'rappel' : ( 'interesse' === $pstatus ? 'hot' : 'done' ) ) );
					$fkind    = $has_site ? 'site' : 'crea';
				?>
					<div class="p" data-grp="<?php echo esc_attr( $fgrp ); ?>" data-kind="<?php echo esc_attr( $fkind ); ?>">
						<div class="nm"><?php echo esc_html( $pp['name'] ?? '' ); ?></div>
						<div class="why"><?php echo esc_html( ( ! empty( $pp['city'] ) ? $pp['city'] . ' · ' : '' ) . ( function_exists( 'ag_prospect_why' ) ? ag_prospect_why( $pp ) : '' ) ); ?></div>
						<div class="row">
							<?php if ( $ptel ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="Appel" href="<?php echo esc_attr( $ptel ); ?>">📞</a><?php endif; ?>
							<?php if ( $psms ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="SMS" href="<?php echo esc_attr( $psms ); ?>">💬</a><?php endif; ?>
							<?php if ( $pwa ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="WhatsApp" href="<?php echo esc_url( $pwa ); ?>" target="_blank" rel="noopener">🟢</a><?php endif; ?>
							<?php if ( $ag_robot_ok && $pnum ) : ?><a class="mini robot ag-robot-one" href="#" data-phone="<?php echo esc_attr( $pnum ); ?>" data-name="<?php echo esc_attr( $pp['name'] ?? '' ); ?>" data-angle="<?php echo esc_attr( $pangle ); ?>">🤖 Robot <?php echo 'securite' === $pangle ? '🔒' : '🌐'; ?></a><?php endif; ?>
							<form method="post" action="<?php echo esc_url( $ag_ppost ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_amb_prospect_status">
								<?php echo $ag_pnonce; // phpcs:ignore ?>
								<input type="hidden" name="id" value="<?php echo esc_attr( $pid ); ?>">
								<select name="status" onchange="this.form.submit()">
									<?php foreach ( $ag_pstat as $sk => $sl ) : ?><option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $pstatus, $sk ); ?>><?php echo esc_html( $sl ); ?></option><?php endforeach; ?>
								</select>
							</form>
							<button type="button" class="mini ag-del" data-id="<?php echo esc_attr( $pid ); ?>" data-name="<?php echo esc_attr( $pp['name'] ?? '' ); ?>" style="cursor:pointer;border-color:#e5484d;color:#ff8a8a;">🗑️</button>
						</div>
						<?php
						$pweb  = $pp['website'] ?? '';
						$pmaps = ! empty( $pp['maps_uri'] ) ? $pp['maps_uri'] : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( ( $pp['name'] ?? '' ) . ' ' . ( $pp['city'] ?? '' ) ) );
						$prat  = (float) ( $pp['rating'] ?? 0 );
						$prev  = (int) ( $pp['reviews'] ?? 0 );
						$pas   = isset( $pp['audit_score'] ) ? (int) $pp['audit_score'] : -1;
						?>
						<div class="grev">
							<?php if ( $prat > 0 ) : ?>⭐ <?php echo esc_html( number_format_i18n( $prat, 1 ) ); ?> <span>(<?php echo (int) $prev; ?> avis)</span> · <?php endif; ?>
							<a href="<?php echo esc_url( $pmaps ); ?>" target="_blank" rel="noopener">📍 Voir sur Google</a>
						</div>
						<?php if ( $pweb ) : ?>
						<div class="audit-wrap" data-url="<?php echo esc_attr( $pweb ); ?>" data-id="<?php echo esc_attr( $pid ); ?>" data-name="<?php echo esc_attr( $pp['name'] ?? '' ); ?>" data-num="<?php echo esc_attr( $psnum ); ?>">
							<div class="row" style="margin-top:2px;">
								<button type="button" class="mini ag-audit" data-mode="complet">🛡️ Auditer le site</button>
								<?php if ( $pas >= 0 ) : ?><span class="sub" style="font-size:.76rem;margin:0;">Note : <?php echo (int) $pas; ?>/100</span><?php endif; ?>
							</div>
							<div class="audit-res"></div>
						</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>

	<!-- CHERCHER -->
	<section class="view" id="view-chercher">
		<h2 class="sec">🔎 Chercher des prospects</h2>
		<div class="card">
			<h3>Recherche par ville</h3>
			<input type="text" id="ag-city" placeholder="Ex : Nantes, ou 'restaurant Nantes'" autocomplete="off">
			<button type="button" id="ag-search" class="b gold" style="margin-top:12px;">🔎 Lancer la recherche</button>
			<p class="sub" style="margin:8px 0 0; font-size:.78rem;">Trouve les entreprises du secteur et ajoute-les à tes prospects.</p>
		</div>
		<div id="ag-results"></div>
	</section>

	<?php if ( $ag_is_admin ) : ?>
	<!-- APPELS (journal du robot) -->
	<section class="view" id="view-appels">
		<h2 class="sec">📞 Appels du robot</h2>
		<p class="sub">Tous les appels d'Emma (intéressés, répondeur, refus…) avec la retranscription.</p>
		<?php if ( empty( $ag_calls ) ) : ?>
			<div class="card"><p class="sub" style="margin:0;">Aucun appel enregistré pour l'instant. Dès qu'Emma appelle (et que Retell renvoie l'analyse), ça s'affiche ici.</p></div>
		<?php else : ?>
			<?php foreach ( $ag_calls as $e ) :
				$lbl   = function_exists( 'ag_voice_status_label' ) ? ag_voice_status_label( $e['status'] ?? '' ) : ( $e['status'] ?? '' );
				$when  = ! empty( $e['ts'] ) ? date_i18n( 'd/m à H\hi', (int) $e['ts'] ) : '';
				$who   = ! empty( $e['name'] ) ? $e['name'] : ( $e['phone'] ?? '' );
				$trans = trim( (string) ( $e['transcript'] ?? '' ) ); if ( '' === $trans ) { $trans = trim( (string) ( $e['summary'] ?? '' ) ); }
			?>
				<div class="card" style="padding:13px;">
					<div style="display:flex;justify-content:space-between;gap:8px;align-items:baseline;">
						<strong><?php echo esc_html( $who ); ?></strong><span class="sub" style="margin:0;font-size:.78rem;"><?php echo esc_html( $when ); ?></span>
					</div>
					<div style="margin:5px 0 2px;"><span style="display:inline-block;background:rgba(212,180,92,.15);border:1px solid rgba(212,180,92,.35);border-radius:100px;padding:2px 10px;font-size:.78rem;font-weight:700;"><?php echo esc_html( $lbl ); ?></span> <span class="sub" style="font-size:.76rem;">📞 <?php echo esc_html( $e['phone'] ?? '' ); ?><?php echo ! empty( $e['rappel'] ) ? ' · 🗓️ ' . esc_html( $e['rappel'] ) : ''; ?></span></div>
					<?php if ( '' !== $trans ) : ?>
						<details style="margin-top:6px;"><summary style="cursor:pointer;color:var(--gold);font-size:.85rem;font-weight:600;">📝 Retranscription</summary><div style="white-space:pre-wrap;background:rgba(0,0,0,.25);border-radius:9px;padding:9px;margin-top:6px;font-size:.82rem;line-height:1.5;color:#d6d6db;"><?php echo esc_html( $trans ); ?></div></details>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<?php if ( $ag_is_admin ) : ?>
	<!-- AMBASSADEURS (SMS en masse + suivi) -->
	<section class="view" id="view-ambass">
		<h2 class="sec">🤝 Ambassadeurs — SMS en masse</h2>
		<div class="card">
			<h3>Nouvel envoi</h3>
			<p class="sub" style="margin:-4px 0 10px;">Colle jusqu'à 100 numéros (un par ligne, ou séparés par virgule/espace).</p>
			<?php if ( $ag_ambs ) : ?><button type="button" id="ag-fill-ambs" class="mini" style="cursor:pointer;margin-bottom:9px;">➕ Remplir avec mes ambassadeurs (<?php echo count( $ag_ambs ); ?>)</button><?php endif; ?>
			<textarea id="ag-bulk-nums" placeholder="0612345678&#10;0798765432&#10;..." style="min-height:110px;"></textarea>
			<textarea id="ag-bulk-msg" placeholder="Ton message aux ambassadeurs..." style="min-height:82px;">Salut ! Nouvelle campagne Alliance Groupe : de nouveaux prospects à contacter t'attendent dans ton espace. On compte sur toi 💪</textarea>
			<button type="button" id="ag-bulk-send" class="b gold" style="margin-top:10px;">📨 Envoyer en masse</button>
			<div id="ag-bulk-res" class="sub" style="margin-top:8px;"></div>
		</div>
		<h3 style="margin:6px 2px 10px;font-size:1.02rem;">Suivi des envois</h3>
		<?php if ( empty( $ag_bulklog ) ) : ?>
			<div class="card"><p class="sub" style="margin:0;">Aucun envoi pour l'instant.</p></div>
		<?php else : foreach ( $ag_bulklog as $c ) : ?>
			<div class="card" style="padding:13px;">
				<div style="display:flex;justify-content:space-between;align-items:baseline;"><strong><?php echo (int) $c['total']; ?> SMS</strong><span class="sub" style="margin:0;font-size:.76rem;"><?php echo esc_html( date_i18n( 'd/m à H\hi', (int) ( $c['ts'] ?? 0 ) ) ); ?></span></div>
				<div style="margin:4px 0;font-size:.85rem;"><span style="color:#2ecc71;">✅ <?php echo (int) $c['ok']; ?> envoyés</span> · <span style="color:#ff6b6b;">❌ <?php echo (int) $c['ko']; ?> échecs</span></div>
				<div class="sub" style="font-size:.78rem;"><?php echo esc_html( mb_substr( (string) ( $c['msg'] ?? '' ), 0, 90 ) ); ?></div>
				<details style="margin-top:6px;"><summary style="cursor:pointer;color:var(--gold);font-size:.82rem;">Voir les numéros</summary><div style="font-size:.78rem;color:#cfcfd6;margin-top:5px;line-height:1.7;"><?php foreach ( (array) ( $c['detail'] ?? array() ) as $dd ) { echo esc_html( ( 'envoyé' === ( $dd['st'] ?? '' ) ? '✅ ' : '❌ ' ) . ( $dd['to'] ?? '' ) ) . '<br>'; } ?></div></details>
			</div>
		<?php endforeach; endif; ?>
	</section>
	<?php endif; ?>

	<!-- AUDIT -->
	<section class="view" id="view-audit">
		<h2 class="sec">🛡️ Audits</h2>
		<div class="card">
			<h3>Auditer un site</h3>
			<p class="sub" style="margin:-4px 0 10px;">Colle une adresse → note, problèmes, capture, et rapport client à envoyer.</p>
			<div class="audit-wrap" id="ag-audit-manual" data-num="">
				<input type="url" id="ag-audit-url" inputmode="url" placeholder="https://site-du-prospect.fr" autocomplete="off">
				<button type="button" id="ag-audit-run" class="b gold" style="margin-top:12px;">🛡️ Lancer l'audit</button>
				<div class="audit-res" style="margin-top:10px;"></div>
			</div>
		</div>

		<h3 style="margin:6px 2px 10px;font-size:1.02rem;">Sites déjà audités (<?php echo count( $ag_audits ); ?>)</h3>
		<?php if ( empty( $ag_audits ) ) : ?>
			<div class="card"><p class="sub" style="margin:0;">Aucun site audité pour l'instant. Lance un audit ci-dessus, ou depuis un prospect / la recherche.</p></div>
		<?php else : ?>
			<?php foreach ( $ag_audits as $au ) :
				$acol = ( (int) $au['score'] < 50 ) ? '#ff6b6b' : ( (int) $au['score'] < 75 ? '#e6b35a' : '#2ecc71' );
			?>
				<div class="card" style="padding:13px;">
					<div class="audit-wrap" data-url="<?php echo esc_attr( $au['url'] ); ?>" data-name="<?php echo esc_attr( $au['name'] ?? '' ); ?>" data-num="<?php echo esc_attr( $au['phone'] ?? '' ); ?>">
						<div style="display:flex;justify-content:space-between;gap:8px;align-items:baseline;">
							<strong><?php echo esc_html( $au['name'] ?? $au['url'] ); ?></strong>
							<span style="color:<?php echo esc_attr( $acol ); ?>;font-weight:800;"><?php echo (int) $au['score']; ?>/100</span>
						</div>
						<div class="sub" style="font-size:.76rem;word-break:break-all;margin:2px 0 8px;"><?php echo esc_html( $au['url'] ); ?><?php echo ! empty( $au['crit'] ) ? ' · ' . (int) $au['crit'] . ' faille(s)' : ''; ?></div>
						<button type="button" class="mini ag-audit" data-mode="light" style="cursor:pointer;">🛡️ Voir le rapport</button>
						<div class="audit-res" style="margin-top:8px;"></div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>

</div>

<button id="toTop" aria-label="Remonter">↑</button>
<div id="toast"></div>

<nav class="tabbar">
	<a href="#" data-t="accueil" class="active" onclick="AG.tab('accueil');return false"><i>🏠</i>Accueil</a>
	<a href="#" data-t="prospecter" onclick="AG.tab('prospecter');return false"><i>🎯</i>Prospecter</a>
	<a href="#" data-t="chercher" onclick="AG.tab('chercher');return false"><i>🔎</i>Chercher</a>
	<?php if ( $ag_is_admin ) : ?><a href="#" data-t="appels" onclick="AG.tab('appels');return false"><i>📞</i>Appels</a><?php endif; ?>
	<?php if ( $ag_is_admin ) : ?><a href="#" data-t="ambass" onclick="AG.tab('ambass');return false"><i>🤝</i>Ambass.</a><?php endif; ?>
	<a href="#" data-t="audit" onclick="AG.tab('audit');return false"><i>🛡️</i>Audit</a>
</nav>

<script>
var AG = (function(){
	var AJAX='<?php echo esc_js( $ag_ajax_url ); ?>', N='<?php echo esc_js( $ag_ajax_nonce ); ?>';
	function tab(id){
		document.querySelectorAll('.view').forEach(function(v){ v.classList.remove('on'); });
		var el=document.getElementById('view-'+id); if(el){ el.classList.add('on'); }
		document.querySelectorAll('.tabbar a').forEach(function(a){ a.classList.toggle('active', a.getAttribute('data-t')===id); });
		window.scrollTo(0,0);
	}
	var toastT;
	function toast(m){ var t=document.getElementById('toast'); t.textContent=m; t.classList.add('on'); clearTimeout(toastT); toastT=setTimeout(function(){ t.classList.remove('on'); },3200); }
	function post(action, data){ var fd=new FormData(); fd.append('action',action); fd.append('_n',N); Object.keys(data||{}).forEach(function(k){ fd.append(k,data[k]); }); return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}); }
	return { tab:tab, toast:toast, post:post, AJAX:AJAX, N:N, admin:<?php echo $ag_is_admin ? 'true' : 'false'; ?> };
})();

(function(){
	// Capture d'écran : mShots met parfois du temps ou échoue → on recharge, sinon on masque (pas de cadre cassé).
	window.agShot = function(img){
		if(img.naturalWidth>50 && !img.getAttribute('data-fail')){ return; } // vraie image chargée → stop
		var n = +(img.getAttribute('data-try')||0);
		if(n>=5){ img.style.display='none'; return; } // abandon propre : on cache l'image
		img.setAttribute('data-try', n+1); img.removeAttribute('data-fail');
		setTimeout(function(){ img.src = img.getAttribute('data-base')+'&r='+Date.now(); }, 3200);
	};
	window.agShotErr = function(img){ img.setAttribute('data-fail','1'); window.agShot(img); };
	// Rapport d'audit partagé (capture du site + note + problèmes + SMS + lien rapport client)
	function agAuditHTML(url, d, num){
		var color = d.score<50 ? '#ff6b6b' : (d.score<75 ? '#e6b35a' : '#2ecc71');
		var reco  = (d.reco==='securite') ? '🛡️ Sécurité conseillée' : '🔧 Refonte conseillée';
		var shot  = 'https://s.wordpress.com/mshots/v1/'+encodeURIComponent(url)+'?w=520';
		var h='<img src="'+shot+'" data-base="'+shot+'" data-try="0" onload="agShot(this)" onerror="agShotErr(this)" alt="" style="width:100%;height:170px;object-fit:cover;object-position:top;border-radius:10px;border:1px solid rgba(255,255,255,.12);margin:6px 0;background:#0e0e13;">';
		h+='<div style="margin:4px 0 6px;"><span class="sc" style="color:'+color+';font-size:1.05rem;">Note '+d.score+'/100</span>'+(d.critical>0?' · '+d.critical+' faille(s)':'')+(d.tech?' · '+d.tech:'')+' · <b>'+reco+'</b></div>';
		if(d.fails && d.fails.length){ h+='<ul style="margin:0 0 8px;padding:0;list-style:none;font-size:.82rem;color:#cfcfd6;line-height:1.6;">'; d.fails.forEach(function(f){ h+='<li>'+f+'</li>'; }); h+='</ul>'; }
		if(d.report){ h+='<a class="mini" target="_blank" rel="noopener" style="display:inline-block;margin-bottom:6px;border-color:#22c55e;color:#7ee2a8;" href="'+d.report+'">👁️ Voir le rapport client</a> '; }
		if(d.report_card){ h+='<a class="mini" target="_blank" rel="noopener" style="display:inline-block;margin-bottom:8px;border-color:#e6b35a;color:#e6b35a;" href="'+d.report_card+'">🖼️ Image à envoyer (screenshot)</a>'; }
		if(num){
			var waN=(num||'').replace(/[^0-9]/g,''); if(waN.charAt(0)==='0'){ waN='33'+waN.substring(1); }
			h+='<div class="row">'
			 +'<a class="mini" target="_blank" rel="noopener" style="border-color:#25d366;color:#7ee2a8;background:rgba(37,211,102,.14);" href="https://wa.me/'+waN+'?text='+encodeURIComponent((d.msg&&d.msg.rapport)||'')+'">🟢 Rapport WhatsApp (avec image)</a>'
			 +'<a class="mini" style="border-color:#22c55e;color:#7ee2a8;" href="sms:'+num+'?body='+encodeURIComponent((d.msg&&d.msg.rapport)||'')+'">📤 Rapport SMS</a>'
			 +'<a class="mini" style="border-color:#d4b45c;color:#e6b35a;" href="sms:'+num+'?body='+encodeURIComponent(d.msg.refonte)+'">✉️ Refonte</a>'
			 +'<a class="mini" style="border-color:#a855f7;color:#c58bff;" href="sms:'+num+'?body='+encodeURIComponent(d.msg.securite)+'">✉️ Sécurité</a>'
			 +'<a class="mini" style="border-color:#3aa3ff;color:#8fc7ff;" href="sms:'+num+'?body='+encodeURIComponent(d.msg.mixte)+'">✉️ Mixte</a>'
			 +'</div>';
		} else { h+='<span class="sub">Pas de mobile pour SMS (fixe → utilise le robot).</span>'; }
		if(AG.admin){
			h+='<details style="margin-top:9px;"><summary style="cursor:pointer;color:#7fb4ff;font-size:.82rem;font-weight:600;">🔬 Résultats scan Kali (PC) — enrichir le rapport</summary>'
			 +'<textarea class="agk-txt" style="width:100%;min-height:80px;margin-top:6px;" placeholder="Colle ici le résultat de ton scan Kali pour ce site…"></textarea>'
			 +'<button type="button" class="mini agk-save" data-url="'+url+'" style="cursor:pointer;margin-top:6px;">💾 Enregistrer pour ce site</button></details>';
		}
		return h;
	}

	// Numéro rapide
	var num=document.getElementById('ag-num'), msg=document.getElementById('ag-msg'),
	    bC=document.getElementById('ag-call'), bS=document.getElementById('ag-sms'), bW=document.getElementById('ag-wa'), bR=document.getElementById('ag-robot');
	function telN(v){ return (v||'').replace(/[^0-9+]/g,''); }
	function waN(v){ var n=(v||'').replace(/[^0-9]/g,''); if(n.charAt(0)==='0'){ n='33'+n.substring(1); } return n; }
	function refresh(){
		var t=telN(num.value), ok=t.replace(/\D/g,'').length>=6, m=encodeURIComponent(msg.value||'');
		[bC,bS,bW].forEach(function(b){ ok?b.removeAttribute('disabled'):b.setAttribute('disabled','disabled'); });
		if(bR){ ok?bR.removeAttribute('disabled'):bR.setAttribute('disabled','disabled'); }
		if(!ok) return;
		bC.setAttribute('href','tel:'+t); bS.setAttribute('href','sms:'+t+'?body='+m); bW.setAttribute('href','https://wa.me/'+waN(num.value)+'?text='+m);
	}
	if(num){ num.addEventListener('input',refresh); msg.addEventListener('input',refresh); refresh(); }
	if(bR){ bR.addEventListener('click',function(){
		var ang=document.getElementById('ag-angle').value;
		bR.textContent='📞 Appel en cours…'; bR.setAttribute('disabled','disabled');
		AG.post('ag_app_voice_call',{phone:num.value,angle:ang}).then(function(j){
			bR.textContent='🤖 Appel robot'; refresh();
			AG.toast(j&&j.success ? '🤖 Le robot appelle '+num.value+' !' : ('❌ '+((j&&j.data&&j.data.m)||'Échec')));
		}).catch(function(){ bR.textContent='🤖 Appel robot'; refresh(); AG.toast('❌ Erreur réseau'); });
	}); }

	// Appel robot par prospect (angle intelligent : sécurité si site, création sinon)
	document.querySelectorAll('.ag-robot-one').forEach(function(a){
		a.addEventListener('click',function(e){ e.preventDefault();
			var ph=a.getAttribute('data-phone'), nm=a.getAttribute('data-name'), ang=a.getAttribute('data-angle')||'creation';
			if(!confirm('Le robot Emma va appeler '+(nm||ph)+' (angle '+(ang==='securite'?'sécurité':'création de site')+'). Lancer ?')) return;
			var lbl=a.innerHTML; a.textContent='📞…';
			AG.post('ag_app_voice_call',{phone:ph,name:nm,angle:ang}).then(function(j){
				a.innerHTML=lbl; AG.toast(j&&j.success ? '🤖 Le robot appelle '+(nm||ph)+' !' : ('❌ '+((j&&j.data&&j.data.m)||'Échec')));
			}).catch(function(){ a.innerHTML=lbl; AG.toast('❌ Erreur réseau'); });
		});
	});

	// Filtres (chips) des prospects
	var chips=document.getElementById('ag-chips');
	if(chips){
		var flt={grp:'all',kind:'all'};
		function applyFlt(){
			document.querySelectorAll('#view-prospecter .p').forEach(function(p){
				var okg=(flt.grp==='all'||p.getAttribute('data-grp')===flt.grp);
				var okk=(flt.kind==='all'||p.getAttribute('data-kind')===flt.kind);
				p.style.display=(okg&&okk)?'':'none';
			});
		}
		chips.querySelectorAll('.chip').forEach(function(c){
			c.addEventListener('click',function(){
				var f=c.getAttribute('data-f'), v=c.getAttribute('data-v');
				flt[f]=(flt[f]===v)?'all':v; // re-clic = enlève le filtre
				chips.querySelectorAll('.chip[data-f="'+f+'"]').forEach(function(x){ x.classList.toggle('on', x.getAttribute('data-v')===flt[f]); });
				// "Tous" gère le groupe
				if(f==='grp'){ chips.querySelector('.chip[data-v="all"]').classList.toggle('on', flt.grp==='all'); }
				applyFlt();
			});
		});
	}

	// Suppression d'un prospect
	document.querySelectorAll('.ag-del').forEach(function(b){
		b.addEventListener('click',function(){
			var id=b.getAttribute('data-id'), nm=b.getAttribute('data-name')||'ce prospect';
			if(!confirm('Supprimer '+nm+' de tes prospects ?')) return;
			b.textContent='…';
			AG.post('ag_app_prospect_delete',{id:id}).then(function(j){
				if(j&&j.success){ var card=b.closest('.p'); if(card){ card.style.display='none'; } AG.toast('🗑️ Supprimé'); }
				else { b.textContent='🗑️'; AG.toast('❌ '+((j&&j.data&&j.data.m)||'Erreur')); }
			}).catch(function(){ b.textContent='🗑️'; AG.toast('❌ Erreur réseau'); });
		});
	});

	// Note auto du contact
	document.querySelectorAll('.ag-touch').forEach(function(a){
		a.addEventListener('click',function(){ var id=a.getAttribute('data-id'), ch=a.getAttribute('data-channel'); if(id){ AG.post('ag_amb_touch',{id:id,channel:ch}).catch(function(){}); } });
	});

	// Audit d'un site (léger / avancé) → note + SMS auto selon la note
	document.querySelectorAll('.ag-audit').forEach(function(btn){
		btn.addEventListener('click',function(){
			var w=btn.closest('.audit-wrap'); if(!w) return;
			var url=w.getAttribute('data-url'), id=w.getAttribute('data-id'), nm=w.getAttribute('data-name'), num=w.getAttribute('data-num');
			var res=w.querySelector('.audit-res'), mode=btn.getAttribute('data-mode');
			res.innerHTML='<span class="sub">🔍 Audit en cours…</span>';
			AG.post('ag_app_audit',{url:url,mode:mode,id:id,name:nm,phone:num}).then(function(j){
				if(!j||!j.success){ res.innerHTML='<span class="sub">❌ '+((j&&j.data&&j.data.m)||'Erreur')+'</span>'; return; }
				res.innerHTML=agAuditHTML(url, j.data, num);
			}).catch(function(){ res.innerHTML='<span class="sub">❌ Erreur réseau</span>'; });
		});
	});

	// Recherche intégrée
	var sb=document.getElementById('ag-search'), city=document.getElementById('ag-city'), box=document.getElementById('ag-results');
	if(sb){ sb.addEventListener('click',function(){
		var c=(city.value||'').trim(); if(!c){ AG.toast('Indique une ville'); return; }
		sb.textContent='🔎 Recherche…'; sb.setAttribute('disabled','disabled'); box.innerHTML='';
		AG.post('ag_amb_search',{city:c}).then(function(j){
			sb.textContent='🔎 Lancer la recherche'; sb.removeAttribute('disabled');
			if(!j||!j.success){ AG.toast('❌ '+((j&&j.data&&j.data.m)||'Erreur')); return; }
			var items=j.data.items||[];
			if(!items.length){ box.innerHTML='<p class="sub">Aucun résultat.</p>'; return; }
			box.innerHTML='';
			items.forEach(function(it,i){
				var d=document.createElement('div'); d.className='res';
				var tel=(it.phone_intl||it.phone||'').replace(/[^0-9+]/g,'');
				d.innerHTML='<div class="rn">'+(it.name||'')+'</div><div class="rk">'+(it.city||'')+' · '+(it.kind||'')+(it.exists?' · déjà en base':'')+'</div>';
				var row=document.createElement('div'); row.className='row'; row.style.display='flex'; row.style.gap='7px'; row.style.flexWrap='wrap';
				if(tel){ var ta=document.createElement('a'); ta.className='mini'; ta.href='tel:'+tel; ta.textContent='📞'; row.appendChild(ta); }
				var add=document.createElement('button'); add.className='mini'; add.style.cursor='pointer'; add.textContent=it.exists?'✓ En base':'+ Ajouter';
				if(it.exists){ add.setAttribute('disabled','disabled'); }
				add.addEventListener('click',function(){
					add.textContent='…';
					AG.post('ag_amb_add',{name:it.name,type:it.type,city:it.city,phone:it.phone,phone_intl:it.phone_intl,website:it.website}).then(function(r){
						add.textContent=(r&&r.success)?'✓ Ajouté':'Erreur'; AG.toast((r&&r.success)?'✅ Ajouté à tes prospects':'❌ Erreur');
					}).catch(function(){ add.textContent='Erreur'; });
				});
				row.appendChild(add);
				// Site présent → bouton d'audit + rapport (capture + note + SMS) dans le résultat.
				var rep=document.createElement('div'); rep.style.marginTop='8px';
				if(it.website){
					var aud=document.createElement('button'); aud.className='mini'; aud.style.cursor='pointer'; aud.style.borderColor='#7c3aed'; aud.style.color='#c58bff'; aud.textContent='🛡️ Auditer le site';
					aud.addEventListener('click',function(){
						rep.innerHTML='<span class="sub">🔍 Audit en cours…</span>';
						AG.post('ag_app_audit',{url:it.website,mode:'light',name:it.name,phone:tel}).then(function(r){
							if(!r||!r.success){ rep.innerHTML='<span class="sub">❌ '+((r&&r.data&&r.data.m)||'Erreur')+'</span>'; return; }
							rep.innerHTML=agAuditHTML(it.website, r.data, tel);
						}).catch(function(){ rep.innerHTML='<span class="sub">❌ Erreur réseau</span>'; });
					});
					row.appendChild(aud);
				}
				d.appendChild(row); d.appendChild(rep); box.appendChild(d);
			});
			if(typeof j.data.left!=='undefined'){ AG.toast('Recherches restantes ce mois : '+j.data.left); }
		}).catch(function(){ sb.textContent='🔎 Lancer la recherche'; sb.removeAttribute('disabled'); AG.toast('❌ Erreur réseau'); });
	}); }

	// Audit manuel (onglet Audit) : lance l'audit sur l'URL saisie et affiche le rapport dans l'app
	var arun=document.getElementById('ag-audit-run'), aurl=document.getElementById('ag-audit-url');
	if(arun){ arun.addEventListener('click',function(){
		var u=(aurl.value||'').trim(); if(!u){ AG.toast('Colle une adresse de site'); return; }
		if(!/^https?:\/\//i.test(u)){ u='https://'+u; }
		var res=document.getElementById('ag-audit-manual').querySelector('.audit-res');
		res.innerHTML='<span class="sub">🔍 Audit en cours…</span>'; arun.setAttribute('disabled','disabled'); arun.textContent='🔍 Audit…';
		AG.post('ag_app_audit',{url:u,mode:'light'}).then(function(j){
			arun.removeAttribute('disabled'); arun.textContent='🛡️ Lancer l\'audit';
			if(!j||!j.success){ res.innerHTML='<span class="sub">❌ '+((j&&j.data&&j.data.m)||'Erreur')+'</span>'; return; }
			res.innerHTML=agAuditHTML(u, j.data, '');
		}).catch(function(){ arun.removeAttribute('disabled'); arun.textContent='🛡️ Lancer l\'audit'; res.innerHTML='<span class="sub">❌ Erreur réseau</span>'; });
	}); }

	// Ambassadeurs — SMS en masse + suivi
	var ambPhones = <?php echo wp_json_encode( array_values( array_map( function ( $a ) { return $a['phone']; }, $ag_ambs ) ) ); ?>;
	var fillA=document.getElementById('ag-fill-ambs');
	if(fillA){ fillA.addEventListener('click',function(){ document.getElementById('ag-bulk-nums').value=ambPhones.join('\n'); AG.toast(ambPhones.length+' numéros ajoutés'); }); }
	var bulkSend=document.getElementById('ag-bulk-send');
	if(bulkSend){ bulkSend.addEventListener('click',function(){
		var nums=document.getElementById('ag-bulk-nums').value, m=document.getElementById('ag-bulk-msg').value;
		if(!nums.trim()){ AG.toast('Colle des numéros'); return; }
		if(!confirm('Envoyer ce SMS à tous ces numéros ?')) return;
		var r=document.getElementById('ag-bulk-res'); r.textContent='📨 Envoi en cours…'; bulkSend.setAttribute('disabled','disabled'); bulkSend.textContent='📨 Envoi…';
		AG.post('ag_app_bulk_sms',{numbers:nums,msg:m}).then(function(j){
			bulkSend.removeAttribute('disabled'); bulkSend.textContent='📨 Envoyer en masse';
			if(j&&j.success){ r.innerHTML='✅ '+j.data.ok+' envoyés · ❌ '+j.data.ko+' échecs (sur '+j.data.total+'). Recharge la page pour voir le suivi.'; }
			else { r.textContent='⚠️ '+((j&&j.data&&j.data.m)||'Erreur'); }
		}).catch(function(){ bulkSend.removeAttribute('disabled'); bulkSend.textContent='📨 Envoyer en masse'; r.textContent='❌ Erreur réseau'; });
	}); }

	// Enregistrer un résultat Kali (délégué car injecté dynamiquement)
	document.addEventListener('click',function(e){
		var b=e.target && e.target.closest ? e.target.closest('.agk-save') : null; if(!b) return;
		var det=b.closest('details'), txt=det?det.querySelector('.agk-txt'):null, url=b.getAttribute('data-url');
		b.textContent='…';
		AG.post('ag_app_kali_save',{url:url,kali:txt?txt.value:''}).then(function(j){
			b.textContent=(j&&j.success)?'✅ Enregistré':'Erreur'; AG.toast((j&&j.success)?'🔬 Résultat Kali enregistré (rapport enrichi)':'❌ Erreur');
		}).catch(function(){ b.textContent='💾 Enregistrer pour ce site'; AG.toast('❌ Erreur réseau'); });
	});

	// Générateur QR code
	var qin=document.getElementById('ag-qr-in'), qimg=document.getElementById('ag-qr-img'), qopen=document.getElementById('ag-qr-open');
	function qrUpd(){ var d=encodeURIComponent(qin.value||''); qimg.src='https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data='+d; qopen.href='https://api.qrserver.com/v1/create-qr-code/?size=800x800&margin=20&data='+d; }
	if(qin){ qin.addEventListener('input',qrUpd);
		document.querySelectorAll('.qr-preset').forEach(function(b){ b.addEventListener('click',function(){ qin.value=b.getAttribute('data-v'); qrUpd(); qin.focus(); }); });
	}

	// Bouton remonter
	var top=document.getElementById('toTop');
	window.addEventListener('scroll',function(){ top.style.display = window.scrollY>320?'flex':'none'; },{passive:true});
	top.addEventListener('click',function(){ window.scrollTo({top:0,behavior:'smooth'}); });

	// Tirer-pour-rafraîchir
	var ptr=document.getElementById('ptr'), sy=0, pulling=false;
	window.addEventListener('touchstart',function(e){ if(window.scrollY<=0){ sy=e.touches[0].clientY; pulling=true; } },{passive:true});
	window.addEventListener('touchmove',function(e){
		if(!pulling) return; var dy=e.touches[0].clientY - sy;
		if(dy>0 && window.scrollY<=0){ ptr.style.height=Math.min(dy,80)+'px'; ptr.style.opacity=Math.min(dy/80,1); ptr.classList.toggle('ready',dy>65); ptr.textContent = dy>65 ? '↻ Relâche pour rafraîchir' : '↓ Tire pour rafraîchir'; }
	},{passive:true});
	window.addEventListener('touchend',function(){
		if(pulling && ptr.classList.contains('ready')){ ptr.textContent='↻ Actualisation…'; location.reload(); }
		else { ptr.style.height='0'; ptr.style.opacity='0'; }
		pulling=false;
	});
})();
</script>
</body>
</html>
<?php
exit;
