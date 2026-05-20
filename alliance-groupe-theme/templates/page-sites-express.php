<?php
/**
 * Template Name: Sites Express
 *
 * Vente de sites "produit" 100% en ligne, sans rendez-vous :
 * 3 packs a prix fixe -> paiement Stripe (Payment Links) -> formulaire de brief.
 * Les URLs Stripe se configurent dans Reglages > Stripe (admin).
 */
get_header();

$ph = 'STRIPE_PLACEHOLDER';
$packs = array(
	'essentiel' => array(
		'url'   => get_option( 'ag_stripe_express_essentiel_url', $ph ),
		'nom'   => 'Essentiel',
		'prix'  => '490 €',
		'desc'  => 'Le site vitrine qui te rend crédible.',
		'feats' => array( 'Site 1 page (one-page) premium', 'Design sur-mesure à ta marque', 'Optimisé mobile + rapide', 'Formulaire de contact + Google Maps', 'Livré en 5 jours', 'Référencement de base' ),
	),
	'pro' => array(
		'url'   => get_option( 'ag_stripe_express_pro_url', $ph ),
		'nom'   => 'Pro',
		'prix'  => '890 €',
		'desc'  => 'Le site complet pour développer ton activité.',
		'feats' => array( 'Jusqu\'à 6 pages', 'Design sur-mesure premium', 'SEO optimisé (Google)', 'Blog / actualités', 'Prise de RDV en ligne', 'Connexion réseaux sociaux', 'Livré en 8 jours' ),
		'star'  => true,
	),
	'boutique' => array(
		'url'   => get_option( 'ag_stripe_express_boutique_url', $ph ),
		'nom'   => 'Boutique',
		'prix'  => '1 490 €',
		'desc'  => 'Ta boutique en ligne, prête à vendre.',
		'feats' => array( 'Boutique e-commerce (WooCommerce)', 'Jusqu\'à 30 produits intégrés', 'Paiement en ligne (CB, PayPal)', 'Design premium + SEO', 'Gestion stock & commandes', 'Formation à l\'utilisation', 'Livré en 12 jours' ),
	),
);

$paid = isset( $_GET['paid'] ) ? sanitize_key( $_GET['paid'] ) : '';
$brief_ok = isset( $_GET['brief'] ) && $_GET['brief'] === 'ok';
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div><div class="ag-hero__orb ag-hero__orb--2"></div></div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Sites Express ⚡</span>
            <h1 class="ag-hero__title"><span class="ag-line">Ton site pro,</span><span class="ag-line"><em>livré en quelques jours.</em></span></h1>
            <p class="ag-hero__sub">
                Choisis ton pack, paie en ligne, remplis un court formulaire — on s'occupe de tout.
                <strong>Zéro rendez-vous, zéro prise de tête.</strong>
            </p>
            <div class="ag-hero__buttons"><a href="#packs" class="ag-btn-gold">Voir les packs →</a></div>
        </div>
    </section>

    <!-- Packs -->
    <section class="ag-section ag-section--graphite" id="packs">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Nos packs</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Un prix clair, <em>tout compris</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Pas de devis, pas d'attente. Tu paies, tu briefes, on livre.</p>

            <div class="ag-xpress__grid">
                <?php foreach ( $packs as $key => $p ) :
                    $is_set = ( $p['url'] !== $ph && $p['url'] !== '' );
                    $cta_url = $is_set ? $p['url'] : ( home_url( '/sites-express?paid=' . $key ) . '#brief' );
                ?>
                <div class="ag-xpress__card<?php echo ! empty( $p['star'] ) ? ' ag-xpress__card--star' : ''; ?> ag-anim" data-anim="card">
                    <?php if ( ! empty( $p['star'] ) ) echo '<span class="ag-xpress__badge">Le plus choisi</span>'; ?>
                    <h3 class="ag-xpress__name"><?php echo esc_html( $p['nom'] ); ?></h3>
                    <div class="ag-xpress__price"><?php echo esc_html( $p['prix'] ); ?></div>
                    <p class="ag-xpress__desc"><?php echo esc_html( $p['desc'] ); ?></p>
                    <ul class="ag-xpress__feats">
                        <?php foreach ( $p['feats'] as $f ) echo '<li>' . esc_html( $f ) . '</li>'; ?>
                    </ul>
                    <a href="<?php echo esc_url( $cta_url ); ?>" class="ag-btn-gold ag-xpress__cta"<?php echo $is_set ? '' : ' '; ?>>Commander →</a>
                    <?php if ( ! $is_set ) echo '<small class="ag-xpress__note">Paiement bientôt en ligne — remplis le brief, on te recontacte.</small>'; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section class="ag-section ag-section--onyx">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Comment ça marche</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">3 étapes, <em>sans rendez-vous</em></h2>
            <div class="ag-amb__steps">
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">01</span><h3>Tu choisis &amp; tu paies</h3><p>Un pack à prix fixe, paiement sécurisé en ligne. Aucun appel nécessaire.</p></div>
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">02</span><h3>Tu remplis le brief</h3><p>Un court formulaire : ton activité, tes textes, ton logo, tes photos. 10 minutes.</p></div>
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">03</span><h3>On livre + vidéo</h3><p>On construit ton site et on t'envoie une vidéo de présentation. Retouches par écrit.</p></div>
            </div>
        </div>
    </section>

    <!-- Brief -->
    <section class="ag-section ag-section--or" id="brief">
        <div class="ag-container ag-container--narrow">
            <?php if ( $brief_ok ) : ?>
                <div class="ag-question-success">
                    <div class="ag-question-success__check">✓</div>
                    <h2>Brief reçu 🚀</h2>
                    <p class="ag-question-success__sub">Merci ! On démarre la production de ton site et on t'envoie une première version rapidement, avec une vidéo. Tout par écrit, sans rendez-vous.</p>
                </div>
            <?php else : ?>
                <span class="ag-tag">Étape 2</span>
                <h2 class="ag-section__title">Le brief de <em>ton site</em></h2>
                <p class="ag-section__desc"><?php echo $paid ? 'Paiement reçu ✅ Remplis ce brief pour qu\'on démarre.' : 'Déjà payé ? Remplis ce brief pour qu\'on lance ton site.'; ?></p>
                <form class="ag-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
                    <input type="hidden" name="action" value="ag_submit_brief">
                    <input type="hidden" name="pack" value="<?php echo esc_attr( $paid ); ?>">
                    <?php wp_nonce_field( 'ag_brief_nonce', 'ag_brief_nonce' ); ?>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="b-business">Nom de ton activité *</label><input type="text" id="b-business" name="business" required placeholder="Ex : Garage Diallo"></div>
                        <div class="ag-form__group"><label for="b-sector">Secteur</label><input type="text" id="b-sector" name="sector" placeholder="Ex : restaurant, coiffeur..."></div>
                    </div>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="b-name">Ton nom *</label><input type="text" id="b-name" name="name" required placeholder="Prénom Nom"></div>
                        <div class="ag-form__group"><label for="b-email">Email *</label><input type="email" id="b-email" name="email" required placeholder="ton@email.com"></div>
                    </div>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="b-phone">Téléphone</label><input type="tel" id="b-phone" name="phone" placeholder="06 ..."></div>
                        <div class="ag-form__group"><label for="b-domain">Nom de domaine souhaité</label><input type="text" id="b-domain" name="domain" placeholder="monentreprise.fr"></div>
                    </div>
                    <div class="ag-form__group"><label for="b-content">Tes textes / infos (services, horaires, à propos...)</label><textarea id="b-content" name="content" placeholder="Décris ton activité, tes services, tes horaires, ce que tu veux mettre en avant..."></textarea></div>
                    <div class="ag-form__group"><label for="b-inspi">Sites que tu aimes (inspirations)</label><textarea id="b-inspi" name="inspiration" placeholder="Colle des liens de sites que tu trouves beaux."></textarea></div>
                    <p style="color:var(--color-text-secondary);font-size:.9rem;margin:0 0 14px;">Envoie ton logo et tes photos par email à <strong>contact@alliancegroupe-inc.com</strong> en répondant à l'email de confirmation.</p>
                    <button type="submit" class="ag-btn-gold">Envoyer mon brief 🚀</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

</main>

<style>
#brief .ag-form__group{position:relative;margin-bottom:0;}
#brief .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
#brief .ag-form__group input,#brief .ag-form__group textarea{padding:14px 18px;}
.ag-xpress__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:46px;align-items:start;}
.ag-xpress__card{position:relative;display:flex;flex-direction:column;padding:38px 30px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:20px;transition:transform .4s,border-color .4s,box-shadow .4s;}
.ag-xpress__card:hover{transform:translateY(-6px);border-color:rgba(212,180,92,.45);box-shadow:0 30px 70px rgba(0,0,0,.45);}
.ag-xpress__card--star{border-color:rgba(212,180,92,.5);background:linear-gradient(160deg,rgba(212,180,92,.12),rgba(243,122,31,.05));}
.ag-xpress__badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#1a1207;font-weight:800;font-size:.72rem;letter-spacing:1px;text-transform:uppercase;padding:5px 14px;border-radius:20px;white-space:nowrap;}
.ag-xpress__name{font-family:var(--font-serif);font-size:1.5rem;color:#fff;margin:0 0 6px;}
.ag-xpress__price{font-family:var(--font-serif);font-size:2.4rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;margin-bottom:8px;}
.ag-xpress__desc{color:var(--color-text-secondary);font-size:.95rem;margin:0 0 18px;}
.ag-xpress__feats{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:10px;}
.ag-xpress__feats li{position:relative;padding-left:26px;color:var(--color-text-soft);font-size:.95rem;line-height:1.4;}
.ag-xpress__feats li::before{content:'✓';position:absolute;left:0;color:var(--color-gold);font-weight:800;}
.ag-xpress__cta{margin-top:auto;text-align:center;}
.ag-xpress__note{display:block;margin-top:10px;color:var(--color-text-muted);font-size:.8rem;text-align:center;}
@media(max-width:900px){.ag-xpress__grid{grid-template-columns:1fr;max-width:440px;margin-left:auto;margin-right:auto;}}
</style>

<?php get_footer(); ?>
