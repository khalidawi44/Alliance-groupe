<?php get_header(); ?>
<main id="main" style="min-height:70vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 24px;">
    <div>
        <p style="font-size:6rem;font-weight:800;color:var(--gold);line-height:1;margin-bottom:16px;">404</p>
        <h1 style="font-size:1.8rem;color:#fff;margin-bottom:12px;"><?php esc_html_e( 'Page introuvable', 'ag-starter-barber' ); ?></h1>
        <p style="color:var(--text-muted);max-width:400px;margin:0 auto 32px;"><?php esc_html_e( 'Cette page n\'existe pas ou a été déplacée.', 'ag-starter-barber' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-btn ag-btn--gold"><?php esc_html_e( 'Retour à l\'accueil', 'ag-starter-barber' ); ?></a>
    </div>
</main>
<?php get_footer(); ?>
