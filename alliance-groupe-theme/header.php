<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://app.cal.com">
    <link rel="dns-prefetch" href="//cal.com">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#ag-main-content" class="ag-skip-link">Aller au contenu principal</a>

<!-- Menu fullscreen animé (burger overlay) -->
<?php get_template_part( 'template-parts/fullscreen-menu' ); ?>

<nav class="ag-nav" id="ag-nav" aria-label="Navigation principale">
    <div class="ag-nav__inner">
        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="ag-nav__logo" aria-label="Alliance Groupe — Accueil">
            <?php
            $logo_url = '';
            $img_dir = get_stylesheet_directory() . '/assets/images/';
            $img_uri = get_stylesheet_directory_uri() . '/assets/images/';
            // Préfère logo-carte (logo officiel) puis la version d'en-tête transparente.
            foreach ( array('logo-carte','logo-header','logo') as $base ) {
                foreach ( array('png','webp','svg','jpg','jpeg') as $ext ) {
                    if ( file_exists( $img_dir . $base . '.' . $ext ) ) { $logo_url = $img_uri . $base . '.' . $ext; break 2; }
                }
            }
            if ( $logo_url ) :
            ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="Alliance Groupe" class="ag-nav__logo-img">
            <?php endif; ?>
            <span class="ag-nav__logo-text">
            <?php
            $logo_text = 'Alliance Groupe';
            $delay = 0;
            for ($i = 0; $i < mb_strlen($logo_text); $i++) {
                $char = mb_substr($logo_text, $i, 1);
                if ($char === ' ') {
                    echo '&nbsp;';
                } else {
                    echo '<span class="ag-logo-letter" style="--d:' . $delay . '">' . esc_html($char) . '</span>';
                    $delay++;
                }
            }
            ?>
            </span>
        </a>

        <!-- Desktop Mega Menu -->
        <?php /* Barre réorganisée (oct. 2026) dans l'ordre du parcours client :
                 Nos sites → Maquette gratuite → Devis → Gratuit → Réalisations → L'agence.
                 Les anciennes entrées « Gagner » / « Cadeaux » / Studio / Classement /
                 Fait par l'IA / Composants / Questions Flash restent accessibles par
                 leurs pages et le pied de page, pas par la barre. */ ?>
        <ul class="ag-nav__list" id="ag-nav-list">

            <!-- Nos sites + dropdown -->
            <li class="ag-nav__has-sub ag-nav__highlight">
                <a href="<?php echo esc_url(home_url('/sites-express')); ?>">Nos sites <span class="ag-nav__pulse-pill">dès 490 €</span> <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Sites Express · prix fixe, livré vite</span>
                            <a href="<?php echo esc_url(home_url('/sites-express')); ?>#packs" class="ag-mega__link">
                                <span><strong>Essentiel · 490 €</strong><small>Site vitrine 1 page, livré en 5 jours</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/sites-express')); ?>#packs" class="ag-mega__link">
                                <span><strong>Pro · 890 € <span class="ag-nav__pulse-pill">le plus choisi</span></strong><small>Jusqu'à 6 pages, SEO, prise de rendez-vous</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/sites-express')); ?>#packs" class="ag-mega__link">
                                <span><strong>Boutique · 1 490 €</strong><small>E-commerce, jusqu'à 30 produits, paiement en ligne</small></span>
                            </a>
                        </div>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Et après</span>
                            <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>" class="ag-mega__link">
                                <span><strong>Projet sur-mesure</strong><small>Devis gratuit pour les projets exigeants</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/maintenance')); ?>" class="ag-mega__link">
                                <span><strong>Maintenance</strong><small>Hébergement, sauvegardes, sécurité · dès 29 €/mois</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/services')); ?>" class="ag-mega__link ag-mega__link--all">
                                <span><strong>Tous nos services →</strong><small>Création web, IA, SEO, publicité, branding, conseil</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Maquette gratuite (lien direct, mis en avant) -->
            <li class="ag-nav__item ag-nav__highlight">
                <a href="<?php echo esc_url(home_url('/refais-mon-site')); ?>">Maquette gratuite</a>
            </li>

            <!-- Devis instantané (lien direct) -->
            <li class="ag-nav__item">
                <a href="<?php echo esc_url(home_url('/devis-instant')); ?>">Devis instantané</a>
            </li>

            <!-- Gratuit + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/audit-seo')); ?>">Gratuit <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Offert · 0 €</span>
                            <a href="<?php echo esc_url(home_url('/refais-mon-site')); ?>" class="ag-mega__link">
                                <span><strong>Ma maquette gratuite</strong><small>Ton site modernisé par l'IA, en 2 minutes</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/audit-seo')); ?>" class="ag-mega__link">
                                <span><strong>Audit SEO gratuit</strong><small>Note /100 + rapport PDF de ton site</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>" class="ag-mega__link">
                                <span><strong>Tester la sécurité de mon site</strong><small>Note /100 + failles visibles, gratuit</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>" class="ag-mega__link">
                                <span><strong>Templates WordPress gratuits</strong><small>Avocat, restaurant, artisan, coach, barber, association</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/tirage-au-sort')); ?>" class="ag-mega__link">
                                <span><strong>1 site gratuit / mois</strong><small>Tirage au sort, participation gratuite</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Réalisations + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/realisations')); ?>">Réalisations <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <?php /* Uniquement des clients REELS (Gwen Services, Anna Photo,
                                 L.A Environnement). LFI Nantes Sud Clos Toreau reste
                                 volontairement sur /realisations seulement. Ne jamais
                                 remettre de projet d'exemple ici. */ ?>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Nos projets</span>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#gwen-services" class="ag-mega__link">
                                <span><strong>Gwen Services</strong><small>Aide à domicile, Nantes</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#anna-photo" class="ag-mega__link">
                                <span><strong>Anna Photo</strong><small>Blog photo, Nantes</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#la-environnement" class="ag-mega__link">
                                <span><strong>L.A Environnement</strong><small>Site vitrine, Loire-Atlantique</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>" class="ag-mega__link ag-mega__link--all">
                                <span><strong>Voir tous les projets →</strong></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- L'agence + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/a-propos')); ?>">L'agence <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Qui sommes-nous</span>
                            <a href="<?php echo esc_url(home_url('/a-propos')); ?>" class="ag-mega__link">
                                <span><strong>Notre histoire</strong><small>Un studio entre Nantes et Naples</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/notre-fondateur')); ?>" class="ag-mega__link">
                                <span><strong>Le fondateur</strong><small>Le parcours de Fabrice</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/articles')); ?>" class="ag-mega__link">
                                <span><strong>Articles</strong><small>Sécurité, WordPress, conseils</small></span>
                            </a>
                        </div>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Travailler avec nous</span>
                            <a href="<?php echo esc_url(home_url('/ambassadeurs')); ?>" class="ag-mega__link">
                                <span><strong>Devenir ambassadeur</strong><small>Partage ton lien, touche une commission sur chaque site vendu</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-mega__link">
                                <span><strong>Contact</strong><small>07 44 82 95 16 · réponse sous 24 h</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/connexion')); ?>" class="ag-mega__link ag-mega__link--all">
                                <span><strong>Mon espace →</strong><small>Connexion client / ambassadeur</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <a href="<?php echo esc_url(home_url('/sites-express')); ?>" class="ag-nav__cta">
            Commander mon site <span class="ag-prix">490&nbsp;&euro;</span>
            <span class="ag-pastille" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="1"/><path d="M3 9h18"/></svg>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M9 7h8v8"/></svg>
            </span>
        </a>

        <?php /* Le bouton « Sites pro » est masque sous 900px : sur telephone,
                 l'en-tete garde le telephone, seule action utile a une main. */ ?>
        <a href="tel:+33744829516" class="ag-nav__tel" aria-label="Appeler le 07 44 82 95 16">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.8 2z"/></svg>
        </a>

        <button class="ag-nav__burger" id="ag-burger" aria-label="Menu" type="button">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Mobile Fullscreen Menu -->
<div class="ag-mobile-menu" id="ag-mobile-menu">
    <div class="ag-mobile-menu__inner">
        <div class="ag-mobile-menu__header">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ag-nav__logo">
                <?php if ( $logo_url ) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="Alliance Groupe" class="ag-nav__logo-img">
                <?php endif; ?>
                Alliance Groupe
            </a>
            <button class="ag-mobile-menu__close" id="ag-mobile-close" type="button" aria-label="Fermer"></button>
        </div>

        <div class="ag-mobile-menu__content">

            <!-- PRIORITÉ 1 : ce qui convertit, en haut et en direct -->
            <a href="<?php echo esc_url(home_url('/refais-mon-site')); ?>" style="display:block;padding:15px 4px;color:#D4B45C;font-weight:700;border-bottom:1px solid rgba(212,180,92,.15);">Ma maquette gratuite <span style="opacity:.7;font-weight:400;">(en 2 minutes)</span></a>

            <a href="<?php echo esc_url(home_url('/devis-instant')); ?>" style="display:block;padding:15px 4px;color:#fff;font-weight:700;border-bottom:1px solid rgba(212,180,92,.15);">Devis instantané <span style="opacity:.7;font-weight:400;">(30 s, sans rendez-vous)</span></a>

            <a href="<?php echo esc_url(home_url('/sites-express')); ?>" style="display:block;padding:15px 4px;color:#fff;font-weight:700;border-bottom:1px solid rgba(212,180,92,.15);">Nos sites <span style="opacity:.7;font-weight:400;">(dès 490 €, prix fixe, payable 4×)</span></a>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Nos offres <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/sites-express')); ?>#packs">Essentiel · 490 €</a>
                    <a href="<?php echo esc_url(home_url('/sites-express')); ?>#packs">Pro · 890 € (le plus choisi)</a>
                    <a href="<?php echo esc_url(home_url('/sites-express')); ?>#packs">Boutique · 1 490 €</a>
                    <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>">Projet sur-mesure (sur devis)</a>
                    <a href="<?php echo esc_url(home_url('/maintenance')); ?>">Maintenance (dès 29 €/mois)</a>
                    <a href="<?php echo esc_url(home_url('/services')); ?>">Tous nos services</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Gratuit <span class="ag-nav__pulse-pill">0 €</span> <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/audit-seo')); ?>">Audit SEO gratuit (note /100)</a>
                    <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>">Tester la sécurité de mon site</a>
                    <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>">Templates WordPress gratuits</a>
                    <a href="<?php echo esc_url(home_url('/tirage-au-sort')); ?>">Gagner 1 site / mois (tirage)</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Nos réalisations <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <?php /* Uniquement des clients REELS. Ne jamais remettre de projet
                             d'exemple ici : ce menu est lu comme un portefeuille client. */ ?>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>">Tous les projets</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#gwen-services">Gwen Services</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#anna-photo">Anna Photo</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#la-environnement">L.A Environnement</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">L'agence <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/a-propos')); ?>">Notre histoire</a>
                    <a href="<?php echo esc_url(home_url('/notre-fondateur')); ?>">Le fondateur</a>
                    <a href="<?php echo esc_url(home_url('/articles')); ?>">Articles</a>
                    <a href="<?php echo esc_url(home_url('/ambassadeurs')); ?>">Devenir ambassadeur</a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a>
                    <a href="<?php echo esc_url(home_url('/connexion')); ?>">Mon espace</a>
                </div>
            </div>
        </div>
        <div class="ag-mobile-menu__footer">
            <a href="tel:+33744829516" class="ag-btn-gold" style="width:100%;justify-content:center;">07.44.82.95.16</a>
            <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline" style="width:100%;justify-content:center;">Nous écrire</a>
        </div>
    </div>
</div>
