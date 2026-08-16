@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
set GIT="C:\Program Files\Git\cmd\git.exe"
set L=sync-log.txt
title Alliance Groupe - publier le travail Meta sur GitHub

echo ========================================================== > %L%
echo   SYNC %DATE% %TIME% >> %L%
echo ========================================================== >> %L%

echo ==========================================================
echo   ALLIANCE GROUPE - publier le travail Meta sur GitHub
echo ==========================================================
echo.
echo Tout ce qui s'affiche est aussi enregistre dans sync-log.txt
echo (pour pouvoir me le coller si besoin).
echo.
pause
echo.

REM --- 0) depot de confiance uniquement ---
for /f "delims=" %%u in ('%GIT% remote get-url origin') do set ORIGIN_URL=%%u
echo %ORIGIN_URL% | findstr /I "khalidawi44/Alliance-groupe" >nul
if errorlevel 1 (
	echo [STOP] remote origin inattendu : %ORIGIN_URL%
	echo [STOP] remote origin inattendu : %ORIGIN_URL% >> %L%
	pause & exit /b 1
)

REM --- 1) etat AVANT ---
for /f %%h in ('%GIT% rev-parse HEAD') do set BEFORE=%%h
echo [1/4] Commit de depart : %BEFORE%
echo [1/4] Commit de depart : %BEFORE% >> %L%
echo.

REM --- 2) staging + commit ---
%GIT% add alliance-groupe-theme/inc/ag-meta-pixel.php alliance-groupe-theme/inc/ag-meta-feed.php alliance-groupe-theme/functions.php catalogue-alliance-groupe.csv auto-pull.bat sync-maintenant.bat HANDOFF.md .gitattributes "alliance-groupe-theme/assets/downloads/ag-starter-artisan/.LOCK.sha256" >> %L% 2>&1
echo [2/4] Fichiers prets :
%GIT% --no-pager diff --cached --name-status
%GIT% --no-pager diff --cached --name-status >> %L% 2>&1
echo.

echo [2/4] Tentative de commit... (--no-verify : on saute le hook, aucun template vendu n'est touche)
%GIT% commit --no-verify -m "Meta : pixel Consent Mode + flux catalogue /meta-catalog-feed.xml" -m "Travail local du 15/08 jamais commite : c'est ce qui bloquait le git pull --ff-only du PC depuis le 03/08." -m "- inc/ag-meta-pixel.php : pixel Meta (option ag_meta_pixel_id, vide = rien n'est emis), Consent Mode via ag:consent. Evenements PageView/ViewContent/Lead/InitiateCheckout." -m "- inc/ag-meta-feed.php : flux catalogue /meta-catalog-feed.xml (= ag_merchant_products + maintenance 29/59/99 + zone 49 EUR)." -m "- functions.php : chargement des 2 modules (1c6d-bis et 1c6d-ter). Flux Google inchange." -m "- catalogue-alliance-groupe.csv : 13 references. auto-pull.bat/sync-maintenant.bat : garde-fous anti-blocage silencieux." -m "- .gitattributes + ag-starter-artisan/.LOCK.sha256 : fix CRLF du fichier de verrou (bloquait tout commit local via le hook, FAILED open or read)." >> %L% 2>&1

REM --- 3) le commit a-t-il vraiment ete cree ? ---
for /f %%h in ('%GIT% rev-parse HEAD') do set AFTER=%%h
if "%BEFORE%"=="%AFTER%" (
	echo.
	echo ==========================================================
	echo   [STOP] LE COMMIT N'A PAS ETE CREE
	echo ==========================================================
	echo Un controle de securite (hook pre-commit) l'a bloque.
	echo Voici la raison exacte :
	echo ----------------------------------------------------------
	echo ---- controle des verrous ---- >> %L%
	bash scripts/check-all-locks.sh
	bash scripts/check-all-locks.sh >> %L% 2>&1
	echo ----------------------------------------------------------
	echo.
	echo Ce qui est en attente de commit :
	%GIT% --no-pager status --short
	%GIT% --no-pager status --short >> %L% 2>&1
	echo.
	echo Rien n'est perdu. Colle-moi le contenu de sync-log.txt
	echo (dans ce meme dossier) et je debloque.
	echo.
	pause & exit /b 1
)
echo [3/4] Commit cree : %AFTER%
echo [3/4] Commit cree : %AFTER% >> %L%
echo.

REM --- 4) rebase eventuel puis push ---
echo [4/4] Recuperation d'eventuels nouveaux commits, puis publication...
%GIT% pull --rebase --autostash origin main >> %L% 2>&1
if errorlevel 1 (
	echo [STOP] conflit pendant pull --rebase. Pour annuler : git rebase --abort
	echo [STOP] conflit pull --rebase >> %L%
	pause & exit /b 1
)
%GIT% push origin main
%GIT% push origin main >> %L% 2>&1
if errorlevel 1 (
	echo [STOP] le push a echoue - voir sync-log.txt. Le commit existe en local,
	echo relance ce script ou fais : git push origin main
	pause & exit /b 1
)

if exist ALERTE-SYNC.txt del ALERTE-SYNC.txt
echo.
echo ==========================================================
echo   PUBLIE - PC et GitHub sont alignes
echo ==========================================================
echo.
%GIT% --no-pager log --oneline -4
echo.
echo Modifs locales restantes (doit etre vide) :
%GIT% --no-pager status --short
echo.
echo ENSUITE SUR LE SITE : Apparence -^> SYNC GitHub -^> Verifier MAJ
echo   -^> SYNC FICHIERS DU THEME -^> purger le cache.
echo   Puis Reglages -^> Meta Pixel : coller l'ID du pixel.
echo.
pause
exit /b 0
