# 🔒 LOCKED — ag-starter-avocat (Free)

**This directory is the FREE version of the AG Starter Avocat theme. It is locked from `2026-04-27`.**

Aucun fichier de ce dossier ne doit être modifié — c'est la version stable validée par l'utilisateur (commit `698f3c9`).

## Règles

1. **Aucune modification** d'aucun fichier ici, sous aucun prétexte
2. Toute fonctionnalité **Premium** doit aller dans `../ag-premium-avocat/`
3. Toute fonctionnalité **Business** doit aller dans `../ag-business-avocat/`
4. Premium et Business utilisent **uniquement** des classes CSS préfixées :
   - Premium : `.ag-premium-*`
   - Business : `.ag-business-*`
5. Free expose des hooks (`do_action`, `apply_filters`) — Premium/Business s'y attache. Aucune modification de la signature des hooks.

## Comment débloquer (procédure exceptionnelle)

Si une modification Free est strictement indispensable :
1. L'utilisateur doit donner l'autorisation explicite par écrit
2. Mettre à jour ce fichier `LOCKED.md` avec la justification et la nouvelle date
3. Régénérer `.LOCK.sha256` via `scripts/regenerate-free-lock.sh`
4. Inclure la déverrouillage dans le commit message (`unlock-free: <raison>`)

## Vérifier l'intégrité du lock

```bash
bash scripts/check-free-lock.sh
```

Renvoie `OK` si rien n'a changé, sinon liste les fichiers modifiés avec leur hash attendu.

## Installer le hook git pré-commit

```bash
bash scripts/install-git-hooks.sh
```

Une fois installé, tout commit qui touche à un fichier de ce dossier (sauf `LOCKED.md` lui-même) échouera automatiquement.

## Périmètre du lock

- **Verrouillé** : tous les fichiers de `ag-starter-avocat/` SAUF `LOCKED.md`
- **Non concerné** : `ag-premium-avocat/`, `ag-business-avocat/`, plugins, etc.

## Journal des déverrouillages

- **2026-09-24 — `unlock-free:` libellés « Domaines d'intervention », publié en 1.1.23.**
  Autorisation écrite de Fabrice. `ag_avocat_opt()` utilise son 2ᵉ argument comme
  repli (pas le tableau de défauts du Customizer) : les replis en dur restaient
  « Domaines d'expertise » / « Specialites » sur une install neuve. Corrigés dans
  `front-page.php`, `page-cabinet.php`, `single-ag_domaine.php`, `inc/guide.php`,
  `inc/cpt-domaine.php` (libellés du CPT). Seul le **slug** de page `expertise`
  (identifiant d'URL) subsiste. Build `.org` aligné en 1.1.23. `.LOCK.sha256`
  régénéré, template de nouveau **contrôlé**.

- **2026-09-24 — `unlock-free:` conformité RIN (format WordPress.org), publié en 1.1.22.**
  Autorisation écrite de Fabrice. Correctif du crédit (un seul lien `rel="nofollow"`,
  retrait du 2ᵉ lien) en miroir du build `.org` `wporg-builds/ag-starter-avocat`
  (v1.1.22 : compteurs vides par défaut, libellés « Practice areas », i18n
  `fr_FR.po/.mo` recompilés à 100 %). `.LOCK.sha256` régénéré, template de nouveau
  **contrôlé**.

- **2026-09-24 — `unlock-free:` conformité RIN art. 10 (autorisation écrite de Fabrice).**
  Modifications strictement déontologiques : suppression des chiffres inventés
  (`render_counters` désormais vide par défaut, configurable via Customizer),
  retrait du JSON-LD `Organization` « franco-italo-marocaine » du `footer.php`
  (le schema `Attorney` suffit), remplacement de la grande publicité du pied de
  page gratuit par un crédit discret (RIN art. 10.5 — pas de bannière), correction
  des libellés (« Domaines d'expertise » → « Domaines d'intervention »,
  « Avocat inscrit au %s » qui doublonnait, accents), et CSS `.ag-deonto-item`
  (débordement mobile). Publié en **1.1.20**. `.LOCK.sha256` régénéré sur ce
  contenu ; le template repasse **contrôlé** (retiré de `.AG_FOCUS`).
