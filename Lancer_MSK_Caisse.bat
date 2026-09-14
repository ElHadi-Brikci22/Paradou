@echo off
title Lancement de MSK DRY PLUS - Caisse

:: Definition des chemins
set "BASE_DIR=%~dp0"
cd /d "%BASE_DIR%"

:: Recherche du binaire PHP (Laragon ou PATH systeme)
set "PHP_EXE=php.exe"
if exist "C:\laragon\bin\php" (
    for /d %%D in ("C:\laragon\bin\php\php-8*") do (
        if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
    )
)

:: Recherche du binaire MySQL
set "MYSQLD_EXE="
if exist "C:\laragon\bin\mysql" (
    for /d %%D in ("C:\laragon\bin\mysql\mysql-*") do (
        if exist "%%D\bin\mysqld.exe" set "MYSQLD_EXE=%%D\bin\mysqld.exe"
    )
)

:: 1. Demarrer MySQL s'il ne tourne pas deja
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    if defined MYSQLD_EXE (
        start "" /b "%MYSQLD_EXE%" --standalone
        timeout /t 2 /nobreak >nul
    )
)

:: 2. Verifier si le serveur PHP Artisan tourne deja sur le port 8000
netstat -ano | findstr :8000 | findstr LISTENING >nul
if "%ERRORLEVEL%"=="1" (
    start "" /b "%PHP_EXE%" artisan serve --host=127.0.0.1 --port=8000
    timeout /t 2 /nobreak >nul
)

:: 3. Lancer l'application en mode Kiosque / App avec impression directe automatique
if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" --app=http://127.0.0.1:8000 --start-maximized --kiosk-printing
    exit /b 0
)

if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" --app=http://127.0.0.1:8000 --start-maximized --kiosk-printing
    exit /b 0
)

if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" --app=http://127.0.0.1:8000 --start-maximized --kiosk-printing
    exit /b 0
)

if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" --app=http://127.0.0.1:8000 --start-maximized --kiosk-printing
    exit /b 0
)

start http://127.0.0.1:8000
exit /b 0
