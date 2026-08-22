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

/* Icône SVG par pôle (trait, hérite de la couleur or). */
if ( ! function_exists( 'ag_plan_icone' ) ) {
	function ag_plan_icone( $titre ) {
		$svg = array(
			'Sécurité'         => '<path d="M12 3l7 2.5v5.6c0 4.2-2.9 7.2-7 8.4-4.1-1.2-7-4.2-7-8.4V5.5z"/><path d="M9 12l2 2 4-4"/>',
			'Création de site' => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 8h18M7 6h.01M10 6h.01"/>',
			'Templates métier' => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>',
			'Outils IA'        => '<circle cx="12" cy="12" r="3"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M6 6l2.1 2.1M15.9 15.9 18 18M18 6l-2.1 2.1M8.1 15.9 6 18"/>',
			'Nos expertises'   => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/>',
			'Le studio'        => '<path d="M4 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6M9 10h.01M15 10h.01"/>',
			'Infos légales'    => '<path d="M6 3h9l3 3v15H6z"/><path d="M9 9h6M9 13h6M9 17h4"/>',
		);
		$path = isset( $svg[ $titre ] ) ? $svg[ $titre ] : '<circle cx="12" cy="12" r="9"/>';
		return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
	}
}

/* Shortcode : [ag_plan_du_site] */
add_shortcode( 'ag_plan_du_site', function () {
	$carte   = 'display:flex;flex-direction:column;background:rgba(212,180,92,.05);border:1px solid rgba(212,180,92,.24);border-radius:16px;padding:22px 22px 10px';
	$titre_s = 'display:flex;align-items:center;gap:11px;margin:0 0 14px;font-family:Georgia,serif;font-weight:500;font-size:1.15rem;color:inherit';
	$ic_s    = 'flex:none;width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:rgba(212,180,92,.12);color:#C9A24B';
	$li_s    = 'padding:8px 0;border-bottom:1px solid rgba(212,180,92,.12)';
	$a_s     = 'text-decoration:none;color:inherit;display:block';

	ob_start();
	echo '<div style="max-width:1080px;margin:0 auto">';
	echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr));gap:18px;align-items:start">';

	foreach ( ag_plan_poles() as $titre => $slugs ) {
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
		echo '<section style="' . esc_attr( $carte ) . '">';
		echo '<h2 style="' . esc_attr( $titre_s ) . '"><span style="' . esc_attr( $ic_s ) . '">' . ag_plan_icone( $titre ) . '</span>' . esc_html( $titre ) . '</h2>';
		echo '<ul style="list-style:none;padding:0;margin:0">';
		foreach ( $liens as $l ) {
			echo '<li style="' . esc_attr( $li_s ) . '"><a href="' . esc_url( $l[0] ) . '" style="' . esc_attr( $a_s ) . '">' . esc_html( $l[1] ) . '</a></li>';
		}
		echo '</ul></section>';
	}

	$posts = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( $posts ) {
		echo '<section style="' . esc_attr( $carte ) . ';grid-column:1/-1">';
		echo '<h2 style="' . esc_attr( $titre_s ) . '"><span style="' . esc_attr( $ic_s ) . '"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h11a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/><path d="M17 8h3v10a2 2 0 0 1-2 2M8 8h5M8 12h5"/></svg></span>Articles du blog</h2>';
		echo '<ul style="list-style:none;padding:0;margin:0;columns:2;column-gap:30px">';
		foreach ( $posts as $po ) {
			echo '<li style="' . esc_attr( $li_s ) . ';break-inside:avoid"><a href="' . esc_url( get_permalink( $po->ID ) ) . '" style="' . esc_attr( $a_s ) . '">' . esc_html( get_the_title( $po->ID ) ) . '</a></li>';
		}
		echo '</ul></section>';
	}

	echo '</div></div>';
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
