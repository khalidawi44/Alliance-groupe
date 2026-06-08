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

/* ── Données produits ────────────────────────────────────────────────── */
function ag_merchant_products() {
	$price = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69;

	// slug => [ nom, description, image de repli (si pas de capture locale) ]
	$metiers = array(
		'avocat'      => array( 'Avocat', 'Site WordPress Premium clé en main pour cabinet d\'avocats, juriste ou notaire : design navy & champagne, domaines d\'expertise, prise de rendez-vous RGPD, honoraires transparents. Téléchargement immédiat, installation en 2 minutes.', 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1200&q=85' ),
		'restaurant'  => array( 'Restaurant', 'Site WordPress Premium pour restaurant, bistrot, bar ou café : hero appétissant, carte, réservation, privatisation et horaires. Téléchargement immédiat, prêt à installer.', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=85' ),
		'artisan'     => array( 'Artisan', 'Site WordPress Premium pour artisan du bâtiment (plombier, électricien, menuisier, maçon) : prestations, zones d\'intervention, devis et réalisations. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1200&q=85' ),
		'coach'       => array( 'Coach', 'Site WordPress Premium pour coach, consultant, formateur ou thérapeute : présentation des accompagnements, séances, témoignages et prise de rendez-vous. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=1200&q=85' ),
		'barber'      => array( 'Barber Shop', 'Site WordPress Premium pour barbershop, coiffeur ou salon urbain : tarifs, prestations, file d\'attente et galerie. Téléchargement immédiat, prêt à installer.', 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=1200&q=85' ),
		'association' => array( 'Association', 'Site WordPress Premium pour association, mouvement ou ONG : manifeste, événements, groupes locaux, dons et adhérents. Téléchargement immédiat.', 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1200&q=85' ),
	);

	$items = array();
	foreach ( $metiers as $slug => $d ) {
		$img = $d[2];
		$gal = function_exists( 'ag_template_gallery_images' ) ? ag_template_gallery_images( $slug ) : array();
		if ( ! empty( $gal ) ) {
			$img = $gal[0]['url']; // capture locale réelle si dispo.
		}
		$items[] = array(
			'id'          => 'tpl-premium-' . $slug,
			'title'       => 'Template WordPress Premium — ' . $d[0],
			'description' => $d[1],
			'link'        => home_url( '/wordpress-' . $slug ),
			'image'       => $img,
			'price'       => number_format( (float) $price, 2, '.', '' ) . ' EUR',
		);
	}
	return $items;
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
		echo '<g:google_product_category>Software &gt; Computer Software</g:google_product_category>' . "\n";
		echo '<g:product_type>Templates WordPress &gt; Premium</g:product_type>' . "\n";
		echo "</item>\n";
	}

	echo "</channel>\n</rss>\n";
	exit;
} );
