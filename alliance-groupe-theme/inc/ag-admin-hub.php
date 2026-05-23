<?php
/**
 * Alliance Groupe — Centre de contrôle (un seul écran pour tout piloter).
 *
 * Regroupe au même endroit : les chiffres clés, les actions fréquentes
 * (publier aux clients, prospects à contacter) et l'état de chaque réglage
 * avec un bouton "Configurer" qui mène directement à la bonne page.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Menu principal en haut de la barre WordPress ───────────────────── */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Alliance Groupe',
		'🦁 Alliance Groupe',
		'manage_options',
		'ag-hub',
		'ag_hub_render',
		'dashicons-superhero',
		2
	);
	add_submenu_page( 'ag-hub', 'Centre de contrôle', '🏠 Tableau de bord', 'manage_options', 'ag-hub', 'ag_hub_render' );
}, 1 );

/* ── Petites aides ──────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_hub_badge' ) ) {
	/** Pastille ✓ / ⚠ selon que la brique est configurée. */
	function ag_hub_badge( $ok ) {
		return $ok
			? '<span style="display:inline-block;background:#e6f4ea;color:#1e7e34;font-weight:700;border-radius:100px;padding:2px 12px;font-size:.8rem;">✓ Activé</span>'
			: '<span style="display:inline-block;background:#fdecea;color:#b32d2e;font-weight:700;border-radius:100px;padding:2px 12px;font-size:.8rem;">⚠ À configurer</span>';
	}
}

if ( ! function_exists( 'ag_hub_card' ) ) {
	/** Carte raccourci (titre, description, lien, emoji). */
	function ag_hub_card( $emoji, $title, $desc, $url, $cta = 'Ouvrir' ) {
		printf(
			'<a href="%1$s" target="_blank" rel="noopener" style="display:flex;flex-direction:column;gap:6px;text-decoration:none;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:18px 18px 16px;box-shadow:0 1px 2px rgba(0,0,0,.04);transition:.15s;min-height:120px;" onmouseover="this.style.borderColor=\'#d4b45c\';this.style.boxShadow=\'0 6px 18px rgba(120,100,40,.12)\'" onmouseout="this.style.borderColor=\'#dcdcde\';this.style.boxShadow=\'0 1px 2px rgba(0,0,0,.04)\'">'
				. '<span style="font-size:1.7rem;line-height:1;">%2$s</span>'
				. '<strong style="color:#1d2327;font-size:1.05rem;">%3$s</strong>'
				. '<span style="color:#646970;font-size:.88rem;line-height:1.4;flex:1;">%4$s</span>'
				. '<span style="color:#b08524;font-weight:700;font-size:.88rem;margin-top:4px;">%5$s →</span>'
			. '</a>',
			esc_url( $url ), esc_html( $emoji ), esc_html( $title ), esc_html( $desc ), esc_html( $cta )
		);
	}
}

/* ── Le tableau de bord ─────────────────────────────────────────────── */
if ( ! function_exists( 'ag_hub_render' ) ) {
	function ag_hub_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		// ---- Chiffres clés ----
		$prospects = (array) get_option( 'ag_prospects', array() );
		$leads     = (array) get_option( 'ag_leads', array() );
		$ventes    = (array) get_option( 'ag_ambassadeur_ventes', array() );
		$briefs    = (array) get_option( 'ag_express_briefs', array() );

		$nb_a_contacter = 0;
		foreach ( $prospects as $p ) { if ( ( $p['status'] ?? 'nouveau' ) === 'nouveau' ) $nb_a_contacter++; }
		$nb_a_valider = 0;
		foreach ( $ventes as $v ) { if ( ( $v['statut'] ?? '' ) === 'declaree' ) $nb_a_valider++; }
		$nb_amb = count( get_users( array( 'role' => 'ag_ambassadeur', 'fields' => 'ID' ) ) );

		// ---- État des réglages ----
		$cfg_paypal   = ag_paypal_cfg( 'client_id' ) && ag_paypal_cfg( 'secret' ) && ag_paypal_cfg( 'webhook_id' );
		$cfg_notify   = ag_tg_cfg( 'token' ) && ( ag_tg_cfg( 'chat' ) || ag_tg_cfg( 'chan' ) );
		$cfg_places   = '' !== ag_places_key();
		$cfg_video    = '' !== ag_amb_guide_video();
		$cfg_paylinks = get_option( 'ag_stripe_premium_url', 'STRIPE_PLACEHOLDER' ) !== 'STRIPE_PLACEHOLDER';
		$cfg_google   = true; // ID par défaut intégré, fonctionnel.

		$opt = admin_url( 'options-general.php?page=' );
		$adm = admin_url( 'admin.php?page=' );

		$stat = function ( $emoji, $value, $label, $url, $hot = false ) {
			printf(
				'<a href="%5$s" target="_blank" rel="noopener" style="text-decoration:none;flex:1;min-width:150px;display:block;background:%6$s;border:1px solid %7$s;border-radius:14px;padding:16px 18px;">'
					. '<div style="font-size:1.3rem;">%1$s</div>'
					. '<div style="font-size:2rem;font-weight:800;color:#1d2327;line-height:1.1;margin:2px 0;">%2$s</div>'
					. '<div style="color:#646970;font-size:.85rem;font-weight:600;">%3$s%4$s</div>'
				. '</a>',
				esc_html( $emoji ), esc_html( (string) $value ), esc_html( $label ),
				$hot && $value ? ' <span style="color:#b32d2e;">●</span>' : '',
				esc_url( $url ),
				$hot && $value ? '#fff9ec' : '#fff',
				$hot && $value ? '#e9c96a' : '#dcdcde'
			);
		};

		$notice = isset( $_GET['ag_hub_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['ag_hub_msg'] ) ) : '';
		?>
		<div class="wrap">
			<h1 style="display:flex;align-items:center;gap:10px;">🦁 Centre de contrôle Alliance Groupe</h1>
			<p style="color:#646970;font-size:1rem;max-width:760px;margin-top:4px;">Tout ce que tu as à faire, au même endroit. Les chiffres en rouge demandent une action.</p>

			<?php if ( 'broadcast_ok' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p>✅ Message envoyé au canal clients.</p></div>
			<?php elseif ( 'broadcast_err' === $notice ) : ?>
				<div class="notice notice-error is-dismissible"><p>⚠ Envoi impossible : vérifie le token et le canal dans <em>Notifications téléphone</em>.</p></div>
			<?php elseif ( 'coach_ok' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p>✅ Feuille de route envoyée sur Telegram.</p></div>
			<?php elseif ( 'coach_err' === $notice ) : ?>
				<div class="notice notice-error is-dismissible"><p>⚠ Envoi impossible : configure le canal interne dans <em>Notifications téléphone</em>.</p></div>
			<?php endif; ?>

			<?php if ( function_exists( 'ag_daily_roadmap' ) ) :
				$coach_on = '1' === get_option( 'ag_coach_on', '1' );
			?>
			<div style="background:linear-gradient(135deg,#1d2327,#2c3338);color:#fff;border-radius:14px;padding:18px 22px;margin:6px 0 22px;">
				<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;">
					<h2 style="margin:0;color:#fff;font-size:1.2rem;">🗺️ Ta feuille de route du jour</h2>
					<div style="display:flex;gap:8px;">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="ag_coach_now"><?php wp_nonce_field( 'ag_coach', '_n' ); ?>
							<button class="button button-small">📲 M'envoyer sur Telegram</button>
						</form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="ag_coach_toggle"><?php wp_nonce_field( 'ag_coach', '_n' ); ?>
							<button class="button button-small"><?php echo $coach_on ? '🔔 Auto 8h : ON' : '🔕 Auto : OFF'; ?></button>
						</form>
					</div>
				</div>
				<ul style="margin:12px 0 0;padding-left:4px;list-style:none;line-height:1.9;">
					<?php foreach ( ag_daily_roadmap() as $t ) : ?>
						<li><?php echo esc_html( $t ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p style="margin:10px 0 0;color:#b9c0c7;font-size:.82rem;">Envoyée chaque matin 8h sur ton téléphone (Telegram) <?php echo $coach_on ? '— activé' : '— désactivé'; ?>.</p>
			</div>
			<?php endif; ?>

			<!-- CHIFFRES CLÉS -->
			<div style="display:flex;flex-wrap:wrap;gap:14px;margin:18px 0 26px;">
				<?php
				$stat( '🎯', $nb_a_contacter, 'Prospects à contacter', $adm . 'ag-prospects', true );
				$stat( '💬', count( $leads ), 'Leads du chat', $adm . 'ag-prospects', true );
				$stat( '💰', $nb_a_valider, 'Ventes à valider', $adm . 'ag-ambassadeurs', true );
				$stat( '📝', count( $briefs ), 'Briefs Sites Express', $adm . 'ag-express-briefs' );
				$stat( '🤝', $nb_amb, 'Ambassadeurs', $adm . 'ag-ambassadeurs' );
				?>
			</div>

			<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
				<!-- COLONNE GAUCHE -->
				<div>
					<!-- ACTIONS RAPIDES -->
					<h2 style="margin:0 0 12px;">⚡ Actions rapides</h2>
					<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-bottom:30px;">
						<?php
						ag_hub_card( '🎯', 'Prospection / CRM', 'Trouve des entreprises sans site, suis tes contacts.', $adm . 'ag-prospects', 'Prospecter' );
						ag_hub_card( '🤝', 'Ambassadeurs', 'Valide les ventes, paie les commissions, classement.', $adm . 'ag-ambassadeurs', 'Gérer' );
						ag_hub_card( '📝', 'Briefs Sites Express', 'Les commandes de sites reçues, prêtes à produire.', $adm . 'ag-express-briefs', 'Voir' );
						ag_hub_card( '🎬', 'Studio créatif', 'Crée des vidéos & visuels prêts à publier.', home_url( '/studio' ), 'Ouvrir' );
						ag_hub_card( '🏆', 'Classement', 'Le championnat des vendeurs (jour/mois/général).', home_url( '/classement' ), 'Voir' );
						ag_hub_card( '💬', 'Questions Flash', 'Les consultations écrites reçues.', $adm . 'ag-questions', 'Voir' );
						?>
					</div>

					<!-- PROSPECTS À CONTACTER (aperçu) -->
					<h2 style="margin:0 0 12px;">🎯 Prochains prospects à contacter</h2>
					<?php
					$todo = array();
					foreach ( array_reverse( $prospects ) as $p ) {
						if ( ( $p['status'] ?? 'nouveau' ) === 'nouveau' ) { $todo[] = $p; }
						if ( count( $todo ) >= 6 ) break;
					}
					if ( empty( $todo ) ) {
						echo '<p style="color:#646970;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">Aucun prospect en attente. <a href="' . esc_url( $adm . 'ag-prospects' ) . '">Lance une recherche →</a></p>';
					} else {
						echo '<table class="widefat striped" style="border-radius:12px;overflow:hidden;"><thead><tr><th>Entreprise</th><th>Secteur</th><th>Ville</th><th>Note</th><th></th></tr></thead><tbody>';
						foreach ( $todo as $p ) {
							printf(
								'<tr><td><strong>%s</strong></td><td>%s</td><td>%s</td><td>%s</td><td><a class="button button-small" href="%s">Traiter →</a></td></tr>',
								esc_html( $p['name'] ?? '' ),
								esc_html( $p['type'] ?? ( $p['sector'] ?? '—' ) ),
								esc_html( $p['city'] ?? '—' ),
								$p['rating'] ? esc_html( $p['rating'] . '★ (' . (int) ( $p['reviews'] ?? 0 ) . ')' ) : '—',
								esc_url( $adm . 'ag-prospects' )
							);
						}
						echo '</tbody></table>';
					}
					?>
				</div>

				<!-- COLONNE DROITE -->
				<div>
					<!-- PUBLIER AUX CLIENTS -->
					<div style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:18px;margin-bottom:22px;">
						<h2 style="margin:0 0 6px;font-size:1.15rem;">📣 Publier aux clients</h2>
						<p style="color:#646970;font-size:.86rem;margin:0 0 12px;">Envoie un message instantané au canal Telegram général des clients.</p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="ag_push_clients">
							<input type="hidden" name="_ag_hub" value="1">
							<?php wp_nonce_field( 'ag_push_clients', '_n' ); ?>
							<textarea name="msg" rows="3" required placeholder="Ex : ⚡ Offre flash 48h : -15% sur le pack Pro. Places limitées !" style="width:100%;border-radius:8px;"></textarea>
							<button type="submit" class="button button-primary" style="margin-top:8px;width:100%;"<?php echo $cfg_notify ? '' : ' disabled'; ?>>Envoyer maintenant</button>
							<?php if ( ! $cfg_notify ) : ?>
								<p style="color:#b32d2e;font-size:.82rem;margin:8px 0 0;">Configure d'abord le canal dans <a href="<?php echo esc_url( $opt . 'ag-notify' ); ?>">Notifications</a>.</p>
							<?php endif; ?>
						</form>
					</div>

					<!-- ÉTAT DES RÉGLAGES -->
					<div style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:18px;">
						<h2 style="margin:0 0 12px;font-size:1.15rem;">🔧 État des réglages</h2>
						<?php
						$rows = array(
							array( 'PayPal automatique', $cfg_paypal, $opt . 'ag-paypal-cfg' ),
							array( 'Liens de paiement', $cfg_paylinks, $opt . 'ag-stripe-config' ),
							array( 'Notifications téléphone', $cfg_notify, $opt . 'ag-notify' ),
							array( 'Recherche prospects (Places)', $cfg_places, $adm . 'ag-prospects' ),
							array( 'Connexion Google', $cfg_google, $opt . 'ag-social-login' ),
							array( 'Vidéo programme ambassadeur', $cfg_video, $opt . 'ag-amb-programme' ),
						);
						echo '<table style="width:100%;border-collapse:collapse;">';
						foreach ( $rows as $r ) {
							printf(
								'<tr style="border-bottom:1px solid #f0f0f1;"><td style="padding:9px 0;font-weight:600;color:#1d2327;">%s</td><td style="text-align:right;white-space:nowrap;">%s &nbsp;<a class="button button-small" href="%s">Configurer</a></td></tr>',
								esc_html( $r[0] ), ag_hub_badge( $r[1] ), esc_url( $r[2] )
							);
						}
						echo '</table>';
						?>
					</div>
				</div>
			</div>

			<!-- MESSAGES AUTOMATIQUES CLIENTS -->
			<?php
			$daily_on  = get_option( 'ag_client_daily_on' ) === '1';
			$next_run  = wp_next_scheduled( 'ag_client_daily' );
			$msg_count = function_exists( 'ag_client_messages' ) ? count( ag_client_messages() ) : 0;
			$today_msg = function_exists( 'ag_client_message_today' ) ? ag_client_message_today() : '';
			$chan_ok   = '' !== ag_tg_cfg( 'chan' );
			$auto_msg  = isset( $_GET['ag_auto'] ) ? sanitize_text_field( wp_unslash( $_GET['ag_auto'] ) ) : '';
			?>
			<div style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;margin-top:24px;">
				<h2 style="margin:0 0 4px;font-size:1.2rem;">📅 Messages automatiques clients</h2>
				<p style="color:#646970;font-size:.9rem;margin:0 0 14px;max-width:820px;">Un message percutant (urgence + offre limitée + exclusivité) part <strong>chaque jour à 9h</strong> dans le canal Telegram clients. Banque actuelle : <strong><?php echo (int) $msg_count; ?> messages</strong> en rotation (1 par jour).</p>

				<?php if ( 'saved' === $auto_msg ) : ?>
					<div class="notice notice-success inline" style="margin:0 0 12px;"><p>✅ Réglages des messages auto enregistrés.</p></div>
				<?php elseif ( 'sent_ok' === $auto_msg ) : ?>
					<div class="notice notice-success inline" style="margin:0 0 12px;"><p>✅ Message du jour envoyé aux clients.</p></div>
				<?php elseif ( 'sent_err' === $auto_msg ) : ?>
					<div class="notice notice-error inline" style="margin:0 0 12px;"><p>⚠ Envoi impossible : configure le canal clients dans Notifications.</p></div>
				<?php endif; ?>

				<p style="margin:0 0 16px;font-weight:600;">
					État :
					<?php if ( $daily_on && $chan_ok ) : ?>
						<span style="color:#1e7e34;">🟢 Actif</span> <span style="font-weight:400;color:#646970;">— prochain envoi : <strong><?php echo $next_run ? esc_html( wp_date( 'd/m/Y à H:i', $next_run ) ) : 'demain 9h'; ?></strong></span>
					<?php elseif ( $daily_on && ! $chan_ok ) : ?>
						<span style="color:#b26a00;">⚠ Activé, mais canal clients non configuré</span> — <a href="<?php echo esc_url( $opt . 'ag-notify' ); ?>">Configurer le canal</a>
					<?php else : ?>
						<span style="color:#646970;">⚪ Désactivé</span>
					<?php endif; ?>
				</p>

				<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;align-items:start;">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ag_hub_save_auto">
						<?php wp_nonce_field( 'ag_hub_save_auto', '_n' ); ?>
						<label style="display:flex;gap:8px;align-items:center;font-weight:600;margin-bottom:12px;">
							<input type="checkbox" name="ag_client_daily_on" value="1" <?php checked( $daily_on ); ?>>
							Envoyer automatiquement chaque jour à 9h
						</label>
						<label style="display:block;font-weight:600;margin-bottom:4px;">Tes propres messages (optionnel)</label>
						<p style="color:#646970;font-size:.82rem;margin:0 0 6px;">Sépare chaque message par une ligne contenant uniquement <code>---</code>. Ils s'ajoutent à la banque.</p>
						<textarea name="ag_client_msgs_custom" rows="8" style="width:100%;border-radius:8px;" placeholder="🔥 Offre flash 48h ...&#10;---&#10;⏳ Dernières places du mois ..."><?php echo esc_textarea( get_option( 'ag_client_msgs_custom', '' ) ); ?></textarea>
						<button type="submit" class="button button-primary" style="margin-top:10px;">Enregistrer</button>
					</form>

					<div>
						<label style="display:block;font-weight:600;margin-bottom:6px;">Aperçu du message d'aujourd'hui</label>
						<div style="white-space:pre-wrap;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:8px;padding:14px;min-height:90px;color:#1d2327;line-height:1.5;"><?php echo $today_msg ? esc_html( $today_msg ) : 'Aucun message (banque vide).'; ?></div>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px;">
							<input type="hidden" name="action" value="ag_client_send_now">
							<input type="hidden" name="_ag_hub" value="1">
							<?php wp_nonce_field( 'ag_hub_now', '_n' ); ?>
							<button type="submit" class="button"<?php echo $chan_ok ? '' : ' disabled'; ?>>📅 Envoyer celui-ci maintenant</button>
						</form>
						<?php if ( ! $chan_ok ) : ?>
							<p style="color:#b32d2e;font-size:.82rem;margin:8px 0 0;">Configure le canal clients dans <a href="<?php echo esc_url( $opt . 'ag-notify' ); ?>">Notifications</a> pour activer l'envoi.</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}
	}

/* ── Rediriger l'envoi "Publier aux clients" vers le hub avec un message ─ */
add_action( 'admin_post_ag_push_clients', function () {
	if ( ! isset( $_POST['_ag_hub'] ) ) return; // laisse l'autre handler gérer la page Notifications
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_push_clients' ) ) wp_die( 'no' );
	$msg = trim( (string) wp_unslash( $_POST['msg'] ?? '' ) );
	$ok  = ( '' !== $msg ) && function_exists( 'ag_push_clients' ) && ag_push_clients( $msg );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-hub&ag_hub_msg=' . ( $ok ? 'broadcast_ok' : 'broadcast_err' ) ) );
	exit;
}, 1 );

/* ── Enregistrer les réglages des messages auto depuis le hub ───────── */
add_action( 'admin_post_ag_hub_save_auto', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_hub_save_auto' ) ) wp_die( 'no' );
	update_option( 'ag_client_daily_on', isset( $_POST['ag_client_daily_on'] ) ? '1' : '' );
	update_option( 'ag_client_msgs_custom', sanitize_textarea_field( wp_unslash( $_POST['ag_client_msgs_custom'] ?? '' ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-hub&ag_auto=saved' ) );
	exit;
} );

/* ── "Envoyer le message du jour" depuis le hub (variante du handler) ── */
add_action( 'admin_post_ag_client_send_now', function () {
	if ( ! isset( $_POST['_ag_hub'] ) ) return; // laisse le handler de la page Notifications gérer
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_hub_now' ) ) wp_die( 'no' );
	$ok = function_exists( 'ag_push_clients' ) && function_exists( 'ag_client_message_today' ) && ag_push_clients( ag_client_message_today() );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-hub&ag_auto=' . ( $ok ? 'sent_ok' : 'sent_err' ) ) );
	exit;
}, 1 );
