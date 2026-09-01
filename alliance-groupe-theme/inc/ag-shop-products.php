<?php
/**
 * AG Shop Products — pages produit PROPRES pour les templates WordPress,
 * conformes à Google Merchant Center (Shopping / free listings).
 *
 * Problème résolu : le flux Merchant pointait vers les pages marketing
 * /wordpress-{métier} (template gratuit + upsell services + « appel »), que
 * Google a classées « affilié / page qui redirige ailleurs ». Ici on crée,
 * pour chaque template, une vraie fiche PRODUIT :
 *   - un seul produit, prix clair (69 €), image, description ;
 *   - bouton « Acheter » vers le paiement sécurisé Stripe (lien existant) ;
 *   - livraison (licence par email) + retours (pages /livraison, /retours) ;
 *   - schema Product JSON-LD (prix cohérent avec le flux).
 *
 * Slugs créés : /template-premium-{métier}. Le flux Merchant pointe ici.
 * Création idempotente + versionnée ; contenu dynamique (prix/bouton) rendu
 * au filtre the_content pour rester la source unique et cohérente.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_SHOP_VER', 1 );

/* ── Prix unique du template Premium ─────────────────────────────────────── */
if ( ! function_exists( 'ag_shop_price' ) ) {
	function ag_shop_price() {
		return function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69;
	}
}

/* ── Catalogue des 6 templates (source unique : pages + schema) ───────────── */
if ( ! function_exists( 'ag_shop_products' ) ) {
	function ag_shop_products() {
		return array(
			'avocat' => array(
				'name'  => 'Avocat',
				'img'   => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1200&q=85',
				'desc'  => "Thème WordPress Premium clé en main pour cabinet d'avocats, juriste ou notaire : domaines d'expertise, prise de rendez-vous conforme RGPD, honoraires transparents, design sobre et crédible. Compatible avec tous les thèmes AG Starter.",
			),
			'restaurant' => array(
				'name'  => 'Restaurant',
				'img'   => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=85',
				'desc'  => "Thème WordPress Premium pour restaurant, bistrot, bar ou café : carte élégante éditable, réservation, commande en ligne sans commission, horaires, galerie. Design or & noir gastronomique.",
			),
			'artisan' => array(
				'name'  => 'Artisan',
				'img'   => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1200&q=85',
				'desc'  => "Thème WordPress Premium pour artisan du bâtiment (plombier, électricien, menuisier, maçon) : prestations, zones d'intervention, demande de devis, galerie de réalisations, avis clients.",
			),
			'coach' => array(
				'name'  => 'Coach',
				'img'   => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=1200&q=85',
				'desc'  => "Thème WordPress Premium pour coach, consultant ou thérapeute : accompagnements, séances, témoignages, prise de rendez-vous en ligne, blog. Design premium et rassurant.",
			),
			'barber' => array(
				'name'  => 'Barber Shop',
				'img'   => 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=1200&q=85',
				'desc'  => "Thème WordPress Premium pour barbershop, coiffeur ou salon urbain : tarifs, prestations, prise de rendez-vous, galerie, horaires. Design urbain moderne.",
			),
			'association' => array(
				'name'  => 'Association',
				'img'   => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1200&q=85',
				'desc'  => "Thème WordPress Premium pour association, mouvement ou ONG : manifeste, événements, groupes locaux, dons, espace adhérents. Design engageant.",
			),
		);
	}
}

/** Image produit : capture réelle du template si dispo, sinon illustration. */
if ( ! function_exists( 'ag_shop_product_img' ) ) {
	function ag_shop_product_img( $slug, $fallback ) {
		if ( function_exists( 'ag_template_gallery_images' ) ) {
			$gal = ag_template_gallery_images( $slug );
			if ( ! empty( $gal[0]['url'] ) ) return $gal[0]['url'];
		}
		return $fallback;
	}
}

/** Lien de paiement sécurisé (Stripe existant), sinon repli contact. */
if ( ! function_exists( 'ag_shop_buy_url' ) ) {
	function ag_shop_buy_url( $slug ) {
		$u = (string) get_option( 'ag_stripe_business_url', 'STRIPE_PLACEHOLDER' );
		if ( '' !== $u && 'STRIPE_PLACEHOLDER' !== $u && preg_match( '#^https://#i', $u ) ) {
			return array( $u, true ); // paiement direct
		}
		return array( add_query_arg( array( 'produit' => 'template-' . $slug ), home_url( '/contact' ) ), false );
	}
}

/* ── Création idempotente des pages produit ──────────────────────────────── */
if ( ! function_exists( 'ag_shop_ensure_pages' ) ) {
	function ag_shop_ensure_pages() {
		if ( (int) get_option( 'ag_shop_pages_done', 0 ) >= AG_SHOP_VER ) return;
		if ( ! function_exists( 'wp_insert_post' ) ) return;
		foreach ( ag_shop_products() as $slug => $p ) {
			$page_slug = 'template-premium-' . $slug;
			if ( get_page_by_path( $page_slug ) ) continue;
			// Contenu propre = description produit (le prix/bouton/livraison sont
			// rendus au filtre the_content, dynamiques et cohérents avec le flux).
			$content = '<p>' . esc_html( $p['desc'] ) . '</p>';
			wp_insert_post( array(
				'post_title'   => 'Template WordPress Premium — ' . $p['name'],
				'post_name'    => $page_slug,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => 1,
			) );
		}
		update_option( 'ag_shop_pages_done', AG_SHOP_VER );
	}
}
add_action( 'admin_init', 'ag_shop_ensure_pages' );
add_action( 'init', function () {
	if ( (int) get_option( 'ag_shop_pages_done', 0 ) < AG_SHOP_VER ) ag_shop_ensure_pages();
}, 99 );

/** Le slug courant correspond-il à une page produit ? Renvoie [slug_metier, def] ou false. */
if ( ! function_exists( 'ag_shop_current' ) ) {
	function ag_shop_current() {
		if ( ! is_page() ) return false;
		$slug = (string) get_post_field( 'post_name' );
		if ( 0 !== strpos( $slug, 'template-premium-' ) ) return false;
		$metier = substr( $slug, strlen( 'template-premium-' ) );
		$prods  = ag_shop_products();
		if ( ! isset( $prods[ $metier ] ) ) return false;
		return array( $metier, $prods[ $metier ] );
	}
}

/* ── Rendu de la fiche produit (prix, bouton, livraison, retours) ─────────── */
add_filter( 'the_content', function ( $c ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) return $c;
	$cur = ag_shop_current();
	if ( ! $cur ) return $c;
	list( $metier, $p ) = $cur;
	$price = ag_shop_price();
	$img   = ag_shop_product_img( $metier, $p['img'] );
	list( $buy_url, $direct ) = ag_shop_buy_url( $metier );
	$demo  = home_url( '/wordpress-' . $metier ); // aperçu du thème (page démo)

	$box  = '<div class="ag-shop">';
	$box .= '<div class="ag-shop__media"><img src="' . esc_url( $img ) . '" alt="Template WordPress ' . esc_attr( $p['name'] ) . ' — aperçu" loading="eager"></div>';
	$box .= '<div class="ag-shop__buy">';
	$box .= '<div class="ag-shop__price"><span class="ag-shop__amount">' . (int) $price . ' €</span><span class="ag-shop__vat">paiement unique · licence à vie</span></div>';
	$box .= '<p class="ag-shop__avail">✔ En stock — livraison immédiate</p>';
	$box .= '<a class="ag-shop__btn" href="' . esc_url( $buy_url ) . '" rel="nofollow">Acheter maintenant — ' . (int) $price . ' €</a>';
	$box .= '<ul class="ag-shop__reassure">';
	$box .= '<li>📩 <strong>Livraison</strong> : lien de téléchargement + clé de licence par email sous 5 minutes.</li>';
	$box .= '<li>🔒 <strong>Paiement sécurisé</strong> par Stripe (carte bancaire).</li>';
	$box .= '<li>↩️ <strong>Retours</strong> : voir notre <a href="' . esc_url( home_url( '/retours' ) ) . '">politique de retour</a> et nos <a href="' . esc_url( home_url( '/livraison' ) ) . '">conditions de livraison</a>.</li>';
	$box .= '<li>🖥️ <a href="' . esc_url( $demo ) . '">Voir la démo du thème ' . esc_html( $p['name'] ) . ' →</a></li>';
	$box .= '</ul>';
	$box .= '</div></div>';

	$box .= '<div class="ag-shop__desc"><h2>Ce que contient le template Premium</h2>' . $c
		. '<ul class="ag-shop__feats">'
		. '<li>Design Premium abouti (animations, blocs Gutenberg premium, customizer étendu).</li>'
		. '<li>Page d\'accueil pré-remplie, 100 % en français, prête à éditer.</li>'
		. '<li>Responsive mobile, compatible Gutenberg, sans plugin requis.</li>'
		. '<li>Installation en quelques minutes + documentation.</li>'
		. '<li>Compatible avec tous les thèmes AG Starter.</li>'
		. '</ul></div>';

	return '<div class="ag-shop-page">' . $box . '</div>';
}, 18 );

/* ── Schema Product JSON-LD (prix cohérent avec le flux Merchant) ─────────── */
add_action( 'wp_head', function () {
	$cur = ag_shop_current();
	if ( ! $cur ) return;
	list( $metier, $p ) = $cur;
	$price = ag_shop_price();
	$img   = ag_shop_product_img( $metier, $p['img'] );
	$ld = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => 'Template WordPress Premium — ' . $p['name'],
		'image'       => array( $img ),
		'description' => $p['desc'],
		'brand'       => array( '@type' => 'Brand', 'name' => get_bloginfo( 'name' ) ?: 'Alliance Groupe' ),
		'category'    => 'Software > WordPress Theme',
		'offers'      => array(
			'@type'         => 'Offer',
			'price'         => number_format( (float) $price, 2, '.', '' ),
			'priceCurrency' => 'EUR',
			'availability'  => 'https://schema.org/InStock',
			'itemCondition' => 'https://schema.org/NewCondition',
			'url'           => get_permalink(),
			'seller'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ?: 'Alliance Groupe' ),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 7 );

/* ── Mise en beauté (CSS scoppé marque) ──────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! ag_shop_current() ) return;
	?>
<style>
.ag-shop-page{max-width:1040px;margin:0 auto;font-size:1.03rem;line-height:1.7}
.ag-shop{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start;margin:1em 0 2em}
.ag-shop__media img{width:100%;border-radius:16px;border:1px solid rgba(212,180,92,.25);display:block}
.ag-shop__buy{background:linear-gradient(160deg,#14141f,#0b0b13);border:1px solid rgba(212,180,92,.3);border-radius:18px;padding:22px 22px 24px}
.ag-shop__price{display:flex;flex-direction:column;gap:2px;margin-bottom:6px}
.ag-shop__amount{font-family:Georgia,serif;font-size:2.4rem;font-weight:700;color:#D4B45C;line-height:1}
.ag-shop__vat{color:#9a9aa5;font-size:.85rem}
.ag-shop__avail{color:#3fbf7f;font-weight:600;margin:.4em 0 1em}
.ag-shop__btn{display:block;text-align:center;padding:15px 24px;border-radius:999px;font-weight:800;text-decoration:none;color:#0a0a0f;background:linear-gradient(90deg,#D4B45C,#F37A1F);box-shadow:0 10px 24px rgba(243,122,31,.28);font-size:1.05rem}
.ag-shop__btn:hover{filter:brightness(1.07)}
.ag-shop__reassure{list-style:none;padding:0;margin:1.2em 0 0;display:grid;gap:9px;font-size:.92rem;color:#cdc6b8}
.ag-shop__reassure a{color:#e7c979}
.ag-shop__desc h2{color:#D4B45C;font-size:clamp(1.3rem,3vw,1.7rem);margin:1.4em 0 .4em}
.ag-shop__feats{list-style:none;padding:0;margin:1em 0;display:grid;gap:9px}
.ag-shop__feats li{position:relative;padding:11px 15px 11px 44px;background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.2);border-radius:12px}
.ag-shop__feats li:before{content:"✓";position:absolute;left:14px;top:11px;width:22px;height:22px;border-radius:50%;background:#D4B45C;color:#0a0a0f;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center}
@media(max-width:760px){.ag-shop{grid-template-columns:1fr}}
</style>
<?php
}, 8 );
