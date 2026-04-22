<?php
/**
 * QR Code landing page — customer joins the queue.
 * Accessible via ?ag_queue=join
 */
get_header();
$settings = AG_Barber_Queue::get_settings();
$waiting  = AG_Barber_Queue::count_waiting();
$wait_min = AG_Barber_Queue::estimate_wait();

// Handle form submission (non-AJAX fallback)
$ticket = null;
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ag_queue_nonce'] ) && wp_verify_nonce( $_POST['ag_queue_nonce'], 'ag_queue_join' ) ) {
    $name    = sanitize_text_field( $_POST['name'] ?? '' );
    $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
    $service = sanitize_text_field( $_POST['service'] ?? '' );
    if ( $name ) {
        $ticket = AG_Barber_Queue::join( $name, $phone, $service );
    }
}
?>

<main id="main" style="min-height:90vh;display:flex;align-items:center;padding:40px 24px;">
    <div class="ag-container" style="max-width:520px;">

        <?php if ( $ticket ) : ?>
        <!-- TICKET CONFIRMATION -->
        <div class="ag-ticket">
            <div style="font-size:1.6rem;margin-bottom:8px;">🎫</div>
            <div class="ag-ticket__label"><?php esc_html_e( 'Votre numéro', 'ag-starter-barber' ); ?></div>
            <div class="ag-ticket__number">#<?php echo esc_html( $ticket['number'] ); ?></div>

            <div class="ag-ticket__info">
                <p><strong><?php echo esc_html( $ticket['name'] ); ?></strong></p>
                <p><?php echo esc_html( $ticket['service'] ); ?></p>
                <p>⏱ <?php esc_html_e( 'Attente estimée :', 'ag-starter-barber' ); ?> <strong>~<?php echo esc_html( $ticket['estimated_wait'] ); ?> min</strong></p>
                <p style="font-size:1.3rem;margin-top:12px;">🕐 <?php esc_html_e( 'Passage prévu vers', 'ag-starter-barber' ); ?> <strong style="font-size:1.5rem;"><?php echo esc_html( $ticket['estimated_time'] ); ?></strong></p>
            </div>

            <p style="color:var(--text-muted);font-size:.88rem;line-height:1.5;">
                <?php esc_html_e( 'Gardez cette page ouverte. Vous pouvez aller prendre un café et revenir à l\'heure indiquée. Présentez ce ticket au barber.', 'ag-starter-barber' ); ?>
            </p>

            <div style="margin-top:20px;padding:16px;background:rgba(212,180,92,.08);border-radius:10px;">
                <p style="color:var(--gold);font-weight:700;font-size:.9rem;margin:0;">Ticket : <?php echo esc_html( $ticket['id'] ); ?></p>
            </div>
        </div>

        <?php else : ?>
        <!-- JOIN FORM -->
        <div class="ag-queue-form">
            <div style="font-size:2rem;text-align:center;margin-bottom:12px;">💈</div>
            <h2><?php echo esc_html( $settings['shop_name'] ); ?></h2>
            <p class="ag-queue-form__sub">
                <?php if ( $waiting > 0 ) : ?>
                    <?php echo esc_html( $waiting ); ?> <?php esc_html_e( 'personne(s) devant vous — ~', 'ag-starter-barber' ); ?><?php echo esc_html( $wait_min ); ?> <?php esc_html_e( 'min d\'attente', 'ag-starter-barber' ); ?>
                <?php else : ?>
                    <?php esc_html_e( 'Aucune attente — passage immédiat !', 'ag-starter-barber' ); ?>
                <?php endif; ?>
            </p>

            <form method="post">
                <?php wp_nonce_field( 'ag_queue_join', 'ag_queue_nonce' ); ?>

                <label for="ag-name"><?php esc_html_e( 'Votre prénom *', 'ag-starter-barber' ); ?></label>
                <input type="text" id="ag-name" name="name" required placeholder="Ex : Karim" autofocus>

                <label for="ag-phone"><?php esc_html_e( 'Téléphone (facultatif)', 'ag-starter-barber' ); ?></label>
                <input type="tel" id="ag-phone" name="phone" placeholder="06 12 34 56 78">

                <label for="ag-service"><?php esc_html_e( 'Prestation', 'ag-starter-barber' ); ?></label>
                <select id="ag-service" name="service">
                    <?php foreach ( $settings['services'] as $svc ) : ?>
                        <option value="<?php echo esc_attr( $svc['name'] ); ?>"><?php echo esc_html( $svc['name'] ); ?> — <?php echo esc_html( $svc['price'] ); ?>€ (<?php echo esc_html( $svc['time'] ); ?> min)</option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="ag-btn ag-btn--gold" style="width:100%;justify-content:center;margin-top:8px;">🎫 <?php esc_html_e( 'Prendre mon ticket', 'ag-starter-barber' ); ?></button>
            </form>

            <p style="color:var(--text-muted);font-size:.8rem;text-align:center;margin-top:16px;">
                <?php esc_html_e( 'En prenant un ticket, vous acceptez d\'être ajouté à la file d\'attente. Aucune donnée n\'est conservée après votre passage.', 'ag-starter-barber' ); ?>
            </p>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
