<?php $settings = AG_Barber_Queue::get_settings(); ?>
<footer class="ag-footer">
    <div class="ag-container">
        <div class="ag-footer__name"><?php echo esc_html( $settings['shop_name'] ); ?></div>
        <div class="ag-footer__addr">
            <?php echo esc_html( get_theme_mod( 'ag_barber_address', '12 Rue du Centre, 75001 Paris' ) ); ?><br>
            <?php echo esc_html( get_theme_mod( 'ag_barber_hours', 'Lun-Sam : 9h-20h | Dim : fermé' ) ); ?><br>
            <a href="tel:<?php echo esc_attr( get_theme_mod( 'ag_barber_phone', '+33612345678' ) ); ?>" style="color:#D4B45C;"><?php echo esc_html( get_theme_mod( 'ag_barber_phone', '06 12 34 56 78' ) ); ?></a>
        </div>
        <div class="ag-footer__copy">&copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( $settings['shop_name'] ); ?>. <?php printf( wp_kses( __( 'Thème par %s.', 'ag-starter-barber' ), array( 'a' => array( 'href' => array(), 'rel' => array() ) ) ), '<a href="https://alliancegroupe-inc.com" rel="nofollow">Alliance Groupe</a>' ); ?></div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
