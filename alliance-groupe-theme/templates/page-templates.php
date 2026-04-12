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

            <div class="ag-services__grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="ag-scard ag-anim" data-anim="card">
                    <div class="ag-scard__icon">🇫🇷</div>
                    <h3 class="ag-scard__title">100% français natif</h3>
                    <p class="ag-scard__text">Tous les textes, titres, horaires, exemples et messages sont déjà rédigés en français. Aucune traduction à faire, aucun Lorem ipsum anglais.</p>
                </div>
                <div class="ag-scard ag-anim" data-anim="card">
                    <div class="ag-scard__icon">⚡</div>
                    <h3 class="ag-scard__title">95% déjà prêt</h3>
                    <p class="ag-scard__text">Contrairement aux thèmes génériques qui vous demandent de tout construire, nos templates arrivent avec une structure complète : hero, sections, footer, réservation/devis. Vous changez juste le nom, les horaires et les photos.</p>
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

    <!-- Templates gratuits -->
    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gratuit</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Nos templates <em>gratuits</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Téléchargez le ZIP, installez via Apparence &gt; Thèmes &gt; Ajouter, et c'est parti. Bientôt disponibles aussi sur le répertoire officiel WordPress.org.</p>

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

            <!-- Plugin compagnon -->
            <div style="max-width:900px;margin:56px auto 0;padding:40px;background:linear-gradient(135deg,rgba(212,180,92,.08),rgba(212,180,92,.02));border:1px solid rgba(212,180,92,.3);border-radius:16px;">
                <div style="text-align:center;">
                    <span class="ag-tag" style="background:rgba(212,180,92,.15);">Bonus — Plugin compagnon</span>
                    <h3 style="font-size:1.4rem;margin:12px 0 8px;">Encore plus rapide avec <em>AG Starter Companion</em></h3>
                    <p style="color:#b0b0bc;max-width:640px;margin:0 auto 24px;font-size:.98rem;line-height:1.7;">Installez aussi notre plugin compagnon gratuit et vous obtenez un bouton <strong style="color:#D4B45C;">"Importer le contenu démo"</strong> dans l'admin WordPress. En un clic, il crée pour vous les 5 pages (Accueil, Carte/Prestations/Accompagnements, À propos, Contact…), le menu principal, la page d'accueil statique et les permaliens. Zéro code, zéro manipulation manuelle.</p>
                    <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                        <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="companion" data-file="<?php echo esc_url($dl_base . 'ag-starter-companion.zip'); ?>">⚡ Télécharger le plugin →</button>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Besoin d'aide ? On en parle</a>
                    </div>
                    <p style="color:#888;font-size:.85rem;margin-top:20px;">Le plugin détecte automatiquement quel thème AG Starter est actif et adapte le contenu. Fonctionne avec Restaurant, Artisan et Coach.</p>
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

    <!-- Packs Premium Stripe -->
    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Premium</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Packs <em>Design Premium</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Allez plus loin avec des outils de personnalisation professionnels. Modifiez tout en drag & drop.</p>

            <div class="ag-pricing__grid">

                <!-- Pack 69€ -->
                <div class="ag-price-card ag-anim" data-anim="card">
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">69<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Starter</h3>
                        <p class="ag-price-card__sub">Pour ceux qui veulent personnaliser</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li>Template WordPress au choix</li>
                        <li>WordPress Block Editor amélioré</li>
                        <li>10 blocs design premium</li>
                        <li>Personnalisation couleurs & polices</li>
                        <li>Support email 30 jours</li>
                        <li>Documentation d'installation</li>
                    </ul>
                    <a href="#" class="ag-btn-outline ag-stripe-btn" data-price="69">Acheter — 69€</a>
                </div>

                <!-- Pack 99€ -->
                <div class="ag-price-card ag-price-card--pop ag-anim" data-anim="card">
                    <span class="ag-price-card__badge">Populaire</span>
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">99<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Pro</h3>
                        <p class="ag-price-card__sub">Le meilleur rapport qualité-prix</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li>Template WordPress au choix</li>
                        <li>Elementor Pro inclus (1 site)</li>
                        <li>Drag & drop — modifiez tout</li>
                        <li>50+ widgets premium</li>
                        <li>Formulaires avancés</li>
                        <li>Support email 60 jours</li>
                        <li>Tutoriel vidéo personnalisé</li>
                    </ul>
                    <a href="#" class="ag-btn-gold ag-stripe-btn" data-price="99">Acheter — 99€</a>
                </div>

                <!-- Pack 149€ -->
                <div class="ag-price-card ag-anim" data-anim="card">
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">149<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Business</h3>
                        <p class="ag-price-card__sub">Tout inclus pour les ambitieux</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li>Template WordPress au choix</li>
                        <li>Elementor Pro inclus (1 site)</li>
                        <li>Plugin SEO Premium (Yoast SEO Premium)</li>
                        <li>Plugin de formulaires (WPForms Pro)</li>
                        <li>Plugin de cache (WP Rocket)</li>
                        <li>Support prioritaire 90 jours</li>
                        <li>Appel de 30 min avec un expert</li>
                        <li>Installation assistée</li>
                    </ul>
                    <a href="#" class="ag-btn-outline ag-stripe-btn" data-price="149">Acheter — 149€</a>
                </div>

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
                    <span class="ag-compare__price">Gratuit / 69-149€</span>
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

    // Open modal on click
    document.querySelectorAll('.ag-dl-trigger').forEach(function(btn){
        btn.addEventListener('click', function(){
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
