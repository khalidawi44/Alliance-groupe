<?php
/**
 * ag-autopilot.php — PILOTE AUTOMATIQUE de la chaîne commerciale.
 *
 * PROBLÈME RÉSOLU : toute la prospection tourne en WP-Cron, qui ne se déclenche
 * QUE lorsqu'un visiteur charge une page du site. Peu de trafic = les agents ne
 * partent presque jamais = « aucun client trouvé », sans que rien ne soit cassé.
 *
 * SOLUTION : un planificateur externe gratuit (cron-job.org, UptimeRobot) appelle
 * toutes les ~15 min une URL à jeton :  /wp-json/ag/v1/run?token=XXXX
 * → on déclenche enrichissement, chasse (1×/h), Hugo, relances, relève des réponses.
 * Tout passe par les MÊMES garde-fous (cap journalier, opt-out, chauffe, agent éteint) :
 * ce module ne fait que « réveiller » la chaîne, il ne contourne aucune sécurité.
 *
 * Expose aussi un DIAGNOSTIC qui dit en clair ce qui empêche la production de
 * clients (clé Places manquante, Hugo éteint, aucun prospect avec email…).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Jeton secret (auto-généré une fois) ─────────────────────────────── */
if ( ! function_exists( 'ag_auto_token' ) ) {
	function ag_auto_token() {
		$t = (string) get_option( 'ag_auto_token', '' );
		if ( '' === $t ) { $t = wp_generate_password( 32, false, false ); update_option( 'ag_auto_token', $t, false ); }
		return $t;
	}
}
if ( ! function_exists( 'ag_auto_url' ) ) {
	function ag_auto_url() { return add_query_arg( 'token', ag_auto_token(), rest_url( 'ag/v1/run' ) ); }
}

/* ── Diagnostic : pourquoi ça ne produit pas (encore) ? ──────────────── */
if ( ! function_exists( 'ag_auto_diag' ) ) {
	function ag_auto_diag() {
		$prospects = (array) get_option( 'ag_prospects', array() );
		$avec_mail = 0;
		foreach ( $prospects as $p ) { if ( ! empty( $p['email'] ) && is_email( $p['email'] ) ) { $avec_mail++; } }
		$jour        = (array) get_option( 'ag_closer_jour', array() );
		$envoyes_auj = ( ( $jour['d'] ?? '' ) === current_time( 'Y-m-d' ) ) ? (int) ( $jour['n'] ?? 0 ) : 0;
		$sigs        = (array) get_option( 'ag_signatures', array() );
		$places      = function_exists( 'ag_places_key' ) ? ag_places_key() : '';
		$hugo_on     = function_exists( 'ag_closer_on' ) ? ag_closer_on() : false;
		$from        = function_exists( 'ag_closer_expediteur' ) ? (array) ag_closer_expediteur() : array( '' );
		$smtp        = (bool) get_option( 'ag_smtp_host', '' ) || (bool) get_option( 'ag_smtp_user', '' )
			|| ( defined( 'AG_SMTP_HOST' ) && AG_SMTP_HOST ) || ( defined( 'AG_SMTP_USER' ) && AG_SMTP_USER );

		$bloc = array();
		if ( '' === $places ) { $bloc[] = 'Clé Google Places MANQUANTE → le chasseur ne peut trouver aucun prospect (Prospection → Réglages).'; }
		if ( ! $hugo_on )      { $bloc[] = 'Hugo (démarchage) est ÉTEINT → aucun email ne part (Prospection → Hugo : activer l\'agent).'; }
		if ( empty( $from[0] ) || ! is_email( $from[0] ) ) { $bloc[] = 'Expéditeur d\'Hugo non configuré (email d\'envoi dans l\'écran Hugo).'; }
		if ( 0 === $avec_mail ) { $bloc[] = 'Aucun prospect avec email → rien à démarcher pour l\'instant : laisse tourner la chasse + l\'enrichissement (ça se remplit tout seul), ou importe des cibles.'; }

		return array(
			'prospects_total'           => count( $prospects ),
			'prospects_avec_email'      => $avec_mail,
			'emails_envoyes_aujourdhui' => $envoyes_auj,
			'contrats_signes'           => count( $sigs ),
			'places_key'                => '' !== $places ? 'ok' : 'manquante',
			'hugo'                      => $hugo_on ? 'actif' : 'éteint',
			'smtp'                      => $smtp ? 'ok' : 'à vérifier',
			'dernier_run'               => (int) get_option( 'ag_auto_last', 0 ),
			'bloquants'                 => $bloc,
		);
	}
}

/* ── Le vrai travail (lourd) : appels externes, Hugo, chasse Places… ──── */
if ( ! function_exists( 'ag_auto_tick' ) ) {
	function ag_auto_tick() {
		// Détaché : on finit même si l'appelant a raccroché (loopback non bloquant).
		if ( function_exists( 'ignore_user_abort' ) ) { ignore_user_abort( true ); }
		if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 150 ); }
		$now = time(); $ran = array();
		// Légers : à chaque passage (chacun respecte ses propres garde-fous).
		foreach ( array( 'ag_enrich_cron', 'ag_closer_cron', 'ag_rc_cron', 'ag_boite_cron' ) as $hook ) {
			if ( has_action( $hook ) ) { do_action( $hook ); $ran[] = $hook; }
		}
		// Chasse Google Places (coûteuse en quota/argent) : 1×/heure max.
		if ( has_action( 'ag_prospect_cron' ) && $now - (int) get_option( 'ag_auto_hunt', 0 ) > 3500 ) {
			do_action( 'ag_prospect_cron' ); update_option( 'ag_auto_hunt', $now, false ); $ran[] = 'ag_prospect_cron';
		}
		// Relances quotidiennes : 1×/jour max.
		if ( has_action( 'ag_relance_cron' ) && $now - (int) get_option( 'ag_auto_relance', 0 ) > 80000 ) {
			do_action( 'ag_relance_cron' ); update_option( 'ag_auto_relance', $now, false ); $ran[] = 'ag_relance_cron';
		}
		update_option( 'ag_auto_last', $now, false );
		update_option( 'ag_auto_last_ran', $ran, false );
		return $ran;
	}
}

/* ── Endpoints : /run (répond VITE + lance en fond) et /worker (bosse) ── */
add_action( 'rest_api_init', function () {
	// Déclencheur appelé par le planificateur externe : répond en < 1 s.
	register_rest_route( 'ag/v1', '/run', array(
		'methods'             => array( 'GET', 'POST' ),
		'permission_callback' => '__return_true', // jeton vérifié dans le callback
		'callback'            => function ( $req ) {
			if ( '' === ag_auto_token() || ! hash_equals( ag_auto_token(), (string) $req->get_param( 'token' ) ) ) {
				return new WP_REST_Response( array( 'ok' => false, 'err' => 'token' ), 403 );
			}
			// On lance le vrai travail en ARRIÈRE-PLAN (appel non bloquant à /worker),
			// puis on répond immédiatement : plus de 504, le planificateur est content.
			wp_remote_get( add_query_arg( 'token', ag_auto_token(), rest_url( 'ag/v1/worker' ) ), array(
				'blocking'  => false,
				'timeout'   => 0.01,
				'sslverify' => false,
				'headers'   => array( 'Cache-Control' => 'no-cache' ),
			) );
			return new WP_REST_Response( array(
				'ok'     => true,
				'queued' => true,
				'note'   => 'Chaine lancee en arriere-plan. Diagnostic ci-dessous.',
				'diag'   => ag_auto_diag(),
			), 200 );
		},
	) );
	// Ouvrier : fait le travail lourd, détaché. Personne n'attend sa réponse.
	register_rest_route( 'ag/v1', '/worker', array(
		'methods'             => array( 'GET', 'POST' ),
		'permission_callback' => '__return_true',
		'callback'            => function ( $req ) {
			if ( '' === ag_auto_token() || ! hash_equals( ag_auto_token(), (string) $req->get_param( 'token' ) ) ) {
				return new WP_REST_Response( array( 'ok' => false, 'err' => 'token' ), 403 );
			}
			$ran = ag_auto_tick();
			return new WP_REST_Response( array( 'ok' => true, 'ran' => $ran ), 200 );
		},
	) );
} );

/* ── Écran admin : URL à coller + diagnostic + Gmail contrat ─────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Pilote automatique', '🤖 Pilote automatique', 'manage_options', 'ag-autopilot', 'ag_auto_render' );
}, 25 );

add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_auto_save'] ) && check_admin_referer( 'ag_auto' ) ) {
		update_option( 'ag_contrat_gmail', sanitize_email( wp_unslash( $_POST['ag_contrat_gmail'] ?? '' ) ), false );
	}
	if ( isset( $_POST['ag_auto_regen'] ) && check_admin_referer( 'ag_auto' ) ) {
		update_option( 'ag_auto_token', wp_generate_password( 32, false, false ), false );
	}
} );

if ( ! function_exists( 'ag_auto_render' ) ) {
	function ag_auto_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$url   = ag_auto_url();
		$d     = ag_auto_diag();
		$gmail = (string) get_option( 'ag_contrat_gmail', 'fabrice.doucet44@gmail.com' );
		echo '<div class="wrap"><h1>🤖 Pilote automatique commercial</h1>';
		echo '<p>Pour que l\'équipe tourne <strong>24/7 sans s\'arrêter</strong> (même sans visiteur), un planificateur gratuit doit appeler cette adresse toutes les <strong>10-15 min</strong> :</p>';
		echo '<p><input type="text" readonly value="' . esc_attr( $url ) . '" style="width:100%;max-width:860px;font-family:monospace" onclick="this.select()"></p>';
		echo '<p class="description">Gratuit : <a href="https://cron-job.org" target="_blank" rel="noopener">cron-job.org</a> (ou UptimeRobot). Crée un « cron job », colle cette URL, intervalle 15 min, enregistre. C\'est tout : la chasse, Hugo, les relances et la relève des réponses partent alors tout seuls, en continu.</p>';
		echo '<form method="post" style="margin:10px 0 18px">';
		wp_nonce_field( 'ag_auto' );
		echo '<button class="button" name="ag_auto_regen" value="1" onclick="return confirm(\'Régénérer le jeton ? L\\\'ancienne URL cessera de marcher.\');">Régénérer le jeton</button></form>';

		echo '<h2>État — pourquoi ça ne produit pas encore</h2>';
		if ( ! empty( $d['bloquants'] ) ) {
			echo '<div style="background:#fff8e5;border-left:4px solid #dba617;padding:10px 14px;max-width:860px"><strong>À débloquer :</strong><ul style="margin:6px 0 0">';
			foreach ( $d['bloquants'] as $b ) { echo '<li>' . esc_html( $b ) . '</li>'; }
			echo '</ul></div>';
		} else {
			echo '<p style="color:#1e7e34">✅ Aucun blocage détecté — laisse tourner : les résultats arrivent avec le volume et le temps (chauffe du domaine, cap journalier).</p>';
		}
		$rows = array(
			'Prospects (total)'           => (string) $d['prospects_total'],
			'Prospects avec email'        => (string) $d['prospects_avec_email'],
			'Emails envoyés aujourd\'hui' => (string) $d['emails_envoyes_aujourdhui'],
			'Contrats signés'             => (string) $d['contrats_signes'],
			'Clé Google Places'           => $d['places_key'],
			'Hugo (démarchage)'           => $d['hugo'],
			'SMTP'                        => $d['smtp'],
			'Dernier passage auto'        => $d['dernier_run'] ? human_time_diff( $d['dernier_run'], time() ) . ' avant maintenant' : 'jamais',
		);
		echo '<table class="widefat striped" style="max-width:560px;margin-top:10px"><tbody>';
		foreach ( $rows as $k => $v ) { echo '<tr><th style="text-align:left">' . esc_html( $k ) . '</th><td>' . esc_html( $v ) . '</td></tr>'; }
		echo '</tbody></table>';

		echo '<h2 style="margin-top:24px">📄 Recevoir les contrats dans ton Gmail</h2>';
		echo '<form method="post">';
		wp_nonce_field( 'ag_auto' );
		echo '<p>Chaque contrat signé est envoyé <strong>en copie</strong> à cette adresse (en plus de la boîte maison) :</p>';
		echo '<p><input type="email" name="ag_contrat_gmail" value="' . esc_attr( $gmail ) . '" style="width:360px" placeholder="ton-adresse@gmail.com"> <button class="button button-primary" name="ag_auto_save" value="1">Enregistrer</button></p>';
		echo '<p class="description">Laisse vide pour désactiver la copie Gmail.</p>';
		echo '</form></div>';
	}
}
