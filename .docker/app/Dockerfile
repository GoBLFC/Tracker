# Install Composer dependencies
FROM composer:2 AS build-composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --classmap-authoritative --ignore-platform-reqs --no-interaction

# Install NPM dependencies and run Vite
FROM node:22 AS build-npm
WORKDIR /app
COPY . .
RUN npm install
ENV NODE_ENV=production
RUN npm run build

# Set up the application
FROM php:8.4-fpm AS app
WORKDIR /var/www/html

# Install necessary packages and PHP extensions
RUN apt update \
	&& apt install -y \
		supervisor \
		cron \
		libfcgi-bin \
		libpq-dev \
		libzip-dev \
		libfreetype-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& apt clean \
	&& rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql && docker-php-ext-install -j$(nproc) pdo_pgsql
RUN docker-php-ext-configure zip && docker-php-ext-install -j$(nproc) zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-configure pcntl --enable-pcntl && docker-php-ext-install -j$(nproc) pcntl
RUN pecl install redis-6.2.0 && docker-php-ext-enable redis

# Override some PHP configuration
COPY .docker/app/fpm-overrides.conf /usr/local/etc/php-fpm.d/zzz-overrides.conf
COPY .docker/app/php-overrides.ini $PHP_INI_DIR/conf.d/overrides.ini

# Configure cron
RUN echo '* * * * * cd /var/www/html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1' >> /etc/cron.d/laravel-scheduler \
	&& crontab -u www-data /etc/cron.d/laravel-scheduler \
	&& chmod u+s /usr/sbin/cron

# Configure supervisor
COPY .docker/app/supervisord.ini /etc/supervisor.d/supervisord.ini
ENTRYPOINT ["supervisord", "-n", "-c", "/etc/supervisor.d/supervisord.ini"]

# Copy everything over
COPY --chown=www-data:www-data . .
COPY --from=build-composer /usr/bin/composer /usr/bin/composer
COPY --chown=www-data:www-data --from=build-composer /app/vendor/ ./vendor/
COPY --chown=www-data:www-data --from=build-npm /app/public/build/ ./public/build/

# Pre-cache the views and discovered event listeners, as they are guaranteed to not change or rely on configuration
USER www-data
RUN php artisan view:cache
RUN php artisan event:cache
USER root

# Mark expected volumes
VOLUME /var/www/html/storage/logs

# Set up basic healthcheck
HEALTHCHECK --interval=10s --timeout=3s \
  CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1
