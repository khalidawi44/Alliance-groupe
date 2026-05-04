<footer class="ag-asso-footer">
    <div class="ag-asso-footer__inner">
        <div class="ag-asso-footer__col">
            <div class="ag-asso-footer__name"><?php echo esc_html( ag_asso_opt( 'ag_asso_name', '[Mouvement]' ) ); ?></div>
            <p class="ag-asso-footer__slogan"><?php echo esc_html( ag_asso_opt( 'ag_asso_slogan', '[Slogan]' ) ); ?></p>
        </div>
        <div class="ag-asso-footer__col">
            <h4><?php esc_html_e( 'Contact', 'ag-starter-association' ); ?></h4>
            <p><?php echo esc_html( ag_asso_opt( 'ag_asso_address', '[Adresse]' ) ); ?></p>
            <p>
                <a href="mailto:<?php echo esc_attr( ag_asso_opt( 'ag_asso_email', '' ) ); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_email', '[email]' ) ); ?></a><br>
                <a href="tel:<?php echo esc_attr( ag_asso_opt( 'ag_asso_phone', '' ) ); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_phone', '[téléphone]' ) ); ?></a>
            </p>
        </div>
        <div class="ag-asso-footer__col">
            <h4><?php esc_html_e( 'Liens', 'ag-starter-association' ); ?></h4>
            <ul>
                <li><a href="<?php echo esc_url( ag_asso_link( 'manifeste' ) ); ?>"><?php esc_html_e( 'Le manifeste', 'ag-starter-association' ); ?></a></li>
                <li><a href="<?php echo esc_url( ag_asso_link( 'groupes' ) ); ?>"><?php esc_html_e( 'Trouver mon groupe', 'ag-starter-association' ); ?></a></li>
                <li><a href="<?php echo esc_url( ag_asso_link( 'don' ) ); ?>"><?php esc_html_e( 'Faire un don', 'ag-starter-association' ); ?></a></li>
                <li><a href="<?php echo esc_url( ag_asso_link( 'mentions' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'ag-starter-association' ); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="ag-asso-footer__copy">
        &copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( ag_asso_opt( 'ag_asso_name', '[Mouvement]' ) ); ?>.
        <?php esc_html_e( 'Thème par', 'ag-starter-association' ); ?> <a href="https://alliancegroupe-inc.com" rel="nofollow">Alliance Groupe</a>.
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
