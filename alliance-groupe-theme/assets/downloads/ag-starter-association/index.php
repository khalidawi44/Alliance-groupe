<?php
/**
 * @package AG_Starter_Association
 */
get_header();
?>
<main id="main" class="ag-asso-section">
    <div class="ag-asso-container">
        <?php if ( have_posts() ) : ?>
            <h1 class="ag-asso-section__title"><?php single_post_title(); ?></h1>
            <div class="ag-asso-actu-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="ag-asso-actu">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="ag-asso-actu__img"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a></div>
                        <?php endif; ?>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="ag-asso-actu__date"><?php echo get_the_date(); ?></p>
                        <p><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'Aucun article pour le moment.', 'ag-starter-association' ); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer();
