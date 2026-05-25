<?php
/**
 * Template Name: Politique de retour
 *
 * Politique de retour / rétractation / remboursement (exigée notamment par
 * Google Merchant Center). Adaptée aux prestations & produits numériques,
 * avec le droit de rétractation applicable aux consommateurs.
 */
get_header();
$ag_phone = function_exists( 'ag_contact_phone' ) ? apply_filters( 'ag_contact_phone', '07 44 82 95 16' ) : '07 44 82 95 16';
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:38vh;">
        <div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div></div>
        <div class="ag-hero__content">
            <span class="ag-tag">Vos droits</span>
            <h1 class="ag-hero__title"><span class="ag-line">Politique de retour</span><span class="ag-line">&amp; de <em>remboursement</em></span></h1>
            <p class="ag-hero__sub">Rétractation, annulation et remboursement — clair et conforme à la loi.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container" style="max-width:860px;color:var(--color-text-soft);line-height:1.7;">

            <p><strong>Alliance Groupe</strong> — contact@alliancegroupe-inc.com · <?php echo esc_html( $ag_phone ); ?>. Pays : <strong>France</strong>. Dernière mise à jour : <?php echo esc_html( wp_date( 'd/m/Y' ) ); ?>.</p>

            <div style="background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.3);border-radius:14px;padding:18px 22px;margin:16px 0 30px;color:#fff;">
                <strong>En résumé :</strong> nous acceptons les retours, qu'il s'agisse de produits <strong>défectueux ou non défectueux</strong>, dans un délai de <strong>2 jours</strong>. Le produit doit être <strong>neuf</strong>. Les <strong>échanges ne sont pas acceptés</strong>. Aucuns frais de remise en stock. Remboursement traité sous <strong>15 jours</strong>.
            </div>

            <h2 style="color:#fff;margin-top:30px;">1. Retours acceptés</h2>
            <p>Nous acceptons les retours pour les produits <strong>défectueux</strong> comme <strong>non défectueux</strong>. Les <strong>échanges ne sont pas acceptés</strong> (le retour donne lieu à un remboursement).</p>

            <h2 style="color:#fff;margin-top:30px;">2. Délai &amp; état du produit</h2>
            <p>Vous disposez d'un délai de <strong>2 jours</strong> à compter de la réception pour demander un retour. Le produit doit être retourné <strong>neuf</strong> (état d'origine, non utilisé, complet).</p>

            <h2 style="color:#fff;margin-top:30px;">3. Méthode de retour &amp; frais</h2>
            <p>Le retour s'effectue <strong>en le déposant à un point de dépôt</strong> que nous vous indiquons après votre demande. <strong>Aucuns frais de remise en stock</strong> ne sont appliqués.</p>

            <h2 style="color:#fff;margin-top:30px;">4. Remboursement</h2>
            <p>Une fois le retour reçu et vérifié, le remboursement est <strong>traité sous 15 jours</strong>, via le <strong>même moyen de paiement</strong> que celui utilisé lors de la commande (sauf accord pour un autre moyen).</p>

            <h2 style="color:#fff;margin-top:30px;">5. Comment demander un retour</h2>
            <p>Contactez-nous à <strong>contact@alliancegroupe-inc.com</strong> ou au <strong><?php echo esc_html( $ag_phone ); ?></strong> en indiquant votre nom et votre commande. Nous vous communiquons le point de dépôt et la marche à suivre. Réponse sous 48 h ouvrées.</p>

            <p style="margin-top:30px;font-size:.9rem;">Voir aussi nos <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" style="color:var(--color-gold);">Mentions légales &amp; CGV</a> et notre <a href="<?php echo esc_url( home_url( '/confidentialite' ) ); ?>" style="color:var(--color-gold);">Politique de confidentialité</a>.</p>

        </div>
    </section>

</main>
<?php get_footer(); ?>
