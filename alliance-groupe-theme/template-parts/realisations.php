<section class="ag-section ag-section--marbre ag-rise">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">Réalisations</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Nos projets <em>récents</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Des résultats concrets pour des clients ambitieux.</p>

        <div class="ag-reals__grid ag-reals__grid--full">
            <?php
            $img_base = get_stylesheet_directory_uri() . '/assets/images/realisations/';
            $projets = [
                [
                    'id'    => 'gwen-services',
                    'title' => 'Gwen Services',
                    'url'   => 'https://gwen-services.alliancegroupe-inc.com/',
                    'img'   => $img_base . 'gwen-maquette.jpg',
                    'tags'  => ['Aide à domicile', 'Site vitrine', 'Images sur mesure', 'SEO local'],
                    'desc'  => 'Site complet pour une auxiliaire de vie à Nantes : conception, textes, images générées sur mesure, référencement local et sécurité. Livré en cinq jours, prêt à recevoir des appels.',
                    'stats' => ['Livré en 5 jours', 'Crédit d\'impôt expliqué', 'Devis en 1 clic'],
                    'etude' => home_url( '/realisation-gwen' ),
                    'google'=> 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Gwen Services aide à domicile Nantes' ),
                ],
                [
                    'id'    => 'anna-photo',
                    'title' => 'Anna Photo',
                    'url'   => 'https://annaphoto.eu/',
                    'img'   => $img_base . 'anna_photo.jpg',
                    'tags'  => ['Photographie', 'Blog WordPress', 'Portfolio'],
                    'desc'  => 'Blog photo WordPress pour une photographe portraitiste à Nantes. Design immersif mettant en valeur ses clichés avec une navigation fluide et un SEO optimisé.',
                    'stats' => ['+180% trafic', '23 articles', 'Portfolio complet'],
                ],
                [
                    'id'    => 'la-environnement',
                    'title' => 'L.A Environnement',
                    'url'   => '#',
                    'img'   => $img_base . 'la-environnement.jpg',
                    'tags'  => ['Paysagiste', 'Site Vitrine', 'SEO Local'],
                    'desc'  => 'Site vitrine réalisé pour un paysagiste en Loire-Atlantique : mise en valeur des aménagements extérieurs, génération de leads via formulaires optimisés et référencement local. Activité aujourd\'hui cessée.',
                    'stats' => ['Aménagement paysager', 'SEO local', 'Génération de devis'],
                    'google'=> 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'L.A Environnement paysagiste Nantes' ),
                ],
            ];
            // Projets ajoutés par l'admin (Réalisations) — tous les sites créés + fiche Google.
            if ( function_exists( 'ag_portfolio_projects' ) ) {
                $projets = array_merge( $projets, ag_portfolio_projects() );
            }

            foreach ( $projets as $p ) :
                // Check if local image exists, otherwise use placeholder
                $img_path = ! empty( $p['img'] ) ? get_stylesheet_directory() . '/assets/images/realisations/' . basename( $p['img'] ) : '';
                $has_img = ( $img_path && file_exists( $img_path ) ) || ( ! empty( $p['img'] ) && preg_match( '#^https?://#i', $p['img'] ) );
                // Lien « fiche Google » : fourni, sinon généré depuis le nom du projet.
                $p_google = ! empty( $p['google'] ) ? $p['google'] : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( (string) ( $p['title'] ?? '' ) );
            ?>
            <div class="ag-rcard ag-anim" data-anim="real" id="<?php echo esc_attr( $p['id'] ); ?>">
                <div class="ag-rcard__img">
                    <?php if ( $has_img ) : ?>
                        <img src="<?php echo esc_url( $p['img'] ); ?>" alt="<?php echo esc_attr( $p['title'] ); ?>" loading="lazy">
                    <?php else : ?>
                        <div class="ag-rcard__placeholder">
                            <span class="ag-rcard__placeholder-title"><?php echo esc_html( $p['title'] ); ?></span>
                            <span class="ag-rcard__placeholder-tags">
                                <?php echo esc_html( implode( ' · ', $p['tags'] ) ); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ag-rcard__body">
                    <div class="ag-rcard__tags">
                        <?php foreach ( $p['tags'] as $tag ) : ?>
                        <span class="ag-rcard__tag"><?php echo esc_html( $tag ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <h3 class="ag-rcard__title"><?php echo esc_html( $p['title'] ); ?></h3>
                    <p class="ag-rcard__text"><?php echo esc_html( $p['desc'] ); ?></p>
                    <div class="ag-rcard__stats">
                        <?php foreach ( $p['stats'] as $stat ) : ?>
                        <span class="ag-rcard__stat"><?php echo esc_html( $stat ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="ag-rcard__links" style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;">
                        <?php if ( $p['url'] !== '#' ) : ?>
                        <a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="ag-rcard__link">Voir le projet →</a>
                        <?php else : ?>
                        <span class="ag-rcard__link">Projet client confidentiel</span>
                        <?php endif; ?>
                        <?php if ( ! empty( $p['etude'] ) ) : ?>
                        <a href="<?php echo esc_url( $p['etude'] ); ?>" class="ag-rcard__link">Voir l'étude de cas →</a>
                        <?php endif; ?>
                        <?php if ( $p_google ) : ?>
                        <a href="<?php echo esc_url( $p_google ); ?>" target="_blank" rel="noopener noreferrer" class="ag-rcard__link" style="opacity:.9;">Voir sur Google →</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
