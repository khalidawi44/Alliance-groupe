<?php
/**
 * Réalisations (portfolio) — liste éditable des sites créés.
 *
 * La section « Nos projets récents » (template-parts/realisations.php) affiche :
 *  - 2 projets vitrines codés en dur (Anna Photo, L.A Environnement),
 *  - + tous les projets ajoutés ICI par l'admin (option `ag_portfolio`),
 * chacun avec un lien « Voir le projet » ET un lien « Voir sur Google »
 * (fiche Google, généré depuis le nom + la ville si non fourni).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Projets ajoutés à la main (normalisés pour le template). */
if ( ! function_exists( 'ag_portfolio_projects' ) ) {
	function ag_portfolio_projects() {
		$rows = get_option( 'ag_portfolio', array() );
		if ( ! is_array( $rows ) ) return array();
		$out = array();
		foreach ( $rows as $r ) {
			if ( empty( $r['title'] ) ) continue;
			$title  = (string) $r['title'];
			$google = trim( (string) ( $r['google'] ?? '' ) );
			if ( '' === $google ) {
				$google = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( $title . ' ' . ( $r['city'] ?? '' ) ) );
			}
			$out[] = array(
				'id'     => sanitize_title( $title ) . '-' . substr( md5( (string) ( $r['url'] ?? $title ) ), 0, 5 ),
				'title'  => $title,
				'url'    => trim( (string) ( $r['url'] ?? '' ) ) ?: '#',
				'img'    => trim( (string) ( $r['img'] ?? '' ) ),
				'google' => $google,
				'tags'   => array_filter( array_map( 'trim', explode( ',', (string) ( $r['tags'] ?? '' ) ) ) ),
				'desc'   => (string) ( $r['desc'] ?? '' ),
				'stats'  => array_filter( array_map( 'trim', explode( ',', (string) ( $r['stats'] ?? '' ) ) ) ),
			);
		}
		return $out;
	}
}

/* ------------------------------------------------------------------ Admin */
add_action( 'admin_menu', function () {
	$parent = menu_page_url( 'ag-hub', false ) ? 'ag-hub' : 'options-general.php';
	add_submenu_page( $parent, 'Réalisations', '🏆 Réalisations', 'manage_options', 'ag-portfolio', 'ag_portfolio_render' );
}, 23 );

add_action( 'admin_post_ag_portfolio_add', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
	check_admin_referer( 'ag_portfolio_add' );
	$rows   = (array) get_option( 'ag_portfolio', array() );
	$rows[] = array(
		'title'  => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
		'url'    => esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ),
		'img'    => esc_url_raw( wp_unslash( $_POST['img'] ?? '' ) ),
		'google' => esc_url_raw( wp_unslash( $_POST['google'] ?? '' ) ),
		'city'   => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
		'tags'   => sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) ),
		'stats'  => sanitize_text_field( wp_unslash( $_POST['stats'] ?? '' ) ),
		'desc'   => sanitize_textarea_field( wp_unslash( $_POST['desc'] ?? '' ) ),
		'ts'     => time(),
	);
	update_option( 'ag_portfolio', $rows, false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-portfolio', 'added' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_ag_portfolio_del', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
	check_admin_referer( 'ag_portfolio_del' );
	$i    = (int) ( $_POST['i'] ?? -1 );
	$rows = (array) get_option( 'ag_portfolio', array() );
	if ( isset( $rows[ $i ] ) ) { unset( $rows[ $i ] ); update_option( 'ag_portfolio', array_values( $rows ), false ); }
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-portfolio' ), admin_url( 'admin.php' ) ) );
	exit;
} );

if ( ! function_exists( 'ag_portfolio_render' ) ) {
	function ag_portfolio_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$rows = (array) get_option( 'ag_portfolio', array() );
		echo '<div class="wrap"><h1>🏆 Réalisations — « Nos projets récents »</h1>';
		if ( isset( $_GET['added'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Projet ajouté ✅. Il apparaît sur la page d\'accueil (section Réalisations).</p></div>';
		echo '<p style="max-width:820px;color:#50575e;">Ajoute ici <strong>tous les sites que tu as créés</strong>. Chacun s\'affiche sur la page d\'accueil avec un bouton <strong>« Voir le projet »</strong> et <strong>« Voir sur Google »</strong> (sa fiche). Laisse « Lien fiche Google » vide = généré automatiquement depuis le nom + la ville.</p>';

		// Formulaire d'ajout.
		echo '<h2>➕ Ajouter un projet</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="max-width:820px;background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:16px;">';
		wp_nonce_field( 'ag_portfolio_add' );
		echo '<input type="hidden" name="action" value="ag_portfolio_add">';
		$f = function ( $label, $name, $ph = '', $type = 'text' ) {
			echo '<p><label style="display:block;font-weight:600;margin-bottom:3px;">' . esc_html( $label ) . '</label>';
			if ( 'textarea' === $type ) echo '<textarea name="' . esc_attr( $name ) . '" rows="3" class="large-text" placeholder="' . esc_attr( $ph ) . '"></textarea>';
			else echo '<input type="text" name="' . esc_attr( $name ) . '" class="large-text" placeholder="' . esc_attr( $ph ) . '">';
			echo '</p>';
		};
		$f( 'Nom du projet / client *', 'title', 'Ex : Anna Photo' );
		$f( 'Adresse du site (URL)', 'url', 'https://…' );
		$f( 'Ville (pour la fiche Google)', 'city', 'Nantes' );
		$f( 'Lien fiche Google (optionnel)', 'google', 'Laisser vide = généré automatiquement' );
		$f( 'Image (URL) — optionnel', 'img', 'https://… (sinon une carte texte s\'affiche)' );
		$f( 'Étiquettes (séparées par des virgules)', 'tags', 'Photographie, Blog WordPress, Portfolio' );
		$f( 'Chiffres clés (séparés par des virgules)', 'stats', '+180% trafic, 23 articles' );
		$f( 'Description', 'desc', 'Une phrase ou deux sur le projet.', 'textarea' );
		submit_button( 'Ajouter à mes réalisations' );
		echo '</form>';

		// Liste existante.
		echo '<h2 style="margin-top:24px;">Mes projets (' . count( $rows ) . ')</h2>';
		if ( ! $rows ) { echo '<p><em>Aucun projet ajouté pour l\'instant (les 2 vitrines Anna Photo / L.A Environnement restent affichées).</em></p>'; }
		else {
			echo '<table class="widefat striped" style="max-width:900px;"><thead><tr><th>Nom</th><th>Site</th><th>Google</th><th>Étiquettes</th><th></th></tr></thead><tbody>';
			foreach ( $rows as $i => $r ) {
				echo '<tr><td><strong>' . esc_html( $r['title'] ?? '' ) . '</strong></td>';
				echo '<td>' . ( ! empty( $r['url'] ) ? '<a href="' . esc_url( $r['url'] ) . '" target="_blank">ouvrir</a>' : '—' ) . '</td>';
				echo '<td>' . ( ! empty( $r['google'] ) ? '<a href="' . esc_url( $r['google'] ) . '" target="_blank">fiche</a>' : 'auto' ) . '</td>';
				echo '<td>' . esc_html( $r['tags'] ?? '' ) . '</td>';
				echo '<td><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Supprimer ce projet ?\');" style="margin:0;">';
				wp_nonce_field( 'ag_portfolio_del' );
				echo '<input type="hidden" name="action" value="ag_portfolio_del"><input type="hidden" name="i" value="' . (int) $i . '">';
				echo '<button class="button button-small button-link-delete">🗑️</button></form></td></tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}
}

/* ==================================================================
 *  ÉTUDE DE CAS « GWEN SERVICES » SUR /realisation-gwen
 *  ------------------------------------------------------------------
 *  L'aperçu « Ce qu'on livre, pour de vrai » de l'accueil cinématique
 *  et la carte Gwen de « Nos projets récents » pointent tous les deux
 *  vers /realisation-gwen. Plutôt que d'obliger à créer une page à la
 *  main dans WordPress (et de renvoyer une 404 tant que ce n'est pas
 *  fait), on intercepte la 404 sur cette URL et on sert le gabarit
 *  templates/page-realisation-gwen.php.
 *
 *  Rien n'est écrit en base, aucune règle de réécriture à vider. Si une
 *  vraie page est créée un jour avec ce slug, WordPress la sert et ce
 *  bloc ne se déclenche jamais (is_404() est alors faux).
 * ================================================================== */

if ( ! function_exists( 'ag_is_realisation_gwen' ) ) {
	/** Sommes-nous sur l'étude de cas servie par ce module ? */
	function ag_is_realisation_gwen() {
		static $oui = null;
		if ( null !== $oui ) {
			return $oui;
		}
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( $home && 0 === strpos( $path, $home ) ) {
			$path = substr( $path, strlen( $home ) );
		}
		$oui = ( 'realisation-gwen' === trim( $path, '/' ) );
		return $oui;
	}
}

/* Titre, description et partage propres. */
add_filter( 'pre_get_document_title', function ( $titre ) {
	return ag_is_realisation_gwen()
		? 'Gwen Services — aide à domicile à Nantes | Réalisation Alliance Groupe'
		: $titre;
}, 20 );

add_action( 'wp_head', function () {
	if ( ! ag_is_realisation_gwen() ) {
		return;
	}
	$desc = 'Site complet livré en 5 jours pour une auxiliaire de vie à Nantes : conception, textes, images générées sur mesure, référencement local et sécurité.';
	$img  = get_stylesheet_directory_uri() . '/assets/images/realisations/gwen-maquette.jpg';
	$url  = home_url( '/realisation-gwen' );
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:type" content="article">' . "\n";
	echo '<meta property="og:title" content="Gwen Services — une réalisation Alliance Groupe">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 2 );

/* On sert le gabarit à la place de la 404. */
add_action( 'template_redirect', function () {
	if ( is_admin() || ! is_404() || ! ag_is_realisation_gwen() ) {
		return;
	}
	$gabarit = get_stylesheet_directory() . '/templates/page-realisation-gwen.php';
	if ( ! file_exists( $gabarit ) ) {
		return; // gabarit absent : on laisse la 404 normale.
	}
	global $wp_query;
	$wp_query->is_404 = false;
	status_header( 200 );
	include $gabarit;
	exit;
}, 5 );
