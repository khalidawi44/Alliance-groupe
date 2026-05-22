<?php
/**
 * Alliance Groupe — Prospection.
 *  - Capture des prospects entrants depuis le chat du site (AJAX).
 *  - Poste de travail admin "Prospection" : chasse aux entreprises (Google Places,
 *    repère celles SANS site web), suivi, et outils pour prospecter soi-même
 *    (message prêt, appel, email, WhatsApp). 100% piloté par l'humain.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── 1. Prospects entrants (chat du site) ───────────────────────── */
if ( ! function_exists( 'ag_lead_handler' ) ) {
	function ag_lead_handler() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ag_lead' ) ) wp_send_json_error( 'nonce', 400 );
		if ( ! empty( $_POST['company'] ) ) wp_send_json_success(); // pot de miel
		$lead = array(
			'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'email'    => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'phone'    => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'interest' => sanitize_text_field( wp_unslash( $_POST['interest'] ?? '' ) ),
			'message'  => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
			'date'     => current_time( 'd/m/Y H:i' ),
			'ts'       => time(),
		);
		if ( ! $lead['email'] && ! $lead['phone'] ) wp_send_json_error( 'contact', 400 );
		$leads = (array) get_option( 'ag_leads', array() );
		$leads[] = $lead;
		update_option( 'ag_leads', array_slice( $leads, -1000 ) );
		$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
		wp_mail( $to, '🎯 Nouveau prospect (chat) : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ), "Intérêt : {$lead['interest']}\nNom : {$lead['name']}\nEmail : {$lead['email']}\nTél : {$lead['phone']}\nMessage : {$lead['message']}\nDate : {$lead['date']}" );
		if ( function_exists( 'ag_calendar_notify' ) ) ag_calendar_notify( '🎯 Prospect à rappeler : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ), "Intérêt : {$lead['interest']}\nEmail : {$lead['email']}\nTél : {$lead['phone']}\n{$lead['message']}" );
		wp_send_json_success();
	}
}
add_action( 'wp_ajax_nopriv_ag_lead', 'ag_lead_handler' );
add_action( 'wp_ajax_ag_lead', 'ag_lead_handler' );

/* ── 2. Réglages : clé Google Places ────────────────────────────── */
add_action( 'admin_init', function () {
	register_setting( 'ag_prospection_cfg', 'ag_places_key', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
} );
if ( ! function_exists( 'ag_places_key' ) ) {
	function ag_places_key() { return trim( (string) get_option( 'ag_places_key', '' ) ); }
}

/* ── 3. Recherche d'entreprises via Google Places (New) ─────────── */
if ( ! function_exists( 'ag_places_search' ) ) {
	function ag_places_search( $query ) {
		$key = ag_places_key();
		if ( '' === $key || '' === trim( $query ) ) return null;
		$resp = wp_remote_post( 'https://places.googleapis.com/v1/places:searchText', array(
			'timeout' => 20,
			'headers' => array(
				'Content-Type'     => 'application/json',
				'X-Goog-Api-Key'   => $key,
				'X-Goog-FieldMask' => 'places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.websiteUri,places.id',
			),
			'body'    => wp_json_encode( array( 'textQuery' => $query, 'languageCode' => 'fr', 'maxResultCount' => 20 ) ),
		) );
		if ( is_wp_error( $resp ) ) return array( 'error' => $resp->get_error_message() );
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code ) return array( 'error' => ( $data['error']['message'] ?? ( 'Erreur ' . $code ) ) );
		$out = array();
		foreach ( (array) ( $data['places'] ?? array() ) as $p ) {
			$out[] = array(
				'name'    => $p['displayName']['text'] ?? '',
				'address' => $p['formattedAddress'] ?? '',
				'phone'   => $p['nationalPhoneNumber'] ?? '',
				'website' => $p['websiteUri'] ?? '',
			);
		}
		return $out;
	}
}

/* ── 4. Enregistrement / suivi des prospects ────────────────────── */
add_action( 'admin_post_ag_prospect_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$list = (array) get_option( 'ag_prospects', array() );
	$list[] = array(
		'id'      => uniqid( 'p_' ),
		'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'type'    => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
		'city'    => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'website' => esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) ),
		'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
		'status'  => 'a_contacter',
		'ts'      => time(),
	);
	update_option( 'ag_prospects', array_slice( $list, -2000 ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-prospects', 'saved' => 1 ), admin_url( 'admin.php' ) ) ); exit;
} );
add_action( 'admin_post_ag_prospect_update', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$st = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
	$del = ! empty( $_POST['delete'] );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			if ( $del ) unset( $list[ $k ] );
			else $list[ $k ]['status'] = $st;
			break;
		}
	}
	update_option( 'ag_prospects', array_values( $list ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-prospects' ), admin_url( 'admin.php' ) ) ); exit;
} );

/* ── 5. Message de prospection prêt à envoyer ───────────────────── */
if ( ! function_exists( 'ag_prospect_message' ) ) {
	function ag_prospect_message( $p ) {
		$site = home_url( '/sites-express' );
		$no   = empty( $p['website'] );
		$hook = $no ? "j'ai remarqué que vous n'avez pas encore de site web" : "j'ai vu votre présence en ligne et je pense qu'on peut la moderniser pour qu'elle vous ramène plus de clients";
		$nom  = $p['name'] ? $p['name'] : 'votre établissement';
		return "Bonjour,\n\nJe me permets de vous contacter au sujet de {$nom} : {$hook}. Chez Alliance Groupe, on crée des sites professionnels à prix fixe (dès 490 €), livrés en quelques jours, sans rendez-vous, et payables en 4×.\n\nUn site qui travaille pour vous 24h/24 et vous ramène des clients. Seriez-vous ouvert(e) à en discuter rapidement ?\n\nNos offres : {$site}\n\nBien à vous,\nAlliance Groupe — contact@alliancegroupe-inc.com\n(Si vous ne souhaitez pas être recontacté, dites-le-moi, j'en prends note.)";
	}
}

/* ── 6. Page admin "Prospection" ────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_menu_page( 'Prospection', 'Prospection', 'manage_options', 'ag-prospects', 'ag_prospects_render', 'dashicons-search', 30 );
} );
if ( ! function_exists( 'ag_prospects_render' ) ) {
	function ag_prospects_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$nonce  = wp_create_nonce( 'ag_prospect' );
		$key    = ag_places_key();
		$q      = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$city   = isset( $_GET['city'] ) ? sanitize_text_field( wp_unslash( $_GET['city'] ) ) : '';
		$results = ( $q || $city ) ? ag_places_search( trim( $q . ' ' . $city ) ) : null;
		$prospects = array_reverse( (array) get_option( 'ag_prospects', array() ) );
		$leads     = array_reverse( (array) get_option( 'ag_leads', array() ) );
		$labels = array( 'a_contacter' => 'À contacter', 'contacte' => 'Contacté', 'interesse' => 'Intéressé', 'client' => 'Client ✅', 'perdu' => 'Perdu' );
		$post = admin_url( 'admin-post.php' );
		?>
		<div class="wrap ag-prospect-wrap">
			<h1 style="display:flex;align-items:center;gap:10px;">🎯 Prospection <span style="font-size:.5em;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;padding:3px 10px;border-radius:100px;">Alliance Groupe</span></h1>
			<p style="max-width:820px;color:#50575e;">Trouve des entreprises qui ont besoin d'un site (Google Maps repère celles <strong>sans site web</strong>), ajoute-les à ta liste, puis prospecte-les toi-même avec le message prêt, l'appel, l'email ou WhatsApp. Tu gardes la main sur tout.</p>

			<!-- Chasse Google Places -->
			<div style="max-width:980px;margin-top:14px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;border-radius:6px;">
				<h2 style="margin-top:0;">🔎 Trouver des entreprises</h2>
				<?php if ( '' === $key ) : ?>
					<p>Pour la recherche automatique, ajoute ta <strong>clé Google Places (New)</strong> ci-dessous (dans ton projet Google Cloud → active « Places API (New) » → crée une clé API). En attendant, tu peux ajouter des prospects à la main plus bas, ou chercher sur <a href="https://www.google.com/maps" target="_blank" rel="noopener">Google Maps</a> (ex. « restaurant Nantes ») et copier les infos.</p>
					<form method="post" action="options.php">
						<?php settings_fields( 'ag_prospection_cfg' ); ?>
						<input type="text" name="ag_places_key" value="" class="regular-text" placeholder="Clé API Google Places" style="width:420px;">
						<?php submit_button( 'Enregistrer la clé', 'secondary', 'submit', false ); ?>
					</form>
				<?php else : ?>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
						<input type="hidden" name="page" value="ag-prospects">
						<input type="text" name="q" value="<?php echo esc_attr( $q ); ?>" placeholder="Type d'activité (ex : restaurant, coiffeur, plombier)" style="width:320px;">
						<input type="text" name="city" value="<?php echo esc_attr( $city ); ?>" placeholder="Ville (ex : Nantes)" style="width:200px;">
						<?php submit_button( 'Chercher', 'primary', 'submit', false ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-prospects' ) ); ?>" class="button">Réinitialiser</a>
					</form>
					<?php if ( is_array( $results ) && isset( $results['error'] ) ) : ?>
						<p style="color:#b32d2e;">Erreur Places : <?php echo esc_html( $results['error'] ); ?> (vérifie que « Places API (New) » est activée et la facturation aussi.)</p>
					<?php elseif ( is_array( $results ) ) : ?>
						<p style="color:#50575e;"><?php echo count( $results ); ?> résultat(s). Les ❗ <strong>sans site web</strong> sont les meilleurs prospects.</p>
						<table class="widefat striped"><thead><tr><th>Entreprise</th><th>Adresse</th><th>Téléphone</th><th>Site web</th><th></th></tr></thead><tbody>
						<?php foreach ( $results as $r ) : if ( empty( $r['name'] ) ) continue; ?>
							<tr>
								<td><strong><?php echo esc_html( $r['name'] ); ?></strong></td>
								<td><?php echo esc_html( $r['address'] ); ?></td>
								<td><?php echo esc_html( $r['phone'] ); ?></td>
								<td><?php echo $r['website'] ? '<a href="' . esc_url( $r['website'] ) . '" target="_blank" rel="noopener">site ✓</a>' : '<strong style="color:#b32d2e;">❗ Pas de site</strong>'; ?></td>
								<td>
									<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
										<input type="hidden" name="action" value="ag_prospect_save">
										<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
										<input type="hidden" name="name" value="<?php echo esc_attr( $r['name'] ); ?>">
										<input type="hidden" name="type" value="<?php echo esc_attr( $q ); ?>">
										<input type="hidden" name="city" value="<?php echo esc_attr( $city ); ?>">
										<input type="hidden" name="phone" value="<?php echo esc_attr( $r['phone'] ); ?>">
										<input type="hidden" name="website" value="<?php echo esc_attr( $r['website'] ); ?>">
										<input type="hidden" name="address" value="<?php echo esc_attr( $r['address'] ); ?>">
										<button class="button button-primary">+ Suivre</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody></table>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<!-- Ajout manuel -->
			<div style="max-width:980px;margin-top:18px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-radius:6px;">
				<h2 style="margin-top:0;">➕ Ajouter un prospect à la main</h2>
				<form method="post" action="<?php echo esc_url( $post ); ?>">
					<input type="hidden" name="action" value="ag_prospect_save">
					<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
					<input type="text" name="name" placeholder="Nom de l'entreprise *" required style="width:240px;">
					<input type="text" name="type" placeholder="Type (resto, artisan…)" style="width:160px;">
					<input type="text" name="city" placeholder="Ville" style="width:140px;">
					<input type="text" name="phone" placeholder="Téléphone" style="width:140px;">
					<input type="email" name="email" placeholder="Email" style="width:200px;">
					<input type="url" name="website" placeholder="Site web (si existant)" style="width:200px;">
					<?php submit_button( 'Ajouter', 'secondary', 'submit', false ); ?>
				</form>
			</div>

			<!-- Mes prospects -->
			<h2 style="margin-top:26px;">📋 Mes prospects (<?php echo count( $prospects ); ?>)</h2>
			<?php if ( empty( $prospects ) ) : ?>
				<p>Aucun prospect pour l'instant. Cherche des entreprises ci-dessus ou ajoute-en à la main.</p>
			<?php else : ?>
				<table class="widefat striped"><thead><tr><th>Entreprise</th><th>Ville</th><th>Contact</th><th>Statut</th><th>Prospecter</th><th></th></tr></thead><tbody>
				<?php foreach ( $prospects as $p ) :
					$digits = preg_replace( '/[^0-9]/', '', $p['phone'] ?? '' );
					$msg    = ag_prospect_message( $p );
					$mailto = $p['email'] ? 'mailto:' . rawurlencode( $p['email'] ) . '?subject=' . rawurlencode( 'Votre site web — Alliance Groupe' ) . '&body=' . rawurlencode( $msg ) : '';
					$wa     = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
					?>
					<tr>
						<td><strong><?php echo esc_html( $p['name'] ?? '' ); ?></strong><?php echo empty( $p['website'] ) ? ' <span style="color:#b32d2e;">❗</span>' : ''; ?><br><small><?php echo esc_html( $p['type'] ?? '' ); ?></small></td>
						<td><?php echo esc_html( $p['city'] ?? '' ); ?></td>
						<td>
							<?php if ( ! empty( $p['phone'] ) ) : ?><a href="tel:<?php echo esc_attr( $p['phone'] ); ?>">📞 <?php echo esc_html( $p['phone'] ); ?></a><br><?php endif; ?>
							<?php if ( ! empty( $p['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a><?php endif; ?>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_prospect_update">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="id" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>">
								<select name="status" onchange="this.form.submit()">
									<?php foreach ( $labels as $k => $lab ) : ?>
										<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $p['status'] ?? 'a_contacter', $k ); ?>><?php echo esc_html( $lab ); ?></option>
									<?php endforeach; ?>
								</select>
							</form>
						</td>
						<td>
							<?php if ( $wa ) : ?><a class="button button-small" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener">WhatsApp</a> <?php endif; ?>
							<?php if ( $mailto ) : ?><a class="button button-small" href="<?php echo esc_url( $mailto ); ?>">Email</a> <?php endif; ?>
							<details style="display:inline-block;margin-top:4px;"><summary class="button button-small">Message</summary><textarea readonly rows="7" style="width:340px;margin-top:6px;"><?php echo esc_textarea( $msg ); ?></textarea></details>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;" onsubmit="return confirm('Supprimer ce prospect ?');">
								<input type="hidden" name="action" value="ag_prospect_update">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="id" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>">
								<input type="hidden" name="delete" value="1">
								<button class="button button-small button-link-delete">✕</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>

			<!-- Prospects entrants (chat) -->
			<h2 style="margin-top:26px;">💬 Prospects entrants (chat du site) (<?php echo count( $leads ); ?>)</h2>
			<?php if ( empty( $leads ) ) : ?>
				<p>Aucun pour l'instant. Ils arrivent dès qu'un visiteur laisse ses coordonnées dans le chat.</p>
			<?php else : ?>
				<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Tél</th><th>Intérêt</th><th>Message</th></tr></thead><tbody>
				<?php foreach ( $leads as $l ) : ?>
					<tr><td><?php echo esc_html( $l['date'] ?? '' ); ?></td><td><?php echo esc_html( $l['name'] ?? '' ); ?></td>
					<td><?php echo ! empty( $l['email'] ) ? '<a href="mailto:' . esc_attr( $l['email'] ) . '">' . esc_html( $l['email'] ) . '</a>' : ''; ?></td>
					<td><?php echo ! empty( $l['phone'] ) ? '<a href="tel:' . esc_attr( $l['phone'] ) . '">' . esc_html( $l['phone'] ) . '</a>' : ''; ?></td>
					<td><?php echo esc_html( $l['interest'] ?? '' ); ?></td><td><?php echo esc_html( $l['message'] ?? '' ); ?></td></tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>
		</div>
		<?php
	}
}
