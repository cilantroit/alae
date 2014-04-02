@echo off

tasklist /v | find /I /c "MyUniqueTitle" > nul
if "%ERRORLEVEL%" == "0" goto ErrorAlreadyRunning

title MyUniqueTitle
echo "Running as Single Instance!"
start "cronjob" "C:\xampp\php\php.exe" -f C:\xampp\htdocs\alae\public\index.php checkdirectory
goto end


:ErrorAlreadyRunning
echo "ErrorAlreadyRunning"

:end