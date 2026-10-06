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
		$avec_mail = 0; $eligibles = 0;
		$peut_tester = function_exists( 'ag_closer_eligible' );
		foreach ( $prospects as $p ) {
			if ( ! empty( $p['email'] ) && is_email( $p['email'] ) ) { $avec_mail++; }
			if ( $peut_tester && ag_closer_eligible( $p ) ) { $eligibles++; }
		}
		$jour = (array) get_option( 'ag_closer_jour', array() );
		// IMPORTANT : Hugo stocke le jour au format gmdate('Ymd') (ex. 20261006).
		// On DOIT comparer au même format, sinon le compteur affiche toujours 0.
		$envoyes_auj = ( ( $jour['d'] ?? '' ) === gmdate( 'Ymd' ) ) ? (int) ( $jour['n'] ?? 0 ) : 0;
		$cap         = function_exists( 'ag_closer_cap' ) ? ag_closer_cap() : 0;
		$sigs        = (array) get_option( 'ag_signatures', array() );
		$places      = function_exists( 'ag_places_key' ) ? ag_places_key() : '';
		$hugo_on     = function_exists( 'ag_closer_on' ) ? ag_closer_on() : false;
		$from        = function_exists( 'ag_closer_expediteur' ) ? (array) ag_closer_expediteur() : array( '' );
		$smtp        = (bool) get_option( 'ag_smtp_host', '' ) || (bool) get_option( 'ag_smtp_user', '' )
			|| ( defined( 'AG_SMTP_HOST' ) && AG_SMTP_HOST ) || ( defined( 'AG_SMTP_USER' ) && AG_SMTP_USER );

		$bloc = array();  // VRAIS blocages : tant qu'ils sont là, zéro email possible.
		$warn = array();  // Avertissements : l'envoi marche, mais quelque chose est à améliorer.
		if ( '' === $places ) { $bloc[] = 'Clé Google Places MANQUANTE → le chasseur ne peut trouver aucun NOUVEAU prospect (Prospection → Réglages). (N\'empêche pas d\'écrire aux prospects déjà présents.)'; }
		if ( ! $hugo_on )      { $bloc[] = 'Hugo (démarchage) est ÉTEINT → aucun email ne part (Prospection → Hugo : activer l\'agent).'; }
		if ( 0 === $avec_mail ) { $bloc[] = 'Aucun prospect avec email → rien à démarcher pour l\'instant : laisse tourner la chasse + l\'enrichissement (ça se remplit tout seul), ou importe des cibles.'; }
		// IMPORTANT : l'expéditeur d'Hugo n'est PAS un blocage. Sans lui, les emails
		// partent quand même (le From vient du SMTP) ; seul le Reply-To manque.
		if ( empty( $from[0] ) || ! is_email( $from[0] ) ) {
			$warn[] = 'Expéditeur d\'Hugo non renseigné : les emails PARTENT quand même (l\'adresse d\'envoi vient du SMTP). Mais les RÉPONSES des prospects iront sur l\'adresse SMTP par défaut. Pour choisir où arrivent les réponses, mets l\'email d\'envoi dans l\'écran Hugo (Prospection → Hugo).';
		}

		return array(
			'prospects_total'           => count( $prospects ),
			'prospects_avec_email'      => $avec_mail,
			'prospects_eligibles'       => $eligibles,
			'cap_jour'                  => $cap,
			'emails_envoyes_aujourdhui' => $envoyes_auj,
			'contrats_signes'           => count( $sigs ),
			'places_key'                => '' !== $places ? 'ok' : 'manquante',
			'hugo'                      => $hugo_on ? 'actif' : 'éteint',
			'smtp'                      => $smtp ? 'ok' : 'à vérifier',
			'dernier_run'               => (int) get_option( 'ag_auto_last', 0 ),
			'bloquants'                 => $bloc,
			'avertissements'            => $warn,
		);
	}
}

/* ── Le vrai travail (lourd) : appels externes, Hugo, chasse Places… ──── */
if ( ! function_exists( 'ag_auto_tick' ) ) {
	function ag_auto_tick( $heavy = true ) {
		// Détaché : on finit même si l'appelant a raccroché (loopback non bloquant).
		if ( function_exists( 'ignore_user_abort' ) ) { ignore_user_abort( true ); }
		if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( $heavy ? 150 : 45 ); }
		$now = time(); $ran = array();
		// RAPIDE et PRIORITAIRE (toujours) : l'ENVOI d'Hugo d'abord (ag_closer_cron),
		// puis les relances chaudes et la relève des réponses. Ces 3-là sont légers :
		// le bouton « Lancer maintenant » n'exécute QUE ça → réponse quasi immédiate,
		// et les emails sont partis avant toute étape lente.
		foreach ( array( 'ag_closer_cron', 'ag_rc_cron', 'ag_boite_cron' ) as $hook ) {
			if ( has_action( $hook ) ) { do_action( $hook ); $ran[] = $hook; }
		}
		// LOURD (seulement en mode auto / cron, pas au clic) : enrichissement (lent),
		// chasse Google Places (coûteuse, 1×/h), relances quotidiennes (1×/j).
		if ( $heavy ) {
			if ( has_action( 'ag_enrich_cron' ) ) { do_action( 'ag_enrich_cron' ); $ran[] = 'ag_enrich_cron'; }
			if ( has_action( 'ag_prospect_cron' ) && $now - (int) get_option( 'ag_auto_hunt', 0 ) > 3500 ) {
				do_action( 'ag_prospect_cron' ); update_option( 'ag_auto_hunt', $now, false ); $ran[] = 'ag_prospect_cron';
			}
			if ( has_action( 'ag_relance_cron' ) && $now - (int) get_option( 'ag_auto_relance', 0 ) > 80000 ) {
				do_action( 'ag_relance_cron' ); update_option( 'ag_auto_relance', $now, false ); $ran[] = 'ag_relance_cron';
			}
			// L'analyste (« Léa ») lit le tunnel et pousse son orientation : 1×/jour.
			if ( function_exists( 'ag_funnel_cron_maybe' ) && ag_funnel_cron_maybe() ) { $ran[] = 'ag_funnel_analyste'; }
			// Max l'expérimentateur : promeut tout seul la variante gagnante quand elle est nette.
			if ( function_exists( 'ag_ab_auto_promote' ) && ag_ab_auto_promote() ) { $ran[] = 'ag_ab_promotion'; }
			// Le Gardien de réputation : frein/chauffe automatique du plafond d'Hugo.
			if ( function_exists( 'ag_gardien_cron_maybe' ) && ag_gardien_cron_maybe() ) { $ran[] = 'ag_gardien'; }
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
			$diag = ag_auto_diag();
			// MÉTHODE ROBUSTE (PHP-FPM, cas Hostinger) : on RÉPOND au planificateur
			// tout de suite, on FERME la connexion, PUIS on fait le vrai travail dans
			// le MÊME process. Plus de 504, et surtout AUCUNE dépendance à un appel
			// HTTP interne (loopback) que l'hébergeur bloque parfois en silence —
			// c'était la cause probable de « dernier_run : jamais » = zéro email.
			if ( function_exists( 'fastcgi_finish_request' ) ) {
				if ( ! headers_sent() ) {
					status_header( 200 );
					header( 'Content-Type: application/json; charset=utf-8' );
					header( 'Connection: close' );
				}
				echo wp_json_encode( array(
					'ok'     => true,
					'queued' => true,
					'mode'   => 'fastcgi',
					'note'   => 'Chaine lancee (meme process, connexion fermee). Diagnostic ci-dessous.',
					'diag'   => $diag,
				) );
				fastcgi_finish_request();
				ag_auto_tick();
				exit;
			}
			// SECOURS : pas de FPM → appel non bloquant à /worker (peut être filtré).
			wp_remote_get( add_query_arg( 'token', ag_auto_token(), rest_url( 'ag/v1/worker' ) ), array(
				'blocking'  => false,
				'timeout'   => 0.01,
				'sslverify' => false,
				'headers'   => array( 'Cache-Control' => 'no-cache' ),
			) );
			return new WP_REST_Response( array(
				'ok'     => true,
				'queued' => true,
				'mode'   => 'loopback',
				'note'   => 'Chaine lancee en arriere-plan (loopback). Diagnostic ci-dessous.',
				'diag'   => $diag,
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
	// Lancer un tour TOUT DE SUITE, sans service externe : prouve l'envoi + met à jour le diagnostic.
	if ( isset( $_POST['ag_auto_now'] ) && check_admin_referer( 'ag_auto' ) && current_user_can( 'manage_options' ) ) {
		$avant = (array) get_option( 'ag_closer_jour', array() );
		$n0    = ( ( $avant['d'] ?? '' ) === gmdate( 'Ymd' ) ) ? (int) ( $avant['n'] ?? 0 ) : 0;
		$ran   = function_exists( 'ag_auto_tick' ) ? (array) ag_auto_tick( false ) : array();
		$apres = (array) get_option( 'ag_closer_jour', array() );
		$n1    = ( ( $apres['d'] ?? '' ) === gmdate( 'Ymd' ) ) ? (int) ( $apres['n'] ?? 0 ) : 0;
		$d2    = ag_auto_diag();
		set_transient( 'ag_auto_now_msg', array(
			'ran'       => $ran,
			'envoyes'   => max( 0, $n1 - $n0 ),
			'cumul'     => $n1,
			'cap'       => (int) ( $d2['cap_jour'] ?? 0 ),
			'eligibles' => (int) ( $d2['prospects_eligibles'] ?? 0 ),
		), 60 );
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
		echo '<form method="post" style="margin:10px 0 18px;display:flex;gap:8px;flex-wrap:wrap">';
		wp_nonce_field( 'ag_auto' );
		echo '<button class="button button-primary" name="ag_auto_now" value="1">▶ Lancer un tour maintenant</button>';
		echo '<button class="button" name="ag_auto_regen" value="1" onclick="return confirm(\'Régénérer le jeton ? L\\\'ancienne URL cessera de marcher.\');">Régénérer le jeton</button></form>';

		$msg = get_transient( 'ag_auto_now_msg' );
		if ( is_array( $msg ) ) {
			delete_transient( 'ag_auto_now_msg' );
			$env  = (int) ( $msg['envoyes'] ?? 0 );
			$cum  = (int) ( $msg['cumul'] ?? 0 );
			$cap  = (int) ( $msg['cap'] ?? 0 );
			$elig = (int) ( $msg['eligibles'] ?? 0 );
			$ran  = implode( ', ', array_map( 'sanitize_text_field', (array) ( $msg['ran'] ?? array() ) ) );
			$coul = $env > 0 ? '#1e7e34' : '#996800';
			echo '<div style="background:#f0f6fc;border-left:4px solid ' . esc_attr( $coul ) . ';padding:10px 14px;max-width:860px;margin-bottom:14px">';
			if ( $env > 0 ) {
				$txt = '✉️ <strong>' . esc_html( (string) $env ) . ' email(s) envoyé(s)</strong> à l\'instant. '
					. 'Total aujourd\'hui : ' . esc_html( (string) $cum ) . ' / ' . esc_html( (string) $cap ) . '.';
			} elseif ( $cap > 0 && $cum >= $cap ) {
				$txt = '✅ <strong>Plafond du jour atteint</strong> (' . esc_html( (string) $cum ) . ' / ' . esc_html( (string) $cap )
					. ' déjà envoyés aujourd\'hui). C\'est normal — c\'est la chauffe du domaine. Reviens demain, ou augmente le plafond dans l\'écran Hugo.';
			} elseif ( 0 === $elig ) {
				$txt = 'Aucun email ce tour : <strong>0 prospect éligible</strong> en ce moment. '
					. 'Soit ils ont déjà été contactés récemment (Hugo attend 2 jours entre deux messages), soit ils ont répondu / sont marqués client-refus-ne plus contacter. '
					. 'La chasse + l\'enrichissement (tour auto) ramènent de nouvelles cibles.';
			} else {
				$txt = 'Tour exécuté, 0 email ce passage (' . esc_html( (string) $elig ) . ' éligible(s) — réessaie, ou vérifie le plafond).';
			}
			echo '<strong>Tour exécuté.</strong> ' . $txt;
			if ( '' !== $ran ) { echo '<br><span class="description">Agents réveillés : ' . esc_html( $ran ) . '</span>'; }
			echo '</div>';
		}

		echo '<h2>État — pourquoi ça ne produit pas encore</h2>';
		if ( ! empty( $d['bloquants'] ) ) {
			echo '<div style="background:#fff8e5;border-left:4px solid #dba617;padding:10px 14px;max-width:860px"><strong>À débloquer :</strong><ul style="margin:6px 0 0">';
			foreach ( $d['bloquants'] as $b ) { echo '<li>' . esc_html( $b ) . '</li>'; }
			echo '</ul></div>';
		} else {
			echo '<p style="color:#1e7e34">✅ Aucun blocage détecté — laisse tourner : les résultats arrivent avec le volume et le temps (chauffe du domaine, cap journalier).</p>';
		}
		if ( ! empty( $d['avertissements'] ) ) {
			echo '<div style="background:#f0f6fc;border-left:4px solid #0073aa;padding:10px 14px;max-width:860px;margin-top:10px"><strong>ℹ️ À améliorer (n\'empêche pas l\'envoi) :</strong><ul style="margin:6px 0 0">';
			foreach ( $d['avertissements'] as $w ) { echo '<li>' . esc_html( $w ) . '</li>'; }
			echo '</ul></div>';
		}
		$rows = array(
			'Prospects (total)'           => (string) $d['prospects_total'],
			'Prospects avec email'        => (string) $d['prospects_avec_email'],
			'Prospects éligibles (prêts à démarcher)' => (string) $d['prospects_eligibles'],
			'Emails envoyés aujourd\'hui' => (string) $d['emails_envoyes_aujourdhui'] . ' / ' . (string) $d['cap_jour'] . ' (plafond du jour)',
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
