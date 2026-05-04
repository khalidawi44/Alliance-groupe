<?php
/**
 * Front page — sections placeholders.
 * Tous les textes sont entre crochets : a personnaliser via Customizer
 * ou directement dans ce fichier (apres copie en child theme).
 *
 * @package AG_Starter_Association
 */
get_header();
?>

<main id="main">

    <!-- Hero -->
    <section class="ag-asso-hero">
        <div class="ag-asso-hero__inner">
            <span class="ag-asso-hero__tag"><?php echo esc_html( ag_asso_opt( 'ag_asso_slogan', '[Slogan court]' ) ); ?></span>
            <h1 class="ag-asso-hero__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_hero_title', '[Le grand titre de mobilisation]' ) ); ?></h1>
            <p class="ag-asso-hero__sub"><?php echo esc_html( ag_asso_opt( 'ag_asso_hero_sub', '[Description courte du combat — 2 lignes max]' ) ); ?></p>
            <div class="ag-asso-hero__ctas">
                <a href="<?php echo esc_url( ag_asso_opt( 'ag_asso_cta_url', '' ) ?: ag_asso_link( 'signer' ) ); ?>" class="ag-asso-btn ag-asso-btn--primary">
                    <?php echo esc_html( ag_asso_opt( 'ag_asso_cta_label', 'Rejoindre le mouvement' ) ); ?>
                </a>
                <?php if ( $cta2_label = ag_asso_opt( 'ag_asso_cta2_label', '' ) ) : ?>
                    <a href="<?php echo esc_url( ag_asso_opt( 'ag_asso_cta2_url', '' ) ?: ag_asso_link( 'don' ) ); ?>" class="ag-asso-btn ag-asso-btn--ghost">
                        <?php echo esc_html( $cta2_label ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
            $sig_count  = ag_asso_opt( 'ag_asso_signatures_count', '' );
            $sig_target = ag_asso_opt( 'ag_asso_signatures_target', '' );
            $sig_label  = ag_asso_opt( 'ag_asso_signatures_label', 'signataires' );
            if ( $sig_count ) : ?>
                <div class="ag-asso-hero__counter">
                    <strong><?php echo esc_html( $sig_count ); ?></strong>
                    <?php if ( $sig_target ) : ?>
                        <span>/ <?php echo esc_html( $sig_target ); ?></span>
                    <?php endif; ?>
                    <span class="ag-asso-hero__counter-lbl"><?php echo esc_html( $sig_label ); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Parallax manifeste -->
    <?php if ( ag_asso_opt( 'ag_asso_parallax_manifeste', '' ) ) : ?>
        <section class="ag-asso-parallax ag-asso-parallax--manifeste">
            <h2 class="ag-asso-parallax__title">Notre vision</h2>
            <p class="ag-asso-parallax__text">Une société plus juste, plus solidaire — c'est notre combat quotidien.</p>
        </section>
    <?php endif; ?>

    <!-- Manifeste -->
    <section class="ag-asso-section" id="manifeste">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Notre', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'manifeste', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">[Texte d'introduction du manifeste — à remplacer par le manifeste réel du mouvement.]</p>
            <div class="ag-asso-manifeste">
                <p>[Paragraphe 1 du manifeste — vision politique générale.]</p>
                <p>[Paragraphe 2 — diagnostic de la situation actuelle.]</p>
                <p>[Paragraphe 3 — projet de société proposé.]</p>
            </div>
        </div>
    </section>

    <!-- Parallax combats -->
    <?php if ( ag_asso_opt( 'ag_asso_parallax_combats', '' ) ) : ?>
        <section class="ag-asso-parallax ag-asso-parallax--combats">
            <h2 class="ag-asso-parallax__title">Nos combats</h2>
            <p class="ag-asso-parallax__text">Des actions concrètes, sur le terrain, partout en France.</p>
        </section>
    <?php endif; ?>

    <!-- Combats -->
    <section class="ag-asso-section ag-asso-section--alt" id="combats">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Nos', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'combats', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">[Liste des grands axes / thématiques portées par le mouvement.]</p>
            <div class="ag-asso-combats-grid">
                <?php for ( $i = 1; $i <= 6; $i++ ) : ?>
                    <article class="ag-asso-combat">
                        <h3>[Combat <?php echo $i; ?>]</h3>
                        <p>[Description courte — quel objectif, pourquoi, quelles propositions concrètes.]</p>
                        <a href="#"><?php esc_html_e( 'En savoir plus →', 'ag-starter-association' ); ?></a>
                    </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Parallax événements -->
    <?php if ( ag_asso_opt( 'ag_asso_parallax_evenements', '' ) ) : ?>
        <section class="ag-asso-parallax ag-asso-parallax--evenements">
            <h2 class="ag-asso-parallax__title">Mobilisations</h2>
            <p class="ag-asso-parallax__text">Marches, meetings, actions — rejoignez-nous sur le terrain.</p>
        </section>
    <?php endif; ?>

    <!-- Événements -->
    <section class="ag-asso-section" id="evenements">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Prochains', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'événements', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">[Marches, meetings, AG, débats publics.]</p>
            <div class="ag-asso-events">
                <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                    <article class="ag-asso-event">
                        <div class="ag-asso-event__date">
                            <span class="ag-asso-event__day">[XX]</span>
                            <span class="ag-asso-event__month">[mois]</span>
                        </div>
                        <div class="ag-asso-event__body">
                            <h3>[Titre de l'événement]</h3>
                            <p class="ag-asso-event__where">[Ville — lieu précis]</p>
                            <p>[Description courte — qui, pourquoi, comment participer.]</p>
                            <a href="#"><?php esc_html_e( "M'inscrire →", 'ag-starter-association' ); ?></a>
                        </div>
                    </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Groupes locaux -->
    <section class="ag-asso-section ag-asso-section--alt" id="groupes">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Trouver mon', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'groupe local', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">[Plus de XXX groupes partout en France. Cherchez le vôtre.]</p>
            <form class="ag-asso-search" action="#" method="get">
                <input type="text" name="cp" placeholder="<?php esc_attr_e( 'Code postal ou ville', 'ag-starter-association' ); ?>">
                <button type="submit"><?php esc_html_e( 'Trouver', 'ag-starter-association' ); ?></button>
            </form>
            <p class="ag-asso-search__note">[Carte interactive à intégrer ici — placeholder.]</p>
        </div>
    </section>

    <!-- Actualités -->
    <section class="ag-asso-section" id="actu">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Dernières', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'actualités', 'ag-starter-association' ); ?></em></h2>
            <div class="ag-asso-actu-grid">
                <?php
                $recent = get_posts( array( 'numberposts' => 3 ) );
                if ( $recent ) :
                    foreach ( $recent as $post ) : setup_postdata( $post ); ?>
                        <article class="ag-asso-actu">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="ag-asso-actu__img"><?php the_post_thumbnail( 'medium' ); ?></div>
                            <?php endif; ?>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="ag-asso-actu__date"><?php echo get_the_date(); ?></p>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 24 ); ?></p>
                        </article>
                    <?php endforeach; wp_reset_postdata();
                else :
                    for ( $i = 1; $i <= 3; $i++ ) : ?>
                        <article class="ag-asso-actu">
                            <h3><a href="#">[Titre article <?php echo $i; ?>]</a></h3>
                            <p class="ag-asso-actu__date">[Date]</p>
                            <p>[Extrait de l'article — 2 lignes.]</p>
                        </article>
                    <?php endfor;
                endif; ?>
            </div>
        </div>
    </section>

    <!-- Signer -->
    <section class="ag-asso-section ag-asso-section--cta" id="signer">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Signez', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'l\'appel', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">[Texte d'appel — pourquoi signer, quel engagement.]</p>
            <form class="ag-asso-form" action="#" method="post">
                <input type="text" name="prenom" placeholder="<?php esc_attr_e( 'Prénom', 'ag-starter-association' ); ?>" required>
                <input type="text" name="nom" placeholder="<?php esc_attr_e( 'Nom', 'ag-starter-association' ); ?>" required>
                <input type="email" name="email" placeholder="<?php esc_attr_e( 'Email', 'ag-starter-association' ); ?>" required>
                <input type="text" name="cp" placeholder="<?php esc_attr_e( 'Code postal', 'ag-starter-association' ); ?>" required>
                <label class="ag-asso-form__rgpd">
                    <input type="checkbox" required>
                    <span><?php esc_html_e( "J'accepte que mes données soient traitées dans le cadre de cet engagement. Conformément au RGPD, je peux les modifier ou les supprimer.", 'ag-starter-association' ); ?></span>
                </label>
                <button type="submit" class="ag-asso-btn ag-asso-btn--primary"><?php esc_html_e( 'Je signe', 'ag-starter-association' ); ?></button>
            </form>
        </div>
    </section>

    <!-- Parallax don -->
    <?php if ( ag_asso_opt( 'ag_asso_parallax_don', '' ) ) : ?>
        <section class="ag-asso-parallax ag-asso-parallax--don">
            <h2 class="ag-asso-parallax__title">Soutenir le mouvement</h2>
            <p class="ag-asso-parallax__text">Chaque don nous donne plus de moyens d'action.</p>
        </section>
    <?php endif; ?>

    <!-- Don -->
    <section class="ag-asso-section ag-asso-section--alt" id="don">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Faire un', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'don', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">[Pourquoi un don. Avantage fiscal éventuel. Plafond légal.]</p>
            <div class="ag-asso-don-grid">
                <?php foreach ( array( 5, 20, 50, 100 ) as $amount ) : ?>
                    <a href="#" class="ag-asso-don-card">
                        <span class="ag-asso-don-card__amount"><?php echo $amount; ?>€</span>
                        <span class="ag-asso-don-card__note">[Note coût réel après déduction]</span>
                    </a>
                <?php endforeach; ?>
                <a href="#" class="ag-asso-don-card ag-asso-don-card--free">
                    <span class="ag-asso-don-card__amount"><?php esc_html_e( 'Libre', 'ag-starter-association' ); ?></span>
                </a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
