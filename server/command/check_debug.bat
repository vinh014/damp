@echo off
mode con: cols=80 lines=40

php -m | findstr xdebug | more > "%temp%\xdebug%~n0"
set /p var= < "%temp%\xdebug%~n0"
:: del "%temp%\xdebug%~n0"
echo.
echo.
IF [%var%] EQU [] (
	color 47
	echo xdebug is not enabled
)
IF [%var%] NEQ [] ( 
	color 17
	echo xdebug is enabled
)

timeout 2