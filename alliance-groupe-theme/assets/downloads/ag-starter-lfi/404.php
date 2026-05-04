<?php
get_header();
?>
<main id="main" class="ag-lfi-section">
    <div class="ag-lfi-container" style="text-align:center;">
        <h1 class="ag-lfi-section__title" style="font-size:5rem;">404</h1>
        <p class="ag-lfi-section__lead" style="margin:0 auto 24px;"><?php esc_html_e( 'Page introuvable.', 'ag-starter-lfi' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-lfi-btn ag-lfi-btn--primary"><?php esc_html_e( "Retour à l'accueil", 'ag-starter-lfi' ); ?></a>
    </div>
</main>
<?php get_footer();
