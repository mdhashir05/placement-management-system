@echo off
title Placement Management System - Local Server
cd /d "%~dp0"

echo ===================================================
echo   Starting Placement Management System
echo ===================================================
echo.

:: 1. Start MariaDB / MySQL if not already running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MariaDB/MySQL is already running.
) else (
    echo [*] Starting MariaDB from XAMPP...
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "MariaDB Server" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
    ) else (
        echo [!] C:\xampp\mysql\bin\mysqld.exe not found. Please ensure MySQL is running.
    )
)

:: 2. Determine PHP executable path
set PHP_BIN=
if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" (
    set "PHP_BIN=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
) else (
    where php >nul 2>nul
    if "%ERRORLEVEL%"=="0" (
        set PHP_BIN=php
    ) else if exist "C:\xampp\php\php.exe" (
        set "PHP_BIN=C:\xampp\php\php.exe"
    )
)

if "%PHP_BIN%"=="" (
    echo [ERROR] PHP executable could not be found!
    pause
    exit /b 1
)

echo.
echo ===================================================
echo   Server URL: http://localhost:8000
echo   Admin Login:   admin@placement.com   / admin123
echo   Student Login: john.doe@student.edu   / student123
echo   Company Login: hr@apexcloud.tech      / company123
echo ===================================================
echo.
echo Press Ctrl+C in this window to stop the server anytime.
echo.

:: 3. Open browser and launch server
start http://localhost:8000
"%PHP_BIN%" -S 127.0.0.1:8000
