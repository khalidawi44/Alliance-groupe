# COWORK — ag-starter-avocat

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

- Version publiée : **1.1.17**
- Famille : **GRATUIT**
- Métier : Cabinet d'avocat
- Fichiers PHP : **26**
- **Images présentes : 1**

**Note :** Déontologie : pas de promesse de résultat, pas de témoignage client.

## Doctrine des offres (décision de Fabrice)

- **GRATUIT** = un site **beau et plein**. Photos, galerie, contenu réel. Il doit
  donner envie tel quel. C'est l'aimant à prospects — s'il est vide ou moche,
  il ne sert à rien.
- **PAYANT** = ce que le client **ne sait pas faire lui-même** : SEO technique,
  statistiques, schema.org, performance, sécurité. C'est ça qui justifie le prix,
  pas un habillage graphique en plus.

## Lane DESIGN — à faire par la session Cowork

**Photos attendues :** Bureau, bibliothèque juridique, poignée de main, salle de réunion. Sobre, institutionnel, jamais de mise en scène de tribunal. Aucun visage de client identifiable.

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
bash scripts/release.sh ag-starter-avocat <nouvelle-version>   # bump + json + zip + push + merge main
bash scripts/check-releases.sh                     # vérifie la cohérence
```

Avant tout push sur `main` : `git fetch origin main && git rebase origin/main`
(le robot d'images Gwen commite directement sur `main` sans prévenir).

Chaîne complète expliquée dans `docs/MECANIQUE-DEPLOIEMENT.md`.

## Journal partagé

Format : `YYYY-MM-DD · LANE · ce qui a été fait`

- 2026-08-27 · CODE · Création de ce fichier de liaison. Inventaire mesuré : 1 image(s), 26 fichier(s) PHP, version 1.1.17.
- 2026-09-24 · CODE · Libellés **« Domaines d'intervention » partout** (complément, autorisation écrite de Fabrice). **Publié en 1.1.23** (a) + (b). `ag_avocat_opt()` prenant son 2ᵉ argument comme repli, les replis en dur restaient « Domaines d'expertise » / « Specialites » sur une install neuve → corrigés dans `front-page.php`, `page-cabinet.php`, `single-ag_domaine.php`, `inc/guide.php`, `inc/cpt-domaine.php`. Build `.org` : `fr_FR.po` aligné (plus aucun libellé « expertise » traduit ; seul le slug de page `expertise` subsiste), `.mo` recompilé (291 chaînes, 100 %). ⚠️ La version passe à **1.1.23** (et non 1.1.22 comme demandé) car 1.1.22 était **déjà publiée** plus tôt dans la session : un nouveau numéro est nécessaire pour que l'updater livre le correctif aux clients.
- 2026-09-24 · CODE · Conformité RIN **au format WordPress.org** (v2, autorisation écrite de Fabrice). **Publié en 1.1.22** pour les DEUX formats : version distribuée `assets/downloads/ag-starter-avocat` (1.1.22) ET build `.org` `wporg-builds/ag-starter-avocat` (1.1.22, code source EN + `fr_FR.po/.mo` recompilés à 100 % — 291 chaînes, 0 fuzzy, couverture `.po`↔`.pot` vérifiée `msgcmp`, 0 phone-home). Crédit pied de page réduit à **un seul lien `rel="nofollow"`** (retrait du 2ᵉ lien « Agence web à Nantes ») dans les deux formats. Reste identique à l'entrée précédente (compteurs vides, libellés « Domaines d'intervention », JSON-LD, CSS mobile). `.org` **non soumis** : Theme Check à lancer par Fabrice. NB : la passation DESIGN du 24/09 n'est toujours **pas** dans le dépôt (locale Cowork).
- 2026-09-24 · CODE · Conformité RIN art. 10 (autorisation écrite de Fabrice, `unlock-free`). **Publié en 1.1.20.** `render_counters()` : plus de chiffres inventés (15+/500+/98 %/24-7) — compteurs lus depuis le Customizer (section « Chiffres clés »), vides par défaut, section non rendue si vide. `footer.php` : retrait du JSON-LD `Organization` « franco-italo-marocaine » (le schema `Attorney` de `render_schema_org()` suffit) ; libellé barreau affiche uniquement la valeur (fin du « Avocat inscrit au Avocate au Barreau de… ») ; accents « Tous droits réservés » / « Site réalisé par ». Libellés « Domaines d'expertise » → « Domaines d'intervention » (`customizer.php`, `functions.php`, `page-expertise.php`) ; « Spécialités : » → « Domaines d'intervention : ». Pub (RIN 10.5) : grande promo du pied de page gratuit remplacée par le crédit discret du Business (un seul lien `nofollow`) ; « Agence Web Nantes & Marrakech » → « Agence web à Nantes » (0 occurrence de la ville retirée). CSS `.ag-deonto-item` : plus de débordement < 480 px. Template retiré de `.AG_FOCUS` → de nouveau **contrôlé** par check-all-locks. NB : la passation DESIGN du 24/09 annoncée pour ce fichier n'était **pas** dans le dépôt (restée en local Cowork) — seule cette ligne CODE a pu être ajoutée.
