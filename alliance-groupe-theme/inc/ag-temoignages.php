<?php
/**
 * AG Témoignages — avis clients SUR NOTRE site (en attendant la fiche Google).
 *  - Formulaire public (note 1-5 + texte) → enregistré en « à modérer » ;
 *  - affichage des témoignages approuvés ([ag_temoignages]) + note moyenne ;
 *  - schema JSON-LD Review/AggregateRating sur la page « avis-clients » ;
 *  - page admin ⭐ Témoignages (approuver / supprimer) + notif à la réception ;
 *  - page « avis-clients » auto-créée (idempotent).
 *
 * 100 % réel : ce sont de VRAIS retours clients, modérés à la main, sans triche.
 * Le jour où la fiche Google est validée, on aiguille vers ag-avis.php (QR/lien).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_TEMOIGNAGES_VER', 1 );

/* Lecture / écriture du stock. */
if ( ! function_exists( 'ag_temoignages_all' ) ) {
	function ag_temoignages_all() {
		$d = get_option( 'ag_temoignages', array() );
		return is_array( $d ) ? $d : array();
	}
}
if ( ! function_exists( 'ag_temoignages_approved' ) ) {
	function ag_temoignages_approved() {
		return array_values( array_filter( ag_temoignages_all(), function ( $t ) {
			return 'approved' === ( $t['status'] ?? '' );
		} ) );
	}
}
if ( ! function_exists( 'ag_temoignages_avg' ) ) {
	function ag_temoignages_avg() {
		$a = ag_temoignages_approved();
		if ( empty( $a ) ) return array( 0, 0 );
		$sum = 0;
		foreach ( $a as $t ) { $sum += (int) ( $t['rating'] ?? 5 ); }
		return array( round( $sum / count( $a ), 1 ), count( $a ) );
	}
}

/* Réception d'un témoignage (formulaire public). */
add_action( 'admin_post_nopriv_ag_temoignage_submit', 'ag_temoignage_submit' );
add_action( 'admin_post_ag_temoignage_submit', 'ag_temoignage_submit' );
if ( ! function_exists( 'ag_temoignage_submit' ) ) {
	function ag_temoignage_submit() {
		$back = wp_get_referer() ?: home_url( '/avis-clients/' );
		if ( ! isset( $_POST['ag_tm_nonce'] ) || ! wp_verify_nonce( $_POST['ag_tm_nonce'], 'ag_temoignage' ) ) {
			wp_safe_redirect( add_query_arg( 'tm', 'err', $back ) ); exit;
		}
		// Anti-spam : pot de miel (doit rester vide).
		if ( ! empty( $_POST['website'] ) ) { wp_safe_redirect( add_query_arg( 'tm', 'ok', $back ) ); exit; }
		$name   = sanitize_text_field( wp_unslash( $_POST['ag_tm_name'] ?? '' ) );
		$city   = sanitize_text_field( wp_unslash( $_POST['ag_tm_city'] ?? '' ) );
		$rating = max( 1, min( 5, (int) ( $_POST['ag_tm_rating'] ?? 5 ) ) );
		$text   = sanitize_textarea_field( wp_unslash( $_POST['ag_tm_text'] ?? '' ) );
		if ( '' === $name || '' === $text ) {
			wp_safe_redirect( add_query_arg( 'tm', 'err', $back ) ); exit;
		}
		$all   = ag_temoignages_all();
		$all[] = array(
			'id'     => 'tm_' . substr( md5( $name . microtime() ), 0, 10 ),
			'name'   => $name,
			'city'   => $city,
			'rating' => $rating,
			'text'   => wp_trim_words( $text, 80, '…' ),
			'status' => 'pending',
			'date'   => current_time( 'mysql' ),
		);
		update_option( 'ag_temoignages', $all, false );
		// Notif au patron (Telegram interne + SMS si dispo).
		if ( function_exists( 'ag_push' ) ) {
			ag_push( "⭐ Nouvel avis client à valider ({$rating}/5) — {$name}" . ( $city ? " ({$city})" : '' ) . "\n« " . wp_trim_words( $text, 25, '…' ) . " »\n→ wp-admin ⭐ Témoignages" );
		}
		wp_safe_redirect( add_query_arg( 'tm', 'ok', $back ) ); exit;
	}
}

/* Shortcode d'affichage + formulaire : [ag_temoignages] */
add_shortcode( 'ag_temoignages', function () {
	$list = ag_temoignages_approved();
	list( $avg, $n ) = ag_temoignages_avg();
	$stars = function ( $r ) {
		$r = (int) $r; $o = '';
		for ( $i = 1; $i <= 5; $i++ ) { $o .= $i <= $r ? '★' : '☆'; }
		return $o;
	};
	ob_start();
	echo '<div class="ag-tm" style="max-width:860px;margin:0 auto;font-family:-apple-system,Segoe UI,Arial,sans-serif;">';

	// Bandeau confirmation.
	$flag = sanitize_key( $_GET['tm'] ?? '' );
	if ( 'ok' === $flag ) {
		echo '<p style="background:#eaf7ea;border:1px solid #bfe3bf;color:#1c6b1c;padding:12px 16px;border-radius:12px;">Merci beaucoup pour votre avis 🙏 Il sera publié après une rapide vérification.</p>';
	} elseif ( 'err' === $flag ) {
		echo '<p style="background:#fdeaea;border:1px solid #f3bdbd;color:#9a1c1c;padding:12px 16px;border-radius:12px;">Oups, votre nom et un message sont nécessaires. Réessayez 🙂</p>';
	}

	// Note moyenne.
	if ( $n > 0 ) {
		echo '<div style="text-align:center;margin:18px 0 26px;">';
		echo '<div style="font-size:2.4rem;color:#D4B45C;line-height:1;">' . esc_html( $stars( round( $avg ) ) ) . '</div>';
		echo '<p style="margin:6px 0 0;color:#444;"><strong>' . esc_html( number_format_i18n( $avg, 1 ) ) . '/5</strong> — ' . (int) $n . ' avis clients</p>';
		echo '</div>';
	}

	// Liste des témoignages.
	if ( $n > 0 ) {
		echo '<div style="display:grid;gap:16px;">';
		foreach ( $list as $t ) {
			echo '<blockquote style="margin:0;padding:18px 20px;border:1px solid #eee;border-left:4px solid #D4B45C;border-radius:12px;background:#fff;">';
			echo '<div style="color:#D4B45C;font-size:1.1rem;">' . esc_html( $stars( $t['rating'] ?? 5 ) ) . '</div>';
			echo '<p style="margin:8px 0;color:#222;font-size:1.02rem;">« ' . esc_html( $t['text'] ?? '' ) . ' »</p>';
			echo '<footer style="color:#777;font-size:.9rem;"><strong>' . esc_html( $t['name'] ?? '' ) . '</strong>' . ( ! empty( $t['city'] ) ? ' — ' . esc_html( $t['city'] ) : '' ) . '</footer>';
			echo '</blockquote>';
		}
		echo '</div>';
	}

	// Formulaire.
	echo '<h3 style="margin:34px 0 12px;">Laissez votre avis</h3>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:grid;gap:12px;max-width:560px;">';
	echo '<input type="hidden" name="action" value="ag_temoignage_submit">';
	echo wp_nonce_field( 'ag_temoignage', 'ag_tm_nonce', true, false );
	echo '<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">'; // honeypot
	echo '<label>Votre nom<br><input type="text" name="ag_tm_name" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;"></label>';
	echo '<label>Ville (facultatif)<br><input type="text" name="ag_tm_city" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;"></label>';
	echo '<label>Votre note<br><select name="ag_tm_rating" style="padding:10px;border:1px solid #ccc;border-radius:8px;"><option value="5">★★★★★ Excellent</option><option value="4">★★★★ Très bien</option><option value="3">★★★ Bien</option><option value="2">★★ Moyen</option><option value="1">★ Décevant</option></select></label>';
	echo '<label>Votre message<br><textarea name="ag_tm_text" rows="4" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;"></textarea></label>';
	echo '<button type="submit" style="justify-self:start;padding:12px 26px;border:0;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;cursor:pointer;">Envoyer mon avis ⭐</button>';
	echo '</form>';

	echo '</div>';
	return ob_get_clean();
} );

/* Schema Review + AggregateRating sur la page « avis-clients ». */
add_action( 'wp_head', function () {
	if ( ! is_page() ) return;
	if ( 'avis-clients' !== get_post_field( 'post_name' ) ) return;
	$list = ag_temoignages_approved();
	if ( empty( $list ) ) return;
	list( $avg, $n ) = ag_temoignages_avg();
	$reviews = array();
	foreach ( array_slice( $list, 0, 20 ) as $t ) {
		$reviews[] = array(
			'@type'         => 'Review',
			'reviewRating'  => array( '@type' => 'Rating', 'ratingValue' => (string) ( $t['rating'] ?? 5 ), 'bestRating' => '5' ),
			'author'        => array( '@type' => 'Person', 'name' => $t['name'] ?? 'Client' ),
			'reviewBody'    => $t['text'] ?? '',
		);
	}
	$data = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'LocalBusiness',
		'name'            => 'Alliance Groupe',
		'url'             => home_url( '/' ),
		'aggregateRating' => array(
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) $avg,
			'reviewCount' => (string) $n,
			'bestRating'  => '5',
		),
		'review'          => $reviews,
	);
	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 20 );

/* Page « avis-clients » auto-créée. */
add_action( 'admin_init', function () {
	if ( (int) get_option( 'ag_temoignages_page_done', 0 ) >= AG_TEMOIGNAGES_VER ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! get_page_by_path( 'avis-clients' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Avis clients',
			'post_name'    => 'avis-clients',
			'post_content' => "<p>Ce que nos clients disent d'Alliance Groupe. Merci à eux pour leur confiance 🙏</p>\n[ag_temoignages]",
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id() ?: 1,
		) );
	}
	update_option( 'ag_temoignages_page_done', AG_TEMOIGNAGES_VER );
} );

/* Admin : modération. */
add_action( 'admin_init', function () {
	if ( empty( $_POST['ag_tm_action'] ) || ! check_admin_referer( 'ag_tm_admin' ) ) return;
	$id  = sanitize_text_field( wp_unslash( $_POST['tid'] ?? '' ) );
	$act = sanitize_key( $_POST['ag_tm_action'] );
	$all = ag_temoignages_all();
	foreach ( $all as $k => $t ) {
		if ( ( $t['id'] ?? '' ) !== $id ) continue;
		if ( 'approve' === $act ) { $all[ $k ]['status'] = 'approved'; }
		elseif ( 'delete' === $act ) { unset( $all[ $k ] ); }
		break;
	}
	update_option( 'ag_temoignages', array_values( $all ), false );
	add_settings_error( 'ag_tm', 'd', 'approve' === $act ? '✅ Avis publié.' : '🗑 Avis supprimé.', 'updated' );
} );
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-social', 'Témoignages', '⭐ Témoignages', 'manage_options', 'ag-temoignages', 'ag_temoignages_render' );
}, 20 );
if ( ! function_exists( 'ag_temoignages_render' ) ) {
	function ag_temoignages_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		settings_errors( 'ag_tm' );
		$all = ag_temoignages_all();
		list( $avg, $n ) = ag_temoignages_avg();
		echo '<div class="wrap"><h1>⭐ Témoignages (avis clients sur le site)</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Avis affichés sur <code>/avis-clients</code> (note moyenne ' . esc_html( number_format_i18n( $avg, 1 ) ) . '/5 — ' . (int) $n . ' publiés). Modère à la main : ce sont de VRAIS retours. Quand ta <strong>fiche Google</strong> sera validée, oriente plutôt vers le lien Google (page ⭐ Avis Google).</p>';
		if ( empty( $all ) ) { echo '<p>Aucun avis pour l’instant. Partage le lien <code>' . esc_html( home_url( '/avis-clients/' ) ) . '</code> à tes clients.</p></div>'; return; }
		usort( $all, function ( $a, $b ) { return strcmp( $b['date'] ?? '', $a['date'] ?? '' ); } );
		echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Client</th><th>Note</th><th>Message</th><th>Statut</th><th>Action</th></tr></thead><tbody>';
		foreach ( $all as $t ) {
			$pending = 'approved' !== ( $t['status'] ?? '' );
			echo '<tr' . ( $pending ? ' style="background:#fff8e5;"' : '' ) . '>';
			echo '<td style="white-space:nowrap;font-size:.85em;">' . esc_html( mysql2date( 'd/m/Y', $t['date'] ?? '' ) ) . '</td>';
			echo '<td><strong>' . esc_html( $t['name'] ?? '' ) . '</strong>' . ( ! empty( $t['city'] ) ? '<br><small>' . esc_html( $t['city'] ) . '</small>' : '' ) . '</td>';
			echo '<td style="color:#D4B45C;white-space:nowrap;">' . esc_html( str_repeat( '★', (int) ( $t['rating'] ?? 5 ) ) ) . '</td>';
			echo '<td>' . esc_html( $t['text'] ?? '' ) . '</td>';
			echo '<td>' . ( $pending ? '<span style="color:#b26a00;">à valider</span>' : '<span style="color:#1c6b1c;">publié</span>' ) . '</td>';
			echo '<td><form method="post" style="margin:0;display:flex;gap:6px;"><input type="hidden" name="tid" value="' . esc_attr( $t['id'] ?? '' ) . '">';
			echo wp_nonce_field( 'ag_tm_admin', '_wpnonce', true, false );
			if ( $pending ) echo '<button name="ag_tm_action" value="approve" class="button button-small button-primary">Publier</button>';
			echo '<button name="ag_tm_action" value="delete" class="button button-small" onclick="return confirm(\'Supprimer cet avis ?\')">Supprimer</button>';
			echo '</form></td></tr>';
		}
		echo '</tbody></table></div>';
	}
}
