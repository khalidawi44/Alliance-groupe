<footer class="ag-asso-footer">
    <aside class="ag-asso-promo-banner ag-asso-promo-banner--in-footer" aria-label="<?php esc_attr_e( 'Crédit thème', 'ag-starter-association' ); ?>">
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

<?php // Sticky pub a gauche : pilule verticale -> panneau au clic ?>
<aside class="ag-asso-sticky-pub" id="agAssoStickyPub" aria-label="<?php esc_attr_e( 'Crédit thème — Alliance Groupe', 'ag-starter-association' ); ?>">
    <button type="button" class="ag-asso-sticky-pub__toggle" aria-expanded="false" aria-controls="agAssoStickyPubPanel">
        <span class="ag-asso-sticky-pub__emoji" aria-hidden="true">🚀</span>
        <span class="ag-asso-sticky-pub__text">Template gratuit · Alliance Groupe</span>
    </button>
    <div class="ag-asso-sticky-pub__panel" id="agAssoStickyPubPanel" hidden>
        <button type="button" class="ag-asso-sticky-pub__close" aria-label="<?php esc_attr_e( 'Fermer', 'ag-starter-association' ); ?>" data-pub-close>×</button>
        <div class="ag-asso-sticky-pub__emojis" aria-hidden="true">
            <span style="--d:0s">🚀</span>
            <span style="--d:.3s">✨</span>
            <span style="--d:.6s">💎</span>
        </div>
        <h3 class="ag-asso-sticky-pub__title">Alliance Groupe</h3>
        <p class="ag-asso-sticky-pub__sub">Agence web &amp; IA française qui offre des templates WordPress 100% gratuits par métier.</p>
        <h4 class="ag-asso-sticky-pub__h4">Pourquoi ce template ?</h4>
        <p class="ag-asso-sticky-pub__sub">Les associations militantes ont rarement les budgets d'un développeur. On a donc conçu <strong>AG Starter Association</strong> avec toutes les fonctions premium (pages, calendrier, dons, AG en visio, espace adhérent…) — <strong style="color:#FFD23F;">offert sans contrepartie</strong> aux mouvements citoyens, syndicats et associations loi 1901.</p>
        <div class="ag-asso-sticky-pub__actions">
            <a href="https://alliancegroupe-inc.com/wordpress-association" target="_blank" rel="noopener" class="ag-asso-sticky-pub__cta-primary">⚡ Télécharger gratuitement</a>
            <a href="https://alliancegroupe-inc.com/templates-wordpress" target="_blank" rel="noopener" class="ag-asso-sticky-pub__cta-secondary">Découvrir les autres →</a>
        </div>
        <p class="ag-asso-sticky-pub__credit">🚀 Fièrement créé par <strong>Alliance Groupe</strong> 💎</p>
    </div>
</aside>
<script>
(function () {
    var pub = document.getElementById('agAssoStickyPub');
    if (!pub) return;
    var btn   = pub.querySelector('.ag-asso-sticky-pub__toggle');
    var panel = pub.querySelector('.ag-asso-sticky-pub__panel');
    function toggle(force) {
        var open = (typeof force === 'boolean') ? force : !pub.classList.contains('is-open');
        pub.classList.toggle('is-open', open);
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (panel) panel.hidden = !open;
    }
    if (btn) btn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggle();
    });
    pub.querySelectorAll('[data-pub-close]').forEach(function (b) {
        b.addEventListener('click', function (e) {
            e.stopPropagation();
            toggle(false);
        });
    });
    document.addEventListener('click', function (e) {
        if (!pub.contains(e.target) && pub.classList.contains('is-open')) toggle(false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && pub.classList.contains('is-open')) toggle(false);
    });
})();
</script>

<?php // Bouton retour haut de page (apparait apres 400px de scroll) ?>
<button type="button" class="ag-asso-backtop" id="agAssoBackTop" aria-label="<?php esc_attr_e( 'Retour en haut', 'ag-starter-association' ); ?>" hidden>
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
        <path d="M12 4 L12 20 M5 11 L12 4 L19 11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>

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
    }, 10000);
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

/* Header sticky scroll-aware : disparait au scroll bas, reapparait au scroll haut */
(function () {
    var header = document.querySelector('.ag-asso-header');
    if (!header) return;
    var lastY = window.scrollY;
    var ticking = false;
    function update() {
        var y = window.scrollY;
        if (y > lastY && y > 120) {
            header.classList.add('is-hidden');
        } else {
            header.classList.remove('is-hidden');
        }
        lastY = y;
        ticking = false;
    }
    window.addEventListener('scroll', function () {
        if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
})();

/* Bouton retour haut */
(function () {
    var btn = document.getElementById('agAssoBackTop');
    if (!btn) return;
    var ticking = false;
    function update() {
        if (window.scrollY > 400) { btn.hidden = false; btn.classList.add('is-visible'); }
        else { btn.classList.remove('is-visible'); }
        ticking = false;
    }
    window.addEventListener('scroll', function () {
        if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
