<?php
/**
 * Template Name: Merci — Rendez-vous confirmé
 *
 * Dedicated thank-you page triggered after a successful Calendly
 * booking. Calendly is configured (per event type, in the advanced
 * settings) to redirect here once the buyer has finished the flow.
 *
 * Supported query params:
 *   ?offer=free|flash|strategique|deep  → label the confirmation
 *   ?event_type_uuid=...                → forwarded by Calendly, not used here
 *   ?invitee_uuid=...                   → forwarded by Calendly, not used here
 */
get_header();

$tiers  = function_exists( 'ag_calendly_tiers' ) ? ag_calendly_tiers() : array();
$offer  = isset( $_GET['offer'] ) ? sanitize_key( $_GET['offer'] ) : '';
$tier   = isset( $tiers[ $offer ] ) ? $tiers[ $offer ] : null;
?>

<main id="ag-main-content">

    <!-- Hero with check mark -->
    <section class="ag-section ag-section--darker ag-merci-hero">
        <div class="ag-container">
            <div class="ag-merci">

                <span class="ag-merci__check">✓</span>

                <span class="ag-tag">Rendez-vous confirmé</span>

                <h1 class="ag-merci__title">
                    Merci, votre rendez-vous est <em>bien pris</em>.
                </h1>

                <?php if ( $tier ) : ?>
                <p class="ag-merci__tier">
                    <strong><?php echo esc_html( $tier['label'] ); ?></strong>
                    · <?php echo esc_html( $tier['duration'] ); ?>
                    <?php if ( 'free' !== $offer ) : ?> · <?php echo esc_html( $tier['price'] ); ?><?php endif; ?>
                </p>
                <?php endif; ?>

                <p class="ag-merci__sub">
                    Nous avons reçu votre réservation. Vous allez recevoir dans les prochaines
                    minutes un <strong>email de confirmation</strong> avec tous les détails de
                    l'appel — date, heure, lien visio et ajout automatique à votre agenda.
                </p>

                <!-- What happens next -->
                <div class="ag-merci__checklist">
                    <h2>Ce qui se passe maintenant</h2>
                    <ul>
                        <li>
                            <span class="ag-merci__icon">📧</span>
                            <div>
                                <strong>Email de confirmation</strong>
                                Arrive dans les 2 prochaines minutes dans votre boîte mail — vérifiez vos spams si vous ne le voyez pas.
                            </div>
                        </li>
                        <li>
                            <span class="ag-merci__icon">📅</span>
                            <div>
                                <strong>Invitation calendrier</strong>
                                Un fichier <code>.ics</code> est joint à l'email. Un clic pour l'ajouter à Google Calendar, Outlook, Apple Calendar ou tout autre agenda.
                            </div>
                        </li>
                        <li>
                            <span class="ag-merci__icon">🎥</span>
                            <div>
                                <strong>Lien visio</strong>
                                Le lien Google Meet (ou Zoom) est dans l'email et dans l'événement calendrier. Aucune installation à prévoir — ça s'ouvre dans le navigateur.
                            </div>
                        </li>
                        <li>
                            <span class="ag-merci__icon">🔔</span>
                            <div>
                                <strong>Rappel automatique</strong>
                                Vous recevrez un rappel email 24h avant et un autre 30 minutes avant, pour ne rien oublier.
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Prepare for the call -->
                <div class="ag-merci__prepare">
                    <h2>Pour que l'appel soit le plus utile possible</h2>
                    <p class="ag-merci__prepare-intro">
                        Prenez 5 minutes avant notre appel pour noter mentalement (ou par écrit)&nbsp;:
                    </p>
                    <div class="ag-merci__prepare-grid">
                        <div class="ag-merci__prepare-item">
                            <span>1</span>
                            <p><strong>Votre activité en une phrase</strong> — ce que vous faites et pour qui.</p>
                        </div>
                        <div class="ag-merci__prepare-item">
                            <span>2</span>
                            <p><strong>Votre blocage principal</strong> aujourd'hui — la chose qui vous empêche d'avancer.</p>
                        </div>
                        <div class="ag-merci__prepare-item">
                            <span>3</span>
                            <p><strong>L'objectif</strong> que vous aimeriez atteindre dans les 3-6 prochains mois.</p>
                        </div>
                    </div>
                    <p class="ag-merci__prepare-note">
                        💡 Pas besoin de préparer un dossier ou une présentation — juste avoir ces 3 points en tête. Le reste, on le fait ensemble pendant l'appel.
                    </p>
                </div>

                <!-- Urgent contact fallback -->
                <div class="ag-merci__urgent">
                    <strong>Besoin de nous joindre avant l'appel ?</strong>
                    <p>Répondez simplement à l'email de confirmation que vous venez de recevoir, ou contactez-nous directement&nbsp;:</p>
                    <div class="ag-merci__urgent-btns">
                        <a href="tel:+33623526074" class="ag-btn-gold">📞 06.23.52.60.74</a>
                        <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline">✉️ Nous écrire</a>
                    </div>
                </div>

                <!-- Soft cross-sell: in-between reading -->
                <div class="ag-merci__reading">
                    <span class="ag-tag">En attendant notre appel</span>
                    <h2>3 lectures qui pourraient vous servir</h2>
                    <p>Des articles rapides (5-8 min de lecture) qui abordent des sujets qu'on croise souvent pendant nos consultations&nbsp;:</p>
                    <ul>
                        <?php
                        // Pick 3 recent articles from the blog.
                        $articles = new WP_Query( array(
                            'posts_per_page' => 3,
                            'post_status'    => 'publish',
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                        ) );
                        if ( $articles->have_posts() ) :
                            while ( $articles->have_posts() ) : $articles->the_post();
                        ?>
                        <li>
                            <a href="<?php the_permalink(); ?>">
                                <strong><?php the_title(); ?></strong>
                                <small><?php echo esc_html( get_the_date( 'd M Y' ) ); ?> · <?php echo esc_html( ag_reading_time() ); ?> min de lecture</small>
                            </a>
                        </li>
                        <?php endwhile; wp_reset_postdata(); else : ?>
                        <li><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">Voir tous nos articles →</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-merci__home">← Retour à l'accueil</a>

            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
