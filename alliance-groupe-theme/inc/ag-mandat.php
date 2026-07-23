<?php
/**
 * Tunnels de vente SANS APPEL :
 *  1. Audit payant (mandat signé) — `?ag_mandat=1&tier=deep|expert`
 *     Le client remplit un formulaire (URL + coordonnées + AUTORISATION écrite
 *     horodatée = mandat) puis paie directement. Aucun appel nécessaire.
 *  2. Rendez-vous « site personnalisé » — `?ag_rdv=1`
 *     Petit formulaire (email/tel/commerce/besoin + créneau) → crée un événement
 *     dans le Google Agenda de Fabrice (via ag_calendar_notify) + notification.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------- Config des paliers */
if ( ! function_exists( 'ag_mandat_tier' ) ) {
	function ag_mandat_tier( $t ) {
		$opt = function_exists( 'ag_tester_opt' ) ? 'ag_tester_opt' : null;
		if ( 'expert' === $t ) {
			return array(
				'kind'  => 'expert',
				'label' => 'Diagnostic Expert 24h',
				'price' => $opt ? (float) ag_tester_opt( 'deep_price' ) : 290,
				// UNIQUEMENT le lien 290 € dédié — jamais le lien 49 € (sinon mauvais montant).
				'pay'   => $opt ? (string) ag_tester_opt( 'deep_pay_url' ) : '',
				'desc'  => 'Scan de sécurité en profondeur (simulation d\'attaque réelle, ports, vulnérabilités connues, plugins). Rapport complet + plan de correction chiffré, livré en moins de 24 h.',
			);
		}
		return array(
			'kind'  => 'deep',
			'label' => 'Audit approfondi',
			'price' => $opt ? (float) ag_tester_opt( 'price' ) : 49,
			'pay'   => $opt ? (string) ag_tester_opt( 'pay_url' ) : '',
			'desc'  => 'Analyse de sécurité complète de votre site : sauvegardes/config exposées, versions de plugins et du CMS, énumération des comptes, en-têtes de sécurité, SSL, xmlrpc… Rapport détaillé + recommandations de correction.',
		);
	}
}

/* ---------------------------------------------- 1) Tunnel MANDAT (rendu autonome) */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['ag_mandat'] ) ) return;
	$tier = ( isset( $_GET['tier'] ) && 'expert' === $_GET['tier'] ) ? 'expert' : 'deep';
	$site = isset( $_GET['site'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['site'] ) ) ) : '';
	ag_mandat_render( $tier, $site );
	exit;
} );

if ( ! function_exists( 'ag_mandat_render' ) ) {
	function ag_mandat_render( $tier, $site = '' ) {
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		$T      = ag_mandat_tier( $tier );
		$price  = (float) $T['price'];
		$haspay = '' !== trim( (string) $T['pay'] );
		$logo   = get_stylesheet_directory_uri() . '/assets/images/logo-carte-square.jpg';
		$err    = isset( $_GET['err'] ) ? sanitize_key( $_GET['err'] ) : '';
		$okp    = isset( $_GET['ok'] );
		?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
		<title>Commander — <?php echo esc_html( $T['label'] ); ?> · Alliance Groupe</title>
		<style>
			*{box-sizing:border-box}body{margin:0;background:#0b0b0f;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;line-height:1.55;}
			.wrap{max-width:620px;margin:0 auto;padding:24px 16px 60px;}
			.top{display:flex;align-items:center;gap:10px;margin-bottom:16px;}.top img{width:34px;height:34px;border-radius:8px;}.top b{font-size:1.05rem;}.top b span{color:#d4b45c;}
			.tag{display:inline-block;background:#15151b;color:#e6b35a;border:1px solid #e6b35a;border-radius:100px;padding:4px 13px;font-size:.75rem;font-weight:700;}
			h1{font-size:1.5rem;margin:12px 0 4px;}
			.price{font-size:2rem;font-weight:800;color:#d4b45c;margin:6px 0 2px;}
			.desc{color:#c9c9d2;font-size:.95rem;margin:8px 0 18px;}
			.card{background:#14141a;border:1px solid #26262f;border-radius:16px;padding:18px;}
			label.f{display:block;font-weight:600;color:#e6e6ee;margin:12px 0 5px;font-size:.9rem;}
			input[type=text],input[type=email],input[type=url],input[type=tel]{width:100%;padding:13px;border-radius:10px;border:1px solid #333;background:#0e0e13;color:#fff;font-size:1rem;}
			.chk{display:flex;gap:10px;align-items:flex-start;margin:14px 0;color:#cfcfd6;font-size:.86rem;}
			.chk input{margin-top:3px;transform:scale(1.25);}
			.cta{display:block;width:100%;text-align:center;background:linear-gradient(135deg,#d4b45c,#b98f2f);color:#0b0b0f;font-weight:800;padding:16px;border-radius:14px;text-decoration:none;font-size:1.05rem;border:0;cursor:pointer;margin-top:16px;}
			.mand{background:#101826;border:1px solid #24405f;border-radius:12px;padding:12px 14px;margin:14px 0;color:#aac4e6;font-size:.84rem;}
			.err{color:#ff6b6b;font-size:.86rem;margin:8px 0;}
			.leg{color:#8a8a92;font-size:.78rem;margin-top:14px;text-align:center;}
			a.back{display:inline-block;margin:12px 0 0;color:#d4b45c;font-weight:700;text-decoration:none;}
		</style></head><body>
		<a class="back" href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">← Retour</a>
		<div class="wrap">
			<div class="top"><img src="<?php echo esc_url( $logo ); ?>" alt=""><b>Alliance <span>Groupe</span></b></div>
			<span class="tag">🛡️ COMMANDE SÉCURISÉE</span>
			<h1><?php echo esc_html( $T['label'] ); ?></h1>
			<div class="price"><?php echo esc_html( number_format_i18n( $price, 0 ) ); ?> € <span style="font-size:.9rem;color:#8a8a92;font-weight:400;">TTC</span></div>
			<p class="desc"><?php echo esc_html( $T['desc'] ); ?></p>
			<?php if ( $okp ) : ?><div class="mand" style="background:#0e1f13;border-color:#2e6a3f;color:#bfe6c9;">✅ <strong>Commande &amp; mandat enregistrés.</strong> Nous vous envoyons le <strong>lien de paiement sécurisé par email</strong> dans les plus brefs délais.</div><?php endif; ?>
			<?php if ( 'email' === $err ) : ?><p class="err">Merci d'indiquer une adresse email valide et l'URL de votre site.</p><?php endif; ?>
			<?php if ( 'mandat' === $err ) : ?><p class="err">Vous devez cocher l'autorisation (mandat) pour commander.</p><?php endif; ?>
			<?php if ( 'nonce' === $err ) : ?><p class="err">Session expirée, réessayez.</p><?php endif; ?>
			<form class="card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ag_mandat_order">
				<input type="hidden" name="tier" value="<?php echo esc_attr( $T['kind'] ); ?>">
				<input type="hidden" name="_n" value="<?php echo esc_attr( wp_create_nonce( 'ag_mandat' ) ); ?>">
				<label class="f">Adresse de votre site *</label>
				<input type="url" name="site" required placeholder="https://votresite.fr" value="<?php echo esc_attr( $site ); ?>">
				<label class="f">Votre nom / raison sociale *</label>
				<input type="text" name="nom" required placeholder="Nom ou entreprise">
				<label class="f">Email *</label>
				<input type="email" name="email" required placeholder="vous@exemple.com">
				<label class="f">Téléphone</label>
				<input type="tel" name="phone" placeholder="06 12 34 56 78">
				<div class="mand">🔒 <strong>Mandat / autorisation</strong> — obligatoire pour tout audit approfondi (art. 323-1 C. pénal). En cochant, vous signez électroniquement cette autorisation (date + heure + IP conservées).</div>
				<label class="chk"><input type="checkbox" name="mandat" value="1" required> J'atteste être le <strong>propriétaire ou le représentant autorisé</strong> du site ci-dessus et j'<strong>autorise Alliance Groupe</strong> à réaliser cet audit de sécurité sur ce site.</label>
				<label class="chk"><input type="checkbox" name="waive" value="1"> Je demande la <strong>fourniture immédiate après paiement</strong> et renonce à mon délai de rétractation de 14 jours (services numériques).</label>
				<label class="chk"><input type="checkbox" name="cgv" value="1" required> J'accepte les <strong>conditions générales</strong> et je passe une <strong>commande ferme</strong> de <?php echo esc_html( number_format_i18n( $price, 0 ) ); ?> €.</label>
				<button type="submit" class="cta"><?php echo $haspay ? '💳 Payer ' . esc_html( number_format_i18n( $price, 0 ) ) . ' € et commander' : '📩 Commander (' . esc_html( number_format_i18n( $price, 0 ) ) . ' €) — recevoir le lien de paiement'; ?></button>
			</form>
			<p class="leg">Paiement sécurisé. Facture envoyée par email. L'audit approfondi n'est jamais réalisé sans votre autorisation écrite.</p>
		</div></body></html><?php
	}
}

/* Handler : enregistre le mandat + la commande en attente, puis envoie payer. */
add_action( 'admin_post_nopriv_ag_mandat_order', 'ag_mandat_order' );
add_action( 'admin_post_ag_mandat_order', 'ag_mandat_order' );
if ( ! function_exists( 'ag_mandat_order' ) ) {
	function ag_mandat_order() {
		$tier = ( ( $_POST['tier'] ?? '' ) === 'expert' ) ? 'expert' : 'deep';
		$site = isset( $_POST['site'] ) ? esc_url_raw( wp_unslash( $_POST['site'] ) ) : '';
		$back = add_query_arg( array( 'ag_mandat' => 1, 'tier' => $tier, 'site' => rawurlencode( $site ) ), home_url( '/' ) );
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_mandat' ) ) { wp_safe_redirect( add_query_arg( 'err', 'nonce', $back ) ); exit; }
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$nom   = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
		$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		if ( '' === $site || ! is_email( $email ) ) { wp_safe_redirect( add_query_arg( 'err', 'email', $back ) ); exit; }
		if ( empty( $_POST['mandat'] ) || empty( $_POST['cgv'] ) ) { wp_safe_redirect( add_query_arg( 'err', 'mandat', $back ) ); exit; }

		$T     = ag_mandat_tier( $tier );
		$price = (float) $T['price'];
		$ip    = function_exists( 'ag_tester_client_ip' ) ? ag_tester_client_ip() : ( $_SERVER['REMOTE_ADDR'] ?? '' );

		// Mandat horodaté (signature électronique simple) — preuve d'autorisation.
		$mandats = (array) get_option( 'ag_mandats', array() );
		$mandats[] = array(
			'tier' => $tier, 'label' => $T['label'], 'site' => $site, 'nom' => $nom, 'email' => $email, 'phone' => $phone,
			'price' => $price, 'accepted_at' => current_time( 'mysql' ), 'ip' => $ip, 'waive' => ! empty( $_POST['waive'] ), 'paid' => 0,
		);
		if ( count( $mandats ) > 300 ) $mandats = array_slice( $mandats, -300 );
		update_option( 'ag_mandats', $mandats, false );

		// Commande en attente PARTAGÉE avec le webhook PayPal (déblocage/livraison au paiement).
		if ( function_exists( 'ag_rapport_key' ) ) {
			$pend = (array) get_option( 'ag_audit_pending', array() );
			$dup  = false;
			foreach ( $pend as $p ) { if ( empty( $p['paid'] ) && ag_rapport_key( $p['site'] ?? '' ) === ag_rapport_key( $site ) && strtolower( (string) ( $p['email'] ?? '' ) ) === strtolower( $email ) ) { $dup = true; break; } }
			if ( ! $dup ) {
				$pend[] = array( 'site' => $site, 'name' => $nom, 'email' => $email, 'price' => $price, 'ts' => time(), 'paid' => 0, 'txn' => '', 'tier' => $tier );
				if ( count( $pend ) > 300 ) $pend = array_slice( $pend, -300 );
				update_option( 'ag_audit_pending', $pend, false );
			}
		}
		if ( function_exists( 'ag_push' ) ) ag_push( '🛒 ' . $T['label'] . ' commandé (mandat signé)', $nom . ' (' . $email . ') — ' . $site . ' — ' . number_format_i18n( $price, 0 ) . ' €' );
		if ( function_exists( 'ag_sms' ) )  ag_sms( '🛒 ' . $T['label'] . ' : ' . $nom . ' — ' . $site . ' (' . number_format_i18n( $price, 0 ) . ' €)' );

		$pay = trim( (string) $T['pay'] );
		if ( '' !== $pay ) { wp_redirect( $pay ); exit; }
		wp_safe_redirect( add_query_arg( 'ok', 'pending', $back ) ); exit;
	}
}

/* Paiement vérifié du DIAGNOSTIC EXPERT (montant deep_price) : mandat déjà signé →
 * prévient Fabrice de réaliser le scan sous 24 h + accuse réception au client + compte client. */
add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn = '', $type = '', $resource = array() ) {
	$amount = (float) $amount;
	$expert = function_exists( 'ag_tester_opt' ) ? (float) ag_tester_opt( 'deep_price' ) : 0;
	if ( $expert <= 0 || abs( $amount - $expert ) > 0.5 ) return; // pas un Diagnostic Expert
	$email = sanitize_email( (string) $email );
	$pend  = (array) get_option( 'ag_audit_pending', array() );
	$hit   = -1;
	foreach ( $pend as $i => $p ) { if ( empty( $p['paid'] ) && ( $p['tier'] ?? '' ) === 'expert' && strtolower( (string) ( $p['email'] ?? '' ) ) === strtolower( $email ) ) { $hit = $i; break; } }
	if ( $hit < 0 ) { foreach ( $pend as $i => $p ) { if ( empty( $p['paid'] ) && ( $p['tier'] ?? '' ) === 'expert' ) { $hit = $i; break; } } }
	if ( $hit < 0 ) return;
	$site = (string) $pend[ $hit ]['site']; $nom = (string) ( $pend[ $hit ]['name'] ?? '' ); $to = is_email( $email ) ? $email : (string) $pend[ $hit ]['email'];
	$pend[ $hit ]['paid'] = 1; $pend[ $hit ]['txn'] = (string) $txn;
	update_option( 'ag_audit_pending', $pend, false );
	if ( function_exists( 'ag_create_member' ) ) ag_create_member( $to, $nom, 'ag_client' );
	// Client : accusé de réception.
	if ( function_exists( 'ag_email_wrap' ) ) {
		$inner = '<p>Bonjour' . ( $nom ? ' ' . esc_html( $nom ) : '' ) . ',</p><p>Nous avons bien reçu votre commande du <strong>Diagnostic Expert 24h</strong> pour ' . esc_html( $site ) . ' ainsi que votre autorisation. Notre expert lance le diagnostic : vous recevrez le <strong>rapport complet + plan de correction sous 24 h</strong>.</p><p>Une question ? Répondez à cet email.</p>';
		wp_mail( $to, 'Votre Diagnostic Expert 24h est lancé 🛡️', ag_email_wrap( 'Commande reçue', $inner ), array( 'Content-Type: text/html; charset=UTF-8', 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' ) );
	}
	// Fabrice : à réaliser.
	$admin = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
	wp_mail( $admin, '🔬 Diagnostic Expert 24h PAYÉ — à réaliser', "Mandat signé + payé.\nClient : $nom ($to)\nSite : $site\nMontant : $amount €\nTxn : $txn\n\n→ Lancer le scan (Kali) et livrer le rapport sous 24 h. Colle le résultat dans Sécurité → Audits Kali." );
	if ( function_exists( 'ag_push' ) ) ag_push( '🔬 Diagnostic Expert PAYÉ (24h)', "$nom — $site — à réaliser sous 24 h" );
}, 16, 5 );

/* ---------------------------------------------- 2) RENDEZ-VOUS « site personnalisé » */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['ag_rdv'] ) ) return;
	ag_rdv_render();
	exit;
} );

if ( ! function_exists( 'ag_rdv_render' ) ) {
	function ag_rdv_render() {
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		$logo = get_stylesheet_directory_uri() . '/assets/images/logo-carte-square.jpg';
		$ok   = isset( $_GET['ok'] );
		$err  = isset( $_GET['err'] ) ? sanitize_key( $_GET['err'] ) : '';
		?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
		<title>Prendre rendez-vous — Site personnalisé · Alliance Groupe</title>
		<style>
			*{box-sizing:border-box}body{margin:0;background:#0b0b0f;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;line-height:1.55;}
			.wrap{max-width:620px;margin:0 auto;padding:24px 16px 60px;}
			.top{display:flex;align-items:center;gap:10px;margin-bottom:16px;}.top img{width:34px;height:34px;border-radius:8px;}.top b{font-size:1.05rem;}.top b span{color:#d4b45c;}
			.tag{display:inline-block;background:#15151b;color:#e6b35a;border:1px solid #e6b35a;border-radius:100px;padding:4px 13px;font-size:.75rem;font-weight:700;}
			h1{font-size:1.5rem;margin:12px 0 4px;}.desc{color:#c9c9d2;font-size:.95rem;margin:8px 0 18px;}
			.card{background:#14141a;border:1px solid #26262f;border-radius:16px;padding:18px;}
			label.f{display:block;font-weight:600;color:#e6e6ee;margin:12px 0 5px;font-size:.9rem;}
			input,textarea,select{width:100%;padding:13px;border-radius:10px;border:1px solid #333;background:#0e0e13;color:#fff;font-size:1rem;}
			textarea{min-height:90px;}
			.row2{display:flex;gap:10px;}.row2>div{flex:1;}
			.cta{display:block;width:100%;text-align:center;background:linear-gradient(135deg,#d4b45c,#b98f2f);color:#0b0b0f;font-weight:800;padding:16px;border-radius:14px;border:0;cursor:pointer;font-size:1.05rem;margin-top:16px;}
			.ok{background:#0e1f13;border:1px solid #2e6a3f;border-radius:14px;padding:20px;color:#bfe6c9;text-align:center;}
			.err{color:#ff6b6b;font-size:.86rem;margin:8px 0;}
			a.back{display:inline-block;margin:12px 0 0;color:#d4b45c;font-weight:700;text-decoration:none;}
		</style></head><body>
		<a class="back" href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">← Retour</a>
		<div class="wrap">
			<div class="top"><img src="<?php echo esc_url( $logo ); ?>" alt=""><b>Alliance <span>Groupe</span></b></div>
			<?php if ( $ok ) : ?>
				<div class="ok"><div style="font-size:2rem;">✅</div><h1>Rendez-vous enregistré</h1><p>Merci ! Votre demande de site personnalisé est bien reçue. Nous vous recontactons au créneau indiqué. Un email de confirmation vous a été envoyé.</p></div>
			<?php else : ?>
				<span class="tag">🎨 SITE 100% PERSONNALISÉ</span>
				<h1>Prendre rendez-vous</h1>
				<p class="desc">Pour un site sur-mesure, dites-nous ce que vous voulez et choisissez un créneau. On vous rappelle pour en parler — pas d'engagement.</p>
				<?php if ( 'email' === $err ) : ?><p class="err">Merci d'indiquer au moins un email valide et ce que vous souhaitez.</p><?php endif; ?>
				<?php if ( 'nonce' === $err ) : ?><p class="err">Session expirée, réessayez.</p><?php endif; ?>
				<form class="card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ag_rdv_book">
					<input type="hidden" name="_n" value="<?php echo esc_attr( wp_create_nonce( 'ag_rdv' ) ); ?>">
					<label class="f">Votre nom *</label><input type="text" name="nom" required placeholder="Prénom Nom">
					<div class="row2"><div><label class="f">Email *</label><input type="email" name="email" required placeholder="vous@exemple.com"></div><div><label class="f">Téléphone</label><input type="tel" name="phone" placeholder="06…"></div></div>
					<label class="f">Votre commerce / activité</label><input type="text" name="commerce" placeholder="Ex : salon de coiffure, restaurant…">
					<label class="f">Ce que vous voulez sur votre site *</label><textarea name="besoin" required placeholder="Ex : réservation en ligne, galerie photos, menu, prise de contact, couleurs souhaitées…"></textarea>
					<div class="row2"><div><label class="f">Date souhaitée</label><input type="date" name="date"></div><div><label class="f">Heure</label><input type="time" name="heure" value="10:00"></div></div>
					<button type="submit" class="cta">📅 Réserver mon rendez-vous</button>
				</form>
			<?php endif; ?>
		</div></body></html><?php
	}
}

add_action( 'admin_post_nopriv_ag_rdv_book', 'ag_rdv_book' );
add_action( 'admin_post_ag_rdv_book', 'ag_rdv_book' );
if ( ! function_exists( 'ag_rdv_book' ) ) {
	function ag_rdv_book() {
		$back = add_query_arg( 'ag_rdv', 1, home_url( '/' ) );
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_rdv' ) ) { wp_safe_redirect( add_query_arg( 'err', 'nonce', $back ) ); exit; }
		$nom      = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
		$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$phone    = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$commerce = sanitize_text_field( wp_unslash( $_POST['commerce'] ?? '' ) );
		$besoin   = sanitize_textarea_field( wp_unslash( $_POST['besoin'] ?? '' ) );
		$date     = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );
		$heure    = sanitize_text_field( wp_unslash( $_POST['heure'] ?? '10:00' ) );
		if ( ! is_email( $email ) || '' === $besoin ) { wp_safe_redirect( add_query_arg( 'err', 'email', $back ) ); exit; }

		// Créneau → timestamp (fuseau du site). Défaut : demain 10h si non fourni.
		$start_ts = 0;
		if ( $date ) {
			$dt = date_create( trim( $date . ' ' . ( $heure ?: '10:00' ) ), wp_timezone() );
			if ( $dt ) $start_ts = $dt->getTimestamp();
		}
		if ( ! $start_ts ) $start_ts = strtotime( 'tomorrow 10:00' );

		$summary = '🎨 RDV site personnalisé — ' . ( $nom ?: $email );
		$desc    = "Demande de SITE PERSONNALISÉ\n"
			. "Client : $nom\nEmail : $email\nTéléphone : $phone\nCommerce : $commerce\n\n"
			. "Ce qu'il veut :\n$besoin";
		// → Google Agenda (via ag_calendar_notify) + notifications.
		if ( function_exists( 'ag_calendar_notify' ) ) ag_calendar_notify( $summary, $desc, $commerce, $start_ts );
		if ( function_exists( 'ag_push' ) ) ag_push( '🎨 RDV site personnalisé', "$nom ($email / $phone)\n$commerce\n" . mb_substr( $besoin, 0, 160 ) );
		if ( function_exists( 'ag_sms' ) )  ag_sms( '🎨 RDV site perso : ' . $nom . ' — ' . $commerce . ' — ' . $email );

		// CRM (lead) + accusé de réception client.
		if ( function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array( 'name' => $nom ?: $email, 'email' => $email, 'phone' => $phone, 'type' => $commerce, 'notes' => 'RDV site personnalisé : ' . $besoin, 'status' => 'interesse', 'source' => 'rdv-site' ) );
		}
		if ( function_exists( 'ag_email_wrap' ) ) {
			$inner = '<p>Bonjour' . ( $nom ? ' ' . esc_html( $nom ) : '' ) . ',</p><p>Votre demande de <strong>site personnalisé</strong> est bien enregistrée' . ( $date ? ' pour le <strong>' . esc_html( wp_date( 'd/m/Y à H\hi', $start_ts ) ) . '</strong>' : '' ) . '. On vous recontacte très vite pour en parler.</p><p>Vous vouliez : ' . esc_html( mb_substr( $besoin, 0, 300 ) ) . '</p>';
			wp_mail( $email, 'Votre rendez-vous — Site personnalisé Alliance Groupe', ag_email_wrap( 'Rendez-vous enregistré', $inner ), array( 'Content-Type: text/html; charset=UTF-8', 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' ) );
		}
		wp_safe_redirect( add_query_arg( 'ok', 1, $back ) ); exit;
	}
}
