@echo off
cd /d "%~dp0"
echo === %DATE% %TIME% === >> auto-pull.log
git pull origin main >> auto-pull.log 2>&1
echo. >> auto-pull.log
exit