<footer class="ag-lfi-footer">
    <div class="ag-lfi-footer__inner">
        <div class="ag-lfi-footer__col">
            <div class="ag-lfi-footer__name"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_name', '[Mouvement]' ) ); ?></div>
            <p class="ag-lfi-footer__slogan"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_slogan', '[Slogan]' ) ); ?></p>
        </div>
        <div class="ag-lfi-footer__col">
            <h4><?php esc_html_e( 'Contact', 'ag-starter-lfi' ); ?></h4>
            <p><?php echo esc_html( ag_lfi_opt( 'ag_lfi_address', '[Adresse]' ) ); ?></p>
            <p>
                <a href="mailto:<?php echo esc_attr( ag_lfi_opt( 'ag_lfi_email', '' ) ); ?>"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_email', '[email]' ) ); ?></a><br>
                <a href="tel:<?php echo esc_attr( ag_lfi_opt( 'ag_lfi_phone', '' ) ); ?>"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_phone', '[téléphone]' ) ); ?></a>
            </p>
        </div>
        <div class="ag-lfi-footer__col">
            <h4><?php esc_html_e( 'Liens', 'ag-starter-lfi' ); ?></h4>
            <ul>
                <li><a href="#manifeste"><?php esc_html_e( 'Le manifeste', 'ag-starter-lfi' ); ?></a></li>
                <li><a href="#groupes"><?php esc_html_e( 'Trouver mon groupe', 'ag-starter-lfi' ); ?></a></li>
                <li><a href="#don"><?php esc_html_e( 'Faire un don', 'ag-starter-lfi' ); ?></a></li>
                <li><a href="#"><?php esc_html_e( 'Mentions légales', 'ag-starter-lfi' ); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="ag-lfi-footer__copy">
        &copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( ag_lfi_opt( 'ag_lfi_name', '[Mouvement]' ) ); ?>.
        <?php esc_html_e( 'Thème par', 'ag-starter-lfi' ); ?> <a href="https://alliancegroupe-inc.com" rel="nofollow">Alliance Groupe</a>.
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
