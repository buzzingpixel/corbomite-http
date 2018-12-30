@echo off

if "%1" == "" (
    set ENV=php
) else (
    set ENV=%1
)

docker exec -it --user root %ENV%-corbomite-http bash
