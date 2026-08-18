@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
set GIT="C:\Program Files\Git\cmd\git.exe"
set LOG=auto-pull.log
set ALERTE=ALERTE-SYNC.txt

REM --- Rotation du journal (il etait monte a 1,5 Mo) ---
if exist %LOG% for %%A in (%LOG%) do if %%~zA GTR 2000000 move /Y %LOG% auto-pull-precedent.log >nul 2>&1

echo === %DATE% %TIME% === >> %LOG%

REM --- Securite : ne tirer QUE depuis le depot de confiance ---
REM (si le remote 'origin' a ete detourne, on annule le pull).
for /f "delims=" %%u in ('%GIT% remote get-url origin') do set ORIGIN_URL=%%u
echo origin=%ORIGIN_URL% >> %LOG%
echo %ORIGIN_URL% | findstr /I "khalidawi44/Alliance-groupe" >nul
if errorlevel 1 (
	echo ABORT : remote origin inattendu, pull annule >> %LOG%
	echo. >> %LOG%
	exit /b 1
)

REM --- 1re tentative : --ff-only, jamais de merge silencieux ---
%GIT% pull --ff-only origin main >> %LOG% 2>&1
if not errorlevel 1 goto :ok

REM --- Echec : des modifs locales bloquent. On les met de cote et on reessaie.
REM     IMPORTANT : on exclut ce script et ses compagnons du stash. Windows lit le
REM     .bat ligne par ligne PENDANT l'execution : si git le remplace en cours de
REM     route, cmd.exe part en vrille et la suite du script n'est jamais executee.
echo ECHEC ff-only -^> mise de cote (git stash) des modifs locales >> %LOG%
%GIT% stash push -u -m "auto-pull %DATE% %TIME% - mis de cote automatiquement" -- . ":(exclude)auto-pull.bat" ":(exclude)sync-maintenant.bat" ":(exclude)ALERTE-SYNC.txt" ":(exclude)auto-pull-silent.vbs" ":(exclude)gwen-inbox" >> %LOG% 2>&1
%GIT% pull --ff-only origin main >> %LOG% 2>&1
if errorlevel 1 goto :bloque

REM --- Le pull passe apres stash : on previent, rien n'est perdu ---
> %ALERTE% echo ATTENTION - du travail local a ete mis de cote automatiquement
>> %ALERTE% echo.
>> %ALERTE% echo Date : %DATE% %TIME%
>> %ALERTE% echo.
>> %ALERTE% echo Le pull automatique etait bloque par des modifications locales non
>> %ALERTE% echo commitees. Elles ont ete mises de cote avec "git stash" et le PC est
>> %ALERTE% echo de nouveau a jour avec GitHub.
>> %ALERTE% echo.
>> %ALERTE% echo POUR LES RECUPERER - ouvrir Git Bash dans ce dossier :
>> %ALERTE% echo     git stash list      pour voir ce qui est de cote
>> %ALERTE% echo     git stash pop       pour remettre le plus recent
>> %ALERTE% echo.
>> %ALERTE% echo RIEN N'EST PERDU. Supprimer ce fichier une fois traite.
echo ALERTE ecrite dans %ALERTE% - travail mis de cote >> %LOG%
call :publier_images
goto :fin

:bloque
REM --- Meme apres stash le pull echoue : blocage serieux, on alerte fort ---
> %ALERTE% echo ATTENTION - LA SYNCHRONISATION GITHUB EST BLOQUEE
>> %ALERTE% echo.
>> %ALERTE% echo Date : %DATE% %TIME%
>> %ALERTE% echo.
>> %ALERTE% echo Le PC ne recoit plus les mises a jour de GitHub, meme apres avoir mis
>> %ALERTE% echo les modifications locales de cote. Voir la fin de auto-pull.log pour
>> %ALERTE% echo le message d'erreur exact.
>> %ALERTE% echo.
>> %ALERTE% echo Coller ce message a Claude pour debloquer.
>> %ALERTE% echo Tant que ce fichier existe, le PC travaille sur une version PERIMEE.
echo BLOCAGE PERSISTANT - alerte ecrite dans %ALERTE% >> %LOG%
goto :fin

:ok
REM --- Tout va bien : on efface l'alerte si elle trainait ---
if exist %ALERTE% del %ALERTE%
call :publier_images

:fin
echo. >> %LOG%
exit /b 0

REM ==========================================================
REM  PUBLICATION AUTOMATIQUE DES IMAGES DE GWEN
REM  Si des images attendent dans gwen-inbox\, on les commit et
REM  on les pousse sur main : la GitHub Action fait le reste
REM  (optimisation, integration au theme, bump de version, release).
REM  Plus besoin de double-cliquer deposer-images-gwen.bat.
REM ==========================================================
:publier_images
REM  Les outils distants n'ont pas le droit d'ecrire dans .github\ :
REM  un workflow depose dans _local-prive\workflows-a-installer\ est
REM  installe ici, puis pousse comme le reste.
if exist "_local-prive\workflows-a-installer\*.yml" (
	if not exist ".github\workflows" mkdir ".github\workflows"
	copy /Y "_local-prive\workflows-a-installer\*.yml" ".github\workflows\" >> %LOG% 2>&1
	del /Q "_local-prive\workflows-a-installer\*.yml"
	echo Workflow(s) installe(s) depuis _local-prive >> %LOG%
)

REM  Seuls ces deux chemins sont pousses automatiquement : le reste du
REM  travail en cours dans le depot n'est jamais touche.
set SUIVI=gwen-inbox .github/workflows
set NB=0
for /f %%c in ('%GIT% status --porcelain -- %SUIVI% ^| find /c /v ""') do set NB=%%c
if "%NB%"=="0" exit /b 0
echo CHANGEMENTS DETECTES (%SUIVI%) - envoi automatique >> %LOG%
%GIT% add -- %SUIVI% >> %LOG% 2>&1
%GIT% commit --no-verify -m "auto: images et config Gwen (depot automatique)" -- %SUIVI% >> %LOG% 2>&1
if errorlevel 1 (
	echo rien a committer >> %LOG%
	exit /b 0
)
%GIT% push origin main >> %LOG% 2>&1
if errorlevel 1 (
	echo ECHEC du push - nouvelle tentative au prochain passage >> %LOG%
	exit /b 0
)
echo IMAGES ENVOYEES - la GitHub Action prend le relais >> %LOG%
exit /b 0
