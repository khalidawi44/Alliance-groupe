<?php get_header(); ?>

<section id="ag-main-content" style="background:#080808;min-height:80vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 24px;">
    <div>
        <p style="font-size:clamp(6rem,15vw,10rem);font-weight:800;color:#D4B45C;line-height:1;margin-bottom:16px;">404</p>
        <h1 style="font-size:clamp(1.6rem,4vw,2.4rem);color:#fff;margin-bottom:12px;">Page introuvable</h1>
        <p style="font-size:1.1rem;color:rgba(255,255,255,.6);max-width:480px;margin:0 auto 40px;">La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ag-btn-gold">Retour à l'accueil</a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Nous contacter</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
