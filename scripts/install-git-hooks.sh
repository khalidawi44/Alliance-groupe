#!/usr/bin/env bash
# Install a pre-commit hook that runs scripts/check-all-locks.sh
# before every commit and fails if a locked Free or Premium file was
# modified.
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
# Blocks any commit that modifies a locked Free or Premium file.

set -e

REPO_ROOT="$(git rev-parse --show-toplevel)"
LOCKS=(
    "alliance-groupe-theme/assets/downloads/ag-starter-avocat/:free"
    "alliance-groupe-theme/assets/downloads/ag-premium-avocat/:premium"
)

CHANGED_STAGED=$(git diff --cached --name-only --diff-filter=ACDMR)

for entry in "${LOCKS[@]}"; do
    PREFIX="${entry%%:*}"
    NAME="${entry##*:}"
    HITS=$(echo "${CHANGED_STAGED}" \
        | grep "^${PREFIX}" \
        | grep -v "^${PREFIX}LOCKED\.md$" \
        | grep -v "^${PREFIX}\.LOCK\.sha256$" \
        || true)
    if [[ -z "${HITS}" ]]; then
        continue
    fi

    # Allow if commit message contains "unlock-${NAME}:"
    if [[ -n "${COMMIT_MSG_FILE:-}" && -f "${COMMIT_MSG_FILE}" ]]; then
        if grep -q "unlock-${NAME}:" "${COMMIT_MSG_FILE}"; then
            continue
        fi
    fi

    echo "" >&2
    echo "🔒 ${PREFIX} (${NAME}) is LOCKED. Forbidden staged files:" >&2
    echo "${HITS}" | sed 's/^/    /' >&2
    echo "" >&2
    echo "If intentional, see LOCKED.md and prefix your commit message with 'unlock-${NAME}:'" >&2
    exit 1
done

exit 0
HOOK

chmod +x "${HOOK_PATH}"

echo "Installed: ${HOOK_PATH}"
echo "Pre-commit hook now blocks Free AND Premium modifications."
