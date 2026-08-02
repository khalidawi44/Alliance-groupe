<?php
/**
 * AG — Missions ambassadeurs (« ton BeMyEye ») + Plateformes de missions web.
 *
 * 1) TABLEAU MISSIONS : l'admin (Fabrice) publie des missions terrain
 *    (ex. « trouve 10 commerces sans site à Pornic »). Les ambassadeurs les
 *    voient dans l'appli, RÉSERVENT une place, puis SOUMETTENT leur travail
 *    (note + prospects trouvés + photo). L'admin valide → les prospects
 *    tombent dans le CRM + une prime est enregistrée + notification.
 *
 * 2) PLATEFORMES DE MISSIONS WEB : page ressource (Codeur / ComeUp / Malt /
 *    Fiverr) avec des pitchs prêts à coller pour décrocher des missions.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Données ─────────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_missions_all' ) ) {
	function ag_missions_all() {
		$m = get_option( 'ag_missions', array() );
		return is_array( $m ) ? $m : array();
	}
}
if ( ! function_exists( 'ag_mission_get' ) ) {
	function ag_mission_get( $id ) {
		foreach ( ag_missions_all() as $m ) { if ( ( $m['id'] ?? '' ) === $id ) return $m; }
		return null;
	}
}
if ( ! function_exists( 'ag_mission_subs' ) ) {
	function ag_mission_subs() {
		$s = get_option( 'ag_mission_subs', array() );
		return is_array( $s ) ? $s : array();
	}
}
if ( ! function_exists( 'ag_mission_is_open' ) ) {
	function ag_mission_is_open( $m ) {
		if ( 'open' !== ( $m['status'] ?? 'open' ) ) return false;
		if ( ! empty( $m['deadline_ts'] ) && (int) $m['deadline_ts'] < time() ) return false;
		return true;
	}
}
if ( ! function_exists( 'ag_mission_taken_count' ) ) {
	function ag_mission_taken_count( $m ) { return count( (array) ( $m['taken'] ?? array() ) ); }
}

/* ── Menus admin ─────────────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Missions ambassadeurs', '🎯 Missions', 'manage_options', 'ag-missions', 'ag_missions_admin_render' );
	add_submenu_page( 'ag-prospects', 'Plateformes de missions', '📋 Plateformes web', 'manage_options', 'ag-plateformes', 'ag_plateformes_render' );
} );

/* ── Créer / fermer / supprimer une mission (admin-post) ─────────────────── */
add_action( 'admin_post_ag_mission_create', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_mission' ) ) wp_die( 'no' );
	$m = ag_missions_all();
	$m[] = array(
		'id'          => uniqid( 'm_' ),
		'title'       => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
		'desc'        => sanitize_textarea_field( wp_unslash( $_POST['desc'] ?? '' ) ),
		'city'        => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'prime'       => preg_replace( '/[^0-9.,]/', '', (string) ( $_POST['prime'] ?? '' ) ),
		'slots'       => max( 1, (int) ( $_POST['slots'] ?? 1 ) ),
		'deadline_ts' => ! empty( $_POST['deadline'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['deadline'] ) ) ) : 0,
		'status'      => 'open',
		'taken'       => array(),
		'ts'          => time(),
	);
	update_option( 'ag_missions', $m );
	if ( function_exists( 'ag_push' ) ) ag_push( '🎯 Nouvelle mission : ' . sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ), 'Ouverte aux ambassadeurs dans l\'appli.', true );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-missions&created=1' ) ); exit;
} );

add_action( 'admin_post_ag_mission_close', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ag_mission' ) ) wp_die( 'no' );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$act = sanitize_key( $_POST['act'] ?? 'close' );
	$m = ag_missions_all();
	foreach ( $m as $k => $mi ) {
		if ( ( $mi['id'] ?? '' ) === $id ) {
			if ( 'delete' === $act ) { unset( $m[ $k ] ); }
			else { $m[ $k ]['status'] = ( 'open' === ( $mi['status'] ?? '' ) ) ? 'closed' : 'open'; }
			break;
		}
	}
	update_option( 'ag_missions', array_values( $m ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-missions' ) ); exit;
} );

/* ── Valider / refuser une soumission (AJAX admin) ───────────────────────── */
add_action( 'wp_ajax_ag_mission_review', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_mission' ) ) wp_send_json_error();
	$sid = sanitize_text_field( wp_unslash( $_POST['sid'] ?? '' ) );
	$ok  = ! empty( $_POST['ok'] );
	$subs = ag_mission_subs();
	$found = null;
	foreach ( $subs as $k => $s ) {
		if ( ( $s['id'] ?? '' ) === $sid ) {
			$subs[ $k ]['status'] = $ok ? 'valide' : 'refuse';
			$subs[ $k ]['review_ts'] = time();
			$found = $subs[ $k ];
			break;
		}
	}
	if ( ! $found ) wp_send_json_error();
	update_option( 'ag_mission_subs', array_values( $subs ) );

	$added = 0;
	if ( $ok ) {
		// Prospects trouvés → CRM, assignés à l'ambassadeur.
		if ( function_exists( 'ag_prospect_add_record' ) ) {
			foreach ( (array) ( $found['leads'] ?? array() ) as $ld ) {
				if ( empty( $ld['name'] ) ) continue;
				$done = ag_prospect_add_record( array(
					'name'       => $ld['name'],
					'city'       => $ld['city'] ?? '',
					'phone'      => $ld['phone'] ?? '',
					'source'     => 'mission',
					'owner_email' => $found['email'] ?? '',
					'notes'      => 'Trouvé via mission : ' . ( $found['mission_title'] ?? '' ),
				) );
				if ( $done ) $added++;
			}
		}
		// Prime « à payer » (paiement manuel).
		$primes = (array) get_option( 'ag_mission_primes', array() );
		$primes[] = array(
			'id' => uniqid( 'mp_' ), 'sub_id' => $sid, 'email' => $found['email'] ?? '', 'name' => $found['name'] ?? '',
			'mission' => $found['mission_title'] ?? '', 'amount' => (float) ( $found['prime'] ?? 0 ), 'ts' => time(), 'paid' => 0,
		);
		update_option( 'ag_mission_primes', $primes );
		if ( ! empty( $found['email'] ) ) {
			wp_mail( $found['email'], '✅ Mission validée — prime à venir', "Bravo " . ( $found['name'] ?? '' ) . ",\n\nTa mission « " . ( $found['mission_title'] ?? '' ) . " » est validée." . ( (float) ( $found['prime'] ?? 0 ) > 0 ? "\nPrime : " . number_format( (float) $found['prime'], 2, ',', ' ' ) . " €." : '' ) . "\n\nMerci 💪\nAlliance Groupe" );
		}
	}
	wp_send_json_success( array( 'status' => $ok ? 'valide' : 'refuse', 'added' => $added ) );
} );

/* ── Rendu admin : créer + gérer les missions ───────────────────────────── */
if ( ! function_exists( 'ag_missions_admin_render' ) ) {
	function ag_missions_admin_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$missions = ag_missions_all();
		usort( $missions, function ( $a, $b ) { return (int) ( $b['ts'] ?? 0 ) - (int) ( $a['ts'] ?? 0 ); } );
		$subs = ag_mission_subs();
		$nonce = wp_create_nonce( 'ag_mission' );
		?>
		<div class="wrap">
			<h1>🎯 Missions ambassadeurs</h1>
			<p style="max-width:820px;color:#444;">Publie des missions terrain (prospection, distribution…). Tes ambassadeurs les réservent dans l'appli, les réalisent, et tu valides ici → les prospects trouvés arrivent dans le CRM + une prime est enregistrée.</p>
			<?php if ( isset( $_GET['created'] ) ) : ?><div class="notice notice-success"><p>Mission publiée ✅</p></div><?php endif; ?>

			<h2>➕ Nouvelle mission</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="background:#fff;border:1px solid #ccd0d4;padding:14px 18px;border-radius:8px;max-width:820px;">
				<input type="hidden" name="action" value="ag_mission_create">
				<?php wp_nonce_field( 'ag_mission' ); ?>
				<p><label><strong>Titre</strong><br><input type="text" name="title" class="regular-text" required placeholder="Trouver 10 commerces sans site à Pornic"></label></p>
				<p><label><strong>Détail</strong><br><textarea name="desc" rows="3" class="large-text" placeholder="Ce qu'il faut faire, ce qu'il faut ramener (nom + téléphone + ville de chaque commerce sans vrai site)."></textarea></label></p>
				<p style="display:flex;gap:18px;flex-wrap:wrap;">
					<label><strong>Ville / zone</strong><br><input type="text" name="city" placeholder="Pornic"></label>
					<label><strong>Prime (€)</strong><br><input type="text" name="prime" style="width:90px;" placeholder="20"></label>
					<label><strong>Places</strong><br><input type="number" name="slots" min="1" value="3" style="width:80px;"></label>
					<label><strong>Date limite</strong><br><input type="date" name="deadline"></label>
				</p>
				<?php submit_button( 'Publier la mission' ); ?>
			</form>

			<h2 style="margin-top:26px;">📋 Missions</h2>
			<?php if ( ! $missions ) : ?>
				<p>Aucune mission pour l'instant.</p>
			<?php else : foreach ( $missions as $m ) :
				$msubs = array_values( array_filter( $subs, function ( $s ) use ( $m ) { return ( $s['mission_id'] ?? '' ) === ( $m['id'] ?? '' ); } ) );
			?>
				<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid <?php echo ag_mission_is_open( $m ) ? '#46b450' : '#999'; ?>;padding:12px 16px;border-radius:6px;margin:12px 0;max-width:900px;">
					<div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:baseline;">
						<strong style="font-size:1.05em;"><?php echo esc_html( $m['title'] ?? '' ); ?></strong>
						<span><?php echo ag_mission_is_open( $m ) ? '🟢 ouverte' : '⚪ fermée'; ?> · <?php echo (int) ag_mission_taken_count( $m ); ?>/<?php echo (int) ( $m['slots'] ?? 1 ); ?> places<?php echo ! empty( $m['prime'] ) ? ' · prime ' . esc_html( $m['prime'] ) . ' €' : ''; ?></span>
					</div>
					<?php if ( ! empty( $m['desc'] ) ) : ?><p style="margin:6px 0;color:#555;"><?php echo esc_html( $m['desc'] ); ?></p><?php endif; ?>
					<p style="color:#888;font-size:.9em;margin:4px 0;"><?php echo esc_html( $m['city'] ?? '' ); ?><?php echo ! empty( $m['deadline_ts'] ) ? ' · limite ' . esc_html( date_i18n( 'd/m/Y', (int) $m['deadline_ts'] ) ) : ''; ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
						<input type="hidden" name="action" value="ag_mission_close"><input type="hidden" name="id" value="<?php echo esc_attr( $m['id'] ); ?>">
						<?php wp_nonce_field( 'ag_mission' ); ?>
						<button class="button button-small" name="act" value="close"><?php echo ag_mission_is_open( $m ) ? 'Fermer' : 'Rouvrir'; ?></button>
						<button class="button button-small" name="act" value="delete" onclick="return confirm('Supprimer cette mission ?');" style="color:#b32d2e;">Supprimer</button>
					</form>

					<?php if ( $msubs ) : ?>
						<h4 style="margin:12px 0 4px;">Rendus (<?php echo count( $msubs ); ?>)</h4>
						<?php foreach ( $msubs as $s ) :
							$st = $s['status'] ?? 'pending';
							$stl = array( 'pending' => '⏳ à valider', 'valide' => '✅ validé', 'refuse' => '❌ refusé' );
						?>
							<div style="border-top:1px solid #eee;padding:8px 0;" data-sub="<?php echo esc_attr( $s['id'] ); ?>">
								<div><strong><?php echo esc_html( $s['name'] ?: $s['email'] ); ?></strong> — <span class="ag-sub-st"><?php echo esc_html( $stl[ $st ] ?? $st ); ?></span></div>
								<?php if ( ! empty( $s['note'] ) ) : ?><div style="color:#555;font-size:.92em;"><?php echo esc_html( $s['note'] ); ?></div><?php endif; ?>
								<?php if ( ! empty( $s['leads'] ) ) : ?><div style="color:#555;font-size:.9em;">📋 <?php echo count( (array) $s['leads'] ); ?> prospect(s) : <?php echo esc_html( implode( ' · ', array_map( function ( $l ) { return ( $l['name'] ?? '' ) . ( ! empty( $l['phone'] ) ? ' (' . $l['phone'] . ')' : '' ); }, array_slice( (array) $s['leads'], 0, 12 ) ) ) ); ?></div><?php endif; ?>
								<?php if ( ! empty( $s['photo'] ) ) : ?><div style="margin:5px 0;"><a href="<?php echo esc_url( $s['photo'] ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( $s['photo'] ); ?>" alt="" style="max-width:160px;border-radius:6px;border:1px solid #ddd;"></a></div><?php endif; ?>
								<?php if ( 'pending' === $st ) : ?>
									<button class="button button-small button-primary ag-sub-ok" data-sid="<?php echo esc_attr( $s['id'] ); ?>">✅ Valider (prospects → CRM + prime)</button>
									<button class="button button-small ag-sub-ko" data-sid="<?php echo esc_attr( $s['id'] ); ?>" style="color:#b32d2e;">Refuser</button>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			<?php endforeach; endif; ?>
		</div>
		<script>
		(function(){
			var n=<?php echo wp_json_encode( $nonce ); ?>, ajax=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			document.addEventListener('click',function(e){
				var ok=e.target.closest?e.target.closest('.ag-sub-ok'):null;
				var ko=e.target.closest?e.target.closest('.ag-sub-ko'):null;
				var b=ok||ko; if(!b) return; e.preventDefault();
				var sid=b.getAttribute('data-sid'); b.disabled=true;
				var body=new URLSearchParams(); body.set('action','ag_mission_review'); body.set('_n',n); body.set('sid',sid); if(ok) body.set('ok','1');
				fetch(ajax,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
					.then(function(r){return r.json();}).then(function(j){
						var wrap=b.closest('[data-sub]'); if(!wrap) return;
						var st=wrap.querySelector('.ag-sub-st');
						if(j&&j.success){ if(st) st.textContent=(j.data.status==='valide')?('✅ validé'+(j.data.added?(' · '+j.data.added+' au CRM'):'')):'❌ refusé';
							wrap.querySelectorAll('button').forEach(function(x){ x.remove(); }); }
						else { b.disabled=false; alert('Erreur'); }
					}).catch(function(){ b.disabled=false; });
			});
		})();
		</script>
		<?php
	}
}

/* ── AJAX appli (ambassadeurs) ───────────────────────────────────────────── */
add_action( 'wp_ajax_ag_app_missions', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$email = strtolower( wp_get_current_user()->user_email );
	$subs  = ag_mission_subs();
	$out   = array();
	foreach ( ag_missions_all() as $m ) {
		if ( ! ag_mission_is_open( $m ) ) continue;
		$taken = array_map( 'strtolower', (array) ( $m['taken'] ?? array() ) );
		$mine  = null;
		foreach ( $subs as $s ) { if ( ( $s['mission_id'] ?? '' ) === ( $m['id'] ?? '' ) && strtolower( $s['email'] ?? '' ) === $email ) { $mine = $s['status'] ?? 'pending'; break; } }
		$out[] = array(
			'id'       => $m['id'],
			'title'    => $m['title'] ?? '',
			'desc'     => $m['desc'] ?? '',
			'city'     => $m['city'] ?? '',
			'prime'    => $m['prime'] ?? '',
			'slots'    => (int) ( $m['slots'] ?? 1 ),
			'taken'    => count( $taken ),
			'reserved' => in_array( $email, $taken, true ),
			'sub'      => $mine, // null | pending | valide | refuse
			'deadline' => ! empty( $m['deadline_ts'] ) ? date_i18n( 'd/m/Y', (int) $m['deadline_ts'] ) : '',
		);
	}
	wp_send_json_success( array( 'missions' => $out ) );
} );

add_action( 'wp_ajax_ag_app_mission_reserve', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$email = strtolower( wp_get_current_user()->user_email );
	$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$m = ag_missions_all(); $done = false;
	foreach ( $m as $k => $mi ) {
		if ( ( $mi['id'] ?? '' ) === $id ) {
			if ( ! ag_mission_is_open( $mi ) ) wp_send_json_error( array( 'm' => 'Mission fermée.' ) );
			$taken = array_map( 'strtolower', (array) ( $mi['taken'] ?? array() ) );
			if ( in_array( $email, $taken, true ) ) { $done = true; break; }
			if ( count( $taken ) >= (int) ( $mi['slots'] ?? 1 ) ) wp_send_json_error( array( 'm' => 'Plus de place.' ) );
			$m[ $k ]['taken'][] = $email; $done = true; break;
		}
	}
	if ( ! $done ) wp_send_json_error();
	update_option( 'ag_missions', $m );
	wp_send_json_success();
} );

add_action( 'wp_ajax_ag_app_mission_submit', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$u     = wp_get_current_user();
	$email = strtolower( $u->user_email );
	$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$m     = ag_mission_get( $id );
	if ( ! $m ) wp_send_json_error( array( 'm' => 'Mission introuvable.' ) );
	$note  = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
	// Prospects : une ligne = « Nom, Téléphone, Ville ».
	$leads = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $_POST['leads'] ?? '' ) ) as $line ) {
		$line = trim( $line ); if ( '' === $line ) continue;
		$p = array_map( 'trim', preg_split( '/[,;]/', $line ) );
		if ( '' === ( $p[0] ?? '' ) ) continue;
		$leads[] = array(
			'name'  => sanitize_text_field( $p[0] ),
			'phone' => sanitize_text_field( $p[1] ?? '' ),
			'city'  => sanitize_text_field( $p[2] ?? ( $m['city'] ?? '' ) ),
		);
		if ( count( $leads ) >= 50 ) break;
	}
	// Photo optionnelle.
	$photo = '';
	if ( ! empty( $_FILES['photo']['tmp_name'] ) && is_uploaded_file( $_FILES['photo']['tmp_name'] ) ) {
		if ( (int) $_FILES['photo']['size'] <= 8 * MB_IN_BYTES ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( 'photo', 0 );
			if ( ! is_wp_error( $aid ) ) { $photo = (string) wp_get_attachment_url( $aid ); }
		}
	}
	if ( '' === $note && ! $leads && '' === $photo ) wp_send_json_error( array( 'm' => 'Ajoute au moins une note, des prospects ou une photo.' ) );

	$subs = ag_mission_subs();
	// Une soumission par ambassadeur/mission (on remplace la précédente si « refuse »/pending).
	$subs = array_values( array_filter( $subs, function ( $s ) use ( $id, $email ) {
		return ! ( ( $s['mission_id'] ?? '' ) === $id && strtolower( $s['email'] ?? '' ) === $email && 'valide' !== ( $s['status'] ?? '' ) );
	} ) );
	$subs[] = array(
		'id' => uniqid( 'sub_' ), 'mission_id' => $id, 'mission_title' => $m['title'] ?? '',
		'email' => $email, 'name' => $u->display_name ?: $email, 'note' => $note, 'leads' => $leads,
		'photo' => $photo, 'prime' => $m['prime'] ?? '', 'status' => 'pending', 'ts' => time(),
	);
	update_option( 'ag_mission_subs', $subs );
	if ( function_exists( 'ag_push' ) ) ag_push( '📥 Mission rendue : ' . ( $m['title'] ?? '' ), ( $u->display_name ?: $email ) . ' a rendu' . ( $leads ? ' (' . count( $leads ) . ' prospect·s)' : '' ) . '. À valider dans Missions.' );
	wp_send_json_success();
} );

/* ─────────────────────────────────────────────────────────────────────────
 *  PAGE « 📋 Plateformes de missions web » — pitchs prêts à coller.
 * ───────────────────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_plateformes_render' ) ) {
	function ag_plateformes_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$site  = 'alliancegroupe-inc.com';
		$tel   = '07 44 82 95 16';
		$bio   = "Alliance Groupe — création de sites internet professionnels (WordPress sur-mesure), refonte, e-commerce, maintenance et audit de sécurité. Basé à Nantes, j'interviens partout en France. Sites clé en main à partir de 490 €, design moderne, 100% mobile, référencement local inclus, livraison rapide. Templates métiers prêts (avocat, restaurant, barber, coach, artisan, association). Contact : $tel — $site";
		$reply = "Bonjour, je peux réaliser votre site. Je conçois des sites WordPress sur-mesure, rapides, responsive et optimisés pour Google. Ma prestation type : maquette + intégration + mise en ligne + formation. Budget à partir de 490 € selon les fonctionnalités, délai court. Vous pouvez voir mes réalisations sur $site. Disponible pour en discuter quand vous voulez. — Fabrice, Alliance Groupe, $tel";
		$offer = "Je crée votre site internet professionnel WordPress (vitrine ou e-commerce) : design moderne, 100% mobile, rapide et optimisé référencement local. Clé en main à partir de 490 €, formation incluse. Templates métiers disponibles (avocat, resto, barber, coach…). Livraison rapide, satisfaction garantie.";
		$platforms = array(
			array( 'Codeur.com', 'https://www.codeur.com/', '🎯 LA place de marché française des projets web. Les clients postent des projets, tu réponds avec un devis. La plus rentable pour toi.' ),
			array( 'ComeUp (ex-5euros)', 'https://comeup.com/', 'Vends des offres packagées prêtes (réutilise tes templates). Revenus quasi passifs.' ),
			array( 'Malt', 'https://www.malt.fr/', 'La plus grosse plateforme freelance FR. Crée un profil béton avec portfolio + avis.' ),
			array( 'Fiverr', 'https://www.fiverr.com/', 'International, gros volume, offres packagées en anglais/français.' ),
			array( 'Graphiste.com', 'https://www.graphiste.com/', 'Même groupe que Codeur — projets design/web supplémentaires.' ),
		);
		?>
		<div class="wrap">
			<h1>📋 Plateformes de missions web</h1>
			<p style="max-width:820px;color:#444;">Inscris-toi sur ces plateformes pour décrocher des missions de création de site. Copie-colle les textes ci-dessous (déjà rédigés à ton nom) pour aller vite.</p>

			<h2>Où s'inscrire</h2>
			<ul style="max-width:820px;line-height:1.9;">
				<?php foreach ( $platforms as $p ) : ?>
					<li><a href="<?php echo esc_url( $p[1] ); ?>" target="_blank" rel="noopener"><strong><?php echo esc_html( $p[0] ); ?></strong></a> — <?php echo esc_html( $p[2] ); ?></li>
				<?php endforeach; ?>
			</ul>

			<h2 style="margin-top:22px;">Textes prêts à coller</h2>
			<?php
			$blocks = array(
				array( 'Bio de profil (Malt / Codeur / ComeUp)', $bio ),
				array( 'Réponse à un projet (Codeur / Malt)', $reply ),
				array( 'Offre packagée (ComeUp / Fiverr)', $offer ),
			);
			foreach ( $blocks as $i => $b ) : ?>
				<div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:12px 16px;margin:12px 0;max-width:820px;">
					<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
						<strong><?php echo esc_html( $b[0] ); ?></strong>
						<button class="button button-small ag-copy" data-t="ag-txt-<?php echo (int) $i; ?>">📋 Copier</button>
					</div>
					<textarea id="ag-txt-<?php echo (int) $i; ?>" rows="4" style="width:100%;margin-top:8px;font-size:.92rem;"><?php echo esc_textarea( $b[1] ); ?></textarea>
				</div>
			<?php endforeach; ?>
			<p style="max-width:820px;color:#777;">💡 Astuce : sur Codeur, réponds vite (dans l'heure) et personnalise la 1re phrase avec le nom du projet. Sur ComeUp/Fiverr, mets tes captures de templates en visuel d'offre.</p>
		</div>
		<script>
		(function(){
			document.addEventListener('click',function(e){
				var b=e.target.closest?e.target.closest('.ag-copy'):null; if(!b) return; e.preventDefault();
				var t=document.getElementById(b.getAttribute('data-t')); if(!t) return;
				t.select(); t.setSelectionRange(0,99999);
				var done=function(){ b.textContent='✓ Copié'; setTimeout(function(){ b.textContent='📋 Copier'; },1500); };
				if(navigator.clipboard&&navigator.clipboard.writeText){ navigator.clipboard.writeText(t.value).then(done).catch(function(){ try{document.execCommand('copy');done();}catch(_){} }); }
				else { try{document.execCommand('copy');done();}catch(_){} }
			});
		})();
		</script>
		<?php
	}
}
