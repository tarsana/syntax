FROM php:8.4-cli

# Install system dependencies needed for Composer, Git, and building extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    gzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install zip mbstring xml \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Enable Xdebug coverage mode
ENV XDEBUG_MODE=coverage

# Install the latest Composer (v2)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Make /app a "safe" Git directory
RUN git config --global --add safe.directory /app

# Verify installation
RUN composer --version && git --version

WORKDIR /app
COPY . /app