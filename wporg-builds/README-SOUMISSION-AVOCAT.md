# Build WordPress.org — AG Starter Avocat (thème GRATUIT)

Ce dossier `wporg-builds/ag-starter-avocat/` est la **version « .org propre »** du thème
gratuit, prête à tester puis soumettre à https://wordpress.org/themes/upload/.

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
