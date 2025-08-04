# Multi-stage build for production-ready PHP application
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        mbstring \
        opcache \
        bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -D -S -G www www

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Set proper permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs \
    && chmod -R 777 /var/www/html/cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && mkdir -p /var/www/html/logs \
    && mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/cache

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Development stage
FROM base AS development

# Install development dependencies
RUN composer install --optimize-autoloader --no-interaction

# Install Xdebug for development
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy development PHP configuration
COPY docker/php-dev.ini /usr/local/etc/php/conf.d/99-dev.ini

# Production stage
FROM base AS production

# Remove development files
RUN rm -rf tests/ \
    && rm -rf docker/ \
    && rm -f composer.lock \
    && rm -f .env.example \
    && rm -f README.md

# Optimize for production
RUN composer dump-autoload --optimize --classmap-authoritative

# Set production environment
ENV APP_ENV=production
ENV APP_DEBUG=false

# Final security hardening
RUN rm -rf /tmp/* /var/cache/apk/*

USER www
