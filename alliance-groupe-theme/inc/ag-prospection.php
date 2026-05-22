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
	$ok = ag_prospect_add_record( array(
		'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'type'    => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
		'city'    => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'website' => esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) ),
		'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
	) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-prospects', 'saved' => $ok ? 1 : 0, 'dup' => $ok ? 0 : 1 ), admin_url( 'admin.php' ) ) ); exit;
} );
add_action( 'admin_post_ag_prospect_update', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$del = ! empty( $_POST['delete'] );
	$has_status = array_key_exists( 'status', $_POST );
	$has_owner  = array_key_exists( 'owner', $_POST );
	$st = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
	$owner = sanitize_email( wp_unslash( $_POST['owner'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			if ( $del ) { unset( $list[ $k ] ); break; }
			if ( $has_status ) $list[ $k ]['status'] = $st;
			if ( $has_owner ) {
				$list[ $k ]['owner_email'] = $owner;
				$rec = ( $owner && function_exists( 'ag_ambassadeur_record' ) ) ? ag_ambassadeur_record( $owner ) : null;
				$list[ $k ]['owner_name'] = $rec['name'] ?? '';
			}
			break;
		}
	}
	update_option( 'ag_prospects', array_values( $list ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-prospects' ), admin_url( 'admin.php' ) ) ); exit;
} );

/* ── 4b. Type de présence en ligne (réseau social = pas un vrai site) ─ */
if ( ! function_exists( 'ag_site_kind' ) ) {
	function ag_site_kind( $url ) {
		$u = strtolower( trim( (string) $url ) );
		if ( '' === $u ) return array( 'none', '❗ Pas de site' );
		$social = array( 'facebook.com', 'fb.com', 'fb.me', 'instagram.com', 'tiktok.com', 'twitter.com', 'x.com', 'linktr.ee', 'snapchat.com', 'business.site', 'linkedin.com', 'youtube.com', 'wa.me', 'pages.', 'google.com/maps' );
		foreach ( $social as $s ) { if ( false !== strpos( $u, $s ) ) return array( 'social', '⚠ Réseau social' ); }
		return array( 'real', 'site ✓' );
	}
}

/* ── 4d. CRM partagé : statuts, anti-doublon, ajout centralisé ──── */
if ( ! function_exists( 'ag_prospect_statuses' ) ) {
	function ag_prospect_statuses() {
		return array(
			'nouveau'          => '🆕 Nouveau',
			'contacte'         => '📞 Contacté',
			'relance'          => '🔁 Relancé',
			'sans_reponse'     => '🔇 Sans réponse',
			'interesse'        => '🔥 Intéressé',
			'client'           => '✅ Client',
			'refus'            => '✋ Refusé',
			'ne_pas_contacter' => '🚫 Ne plus contacter',
		);
	}
}
if ( ! function_exists( 'ag_prospect_blocked' ) ) {
	/** Statuts qui sortent le prospect du circuit (personne ne le recontacte). */
	function ag_prospect_blocked( $status ) { return in_array( $status, array( 'refus', 'ne_pas_contacter', 'client' ), true ); }
}
if ( ! function_exists( 'ag_prospect_sig' ) ) {
	function ag_prospect_sig( $name, $city ) { return strtolower( trim( preg_replace( '/\s+/', ' ', (string) $name ) ) ) . '|' . strtolower( trim( (string) $city ) ); }
}
if ( ! function_exists( 'ag_prospect_find' ) ) {
	/** Cherche un prospect existant (anti-doublon) par nom+ville ou téléphone. */
	function ag_prospect_find( $name, $city, $phone = '' ) {
		$sig    = ag_prospect_sig( $name, $city );
		$digits = preg_replace( '/[^0-9]/', '', (string) $phone );
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( ag_prospect_sig( $p['name'] ?? '', $p['city'] ?? '' ) === $sig ) return $p;
			if ( $digits && strlen( $digits ) >= 6 && preg_replace( '/[^0-9]/', '', $p['phone'] ?? '' ) === $digits ) return $p;
		}
		return null;
	}
}
if ( ! function_exists( 'ag_prospect_add_record' ) ) {
	/** Ajoute un prospect SANS doublon. Retourne true si ajouté, false si déjà présent. */
	function ag_prospect_add_record( $data ) {
		if ( empty( $data['name'] ) ) return false;
		if ( ag_prospect_find( $data['name'], $data['city'] ?? '', $data['phone'] ?? '' ) ) return false;
		$list   = (array) get_option( 'ag_prospects', array() );
		$list[] = array_merge( array(
			'id' => uniqid( 'p_' ), 'name' => '', 'type' => '', 'city' => '', 'phone' => '', 'email' => '',
			'website' => '', 'address' => '', 'status' => 'nouveau', 'owner_email' => '', 'owner_name' => '',
			'notes' => '', 'source' => 'manuel', 'ts' => time(),
		), $data );
		update_option( 'ag_prospects', array_slice( $list, -5000 ) );
		return true;
	}
}

/* ── 4c. Ajout d'un prospect en AJAX (depuis les résultats, sans recharger) ─ */
add_action( 'wp_ajax_ag_prospect_add', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_send_json_error();
	$ok = ag_prospect_add_record( array(
		'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'type'    => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
		'city'    => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'website' => esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) ),
		'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
		'source'  => 'recherche',
	) );
	wp_send_json_success( array( 'added' => $ok ) );
} );

/* ── 5. Priorité (qui en a vraiment besoin), pourquoi, et message émotionnel ─ */
if ( ! function_exists( 'ag_prospect_score' ) ) {
	/** Score de besoin 0-100 : plus c'est haut, plus l'entreprise a besoin d'un site. */
	function ag_prospect_score( $p ) {
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		$s = ( 'none' === $kind ) ? 80 : ( ( 'social' === $kind ) ? 60 : 20 );
		// Métiers où un site rapporte beaucoup (visibilité, réservations, commandes).
		$type = strtolower( $p['type'] ?? '' );
		$hot = array( 'restaurant', 'coiffeur', 'barbier', 'institut', 'beauté', 'plombier', 'électricien', 'garagiste', 'artisan', 'boulangerie', 'bar', 'coach', 'photographe', 'fleuriste', 'avocat', 'commerce', 'btp' );
		foreach ( $hot as $h ) { if ( false !== strpos( $type, $h ) ) { $s += 15; break; } }
		if ( ! empty( $p['phone'] ) ) $s += 5; // joignable = actionnable
		return min( 100, $s );
	}
}
if ( ! function_exists( 'ag_prospect_why' ) ) {
	/** Pourquoi cette entreprise a besoin d'un site + ce que ça lui apporte. */
	function ag_prospect_why( $p ) {
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		if ( 'none' === $kind )   $gap = "n'a aucun site web : elle est quasi invisible sur Google, et perd les clients qui la cherchent en ligne.";
		elseif ( 'social' === $kind ) $gap = "n'a qu'une page de réseau social : pas de vraie vitrine, pas de référencement Google, dépendante d'une plateforme qui peut fermer son compte.";
		else $gap = "a un site, mais il peut être modernisé (mobile, rapidité, réservation/commande) pour convertir plus.";
		$type = strtolower( $p['type'] ?? '' );
		$bene = "être trouvée 24h/24 sur Google, rassurer les clients, et recevoir des demandes en automatique";
		if ( false !== strpos( $type, 'restaurant' ) || false !== strpos( $type, 'bar' ) ) $bene = "afficher son menu, prendre des réservations en ligne et remplir sa salle même la nuit";
		elseif ( false !== strpos( $type, 'coiffeur' ) || false !== strpos( $type, 'barbier' ) || false !== strpos( $type, 'institut' ) || false !== strpos( $type, 'beauté' ) ) $bene = "permettre la prise de rendez-vous en ligne 24h/24 et réduire les trous dans le planning";
		elseif ( false !== strpos( $type, 'plombier' ) || false !== strpos( $type, 'électricien' ) || false !== strpos( $type, 'garagiste' ) || false !== strpos( $type, 'artisan' ) || false !== strpos( $type, 'btp' ) ) $bene = "capter les demandes de devis urgentes et apparaître avant les concurrents sur Google";
		return 'Cette entreprise ' . $gap . ' Un site lui permettrait de ' . $bene . '.';
	}
}
if ( ! function_exists( 'ag_prospect_message' ) ) {
	/** Message personnalisé et émotionnel, adapté au métier et au manque constaté. */
	function ag_prospect_message( $p ) {
		$site = home_url( '/sites-express' );
		$nom  = $p['name'] ? $p['name'] : 'votre établissement';
		$kind = ag_site_kind( $p['website'] ?? '' )[0];
		$type = strtolower( $p['type'] ?? '' );

		if ( 'none' === $kind )       $accroche = "j'ai cherché {$nom} sur Google… et je n'ai trouvé aucun site. Honnêtement, ça m'a fait quelque chose : vous faites sûrement un travail de qualité, mais des clients passent à côté de vous chaque jour, juste parce qu'ils ne vous trouvent pas en ligne.";
		elseif ( 'social' === $kind ) $accroche = "j'ai vu {$nom} sur les réseaux, mais pas de vrai site. Le souci, c'est que tout votre travail repose sur une page que vous ne possédez pas vraiment — et beaucoup de clients vous cherchent sur Google, pas sur les réseaux.";
		else                          $accroche = "j'ai regardé le site de {$nom}, et je pense sincèrement qu'avec quelques améliorations il pourrait vous ramener bien plus de clients.";

		$promesse = "imaginez : pendant que vous travaillez (ou que vous dormez), votre site présente votre savoir-faire, rassure les clients et reçoit des demandes tout seul.";
		if ( false !== strpos( $type, 'restaurant' ) || false !== strpos( $type, 'bar' ) ) $promesse = "imaginez votre salle qui se remplit grâce aux réservations prises en ligne, même tard le soir, sans que vous touchiez votre téléphone.";
		elseif ( false !== strpos( $type, 'coiffeur' ) || false !== strpos( $type, 'barbier' ) || false !== strpos( $type, 'beauté' ) || false !== strpos( $type, 'institut' ) ) $promesse = "imaginez un agenda qui se remplit tout seul : vos clients prennent rendez-vous en ligne, 24h/24, même quand le salon est fermé.";
		elseif ( false !== strpos( $type, 'plombier' ) || false !== strpos( $type, 'électricien' ) || false !== strpos( $type, 'garagiste' ) || false !== strpos( $type, 'artisan' ) ) $promesse = "imaginez recevoir les demandes de devis urgentes directement sur votre téléphone, avant que le client n'appelle le concurrent d'à côté.";

		return "Bonjour,\n\n{$accroche}\n\nJe suis d'Alliance Groupe. On crée des sites professionnels à prix fixe (dès 490 €), livrés en quelques jours, payables en 4×, et sans rendez-vous. {$promesse}\n\nVotre métier mérite d'être vu. Est-ce que ça vous dirait qu'on en parle 5 minutes, sans engagement ?\n\nUn aperçu de ce qu'on fait : {$site}\n\nBien à vous,\nAlliance Groupe — contact@alliancegroupe-inc.com\n(Si vous préférez ne pas être recontacté, dites-le-moi simplement, j'en prends note et je n'insiste pas.)";
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
		if ( is_array( $results ) && ! isset( $results['error'] ) ) {
			foreach ( $results as &$_r ) { $_r['type'] = $q; } unset( $_r );
			usort( $results, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
		}
		$prospects = (array) get_option( 'ag_prospects', array() );
		$leads     = array_reverse( (array) get_option( 'ag_leads', array() ) );
		$labels    = ag_prospect_statuses();
		$post      = admin_url( 'admin-post.php' );
		$ambs = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) { if ( ! empty( $a['email'] ) ) $ambs[ $a['email'] ] = $a['name'] ?? $a['email']; }
		$f_status = isset( $_GET['fstatus'] ) ? sanitize_text_field( wp_unslash( $_GET['fstatus'] ) ) : '';
		$f_q      = isset( $_GET['fq'] ) ? sanitize_text_field( wp_unslash( $_GET['fq'] ) ) : '';
		$sortby   = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'besoin';
		if ( '' !== $f_status ) $prospects = array_filter( $prospects, function ( $p ) use ( $f_status ) { return ( $p['status'] ?? 'nouveau' ) === $f_status; } );
		if ( '' !== $f_q ) { $needle = strtolower( $f_q ); $prospects = array_filter( $prospects, function ( $p ) use ( $needle ) { return false !== strpos( strtolower( ( $p['name'] ?? '' ) . ' ' . ( $p['city'] ?? '' ) . ' ' . ( $p['type'] ?? '' ) ), $needle ); } ); }
		$prospects = array_values( $prospects );
		usort( $prospects, function ( $a, $b ) use ( $sortby ) {
			if ( 'nom' === $sortby )  return strcasecmp( $a['name'] ?? '', $b['name'] ?? '' );
			if ( 'date' === $sortby ) return ( $b['ts'] ?? 0 ) <=> ( $a['ts'] ?? 0 );
			return ag_prospect_score( $b ) <=> ag_prospect_score( $a );
		} );
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
					<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="ag-search-form">
						<input type="hidden" name="page" value="ag-prospects">
						<input type="text" name="q" id="ag-q" value="<?php echo esc_attr( $q ); ?>" placeholder="Type d'activité (ex : restaurant, coiffeur, plombier)" style="width:320px;">
						<input type="text" name="city" value="<?php echo esc_attr( $city ); ?>" placeholder="Ville (ex : Nantes)" style="width:200px;">
						<?php submit_button( 'Chercher', 'primary', 'submit', false ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-prospects' ) ); ?>" class="button">Réinitialiser</a>
					</form>
					<div style="margin:8px 0 0;color:#50575e;font-size:.9rem;">Idées rapides :
						<?php foreach ( array( 'restaurant', 'coiffeur', 'barbier', 'plombier', 'électricien', 'garagiste', 'boulangerie', 'bar', 'institut de beauté', 'artisan', 'coach sportif', 'photographe', 'fleuriste', 'avocat' ) as $c ) : ?>
							<button type="button" class="button button-small ag-chip" data-q="<?php echo esc_attr( $c ); ?>"><?php echo esc_html( $c ); ?></button>
						<?php endforeach; ?>
					</div>
					<?php if ( is_array( $results ) && isset( $results['error'] ) ) : ?>
						<p style="color:#b32d2e;">Erreur Places : <?php echo esc_html( $results['error'] ); ?> (vérifie que « Places API (New) » est activée et la facturation aussi.)</p>
					<?php elseif ( is_array( $results ) ) : ?>
						<p style="color:#50575e;margin-top:14px;"><?php echo count( $results ); ?> résultat(s). <label style="margin-left:8px;"><input type="checkbox" id="ag-onlyno"> N'afficher que ceux <strong>sans vrai site</strong> (réseaux sociaux inclus)</label></p>
						<table class="widefat striped" id="ag-results"><thead><tr><th>Entreprise</th><th>Adresse</th><th>Téléphone</th><th>Présence en ligne</th><th></th></tr></thead><tbody>
						<?php foreach ( $results as $r ) : if ( empty( $r['name'] ) ) continue; $kind = ag_site_kind( $r['website'] ); ?>
							<tr data-kind="<?php echo esc_attr( $kind[0] ); ?>">
								<td><strong><?php echo esc_html( $r['name'] ); ?></strong></td>
								<td><?php echo esc_html( $r['address'] ); ?></td>
								<td><?php echo esc_html( $r['phone'] ); ?></td>
								<td><?php echo ( 'real' === $kind[0] ) ? '<a href="' . esc_url( $r['website'] ) . '" target="_blank" rel="noopener">site ✓</a>' : '<strong style="color:#b32d2e;">' . esc_html( $kind[1] ) . '</strong>'; ?></td>
								<td>
									<button type="button" class="button button-primary ag-add"
										data-name="<?php echo esc_attr( $r['name'] ); ?>" data-type="<?php echo esc_attr( $q ); ?>"
										data-city="<?php echo esc_attr( $city ); ?>" data-phone="<?php echo esc_attr( $r['phone'] ); ?>"
										data-website="<?php echo esc_attr( $r['website'] ); ?>" data-address="<?php echo esc_attr( $r['address'] ); ?>">+ Suivre</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody></table>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<!-- Agent automatique -->
			<div style="max-width:980px;margin-top:18px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #F37A1F;border-radius:6px;">
				<h2 style="margin-top:0;">🤖 Agent automatique</h2>
				<?php if ( '' === $key ) : ?>
					<p>Ajoute d'abord ta <strong>clé Google Places</strong> ci-dessus pour activer la recherche automatique.</p>
				<?php else :
					$auto = (array) get_option( 'ag_auto_searches', array() );
					$lr   = (array) get_option( 'ag_prospect_lastrun', array() );
					?>
					<p>L'agent cherche <strong>tout seul (1×/jour)</strong> les recherches ci-dessous et ajoute les nouvelles entreprises à ta liste (tu reçois un email récap). <strong>Il ne contacte personne automatiquement</strong> — tu prospectes toi-même, en 1 clic.</p>
					<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:10px;">
						<input type="hidden" name="action" value="ag_autosearch_add">
						<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
						<input type="text" name="q" placeholder="Type (restaurant, coiffeur…)" required style="width:240px;">
						<input type="text" name="city" placeholder="Ville" style="width:160px;">
						<?php submit_button( '+ Ajouter une recherche auto', 'primary', 'submit', false ); ?>
					</form>
					<?php if ( $auto ) : ?>
						<ul style="margin:0 0 10px;">
						<?php foreach ( $auto as $i => $a ) : ?>
							<li style="margin-bottom:4px;">🔁 <strong><?php echo esc_html( ( $a['q'] ?? '' ) . ( ! empty( $a['city'] ) ? ' — ' . $a['city'] : '' ) ); ?></strong>
								<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:inline;">
									<input type="hidden" name="action" value="ag_autosearch_del"><input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>"><input type="hidden" name="i" value="<?php echo (int) $i; ?>">
									<button class="button-link" style="color:#b32d2e;">retirer</button>
								</form>
							</li>
						<?php endforeach; ?>
						</ul>
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:inline;">
							<input type="hidden" name="action" value="ag_autosearch_run"><input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
							<button class="button">▶ Lancer maintenant</button>
						</form>
						<?php if ( ! empty( $lr['ts'] ) ) : ?><span style="color:#50575e;margin-left:10px;">Dernier passage : <?php echo esc_html( date_i18n( 'd/m/Y H:i', $lr['ts'] ) ); ?> (+<?php echo (int) ( $lr['added'] ?? 0 ); ?>)</span><?php endif; ?>
					<?php else : ?>
						<p style="color:#50575e;">Ajoute au moins une recherche pour activer l'agent.</p>
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
			<h2 style="margin-top:26px;">📋 Mes prospects (<?php echo count( $prospects ); ?>) — triés par <strong>besoin</strong></h2>
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin-bottom:10px;">
				<input type="hidden" name="page" value="ag-prospects">
				<input type="search" name="fq" value="<?php echo esc_attr( $f_q ); ?>" placeholder="Filtrer (nom, ville, métier)" style="width:240px;">
				<select name="fstatus"><option value="">Tous les statuts</option><?php foreach ( $labels as $k => $lab ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $f_status, $k ); ?>><?php echo esc_html( $lab ); ?></option><?php endforeach; ?></select>
				<select name="sort"><option value="besoin" <?php selected( $sortby, 'besoin' ); ?>>Tri : besoin (priorité)</option><option value="date" <?php selected( $sortby, 'date' ); ?>>Tri : plus récents</option><option value="nom" <?php selected( $sortby, 'nom' ); ?>>Tri : nom</option></select>
				<?php submit_button( 'Filtrer', 'secondary', 'submit', false ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-prospects' ) ); ?>" class="button">Tout voir</a>
			</form>
			<?php if ( empty( $prospects ) ) : ?>
				<p>Aucun prospect (avec ces filtres). Cherche des entreprises ci-dessus ou ajoute-en à la main.</p>
			<?php else : ?>
				<table class="widefat striped"><thead><tr><th>Priorité</th><th>Entreprise</th><th>Pourquoi (besoin)</th><th>Contact</th><th>Statut</th><th>Assigné à</th><th>Prospecter</th><th></th></tr></thead><tbody>
				<?php foreach ( $prospects as $p ) :
					$digits = preg_replace( '/[^0-9]/', '', $p['phone'] ?? '' );
					$msg    = ag_prospect_message( $p );
					$mailto = $p['email'] ? 'mailto:' . rawurlencode( $p['email'] ) . '?subject=' . rawurlencode( 'Votre site web — Alliance Groupe' ) . '&body=' . rawurlencode( $msg ) : '';
					$wa     = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
					$score  = ag_prospect_score( $p );
					$scol   = $score >= 80 ? '#b32d2e' : ( $score >= 60 ? '#bd7b00' : '#50575e' );
					$blocked = ag_prospect_blocked( $p['status'] ?? '' );
					?>
					<tr<?php echo $blocked ? ' style="opacity:.55;"' : ''; ?>>
						<td><span style="display:inline-block;min-width:34px;text-align:center;font-weight:800;color:#fff;background:<?php echo esc_attr( $scol ); ?>;border-radius:6px;padding:2px 6px;"><?php echo (int) $score; ?></span></td>
						<td><strong><?php echo esc_html( $p['name'] ?? '' ); ?></strong><?php $pk = ag_site_kind( $p['website'] ?? '' ); echo ( 'real' !== $pk[0] ) ? ' <span style="color:#b32d2e;" title="' . esc_attr( $pk[1] ) . '">❗</span>' : ''; ?><br><small><?php echo esc_html( ( $p['type'] ?? '' ) . ( ! empty( $p['city'] ) ? ' · ' . $p['city'] : '' ) ); ?></small></td>
						<td style="max-width:280px;font-size:.85em;color:#50575e;"><?php echo esc_html( ag_prospect_why( $p ) ); ?></td>
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
										<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $p['status'] ?? 'nouveau', $k ); ?>><?php echo esc_html( $lab ); ?></option>
									<?php endforeach; ?>
								</select>
							</form>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_prospect_update">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="id" value="<?php echo esc_attr( $p['id'] ?? '' ); ?>">
								<select name="owner" onchange="this.form.submit()">
									<option value="">— personne —</option>
									<?php foreach ( $ambs as $em => $nm ) : ?><option value="<?php echo esc_attr( $em ); ?>" <?php selected( $p['owner_email'] ?? '', $em ); ?>><?php echo esc_html( $nm ); ?></option><?php endforeach; ?>
								</select>
							</form>
						</td>
						<td>
							<?php if ( $blocked ) : ?><em style="color:#50575e;">à ne pas recontacter</em><?php else : ?>
							<?php if ( $wa ) : ?><a class="button button-small" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener">WhatsApp</a> <?php endif; ?>
							<?php if ( $mailto ) : ?><a class="button button-small" href="<?php echo esc_url( $mailto ); ?>">Email</a> <?php endif; ?>
							<details style="display:inline-block;margin-top:4px;"><summary class="button button-small">Message émotionnel</summary><textarea readonly rows="9" style="width:360px;margin-top:6px;"><?php echo esc_textarea( $msg ); ?></textarea></details>
							<?php endif; ?>
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
		<script>
		(function(){
			var nonce=<?php echo wp_json_encode( $nonce ); ?>;
			// Raccourcis métiers : remplit la recherche et lance.
			document.querySelectorAll('.ag-chip').forEach(function(b){ b.addEventListener('click',function(){ var i=document.getElementById('ag-q'); if(i){ i.value=b.getAttribute('data-q'); document.getElementById('ag-search-form').submit(); } }); });
			// Filtre "sans vrai site".
			var only=document.getElementById('ag-onlyno'); if(only){ only.addEventListener('change',function(){ document.querySelectorAll('#ag-results tbody tr').forEach(function(tr){ tr.style.display=(only.checked && tr.getAttribute('data-kind')==='real')?'none':''; }); }); }
			// Ajout en AJAX (sans recharger -> la recherche reste).
			document.querySelectorAll('.ag-add').forEach(function(b){ b.addEventListener('click',function(){
				var fd=new FormData(); fd.append('action','ag_prospect_add'); fd.append('_n',nonce);
				['name','type','city','phone','website','address'].forEach(function(k){ fd.append(k, b.getAttribute('data-'+k)||''); });
				b.disabled=true; b.textContent='…';
				fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ b.textContent=(j&&j.success)?'✓ Ajouté':'Erreur'; }).catch(function(){ b.textContent='Erreur'; b.disabled=false; });
			}); });
		})();
		</script>
		<?php
	}
}

/* ── 7. Agent automatique (cron quotidien : recherche planifiée) ─── */
add_action( 'admin_post_ag_autosearch_add', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
	$city = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	if ( $q || $city ) { $s = (array) get_option( 'ag_auto_searches', array() ); $s[] = array( 'q' => $q, 'city' => $city ); update_option( 'ag_auto_searches', $s ); }
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );
add_action( 'admin_post_ag_autosearch_del', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	$i = (int) ( $_POST['i'] ?? -1 ); $s = (array) get_option( 'ag_auto_searches', array() );
	if ( isset( $s[ $i ] ) ) { unset( $s[ $i ] ); update_option( 'ag_auto_searches', array_values( $s ) ); }
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects' ) ); exit;
} );
add_action( 'admin_post_ag_autosearch_run', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_prospect' ) ) wp_die( 'no' );
	ag_run_auto_prospection();
	wp_safe_redirect( admin_url( 'admin.php?page=ag-prospects&ran=1' ) ); exit;
} );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_prospect_cron' ) ) wp_schedule_event( time() + 600, 'daily', 'ag_prospect_cron' );
} );
add_action( 'ag_prospect_cron', 'ag_run_auto_prospection' );
if ( ! function_exists( 'ag_run_auto_prospection' ) ) {
	function ag_run_auto_prospection() {
		$searches = (array) get_option( 'ag_auto_searches', array() );
		if ( empty( $searches ) || '' === ag_places_key() ) return;
		$added = 0; $nosite = 0;
		foreach ( $searches as $s ) {
			$res = ag_places_search( trim( ( $s['q'] ?? '' ) . ' ' . ( $s['city'] ?? '' ) ) );
			if ( ! is_array( $res ) || isset( $res['error'] ) ) continue;
			foreach ( $res as $r ) {
				if ( empty( $r['name'] ) ) continue;
				$ok = ag_prospect_add_record( array(
					'name' => $r['name'], 'type' => $s['q'] ?? '', 'city' => $s['city'] ?? '',
					'phone' => $r['phone'] ?? '', 'website' => $r['website'] ?? '', 'address' => $r['address'] ?? '', 'source' => 'robot',
				) );
				if ( $ok ) { $added++; if ( 'real' !== ag_site_kind( $r['website'] ?? '' )[0] ) $nosite++; }
			}
		}
		update_option( 'ag_prospect_lastrun', array( 'ts' => time(), 'added' => $added ) );
		if ( $added ) {
			$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
			wp_mail( $to, "🤖 $added nouveaux prospects trouvés (dont $nosite sans vrai site)", "L'agent automatique a ajouté $added entreprises a ta liste ($nosite sans vrai site web — prioritaires).\n\nVa les prospecter : " . admin_url( 'admin.php?page=ag-prospects' ) );
		}
	}
}

/* ── 8. Côté ambassadeur : ses prospects assignés (liste partagée) ─ */
if ( ! function_exists( 'ag_prospects_for_owner' ) ) {
	function ag_prospects_for_owner( $email ) {
		$out = array(); $email = strtolower( (string) $email );
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) { if ( strtolower( $p['owner_email'] ?? '' ) === $email ) $out[] = $p; }
		usort( $out, function ( $a, $b ) { return ag_prospect_score( $b ) <=> ag_prospect_score( $a ); } );
		return $out;
	}
}
add_action( 'admin_post_ag_amb_prospect_status', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_die( 'no' );
	$email = strtolower( wp_get_current_user()->user_email );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$st = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
	$list = (array) get_option( 'ag_prospects', array() );
	foreach ( $list as $k => $p ) {
		if ( ( $p['id'] ?? '' ) === $id && strtolower( $p['owner_email'] ?? '' ) === $email ) { $list[ $k ]['status'] = $st; break; }
	}
	update_option( 'ag_prospects', array_values( $list ) );
	wp_safe_redirect( home_url( '/espace-ambassadeur#prospects' ) ); exit;
} );
