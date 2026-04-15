<?php
/**
 * Template Name: Bureau Naples
 *
 * Page dédiée à l'équipe du bureau de Naples (Italie).
 * Accent visuel : vert heritage + photo locale de Naples en slideshow.
 */
get_header();

$img_base = get_stylesheet_directory_uri() . '/assets/images/team/';
$img_dir  = get_stylesheet_directory() . '/assets/images/team/';

$resolve = function( $slug ) use ( $img_base, $img_dir ) {
    foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
        if ( file_exists( $img_dir . $slug . '.' . $ext ) ) {
            return $img_base . $slug . '.' . $ext;
        }
    }
    return '';
};

// Slideshow hero : cherche toutes les images de Naples disponibles
$naples_candidates = array(
    get_stylesheet_directory_uri() . '/assets/images/team/1_bureau_naples.jpg',
);
$extra_dir = get_stylesheet_directory() . '/assets/images/cities/';
foreach ( array( 'naples-1.jpg', 'naples-2.jpg', 'naples-3.jpg' ) as $slide ) {
    if ( file_exists( $extra_dir . $slide ) ) {
        $naples_candidates[] = get_stylesheet_directory_uri() . '/assets/images/cities/' . $slide;
    }
}
// Filter only those that actually exist on disk
$naples_slides = array();
foreach ( $naples_candidates as $url ) {
    $path = str_replace( get_stylesheet_directory_uri(), get_stylesheet_directory(), $url );
    if ( file_exists( $path ) ) {
        $naples_slides[] = $url;
    }
}

$team = array(
    array(
        'name'  => 'Carlito',
        'role'  => 'Directeur Technique',
        'img'   => $resolve( 'carlito' ),
        'bio'   => 'Carlito est né et a grandi à Naples, dans les ruelles vibrantes des Quartieri Spagnoli. Ingénieur de formation, il a fait ses armes dans plusieurs startups italiennes avant de rejoindre Fabrizio dans l\'aventure Alliance Groupe. Il dirige aujourd\'hui le pôle technique depuis le bureau napolitain, entre code, architecture et expressos bien serrés. Son credo&nbsp;: "La tecnologia è l\'arte di semplificare la complessità".',
        'specialties' => array(
            'Architecture web &amp; backend scalable',
            'Intégrations WordPress avancées',
            'DevOps &amp; infrastructure cloud',
            'Code review &amp; mentoring technique',
        ),
    ),
);
?>

<main id="ag-main-content">

    <!-- Hero slideshow -->
    <section class="ag-hero ag-hero--slideshow">
        <div class="ag-hero__bg">
            <?php if ( ! empty( $naples_slides ) ) : ?>
            <div class="ag-hero__slideshow" aria-hidden="true">
                <?php foreach ( $naples_slides as $src ) : ?>
                    <div class="ag-hero__slide" style="background-image:url('<?php echo esc_url( $src ); ?>');"></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-tag--green ag-anim" data-anim="tag">Bureau de Naples 🇮🇹</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Notre équipe</span>
                <span class="ag-line"><em>à Naples</em></span>
            </h1>
            <span class="ag-heritage-strip ag-heritage-strip--center" aria-hidden="true"></span>
            <p class="ag-hero__sub">Là où l'histoire d'Alliance Groupe a commencé. Entre les ruelles des Quartieri Spagnoli et l'effervescence de la baie, notre pôle technique transforme le code en outil de service.</p>
        </div>
    </section>

    <!-- À propos du bureau -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container ag-container--narrow">
            <div style="text-align:center;max-width:720px;margin:0 auto;">
                <span class="ag-tag">Pourquoi Naples ?</span>
                <h2 class="ag-section__title">Là où <em>tout a commencé</em></h2>
                <p style="color:var(--color-text-secondary);font-size:1.05rem;line-height:1.75;margin-top:24px;">
                    Naples n'est pas une ville comme les autres. C'est là que Fabrizio a grandi, là qu'il a eu sa révélation&nbsp;: le digital pouvait sortir les gens de la précarité. Notre bureau napolitain, installé à deux pas des Quartieri Spagnoli, est le cœur technique de l'agence. Architecture, développement, intégrations avancées — tout passe par Carlito et son équipe.
                </p>
                <p style="color:var(--color-text-secondary);font-size:1.05rem;line-height:1.75;margin-top:16px;">
                    À Naples, on ne fait pas semblant. <strong>On code comme on cuisine une pizza&nbsp;: avec passion, précision, et zéro compromis sur la qualité des ingrédients.</strong>
                </p>
            </div>
        </div>
    </section>

    <!-- Équipe Naples -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <div style="text-align:center;max-width:640px;margin:0 auto 56px;">
                <span class="ag-tag ag-tag--green">L'équipe</span>
                <h2 class="ag-section__title">Les visages de <em>Naples</em></h2>
            </div>

            <div class="ag-mk-team" style="grid-template-columns:1fr;max-width:720px;">
                <?php foreach ( $team as $m ) : ?>
                <article class="ag-mk-card">
                    <div class="ag-mk-card__photo" style="aspect-ratio:16/9;">
                        <?php if ( $m['img'] ) : ?>
                            <img src="<?php echo esc_url( $m['img'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy">
                        <?php else : ?>
                            <div class="ag-mk-card__initial"><?php echo esc_html( mb_substr( $m['name'], 0, 1 ) ); ?></div>
                        <?php endif; ?>
                        <span class="ag-mk-card__location">🇮🇹 Naples, Italie</span>
                    </div>
                    <div class="ag-mk-card__body">
                        <h3 class="ag-mk-card__name"><?php echo esc_html( $m['name'] ); ?></h3>
                        <span class="ag-mk-card__role"><?php echo wp_kses_post( $m['role'] ); ?></span>
                        <p class="ag-mk-card__bio"><?php echo wp_kses_post( $m['bio'] ); ?></p>
                        <div class="ag-mk-card__specialties">
                            <strong>Spécialités</strong>
                            <ul>
                                <?php foreach ( $m['specialties'] as $sp ) : ?>
                                <li><?php echo wp_kses_post( $sp ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Autres bureaux -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div style="text-align:center;max-width:720px;margin:0 auto 40px;">
                <h2 class="ag-section__title">Nos autres <em>bureaux</em></h2>
                <p class="ag-section__desc" style="margin:0 auto;">Alliance Groupe, c'est trois villes, trois cultures, une seule équipe.</p>
            </div>
            <div class="ag-heritage-grid">
                <a href="<?php echo esc_url( home_url( '/bureau-nantes' ) ); ?>" class="ag-heritage-card ag-heritage-card--fr" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇫🇷</span>
                    <h3>Nantes</h3>
                    <p>Le siège historique. Fabrizio, Kate, Laurent et Julie. Le cœur opérationnel et commercial de l'agence.</p>
                </a>
                <div class="ag-heritage-card ag-heritage-card--it" style="opacity:.7;cursor:default;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇮🇹</span>
                    <h3>Naples</h3>
                    <p>Vous êtes ici. Carlito et l'équipe technique. Les Quartieri Spagnoli, là où tout a commencé.</p>
                </div>
                <a href="<?php echo esc_url( home_url( '/bureau-marrakech' ) ); ?>" class="ag-heritage-card ag-heritage-card--ma" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇲🇦</span>
                    <h3>Marrakech</h3>
                    <p>Halim et Amina, les experts SEO et IA du groupe, entre la médina et les souks de Bab Doukkala.</p>
                </a>
            </div>
        </div>
    </section>

    <?php get_template_part( 'template-parts/cta' ); ?>

</main>

<?php get_footer(); ?>
