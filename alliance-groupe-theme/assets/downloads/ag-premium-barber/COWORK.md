# COWORK — ag-premium-barber

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

- Version publiée : **0.5.3**
- Famille : **PAYANT**
- Métier : Add-on Premium Barber
- Fichiers PHP : **3**
- **Images présentes : 0**

> ⚠️ **Zéro image.** C'est le problème n°1 : un template sans visuel ne se vend pas.

**Note :** S'active APRÈS ag-starter-barber.

## Doctrine des offres (décision de Fabrice)

- **GRATUIT** = un site **beau et plein**. Photos, galerie, contenu réel. Il doit
  donner envie tel quel. C'est l'aimant à prospects — s'il est vide ou moche,
  il ne sert à rien.
- **PAYANT** = ce que le client **ne sait pas faire lui-même** : SEO technique,
  statistiques, schema.org, performance, sécurité. C'est ça qui justifie le prix,
  pas un habillage graphique en plus.

## Lane DESIGN — à faire par la session Cowork

**Photos attendues :** Pas de photos à produire. La valeur est technique.

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
bash scripts/release.sh ag-premium-barber <nouvelle-version>   # bump + json + zip + push + merge main
bash scripts/check-releases.sh                     # vérifie la cohérence
```

Avant tout push sur `main` : `git fetch origin main && git rebase origin/main`
(le robot d'images Gwen commite directement sur `main` sans prévenir).

Chaîne complète expliquée dans `docs/MECANIQUE-DEPLOIEMENT.md`.

## Ce que porte le Premium (construit le 27/08)

Avant : uniquement du CSS et du JS d'habillage. **Zéro SEO, zéro statistiques.**
Le Premium ne vendait que de la décoration — ce qu'un client peut bricoler seul.

Maintenant : **`inc/class-ag-pb-stats.php`**, l'outil de suivi du salon.

Ce que le patron voit dans son admin (menu **Statistiques**) :
- ses **visiteurs** et ses **pages vues**, sur 7 / 30 / 90 jours, avec un graphique
- **d'où viennent les gens** : Google, Instagram, Maps, direct…
- ses **pages les plus consultées**
- ses **tickets pris**, ses **heures de pointe**, ses **prestations les plus demandées**

Choix techniques qui font la différence commerciale :
- **Aucun service externe.** Pas de Google Analytics, pas de script tiers. Les
  chiffres sont calculés sur son propre hébergement, ils lui appartiennent.
- **Aucun cookie, aucune IP conservée.** Un visiteur unique est reconnu par un
  hash salé (IP + navigateur + sel qui tourne chaque jour), gardé 24 h en
  transient. Rien n'est reconstituable → **pas de bandeau cookies pour ce module**.
  C'est un argument de vente : conformité RGPD sans effort pour le client.
- **Historique des tickets sans toucher au thème gratuit.** La file d'attente ne
  garde que les gens en attente. On écoute `update_option_ag_barber_queue` pour
  archiver chaque ticket au moment où il apparaît — aucune modification du
  gratuit, donc aucun risque de casser ce qui est déjà installé.

Reste à construire côté CODE :
1. SEO local — schema.org LocalBusiness, meta OG/Twitter, canonical, sitemap
2. Performance — lazy-loading, préchargement des polices, Core Web Vitals

## Journal partagé

Format : `YYYY-MM-DD · LANE · ce qui a été fait`

- 2026-08-27 · CODE · Création de ce fichier de liaison. Inventaire mesuré : 0 image(s), 3 fichier(s) PHP, version 0.5.3.
- 2026-08-27 · CODE · Module Statistiques construit et branché (class-ag-pb-stats.php). Descriptions plugin + .json mises en cohérence : le Premium porte désormais la technique, pas l'habillage.
