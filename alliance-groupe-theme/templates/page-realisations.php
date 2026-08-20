<?php
/**
 * Template Name: Réalisations
 *
 * Page « Nos réalisations » — étude de cas Gwen Services (aide à domicile, Nantes).
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
<style>
  .rp{--gold:#d4b45c;--gold-hi:#f4d06f;--ink:#05050a;--panel:#0b0b12;--text:#eef1f6;--muted:#9aa3b4;
      --serif:"Playfair Display",Georgia,serif;--vert:#3f9d6d;
      background:var(--ink);color:var(--text);overflow-x:hidden}
  .rp *{box-sizing:border-box}
  .rp img{display:block;max-width:100%}
  .rp .wr{max-width:1180px;margin:0 auto;padding:0 26px}
  .rp .eb{font-size:.7rem;letter-spacing:.34em;text-transform:uppercase;color:var(--gold);font-weight:700}
  .rp h1,.rp h2,.rp h3{font-family:var(--serif);font-weight:500;line-height:1.05;margin:0}
  .rp em{font-style:italic;color:var(--gold-hi)}
  .rp p{margin:0;line-height:1.7;color:var(--muted)}
  .rp a{color:inherit}
  .rp .bt{display:inline-block;background:linear-gradient(120deg,var(--gold),var(--gold-hi));color:#1a1206;font-weight:800;
     text-decoration:none;border-radius:999px;padding:15px 32px;font-size:.96rem;
     box-shadow:0 18px 44px -18px rgba(212,180,92,.75);transition:transform .25s,box-shadow .25s}
  .rp .bt:hover{transform:translateY(-2px);box-shadow:0 24px 54px -20px rgba(212,180,92,.95)}
  .rp .bt--g{background:transparent;color:var(--text);border:1px solid rgba(255,255,255,.22);box-shadow:none;font-weight:600}
  .rp .bt--g:hover{border-color:var(--gold)}

  /* héros */
  .rp__hero{position:relative;padding:clamp(96px,15vh,170px) 0 clamp(40px,7vh,80px);text-align:center;overflow:hidden}
  .rp__halo{position:absolute;left:50%;top:34%;transform:translate(-50%,-50%);width:min(96vw,1200px);height:70vh;pointer-events:none;
     background:radial-gradient(closest-side,rgba(63,157,109,.18),transparent 70%)}
  .rp__hero h1{font-size:clamp(2.6rem,7vw,5.4rem);letter-spacing:-.02em;margin:14px 0 18px}
  .rp__lead{max-width:60ch;margin:0 auto;font-size:clamp(1rem,2.1vw,1.14rem)}
  .rp__tags{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:26px 0 34px}
  .rp__tags span{border:1px solid rgba(212,180,92,.34);border-radius:999px;padding:8px 18px;font-size:.8rem;color:#cdd4e2}
  .rp__maq{position:relative;z-index:2;max-width:1080px;margin:0 auto;border-radius:20px;overflow:hidden;
     border:1px solid rgba(212,180,92,.22);box-shadow:0 70px 130px -60px rgba(0,0,0,.95)}
  .rp__cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-top:34px}

  /* fiche */
  .rp__fiche{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:26px;
     padding:clamp(56px,9vh,110px) 0 clamp(30px,5vh,60px)}
  .rp__fiche article{background:var(--panel);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:26px 24px 28px}
  .rp__fiche h3{font-size:1.3rem;margin:10px 0 12px}
  .rp__fiche p{font-size:.97rem}
  .rp__fiche ul{margin:12px 0 0;padding-left:18px;color:var(--muted);font-size:.95rem;line-height:1.75}
  .rp__fiche li::marker{color:var(--gold)}

  /* galerie */
  .rp__gal{padding:clamp(40px,7vh,80px) 0 clamp(60px,10vh,120px)}
  .rp__gal h2{font-size:clamp(1.8rem,4.6vw,3rem);text-align:center;margin-bottom:10px}
  .rp__gal>.wr>p{text-align:center;max-width:56ch;margin:0 auto 44px}
  .rp__shots{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:26px}
  .rp__shot{background:var(--panel);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;
     transition:border-color .35s,transform .35s}
  .rp__shot:hover{border-color:rgba(212,180,92,.5);transform:translateY(-4px)}
  .rp__shot img{width:100%;height:auto}
  .rp__shot div{padding:16px 18px 20px}
  .rp__shot b{display:block;font-size:1rem;margin-bottom:6px}
  .rp__shot span{color:var(--muted);font-size:.88rem;line-height:1.5}

  /* mobile mis en avant */
  .rp__mob{display:grid;grid-template-columns:1.1fr .9fr;gap:clamp(26px,5vw,70px);align-items:center;
     padding:clamp(50px,8vh,100px) 0;border-top:1px solid rgba(255,255,255,.07)}
  .rp__mob h2{font-size:clamp(1.7rem,4.2vw,2.8rem);margin-bottom:16px}
  .rp__phone{justify-self:center;width:min(74vw,300px);border:10px solid #0e0e12;border-radius:38px;overflow:hidden;
     box-shadow:0 60px 110px -50px rgba(0,0,0,.95),0 0 0 1px rgba(212,180,92,.25)}

  /* bande finale */
  .rp__fin{text-align:center;padding:clamp(60px,11vh,140px) 0;border-top:1px solid rgba(255,255,255,.07)}
  .rp__fin h2{font-size:clamp(1.9rem,5vw,3.4rem);margin-bottom:18px}
  .rp__fin p{max-width:50ch;margin:0 auto 30px}

  [data-r]{opacity:0;transform:translateY(28px);transition:opacity .8s cubic-bezier(.22,1,.3,1),transform .8s cubic-bezier(.22,1,.3,1)}
  [data-r].vu{opacity:1;transform:none}

  @media(max-width:960px){
    .rp__fiche{grid-template-columns:1fr;gap:18px;padding-top:44px}
    .rp__shots{grid-template-columns:1fr;gap:18px}
    .rp__mob{grid-template-columns:1fr;gap:28px;text-align:center}
    .rp__maq{border-radius:14px}
    .rp__hero{padding-top:clamp(84px,12vh,120px)}
  }
  @media(prefers-reduced-motion:reduce){[data-r]{opacity:1;transform:none;transition:none}}
</style>

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
