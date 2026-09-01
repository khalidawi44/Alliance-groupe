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

## Journal maître

- 2026-08-27 · CODE · Création du protocole et d'un `COWORK.md` dans chacun des 16 templates. Inventaire mesuré (versions, images, PHP). Diagnostic : famine d'images sur tout le parc hors Gwen.
- 2026-08-27 · CODE · Doctrine tranchée : site illustré = gratuit, module galerie = Business (inchangé), technique = Premium. Alerte levée après vérification du code.
- 2026-08-27 · CODE · Premium barber : module Statistiques construit (trafic sans cookie ni IP + historique des tickets). Descriptions plugin et .json mises en cohérence.
