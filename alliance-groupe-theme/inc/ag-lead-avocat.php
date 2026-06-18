<?php
/**
 * AG — Capture de leads « Guide gratuit avocat » (entonnoir freemium).
 *
 * Rôle dans la stratégie : le thème gratuit AG Starter Avocat (wordpress.org)
 * ne fait QUE renvoyer vers une page de ce site mère. C'est ICI qu'on capte
 * l'email (zéro contrainte wordpress.org), qu'on crée un lead dans le CRM, et
 * qu'on envoie le guide + une invitation à découvrir le Premium.
 *
 * - Page publique « /guide-avocat » (template page-guide-avocat.php), créée auto.
 * - Formulaire → admin-post `ag_lead_avocat` → lead CRM (ag_prospect_add_record)
 *   + email du guide (ag_email_wrap) + notif interne (ag_push).
 * - Conforme déonto : on ne démarche pas l'avocat, c'est LUI qui demande le guide
 *   (opt-in clair). Aucune promesse, aucun faux avis.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Slug de la page de capture. */
function ag_lead_avocat_slug() {
	return 'guide-avocat';
}

/** URL publique de la page guide (avec UTM optionnels passés tels quels). */
function ag_lead_avocat_url() {
	$page = get_page_by_path( ag_lead_avocat_slug() );
	return $page ? get_permalink( $page ) : home_url( '/' . ag_lead_avocat_slug() );
}

/**
 * Le « bonus » : checklist actionnable, conforme déontologie (RIN/CNB).
 * Affichée après inscription ET envoyée par email.
 */
function ag_lead_avocat_guide_items() {
	return array(
		'Une page d\'accueil claire : nom du cabinet, barreau, domaines, bouton « Prendre rendez-vous » visible sans scroller.',
		'Une page « Domaines d\'expertise » : 1 page par domaine (divorce, pénal, affaires…) = autant de portes d\'entrée Google.',
		'Une page « Honoraires » transparente (1er RDV, forfait, temps passé) : rassure et filtre les bons dossiers.',
		'Une page « Le cabinet » : photo, parcours, barreau d\'inscription, langues — l\'humain crée la confiance.',
		'Un formulaire de prise de rendez-vous confidentiel (RGPD, secret professionnel) plutôt qu\'un simple numéro.',
		'Les mentions obligatoires : mentions légales, RGPD, barreau — exigées par la déontologie et rassurantes.',
		'Une fiche Google Business à jour reliée au site (sans solliciter d\'avis, conformément au RIN).',
	);
}

/** Traitement du formulaire de capture. */
function ag_lead_avocat_handle() {
	$back = ag_lead_avocat_url();
	if ( ! isset( $_POST['ag_lead_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_lead_nonce'] ), 'ag_lead_avocat' ) ) {
		wp_safe_redirect( add_query_arg( 'ag_lead', 'err', $back ) );
		exit;
	}
	// Honeypot anti-spam : champ caché qui doit rester vide.
	if ( ! empty( $_POST['site_web_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'ag_lead', 'ok', $back ) );
		exit;
	}
	$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
	$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$ville  = sanitize_text_field( wp_unslash( $_POST['ville'] ?? '' ) );
	$rgpd   = ! empty( $_POST['rgpd'] );

	if ( ! $prenom || ! is_email( $email ) || ! $rgpd ) {
		wp_safe_redirect( add_query_arg( 'ag_lead', 'champs', $back ) );
		exit;
	}

	// 1) Lead dans le CRM partagé (source dédiée pour le suivi de l'entonnoir).
	if ( function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'   => $prenom,
			'type'   => 'avocat',
			'city'   => $ville,
			'email'  => $email,
			'status' => 'interesse',
			'source' => 'lead-guide-avocat',
			'notes'  => 'A demandé le guide gratuit avocat (thème .org → site mère).',
		) );
	}

	// 2) Email du guide (brandé) + invitation douce à découvrir le Premium.
	ag_lead_avocat_send_email( $prenom, $email );

	// 3) Notif interne (Telegram/SMS) pour relancer à chaud.
	if ( function_exists( 'ag_push' ) ) {
		ag_push( '🎁 Lead guide avocat', sprintf( "%s (%s)%s — a demandé le guide gratuit.", $prenom, $email, $ville ? ' · ' . $ville : '' ) );
	}

	wp_safe_redirect( add_query_arg( 'ag_lead', 'ok', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_ag_lead_avocat', 'ag_lead_avocat_handle' );
add_action( 'admin_post_ag_lead_avocat', 'ag_lead_avocat_handle' );

/** Envoi de l'email brandé contenant le guide + lien Premium. */
function ag_lead_avocat_send_email( $prenom, $email ) {
	$items = ag_lead_avocat_guide_items();
	$li    = '';
	foreach ( $items as $i => $it ) {
		$li .= '<tr><td style="padding:8px 0;border-bottom:1px solid #eee;font-size:15px;color:#222;"><strong style="color:#B8860B;">' . ( $i + 1 ) . '.</strong> ' . esc_html( $it ) . '</td></tr>';
	}
	$premium_url = add_query_arg(
		array( 'utm_source' => 'email', 'utm_medium' => 'guide-avocat', 'utm_campaign' => 'premium' ),
		home_url( '/wordpress-avocat' )
	);
	$inner  = '<p style="font-size:16px;color:#222;">Bonjour ' . esc_html( $prenom ) . ',</p>';
	$inner .= '<p style="font-size:15px;color:#444;line-height:1.6;">Voici votre guide : <strong>les 7 pages qui font venir des clients à un cabinet d\'avocats</strong>. Chaque page est déjà prête dans le thème gratuit <em>AG Starter Avocat</em>.</p>';
	$inner .= '<table style="width:100%;border-collapse:collapse;margin:18px 0;">' . $li . '</table>';
	$inner .= '<p style="font-size:15px;color:#444;line-height:1.6;">Pour aller plus loin (recherche de jurisprudence Judilibre intégrée, espace client sécurisé où vos clients déposent leurs pièces, en-tête fixe avec votre téléphone…), le <strong>Pack Premium</strong> transforme la vitrine en véritable outil de cabinet.</p>';

	if ( function_exists( 'ag_email_wrap' ) && function_exists( 'ag_email_button' ) ) {
		$inner .= ag_email_button( 'Découvrir le Pack Premium', $premium_url );
		$html   = ag_email_wrap( '🎁 Votre guide : 7 pages qui attirent des clients', $inner );
	} else {
		$inner .= '<p><a href="' . esc_url( $premium_url ) . '">Découvrir le Pack Premium</a></p>';
		$html   = '<html><body>' . $inner . '</body></html>';
	}
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	wp_mail( $email, 'Votre guide gratuit — 7 pages qui attirent des clients', $html, $headers );
}

/** Création automatique de la page « Guide avocat » (une seule fois). */
function ag_lead_avocat_ensure_page() {
	if ( get_option( 'ag_lead_avocat_page_done' ) ) {
		return;
	}
	if ( ! get_page_by_path( ag_lead_avocat_slug() ) ) {
		wp_insert_post( array(
			'post_title'   => 'Guide gratuit — Site d\'avocat',
			'post_name'    => ag_lead_avocat_slug(),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'page_template' => 'templates/page-guide-avocat.php',
		) );
	}
	update_option( 'ag_lead_avocat_page_done', 1 );
}
add_action( 'init', 'ag_lead_avocat_ensure_page' );
