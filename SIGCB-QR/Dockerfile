FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm ci && npm run build

FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev libpng-dev libonig-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring gd zip \
    && a2enmod rewrite

COPY --from=vendor /app/vendor /var/www/html/vendor
COPY --from=frontend /app/public/build /var/www/html/public/build
COPY . /var/www/html/

RUN cp .env.example .env 2>/dev/null || true; \
    php artisan key:generate --force 2>/dev/null || true

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
