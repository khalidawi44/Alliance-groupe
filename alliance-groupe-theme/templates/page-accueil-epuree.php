<?php
/**
 * Template Name: Accueil épurée (audit-first)
 *
 * Home décluttée, audit-first, studio solo. Réutilise l'esthétique
 * « cabinet d'audit » (assets/css/audit-home.css, chargé via functions.php,
 * styles du thème déchargés sur ce template). Standalone (header/footer propres).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$ag_mail   = 'contact@alliancegroupe-inc.com';
$ag_audit  = home_url( '/ag-audit' );
$ag_create = home_url( '/sites-express' );
$ag_maint  = home_url( '/maintenance' );
$ag_voyage = home_url( '/experience' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'ag-audit-home ag-home-v2' ); ?>>

<header>
  <div class="container">
    <div class="header-inner">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo"><span class="logo-mark"></span>Alliance Groupe</a>
      <nav>
        <ul>
          <li><a href="#securite">Sécurité</a></li>
          <li><a href="#creation">Création</a></li>
          <li><a href="<?php echo esc_url( $ag_voyage ); ?>">L'expérience</a></li>
          <li><a href="#contact" class="cta-button">Audit gratuit →</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-meta">Studio web &amp; sécurité · Nantes</div>
    <h1>Je <em>sécurise</em> et je crée<br>des sites qui inspirent confiance.</h1>
    <p class="hero-sub">Un artisan indépendant, un interlocuteur unique. On commence par un audit qui révèle vos failles — puis on corrige, on (re)construit, et on veille. Sans jargon, sans intermédiaire.</p>
    <div class="hero-actions">
      <a href="#contact" class="cta-button">Mon audit gratuit →</a>
      <a href="#creation" class="cta-button ghost">Créer un site</a>
      <span class="small">Réponse sous 24 h · Devis gratuit</span>
    </div>
    <div class="dossier-stamp">
      <strong>Studio · Nantes</strong>
      <div class="ref-line"><span>Sécurité</span><span class="v">Audit · Remédiation</span></div>
      <div class="ref-line"><span>Création</span><span class="v">Sites sur-mesure</span></div>
      <div class="ref-line"><span>Sérénité</span><span class="v">Maintenance</span></div>
    </div>
  </div>
</section>

<!-- LE RISQUE -->
<section class="stats-band" id="risque">
  <div class="container">
    <div class="section-tag">⟶ Pourquoi maintenant</div>
    <h2 class="section-h2">La question n'est pas <em>si</em>, mais <em>quand</em>.</h2>
    <div class="stats-grid">
      <div class="stat">
        <div class="num">30 000</div>
        <div class="label">sites web piratés chaque jour dans le monde.</div>
        <div class="source">Source · Sucuri 2024</div>
      </div>
      <div class="stat">
        <div class="num">73 %</div>
        <div class="label">des sites ont au moins un composant tiers avec une faille connue.</div>
        <div class="source">Source · Rapports sécurité 2024</div>
      </div>
      <div class="stat">
        <div class="num">196 j</div>
        <div class="label">avant qu'une PME détecte une intrusion sur son site.</div>
        <div class="source">Source · IBM Cost of a Breach</div>
      </div>
    </div>
  </div>
</section>

<!-- 2 EXPERTISES -->
<section class="audit-section">
  <div class="container">
    <div class="audit-header">
      <div>
        <div class="section-tag">⟶ Deux façons de travailler ensemble</div>
        <h2 class="section-h2">Sécuriser. Créer. <em>Sereinement.</em></h2>
      </div>
      <p>Que votre site existe déjà ou soit à venir, le point d'entrée est le même : on met les choses au clair, puis on agit. Un seul interlocuteur, du conseil à la livraison.</p>
    </div>
    <div class="audit-grid">
      <div class="audit-item" id="securite">
        <div class="audit-num">01</div>
        <div class="audit-content">
          <h3>🔒 Sécurité</h3>
          <p>Audit de sécurité en 48 h, remédiation des failles, puis maintenance continue (mises à jour, sauvegardes, monitoring).</p>
          <span class="severity-tag warn"><a href="<?php echo esc_url( $ag_audit ); ?>" style="color:inherit;">Commencer par l'audit →</a></span>
        </div>
      </div>
      <div class="audit-item" id="creation">
        <div class="audit-num">02</div>
        <div class="audit-content">
          <h3>✨ Création</h3>
          <p>Sites vitrines, e-commerce, plateformes métier — conçus rapides, propres et sécurisés dès le départ.</p>
          <span class="severity-tag warn"><a href="<?php echo esc_url( $ag_create ); ?>" style="color:inherit;">Créer mon site →</a></span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">03</div>
        <div class="audit-content">
          <h3>🛡️ Maintenance</h3>
          <p>Votre site reste à jour, protégé et sauvegardé — chaque mois, sans que vous y pensiez.</p>
          <span class="severity-tag warn"><a href="<?php echo esc_url( $ag_maint ); ?>" style="color:inherit;">Voir les formules →</a></span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">04</div>
        <div class="audit-content">
          <h3>🌍 L'expérience</h3>
          <p>Découvrez la menace en direct et ma méthode dans un parcours immersif (la démo de ce que je sais construire).</p>
          <span class="severity-tag warn"><a href="<?php echo esc_url( $ag_voyage ); ?>" style="color:inherit;">Vivre l'expérience →</a></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MÉTHODE -->
<section class="process">
  <div class="container">
    <div class="section-tag">⟶ Comment ça marche</div>
    <h2 class="section-h2">Trois temps, <em>zéro mauvaise surprise.</em></h2>
    <div class="process-grid">
      <div class="step">
        <div class="step-num">01</div>
        <h3>J'audite</h3>
        <p>Je révèle les failles de votre site en 48 h. Rapport clair, recommandations chiffrées.</p>
        <div class="duration">Sous 48 h</div>
      </div>
      <div class="step">
        <div class="step-num">02</div>
        <h3>Je corrige ou je crée</h3>
        <p>Je reprends votre site et le sécurise — ou j'en construis un neuf, blindé dès le départ.</p>
        <div class="duration">Sur devis</div>
      </div>
      <div class="step">
        <div class="step-num">03</div>
        <h3>Je maintiens</h3>
        <p>Mises à jour, sauvegardes, monitoring : votre site reste sain dans la durée.</p>
        <div class="duration">Chaque mois</div>
      </div>
      <div class="step">
        <div class="step-num">04</div>
        <h3>Vous êtes tranquille</h3>
        <p>Un seul interlocuteur, joignable, qui connaît votre site. Pas de service client anonyme.</p>
        <div class="duration">Toujours</div>
      </div>
    </div>
  </div>
</section>

<!-- PREUVE -->
<section class="testimonials">
  <div class="container">
    <div class="section-tag">⟶ Quelques chantiers</div>
    <h2 class="section-h2">Des projets <em>concrets</em>, des résultats mesurables.</h2>
    <div class="testimonials-grid">
      <div class="testi">
        <div class="testi-quote">« Refonte complète, design immersif pour mes portraits et SEO local. Le trafic organique a presque triplé en six mois. »</div>
        <div class="testi-author">
          <div><div class="name">Anna B.</div><div class="role">Photographe portraitiste · Nantes</div></div>
          <a href="https://annaphoto.eu/" class="link" target="_blank" rel="noopener">Voir →</a>
        </div>
      </div>
      <div class="testi">
        <div class="testi-quote">« Site vitrine optimisé pour générer des demandes de devis en local. On est passé de 1 à 15 demandes par mois en deux trimestres. »</div>
        <div class="testi-author">
          <div><div class="name">L.A Environnement</div><div class="role">Paysagiste · Loire-Atlantique</div></div>
          <a href="https://www.paysagiste-environnement.com/" class="link" target="_blank" rel="noopener">Voir →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="final-cta" id="contact">
  <div class="container">
    <h2>Découvrons les <em>angles morts</em> de votre site.</h2>
    <p>Audit confidentiel sous 48 h. Devis gratuit. Si l'on ne trouve rien d'exploitable, on vous le dit honnêtement.</p>
    <a href="<?php echo esc_url( 'mailto:' . $ag_mail . '?subject=' . rawurlencode( "Demande d'audit" ) ); ?>" class="cta-button">Demander mon audit →</a>
  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo"><span class="logo-mark"></span>Alliance Groupe</a>
        <p>Studio indépendant d'audit de sécurité web, de création de sites et de maintenance, basé à Nantes.</p>
      </div>
      <div>
        <h5>Services</h5>
        <ul>
          <li><a href="<?php echo esc_url( $ag_audit ); ?>">Audit de sécurité</a></li>
          <li><a href="<?php echo esc_url( $ag_create ); ?>">Création de site</a></li>
          <li><a href="<?php echo esc_url( $ag_maint ); ?>">Maintenance</a></li>
        </ul>
      </div>
      <div>
        <h5>Studio</h5>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/a-propos' ) ); ?>">À propos</a></li>
          <li><a href="<?php echo esc_url( 'mailto:' . $ag_mail ); ?>"><?php echo esc_html( $ag_mail ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?php echo esc_html( date( 'Y' ) ); ?> ALLIANCE GROUPE · TOUS DROITS RÉSERVÉS</span>
      <span>NANTES · FRANCE</span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
