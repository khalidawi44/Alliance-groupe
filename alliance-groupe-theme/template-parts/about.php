<?php $img_base = get_stylesheet_directory_uri() . '/assets/images/team/'; ?>
<?php
// Detection PHP fiable : on cache les collaborateurs sur mobile
// uniquement quand cette partial est incluse depuis la page d'accueil
// (page-accueil.php). Ne depend pas des body classes WP qui peuvent
// varier selon la config front-page-statique vs blog.
$ag_about_is_home = is_front_page() || is_home() || is_page( 'accueil' );
?>

<section class="ag-about-refondu">
    <div class="ag-about-refondu__bg" aria-hidden="true"></div>
    <div class="ag-about-refondu__container">
        <div class="ag-about-refondu__head">
            <span class="ag-about-refondu__tag">Qui sommes-nous</span>
            <h2 class="ag-about-refondu__title">
                Une agence <em>multiculturelle</em><br>
                au service de votre croissance
            </h2>
            <p class="ag-about-refondu__lead">
                Alliance Groupe est une agence web &amp; IA internationale avec 3 bureaux —
                <strong>Naples</strong>, <strong>Nantes</strong>, <strong>Marrakech</strong>.
                Design premium, développement performant et intelligence artificielle pour des
                expériences digitales qui convertissent.
            </p>
        </div>

        <!-- 4 valeurs avec icônes SVG -->
        <div class="ag-about-refondu__valeurs">
            <?php
            $valeurs = array(
                array(
                    'title' => 'Résultats',
                    'text'  => 'Chaque projet piloté par des KPIs concrets et mesurables.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>',
                ),
                array(
                    'title' => 'Performance',
                    'text'  => 'Sites ultra-rapides, code optimisé, UX fluide partout.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7z" fill="currentColor" fill-opacity=".15"/><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg>',
                ),
                array(
                    'title' => 'Transparence',
                    'text'  => 'Communication claire, reporting régulier, pas de jargon.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3.5"/></svg>',
                ),
                array(
                    'title' => 'Innovation',
                    'text'  => 'IA, automatisation et tech de pointe au service de votre croissance.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21h14M12 3a7 7 0 0 0-4 13l1 2v3h6v-3l1-2a7 7 0 0 0-4-13z"/><path d="M12 16v-4M10 12h4" opacity=".5"/></svg>',
                ),
            );
            foreach ( $valeurs as $i => $v ) :
            ?>
            <div class="ag-about-refondu__valeur" style="--i:<?php echo (int) $i; ?>;">
                <div class="ag-about-refondu__valeur-icon"><?php echo $v['svg']; // phpcs:ignore ?></div>
                <h3 class="ag-about-refondu__valeur-title"><?php echo esc_html( $v['title'] ); ?></h3>
                <p class="ag-about-refondu__valeur-text"><?php echo esc_html( $v['text'] ); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Stats cards -->
        <div class="ag-about-refondu__stats">
            <div class="ag-about-refondu__stat">
                <span class="ag-about-refondu__stat-num" data-count="3">3</span>
                <span class="ag-about-refondu__stat-label">Bureaux internationaux</span>
            </div>
            <div class="ag-about-refondu__stat">
                <span class="ag-about-refondu__stat-num" data-count="7">7</span>
                <span class="ag-about-refondu__stat-label">Experts en équipe</span>
            </div>
            <div class="ag-about-refondu__stat">
                <span class="ag-about-refondu__stat-num" data-count="6">6</span>
                <span class="ag-about-refondu__stat-label">Templates métier</span>
            </div>
            <div class="ag-about-refondu__stat">
                <span class="ag-about-refondu__stat-num">24h</span>
                <span class="ag-about-refondu__stat-label">Réponse garantie</span>
            </div>
        </div>
    </div>

    <style>
    .ag-about-refondu{position:relative;padding:100px 24px;background:linear-gradient(180deg,#0a0a0f 0%,#14141c 50%,#0a0a0f 100%);color:#fff;overflow:hidden}
    .ag-about-refondu__bg{position:absolute;inset:0;background-image:radial-gradient(circle at 20% 30%,rgba(212,180,92,.08) 0%,transparent 50%),radial-gradient(circle at 80% 70%,rgba(243,122,31,.06) 0%,transparent 50%);pointer-events:none}
    .ag-about-refondu__container{position:relative;z-index:1;max-width:1180px;margin:0 auto}
    .ag-about-refondu__head{text-align:center;max-width:780px;margin:0 auto 70px}
    .ag-about-refondu__tag{display:inline-block;padding:6px 14px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#D4B45C;font-size:.78rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:20px}
    .ag-about-refondu__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.9rem,4vw,3.2rem);font-weight:700;line-height:1.15;color:#fff;margin:0 0 22px;letter-spacing:-.01em}
    .ag-about-refondu__title em{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
    .ag-about-refondu__lead{color:rgba(255,255,255,.78);font-size:1.08rem;line-height:1.75;margin:0}
    .ag-about-refondu__lead strong{color:#D4B45C;font-weight:700}
    .ag-about-refondu__valeurs{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-bottom:60px}
    .ag-about-refondu__valeur{background:linear-gradient(180deg,rgba(20,20,28,.6) 0%,rgba(10,10,15,.85) 100%);border:1px solid rgba(212,180,92,.12);border-radius:16px;padding:28px 24px;transition:transform .4s cubic-bezier(.16,1,.3,1),border-color .35s ease,box-shadow .35s ease;position:relative;overflow:hidden}
    .ag-about-refondu__valeur::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(212,180,92,.4),transparent);opacity:0;transition:opacity .35s ease}
    .ag-about-refondu__valeur:hover{transform:translateY(-6px);border-color:rgba(212,180,92,.4);box-shadow:0 24px 60px rgba(0,0,0,.5),0 0 40px rgba(212,180,92,.1)}
    .ag-about-refondu__valeur:hover::before{opacity:1}
    .ag-about-refondu__valeur-icon{width:52px;height:52px;display:inline-flex;align-items:center;justify-content:center;border-radius:14px;background:linear-gradient(135deg,rgba(212,180,92,.15) 0%,rgba(243,122,31,.08) 100%);border:1px solid rgba(212,180,92,.25);color:#D4B45C;margin-bottom:18px;transition:transform .4s ease,color .3s ease,border-color .3s ease}
    .ag-about-refondu__valeur-icon svg{width:26px;height:26px}
    .ag-about-refondu__valeur:hover .ag-about-refondu__valeur-icon{transform:rotate(-4deg) scale(1.08);color:#F37A1F;border-color:rgba(212,180,92,.6)}
    .ag-about-refondu__valeur-title{font-family:Georgia,'Playfair Display',serif;font-size:1.25rem;font-weight:700;color:#fff;margin:0 0 10px}
    .ag-about-refondu__valeur-text{color:rgba(255,255,255,.72);font-size:.94rem;line-height:1.6;margin:0}
    .ag-about-refondu__stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;background:linear-gradient(135deg,rgba(30,25,20,.6) 0%,rgba(20,15,10,.85) 100%);border:1px solid rgba(212,180,92,.25);border-radius:18px;padding:34px 28px;backdrop-filter:blur(20px)}
    .ag-about-refondu__stat{text-align:center;border-right:1px solid rgba(212,180,92,.12);padding:8px}
    .ag-about-refondu__stat:last-child{border-right:none}
    @media(max-width:768px){.ag-about-refondu__stat{border-right:none;border-bottom:1px solid rgba(212,180,92,.12);padding:14px}.ag-about-refondu__stat:last-child{border-bottom:none}}
    .ag-about-refondu__stat-num{display:block;font-family:Georgia,serif;font-size:2.6rem;font-weight:800;color:#D4B45C;line-height:1;text-shadow:0 0 24px rgba(212,180,92,.3);font-variant-numeric:tabular-nums}
    .ag-about-refondu__stat-label{display:block;margin-top:6px;color:rgba(255,255,255,.65);font-size:.82rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:600}
    </style>
</section>

<!-- Équipe -->
<section class="ag-section ag-section--cendre ag-teamrow">
    <div class="ag-teamrow__pin">
    <div class="ag-container ag-teamrow__head">
        <span class="ag-tag">L'équipe</span>
        <h2 class="ag-section__title">Les visages derrière <em>Alliance Groupe</em></h2>
        <p class="ag-section__desc">Une équipe internationale répartie entre Naples, Nantes et Marrakech. <span class="ag-teamrow__hint">Fais défiler →</span></p>
    </div>
    <div class="ag-teamrow__track">
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
        </div>

        <?php if ( $ag_about_is_home ) : ?>
            <div class="ag-team__see-all-mobile">
                <a href="<?php echo esc_url(home_url('/a-propos')); ?>" class="ag-btn-outline">Voir toute l'équipe →</a>
            </div>
        <?php endif; ?>
    </div>
</section>
