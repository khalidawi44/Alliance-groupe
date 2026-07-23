<?php
/**
 * AG — Veille « Appels d'offres publics » (marchés publics).
 *
 * Source : BOAMP en open data (Opendatasoft v2.1), API publique GRATUITE, sans clé.
 * On récupère les marchés OUVERTS (date limite de réponse encore à venir) qui
 * concernent NOS métiers : création / refonte de site, hébergement, maintenance,
 * cybersécurité, audit de sécurité. C'est une source de leads « chauds » :
 * l'acheteur a déjà un budget voté et cherche activement un prestataire.
 *
 * Page admin : Prospection → « 📢 Appels d'offres publics ».
 * Filtres (catégorie + département), lien direct pour répondre (avis BOAMP),
 * et bouton « + Suivre » qui ajoute l'acheteur au CRM Prospection.
 *
 * 100% légal : données publiques officielles republiées en open data (licence ouverte).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Catégories de recherche (chaque catégorie = liste de mots-clés « objet like ») ── */
if ( ! function_exists( 'ag_boamp_categories' ) ) {
	function ag_boamp_categories() {
		return array(
			'web' => array(
				'label' => 'Sites web (création / refonte)',
				'kw'    => array( 'site internet', 'site web', 'création de site', 'refonte du site', 'refonte de site', 'refonte du site internet', 'conception de site', 'développement web', 'portail internet' ),
			),
			'cyber' => array(
				'label' => 'Cybersécurité / audit sécurité',
				'kw'    => array( 'cybersécurité', 'cyber sécurité', 'sécurité informatique', 'sécurité des systèmes', 'audit de sécurité', 'audit sécurité', 'pentest', 'test d\'intrusion' ),
			),
			'maint' => array(
				'label' => 'Hébergement / maintenance / TMA',
				'kw'    => array( 'hébergement du site', 'hébergement et maintenance', 'maintenance du site', 'tierce maintenance applicative', 'infogérance' ),
			),
			'com' => array(
				'label' => 'Communication digitale',
				'kw'    => array( 'communication digitale', 'stratégie digitale', 'référencement', 'community management', 'identité visuelle' ),
			),
		);
	}
}

/* ── Construit la clause ODSQL « where » à partir des catégories choisies ── */
if ( ! function_exists( 'ag_boamp_where' ) ) {
	function ag_boamp_where( $cats, $dept = '' ) {
		$cats_def = ag_boamp_categories();
		$kw = array();
		foreach ( (array) $cats as $c ) {
			if ( isset( $cats_def[ $c ] ) ) {
				foreach ( $cats_def[ $c ]['kw'] as $w ) { $kw[] = $w; }
			}
		}
		if ( ! $kw ) { // par défaut : toutes les catégories
			foreach ( $cats_def as $def ) { foreach ( $def['kw'] as $w ) { $kw[] = $w; } }
		}
		$ors = array();
		foreach ( array_unique( $kw ) as $w ) {
			$w = str_replace( '"', '', $w );
			$ors[] = 'objet like "' . $w . '"';
		}
		$where = '(' . implode( ' or ', $ors ) . ') and datelimitereponse > now()';
		$dept = preg_replace( '/[^0-9A-Za-z]/', '', (string) $dept );
		if ( '' !== $dept ) {
			// code_departement est un tableau : le test « like » couvre l'appartenance.
			$where .= ' and code_departement like "' . $dept . '"';
		}
		return $where;
	}
}

/* ── Appel API BOAMP (Opendatasoft v2.1), avec cache transient 3 h ── */
if ( ! function_exists( 'ag_boamp_fetch' ) ) {
	function ag_boamp_fetch( $cats = array(), $dept = '', $limit = 60 ) {
		$limit = max( 1, min( 100, (int) $limit ) );
		$where = ag_boamp_where( $cats, $dept );
		$ck    = 'ag_boamp_' . md5( $where . '|' . $limit );
		$cached = get_transient( $ck );
		if ( false !== $cached ) { return $cached; }

		$base = 'https://boamp-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/boamp/records';
		$url  = add_query_arg( array(
			'where'    => $where,
			'order_by' => 'datelimitereponse asc',
			'limit'    => $limit,
			'select'   => 'idweb,objet,nomacheteur,code_departement,dateparution,datelimitereponse,url_avis,famille_libelle,procedure_libelle',
		), $base );

		$res = wp_remote_get( $url, array( 'timeout' => 15, 'headers' => array( 'Accept' => 'application/json' ) ) );
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			$out = array( 'ok' => false, 'total' => 0, 'items' => array(), 'error' => is_wp_error( $res ) ? $res->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $res ) );
			set_transient( $ck, $out, 15 * MINUTE_IN_SECONDS );
			return $out;
		}
		$json  = json_decode( wp_remote_retrieve_body( $res ), true );
		$items = array();
		foreach ( (array) ( $json['results'] ?? array() ) as $r ) {
			$dl = $r['datelimitereponse'] ?? '';
			$ts = $dl ? strtotime( $dl ) : 0;
			$items[] = array(
				'id'        => (string) ( $r['idweb'] ?? '' ),
				'objet'     => (string) ( $r['objet'] ?? '' ),
				'acheteur'  => (string) ( $r['nomacheteur'] ?? '' ),
				'dept'      => implode( ', ', array_filter( (array) ( $r['code_departement'] ?? array() ) ) ),
				'parution'  => (string) ( $r['dateparution'] ?? '' ),
				'limite_ts' => $ts,
				'limite'    => $ts ? date_i18n( 'd/m/Y', $ts ) : '',
				'jours'     => $ts ? max( 0, (int) ceil( ( $ts - time() ) / DAY_IN_SECONDS ) ) : null,
				'url'       => (string) ( $r['url_avis'] ?? '' ),
				'famille'   => (string) ( $r['famille_libelle'] ?? '' ),
				'procedure' => (string) ( $r['procedure_libelle'] ?? '' ),
			);
		}
		$out = array( 'ok' => true, 'total' => (int) ( $json['total_count'] ?? count( $items ) ), 'items' => $items );
		set_transient( $ck, $out, 3 * HOUR_IN_SECONDS );
		return $out;
	}
}

/* ─────────────────────────────────────────────────────────────────────────
 *  DÉTAIL D'UN MARCHÉ (fiche complète) — parse le blob `donnees` du BOAMP
 *  pour en extraire : contact acheteur, URL du profil d'acheteur (là où on
 *  DÉPOSE l'offre), référence du marché, délai de validité, forme juridique.
 * ───────────────────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_boamp_dig' ) ) {
	/* Cherche récursivement la 1re valeur (scalaire non vide) dont la clé == $needle. */
	function ag_boamp_dig( $node, $needle ) {
		if ( is_array( $node ) ) {
			foreach ( $node as $k => $v ) {
				if ( $k === $needle && ! is_array( $v ) && '' !== (string) $v ) return (string) $v;
				$found = ag_boamp_dig( $v, $needle );
				if ( '' !== $found ) return $found;
			}
		}
		return '';
	}
}

if ( ! function_exists( 'ag_boamp_detail' ) ) {
	function ag_boamp_detail( $idweb ) {
		$idweb = preg_replace( '/[^0-9A-Za-z\-]/', '', (string) $idweb );
		if ( '' === $idweb ) return null;
		$ck = 'ag_boampd_' . md5( $idweb );
		$c  = get_transient( $ck );
		if ( false !== $c ) return $c;

		$base = 'https://boamp-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/boamp/records';
		$url  = add_query_arg( array( 'where' => 'idweb="' . $idweb . '"', 'limit' => 1 ), $base );
		$res  = wp_remote_get( $url, array( 'timeout' => 15, 'headers' => array( 'Accept' => 'application/json' ) ) );
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) return null;
		$json = json_decode( wp_remote_retrieve_body( $res ), true );
		$r    = $json['results'][0] ?? null;
		if ( ! $r ) return null;

		$don = $r['donnees'] ?? '';
		if ( is_string( $don ) ) { $d = json_decode( $don, true ); if ( is_array( $d ) ) $don = $d; }

		$ts = ! empty( $r['datelimitereponse'] ) ? strtotime( $r['datelimitereponse'] ) : 0;
		$out = array(
			'id'        => $idweb,
			'objet'     => (string) ( $r['objet'] ?? ag_boamp_dig( $don, 'objet' ) ),
			'acheteur'  => (string) ( $r['nomacheteur'] ?? ag_boamp_dig( $don, 'acheteurPublic' ) ),
			'dept'      => implode( ', ', array_filter( (array) ( $r['code_departement'] ?? array() ) ) ),
			'limite_ts' => $ts,
			'limite'    => $ts ? date_i18n( 'd/m/Y à H\hi', $ts ) : '',
			'url_avis'  => (string) ( $r['url_avis'] ?? '' ),
			'famille'   => (string) ( $r['famille_libelle'] ?? '' ),
			'procedure' => (string) ( $r['procedure_libelle'] ?? '' ),
			// Depuis le blob « donnees » :
			'profil'    => ag_boamp_dig( $don, 'urlProfilAcheteur' ),
			'ref'       => ag_boamp_dig( $don, 'idMarche' ),
			'validite'  => ag_boamp_dig( $don, 'nbJours' ),
			'forme'     => ag_boamp_dig( $don, 'formeJuridique' ),
			'ac_civ'    => ag_boamp_dig( $don, 'civilite' ),
			'ac_nom'    => ag_boamp_dig( $don, 'nom' ),
			'ac_pren'   => ag_boamp_dig( $don, 'pren' ),
			'ac_fonc'   => ag_boamp_dig( $don, 'fonc' ),
			'ac_ville'  => ag_boamp_dig( $don, 'ville' ),
			'ac_cp'     => ag_boamp_dig( $don, 'cp' ),
			'ac_voie'   => ag_boamp_dig( $don, 'nomvoie' ),
		);
		if ( '' === $out['profil'] ) $out['profil'] = $out['url_avis']; // repli
		set_transient( $ck, $out, 6 * HOUR_IN_SECONDS );
		return $out;
	}
}

/* ─────────────────────────────────────────────────────────────────────────
 *  IDENTITÉ CANDIDAT (Alliance Groupe) — pré-remplit le dossier.
 *  Modifiable dans « 📢 Appels d'offres → onglet Identité candidat ».
 * ───────────────────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_cand_opt' ) ) {
	function ag_cand_opt( $key ) {
		$def = array(
			'raison'   => 'DOUCET FABRICE',                 // raison sociale légale
			'enseigne' => 'Alliance Groupe',                // nom commercial
			'forme'    => 'Entreprise individuelle',        // forme juridique (à vérifier)
			'siren'    => '513593921',
			'siret'    => '',                               // 14 chiffres (à compléter)
			'ape'      => '',                               // code APE/NAF (à compléter)
			'tva'      => '',                               // n° TVA intracom (si applicable)
			'repr'     => 'Fabrice DOUCET',                 // représentant légal
			'repr_qual'=> 'Gérant',                          // qualité du signataire
		);
		$v = get_option( 'ag_cand_' . $key, '' );
		if ( '' !== $v && false !== $v ) return $v;
		// Reprend l'adresse/tel/email depuis le NAP si dispo.
		if ( function_exists( 'ag_nap_opt' ) ) {
			$map = array( 'street' => 'street', 'zip' => 'zip', 'city' => 'city', 'phone' => 'phone', 'email' => 'email' );
			if ( isset( $map[ $key ] ) ) { $n = ag_nap_opt( $map[ $key ] ); if ( '' !== $n ) return $n; }
		}
		return $def[ $key ] ?? '';
	}
}

/* Détecte la catégorie métier d'un marché (pour adapter le mémoire technique). */
if ( ! function_exists( 'ag_cand_metier' ) ) {
	function ag_cand_metier( $objet ) {
		$o = function_exists( 'mb_strtolower' ) ? mb_strtolower( $objet ) : strtolower( $objet );
		if ( false !== strpos( $o, 'cyber' ) || false !== strpos( $o, 'sécurit' ) || false !== strpos( $o, 'securit' ) || false !== strpos( $o, 'intrusion' ) || false !== strpos( $o, 'pentest' ) ) return 'cyber';
		if ( false !== strpos( $o, 'maintenance' ) || false !== strpos( $o, 'hébergement' ) || false !== strpos( $o, 'hebergement' ) || false !== strpos( $o, 'infogérance' ) || false !== strpos( $o, 'tma' ) ) return 'maint';
		return 'web';
	}
}

/* ── Suivi des candidatures : statut par marché (option ag_candidatures) ── */
if ( ! function_exists( 'ag_cand_status_get' ) ) {
	function ag_cand_status_get( $idweb ) {
		$all = (array) get_option( 'ag_candidatures', array() );
		return $all[ $idweb ] ?? null;
	}
}
add_action( 'wp_ajax_ag_cand_status', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_boamp' ) ) wp_send_json_error();
	$id  = preg_replace( '/[^0-9A-Za-z\-]/', '', (string) ( $_POST['id'] ?? '' ) );
	$st  = sanitize_key( $_POST['statut'] ?? '' );
	$ok  = array( 'a_faire', 'pret', 'depose', 'gagne', 'perdu' );
	if ( '' === $id || ! in_array( $st, $ok, true ) ) wp_send_json_error();
	$all = (array) get_option( 'ag_candidatures', array() );
	$all[ $id ] = array(
		'statut' => $st,
		'ts'     => time(),
		'objet'  => sanitize_text_field( wp_unslash( $_POST['objet'] ?? ( $all[ $id ]['objet'] ?? '' ) ) ),
		'acheteur' => sanitize_text_field( wp_unslash( $_POST['acheteur'] ?? ( $all[ $id ]['acheteur'] ?? '' ) ) ),
	);
	update_option( 'ag_candidatures', $all );
	wp_send_json_success( array( 'statut' => $st ) );
} );

/* ── Renderer autonome du DOSSIER DE CANDIDATURE : ?ag_candidature=1&id=<idweb> ── */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['ag_candidature'] ) ) return;
	if ( ! current_user_can( 'manage_options' ) && ! ( function_exists( 'ag_espace_member_kind' ) && 'ambassadeur' === ag_espace_member_kind() ) ) {
		auth_redirect();
	}
	$id = preg_replace( '/[^0-9A-Za-z\-]/', '', (string) ( $_GET['id'] ?? '' ) );
	ag_candidature_render( $id );
	exit;
} );

/* ── Menu admin ── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Appels d\'offres publics', '📢 Appels d\'offres', 'manage_options', 'ag-appels-offres', 'ag_appels_offres_render' );
	add_submenu_page( 'ag-prospects', 'Mes candidatures', '🗂️ Mes candidatures', 'manage_options', 'ag-candidatures', 'ag_candidatures_render' );
} );

/* Enregistre les réglages « identité candidat ». */
add_action( 'admin_init', function () {
	foreach ( array( 'raison', 'enseigne', 'forme', 'siren', 'siret', 'ape', 'tva', 'repr', 'repr_qual', 'street', 'zip', 'city', 'phone', 'email' ) as $k ) {
		register_setting( 'ag_cand_group', 'ag_cand_' . $k );
	}
} );

/* ── Bouton « + Suivre » : ajoute l'acheteur public au CRM Prospection ── */
add_action( 'wp_ajax_ag_boamp_follow', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_boamp' ) ) wp_send_json_error();
	if ( ! function_exists( 'ag_prospect_add_record' ) ) wp_send_json_error( array( 'msg' => 'CRM indisponible' ) );
	$name  = sanitize_text_field( wp_unslash( $_POST['acheteur'] ?? '' ) );
	$objet = sanitize_text_field( wp_unslash( $_POST['objet'] ?? '' ) );
	$url   = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$dept  = sanitize_text_field( wp_unslash( $_POST['dept'] ?? '' ) );
	$lim   = sanitize_text_field( wp_unslash( $_POST['limite'] ?? '' ) );
	if ( '' === $name ) wp_send_json_error( array( 'msg' => 'Acheteur manquant' ) );
	$ok = ag_prospect_add_record( array(
		'name'   => $name,
		'type'   => 'Marché public',
		'city'   => $dept,
		'status' => 'nouveau',
		'source' => 'appel-offres',
		'notes'  => 'Appel d\'offres BOAMP — ' . $objet . ( $lim ? ' (limite ' . $lim . ')' : '' ) . ' — ' . $url,
	) );
	if ( ! $ok ) wp_send_json_error( array( 'msg' => 'Déjà suivi ou ignoré' ) );
	wp_send_json_success( array( 'msg' => 'Ajouté au CRM' ) );
} );

/* ── AJAX appli ambassadeur : liste des appels d'offres (connectés) ── */
add_action( 'wp_ajax_ag_app_boamp', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_amb_prospect' ) ) wp_send_json_error();
	$cats = isset( $_POST['cat'] ) ? array_map( 'sanitize_key', (array) $_POST['cat'] ) : array();
	$dept = sanitize_text_field( wp_unslash( $_POST['dept'] ?? '' ) );
	$data = ag_boamp_fetch( $cats, $dept, 40 );
	$out  = array();
	foreach ( $data['items'] as $it ) {
		$out[] = array(
			'objet'  => $it['objet'],
			'acheteur' => $it['acheteur'],
			'dept'   => $it['dept'],
			'limite' => $it['limite'],
			'jours'  => $it['jours'],
			'url'    => $it['url'],
			'cand'   => home_url( '/?ag_candidature=1&id=' . rawurlencode( $it['id'] ) ),
		);
	}
	wp_send_json_success( array( 'ok' => $data['ok'], 'total' => $data['total'], 'items' => $out ) );
} );

/* ── Rendu de la page admin ── */
if ( ! function_exists( 'ag_appels_offres_render' ) ) {
	function ag_appels_offres_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$cats_def = ag_boamp_categories();
		$sel  = isset( $_GET['cat'] ) ? array_map( 'sanitize_key', (array) $_GET['cat'] ) : array_keys( $cats_def );
		$dept = isset( $_GET['dept'] ) ? preg_replace( '/[^0-9A-Za-z]/', '', (string) $_GET['dept'] ) : '';
		$data = ag_boamp_fetch( $sel, $dept, 80 );
		$nonce = wp_create_nonce( 'ag_boamp' );
		?>
		<div class="wrap">
			<h1>📢 Appels d'offres publics</h1>
			<p style="max-width:820px;color:#444;">Marchés publics <strong>ouverts</strong> (date limite encore à venir) qui correspondent à nos métiers — source officielle <strong>BOAMP</strong> (open data). L'acheteur a déjà un budget : c'est un lead chaud. Clique sur <em>Répondre / voir l'avis</em> pour lire le cahier des charges et déposer une offre, ou <em>+ Suivre</em> pour l'ajouter au CRM.</p>

			<form method="get" style="background:#fff;border:1px solid #ccd0d4;padding:14px 16px;border-radius:8px;margin:14px 0;">
				<input type="hidden" name="page" value="ag-appels-offres">
				<div style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
					<div>
						<strong style="display:block;margin-bottom:6px;">Catégories</strong>
						<?php foreach ( $cats_def as $k => $def ) : ?>
							<label style="display:inline-block;margin-right:14px;white-space:nowrap;">
								<input type="checkbox" name="cat[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $sel, true ) ); ?>> <?php echo esc_html( $def['label'] ); ?>
							</label>
						<?php endforeach; ?>
					</div>
					<div>
						<strong style="display:block;margin-bottom:6px;">Département</strong>
						<input type="text" name="dept" value="<?php echo esc_attr( $dept ); ?>" placeholder="ex : 44, 75, 2A" style="width:120px;">
					</div>
					<div>
						<button class="button button-primary">Filtrer</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-appels-offres' ) ); ?>" class="button">Réinitialiser</a>
					</div>
				</div>
			</form>

			<?php if ( ! $data['ok'] ) : ?>
				<div class="notice notice-error"><p>Impossible de joindre le BOAMP pour le moment (<?php echo esc_html( $data['error'] ?? 'erreur' ); ?>). Réessaie dans quelques minutes.</p></div>
			<?php else : ?>
				<p style="color:#666;"><strong><?php echo (int) $data['total']; ?></strong> appel(s) d'offres ouvert(s) — <?php echo count( $data['items'] ); ?> affiché(s), les plus urgents en premier.</p>
				<table class="widefat striped" style="margin-top:8px;">
					<thead><tr>
						<th style="width:38%;">Objet</th>
						<th>Acheteur</th>
						<th>Dépt</th>
						<th>Type</th>
						<th>Date limite</th>
						<th style="width:210px;">Actions</th>
					</tr></thead>
					<tbody>
					<?php if ( ! $data['items'] ) : ?>
						<tr><td colspan="6">Aucun appel d'offres ouvert pour ces critères. Élargis les catégories ou vide le département.</td></tr>
					<?php endif; ?>
					<?php foreach ( $data['items'] as $it ) :
						$j = $it['jours'];
						$urg = ( null !== $j && $j <= 7 ) ? 'color:#b32d2e;font-weight:700;' : ( ( null !== $j && $j <= 15 ) ? 'color:#b26a00;font-weight:600;' : 'color:#1e7e34;' );
					?>
						<tr>
							<td><?php echo esc_html( wp_trim_words( $it['objet'], 28, '…' ) ); ?></td>
							<td><strong><?php echo esc_html( $it['acheteur'] ); ?></strong><br><span style="color:#888;font-size:.85em;"><?php echo esc_html( $it['procedure'] ); ?></span></td>
							<td><?php echo esc_html( $it['dept'] ); ?></td>
							<td style="font-size:.85em;color:#666;"><?php echo esc_html( $it['famille'] ); ?></td>
							<td style="<?php echo esc_attr( $urg ); ?>">
								<?php echo esc_html( $it['limite'] ); ?>
								<?php if ( null !== $j ) : ?><br><span style="font-size:.85em;"><?php echo (int) $j; ?> j restant<?php echo $j > 1 ? 's' : ''; ?></span><?php endif; ?>
							</td>
							<td>
								<a href="<?php echo esc_url( home_url( '/?ag_candidature=1&id=' . rawurlencode( $it['id'] ) ) ); ?>" target="_blank" rel="noopener" class="button button-small button-primary" style="background:#7a3fb3;border-color:#652f99;">🗂️ Préparer le dossier</a>
								<?php if ( $it['url'] ) : ?><a href="<?php echo esc_url( $it['url'] ); ?>" target="_blank" rel="noopener" class="button button-small" style="margin-top:4px;">Voir l'avis officiel</a><?php endif; ?>
								<button class="button button-small ag-boamp-follow"
									data-acheteur="<?php echo esc_attr( $it['acheteur'] ); ?>"
									data-objet="<?php echo esc_attr( $it['objet'] ); ?>"
									data-url="<?php echo esc_attr( $it['url'] ); ?>"
									data-dept="<?php echo esc_attr( $it['dept'] ); ?>"
									data-limite="<?php echo esc_attr( $it['limite'] ); ?>"
									style="margin-top:4px;">+ Suivre</button>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<details style="margin-top:22px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:6px 16px;">
				<summary style="cursor:pointer;font-weight:600;padding:8px 0;">🏢 Identité candidat (pré-remplit tes dossiers)</summary>
				<p style="color:#666;max-width:760px;">Ces informations sont injectées automatiquement dans chaque dossier de candidature (lettre de candidature, déclaration du candidat). Vérifie le <strong>SIRET</strong>, le <strong>code APE</strong> et la <strong>forme juridique</strong> avant de déposer.</p>
				<form method="post" action="options.php">
					<?php settings_fields( 'ag_cand_group' ); ?>
					<table class="form-table"><tbody>
					<?php
					$idf = array(
						'raison' => 'Raison sociale (légale)', 'enseigne' => 'Nom commercial / enseigne', 'forme' => 'Forme juridique',
						'siren' => 'SIREN (9 chiffres)', 'siret' => 'SIRET (14 chiffres)', 'ape' => 'Code APE / NAF', 'tva' => 'N° TVA intracom.',
						'repr' => 'Représentant légal (signataire)', 'repr_qual' => 'Qualité du signataire',
						'street' => 'Adresse', 'zip' => 'Code postal', 'city' => 'Ville', 'phone' => 'Téléphone', 'email' => 'Email',
					);
					foreach ( $idf as $k => $lab ) : ?>
						<tr>
							<th scope="row"><label for="ag_cand_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $lab ); ?></label></th>
							<td><input name="ag_cand_<?php echo esc_attr( $k ); ?>" id="ag_cand_<?php echo esc_attr( $k ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( ag_cand_opt( $k ) ); ?>"></td>
						</tr>
					<?php endforeach; ?>
					</tbody></table>
					<?php submit_button( 'Enregistrer l\'identité' ); ?>
				</form>
			</details>
		</div>
		<script>
		(function(){
			var nonce = <?php echo wp_json_encode( $nonce ); ?>;
			var ajax  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			document.addEventListener('click', function(e){
				var b = e.target.closest ? e.target.closest('.ag-boamp-follow') : null;
				if (!b) return;
				e.preventDefault();
				b.disabled = true;
				var d = b.dataset;
				var body = new URLSearchParams();
				body.set('action','ag_boamp_follow');
				body.set('_n', nonce);
				body.set('acheteur', d.acheteur || '');
				body.set('objet', d.objet || '');
				body.set('url', d.url || '');
				body.set('dept', d.dept || '');
				body.set('limite', d.limite || '');
				fetch(ajax, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
					.then(function(r){ return r.json(); })
					.then(function(j){
						b.textContent = (j && j.success) ? '✓ Suivi' : ((j && j.data && j.data.msg) ? j.data.msg : 'Erreur');
						b.style.color = (j && j.success) ? '#1e7e34' : '#b32d2e';
					})
					.catch(function(){ b.textContent = 'Erreur'; b.disabled = false; });
			});
		})();
		</script>
		<?php
	}
}

/* ─────────────────────────────────────────────────────────────────────────
 *  DOSSIER DE CANDIDATURE (généré, imprimable/PDF) — ?ag_candidature=1&id=<idweb>
 * ───────────────────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_cand_metier_memoire' ) ) {
	function ag_cand_metier_memoire( $metier, $enseigne ) {
		if ( 'cyber' === $metier ) {
			return array(
				'titre' => 'Prestations de cybersécurité / audit de sécurité',
				'blocs' => array(
					'Compréhension du besoin' => 'Nous avons pris connaissance du périmètre de la consultation. Notre approche vise à réduire concrètement la surface d\'attaque du pouvoir adjudicateur et à mettre en conformité son système d\'information avec les recommandations de l\'ANSSI et le RGPD.',
					'Méthodologie' => 'Audit en trois temps : (1) cartographie et reconnaissance passive (exposition, en-têtes de sécurité, versions, fuites d\'information) ; (2) tests actifs encadrés par un mandat écrit (recherche de vulnérabilités OWASP Top 10, configuration TLS, contrôle d\'accès) ; (3) restitution priorisée par criticité (CVSS) avec plan de remédiation actionnable.',
					'Livrables' => 'Rapport d\'audit détaillé, synthèse pour la direction, matrice des vulnérabilités priorisées, plan de remédiation avec charges estimées, et réunion de restitution.',
					'Moyens & sécurité des données' => 'Intervention par un référent unique. Données traitées en France, chiffrées, supprimées à l\'issue de la mission. Engagement de confidentialité et clause de non-divulgation.',
					'Délais' => 'Démarrage sous 10 jours ouvrés après notification. Audit initial restitué sous 3 à 4 semaines selon le périmètre.',
				),
			);
		}
		if ( 'maint' === $metier ) {
			return array(
				'titre' => 'Hébergement, maintenance et TMA du site',
				'blocs' => array(
					'Compréhension du besoin' => 'Assurer la disponibilité, la sécurité et l\'évolution du site du pouvoir adjudicateur, avec un interlocuteur réactif et des engagements de service clairs.',
					'Hébergement' => 'Hébergement en France (souveraineté des données), sauvegardes quotidiennes automatiques avec restauration testée, certificat TLS, pare-feu applicatif et supervision de disponibilité 24/7.',
					'Maintenance & TMA' => 'Mises à jour de sécurité du CMS et des extensions, corrections de bugs, petites évolutions incluses dans un forfait mensuel, et suivi via un canal de tickets. Engagements de délais de prise en charge selon la criticité.',
					'Réversibilité' => 'À l\'issue du marché, remise complète des accès, des sauvegardes et de la documentation pour garantir la réversibilité sans dépendance.',
					'Délais' => 'Reprise du site et mise en supervision sous 15 jours ouvrés après notification.',
				),
			);
		}
		return array(
			'titre' => 'Refonte / création du site internet',
			'blocs' => array(
				'Compréhension du besoin' => 'Concevoir un site moderne, accessible et performant, à l\'image du pouvoir adjudicateur, centré sur les usages des administrés et facile à administrer en interne.',
				'Méthodologie' => 'Démarche en 5 phases : cadrage & arborescence, conception UX/UI et maquettes, développement, recette & accessibilité, mise en ligne et formation. Points d\'étape réguliers avec le référent du pouvoir adjudicateur.',
				'Accessibilité & conformité' => 'Conception conforme au RGAA (accessibilité), au RGPD (mentions, cookies, registre) et aux obligations légales des sites publics. Éco-conception et performance (temps de chargement, Core Web Vitals).',
				'Hébergement & sécurité' => 'Hébergement souverain en France, HTTPS, sauvegardes, durcissement de sécurité et supervision. Maintenance et TMA proposées en option.',
				'Formation & autonomie' => 'Formation des équipes à l\'administration du site et remise d\'une documentation. Le pouvoir adjudicateur reste pleinement propriétaire et autonome.',
				'Délais' => 'Livraison selon un planning détaillé fourni au démarrage, jalonné et validé avec le pouvoir adjudicateur.',
			),
		);
	}
}

if ( ! function_exists( 'ag_candidature_render' ) ) {
	function ag_candidature_render( $idweb ) {
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		$d = $idweb ? ag_boamp_detail( $idweb ) : null;
		if ( ! $d ) {
			echo '<!DOCTYPE html><meta charset="utf-8"><div style="font-family:sans-serif;max-width:560px;margin:60px auto;text-align:center;"><h1>Marché introuvable</h1><p>Impossible de charger cet appel d\'offres. Reviens à la liste <strong>Prospection → 📢 Appels d\'offres</strong>.</p></div>';
			return;
		}
		$metier = ag_cand_metier( $d['objet'] );
		$mem    = ag_cand_metier_memoire( $metier, ag_cand_opt( 'enseigne' ) );
		$today  = date_i18n( 'd/m/Y' );
		$adr    = trim( ag_cand_opt( 'street' ) . ', ' . ag_cand_opt( 'zip' ) . ' ' . ag_cand_opt( 'city' ), ', ' );
		$acheteur_adr = trim( $d['ac_voie'] . ', ' . $d['ac_cp'] . ' ' . $d['ac_ville'], ', ' );
		$contact = trim( $d['ac_civ'] . ' ' . $d['ac_pren'] . ' ' . $d['ac_nom'] );
		$refs = function_exists( 'ag_portfolio_projects' ) ? array_slice( (array) ag_portfolio_projects(), 0, 6 ) : array();
		?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Dossier de candidature — <?php echo esc_html( wp_trim_words( $d['objet'], 8, '' ) ); ?></title>
		<style>
			*{box-sizing:border-box}
			body{margin:0;background:#eceef1;color:#1a1a1a;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;line-height:1.5;}
			.bar{position:sticky;top:0;z-index:10;background:#0b0b0f;color:#fff;display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 14px;}
			.bar a,.bar button{font:inherit;font-size:.85rem;font-weight:700;border:0;border-radius:8px;padding:9px 13px;cursor:pointer;text-decoration:none;color:#fff;}
			.bar .print{background:#2d7a3f;}.bar .depot{background:#d4b45c;color:#0b0b0f;}.bar .back{background:#333;}
			.bar .st{background:#1f2f45;}
			.bar .sp{flex:1;}
			.doc{max-width:820px;margin:18px auto;background:#fff;padding:46px 54px;box-shadow:0 2px 14px rgba(0,0,0,.08);}
			.doc h1{font-size:1.5rem;margin:0 0 4px;}
			.doc h2{font-size:1.15rem;margin:30px 0 8px;border-bottom:2px solid #d4b45c;padding-bottom:5px;color:#111;}
			.doc h3{font-size:1rem;margin:16px 0 4px;color:#333;}
			.muted{color:#666;}.small{font-size:.85rem;}
			.grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 22px;margin:10px 0;}
			.kv{font-size:.9rem;}.kv b{color:#000;}
			table.id{width:100%;border-collapse:collapse;margin:8px 0;font-size:.9rem;}
			table.id td{border:1px solid #ddd;padding:7px 10px;}table.id td:first-child{background:#f7f7f9;font-weight:600;width:38%;}
			.att{background:#f7f9fc;border:1px solid #dce4ee;border-radius:8px;padding:12px 16px;font-size:.88rem;margin:10px 0;}
			ul.check{list-style:none;padding:0;}ul.check li{padding:5px 0 5px 26px;position:relative;font-size:.92rem;}
			ul.check li:before{content:"\2610";position:absolute;left:0;font-size:1.1rem;color:#7a3fb3;}
			.sign{margin-top:26px;display:flex;justify-content:space-between;font-size:.9rem;}
			.callout{background:#faf5ea;border:1px solid #e6d29a;border-radius:8px;padding:12px 16px;margin:14px 0;font-size:.9rem;}
			.page{page-break-after:always;}
			@media print{ body{background:#fff;} .bar{display:none;} .doc{box-shadow:none;margin:0;max-width:none;padding:0 8mm;} }
		</style></head><body>
		<div class="bar">
			<a class="back" href="<?php echo esc_url( admin_url( 'admin.php?page=ag-appels-offres' ) ); ?>">← Liste</a>
			<button class="print" onclick="window.print()">🖨️ Imprimer / PDF</button>
			<?php if ( $d['profil'] ) : ?><a class="depot" href="<?php echo esc_url( $d['profil'] ); ?>" target="_blank" rel="noopener">📤 Déposer sur le profil d'acheteur</a><?php endif; ?>
			<span class="sp"></span>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<button class="st" data-st="pret">Dossier prêt</button>
				<button class="st" data-st="depose">Déposé ✓</button>
			<?php endif; ?>
		</div>

		<div class="doc page">
			<div class="small muted">DOSSIER DE CANDIDATURE ET OFFRE</div>
			<h1><?php echo esc_html( $d['objet'] ); ?></h1>
			<div class="grid" style="margin-top:16px;">
				<div class="kv"><b>Pouvoir adjudicateur :</b> <?php echo esc_html( $d['acheteur'] ); ?></div>
				<div class="kv"><b>Référence :</b> <?php echo esc_html( $d['ref'] ?: $d['id'] ); ?></div>
				<div class="kv"><b>Département :</b> <?php echo esc_html( $d['dept'] ); ?></div>
				<div class="kv"><b>Procédure :</b> <?php echo esc_html( $d['procedure'] ); ?></div>
				<div class="kv" style="color:#b32d2e;"><b>Date limite de remise :</b> <?php echo esc_html( $d['limite'] ); ?></div>
				<div class="kv"><b>Validité de l'offre :</b> <?php echo esc_html( $d['validite'] ? $d['validite'] . ' jours' : '120 jours' ); ?></div>
			</div>
			<div class="callout"><strong>Candidat :</strong> <?php echo esc_html( ag_cand_opt( 'enseigne' ) ); ?> (<?php echo esc_html( ag_cand_opt( 'raison' ) ); ?>) — SIREN <?php echo esc_html( ag_cand_opt( 'siren' ) ); ?><?php echo $adr ? ' — ' . esc_html( $adr ) : ''; ?></div>
			<p class="small muted">Établi le <?php echo esc_html( $today ); ?>. Dépôt dématérialisé sur le profil d'acheteur.</p>
		</div>

		<div class="doc page">
			<h2>1. Lettre de candidature</h2>
			<p class="small muted"><?php echo esc_html( ag_cand_opt( 'enseigne' ) ); ?> — <?php echo esc_html( $adr ?: ag_cand_opt( 'city' ) ); ?><br>
			<?php echo esc_html( $today ); ?></p>
			<p><strong>À l'attention de<?php echo $contact ? ' ' . esc_html( $contact ) : ''; ?><?php echo $d['ac_fonc'] ? ' — ' . esc_html( $d['ac_fonc'] ) : ''; ?></strong><br>
			<?php echo esc_html( $d['acheteur'] ); ?><?php echo $acheteur_adr ? '<br>' . esc_html( $acheteur_adr ) : ''; ?></p>
			<p><strong>Objet :</strong> candidature au marché « <?php echo esc_html( wp_trim_words( $d['objet'], 20, '…' ) ); ?> » — réf. <?php echo esc_html( $d['ref'] ?: $d['id'] ); ?>.</p>
			<p>Madame, Monsieur,</p>
			<p>Par la présente, <strong><?php echo esc_html( ag_cand_opt( 'raison' ) ); ?></strong> (enseigne « <?php echo esc_html( ag_cand_opt( 'enseigne' ) ); ?> »), représentée par <?php echo esc_html( ag_cand_opt( 'repr' ) ); ?>, <?php echo esc_html( ag_cand_opt( 'repr_qual' ) ); ?>, présente sa candidature à la consultation citée en objet.</p>
			<p>Notre structure dispose des compétences, moyens et références nécessaires à la bonne exécution des prestations attendues, dans le respect du cahier des charges, des délais et de la réglementation applicable. Vous trouverez ci-après notre déclaration de candidat, notre mémoire technique et la liste des pièces justificatives jointes.</p>
			<p>Nous nous tenons à votre disposition pour toute audition ou précision.</p>
			<p>Nous vous prions d'agréer, Madame, Monsieur, l'expression de nos salutations distinguées.</p>
			<div class="sign"><div><?php echo esc_html( ag_cand_opt( 'repr' ) ); ?><br><span class="muted small"><?php echo esc_html( ag_cand_opt( 'repr_qual' ) ); ?></span></div><div class="muted small">Signature</div></div>
		</div>

		<div class="doc page">
			<h2>2. Déclaration du candidat</h2>
			<table class="id"><tbody>
				<tr><td>Raison sociale</td><td><?php echo esc_html( ag_cand_opt( 'raison' ) ); ?></td></tr>
				<tr><td>Nom commercial / enseigne</td><td><?php echo esc_html( ag_cand_opt( 'enseigne' ) ); ?></td></tr>
				<tr><td>Forme juridique</td><td><?php echo esc_html( ag_cand_opt( 'forme' ) ); ?></td></tr>
				<tr><td>SIREN</td><td><?php echo esc_html( ag_cand_opt( 'siren' ) ); ?></td></tr>
				<tr><td>SIRET</td><td><?php echo esc_html( ag_cand_opt( 'siret' ) ?: '— à compléter —' ); ?></td></tr>
				<tr><td>Code APE / NAF</td><td><?php echo esc_html( ag_cand_opt( 'ape' ) ?: '— à compléter —' ); ?></td></tr>
				<tr><td>N° TVA intracom.</td><td><?php echo esc_html( ag_cand_opt( 'tva' ) ?: '—' ); ?></td></tr>
				<tr><td>Adresse</td><td><?php echo esc_html( $adr ?: '— à compléter —' ); ?></td></tr>
				<tr><td>Téléphone</td><td><?php echo esc_html( ag_cand_opt( 'phone' ) ); ?></td></tr>
				<tr><td>Email</td><td><?php echo esc_html( ag_cand_opt( 'email' ) ); ?></td></tr>
				<tr><td>Représentant légal</td><td><?php echo esc_html( ag_cand_opt( 'repr' ) ); ?> (<?php echo esc_html( ag_cand_opt( 'repr_qual' ) ); ?>)</td></tr>
			</tbody></table>
			<div class="att">
				<strong>Attestations sur l'honneur.</strong> Le candidat atteste sur l'honneur : ne pas entrer dans un cas d'interdiction de soumissionner (art. L2141-1 et suivants du Code de la commande publique) ; être en règle au regard de ses obligations fiscales et sociales ; que le travail sera réalisé avec des salariés employés régulièrement ; et que les renseignements fournis sont exacts.
			</div>
			<p class="small muted">Ce cadre reprend les informations des formulaires DC1 (lettre de candidature) et DC2 (déclaration du candidat). Joindre les formulaires officiels signés si le règlement de la consultation les exige.</p>
		</div>

		<div class="doc page">
			<h2>3. Mémoire technique — <?php echo esc_html( $mem['titre'] ); ?></h2>
			<?php foreach ( $mem['blocs'] as $t => $c ) : ?>
				<h3><?php echo esc_html( $t ); ?></h3>
				<p><?php echo esc_html( $c ); ?></p>
			<?php endforeach; ?>
			<?php if ( $refs ) : ?>
				<h3>Références</h3>
				<ul>
				<?php foreach ( $refs as $r ) : $rn = is_array( $r ) ? ( $r['name'] ?? $r['title'] ?? '' ) : (string) $r; if ( '' === $rn ) continue; ?>
					<li><?php echo esc_html( $rn ); ?><?php if ( is_array( $r ) && ! empty( $r['url'] ) ) : ?> — <span class="muted small"><?php echo esc_html( $r['url'] ); ?></span><?php endif; ?></li>
				<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p class="callout small">✏️ Personnalise ce mémoire selon le <strong>règlement de la consultation</strong> (critères de jugement, exigences techniques précises) avant de déposer. C'est une trame solide, pas un document figé.</p>
		</div>

		<div class="doc">
			<h2>4. Pièces à joindre au dépôt</h2>
			<ul class="check">
				<li>Lettre de candidature (DC1) signée</li>
				<li>Déclaration du candidat (DC2) signée</li>
				<li>Extrait Kbis ou avis de situation SIRENE (&lt; 3 mois)</li>
				<li>Attestation de régularité fiscale</li>
				<li>Attestation de vigilance URSSAF (&lt; 6 mois)</li>
				<li>Attestation d'assurance responsabilité civile professionnelle en cours</li>
				<li>Mémoire technique</li>
				<li>Acte d'engagement (ATTRI1/DC3) complété et signé — le cas échéant</li>
				<li>Bordereau de prix / DPGF chiffré selon le cadre fourni</li>
				<li>RIB</li>
			</ul>
			<div class="callout">
				<strong>📤 Où déposer :</strong> le dépôt est <strong>obligatoirement dématérialisé</strong> sur le profil d'acheteur du marché.<br>
				<?php if ( $d['profil'] ) : ?><a href="<?php echo esc_url( $d['profil'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $d['profil'] ); ?></a><?php else : ?>Voir l'avis officiel : <a href="<?php echo esc_url( $d['url_avis'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $d['url_avis'] ); ?></a><?php endif; ?><br>
				<span class="small muted">Avant la date limite : <strong><?php echo esc_html( $d['limite'] ); ?></strong>. Prévoir une signature électronique si elle est exigée.</span>
			</div>
		</div>

		<script>
		(function(){
			var nonce = <?php echo wp_json_encode( wp_create_nonce( 'ag_boamp' ) ); ?>;
			var ajax  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var id    = <?php echo wp_json_encode( $d['id'] ); ?>;
			var objet = <?php echo wp_json_encode( $d['objet'] ); ?>;
			var ach   = <?php echo wp_json_encode( $d['acheteur'] ); ?>;
			document.addEventListener('click', function(e){
				var b = e.target.closest ? e.target.closest('.st') : null;
				if (!b) return;
				var body = new URLSearchParams();
				body.set('action','ag_cand_status');
				body.set('_n', nonce);
				body.set('id', id);
				body.set('statut', b.dataset.st);
				body.set('objet', objet);
				body.set('acheteur', ach);
				fetch(ajax, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
					.then(function(r){ return r.json(); })
					.then(function(j){ b.textContent = (j && j.success) ? '✓ enregistré' : 'erreur'; });
			});
		})();
		</script>
		</body></html>
		<?php
	}
}

/* ── Page admin « 🗂️ Mes candidatures » (suivi) ── */
if ( ! function_exists( 'ag_candidatures_render' ) ) {
	function ag_candidatures_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$all = (array) get_option( 'ag_candidatures', array() );
		uasort( $all, function ( $a, $b ) { return (int) ( $b['ts'] ?? 0 ) - (int) ( $a['ts'] ?? 0 ); } );
		$labels = array( 'a_faire' => 'À faire', 'pret' => 'Dossier prêt', 'depose' => 'Déposé', 'gagne' => 'Gagné 🏆', 'perdu' => 'Perdu' );
		?>
		<div class="wrap">
			<h1>🗂️ Mes candidatures</h1>
			<p style="color:#666;">Suivi des appels d'offres sur lesquels tu candidates. Le statut se met à jour depuis le dossier (« Dossier prêt » / « Déposé ») ou ici.</p>
			<?php if ( ! $all ) : ?>
				<div class="notice notice-info"><p>Aucune candidature suivie pour l'instant. Va dans <strong>📢 Appels d'offres</strong>, ouvre un marché avec « 🗂️ Préparer le dossier ».</p></div>
			<?php else : ?>
				<table class="widefat striped">
					<thead><tr><th>Objet</th><th>Acheteur</th><th>Statut</th><th>Maj</th><th>Dossier</th></tr></thead>
					<tbody>
					<?php foreach ( $all as $id => $c ) : ?>
						<tr>
							<td><?php echo esc_html( wp_trim_words( (string) ( $c['objet'] ?? '' ), 16, '…' ) ?: $id ); ?></td>
							<td><?php echo esc_html( $c['acheteur'] ?? '' ); ?></td>
							<td><strong><?php echo esc_html( $labels[ $c['statut'] ?? '' ] ?? ( $c['statut'] ?? '' ) ); ?></strong></td>
							<td style="color:#888;"><?php echo esc_html( ! empty( $c['ts'] ) ? date_i18n( 'd/m/Y', (int) $c['ts'] ) : '' ); ?></td>
							<td><a class="button button-small" target="_blank" rel="noopener" href="<?php echo esc_url( home_url( '/?ag_candidature=1&id=' . rawurlencode( $id ) ) ); ?>">Ouvrir</a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}
}
