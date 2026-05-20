<?php get_header(); ?>

<?php
/**
 * Articles (blog) listing — refonte : intro + filtres par categorie + cartes
 * avec images (featured si dispo, sinon image Unsplash par categorie).
 */

// Images de fallback par categorie (rotation pour varier les cartes).
$ag_cat_images = array(
    'Tech & IA' => array(
        'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=800&q=80',
        'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80',
        'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=800&q=80',
    ),
    'Conseils Digital' => array(
        'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
        'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&q=80',
        'https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?w=800&q=80',
    ),
);
$ag_cat_default = 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=800&q=80';

// Recupere les categories qui ont des articles
$ag_cats = get_categories( array( 'hide_empty' => true ) );
?>

<main class="ag-blog" id="ag-main-content">
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Le blog Alliance Groupe</span>
            <h1 class="ag-section__title ag-anim" data-anim="title">Nos derniers <em>articles</em></h1>
            <p class="ag-section__desc ag-anim" data-anim="desc">Conseils, tendances et retours d'expérience sur le web, l'IA, le SEO et le marketing digital pour faire grandir votre activité.</p>

            <?php if ( ! empty( $ag_cats ) ) : ?>
            <div class="ag-blog-filters" role="tablist" aria-label="Filtrer par catégorie">
                <button class="ag-blog-filter is-active" type="button" data-filter="all">Tous</button>
                <?php foreach ( $ag_cats as $cat ) : ?>
                <button class="ag-blog-filter" type="button" data-filter="cat-<?php echo (int) $cat->term_id; ?>">
                    <?php echo esc_html( $cat->name ); ?>
                    <span class="ag-blog-filter__count"><?php echo (int) $cat->count; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (have_posts()) : ?>
            <div class="ag-blog__grid" id="ag-blog-grid">
                <?php
                $ag_i = 0;
                while (have_posts()) : the_post();
                    $post_cats = get_the_category();
                    $cat_main  = $post_cats ? $post_cats[0] : null;
                    $cat_name  = $cat_main ? $cat_main->name : '';
                    // Classes categorie (pour le filtre JS)
                    $cat_classes = '';
                    foreach ( $post_cats as $pc ) { $cat_classes .= ' cat-' . (int) $pc->term_id; }

                    // Image : featured sinon fallback Unsplash par categorie
                    if ( has_post_thumbnail() ) {
                        $img_url = get_the_post_thumbnail_url( null, 'medium_large' );
                    } elseif ( isset( $ag_cat_images[ $cat_name ] ) ) {
                        $set = $ag_cat_images[ $cat_name ];
                        $img_url = $set[ $ag_i % count( $set ) ];
                    } else {
                        $img_url = $ag_cat_default;
                    }
                ?>
                <article class="ag-blog-card ag-anim<?php echo esc_attr( $cat_classes ); ?>" data-anim="card">
                    <a href="<?php the_permalink(); ?>" class="ag-blog-card__img" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                        <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" width="800" height="450">
                        <?php if ( $cat_name ) : ?>
                        <span class="ag-blog-card__badge"><?php echo esc_html( $cat_name ); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="ag-blog-card__body">
                        <div class="ag-blog-card__meta">
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('d M Y'); ?></time>
                            <span class="ag-blog-card__read">⏱ <?php echo ag_reading_time(); ?> min</span>
                        </div>
                        <h2 class="ag-blog-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <p class="ag-blog-card__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                        <a href="<?php the_permalink(); ?>" class="ag-blog-card__link">Lire l'article →</a>
                    </div>
                </article>
                <?php $ag_i++; endwhile; ?>
            </div>

            <p class="ag-blog__noresult" id="ag-blog-noresult" hidden>Aucun article dans cette catégorie pour le moment.</p>

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

<script>
(function(){
    var filters = document.querySelectorAll('.ag-blog-filter');
    var cards   = document.querySelectorAll('#ag-blog-grid .ag-blog-card');
    var noResult = document.getElementById('ag-blog-noresult');
    if (!filters.length) return;
    filters.forEach(function(btn){
        btn.addEventListener('click', function(){
            var f = btn.getAttribute('data-filter');
            filters.forEach(function(b){ b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            var visible = 0;
            cards.forEach(function(card){
                var show = (f === 'all') || card.classList.contains(f);
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (noResult) noResult.hidden = visible > 0;
        });
    });
})();
</script>

<?php get_footer(); ?>
