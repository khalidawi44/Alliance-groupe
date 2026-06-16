<?php
/**
 * AG Recherche Juridique — moteur principal.
 *
 * @package ag-avocat-recherche
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_Recherche_Juridique {

	const CPT_DOSSIER  = 'ag_jr_dossier';
	const CPT_ARGUMENT = 'ag_jr_argument';
	const CAP          = 'edit_posts'; // L'avocat (admin/éditeur) accède à l'outil.

	private static $inst = null;

	public static function instance() {
		if ( null === self::$inst ) {
			self::$inst = new self();
		}
		return self::$inst;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_cpts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Métabox dossier (champs structurés).
		add_action( 'add_meta_boxes', array( $this, 'dossier_metabox' ) );
		add_action( 'save_post_' . self::CPT_DOSSIER, array( $this, 'dossier_save' ) );

		// AJAX.
		add_action( 'wp_ajax_ag_jr_judilibre', array( $this, 'ajax_judilibre' ) );
		add_action( 'wp_ajax_ag_jr_decision', array( $this, 'ajax_decision' ) );
		add_action( 'wp_ajax_ag_jr_ai', array( $this, 'ajax_ai' ) );
		add_action( 'wp_ajax_ag_jr_arg_add', array( $this, 'ajax_arg_add' ) );
		add_action( 'wp_ajax_ag_jr_save_settings', array( $this, 'ajax_save_settings' ) );

		// Vue imprimable d'un dossier.
		add_action( 'admin_post_ag_jr_print', array( $this, 'print_dossier' ) );

		// ----- FRONT-END (espace cabinet, hors wp-admin) -----
		add_shortcode( 'ag_juridique', array( $this, 'shortcode_app' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_register' ) );
		add_action( 'init', array( $this, 'ensure_front_page' ) );
		// Lien menu « Espace juridique » — visible UNIQUEMENT pour le cabinet connecté.
		add_filter( 'wp_nav_menu_items', array( $this, 'nav_menu_link' ), 20, 2 );
		// CRUD dossiers en façade.
		add_action( 'wp_ajax_ag_jr_dossier_list', array( $this, 'ajax_dossier_list' ) );
		add_action( 'wp_ajax_ag_jr_dossier_get', array( $this, 'ajax_dossier_get' ) );
		add_action( 'wp_ajax_ag_jr_dossier_save_front', array( $this, 'ajax_dossier_save_front' ) );
		add_action( 'wp_ajax_ag_jr_dossier_delete', array( $this, 'ajax_dossier_delete' ) );

		// ----- PWA (appli installable iOS/Android, sans store) -----
		add_action( 'init', array( $this, 'pwa_endpoints' ), 1 );
		add_action( 'wp_head', array( $this, 'pwa_head' ) );
	}

	/* ============================================================
	 *  ACTIVATION
	 * ============================================================ */
	public static function on_activate() {
		// Rien de lourd : les CPT s'enregistrent au boot. On flush au cas où.
		self::instance()->register_cpts();
		flush_rewrite_rules();
	}

	/* ============================================================
	 *  CPT : Dossiers d'analyse + Banque d'arguments
	 * ============================================================ */
	public function register_cpts() {
		register_post_type( self::CPT_DOSSIER, array(
			'labels' => array(
				'name'          => 'Dossiers',
				'singular_name' => 'Dossier',
				'add_new'       => 'Nouveau dossier',
				'add_new_item'  => 'Nouveau dossier d\'analyse',
				'edit_item'     => 'Analyser le dossier',
				'search_items'  => 'Rechercher un dossier',
				'not_found'     => 'Aucun dossier pour le moment.',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false, // Rattaché à notre menu maison.
			'capability_type'     => 'post',
			'supports'            => array( 'title' ),
			'menu_icon'           => 'dashicons-portfolio',
		) );

		register_post_type( self::CPT_ARGUMENT, array(
			'labels' => array(
				'name'          => 'Banque d\'arguments',
				'singular_name' => 'Argument',
				'add_new'       => 'Nouvel argument',
				'add_new_item'  => 'Nouvel argument',
				'edit_item'     => 'Modifier l\'argument',
				'search_items'  => 'Rechercher un argument',
				'not_found'     => 'Banque vide : ajoutez vos premiers moyens.',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'supports'        => array( 'title', 'editor' ),
			'menu_icon'       => 'dashicons-archive',
		) );
	}

	/* ============================================================
	 *  MENU ADMIN
	 * ============================================================ */
	public function admin_menu() {
		add_menu_page(
			'Recherche juridique',
			'⚖️ Recherche juridique',
			self::CAP,
			'ag-jr',
			array( $this, 'page_recherche' ),
			'dashicons-search',
			3
		);
		add_submenu_page( 'ag-jr', 'Recherche', '🔎 Recherche', self::CAP, 'ag-jr', array( $this, 'page_recherche' ) );
		add_submenu_page( 'ag-jr', 'Mes dossiers', '📁 Mes dossiers', self::CAP, 'edit.php?post_type=' . self::CPT_DOSSIER );
		add_submenu_page( 'ag-jr', 'Banque d\'arguments', '🗂️ Arguments', self::CAP, 'edit.php?post_type=' . self::CPT_ARGUMENT );
		add_submenu_page( 'ag-jr', 'Réglages', '⚙️ Réglages', 'manage_options', 'ag-jr-settings', array( $this, 'page_settings' ) );
	}

	/* ============================================================
	 *  ASSETS
	 * ============================================================ */
	public function enqueue( $hook ) {
		// Charge sur nos pages + l'édition d'un dossier.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_ours = ( strpos( (string) $hook, 'ag-jr' ) !== false );
		$is_dossier = ( $screen && $screen->post_type === self::CPT_DOSSIER );
		if ( ! $is_ours && ! $is_dossier ) {
			return;
		}
		wp_enqueue_style( 'ag-jr', AG_JR_URL . 'assets/recherche.css', array(), AG_JR_VERSION );
		wp_enqueue_script( 'ag-jr', AG_JR_URL . 'assets/recherche.js', array(), AG_JR_VERSION, true );
		wp_localize_script( 'ag-jr', 'AGJR', array(
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ag_jr' ),
			'sources' => $this->sources(),
			'ai_on'   => (bool) trim( (string) get_option( 'ag_jr_ai_key', '' ) ),
			'live_on' => ( trim( (string) get_option( 'ag_jr_piste_id', '' ) ) && trim( (string) get_option( 'ag_jr_piste_secret', '' ) ) ),
		) );
	}

	/* ============================================================
	 *  SOURCES JURIDIQUES (méta-moteur) — {q} = requête encodée
	 * ============================================================ */
	public function sources() {
		$s = array(
			'Jurisprudence judiciaire' => array(
				array( 'Légifrance — jurisprudence judiciaire', 'https://www.legifrance.gouv.fr/search/juri?query={q}&searchField=ALL&tab_selection=juri' ),
				array( 'Cour de cassation (Judilibre)', 'https://www.courdecassation.fr/recherche-judilibre?search_api_fulltext={q}' ),
				array( 'Cours d\'appel (Judilibre)', 'https://www.courdecassation.fr/recherche-judilibre?search_api_fulltext={q}&jurisdiction=ca' ),
			),
			'Jurisprudence administrative' => array(
				array( 'Légifrance — juridiction administrative', 'https://www.legifrance.gouv.fr/search/juriAdmin?query={q}&searchField=ALL&tab_selection=juriAdmin' ),
				array( 'Conseil d\'État — ArianeWeb', 'https://www.conseil-etat.fr/arianeweb/#/recherche' ),
			),
			'Jurisprudence constitutionnelle' => array(
				array( 'Conseil constitutionnel', 'https://www.conseil-constitutionnel.fr/recherche?search_api_fulltext={q}' ),
			),
			'Jurisprudence européenne' => array(
				array( 'CEDH — HUDOC', 'https://hudoc.echr.coe.int/fre#%7B%22fulltext%22:%5B%22{q}%22%5D%7D' ),
				array( 'CJUE — CURIA', 'https://curia.europa.eu/juris/recherche.jsf?language=fr' ),
				array( 'EUR-Lex (textes UE)', 'https://eur-lex.europa.eu/search.html?scope=EURLEX&text={q}&lang=fr&type=quick' ),
			),
			'Codes & textes' => array(
				array( 'Légifrance — codes', 'https://www.legifrance.gouv.fr/search/code?query={q}&searchField=ALL&tab_selection=code' ),
				array( 'Légifrance — recherche globale', 'https://www.legifrance.gouv.fr/search/all?query={q}&searchField=ALL&tab_selection=all' ),
				array( 'Légifrance — Journal officiel', 'https://www.legifrance.gouv.fr/search/jorf?query={q}&searchField=ALL&tab_selection=jorf' ),
			),
			'Doctrine & commentaires' => array(
				array( 'Doctrine.fr', 'https://www.doctrine.fr/recherche?q={q}' ),
				array( 'Dalloz Actualité', 'https://www.dalloz-actualite.fr/recherche?query={q}' ),
				array( 'Le Village de la Justice', 'https://www.google.com/search?q=site:village-justice.com+{q}' ),
				array( 'Google Scholar', 'https://scholar.google.com/scholar?q={q}' ),
			),
			'Fiscal & social' => array(
				array( 'BOFiP (doctrine fiscale)', 'https://bofip.impots.gouv.fr/bofip/recherche/resultats?q={q}' ),
				array( 'Service-public.fr', 'https://www.service-public.fr/particuliers/recherche?keyword={q}' ),
				array( 'BOSS (sécurité sociale)', 'https://boss.gouv.fr/recherche?search={q}' ),
			),
			'Vérifier un adversaire / une société' => array(
				array( 'Pappers (sociétés)', 'https://www.pappers.fr/recherche?q={q}' ),
				array( 'BODACC (annonces légales)', 'https://www.bodacc.fr/pages/annonces-commerciales/?q={q}' ),
				array( 'Infogreffe', 'https://www.infogreffe.fr/recherche-entreprise?q={q}' ),
			),
		);
		return apply_filters( 'ag_jr_sources', $s );
	}

	/* ============================================================
	 *  PAGE : RECHERCHE (méta-moteur + live Judilibre)
	 * ============================================================ */
	public function page_recherche() {
		$live_on = ( trim( (string) get_option( 'ag_jr_piste_id', '' ) ) && trim( (string) get_option( 'ag_jr_piste_secret', '' ) ) );
		?>
		<div class="wrap ag-jr">
			<h1>⚖️ Recherche juridique</h1>
			<p class="ag-jr-sub">Un seul endroit pour chercher, analyser et préparer vos arguments. Tapez votre question (mots-clés, notion, numéro de pourvoi, article de code…).</p>

			<div class="ag-jr-searchbar">
				<input type="text" id="ag-jr-q" placeholder="Ex : licenciement sans cause réelle, clause abusive, garde alternée, article 1240 du code civil…" autofocus />
				<button class="button button-primary button-hero" id="ag-jr-go">Lancer la recherche</button>
			</div>

			<div class="ag-jr-tabs">
				<button class="ag-jr-tab is-active" data-tab="live">🔴 Jurisprudence en direct</button>
				<button class="ag-jr-tab" data-tab="meta">🌐 Toutes les sources</button>
				<button class="ag-jr-tab" data-tab="ai">🤖 Aide à l'analyse</button>
			</div>

			<!-- ONGLET LIVE -->
			<section class="ag-jr-panel is-active" data-panel="live">
				<?php if ( ! $live_on ) : ?>
					<div class="ag-jr-note">
						<strong>Activez la recherche LIVE gratuite.</strong> L'API <em>Judilibre</em> (Cour de cassation + cours d'appel, open data officiel) est gratuite.
						Créez un compte sur <a href="https://piste.gouv.fr" target="_blank" rel="noopener">piste.gouv.fr</a>, abonnez-vous à l'API <em>Judilibre</em>, puis collez votre identifiant/clé dans
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-jr-settings' ) ); ?>">Réglages</a>.
						En attendant, l'onglet « Toutes les sources » fonctionne déjà.
					</div>
				<?php endif; ?>
				<div class="ag-jr-filters">
					<label>Juridiction
						<select id="ag-jr-jur">
							<option value="">Toutes</option>
							<option value="cc">Cour de cassation</option>
							<option value="ca">Cours d'appel</option>
							<option value="tj">Tribunaux judiciaires</option>
						</select>
					</label>
					<label>Trier par
						<select id="ag-jr-sort">
							<option value="score">Pertinence</option>
							<option value="date">Date (récent)</option>
						</select>
					</label>
				</div>
				<div id="ag-jr-results" class="ag-jr-results"><p class="ag-jr-empty">Vos résultats de jurisprudence apparaîtront ici.</p></div>
			</section>

			<!-- ONGLET META -->
			<section class="ag-jr-panel" data-panel="meta">
				<p class="ag-jr-hint">Cliquez sur une source : elle ouvre la recherche déjà pré-remplie avec vos mots-clés, dans un nouvel onglet.</p>
				<div id="ag-jr-sources"></div>
			</section>

			<!-- ONGLET IA -->
			<section class="ag-jr-panel" data-panel="ai">
				<?php $this->render_ai_panel(); ?>
			</section>
		</div>
		<?php
	}

	private function render_ai_panel() {
		$ai_on = (bool) trim( (string) get_option( 'ag_jr_ai_key', '' ) );
		if ( ! $ai_on ) {
			echo '<div class="ag-jr-note"><strong>Assistant IA optionnel (désactivé).</strong> Ajoutez une clé API (Anthropic Claude recommandé) dans <a href="' . esc_url( admin_url( 'admin.php?page=ag-jr-settings' ) ) . '">Réglages</a> pour : reformuler le problème de droit, anticiper les arguments adverses et leur parade, ou résumer une décision. <em>L\'IA assiste — l\'avocat vérifie toujours les sources.</em></div>';
			return;
		}
		?>
		<div class="ag-jr-ai">
			<div class="ag-jr-ai-tasks">
				<button class="button ag-jr-ai-task" data-task="probleme">⚖️ Formuler le problème de droit</button>
				<button class="button ag-jr-ai-task" data-task="adverse">🛡️ Arguments adverses + parade</button>
				<button class="button ag-jr-ai-task" data-task="resume">📄 Résumer une décision</button>
				<button class="button ag-jr-ai-task" data-task="strategie">♟️ Stratégie & moyens</button>
			</div>
			<textarea id="ag-jr-ai-input" rows="7" placeholder="Décrivez les faits, collez la décision à résumer, ou exposez votre position…"></textarea>
			<button class="button button-primary" id="ag-jr-ai-run">Analyser</button>
			<div id="ag-jr-ai-out" class="ag-jr-ai-out"></div>
			<p class="ag-jr-disclaimer">⚠️ Aide à la réflexion générée automatiquement. Vérifiez systématiquement les références et la jurisprudence avant tout usage.</p>
		</div>
		<?php
	}

	/* ============================================================
	 *  AJAX : RECHERCHE LIVE JUDILIBRE (open data, via PISTE)
	 * ============================================================ */
	private function check_nonce() {
		if ( ! current_user_can( self::CAP ) || ! check_ajax_referer( 'ag_jr', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Accès refusé.' ), 403 );
		}
	}

	private function piste_token() {
		$id     = trim( (string) get_option( 'ag_jr_piste_id', '' ) );
		$secret = trim( (string) get_option( 'ag_jr_piste_secret', '' ) );
		if ( ! $id || ! $secret ) {
			return new WP_Error( 'no_key', 'Identifiants PISTE manquants (Réglages).' );
		}
		$cached = get_transient( 'ag_jr_piste_token' );
		if ( $cached ) {
			return $cached;
		}
		$oauth = trim( (string) get_option( 'ag_jr_piste_oauth', 'https://oauth.piste.gouv.fr/api/oauth/token' ) );
		// Auto-réparation : l'ancien host PISTE (aife.economie.gouv.fr) est mort.
		$oauth = str_replace( 'aife.economie.gouv.fr', 'piste.gouv.fr', $oauth );
		if ( '' === $oauth ) {
			$oauth = 'https://oauth.piste.gouv.fr/api/oauth/token';
		}
		$scope = trim( (string) get_option( 'ag_jr_piste_scope', 'openid' ) );

		// PISTE accepte l'authentification client dans le CORPS, mais l'environnement
		// de PRODUCTION exige souvent l'en-tête HTTP Basic. On tente le corps, puis on
		// bascule automatiquement en Basic si « invalid_client » → robuste partout.
		$attempts = array(
			array(
				'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
				'body'    => array(
					'grant_type'    => 'client_credentials',
					'client_id'     => $id,
					'client_secret' => $secret,
					'scope'         => $scope,
				),
			),
			array(
				'headers' => array(
					'Content-Type'  => 'application/x-www-form-urlencoded',
					'Authorization' => 'Basic ' . base64_encode( $id . ':' . $secret ),
				),
				'body'    => array(
					'grant_type' => 'client_credentials',
					'scope'      => $scope,
				),
			),
		);

		$last = '';
		foreach ( $attempts as $a ) {
			$resp = wp_remote_post( $oauth, array( 'timeout' => 20, 'headers' => $a['headers'], 'body' => $a['body'] ) );
			if ( is_wp_error( $resp ) ) {
				$last = $resp->get_error_message();
				continue;
			}
			$raw  = wp_remote_retrieve_body( $resp );
			$data = json_decode( $raw, true );
			if ( ! empty( $data['access_token'] ) ) {
				$ttl = isset( $data['expires_in'] ) ? max( 60, intval( $data['expires_in'] ) - 60 ) : 1800;
				set_transient( 'ag_jr_piste_token', $data['access_token'], $ttl );
				return $data['access_token'];
			}
			$last = $raw;
			// Si ce n'est PAS un souci d'authentification client, inutile de réessayer en Basic.
			if ( false === strpos( (string) $raw, 'invalid_client' ) ) {
				break;
			}
		}
		$idmask = strlen( $id ) > 8 ? substr( $id, 0, 6 ) . '…' : $id;
		return new WP_Error(
			'oauth',
			'OAuth refusé. Vérifiez : (1) le Client ID utilisé = « ' . $idmask . ' » (ce doit être l\'« Identifiants OAuth → Client ID », PAS l\'API Key) ; '
			. '(2) le Secret = bouton « Consulter le secret » de la section « Identifiants OAuth » (pas celui des « API Keys ») ; '
			. '(3) l\'URL OAuth = « ' . $oauth .' » (production, sans « sandbox- »). Réponse PISTE : ' . substr( (string) $last, 0, 240 )
		);
	}

	public function ajax_judilibre() {
		$this->check_nonce();
		$q = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
		if ( '' === $q ) {
			wp_send_json_error( array( 'message' => 'Tapez une recherche.' ) );
		}
		$token = $this->piste_token();
		if ( is_wp_error( $token ) ) {
			wp_send_json_error( array( 'message' => $token->get_error_message() ) );
		}
		$base = get_option( 'ag_jr_judilibre_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' );
		$args = array(
			'query'     => $q,
			'page_size' => 12,
			'resolve_references' => 'true',
		);
		$jur  = isset( $_POST['jur'] ) ? sanitize_text_field( wp_unslash( $_POST['jur'] ) ) : '';
		$sort = isset( $_POST['sort'] ) ? sanitize_text_field( wp_unslash( $_POST['sort'] ) ) : 'score';
		if ( 'date' === $sort ) {
			$args['sort']  = 'date';
			$args['order'] = 'desc';
		}
		$url = add_query_arg( $args, $base );
		if ( $jur ) {
			// Judilibre attend des jurisdiction[] répétés ; on ajoute proprement.
			$url .= '&jurisdiction=' . rawurlencode( $jur );
		}
		$headers = array(
			'Authorization' => 'Bearer ' . $token,
			'Accept'        => 'application/json',
		);
		$apikey = trim( (string) get_option( 'ag_jr_piste_apikey', '' ) );
		if ( '' !== $apikey ) {
			$headers['KeyId'] = $apikey;
		}
		$resp = wp_remote_get( $url, array( 'timeout' => 25, 'headers' => $headers ) );
		if ( is_wp_error( $resp ) ) {
			wp_send_json_error( array( 'message' => $resp->get_error_message() ) );
		}
		$code = wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( $code >= 400 || ! is_array( $body ) ) {
			$raw = wp_remote_retrieve_body( $resp );
			$msg = 'API Judilibre (HTTP ' . $code . ') : ' . substr( $raw, 0, 300 );
			if ( 403 === $code ) {
				$msg = 'Accès Judilibre refusé (HTTP 403). Votre application PISTE n\'est pas autorisée à appeler cette API. À vérifier : (1) l\'abonnement à l\'API « Judilibre » est ACTIF sur votre application (pas « en attente » / « Demander l\'accès ») ; (2) le champ « KeyId / clé API » est renseigné dans les Réglages ; (3) Sandbox vs Production : vos clés et les URLs doivent être du même environnement.';
			} elseif ( $code >= 500 || strpos( $raw, 'no_shard_available' ) !== false || strpos( $raw, 'search_phase_execution' ) !== false ) {
				$msg = 'Judilibre a renvoyé une erreur serveur (HTTP ' . $code . '). C\'est presque toujours l\'environnement SANDBOX : son index de test est vide/instable et ne contient pas les vraies décisions. Pour obtenir de vrais résultats, basculez en PRODUCTION (application + abonnement Judilibre de production sur piste.gouv.fr, puis URLs sans « sandbox- » dans les Réglages avancés). En attendant, utilisez l\'onglet « Toutes les sources ».';
			}
			wp_send_json_error( array( 'message' => $msg ) );
		}
		$out = array();
		$results = isset( $body['results'] ) ? $body['results'] : array();
		foreach ( $results as $r ) {
			$summary = '';
			if ( ! empty( $r['summary'] ) ) {
				$summary = $r['summary'];
			} elseif ( ! empty( $r['highlights']['text'] ) ) {
				$summary = is_array( $r['highlights']['text'] ) ? implode( ' […] ', $r['highlights']['text'] ) : $r['highlights']['text'];
			} elseif ( ! empty( $r['text'] ) ) {
				$summary = mb_substr( wp_strip_all_tags( $r['text'] ), 0, 320 );
			}
			$out[] = array(
				'id'        => isset( $r['id'] ) ? $r['id'] : '',
				'jurisdiction' => isset( $r['jurisdiction'] ) ? $r['jurisdiction'] : '',
				'chamber'   => isset( $r['chamber'] ) ? $r['chamber'] : '',
				'number'    => isset( $r['number'] ) ? ( is_array( $r['number'] ) ? implode( ', ', $r['number'] ) : $r['number'] ) : '',
				'ecli'      => isset( $r['ecli'] ) ? $r['ecli'] : '',
				'date'      => isset( $r['decision_date'] ) ? $r['decision_date'] : ( isset( $r['date'] ) ? $r['date'] : '' ),
				'type'      => isset( $r['type'] ) ? $r['type'] : '',
				'themes'    => isset( $r['themes'] ) ? (array) $r['themes'] : array(),
				'solution'  => isset( $r['solution'] ) ? $r['solution'] : '',
				'summary'   => wp_strip_all_tags( (string) $summary ),
			);
		}
		wp_send_json_success( array(
			'total'   => isset( $body['total'] ) ? intval( $body['total'] ) : count( $out ),
			'results' => $out,
		) );
	}

	public function ajax_decision() {
		$this->check_nonce();
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Identifiant manquant.' ) );
		}
		$token = $this->piste_token();
		if ( is_wp_error( $token ) ) {
			wp_send_json_error( array( 'message' => $token->get_error_message() ) );
		}
		$base = get_option( 'ag_jr_judilibre_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' );
		$base = str_replace( '/search', '/decision', $base );
		$resp = wp_remote_get( add_query_arg( array( 'id' => $id ), $base ), array(
			'timeout' => 25,
			'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json', 'KeyId' => get_option( 'ag_jr_piste_apikey', '' ) ),
		) );
		if ( is_wp_error( $resp ) ) {
			wp_send_json_error( array( 'message' => $resp->get_error_message() ) );
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! is_array( $body ) ) {
			wp_send_json_error( array( 'message' => 'Réponse invalide.' ) );
		}
		wp_send_json_success( array(
			'text'    => isset( $body['text'] ) ? $body['text'] : '',
			'summary' => isset( $body['summary'] ) ? $body['summary'] : '',
			'zones'   => isset( $body['zones'] ) ? $body['zones'] : array(),
		) );
	}

	/* ============================================================
	 *  AJAX : ASSISTANT IA (optionnel, Anthropic par défaut)
	 * ============================================================ */
	public function ajax_ai() {
		$this->check_nonce();
		$key = trim( (string) get_option( 'ag_jr_ai_key', '' ) );
		if ( ! $key ) {
			wp_send_json_error( array( 'message' => 'Aucune clé IA configurée.' ) );
		}
		$task  = isset( $_POST['task'] ) ? sanitize_text_field( wp_unslash( $_POST['task'] ) ) : 'probleme';
		$input = isset( $_POST['input'] ) ? sanitize_textarea_field( wp_unslash( $_POST['input'] ) ) : '';
		if ( '' === $input ) {
			wp_send_json_error( array( 'message' => 'Donnez du contexte (faits, position, ou décision).' ) );
		}

		$prompts = array(
			'probleme'  => "Tu es juriste français. À partir des faits ci-dessous : 1) qualifie juridiquement la situation, 2) formule le ou les problème(s) de droit de façon précise, 3) cite les textes (codes/articles) et notions probablement applicables. Sois rigoureux et signale si une vérification de jurisprudence est nécessaire.",
			'adverse'   => "Tu es avocat français en posture stratégique. À partir de la position ci-dessous : liste les arguments que la partie ADVERSE soulèvera le plus probablement, et pour CHAQUE argument adverse, propose une PARADE (contre-argument, moyen de droit, texte ou jurisprudence à mobiliser). Présente sous forme « Argument adverse → Parade ».",
			'resume'    => "Tu es juriste français. Résume la décision ci-dessous : juridiction et date, problème de droit, solution retenue, motifs essentiels, et PORTÉE/apport de l'arrêt. Termine par « À retenir » en une phrase.",
			'strategie' => "Tu es avocat français. À partir des éléments ci-dessous, propose une stratégie : moyens à invoquer (par ordre de force), pièces à réunir, risques, et angle de plaidoirie. Sois concret et opérationnel.",
		);
		$sys = isset( $prompts[ $task ] ) ? $prompts[ $task ] : $prompts['probleme'];
		$sys .= " Réponds en français, de façon structurée (titres, listes). Rappelle en fin de réponse que l'avocat doit vérifier les sources primaires.";

		$model = get_option( 'ag_jr_ai_model', 'claude-sonnet-4-6' );
		$resp  = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
			'timeout' => 60,
			'headers' => array(
				'x-api-key'         => $key,
				'anthropic-version' => '2023-06-01',
				'content-type'      => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model'      => $model,
				'max_tokens' => 1500,
				'system'     => $sys,
				'messages'   => array( array( 'role' => 'user', 'content' => $input ) ),
			) ),
		) );
		if ( is_wp_error( $resp ) ) {
			wp_send_json_error( array( 'message' => $resp->get_error_message() ) );
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! empty( $body['content'][0]['text'] ) ) {
			wp_send_json_success( array( 'text' => $body['content'][0]['text'] ) );
		}
		$msg = isset( $body['error']['message'] ) ? $body['error']['message'] : substr( wp_remote_retrieve_body( $resp ), 0, 300 );
		wp_send_json_error( array( 'message' => 'IA : ' . $msg ) );
	}

	/* ============================================================
	 *  AJAX : ajout rapide d'un argument à la banque
	 * ============================================================ */
	public function ajax_arg_add() {
		$this->check_nonce();
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$body  = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
		if ( '' === $title ) {
			wp_send_json_error( array( 'message' => 'Intitulé requis.' ) );
		}
		$id = wp_insert_post( array(
			'post_type'    => self::CPT_ARGUMENT,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $body,
		) );
		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}
		wp_send_json_success( array( 'id' => $id, 'edit' => get_edit_post_link( $id, 'raw' ) ) );
	}

	/* ============================================================
	 *  DOSSIER : métabox des champs structurés
	 * ============================================================ */
	private function dossier_fields() {
		return array(
			'reference'   => array( 'Référence / Client', 'text' ),
			'domaine'     => array( 'Domaine du droit', 'text' ),
			'faits'       => array( 'Faits', 'area' ),
			'probleme'    => array( 'Problème de droit', 'area' ),
			'textes'      => array( 'Textes applicables (codes, articles, lois)', 'area' ),
			'jurisprudence' => array( 'Jurisprudence favorable', 'area' ),
			'adverse'     => array( 'Arguments adverses', 'area' ),
			'parade'      => array( 'Parade / contre-arguments', 'area' ),
			'strategie'   => array( 'Stratégie & moyens', 'area' ),
			'pieces'      => array( 'Pièces / annexes', 'area' ),
		);
	}

	public function dossier_metabox() {
		add_meta_box( 'ag_jr_dossier', '⚖️ Analyse du dossier', array( $this, 'dossier_box_html' ), self::CPT_DOSSIER, 'normal', 'high' );
	}

	public function dossier_box_html( $post ) {
		wp_nonce_field( 'ag_jr_dossier_save', 'ag_jr_dossier_nonce' );
		echo '<div class="ag-jr-dossier">';
		foreach ( $this->dossier_fields() as $k => $def ) {
			list( $label, $type ) = $def;
			$val = get_post_meta( $post->ID, '_ag_jr_' . $k, true );
			echo '<p><label for="ag_jr_' . esc_attr( $k ) . '"><strong>' . esc_html( $label ) . '</strong></label><br/>';
			if ( 'area' === $type ) {
				echo '<textarea class="large-text" rows="4" id="ag_jr_' . esc_attr( $k ) . '" name="ag_jr_' . esc_attr( $k ) . '">' . esc_textarea( $val ) . '</textarea>';
			} else {
				echo '<input type="text" class="large-text" id="ag_jr_' . esc_attr( $k ) . '" name="ag_jr_' . esc_attr( $k ) . '" value="' . esc_attr( $val ) . '" />';
			}
			echo '</p>';
		}
		$print = wp_nonce_url( admin_url( 'admin-post.php?action=ag_jr_print&id=' . $post->ID ), 'ag_jr_print_' . $post->ID );
		echo '<p><a href="' . esc_url( $print ) . '" target="_blank" class="button">🖨️ Vue imprimable / Export</a></p>';
		echo '</div>';
	}

	public function dossier_save( $post_id ) {
		if ( ! isset( $_POST['ag_jr_dossier_nonce'] ) || ! wp_verify_nonce( $_POST['ag_jr_dossier_nonce'], 'ag_jr_dossier_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( array_keys( $this->dossier_fields() ) as $k ) {
			if ( isset( $_POST[ 'ag_jr_' . $k ] ) ) {
				update_post_meta( $post_id, '_ag_jr_' . $k, sanitize_textarea_field( wp_unslash( $_POST[ 'ag_jr_' . $k ] ) ) );
			}
		}
	}

	public function print_dossier() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'Accès refusé.' );
		}
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! $id || ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '', 'ag_jr_print_' . $id ) ) {
			wp_die( 'Lien invalide.' );
		}
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== self::CPT_DOSSIER ) {
			wp_die( 'Dossier introuvable.' );
		}
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>' . esc_html( $post->post_title ) . '</title>';
		echo '<style>body{font-family:Georgia,serif;max-width:800px;margin:30px auto;color:#1a1a1a;line-height:1.6;padding:0 20px}h1{border-bottom:3px solid #D4B45C;padding-bottom:8px}h2{color:#1f2740;margin-top:26px;font-size:17px;border-left:4px solid #D4B45C;padding-left:10px}p{white-space:pre-wrap}.muted{color:#888;font-size:13px}@media print{.noprint{display:none}}</style></head><body>';
		echo '<p class="noprint"><button onclick="window.print()">🖨️ Imprimer / PDF</button></p>';
		echo '<h1>' . esc_html( $post->post_title ) . '</h1>';
		echo '<p class="muted">Dossier d\'analyse — ' . esc_html( get_the_date( 'd/m/Y', $post ) ) . '</p>';
		foreach ( $this->dossier_fields() as $k => $def ) {
			$val = get_post_meta( $id, '_ag_jr_' . $k, true );
			if ( '' === trim( (string) $val ) ) {
				continue;
			}
			echo '<h2>' . esc_html( $def[0] ) . '</h2><p>' . esc_html( $val ) . '</p>';
		}
		echo '<hr/><p class="muted">Document de travail interne. Vérifier les sources primaires (Légifrance, Judilibre) avant tout usage.</p>';
		echo '</body></html>';
		exit;
	}

	/* ============================================================
	 *  PAGE : RÉGLAGES
	 * ============================================================ */
	public function ajax_save_settings() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'ag_jr', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Accès refusé.' ), 403 );
		}
		$map = array(
			'ag_jr_piste_id'      => 'sanitize_text_field',
			'ag_jr_piste_secret'  => 'sanitize_text_field',
			'ag_jr_piste_apikey'  => 'sanitize_text_field',
			'ag_jr_piste_oauth'   => 'esc_url_raw',
			'ag_jr_piste_scope'   => 'sanitize_text_field',
			'ag_jr_judilibre_url' => 'esc_url_raw',
			'ag_jr_ai_key'        => 'sanitize_text_field',
			'ag_jr_ai_model'      => 'sanitize_text_field',
		);
		foreach ( $map as $opt => $san ) {
			if ( isset( $_POST[ $opt ] ) ) {
				update_option( $opt, call_user_func( $san, wp_unslash( $_POST[ $opt ] ) ) );
			}
		}
		delete_transient( 'ag_jr_piste_token' );
		wp_send_json_success( array( 'message' => 'Réglages enregistrés.' ) );
	}

	public function page_settings() {
		$g = function ( $k, $d = '' ) {
			return esc_attr( get_option( $k, $d ) );
		};
		?>
		<div class="wrap ag-jr">
			<h1>⚙️ Réglages — Recherche juridique</h1>

			<h2>🔴 Recherche LIVE de jurisprudence (Judilibre — gratuit)</h2>
			<p class="ag-jr-sub">Open data officiel de la Cour de cassation et des cours d'appel. <strong>Comment l'activer :</strong></p>
			<ol class="ag-jr-steps">
				<li>Créez un compte sur <a href="https://piste.gouv.fr" target="_blank" rel="noopener">piste.gouv.fr</a> (gratuit).</li>
				<li>Créez une application, puis abonnez-vous à l'API <strong>Judilibre</strong>.</li>
				<li>Copiez le <strong>Client ID</strong> et le <strong>Client Secret</strong> de l'application ci-dessous.</li>
			</ol>
			<table class="form-table">
				<tr><th>Client ID (PISTE)</th><td><input type="text" class="regular-text" id="ag_jr_piste_id" value="<?php echo $g( 'ag_jr_piste_id' ); ?>" /></td></tr>
				<tr><th>Client Secret (PISTE)</th><td><input type="password" class="regular-text" id="ag_jr_piste_secret" value="<?php echo $g( 'ag_jr_piste_secret' ); ?>" /></td></tr>
				<tr><th>KeyId / clé API <em>(si demandée)</em></th><td><input type="text" class="regular-text" id="ag_jr_piste_apikey" value="<?php echo $g( 'ag_jr_piste_apikey' ); ?>" /></td></tr>
			</table>
			<details>
				<summary>Réglages avancés (URLs / scope)</summary>
				<table class="form-table">
					<tr><th>URL OAuth</th><td><input type="text" class="large-text" id="ag_jr_piste_oauth" value="<?php echo $g( 'ag_jr_piste_oauth', 'https://oauth.piste.gouv.fr/api/oauth/token' ); ?>" /></td></tr>
					<tr><th>Scope</th><td><input type="text" class="regular-text" id="ag_jr_piste_scope" value="<?php echo $g( 'ag_jr_piste_scope', 'openid' ); ?>" /></td></tr>
					<tr><th>URL API Judilibre</th><td><input type="text" class="large-text" id="ag_jr_judilibre_url" value="<?php echo $g( 'ag_jr_judilibre_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' ); ?>" /></td></tr>
				</table>
			</details>

			<h2>🤖 Assistant IA (optionnel)</h2>
			<p class="ag-jr-sub">Pour reformuler un problème de droit, anticiper les arguments adverses, résumer une décision. <strong>Anthropic Claude recommandé.</strong> Clé sur <a href="https://console.anthropic.com" target="_blank" rel="noopener">console.anthropic.com</a>.</p>
			<table class="form-table">
				<tr><th>Clé API Anthropic</th><td><input type="password" class="regular-text" id="ag_jr_ai_key" value="<?php echo $g( 'ag_jr_ai_key' ); ?>" /></td></tr>
				<tr><th>Modèle</th><td>
					<select id="ag_jr_ai_model">
						<?php
						$cur = get_option( 'ag_jr_ai_model', 'claude-sonnet-4-6' );
						foreach ( array(
							'claude-sonnet-4-6' => 'Claude Sonnet 4.6 (rapide, économique — recommandé)',
							'claude-opus-4-8'   => 'Claude Opus 4.8 (le plus puissant)',
							'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (le moins cher)',
						) as $val => $lbl ) {
							echo '<option value="' . esc_attr( $val ) . '" ' . selected( $cur, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
						}
						?>
					</select>
				</td></tr>
			</table>

			<p><button class="button button-primary button-hero" id="ag-jr-save">💾 Enregistrer les réglages</button> <span id="ag-jr-save-msg"></span></p>
			<p class="ag-jr-disclaimer">🔒 Les clés sont stockées dans votre base WordPress et ne quittent jamais votre site (sauf appels directs aux API officielles). Déontologie : cet outil assiste la recherche, il ne remplace pas l'analyse de l'avocat.</p>
		</div>
		<?php
	}

	/* ============================================================
	 *  FRONT-END — Espace cabinet (hors wp-admin)
	 * ============================================================ */

	/** Crée la page « Espace juridique » avec le shortcode, une seule fois. */
	public function ensure_front_page() {
		if ( get_option( 'ag_jr_front_page_done' ) ) {
			return;
		}
		if ( ! get_page_by_path( 'espace-juridique' ) ) {
			wp_insert_post( array(
				'post_title'   => 'Espace juridique',
				'post_name'    => 'espace-juridique',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[ag_juridique]',
			) );
		}
		update_option( 'ag_jr_front_page_done', 1 );
	}

	/**
	 * Ajoute « ⚖️ Espace juridique » au menu principal — réservé : le lien
	 * n'apparaît QUE pour un membre du cabinet connecté (capacité CAP). Les
	 * visiteurs ne le voient pas. Sur l'emplacement de menu principal seulement.
	 */
	public function nav_menu_link( $items, $args ) {
		if ( ! is_user_logged_in() || ! current_user_can( self::CAP ) ) {
			return $items;
		}
		$loc = isset( $args->theme_location ) ? $args->theme_location : '';
		if ( 'primary' !== $loc && 'menu-1' !== $loc && 'main' !== $loc ) {
			return $items;
		}
		$page = get_page_by_path( 'espace-juridique' );
		$url  = $page ? get_permalink( $page ) : home_url( '/espace-juridique/' );
		$current = ( $page && is_page( $page->ID ) ) ? ' current-menu-item' : '';
		$items  .= '<li class="menu-item ag-jr-menu-item' . $current . '"><a href="' . esc_url( $url ) . '">⚖️ Espace juridique</a></li>';
		return $items;
	}

	/** Enregistre (sans charger) les assets front ; le shortcode les charge. */
	public function front_register() {
		wp_register_style( 'ag-jr', AG_JR_URL . 'assets/recherche.css', array(), AG_JR_VERSION );
		wp_register_style( 'ag-jr-front', AG_JR_URL . 'assets/front.css', array( 'ag-jr' ), AG_JR_VERSION );
		wp_register_script( 'ag-jr', AG_JR_URL . 'assets/recherche.js', array(), AG_JR_VERSION, true );
		wp_register_script( 'ag-jr-dossiers', AG_JR_URL . 'assets/front-dossiers.js', array( 'ag-jr' ), AG_JR_VERSION, true );
		wp_register_script( 'ag-jr-pwa', AG_JR_URL . 'assets/pwa.js', array( 'ag-jr' ), AG_JR_VERSION, true );
	}

	/** URL de démarrage de l'app (page Espace juridique). */
	private function pwa_start_url() {
		$page = get_page_by_path( 'espace-juridique' );
		return $page ? get_permalink( $page ) : home_url( '/' );
	}

	/** Le shortcode [ag_juridique] : application complète en façade. */
	public function shortcode_app( $atts ) {
		if ( ! is_user_logged_in() || ! current_user_can( self::CAP ) ) {
			$login = wp_login_url( get_permalink() );
			return '<div class="ag-jr-front ag-jr-gate">'
				. '<h2>⚖️ Espace juridique réservé au cabinet</h2>'
				. '<p>Cet outil de recherche et d\'analyse est privé. Connectez-vous pour y accéder.</p>'
				. '<p><a class="ag-jr-btn" href="' . esc_url( $login ) . '">Se connecter</a></p>'
				. '</div>';
		}

		wp_enqueue_style( 'ag-jr-front' );
		wp_enqueue_script( 'ag-jr-dossiers' );
		wp_enqueue_script( 'ag-jr-pwa' );
		$is_admin = current_user_can( 'manage_options' );
		$fields   = array();
		foreach ( $this->dossier_fields() as $k => $def ) {
			$fields[ $k ] = array( 'label' => $def[0], 'type' => $def[1] );
		}
		$start = $this->pwa_start_url();
		wp_localize_script( 'ag-jr', 'AGJR', array(
			'ajax'      => admin_url( 'admin-ajax.php' ),
			'print'     => admin_url( 'admin-post.php' ),
			'nonce'     => wp_create_nonce( 'ag_jr' ),
			'sources'   => $this->sources(),
			'fields'    => $fields,
			'ai_on'     => (bool) trim( (string) get_option( 'ag_jr_ai_key', '' ) ),
			'live_on'   => ( trim( (string) get_option( 'ag_jr_piste_id', '' ) ) && trim( (string) get_option( 'ag_jr_piste_secret', '' ) ) ),
			'is_admin'  => $is_admin,
			'pwa'       => array(
				'sw'    => add_query_arg( 'ag_jr_sw', '1', home_url( '/' ) ),
				'scope' => wp_parse_url( $start, PHP_URL_PATH ),
			),
		) );

		$live_on = ( trim( (string) get_option( 'ag_jr_piste_id', '' ) ) && trim( (string) get_option( 'ag_jr_piste_secret', '' ) ) );

		ob_start();
		?>
		<div class="ag-jr ag-jr-front">
			<div class="ag-jr-front-head">
				<div class="ag-jr-brand">⚖️ Espace juridique</div>
				<nav class="ag-jr-nav">
					<button class="ag-jr-navbtn is-active" data-section="recherche">🔎 Recherche</button>
					<button class="ag-jr-navbtn" data-section="dossiers">📁 Mes dossiers</button>
					<?php if ( $is_admin ) : ?><button class="ag-jr-navbtn" data-section="reglages">⚙️ Réglages</button><?php endif; ?>
					<button class="ag-jr-navbtn ag-jr-install" id="ag-jr-install" hidden>📲 Installer l'app</button>
				</nav>
			</div>

			<!-- ===== RECHERCHE ===== -->
			<section class="ag-jr-section is-active" data-section="recherche">
				<div class="ag-jr-searchbar">
					<input type="text" id="ag-jr-q" placeholder="Ex : punaises de lit logement décent, licenciement sans cause réelle, article 1240 du code civil…" />
					<button class="ag-jr-btn ag-jr-btn-go" id="ag-jr-go">Rechercher</button>
				</div>
				<div class="ag-jr-tabs">
					<button class="ag-jr-tab is-active" data-tab="live">🔴 Jurisprudence en direct</button>
					<button class="ag-jr-tab" data-tab="meta">🌐 Toutes les sources</button>
					<button class="ag-jr-tab" data-tab="ai">🤖 Aide à l'analyse</button>
				</div>
				<section class="ag-jr-panel is-active" data-panel="live">
					<?php if ( ! $live_on ) : ?>
						<div class="ag-jr-note">La recherche en direct n'est pas encore activée. En attendant, l'onglet <strong>« Toutes les sources »</strong> fonctionne déjà.<?php if ( $is_admin ) : ?> Activez-la dans <strong>Réglages</strong> (clés Judilibre).<?php endif; ?></div>
					<?php endif; ?>
					<div class="ag-jr-filters">
						<label>Juridiction
							<select id="ag-jr-jur">
								<option value="">Toutes</option>
								<option value="cc">Cour de cassation</option>
								<option value="ca">Cours d'appel</option>
								<option value="tj">Tribunaux judiciaires</option>
							</select>
						</label>
						<label>Trier par
							<select id="ag-jr-sort">
								<option value="score">Pertinence</option>
								<option value="date">Date (récent)</option>
							</select>
						</label>
					</div>
					<div id="ag-jr-results" class="ag-jr-results"><p class="ag-jr-empty">Vos résultats de jurisprudence apparaîtront ici.</p></div>
				</section>
				<section class="ag-jr-panel" data-panel="meta">
					<p class="ag-jr-hint">Cliquez sur une source : elle ouvre la recherche déjà pré-remplie avec vos mots-clés.</p>
					<div id="ag-jr-sources"></div>
				</section>
				<section class="ag-jr-panel" data-panel="ai">
					<?php $this->render_ai_panel(); ?>
				</section>
			</section>

			<!-- ===== DOSSIERS ===== -->
			<section class="ag-jr-section" data-section="dossiers">
				<div class="ag-jr-dossiers">
					<div class="ag-jr-dossiers-bar">
						<button class="ag-jr-btn" id="ag-jr-d-new">+ Nouveau dossier</button>
						<input type="search" id="ag-jr-d-search" placeholder="Filtrer mes dossiers…" />
					</div>
					<div id="ag-jr-d-list" class="ag-jr-d-list"><p class="ag-jr-empty">Chargement…</p></div>
					<div id="ag-jr-d-editor" class="ag-jr-d-editor" hidden></div>
				</div>
			</section>

			<?php if ( $is_admin ) : ?>
			<!-- ===== RÉGLAGES ===== -->
			<section class="ag-jr-section" data-section="reglages">
				<?php $this->render_settings_fields_front(); ?>
			</section>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/** Champs de réglages en façade (mêmes id que l'admin → recherche.js gère l'enregistrement). */
	private function render_settings_fields_front() {
		$g = function ( $k, $d = '' ) {
			return esc_attr( get_option( $k, $d ) );
		};
		?>
		<div class="ag-jr-reglages">
			<h3>🔴 Recherche en direct (Judilibre — gratuit)</h3>
			<p class="ag-jr-hint">Compte sur <a href="https://piste.gouv.fr" target="_blank" rel="noopener">piste.gouv.fr</a> → application abonnée à l'API Judilibre → copiez vos identifiants.</p>
			<label>Client ID (PISTE)<input type="text" id="ag_jr_piste_id" value="<?php echo $g( 'ag_jr_piste_id' ); ?>" /></label>
			<label>Client Secret (PISTE)<input type="password" id="ag_jr_piste_secret" value="<?php echo $g( 'ag_jr_piste_secret' ); ?>" /></label>
			<label>KeyId / clé API <span class="ag-jr-opt">(si demandée)</span><input type="text" id="ag_jr_piste_apikey" value="<?php echo $g( 'ag_jr_piste_apikey' ); ?>" /></label>
			<details>
				<summary>Réglages avancés (Sandbox / URLs)</summary>
				<label>URL OAuth<input type="text" id="ag_jr_piste_oauth" value="<?php echo $g( 'ag_jr_piste_oauth', 'https://oauth.piste.gouv.fr/api/oauth/token' ); ?>" /></label>
				<label>Scope<input type="text" id="ag_jr_piste_scope" value="<?php echo $g( 'ag_jr_piste_scope', 'openid' ); ?>" /></label>
				<label>URL API Judilibre<input type="text" id="ag_jr_judilibre_url" value="<?php echo $g( 'ag_jr_judilibre_url', 'https://api.piste.gouv.fr/cassation/judilibre/v1.0/search' ); ?>" /></label>
				<p class="ag-jr-opt">En Sandbox, préfixez les domaines par <code>sandbox-</code> (oauth + api).</p>
			</details>

			<h3>🤖 Assistant IA (optionnel)</h3>
			<p class="ag-jr-hint">Anthropic Claude recommandé. Clé sur <a href="https://console.anthropic.com" target="_blank" rel="noopener">console.anthropic.com</a>.</p>
			<label>Clé API Anthropic<input type="password" id="ag_jr_ai_key" value="<?php echo $g( 'ag_jr_ai_key' ); ?>" /></label>
			<label>Modèle
				<select id="ag_jr_ai_model">
					<?php
					$cur = get_option( 'ag_jr_ai_model', 'claude-sonnet-4-6' );
					foreach ( array(
						'claude-sonnet-4-6' => 'Claude Sonnet 4.6 (rapide, économique)',
						'claude-opus-4-8'   => 'Claude Opus 4.8 (le plus puissant)',
						'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (le moins cher)',
					) as $val => $lbl ) {
						echo '<option value="' . esc_attr( $val ) . '" ' . selected( $cur, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
					}
					?>
				</select>
			</label>
			<p><button class="ag-jr-btn ag-jr-btn-go" id="ag-jr-save">💾 Enregistrer</button> <span id="ag-jr-save-msg"></span></p>
			<p class="ag-jr-disclaimer">🔒 Les clés restent sur votre site. Cet outil assiste la recherche ; l'avocat vérifie toujours les sources primaires.</p>
		</div>
		<?php
	}

	/* ----- CRUD dossiers en façade ----- */
	public function ajax_dossier_list() {
		$this->check_nonce();
		$posts = get_posts( array(
			'post_type'      => self::CPT_DOSSIER,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 200,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );
		$out = array();
		foreach ( $posts as $p ) {
			$out[] = array(
				'id'      => $p->ID,
				'title'   => $p->post_title,
				'domaine' => get_post_meta( $p->ID, '_ag_jr_domaine', true ),
				'date'    => get_the_modified_date( 'd/m/Y', $p ),
			);
		}
		wp_send_json_success( $out );
	}

	public function ajax_dossier_get() {
		$this->check_nonce();
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$p  = get_post( $id );
		if ( ! $p || $p->post_type !== self::CPT_DOSSIER ) {
			wp_send_json_error( array( 'message' => 'Dossier introuvable.' ) );
		}
		$vals = array();
		foreach ( array_keys( $this->dossier_fields() ) as $k ) {
			$vals[ $k ] = get_post_meta( $id, '_ag_jr_' . $k, true );
		}
		wp_send_json_success( array( 'id' => $id, 'title' => $p->post_title, 'fields' => $vals ) );
	}

	public function ajax_dossier_save_front() {
		$this->check_nonce();
		$id    = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		if ( '' === $title ) {
			$title = 'Dossier sans titre';
		}
		$data = array( 'post_type' => self::CPT_DOSSIER, 'post_status' => 'publish', 'post_title' => $title );
		if ( $id ) {
			$data['ID'] = $id;
			$id = wp_update_post( $data, true );
		} else {
			$id = wp_insert_post( $data, true );
		}
		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}
		foreach ( array_keys( $this->dossier_fields() ) as $k ) {
			if ( isset( $_POST[ 'f_' . $k ] ) ) {
				update_post_meta( $id, '_ag_jr_' . $k, sanitize_textarea_field( wp_unslash( $_POST[ 'f_' . $k ] ) ) );
			}
		}
		$print = wp_nonce_url( admin_url( 'admin-post.php?action=ag_jr_print&id=' . $id ), 'ag_jr_print_' . $id );
		wp_send_json_success( array( 'id' => $id, 'print' => $print ) );
	}

	public function ajax_dossier_delete() {
		$this->check_nonce();
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$p  = get_post( $id );
		if ( ! $p || $p->post_type !== self::CPT_DOSSIER ) {
			wp_send_json_error( array( 'message' => 'Dossier introuvable.' ) );
		}
		wp_trash_post( $id );
		wp_send_json_success( array( 'id' => $id ) );
	}

	/* ============================================================
	 *  PWA — application installable (iOS/Android, hors store)
	 * ============================================================ */

	/** Sert le manifeste et le service worker via ?ag_jr_manifest / ?ag_jr_sw. */
	public function pwa_endpoints() {
		if ( isset( $_GET['ag_jr_manifest'] ) ) {
			$start = $this->pwa_start_url();
			$ic    = AG_JR_URL . 'assets/icons/';
			$m = array(
				'name'             => 'Espace juridique',
				'short_name'       => 'Juridique',
				'description'      => 'Recherche et analyse juridique du cabinet.',
				'lang'             => 'fr',
				'start_url'        => $start,
				'scope'            => wp_parse_url( $start, PHP_URL_PATH ),
				'display'          => 'standalone',
				'orientation'      => 'portrait',
				'background_color' => '#131826',
				'theme_color'      => '#131826',
				'icons'            => array(
					array( 'src' => $ic . 'icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
					array( 'src' => $ic . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
					array( 'src' => $ic . 'icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
				),
			);
			header( 'Content-Type: application/manifest+json; charset=utf-8' );
			echo wp_json_encode( $m );
			exit;
		}
		if ( isset( $_GET['ag_jr_sw'] ) ) {
			$shell = wp_json_encode( array(
				AG_JR_URL . 'assets/recherche.css',
				AG_JR_URL . 'assets/front.css',
				AG_JR_URL . 'assets/recherche.js',
				AG_JR_URL . 'assets/front-dossiers.js',
				AG_JR_URL . 'assets/pwa.js',
				AG_JR_URL . 'assets/icons/icon-192.png',
			) );
			$start = wp_json_encode( $this->pwa_start_url() );
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Service-Worker-Allowed: /' );
			echo "var CACHE='ag-jr-pwa-" . AG_JR_VERSION . "';var SHELL=" . $shell . ";var START=" . $start . ";\n";
			echo <<<'SW'
self.addEventListener('install', function(e){ self.skipWaiting(); e.waitUntil(caches.open(CACHE).then(function(c){ return c.addAll(SHELL).catch(function(){}); })); });
self.addEventListener('activate', function(e){ self.clients.claim(); e.waitUntil(caches.keys().then(function(ks){ return Promise.all(ks.map(function(k){ return k!==CACHE ? caches.delete(k) : null; })); })); });
self.addEventListener('fetch', function(e){
	var req = e.request;
	if (req.method !== 'GET') { return; }
	var u = new URL(req.url);
	if (u.pathname.indexOf('/wp-admin') >= 0 || u.pathname.indexOf('/wp-login') >= 0 || u.pathname.indexOf('admin-ajax') >= 0 || u.search.indexOf('ag_jr_sw') >= 0 || u.search.indexOf('ag_jr_manifest') >= 0) { return; }
	if (req.mode === 'navigate') {
		e.respondWith(fetch(req).then(function(r){ var cp = r.clone(); caches.open(CACHE).then(function(c){ c.put(req, cp); }); return r; }).catch(function(){ return caches.match(req).then(function(m){ return m || caches.match(START); }); }));
		return;
	}
	if (u.origin === location.origin) {
		e.respondWith(caches.match(req).then(function(m){ var f = fetch(req).then(function(r){ if (r && r.status === 200) { var cp = r.clone(); caches.open(CACHE).then(function(c){ c.put(req, cp); }); } return r; }).catch(function(){ return m; }); return m || f; }));
	}
});
SW;
			exit;
		}
	}

	/** Balises PWA dans le <head> de la page Espace juridique. */
	public function pwa_head() {
		if ( ! is_singular() ) {
			return;
		}
		$post = get_post();
		if ( ! $post || ! has_shortcode( $post->post_content, 'ag_juridique' ) ) {
			return;
		}
		echo "\n" . '<link rel="manifest" href="' . esc_url( add_query_arg( 'ag_jr_manifest', '1', home_url( '/' ) ) ) . '">' . "\n";
		echo '<meta name="theme-color" content="#131826">' . "\n";
		echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
		echo '<meta name="apple-mobile-web-app-title" content="Juridique">' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( AG_JR_URL . 'assets/icons/apple-touch-180.png' ) . '">' . "\n";
	}
}
