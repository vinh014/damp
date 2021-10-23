@echo off

:: BatchGotAdmin
:-------------------------------------
REM  --> Check for permissions
>nul 2>&1 "%SYSTEMROOT%\system32\cacls.exe" "%SYSTEMROOT%\system32\config\system"

REM --> If error flag set, we do not have admin.
if '%errorlevel%' NEQ '0' (
    echo Requesting administrative privileges...
    goto UACPrompt
) else ( goto gotAdmin )

:UACPrompt
    echo Set UAC = CreateObject^("Shell.Application"^) > "%temp%\getadmin%~n0.vbs"
    set params = %*:"=""
    echo UAC.ShellExecute "cmd.exe", "/c %~s0 %params%", "", "runas", 1 >> "%temp%\getadmin%~n0.vbs"

    "%temp%\getadmin%~n0.vbs"
    :: del "%temp%\getadmin%~n0.vbs"
    exit /B

:gotAdmin
    pushd "%CD%"
    CD /D "%~dp0"
:--------------------------------------

@echo off
mode con: cols=80 lines=40

:: http://stackoverflow.com/questions/9102422/windows-batch-set-inside-if-not-working
:: Therefore the delayedExpansion syntax exists, it uses ! instead of % and it is evaluated at execution time, not parse time.
setlocal EnableDelayedExpansion

:: http://stackoverflow.com/questions/2763875/batch-file-include-external-file-for-variables
set service="Apache"
call check_exist.bat %service%
if %exist% == 0 (
    set service2="Mysql"
    call check_exist.bat !service2!
    if !exist! == 0 (
        exit 0
    )
    echo Service %service% is not installed.
    TIMEOUT 3
    exit 0
)

echo %~1 & echo.

echo Reload Apache service & echo. & echo.

echo 1. Reload Apache service & echo.
C:\server\apache\bin\httpd.exe -k restart -n Apache

echo 2. Flush Dns
ipconfig /flushdns

echo Done & echo.

TIMEOUT 3