<?php
/**
 * AG Ma Prospection — page front-end SIMPLE pour qu'un proche (enfant, neveu…)
 * prospecte facilement depuis son téléphone, sans accès admin.
 *
 * Shortcode [ag_ma_prospection] (page /ma-prospection auto-créée) :
 *  - il se connecte → voit SES cibles assignées + un POOL de cibles libres ;
 *  - 1 clic WhatsApp / SMS / Appel / Email avec le message DÉJÀ ÉCRIT ;
 *  - boutons « 🔥 Intéressé » / « 🔇 Sans réponse » / « 🔁 Relancé » (suivi) ;
 *  - « Je prends » pour s'attribuer une cible libre.
 *
 * Sécurité : actions réservées au propriétaire de la fiche (owner_email) ou admin.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_mp_user_email' ) ) {
	function ag_mp_user_email() {
		$u = wp_get_current_user();
		return ( $u && $u->exists() ) ? strtolower( $u->user_email ) : '';
	}
}
if ( ! function_exists( 'ag_mp_can_edit' ) ) {
	/** L'utilisateur courant peut-il agir sur ce prospect ? (proprio ou admin) */
	function ag_mp_can_edit( $p ) {
		if ( current_user_can( 'manage_options' ) ) return true;
		$me = ag_mp_user_email();
		return $me && strtolower( $p['owner_email'] ?? '' ) === $me;
	}
}

/* ---------------------------------------------------------------- AJAX front */
add_action( 'wp_ajax_ag_mp_action', 'ag_mp_action_handler' );
if ( ! function_exists( 'ag_mp_action_handler' ) ) {
	function ag_mp_action_handler() {
		if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_mp' ) ) wp_send_json_error();
		$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$act = sanitize_text_field( wp_unslash( $_POST['act'] ?? '' ) );
		$ch  = sanitize_text_field( wp_unslash( $_POST['channel'] ?? '' ) );
		$list = (array) get_option( 'ag_prospects', array() );
		foreach ( $list as $k => $p ) {
			if ( ( $p['id'] ?? '' ) !== $id ) continue;
			if ( ! ag_mp_can_edit( $p ) ) wp_send_json_error( array( 'msg' => 'non autorisé' ) );
			$today = current_time( 'd/m/Y' );
			switch ( $act ) {
				case 'touch':
					$list[ $k ]['date_contact'] = $list[ $k ]['date_contact'] ?? '' ?: $today;
					$list[ $k ]['last_contact'] = $today;
					$list[ $k ]['last_channel'] = $ch ?: ( $list[ $k ]['last_channel'] ?? '' );
					$list[ $k ]['contact_count'] = (int) ( $p['contact_count'] ?? 0 ) + 1;
					if ( in_array( $list[ $k ]['status'] ?? 'nouveau', array( 'nouveau', '' ), true ) ) $list[ $k ]['status'] = 'contacte';
					break;
				case 'interesse':
					$list[ $k ]['status'] = 'interesse'; $list[ $k ]['replied'] = 1; if ( empty( $list[ $k ]['date_reply'] ) ) $list[ $k ]['date_reply'] = $today;
					if ( function_exists( 'ag_push' ) ) ag_push( '🔥 Prospect intéressé', ( $p['name'] ?? '' ) . ' — via ' . ag_mp_user_email() );
					break;
				case 'sans_reponse':
					$list[ $k ]['status'] = 'sans_reponse';
					break;
				case 'relance':
					$list[ $k ]['status'] = 'relance'; $list[ $k ]['last_relance'] = $today; $list[ $k ]['last_contact'] = $today; $list[ $k ]['contact_count'] = (int) ( $p['contact_count'] ?? 0 ) + 1;
					break;
				default:
					wp_send_json_error();
			}
			update_option( 'ag_prospects', array_values( $list ) );
			wp_send_json_success( array( 'status' => $list[ $k ]['status'] ?? '', 'date' => $today ) );
		}
		wp_send_json_error();
	}
}
add_action( 'wp_ajax_ag_mp_claim', 'ag_mp_claim_handler' );
if ( ! function_exists( 'ag_mp_claim_handler' ) ) {
	function ag_mp_claim_handler() {
		if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_mp' ) ) wp_send_json_error();
		$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$me   = ag_mp_user_email();
		$list = (array) get_option( 'ag_prospects', array() );
		foreach ( $list as $k => $p ) {
			if ( ( $p['id'] ?? '' ) !== $id ) continue;
			// On ne prend qu'une fiche libre (anti-vol de lead d'un autre).
			if ( ! empty( $p['owner_email'] ) && strtolower( $p['owner_email'] ) !== $me && ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'msg' => 'déjà prise' ) );
			$list[ $k ]['owner_email'] = $me;
			$u = wp_get_current_user();
			$list[ $k ]['owner_name'] = $u->display_name ?: $me;
			update_option( 'ag_prospects', array_values( $list ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}
}

/* ---------------------------------------------------------------- Shortcode */
add_shortcode( 'ag_ma_prospection', function () {
	if ( ! is_user_logged_in() ) {
		return '<div style="max-width:520px;margin:30px auto;padding:22px;border-radius:14px;background:#14141c;border:1px solid rgba(212,180,92,.3);color:#e8e8ee;text-align:center;font-family:Arial,sans-serif;">Connecte-toi pour prospecter.<br><a href="' . esc_url( home_url( '/connexion' ) ) . '" style="display:inline-block;margin-top:12px;padding:12px 26px;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:bold;text-decoration:none;">Se connecter</a></div>';
	}
	$me   = ag_mp_user_email();
	$u    = wp_get_current_user();
	$all  = (array) get_option( 'ag_prospects', array() );
	$mine = array(); $pool = array();
	foreach ( $all as $p ) {
		if ( function_exists( 'ag_prospect_blocked' ) && ag_prospect_blocked( $p['status'] ?? '' ) ) continue;
		$own = strtolower( $p['owner_email'] ?? '' );
		if ( $own === $me ) $mine[] = $p;
		elseif ( '' === $own ) $pool[] = $p;
	}
	if ( function_exists( 'ag_prospect_score' ) ) {
		usort( $mine, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
		usort( $pool, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
	}
	$pool  = array_slice( $pool, 0, 30 );
	$nonce = wp_create_nonce( 'ag_mp' );
	$prog  = function_exists( 'ag_cand_program_url' ) ? ag_cand_program_url() : home_url( '/ambassadeurs' );

	ob_start();
	?>
	<style>
	.agmp{max-width:680px;margin:18px auto;font-family:-apple-system,Arial,sans-serif;color:#e8e8ee;}
	.agmp h2{font-family:Georgia,serif;color:#D4B45C;margin:18px 0 8px;}
	.agmp__hi{background:#14141c;border:1px solid rgba(212,180,92,.3);border-radius:14px;padding:16px 18px;margin-bottom:14px;}
	.agmp__card{background:#14141c;border:1px solid rgba(255,255,255,.08);border-left:4px solid #D4B45C;border-radius:12px;padding:14px 16px;margin:10px 0;}
	.agmp__name{font-weight:700;font-size:1.05rem;}
	.agmp__why{font-size:.86rem;color:#9a9aa5;margin:4px 0 10px;}
	.agmp__btns{display:flex;flex-wrap:wrap;gap:8px;}
	.agmp__btns a,.agmp__btns button{flex:1 1 auto;min-width:88px;text-align:center;padding:11px 10px;border-radius:10px;border:0;font-weight:700;font-size:.9rem;text-decoration:none;cursor:pointer;}
	.agmp__wa{background:#25D366;color:#fff;}.agmp__sms{background:#0a66c2;color:#fff;}.agmp__call{background:#444;color:#fff;}.agmp__mail{background:#6d28d9;color:#fff;}
	.agmp__ok{background:#1e7e34;color:#fff;}.agmp__no{background:#7a2222;color:#fff;}.agmp__take{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;}
	.agmp__suivi{font-size:.8rem;color:#9a9aa5;margin-top:8px;}
	</style>
	<div class="agmp">
		<div class="agmp__hi">
			<strong style="font-size:1.1rem;">Salut <?php echo esc_html( $u->first_name ?: $u->display_name ); ?> 👋</strong>
			<p style="margin:6px 0 0;color:#cfcfd6;font-size:.92rem;">Voici tes cibles. <strong>Tape WhatsApp</strong> : le message est déjà écrit, tu n'as qu'à envoyer. Quand quelqu'un est partant, appuie sur <strong>🔥 Intéressé</strong>.</p>
			<p style="margin:8px 0 0;font-size:.85rem;">Ton lien à partager : <a href="<?php echo esc_url( $prog ); ?>" style="color:#D4B45C;"><?php echo esc_html( $prog ); ?></a></p>
		</div>

		<h2>🎯 Mes cibles (<?php echo count( $mine ); ?>)</h2>
		<?php if ( empty( $mine ) ) : ?>
			<p style="color:#9a9aa5;">Aucune cible pour l'instant. Prends-en dans la liste « cibles libres » juste en dessous 👇</p>
		<?php endif; ?>
		<?php echo ag_mp_render_cards( $mine, $nonce, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( ! empty( $pool ) ) : ?>
			<h2>🆓 Cibles libres — « Je prends »</h2>
			<?php echo ag_mp_render_cards( $pool, $nonce, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>
	<script>
	(function(){
		var AX='<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', N=<?php echo wp_json_encode( $nonce ); ?>;
		function send(act,id,channel,cb){var fd=new FormData();fd.append('action','ag_mp_action');fd.append('_n',N);fd.append('id',id);fd.append('act',act);if(channel)fd.append('channel',channel);fetch(AX,{method:'POST',body:fd,credentials:'same-origin',keepalive:true}).then(function(r){return r.json();}).then(function(j){if(cb)cb(j);});}
		document.querySelectorAll('.agmp__touch').forEach(function(a){a.addEventListener('click',function(){send('touch',a.getAttribute('data-id'),a.getAttribute('data-ch'));});});
		document.querySelectorAll('.agmp__act').forEach(function(b){b.addEventListener('click',function(){var act=b.getAttribute('data-act');b.disabled=true;send(act,b.getAttribute('data-id'),'',function(j){b.textContent=j&&j.success?'✓':'…';});});});
		document.querySelectorAll('.agmp__take').forEach(function(b){b.addEventListener('click',function(){var fd=new FormData();fd.append('action','ag_mp_claim');fd.append('_n',N);fd.append('id',b.getAttribute('data-id'));b.disabled=true;b.textContent='…';fetch(AX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){location.reload();}else{b.textContent='déjà prise';}});});});
	})();
	</script>
	<?php
	return ob_get_clean();
} );

if ( ! function_exists( 'ag_mp_render_cards' ) ) {
	function ag_mp_render_cards( $items, $nonce, $is_pool ) {
		$out = '';
		foreach ( $items as $p ) {
			$id    = $p['id'] ?? '';
			$msg   = function_exists( 'ag_prospect_message' ) ? ag_prospect_message( $p ) : 'Bonjour, je vous contacte de la part d\'Alliance Groupe.';
			$why   = function_exists( 'ag_prospect_why' ) ? ag_prospect_why( $p ) : '';
			$digits = function_exists( 'ag_wa_number' ) ? ag_wa_number( $p['phone'] ?? '', $p['phone_intl'] ?? '' ) : '';
			$smsnum = preg_replace( '/[^0-9+]/', '', $p['phone_intl'] ?? '' ) ?: preg_replace( '/[^0-9+]/', '', $p['phone'] ?? '' );
			$wa    = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
			$sms   = $smsnum ? 'sms:' . $smsnum . '?body=' . rawurlencode( $msg ) : '';
			$mailto = ! empty( $p['email'] ) ? 'mailto:' . rawurlencode( $p['email'] ) . '?subject=' . rawurlencode( 'Votre site web — Alliance Groupe' ) . '&body=' . rawurlencode( $msg ) : '';

			$out .= '<div class="agmp__card">';
			$out .= '<div class="agmp__name">' . esc_html( $p['name'] ?? '' ) . ( ! empty( $p['city'] ) ? ' <span style="color:#9a9aa5;font-weight:400;">· ' . esc_html( $p['city'] ) . '</span>' : '' ) . '</div>';
			if ( $why ) $out .= '<div class="agmp__why">' . esc_html( $why ) . '</div>';
			if ( $is_pool ) {
				$out .= '<div class="agmp__btns"><button class="agmp__take" data-id="' . esc_attr( $id ) . '">➕ Je prends cette cible</button></div>';
			} else {
				$out .= '<div class="agmp__btns">';
				if ( $wa )     $out .= '<a class="agmp__wa agmp__touch" data-id="' . esc_attr( $id ) . '" data-ch="WhatsApp" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">WhatsApp</a>';
				if ( $sms )    $out .= '<a class="agmp__sms agmp__touch" data-id="' . esc_attr( $id ) . '" data-ch="SMS" href="' . esc_attr( $sms ) . '">SMS</a>';
				if ( ! empty( $p['phone'] ) ) $out .= '<a class="agmp__call agmp__touch" data-id="' . esc_attr( $id ) . '" data-ch="Appel" href="tel:' . esc_attr( $p['phone'] ) . '">Appeler</a>';
				if ( $mailto ) $out .= '<a class="agmp__mail agmp__touch" data-id="' . esc_attr( $id ) . '" data-ch="Email" href="' . esc_url( $mailto ) . '">Email</a>';
				$out .= '</div>';
				$out .= '<div class="agmp__btns" style="margin-top:8px;">';
				$out .= '<button class="agmp__ok agmp__act" data-id="' . esc_attr( $id ) . '" data-act="interesse">🔥 Intéressé</button>';
				$out .= '<button class="agmp__no agmp__act" data-id="' . esc_attr( $id ) . '" data-act="sans_reponse">🔇 Sans réponse</button>';
				$out .= '<button class="agmp__call agmp__act" data-id="' . esc_attr( $id ) . '" data-act="relance">🔁 Relancé</button>';
				$out .= '</div>';
				if ( ! empty( $p['date_contact'] ) ) {
					$out .= '<div class="agmp__suivi">📨 Contacté le ' . esc_html( $p['date_contact'] ) . ' (×' . (int) ( $p['contact_count'] ?? 1 ) . ')' . ( ! empty( $p['status'] ) ? ' · ' . esc_html( $p['status'] ) : '' ) . '</div>';
				}
			}
			$out .= '</div>';
		}
		return $out;
	}
}

/* -------------------------------------------------- Auto-création de la page */
add_action( 'admin_init', function () {
	if ( get_option( 'ag_mp_page_v1' ) ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! get_page_by_path( 'ma-prospection' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Ma prospection',
			'post_name'    => 'ma-prospection',
			'post_content' => '<!-- wp:shortcode -->[ag_ma_prospection]<!-- /wp:shortcode -->',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
	}
	update_option( 'ag_mp_page_v1', 1 );
} );
