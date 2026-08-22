<?php
/**
 * Template Name: Audit sécurité (home v2)
 *
 * Landing "audit-first" — esthétique cabinet d'audit confidentiel.
 * Standalone (header/nav/footer propres) : on n'utilise pas get_header/get_footer.
 * Styles dans assets/css/audit-home.css (chargés via functions.php, styles du
 * thème déchargés sur ce template pour éviter les conflits).
 *
 * Texte volontairement GÉNÉRIQUE (audit de sécurité de site web, pas « WordPress »).
 * Aucune équipe nommée (cohérent avec le positionnement « cabinet indépendant »).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$ag_mail = 'contact@alliancegroupe-inc.com';
$ag_test = home_url( '/tester-mon-site' );
$ag_dir  = get_template_directory_uri();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'ag-audit-home' ); ?>>

<!-- ─── HEADER ─── -->
<header>
  <div class="container">
    <div class="header-inner">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
        <span class="logo-mark"></span>Alliance Groupe
      </a>
      <nav>
        <ul>
          <li><a href="#audit">L'audit</a></li>
          <li><a href="#methode">Méthode</a></li>
          <li><a href="#services">Au-delà</a></li>
          <li><a href="<?php echo esc_url( $ag_test ); ?>" class="cta-button">Tester mon site →</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<!-- ─── HERO ─── -->
<section class="hero">
  <img class="hero-embleme" aria-hidden="true"
       src="<?php echo esc_url( $ag_dir . '/assets/images/securite/ag-bouclier-decoupe.png' ); ?>"
       alt="" width="1080" height="1080" loading="eager" decoding="async">
  <div class="container">
    <div class="hero-meta">Cabinet d'audit sécurité · Nantes</div>
    <h1>Votre site vous expose-t-il<br>à un <em>risque</em> ?</h1>
    <p class="hero-sub">Un audit confidentiel le révèle en 48 h. Rapport PDF détaillé, recommandations actionnables, sans jargon. Avant qu'un attaquant le trouve à votre place.</p>
    <div class="hero-actions">
      <a href="<?php echo esc_url( $ag_test ); ?>" class="cta-button">Tester mon site — gratuit →</a>
      <a href="#audit" class="cta-button ghost">Voir ce qu'on analyse</a>
      <span class="small">Réponse sous 24 h · Devis gratuit</span>
    </div>

    <div class="dossier-stamp">
      <strong>Dossier · Exemple</strong>
      <div class="ref-line"><span>Référence</span><span class="v">AG-2026-014</span></div>
      <div class="ref-line"><span>Cible</span><span class="v">cabinet-x.fr</span></div>
      <div class="ref-line"><span>Findings</span><span class="v">17 (2 critiques)</span></div>
      <div class="ref-line"><span>Livré</span><span class="v">48 h</span></div>
    </div>
  </div>
</section>

<!-- ─── STATS ─── -->
<section class="stats-band">
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
        <div class="label">des sites contiennent au moins un composant tiers (extension, thème, bibliothèque) avec une faille connue.</div>
        <div class="source">Source · Rapports sécurité 2024</div>
      </div>
      <div class="stat">
        <div class="num">196 j</div>
        <div class="label">temps moyen avant qu'une PME détecte une intrusion sur son site.</div>
        <div class="source">Source · IBM Cost of a Breach</div>
      </div>
    </div>
  </div>
</section>

<!-- ─── WHAT WE AUDIT ─── -->
<section class="audit-section" id="audit">
  <div class="container">
    <div class="audit-header">
      <div>
        <div class="section-tag">⟶ Ce qu'on regarde</div>
        <h2 class="section-h2">Un examen <em>méthodique</em>, par couches.</h2>
      </div>
      <p>Notre audit suit une méthodologie reproductible en cinq phases. Chaque point est documenté, scoré et accompagné d'une recommandation concrète. Pas de jargon, pas de scan automatisé jeté tel quel — un livrable lisible par votre direction.</p>
    </div>

    <div class="audit-grid">
      <div class="audit-item">
        <div class="audit-num">01</div>
        <div class="audit-content">
          <h3>En-têtes HTTP &amp; TLS</h3>
          <p>Configuration TLS, suites de chiffrement, présence des en-têtes de sécurité (HSTS, CSP, X-Frame-Options).</p>
          <span class="severity-tag warn">Impact moyen</span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">02</div>
        <div class="audit-content">
          <h3>Fichiers exposés</h3>
          <p>Sauvegardes oubliées, fichiers de configuration et logs accessibles, dépôts git publics — fuites courantes mais sous-estimées.</p>
          <span class="severity-tag critical">Impact critique</span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">03</div>
        <div class="audit-content">
          <h3>Composants tiers vulnérables</h3>
          <p>Extensions, thèmes et bibliothèques : corrélation avec les bases CVE publiques. Versions obsolètes, vulnérabilités connues.</p>
          <span class="severity-tag critical">Impact critique</span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">04</div>
        <div class="audit-content">
          <h3>Authentification &amp; énumération</h3>
          <p>Vecteurs classiques de bruteforce, anciens endpoints, énumération des comptes utilisateurs, exposition d'API.</p>
          <span class="severity-tag warn">Impact moyen</span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">05</div>
        <div class="audit-content">
          <h3>Sessions &amp; cookies</h3>
          <p>Configuration des cookies (Secure, HttpOnly, SameSite), protection contre la prise de contrôle de session.</p>
          <span class="severity-tag warn">Impact moyen</span>
        </div>
      </div>
      <div class="audit-item">
        <div class="audit-num">06</div>
        <div class="audit-content">
          <h3>Hygiène applicative</h3>
          <p>Version du CMS/serveur exposée, configuration PHP, inscription publique, formulaires sans protection anti-spam.</p>
          <span class="severity-tag">Impact faible</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── DELIVERABLE / REPORT ─── -->
<section class="deliverable">
  <div class="container">
    <div class="deliverable-grid">
      <div>
        <div class="section-tag">⟶ Ce que vous recevez</div>
        <h2 class="section-h2">Un <em>rapport</em> que votre direction peut lire.</h2>

        <ul class="deliverable-list">
          <li>
            <span>Résumé exécutif d'une page<small>Niveau de risque global, top vulnérabilités, recommandations chiffrées</small></span>
          </li>
          <li>
            <span>Findings détaillés, classés par gravité<small>Description, preuve, impact business, recommandation, effort</small></span>
          </li>
          <li>
            <span>Plan d'action priorisé<small>Croisement gravité × effort, prêt à exécuter en interne ou avec nous</small></span>
          </li>
          <li>
            <span>Configurations prêtes à coller<small>En-têtes serveur, durcissement, règles .htaccess / nginx — testés en production</small></span>
          </li>
          <li>
            <span>Restitution orale en visio (45 min)<small>Walkthrough du rapport, questions, vulgarisation pour décideur non-tech</small></span>
          </li>
        </ul>
      </div>

      <div class="report-mockup">
        <div class="report-header">
          <span>ALLIANCE GROUPE · CONFIDENTIEL</span>
          <span>14 / 28</span>
        </div>
        <div class="report-body">
          <div class="ref">AG-AUDIT-2026-014</div>
          <h4>Rapport d'audit de sécurité</h4>
          <div class="sub">Section 4 — Vulnérabilités identifiées</div>

          <div class="findings-row head">
            <span>Réf.</span>
            <span>Vulnérabilité</span>
            <span>Gravité</span>
          </div>
          <div class="findings-row">
            <span class="id">F-001</span>
            <span class="title">Sauvegarde de configuration exposée</span>
            <span><span class="sev crit">Critique</span></span>
          </div>
          <div class="findings-row">
            <span class="id">F-002</span>
            <span class="title">Composant tiers obsolète (CVE-2024-9183)</span>
            <span><span class="sev high">Élevée</span></span>
          </div>
          <div class="findings-row">
            <span class="id">F-003</span>
            <span class="title">Endpoint d'authentification exposé (bruteforce)</span>
            <span><span class="sev high">Élevée</span></span>
          </div>
          <div class="findings-row">
            <span class="id">F-004</span>
            <span class="title">Énumération des comptes</span>
            <span><span class="sev med">Moyenne</span></span>
          </div>
          <div class="findings-row">
            <span class="id">F-005</span>
            <span class="title">En-têtes HSTS, CSP absents</span>
            <span><span class="sev low">Faible</span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── PROCESS ─── -->
<section class="process" id="methode">
  <div class="container">
    <div class="section-tag">⟶ Comment ça marche</div>
    <h2 class="section-h2">Quatre étapes, <em>aucun rendez-vous</em> requis.</h2>

    <div class="process-grid">
      <div class="step">
        <div class="step-num">01</div>
        <h3>Vous nous transmettez</h3>
        <p>L'URL du site et l'autorisation écrite signée (modèle fourni). Pas besoin d'accès admin.</p>
        <div class="duration">5 minutes</div>
      </div>
      <div class="step">
        <div class="step-num">02</div>
        <h3>Scan automatisé</h3>
        <p>Notre outil propriétaire AG-Audit lance les contrôles. Aucun bruteforce, aucun risque pour votre site en production.</p>
        <div class="duration">20 minutes</div>
      </div>
      <div class="step">
        <div class="step-num">03</div>
        <h3>Vérification humaine</h3>
        <p>Chaque finding est relu, vérifié, priorisé selon votre contexte business. C'est ce qui fait la différence avec un scan brut.</p>
        <div class="duration">2 à 4 heures</div>
      </div>
      <div class="step">
        <div class="step-num">04</div>
        <h3>Livraison &amp; restitution</h3>
        <p>Vous recevez le rapport PDF, puis on s'appelle 45 min pour le parcourir ensemble et répondre à vos questions.</p>
        <div class="duration">Sous 48 heures</div>
      </div>
    </div>
  </div>
</section>

<!-- ─── BEYOND / OTHER SERVICES ─── -->
<section class="beyond" id="services">
  <div class="container">
    <div class="beyond-inner">
      <div>
        <div class="section-tag">⟶ Au-delà de l'audit</div>
        <h2 class="section-h2">Si vous voulez <em>aller plus loin</em>.</h2>
        <p>L'audit est notre point d'entrée le plus honnête : il met les choses au clair, sans engagement de poursuite. Si vous souhaitez nous confier les corrections ou un projet plus large, voici ce qu'on fait.</p>
      </div>

      <ul class="services-list">
        <li>
          <strong>Création de site sur-mesure</strong>
          <small>Sites vitrines, e-commerce, plateformes métier — à partir de 1 800 €</small>
        </li>
        <li>
          <strong>Remédiation post-audit</strong>
          <small>Application directe des correctifs identifiés, accompagnement</small>
        </li>
        <li>
          <strong>Maintenance &amp; monitoring</strong>
          <small>Surveillance continue, mises à jour, sauvegardes chiffrées</small>
        </li>
        <li>
          <strong>SEO technique &amp; sémantique</strong>
          <small>Audit, refonte de structure, contenu IA-assisté supervisé</small>
        </li>
      </ul>
    </div>
  </div>
</section>

<!-- ─── TESTIMONIALS / CASES ─── -->
<section class="testimonials">
  <div class="container">
    <div class="section-tag">⟶ Quelques chantiers</div>
    <h2 class="section-h2">Des projets <em>concrets</em>, des résultats mesurables.</h2>

    <div class="testimonials-grid">
      <div class="testi">
        <div class="testi-quote">« Refonte complète, design immersif pour mes portraits et SEO local. Le trafic organique a presque triplé en six mois. »</div>
        <div class="testi-author">
          <div>
            <div class="name">Anna B.</div>
            <div class="role">Photographe portraitiste · Nantes</div>
          </div>
          <a href="https://annaphoto.eu/" class="link" target="_blank" rel="noopener">Voir →</a>
        </div>
      </div>

      <div class="testi">
        <div class="testi-quote">« Site vitrine optimisé pour générer des demandes de devis en local. On est passé de 1 à 15 demandes par mois en deux trimestres. »</div>
        <div class="testi-author">
          <div>
            <div class="name">L.A Environnement</div>
            <div class="role">Paysagiste · Loire-Atlantique</div>
          </div>
          <a href="https://www.paysagiste-environnement.com/" class="link" target="_blank" rel="noopener">Voir →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── FINAL CTA ─── -->
<section class="final-cta" id="contact">
  <div class="container">
    <figure class="cta-embleme">
      <video autoplay muted loop playsinline preload="none"
             poster="<?php echo esc_url( $ag_dir . '/assets/videos/embleme-securite-poster.jpg' ); ?>">
        <source src="<?php echo esc_url( $ag_dir . '/assets/videos/embleme-securite-720.webm' ); ?>" type="video/webm">
        <source src="<?php echo esc_url( $ag_dir . '/assets/videos/embleme-securite-720.mp4' ); ?>" type="video/mp4">
        <img src="<?php echo esc_url( $ag_dir . '/assets/images/atelier/securite.webp' ); ?>"
             alt="Emblème Alliance Groupe : un bouclier de marbre noir et d'or frappé d'un cadenas"
             width="1000" height="563" loading="lazy" decoding="async">
      </video>
    </figure>
    <h2>Découvrons les <em>angles morts</em> de votre site.</h2>
    <p>Audit confidentiel sous 48 h. Devis gratuit. Si l'on ne trouve rien d'exploitable, on vous le dit honnêtement.</p>
    <a href="<?php echo esc_url( $ag_test ); ?>" class="cta-button">Tester mon site — gratuit →</a>
  </div>
</section>

<!-- ─── FOOTER ─── -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo"><span class="logo-mark"></span>Alliance Groupe</a>
        <p>Cabinet indépendant d'audit de sécurité web et d'accompagnement, basé à Nantes.</p>
      </div>
      <div>
        <h5>Contact</h5>
        <ul>
          <li><a href="<?php echo esc_url( 'mailto:' . $ag_mail ); ?>"><?php echo esc_html( $ag_mail ); ?></a></li>
          <li><a href="tel:+33744829516">07 44 82 95 16</a></li>
        </ul>
      </div>
      <div>
        <h5>À propos</h5>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/a-propos' ) ); ?>">L'agence</a></li>
          <li><a href="<?php echo esc_url( home_url( '/articles' ) ); ?>">Articles</a></li>
          <li><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales</a></li>
          <li><a href="<?php echo esc_url( home_url( '/confidentialite' ) ); ?>">Confidentialité</a></li>
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
