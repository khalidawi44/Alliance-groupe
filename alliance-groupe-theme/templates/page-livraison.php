<?php
/**
 * Template Name: Politique de livraison
 *
 * Politique de livraison (exigée par Google Merchant Center).
 * À aligner avec les réglages "Livraison" de Merchant Center.
 */
get_header();
$ag_phone = function_exists( 'ag_contact_phone' ) ? apply_filters( 'ag_contact_phone', '07 44 82 95 16' ) : '07 44 82 95 16';
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:38vh;">
        <div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div></div>
        <div class="ag-hero__content">
            <span class="ag-tag">Livraison</span>
            <h1 class="ag-hero__title"><span class="ag-line">Politique de</span><span class="ag-line"><em>livraison</em></span></h1>
            <p class="ag-hero__sub">Délais, zones et frais de livraison — en toute clarté.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container" style="max-width:860px;color:var(--color-text-soft);line-height:1.7;">

            <p><strong>Alliance Groupe</strong> — contact@alliancegroupe-inc.com · <?php echo esc_html( $ag_phone ); ?>. Pays : <strong>France</strong>. Dernière mise à jour : <?php echo esc_html( wp_date( 'd/m/Y' ) ); ?>.</p>

            <div style="background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.3);border-radius:14px;padding:18px 22px;margin:16px 0 30px;color:#fff;">
                <strong>En résumé :</strong> <strong>livraison gratuite (0 €)</strong> en France. Commande traitée sous <strong>0 à 1 jour ouvré</strong>. Pour nos prestations et contenus <strong>numériques</strong>, l'accès est transmis <strong>par email</strong> immédiatement à 48 h après confirmation du paiement.
            </div>

            <h2 style="color:#fff;margin-top:30px;">1. Zone de livraison</h2>
            <p>Nous livrons en <strong>France</strong>. Pour toute commande hors de cette zone, contactez-nous au préalable.</p>

            <h2 style="color:#fff;margin-top:30px;">2. Frais de livraison</h2>
            <p>La livraison est <strong>gratuite (0 €)</strong>.</p>

            <h2 style="color:#fff;margin-top:30px;">3. Délais</h2>
            <ul style="margin:8px 0 0 18px;">
                <li><strong>Délai de traitement :</strong> 0 à 1 jour ouvré après confirmation du paiement.</li>
                <li><strong>Produits &amp; services numériques :</strong> accès / livraison <strong>par email</strong> (lien, identifiants ou démarrage de la prestation), <strong>immédiatement à 48 h</strong>.</li>
            </ul>

            <h2 style="color:#fff;margin-top:30px;">4. Suivi &amp; réception</h2>
            <p>Vous recevez une <strong>confirmation par email</strong> dès que votre commande est traitée. En cas de non-réception, vérifiez vos spams puis contactez-nous.</p>

            <h2 style="color:#fff;margin-top:30px;">5. Contact</h2>
            <p>Une question sur votre livraison ? Écrivez à <strong>contact@alliancegroupe-inc.com</strong> ou appelez le <strong><?php echo esc_html( $ag_phone ); ?></strong>. Réponse sous 48 h ouvrées.</p>

            <p style="margin-top:30px;font-size:.9rem;">Voir aussi notre <a href="<?php echo esc_url( home_url( '/retours' ) ); ?>" style="color:var(--color-gold);">Politique de retour</a>, nos <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" style="color:var(--color-gold);">Mentions légales &amp; CGV</a> et notre <a href="<?php echo esc_url( home_url( '/confidentialite' ) ); ?>" style="color:var(--color-gold);">Politique de confidentialité</a>.</p>

        </div>
    </section>

</main>
<?php get_footer(); ?>
