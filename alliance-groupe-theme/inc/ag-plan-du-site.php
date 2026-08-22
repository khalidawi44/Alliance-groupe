<?php
/**
 * AG Plan du site — le parcours utilisateur, organisé par pôles.
 *
 * Le menu est réservé à l'essentiel ; ce plan réunit les pages que doit pouvoir
 * trouver un visiteur, rangées par pôle. Il est lié depuis le pied de page.
 *
 * ┌─ POUR AJOUTER / RETIRER UNE PAGE ─────────────────────────────────────────┐
 * │ Modifie le tableau $AG_PLAN_POLES ci-dessous : chaque pôle = un titre et   │
 * │ une liste de slugs de pages. Un slug qui ne correspond à aucune page       │
 * │ publiée est simplement ignoré (jamais de lien mort). Les pages internes    │
 * │ ou mortes (le-voyage, connexion, stripe-checkout-result, prospection…)     │
 * │ ne sont volontairement PAS listées.                                        │
 * └────────────────────────────────────────────────────────────────────────────┘
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_PLAN_VER', 2 );

/** Les pôles du parcours utilisateur. Édite librement (toi ou l'autre session). */
if ( ! function_exists( 'ag_plan_poles' ) ) {
	function ag_plan_poles() {
		return array(
			'Sécurité' => array(
				'tester-mon-site', 'audit-securite', 'resilience-ransomware', 'audit-seo',
			),
			'Création de site' => array(
				'sites-express', 'sur-mesure', 'refais-mon-site', 'maintenance', 'realisations',
			),
			'Templates métier' => array(
				'templates-wordpress', 'wordpress-avocat', 'wordpress-restaurant',
				'wordpress-artisan', 'wordpress-coach', 'wordpress-barber',
				'wordpress-association', 'wordpress-domicile',
			),
			'Outils IA' => array(
				'atelier', 'devis-instant', 'studio', 'fait-par-lia', 'composants',
			),
			'Nos expertises' => array(
				'services', 'service-creation-web', 'service-ia', 'service-seo',
				'service-publicite', 'service-branding', 'service-conseil',
			),
			'Le studio' => array(
				'a-propos', 'notre-fondateur', 'ambassadeurs', 'avis-clients', 'contact',
			),
			'Infos légales' => array(
				'mentions-legales', 'confidentialite', 'cookies', 'retours', 'livraison',
			),
		);
	}
}

/* Shortcode : [ag_plan_du_site] */
add_shortcode( 'ag_plan_du_site', function () {
	ob_start();
	echo '<div style="max-width:1000px;margin:0 auto">';

	foreach ( ag_plan_poles() as $titre => $slugs ) {
		// On ne garde que les pages réellement publiées.
		$liens = array();
		foreach ( $slugs as $slug ) {
			$pg = get_page_by_path( $slug );
			if ( $pg && 'publish' === get_post_status( $pg ) ) {
				$liens[] = array( get_permalink( $pg->ID ), get_the_title( $pg->ID ) );
			}
		}
		if ( empty( $liens ) ) {
			continue;
		}
		echo '<section style="margin:0 0 30px">';
		echo '<h2 style="font-family:Georgia,serif;font-weight:500;margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid rgba(212,180,92,.3)">' . esc_html( $titre ) . '</h2>';
		echo '<ul style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,230px),1fr));gap:2px 26px">';
		foreach ( $liens as $l ) {
			echo '<li style="padding:9px 0;border-bottom:1px solid rgba(212,180,92,.12)"><a href="' . esc_url( $l[0] ) . '" style="text-decoration:none;color:inherit;display:block">' . esc_html( $l[1] ) . '</a></li>';
		}
		echo '</ul></section>';
	}

	// Le blog : tous les articles publiés (dynamique).
	$posts = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( $posts ) {
		echo '<section style="margin:0 0 10px">';
		echo '<h2 style="font-family:Georgia,serif;font-weight:500;margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid rgba(212,180,92,.3)">Articles du blog</h2>';
		echo '<ul style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,260px),1fr));gap:2px 26px">';
		foreach ( $posts as $po ) {
			echo '<li style="padding:9px 0;border-bottom:1px solid rgba(212,180,92,.12)"><a href="' . esc_url( get_permalink( $po->ID ) ) . '" style="text-decoration:none;color:inherit;display:block">' . esc_html( get_the_title( $po->ID ) ) . '</a></li>';
		}
		echo '</ul></section>';
	}

	echo '</div>';
	return ob_get_clean();
} );

/* Page « plan-du-site » auto-créée (idempotent). */
add_action( 'admin_init', function () {
	if ( (int) get_option( 'ag_plan_page_done', 0 ) >= AG_PLAN_VER ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$existing = get_page_by_path( 'plan-du-site' );
	$content  = "<p>Toutes les pages utiles du site, rangées par pôle.</p>\n[ag_plan_du_site]";
	if ( $existing ) {
		wp_update_post( array( 'ID' => $existing->ID, 'post_content' => $content ) );
	} else {
		wp_insert_post( array(
			'post_title'   => 'Plan du site',
			'post_name'    => 'plan-du-site',
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id() ?: 1,
		) );
	}
	update_option( 'ag_plan_page_done', AG_PLAN_VER );
} );
