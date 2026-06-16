#!/usr/bin/env bash
# wporg-clean.sh — produit un build « WordPress.org propre » d'un thème AG Starter.
#
# Usage : bash scripts/wporg-clean.sh <slug>
#   ex.  : bash scripts/wporg-clean.sh ag-starter-restaurant
#
# Retire AUTOMATIQUEMENT ce que WordPress.org interdit (commun à tous les thèmes) :
#   - updater GitHub (class-ag-updater.php, theme-updater.php) + leurs require
#   - client de licence phone-home (class-ag-licence-client.php) + son require
#   - filtre d'auto-MAJ forcée (ag_force_auto_updates)
#   - désactivation des commentaires + unregister des widgets/sidebars + remove_menu_page core
#   - .LOCK.sha256 / LOCKED.md (internes)
# Puis : php -l, zip, et un RAPPORT des points à vérifier À LA MAIN (CPT, crédit, marque WordPress).
#
# Le build sort dans wporg-builds/<slug>/ + wporg-builds/<slug>.zip
set -u
ROOT="$(git rev-parse --show-toplevel)"
SLUG="${1:-}"
[ -z "$SLUG" ] && { echo "Usage: bash scripts/wporg-clean.sh <slug>"; exit 1; }
SRC="$ROOT/alliance-groupe-theme/assets/downloads/$SLUG"
OUT="$ROOT/wporg-builds/$SLUG"
[ -d "$SRC" ] || { echo "✗ Thème introuvable : $SRC"; exit 1; }
[ -f "$SRC/style.css" ] || { echo "✗ $SLUG n'est pas un thème (pas de style.css)"; exit 1; }

mkdir -p "$ROOT/wporg-builds"
rm -rf "$OUT"; cp -r "$SRC" "$OUT"
cd "$OUT"

# 1) Fichiers interdits
rm -f inc/class-ag-updater.php inc/theme-updater.php inc/class-ag-licence-client.php .LOCK.sha256 LOCKED.md

# 2) Strip des blocs communs de functions.php
python3 - "$SLUG" <<'PY'
import re, sys
slug = sys.argv[1]
f = 'functions.php'
s = open(f, encoding='utf-8').read()

# requires interdits
for line in (
    "require get_template_directory() . '/inc/class-ag-licence-client.php';\n",
    "require get_template_directory() . '/inc/class-ag-updater.php';\n",
    "require_once get_template_directory() . '/inc/theme-updater.php';\n",
):
    s = s.replace(line, "")

# lignes d'instanciation licence/updater (gardent AG_Pro_Features)
s = re.sub(r"^\s*AG_Licence_Client::register_admin\(\);\n", "", s, flags=re.M)
s = re.sub(r"^\s*new AG_Theme_Updater\([^;]*\);\n", "", s, flags=re.M)

# bloc auto-update forcée (ag_force_auto_updates ... })
s = re.sub(r"\n?/\*[^*]*AG : mises à jour AUTOMATIQUES.*?\n\}\n", "\n", s, flags=re.S)

# désactivation commentaires (filtres + remove menu + admin bar)
s = re.sub(r"add_filter\(\s*'comments_open'[^\n]*\n", "", s)
s = re.sub(r"add_filter\(\s*'pings_open'[^\n]*\n", "", s)
s = re.sub(r"add_filter\(\s*'comments_array'[^\n]*\n", "", s)
s = re.sub(r"add_action\(\s*'admin_menu',\s*function\s*\(\)\s*\{\s*remove_menu_page\([^}]*\}\s*\);\n", "", s, flags=re.S)
s = re.sub(r"add_action\(\s*'admin_bar_menu',\s*function[^}]*remove_node\([^}]*\}\s*,\s*999\s*\);\n", "", s, flags=re.S)

# unregister widgets + remove widgets submenu
s = re.sub(r"add_action\(\s*'widgets_init',\s*function\s*\(\)\s*\{.*?unregister_widget.*?\}\s*,\s*99\s*\);\n", "", s, flags=re.S)
s = re.sub(r"add_action\(\s*'admin_menu',\s*function\s*\(\)\s*\{\s*remove_submenu_page\(\s*'themes\.php'[^}]*\}\s*,\s*99\s*\);\n", "", s, flags=re.S)

open(f, 'w', encoding='utf-8').write(s)
print("functions.php nettoyé")
PY

# 3) Lint
if ! php -l functions.php >/dev/null; then echo "✗ functions.php a une erreur PHP après strip — à corriger manuellement"; fi

# 4) Zip
cd "$ROOT/wporg-builds"
rm -f "$SLUG.zip"; zip -rq "$SLUG.zip" "$SLUG" -x "*.DS_Store" "*.git*"

# 5) Rapport des points à VÉRIFIER À LA MAIN
echo ""
echo "================ BUILD .ORG : $SLUG ================"
echo "✅ Généré : wporg-builds/$SLUG/ + $SLUG.zip"
echo ""
echo "⚠ À VÉRIFIER / TRAITER À LA MAIN (varie par thème) :"
cd "$OUT"
grep -rln "register_post_type\|register_taxonomy" . --include=*.php >/dev/null 2>&1 && echo "  • CPT/taxo dans le thème → déplacer dans le plugin Companion (interdit .org) :" && grep -rln "register_post_type\|register_taxonomy" . --include=*.php | sed 's/^/      /'
grep -rln "wp_remote_\|githubusercontent" . --include=*.php >/dev/null 2>&1 && echo "  • Appels externes restants :" && grep -rln "wp_remote_\|githubusercontent" . --include=*.php | sed 's/^/      /'
grep -niE "theme WordPress|thème WordPress" style.css readme.txt 2>/dev/null | head -3 | sed 's/^/  • Marque WordPress (marketing) à retirer : /'
echo "  • Crédit footer : 1 seul lien max, rel=\"nofollow\"."
echo "  • Lancer le plugin Theme Check en local → viser 0 ERROR."
echo "==================================================="
