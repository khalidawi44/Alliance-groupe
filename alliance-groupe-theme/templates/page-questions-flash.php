<?php
/**
 * Template Name: Questions Flash
 *
 * Pricing grid for the written-consultation offers ("Questions Flash")
 * + post-purchase form for the buyer to submit their question.
 * The form is shown automatically when the page is hit with ?paid=1,
 * which is the Success URL configured on the Stripe Payment Links.
 */
get_header();

// ── Stripe URLs (fallback to /contact if not configured) ──────
$contact_url = home_url( '/contact' );
$stripe_single = get_option( 'ag_stripe_question_single_url', 'STRIPE_PLACEHOLDER' );
$stripe_pack   = get_option( 'ag_stripe_question_pack_url', 'STRIPE_PLACEHOLDER' );
$stripe_sub    = get_option( 'ag_stripe_question_sub_url', 'STRIPE_PLACEHOLDER' );

$btn_url = function( $stripe ) use ( $contact_url ) {
	if ( 'STRIPE_PLACEHOLDER' === $stripe || '' === $stripe ) {
		return $contact_url . '?source=questions-flash';
	}
	return $stripe;
};

// ── Is the buyer coming back from Stripe? ───────────────────
$paid_pack = isset( $_GET['paid'] ) && isset( $_GET['pack'] ) ? sanitize_key( $_GET['pack'] ) : '';
$pack_labels = array(
	'single' => '1 Question Flash (45 €)',
	'pack'   => 'Pack 3 Questions (120 €)',
	'sub'    => 'Abonnement Expert (199 €/mois)',
);
$is_success    = isset( $pack_labels[ $paid_pack ] );
$question_sent = isset( $_GET['question_sent'] ) && '1' === $_GET['question_sent'];
?>

<main id="ag-main-content">

    <!-- Hero -->
    <section class="ag-hero" style="min-height:50vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Questions Flash — réponses écrites</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Posez <em>une question</em>,</span>
                <span class="ag-line">recevez une vraie réponse</span>
            </h1>
            <p class="ag-hero__sub">Pas besoin de caler un appel. Vous payez, vous posez votre question par écrit, et vous recevez sous 48h une analyse experte détaillée — comme un avocat répondrait à une consultation écrite.</p>
        </div>
    </section>

    <?php if ( $question_sent ) : ?>
    <!-- ── QUESTION SUBMITTED CONFIRMATION ─────────────────── -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <div class="ag-question-success">
                <span class="ag-question-success__check">✓</span>
                <h2>Votre question a bien été envoyée !</h2>
                <p class="ag-question-success__sub">
                    Fabrizio l'a reçue et commence à y travailler. Vous recevrez une analyse écrite détaillée dans votre boîte mail <strong>sous 48h ouvrées</strong>.
                </p>
                <p style="color:#b0b0bc;max-width:560px;margin:16px auto 28px;line-height:1.6;">
                    Un email de confirmation vient de vous être envoyé (vérifiez vos spams si vous ne le voyez pas).
                    Si vous avez besoin d'ajouter du contexte ou une précision, répondez simplement à cet email.
                </p>
                <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                    <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="ag-btn-outline">Lire notre blog →</a>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-btn-gold">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </section>
    <?php elseif ( $is_success ) : ?>
    <!-- ── POST-PURCHASE FORM (shown when redirected from Stripe) ── -->
    <section class="ag-section ag-section--darker" id="ag-question-form">
        <div class="ag-container">
            <div class="ag-question-success">
                <span class="ag-question-success__check">✓</span>
                <h2>Paiement confirmé — merci pour votre achat&nbsp;!</h2>
                <p class="ag-question-success__sub">
                    Vous avez réglé <strong><?php echo esc_html( $pack_labels[ $paid_pack ] ); ?></strong>.
                    <?php if ( 'sub' === $paid_pack ) : ?>
                        Votre abonnement est actif — vous pouvez poser jusqu'à 8 questions par mois.
                    <?php elseif ( 'pack' === $paid_pack ) : ?>
                        Vous avez 3 questions utilisables sur 90 jours. Posez votre première question ci-dessous, nous vous répondrons sous 48h.
                    <?php else : ?>
                        Posez votre question ci-dessous, nous vous répondrons sous 48h avec une analyse écrite détaillée.
                    <?php endif; ?>
                </p>

                <form class="ag-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
                    <input type="hidden" name="action" value="ag_submit_question">
                    <input type="hidden" name="pack" value="<?php echo esc_attr( $paid_pack ); ?>">
                    <?php wp_nonce_field( 'ag_question_nonce', 'ag_question_nonce' ); ?>

                    <div class="ag-form__row">
                        <div class="ag-form__group">
                            <label for="ag-q-name">Nom complet *</label>
                            <input type="text" id="ag-q-name" name="name" required placeholder="Votre nom">
                        </div>
                        <div class="ag-form__group">
                            <label for="ag-q-email">Email (pour recevoir la réponse) *</label>
                            <input type="email" id="ag-q-email" name="email" required placeholder="votre@email.com">
                        </div>
                    </div>

                    <div class="ag-form__group">
                        <label for="ag-q-activity">Votre activité en 1 phrase</label>
                        <input type="text" id="ag-q-activity" name="activity" placeholder="Ex. : restaurant italien à Nantes, 2 salariés, ouvert depuis 3 ans">
                    </div>

                    <div class="ag-form__group">
                        <label for="ag-q-question">Votre question précise * <small style="color:#888;font-weight:400;">(plus c'est précis, plus la réponse sera utile)</small></label>
                        <textarea id="ag-q-question" name="question" required rows="6" placeholder="Ex. : Mon site WordPress reçoit 200 visites/mois. Comment je fais pour passer à 1000 sans exploser mon budget publicité ?"></textarea>
                    </div>

                    <div class="ag-form__group">
                        <label for="ag-q-context">Contexte complémentaire (facultatif)</label>
                        <textarea id="ag-q-context" name="context" rows="3" placeholder="Ce que vous avez déjà essayé, votre budget, vos contraintes..."></textarea>
                    </div>

                    <button type="submit" class="ag-btn-gold">Envoyer ma question →</button>

                    <p style="color:#b0b0bc;font-size:.88rem;margin-top:12px;text-align:center;">
                        Vous recevrez une réponse écrite détaillée sous 48h ouvrées à l'adresse indiquée.
                    </p>
                </form>
            </div>
        </div>
    </section>
    <?php else : ?>

    <!-- ── PRICING GRID (normal page view) ─────────────────── -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div class="ag-qpack">

                <!-- 1 Question -->
                <div class="ag-qpack__card">
                    <span class="ag-qpack__tagline">Idéal pour tester</span>
                    <h3 class="ag-qpack__label">1 Question Flash</h3>
                    <div class="ag-qpack__price">
                        <strong>45 €</strong>
                    </div>
                    <ul class="ag-qpack__features">
                        <li>✓ Réponse écrite experte (300-500 mots)</li>
                        <li>✓ Livrée sous 48h ouvrées</li>
                        <li>✓ Liens et ressources inclus</li>
                        <li>✓ 1 question précise</li>
                    </ul>
                    <a href="<?php echo esc_url( $btn_url( $stripe_single ) ); ?>" class="ag-btn-outline" target="_blank" rel="noopener">
                        Acheter — 45 € →
                    </a>
                </div>

                <!-- Pack 3 -->
                <div class="ag-qpack__card ag-qpack__card--hero">
                    <span class="ag-qpack__badge">⭐ Économique</span>
                    <span class="ag-qpack__tagline">-11 % par question</span>
                    <h3 class="ag-qpack__label">Pack 3 Questions</h3>
                    <div class="ag-qpack__price">
                        <strong>120 €</strong>
                        <small>soit 40 €/question</small>
                    </div>
                    <ul class="ag-qpack__features">
                        <li>✓ 3 questions écrites détaillées</li>
                        <li>✓ Chaque réponse sous 48h</li>
                        <li>✓ Utilisables sur 90 jours</li>
                        <li>✓ Sujets variés autorisés</li>
                        <li>✓ Paiement unique</li>
                    </ul>
                    <a href="<?php echo esc_url( $btn_url( $stripe_pack ) ); ?>" class="ag-btn-gold" target="_blank" rel="noopener">
                        Acheter — 120 € →
                    </a>
                </div>

                <!-- Abonnement -->
                <div class="ag-qpack__card">
                    <span class="ag-qpack__tagline">Accompagnement continu</span>
                    <h3 class="ag-qpack__label">Abonnement Expert</h3>
                    <div class="ag-qpack__price">
                        <strong>199 €</strong>
                        <small>/ mois</small>
                    </div>
                    <ul class="ag-qpack__features">
                        <li>✓ Jusqu'à 8 questions / mois</li>
                        <li>✓ Réponses sous 48h</li>
                        <li>✓ Chat privé (WhatsApp ou Slack)</li>
                        <li>✓ Priorité de traitement</li>
                        <li>✓ Résiliable à tout moment</li>
                    </ul>
                    <a href="<?php echo esc_url( $btn_url( $stripe_sub ) ); ?>" class="ag-btn-outline" target="_blank" rel="noopener">
                        S'abonner — 199 €/mois →
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <h2 class="ag-section__title" style="text-align:center;">Comment ça marche</h2>
            <p class="ag-section__desc" style="text-align:center;margin:0 auto 48px;">3 étapes simples — aucun appel, aucun rendez-vous à caler.</p>

            <div class="ag-rdv-expect__grid">
                <div class="ag-rdv-expect__item">
                    <span class="ag-rdv-expect__num">1</span>
                    <h3>Vous choisissez un pack</h3>
                    <p>Payez en ligne via Stripe en 30 secondes. Sécurisé, carte bancaire ou Apple Pay. Aucun abonnement caché (sauf si vous choisissez l'Abonnement Expert, évidemment).</p>
                </div>
                <div class="ag-rdv-expect__item">
                    <span class="ag-rdv-expect__num">2</span>
                    <h3>Vous posez votre question</h3>
                    <p>Un formulaire s'ouvre après paiement. Décrivez votre activité et votre question avec le plus de précision possible. Plus c'est précis, plus la réponse est utile.</p>
                </div>
                <div class="ag-rdv-expect__item">
                    <span class="ag-rdv-expect__num">3</span>
                    <h3>Vous recevez une vraie réponse</h3>
                    <p>Sous 48h ouvrées, dans votre boîte mail. Analyse experte rédigée par Fabrizio lui-même. Entre 300 et 500 mots, avec les ressources, outils et liens utiles.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ / Examples -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <h2 class="ag-section__title" style="text-align:center;">Exemples de questions que vous pouvez poser</h2>
            <div class="ag-q-examples">
                <div class="ag-q-example">
                    <span class="ag-q-example__icon">🌐</span>
                    <p>« Je dois refaire mon site. WordPress, Webflow ou Shopify pour mon restaurant ? »</p>
                </div>
                <div class="ag-q-example">
                    <span class="ag-q-example__icon">📈</span>
                    <p>« Mon trafic stagne à 150 visites/mois. Par où je commence pour le décupler ? »</p>
                </div>
                <div class="ag-q-example">
                    <span class="ag-q-example__icon">🤖</span>
                    <p>« Quel outil d'IA me fait gagner le plus de temps en priorité dans mon activité de coach ? »</p>
                </div>
                <div class="ag-q-example">
                    <span class="ag-q-example__icon">🎯</span>
                    <p>« Mon concurrent est premier sur Google, pas moi. Quelles sont les 3 actions à faire en priorité ? »</p>
                </div>
                <div class="ag-q-example">
                    <span class="ag-q-example__icon">💰</span>
                    <p>« J'ai 500€/mois de budget pub. Je mets tout sur Google Ads ou je split avec Meta ? »</p>
                </div>
                <div class="ag-q-example">
                    <span class="ag-q-example__icon">📧</span>
                    <p>« Je veux automatiser mes relances clients sans perdre le côté humain. Par quoi je commence ? »</p>
                </div>
            </div>
        </div>
    </section>

    <?php endif; ?>

</main>

<?php get_footer(); ?>
