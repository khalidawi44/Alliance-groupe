<?php
/**
 * Template Name: Contrat Client
 *
 * Contrat de prestation / CGV affiché au client AVANT paiement.
 * ⚠️ MODÈLE à faire valider par un avocat. Versions par pays via ?pays=fr|it|ma.
 */
get_header();
$ag_lg = function_exists( 'ag_company_legal' ) ? ag_company_legal() : array(
	'raison' => 'Alliance Groupe', 'dirigeant' => 'Fabrice Doucet', 'forme' => 'Entreprise individuelle',
	'siren' => '', 'siret' => '', 'tva' => '', 'rcs' => '', 'adresse' => '', 'email' => 'contact@alliancegroupe-inc.com', 'site' => 'alliancegroupe-inc.com',
);
$ag_pays = isset( $_GET['pays'] ) ? sanitize_key( $_GET['pays'] ) : 'fr';
if ( ! in_array( $ag_pays, array( 'fr', 'it', 'ma' ), true ) ) $ag_pays = 'fr';
$ag_pays_lib = array( 'fr' => 'France', 'it' => 'Italie', 'ma' => 'Maroc' )[ $ag_pays ];
?>
<main id="ag-main-content">
	<section class="ag-section ag-section--onyx" style="padding-top:140px;">
		<div class="ag-container ag-container--narrow ag-legal">
			<span class="ag-tag">Document contractuel</span>
			<h1 class="ag-section__title" style="margin-bottom:10px;">Contrat de prestation &amp; CGV</h1>
			<p class="ag-section__desc">Création de site web — Alliance Groupe. Droit applicable : <strong><?php echo esc_html( $ag_pays_lib ); ?></strong>. À lire et accepter avant tout paiement.</p>

			<p style="background:rgba(212,180,92,.1);border:1px solid rgba(212,180,92,.35);border-radius:10px;padding:12px 16px;font-size:.9rem;">
				Choisir le droit applicable :
				<a href="?pays=fr" style="color:#D4B45C;">🇫🇷 France</a> ·
				<a href="?pays=it" style="color:#D4B45C;">🇮🇹 Italie</a> ·
				<a href="?pays=ma" style="color:#D4B45C;">🇲🇦 Maroc</a>
			</p>

			<?php if ( 'fr' !== $ag_pays ) : ?>
				<p style="background:rgba(243,122,31,.12);border:1px solid rgba(243,122,31,.4);border-radius:10px;padding:14px 18px;">
					⚠️ La version <strong><?php echo esc_html( $ag_pays_lib ); ?></strong> est en cours de finalisation avec un avocat local. En attendant, la version France ci-dessous s'applique à titre indicatif. (L'admin peut coller la version localisée définitive.)
				</p>
				<?php
				$ag_custom = get_option( 'ag_contract_cli_' . $ag_pays, '' );
				if ( $ag_custom ) { echo '<div>' . wp_kses_post( wpautop( $ag_custom ) ) . '</div>'; get_footer(); return; }
				?>
			<?php endif; ?>

			<h2>1. Parties</h2>
			<p><strong>Le Prestataire :</strong> <?php echo esc_html( $ag_lg['raison'] ); ?>, <?php echo esc_html( $ag_lg['forme'] ); ?>, représentée par <?php echo esc_html( $ag_lg['dirigeant'] ); ?> — SIREN <?php echo esc_html( $ag_lg['siren'] ?? '' ); ?>, <?php echo esc_html( $ag_lg['adresse'] ); ?>, <?php echo esc_html( $ag_lg['email'] ); ?>.</p>
			<p><strong>Le Client :</strong> la personne physique ou morale qui commande la prestation et accepte le présent contrat en cochant la case prévue avant paiement.</p>

			<h2>2. Objet</h2>
			<p>Création d'un site internet professionnel selon le pack choisi (Essentiel, Pro ou Boutique), incluant les fonctionnalités décrites sur la page Sites Express au jour de la commande.</p>

			<h2>3. Prix &amp; paiement</h2>
			<p>Les prix sont indiqués en euros, à prix fixe selon le pack. Le paiement s'effectue en ligne (PayPal), en une fois ou en 4× sans frais lorsque proposé. La production démarre après réception du paiement (ou du 1er versement) et du brief complété par le Client.</p>

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
