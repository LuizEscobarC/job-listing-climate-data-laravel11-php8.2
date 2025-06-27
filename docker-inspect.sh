#!/bin/bash

echo "====================================================="
echo "Laravel Docker Environment Inspector"
echo "====================================================="

echo "1. Container Status"
echo "-------------------"
docker ps | grep app-estech-api

echo
echo "2. Laravel Version"
echo "-------------------"
docker exec app-estech-api php artisan --version || echo "Failed to get Laravel version"

echo 
echo "3. PHP Version"
echo "-------------------"
docker exec app-estech-api php -v || echo "Failed to get PHP version"

echo
echo "4. Installed Composer Packages"
echo "-------------------"
docker exec app-estech-api composer show --format=json | grep name || echo "Failed to get Composer packages"

echo
echo "5. PHP Unit Version"
echo "-------------------"
docker exec app-estech-api ./vendor/bin/phpunit --version || echo "Failed to get PHPUnit version"

echo
echo "6. Checking PHPUnit Configuration"
echo "-------------------"
docker exec app-estech-api cat phpunit.xml | grep -A 5 "<testsuites>" || echo "Failed to inspect PHPUnit config"

echo
echo "====================================================="
