<?php
/**
 * Template Name: Contrat Ambassadeur
 *
 * Contrat d'apporteur d'affaires affiché aux candidats du programme.
 * Les infos légales viennent de ag_company_legal() (inc/ag-ambassadeurs.php).
 */
get_header();
$ag_lg = function_exists( 'ag_company_legal' ) ? ag_company_legal() : array(
	'raison' => 'Advise Alliance Group', 'dirigeant' => 'Fabrice Doucet',
	'forme' => '[forme]', 'siret' => '[SIRET]', 'adresse' => '[adresse]',
	'email' => 'contact@alliancegroupe-inc.com', 'site' => 'alliancegroupe-inc.com',
);
?>
<main id="ag-main-content">
    <section class="ag-section ag-section--onyx" style="padding-top:140px;">
        <div class="ag-container ag-container--narrow ag-legal">
            <span class="ag-tag">Document contractuel</span>
            <h1 class="ag-section__title" style="margin-bottom:10px;">Contrat d'apporteur d'affaires</h1>
            <p class="ag-section__desc">Programme Ambassadeurs — Alliance Groupe. À lire avant de rejoindre le programme.</p>

            <h2>Entre les soussignés</h2>
            <p><strong>La Société :</strong> <?php echo esc_html( $ag_lg['raison'] ); ?>, <?php echo esc_html( $ag_lg['forme'] ); ?>,
            représentée par <?php echo esc_html( $ag_lg['dirigeant'] ); ?>, SIRET <?php echo esc_html( $ag_lg['siret'] ); ?>,
            siège : <?php echo esc_html( $ag_lg['adresse'] ); ?> — ci-après « la Société ».</p>
            <p><strong>L'Apporteur :</strong> la personne physique majeure qui s'inscrit au programme et signe
            électroniquement le présent contrat (nom, prénom, date de naissance et adresse renseignés à l'inscription)
            — ci-après « l'Apporteur ».</p>

            <h2>Article 1 — Objet</h2>
            <p>L'Apporteur a pour mission de présenter à la Société des prospects (clients potentiels) intéressés par
            les services d'Alliance Groupe (création de sites, SEO, publicité, branding, IA, conseil). Il agit en
            <strong>apporteur d'affaires indépendant</strong>. Le présent contrat ne crée <strong>aucun lien de
            subordination ni contrat de travail</strong> entre les parties.</p>

            <h2>Article 2 — Identité et éligibilité</h2>
            <p>L'inscription requiert une identité réelle et exacte. La Société se réserve le droit de
            <strong>vérifier l'identité</strong> de l'Apporteur (pièce justificative) et de refuser ou suspendre toute
            inscription. L'Apporteur ne peut déclarer de vente qu'après <strong>validation de son compte</strong> par
            la Société.</p>

            <h2>Article 3 — Rémunération</h2>
            <p>L'Apporteur perçoit une commission de <strong>10 % du montant</strong> (hors taxes) de chaque vente
            qu'il a effectivement apportée et qui a été <strong>encaissée</strong> par la Société. Aucune commission
            n'est due sur un devis non signé, une vente non encaissée, annulée ou remboursée.</p>

            <h2>Article 4 — Déclaration & validation des ventes</h2>
            <p>L'Apporteur déclare ses ventes via l'espace prévu sur le site. La Société valide la vente une fois le
            paiement client encaissé. Le suivi des ventes et des commissions est tenu par la Société.</p>

            <h2>Article 5 — Paiement des commissions</h2>
            <p>Les commissions validées sont versées par <strong>PayPal</strong> (ou autre moyen convenu : virement,
            etc.) sur les coordonnées fournies par l'Apporteur, selon une périodicité communiquée par la Société.
            L'Apporteur est responsable de ses obligations fiscales et sociales liées à ces revenus.</p>

            <h2>Article 6 — Obligations de l'Apporteur</h2>
            <p>Représenter la Société avec honnêteté, ne faire aucune promesse mensongère, ne pas engager la Société
            (les prix, devis et contrats restent de la seule responsabilité de la Société), respecter la
            réglementation (RGPD, démarchage) et l'image de la marque.</p>

            <h2>Article 7 — Durée & résiliation</h2>
            <p>Le contrat prend effet à la signature électronique et est conclu pour une durée indéterminée. Chaque
            partie peut y mettre fin à tout moment. Les commissions dues sur les ventes encaissées avant la
            résiliation restent payables.</p>

            <h2>Article 8 — Protection des données</h2>
            <p>Les données de l'Apporteur et des prospects sont traitées par la Société conformément au RGPD,
            uniquement pour la gestion du programme et le versement des commissions.</p>

            <p style="margin-top:30px;color:var(--color-text-muted);font-size:.9rem;">
                Signature électronique : en cochant la case d'acceptation et en saisissant son nom complet lors de
                l'inscription, l'Apporteur reconnaît avoir lu et accepté le présent contrat (date et IP horodatées).
            </p>
            <p style="margin-top:14px;">
                <a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>#rejoindre" class="ag-btn-gold">Rejoindre le programme →</a>
            </p>
        </div>
    </section>
</main>
<style>
.ag-legal h2{font-family:var(--font-serif);color:#fff;font-size:1.25rem;margin:26px 0 8px;}
.ag-legal p{color:var(--color-text-secondary);line-height:1.7;margin:0 0 10px;}
.ag-legal strong{color:var(--color-text-soft);}
</style>
<?php get_footer(); ?>
