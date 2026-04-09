<section class="ag-section" style="background:#101014;">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">Réalisations</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Nos projets <em>récents</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Des résultats concrets pour des clients ambitieux. 7 projets, 7 réussites.</p>

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
                [
                    'id'    => 'maison-riviera',
                    'title' => 'Maison Riviera',
                    'url'   => '#',
                    'img'   => $img_base . 'maison-riviera.svg',
                    'tags'  => ['E-commerce', 'Shopify', 'Luxe'],
                    'desc'  => 'Boutique e-commerce premium pour une maison de décoration intérieure à Nice. Design luxueux, tunnel de vente optimisé et intégration de paiement sécurisé.',
                    'stats' => ['+280% ventes', 'Panier moyen 185€', 'Taux conv. 4.2%'],
                ],
                [
                    'id'    => 'cabinet-martin',
                    'title' => 'Cabinet Martin & Associés',
                    'url'   => '#',
                    'img'   => $img_base . 'cabinet-martin.svg',
                    'tags'  => ['Avocat', 'Site Vitrine', 'Lead Gen'],
                    'desc'  => 'Site vitrine pour un cabinet d\'avocats parisien spécialisé en droit des affaires. Crédibilité renforcée, formulaire de prise de RDV et SEO sectoriel.',
                    'stats' => ['+210% contacts', 'Page 1 Google', '40+ avis 5★'],
                ],
                [
                    'id'    => 'fitness-lab',
                    'title' => 'Fitness Lab',
                    'url'   => '#',
                    'img'   => $img_base . 'fitness-lab.svg',
                    'tags'  => ['Fitness', 'App Web', 'Booking'],
                    'desc'  => 'Plateforme de réservation de cours et site vitrine pour une salle de sport à Lyon. Système de booking en ligne, paiement intégré et espace membre.',
                    'stats' => ['+450% réservations', '1 200 membres', 'Churn -35%'],
                ],
                [
                    'id'    => 'saveurs-orient',
                    'title' => 'Saveurs d\'Orient',
                    'url'   => '#',
                    'img'   => $img_base . 'saveurs-orient.svg',
                    'tags'  => ['Restaurant', 'Site Vitrine', 'Google Ads'],
                    'desc'  => 'Site web et stratégie digitale complète pour un restaurant gastronomique à Marrakech. Réservation en ligne, menu interactif et campagnes Google Ads ciblées.',
                    'stats' => ['+190% réservations', 'ROAS x6.3', 'TripAdvisor Top 10'],
                ],
                [
                    'id'    => 'techvision',
                    'title' => 'TechVision Pro',
                    'url'   => '#',
                    'img'   => $img_base . 'techvision.svg',
                    'tags'  => ['SaaS', 'Landing Page', 'Automatisation'],
                    'desc'  => 'Landing page et tunnel d\'acquisition pour une startup SaaS nantaise. Optimisation du taux de conversion, intégration CRM et séquences email automatisées.',
                    'stats' => ['Conv. 8.7%', '+520% MRR', '2 300 leads/mois'],
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
