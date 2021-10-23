@echo off
SC QUERY %~1 > NUL
IF %ERRORLEVEL% == 1060 (
	set exist=0
) else (
	set exist=1
)