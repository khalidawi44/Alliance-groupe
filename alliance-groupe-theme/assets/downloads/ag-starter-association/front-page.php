<?php
/**
 * Front page — sections placeholders.
 * Apparence stylee preservee : les sections PHP rendent leur design
 * d'origine (parallax, hero, combats grid, etc.). Tous les textes
 * (titres, leads, contenu) sont editables sans coder via :
 *   - Apparence > Personnaliser > Contenu accueil (textes des sections)
 *   - Combats / Evenements / Articles (admin) pour les contenus dynamiques
 *   - Pages > Manifeste / Qui sommes-nous (Gutenberg) pour les pages liees
 *
 * @package AG_Starter_Association
 */
get_header();
?>

<main id="main">

    <?php // Zone Gutenberg optionnelle : si l'utilisateur ajoute du
    // contenu sur la page Accueil (Pages > Accueil), il s'affiche EN PLUS
    // au-dessus des sections design — utile pour annonce de derniere minute,
    // bandeau de campagne, etc. Ne casse jamais le design d'origine.
    if ( have_posts() ) : while ( have_posts() ) : the_post();
        if ( trim( get_the_content() ) ) : ?>
            <section class="ag-asso-section ag-asso-custom" style="padding:60px 24px;">
                <div class="ag-asso-container ag-asso-custom__inner">
                    <?php the_content(); ?>
                </div>
            </section>
        <?php endif;
    endwhile; rewind_posts(); endif; ?>

    <!-- Hero -->
    <section class="ag-asso-hero">
        <div class="ag-asso-hero__inner">
            <span class="ag-asso-hero__tag"><?php echo esc_html( ag_asso_opt( 'ag_asso_slogan', 'Pour une société plus juste' ) ); ?></span>
            <h1 class="ag-asso-hero__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_hero_title', 'Ensemble, changeons les choses' ) ); ?></h1>
            <p class="ag-asso-hero__sub"><?php echo esc_html( ag_asso_opt( 'ag_asso_hero_sub', 'Plus de 12 000 citoyennes et citoyens engagés pour la justice sociale, climatique et démocratique.' ) ); ?></p>
            <div class="ag-asso-hero__ctas">
                <a href="<?php echo esc_url( ag_asso_opt( 'ag_asso_cta_url', '' ) ?: ag_asso_link( 'signer' ) ); ?>" class="ag-asso-btn ag-asso-btn--primary">
                    <?php echo esc_html( ag_asso_opt( 'ag_asso_cta_label', 'Rejoindre le mouvement' ) ); ?>
                </a>
                <?php if ( ag_asso_opt( 'ag_asso_show_cta_secondaire', 1 ) && $cta2_label = ag_asso_opt( 'ag_asso_cta2_label', '' ) ) : ?>
                    <a href="<?php echo esc_url( ag_asso_opt( 'ag_asso_cta2_url', '' ) ?: ag_asso_link( 'don' ) ); ?>" class="ag-asso-btn ag-asso-btn--ghost">
                        <?php echo esc_html( $cta2_label ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
            $sig_count  = ag_asso_opt( 'ag_asso_signatures_count', '' );
            $sig_target = ag_asso_opt( 'ag_asso_signatures_target', '' );
            $sig_label  = ag_asso_opt( 'ag_asso_signatures_label', 'signataires' );
            if ( $sig_count && ag_asso_opt( 'ag_asso_show_compteur_sig', 1 ) ) : ?>
                <a href="<?php echo esc_url( ag_asso_link( 'signer' ) ); ?>" class="ag-asso-hero__counter" aria-label="<?php esc_attr_e( 'Voir la pétition', 'ag-starter-association' ); ?>">
                    <strong><?php echo esc_html( $sig_count ); ?></strong>
                    <?php if ( $sig_target ) : ?>
                        <span>/ <?php echo esc_html( $sig_target ); ?></span>
                    <?php endif; ?>
                    <span class="ag-asso-hero__counter-lbl"><?php echo esc_html( $sig_label ); ?> →</span>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Parallax manifeste -->
    <?php $parallax_manifeste_img = ag_asso_opt( 'ag_asso_parallax_manifeste_image', '' ); ?>
    <section class="ag-asso-parallax ag-asso-parallax--manifeste"<?php if ( $parallax_manifeste_img ) echo ' style="background-image:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),url(' . esc_url( $parallax_manifeste_img ) . ');"'; ?>>
        <h2 class="ag-asso-parallax__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_manifeste_title', 'Notre vision' ) ); ?></h2>
        <p class="ag-asso-parallax__text"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_manifeste_text', 'Une société plus juste, plus solidaire — c\'est notre combat quotidien.' ) ); ?></p>
    </section>

    <!-- Manifeste -->
    <?php if ( ag_asso_opt( 'ag_asso_show_manifeste', 1 ) ) :
        list( $manif_title, $manif_lead ) = ag_asso_page_section_text( 'manifeste', 'Notre manifeste', 'Nous croyons qu\'une autre société est possible — plus juste, plus écologique, plus démocratique. Voici nos engagements.' );
    ?>
    <section class="ag-asso-section" id="manifeste">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $manif_title ); ?></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( $manif_lead ); ?></p>
            <div class="ag-asso-manifeste">
                <?php
                // Le corps du manifeste se modifie via Pages > Manifeste
                // (editeur Gutenberg standard). Si la page n'existe pas
                // ou est vide, on affiche un message d'admin guidant
                // l'utilisateur (visible uniquement aux admins connectes).
                $manif_page = get_page_by_path( 'manifeste' );
                if ( $manif_page && trim( $manif_page->post_content ) ) {
                    echo apply_filters( 'the_content', $manif_page->post_content );
                } elseif ( current_user_can( 'edit_pages' ) ) {
                    $edit_url = $manif_page ? get_edit_post_link( $manif_page->ID ) : admin_url( 'post-new.php?post_type=page' );
                    echo '<p style="opacity:.7;font-style:italic;">[Editez le contenu du manifeste dans <a href="' . esc_url( $edit_url ) . '">Pages > Manifeste</a> avec l\'editeur de blocs.]</p>';
                }
                ?>
            </div>
            <p style="text-align:center;margin-top:24px;">
                <a class="ag-asso-btn ag-asso-btn--primary" href="<?php echo esc_url( ag_asso_link( 'manifeste' ) ); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_manifeste_btn_label', 'Lire le manifeste complet →' ) ); ?></a>
            </p>
        </div>
    </section>

    <?php endif; // /manifeste ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_combats', 1 ) ) : ?>
    <!-- Parallax combats -->
    <?php $parallax_combats_img = ag_asso_opt( 'ag_asso_parallax_combats_image', '' ); ?>
    <section class="ag-asso-parallax ag-asso-parallax--combats"<?php if ( $parallax_combats_img ) echo ' style="background-image:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),url(' . esc_url( $parallax_combats_img ) . ');"'; ?>>
        <h2 class="ag-asso-parallax__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_combats_title', 'Nos combats' ) ); ?></h2>
        <p class="ag-asso-parallax__text"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_combats_text', 'Des actions concrètes, sur le terrain, partout en France.' ) ); ?></p>
    </section>

    <!-- Combats -->
    <?php list( $combats_title, $combats_lead ) = ag_asso_page_section_text( 'combats', 'Nos combats', 'Six grandes campagnes que nous portons cette année, sur le terrain et dans les institutions.' ); ?>
    <section class="ag-asso-section ag-asso-section--alt" id="combats">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $combats_title ); ?></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( $combats_lead ); ?></p>
            <div class="ag-asso-combats-grid">
                <?php
                // Lecture dynamique : CPT ag_combat (modifiables via Combats > admin).
                // Fallback hardcode si CPT vide.
                $colors = array( '#1F8A3D', '#3B5998', '#E10F1A', '#8B1A8B', '#0A0A0D', '#FFD23F' );
                $emojis = array( '🌍', '🏠', '🏥', '🗳️', '🔍', '⚖️' );
                $q_combats = new WP_Query( array( 'post_type' => 'ag_combat', 'posts_per_page' => 6 ) );
                if ( $q_combats->have_posts() ) :
                    $i = 0;
                    while ( $q_combats->have_posts() ) : $q_combats->the_post();
                        // Meta box admin > Combats > apparence : emoji + couleur per combat.
                        // Fallback sur le cycle de couleurs/emojis par defaut si meta vide.
                        $meta_emoji = get_post_meta( get_the_ID(), '_ag_combat_emoji', true );
                        $meta_color = get_post_meta( get_the_ID(), '_ag_combat_color', true );
                        $color = $meta_color ?: $colors[ $i % count( $colors ) ];
                        $emoji = $meta_emoji ?: $emojis[ $i % count( $emojis ) ];
                        $i++; ?>
                        <article class="ag-asso-combat">
                            <div class="ag-asso-combat__icon" style="background:<?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $emoji ); ?></div>
                            <h3><?php the_title(); ?></h3>
                            <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                            <a href="<?php the_permalink(); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_combats_btn_label', 'En savoir plus →' ) ); ?></a>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                else :
                    $combats_fb = array(
                        array( '🌍', 'Justice climatique',     '#1F8A3D', 'Pour une transition écologique qui ne pèse pas sur les plus modestes.' ),
                        array( '🏠', 'Logement digne',         '#3B5998', 'Plafonnement effectif des loyers, réquisition des vacants.' ),
                        array( '🏥', 'Service public fort',    '#E10F1A', 'Refonder l\'hôpital, l\'école, les transports publics.' ),
                        array( '🗳️', 'Démocratie réelle',      '#8B1A8B', 'RIC, assemblées tirées au sort, reconnaissance du vote blanc.' ),
                        array( '🔍', 'Transparence publique', '#0A0A0D', 'Open data, traçabilité des marchés publics, registre des lobbies.' ),
                        array( '⚖️', 'Égalité réelle',         '#FFD23F', 'Lutte contre toutes les discriminations.' ),
                    );
                    foreach ( $combats_fb as $c ) : ?>
                        <article class="ag-asso-combat">
                            <div class="ag-asso-combat__icon" style="background:<?php echo esc_attr( $c[2] ); ?>;"><?php echo $c[0]; ?></div>
                            <h3><?php echo esc_html( $c[1] ); ?></h3>
                            <p><?php echo esc_html( $c[3] ); ?></p>
                            <a href="<?php echo esc_url( home_url( '/combats/' ) ); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_combats_btn_label', 'En savoir plus →' ) ); ?></a>
                        </article>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </section>

    <?php endif; // /combats ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_evenements', 1 ) ) : ?>
    <!-- Parallax événements -->
    <?php $parallax_evenements_img = ag_asso_opt( 'ag_asso_parallax_evenements_image', '' ); ?>
    <section class="ag-asso-parallax ag-asso-parallax--evenements"<?php if ( $parallax_evenements_img ) echo ' style="background-image:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),url(' . esc_url( $parallax_evenements_img ) . ');"'; ?>>
        <h2 class="ag-asso-parallax__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_evenements_title', 'Mobilisations' ) ); ?></h2>
        <p class="ag-asso-parallax__text"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_evenements_text', 'Marches, meetings, actions — rejoignez-nous sur le terrain.' ) ); ?></p>
    </section>

    <!-- Événements -->
    <?php list( $evt_title, $evt_lead ) = ag_asso_page_section_text( 'evenements', 'Prochains événements', 'Marches, meetings, assemblées générales, débats publics — venez nous rencontrer près de chez vous.' ); ?>
    <section class="ag-asso-section" id="evenements">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $evt_title ); ?></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( $evt_lead ); ?></p>
            <div class="ag-asso-events">
                <?php
                // Lecture dynamique du CPT ag_evenement (modifiable via
                // Evenements > admin). Trie par date (meta _ag_event_date)
                // ASC. Fallback : 3 evenements hardcodes si CPT vide.
                $months_fr = array( 1 => 'JAN', 'FEV', 'MARS', 'AVR', 'MAI', 'JUIN', 'JUIL', 'AOUT', 'SEPT', 'OCT', 'NOV', 'DEC' );
                $q_evts = new WP_Query( array(
                    'post_type'      => 'ag_evenement',
                    'posts_per_page' => 3,
                    'meta_key'       => '_ag_event_date',
                    'orderby'        => 'meta_value',
                    'order'          => 'ASC',
                ) );
                if ( $q_evts->have_posts() ) :
                    while ( $q_evts->have_posts() ) : $q_evts->the_post();
                        $date  = get_post_meta( get_the_ID(), '_ag_event_date', true );
                        $city  = get_post_meta( get_the_ID(), '_ag_event_city',  true );
                        $place = get_post_meta( get_the_ID(), '_ag_event_place', true );
                        $day = $month_lbl = '';
                        if ( $date && ( $ts = strtotime( $date ) ) ) {
                            $day       = date( 'd', $ts );
                            $month_lbl = isset( $months_fr[ (int) date( 'n', $ts ) ] ) ? $months_fr[ (int) date( 'n', $ts ) ] : '';
                        }
                        $where = trim( $city . ( $place ? ' — ' . $place : '' ), ' —' );
                        ?>
                        <a class="ag-asso-event ag-asso-event--link" href="<?php the_permalink(); ?>">
                            <div class="ag-asso-event__date">
                                <span class="ag-asso-event__day"><?php echo esc_html( $day ?: '—' ); ?></span>
                                <span class="ag-asso-event__month"><?php echo esc_html( $month_lbl ); ?></span>
                            </div>
                            <div class="ag-asso-event__body">
                                <h3><?php the_title(); ?></h3>
                                <?php if ( $where ) : ?><p class="ag-asso-event__where"><?php echo esc_html( $where ); ?></p><?php endif; ?>
                                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
                                <span class="ag-asso-event__cta"><?php esc_html_e( "M'inscrire →", 'ag-starter-association' ); ?></span>
                            </div>
                        </a>
                    <?php endwhile;
                    wp_reset_postdata();
                else :
                    // Pas de CPT : on lit les 6 creneaux editables du Customizer
                    // (Personnaliser > Evenements (accueil)). Chaque creneau a un
                    // toggle "Afficher" : decocher = retirer l'evenement (y compris
                    // les evenements de demonstration). Si aucun n'est affiche, la
                    // grille reste vide (aucun evenement code en dur).
                    for ( $i = 1; $i <= 6; $i++ ) :
                        if ( ! ag_asso_opt( "ag_asso_event_on_$i", $i <= 3 ? 1 : 0 ) ) {
                            continue;
                        }
                        $ev_title = ag_asso_opt( "ag_asso_event_title_$i", '' );
                        if ( ! $ev_title ) {
                            continue;
                        }
                        $ev_day   = ag_asso_opt( "ag_asso_event_day_$i", '' );
                        $ev_month = ag_asso_opt( "ag_asso_event_month_$i", '' );
                        $ev_place = ag_asso_opt( "ag_asso_event_place_$i", '' );
                        $ev_desc  = ag_asso_opt( "ag_asso_event_desc_$i", '' );
                        $ev_url   = ag_asso_opt( "ag_asso_event_url_$i", '' ) ?: home_url( '/evenements/' );
                        ?>
                        <a class="ag-asso-event ag-asso-event--link" href="<?php echo esc_url( $ev_url ); ?>">
                            <div class="ag-asso-event__date">
                                <span class="ag-asso-event__day"><?php echo esc_html( $ev_day ?: '—' ); ?></span>
                                <span class="ag-asso-event__month"><?php echo esc_html( $ev_month ); ?></span>
                            </div>
                            <div class="ag-asso-event__body">
                                <h3><?php echo esc_html( $ev_title ); ?></h3>
                                <?php if ( $ev_place ) : ?><p class="ag-asso-event__where"><?php echo esc_html( $ev_place ); ?></p><?php endif; ?>
                                <?php if ( $ev_desc ) : ?><p><?php echo esc_html( $ev_desc ); ?></p><?php endif; ?>
                                <span class="ag-asso-event__cta"><?php esc_html_e( "M'inscrire →", 'ag-starter-association' ); ?></span>
                            </div>
                        </a>
                    <?php endfor;
                endif; ?>
            </div>
            <p style="text-align:center;margin-top:32px;">
                <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>" class="ag-asso-btn ag-asso-btn--primary">
                    <?php echo esc_html( ag_asso_opt( 'ag_asso_evenements_btn_label', '📅 Tous les événements + calendrier' ) ); ?>
                </a>
            </p>
        </div>
    </section>

    <?php endif; // /evenements ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_groupes', 1 ) ) : ?>
    <!-- Groupes locaux -->
    <section class="ag-asso-section ag-asso-section--alt ag-asso-section--map" id="groupes">
        <div class="ag-asso-container">
            <?php list( $grp_title, $grp_lead ) = ag_asso_page_section_text( 'groupes', 'Groupes locaux', 'Trouvez le groupe local actif près de chez vous via la carte officielle Action Populaire.' ); ?>
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $grp_title ); ?></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( $grp_lead ); ?></p>
            <p style="text-align:center;margin:24px 0;">
                <a href="<?php echo esc_url( home_url( '/groupes/' ) ); ?>" class="ag-asso-btn ag-asso-btn--primary">
                    🗺️ <?php echo esc_html( ag_asso_opt( 'ag_asso_groupes_btn_label', 'Trouver mon groupe local' ) ); ?>
                </a>
            </p>
            <p class="ag-asso-search__note" style="text-align:center;"><?php echo wp_kses_post( ag_asso_opt( 'ag_asso_groupes_search_note', 'Pas de groupe près de chez vous ? <a href="/groupes/">Contactez-nous</a> — nous vous accompagnons pour créer le vôtre.' ) ); ?></p>
            <div class="ag-asso-stats">
                <div><strong><?php echo esc_html( ag_asso_opt( 'ag_asso_stat1_value', '47' ) ); ?></strong><span><?php echo esc_html( ag_asso_opt( 'ag_asso_stat1_label', 'groupes locaux' ) ); ?></span></div>
                <div><strong><?php echo esc_html( ag_asso_opt( 'ag_asso_stat2_value', '2 130' ) ); ?></strong><span><?php echo esc_html( ag_asso_opt( 'ag_asso_stat2_label', 'adhérents' ) ); ?></span></div>
                <div><strong><?php echo esc_html( ag_asso_opt( 'ag_asso_stat3_value', '12 480' ) ); ?></strong><span><?php echo esc_html( ag_asso_opt( 'ag_asso_stat3_label', 'signataires' ) ); ?></span></div>
            </div>
        </div>
    </section>

    <?php endif; // /groupes ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_actu', 1 ) ) : ?>
    <!-- Actualités -->
    <section class="ag-asso-section" id="actu">
        <div class="ag-asso-container">
            <?php list( $actu_title, $actu_lead ) = ag_asso_page_section_text( 'actu', 'Dernières actualités', '' ); ?>
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $actu_title ); ?></h2>
            <?php if ( $actu_lead ) : ?>
                <p class="ag-asso-section__lead"><?php echo esc_html( $actu_lead ); ?></p>
            <?php endif; ?>
            <div class="ag-asso-actu-grid">
                <?php
                $recent = get_posts( array( 'numberposts' => 3 ) );
                if ( $recent ) :
                    foreach ( $recent as $post ) : setup_postdata( $post ); ?>
                        <article class="ag-asso-actu">
                            <a href="<?php the_permalink(); ?>" class="ag-asso-actu__imglink">
                                <?php echo ag_asso_post_visual_html( get_the_ID(), 'medium', 'ag-asso-actu__img' ); ?>
                            </a>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="ag-asso-actu__date"><?php echo get_the_date(); ?></p>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 24 ); ?></p>
                        </article>
                    <?php endforeach; wp_reset_postdata();
                else :
                    $sample_news = array(
                        array( 'Hôpital public : nous publions notre contre-budget',     '12 mai 2026', 'Notre groupe de travail santé publie aujourd\'hui un rapport de 60 pages chiffrant un plan d\'urgence pour l\'hôpital. À télécharger librement.' ),
                        array( 'Pétition climat : 47 000 signatures en 3 semaines',        '5 mai 2026',  'L\'objectif de 50 000 est désormais à portée. Le dépôt à l\'Assemblée est prévu pour la fin du mois. Merci à tou·tes.' ),
                        array( 'Nouveau groupe local à Saint-Étienne — bienvenue !',      '28 avr 2026', 'Le 47e groupe local du mouvement vient d\'être officialisé. Première réunion publique le 18 mai à la Maison des Syndicats.' ),
                    );
                    foreach ( $sample_news as $n ) : ?>
                        <article class="ag-asso-actu">
                            <h3><a href="<?php echo esc_url( home_url( '/actu/' ) ); ?>"><?php echo esc_html( $n[0] ); ?></a></h3>
                            <p class="ag-asso-actu__date"><?php echo esc_html( $n[1] ); ?></p>
                            <p><?php echo esc_html( $n[2] ); ?></p>
                        </article>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </section>

    <?php endif; // /actu ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_equipe', 1 ) ) : ?>
    <!-- Équipe -->
    <section class="ag-asso-section ag-asso-section--team" id="equipe">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_equipe_title_pre', 'Notre' ) ); ?> <em><?php echo esc_html( ag_asso_opt( 'ag_asso_equipe_title_em', 'équipe' ) ); ?></em></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( ag_asso_opt( 'ag_asso_equipe_lead', 'Bénévoles, élu·es au CA, salarié·es — celles et ceux qui font vivre le mouvement au quotidien.' ) ); ?></p>
            <div class="ag-asso-team__grid">
                <?php
                $colors = array( '#E10F1A', '#FFD23F', '#0A0A0D', '#1F8A3D', '#3B5998', '#8B1A8B' );
                $team_fb = array(
                    1 => array( 'Yacine Bouzid',   'Vice-président — pôle juridique' ),
                    2 => array( 'Léa Marchand',    'Trésorière' ),
                    3 => array( 'Mehdi El Amrani', 'Secrétaire général' ),
                    4 => array( 'Sophie Tremblay', 'Coordination groupes locaux' ),
                    5 => array( 'Thomas Vasseur',  'Responsable communication' ),
                    6 => array( 'Aïcha Diallo',    'Animation jeunes engagés' ),
                );
                for ( $i = 1; $i <= 12; $i++ ) :
                    $on    = (int) ag_asso_opt( "ag_asso_about_team_on_$i", $i <= 6 ? 1 : 0 );
                    if ( ! $on ) continue;
                    $photo = ag_asso_opt( "ag_asso_about_team_photo_$i", '' );
                    $name  = ag_asso_opt( "ag_asso_about_team_name_$i", isset( $team_fb[ $i ] ) ? $team_fb[ $i ][0] : '' );
                    $role  = ag_asso_opt( "ag_asso_about_team_role_$i", isset( $team_fb[ $i ] ) ? $team_fb[ $i ][1] : '' );
                    if ( ! $name ) continue;
                    $initials = '';
                    foreach ( explode( ' ', $name ) as $part ) {
                        if ( $part ) $initials .= mb_strtoupper( mb_substr( $part, 0, 1 ) );
                    }
                    $color = $colors[ ( $i - 1 ) % count( $colors ) ];
                    ?>
                    <article class="ag-asso-team__card">
                        <?php if ( $photo ) : ?>
                            <img class="ag-asso-team__photo" src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>">
                        <?php else : ?>
                            <div class="ag-asso-team__photo ag-asso-team__photo--placeholder" style="background:<?php echo esc_attr( $color ); ?>;">
                                <span><?php echo esc_html( $initials ); ?></span>
                            </div>
                        <?php endif; ?>
                        <h4 class="ag-asso-team__name"><?php echo esc_html( $name ); ?></h4>
                        <p class="ag-asso-team__role"><?php echo esc_html( $role ); ?></p>
                    </article>
                <?php endfor; ?>
            </div>
            <p style="text-align:center;margin-top:32px;">
                <a class="ag-asso-btn ag-asso-btn--ghost ag-asso-btn--ghost-dark" href="<?php echo esc_url( home_url( '/qui-sommes-nous/' ) ); ?>"><?php echo esc_html( ag_asso_opt( 'ag_asso_equipe_btn_label', 'Découvrir toute l\'équipe →' ) ); ?></a>
            </p>
        </div>
    </section>

    <?php endif; // /equipe ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_signer', 1 ) ) : ?>
    <!-- Signer -->
    <section class="ag-asso-section ag-asso-section--cta" id="signer">
        <div class="ag-asso-container">
            <?php list( $sign_title, $sign_lead ) = ag_asso_page_section_text( 'signer', 'Signez l\'appel', 'Pour une société plus juste, écologique et démocratique. Signer, c\'est s\'engager à recevoir nos appels à mobilisation et à les relayer autour de soi.' ); ?>
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $sign_title ); ?></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( $sign_lead ); ?></p>
            <form class="ag-asso-form" action="#" method="post">
                <input type="text" name="prenom" placeholder="<?php esc_attr_e( 'Prénom', 'ag-starter-association' ); ?>" required>
                <input type="text" name="nom" placeholder="<?php esc_attr_e( 'Nom', 'ag-starter-association' ); ?>" required>
                <input type="email" name="email" placeholder="<?php esc_attr_e( 'Email', 'ag-starter-association' ); ?>" required>
                <input type="text" name="cp" placeholder="<?php esc_attr_e( 'Code postal', 'ag-starter-association' ); ?>" required>
                <label class="ag-asso-form__rgpd">
                    <input type="checkbox" required>
                    <span><?php echo esc_html( ag_asso_opt( 'ag_asso_signer_rgpd', 'J\'accepte que mes données soient traitées dans le cadre de cet engagement. Conformément au RGPD, je peux les modifier ou les supprimer.' ) ); ?></span>
                </label>
                <button type="submit" class="ag-asso-btn ag-asso-btn--primary"><?php echo esc_html( ag_asso_opt( 'ag_asso_signer_btn_label', 'Je signe' ) ); ?></button>
            </form>
        </div>
    </section>

    <?php endif; // /signer ?>

    <?php if ( ag_asso_opt( 'ag_asso_show_don', 1 ) ) : ?>
    <!-- Parallax don -->
    <?php $parallax_don_img = ag_asso_opt( 'ag_asso_parallax_don_image', '' ); ?>
    <section class="ag-asso-parallax ag-asso-parallax--don"<?php if ( $parallax_don_img ) echo ' style="background-image:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),url(' . esc_url( $parallax_don_img ) . ');"'; ?>>
        <h2 class="ag-asso-parallax__title"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_don_title', 'Soutenir le mouvement' ) ); ?></h2>
        <p class="ag-asso-parallax__text"><?php echo esc_html( ag_asso_opt( 'ag_asso_parallax_don_text', 'Chaque don nous donne plus de moyens d\'action.' ) ); ?></p>
    </section>

    <!-- Don -->
    <section class="ag-asso-section ag-asso-section--alt" id="don">
        <div class="ag-asso-container">
            <?php list( $don_title, $don_lead ) = ag_asso_page_section_text( 'don', 'Faire un don', 'Indépendants des partis et des grands donateurs, nous ne tenons que par vous. 66% de votre don est déductible de vos impôts.' ); ?>
            <h2 class="ag-asso-section__title"><?php echo ag_asso_render_split_title( $don_title ); ?></h2>
            <p class="ag-asso-section__lead"><?php echo esc_html( $don_lead ); ?></p>
            <div class="ag-asso-don-grid">
                <?php
                $reduc = (int) ag_asso_opt( 'ag_asso_don_tax_reduc', 66 );
                $amounts = array_filter( array_map( 'trim', explode( ',', ag_asso_opt( 'ag_asso_don_amounts', '5,20,50,100' ) ) ) );
                foreach ( $amounts as $amount ) :
                    $real = max( 0, round( (int) $amount * ( 100 - $reduc ) / 100 ) );
                    ?>
                    <a href="<?php echo esc_url( home_url( '/don/' ) ); ?>" class="ag-asso-don-card">
                        <span class="ag-asso-don-card__amount"><?php echo esc_html( $amount ); ?>€</span>
                        <span class="ag-asso-don-card__note">Coût réel : <?php echo esc_html( $real ); ?>€</span>
                    </a>
                <?php endforeach; ?>
                <a href="#" class="ag-asso-don-card ag-asso-don-card--free">
                    <span class="ag-asso-don-card__amount"><?php echo esc_html( ag_asso_opt( 'ag_asso_don_libre_label', 'Libre' ) ); ?></span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; // /don ?>

</main>

<?php get_footer(); ?>
