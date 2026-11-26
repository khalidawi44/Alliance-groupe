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

# --- (Re)installe les hooks git dans ce conteneur neuf -----------------------
# .git/hooks ne survit pas au conteneur ephemere : on reinstalle a chaque
# session pour que le tampon auto de HANDOFF.md (pre-commit) soit toujours actif.
if [ -f scripts/install-git-hooks.sh ]; then
	bash scripts/install-git-hooks.sh >/dev/null 2>&1 || true
fi
# Tampon immediat (date + branche), au cas ou aucun commit ne suit cette session.
if [ -f scripts/stamp-handoff.sh ]; then
	bash scripts/stamp-handoff.sh >/dev/null 2>&1 || true
fi

echo "===== REPRISE DE SESSION — Alliance Groupe ====="
echo "(Genere par .claude/hooks/session-start.sh — etat reel du repo)"
echo

# --- Branche & alignement git -------------------------------------------------
BRANCH="$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
echo "## Branche de travail : ${BRANCH}"
echo "Rappel : sur Claude Code web, le conteneur est neuf a chaque session."
echo "Seul ce qui est COMMITE + POUSSE survit. Pense a pousser avant de fermer."
echo

# --- Modules du theme : alerte immediate si l'un d'eux est eteint -------------
if [ -f scripts/check-modules.sh ]; then
	if ! MODS_OUT="$(bash scripts/check-modules.sh 2>&1)"; then
		echo "## 🚨 MODULES ETEINTS : A RALLUMER AVANT TOUT AUTRE TRAVAIL (regle de Fabrice : toujours operationnel)"
		echo "${MODS_OUT}"
		echo
	else
		echo "## Modules du theme : ${MODS_OUT}"
		echo
	fi
fi

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

# --- Regles de conception permanentes (tous supports) ------------------------
if [ -f REGLES-CONCEPTION.md ]; then
	echo "## REGLES DE CONCEPTION (REGLES-CONCEPTION.md) : LOI, a appliquer a chaque travail d'interface"
	cat REGLES-CONCEPTION.md
	echo
fi

# --- Cartographie + journal (memoire longue du projet) -----------------------
if [ -f CARTOGRAPHIE.md ]; then
	echo "## Cartographie (CARTOGRAPHIE.md) : LIRE EN ENTIER, puis ajouter une ligne au journal pour TOUT changement"
	sed -n '1,4p' CARTOGRAPHIE.md
	echo "Derniers jalons du journal :"
	awk '/^## Journal/{f=1;next} f && /^- /{print; if(++n>=6) exit}' CARTOGRAPHIE.md 2>/dev/null || true
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

echo "===== FIN REPRISE — mets a jour CARTOGRAPHIE.md (journal) et HANDOFF.md avant de fermer la session ====="
