<?php get_header(); ?>

<main class="ag-blog">
    <section class="ag-section" style="background:#0c0c0f;">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Blog</span>
            <h1 class="ag-section__title ag-anim" data-anim="title">Nos derniers <em>articles</em></h1>
            <p class="ag-section__desc ag-anim" data-anim="desc">Conseils, tendances et retours d'expérience sur le web, l'IA et le marketing digital.</p>

            <?php if (have_posts()) : ?>
            <div class="ag-blog__grid">
                <?php while (have_posts()) : the_post(); ?>
                <article class="ag-blog-card ag-anim" data-anim="card">
                    <?php if (has_post_thumbnail()) : ?>
                    <a href="<?php the_permalink(); ?>" class="ag-blog-card__img">
                        <?php the_post_thumbnail('medium_large'); ?>
                    </a>
                    <?php endif; ?>
                    <div class="ag-blog-card__body">
                        <div class="ag-blog-card__meta">
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('d M Y'); ?></time>
                            <?php
                            $cats = get_the_category();
                            if ($cats) {
                                echo '<span class="ag-blog-card__cat">' . esc_html($cats[0]->name) . '</span>';
                            }
                            ?>
                        </div>
                        <h2 class="ag-blog-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <p class="ag-blog-card__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 22); ?></p>
                        <a href="<?php the_permalink(); ?>" class="ag-blog-card__link">Lire l'article →</a>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>

            <div class="ag-blog__pagination">
                <?php the_posts_pagination([
                    'mid_size'  => 1,
                    'prev_text' => '←',
                    'next_text' => '→',
                ]); ?>
            </div>
            <?php else : ?>
            <p class="ag-blog__empty">Aucun article pour le moment.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
