<?php
/**
 * AG Robot Recrutement Ambassadeurs — sous-menu « 🤖 Robot Ambassadeurs ».
 *
 * 3 moteurs, 100 % conformes (aucun scraping de données personnelles de
 * demandeurs d'emploi — interdit RGPD/CGU) :
 *   1. DIFFUSION + CAPTURE : annonce prête (texte + lien d'inscription + QR) +
 *      liste curée d'endroits où poster (France Travail dépôt d'offre, Indeed,
 *      HelloWork, groupes FB/Telegram emploi, missions locales, écoles…),
 *      avec marquage « posté ». Les candidats répondent → CRM.
 *   2. BASSINS D'EMPLOI (API officielle France Travail) : par département +
 *      mot-clé, nombre d'offres actives = signal du vivier où diffuser, +
 *      employeurs qui recrutent (bonus : ce sont aussi des prospects).
 *   3. CIBLER DES PROFILS PRO (Google Places) : repère des indépendants /
 *      petits pros susceptibles de devenir ambassadeurs, message de
 *      recrutement prêt + ajout au CRM (tri/suppression de masse hérités).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------------------
 * Helpers communs.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_rr_link' ) ) {
	/** Lien de candidature ambassadeur (funnel géré : email auto + alerte SMS +
	    suivi). On envoie vers /candidature-ambassadeur si la page existe, sinon
	    vers /ambassadeurs. Outil admin : aucun code parrain. */
	function ag_rr_link() {
		$pg = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'candidature-ambassadeur' ) : null;
		if ( $pg && 'publish' === $pg->post_status ) return get_permalink( $pg );
		if ( function_exists( 'ag_cand_program_url' ) ) return ag_cand_program_url();
		return function_exists( 'home_url' ) ? home_url( '/ambassadeurs' ) : '/ambassadeurs';
	}
}
if ( ! function_exists( 'ag_rr_message' ) ) {
	/** Messages de recrutement (pour candidats / pros). {link} remplacé. */
	function ag_rr_message( $link, $variation = 0 ) {
		$msgs = array(
			"Tu cherches un job ou un complément de revenu ? 💸\nAlliance Groupe (agence web & sécurité) recrute des ambassadeurs : tu touches *10 % de commission À VIE* sur chaque site vendu, sans plafond, payé sur PayPal, 100 % en ligne.\n📊 1 site Pro (890 €) = 89 € pour toi. 1 vente/semaine ≈ 4 600 €/an.\nGratuit, sans avance. Inscris-toi : {link}",
			"Complément de revenu sérieux, depuis chez toi 🏠\nDeviens ambassadeur Alliance Groupe : 10 % à vie sur chaque vente de site web. Tu présentes, on s'occupe du reste.\n1 vente Pro = 89 €. Bien lancé = plusieurs centaines d'€/mois. Aucun frais.\nC'est par ici : {link}",
			"Tu veux gagner de l'argent en ligne, à ton rythme ? 🚀\nAlliance Groupe cherche des ambassadeurs : 10 % de commission à vie sur les sites web qu'on vend grâce à toi. Formation simple, outils fournis, paiement PayPal.\nSans plafond, sans avance. Rejoins : {link}",
		);
		$m = $msgs[ $variation % count( $msgs ) ];
		return str_replace( '{link}', (string) $link, $m );
	}
}

/* ---------------------------------------------------------------------------
 * Marquage « posté » (par utilisateur + endroit).
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_rr_posts_get' ) ) {
	function ag_rr_posts_get() { $d = get_option( 'ag_rr_posted', array() ); return is_array( $d ) ? $d : array(); }
}
if ( ! function_exists( 'ag_rr_post_key' ) ) {
	function ag_rr_post_key( $email, $slug ) { return strtolower( (string) $email ) . '|' . $slug; }
}
if ( ! function_exists( 'ag_rr_job_ad' ) ) {
	/** Annonce d'emploi FORMATÉE, prête à déposer (France Travail/Indeed/HelloWork). */
	function ag_rr_job_ad( $link, $ville = '', $variant = 0 ) {
		$lieu = $ville ? $ville . ' / Télétravail' : 'Télétravail (France entière)';
		$ads  = array(
			array(
				'titre' => 'Conseiller(ère) commercial(e) indépendant(e) — Web & Sécurité (H/F)',
				'corps' => "Alliance Groupe, studio web & cybersécurité, recherche des ambassadeurs / apporteurs d'affaires indépendants pour présenter ses services de création de sites internet aux professionnels.\n\n"
					. "🧭 VOTRE MISSION\n• Faire connaître nos offres (sites web, sécurité) autour de vous et en ligne.\n• Mettre en relation des pros intéressés ; nous nous occupons de la vente et de la réalisation.\n\n"
					. "💸 RÉMUNÉRATION\n• Commission de 10 % À VIE sur chaque vente réalisée grâce à vous, sans plafond.\n• Exemple : 1 site Pro (890 €) = 89 € ; régularité = plusieurs centaines d'€/mois.\n• Paiement sur PayPal. Aucune avance, aucun frais.\n\n"
					. "👤 PROFIL\n• Motivé(e), à l'aise dans le contact (réseaux, entourage, terrain).\n• Aucun diplôme requis. Débutant accepté. Formation et outils fournis.\n• Statut indépendant (auto-entrepreneur recommandé) — 100 % en ligne, à votre rythme.\n\n"
					. "📍 LIEU : " . $lieu . "\n📝 CONTRAT : indépendant / temps libre.\n\n"
					. "➡️ POUR CANDIDATER : " . $link,
			),
			array(
				'titre' => 'Apporteur d\'affaires / Ambassadeur web (H/F) — complément de revenu',
				'corps' => "Vous cherchez un complément de revenu flexible ? Devenez ambassadeur Alliance Groupe (agence web).\n\n"
					. "Votre rôle : parler de nos sites internet autour de vous. Dès qu'une de vos mises en relation devient client, vous touchez 10 % à vie sur les ventes — sans plafond, payé sur PayPal.\n\n"
					. "✔ Gratuit, sans avance, 100 % en ligne, à votre rythme.\n✔ Débutants bienvenus, formation et supports fournis.\n✔ Idéal étudiants, personnes en recherche, indépendants.\n\n"
					. "Lieu : " . $lieu . ". Statut indépendant.\n\n"
					. "Candidature : " . $link,
			),
		);
		return $ads[ $variant % count( $ads ) ];
	}
}
add_action( 'admin_post_ag_rr_toggle', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_rr' ) ) wp_die( 'no' );
	$slug  = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$email = strtolower( wp_get_current_user()->user_email );
	if ( '' === $slug ) wp_die( 'bad' );
	$posts = ag_rr_posts_get();
	$key   = ag_rr_post_key( $email, $slug );
	if ( isset( $posts[ $key ] ) ) unset( $posts[ $key ] );
	else $posts[ $key ] = array( 'ts' => time(), 'date' => current_time( 'd/m/Y' ) );
	update_option( 'ag_rr_posted', $posts, false );
	$back = isset( $_POST['_back'] ) ? esc_url_raw( wp_unslash( $_POST['_back'] ) ) : admin_url( 'admin.php?page=ag-recrut-robot' );
	wp_safe_redirect( $back ); exit;
} );

/* ---------------------------------------------------------------------------
 * Endroits où poster l'annonce (curés, France).
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_rr_places' ) ) {
	function ag_rr_places( $ville = '' ) {
		$v = rawurlencode( trim( $ville ) );
		return array(
			array( 'slug' => 'francetravail', 'name' => 'France Travail — déposer une offre', 'why' => 'Touche tous les demandeurs d\'emploi. Publie l\'opportunité comme une offre « commercial / indépendant ».', 'url' => 'https://entreprise.francetravail.fr/' ),
			array( 'slug' => 'indeed', 'name' => 'Indeed — publier une offre (gratuit)', 'why' => 'Le plus gros trafic candidats. Offre gratuite possible.', 'url' => 'https://employers.indeed.com/' ),
			array( 'slug' => 'hellowork', 'name' => 'HelloWork / RegionsJob', 'why' => 'Forte audience France, emploi & alternance.', 'url' => 'https://www.hellowork.com/fr-fr/employeur.html' ),
			array( 'slug' => 'leboncoin', 'name' => 'Leboncoin — Emploi', 'why' => 'Très consulté en local pour les jobs et compléments.', 'url' => 'https://www.leboncoin.fr/' ),
			array( 'slug' => 'fb', 'name' => 'Groupes Facebook « Emploi ' . ( $ville ?: 'ville' ) . ' »', 'why' => 'Énormément de demandeurs y postent. Publie ton annonce + lien.', 'url' => 'https://www.facebook.com/search/groups/?q=' . ( $v ?: 'emploi' ) ),
			array( 'slug' => 'telegram', 'name' => 'Canaux Telegram emploi / petits jobs', 'why' => 'Audience jeune, réactive, complément de revenu.', 'url' => 'https://www.google.com/search?q=' . rawurlencode( 'telegram groupe emploi ' . $ville ) ),
			array( 'slug' => 'missionlocale', 'name' => 'Missions Locales (16-25 ans)', 'why' => 'Jeunes en recherche, parfaits pour un 1er revenu en ligne.', 'url' => 'https://www.google.com/search?q=' . rawurlencode( 'mission locale ' . $ville . ' contact' ) ),
			array( 'slug' => 'ecoles', 'name' => 'Écoles / BTS / CFA / universités', 'why' => 'Job étudiant + alternance. Contacte le BDE ou le service stages.', 'url' => 'https://www.google.com/search?q=' . rawurlencode( 'école BTS université ' . $ville . ' BDE jobs étudiants' ) ),
			array( 'slug' => 'studentjob', 'name' => 'StudentJob / Jobaviz (étudiants)', 'why' => 'Jobboards dédiés aux étudiants cherchant un revenu.', 'url' => 'https://www.studentjob.fr/' ),
			array( 'slug' => 'forums', 'name' => 'Forums / Reddit r/france (emploi)', 'why' => 'Discussions complément de revenu. Réponds avec ton lien (sans spam).', 'url' => 'https://www.reddit.com/search/?q=' . rawurlencode( 'complément revenu ' . $ville ) ),
		);
	}
}

/* ---------------------------------------------------------------------------
 * API France Travail (OAuth client_credentials + recherche d'offres).
 * Clé gratuite : https://francetravail.io (app + scope api_offresdemploiv2).
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_ft_token' ) ) {
	function ag_ft_token() {
		$id     = trim( (string) get_option( 'ag_ft_client_id', '' ) );
		$secret = trim( (string) get_option( 'ag_ft_client_secret', '' ) );
		if ( '' === $id || '' === $secret ) { update_option( 'ag_ft_last_error', 'Identifiants manquants (Client ID / Secret).' ); return ''; }
		$cached = get_transient( 'ag_ft_token' );
		if ( $cached ) return $cached;
		$resp = wp_remote_post( 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=%2Fpartenaire', array(
			'timeout' => 20,
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $id,
				'client_secret' => $secret,
				'scope'         => 'api_offresdemploiv2 o2dsoffre',
			),
		) );
		if ( is_wp_error( $resp ) ) { update_option( 'ag_ft_last_error', 'Réseau : ' . $resp->get_error_message() ); return ''; }
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		$tok  = $data['access_token'] ?? '';
		if ( $tok ) {
			$exp = (int) ( $data['expires_in'] ?? 1400 );
			set_transient( 'ag_ft_token', $tok, max( 60, $exp - 60 ) );
			delete_option( 'ag_ft_last_error' );
			return $tok;
		}
		$err = $data['error_description'] ?? ( $data['error'] ?? ( 'HTTP ' . $code ) );
		update_option( 'ag_ft_last_error', 'OAuth ' . $code . ' : ' . $err . ' (vérifie Client ID/Secret et que l’API « Offres d’emploi v2 » est bien souscrite).' );
		return '';
	}
}
if ( ! function_exists( 'ag_ft_token_for' ) ) {
	/** Jeton OAuth France Travail pour un SCOPE donné (autre API souscrite sur la
	    même application). Cache séparé pour ne pas interférer avec les offres. */
	function ag_ft_token_for( $scope, $cache_key ) {
		$id     = trim( (string) get_option( 'ag_ft_client_id', '' ) );
		$secret = trim( (string) get_option( 'ag_ft_client_secret', '' ) );
		if ( '' === $id || '' === $secret || '' === trim( $scope ) ) return array( '', 'Identifiants ou scope manquant.' );
		$cached = get_transient( $cache_key );
		if ( $cached ) return array( $cached, '' );
		$resp = wp_remote_post( 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=%2Fpartenaire', array(
			'timeout' => 20,
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array( 'grant_type' => 'client_credentials', 'client_id' => $id, 'client_secret' => $secret, 'scope' => trim( $scope ) ),
		) );
		if ( is_wp_error( $resp ) ) return array( '', 'Réseau : ' . $resp->get_error_message() );
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		$tok  = $data['access_token'] ?? '';
		if ( $tok ) { set_transient( $cache_key, $tok, max( 60, (int) ( $data['expires_in'] ?? 1400 ) - 60 ) ); return array( $tok, '' ); }
		return array( '', 'OAuth ' . $code . ' : ' . ( $data['error_description'] ?? ( $data['error'] ?? 'erreur' ) ) );
	}
}
/* ── Testeur générique d'API France Travail (pour brancher events / JCMO sans deviner) ── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'API France Travail (test)', '🧪 API France Travail', 'manage_options', 'ag-ft-test', 'ag_ft_test_render' );
}, 26 );
if ( ! function_exists( 'ag_ft_test_render' ) ) {
	function ag_ft_test_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		echo '<div class="wrap"><h1>🧪 Testeur API France Travail</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Réutilise ton application France Travail (Client ID/Secret déjà saisis dans le Robot). Le scope n’est pas toujours affiché dans la doc → utilise d’abord <strong>« 🔍 Détecter le scope »</strong> : ça essaie les noms probables et te dit lesquels sont autorisés sur ton appli.</p>';

		// Détection automatique du scope (essaie les candidats connus).
		if ( isset( $_POST['ft_discover'] ) ) {
			$cands = array(
				'api_evenementsv1', 'api_evenementsemploiv1', 'api_mesevenementsemploiv1', 'api_mes-evenements-emploiv1', 'api_mes-evenements-emploi-v1', 'api_evenements-france-travailv1',
				'api_jcmov1', 'api_jcmo-v1', 'api_je-controle-mon-offrev1', 'api_controle-offrev1', 'api_jcmo-controle-offrev1',
			);
			echo '<h3>Résultat de la détection :</h3><table class="widefat striped" style="max-width:680px"><thead><tr><th>Scope testé</th><th>Autorisé ?</th></tr></thead><tbody>';
			foreach ( $cands as $cand ) {
				delete_transient( 'ag_ft_token_test_' . md5( $cand ) );
				list( $tk, $er ) = ag_ft_token_for( $cand, 'ag_ft_token_test_' . md5( $cand ) );
				$ok = ( '' !== $tk );
				echo '<tr><td><code>' . esc_html( $cand ) . '</code></td><td>' . ( $ok ? '<strong style="color:#1e7e34">✅ OUI — utilise ce scope</strong>' : '<span style="color:#b32d2e">non</span>' ) . '</td></tr>';
				usleep( 150000 );
			}
			echo '</tbody></table><p class="description">Copie le scope ✅ dans le champ ci-dessous, puis teste l’URL. (Si aucun n’est ✅, envoie-moi une capture de la fiche 👁 de l’API.)</p>';
		}
		echo '<form method="post" style="margin:6px 0 14px;"><button name="ft_discover" value="1" class="button button-secondary">🔍 Détecter le scope (Événements / JCMO)</button></form>';

		// Détection automatique de l'ENDPOINT (avec le scope saisi).
		if ( isset( $_POST['ft_probe'] ) ) {
			$sc = sanitize_text_field( wp_unslash( $_POST['ft_scope'] ?? 'api_evenementsv1' ) );
			list( $tk, $er ) = ag_ft_token_for( $sc, 'ag_ft_token_test_' . md5( $sc ) );
			if ( '' === $tk ) {
				echo '<p style="color:#b32d2e;">❌ Jeton ko pour ce scope : ' . esc_html( $er ) . '</p>';
			} else {
				$paths = array(
					'https://api.francetravail.io/partenaire/evenements/v1/evenements',
					'https://api.francetravail.io/partenaire/evenements/v1/evenements/recherche',
					'https://api.francetravail.io/partenaire/evenements/v1/recherche',
					'https://api.francetravail.io/partenaire/mes-evenements-emploi/v1/evenements',
					'https://api.francetravail.io/partenaire/mes-evenements-emploi/v1/evenements/recherche',
					'https://api.francetravail.io/partenaire/mesevenementsemploi/v1/evenements',
					'https://api.francetravail.io/partenaire/evenementsemploi/v1/evenements',
					'https://api.francetravail.io/partenaire/evenements/v1/evenements?codePostal=44000',
				);
				echo '<h3>Détection de l’endpoint (scope <code>' . esc_html( $sc ) . '</code>) :</h3><table class="widefat striped" style="max-width:920px"><thead><tr><th>URL</th><th>HTTP</th><th>Début de réponse</th></tr></thead><tbody>';
				foreach ( $paths as $pu ) {
					$r  = wp_remote_get( $pu, array( 'timeout' => 18, 'headers' => array( 'Authorization' => 'Bearer ' . $tk, 'Accept' => 'application/json' ) ) );
					$rc = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
					$bd = is_wp_error( $r ) ? $r->get_error_message() : mb_substr( (string) wp_remote_retrieve_body( $r ), 0, 120 );
					$mark = in_array( $rc, array( 200, 206 ), true ) ? '✅ ' : ( 400 === $rc ? '🟠 ' : '' );
					echo '<tr><td style="font-size:11px"><code>' . esc_html( $pu ) . '</code></td><td>' . $mark . (int) $rc . '</td><td style="font-size:11px">' . esc_html( $bd ) . '</td></tr>';
					usleep( 150000 );
				}
				echo '</tbody></table><p class="description">✅ 200/206 = bon endpoint. 🟠 400 = bon endpoint mais paramètres manquants (on ajustera). 403/404 partout = il me faut une capture de la fiche 👁.</p>';
			}
		}
		echo '<form method="post" style="margin:0 0 14px;"><input type="hidden" name="ft_scope" value="' . esc_attr( isset( $_POST['ft_scope'] ) ? sanitize_text_field( wp_unslash( $_POST['ft_scope'] ) ) : 'api_evenementsv1' ) . '"><button name="ft_probe" value="1" class="button button-secondary">🔍 Détecter l’endpoint Événements (avec le scope ci-dessous)</button></form>';

		$scope  = isset( $_POST['ft_scope'] ) ? sanitize_text_field( wp_unslash( $_POST['ft_scope'] ) ) : 'api_evenementsv1';
		$method = isset( $_POST['ft_method'] ) && 'POST' === $_POST['ft_method'] ? 'POST' : 'GET';
		$url    = isset( $_POST['ft_url'] ) ? esc_url_raw( wp_unslash( $_POST['ft_url'] ) ) : 'https://api.francetravail.io/partenaire/evenements/v1/evenements';
		$body   = isset( $_POST['ft_body'] ) ? trim( wp_unslash( $_POST['ft_body'] ) ) : '';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo '<tr><th>Scope</th><td><input type="text" name="ft_scope" value="' . esc_attr( $scope ) . '" style="width:480px"><p class="description">Celui qui est ✅ dans la détection ci-dessus.</p></td></tr>';
		echo '<tr><th>Méthode</th><td><select name="ft_method"><option ' . selected( $method, 'GET', false ) . '>GET</option><option ' . selected( $method, 'POST', false ) . '>POST</option></select></td></tr>';
		echo '<tr><th>URL</th><td><input type="text" name="ft_url" value="' . esc_attr( $url ) . '" style="width:680px"><p class="description">URL d’accès complète (avec paramètres pour un GET, ex. <code>?codePostal=44000</code>).</p></td></tr>';
		echo '<tr><th>Corps (POST/JSON)</th><td><textarea name="ft_body" rows="5" style="width:680px" placeholder=\'{"texte":"..."}\'>' . esc_textarea( $body ) . '</textarea></td></tr>';
		echo '</tbody></table><button name="ft_go" value="1" class="button button-primary">Tester l’appel</button></form>';
		if ( isset( $_POST['ft_go'] ) ) {
			list( $tok, $terr ) = ag_ft_token_for( $scope, 'ag_ft_token_test_' . md5( $scope ) );
			if ( '' === $tok ) {
				echo '<p style="color:#b32d2e;">❌ Jeton : ' . esc_html( $terr ) . '</p>';
			} else {
				$args = array( 'method' => $method, 'timeout' => 25, 'headers' => array( 'Authorization' => 'Bearer ' . $tok, 'Accept' => 'application/json' ) );
				if ( 'POST' === $method && '' !== $body ) { $args['headers']['Content-Type'] = 'application/json'; $args['body'] = $body; }
				$r = wp_remote_request( $url, $args );
				if ( is_wp_error( $r ) ) {
					echo '<p style="color:#b32d2e;">❌ ' . esc_html( $r->get_error_message() ) . '</p>';
				} else {
					$rc  = (int) wp_remote_retrieve_response_code( $r );
					$rb  = wp_remote_retrieve_body( $r );
					$cr  = wp_remote_retrieve_header( $r, 'content-range' );
					echo '<h3>Réponse — HTTP ' . $rc . ( $cr ? ' · Content-Range: ' . esc_html( $cr ) : '' ) . '</h3>';
					$pretty = json_decode( $rb, true );
					if ( null !== $pretty ) $rb = wp_json_encode( $pretty, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
					echo '<textarea rows="22" style="width:100%;font-family:monospace;font-size:12px" onclick="this.select()">' . esc_textarea( mb_substr( (string) $rb, 0, 12000 ) ) . '</textarea>';
					echo '<p class="description">🟢 HTTP 200/206 = ça marche → colle-moi cette réponse, je construis l’écran propre. 401/403 = scope/URL à corriger.</p>';
				}
			}
		}
		echo '</div>';
	}
}
if ( ! function_exists( 'ag_ft_offres' ) ) {
	/** Cherche les offres (signal de vivier). Retourne [total, offres[]] ou ['error']. */
	function ag_ft_offres( $departement, $mots = '' ) {
		$tok = ag_ft_token();
		if ( '' === $tok ) return array( 'error' => 'Clé API France Travail manquante (Réglages ci-dessous).' );
		$dep = preg_replace( '/[^0-9AB]/i', '', (string) $departement );
		$args = array_filter( array(
			'departement' => $dep ?: null,
			'motsCles'    => $mots ? sanitize_text_field( $mots ) : null,
			'range'       => '0-19',
		) );
		$url  = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search?' . http_build_query( $args );
		$resp = wp_remote_get( $url, array( 'timeout' => 25, 'headers' => array( 'Authorization' => 'Bearer ' . $tok, 'Accept' => 'application/json' ) ) );
		if ( is_wp_error( $resp ) ) return array( 'error' => $resp->get_error_message() );
		$code = (int) wp_remote_retrieve_response_code( $resp );
		if ( 200 !== $code && 206 !== $code ) {
			$b = json_decode( wp_remote_retrieve_body( $resp ), true );
			return array( 'error' => 'France Travail ' . $code . ' : ' . ( $b['message'] ?? 'erreur' ) );
		}
		$total = 0;
		$cr = wp_remote_retrieve_header( $resp, 'content-range' ); // ex. "offres 0-19/1234"
		if ( $cr && preg_match( '#/(\d+)\s*$#', $cr, $m ) ) $total = (int) $m[1];
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		$offres = array();
		foreach ( (array) ( $data['resultats'] ?? array() ) as $o ) {
			$offres[] = array(
				'intitule'   => $o['intitule'] ?? '',
				'entreprise' => $o['entreprise']['nom'] ?? '',
				'lieu'       => $o['lieuTravail']['libelle'] ?? '',
				'date'       => isset( $o['dateCreation'] ) ? substr( $o['dateCreation'], 0, 10 ) : '',
				'url'        => $o['origineOffre']['urlOrigine'] ?? '',
			);
		}
		if ( ! $total && $offres ) $total = count( $offres );
		return array( 'total' => $total, 'offres' => $offres );
	}
}

/* ---------------------------------------------------------------------------
 * Réglages clé France Travail.
 * ------------------------------------------------------------------------- */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_rr_save_ft'] ) && check_admin_referer( 'ag_rr_ft' ) ) {
		update_option( 'ag_ft_client_id', sanitize_text_field( wp_unslash( $_POST['ag_ft_client_id'] ?? '' ) ) );
		update_option( 'ag_ft_client_secret', sanitize_text_field( wp_unslash( $_POST['ag_ft_client_secret'] ?? '' ) ) );
		delete_transient( 'ag_ft_token' );
	}
} );

/* ---------------------------------------------------------------------------
 * Menu + page.
 * ------------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-ambassadeurs', 'Robot Ambassadeurs', '🤖 Robot Ambassadeurs', 'manage_options', 'ag-recrut-robot', 'ag_rr_render' );
}, 20 );

if ( ! function_exists( 'ag_rr_render' ) ) {
	function ag_rr_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$email = strtolower( wp_get_current_user()->user_email );
		$ville = isset( $_GET['rv'] ) ? sanitize_text_field( wp_unslash( $_GET['rv'] ) ) : '';
		$link  = ag_rr_link();
		$back  = esc_url_raw( add_query_arg( array_filter( array( 'page' => 'ag-recrut-robot', 'rv' => $ville ?: null ) ), admin_url( 'admin.php' ) ) );
		$posts = ag_rr_posts_get();
		$nonce = wp_create_nonce( 'ag_rr' );
		echo '<div class="wrap"><h1>🤖 Robot Recrutement Ambassadeurs</h1>';
		echo '<p style="max-width:880px;color:#50575e;">Trouve et recrute des ambassadeurs (gens qui cherchent un job / un complément de revenu) <strong>légalement</strong> : on diffuse l\'opportunité là où ils sont, on cible des profils pros, et on repère les bassins d\'emploi via l\'API officielle France Travail. <em>Aucune donnée personnelle de demandeur d\'emploi n\'est aspirée (interdit RGPD/CGU).</em></p>';

		// Sélecteur de ville (commun aux sections).
		echo '<form method="get" style="margin:6px 0 14px;"><input type="hidden" name="page" value="ag-recrut-robot"><label>Ville ciblée : <input type="text" name="rv" value="' . esc_attr( $ville ) . '" placeholder="ex : Nantes" style="width:200px;"></label> <button class="button">Cibler</button></form>';

		// ── 1) Mon annonce + diffusion ───────────────────────────────────
		echo '<div style="max-width:980px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;border-radius:8px;padding:16px 18px;margin:14px 0;">';
		echo '<h2 style="margin-top:0;">📣 1. Mon annonce + où la diffuser</h2>';
		echo '<p style="margin:0 0 6px;"><strong>Lien d\'inscription ambassadeur :</strong> <a href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( $link ) . '</a></p>';
		echo '<div style="display:flex;gap:18px;flex-wrap:wrap;align-items:flex-start;">';
		echo '<img src="' . esc_url( 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode( $link ) ) . '" alt="QR inscription ambassadeur" width="160" height="160" style="background:#fff;border:1px solid #ddd;border-radius:8px;flex:none;">';
		echo '<div style="flex:1;min-width:280px;">';
		for ( $i = 0; $i < 3; $i++ ) {
			echo '<details ' . ( 0 === $i ? 'open' : '' ) . ' style="margin-bottom:6px;"><summary class="button button-small">✍️ Message ' . ( $i + 1 ) . '</summary>';
			echo '<textarea readonly rows="7" style="width:100%;margin-top:6px;">' . esc_textarea( ag_rr_message( $link, $i ) ) . '</textarea></details>';
		}
		echo '</div></div>';
		// Endroits où poster.
		echo '<h3>📍 Où poster (coche quand c\'est fait)</h3><table class="widefat striped"><thead><tr><th>Endroit</th><th>Pourquoi</th><th>Action</th><th>Posté ?</th></tr></thead><tbody>';
		foreach ( ag_rr_places( $ville ) as $pl ) {
			$done = isset( $posts[ ag_rr_post_key( $email, $pl['slug'] ) ] );
			echo '<tr>';
			echo '<td><strong>' . esc_html( $pl['name'] ) . '</strong></td>';
			echo '<td style="font-size:.85em;color:#50575e;">' . esc_html( $pl['why'] ) . '</td>';
			echo '<td><a class="button button-small" href="' . esc_url( $pl['url'] ) . '" target="_blank" rel="noopener">Ouvrir</a></td>';
			echo '<td><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0;"><input type="hidden" name="action" value="ag_rr_toggle"><input type="hidden" name="_n" value="' . esc_attr( $nonce ) . '"><input type="hidden" name="slug" value="' . esc_attr( $pl['slug'] ) . '"><input type="hidden" name="_back" value="' . esc_attr( $back ) . '">';
			echo $done ? '<button class="button button-small" style="color:#1e7e34;">✓ Posté le ' . esc_html( $posts[ ag_rr_post_key( $email, $pl['slug'] ) ]['date'] ) . '</button>' : '<button class="button button-small button-primary">Marquer posté</button>';
			echo '</form></td></tr>';
		}
		echo '</tbody></table>';

		// ── Annonce d'emploi prête à déposer ─────────────────────────────
		echo '<h3 style="margin-top:18px;">📋 Annonce d\'emploi prête à déposer (Pôle emploi / Indeed / HelloWork)</h3>';
		echo '<p style="font-size:.86em;color:#50575e;max-width:760px;">Copie-colle cette annonce dans l\'espace recruteur. <strong>Important</strong> : sur France Travail, comme c\'est un poste <strong>indépendant à la commission</strong> (pas un salaire), choisis le type « <strong>Profession commerciale / agent commercial indépendant</strong> » ou indique clairement « statut indépendant ». Indeed, HelloWork et Leboncoin acceptent ce format sans souci.</p>';
		for ( $v = 0; $v < 2; $v++ ) {
			$ad = ag_rr_job_ad( $link, $ville, $v );
			echo '<details ' . ( 0 === $v ? 'open' : '' ) . ' style="margin:8px 0;">';
			echo '<summary class="button button-small">📋 Annonce ' . ( $v + 1 ) . ' — ' . esc_html( $ad['titre'] ) . '</summary>';
			echo '<p style="margin:6px 0 2px;font-weight:600;">Titre de l\'offre :</p><input type="text" readonly value="' . esc_attr( $ad['titre'] ) . '" style="width:100%;" onclick="this.select()">';
			echo '<p style="margin:8px 0 2px;font-weight:600;">Description :</p><textarea readonly rows="16" style="width:100%;" onclick="this.select()">' . esc_textarea( $ad['corps'] ) . '</textarea>';
			echo '</details>';
		}
		echo '</div>';

		// ── 2) Bassins d'emploi (API France Travail) ─────────────────────
		$dep  = isset( $_GET['rdep'] ) ? sanitize_text_field( wp_unslash( $_GET['rdep'] ) ) : '44';
		$mots = isset( $_GET['rmots'] ) ? sanitize_text_field( wp_unslash( $_GET['rmots'] ) ) : '';
		echo '<div style="max-width:980px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #0e7490;border-radius:8px;padding:16px 18px;margin:14px 0;">';
		echo '<h2 style="margin-top:0;">📊 2. Bassins d\'emploi (API France Travail)</h2>';
		echo '<p style="color:#50575e;max-width:760px;">Beaucoup d\'offres actives dans un département = marché de l\'emploi qui bouge = <strong>beaucoup de gens en recherche</strong> → c\'est là qu\'il faut diffuser ton annonce. (Données publiques d\'offres, pas de candidats.)</p>';
		if ( '' === ag_ft_token() ) {
			echo '<form method="post">';
			wp_nonce_field( 'ag_rr_ft' );
			echo '<p><strong>Active l\'API (gratuit)</strong> : crée une appli sur <a href="https://francetravail.io" target="_blank" rel="noopener">francetravail.io</a>, souscris à <strong>« Offres d\'emploi v2 »</strong>, puis colle ici :</p>';
			echo '<p><label>Client ID (Identifiant client)<br><input type="text" name="ag_ft_client_id" value="' . esc_attr( get_option( 'ag_ft_client_id', '' ) ) . '" style="width:480px;"></label></p>';
			echo '<p><label>Client Secret (Clé secrète)<br><input type="password" name="ag_ft_client_secret" value="' . esc_attr( get_option( 'ag_ft_client_secret', '' ) ) . '" style="width:480px;"></label></p>';
			echo '<button name="ag_rr_save_ft" value="1" class="button button-primary">Enregistrer &amp; tester</button></form>';
			$ft_err = get_option( 'ag_ft_last_error', '' );
			if ( $ft_err && ( get_option( 'ag_ft_client_id', '' ) || get_option( 'ag_ft_client_secret', '' ) ) ) {
				echo '<p style="color:#b32d2e;margin-top:8px;">❌ Connexion impossible — ' . esc_html( $ft_err ) . '</p>';
			}
			echo '<p style="font-size:.85em;color:#50575e;max-width:760px;margin-top:6px;">💡 Sur francetravail.io : « Mon espace » → crée une application → onglet <strong>« Souscrire à une API »</strong> → choisis <strong>« Offres d\'emploi v2 »</strong>. Récupère ensuite l\'<strong>Identifiant client</strong> et la <strong>Clé secrète</strong> dans les détails de l\'application.</p>';
		} else {
			echo '<p style="color:#1e7e34;margin:0 0 8px;">🟢 API France Travail connectée.</p>';
			echo '<form method="get" style="margin-bottom:10px;"><input type="hidden" name="page" value="ag-recrut-robot"><input type="hidden" name="rv" value="' . esc_attr( $ville ) . '">';
			echo '<label>Département <input type="text" name="rdep" value="' . esc_attr( $dep ) . '" style="width:60px;" placeholder="44"></label> ';
			echo '<label>Mot-clé (optionnel) <input type="text" name="rmots" value="' . esc_attr( $mots ) . '" style="width:200px;" placeholder="commercial, vente…"></label> ';
			echo '<button class="button button-primary">Analyser le bassin</button></form>';
			if ( isset( $_GET['rdep'] ) || isset( $_GET['rmots'] ) ) {
				$res = ag_ft_offres( $dep, $mots );
				if ( isset( $res['error'] ) ) {
					echo '<p style="color:#b32d2e;">' . esc_html( $res['error'] ) . '</p>';
				} else {
					echo '<p style="font-size:1.1em;"><strong>' . number_format_i18n( $res['total'] ) . '</strong> offre(s) active(s) dans le département ' . esc_html( $dep ) . ( $mots ? ' pour « ' . esc_html( $mots ) . ' »' : '' ) . ' → ' . ( $res['total'] > 300 ? '🟢 gros vivier, diffuse ici en priorité.' : ( $res['total'] > 50 ? '🟠 vivier correct.' : '🔵 vivier plus faible.' ) ) . '</p>';
					if ( ! empty( $res['offres'] ) ) {
						echo '<p style="font-size:.85em;color:#50575e;">Employeurs qui recrutent = entreprises actives, <strong>aussi d\'excellents prospects web/securite</strong>. Ajoute-les au CRM en 1 clic :</p><table class="widefat striped"><thead><tr><th>Poste</th><th>Entreprise</th><th>Lieu</th><th>Date</th><th>CRM</th></tr></thead><tbody>';
						foreach ( $res['offres'] as $o ) {
							$ent = $o['entreprise'] ?? '';
							echo '<tr><td>' . ( $o['url'] ? '<a href="' . esc_url( $o['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $o['intitule'] ) . '</a>' : esc_html( $o['intitule'] ) ) . '</td><td>' . esc_html( $ent ?: '—' ) . '</td><td>' . esc_html( $o['lieu'] ) . '</td><td>' . esc_html( $o['date'] ) . '</td>';
							if ( $ent ) { echo '<td><button type="button" class="button button-small agrr-add" data-name="' . esc_attr( $ent ) . '" data-type="entreprise qui recrute" data-city="' . esc_attr( $o['lieu'] ?? '' ) . '" data-phone="" data-website="" data-source="francetravail" data-notes="' . esc_attr( 'Recrute : ' . $o['intitule'] . ' (repere via France Travail). Entreprise active = bon prospect web.' ) . '">+ Prospect</button></td>'; } else { echo '<td>—</td>'; }
							echo '</tr>';
						}
						echo '</tbody></table>';
					}
				}
			}
		}
		// Ajouter un candidat repéré (job board / France Travail) → 🎯 Candidatures.
		echo '<hr style="margin:14px 0;border:none;border-top:1px solid #eee;">';
		echo '<h3 style="margin:0 0 4px;">➕ Un candidat t\'a répondu ? Ajoute-le ici</h3>';
		echo '<p style="font-size:.85em;color:#50575e;max-width:760px;">Quelqu\'un t\'a contacté via une annonce (France Travail, Indeed…) ? Saisis-le : il atterrit dans <strong>Ambassadeurs → 🎯 Candidatures</strong>, reçoit la réponse automatique, et tu es notifié.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">';
		echo '<input type="hidden" name="action" value="ag_cand_manual_add"><input type="hidden" name="source" value="francetravail"><input type="hidden" name="_back" value="' . esc_attr( admin_url( 'admin.php?page=ag-recrut-robot' ) ) . '">';
		wp_nonce_field( 'ag_cand_manual', '_n' );
		echo '<label>Prénom<br><input type="text" name="prenom" required style="width:130px;"></label>';
		echo '<label>Email<br><input type="email" name="email" style="width:190px;"></label>';
		echo '<label>Téléphone<br><input type="text" name="phone" style="width:130px;"></label>';
		echo '<label>Ville<br><input type="text" name="ville" value="' . esc_attr( $ville ) . '" style="width:110px;"></label>';
		echo '<button class="button button-primary">→ Envoyer aux Candidatures</button>';
		echo '</form>';
		if ( isset( $_GET['candadd'] ) ) echo '<p style="color:#1e7e34;">✅ Candidat envoyé aux Candidatures (réponse auto faite).</p>';
		echo '</div>';

		// ── 3) Cibler des profils pro (Google Places) ────────────────────
		echo '<div style="max-width:980px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #7a4ed4;border-radius:8px;padding:16px 18px;margin:14px 0;">';
		echo '<h2 style="margin-top:0;">🎯 3. Cibler des profils pro (à transformer en ambassadeurs)</h2>';
		echo '<p style="color:#50575e;max-width:760px;">Repère des indépendants susceptibles de vouloir un revenu en plus (coachs, VDI, photographes, auto-entrepreneurs…). Coordonnées <strong>pros publiques</strong> via Google Places. Le message de recrutement est prêt.</p>';
		if ( ! function_exists( 'ag_places_search' ) || '' === ( function_exists( 'ag_places_key' ) ? ag_places_key() : '' ) ) {
			echo '<p style="color:#b26a00;">Ajoute ta clé Google Places dans <strong>Prospection</strong> pour activer cette recherche.</p>';
		} else {
			$metier = isset( $_GET['rmetier'] ) ? sanitize_text_field( wp_unslash( $_GET['rmetier'] ) ) : '';
			echo '<form method="get" style="margin-bottom:10px;"><input type="hidden" name="page" value="ag-recrut-robot"><input type="hidden" name="rv" value="' . esc_attr( $ville ) . '">';
			echo '<label>Type de profil <input type="text" name="rmetier" value="' . esc_attr( $metier ) . '" placeholder="coach, VDI, photographe…" style="width:220px;"></label> ';
			echo '<button class="button button-primary">Chercher</button></form>';
			if ( '' !== $metier && '' !== $ville ) {
				$pres = ag_places_search( $metier . ' ' . $ville );
				if ( is_array( $pres ) && isset( $pres['error'] ) ) {
					echo '<p style="color:#b32d2e;">' . esc_html( $pres['error'] ) . '</p>';
				} elseif ( empty( $pres ) ) {
					echo '<p style="color:#50575e;">Aucun résultat.</p>';
				} else {
					echo '<table class="widefat striped"><thead><tr><th>Profil</th><th>Téléphone</th><th>Recruter</th></tr></thead><tbody>';
					foreach ( $pres as $r ) {
						$nm  = $r['name'] ?? '';
						$ph  = $r['phone'] ?? '';
						$msg = ag_rr_message( $link, 0 );
						$digits = function_exists( 'ag_wa_number' ) ? ag_wa_number( $ph, $r['phone_intl'] ?? '' ) : '';
						$wa  = $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( $msg ) : '';
						$smsnum = preg_replace( '/[^0-9+]/', '', $r['phone_intl'] ?? '' ) ?: preg_replace( '/[^0-9+]/', '', $ph );
						$sms = $smsnum ? 'sms:' . $smsnum . '?body=' . rawurlencode( $msg ) : '';
						echo '<tr><td><strong>' . esc_html( $nm ) . '</strong><br><small>' . esc_html( $r['address'] ?? '' ) . '</small></td>';
						echo '<td>' . ( $ph ? '<a href="tel:' . esc_attr( $ph ) . '">' . esc_html( $ph ) . '</a>' : '—' ) . '</td>';
						echo '<td>';
						if ( $wa ) echo '<a class="button button-small" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">WhatsApp</a> ';
						if ( $sms ) echo '<a class="button button-small" href="' . esc_attr( $sms ) . '">SMS</a> ';
						echo '<button type="button" class="button button-small button-primary agrr-add" data-name="' . esc_attr( $nm ) . '" data-type="' . esc_attr( $metier ) . '" data-city="' . esc_attr( $ville ) . '" data-phone="' . esc_attr( $ph ) . '" data-website="' . esc_attr( $r['website'] ?? '' ) . '">+ CRM (futur ambassadeur)</button>';
						echo '</td></tr>';
					}
					echo '</tbody></table>';
					echo '<p style="font-size:.82em;color:#50575e;">Les profils ajoutés au CRM ont la source <code>recrut-ambassadeur</code> : tri, contact et suppression de masse se gèrent dans <strong>Prospection</strong>.</p>';
				}
			} elseif ( '' !== $metier ) {
				echo '<p style="color:#b26a00;">Renseigne aussi une ville en haut de page.</p>';
			}
		}
		echo '</div>';

		// JS : ajout au CRM (futur ambassadeur).
		$pn = wp_create_nonce( 'ag_prospect' );
		echo '<script>(function(){var n=' . wp_json_encode( $pn ) . ';document.querySelectorAll(".agrr-add").forEach(function(b){b.addEventListener("click",function(){var fd=new FormData();fd.append("action","ag_prospect_add");fd.append("_n",n);["name","type","city","phone","website"].forEach(function(k){fd.append(k,b.getAttribute("data-"+k)||"");});fd.append("source",b.getAttribute("data-source")||"recrut-ambassadeur");fd.append("notes",b.getAttribute("data-notes")||"Profil repéré pour recrutement ambassadeur.");b.disabled=true;b.textContent="…";fetch(ajaxurl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){b.textContent=(j&&j.success)?"✓ Ajouté":"Erreur";}).catch(function(){b.textContent="Erreur";b.disabled=false;});});});})();</script>';
		echo '</div>';
	}
}
