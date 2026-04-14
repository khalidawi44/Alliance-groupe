<?php
/**
 * Template Name: Templates WordPress
 *
 * HUB : page courte qui oriente vers les 4 pages métier dédiées.
 * Les fiches complètes (features, Pro/Premium/Business, config,
 * installation) vivent sur les pages individuelles :
 *   /wordpress-avocat
 *   /wordpress-restaurant
 *   /wordpress-artisan
 *   /wordpress-coach
 */
get_header();

$dl_base = get_stylesheet_directory_uri() . '/assets/downloads/';

// Stripe URLs (still loaded for the companion plugin CTA + compatibility).
$ag_stripe_placeholder = 'STRIPE_PLACEHOLDER';
$ag_stripe_pro      = get_option( 'ag_stripe_pro_url', $ag_stripe_placeholder );
$ag_stripe_premium  = get_option( 'ag_stripe_premium_url', $ag_stripe_placeholder );
$ag_stripe_business = get_option( 'ag_stripe_business_url', $ag_stripe_placeholder );

// 4 métiers → chaque entrée mappe vers sa page dédiée.
$ag_hub_metiers = array(
    array(
        'slug'     => 'avocat',
        'icon'     => '⚖️',
        'name'     => 'Avocat',
        'palette'  => 'Navy &amp; champagne',
        'audience' => 'Cabinet d\'avocats, juriste, notaire, conseil juridique.',
        'tagline'  => 'CPT Domaines, formulaire RDV RGPD, honoraires transparents.',
        'url'      => home_url( '/wordpress-avocat' ),
        'is_new'   => true,
    ),
    array(
        'slug'     => 'restaurant',
        'icon'     => '🍽️',
        'name'     => 'Restaurant',
        'palette'  => 'Or &amp; noir',
        'audience' => 'Bistrot, bar, café, restaurant gastronomique.',
        'tagline'  => 'Hero, carte, réservation, privatisation, horaires.',
        'url'      => home_url( '/wordpress-restaurant' ),
        'is_new'   => false,
    ),
    array(
        'slug'     => 'artisan',
        'icon'     => '🔨',
        'name'     => 'Artisan',
        'palette'  => 'Bronze &amp; noir',
        'audience' => 'Plombier, électricien, menuisier, maçon, BTP.',
        'tagline'  => 'Prestations, zones d\'intervention, devis, réalisations.',
        'url'      => home_url( '/wordpress-artisan' ),
        'is_new'   => false,
    ),
    array(
        'slug'     => 'coach',
        'icon'     => '💼',
        'name'     => 'Coach',
        'palette'  => 'Bleu teal &amp; marine',
        'audience' => 'Coach, consultant, formateur, thérapeute.',
        'tagline'  => 'Services, séances, témoignages, prise de rendez-vous.',
        'url'      => home_url( '/wordpress-coach' ),
        'is_new'   => false,
    ),
);
?>

<main id="ag-main-content">

    <!-- Hero hub -->
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag">Templates WordPress</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Templates WordPress <em>gratuits</em></span>
                <span class="ag-line">100% français, prêts à installer</span>
            </h1>
            <p class="ag-hero__sub">Quatre thèmes pensés par métier : avocat, restaurant, artisan, coach. Choisissez celui qui vous correspond pour voir tous les détails, télécharger et passer à l'action.</p>
        </div>
    </section>

    <!-- 4 fiches métier -->
    <section class="ag-section ag-section--graphite" id="ag-metiers">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Choisissez votre métier</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Votre <em>métier</em>, votre thème</h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Chaque fiche vous conduit vers une page dédiée avec le descriptif complet, le configurateur de pack, les CTA d'achat et les instructions d'installation.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;max-width:1200px;margin:48px auto 0;">
                <?php foreach ( $ag_hub_metiers as $m ) : ?>
                <a href="<?php echo esc_url( $m['url'] ); ?>" class="ag-anim" data-anim="card" style="display:flex;flex-direction:column;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:28px;text-decoration:none;transition:border-color .3s,transform .3s,box-shadow .3s;color:inherit;">
                    <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:18px;">
                        <span style="font-size:2.4rem;line-height:1;"><?php echo $m['icon']; // phpcs:ignore ?></span>
                        <div style="flex:1;">
                            <h3 style="color:#fff;font-size:1.3rem;margin:0 0 4px;">
                                <?php echo esc_html( $m['name'] ); ?>
                                <?php if ( $m['is_new'] ) : ?>
                                    <span style="display:inline-block;margin-left:6px;padding:2px 10px;background:#28a745;color:#fff;font-size:.7rem;font-weight:700;border-radius:100px;text-transform:uppercase;letter-spacing:.5px;vertical-align:middle;">Nouveau</span>
                                <?php endif; ?>
                            </h3>
                            <span style="color:#888;font-size:.82rem;"><?php echo $m['palette']; // phpcs:ignore ?></span>
                        </div>
                    </div>
                    <p style="color:#e8e6e0;font-size:.92rem;line-height:1.55;margin:0 0 8px;"><strong><?php echo esc_html( $m['audience'] ); ?></strong></p>
                    <p style="color:#b0b0bc;font-size:.88rem;line-height:1.55;margin:0 0 auto;"><?php echo esc_html( $m['tagline'] ); ?></p>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:22px;padding-top:16px;border-top:1px solid rgba(212,180,92,.15);">
                        <span style="color:#D4B45C;font-weight:700;font-size:.92rem;">Voir la fiche →</span>
                        <span style="color:#28a745;font-size:.78rem;font-weight:700;">Gratuit</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Vue d'ensemble des 3 packs payants (courte, le détail est dans les pages métier) -->
    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">3 niveaux d'amélioration</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Passez au <em>niveau supérieur</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Les 4 templates gratuits sont volontairement basiques. Trois packs payants viennent compléter <strong style="color:#e8e6e0;">n'importe lequel des 4 thèmes</strong> — un seul achat, il fonctionne avec le thème actif. Le détail de chaque pack vit sur la page métier correspondante.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;max-width:1000px;margin:40px auto 0;">
                <div style="padding:24px;background:rgba(212,180,92,.05);border:1px solid rgba(212,180,92,.25);border-radius:12px;text-align:center;">
                    <div style="font-size:2rem;margin-bottom:6px;">⚡</div>
                    <strong style="display:block;color:#D4B45C;font-size:1.1rem;margin-bottom:6px;">Pack Pro — 49€</strong>
                    <p style="color:#b0b0bc;font-size:.88rem;line-height:1.55;margin:0;">Design travaillé, animations, blocs Gutenberg premium, customizer étendu, sticky header, polices Google Fonts, support 60j.</p>
                </div>
                <div style="padding:24px;background:rgba(212,180,92,.08);border:2px solid rgba(212,180,92,.4);border-radius:12px;text-align:center;position:relative;">
                    <span style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#D4B45C;color:#080808;font-size:.68rem;font-weight:700;padding:3px 12px;border-radius:100px;text-transform:uppercase;letter-spacing:1px;">Populaire</span>
                    <div style="font-size:2rem;margin-bottom:6px;">🌍</div>
                    <strong style="display:block;color:#D4B45C;font-size:1.1rem;margin-bottom:6px;">Pack Premium — 99€</strong>
                    <p style="color:#b0b0bc;font-size:.88rem;line-height:1.55;margin:0;">Tout Pro + multi-langue 6 langues + WooCommerce + espace client + support prioritaire 12 mois + mises à jour à vie.</p>
                </div>
                <div style="padding:24px;background:rgba(212,180,92,.10);border:2px solid rgba(212,180,92,.5);border-radius:12px;text-align:center;">
                    <div style="font-size:2rem;margin-bottom:6px;">💼</div>
                    <strong style="display:block;color:#D4B45C;font-size:1.1rem;margin-bottom:6px;">Pack Business — 149€</strong>
                    <p style="color:#b0b0bc;font-size:.88rem;line-height:1.55;margin:0;">Tout Premium + installation visio 1h + maintenance 1 an + audit SEO + white-label + intégration CRM + appel Fabrizio.</p>
                </div>
            </div>

            <p style="text-align:center;color:#888;font-size:.88rem;margin-top:32px;font-style:italic;">
                👆 Pour les features détaillées par métier et les boutons d'achat, cliquez sur la fiche métier qui vous correspond.
            </p>
        </div>
    </section>

    <!-- 🚨 Avertissement sur-mesure -->
    <section class="ag-section ag-section--or" style="padding:80px 0;">
        <div class="ag-container">
            <div style="max-width:900px;margin:0 auto;padding:40px 36px;background:linear-gradient(135deg,rgba(212,180,92,.12) 0%,rgba(20,20,22,.6) 100%);border:2px solid rgba(212,180,92,.4);border-radius:20px;text-align:center;position:relative;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#D4B45C,transparent);"></div>
                <span style="display:inline-block;padding:6px 18px;background:rgba(212,180,92,.2);border:1px solid rgba(212,180,92,.5);border-radius:100px;color:#D4B45C;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:14px;">💎 Et si vous alliez plus loin ?</span>
                <h2 style="font-size:clamp(1.5rem,3vw,2rem);margin-bottom:12px;line-height:1.2;">Un template ne remplacera <em>jamais</em> un site sur-mesure</h2>
                <p style="font-size:1rem;color:#e8e6e0;max-width:720px;margin:0 auto 24px;line-height:1.7;">
                    Si votre business compte vraiment — restaurant en ville touristique, cabinet qui veut dominer sa zone, coach qui veut remplir son agenda, avocat qui veut gagner ses gros dossiers — un site sur-mesure conçu par notre équipe va beaucoup plus loin.
                    <strong style="color:#D4B45C;">+340% de leads en moyenne en 3 mois.</strong>
                </p>
                <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                    <a href="tel:+33623526074" class="ag-btn-gold">📞 Appeler Fabrizio</a>
                    <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-btn-outline">Réserver un appel gratuit →</a>
                </div>
                <p style="color:#888;font-size:.82rem;margin-top:16px;font-style:italic;">Premier appel 30 min gratuit, sans engagement.</p>
            </div>
        </div>
    </section>

    <!-- 🎁 Plugin compagnon bonus -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div style="max-width:900px;margin:0 auto;padding:40px;background:linear-gradient(135deg,rgba(40,167,69,.08),rgba(40,167,69,.02));border:1px solid rgba(40,167,69,.3);border-radius:16px;text-align:center;">
                <span class="ag-tag" style="background:rgba(40,167,69,.15);color:#28a745;border-color:rgba(40,167,69,.3);">Bonus gratuit — Plugin compagnon</span>
                <h3 style="font-size:1.4rem;margin:12px 0 8px;">Installation en 1 clic avec <em>AG Starter Companion</em></h3>
                <p style="color:#b0b0bc;max-width:680px;margin:0 auto 20px;font-size:.95rem;line-height:1.7;">
                    Le plugin gratuit qui crée automatiquement les pages, le menu, la page d'accueil et les permaliens quand vous activez un thème AG Starter. Compatible avec les 4 thèmes.
                    Pour Avocat, il crée aussi 6 Domaines d'expertise préremplis.
                </p>
                <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="companion" data-file="<?php echo esc_url($dl_base . 'ag-starter-companion.zip'); ?>">⚡ Télécharger le plugin gratuit →</button>
                <p style="color:#888;font-size:.82rem;margin-top:14px;">100% gratuit, zéro limite. Détecte automatiquement le thème actif.</p>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="ag-section ag-section--cendre">
        <div class="ag-container">
            <h2 class="ag-section__title ag-anim" data-anim="title" style="text-align:center;">Questions <em>fréquentes</em></h2>
            <div class="ag-faq__list">
                <?php
                $tpl_faqs = [
                    ['q' => 'Comment ça marche exactement ?', 'a' => 'Choisissez votre métier sur cette page, vous arrivez sur la fiche dédiée. Vous y trouvez la description complète du thème, le configurateur pour choisir votre niveau (Gratuit / Pro / Premium / Business), le bouton de téléchargement ou d\'achat, et les instructions d\'installation.'],
                    ['q' => 'Les templates sont-ils vraiment en français ?', 'a' => '100% français natif. Tous les textes, titres, horaires, exemples et messages sont déjà rédigés en français — pas de Lorem ipsum, pas de strings anglaises à traduire. Vous remplacez juste les éléments entre crochets.'],
                    ['q' => 'Le plugin compagnon est-il obligatoire ?', 'a' => 'Non, mais il rend l\'installation 10x plus rapide. Sans le plugin, vous devez créer manuellement les 5 pages et le menu. Avec le plugin, un seul clic suffit. Il est gratuit et compatible avec les 4 thèmes.'],
                    ['q' => 'Un pack Pro marche-t-il avec les 4 thèmes ?', 'a' => 'Oui. Vous achetez UN seul plugin (Pro, Premium ou Business) et il fonctionne avec n\'importe quel thème AG Starter que vous avez activé. Le plugin détecte automatiquement le thème actif et adapte ses features.'],
                    ['q' => 'Les templates sont-ils sur WordPress.org ?', 'a' => 'En cours de soumission. Nos 4 thèmes et le plugin compagnon respectent les standards WordPress.org (GPL v2+, translation-ready, escaping strict, Theme Check compatible). Une fois validés, ils seront installables directement depuis votre admin WordPress.'],
                    ['q' => 'Et si je veux un vrai site sur-mesure ?', 'a' => 'Contactez-nous au 06.23.52.60.74 ou via la page contact. Premier appel de 30 min gratuit avec Fabrizio, sans engagement. Nos clients génèrent +340% de leads en moyenne avec un site sur-mesure vs un template.'],
                ];
                foreach ($tpl_faqs as $faq) :
                ?>
                <div class="ag-faq-item ag-anim" data-anim="faq-item">
                    <button class="ag-faq-q" type="button">
                        <span><?php echo esc_html($faq['q']); ?></span>
                        <span class="ag-faq-icon">+</span>
                    </button>
                    <div class="ag-faq-a">
                        <p><?php echo esc_html($faq['a']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</main>

<!-- Modal capture email (partagée pour le plugin compagnon download) -->
<div class="ag-dl-modal" id="ag-dl-modal">
    <div class="ag-dl-modal__overlay" id="ag-dl-modal-close"></div>
    <div class="ag-dl-modal__box">
        <button type="button" class="ag-dl-modal__close" id="ag-dl-modal-x">✕</button>
        <div class="ag-dl-modal__icon">🎁</div>
        <h3 class="ag-dl-modal__title">Votre téléchargement <em>gratuit</em></h3>
        <p class="ag-dl-modal__desc">Entrez vos coordonnées pour recevoir le lien de téléchargement. Pas de spam, promis.</p>
        <form class="ag-dl-modal__form" id="ag-dl-form">
            <input type="hidden" id="ag-dl-file" value="">
            <input type="hidden" id="ag-dl-template" value="">
            <div class="ag-form__group">
                <input type="text" id="ag-dl-name" placeholder="Votre nom" required>
            </div>
            <div class="ag-form__group">
                <input type="email" id="ag-dl-email" placeholder="Votre email" required>
            </div>
            <div class="ag-form__group">
                <input type="tel" id="ag-dl-phone" placeholder="Votre téléphone (optionnel)">
            </div>
            <button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Télécharger maintenant →</button>
        </form>
        <p class="ag-dl-modal__trust">🔒 Vos données sont protégées. Pas de spam.</p>
    </div>
</div>

<script>
(function(){
    var modal = document.getElementById('ag-dl-modal');
    var form = document.getElementById('ag-dl-form');
    var fileInput = document.getElementById('ag-dl-file');
    var tplInput = document.getElementById('ag-dl-template');

    document.querySelectorAll('.ag-dl-trigger').forEach(function(btn){
        btn.addEventListener('click', function(){
            fileInput.value = btn.getAttribute('data-file');
            tplInput.value = btn.getAttribute('data-template');
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModal(){
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.getElementById('ag-dl-modal-close').addEventListener('click', closeModal);
    document.getElementById('ag-dl-modal-x').addEventListener('click', closeModal);

    form.addEventListener('submit', function(e){
        e.preventDefault();
        var name = document.getElementById('ag-dl-name').value;
        var email = document.getElementById('ag-dl-email').value;
        var phone = document.getElementById('ag-dl-phone').value;
        var template = tplInput.value;
        var file = fileInput.value;

        var data = new FormData();
        data.append('action', 'ag_save_lead');
        data.append('name', name);
        data.append('email', email);
        data.append('phone', phone);
        data.append('template', template);
        data.append('ag_lead_nonce', '<?php echo wp_create_nonce("ag_lead_nonce"); ?>');

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        }).then(function(){
            var link = document.createElement('a');
            link.href = file;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            form.innerHTML = '<div style="text-align:center;padding:20px 0;"><div style="font-size:3rem;margin-bottom:12px;">✅</div><h3 style="margin-bottom:8px;">Merci ' + name + ' !</h3><p style="color:#b0b0bc;">Le téléchargement a démarré. Besoin d\'aide pour l\'installation ?</p><a href="tel:+33623526074" class="ag-btn-gold" style="margin-top:16px;">Appeler Fabrizio — 06.23.52.60.74</a></div>';
        });
    });
})();
</script>

<?php get_footer(); ?>
