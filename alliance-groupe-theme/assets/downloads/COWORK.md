# COWORK — Protocole entre les deux sessions Claude

> **Fichier maître.** Chaque template a en plus son propre `COWORK.md` avec son
> état mesuré et son brief photo. Lire celui-ci d'abord, puis celui du template.

## Les deux sessions

| Session | Accès | Domaine |
|---|---|---|
| **DESIGN** — Cowork, sur le PC de Fabrice | Fichiers locaux, Chrome, outils images | Visuels, photos, mise en page, rendu, captures d'écran |
| **CODE** — Claude Code web | Dépôt seul | PHP/CSS/JS, SEO technique, statistiques, sécurité, performance, analyse |

**Règle simple :** ce qui se voit → DESIGN. Ce qui se calcule → CODE.

**Anti-collision :** avant de modifier un fichier, écrire une ligne dans le
journal du `COWORK.md` du template concerné. Celui qui écrit en premier a la main
sur ce fichier jusqu'à ce qu'il note qu'il a terminé. Ça évite qu'on se marche
dessus sur `main`, où le robot d'images Gwen commite aussi de son côté.

---

## La doctrine des offres (décision de Fabrice, 27/08)

**GRATUIT = un site beau et plein.**
Photos, galerie, contenu réel. Il doit donner envie tel quel, sans rien acheter.
C'est l'aimant à prospects : un template vide ou moche ne se vend pas et ne fait
pas vendre. C'est le constat que Fabrice a fait sur le terrain, en salon.

**PAYANT = ce que le client ne sait pas faire lui-même.**
SEO technique, statistiques, schema.org, performance, sécurité, conformité.
Du travail que personne sans compétence informatique ne peut produire — c'est ça
qui justifie le prix, pas un habillage graphique supplémentaire.

### Tranché le 27/08 — il n'y a pas de conflit

J'avais alerté sur une contradiction avec les `.json`. Vérification faite dans le
code : **c'en était pas une.** Deux choses différentes étaient confondues.

| | Où ça vit |
|---|---|
| **Site illustré** — des photos dans les sections qui existent déjà (hero, prestations, ambiance). Le site est beau et plein dès le gratuit. | **GRATUIT** |
| **Module galerie** — grille éditable + équipe + témoignages, que le client pilote lui-même depuis le Customizer. Réellement codé, 1 169 lignes. | **BUSINESS** (inchangé) |

Un barbier voit un site magnifique en gratuit. S'il veut *sa* galerie qu'il gère,
il monte en gamme. **Personne ne perd ce qu'il a payé.**

Constat au passage : le **Premium** ne contenait que du CSS et du JS d'habillage
(1 177 lignes), **zéro SEO, zéro statistiques**. C'est ce qui est en train d'être
corrigé côté CODE — c'est là qu'est la vraie valeur payante.

---

## RÈGLE DE RÉPERCUSSION (demande ferme de Fabrice, 27/08)

**Dès qu'on change quelque chose, on vérifie si ça doit être changé ailleurs.**
Un produit modifié sans que ce qui le décrit soit mis à jour, c'est une promesse
fausse faite au client.

Checklist à dérouler à chaque changement fonctionnel d'un template :

1. `<slug>/` — le code, `style.css` (en-tête Description), l'en-tête du plugin
2. `<slug>.json` — **c'est CE fichier que lit l'updater du client**
3. `templates/page-wordpress-<metier>.php` — la page de vente :
   `free_features`, `premium_features`, `business_features`, `palette`,
   `hero_subtitle`, `description_long`
4. `templates/page-templates.php` — la liste des templates (palette, tagline)
5. Le `COWORK.md` du template + ce fichier maître
6. `bash scripts/release.sh <slug> <version>` puis `bash scripts/check-releases.sh`

**Ne jamais annoncer une fonctionnalité qui n'est pas dans le code.** Vérifier
par `grep` avant d'écrire une ligne sur la page de vente.

### Ce que ce contrôle a révélé le 27/08

La page de vente barber promettait, dans le Premium et le Business :
notifications SMS, multi-barbers, écran TV en salon, API publique, multi-salons,
chiffre d'affaires quotidien. **Aucune de ces fonctionnalités n'existe dans le
code.** Le Business ne contient que équipe, galerie, témoignages et horaires.

Ces promesses ont été retirées de la page. Elles sont à considérer comme un
backlog : soit on les construit, soit on ne les vend pas.

---

## État mesuré du parc (27/08/2026)

| Template | Version | Images | PHP | Famille |
|---|---:|---:|---:|---|
| ag-starter-barber | 1.0.11 | **0** | 14 | Gratuit |
| ag-starter-avocat | 1.1.17 | 1 | 26 | Gratuit |
| ag-starter-restaurant | 1.24.3 | 1 | 34 | Gratuit |
| ag-starter-artisan | 1.15.6 | 1 | 29 | Gratuit |
| ag-starter-coach | 1.3.6 | 1 | 29 | Gratuit |
| ag-starter-association | 0.56.2 | **0** | 16 | Gratuit |
| ag-starter-domicile | 1.0.4 | 2 | 29 | Gratuit |
| ag-starter-companion | 1.13.2 | 1 | 1 | Gratuit |
| ag-gwen-services | 1.2.6 | 11 | 30 | Site client (production) |
| ag-premium-barber | 0.5.3 | 0 | 3 | Payant |
| ag-premium-avocat | 0.6.0 | 0 | 3 | Payant |
| ag-premium-domicile | 1.0.2 | 0 | 3 | Payant |
| ag-business-barber | 0.5.2 | 0 | 3 | Payant |
| ag-business-avocat | 0.55.0 | 0 | 4 | Payant |
| ag-fidelite-association | 0.50.1 | 0 | 10 | Payant |
| ag-avocat-recherche | 1.6.2 | 4 | 4 | Outil interne |

**Le diagnostic tient en une ligne :** hors Gwen, aucun template vitrine n'a plus
d'une image. Les gratuits doivent être visuellement complets — c'est la priorité
absolue de la lane DESIGN.

---

## Priorités

**DESIGN — dans l'ordre**
1. `ag-starter-barber` (0 image, c'est celui que Fabrice vend en ce moment en salon)
2. `ag-starter-association` (0 image)
3. Les autres gratuits : compléter au-delà de l'image unique
4. Captures d'écran des 6 templates pour la page de vente du site principal
   (avocat, restaurant, artisan, coach, barber, association) — demande en attente
   de longue date côté Fabrice

**CODE — dans l'ordre**
1. Bug CSS du bouton d'en-tête barber (détaillé dans `ag-starter-barber/COWORK.md`)
2. Couche SEO technique des versions payantes : schema.org, meta, sitemap
3. Tableau de bord statistiques à partir des données réelles du thème
4. Performance et accessibilité sur les gratuits

---

## Règles communes

**Images**
- Licence libre commerciale (Unsplash, Pexels) ou photos de Fabrice. Jamais une
  photo prise sur le site d'un concurrent ou d'un salon.
- Personne reconnaissable → autorisation à l'image écrite, sinon on évite.
- Largeur max 1200 px, qualité ~82, `.webp` de préférence.
- Toujours branchée sur le Customizer pour que le client puisse la remplacer.

**Charte**
Réutiliser les variables CSS de chaque thème (`--gold`, `--dark`, `--card`,
`--text-muted`…). Ne pas inventer de couleurs ni de nouveau système de classes :
tout existe déjà dans les `style.css`.

**Déploiement — vital**
Modifier les fichiers ne suffit pas. L'updater du client lit la **version du
`.json`** et télécharge le **`.zip`**. Sans rebuild, l'acheteur ne reçoit jamais
la mise à jour, en silence.

```bash
bash scripts/release.sh <slug> <version>   # bump + json + zip + push + merge main
bash scripts/check-releases.sh             # vérifie la cohérence
```

Avant tout push sur `main` : `git fetch origin main && git rebase origin/main`.
Chaîne complète : `docs/MECANIQUE-DEPLOIEMENT.md`.

**Deux interdits permanents**
- Hero de Gwen (`ag-gwen-services`, `ag-starter-domicile`, `ag-premium-domicile`) :
  ne jamais changer/toucher/remplacer l'image de fond du hero. Fabrice la gère
  lui-même via Apparence → Personnaliser.
- `ag-gwen-services` est **en production chez une vraie cliente**. Toute
  modification part en ligne. Prudence maximale.

---

## FRONTIÈRE — rappelée le 03/09 après un accrochage

La session CODE a retouché du visuel sur l'accueil (taille du lion, taille du
titre, style du champ, position de la section). **C'était hors de son
périmètre**, ça a mis Fabrice en conflit entre les deux sessions, et **ça a été
annulé** (revert du commit `5ac7af0`).

La règle ne bouge pas : **ce qui se voit → DESIGN. Ce qui se calcule → CODE.**
Elle est désormais aussi dans `CLAUDE.md`, lu à chaque démarrage de session.

### Ce que la lane CODE a observé, et qu'elle laisse à la lane DESIGN

Sur l'accueil (`templates/page-accueil-cinema.php`), section `.lion`
« Vois ton site refait en 60 secondes », constaté sur une capture iPhone :

1. **Le champ où l'on colle son adresse est invisible.** `background` à 5 %
   d'opacité et bordure à 32 %, posé sur la photo du lion : il n'y a aucun
   cadre perceptible, le texte d'exemple flotte sur la crinière. Le visiteur ne
   peut pas deviner qu'on écrit là. **C'est le défaut le plus coûteux de la
   page** — l'outil ne peut pas convertir si personne ne voit le champ.
2. **Le lion déborde.** Demande de Fabrice : qu'il tienne entier à l'écran.
3. **Titre et sous-titre trop hauts** : ils écrasent l'animation. Fabrice veut
   les réduire.
4. **La section est trop bas dans la page.** Fabrice la veut plus haut, c'est
   une promesse qui se tient en dix secondes. ⚠️ Attention en la remontant :
   elle mesure 210svh (150 sur mobile) et repousserait d'autant les offres, qui
   sont déjà loin. La raccourcir en même temps.

### Un point technique, à faire par la lane CODE sur demande

`page-accueil-cinema.php`, animation du lion : la plage `ScrollTrigger` va de
`start:"top top"` à `end:"center bottom"`. **Cette plage est inversée** — la fin
tombe avant le début, donc la révélation se joue d'un bloc au lieu de suivre le
défilement. Ce n'est pas du goût, c'est un défaut de calcul. **La lane CODE le
corrige dès que la lane DESIGN le demande**, et pas avant, pour ne pas éditer le
fichier pendant qu'ils travaillent dessus.

Le champ manque aussi d'un `<label>` (le texte d'exemple ne tient pas lieu
d'étiquette pour un lecteur d'écran). Même règle : sur demande.

---

## Journal maître

- 2026-08-27 · CODE · Création du protocole et d'un `COWORK.md` dans chacun des 16 templates. Inventaire mesuré (versions, images, PHP). Diagnostic : famine d'images sur tout le parc hors Gwen.
- 2026-08-27 · CODE · Doctrine tranchée : site illustré = gratuit, module galerie = Business (inchangé), technique = Premium. Alerte levée après vérification du code.
- 2026-08-27 · CODE · Premium barber : module Statistiques construit (trafic sans cookie ni IP + historique des tickets). Descriptions plugin et .json mises en cohérence.
- 2026-08-27 · CODE · Design Premium transféré dans le thème gratuit (starter 1.0.13, premium 0.7.0). Statistiques verrouillées sur la licence — elles tournaient gratuitement, c'était une faute de ma part.
- 2026-08-27 · CODE · Règle de répercussion appliquée : page de vente barber réécrite, palette corrigée (or → bleu) dans la page métier ET la liste des templates. Promesses non tenues retirées (SMS, multi-barbers, écran TV, API, multi-salons, CA quotidien) — elles n'existaient pas dans le code.
- 2026-09-03 · CODE · Frontière franchie puis réparée : retouche visuelle de l'accueil annulée (revert `5ac7af0`). Constats passés à la lane DESIGN ci-dessus. La règle de périmètre monte dans `CLAUDE.md`.
