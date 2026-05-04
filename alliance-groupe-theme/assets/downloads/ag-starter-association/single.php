<?php
/**
 * @package AG_Starter_Association
 */
get_header();
?>
<main id="main" class="ag-asso-section">
    <div class="ag-asso-container" style="max-width:780px;">
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <h1 class="ag-asso-section__title"><?php the_title(); ?></h1>
                <p class="ag-asso-actu__date" style="margin: 0 0 24px;"><?php echo get_the_date(); ?></p>
                <?php if ( has_post_thumbnail() ) : ?>
                    <div style="margin-bottom: 28px;"><?php the_post_thumbnail( 'large' ); ?></div>
                <?php endif; ?>
                <div class="ag-asso-manifeste">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer();
