<?php
/**
 * Template Name: Bureau Marrakech
 *
 * Page dédiée à l'équipe du bureau de Marrakech.
 * Accent visuel : vert heritage + motif zellige SVG.
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

$team = array(
    array(
        'name'  => 'Halim',
        'role'  => 'Responsable SEO &amp; Data',
        'img'   => $resolve( 'halim' ),
        'bio'   => 'Halim a grandi à Marrakech entre le quartier de la Médina et les allées du Guéliz. Passionné par les chiffres depuis toujours, il a d\'abord étudié les mathématiques avant de se plonger dans le SEO et l\'analyse de données. Chez Alliance Groupe, il est celui qui transforme des pages invisibles en machines à trafic. Son secret&nbsp;: un œil obsessionnel pour les détails, et une patience à l\'image des artisans du souk.',
        'specialties' => array(
            'Audit SEO technique &amp; sémantique',
            'Stratégies de linking &amp; autorité',
            'Google Analytics &amp; Search Console',
            'Suivi de positionnement &amp; reporting',
        ),
    ),
    array(
        'name'  => 'Amina',
        'role'  => 'Responsable IA &amp; Automatisation',
        'img'   => $resolve( 'amina' ),
        'bio'   => 'Amina est diplômée en informatique de l\'Université Cadi Ayyad de Marrakech. Elle a été l\'une des premières à travailler sur l\'IA générative pour les PME francophones. Chez Alliance Groupe, elle conçoit les automatisations qui libèrent nos clients des tâches répétitives&nbsp;: chatbots, workflows, agents personnalisés. Son approche est humble et rigoureuse&nbsp;: "L\'IA ne remplace personne, elle libère du temps pour ce qui compte vraiment."',
        'specialties' => array(
            'Chatbots intelligents &amp; agents conversationnels',
            'Workflows n8n / Make / Zapier avancés',
            'Intégrations API OpenAI, Claude, Mistral',
            'Analyse et nettoyage de données',
        ),
    ),
);
?>

<main id="ag-main-content">

    <!-- Hero avec slideshow + motif zellige subtil -->
    <?php
    $mk_slides = array( 'marrakech-1.jpg', 'marrakech-2.jpg', 'marrakech-3.jpg' );
    $mk_existing = array();
    foreach ( $mk_slides as $slide ) {
        if ( file_exists( get_stylesheet_directory() . '/assets/images/cities/' . $slide ) ) {
            $mk_existing[] = get_stylesheet_directory_uri() . '/assets/images/cities/' . $slide;
        }
    }
    ?>
    <section class="ag-hero ag-hero--slideshow ag-hero--marrakech">
        <div class="ag-hero__bg">
            <?php if ( ! empty( $mk_existing ) ) : ?>
            <div class="ag-hero__slideshow" aria-hidden="true">
                <?php foreach ( $mk_existing as $src ) : ?>
                    <div class="ag-hero__slide" style="background-image:url('<?php echo esc_url( $src ); ?>');"></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="ag-hero__orb ag-hero__orb--1"></div>
            <div class="ag-zellige-pattern" aria-hidden="true"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-tag--green ag-anim" data-anim="tag">Bureau de Marrakech 🇲🇦</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Notre équipe</span>
                <span class="ag-line"><em>à Marrakech</em></span>
            </h1>
            <span class="ag-heritage-strip ag-heritage-strip--center" aria-hidden="true"></span>
            <p class="ag-hero__sub">Deux experts passionnés qui combinent rigueur technique et hospitalité marocaine. Le SEO et l'IA, portés par une culture du détail et du travail bien fait.</p>
        </div>
    </section>

    <!-- À propos du bureau -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container ag-container--narrow">
            <div style="text-align:center;max-width:720px;margin:0 auto;">
                <span class="ag-tag">Pourquoi Marrakech ?</span>
                <h2 class="ag-section__title">Un pont entre <em>l'Europe</em> et l'Afrique</h2>
                <p style="color:var(--color-text-secondary);font-size:1.05rem;line-height:1.75;margin-top:24px;">
                    Marrakech n'est pas un choix de hasard. C'est la ville où le digital rencontre l'artisanat séculaire, où les jeunes talents francophones maîtrisent à la fois les dernières technologies et l'art de prendre son temps pour bien faire. Notre bureau marocain est le cœur de notre pôle <strong>SEO et Intelligence Artificielle</strong>, avec une équipe qui intervient sur tous les projets Alliance Groupe.
                </p>
                <p style="color:var(--color-text-secondary);font-size:1.05rem;line-height:1.75;margin-top:16px;">
                    La médina, les jardins de la Ménara, les souks de Bab Doukkala — autant de lieux qui inspirent notre approche : <strong>construire en profondeur, soigner chaque finition, penser sur le long terme</strong>.
                </p>
            </div>
        </div>
    </section>

    <!-- Équipe Marrakech (2 cartes détaillées) -->
    <section class="ag-section ag-section--darker">
        <div class="ag-container">
            <div style="text-align:center;max-width:640px;margin:0 auto 56px;">
                <span class="ag-tag ag-tag--green">L'équipe</span>
                <h2 class="ag-section__title">Les visages de <em>Marrakech</em></h2>
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
                        <span class="ag-mk-card__location">🇲🇦 Marrakech, Maroc</span>
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

    <!-- Autres bureaux (cross-link) -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div style="text-align:center;max-width:720px;margin:0 auto 40px;">
                <h2 class="ag-section__title">Nos autres <em>bureaux</em></h2>
                <p class="ag-section__desc" style="margin:0 auto;">Alliance Groupe, c'est trois villes, trois cultures, une seule équipe.</p>
            </div>
            <div class="ag-heritage-grid">
                <a href="<?php echo esc_url( home_url( '/a-propos' ) ); ?>" class="ag-heritage-card ag-heritage-card--fr" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇫🇷</span>
                    <h3>Nantes</h3>
                    <p>Le siège historique. Fabrizio, Kate, Laurent et Julie. Le cœur opérationnel et commercial de l'agence.</p>
                </a>
                <a href="<?php echo esc_url( home_url( '/notre-fondateur' ) ); ?>" class="ag-heritage-card ag-heritage-card--it" style="text-decoration:none;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇮🇹</span>
                    <h3>Naples</h3>
                    <p>L'histoire originelle. Carlito et son équipe technique. Les Quartieri Spagnoli, là où tout a commencé.</p>
                </a>
                <div class="ag-heritage-card ag-heritage-card--ma" style="opacity:.7;cursor:default;">
                    <span class="ag-heritage-card__flag" aria-hidden="true">🇲🇦</span>
                    <h3>Marrakech</h3>
                    <p>Vous êtes ici. Halim et Amina, les experts SEO et IA du groupe.</p>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part( 'template-parts/cta' ); ?>

</main>

<?php get_footer(); ?>
