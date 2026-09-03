<?php
/**
 * Template Name: Contrat Client
 *
 * Contrat de prestation / CGV affiché au client AVANT paiement.
 * MODÈLE à faire valider par un avocat. Versions par pays via ?pays=fr|it|ma.
 */
get_header();
$ag_lg = function_exists( 'ag_company_legal' ) ? ag_company_legal() : array(
	'raison' => 'Alliance Groupe', 'dirigeant' => 'la Direction', 'forme' => 'Entreprise individuelle',
	'siren' => '', 'siret' => '', 'tva' => '', 'rcs' => '', 'adresse' => '', 'email' => 'contact@alliancegroupe-inc.com', 'site' => 'alliancegroupe-inc.com',
);
$ag_pays_lib = isset( $_GET['pays'] ) ? sanitize_text_field( wp_unslash( $_GET['pays'] ) ) : 'France';
if ( function_exists( 'ag_countries' ) && ! in_array( $ag_pays_lib, ag_countries(), true ) ) $ag_pays_lib = 'France';
?>
<main id="ag-main-content">
	<section class="ag-section ag-section--onyx" style="padding-top:140px;">
		<div class="ag-container ag-container--narrow ag-legal">
			<span class="ag-tag">Document contractuel</span>
			<h1 class="ag-section__title" style="margin-bottom:10px;">Contrat de prestation &amp; CGV</h1>
			<p class="ag-section__desc">Création de site web — Alliance Groupe. Droit applicable : <strong><?php echo esc_html( $ag_pays_lib ); ?></strong>. À lire et accepter avant tout paiement.</p>

			<?php if ( 'France' !== $ag_pays_lib ) : ?>
				<p style="background:rgba(243,122,31,.12);border:1px solid rgba(243,122,31,.4);border-radius:10px;padding:14px 18px;">
					ℹDroit applicable choisi : <strong><?php echo esc_html( $ag_pays_lib ); ?></strong>. Le présent contrat s'applique avec ce droit pour droit applicable ; une version localisée définitive peut être validée par un avocat local.
				</p>
			<?php endif; ?>

			<h2>1. Parties</h2>
			<p><strong>Le Prestataire :</strong> <?php echo esc_html( $ag_lg['raison'] ); ?>, <?php echo esc_html( $ag_lg['forme'] ); ?>, représentée par <?php echo esc_html( $ag_lg['dirigeant'] ); ?> — SIREN <?php echo esc_html( $ag_lg['siren'] ?? '' ); ?>, <?php echo esc_html( $ag_lg['adresse'] ); ?>, <?php echo esc_html( $ag_lg['email'] ); ?>.</p>
			<p><strong>Le Client :</strong> la personne physique ou morale qui commande la prestation et accepte le présent contrat en cochant la case prévue avant paiement.</p>

			<h2>2. Objet</h2>
			<p>Création d'un site internet professionnel selon le pack choisi (Essentiel, Pro ou Boutique), incluant les fonctionnalités décrites sur la page Sites Express au jour de la commande.</p>

			<h2>3. Prix &amp; paiement</h2>
			<p>Les prix sont indiqués en euros, à prix fixe selon le pack. Le paiement s'effectue en ligne (PayPal). La production démarre après réception du paiement (ou du 1er versement) et du brief complété par le Client.</p>

			<h2>3 bis. Paiement en 4× sans frais — conditions</h2>
			<p>Le paiement en 4× sans frais est une <strong>facilité accordée sous conditions</strong> et n'est pas un droit automatique :</p>
			<ul>
				<li>Il est réservé aux paiements par <strong>carte bancaire nominative à débit (carte « classique » à puce, numéros en relief)</strong> émise par une banque. Les <strong>cartes prépayées, cartes de néo-banques, cartes virtuelles ou à autorisation systématique</strong> peuvent être <strong>refusées</strong> pour le 4×.</li>
				<li>Le Client autorise expressément le <strong>prélèvement automatique des 3 échéances restantes</strong> aux dates prévues.</li>
				<li>En cas d'<strong>échéance impayée</strong> (rejet, provision insuffisante, opposition non justifiée), la <strong>totalité du solde restant devient immédiatement exigible</strong>, et le Prestataire peut <strong>suspendre la prestation et/ou la mise en ligne</strong> du site jusqu'au règlement complet.</li>
			</ul>

			<h2>3 ter. Retard, impayé &amp; recouvrement</h2>
			<p>Le site et ses contenus <strong>restent la propriété du Prestataire jusqu'au paiement intégral</strong> ; aucun transfert de propriété ni mise en ligne définitive n'intervient avant règlement complet.</p>
			<p>En cas de non-paiement à l'échéance, après une <strong>relance restée sans effet sous 8 jours</strong>, sont dus de plein droit : le <strong>solde total</strong>, des <strong>intérêts de retard</strong> au taux légal applicable, et une <strong>indemnité forfaitaire de recouvrement</strong> (40 € pour les professionnels, là où la loi le prévoit). Le Prestataire se réserve le droit de <strong>suspendre l'accès</strong>, de <strong>reprendre/dépublier</strong> les éléments non payés et d'engager toute <strong>procédure de recouvrement</strong>, les frais raisonnables restant à la charge du Client défaillant.</p>
			<p>Le Client reconnaît que la prestation a une valeur dès son démarrage et qu'un défaut de paiement ne le dispense pas des sommes dues pour le travail déjà engagé.</p>

			<h2>4. Délais &amp; livraison</h2>
			<p>Les délais annoncés (en jours ouvrés) courent à compter de la réception du brief complet et des éléments nécessaires (textes, logo, photos). Le site est livré avec une vidéo de présentation ; les retouches raisonnables se font par écrit.</p>

			<h2>5. Obligations du Client</h2>
			<p>Le Client fournit des contenus licites dont il détient les droits, et garantit le Prestataire contre toute réclamation liée à ces contenus.</p>

			<h2>6. Droit de rétractation (consommateurs)</h2>
			<p>Conformément au droit applicable, le consommateur dispose en principe d'un délai de rétractation de 14 jours pour les contrats à distance. Toutefois, l'exécution d'une prestation pleinement réalisée, ou commencée avec l'accord exprès du consommateur avant la fin de ce délai, peut entraîner la renonciation à ce droit. En validant la commande et le brief, le Client demande expressément le démarrage immédiat de la prestation.</p>

			<h2>7. Propriété &amp; mise en ligne</h2>
			<p>Le site et son contenu deviennent la propriété du Client après paiement intégral. Le Prestataire peut citer la réalisation à titre de référence, sauf opposition écrite du Client.</p>

			<h2>8. Maintenance</h2>
			<p>La maintenance (sécurité, mises à jour, sauvegardes) fait l'objet d'un abonnement distinct, facultatif et résiliable à tout moment.</p>

			<h2>9. Données personnelles (RGPD)</h2>
			<p>Les données du Client sont traitées pour l'exécution du contrat et conservées de façon sécurisée. Le Client dispose d'un droit d'accès, de rectification et de suppression en écrivant à <?php echo esc_html( $ag_lg['email'] ); ?>.</p>

			<h2>10. Responsabilité</h2>
			<p>La responsabilité du Prestataire est limitée au montant de la prestation. Le Prestataire n'est pas responsable des indisponibilités liées à l'hébergement tiers ou à des contenus fournis par le Client.</p>

			<h2>11. Droit applicable &amp; litiges</h2>
			<p>Le présent contrat est régi par le droit en vigueur en <strong><?php echo esc_html( $ag_pays_lib ); ?></strong>. En cas de litige, les parties rechercheront une solution amiable avant toute action ; à défaut, les tribunaux compétents seront ceux prévus par la loi applicable.</p>

			<p style="margin-top:24px;color:var(--color-text-soft);font-size:.85rem;">Modèle — version <?php echo esc_html( strtoupper( $ag_pays ) ); ?>. Document à faire valider par un conseil juridique.</p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
