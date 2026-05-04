<footer class="ag-asso-footer">
    <div class="ag-asso-footer__inner">
        <div class="ag-asso-footer__col">
            <?php if ( $logo_footer = ag_asso_opt( 'ag_asso_logo_footer', '' ) ) : ?>
                <img class="ag-asso-footer__logo" src="<?php echo esc_url( $logo_footer ); ?>" alt="<?php echo esc_attr( ag_asso_opt( 'ag_asso_name', '' ) ); ?>">
            <?php endif; ?>
            <div class="ag-asso-footer__name"><?php echo esc_html( ag_asso_opt( 'ag_asso_name', '[Mouvement]' ) ); ?></div>
            <p class="ag-asso-footer__slogan"><?php echo esc_html( ag_asso_opt( 'ag_asso_baseline', ag_asso_opt( 'ag_asso_slogan', '[Slogan]' ) ) ); ?></p>
            <?php
            $socials = array(
                'facebook'  => 'Facebook',
                'twitter'   => 'X / Twitter',
                'instagram' => 'Instagram',
                'youtube'   => 'YouTube',
                'tiktok'    => 'TikTok',
                'telegram'  => 'Telegram',
                'whatsapp'  => 'WhatsApp',
                'linkedin'  => 'LinkedIn',
                'mastodon'  => 'Mastodon',
            );
            $has_social = false;
            foreach ( $socials as $key => $label ) {
                if ( ag_asso_opt( 'ag_asso_social_' . $key, '' ) ) { $has_social = true; break; }
            }
            if ( $has_social ) : ?>
                <ul class="ag-asso-social">
                    <?php foreach ( $socials as $key => $label ) :
                        $url = ag_asso_opt( 'ag_asso_social_' . $key, '' );
                        if ( ! $url ) continue; ?>
                        <li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $label ); ?>" class="ag-asso-social__<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
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
            <?php if ( has_nav_menu( 'footer' ) ) : ?>
                <?php wp_nav_menu( array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'ag-asso-footer__menu',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ) ); ?>
            <?php else : ?>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/manifeste/' ) ); ?>"><?php esc_html_e( 'Le manifeste', 'ag-starter-association' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/groupes/' ) ); ?>"><?php esc_html_e( 'Trouver mon groupe', 'ag-starter-association' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/don/' ) ); ?>"><?php esc_html_e( 'Faire un don', 'ag-starter-association' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/mentions/' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'ag-starter-association' ); ?></a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="ag-asso-footer__copy">
        &copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( ag_asso_opt( 'ag_asso_name', '[Mouvement]' ) ); ?>.
        <?php if ( $copy = ag_asso_opt( 'ag_asso_footer_copy', '' ) ) : ?>
            <span class="ag-asso-footer__phrase"><?php echo esc_html( $copy ); ?></span>
        <?php endif; ?>
        <?php if ( ag_asso_opt( 'ag_asso_show_credit', 1 ) ) : ?>
            <?php esc_html_e( 'Thème par', 'ag-starter-association' ); ?> <a href="https://alliancegroupe-inc.com" rel="nofollow">Alliance Groupe</a>.
        <?php endif; ?>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
