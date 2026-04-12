<section class="ag-section ag-section--marbre">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">Réalisations</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Nos projets <em>récents</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Des résultats concrets pour des clients ambitieux.</p>

        <div class="ag-reals__grid ag-reals__grid--full">
            <?php
            $img_base = get_stylesheet_directory_uri() . '/assets/images/realisations/';
            $projets = [
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
                    'url'   => 'https://www.paysagiste-environnement.com/',
                    'img'   => $img_base . 'la-environnement-logo.png',
                    'tags'  => ['Paysagiste', 'Site Vitrine', 'SEO Local'],
                    'desc'  => 'Site vitrine pour un paysagiste en Loire-Atlantique. Génération de leads automatisée avec formulaires optimisés et référencement local dominant.',
                    'stats' => ['+320% devis', 'Top 3 Google', '15 devis/mois'],
                ],
            ];

            foreach ( $projets as $p ) :
                // Check if local image exists, otherwise use placeholder
                $img_path = get_stylesheet_directory() . '/assets/images/realisations/' . basename( $p['img'] );
                $has_img = file_exists( $img_path );
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
                    <?php if ( $p['url'] !== '#' ) : ?>
                    <a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="ag-rcard__link">Voir le projet →</a>
                    <?php else : ?>
                    <span class="ag-rcard__link">Projet client confidentiel</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
