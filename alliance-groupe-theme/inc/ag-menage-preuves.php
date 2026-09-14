<?php
/**
 * ag-menage-preuves.php — passage unique : mettre hors ligne l'etude de cas
 * fabriquee sur L.A Environnement.
 *
 * L'article « Etude de cas : comment un paysagiste a multiplie ses devis par 4
 * en 4 mois » est invente de bout en bout — titre, chronologie mois par mois,
 * « +320 % de devis », « Top 3 Google local », et une meta description qui
 * affirme « L.A Environnement est passe de 3 devis par mois a 15 ». Aucun de
 * ces chiffres ne peut etre produit si un prospect les demande, et ils sont
 * attribues a une entreprise reelle, nommee, joignable.
 *
 * Decision de Fabrice, 03/09/2026, deja appliquee ailleurs dans le theme :
 * « Une section absente vaut mieux qu'une section fausse. »
 *
 * Pourquoi un module plutot qu'une correction de la source : `ag-import.php`
 * SAUTE les articles qui existent deja (`if ( $ex ) { $skip++; continue; }`).
 * Le fichier source a bien ete retire du depot et du manifeste — ce qui
 * empeche toute reimportation — mais l'article deja publie, lui, reste en
 * ligne tant que personne n'y touche.
 *
 * Le passage est BORNE : un seul article, designe par son slug, passe en
 * BROUILLON. Aucune suppression : Fabrice reste maitre de son contenu et peut
 * le republier d'un clic s'il n'est pas d'accord. La vraie etude de cas, elle,
 * existe et tient debout : /realisation-elagage.
 *
 * Il tourne une seule fois (option `ag_menage_preuves`). Pour le rejouer :
 * supprimer cette option, ou incrementer AG_MENAGE_PREUVES_VER.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Version du passage : l'incrementer rejoue le menage. */
if ( ! defined( 'AG_MENAGE_PREUVES_VER' ) ) { define( 'AG_MENAGE_PREUVES_VER', 1 ); }

/** Le slug de l'article fabrique. */
if ( ! defined( 'AG_MENAGE_PREUVES_SLUG' ) ) { define( 'AG_MENAGE_PREUVES_SLUG', 'etude-cas-paysagiste-multiplie-devis-par-4' ); }

if ( ! function_exists( 'ag_menage_preuves_go' ) ) {
	/**
	 * Met l'article fabrique hors ligne. Ne supprime rien.
	 *
	 * @return array Lignes de compte rendu.
	 */
	function ag_menage_preuves_go() {
		$post = get_page_by_path( AG_MENAGE_PREUVES_SLUG, OBJECT, 'post' );

		if ( ! $post ) {
			return array( 'Article « etude de cas paysagiste » introuvable : rien a faire.' );
		}
		if ( 'publish' !== $post->post_status ) {
			return array( 'Article « etude de cas paysagiste » deja hors ligne (statut : ' . $post->post_status . ').' );
		}

		$ok = wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ), true );
		if ( is_wp_error( $ok ) ) {
			return array( 'ECHEC de la mise hors ligne : ' . $ok->get_error_message() . ' — a faire a la main.' );
		}

		return array(
			'Article « ' . $post->post_title . ' » repasse en BROUILLON : chiffres invérifiables attribués à un client réel.',
			'Il n\'est pas supprimé — Articles > Brouillons pour le relire ou le republier.',
			'La vraie étude de cas est en ligne : /realisation-elagage',
		);
	}
}

/* Passage unique, au chargement de l'admin — jamais pendant une visite. */
add_action( 'admin_init', function () {
	if ( (int) get_option( 'ag_menage_preuves', 0 ) >= AG_MENAGE_PREUVES_VER ) { return; }
	$journal = ag_menage_preuves_go();
	update_option( 'ag_menage_preuves', AG_MENAGE_PREUVES_VER, false );
	update_option( 'ag_menage_preuves_journal', $journal, false );
	if ( function_exists( 'ag_log_msg' ) ) {
		foreach ( $journal as $l ) { ag_log_msg( 'Menage preuves: ' . $l ); }
	}
} );

/* Compte rendu montre une fois : un menage silencieux est un menage invisible. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$journal = get_option( 'ag_menage_preuves_journal', array() );
	if ( empty( $journal ) || ! is_array( $journal ) ) { return; }
	echo '<div class="notice notice-info is-dismissible"><p><strong>Ménage des preuves fabriquées :</strong></p><ul style="margin-left:18px;list-style:disc;">';
	foreach ( $journal as $l ) { echo '<li>' . esc_html( $l ) . '</li>'; }
	echo '</ul><p>Ce message ne reviendra plus.</p></div>';
	delete_option( 'ag_menage_preuves_journal' );
} );
