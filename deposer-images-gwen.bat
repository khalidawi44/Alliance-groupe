@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
set GIT="C:\Program Files\Git\cmd\git.exe"
title Gwen Services - envoyer les images sur le site

echo ==========================================================
echo   GWEN SERVICES - envoi des images vers le site
echo ==========================================================
echo.

REM --- 0) depot de confiance uniquement ---
for /f "delims=" %%u in ('%GIT% remote get-url origin') do set ORIGIN_URL=%%u
echo %ORIGIN_URL% | findstr /I "khalidawi44/Alliance-groupe" >nul
if errorlevel 1 (
	echo [STOP] remote origin inattendu : %ORIGIN_URL%
	pause & exit /b 1
)

REM --- 1) y a-t-il des images dans gwen-inbox ? ---
set FOUND=0
for %%f in (gwen-inbox\*.jpg gwen-inbox\*.jpeg gwen-inbox\*.png gwen-inbox\*.webp gwen-inbox\*.heic gwen-inbox\*.JPG gwen-inbox\*.JPEG gwen-inbox\*.PNG) do set FOUND=1
if "%FOUND%"=="0" (
	echo Aucune image trouvee dans gwen-inbox\
	echo Depose d'abord tes images dans ce dossier, puis relance.
	pause & exit /b 0
)
echo Images trouvees dans gwen-inbox :
dir /b gwen-inbox | findstr /V /I "LISEZMOI"
echo.

REM --- 2) recuperer les dernieres modifs ---
echo [1/3] Recuperation des dernieres modifs (pull --rebase)...
%GIT% pull --rebase --autostash origin main
if errorlevel 1 (
	echo [STOP] conflit pendant pull --rebase. Pour annuler : git rebase --abort
	pause & exit /b 1
)

REM --- 3) commit + push (uniquement gwen-inbox, aucun template touche) ---
echo [2/3] Envoi des images...
%GIT% add gwen-inbox
%GIT% commit --no-verify -m "gwen-inbox : nouvelles images a traiter"
if errorlevel 1 (
	echo [STOP] rien a committer ou commit bloque.
	pause & exit /b 1
)
%GIT% push origin main
if errorlevel 1 (
	echo [STOP] le push a echoue. Relance ce script.
	pause & exit /b 1
)

echo.
echo ==========================================================
echo   ENVOYE ! Le robot GitHub prend le relais (1 a 2 min) :
echo   optimisation + integration au theme + release auto.
echo   Le site de Gwen sera a jour tout seul.
echo   Suivi : https://github.com/khalidawi44/Alliance-groupe/actions
echo ==========================================================
echo.
echo [3/3] Dans ~3 min, ce dossier gwen-inbox sera vide ici aussi
echo        au prochain pull (auto-pull ou relance de ce script).
echo.
pause
exit /b 0
