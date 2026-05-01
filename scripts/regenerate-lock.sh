#!/usr/bin/env bash
# Generique : regenere .LOCK.sha256 pour un dossier de template/plugin AG.
#
# Usage : bash scripts/regenerate-lock.sh <slug>
# Ex.   : bash scripts/regenerate-lock.sh ag-starter-restaurant
#
# Le dossier doit etre dans alliance-groupe-theme/assets/downloads/<slug>/

set -euo pipefail

if [[ $# -lt 1 ]]; then
    echo "Usage: bash scripts/regenerate-lock.sh <slug>" >&2
    echo "Slugs valides : tout dossier dans alliance-groupe-theme/assets/downloads/" >&2
    exit 1
fi

SLUG="$1"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIR="${REPO_ROOT}/alliance-groupe-theme/assets/downloads/${SLUG}"
LOCK_FILE="${DIR}/.LOCK.sha256"

if [[ ! -d "${DIR}" ]]; then
    echo "ERROR: dossier introuvable : ${DIR}" >&2
    exit 1
fi

cd "${REPO_ROOT}"

find "alliance-groupe-theme/assets/downloads/${SLUG}" -type f \
    ! -name "LOCKED.md" \
    ! -name ".LOCK.sha256" \
    | sort \
    | xargs sha256sum > "${LOCK_FILE}"

echo "Regenerated ${LOCK_FILE}"
echo "$(wc -l < "${LOCK_FILE}") files locked for '${SLUG}'."
