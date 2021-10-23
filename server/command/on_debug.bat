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

:: turn on xdebug
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension=""C:/server/ext/php_xdebug" --replace "zend_extension=""C:/server/ext/php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension=""C:/server/ext/php_xdebug" --replace "zend_extension=""C:/server/ext/php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension=""C:/server/ext/php_xdebug" --replace "zend_extension=""C:/server/ext/php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension_ts=""C:/server/ext/php_xdebug" --replace "zend_extension_ts=""C:/server/ext/php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension_ts=""C:/server/ext/php_xdebug" --replace "zend_extension_ts=""C:/server/ext/php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension_ts=""C:/server/ext/php_xdebug" --replace "zend_extension_ts=""C:/server/ext/php_xdebug"

"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension=""C:\server\ext\php_xdebug" --replace "zend_extension=""C:\server\ext\php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension=""C:\server\ext\php_xdebug" --replace "zend_extension=""C:\server\ext\php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension=""C:\server\ext\php_xdebug" --replace "zend_extension=""C:\server\ext\php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension_ts=""C:\server\ext\php_xdebug" --replace "zend_extension_ts=""C:\server\ext\php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension_ts=""C:\server\ext\php_xdebug" --replace "zend_extension_ts=""C:\server\ext\php_xdebug"
"C:\server\fnr.exe" --cl --silent --dir "C:\server\php" --fileMask "php.ini" --find ";zend_extension_ts=""C:\server\ext\php_xdebug" --replace "zend_extension_ts=""C:\server\ext\php_xdebug"
restart_apache.bat "Turning on xdebug"
:: reload_apache.bat "Turning on xdebug"