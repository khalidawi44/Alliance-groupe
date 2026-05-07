#!/usr/bin/env bash
# bump-template.sh — automatise le bump de version + rebuild zip + json
# de n'importe quel template Alliance (theme ou plugin), pour que
# l'auto-update prod fonctionne immediatement sans oubli.
#
# Usage :
#   bash scripts/bump-template.sh <slug> <new_version>
#   bash scripts/bump-template.sh ag-starter-barber 1.0.1
#   bash scripts/bump-template.sh ag-fidelite-association 0.25.0
#   bash scripts/bump-template.sh all-themes 1.0.1     # bump TOUS les themes
#   bash scripts/bump-template.sh all-plugins 0.2.0    # bump TOUS les plugins
#
# Detecte automatiquement si c'est un theme (style.css) ou plugin (.php principal).

set -e
ROOT="$(git rev-parse --show-toplevel)"
DL="$ROOT/alliance-groupe-theme/assets/downloads"
TODAY="$(date +%Y-%m-%d)"

THEMES=(
    ag-starter-barber
    ag-starter-association
    ag-starter-avocat
    ag-starter-restaurant
    ag-starter-artisan
    ag-starter-coach
)
PLUGINS=(
    ag-fidelite-association
    ag-premium-barber
    ag-business-barber
)

bump_theme() {
    local SLUG="$1"
    local NEW="$2"
    local STYLE="$DL/$SLUG/style.css"
    local JSON="$DL/$SLUG.json"
    [[ ! -f "$STYLE" ]] && { echo "✗ Manque : $STYLE"; return 1; }
    [[ ! -f "$JSON"  ]] && { echo "✗ Manque : $JSON";  return 1; }
    sed -i "s/^Version: .*/Version: $NEW/" "$STYLE"
    sed -i "s/\"version\": \".*\"/\"version\": \"$NEW\"/" "$JSON"
    sed -i "s/\"last_updated\": \".*\"/\"last_updated\": \"$TODAY\"/" "$JSON"
    rm -f "$DL/$SLUG.zip"
    cd "$DL" && zip -rq "$SLUG.zip" "$SLUG" -x "*.DS_Store" "*.git*"
    cd "$ROOT"
    echo "✓ Theme $SLUG bumpé en $NEW"
}

bump_plugin() {
    local SLUG="$1"
    local NEW="$2"
    local MAIN="$DL/$SLUG/$SLUG.php"
    local JSON="$DL/$SLUG.json"
    [[ ! -f "$MAIN" ]] && { echo "✗ Manque : $MAIN"; return 1; }
    [[ ! -f "$JSON" ]] && { echo "✗ Manque : $JSON"; return 1; }
    # Bump le header WP (Version: X.Y.Z avec espaces variables)
    sed -i -E "s/^([[:space:]]*\*?[[:space:]]*Version:[[:space:]]+).*/\1$NEW/" "$MAIN"
    # Bump les define VERSION (toutes les variantes : AG_FID_VERSION, AG_PREMIUM_BARBER_VERSION, etc.)
    sed -i -E "s/(define\([[:space:]]*'[A-Z_]+_VERSION'[[:space:]]*,[[:space:]]*')[^']+(')/\1$NEW\2/" "$MAIN"
    sed -i "s/\"version\": \".*\"/\"version\": \"$NEW\"/" "$JSON"
    sed -i "s/\"last_updated\": \".*\"/\"last_updated\": \"$TODAY\"/" "$JSON"
    rm -f "$DL/$SLUG.zip"
    cd "$DL" && zip -rq "$SLUG.zip" "$SLUG" -x "*.DS_Store" "*.git*"
    cd "$ROOT"
    echo "✓ Plugin $SLUG bumpé en $NEW"
}

bump_one() {
    local SLUG="$1"
    local NEW="$2"
    if [[ -f "$DL/$SLUG/style.css" ]]; then
        bump_theme "$SLUG" "$NEW"
    elif [[ -f "$DL/$SLUG/$SLUG.php" ]]; then
        bump_plugin "$SLUG" "$NEW"
    else
        echo "✗ Template inconnu : $SLUG (ni theme ni plugin detecte)"
        exit 1
    fi
}

case "$1" in
    all-themes)
        [[ -z "$2" ]] && { echo "Usage: $0 all-themes <version>"; exit 1; }
        for s in "${THEMES[@]}"; do bump_theme "$s" "$2"; done
        ;;
    all-plugins)
        [[ -z "$2" ]] && { echo "Usage: $0 all-plugins <version>"; exit 1; }
        for s in "${PLUGINS[@]}"; do bump_plugin "$s" "$2"; done
        ;;
    "")
        echo "Usage:"
        echo "  $0 <slug> <new_version>           # bump un seul template"
        echo "  $0 all-themes <new_version>       # bump tous les themes"
        echo "  $0 all-plugins <new_version>      # bump tous les plugins"
        echo ""
        echo "Themes  : ${THEMES[*]}"
        echo "Plugins : ${PLUGINS[*]}"
        exit 1
        ;;
    *)
        [[ -z "$2" ]] && { echo "Usage: $0 $1 <new_version>"; exit 1; }
        bump_one "$1" "$2"
        ;;
esac

echo "→ Pense à : git add -A && git commit && git push origin <branch>"
