#!/usr/bin/env bash
# ============================================================================
# stamp-handoff.sh — tamponne automatiquement l'en-tete de HANDOFF.md
# ----------------------------------------------------------------------------
# Reecrit la ligne "Derniere mise a jour : <date> — branche de travail : <branche>"
# avec la vraie date du jour et la vraie branche courante.
#
# Appele automatiquement par le pre-commit git (voir scripts/install-git-hooks.sh)
# => l'en-tete de reprise ne peut plus mentir, meme si personne ne pense a la
# mettre a jour. Rien n'est perdu en cas de plantage : c'est commite + pousse.
#
# Idempotent : si la ligne est deja a jour, ne change rien.
# ============================================================================
set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || echo .)"
HANDOFF="${REPO_ROOT}/HANDOFF.md"

[ -f "$HANDOFF" ] || exit 0

BRANCH="$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
TODAY="$(date +%F)"
NEW_LINE="> Dernière mise à jour : ${TODAY} — branche de travail : \`${BRANCH}\` (tampon auto à chaque commit)."

# Remplace la 1re ligne commencant par "> Dernière mise à jour :".
# Si absente, on ne casse rien (HANDOFF garde sa structure).
tmp="$(mktemp)"
awk -v repl="$NEW_LINE" '
	!done && /^> Dernière mise à jour :/ { print repl; done=1; next }
	{ print }
' "$HANDOFF" > "$tmp"

if ! cmp -s "$tmp" "$HANDOFF"; then
	mv "$tmp" "$HANDOFF"
else
	rm -f "$tmp"
fi
