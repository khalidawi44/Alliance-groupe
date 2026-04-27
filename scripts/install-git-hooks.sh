#!/usr/bin/env bash
# Install a pre-commit hook that runs scripts/check-free-lock.sh
# before every commit and fails if a locked Free file was modified.
#
# Usage: bash scripts/install-git-hooks.sh

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
# Blocks any commit that modifies a locked Free theme file.

set -e

REPO_ROOT="$(git rev-parse --show-toplevel)"
LOCKED_PREFIX="alliance-groupe-theme/assets/downloads/ag-starter-avocat/"

# Files staged for commit, excluding the lock-management files themselves.
CHANGED=$(git diff --cached --name-only --diff-filter=ACDMR \
    | grep "^${LOCKED_PREFIX}" \
    | grep -v "^${LOCKED_PREFIX}LOCKED\.md$" \
    | grep -v "^${LOCKED_PREFIX}\.LOCK\.sha256$" \
    || true)

if [[ -z "${CHANGED}" ]]; then
    exit 0
fi

# Allow if the commit message contains "unlock-free:" (set via -m or .git/COMMIT_EDITMSG)
if [[ -n "${COMMIT_MSG_FILE:-}" && -f "${COMMIT_MSG_FILE}" ]]; then
    if grep -q "unlock-free:" "${COMMIT_MSG_FILE}"; then
        exit 0
    fi
fi

echo "" >&2
echo "🔒 ag-starter-avocat (Free) is LOCKED. The following staged files are forbidden:" >&2
echo "${CHANGED}" | sed 's/^/    /' >&2
echo "" >&2
echo "If this is an intentional unlock, see LOCKED.md and prefix your commit message with 'unlock-free:'" >&2
exit 1
HOOK

chmod +x "${HOOK_PATH}"

echo "Installed: ${HOOK_PATH}"
echo "Pre-commit hook now blocks Free theme modifications."
