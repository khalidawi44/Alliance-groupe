# 🔒 LOCKED — ag-starter-restaurant

> **Déblocage ponctuel autorisé le `2026-06-22`** (autorisation écrite explicite de l'utilisateur) : rendre éditables via le Customizer les **chiffres clés (stats hero)**, les **3 témoignages** et la **FAQ**, qui étaient figés par le preset. Override manuel qui prime sur le preset (même pattern que les spécialités). Périmètre limité à ces sections + leurs réglages.

**Verrouille le 2026-05-01.** Pas en developpement actif.

Le focus actuel est sur **Barber** (cf `.AG_FOCUS` a la racine du repo).

## Comment debloquer

1. Editer `.AG_FOCUS` et ajouter `ag-starter-restaurant`
2. Committer les modifs
3. Le pre-commit hook ne bloquera plus
4. Quand le travail est termine : retirer du `.AG_FOCUS` puis :
   ```
   bash scripts/regenerate-lock.sh ag-starter-restaurant
   ```
5. Committer le nouveau `.LOCK.sha256`

## Verifier l'integrite du lock

```
bash scripts/check-all-locks.sh
```
