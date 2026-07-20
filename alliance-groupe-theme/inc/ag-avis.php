<?php
/**
 * AG Avis Google — obtention de VRAIS avis, automatiquement et légalement :
 *  - QR code « Laissez un avis » (factures, flyers, écran) ;
 *  - relance auto par email (+ SMS si passerelle) quand un prospect passe
 *    « client » dans le CRM ;
 *  - encart / bouton à poser où l'on veut ([ag_avis_qr], [ag_avis_bouton]) ;
 *  - page admin ⭐ Avis Google (lien, QR, toggle, demande manuelle par client).
 *
 * 100 % conforme : on sollicite de VRAIS clients, sans contrepartie, opt-out.
 * (Aucun faux avis — interdit par Google et par la loi.)
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_avis_link' ) ) {
	function ag_avis_link() {
		$l = trim( (string) get_option( 'ag_avis_link', '' ) );
		if ( $l ) return $l;
		// Repli : si le Place ID est renseigné, on construit le lien « laisser un avis » automatiquement.
		$pid = trim( (string) get_option( 'ag_geo_place_id', '' ) );
		if ( $pid ) { return 'https://search.google.com/local/writereview?placeid=' . rawurlencode( $pid ); }
		// Défaut : lien court « laisser un avis » officiel de la fiche Alliance Groupe.
		return 'https://g.page/r/CR0FmSuutGyMEAE/review';
	}
}
if ( ! function_exists( 'ag_avis_qr_src' ) ) {
	function ag_avis_qr_src( $size = 240 ) {
		$l = ag_avis_link();
		return $l ? 'https://api.qrserver.com/v1/create-qr-code/?size=' . (int) $size . 'x' . (int) $size . '&data=' . rawurlencode( $l ) : '';
	}
}

/* Envoi d'une demande d'avis (email + SMS si dispo). Dédoublonné. */
if ( ! function_exists( 'ag_avis_send' ) ) {
	function ag_avis_send( $name, $email, $phone, $force = false ) {
		$link = ag_avis_link();
		if ( '' === $link ) return false;
		$prenom = trim( (string) $name ) ?: 'Bonjour';
		$key    = strtolower( trim( $email . '|' . $phone ) );
		$sent   = (array) get_option( 'ag_avis_sent', array() );
		if ( ! $force && $key && isset( $sent[ $key ] ) ) return false; // déjà demandé une fois
		$ok = false;
		// Email
		if ( $email && function_exists( 'ag_email_wrap' ) ) {
			$inner  = '<p>Bonjour ' . esc_html( $prenom ) . ',</p>';
			$inner .= '<p>Merci de votre confiance ! Si notre travail vous a plu, un petit <strong>avis Google</strong> nous aiderait énormément (moins d\'une minute) :</p>';
			$inner .= function_exists( 'ag_email_button' ) ? ag_email_button( 'Laisser un avis Google', $link ) : '<p><a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a></p>';
			$inner .= '<p>Un souci ? Répondez simplement à cet email. Merci !</p><p style="color:#9a9aa5;font-size:12px;">Vous recevez ce message dans le cadre de notre prestation ; il ne sera pas renouvelé.</p>';
			$ok = wp_mail( $email, 'Votre avis compte pour nous 🙏', ag_email_wrap( 'Merci ' . $prenom . ' !', $inner ), array( 'Content-Type: text/html; charset=UTF-8' ) ) || $ok;
		}
		// SMS (si passerelle configurée)
		if ( $phone && function_exists( 'ag_sms_send' ) && function_exists( 'ag_sms_gateway_ready' ) && ag_sms_gateway_ready() ) {
			$ok = ag_sms_send( $phone, 'Bonjour ' . $prenom . ', merci pour votre confiance ! Un avis Google nous aide beaucoup (1 min) : ' . $link . ' . STOP pour stop.' ) || $ok;
		}
		if ( $key ) { $sent[ $key ] = time(); update_option( 'ag_avis_sent', $sent, false ); }
		return $ok;
	}
}

/* Relance AUTO quand un prospect devient « client ». */
add_action( 'ag_prospect_status_changed', function ( $id, $status ) {
	if ( 'client' !== $status ) return;
	if ( '1' !== get_option( 'ag_avis_auto', '1' ) ) return;
	if ( '' === ag_avis_link() ) return;
	foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
		if ( ( $p['id'] ?? '' ) === $id ) {
			ag_avis_send( $p['name'] ?? '', $p['email'] ?? '', $p['phone'] ?? '' );
			break;
		}
	}
}, 10, 2 );

/* Réglages + actions admin. */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_avis_save'] ) && check_admin_referer( 'ag_avis' ) ) {
		update_option( 'ag_avis_link', esc_url_raw( wp_unslash( $_POST['ag_avis_link'] ?? '' ) ) );
		update_option( 'ag_avis_auto', isset( $_POST['ag_avis_auto'] ) ? '1' : '0' );
		update_option( 'ag_geo_place_id', sanitize_text_field( wp_unslash( $_POST['ag_geo_place_id'] ?? '' ) ) );
		update_option( 'ag_geo_reviews', wp_kses_post( wp_unslash( $_POST['ag_geo_reviews'] ?? '' ) ) );
		delete_transient( 'ag_geo_gdata' ); // affiche les bons avis tout de suite
	}
	if ( isset( $_POST['ag_avis_flush'] ) && check_admin_referer( 'ag_avis' ) ) {
		delete_transient( 'ag_geo_gdata' );
		add_settings_error( 'ag_avis', 'f', '✅ Cache des avis vidé.', 'updated' );
	}
	if ( isset( $_POST['ag_avis_test'] ) && check_admin_referer( 'ag_avis' ) ) {
		$to = sanitize_email( wp_unslash( $_POST['ag_avis_test_to'] ?? '' ) );
		$ok = $to ? ag_avis_send( 'Test', $to, '', true ) : false;
		add_settings_error( 'ag_avis', 't', $ok ? '✅ Email de test envoyé.' : 'Échec (vérifie le lien d’avis + l’email).', $ok ? 'updated' : 'error' );
	}
	if ( isset( $_POST['ag_avis_ask'], $_POST['cid'] ) && check_admin_referer( 'ag_avis' ) ) {
		$cid = sanitize_text_field( wp_unslash( $_POST['cid'] ) );
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( ( $p['id'] ?? '' ) === $cid ) { ag_avis_send( $p['name'] ?? '', $p['email'] ?? '', $p['phone'] ?? '', true ); break; }
		}
		add_settings_error( 'ag_avis', 'a', '✅ Demande d’avis envoyée.', 'updated' );
	}
} );
add_action( 'admin_menu', function () {
	add_menu_page( 'Avis Google', '⭐ Avis Google', 'manage_options', 'ag-avis', 'ag_avis_render', 'dashicons-star-filled', 33 );
} );
if ( ! function_exists( 'ag_avis_render' ) ) {
	function ag_avis_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		settings_errors( 'ag_avis' );
		$link = ag_avis_link();
		$auto = '1' === get_option( 'ag_avis_auto', '1' );
		$qr   = ag_avis_qr_src( 220 );
		echo '<div class="wrap"><h1>⭐ Avis Google (100 % réels & conformes)</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Récolte de VRAIS avis automatiquement. <strong>Pas de faux avis</strong> (interdit par Google + la loi, et ça fait bannir la fiche). Renseigne ton lien d’avis Google ci-dessous.</p>';
		$pid     = trim( (string) get_option( 'ag_geo_place_id', '' ) );
		$reviews = (string) get_option( 'ag_geo_reviews', '' );
		// État API (si le module d'affichage est dispo)
		if ( function_exists( 'ag_geo_google_data' ) ) {
			$d = ag_geo_google_data();
			if ( ! empty( $d['total'] ) ) {
				echo '<div class="notice notice-success inline"><p>✅ Avis détectés : note <strong>' . esc_html( number_format_i18n( $d['rating'], 1 ) ) . '/5</strong> · <strong>' . (int) $d['total'] . '</strong> avis · ' . count( $d['reviews'] ) . ' affichés sur le site.</p></div>';
			} else {
				echo '<div class="notice notice-warning inline"><p>Aucun avis affiché pour l’instant — renseigne le <strong>Place ID</strong> ci-dessous (sinon, risque d’afficher les avis d’un <strong>homonyme</strong>). Puis « Vider le cache ».</p></div>';
			}
		}
		echo '<form method="post"><table class="form-table"><tbody>';
		echo '<tr><th>Place ID de ta fiche</th><td><input type="text" name="ag_geo_place_id" value="' . esc_attr( $pid ) . '" style="width:420px" placeholder="ChIJ…"><p class="description"><strong>Identifie ta fiche</strong> (affiche tes vrais avis + génère le lien d’avis tout seul). Ex. Alliance Groupe : <code>ChIJ4zk2LCCNZqgRHQWZK660blw</code></p></td></tr>';
		echo '<tr><th>Lien d’avis Google</th><td><input type="text" name="ag_avis_link" value="' . esc_attr( trim( (string) get_option( 'ag_avis_link', '' ) ) ) . '" style="width:520px" placeholder="laisser vide = généré depuis le Place ID"><p class="description">Optionnel. Si vide, on utilise <code>writereview?placeid=…</code>. Ou colle le lien « Demander des avis » de ta fiche.</p></td></tr>';
		echo '<tr><th>Relance automatique</th><td><label><input type="checkbox" name="ag_avis_auto" ' . checked( $auto, true, false ) . '> Demander un avis automatiquement quand un prospect passe « Client » dans le CRM (email + SMS si passerelle)</label></td></tr>';
		echo '<tr><th>Avis à la main (secours)</th><td><textarea name="ag_geo_reviews" rows="3" style="width:560px" class="code" placeholder=\'[{"author":"Prénom N.","rating":5,"text":"Super travail !","time":"juin 2026"}]\'>' . esc_textarea( $reviews ) . '</textarea><p class="description">Uniquement de VRAIS avis (JSON) si l’API ne les renvoie pas.</p></td></tr>';
		echo '</tbody></table>';
		wp_nonce_field( 'ag_avis' );
		echo '<button name="ag_avis_save" value="1" class="button button-primary">Enregistrer</button> ';
		echo '<button name="ag_avis_flush" value="1" class="button">Vider le cache des avis</button></form>';

		if ( $qr ) {
			echo '<hr><h2>QR code « Laissez un avis »</h2>';
			echo '<p style="color:#50575e;">À imprimer sur tes factures / flyers / à afficher. Le client scanne → page d’avis.</p>';
			echo '<img src="' . esc_url( $qr ) . '" alt="QR avis Google" width="220" height="220" style="border:1px solid #ddd;border-radius:8px;background:#fff;padding:6px;">';
			echo '<p><a href="' . esc_url( ag_avis_qr_src( 600 ) ) . '" target="_blank" rel="noopener" class="button">⬇ Télécharger en grand</a></p>';
			echo '<hr><h2>Tester l’email de demande</h2><form method="post">';
			wp_nonce_field( 'ag_avis' );
			echo '<input type="email" name="ag_avis_test_to" placeholder="ton@email.fr" style="width:280px"> <button name="ag_avis_test" value="1" class="button">Envoyer un test</button></form>';
		} else {
			echo '<p style="color:#b26a00;">➕ Renseigne ton lien d’avis pour activer le QR code et les relances.</p>';
		}

		// Clients du CRM → demande en 1 clic.
		$clients = array_filter( (array) get_option( 'ag_prospects', array() ), function ( $p ) { return 'client' === ( $p['status'] ?? '' ); } );
		echo '<hr><h2>Mes clients — demander un avis</h2>';
		if ( empty( $clients ) ) {
			echo '<p style="color:#50575e;">Aucun prospect en statut « Client » pour l’instant. Passe un prospect en « Client » dans la Prospection : la demande d’avis partira automatiquement.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Client</th><th>Contact</th><th>Action</th></tr></thead><tbody>';
			foreach ( $clients as $p ) {
				echo '<tr><td><strong>' . esc_html( $p['name'] ?? '' ) . '</strong>' . ( ! empty( $p['city'] ) ? '<br><small>' . esc_html( $p['city'] ) . '</small>' : '' ) . '</td>';
				echo '<td style="font-size:.85em;">' . esc_html( trim( ( $p['email'] ?? '' ) . ' ' . ( $p['phone'] ?? '' ) ) ?: '—' ) . '</td>';
				echo '<td><form method="post" style="margin:0"><input type="hidden" name="cid" value="' . esc_attr( $p['id'] ?? '' ) . '">';
				wp_nonce_field( 'ag_avis' );
				echo '<button name="ag_avis_ask" value="1" class="button button-small button-primary" ' . ( $link ? '' : 'disabled' ) . '>⭐ Demander l’avis</button></form></td></tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}
}

/* Shortcodes : QR + bouton (page merci, footer, etc.). */
add_shortcode( 'ag_avis_qr', function () {
	$qr = ag_avis_qr_src( 200 );
	if ( '' === $qr ) return '';
	return '<div style="text-align:center;font-family:-apple-system,Arial,sans-serif;"><p style="font-weight:700;color:#D4B45C;">⭐ Votre avis compte !</p><img src="' . esc_url( $qr ) . '" alt="Laisser un avis Google" width="200" height="200" style="border-radius:10px;background:#fff;padding:6px;"><p style="font-size:.85rem;color:#777;">Scannez pour laisser un avis Google</p></div>';
} );
add_shortcode( 'ag_avis_bouton', function ( $atts ) {
	$l = ag_avis_link(); if ( '' === $l ) return '';
	$a = shortcode_atts( array( 'texte' => 'Laisser un avis Google ⭐' ), $atts );
	return '<a href="' . esc_url( $l ) . '" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;text-decoration:none;">' . esc_html( $a['texte'] ) . '</a>';
} );

/* Encart auto sur la page de remerciement après achat/audit. */
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_main_query() || ! in_the_loop() || ! is_page() ) return $content;
	if ( '' === ag_avis_link() ) return $content;
	$slug = get_post_field( 'post_name' );
	if ( ! in_array( $slug, array( 'merci-achat', 'merci', 'merci-rdv' ), true ) ) return $content;
	return $content . '<div style="margin:26px auto;max-width:520px;text-align:center;padding:18px;border:1px solid rgba(212,180,92,.4);border-radius:16px;">' . do_shortcode( '[ag_avis_bouton]' ) . '</div>';
} );
