<?php
get_header();

// Zone editable WP : contenu de la page "accueil" Gutenberg
if ( have_posts() ) : while ( have_posts() ) : the_post();
    if ( trim( get_the_content() ) ) :
        echo "<section class=\"ag-custom-content\" style=\"padding:50px 24px;background:#fff;\"><div style=\"max-width:1180px;margin:0 auto;\">";
        the_content();
        echo "</div></section>";
    endif;
endwhile; rewind_posts(); endif;

$settings = AG_Barber_Queue::get_settings();
$waiting  = AG_Barber_Queue::count_waiting();
$wait_min = AG_Barber_Queue::estimate_wait();
$est_time = AG_Barber_Queue::estimate_time();
$qr_url   = home_url( '/?ag_queue=join' );
?>

<main id="main">

    <!-- Hero -->
    <section class="ag-hero">
        <div class="ag-hero__bg"></div>
        <div style="position:relative;z-index:1;">
            <span class="ag-hero__tag">💈 <?php echo esc_html( $settings['shop_name'] ); ?></span>
            <h1><?php echo esc_html( get_theme_mod( 'ag_barber_hero_title', 'Votre coupe, sans rendez-vous.' ) ); ?><br><em><?php echo esc_html( get_theme_mod( 'ag_barber_hero_subtitle', 'Sans attente inutile.' ) ); ?></em></h1>
            <p class="ag-hero__sub"><?php echo esc_html( get_theme_mod( 'ag_barber_hero_text', 'Scannez le QR code en vitrine, prenez votre ticket, et revenez quand c\'est votre tour. Fini de poireauter debout.' ) ); ?></p>
            <div class="ag-hero__price">
                <strong><?php echo esc_html( get_theme_mod( 'ag_barber_main_price', '10€' ) ); ?></strong>
                <span><?php echo esc_html( ag_barber_opt( 'ag_barber_hero_price_suffix', 'la coupe homme' ) ); ?></span>
            </div>
            <div class="ag-hero__btns">
                <a href="<?php echo esc_url( $qr_url ); ?>" class="ag-btn ag-btn--gold"><?php echo esc_html( ag_barber_opt( 'ag_barber_hero_btn_primary', '📱 Prendre un ticket maintenant' ) ); ?></a>
                <a href="#services" class="ag-btn ag-btn--outline"><?php echo esc_html( ag_barber_opt( 'ag_barber_hero_btn_secondary', 'Voir les tarifs' ) ); ?></a>
            </div>
        </div>
    </section>

    <!-- File d'attente en temps réel -->
    <section class="ag-section ag-section--card" id="queue">
        <div class="ag-container">
            <h2 class="ag-section__title"><?php echo esc_html( ag_barber_opt( 'ag_barber_queue_title_pre', 'File d\'attente' ) ); ?> <em><?php echo esc_html( ag_barber_opt( 'ag_barber_queue_title_em', 'en direct' ) ); ?></em></h2>
            <p class="ag-section__sub"><?php echo esc_html( ag_barber_opt( 'ag_barber_queue_lead', 'Mis à jour en temps réel. Scannez le QR code en vitrine ou cliquez ci-dessous.' ) ); ?></p>

            <div class="ag-queue-status">
                <div class="ag-queue-status__count" id="ag-q-count"><?php echo esc_html( $waiting ); ?></div>
                <div class="ag-queue-status__label"><?php echo esc_html( ag_barber_opt( 'ag_barber_queue_count_label', 'personne(s) en attente' ) ); ?></div>
                <div class="ag-queue-status__wait">
                    <?php echo esc_html( ag_barber_opt( 'ag_barber_queue_wait_label', '⏱ Temps d\'attente estimé :' ) ); ?>
                    <strong id="ag-q-wait">~<?php echo esc_html( $wait_min ); ?> min</strong>
                    <?php if ( $waiting > 0 ) : ?>
                    — <?php echo esc_html( ag_barber_opt( 'ag_barber_queue_next_label', 'prochain passage vers' ) ); ?> <strong id="ag-q-time"><?php echo esc_html( $est_time ); ?></strong>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url( $qr_url ); ?>" class="ag-btn ag-btn--gold ag-queue-status__btn"><?php echo esc_html( ag_barber_opt( 'ag_barber_queue_btn_label', '📱 Rejoindre la file' ) ); ?></a>
            </div>
        </div>
    </section>

    <!-- Tarifs -->
    <section class="ag-section ag-section--dark" id="services">
        <div class="ag-container">
            <h2 class="ag-section__title"><?php echo esc_html( ag_barber_opt( 'ag_barber_services_title_pre', 'Nos' ) ); ?> <em><?php echo esc_html( ag_barber_opt( 'ag_barber_services_title_em', 'tarifs' ) ); ?></em></h2>
            <p class="ag-section__sub"><?php echo esc_html( ag_barber_opt( 'ag_barber_services_lead', 'Tarifs fixes, pas de surprise. Paiement en espèces ou carte bancaire.' ) ); ?></p>

            <div class="ag-services">
                <?php
                $icons = array( '✂️', '💈', '🪒', '👦', '⭐', '💇', '🧔', '✨' );
                foreach ( $settings['services'] as $i => $svc ) :
                    $icon = $icons[ $i % count( $icons ) ];
                ?>
                <div class="ag-service-card">
                    <div class="ag-service-card__icon"><?php echo $icon; ?></div>
                    <div class="ag-service-card__name"><?php echo esc_html( $svc['name'] ); ?></div>
                    <div class="ag-service-card__price"><?php echo esc_html( $svc['price'] ); ?>€</div>
                    <div class="ag-service-card__time">⏱ <?php echo esc_html( $svc['time'] ); ?> min</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section class="ag-section ag-section--card">
        <div class="ag-container">
            <h2 class="ag-section__title"><?php echo esc_html( ag_barber_opt( 'ag_barber_howitworks_title_pre', 'Comment' ) ); ?> <em><?php echo esc_html( ag_barber_opt( 'ag_barber_howitworks_title_em', 'ça marche' ) ); ?></em> ?</h2>
            <p class="ag-section__sub"><?php echo esc_html( ag_barber_opt( 'ag_barber_howitworks_lead', 'Plus besoin d\'attendre debout. 3 étapes, 30 secondes.' ) ); ?></p>

            <div class="ag-services" style="grid-template-columns:repeat(3,1fr);">
                <div class="ag-service-card">
                    <div class="ag-service-card__icon">📱</div>
                    <div class="ag-service-card__name"><?php echo esc_html( ag_barber_opt( 'ag_barber_step1_title', '1. Scannez le QR code' ) ); ?></div>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-top:8px;"><?php echo esc_html( ag_barber_opt( 'ag_barber_step1_text', 'En vitrine du salon. Votre téléphone ouvre la page de réservation.' ) ); ?></p>
                </div>
                <div class="ag-service-card">
                    <div class="ag-service-card__icon">🎫</div>
                    <div class="ag-service-card__name"><?php echo esc_html( ag_barber_opt( 'ag_barber_step2_title', '2. Prenez votre ticket' ) ); ?></div>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-top:8px;"><?php echo esc_html( ag_barber_opt( 'ag_barber_step2_text', 'Entrez votre prénom et choisissez votre prestation. Vous recevez une heure estimée.' ) ); ?></p>
                </div>
                <div class="ag-service-card">
                    <div class="ag-service-card__icon">💈</div>
                    <div class="ag-service-card__name"><?php echo esc_html( ag_barber_opt( 'ag_barber_step3_title', '3. Revenez à l\'heure' ) ); ?></div>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-top:8px;"><?php echo esc_html( ag_barber_opt( 'ag_barber_step3_text', 'Allez prendre un café, faites vos courses. Revenez quand c\'est votre tour.' ) ); ?></p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
