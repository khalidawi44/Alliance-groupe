<?php get_header(); ?>

<main class="ag-single" id="ag-main-content">
    <article class="ag-article" itemscope itemtype="https://schema.org/Article">

        <!-- Header article : image plein cadre + titre en surimpression -->
        <?php
        if ( ! function_exists( 'ag_article_header_image' ) ) {
            /** Image d'en-tête d'un article : la « une » si définie, sinon une photo
             *  libre de droit du thème choisie selon le sujet (slug + catégorie). */
            function ag_article_header_image( $post_id ) {
                if ( has_post_thumbnail( $post_id ) ) {
                    return get_the_post_thumbnail_url( $post_id, 'large' );
                }
                $dir = get_stylesheet_directory_uri() . '/assets/images/';
                $h   = strtolower( get_post_field( 'post_name', $post_id ) );
                $c   = get_the_category( $post_id );
                if ( $c ) { $h .= ' ' . strtolower( $c[0]->slug . ' ' . $c[0]->name ); }
                $map = array(
                    // ===== 1 ARTICLE = 1 PHOTO UNIQUE (clés spécifiques par slug, 1re correspondance gagne) =====
                    // --- Sécurité ---
                    'resilience' => 'securite/pexels-lucasandrade-14019734.jpg',            // résilience ransomware (avant 'ransomware')
                    'ransomware' => 'securite/kaptured-by-kasia-7Ss09bTO5Zo-unsplash.jpg',  // que faire — pirate au clavier
                    'rancongiciel' => 'securite/kaptured-by-kasia-7Ss09bTO5Zo-unsplash.jpg',
                    'phishing'   => 'securite/tarik-haiga-BxELNNMN88Y-unsplash.jpg',        // masque / usurpation
                    'hameconnage'=> 'securite/tarik-haiga-BxELNNMN88Y-unsplash.jpg',
                    'nis2'       => 'securite/pexels-cookiecutter-37564547.jpg',            // salle serveurs
                    'sauvegarde' => 'securite/pexels-cookiecutter-17302202.jpg',            // baie de serveurs
                    'rgpd'       => 'securite/max-bender-XIVDN9cxOVc-unsplash.jpg',         // conformité / data
                    'wordpress-est-il' => 'securite/wordpress-securise.jpg',               // laptop + logo WordPress
                    'securise-comment' => 'securite/pexels-julio-lopez-75309646-34258666.jpg', // site sécurisé — code/terminal
                    'failles'    => 'securite/pexels-julio-lopez-75309646-34258666.jpg', // failles thèmes/plugins — code/terminal
                    // --- Web / marketing (vraies photos étalonnées marque) ---
                    'debutant-ia'=> 'articles/ia.jpg',                        // débuter avec l'IA — réseau neuronal
                    'ia-revolution' => 'articles/ia-leads.jpg',              // IA génère des leads — androïde
                    'automatis'  => 'articles/automatisation.jpg',           // automatisation — bras robotisés
                    'concurrents-volent' => 'articles/seo-concurrents.jpg',  // concurrents SEO — analytics
                    'seo-local'  => 'articles/seo-local.jpg',                // SEO local — fiche Google Maps
                    'refonte'    => 'articles/refonte.jpg',                  // refonte — poste designer
                    'template'   => 'articles/templates.jpg',               // templates — sites sur écrans
                    'coach'      => 'articles/coach.jpg',                    // coach sportif — salle de sport
                    'prix-site-internet-nantes' => 'articles/prix-nantes.jpg',       // prix Nantes — calculatrice
                    'prix-site-internet-professionnel' => 'articles/prix-pro.jpg',   // prix pro — poignée de main
                    'artisan'    => 'articles/artisan.jpg',                  // artisan — atelier
                    'pme-sans-site' => 'articles/pme.jpg',                   // PME sans site — commerçante
                    'ne-genere-aucun-lead' => 'articles/leads.jpg',         // aucun lead — courbe de croissance
                    // ===== Repli générique pour tout futur article =====
                    'ia-'        => 'articles/ia.jpg',
                    'seo'        => 'articles/seo-local.jpg',
                    'google'     => 'articles/seo-local.jpg',
                    'visibilite' => 'articles/seo-local.jpg',
                    'signes'     => 'articles/refonte.jpg',
                    'lent'       => 'articles/refonte.jpg',
                    'prix'       => 'articles/prix-nantes.jpg',
                    'cout'       => 'articles/prix-nantes.jpg',
                    'combien'    => 'articles/prix-nantes.jpg',
                    'devis'      => 'articles/prix-pro.jpg',
                    'ecommerce'  => 'articles/templates.jpg',
                    'vitrine'    => 'articles/templates.jpg',
                    'domaine'    => 'articles/templates.jpg',
                    'restaurant' => 'articles/templates.jpg',
                    'avocat'     => 'articles/templates.jpg',
                    'lead'       => 'articles/leads.jpg',
                    'pme'        => 'articles/pme.jpg',
                    'malware'    => 'securite/pexels-lucasandrade-14019734.jpg',
                    'virus'      => 'securite/pexels-lucasandrade-14019734.jpg',
                    'securise'   => 'securite/pexels-julio-lopez-75309646-34258666.jpg',
                    'securit'    => 'securite/max-bender-XIVDN9cxOVc-unsplash.jpg',
                    'wordpress'  => 'articles/templates.jpg',
                );
                foreach ( $map as $k => $img ) {
                    if ( false !== strpos( $h, $k ) ) { return $dir . $img; }
                }
                return $dir . 'cities/naples-1.jpg';
            }
        }
        $ag_hero_img = ag_article_header_image( get_the_ID() );
        ?>
        <header class="ag-article__header" style="position:relative;min-height:46vh;display:flex;align-items:flex-end;overflow:hidden;background:#0a0a0f">
            <img src="<?php echo esc_url( $ag_hero_img ); ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.5" loading="eager" fetchpriority="high">
            <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,10,15,.5),rgba(10,10,15,.35) 38%,rgba(10,10,15,.96))"></div>
            <div class="ag-container ag-container--narrow" style="position:relative;z-index:1;color:#fff;padding-top:44px;padding-bottom:42px">
                <nav class="ag-breadcrumb" aria-label="Fil d'Ariane" style="opacity:.85">
                    <a href="<?php echo esc_url(home_url('/')); ?>" style="color:#e7c979">Accueil</a>
                    <span>›</span>
                    <a href="<?php echo esc_url(home_url('/articles')); ?>" style="color:#e7c979">Articles</a>
                    <span>›</span>
                    <?php
                    $cats = get_the_category();
                    if ($cats) {
                        echo '<a href="' . esc_url(get_category_link($cats[0]->term_id)) . '" style="color:#e7c979">' . esc_html($cats[0]->name) . '</a>';
                        echo '<span>›</span>';
                    }
                    ?>
                    <span class="ag-breadcrumb__current"><?php the_title(); ?></span>
                </nav>

                <div class="ag-article__meta" style="display:flex;flex-wrap:wrap;gap:10px 18px;align-items:center;margin:16px 0 14px;font-size:.9rem;opacity:.9">
                    <time datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished"><?php echo get_the_date('d M Y'); ?></time>
                    <?php if ($cats) : ?>
                    <span class="ag-article__cat" style="padding:3px 12px;border:1px solid rgba(231,201,121,.5);border-radius:999px;color:#e7c979"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <span class="ag-article__read"><?php echo ag_reading_time(); ?> min de lecture</span>
                </div>

                <h1 class="ag-article__title" itemprop="headline" style="font-family:Georgia,serif;font-weight:600;font-size:clamp(1.9rem,4.6vw,3.1rem);line-height:1.1;margin:0;max-width:20ch;text-wrap:balance"><?php the_title(); ?></h1>

                <?php if (has_excerpt()) : ?>
                <p class="ag-article__chapeau" itemprop="description" style="margin:16px 0 0;max-width:60ch;font-size:1.1rem;line-height:1.55;color:rgba(255,255,255,.82)"><?php echo get_the_excerpt(); ?></p>
                <?php endif; ?>
            </div>
        </header>

        <!-- Contenu article -->
        <div class="ag-article__content" itemprop="articleBody">
            <div class="ag-container ag-container--narrow">
                <?php the_content(); ?>
            </div>
        </div>

        <!-- Maillage interne SEO — liens vers les pages clés (toutes les pages article) -->
        <section class="ag-article-links">
            <div class="ag-container ag-container--narrow">
                <div class="ag-article-links__box">
                    <h2 class="ag-article-links__title">Pour aller plus loin <em>avec Alliance Groupe</em></h2>
                    <div class="ag-article-links__grid">
                        <div class="ag-article-links__col">
                            <span class="ag-article-links__label">Nos services</span>
                            <ul>
                                <li><a href="<?php echo esc_url(home_url('/service-creation-web')); ?>">Création de site WordPress sur-mesure</a></li>
                                <li><a href="<?php echo esc_url(home_url('/service-seo')); ?>">SEO &amp; référencement naturel</a></li>
                                <li><a href="<?php echo esc_url(home_url('/service-ia')); ?>">IA &amp; automatisation</a></li>
                                <li><a href="<?php echo esc_url(home_url('/service-publicite')); ?>">Publicité Google &amp; Meta Ads</a></li>
                                <li><a href="<?php echo esc_url(home_url('/service-branding')); ?>">Branding &amp; identité visuelle</a></li>
                                <li><a href="<?php echo esc_url(home_url('/service-conseil')); ?>">Conseil stratégique digital</a></li>
                            </ul>
                        </div>
                        <div class="ag-article-links__col">
                            <span class="ag-article-links__label">Ressources &amp; agence</span>
                            <ul>
                                <li><a href="<?php echo esc_url(home_url('/templates-wordpress')); ?>">Templates WordPress métier gratuits</a></li>
                                <li><a href="<?php echo esc_url(home_url('/realisations')); ?>">Nos réalisations</a></li>
                                <li><a href="<?php echo esc_url(home_url('/pourquoi-alliance')); ?>">Pourquoi Alliance vs ThemeForest</a></li>
                                <li><a href="<?php echo esc_url(home_url('/a-propos')); ?>">Le studio, à Nantes</a></li>
                                <li><a href="<?php echo esc_url(home_url('/rendez-vous')); ?>">Réserver un audit gratuit</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Intermédiaire — Bandeau conversion -->
        <section class="ag-article-cta">
            <div class="ag-container ag-container--narrow">
                <div class="ag-article-cta__box">
                    <div class="ag-article-cta__icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#e7c979" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></div>
                    <h3 class="ag-article-cta__title">Besoin d'un accompagnement <em>professionnel</em> ?</h3>
                    <p class="ag-article-cta__text">Ne laissez pas vos concurrents prendre l'avantage. Appelez-nous pour un diagnostic gratuit de votre présence digitale.</p>
                    <div class="ag-article-cta__actions">
                        <a href="tel:+33744829516" class="ag-btn-tel"><svg class="ag-cadre" viewBox="0 0 240 58" preserveAspectRatio="none" aria-hidden="true"><path d="M9 1 H239 V49 L231 57 H1 V9 Z" vector-effect="non-scaling-stroke"/></svg><span class="ag-onde"></span><span class="ag-onde"></span><svg class="ag-combine" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.8 2z"/></svg><span class="ag-num">07 44 82 95 16</span></a>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Demander un devis gratuit →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tags -->
        <?php
        $tags = get_the_tags();
        if ($tags) :
        ?>
        <div class="ag-article__tags">
            <div class="ag-container ag-container--narrow">
                <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="ag-article__tag-link"><?php echo esc_html($tag->name); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Auteur / Crédibilité (E-E-A-T : auteur nommé, expertise adaptée au sujet) -->
        <?php
        $ag_is_secu = false;
        if ( $cats ) {
            foreach ( $cats as $ct ) {
                if ( false !== stripos( $ct->slug . ' ' . $ct->name, 'secur' ) || false !== stripos( $ct->slug, 'cyber' ) ) { $ag_is_secu = true; break; }
            }
        }
        $ag_author_role = $ag_is_secu
            ? 'Fondateur d\'Alliance Groupe · expert cybersécurité (audit, conformité NIS2)'
            : 'Fondateur d\'Alliance Groupe · expert création web &amp; référencement local';
        $ag_author_bio = $ag_is_secu
            ? 'Studio web ET cybersécurité à Nantes et Naples. J\'audite, sécurise et mets en conformité (NIS2, RGPD) les sites et les PME — parce qu\'un site qui se fait pirater ne rapporte rien.'
            : 'Studio web ET cybersécurité à Nantes et Naples. Je conçois des sites rapides, sécurisés et pensés pour générer des demandes — un seul interlocuteur, du conseil à la livraison.';
        ?>
        <section class="ag-author-box">
            <div class="ag-container ag-container--narrow">
                <div class="ag-author-box__inner">
                    <div class="ag-author-box__avatar">F</div>
                    <div class="ag-author-box__content">
                        <span class="ag-author-box__label">Rédigé par</span>
                        <strong class="ag-author-box__name">Fabrizio — Alliance Groupe</strong>
                        <span class="ag-author-box__role" style="display:block;color:#e7c979;font-size:.88rem;margin:2px 0 6px"><?php echo $ag_author_role; ?></span>
                        <p class="ag-author-box__bio"><?php echo esc_html( $ag_author_bio ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/a-propos' ) ); ?>" class="ag-author-box__cta">En savoir plus sur nous →</a>
                        &nbsp;<a href="tel:+33744829516" class="ag-author-box__cta">Nous appeler →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Articles liés -->
        <?php
        $related = new WP_Query([
            'posts_per_page' => 3,
            'post__not_in'   => [get_the_ID()],
            'category__in'   => $cats ? [wp_list_pluck($cats, 'term_id')[0]] : [],
            'orderby'        => 'rand',
        ]);
        if ($related->have_posts()) :
        ?>
        <section class="ag-related">
            <div class="ag-container">
                <h2 class="ag-related__title">Articles qui pourraient vous <em>intéresser</em></h2>
                <div class="ag-related__grid">
                    <?php while ($related->have_posts()) : $related->the_post(); ?>
                    <article class="ag-blog-card ag-anim" data-anim="card">
                        <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="ag-blog-card__img">
                            <?php the_post_thumbnail('medium_large'); ?>
                        </a>
                        <?php endif; ?>
                        <div class="ag-blog-card__body">
                            <div class="ag-blog-card__meta">
                                <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('d M Y'); ?></time>
                            </div>
                            <h3 class="ag-blog-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <p class="ag-blog-card__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 18); ?></p>
                            <a href="<?php the_permalink(); ?>" class="ag-blog-card__link">Lire l'article →</a>
                        </div>
                    </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- CTA Final — Maximum conversion -->
        <section class="ag-cta-final">
            <div class="ag-container">
                <div class="ag-cta-final__inner">
                    <span class="ag-cta-final__badge">Passez à l'action</span>
                    <h2 class="ag-cta-final__title">Votre entreprise mérite d'<em>exister en ligne</em></h2>
                    <p class="ag-cta-final__desc">Chaque jour sans stratégie digitale, c'est des clients qui vont chez vos concurrents. Discutons de votre projet — c'est gratuit et sans engagement.</p>
                    <div class="ag-cta-final__actions">
                        <a href="tel:+33744829516" class="ag-btn-tel"><svg class="ag-cadre" viewBox="0 0 240 58" preserveAspectRatio="none" aria-hidden="true"><path d="M9 1 H239 V49 L231 57 H1 V9 Z" vector-effect="non-scaling-stroke"/></svg><span class="ag-onde"></span><span class="ag-onde"></span><svg class="ag-combine" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.8 2z"/></svg><span class="ag-num">07 44 82 95 16</span></a>
                        <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline">contact@alliancegroupe-inc.com</a>
                    </div>
                    <p class="ag-cta-final__trust">Diagnostic gratuit &nbsp;·&nbsp; Sans engagement &nbsp;·&nbsp; Réponse sous 24 h</p>
                </div>
            </div>
        </section>

        <footer class="ag-article__footer">
            <div class="ag-container ag-container--narrow">
                <a href="<?php echo esc_url(home_url('/articles')); ?>" class="ag-btn-outline">← Retour aux articles</a>
            </div>
        </footer>
    </article>
</main>

<?php get_footer(); ?>
