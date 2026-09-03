#!/usr/bin/env bash
# Verifie l'integrite de TOUS les templates/plugins AG, en excluant
# ceux listes dans .AG_FOCUS (les templates en developpement actif).
#
# Usage : bash scripts/check-all-locks.sh

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOWNLOADS_DIR="${REPO_ROOT}/alliance-groupe-theme/assets/downloads"
FOCUS_FILE="${REPO_ROOT}/.AG_FOCUS"

cd "${REPO_ROOT}"

# Lit la liste des slugs en focus (ignore commentaires et lignes vides)
FOCUS_LIST=()
if [[ -f "${FOCUS_FILE}" ]]; then
    while IFS= read -r line || [[ -n "${line}" ]]; do
        line="${line%%#*}"      # remove comments
        line="${line// /}"      # remove spaces
        line="${line//$'\t'/}"  # remove tabs
        line="${line//$'\r'/}"  # remove CR (.AG_FOCUS est en CRLF sous Windows)
        [[ -n "${line}" ]] && FOCUS_LIST+=("${line}")
    done < "${FOCUS_FILE}"
fi

# Tous les dossiers susceptibles d'etre lockes
ALL_SLUGS=(
    "ag-starter-avocat"
    "ag-starter-barber"
    "ag-starter-artisan"
    "ag-starter-coach"
    "ag-starter-restaurant"
    "ag-starter-association"
    "ag-fidelite-association"
    "ag-starter-companion"
    "ag-premium-avocat"
    "ag-business-avocat"
    "ag-premium-barber"
    "ag-business-barber"
)

is_in_focus() {
    local needle="$1"
    for s in "${FOCUS_LIST[@]+"${FOCUS_LIST[@]}"}"; do
        [[ "$s" == "$needle" ]] && return 0
    done
    return 1
}

# Verifie un .LOCK.sha256 en tolerant les fins de ligne.
# Sous Windows, core.autocrlf=true fait sortir les fichiers texte en CRLF
# alors que les locks ont ete generes sur du LF : l'empreinte brute ne peut
# pas correspondre. On compare donc l'empreinte brute PUIS, si elle echoue,
# l'empreinte du contenu sans CR. Un vrai changement de contenu echoue aux deux.
# Affiche la liste des fichiers en defaut (vide = tout est conforme).
verify_lock() {
    local lock_file="$1"
    local expected path actual
    while read -r expected path; do
        [[ -z "${expected}" || -z "${path}" ]] && continue
        path="${path#\*}"          # marqueur binaire eventuel de sha256sum
        path="${path//$'\r'/}"
        if [[ ! -f "${path}" ]]; then
            echo "${path}: MISSING"
            continue
        fi
        actual="$(sha256sum < "${path}")"
        actual="${actual%% *}"
        [[ "${actual}" == "${expected}" ]] && continue
        actual="$(tr -d '\r' < "${path}" | sha256sum)"
        actual="${actual%% *}"
        [[ "${actual}" == "${expected}" ]] && continue
        echo "${path}: FAILED"
    done < "${lock_file}"
}

FAIL=0
SKIPPED=()
for SLUG in "${ALL_SLUGS[@]}"; do
    DIR="${DOWNLOADS_DIR}/${SLUG}"
    LOCK_FILE="${DIR}/.LOCK.sha256"

    if [[ ! -d "${DIR}" ]]; then
        # Dossier n'existe pas (= pas pertinent pour ce checkout)
        continue
    fi

    if is_in_focus "${SLUG}"; then
        SKIPPED+=("${SLUG}")
        continue
    fi

    if [[ ! -f "${LOCK_FILE}" ]]; then
        echo "❌ ${SLUG} : .LOCK.sha256 manquant" >&2
        echo "   -> bash scripts/regenerate-lock.sh ${SLUG}" >&2
        FAIL=1
        continue
    fi

    BAD_FILES="$(verify_lock "${LOCK_FILE}")"
    if [[ -z "${BAD_FILES}" ]]; then
        echo "OK — ${SLUG} ($(wc -l < "${LOCK_FILE}") files verified)"
    else
        echo "" >&2
        echo "❌ ${SLUG} a ete modifie. Fichiers qui different :" >&2
        echo "${BAD_FILES}" >&2
        echo "" >&2
        echo "   Si intentionnel : ajouter '${SLUG}' a .AG_FOCUS et regenerer le lock." >&2
        FAIL=1
    fi
done

if [[ ${#SKIPPED[@]} -gt 0 ]]; then
    echo ""
    echo "⏭  Skipped (focus actif) : ${SKIPPED[*]}"
fi

if [[ $FAIL -eq 0 ]]; then
    echo ""
    echo "All locks verified."
    exit 0
fi

exit 1
