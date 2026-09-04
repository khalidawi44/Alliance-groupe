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
        <ul class="ag-nav__list" id="ag-nav-list">

            <!-- Gagner / Studio + dropdown -->
            <li class="ag-nav__has-sub ag-nav__highlight">
                <a href="<?php echo esc_url(home_url('/sites-express')); ?>">Gagner <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Commander &amp; créer</span>
                            <a href="<?php echo esc_url(home_url('/sites-express')); ?>" class="ag-mega__link">
                                <span><strong>Commander mon site</strong><small>Prix fixe dès 490 € · payable en 4× PayPal</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/studio')); ?>" class="ag-mega__link">
                                <span><strong>Studio créatif</strong><small>Crée vidéos &amp; visuels, partage en 1 clic</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/ambassadeurs')); ?>" class="ag-mega__link">
                                <span><strong>Devenir ambassadeur</strong><small>Gagne 10 % sur chaque vente</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/classement')); ?>" class="ag-mega__link">
                                <span><strong>Classement</strong><small>Le championnat des commerciaux</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/connexion')); ?>" class="ag-mega__link ag-mega__link--all" style="margin-top:8px;border-top:1px dashed rgba(212,180,92,.25);padding-top:12px;">
                                <span><strong>Mon espace</strong><small>Connexion / tableau de bord</small></span>
                            </a>
                        </div>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Nouveau · par l'IA</span>
                            <a href="<?php echo esc_url(home_url('/atelier')); ?>" class="ag-mega__link">
                                <span><strong>Atelier IA</strong><small>Tous les outils IA en un seul endroit</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/devis-instant')); ?>" class="ag-mega__link">
                                <span><strong>Devis instantané</strong><small>Chiffrage en 30 s, sans rendez-vous</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/refais-mon-site')); ?>" class="ag-mega__link">
                                <span><strong>Refais mon site</strong><small>Ton site modernisé par l'IA, en direct</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/fait-par-lia')); ?>" class="ag-mega__link">
                                <span><strong>Fait par l'IA</strong><small>Le journal public de notre IA</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Cadeaux gratuits + dropdown -->
            <li class="ag-nav__has-sub ag-nav__highlight">
                <a href="<?php echo esc_url(home_url('/audit-seo')); ?>">Cadeaux <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Offert · 0 €</span>
                            <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>" class="ag-mega__link">
                                <span><strong>6 templates WordPress</strong><small>Thèmes métier gratuits, prêts à installer</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/audit-seo')); ?>" class="ag-mega__link">
                                <span><strong>Audit SEO gratuit</strong><small>Note /100 + rapport PDF de votre site</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>" class="ag-mega__link">
                                <span><strong>Tester mon site</strong><small>Sécurité : note /100 + failles visibles, gratuit</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/tirage-au-sort')); ?>" class="ag-mega__link">
                                <span><strong>1 site gratuit / mois</strong><small>Tirage au sort, participation gratuite</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/composants')); ?>" class="ag-mega__link">
                                <span><strong>Composants web gratuits</strong><small>Boutons &amp; effets à copier, façon uiverse</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Sur-mesure (offre premium) -->
            <li class="ag-nav__item">
                <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>">&nbsp;Sur-mesure</a>
            </li>

            <!-- Services + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/services')); ?>">Services <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Nos expertises</span>
                            <a href="<?php echo esc_url(home_url('/service-creation-web')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>Création Web</strong>
                                    <small>Sites vitrines & e-commerce</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-ia')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>IA & Automatisation</strong>
                                    <small>Chatbots, workflows, gains de temps</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-seo')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>SEO</strong>
                                    <small>Référencement naturel & local</small>
                                </span>
                            </a>
                        </div>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">&nbsp;</span>
                            <a href="<?php echo esc_url(home_url('/service-publicite')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>Publicité Digitale</strong>
                                    <small>Google Ads, Meta Ads</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-branding')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>Branding</strong>
                                    <small>Identité visuelle & charte</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-conseil')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>Conseil Stratégique</strong>
                                    <small>Audit & accompagnement</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/resilience-ransomware')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>Résilience Ransomware</strong>
                                    <small>Attaque simulée &amp; test de sauvegardes · dès 490 €</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>" class="ag-mega__link">
                                <span>
                                    <strong>Tester mon site</strong>
                                    <small>Diagnostic gratuit, puis audit approfondi &amp; expert 24 h</small>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Templates WordPress + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>">Templates <span class="ag-nav__pulse-pill">GRATUIT</span> <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Templates WordPress gratuits</span>
                            <a href="<?php echo esc_url(home_url('/wordpress-avocat')); ?>" class="ag-mega__link">
                                <span><strong>Avocat</strong><small>Cabinet, juriste, conseil juridique</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-restaurant')); ?>" class="ag-mega__link">
                                <span><strong>Restaurant</strong><small>Bistrot, bar, café, gastronomique</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-artisan')); ?>" class="ag-mega__link">
                                <span><strong>Artisan</strong><small>Plombier, électricien, menuisier, BTP</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-coach')); ?>" class="ag-mega__link">
                                <span><strong>Coach</strong><small>Consultant, formateur, thérapeute</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-barber')); ?>" class="ag-mega__link">
                                <span><strong>Barber Shop <span style="display:inline-block;margin-left:4px;padding:1px 8px;background:var(--color-success);color:#fff;font-size:.62rem;font-weight:700;border-radius:100px;text-transform:uppercase;">Nouveau</span></strong><small>Coiffeur, barbier, file d'attente QR</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-association')); ?>" class="ag-mega__link" style="background:rgba(225,15,26,0.08);border:1px solid rgba(225,15,26,0.35);border-radius:8px;padding:10px 12px;">
                                <span><strong style="color:#ffb1b6;">Association <span style="display:inline-block;margin-left:4px;padding:1px 8px;background:#E10F1A;color:#fff;font-size:.62rem;font-weight:700;border-radius:100px;text-transform:uppercase;">100% gratuit</span></strong><small style="color:#ffd0d4;">Mouvement militant, asso loi 1901, syndicat</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/contact?source=menu&demande=template-metier')); ?>" class="ag-mega__link ag-mega__link--all" style="margin-top:8px;border-top:1px dashed rgba(212,180,92,.25);padding-top:12px;">
                                <span><strong>Votre métier ? Demandez-le</strong><small>On vous crée votre template sur mesure</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Réalisations + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/realisations')); ?>">Nos réalisations <span class="ag-nav__arrow">&#9662;</span></a>
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

            <!-- À propos + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/a-propos')); ?>">À&nbsp;propos <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">L'agence</span>
                            <a href="<?php echo esc_url(home_url('/a-propos')); ?>" class="ag-mega__link">
                                <span><strong>Notre histoire</strong><small>Vision &amp; valeurs du studio</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/notre-fondateur')); ?>" class="ag-mega__link">
                                <span><strong>Notre Fondateur</strong><small>Le parcours de Fabrizio</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Articles + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/articles')); ?>">Articles <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Articles récents</span>
                            <?php
                            $recent = new WP_Query(['posts_per_page' => 4, 'post_status' => 'publish']);
                            if ($recent->have_posts()) :
                                while ($recent->have_posts()) : $recent->the_post();
                            ?>
                            <a href="<?php the_permalink(); ?>" class="ag-mega__link">
                                <span><strong><?php echo wp_trim_words(get_the_title(), 6); ?></strong><small><?php echo get_the_date('d M Y'); ?></small></span>
                            </a>
                            <?php endwhile; wp_reset_postdata(); endif; ?>
                            <a href="<?php echo esc_url(home_url('/articles')); ?>" class="ag-mega__link ag-mega__link--all">
                                <span><strong>Tous les articles →</strong></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega ag-mega--sm">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Nous joindre</span>
                            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-mega__link">
                                <span><strong>Contact d'urgence</strong><small>Besoin urgent — réponse sous 24h</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>" class="ag-mega__link">
                                <span><strong>Projet sur-mesure</strong><small>Devis gratuit pour les projets exigeants</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/questions-flash')); ?>" class="ag-mega__link">
                                <span><strong>Questions Flash</strong><small>Réponse écrite experte sous 48h</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <a href="<?php echo esc_url(home_url('/sites-express')); ?>" class="ag-nav__cta">
            Sites pro d&egrave;s <span class="ag-prix">490&nbsp;&euro;</span>
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

            <!-- PRIORITÉ 1 : VENDRE — offres en haut -->
            <a href="<?php echo esc_url(home_url('/sites-express')); ?>" style="display:block;padding:15px 4px;color:#fff;font-weight:700;border-bottom:1px solid rgba(212,180,92,.15);">Sites Express <span style="opacity:.7;font-weight:400;">(prix fixes, payable 4×)</span></a>

            <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>" style="display:block;padding:15px 4px;color:#D4B45C;font-weight:700;border-bottom:1px solid rgba(212,180,92,.15);">Sur-mesure <span style="opacity:.7;font-weight:400;">(sur devis)</span></a>

            <a href="<?php echo esc_url(home_url('/systeme-prospection')); ?>" style="display:block;padding:15px 4px;color:#fff;font-weight:700;border-bottom:1px solid rgba(212,180,92,.15);">Système de prospection <span style="opacity:.7;font-weight:400;">(trouve vos clients)</span></a>

            <!-- PRIORITÉ 1bis : NOS OUTILS IA (nouveauté, aimant à leads) -->
            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Nos outils IA <span class="ag-nav__pulse-pill">NOUVEAU</span> <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/devis-instant')); ?>">Devis instantané (30 s)</a>
                    <a href="<?php echo esc_url(home_url('/refais-mon-site')); ?>">Refais mon site par l'IA</a>
                    <a href="<?php echo esc_url(home_url('/fait-par-lia')); ?>">Fait par l'IA (journal)</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Cadeaux gratuits <span class="ag-nav__pulse-pill">0 €</span> <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>">6 templates WordPress gratuits</a>
                    <a href="<?php echo esc_url(home_url('/audit-seo')); ?>">Audit SEO gratuit (note /100)</a>
                    <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>">Tester mon site (sécurité, gratuit)</a>
                    <a href="<?php echo esc_url(home_url('/tirage-au-sort')); ?>">Gagner 1 site / mois (tirage)</a>
                    <a href="<?php echo esc_url(home_url('/composants')); ?>">Composants web gratuits</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Services <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/services')); ?>">Tous les services</a>
                    <a href="<?php echo esc_url(home_url('/service-creation-web')); ?>">Création Web</a>
                    <a href="<?php echo esc_url(home_url('/service-ia')); ?>">IA & Automatisation</a>
                    <a href="<?php echo esc_url(home_url('/service-seo')); ?>">SEO</a>
                    <a href="<?php echo esc_url(home_url('/service-publicite')); ?>">Publicité Digitale</a>
                    <a href="<?php echo esc_url(home_url('/service-branding')); ?>">Branding</a>
                    <a href="<?php echo esc_url(home_url('/service-conseil')); ?>">Conseil Stratégique</a>
                    <a href="<?php echo esc_url(home_url('/resilience-ransomware')); ?>">Résilience Ransomware</a>
                    <a href="<?php echo esc_url(home_url('/tester-mon-site')); ?>">Tester mon site (audit sécurité)</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Nos templates <span class="ag-nav__pulse-pill">GRATUIT</span> <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>" style="color:#D4B45C;font-weight:700;">Tous les templates · Choisissez votre métier</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-avocat')); ?>">Avocat</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-restaurant')); ?>">Restaurant</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-artisan')); ?>">Artisan</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-coach')); ?>">Coach</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-barber')); ?>">Barber Shop <span style="display:inline-block;margin-left:4px;padding:1px 6px;background:#22c55e;color:#fff;font-size:.62rem;font-weight:700;border-radius:100px;text-transform:uppercase;">Nouveau</span></a>
                    <a href="<?php echo esc_url(home_url('/wordpress-association')); ?>" style="color:#ffb1b6;">Association <span style="display:inline-block;margin-left:4px;padding:1px 6px;background:#E10F1A;color:#fff;font-size:.62rem;font-weight:700;border-radius:100px;text-transform:uppercase;">100% gratuit</span></a>
                    <a href="<?php echo esc_url(home_url('/contact?source=menu&demande=template-metier')); ?>" style="color:#D4B45C;font-weight:700;">Votre métier ? Demandez-le</a>
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

            <!-- PRIORITÉ 2 : GAGNER DE L'ARGENT (vendeurs/ambassadeurs) -->
            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Gagner de l'argent <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/ambassadeurs')); ?>">Devenir ambassadeur (10 %)</a>
                    <a href="<?php echo esc_url(home_url('/studio')); ?>">Studio créatif</a>
                    <a href="<?php echo esc_url(home_url('/classement')); ?>">Classement</a>
                    <a href="<?php echo esc_url(home_url('/connexion')); ?>">Mon espace</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">À propos <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/a-propos')); ?>">Notre histoire</a>
                    <a href="<?php echo esc_url(home_url('/notre-fondateur')); ?>">Notre Fondateur</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Articles <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/articles')); ?>">Tous les articles</a>
                    <?php
                    $recent_m = new WP_Query(['posts_per_page' => 4, 'post_status' => 'publish']);
                    if ($recent_m->have_posts()) :
                        while ($recent_m->have_posts()) : $recent_m->the_post();
                    ?>
                    <a href="<?php the_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 6); ?></a>
                    <?php endwhile; wp_reset_postdata(); endif; ?>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Contact <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact d'urgence</a>
                    <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>">Projet sur-mesure</a>
                    <a href="<?php echo esc_url(home_url('/questions-flash')); ?>">Questions Flash</a>
                </div>
            </div>
        </div>

        <div class="ag-mobile-menu__footer">
            <a href="tel:+33744829516" class="ag-btn-gold" style="width:100%;justify-content:center;">07.44.82.95.16</a>
            <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline" style="width:100%;justify-content:center;">Nous écrire</a>
        </div>
    </div>
</div>
