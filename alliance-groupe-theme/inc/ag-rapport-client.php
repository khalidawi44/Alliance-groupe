<?php
/**
 * AG — Rapport client privé + paiement (la page derrière les QR de l'assistant).
 *
 * Fabrice crée un « Rapport client » dans wp-admin (colle le HTML du rapport
 * généré par assistant-defi + les liens de paiement Stripe/PayPal par formule).
 * Chaque rapport a une URL PRIVÉE non devinable : /rapport/<jeton> (non indexée).
 * Les QR / boutons de l'assistant pointent dessus, avec ?offre=rapport|correction|refonte.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---- 1. Type de contenu « Rapport client » (admin uniquement) ---- */
add_action( 'init', function () {
	register_post_type( 'ag_rapport', array(
		'labels' => array(
			'name' => 'Rapports client', 'singular_name' => 'Rapport client',
			'add_new_item' => 'Nouveau rapport client', 'edit_item' => 'Modifier le rapport',
			'menu_name' => '🛡️ Rapports client',
		),
		'public' => false, 'show_ui' => true, 'show_in_menu' => true,
		'menu_icon' => 'dashicons-shield', 'menu_position' => 58,
		'supports' => array( 'title' ), 'exclude_from_search' => true,
		'capability_type' => 'post',
	) );
	add_rewrite_rule( '^rapport/([^/]+)/?$', 'index.php?ag_rapport_token=$matches[1]', 'top' );
	if ( get_option( 'ag_rapport_rw' ) !== '2' ) { flush_rewrite_rules( false ); update_option( 'ag_rapport_rw', '2' ); }
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'ag_rapport_token'; return $v; } );

/* ---- 2. Champs (métabox) ---- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ag_rap_box', 'Contenu du rapport & paiement', 'ag_rap_box_render', 'ag_rapport', 'normal', 'high' );
} );
function ag_rap_field( $post, $k ) { return esc_attr( (string) get_post_meta( $post->ID, $k, true ) ); }
function ag_rap_box_render( $post ) {
	wp_nonce_field( 'ag_rap_save', 'ag_rap_nonce' );
	$tok = get_post_meta( $post->ID, '_ag_rap_token', true );
	$url = $tok ? home_url( '/rapport/' . $tok ) : '(enregistrez pour générer le lien privé)';
	$html = (string) get_post_meta( $post->ID, '_ag_rap_html', true );
	echo '<style>.agr label{display:block;font-weight:600;margin:12px 0 4px}.agr input,.agr textarea{width:100%;padding:8px;border:1px solid #ccd0d4;border-radius:5px}.agr .row2{display:flex;gap:12px}.agr .row2>div{flex:1}</style>';
	echo '<div class="agr">';
	echo '<p><b>Lien privé du rapport :</b> <a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a><br><span style="color:#666">C\'est ce lien (sans indexation) que tu mets dans l\'assistant, champ « Lien de paiement ». Les QR pointeront dessus.</span></p>';
	echo '<div class="row2"><div><label>Client</label><input type="text" name="ag_rap_client" value="' . ag_rap_field( $post, '_ag_rap_client' ) . '"></div>';
	echo '<div><label>Cible (domaine)</label><input type="text" name="ag_rap_cible" value="' . ag_rap_field( $post, '_ag_rap_cible' ) . '"></div></div>';
	echo '<label>HTML du rapport (colle le rapport « version client » généré par assistant-defi)</label>';
	echo '<textarea name="ag_rap_html" rows="10" placeholder="Colle ici le contenu du cadre blanc du rapport (bouton clic droit → Inspecter → copier, ou laisse vide pour n\'afficher que les formules)">' . esc_textarea( $html ) . '</textarea>';
	echo '<p style="color:#666">Astuce : tu peux laisser vide et n\'afficher que le bloc paiement ci-dessous.</p>';
	echo '<div class="row2"><div><label>Prix Rapport seul (€)</label><input type="number" name="ag_rap_px_rapport" value="' . ag_rap_field( $post, '_ag_rap_px_rapport' ) . '" placeholder="149"></div>';
	echo '<div><label>Prix Rapport + Correction (€)</label><input type="number" name="ag_rap_px_correction" value="' . ag_rap_field( $post, '_ag_rap_px_correction' ) . '" placeholder="1299"></div>';
	echo '<div><label>Prix Refonte (à partir de €)</label><input type="number" name="ag_rap_px_refonte" value="' . ag_rap_field( $post, '_ag_rap_px_refonte' ) . '" placeholder="1490"></div></div>';
	echo '<label>Lien de paiement — Rapport seul (Stripe/PayPal)</label><input type="url" name="ag_rap_pay_rapport" value="' . ag_rap_field( $post, '_ag_rap_pay_rapport' ) . '" placeholder="https://buy.stripe.com/...">';
	echo '<label>Lien de paiement — Rapport + Correction</label><input type="url" name="ag_rap_pay_correction" value="' . ag_rap_field( $post, '_ag_rap_pay_correction' ) . '" placeholder="https://buy.stripe.com/...">';
	echo '<label>Lien de paiement — Refonte complète</label><input type="url" name="ag_rap_pay_refonte" value="' . ag_rap_field( $post, '_ag_rap_pay_refonte' ) . '" placeholder="https://buy.stripe.com/...">';
	echo '</div>';
}

/* ---- 3. Enregistrement ---- */
add_action( 'save_post_ag_rapport', function ( $post_id ) {
	if ( ! isset( $_POST['ag_rap_nonce'] ) || ! wp_verify_nonce( $_POST['ag_rap_nonce'], 'ag_rap_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$txt = array( '_ag_rap_client'=>'ag_rap_client', '_ag_rap_cible'=>'ag_rap_cible',
		'_ag_rap_px_rapport'=>'ag_rap_px_rapport', '_ag_rap_px_correction'=>'ag_rap_px_correction', '_ag_rap_px_refonte'=>'ag_rap_px_refonte' );
	foreach ( $txt as $meta=>$field ) { if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) ); }
	$urls = array( '_ag_rap_pay_rapport'=>'ag_rap_pay_rapport', '_ag_rap_pay_correction'=>'ag_rap_pay_correction', '_ag_rap_pay_refonte'=>'ag_rap_pay_refonte' );
	foreach ( $urls as $meta=>$field ) { if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, $meta, esc_url_raw( wp_unslash( $_POST[ $field ] ) ) ); }
	// HTML : autorisé seulement pour un auteur qui a le droit unfiltered_html (admin)
	if ( isset( $_POST['ag_rap_html'] ) ) {
		$raw = wp_unslash( $_POST['ag_rap_html'] );
		update_post_meta( $post_id, '_ag_rap_html', current_user_can( 'unfiltered_html' ) ? $raw : wp_kses_post( $raw ) );
	}
	if ( ! get_post_meta( $post_id, '_ag_rap_token', true ) ) {
		update_post_meta( $post_id, '_ag_rap_token', wp_generate_password( 22, false, false ) );
	}
} );

/* ---- 4. Colonne « Lien privé » dans la liste ---- */
add_filter( 'manage_ag_rapport_posts_columns', function ( $c ) { $c['ag_lien'] = 'Lien privé'; return $c; } );
add_action( 'manage_ag_rapport_posts_custom_column', function ( $col, $id ) {
	if ( 'ag_lien' === $col ) { $t = get_post_meta( $id, '_ag_rap_token', true ); if ( $t ) { $u = home_url( '/rapport/' . $t ); echo '<a href="' . esc_url( $u ) . '" target="_blank">/rapport/' . esc_html( substr( $t, 0, 8 ) ) . '…</a>'; } }
}, 10, 2 );

/* ---- 5. Rendu de la page publique privée /rapport/<jeton> ---- */
add_action( 'template_redirect', function () {
	$tok = get_query_var( 'ag_rapport_token' );
	if ( ! $tok ) return;
	$q = get_posts( array( 'post_type'=>'ag_rapport', 'post_status'=>'publish', 'numberposts'=>1,
		'meta_key'=>'_ag_rap_token', 'meta_value'=>$tok, 'fields'=>'ids' ) );
	if ( empty( $q ) ) { status_header( 404 ); nocache_headers(); echo '<!doctype html><meta charset="utf-8"><p style="font:16px sans-serif;padding:40px">Rapport introuvable ou expiré.</p>'; exit; }
	$id = $q[0];
	$client = get_post_meta( $id, '_ag_rap_client', true );
	$cible  = get_post_meta( $id, '_ag_rap_cible', true );
	$html   = (string) get_post_meta( $id, '_ag_rap_html', true );
	$offre  = isset( $_GET['offre'] ) ? sanitize_key( $_GET['offre'] ) : '';
	$px = array(
		'rapport'    => array( 'Rapport seul',            get_post_meta( $id, '_ag_rap_px_rapport', true ),    get_post_meta( $id, '_ag_rap_pay_rapport', true ),    'Le rapport complet, à vous de corriger.' ),
		'correction' => array( 'Rapport + Correction',    get_post_meta( $id, '_ag_rap_px_correction', true ), get_post_meta( $id, '_ag_rap_pay_correction', true ), 'On corrige tout + re-test de vérification.' ),
		'refonte'    => array( 'Refonte complète sécurisée', get_post_meta( $id, '_ag_rap_px_refonte', true ),  get_post_meta( $id, '_ag_rap_pay_refonte', true ),    'Site reconstruit propre et durci.' ),
	);
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	$logo = '<svg viewBox="0 0 120 120" width="46" height="46" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="agg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#E6A93C"/><stop offset="1" stop-color="#F37A1F"/></linearGradient></defs><circle cx="60" cy="60" r="55" fill="#0e1016" stroke="url(#agg)" stroke-width="5"/><text x="60" y="77" text-anchor="middle" font-family="Georgia,serif" font-weight="800" font-size="48" fill="url(#agg)">AG</text></svg>';
	echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Rapport de sécurité — Alliance Groupe</title>';
	echo '<style>body{margin:0;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f4f5f7;color:#141414}.wrap{max-width:820px;margin:0 auto;background:#fff}.hd{display:flex;align-items:center;gap:14px;background:linear-gradient(135deg,#0e1016,#1b2030);padding:20px 24px}.hd .t{color:#fff;font-weight:800;font-size:19px}.hd .s{color:#E6A93C;font-size:13px}.body{padding:8px 24px 28px}.offers{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}.card{flex:1;min-width:210px;border:1px solid #e3e3e3;border-radius:10px;padding:16px}.card.hi{border:2px solid #F37A1F;background:#fff8ec}.card .p{font-size:24px;font-weight:900;margin:6px 0}.btn{display:inline-block;margin-top:8px;background:linear-gradient(135deg,#E6A93C,#F37A1F);color:#241200;font-weight:800;text-decoration:none;padding:12px 18px;border-radius:9px}.btn.d{background:#0e1016;color:#fff}.foot{padding:16px 24px;color:#777;font-size:12px;border-top:1px solid #eee}</style></head><body><div class="wrap">';
	echo '<div class="hd">' . $logo . '<div><div class="t">Alliance Groupe — Rapport de sécurité</div><div class="s">' . esc_html( $client ? $client : 'Client' ) . ( $cible ? ' · ' . esc_html( $cible ) : '' ) . '</div></div></div>';
	echo '<div class="body">';
	if ( $html !== '' ) { echo $html; } // contenu authoré en admin (trusted)
	echo '<h3>Choisissez votre formule</h3><div class="offers">';
	foreach ( $px as $key => $o ) {
		$hi = ( $key === 'correction' || $key === $offre ) ? ' hi' : '';
		$prix = $o[1] !== '' ? number_format_i18n( (float) $o[1], 0 ) . ' €' . ( $key === 'refonte' ? ' et +' : '' ) : 'Sur devis';
		echo '<div class="card' . $hi . '" id="offre-' . esc_attr( $key ) . '"><div style="font-weight:800">' . esc_html( $o[0] ) . '</div><div class="p">' . esc_html( $prix ) . '</div><div style="font-size:13px;color:#555">' . esc_html( $o[3] ) . '</div>';
		if ( $o[2] ) { echo '<a class="btn' . ( $key === 'correction' ? '' : ' d' ) . '" href="' . esc_url( $o[2] ) . '">Payer en ligne</a>'; }
		else { echo '<div style="margin-top:8px;font-size:12px;color:#888">Nous contacter : 07 44 82 95 16</div>'; }
		echo '</div>';
	}
	echo '</div></div>';
	echo '<div class="foot">Document confidentiel remis dans le cadre d\'un mandat / programme autorisé. Alliance Groupe — 07 44 82 95 16 · alliancegroupe-inc.com/contact</div>';
	echo '</div>';
	if ( $offre ) { echo '<script>var e=document.getElementById("offre-' . esc_js( $offre ) . '");if(e)e.scrollIntoView({behavior:"smooth",block:"center"});</script>'; }
	echo '</body></html>';
	exit;
} );
