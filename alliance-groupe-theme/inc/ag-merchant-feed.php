<?php
/**
 * AG Merchant Feed — flux produits Google Shopping (RSS 2.0).
 *
 * Génère un flux XML à l'URL /google-merchant-feed.xml (ou
 * ?ag_merchant_feed=1) listant les 6 templates WordPress Premium comme
 * produits numériques téléchargeables (69 €), avec la vraie capture du
 * métier comme image produit et le lien vers la fiche dédiée.
 *
 * À enregistrer dans Google Merchant Center comme source de données
 * « récupération programmée » (scheduled fetch). Aucun OAuth nécessaire.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── Routage : /google-merchant-feed.xml ─────────────────────────────── */
add_action( 'init', function () {
	add_rewrite_rule( '^google-merchant-feed\.xml$', 'index.php?ag_merchant_feed=1', 'top' );
	// Flush une seule fois (après déploiement).
	if ( '2' !== get_option( 'ag_merchant_feed_rw' ) ) {
		flush_rewrite_rules( false );
		update_option( 'ag_merchant_feed_rw', '2' );
	}
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'ag_merchant_feed';
	return $vars;
} );

/* ── Résolution d'une image locale (1re existante, sinon bannière OG) ─── */
function ag_merchant_img( $candidates ) {
	$base = get_stylesheet_directory() . '/assets/images/';
	$url  = get_stylesheet_directory_uri() . '/assets/images/';
	foreach ( (array) $candidates as $rel ) {
		if ( file_exists( $base . $rel ) ) {
			return $url . $rel;
		}
	}
	return get_stylesheet_directory_uri() . '/assets/images/og-banner.png';
}

/* ── Catalogue produits (templates + personnalisé + sécurité) ────────── */
function ag_merchant_products() {
	$tpl = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69;
	$eur = function ( $n ) { return number_format( (float) $n, 2, '.', '' ) . ' EUR'; };

	$items = array();

	/* 1) TEMPLATES Premium (produits numériques téléchargeables) ───────── */
	$metiers = array(
		'avocat'      => array( 'Avocat', 'Site WordPress Premium clé en main pour cabinet d\'avocats, juriste ou notaire : domaines d\'expertise, prise de rendez-vous RGPD, honoraires transparents. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1200&q=85' ),
		'restaurant'  => array( 'Restaurant', 'Site WordPress Premium pour restaurant, bistrot, bar ou café : carte, réservation, privatisation, horaires. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=85' ),
		'artisan'     => array( 'Artisan', 'Site WordPress Premium pour artisan du bâtiment (plombier, électricien, menuisier, maçon) : prestations, zones, devis, réalisations. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1200&q=85' ),
		'coach'       => array( 'Coach', 'Site WordPress Premium pour coach, consultant ou thérapeute : accompagnements, séances, témoignages, prise de rendez-vous. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=1200&q=85' ),
		'barber'      => array( 'Barber Shop', 'Site WordPress Premium pour barbershop, coiffeur ou salon urbain : tarifs, prestations, file d\'attente, galerie. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=1200&q=85' ),
		'association' => array( 'Association', 'Site WordPress Premium pour association, mouvement ou ONG : manifeste, événements, groupes locaux, dons, adhérents. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1200&q=85' ),
	);
	foreach ( $metiers as $slug => $d ) {
		$img = $d[2];
		$gal = function_exists( 'ag_template_gallery_images' ) ? ag_template_gallery_images( $slug ) : array();
		if ( ! empty( $gal ) ) {
			$img = $gal[0]['url'];
		}
		$items[] = array(
			'id'          => 'tpl-premium-' . $slug,
			'title'       => 'Template WordPress Premium — ' . $d[0],
			'description' => $d[1],
			'link'        => home_url( '/wordpress-' . $slug ),
			'image'       => $img,
			'price'       => $eur( $tpl ),
			'category'    => 'Software &gt; Computer Software',
		);
	}

	/* 2) PERSONNALISÉ — création sur-mesure ───────────────────────────── */
	// Site personnalisé à votre métier (généré + ZIP, produit numérique).
	$items[] = array(
		'id'          => 'site-personnalise',
		'title'       => 'Site web personnalisé à votre métier',
		'description' => 'Nous générons votre site sur notre plus beau design, personnalisé à votre nom, votre métier et vos couleurs. Vous repartez avec un site prêt à installer. Idéal si votre métier n\'a pas encore son template.',
		'link'        => home_url( '/templates-wordpress#ag-creer-mon-site' ),
		'image'       => ag_merchant_img( array( 'templates/avocat/accueil-jour.jpg', 'team/1_bureau_naples.jpg' ) ),
		'price'       => $eur( $tpl ),
		'category'    => 'Software &gt; Computer Software',
	);
	// Packs création « Sites Express » (prestation à prix fixe).
	$express = array(
		'essentiel' => array( 'Création de site — Pack Essentiel', 490, 'Site vitrine professionnel à prix fixe, livré en quelques jours, sans rendez-vous : design sur-mesure, mobile, référencé Google.' ),
		'pro'       => array( 'Création de site — Pack Pro', 890, 'Site professionnel complet : plusieurs pages, SEO, formulaires, design sur-mesure. Livré clé en main, sans rendez-vous.' ),
		'boutique'  => array( 'Création de site — Pack Boutique', 1490, 'Boutique en ligne / site avancé : catalogue, paiement, design sur-mesure, référencement. Livré clé en main.' ),
	);
	foreach ( $express as $k => $e ) {
		$items[] = array(
			'id'          => 'express-' . $k,
			'title'       => $e[0],
			'description' => $e[2],
			'link'        => home_url( '/sites-express' ),
			'image'       => ag_merchant_img( array( 'parcours/creation.jpg', 'team/1_bureau_naples.jpg' ) ),
			'price'       => $eur( $e[1] ),
			'category'    => 'Software &gt; Computer Software',
		);
	}

	/* 3) SÉCURITÉ ─────────────────────────────────────────────────────── */
	$items[] = array(
		'id'          => 'securite-resilience',
		'title'       => 'Test de résilience ransomware',
		'description' => 'Simulation contrôlée et 100 % légale pour mesurer la résistance de votre système face à un ransomware, sans rien chiffrer. Rapport clair + plan d\'action. Sur mandat.',
		'link'        => home_url( '/resilience-ransomware' ),
		'image'       => ag_merchant_img( array( 'securite/hero-secu.jpg', 'parcours/audit.jpg', 'securite/menace.jpg' ) ),
		'price'       => $eur( 490 ),
		'category'    => 'Software &gt; Computer Software',
	);
	$items[] = array(
		'id'          => 'securite-audit',
		'title'       => 'Audit de sécurité de site web',
		'description' => 'Diagnostic de sécurité de votre site : failles, configuration, exposition de données. Rapport détaillé et recommandations priorisées.',
		'link'        => home_url( '/tester-mon-site' ),
		'image'       => ag_merchant_img( array( 'securite/menace.jpg', 'securite/hero-secu.jpg', 'parcours/audit.jpg' ) ),
		'price'       => $eur( 490 ),
		'category'    => 'Software &gt; Computer Software',
	);

	/**
	 * Permet d'ajuster le catalogue Merchant (ajouter/retirer/modifier
	 * des produits) sans toucher au code : add_filter('ag_merchant_products', ...).
	 */
	return apply_filters( 'ag_merchant_products', $items );
}

/* ── Sortie du flux XML ──────────────────────────────────────────────── */
add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'ag_merchant_feed' ) && ! isset( $_GET['ag_merchant_feed'] ) ) {
		return;
	}
	nocache_headers();
	header( 'Content-Type: application/xml; charset=utf-8' );

	$x = function ( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8' ); };

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
	echo "<channel>\n";
	echo '<title>' . $x( get_bloginfo( 'name' ) . ' — Templates WordPress' ) . "</title>\n";
	echo '<link>' . $x( home_url( '/templates-wordpress' ) ) . "</link>\n";
	echo "<description>Templates WordPress Premium prêts à installer.</description>\n";

	foreach ( ag_merchant_products() as $p ) {
		echo "<item>\n";
		echo '<g:id>' . $x( $p['id'] ) . "</g:id>\n";
		echo '<g:title><![CDATA[' . $p['title'] . "]]></g:title>\n";
		echo '<g:description><![CDATA[' . $p['description'] . "]]></g:description>\n";
		echo '<g:link>' . $x( esc_url_raw( $p['link'] ) ) . "</g:link>\n";
		echo '<g:image_link>' . $x( esc_url_raw( $p['image'] ) ) . "</g:image_link>\n";
		echo '<g:availability>in_stock</g:availability>' . "\n";
		echo '<g:price>' . $x( $p['price'] ) . "</g:price>\n";
		echo '<g:condition>new</g:condition>' . "\n";
		echo '<g:brand>' . $x( get_bloginfo( 'name' ) ) . "</g:brand>\n";
		echo '<g:identifier_exists>no</g:identifier_exists>' . "\n";
		$cat = ! empty( $p['category'] ) ? $p['category'] : 'Software &gt; Computer Software';
		echo '<g:google_product_category>' . $cat . '</g:google_product_category>' . "\n";
		echo '<g:product_type>Alliance Groupe</g:product_type>' . "\n";
		// Produits/services 100% EN LIGNE : on exclut la surface "magasin/local"
		// (sinon Google réclame un inventaire en magasin → erreur « inventaire manquant »).
		echo '<g:excluded_destination>local_inventory_ads</g:excluded_destination>' . "\n";
		echo '<g:excluded_destination>free_local_listings</g:excluded_destination>' . "\n";
		// On cible explicitement les surfaces en ligne.
		echo '<g:included_destination>Shopping_ads</g:included_destination>' . "\n";
		echo '<g:included_destination>Free_listings</g:included_destination>' . "\n";
		echo "</item>\n";
	}

	echo "</channel>\n</rss>\n";
	exit;
} );
