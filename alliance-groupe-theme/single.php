<?php get_header(); ?>

<main class="ag-single">
    <article class="ag-article">
        <header class="ag-article__header" style="background:#0c0c0f;">
            <div class="ag-container">
                <div class="ag-article__meta">
                    <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('d M Y'); ?></time>
                    <?php
                    $cats = get_the_category();
                    if ($cats) {
                        echo '<span class="ag-article__cat">' . esc_html($cats[0]->name) . '</span>';
                    }
                    ?>
                </div>
                <h1 class="ag-article__title"><?php the_title(); ?></h1>
            </div>
        </header>

        <?php if (has_post_thumbnail()) : ?>
        <div class="ag-article__featured">
            <div class="ag-container">
                <?php the_post_thumbnail('large'); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="ag-article__content">
            <div class="ag-container ag-container--narrow">
                <?php the_content(); ?>
            </div>
        </div>

        <footer class="ag-article__footer">
            <div class="ag-container ag-container--narrow">
                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="ag-btn-outline">← Retour au blog</a>
            </div>
        </footer>
    </article>
</main>

<?php get_footer(); ?>
