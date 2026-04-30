<?php $img_base = get_stylesheet_directory_uri() . '/assets/images/team/'; ?>
<?php
// Detection PHP fiable : on cache les collaborateurs sur mobile
// uniquement quand cette partial est incluse depuis la page d'accueil
// (page-accueil.php). Ne depend pas des body classes WP qui peuvent
// varier selon la config front-page-statique vs blog.
$ag_about_is_home = is_front_page() || is_home() || is_page( 'accueil' );
?>

<section class="ag-section ag-section--or">
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
<section class="ag-section ag-section--cendre">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">L'équipe</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Les visages derrière <em>Alliance Groupe</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Une équipe internationale répartie entre Naples, Nantes et Marrakech.</p>

        <div class="ag-team__grid<?php if ( $ag_about_is_home ) echo ' ag-team__grid--home-condense'; ?>">
            <?php
            $team = [
                [
                    'name'  => 'Fabrizio',
                    'role'  => 'Fondateur & CEO',
                    'city'  => 'Nantes, France',
                    'img'   => 'fabrizio',
                    'desc'  => 'Né à Naples dans les Quartieri Spagnoli, installé à Nantes depuis 2009. Fabrizio a commencé par former gratuitement des familles défavorisées au digital dans l\'arrière-salle d\'une église. Aujourd\'hui, il dirige Alliance Groupe avec la même conviction : le web est un outil d\'émancipation.',
                    'link'  => home_url('/notre-fondateur'),
                ],
                [
                    'name'  => 'Carlito',
                    'role'  => 'Directeur Technique',
                    'city'  => 'Naples, Italie',
                    'img'   => 'carlito',
                    'desc'  => 'Ingénieur napolitain passé par plusieurs startups italiennes, Carlito dirige le pôle technique depuis le bureau de Naples. Architecture backend, intégrations WordPress avancées, DevOps — il transforme les visions en produits solides et scalables. Son credo : "La tecnologia è l\'arte di semplificare la complessità".',
                    'link'  => home_url('/bureau-naples'),
                ],
                [
                    'name'  => 'Kate',
                    'role'  => 'Directrice Artistique',
                    'city'  => 'Nantes, France',
                    'img'   => 'kate',
                    'desc'  => 'Diplômée de l\'École de Design Nantes Atlantique, Kate a fait ses armes en agence parisienne avant de rejoindre Alliance Groupe. Créative perfectionniste, elle conçoit des identités visuelles qui marquent et des interfaces qui convertissent. Son obsession : les détails que personne d\'autre ne remarque.',
                    'link'  => home_url('/bureau-nantes'),
                ],
                [
                    'name'  => 'Halim',
                    'role'  => 'Responsable SEO & Data',
                    'city'  => 'Marrakech, Maroc',
                    'img'   => 'halim',
                    'desc'  => 'Mathématicien de formation devenu expert SEO, Halim combine rigueur analytique et patience d\'artisan marocain. Depuis le bureau de Marrakech, il pilote les stratégies de référencement, l\'audit technique et le linking pour propulser nos clients en première page de Google.',
                    'link'  => home_url('/bureau-marrakech'),
                ],
                [
                    'name'  => 'Amina',
                    'role'  => 'Responsable IA & Automatisation',
                    'city'  => 'Marrakech, Maroc',
                    'img'   => 'amina',
                    'desc'  => 'Diplômée en informatique de l\'Université Cadi Ayyad, Amina est l\'une des pionnières de l\'IA générative pour PME francophones. Elle conçoit chatbots, workflows et agents personnalisés qui libèrent nos clients des tâches répétitives. Son mantra : "L\'IA ne remplace personne, elle libère du temps."',
                    'link'  => home_url('/bureau-marrakech'),
                ],
                [
                    'name'  => 'Laurent',
                    'role'  => 'Responsable Commercial',
                    'city'  => 'Nantes, France',
                    'img'   => 'laurent',
                    'desc'  => '15 ans de vente B2B avant de rejoindre Alliance Groupe. Patient, relationnel et profondément honnête, Laurent refuse de vendre ce dont le client n\'a pas besoin — ce qui explique pourquoi nos clients reviennent. Il traduit les besoins business en cahier des charges concret pour l\'équipe tech.',
                    'link'  => home_url('/bureau-nantes'),
                ],
                [
                    'name'  => 'Julie',
                    'role'  => 'Cheffe de Projet',
                    'city'  => 'Nantes, France',
                    'img'   => 'julie',
                    'desc'  => 'La colonne vertébrale opérationnelle d\'Alliance Groupe. Organisée, méthodique et dotée d\'un sang-froid à toute épreuve, Julie coordonne au quotidien les équipes de Nantes, Naples et Marrakech. Son super-pouvoir : transformer un planning chaotique en machine bien huilée.',
                    'link'  => home_url('/bureau-nantes'),
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

        <?php if ( $ag_about_is_home ) : ?>
            <?php /* Mobile + home uniquement : bouton vers la page A propos. */ ?>
            <div class="ag-team__see-all-mobile">
                <a href="<?php echo esc_url(home_url('/a-propos')); ?>" class="ag-btn-outline">Voir toute l'équipe →</a>
            </div>
        <?php endif; ?>
    </div>
</section>
