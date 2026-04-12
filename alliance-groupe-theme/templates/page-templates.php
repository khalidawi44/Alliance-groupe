<?php
/**
 * Template Name: Templates WordPress
 */
get_header();
$dl_base = get_stylesheet_directory_uri() . '/assets/downloads/';
?>

<main id="ag-main-content">

    <!-- Hero -->
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag">Templates WordPress</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Templates WordPress <em>gratuits</em></span>
                <span class="ag-line">prêts à installer</span>
            </h1>
            <p class="ag-hero__sub">Téléchargez, installez, personnalisez. Des thèmes professionnels pour lancer votre site rapidement.</p>
        </div>
    </section>

    <!-- Avertissement honnête -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container ag-container--narrow" style="text-align:center;">
            <div style="background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.15);border-radius:16px;padding:36px;">
                <h2 style="font-size:1.3rem;margin-bottom:12px;">Un template, c'est un <em>point de départ</em></h2>
                <p style="color:#b0b0bc;line-height:1.7;">Nos templates gratuits sont fonctionnels mais basiques. Le contenu est placeholder (textes et images à remplacer). La personnalisation demande du temps et des connaissances techniques en WordPress. Si vous voulez un site professionnel qui génère des résultats — <a href="<?php echo esc_url(home_url('/contact')); ?>" style="color:#D4B45C;font-weight:700;">parlons de votre projet</a>.</p>
            </div>
        </div>
    </section>

    <!-- Templates gratuits -->
    <section class="ag-section ag-section--marbre">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Gratuit</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Templates <em>gratuits</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Téléchargez le ZIP, installez via Apparence > Thèmes > Ajouter, et c'est parti.</p>

            <div class="ag-tpl__grid">

                <!-- Template Restaurant -->
                <div class="ag-tpl-card ag-anim" data-anim="card">
                    <div class="ag-tpl-card__img">
                        <div class="ag-tpl-card__preview">
                            <span style="font-size:2rem;">🍽️</span>
                            <strong>AG Starter Restaurant</strong>
                            <small>Thème sombre, or & noir</small>
                        </div>
                    </div>
                    <div class="ag-tpl-card__body">
                        <span class="ag-tpl-card__badge ag-tpl-card__badge--free">Gratuit</span>
                        <h3 class="ag-tpl-card__title">Starter Restaurant</h3>
                        <p class="ag-tpl-card__desc">Thème WordPress pour restaurant, bar ou café. Hero, carte, réservation, horaires. Contenu placeholder à remplacer.</p>
                        <ul class="ag-tpl-card__features">
                            <li>Page d'accueil complète</li>
                            <li>Design sombre premium</li>
                            <li>100% responsive</li>
                            <li>Sans Elementor</li>
                            <li>Contenu placeholder</li>
                        </ul>
                        <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="restaurant" data-file="<?php echo esc_url($dl_base . 'ag-starter-restaurant.zip'); ?>">Télécharger gratuitement →</button>
                    </div>
                </div>

                <!-- Template Artisan (coming soon) -->
                <div class="ag-tpl-card ag-tpl-card--soon ag-anim" data-anim="card">
                    <div class="ag-tpl-card__img">
                        <div class="ag-tpl-card__preview">
                            <span style="font-size:2rem;">🔨</span>
                            <strong>AG Starter Artisan</strong>
                            <small>Bientôt disponible</small>
                        </div>
                    </div>
                    <div class="ag-tpl-card__body">
                        <span class="ag-tpl-card__badge ag-tpl-card__badge--soon">Bientôt</span>
                        <h3 class="ag-tpl-card__title">Starter Artisan</h3>
                        <p class="ag-tpl-card__desc">Pour artisans, plombiers, électriciens, BTP. Portfolio de réalisations, zones d'intervention, formulaire de devis.</p>
                    </div>
                </div>

                <!-- Template Coach (coming soon) -->
                <div class="ag-tpl-card ag-tpl-card--soon ag-anim" data-anim="card">
                    <div class="ag-tpl-card__img">
                        <div class="ag-tpl-card__preview">
                            <span style="font-size:2rem;">💼</span>
                            <strong>AG Starter Coach</strong>
                            <small>Bientôt disponible</small>
                        </div>
                    </div>
                    <div class="ag-tpl-card__body">
                        <span class="ag-tpl-card__badge ag-tpl-card__badge--soon">Bientôt</span>
                        <h3 class="ag-tpl-card__title">Starter Coach</h3>
                        <p class="ag-tpl-card__desc">Pour coachs, consultants, formateurs. Page de services, témoignages, prise de rendez-vous, blog.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Packs Premium Stripe -->
    <section class="ag-section ag-section--or">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Premium</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Packs <em>Design Premium</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Allez plus loin avec des outils de personnalisation professionnels. Modifiez tout en drag & drop.</p>

            <div class="ag-pricing__grid">

                <!-- Pack 69€ -->
                <div class="ag-price-card ag-anim" data-anim="card">
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">69<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Starter</h3>
                        <p class="ag-price-card__sub">Pour ceux qui veulent personnaliser</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li>Template WordPress au choix</li>
                        <li>WordPress Block Editor amélioré</li>
                        <li>10 blocs design premium</li>
                        <li>Personnalisation couleurs & polices</li>
                        <li>Support email 30 jours</li>
                        <li>Documentation d'installation</li>
                    </ul>
                    <a href="#" class="ag-btn-outline ag-stripe-btn" data-price="69">Acheter — 69€</a>
                </div>

                <!-- Pack 99€ -->
                <div class="ag-price-card ag-price-card--pop ag-anim" data-anim="card">
                    <span class="ag-price-card__badge">Populaire</span>
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">99<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Pro</h3>
                        <p class="ag-price-card__sub">Le meilleur rapport qualité-prix</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li>Template WordPress au choix</li>
                        <li>Elementor Pro inclus (1 site)</li>
                        <li>Drag & drop — modifiez tout</li>
                        <li>50+ widgets premium</li>
                        <li>Formulaires avancés</li>
                        <li>Support email 60 jours</li>
                        <li>Tutoriel vidéo personnalisé</li>
                    </ul>
                    <a href="#" class="ag-btn-gold ag-stripe-btn" data-price="99">Acheter — 99€</a>
                </div>

                <!-- Pack 149€ -->
                <div class="ag-price-card ag-anim" data-anim="card">
                    <div class="ag-price-card__header">
                        <span class="ag-price-card__price">149<small>€</small></span>
                        <h3 class="ag-price-card__title">Pack Business</h3>
                        <p class="ag-price-card__sub">Tout inclus pour les ambitieux</p>
                    </div>
                    <ul class="ag-price-card__list">
                        <li>Template WordPress au choix</li>
                        <li>Elementor Pro inclus (1 site)</li>
                        <li>Plugin SEO Premium (Yoast SEO Premium)</li>
                        <li>Plugin de formulaires (WPForms Pro)</li>
                        <li>Plugin de cache (WP Rocket)</li>
                        <li>Support prioritaire 90 jours</li>
                        <li>Appel de 30 min avec un expert</li>
                        <li>Installation assistée</li>
                    </ul>
                    <a href="#" class="ag-btn-outline ag-stripe-btn" data-price="149">Acheter — 149€</a>
                </div>

            </div>
        </div>
    </section>

    <!-- Comparatif DIY vs Pro -->
    <section class="ag-section ag-section--image-luxe">
        <div class="ag-container">
            <h2 class="ag-section__title ag-anim" data-anim="title" style="text-align:center;">Template DIY vs <em>Site professionnel</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc" style="text-align:center;margin-left:auto;margin-right:auto;">Vous hésitez ? Voici la différence entre faire soi-même et confier à un professionnel.</p>

            <div class="ag-compare">
                <div class="ag-compare__col ag-compare__col--diy ag-anim" data-anim="card">
                    <h3 class="ag-compare__title">Template DIY</h3>
                    <span class="ag-compare__price">Gratuit / 69-149€</span>
                    <ul>
                        <li class="ag-compare__bad">Contenu placeholder à remplacer</li>
                        <li class="ag-compare__bad">Design générique, vu sur d'autres sites</li>
                        <li class="ag-compare__bad">SEO basique, pas optimisé pour votre marché</li>
                        <li class="ag-compare__bad">Pas de stratégie de conversion</li>
                        <li class="ag-compare__bad">Temps d'installation : 10-40 heures</li>
                        <li class="ag-compare__bad">Résultat : un site qui existe</li>
                        <li class="ag-compare__bad">Support limité</li>
                        <li class="ag-compare__bad">Nécessite des connaissances techniques</li>
                    </ul>
                </div>

                <div class="ag-compare__vs">VS</div>

                <div class="ag-compare__col ag-compare__col--pro ag-anim" data-anim="card">
                    <h3 class="ag-compare__title">Site par Alliance Groupe</h3>
                    <span class="ag-compare__price">À partir de 1 500€</span>
                    <ul>
                        <li class="ag-compare__good">Contenu rédigé par des pros du copywriting</li>
                        <li class="ag-compare__good">Design unique, sur-mesure pour votre marque</li>
                        <li class="ag-compare__good">SEO avancé ciblant VOS mots-clés</li>
                        <li class="ag-compare__good">Stratégie de conversion éprouvée (+340% leads)</li>
                        <li class="ag-compare__good">Temps pour vous : 0 heures — on fait tout</li>
                        <li class="ag-compare__good">Résultat : un site qui VEND</li>
                        <li class="ag-compare__good">Support illimité + maintenance</li>
                        <li class="ag-compare__good">Aucune compétence technique requise</li>
                    </ul>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-gold" style="margin-top:20px;">Parlons de votre projet →</a>
                </div>
            </div>

            <div style="text-align:center;margin-top:48px;padding:36px;background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.15);border-radius:16px;">
                <p style="font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:8px;">Un template ne remplacera jamais un professionnel.</p>
                <p style="color:#b0b0bc;margin-bottom:20px;">Nos clients génèrent en moyenne +340% de leads avec un site sur-mesure. Un template génère... des frustrations.</p>
                <div class="ag-hero__buttons">
                    <a href="tel:+33623526074" class="ag-btn-gold">Appeler Fabrizio — 06.23.52.60.74</a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Demander un devis gratuit →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Templates -->
    <section class="ag-section ag-section--cendre">
        <div class="ag-container">
            <h2 class="ag-section__title ag-anim" data-anim="title" style="text-align:center;">Questions sur les <em>templates</em></h2>
            <div class="ag-faq__list">
                <?php
                $tpl_faqs = [
                    ['q' => 'Comment installer un template gratuit ?', 'a' => 'Téléchargez le fichier ZIP, puis dans WordPress : Apparence > Thèmes > Ajouter > Envoyer un thème. Uploadez le ZIP et activez le thème.'],
                    ['q' => 'Le template est vraiment gratuit ?', 'a' => 'Oui, 100% gratuit. Pas de piège, pas d\'abonnement caché. C\'est un thème WordPress basique avec du contenu placeholder à remplacer.'],
                    ['q' => 'Quelle est la différence avec un site professionnel ?', 'a' => 'Un template est un point de départ générique. Un site professionnel est conçu sur-mesure pour VOTRE marque, avec du contenu rédigé par des experts, un SEO ciblé et une stratégie de conversion. C\'est la différence entre un costume de supermarché et un costume sur-mesure.'],
                    ['q' => 'J\'ai besoin d\'aide pour personnaliser, vous pouvez m\'aider ?', 'a' => 'Bien sûr ! Appelez-nous au 06.23.52.60.74. On peut personnaliser votre template ou créer un site entièrement sur-mesure qui génère des résultats concrets.'],
                    ['q' => 'Les packs premium incluent quoi exactement ?', 'a' => 'Le template WordPress + les plugins premium (Elementor Pro, Yoast SEO Premium, etc. selon le pack) + un support par email + une documentation d\'installation détaillée.'],
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

    <?php get_template_part('template-parts/cta'); ?>

</main>

<!-- Modal capture email -->
<div class="ag-dl-modal" id="ag-dl-modal">
    <div class="ag-dl-modal__overlay" id="ag-dl-modal-close"></div>
    <div class="ag-dl-modal__box">
        <button type="button" class="ag-dl-modal__close" id="ag-dl-modal-x">✕</button>
        <div class="ag-dl-modal__icon">🎁</div>
        <h3 class="ag-dl-modal__title">Votre template <em>gratuit</em> vous attend</h3>
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

    // Open modal on click
    document.querySelectorAll('.ag-dl-trigger').forEach(function(btn){
        btn.addEventListener('click', function(){
            fileInput.value = btn.getAttribute('data-file');
            tplInput.value = btn.getAttribute('data-template');
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    // Close modal
    function closeModal(){
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.getElementById('ag-dl-modal-close').addEventListener('click', closeModal);
    document.getElementById('ag-dl-modal-x').addEventListener('click', closeModal);

    // Submit form
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var name = document.getElementById('ag-dl-name').value;
        var email = document.getElementById('ag-dl-email').value;
        var phone = document.getElementById('ag-dl-phone').value;
        var template = tplInput.value;
        var file = fileInput.value;

        // Save lead via AJAX to WordPress
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
            // Trigger download
            var link = document.createElement('a');
            link.href = file;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Show thank you
            form.innerHTML = '<div style="text-align:center;padding:20px 0;"><div style="font-size:3rem;margin-bottom:12px;">✅</div><h3 style="margin-bottom:8px;">Merci ' + name + ' !</h3><p style="color:#b0b0bc;">Le téléchargement a démarré. Besoin d\'aide pour l\'installation ?</p><a href="tel:+33623526074" class="ag-btn-gold" style="margin-top:16px;">Appeler Fabrizio — 06.23.52.60.74</a></div>';
        });
    });
})();
</script>

<?php get_footer(); ?>
