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
    </div>
</footer>

<?php // Sticky pub a gauche, suit le scroll, desktop seulement (>1100px) ?>
<aside class="ag-asso-sticky-pub" aria-label="<?php esc_attr_e( 'Crédit thème', 'ag-starter-association' ); ?>">
    <a href="https://alliancegroupe-inc.com/wordpress-association" target="_blank" rel="noopener">
        <span class="ag-asso-sticky-pub__emoji" aria-hidden="true">🚀</span>
        <span class="ag-asso-sticky-pub__text">
            <strong>Template gratuit</strong>
            <em>par Alliance Groupe</em>
        </span>
        <span class="ag-asso-sticky-pub__cta">→</span>
    </a>
</aside>

<?php // Banner discrete avant footer + popup auto vers Alliance Groupe ?>
<aside class="ag-asso-promo-banner" aria-label="<?php esc_attr_e( 'Crédit thème', 'ag-starter-association' ); ?>">
    <div class="ag-asso-promo-banner__inner">
        <span class="ag-asso-promo-banner__emoji" aria-hidden="true">🚀</span>
        <span class="ag-asso-promo-banner__text">
            Site militant créé avec le template gratuit
            <strong>AG Starter Association</strong>
            d'Alliance Groupe.
        </span>
        <a href="https://alliancegroupe-inc.com/templates-wordpress" target="_blank" rel="noopener" class="ag-asso-promo-banner__cta">
            Découvrir nos templates →
        </a>
    </div>
</aside>

<?php // Popup d'accueil — affichage 1 fois par session ?>
<div class="ag-asso-welcome" id="agAssoWelcome" role="dialog" aria-modal="true" aria-labelledby="agAssoWelcomeTitle" hidden>
    <div class="ag-asso-welcome__backdrop" data-close></div>
    <div class="ag-asso-welcome__box">
        <button type="button" class="ag-asso-welcome__close" data-close aria-label="<?php esc_attr_e( 'Fermer', 'ag-starter-association' ); ?>">×</button>
        <div class="ag-asso-welcome__emojis" aria-hidden="true">
            <span style="--d:0s">🚀</span>
            <span style="--d:.3s">✨</span>
            <span style="--d:.6s">💎</span>
            <span style="--d:.9s">🎯</span>
            <span style="--d:1.2s">🤝</span>
        </div>
        <h2 id="agAssoWelcomeTitle" class="ag-asso-welcome__title">Vous aimez ce site ?</h2>
        <p class="ag-asso-welcome__sub">
            Ce site est construit avec <strong>AG Starter Association</strong>, un template
            WordPress <span class="ag-asso-welcome__hl">100% gratuit</span> conçu par
            <strong>Alliance Groupe</strong> pour les associations militantes, syndicats et
            mouvements citoyens.
        </p>
        <p class="ag-asso-welcome__sub">
            Lancez votre site asso en 10 minutes avec le même thème : pages, calendrier
            événements, dons, adhérents, dons, manifestes — tout est déjà inclus.
        </p>
        <div class="ag-asso-welcome__actions">
            <a href="https://alliancegroupe-inc.com/wordpress-association" target="_blank" rel="noopener" class="ag-asso-welcome__cta">
                ⚡ Télécharger le template gratuit
            </a>
            <button type="button" class="ag-asso-welcome__skip" data-close>Plus tard</button>
        </div>
        <p class="ag-asso-welcome__credit">🚀 Fièrement créé par <strong>Alliance Groupe</strong> 💎</p>
    </div>
</div>

<script>
(function () {
    var key = 'agAssoWelcomeSeen';
    try {
        if (sessionStorage.getItem(key)) return;
    } catch (e) {}
    var pop = document.getElementById('agAssoWelcome');
    if (!pop) return;
    setTimeout(function () {
        pop.hidden = false;
        document.body.classList.add('ag-asso-welcome-open');
    }, 1500);
    function close() {
        pop.hidden = true;
        document.body.classList.remove('ag-asso-welcome-open');
        try { sessionStorage.setItem(key, '1'); } catch (e) {}
    }
    pop.querySelectorAll('[data-close]').forEach(function (b) {
        b.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !pop.hidden) close();
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
