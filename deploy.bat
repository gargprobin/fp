@echo off
echo ===================================================
echo  🚀 FP Project Auto-Deployment & Git Sync Script
echo ===================================================
echo.

echo 🔍 Staging changes...
git add .

set /p commit_msg="💬 Enter commit message (or press Enter for default): "
if "%commit_msg%"=="" (
    set commit_msg="updates and bug fixes"
)

echo.
echo 💾 Committing changes...
git commit -m "%commit_msg%"

echo.
echo 🚀 Pushing to GitHub (main)...
git push origin main

echo.
echo ===================================================
echo  ✅ Code committed and pushed successfully!
echo ===================================================
pause
