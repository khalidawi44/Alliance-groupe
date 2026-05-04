# 🔒 LOCKED — ag-premium-avocat (Premium)

**Plugin verrouillé le `2026-04-28`.** Version Premium finalisée par l'utilisateur, plus aucune modification autorisée tant qu'il ne demande pas explicitement.

## Règles

1. **Aucune modification** d'aucun fichier ici, sous aucun prétexte
2. Toute fonctionnalité **Business** doit aller dans `../ag-business-avocat/`
3. Business utilise **uniquement** des classes CSS préfixées `.ag-business-*`
4. Business hérite des comportements Premium via les body classes (`ag-premium-active`) et hooks Free
5. Aucun override des classes `.ag-premium-*` par Business — Business ajoute, ne modifie pas

## Comment débloquer (procédure exceptionnelle)

Si une modification Premium est strictement indispensable :
1. L'utilisateur doit donner l'autorisation explicite par écrit
2. Mettre à jour ce fichier `LOCKED.md` avec la justification et la nouvelle date
3. Régénérer `.LOCK.sha256` via `scripts/regenerate-premium-lock.sh`
4. Inclure le déblocage dans le commit message (`unlock-premium: <raison>`)

## Vérifier l'intégrité du lock

```bash
bash scripts/check-premium-lock.sh
```

## Périmètre du lock

- **Verrouillé** : tous les fichiers de `ag-premium-avocat/` SAUF `LOCKED.md`
- **Non concerné** : `ag-starter-avocat/` (a son propre lock), `ag-business-avocat/` (en cours), plugins tiers
