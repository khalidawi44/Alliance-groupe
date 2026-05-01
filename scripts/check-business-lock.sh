#!/usr/bin/env bash
# Verify that no file in the Business plugin directory has been modified
# since the lock was set. Reads expected hashes from .LOCK.sha256 and
# fails if any file's current hash differs (or is missing).
#
# Usage: bash scripts/check-business-lock.sh
# Exit:  0 OK, 1 if any locked file changed/missing.

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOCK_FILE="${REPO_ROOT}/alliance-groupe-theme/assets/downloads/ag-business-avocat/.LOCK.sha256"

if [[ ! -f "${LOCK_FILE}" ]]; then
    echo "ERROR: lock file missing at ${LOCK_FILE}" >&2
    exit 1
fi

cd "${REPO_ROOT}"

if sha256sum --quiet --check "${LOCK_FILE}" 2>/dev/null; then
    echo "OK — Business plugin integrity verified ($(wc -l < "${LOCK_FILE}") files)."
    exit 0
fi

echo ""
echo "❌ Business plugin has been modified. The following files differ from the lock:" >&2
sha256sum --check "${LOCK_FILE}" 2>&1 | grep -E "FAILED|MISSING" || true
echo "" >&2
echo "If this change is intentional, follow the unlock procedure in" >&2
echo "alliance-groupe-theme/assets/downloads/ag-business-avocat/LOCKED.md" >&2
exit 1
