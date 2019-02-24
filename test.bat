@echo off

docker exec -it --user root --workdir /app php-corbomite-http bash -c "php /app/scripts/phpunit"
