FROM php:8.2-cli

WORKDIR /app

# Installs system dependencies required for Composer
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Installs Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./

# Installs PHP dependencies using Composer
RUN composer install --no-autoloader --no-scripts

COPY . .

RUN composer dump-autoload
