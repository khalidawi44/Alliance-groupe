#!/bin/bash
# ============================================================================
# SessionStart — Reprise automatique entre conversations Claude
# ----------------------------------------------------------------------------
# But : relier chaque nouvelle conversation a la precedente. La sortie de ce
# hook est injectee dans le contexte de Claude au demarrage de CHAQUE session
# (web, PC ou mobile). Plus besoin de re-expliquer ou on en etait.
#
# Lecture seule, rapide, idempotent, non interactif.
# ============================================================================
set -euo pipefail

cd "${CLAUDE_PROJECT_DIR:-.}"

echo "===== REPRISE DE SESSION — Alliance Groupe ====="
echo "(Genere par .claude/hooks/session-start.sh — etat reel du repo)"
echo

# --- Branche & alignement git -------------------------------------------------
BRANCH="$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
echo "## Branche de travail : ${BRANCH}"
echo "Rappel : sur Claude Code web, le conteneur est neuf a chaque session."
echo "Seul ce qui est COMMITE + POUSSE survit. Pense a pousser avant de fermer."
echo

# --- Derniers commits ---------------------------------------------------------
echo "## Derniers commits"
git log --oneline -8 2>/dev/null || echo "(pas d'historique)"
echo

# --- Travail non commite (= ce qui serait perdu) ------------------------------
DIRTY="$(git status --porcelain 2>/dev/null || true)"
if [ -n "$DIRTY" ]; then
	echo "## ⚠️ Modifications NON commitees (a sauvegarder avant fermeture)"
	echo "$DIRTY"
else
	echo "## Arbre de travail propre (rien en attente)"
fi
echo

# --- Focus actif (versions/templates en cours) --------------------------------
if [ -f .WORKING_ON.md ]; then
	echo "## Version active (.WORKING_ON.md)"
	grep -m1 -i "version active" .WORKING_ON.md 2>/dev/null || true
	echo
fi
if [ -f .AG_FOCUS ]; then
	echo "## Templates en focus (.AG_FOCUS)"
	grep -vE '^\s*#|^\s*$' .AG_FOCUS 2>/dev/null | paste -sd ' ' - || true
	echo
fi

# --- Reprise rapide depuis HANDOFF.md ----------------------------------------
if [ -f HANDOFF.md ]; then
	echo "## Reprise rapide (en-tete HANDOFF.md)"
	# Les premieres lignes contiennent date + branche + reprise rapide.
	sed -n '1,6p' HANDOFF.md
	echo
	echo "→ Detail complet : lire HANDOFF.md (§9 = taches restantes), BACKLOG.md."
fi
echo

echo "===== FIN REPRISE — mets a jour HANDOFF.md avant de fermer la session ====="
