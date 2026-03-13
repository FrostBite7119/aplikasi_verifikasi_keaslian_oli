# ============================================
# Stage 1: Build frontend assets (Vite + Tailwind)
# ============================================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ============================================
# Stage 2: Install PHP dependencies
# ============================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize

# ============================================
# Stage 3: Production image
# ============================================
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        opcache \
        bcmath \
        pcntl

# Cleanup
RUN apk del $PHPIZE_DEPS && rm -rf /var/cache/apk/*

# Configure OPcache for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor from composer stage
COPY --from=vendor /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=frontend /app/public/build ./public/build

# Create required directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Remove any cached config from the source code
RUN rm -f bootstrap/cache/config.php \
    && rm -f bootstrap/cache/routes-v7.php \
    && rm -f bootstrap/cache/services.php \
    && rm -f bootstrap/cache/packages.php

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Ensure PHP-FPM listens on 127.0.0.1:9000 (fix zz-docker.conf which overrides www.conf)
RUN sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/zz-docker.conf 2>/dev/null; \
    sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null; \
    true

# Debug: verify PHP-FPM config
RUN echo "=== PHP-FPM listen config ===" && grep -r "^listen" /usr/local/etc/php-fpm.d/ && echo "==="

# Configure Nginx — remove all defaults and add our config
RUN rm -f /etc/nginx/http.d/default.conf && \
    mkdir -p /etc/nginx/http.d && \
    mkdir -p /run/nginx

# Copy and fix CRLF on ALL config files
COPY docker/nginx.conf /etc/nginx/http.d/app.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN sed -i 's/\r$//' /etc/nginx/http.d/app.conf && \
    sed -i 's/\r$//' /etc/supervisord.conf && \
    sed -i 's/\r$//' /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

# Debug: verify nginx config
RUN nginx -t 2>&1 || true
RUN echo "=== Nginx includes ===" && cat /etc/nginx/nginx.conf | grep -i include && echo "==="

# Cloud Run will set PORT environment variable (default 8080)
EXPOSE 8080

# Start via startup script
CMD ["/usr/local/bin/start.sh"]