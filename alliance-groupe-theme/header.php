<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#ag-main-content" class="ag-skip-link">Aller au contenu principal</a>

<nav class="ag-nav" id="ag-nav">
    <div class="ag-nav__inner">
        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="ag-nav__logo" aria-label="Alliance Groupe — Accueil">
            <?php
            $logo_url = '';
            $img_dir = get_stylesheet_directory() . '/assets/images/';
            $img_uri = get_stylesheet_directory_uri() . '/assets/images/';
            foreach ( array('jpg','jpeg','png','webp','svg') as $ext ) {
                if ( file_exists( $img_dir . 'logo.' . $ext ) ) {
                    $logo_url = $img_uri . 'logo.' . $ext;
                    break;
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
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a></li>

            <!-- Services + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/services')); ?>">Services <span class="ag-nav__arrow">&#9662;</span></a>
                <div class="ag-mega">
                    <div class="ag-mega__inner">
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Nos expertises</span>
                            <a href="<?php echo esc_url(home_url('/service-creation-web')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">🌐</span>
                                <span>
                                    <strong>Création Web</strong>
                                    <small>Sites vitrines & e-commerce</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-ia')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">🤖</span>
                                <span>
                                    <strong>IA & Automatisation</strong>
                                    <small>Chatbots, workflows, gains de temps</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-seo')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">🔍</span>
                                <span>
                                    <strong>SEO</strong>
                                    <small>Référencement naturel & local</small>
                                </span>
                            </a>
                        </div>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">&nbsp;</span>
                            <a href="<?php echo esc_url(home_url('/service-publicite')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">📢</span>
                                <span>
                                    <strong>Publicité Digitale</strong>
                                    <small>Google Ads, Meta Ads</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-branding')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">🎨</span>
                                <span>
                                    <strong>Branding</strong>
                                    <small>Identité visuelle & charte</small>
                                </span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/service-conseil')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">💡</span>
                                <span>
                                    <strong>Conseil Stratégique</strong>
                                    <small>Audit & accompagnement</small>
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
                                <span class="ag-mega__icon">⚖️</span>
                                <span><strong>Avocat <span style="display:inline-block;margin-left:4px;padding:1px 8px;background:#28a745;color:#fff;font-size:.62rem;font-weight:700;border-radius:100px;text-transform:uppercase;letter-spacing:.5px;">Nouveau</span></strong><small>Cabinet, juriste, conseil juridique</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-restaurant')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">🍽️</span>
                                <span><strong>Restaurant</strong><small>Bistrot, bar, café, gastronomique</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-artisan')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">🔨</span>
                                <span><strong>Artisan</strong><small>Plombier, électricien, menuisier, BTP</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/wordpress-coach')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">💼</span>
                                <span><strong>Coach</strong><small>Consultant, formateur, thérapeute</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/contact?source=menu&demande=template-metier')); ?>" class="ag-mega__link ag-mega__link--all" style="margin-top:8px;border-top:1px dashed rgba(212,180,92,.25);padding-top:12px;">
                                <span class="ag-mega__icon">💎</span>
                                <span><strong>Votre métier ? Demandez-le</strong><small>On vous crée votre template sur mesure</small></span>
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
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">Nos projets</span>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#anna-photo" class="ag-mega__link">
                                <span class="ag-mega__icon">📸</span>
                                <span><strong>Anna Photo</strong><small>Blog photo, Nantes</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#la-environnement" class="ag-mega__link">
                                <span class="ag-mega__icon">🌿</span>
                                <span><strong>L.A Environnement</strong><small>Site vitrine, Loire-Atlantique</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#maison-riviera" class="ag-mega__link">
                                <span class="ag-mega__icon">🏠</span>
                                <span><strong>Maison Riviera</strong><small>E-commerce, Nice</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#cabinet-martin" class="ag-mega__link">
                                <span class="ag-mega__icon">⚖️</span>
                                <span><strong>Cabinet Martin</strong><small>Site vitrine, Paris</small></span>
                            </a>
                        </div>
                        <div class="ag-mega__col">
                            <span class="ag-mega__label">&nbsp;</span>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#fitness-lab" class="ag-mega__link">
                                <span class="ag-mega__icon">💪</span>
                                <span><strong>Fitness Lab</strong><small>App & site, Lyon</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#saveurs-orient" class="ag-mega__link">
                                <span class="ag-mega__icon">🍽️</span>
                                <span><strong>Saveurs d'Orient</strong><small>Restaurant, Marrakech</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/realisations')); ?>#techvision" class="ag-mega__link">
                                <span class="ag-mega__icon">🚀</span>
                                <span><strong>TechVision Pro</strong><small>SaaS, Nantes</small></span>
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
                                <span class="ag-mega__icon">🏛️</span>
                                <span><strong>Notre histoire</strong><small>Vision, valeurs & équipe</small></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/notre-fondateur')); ?>" class="ag-mega__link">
                                <span class="ag-mega__icon">👤</span>
                                <span><strong>Notre Fondateur</strong><small>Le parcours de Fabrizio</small></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Blog + dropdown -->
            <li class="ag-nav__has-sub">
                <a href="<?php echo esc_url(home_url('/blog')); ?>">Blog <span class="ag-nav__arrow">&#9662;</span></a>
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
                            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="ag-mega__link ag-mega__link--all">
                                <span><strong>Tous les articles →</strong></span>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a></li>
        </ul>

        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-nav__cta">
            Parlons-en <span>→</span>
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
            <button class="ag-mobile-menu__close" id="ag-mobile-close" type="button" aria-label="Fermer">✕</button>
        </div>

        <div class="ag-mobile-menu__content">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ag-mobile-menu__link">Accueil</a>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Services <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/services')); ?>">Tous les services</a>
                    <a href="<?php echo esc_url(home_url('/service-creation-web')); ?>">🌐 Création Web</a>
                    <a href="<?php echo esc_url(home_url('/service-ia')); ?>">🤖 IA & Automatisation</a>
                    <a href="<?php echo esc_url(home_url('/service-seo')); ?>">🔍 SEO</a>
                    <a href="<?php echo esc_url(home_url('/service-publicite')); ?>">📢 Publicité Digitale</a>
                    <a href="<?php echo esc_url(home_url('/service-branding')); ?>">🎨 Branding</a>
                    <a href="<?php echo esc_url(home_url('/service-conseil')); ?>">💡 Conseil Stratégique</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Templates <span class="ag-nav__pulse-pill">GRATUIT</span> <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>">Tous les templates</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-avocat')); ?>">⚖️ Avocat <span style="display:inline-block;margin-left:4px;padding:1px 6px;background:#28a745;color:#fff;font-size:.62rem;font-weight:700;border-radius:100px;text-transform:uppercase;letter-spacing:.3px;">Nouveau</span></a>
                    <a href="<?php echo esc_url(home_url('/wordpress-restaurant')); ?>">🍽️ Restaurant</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-artisan')); ?>">🔨 Artisan</a>
                    <a href="<?php echo esc_url(home_url('/wordpress-coach')); ?>">💼 Coach</a>
                    <a href="<?php echo esc_url(home_url('/contact?source=menu&demande=template-metier')); ?>" style="color:#D4B45C;font-weight:700;">💎 Votre métier ? Demandez-le</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Réalisations <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>">Tous les projets</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#anna-photo">📸 Anna Photo</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#la-environnement">🌿 L.A Environnement</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#maison-riviera">🏠 Maison Riviera</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#cabinet-martin">⚖️ Cabinet Martin</a>
                    <a href="<?php echo esc_url(home_url('/realisations')); ?>#fitness-lab">💪 Fitness Lab</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">À propos <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/a-propos')); ?>">🏛️ Notre histoire</a>
                    <a href="<?php echo esc_url(home_url('/notre-fondateur')); ?>">👤 Notre Fondateur</a>
                </div>
            </div>

            <div class="ag-mobile-menu__group">
                <button class="ag-mobile-menu__toggle" type="button">Blog <span class="ag-mobile-menu__arrow">+</span></button>
                <div class="ag-mobile-menu__sub">
                    <a href="<?php echo esc_url(home_url('/blog')); ?>">Tous les articles</a>
                    <?php
                    $recent_m = new WP_Query(['posts_per_page' => 4, 'post_status' => 'publish']);
                    if ($recent_m->have_posts()) :
                        while ($recent_m->have_posts()) : $recent_m->the_post();
                    ?>
                    <a href="<?php the_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), 6); ?></a>
                    <?php endwhile; wp_reset_postdata(); endif; ?>
                </div>
            </div>

            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-mobile-menu__link">Contact</a>
        </div>

        <div class="ag-mobile-menu__footer">
            <a href="tel:+33623526074" class="ag-btn-gold" style="width:100%;justify-content:center;">📞 06.23.52.60.74</a>
            <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline" style="width:100%;justify-content:center;">✉️ Nous écrire</a>
        </div>
    </div>
</div>
