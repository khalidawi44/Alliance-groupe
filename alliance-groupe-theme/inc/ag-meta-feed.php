<?php
/**
 * AG Meta Feed — flux produits pour le catalogue Meta (Facebook / Instagram).
 *
 * Génère un flux XML à l'URL /meta-catalog-feed.xml (ou ?ag_meta_feed=1)
 * au format RSS 2.0 + namespace Google, que Meta Commerce Manager accepte
 * tel quel comme « source de données » en récupération programmée
 * (Catalogue → Sources de données → Flux de données → URL planifiée).
 *
 * Contenu : les produits déjà déclarés pour Google Merchant
 * (ag_merchant_products()) + les formules de maintenance mensuelles et la
 * zone ambassadeur, qui n'ont pas leur place dans le flux Google Shopping
 * (abonnements et licences internes y déclenchent des refus).
 *
 * Le flux Google existant (/google-merchant-feed.xml) n'est PAS modifié.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── Routage : /meta-catalog-feed.xml ────────────────────────────────── */
add_action( 'init', function () {
	add_rewrite_rule( '^meta-catalog-feed\.xml$', 'index.php?ag_meta_feed=1', 'top' );
	// Flush une seule fois (après déploiement).
	if ( '1' !== get_option( 'ag_meta_feed_rw' ) ) {
		flush_rewrite_rules( false );
		update_option( 'ag_meta_feed_rw', '1' );
	}
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'ag_meta_feed';
	return $vars;
} );

/* ── Produits propres au flux Meta (maintenance + zone) ──────────────── */
function ag_meta_extra_products() {
	$eur = function ( $n ) { return number_format( (float) $n, 2, '.', '' ) . ' EUR'; };
	$img = function ( $rel ) {
		return function_exists( 'ag_merchant_img' )
			? ag_merchant_img( array( $rel ) )
			: get_stylesheet_directory_uri() . '/assets/images/' . $rel;
	};

	$items = array();

	/* Formules de maintenance mensuelles ─────────────────────────────── */
	$maintenance = array(
		'serenite'    => array( 'Sérénité', 29, 'Hébergement, nom de domaine, sauvegardes automatiques et sécurité de votre site. Sans engagement.', 'produits/produit-serenite.jpg' ),
		'croissance'  => array( 'Croissance', 59, 'Tout le pack Sérénité, plus 30 minutes de modifications par mois et le suivi analytics de votre trafic.', 'produits/produit-croissance.jpg' ),
		'performance' => array( 'Performance', 99, 'Tout le pack Croissance, plus 1 heure de modifications par mois, optimisation SEO continue et rapport de performance.', 'produits/produit-performance.jpg' ),
	);
	foreach ( $maintenance as $k => $m ) {
		$items[] = array(
			'id'          => 'maintenance-' . $k,
			'title'       => 'Maintenance ' . $m[0] . ' — ' . $m[1] . ' € / mois',
			'description' => $m[2] . ' Abonnement mensuel de ' . $m[1] . ' €.',
			'link'        => home_url( '/maintenance' ),
			'image'       => $img( $m[3] ),
			'price'       => $eur( $m[1] ),
			'category'    => 'Software &gt; Computer Software',
			'product_type' => 'Maintenance',
		);
	}

	/* Zone ambassadeur supplémentaire ────────────────────────────────── */
	// Prix réel configuré dans Ambassadeurs → Zones (helper existant du thème).
	$zone = function_exists( 'ag_zone_price' ) ? (float) ag_zone_price() : (float) get_option( 'ag_zone_price', 49 );
	$items[] = array(
		'id'           => 'zone-ambassadeur',
		'title'        => 'Zone ambassadeur supplémentaire',
		'description'  => 'Zone géographique supplémentaire pour les ambassadeurs Alliance Groupe : exclusivité sur un département additionnel, réception des leads de la zone et outils de prospection inclus. Paiement unique.',
		'link'         => home_url( '/ambassadeurs' ),
		'image'        => $img( 'logo-carte-square.jpg' ),
		'price'        => $eur( $zone > 0 ? $zone : 49 ),
		'category'     => 'Software &gt; Computer Software',
		'product_type' => 'Programme Ambassadeurs',
	);

	return apply_filters( 'ag_meta_extra_products', $items );
}

/* ── Catalogue complet servi à Meta ──────────────────────────────────── */
function ag_meta_feed_products() {
	$base = function_exists( 'ag_merchant_products' ) ? ag_merchant_products() : array();
	$all  = array_merge( $base, ag_meta_extra_products() );

	return apply_filters( 'ag_meta_feed_products', $all );
}

/* ── Sortie du flux XML ──────────────────────────────────────────────── */
add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'ag_meta_feed' ) && ! isset( $_GET['ag_meta_feed'] ) ) {
		return;
	}
	nocache_headers();
	header( 'Content-Type: application/xml; charset=utf-8' );

	$x = function ( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8' ); };

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
	echo "<channel>\n";
	echo '<title>' . $x( get_bloginfo( 'name' ) . ' — Catalogue Meta' ) . "</title>\n";
	echo '<link>' . $x( home_url( '/' ) ) . "</link>\n";
	echo "<description>Offres Alliance Groupe : création de sites, maintenance, thèmes WordPress Premium.</description>\n";

	foreach ( ag_meta_feed_products() as $p ) {
		if ( empty( $p['id'] ) || empty( $p['link'] ) ) {
			continue;
		}
		$type = ! empty( $p['product_type'] ) ? $p['product_type'] : 'Alliance Groupe';
		echo "<item>\n";
		echo '<g:id>' . $x( $p['id'] ) . "</g:id>\n";
		echo '<g:title><![CDATA[' . $p['title'] . "]]></g:title>\n";
		echo '<g:description><![CDATA[' . $p['description'] . "]]></g:description>\n";
		echo '<g:link>' . $x( esc_url_raw( $p['link'] ) ) . "</g:link>\n";
		echo '<g:image_link>' . $x( esc_url_raw( $p['image'] ) ) . "</g:image_link>\n";
		echo '<g:availability>in stock</g:availability>' . "\n";
		echo '<g:condition>new</g:condition>' . "\n";
		echo '<g:price>' . $x( $p['price'] ) . "</g:price>\n";
		echo '<g:brand>' . $x( get_bloginfo( 'name' ) ) . "</g:brand>\n";
		echo '<g:product_type>' . $x( $type ) . "</g:product_type>\n";
		// Meta signale un avertissement d'import sans catégorie produit.
		$cat = ! empty( $p['category'] ) ? $p['category'] : 'Software &gt; Computer Software';
		echo '<g:google_product_category>' . $cat . "</g:google_product_category>\n";
		echo '<g:identifier_exists>no</g:identifier_exists>' . "\n";
		echo "</item>\n";
	}

	echo "</channel>\n</rss>\n";
	exit;
} );
