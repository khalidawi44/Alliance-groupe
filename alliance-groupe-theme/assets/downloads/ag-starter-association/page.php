<?php
get_header();
?>
<main id="main" class="ag-asso-section">
    <div class="ag-asso-container" style="max-width:780px;">
        <?php while ( have_posts() ) : the_post(); ?>
            <h1 class="ag-asso-section__title"><?php the_title(); ?></h1>
            <div class="ag-asso-manifeste"><?php the_content(); ?></div>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer();
