<?php
/**
 * Template Name: Prise de rendez-vous
 */
get_header();

$calendly_url = function_exists( 'ag_get_calendly_url' ) ? ag_get_calendly_url() : '';
// Add Calendly theme params only if the base URL doesn't already carry a query string.
$calendly_params = 'hide_gdpr_banner=1&background_color=0a0a0f&text_color=e8e6e0&primary_color=d4b45c';
$calendly_embed = '';
if ( $calendly_url ) {
	$sep             = ( false === strpos( $calendly_url, '?' ) ) ? '?' : '&';
	$calendly_embed  = $calendly_url . $sep . $calendly_params;
}
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
                <span class="ag-line">Réservez votre <em>appel gratuit</em></span>
                <span class="ag-line">en 30 secondes</span>
            </h1>
            <p class="ag-hero__sub">30 minutes en visio avec Fabrizio pour analyser votre activité, identifier les leviers prioritaires et savoir concrètement ce qu'on peut faire pour vous. Sans engagement, sans jargon.</p>
        </div>
    </section>

    <!-- Calendly embed -->
    <section class="ag-section ag-section--darker" id="ag-booking">
        <div class="ag-container">
            <div class="ag-calendly">
                <?php if ( $calendly_embed ) : ?>
                    <!-- Calendly inline widget — real embed -->
                    <div class="calendly-inline-widget ag-calendly__widget"
                         data-url="<?php echo esc_url( $calendly_embed ); ?>"
                         style="min-width:320px;height:780px;"></div>
                    <script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>

                    <noscript>
                        <p style="text-align:center;color:#b0b0bc;margin-top:20px;">
                            JavaScript désactivé&nbsp;? Réservez directement sur
                            <a href="<?php echo esc_url( $calendly_url ); ?>" target="_blank" rel="noopener noreferrer" style="color:#D4B45C;">Calendly</a>.
                        </p>
                    </noscript>
                <?php else : ?>
                    <!-- Placeholder shown only if the URL isn't configured yet -->
                    <div class="ag-calendly__empty">
                        <div style="font-size:2.6rem;margin-bottom:14px;">📅</div>
                        <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:#fff;margin:0 0 12px;">Widget de réservation non configuré</h2>
                        <p style="color:#b0b0bc;max-width:520px;margin:0 auto 22px;">
                            L'URL Calendly n'a pas encore été renseignée dans l'administration du site.
                            En attendant, vous pouvez nous joindre directement&nbsp;:
                        </p>
                        <div style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;">
                            <a href="tel:+33623526074" class="ag-btn-gold">📞 06.23.52.60.74</a>
                            <a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline">✉️ Nous écrire</a>
                        </div>
                        <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <p style="margin-top:28px;padding:12px 18px;background:rgba(212,180,92,.08);border:1px dashed rgba(212,180,92,.35);border-radius:10px;color:#D4B45C;font-size:.88rem;">
                            <strong>Admin :</strong> rendez-vous dans <a href="<?php echo esc_url( admin_url( 'options-general.php?page=ag-calendly-config' ) ); ?>" style="color:#D4B45C;text-decoration:underline;">Réglages &rsaquo; Calendly AG</a> pour coller votre URL Calendly.
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
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
