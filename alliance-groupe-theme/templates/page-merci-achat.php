<?php
/**
 * Template Name: Merci pour votre achat
 *
 * Page de remerciement post-paiement Stripe. Détecte le pack acheté
 * via le paramètre ?pack=pro|premium|business dans l'URL et affiche
 * le bon contenu. Met en avant l'offre de site sur-mesure pour
 * remonter les acheteurs vers le ticket le plus élevé.
 */

get_header();

$pack = isset( $_GET['pack'] ) ? sanitize_key( $_GET['pack'] ) : 
'premium';
$pack_data = array(
    
'premium' => array(
        'title'  => 'AG Starter Pro',
        'price'  => '99€',
        'icon'   => '⚡',
        'desc'   => 'Votre plugin AG Starter Pro est en cours de préparation. Vous le recevrez par email à l\'adresse utilisée pour le paiement, dans les prochaines minutes.',
    ),
    'premium' => array(
        'title'  => 'AG Starter Premium',
        'price'  => '99€',
        'icon'   => '🌍',
        'desc'   => 'Votre plugin AG Starter Premium (incluant les 6 langues et l\'intégration WooCommerce) est en cours de préparation. Vous le recevrez par email à l\'adresse utilisée pour le paiement, dans les prochaines minutes.',
    ),
    'business' => array(
        'title'  => 'AG Starter Business',
        'price'  => '149€',
        'icon'   => '💼',
        'desc'   => 'Votre Pack Business est confirmé. Notre équipe va vous contacter sous 24h ouvrées pour planifier votre installation assistée et l\'appel stratégique de lancement avec Fabrizio.',
    ),
);
if ( ! isset( $pack_data[ $pack ] ) ) {
    $pack = 
'premium';
}
$current = $pack_data[ $pack ];
?>

<main id="ag-main-content">

    <!-- Confirmation -->
    <section class="ag-section ag-section--or" style="padding:120px 0 80px;">
        <div class="ag-container">
            <div style="max-width:780px;margin:0 auto;text-align:center;">
                <div style="font-size:4rem;margin-bottom:16px;">✅</div>
                <span class="ag-tag" style="background:rgba(40,167,69,.15);color:#28a745;border-color:rgba(40,167,69,.3);">Paiement reçu</span>
                <h1 class="ag-section__title" style="margin-top:16px;">Merci pour votre <em>confiance</em> !</h1>
                <div style="display:inline-flex;align-items:center;gap:12px;margin:24px 0 12px;padding:14px 28px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.3);border-radius:100px;">
                    <span style="font-size:1.6rem;"><?php echo esc_html( $current['icon'] ); ?></span>
                    <strong style="color:#D4B45C;font-size:1.1rem;"><?php echo esc_html( $current['title'] ); ?></strong>
                    <span style="color:#b0b0bc;">·</span>
                    <span style="color:#e8e6e0;font-weight:700;"><?php echo esc_html( $current['price'] ); ?></span>
                </div>
                <p style="color:#b0b0bc;font-size:1.05rem;line-height:1.7;max-width:640px;margin:20px auto;">
                    <?php echo esc_html( $current['desc'] ); ?>
                </p>
                <p style="color:#888;font-size:.9rem;margin-top:24px;">
                    Vous n'avez pas reçu l'email sous 30 minutes ? Vérifiez vos spams ou
                    <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" style="color:#D4B45C;font-weight:700;">contactez-nous</a>.
                </p>
            </div>
        </div>
    </section>

    <!-- Upsell sur-mesure -->
    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <div style="max-width:1000px;margin:0 auto;padding:56px 48px;background:linear-gradient(135deg,rgba(212,180,92,.10) 0%,rgba(20,20,22,.6) 100%);border:2px solid rgba(212,180,92,.4);border-radius:24px;box-shadow:0 30px 80px rgba(0,0,0,.5);position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,transparent,#D4B45C,transparent);"></div>

                <div style="text-align:center;margin-bottom:36px;">
                    <span style="display:inline-block;padding:8px 24px;background:rgba(212,180,92,.2);border:1px solid rgba(212,180,92,.5);border-radius:100px;color:#D4B45C;font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:20px;">💎 Et si vous alliez plus loin ?</span>
                    <h2 style="font-size:clamp(1.7rem,3.5vw,2.5rem);margin-bottom:16px;line-height:1.2;">Votre business mérite plus qu'un template, <em>même Pro</em></h2>
                    <p style="font-size:1.1rem;color:#e8e6e0;max-width:780px;margin:0 auto;line-height:1.7;">
                        Votre achat est une excellente première étape. Mais si votre objectif est
                        de <strong style="color:#fff;">vraiment générer des clients et du chiffre d'affaires</strong>,
                        un site sur-mesure conçu pour VOTRE marque va beaucoup plus loin qu'un template.
                    </p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:36px;">
                    <div style="text-align:center;padding:24px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:12px;">
                        <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;margin-bottom:4px;">+340%</div>
                        <div style="color:#b0b0bc;font-size:.92rem;">Leads générés en moyenne par nos sites sur-mesure</div>
                    </div>
                    <div style="text-align:center;padding:24px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:12px;">
                        <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;margin-bottom:4px;">3 mois</div>
                        <div style="color:#b0b0bc;font-size:.92rem;">Délai moyen pour rentabiliser le site</div>
                    </div>
                    <div style="text-align:center;padding:24px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:12px;">
                        <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;margin-bottom:4px;">0€</div>
                        <div style="color:#b0b0bc;font-size:.92rem;">Premier appel stratégique sans engagement</div>
                    </div>
                </div>

                <div style="background:rgba(255,255,255,.02);border-left:4px solid #D4B45C;padding:24px 28px;margin-bottom:32px;border-radius:0 8px 8px 0;">
                    <p style="font-size:1.02rem;color:#e8e6e0;line-height:1.8;margin:0;">
                        <strong style="color:#D4B45C;">Offre exclusive achat :</strong>
                        en tant que client AG Starter, votre <strong style="color:#fff;">premier appel stratégique de 30 minutes</strong>
                        avec Fabrizio est gratuit, sans engagement. Il auditera votre situation,
                        vous dira franchement si un template suffit, et si non, il vous chiffrera
                        un site sur-mesure adapté à votre budget. Pas de blabla, pas de vente forcée.
                    </p>
                </div>

                <div style="text-align:center;">
                    <h3 style="font-size:1.3rem;margin-bottom:12px;color:#fff;">Prêt à passer au niveau supérieur ?</h3>
                    <p style="color:#b0b0bc;margin-bottom:28px;max-width:640px;margin-left:auto;margin-right:auto;">
                        Un site sur-mesure démarre à <strong style="color:#D4B45C;">1 500€</strong>,
                        et nos clients le rentabilisent en 3 mois en moyenne. Contactez-nous maintenant.
                    </p>
                    <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                        <a href="tel:+33623526074" class="ag-btn-gold">📞 Appeler Fabrizio — 06.23.52.60.74</a>
                        <a href="<?php echo esc_url( home_url( '/contact?source=merci-achat&pack=' . $pack ) ); ?>" class="ag-btn-outline">Réserver mon appel gratuit →</a>
                    </div>
                    <p style="color:#888;font-size:.85rem;margin-top:20px;font-style:italic;">7j/7 · Réponse sous 24h · Aucun engagement</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Étapes suivantes -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <h2 class="ag-section__title" style="text-align:center;">Et maintenant, <em>concrètement</em> ?</h2>
            <div style="max-width:900px;margin:32px auto 0;display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
                <div style="padding:24px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.2);border-radius:12px;text-align:center;">
                    <div style="width:48px;height:48px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem;margin:0 auto 12px;">1</div>
                    <strong style="color:#fff;display:block;margin-bottom:6px;">Vérifiez vos emails</strong>
                    <p style="color:#b0b0bc;font-size:.9rem;line-height:1.6;margin:0;">Le ZIP arrive sous 5 minutes maximum à l'adresse utilisée pour le paiement.</p>
                </div>
                <div style="padding:24px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.2);border-radius:12px;text-align:center;">
                    <div style="width:48px;height:48px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem;margin:0 auto 12px;">2</div>
                    <strong style="color:#fff;display:block;margin-bottom:6px;">Installez le plugin</strong>
                    <p style="color:#b0b0bc;font-size:.9rem;line-height:1.6;margin:0;">Extensions &gt; Ajouter &gt; Téléverser. Activez le plugin et configurez via Apparence &gt; Personnaliser.</p>
                </div>
                <div style="padding:24px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.2);border-radius:12px;text-align:center;">
                    <div style="width:48px;height:48px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem;margin:0 auto 12px;">3</div>
                    <strong style="color:#fff;display:block;margin-bottom:6px;">Besoin d'aide ?</strong>
                    <p style="color:#b0b0bc;font-size:.9rem;line-height:1.6;margin:0;">Répondez simplement à l'email reçu ou <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" style="color:#D4B45C;">contactez-nous</a>. Support inclus.</p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
