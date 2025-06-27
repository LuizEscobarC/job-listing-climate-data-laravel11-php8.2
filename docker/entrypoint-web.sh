#!/bin/bash

echo "=== CONTAINER WEB STARTUP ==="

# Wait for database
echo "Aguardando banco de dados..."
while ! nc -z db-estech-api 3306; do
  sleep 1
done

# Aguardar vendor ser instalado pelo setup.sh
while [ ! -f "vendor/autoload.php" ]; do
    echo "⏳ Aguardando vendor/ (será instalado pelo setup.sh)..."
    sleep 2
done

# Set permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Generate app key if needed
if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q "APP_KEY=" .env || [ "$(grep APP_KEY= .env | cut -d '=' -f2)" = "" ]; then
    php artisan key:generate --force
fi

# Clear caches
php artisan config:clear
php artisan cache:clear

echo "Iniciando PHP-FPM..."
exec php-fpm