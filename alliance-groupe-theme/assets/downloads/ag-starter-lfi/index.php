<?php
/**
 * @package AG_Starter_LFI
 */
get_header();
?>
<main id="main" class="ag-lfi-section">
    <div class="ag-lfi-container">
        <?php if ( have_posts() ) : ?>
            <h1 class="ag-lfi-section__title"><?php single_post_title(); ?></h1>
            <div class="ag-lfi-actu-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="ag-lfi-actu">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="ag-lfi-actu__img"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a></div>
                        <?php endif; ?>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="ag-lfi-actu__date"><?php echo get_the_date(); ?></p>
                        <p><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'Aucun article pour le moment.', 'ag-starter-lfi' ); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer();
