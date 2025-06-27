FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    sqlite3 \
    libsqlite3-dev \
    default-mysql-client \
    netcat-openbsd \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer APENAS UMA VEZ
COPY --from=composer:2.7.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application code
COPY . .

# Create Laravel directories and set proper permissions
RUN mkdir -p storage/logs \
             storage/framework/cache \
             storage/framework/sessions \
             storage/framework/views \
             bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy entrypoint and PHP config
COPY docker/entrypoint-web.sh /usr/local/bin/entrypoint-web.sh
COPY docker/config/php.ini /usr/local/etc/php/conf.d/custom.ini
RUN chmod +x /usr/local/bin/entrypoint-web.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint-web.sh"]