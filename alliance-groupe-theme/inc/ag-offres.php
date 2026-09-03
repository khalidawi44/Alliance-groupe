<?php
/**
 * AG Offres — SOURCE DE VERITE unique des packs Sites Express.
 *
 * REGLE DE REPERCUSSION : les prix, le contenu et les delais des packs ne
 * doivent exister qu'ICI. Avant, ils etaient ecrits en dur dans
 * templates/page-sites-express.php et, en image, dans les JPEG de l'accueil :
 * un prix change a un endroit devenait une promesse fausse ailleurs.
 * Toute page qui affiche un pack passe par ag_sites_express_packs().
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'AG_PACK_PLACEHOLDER' ) ) {
	define( 'AG_PACK_PLACEHOLDER', 'STRIPE_PLACEHOLDER' );
}

if ( ! function_exists( 'ag_sites_express_packs' ) ) {
	/**
	 * Les 3 packs Sites Express.
	 *
	 * 'feats' = la liste complete (page de vente).
	 * 'cles'  = les 3 arguments qui decident, pour les surfaces courtes
	 *           (accueil sur telephone). Ce sont des extraits de 'feats',
	 *           jamais des promesses supplementaires.
	 * 'delai' = le delai reellement annonce pour ce pack.
	 *
	 * @return array
	 */
	function ag_sites_express_packs() {
		return array(
			'essentiel' => array(
				'url'   => get_option( 'ag_stripe_express_essentiel_url', 'https://www.paypal.com/ncp/payment/YNEZPTYSYR6EU' ),
				'nom'   => 'Essentiel',
				'prix'  => '490 €',
				'desc'  => 'Le site vitrine qui te rend crédible.',
				'delai' => 'Livré en 5 jours',
				'feats' => array( 'Site 1 page (one-page) premium', 'Design sur-mesure à ta marque', 'Optimisé mobile + rapide', 'Formulaire de contact + Google Maps', 'Livré en 5 jours', 'Référencement de base' ),
				'cles'  => array( 'Site 1 page premium', 'Formulaire de contact + Google Maps', 'Référencement de base' ),
			),
			'pro' => array(
				'url'   => get_option( 'ag_stripe_express_pro_url', 'https://www.paypal.com/ncp/payment/EHEAJGG96G7SY' ),
				'nom'   => 'Pro',
				'prix'  => '890 €',
				'desc'  => 'Le site complet pour développer ton activité.',
				'delai' => 'Livré en 8 jours',
				'feats' => array( 'Jusqu\'à 6 pages', 'Design sur-mesure premium', 'SEO optimisé (Google)', 'Blog / actualités', 'Prise de RDV en ligne', 'Connexion réseaux sociaux', 'Livré en 8 jours' ),
				'cles'  => array( 'Jusqu\'à 6 pages', 'Blog / actualités', 'Prise de RDV en ligne' ),
				'star'  => true,
			),
			'boutique' => array(
				'url'   => get_option( 'ag_stripe_express_boutique_url', 'https://www.paypal.com/ncp/payment/N9DCC5VWTS5LY' ),
				'nom'   => 'Boutique',
				'prix'  => '1 490 €',
				'desc'  => 'Ta boutique en ligne, prête à vendre.',
				'delai' => 'Livré en 12 jours',
				'feats' => array( 'Boutique e-commerce (WooCommerce)', 'Jusqu\'à 30 produits intégrés', 'Paiement en ligne (CB, PayPal)', 'Design premium + SEO', 'Gestion stock & commandes', 'Formation à l\'utilisation', 'Livré en 12 jours' ),
				'cles'  => array( 'Boutique e-commerce (WooCommerce)', 'Jusqu\'à 30 produits intégrés', 'Paiement en ligne (CB, PayPal)' ),
			),
		);
	}
}

if ( ! function_exists( 'ag_refais_boost_offre' ) ) {
	/**
	 * La micro-offre qui suit une maquette IA : « cette maquette, en vrai ».
	 *
	 * C'est le pas le plus court entre « c'est joli » et « j'ai payé ». Un
	 * artisan sort ce montant sans réunion, et il est DÉDUIT de l'Essentiel :
	 * personne ne paie deux fois, donc dire oui ne coûte rien.
	 *
	 * Le prix vit ici, avec les autres — un prix ne doit exister qu'à un seul
	 * endroit. Le lien de paiement est réglable comme les trois packs
	 * (option `ag_refais_boost_url`) ; tant qu'il est vide, le bouton renvoie
	 * vers le contact plutôt que vers une page de paiement morte.
	 */
	function ag_refais_boost_offre() {
		$paiement = trim( (string) get_option( 'ag_refais_boost_url', '' ) );
		return array(
			'nom'      => 'Ma maquette en vrai',
			'prix'     => get_option( 'ag_refais_boost_prix', '49 €' ),
			'delai'    => 'En ligne sous 24 h',
			'desc'     => 'On met cette maquette en ligne, à ton nom, sur une vraie adresse.',
			'deduit'   => 'Déduits de ton site complet si tu continues.',
			'url'      => $paiement ? $paiement : home_url( '/contact' ),
			'payable'  => (bool) $paiement,
			'feats'    => array(
				'La maquette mise en ligne telle que tu la vois',
				'Tes vrais textes, ton logo, tes coordonnées',
				'Une adresse web à toi, en ligne sous 24 h',
				'Le montant déduit si tu passes au site complet',
			),
		);
	}
}
