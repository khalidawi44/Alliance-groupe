@echo off
cd /d "%~dp0"
echo === %DATE% %TIME% === >> auto-pull.log

REM --- Securite : ne tirer QUE depuis le depot de confiance ---
REM (si le remote 'origin' a ete detourne, on annule le pull).
for /f "delims=" %%u in ('"C:\Program Files\Git\cmd\git.exe" remote get-url origin') do set ORIGIN_URL=%%u
echo origin=%ORIGIN_URL% >> auto-pull.log
echo %ORIGIN_URL% | findstr /I "khalidawi44/Alliance-groupe" >nul
if errorlevel 1 (
	echo ABORT : remote origin inattendu, pull annule >> auto-pull.log
	echo. >> auto-pull.log
	exit /b 1
)

REM --ff-only : refuse une histoire divergente (pas de merge silencieux).
"C:\Program Files\Git\cmd\git.exe" pull --ff-only origin main >> auto-pull.log 2>&1
echo. >> auto-pull.log
exit
