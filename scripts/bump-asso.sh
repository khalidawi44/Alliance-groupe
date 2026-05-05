#!/usr/bin/env bash
# bump-asso.sh — automatise le bump de version + rebuild zip + json
# du theme et/ou plugin association, pour que l'auto-update prod
# fonctionne immediatement sans oubli.
#
# Usage :
#   bash scripts/bump-asso.sh theme 0.33.0
#   bash scripts/bump-asso.sh plugin 0.18.0
#   bash scripts/bump-asso.sh both 0.33.0 0.18.0

set -e
ROOT="$(git rev-parse --show-toplevel)"
DL="$ROOT/alliance-groupe-theme/assets/downloads"

bump_theme() {
    local NEW="$1"
    [[ -z "$NEW" ]] && { echo "Usage: bump-asso.sh theme <new_version>"; exit 1; }
    local STYLE="$DL/ag-starter-association/style.css"
    local JSON="$DL/ag-starter-association.json"
    sed -i "s/^Version: .*/Version: $NEW/" "$STYLE"
    sed -i "s/\"version\": \".*\"/\"version\": \"$NEW\"/" "$JSON"
    sed -i "s/\"last_updated\": \".*\"/\"last_updated\": \"$(date +%Y-%m-%d)\"/" "$JSON"
    rm -f "$DL/ag-starter-association.zip"
    cd "$DL" && zip -rq ag-starter-association.zip ag-starter-association -x "*.DS_Store" "*.git*"
    cd "$ROOT"
    echo "✓ Theme bumpé en $NEW"
}

bump_plugin() {
    local NEW="$1"
    [[ -z "$NEW" ]] && { echo "Usage: bump-asso.sh plugin <new_version>"; exit 1; }
    local MAIN="$DL/ag-fidelite-association/ag-fidelite-association.php"
    local JSON="$DL/ag-fidelite-association.json"
    sed -i "s/Version:           .*/Version:           $NEW/" "$MAIN"
    sed -i "s/AG_FID_VERSION', '.*'/AG_FID_VERSION', '$NEW'/" "$MAIN"
    sed -i "s/\"version\": \".*\"/\"version\": \"$NEW\"/" "$JSON"
    sed -i "s/\"last_updated\": \".*\"/\"last_updated\": \"$(date +%Y-%m-%d)\"/" "$JSON"
    rm -f "$DL/ag-fidelite-association.zip"
    cd "$DL" && zip -rq ag-fidelite-association.zip ag-fidelite-association -x "*.DS_Store" "*.git*"
    cd "$ROOT"
    echo "✓ Plugin bumpé en $NEW"
}

case "$1" in
    theme)  bump_theme  "$2" ;;
    plugin) bump_plugin "$2" ;;
    both)   bump_theme  "$2"; bump_plugin "$3" ;;
    *)      echo "Usage: $0 {theme|plugin|both} <version> [version_plugin]"; exit 1 ;;
esac

echo "→ Pense à : git add -A && git commit && git push origin <branch>"
