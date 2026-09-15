<?php
/**
 * Template Name: Réalisation — L.A Environnement
 *
 * Étude de cas détaillée : elagage-vertou.fr (élagage, abattage et création de
 * jardin, Vertou / Loire-Atlantique). Habillage commun aux etudes de cas du
 * portfolio Alliance Groupe : template-parts/realisation-style.php.
 *
 * Tout ce qui est affirmé ici a été relevé sur le site en ligne le 14/09/2026 :
 * en-têtes HTTP, balisage schema.org, scripts de la page, poids reellement
 * charge et rendu mesure a 1440x900, 834x1112 et 390x844. On ne decrit pas un
 * site de memoire : on le mesure, puis on le raconte.
 *
 * @package Alliance_Groupe
 */

get_header();
$dir = get_stylesheet_directory_uri();
$img = $dir . '/assets/images/realisations/';

$galerie = array(
	array( 'la-prestations', 'Les six prestations',      'Élagage en grimpe, abattage, haubanage, création et entretien de jardin, évacuation. Filtrables d\'un clic, chacune avec sa vraie photo de chantier.' ),
	array( 'la-chantiers',   'Les chantiers, avant / après', 'Pas de banque d\'images : ce qu\'il y avait, ce qui a été fait, ce qu\'il en reste. Photos prises sur place, avec l\'avis Google réel juste en dessous.' ),
	array( 'la-tarifs',      'Ce que ça coûte',          'La page ne donne pas SON prix — il se fixe après la visite. Elle donne les prix constatés du métier, puis explique que le tarif est adapté aux revenus : devis normal, réduction appliquée dessus, écrite noir sur blanc.' ),
	array( 'la-devis',       'La demande de devis',      'Une seule question posée au visiteur : ce qui vous inquiète. Le formulaire vient après, jamais avant.' ),
);

/* Les pages interieures : c'est la que le travail se voit vraiment. Une page
   par prestation, une par chantier, une pour l'urgence, une pour les prix —
   chacune ecrite pour une recherche precise, pas un fourre-tout. */
$exemples = array(
	array( 'la-ex-prestation', 'Une page par prestation',
		'« Élagage en grimpe à Vertou (44) : taille douce d\'arbre ». Six prestations, six pages, six recherches Google différentes. Une page fourre-tout « nos services » n\'aurait rien capté.' ),
	array( 'la-ex-chantier', 'Une page par chantier',
		'Pendant / après, photographié sur place. C\'est la preuve qu\'un devis ne remplace pas : le client voit ce qui l\'attend avant même d\'appeler.' ),
	array( 'la-ex-urgences', 'La page qui sonne à 2 h du matin',
		'« Urgence arbre tombé à Vertou — élagueur 24 h/24 ». Une page dédiée, le numéro en gros, pour la recherche faite dans la panique après une tempête.' ),
	array( 'la-ex-tarifs', 'Ce que ça coûte, sans détour',
		'« Prix d\'un élagage ou d\'un abattage à Vertou et Nantes ». La page donne les prix constatés du métier — pas les siens, qui se fixent sur place — puis annonce que le tarif est adapté aux revenus. Rare, et désarmant.' ),
);
?>
<?php get_template_part( 'template-parts/realisation-style' ); ?>
<style>
  /* ── LE FOND DE PAGE : la canopee, en automne ────────────────────────
     La video est TEINTEE, pas reencodee : sepia + saturation + rotation de
     teinte transforment le vert d'ete en ambre d'automne, sans un octet de
     plus et sans ffmpeg. Reglage calibre sur une vraie photo de sous-bois :
     teinte 33 degres, saturation 0,64 — de l'ambre, pas du jaune fluo.

     Le degrade sous la video est deja automnal : si la video ne charge pas
     (telephone, reduction de mouvement, reseau coupe), le fond reste juste,
     il ne devient pas un aplat noir. */
  .rp{background:transparent}
  .rp__fond{position:fixed;inset:0;z-index:-1;overflow:hidden;pointer-events:none;
     background:radial-gradient(78% 52% at 50% 6%,#3a2412,transparent 66%),
                radial-gradient(72% 58% at 50% 100%,#241505,transparent 70%),
                linear-gradient(180deg,#140b04,#0a0603 54%,#120a04)}
  .rp__fond-video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;
     opacity:.34;filter:sepia(.75) saturate(2.2) hue-rotate(-14deg) brightness(.95)}
  .rp__fond-voile{position:absolute;inset:0;
     background:linear-gradient(180deg,rgba(9,5,2,.80),rgba(6,4,2,.86) 46%,rgba(9,5,2,.82))}

  @media(max-width:960px){
    /* Comme sur le site du client : pas de video de fond sur telephone.
       Le degrade d'automne suffit, et le forfait du visiteur est epargne. */
    .rp__fond-video{display:none}
  }
  @media(prefers-reduced-motion:reduce){
    .rp__fond-video{display:none}
  }

  /* Le halo prend la teinte de la marque présentée — ici le vert du sous-bois.
     C'est `--halo` que lit realisation-style.php ; `--vert` n'y sert a rien. */
  .rp{--halo:rgba(127,176,74,.20)}
  .rp__bandeau{margin:clamp(40px,7vh,80px) 0 0;border-radius:16px;overflow:hidden;
     border:1px solid rgba(255,255,255,.09)}
  .rp__bandeau img{width:100%;height:auto}
  .rp__leg{display:grid;grid-template-columns:repeat(3,1fr);gap:0;margin-top:10px}
  .rp__leg span{font-size:.78rem;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);text-align:center}
  .rp__meca{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px;
     padding:clamp(40px,7vh,80px) 0 0}
  .rp__meca article{background:var(--panel);border:1px solid rgba(255,255,255,.08);
     border-radius:16px;padding:24px 22px 26px}
  .rp__meca h3{font-size:1.18rem;margin:8px 0 10px}
  .rp__meca p{font-size:.95rem}
  .rp__meca p+p{margin-top:10px}
  .rp__chiffres{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;
     padding:clamp(40px,7vh,80px) 0 0}
  .rp__chiffres div{text-align:center;background:var(--panel);border:1px solid rgba(255,255,255,.08);
     border-radius:14px;padding:22px 14px}
  .rp__chiffres b{display:block;font-family:var(--serif);font-size:clamp(1.6rem,3.4vw,2.3rem);color:var(--gold-hi)}
  .rp__chiffres span{display:block;margin-top:6px;font-size:.83rem;color:var(--muted);line-height:1.45}

  /* ── LA DEMO ────────────────────────────────────────────────────────────
     Expliquer une scene avec des phrases, c'est demander au lecteur de
     l'imaginer. On la lui montre : meme canopee filmee, meme arbre qui
     descend au defilement que sur elagage-vertou.fr. La section est haute,
     le cadre est collant : on defile DANS la scene. */
  .rp__demo{position:relative;height:250vh;margin:clamp(40px,7vh,80px) 0 0}
  .rp__demo-cadre{position:sticky;top:7vh;height:82vh;max-width:1180px;margin:0 auto;
     border-radius:20px;overflow:hidden;border:1px solid rgba(127,176,74,.28);
     box-shadow:0 70px 130px -60px rgba(0,0,0,.95)}
  /* Le cadre est une vitre : c'est la canopee de la page qui passe dessous,
     l'arbre descend par-dessus. Une seule video sur toute la page. */
  .rp__demo-scene{position:absolute;inset:0;overflow:hidden;
     background:linear-gradient(180deg,rgba(20,11,4,.30),rgba(10,6,3,.52))}
  .rp__demo-lueur{position:absolute;inset:0;z-index:2;pointer-events:none;
     background:radial-gradient(38% 70% at 50% 40%,rgba(169,211,106,.16),transparent 70%)}
  .rp__demo-arbre{position:absolute;left:50%;top:0;z-index:3;width:min(56%,520px);height:auto;
     transform:translate3d(-50%,0,0);will-change:transform;pointer-events:none;
     /* La couronne est coupee net aux bords de l'image — sur le site du client
        ca passe inapercu parce qu'elle occupe tout l'ecran ; dans un cadre
        etroit, la coupure se voit. On dissout les deux bords. */
     -webkit-mask-image:linear-gradient(90deg,transparent,#000 14%,#000 86%,transparent);
             mask-image:linear-gradient(90deg,transparent,#000 14%,#000 86%,transparent);
     /* L'arbre entre dans la saison de la page : un feuillage vert au milieu
        d'un sous-bois d'automne se voyait comme un decoupage. Teinte plus
        legere que celle du fond — il reste le sujet, il ne se fond pas. */
     filter:sepia(.58) saturate(1.9) hue-rotate(-12deg) brightness(1.06)}
  .rp__demo-voile{position:absolute;inset:0;z-index:4;pointer-events:none;
     background:linear-gradient(180deg,rgba(4,20,12,.34),transparent 30%,rgba(4,20,12,.86))}
  .rp__demo-txt{position:absolute;z-index:5;left:0;right:0;bottom:clamp(24px,5vh,54px);
     text-align:center;padding:0 26px}
  .rp__demo-txt h2{font-size:clamp(1.5rem,4vw,2.6rem);margin:10px 0 12px;
     text-shadow:0 2px 24px rgba(2,12,7,.8)}
  .rp__demo-txt p{max-width:56ch;margin:0 auto;color:#d6e2d6;
     text-shadow:0 2px 16px rgba(2,12,7,.85)}
  .rp__demo-note{display:inline-block;margin-top:14px;font-size:.78rem;letter-spacing:.14em;
     text-transform:uppercase;color:rgba(214,226,214,.62)}

  @media(max-width:960px){
    /* Comme sur le site du client : pas de video de fond sur telephone.
       L'arbre, lui, reste — c'est lui l'effet. */
    .rp__demo{height:210vh}
    .rp__demo-cadre{height:76vh;border-radius:14px}
    .rp__demo-arbre{width:min(66%,300px)}
  }
  @media(prefers-reduced-motion:reduce){
    /* Rien ne bouge : la scene est posee a mi-course, lisible telle quelle,
       et la section cesse d'etre haute pour ne pas faire defiler dans le vide. */
    .rp__demo{height:auto}
    .rp__demo-cadre{position:static;height:min(78vh,620px)}
    .rp__demo-arbre{transform:translate3d(-50%,-30%,0)!important}
  }
  @media(max-width:960px){
    .rp__meca{grid-template-columns:1fr;gap:16px}
    .rp__chiffres{grid-template-columns:repeat(2,minmax(0,1fr))}
  }
</style>

<div class="rp">

  <!-- La canopee derriere TOUTE la page, en automne. Fixe : elle ne defile
       pas, c'est le contenu qui passe devant — comme sur le site du client. -->
  <div class="rp__fond" aria-hidden="true">
    <video class="rp__fond-video" muted loop playsinline preload="none" data-fond-video>
      <source src="<?php echo esc_url( $dir . '/assets/video/la-demo-canopee.mp4' ); ?>" type="video/mp4">
    </video>
    <div class="rp__fond-voile"></div>
  </div>


  <section class="rp__hero">
    <div class="rp__halo"></div>
    <div class="wr">
      <span class="eb" data-r>Réalisation · Élagage &amp; abattage</span>
      <h1 data-r>L.A <em>Environnement</em></h1>
      <p class="rp__lead" data-r>Un arboriste-grimpeur de Vertou qui travaillait au bouche-à-oreille et au flyer. Aujourd'hui : un site qui change d'apparence selon l'heure et la saison, montre ses chantiers en photos réelles, explique sans détour ce que ça coûte, et prend les urgences jour et nuit.</p>
      <div class="rp__tags" data-r>
        <span>Élagage · abattage · jardin</span><span>Thème sur mesure</span><span>SEO local Loire-Atlantique</span><span>Durcissement sécurité</span><span>Mobile d'abord</span>
      </div>
      <div class="rp__maq" data-r>
        <img src="<?php echo esc_url( $img . 'la-environnement.jpg' ); ?>" alt="Le site L.A Environnement sur ordinateur, tablette et téléphone" fetchpriority="high" width="1800" height="1125">
      </div>
      <div class="rp__cta" data-r>
        <a class="bt" href="https://elagage-vertou.fr/" target="_blank" rel="noopener">Voir le site en ligne →</a>
        <a class="bt bt--g" href="<?php echo esc_url( home_url( '/devis-instant' ) ); ?>">Je veux le mien</a>
      </div>
    </div>
  </section>

  <section class="wr">
    <div class="rp__fiche">
      <article data-r>
        <span class="eb">Le besoin</span>
        <h3>Sortir du flyer</h3>
        <p>Anthony Lamarque avait un métier, une camionnette et un papier à distribuer. Rien en ligne. Or un arbre qui penche après une tempête, ça se cherche sur un téléphone, à 22 h, en urgence — et celui qui ne sort pas dans cette recherche-là n'existe pas.</p>
      </article>
      <article data-r>
        <span class="eb">Ce qu'on a fait</span>
        <h3>Un thème à lui seul</h3>
        <ul>
          <li>Thème WordPress sur mesure, sans constructeur de page</li>
          <li>Rédaction complète, métier par métier</li>
          <li>Fiche Google structurée (schema LocalBusiness)</li>
          <li>Référencement local Vertou / Loire-Atlantique</li>
          <li>Durcissement de sécurité et en-têtes HTTP</li>
        </ul>
      </article>
      <article data-r>
        <span class="eb">Le résultat</span>
        <h3>Le site répond avant lui</h3>
        <p>Les tarifs sont affichés, les chantiers sont montrés en photos réelles, l'urgence 24 h/24 est annoncée en haut de page. Le visiteur arrive renseigné ; l'appel ne sert plus à expliquer, il sert à convenir d'une date.</p>
      </article>
    </div>
  </section>

  <section class="wr">
    <h2 data-r style="font-size:clamp(1.8rem,4.6vw,3rem);text-align:center;margin-bottom:10px;">Le site change <em>avec le jour</em></h2>
    <p data-r style="text-align:center;max-width:60ch;margin:0 auto;">C'est la pièce maîtresse. Le premier écran existe en <strong style="color:var(--text)">douze états</strong> — quatre saisons croisées avec trois moments de la journée. La saison est décidée par le serveur ; l'heure, elle, est celle du <em style="color:var(--gold-hi)">visiteur</em>, lue et posée avant le premier rendu. Pas de scintillement, et surtout : le cache ne peut pas figer une ambiance de midi sur un visiteur de minuit.</p>
    <div class="rp__bandeau" data-r>
      <img src="<?php echo esc_url( $img . 'la-ambiances.jpg' ); ?>" alt="Le même premier écran du site L.A Environnement en version jour, crépuscule et nuit" loading="lazy" width="1800" height="373">
    </div>
    <div class="rp__leg" data-r><span>Jour</span><span>Crépuscule</span><span>Nuit</span></div>
    <p data-r style="text-align:center;max-width:62ch;margin:18px auto 0;font-size:.92rem;">Douze ambiances, c'est douze risques de rendre un titre blanc illisible. Les douze ont donc été <strong style="color:var(--text)">mesurées</strong>, texte masqué et fond réellement peint, en 390×844 et en 1440×900 : le plancher tombe à 10,01:1 au 5<sup>e</sup> centile, pour un seuil d'accessibilité AA fixé à 4,5:1. Le plafond monte à 17,7:1 la nuit.</p>
  </section>

  <section class="rp__demo" aria-labelledby="rp-demo-titre">
    <div class="rp__demo-cadre">
      <div class="rp__demo-scene">
        <div class="rp__demo-lueur" aria-hidden="true"></div>
        <img class="rp__demo-arbre" data-demo-arbre
             src="<?php echo esc_url( $img . 'la-demo-arbre.webp' ); ?>"
             alt="" aria-hidden="true" loading="lazy" decoding="async" width="600" height="3000">
        <div class="rp__demo-voile" aria-hidden="true"></div>
        <div class="rp__demo-txt">
          <span class="eb">Démonstration</span>
          <h2 id="rp-demo-titre">Défilez : <em>l'arbre descend</em></h2>
          <p>Voilà ce qui se passe derrière chaque page de son site : une canopée filmée en couche fixe, et un arbre de six mille pixels de haut qui descend au rythme du défilement. Ce n'est pas une image de fond — c'est une scène qu'on traverse.</p>
          <span class="rp__demo-note">Extrait réel du site · elagage-vertou.fr</span>
        </div>
      </div>
    </div>
  </section>

  <section class="wr">
    <h2 data-r style="font-size:clamp(1.8rem,4.6vw,3rem);text-align:center;margin:clamp(56px,9vh,110px) 0 10px;">Ce qui bouge, <em>et pourquoi</em></h2>
    <div class="rp__meca">
      <article data-r>
        <span class="eb">Le fond</span>
        <h3>Une canopée qui respire</h3>
        <p>Derrière toute la page, une couche fixe : une vidéo de canopée en boucle, muette, et par-dessus un arbre de 1 200 × 6 000 pixels qui <strong style="color:var(--text)">descend au fil du défilement</strong>. C'est ce feuillage-là qui bouge — il n'y a pas de feuilles qui tombent, il y a une forêt derrière la page.</p>
        <p>La vidéo est chargée en dernier, jamais avant le texte. <strong style="color:var(--text)">Sur téléphone elle n'est pas chargée du tout</strong> : 0 octet mesuré, contre 586 Ko sur ordinateur.</p>
      </article>
      <article data-r>
        <span class="eb">Le défilement</span>
        <h3>Amorti à la molette, jamais au doigt</h3>
        <p>GSAP et ScrollTrigger pilotent les scènes ; Lenis adoucit la molette. Le tactile, lui, est laissé strictement natif — un défilement « amélioré » au doigt est la première chose qui fait fuir d'un site sur mobile.</p>
        <p>Les rappels sont regroupés sur la boucle d'animation plutôt que tirés à chaque événement, et le redimensionnement est ignoré quand la barre d'adresse du navigateur mobile se rétracte : sans ça, chaque petit glissement relançait tout le calcul.</p>
      </article>
      <article data-r>
        <span class="eb">Les scènes</span>
        <h3>Une dissolution au pixel</h3>
        <p>La section des prestations se révèle par une dissolution dessinée sur un canevas, pixel par pixel, dont l'avancement suit le défilement. Les titres montent mot à mot derrière un masque ; un bandeau défilant accélère quand le visiteur défile vite.</p>
        <p>Sous 960 pixels de large, le canevas est purement et simplement coupé : un petit processeur n'a pas à peindre une grille pour afficher six cartes.</p>
      </article>
      <article data-r>
        <span class="eb">Le garde-fou</span>
        <h3>Réduction de mouvement : coupure nette</h3>
        <p>C'est le point le plus important, et le moins visible. Les animations étant posées par GSAP en styles <em>en ligne</em>, la règle CSS habituelle ne les coupait pas : un visiteur ayant demandé moins d'animations restait devant des blocs invisibles — <strong style="color:var(--text)">les tarifs n'existaient tout simplement pas pour lui</strong>.</p>
        <p>L'état final est désormais forcé : tout visible, tout cliquable, sans aucune animation. Même filet si GSAP ne se charge pas — 4G coupée, script bloqué.</p>
      </article>
    </div>
  </section>

  <section class="wr">
    <h2 data-r style="font-size:clamp(1.8rem,4.6vw,3rem);text-align:center;margin:clamp(56px,9vh,110px) 0 10px;">Ce qui se passe <em>sous le capot</em></h2>
    <p data-r style="text-align:center;max-width:60ch;margin:0 auto;">Relevé sur le site en ligne, le 14 septembre 2026.</p>
    <div class="rp__chiffres">
      <div data-r><b>17</b><span>requêtes au chargement, images comprises</span></div>
      <div data-r><b>16/17</b><span>images en chargement différé</span></div>
      <div data-r><b>0</b><span>débordement horizontal, aux trois tailles testées</span></div>
      <div data-r><b>24 h/24</b><span>déclaré dans la fiche structurée, 7 jours sur 7</span></div>
    </div>
    <div class="rp__meca">
      <article data-r>
        <span class="eb">Référencement</span>
        <h3>Lisible par Google, ligne à ligne</h3>
        <p>Titre, description, adresse canonique et partage social complet (image 1920 px). Surtout : une fiche <strong style="color:var(--text)">LocalBusiness</strong> structurée qui donne le téléphone, l'adresse exacte — 554 route de Clisson, 44120 Vertou —, la zone couverte (Loire-Atlantique et Vertou), les horaires 7 j/7, et un canal déclaré « Intervention d'urgence » rattaché au numéro.</p>
        <p>C'est ce balisage qui permet à une recherche « élagueur urgence Vertou » de tomber sur la bonne entreprise, avec le bon numéro, sans passer par un annuaire payant. Plan du site et robots.txt en place.</p>
      </article>
      <article data-r>
        <span class="eb">Sécurité</span>
        <h3>Fermé comme nos propres sites</h3>
        <p>Le même durcissement que celui livré avec nos thèmes : xmlrpc refusé, énumération des comptes bloquée côté API comme côté URL, en-têtes <em>nosniff</em>, anti-encadrement, politique de référent et de permissions, HSTS d'un an, et montée en HTTPS forcée.</p>
        <p>Un site d'artisan n'est pas attaqué pour ce qu'il contient — il l'est parce qu'il est trouvable et qu'il tourne sur WordPress. Le fermer coûte une heure ; le rouvrir après coup coûte un client.</p>
      </article>
      <article data-r>
        <span class="eb">Poids</span>
        <h3>Images modernes, texte compressé</h3>
        <p>Toutes les images sont en WebP, compression Brotli sur le texte, et seize des dix-sept images attendent d'être à l'écran pour se charger. Les pages suivantes sont préchargées en arrière-plan pendant que le visiteur lit.</p>
        <p>La page reste dense — un site d'élagage se vend avec des photos, pas avec des paragraphes. Sur téléphone, la vidéo de fond est écartée, ce qui retire à elle seule plus d'un demi-mégaoctet.</p>
      </article>
      <article data-r>
        <span class="eb">Preuves</span>
        <h3>Rien d'inventé</h3>
        <p>Pas une photo de banque d'images dans les chantiers : ce sont ses arbres, son broyeur, ses murs. L'avis Google affiché est le vrai, avec sa note et son nombre réel — un seul avis, affiché comme tel.</p>
        <p>C'est une règle de la maison : on n'écrit jamais sur la page d'un client une preuve qu'il ne peut pas produire si on la lui demande.</p>
      </article>
    </div>
  </section>

  <section class="rp__gal">
    <div class="wr">
      <h2 data-r>Page <em>par page</em></h2>
      <p data-r>Chaque section répond à une question que le client posait au téléphone.</p>
      <div class="rp__shots">
        <?php foreach ( $galerie as $g ) : ?>
          <figure class="rp__shot" data-r>
            <img src="<?php echo esc_url( $img . $g[0] . '.jpg' ); ?>" alt="<?php echo esc_attr( $g[1] ); ?> — L.A Environnement" loading="lazy">
            <div><b><?php echo esc_html( $g[1] ); ?></b><span><?php echo esc_html( $g[2] ); ?></span></div>
          </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="rp__gal" style="padding-top:0">
    <div class="wr">
      <h2 data-r>Des exemples, <em>pas des promesses</em></h2>
      <p data-r>Le site ne se résume pas à son accueil. Chaque page a été écrite pour une question précise que quelqu'un tape réellement dans Google.</p>
      <div class="rp__shots">
        <?php foreach ( $exemples as $e ) : ?>
          <figure class="rp__shot" data-r>
            <img src="<?php echo esc_url( $img . $e[0] . '.jpg' ); ?>" alt="<?php echo esc_attr( $e[1] ); ?> — L.A Environnement" loading="lazy" width="1200" height="750">
            <div><b><?php echo esc_html( $e[1] ); ?></b><span><?php echo esc_html( $e[2] ); ?></span></div>
          </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="wr">
    <div class="rp__mob">
      <div data-r>
        <span class="eb">Sur le téléphone</span>
        <h2>Deux boutons, <em>toujours là</em></h2>
        <p>Un arbre menaçant se cherche dehors, sous la pluie, sur un écran de six pouces. Une barre fixe reste donc collée en bas de l'écran du début à la fin : <strong style="color:var(--text)">Appeler</strong> et <strong style="color:var(--text)">Devis</strong>. Le visiteur n'a jamais à remonter chercher un numéro.</p>
        <p style="margin-top:12px;">Le rendu a été vérifié à 390 × 844 : aucun débordement latéral, aucun texte coupé, et la vidéo de fond écartée pour ne pas manger le forfait.</p>
      </div>
      <div class="rp__phone" data-r>
        <img src="<?php echo esc_url( $img . 'la-mobile.jpg' ); ?>" alt="Le site L.A Environnement sur téléphone" loading="lazy">
      </div>
    </div>
  </section>

  <section class="rp__fin">
    <div class="wr">
      <h2 data-r>Le vôtre, <em>le mois prochain</em></h2>
      <p data-r>Audit gratuit, devis en trente secondes, livraison en cinq jours. On commence par un échange, sans engagement.</p>
      <a class="bt" data-r href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">Demander mon audit</a>
    </div>
  </section>

</div>

<script>
/* ── LA DEMO : l'arbre descend au defilement ──────────────────────────────
   Meme calcul que sur elagage-vertou.fr : la course vaut la hauteur de
   l'arbre moins celle du cadre, et on la parcourt au fur et a mesure que la
   section traverse l'ecran. Tout passe par requestAnimationFrame — un
   listener de scroll qui ecrit un transform a chaque evenement fait saccader
   sur telephone.

   Rien ne demarre si le visiteur a demande moins d'animations : le CSS pose
   deja la scene a mi-course, lisible telle quelle. */
(function(){
  var section = document.querySelector('.rp__demo');
  var arbre   = document.querySelector('[data-demo-arbre]');
  var cadre   = document.querySelector('.rp__demo-cadre');
  if (!section || !arbre || !cadre) { return; }

  var REDUIT = !!(window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches);
  if (REDUIT) { return; }

  var course = 0, attente = false;

  function mesurer(){
    // L'image peut ne pas etre encore chargee : on retombe sur le ratio connu.
    var h = arbre.offsetHeight || (arbre.offsetWidth * 5);
    course = Math.max(0, h - cadre.offsetHeight);
  }

  function placer(){
    var r = section.getBoundingClientRect();
    var total = Math.max(1, r.height - window.innerHeight);
    var p = Math.min(1, Math.max(0, -r.top / total));
    arbre.style.transform = 'translate3d(-50%,' + (-course * p) + 'px,0)';
    attente = false;
  }

  function demander(){
    if (!attente) { attente = true; requestAnimationFrame(placer); }
  }

  mesurer(); placer();
  if (!arbre.complete) { arbre.addEventListener('load', function(){ mesurer(); placer(); }); }
  addEventListener('scroll', demander, { passive: true });
  addEventListener('resize', function(){ mesurer(); demander(); });

})();

/* ── LE FOND DE PAGE : la canopee tourne derriere tout ────────────────────
   Independante de la demo : si un jour la demo disparait, le fond reste.
   Chargee APRES le reste — un demi-megaoctet de decor ne passe jamais avant
   le texte. Jamais sur petit ecran ni sous reduction de mouvement : le CSS
   la masque deja, on evite en plus d'aller la chercher sur le reseau. */
(function(){
  var video = document.querySelector('[data-fond-video]');
  if (!video) { return; }
  if (matchMedia('(max-width:960px)').matches) { return; }
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

  function lancer(){
    video.preload = 'auto';
    video.load();
    var j = video.play();
    if (j && j.catch) { j.catch(function(){}); }
  }
  if (document.readyState === 'complete') { setTimeout(lancer, 250); }
  else { addEventListener('load', function(){ setTimeout(lancer, 250); }); }

  /* Onglet en arriere-plan : on met en pause. Une video qui tourne dans un
     onglet invisible, c'est de la batterie pour personne. */
  document.addEventListener('visibilitychange', function(){
    if (document.hidden) { video.pause(); }
    else { var j = video.play(); if (j && j.catch) { j.catch(function(){}); } }
  });
})();

(function(){
  var els = document.querySelectorAll('.rp [data-r]');
  if (!('IntersectionObserver' in window)) {
    els.forEach(function(e){ e.classList.add('vu'); });
    return;
  }
  var i = 0;
  var ob = new IntersectionObserver(function(entries){
    entries.forEach(function(en){
      if (!en.isIntersecting) return;
      var el = en.target;
      el.style.transitionDelay = ((i++ % 4) * 70) + 'ms';
      el.classList.add('vu');
      ob.unobserve(el);
    });
  }, { threshold: .12, rootMargin: '0px 0px -8% 0px' });
  els.forEach(function(e){ ob.observe(e); });
})();
</script>

<?php get_footer();
