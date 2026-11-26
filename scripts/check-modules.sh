#!/usr/bin/env bash
# ============================================================================
# check-modules.sh : verifie qu'AUCUN module du theme n'est eteint.
# ----------------------------------------------------------------------------
# Contexte : debut novembre 2026, une fusion a remis une ancienne version de
# functions.php et 33 modules se sont eteints sans message. Ce controle empeche
# que cela se reproduise.
#
# Il verifie :
#   1. functions.php appelle bien le chargeur inc/ag-modules.php ;
#   2. inc/ag-prospection.php l'appelle aussi (second appel de secours) ;
#   3. chaque module de la liste du chargeur existe sur le disque ;
#   4. chaque fichier inc/ag-*.php est appele quelque part (sinon il est eteint).
#
# Sortie 0 = tout est allume. Sortie 1 = au moins un module eteint.
# Appele par le pre-commit, le pre-merge-commit et le demarrage de session.
# Usage : bash scripts/check-modules.sh
# ============================================================================
set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="${ROOT}/alliance-groupe-theme"
INC="${THEME}/inc"
LOADER="${INC}/ag-modules.php"
KO=0

dire_ko() { echo "ETEINT : $1"; KO=1; }

[[ -f "${LOADER}" ]] || { echo "ETEINT : le chargeur inc/ag-modules.php est absent."; exit 1; }

grep -qF "inc/ag-modules.php" "${THEME}/functions.php" \
	|| dire_ko "functions.php n'appelle plus inc/ag-modules.php (remettre le bloc CHARGEUR DE SECOURS)."
grep -qF "inc/ag-modules.php" "${INC}/ag-prospection.php" \
	|| dire_ko "inc/ag-prospection.php n'appelle plus inc/ag-modules.php (second appel de secours)."

# 3. Chaque module liste dans le chargeur existe.
while IFS= read -r nom; do
	[[ -f "${INC}/${nom}.php" ]] || dire_ko "le module ${nom}.php est dans la liste mais le fichier manque."
done < <(grep -oE "^[[:space:]]*'ag-[a-z0-9-]+'," "${LOADER}" | tr -d " \t',")

# 4. Chaque fichier inc/ag-*.php est appele quelque part dans le theme.
for f in "${INC}"/ag-*.php; do
	base="$(basename "${f}")"
	nom="${base%.php}"
	[[ "${base}" == "ag-modules.php" ]] && continue
	# Appel direct par nom de fichier (functions.php, un autre module, ag-import.php)...
	# On cherche le CHEMIN « /inc/nom.php » : une simple mention dans un commentaire ne compte pas.
	if grep -lF "/inc/${base}" "${THEME}/functions.php" "${THEME}/ag-import.php" "${INC}"/*.php 2>/dev/null \
		| grep -vxF "${f}" | grep -q .; then
		continue
	fi
	# ... ou nom de fichier entre apostrophes dans une liste de chargement ('ag-x.php').
	if grep -lF "'${base}'" "${THEME}/functions.php" "${INC}"/*.php 2>/dev/null \
		| grep -vxF "${f}" | grep -q .; then
		continue
	fi
	# ... ou presence dans la liste du chargeur.
	if grep -qE "^[[:space:]]*'${nom}'," "${LOADER}"; then
		continue
	fi
	dire_ko "${base} n'est appele nulle part. L'ajouter a la liste de inc/ag-modules.php."
done

if [[ "${KO}" -eq 0 ]]; then
	echo "OK : tous les modules du theme sont allumes."
	exit 0
fi
echo
echo "Reparation : ajouter le nom du module a la liste de alliance-groupe-theme/inc/ag-modules.php,"
echo "ou remettre le bloc CHARGEUR DE SECOURS dans functions.php. Puis relancer ce controle."
exit 1
