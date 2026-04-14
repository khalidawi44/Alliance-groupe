<?php
/**
 * Template Name: Prise de rendez-vous
 */
get_header();

$tiers          = function_exists( 'ag_calendly_tiers' ) ? ag_calendly_tiers() : array();
$selected       = isset( $_GET['offer'] ) ? sanitize_key( $_GET['offer'] ) : 'free';
if ( ! isset( $tiers[ $selected ] ) ) {
	$selected = 'free';
}
$calendly_params = 'hide_gdpr_banner=1&background_color=0a0a0f&text_color=e8e6e0&primary_color=d4b45c';

/**
 * Build a Calendly embed URL with our theme params appended.
 */
$build_embed_url = function( $base ) use ( $calendly_params ) {
	if ( ! $base ) return '';
	$sep = ( false === strpos( $base, '?' ) ) ? '?' : '&';
	return $base . $sep . $calendly_params;
};

$selected_url   = function_exists( 'ag_get_calendly_tier_url' ) ? ag_get_calendly_tier_url( $selected ) : '';
$selected_embed = $build_embed_url( $selected_url );
$selected_tier  = $tiers[ $selected ];
?>

<main id="ag-main-content">

    <!-- Hero -->
    <section class="ag-hero" style="min-height:50vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Prise de rendez-vous</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Réservez votre <em>consultation</em></span>
                <span class="ag-line">en 30 secondes</span>
            </h1>
            <p class="ag-hero__sub">Un appel découverte gratuit pour faire connaissance, ou une consultation premium avec livrable écrit si vous voulez un vrai plan d'action. Vous choisissez.</p>
        </div>
    </section>

    <!-- Consultations grid -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div class="ag-tiers">
                <?php foreach ( $tiers as $key => $tier ) :
                    $is_active = ( $key === $selected );
                    $is_paid   = ( 'free' !== $key );
                    $card_url  = esc_url( add_query_arg( 'offer', $key, get_permalink() ) ) . '#ag-booking';
                ?>
                <a href="<?php echo esc_url( $card_url ); ?>"
                   class="ag-tier-card<?php echo $is_active ? ' ag-tier-card--active' : ''; ?><?php echo ( 'strategique' === $key ) ? ' ag-tier-card--hero' : ''; ?>">
                    <?php if ( 'strategique' === $key ) : ?>
                        <span class="ag-tier-card__badge">⭐ Le plus choisi</span>
                    <?php elseif ( 'free' === $key ) : ?>
                        <span class="ag-tier-card__badge ag-tier-card__badge--free">Gratuit</span>
                    <?php endif; ?>

                    <span class="ag-tier-card__tagline"><?php echo esc_html( $tier['tagline'] ); ?></span>
                    <h3 class="ag-tier-card__label"><?php echo esc_html( $tier['label'] ); ?></h3>
                    <div class="ag-tier-card__price">
                        <strong><?php echo esc_html( $tier['price'] ); ?></strong>
                        <small><?php echo esc_html( $tier['duration'] ); ?></small>
                    </div>
                    <p class="ag-tier-card__desc"><?php echo esc_html( $tier['description'] ); ?></p>

                    <span class="ag-tier-card__cta">
                        <?php echo $is_paid ? 'Réserver et payer' : 'Choisir un créneau'; ?> →
                    </span>
                </a>
                <?php endforeach; ?>
            </div>

            <p class="ag-tiers__note">
                💡 <strong>Conseil</strong> : commencez par l'appel découverte gratuit si vous hésitez — il est honnête et sans pression. Si vous savez déjà que vous voulez un vrai plan, <strong>l'Audit Stratégique</strong> est le meilleur rapport qualité-prix.
            </p>
        </div>
    </section>

    <!-- Calendly embed for the selected tier -->
    <section class="ag-section ag-section--darker" id="ag-booking">
        <div class="ag-container">
            <div class="ag-calendly">
                <div class="ag-calendly__header">
                    <span class="ag-tag">
                        <?php echo esc_html( $selected_tier['label'] ); ?> · <?php echo esc_html( $selected_tier['duration'] ); ?> · <?php echo esc_html( $selected_tier['price'] ); ?>
                    </span>
                    <h2 class="ag-calendly__title">
                        Réservez votre <em><?php echo esc_html( $selected_tier['label'] ); ?></em>
                    </h2>
                    <p class="ag-calendly__sub">
                        <?php echo esc_html( $selected_tier['description'] ); ?>
                    </p>
                </div>

                <?php if ( $selected_embed ) : ?>
                    <div class="calendly-inline-widget ag-calendly__widget"
                         data-url="<?php echo esc_url( $selected_embed ); ?>"
                         style="min-width:320px;height:780px;"></div>
                    <script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>

                    <noscript>
                        <p style="text-align:center;color:#b0b0bc;margin-top:20px;">
                            JavaScript désactivé&nbsp;? Réservez directement sur
                            <a href="<?php echo esc_url( $selected_url ); ?>" target="_blank" rel="noopener noreferrer" style="color:#D4B45C;">Calendly</a>.
                        </p>
                    </noscript>
                <?php else : ?>
                    <div class="ag-calendly__empty">
                        <div style="font-size:2.6rem;margin-bottom:14px;">📅</div>
                        <p style="color:#b0b0bc;">Widget non configuré pour cette offre.</p>
                        <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <p style="margin-top:14px;padding:10px 16px;background:rgba(212,180,92,.08);border:1px dashed rgba(212,180,92,.35);border-radius:10px;color:#D4B45C;font-size:.88rem;display:inline-block;">
                            <strong>Admin :</strong> allez dans <a href="<?php echo esc_url( admin_url( 'options-general.php?page=ag-calendly-config' ) ); ?>" style="color:#D4B45C;text-decoration:underline;">Réglages &rsaquo; Calendly AG</a> pour configurer l'URL de <?php echo esc_html( $selected_tier['label'] ); ?>.
                        </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- À quoi s'attendre -->
            <div class="ag-rdv-expect">
                <h2 class="ag-rdv-expect__title">À quoi s'attendre pendant l'appel</h2>
                <div class="ag-rdv-expect__grid">
                    <div class="ag-rdv-expect__item ag-anim" data-anim="card">
                        <span class="ag-rdv-expect__num">1</span>
                        <h3>On écoute votre activité</h3>
                        <p>Votre métier, vos clients, ce qui vous prend du temps, ce qui vous stresse. Pas de questionnaire type, juste une vraie discussion.</p>
                    </div>
                    <div class="ag-rdv-expect__item ag-anim" data-anim="card">
                        <span class="ag-rdv-expect__num">2</span>
                        <h3>On identifie les leviers concrets</h3>
                        <p>Les 2 ou 3 actions qui auraient le plus d'impact sur votre chiffre d'affaires et sur votre temps. Classé par priorité, chiffré.</p>
                    </div>
                    <div class="ag-rdv-expect__item ag-anim" data-anim="card">
                        <span class="ag-rdv-expect__num">3</span>
                        <h3>Vous repartez avec un plan clair</h3>
                        <p>Que vous travailliez avec nous ou non, vous aurez une vision nette de ce qui peut être fait, à quel coût, et dans quel ordre.</p>
                    </div>
                </div>

                <div class="ag-rdv-expect__trust">
                    <strong>Ce qu'on ne fera pas :</strong>
                    <ul>
                        <li>pas de démo PowerPoint de 20 minutes&nbsp;;</li>
                        <li>pas de script commercial qui insiste pour signer à la fin&nbsp;;</li>
                        <li>pas de devis de 12 pages envoyé dans la foulée sans qu'on se reparle&nbsp;;</li>
                        <li>pas de jargon technique — on parle comme deux humains.</li>
                    </ul>
                </div>

                <!-- Cross-sell Questions Flash -->
                <div class="ag-rdv-expect__crosssell">
                    <div>
                        <span class="ag-tag">Alternative écrite</span>
                        <h3>Vous avez juste UNE question précise ?</h3>
                        <p>Pas besoin de caler un appel. Posez votre question par écrit, on vous répond sous 48h avec une analyse experte détaillée. <strong>À partir de 45 €.</strong></p>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/questions-flash' ) ); ?>" class="ag-btn-outline">
                        Voir les Questions Flash →
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
