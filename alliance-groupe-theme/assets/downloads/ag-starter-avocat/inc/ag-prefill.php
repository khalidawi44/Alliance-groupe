<?php
/**
 * AG Prefill — pré-remplissage automatique du thème.
 *
 * Quand un ZIP est généré par le « Créateur de site » d'Alliance Groupe, on
 * glisse un fichier `ag-prefill.json` à la racine du thème. À la première
 * activation (ou au premier chargement admin), on applique ces valeurs aux
 * réglages du thème (nom du business, métier, slogan, couleur) pour que le
 * site soit DÉJÀ personnalisé — l'utilisateur a l'impression de l'avoir
 * créé chez nous.
 *
 * Idempotent : on n'applique qu'une seule fois (flag option), ensuite le
 * client est libre de tout modifier dans Apparence > Personnaliser.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_avocat_apply_prefill' ) ) :
	/**
	 * Lit ag-prefill.json (racine du thème) et applique les valeurs une fois.
	 */
	function ag_avocat_apply_prefill() {
		// Déjà appliqué ? on ne touche plus à rien.
		if ( get_option( 'ag_avocat_prefill_done' ) ) {
			return;
		}

		$file = get_template_directory() . '/ag-prefill.json';
		if ( ! file_exists( $file ) ) {
			return;
		}

		$raw  = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( (string) $raw, true );
		if ( ! is_array( $data ) ) {
			update_option( 'ag_avocat_prefill_done', 1 ); // fichier invalide : on évite de boucler.
			return;
		}

		$business = isset( $data['business'] ) ? sanitize_text_field( $data['business'] ) : '';
		$metier   = isset( $data['metier'] ) ? sanitize_text_field( $data['metier'] ) : '';
		$slogan   = isset( $data['slogan'] ) ? sanitize_text_field( $data['slogan'] ) : '';
		$accent   = isset( $data['accent'] ) ? sanitize_hex_color( $data['accent'] ) : '';
		$prefix   = isset( $data['prefix'] ) ? sanitize_text_field( $data['prefix'] ) : '';

		// Titre du site = nom du business.
		if ( $business ) {
			update_option( 'blogname', $business );
		}

		// Hero : préfixe (métier) + marque (nom du business) + slogan.
		// Ex. « Boucherie » / « Maison Martin ».
		$hero_prefix = $prefix ? $prefix : $metier;
		if ( $hero_prefix ) {
			set_theme_mod( 'ag_hero_prefix', $hero_prefix );
		}
		if ( $business ) {
			set_theme_mod( 'ag_hero_brand', $business );
		}
		if ( $slogan ) {
			set_theme_mod( 'ag_hero_subtitle', $slogan );
		}
		if ( $accent ) {
			set_theme_mod( 'ag_color_accent', $accent );
		}

		// Mémorise le métier (réutilisable par les plugins Premium/Business).
		if ( $metier ) {
			update_option( 'ag_avocat_metier', $metier );
			set_theme_mod( 'ag_coach_metier_nom', $metier );
		}

		update_option( 'ag_avocat_prefill_done', 1 );
	}
endif;

// À l'activation du thème ET en filet de sécurité au 1er chargement admin.
add_action( 'after_switch_theme', 'ag_avocat_apply_prefill' );
add_action( 'admin_init', 'ag_avocat_apply_prefill' );
