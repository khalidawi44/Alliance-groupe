<?php get_header(); ?>
<main id="main" class="ag-section">
    <div class="ag-container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article style="margin-bottom:40px;">
                <h2><a href="<?php the_permalink(); ?>" style="color:var(--gold);"><?php the_title(); ?></a></h2>
                <div style="color:var(--text-muted);"><?php the_excerpt(); ?></div>
            </article>
        <?php endwhile; else : ?>
            <p style="color:var(--text-muted);"><?php esc_html_e( 'Aucun contenu.', 'ag-starter-barber' ); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
