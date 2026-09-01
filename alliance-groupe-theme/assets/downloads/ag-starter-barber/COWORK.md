# COWORK — ag-starter-barber

> **Fichier de liaison entre les deux sessions Claude.**
> Ne pas supprimer. Chaque session lit ce fichier avant de toucher au template,
> et écrit ce qu'elle a fait dans le journal en bas.

## Qui fait quoi

| Session | Accès | Domaine |
|---|---|---|
| **DESIGN** (Cowork, sur le PC de Fabrice) | Fichiers locaux + Chrome + outils images | Visuels, photos, mise en page, rendu, captures |
| **CODE** (Claude Code web, dépôt) | Dépôt seul | PHP/CSS/JS, SEO technique, statistiques, sécurité, performance, analyse |

**Règle simple :** ce qui se voit → DESIGN. Ce qui se calcule → CODE.
En cas de chevauchement, celui qui commence écrit une ligne dans le journal AVANT de modifier.

## État mesuré de ce template

- Version publiée : **1.0.11**
- Famille : **GRATUIT**
- Métier : Barbier / coiffeur homme
- Fichiers PHP : **14**
- **Images présentes : 0**

> ⚠️ **Zéro image.** C'est le problème n°1 : un template sans visuel ne se vend pas.

**Note :** BUG CSS CONFIRMÉ à corriger, voir section Code.

## Doctrine des offres (décision de Fabrice)

- **GRATUIT** = un site **beau et plein**. Photos, galerie, contenu réel. Il doit
  donner envie tel quel. C'est l'aimant à prospects — s'il est vide ou moche,
  il ne sert à rien.
- **PAYANT** = ce que le client **ne sait pas faire lui-même** : SEO technique,
  statistiques, schema.org, performance, sécurité. C'est ça qui justifie le prix,
  pas un habillage graphique en plus.

## Lane DESIGN — à faire par la session Cowork

**Photos attendues :** Coupes finies (dégradé, taper fade), barbe taillée, fauteuil et intérieur du salon, mains au travail. Format portrait 4:5. Ambiance sombre, lumière chaude — la charte est or #D4B45C sur nuit #0a0a0f.

Règles non négociables :
1. **Droits** — uniquement licence libre commerciale (Unsplash, Pexels) ou photos
   de Fabrice. Jamais de photo prise sur le site d'un concurrent ou d'un salon.
2. **Personnes reconnaissables** — autorisation à l'image écrite, sinon on évite.
3. **Poids** — largeur max 1200 px, qualité ~82, .webp de préférence, .jpg sinon.
   Une page qui met 4 s à charger fait fuir avant d'avoir convaincu.
4. **Charte** — réutiliser les variables CSS déjà définies dans `style.css`
   (`--gold`, `--dark`, `--card`, `--text-muted`). Ne pas inventer de nouvelles
   couleurs ni un nouveau système de classes : tout existe.
5. **Éditable par le client** — brancher chaque image sur le Customizer
   (voir `inc/customizer.php` pour le motif déjà en place).

## Lane CODE — à faire par la session Claude Code

- SEO technique : schema.org LocalBusiness, meta OG/Twitter, canonical, sitemap.
- Statistiques exploitables par le patron (données réelles du thème, pas de fantaisie).
- Performance : lazy-loading, préchargement des polices, pas de blocage du rendu.
- Accessibilité : contrastes, focus visible, libellés.
- Analyse et correction des bugs, revue de sécurité.

## Règle de déploiement — VITALE

Modifier les fichiers **ne suffit pas**. L'updater installé chez le client lit la
**version dans le `.json`** et télécharge le **`.zip`**. Sans rebuild, l'acheteur
ne reçoit **jamais** la mise à jour, en silence.

```bash
bash scripts/release.sh ag-starter-barber <nouvelle-version>   # bump + json + zip + push + merge main
bash scripts/check-releases.sh                     # vérifie la cohérence
```

Avant tout push sur `main` : `git fetch origin main && git rebase origin/main`
(le robot d'images Gwen commite directement sur `main` sans prévenir).

Chaîne complète expliquée dans `docs/MECANIQUE-DEPLOIEMENT.md`.

## Bug corrigé côté CODE — bouton d'en-tête illisible

**Symptôme** — Le bouton « 📱 Prendre un ticket » de l'en-tête affichait son
libellé en gris pâle sur fond or : illisible. Constaté par Fabrice sur mobile en
clientèle, le 27/08.

**Cause** — Conflit de spécificité dans `style.css` :

```css
.ag-header__nav a { color: var(--text-muted); }   /* (0,1,1) — gagne */
.ag-header__cta   { color: var(--dark); }         /* (0,1,0) — perd  */
```

Le bouton est placé **à l'intérieur** de `<nav class="ag-header__nav">`
(cf. `header.php`), donc la règle du nav l'emporte quel que soit l'ordre
d'écriture. Le défaut touche **tous les sites barber déjà livrés**.

**Correctif appliqué** — on regagne la spécificité plutôt que d'ajouter un
`!important` qui aurait durci la feuille pour la suite :

```css
.ag-header__nav a.ag-header__cta { color: var(--dark); }
.ag-header__nav a.ag-header__cta:hover { color: var(--dark); }
```

**⚠️ RESTE À FAIRE — la correction n'atteint aucun client tant que ceci n'est pas
lancé :**

```bash
bash scripts/release.sh ag-starter-barber 1.0.12
```

Je n'ai pas pu le lancer moi-même : mon accès GitHub en écriture est coupé
(HTTP 403 sur tout push). La session qui retrouve l'accès doit le faire.

**À vérifier sur les autres templates** — le même motif
(`.xxx__nav a` qui écrase un `.xxx__cta` imbriqué) est probablement présent
ailleurs. Contrôle rapide : chercher `__cta` dans chaque `style.css` et vérifier
si le bouton est enfant du `nav`.

## Journal partagé

Format : `YYYY-MM-DD · LANE · ce qui a été fait`

- 2026-08-27 · CODE · Création de ce fichier de liaison. Inventaire mesuré : 0 image(s), 14 fichier(s) PHP, version 1.0.11.
- 2026-08-27 · CODE · Corrigé le conflit de spécificité qui rendait le bouton d'en-tête illisible (gris sur or). Release 1.0.12 encore à publier — push bloqué en 403.
