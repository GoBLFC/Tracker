# Install Composer dependencies
FROM composer:2 AS build-composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies and run Vite
FROM node:20 AS build-npm
WORKDIR /app
COPY . .
RUN npm install
ENV NODE_ENV=production
RUN npm run build

# Set up the application
FROM php:8.2-fpm AS app
WORKDIR /var/www/html

# Override some PHP configuration
COPY .docker/fpm-overrides.conf /usr/local/etc/php-fpm.d/zzz-overrides.conf
COPY .docker/php-overrides.ini $PHP_INI_DIR/conf.d/overrides.ini

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y libpq-dev && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo_pgsql

# Copy everything over
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=build-composer /usr/bin/composer /usr/bin/composer
COPY --chown=www-data:www-data --from=build-composer /app/vendor/ ./vendor/
COPY --chown=www-data:www-data --from=build-npm /app/public/build/ ./public/build/

# Cache all the things!
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Set up basic healthcheck
HEALTHCHECK --interval=10s --timeout=3s \
  CMD curl -f http://localhost/ping || exit 1
