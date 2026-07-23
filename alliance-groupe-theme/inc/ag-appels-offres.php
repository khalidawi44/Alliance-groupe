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

/* ── Menu admin ── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Appels d\'offres publics', '📢 Appels d\'offres', 'manage_options', 'ag-appels-offres', 'ag_appels_offres_render' );
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
								<?php if ( $it['url'] ) : ?><a href="<?php echo esc_url( $it['url'] ); ?>" target="_blank" rel="noopener" class="button button-small button-primary">Répondre / voir l'avis</a><?php endif; ?>
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
