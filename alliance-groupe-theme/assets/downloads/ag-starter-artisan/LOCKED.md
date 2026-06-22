# 🔒 LOCKED — ag-starter-artisan

> **Déblocage ponctuel autorisé le `2026-06-22`** (autorisation écrite explicite de l'utilisateur) : rendre éditables via le Customizer les **chiffres clés**, **témoignages**, **FAQ** (override preset), la **galerie réalisations** (6 photos), la **photo équipe** et la **timeline « Notre histoire »**, qui étaient figés par le métier/preset. Périmètre limité à ces sections.

**Verrouille le 2026-05-01.** Pas en developpement actif.

Le focus actuel est sur **Barber** (cf `.AG_FOCUS` a la racine du repo).

## Comment debloquer

1. Editer `.AG_FOCUS` et ajouter `ag-starter-artisan`
2. Committer les modifs
3. Le pre-commit hook ne bloquera plus
4. Quand le travail est termine : retirer du `.AG_FOCUS` puis :
   ```
   bash scripts/regenerate-lock.sh ag-starter-artisan
   ```
5. Committer le nouveau `.LOCK.sha256`

## Verifier l'integrite du lock

```
bash scripts/check-all-locks.sh
```
