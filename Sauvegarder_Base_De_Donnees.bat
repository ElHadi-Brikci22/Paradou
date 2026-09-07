@echo off
title Sauvegarde de la Base de Donnees - MSK DRY PLUS

set "BASE_DIR=%~dp0"
cd /d "%BASE_DIR%"

set "BACKUP_DIR=%BASE_DIR%sauvegardes"
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set "DATE_STR=%datetime:~0,4%-%datetime:~4,2%-%datetime:~6,2%_%datetime:~8,2%h%datetime:~10,2%"
set "BACKUP_FILE=%BACKUP_DIR%\msk_dry_plus_backup_%DATE_STR%.sql"

set "MYSQLDUMP_EXE=mysqldump.exe"
if exist "C:\laragon\bin\mysql" (
    for /d %%D in ("C:\laragon\bin\mysql\mysql-*") do (
        if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP_EXE=%%D\bin\mysqldump.exe"
    )
)

echo [INFO] Sauvegarde en cours vers : %BACKUP_FILE% ...

"%MYSQLDUMP_EXE%" -u root --databases msk_dry_plus > "%BACKUP_FILE%"

if %ERRORLEVEL% equ 0 (
    echo [SUCCES] Base de donnees sauvegardee avec succes !
    echo Fichier : %BACKUP_FILE%
) else (
    echo [ERREUR] Echec de la sauvegarde. Verifiez que MySQL est bien en cours d'execution.
)

pause
