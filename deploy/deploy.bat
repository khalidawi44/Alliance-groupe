@echo off
REM ═══════════════════════════════════════════════════════════════════
REM  Alliance Groupe — Deploiement Windows en 1 clic
REM ═══════════════════════════════════════════════════════════════════
REM
REM  UTILISATION : Double-cliquer sur ce fichier
REM
REM  PREREQUIS :
REM    - WinSCP installe (https://winscp.net/eng/download.php)
REM    - OU configurer les identifiants FTP ci-dessous
REM
REM ═══════════════════════════════════════════════════════════════════

REM ── CONFIGURATION — MODIFIE CES VALEURS ────────────────────────
set FTP_HOST=ftp.ton-serveur.o2switch.net
set FTP_USER=ton-identifiant-ftp
set FTP_PASS=ton-mot-de-passe-ftp
set FTP_PATH=/www/wp-content/themes
REM ───────────────────────────────────────────────────────────────

set THEME_DIR=%~dp0..\alliance-groupe-theme

echo.
echo ===================================================
echo   Alliance Groupe — Deploiement en 1 clic
echo ===================================================
echo.
echo Dossier source  : %THEME_DIR%
echo Serveur FTP     : %FTP_HOST%
echo Destination     : %FTP_PATH%/alliance-groupe-theme/
echo.

REM Verifier que le dossier existe
if not exist "%THEME_DIR%" (
    echo ERREUR : Dossier du theme introuvable !
    echo Chemin attendu : %THEME_DIR%
    pause
    exit /b 1
)

set /p CONFIRM="Lancer le deploiement ? (O/N) : "
if /i not "%CONFIRM%"=="O" (
    echo Deploiement annule.
    pause
    exit /b 0
)

echo.
echo Deploiement en cours...
echo.

REM Methode 1 : WinSCP (recommande)
where winscp.com >nul 2>nul
if %errorlevel% equ 0 (
    winscp.com /command ^
        "open ftp://%FTP_USER%:%FTP_PASS%@%FTP_HOST%/" ^
        "synchronize remote ""%THEME_DIR%"" ""%FTP_PATH%/alliance-groupe-theme/"" -delete" ^
        "exit"
    goto :check_result
)

REM Methode 2 : FTP natif Windows (basique mais fonctionne)
echo open %FTP_HOST%> "%TEMP%\ftp_deploy.txt"
echo %FTP_USER%>> "%TEMP%\ftp_deploy.txt"
echo %FTP_PASS%>> "%TEMP%\ftp_deploy.txt"
echo binary>> "%TEMP%\ftp_deploy.txt"
echo cd %FTP_PATH%>> "%TEMP%\ftp_deploy.txt"
echo mkdir alliance-groupe-theme>> "%TEMP%\ftp_deploy.txt"
echo cd alliance-groupe-theme>> "%TEMP%\ftp_deploy.txt"
echo prompt>> "%TEMP%\ftp_deploy.txt"
echo mput "%THEME_DIR%\*.*">> "%TEMP%\ftp_deploy.txt"
echo quit>> "%TEMP%\ftp_deploy.txt"

ftp -s:"%TEMP%\ftp_deploy.txt"
del "%TEMP%\ftp_deploy.txt"

:check_result
echo.
if %errorlevel% equ 0 (
    echo ===================================================
    echo   DEPLOIEMENT REUSSI !
    echo ===================================================
    echo.
    echo Theme a jour sur : https://alliancegroupe-inc.com/
) else (
    echo ERREUR lors du deploiement.
    echo Verifie tes identifiants FTP.
)

echo.
pause
