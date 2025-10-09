# Stage 1: Build PHP dependencies
FROM php:8.4-fpm-alpine AS builder

# Install build dependencies for PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    curl \
    unzip \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    && apk add --no-cache \
    libzip \
    oniguruma \
    icu-libs \
    libxml2

# Install PHP extensions required for Laravel
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    bcmath \
    opcache \
    soap

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Clean up build dependencies
RUN apk del .build-deps

WORKDIR /var/www

COPY . /var/www

RUN mkdir -p /var/www/bootstrap/cache /var/www/storage/framework/views \
    && chmod -R 775 /var/www/bootstrap/cache /var/www/storage

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && COMPOSER_PROCESS_TIMEOUT=1200 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Stage 2: Build frontend assets
FROM node:20-alpine AS node-builder

WORKDIR /var/www

COPY package.json package-lock.json ./
RUN npm install

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# Stage 3: Production runtime
FROM php:8.4-fpm-alpine

WORKDIR /var/www

# Install runtime dependencies
RUN apk add --no-cache \
    libzip \
    oniguruma \
    icu-libs \
    libxml2 \
    && apk add --no-cache --virtual .build-deps \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        libxml2-dev \
        autoconf \
        g++ \
        make \
        curl \
        linux-headers \
    && docker-php-ext-install pdo_mysql mbstring zip intl bcmath opcache soap \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN addgroup -S laravel && adduser -S -G laravel laravel

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

RUN mkdir -p /var/www/bootstrap/cache /var/www/storage/framework/views /var/www/storage/framework/sessions /var/www/storage/framework/cache \
    && chown -R laravel:laravel /var/www/bootstrap/cache /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache /var/www/storage

COPY --from=builder --chown=laravel:laravel /var/www/vendor /var/www/vendor

COPY --from=node-builder --chown=laravel:laravel /var/www/public/build /var/www/public/build

COPY --chown=laravel:laravel . .

# Fix storage link (as laravel user)
USER laravel
RUN php artisan storage:link
USER root

COPY --chown=laravel:laravel docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER laravel

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]