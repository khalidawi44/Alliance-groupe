<?php
/**
 * ag-juriste.php — le juriste interne d'Alliance Groupe.
 *
 * Il travaille POUR la maison, sur les contrats de la maison : il redige le
 * contrat a partir d'une affaire, il le relit avant qu'il parte, et il bloque
 * ce qui ne doit pas sortir. Ce n'est pas un conseil juridique donne a des
 * tiers — c'est la gestion de ses propres contrats, comme un service juridique
 * interne. La relecture du MODELE par un avocat reste un acte humain, verrouille
 * dans ag-signature.php.
 *
 * ── POURQUOI UNE RELECTURE AUTOMATIQUE SERT VRAIMENT ─────────────────────
 * Un contrat part vite et se rattrape lentement. Les fautes qui coutent cher
 * ne sont presque jamais des fautes de droit subtiles : c'est un SIRET absent,
 * un objet qui dit « etc. », un montant qui ne correspond a aucune offre, une
 * promesse de resultat glissee dans la description. Toutes se voient a la
 * lecture, et aucune ne se voit quand on envoie trente contrats par semaine.
 *
 * Deux niveaux :
 * · BLOQUANT — le contrat ne part pas. Ce sont les manques qui rendent le
 *   document inopposable ou trompeur.
 * · ALERTE — le contrat peut partir, mais quelqu'un devrait regarder.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_JURISTE_VER' ) ) { define( 'AG_JURISTE_VER', '1.0.0' ); }

/* ── 1. La relecture ─────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_promesses_interdites' ) ) {
	/**
	 * Ce que la maison ne promet JAMAIS, ou que ce soit ecrit.
	 *
	 * Une position sur Google ne se promet pas : elle depend d'un tiers. Ecrite
	 * dans un contrat elle devient une obligation de resultat — due ou
	 * remboursee. Ecrite dans un courriel de negociation, elle sera opposee le
	 * jour ou le client sera decu. Meme liste pour tout le monde : le juriste
	 * relit les contrats avec, le negociateur relit ses propres messages avec.
	 *
	 * @return array motif (en minuscules) => genre de promesse
	 */
	function ag_promesses_interdites() {
		return array(
			'1re page'          => 'position sur Google',
			'premiere page'     => 'position sur Google',
			'première page'     => 'position sur Google',
			'top 3'             => 'position sur Google',
			'1er sur google'    => 'position sur Google',
			'garanti'           => 'garantie de resultat',
			'garantie de result'=> 'garantie de resultat',
			'x fois plus'       => 'promesse chiffree',
			'% de clients'      => 'promesse chiffree',
			'doublera'          => 'promesse chiffree',
			'triplera'          => 'promesse chiffree',
			'assure de'         => 'garantie de resultat',
		);
	}
}

if ( ! function_exists( 'ag_juriste_relire' ) ) {
	/**
	 * Relit une affaire avant qu'elle devienne un contrat.
	 *
	 * @param array $d Les donnees de l'affaire (memes clefs que ag_sign_creer).
	 * @return array array( 'ok' => bool, 'bloquants' => string[], 'alertes' => string[] )
	 */
	function ag_juriste_relire( $d ) {
		$bloquants = array();
		$alertes   = array();

		/* ── Le prestataire ──────────────────────────────────────────────
		   Un contrat ou le prestataire n'est pas identifiable n'engage pas
		   grand-chose et se retourne contre celui qui l'a redige. Le SIRET et
		   l'adresse ne sont pas des ornements : ce sont eux qui disent QUI
		   s'engage. HANDOFF signale qu'ils sont vides dans la maison. */
		$lg = function_exists( 'ag_company_legal' ) ? (array) ag_company_legal() : array();
		foreach ( array(
			'raison'  => 'la raison sociale',
			'forme'   => 'la forme juridique',
			'adresse' => 'l\'adresse du siege',
			'siret'   => 'le SIRET',
		) as $clef => $libelle ) {
			if ( '' === trim( (string) ( $lg[ $clef ] ?? '' ) ) ) {
				$bloquants[] = 'Mentions du prestataire incompletes : ' . $libelle . ' manque. '
					. 'A renseigner dans les reglages de la maison avant tout envoi.';
			}
		}

		/* ── Le client ───────────────────────────────────────────────── */
		if ( '' === trim( (string) ( $d['client_nom'] ?? '' ) ) && '' === trim( (string) ( $d['client_entreprise'] ?? '' ) ) ) {
			$bloquants[] = 'Le client n\'est pas identifie : ni nom, ni raison sociale.';
		}
		if ( ! is_email( (string) ( $d['client_email'] ?? '' ) ) ) {
			$bloquants[] = 'Adresse email du client absente ou invalide — sans elle, pas de verification du signataire.';
		}
		if ( '' === trim( (string) ( $d['client_adresse'] ?? '' ) ) ) {
			$alertes[] = 'Adresse du client absente. Acceptable en B2B courant, genante en cas de litige.';
		}

		/* ── L'objet ─────────────────────────────────────────────────────
		   Un objet ouvert est la premiere cause de desaccord : le client
		   comprend « tout ce qui va avec », le prestataire comprend « ce qui
		   est ecrit ». On refuse les formules qui n'ont pas de fin. */
		$objet = trim( (string) ( $d['objet'] ?? '' ) );
		if ( mb_strlen( $objet ) < 12 ) {
			$bloquants[] = 'Objet du contrat vide ou trop vague pour delimiter la prestation.';
		}
		foreach ( array( 'etc', '…', '...', 'et plus', 'entre autres', 'notamment' ) as $ouvert ) {
			if ( false !== mb_stripos( $objet . ' ' . implode( ' ', (array) ( $d['details'] ?? array() ) ), $ouvert ) ) {
				$alertes[] = 'Le perimetre contient une formule ouverte (« ' . $ouvert . ' ») : '
					. 'tout ce qui n\'est pas ferme sera demande, et vous le devrez.';
				break;
			}
		}

		/* ── Le prix ─────────────────────────────────────────────────── */
		$montant = trim( (string) ( $d['montant'] ?? '' ) );
		if ( '' === $montant ) {
			$bloquants[] = 'Aucun montant : un contrat de prestation sans prix n\'est pas un contrat.';
		} else {
			$chiffre = (float) preg_replace( '/[^0-9.]/', '', str_replace( array( ' ', ',' ), array( '', '.' ), $montant ) );
			if ( $chiffre <= 0 ) {
				$bloquants[] = 'Le montant (« ' . $montant . ' ») ne se lit pas comme un prix.';
			} elseif ( function_exists( 'ag_sites_express_packs' ) ) {
				/* Un montant qui ne correspond a aucune offre connue n'est pas
				   interdit — mais c'est le genre d'ecart qui vient d'une faute
				   de frappe, et une faute de frappe sur un prix se decouvre au
				   moment de facturer. */
				$connus = array();
				foreach ( (array) ag_sites_express_packs() as $pack ) {
					$connus[] = (float) preg_replace( '/[^0-9]/', '', (string) ( $pack['prix'] ?? '' ) );
				}
				if ( $connus && ! in_array( $chiffre, $connus, true ) ) {
					$alertes[] = 'Le montant (' . $montant . ') ne correspond a aucun pack connu ('
						. implode( ' / ', array_map( 'intval', $connus ) ) . ' EUR). Verifiez que ce n\'est pas une coquille.';
				}
			}
		}
		if ( '' === trim( (string) ( $d['modalites'] ?? '' ) ) ) {
			$alertes[] = 'Modalites de paiement absentes : quand, en combien de fois, a quelle echeance.';
		}
		if ( '' === trim( (string) ( $d['delai'] ?? '' ) ) ) {
			$alertes[] = 'Aucun delai de livraison annonce. Sans delai ecrit, c\'est « raisonnable » qui s\'applique, et il se plaide.';
		}

		/* ── Les promesses ───────────────────────────────────────────────
		   Regle de la maison (03/09) : on n'ecrit jamais une preuve qu'on ne
		   peut pas produire. Dans un contrat c'est pire qu'ailleurs — une
		   promesse de resultat devient une obligation de resultat. */
		$tout = mb_strtolower( $objet . ' ' . implode( ' ', (array) ( $d['details'] ?? array() ) ) . ' ' . (string) ( $d['modalites'] ?? '' ) );
		foreach ( ag_promesses_interdites() as $motif => $genre ) {
			if ( false !== mb_strpos( $tout, $motif ) ) {
				$bloquants[] = 'Promesse de resultat detectee (« ' . $motif . ' » — ' . $genre . '). '
					. 'Ecrite dans un contrat, elle devient une obligation de resultat : vous la devrez, ou vous rembourserez.';
			}
		}

		/* ── Le consommateur ─────────────────────────────────────────── */
		$pro = ! empty( $d['client_entreprise'] );
		if ( ! $pro ) {
			$alertes[] = 'Le client semble etre un particulier : droit de retractation de 14 jours applicable. '
				. 'La clause est dans le contrat, mais verifiez que la livraison ne demarre pas avant la fin du delai '
				. 'sans accord ecrit de sa part.';
		}

		return array(
			'ok'        => empty( $bloquants ),
			'bloquants' => $bloquants,
			'alertes'   => $alertes,
		);
	}
}

/* ── 2. Rediger une affaire a partir d'un prospect ───────────────────── */

if ( ! function_exists( 'ag_juriste_affaire_depuis_prospect' ) ) {
	/**
	 * Compose une affaire a partir d'une fiche prospect et d'un pack.
	 *
	 * @param array  $p    La fiche prospect (CRM).
	 * @param string $pack essentiel | pro | boutique
	 * @return array|WP_Error
	 */
	function ag_juriste_affaire_depuis_prospect( $p, $pack = 'essentiel' ) {
		if ( ! function_exists( 'ag_sites_express_packs' ) ) {
			return new WP_Error( 'ag_juriste_packs', 'Les offres ne sont pas chargees.' );
		}
		$packs = (array) ag_sites_express_packs();
		if ( ! isset( $packs[ $pack ] ) ) {
			return new WP_Error( 'ag_juriste_pack', 'Offre inconnue : ' . $pack );
		}
		$o = $packs[ $pack ];

		return array(
			'client_entreprise' => (string) ( $p['name'] ?? '' ),
			'client_nom'        => (string) ( $p['contact_nom'] ?? '' ),
			'client_email'      => (string) ( $p['email'] ?? '' ),
			'client_tel'        => (string) ( $p['phone'] ?? '' ),
			'client_adresse'    => (string) ( $p['address'] ?? '' ),
			'objet'             => 'Creation d\'un site internet — formule ' . (string) ( $o['nom'] ?? $pack ),
			'details'           => (array) ( $o['feats'] ?? array() ),
			'montant'           => (string) ( $o['prix'] ?? '' ),
			'modalites'         => 'Acompte de 50 % a la commande, solde a la livraison.',
			'delai'             => (string) ( $o['delai'] ?? '' ),
		);
	}
}

/* ── 3. Le passage de relais : relire, puis envoyer ──────────────────── */

if ( ! function_exists( 'ag_juriste_envoyer_contrat' ) ) {
	/**
	 * Relit l'affaire et, si rien ne bloque, cree le contrat et l'envoie.
	 *
	 * C'est le seul chemin que l'equipe doit emprunter pour faire partir un
	 * contrat : appeler ag_sign_creer() directement contourne la relecture.
	 *
	 * @return array|WP_Error Le dossier cree, ou l'erreur / les blocages.
	 */
	function ag_juriste_envoyer_contrat( $affaire ) {
		$avis = ag_juriste_relire( $affaire );
		ag_juriste_journal( array(
			'ts'        => time(),
			'client'    => (string) ( $affaire['client_entreprise'] ?: ( $affaire['client_nom'] ?? '' ) ),
			'objet'     => (string) ( $affaire['objet'] ?? '' ),
			'verdict'   => $avis['ok'] ? 'envoye' : 'bloque',
			'bloquants' => $avis['bloquants'],
			'alertes'   => $avis['alertes'],
		) );

		if ( ! $avis['ok'] ) {
			if ( function_exists( 'ag_push' ) ) {
				ag_push( '⚖️ Contrat bloque par le juriste', implode( ' · ', array_slice( $avis['bloquants'], 0, 3 ) ) );
			}
			return new WP_Error( 'ag_juriste_bloque', implode( "\n", $avis['bloquants'] ), $avis );
		}
		if ( ! empty( $avis['alertes'] ) && function_exists( 'ag_push' ) ) {
			ag_push( '⚖️ Contrat envoye, avec reserves', implode( ' · ', array_slice( $avis['alertes'], 0, 3 ) ) );
		}
		if ( ! function_exists( 'ag_sign_creer' ) ) {
			return new WP_Error( 'ag_juriste_sign', 'Le module de signature n\'est pas charge.' );
		}
		return ag_sign_creer( $affaire );
	}
}

if ( ! function_exists( 'ag_juriste_journal' ) ) {
	/** Journal des relectures — 100 dernieres. */
	function ag_juriste_journal( $ligne = null ) {
		$j = (array) get_option( 'ag_juriste_journal', array() );
		if ( null === $ligne ) { return $j; }
		array_unshift( $j, $ligne );
		update_option( 'ag_juriste_journal', array_slice( $j, 0, 100 ), false );
		return $j;
	}
}

/* ── 4. L'ecran du juriste ───────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-hub', 'Juriste interne', '⚖️ Juriste interne',
		'manage_options', 'ag-juriste', 'ag_juriste_ecran'
	);
}, 31 );

if ( ! function_exists( 'ag_juriste_ecran' ) ) {
	function ag_juriste_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		/* Un controle a blanc : on relit une affaire type, sans rien envoyer.
		   C'est ce qui revele tout de suite les mentions manquantes de la
		   maison, avant qu'un vrai contrat les revele a un client. */
		$test = array(
			'client_entreprise' => 'Entreprise de controle',
			'client_email'      => 'controle@example.com',
			'objet'             => 'Creation d\'un site internet — formule Essentiel',
			'montant'           => '490 €',
			'modalites'         => 'Acompte de 50 % a la commande, solde a la livraison.',
			'delai'             => 'Livre en 5 jours',
		);
		$avis = ag_juriste_relire( $test );
		?>
		<div class="wrap">
			<h1>⚖️ Juriste interne</h1>
			<p class="description" style="max-width:820px">
				Il redige les contrats de la maison a partir d'une affaire, les relit avant qu'ils partent, et bloque ce qui
				ne doit pas sortir. Il travaille sur <strong>vos</strong> contrats — ce n'est pas du conseil donne a vos clients,
				et il ne remplace pas la relecture du modele par un avocat, qui reste un acte humain.
			</p>

			<h2>Controle a blanc</h2>
			<p class="description">Une affaire type est relue ci-dessous. Rien n'est envoye.</p>
			<?php if ( $avis['ok'] ) : ?>
				<div class="notice notice-success inline"><p><strong>Rien ne bloque.</strong> Un contrat type pourrait partir.</p></div>
			<?php else : ?>
				<div class="notice notice-error inline"><p><strong>Aucun contrat ne peut partir en l'etat :</strong></p>
					<ul style="list-style:disc;margin-left:20px">
						<?php foreach ( $avis['bloquants'] as $b ) : ?><li><?php echo esc_html( $b ); ?></li><?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $avis['alertes'] ) ) : ?>
				<div class="notice notice-warning inline"><p><strong>Points a regarder :</strong></p>
					<ul style="list-style:disc;margin-left:20px">
						<?php foreach ( $avis['alertes'] as $a ) : ?><li><?php echo esc_html( $a ); ?></li><?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<h2>Ce qu'il refuse de laisser passer</h2>
			<table class="widefat" style="max-width:860px">
				<tr><th style="width:36%">Controle</th><th>Pourquoi</th></tr>
				<tr><td>Mentions du prestataire (raison, forme, adresse, SIRET)</td>
					<td>Un contrat ou le prestataire n'est pas identifiable se retourne contre celui qui l'a redige.</td></tr>
				<tr><td>Identite et email du client</td>
					<td>Sans adresse verifiable, pas de preuve de qui a signe.</td></tr>
				<tr><td>Objet ferme, sans « etc. »</td>
					<td>Tout ce qui n'est pas ferme sera demande — et vous le devrez.</td></tr>
				<tr><td>Montant present et lisible</td>
					<td>Une prestation sans prix n'est pas un contrat. Un prix hors offres connues sent la coquille.</td></tr>
				<tr><td>Absence de promesse de resultat</td>
					<td>« 1re page Google », « garanti » : ecrit dans un contrat, cela devient une obligation de resultat.</td></tr>
				<tr><td>Droit de retractation si particulier</td>
					<td>14 jours. Demarrer la livraison avant la fin du delai sans accord ecrit se paie.</td></tr>
			</table>

			<h2>Journal des relectures</h2>
			<table class="widefat striped">
				<tr><th>Quand</th><th>Client</th><th>Objet</th><th>Verdict</th><th>Motifs</th></tr>
				<?php $j = ag_juriste_journal(); if ( empty( $j ) ) : ?>
					<tr><td colspan="5">Aucune relecture pour l'instant.</td></tr>
				<?php else : foreach ( array_slice( $j, 0, 50 ) as $l ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( 'd/m H:i', (int) ( $l['ts'] ?? 0 ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['client'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['objet'] ?? '' ) ); ?></td>
						<td><?php echo 'envoye' === ( $l['verdict'] ?? '' ) ? '✅ envoye' : '⛔ bloque'; ?></td>
						<td><small><?php
							$motifs = array_merge( (array) ( $l['bloquants'] ?? array() ), (array) ( $l['alertes'] ?? array() ) );
							echo esc_html( $motifs ? implode( ' · ', array_slice( $motifs, 0, 2 ) ) : '—' );
						?></small></td>
					</tr>
				<?php endforeach; endif; ?>
			</table>
		</div>
		<?php
	}
}
