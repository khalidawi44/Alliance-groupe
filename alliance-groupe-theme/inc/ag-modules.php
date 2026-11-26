<?php
/**
 * ag-modules.php : CHARGEUR DE SECOURS des modules du thème.
 *
 * POURQUOI CE FICHIER EXISTE
 * Début novembre 2026, une fusion Git a remis une ancienne version de
 * functions.php : 49 modules n'étaient plus chargés et se sont éteints sans
 * aucun message (démarchage par mail, commande d'audit, pilote automatique,
 * tunnel, gardien, formulaire de contact...).
 * Les fichiers étaient toujours là, seule la ligne « require » avait disparu.
 *
 * CE QUE FAIT CE FICHIER
 * Il tient la liste de ces modules et les charge tous. Il est appelé depuis
 * DEUX endroits (functions.php et inc/ag-prospection.php) : si l'un des deux
 * est écrasé un jour, l'autre rallume tout. require_once garantit qu'un module
 * n'est jamais chargé deux fois.
 *
 * POUR AJOUTER UN MODULE : ajouter son nom à la liste ci-dessous. C'est tout.
 * POUR EN ÉTEINDRE UN : utiliser son interrupteur dans les réglages, pas ce fichier.
 * Contrôle à tout moment : bash scripts/check-modules.sh
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_modules_liste' ) ) {
	/**
	 * Liste des modules à charger, dans leur ordre d'origine.
	 * ag-refais-acces doit rester après ag-refais-mon-site (il s'y branche).
	 */
	function ag_modules_liste() {
		return array(
			// Site public, SEO, offres.
			'ag-plan-du-site',
			'ag-redirections',
			'ag-seo-noindex',
			'ag-offres',
			'ag-seo-localbiz',
			'ag-portfolio',
			// Tunnels sans appel : commande d'audit avec mandat, rendez-vous.
			'ag-mandat',
			// Prospection élargie et ambassadeurs.
			'ag-agences',
			'ag-appels-offres',
			'ag-missions',
			// Composants et atelier.
			'ag-composants',
			'ag-composants-market',
			'ag-atelier',
			// Outils IA (clé ag_ai_key requise pour ceux qui appellent l'IA).
			'ag-ia',
			'ag-journal-ia',
			'ag-refais-mon-site',
			'ag-refais-acces',
			'ag-concierge',
			'ag-devis-instant',
			'ag-nuit',
			'ag-visiteurs',
			'ag-menage-preuves',
			// Agent commercial : envoi des mails, démarchage, contrat, signature, réponses.
			// L'ordre compte : le moteur d'abord, les écrans de pilotage ensuite.
			'ag-smtp',
			'ag-equipe',
			'ag-surclassement',
			'ag-provenance',
			'ag-enrichir',
			'ag-closer',
			'ag-signature',
			'ag-juriste',
			'ag-negociateur',
			'ag-reponses',
			'ag-boite',
			'ag-relance-chaud',
			'ag-captures',
			'ag-directeur',
			'ag-commercial-admin',
			// Robot vocal : n'appelle que si sa clé, son numéro et son interrupteur sont en place.
			'ag-voice-webhook',
			// Meta (catalogue et pixel avec consentement).
			'ag-meta-feed',
			'ag-meta-pixel',
			'ag-reseautage',
			// Chaîne commerciale autonome : le pilote lance tous les suivants.
			'ag-autopilot',
			'ag-funnel',
			'ag-ab',
			'ag-scoreur',
			'ag-gardien',
			'ag-rapport-soir',
			'ag-cm',
			// Session de l'administrateur qui ne se déconnecte pas.
			'ag-session-persist',
		);
	}
}

// Chargement. Un fichier absent est noté, jamais fatal.
$GLOBALS['ag_modules_absents'] = isset( $GLOBALS['ag_modules_absents'] ) ? (array) $GLOBALS['ag_modules_absents'] : array();
foreach ( ag_modules_liste() as $ag_mod_nom ) {
	$ag_mod_fichier = get_stylesheet_directory() . '/inc/' . $ag_mod_nom . '.php';
	if ( file_exists( $ag_mod_fichier ) ) {
		require_once $ag_mod_fichier;
	} elseif ( ! in_array( $ag_mod_nom, $GLOBALS['ag_modules_absents'], true ) ) {
		$GLOBALS['ag_modules_absents'][] = $ag_mod_nom;
	}
}
unset( $ag_mod_nom, $ag_mod_fichier );

// Alerte visible dans l'administration si un fichier de module manque sur le serveur.
if ( ! function_exists( 'ag_modules_alerte' ) ) {
	function ag_modules_alerte() {
		$absents = isset( $GLOBALS['ag_modules_absents'] ) ? (array) $GLOBALS['ag_modules_absents'] : array();
		if ( empty( $absents ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p><strong>Modules éteints : fichier manquant sur le serveur.</strong> '
			. esc_html( implode( ', ', $absents ) )
			. '. Relance la SYNC GitHub (Apparence, SYNC GitHub, SYNC FICHIERS DU THÈME) pour les remettre.</p></div>';
	}
	add_action( 'admin_notices', 'ag_modules_alerte' );
}
