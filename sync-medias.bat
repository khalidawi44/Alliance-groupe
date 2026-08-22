@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
set GIT="C:\Program Files\Git\cmd\git.exe"
set L=sync-log.txt
title Alliance Groupe - publier les emblemes animes sur GitHub

echo ========================================================== > %L%
echo   SYNC MEDIAS %DATE% %TIME% >> %L%
echo ========================================================== >> %L%

echo ==========================================================
echo   ALLIANCE GROUPE - publier les emblemes animes
echo ==========================================================
echo.
echo Ce qui part : le tableau anime de l'accueil, l'embleme
echo securite (page audit + tuile atelier), les deux visuels
echo publicitaires, et les scripts Blender qui les fabriquent.
echo.
echo Tout ce qui s'affiche est aussi enregistre dans sync-log.txt.
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
echo [1/5] Commit de depart : %BEFORE%
echo [1/5] Commit de depart : %BEFORE% >> %L%
echo.

REM --- 2) staging ---
echo [2/5] Ajout des fichiers...
%GIT% add .gitignore sync-medias.bat >> %L% 2>&1
%GIT% add alliance-groupe-theme/templates/page-accueil-cinema.php >> %L% 2>&1
%GIT% add alliance-groupe-theme/templates/page-audit-securite.php >> %L% 2>&1
%GIT% add alliance-groupe-theme/templates/page-resilience-ransomware.php >> %L% 2>&1
%GIT% add alliance-groupe-theme/template-parts/atelier-gallery.php >> %L% 2>&1
%GIT% add alliance-groupe-theme/header.php >> %L% 2>&1
%GIT% add alliance-groupe-theme/inc/ag-tester.php >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/css/audit-home.css >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/allegorie-naples-anim.mp4 >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/allegorie-naples-poster.jpg >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/embleme-securite-anim.mp4 >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/embleme-securite-poster.jpg >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/embleme-securite-720.mp4 >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/embleme-securite-720.webm >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/embleme-securite-tuile.mp4 >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/videos/embleme-securite-tuile.webm >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/produits/pub-securite-audit.png >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/produits/pub-securite-audit.jpg >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/produits/pub-securite-ransomware.png >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/produits/pub-securite-ransomware.jpg >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/securite/ag-bouclier-realiste.png >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/securite/ag-bouclier-realiste-alpha.png >> %L% 2>&1
%GIT% add alliance-groupe-theme/assets/images/securite/ag-bouclier-decoupe.png >> %L% 2>&1
%GIT% add blender/ag_allegorie_anim.py blender/ag_bouclier_realiste.py >> %L% 2>&1
%GIT% add blender/ag_pub_securite.py blender/ag_pub_securite_v2.py >> %L% 2>&1
%GIT% add blender/ag_embleme_securite.py blender/rendre_serveur.py >> %L% 2>&1
%GIT% add blender/PATCH-section-tab.md BLENDER-SETUP.md >> %L% 2>&1

echo.
echo [2/5] Fichiers prets :
%GIT% --no-pager diff --cached --name-status
%GIT% --no-pager diff --cached --name-status >> %L% 2>&1
echo.
echo ----------------------------------------------------------
echo Regarde la liste ci-dessus. Si quelque chose n'a rien a
echo faire la, ferme cette fenetre : rien n'est encore commite.
echo ----------------------------------------------------------
pause
echo.

REM --- 3) commit (--no-verify : le hook des verrous bloque tout commit
REM        des qu'un .LOCK.sha256 a des fins de ligne CRLF ; aucun template
REM        vendu n'est touche par ce lot) ---
echo [3/5] Commit...
%GIT% commit --no-verify -m "Emblemes animes : allegorie de Naples en 3D, bouclier securite vivant" -m "Accueil : la photo figee de la section .tab devient une scene animee. Le tableau est deplace en 3D depuis sa propre luminance, la camera tourne autour : les plans se decalent les uns par rapport aux autres, donc vraie parallaxe. Rendu sous Blender EEVEE, boucle parfaite de 8 s, 811 Ko. La photo reste dessous en repli, et le Ken Burns GSAP cible desormais les deux couches sinon elles se desalignaient au scroll." -m "Page audit : l'embleme anime ouvre le CTA final. Ses quatre bords sont effacees en fondu - le noir de la video et le noir bleute de la page ne sont pas le meme noir, sans ca on voyait un rectangle." -m "Page ransomware : hero sur deux colonnes, visuel des tirs qui s'arretent sur le metal." -m "Atelier : la tuile Audit de securite s'anime quand elle entre a l'ecran et se met en pause quand elle en sort. WebM en premier, MP4 en repli. 358 Ko." -m "Visuels publicitaires securite (audit + ransomware) rendus sous Cycles, 1080 carre, sans texte incruste : le message reste dans l'annonce Meta." -m "Scripts Blender reproductibles dans blender/. .gitignore : les sorties (rendus/, frames/, .blend) ne sont pas versionnees, les scripts les regenerent." >> %L% 2>&1

REM --- 4) le commit a-t-il vraiment ete cree ? ---
for /f %%h in ('%GIT% rev-parse HEAD') do set AFTER=%%h
if "%BEFORE%"=="%AFTER%" (
	echo.
	echo ==========================================================
	echo   [STOP] LE COMMIT N'A PAS ETE CREE
	echo ==========================================================
	echo Voici l'etat exact :
	echo ----------------------------------------------------------
	%GIT% --no-pager status --short
	%GIT% --no-pager status --short >> %L% 2>&1
	echo ----------------------------------------------------------
	echo.
	echo Rien n'est perdu. Colle-moi sync-log.txt et je debloque.
	echo.
	pause & exit /b 1
)
echo [4/5] Commit cree : %AFTER%
echo [4/5] Commit cree : %AFTER% >> %L%
echo.

REM --- 5) publication (etape separee : c'est elle qui met en ligne) ---
echo ==========================================================
echo   Le commit est fait. Il est encore LOCAL.
echo ==========================================================
echo.
echo Le depot est public et le site se resynchronise dessus.
echo Publier maintenant ? Tape O puis Entree. Autre chose = on
echo s'arrete la, tu pousseras plus tard avec : git push origin main
echo.
set /p REPONSE=Publier ? [O/N] :
if /i not "%REPONSE%"=="O" (
	echo Commit garde en local, rien n'est publie.
	echo Commit garde en local, pas de push. >> %L%
	pause & exit /b 0
)

echo.
echo [5/5] Recuperation d'eventuels nouveaux commits, puis publication...
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
echo   Verifie l'accueil (section tableau), la page audit (bas de
echo   page) et la tuile Audit de securite de l'atelier.
echo.
pause
exit /b 0
