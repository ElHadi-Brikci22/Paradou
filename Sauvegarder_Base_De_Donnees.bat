@echo off
chcp 65001 >nul
title Sauvegarde de la Base de Donnees - Paradou

set "BASE_DIR=%~dp0"
cd /d "%BASE_DIR%"

echo ========================================================
echo        PARADOU - SAUVEGARDE DE LA BASE DE DONNEES
echo ========================================================
echo.

rem 1. Chercher PHP (dans PATH ou dans Laragon)
set "PHP_BIN=php"
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    if exist "C:\laragon\bin\php" (
        for /d %%P in ("C:\laragon\bin\php\php-*") do (
            if exist "%%P\php.exe" set "PHP_BIN=%%P\php.exe"
        )
    )
)

rem 2. Si PHP est disponible et artisan existe, executer la commande Laravel (qui utilise l'emplacement configure dans l'application)
if exist "artisan" (
    echo [INFO] Lancement de la sauvegarde avec l'emplacement configure dans l'application...
    echo.
    "%PHP_BIN%" artisan paradou:backup
    if %ERRORLEVEL% equ 0 (
        goto FIN
    )
    echo [ATTENTION] La commande artisan a echoue, bascule vers la sauvegarde directe mysqldump...
    echo.
)

rem 3. Methode de secours directe mysqldump + PowerShell pour la date
echo [INFO] Recherche de l'emplacement de sauvegarde configure...
set "BACKUP_DIR=%BASE_DIR%sauvegardes"

rem Verifier si un dossier personnalise est configure dans storage\app\backup_settings.json
if exist "storage\app\backup_settings.json" (
    for /f "usebackq delims=" %%D in (`powershell -NoProfile -Command "$cfg = Get-Content -Raw 'storage\app\backup_settings.json' | ConvertFrom-Json; if ($cfg.backup_directory) { Write-Output $cfg.backup_directory }"`) do (
        if not "%%D"=="" set "BACKUP_DIR=%%D"
    )
)

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

rem Obtenir la date sans dependre de wmic (qui est supprime dans Windows 11)
for /f "usebackq" %%a in (`powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HH\hmm\sss"`) do set "DATE_STR=%%a"
if "%DATE_STR%"=="" set "DATE_STR=%date:~6,4%-%date:~3,2%-%date:~0,2%_%time:~0,2%h%time:~3,2%"

set "BACKUP_FILE=%BACKUP_DIR%\paradou_backup_%DATE_STR%.sql"

rem Trouver mysqldump
set "MYSQLDUMP_EXE=mysqldump.exe"
if exist "C:\laragon\bin\mysql" (
    for /d %%D in ("C:\laragon\bin\mysql\mysql-*") do (
        if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP_EXE=%%D\bin\mysqldump.exe"
    )
)

echo [INFO] Sauvegarde directe en cours vers :
echo %BACKUP_FILE%
echo.

"%MYSQLDUMP_EXE%" -h 127.0.0.1 -P 3306 -u root --databases msk_dry_plus > "%BACKUP_FILE%" 2>&1

if %ERRORLEVEL% equ 0 (
    echo ========================================================
    echo   [SUCCES] Base de donnees sauvegardee avec succes !
    echo   Fichier : %BACKUP_FILE%
    echo ========================================================
) else (
    echo [ERREUR] Echec de la sauvegarde directe. Verifiez que MySQL (Laragon) est demarre.
)

:FIN
echo.
echo ========================================================
echo Appuyez sur une touche pour quitter cette fenetre...
pause >nul
