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
                Un <em>artisan du web</em><br>
                qui sécurise votre présence en ligne
            </h2>
            <p class="ag-about-refondu__lead">
                Alliance Groupe, c'est un <strong>studio indépendant</strong> basé à <strong>Naples</strong>, et à <strong>Nantes</strong>.
                Un interlocuteur unique : audit de sécurité, création de sites et SEO — du conseil à la livraison, sans intermédiaire.
            </p>
        </div>

        <!-- 2 colonnes : 4 cases à gauche · photo + présentation à droite -->
        <div class="ag-about-refondu__cols">
        <div class="ag-about-refondu__valeurs">
            <?php
            $valeurs = array(
                array(
                    'title' => 'Sécurité d\'abord',
                    'text'  => 'J\'audite et je verrouille votre site. Protégé comme on protège ce qui compte.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-4"/></svg>',
                ),
                array(
                    'title' => 'Interlocuteur unique',
                    'text'  => 'Un seul contact, du conseil à la livraison. Pas de service client anonyme.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>',
                ),
                array(
                    'title' => 'Sans jargon',
                    'text'  => 'Un rapport clair, des explications simples. Vous comprenez ce que vous payez.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3.5"/></svg>',
                ),
                array(
                    'title' => 'Dans la durée',
                    'text'  => 'Mises à jour, sauvegardes, surveillance : votre site reste sain chaque mois.',
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v4h-4"/></svg>',
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

        <!-- DROITE : photo + présentation -->
        <div class="ag-about-refondu__studio">
            <?php
            $ag_fab_img = '';
            foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
                if ( file_exists( get_stylesheet_directory() . '/assets/images/team/fabrizio.' . $ext ) ) { $ag_fab_img = $img_base . 'fabrizio.' . $ext; break; }
            }
            ?>
            <article class="ag-team-card ag-team-card--solo">
                <div class="ag-team-card__img">
                    <?php if ( $ag_fab_img ) : ?>
                        <img src="<?php echo esc_url( $ag_fab_img ); ?>" alt="Fabrizio" loading="lazy">
                    <?php else : ?>
                        <div class="ag-team-card__placeholder">F</div>
                    <?php endif; ?>
                </div>
                <div class="ag-team-card__body">
                    <span class="ag-team-card__role">Fondateur — votre interlocuteur unique</span>
                    <h3 class="ag-team-card__name">Fabrizio</h3>
                    <span class="ag-team-card__city">Naples (Italie) &amp; Nantes</span>
                    <p class="ag-team-card__desc">Né à Naples (Quartieri Spagnoli), j'y suis basé — et aussi à Nantes. Studio indépendant : je conçois et sécurise des sites web, du conseil à la livraison. Ma conviction : un site, ça se protège comme on protège ce qui compte. Pour les projets ambitieux, je m'entoure de partenaires freelances de confiance.</p>
                    <a href="<?php echo esc_url( home_url( '/a-propos' ) ); ?>" class="ag-team-card__link">Découvrir son histoire →</a>
                </div>
            </article>
        </div>

        </div><!-- /.ag-about-refondu__cols -->

        <!-- Stats (pleine largeur, sous les 2 colonnes) -->
        <div class="ag-about-refondu__stats">
            <div class="ag-about-refondu__stat">
                <span class="ag-about-refondu__stat-num">48h</span>
                <span class="ag-about-refondu__stat-label">Audit livré</span>
            </div>
            <div class="ag-about-refondu__stat">
                <span class="ag-about-refondu__stat-num">1</span>
                <span class="ag-about-refondu__stat-label">Interlocuteur unique</span>
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
    .ag-about-refondu__cols{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:stretch;margin-bottom:50px}
    .ag-about-refondu__valeurs{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin:0}
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
    /* Colonne droite : carte studio (photo en haut, présentation dessous) */
    .ag-about-refondu__studio{display:flex}
    .ag-team-card--solo{display:flex;flex-direction:column;width:100%;background:linear-gradient(180deg,rgba(20,20,28,.6),rgba(10,10,15,.85));border:1px solid rgba(212,180,92,.2);border-radius:18px;overflow:hidden}
    .ag-team-card--solo .ag-team-card__img{width:100%;height:300px;overflow:hidden}
    .ag-team-card--solo .ag-team-card__img img{width:100%;height:100%;object-fit:cover;object-position:center 25%}
    .ag-team-card--solo .ag-team-card__body{padding:28px 30px;display:flex;flex-direction:column;justify-content:center;text-align:left;flex:1}
    .ag-team-card--solo .ag-team-card__role{color:#D4B45C;font-size:.78rem;letter-spacing:2px;text-transform:uppercase;font-weight:700}
    .ag-team-card--solo .ag-team-card__name{font-family:Georgia,serif;font-size:1.7rem;color:#fff;margin:6px 0 2px}
    .ag-team-card--solo .ag-team-card__city{color:rgba(255,255,255,.55);font-size:.88rem}
    .ag-team-card--solo .ag-team-card__desc{color:rgba(255,255,255,.78);font-size:.95rem;line-height:1.6;margin:14px 0 16px}
    .ag-team-card--solo .ag-team-card__link{color:#F3D27A;font-weight:700;text-decoration:none;align-self:flex-start}
    .ag-team-card--solo .ag-team-card__link:hover{text-decoration:underline}
    .ag-about-refondu__stats{margin-top:0}
    @media(max-width:860px){.ag-about-refondu__cols{grid-template-columns:1fr;gap:20px}}
    @media(max-width:460px){.ag-about-refondu__valeurs{grid-template-columns:1fr}}
    </style>
</section>

<!-- (Ancienne section "Le studio" fusionnée dans .ag-about-refondu ci-dessus) -->
