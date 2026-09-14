<?php
/**
 * Template Name: Réalisation — L.A Environnement
 *
 * Étude de cas détaillée : elagage-vertou.fr (élagage, abattage et création de
 * jardin, Vertou / Loire-Atlantique). Même habillage que l'étude Gwen, via
 * template-parts/realisation-style.php.
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
	array( 'la-tarifs',      'Les tarifs annoncés',      'Les fourchettes sont sur la page. Un artisan qui affiche ses prix n\'a plus à se justifier au téléphone.' ),
	array( 'la-devis',       'La demande de devis',      'Une seule question posée au visiteur : ce qui vous inquiète. Le formulaire vient après, jamais avant.' ),
);
?>
<?php get_template_part( 'template-parts/realisation-style' ); ?>
<style>
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
  @media(max-width:960px){
    .rp__meca{grid-template-columns:1fr;gap:16px}
    .rp__chiffres{grid-template-columns:repeat(2,minmax(0,1fr))}
  }
</style>

<div class="rp">

  <section class="rp__hero">
    <div class="rp__halo"></div>
    <div class="wr">
      <span class="eb" data-r>Réalisation · Élagage &amp; abattage</span>
      <h1 data-r>L.A <em>Environnement</em></h1>
      <p class="rp__lead" data-r>Un arboriste-grimpeur de Vertou qui travaillait au bouche-à-oreille et au flyer. Aujourd'hui : un site qui change d'apparence selon l'heure et la saison, qui affiche ses tarifs, montre ses chantiers en photos réelles, et prend les urgences jour et nuit.</p>
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
