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
            <p class="ag-asso-section__lead">Nous croyons qu'une autre société est possible — plus juste, plus écologique, plus démocratique. Voici nos engagements.</p>
            <div class="ag-asso-manifeste">
                <p><strong>1. Justice sociale.</strong> Nous combattons les inégalités là où elles se creusent : accès au logement, au soin, à l'éducation. Aucun territoire ne doit être abandonné, aucune personne ne doit être laissée de côté.</p>
                <p><strong>2. Urgence climatique.</strong> Le dérèglement climatique n'est plus une menace lointaine, c'est notre quotidien. Nous portons un projet de transition juste, qui ne fasse pas peser l'effort sur les plus modestes.</p>
                <p><strong>3. Démocratie vivante.</strong> Voter tous les cinq ans ne suffit plus. Nous défendons des assemblées citoyennes tirées au sort, le référendum d'initiative citoyenne et la transparence totale de l'action publique.</p>
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
            <p class="ag-asso-section__lead">Six grandes campagnes que nous portons en 2026, sur le terrain et dans les institutions.</p>
            <div class="ag-asso-combats-grid">
                <?php
                $combats = array(
                    array( 'Justice climatique',     'Pour une transition écologique qui ne pèse pas sur les plus modestes : rénovation thermique massive, transports gratuits, fin des aides aux énergies fossiles.' ),
                    array( 'Logement digne',         'Plafonnement effectif des loyers, réquisition des logements vacants, construction massive de logements sociaux. Le logement n\'est pas une marchandise.' ),
                    array( 'Service public fort',    'Refonder l\'hôpital, l\'école, la SNCF. Des investissements publics massifs, fin des fermetures de lits, recrutements à hauteur des besoins.' ),
                    array( 'Démocratie réelle',      'Référendum d\'initiative citoyenne, assemblées tirées au sort, reconnaissance du vote blanc. Redonner le pouvoir aux citoyens.' ),
                    array( 'Transparence publique', 'Open data total, traçabilité des marchés publics, registre obligatoire des lobbies. La démocratie sans transparence est une illusion.' ),
                    array( 'Égalité réelle',         'Lutter contre toutes les discriminations : sexe, origine, handicap, orientation. Promouvoir activement la parité et l\'inclusion à tous les niveaux.' ),
                );
                foreach ( $combats as $c ) : ?>
                    <article class="ag-asso-combat">
                        <h3><?php echo esc_html( $c[0] ); ?></h3>
                        <p><?php echo esc_html( $c[1] ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/combats/' ) ); ?>"><?php esc_html_e( 'En savoir plus →', 'ag-starter-association' ); ?></a>
                    </article>
                <?php endforeach; ?>
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
            <p class="ag-asso-section__lead">Marches, meetings, assemblées générales, débats publics — venez nous rencontrer près de chez vous.</p>
            <div class="ag-asso-events">
                <?php
                $events = array(
                    array( '15', 'JUIN',  'Marche pour la justice climatique', 'Paris — République → Bastille', 'Grande mobilisation nationale. Départ 14h, République. Plus de 50 organisations partenaires.' ),
                    array( '22', 'JUIN',  'Assemblée générale annuelle',       'Lyon — Bourse du Travail',     'AG ouverte aux adhérent·es : bilan moral, financier, vote du programme 2027.' ),
                    array( '06', 'JUIL.', 'Université d\'été du mouvement',    'Marseille — Friche Belle de Mai', 'Trois jours de formations, ateliers, débats. Inscription obligatoire (gratuit pour adhérents).' ),
                );
                foreach ( $events as $ev ) : ?>
                    <article class="ag-asso-event">
                        <div class="ag-asso-event__date">
                            <span class="ag-asso-event__day"><?php echo esc_html( $ev[0] ); ?></span>
                            <span class="ag-asso-event__month"><?php echo esc_html( $ev[1] ); ?></span>
                        </div>
                        <div class="ag-asso-event__body">
                            <h3><?php echo esc_html( $ev[2] ); ?></h3>
                            <p class="ag-asso-event__where"><?php echo esc_html( $ev[3] ); ?></p>
                            <p><?php echo esc_html( $ev[4] ); ?></p>
                            <a href="<?php echo esc_url( home_url( '/evenements/' ) ); ?>"><?php esc_html_e( "M'inscrire →", 'ag-starter-association' ); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Groupes locaux -->
    <section class="ag-asso-section ag-asso-section--alt ag-asso-section--map" id="groupes">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Trouver mon', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'groupe local', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">47 groupes locaux actifs partout en France. Tapez votre code postal pour trouver celui le plus proche.</p>
            <form class="ag-asso-search" action="#" method="get">
                <input type="text" name="cp" placeholder="<?php esc_attr_e( 'Code postal ou ville', 'ag-starter-association' ); ?>">
                <button type="submit"><?php esc_html_e( 'Trouver', 'ag-starter-association' ); ?></button>
            </form>
            <p class="ag-asso-search__note">Pas de groupe près de chez vous ? <a href="<?php echo esc_url( home_url( '/groupes/' ) ); ?>">Créez le vôtre</a> — nous vous accompagnons.</p>
            <div class="ag-asso-stats">
                <div><strong>47</strong><span>groupes locaux</span></div>
                <div><strong>2 130</strong><span>adhérents</span></div>
                <div><strong>12 480</strong><span>signataires</span></div>
            </div>
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

    <!-- Équipe -->
    <section class="ag-asso-section ag-asso-section--team" id="equipe">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Notre', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'équipe', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">Bénévoles, élu·es au CA, salarié·es — celles et ceux qui font vivre le mouvement au quotidien.</p>
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
                for ( $i = 1; $i <= 6; $i++ ) :
                    $photo = ag_asso_opt( "ag_asso_about_team_photo_$i", '' );
                    $name  = ag_asso_opt( "ag_asso_about_team_name_$i", $team_fb[ $i ][0] );
                    $role  = ag_asso_opt( "ag_asso_about_team_role_$i", $team_fb[ $i ][1] );
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
                <a class="ag-asso-btn ag-asso-btn--ghost ag-asso-btn--ghost-dark" href="<?php echo esc_url( home_url( '/qui-sommes-nous/' ) ); ?>">Découvrir toute l'équipe →</a>
            </p>
        </div>
    </section>

    <!-- Signer -->
    <section class="ag-asso-section ag-asso-section--cta" id="signer">
        <div class="ag-asso-container">
            <h2 class="ag-asso-section__title"><?php esc_html_e( 'Signez', 'ag-starter-association' ); ?> <em><?php esc_html_e( 'l\'appel', 'ag-starter-association' ); ?></em></h2>
            <p class="ag-asso-section__lead">Pour une société plus juste, écologique et démocratique. Signer, c'est s'engager à recevoir nos appels à mobilisation et à les relayer autour de soi.</p>
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
            <p class="ag-asso-section__lead">Indépendants des partis et des grands donateurs, nous ne tenons que par vous. 66% de votre don est déductible de vos impôts.</p>
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
                    <span class="ag-asso-don-card__amount"><?php esc_html_e( 'Libre', 'ag-starter-association' ); ?></span>
                </a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
