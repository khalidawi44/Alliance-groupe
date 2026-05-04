<?php
/**
 * Front page — sections placeholders.
 * Tous les textes sont entre crochets : a personnaliser via Customizer
 * ou directement dans ce fichier (apres copie en child theme).
 *
 * @package AG_Starter_LFI
 */
get_header();
?>

<main id="main">

    <!-- Hero -->
    <section class="ag-lfi-hero">
        <div class="ag-lfi-hero__inner">
            <span class="ag-lfi-hero__tag"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_slogan', '[Slogan court]' ) ); ?></span>
            <h1 class="ag-lfi-hero__title"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_hero_title', '[Le grand titre de mobilisation]' ) ); ?></h1>
            <p class="ag-lfi-hero__sub"><?php echo esc_html( ag_lfi_opt( 'ag_lfi_hero_sub', '[Description courte du combat — 2 lignes max]' ) ); ?></p>
            <a href="<?php echo esc_url( ag_lfi_opt( 'ag_lfi_cta_url', '#signer' ) ); ?>" class="ag-lfi-btn ag-lfi-btn--primary">
                <?php echo esc_html( ag_lfi_opt( 'ag_lfi_cta_label', 'Rejoindre le mouvement' ) ); ?>
            </a>
        </div>
    </section>

    <!-- Manifeste -->
    <section class="ag-lfi-section" id="manifeste">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Notre', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'manifeste', 'ag-starter-lfi' ); ?></em></h2>
            <p class="ag-lfi-section__lead">[Texte d'introduction du manifeste — à remplacer par le manifeste réel du mouvement.]</p>
            <div class="ag-lfi-manifeste">
                <p>[Paragraphe 1 du manifeste — vision politique générale.]</p>
                <p>[Paragraphe 2 — diagnostic de la situation actuelle.]</p>
                <p>[Paragraphe 3 — projet de société proposé.]</p>
            </div>
        </div>
    </section>

    <!-- Combats -->
    <section class="ag-lfi-section ag-lfi-section--alt" id="combats">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Nos', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'combats', 'ag-starter-lfi' ); ?></em></h2>
            <p class="ag-lfi-section__lead">[Liste des grands axes / thématiques portées par le mouvement.]</p>
            <div class="ag-lfi-combats-grid">
                <?php for ( $i = 1; $i <= 6; $i++ ) : ?>
                    <article class="ag-lfi-combat">
                        <h3>[Combat <?php echo $i; ?>]</h3>
                        <p>[Description courte — quel objectif, pourquoi, quelles propositions concrètes.]</p>
                        <a href="#"><?php esc_html_e( 'En savoir plus →', 'ag-starter-lfi' ); ?></a>
                    </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Événements -->
    <section class="ag-lfi-section" id="evenements">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Prochains', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'événements', 'ag-starter-lfi' ); ?></em></h2>
            <p class="ag-lfi-section__lead">[Marches, meetings, AG, débats publics.]</p>
            <div class="ag-lfi-events">
                <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                    <article class="ag-lfi-event">
                        <div class="ag-lfi-event__date">
                            <span class="ag-lfi-event__day">[XX]</span>
                            <span class="ag-lfi-event__month">[mois]</span>
                        </div>
                        <div class="ag-lfi-event__body">
                            <h3>[Titre de l'événement]</h3>
                            <p class="ag-lfi-event__where">[Ville — lieu précis]</p>
                            <p>[Description courte — qui, pourquoi, comment participer.]</p>
                            <a href="#"><?php esc_html_e( "M'inscrire →", 'ag-starter-lfi' ); ?></a>
                        </div>
                    </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Groupes locaux -->
    <section class="ag-lfi-section ag-lfi-section--alt" id="groupes">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Trouver mon', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'groupe local', 'ag-starter-lfi' ); ?></em></h2>
            <p class="ag-lfi-section__lead">[Plus de XXX groupes partout en France. Cherchez le vôtre.]</p>
            <form class="ag-lfi-search" action="#" method="get">
                <input type="text" name="cp" placeholder="<?php esc_attr_e( 'Code postal ou ville', 'ag-starter-lfi' ); ?>">
                <button type="submit"><?php esc_html_e( 'Trouver', 'ag-starter-lfi' ); ?></button>
            </form>
            <p class="ag-lfi-search__note">[Carte interactive à intégrer ici — placeholder.]</p>
        </div>
    </section>

    <!-- Actualités -->
    <section class="ag-lfi-section" id="actu">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Dernières', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'actualités', 'ag-starter-lfi' ); ?></em></h2>
            <div class="ag-lfi-actu-grid">
                <?php
                $recent = get_posts( array( 'numberposts' => 3 ) );
                if ( $recent ) :
                    foreach ( $recent as $post ) : setup_postdata( $post ); ?>
                        <article class="ag-lfi-actu">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="ag-lfi-actu__img"><?php the_post_thumbnail( 'medium' ); ?></div>
                            <?php endif; ?>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="ag-lfi-actu__date"><?php echo get_the_date(); ?></p>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 24 ); ?></p>
                        </article>
                    <?php endforeach; wp_reset_postdata();
                else :
                    for ( $i = 1; $i <= 3; $i++ ) : ?>
                        <article class="ag-lfi-actu">
                            <h3><a href="#">[Titre article <?php echo $i; ?>]</a></h3>
                            <p class="ag-lfi-actu__date">[Date]</p>
                            <p>[Extrait de l'article — 2 lignes.]</p>
                        </article>
                    <?php endfor;
                endif; ?>
            </div>
        </div>
    </section>

    <!-- Signer -->
    <section class="ag-lfi-section ag-lfi-section--cta" id="signer">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Signez', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'l\'appel', 'ag-starter-lfi' ); ?></em></h2>
            <p class="ag-lfi-section__lead">[Texte d'appel — pourquoi signer, quel engagement.]</p>
            <form class="ag-lfi-form" action="#" method="post">
                <input type="text" name="prenom" placeholder="<?php esc_attr_e( 'Prénom', 'ag-starter-lfi' ); ?>" required>
                <input type="text" name="nom" placeholder="<?php esc_attr_e( 'Nom', 'ag-starter-lfi' ); ?>" required>
                <input type="email" name="email" placeholder="<?php esc_attr_e( 'Email', 'ag-starter-lfi' ); ?>" required>
                <input type="text" name="cp" placeholder="<?php esc_attr_e( 'Code postal', 'ag-starter-lfi' ); ?>" required>
                <label class="ag-lfi-form__rgpd">
                    <input type="checkbox" required>
                    <span><?php esc_html_e( "J'accepte que mes données soient traitées dans le cadre de cet engagement. Conformément au RGPD, je peux les modifier ou les supprimer.", 'ag-starter-lfi' ); ?></span>
                </label>
                <button type="submit" class="ag-lfi-btn ag-lfi-btn--primary"><?php esc_html_e( 'Je signe', 'ag-starter-lfi' ); ?></button>
            </form>
        </div>
    </section>

    <!-- Don -->
    <section class="ag-lfi-section ag-lfi-section--alt" id="don">
        <div class="ag-lfi-container">
            <h2 class="ag-lfi-section__title"><?php esc_html_e( 'Faire un', 'ag-starter-lfi' ); ?> <em><?php esc_html_e( 'don', 'ag-starter-lfi' ); ?></em></h2>
            <p class="ag-lfi-section__lead">[Pourquoi un don. Avantage fiscal éventuel. Plafond légal.]</p>
            <div class="ag-lfi-don-grid">
                <?php foreach ( array( 5, 20, 50, 100 ) as $amount ) : ?>
                    <a href="#" class="ag-lfi-don-card">
                        <span class="ag-lfi-don-card__amount"><?php echo $amount; ?>€</span>
                        <span class="ag-lfi-don-card__note">[Note coût réel après déduction]</span>
                    </a>
                <?php endforeach; ?>
                <a href="#" class="ag-lfi-don-card ag-lfi-don-card--free">
                    <span class="ag-lfi-don-card__amount"><?php esc_html_e( 'Libre', 'ag-starter-lfi' ); ?></span>
                </a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
