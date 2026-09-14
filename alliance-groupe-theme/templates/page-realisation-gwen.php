<?php
/**
 * Template Name: Réalisation — Gwen Services
 *
 * Étude de cas détaillée : le site Gwen Services (aide à domicile, Nantes).
 * La page portfolio générale reste page-realisations.php.
 * Autonome : CSS et JS inline, aucune dépendance de plugin ni de librairie.
 *
 * @package Alliance_Groupe
 */

get_header();
$dir = get_stylesheet_directory_uri();
$img = $dir . '/assets/images/realisations/';

$galerie = array(
	array( 'accueil',          'La page d\'accueil',      'Une promesse claire dès la première seconde : ce que Gwen fait, où, et le crédit d\'impôt de 50 %.' ),
	array( 'services',         'Les prestations',         'Huit services lisibles d\'un coup d\'œil, pensés pour des familles pressées.' ),
	array( 'etapes',           'La méthode en 3 temps',   'Premier échange, planning adapté, présence de confiance — le parcours est raconté, pas listé.' ),
	array( 'galerie-seniors',  'Auprès des personnes âgées', 'Des images générées sur mesure : même intervenante, même lumière, même maison.' ),
	array( 'galerie-enfants',  'La garde d\'enfants',     'Le deuxième métier de Gwen, traité avec la même cohérence visuelle.' ),
	array( 'chiffres',         'Les preuves',             'Chiffres, garanties et arguments regroupés là où la décision se prend.' ),
	array( 'faq',              'Les questions qui bloquent', 'Crédit d\'impôt, âge des enfants, week-ends : les objections traitées avant l\'appel.' ),
);
?>
<?php get_template_part( 'template-parts/realisation-style' ); ?>

<div class="rp">

  <section class="rp__hero">
    <div class="rp__halo"></div>
    <div class="wr">
      <span class="eb" data-r>Réalisation · Aide à domicile</span>
      <h1 data-r>Gwen <em>Services</em></h1>
      <p class="rp__lead" data-r>Une auxiliaire de vie à Nantes qui n'avait rien en ligne. En cinq jours : un site complet, ses images, ses textes, son référencement et sa sécurité — livré prêt à recevoir des appels.</p>
      <div class="rp__tags" data-r>
        <span>Site vitrine</span><span>Images générées sur mesure</span><span>SEO local</span><span>Sécurité incluse</span><span>Livré en 5 jours</span>
      </div>
      <div class="rp__maq" data-r>
        <img src="<?php echo esc_url( $img . 'gwen-maquette.jpg' ); ?>" alt="Le site Gwen Services sur ordinateur et sur téléphone" fetchpriority="high">
      </div>
      <div class="rp__cta" data-r>
        <a class="bt" href="https://gwen-services.alliancegroupe-inc.com/" target="_blank" rel="noopener">Voir le site en ligne →</a>
        <a class="bt bt--g" href="<?php echo esc_url( home_url( '/devis-instant' ) ); ?>">Je veux le mien</a>
      </div>
    </div>
  </section>

  <section class="wr">
    <div class="rp__fiche">
      <article data-r>
        <span class="eb">Le besoin</span>
        <h3>Exister, vite</h3>
        <p>Gwen travaillait au bouche-à-oreille. Pas de site, pas de fiche Google, rien à envoyer à une famille qui hésite. Il fallait un endroit crédible où l'on comprend en dix secondes ce qu'elle fait — et combien ça coûte vraiment après le crédit d'impôt.</p>
      </article>
      <article data-r>
        <span class="eb">Ce qu'on a fait</span>
        <h3>Tout, de A à Z</h3>
        <ul>
          <li>Conception et développement du site</li>
          <li>Rédaction complète des textes</li>
          <li>Images générées sur mesure — cohérence de visage, de lieu et de lumière</li>
          <li>Référencement local (Nantes et alentours)</li>
          <li>Sécurisation, sauvegardes et surveillance</li>
        </ul>
      </article>
      <article data-r>
        <span class="eb">Le résultat</span>
        <h3>Un site qui répond à sa place</h3>
        <p>Les objections qui faisaient perdre du temps au téléphone — le crédit d'impôt, l'âge des enfants gardés, les week-ends — sont traitées sur la page. Le devis se demande en un clic, jour et nuit.</p>
      </article>
    </div>
  </section>

  <section class="rp__gal">
    <div class="wr">
      <h2 data-r>Page <em>par page</em></h2>
      <p data-r>Chaque section a été pensée pour une famille qui cherche quelqu'un de confiance, pas pour un jury de design.</p>
      <div class="rp__shots">
        <?php foreach ( $galerie as $g ) : ?>
          <figure class="rp__shot" data-r>
            <img src="<?php echo esc_url( $img . 'gwen-' . $g[0] . '.jpg' ); ?>" alt="<?php echo esc_attr( $g[1] ); ?> — Gwen Services" loading="lazy">
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
        <h2>C'est là que <em>ça se joue</em></h2>
        <p>Huit visiteurs sur dix arrivent depuis un mobile, souvent depuis une recherche Google faite dans l'urgence. Le site a donc été dessiné pour le pouce avant de l'être pour la souris : titre lisible sans zoomer, bouton de devis toujours à portée, chargement immédiat.</p>
      </div>
      <div class="rp__phone" data-r>
        <img src="<?php echo esc_url( $img . 'gwen-mobile.jpg' ); ?>" alt="Le site Gwen Services sur téléphone" loading="lazy">
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
