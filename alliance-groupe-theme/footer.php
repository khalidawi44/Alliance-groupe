<footer class="ag-footer">
    <div class="ag-footer__inner">
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Alliance Groupe</h4>
            <p class="ag-footer__text">Agence Web & IA basée en France. Nous transformons votre présence digitale en machine à générer des leads.</p>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Services</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/service-creation-web')); ?>">Création Web</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-ia')); ?>">IA & Automatisation</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-seo')); ?>">SEO</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-publicite')); ?>">Publicité</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-branding')); ?>">Branding</a></li>
                <li><a href="<?php echo esc_url(home_url('/service-conseil')); ?>">Conseil</a></li>
            </ul>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Liens</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/realisations')); ?>">Réalisations</a></li>
                <li><a href="<?php echo esc_url(home_url('/a-propos')); ?>">À propos</a></li>
                <li><a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a></li>
                <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a></li>
            </ul>
        </div>
        <div class="ag-footer__col">
            <h4 class="ag-footer__title">Contact</h4>
            <ul>
                <li><a href="tel:+33623526074">06.23.52.60.74</a></li>
                <li><a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a></li>
            </ul>
        </div>
    </div>
    <div class="ag-footer__bottom">
        <p>&copy; <?php echo date('Y'); ?> Alliance Groupe. Tous droits réservés.</p>
    </div>
</footer>

<!-- Back to top — APRÈS </footer> -->
<button class="ag-totop" id="ag-totop" aria-label="Retour en haut">↑</button>

<?php wp_footer(); ?>
</body>
</html>
