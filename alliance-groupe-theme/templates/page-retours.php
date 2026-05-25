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

            <p><strong>Alliance Groupe</strong> — contact@alliancegroupe-inc.com · <?php echo esc_html( $ag_phone ); ?>. Dernière mise à jour : <?php echo esc_html( wp_date( 'd/m/Y' ) ); ?>.</p>

            <div style="background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.3);border-radius:14px;padding:18px 22px;margin:16px 0 30px;color:#fff;">
                <strong>En résumé :</strong> nos offres sont des <strong>produits et services 100% numériques</strong> (création et gestion de sites, prestations en ligne). À ce titre, <strong>aucun retour ni remboursement n'est possible une fois la prestation commencée ou le contenu numérique livré</strong>.
            </div>

            <h2 style="color:#fff;margin-top:30px;">1. Nature numérique de nos offres</h2>
            <p>Alliance Groupe fournit exclusivement des <strong>services et contenus numériques</strong> (sites web, gestion, prestations en ligne). Il n'y a <strong>aucune livraison de produit physique</strong>, donc aucun retour de marchandise.</p>

            <h2 style="color:#fff;margin-top:30px;">2. Pas de retour / pas de remboursement après le début de la prestation</h2>
            <p>Conformément à l'<strong>article L221-28 du Code de la consommation</strong>, le droit de rétractation <strong>ne s'applique pas</strong> :</p>
            <ul style="margin:8px 0 0 18px;">
                <li>aux <strong>services pleinement exécutés</strong> dont l'exécution a commencé avec votre accord exprès et après renoncement à votre droit de rétractation ;</li>
                <li>aux <strong>contenus numériques</strong> fournis sur un support immatériel dont l'exécution a commencé après votre accord exprès et votre renoncement au droit de rétractation.</li>
            </ul>
            <p>En validant votre commande, vous <strong>demandez le démarrage immédiat</strong> de la prestation et <strong>reconnaissez renoncer à votre droit de rétractation</strong>. Par conséquent, <strong>aucun remboursement</strong> ne pourra être exigé une fois la prestation entamée.</p>

            <h2 style="color:#fff;margin-top:30px;">3. Avant le démarrage</h2>
            <p><strong>Tant que la prestation n'a pas commencé</strong> (aucun travail engagé), vous pouvez annuler et être <strong>remboursé intégralement</strong>. Il suffit de nous prévenir rapidement par email.</p>

            <h2 style="color:#fff;margin-top:30px;">4. Notre engagement qualité</h2>
            <p>L'absence de remboursement ne nous empêche pas d'être à vos côtés : en cas de souci, nous <strong>corrigeons et ajustons</strong> jusqu'à votre satisfaction dans le cadre de la prestation commandée. On privilégie toujours la solution amiable.</p>

            <h2 style="color:#fff;margin-top:30px;">5. Contact</h2>
            <p>Pour toute question, annulation (avant démarrage) ou réclamation : <strong>contact@alliancegroupe-inc.com</strong> ou <strong><?php echo esc_html( $ag_phone ); ?></strong>. Réponse sous 48 h ouvrées.</p>

            <p style="margin-top:30px;font-size:.9rem;">Voir aussi nos <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" style="color:var(--color-gold);">Mentions légales &amp; CGV</a> et notre <a href="<?php echo esc_url( home_url( '/confidentialite' ) ); ?>" style="color:var(--color-gold);">Politique de confidentialité</a>.</p>

        </div>
    </section>

</main>
<?php get_footer(); ?>
