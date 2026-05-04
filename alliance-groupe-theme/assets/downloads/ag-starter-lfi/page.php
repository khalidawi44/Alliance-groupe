<?php
get_header();
?>
<main id="main" class="ag-lfi-section">
    <div class="ag-lfi-container" style="max-width:780px;">
        <?php while ( have_posts() ) : the_post(); ?>
            <h1 class="ag-lfi-section__title"><?php the_title(); ?></h1>
            <div class="ag-lfi-manifeste"><?php the_content(); ?></div>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer();
