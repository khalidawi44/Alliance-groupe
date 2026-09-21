<?php
/**
 * LA PROVENANCE — savoir d'où vient chaque contact et ce qu'il a fait
 * ---------------------------------------------------------------------------
 * Le problème constaté : un « intéressé a ouvert un compte » et Fabrice n'a
 * AUCUN moyen de savoir d'où il vient ni ce qu'il a fait. Le tracker Visiteurs
 * enregistrait bien le parcours (pages, clics) mais jamais la PROVENANCE
 * (Google ? un lien ambassadeur ? une campagne ?), et `ag_create_member` ne
 * reliait la création de compte à rien.
 *
 * Ce module comble les deux :
 *   1. À la première visite, il capte l'origine — référent, paramètres UTM,
 *      lien ambassadeur (?ref=) ou parrain (?parrain=), page d'arrivée. En
 *      « premier contact » (first-touch) : on garde la toute première source,
 *      celle qui a vraiment amené la personne.
 *   2. À la création d'un compte, il attache cette provenance ET le parcours à
 *      l'inscrit (métadonnée `ag_prov`), et marque la visite « compte créé ».
 *   3. Il donne un RÉSUMÉ lisible : « Venu de …, arrivé sur …, a fait …,
 *      compte créé le … » — visible sur la fiche du membre.
 *
 * RGPD : on ne capte que si le cookie de mesure existe déjà (il n'est posé
 * qu'avec le consentement analytics, via ag-visiteurs). Pas de consentement,
 * pas de cookie, pas de captation. Aucune donnée nouvelle n'est collectée
 * hors de ce cadre déjà accepté.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_PROV_VER' ) ) { define( 'AG_PROV_VER', '1.0.0' ); }

/* ── 1. Capter l'origine à la première visite ────────────────────────── */

add_action( 'wp', function () {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) { return; }
	if ( ! function_exists( 'ag_visitor_id' ) ) { return; }
	$vid = ag_visitor_id();
	if ( '' === $vid ) { return; } // pas de cookie = pas de consentement = on ne capte rien

	$visits = (array) get_option( 'ag_visits', array() );
	if ( ! isset( $visits[ $vid ] ) ) { return; } // la visite sera créée par le tracker JS

	// First-touch : si la provenance est déjà notée, on n'y touche plus.
	if ( ! empty( $visits[ $vid ]['prov'] ) ) { return; }

	$g = function ( $k ) {
		return isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : '';
	};
	$ref_http = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	$ref_host = $ref_http ? (string) wp_parse_url( $ref_http, PHP_URL_HOST ) : '';
	$self     = (string) wp_parse_url( home_url(), PHP_URL_HOST );

	// Canal résumé, en clair.
	$canal = 'Direct';
	if ( '' !== $g( 'utm_source' ) )                    { $canal = ucfirst( $g( 'utm_source' ) ) . ( $g( 'utm_medium' ) ? ' (' . $g( 'utm_medium' ) . ')' : '' ); }
	elseif ( '' !== $g( 'ref' ) )                        { $canal = 'Lien ambassadeur'; }
	elseif ( '' !== $g( 'parrain' ) )                    { $canal = 'Parrainage'; }
	elseif ( $ref_host && $ref_host !== $self ) {
		if ( false !== stripos( $ref_host, 'google' ) )      { $canal = 'Google'; }
		elseif ( false !== stripos( $ref_host, 'facebook' ) || false !== stripos( $ref_host, 'fb.' ) ) { $canal = 'Facebook'; }
		elseif ( false !== stripos( $ref_host, 'instagram' ) ) { $canal = 'Instagram'; }
		elseif ( false !== stripos( $ref_host, 'linkedin' ) )  { $canal = 'LinkedIn'; }
		elseif ( false !== stripos( $ref_host, 't.co' ) || false !== stripos( $ref_host, 'twitter' ) || false !== stripos( $ref_host, 'x.com' ) ) { $canal = 'X/Twitter'; }
		else                                                   { $canal = $ref_host; }
	}

	$visits[ $vid ]['prov'] = array(
		'canal'     => $canal,
		'utm_source'=> $g( 'utm_source' ),
		'utm_medium'=> $g( 'utm_medium' ),
		'utm_camp'  => $g( 'utm_campaign' ),
		'ref'       => $g( 'ref' ),        // ambassadeur
		'parrain'   => $g( 'parrain' ),
		'referent'  => $ref_host,
		'arrivee'   => mb_substr( (string) ( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '' ), 0, 160 ),
		'ts'        => time(),
	);
	update_option( 'ag_visits', $visits, false );
}, 20 );

/* ── 2. Relier la création de compte au parcours ─────────────────────── */

add_action( 'user_register', function ( $user_id ) {
	if ( ! function_exists( 'ag_visitor_id' ) ) { return; }
	$vid = ag_visitor_id();
	$prov = array( 'compte_le' => time() );

	if ( '' !== $vid ) {
		$visits = (array) get_option( 'ag_visits', array() );
		if ( isset( $visits[ $vid ] ) ) {
			$prov['vid']      = $vid;
			$prov['origine']  = $visits[ $vid ]['prov'] ?? array();
			$prov['pages']    = count( (array) ( $visits[ $vid ]['ev'] ?? array() ) );
			$prov['premiere'] = (int) ( $visits[ $vid ]['first'] ?? 0 );
			// On marque la visite : ce parcours a abouti à un compte.
			$visits[ $vid ]['account'] = (int) $user_id;
			update_option( 'ag_visits', $visits, false );
		}
	}
	update_user_meta( $user_id, 'ag_prov', $prov );
}, 20 );

/* ── 3. Le résumé lisible ────────────────────────────────────────────── */

if ( ! function_exists( 'ag_prov_resume' ) ) {
	/**
	 * Résumé en clair de la provenance d'un membre. Retourne '' si on ne sait
	 * rien (compte créé avant que cette capture existe, ou sans consentement).
	 */
	function ag_prov_resume( $user_id ) {
		$p = get_user_meta( $user_id, 'ag_prov', true );
		if ( ! is_array( $p ) || empty( $p ) ) { return ''; }

		$o = is_array( $p['origine'] ?? null ) ? $p['origine'] : array();
		$bits = array();
		$bits[] = 'Venu de : ' . ( ! empty( $o['canal'] ) ? $o['canal'] : 'origine inconnue' );
		if ( ! empty( $o['ref'] ) )     { $bits[] = 'lien ambassadeur ' . $o['ref']; }
		if ( ! empty( $o['parrain'] ) ) { $bits[] = 'parrain ' . $o['parrain']; }
		if ( ! empty( $o['utm_camp'] ) ){ $bits[] = 'campagne ' . $o['utm_camp']; }
		if ( ! empty( $o['arrivee'] ) ) { $bits[] = 'arrivé sur ' . $o['arrivee']; }
		if ( ! empty( $p['pages'] ) )   { $bits[] = (int) $p['pages'] . ' page(s) vues avant de s\'inscrire'; }
		if ( ! empty( $p['compte_le'] ) ) { $bits[] = 'compte créé le ' . date_i18n( 'd/m/Y à H:i', (int) $p['compte_le'] ); }
		return implode( ' · ', $bits );
	}
}

/* ── 4. Afficher la provenance sur la fiche du membre (admin) ────────── */

add_action( 'show_user_profile', 'ag_prov_profil' );
add_action( 'edit_user_profile', 'ag_prov_profil' );
if ( ! function_exists( 'ag_prov_profil' ) ) {
	function ag_prov_profil( $user ) {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$resume = ag_prov_resume( $user->ID );
		echo '<h2>Provenance (Alliance Groupe)</h2><table class="form-table"><tr><th>D\'où vient ce compte</th><td>';
		echo $resume
			? '<strong>' . esc_html( $resume ) . '</strong>'
			: '<em>Inconnue — compte créé avant la capture de provenance, ou sans consentement mesure d\'audience.</em>';
		echo '</td></tr></table>';
	}
}

/* ── 5. Écran : les comptes récents et leur origine ──────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'D\'où viennent les comptes', '🧭 Origine des comptes',
		'manage_options', 'ag-provenance', 'ag_prov_ecran'
	);
}, 35 );

if ( ! function_exists( 'ag_prov_ecran' ) ) {
	function ag_prov_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$users = get_users( array(
			'role__in' => array( 'ag_client', 'ag_ambassadeur' ),
			'number'   => 60,
			'orderby'  => 'registered',
			'order'    => 'DESC',
		) );
		?>
		<div class="wrap">
			<h1>🧭 Origine des comptes</h1>
			<p class="description" style="max-width:58em">
				D'où vient chaque personne qui a ouvert un compte, et ce qu'elle a fait avant. La provenance
				est captée à la première visite (référent, campagne, lien ambassadeur), dans le cadre du
				consentement mesure d'audience déjà accepté. Les comptes créés <strong>avant</strong> cette
				capture apparaissent « origine inconnue » — c'est normal, on ne réécrit pas le passé.
			</p>
			<table class="widefat striped" style="max-width:80em">
				<thead><tr><th>Membre</th><th>Rôle</th><th>Inscrit le</th><th>Provenance</th></tr></thead>
				<tbody>
				<?php if ( ! $users ) : ?>
					<tr><td colspan="4">Aucun compte membre pour l'instant.</td></tr>
				<?php else : foreach ( $users as $u ) :
					$resume = ag_prov_resume( $u->ID );
					$role   = in_array( 'ag_ambassadeur', (array) $u->roles, true ) ? 'Ambassadeur' : 'Client';
					?>
					<tr>
						<td><strong><?php echo esc_html( $u->display_name ); ?></strong><br><small><?php echo esc_html( $u->user_email ); ?></small></td>
						<td><?php echo esc_html( $role ); ?></td>
						<td><?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $u->user_registered ) ) ); ?></td>
						<td><?php echo $resume ? esc_html( $resume ) : '<em style="color:#8a8a94">origine inconnue</em>'; ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
