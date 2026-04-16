<?php get_header(); ?>

<main class="ag-single" id="ag-main-content">
    <article class="ag-article" itemscope itemtype="https://schema.org/Article">

        <!-- Header article avec breadcrumb SEO -->
        <header class="ag-article__header" style="background:#0c0c0f;">
            <div class="ag-container ag-container--narrow">
                <nav class="ag-breadcrumb" aria-label="Fil d'Ariane">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a>
                    <span>›</span>
                    <a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a>
                    <span>›</span>
                    <?php
                    $cats = get_the_category();
                    if ($cats) {
                        echo '<a href="' . esc_url(get_category_link($cats[0]->term_id)) . '">' . esc_html($cats[0]->name) . '</a>';
                        echo '<span>›</span>';
                    }
                    ?>
                    <span class="ag-breadcrumb__current"><?php the_title(); ?></span>
                </nav>

                <div class="ag-article__meta">
                    <time datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished"><?php echo get_the_date('d M Y'); ?></time>
                    <?php if ($cats) : ?>
                    <span class="ag-article__cat"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <span class="ag-article__read">⏱ <?php echo ag_reading_time(); ?> min de lecture</span>
                </div>

                <h1 class="ag-article__title" itemprop="headline"><?php the_title(); ?></h1>

                <?php if (has_excerpt()) : ?>
                <p class="ag-article__chapeau" itemprop="description"><?php echo get_the_excerpt(); ?></p>
                <?php endif; ?>
            </div>
        </header>

        <?php if (has_post_thumbnail()) : ?>
        <div class="ag-article__featured">
            <div class="ag-container ag-container--narrow">
                <?php the_post_thumbnail('large', ['itemprop' => 'image', 'loading' => 'lazy']); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contenu article -->
        <div class="ag-article__content" itemprop="articleBody">
            <div class="ag-container ag-container--narrow">
                <?php the_content(); ?>
            </div>
        </div>

        <!-- CTA Intermédiaire — Bandeau conversion -->
        <section class="ag-article-cta">
            <div class="ag-container ag-container--narrow">
                <div class="ag-article-cta__box">
                    <div class="ag-article-cta__icon">📞</div>
                    <h3 class="ag-article-cta__title">Besoin d'un accompagnement <em>professionnel</em> ?</h3>
                    <p class="ag-article-cta__text">Ne laissez pas vos concurrents prendre l'avantage. Appelez-nous pour un diagnostic gratuit de votre présence digitale.</p>
                    <div class="ag-article-cta__actions">
                        <a href="tel:+33623526074" class="ag-btn-gold">📞 Appeler maintenant — 06.23.52.60.74</a>
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

        <!-- Auteur / Crédibilité -->
        <section class="ag-author-box">
            <div class="ag-container ag-container--narrow">
                <div class="ag-author-box__inner">
                    <div class="ag-author-box__avatar">AG</div>
                    <div class="ag-author-box__content">
                        <span class="ag-author-box__label">Rédigé par</span>
                        <strong class="ag-author-box__name">L'équipe Alliance Groupe</strong>
                        <p class="ag-author-box__bio">Experts en création web, IA et stratégie digitale. Nous aidons les entreprises à transformer leur présence en ligne en machine à générer des leads.</p>
                        <a href="tel:+33623526074" class="ag-author-box__cta">Nous appeler →</a>
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
                    <span class="ag-cta-final__badge">🚀 Passez à l'action</span>
                    <h2 class="ag-cta-final__title">Votre entreprise mérite d'<em>exister en ligne</em></h2>
                    <p class="ag-cta-final__desc">Chaque jour sans stratégie digitale, c'est des clients qui vont chez vos concurrents. Discutons de votre projet — c'est gratuit et sans engagement.</p>
                    <div class="ag-cta-final__actions">
                        <a href="tel:+33623526074" class="ag-btn-gold">📞 06.23.52.60.74 — Appel gratuit</a>
                        <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline">✉️ contact@alliancegroupe-inc.com</a>
                    </div>
                    <p class="ag-cta-final__trust">✓ Diagnostic gratuit &nbsp; ✓ Sans engagement &nbsp; ✓ Réponse sous 24h</p>
                </div>
            </div>
        </section>

        <footer class="ag-article__footer">
            <div class="ag-container ag-container--narrow">
                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="ag-btn-outline">← Retour au blog</a>
            </div>
        </footer>
    </article>
</main>

<?php get_footer(); ?>
