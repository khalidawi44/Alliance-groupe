<?php
get_header();
?>
<main id="main" class="ag-asso-section">
    <div class="ag-asso-container" style="text-align:center;">
        <h1 class="ag-asso-section__title" style="font-size:5rem;">404</h1>
        <p class="ag-asso-section__lead" style="margin:0 auto 24px;"><?php esc_html_e( 'Page introuvable.', 'ag-starter-association' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-asso-btn ag-asso-btn--primary"><?php esc_html_e( "Retour à l'accueil", 'ag-starter-association' ); ?></a>
    </div>
</main>
<?php get_footer();
