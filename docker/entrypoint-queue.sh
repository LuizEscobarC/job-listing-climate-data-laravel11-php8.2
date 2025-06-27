#!/bin/bash

echo "=== QUEUE WORKERS STARTUP ==="

# Wait for database
while ! nc -z db-estech-api 3306; do
  sleep 1
done

# Wait for Redis
while ! nc -z redis-estech-api 6379; do
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

echo "🚀 Iniciando Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf