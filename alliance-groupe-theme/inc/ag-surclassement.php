<?php
/**
 * LA MONTÉE EN GAMME — guider chacun vers l'étape suivante
 * ---------------------------------------------------------------------------
 * L'idée de Fabrice : chaque message doit guider le contact vers ce qui est
 * MIEUX pour lui et rapporte le plus à la maison — par un LIEN vers une offre
 * claire (jamais une case pré-cochée, jamais un contrat caché dans un e-mail).
 *
 * Ce module donne UNE brique réutilisable : à partir de là où en est le
 * contact, il produit un appel à l'action brandé vers l'étape suivante, avec
 * son prix affiché et ce qu'elle apporte EN PLUS. Le contact clique s'il veut ;
 * l'engagement se fait ensuite par le vrai contrat (Camille + signature Margot).
 *
 * L'échelle, une seule source de vérité :
 *   Prospect / intéressé (rien acheté)  → « Ma maquette en ligne » 9,90 €/mois
 *   Maquette en ligne                    → Essentiel 490 €
 *   Essentiel 490 €                      → Pro 890 €
 *   Pro 890 €                            → Boutique 1 490 €
 *   Boutique 1 490 €                     → Sur-mesure (devis)
 *
 * ─────────────────────────────────────────────────────────────────────────
 * LES GARDE-FOUS (droit de la conso / RGPD) :
 *  · JAMAIS de case pré-cochée : l'accord est un acte positif du client.
 *  · JAMAIS de nouveau contrat glissé dans un e-mail : on renvoie vers une
 *    offre claire, avec le prix, qui passe ensuite par la signature normale.
 *  · Le prix vient toujours de la source unique (ag-offres), jamais réécrit.
 *  · Chaque étape dit ce qu'elle APPORTE, pas ce qui manque : on guide, on ne
 *    fait pas peur.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_SURCL_VER' ) ) { define( 'AG_SURCL_VER', '1.0.0' ); }

/* ── 1. L'échelle ────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_surcl_prochain' ) ) {
	/**
	 * L'étape suivante pour un contact, selon où il en est.
	 *
	 * @param string $niveau clé du niveau actuel :
	 *        '' ou 'prospect' | 'maquette' | 'essentiel' | 'pro' | 'boutique' | 'sur-mesure'
	 * @return array|null  array( titre, prix, url, apporte[] ) ou null (déjà au sommet)
	 */
	function ag_surcl_prochain( $niveau = '' ) {
		$packs = function_exists( 'ag_sites_express_packs' ) ? (array) ag_sites_express_packs() : array();
		$prix  = function ( $cle, $def ) use ( $packs ) {
			return isset( $packs[ $cle ]['prix'] ) ? (string) $packs[ $cle ]['prix'] : $def;
		};
		$boost = function_exists( 'ag_refais_boost_offre' ) ? (array) ag_refais_boost_offre() : array();

		switch ( (string) $niveau ) {
			case '':
			case 'prospect':
			case 'interesse':
				return array(
					'titre'   => 'Votre maquette mise en ligne',
					'prix'    => ( $boost['prix'] ?? '9,90 €' ) . ( $boost['unite'] ?? '/mois' ),
					'url'     => home_url( '/refais-mon-site' ),
					'apporte' => array(
						'Votre site en ligne sous 24 h, sans rien installer',
						'Nom de domaine et certificat de sécurité compris',
						'Chaque mois payé déduit si vous passez au site complet',
					),
				);
			case 'maquette':
				return array(
					'titre'   => 'Le pack Essentiel — votre vrai site',
					'prix'    => $prix( 'essentiel', '490 €' ),
					'url'     => home_url( '/sites-express#essentiel' ),
					'apporte' => array( 'Un site vitrine complet, à votre marque', 'Formulaire de contact et Google Maps', 'Référencement de base pour être trouvé' ),
				);
			case 'essentiel':
				return array(
					'titre'   => 'Le pack Pro — pour développer',
					'prix'    => $prix( 'pro', '890 €' ),
					'url'     => home_url( '/sites-express#pro' ),
					'apporte' => array( 'Jusqu\'à 6 pages', 'Blog et prise de rendez-vous en ligne', 'SEO renforcé sur Google' ),
				);
			case 'pro':
				return array(
					'titre'   => 'Le pack Boutique — vendre en ligne',
					'prix'    => $prix( 'boutique', '1 490 €' ),
					'url'     => home_url( '/sites-express#boutique' ),
					'apporte' => array( 'Boutique e-commerce (paiement en ligne)', 'Jusqu\'à 30 produits', 'Gestion des stocks et des commandes' ),
				);
			case 'boutique':
				return array(
					'titre'   => 'Un site sur-mesure',
					'prix'    => 'sur devis',
					'url'     => home_url( '/sur-mesure' ),
					'apporte' => array( 'Conçu entièrement pour votre activité', 'Fonctionnalités spécifiques', 'Accompagnement dédié' ),
				);
			default:
				return null; // sur-mesure : plus haut, on ne surclasse pas
		}
	}
}

/* ── 2. Le bloc brandé pour un e-mail ────────────────────────────────── */

if ( ! function_exists( 'ag_surcl_cta' ) ) {
	/**
	 * Un appel à l'action harmonisé à glisser dans un e-mail brandé. Guide vers
	 * l'étape suivante, prix affiché, bénéfices — un LIEN, jamais un contrat.
	 *
	 * @param string $niveau  où en est le contact
	 * @param string $prenom  optionnel
	 * @return string  HTML (vide si déjà au sommet)
	 */
	function ag_surcl_cta( $niveau = '', $prenom = '' ) {
		$p = ag_surcl_prochain( $niveau );
		if ( ! $p ) { return ''; }

		$plus = '';
		foreach ( array_slice( (array) $p['apporte'], 0, 3 ) as $a ) {
			$plus .= '<li style="margin:0 0 4px;">' . esc_html( $a ) . '</li>';
		}

		$html  = '<div style="margin:26px 0 6px;padding:20px 22px;background:#0f0f16;border:1px solid rgba(212,180,92,.28);border-radius:14px;">';
		$html .= '<div style="font-family:Arial,sans-serif;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#D4B45C;margin-bottom:6px;">L\'étape d\'après pour vous</div>';
		$html .= '<div style="font-family:Georgia,serif;font-size:19px;color:#fff;margin-bottom:4px;">' . esc_html( (string) $p['titre'] ) . '</div>';
		$html .= '<div style="font-family:Arial,sans-serif;font-size:15px;color:#D4B45C;font-weight:bold;margin-bottom:10px;">' . esc_html( (string) $p['prix'] ) . '</div>';
		$html .= '<ul style="font-family:Arial,sans-serif;font-size:14px;color:#c9c9d3;line-height:1.6;padding-left:20px;margin:0 0 6px;">' . $plus . '</ul>';
		$html .= ( function_exists( 'ag_email_button' ) ? ag_email_button( 'Voir cette offre', (string) $p['url'] )
			: '<p><a href="' . esc_url( (string) $p['url'] ) . '">Voir cette offre</a></p>' );
		$html .= '<div style="font-family:Arial,sans-serif;font-size:11px;color:#7a7a85;">Sans engagement à ce stade : vous découvrez l\'offre, vous décidez ensuite.</div>';
		$html .= '</div>';
		return $html;
	}
}

/* Filtre pratique : n'importe quel e-mail brandé peut réclamer le CTA
   d'après le niveau du destinataire, sans dépendre directement du module. */
add_filter( 'ag_email_upsell', function ( $vide, $niveau = '', $prenom = '' ) {
	return ag_surcl_cta( $niveau, $prenom );
}, 10, 3 );

/* ── 3. Deviner le niveau d'un contact depuis sa fiche ───────────────── */

if ( ! function_exists( 'ag_surcl_niveau_prospect' ) ) {
	/**
	 * À partir d'une fiche prospect/client, déduit le niveau pour choisir la
	 * bonne étape suivante. Prudent : sans indice d'achat, on reste « prospect »
	 * (on propose la première marche, pas un surclassement qui n'a pas de sens).
	 */
	function ag_surcl_niveau_prospect( $p ) {
		$pack = strtolower( (string) ( $p['pack'] ?? $p['offre'] ?? '' ) );
		foreach ( array( 'boutique', 'pro', 'essentiel', 'maquette' ) as $n ) {
			if ( false !== strpos( $pack, $n ) ) { return $n; }
		}
		return 'prospect';
	}
}
