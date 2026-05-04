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
# AG cadena — auto-installed by scripts/install-git-hooks.sh
# Bloque tout commit qui modifie un template/plugin verrouille.
# Les templates en focus actif (.AG_FOCUS) sont autorises.

set -e

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "${REPO_ROOT}"

# Si le message de commit commence par "unlock-<slug>:" on autorise
# (procedure exceptionnelle).
if [[ -n "${COMMIT_MSG_FILE:-}" && -f "${COMMIT_MSG_FILE}" ]]; then
    if grep -qE "^unlock-(free|premium|business|.+):" "${COMMIT_MSG_FILE}"; then
        exit 0
    fi
fi

bash scripts/check-all-locks.sh
HOOK

chmod +x "${HOOK_PATH}"

echo "Installed: ${HOOK_PATH}"
echo "Pre-commit hook execute maintenant scripts/check-all-locks.sh."
echo "Le focus actif est lu depuis .AG_FOCUS a la racine du repo."
