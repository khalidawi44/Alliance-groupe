#!/bin/bash
# ═══════════════════════════════════════════════════════════════════
# Alliance Groupe — Script de déploiement en 1 clic
# ═══════════════════════════════════════════════════════════════════
#
# UTILISATION :
#   ./deploy.sh
#
# CE QUE FAIT CE SCRIPT :
#   1. Se connecte à ton serveur o2switch via FTP
#   2. Upload TOUT le dossier alliance-groupe-theme/
#   3. Remplace les fichiers existants sur le serveur
#
# PRÉREQUIS :
#   - lftp installé (sudo apt install lftp / brew install lftp)
#   - OU tu peux utiliser le .bat pour Windows (voir deploy.bat)
#
# ═══════════════════════════════════════════════════════════════════

# ── CONFIGURATION — MODIFIE CES VALEURS ──────────────────────────
FTP_HOST="ftp.ton-serveur.o2switch.net"      # ← Ton serveur FTP o2switch
FTP_USER="ton-identifiant-ftp"                # ← Ton identifiant FTP
FTP_PASS="ton-mot-de-passe-ftp"              # ← Ton mot de passe FTP
FTP_PATH="/www/wp-content/themes"            # ← Chemin vers le dossier themes
# ─────────────────────────────────────────────────────────────────

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
THEME_DIR="$SCRIPT_DIR/../alliance-groupe-theme"

echo ""
echo "═══════════════════════════════════════════════════"
echo "  Alliance Groupe — Deploiement en 1 clic"
echo "═══════════════════════════════════════════════════"
echo ""

# Vérifier que le dossier du thème existe
if [ ! -d "$THEME_DIR" ]; then
    echo -e "${RED}ERREUR : Dossier du theme introuvable : $THEME_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}Dossier source :${NC} $THEME_DIR"
echo -e "${YELLOW}Serveur FTP    :${NC} $FTP_HOST"
echo -e "${YELLOW}Destination    :${NC} $FTP_PATH/alliance-groupe-theme/"
echo ""

# Compter les fichiers
FILE_COUNT=$(find "$THEME_DIR" -type f | wc -l)
echo -e "${GREEN}$FILE_COUNT fichiers a deployer${NC}"
echo ""

# Confirmation
read -p "Lancer le deploiement ? (o/N) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Oo]$ ]]; then
    echo "Deploiement annule."
    exit 0
fi

echo ""
echo "Deploiement en cours..."
echo ""

# Vérifier si lftp est installé
if command -v lftp &> /dev/null; then
    lftp -e "
        set ssl:verify-certificate no;
        set ftp:ssl-allow yes;
        open ftp://$FTP_USER:$FTP_PASS@$FTP_HOST;
        mirror --reverse --delete --verbose \
            $THEME_DIR \
            $FTP_PATH/alliance-groupe-theme/;
        quit
    "
    STATUS=$?
elif command -v ncftpput &> /dev/null; then
    ncftpput -R -v -u "$FTP_USER" -p "$FTP_PASS" \
        "$FTP_HOST" "$FTP_PATH/" "$THEME_DIR"
    STATUS=$?
else
    echo -e "${RED}ERREUR : Aucun client FTP trouve.${NC}"
    echo "Installe lftp :"
    echo "  Ubuntu/Debian : sudo apt install lftp"
    echo "  macOS         : brew install lftp"
    echo ""
    echo "Ou utilise deploy.bat sur Windows avec WinSCP."
    exit 1
fi

echo ""
if [ $STATUS -eq 0 ]; then
    echo "═══════════════════════════════════════════════════"
    echo -e "  ${GREEN}DEPLOIEMENT REUSSI !${NC}"
    echo "═══════════════════════════════════════════════════"
    echo ""
    echo "Ton theme est a jour sur : https://alliancegroupe-inc.com/"
    echo ""
else
    echo -e "${RED}ERREUR lors du deploiement (code: $STATUS)${NC}"
    echo "Verifie tes identifiants FTP et reessaie."
    exit 1
fi
