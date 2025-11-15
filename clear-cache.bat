@echo off
echo Clearing Laravel Cache...
echo.

cd /d %~dp0

echo [1/4] Clearing route cache...
php artisan route:clear

echo [2/4] Clearing config cache...
php artisan config:clear

echo [3/4] Clearing view cache...
php artisan view:clear

echo [4/4] Clearing application cache...
php artisan cache:clear

echo.
echo ========================================
echo All caches cleared successfully!
echo ========================================
echo.
pause
