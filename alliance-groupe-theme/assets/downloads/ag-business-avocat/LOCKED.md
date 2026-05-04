# 🔒 LOCKED — ag-business-avocat (Business)

**Plugin verrouillé le `2026-05-01`.** Version Business finalisée par l'utilisateur, plus aucune modification autorisée tant qu'il ne demande pas explicitement.

Bloque toute modification du thème avocat dans son ensemble :
- `ag-starter-avocat/` (Free) → déjà verrouillé
- `ag-premium-avocat/` (Premium) → déjà verrouillé
- `ag-business-avocat/` (Business) → **verrouillé maintenant** ✅

## Règles

1. **Aucune modification** d'aucun fichier ici, sous aucun prétexte
2. Aucune nouvelle feature ni correctif tant que l'utilisateur ne demande pas explicitement
3. Si une nouvelle feature est demandée, elle doit aller dans un nouveau plugin séparé (ag-business-avocat-v2 par exemple) ou un autre namespace

## Comment débloquer (procédure exceptionnelle)

Si une modification Business est strictement indispensable :
1. L'utilisateur doit donner l'autorisation explicite par écrit ("débloque le thème avocat business")
2. Mettre à jour ce fichier `LOCKED.md` avec la justification et la nouvelle date
3. Régénérer `.LOCK.sha256` via `scripts/regenerate-business-lock.sh`
4. Inclure le déblocage dans le commit message (`unlock-business: <raison>`)

## Vérifier l'intégrité du lock

```bash
bash scripts/check-business-lock.sh
```

Ou tous les locks (Free + Premium + Business) :

```bash
bash scripts/check-all-locks.sh
```

## Périmètre du lock

- **Verrouillé** : tous les fichiers de `ag-business-avocat/` SAUF `LOCKED.md`
- Le `.zip` distribuable (`ag-business-avocat.zip` à la racine de `assets/downloads/`) reste rebuilable mais doit refléter exactement le contenu verrouillé

## Version verrouillée

Au moment du lock : **v0.49.0**
