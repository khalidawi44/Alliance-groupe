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
