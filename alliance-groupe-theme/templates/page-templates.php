<?php
/**
 * Template Name: Templates WordPress
 */
get_header();
$dl_base = get_stylesheet_directory_uri() . '/assets/downloads/';
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

    <!-- Avertissement honnête -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container ag-container--narrow" style="text-align:center;">
            <div style="background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.15);border-radius:16px;padding:36px;">
                <h2 style="font-size:1.3rem;margin-bottom:12px;">Un template, c'est un <em>point de départ</em></h2>
                <p style="color:#b0b0bc;line-height:1.7;">Nos templates gratuits sont fonctionnels mais basiques. Le contenu est placeholder (textes et images à remplacer). La personnalisation demande du temps et des connaissances techniques en WordPress. Si vous voulez un site professionnel qui génère des résultats — <a href="<?php echo esc_url(home_url('/contact')); ?>" style="color:#D4B45C;font-weight:700;">parlons de votre projet</a>.</p>
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

            <div style="max-width:900px;margin:48px auto 0;padding:32px;background:rgba(255,255,255,.02);border:1px solid rgba(212,180,92,.15);border-radius:16px;">
                <h3 style="text-align:center;font-size:1.1rem;margin-bottom:20px;color:#D4B45C;text-transform:uppercase;letter-spacing:1px;">Comparaison avec les templates étrangers</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div>
                        <h4 style="color:#b0b0bc;margin-bottom:10px;font-size:.95rem;">❌ Templates classiques (EN)</h4>
                        <ul style="color:#b0b0bc;font-size:.9rem;line-height:1.9;list-style:none;">
                            <li>• Textes en anglais ou Lorem ipsum</li>
                            <li>• Horaires au format américain</li>
                            <li>• Exemples culturellement décalés</li>
                            <li>• Plugins payants obligatoires</li>
                            <li>• 20+ heures de personnalisation</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color:#D4B45C;margin-bottom:10px;font-size:.95rem;">✓ AG Starter (FR)</h4>
                        <ul style="color:#e8e6e0;font-size:.9rem;line-height:1.9;list-style:none;">
                            <li>• Textes rédigés en français natif</li>
                            <li>• Horaires et formats FR/EU</li>
                            <li>• Contenu adapté au marché français</li>
                            <li>• Aucun plugin requis</li>
                            <li>• 30 minutes pour personnaliser</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packs Premium Stripe -->
    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Premium</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Passez au <em>niveau supérieur</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Les templates gratuits sont volontairement basiques — design minimaliste, contenu pré-rempli, juste ce qu'il faut pour démarrer. Quand vous êtes prêt à aller plus loin, deux plugins payants viennent compléter le thème : <strong style="color:#D4B45C;">Pro</strong> pour un vrai design travaillé, <strong style="color:#D4B45C;">Premium</strong> pour tout ça + le multi-langue.</p>

            <!-- Comparaison en 3 colonnes Gratuit / Pro / Premium -->
            <div style="max-width:1100px;margin:40px auto 56px;overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;background:rgba(255,255,255,.02);border:1px solid rgba(212,180,92,.15);border-radius:12px;overflow:hidden;">
                    <thead>
                        <tr style="background:rgba(212,180,92,.08);">
                            <th style="text-align:left;padding:18px 22px;color:#b0b0bc;font-weight:600;font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;width:40%;">Fonctionnalité</th>
                            <th style="padding:18px 16px;color:#e8e6e0;font-weight:700;text-align:center;">Gratuit<br><small style="color:#888;font-weight:400;">Thème seul</small></th>
                            <th style="padding:18px 16px;color:#D4B45C;font-weight:700;text-align:center;border-left:1px solid rgba(212,180,92,.15);border-right:1px solid rgba(212,180,92,.15);">Pro<br><small style="color:#888;font-weight:400;">49€</small></th>
                            <th style="padding:18px 16px;color:#D4B45C;font-weight:700;text-align:center;background:rgba(212,180,92,.06);">Premium<br><small style="color:#888;font-weight:400;">99€</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $matrix = [
                            ['Page d\'accueil pré-remplie en français', '✓', '✓', '✓'],
                            ['Plugin compagnon (import 1 clic)', '✓', '✓', '✓'],
                            ['Customizer WordPress (couleurs, typo, textes)', '✓', '✓', '✓'],
                            ['Blog, commentaires, recherche', '✓', '✓', '✓'],
                            ['100% responsive', '✓', '✓', '✓'],
                            ['Design travaillé (hover, animations, transitions)', '—', '✓', '✓'],
                            ['Gradients, ombres, effets premium', '—', '✓', '✓'],
                            ['10 blocs Gutenberg personnalisés', '—', '✓', '✓'],
                            ['Customizer étendu (50+ réglages, espacements, layouts)', '—', '✓', '✓'],
                            ['Sticky header + menu mobile stylisé', '—', '✓', '✓'],
                            ['Polices Google Fonts premium', '—', '✓', '✓'],
                            ['Support email 60 jours', '—', '✓', '✓'],
                            ['Multi-langue (FR, EN, ES, IT, DE, AR)', '—', '—', '✓'],
                            ['Sections premium (témoignages, galerie, pricing)', '—', '—', '✓'],
                            ['Intégration WooCommerce complète', '—', '—', '✓'],
                            ['Import / export des réglages', '—', '—', '✓'],
                            ['Support prioritaire 12 mois', '—', '—', '✓'],
                            ['Mises à jour à vie', '—', '—', '✓'],
                            ['Appel de 30 min avec un expert', '—', '—', '✓'],
                        ];
                        foreach ($matrix as $row) : ?>
                        <tr style="border-top:1px solid rgba(255,255,255,.04);">
                            <td style="padding:14px 22px;color:#e8e6e0;font-size:.92rem;"><?php echo esc_html($row[0]); ?></td>
                            <td style="padding:14px 16px;text-align:center;color:<?php echo $row[1] === '✓' ? '#28a745' : '#555'; ?>;font-weight:700;font-size:1.1rem;"><?php echo esc_html($row[1]); ?></td>
                            <td style="padding:14px 16px;text-align:center;color:<?php echo $row[2] === '✓' ? '#D4B45C' : '#555'; ?>;font-weight:700;font-size:1.1rem;border-left:1px solid rgba(212,180,92,.1);border-right:1px solid rgba(212,180,92,.1);"><?php echo esc_html($row[2]); ?></td>
                            <td style="padding:14px 16px;text-align:center;color:<?php echo $row[3] === '✓' ? '#D4B45C' : '#555'; ?>;font-weight:700;font-size:1.1rem;background:rgba(212,180,92,.03);"><?php echo esc_html($row[3]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ag-pricing__grid" style="grid-template-columns:1fr 1fr;max-width:800px;margin:0 auto;">

                <!-- Pack Pro 49€ -->
                <div class="ag-price-card ag-anim" data-anim="card">
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">49<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Pro</h3>
                        <p class="ag-price-card__sub">Plugin AG Starter Pro — transforme le thème basique en thème professionnel</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li><strong>Design travaillé</strong> : animations, transitions, hover, gradients</li>
                        <li><strong>10 blocs Gutenberg</strong> premium (hero, CTA, stats, timeline…)</li>
                        <li><strong>Customizer avancé</strong> : couleurs, typo, espacements en direct</li>
                        <li><strong>Polices Google Fonts</strong> premium (Playfair, Manrope…)</li>
                        <li><strong>Sticky header</strong> + menu mobile stylisé</li>
                        <li><strong>Effets premium</strong> : ombres, transformations, scroll smooth</li>
                        <li><strong>Support email 60 jours</strong></li>
                        <li><strong>Documentation vidéo</strong> complète</li>
                    </ul>
                    <a href="<?php echo esc_url(home_url('/contact?pack=pro')); ?>" class="ag-btn-outline">Acheter — 49€ une fois</a>
                    <p style="font-size:.8rem;color:#888;text-align:center;margin-top:12px;">Prix unique — pas d'abonnement.<br>Compatible avec les 3 thèmes AG Starter.</p>
                </div>

                <!-- Pack Premium 99€ -->
                <div class="ag-price-card ag-price-card--pop ag-anim" data-anim="card">
                    <span class="ag-price-card__badge">⭐ Premium</span>
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">99<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Premium</h3>
                        <p class="ag-price-card__sub">Plugin AG Starter Premium — tout Pro + multi-langue + features avancées</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li><strong>Tout ce qui est dans Pro</strong> (design, blocs, customizer…)</li>
                        <li><strong>🌍 Multi-langue</strong> : thèmes traduits dans <strong>6 langues</strong> (FR, EN, ES, IT, DE, AR)</li>
                        <li><strong>Switcher de langue</strong> automatique dans le menu</li>
                        <li><strong>Sections premium</strong> : témoignages, galerie, pricing table</li>
                        <li><strong>Intégration WooCommerce</strong> complète (e-commerce ready)</li>
                        <li><strong>Import / export</strong> des réglages</li>
                        <li><strong>Support prioritaire 12 mois</strong> (réponse sous 24h)</li>
                        <li><strong>Mises à jour à vie</strong></li>
                        <li><strong>Appel de 30 min</strong> avec un expert pour la mise en place</li>
                    </ul>
                    <a href="<?php echo esc_url(home_url('/contact?pack=premium')); ?>" class="ag-btn-gold">Acheter — 99€ une fois</a>
                    <p style="font-size:.8rem;color:#888;text-align:center;margin-top:12px;">Prix unique — pas d'abonnement.<br>Compatible avec les 3 thèmes AG Starter.</p>
                </div>

            </div>

            <!-- Positionnement vs concurrents -->
            <div style="max-width:900px;margin:56px auto 0;padding:32px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:16px;">
                <h3 style="text-align:center;font-size:1.1rem;margin-bottom:8px;color:#D4B45C;text-transform:uppercase;letter-spacing:1px;">Positionnement vs concurrents</h3>
                <p style="text-align:center;color:#b0b0bc;margin-bottom:24px;font-size:.95rem;">Des prix accessibles, pensés pour le marché français.</p>
                <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;font-size:.92rem;">
                    <div style="color:#888;padding:10px 14px;">Concurrent / Pack équivalent</div>
                    <div style="color:#888;padding:10px 14px;text-align:center;">Prix /an</div>
                    <div style="color:#888;padding:10px 14px;text-align:center;">AG Starter</div>

                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);color:#e8e6e0;">Astra Pro (base)</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#b0b0bc;">59€/an</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#D4B45C;font-weight:700;">49€ une fois</div>

                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);color:#e8e6e0;">OceanWP Business</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#b0b0bc;">79€/an</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#D4B45C;font-weight:700;">99€ une fois</div>

                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);color:#e8e6e0;">Kadence Full Bundle</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#b0b0bc;">129€/an</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#D4B45C;font-weight:700;">99€ une fois</div>

                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);color:#e8e6e0;">GeneratePress Premium</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#b0b0bc;">59€/an</div>
                    <div style="padding:12px 14px;border-top:1px solid rgba(255,255,255,.06);text-align:center;color:#D4B45C;font-weight:700;">49€ une fois</div>
                </div>
                <p style="text-align:center;color:#888;font-size:.85rem;margin-top:20px;font-style:italic;">Paiement unique vs abonnement annuel. Nos thèmes ont moins de features que les concurrents matures, d'où un tarif volontairement bas. Vous payez pour ce dont vous avez vraiment besoin, pas pour 200 features que vous n'utiliserez jamais.</p>
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

            <div style="max-width:900px;margin:56px auto 0;text-align:center;padding:28px;background:rgba(255,255,255,.02);border:1px dashed rgba(212,180,92,.3);border-radius:12px;">
                <p style="color:#e8e6e0;font-size:1.05rem;margin-bottom:8px;"><strong>En résumé :</strong></p>
                <p style="color:#b0b0bc;font-size:.98rem;line-height:1.8;margin:0;">Si votre budget est à zéro → <strong style="color:#28a745;">thème gratuit</strong>. Si vous voulez un rendu pro sans agence → <strong style="color:#D4B45C;">Pack Pro 49€</strong>. Si vous voulez toucher plusieurs langues et WooCommerce → <strong style="color:#D4B45C;">Pack Premium 99€</strong>. <strong style="color:#fff;">Si votre business doit vraiment générer des résultats → parlons sur-mesure.</strong></p>
            </div>
        </div>
    </section>

    <!-- Comparatif DIY vs Pro -->
    <section class="ag-section ag-section--image-luxe">
        <div class="ag-container">
            <h2 class="ag-section__title ag-anim" data-anim="title" style="text-align:center;">Template DIY vs <em>Site professionnel</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc" style="text-align:center;margin-left:auto;margin-right:auto;">Vous hésitez ? Voici la différence entre faire soi-même et confier à un professionnel.</p>

            <div class="ag-compare">
                <div class="ag-compare__col ag-compare__col--diy ag-anim" data-anim="card">
                    <h3 class="ag-compare__title">Template DIY</h3>
                    <span class="ag-compare__price">Gratuit / 49€ / 99€</span>
                    <ul>
                        <li class="ag-compare__bad">Contenu placeholder à remplacer</li>
                        <li class="ag-compare__bad">Design générique, vu sur d'autres sites</li>
                        <li class="ag-compare__bad">SEO basique, pas optimisé pour votre marché</li>
                        <li class="ag-compare__bad">Pas de stratégie de conversion</li>
                        <li class="ag-compare__bad">Temps d'installation : 10-40 heures</li>
                        <li class="ag-compare__bad">Résultat : un site qui existe</li>
                        <li class="ag-compare__bad">Support limité</li>
                        <li class="ag-compare__bad">Nécessite des connaissances techniques</li>
                    </ul>
                </div>

                <div class="ag-compare__vs">VS</div>

                <div class="ag-compare__col ag-compare__col--pro ag-anim" data-anim="card">
                    <h3 class="ag-compare__title">Site par Alliance Groupe</h3>
                    <span class="ag-compare__price">À partir de 1 500€</span>
                    <ul>
                        <li class="ag-compare__good">Contenu rédigé par des pros du copywriting</li>
                        <li class="ag-compare__good">Design unique, sur-mesure pour votre marque</li>
                        <li class="ag-compare__good">SEO avancé ciblant VOS mots-clés</li>
                        <li class="ag-compare__good">Stratégie de conversion éprouvée (+340% leads)</li>
                        <li class="ag-compare__good">Temps pour vous : 0 heures — on fait tout</li>
                        <li class="ag-compare__good">Résultat : un site qui VEND</li>
                        <li class="ag-compare__good">Support illimité + maintenance</li>
                        <li class="ag-compare__good">Aucune compétence technique requise</li>
                    </ul>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-gold" style="margin-top:20px;">Parlons de votre projet →</a>
                </div>
            </div>

            <div style="text-align:center;margin-top:48px;padding:36px;background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.15);border-radius:16px;">
                <p style="font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:8px;">Un template ne remplacera jamais un professionnel.</p>
                <p style="color:#b0b0bc;margin-bottom:20px;">Nos clients génèrent en moyenne +340% de leads avec un site sur-mesure. Un template génère... des frustrations.</p>
                <div class="ag-hero__buttons">
                    <a href="tel:+33623526074" class="ag-btn-gold">Appeler Fabrizio — 06.23.52.60.74</a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Demander un devis gratuit →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Templates gratuits (download zone — en bout de parcours) -->
    <section class="ag-section ag-section--marbre" id="ag-download-zone">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Téléchargement gratuit</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Vous êtes encore là ? <em>Téléchargez</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Vous avez tout lu, vous connaissez les limites, vous savez quand passer au Pro ou au sur-mesure. Maintenant vous pouvez télécharger en toute connaissance de cause. Également disponibles sur <a href="https://profiles.wordpress.org/adminag/" target="_blank" rel="noopener" style="color:#D4B45C;font-weight:700;">ma page WordPress.org officielle</a>.</p>

            <div class="ag-tpl__grid">

                <!-- Template Restaurant -->
                <div class="ag-tpl-card ag-anim" data-anim="card">
                    <div class="ag-tpl-card__img">
                        <div class="ag-tpl-card__preview">
                            <span style="font-size:2rem;">🍽️</span>
                            <strong>AG Starter Restaurant</strong>
                            <small>Or &amp; noir — 100% FR</small>
                        </div>
                    </div>
                    <div class="ag-tpl-card__body">
                        <span class="ag-tpl-card__badge ag-tpl-card__badge--free">Gratuit · v1.0</span>
                        <h3 class="ag-tpl-card__title">Starter Restaurant</h3>
                        <p class="ag-tpl-card__desc">Pour restaurant, bistrot, bar ou café. Hero, carte, réservation, privatisation, histoire, horaires. Textes français, pas de Lorem ipsum.</p>
                        <ul class="ag-tpl-card__features">
                            <li>Page d'accueil complète pré-remplie</li>
                            <li>Design sombre or &amp; noir</li>
                            <li>100% responsive, sans plugin</li>
                            <li>Blog, commentaires, recherche</li>
                            <li>Translation-ready (GPL v2+)</li>
                        </ul>
                        <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="restaurant" data-file="<?php echo esc_url($dl_base . 'ag-starter-restaurant.zip'); ?>">Télécharger gratuitement →</button>
                    </div>
                </div>

                <!-- Template Artisan -->
                <div class="ag-tpl-card ag-anim" data-anim="card">
                    <div class="ag-tpl-card__img">
                        <div class="ag-tpl-card__preview">
                            <span style="font-size:2rem;">🔨</span>
                            <strong>AG Starter Artisan</strong>
                            <small>Bronze &amp; noir — 100% FR</small>
                        </div>
                    </div>
                    <div class="ag-tpl-card__body">
                        <span class="ag-tpl-card__badge ag-tpl-card__badge--free">Gratuit · v1.0</span>
                        <h3 class="ag-tpl-card__title">Starter Artisan</h3>
                        <p class="ag-tpl-card__desc">Pour artisans, plombiers, électriciens, menuisiers, BTP. Prestations, zones d'intervention, devis, réalisations. Textes français, ton pro.</p>
                        <ul class="ag-tpl-card__features">
                            <li>Page d'accueil complète pré-remplie</li>
                            <li>Design sombre bronze &amp; noir</li>
                            <li>100% responsive, sans plugin</li>
                            <li>Blog, commentaires, recherche</li>
                            <li>Translation-ready (GPL v2+)</li>
                        </ul>
                        <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="artisan" data-file="<?php echo esc_url($dl_base . 'ag-starter-artisan.zip'); ?>">Télécharger gratuitement →</button>
                    </div>
                </div>

                <!-- Template Coach -->
                <div class="ag-tpl-card ag-anim" data-anim="card">
                    <div class="ag-tpl-card__img">
                        <div class="ag-tpl-card__preview">
                            <span style="font-size:2rem;">💼</span>
                            <strong>AG Starter Coach</strong>
                            <small>Bleu teal &amp; marine — 100% FR</small>
                        </div>
                    </div>
                    <div class="ag-tpl-card__body">
                        <span class="ag-tpl-card__badge ag-tpl-card__badge--free">Gratuit · v1.0</span>
                        <h3 class="ag-tpl-card__title">Starter Coach</h3>
                        <p class="ag-tpl-card__desc">Pour coachs, consultants, formateurs, thérapeutes. Services, séances, témoignages, rendez-vous. Textes français, ton bienveillant.</p>
                        <ul class="ag-tpl-card__features">
                            <li>Page d'accueil complète pré-remplie</li>
                            <li>Design sombre teal &amp; marine</li>
                            <li>100% responsive, sans plugin</li>
                            <li>Blog, commentaires, recherche</li>
                            <li>Translation-ready (GPL v2+)</li>
                        </ul>
                        <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="coach" data-file="<?php echo esc_url($dl_base . 'ag-starter-coach.zip'); ?>">Télécharger gratuitement →</button>
                    </div>
                </div>

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

            <!-- Résumé des 3 niveaux -->
            <div style="max-width:900px;margin:32px auto 0;padding:32px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.08);border-radius:16px;">
                <h3 style="text-align:center;font-size:1.15rem;margin-bottom:20px;">L'écosystème AG Starter en <em>3 niveaux</em></h3>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;text-align:center;">
                    <div style="padding:20px;background:rgba(40,167,69,.05);border:1px solid rgba(40,167,69,.2);border-radius:12px;">
                        <div style="font-size:1.8rem;margin-bottom:8px;">🆓</div>
                        <strong style="color:#28a745;display:block;margin-bottom:6px;">Gratuit</strong>
                        <p style="color:#b0b0bc;font-size:.85rem;line-height:1.6;margin:0;">Thème basique en français + plugin compagnon d'import. Vous avez un site fonctionnel et minimaliste. Parfait pour démarrer.</p>
                    </div>
                    <div style="padding:20px;background:rgba(212,180,92,.05);border:1px solid rgba(212,180,92,.25);border-radius:12px;">
                        <div style="font-size:1.8rem;margin-bottom:8px;">⚡</div>
                        <strong style="color:#D4B45C;display:block;margin-bottom:6px;">Pro — 49€</strong>
                        <p style="color:#b0b0bc;font-size:.85rem;line-height:1.6;margin:0;">Plugin qui ajoute un vrai design travaillé : animations, blocs personnalisés, customizer avancé. Votre site passe de "basique" à "pro".</p>
                    </div>
                    <div style="padding:20px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.35);border-radius:12px;">
                        <div style="font-size:1.8rem;margin-bottom:8px;">🌍</div>
                        <strong style="color:#D4B45C;display:block;margin-bottom:6px;">Premium — 99€</strong>
                        <p style="color:#b0b0bc;font-size:.85rem;line-height:1.6;margin:0;">Tout Pro + multi-langue (6 langues), WooCommerce, sections premium, support prioritaire. Pour toucher un public international.</p>
                    </div>
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
