<?php
/**
 * Template Name: Bureau Nantes
 *
 * Page dédiée à l'équipe du bureau de Nantes (siège).
 * Accent visuel : bleu royal France + slideshow hero.
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

// Slideshow hero : cherche les images de Nantes disponibles
$nantes_slides = array();
$cities_dir = get_stylesheet_directory() . '/assets/images/cities/';
foreach ( array( 'nantes-1.jpg', 'nantes-2.jpg', 'nantes-3.jpg' ) as $slide ) {
    if ( file_exists( $cities_dir . $slide ) ) {
        $nantes_slides[] = get_stylesheet_directory_uri() . '/assets/images/cities/' . $slide;
    }
}

$team = array(
    array(
        'name'  => 'Fabrizio',
        'role'  => 'Fondateur &amp; CEO',
        'img'   => $resolve( 'fabrizio' ),
        'bio'   => 'Fabrizio est né à Naples, dans les ruelles des Quartieri Spagnoli. À 19 ans, il a compris que le digital pouvait sortir les gens de la précarité, et a commencé à former des familles entières dans l\'arrière-salle d\'une église de quartier. Installé à Nantes depuis 2009, il y a fondé Alliance Groupe avec une conviction intacte&nbsp;: <strong>le web est un outil d\'émancipation avant d\'être un outil de business</strong>. Il pilote aujourd\'hui la vision globale de l\'agence, sans jamais oublier d\'où il vient.',
        'specialties' => array(
            'Vision produit &amp; stratégie long terme',
            'Accompagnement des dirigeants',
            'Architecture de marque &amp; positionnement',
            'Mentoring des équipes internationales',
        ),
        'link'  => home_url( '/notre-fondateur' ),
    ),
    array(
        'name'  => 'Kate',
        'role'  => 'Directrice Artistique',
        'img'   => $resolve( 'kate' ),
        'bio'   => 'Kate est diplômée de l\'École de Design Nantes Atlantique. Elle a travaillé pour plusieurs agences parisiennes avant de rejoindre Alliance Groupe avec une conviction forte&nbsp;: <strong>le design n\'est pas une décoration, c\'est une stratégie</strong>. Créative obsessionnelle et perfectionniste, elle conçoit des identités visuelles qui marquent les esprits et des interfaces qui convertissent. Son arme secrète&nbsp;: un œil clinique pour les détails que personne d\'autre ne remarque.',
        'specialties' => array(
            'Direction artistique &amp; branding',
            'UX/UI design premium',
            'Chartes graphiques complètes',
            'Prototypage Figma &amp; design system',
        ),
    ),
    array(
        'name'  => 'Laurent',
        'role'  => 'Responsable Commercial',
        'img'   => $resolve( 'laurent' ),
        'bio'   => 'Laurent a passé quinze ans dans la vente BtoB avant de rejoindre Alliance Groupe. Relationnel, patient et profondément honnête, il refuse de vendre ce dont le client n\'a pas besoin — ce qui, paradoxalement, est la raison pour laquelle nos clients reviennent. Il accompagne chaque projet depuis le premier contact jusqu\'à la livraison, en traduisant les besoins business en cahier des charges concret pour l\'équipe tech.',
        'specialties' => array(
            'Qualification de projet &amp; découverte client',
            'Rédaction de devis &amp; cahiers des charges',
            'Négociation commerciale B2B',
            'Suivi &amp; fidélisation post-livraison',
        ),
    ),
    array(
        'name'  => 'Julie',
        'role'  => 'Cheffe de Projet',
        'img'   => $resolve( 'julie' ),
        'bio'   => 'Julie est la colonne vertébrale opérationnelle d\'Alliance Groupe. Organisée, méthodique et dotée d\'un sang-froid à toute épreuve, elle coordonne au quotidien les équipes de Nantes, Naples et Marrakech sur tous les projets en cours. Sans elle, rien ne sortirait à temps. <strong>Son super-pouvoir&nbsp;: transformer un planning chaotique en machine bien huilée</strong>, sans jamais perdre son sourire.',
        'specialties' => array(
            'Coordination équipes internationales',
            'Méthodologie agile &amp; sprints',
            'Gestion des délais &amp; budget',
            'Communication client &amp; reporting',
        ),
    ),
);
?>

<main id="ag-main-content">

    <!-- Hero slideshow -->
    <section class="ag-hero ag-hero--slideshow">
        <div class="ag-hero__bg">
            <?php if ( ! empty( $nantes_slides ) ) : ?>
            <div class="ag-hero__slideshow" aria-hidden="true">
                <?php foreach ( $nantes_slides as $src ) : ?>
                    <div class="ag-hero__slide" style="background-image:url('<?php echo esc_url( $src ); ?>');"></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-tag--blue ag-anim" data-anim="tag">Bureau de Nantes 🇫🇷</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Notre équipe</span>
                <span class="ag-line"><em>à Nantes</em></span>
            </h1>
            <span class="ag-heritage-strip ag-heritage-strip--center" aria-hidden="true"></span>
            <p class="ag-hero__sub">Le siège historique d'Alliance Groupe. Entre la Loire et le centre-ville, quatre profils qui portent au quotidien la vision, la créa, le commerce et la gestion de nos projets.</p>
        </div>
    </section>

    <!-- À propos du bureau -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container ag-container--narrow">
            <div style="text-align:center;max-width:720px;margin:0 auto;">
                <span class="ag-tag">Pourquoi Nantes ?</span>
                <h2 class="ag-section__title">Le <em>cœur opérationnel</em> de l'agence</h2>
                <p style="color:var(--color-text-secondary);font-size:1.05rem;line-height:1.75;margin-top:24px;">
                    Nantes est la ville où Fabrizio s'est installé en 2009, et où Alliance Groupe a été fondée. C'est notre siège social, notre base commerciale, notre pôle créatif. La majeure partie des échanges clients, des devis, des revues de projet et des décisions stratégiques passent par ici.
                </p>
                <p style="color:var(--color-text-secondary);font-size:1.05rem;line-height:1.75;margin-top:16px;">
                    Nantes, c'est aussi <strong>la rigueur française, le goût du produit fini et l'attachement au service client de long terme</strong>. Ce qui fait la force de l'agence, c'est la combinaison de cet ADN nantais avec le feu napolitain et la patience marocaine.
                </p>
            </div>
        </div>
    </section>

    <!-- Équipe Nantes (4 cartes détaillées) -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <div style="text-align:center;max-width:640px;margin:0 auto 56px;">
                <span class="ag-tag ag-tag--blue">L'équipe</span>
                <h2 class="ag-section__title">Les visages de <em>Nantes</em></h2>
            </div>

            <div class="ag-mk-team">
                <?php foreach ( $team as $m ) : ?>
                <article class="ag-mk-card">
                    <div class="ag-mk-card__photo">
                        <?php if ( $m['img'] ) : ?>
                            <img src="<?php echo esc_url( $m['img'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy">
                        <?php else : ?>
                            <div class="ag-mk-card__initial"><?php echo esc_html( mb_substr( $m['name'], 0, 1 ) ); ?></div>
                        <?php endif; ?>
                        <span class="ag-mk-card__location">🇫🇷 Nantes, France</span>
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
                        <?php if ( ! empty( $m['link'] ) ) : ?>
                        <a href="<?php echo esc_url( $m['link'] ); ?>" class="ag-btn-outline" style="margin-top:20px;">Découvrir son histoire →</a>
                        <?php endif; ?>
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
                <div class="ag-heritage-card ag-heritage-card--fr" style="opacity:.7;cursor:default;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇫🇷</span>
                    <h3>Nantes</h3>
                    <p>Vous êtes ici. Fabrizio, Kate, Laurent et Julie. Le cœur opérationnel et commercial de l'agence.</p>
                </div>
                <a href="<?php echo esc_url( home_url( '/bureau-naples' ) ); ?>" class="ag-heritage-card ag-heritage-card--it" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇮🇹</span>
                    <h3>Naples</h3>
                    <p>L'histoire originelle. Carlito et son équipe technique. Les Quartieri Spagnoli, là où tout a commencé.</p>
                </a>
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
