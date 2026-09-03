# Product

<!-- impeccable:product-schema 1 -->

> Contexte produit durable d'Alliance Groupe. Ce fichier ne décrit **aucun** parti pris visuel
> (couleurs, typo, composants) : ça, c'est le rôle de `DESIGN.md`, écrit plus tard.
> Règle maison : rien d'inventé ici. Ce qui n'est pas confirmé est marqué « non tranché ».

## Platform

web

## Users

**Public prioritaire (confirmé par Fabrice) : le dirigeant de PME / l'artisan.**
Quand une décision de design oppose deux audiences, c'est lui qui gagne. Contexte type : patron
pressé, souvent seul à décider, consultation majoritairement mobile, il veut savoir vite
« combien, en combien de temps, et est-ce que ces gens sont sérieux ». Son job : obtenir une
présence web crédible sans y passer des semaines ni y laisser plusieurs milliers d'euros.

Publics secondaires, réels mais non prioritaires :

- **Ambassadeurs / recruteurs** — vendeurs terrain inscrits (KYC : selfie en direct + Telegram
  obligatoires, zone attribuée par département). Le site est leur outil de travail : dashboard,
  CRM partagé, classement, kit print, Studio. Commission 10 %, prime de parrainage 25 €,
  zone supplémentaire 49 €.
- **Acheteurs de templates / composants** — confrères et petites structures qui achètent un
  thème métier (licence Premium 99 € / Business 149 €) ou un composant sur la marketplace
  (1,99 / 2,99 / 4,99 / 9,99 €, commission plateforme 8 %).
- **Clients existants** — espace client, briefs, maintenance (29 / 59 / 99 €/mois).
- **Associations** — offre de site gratuit, programme Racines.

## Product Purpose

Alliance Groupe est une agence web & IA (site `alliancegroupe-inc.com`) qui vend et livre des
sites professionnels à des TPE/PME et des indépendants, à prix bas et en délai court, en
s'appuyant sur des templates métiers déjà construits. Elle vend aussi ces templates comme
produits numériques, et recrute un réseau de vendeurs terrain rémunérés à la commission.

Succès = un dirigeant qui commande (Sites Express 490 / 890 / 1490 €, sur-mesure, ou maintenance),
ou un vendeur qui s'inscrit et fait sa première vente.

## Positioning

Deux mécanismes que Fabrice a confirmés comme non copiables honnêtement par une agence classique :

1. **Le prix et la vitesse.** Un site livré vite à 490–1490 € là où une agence facture
   3 000–8 000 €, parce que les templates métiers (avocat, barber, restaurant, artisan, coach,
   association, services à domicile) existent déjà, sont maintenus et versionnés.
2. **L'infrastructure maison.** Chasse Google Places avec score de probabilité d'achat, CRM
   partagé anti-doublon avec assignation par zone, agent coach quotidien, updater automatique
   des templates vendus, système de licences, marketplace de composants. C'est de l'outillage
   propriétaire, pas du no-code assemblé.

Le réseau d'ambassadeurs par zone et l'engagement solidaire (Racines, sites gratuits pour assos,
bureaux Nantes / Naples / Marrakech) existent bel et bien, mais n'ont **pas** été retenus comme
l'argument différenciant n°1.

## Operating Context

- **Chemin d'arrivée sur le site : non tranché.** La question a été posée et laissée sans
  réponse. Les chemins possibles présents dans le code (lien ambassadeur `?ref=`, Google Ads +
  GA4, canal Telegram général, bouche-à-oreille) ne doivent pas être traités comme confirmés
  tant que Fabrice n'a pas répondu. À redemander avant toute décision de hiérarchie d'accueil.
- Le site est **auto-géré** : le thème est autonome (pas d'Elementor), déployé par
  Apparence → SYNC GitHub depuis `main` (cron 5 min). Détail : `docs/MECANIQUE-DEPLOIEMENT.md`.
- Les templates vendus sont mis à jour chez l'acheteur par un updater qui compare la version du
  `.json` sur `main` : un changement de code non publié via `scripts/release.sh` n'arrive jamais
  au client.
- Notifications réelles en production : SMS via l'API Free sur la ligne pro, 2 canaux Telegram
  (interne équipe / général clients), emails brandés.
- Trois bureaux affichés : Nantes, Naples, Marrakech.

## Capabilities and Constraints

**Stack :** WordPress, thème maison `alliance-groupe-theme` (v2.0.0), PHP + CSS/JS écrits à la
main, pas de build step ni de framework front. ~72 modules `inc/*.php` chargés par
`functions.php`, ~60 templates de page (`templates/page-*.php`, chacun un « Template Name »),
`template-parts/` pour les fragments. Indentation = tabulations. `php -l` obligatoire avant
commit. Pas d'Elementor, pas de page builder.

**Modules produits en place** (chacun protégé par `function_exists`, donc réutilisable ailleurs) :
espaces membres + auth maison, paiement PayPal/Stripe + commissions, programme ambassadeurs et
recruteurs avec zones, Studio créatif (vidéo/image dans le navigateur), prospection/CRM, moteur
de notifications, audit SEO gratuit, tirage mensuel, kit print, licences de templates, admin hub,
expérience 3D Three.js, marketplace de composants.

**Contraintes dures :**

- **Règle de répercussion** (demande ferme de Fabrice) : une fonctionnalité changée dans le code
  d'un template doit être répercutée sur la page de vente, la liste des templates, le `.json`,
  le zip et le journal. Ne jamais annoncer sur une page de vente ce qui n'est pas dans le code.
- **Vérifier avant d'ajouter** : ne jamais dupliquer un module existant (grep d'abord).
- **Ne jamais toucher l'image du hero de Gwen Services** (`assets/hero.jpg`, theme_mods
  `ag_domicile_hero_custom` / `ag_domicile_hero_image`) : Fabrice la gère lui-même.
- **Déontologie avocats** : contact par email/courrier uniquement, jamais SMS/WhatsApp/appel.
- **Convention UX imposée** : toute liste d'éléments doit offrir tri, ajout, et suppression en
  masse (cases à cocher + tout sélectionner), plus les actions de contact et « Relancer ».
- **Build WordPress.org** : un thème poussé sur .org passe par `scripts/wporg-clean.sh` (retrait
  updater GitHub, licence phone-home, unregister widgets…) et doit finir à 0 ERROR au Theme Check.
- Conformité : Consent Mode RGPD sur GA4/Ads, pages `/retours` et `/livraison` pour Merchant
  Center, AdSense + `ads.txt`.

**Non tranché / à ne pas inventer :** le chemin d'arrivée client (ci-dessus) ; la Phase 2 de la
marketplace (Stripe Connect / PayPal Commerce non configurés — « Acheter » affiche « paiement
bientôt actif ») ; la passerelle SMS Android (code déployé, en pause faute de SIM physique
dédiée) ; le robot vocal IA pour les numéros fixes ; la ligne dédiée au démarchage.

## Brand Commitments

- Nom : **Alliance Groupe**. Domaine : `alliancegroupe-inc.com`. Contact propriétaire :
  advise.alliance.group@gmail.com (Fabrice).
- Identité existante : tête de lion + monogramme « AG » (`assets/images/logo.png`,
  `logo-header.png`, `og-banner.png`, cartes carrées).
- Registre visuel incumbent, constaté dans le code (constat, pas prescription) : fond sombre,
  accent doré `#D4B45C` (`assets/css/main.css`), variante sable `#D4A574` sur la page audit ;
  polices Manrope, Fraunces, Playfair Display, JetBrains Mono.
- Langue : français, tutoiement côté ambassadeurs, vouvoiement côté clients.
- Chaque template métier vendu porte sa propre palette, distincte de celle d'Alliance Groupe.

## Evidence on Hand

**Réel, utilisable :**

- Réalisation client documentée : **Gwen Services** (services à domicile) — page dédiée
  `templates/page-realisation-gwen.php`, photos réelles dans le thème (hero + galerie 7 photos).
- 14 templates métiers réellement construits, versionnés et téléchargeables
  (`assets/downloads/*/` + `.json` + `.zip`) : barber (starter/premium/business), avocat
  (starter/premium/business + recherche), association (starter/fidélité), restaurant, coach,
  artisan, domicile (starter/premium), companion, gwen-services.
- Banque visuelle propre : logo, bannière OG, villes, produits, offres, équipe, modèles 3D
  `.glb` locaux, cartes promo (`assets/images/`, inventaire dans `BANQUE-VISUELS.md`).
- Vidéos verticales à la marque, faites maison (Remotion, `video-remotion/`) : `AG-Recrutement`,
  `AG-Vente-247`, `AG-Luxe`, `AG-Naples-Suite`.
- Avis Google réels branchés via `inc/ag-geo.php` sur la page d'accueil cinéma.

**Absences à ne jamais combler par de l'invention :**

- Pas de portefeuille de réalisations clients au-delà de Gwen Services : ne pas afficher de faux
  logos clients, faux chiffres, faux « +150 sites livrés ».
- Pas de témoignages clients rédigés et validés en stock. Les témoignages visibles dans les
  templates de démo sont du **contenu d'exemple**, pas des clients réels.
- Le classement public contient encore des profils de démonstration
  (`ag_demo_leaderboard_on`) : à couper dès qu'il y a assez de vrais inscrits, et à ne jamais
  présenter comme des résultats réels.
- Captures d'écran des 6 templates et images du système de prospection : attendues de Fabrice,
  pas encore fournies. Ne pas fabriquer de fausses captures.

## Product Principles

1. **Le dirigeant pressé arbitre.** Prix, délai et preuve de sérieux doivent être atteignables
   en quelques secondes, sur mobile, sans scroll d'exploration.
2. **Prix et vitesse sont l'argument.** Toute page qui vend doit rendre l'écart de prix et de
   délai lisible immédiatement, sans le noyer dans du vocabulaire d'agence.
3. **Rien d'annoncé qui n'existe pas.** Une promesse sur une page de vente doit être vérifiable
   dans le code livré — c'est la règle de répercussion, appliquée au design comme au reste.
4. **L'infrastructure est une preuve, pas un décor.** Montrer l'outillage réel (CRM, updater,
   audit, licences) vaut mieux que des superlatifs sur l'IA.
5. **Un module existe déjà : on l'étend, on ne le double pas.** Vérifier avant d'ajouter, sur
   le design comme sur le code.

## Accessibility & Inclusion

Pas d'exigence normative (RGAA/WCAG niveau X) confirmée par le propriétaire à ce stade.
Constaté dans le code : `prefers-reduced-motion` est déjà respecté dans `main.css`,
`ag-cinema.css`, `ag-immersive.css` et plusieurs templates — sauf `page-experience.php`
(parcours 3D), qui n'a pas de garde. Le public prioritaire consulte majoritairement sur mobile,
souvent en extérieur : contraste et taille de cible sont des contraintes d'usage réelles, même
sans norme imposée.
