<?php
/**
 * AG — Tirage au sort mensuel : 1 site gratuit chaque mois.
 *
 * Le visiteur s'inscrit (email + prénom). Chaque 1er du mois, un cron
 * tire un gagnant au hasard parmi les participants du mois écoulé,
 * l'archive, notifie l'équipe, et remet les compteurs à zéro.
 * L'admin peut aussi tirer manuellement.
 *
 * Page publique : créer une page WordPress avec le template
 * "Tirage au sort (site gratuit)".
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'ag_tirage_entries' ) ) {
	function ag_tirage_entries() {
		$d = get_option( 'ag_tirage_entries', array() );
		return is_array( $d ) ? $d : array();
	}
}
if ( ! function_exists( 'ag_tirage_winners' ) ) {
	function ag_tirage_winners() {
		$d = get_option( 'ag_tirage_winners', array() );
		return is_array( $d ) ? $d : array();
	}
}
if ( ! function_exists( 'ag_tirage_last_winner' ) ) {
	function ag_tirage_last_winner() {
		$w = ag_tirage_winners();
		return empty( $w ) ? null : $w[0];
	}
}

/* ---------------------------------------------------------------------------
 * Inscription (front)
 * ------------------------------------------------------------------------- */
add_action( 'admin_post_nopriv_ag_tirage_join', 'ag_tirage_join' );
add_action( 'admin_post_ag_tirage_join', 'ag_tirage_join' );
if ( ! function_exists( 'ag_tirage_join' ) ) {
	function ag_tirage_join() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ag_tirage' ) ) wp_die( 'Lien expiré.' );
		if ( ! empty( $_POST['hp_field'] ) ) wp_die( 'Spam détecté.' );

		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
		$tel    = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$back   = wp_get_referer() ?: home_url( '/tirage-au-sort' );

		if ( ! is_email( $email ) ) { wp_safe_redirect( add_query_arg( 'tmsg', 'email', $back ) ); exit; }

		$list  = ag_tirage_entries();
		$month = wp_date( 'Y-m' );
		// Anti-doublon : 1 participation par email et par mois.
		foreach ( $list as $e ) {
			if ( strtolower( $e['email'] ?? '' ) === strtolower( $email ) && ( $e['month'] ?? '' ) === $month ) {
				wp_safe_redirect( add_query_arg( 'tmsg', 'dup', $back ) ); exit;
			}
		}
		$list[] = array( 'email' => $email, 'prenom' => $prenom, 'phone' => $tel, 'month' => $month, 'ts' => time() );
		update_option( 'ag_tirage_entries', $list, false );

		// Lead dans le CRM + notif
		if ( function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name' => $prenom ?: $email, 'email' => $email, 'phone' => $tel,
				'source' => 'tirage', 'status' => 'nouveau',
				'notes' => 'Inscrit au tirage au sort ' . $month,
			) );
		}
		if ( function_exists( 'ag_push' ) ) ag_push( '🎰 Inscription tirage', ( $prenom ?: $email ) . ' participe au tirage du mois.' );

		wp_safe_redirect( add_query_arg( 'tmsg', 'ok', $back ) ); exit;
	}
}

/* ---------------------------------------------------------------------------
 * Tirage du gagnant (manuel admin + cron mensuel)
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_tirage_pick' ) ) {
	function ag_tirage_pick( $for_month = '' ) {
		$list = ag_tirage_entries();
		if ( '' === $for_month ) $for_month = wp_date( 'Y-m', strtotime( 'first day of last month' ) );
		// Candidats du mois ciblé (sinon tous si aucun ciblé trouvé).
		$pool = array_values( array_filter( $list, function ( $e ) use ( $for_month ) { return ( $e['month'] ?? '' ) === $for_month; } ) );
		if ( empty( $pool ) ) $pool = $list; // fallback : tout le monde
		if ( empty( $pool ) ) return null;

		$winner = $pool[ wp_rand( 0, count( $pool ) - 1 ) ];
		$winner['won_month'] = $for_month;
		$winner['won_ts']    = time();

		$winners = ag_tirage_winners();
		array_unshift( $winners, $winner );
		update_option( 'ag_tirage_winners', array_slice( $winners, 0, 36 ), false );

		// Notifications
		if ( function_exists( 'ag_push' ) ) ag_push( '🏆 Gagnant tirage', ( $winner['prenom'] ?: $winner['email'] ) . ' gagne le site gratuit (' . $for_month . ') !' );
		$subj = '🎉 Vous avez gagné un site web gratuit — Alliance Groupe';
		$body = "Bonjour " . ( $winner['prenom'] ?: '' ) . ",\n\nFélicitations ! Vous êtes le gagnant du tirage au sort Alliance Groupe : un site web Express OFFERT (valeur 490 €).\n\nRépondez à ce mail ou appelez le 07 44 82 95 16 pour lancer votre projet.\n\nÀ très vite,\nAlliance Groupe\n";
		if ( is_email( $winner['email'] ?? '' ) ) wp_mail( $winner['email'], $subj, $body );

		return $winner;
	}
}

/* Cron mensuel : tire le 1er du mois à 10h pour le mois précédent. */
add_action( 'ag_tirage_cron', function () {
	ag_tirage_pick();
} );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_tirage_cron' ) ) {
		// Prochain 1er du mois à 10h.
		$next = strtotime( 'first day of next month 10:00' );
		wp_schedule_event( $next, 'monthly', 'ag_tirage_cron' );
	}
} );
add_filter( 'cron_schedules', function ( $s ) {
	if ( ! isset( $s['monthly'] ) ) $s['monthly'] = array( 'interval' => 30 * DAY_IN_SECONDS, 'display' => 'Une fois par mois' );
	return $s;
} );

/* Admin : tirer manuellement + reset. */
add_action( 'admin_post_ag_tirage_draw', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_tirage_admin' ) ) wp_die( 'no' );
	$w = ag_tirage_pick( sanitize_text_field( wp_unslash( $_POST['month'] ?? wp_date( 'Y-m' ) ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-tirage&tdraw=' . ( $w ? '1' : '0' ) ) ); exit;
} );

/* ---------------------------------------------------------------------------
 * Page admin
 * ------------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-ambassadeurs', 'Tirage au sort', '🎰 Tirage au sort', 'manage_options', 'ag-tirage', 'ag_tirage_admin_render' );
}, 35 );

if ( ! function_exists( 'ag_tirage_admin_render' ) ) {
	function ag_tirage_admin_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$entries = ag_tirage_entries();
		$winners = ag_tirage_winners();
		$month   = wp_date( 'Y-m' );
		$this_month = array_filter( $entries, function ( $e ) use ( $month ) { return ( $e['month'] ?? '' ) === $month; } );
		$post = admin_url( 'admin-post.php' );
		?>
		<div class="wrap">
			<h1>🎰 Tirage au sort — 1 site gratuit / mois</h1>
			<?php if ( isset( $_GET['tdraw'] ) ) : ?>
				<div class="notice notice-<?php echo '1' === $_GET['tdraw'] ? 'success' : 'warning'; ?> is-dismissible"><p><?php echo '1' === $_GET['tdraw'] ? '🏆 Gagnant tiré ! Il a reçu un email + une notif.' : 'Aucun participant à tirer.'; ?></p></div>
			<?php endif; ?>

			<div style="display:flex;gap:14px;flex-wrap:wrap;margin:14px 0">
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:14px 18px;"><div style="font-size:1.8rem;font-weight:800"><?php echo count( $this_month ); ?></div><div style="color:#646970">participants ce mois</div></div>
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:14px 18px;"><div style="font-size:1.8rem;font-weight:800"><?php echo count( $entries ); ?></div><div style="color:#646970">participations totales</div></div>
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:14px 18px;"><div style="font-size:1.8rem;font-weight:800"><?php echo count( $winners ); ?></div><div style="color:#646970">gagnants archivés</div></div>
			</div>

			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:14px 0;background:#fff;border:1px solid #ccd0d4;border-radius:10px;padding:16px 18px;max-width:640px" onsubmit="return confirm('Tirer un gagnant au sort maintenant ?');">
				<input type="hidden" name="action" value="ag_tirage_draw">
				<?php wp_nonce_field( 'ag_tirage_admin', '_n' ); ?>
				<label>Mois à tirer : <input type="text" name="month" value="<?php echo esc_attr( $month ); ?>" style="width:120px" placeholder="2026-05"></label>
				<button class="button button-primary">🎲 Tirer un gagnant</button>
				<p class="description">Le cron tire automatiquement le 1er de chaque mois (mois précédent). Ce bouton est pour un tirage manuel.</p>
			</form>

			<?php if ( $winners ) : ?>
				<h2>🏆 Gagnants</h2>
				<table class="widefat striped" style="max-width:760px"><thead><tr><th>Mois gagné</th><th>Gagnant</th><th>Email</th><th>Tél</th><th>Date</th></tr></thead><tbody>
				<?php foreach ( $winners as $w ) : ?>
					<tr><td><strong><?php echo esc_html( $w['won_month'] ?? '' ); ?></strong></td><td><?php echo esc_html( $w['prenom'] ?? '' ); ?></td><td><?php echo esc_html( $w['email'] ?? '' ); ?></td><td><?php echo esc_html( $w['phone'] ?? '—' ); ?></td><td><?php echo esc_html( ! empty( $w['won_ts'] ) ? wp_date( 'd/m/Y', $w['won_ts'] ) : '' ); ?></td></tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>

			<h2 style="margin-top:24px">📋 Participants (<?php echo count( $entries ); ?>)</h2>
			<table class="widefat striped" style="max-width:760px"><thead><tr><th>Mois</th><th>Prénom</th><th>Email</th><th>Tél</th><th>Date</th></tr></thead><tbody>
			<?php foreach ( array_reverse( $entries ) as $e ) : ?>
				<tr><td><?php echo esc_html( $e['month'] ?? '' ); ?></td><td><?php echo esc_html( $e['prenom'] ?? '' ); ?></td><td><?php echo esc_html( $e['email'] ?? '' ); ?></td><td><?php echo esc_html( $e['phone'] ?? '—' ); ?></td><td><?php echo esc_html( ! empty( $e['ts'] ) ? wp_date( 'd/m/Y', $e['ts'] ) : '' ); ?></td></tr>
			<?php endforeach; ?>
			<?php if ( empty( $entries ) ) : ?><tr><td colspan="5" style="text-align:center;color:#646970;padding:18px">Aucun participant pour l'instant.</td></tr><?php endif; ?>
			</tbody></table>
		</div>
		<?php
	}
}

/* ---------------------------------------------------------------------------
 * Rendu page publique
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_tirage_render' ) ) {
	function ag_tirage_render() {
		$month = wp_date( 'Y-m' );
		$count = count( array_filter( ag_tirage_entries(), function ( $e ) use ( $month ) { return ( $e['month'] ?? '' ) === $month; } ) );
		$last  = ag_tirage_last_winner();
		$msg   = isset( $_GET['tmsg'] ) ? sanitize_text_field( wp_unslash( $_GET['tmsg'] ) ) : '';
		?>
		<section style="background:linear-gradient(180deg,#0a0a0f 0%,#1a1a2e 100%);color:#fff;padding:80px 24px;min-height:80vh">
			<div style="max-width:680px;margin:0 auto;text-align:center">
				<span style="display:inline-block;padding:6px 14px;background:rgba(243,122,31,.15);border:1px solid rgba(243,122,31,.5);border-radius:999px;color:#F37A1F;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px">🎰 Jeu concours · Gratuit</span>
				<h1 style="font-family:Georgia,serif;font-size:clamp(2rem,5.5vw,3.8rem);line-height:1.1;margin:0 0 14px">Gagnez un <em style="background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-style:italic">site web</em><br>chaque mois</h1>
				<p style="color:rgba(255,255,255,.78);font-size:1.1rem;line-height:1.6;margin:0 0 14px">Un site web pro <strong style="color:#D4B45C">offert</strong> (valeur 490 €) tiré au sort tous les mois parmi les inscrits. Une participation par mois, gratuite, sans engagement.</p>
				<p style="color:rgba(255,255,255,.6);margin:0 0 30px"><strong style="color:#fff;font-size:1.3rem"><?php echo (int) $count; ?></strong> participant(s) ce mois-ci</p>

				<?php if ( 'ok' === $msg ) : ?>
					<div style="background:rgba(40,167,69,.15);border:1px solid #28a745;border-radius:12px;padding:20px;margin-bottom:24px;color:#9be8ac">✅ Vous êtes inscrit pour le tirage de ce mois ! Bonne chance 🍀 On vous prévient par email si vous gagnez.</div>
				<?php elseif ( 'dup' === $msg ) : ?>
					<div style="background:rgba(243,122,31,.15);border:1px solid #F37A1F;border-radius:12px;padding:20px;margin-bottom:24px;color:#ffcf9e">ℹ️ Vous participez déjà ce mois-ci. Revenez le mois prochain pour retenter !</div>
				<?php elseif ( 'email' === $msg ) : ?>
					<div style="background:rgba(225,15,26,.15);border:1px solid #E10F1A;border-radius:12px;padding:20px;margin-bottom:24px;color:#ffb1b6">⚠️ Email invalide, réessaie.</div>
				<?php endif; ?>

				<?php if ( 'ok' !== $msg ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="text-align:left;display:flex;flex-direction:column;gap:14px;max-width:480px;margin:0 auto">
					<input type="hidden" name="action" value="ag_tirage_join">
					<?php wp_nonce_field( 'ag_tirage' ); ?>
					<input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
					<input type="text" name="prenom" placeholder="Votre prénom" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					<input type="email" name="email" required placeholder="Votre email *" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					<input type="tel" name="phone" placeholder="Téléphone (optionnel)" style="padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.3);border-radius:10px;color:#fff;font-size:1rem">
					<button type="submit" style="margin-top:8px;padding:18px 36px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#0a0a0f;font-weight:800;border:none;border-radius:999px;font-size:1rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;box-shadow:0 12px 32px rgba(212,180,92,.35)">🍀 Je participe gratuitement →</button>
					<p style="color:rgba(255,255,255,.5);font-size:.78rem;text-align:center;margin:6px 0 0">Sans engagement. Désinscription en 1 clic. Tirage le 1er de chaque mois.</p>
				</form>
				<?php endif; ?>

				<?php if ( $last && ! empty( $last['prenom'] ) ) : ?>
				<div style="margin-top:40px;padding:22px;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.3);border-radius:14px">
					<div style="color:#D4B45C;font-size:.8rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px">🏆 Dernier gagnant</div>
					<div style="font-size:1.2rem;color:#fff"><strong><?php echo esc_html( $last['prenom'] ); ?></strong> a gagné son site gratuit <?php echo esc_html( ! empty( $last['won_month'] ) ? '(' . $last['won_month'] . ')' : '' ); ?></div>
				</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
