@echo OFF

set backendFolder=.\

for /f "delims=" %%i in ('composer -V 2^>nul') do set outputComposer=%%i

echo Checking Composer status:
if "!outputComposer!" EQU "" (
    echo - Composer could not be found.
    echo - Please install it going to https://getcomposer.org/download/
    pause
    Goto :eof
) else (
    echo - Correct!
)

cd %backendFolder%
if NOT exist %backendFolder%\vendor\ (
  echo Installing backend composer modules:
  call composer install
  echo - Done
)

echo Booting backend:
call php artisan serve
echo - Done