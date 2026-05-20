<?php
/**
 * Template Name: Programme Ambassadeurs
 *
 * Page publique : rejoindre le programme de vente (10% par vente, paiement
 * PayPal ou autre) + declarer une vente. Tout est gere par inc/ag-ambassadeurs.php.
 */
get_header();

$ag_amb_ok   = isset( $_GET['ambassadeur'] ) && $_GET['ambassadeur'] === 'ok';
$ag_amb_deja = isset( $_GET['ambassadeur'] ) && $_GET['ambassadeur'] === 'deja';
$ag_vente_ok = isset( $_GET['vente'] ) && $_GET['vente'] === 'ok';
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:62vh;">
        <div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div><div class="ag-hero__orb ag-hero__orb--2"></div></div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Programme Ambassadeurs 🤝</span>
            <h1 class="ag-hero__title"><span class="ag-line">Vends nos services,</span><span class="ag-line"><em>touche 10 %.</em></span></h1>
            <p class="ag-hero__sub">
                Rejoins l'équipe de vente d'Alliance Groupe. Pour chaque client que tu
                ramènes, tu gagnes <strong>10 % du montant</strong>, payé via PayPal (ou autre).
                Pas besoin d'être expert : on te donne tous les outils.
            </p>
            <div class="ag-hero__buttons">
                <a href="#rejoindre" class="ag-btn-gold">Rejoindre le programme →</a>
                <a href="#declarer" class="ag-btn-outline">Déclarer une vente</a>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Comment ça marche</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Simple, transparent, <em>rémunéré</em></h2>
            <div class="ag-amb__steps">
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">01</span><h3>Tu t'inscris</h3><p>En 1 minute avec ton email. On valide ton inscription et on t'envoie tes outils de vente (scripts, pitch, supports).</p></div>
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">02</span><h3>Tu prospectes</h3><p>Commerçants, artisans, restos, indépendants autour de toi. Tu proposes nos services : site web, SEO, pub, branding, IA.</p></div>
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">03</span><h3>Tu déclares la vente</h3><p>Dès qu'un client signe, tu remplis le formulaire ci-dessous. On valide la vente une fois encaissée.</p></div>
                <div class="ag-amb__step ag-anim" data-anim="card"><span class="ag-amb__num">04</span><h3>Tu es payé</h3><p><strong>10 %</strong> du montant de chaque vente, versés sur ton PayPal (ou autre moyen). Suivi clair de tes commissions.</p></div>
            </div>
        </div>
    </section>

    <!-- Inscription -->
    <section class="ag-section ag-section--onyx" id="rejoindre">
        <div class="ag-container ag-container--narrow">
            <?php if ( $ag_amb_ok ) : ?>
                <div class="ag-question-success">
                    <div class="ag-question-success__check">✓</div>
                    <h2>Inscription reçue 🤝</h2>
                    <p class="ag-question-success__sub">Bienvenue ! On valide ton inscription et on te recontacte avec tes outils de vente. Vérifie ta boîte mail.</p>
                    <a href="#declarer" class="ag-btn-outline">Déclarer une vente</a>
                </div>
            <?php else : ?>
                <?php if ( $ag_amb_deja ) : ?>
                    <p style="background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);border-radius:10px;padding:14px 18px;color:#fff;">Cet email est déjà inscrit. Tu peux directement <a href="#declarer" style="color:var(--color-gold);">déclarer une vente</a>.</p>
                <?php endif; ?>
                <span class="ag-tag ag-anim" data-anim="tag">Rejoindre</span>
                <h2 class="ag-section__title ag-anim" data-anim="title">Inscris-toi au <em>programme</em></h2>
                <p class="ag-section__desc ag-anim" data-anim="desc">Gratuit, sans engagement. Tu gagnes sur ce que tu vends, c'est tout.</p>

                <form class="ag-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="ag_ambassadeur_signup">
                    <?php wp_nonce_field( 'ag_amb_signup', 'ag_amb_nonce' ); ?>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="amb-name">Nom complet *</label><input type="text" id="amb-name" name="name" required placeholder="Ton nom"></div>
                        <div class="ag-form__group"><label for="amb-email">Email *</label><input type="email" id="amb-email" name="email" required placeholder="ton@email.com"></div>
                    </div>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="amb-phone">Téléphone</label><input type="tel" id="amb-phone" name="phone" placeholder="06 ..."></div>
                        <div class="ag-form__group"><label for="amb-city">Ville</label><input type="text" id="amb-city" name="city" placeholder="Ta ville"></div>
                    </div>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="amb-birth">Date de naissance *</label><input type="date" id="amb-birth" name="birthdate" required></div>
                        <div class="ag-form__group"><label for="amb-address">Adresse complète *</label><input type="text" id="amb-address" name="address" required placeholder="N°, rue, code postal, ville"></div>
                    </div>
                    <div class="ag-form__group">
                        <label for="amb-id">Pièce d'identité (vérification KYC) *</label>
                        <input type="file" id="amb-id" name="id_document" accept="image/jpeg,image/png,application/pdf" required>
                        <small style="display:block;color:var(--color-text-muted);margin-top:6px;">Carte d'identité, passeport ou titre de séjour (JPG, PNG ou PDF, 5 Mo max). Stockée de façon sécurisée, consultée uniquement pour vérifier ton identité.</small>
                    </div>
                    <div class="ag-form__row">
                        <div class="ag-form__group">
                            <label for="amb-payout">Moyen de paiement</label>
                            <select id="amb-payout" name="payout_method">
                                <option value="PayPal">PayPal</option>
                                <option value="Virement IBAN">Virement (IBAN)</option>
                                <option value="Wero / Lydia">Wero / Lydia</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="ag-form__group"><label for="amb-payid">Email PayPal / coordonnées</label><input type="text" id="amb-payid" name="payout_id" placeholder="email PayPal, IBAN, n° Lydia..."></div>
                    </div>
                    <div class="ag-form__group"><label for="amb-motiv">Pourquoi tu veux rejoindre ? (optionnel)</label><textarea id="amb-motiv" name="motivation" placeholder="Ton réseau, ta motivation..."></textarea></div>

                    <div class="ag-amb__contract">
                        <p>Pour des raisons légales, ton identité est vérifiée et un <strong>contrat d'apporteur d'affaires</strong> encadre ta rémunération de 10%.
                        <a href="<?php echo esc_url( home_url( '/contrat-ambassadeur' ) ); ?>" target="_blank" rel="noopener">Lire le contrat →</a></p>
                        <label class="ag-amb__check"><input type="checkbox" name="accept_contract" value="1" required> J'ai lu et j'accepte le contrat d'apporteur d'affaires Alliance Groupe, et je certifie que mon identité est exacte. *</label>
                        <div class="ag-form__group"><label for="amb-sign">Signature — recopie ton nom et prénom complets *</label><input type="text" id="amb-sign" name="signature" required placeholder="Prénom NOM"></div>
                    </div>

                    <button type="submit" class="ag-btn-gold">Je signe et je rejoins 🤝</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- Déclarer une vente -->
    <section class="ag-section ag-section--or" id="declarer">
        <div class="ag-container ag-container--narrow">
            <?php if ( $ag_vente_ok ) : ?>
                <div class="ag-question-success">
                    <div class="ag-question-success__check">✓</div>
                    <h2>Vente enregistrée 🎉</h2>
                    <p class="ag-question-success__sub">Bravo ! Ta vente est enregistrée. Ta commission de 10 % sera validée une fois le client encaissé, puis versée sur ton moyen de paiement.</p>
                    <a href="#declarer" class="ag-btn-outline">Déclarer une autre vente</a>
                </div>
            <?php else : ?>
                <span class="ag-tag">Déjà ambassadeur ?</span>
                <h2 class="ag-section__title">Déclare ta <em>vente</em></h2>
                <p class="ag-section__desc">Dès qu'un client signe, déclare-la ici pour toucher tes 10 %.</p>
                <form class="ag-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
                    <input type="hidden" name="action" value="ag_ambassadeur_vente">
                    <?php wp_nonce_field( 'ag_amb_vente', 'ag_vente_nonce' ); ?>
                    <div class="ag-form__row">
                        <div class="ag-form__group"><label for="v-email">Ton email (d'inscription) *</label><input type="email" id="v-email" name="email" required placeholder="ton@email.com"></div>
                        <div class="ag-form__group"><label for="v-client">Client *</label><input type="text" id="v-client" name="client" required placeholder="Nom du client"></div>
                    </div>
                    <div class="ag-form__row">
                        <div class="ag-form__group">
                            <label for="v-activite">Activité vendue</label>
                            <select id="v-activite" name="activite">
                                <option value="Création site web">Création site web</option>
                                <option value="SEO">SEO / Référencement</option>
                                <option value="Publicité (Ads)">Publicité (Google/Meta)</option>
                                <option value="Branding">Branding</option>
                                <option value="IA & Automatisation">IA &amp; Automatisation</option>
                                <option value="Conseil">Conseil stratégique</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="ag-form__group"><label for="v-montant">Montant de la vente (€) *</label><input type="text" id="v-montant" name="montant" required placeholder="ex : 1500"></div>
                    </div>
                    <p style="color:var(--color-text-secondary);font-size:.9rem;margin:0 0 16px;">Ta commission estimée : <strong style="color:var(--color-gold);">10 % du montant</strong>.</p>
                    <button type="submit" class="ag-btn-gold">Déclarer ma vente →</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

</main>

<style>
/* labels classiques (neutralise les floating labels de cinema-upgrades) */
#rejoindre .ag-form__group, #declarer .ag-form__group{position:relative;margin-bottom:0;}
#rejoindre .ag-form__group label, #declarer .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
#rejoindre .ag-form__group input, #rejoindre .ag-form__group textarea, #rejoindre .ag-form__group select,
#declarer .ag-form__group input, #declarer .ag-form__group textarea, #declarer .ag-form__group select{padding:14px 18px;}
.ag-amb__contract{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.25);border-radius:14px;padding:20px 22px;margin:6px 0 4px;}
.ag-amb__contract p{color:var(--color-text-secondary);font-size:.95rem;line-height:1.6;margin:0 0 14px;}
.ag-amb__contract a{color:var(--color-gold);font-weight:700;}
.ag-amb__check{display:flex;gap:10px;align-items:flex-start;color:var(--color-text-soft);font-size:.92rem;line-height:1.5;margin-bottom:16px;cursor:pointer;}
.ag-amb__check input{margin-top:3px;flex:0 0 auto;width:18px;height:18px;accent-color:var(--color-gold);}
.ag-amb__steps{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:46px;}
.ag-amb__step{padding:30px 24px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.15);border-radius:18px;}
.ag-amb__num{display:inline-block;font-family:var(--font-serif);font-size:1.8rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;margin-bottom:10px;}
.ag-amb__step h3{font-family:var(--font-serif);font-size:1.2rem;color:#fff;margin:0 0 8px;}
.ag-amb__step p{color:var(--color-text-secondary);font-size:.92rem;line-height:1.6;margin:0;}
@media(max-width:900px){.ag-amb__steps{grid-template-columns:1fr 1fr;}}
@media(max-width:560px){.ag-amb__steps{grid-template-columns:1fr;}}
</style>

<?php get_footer(); ?>
