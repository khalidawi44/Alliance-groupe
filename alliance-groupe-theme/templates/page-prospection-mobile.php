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

/* Numéro FR mobile (06/07) ? → SMS/WhatsApp possibles. Sinon fixe (01-05/09) → appel + robot uniquement. */
if ( ! function_exists( 'ag_is_mobile_fr' ) ) {
	function ag_is_mobile_fr( $raw ) {
		$d = preg_replace( '/[^0-9]/', '', (string) $raw );
		if ( '' === $d ) return true; // inconnu → on ne masque rien
		if ( 0 === strpos( $d, '0033' ) ) $d = substr( $d, 4 );
		elseif ( 0 === strpos( $d, '33' ) && strlen( $d ) >= 11 ) $d = substr( $d, 2 );
		if ( 0 === strpos( $d, '0' ) ) $d = substr( $d, 1 );
		$first = substr( $d, 0, 1 );
		return in_array( $first, array( '6', '7' ), true );
	}
}

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
	<meta name="apple-mobile-web-app-title" content="<?php echo $ag_is_admin ? 'Alliance Admin' : 'Alliance Amb'; ?>">
	<meta name="theme-color" content="#0b0b0f">
	<link rel="apple-touch-icon" href="<?php echo esc_url( $ag_icon ); ?>">
	<title>Alliance Groupe — <?php echo $ag_is_admin ? 'Admin' : 'Ambassadeur'; ?></title>
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
		.res .rlink{ font-size:.78rem; color:#7fb4ff; text-decoration:none; word-break:break-all; }
		/* Accordéon des recherches sauvegardées (jamais de longue liste) */
		.acc{ border:1px solid var(--line); border-radius:14px; margin-bottom:10px; overflow:hidden; background:rgba(255,255,255,.02); }
		.acc>summary{ list-style:none; cursor:pointer; padding:13px 14px; display:flex; align-items:center; gap:9px; font-weight:700; }
		.acc>summary::-webkit-details-marker{ display:none; }
		.acc>summary .chev{ margin-left:auto; transition:transform .2s; color:var(--soft); }
		.acc[open]>summary .chev{ transform:rotate(90deg); }
		.acc>summary .cnt{ background:rgba(212,180,92,.16); color:var(--gold); border-radius:100px; padding:1px 9px; font-size:.74rem; }
		.acc .accb{ padding:6px 12px 12px; }
		.acc .adel{ border:none; background:transparent; color:#e5484d; cursor:pointer; font-size:1rem; padding:2px 4px; }
		.sortbar{ display:flex; gap:6px; flex-wrap:wrap; margin:2px 0 12px; }
		.sortbar button{ background:rgba(255,255,255,.05); border:1px solid var(--line); color:var(--soft); border-radius:100px; padding:6px 12px; font-size:.78rem; font-weight:600; cursor:pointer; }
		.sortbar button.on{ background:rgba(212,180,92,.16); border-color:var(--gold); color:var(--gold); }
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
	<b>Alliance <span><?php echo $ag_is_admin ? 'Admin' : 'Ambassadeur'; ?></span></b>
</header>

<div class="wrap">

	<!-- ACCUEIL -->
	<section class="view on" id="view-accueil">
		<p class="hi">Salut <span><?php echo esc_html( $ag_prenom ); ?></span> </p>
		<p class="sub">Ton QG de prospection.</p>
		<div class="stats">
			<a class="stat todo" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['a_contacter']; ?></b><span>À contacter</span></a>
			<a class="stat done" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['contacte']; ?></b><span>Contactés</span></a>
			<a class="stat rep" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['repondeur']; ?></b><span>Répondeur</span></a>
			<a class="stat hot" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['interesse']; ?></b><span>Intéressés</span></a>
			<a class="stat cli" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['client']; ?></b><span>Clients</span></a>
			<a class="stat" href="#" onclick="AG.tab('prospecter');return false"><b><?php echo (int) $ag_cnt['total']; ?></b><span>Total actifs</span></a>
		</div>
		<div class="launch">
			<a href="#" onclick="AG.tab('prospecter');return false"><i></i>Numéro rapide</a>
			<a href="#" onclick="AG.tab('prospecter');return false"><i></i>Mes prospects</a>
			<a href="#" onclick="AG.tab('chercher');return false"><i></i>Chercher</a>
			<a href="#" onclick="AG.tab('audit');return false"><i></i>Audit</a>
		</div>
		<div class="card" style="margin-top:14px;">
			<h3>Générateur de QR code</h3>
			<p class="sub" style="margin:-4px 0 10px;">Ton lien de vente, un réseau, un numéro… → QR à imprimer/partager (flyer, carte, vitrine).</p>
			<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:9px;">
				<button type="button" class="mini qr-preset" data-v="<?php echo esc_attr( $ag_sale_link ); ?>" style="cursor:pointer;">Mon lien</button>
				<button type="button" class="mini qr-preset" data-v="https://wa.me/33744829516" style="cursor:pointer;">WhatsApp</button>
				<button type="button" class="mini qr-preset" data-v="https://www.instagram.com/" style="cursor:pointer;">Insta</button>
				<button type="button" class="mini qr-preset" data-v="https://www.snapchat.com/add/" style="cursor:pointer;">Snap</button>
				<button type="button" class="mini qr-preset" data-v="https://www.tiktok.com/@" style="cursor:pointer;">TikTok</button>
			</div>
			<input type="text" id="ag-qr-in" value="<?php echo esc_attr( $ag_sale_link ); ?>" placeholder="Lien ou texte">
			<div style="text-align:center;margin-top:12px;"><img id="ag-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=<?php echo rawurlencode( $ag_sale_link ); ?>" alt="QR" style="width:200px;height:200px;background:#fff;border-radius:14px;padding:8px;"></div>
			<a id="ag-qr-open" class="b gold" style="margin-top:12px;" target="_blank" rel="noopener" href="https://api.qrserver.com/v1/create-qr-code/?size=800x800&margin=20&data=<?php echo rawurlencode( $ag_sale_link ); ?>">Ouvrir en grand (appui long → Enregistrer l'image)</a>
			<a class="link" style="margin-top:10px;" href="<?php echo esc_url( home_url( '/studio' ) ); ?>"><i></i><span><b>Studio créatif</b><span>Vidéos &amp; visuels de pub</span></span><span class="arr">→</span></a>
		</div>
		<p class="logout"><a href="<?php echo esc_url( wp_logout_url( home_url( '/connexion' ) ) ); ?>">Se déconnecter</a></p>
		<p class="hint">Installer l'app : Safari → Partager ↑ → « Sur l'écran d'accueil ».</p>
	</section>

	<!-- PROSPECTER -->
	<section class="view" id="view-prospecter">
		<h2 class="sec">Prospecter</h2>
		<div class="card">
			<h3>Numéro rapide</h3>
			<input type="tel" id="ag-num" inputmode="tel" placeholder="Ex : 06 12 34 56 78" autocomplete="off">
			<textarea id="ag-msg"><?php echo esc_textarea( $ag_default_msg ); ?></textarea>
			<div class="acts">
				<a href="#" id="ag-call" class="b call" disabled>Appeler</a>
				<a href="#" id="ag-sms" class="b sms" disabled>SMS</a>
				<a href="#" id="ag-wa" class="b wa" disabled>WA</a>
			</div>
			<?php if ( $ag_robot_ok ) : ?>
			<div style="display:flex; gap:8px; margin-top:10px;">
				<select id="ag-angle" style="flex:1;"><option value="creation">Angle site</option><option value="securite">Angle sécurité</option></select>
				<button type="button" id="ag-robot" class="b robot" style="flex:1;" disabled>Appel robot</button>
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
					<button class="chip" data-f="grp" data-v="todo">À contacter</button>
					<button class="chip" data-f="grp" data-v="done">Contactés</button>
					<button class="chip" data-f="grp" data-v="rappel">À rappeler</button>
					<button class="chip" data-f="rep" data-v="1">Ont répondu</button>
					<button class="chip" data-f="grp" data-v="hot">Intéressés</button>
					<button class="chip" data-f="kind" data-v="site">Sécurité (site)</button>
					<button class="chip" data-f="kind" data-v="crea">Site web</button>
				</div>
				<?php if ( $ag_robot_ok ) : ?>
				<button type="button" id="ag-robot-all" class="b" style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;margin:0 0 12px;">Appeler tout le monde au robot (visibles)</button>
				<p class="sub" style="margin:-6px 0 12px;font-size:.74rem;">Le robot Emma appelle tous les prospects affichés, quel que soit le numéro (06, 02, 08, 09…).</p>
				<?php endif; ?>
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
					$pmobile = ag_is_mobile_fr( $pp['phone_intl'] ?? ( $pp['phone'] ?? '' ) ); // fixe → pas de SMS/WhatsApp
					$has_site = '' !== trim( (string) ( $pp['website'] ?? '' ) );
					$pangle   = $has_site ? 'securite' : 'creation';
					$fgrp     = ( 'nouveau' === $pstatus ) ? 'todo' : ( in_array( $pstatus, array( 'contacte', 'relance' ), true ) ? 'done' : ( 'repondeur' === $pstatus ? 'rappel' : ( 'interesse' === $pstatus ? 'hot' : 'done' ) ) );
					$fkind    = $has_site ? 'site' : 'crea';
					$preplied = ( ! empty( $pp['replied'] ) || 'interesse' === $pstatus ) ? 1 : 0;
					$plabel   = $ag_pstat[ $pstatus ] ?? '';
				?>
					<div class="p" data-grp="<?php echo esc_attr( $fgrp ); ?>" data-kind="<?php echo esc_attr( $fkind ); ?>" data-rep="<?php echo (int) $preplied; ?>" data-id="<?php echo esc_attr( $pid ); ?>">
						<div class="nm"><?php echo esc_html( $pp['name'] ?? '' ); ?></div>
						<div class="why"><?php echo esc_html( ( ! empty( $pp['city'] ) ? $pp['city'] . ' · ' : '' ) . ( function_exists( 'ag_prospect_why' ) ? ag_prospect_why( $pp ) : '' ) ); ?></div>
						<div class="why" style="font-size:.76rem;margin-top:-2px;">
							<?php echo esc_html( $plabel ); ?>
							<?php if ( ! empty( $pp['date_contact'] ) ) : ?> · <?php echo esc_html( ! empty( $pp['last_channel'] ) ? $pp['last_channel'] : 'contacté' ); ?> le <?php echo esc_html( $pp['date_contact'] ); ?><?php endif; ?>
							<?php if ( $preplied ) : ?> · <span style="color:#4ade80;font-weight:700;">a répondu</span><?php endif; ?>
						</div>
						<div class="row">
							<?php if ( $ptel ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="Appel" href="<?php echo esc_attr( $ptel ); ?>"></a><?php endif; ?>
							<?php if ( $pmobile && $psms ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="SMS" href="<?php echo esc_attr( $psms ); ?>"></a><?php endif; ?>
							<?php if ( $pmobile && $pwa ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="WhatsApp" href="<?php echo esc_url( $pwa ); ?>" target="_blank" rel="noopener"></a><?php endif; ?>
							<?php if ( $ag_robot_ok && $pnum ) : ?><a class="mini robot ag-robot-one" href="#" data-phone="<?php echo esc_attr( $pnum ); ?>" data-name="<?php echo esc_attr( $pp['name'] ?? '' ); ?>" data-angle="<?php echo esc_attr( $pangle ); ?>">Robot <?php echo 'securite' === $pangle ? '' : ''; ?></a><?php endif; ?>
							<?php if ( ! $pmobile && $pnum ) : ?><span class="mini" style="border-color:#3a3a44;color:#8a8a92;pointer-events:none;">fixe</span><?php endif; ?>
							<?php if ( 'repondeur' === $pstatus && $ptel ) : ?><a class="mini ag-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="Rappel" href="<?php echo esc_attr( $ptel ); ?>" style="border-color:#e6a817;color:#f0c96b;">Rappeler</a><?php endif; ?>
							<button type="button" class="mini ag-reply" data-id="<?php echo esc_attr( $pid ); ?>" data-rep="<?php echo (int) $preplied; ?>" style="cursor:pointer;border-color:#2e6a3f;color:#7ee2a8;"><?php echo $preplied ? 'A répondu' : 'A répondu ?'; ?></button>
							<form method="post" action="<?php echo esc_url( $ag_ppost ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_amb_prospect_status">
								<?php echo $ag_pnonce; // phpcs:ignore ?>
								<input type="hidden" name="id" value="<?php echo esc_attr( $pid ); ?>">
								<select name="status" onchange="this.form.submit()">
									<?php foreach ( $ag_pstat as $sk => $sl ) : ?><option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $pstatus, $sk ); ?>><?php echo esc_html( $sl ); ?></option><?php endforeach; ?>
								</select>
							</form>
							<button type="button" class="mini ag-del" data-id="<?php echo esc_attr( $pid ); ?>" data-name="<?php echo esc_attr( $pp['name'] ?? '' ); ?>" style="cursor:pointer;border-color:#e5484d;color:#ff8a8a;"></button>
						</div>
						<?php
						$pweb  = $pp['website'] ?? '';
						$pmaps = ! empty( $pp['maps_uri'] ) ? $pp['maps_uri'] : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( ( $pp['name'] ?? '' ) . ' ' . ( $pp['city'] ?? '' ) ) );
						$prat  = (float) ( $pp['rating'] ?? 0 );
						$prev  = (int) ( $pp['reviews'] ?? 0 );
						$pas   = isset( $pp['audit_score'] ) ? (int) $pp['audit_score'] : -1;
						?>
						<div class="grev">
							<?php if ( $prat > 0 ) : ?><?php echo esc_html( number_format_i18n( $prat, 1 ) ); ?> <span>(<?php echo (int) $prev; ?> avis)</span> · <?php endif; ?>
							<a href="<?php echo esc_url( $pmaps ); ?>" target="_blank" rel="noopener">Voir sur Google</a>
						</div>
						<?php if ( $pweb ) : ?>
						<div class="audit-wrap" data-url="<?php echo esc_attr( $pweb ); ?>" data-id="<?php echo esc_attr( $pid ); ?>" data-name="<?php echo esc_attr( $pp['name'] ?? '' ); ?>" data-num="<?php echo esc_attr( $psnum ); ?>">
							<div class="row" style="margin-top:2px;">
								<button type="button" class="mini ag-audit" data-mode="complet">Auditer le site</button>
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
		<h2 class="sec">Chercher des prospects</h2>
		<div class="card">
			<h3>Recherche par ville</h3>
			<input type="text" id="ag-city" placeholder="Ex : Nantes, ou 'restaurant Nantes'" autocomplete="off">
			<button type="button" id="ag-search" class="b gold" style="margin-top:12px;">Lancer la recherche</button>
			<p class="sub" style="margin:8px 0 0; font-size:.78rem;">Ex : « massage Nantes ». Les résultats sont <strong>sauvegardés</strong> et classés par catégorie/ville ci-dessous.</p>
		</div>
		<div id="ag-results"></div>
		<h3 style="margin:18px 0 6px;">Mes recherches sauvegardées</h3>
		<div class="sortbar" id="ag-sort">
			<button type="button" data-s="recent" class="on">Récentes</button>
			<button type="button" data-s="az">A → Z</button>
			<button type="button" data-s="big">Plus de résultats</button>
		</div>
		<div id="ag-saved"><p class="sub">Chargement…</p></div>

		<h3 style="margin:20px 0 4px;">Agences web cartographiées</h3>
		<p class="sub" style="margin:0 0 10px;font-size:.78rem;">Classées du <strong>pire au meilleur</strong>. Audite le site d'une agence web puis clique « Cartographier ses créations » : tu vois tous les sites qu'elle a faits (et tu peux démarcher ses clients).</p>
		<div id="ag-agencies"><p class="sub">Aucune agence cartographiée pour l'instant.</p></div>
	</section>

	<?php if ( $ag_is_admin ) : ?>
	<!-- APPELS (journal du robot) -->
	<section class="view" id="view-appels">
		<h2 class="sec">Appels du robot</h2>
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
					<div style="margin:5px 0 2px;"><span style="display:inline-block;background:rgba(212,180,92,.15);border:1px solid rgba(212,180,92,.35);border-radius:100px;padding:2px 10px;font-size:.78rem;font-weight:700;"><?php echo esc_html( $lbl ); ?></span> <span class="sub" style="font-size:.76rem;"><?php echo esc_html( $e['phone'] ?? '' ); ?><?php echo ! empty( $e['rappel'] ) ? ' · ' . esc_html( $e['rappel'] ) : ''; ?></span></div>
					<?php if ( '' !== $trans ) : ?>
						<details style="margin-top:6px;"><summary style="cursor:pointer;color:var(--gold);font-size:.85rem;font-weight:600;">Retranscription</summary><div style="white-space:pre-wrap;background:rgba(0,0,0,.25);border-radius:9px;padding:9px;margin-top:6px;font-size:.82rem;line-height:1.5;color:#d6d6db;"><?php echo esc_html( $trans ); ?></div></details>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<?php if ( $ag_is_admin ) : ?>
	<!-- AMBASSADEURS (SMS en masse + suivi) -->
	<section class="view" id="view-ambass">
		<h2 class="sec">Ambassadeurs — SMS en masse</h2>
		<div class="card">
			<h3>Nouvel envoi</h3>
			<p class="sub" style="margin:-4px 0 10px;">Colle jusqu'à 100 numéros (un par ligne, ou séparés par virgule/espace).</p>
			<?php if ( $ag_ambs ) : ?><button type="button" id="ag-fill-ambs" class="mini" style="cursor:pointer;margin-bottom:9px;">Remplir avec mes ambassadeurs (<?php echo count( $ag_ambs ); ?>)</button><?php endif; ?>
			<textarea id="ag-bulk-nums" placeholder="0612345678&#10;0798765432&#10;..." style="min-height:110px;"></textarea>
			<textarea id="ag-bulk-msg" placeholder="Ton message aux ambassadeurs..." style="min-height:82px;">Salut Rappel : 3 ventes/semaine = ~1 068 €/mois (10% par vente, +89 € par site à 890 €). De nouveaux prospects t'attendent dans ton espace, tu peux prospecter partout et même réserver ta région en exclu. On y va ! </textarea>
			<button type="button" id="ag-bulk-send" class="b gold" style="margin-top:10px;">Envoyer en masse</button>
			<div id="ag-bulk-res" class="sub" style="margin-top:8px;"></div>
		</div>
		<h3 style="margin:6px 2px 10px;font-size:1.02rem;">Suivi des envois</h3>
		<?php if ( empty( $ag_bulklog ) ) : ?>
			<div class="card"><p class="sub" style="margin:0;">Aucun envoi pour l'instant.</p></div>
		<?php else : foreach ( $ag_bulklog as $c ) : ?>
			<div class="card" style="padding:13px;">
				<div style="display:flex;justify-content:space-between;align-items:baseline;"><strong><?php echo (int) $c['total']; ?> SMS</strong><span class="sub" style="margin:0;font-size:.76rem;"><?php echo esc_html( date_i18n( 'd/m à H\hi', (int) ( $c['ts'] ?? 0 ) ) ); ?></span></div>
				<div style="margin:4px 0;font-size:.85rem;"><span style="color:#2ecc71;"><?php echo (int) $c['ok']; ?> envoyés</span> · <span style="color:#ff6b6b;"><?php echo (int) $c['ko']; ?> échecs</span></div>
				<div class="sub" style="font-size:.78rem;"><?php echo esc_html( mb_substr( (string) ( $c['msg'] ?? '' ), 0, 90 ) ); ?></div>
				<details style="margin-top:6px;"><summary style="cursor:pointer;color:var(--gold);font-size:.82rem;">Voir les numéros</summary><div style="font-size:.78rem;color:#cfcfd6;margin-top:5px;line-height:1.7;"><?php foreach ( (array) ( $c['detail'] ?? array() ) as $dd ) { echo esc_html( ( 'envoyé' === ( $dd['st'] ?? '' ) ? '' : '' ) . ( $dd['to'] ?? '' ) ) . '<br>'; } ?></div></details>
			</div>
		<?php endforeach; endif; ?>
	</section>
	<?php endif; ?>

	<!-- AUDIT -->
	<section class="view" id="view-audit">
		<h2 class="sec">Audits</h2>
		<div class="card">
			<h3>Auditer un site</h3>
			<p class="sub" style="margin:-4px 0 10px;">Colle une adresse → note, problèmes, capture, et rapport client à envoyer.</p>
			<div class="audit-wrap" id="ag-audit-manual" data-num="">
				<input type="url" id="ag-audit-url" inputmode="url" placeholder="https://site-du-prospect.fr" autocomplete="off">
				<button type="button" id="ag-audit-run" class="b gold" style="margin-top:12px;">Lancer l'audit</button>
				<div class="audit-res" style="margin-top:10px;"></div>
			</div>
		</div>

		<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:6px 2px 10px;">
			<h3 style="margin:0;font-size:1.02rem;">Sites déjà audités (<?php echo count( $ag_audits ); ?>)</h3>
			<?php if ( ! empty( $ag_audits ) ) : ?><button type="button" id="ag-refresh-all" class="mini" style="cursor:pointer;border-color:#3aa3ff;color:#fff;background:rgba(58,163,255,.42);font-weight:700;">Tout réactualiser</button><?php endif; ?>
		</div>
		<p class="sub" style="margin:-4px 2px 10px;font-size:.76rem;">Les notes se mettent en cache 1 h. « Réactualiser » recalcule à neuf et met à jour la note dans tes prospects.</p>
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
						<button type="button" class="mini ag-audit" data-mode="light" style="cursor:pointer;">Voir le rapport</button>
						<div class="audit-res" style="margin-top:8px;"></div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>

	<section class="view" id="view-mis">
		<h2 class="sec">Missions</h2>
		<div class="card"><p class="sub" style="margin:0;">Des missions à réaliser pour l'équipe (prospection, terrain…). Réserve ta place, fais la mission, envoie ton rendu → tu gagnes une prime. Chaque prospect trouvé nourrit le CRM.</p></div>
		<div id="mis-list"><p class="sub" style="padding:6px 2px;">Chargement…</p></div>
	</section>

	<?php if ( $ag_is_admin ) : ?>
	<section class="view" id="view-ao">
		<h2 class="sec">Appels d'offres publics <span style="font-size:.7rem;color:#8a8a92;font-weight:600;">(admin)</span></h2>
		<?php
		$ag_saved = (array) get_option( 'ag_candidatures', array() );
		uasort( $ag_saved, function ( $a, $b ) { return (int) ( $b['ts'] ?? 0 ) - (int) ( $a['ts'] ?? 0 ); } );
		if ( $ag_saved ) :
			$ag_stl = array( 'a_faire' => 'À faire', 'pret' => 'Dossier prêt', 'depose' => 'Déposé ', 'gagne' => 'Gagné ', 'perdu' => 'Perdu' );
		?>
		<div class="card">
			<h3 style="margin:0 0 8px;">Mes marchés sauvegardés (<?php echo count( $ag_saved ); ?>)</h3>
			<?php foreach ( array_slice( $ag_saved, 0, 30, true ) as $sid => $sc ) : ?>
				<div style="border-top:1px solid var(--line);padding:9px 0;">
					<div style="font-size:.9rem;"><?php echo esc_html( wp_trim_words( (string) ( $sc['objet'] ?? '' ), 14, '…' ) ?: $sid ); ?></div>
					<div class="sub" style="font-size:.76rem;margin:3px 0 6px;"><?php echo esc_html( $sc['acheteur'] ?? '' ); ?> · <strong><?php echo esc_html( $ag_stl[ $sc['statut'] ?? '' ] ?? '' ); ?></strong></div>
					<a href="<?php echo esc_url( home_url( '/?ag_candidature=1&id=' . rawurlencode( $sid ) ) ); ?>" target="_blank" rel="noopener" class="b gold" style="display:inline-block;text-decoration:none;padding:7px 12px;font-size:.85rem;">Ouvrir le dossier</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<div class="card">
			<p class="sub" style="margin:0 0 8px;">Marchés publics <strong>ouverts</strong> (site web, cybersécurité, maintenance). Ouvre « Préparer le dossier » → dossier de candidature tout prêt à déposer. Outil perso admin.</p>
			<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
				<input type="text" id="ao-dept" inputmode="text" placeholder="Département (44, 75, 2A…)" style="flex:1;min-width:150px;">
				<button type="button" id="ao-filter" class="mini" style="cursor:pointer;border-color:#3aa3ff;color:#fff;background:rgba(58,163,255,.42);font-weight:700;">Filtrer</button>
			</div>
		</div>
		<div id="ao-list"><p class="sub" style="padding:6px 2px;">Chargement…</p></div>
	</section>
	<?php endif; ?>

</div>

<button id="toTop" aria-label="Remonter">↑</button>
<div id="toast"></div>

<nav class="tabbar">
	<a href="#" data-t="accueil" class="active" onclick="AG.tab('accueil');return false"><i></i>Accueil</a>
	<a href="#" data-t="prospecter" onclick="AG.tab('prospecter');return false"><i></i>Prospecter</a>
	<a href="#" data-t="chercher" onclick="AG.tab('chercher');return false"><i></i>Chercher</a>
	<?php if ( $ag_is_admin ) : ?><a href="#" data-t="appels" onclick="AG.tab('appels');return false"><i></i>Appels</a><?php endif; ?>
	<?php if ( $ag_is_admin ) : ?><a href="#" data-t="ambass" onclick="AG.tab('ambass');return false"><i></i>Ambass.</a><?php endif; ?>
	<a href="#" data-t="audit" onclick="AG.tab('audit');return false"><i></i>Audit</a>
	<a href="#" data-t="mis" onclick="AG.tab('mis');AG.loadMissions();return false"><i></i>Missions</a>
	<?php if ( $ag_is_admin ) : ?><a href="#" data-t="ao" onclick="AG.tab('ao');AG.loadAO();return false"><i></i>Marchés</a><?php endif; ?>
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
		var reco  = (d.reco==='securite') ? 'Sécurité conseillée' : 'Refonte conseillée';
		var shot  = 'https://s.wordpress.com/mshots/v1/'+encodeURIComponent(url)+'?w=520';
		// L'aperçu du site est CLIQUABLE → ouvre le vrai site dans un nouvel onglet.
		var h='<a href="'+url+'" target="_blank" rel="noopener" title="Ouvrir le site" style="display:block;position:relative;">'
			+'<img src="'+shot+'" data-base="'+shot+'" data-try="0" onload="agShot(this)" onerror="agShotErr(this)" alt="" style="width:100%;height:170px;object-fit:cover;object-position:top;border-radius:10px;border:1px solid rgba(255,255,255,.12);margin:6px 0;background:#0e0e13;">'
			+'<span style="position:absolute;right:10px;bottom:14px;background:rgba(0,0,0,.62);color:#fff;font-size:.72rem;font-weight:700;padding:3px 9px;border-radius:100px;">Ouvrir le site</span></a>';
		h+='<div style="margin:4px 0 6px;"><span class="sc" style="color:'+color+';font-size:1.05rem;">Note '+d.score+'/100</span>'+(d.critical>0?' · '+d.critical+' faille(s)':'')+(d.tech?' · '+d.tech:'')+' · <b>'+reco+'</b></div>';
		h+='<a class="mini" target="_blank" rel="noopener" style="display:inline-block;margin:0 0 8px;border-color:#3aa3ff;color:#eaf4ff;background:rgba(58,163,255,.28);font-weight:700;" href="'+url+'">Voir le site</a> ';
		if(d.fails && d.fails.length){ h+='<ul style="margin:0 0 8px;padding:0;list-style:none;font-size:.82rem;color:#cfcfd6;line-height:1.6;">'; d.fails.forEach(function(f){ h+='<li>'+f+'</li>'; }); h+='</ul>'; }
		if(d.report){ h+='<a class="mini" target="_blank" rel="noopener" style="display:inline-block;margin-bottom:6px;border-color:#22c55e;color:#fff;background:rgba(34,197,94,.34);font-weight:700;" href="'+d.report+'">Voir le rapport client</a> '; }
		if(d.report_card){ h+='<a class="mini" target="_blank" rel="noopener" style="display:inline-block;margin-bottom:8px;border-color:#e6b35a;color:#fff;background:rgba(212,180,92,.32);font-weight:700;" href="'+d.report_card+'">Image à envoyer (screenshot)</a>'; }
		if(d.msg && d.msg.perso){ h+='<div><button type="button" class="mini agmsg-copy" data-m="'+encodeURIComponent(d.msg.perso)+'" style="cursor:pointer;border-color:#d4b45c;color:#fff;background:rgba(212,180,92,.32);font-weight:700;margin-bottom:8px;">Copier — message au PROPRIÉTAIRE</button></div>'; }
		if(d.msg && d.msg.partenaire){ h+='<div><button type="button" class="mini agmsg-copy" data-m="'+encodeURIComponent(d.msg.partenaire)+'" style="cursor:pointer;border-color:#a855f7;color:#fff;background:rgba(124,58,237,.42);font-weight:700;margin-bottom:8px;">Copier — message à l\'AGENCE (partenaire %)</button></div>'; }
		if(d.is_agency){ h+='<div><button type="button" class="mini ag-agency-scan" data-url="'+(d.url||url)+'" style="cursor:pointer;border-color:#3aa3ff;color:#fff;background:rgba(58,163,255,.42);font-weight:700;margin-bottom:8px;">Cartographier ses créations (agence web détectée)</button></div>'; }
		if(num && agIsMobileFr(num)){
			var waN=(num||'').replace(/[^0-9]/g,''); if(waN.charAt(0)==='0'){ waN='33'+waN.substring(1); }
			h+='<div class="row">'
			 +'<a class="mini" target="_blank" rel="noopener" style="border-color:#25d366;color:#7ee2a8;background:rgba(37,211,102,.14);" href="https://wa.me/'+waN+'?text='+encodeURIComponent((d.msg&&d.msg.rapport)||'')+'">Rapport WhatsApp (avec image)</a>'
			 +'<a class="mini" style="border-color:#22c55e;color:#7ee2a8;" href="sms:'+num+'?body='+encodeURIComponent((d.msg&&d.msg.rapport)||'')+'">Rapport SMS</a>'
			 +'<a class="mini" style="border-color:#d4b45c;color:#e6b35a;" href="sms:'+num+'?body='+encodeURIComponent(d.msg.refonte)+'">Refonte</a>'
			 +'<a class="mini" style="border-color:#a855f7;color:#c58bff;" href="sms:'+num+'?body='+encodeURIComponent(d.msg.securite)+'">Sécurité</a>'
			 +'<a class="mini" style="border-color:#3aa3ff;color:#8fc7ff;" href="sms:'+num+'?body='+encodeURIComponent(d.msg.mixte)+'">Mixte</a>'
			 +'</div>';
		} else if(num){
			var angFixe=(d.reco==='securite')?'securite':'creation';
			h+='<div class="row"><a class="mini robot ag-robot-num" href="#" data-phone="'+num+'" data-angle="'+angFixe+'">Appel robot (fixe → SMS/WhatsApp impossibles)</a></div>';
		} else { h+='<span class="sub">Numéro fixe : SMS/WhatsApp impossibles → utilise l’appel robot.</span>'; }
		if(AG.admin){
			h+='<details style="margin-top:9px;"><summary style="cursor:pointer;color:#7fb4ff;font-size:.82rem;font-weight:600;">Résultats scan Kali (PC) — enrichir le rapport</summary>'
			 +'<textarea class="agk-txt" style="width:100%;min-height:80px;margin-top:6px;" placeholder="Colle ici le résultat de ton scan Kali pour ce site…"></textarea>'
			 +'<button type="button" class="mini agk-save" data-url="'+url+'" style="cursor:pointer;margin-top:6px;">Enregistrer pour ce site</button></details>';
		}
		h+='<div><button type="button" class="mini ag-audit-refresh" data-url="'+url+'" data-num="'+(num||'')+'" style="cursor:pointer;border-color:#8a8a92;color:#fff;background:rgba(138,138,146,.30);font-weight:700;margin-top:8px;">Réactualiser cet audit</button></div>';
		return h;
	}

	// Numéro rapide
	var num=document.getElementById('ag-num'), msg=document.getElementById('ag-msg'),
	    bC=document.getElementById('ag-call'), bS=document.getElementById('ag-sms'), bW=document.getElementById('ag-wa'), bR=document.getElementById('ag-robot');
	function telN(v){ return (v||'').replace(/[^0-9+]/g,''); }
	function waN(v){ var n=(v||'').replace(/[^0-9]/g,''); if(n.charAt(0)==='0'){ n='33'+n.substring(1); } return n; }
	// Mobile FR (06/07) → SMS/WhatsApp possibles. Fixe (01-05/09) → appel + robot seulement.
	function agIsMobileFr(v){ var d=(v||'').replace(/[^0-9]/g,''); if(!d) return true; if(d.indexOf('0033')===0)d=d.slice(4); else if(d.indexOf('33')===0&&d.length>=11)d=d.slice(2); if(d.charAt(0)==='0')d=d.slice(1); return d.charAt(0)==='6'||d.charAt(0)==='7'; }
	function refresh(){
		var t=telN(num.value), ok=t.replace(/\D/g,'').length>=6, m=encodeURIComponent(msg.value||''), mob=agIsMobileFr(num.value);
		// Appel + robot : toujours si numéro valide. SMS/WhatsApp : mobile uniquement.
		[bC].forEach(function(b){ ok?b.removeAttribute('disabled'):b.setAttribute('disabled','disabled'); });
		[bS,bW].forEach(function(b){ (ok&&mob)?b.removeAttribute('disabled'):b.setAttribute('disabled','disabled'); b.style.display=(ok&&!mob)?'none':''; });
		if(bR){ ok?bR.removeAttribute('disabled'):bR.setAttribute('disabled','disabled'); }
		if(!ok) return;
		bC.setAttribute('href','tel:'+t); bS.setAttribute('href','sms:'+t+'?body='+m); bW.setAttribute('href','https://wa.me/'+waN(num.value)+'?text='+m);
	}
	if(num){ num.addEventListener('input',refresh); msg.addEventListener('input',refresh); refresh(); }
	if(bR){ bR.addEventListener('click',function(){
		var ang=document.getElementById('ag-angle').value;
		bR.textContent='Appel en cours…'; bR.setAttribute('disabled','disabled');
		AG.post('ag_app_voice_call',{phone:num.value,angle:ang}).then(function(j){
			bR.textContent='Appel robot'; refresh();
			AG.toast(j&&j.success ? 'Le robot appelle '+num.value+' !' : (''+((j&&j.data&&j.data.m)||'Échec')));
		}).catch(function(){ bR.textContent='Appel robot'; refresh(); AG.toast('Erreur réseau'); });
	}); }

	// Appel robot sur un numéro donné (utilisé dans le résultat d'audit pour les fixes).
	window.agRobotNum = function(el, ph, ang){
		if(!confirm('Le robot Emma va appeler '+ph+' (angle '+(ang==='securite'?'sécurité':'création de site')+'). Lancer ?')) return;
		var lbl=el.innerHTML; el.textContent='…';
		AG.post('ag_app_voice_call',{phone:ph,angle:ang}).then(function(j){
			el.innerHTML=lbl; AG.toast(j&&j.success ? 'Le robot appelle '+ph+' !' : (''+((j&&j.data&&j.data.m)||'Échec')));
		}).catch(function(){ el.innerHTML=lbl; AG.toast('Erreur réseau'); });
	};
	// Robot sur les boutons injectés dynamiquement (résultat d'audit d'un fixe).
	document.addEventListener('click',function(e){
		var a=e.target.closest?e.target.closest('.ag-robot-num'):null; if(!a) return;
		e.preventDefault(); agRobotNum(a, a.getAttribute('data-phone'), a.getAttribute('data-angle')||'creation');
	});
	// Réactualiser UN audit (ignore le cache) → remplace le résultat sur place.
	document.addEventListener('click',function(e){
		var b=e.target.closest?e.target.closest('.ag-audit-refresh'):null; if(!b) return; e.preventDefault();
		var host=b.closest('.audit-res'); if(!host) return;
		var u=b.getAttribute('data-url'), num=b.getAttribute('data-num')||'';
		host.innerHTML='<span class="sub">Réanalyse en cours…</span>';
		AG.post('ag_app_audit',{url:u,mode:'light',fresh:1,phone:num}).then(function(j){
			if(j&&j.success){ host.innerHTML=agAuditHTML(u, j.data, num); AG.toast('Audit réactualisé : '+j.data.score+'/100'); }
			else { host.innerHTML='<span class="sub">'+((j&&j.data&&j.data.m)||'Erreur')+'</span>'; }
		}).catch(function(){ host.innerHTML='<span class="sub">Erreur réseau</span>'; });
	});

	// « Tout réactualiser » : recalcule tous les sites audités (par lots) + maj des prospects.
	var refAll=document.getElementById('ag-refresh-all');
	if(refAll){ refAll.addEventListener('click',function(){
		if(!confirm('Recalculer la note de tous les sites déjà audités ? Ça peut prendre un moment (par lots).')) return;
		refAll.setAttribute('disabled','disabled'); var total=0;
		function batch(){ refAll.textContent='Actualisation… ('+total+')';
			AG.post('ag_app_audit_refresh_all',{}).then(function(j){
				if(!j||!j.success){ refAll.removeAttribute('disabled'); refAll.textContent='Tout réactualiser'; AG.toast('Erreur'); return; }
				total+=j.data.done;
				if(j.data.remaining>0 && j.data.done>0){ batch(); }
				else { refAll.removeAttribute('disabled'); refAll.textContent='Tout réactualiser'; AG.toast(''+total+' audit(s) réactualisé(s). Recharge pour voir les notes.'); }
			}).catch(function(){ refAll.removeAttribute('disabled'); refAll.textContent='Tout réactualiser'; AG.toast('Erreur réseau'); });
		}
		batch();
	}); }

	// Copier le message perso d'audit (à coller où on veut : SMS, WhatsApp, email, DM).
	document.addEventListener('click',function(e){
		var b=e.target.closest?e.target.closest('.agmsg-copy'):null; if(!b) return;
		e.preventDefault(); var txt=decodeURIComponent(b.getAttribute('data-m')||'');
		var done=function(){ var old=b.textContent; b.textContent='Copié !'; setTimeout(function(){ b.textContent=old; },1600); AG.toast('Message copié'); };
		if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(txt).then(done).catch(function(){ window.prompt('Copie le message :', txt); }); }
		else { window.prompt('Copie le message :', txt); }
	});
	// Appel robot par prospect (angle intelligent : sécurité si site, création sinon)
	document.querySelectorAll('.ag-robot-one').forEach(function(a){
		a.addEventListener('click',function(e){ e.preventDefault();
			var ph=a.getAttribute('data-phone'), nm=a.getAttribute('data-name'), ang=a.getAttribute('data-angle')||'creation';
			if(!confirm('Le robot Emma va appeler '+(nm||ph)+' (angle '+(ang==='securite'?'sécurité':'création de site')+'). Lancer ?')) return;
			var lbl=a.innerHTML; a.textContent='…';
			AG.post('ag_app_voice_call',{phone:ph,name:nm,angle:ang}).then(function(j){
				a.innerHTML=lbl; AG.toast(j&&j.success ? 'Le robot appelle '+(nm||ph)+' !' : (''+((j&&j.data&&j.data.m)||'Échec')));
			}).catch(function(){ a.innerHTML=lbl; AG.toast('Erreur réseau'); });
		});
	});

	// Filtres (chips) des prospects
	var chips=document.getElementById('ag-chips');
	if(chips){
		var flt={grp:'all',kind:'all',rep:'all'};
		function applyFlt(){
			document.querySelectorAll('#view-prospecter .p').forEach(function(p){
				var okg=(flt.grp==='all'||p.getAttribute('data-grp')===flt.grp);
				var okk=(flt.kind==='all'||p.getAttribute('data-kind')===flt.kind);
				var okr=(flt.rep==='all'||p.getAttribute('data-rep')===flt.rep);
				p.style.display=(okg&&okk&&okr)?'':'none';
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
				if(j&&j.success){ var card=b.closest('.p'); if(card){ card.style.display='none'; } AG.toast('Supprimé'); }
				else { b.textContent=''; AG.toast(''+((j&&j.data&&j.data.m)||'Erreur')); }
			}).catch(function(){ b.textContent=''; AG.toast('Erreur réseau'); });
		});
	});

	// Note auto du contact
	document.querySelectorAll('.ag-touch').forEach(function(a){
		a.addEventListener('click',function(){ var id=a.getAttribute('data-id'), ch=a.getAttribute('data-channel'); if(id){ AG.post('ag_amb_touch',{id:id,channel:ch}).catch(function(){}); } });
	});

	// « A répondu » : bascule le statut réponse (suivi lié au message)
	document.querySelectorAll('.ag-reply').forEach(function(b){
		b.addEventListener('click',function(){
			var id=b.getAttribute('data-id'), cur=b.getAttribute('data-rep')==='1', nv=cur?0:1;
			b.textContent='…';
			AG.post('ag_app_prospect_reply',{id:id,replied:nv}).then(function(j){
				if(j&&j.success){ b.setAttribute('data-rep',nv); b.textContent=nv?'A répondu':'A répondu ?';
					var card=b.closest('.p'); if(card){ card.setAttribute('data-rep',nv); } AG.toast(nv?'Marqué : a répondu':'Réponse annulée'); }
				else { b.textContent=cur?'A répondu':'A répondu ?'; AG.toast('Erreur'); }
			}).catch(function(){ b.textContent=cur?'A répondu':'A répondu ?'; });
		});
	});

	// Appeler TOUS les prospects visibles au robot (tout numéro : 06/02/08/09)
	var robotAll=document.getElementById('ag-robot-all');
	if(robotAll){ robotAll.addEventListener('click',function(){
		var ids=[]; document.querySelectorAll('#view-prospecter .p').forEach(function(p){ if(p.style.display!=='none'){ var id=p.getAttribute('data-id'); if(id) ids.push(id); } });
		if(!ids.length){ AG.toast('Aucun prospect affiché.'); return; }
		if(!confirm('Le robot Emma va appeler '+ids.length+' prospect(s) affiché(s), un par un. Lancer ?')) return;
		robotAll.textContent='Lancement…'; robotAll.setAttribute('disabled','disabled');
		var fd={}; ids.forEach(function(id,i){ fd['ids['+i+']']=id; });
		AG.post('ag_app_voice_bulk',fd).then(function(j){
			robotAll.removeAttribute('disabled'); robotAll.textContent='Appeler tout le monde au robot (visibles)';
			AG.toast(j&&j.success?(''+j.data.ok+' appel(s) lancé(s)'+(j.data.ko?(' · '+j.data.ko+' échec(s)'):'')+(j.data.capped?' (limité à 60)':'')):(''+((j&&j.data&&j.data.m)||'Erreur')));
		}).catch(function(){ robotAll.removeAttribute('disabled'); robotAll.textContent='Appeler tout le monde au robot (visibles)'; AG.toast('Erreur réseau'); });
	}); }

	// Audit d'un site (léger / avancé) → note + SMS auto selon la note
	document.querySelectorAll('.ag-audit').forEach(function(btn){
		btn.addEventListener('click',function(){
			var w=btn.closest('.audit-wrap'); if(!w) return;
			var url=w.getAttribute('data-url'), id=w.getAttribute('data-id'), nm=w.getAttribute('data-name'), num=w.getAttribute('data-num');
			var res=w.querySelector('.audit-res'), mode=btn.getAttribute('data-mode');
			res.innerHTML='<span class="sub">Audit en cours…</span>';
			AG.post('ag_app_audit',{url:url,mode:mode,id:id,name:nm,phone:num}).then(function(j){
				if(!j||!j.success){ res.innerHTML='<span class="sub">'+((j&&j.data&&j.data.m)||'Erreur')+'</span>'; return; }
				res.innerHTML=agAuditHTML(url, j.data, num);
			}).catch(function(){ res.innerHTML='<span class="sub">Erreur réseau</span>'; });
		});
	});

	// ── Recherche + recherches SAUVEGARDÉES (accordéon, jamais de longue liste) ──
	var box=document.getElementById('ag-results'), saved=document.getElementById('ag-saved');
	var sb=document.getElementById('ag-search'), city=document.getElementById('ag-city');
	var savedData=[], savedSort='recent';
	var AG_SALE=<?php echo wp_json_encode( $ag_sale_link ); ?>;
	var AG_TEL=<?php echo wp_json_encode( preg_replace( '/[^0-9+]/', '', (string) get_option( 'ag_tester_phone', '0744829516' ) ) ); ?>;

	// Une ligne « entreprise » réutilisée dans la recherche live ET les recherches sauvegardées.
	function agItemRow(it){
		var tel=(it.phone_intl||it.phone||'').replace(/[^0-9+]/g,'');
		var d=document.createElement('div'); d.className='res';
		var stars=(it.rating>0)?(' · '+it.rating+(it.reviews?(' ('+it.reviews+')'):'')):'';
		var cat=it.type||it.kind||'';
		// Badge « besoin d'un site ? » : vrai site => a déjà un site (mauvaise cible) ; sinon => bonne cible.
		var needBadge = it.real
			? '<span style="color:#8a8a92;">a déjà un site</span>'
			: '<span style="color:#2ecc71;font-weight:700;">pas de vrai site — bonne cible</span>';
		d.innerHTML='<div class="rn">'+(it.name||'')+'</div><div class="rk">'+(it.city||'')+(cat?(' · '+cat):'')+stars+(it.exists?' · déjà en base':'')+'</div><div class="rk" style="margin-top:2px;">'+needBadge+'</div>';
		if(it.website){ var lk=document.createElement('a'); lk.className='rlink'; lk.href=it.website; lk.target='_blank'; lk.rel='noopener'; lk.textContent=''+String(it.website).replace(/^https?:\/\//,'').replace(/\/$/,''); d.appendChild(lk); }
		var row=document.createElement('div'); row.className='row'; row.style.cssText='display:flex;gap:7px;flex-wrap:wrap;margin-top:8px;';
		if(tel){ var ta=document.createElement('a'); ta.className='mini'; ta.href='tel:'+tel; ta.textContent=''; row.appendChild(ta); }
		if(it.website){ var vs=document.createElement('a'); vs.className='mini'; vs.href=it.website; vs.target='_blank'; vs.rel='noopener'; vs.textContent='Voir le site'; row.appendChild(vs); }
		// Lire les avis Google (ouvre la fiche Maps de l'établissement).
		var mapsUrl=it.maps||('https://www.google.com/maps/search/?api=1&query='+encodeURIComponent((it.name||'')+' '+(it.city||'')));
		var av=document.createElement('a'); av.className='mini'; av.href=mapsUrl; av.target='_blank'; av.rel='noopener'; av.style.cssText='border-color:#e6b35a;color:#e6b35a;'; av.textContent='Avis Google'+(it.reviews?(' ('+it.reviews+')'):''); row.appendChild(av);
		var add=document.createElement('button'); add.className='mini'; add.style.cursor='pointer'; add.textContent=it.exists?'En base':'+ Ajouter';
		if(it.exists){ add.setAttribute('disabled','disabled'); }
		add.addEventListener('click',function(){ add.textContent='…';
			AG.post('ag_amb_add',{name:it.name,type:it.type,city:it.city,phone:it.phone,phone_intl:it.phone_intl,website:it.website}).then(function(r){
				add.textContent=(r&&r.success)?'Ajouté':'Erreur'; if(r&&r.success){ add.setAttribute('disabled','disabled'); } AG.toast((r&&r.success)?'Ajouté à tes prospects':'Erreur');
			}).catch(function(){ add.textContent='Erreur'; }); });
		row.appendChild(add);
		var rep=document.createElement('div'); rep.className='audit-res'; rep.style.marginTop='8px';
		if(it.website){
			var aud=document.createElement('button'); aud.className='mini'; aud.style.cssText='cursor:pointer;border-color:#7c3aed;color:#c58bff;'; aud.textContent='Auditer le site';
			aud.addEventListener('click',function(){ rep.innerHTML='<span class="sub">Audit en cours…</span>';
				AG.post('ag_app_audit',{url:it.website,mode:'light',name:it.name,phone:tel}).then(function(r){
					if(!r||!r.success){ rep.innerHTML='<span class="sub">'+((r&&r.data&&r.data.m)||'Erreur')+'</span>'; return; }
					rep.innerHTML=agAuditHTML(it.website, r.data, tel);
				}).catch(function(){ rep.innerHTML='<span class="sub">Erreur réseau</span>'; }); });
			row.appendChild(aud);
		}
		// Un template existe pour ce métier → propose-le (voir/télécharger) + message « site personnalisé ».
		if(it.demo){
			var trow=document.createElement('div'); trow.className='row'; trow.style.cssText='display:flex;gap:7px;flex-wrap:wrap;margin-top:7px;';
			var slug=String(it.demo).split('/wordpress-')[1]||''; var met=slug?(slug.charAt(0).toUpperCase()+slug.slice(1)):'';
			var tv=document.createElement('a'); tv.className='mini'; tv.href=it.demo; tv.target='_blank'; tv.rel='noopener'; tv.style.cssText='border-color:#d4b45c;color:#e6b35a;'; tv.textContent='Modèle '+met+' (voir / télécharger)'; trow.appendChild(tv);
			if(tel){ var prop=document.createElement('a'); prop.className='mini'; prop.style.cssText='border-color:#25d366;color:#7ee2a8;';
				var m='Bonjour, j\'ai vu '+(it.name||'votre établissement')+'. Je peux vous créer un site web pro comme ce modèle : '+it.demo+' — clé en main ou 100% personnalisé. On en parle ? '+(AG_SALE||('Tel : '+AG_TEL));
				prop.href='sms:'+tel+'?body='+encodeURIComponent(m); prop.textContent='Proposer un site'; trow.appendChild(prop); }
			d.appendChild(trow);
		}
		d.appendChild(row); d.appendChild(rep); return d;
	}

	function renderSaved(){
		if(!saved) return;
		var arr=savedData.slice();
		if(savedSort==='az') arr.sort(function(a,b){ return String(a.q||a.city||'').localeCompare(String(b.q||b.city||'')); });
		else if(savedSort==='big') arr.sort(function(a,b){ return (b.count||0)-(a.count||0); });
		else arr.sort(function(a,b){ return (b.ts||0)-(a.ts||0); });
		if(!arr.length){ saved.innerHTML='<p class="sub">Aucune recherche sauvegardée. Lance une recherche ci-dessus (ex : « massage Nantes »).</p>'; return; }
		saved.innerHTML='';
		arr.forEach(function(s){
			var det=document.createElement('details'); det.className='acc';
			var sum=document.createElement('summary');
			var title=String(s.q||s.city||'Recherche'); title=title.charAt(0).toUpperCase()+title.slice(1);
			sum.innerHTML='<span>'+title+'</span><span class="cnt">'+(s.count||s.items.length)+'</span>';
			var del=document.createElement('button'); del.className='adel'; del.title='Supprimer cette recherche'; del.textContent='';
			del.addEventListener('click',function(e){ e.preventDefault(); e.stopPropagation(); if(!confirm('Supprimer la recherche « '+title+' » ?')) return;
				AG.post('ag_app_search_del',{key:s.key}).then(function(){ savedData=savedData.filter(function(x){return x.key!==s.key;}); renderSaved(); AG.toast('Recherche supprimée'); }); });
			var chev=document.createElement('span'); chev.className='chev'; chev.textContent='▸';
			sum.appendChild(del); sum.appendChild(chev); det.appendChild(sum);
			var body=document.createElement('div'); body.className='accb';
			det.addEventListener('toggle',function(){ if(det.open && !body.getAttribute('data-filled')){ body.setAttribute('data-filled','1');
				<?php if ( $ag_robot_ok ) : ?>
				var phones=(s.items||[]).map(function(it){ return (it.phone_intl||it.phone||''); }).filter(function(x){ return x && x.replace(/\D/g,'').length>=6; });
				if(phones.length){ var rb=document.createElement('button'); rb.className='b'; rb.style.cssText='background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;margin:2px 0 10px;'; rb.textContent='Appeler tous au robot ('+phones.length+')';
					rb.addEventListener('click',function(){ if(!confirm('Le robot va appeler les '+phones.length+' entreprise(s) de cette recherche. Lancer ?')) return; rb.textContent='Lancement…'; rb.setAttribute('disabled','disabled');
						var fd={}; phones.forEach(function(p,i){ fd['phones['+i+']']=p; });
						AG.post('ag_app_voice_bulk',fd).then(function(j){ rb.removeAttribute('disabled'); rb.textContent='Appeler tous au robot ('+phones.length+')'; AG.toast(j&&j.success?(''+j.data.ok+' appel(s) lancé(s)'+(j.data.capped?' (limité à 60)':'')):(''+((j&&j.data&&j.data.m)||'Erreur'))); }).catch(function(){ rb.removeAttribute('disabled'); AG.toast('Erreur réseau'); }); });
					body.appendChild(rb); }
				<?php endif; ?>
				(s.items||[]).forEach(function(it){ body.appendChild(agItemRow(it)); }); } });
			det.appendChild(body); saved.appendChild(det);
		});
	}

	function agLoadSaved(){ if(!saved) return; AG.post('ag_app_searches',{}).then(function(j){ savedData=(j&&j.success&&j.data.searches)||[]; renderSaved(); }).catch(function(){ saved.innerHTML='<p class="sub">Erreur de chargement.</p>'; }); }

	var sortbar=document.getElementById('ag-sort');
	if(sortbar){ sortbar.querySelectorAll('button').forEach(function(b){ b.addEventListener('click',function(){ sortbar.querySelectorAll('button').forEach(function(x){ x.classList.remove('on'); }); b.classList.add('on'); savedSort=b.getAttribute('data-s'); renderSaved(); }); }); }

	// Résultats de recherche : filtre « sans site » + tri (cible / avis / note).
	var freshItems=[], freshLocked=0, freshNoSiteOnly=false, freshSort='cible';
	function agRenderFresh(){
		if(!box) return;
		box.innerHTML='';
		if(!freshItems.length){ box.innerHTML='<p class="sub">Aucun résultat.</p>'; return; }
		// Barre filtre + tri
		var bar=document.createElement('div'); bar.style.cssText='display:flex;gap:7px;flex-wrap:wrap;align-items:center;margin:0 0 10px;';
		var f=document.createElement('button'); f.className='mini'; f.style.cursor='pointer'; f.textContent=freshNoSiteOnly?'Sans site ':'Sans site seulement';
		if(freshNoSiteOnly){ f.style.cssText+='border-color:#2ecc71;color:#2ecc71;font-weight:700;'; }
		f.addEventListener('click',function(){ freshNoSiteOnly=!freshNoSiteOnly; agRenderFresh(); });
		bar.appendChild(f);
		var sorts=[['cible','Meilleure cible'],['avis','+ d\'avis'],['note','Meilleure note']];
		sorts.forEach(function(s){ var b=document.createElement('button'); b.className='mini'; b.style.cursor='pointer'; b.textContent=s[1];
			if(freshSort===s[0]){ b.style.cssText+='border-color:#3aa3ff;color:#8fc7ff;font-weight:700;'; }
			b.addEventListener('click',function(){ freshSort=s[0]; agRenderFresh(); }); bar.appendChild(b); });
		box.appendChild(bar);
		if(freshLocked>0){ var lk=document.createElement('p'); lk.className='sub'; lk.style.color='#e6b35a'; lk.textContent=''+freshLocked+' résultat(s) masqué(s) : région réservée par un autre ambassadeur.'; box.appendChild(lk); }
		var list=freshItems.slice();
		if(freshNoSiteOnly){ list=list.filter(function(it){ return !it.real; }); }
		if(freshSort==='avis'){ list.sort(function(a,b){ return (b.reviews||0)-(a.reviews||0); }); }
		else if(freshSort==='note'){ list.sort(function(a,b){ return (b.rating||0)-(a.rating||0); }); }
		// 'cible' = ordre serveur (score) : sans-site d'abord.
		if(!list.length){ var e=document.createElement('p'); e.className='sub'; e.textContent='Aucun résultat sans site. Décoche le filtre.'; box.appendChild(e); return; }
		list.forEach(function(it){ box.appendChild(agItemRow(it)); });
	}
	if(sb){ sb.addEventListener('click',function(){
		var c=(city.value||'').trim(); if(!c){ AG.toast('Indique une ville'); return; }
		sb.textContent='Recherche…'; sb.setAttribute('disabled','disabled'); box.innerHTML='';
		AG.post('ag_amb_search',{city:c}).then(function(j){
			sb.textContent='Lancer la recherche'; sb.removeAttribute('disabled');
			if(!j||!j.success){ AG.toast(''+((j&&j.data&&j.data.m)||'Erreur')); return; }
			freshItems=j.data.items||[]; freshLocked=(j.data.locked||0); freshSort='cible';
			agRenderFresh();
			if(typeof j.data.left!=='undefined'){ AG.toast('Recherches restantes ce mois : '+j.data.left); }
			agLoadSaved(); // la recherche vient d'être sauvegardée → recharge l'accordéon
		}).catch(function(){ sb.textContent='Lancer la recherche'; sb.removeAttribute('disabled'); AG.toast('Erreur réseau'); });
	}); }
	agLoadSaved();

	// ── Cartographie des AGENCES web (agence → ses créations, pire → meilleur) ──
	var agencies=document.getElementById('ag-agencies');
	function agScoreCol(s){ return s<50?'#ff6b6b':(s<75?'#e6b35a':'#2ecc71'); }
	function agLoadAgencies(){ if(!agencies) return; AG.post('ag_app_agencies',{}).then(function(j){
		var arr=(j&&j.success&&j.data.agencies)||[];
		if(!arr.length){ agencies.innerHTML='<p class="sub">Aucune agence cartographiée. Audite le site d\'une agence web puis clique « Cartographier ses créations ».</p>'; return; }
		agencies.innerHTML='';
		arr.forEach(function(ag){
			var det=document.createElement('details'); det.className='acc';
			var sum=document.createElement('summary'); var col=agScoreCol(ag.score||0);
			sum.innerHTML='<span>'+(ag.host||'')+' <span style="color:'+col+';font-weight:800;">'+(ag.score||0)+'/100</span></span><span class="cnt">'+(ag.n||0)+' site'+((ag.n||0)>1?'s':'')+((ag.n)?(' · moy '+ag.avg):'')+'</span>';
			var del=document.createElement('button'); del.className='adel'; del.title='Retirer'; del.textContent='';
			del.addEventListener('click',function(e){ e.preventDefault(); e.stopPropagation(); if(!confirm('Retirer cette agence de la carte ?')) return; AG.post('ag_app_agency_del',{key:ag.key||''}).then(function(){ agLoadAgencies(); AG.toast('Retiré'); }); });
			var chev=document.createElement('span'); chev.className='chev'; chev.textContent='▸'; sum.appendChild(del); sum.appendChild(chev); det.appendChild(sum);
			var body=document.createElement('div'); body.className='accb';
			det.addEventListener('toggle',function(){ if(det.open && !body.getAttribute('data-filled')){ body.setAttribute('data-filled','1');
				var av=document.createElement('a'); av.className='mini'; av.href=ag.url; av.target='_blank'; av.rel='noopener'; av.style.cssText='border-color:#3aa3ff;color:#8fc7ff;margin:0 0 10px;display:inline-block;'; av.textContent='Voir le site de l\'agence'; body.appendChild(av);
				(ag.creations||[]).forEach(function(c){
					var head=document.createElement('div'); head.className='rk'; head.style.margin='8px 0 -2px';
					head.innerHTML='<span style="color:'+agScoreCol(c.score||0)+';font-weight:800;">'+(c.score||0)+'/100</span>'+((c.crit||0)>0?(' · '+c.crit+' faille(s)'):'')+' — création de l\'agence';
					body.appendChild(head);
					body.appendChild(agItemRow({name:c.host, website:c.url, city:'', type:'client de l\'agence'}));
				});
				if(!(ag.creations||[]).length){ var em=document.createElement('p'); em.className='sub'; em.textContent='Aucune création détectée automatiquement (portfolio non public ou liens absents).'; body.appendChild(em); }
			} });
			det.appendChild(body); agencies.appendChild(det);
		});
	}).catch(function(){}); }

	// Bouton « Cartographier ses créations » (injecté dans le résultat d'audit d'une agence).
	document.addEventListener('click',function(e){
		var b=e.target.closest?e.target.closest('.ag-agency-scan'):null; if(!b) return; e.preventDefault();
		var u=b.getAttribute('data-url'); if(!u) return;
		if(!confirm('Cartographier les créations de cette agence ? On analyse plusieurs sites, ça peut prendre 20 à 40 secondes.')) return;
		var old=b.textContent; b.textContent='Analyse en cours… patiente'; b.setAttribute('disabled','disabled');
		AG.post('ag_app_agency_scan',{url:u}).then(function(j){ b.textContent=old; b.removeAttribute('disabled');
			if(j&&j.success){ AG.toast(''+(((j.data.agency)&&j.data.agency.n)||0)+' création(s) trouvée(s) — voir « Agences web » (onglet Chercher)'); agLoadAgencies(); }
			else { AG.toast(''+((j&&j.data&&j.data.m)||'Erreur')); }
		}).catch(function(){ b.textContent=old; b.removeAttribute('disabled'); AG.toast('Erreur réseau (scan peut-être trop long)'); });
	});
	agLoadAgencies();

	// Audit manuel (onglet Audit) : lance l'audit sur l'URL saisie et affiche le rapport dans l'app
	var arun=document.getElementById('ag-audit-run'), aurl=document.getElementById('ag-audit-url');
	if(arun){ arun.addEventListener('click',function(){
		var u=(aurl.value||'').trim(); if(!u){ AG.toast('Colle une adresse de site'); return; }
		if(!/^https?:\/\//i.test(u)){ u='https://'+u; }
		var res=document.getElementById('ag-audit-manual').querySelector('.audit-res');
		res.innerHTML='<span class="sub">Audit en cours…</span>'; arun.setAttribute('disabled','disabled'); arun.textContent='Audit…';
		AG.post('ag_app_audit',{url:u,mode:'light'}).then(function(j){
			arun.removeAttribute('disabled'); arun.textContent='Lancer l\'audit';
			if(!j||!j.success){ res.innerHTML='<span class="sub">'+((j&&j.data&&j.data.m)||'Erreur')+'</span>'; return; }
			res.innerHTML=agAuditHTML(u, j.data, '');
		}).catch(function(){ arun.removeAttribute('disabled'); arun.textContent='Lancer l\'audit'; res.innerHTML='<span class="sub">Erreur réseau</span>'; });
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
		var r=document.getElementById('ag-bulk-res'); r.textContent='Envoi en cours…'; bulkSend.setAttribute('disabled','disabled'); bulkSend.textContent='Envoi…';
		AG.post('ag_app_bulk_sms',{numbers:nums,msg:m}).then(function(j){
			bulkSend.removeAttribute('disabled'); bulkSend.textContent='Envoyer en masse';
			if(j&&j.success){ r.innerHTML=''+j.data.ok+' envoyés · '+j.data.ko+' échecs (sur '+j.data.total+'). Recharge la page pour voir le suivi.'; }
			else { r.textContent=''+((j&&j.data&&j.data.m)||'Erreur'); }
		}).catch(function(){ bulkSend.removeAttribute('disabled'); bulkSend.textContent='Envoyer en masse'; r.textContent='Erreur réseau'; });
	}); }

	// Enregistrer un résultat Kali (délégué car injecté dynamiquement)
	document.addEventListener('click',function(e){
		var b=e.target && e.target.closest ? e.target.closest('.agk-save') : null; if(!b) return;
		var det=b.closest('details'), txt=det?det.querySelector('.agk-txt'):null, url=b.getAttribute('data-url');
		b.textContent='…';
		AG.post('ag_app_kali_save',{url:url,kali:txt?txt.value:''}).then(function(j){
			b.textContent=(j&&j.success)?'Enregistré':'Erreur'; AG.toast((j&&j.success)?'Résultat Kali enregistré (rapport enrichi)':'Erreur');
		}).catch(function(){ b.textContent='Enregistrer pour ce site'; AG.toast('Erreur réseau'); });
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

	// ── Appels d'offres publics (marchés) — onglet ADMIN uniquement ──
	var aoLoaded=false;
	function aoEsc(s){ return String(s==null?'':s).replace(/[&<>"]/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }
	function aoRender(items,total){
		var box=document.getElementById('ao-list'); if(!box) return;
		if(!items || !items.length){ box.innerHTML='<div class="card"><p class="sub" style="margin:0;">Aucun marché ouvert pour ces critères. Vide le département ou reviens plus tard.</p></div>'; return; }
		var h='<p class="sub" style="padding:4px 2px;">'+total+' marché(s) ouvert(s) — les plus urgents en premier.</p>';
		items.forEach(function(it){
			var j=it.jours, col=(j!=null&&j<=7)?'#ff6b6b':((j!=null&&j<=15)?'#e6b35a':'#2ecc71');
			h+='<div class="card" style="padding:13px;">';
			h+='<strong>'+aoEsc(it.acheteur)+'</strong>';
			h+='<div class="sub" style="font-size:.82rem;margin:4px 0 8px;">'+aoEsc(it.objet).slice(0,160)+'</div>';
			h+='<div class="sub" style="font-size:.78rem;margin-bottom:8px;">'+(it.dept?(''+aoEsc(it.dept)+' · '):'')+'⏳ <span style="color:'+col+';font-weight:700;">'+aoEsc(it.limite)+(j!=null?(' ('+j+' j)'):'')+'</span></div>';
			h+='<a href="'+aoEsc(it.cand)+'" target="_blank" rel="noopener" class="b gold" style="display:block;text-align:center;text-decoration:none;margin-bottom:6px;">Préparer le dossier</a>';
			if(it.url){ h+='<a href="'+aoEsc(it.url)+'" target="_blank" rel="noopener" class="mini" style="display:inline-block;text-decoration:none;cursor:pointer;">Voir l\'avis officiel</a>'; }
			h+='</div>';
		});
		box.innerHTML=h;
	}
	AG.loadAO=function(force){
		var box=document.getElementById('ao-list'); if(!box) return; // pas admin → onglet absent
		if(aoLoaded && !force) return;
		box.innerHTML='<p class="sub" style="padding:6px 2px;">Chargement…</p>';
		var dept=(document.getElementById('ao-dept')||{}).value||'';
		AG.post('ag_app_boamp',{dept:dept}).then(function(j){
			aoLoaded=true;
			if(j&&j.success){ aoRender(j.data.items, j.data.total); }
			else { box.innerHTML='<div class="card"><p class="sub" style="margin:0;">Impossible de charger les marchés. Réessaie dans un instant.</p></div>'; }
		}).catch(function(){ box.innerHTML='<div class="card"><p class="sub" style="margin:0;">Erreur réseau.</p></div>'; });
	};
	var aoBtn=document.getElementById('ao-filter');
	if(aoBtn){ aoBtn.addEventListener('click',function(){ AG.loadAO(true); }); }

	// ── Missions (ambassadeurs) ──
	function misEsc(s){ return String(s==null?'':s).replace(/[&<>"]/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }
	function misCard(m){
		var c=document.createElement('div'); c.className='card'; c.style.padding='13px';
		var full=(m.taken>=m.slots)&&!m.reserved;
		var h='<div style="display:flex;justify-content:space-between;gap:8px;align-items:baseline;"><strong>'+misEsc(m.title)+'</strong>'+(m.prime?('<span style="color:#e6b35a;font-weight:800;white-space:nowrap;">'+misEsc(m.prime)+' €</span>'):'')+'</div>';
		if(m.desc){ h+='<div class="sub" style="font-size:.84rem;margin:5px 0;">'+misEsc(m.desc)+'</div>'; }
		h+='<div class="sub" style="font-size:.76rem;margin-bottom:8px;">'+(m.city?(''+misEsc(m.city)+' · '):'')+''+m.taken+'/'+m.slots+' places'+(m.deadline?(' · ⏳ '+misEsc(m.deadline)):'')+'</div>';
		c.innerHTML=h;
		if(m.sub==='valide'){ var okd=document.createElement('div'); okd.className='sub'; okd.style.color='#2ecc71'; okd.textContent='Mission validée — prime en route'; c.appendChild(okd); return c; }
		if(m.sub==='pending'){ var pd=document.createElement('div'); pd.className='sub'; pd.style.color='#e6b35a'; pd.textContent='⏳ Rendu envoyé — en attente de validation'; c.appendChild(pd); }
		if(!m.reserved){
			if(full){ var fd0=document.createElement('div'); fd0.className='sub'; fd0.textContent='Complet'; c.appendChild(fd0); return c; }
			var rb=document.createElement('button'); rb.className='b gold'; rb.style.cssText='display:block;width:100%;'; rb.textContent='Réserver ma place';
			rb.addEventListener('click',function(){ rb.disabled=true; rb.textContent='…';
				AG.post('ag_app_mission_reserve',{id:m.id}).then(function(j){ if(j&&j.success){ AG.toast('Place réservée'); AG.loadMissions(true); } else { rb.disabled=false; rb.textContent='Réserver ma place'; AG.toast(''+((j&&j.data&&j.data.m)||'Erreur')); } }).catch(function(){ rb.disabled=false; rb.textContent='Réserver ma place'; }); });
			c.appendChild(rb); return c;
		}
		if(m.sub!=='pending'){ var rd=document.createElement('div'); rd.className='sub'; rd.style.color='#2ecc71'; rd.textContent='Réservée — envoie ton rendu ci-dessous'; c.appendChild(rd); }
		var note=document.createElement('textarea'); note.placeholder='Note (ce que tu as fait)'; note.rows=2; note.style.cssText='width:100%;margin:6px 0;padding:9px;border-radius:8px;border:1px solid #333;background:#0e0e13;color:#fff;'; c.appendChild(note);
		var leads=document.createElement('textarea'); leads.placeholder='Prospects trouvés — 1 par ligne : Nom, Téléphone, Ville'; leads.rows=3; leads.style.cssText='width:100%;margin:0 0 6px;padding:9px;border-radius:8px;border:1px solid #333;background:#0e0e13;color:#fff;'; c.appendChild(leads);
		var flab=document.createElement('label'); flab.className='sub'; flab.style.cssText='display:block;font-size:.78rem;margin:2px 0;'; flab.textContent='Photo (optionnel)'; c.appendChild(flab);
		var photo=document.createElement('input'); photo.type='file'; photo.accept='image/*'; photo.style.marginBottom='8px'; c.appendChild(photo);
		var sb=document.createElement('button'); sb.className='b gold'; sb.style.cssText='display:block;width:100%;'; sb.textContent='Envoyer mon rendu';
		sb.addEventListener('click',function(){
			if(!note.value.trim() && !leads.value.trim() && !(photo.files&&photo.files[0])){ AG.toast('Ajoute une note, des prospects ou une photo'); return; }
			sb.disabled=true; sb.textContent='Envoi…';
			var fd=new FormData(); fd.append('action','ag_app_mission_submit'); fd.append('_n',AG.N); fd.append('id',m.id); fd.append('note',note.value); fd.append('leads',leads.value);
			if(photo.files&&photo.files[0]) fd.append('photo',photo.files[0]);
			fetch(AG.AJAX,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(j){
				if(j&&j.success){ AG.toast('Rendu envoyé !'); AG.loadMissions(true); } else { sb.disabled=false; sb.textContent='Envoyer mon rendu'; AG.toast(''+((j&&j.data&&j.data.m)||'Erreur')); }
			}).catch(function(){ sb.disabled=false; sb.textContent='Envoyer mon rendu'; AG.toast('Erreur réseau'); });
		});
		c.appendChild(sb);
		return c;
	}
	var misLoaded=false;
	AG.loadMissions=function(force){
		var box=document.getElementById('mis-list'); if(!box) return;
		if(misLoaded && !force) return;
		box.innerHTML='<p class="sub" style="padding:6px 2px;">Chargement…</p>';
		AG.post('ag_app_missions',{}).then(function(j){
			misLoaded=true; box.innerHTML='';
			var d=(j&&j.success&&j.data)||{};
			// Mon solde primes
			var e=d.earn||{pending:0,paid:0};
			if((e.pending||0)>0||(e.paid||0)>0){
				var eb=document.createElement('div'); eb.className='card'; eb.style.padding='12px';
				eb.innerHTML='<div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;"><span class="sub">Mes primes de mission</span></div>'
					+'<div style="display:flex;gap:16px;margin-top:6px;"><div><div style="font-size:1.3rem;font-weight:800;color:#e6b35a;">'+(e.pending||0).toFixed(2).replace(".",",")+' €</div><div class="sub" style="font-size:.72rem;">en attente</div></div>'
					+'<div><div style="font-size:1.3rem;font-weight:800;color:#2ecc71;">'+(e.paid||0).toFixed(2).replace(".",",")+' €</div><div class="sub" style="font-size:.72rem;">déjà payé</div></div></div>';
				box.appendChild(eb);
			}
			// Classement top missions
			var top=d.top||[];
			if(top.length){
				var tb=document.createElement('div'); tb.className='card'; tb.style.padding='12px';
				var med=['','',''];
				var th='<div class="sub" style="margin-bottom:6px;">Top missions</div>';
				top.forEach(function(r,i){ th+='<div style="display:flex;justify-content:space-between;padding:3px 0;'+(r.me?'color:#e6b35a;font-weight:700;':'')+'"><span>'+(med[i]||('#'+(i+1)))+' '+misEsc(r.name)+'</span><span>'+r.count+' mission'+(r.count>1?'s':'')+'</span></div>'; });
				tb.innerHTML=th; box.appendChild(tb);
			}
			var list=d.missions||[];
			if(!list.length){ var nm=document.createElement('div'); nm.className='card'; nm.innerHTML='<p class="sub" style="margin:0;">Aucune mission ouverte pour l\'instant. Reviens bientôt </p>'; box.appendChild(nm); return; }
			list.forEach(function(m){ box.appendChild(misCard(m)); });
		}).catch(function(){ box.innerHTML='<div class="card"><p class="sub" style="margin:0;">Erreur de chargement.</p></div>'; });
	};
})();
</script>
</body>
</html>
<?php
exit;
