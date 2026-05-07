#!/usr/bin/env bash
# release.sh — workflow complet de release : bump + commit + push + merge main + push main.
# GARANTIE que l'auto-update marche toujours, parce que les JSON sur main
# refletent TOUJOURS la derniere version pushee.
#
# Usage :
#   bash scripts/release.sh ag-starter-association 0.40.0
#   bash scripts/release.sh ag-fidelite-association 0.29.0
#   bash scripts/release.sh ag-starter-barber 1.0.2
#
#   bash scripts/release.sh both 0.40.0 0.29.0   # association theme+plugin en une commande
#
# Si tu es sur une branche autre que main, ce script :
#   1. Bumpe + commit + push sur la branche courante
#   2. Switch sur main, merge la branche courante (avec resolution auto des conflits via --ours)
#   3. Push main
#   4. Reviens sur la branche d'origine
# Si tu es sur main, juste bump + commit + push direct.

set -e
ROOT="$(git rev-parse --show-toplevel)"
cd "$ROOT"
ORIGIN_BRANCH="$(git branch --show-current)"

# Verifie travail propre avant de commencer
if [[ -n "$(git status --porcelain)" ]]; then
    echo "✗ Working tree non propre. Commit ou stash d'abord :"
    git status --short
    exit 1
fi

bump_and_commit() {
    local SLUG="$1"
    local NEW="$2"
    bash "$ROOT/scripts/bump-template.sh" "$SLUG" "$NEW"
    git add -A "alliance-groupe-theme/assets/downloads/$SLUG"* 2>/dev/null || git add -A
}

case "$1" in
    both)
        # both <theme_version> <plugin_version> : bumpe association theme+plugin
        [[ -z "$2" || -z "$3" ]] && { echo "Usage: $0 both <theme_version> <plugin_version>"; exit 1; }
        bash "$ROOT/scripts/bump-template.sh" ag-starter-association "$2"
        bash "$ROOT/scripts/bump-template.sh" ag-fidelite-association "$3"
        git add -A
        git commit -m "release: ag-starter-association v$2 + ag-fidelite-association v$3"
        ;;
    "")
        echo "Usage:"
        echo "  $0 <slug> <version>                  # release un seul template"
        echo "  $0 both <theme_v> <plugin_v>         # release association theme+plugin"
        exit 1
        ;;
    *)
        SLUG="$1"; NEW="$2"
        [[ -z "$NEW" ]] && { echo "Usage: $0 $SLUG <version>"; exit 1; }
        bash "$ROOT/scripts/bump-template.sh" "$SLUG" "$NEW"
        git add -A
        git commit -m "release: $SLUG v$NEW"
        ;;
esac

# Push branche courante
echo ""
echo "→ Push branche courante : $ORIGIN_BRANCH"
git push -u origin "$ORIGIN_BRANCH"

# Si on n'est pas deja sur main, merge to main pour que l'auto-update voie la nouvelle version
if [[ "$ORIGIN_BRANCH" != "main" ]]; then
    echo ""
    echo "→ Merge → main (les URL d'auto-update pointent sur main)"
    git fetch origin main
    git checkout main
    git pull origin main --ff-only

    if ! git merge "$ORIGIN_BRANCH" --no-edit; then
        # Conflits : on garde notre version (la plus recente)
        echo "⚠ Conflits detectes, resolution auto : on garde notre version (--ours)"
        CONFLICTS="$(git diff --name-only --diff-filter=U)"
        if [[ -n "$CONFLICTS" ]]; then
            git checkout --ours $CONFLICTS
            git add -A
            git commit --no-edit
        fi
    fi

    git push origin main
    git checkout "$ORIGIN_BRANCH"
    echo "✓ Retour sur $ORIGIN_BRANCH"
fi

echo ""
echo "════════════════════════════════════════════════════════"
echo "✓ RELEASE COMPLETE — auto-update operationnel sous 1h"
echo "════════════════════════════════════════════════════════"
echo ""
echo "Force-check immediat cote client (sans attendre 1h) :"
echo "  Theme  : wp-admin/themes.php?ag_<slug>_check_theme=1"
echo "  Plugin : wp-admin/plugins.php?ag_<slug>_check_update=1"
