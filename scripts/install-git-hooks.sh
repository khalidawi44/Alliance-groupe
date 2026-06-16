#!/usr/bin/env bash
# Installe un pre-commit hook qui execute scripts/check-all-locks.sh
# avant chaque commit. Bloque tout commit qui modifie un template/plugin
# verrouille (i.e. NON liste dans .AG_FOCUS).
#
# Usage : bash scripts/install-git-hooks.sh

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOOK_PATH="${REPO_ROOT}/.git/hooks/pre-commit"

if [[ ! -d "${REPO_ROOT}/.git" ]]; then
    echo "ERROR: ${REPO_ROOT} is not a git repository." >&2
    exit 1
fi

cat > "${HOOK_PATH}" <<'HOOK'
#!/usr/bin/env bash
# AG cadena + reprise — auto-installed by scripts/install-git-hooks.sh
# 1) Bloque tout commit qui modifie un template/plugin verrouille.
# 2) Tamponne HANDOFF.md (date + branche) a CHAQUE commit -> la reprise
#    entre conversations ne peut plus se perimer, meme en cas de plantage.
# Les templates en focus actif (.AG_FOCUS) sont autorises.

set -e

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "${REPO_ROOT}"

# --- Tampon de reprise (toujours, meme sur les commits "unlock-") ---
if [[ -f scripts/stamp-handoff.sh ]]; then
    bash scripts/stamp-handoff.sh || true
    git add HANDOFF.md 2>/dev/null || true
fi

# Si le message de commit commence par "unlock-<slug>:" on autorise
# (procedure exceptionnelle).
if [[ -n "${COMMIT_MSG_FILE:-}" && -f "${COMMIT_MSG_FILE}" ]]; then
    if grep -qE "^unlock-(free|premium|business|.+):" "${COMMIT_MSG_FILE}"; then
        exit 0
    fi
fi

bash scripts/check-all-locks.sh

# --- Rappel release : code d'un template modifie sans rebuild du .zip ---
# (non bloquant : sinon les acheteurs ne recoivent pas la MAJ)
if [[ -f scripts/check-staged-releases.sh ]]; then
    bash scripts/check-staged-releases.sh || true
fi
HOOK

chmod +x "${HOOK_PATH}"

echo "Installed: ${HOOK_PATH}"
echo "Pre-commit hook execute maintenant scripts/check-all-locks.sh."
echo "Le focus actif est lu depuis .AG_FOCUS a la racine du repo."
