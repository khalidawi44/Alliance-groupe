# Build WordPress.org — AG Starter Avocat (thème GRATUIT)

Ce dossier `wporg-builds/ag-starter-avocat/` est la **version « .org propre »** du thème
gratuit, prête à tester puis soumettre à https://wordpress.org/themes/upload/.

## ✅ v1.1.21 — 2ᵉ passe i18n (complète)
Après vérification point par point du build 1.1.20, il RESTAIT du français non traité par la 1ʳᵉ passe (qui n'avait converti que les appels `__()`) :
- **Libellés du Customizer** (`inc/customizer.php`) : ~33 `'label' => 'Français'` bruts, non traduisibles → c'est l'écran que le relecteur ouvre. **Corrigés** en `esc_html__( 'English', … )`.
- **Valeurs par défaut affichées** (`front-page.php` + tableau `defaults`) : ~30 textes FR affichés en install neuve → passés en anglais (labels de formulaire enveloppés en `__()` pour rester FR via le `.mo`).
- **Page guide admin** (`inc/guide.php`) : tout le contenu FR → anglais traduisible.
- **`inc/ag-prefill.php`** : la création automatique des 3 pages légales à l'activation est **désactivée** (un thème ne doit pas générer de contenu à l'activation — règle du répertoire).
- **`.pot` + `fr_FR.po`/`fr_FR.mo`** régénérés : **297 chaînes, 297 traduites FR** (validées via gettext).
- **`inc/pro-features.php`** : le français restant y est du **code mort** (méthodes premium/business protégées par `is_at_least()` + `render_testimonials()`/`render_footer_branding()` = `return;` immédiat). En tier `free` (le build .org), **aucun français n'est affiché**. Laissé tel quel.
- Zip prêt : **`ag-starter-avocat-1.1.21.zip`**.

## ⛔ REFUS ticket #277598 (v1.1.18) — CORRIGÉ en v1.1.20
Le relecteur (fahimmurshed) a fermé le ticket en **not-approved** pour DEUX raisons, toutes deux d'internationalisation :
1. **« Theme should be in English »** : les textes source (Customizer, libellés) étaient en français.
2. **Fichiers de traduction manquants** : pas de `/languages` ni de `.pot`.

**Correctif appliqué (v1.1.20) :**
- **Les 207 chaînes source sont passées en ANGLAIS** (dans tous les `.php` via `__()/esc_html__()` etc.).
- **Traduction française fournie** : `languages/fr_FR.po` + `languages/fr_FR.mo` → l'utilisateur voit le thème en français (via `load_theme_textdomain`, fichier `{locale}.mo`).
- **`.pot` régénéré** en anglais : `languages/ag-starter-avocat.pot`.
- **`readme.txt` + description `style.css`** réécrits en **anglais**. Version bumpée en **1.1.20**.
- Zip prêt : **`ag-starter-avocat-1.1.20.zip`**.
- ⚠️ Point résiduel à surveiller : `inc/ag-prefill.php` insère des pages légales en français à l'activation (contenu, pas de l'UI). Si le reviewer le signale, on passera aussi ce contenu en anglais-source + traduction.

## Ce qui a été RETIRÉ (interdit par WordPress.org)
- `inc/class-ag-updater.php` + `inc/theme-updater.php` (auto-update GitHub) — sur .org, les MAJ passent par le répertoire.
- `inc/class-ag-licence-client.php` (appel licence vers ton serveur) — pas de « phone-home ».
- Le filtre `ag_force_auto_updates` (auto-MAJ forcée).
- Les liens externes vers le `.zip` du Companion → remplacés par une recherche wp.org (admin).
- Crédit footer réduit à **1 seul lien** (`rel="nofollow"`).
- `.LOCK.sha256` / `LOCKED.md` (internes).

→ Résultat : `grep` ne trouve plus **aucun** `wp_remote_*` ni `githubusercontent` dans le build. `php -l` OK.

## Modèle (comme Divi / Astra / Kadence)
- **Gratuit** → wordpress.org (ce build) = funnel.
- **Premium** → TON site (licence + serveur de MAJ déjà en place) — c'est le modèle Divi. Le premium ne va PAS sur wordpress.org.

## Avant de soumettre — À FAIRE par toi (obligatoire)
1. Installer le plugin **Theme Check** (Apparence → Theme Check) et lancer l'analyse sur ce build.
   Il doit ressortir **0 ERROR**. Corrige/dis-moi les points.
2. Tester sur un WP local (`WP_DEBUG = true`) : accueil, menu, 404, recherche, commentaires — aucune erreur PHP.
3. Compte wordpress.org : login `adminag`, pseudo `AGthèmes` (déjà déclarés dans readme.txt / style.css).

## ⚠️ Risques de review connus (à traiter)
1. **CPT dans le thème** (`inc/cpt-domaine.php`) — Theme Review **déconseille** les Custom Post Types dans un thème (« plugin territory »). C'est LE point le plus susceptible de blocage.
   → Solution propre (comme les gros) : **déplacer le CPT `ag_domaine` dans le plugin Companion**. Dis-moi « déplace le CPT » et je le fais (le thème garde un fallback si le CPT est absent).
2. **`inc/ag-prefill.php`** : ne crée du contenu que si un `ag-prefill.json` est présent (inerte sur une install normale). Probablement OK, à surveiller.
3. **`inc/pro-features.php`** : contient l'upsell Premium (autorisé façon Astra, mais le reviewer regarde). En l'absence du licence-client, tout retombe en tier `free` automatiquement.

## Soumission
- Zippe le dossier `ag-starter-avocat/` (le zip est déjà généré : `ag-starter-avocat.zip`).
- https://wordpress.org/themes/upload/ → téléverse → suis les retours de l'équipe Theme Review (2–8 semaines, allers-retours normaux).
