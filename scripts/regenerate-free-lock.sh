#!/usr/bin/env bash
# Regenerate .LOCK.sha256 with current file hashes. Use only when an
# intentional, documented unlock is being committed (see LOCKED.md).
#
# Usage: bash scripts/regenerate-free-lock.sh

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FREE_DIR="${REPO_ROOT}/alliance-groupe-theme/assets/downloads/ag-starter-avocat"
LOCK_FILE="${FREE_DIR}/.LOCK.sha256"

cd "${REPO_ROOT}"

find "alliance-groupe-theme/assets/downloads/ag-starter-avocat" -type f \
    ! -name "LOCKED.md" \
    ! -name ".LOCK.sha256" \
    | sort \
    | xargs sha256sum > "${LOCK_FILE}"

echo "Regenerated ${LOCK_FILE}"
echo "$(wc -l < "${LOCK_FILE}") files locked."
