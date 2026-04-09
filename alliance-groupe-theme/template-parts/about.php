<?php $img_base = get_stylesheet_directory_uri() . '/assets/images/team/'; ?>

<section class="ag-section" style="background:#0c0c0f;">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">À propos</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Qui sommes-<em>nous</em></h2>

        <div class="ag-about__content">
            <div>
                <p class="ag-about__text ag-anim" data-anim="desc">
                    Alliance Groupe est une agence web & IA internationale, avec des bureaux à <strong>Naples</strong>, <strong>Nantes</strong> et <strong>Marrakech</strong>. Nous accompagnons les entreprises ambitieuses dans leur transformation digitale avec une approche centrée sur les résultats.
                </p>
                <p class="ag-about__text ag-anim" data-anim="desc">
                    Notre expertise combine design premium, développement performant et intelligence artificielle pour créer des expériences digitales qui convertissent. Une équipe multiculturelle, passionnée et complémentaire, réunie par une seule mission : faire grandir votre business.
                </p>
            </div>

            <div class="ag-valeurs__grid">
                <?php
                $valeurs = [
                    ['icon' => '🎯', 'title' => 'Résultats', 'text' => 'Chaque projet est piloté par des KPIs concrets et mesurables.'],
                    ['icon' => '⚡', 'title' => 'Performance', 'text' => 'Sites ultra-rapides, code optimisé, expérience utilisateur fluide.'],
                    ['icon' => '🤝', 'title' => 'Transparence', 'text' => 'Communication claire, reporting régulier, pas de jargon inutile.'],
                    ['icon' => '🚀', 'title' => 'Innovation', 'text' => 'IA, automatisation et technologies de pointe au service de votre croissance.'],
                ];
                foreach ($valeurs as $v) :
                ?>
                <div class="ag-valeur ag-anim" data-anim="valeur">
                    <div class="ag-valeur__icon"><?php echo $v['icon']; ?></div>
                    <h3 class="ag-valeur__title"><?php echo esc_html($v['title']); ?></h3>
                    <p class="ag-valeur__text"><?php echo esc_html($v['text']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Équipe -->
<section class="ag-section" style="background:#101014;">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">L'équipe</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Les visages derrière <em>Alliance Groupe</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Une équipe internationale répartie entre Naples, Nantes et Marrakech.</p>

        <div class="ag-team__grid">
            <?php
            $team = [
                [
                    'name'  => 'Fabrizio',
                    'role'  => 'Fondateur & CEO',
                    'city'  => 'Naples, Italie',
                    'img'   => '1_bureau_naples',
                    'desc'  => 'Né à Naples, Fabrizio a commencé par offrir l\'accès au numérique aux plus démunis de sa ville. Aujourd\'hui il dirige Alliance Groupe avec la même passion : rendre le digital accessible à tous.',
                    'link'  => home_url('/notre-fondateur'),
                ],
                [
                    'name'  => 'Carlito',
                    'role'  => 'Directeur Technique',
                    'city'  => 'Naples, Italie',
                    'img'   => 'carlito',
                    'desc'  => 'Expert en développement web et architecte technique, Carlito transforme les visions en produits digitaux performants.',
                ],
                [
                    'name'  => 'Kate',
                    'role'  => 'Directrice Artistique',
                    'city'  => 'Nantes, France',
                    'img'   => 'kate',
                    'desc'  => 'Créative et perfectionniste, Kate conçoit des identités visuelles et des interfaces qui marquent les esprits.',
                ],
                [
                    'name'  => 'Halim',
                    'role'  => 'Responsable SEO & Data',
                    'city'  => 'Marrakech, Maroc',
                    'img'   => 'halim',
                    'desc'  => 'Obsédé par les données et les classements Google, Halim propulse nos clients en première page.',
                ],
                [
                    'name'  => 'Amina',
                    'role'  => 'Responsable IA & Automatisation',
                    'city'  => 'Marrakech, Maroc',
                    'img'   => 'amina',
                    'desc'  => 'Spécialiste en intelligence artificielle, Amina intègre les solutions d\'automatisation qui font gagner du temps.',
                ],
                [
                    'name'  => 'Laurent',
                    'role'  => 'Responsable Commercial',
                    'city'  => 'Nantes, France',
                    'img'   => 'laurent',
                    'desc'  => 'Relationnel et persuasif, Laurent accompagne chaque client du premier contact jusqu\'à la réussite de son projet.',
                ],
                [
                    'name'  => 'Julie',
                    'role'  => 'Cheffe de Projet',
                    'city'  => 'Nantes, France',
                    'img'   => 'julie',
                    'desc'  => 'Organisée et rigoureuse, Julie coordonne les équipes et garantit le respect des délais et de la qualité.',
                ],
            ];
            foreach ($team as $m) :
                $img_url = '';
                $img_dir = get_stylesheet_directory() . '/assets/images/team/';
                foreach (array('jpg','jpeg','png','webp') as $ext) {
                    if (file_exists($img_dir . $m['img'] . '.' . $ext)) {
                        $img_url = $img_base . $m['img'] . '.' . $ext;
                        break;
                    }
                }
            ?>
            <div class="ag-team-card ag-anim" data-anim="card">
                <div class="ag-team-card__img">
                    <?php if ($img_url) : ?>
                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($m['name']); ?>" loading="lazy">
                    <?php else : ?>
                        <div class="ag-team-card__placeholder"><?php echo mb_substr($m['name'], 0, 1); ?></div>
                    <?php endif; ?>
                </div>
                <div class="ag-team-card__body">
                    <h3 class="ag-team-card__name"><?php echo esc_html($m['name']); ?></h3>
                    <span class="ag-team-card__role"><?php echo esc_html($m['role']); ?></span>
                    <span class="ag-team-card__city"><?php echo esc_html($m['city']); ?></span>
                    <p class="ag-team-card__desc"><?php echo esc_html($m['desc']); ?></p>
                    <?php if (!empty($m['link'])) : ?>
                    <a href="<?php echo esc_url($m['link']); ?>" class="ag-team-card__link">Découvrir son histoire →</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
