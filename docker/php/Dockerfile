FROM php:8.4-fpm-alpine

LABEL maintainer="Laravel Team"

ARG WWWGROUP=1000
ARG NODE_VERSION=20

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    supervisor \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user and set permissions
RUN addgroup -g ${WWWGROUP} app && \
    adduser -D -G app -s /bin/bash -u 1337 app && \
    mkdir -p /var/www/html/storage/framework/{cache,sessions,views} \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
             /var/www/html/node_modules \
             /home/app/.npm && \
    chown -R 1337:${WWWGROUP} /var/www/html /home/app && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /home/app/.npm

# Copy configurations
COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set npm cache directory for the app user
USER app
ENV npm_config_cache=/home/app/.npm

EXPOSE 9000

CMD ["php-fpm", "-F"]
