@echo off
REM =====================================================================
REM  Alliance Groupe - Mise a jour AUTOMATIQUE des outils depuis GitHub
REM  Double-clique ce fichier (ou laisse le planificateur le lancer).
REM  Il recupere la derniere version des outils pentest / du theme.
REM =====================================================================
cd /d "%~dp0"

REM Branche de travail ou vivent les outils (change ici si un jour on passe sur main)
set BRANCHE=claude/pentest-local-security-offers-KNehV

echo.
echo === Alliance Groupe : mise a jour depuis GitHub ===
echo Branche : %BRANCHE%
echo.

git fetch origin --prune
git checkout %BRANCHE% 2>nul
git merge --ff-only origin/%BRANCHE%

if %errorlevel% neq 0 (
  echo.
  echo [!] La mise a jour automatique n'a pas pu s'appliquer d'un coup.
  echo     Tu as peut-etre des modifications locales non enregistrees.
  echo     Previens Claude, ou lance :  git status
  echo.
) else (
  echo.
  echo [OK] Outils a jour. Recharge la page dans le navigateur avec Ctrl+Maj+R.
  echo.
)

REM Fermeture auto apres 3s (retire la ligne suivante pour garder la fenetre ouverte)
timeout /t 3 >nul
