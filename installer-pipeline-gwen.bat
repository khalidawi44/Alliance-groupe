@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
set GIT="C:\Program Files\Git\cmd\git.exe"
title Gwen Services - installation du pipeline automatique

echo ==========================================================
echo   INSTALLATION DU PIPELINE AUTOMATIQUE IMAGES GWEN
echo ==========================================================
echo.
echo Ce script doit etre lance depuis la RACINE du depot
echo Alliance-groupe (la ou se trouve le dossier .git).
echo.
pause

REM --- 0) verifications ---
if not exist ".git" (
	echo [STOP] Pas de dossier .git ici. Place ce fichier a la
	echo racine de ton clone Alliance-groupe et relance.
	pause & exit /b 1
)
for /f "delims=" %%u in ('%GIT% remote get-url origin') do set ORIGIN_URL=%%u
echo %ORIGIN_URL% | findstr /I "khalidawi44/Alliance-groupe" >nul
if errorlevel 1 (
	echo [STOP] remote origin inattendu : %ORIGIN_URL%
	pause & exit /b 1
)
if not exist ".github\workflows\gwen-images.yml" (
	echo [STOP] Fichiers du pipeline introuvables. As-tu bien extrait
	echo TOUT le zip a la racine du depot ^(dossiers .github et
	echo gwen-inbox inclus^) ?
	pause & exit /b 1
)

REM --- 1) pull d'abord ---
echo [1/3] Recuperation des dernieres modifs...
%GIT% pull --rebase --autostash origin main
if errorlevel 1 (
	echo [STOP] conflit pendant pull --rebase. Pour annuler : git rebase --abort
	pause & exit /b 1
)

REM --- 2) commit des fichiers du pipeline ---
echo [2/3] Installation...
%GIT% add .github/workflows/gwen-images.yml gwen-inbox/LISEZMOI.txt deposer-images-gwen.bat installer-pipeline-gwen.bat
%GIT% commit --no-verify -m "feat: pipeline automatique images Gwen (gwen-inbox + GitHub Action)"
if errorlevel 1 (
	echo [STOP] rien a committer ou commit bloque.
	pause & exit /b 1
)

REM --- 3) push ---
echo [3/3] Publication sur GitHub...
%GIT% push origin main
if errorlevel 1 (
	echo [STOP] le push a echoue. Si le message parle de "workflow scope",
	echo dis-le a Claude : il faut re-autoriser Git sur GitHub.
	pause & exit /b 1
)

echo.
echo ==========================================================
echo   PIPELINE INSTALLE !
echo ==========================================================
echo.
echo Desormais, pour mettre des images sur le site de Gwen :
echo   1. Depose tes images dans le dossier gwen-inbox\
echo   2. Double-clique deposer-images-gwen.bat
echo   3. C'est tout. Le robot GitHub fait le reste (2 min).
echo.
pause
exit /b 0
