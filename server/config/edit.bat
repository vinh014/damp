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

if "default" == "%2" (
    start "" "C:\Windows\System32\notepad.exe"
    "C:\Windows\System32\notepad.exe" %~1
    EXIT
)

:: http://stackoverflow.com/questions/5909012/windows-batch-script-launch-program-and-exit-console
if exist "C:\Program Files (x86)\Notepad++\notepad++.exe" (
    start "" "C:\Program Files (x86)\Notepad++\notepad++.exe"
    "C:\Program Files (x86)\Notepad++\notepad++.exe"  %~1
) else (
    if exist "C:\Program Files\Notepad++\notepad++.exe" (
		start "" "C:\Program Files\Notepad++\notepad++.exe"
		"C:\Program Files\Notepad++\notepad++.exe"  %~1
	) else (
		start "" "C:\Windows\System32\notepad.exe"
		"C:\Windows\System32\notepad.exe" %~1
	)
)
