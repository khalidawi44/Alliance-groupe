#!/usr/bin/env bash
# check-releases.sh — garantit que les ACHETEURS reçoivent les MAJ.
# Pour chaque template (thème ou plugin) distribué :
#   1) version du header (style.css / <slug>.php) == version du <slug>.json
#   2) le <slug>.zip livré == le code source actuel (pas de zip périmé)
#
# Sort en erreur (code 1) si un template est désynchronisé, avec la
# commande exacte à lancer pour corriger.
#
# Usage : bash scripts/check-releases.sh

set -u
ROOT="$(git rev-parse --show-toplevel)"
DL="$ROOT/alliance-groupe-theme/assets/downloads"
cd "$DL" || exit 2

SLUGS=(
    ag-starter-barber ag-starter-association ag-starter-avocat
    ag-starter-restaurant ag-starter-artisan ag-starter-coach
    ag-starter-companion ag-fidelite-association
    ag-premium-avocat ag-business-avocat
    ag-premium-barber ag-business-barber
)

problems=0
for slug in "${SLUGS[@]}"; do
    [ -d "$slug" ] || continue
    # version du header
    if [ -f "$slug/style.css" ]; then
        hver=$(grep -iE '^Version:' "$slug/style.css" | head -1 | grep -oE '[0-9][0-9.]*')
    elif [ -f "$slug/$slug.php" ]; then
        hver=$(grep -iE '^[[:space:]]*\*?[[:space:]]*Version:' "$slug/$slug.php" | head -1 | grep -oE '[0-9][0-9.]*')
    else
        hver="?"
    fi
    # version du json
    if [ -f "$slug.json" ]; then
        jver=$(grep -oE '"version"[^,]*' "$slug.json" | head -1 | grep -oE '[0-9][0-9.]*')
    else
        jver="(pas de .json — pas d'auto-update)"
    fi
    # cohérence des versions
    if [ -f "$slug.json" ] && [ "$hver" != "$jver" ]; then
        echo "❌ $slug : version header=$hver ≠ json=$jver"
        echo "   → bash scripts/bump-template.sh $slug <version>"
        problems=$((problems+1))
        continue
    fi
    # staleness du zip
    if [ -f "$slug.zip" ]; then
        tmp=$(mktemp -d)
        unzip -qq "$slug.zip" -d "$tmp" 2>/dev/null
        if ! diff -rq --exclude=".DS_Store" --exclude=".git*" "$tmp/$slug" "$slug" >/dev/null 2>&1; then
            echo "❌ $slug : le .zip livré ≠ le code source (zip PÉRIMÉ → les acheteurs reçoivent l'ancien code)"
            echo "   → bash scripts/bump-template.sh $slug <nouvelle_version>   (rebuild le zip + bump version)"
            problems=$((problems+1))
        fi
        rm -rf "$tmp"
    elif [ -f "$slug.json" ]; then
        echo "❌ $slug : .json présent mais .zip manquant (l'auto-update pointera vers un paquet absent)"
        problems=$((problems+1))
    fi
done

if [ "$problems" -eq 0 ]; then
    echo "✅ Tous les templates sont à jour : versions cohérentes + zips = source. Les acheteurs recevront les MAJ."
    exit 0
fi
echo ""
echo "⚠ $problems template(s) désynchronisé(s) — corrige avant de pousser sur main (sinon les acheteurs ne reçoivent pas la MAJ)."
exit 1
