<?php
/**
 * Template Name: Programme Racines
 *
 * Programme d'accompagnement + association pour entrepreneurs des
 * quartiers populaires. Alliance Groupe cree le site, devient associe,
 * accompagne la creation/gestion d'entreprise, gere le numerique et forme.
 */
get_header();

$ag_racines_submitted = isset( $_GET['racines'] ) && $_GET['racines'] === 'ok';
?>

<main id="ag-main-content">

    <!-- Hero -->
    <section class="ag-hero" style="min-height:70vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
            <div class="ag-hero__orb ag-hero__orb--2"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-tag--green ag-anim" data-anim="tag">Programme Racines 🌱</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Ton talent mérite</span>
                <span class="ag-line"><em>un vrai départ.</em></span>
            </h1>
            <span class="ag-heritage-strip ag-heritage-strip--center" aria-hidden="true"></span>
            <p class="ag-hero__sub">
                Tu as un projet, de l'énergie, mais pas les moyens d'une agence ?
                Alliance Groupe ne te vend pas un site — on devient <strong>ton associé</strong>.
                On construit ton entreprise ensemble, des quartiers populaires jusqu'au succès.
            </p>
            <div class="ag-hero__buttons">
                <a href="#racines-candidature" class="ag-btn-gold">Candidater au programme →</a>
                <a href="#racines-comment" class="ag-btn-outline">Comment ça marche</a>
            </div>
        </div>
    </section>

    <!-- Origine / mission -->
    <section class="ag-section ag-section--onyx">
        <div class="ag-container ag-container--narrow" style="text-align:center;">
            <span class="ag-tag ag-anim" data-anim="tag">D'où ça vient</span>
            <h2 class="ag-section__title ag-anim" data-anim="title" style="margin-left:auto;margin-right:auto;">Né dans un <em>quartier populaire</em></h2>
            <p class="ag-body-lg ag-anim" data-anim="desc" style="max-width:680px;margin:0 auto 20px;">
                Fabrizio, notre fondateur, a grandi à Naples dans les Quartieri Spagnoli.
                Il a commencé par former gratuitement des familles défavorisées au digital,
                dans l'arrière-salle d'une église. <strong>Le web comme outil d'émancipation.</strong>
            </p>
            <p class="ag-body-lg ag-anim" data-anim="desc" style="max-width:680px;margin:0 auto 28px;">
                Le Programme Racines, c'est cette conviction transformée en action :
                le talent est partout, les opportunités non. On vient corriger ça.
            </p>
            <a href="<?php echo esc_url( home_url( '/notre-fondateur' ) ); ?>" class="ag-btn-outline">Lire l'histoire de Fabrizio →</a>
        </div>
    </section>

    <!-- Comment ça marche : 4 étapes -->
    <section class="ag-section ag-section--graphite" id="racines-comment">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Le principe</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">On ne te vend rien. <em>On s'associe.</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">
                Pas de grosse facture à payer au démarrage. On investit notre travail dans
                ton projet, et on grandit ensemble : on devient partenaires de ton business.
            </p>

            <div class="ag-racines__steps">
                <div class="ag-racines__step ag-anim" data-anim="card">
                    <span class="ag-racines__num">01</span>
                    <h3>On crée ton site au top</h3>
                    <p>Un site professionnel premium, le même niveau que nos clients payants. Conçu pour vendre et te rendre crédible dès le jour 1.</p>
                </div>
                <div class="ag-racines__step ag-anim" data-anim="card">
                    <span class="ag-racines__num">02</span>
                    <h3>On devient associés</h3>
                    <p>Plutôt qu'une facture impossible à payer, on prend une part dans ton business. Ton succès devient le nôtre — on rame dans le même bateau.</p>
                </div>
                <div class="ag-racines__step ag-anim" data-anim="card">
                    <span class="ag-racines__num">03</span>
                    <h3>On t'accompagne de A à Z</h3>
                    <p>Création de l'entreprise, gestion, démarches, stratégie. On t'épaule sur tout ce que tu ne maîtrises pas encore, étape par étape.</p>
                </div>
                <div class="ag-racines__step ag-anim" data-anim="card">
                    <span class="ag-racines__num">04</span>
                    <h3>On gère ton numérique + formation</h3>
                    <p>Site, SEO, réseaux, automatisation : on s'occupe de tout le digital. Et on te forme pour que tu deviennes autonome et maître de ton outil.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Le deal clairement -->
    <section class="ag-section ag-section--or">
        <div class="ag-container ag-container--narrow">
            <div class="ag-racines__deal">
                <h2 class="ag-racines__deal-title">Le deal, clairement</h2>
                <ul class="ag-racines__deal-list">
                    <li><strong>Toi, tu apportes :</strong> le projet, le métier, l'envie d'avancer, et le travail de terrain.</li>
                    <li><strong>Nous, on apporte :</strong> le site premium, la stratégie digitale, l'accompagnement business et la formation — sans facture lourde au départ.</li>
                    <li><strong>Ensemble :</strong> on définit une part de partenariat juste et transparente. Tu n'es jamais seul, on est associés.</li>
                </ul>
                <p class="ag-racines__deal-note">
                    🤝 Transparence totale : chaque partenariat est défini noir sur blanc, équitablement, avant de commencer. Pas de piège, pas de petites lignes.
                </p>
            </div>
        </div>
    </section>

    <!-- Pour qui -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Pour qui</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Ce programme est fait pour <em>toi</em> si...</h2>
            <div class="ag-racines__for">
                <div class="ag-racines__for-item ag-anim" data-anim="card">✊ Tu as un projet ou un savoir-faire, mais pas les moyens d'une agence</div>
                <div class="ag-racines__for-item ag-anim" data-anim="card">🏙️ Tu viens d'un quartier populaire et tu veux t'en sortir par le travail</div>
                <div class="ag-racines__for-item ag-anim" data-anim="card">🔥 Tu es prêt à t'investir à fond — on accompagne, on ne fait pas à ta place</div>
                <div class="ag-racines__for-item ag-anim" data-anim="card">🚀 Tu veux un vrai partenaire long terme, pas juste un prestataire</div>
            </div>
        </div>
    </section>

    <!-- Formulaire de candidature -->
    <section class="ag-section ag-section--onyx" id="racines-candidature">
        <div class="ag-container ag-container--narrow">
            <?php if ( $ag_racines_submitted ) : ?>
            <div class="ag-question-success">
                <div class="ag-question-success__check">✓</div>
                <h2>Candidature reçue 🌱</h2>
                <p class="ag-question-success__sub">
                    Merci pour ta confiance. On lit chaque candidature avec attention.
                    Si ton projet correspond au programme, on te recontacte sous 7 jours
                    pour un premier échange. En attendant, prépare bien ton idée !
                </p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-btn-outline">Retour à l'accueil</a>
            </div>
            <?php else : ?>
            <span class="ag-tag ag-anim" data-anim="tag">Candidature</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Présente-nous <em>ton projet</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">
                Pas besoin d'un dossier parfait. Sois honnête et concret : c'est ton énergie
                et ton projet qui comptent. On lit tout.
            </p>

            <form class="ag-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
                <input type="hidden" name="action" value="ag_submit_racines">
                <?php wp_nonce_field( 'ag_racines_nonce', 'ag_racines_nonce' ); ?>

                <div class="ag-form__row">
                    <div class="ag-form__group">
                        <label for="rac-name">Nom complet *</label>
                        <input type="text" id="rac-name" name="name" required placeholder="Ton nom">
                    </div>
                    <div class="ag-form__group">
                        <label for="rac-email">Email *</label>
                        <input type="email" id="rac-email" name="email" required placeholder="ton@email.com">
                    </div>
                </div>

                <div class="ag-form__row">
                    <div class="ag-form__group">
                        <label for="rac-phone">Téléphone</label>
                        <input type="tel" id="rac-phone" name="phone" placeholder="06 ...">
                    </div>
                    <div class="ag-form__group">
                        <label for="rac-city">Ville / Quartier *</label>
                        <input type="text" id="rac-city" name="city" required placeholder="Ex : Nantes, Bellevue">
                    </div>
                </div>

                <div class="ag-form__group">
                    <label for="rac-project">Ton projet en quelques mots *</label>
                    <textarea id="rac-project" name="project" required placeholder="Quel est ton métier / ton idée ? Où en es-tu aujourd'hui ?"></textarea>
                </div>

                <div class="ag-form__group">
                    <label for="rac-why">Pourquoi le Programme Racines ? *</label>
                    <textarea id="rac-why" name="why" required placeholder="Qu'est-ce qui te bloque aujourd'hui ? Pourquoi ce partenariat ferait la différence ?"></textarea>
                </div>

                <button type="submit" class="ag-btn-gold">Envoyer ma candidature 🌱</button>
                <p style="color:var(--color-text-muted);font-size:.85rem;margin-top:14px;">
                    En envoyant ce formulaire, tu acceptes d'être recontacté par Alliance Groupe au sujet de ta candidature.
                </p>
            </form>
            <?php endif; ?>
        </div>
    </section>

</main>

<style>
.ag-racines__steps{display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-top:50px;}
.ag-racines__step{position:relative;padding:36px 30px 32px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.15);border-radius:18px;transition:transform .4s,border-color .4s,box-shadow .4s;}
.ag-racines__step:hover{transform:translateY(-5px);border-color:rgba(212,180,92,.4);box-shadow:0 24px 60px rgba(0,0,0,.4);}
.ag-racines__num{display:inline-block;font-family:var(--font-serif);font-size:2rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;margin-bottom:14px;}
.ag-racines__step h3{font-family:var(--font-serif);font-size:1.3rem;color:#fff;margin:0 0 10px;}
.ag-racines__step p{color:var(--color-text-secondary);font-size:.95rem;line-height:1.65;margin:0;}
.ag-racines__deal{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.25);border-radius:20px;padding:44px 44px 38px;}
.ag-racines__deal-title{font-family:var(--font-serif);font-size:clamp(1.5rem,3vw,2rem);color:#fff;margin:0 0 24px;text-align:center;}
.ag-racines__deal-list{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:16px;}
.ag-racines__deal-list li{padding:18px 22px;background:rgba(0,0,0,.2);border-left:3px solid var(--color-gold);border-radius:0 12px 12px 0;color:var(--color-text-soft);font-size:1rem;line-height:1.6;}
.ag-racines__deal-list strong{color:var(--color-gold);}
.ag-racines__deal-note{text-align:center;color:var(--color-text-secondary);font-size:.95rem;line-height:1.6;margin:0;padding:16px 20px;background:rgba(0,132,61,.08);border:1px solid rgba(0,132,61,.25);border-radius:12px;}
.ag-racines__for{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-top:40px;}
.ag-racines__for-item{padding:24px 26px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.08);border-radius:14px;color:var(--color-text-soft);font-size:1.02rem;line-height:1.5;}
@media(max-width:768px){
    .ag-racines__steps{grid-template-columns:1fr;gap:16px;}
    .ag-racines__for{grid-template-columns:1fr;}
    .ag-racines__deal{padding:30px 22px;}
}
</style>

<?php get_footer(); ?>
