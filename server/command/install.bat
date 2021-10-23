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

echo Install Apache and MySQL services & echo. & echo. 

C:

echo 1. Create Apache service & echo.
sc create Apache binPath= "C:\server\apache\bin\httpd.exe -k runservice" DisplayName= "Apache" start= auto 

cd C:\server\mysql\bin

echo. & echo 2. Create MySQL service & echo.
mysqld.exe --install MySQL --defaults-file=C:\server\mysql\my.ini

SET PASSWORD FOR root@'127.0.0.1' = PASSWORD('root');

echo. & echo 3. Start Apache service & echo.
net start Apache

echo 4. Start MySQL service & echo.
net start MySQL

echo 5. Flush Dns & echo.
ipconfig /flushdns

echo Done & echo.

TIMEOUT 3