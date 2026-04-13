<?php
/**
 * Template Name: Templates WordPress
 */
get_header();
$dl_base = get_stylesheet_directory_uri() . '/assets/downloads/';

/*
 * Stripe Payment Links for the Pro / Premium / Business packs.
 *
 * To activate real payments :
 *   1. Go to https://dashboard.stripe.com/payment-links/create
 *   2. Create 3 Payment Links (one per pack) with the matching amount
 *      (49€, 99€, 149€) and success URL pointing to your /contact
 *      confirmation page.
 *   3. Copy each resulting https://buy.stripe.com/xxxxx URL.
 *   4. Either replace the defaults below, OR set them as WordPress
 *      options via wp-admin/options.php :
 *        ag_stripe_pro_url      = https://buy.stripe.com/xxx
 *        ag_stripe_premium_url  = https://buy.stripe.com/yyy
 *        ag_stripe_business_url = https://buy.stripe.com/zzz
 *
 * If a URL is still the placeholder, the button falls back to the
 * /contact page with the pack slug in the query string so the lead
 * is still captured.
 */
$ag_stripe_placeholder    = 'STRIPE_PLACEHOLDER';
$ag_stripe_pro_default    = $ag_stripe_placeholder;
$ag_stripe_premium_default = $ag_stripe_placeholder;
$ag_stripe_business_default = $ag_stripe_placeholder;

$ag_stripe_pro      = get_option( 'ag_stripe_pro_url', $ag_stripe_pro_default );
$ag_stripe_premium  = get_option( 'ag_stripe_premium_url', $ag_stripe_premium_default );
$ag_stripe_business = get_option( 'ag_stripe_business_url', $ag_stripe_business_default );

$ag_contact_base = home_url( '/contact' );
$ag_pro_cta_url      = ( $ag_stripe_pro      !== $ag_stripe_placeholder ) ? $ag_stripe_pro      : add_query_arg( 'pack', 'pro', $ag_contact_base );
$ag_premium_cta_url  = ( $ag_stripe_premium  !== $ag_stripe_placeholder ) ? $ag_stripe_premium  : add_query_arg( 'pack', 'premium', $ag_contact_base );
$ag_business_cta_url = ( $ag_stripe_business !== $ag_stripe_placeholder ) ? $ag_stripe_business : add_query_arg( 'pack', 'business', $ag_contact_base );

$ag_pro_cta_label      = ( $ag_stripe_pro      !== $ag_stripe_placeholder ) ? 'Payer 49€ via Stripe →'  : 'Acheter — 49€ une fois';
$ag_premium_cta_label  = ( $ag_stripe_premium  !== $ag_stripe_placeholder ) ? 'Payer 99€ via Stripe →'  : 'Acheter — 99€ une fois';
$ag_business_cta_label = ( $ag_stripe_business !== $ag_stripe_placeholder ) ? 'Payer 149€ via Stripe →' : 'Acheter — 149€ une fois';
?>

<main id="ag-main-content">

    <!-- Hero -->
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag">Templates WordPress</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Templates WordPress <em>gratuits</em></span>
                <span class="ag-line">prêts à installer</span>
            </h1>
            <p class="ag-hero__sub">Téléchargez, installez, personnalisez. Des thèmes professionnels pour lancer votre site rapidement.</p>
        </div>
    </section>

    <!-- 📖 Barre de progression de lecture + avertissement -->
    <div id="ag-reading-bar" style="position:fixed;top:72px;left:0;right:0;z-index:9998;background:rgba(10,10,14,.94);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid rgba(212,180,92,.25);box-shadow:0 4px 20px rgba(0,0,0,.4);transform:translateY(-200%);transition:transform .35s ease;">
        <div style="max-width:1100px;margin:0 auto;padding:10px 20px;display:flex;align-items:center;gap:16px;">
            <span style="color:#D4B45C;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;white-space:nowrap;">📖 Lecture</span>
            <div style="flex:1;height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;position:relative;">
                <div id="ag-reading-bar-fill" style="height:100%;width:0;background:linear-gradient(90deg,#D4B45C,#e8c776);transition:width .25s linear;border-radius:4px;"></div>
            </div>
            <span id="ag-reading-bar-pct" style="color:#e8e6e0;font-size:.85rem;font-weight:700;min-width:42px;text-align:right;">0%</span>
        </div>
    </div>

    <!-- ⏳ Avertissement de lecture au-dessus du fold -->
    <section style="padding:0;background:#0a0a0e;">
        <div class="ag-container" style="padding-top:24px;padding-bottom:24px;">
            <div style="max-width:900px;margin:0 auto;padding:24px 28px;background:linear-gradient(135deg,rgba(212,180,92,.12),rgba(212,180,92,.04));border:1px solid rgba(212,180,92,.35);border-radius:14px;display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
                <div style="font-size:2.4rem;line-height:1;">📖</div>
                <div style="flex:1;min-width:260px;">
                    <strong style="display:block;color:#D4B45C;font-size:1.05rem;margin-bottom:4px;">Les templates gratuits sont tout en bas de page.</strong>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.6;margin:0;">On vous demande de lire les explications d'abord — c'est important pour choisir la bonne option. Les boutons de téléchargement se <strong style="color:#e8e6e0;">débloquent automatiquement</strong> quand vous avez parcouru le contenu. Suivez la barre de progression en haut de la page.</p>
                </div>
                <a href="#ag-download-zone" class="ag-btn-outline" style="font-size:.9rem;padding:12px 22px;">Aller en bas &darr;</a>
            </div>
        </div>
    </section>

    <!-- Pourquoi nos templates (arguments vendeurs) -->
    <section class="ag-section ag-section--onyx">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Pourquoi nos templates</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Des templates pensés <em>pour le marché français</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">La plupart des thèmes WordPress gratuits sont en anglais, avec des textes à traduire ligne par ligne. Les nôtres sont différents.</p>

            <div class="ag-services__grid" style="grid-template-columns:repeat(2,1fr);">
                <div class="ag-scard ag-anim" data-anim="card">
                    <div class="ag-scard__icon">🇫🇷</div>
                    <h3 class="ag-scard__title">100% français natif</h3>
                    <p class="ag-scard__text">Tous les textes, titres, horaires, exemples et messages sont déjà rédigés en français. Aucune traduction à faire, aucun Lorem ipsum anglais.</p>
                </div>
                <div class="ag-scard ag-anim" data-anim="card">
                    <div class="ag-scard__icon">⚡</div>
                    <h3 class="ag-scard__title">95% déjà prêt</h3>
                    <p class="ag-scard__text">Structure complète : hero, sections, footer, réservation/devis. Vous changez juste le nom, les horaires et les photos. 30 minutes contre 20 heures pour un template étranger.</p>
                </div>
                <div class="ag-scard ag-anim" data-anim="card">
                    <div class="ag-scard__icon">🎨</div>
                    <h3 class="ag-scard__title">Customizer WordPress intégré</h3>
                    <p class="ag-scard__text">Modifiez <strong style="color:#D4B45C;">couleurs, typographie, textes du hero et du footer</strong> directement depuis <code style="background:rgba(212,180,92,.1);padding:2px 5px;border-radius:3px;color:#D4B45C;">Apparence &gt; Personnaliser</code> avec prévisualisation en direct. Comme Astra, OceanWP, Kadence — sans coder une ligne.</p>
                </div>
                <div class="ag-scard ag-anim" data-anim="card">
                    <div class="ag-scard__icon">📦</div>
                    <h3 class="ag-scard__title">Zéro plugin requis</h3>
                    <p class="ag-scard__text">Pas d'Elementor, pas de Divi, pas de dépendances payantes. Installation en 2 minutes, fonctionne immédiatement. Code propre, WordPress 6.0+, PHP 7.4+.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 🚨 AVERTISSEMENT PRIORITAIRE — Offre création sur-mesure -->
    <section class="ag-section ag-section--or" style="padding:100px 0;">
        <div class="ag-container">
            <div style="max-width:1000px;margin:0 auto;padding:56px 48px;background:linear-gradient(135deg,rgba(212,180,92,.10) 0%,rgba(20,20,22,.6) 100%);border:2px solid rgba(212,180,92,.4);border-radius:24px;box-shadow:0 30px 80px rgba(0,0,0,.5);position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,transparent,#D4B45C,transparent);"></div>

                <div style="text-align:center;margin-bottom:36px;">
                    <span style="display:inline-block;padding:8px 24px;background:rgba(212,180,92,.2);border:1px solid rgba(212,180,92,.5);border-radius:100px;color:#D4B45C;font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:20px;">⚠️ Avertissement honnête</span>
                    <h2 style="font-size:clamp(1.8rem,4vw,2.8rem);margin-bottom:16px;line-height:1.2;">Avant d'acheter un template, <em>lisez ceci</em></h2>
                    <p style="font-size:1.15rem;color:#e8e6e0;max-width:780px;margin:0 auto;line-height:1.7;">Les templates (gratuits ou payants) sont un <strong style="color:#D4B45C;">point de départ</strong>, pas une solution complète. Si votre objectif est de <strong style="color:#fff;">générer de vrais clients et du vrai chiffre d'affaires</strong>, un site sur-mesure est toujours la meilleure solution. Voici pourquoi, et pourquoi on vous le dit alors que ça pourrait nous faire perdre des ventes.</p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:36px;">
                    <div style="text-align:center;padding:24px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:12px;">
                        <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;margin-bottom:4px;">+340%</div>
                        <div style="color:#b0b0bc;font-size:.92rem;">Leads générés en moyenne<br>par nos sites sur-mesure</div>
                    </div>
                    <div style="text-align:center;padding:24px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:12px;">
                        <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;margin-bottom:4px;">3 mois</div>
                        <div style="color:#b0b0bc;font-size:.92rem;">Délai moyen pour<br>rentabiliser le site</div>
                    </div>
                    <div style="text-align:center;padding:24px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:12px;">
                        <div style="font-size:2.4rem;font-weight:800;color:#D4B45C;margin-bottom:4px;">0€</div>
                        <div style="color:#b0b0bc;font-size:.92rem;">Coût du premier rendez-vous<br>stratégique (sans engagement)</div>
                    </div>
                </div>

                <div style="background:rgba(255,255,255,.02);border-left:4px solid #D4B45C;padding:24px 28px;margin-bottom:32px;border-radius:0 8px 8px 0;">
                    <p style="font-size:1.05rem;color:#e8e6e0;line-height:1.8;margin:0;">
                        <strong style="color:#D4B45C;">La vérité :</strong> même avec notre plugin Premium à 99€, un template AG Starter reste un template. Il aura l'air propre, fonctionnera bien, mais il ne vendra <em>jamais</em> aussi bien qu'un site conçu pour VOTRE marque, VOS mots-clés, VOS clients cibles. Si vous jouez pour gagner — restaurant en ville touristique, artisan qui veut dominer sa zone, coach qui veut remplir son agenda — un site sur-mesure est un investissement, pas une dépense.
                    </p>
                </div>

                <div style="text-align:center;">
                    <h3 style="font-size:1.35rem;margin-bottom:12px;color:#fff;">Votre business mérite plus qu'un template</h3>
                    <p style="color:#b0b0bc;margin-bottom:28px;max-width:640px;margin-left:auto;margin-right:auto;">Réservez un <strong style="color:#D4B45C;">appel stratégique gratuit de 30 minutes</strong>. Pas de blabla, pas de vente forcée — juste un audit honnête de votre situation et des recommandations concrètes. Si un template suffit, on vous le dira. Si vous avez besoin d'un vrai site, on vous expliquera pourquoi avec des chiffres.</p>
                    <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                        <a href="tel:+33623526074" class="ag-btn-gold">📞 Appeler Fabrizio — 06.23.52.60.74</a>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Réserver un appel stratégique →</a>
                    </div>
                    <p style="color:#888;font-size:.85rem;margin-top:20px;font-style:italic;">7j/7 · Réponse sous 24h · Aucun engagement</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 📊 Explication approfondie Gratuit / Pro / Premium / Sur-mesure -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Comprendre les 4 niveaux</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Template gratuit, Pro, Premium ou <em>sur-mesure</em> ?</h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Chaque niveau sert un besoin différent. Voici comment choisir selon votre objectif réel — et ce que les concurrents proposent au même tarif.</p>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;max-width:1100px;margin:48px auto 0;">

                <!-- Gratuit -->
                <div style="padding:32px;background:rgba(40,167,69,.04);border:1px solid rgba(40,167,69,.25);border-radius:16px;">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:16px;">
                        <h3 style="color:#28a745;font-size:1.3rem;margin:0;">🆓 Gratuit — Template seul</h3>
                        <span style="color:#28a745;font-weight:700;">0€</span>
                    </div>
                    <p style="color:#b0b0bc;font-size:.95rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Pour qui ?</strong> Quelqu'un qui veut juste être en ligne rapidement, avec un budget zéro, sans ambition commerciale immédiate.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Ce que vous obtenez :</strong> un thème WordPress minimaliste en français, 5 pages pré-remplies, un customizer pour changer couleurs/typo/textes, un plugin d'import en 1 clic. C'est fonctionnel, c'est propre, c'est en ligne en 30 minutes.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Les limites :</strong> design volontairement basique (pas d'animations, pas d'effets, pas de gradients). Pas de multi-langue. Pas de WooCommerce. Aucun support dédié.</p>
                    <p style="color:#888;font-size:.85rem;line-height:1.6;margin:0;"><strong style="color:#28a745;">Concurrents équivalents :</strong> Astra free, OceanWP free, Kadence free, GeneratePress free — mais tous en anglais avec du Lorem ipsum à remplacer.</p>
                </div>

                <!-- Pro -->
                <div style="padding:32px;background:rgba(212,180,92,.04);border:1px solid rgba(212,180,92,.25);border-radius:16px;">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:16px;">
                        <h3 style="color:#D4B45C;font-size:1.3rem;margin:0;">⚡ Pro — Plugin payant</h3>
                        <span style="color:#D4B45C;font-weight:700;">49€ une fois</span>
                    </div>
                    <p style="color:#b0b0bc;font-size:.95rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Pour qui ?</strong> Quelqu'un qui veut un rendu visuel professionnel sans payer une agence. Un artisan, un petit commerce, un coach qui débute.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Ce que le plugin ajoute au thème gratuit :</strong> toutes les finitions visuelles (animations, hover, transitions, gradients, ombres), 10 blocs Gutenberg premium, un customizer étendu (50+ réglages), les polices Google Fonts, un sticky header et un menu mobile stylisé. Votre site passe de "brouillon fonctionnel" à "site professionnel".</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Les limites :</strong> toujours un template. Le contenu et la structure restent ceux qu'on a conçus — vous ne pouvez pas réinventer la page d'accueil.</p>
                    <p style="color:#888;font-size:.85rem;line-height:1.6;margin:0;"><strong style="color:#D4B45C;">Concurrents équivalents :</strong> Astra Pro à 59€/an (abonnement), GeneratePress Premium à 59€/an. Chez nous c'est <strong>un paiement unique</strong> — sur 3 ans vous économisez 128€.</p>
                </div>

                <!-- Premium -->
                <div style="padding:32px;background:rgba(212,180,92,.08);border:2px solid rgba(212,180,92,.4);border-radius:16px;">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:16px;">
                        <h3 style="color:#D4B45C;font-size:1.3rem;margin:0;">🌍 Premium — Plugin payant</h3>
                        <span style="color:#D4B45C;font-weight:700;">99€ une fois</span>
                    </div>
                    <p style="color:#b0b0bc;font-size:.95rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Pour qui ?</strong> Quelqu'un qui veut toucher plusieurs pays (touristes, expatriés), vendre en ligne via WooCommerce, ou avoir les meilleures features disponibles. Un restaurant en ville touristique, un coach international, une boutique artisanale qui expédie en Europe.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Ce que le plugin ajoute en plus de Pro :</strong> thèmes traduits dans <strong>6 langues</strong> (FR, EN, ES, IT, DE, AR) avec switcher automatique, sections premium (témoignages, galerie, pricing), intégration WooCommerce complète, import/export des réglages, support prioritaire 12 mois, mises à jour à vie, et un appel de 30 min avec un expert pour la mise en place.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Les limites :</strong> toujours un template. Même avec tout ça, vous ne bénéficiez pas d'une stratégie de conversion spécifique à votre business.</p>
                    <p style="color:#888;font-size:.85rem;line-height:1.6;margin:0;"><strong style="color:#D4B45C;">Concurrents équivalents :</strong> OceanWP Business à 79€/an, Kadence Full Bundle à 129€/an — tous en abonnement. Chez nous 99€ <strong>une fois pour toute</strong>. Sur 3 ans vous économisez entre 138€ et 288€.</p>
                </div>

                <!-- Sur-mesure -->
                <div style="padding:32px;background:linear-gradient(135deg,rgba(212,180,92,.15),rgba(212,180,92,.05));border:2px solid #D4B45C;border-radius:16px;position:relative;">
                    <span style="position:absolute;top:-14px;left:24px;background:#D4B45C;color:#080808;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:4px 12px;border-radius:4px;">Recommandé</span>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:16px;">
                        <h3 style="color:#fff;font-size:1.3rem;margin:0;">💎 Sur-mesure — Site Alliance Groupe</h3>
                        <span style="color:#D4B45C;font-weight:700;">à partir de 1 500€</span>
                    </div>
                    <p style="color:#b0b0bc;font-size:.95rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Pour qui ?</strong> Quelqu'un qui traite son site web comme un <strong style="color:#D4B45C;">investissement business</strong>, pas une dépense. Quelqu'un qui veut que son site <strong style="color:#fff;">vende</strong>, pas juste qu'il existe.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Ce que vous obtenez :</strong> un site conçu par notre équipe (Fabrizio, Kate, Carlito, Halim, Amina) <strong>100% pour votre marque</strong>. Contenu rédigé par un copywriter professionnel, design unique, SEO ciblant vos vrais mots-clés, stratégie de conversion éprouvée, intégration IA et chatbots si pertinent, hébergement premium inclus, maintenance mensuelle.</p>
                    <p style="color:#b0b0bc;font-size:.92rem;line-height:1.7;margin-bottom:16px;"><strong style="color:#e8e6e0;">Les résultats :</strong> +340% de leads en moyenne, 3 mois pour rentabiliser, un vrai commercial digital qui travaille 24/7 pour vous. Zéro compétence technique requise de votre côté — on gère tout, du devis à la mise en ligne.</p>
                    <p style="color:#888;font-size:.85rem;line-height:1.6;margin:0 0 16px 0;"><strong style="color:#D4B45C;">Concurrents équivalents :</strong> une agence parisienne facturera entre 3 000€ et 8 000€ pour un résultat identique, et vous attendrez 4 mois au lieu de 4 semaines.</p>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-gold" style="display:inline-block;margin-top:8px;">Discuter de mon projet →</a>
                </div>

            </div>
        </div>
    </section>

    <!-- Templates gratuits (download zone — en bout de parcours) -->
    <section class="ag-section ag-section--marbre" id="ag-download-zone">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Choisissez votre métier</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Votre métier, vos <em>améliorations</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Chaque fiche contient tout : le thème gratuit à télécharger + les 3 packs payants qui viennent l'améliorer. Le plugin que vous achetez fonctionne avec les 4 thèmes, mais on a aligné les fiches par métier pour que vous vous y retrouviez vite. Également disponibles sur <a href="https://profiles.wordpress.org/adminag/" target="_blank" rel="noopener" style="color:#D4B45C;font-weight:700;">ma page WordPress.org officielle</a>.</p>

            <?php
            // 3 cartes promo décoratives (QR codes scannables au téléphone).
            // Affichées en petit au-dessus des fiches métier si les PNGs existent.
            $ag_promo_dir   = get_stylesheet_directory() . '/assets/images/promo-cards/';
            $ag_promo_url_base = get_stylesheet_directory_uri() . '/assets/images/promo-cards/';
            $ag_promo_files = array(
                'pro'      => array( 'file' => 'ag-pro-card.png',      'alt' => 'AG Starter Pro — 49€' ),
                'premium'  => array( 'file' => 'ag-premium-card.png',  'alt' => 'AG Starter Premium — 99€' ),
                'business' => array( 'file' => 'ag-business-card.png', 'alt' => 'AG Starter Business — 149€' ),
            );
            $ag_promo_has_any = false;
            foreach ( $ag_promo_files as $pc ) {
                if ( file_exists( $ag_promo_dir . $pc['file'] ) ) {
                    $ag_promo_has_any = true;
                    break;
                }
            }
            if ( $ag_promo_has_any ) : ?>
            <div style="max-width:900px;margin:32px auto 8px;text-align:center;">
                <p style="color:#888;font-size:.85rem;margin:0 0 16px;font-style:italic;">📱 Cartes à scanner au téléphone — le QR code vous amène direct sur la page de contact.</p>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;max-width:720px;margin:0 auto;">
                    <?php foreach ( $ag_promo_files as $pc ) :
                        if ( ! file_exists( $ag_promo_dir . $pc['file'] ) ) { continue; }
                    ?>
                    <img src="<?php echo esc_url( $ag_promo_url_base . $pc['file'] ); ?>"
                         alt="<?php echo esc_attr( $pc['alt'] ); ?>"
                         loading="lazy"
                         style="width:100%;height:auto;display:block;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.4);">
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            $ag_metiers = array(
                'restaurant' => array(
                    'icon'     => '🍽️',
                    'name'     => 'Restaurant',
                    'palette'  => 'Or &amp; noir',
                    'audience' => 'Bistrot, bar, café, restaurant gastronomique.',
                    'free_zip' => 'ag-starter-restaurant.zip',
                    'free_trig' => 'restaurant',
                    'free_benefit' => 'Hero, carte, réservation, privatisation, histoire, horaires — tout pré-rempli en français.',
                    'pro'      => 'Animations douces sur les sections carte + hero plein écran avec vidéo de fond',
                    'premium'  => 'Menu réservation multilingue (touristes) + module de commande en ligne WooCommerce',
                    'business' => 'Installation assistée, intégration Deliveroo / TheFork, maintenance 1 an',
                ),
                'artisan' => array(
                    'icon'     => '🔨',
                    'name'     => 'Artisan',
                    'palette'  => 'Bronze &amp; noir',
                    'audience' => 'Plombier, électricien, menuisier, maçon, chauffagiste, BTP.',
                    'free_zip' => 'ag-starter-artisan.zip',
                    'free_trig' => 'artisan',
                    'free_benefit' => 'Prestations, zones d\'intervention, devis, réalisations — ton professionnel.',
                    'pro'      => 'Galerie avant/après des chantiers + formulaire de devis avancé avec calculateur',
                    'premium'  => 'Réservation d\'interventions en ligne (WooCommerce) + planning Google Calendar',
                    'business' => 'Installation assistée, maintenance 1 an, audit SEO local pour dominer votre zone',
                ),
                'coach' => array(
                    'icon'     => '💼',
                    'name'     => 'Coach / Consultant',
                    'palette'  => 'Bleu teal &amp; marine',
                    'audience' => 'Coach, consultant, formateur, thérapeute.',
                    'free_zip' => 'ag-starter-coach.zip',
                    'free_trig' => 'coach',
                    'free_benefit' => 'Services, séances, témoignages, rendez-vous — ton bienveillant et structurant.',
                    'pro'      => 'Témoignages animés + blocs parcours transformation + booking Calendly intégré',
                    'premium'  => 'Vente de formations en ligne (WooCommerce) + multi-langue pour coaching international',
                    'business' => 'Installation assistée, intégration CRM (HubSpot, Pipedrive), appel stratégique Fabrizio',
                ),
                'avocat' => array(
                    'icon'     => '⚖️',
                    'name'     => 'Avocat / Juridique',
                    'palette'  => 'Navy &amp; champagne <strong style="color:#28a745;">· v1.1</strong>',
                    'audience' => 'Cabinet d\'avocats, juriste, notaire, conseil juridique.',
                    'free_zip' => 'ag-starter-avocat.zip',
                    'free_trig' => 'avocat',
                    'free_benefit' => 'CPT Domaines d\'expertise, formulaire RDV RGPD (wp_mail), 4 sections cabinet + Maître + honoraires transparent + Google Maps. 100% configurable depuis le Customizer.',
                    'pro'      => 'Animations + sticky header téléphone toujours visible + 10 blocs Gutenberg juridiques (FAQ, timeline, témoignages anonymisés)',
                    'premium'  => 'Multi-langue 6 langues (clients internationaux) + espace client sécurisé pour partage de documents (WooCommerce + RGPD) + calendrier RDV intégré',
                    'business' => 'Installation visio 1h, audit SEO juridique (mots-clés "avocat divorce Paris" etc.), white-label complet, intégration CRM (HubSpot/Pipedrive), maintenance 1 an, appel stratégique Fabrizio',
                ),
            );

            $ag_tiers = array(
                'pro' => array(
                    'icon'  => '⚡',
                    'label' => 'Pack Pro',
                    'price' => '49€',
                    'cta'   => $ag_pro_cta_url,
                    'stripe' => $ag_stripe_pro,
                    'btn_label' => $ag_pro_cta_label,
                    'btn_class' => 'ag-btn-outline',
                ),
                'premium' => array(
                    'icon'  => '🌍',
                    'label' => 'Pack Premium',
                    'price' => '99€',
                    'cta'   => $ag_premium_cta_url,
                    'stripe' => $ag_stripe_premium,
                    'btn_label' => $ag_premium_cta_label,
                    'btn_class' => 'ag-btn-gold',
                ),
                'business' => array(
                    'icon'  => '💼',
                    'label' => 'Pack Business',
                    'price' => '149€',
                    'cta'   => $ag_business_cta_url,
                    'stripe' => $ag_stripe_business,
                    'btn_label' => $ag_business_cta_label,
                    'btn_class' => 'ag-btn-outline',
                ),
            );
            ?>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:28px;max-width:1200px;margin:40px auto 0;">
                <?php foreach ( $ag_metiers as $slug => $m ) : ?>
                <div class="ag-anim" data-anim="card" style="background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.2);border-radius:18px;overflow:hidden;display:flex;flex-direction:column;">

                    <!-- Header métier -->
                    <div style="padding:24px 24px 20px;border-bottom:1px solid rgba(212,180,92,.15);background:rgba(212,180,92,.03);">
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                            <span style="font-size:2rem;"><?php echo $m['icon']; // phpcs:ignore ?></span>
                            <div>
                                <h3 style="margin:0;font-size:1.3rem;color:#fff;"><?php echo esc_html( $m['name'] ); ?></h3>
                                <span style="color:#b0b0bc;font-size:.82rem;"><?php echo $m['palette']; // phpcs:ignore ?></span>
                            </div>
                        </div>
                        <p style="color:#b0b0bc;font-size:.88rem;line-height:1.55;margin:0;"><?php echo esc_html( $m['audience'] ); ?></p>
                    </div>

                    <!-- Tier Gratuit -->
                    <div style="padding:20px 24px;border-bottom:1px dashed rgba(255,255,255,.07);">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <strong style="color:#28a745;font-size:.95rem;">🆓 Thème gratuit</strong>
                            <span style="color:#28a745;font-weight:700;font-size:.9rem;">0€</span>
                        </div>
                        <p style="color:#b0b0bc;font-size:.85rem;line-height:1.55;margin:0 0 12px;"><?php echo esc_html( $m['free_benefit'] ); ?></p>
                        <button type="button" class="ag-btn-gold ag-dl-trigger" style="width:100%;justify-content:center;font-size:.9rem;padding:11px 18px;background:#28a745;box-shadow:none;" data-template="<?php echo esc_attr( $m['free_trig'] ); ?>" data-file="<?php echo esc_url( $dl_base . $m['free_zip'] ); ?>">Télécharger gratuitement →</button>
                    </div>

                    <!-- Tiers payants -->
                    <?php foreach ( $ag_tiers as $tier_key => $t ) : ?>
                    <div style="padding:16px 24px;border-bottom:1px dashed rgba(255,255,255,.07);">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                            <strong style="color:#D4B45C;font-size:.92rem;"><?php echo $t['icon']; // phpcs:ignore ?> <?php echo esc_html( $t['label'] ); ?></strong>
                            <span style="color:#D4B45C;font-weight:700;font-size:.9rem;"><?php echo esc_html( $t['price'] ); ?></span>
                        </div>
                        <p style="color:#b0b0bc;font-size:.82rem;line-height:1.5;margin:0 0 10px;">+ <?php echo esc_html( $m[ $tier_key ] ); ?></p>
                        <a href="<?php echo esc_url( $t['cta'] ); ?>" class="<?php echo esc_attr( $t['btn_class'] ); ?>" style="width:100%;display:flex;justify-content:center;font-size:.85rem;padding:10px 16px;"<?php echo ( $t['stripe'] !== $ag_stripe_placeholder ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $t['btn_label'] ); ?></a>
                    </div>
                    <?php endforeach; ?>

                    <!-- Footer note -->
                    <div style="padding:14px 24px;background:rgba(255,255,255,.02);color:#888;font-size:.78rem;text-align:center;margin-top:auto;">
                        Le plugin détecte automatiquement votre thème et adapte ses features
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Plugin compagnon gratuit -->
            <div style="max-width:900px;margin:56px auto 0;padding:40px;background:linear-gradient(135deg,rgba(40,167,69,.08),rgba(40,167,69,.02));border:1px solid rgba(40,167,69,.3);border-radius:16px;">
                <div style="text-align:center;">
                    <span class="ag-tag" style="background:rgba(40,167,69,.15);color:#28a745;border-color:rgba(40,167,69,.3);">Bonus gratuit — Plugin compagnon</span>
                    <h3 style="font-size:1.4rem;margin:12px 0 8px;">Installation en 1 clic avec <em>AG Starter Companion</em></h3>
                    <p style="color:#b0b0bc;max-width:680px;margin:0 auto 24px;font-size:.98rem;line-height:1.7;">Installez aussi notre <strong style="color:#28a745;">plugin compagnon gratuit</strong> et vous obtenez un bouton <strong style="color:#D4B45C;">"Importer le contenu démo"</strong> dans l'admin WordPress. En un clic il crée pour vous les 5 pages (Accueil, Carte/Prestations/Accompagnements, À propos, Contact…), le menu principal, la page d'accueil statique et active les permaliens. <strong style="color:#e8e6e0;">Zéro code, zéro manipulation manuelle.</strong></p>
                    <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                        <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="companion" data-file="<?php echo esc_url($dl_base . 'ag-starter-companion.zip'); ?>">⚡ Télécharger le plugin gratuit →</button>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Besoin d'aide ? On en parle</a>
                    </div>
                    <p style="color:#888;font-size:.85rem;margin-top:20px;">Le plugin détecte automatiquement quel thème AG Starter est actif et adapte le contenu. Fonctionne avec Restaurant, Artisan et Coach. <strong style="color:#28a745;">100% gratuit, zéro limite.</strong></p>
                </div>
            </div>


            <!-- Instructions d'installation -->
            <div style="max-width:900px;margin:56px auto 0;padding:40px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.18);border-radius:16px;">
                <h3 style="text-align:center;font-size:1.25rem;margin-bottom:8px;">Installation en <em>4 étapes</em> — 2 minutes chrono</h3>
                <p style="text-align:center;color:#b0b0bc;margin-bottom:28px;font-size:.95rem;">Aucune compétence technique nécessaire, juste un accès admin à votre WordPress.</p>
                <ol style="list-style:none;counter-reset:step;padding:0;">
                    <li style="counter-increment:step;position:relative;padding:16px 0 16px 60px;border-bottom:1px solid rgba(255,255,255,.06);">
                        <span style="position:absolute;left:0;top:14px;width:40px;height:40px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;">1</span>
                        <strong style="color:#fff;display:block;margin-bottom:4px;">Téléchargez le ZIP du template (et celui du plugin compagnon)</strong>
                        <span style="color:#b0b0bc;font-size:.92rem;">Cliquez sur "Télécharger gratuitement" ci-dessus. Un formulaire s'ouvre : entrez votre nom et email, le téléchargement démarre automatiquement. Téléchargez aussi <strong>AG Starter Companion</strong> pour l'import automatique.</span>
                    </li>
                    <li style="counter-increment:step;position:relative;padding:16px 0 16px 60px;border-bottom:1px solid rgba(255,255,255,.06);">
                        <span style="position:absolute;left:0;top:14px;width:40px;height:40px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;">2</span>
                        <strong style="color:#fff;display:block;margin-bottom:4px;">Installez le thème, puis le plugin</strong>
                        <span style="color:#b0b0bc;font-size:.92rem;">Dans votre admin WordPress : <code style="background:rgba(212,180,92,.1);padding:2px 6px;border-radius:3px;color:#D4B45C;">Apparence &gt; Thèmes &gt; Ajouter &gt; Téléverser un thème</code>, puis sélectionnez le ZIP du template et activez-le. Ensuite <code style="background:rgba(212,180,92,.1);padding:2px 6px;border-radius:3px;color:#D4B45C;">Extensions &gt; Ajouter &gt; Téléverser une extension</code> pour le plugin compagnon.</span>
                    </li>
                    <li style="counter-increment:step;position:relative;padding:16px 0 16px 60px;border-bottom:1px solid rgba(255,255,255,.06);">
                        <span style="position:absolute;left:0;top:14px;width:40px;height:40px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;">3</span>
                        <strong style="color:#fff;display:block;margin-bottom:4px;">Cliquez sur "Lancer la configuration"</strong>
                        <span style="color:#b0b0bc;font-size:.92rem;">Une notice apparaît en haut de l'admin. Cliquez dessus (ou allez dans <code style="background:rgba(212,180,92,.1);padding:2px 6px;border-radius:3px;color:#D4B45C;">Apparence &gt; Configuration AG</code>) et cliquez sur <strong>"Importer le contenu démo"</strong>. Les 5 pages, le menu et la page d'accueil sont créés automatiquement.</span>
                    </li>
                    <li style="counter-increment:step;position:relative;padding:16px 0 16px 60px;">
                        <span style="position:absolute;left:0;top:14px;width:40px;height:40px;border-radius:50%;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.3);color:#D4B45C;display:flex;align-items:center;justify-content:center;font-weight:700;">4</span>
                        <strong style="color:#fff;display:block;margin-bottom:4px;">Personnalisez les éléments entre crochets</strong>
                        <span style="color:#b0b0bc;font-size:.92rem;">Remplacez "[Votre Restaurant]", "[Votre entreprise]", etc. par vos vraies informations. Si vous bloquez, <a href="<?php echo esc_url(home_url('/contact')); ?>" style="color:#D4B45C;font-weight:700;">contactez-nous</a> — on vous aide gratuitement.</span>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- FAQ Templates -->
    <section class="ag-section ag-section--cendre">
        <div class="ag-container">
            <h2 class="ag-section__title ag-anim" data-anim="title" style="text-align:center;">Questions sur les <em>templates</em></h2>
            <div class="ag-faq__list">
                <?php
                $tpl_faqs = [
                    ['q' => 'Comment installer un template gratuit ?', 'a' => 'Téléchargez le fichier ZIP, puis dans WordPress : Apparence > Thèmes > Ajouter > Téléverser un thème. Uploadez le ZIP et activez le thème. Le guide complet en 4 étapes est disponible plus haut sur cette page.'],
                    ['q' => 'Les templates sont-ils vraiment en français ?', 'a' => '100% français natif. Tous les textes (hero, cartes, horaires, footer, exemples) sont déjà rédigés en français — pas de Lorem ipsum, pas de strings anglaises à traduire. Vous remplacez juste les éléments entre crochets comme "[Votre Restaurant]" ou "[Votre entreprise]".'],
                    ['q' => 'Combien de temps pour personnaliser un template ?', 'a' => 'Environ 30 minutes contre 20 heures pour un template étranger. Comme tout est déjà en français et en place, vous changez juste le nom, l\'adresse, les horaires et vos photos. C\'est le gros avantage par rapport aux templates génériques anglais.'],
                    ['q' => 'Les templates sont-ils sur le répertoire officiel WordPress.org ?', 'a' => 'En cours de soumission. Nos trois templates (Restaurant, Artisan, Coach) sont conformes aux standards WordPress.org (GPL v2+, translation-ready, sans plugin requis, escaping strict, Theme Check compatible). Une fois validés par l\'équipe Theme Review, ils seront installables directement depuis Apparence > Thèmes > Ajouter dans votre WordPress.'],
                    ['q' => 'Le template est vraiment gratuit ?', 'a' => 'Oui, 100% gratuit sous licence GPL v2 ou ultérieure. Pas de piège, pas d\'abonnement caché. Vous pouvez l\'utiliser, le modifier et même le redistribuer librement.'],
                    ['q' => 'Ai-je besoin de plugins payants ?', 'a' => 'Non. Contrairement à beaucoup de thèmes gratuits qui exigent Elementor Pro ou d\'autres plugins payants pour débloquer les fonctionnalités, nos templates sont 100% autonomes. Installation et fonctionnement immédiat.'],
                    ['q' => 'Quelle est la différence avec un site professionnel ?', 'a' => 'Un template est un point de départ générique, même s\'il est de qualité. Un site professionnel est conçu sur-mesure pour VOTRE marque, avec du contenu rédigé par des experts, un SEO ciblé et une stratégie de conversion. Nos clients génèrent +340% de leads en moyenne avec un site sur-mesure.'],
                    ['q' => 'J\'ai besoin d\'aide pour personnaliser, vous pouvez m\'aider ?', 'a' => 'Bien sûr ! Appelez-nous au 06.23.52.60.74. On peut personnaliser votre template ou créer un site entièrement sur-mesure qui génère des résultats concrets.'],
                ];
                foreach ($tpl_faqs as $faq) :
                ?>
                <div class="ag-faq-item ag-anim" data-anim="faq-item">
                    <button class="ag-faq-q" type="button">
                        <span><?php echo esc_html($faq['q']); ?></span>
                        <span class="ag-faq-icon">+</span>
                    </button>
                    <div class="ag-faq-a">
                        <p><?php echo esc_html($faq['a']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/cta'); ?>

</main>

<!-- Modal capture email -->
<div class="ag-dl-modal" id="ag-dl-modal">
    <div class="ag-dl-modal__overlay" id="ag-dl-modal-close"></div>
    <div class="ag-dl-modal__box">
        <button type="button" class="ag-dl-modal__close" id="ag-dl-modal-x">✕</button>
        <div class="ag-dl-modal__icon">🎁</div>
        <h3 class="ag-dl-modal__title">Votre template <em>gratuit</em> vous attend</h3>
        <p class="ag-dl-modal__desc">Entrez vos coordonnées pour recevoir le lien de téléchargement. Pas de spam, promis.</p>
        <form class="ag-dl-modal__form" id="ag-dl-form">
            <input type="hidden" id="ag-dl-file" value="">
            <input type="hidden" id="ag-dl-template" value="">
            <div class="ag-form__group">
                <input type="text" id="ag-dl-name" placeholder="Votre nom" required>
            </div>
            <div class="ag-form__group">
                <input type="email" id="ag-dl-email" placeholder="Votre email" required>
            </div>
            <div class="ag-form__group">
                <input type="tel" id="ag-dl-phone" placeholder="Votre téléphone (optionnel)">
            </div>
            <button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Télécharger maintenant →</button>
        </form>
        <p class="ag-dl-modal__trust">🔒 Vos données sont protégées. Pas de spam.</p>
    </div>
</div>

<script>
(function(){
    var modal = document.getElementById('ag-dl-modal');
    var form = document.getElementById('ag-dl-form');
    var fileInput = document.getElementById('ag-dl-file');
    var tplInput = document.getElementById('ag-dl-template');

    // 📖 Download lock : the buttons unlock the moment the download zone
    // enters the viewport. Progress bar in the top of the viewport fills
    // proportionally to how far we've scrolled vs the download zone.
    var triggers = document.querySelectorAll('.ag-dl-trigger');
    var originalLabels = new WeakMap();
    var isUnlocked = false;

    triggers.forEach(function(btn){
        originalLabels.set(btn, btn.innerHTML);
        btn.setAttribute('data-locked', '1');
        btn.style.opacity = '0.55';
        btn.style.cursor = 'not-allowed';
        btn.setAttribute('aria-disabled', 'true');
        btn.innerHTML = '🔒 Continuez votre lecture...';
    });

    var readingBar = document.getElementById('ag-reading-bar');
    var readingBarFill = document.getElementById('ag-reading-bar-fill');
    var readingBarPct = document.getElementById('ag-reading-bar-pct');
    var dlZone = document.getElementById('ag-download-zone');
    var maxProgress = 0;

    var unlockButtons = function(){
        if (isUnlocked) { return; }
        isUnlocked = true;
        triggers.forEach(function(btn){
            btn.removeAttribute('data-locked');
            btn.setAttribute('aria-disabled', 'false');
            btn.style.opacity = '';
            btn.style.cursor = '';
            btn.innerHTML = originalLabels.get(btn);
            btn.style.transition = 'box-shadow .3s';
            btn.style.boxShadow = '0 0 0 3px rgba(212,180,92,.55)';
            setTimeout(function(){ btn.style.boxShadow = ''; }, 900);
        });
        if (readingBarFill && readingBarPct) {
            readingBarFill.style.width = '100%';
            readingBarFill.style.background = 'linear-gradient(90deg,#28a745,#43b85c)';
            readingBarPct.innerHTML = '✓';
            readingBarPct.style.color = '#28a745';
        }
        if (readingBar) {
            readingBar.style.borderBottomColor = 'rgba(40,167,69,.6)';
        }
    };

    // Show the bar after the hero + update progress fill
    var updateProgressBar = function(){
        if (!readingBar || !dlZone) { return; }
        var scrolled = window.scrollY || window.pageYOffset;
        var viewport = window.innerHeight;

        // Show/hide the bar
        if (scrolled > 260 && !isUnlocked) {
            readingBar.style.transform = 'translateY(0)';
        } else if (scrolled <= 100) {
            readingBar.style.transform = 'translateY(-200%)';
        } else if (isUnlocked && scrolled > 260) {
            // keep visible briefly after unlock, then hide
            readingBar.style.transform = 'translateY(0)';
        }

        if (isUnlocked) { return; }

        // Compute progress : from the top of the page (0) to the top of the
        // download zone (= 100 percent). Uses getBoundingClientRect for
        // accurate measurement independent of layout shifts.
        var dlRect = dlZone.getBoundingClientRect();
        var dlTop = dlRect.top + scrolled; // absolute Y of download zone
        var dlTarget = Math.max(1, dlTop - viewport * 0.2); // trigger a bit before the zone
        var progress = Math.min(1, scrolled / dlTarget);
        if (progress > maxProgress) { maxProgress = progress; }
        var displayPct = Math.round(maxProgress * 100);
        if (readingBarFill && readingBarPct) {
            readingBarFill.style.width = displayPct + '%';
            readingBarPct.textContent = displayPct + '%';
        }
    };

    // IntersectionObserver : unlocks the buttons the instant the download
    // zone enters the viewport, regardless of scroll percentage.
    if ('IntersectionObserver' in window && dlZone) {
        var observer = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if (entry.isIntersecting) {
                    unlockButtons();
                    observer.disconnect();
                }
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.01 });
        observer.observe(dlZone);
    } else if (dlZone) {
        // Fallback for very old browsers : unlock when we scroll past the
        // section's top offset.
        window.addEventListener('scroll', function(){
            if (isUnlocked) { return; }
            var rect = dlZone.getBoundingClientRect();
            if (rect.top < window.innerHeight) { unlockButtons(); }
        }, { passive: true });
    }

    window.addEventListener('scroll', updateProgressBar, { passive: true });
    window.addEventListener('resize', updateProgressBar);
    updateProgressBar();

    // Open modal on click (only if button is unlocked)
    document.querySelectorAll('.ag-dl-trigger').forEach(function(btn){
        btn.addEventListener('click', function(e){
            if (btn.getAttribute('data-locked') === '1') {
                e.preventDefault();
                e.stopPropagation();
                // Flash a small visual feedback
                btn.style.boxShadow = '0 0 0 3px rgba(212,180,92,.4)';
                setTimeout(function(){ btn.style.boxShadow = ''; }, 400);
                return;
            }
            fileInput.value = btn.getAttribute('data-file');
            tplInput.value = btn.getAttribute('data-template');
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    // Close modal
    function closeModal(){
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.getElementById('ag-dl-modal-close').addEventListener('click', closeModal);
    document.getElementById('ag-dl-modal-x').addEventListener('click', closeModal);

    // Submit form
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var name = document.getElementById('ag-dl-name').value;
        var email = document.getElementById('ag-dl-email').value;
        var phone = document.getElementById('ag-dl-phone').value;
        var template = tplInput.value;
        var file = fileInput.value;

        // Save lead via AJAX to WordPress
        var data = new FormData();
        data.append('action', 'ag_save_lead');
        data.append('name', name);
        data.append('email', email);
        data.append('phone', phone);
        data.append('template', template);
        data.append('ag_lead_nonce', '<?php echo wp_create_nonce("ag_lead_nonce"); ?>');

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        }).then(function(){
            // Trigger download
            var link = document.createElement('a');
            link.href = file;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Show thank you
            form.innerHTML = '<div style="text-align:center;padding:20px 0;"><div style="font-size:3rem;margin-bottom:12px;">✅</div><h3 style="margin-bottom:8px;">Merci ' + name + ' !</h3><p style="color:#b0b0bc;">Le téléchargement a démarré. Besoin d\'aide pour l\'installation ?</p><a href="tel:+33623526074" class="ag-btn-gold" style="margin-top:16px;">Appeler Fabrizio — 06.23.52.60.74</a></div>';
        });
    });
})();
</script>

<?php get_footer(); ?>
