#!/usr/bin/env bash
# check-staged-releases.sh — ALERTE (non bloquante) au commit.
# Si on a staged des modifs dans le code source d'un template SANS reconstruire
# son .zip dans le même commit, on prévient : sinon les acheteurs ne reçoivent
# pas la MAJ. Appelé par le hook pre-commit.
#
# N'échoue jamais (exit 0) — c'est un rappel, pas un blocage : on peut éditer
# en plusieurs commits puis faire `bash scripts/release.sh <slug> <version>`.

ROOT="$(git rev-parse --show-toplevel)" || exit 0
rel="alliance-groupe-theme/assets/downloads"

SLUGS="ag-starter-barber ag-starter-association ag-starter-avocat ag-starter-restaurant ag-starter-artisan ag-starter-coach ag-starter-companion ag-fidelite-association ag-premium-avocat ag-business-avocat ag-premium-barber ag-business-barber"

staged="$(git diff --cached --name-only)"
[ -z "$staged" ] && exit 0

warned=0
for slug in $SLUGS; do
    # modifs dans le dossier source du template ?
    if echo "$staged" | grep -q "^$rel/$slug/"; then
        # le zip a-t-il été staged aussi ?
        if ! echo "$staged" | grep -q "^$rel/$slug.zip$"; then
            if [ "$warned" -eq 0 ]; then
                echo ""
                echo "⚠  RAPPEL RELEASE — code modifié sans rebuild du .zip :"
                warned=1
            fi
            echo "   • $slug : pense à  bash scripts/release.sh $slug <version>  (sinon les acheteurs ne reçoivent pas la MAJ)"
        fi
    fi
done
exit 0
