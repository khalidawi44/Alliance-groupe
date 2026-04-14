# Guide de soumission à WordPress.org — AG Starter

Ce guide explique comment soumettre **les 4 thèmes AG Starter + le plugin compagnon AG Starter Companion** aux deux répertoires officiels WordPress.org (themes + plugins). **C'est une démarche manuelle que tu dois faire depuis ton ordinateur** (je ne peux pas la faire à ta place — elle nécessite un compte wordpress.org et l'upload via leur interface).

## Résumé : 5 soumissions au total

1. **Thème** `ag-starter-restaurant` → https://wordpress.org/themes/upload/
2. **Thème** `ag-starter-artisan` → https://wordpress.org/themes/upload/
3. **Thème** `ag-starter-coach` → https://wordpress.org/themes/upload/
4. **Thème** `ag-starter-avocat` → https://wordpress.org/themes/upload/ ⚠️ inclut un CPT `ag_domaine` — vérifier la review
5. **Plugin** `ag-starter-companion` → https://wordpress.org/plugins/developers/add/

Les thèmes passent par l'équipe Theme Review (délai 2–8 semaines). Le plugin passe par l'équipe Plugin Review (délai 1–4 semaines, plus rapide en général). Ce sont deux équipes distinctes avec des règles différentes.

## Pourquoi un plugin compagnon séparé ?

Les règles WordPress.org **interdisent aux thèmes** de créer des pages, menus ou contenus automatiquement — c'est considéré comme "du domaine des plugins". C'est pour ça qu'Astra, OceanWP, Kadence et les autres thèmes gratuits populaires utilisent un **plugin compagnon** pour leurs importers de contenu démo. `AG Starter Companion` applique la même architecture :

- Il détecte automatiquement quel thème AG Starter est actif (Restaurant, Artisan, Coach ou Avocat)
- Il adapte le contenu importé en fonction du thème détecté
- Si aucun thème AG Starter n'est actif, il reste dormant
- Chaque thème affiche une admin notice invitant l'utilisateur à installer le plugin

## Prérequis

### 1. Créer un compte wordpress.org

- Va sur https://login.wordpress.org/register
- **Nom d'utilisateur (login)** : `adminag` — c'est ce qui est déclaré dans les `readme.txt` sous `Contributors:` et ce qui te permet de te connecter à wordpress.org
- **Nom affiché / pseudo forum** : `AGthèmes` — c'est ce qui apparaît publiquement sur la page de tes thèmes (déclaré dans `style.css` sous `Author:`)
- **Email** : ton email professionnel

**Note importante** : le champ `Contributors:` dans `readme.txt` utilise obligatoirement le nom d'utilisateur (lowercase, sans espaces ni accents) — c'est `adminag`. Le champ `Author:` dans `style.css` est le nom d'affichage libre — c'est `AGthèmes`. Les deux sont déjà correctement configurés dans les 4 thèmes.

### 2. Installer le plugin Theme Check

Dans ton WordPress local (ou un site de test) :
- `Extensions > Ajouter > Rechercher "Theme Check"` (par Otto42, Pross)
- Installer et activer
- Puis dans `Apparence > Theme Check` : sélectionne le thème à tester et lance l'analyse

Le plugin doit retourner **zéro ERROR / WARNING bloquant**. Si c'est le cas, le thème passera probablement la review automatique.

### 3. Installer un site WordPress local (pour tester)

- LocalWP (https://localwp.com) ou XAMPP
- Importer chaque thème (`Apparence > Thèmes > Ajouter > Téléverser`)
- Tester : page d'accueil, menu, widgets, commentaires, 404, recherche
- Vérifier qu'aucun fichier PHP ne génère d'erreur (activer `WP_DEBUG` dans `wp-config.php`)

## Ce qui est déjà prêt dans chaque thème

Les 4 thèmes AG Starter (restaurant, artisan, coach, avocat) sont déjà conformes aux exigences WordPress.org :

### ✅ Conformité technique

| Exigence | Statut |
|---|---|
| Licence GPL v2 ou ultérieure | ✓ Déclarée dans `style.css` et `readme.txt` |
| Text Domain cohérent | ✓ Ex. `ag-starter-restaurant` partout |
| Toutes les strings internationalisées | ✓ `__()`, `esc_html__()`, `esc_attr__()` |
| Escaping strict | ✓ `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` |
| Support `wp_head()` / `wp_footer()` | ✓ Dans `header.php` / `footer.php` |
| Support `wp_body_open()` | ✓ Dans `header.php` |
| Support `body_class()` / `post_class()` | ✓ |
| `load_theme_textdomain()` | ✓ Dans `functions.php` |
| `add_theme_support()` requis | ✓ title-tag, post-thumbnails, html5, automatic-feed-links, responsive-embeds, align-wide, custom-logo, custom-background |
| `register_nav_menus()` | ✓ Menu principal |
| `register_sidebar()` | ✓ Barre latérale |
| Support comment-reply | ✓ Conditionnel dans `wp_enqueue_scripts` |
| Pingback header | ✓ Via action `wp_head` |
| Tags WordPress.org valides | ✓ Seulement des tags de la [liste officielle](https://make.wordpress.org/themes/handbook/review/required/theme-tags/) |

### ✅ Fichiers templates

Chaque thème inclut tous les templates minimum + recommandés :

- `index.php` — template principal avec boucle
- `front-page.php` — page d'accueil statique
- `header.php` — en-tête avec menu
- `footer.php` — pied de page avec adresse/horaires/contact
- `sidebar.php` — zone de widgets
- `page.php` — pages standard
- `single.php` — articles
- `search.php` — résultats de recherche
- `searchform.php` — formulaire de recherche
- `404.php` — page introuvable
- `comments.php` — commentaires threadés
- `functions.php` — setup, enqueue, menus, widgets
- `style.css` — header complet + CSS
- `readme.txt` — format wordpress.org
- `screenshot.png` — 1200×900 PNG

## Ce que tu dois faire AVANT la soumission

### ⚠️ Remplacer les screenshots placeholder

Les `screenshot.png` actuels sont des visuels génériques générés automatiquement. **Ils fonctionnent mais ne vendent pas bien le thème.** Pour maximiser tes chances d'être approuvé rapidement et d'attirer des téléchargements, remplace-les par de vrais screenshots :

1. Installe chaque thème dans un WordPress local
2. Prends une capture d'écran de la page d'accueil (viewport desktop, `1200×900` exactement)
3. Optimise l'image avec TinyPNG (https://tinypng.com)
4. Remplace `assets/downloads/ag-starter-[nom]/screenshot.png`
5. Rebuild le ZIP (voir plus bas)

### ⚠️ Choix du slug final sur wordpress.org

Les slugs doivent être uniques dans le répertoire. Vérifie la disponibilité :

- https://wordpress.org/themes/ag-starter-restaurant/
- https://wordpress.org/themes/ag-starter-artisan/
- https://wordpress.org/themes/ag-starter-coach/

Si l'un est pris, tu devras renommer : `alliance-starter-restaurant`, `ag-french-restaurant`, etc. **Important** : si tu changes le slug, tu dois aussi changer le Text Domain partout dans le thème (c'est un search & replace global sur le dossier).

### ⚠️ Rebuild des ZIPs

Si tu modifies un fichier, rebuild le ZIP concerné :

```bash
cd alliance-groupe-theme/assets/downloads
rm ag-starter-restaurant.zip
zip -r ag-starter-restaurant.zip ag-starter-restaurant -x "*.DS_Store"
```

## Processus de soumission

### Étape 1 — Lance Theme Check sur chaque thème

Dans ton WordPress local, active le thème et va dans `Apparence > Theme Check`. Corrige toutes les erreurs (warnings acceptables). Si tu vois des INFO, ce n'est pas bloquant.

### Étape 2 — Upload sur wordpress.org

1. Va sur https://wordpress.org/themes/upload/
2. Connecte-toi avec ton compte `adminag`
3. Clique `Select File` et choisis `ag-starter-restaurant.zip`
4. Clique `Upload`
5. Si l'upload automatique réussit, tu reçois un numéro de ticket par email
6. Répète pour `ag-starter-artisan.zip` puis `ag-starter-coach.zip`

### Étape 3 — Attendre la review

- **Délai** : 2 à 8 semaines (parfois plus selon la charge de l'équipe)
- Tu recevras des emails automatiques t'informant de l'avancement
- Si un reviewer demande des corrections, il te laisse un ticket Trac avec les problèmes à corriger
- Tu corriges, tu rebuilds le ZIP, tu replies au ticket avec le nouveau fichier

### Étape 4 — Publication

Une fois approuvé :
- Le thème apparaît sur `https://wordpress.org/themes/[slug]/`
- Les utilisateurs WordPress pourront l'installer directement depuis `Apparence > Thèmes > Ajouter > Rechercher`
- Tes statistiques (téléchargements, installations actives, notes) sont visibles depuis ton profil wordpress.org

## Arguments marketing à mettre en avant

Dans la description wordpress.org (déjà présente dans les `readme.txt`), insiste sur :

1. **100% français natif** — textes rédigés en français, pas de Lorem ipsum, pas de strings anglaises. C'est rare dans le répertoire wordpress.org, et c'est ton angle différenciant.

2. **95% prêt à l'emploi** — contrairement aux starter themes classiques qui demandent des heures de configuration, AG Starter a déjà tout en place : hero, cartes, footer, informations de contact. L'utilisateur remplace juste le nom et les photos.

3. **Zéro plugin requis** — beaucoup de thèmes gratuits sur wordpress.org dépendent d'Elementor, Beaver Builder ou d'autres plugins payants. AG Starter fonctionne seul, immédiatement.

4. **Design sombre premium** — style luxe or/noir (restaurant), bronze/noir (artisan), bleu teal/marine (coach) qui sort de l'esthétique "claire et générique" de 90% des thèmes gratuits.

## Après la publication

### Maintenir les thèmes

- Chaque nouvelle version demande un bump de `Version:` dans `style.css` ET de `Stable tag:` dans `readme.txt`, puis un re-upload via l'interface wordpress.org
- Réponds aux questions dans le forum de support du thème (https://wordpress.org/support/theme/[slug])
- Les bugs doivent être corrigés sous 14 jours en général

### Monétisation indirecte

- Les utilisateurs qui installent le thème peuvent cliquer sur "Author: Alliance Group" → direction https://alliancegroupe-inc.com
- C'est une source de trafic qualifié (gens qui ont besoin d'un site WordPress)
- Tu peux proposer des upsells depuis ton site : installation, personnalisation, version premium

## Support et docs officielles

- Handbook officiel : https://make.wordpress.org/themes/handbook/
- Required checks : https://make.wordpress.org/themes/handbook/review/required/
- Tags valides : https://make.wordpress.org/themes/handbook/review/required/theme-tags/
- Theme Check plugin : https://wordpress.org/plugins/theme-check/
- Trac (tickets de review) : https://themes.trac.wordpress.org/

## Questions fréquentes

**Q : Combien de temps prend la review ?**
R : Entre 2 et 8 semaines en moyenne, parfois plus. C'est gratuit mais long.

**Q : Puis-je soumettre les 4 thèmes en même temps ?**
R : Oui, mais la review de chaque thème est séparée. Préfère soumettre restaurant d'abord, voir si ça passe, puis artisan, coach et avocat en appliquant les éventuelles corrections demandées. Note : avocat embarque un Custom Post Type (`ag_domaine`), ce qui peut faire l'objet de remarques de la part de l'équipe Theme Review (les CPT sont normalement réservés aux plugins). Si c'est rejeté, on déplacera le CPT dans le plugin compagnon.

**Q : Est-ce que je peux faire de la pub pour alliancegroupe-inc.com dans le thème ?**
R : Oui, mais discrètement. Un lien dans le footer avec `rel="nofollow"` est accepté (c'est déjà le cas dans `footer.php` de chaque thème). Évite les pop-ups, bandeaux ou notices admin intrusives — elles sont systématiquement rejetées.

**Q : Est-ce que je peux mettre des liens affiliés ou du tracking ?**
R : Non, c'est interdit. Le thème doit fonctionner sans aucun tracking externe.

**Q : Puis-je vendre une version "premium" de ces thèmes ailleurs ?**
R : Oui, tant que la version gratuite sur wordpress.org reste fonctionnelle et sous GPL v2. Tu peux tout à fait vendre une version "Pro" avec plus de features sur ton site, ou proposer des services d'installation payants.
