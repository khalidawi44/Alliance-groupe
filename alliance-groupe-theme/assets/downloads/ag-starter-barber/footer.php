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

<script>
// Fix : detecte l'URL active et ajoute la classe 'current-menu-item' sur
// les items du menu qui matchent. Necessaire quand les items du menu
// sont en URL custom au lieu d'etre lies a un objet WP page.
// Le nav barber est une serie de <a> directs (pas de <li>) donc on cible
// les liens eux-memes en plus des <li> eventuels (menu WP).
(function () {
    var currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
    function match(a) {
        if (!a) return false;
        var aPath;
        try { aPath = new URL(a.href).pathname.replace(/\/+$/, '') || '/'; }
        catch (e) { aPath = (a.getAttribute('href') || '').replace(/\/+$/, '') || '/'; }
        return aPath === currentPath;
    }
    // <li> dans menu WP
    var lis = document.querySelectorAll('.ag-header__nav li');
    Array.prototype.forEach.call(lis, function (li) {
        var a = li.querySelector('a');
        if (match(a)) {
            li.classList.add('current-menu-item');
            li.classList.add('current_page_item');
        }
    });
    // <a> directs (cas du header barber sans <ul><li>)
    var anchors = document.querySelectorAll('.ag-header__nav > a');
    Array.prototype.forEach.call(anchors, function (a) {
        if (match(a)) {
            a.classList.add('current-menu-item');
            a.classList.add('current_page_item');
        }
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
