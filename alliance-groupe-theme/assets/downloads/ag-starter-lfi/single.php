<?php
/**
 * @package AG_Starter_LFI
 */
get_header();
?>
<main id="main" class="ag-lfi-section">
    <div class="ag-lfi-container" style="max-width:780px;">
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <h1 class="ag-lfi-section__title"><?php the_title(); ?></h1>
                <p class="ag-lfi-actu__date" style="margin: 0 0 24px;"><?php echo get_the_date(); ?></p>
                <?php if ( has_post_thumbnail() ) : ?>
                    <div style="margin-bottom: 28px;"><?php the_post_thumbnail( 'large' ); ?></div>
                <?php endif; ?>
                <div class="ag-lfi-manifeste">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer();
