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
	 * La micro-offre qui suit une maquette IA : « cette maquette, en ligne ».
	 *
	 * C'est le pas le plus court entre « c'est joli » et « j'ai payé ». Un
	 * artisan sort ce montant sans réunion, et TOUT ce qu'il paie est déduit du
	 * site complet : dire oui ne lui coûte rien.
	 *
	 * POURQUOI UN ABONNEMENT ET PAS UN PAIEMENT UNIQUE
	 * Un hébergement et un nom de domaine se renouvellent chaque année. Les
	 * financer avec un paiement unique, c'est promettre ce qu'on ne peut pas
	 * tenir. L'abonnement est la seule forme honnête — et c'est aussi ce qui
	 * fait vivre la maison entre deux ventes à 490 €.
	 *
	 * CE QUE ÇA NOUS COÛTE VRAIMENT (relevé le 03/09/2026)
	 * Un .fr se renouvelle 7,79 € HT/an chez OVH, soit 0,78 €/mois, plus une
	 * part d'hébergement mutualisé déjà payé. Environ 1 € par mois et par
	 * client. Le nom de domaine peut donc être COMPRIS — et c'est justement là
	 * que les gros hébergeurs rattrapent le client la deuxième année.
	 *
	 * Le prix vit ici, avec les autres : un prix ne doit exister qu'à un seul
	 * endroit. Le lien de paiement se règle comme les trois packs
	 * (option `ag_refais_boost_url`) et doit être un lien d'ABONNEMENT mensuel.
	 * Tant qu'il est vide, le bouton renvoie vers /contact plutôt que vers une
	 * page de paiement morte.
	 */
	function ag_refais_boost_offre() {
		$paiement = trim( (string) get_option( 'ag_refais_boost_url', '' ) );
		return array(
			'nom'        => 'Ma maquette en ligne',
			'prix'       => get_option( 'ag_refais_boost_prix', '9,90 €' ),
			'unite'      => '/mois',
			'delai'      => 'En ligne sous 24 h',
			'desc'       => 'On met cette maquette en ligne pour toi. Tu n\'installes rien, tu n\'écris rien.',
			'engagement' => '3 mois minimum, puis sans engagement. Tu arrêtes quand tu veux.',
			'deduit'     => 'Chaque mois payé est déduit de ton site complet. Tu ne paies jamais deux fois.',
			'url'        => $paiement ? $paiement : home_url( '/contact' ),
			'payable'    => (bool) $paiement,
			'feats'      => array(
				'La maquette mise en ligne telle que tu viens de la voir',
				'Ton nom de domaine compris, renouvellement inclus',
				'Tes vrais textes, ton logo, tes coordonnées, ton téléphone',
				'Hébergement et certificat de sécurité compris',
				'Un interlocuteur joignable au téléphone, à Nantes',
				'Tout ce que tu paies déduit si tu passes au site complet',
			),
			/*
			 * Ce qu'on peut dire des autres — et RIEN de plus.
			 * Relevé le 03/09/2026 sur des comparatifs publics : IONOS 6 €/mois
			 * (pack Now, hors offre d'appel à 1 €) et 12 €/mois (Creator) ;
			 * Wix à partir de 16,80 €/mois ; Squarespace dès 12 €/mois en
			 * engagement annuel. Renouvellement du nom de domaine : 7,79 € HT/an
			 * chez OVH, 11 à 15 €/an chez IONOS.
			 * À revérifier avant toute campagne : ces prix bougent, et une
			 * comparaison fausse se retourne contre nous.
			 */
			'compare'    => array(
				array(
					'eux'  => 'De 6 à 17 € par mois — et c\'est vous qui construisez le site',
					'nous' => 'Le site est fait. Vous n\'ouvrez aucun outil.',
				),
				array(
					'eux'  => 'Vous partez d\'un modèle vide, à remplir vous-même',
					'nous' => 'Vous partez de VOTRE maquette, écrite à partir de votre métier',
				),
				array(
					'eux'  => 'Le nom de domaine renouvelé 11 à 15 € par an, en plus',
					'nous' => 'Nom de domaine compris, renouvellement inclus',
				),
				array(
					'eux'  => 'Un formulaire de support et une file d\'attente',
					'nous' => 'Un numéro de téléphone, à Nantes',
				),
				array(
					'eux'  => 'Ce que vous payez est perdu si vous changez d\'avis',
					'nous' => 'Chaque mois payé est déduit de votre site complet',
				),
			),
		);
	}
}
