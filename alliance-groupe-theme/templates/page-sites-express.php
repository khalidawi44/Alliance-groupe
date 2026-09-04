<?php
/**
 * Template Name: Sites Express
 *
 * Vente de sites "produit" 100% en ligne, sans rendez-vous :
 * 3 packs a prix fixe -> paiement PayPal (liens de paiement) -> formulaire de brief.
 * Les liens de paiement se configurent dans Reglages > Liens de paiement (admin).
 */
get_header();

/* Les packs viennent de inc/ag-offres.php : source de vérité UNIQUE (prix,
   contenu, délais). Ne jamais les réécrire ici — l'accueil affiche les mêmes. */
$ph    = defined( 'AG_PACK_PLACEHOLDER' ) ? AG_PACK_PLACEHOLDER : 'STRIPE_PLACEHOLDER';
$packs = function_exists( 'ag_sites_express_packs' ) ? ag_sites_express_packs() : array();

$maint = array(
	'serenite' => array(
		'url'  => get_option( 'ag_stripe_maint_serenite_url', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ARWC8AS5NM7HQ' ),
		'nom'  => 'Sérénité', 'prix' => '29 €',
		'desc' => 'Ton site toujours en ligne et sécurisé.',
		'feats'=> array( 'Hébergement + nom de domaine', 'Sauvegardes automatiques', 'Mises à jour & sécurité', 'Support par email' ),
	),
	'croissance' => array(
		'url'  => get_option( 'ag_stripe_maint_croissance_url', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=374X8TRBN6QQW' ),
		'nom'  => 'Croissance', 'prix' => '59 €', 'star' => true,
		'desc' => 'Ton site évolue avec ton activité.',
		'feats'=> array( 'Tout « Sérénité »', '30 min de retouches / mois', 'Suivi & conseils', 'Statistiques de visites' ),
	),
	'performance' => array(
		'url'  => get_option( 'ag_stripe_maint_performance_url', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9Y6BHFKDPLMSQ' ),
		'nom'  => 'Performance', 'prix' => '99 €',
		'desc' => 'On pousse ta visibilité chaque mois.',
		'feats'=> array( 'Tout « Croissance »', 'SEO continu (Google)', '1h de retouches / mois', 'Rapport mensuel' ),
	),
);

$paid = isset( $_GET['paid'] ) ? sanitize_key( $_GET['paid'] ) : '';
$brief_ok = isset( $_GET['brief'] ) && $_GET['brief'] === 'ok';
$abo_ok   = isset( $_GET['abo'] ) && $_GET['abo'] === 'ok';
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div><div class="ag-hero__orb ag-hero__orb--2"></div></div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Sites Express</span>
            <h1 class="ag-hero__title"><span class="ag-line">Ton site pro,</span><span class="ag-line"><em>sécurisé dès le départ.</em></span></h1>
            <p class="ag-hero__sub">
                Choisis ton pack, paie en ligne, remplis un court formulaire — on s'occupe de tout.
                Rapide, soigné, et <strong>blindé dès le premier jour</strong> (pas de dette de sécurité à corriger plus tard).
            </p>
            <div class="ag-hero__buttons"><a href="#packs" class="ag-btn-gold">Voir les packs →</a></div>
        </div>
    </section>

    <!-- Barre de réassurance -->
    <div class="ag-xpress__reassure">
        <span><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:7px"><path d="M13 2 4 14h6l-1 8 9-12h-6z"/></svg>Livraison en quelques jours</span>
        <span><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:7px"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>Paiement sécurisé PayPal</span>
        <span>Payable en 4× sans frais</span>
        <span><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:7px"><path d="M4 12l5 5L20 6"/></svg>Révisions incluses</span>
        <span>Interlocuteur unique — studio à Nantes</span>
        <span><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:7px"><path d="M12 3l7 2.5v5.6c0 4.2-2.9 7.2-7 8.4-4.1-1.2-7-4.2-7-8.4V5.5z"/><path d="M9 12l2 2 4-4"/></svg>Sécurisé dès le départ</span>
    </div>

    <!-- Urgence / rareté -->
    <div class="ag-xpress__urgency">On lance un <strong>nombre limité de sites par semaine</strong> pour garder la qualité — réserve ta place ce mois-ci.</div>

    <!-- Packs -->
    <section class="ag-section ag-section--light" id="packs">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Nos packs</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Un prix clair, <em>tout compris</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Pas de devis, pas d'attente. Tu paies, tu briefes, on livre.</p>

            <div class="ag-xpress__grid">
                <?php foreach ( $packs as $key => $p ) :
                    $is_set = ( $p['url'] !== $ph && $p['url'] !== '' );
                    $cta_url = $is_set ? $p['url'] : ( home_url( '/sites-express?paid=' . $key ) . '#brief' );
                    $img = get_stylesheet_directory_uri() . '/assets/images/produits/produit-' . $key . '.jpg';
                ?>
                <div class="ag-xpress__card ag-xpress__card--img<?php echo ! empty( $p['star'] ) ? ' ag-xpress__card--star' : ''; ?> ag-anim" data-anim="card">
                    <img class="ag-xpress__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $p['nom'] . ' — ' . $p['prix'] ); ?>" loading="lazy" width="1024" height="1024">
                    <?php
                    /* Le prix EN TEXTE. Il n'existait que dans le JPEG et dans
                       l'attribut alt : incopiable, invisible pour un lecteur
                       d'écran, absent pour Google, et disparu si l'image passe
                       mal en 4G — sur la page dont c'est tout le métier. */
                    ?>
                    <div class="ag-xpress__head">
                        <h3 class="ag-xpress__name"><?php echo esc_html( $p['nom'] ); ?></h3>
                        <p class="ag-xpress__tarif"><?php echo esc_html( $p['prix'] ); ?></p>
                        <?php if ( ! empty( $p['delai'] ) ) : ?>
                        <p class="ag-xpress__delai"><?php echo esc_html( $p['delai'] ); ?></p>
                        <?php endif; ?>
                        <?php if ( ! empty( $p['desc'] ) ) : ?>
                        <p class="ag-xpress__desc"><?php echo esc_html( $p['desc'] ); ?></p>
                        <?php endif; ?>
                    </div>
                    <ul class="ag-xpress__feats">
                        <?php
                        /* Le délai est déjà affiché en tête de carte : on ne le
                           répète pas dans la liste. */
                        foreach ( $p['feats'] as $f ) {
                            if ( ! empty( $p['delai'] ) && $f === $p['delai'] ) { continue; }
                            echo '<li>' . esc_html( $f ) . '</li>';
                        }
                        ?>
                    </ul>
                    <p class="ag-xpress__pay4">Payable en 4× sans frais via PayPal</p>
                    <a href="<?php echo esc_url( $cta_url ); ?>" class="ag-btn-gold ag-xpress__cta"><span class="ag-l">Commander</span><svg class="ag-trait" viewBox="0 0 34 14" aria-hidden="true"><line x1="1" y1="7" x2="31" y2="7"/><polyline points="25,1 31,7 25,13"/></svg></a>
                    <?php if ( ! $is_set ) echo '<small class="ag-xpress__note">Paiement bientôt en ligne — remplis le brief, on te recontacte.</small>'; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Garantie -->
    <section class="ag-section ag-section--onyx">
        <div class="ag-container">
            <h2 class="ag-section__title ag-anim" data-anim="title">Zéro risque pour toi</h2>
            <div class="ag-xpress__guar">
                <div class="ag-xpress__guar-item"><span class="ag-xpress__guar-ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M12 3l7 2.5v5.6c0 4.2-2.9 7.2-7 8.4-4.1-1.2-7-4.2-7-8.4V5.5z"/><path d="M9 12l2 2 4-4"/></svg></span><strong>Satisfait ou ajusté</strong><span>On retouche jusqu'à ce que ça te plaise.</span></div>
                <div class="ag-xpress__guar-item"><span class="ag-xpress__guar-ic">⏱</span><strong>Délai annoncé tenu</strong><span>En retard de notre faute ? 1 mois de maintenance offert.</span></div>
                <div class="ag-xpress__guar-item"><span class="ag-xpress__guar-ic"></span><strong>Payable en 4×</strong><span>Sans frais, via PayPal — étale le paiement.</span></div>
                <div class="ag-xpress__guar-item"><span class="ag-xpress__guar-ic"></span><strong>Facture &amp; sans engagement</strong><span>Facture émise, maintenance résiliable à tout moment.</span></div>
            </div>
        </div>
    </section>

    <!-- Avant / Après -->
    <section class="ag-section ag-section--light">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Avant / Après</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">La différence qui ramène <em>des clients</em></h2>
            <div class="ag-xpress__ba">
                <div class="ag-xpress__ba-col ag-xpress__ba-col--before">
                    <span class="ag-xpress__ba-tag">Avant</span>
                    <ul>
                        <li>Pas de site, ou un site lent et daté</li>
                        <li>Invisible sur Google</li>
                        <li>Illisible sur mobile</li>
                        <li>Les clients vont chez le concurrent</li>
                    </ul>
                </div>
                <div class="ag-xpress__ba-col ag-xpress__ba-col--after">
                    <span class="ag-xpress__ba-tag">Après</span>
                    <ul>
                        <li>Site premium qui inspire confiance</li>
                        <li>Optimisé Google (SEO de base)</li>
                        <li>Parfait sur mobile, ultra-rapide</li>
                        <li>Contact &amp; commandes 24h/24</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Preuve sociale (à compléter avec de vrais avis) -->
    <section class="ag-section ag-section--onyx">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Ils nous font confiance</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Pourquoi nous choisir</h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Paiement 100 % sécurisé (PayPal), équipe française joignable, révisions incluses, sans engagement. Tu gardes la main, on fait le boulot.</p>
            <?php
            // Avis clients : remplis ce tableau avec de VRAIS avis dès que tu en as.
            $ag_reviews = apply_filters( 'ag_express_reviews', array() );
            if ( ! empty( $ag_reviews ) ) : ?>
                <div class="ag-xpress__reviews">
                    <?php foreach ( $ag_reviews as $r ) : ?>
                        <blockquote>« <?php echo esc_html( $r['text'] ?? '' ); ?> »<cite>— <?php echo esc_html( $r['author'] ?? '' ); ?></cite></blockquote>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section class="ag-section ag-section--light">
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

    <!-- Maintenance (abonnement) -->
    <section class="ag-section ag-section--onyx" id="maintenance">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Après la livraison · revenu tranquille</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Garde ton site <em>au top</em>, chaque mois</h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Un site sans maintenance se dégrade (sécurité, bugs, Google). Pour <strong>quelques euros par mois</strong>, on s'occupe de tout — et toi tu restes serein. <strong>Vivement recommandé avec chaque site.</strong> Résiliable à tout moment.</p>

            <?php if ( $abo_ok ) : ?>
                <p style="text-align:center;background:rgba(0,132,61,.12);border:1px solid rgba(0,132,61,.3);border-radius:12px;padding:16px 20px;color:#fff;max-width:560px;margin:24px auto 0;">Merci, ton abonnement est actif ! Ton site est entre de bonnes mains.</p>
            <?php endif; ?>

            <div class="ag-xpress__grid">
                <?php foreach ( $maint as $key => $p ) :
                    $is_set  = ( $p['url'] !== $ph && $p['url'] !== '' );
                    $cta_url = $is_set ? $p['url'] : ( home_url( '/contact' ) );
                    $img = get_stylesheet_directory_uri() . '/assets/images/produits/produit-' . $key . '.jpg';
                ?>
                <div class="ag-xpress__card ag-xpress__card--img<?php echo ! empty( $p['star'] ) ? ' ag-xpress__card--star' : ''; ?> ag-anim" data-anim="card">
                    <img class="ag-xpress__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $p['nom'] . ' — ' . $p['prix'] . '/mois' ); ?>" loading="lazy" width="1024" height="1024">
                    <div class="ag-xpress__head">
                        <h3 class="ag-xpress__name"><?php echo esc_html( $p['nom'] ); ?></h3>
                        <p class="ag-xpress__tarif"><?php echo esc_html( $p['prix'] ); ?><span class="ag-xpress__per">/mois</span></p>
                        <?php if ( ! empty( $p['desc'] ) ) : ?>
                        <p class="ag-xpress__desc"><?php echo esc_html( $p['desc'] ); ?></p>
                        <?php endif; ?>
                    </div>
                    <ul class="ag-xpress__feats">
                        <?php foreach ( $p['feats'] as $f ) echo '<li>' . esc_html( $f ) . '</li>'; ?>
                    </ul>
                    <a href="<?php echo esc_url( $cta_url ); ?>" class="ag-btn-gold ag-xpress__cta">S'abonner →</a>
                    <?php if ( ! $is_set ) echo '<small class="ag-xpress__note">Abonnement bientôt en ligne — contacte-nous en attendant.</small>'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Site 100% personnalisé → prise de RDV (le SEUL cas qui passe par un échange) -->
            <div style="max-width:760px;margin:34px auto 0;background:linear-gradient(135deg,rgba(212,180,92,.12),rgba(20,20,26,.6));border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:26px 22px;text-align:center;">
                <div style="font-size:1.7rem;"></div>
                <h3 style="font-family:Georgia,serif;font-size:1.5rem;margin:6px 0 8px;color:#fff;">Un projet 100% sur-mesure ?</h3>
                <p style="color:var(--color-text-secondary);margin:0 auto 18px;max-width:520px;">Les packs ci-dessus se commandent et se paient <strong>directement en ligne</strong>. Pour un site <strong>entièrement personnalisé</strong>, prenez rendez-vous : dites-nous ce que vous voulez, on vous rappelle.</p>
                <a href="<?php echo esc_url( add_query_arg( 'ag_rdv', 1, home_url( '/' ) ) ); ?>" class="ag-btn-gold" style="display:inline-block;">Prendre rendez-vous pour un site personnalisé →</a>
            </div>
        </div>
    </section>

    <!-- FAQ : lève les objections pour acheter sans échange -->
    <section class="ag-section ag-section--light" id="faq-express">
        <div class="ag-container ag-container--narrow">
            <span class="ag-tag ag-anim" data-anim="tag">Questions fréquentes</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Tout ce qu'il faut savoir <em>avant d'acheter</em></h2>
            <div class="ag-xpress__faq">
                <details><summary>Comment ça marche, sans rendez-vous ?</summary><p>Tu choisis ton pack, tu paies en ligne, puis tu remplis un court formulaire (10 min) avec tes infos. On construit ton site et on t'envoie une vidéo de présentation. Les retouches se font par écrit. Aucun appel nécessaire.</p></details>
                <details><summary>En combien de temps mon site est livré ?</summary><p>Essentiel : ~5 jours · Pro : ~8 jours · Boutique : ~12 jours, à partir du moment où tu as envoyé ton brief et tes éléments (logo, photos, textes).</p></details>
                <details><summary>Et si le résultat ne me plaît pas ?</summary><p>Des révisions sont incluses : on ajuste jusqu'à ce que tu sois satisfait, par échanges écrits. On ne te laisse pas avec un site qui ne te convient pas.</p></details>
                <details><summary>Je n'ai pas de logo ni de photos, c'est grave ?</summary><p>Non. On peut partir de ton nom et utiliser des visuels professionnels adaptés à ton métier. Si tu as un logo, tu l'envoies ; sinon on t'oriente.</p></details>
                <details><summary>Le nom de domaine et l'hébergement sont inclus ?</summary><p>On s'occupe de la mise en ligne. L'hébergement + le nom de domaine sont gérés via l'abonnement maintenance (à partir de 29€/mois) pour garder ton site en ligne, sécurisé et à jour.</p></details>
                <details><summary>Et après la livraison, qui s'occupe du site ?</summary><p>Toi si tu veux, ou nous via l'abonnement maintenance : mises à jour, sécurité, sauvegardes, petites retouches et référencement. Tu ne touches à rien.</p></details>
                <details><summary>Le paiement est-il sécurisé ?</summary><p>Oui, 100% via PayPal (leader mondial du paiement en ligne). Tes données bancaires ne passent jamais par nous : tu paies sur la page sécurisée PayPal, par carte ou avec ton compte PayPal.</p></details>
                <details><summary>Puis-je avoir une facture ?</summary><p>Oui, une facture est émise automatiquement pour chaque commande.</p></details>
            </div>
        </div>
    </section>

    <!-- Brief -->
    <section class="ag-section ag-section--or" id="brief">
        <div class="ag-container ag-container--narrow">
            <?php if ( $brief_ok ) : ?>
                <div class="ag-question-success">
                    <div class="ag-question-success__check"></div>
                    <h2>Brief reçu</h2>
                    <p class="ag-question-success__sub">Merci ! On démarre la production de ton site et on t'envoie une première version rapidement, avec une vidéo. Tout par écrit, sans rendez-vous.</p>
                </div>
                <div class="ag-xpress__refer">
                    <p><strong>Parraine un commerçant</strong> — s'il commande un site en disant qu'il vient de ta part, tu gagnes <strong>1 mois de maintenance offert</strong>.</p>
                    <textarea id="ag-refer-msg" readonly rows="3">Je viens de faire faire mon site avec Alliance Groupe : à prix fixe (dès 490 €), livré en quelques jours, sans rendez-vous. Jette un œil <?php echo esc_url( home_url( '/sites-express' ) ); ?> (dis que tu viens de ma part)</textarea>
                    <button type="button" class="ag-btn-gold" id="ag-refer-copy" onclick="navigator.clipboard.writeText(document.getElementById('ag-refer-msg').value).then(function(){var b=document.getElementById('ag-refer-copy');b.textContent='Copié';setTimeout(function(){b.textContent='Copier mon message';},1500);});">Copier mon message</button>
                </div>
            <?php elseif ( ! $paid ) : ?>
                <span class="ag-tag">Étape 2</span>
                <h2 class="ag-section__title">Le brief, <em>après ta commande</em></h2>
                <p class="ag-section__desc">Le parcours est simple : tu choisis ton pack, tu paies en ligne (sécurisé PayPal), <strong>puis</strong> tu remplis le brief pour qu'on lance ton site. Pas besoin de le faire avant.</p>
                <div class="ag-xpress__steps">
                    <div class="ag-xpress__step"><span class="ag-xpress__step-n">1</span><strong>Choisis ton pack</strong><span>Essentiel, Pro ou Boutique.</span></div>
                    <div class="ag-xpress__step"><span class="ag-xpress__step-n">2</span><strong>Tu paies en ligne</strong><span>Carte ou PayPal, 100% sécurisé.</span></div>
                    <div class="ag-xpress__step"><span class="ag-xpress__step-n">3</span><strong>Tu remplis le brief</strong><span>On démarre la production.</span></div>
                </div>
                <div class="ag-xpress__brief-cta">
                    <a href="#packs" class="ag-btn-gold">Voir les packs →</a>
                    <a href="<?php echo esc_url( add_query_arg( 'paid', '1' ) ); ?>#brief" class="ag-xpress__paid-link">J'ai déjà payé — remplir le brief</a>
                </div>
            <?php else : ?>
                <span class="ag-tag">Étape 2</span>
                <h2 class="ag-section__title">Le brief de <em>ton site</em></h2>
                <p class="ag-section__desc"><?php echo in_array( $paid, array_keys( $packs ), true ) ? 'Paiement reçu — remplis ce brief pour qu\'on démarre.' : 'Remplis ce brief pour qu\'on lance ton site.'; ?></p>
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
                    <div class="ag-form__group">
                        <label for="b-pays">Pays (droit applicable)</label>
                        <select id="b-pays" name="pays">
                            <?php foreach ( ( function_exists( 'ag_countries' ) ? ag_countries() : array( 'France' ) ) as $ag_c ) : ?>
                                <option value="<?php echo esc_attr( $ag_c ); ?>" <?php selected( 'France', $ag_c ); ?>><?php echo esc_html( $ag_c ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <label style="display:flex;gap:10px;align-items:flex-start;margin:6px 0 16px;font-size:.92rem;color:var(--color-text-secondary);">
                        <input type="checkbox" name="cgv" value="1" required style="margin-top:4px;">
                        <span>J'ai lu et j'accepte le <a href="<?php echo esc_url( home_url( '/contrat-client' ) ); ?>" target="_blank" rel="noopener" style="color:var(--color-gold);text-decoration:underline;">contrat de prestation &amp; les CGV</a>, et je demande le démarrage immédiat de la prestation. *</span>
                    </label>
                    <button type="submit" class="ag-btn-gold">Envoyer mon brief →</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

</main>

<style>
#brief .ag-form__group{position:relative;margin-bottom:0;}
#brief .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
#brief .ag-form__group input,#brief .ag-form__group textarea{padding:14px 18px;}
.ag-xpress__reassure{display:flex;flex-wrap:wrap;justify-content:center;gap:14px 30px;padding:18px 24px;background:rgba(212,180,92,.06);border-top:1px solid rgba(212,180,92,.15);border-bottom:1px solid rgba(212,180,92,.15);color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-xpress__urgency{text-align:center;padding:14px 20px;background:linear-gradient(90deg,rgba(243,122,31,.16),rgba(212,180,92,.12));color:#fff;font-size:.95rem;border-bottom:1px solid rgba(212,180,92,.18);}
.ag-xpress__pay4{margin:0 16px 12px;text-align:center;color:#e8c766;font-size:.85rem;font-weight:600;}
.ag-xpress__guar{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:34px;}
.ag-xpress__guar-item{padding:24px 18px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;text-align:center;display:flex;flex-direction:column;gap:6px;}
.ag-xpress__guar-ic{font-size:2rem;}
.ag-xpress__guar-item strong{color:#fff;font-size:1.02rem;}
.ag-xpress__guar-item span:not(.ag-xpress__guar-ic){color:var(--color-text-soft);font-size:.9rem;line-height:1.45;}
.ag-xpress__ba{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:34px;}
.ag-xpress__ba-col{padding:28px 24px;border-radius:18px;border:1px solid rgba(255,255,255,.1);}
.ag-xpress__ba-col--before{background:rgba(255,255,255,.02);}
.ag-xpress__ba-col--after{background:linear-gradient(160deg,rgba(212,180,92,.12),rgba(243,122,31,.05));border-color:rgba(212,180,92,.4);}
.ag-xpress__ba-tag{display:inline-block;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;}
.ag-xpress__ba-col--before .ag-xpress__ba-tag{background:rgba(255,255,255,.1);color:#cfc7b8;}
.ag-xpress__ba-col--after .ag-xpress__ba-tag{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;}
.ag-xpress__ba-col ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;}
.ag-xpress__ba-col--before li{color:var(--color-text-muted);padding-left:24px;position:relative;}
.ag-xpress__ba-col--before li::before{content:'';position:absolute;left:0;color:#b35a5a;}
.ag-xpress__ba-col--after li{color:#fff;padding-left:24px;position:relative;}
.ag-xpress__ba-col--after li::before{content:'';position:absolute;left:0;color:#4bbf77;font-weight:800;}
.ag-xpress__reviews{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-top:30px;}
.ag-xpress__reviews blockquote{margin:0;padding:22px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;color:#fff;font-style:italic;line-height:1.55;}
.ag-xpress__reviews cite{display:block;margin-top:12px;color:#e8c766;font-style:normal;font-weight:700;font-size:.9rem;}
.ag-xpress__steps{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:30px 0;}
.ag-xpress__step{padding:24px 18px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:6px;}
.ag-xpress__step-n{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:1.1rem;margin-bottom:6px;}
.ag-xpress__step strong{color:#fff;font-size:1.02rem;}
.ag-xpress__step span:not(.ag-xpress__step-n){color:var(--color-text-soft);font-size:.9rem;line-height:1.45;}
.ag-xpress__brief-cta{display:flex;flex-direction:column;align-items:center;gap:14px;margin-top:8px;}
.ag-xpress__paid-link{color:var(--color-text-soft);font-size:.9rem;text-decoration:underline;text-underline-offset:3px;}
.ag-xpress__paid-link:hover{color:#e8c766;}
@media(max-width:760px){.ag-xpress__steps{grid-template-columns:1fr;}}
.ag-xpress__refer{margin-top:26px;padding:22px 24px;background:linear-gradient(160deg,rgba(212,180,92,.12),rgba(243,122,31,.05));border:1px solid rgba(212,180,92,.4);border-radius:16px;text-align:center;}
.ag-xpress__refer p{color:#fff;margin:0 0 14px;}
.ag-xpress__refer textarea{width:100%;padding:14px 16px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(0,0,0,.25);color:#fff;font-size:.92rem;line-height:1.5;resize:vertical;margin-bottom:12px;}
@media(max-width:760px){.ag-xpress__guar{grid-template-columns:1fr 1fr;}.ag-xpress__ba{grid-template-columns:1fr;}}
.ag-xpress__faq{margin-top:32px;display:flex;flex-direction:column;gap:12px;}
.ag-xpress__faq details{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.16);border-radius:12px;padding:0 20px;transition:border-color .3s;}
.ag-xpress__faq details[open]{border-color:rgba(212,180,92,.4);}
.ag-xpress__faq summary{cursor:pointer;list-style:none;padding:18px 0;font-weight:700;color:#fff;font-size:1.02rem;position:relative;padding-right:32px;}
.ag-xpress__faq summary::-webkit-details-marker{display:none;}
.ag-xpress__faq summary::after{content:'+';position:absolute;right:4px;top:50%;transform:translateY(-50%);color:var(--color-gold);font-size:1.4rem;font-weight:400;transition:transform .3s;}
.ag-xpress__faq details[open] summary::after{transform:translateY(-50%) rotate(45deg);}
.ag-xpress__faq p{margin:0 0 18px;color:var(--color-text-secondary);line-height:1.65;font-size:.96rem;}
.ag-xpress__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:46px;align-items:start;}
.ag-xpress__card{position:relative;display:flex;flex-direction:column;padding:38px 30px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:20px;transition:transform .4s,border-color .4s,box-shadow .4s;}
.ag-xpress__card:hover{transform:translateY(-6px);border-color:rgba(212,180,92,.45);box-shadow:0 30px 70px rgba(0,0,0,.45);}
.ag-xpress__card--star{border-color:rgba(212,180,92,.5);background:linear-gradient(160deg,rgba(212,180,92,.12),rgba(243,122,31,.05));}
.ag-xpress__card--img{padding:14px 14px 30px;}
.ag-xpress__img{display:block;width:100%;height:auto;aspect-ratio:1/1;object-fit:cover;border-radius:16px;margin-bottom:24px;border:1px solid rgba(212,180,92,.16);}
.ag-xpress__card--img .ag-xpress__feats,.ag-xpress__card--img .ag-xpress__cta,.ag-xpress__card--img .ag-xpress__head{margin-left:16px;margin-right:16px;}
/* En-tête TEXTE de la carte : nom, prix, délai. Le prix est l'information que
   le visiteur est venu chercher — il ne doit jamais dépendre d'une image.
   Or Champagne plein (pas de dégradé) : l'orange reste réservé à la couche
   cinéma de l'accueil, et un dégradé sur du texte se lit moins bien. */
.ag-xpress__head{margin-bottom:20px;}
.ag-xpress__tarif{font-family:var(--font-serif);font-style:italic;font-size:2.5rem;line-height:1;color:var(--color-gold);margin:2px 0 0;}
.ag-xpress__tarif .ag-xpress__per{font-family:var(--font-sans);font-style:normal;font-size:.95rem;font-weight:600;color:var(--color-text-muted);margin-left:4px;}
.ag-xpress__delai{margin:8px 0 0;font-size:.82rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:var(--color-gold);}
.ag-xpress__head .ag-xpress__desc{margin:10px 0 0;}
/* Sur fond ivoire, l'or clair ne tient pas le contraste : version assombrie. */
.ag-section--light .ag-xpress__tarif,
.ag-section--light .ag-xpress__delai{color:#8a6d1f;}
.ag-xpress__badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#1a1207;font-weight:800;font-size:.72rem;letter-spacing:1px;text-transform:uppercase;padding:5px 14px;border-radius:20px;white-space:nowrap;}
.ag-xpress__name{font-family:var(--font-serif);font-size:1.5rem;color:#fff;margin:0 0 6px;}
.ag-xpress__price{font-family:var(--font-serif);font-size:2.4rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;margin-bottom:8px;}
.ag-xpress__per{font-family:var(--font-sans);font-size:.95rem;font-weight:600;-webkit-text-fill-color:var(--color-text-muted);color:var(--color-text-muted);}
.ag-xpress__desc{color:var(--color-text-secondary);font-size:.95rem;margin:0 0 18px;}
.ag-xpress__feats{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:10px;}
.ag-xpress__feats li{position:relative;padding-left:26px;color:var(--color-text-soft);font-size:.95rem;line-height:1.4;}
.ag-xpress__feats li::before{content:'';position:absolute;left:0;color:var(--color-gold);font-weight:800;}
.ag-xpress__cta{margin-top:auto;text-align:center;}
.ag-xpress__note{display:block;margin-top:10px;color:var(--color-text-muted);font-size:.8rem;text-align:center;}
@media(max-width:900px){.ag-xpress__grid{grid-template-columns:1fr;max-width:440px;margin-left:auto;margin-right:auto;}}

/* ── Étapes "3 étapes" : styles de BASE (manquaient sur cette page) ── */
.ag-amb__steps{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:32px;}
.ag-amb__step{padding:28px 24px;border:1px solid rgba(212,180,92,.25);border-radius:18px;}
.ag-amb__step h3{margin:8px 0;font-size:1.15rem;}
.ag-amb__step p{margin:0;line-height:1.5;}
.ag-amb__num{display:inline-block;font-family:var(--font-serif);font-size:1.8rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;}
@media(max-width:900px){.ag-amb__steps{grid-template-columns:1fr;max-width:480px;margin-left:auto;margin-right:auto;}}

/* ── Variantes CLAIRES (sections ivoire) : cartes blanches + texte foncé ── */
.ag-section--light .ag-xpress__card,
.ag-section--light .ag-xpress__guar-item,
.ag-section--light .ag-xpress__reviews blockquote,
.ag-section--light .ag-amb__step{background:#fff;border-color:rgba(212,180,92,.45);box-shadow:0 12px 34px rgba(120,100,40,.10);}
.ag-section--light .ag-xpress__card--star{background:linear-gradient(160deg,#fffaf0,#fdf3df);border-color:rgba(212,180,92,.7);}
.ag-section--light .ag-xpress__card:hover{box-shadow:0 28px 60px rgba(120,100,40,.20);}
.ag-section--light .ag-xpress__name,
.ag-section--light .ag-xpress__guar-item strong,
.ag-section--light .ag-xpress__reviews blockquote,
.ag-section--light .ag-amb__step h3{color:#17150f;}
.ag-section--light .ag-xpress__feats li,
.ag-section--light .ag-xpress__guar-item span:not(.ag-xpress__guar-ic),
.ag-section--light .ag-xpress__desc,
.ag-section--light .ag-amb__step p{color:#5b5446;}
.ag-section--light .ag-xpress__img{border-color:rgba(212,180,92,.3);}
.ag-section--light .ag-xpress__pay4{color:#b07d1a;}
.ag-section--light .ag-xpress__note{color:#8a7f6a;}
.ag-section--light .ag-xpress__reviews cite{color:#8a6d1f;}
.ag-section--light .ag-xpress__faq details{background:#fff;border-color:rgba(212,180,92,.4);}
.ag-section--light .ag-xpress__faq summary{color:#17150f;}
.ag-section--light .ag-xpress__faq p{color:#5b5446;}
.ag-section--light .ag-xpress__ba-col{border-color:rgba(120,100,40,.18);}
.ag-section--light .ag-xpress__ba-col--before{background:#faf7f0;}
.ag-section--light .ag-xpress__ba-col--before li{color:#6b6354;}
.ag-section--light .ag-xpress__ba-col--before .ag-xpress__ba-tag{background:rgba(120,100,40,.12);color:#6b6354;}
.ag-section--light .ag-xpress__ba-col--after li{color:#3a3428;}
</style>

<?php get_footer(); ?>
