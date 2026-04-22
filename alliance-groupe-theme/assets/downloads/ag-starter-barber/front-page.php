<?php
get_header();
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
                <span><?php esc_html_e( 'la coupe homme', 'ag-starter-barber' ); ?></span>
            </div>
            <div class="ag-hero__btns">
                <a href="<?php echo esc_url( $qr_url ); ?>" class="ag-btn ag-btn--gold">📱 <?php esc_html_e( 'Prendre un ticket maintenant', 'ag-starter-barber' ); ?></a>
                <a href="#services" class="ag-btn ag-btn--outline"><?php esc_html_e( 'Voir les tarifs', 'ag-starter-barber' ); ?></a>
            </div>
        </div>
    </section>

    <!-- File d'attente en temps réel -->
    <section class="ag-section ag-section--card" id="queue">
        <div class="ag-container">
            <h2 class="ag-section__title"><?php esc_html_e( 'File d\'attente', 'ag-starter-barber' ); ?> <em><?php esc_html_e( 'en direct', 'ag-starter-barber' ); ?></em></h2>
            <p class="ag-section__sub"><?php esc_html_e( 'Mis à jour en temps réel. Scannez le QR code en vitrine ou cliquez ci-dessous.', 'ag-starter-barber' ); ?></p>

            <div class="ag-queue-status">
                <div class="ag-queue-status__count" id="ag-q-count"><?php echo esc_html( $waiting ); ?></div>
                <div class="ag-queue-status__label"><?php esc_html_e( 'personne(s) en attente', 'ag-starter-barber' ); ?></div>
                <div class="ag-queue-status__wait">
                    ⏱ <?php esc_html_e( 'Temps d\'attente estimé :', 'ag-starter-barber' ); ?>
                    <strong id="ag-q-wait">~<?php echo esc_html( $wait_min ); ?> min</strong>
                    <?php if ( $waiting > 0 ) : ?>
                    — <?php esc_html_e( 'prochain passage vers', 'ag-starter-barber' ); ?> <strong id="ag-q-time"><?php echo esc_html( $est_time ); ?></strong>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url( $qr_url ); ?>" class="ag-btn ag-btn--gold ag-queue-status__btn">📱 <?php esc_html_e( 'Rejoindre la file', 'ag-starter-barber' ); ?></a>
            </div>
        </div>
    </section>

    <!-- Tarifs -->
    <section class="ag-section ag-section--dark" id="services">
        <div class="ag-container">
            <h2 class="ag-section__title"><?php esc_html_e( 'Nos', 'ag-starter-barber' ); ?> <em><?php esc_html_e( 'tarifs', 'ag-starter-barber' ); ?></em></h2>
            <p class="ag-section__sub"><?php esc_html_e( 'Tarifs fixes, pas de surprise. Paiement en espèces ou carte bancaire.', 'ag-starter-barber' ); ?></p>

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
            <h2 class="ag-section__title"><?php esc_html_e( 'Comment', 'ag-starter-barber' ); ?> <em><?php esc_html_e( 'ça marche', 'ag-starter-barber' ); ?></em> ?</h2>
            <p class="ag-section__sub"><?php esc_html_e( 'Plus besoin d\'attendre debout. 3 étapes, 30 secondes.', 'ag-starter-barber' ); ?></p>

            <div class="ag-services" style="grid-template-columns:repeat(3,1fr);">
                <div class="ag-service-card">
                    <div class="ag-service-card__icon">📱</div>
                    <div class="ag-service-card__name"><?php esc_html_e( '1. Scannez le QR code', 'ag-starter-barber' ); ?></div>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-top:8px;"><?php esc_html_e( 'En vitrine du salon. Votre téléphone ouvre la page de réservation.', 'ag-starter-barber' ); ?></p>
                </div>
                <div class="ag-service-card">
                    <div class="ag-service-card__icon">🎫</div>
                    <div class="ag-service-card__name"><?php esc_html_e( '2. Prenez votre ticket', 'ag-starter-barber' ); ?></div>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-top:8px;"><?php esc_html_e( 'Entrez votre prénom et choisissez votre prestation. Vous recevez une heure estimée.', 'ag-starter-barber' ); ?></p>
                </div>
                <div class="ag-service-card">
                    <div class="ag-service-card__icon">💈</div>
                    <div class="ag-service-card__name"><?php esc_html_e( '3. Revenez à l\'heure', 'ag-starter-barber' ); ?></div>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-top:8px;"><?php esc_html_e( 'Allez prendre un café, faites vos courses. Revenez quand c\'est votre tour.', 'ag-starter-barber' ); ?></p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
