<?php
/**
 * SEO LOCAL — Fiche Google & schema LocalBusiness site-wide.
 *
 * Objectif : viser la 1re page Google (local pack + organique) pour
 * « création site web » / « template WordPress » à Nantes.
 *
 *  1. NAP centralisé (Nom / Adresse / Téléphone) via options `ag_nap_*`
 *     → cohérence partout (schema, footer, factures).
 *  2. JSON-LD ProfessionalService (Organization + LocalBusiness) émis
 *     sur l'accueil et la page contact, avec PostalAddress complète et
 *     aggregateRating UNIQUEMENT si une note Google RÉELLE existe
 *     (jamais de faux avis — réutilise ag_geo_google_data()).
 *  3. Écran d'aide « Fiche Google / Ads » : checklist GBP + extensions
 *     Google Ads pour le look sponsorisé (modèle concurrent Drakkar).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------ NAP options */
if ( ! function_exists( 'ag_nap_opt' ) ) {
	function ag_nap_opt( $key ) {
		$def = array(
			'name'    => 'Alliance Groupe',
			'street'  => '',                    // ex : 12 rue de la Paix (à compléter)
			'zip'     => '44200',
			'city'    => 'Nantes',
			'region'  => 'Loire-Atlantique',
			'country' => 'FR',
			'phone'   => '+33744829516',
			'email'   => 'contact@alliancegroupe-inc.com',
			'price'   => '€€',
			'hours'   => 'Mo-Fr 09:00-19:00',   // format schema.org openingHours
			'gbp'     => '',                    // URL de la fiche Google Business
			'maps'    => '',                    // URL Google Maps (place)
		);
		$v = get_option( 'ag_nap_' . $key, '' );
		return ( '' === $v || false === $v ) ? ( $def[ $key ] ?? '' ) : $v;
	}
}

/* ------------------------------------------------------ Réglages (Settings API) */
add_action( 'admin_init', function () {
	foreach ( array( 'name', 'street', 'zip', 'city', 'region', 'country', 'phone', 'email', 'price', 'hours', 'gbp', 'maps' ) as $k ) {
		register_setting( 'ag_nap_group', 'ag_nap_' . $k );
	}
} );

add_action( 'admin_menu', function () {
	$parent = menu_page_url( 'ag-hub', false ) ? 'ag-hub' : 'options-general.php';
	add_submenu_page( $parent, 'SEO local / Fiche Google', '📍 SEO local / Google', 'manage_options', 'ag-seo-local', 'ag_seo_local_render' );
}, 22 );

if ( ! function_exists( 'ag_seo_local_render' ) ) {
	function ag_seo_local_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$fields = array(
			'name'    => 'Nom de l\'entreprise (identique partout)',
			'street'  => 'Adresse (n° + rue)',
			'zip'     => 'Code postal',
			'city'    => 'Ville',
			'region'  => 'Région / département',
			'country' => 'Pays (code ISO, ex : FR)',
			'phone'   => 'Téléphone (format +33…)',
			'email'   => 'Email de contact',
			'price'   => 'Gamme de prix (€, €€, €€€)',
			'hours'   => 'Horaires (format schema : Mo-Fr 09:00-19:00)',
			'gbp'     => 'Lien de ta fiche Google Business Profile',
			'maps'    => 'Lien Google Maps (fiche « place »)',
		);
		$gd = function_exists( 'ag_geo_google_data' ) ? ag_geo_google_data() : array( 'rating' => 0, 'total' => 0 );
		?>
		<div class="wrap">
			<h1>📍 SEO local &amp; Fiche Google</h1>
			<p style="max-width:840px;color:#50575e;">Ces infos remplissent le <strong>schema LocalBusiness</strong> du site (accueil + contact) pour aider Google à te classer en local. La <strong>note Google</strong> n'est affichée que si elle est <strong>réelle</strong> (jamais de faux avis).</p>

			<div style="max-width:840px;margin:14px 0;padding:14px 18px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid <?php echo ( ! empty( $gd['total'] ) ? '#46b450' : '#dba617' ); ?>;">
				<strong>Note Google détectée :</strong>
				<?php if ( ! empty( $gd['total'] ) ) : ?>
					⭐ <?php echo esc_html( $gd['rating'] ); ?>/5 sur <?php echo (int) $gd['total']; ?> avis (réels, via l'API Places) → intégrés au schema. ✅
				<?php else : ?>
					aucune note récupérée. Renseigne <em>Place ID</em> + clé Places (Prospection → Chasse) et obtiens de <strong>vrais avis</strong>. En attendant, le schema est émis <strong>sans</strong> note (correct).
				<?php endif; ?>
			</div>

			<form method="post" action="options.php" style="max-width:840px;">
				<?php settings_fields( 'ag_nap_group' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( $fields as $k => $label ) : ?>
						<tr>
							<th scope="row"><label for="ag_nap_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td><input name="ag_nap_<?php echo esc_attr( $k ); ?>" id="ag_nap_<?php echo esc_attr( $k ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( ag_nap_opt( $k ) ); ?>"></td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button( 'Enregistrer' ); ?>
			</form>

			<hr style="max-width:840px;margin:24px 0;">
			<div style="max-width:840px;padding:16px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
				<h2 style="margin-top:0;">✅ Checklist « 1re page Google » (à faire côté Google)</h2>
				<ol style="line-height:1.9;">
					<li><strong>Créer / vérifier la fiche Google Business Profile</strong> (business.google.com) : type <strong>« zone desservie »</strong> (à domicile → <strong>adresse MASQUÉE</strong>, on affiche juste la zone : Nantes + agglomération, ~30 km) ; catégorie principale « <strong>Concepteur de sites Web</strong> » (+ secondaires : Agence de marketing, Service informatique) ; NAP <strong>identique</strong> à ci-dessus ; description avec mots-clés (« création de site internet Nantes ») ; <strong>photos réelles</strong> (logo, réalisations, toi au travail) ; horaires ; numéro <strong>07 44 82 95 16</strong> ; site relié. La vérification se fait par vidéo ou courrier.</li>
					<li><strong>Avis 5★ RÉELS</strong> : demande à chaque client satisfait (le site envoie déjà l'invitation après achat). <strong>Jamais de faux avis</strong> (illégal + pénalisé). Réponds à chaque avis.</li>
					<li><strong>Google Ads</strong> (look « sponsorisé » façon Drakkar) : campagne Search sur « création site web Nantes », « site internet artisan », « template WordPress avocat » + <strong>toutes les extensions</strong> : liens annexes (Templates, Sites Express, Audit gratuit), accroches, <strong>appel</strong> (ton 07), <strong>lieu</strong> (relié à la fiche GBP), image.</li>
					<li><strong>SEO organique local + longue traîne</strong> : pages géolocalisées (déjà en place via le module Geo) + articles « thème WordPress <métier> » ciblés Nantes (moins concurrentiel que le national).</li>
					<li><strong>Cohérence NAP</strong> partout (annuaires, réseaux, site) — c'est ~la moitié du classement local.</li>
				</ol>
				<?php if ( ag_nap_opt( 'gbp' ) ) : ?>
					<p><a class="button button-primary" href="<?php echo esc_url( ag_nap_opt( 'gbp' ) ); ?>" target="_blank" rel="noopener">Ouvrir ma fiche Google Business →</a></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

/* ------------------------------------------------- JSON-LD LocalBusiness (front) */
add_action( 'wp_head', function () {
	// L'accueil + la page contact portent le schema « marque ». Les pages Geo ont déjà le leur.
	if ( function_exists( 'ag_geo_is_target' ) && ag_geo_is_target() ) return;
	if ( ! ( is_front_page() || is_page( array( 'contact', 'a-propos', 'about' ) ) ) ) return;

	$addr = array_filter( array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => ag_nap_opt( 'street' ),
		'postalCode'      => ag_nap_opt( 'zip' ),
		'addressLocality' => ag_nap_opt( 'city' ),
		'addressRegion'   => ag_nap_opt( 'region' ),
		'addressCountry'  => ag_nap_opt( 'country' ),
	) );

	$ld = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ProfessionalService',
		'@id'         => home_url( '/#business' ),
		'name'        => ag_nap_opt( 'name' ),
		'url'         => home_url( '/' ),
		'telephone'   => ag_nap_opt( 'phone' ),
		'email'       => ag_nap_opt( 'email' ),
		'image'       => get_template_directory_uri() . '/assets/images/logo-carte.jpg',
		'logo'        => get_template_directory_uri() . '/assets/images/logo-carte.jpg',
		'priceRange'  => ag_nap_opt( 'price' ),
		'address'     => $addr,
		'areaServed'  => trim( ag_nap_opt( 'city' ) . ( ag_nap_opt( 'region' ) ? ', ' . ag_nap_opt( 'region' ) : '' ) ),
		'description' => 'Création de sites web professionnels, templates WordPress et audit de sécurité. ' . ag_nap_opt( 'city' ) . ' et région.',
		'knowsAbout'  => array( 'Création de site web', 'WordPress', 'Référencement local', 'Sécurité web', 'Templates métier' ),
	);

	// Horaires (format schema : "Mo-Fr 09:00-19:00").
	$hours = trim( (string) ag_nap_opt( 'hours' ) );
	if ( '' !== $hours ) $ld['openingHours'] = $hours;

	// sameAs : fiche Google + réseaux (aide Google à relier la marque).
	$gbp = ag_nap_opt( 'gbp' ) ?: ag_nap_opt( 'maps' );
	if ( ! $gbp && function_exists( 'ag_geo_review_url' ) ) $gbp = ag_geo_review_url();
	$ld['sameAs'] = array_values( array_filter( array( $gbp, 'https://www.youtube.com/@advisealliance2078' ) ) );

	// aggregateRating : UNIQUEMENT si note Google réelle.
	if ( function_exists( 'ag_geo_google_data' ) ) {
		$gd = ag_geo_google_data();
		if ( ! empty( $gd['total'] ) && ! empty( $gd['rating'] ) ) {
			$ld['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) $gd['rating'],
				'reviewCount' => (string) $gd['total'],
			);
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 12 );

/* Shortcode [ag_nap] : bloc coordonnées cohérent (réutilisable footer/contact). */
add_shortcode( 'ag_nap', function () {
	$street = ag_nap_opt( 'street' ); $zip = ag_nap_opt( 'zip' ); $city = ag_nap_opt( 'city' );
	$line   = trim( $street . ( $street && ( $zip || $city ) ? ', ' : '' ) . trim( $zip . ' ' . $city ) );
	$phone  = ag_nap_opt( 'phone' );
	$out  = '<div class="ag-nap" style="line-height:1.7;">';
	$out .= '<strong>' . esc_html( ag_nap_opt( 'name' ) ) . '</strong><br>';
	if ( $line )  $out .= esc_html( $line ) . '<br>';
	if ( $phone ) $out .= '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a><br>';
	$out .= '<a href="mailto:' . esc_attr( ag_nap_opt( 'email' ) ) . '">' . esc_html( ag_nap_opt( 'email' ) ) . '</a>';
	$out .= '</div>';
	return $out;
} );
