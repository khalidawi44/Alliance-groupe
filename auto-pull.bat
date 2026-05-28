@echo off
cd /d "%~dp0"
echo === %DATE% %TIME% === >> auto-pull.log
"C:\Program Files\Git\cmd\git.exe" pull origin main >> auto-pull.log 2>&1
echo. >> auto-pull.log
exit